"""Offline new-package wrapper evidence tests: child stubs, no browser/network."""
from pathlib import Path
import json
import os
import shutil
import subprocess
import tempfile

SOURCE = Path(__file__).resolve().parents[1]
RETAINED = ['disclosures-chromium', 'disclosures-firefox', 'layout-chromium',
            'layout-firefox', 'retained-title-rails']
RAW_OUT = 'PASS literal child output retained: ' + 'x' * 18000 + '\r\n'
RAW_ERR = 'literal stderr: backslash \\ and Unicode →\nlast stderr byte'
passed = 0


def check_case(group, mode, scenario, expected, bad_index=0):
    global passed
    with tempfile.TemporaryDirectory(prefix='peek-input-wrapper-') as temp:
        base = Path(temp)
        package = base / group
        package.mkdir()
        (base / 'core-kiro-fixes').mkdir()
        shutil.copyfile(SOURCE / 'input-gates.mjs', base / 'input-gates.mjs')
        for name in ['check-public.mjs', 'check-rehearsal.mjs']:
            shutil.copyfile(SOURCE / group / name, package / name)
        trace = base / 'calls.jsonl'
        env = dict(os.environ, RUNNER_TEMP=str(base / 'logs'))

        def stub(path, labels, retained=False):
            cases = []
            for label, index in labels:
                this = scenario if index == bad_index else 'pass'
                lines = [f'{label}: 2 passed, 0 failed; actual cases']
                exit_code, signal, stderr = 0, False, RAW_ERR
                if retained:
                    lines = [f'INFO Peek retained {i}: 2 passed, 0 failed; actual cases'
                             for i in range(13 if mode == 'public' else 14)]
                    if this == 'retained-short':
                        lines.pop()
                    if this == 'retained-empty':
                        lines[0] = 'INFO Peek retained 0: 0 passed, 0 failed; no cases'
                    # Existing wrappers replay summaries: duplicates must not inflate totals.
                    lines += list(lines)
                    names = RETAINED[:-1] if this == 'retained-wrapper-missing' else RETAINED
                    for name in names:
                        child_exit = 2 if this == 'retained-wrapper-invalid' and name == names[0] else 0
                        lines.append(f'INFO Kiro fixes {mode} wrapper {name}: child_exit={child_exit}; signal=none; effective_exit={child_exit}; 2 passed, 0 failed; stdout=nested; stderr=nested')
                    if this != 'incomplete-retained':
                        lines.append(f'INFO Peek Kiro fixes {mode}: PASSED, 5 wrapper suites, 1s; complete logs: nested')
                elif this == 'missing':
                    lines = ['INFO no final numeric result']
                elif this == 'empty':
                    lines = []
                elif this == 'zero':
                    lines = [f'{label}: 0 passed, 0 failed; no cases']
                elif this == 'failed':
                    lines = ['FAIL actual assertion', f'{label}: 1 passed, 1 failed; actual cases']
                    exit_code = 1
                elif this == 'invalid':
                    lines = ['INVALID HTTP202: zero cases']
                    exit_code = 2
                elif this == 'stderr-invalid':
                    stderr = 'INVALID challenge in stderr\n'
                elif this == 'crash':
                    lines, exit_code = [], 1
                elif this == 'passed-crash':
                    exit_code = 1
                elif this == 'signal':
                    lines, signal = [], True
                elif this == 'conflicting':
                    lines += [f'{label}: 3 passed, 0 failed; inconsistent duplicate']
                elif this == 'wrong-summary':
                    lines = ['INFO Peek wrong suite: 2 passed, 0 failed; wrong evidence']
                output = RAW_OUT + '\n'.join(lines) + ('\n' if lines else '')
                body = f"appendFileSync({json.dumps(str(trace))}, JSON.stringify({index})+'\\n');\n"
                body += f'process.stdout.write({json.dumps(output)});\nprocess.stderr.write({json.dumps(stderr)});\n'
                body += "process.kill(process.pid, 'SIGTERM');\n" if signal else f'process.exitCode = {exit_code};\n'
                cases.append((index, body, output, stderr))
            code = "import { appendFileSync } from 'node:fs';\n"
            if len(cases) == 1:
                code += cases[0][1]
            else:
                code += "if (process.argv.includes('--firefox')) {\n" + cases[1][1] + '\n} else {\n' + cases[0][1] + '\n}\n'
            (base / path).write_text(code)
            return {index: (output, stderr) for index, _, output, stderr in cases}

        if group == 'core-input-focus':
            expected_raw = stub('check-peek-input-focus.mjs', [('INFO Peek input focus chromium', 0)])
            expected_raw.update(stub(f'core-kiro-fixes/check-{mode}.mjs', [('retained', 1)], retained=True))
            names = ['input-focus-chromium', 'retained-kiro-fixes']
        else:
            expected_raw = stub('rail-input/check-keyboard.mjs', [('INFO Rail input keyboard chromium', 0), ('INFO Rail input keyboard firefox', 1)])
            expected_raw.update(stub('check-rail-ideas.mjs', [('INFO Rail input native touch', 2)]))
            expected_raw.update(stub('check-title-strip.mjs', [('INFO Peek title strip chromium', 3), ('INFO Peek title strip firefox', 4)]))
            names = ['keyboard-chromium', 'keyboard-firefox', 'native-touch', 'title-strip-chromium', 'title-strip-firefox']
        url = 'https://staging39.thaisolitude.com' if mode == 'public' else 'http://localhost:8090'
        result = subprocess.run(['node', str(package / f'check-{mode}.mjs'), url], env=env, capture_output=True, text=True)
        if result.returncode != expected:
            for log in (base / 'logs').rglob('*.log'):
                print('DEBUG', log.name, repr(log.read_bytes()[-1000:]), log.stat().st_size)
            print('DEBUG STUB', (base / 'check-peek-input-focus.mjs').read_text()[-1000:] if group == 'core-input-focus' else '')
        assert result.returncode == expected, (group, mode, scenario, result.returncode, result.stdout, result.stderr)
        count = len(names) if scenario == 'pass' else bad_index + 1
        calls = [json.loads(line) for line in trace.read_text().splitlines()]
        assert calls == list(range(count)), (group, mode, scenario, calls)
        assert f'INFO {group} {mode}: ' in result.stdout and f'{count} wrapper suites' in result.stdout
        assert RAW_OUT.strip() not in result.stdout, 'Routine PASS output should be retained, not replayed'
        logs = base / 'logs' / 'ops-out' / f'{group}-{mode}'
        assert len(list(logs.glob('*.log'))) == count * 2
        # Inspect complete bytes, including CRLF, long PASS output and trailing bytes.
        if scenario != 'signal':
            for index, name in enumerate(names[:count]):
                stdout, stderr = expected_raw[index]
                assert (logs / f'{name}.stdout.log').read_bytes() == stdout.encode(), (scenario, name, 'stdout')
                assert (logs / f'{name}.stderr.log').read_bytes() == stderr.encode(), (scenario, name, 'stderr')
        if scenario == 'pass':
            assert f'INFO {group} {mode}: PASSED, {count} wrapper suites' in result.stdout
            if group == 'core-input-focus':
                n = 13 if mode == 'public' else 14
                assert f'{n * 2} passed, 0 failed across {n} reported suites' in result.stdout
        passed += 1


for group in ['core-input-focus', 'rail-input']:
    for mode in ['public', 'rehearsal']:
        for scenario, expected in [('pass', 0), ('missing', 2), ('empty', 2), ('zero', 2),
                                   ('failed', 1), ('invalid', 2), ('stderr-invalid', 2),
                                   ('crash', 2), ('passed-crash', 2), ('signal', 2),
                                   ('conflicting', 2), ('wrong-summary', 2)]:
            check_case(group, mode, scenario, expected)
        if group == 'core-input-focus':
            for scenario in ['incomplete-retained', 'retained-short', 'retained-empty',
                             'retained-wrapper-missing', 'retained-wrapper-invalid']:
                check_case(group, mode, scenario, 2, bad_index=1)
        else:
            for index in range(1, 5):
                check_case(group, mode, 'missing', 2, bad_index=index)
        url = 'https://staging39.thaisolitude.com' if mode == 'public' else 'http://localhost:8090'
        for args in [[], ['https://thaisolitude.com'], [url + '/'], [url, 'extra']]:
            result = subprocess.run(['node', str(SOURCE / group / f'check-{mode}.mjs'), *args], capture_output=True, text=True)
            assert result.returncode == 2 and 'INVALID' in result.stdout, (group, mode, args)
            assert 'wrapper suites' not in result.stdout
            passed += 1

print(f'Input wrapper evidence/exit/stop/origin harness: {passed} cases passed; no browser or network used.')

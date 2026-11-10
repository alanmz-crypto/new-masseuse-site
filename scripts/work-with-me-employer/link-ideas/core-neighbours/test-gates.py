"""Execute actual neighbour wrappers against local child-process evidence doubles.

No browser/network/install runs: even the Actions WebKit CLI is a temporary
process double. Evidence parsers, retained byte logs and stop decisions are real.
"""
from pathlib import Path
import json
import os
import shutil
import subprocess
import tempfile

HERE = Path(__file__).resolve().parent
SLUGS = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage']
RETAINED = [
    'input focus chromium', 'disclosures chromium', 'disclosures firefox',
    'Kiro layout chromium', 'Kiro layout firefox', 'title rails chromium',
    'title rails firefox', 'title strip chromium', 'title strip firefox',
    'clearance chromium', 'clearance firefox', 'pages chromium', 'pages firefox',
    'native touch', 'retained Thai chromium',
]
RAW_ERR = 'literal stderr: backslash \\ and Unicode →\r\nlast stderr byte'
PASSED = 0


def run_case(mode='rehearsal', scenario='pass', bad='chromium', expected=0, actions=False):
    global PASSED
    with tempfile.TemporaryDirectory(prefix='peek-neighbours-evidence-') as directory:
        root = Path(directory)
        package = root / 'core-neighbours'
        shutil.copytree(HERE, package, ignore=shutil.ignore_patterns('*.py', '*.php'))
        (root / 'core-input-focus').mkdir()
        trace = root / 'trace.jsonl'
        engines = ['chromium', 'firefox', 'webkit'] if mode == 'rehearsal' else ['chromium', 'firefox']
        raw = {}

        def body(name, stdout, stderr=RAW_ERR, exit_code=0, signal=False):
            raw[name] = (stdout.encode(), stderr.encode())
            return (
                "import { appendFileSync } from 'node:fs';\n"
                f"appendFileSync({json.dumps(str(trace))}, JSON.stringify({json.dumps(name)})+'\\n');\n"
                f"process.stdout.write({json.dumps(stdout)});\nprocess.stderr.write({json.dumps(stderr)});\n"
                + ("process.kill(process.pid, 'SIGTERM');\n" if signal else f'process.exitCode={exit_code};\n')
            )

        for engine in engines:
            current = scenario if engine == bad else 'pass'
            assertions = [f'PASS neighbours {engine}/case-{i}/assertion' for i in range(2)]
            # A long single literal assertion proves regular-file byte retention.
            assertions.append(f'PASS neighbours {engine}/long-' + 'x' * 18000)
            images = [f'INLINE peek-neighbours-{engine}-{slug}-{side}-{state}.jpg base64:/9j/2Q=='
                      for slug in SLUGS for side in ['right', 'left']
                      for state in ['middle-short', 'research-landscape']]
            summary = (f'INFO Peek neighbours {engine}: 3 passed, 0 failed; 8/8 completed cases, '
                       f'8 page loads, 16 captures; 1s; '
                       + ('trusted CDP touch and keyboard' if engine == 'chromium' else 'keyboard only')
                       + '; viewport resizing is not Safari, physical-device or OS text-scale validation.')
            exit_code, stderr, signal = 0, RAW_ERR, False
            if current == 'failed':
                assertions[0] = assertions[0].replace('PASS ', 'FAIL ', 1)
                summary = summary.replace('3 passed, 0 failed', '2 passed, 1 failed')
                exit_code = 1
            elif current == 'missing-summary':
                summary = ''
            elif current == 'zero':
                assertions = []
                summary = summary.replace('3 passed', '0 passed')
            elif current == 'wrong-count':
                summary = summary.replace('3 passed', '4 passed')
            elif current == 'wrong-engine':
                summary = summary.replace(f'neighbours {engine}:', 'neighbours wrong:')
            elif current == 'missing-case':
                summary = summary.replace('8/8 completed', '7/8 completed')
            elif current == 'wrong-loads':
                summary = summary.replace('8 page loads', '7 page loads')
            elif current == 'incomplete':
                summary = summary[:-1] + '; INCOMPLETE/INVALID.'
            elif current == 'duplicate-summary':
                summary += '\n' + summary
            elif current == 'conflicting-summary':
                summary += '\n' + summary.replace('3 passed', '4 passed')
            elif current == 'duplicate-assertion':
                assertions[1] = assertions[0]
            elif current == 'wrong-assertion':
                assertions[0] = assertions[0].replace(f'neighbours {engine}/', 'other source/')
            elif current == 'missing-image':
                images.pop()
            elif current == 'duplicate-image':
                images[-1] = images[0]
            elif current == 'extra-image':
                images.append(images[0])
            elif current == 'wrong-image-name':
                images[-1] = images[-1].replace('research-landscape.jpg', 'wrong.jpg')
            elif current == 'malformed-image':
                images[-1] = images[-1].replace('/9j/2Q==', 'AAAA')
            elif current == 'invalid':
                assertions, images, summary, exit_code = [], [], 'INVALID HTTP202: no document', 2
            elif current == 'stderr-invalid':
                stderr += '\nINVALID load challenge\n'
            elif current == 'crash':
                assertions, images, summary, exit_code = [], [], '', 1
            elif current == 'passed-crash':
                exit_code = 1
            elif current == 'signal':
                assertions, images, summary, signal = [], [], '', True
            stdout = '\r\n'.join([*assertions, *images, summary]) + '\r\n'
            (root / f'{engine}.mjs').write_text(body(engine, stdout, stderr, exit_code, signal))
        (root / 'check-peek-neighbours.mjs').write_text(
            "const engine=process.argv.includes('--webkit')?'webkit':process.argv.includes('--firefox')?'firefox':'chromium';\n"
            "await import('./'+engine+'.mjs');\n")
        names = RETAINED if mode == 'rehearsal' else RETAINED[:-1]
        leaf = [f'INFO Peek {name}: 2 passed, 0 failed; actual retained cases' for name in names]
        if bad == 'retained':
            if scenario == 'missing-leaf':
                leaf.pop()
            elif scenario == 'empty-leaf':
                leaf[0] = leaf[0].replace('2 passed', '0 passed')
            elif scenario == 'conflicting-leaf':
                leaf.append(leaf[0].replace('2 passed', '3 passed'))
            elif scenario == 'unexpected-leaf':
                leaf[0] = leaf[0].replace('input focus chromium', 'wrong suite')
            elif scenario == 'failed':
                leaf[0] = leaf[0].replace('2 passed, 0 failed', '1 passed, 1 failed')
        # Repeated nested summaries are normal; totals must remain deduplicated.
        retained = leaf + leaf
        verdicts = [f'INFO core-input-focus {mode} wrapper {name}: child_exit=0; signal=none; effective_exit=0; 2 passed, 0 failed; stdout=nested; stderr=nested'
                    for name in ['input-focus-chromium', 'retained-kiro-fixes']]
        aggregate = f'INFO core-input-focus {mode}: PASSED, 2 wrapper suites, 1s; complete logs: nested'
        if bad == 'retained':
            if scenario == 'missing-wrapper':
                verdicts.pop()
            elif scenario == 'duplicate-wrapper':
                verdicts.append(verdicts[0])
            elif scenario == 'bad-wrapper':
                verdicts[0] = verdicts[0].replace('effective_exit=0', 'effective_exit=2')
            elif scenario == 'missing-aggregate':
                aggregate = ''
            elif scenario == 'duplicate-aggregate':
                aggregate += '\n' + aggregate
        stdout = '\n'.join([*retained, *verdicts, aggregate]) + '\n'
        (root / 'core-input-focus' / f'check-{mode}.mjs').write_text(body('retained', stdout))
        # Fake exact package and official CLI: invocation order is exercised,
        # but these subprocesses never download or install anything.
        pw = root / 'pw'
        (pw / 'node_modules/playwright').mkdir(parents=True)
        (pw / 'package.json').write_text('{}')
        version = '0.0.0' if scenario == 'wrong-pin' else '1.63.0'
        (pw / 'node_modules/playwright/package.json').write_text(json.dumps({'name': 'playwright', 'version': version}))
        (pw / 'node_modules/playwright/cli.js').write_text(
            "const fs=require('node:fs');\n"
            f"fs.appendFileSync({json.dumps(str(trace))}, JSON.stringify(process.argv.slice(2))+'\\n');\n"
            f"if(process.argv[2] === {json.dumps('install-deps' if scenario == 'deps-fail' else 'install' if scenario == 'install-fail' else 'none')}) process.exitCode=73;\n")
        commands = root / 'bin'
        commands.mkdir()
        sudo = commands / 'sudo'
        sudo.write_text('#!/bin/sh\n[ "$1" = -n ] && shift\nexec "$@"\n')
        sudo.chmod(0o700)
        env = dict(os.environ, RUNNER_TEMP=str(root / 'logs'), TS_PLAYWRIGHT_ROOT=str(pw / 'package.json'),
                   GITHUB_ACTIONS='true' if actions else 'false', S_TARGET='staging39',
                   PATH=str(commands) + os.pathsep + os.environ['PATH'])
        env.pop('TS_PEEK_REHEARSAL_PROFILE', None)
        url = 'http://localhost:8090' if mode == 'rehearsal' else 'https://staging39.thaisolitude.com'
        result = subprocess.run(['node', str(package / f'check-{mode}.mjs'), url], env=env,
                                capture_output=True, text=True, timeout=20)
        calls = [json.loads(line) for line in trace.read_text().splitlines()] if trace.exists() else []
        assert result.returncode == expected, (mode, scenario, bad, result.returncode, result.stdout[-3000:], result.stderr, calls)
        if scenario in ['wrong-pin', 'deps-fail', 'install-fail']:
            wanted = ['chromium', 'firefox']
            if scenario != 'wrong-pin':
                wanted.append(['install-deps', 'webkit'])
            if scenario == 'install-fail':
                wanted.append(['install', 'webkit'])
        else:
            wanted = engines + ['retained'] if scenario == 'pass' or bad == 'retained' else engines[:engines.index(bad) + 1]
            if actions:
                wanted[2:2] = [['install-deps', 'webkit'], ['install', 'webkit']]
        assert calls == wanted, (mode, scenario, 'child order/stop', calls, wanted)
        folders = list((root / 'logs/ops-out').glob('core-neighbours-*'))
        assert len(folders) == 1
        saved = json.loads((folders[0] / 'results.json').read_text())
        assert saved['code'] == expected
        for name in [call for call in calls if isinstance(call, str)]:
            prefix = 'retained-input-focus' if name == 'retained' else 'neighbours-' + name
            if scenario != 'signal':
                for channel, data in zip(['stdout', 'stderr'], raw[name]):
                    assert (folders[0] / f'{prefix}.{channel}.log').read_bytes() == data
        if scenario == 'pass':
            assert saved['counts'] == {'passed': len(engines) * 3 + len(names) * 2, 'failed': 0}
        PASSED += 1


for mode in ['rehearsal', 'public']:
    run_case(mode)
    for scenario in ['missing-summary', 'zero', 'wrong-count', 'wrong-engine', 'missing-case',
                     'wrong-loads', 'incomplete', 'duplicate-summary', 'conflicting-summary',
                     'duplicate-assertion', 'wrong-assertion', 'missing-image', 'duplicate-image',
                     'extra-image', 'wrong-image-name', 'malformed-image', 'invalid',
                     'stderr-invalid', 'crash', 'passed-crash', 'signal']:
        run_case(mode, scenario, expected=2)
    run_case(mode, 'failed', expected=1)
    for engine in ['firefox'] + (['webkit'] if mode == 'rehearsal' else []):
        run_case(mode, 'missing-image', engine, expected=2)
    for scenario in ['missing-leaf', 'empty-leaf', 'conflicting-leaf', 'unexpected-leaf',
                     'missing-wrapper', 'duplicate-wrapper', 'bad-wrapper',
                     'missing-aggregate', 'duplicate-aggregate']:
        run_case(mode, scenario, 'retained', expected=2)
    run_case(mode, 'failed', 'retained', expected=1)
run_case(actions=True)
for scenario in ['wrong-pin', 'deps-fail', 'install-fail']:
    run_case(scenario=scenario, bad='webkit', expected=2, actions=True)
for mode in ['public', 'rehearsal']:
    url = 'https://staging39.thaisolitude.com' if mode == 'public' else 'http://localhost:8090'
    for args in [[], ['https://thaisolitude.com'], [url + '/'], [url, 'extra']]:
        result = subprocess.run(['node', str(HERE / f'check-{mode}.mjs'), *args], capture_output=True, text=True)
        assert result.returncode == 2 and 'INVALID' in result.stdout and 'phases;' not in result.stdout
        PASSED += 1
    for key, value in [('S_TARGET', 'live'), ('TS_PEEK_REHEARSAL_PROFILE', 'visitor-release-v1')]:
        result = subprocess.run(['node', str(HERE / f'check-{mode}.mjs'), url],
                                env=dict(os.environ, **{key: value}), capture_output=True, text=True)
        assert result.returncode == 2 and 'INVALID' in result.stdout and 'phases;' not in result.stdout
        PASSED += 1
print(f'Core neighbours evidence/origin/stop/WebKit-install harness: {PASSED} cases passed; no browser/network/install used.')

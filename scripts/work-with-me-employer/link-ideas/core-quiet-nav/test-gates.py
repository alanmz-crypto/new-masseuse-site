"""Run the real new gate and retained wrapper graph against browser doubles.

Only temporary child programs are substituted. No network, browser, install or
website operation runs. The old index-clearance harness remains unchanged.
"""
from pathlib import Path
import base64
import json
import os
import shutil
import subprocess
import tempfile

HERE = Path(__file__).resolve().parent
SLUGS = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage',
         'services', 'corporate-massage-portland', 'work-with-me', 'privacy-policy', 'search']
CASES = [f'{slug}/{side}' for slug in SLUGS for side in ['right', 'left']]
RETAINED = ['input focus chromium', 'disclosures chromium', 'disclosures firefox',
            'Kiro layout chromium', 'Kiro layout firefox', 'title rails chromium',
            'title rails firefox', 'title strip chromium', 'title strip firefox',
            'clearance chromium', 'clearance firefox', 'pages chromium', 'pages firefox',
            'native touch', 'retained Thai chromium']
PUBLIC_RETAINED = [name for name in RETAINED if name not in ['pages firefox', 'retained Thai chromium']] + ['clearance chromium diagnostic subset']
LIMITATION = 'browser touch emulation is not physical-device, Safari or assistive-technology validation.'
OLD_LIMITATION = 'viewport resizing is not Safari, physical-device or OS text-scale validation.'
RAW_ERR = 'literal stderr: backslash \\ and Unicode →\r\nlast stderr byte'
PASSED = 0


def run_case(mode='rehearsal', scenario='pass', bad='chromium', expected=0, actual_chain=False):
    global PASSED
    with tempfile.TemporaryDirectory(prefix='peek-quiet-evidence-') as directory:
        root = Path(directory)
        package = root / 'core-quiet-nav'
        shutil.copytree(HERE, package, ignore=shutil.ignore_patterns('*.py', '*.php'))
        engines = ['chromium', 'firefox', 'webkit'] if mode == 'rehearsal' else ['chromium', 'firefox']
        names = RETAINED if mode == 'rehearsal' else PUBLIC_RETAINED
        trace = root / 'trace.jsonl'
        raw = {}

        def program(name, stdout, stderr=RAW_ERR, exit_code=0, signal=False):
            raw[name] = (stdout.encode(), stderr.encode())
            return ("import { appendFileSync } from 'node:fs';\n"
                    f"appendFileSync({json.dumps(str(trace))}, JSON.stringify({json.dumps(name)})+'\\n');\n"
                    f"process.stdout.write({json.dumps(stdout)});\nprocess.stderr.write({json.dumps(stderr)});\n"
                    + ("process.kill(process.pid, 'SIGTERM');\n" if signal else f'process.exitCode={exit_code};\n'))

        for engine in engines:
            current = scenario if bad == engine else 'pass'
            # The focused checker uses exact slash-separated case labels and
            # sequential check numbers, including terminal source/error checks.
            assertions = [line for i, case in enumerate(CASES) for line in [
                f'PASS quiet-nav {engine}/{case}/geometry [check {i * 3 + 1}]',
                f'PASS quiet-nav {engine}/{case}/content, wording, original links and destination count remain unchanged [check {i * 3 + 2}]',
                f'PASS quiet-nav {engine}/{case}/no JavaScript errors [check {i * 3 + 3}]',
            ]]
            assertions[0] = assertions[0].replace('/geometry ', '/geometry-' + 'x' * 18000 + ' ')
            completed = [f'INFO quiet-nav completed {engine}/{case}' for case in CASES]
            captures = [f'INLINE peek-quiet-nav-{engine}-{slug}-{side}-{kind}.jpg base64:/9j/2Q=='
                        for slug in SLUGS for side in ['right', 'left'] for kind in ['idle-reading', 'index-open']]
            # Force a payload larger than pipe buffers through the new adapters.
            if actual_chain:
                captures[0] = captures[0].replace('/9j/2Q==', base64.b64encode(b'\xff\xd8' + b'x' * 262144 + b'\xff\xd9').decode())
            case_count, assertion_count, capture_count = len(CASES), len(assertions), len(captures)
            summary = f'INFO Peek quiet nav {engine}: {assertion_count} passed, 0 failed; {case_count}/{case_count} cases, {case_count} page loads, {capture_count} captures, 1s; {LIMITATION}'
            stderr, exit_code, signal = RAW_ERR, 0, False
            if current == 'failed':
                assertions[0] = assertions[0].replace('PASS ', 'FAIL ', 1)
                summary = summary.replace(f'{assertion_count} passed, 0 failed', f'{assertion_count - 1} passed, 1 failed')
                exit_code = 1
            elif current == 'missing-summary': summary = ''
            elif current == 'wrong-count': summary = summary.replace(f'{assertion_count} passed', f'{assertion_count + 1} passed')
            elif current == 'zero':
                assertions = []
                summary = summary.replace(f'{assertion_count} passed', '0 passed')
            elif current == 'wrong-engine': summary = summary.replace(f'quiet nav {engine}:', 'quiet nav wrong:')
            elif current == 'missing-case-count': summary = summary.replace(f'{case_count}/{case_count} cases', f'{case_count - 1}/{case_count} cases')
            elif current == 'wrong-loads': summary = summary.replace(f'{case_count} page loads', f'{case_count - 1} page loads')
            elif current == 'wrong-captures': summary = summary.replace(f'{capture_count} captures', f'{capture_count - 1} captures')
            elif current == 'incomplete': summary += ' INCOMPLETE/INVALID.'
            elif current == 'duplicate-summary': summary += '\n' + summary
            elif current == 'conflicting-summary': summary += '\n' + summary.replace(f'{assertion_count} passed', f'{assertion_count + 1} passed')
            elif current == 'previous-eight-route-matrix':
                assertions = [line for line in assertions if '/corporate-massage-portland/' not in line]
                completed = [line for line in completed if '/corporate-massage-portland/' not in line]
                captures = [line for line in captures if '-corporate-massage-portland-' not in line]
                summary = f'INFO Peek quiet nav {engine}: {len(assertions)} passed, 0 failed; {case_count - 2}/{case_count - 2} cases, {case_count - 2} page loads, {len(captures)} captures, 1s; {LIMITATION}'
            elif current == 'missing-completion': completed.pop()
            elif current == 'duplicate-completion': completed[-1] = completed[0]
            elif current == 'wrong-completion': completed[-1] = f'INFO quiet-nav completed {engine}/unknown/right'
            elif current == 'duplicate-assertion': assertions[-1] = assertions[0]
            elif current == 'wrong-assertion': assertions[0] = assertions[0].replace('quiet-nav', 'wrong-source')
            elif current == 'wrong-case': assertions[0] = assertions[0].replace('thai-massage/right', 'unknown/right')
            elif current == 'unasserted-case':
                assertions = assertions[:-3]
                summary = summary.replace(f'{assertion_count} passed', f'{assertion_count - 3} passed')
            elif current == 'failed-terminal':
                assertions[-1] = assertions[-1].replace('PASS ', 'FAIL ', 1)
                summary = summary.replace(f'{assertion_count} passed, 0 failed', f'{assertion_count - 1} passed, 1 failed')
                exit_code = 1
            elif current == 'duplicate-terminal': assertions[-1] = assertions[-2]
            elif current == 'missing-terminal': assertions.pop()
            elif current == 'terminal-wrong-case': assertions[-1] = assertions[-1].replace('search/left/', 'unknown/left/')
            elif current == 'terminal-wrong-side': assertions[-1] = assertions[-1].replace('search/left/', 'search/middle/')
            elif current == 'terminal-wrong-engine': assertions[-1] = assertions[-1].replace(f'{engine}/', 'wrong-engine/', 1)
            elif current == 'terminal-wrong-source': assertions[-1] = assertions[-1].replace('quiet-nav', 'wrong-source')
            elif current == 'colon-delimiter': assertions[-1] = assertions[-1].replace('search/left/', 'search/left: ', 1)
            elif current == 'missing-check-number': assertions[-1] = assertions[-1].split(' [check ', 1)[0]
            elif current == 'wrong-check-number': assertions[-1] = assertions[-1].replace(f'[check {assertion_count}]', '[check 1]')
            elif current == 'skipped-check-number': assertions[-1] = assertions[-1].replace(f'[check {assertion_count}]', f'[check {assertion_count + 1}]')
            elif current == 'duplicate-label-new-number': assertions[-1] = assertions[0].replace('[check 1]', f'[check {assertion_count}]')
            elif current == 'missing-image': captures.pop()
            elif current == 'duplicate-image': captures[-1] = captures[0]
            elif current == 'extra-image': captures.append(captures[0])
            elif current == 'wrong-image': captures[-1] = captures[-1].replace('index-open.jpg', 'unknown.jpg')
            elif current == 'malformed-image': captures[-1] = captures[-1].replace('/9j/2Q==', 'AAAA')
            elif current == 'invalid':
                assertions, completed, captures, summary, exit_code = [], [], [], 'INVALID HTTP202: no document', 2
            elif current == 'stderr-invalid': stderr += '\nINVALID hosting challenge\n'
            elif current == 'stderr-error': stderr += '\nERROR browser failure\n'
            elif current == 'crash':
                assertions, completed, captures, summary, exit_code = [], [], [], '', 1
            elif current == 'passed-crash': exit_code = 1
            elif current == 'signal':
                assertions, completed, captures, summary, signal = [], [], [], '', True
            stdout = '\r\n'.join([*assertions, *completed, *captures, summary]) + '\r\n'
            (root / f'quiet-{engine}.mjs').write_text(program('quiet/' + engine, stdout, stderr, exit_code, signal))
        (root / 'check-peek-quiet-nav.mjs').write_text(
            "const engine=process.argv.includes('--webkit')?'webkit':process.argv.includes('--firefox')?'firefox':'chromium';\n"
            "await import('./quiet-'+engine+'.mjs');\n")

        retained = [f'INFO Peek {name}: 2 passed, 0 failed; retained cases' for name in names]
        retained += [f'INFO Peek neighbours {engine}: 3 passed, 0 failed; retained neighbour cases' for engine in engines]
        retained += [f'INFO Peek index clearance {engine}: {len(CASES) * 3} passed, 0 failed; retained index cases' for engine in engines]
        neighbour_total = len(names) * 2 + len(engines) * 3
        total = neighbour_total + len(engines) * len(CASES) * 3
        phases = [f'INFO core-index-clearance {mode} phase index-clearance-{engine}: child_exit=0; signal=none; effective_exit=0; {len(CASES) * 3} passed, 0 failed; captures=36; stdout=nested; stderr=nested' for engine in engines]
        phases += [f'INFO core-index-clearance {mode} phase retained-neighbours: child_exit=0; signal=none; effective_exit=0; {neighbour_total} passed, 0 failed; captures=0; stdout=nested; stderr=nested']
        aggregate = f'INFO core-index-clearance {mode}: PASSED, {len(phases)}/{len(phases)} phases, {len(retained)}/{len(retained)} leaves; {total} passed, 0 failed; 1s; complete logs: nested'
        retained_stderr = RAW_ERR
        if bad == 'retained':
            if scenario == 'missing-leaf': retained.pop()
            elif scenario == 'empty-leaf': retained[0] = retained[0].replace('2 passed', '0 passed')
            elif scenario == 'conflicting-leaf': retained.append(retained[0].replace('2 passed', '3 passed'))
            elif scenario == 'unexpected-leaf': retained[0] = retained[0].replace('input focus chromium', 'wrong suite')
            elif scenario == 'missing-public-diagnostic': retained = [line for line in retained if not line.startswith('INFO Peek clearance chromium diagnostic subset:')]
            elif scenario == 'fictional-public-firefox': retained = [line.replace('clearance chromium diagnostic subset:', 'pages firefox:') for line in retained]
            elif scenario == 'missing-phase': phases.pop()
            elif scenario == 'duplicate-phase': phases.append(phases[0])
            elif scenario == 'malformed-phase': phases[0] = phases[0].replace('child_exit=0;', 'child_exit=;')
            elif scenario == 'phase-signal': phases[0] = phases[0].replace('signal=none', 'signal=SIGTERM')
            elif scenario == 'bad-phase': phases[0] = phases[0].replace('effective_exit=0', 'effective_exit=2')
            elif scenario == 'wrong-phase-count': phases[0] = phases[0].replace(f'{len(CASES) * 3} passed', f'{len(CASES) * 3 + 1} passed')
            elif scenario == 'wrong-phase-captures': phases[0] = phases[0].replace('captures=36', 'captures=35')
            elif scenario == 'missing-phase-logs': phases[0] = phases[0].replace('stdout=nested', 'stdout=unavailable')
            elif scenario == 'missing-aggregate': aggregate = ''
            elif scenario == 'duplicate-aggregate': aggregate += '\n' + aggregate
            elif scenario == 'wrong-aggregate-leaves': aggregate = aggregate.replace(f'{len(retained)}/{len(retained)} leaves', f'{len(retained) - 1}/{len(retained)} leaves')
            elif scenario == 'wrong-aggregate-count': aggregate = aggregate.replace(f'{total} passed', f'{total + 1} passed')
            elif scenario == 'failed': retained[0] = retained[0].replace('2 passed, 0 failed', '1 passed, 1 failed')
            elif scenario == 'stderr-invalid': retained_stderr += '\nINVALID retained browser crash\n'
            elif scenario == 'stderr-error': retained_stderr += '\nERROR retained browser crash\n'
        # Nested wrappers repeat summaries; only unique leaf counts may count.
        (package / 'check-retained.mjs').write_text(program('retained', '\n'.join([*retained, *retained, *phases, aggregate]) + '\n', retained_stderr))

        if actual_chain:
            # The actual complete call graph, including neighbour parser and
            # all retained contracts. Only browser leaves and official CLI fake.
            relatives = ['core-quiet-nav/check-retained.mjs', 'core-index-clearance/gates.mjs', 'core-index-clearance/contract.mjs', 'core-index-clearance/check-retained.mjs', 'core-neighbours/gates.mjs', 'input-gates.mjs',
                         f'core-input-focus/check-{mode}.mjs', f'core-kiro-fixes/check-{mode}.mjs',
                         'core-kiro-fixes/run-gates.mjs', f'core-title-rail/check-{mode}.mjs',
                         f'core-title-strip/check-{mode}.mjs', f'core-upgrade/check-{mode}.mjs']
            for relative in relatives:
                target = root / relative
                target.parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(HERE.parent / relative, target)
            for engine in engines:
                neighbour = [f'PASS neighbours {engine}/assertion-{i}' for i in range(3)]
                neighbour += [f'INLINE peek-neighbours-{engine}-{slug}-{side}-{kind}.jpg base64:/9j/2Q=='
                              for slug in SLUGS[:4] for side in ['right', 'left'] for kind in ['middle-short', 'research-landscape']]
                neighbour += [f'INFO Peek neighbours {engine}: 3 passed, 0 failed; 8/8 completed cases, 8 page loads, 16 captures; 1s; '
                              + ('trusted CDP touch and keyboard' if engine == 'chromium' else 'keyboard only') + f'; {OLD_LIMITATION}']
                (root / f'neighbours-{engine}.mjs').write_text(program('neighbours/' + engine, '\n'.join(neighbour) + '\n'))
            (root / 'check-peek-neighbours.mjs').write_text(
                "const engine=process.argv.includes('--webkit')?'webkit':process.argv.includes('--firefox')?'firefox':'chromium';\n"
                "await import('./neighbours-'+engine+'.mjs');\n")
            # The retained index wrapper is real; only its rendered browser
            # leaf programs are replaced with complete fixed-matrix evidence.
            for engine in engines:
                old = [f'PASS index-clearance {engine}/{case}/geometry [check {i * 3 + j + 1}]'
                       for i, case in enumerate(CASES) for j in range(3)]
                old += [f'INFO index-clearance completed {engine}/{case}' for case in CASES]
                old += [f'INLINE peek-index-clearance-{engine}-{slug}-{side}-{kind}.jpg base64:/9j/2Q=='
                        for slug in SLUGS for side in ['right', 'left'] for kind in ['short-portrait', 'landscape-top']]
                old += [f'INFO Peek index clearance {engine}: {len(CASES) * 3} passed, 0 failed; {len(CASES)}/{len(CASES)} cases, {len(CASES)} page loads, {len(CASES) * 2} captures, 1s; {OLD_LIMITATION}']
                (root / f'index-{engine}.mjs').write_text(program('index/' + engine, '\n'.join(old) + '\n'))
            (root / 'check-peek-index-clearance.mjs').write_text(
                "const engine=process.argv.includes('--webkit')?'webkit':process.argv.includes('--firefox')?'firefox':'chromium';\n"
                "await import('./index-'+engine+'.mjs');\n")
            for filename, label in [('check-peek-input-focus.mjs', 'input focus'), ('check-peek-disclosures.mjs', 'disclosures'),
                                    ('check-peek-kiro-layout.mjs', 'Kiro layout'), ('check-peek-title-rails.mjs', 'title rails'),
                                    ('check-title-strip.mjs', 'title strip'), ('check-peek-clearance.mjs', 'clearance'),
                                    ('check-peek-pages.mjs', 'pages'), ('check-peek-touch.mjs', 'native touch'),
                                    ('check-peek-thai-regression.mjs', 'retained Thai')]:
                (root / filename).write_text(
                    "import { appendFileSync, writeSync } from 'node:fs';\n"
                    "const engine=process.argv.includes('--firefox')?'firefox':'chromium';\n"
                    f"let label={json.dumps(label)};\n"
                    "if(label!=='native touch') label+=' '+engine;\n"
                    "if(process.argv.includes('--quick')) label+=' diagnostic subset';\n"
                    f"appendFileSync({json.dumps(str(trace))}, JSON.stringify('retained/'+label)+'\\n');\n"
                    "writeSync(1, label==='retained Thai chromium'?'chromium: 2 passed, 0 failed\\n':'INFO Peek '+label+': 2 passed, 0 failed; actual retained cases\\n');\n"
                    f"writeSync(2, {json.dumps(RAW_ERR)});\n")

        pw = root / 'pw'
        (pw / 'node_modules/playwright').mkdir(parents=True)
        (pw / 'package.json').write_text('{}')
        (pw / 'node_modules/playwright/package.json').write_text(json.dumps({'name': 'playwright', 'version': '1.63.0'}))
        (pw / 'node_modules/playwright/cli.js').write_text(
            "const fs=require('node:fs');\n"
            f"fs.appendFileSync({json.dumps(str(trace))}, JSON.stringify(process.argv.slice(2))+'\\n');\n")
        commands = root / 'bin'
        commands.mkdir()
        sudo = commands / 'sudo'
        sudo.write_text('#!/bin/sh\n[ "$1" = -n ] && shift\nexec "$@"\n')
        sudo.chmod(0o700)
        env = dict(os.environ, RUNNER_TEMP=str(root / 'logs'), TS_PLAYWRIGHT_ROOT=str(pw / 'package.json'),
                   GITHUB_ACTIONS='true' if actual_chain else 'false', S_TARGET='staging39',
                   PATH=str(commands) + os.pathsep + os.environ['PATH'])
        env.pop('TS_PEEK_REHEARSAL_PROFILE', None)
        url = 'http://localhost:8090' if mode == 'rehearsal' else 'https://staging39.thaisolitude.com'
        result = subprocess.run(['node', str(package / f'check-{mode}.mjs'), url], env=env,
                                capture_output=True, text=True, timeout=30)
        calls = [json.loads(line) for line in trace.read_text().splitlines()] if trace.exists() else []
        assert result.returncode == expected, (mode, scenario, bad, result.returncode, result.stdout[-6000:], result.stderr, calls)
        if actual_chain:
            strings = [call for call in calls if isinstance(call, str)]
            wanted = {f'neighbours/{engine}' for engine in engines} | {f'index/{engine}' for engine in engines} | {f'quiet/{engine}' for engine in engines} | {f'retained/{name}' for name in names}
            assert len(strings) == len(wanted) and set(strings) == wanted, calls
            assert strings[-len(engines):] == ['quiet/' + engine for engine in engines], calls
            assert [call for call in calls if isinstance(call, list)] == ([['install-deps', 'webkit'], ['install', 'webkit']] if mode == 'rehearsal' else []), calls
        else:
            wanted = ['retained']
            if bad != 'retained':
                called_engines = engines if scenario == 'pass' else engines[:engines.index(bad) + 1]
                wanted += ['quiet/' + engine for engine in called_engines]
            assert calls == wanted, (mode, scenario, bad, 'child stop/order', calls, wanted)
        folders = list((root / 'logs/ops-out').glob('core-quiet-nav-*'))
        assert len(folders) == 1
        saved = json.loads((folders[0] / 'results.json').read_text())
        assert saved['code'] == expected
        for child_result in saved['results']:
            if child_result['name'] == 'retained-index-clearance' and actual_chain: continue
            name = 'retained' if child_result['name'] == 'retained-index-clearance' else child_result['name'].replace('quiet-nav-', 'quiet/')
            if scenario != 'signal':
                for channel, data in zip(['stdout', 'stderr'], raw[name]):
                    assert Path(child_result['logs'][channel]).read_bytes() == data, (name, channel)
        if scenario == 'pass':
            assert saved['leaves'] == saved['expectedLeaves'] == (24 if mode == 'rehearsal' else 20)
            assert saved['counts'] == {'passed': total + len(engines) * len(CASES) * 3, 'failed': 0}
            if actual_chain:
                # These full INLINE lines must survive all the way to caller.
                for engine in engines:
                    for line in raw['quiet/' + engine][0].decode().splitlines():
                        if line.startswith('INLINE '): assert line in result.stdout
                index_saved = json.loads(next((root / 'logs/ops-out').glob('core-index-clearance-*/results.json')).read_text())
                assert index_saved['leaves'] == index_saved['expectedLeaves'] == (21 if mode == 'rehearsal' else 18)
                for item in index_saved['results'][1:]:
                    engine = item['name'].replace('index-clearance-', '')
                    assert Path(item['logs']['stdout']).read_bytes() == raw['index/' + engine][0]
                    assert Path(item['logs']['stderr']).read_bytes() == RAW_ERR.encode()
                    for line in raw['index/' + engine][0].decode().splitlines():
                        if line.startswith('INLINE '): assert line in result.stdout
                retained_saved = json.loads(next((root / 'logs/ops-out').glob('core-neighbours-*/results.json')).read_text())
                assert sum(len(item['summaries']) for item in retained_saved['results']) == (18 if mode == 'rehearsal' else 16)
                for item in retained_saved['results'][:len(engines)]:
                    engine = item['name'].replace('neighbours-', '')
                    assert Path(item['logs']['stdout']).read_bytes() == raw['neighbours/' + engine][0]
                    assert Path(item['logs']['stderr']).read_bytes() == RAW_ERR.encode()
        PASSED += 1


for mode in ['rehearsal', 'public']:
    run_case(mode)
    run_case(mode, actual_chain=True)
    for scenario in ['missing-summary', 'wrong-count', 'zero', 'wrong-engine', 'missing-case-count',
                     'wrong-loads', 'wrong-captures', 'incomplete', 'duplicate-summary', 'conflicting-summary',
                     'previous-eight-route-matrix', 'missing-completion', 'duplicate-completion', 'wrong-completion', 'duplicate-assertion',
                     'wrong-assertion', 'wrong-case', 'unasserted-case', 'duplicate-terminal', 'missing-terminal',
                     'terminal-wrong-case', 'terminal-wrong-side', 'terminal-wrong-engine', 'terminal-wrong-source',
                     'colon-delimiter', 'missing-check-number', 'wrong-check-number', 'skipped-check-number', 'duplicate-label-new-number',
                     'missing-image', 'duplicate-image',
                     'extra-image', 'wrong-image', 'malformed-image', 'invalid', 'stderr-invalid', 'stderr-error',
                     'crash', 'passed-crash', 'signal']:
        run_case(mode, scenario, expected=2)
    run_case(mode, 'failed', expected=1)
    run_case(mode, 'failed-terminal', expected=1)
    for engine in ['firefox'] + (['webkit'] if mode == 'rehearsal' else []):
        run_case(mode, 'missing-image', engine, expected=2)
    for scenario in ['missing-leaf', 'empty-leaf', 'conflicting-leaf', 'unexpected-leaf',
                     'missing-phase', 'duplicate-phase', 'malformed-phase', 'bad-phase', 'phase-signal',
                     'wrong-phase-count', 'wrong-phase-captures', 'missing-phase-logs',
                     'missing-aggregate', 'duplicate-aggregate', 'wrong-aggregate-count', 'wrong-aggregate-leaves', 'stderr-invalid', 'stderr-error']:
        run_case(mode, scenario, 'retained', expected=2)
    run_case(mode, 'failed', 'retained', expected=1)
for scenario in ['missing-public-diagnostic', 'fictional-public-firefox']:
    run_case('public', scenario, 'retained', expected=2)
for mode in ['public', 'rehearsal']:
    url = 'https://staging39.thaisolitude.com' if mode == 'public' else 'http://localhost:8090'
    for args in [[], ['https://thaisolitude.com'], [url + '/'], [url, 'extra']]:
        result = subprocess.run(['node', str(HERE / f'check-{mode}.mjs'), *args], capture_output=True, text=True)
        assert result.returncode == 2 and 'INVALID' in result.stdout and 'phases,' not in result.stdout
        PASSED += 1
    for key, value in [('S_TARGET', 'live'), ('TS_PEEK_REHEARSAL_PROFILE', 'visitor-release-v1')]:
        result = subprocess.run(['node', str(HERE / f'check-{mode}.mjs'), url],
                                env=dict(os.environ, **{key: value}), capture_output=True, text=True)
        assert result.returncode == 2 and 'INVALID' in result.stdout and 'phases,' not in result.stdout
        PASSED += 1
print(f'Core quiet nav evidence/origin/stop/retained-chain harness: {PASSED} cases passed; no browser/network/install used.')

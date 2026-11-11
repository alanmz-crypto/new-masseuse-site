"""Run the real resize orchestrator with local fixed children; no browser/site.

The retained wrappers already test their own leaf parsers. These fixtures test
this adapter's exact matrix, wrapper-result binding, stop order and full logs.
"""
from pathlib import Path
import json
import os
import shutil
import subprocess
import tempfile

SOURCE = Path(__file__).resolve().parents[1]
PASSED = 0
CHILD = r'''
import { appendFileSync } from 'node:fs';
import { resizeCases, RESIZE_LABELS, resizeCaptures, RESIZE_LIMITATION } from './core-title-resize/contract.mjs';
import { quietCases, quietCaptures, QUIET_LIMITATION } from './core-quiet-nav/contract.mjs';
const kind = process.argv[1].includes('quiet') ? 'quiet-nav' : process.argv[1].includes('webkit-fixture') ? 'webkit-layout' : 'resize';
const engine = process.argv.includes('--webkit') ? 'webkit' : process.argv.includes('--firefox') ? 'firefox' : 'chromium';
const stage = kind === 'webkit-layout' ? kind : `${kind}-${engine}`;
appendFileSync(process.env.RESIZE_TRACE, stage + '\n');
if (kind === 'webkit-layout' && process.env.GITHUB_ACTIONS === 'true') throw new Error('retained WebKit must reuse prepared dependencies');
const fault = process.env.RESIZE_STAGE === stage ? process.env.RESIZE_FAULT : '';
if (fault === 'signal') process.kill(process.pid, 'SIGTERM');
if (fault === 'empty') process.exit(0);
let lines = [], count = 0;
if (kind === 'webkit-layout') {
  lines = ['disclosures','layout'].map(name => `INFO Peek WebKit suite ${name}: child_exit=0; effective_exit=0; 5 passed, 0 failed; stdout=/fixture/${name}.out; stderr=/fixture/${name}.err`);
  lines.push('INFO Peek WebKit supplemental verification: PASSED, 2/2 suites, 10 passed, 0 failed; complete logs: /fixture');
} else {
  const resize = kind === 'resize', cases = resize ? resizeCases() : quietCases();
  const labels = resize ? RESIZE_LABELS : ['geometry', 'identity', 'no-errors'];
  for (const item of cases) {
    for (const label of labels) lines.push(`PASS ${resize ? 'title-resize' : 'quiet-nav'} ${engine}/${item}/${label} [check ${++count}]`);
    lines.push(`INFO ${resize ? 'title-resize' : 'quiet-nav'} completed ${engine}/${item}`);
  }
  for (const filename of resize ? resizeCaptures(engine) : quietCaptures(engine)) lines.push(`INLINE ${filename} base64:/9j/2Q==`);
  lines.push(`INFO Peek ${resize ? 'title resize' : 'quiet nav'} ${engine}: ${count} passed, 0 failed; ${cases.length}/${cases.length} cases, ${cases.length} page loads, ${cases.length * 2} captures, 1s; ${resize ? RESIZE_LIMITATION : QUIET_LIMITATION}`);
}
if (fault === 'missing-summary') lines.pop();
if (fault === 'duplicate-summary') lines.push(lines.at(-1));
if (fault === 'wrong-summary') lines[lines.length - 1] = lines.at(-1).replace('passed', 'checks');
if (fault === 'wrong-count') lines[lines.length - 1] = lines.at(-1).replace(/\d+ passed/, '999 passed');
if (fault === 'failed-assertion') { lines[0] = lines[0].replace('PASS ', 'FAIL '); lines[lines.length - 1] = lines.at(-1).replace(`${count} passed, 0 failed`, `${count - 1} passed, 1 failed`); }
if (fault === 'duplicate-label') lines[1] = lines[0].replace('[check 1]', '[check 2]');
if (fault === 'wrong-check-number') lines[0] = lines[0].replace('[check 1]', '[check 99]');
if (fault === 'wrong-case') lines[0] = lines[0].replace('therapeutic-massage/right', 'unknown/right');
if (fault === 'missing-completion') lines = lines.filter(line => !line.startsWith('INFO title-resize completed')).concat();
if (fault === 'missing-capture') lines.splice(lines.findIndex(line => line.startsWith('INLINE ')), 1);
if (fault === 'bad-capture') lines = lines.map(line => line.startsWith('INLINE ') ? line.replace('/9j/2Q==', 'YQ==') : line);
if (fault === 'missing-phase') lines.shift();
if (fault === 'duplicate-phase') lines.splice(1, 0, lines[0]);
if (fault === 'phase-failure') lines[0] = lines[0].replace('effective_exit=0', 'effective_exit=1');
if (fault === 'large-logs') console.log('INFO padding ' + 'x'.repeat(700000));
for (const line of lines) console.log(line);
if (fault === 'trailing-fail') console.error('FAIL trailing checker failure');
if (fault === 'trailing-invalid') console.error('INVALID trailing checker failure');
if (fault === 'large-logs') console.error('INFO trailing stderr ' + 'z'.repeat(700000));
process.exitCode = fault === 'exit-nonzero' ? 7 : 0;
'''

RETAINED = r'''
import { appendFileSync } from 'node:fs';
appendFileSync(process.env.RESIZE_TRACE, 'retained-quiet-nav\n');
if (process.env.GITHUB_ACTIONS === 'true') throw new Error('retained chain must reuse prepared dependencies');
const fault = process.env.RESIZE_STAGE === 'retained-quiet-nav' ? process.env.RESIZE_FAULT : '';
let lines = ['retained-index-clearance','quiet-nav-chromium','quiet-nav-firefox','quiet-nav-webkit'].map(name => `INFO core-quiet-nav rehearsal phase ${name}: child_exit=0; signal=none; effective_exit=0; 5 passed, 0 failed; captures=0; stdout=/fixture/${name}.out; stderr=/fixture/${name}.err`);
lines.push('INFO core-quiet-nav rehearsal: PASSED, 4/4 phases, 24/24 leaves; 20 passed, 0 failed; 1s; complete logs: /fixture');
if (fault === 'missing-summary') lines.pop();
if (fault === 'duplicate-summary') lines.push(lines.at(-1));
if (fault === 'missing-phase') lines.shift();
if (fault === 'duplicate-phase') lines.splice(1, 0, lines[0]);
if (fault === 'phase-failure') lines[0] = lines[0].replace('effective_exit=0', 'effective_exit=1');
if (fault === 'wrong-count') lines[lines.length - 1] = lines.at(-1).replace('20 passed', '21 passed');
if (fault === 'wrong-leaves') lines[lines.length - 1] = lines.at(-1).replace('24/24 leaves', '23/24 leaves');
for (const line of lines) console.log(line);
if (fault === 'trailing-fail') console.error('FAIL trailing retained failure');
process.exitCode = fault === 'exit-nonzero' ? 7 : 0;
'''

with tempfile.TemporaryDirectory(prefix='peek-title-resize-gates-') as directory:
    root = Path(directory)
    for relative in ['core-title-resize/check-rehearsal.mjs', 'core-title-resize/check-public.mjs', 'core-title-resize/gates.mjs', 'core-title-resize/contract.mjs',
                     'peek-release/focused-evidence.mjs', 'core-quiet-nav/contract.mjs', 'core-index-clearance/contract.mjs']:
        target = root / relative
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(SOURCE / relative, target)
    (root / 'check-peek-resize.mjs').write_text(CHILD)
    (root / 'check-peek-quiet-nav.mjs').write_text(CHILD)
    (root / 'webkit-fixture.mjs').write_text(CHILD)
    (root / 'check-peek-webkit.sh').write_text('exec node "$(dirname "$0")/webkit-fixture.mjs" "$@"\n')
    (root / 'core-quiet-nav/check-rehearsal.mjs').write_text(RETAINED)
    package = root / 'node_modules/playwright'
    package.mkdir(parents=True)
    (package / 'package.json').write_text(json.dumps({'name': 'playwright', 'version': '1.63.0'}))
    (package / 'cli.js').write_text("require('node:fs').appendFileSync(process.env.RESIZE_TRACE, 'prepare:' + process.argv.slice(2).join(' ') + '\\n'); process.exitCode = process.env.RESIZE_INSTALL_FAIL ? 3 : 0;\n")
    (root / 'package.json').write_text('{}')
    launcher = root / 'launch.mjs'
    launcher.write_text("import { appendFileSync } from 'node:fs';\nconst [script,...args] = process.argv.slice(2);\nprocess.getuid=()=>0;\nglobalThis.setTimeout=(fn,ms)=>{appendFileSync(process.env.RESIZE_TRACE,'delay:'+ms+'\\n');queueMicrotask(fn);};\nprocess.argv=[process.execPath,script,...args];\nawait import(script);\n")

    def run(mode='rehearsal', stage='', fault='', expected=0, env_update=None, args=None):
        global PASSED
        trace = root / 'trace'
        trace.write_text('')
        env = {k: v for k, v in os.environ.items() if not k.startswith(('TS_PEEK_', 'RESIZE_'))}
        logs = root / f'logs-{PASSED}'
        env.update(RUNNER_TEMP=str(logs), TS_PLAYWRIGHT_ROOT=str(root / 'package.json'), GITHUB_ACTIONS='false',
                   RESIZE_TRACE=str(trace), RESIZE_STAGE=stage, RESIZE_FAULT=fault)
        env.pop('S_TARGET', None)
        env.update(env_update or {})
        base = 'http://localhost:8090' if mode == 'rehearsal' else 'https://staging39.thaisolitude.com'
        result = subprocess.run(['node', str(launcher), str(root / f'core-title-resize/check-{mode}.mjs'), *(args if args is not None else [base])], cwd=root, env=env, capture_output=True, text=True, timeout=20)
        assert result.returncode == expected, (mode, stage, fault, expected, result.returncode, result.stdout[-2500:], result.stderr)
        calls = trace.read_text().splitlines()
        browser_calls = [row for row in calls if not row.startswith(('delay:', 'prepare:'))]
        sequence = ['resize-chromium', 'resize-firefox', 'resize-webkit'] + (['webkit-layout', 'retained-quiet-nav'] if mode == 'rehearsal' else ['quiet-nav-chromium'])
        if stage and expected:
            assert browser_calls == sequence[:sequence.index(stage) + 1], (stage, calls)
        elif not expected:
            assert browser_calls == sequence, calls
        else:
            assert not browser_calls, calls
        if not expected:
            evidence = list(logs.glob('ops-out/core-title-resize-*/results.json'))
            assert len(evidence) == 1
            summary = json.loads(evidence[0].read_text())
            assert summary['code'] == 0 and len(summary['results']) == len(sequence)
            assert summary['counts'] == {'passed': 174 if mode == 'rehearsal' else 198, 'failed': 0}
            for item in summary['results']:
                assert Path(item['logs']['stdout']).is_file() and Path(item['logs']['stderr']).is_file()
            if fault == 'large-logs':
                item = summary['results'][0]
                assert Path(item['logs']['stdout']).stat().st_size > 700000 and Path(item['logs']['stderr']).stat().st_size > 700000
            if mode == 'public':
                assert calls.count('delay:5500') == len(sequence) - 1
            if env.get('GITHUB_ACTIONS') == 'true':
                assert calls[:2] == ['prepare:install-deps webkit', 'prepare:install webkit'], calls
        PASSED += 1

    run()
    run('public')
    run(stage='resize-chromium', fault='large-logs')
    run(env_update={'GITHUB_ACTIONS': 'true'})
    run(expected=2, env_update={'GITHUB_ACTIONS': 'true', 'RESIZE_INSTALL_FAIL': '1'})
    for fault in ['missing-summary', 'duplicate-summary', 'wrong-summary', 'wrong-count', 'duplicate-label', 'wrong-check-number', 'wrong-case', 'missing-completion', 'missing-capture', 'bad-capture', 'trailing-invalid', 'empty', 'signal', 'exit-nonzero']:
        run(stage='resize-firefox', fault=fault, expected=2)
    for fault in ['failed-assertion', 'trailing-fail']:
        run(stage='resize-webkit', fault=fault, expected=1)
    for stage in ['webkit-layout', 'retained-quiet-nav']:
        for fault in ['missing-summary', 'duplicate-summary', 'missing-phase', 'duplicate-phase', 'phase-failure', 'wrong-count', 'exit-nonzero']:
            run(stage=stage, fault=fault, expected=2)
        run(stage=stage, fault='trailing-fail', expected=1)
    run(stage='retained-quiet-nav', fault='wrong-leaves', expected=2)
    run('public', 'quiet-nav-chromium', 'missing-summary', 2)
    run('public', 'quiet-nav-chromium', 'trailing-fail', 1)
    for mode in ['rehearsal', 'public']:
        for args in [[], ['https://thaisolitude.com'], ['http://localhost:8091'], ['http://localhost:8090', '--extra']]:
            run(mode, expected=2, args=args)
        for change in [{'S_TARGET': 'live'}, {'TS_PEEK_REHEARSAL_PROFILE': 'visitor-release-v1'}, {'TS_PLAYWRIGHT_ROOT': '/__missing_peek_resize_fixture__/package.json'}]:
            run(mode, expected=2, env_update=change)
    (package / 'package.json').write_text(json.dumps({'name': 'playwright', 'version': '0.0.0'}))
    run(expected=2)
    (package / 'package.json').write_text(json.dumps({'name': 'playwright', 'version': '1.63.0'}))
    blocked = root / 'not-a-directory'
    blocked.write_text('fixture')
    run(expected=2, env_update={'RUNNER_TEMP': str(blocked)})

print(f'Title-resize adapter: {PASSED} offline matrix, stop, origin, dependency and retained-evidence controls passed; no browser/site accessed.')

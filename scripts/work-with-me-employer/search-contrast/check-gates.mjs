// Retain complete per-engine logs and reject partial matrices or missing images.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, mkdtempSync, openSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import { parseSearchContrast } from './check-evidence.mjs';

export async function runSearchContrast(base, mode) {
  if (!['rehearsal', 'public'].includes(mode) || base !== (mode === 'rehearsal' ? 'http://localhost:8090' : 'https://staging39.thaisolitude.com') || (process.env.S_TARGET && process.env.S_TARGET !== 'staging39')) {
    console.log('INVALID Search contrast gates: exact staging39 or its isolated localhost rehearsal only.'); return 2;
  }
  const started = Date.now();
  const parent = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out'); mkdirSync(parent, { recursive: true });
  const out = mkdtempSync(join(parent, `search-contrast-${mode}-gates-`)), results = [];
  for (const engine of ['chromium', 'firefox']) {
    const stdout = join(out, `${engine}.stdout.log`), stderr = join(out, `${engine}.stderr.log`);
    const fds = [openSync(stdout, 'wx', 0o600), openSync(stderr, 'wx', 0o600)];
    const processResult = await new Promise(resolve => {
      let launchError = null;
      const child = spawn(process.execPath, [fileURLToPath(new URL('./check-search-contrast.mjs', import.meta.url)), base, ...(engine === 'firefox' ? ['--firefox'] : [])], { stdio: ['ignore', ...fds], env: process.env });
      child.on('error', error => { launchError = error.message; });
      child.on('close', (exitCode, signal) => { for (const fd of fds) closeSync(fd); resolve({ exitCode, signal, launchError }); });
    });
    const raw = readFileSync(stdout, 'utf8'), err = readFileSync(stderr, 'utf8');
    for (const line of raw.split(/\r?\n/)) if (line && !line.startsWith('PASS ')) process.stdout.write(line + '\n');
    const evidence = parseSearchContrast(raw, err, engine), { passed, failed, invalid } = evidence;
    if (processResult.signal || processResult.launchError) invalid.push(`browser process problem: ${processResult.signal || processResult.launchError}`);
    const code = failed ? 1 : processResult.exitCode !== 0 || invalid.length ? 2 : 0;
    results.push({ engine, ...processResult, code, passed, failed, cases: evidence.cases, captures: evidence.captures, invalid, stdout, stderr });
    for (const reason of invalid) console.log(`INVALID Search contrast ${engine} evidence: ${reason}`);
    console.log(`INFO Search contrast ${mode} engine ${engine}: child_exit=${processResult.exitCode}; signal=${processResult.signal || 'none'}; effective_exit=${code}; ${passed} passed, ${failed} failed; cases=${evidence.cases}; captures=${evidence.captures}; stdout=${stdout}; stderr=${stderr}`);
    if (code) break;
  }
  const total = results.reduce((a, r) => ({ passed: a.passed + r.passed, failed: a.failed + r.failed }), { passed: 0, failed: 0 });
  const code = results.some(r => r.code === 1) ? 1 : results.length !== 2 || results.some(r => r.code !== 0) ? 2 : 0;
  writeFileSync(join(out, 'results.json'), JSON.stringify({ base, mode, code, total, results }, null, 2) + '\n', { flag: 'wx', mode: 0o600 });
  console.log(`INFO Search contrast ${mode}: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length}/2 engines; ${total.passed} passed, ${total.failed} failed; ${Math.round((Date.now() - started) / 1000)}s; complete logs ${out}`);
  return code;
}

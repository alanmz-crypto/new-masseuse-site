// Read-only staging39 gates. Keep complete per-suite streams in the Actions
// artifact and repeat every verdict/count/log path after the matched captures.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, openSync, writeSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'https://staging39.thaisolitude.com' || process.argv.length !== 3) throw new Error('exact staging39 origin only; no extra arguments');
const started = Date.now();
const logDir = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out', 'peek-public');
mkdirSync(logDir, { recursive: true });
const run = (name, file, flags = []) => new Promise(resolve => {
  let summary = '', counts = null, failed = false, launchError = '', logError = '';
  const invalid = [];
  const logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) };
  const descriptors = { stdout: openSync(logs.stdout, 'w', 0o600), stderr: openSync(logs.stderr, 'w', 0o600) };
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
  for (const [channel, stream] of [['stdout', child.stdout], ['stderr', child.stderr]]) {
    // Preserve raw bytes (including final lines without LF) in separate streams.
    stream.on('data', chunk => {
      try {
        let offset = 0;
        while (offset < chunk.length) {
          const written = writeSync(descriptors[channel], chunk, offset, chunk.length - offset);
          if (written === 0) throw new Error('log write made no progress');
          offset += written;
        }
      }
      catch (error) { logError = error.message; }
    });
    createInterface({ input: stream }).on('line', line => {
      // No prefixes: FAIL/INVALID/INFO and INLINE captures retain report syntax.
      if (!/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
      const match = /^INFO Peek .+: (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
      if (match) { summary = line; counts = { passed: Number(match[1]), failed: Number(match[2]) }; }
      if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
      if (/^FAIL(?:\s|$)/.test(line)) failed = true;
    });
  }
  child.on('error', error => { launchError = error.message; });
  child.on('close', (exitCode, signal) => {
    for (const fd of Object.values(descriptors)) {
      try { closeSync(fd); } catch (error) { logError = error.message; }
    }
    if (launchError) invalid.push(`INVALID ${name}: launch failed: ${launchError}`);
    if (signal) invalid.push(`INVALID ${name}: terminated by ${signal}`);
    if (logError) invalid.push(`INVALID ${name}: complete log could not be retained: ${logError}`);
    if (!summary) invalid.push(`INVALID ${name}: no final suite summary`);
    // Never replace a real nonzero child exit with a summary/transport diagnosis.
    // A nominal exit 0 without its final counts cannot establish a valid run.
    const code = exitCode !== null && exitCode !== 0 ? exitCode
      : (launchError || signal || logError || !summary) ? 2
      : (failed || counts.failed > 0) ? 1
      : invalid.length ? 2 : 0;
    resolve({ name, code, exitCode, signal, summary, counts, invalid, logs });
  });
});
const results = await Promise.all([
  run('clearance-chromium', '../check-peek-clearance.mjs'),
  run('clearance-firefox', '../check-peek-clearance.mjs', ['--firefox']),
  run('native-touch-chromium', '../check-peek-touch.mjs'),
  run('pages-chromium', '../check-peek-pages.mjs')
]);
if (results.every(result => result.code === 0)) {
  // Existing diagnostic subset only, for matched Thai/Chair captures.
  results.push(await run('matched-captures-chromium', '../check-peek-clearance.mjs', ['--quick']));
} else console.log('INFO public matched captures skipped because a required matrix failed or was invalid.');
for (const result of results) {
  const verdict = result.code === 0 ? 'PASSED' : result.code === 2 ? 'INVALID' : 'FAILED';
  const counts = result.counts ? `${result.counts.passed} passed, ${result.counts.failed} failed` : 'counts unavailable';
  console.log(`INFO public suite ${result.name}: ${verdict}; child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${counts}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
  if (result.summary) console.log(result.summary);
  result.invalid.forEach(line => console.log(line));
}
// A failed suite must remain a failure even when a different suite is INVALID.
const code = results.some(result => result.code === 1) ? 1
  : (results.find(result => result.code !== 0 && result.code !== 2)?.code
    || (results.some(result => result.code === 2) ? 2 : 0));
console.log(`INFO Peek core-upgrade public verification: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} suites, ${Math.round((Date.now() - started) / 1000)}s; read-only staging39 browser checks; complete suite logs: ${logDir}`);
process.exit(code);

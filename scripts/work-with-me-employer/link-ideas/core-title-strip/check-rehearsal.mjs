// Title matrices first; then reuse the retained core-upgrade rehearsal alone so
// its timing-sensitive Thai gesture suite has no competing title browser jobs.
// The calling rehearsal keeps the package's bounded 900-second timeout.
import { spawn } from 'node:child_process';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'http://localhost:8090' || process.argv.length !== 3) throw new Error('isolated localhost:8090 rehearsal only; no extra arguments');
const started = Date.now();
const run = (name, file, flags = [], retained = false) => new Promise(resolve => {
  const summaries = new Map(), invalid = [];
  let complete = false, failed = false, launchError = '';
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
  for (const stream of [child.stdout, child.stderr]) {
    createInterface({ input: stream }).on('line', line => {
      process.stdout.write(line + '\n');
      const match = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
      if (match) { summaries.set(match[1], line); failed ||= Number(match[3]) > 0; }
      const thai = retained && /^chromium: (\d+) passed, (\d+) failed$/.exec(line);
      if (thai) { summaries.set('INFO Peek retained Thai chromium', `INFO Peek retained Thai chromium: ${thai[1]} passed, ${thai[2]} failed`); failed ||= Number(thai[2]) > 0; }
      if (retained && /^INFO Peek core-upgrade rehearsal: .* checks passed in \d+s\.$/.test(line)) complete = true;
      if (!retained && match && match[1] === `INFO Peek title strip ${name.slice(6)}`) complete = true;
      if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
      if (/^FAIL(?:\s|$)/.test(line)) failed = true;
    });
  }
  child.on('error', error => { launchError = error.message; });
  child.on('close', (exitCode, signal) => {
    if (launchError) invalid.push(`INVALID ${name}: launch failed: ${launchError}`);
    if (signal) invalid.push(`INVALID ${name}: terminated by ${signal}`);
    if (!complete) invalid.push(`INVALID ${name}: no final suite summary`);
    // Keep real nonzero exits; a nominal success without its final summary is invalid.
    const code = exitCode !== null && exitCode !== 0 ? exitCode
      : (launchError || signal || !complete) ? 2 : failed ? 1 : invalid.length ? 2 : 0;
    resolve({ name, code, exitCode, signal, summaries, invalid, failed });
  });
});
const results = await Promise.all([
  run('title-chromium', '../check-title-strip.mjs', ['--fixtures']),
  run('title-firefox', '../check-title-strip.mjs', ['--fixtures', '--firefox'])
]);
if (results.every(result => result.code === 0)) {
  results.push(await run('retained-core', '../core-upgrade/check-rehearsal.mjs', [], true));
} else console.log('INFO retained core-upgrade rehearsal skipped because a title matrix failed or was invalid.');
console.log('INFO Peek title-strip rehearsal final suite results:');
for (const result of results) {
  console.log(`INFO title-strip rehearsal suite ${result.name}: child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}`);
  for (const summary of result.summaries.values()) console.log(summary);
  result.invalid.forEach(line => console.log(line));
}
// A failure observed in the retained wrapper must not disappear behind that
// older wrapper's first-nonzero exit choice if another nested matrix was invalid.
const code = results.some(result => result.code === 1 || result.failed) ? 1
  : (results.find(result => result.code !== 0 && result.code !== 2)?.code || (results.some(result => result.code === 2) ? 2 : 0));
console.log(`INFO Peek title-strip rehearsal: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} wrapper suites, ${Math.round((Date.now() - started) / 1000)}s; retained core-upgrade gates reused without edits.`);
process.exit(code);

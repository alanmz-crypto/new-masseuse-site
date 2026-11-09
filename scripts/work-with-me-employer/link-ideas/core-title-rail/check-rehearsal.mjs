// Add title-only rail gates, then retain the complete title-strip/core rehearsal.
// Sequential phases keep native/Thai timing checks free from extra browser jobs.
// scripts/ci/rehearse.sh gives this package alone a bounded 1500-second budget.
import { spawn } from 'node:child_process';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'http://localhost:8090' || process.argv.length !== 3) throw new Error('isolated localhost:8090 rehearsal only; no extra arguments');
const started = Date.now();
const run = (name, file, flags = [], retained = false) => new Promise(resolve => {
  const summaries = new Map(), verdicts = new Map(), invalid = [];
  let complete = false, failed = false, launchError = '';
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
  for (const stream of [child.stdout, child.stderr]) createInterface({ input: stream }).on('line', line => {
    process.stdout.write(line + '\n');
    const numeric = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
    if (numeric) { summaries.set(numeric[1], line); failed ||= Number(numeric[3]) > 0; }
    if (retained) {
      const verdict = /^INFO title-strip rehearsal suite ([^:]+):/.exec(line);
      if (verdict) { verdicts.set(verdict[1], line); failed ||= /effective_exit=1(?:;|$)/.test(line); }
      if (/^INFO Peek title-strip rehearsal: (PASSED|FAILED|INVALID), 3 wrapper suites,/.test(line)) complete = true;
    } else if (numeric?.[1] === `INFO Peek title rails ${name.slice(6)}`) complete = true;
    if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
    if (/^FAIL(?:\s|$)/.test(line)) failed = true;
  });
  child.on('error', error => { launchError = error.message; });
  child.on('close', (exitCode, signal) => {
    if (launchError) invalid.push(`INVALID ${name}: launch failed: ${launchError}`);
    if (signal) invalid.push(`INVALID ${name}: terminated by ${signal}`);
    if (retained && (verdicts.size !== 3 || summaries.size < 8)) complete = false;
    if (!complete) invalid.push(`INVALID ${name}: complete final suite results missing`);
    const code = failed || exitCode === 1 ? 1 : exitCode !== null && exitCode !== 0 ? exitCode : launchError || signal || !complete || invalid.length ? 2 : 0;
    resolve({ name, code, exitCode, signal, summaries, verdicts, invalid });
  });
});
const results = await Promise.all([
  run('rails-chromium', '../check-peek-title-rails.mjs', ['--fixtures']),
  run('rails-firefox', '../check-peek-title-rails.mjs', ['--fixtures', '--firefox'])
]);
if (results.every(result => result.code === 0)) results.push(await run('retained-title-core', '../core-title-strip/check-rehearsal.mjs', [], true));
else console.log('INFO retained title-strip/core rehearsal skipped because a title-rail matrix failed or was invalid.');
console.log('INFO Peek title-rail rehearsal final suite results:');
for (const result of results) {
  console.log(`INFO title-rail rehearsal suite ${result.name}: child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}`);
  for (const line of result.verdicts.values()) console.log(line);
  for (const line of result.summaries.values()) console.log(line);
  result.invalid.forEach(line => console.log(line));
}
const code = results.some(result => result.code === 1) ? 1 : results.find(result => result.code !== 0 && result.code !== 2)?.code || (results.some(result => result.code === 2) ? 2 : 0);
console.log(`INFO Peek title-rail rehearsal: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} wrapper suites, ${Math.round((Date.now() - started) / 1000)}s; complete retained title-strip/core gates reused.`);
process.exit(code);

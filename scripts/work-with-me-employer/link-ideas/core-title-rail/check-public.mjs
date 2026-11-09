// Read-only staging39 title rails, then the complete retained title/core gates.
// Preserve every child stream and replay all numeric/exit results at the end.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, openSync, writeSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'https://staging39.thaisolitude.com' || process.argv.length !== 3) throw new Error('exact staging39 origin only; no extra arguments');
const started = Date.now(), logDir = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out', 'peek-title-rail-public');
try { mkdirSync(logDir, { recursive: true }); }
catch (error) { console.log(`INVALID title-rail public: cannot retain complete logs: ${error.message}`); process.exit(2); }
const run = (name, file, flags = [], retained = false) => new Promise(resolve => {
  const summaries = new Map(), nested = new Map(), invalid = [];
  let aggregate = '', failed = false, launchError = '', logError = '';
  const logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) }, descriptors = {};
  try { for (const channel of ['stdout', 'stderr']) descriptors[channel] = openSync(logs[channel], 'w', 0o600); }
  catch (error) {
    for (const fd of Object.values(descriptors)) { try { closeSync(fd); } catch {} }
    resolve({ name, code: 2, exitCode: null, signal: null, summaries, nested, aggregate, logs, invalid: [`INVALID ${name}: cannot create complete logs: ${error.message}`] }); return;
  }
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
  for (const [channel, stream] of [['stdout', child.stdout], ['stderr', child.stderr]]) {
    stream.on('data', chunk => {
      try {
        let offset = 0;
        while (offset < chunk.length) { const n = writeSync(descriptors[channel], chunk, offset, chunk.length - offset); if (!n) throw new Error('log write made no progress'); offset += n; }
      } catch (error) { logError = error.message; }
    });
    createInterface({ input: stream }).on('line', line => {
      if (!/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
      const numeric = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
      if (numeric) { summaries.set(numeric[1], { line, passed: Number(numeric[2]), failed: Number(numeric[3]) }); failed ||= Number(numeric[3]) > 0; }
      if (retained) {
        const verdict = /^INFO (title-strip public wrapper|public suite) ([^:]+):/.exec(line);
        if (verdict) { nested.set(verdict[1] + '/' + verdict[2], line); failed ||= /effective_exit=1(?:;|$)/.test(line); }
        if (/^INFO Peek title-strip public verification: (PASSED|FAILED|INVALID),/.test(line)) { aggregate = line; failed ||= aggregate.includes(': FAILED,'); }
      }
      if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
      if (/^FAIL(?:\s|$)/.test(line)) failed = true;
    });
  }
  child.on('error', error => { launchError = error.message; });
  child.on('close', (exitCode, signal) => {
    for (const fd of Object.values(descriptors)) { try { closeSync(fd); } catch (error) { logError = error.message; } }
    if (launchError) invalid.push(`INVALID ${name}: launch failed: ${launchError}`);
    if (signal) invalid.push(`INVALID ${name}: terminated by ${signal}`);
    if (logError) invalid.push(`INVALID ${name}: complete log could not be retained: ${logError}`);
    const complete = retained
      ? /^INFO Peek title-strip public verification: (PASSED|FAILED|INVALID), 3 wrapper suites,/.test(aggregate) && nested.size === 8 && summaries.size === 7 && [...nested.values()].every(line => /; \d+ passed, \d+ failed(?: across \d+ reported suites)?; stdout=.+; stderr=.+$/.test(line))
      : summaries.size === 1 && summaries.has(`INFO Peek title rails ${name.slice(6)}`);
    if (!complete) invalid.push(`INVALID ${name}: complete final nested results missing`);
    const code = failed || exitCode === 1 ? 1 : exitCode !== null && exitCode !== 0 ? exitCode : launchError || signal || logError || !complete || invalid.length ? 2 : 0;
    resolve({ name, code, exitCode, signal, summaries, nested, aggregate, logs, invalid });
  });
});
const results = await Promise.all([
  run('rails-chromium', '../check-peek-title-rails.mjs'),
  run('rails-firefox', '../check-peek-title-rails.mjs', ['--firefox'])
]);
if (results.every(result => result.code === 0)) results.push(await run('retained-title-core', '../core-title-strip/check-public.mjs', [], true));
else console.log('INFO retained title-strip/core public gates skipped because a title-rail matrix failed or was invalid.');
console.log('INFO Peek title-rail public final suite results:');
for (const result of results) {
  const counts = [...result.summaries.values()].reduce((total, item) => ({ passed: total.passed + item.passed, failed: total.failed + item.failed }), { passed: 0, failed: 0 });
  console.log(`INFO title-rail public wrapper ${result.name}: child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${result.summaries.size ? `${counts.passed} passed, ${counts.failed} failed across ${result.summaries.size} reported suites` : 'counts unavailable'}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
  for (const line of result.nested.values()) console.log(line);
  for (const item of result.summaries.values()) console.log(item.line);
  if (result.aggregate) console.log(result.aggregate);
  result.invalid.forEach(line => console.log(line));
}
const code = results.some(result => result.code === 1) ? 1 : results.find(result => result.code !== 0 && result.code !== 2)?.code || (results.some(result => result.code === 2) ? 2 : 0);
console.log(`INFO Peek title-rail public verification: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} wrapper suites, ${Math.round((Date.now() - started) / 1000)}s; read-only staging39; complete wrapper logs: ${logDir}`);
process.exit(code);

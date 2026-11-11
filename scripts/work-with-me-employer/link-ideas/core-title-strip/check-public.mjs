// Read-only staging39 title matrices alongside the retained public core gates.
// Replay all nested numeric results at the end and retain complete child streams.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, openSync, writeSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'https://staging39.thaisolitude.com' || process.argv.length !== 3) throw new Error('exact staging39 origin only; no extra arguments');
const started = Date.now();
const logDir = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out', 'peek-title-public');
mkdirSync(logDir, { recursive: true });
const run = (name, file, flags = [], retained = false) => new Promise(resolve => {
  const summaries = new Map(), nested = new Map(), invalid = [];
  let aggregate = '', declaredSuites = null, failed = false, launchError = '', logError = '';
  const logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) };
  const descriptors = { stdout: openSync(logs.stdout, 'w', 0o600), stderr: openSync(logs.stderr, 'w', 0o600) };
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
  for (const [channel, stream] of [['stdout', child.stdout], ['stderr', child.stderr]]) {
    stream.on('data', chunk => {
      try {
        let offset = 0;
        while (offset < chunk.length) {
          const written = writeSync(descriptors[channel], chunk, offset, chunk.length - offset);
          if (written === 0) throw new Error('log write made no progress');
          offset += written;
        }
      } catch (error) { logError = error.message; }
    });
    createInterface({ input: stream }).on('line', line => {
      if (!/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
      const numeric = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
      if (numeric) {
        summaries.set(numeric[1], { line, passed: Number(numeric[2]), failed: Number(numeric[3]) });
        failed ||= Number(numeric[3]) > 0;
      }
      if (retained) {
        const suite = /^INFO public suite ([^:]+):/.exec(line);
        if (suite) { nested.set(suite[1], line); failed ||= /; effective_exit=1;/.test(line); }
        const finish = /^INFO Peek core-upgrade public verification: (PASSED|FAILED|INVALID), (\d+) suites,/.exec(line);
        if (finish) { aggregate = line; declaredSuites = Number(finish[2]); failed ||= finish[1] === 'FAILED'; }
      }
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
    const complete = retained
      ? !!aggregate && declaredSuites > 0 && nested.size === declaredSuites
        && [...nested.values()].every(line => /; \d+ passed, \d+ failed; stdout=.+; stderr=.+$/.test(line))
        && summaries.size >= declaredSuites
      : summaries.size === 1 && summaries.has(`INFO Peek title strip ${name.slice(6)}`);
    if (!complete) invalid.push(`INVALID ${name}: final suite summary or nested numeric results missing`);
    const code = exitCode !== null && exitCode !== 0 ? exitCode
      : (launchError || signal || logError || !complete) ? 2 : failed ? 1 : invalid.length ? 2 : 0;
    resolve({ name, code, exitCode, signal, summaries, nested, aggregate, invalid, logs, failed });
  });
});
const results = await Promise.all([
  run('retained-core', '../core-upgrade/check-public.mjs', [], true),
  run('title-chromium', '../check-title-strip.mjs'),
  run('title-firefox', '../check-title-strip.mjs', ['--firefox'])
]);
console.log('INFO Peek title-strip public final suite results:');
for (const result of results) {
  const counts = [...result.summaries.values()].reduce((total, summary) => ({ passed: total.passed + summary.passed, failed: total.failed + summary.failed }), { passed: 0, failed: 0 });
  const numeric = result.summaries.size ? `${counts.passed} passed, ${counts.failed} failed across ${result.summaries.size} reported suites` : 'counts unavailable';
  console.log(`INFO title-strip public wrapper ${result.name}: child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${numeric}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
  // These retain each old suite's real exit, counts, and original artifact paths;
  // the old wrapper still owns its suite map and all ten original stream logs.
  for (const line of result.nested.values()) console.log(line);
  for (const summary of result.summaries.values()) console.log(summary.line);
  if (result.aggregate) console.log(result.aggregate);
  result.invalid.forEach(line => console.log(line));
}
const code = results.some(result => result.code === 1 || result.failed) ? 1
  : (results.find(result => result.code !== 0 && result.code !== 2)?.code || (results.some(result => result.code === 2) ? 2 : 0));
console.log(`INFO Peek title-strip public verification: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} wrapper suites, ${Math.round((Date.now() - started) / 1000)}s; read-only staging39; complete wrapper logs: ${logDir}`);
process.exit(code);

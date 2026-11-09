// New input packages only: preserve complete child evidence and stop on bad gates.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, openSync, writeSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';

export async function runInputGates(base, mode, group) {
  if (!['public', 'rehearsal'].includes(mode) || !['core-input-focus', 'rail-input'].includes(group) || base !== (mode === 'public' ? 'https://staging39.thaisolitude.com' : 'http://localhost:8090')) {
    console.log('INVALID input gates: unsupported origin, mode or package.'); return 2;
  }
  const started = Date.now(), logDir = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out', `${group}-${mode}`);
  try { mkdirSync(logDir, { recursive: true }); }
  catch (error) { console.log(`INVALID ${group} ${mode}: cannot retain complete logs: ${error.message}`); return 2; }
  const retainedNames = ['disclosures-chromium', 'disclosures-firefox', 'layout-chromium', 'layout-firefox', 'retained-title-rails'];
  const run = spec => new Promise(resolve => {
    const summaries = new Map(), nested = new Map(), invalid = [];
    let aggregate = '', failed = false, launchError = '', logError = '';
    const logs = { stdout: join(logDir, `${spec.name}.stdout.log`), stderr: join(logDir, `${spec.name}.stderr.log`) }, descriptors = {};
    try { for (const channel of ['stdout', 'stderr']) descriptors[channel] = openSync(logs[channel], 'w', 0o600); }
    catch (error) {
      for (const fd of Object.values(descriptors)) { try { closeSync(fd); } catch {} }
      resolve({ ...spec, code: 2, exitCode: null, signal: null, summaries, nested, aggregate, logs, invalid: [`INVALID ${spec.name}: cannot create complete logs: ${error.message}`] }); return;
    }
    const child = spawn(process.execPath, [fileURLToPath(new URL(spec.file, import.meta.url)), base, ...(spec.flags || [])], { stdio: ['ignore', 'pipe', 'pipe'], env: process.env });
    for (const [channel, stream] of [['stdout', child.stdout], ['stderr', child.stderr]]) {
      stream.on('data', chunk => {
        try {
          let offset = 0;
          while (offset < chunk.length) { const n = writeSync(descriptors[channel], chunk, offset, chunk.length - offset); if (!n) throw new Error('log write made no progress'); offset += n; }
        } catch (error) { logError = error.message; }
      });
      createInterface({ input: stream }).on('line', line => {
        if (!/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
        const numeric = /^(INFO (?:Peek|Rail input) .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
        if (numeric) {
          const previous = summaries.get(numeric[1]), passed = Number(numeric[2]), failedCount = Number(numeric[3]);
          if (previous && (previous.passed !== passed || previous.failed !== failedCount)) invalid.push(`INVALID ${spec.name}: conflicting results for ${numeric[1]}`);
          summaries.set(numeric[1], { line, passed, failed: failedCount }); failed ||= failedCount > 0;
        }
        if (spec.retained) {
          const verdict = new RegExp(`^INFO Kiro fixes ${mode} wrapper ([^:]+):`).exec(line);
          if (verdict) { nested.set(verdict[1], line); failed ||= /effective_exit=1(?:;|$)/.test(line); }
          if (new RegExp(`^INFO Peek Kiro fixes ${mode}: (PASSED|FAILED|INVALID),`).test(line)) {
            aggregate = line; failed ||= aggregate.includes(': FAILED,');
          }
        }
        if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
        if (/^FAIL(?:\s|$)/.test(line)) failed = true;
      });
    }
    child.on('error', error => { launchError = error.message; });
    child.on('close', (exitCode, signal) => {
      for (const fd of Object.values(descriptors)) { try { closeSync(fd); } catch (error) { logError = error.message; } }
      if (launchError) invalid.push(`INVALID ${spec.name}: launch failed: ${launchError}`);
      if (signal) invalid.push(`INVALID ${spec.name}: terminated by ${signal}`);
      if (logError) invalid.push(`INVALID ${spec.name}: complete log could not be retained: ${logError}`);
      const complete = spec.retained
        ? nested.size === retainedNames.length && retainedNames.every(name => /: child_exit=0; signal=none; effective_exit=0;/.test(nested.get(name) || '')) && summaries.size === (mode === 'public' ? 13 : 14) && /: PASSED, 5 wrapper suites,/.test(aggregate)
        : summaries.size === 1 && summaries.has(spec.summary);
      const nonempty = [...summaries.values()].every(row => row.passed + row.failed > 0);
      if (!complete || !nonempty) invalid.push(`INVALID ${spec.name}: complete nonempty final suite results missing`);
      const code = failed ? 1 : exitCode !== 0 || launchError || signal || logError || !complete || !nonempty || invalid.length ? 2 : 0;
      resolve({ ...spec, code, exitCode, signal, summaries, nested, aggregate, logs, invalid });
    });
  });
  const suites = group === 'core-input-focus' ? [
    { name: 'input-focus-chromium', file: './check-peek-input-focus.mjs', summary: 'INFO Peek input focus chromium' },
    { name: 'retained-kiro-fixes', file: `./core-kiro-fixes/check-${mode}.mjs`, retained: true }
  ] : [
    { name: 'keyboard-chromium', file: './rail-input/check-keyboard.mjs', summary: 'INFO Rail input keyboard chromium' },
    { name: 'keyboard-firefox', file: './rail-input/check-keyboard.mjs', flags: ['--firefox'], summary: 'INFO Rail input keyboard firefox' },
    { name: 'native-touch', file: './check-rail-ideas.mjs', summary: 'INFO Rail input native touch' },
    { name: 'title-strip-chromium', file: './check-title-strip.mjs', summary: 'INFO Peek title strip chromium' },
    { name: 'title-strip-firefox', file: './check-title-strip.mjs', flags: ['--firefox'], summary: 'INFO Peek title strip firefox' }
  ], results = [];
  // Each checker paces its own page loads; isolate suites and stop new loads as
  // soon as evidence is bad, including a hosting challenge or missing results.
  for (const spec of suites) {
    const result = await run(spec); results.push(result);
    if (result.code !== 0) { console.log(`INFO remaining ${group} ${mode} gates skipped after ${spec.name}: effective_exit=${result.code}.`); break; }
  }
  console.log(`INFO ${group} ${mode} final suite results:`);
  for (const result of results) {
    const counts = [...result.summaries.values()].reduce((total, row) => ({ passed: total.passed + row.passed, failed: total.failed + row.failed }), { passed: 0, failed: 0 });
    console.log(`INFO ${group} ${mode} wrapper ${result.name}: child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${result.summaries.size ? `${counts.passed} passed, ${counts.failed} failed across ${result.summaries.size} reported suites` : 'counts unavailable'}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
    for (const line of result.nested.values()) console.log(line);
    for (const item of result.summaries.values()) console.log(item.line);
    if (result.aggregate) console.log(result.aggregate);
    result.invalid.forEach(line => console.log(line));
  }
  const code = results.some(result => result.code === 1) ? 1 : results.length !== suites.length || results.some(result => result.code !== 0) ? 2 : 0;
  console.log(`INFO ${group} ${mode}: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} wrapper suites, ${Math.round((Date.now() - started) / 1000)}s; complete logs: ${logDir}`);
  return code;
}

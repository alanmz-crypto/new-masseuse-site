// Retain complete child output and reject empty/incomplete browser evidence.
// Called only by the two exact-origin adapters in this package.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, openSync, writeSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createInterface } from 'node:readline';
import { fileURLToPath } from 'node:url';

export async function runGates(base, mode) {
  if (!['public', 'rehearsal'].includes(mode) || base !== (mode === 'public' ? 'https://staging39.thaisolitude.com' : 'http://localhost:8090')) {
    console.log('INVALID Kiro fixes: unsupported origin or gate mode.'); return 2;
  }
  const started = Date.now(), logDir = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out', `peek-kiro-fixes-${mode}`);
  try { mkdirSync(logDir, { recursive: true }); }
  catch (error) { console.log(`INVALID Kiro fixes ${mode}: cannot retain complete logs: ${error.message}`); return 2; }
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
        const numeric = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
        if (numeric) { summaries.set(numeric[1], { line, passed: Number(numeric[2]), failed: Number(numeric[3]) }); failed ||= Number(numeric[3]) > 0; }
        if (spec.retained) {
          const verdict = (mode === 'public' ? /^INFO title-rail public wrapper ([^:]+):/ : /^INFO title-rail rehearsal suite ([^:]+):/).exec(line);
          if (verdict) { nested.set(verdict[1], line); failed ||= /effective_exit=1(?:;|$)/.test(line); }
          if ((mode === 'public' ? /^INFO Peek title-rail public verification: (PASSED|FAILED|INVALID),/ : /^INFO Peek title-rail rehearsal: (PASSED|FAILED|INVALID),/).test(line)) {
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
        ? nested.size === 3 && summaries.size === (mode === 'public' ? 9 : 10) && /: PASSED, 3 wrapper suites,/.test(aggregate)
        : summaries.size === 1 && summaries.has(spec.summary);
      const nonempty = [...summaries.values()].every(row => row.passed + row.failed > 0);
      if (!complete || !nonempty) invalid.push(`INVALID ${spec.name}: complete nonempty final suite results missing`);
      // A process error without actual failed assertions is invalid evidence.
      const code = failed ? 1 : exitCode !== 0 || launchError || signal || logError || !complete || !nonempty || invalid.length ? 2 : 0;
      resolve({ ...spec, code, exitCode, signal, summaries, nested, aggregate, logs, invalid });
    });
  });
  const suites = [
    { name: 'disclosures-chromium', file: '../check-peek-disclosures.mjs', summary: 'INFO Peek disclosures chromium' },
    { name: 'disclosures-firefox', file: '../check-peek-disclosures.mjs', flags: ['--firefox'], summary: 'INFO Peek disclosures firefox' },
    { name: 'layout-chromium', file: '../check-peek-kiro-layout.mjs', summary: 'INFO Peek Kiro layout chromium' },
    { name: 'layout-firefox', file: '../check-peek-kiro-layout.mjs', flags: ['--firefox'], summary: 'INFO Peek Kiro layout firefox' },
    { name: 'retained-title-rails', file: `../core-title-rail/check-${mode}.mjs`, retained: true }
  ], results = [];
  // Keep timing-sensitive touch checks isolated, and stop new public loads on
  // a failed/invalid gate rather than letting later suites retry a challenge.
  for (const spec of suites) {
    const result = await run(spec); results.push(result);
    if (result.code !== 0) { console.log(`INFO remaining Kiro fixes ${mode} gates skipped after ${spec.name}: effective_exit=${result.code}.`); break; }
  }
  console.log(`INFO Peek Kiro fixes ${mode} final suite results:`);
  for (const result of results) {
    const counts = [...result.summaries.values()].reduce((total, row) => ({ passed: total.passed + row.passed, failed: total.failed + row.failed }), { passed: 0, failed: 0 });
    console.log(`INFO Kiro fixes ${mode} wrapper ${result.name}: child_exit=${result.exitCode}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${result.summaries.size ? `${counts.passed} passed, ${counts.failed} failed across ${result.summaries.size} reported suites` : 'counts unavailable'}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
    for (const line of result.nested.values()) console.log(line);
    for (const item of result.summaries.values()) console.log(item.line);
    if (result.aggregate) console.log(result.aggregate);
    result.invalid.forEach(line => console.log(line));
  }
  const code = results.some(result => result.code === 1) ? 1 : results.length !== suites.length || results.some(result => result.code !== 0) ? 2 : 0;
  console.log(`INFO Peek Kiro fixes ${mode}: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length} wrapper suites, ${Math.round((Date.now() - started) / 1000)}s; complete logs: ${logDir}`);
  return code;
}

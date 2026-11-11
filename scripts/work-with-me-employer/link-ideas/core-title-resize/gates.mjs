// The existing wrappers own their complete retained matrices and parsers.
// This adapter runs them once, keeps every child byte, and adds the small
// resize matrix without reproducing the accepted gesture-test architecture.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, mkdtempSync, openSync, readFileSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import { focusedSpec, parseFocused } from '../peek-release/focused-evidence.mjs';
import { parseResize } from './contract.mjs';

const path = relative => fileURLToPath(new URL(relative, import.meta.url));
const totals = rows => rows.reduce((sum, row) => ({ passed: sum.passed + row.passed, failed: sum.failed + row.failed }), { passed: 0, failed: 0 });

export async function runTitleResizeGates(base, mode) {
  if (!['public', 'rehearsal'].includes(mode) || base !== (mode === 'public' ? 'https://staging39.thaisolitude.com' : 'http://localhost:8090') || (process.env.S_TARGET && process.env.S_TARGET !== 'staging39') || process.env.TS_PEEK_REHEARSAL_PROFILE !== undefined) {
    console.log('INVALID core-title-resize: exact staging39/isolated copy only; no alternate target or release profile.'); return 2;
  }
  const started = Date.now();
  let logDir;
  try {
    const parent = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out'); mkdirSync(parent, { recursive: true });
    logDir = mkdtempSync(join(parent, `core-title-resize-${mode}-`));
  } catch (error) { console.log(`INVALID core-title-resize: cannot retain complete logs: ${error.message}`); return 2; }

  const child = (name, command, args, env = {}) => new Promise(resolve => {
    const logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) }, fds = {};
    const errors = [];
    try { for (const channel of ['stdout', 'stderr']) fds[channel] = openSync(logs[channel], 'wx', 0o600); }
    catch (error) {
      for (const fd of Object.values(fds)) try { closeSync(fd); } catch {}
      resolve({ name, exitCode: null, signal: null, logs, lines: [], errors: [`complete logs unavailable: ${error.message}`] }); return;
    }
    const worker = spawn(command, args, { stdio: ['ignore', fds.stdout, fds.stderr], env: { ...process.env, ...env } });
    worker.on('error', error => errors.push(`launch failed: ${error.message}`));
    worker.on('close', (exitCode, signal) => {
      for (const fd of Object.values(fds)) try { closeSync(fd); } catch (error) { errors.push(`log close failed: ${error.message}`); }
      const lines = [];
      try { for (const file of Object.values(logs)) lines.push(...readFileSync(file, 'utf8').split(/\r?\n/)); }
      catch (error) { errors.push(`complete logs unreadable: ${error.message}`); }
      if (signal) errors.push(`child terminated: ${signal}`);
      resolve({ name, exitCode, signal, logs, lines, errors });
    });
  });

  // Resolve only the pinned, installed CLI. This prerequisite precedes the
  // focused WebKit check; subsequent retained wrappers reuse that installation.
  try {
    if (!process.env.TS_PLAYWRIGHT_ROOT) throw new Error('TS_PLAYWRIGHT_ROOT must name the installed pinned package');
    const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT);
    if (require('playwright/package.json').version !== '1.63.0') throw new Error('Playwright 1.63.0 required');
    if (process.env.GITHUB_ACTIONS === 'true') {
      const cli = join(dirname(require.resolve('playwright/package.json')), 'cli.js');
      const deps = process.getuid?.() === 0 ? [process.execPath, [cli, 'install-deps', 'webkit']] : ['sudo', ['-n', 'env', `PATH=${process.env.PATH}`, process.execPath, cli, 'install-deps', 'webkit']];
      for (const [name, command, args] of [['webkit-dependencies', ...deps], ['webkit-install', process.execPath, [cli, 'install', 'webkit']]]) {
        const result = await child(name, command, args);
        console.log(`INFO core-title-resize ${name}: child_exit=${result.exitCode}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
        if (result.exitCode !== 0 || result.errors.length) throw new Error(`${name} failed; retained logs identify the prerequisite failure`);
      }
    }
  } catch (error) { console.log(`INVALID core-title-resize: ${error.message}`); return 2; }

  const specs = ['chromium', 'firefox', 'webkit'].map(engine => ({ name: `resize-${engine}`, command: process.execPath,
    args: [path('../check-peek-resize.mjs'), base, ...(engine === 'chromium' ? [] : [`--${engine}`])], resize: engine }));
  if (mode === 'rehearsal') specs.push(
    // Dependency preparation has succeeded above. Existing wrappers' local
    // mode still checks the pinned package and launches every required browser,
    // while avoiding repeated install-deps/download steps in their nested chain.
    { name: 'webkit-layout', command: 'bash', args: [path('../check-peek-webkit.sh'), base], env: { GITHUB_ACTIONS: 'false' },
      phases: ['disclosures', 'layout'], phasePrefix: 'INFO Peek WebKit suite ',
      phasePattern: /^INFO Peek WebKit suite ([^:]+): child_exit=0; effective_exit=0; (\d+) passed, (\d+) failed; stdout=.+; stderr=.+$/,
      aggregatePrefix: 'INFO Peek WebKit supplemental verification:', aggregate: /^INFO Peek WebKit supplemental verification: PASSED, 2\/2 suites, (\d+) passed, (\d+) failed; complete logs: .+$/ },
    { name: 'retained-quiet-nav', command: process.execPath, args: [path('../core-quiet-nav/check-rehearsal.mjs'), base], env: { GITHUB_ACTIONS: 'false' },
      phases: ['retained-index-clearance', 'quiet-nav-chromium', 'quiet-nav-firefox', 'quiet-nav-webkit'], phasePrefix: 'INFO core-quiet-nav rehearsal phase ',
      phasePattern: /^INFO core-quiet-nav rehearsal phase ([^:]+): child_exit=0; signal=none; effective_exit=0; (\d+) passed, (\d+) failed; captures=\d+; stdout=.+; stderr=.+$/,
      aggregatePrefix: 'INFO core-quiet-nav rehearsal:', aggregate: /^INFO core-quiet-nav rehearsal: PASSED, 4\/4 phases, 24\/24 leaves; (\d+) passed, (\d+) failed; \d+s; complete logs: .+$/ }
  );
  else specs.push({ name: 'quiet-nav-chromium', command: process.execPath, args: [path('../check-peek-quiet-nav.mjs'), base], focused: focusedSpec('quiet-nav', 'chromium') });

  const results = [];
  for (const spec of specs) {
    // Public checkers pace their own loads. Also cover the boundary between
    // independent children, which otherwise each believe their load is first.
    if (mode === 'public' && results.length) await new Promise(resolve => setTimeout(resolve, 5500));
    const raw = await child(spec.name, spec.command, spec.args, spec.env), { lines, errors, ...metadata } = raw;
    for (const line of lines) if (line && !/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
    let parsed;
    if (spec.resize) parsed = parseResize(lines, spec.resize);
    else if (spec.focused) parsed = parseFocused(lines, spec.focused);
    else {
      const aggregates = lines.filter(line => line.startsWith(spec.aggregatePrefix)), match = aggregates.length === 1 && spec.aggregate.exec(aggregates[0]);
      parsed = { passed: match ? Number(match[1]) : 0, failed: match ? Number(match[2]) : 0, invalid: [], failureObserved: lines.some(line => /^FAIL(?:\s|$)/.test(line)) };
      const phases = new Map();
      for (const line of lines.filter(line => line.startsWith(spec.phasePrefix))) {
        const phase = spec.phasePattern.exec(line);
        if (!phase || !spec.phases.includes(phase[1]) || phases.has(phase[1]) || Number(phase[2]) === 0 || Number(phase[3]) !== 0) parsed.invalid.push('incomplete, duplicate or unsuccessful retained phase');
        else phases.set(phase[1], { passed: Number(phase[2]), failed: Number(phase[3]) });
      }
      const phaseCounts = totals([...phases.values()]);
      if (phases.size !== spec.phases.length || phaseCounts.passed !== parsed.passed || phaseCounts.failed !== parsed.failed) parsed.invalid.push('retained phase evidence differs from aggregate');
      if (!match || parsed.passed === 0 || parsed.failed !== 0) parsed.invalid.push('complete passing retained aggregate missing');
      parsed.invalid.push(...lines.filter(line => /^(?:INVALID|ERROR)(?:\s|$)/.test(line)));
    }
    const invalid = [...errors, ...parsed.invalid];
    const code = parsed.failed || parsed.failureObserved ? 1 : raw.exitCode !== 0 || invalid.length ? 2 : 0;
    const result = { ...metadata, code, passed: parsed.passed, failed: parsed.failed, invalid };
    results.push(result);
    if (code) { console.log(`INFO core-title-resize ${mode}: stop after ${spec.name}; remaining phases skipped.`); break; }
  }
  let code = results.some(result => result.code === 1) ? 1 : results.length !== specs.length || results.some(result => result.code !== 0) ? 2 : 0;
  const counts = totals(results);
  try { writeFileSync(join(logDir, 'results.json'), JSON.stringify({ base, mode, code, expectedPhases: specs.length, results, counts }, null, 2) + '\n', { flag: 'wx', mode: 0o600 }); }
  catch (error) { console.log(`INVALID core-title-resize: cannot retain final evidence: ${error.message}`); code ||= 2; }
  for (const result of results) {
    console.log(`INFO core-title-resize ${mode} phase ${result.name}: child_exit=${result.exitCode ?? 'unavailable'}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${result.passed} passed, ${result.failed} failed; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
    for (const reason of result.invalid) console.log(`INVALID core-title-resize ${result.name}: ${reason}`);
  }
  console.log(`INFO core-title-resize ${mode}: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length}/${specs.length} phases; ${counts.passed} passed, ${counts.failed} failed; ${Math.round((Date.now() - started) / 1000)}s; complete logs: ${logDir}`);
  return code;
}

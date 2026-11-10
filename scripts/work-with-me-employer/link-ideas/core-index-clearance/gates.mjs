// The complete accepted neighbour contract runs once, including pinned WebKit
// preparation and its retained chain. Then run the narrow index matrix. Every
// child stream is a regular file; no pipe buffering can discard leaf evidence.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, mkdtempSync, openSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import { indexCases, indexCaptures, INDEX_LIMITATION } from './contract.mjs';

const retainedNames = [
  'input focus chromium', 'disclosures chromium', 'disclosures firefox',
  'Kiro layout chromium', 'Kiro layout firefox', 'title rails chromium',
  'title rails firefox', 'title strip chromium', 'title strip firefox',
  'clearance chromium', 'clearance firefox', 'pages chromium', 'pages firefox',
  'native touch', 'retained Thai chromium'
];
const publicNames = [...retainedNames.filter(name => !['pages firefox', 'retained Thai chromium'].includes(name)), 'clearance chromium diagnostic subset'];
const exactKeys = (actual, expected) => JSON.stringify([...actual].sort()) === JSON.stringify([...expected].sort());
const sum = rows => [...rows].reduce((total, row) => ({ passed: total.passed + row.passed, failed: total.failed + row.failed }), { passed: 0, failed: 0 });

export async function runIndexClearanceGates(base, mode) {
  if (!['public', 'rehearsal'].includes(mode) || base !== (mode === 'public' ? 'https://staging39.thaisolitude.com' : 'http://localhost:8090') || (process.env.S_TARGET && process.env.S_TARGET !== 'staging39') || process.env.TS_PEEK_REHEARSAL_PROFILE !== undefined) {
    console.log('INVALID core-index-clearance: exact staging39/isolated copy only; no alternate target or release profile.'); return 2;
  }
  const started = Date.now(), engines = mode === 'rehearsal' ? ['chromium', 'firefox', 'webkit'] : ['chromium', 'firefox'];
  const retainedLeaves = [...(mode === 'rehearsal' ? retainedNames : publicNames), ...engines.map(engine => `neighbours ${engine}`)].map(name => `INFO Peek ${name}`);
  let logDir;
  try {
    const parent = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out'); mkdirSync(parent, { recursive: true });
    logDir = mkdtempSync(join(parent, `core-index-clearance-${mode}-`));
  } catch (error) { console.log(`INVALID core-index-clearance: cannot retain complete logs: ${error.message}`); return 2; }

  const child = (name, file, flags = []) => new Promise(resolve => {
    const logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) }, descriptors = {};
    let launchError = '', logError = '';
    try { for (const channel of ['stdout', 'stderr']) descriptors[channel] = openSync(logs[channel], 'wx', 0o600); }
    catch (error) {
      for (const fd of Object.values(descriptors)) try { closeSync(fd); } catch {}
      resolve({ name, exitCode: null, signal: null, logs, lines: [], errors: [`complete logs unavailable: ${error.message}`] }); return;
    }
    const processChild = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: ['ignore', descriptors.stdout, descriptors.stderr], env: process.env });
    processChild.on('error', error => { launchError = error.message; });
    processChild.on('close', (exitCode, signal) => {
      for (const fd of Object.values(descriptors)) try { closeSync(fd); } catch (error) { logError = error.message; }
      const lines = [], errors = [];
      try { for (const file of Object.values(logs)) lines.push(...readFileSync(file, 'utf8').split(/\r?\n/)); }
      catch (error) { logError = error.message; }
      if (launchError || signal || logError) errors.push(`process/log failure: ${launchError || signal || logError}`);
      resolve({ name, exitCode, signal, logs, lines, errors });
    });
  });

  const parse = (raw, engine = null) => {
    const { lines, ...result } = raw, invalid = [...raw.errors], summaries = new Map(), captures = new Set();
    const assertions = new Set(), assertedCases = new Set(), completed = new Set(), verdicts = new Map(), aggregates = [];
    const expectedCases = indexCases(), expectedCaptures = engine ? indexCaptures(engine) : [];
    let passed = 0, failed = 0, failureObserved = false;
    for (const line of lines) {
      // The adapters use exitCode, allowing these writes to finish naturally.
      if (line && !/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
      if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
      if (/^FAIL(?:\s|$)/.test(line)) failureObserved = true;
      if (engine) {
        const assertion = new RegExp(`^(PASS|FAIL) index-clearance ${engine}/([^/]+/[^/]+)/(.+)$`).exec(line);
        if (assertion) {
          assertion[1] === 'PASS' ? passed++ : failed++;
          if (!expectedCases.includes(assertion[2]) || assertions.has(`${assertion[2]}/${assertion[3]}`)) invalid.push('unexpected case or duplicate assertion label');
          assertions.add(`${assertion[2]}/${assertion[3]}`); assertedCases.add(assertion[2]);
        } else if (/^(PASS|FAIL)(?:\s|$)/.test(line)) invalid.push('unexpected assertion source');
        if (/^INFO index-clearance completed /.test(line)) {
          const match = new RegExp(`^INFO index-clearance completed ${engine}/([^/]+/[^/]+)$`).exec(line);
          if (!match || !expectedCases.includes(match[1]) || completed.has(match[1])) invalid.push('unexpected or duplicate completed case');
          else completed.add(match[1]);
        }
        if (/^INFO Peek index clearance /.test(line)) {
          if (summaries.size) invalid.push('duplicate index summary');
          const match = new RegExp(`^INFO Peek index clearance ${engine}: (\\d+) passed, (\\d+) failed; ${expectedCases.length}/${expectedCases.length} cases, ${expectedCases.length} page loads, ${expectedCaptures.length} captures, \\d+s; (.+)$`).exec(line);
          if (!match || match[3] !== INDEX_LIMITATION) invalid.push('incomplete index summary');
          summaries.set(`INFO Peek index clearance ${engine}`, { line, passed: match ? Number(match[1]) : 0, failed: match ? Number(match[2]) : 0 });
        }
        if (line.startsWith('INLINE ')) {
          const match = /^INLINE ([a-zA-Z0-9_.-]+\.jpg) base64:([A-Za-z0-9+/]+={0,2})$/.exec(line), jpeg = match && Buffer.from(match[2], 'base64');
          if (!match || !expectedCaptures.includes(match[1]) || captures.has(match[1]) || jpeg.length < 4 || jpeg[0] !== 0xff || jpeg[1] !== 0xd8 || jpeg.at(-2) !== 0xff || jpeg.at(-1) !== 0xd9) invalid.push('missing, duplicate or malformed screenshot evidence');
          else captures.add(match[1]);
        }
      } else {
        const numeric = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
        if (numeric) {
          const previous = summaries.get(numeric[1]), row = { line, passed: Number(numeric[2]), failed: Number(numeric[3]) };
          if (previous && (row.passed !== previous.passed || row.failed !== previous.failed)) invalid.push('conflicting retained leaf counts');
          if (!retainedLeaves.includes(numeric[1])) invalid.push('unexpected retained leaf');
          summaries.set(numeric[1], row);
        }
        const verdict = new RegExp(`^INFO core-neighbours ${mode} phase ([^:]+): child_exit=([^;]+); effective_exit=(\\d+); (\\d+) passed, (\\d+) failed; captures=(\\d+); stdout=(.+); stderr=(.+)$`).exec(line);
        if (verdict) {
          if (verdicts.has(verdict[1])) invalid.push('duplicate retained phase verdict');
          verdicts.set(verdict[1], { passed: Number(verdict[4]), failed: Number(verdict[5]) });
          const captureCount = verdict[1] === 'retained-input-focus' ? 0 : 16;
          if (verdict[2] !== '0' || verdict[3] !== '0' || Number(verdict[6]) !== captureCount || verdict[7] === 'unavailable' || verdict[8] === 'unavailable') invalid.push('retained phase did not succeed with complete logs/captures');
        } else if (new RegExp(`^INFO core-neighbours ${mode} phase `).test(line)) invalid.push('malformed retained phase verdict');
        if (new RegExp(`^INFO core-neighbours ${mode}: `).test(line)) aggregates.push(line);
      }
    }
    if (engine) {
      const summary = summaries.get(`INFO Peek index clearance ${engine}`);
      if (!summary || summary.passed !== passed || summary.failed !== failed || passed + failed === 0 || !exactKeys(assertedCases, expectedCases) || !exactKeys(completed, expectedCases) || !exactKeys(captures, expectedCaptures)) invalid.push('complete nonempty assertion/case/capture evidence does not match the summary');
    } else {
      if (!exactKeys(summaries.keys(), retainedLeaves) || [...summaries.values()].some(row => row.passed + row.failed === 0)) invalid.push('complete nonempty retained leaf results missing');
      ({ passed, failed } = sum(summaries.values()));
      const phaseNames = [...engines.map(engine => `neighbours-${engine}`), 'retained-input-focus'], phaseCounts = sum(verdicts.values());
      if (!exactKeys(verdicts.keys(), phaseNames) || phaseCounts.passed !== passed || phaseCounts.failed !== failed) invalid.push('retained phase counts do not match unique leaf counts');
      const aggregate = aggregates.length === 1 && new RegExp(`^INFO core-neighbours ${mode}: PASSED, ${phaseNames.length}/${phaseNames.length} phases; (\\d+) passed, (\\d+) failed; \\d+s; complete logs: .+$`).exec(aggregates[0]);
      if (!aggregate || Number(aggregate[1]) !== passed || Number(aggregate[2]) !== failed) invalid.push('complete retained aggregate missing or mismatched');
    }
    const code = failed || failureObserved ? 1 : raw.exitCode !== 0 || invalid.length ? 2 : 0;
    return { ...result, code, passed, failed, captures: captures.size, completedCases: completed.size, summaries: [...summaries.values()], invalid };
  };

  const results = [parse(await child('retained-neighbours', './check-retained.mjs', [mode]))];
  if (!results[0].code) for (const engine of engines) {
    const result = parse(await child(`index-clearance-${engine}`, '../check-peek-index-clearance.mjs', engine === 'chromium' ? [] : [`--${engine}`]), engine);
    results.push(result);
    if (result.code) break;
  }
  let code = results.some(result => result.code === 1) ? 1 : results.length !== engines.length + 1 || results.some(result => result.code !== 0) ? 2 : 0;
  const counts = sum(results), leaves = results.reduce((count, result) => count + result.summaries.length, 0), expectedLeaves = retainedLeaves.length + engines.length;
  if (!code && leaves !== expectedLeaves) code = 2;
  try { writeFileSync(join(logDir, 'results.json'), JSON.stringify({ base, mode, code, leaves, expectedLeaves, results, counts }, null, 2) + '\n', { flag: 'wx', mode: 0o600 }); }
  catch (error) { console.log(`INVALID core-index-clearance: cannot retain final evidence: ${error.message}`); code = code || 2; }
  for (const result of results) {
    console.log(`INFO core-index-clearance ${mode} phase ${result.name}: child_exit=${result.exitCode ?? 'unavailable'}; signal=${result.signal || 'none'}; effective_exit=${result.code}; ${result.passed} passed, ${result.failed} failed; captures=${result.captures}; stdout=${result.logs?.stdout || 'unavailable'}; stderr=${result.logs?.stderr || 'unavailable'}`);
    for (const reason of result.invalid) console.log(`INVALID core-index-clearance ${result.name}: ${reason}`);
  }
  console.log(`INFO core-index-clearance ${mode}: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length}/${engines.length + 1} phases, ${leaves}/${expectedLeaves} leaves; ${counts.passed} passed, ${counts.failed} failed; ${Math.round((Date.now() - started) / 1000)}s; complete logs: ${logDir}`);
  return code;
}

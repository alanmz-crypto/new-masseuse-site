// Complete neighbour evidence first, then the unchanged retained input-focus
// aggregate. All child bytes are retained, including trailing stderr and images.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, mkdtempSync, openSync, readFileSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';

const retainedNames = [
  'INFO Peek input focus chromium', 'INFO Peek disclosures chromium', 'INFO Peek disclosures firefox',
  'INFO Peek Kiro layout chromium', 'INFO Peek Kiro layout firefox', 'INFO Peek title rails chromium',
  'INFO Peek title rails firefox', 'INFO Peek title strip chromium', 'INFO Peek title strip firefox',
  'INFO Peek clearance chromium', 'INFO Peek clearance firefox', 'INFO Peek pages chromium',
  'INFO Peek pages firefox', 'INFO Peek native touch', 'INFO Peek retained Thai chromium'
];
const slugs = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'];
const exactKeys = (actual, expected) => JSON.stringify([...actual].sort()) === JSON.stringify([...expected].sort());

export async function runNeighbourGates(base, mode) {
  if (!['public', 'rehearsal'].includes(mode) || base !== (mode === 'public' ? 'https://staging39.thaisolitude.com' : 'http://localhost:8090') || (process.env.S_TARGET && process.env.S_TARGET !== 'staging39') || process.env.TS_PEEK_REHEARSAL_PROFILE !== undefined) {
    console.log('INVALID core-neighbours: exact staging39/isolated copy only; no alternate target or release profile.'); return 2;
  }
  const started = Date.now(), engines = mode === 'rehearsal' ? ['chromium', 'firefox', 'webkit'] : ['chromium', 'firefox'];
  let logDir;
  try {
    const parent = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out'); mkdirSync(parent, { recursive: true });
    logDir = mkdtempSync(join(parent, `core-neighbours-${mode}-`));
  } catch (error) { console.log(`INVALID core-neighbours: cannot retain complete logs: ${error.message}`); return 2; }
  const child = (name, command, args) => new Promise(resolve => {
    const logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) }, descriptors = {};
    let launchError = '', logError = '';
    try { for (const channel of ['stdout', 'stderr']) descriptors[channel] = openSync(logs[channel], 'wx', 0o600); }
    catch (error) {
      for (const fd of Object.values(descriptors)) try { closeSync(fd); } catch {}
      resolve({ name, exitCode: null, signal: null, logs, lines: [], errors: [`complete logs unavailable: ${error.message}`] }); return;
    }
    const processChild = spawn(command, args, { stdio: ['ignore', descriptors.stdout, descriptors.stderr], env: process.env });
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
  const prepareWebkit = async () => {
    try {
      if (!process.env.TS_PLAYWRIGHT_ROOT) throw new Error('TS_PLAYWRIGHT_ROOT must name the installed pinned package');
      const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT), pkg = require('playwright/package.json');
      if (pkg.version !== '1.63.0') throw new Error(`expected Playwright 1.63.0, found ${pkg.version}`);
      const cli = join(dirname(require.resolve('playwright/package.json')), 'cli.js');
      if (process.env.GITHUB_ACTIONS === 'true') {
        // Official CLI from the exact installed package: no npx/latest fetch.
        const deps = process.getuid?.() === 0
          ? [process.execPath, [cli, 'install-deps', 'webkit']]
          : ['sudo', ['-n', 'env', `PATH=${process.env.PATH}`, process.execPath, cli, 'install-deps', 'webkit']];
        for (const [name, command, args] of [['webkit-dependencies', ...deps], ['webkit-install', process.execPath, [cli, 'install', 'webkit']]]) {
          const result = await child(name, command, args);
          console.log(`INFO core-neighbours ${name}: child_exit=${result.exitCode}; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
          if (result.exitCode !== 0 || result.errors.length) throw new Error(`${name} failed; inspect retained installation logs`);
        }
      }
      console.log('INFO core-neighbours WebKit: exact Playwright 1.63.0; launch is mandatory; Linux WebKit is not iOS Safari.');
      return null;
    } catch (error) { return `WebKit prerequisite: ${error.message}`; }
  };
  const parse = (raw, engine) => {
    const { lines, ...result } = raw, invalid = [...raw.errors], summaries = new Map();
    let passed = 0, failed = 0, failureObserved = false;
    const assertions = new Set(), captures = new Set(), verdicts = new Map(), aggregates = [];
    const names = mode === 'public' ? retainedNames.filter(name => name !== 'INFO Peek retained Thai chromium') : retainedNames;
    const expectedCaptures = engine ? slugs.flatMap(slug => ['right', 'left'].flatMap(side => ['middle-short', 'research-landscape'].map(state => `peek-neighbours-${engine}-${slug}-${side}-${state}.jpg`))) : [];
    for (const line of lines) {
      if (line && !/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
      if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
      if (engine) {
        const assertion = new RegExp(`^(PASS|FAIL) neighbours ${engine}/(.+)$`).exec(line);
        if (assertion) {
          assertion[1] === 'PASS' ? passed++ : failed++;
          if (assertions.has(assertion[2])) invalid.push('duplicate assertion label');
          assertions.add(assertion[2]);
        } else if (/^(PASS|FAIL)(?:\s|$)/.test(line)) invalid.push('unexpected assertion source');
        if (/^INFO Peek neighbours /.test(line)) {
          if (summaries.size) invalid.push('duplicate neighbour summary');
          const match = new RegExp(`^INFO Peek neighbours ${engine}: (\\d+) passed, (\\d+) failed; 8/8 completed cases, 8 page loads, 16 captures; \\d+s; ${engine === 'chromium' ? 'trusted CDP touch and keyboard' : 'keyboard only'}; viewport resizing is not Safari, physical-device or OS text-scale validation\\.$`).exec(line);
          if (!match) invalid.push('incomplete neighbour summary');
          summaries.set('neighbours', { line, passed: match ? Number(match[1]) : 0, failed: match ? Number(match[2]) : 0 });
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
          if (!names.includes(numeric[1])) invalid.push('unexpected retained leaf');
          summaries.set(numeric[1], row);
        }
        const verdict = new RegExp(`^INFO core-input-focus ${mode} wrapper ([^:]+):`).exec(line);
        if (verdict) {
          if (verdicts.has(verdict[1])) invalid.push('duplicate retained wrapper verdict');
          verdicts.set(verdict[1], line);
          if (!/: child_exit=0; signal=none; effective_exit=0;/.test(line)) invalid.push('retained wrapper did not succeed');
        }
        if (new RegExp(`^INFO core-input-focus ${mode}: `).test(line)) aggregates.push(line);
        if (/^FAIL(?:\s|$)/.test(line)) failureObserved = true;
      }
    }
    if (engine) {
      const summary = summaries.get('neighbours');
      if (!summary || summary.passed !== passed || summary.failed !== failed || passed + failed === 0 || !exactKeys(captures, expectedCaptures)) invalid.push('complete nonempty assertion/capture evidence does not match the summary');
    } else {
      if (!exactKeys(summaries.keys(), names) || [...summaries.values()].some(row => row.passed + row.failed === 0)) invalid.push('complete nonempty retained leaf results missing');
      if (!exactKeys(verdicts.keys(), ['input-focus-chromium', 'retained-kiro-fixes']) || aggregates.length !== 1 || !new RegExp(`^INFO core-input-focus ${mode}: PASSED, 2 wrapper suites,`).test(aggregates[0] || '')) invalid.push('complete retained aggregate missing');
      const counts = [...summaries.values()].reduce((sum, row) => ({ passed: sum.passed + row.passed, failed: sum.failed + row.failed }), { passed: 0, failed: 0 });
      passed = counts.passed; failed = counts.failed;
    }
    const code = failed || failureObserved ? 1 : raw.exitCode !== 0 || invalid.length ? 2 : 0;
    return { ...result, code, passed, failed, captures: captures.size, summaries: [...summaries.values()], invalid };
  };
  const results = [];
  for (const engine of engines) {
    if (engine === 'webkit') {
      const error = await prepareWebkit();
      if (error) { results.push({ name: 'neighbours-webkit', code: 2, passed: 0, failed: 0, summaries: [], invalid: [error] }); break; }
    }
    const flags = engine === 'chromium' ? [] : [`--${engine}`];
    const result = parse(await child(`neighbours-${engine}`, process.execPath, [fileURLToPath(new URL('../check-peek-neighbours.mjs', import.meta.url)), base, ...flags]), engine);
    results.push(result);
    if (result.code) break;
  }
  if (results.length === engines.length && results.every(result => result.code === 0)) {
    results.push(parse(await child('retained-input-focus', process.execPath, [fileURLToPath(new URL(`../core-input-focus/check-${mode}.mjs`, import.meta.url)), base]), null));
  }
  let code = results.some(result => result.code === 1) ? 1 : results.length !== engines.length + 1 || results.some(result => result.code !== 0) ? 2 : 0;
  const counts = results.reduce((sum, result) => ({ passed: sum.passed + result.passed, failed: sum.failed + result.failed }), { passed: 0, failed: 0 });
  try { writeFileSync(join(logDir, 'results.json'), JSON.stringify({ base, mode, code, results, counts }, null, 2) + '\n', { flag: 'wx', mode: 0o600 }); }
  catch (error) { console.log(`INVALID core-neighbours: cannot retain final evidence: ${error.message}`); code = code || 2; }
  for (const result of results) {
    console.log(`INFO core-neighbours ${mode} phase ${result.name}: child_exit=${result.exitCode ?? 'unavailable'}; effective_exit=${result.code}; ${result.passed} passed, ${result.failed} failed; captures=${result.captures || 0}; stdout=${result.logs?.stdout || 'unavailable'}; stderr=${result.logs?.stderr || 'unavailable'}`);
    for (const reason of result.invalid) console.log(`INVALID core-neighbours ${result.name}: ${reason}`);
  }
  console.log(`INFO core-neighbours ${mode}: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length}/${engines.length + 1} phases; ${counts.passed} passed, ${counts.failed} failed; ${Math.round((Date.now() - started) / 1000)}s; complete logs: ${logDir}`);
  return code;
}

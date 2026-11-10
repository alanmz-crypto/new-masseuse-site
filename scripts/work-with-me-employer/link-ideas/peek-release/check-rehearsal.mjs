// First-install visitor release: reuse the complete retained core suite, then
// supplemental WebKit on the same isolated copy. No runtime/data injection or
// published practice routes; existing local structural fixtures remain tests.
import { spawn } from 'node:child_process';
import { closeSync, mkdirSync, mkdtempSync, openSync, readFileSync, writeFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const base = process.argv[2], started = Date.now();
const slugs = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'];
const practice = new Set(['/in-page-link-ideas/', '/progress-nav-ideas/', '/rail-nav-ideas/', '/index-nav-ideas/']);
const sha = bytes => createHash('sha256').update(bytes).digest('hex');
const normalize = pages => pages.map(({ id, path, title }) => ({ id, path, title })).sort((a, b) => a.id - b.id);
const keysEqual = (actual, expected) => JSON.stringify([...actual].sort()) === JSON.stringify([...expected].sort());
const retainedNames = [
  'INFO Peek input focus chromium', 'INFO Peek disclosures chromium', 'INFO Peek disclosures firefox',
  'INFO Peek Kiro layout chromium', 'INFO Peek Kiro layout firefox', 'INFO Peek title rails chromium',
  'INFO Peek title rails firefox', 'INFO Peek title strip chromium', 'INFO Peek title strip firefox',
  'INFO Peek clearance chromium', 'INFO Peek clearance firefox', 'INFO Peek pages chromium',
  'INFO Peek pages firefox', 'INFO Peek native touch', 'INFO Peek retained Thai chromium'
];
let logDir, evidence;
try {
  if (base !== 'http://localhost:8090' || process.argv.length !== 3) throw new Error('exact isolated localhost:8090 only; no extra arguments');
  if (process.env.TS_PEEK_REHEARSAL_PROFILE !== undefined) throw new Error('the release adapter owns its child profile; remove inherited TS_PEEK_REHEARSAL_PROFILE');
  if (!process.env.TS_PEEK_TITLE_INVENTORY) throw new Error('fresh native copy inventory is required');
  const manifestBytes = readFileSync(new URL('./manifest.json', import.meta.url)), manifest = JSON.parse(manifestBytes);
  const inventoryBytes = readFileSync(process.env.TS_PEEK_TITLE_INVENTORY), inventory = JSON.parse(inventoryBytes).pages;
  if (manifest.schema !== 1 || manifest.bundle !== 'peek-visitor-release-v1' || !Array.isArray(manifest.pages) || manifest.pages.length !== 15 || !Array.isArray(manifest.summary_slugs) || !keysEqual(manifest.summary_slugs, slugs)) throw new Error('reviewed release manifest identity, visitor inventory or summary scope differs');
  const validPages = pages => Array.isArray(pages) && pages.length === 15 && new Set(pages.map(page => page.id)).size === 15 && new Set(pages.map(page => page.path)).size === 15 && pages.every(page => Number.isInteger(page.id) && page.id > 0 && typeof page.title === 'string' && page.title.trim() && /^\/(?:[^?#]*\/)?$/.test(page.path) && !page.path.startsWith('//') && !practice.has(page.path));
  if (!validPages(manifest.pages) || !validPages(inventory) || JSON.stringify(normalize(inventory)) !== JSON.stringify(normalize(manifest.pages)) || slugs.some(slug => !inventory.some(page => page.path === `/${slug}/`))) throw new Error('fresh copy inventory must exactly match all 15 reviewed visitor IDs, paths and titles');
  if (!manifest.payloads?.core || !manifest.payloads.data || !keysEqual(Object.keys(manifest.payloads.data), slugs)) throw new Error('release payload map differs from the four approved summary pages');
  const payloads = [];
  const verifyPayload = (spec, path, source) => {
    if (spec?.file !== path || !/^[a-f0-9]{64}$/.test(spec.file_sha256 || '')) throw new Error(`invalid frozen payload declaration: ${path}`);
    const bytes = readFileSync(new URL(`./${path}`, import.meta.url)), current = readFileSync(new URL(source, import.meta.url));
    if (sha(bytes) !== spec.file_sha256 || !bytes.equals(current)) throw new Error(`frozen payload or current fixture source differs: ${path}`);
    payloads.push({ file: path, sha256: sha(bytes) }); return bytes;
  };
  verifyPayload(manifest.payloads.core, 'payload/core.php', '../peek-nav.php');
  for (const slug of slugs) {
    const data = JSON.parse(verifyPayload(manifest.payloads.data[slug], `payload/data/${slug}.json`, `../peek-data/${slug}.json`));
    if (data.slug !== slug || data.approval?.status !== 'approved') throw new Error(`frozen summary approval or identity differs: ${slug}`);
  }
  const parent = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out'); mkdirSync(parent, { recursive: true });
  logDir = mkdtempSync(join(parent, 'peek-release-browser-'));
  evidence = { base, manifest_sha256: sha(manifestBytes), inventory_sha256: sha(inventoryBytes), pages: normalize(inventory), summary_slugs: slugs, payloads };
  writeFileSync(join(logDir, 'inventory-and-payloads.json'), JSON.stringify(evidence, null, 2) + '\n', { flag: 'wx', mode: 0o600 });
  console.log(`INFO Peek release inventory: 15 exact visitor pages plus search/404; four approved summaries; manifest=${evidence.manifest_sha256}; fresh_inventory=${evidence.inventory_sha256}; complete logs: ${logDir}`);
} catch (error) {
  console.log(`INVALID Peek visitor-release rehearsal: ${error.message}; no browser suites started.`); process.exit(2);
}

const run = spec => new Promise(resolve => {
  const logs = { stdout: join(logDir, `${spec.name}.stdout.log`), stderr: join(logDir, `${spec.name}.stderr.log`) }, descriptors = {};
  let launchError = '', logError = '';
  try { for (const channel of ['stdout', 'stderr']) descriptors[channel] = openSync(logs[channel], 'wx', 0o600); }
  catch (error) {
    for (const fd of Object.values(descriptors)) try { closeSync(fd); } catch {}
    resolve({ name: spec.name, code: 2, logs, summaries: [], invalid: [`cannot create complete child logs: ${error.message}`] }); return;
  }
  // Redirect directly to regular files. This avoids pipe truncation and keeps
  // the full nested output even if a browser or wrapper exits before its summary.
  const child = spawn(spec.command, spec.args, { stdio: ['ignore', descriptors.stdout, descriptors.stderr], env: { ...process.env, ...spec.env } });
  child.on('error', error => { launchError = error.message; });
  child.on('close', (exitCode, signal) => {
    for (const fd of Object.values(descriptors)) try { closeSync(fd); } catch (error) { logError = error.message; }
    const summaries = new Map(), verdicts = new Map(), invalid = [], aggregates = [];
    let failed = false;
    try {
      for (const path of Object.values(logs)) for (const line of readFileSync(path, 'utf8').split(/\r?\n/)) {
        if (line && !/^PASS(?:\s|$)/.test(line)) process.stdout.write(line + '\n');
        const numeric = /^(INFO Peek .+): (\d+) passed, (\d+) failed(?:[,;].*)?$/.exec(line);
        if (numeric) {
          const name = numeric[1], passed = Number(numeric[2]), failedCount = Number(numeric[3]), previous = summaries.get(name);
          if (previous && (previous.passed !== passed || previous.failed !== failedCount)) invalid.push(`conflicting counts for ${name}`);
          if (!spec.names.includes(name)) invalid.push(`unexpected leaf summary: ${name}`);
          if (/^INFO Peek title (?:rails|strip) (?:chromium|firefox)$/.test(name) && !/; 17 actual routes,/.test(line)) invalid.push(`${name}: complete 15-page plus search/404 matrix is missing`);
          summaries.set(name, { name, passed, failed: failedCount, line }); failed ||= failedCount > 0;
        }
        const verdict = spec.verdict.exec(line);
        if (verdict) {
          const previous = verdicts.get(verdict[1]);
          if (previous && previous !== line) invalid.push(`conflicting wrapper verdict: ${verdict[1]}`);
          verdicts.set(verdict[1], line);
          if (!spec.children.includes(verdict[1]) || !spec.successVerdict.test(line)) invalid.push(`incomplete or unexpected wrapper result: ${line}`);
        }
        if (spec.aggregate.test(line)) aggregates.push(line);
        if (/^INVALID(?:\s|$)/.test(line)) invalid.push(line);
        if (/^FAIL(?:\s|$)/.test(line)) failed = true;
      }
    } catch (error) { logError = error.message; }
    if (!keysEqual(summaries.keys(), spec.names) || [...summaries.values()].some(row => row.passed + row.failed === 0)) invalid.push('exact nonempty leaf suite results missing');
    if (!keysEqual(verdicts.keys(), spec.children) || aggregates.length !== 1 || !spec.successAggregate.test(aggregates[0] || '')) invalid.push('complete successful wrapper results missing');
    if (launchError || signal || logError) invalid.push(`process/log failure: ${launchError || signal || logError}`);
    const code = failed ? 1 : exitCode !== 0 || invalid.length ? 2 : 0;
    resolve({ name: spec.name, code, exitCode, signal, logs, summaries: [...summaries.values()], verdicts: [...verdicts.values()], aggregate: aggregates[0] || null, invalid });
  });
});
const specs = [
  { name: 'retained-core', command: process.execPath, args: [fileURLToPath(new URL('../core-input-focus/check-rehearsal.mjs', import.meta.url)), base],
    env: { TS_PEEK_REHEARSAL_PROFILE: 'visitor-release-v1' }, names: retainedNames,
    children: ['input-focus-chromium', 'retained-kiro-fixes'], verdict: /^INFO core-input-focus rehearsal wrapper ([^:]+):/,
    successVerdict: /: child_exit=0; signal=none; effective_exit=0;/,
    aggregate: /^INFO core-input-focus rehearsal: /, successAggregate: /^INFO core-input-focus rehearsal: PASSED, 2 wrapper suites,/ },
  { name: 'webkit', command: 'bash', args: [fileURLToPath(new URL('../check-peek-webkit.sh', import.meta.url)), base], env: {},
    names: ['INFO Peek disclosures webkit', 'INFO Peek Kiro layout webkit'], children: ['disclosures', 'layout'],
    verdict: /^INFO Peek WebKit suite ([^:]+):/, successVerdict: /: child_exit=0; effective_exit=0;/,
    aggregate: /^INFO Peek WebKit supplemental verification: /, successAggregate: /^INFO Peek WebKit supplemental verification: PASSED, 2\/2 suites,/ }
], results = [];
for (const spec of specs) {
  const result = await run(spec); results.push(result);
  if (result.code !== 0) { console.log(`INFO Peek visitor-release: stop after ${spec.name}; remaining phases skipped.`); break; }
}
let code = results.some(result => result.code === 1) ? 1 : results.length !== specs.length || results.some(result => result.code !== 0) ? 2 : 0;
const counts = results.flatMap(result => result.summaries).reduce((sum, row) => ({ passed: sum.passed + row.passed, failed: sum.failed + row.failed }), { passed: 0, failed: 0 });
try { writeFileSync(join(logDir, 'results.json'), JSON.stringify({ ...evidence, code, results, counts }, null, 2) + '\n', { flag: 'wx', mode: 0o600 }); }
catch (error) { console.log(`INVALID Peek visitor-release: cannot retain complete final evidence: ${error.message}`); code = code || 2; }
for (const result of results) {
  console.log(`INFO Peek visitor-release phase ${result.name}: child_exit=${result.exitCode ?? 'unavailable'}; effective_exit=${result.code}; ${result.summaries.length} leaf suites; stdout=${result.logs.stdout}; stderr=${result.logs.stderr}`);
  for (const reason of result.invalid) console.log(`INVALID Peek visitor-release ${result.name}: ${reason}`);
}
console.log(`INFO Peek visitor-release rehearsal: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length}/2 phases, ${results.reduce((sum, result) => sum + result.summaries.length, 0)}/17 leaf suites; ${counts.passed} passed, ${counts.failed} failed; ${Math.round((Date.now() - started) / 1000)}s; complete logs: ${logDir}`);
process.exit(code);

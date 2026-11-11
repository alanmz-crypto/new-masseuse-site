// Offline tests of the real sanitizer and artifact lifecycle; no browser/network.
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { existsSync, lstatSync, mkdirSync, mkdtempSync, readFileSync, readdirSync, rmSync, symlinkSync, writeFileSync } from 'node:fs';
import { basename, dirname, join } from 'node:path';
import { tmpdir } from 'node:os';
import { createHarCapture, sanitizeHar } from './har-capture.mjs';

let passed = 0;
const check = (label, run) => { run(); passed++; console.log(`PASS HAR capture: ${label}`); };
const documentURL = 'https://staging39.thaisolitude.com/rail-nav-ideas/';
const secret = 'PRIVATE_SENTINEL_NOT_FOR_ARTIFACT';
const entry = (url, type, mimeType, text, encoding) => ({
  _resourceType: type, _private: secret, pageref: secret, timings: { secret },
  request: { method: 'GET', url, httpVersion: 'HTTP/1.1', headers: [{ name: 'Accept', value: '*/*', comment: secret }, { name: 'CoOkIe', value: secret }], cookies: [{ name: 'session', value: secret }], queryString: [{ name: 'private', value: secret }], _private: secret },
  response: { status: 200, statusText: 'OK', httpVersion: 'HTTP/1.1', headers: [{ name: 'Content-Type', value: mimeType }, { name: 'sEt-CoOkIe', value: secret }], content: { mimeType, text, ...(encoding ? { encoding } : {}), _private: secret }, redirectURL: '', cookies: [{ value: secret }], _private: secret }
});
const source = () => ({ log: { version: '1.2', creator: { name: secret }, pages: [{ title: secret }], _private: secret, entries: [
  entry(documentURL, 'document', 'text/html', '<!doctype html><link rel="stylesheet" href="/style.css"><h1>Actual page</h1>'),
  entry('https://staging39.thaisolitude.com/style.css?ver=1', 'stylesheet', 'text/css', '@font-face{font-family:actual;src:url(https://fonts.example/actual.woff2)}'),
  entry('https://fonts.example/actual.woff2', 'font', 'font/woff2', Buffer.from([0, 255, 82, 33]).toString('base64'), 'base64'),
  entry('https://staging39.thaisolitude.com/script.js', 'script', 'application/javascript', 'window.example=true;')
] } });
const refusal = (label, mutate, pattern) => check(label, () => {
  const raw = source(); mutate(raw);
  assert.throws(() => sanitizeHar(raw, documentURL), error => pattern.test(error.message) && !error.message.includes(secret));
});

check('whitelist removes cookie/header/metadata values without changing embedded resources', () => {
  const raw = source(), { har, counts } = sanitizeHar(raw, documentURL), serialized = JSON.stringify(har);
  assert(!serialized.includes(secret)); assert.equal(counts.entries, 4); assert.equal(counts.strippedHeaders, 8);
  assert.deepEqual([counts.documents, counts.styles, counts.scripts, counts.fonts], [1, 1, 1, 1]);
  for (let i = 0; i < 4; i++) assert.equal(har.log.entries[i].response.content.text, raw.log.entries[i].response.content.text);
  assert.deepEqual(Object.keys(har.log), ['version', 'creator', 'entries']);
  assert.deepEqual(Object.keys(har.log.entries[0]), ['request', 'response']);
  assert.deepEqual(Object.keys(har.log.entries[0].request), ['method', 'url', 'httpVersion', 'headers']);
  assert.deepEqual(Object.keys(har.log.entries[0].response), ['status', 'statusText', 'httpVersion', 'headers', 'content', 'redirectURL']);
  assert.deepEqual(har.log.entries[2].response.content, { size: 4, mimeType: 'font/woff2', text: 'AP9SIQ==', encoding: 'base64' });
  assert.equal(raw.log.creator.name, secret); // The raw object is not rewritten.
});
check('response authorization fields are stripped case-insensitively', () => {
  const raw = source(); raw.log.entries[0].response.headers.push({ name: 'AUTHORIZATION', value: secret }, { name: 'Proxy-Authorization', value: secret });
  assert(!JSON.stringify(sanitizeHar(raw, documentURL).har).includes(secret));
});
for (const name of ['Authorization', 'pRoXy-AuThOrIzAtIoN', 'X-API-Key']) refusal(`${name} request refused`, raw => raw.log.entries[0].request.headers.push({ name, value: secret }), /authenticated request refused/);
for (const method of ['POST', 'HEAD', 'get']) refusal(`${method} request refused`, raw => { raw.log.entries[0].request.method = method; }, /GET without postData/);
for (const value of [{ text: secret }, null, {}]) refusal(`postData ${value === null ? 'null' : JSON.stringify(value) === '{}' ? 'empty' : 'body'} refused`, raw => { raw.log.entries[0].request.postData = value; }, /GET without postData/);
for (const value of ['https://user:pass@example.com/a', 'wss://example.com/a', 'file:///private', 'not a URL']) refusal(`unsafe resource URL ${value.split(':')[0]} refused`, raw => { raw.log.entries[1].request.url = value; }, /URL required|invalid URL/);
refusal('credential-bearing redirect refused', raw => { raw.log.entries[1].response.headers.push({ name: 'Location', value: 'https://user:pass@example.com/a' }); }, /URL required/);
refusal('external response body reference refused', raw => { raw.log.entries[1].response.content._file = secret; }, /embedded content required/);
refusal('missing stylesheet body refused', raw => { delete raw.log.entries[1].response.content.text; }, /embedded body missing/);
refusal('missing font body refused', raw => { delete raw.log.entries[2].response.content.text; }, /embedded body missing/);
refusal('malformed font encoding refused', raw => { raw.log.entries[2].response.content.text = 'not base64!'; }, /invalid embedded base64/);
refusal('unknown encoding refused', raw => { raw.log.entries[2].response.content.encoding = 'gzip'; }, /embedded content required/);
refusal('incomplete response refused', raw => { raw.log.entries[1].response.status = -1; }, /complete response required/);
refusal('304 without its cached body refused', raw => { raw.log.entries[1].response.status = 304; }, /complete response required/);
refusal('main document non-200 refused', raw => { raw.log.entries[0].response.status = 202; }, /embedded HTML200/);
refusal('main document must be HTML despite document resource type', raw => { raw.log.entries[0].response.content.mimeType = 'application/json'; }, /embedded HTML200/);
refusal('main document cannot be empty', raw => { raw.log.entries[0].response.content.text = ''; }, /embedded HTML200/);
refusal('missing main document refused', raw => { raw.log.entries.shift(); }, /exactly one embedded main document/);
refusal('duplicate main document refused', raw => { raw.log.entries.push(structuredClone(raw.log.entries[0])); }, /exactly one embedded main document/);
refusal('empty archive refused', raw => { raw.log.entries = []; }, /nonempty HAR/);
refusal('unknown HAR version refused', raw => { raw.log.version = '2'; }, /nonempty HAR/);
refusal('malformed header refused without echoing its value', raw => { raw.log.entries[0].request.headers.push({ name: 'bad header', value: secret }); }, /malformed header/);
check('bodyless redirect preserved with its anonymous target', () => {
  const raw = source(), redirect = entry('https://staging39.thaisolitude.com/old.css', 'stylesheet', 'text/css', undefined);
  redirect.response.status = 302; redirect.response.redirectURL = '/style.css?ver=1';
  redirect.response.headers.push({ name: 'Location', value: '/style.css?ver=1' }); raw.log.entries.push(redirect);
  assert.equal(sanitizeHar(raw, documentURL).har.log.entries[4].response.content.text, '');
});
check('explicitly embedded empty CSS/JS/font bodies are preserved', () => {
  const raw = source(); for (const item of raw.log.entries.slice(1)) item.response.content.text = '';
  const result = sanitizeHar(raw, documentURL);
  for (const item of result.har.log.entries.slice(1)) { assert.equal(item.response.content.text, ''); assert.equal(item.response.content.size, 0); }
});
for (const origin of ['http://localhost:8090', 'http://localhost:8091']) check(`isolated fixture document accepted ${origin}`, () => {
  const raw = source(), fixtureURL = `${origin}/rail-nav-ideas/`; raw.log.entries[0].request.url = fixtureURL;
  assert.equal(sanitizeHar(raw, fixtureURL).counts.documents, 1);
});

const root = mkdtempSync(join(tmpdir(), 'peek-har-selftest-'));
try {
  const make = (extra = {}) => createHarCapture({ caseLabel: '390/hold-4', documentURL, runnerTemp: root, ...extra });
  check('raw evidence is private and outside artifact path', () => {
    const capture = make();
    assert.equal(capture.recordHar.mode, 'minimal'); assert.equal(capture.recordHar.content, 'embed');
    assert(!capture.recordHar.path.includes('/ops-out/'));
    assert.equal(lstatSync(capture.recordHar.path).mode & 0o777, 0o600);
    assert.equal(lstatSync(dirname(capture.recordHar.path)).mode & 0o777, 0o700);
    capture.cleanup(); capture.cleanup(); assert(!existsSync(dirname(capture.recordHar.path)));
    assert.throws(() => capture.finalize(), /already finalized or cleaned/);
  });
  check('closed-context finalize publishes private sanitized HAR/hash manifest and removes raw', () => {
    const capture = make(); writeFileSync(capture.recordHar.path, JSON.stringify(source()));
    const result = capture.finalize();
    assert.equal(result.entries, 4); assert.equal(result.anonymousGetOnly, true);
    assert.equal(dirname(result.harPath), join(root, 'ops-out', 'rail-input-har'));
    assert.equal(lstatSync(result.harPath).mode & 0o777, 0o600); assert.equal(lstatSync(result.manifestPath).mode & 0o777, 0o600);
    const bytes = readFileSync(result.harPath); assert(!bytes.toString().includes(secret));
    assert.equal(result.sha256, createHash('sha256').update(bytes).digest('hex'));
    const manifest = JSON.parse(readFileSync(result.manifestPath, 'utf8')); assert.equal(manifest.sha256, result.sha256);
    assert.equal(manifest.harFile, basename(result.harPath)); assert.equal(manifest.bytes, bytes.length);
    assert(!existsSync(dirname(capture.recordHar.path))); capture.cleanup();
    assert.throws(() => capture.finalize(), /already finalized or cleaned/);
  });
  check('invalid capture is removed without publishing artifacts', () => {
    const before = readdirSync(join(root, 'ops-out', 'rail-input-har')).sort();
    const capture = make(), raw = source(); raw.log.entries[0].request.method = 'POST';
    writeFileSync(capture.recordHar.path, JSON.stringify(raw)); assert.throws(() => capture.finalize(), /GET without postData/);
    assert(!existsSync(dirname(capture.recordHar.path)));
    assert.deepEqual(readdirSync(join(root, 'ops-out', 'rail-input-har')).sort(), before);
  });
  check('unflushed/malformed capture is removed', () => {
    const capture = make(); writeFileSync(capture.recordHar.path, secret);
    assert.throws(() => capture.finalize(), /closed-context HAR missing or malformed/);
    assert(!existsSync(dirname(capture.recordHar.path)));
  });
  for (const [label, extra, pattern] of [
    ['relative temp', { runnerTemp: 'relative' }, /absolute RUNNER_TEMP/],
    ['missing temp', { runnerTemp: undefined }, /absolute RUNNER_TEMP/],
    ['unsafe label', { caseLabel: '../private' }, /invalid case label/],
    ['live origin', { documentURL: 'https://thaisolitude.com/rail-nav-ideas/' }, /exact diagnostic page/],
    ['different page', { documentURL: 'https://staging39.thaisolitude.com/privacy-policy/' }, /exact diagnostic page/],
    ['artifact temp', { runnerTemp: join(root, 'ops-out') }, /outside ops-out/]
  ]) check(`${label} refused`, () => assert.throws(() => make(extra), pattern));
  check('symlinked artifact directory refused and raw removed', () => {
    const separate = join(root, 'symlink-case'); mkdirSync(separate); mkdirSync(join(separate, 'elsewhere'));
    symlinkSync(join(separate, 'elsewhere'), join(separate, 'ops-out'));
    const capture = make({ runnerTemp: separate }); writeFileSync(capture.recordHar.path, JSON.stringify(source()));
    assert.throws(() => capture.finalize(), /must not use symlinks/); assert(!existsSync(dirname(capture.recordHar.path)));
    assert.deepEqual(readdirSync(join(separate, 'elsewhere', 'rail-input-har')), []);
  });
} finally { rmSync(root, { recursive: true, force: true }); }
console.log(`INFO HAR capture self-test: ${passed} passed, 0 failed; offline only.`);

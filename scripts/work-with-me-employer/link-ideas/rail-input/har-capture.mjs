// Optional evidence for the existing anonymous diagnostic loads. Recording does
// not route requests or alter cache/input settings; body collection adds overhead.
import { createHash } from 'node:crypto';
import { chmodSync, lstatSync, mkdirSync, mkdtempSync, readFileSync, realpathSync, rmSync, writeFileSync } from 'node:fs';
import { basename, isAbsolute, join } from 'node:path';

const refused = message => { throw new Error(`HAR capture: ${message}`); };
const authHeaders = new Set(['authorization', 'proxy-authorization', 'x-api-key']);
const privateHeaders = new Set(['cookie', 'set-cookie', ...authHeaders]);
const documentOrigins = new Set(['https://staging39.thaisolitude.com', 'http://localhost:8090', 'http://localhost:8091']);
const hash = bytes => createHash('sha256').update(bytes).digest('hex');
const url = (value, label) => {
  let parsed; try { parsed = new URL(value); } catch { refused(`${label}: invalid URL`); }
  if (typeof value !== 'string' || !['http:', 'https:'].includes(parsed.protocol) || parsed.username || parsed.password || parsed.hash) refused(`${label}: anonymous HTTP(S) URL required`);
  return parsed;
};
const documentUrl = value => {
  const parsed = url(value, 'document');
  if (!documentOrigins.has(parsed.origin) || parsed.pathname !== '/rail-nav-ideas/' || parsed.search) refused('document: exact diagnostic page required');
  return parsed.href;
};

// Reconstruct only routing fields. Never copy arbitrary HAR extension fields,
// cookie arrays, queryString duplication, postData, page metadata or timings.
export function sanitizeHar(raw, expectedDocument) {
  const mainURL = documentUrl(expectedDocument);
  if (raw?.log?.version !== '1.2' || !Array.isArray(raw.log.entries) || !raw.log.entries.length) refused('nonempty HAR 1.2 required');
  const counts = { entries: 0, documents: 0, styles: 0, scripts: 0, fonts: 0, other: 0, strippedHeaders: 0, bodyBytes: 0 };
  let mainDocuments = 0;
  const headers = (input, label, request) => {
    if (!Array.isArray(input)) refused(`${label}: headers missing`);
    return input.flatMap(header => {
      if (typeof header?.name !== 'string' || !/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/.test(header.name) || typeof header.value !== 'string' || /[\r\n]/.test(header.value)) refused(`${label}: malformed header`);
      const name = header.name.toLowerCase();
      if (request && authHeaders.has(name)) refused(`${label}: authenticated request refused`);
      if (privateHeaders.has(name)) { counts.strippedHeaders++; return []; }
      return [{ name: header.name, value: header.value }];
    });
  };
  const entries = raw.log.entries.map((entry, index) => {
    const label = `entry ${index + 1}`, request = entry?.request, response = entry?.response;
    const requestURL = url(request?.url, label);
    if (request.method !== 'GET' || Object.hasOwn(request, 'postData')) refused(`${label}: GET without postData required`);
    if (!Number.isInteger(response?.status) || response.status < 200 || response.status > 599 || response.status === 304) refused(`${label}: complete response required`);
    if (typeof request.httpVersion !== 'string' || typeof response.httpVersion !== 'string' || typeof response.statusText !== 'string') refused(`${label}: HTTP metadata missing`);
    const requestHeaders = headers(request.headers, label, true), responseHeaders = headers(response.headers, label, false);
    const content = response.content;
    if (!content || typeof content.mimeType !== 'string' || Object.hasOwn(content, '_file') || (content.encoding !== undefined && content.encoding !== 'base64')) refused(`${label}: embedded content required`);
    const noBody = [204, 205, 301, 302, 303, 307, 308].includes(response.status);
    if (typeof content.text !== 'string' && (!noBody || content.text !== undefined)) refused(`${label}: embedded body missing`);
    const bodyText = content.text ?? '';
    if (content.encoding === 'base64' && (!/^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/.test(bodyText) || Buffer.from(bodyText, 'base64').toString('base64') !== bodyText)) refused(`${label}: invalid embedded base64`);
    const body = Buffer.from(bodyText, content.encoding === 'base64' ? 'base64' : 'utf8');
    const kind = entry._resourceType, mime = content.mimeType.toLowerCase();
    const category = kind === 'document' || /(?:text\/html|application\/xhtml\+xml)/.test(mime) ? 'documents'
      : kind === 'stylesheet' || mime.startsWith('text/css') ? 'styles'
      : kind === 'script' || /(?:javascript|ecmascript)/.test(mime) ? 'scripts'
      : kind === 'font' || /(?:^font\/|font-|woff|opentype)/.test(mime) ? 'fonts' : 'other';
    if (requestURL.href === mainURL) {
      if (response.status !== 200 || !/^(?:text\/html|application\/xhtml\+xml)(?:;|$)/.test(mime) || !body.length) refused(`${label}: main document must be embedded HTML200`);
      mainDocuments++;
    }
    const redirectURL = response.redirectURL ?? '';
    if (typeof redirectURL !== 'string') refused(`${label}: invalid redirect`);
    for (const target of [redirectURL, ...responseHeaders.filter(header => header.name.toLowerCase() === 'location').map(header => header.value)].filter(Boolean)) {
      let targetURL; try { targetURL = new URL(target, requestURL).href; } catch { refused(`${label}: invalid redirect URL`); }
      url(targetURL, `${label} redirect`);
    }
    counts.entries++; counts[category]++; counts.bodyBytes += body.length;
    return {
      request: { method: 'GET', url: requestURL.href, httpVersion: request.httpVersion, headers: requestHeaders },
      response: { status: response.status, statusText: response.statusText, httpVersion: response.httpVersion, headers: responseHeaders,
        content: { size: body.length, mimeType: content.mimeType, text: bodyText, ...(content.encoding ? { encoding: content.encoding } : {}) }, redirectURL }
    };
  });
  if (mainDocuments !== 1) refused('exactly one embedded main document required');
  return { har: { log: { version: '1.2', creator: { name: 'Peek anonymous diagnostic capture', version: '1' }, entries } }, counts };
}

// All methods are synchronous. finalize() is called only after context.close()
// has flushed Playwright's HAR; cleanup() also belongs in the caller's finally.
export function createHarCapture({ caseLabel, documentURL, runnerTemp }) {
  const mainURL = documentUrl(documentURL);
  if (typeof caseLabel !== 'string' || !/^[a-zA-Z0-9-]+(?:\/[a-zA-Z0-9-]+)?$/.test(caseLabel)) refused('invalid case label');
  if (typeof runnerTemp !== 'string' || !isAbsolute(runnerTemp)) refused('absolute RUNNER_TEMP required');
  const root = realpathSync(runnerTemp);
  if (!lstatSync(root).isDirectory() || root.split('/').includes('ops-out')) refused('private RUNNER_TEMP outside ops-out required');
  const rawDirectory = mkdtempSync(join(root, 'rail-input-har-private-'));
  const rawPath = join(rawDirectory, 'capture.har');
  try { chmodSync(rawDirectory, 0o700); writeFileSync(rawPath, '', { flag: 'wx', mode: 0o600 }); }
  catch (error) { rmSync(rawDirectory, { recursive: true, force: true }); throw error; }
  let finished = false, cleaned = false;
  const cleanup = () => { if (!cleaned) { rmSync(rawDirectory, { recursive: true, force: true }); cleaned = true; } };
  return {
    recordHar: { path: rawPath, mode: 'minimal', content: 'embed' },
    cleanup,
    finalize() {
      if (finished || cleaned) refused('capture already finalized or cleaned');
      finished = true;
      const written = [];
      try {
        let raw; try { raw = JSON.parse(readFileSync(rawPath, 'utf8')); } catch { refused('closed-context HAR missing or malformed'); }
        const { har, counts } = sanitizeHar(raw, mainURL);
        const output = join(root, 'ops-out', 'rail-input-har');
        mkdirSync(output, { recursive: true, mode: 0o700 });
        if (lstatSync(output).isSymbolicLink() || realpathSync(output) !== output) refused('artifact directory must not use symlinks');
        const stem = `${caseLabel.replaceAll('/', '-')}-${basename(rawDirectory).slice('rail-input-har-private-'.length)}`;
        const harPath = join(output, `${stem}.har`), manifestPath = join(output, `${stem}.json`);
        const bytes = Buffer.from(JSON.stringify(har));
        const manifest = { schema: 1, caseLabel, anonymousGetOnly: true, embeddedContent: true, ...counts, bytes: bytes.length, sha256: hash(bytes), harFile: basename(harPath) };
        writeFileSync(harPath, bytes, { flag: 'wx', mode: 0o600 }); written.push(harPath);
        writeFileSync(manifestPath, JSON.stringify(manifest, null, 2) + '\n', { flag: 'wx', mode: 0o600 }); written.push(manifestPath);
        return { ...manifest, harPath, manifestPath };
      } catch (error) {
        for (const path of written) rmSync(path, { force: true });
        throw error;
      } finally { cleanup(); }
    }
  };
}

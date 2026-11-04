// Read-only public-page diagnostic. Emits gzip JSON between NAVDEBUG markers.
// node inspect-navigation.mjs https://staging38.thaisolitude.com [--no-js]
import { createRequire } from 'node:module';
import { gzipSync } from 'node:zlib';

const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = process.argv[2];
if (base !== 'https://staging38.thaisolitude.com' || process.argv.slice(3).some(arg => arg !== '--no-js')) {
  throw new Error('Exact https://staging38.thaisolitude.com [--no-js] only');
}
const browser = await chromium.launch(process.env.TS_CHROMIUM_EXECUTABLE ? { executablePath: process.env.TS_CHROMIUM_EXECUTABLE } : {});
const report = { created: new Date().toISOString(), origin: base, browser: browser.version(), pages: [], scripts: [], noJS: [] };
let invalid = false;
const scriptURLs = new Set();

async function makeContext(javaScriptEnabled = true) {
  const context = await browser.newContext({ javaScriptEnabled, viewport: { width: 390, height: 844 }, reducedMotion: 'no-preference', serviceWorkers: 'block' });
  // No forms, analytics writes, or authenticated storage; requests are public GETs.
  await context.route('**/*', route => invalid || route.request().method() !== 'GET' ? route.abort() : route.continue());
  context.on('response', response => {
    if (response.status() === 202 && new URL(response.url()).origin === base) invalid = true;
  });
  return context;
}

async function load(context, path) {
  if (invalid) throw new Error('INVALID: hosting challenge; no further requests');
  const page = await context.newPage();
  const response = await page.goto(base + path, { waitUntil: 'networkidle', timeout: 60000 });
  if (invalid || response?.status() === 202 || /Robot Challenge/i.test(await page.title())) {
    invalid = true;
    throw new Error('INVALID: hosting challenge; no further requests');
  }
  if (response?.status() !== 200 || page.url() !== base + path) throw new Error(`Rejected document response for ${path}`);
  await page.evaluate(() => document.fonts.ready);
  return page;
}

async function traceClick(page) {
  const first = page.locator('.entry-content .ts-jump a[href^="#"]').first();
  if (!await first.count()) throw new Error('First inline jump link missing');
  await first.scrollIntoViewIfNeeded();
  await first.evaluate(link => {
    const target = document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1)));
    const describe = node => node instanceof Element ? { tag: node.tagName, id: node.id, class: node.getAttribute('class'), href: node.getAttribute('href') } : String(node);
    const snapshot = label => ({ label, at: performance.now(), active: describe(document.activeElement), documentHasFocus: document.hasFocus(), hash: location.hash, scrollY, target: describe(target), targetRect: target?.getBoundingClientRect().toJSON(), linkRect: link.getBoundingClientRect().toJSON() });
    const trace = window.__tsNavigationDiagnostic = { snapshots: [snapshot('before-click')], events: [] };
    const record = (where, capture) => event => {
      if (!link.contains(event.target)) return;
      trace.events.push({ where, capture, phase: event.eventPhase, defaultPrevented: event.defaultPrevented, target: describe(event.target), active: describe(document.activeElement), at: performance.now() });
    };
    // Install after load: existing theme listeners retain their original order.
    for (const [where, node] of [['window', window], ['document', document], ['navigation', link.closest('.ts-jump')], ['link', link]]) {
      node.addEventListener('click', record(where, true), true);
      node.addEventListener('click', record(where, false));
    }
    link.addEventListener('click', event => {
      queueMicrotask(() => { trace.events.push({ where: 'after-dispatch', phase: event.eventPhase, defaultPrevented: event.defaultPrevented }); trace.snapshots.push(snapshot('after-dispatch')); });
      requestAnimationFrame(() => trace.snapshots.push(snapshot('animation-frame')));
      setTimeout(() => trace.snapshots.push(snapshot('200ms')), 200);
      setTimeout(() => trace.snapshots.push(snapshot('1000ms')), 1000);
    }, { capture: true, once: true });
  });
  await first.click();
  await page.waitForTimeout(1150);
  return page.evaluate(() => window.__tsNavigationDiagnostic);
}

try {
  const context = await makeContext();
  for (const path of ['/services/', '/contact/', '/privacy-policy/']) {
    const page = await load(context, path);
    const sources = await page.locator('script[src]').evaluateAll(nodes => nodes.map(node => node.src));
    for (const source of sources) {
      const url = new URL(source);
      if (url.origin === base && !url.username && !url.password) scriptURLs.add(url.href);
    }
    const entry = { path, html: await page.content(), scriptSources: sources.filter(source => new URL(source).origin === base) };
    if (path === '/services/') entry.clickTrace = await traceClick(page);
    report.pages.push(entry);
    await page.close();
  }
  // Prioritize Astra in the bounded source capture. Never follow asset redirects.
  const sources = [...scriptURLs].sort((a, b) => Number(/astra.*frontend/i.test(b)) - Number(/astra.*frontend/i.test(a))).slice(0, 30);
  report.scriptLimit = { discovered: scriptURLs.size, captured: sources.length, maximum: 30 };
  for (const url of sources) {
    if (invalid) throw new Error('INVALID: hosting challenge; no further requests');
    const response = await context.request.get(url, { maxRedirects: 0, timeout: 30000 });
    if (response.status() === 202) { invalid = true; throw new Error('INVALID: hosting challenge; no further requests'); }
    if (response.status() !== 200 || new URL(response.url()).origin !== base) throw new Error('Rejected public script response');
    const source = await response.text();
    if (/^\s*(?:<!doctype html|<html)/i.test(source) || /Robot Challenge/i.test(source.slice(0, 4000))) throw new Error('Rejected HTML response for public script');
    if (Buffer.byteLength(source) > 5 * 1024 * 1024) throw new Error('Public script exceeds 5 MiB diagnostic bound');
    report.scripts.push({ url, source });
    await response.dispose();
  }
  await context.close();
  if (process.argv.includes('--no-js')) {
    const noJS = await makeContext(false);
    for (const path of ['/services/', '/contact/', '/privacy-policy/']) {
      const page = await load(noJS, path);
      const samples = [];
      for (let index = 0; index < 5; index++) {
        samples.push(await page.locator('.entry-content .ts-jump a').first().evaluate(link => ({ at: performance.now(), fonts: document.fonts.status, linkRect: link.getBoundingClientRect().toJSON(), navRect: link.closest('.ts-jump').getBoundingClientRect().toJSON(), scrollY, width: document.documentElement.scrollWidth })));
        await page.waitForTimeout(200);
      }
      report.noJS.push({ path, samples });
      await page.close();
    }
    await noJS.close();
  }
  if (invalid) throw new Error('INVALID: hosting challenge; no further requests');
  const encoded = gzipSync(Buffer.from(JSON.stringify(report))).toString('base64');
  console.log('NAVDEBUG-BEGIN');
  for (let offset = 0; offset < encoded.length; offset += 200000) console.log(encoded.slice(offset, offset + 200000));
  console.log('NAVDEBUG-END');
} catch (error) {
  // Avoid printing request diagnostics, headers, browser storage, or response bodies.
  console.error(invalid ? 'INVALID: hosting challenge; capture stopped' : 'FAIL: public navigation diagnostic did not complete');
  process.exitCode = invalid ? 2 : 1;
} finally {
  await browser.close();
}

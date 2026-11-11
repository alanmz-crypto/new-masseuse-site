// Actual-page regression for title-strip observer delivery. No runtime/data
// injection and no ignored page errors. This is engine evidence, not Safari.
import { createRequire } from 'node:module';
import { RESIZE_SLUGS, RESIZE_SIDES, RESIZE_LIMITATION } from './core-title-resize/contract.mjs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['http://localhost:8090', 'http://localhost:8091', 'https://staging39.thaisolitude.com'].includes(base)) throw new Error('staging39 or isolated localhost only');
const flags = process.argv.slice(3);
if (flags.length > 1 || flags.some(flag => !['--firefox', '--webkit'].includes(flag))) throw new Error('expected at most one of --firefox or --webkit');
const engine = flags.includes('--webkit') ? 'webkit' : flags.includes('--firefox') ? 'firefox' : 'chromium';
const started = Date.now();
let browser, passed = 0, failed = 0, checks = 0, completed = 0, visits = 0, captures = 0;
const cases = RESIZE_SLUGS.flatMap(slug => RESIZE_SIDES.map(hand => ({ slug, hand })));
const check = (caseId, label, condition, detail) => {
  checks++; condition ? passed++ : failed++;
  console.log(`${condition ? 'PASS' : 'FAIL'} title-resize ${engine}/${caseId}/${label} [check ${checks}]${!condition && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const stable = async p => {
  await p.evaluate(() => { window.__peekResizeStable = null; });
  await p.waitForFunction(() => {
    const rect = selector => { const node = document.querySelector(selector); if (!node) return null; const r = node.getBoundingClientRect(); return [r.left, r.top, r.width, r.height]; };
    const key = JSON.stringify([innerWidth, innerHeight, visualViewport?.width, visualViewport?.height,
      document.documentElement.scrollWidth, document.documentElement.scrollHeight, rect('#masthead'), rect('.ts-pk-top'), rect('.ts-pk-top .in')]);
    const previous = window.__peekResizeStable;
    if (!previous || previous.key !== key) window.__peekResizeStable = { key, since: performance.now() };
    return document.fonts.status === 'loaded' && performance.now() - window.__peekResizeStable.since >= 250;
  }, null, { polling: 'raf', timeout: 5000 });
};
const geometry = p => p.evaluate(() => {
  const strip = document.querySelector('.ts-pk-top'), inner = strip.querySelector('.in'), head = document.getElementById('masthead');
  const rect = node => { const r = node.getBoundingClientRect(); return { width: r.width, height: r.height }; };
  const labels = [...strip.querySelectorAll('.before,.tr span')].map(node => {
    const range = document.createRange(); range.selectNodeContents(node);
    const lines = [...range.getClientRects()].filter(r => r.width > 0 && r.height > 0);
    return { text: node.textContent, height: lines.length ? Math.max(...lines.map(r => r.bottom)) - Math.min(...lines.map(r => r.top)) : 0 };
  });
  const toggle = document.querySelector('#ast-mobile-header .menu-toggle.main-header-menu-toggle');
  const panel = document.querySelector('#ast-mobile-header .ast-mobile-header-content');
  return { strip: rect(strip), inner: rect(inner), head: head && rect(head), labels,
    font: getComputedStyle(document.documentElement).fontSize, inlineFont: document.documentElement.style.fontSize,
    headOffset: parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--pk-head')) || 0,
    panelHeight: panel?.offsetHeight || 0, expanded: toggle?.getAttribute('aria-expanded'),
    destinations: [...document.querySelectorAll('.ts-pk-rail [data-ts-pk-section]')].map(node => ({ text: node.getAttribute('aria-label'), href: node.getAttribute('href') })),
    preview: !!document.querySelector('.ts-pk-card.is-on'),
    visual: { width: visualViewport?.width || document.documentElement.clientWidth, height: visualViewport?.height || document.documentElement.clientHeight } };
});
const readable = g => g.inner.height > 0 && g.labels.length >= 3 && g.labels.every(label => label.height > 0 && label.height <= g.inner.height + 1);
const sameSize = (a, b) => Math.abs(a.width - b.width) <= 1 && Math.abs(a.height - b.height) <= 1;
const capture = async (p, slug, hand, state) => {
  const bytes = await p.screenshot({ type: 'jpeg', quality: 80 }); captures++;
  console.log(`INLINE peek-title-resize-${engine}-${slug}-${hand}-${state}.jpg base64:${bytes.toString('base64')}`);
};
const pressMenu = async p => {
  const toggle = p.locator('#ast-mobile-header .menu-toggle.main-header-menu-toggle');
  if (await toggle.count() !== 1) throw new Error('Native mobile menu toggle is missing or duplicated');
  if (engine === 'firefox') { await toggle.click(); return; }
  const at = await toggle.evaluate(node => {
    const r = node.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
    if (r.width < 1 || r.height < 1 || !node.contains(document.elementFromPoint(x, y))) throw new Error('Native mobile menu is not hit-testable');
    return { x, y };
  });
  await p.touchscreen.tap(at.x, at.y);
};
try {
  browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  for (const { slug, hand } of cases) {
    if (visits && base.startsWith('https:')) await new Promise(resolve => setTimeout(resolve, 5500));
    const caseId = `${slug}/${hand}`, viewport = { width: 390, height: 664 };
    const context = await browser.newContext({ viewport, screen: viewport, deviceScaleFactor: 1, hasTouch: true,
      ...(engine === 'firefox' ? {} : { isMobile: true }), reducedMotion: 'reduce' });
    await context.addInitScript(hand => { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', hand); }, hand);
    const p = await context.newPage(), errors = [];
    p.on('pageerror', error => errors.push(error.message));
    const response = await p.goto(`${base}/${slug}/`, { waitUntil: 'load', timeout: 45000 });
    const arrived = new URL(p.url());
    if (!response || response.status() !== 200 || arrived.origin !== base || arrived.pathname !== `/${slug}/` || arrived.search || arrived.hash || /Robot Challenge/i.test(await p.title())) throw new Error(`${caseId}: expected exact same-origin route with 200 and no challenge`);
    visits++;
    await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { state: 'attached', timeout: 10000 });
    await stable(p);
    const before = await geometry(p), originalFont = await p.locator('html').evaluate(root => root.style.fontSize);
    if (!readable(before) || before.destinations.length < 2) throw new Error(`${caseId}: baseline section labels are missing or unreadable`);
    if (await p.locator('html').evaluate(root => root.classList.contains('ts-pk-left')) !== (hand === 'left')) throw new Error(`${caseId}: requested rail side was not applied`);
    for (let cycle = 1; cycle <= 3; cycle++) {
      await p.locator('html').evaluate(root => { root.style.fontSize = '150%'; });
      await stable(p);
      const enlarged = await geometry(p);
      check(caseId, `font-${cycle}-enlarged`, enlarged.inlineFont === '150%' && parseFloat(enlarged.font) > parseFloat(before.font) && readable(enlarged), enlarged);
      if (cycle === 3) await capture(p, slug, hand, 'enlarged');
      await p.locator('html').evaluate((root, original) => { root.style.fontSize = original; }, originalFont);
      await stable(p);
      const restored = await geometry(p);
      check(caseId, `font-${cycle}-restored`, restored.font === before.font && readable(restored) && sameSize(restored.strip, before.strip) && sameSize(restored.inner, before.inner), { before, restored });
    }
    await p.setViewportSize({ width: 390, height: 564 }); await stable(p);
    const shorter = await geometry(p);
    check(caseId, 'viewport-shorter', Math.abs(shorter.visual.height - 564) <= 1 && readable(shorter), shorter);
    await p.setViewportSize(viewport); await stable(p);
    const resized = await geometry(p);
    check(caseId, 'viewport-restored', Math.abs(resized.visual.height - 664) <= 1 && readable(resized) && sameSize(resized.strip, before.strip), { before, resized });
    await pressMenu(p); await stable(p);
    const opened = await geometry(p);
    check(caseId, 'menu-open', opened.expanded === 'true' && opened.panelHeight > 0 && readable(opened) && opened.head && Math.abs(opened.headOffset - (opened.head.height - opened.panelHeight)) <= 1, opened);
    await pressMenu(p); await stable(p);
    const closed = await geometry(p);
    check(caseId, 'menu-closed', closed.expanded === 'false' && closed.panelHeight === 0 && readable(closed) && sameSize(closed.strip, before.strip) && sameSize(closed.head, before.head), { before, closed });
    check(caseId, 'identity', !closed.preview && JSON.stringify(closed.destinations) === JSON.stringify(before.destinations) && JSON.stringify(closed.labels.map(label => label.text)) === JSON.stringify(before.labels.map(label => label.text)), { before, closed });
    await capture(p, slug, hand, 'restored');
    check(caseId, 'no-errors', errors.length === 0, errors);
    completed++; console.log(`INFO title-resize completed ${engine}/${caseId}`);
    await context.close();
  }
} catch (error) {
  console.log(`INVALID title-resize ${engine}: ${error.message}`); process.exitCode = 2;
} finally { if (browser) await browser.close(); }
console.log(`INFO Peek title resize ${engine}: ${passed} passed, ${failed} failed; ${completed}/4 cases, ${visits} page loads, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s; ${RESIZE_LIMITATION}`);
process.exitCode = process.exitCode || (failed ? 1 : 0);

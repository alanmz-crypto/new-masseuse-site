// Read-only probe: who scrolls the page when /thai-massage/#ts-guide-research is opened on staging39 (logs scroll calls with their source, over 3 seconds).
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (base !== 'https://staging39.thaisolitude.com') throw new Error('staging39 only');
const b = await chromium.launch();
const p = await (await b.newContext({ viewport: { width: 1280, height: 860 } })).newPage();
await p.addInitScript(() => {
  window.__log = []; const t0 = performance.now();
  const note = (what, extra) => window.__log.push(`${Math.round(performance.now() - t0)}ms ${what} ${extra || ''} y=${Math.round(scrollY)} :: ${(new Error().stack || '').split('\n').slice(2, 5).map(s => s.trim()).join(' <- ')}`);
  for (const [obj, name] of [[window, 'scrollTo'], [window, 'scroll'], [window, 'scrollBy'], [Element.prototype, 'scrollIntoView'], [Element.prototype, 'scrollTo']]) {
    const orig = obj[name]; obj[name] = function (...a) { note(name, JSON.stringify(a).slice(0, 80) + (this && this.id ? ' on #' + this.id : '')); return orig.apply(this, a); };
  }
  addEventListener('scroll', () => note('scroll-event'), { passive: true });
  new MutationObserver(ms => ms.forEach(m => m.attributeName === 'id' && m.target.id === 'ts-guide-research' && note('id assigned', m.target.tagName))).observe(document, { subtree: true, attributes: true, attributeFilter: ['id'] });
});
const r = await p.goto(`${base}/thai-massage/#ts-guide-research`, { waitUntil: 'load' });
if (r.status() !== 200) { console.log('INVALID', r.status()); process.exit(2); }
await p.waitForTimeout(3000);
const log = await p.evaluate(() => window.__log);
console.log(log.filter((l, i) => !l.includes('scroll-event') || i < 3 || !log[i - 1].includes('scroll-event')).slice(0, 60).join('\n'));
console.log('final', await p.evaluate(() => JSON.stringify({ y: scrollY, h: Math.round(document.getElementById('ts-guide-research').getBoundingClientRect().top), sm: getComputedStyle(document.getElementById('ts-guide-research')).scrollMarginTop, runtime: !!document.getElementById('ts-pill-nav-runtime') })));
await b.close();

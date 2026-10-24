// Diagnostic (read-only): on the phone view of the Deep Tissue page, Tab through the page and report, for every focused element whose centre is covered by another element, WHAT covers it.
// Usage: node probe-covered.mjs <base-url>
import { createRequire } from 'module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
const b = await chromium.launch();
const ctx = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
const page = await ctx.newPage();
await page.goto(base + '/deep-tissue-massage/', { waitUntil: 'networkidle' });
const seen = new Set();
for (let i = 0; i < 90; i++) {
  await page.keyboard.press('Tab');
  await page.waitForTimeout(450);
  const s = await page.evaluate(() => {
    const e = document.activeElement; if (!e || e === document.body) return null;
    const r = e.getBoundingClientRect();
    const cx = Math.min(Math.max(r.left + r.width / 2, 1), innerWidth - 2), cy = Math.min(Math.max(r.top + r.height / 2, 1), innerHeight - 2);
    const top = document.elementFromPoint(cx, cy);
    const cov = !!top && !(e === top || e.contains(top) || top.contains(e));
    const d = n => n ? n.tagName + (n.id ? '#' + n.id : '') + '.' + String(n.className).split(' ').slice(0, 3).join('.') + ' pos=' + getComputedStyle(n).position : null;
    return { id: (e.textContent || '').trim().slice(0, 24) + '@' + Math.round(r.top + scrollY), cov, rectTop: Math.round(r.top), rectBottom: Math.round(r.bottom), vh: innerHeight, left: Math.round(r.left), right: Math.round(r.right), el: d(e), over: cov ? d(top) : null, overRect: cov ? JSON.stringify(top.getBoundingClientRect()) : null };
  });
  if (!s) continue; if (seen.has(s.id)) break; seen.add(s.id);
  if (s.cov) console.log('COVERED', JSON.stringify(s));
}
console.log('stops', seen.size);
await b.close();

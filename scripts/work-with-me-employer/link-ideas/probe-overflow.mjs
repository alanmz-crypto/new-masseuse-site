// Read-only probe (Ryan, 2026-11-09: on a larger, newer iPhone, after navigating with the rail and jumping to each section, the text sits at the screen's
// left edge with no margin; not seen on an iPhone SE, which used the left-hand setting). Measures, per phone width and rail side: whether the page is wider
// than the screen, which elements reach past the right edge, and the sideways scroll position after a series of rail jumps.
// node probe-overflow.mjs https://staging39.thaisolitude.com|http://localhost:8090
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090', 'http://localhost:8091'].includes(base)) throw new Error('staging39 or localhost only');
const b = await chromium.launch();
const sizes = [['iPhone SE', 375, 667], ['iPhone 16/17', 402, 874], ['iPhone 16/17 Pro Max', 440, 956]];
for (const [name, w, h] of sizes) for (const side of ['right', 'left']) {
  const ctx = await b.newContext({ ...devices['iPhone 13'], viewport: { width: w, height: h }, deviceScaleFactor: 1 });
  await ctx.addInitScript(s => { try { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', s); } catch (e) {} }, side);
  const p = await ctx.newPage();
  const r = await p.goto(base + '/thai-massage/', { waitUntil: 'load' });
  if (!r || r.status() !== 200) { console.log(`INVALID answered ${r && r.status()}`); await b.close(); process.exit(2); }
  await p.waitForTimeout(1500);
  const cdp = await ctx.newCDPSession(p);
  const measure = () => p.evaluate(() => {
    const vw = document.documentElement.clientWidth, sw = document.documentElement.scrollWidth, wide = [];
    document.querySelectorAll('body *').forEach(e => { const r = e.getBoundingClientRect(); if (r.width && r.right > vw + 0.5 && getComputedStyle(e).position !== 'fixed' && !e.closest('.ts-pk-card,.ts-pk-nav,.ts-pk-tip')) wide.push((e.id ? '#' + e.id : e.tagName.toLowerCase() + '.' + [...e.classList].slice(0, 2).join('.')) + ' ' + Math.round(r.right)); });
    const c = document.querySelector('.entry-content'), cs = c && getComputedStyle(c);
    return { vw, sw, sx: Math.round(scrollX), pad: cs && [cs.paddingLeft, cs.paddingRight].join('/'), clear: getComputedStyle(document.documentElement).getPropertyValue('--pk-clear'), wide: wide.slice(0, 8) };
  });
  const before = await measure();
  const n = await p.$$eval('.ts-pk-rail li', l => l.length);
  for (let k = 0; k < n; k++) {
    const z = await p.evaluate(i => { const r = document.querySelectorAll('.ts-pk-rail li')[i].getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; }, k);
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [z] }); await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] }); await p.waitForTimeout(600);
    const o = await p.evaluate(() => { const r = document.querySelector('.ts-pk-card .open > b').getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [o] }); await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] }); await p.waitForTimeout(1500);
  }
  const after = await measure();
  console.log(`${name} ${w}px, rail ${side}: before ${JSON.stringify(before)}\n    after ${n} jumps ${JSON.stringify(after)}`);
  await ctx.close();
}
console.log('RESULT: probed');
await b.close();

// Read-only browser check of the practice page /index-nav-ideas/ on staging39: the index icon above the side rail, five designs, real touch input.
// Each design: press and hold the icon = every section name slides out of the rail (the page does not move); lift = they slide back and disappear.
// node check-index-ideas.mjs https://staging39.thaisolitude.com     (Chromium; phone + tablet, light + dark)
// Exit 0 = every check passed; 1 = a check failed; 2 = INVALID (the hosting bot challenge answered; nothing concluded).
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090'].includes(base)) throw new Error('staging39 or localhost:8090 only');
let pass = 0, fail = 0;
const ok = (name, cond, detail = '') => { if (cond) pass++; else fail++; console.log(`${cond ? 'PASS' : 'FAIL'} ${name}${detail !== '' ? `  [${detail}]` : ''}`); };
const names = ['What it is', 'Common reasons', 'What to expect', 'Questions', 'What Research Says'];
const b = await chromium.launch();
for (const [size, dev] of [['phone', devices['iPhone 13']], ['tablet', devices['iPad Mini']]]) {
  for (const theme of ['light', 'dark']) {
    const tag = `${size}/${theme}`;
    const ctx = await b.newContext({ ...dev, deviceScaleFactor: 1 });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const p = await ctx.newPage(); const errors = []; p.on('pageerror', e => errors.push(e.message));
    const r = await p.goto(`${base}/index-nav-ideas/`, { waitUntil: 'load' });
    if (!r || r.status() !== 200) { console.log(`INVALID ${tag}: answered ${r && r.status()} (202 = hosting challenge)`); await b.close(); process.exit(2); }
    const cdp = await ctx.newCDPSession(p);
    const touch = (type, x, y) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ x, y }] });
    const W = ms => p.waitForTimeout(ms);
    const Y = () => p.evaluate(() => Math.round(scrollY));
    if (theme === 'dark') ok(`${tag} site dark theme active`, await p.evaluate(() => document.documentElement.getAttribute('data-ts-theme') === 'dark'));
    const state = () => p.evaluate(() => {
      const nav = document.querySelector('.tsi-nav'), segs = [...document.querySelectorAll('.tsi-rail li')].map(l => { const r = l.getBoundingClientRect(); return r.top + r.height / 2; });
      const items = [...document.querySelectorAll('.tsi-ix li')].map((l, k) => { const r = l.getBoundingClientRect(), cs = getComputedStyle(l); return { name: l.querySelector('span').textContent, op: +cs.opacity, mid: r.top + r.height / 2, left: r.left, right: r.right, now: l.classList.contains('is-now'), seg: segs[k] }; });
      return { open: nav.classList.contains('is-open'), exp: document.querySelector('.tsi-icon').getAttribute('aria-expanded'), items, vw: innerWidth };
    });
    for (let n = 1; n <= 5; n++) {
      const dt = `${tag} design ${n}`;
      await p.evaluate(() => scrollTo(0, 0)); await W(200); await p.tap(`.tsi-picker button[data-idea="${n}"]`); await W(200);
      ok(`${dt}: its own icon shows`, await p.evaluate(k => [...document.querySelectorAll('.tsi-icon svg')].filter(s => getComputedStyle(s).display !== 'none').map(s => s.getAttribute('data-for')).join() === String(k), n));
      await p.evaluate(() => scrollTo(0, document.getElementById('tsi-common-reasons').getBoundingClientRect().top + scrollY + 150)); await W(500);
      const geo = await p.evaluate(() => { const i = document.querySelector('.tsi-icon').getBoundingClientRect(), r = document.querySelector('.tsi-rail').getBoundingClientRect(); return { gap: Math.round(r.top - i.bottom), dx: Math.round(Math.abs(i.right - r.right)), w: Math.round(i.width), h: Math.round(i.height) }; });
      ok(`${dt}: the icon sits directly above the rail`, geo.gap >= 0 && 16 >= geo.gap && 2 >= geo.dx && geo.w >= 44 && geo.h >= 44, JSON.stringify(geo));
      let st = await state();
      ok(`${dt}: closed at rest (no names showing)`, !st.open && st.items.every(i => 0.05 >= i.op));
      const ic = await p.$eval('.tsi-icon', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
      const y0 = await Y();
      await touch('touchStart', ic.x, ic.y); await W(800);
      st = await state();
      ok(`${dt}: pressing and holding the icon slides out every section name`, st.open && st.exp === 'true' && st.items.map(i => i.name).join('|') === names.join('|') && st.items.every(i => i.op > 0.95), JSON.stringify(st.items.map(i => i.op)));
      ok(`${dt}: every name is fully on screen`, st.items.every(i => i.left >= 0 && st.vw >= i.right), JSON.stringify(st.items.map(i => [Math.round(i.left), Math.round(i.right)])));
      ok(`${dt}: the section being read is highlighted`, st.items.filter(i => i.now).map(i => i.name).join() === 'Common reasons');
      if (n === 2) ok(`${dt}: the names unfold as a list below the icon, in order`, st.items.every((i, k) => k === 0 || i.mid > st.items[k - 1].mid) && st.items[0].mid > ic.y, JSON.stringify(st.items.map(i => Math.round(i.mid))));
      else ok(`${dt}: each name sits beside its own piece of the rail${n === 5 ? ' (tilted along the arc)' : ''}`, st.items.every(i => (n === 5 ? 14 : 8) >= Math.abs(i.mid - i.seg)), JSON.stringify(st.items.map(i => Math.round(i.mid - i.seg))));
      ok(`${dt}: the page does not move while the names are out`, (await Y()) === y0);
      await touch('touchEnd'); await W(700);
      st = await state();
      ok(`${dt}: lifting the finger slides them back into the rail`, !st.open && st.exp === 'false' && st.items.every(i => 0.05 >= i.op), JSON.stringify(st.items.map(i => i.op)));
      ok(`${dt}: ...and the page still has not moved`, (await Y()) === y0);
    }
    ok(`${tag} a long press cannot open the browser's menu`, await p.evaluate(() => { const ev = new MouseEvent('contextmenu', { bubbles: true, cancelable: true }); document.querySelector('.tsi-icon').dispatchEvent(ev); return ev.defaultPrevented; }));
    await p.focus('.tsi-icon'); await p.keyboard.press('Enter'); await W(500);
    ok(`${tag} keyboard and screen readers: Enter (a click without a press) opens the index`, (await state()).open);
    await p.keyboard.press('Escape'); await W(500);
    ok(`${tag} Escape closes it and keeps focus on the icon`, !(await state()).open && (await p.evaluate(() => document.activeElement === document.querySelector('.tsi-icon'))));
    ok(`${tag} no sideways page scroll`, await p.evaluate(() => innerWidth + 1 >= document.documentElement.scrollWidth));
    ok(`${tag} no JavaScript errors`, errors.length === 0, errors.join(' | '));
    await ctx.close();
  }
}
await b.close();
console.log(`\nchromium: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

// Read-only browser check of the pinned header + sticky pills on staging39 /thai-massage/, used as a visitor would. Also checks one other page is unchanged.
// node check-pill-nav.mjs https://staging39.thaisolitude.com     (Chromium; five page loads: phone + desktop, light + dark, plus Therapeutic Massage)
// Exit 0 = every check passed; 1 = a check failed; 2 = INVALID (the hosting bot challenge answered; nothing concluded).
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (base !== 'https://staging39.thaisolitude.com') throw new Error('staging39 only');
let pass = 0, fail = 0;
const ok = (name, cond, detail = '') => { if (cond) pass++; else fail++; console.log(`${cond ? 'PASS' : 'FAIL'} ${name}${detail !== '' ? `  [${detail}]` : ''}`); };
const b = await chromium.launch();
const load = async (ctx, path) => { const p = await ctx.newPage(); const errors = []; p.on('pageerror', e => errors.push(e.message));
  const r = await p.goto(base + path, { waitUntil: 'load' }); if (!r || r.status() !== 200) { console.log(`INVALID ${path} answered ${r && r.status()}`); await b.close(); process.exit(2); } return { p, errors }; };
const geo = p => p.evaluate(() => { const h = document.getElementById('masthead').getBoundingClientRect(), n = document.querySelector('.ts-jump').getBoundingClientRect();
  return { headTop: Math.round(h.top), headBottom: Math.round(h.bottom), navTop: Math.round(n.top), navBottom: Math.round(n.bottom) }; });
const labels = ['What it is', 'Common reasons', 'What to expect', 'Questions', 'What Research Says'];
for (const [size, opts] of [['phone', devices['iPhone 13']], ['desktop', { viewport: { width: 1280, height: 860 } }]]) {
  for (const theme of ['light', 'dark']) {
    const tag = `${size}/${theme}`;
    const ctx = await b.newContext({ ...opts, deviceScaleFactor: 1 });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const { p, errors } = await load(ctx, '/thai-massage/');
    const wait = ms => p.waitForTimeout(ms);
    ok(`${tag} the row has the five links in order`, (await p.$$eval('.ts-jump__list a', a => a.map(x => x.textContent).join('|'))) === labels.join('|'));
    ok(`${tag} no sideways page scroll`, await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    await p.evaluate(() => scrollTo(0, 2500)); await wait(600);
    let g = await geo(p);
    ok(`${tag} header stays on top after scrolling`, g.headTop === 0, JSON.stringify(g));
    ok(`${tag} pills stuck right under the header`, Math.abs(g.navTop - g.headBottom) <= 1, JSON.stringify(g));
    ok(`${tag} a pill is highlighted while reading`, (await p.$$eval('.ts-jump__list a[aria-current="true"]', a => a.length)) === 1);
    ok(`${tag} the bottom guide button is hidden`, await p.evaluate(() => { const t = document.getElementById('ts-section-guide-trigger'); return !t || getComputedStyle(t).display === 'none'; }));
    for (const label of ['Questions', 'What Research Says', 'What it is']) {
      await p.click(`.ts-jump__list a:text-is("${label}")`); await wait(1600);
      const r = await p.evaluate(l => { const a = [...document.querySelectorAll('.ts-jump__list a')].find(x => x.textContent === l); const t = document.getElementById(a.getAttribute('href').slice(1)).getBoundingClientRect();
        const n = document.querySelector('.ts-jump').getBoundingClientRect(); return { headingTop: Math.round(t.top), navBottom: Math.round(n.bottom), active: (document.querySelector('.ts-jump__list a[aria-current="true"]') || {}).textContent, ind: getComputedStyle(document.querySelector('.ts-pill-ind')).opacity }; }, label);
      ok(`${tag} "${label}": heading lands just below the pills, not under them`, r.headingTop >= r.navBottom - 1 && r.headingTop <= r.navBottom + 60, JSON.stringify(r));
      ok(`${tag} "${label}": that pill is highlighted`, r.active === label && r.ind === '1', JSON.stringify(r));
    }
    if (size === 'phone') {
      await p.evaluate(() => scrollTo(0, 1800)); await wait(400);
      const toggle = p.locator('#ast-mobile-header .menu-toggle, #ast-mobile-header .ast-mobile-menu-trigger-minimal').first();
      if (await toggle.count()) { await toggle.click(); await wait(600);
        const m = await p.evaluate(() => { const c = document.querySelector('#ast-mobile-header .ast-mobile-header-content'); if (!c) return null; const r = c.getBoundingClientRect(); return { shown: getComputedStyle(c).display !== 'none' && r.height > 0, top: Math.round(r.top), bottom: Math.round(r.bottom), vh: innerHeight }; });
        ok(`${tag} menu opens from the pinned header mid-page and fits the screen`, !!m && m.shown && m.top >= 0 && m.bottom <= m.vh + 1, JSON.stringify(m));
        await toggle.click(); await wait(400);
      } else ok(`${tag} mobile menu button found`, false);
    }
    ok(`${tag} no JavaScript errors`, errors.length === 0, errors.join(' | '));
    await ctx.close();
  }
}
for (const [size, opts] of [['phone', devices['iPhone 13']], ['desktop', { viewport: { width: 1280, height: 860 } }]]) {
  const c = await b.newContext({ ...opts, deviceScaleFactor: 1 });
  const { p } = await load(c, '/thai-massage/#ts-guide-research'); await p.waitForTimeout(1500);
  const r = await p.evaluate(() => ({ headingTop: Math.round(document.getElementById('ts-guide-research').getBoundingClientRect().top), navBottom: Math.round(document.querySelector('.ts-jump').getBoundingClientRect().bottom) }));
  ok(`${size} arriving at /thai-massage/#ts-guide-research: heading below the pills`, r.headingTop >= r.navBottom - 1 && r.headingTop <= r.navBottom + 60, JSON.stringify(r));
  await c.close();
}
const ctx = await b.newContext({ viewport: { width: 1280, height: 860 } });
const { p } = await load(ctx, '/therapeutic-massage/');
await p.evaluate(() => scrollTo(0, 2500)); await p.waitForTimeout(400);
ok('Therapeutic Massage unchanged: header scrolls away as before', await p.evaluate(() => document.getElementById('masthead').getBoundingClientRect().bottom <= 0 && !document.getElementById('ts-pill-nav-runtime')));
await b.close();
console.log(`\nchromium: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

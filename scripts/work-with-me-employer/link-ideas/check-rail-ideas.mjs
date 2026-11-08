// Read-only browser check of the practice page /rail-nav-ideas/ on staging39: each of the five side-rail ideas used with real touch input.
// For every idea: browsing never moves the page; it only jumps when the visitor chooses; "Back to ..." returns to the exact place.
// node check-rail-ideas.mjs https://staging39.thaisolitude.com     (Chromium; four page loads: phone + tablet, light + dark)
// Exit 0 = every check passed; 1 = a check failed; 2 = INVALID (the hosting bot challenge answered; nothing concluded).
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090'].includes(base)) throw new Error('staging39 or localhost:8090 only');
let pass = 0, fail = 0;
const ok = (name, cond, detail = '') => { if (cond) pass++; else fail++; console.log(`${cond ? 'PASS' : 'FAIL'} ${name}${detail !== '' ? `  [${detail}]` : ''}`); };
const b = await chromium.launch();
for (const [size, dev] of [['phone', devices['iPhone 13']], ['tablet', devices['iPad Mini']]]) {
  for (const theme of ['light', 'dark']) {
    const tag = `${size}/${theme}`;
    const ctx = await b.newContext({ ...dev, deviceScaleFactor: 1 });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const p = await ctx.newPage(); const errors = []; p.on('pageerror', e => errors.push(e.message));
    const r = await p.goto(`${base}/rail-nav-ideas/`, { waitUntil: 'load' });
    if (!r || r.status() !== 200) { console.log(`INVALID ${tag}: answered ${r && r.status()} (202 = hosting challenge)`); await b.close(); process.exit(2); }
    const cdp = await ctx.newCDPSession(p);
    const touch = (type, x, y) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ x, y }] });
    const W = ms => p.waitForTimeout(ms);
    const Y = () => p.evaluate(() => Math.round(scrollY));
    const seg = i => p.evaluate(k => { const r = document.querySelectorAll('.tsr-rail li')[k].getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; }, i);
    const slideTo = async (i, { lift = true, pullX = 0, holdMs = 0 } = {}) => {   // thumb down on section 1, slide to section i (+ optional pull inwards), optional hold, optional lift
      const a = await seg(0), z = await seg(i);
      await touch('touchStart', a.x, a.y);
      for (let k = 1; k <= 8; k++) { await touch('touchMove', a.x, a.y + (z.y - a.y) * k / 8); await W(30); }
      for (let k = 1; k <= 6 && pullX; k++) { await touch('touchMove', z.x - pullX * k / 6, z.y); await W(30); }
      if (holdMs) await W(holdMs);
      if (lift) { await touch('touchEnd'); await W(600); }
    };
    const card = () => p.evaluate(() => { const c = document.querySelector('.tsr-card'); return { on: c.classList.contains('is-on'), pinned: c.classList.contains('is-pinned'), armed: c.classList.contains('is-armed'), name: (c.querySelector('b') || {}).textContent || '', blurb: (c.querySelector('.d') || {}).textContent || '', shot: !!c.querySelector('.shot'), inView: c.getBoundingClientRect().left >= 0 }; });
    const landed = id => p.evaluate(i => { const t = document.getElementById(i).getBoundingClientRect().top, sb = document.querySelector('.tsr-top').getBoundingClientRect().bottom; return t >= sb - 2 && sb + 40 >= t; }, id);   // just below the top strip
    const strip = () => p.evaluate(() => { const t = document.querySelector('.tsr-top'); return t.querySelector('.tsr-top-name').textContent + ' / ' + t.querySelector('.tsr-top-count').textContent; });
    const pick = async n => { await p.evaluate(() => scrollTo(0, 0)); await W(200); await p.tap(`.tsr-picker button[data-idea="${n}"]`); await W(250);
      await p.evaluate(() => scrollTo(0, document.getElementById('tsr-common-reasons').getBoundingClientRect().top + scrollY + 80)); await W(500); };
    const backWorks = async (y0, what) => {
      ok(`${tag} ${what}: a Back button names where you were`, await p.$eval('.tsr-back', b => !b.hidden && b.textContent.includes('Common reasons')));
      await W(400); await p.tap('.tsr-back'); await W(1500);
      const y1 = await Y(); ok(`${tag} ${what}: Back returns to exactly where you were`, 3 >= Math.abs(y1 - y0), `${y0} -> ${y1}`);
    };
    ok(`${tag} script ran`, await p.$eval('#tsr-lab', e => e.classList.contains('is-js')));
    if (theme === 'dark') ok(`${tag} site dark theme active`, await p.evaluate(() => document.documentElement.getAttribute('data-ts-theme') === 'dark'));
    await pick(1);
    const rail = await p.$eval('.tsr-rail', e => { const r = e.getBoundingClientRect(); return { top: r.top, bottom: r.bottom, right: r.right, vh: innerHeight, vw: innerWidth }; });
    ok(`${tag} rail sits on the right edge in the thumb zone (lower part of the screen)`, rail.top >= rail.vh * 0.25 && rail.vh >= rail.bottom && rail.right >= rail.vw - 12, JSON.stringify(rail));
    let y0;

    // the top strip: display only
    const st = await p.evaluate(() => { const t = document.querySelector('.tsr-top'), r = t.getBoundingClientRect(), cs = getComputedStyle(t); return { top: Math.round(r.top), h: Math.round(r.height), pe: cs.pointerEvents, controls: t.querySelectorAll('a,button,input,[tabindex]').length, hidden: t.getAttribute('aria-hidden'), fills: [...t.querySelectorAll('li i')].map(i => +i.style.getPropertyValue('--p')) }; });
    ok(`${tag} top strip stays at the top while reading`, st.top === 0 && st.h > 20 && 70 >= st.h, JSON.stringify(st));
    ok(`${tag} top strip names the current topic and position`, (await strip()) === 'Common reasons / 2 of 6', await strip());
    ok(`${tag} top strip shows progress per section`, st.fills[0] === 1 && st.fills[1] > 0 && 1 > st.fills[1] && st.fills[2] === 0, st.fills.join(' '));
    ok(`${tag} top strip is display only (no controls, ignores touches, hidden from screen readers since the rail has the links)`, st.pe === 'none' && st.controls === 0 && st.hidden === 'true', JSON.stringify(st));
    y0 = await Y(); await touch('touchStart', 120, 20); await touch('touchEnd'); await W(500);
    ok(`${tag} touching the top strip does nothing`, (await Y()) === y0 && !(await card()).on);

    // 1. slide, lift, choose
    y0 = await Y(); await slideTo(3, { lift: false });
    let c = await card(); ok(`${tag} 1: sliding shows the section under the thumb and what it covers`, c.on && c.name === 'Questions' && c.blurb.length > 10 && c.inView, JSON.stringify(c));
    ok(`${tag} 1: the page does not move while browsing`, (await Y()) === y0);
    await touch('touchEnd'); await W(600);
    c = await card(); ok(`${tag} 1: lifting keeps the card with Go/Stay, no jump`, c.pinned && c.name === 'Questions' && (await Y()) === y0, JSON.stringify(c));
    await p.tap('.tsr-card .stay'); await W(400);
    ok(`${tag} 1: Stay here keeps the place`, !(await card()).on && (await Y()) === y0);
    await slideTo(3); await p.tap('.tsr-card .go'); await W(1500);
    ok(`${tag} 1: Go there jumps`, await landed('tsr-questions'));
    ok(`${tag} top strip follows the jump`, (await strip()) === 'Questions / 4 of 6', await strip());
    await backWorks(y0, '1');

    // 2. pull in to go
    await pick(2); y0 = await Y();
    await slideTo(3); ok(`${tag} 2: lifting without pulling stays put`, !(await card()).on && (await Y()) === y0);
    await slideTo(3, { lift: false, pullX: 110 });
    c = await card(); ok(`${tag} 2: pulling in arms the jump (card turns green)`, c.armed && c.name === 'Questions', JSON.stringify(c));
    ok(`${tag} 2: still no movement before letting go`, (await Y()) === y0);
    await touch('touchEnd'); await W(1500);
    ok(`${tag} 2: letting go when armed jumps`, await landed('tsr-questions'));
    await backWorks(y0, '2');

    // 3. peek
    await pick(3); y0 = await Y();
    await slideTo(3, { lift: false }); c = await card();
    ok(`${tag} 3: sliding shows a preview of the section`, c.on && c.shot && c.name === 'Questions', JSON.stringify(c));
    await touch('touchEnd'); await W(600);
    ok(`${tag} 3: lifting keeps the preview, no jump`, (await card()).pinned && (await Y()) === y0);
    await touch('touchStart', 12, Math.round(rail.vh * 0.12)); await touch('touchEnd');   // a tap on the page near the top-left, away from the rail and the card
    await W(400); ok(`${tag} 3: tapping elsewhere closes it and keeps the place`, !(await card()).on && (await Y()) === y0);
    await slideTo(3); await p.tap('.tsr-card .shot'); await W(1500);
    ok(`${tag} 3: tapping the preview jumps`, await landed('tsr-questions'));
    await backWorks(y0, '3');

    // 4. hold to go
    await pick(4); y0 = await Y();
    await slideTo(3, { holdMs: 300 });
    ok(`${tag} 4: lifting before the ring fills stays put`, (await Y()) === y0 && !(await card()).on);
    await slideTo(3, { lift: false, holdMs: 1700 });
    ok(`${tag} 4: holding still until the ring fills jumps (thumb still down)`, await landed('tsr-questions'));
    await touch('touchEnd'); await W(500);
    await backWorks(y0, '4');

    // 5. drawer
    await pick(5); y0 = await Y();
    ok(`${tag} 5: the rail is replaced by a short tab showing progress`, await p.evaluate(() => getComputedStyle(document.querySelector('.tsr-rail')).display === 'none' && getComputedStyle(document.querySelector('.tsr-tab')).display !== 'none'));
    await p.tap('.tsr-tab'); await W(500);
    const rows = await p.$$eval('.tsr-drawer a', a => a.map(x => x.querySelector('.num').textContent + '|' + x.querySelector('.t').textContent + '|' + x.querySelector('.d').textContent.length));
    ok(`${tag} 5: tapping the tab opens a drawer with every section, description and ticks`, (await p.$eval('.tsr-drawer', d => d.open)) && rows.length === 6 && rows[0].startsWith('✓|What it is') && rows.every(x => +x.split('|')[2] > 10), rows.join(' ; '));
    await p.evaluate(() => { document.querySelector('.tsr-drawer').scrollTop = 200; }); await W(200);
    ok(`${tag} 5: scrolling the drawer does not move the page`, (await Y()) === y0);
    const dr = await p.$eval('.tsr-drawer', d => { const r = d.getBoundingClientRect(); return { x: r.left + 40, y: r.top + r.height / 2 }; });
    await touch('touchStart', dr.x, dr.y); for (let k = 1; k <= 6; k++) { await touch('touchMove', dr.x + k * 20, dr.y); await W(25); } await touch('touchEnd'); await W(500);
    ok(`${tag} 5: swiping the drawer right closes it, place kept`, !(await p.$eval('.tsr-drawer', d => d.open)) && (await Y()) === y0);
    const tb = await p.$eval('.tsr-tab', t => { const r = t.getBoundingClientRect(); return { x: r.right - 10, y: r.top + r.height / 2 }; });
    await touch('touchStart', tb.x, tb.y); for (let k = 1; k <= 6; k++) { await touch('touchMove', tb.x - k * 12, tb.y); await W(25); } await touch('touchEnd'); await W(600);
    ok(`${tag} 5: swiping the tab left opens the drawer`, await p.$eval('.tsr-drawer', d => d.open));
    await p.tap('.tsr-drawer a[href="#tsr-questions"]'); await W(1500);
    ok(`${tag} 5: tapping a section closes the drawer and jumps`, !(await p.$eval('.tsr-drawer', d => d.open)) && await landed('tsr-questions'));
    await backWorks(y0, '5');

    ok(`${tag} no sideways page scroll`, await p.evaluate(() => innerWidth + 1 >= document.documentElement.scrollWidth));
    ok(`${tag} no JavaScript errors`, errors.length === 0, errors.join(' | '));
    await ctx.close();
  }
}
await b.close();
console.log(`\nchromium: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

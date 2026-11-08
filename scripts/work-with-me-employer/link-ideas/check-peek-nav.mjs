// Read-only browser check of the display-only top strip + Peek side rail on staging39 /thai-massage/, with real touch input. Also checks one other page is unchanged.
// node check-peek-nav.mjs https://staging39.thaisolitude.com     (Chromium; phone + tablet, light + dark, plus one #section arrival and Therapeutic Massage)
// Exit 0 = every check passed; 1 = a check failed; 2 = INVALID (the hosting bot challenge answered; nothing concluded).
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090'].includes(base)) throw new Error('staging39 or localhost:8090 only');
let pass = 0, fail = 0;
const ok = (name, cond, detail = '') => { if (cond) pass++; else fail++; console.log(`${cond ? 'PASS' : 'FAIL'} ${name}${detail !== '' ? `  [${detail}]` : ''}`); };
const labels = ['What it is', 'Common reasons', 'What to expect', 'Questions', 'What Research Says'];
const b = await chromium.launch();
const open = async (ctx, path) => { const p = await ctx.newPage(); const errors = []; p.on('pageerror', e => errors.push(e.message));
  const r = await p.goto(base + path, { waitUntil: 'load' }); if (!r || r.status() !== 200) { console.log(`INVALID ${path} answered ${r && r.status()}`); await b.close(); process.exit(2); } await p.waitForTimeout(400); return { p, errors }; };
for (const [size, dev] of [['phone', devices['iPhone 13']], ['tablet', devices['iPad Mini']]]) {
  for (const theme of ['light', 'dark']) {
    const tag = `${size}/${theme}`;
    const ctx = await b.newContext({ ...dev, deviceScaleFactor: 1 });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const { p, errors } = await open(ctx, '/thai-massage/');
    const cdp = await ctx.newCDPSession(p);
    const touch = (type, x, y) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ x, y }] });
    const W = ms => p.waitForTimeout(ms);
    const Y = () => p.evaluate(() => Math.round(scrollY));
    const seg = i => p.evaluate(k => { const r = document.querySelectorAll('.ts-pk-rail li')[k].getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; }, i);
    const slideTo = async (i, lift = true) => { const a = await seg(0), z = await seg(i); await touch('touchStart', a.x, a.y); for (let k = 1; k <= 8; k++) { await touch('touchMove', a.x, a.y + (z.y - a.y) * k / 8); await W(30); } if (lift) { await touch('touchEnd'); await W(600); } };
    const card = () => p.evaluate(() => { const c = document.querySelector('.ts-pk-card'); return { on: c.classList.contains('is-on'), pinned: c.classList.contains('is-pinned'), name: (c.querySelector('b') || {}).textContent || '', heading: (c.querySelector('.shot strong') || {}).textContent || '', excerpt: (c.querySelector('.shot em') || {}).textContent || '', desc: (c.querySelector('.d') || {}).textContent || '', lines: c.querySelectorAll('.shot > span').length, inView: c.getBoundingClientRect().left >= 0 }; });
    const strip = () => p.evaluate(() => { const t = document.querySelector('.ts-pk-top'); return t.querySelector('b').textContent + ' / ' + t.querySelector('span').textContent; });
    const landed = sel => p.evaluate(s => { const h = document.querySelector(s).getBoundingClientRect().top, sb = document.querySelector('.ts-pk-top').getBoundingClientRect().bottom; return { h: Math.round(h), sb: Math.round(sb), ok: h >= sb - 2 && sb + 40 >= h }; }, sel);
    const hrefOf = i => p.evaluate(k => document.querySelectorAll('.entry-content .ts-jump a')[k].getAttribute('href'), i);

    ok(`${tag} the row still has the five links in order (no-JavaScript fallback)`, (await p.$$eval('.entry-content .ts-jump a', a => a.map(x => x.textContent).join('|'))) === labels.join('|'));
    ok(`${tag} the pills trial is gone`, await p.evaluate(() => !document.getElementById('ts-pill-nav-runtime') && !document.querySelector('.ts-pill-ind')));
    const atTop = await p.evaluate(() => { const row = document.querySelector('.entry-content .ts-jump').getBoundingClientRect(), t = document.querySelector('.ts-pk-top'), r = t.getBoundingClientRect(), n = document.querySelector('.ts-pk-nav'); return { inFlow: t.parentNode === document.querySelector('.entry-content .ts-jump').parentNode, gap: Math.round(r.top - row.bottom), rail: getComputedStyle(n).visibility, railOp: getComputedStyle(n).opacity }; });
    ok(`${tag} the strip sits in the page right under the row (as in the mock-up)`, atTop.inFlow && atTop.gap >= -2 && 40 >= atTop.gap, JSON.stringify(atTop));
    ok(`${tag} the rail is there from the start (as in the mock-up)`, atTop.rail === 'visible' && atTop.railOp === '1', JSON.stringify(atTop));
    ok(`${tag} the rail's names for screen readers include each section's description`, (await p.$$eval('.ts-pk-rail a', a => a.map(x => x.getAttribute('aria-label')))).every(l => l && l.includes(': ') && l.length > 25), (await p.$$eval('.ts-pk-rail a', a => a.map(x => x.getAttribute('aria-label')))).join(' | '));
    await p.evaluate(() => { const h = document.querySelectorAll('.entry-content .ts-jump a')[1]; const t = document.getElementById(h.getAttribute('href').slice(1)); scrollTo(0, t.getBoundingClientRect().top + scrollY - 100); }); await W(700);
    const st = await p.evaluate(() => { const t = document.querySelector('.ts-pk-top'), r = t.getBoundingClientRect(), n = document.querySelector('.ts-pk-rail').getBoundingClientRect(); return { top: Math.round(r.top), h: Math.round(r.height), pe: getComputedStyle(t).pointerEvents, controls: t.querySelectorAll('a,button,[tabindex]').length, rail: { top: Math.round(n.top), bottom: Math.round(n.bottom), right: Math.round(n.right) }, vh: innerHeight, vw: innerWidth, fills: [...t.querySelectorAll('li i')].map(i => +i.style.getPropertyValue('--p')) }; });
    ok(`${tag} once past the row, the strip shows at the top`, st.top === 0 && st.h > 20 && 70 >= st.h, JSON.stringify(st));
    ok(`${tag} the strip names the current topic and position`, (await strip()) === 'Common reasons / 2 of 5', await strip());
    ok(`${tag} the strip shows progress per section`, st.fills[0] === 1 && st.fills[1] > 0 && 1 > st.fills[1] && st.fills[2] === 0, st.fills.join(' '));
    ok(`${tag} the strip is display only`, st.pe === 'none' && st.controls === 0);
    // first-time hint (Ryan, 2026-11-08: yes)
    const tp = await p.evaluate(() => { const t = document.querySelector('.ts-pk-tip'); return { shown: !t.hidden && t.classList.contains('is-on'), text: t.textContent, wiggle: document.querySelector('.ts-pk-nav').classList.contains('is-hinting'), stored: localStorage.getItem('ts-pk-tip') }; });
    ok(`${tag} first visit: a hint appears beside the rail once reading starts`, tp.shown && tp.text.includes('Slide your thumb') && tp.text.includes('Use it on the left') && tp.wiggle && tp.stored === 'seen', JSON.stringify(tp));
    await p.tap('.ts-pk-tip .ok'); await W(400);
    ok(`${tag} "Got it" hides the hint`, await p.evaluate(() => document.querySelector('.ts-pk-tip').hidden));
    await p.mouse.wheel(0, 60); await W(250);
    const lab = await p.evaluate(() => { const n = document.querySelector('.ts-pk-nav'), l = n.querySelector('.ts-pk-label'); return { moving: n.classList.contains('is-moving'), text: l.textContent, op: +getComputedStyle(l).opacity }; });
    ok(`${tag} while the page scrolls, the section name shows beside the rail`, lab.moving && lab.text === 'Common reasons' && lab.op > 0.5, JSON.stringify(lab));
    await W(1300); ok(`${tag} ...and fades once scrolling stops`, !(await p.evaluate(() => document.querySelector('.ts-pk-nav').classList.contains('is-moving'))));
    ok(`${tag} the rail sits on the right edge in the thumb zone`, st.rail.top >= st.vh * 0.25 && st.vh >= st.rail.bottom && st.rail.right >= st.vw - 8, JSON.stringify(st.rail));
    ok(`${tag} the bottom "On this page" button is replaced by the rail`, await p.evaluate(() => { const t = document.getElementById('ts-section-guide-trigger'); return !t || getComputedStyle(t).display === 'none'; }));

    let y0 = await Y();
    await touch('touchStart', 120, 20); await touch('touchEnd'); await W(400);
    ok(`${tag} touching the strip does nothing`, (await Y()) === y0 && !(await card()).on);
    await slideTo(3, false);
    let c = await card();
    ok(`${tag} sliding the rail previews the section: name, description, its real heading and its first lines`, c.on && c.name === 'Questions' && c.desc.length > 20 && c.heading.length > 5 && c.excerpt.length > 30 && c.lines === 3 && c.inView, JSON.stringify(c));
    ok(`${tag} the page does not move while browsing`, (await Y()) === y0);
    await touch('touchEnd'); await W(600);
    ok(`${tag} lifting keeps the preview, no jump`, (await card()).pinned && (await Y()) === y0);
    for (const k of [0, 1, 2, 4]) { await slideTo(k, false); const ck = await card(); await touch('touchEnd'); await W(300);
      ok(`${tag} preview of "${labels[k]}" has its description, heading and first lines`, ck.name === labels[k] && ck.desc.length > 20 && ck.heading.length > 5 && ck.excerpt.length > 30, JSON.stringify(ck)); }
    await touch('touchStart', 12, Math.round(st.vh * 0.12)); await touch('touchEnd'); await W(400);
    ok(`${tag} tapping elsewhere closes the preview and keeps the place`, !(await card()).on && (await Y()) === y0);
    await slideTo(3); await p.tap('.ts-pk-card .shot'); await W(300);
    ok(`${tag} the heading glows briefly after the jump`, await p.evaluate(h => document.querySelector(h).classList.contains('ts-pk-flash'), await hrefOf(3)));
    await W(1300);
    let L = await landed(await hrefOf(3)); ok(`${tag} tapping the preview jumps, heading just below the strip`, L.ok, JSON.stringify(L));
    ok(`${tag} the strip follows the jump`, (await strip()) === 'Questions / 4 of 5', await strip());
    ok(`${tag} a Back button names where you were`, await p.$eval('.ts-pk-back', b => !b.hidden && b.textContent.includes('Common reasons')));
    await W(300); await p.tap('.ts-pk-back'); await W(1600);
    const y1 = await Y(); ok(`${tag} Back returns to exactly where you were`, 3 >= Math.abs(y1 - y0), `${y0} -> ${y1}`);
    // fixes from the Kiro review (2026-11-08)
    ok(`${tag} a long press on the rail cannot open the link menu or drag the link`, await p.evaluate(() => { const a = document.querySelector('.ts-pk-rail a'), ev = new MouseEvent('contextmenu', { bubbles: true, cancelable: true }); a.dispatchEvent(ev); return ev.defaultPrevented && a.getAttribute('draggable') === 'false'; }));
    await p.evaluate(() => { const t = document.createElement('a'); t.id = 'pk-probe'; t.href = '#pk-probe'; t.textContent = 'probe'; t.style.cssText = 'position:fixed;left:8px;top:45%;z-index:95;padding:12px;background:#fff'; window.__pkClicks = 0; t.addEventListener('click', e => { e.preventDefault(); window.__pkClicks++; }); document.body.appendChild(t); });
    await slideTo(1);
    const pr = await p.$eval('#pk-probe', t => { const r = t.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
    await touch('touchStart', pr.x, pr.y); await touch('touchEnd'); await W(500);
    ok(`${tag} tapping elsewhere only closes the preview (does not also click what was tapped)`, !(await card()).on && (await p.evaluate(() => window.__pkClicks)) === 0);
    await touch('touchStart', pr.x, pr.y); await touch('touchEnd'); await W(500);
    ok(`${tag} ...and the next tap works normally`, (await p.evaluate(() => window.__pkClicks)) === 1);
    await p.evaluate(() => document.getElementById('pk-probe').remove());
    await p.evaluate(() => { const a = document.querySelectorAll('.ts-pk-rail a')[2]; a.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, detail: 1 })); }); await W(300);
    c = await card(); ok(`${tag} a screen-reader double-tap on the rail (click without a press) opens the preview and focuses it`, c.pinned && c.name === 'What to expect' && (await p.evaluate(() => document.activeElement === document.querySelector('.ts-pk-card'))), JSON.stringify(c));
    await p.keyboard.press('Escape'); await W(200);
    ok(`${tag} keyboard order: the rail comes right after the strip, before the sections`, await p.evaluate(() => { const n = document.querySelector('.ts-pk-nav'), h = document.querySelector('.entry-content h2'), st = document.querySelector('.ts-pk-top'); return !!(st.compareDocumentPosition(n) & Node.DOCUMENT_POSITION_FOLLOWING) && !!(n.compareDocumentPosition(h) & Node.DOCUMENT_POSITION_FOLLOWING); }));
    const hl = await p.evaluate(() => history.length); y0 = await Y();
    await slideTo(4); await p.tap('.ts-pk-card .shot'); await W(1600);
    ok(`${tag} a jump adds no browser-history entry`, (await p.evaluate(() => history.length)) === hl);
    ok(`${tag} Back is offered after the jump`, await p.$eval('.ts-pk-back', b => !b.hidden));
    await p.evaluate(y => scrollTo(0, y), y0); await W(1400);
    ok(`${tag} Back hides itself once the reader is back there by hand`, await p.$eval('.ts-pk-back', b => b.hidden));
    const cssText = await p.evaluate(() => document.getElementById('ts-peek-nav-css').textContent);
    ok(`${tag} older browsers get a solid strip background (color-mix fallback)`, cssText.includes('background:var(--pk-ground);background:color-mix') && cssText.includes('background:var(--pk-panel);background:color-mix'));
    ok(`${tag} the bottom guide button is hidden only when the rail is there`, cssText.split('#ts-section-guide-trigger').length === cssText.split('html.ts-pk-ready #ts-section-guide-trigger').length);
    // second Kiro review (2026-11-08): taps that are already handled never reach what is underneath; Enter on ×; one keyboard stop
    await p.evaluate(() => { const t = document.createElement('a'); t.id = 'pk-under'; t.href = '#pk-under'; t.style.cssText = 'position:fixed;inset:0;z-index:50'; window.__under = 0; t.addEventListener('click', e => { e.preventDefault(); window.__under++; }); document.body.appendChild(t); });
    await slideTo(2); y0 = await Y();
    const shotAt = await p.$eval('.ts-pk-card .shot', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
    await touch('touchStart', shotAt.x, shotAt.y); await touch('touchEnd'); await W(1500);
    ok(`${tag} a tap on the preview jumps and does not also click what is under the card`, (await Y()) !== y0 && (await p.evaluate(() => window.__under)) === 0, `under=${await p.evaluate(() => window.__under)}`);
    await slideTo(1);
    await touch('touchStart', 12, Math.round(st.vh * 0.5)); await W(700); await touch('touchEnd'); await W(500);
    ok(`${tag} a slow outside press (700 ms) only closes the preview`, !(await card()).on && (await p.evaluate(() => window.__under)) === 0, `under=${await p.evaluate(() => window.__under)}`);
    await touch('touchStart', 12, Math.round(st.vh * 0.5)); await touch('touchEnd'); await W(400);
    ok(`${tag} ...and the next tap reaches the page again`, (await p.evaluate(() => window.__under)) === 1);
    await p.evaluate(() => document.getElementById('pk-under').remove());
    const hash0 = await p.evaluate(() => location.hash);
    await p.evaluate(() => document.querySelectorAll('.ts-pk-rail a')[1].dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, detail: 1 }))); await W(300);
    await p.focus('.ts-pk-card .x'); await p.keyboard.press('Enter'); await W(400);
    ok(`${tag} Enter on × closes the preview without jumping, and focus returns to the rail`, !(await card()).on && (await p.evaluate(() => location.hash)) === hash0 && (await p.evaluate(() => document.activeElement === document.querySelectorAll('.ts-pk-rail a')[1])));
    ok(`${tag} the rail is one keyboard stop`, (await p.$$eval('.ts-pk-rail a', a => a.filter(x => x.getAttribute('tabindex') === '0').length)) === 1);
    await p.keyboard.press('ArrowDown'); await W(100);
    ok(`${tag} arrow keys move along the rail`, await p.evaluate(() => document.activeElement === document.querySelectorAll('.ts-pk-rail a')[2] && document.activeElement.getAttribute('tabindex') === '0'));
    const thm = await p.evaluate(() => { const o = getComputedStyle(document.querySelector('.ts-pk-rail')), l = getComputedStyle(document.querySelector('.ts-pk-rail li')), so = getComputedStyle(document.querySelector('.ts-pk-top ol')), a = getComputedStyle(document.querySelector('.ts-pk-rail a')); return { ml: o.marginLeft, pl: o.paddingLeft, ls: o.listStyleType, lim: l.marginTop + ' ' + l.marginLeft, sol: so.marginLeft + ' ' + so.paddingLeft, td: a.textDecorationLine }; });
    ok(`${tag} the theme's content styles do not reach the rail or strip`, thm.ml === '0px' && thm.pl === '0px' && thm.ls === 'none' && thm.lim === '0px 0px' && thm.sol === '0px 0px' && thm.td === 'none', JSON.stringify(thm));
    // left-hand option (Ryan, 2026-11-08: yes)
    await slideTo(1); await p.tap('.ts-pk-card .side'); await W(500);
    const lh = await p.evaluate(() => { const r = document.querySelector('.ts-pk-rail').getBoundingClientRect(); return { left: document.documentElement.classList.contains('ts-pk-left'), railLeft: Math.round(r.left), stored: localStorage.getItem('ts-pk-side'), card: document.querySelector('.ts-pk-card').classList.contains('is-on') }; });
    ok(`${tag} "⇆ Left" in the preview moves the rail to the left edge and closes the preview`, lh.left && 8 >= lh.railLeft && lh.stored === 'left' && !lh.card, JSON.stringify(lh));
    await slideTo(3, false); c = await card(); const cr = await p.$eval('.ts-pk-card', e => Math.round(e.getBoundingClientRect().left)); await touch('touchEnd'); await W(400);
    ok(`${tag} on the left, sliding previews as on the right (card beside the rail)`, c.on && c.name === 'Questions' && cr >= 40 && 80 >= cr, `left=${cr}`);
    ok(`${tag} on the left, no sideways page scroll`, await p.evaluate(() => innerWidth + 1 >= document.documentElement.scrollWidth));
    await p.reload({ waitUntil: 'load' }); await W(500);
    await p.evaluate(() => { const h = document.querySelectorAll('.entry-content .ts-jump a')[1]; const t = document.getElementById(h.getAttribute('href').slice(1)); scrollTo(0, t.getBoundingClientRect().top + scrollY - 100); }); await W(700);
    ok(`${tag} after a reload the rail stays on the left, and the hint does not show again`, await p.evaluate(() => document.documentElement.classList.contains('ts-pk-left') && document.querySelector('.ts-pk-tip').hidden));
    await slideTo(1); await p.tap('.ts-pk-card .side'); await W(500);
    ok(`${tag} "⇆ Right" moves it back`, await p.evaluate(() => !document.documentElement.classList.contains('ts-pk-left') && localStorage.getItem('ts-pk-side') === 'right' && document.querySelector('.ts-pk-rail').getBoundingClientRect().right >= innerWidth - 8));
    await p.evaluate(() => { const h = document.querySelectorAll('.entry-content .ts-jump a')[1]; const t = document.getElementById(h.getAttribute('href').slice(1)); scrollTo(0, t.getBoundingClientRect().top + scrollY - 100); }); await W(600);
    // the row at the top of the page
    await p.evaluate(() => scrollTo(0, document.querySelector('.entry-content .ts-jump').getBoundingClientRect().top + scrollY - 200)); await W(500);
    await p.tap('.entry-content .ts-jump a:text-is("What Research Says")'); await W(1700);
    L = await landed(await hrefOf(4)); ok(`${tag} the row at the top also lands the heading below the strip`, L.ok, JSON.stringify(L));
    ok(`${tag} no sideways page scroll`, await p.evaluate(() => innerWidth + 1 >= document.documentElement.scrollWidth));
    ok(`${tag} no JavaScript errors`, errors.length === 0, errors.join(' | '));
    await ctx.close();
  }
}
for (const [size, dev] of [['phone', devices['iPhone 13']], ['tablet', devices['iPad Mini']]]) {
  const ctx = await b.newContext({ ...dev, deviceScaleFactor: 1 });
  const { p } = await open(ctx, '/thai-massage/#ts-guide-research'); await p.waitForTimeout(3500);   // the theme's own arrival scroll starts about 0.9 s after load
  const L = await p.evaluate(() => { const h = document.getElementById('ts-guide-research'), s = document.querySelector('.ts-pk-top'); if (!h || !s) return { ok: false }; const t = h.getBoundingClientRect().top, sb = s.getBoundingClientRect().bottom; return { h: Math.round(t), sb: Math.round(sb), ok: t >= sb - 2 && sb + 40 >= t }; });
  ok(`${size} arriving at /thai-massage/#ts-guide-research lands the heading below the strip`, L.ok, JSON.stringify(L));
  await ctx.close();
}
{ // landscape phone: the preview fits the screen
  const ctx = await b.newContext({ ...devices['iPhone 13 landscape'], deviceScaleFactor: 1 });
  const { p, errors } = await open(ctx, '/thai-massage/');
  const cdp = await ctx.newCDPSession(p); const touch = (type, x, y) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ x, y }] });
  await p.evaluate(() => { const t = document.querySelectorAll('.entry-content .ts-jump a')[1]; const h = document.getElementById(t.getAttribute('href').slice(1)); scrollTo(0, h.getBoundingClientRect().top + scrollY - 80); }); await p.waitForTimeout(600);
  for (const k of [0, 4]) {
    const z = await p.evaluate(i => { const r = document.querySelectorAll('.ts-pk-rail li')[i].getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; }, k);
    await touch('touchStart', z.x, z.y); await touch('touchEnd'); await p.waitForTimeout(500);
    const r = await p.evaluate(() => { const c = document.querySelector('.ts-pk-card').getBoundingClientRect(); return { top: Math.round(c.top), bottom: Math.round(c.bottom), vh: innerHeight }; });
    ok(`landscape phone: the preview of section ${k + 1} fits on screen`, r.top >= 0 && r.vh >= r.bottom, JSON.stringify(r));
    await touch('touchStart', 10, 10); await touch('touchEnd'); await p.waitForTimeout(500);
  }
  const rb = await p.evaluate(() => ({ rail: Math.round(document.querySelector('.ts-pk-rail').getBoundingClientRect().bottom), vh: innerHeight }));
  ok('landscape phone: the rail ends above the Back button', rb.vh - rb.rail >= 70, JSON.stringify(rb));
  ok('landscape phone: no JavaScript errors', errors.length === 0, errors.join(' | '));
  await ctx.close();
}
const ctx = await b.newContext({ ...devices['iPhone 13'], deviceScaleFactor: 1 });
const { p } = await open(ctx, '/therapeutic-massage/');
ok('Therapeutic Massage unchanged (no strip, no rail)', await p.evaluate(() => !document.querySelector('.ts-pk') && !document.getElementById('ts-peek-nav-runtime')));
await b.close();
console.log(`\nchromium: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

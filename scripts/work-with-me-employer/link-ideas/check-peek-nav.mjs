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
    const card = () => p.evaluate(() => { const c = document.querySelector('.ts-pk-card'); return { on: c.classList.contains('is-on'), pinned: c.classList.contains('is-pinned'), name: (c.querySelector('.open > b') || {}).textContent || '', heading: (c.querySelector('.shot strong') || {}).textContent || '', excerpt: (c.querySelector('.shot em') || {}).textContent || '', desc: (c.querySelector('.open .d') || {}).textContent || '', lines: c.querySelectorAll('.shot > span').length, inView: c.getBoundingClientRect().left >= 0 }; });
    const strip = () => p.evaluate(() => { const t = document.querySelector('.ts-pk-top'); return t.querySelector('b').textContent + ' / ' + t.querySelector('span').textContent; });
    const landed = sel => p.evaluate(s => { const h = document.querySelector(s).getBoundingClientRect().top, sb = document.querySelector('.ts-pk-top').getBoundingClientRect().bottom; return { h: Math.round(h), sb: Math.round(sb), ok: h >= sb - 2 && sb + 40 >= h }; }, sel);
    const hrefOf = i => p.evaluate(k => document.querySelectorAll('.entry-content .ts-jump a')[k].getAttribute('href'), i);

    ok(`${tag} the row still has the five links in order (no-JavaScript fallback)`, (await p.$$eval('.entry-content .ts-jump a', a => a.map(x => x.textContent).join('|'))) === labels.join('|'));
    ok(`${tag} the pills trial is gone`, await p.evaluate(() => !document.getElementById('ts-pill-nav-runtime') && !document.querySelector('.ts-pill-ind')));
    const atTop = await p.evaluate(() => { const row = document.querySelector('.entry-content .ts-jump'), t = document.querySelector('.ts-pk-top'), n = document.querySelector('.ts-pk-nav'), first = document.querySelector('.entry-content h2'); return { rowShown: getComputedStyle(row).display !== 'none', stripAfterRow: row.nextElementSibling === t, stripBeforeSections: !!(t.compareDocumentPosition(first) & Node.DOCUMENT_POSITION_FOLLOWING), rail: getComputedStyle(n).visibility, railOp: getComputedStyle(n).opacity }; });
    ok(`${tag} the in-page link row under the header is removed (Ryan, 2026-11-08)`, !atTop.rowShown, JSON.stringify(atTop));
    ok(`${tag} the strip takes the row's place at the top of the content`, atTop.stripAfterRow && atTop.stripBeforeSections, JSON.stringify(atTop));
    ok(`${tag} the rail is there from the start (as in the mock-up)`, atTop.rail === 'visible' && atTop.railOp === '1', JSON.stringify(atTop));
    ok(`${tag} the rail's names for screen readers include each section's description`, (await p.$$eval('.ts-pk-rail a', a => a.map(x => x.getAttribute('aria-label')))).every(l => l && l.includes(': ') && l.length > 25), (await p.$$eval('.ts-pk-rail a', a => a.map(x => x.getAttribute('aria-label')))).join(' | '));
    await p.evaluate(() => { const h = document.querySelectorAll('.entry-content .ts-jump a')[1]; const t = document.getElementById(h.getAttribute('href').slice(1)); scrollTo(0, t.getBoundingClientRect().top + scrollY - 100); }); await W(700);
    const st = await p.evaluate(() => { const t = document.querySelector('.ts-pk-top'), r = t.getBoundingClientRect(), n = document.querySelector('.ts-pk-rail').getBoundingClientRect(); return { top: Math.round(r.top), h: Math.round(r.height), pe: getComputedStyle(t).pointerEvents, controls: t.querySelectorAll('a,button,[tabindex]').length, rail: { top: Math.round(n.top), bottom: Math.round(n.bottom), right: Math.round(n.right) }, vh: innerHeight, vw: innerWidth, fills: [...t.querySelectorAll('li i')].map(i => +i.style.getPropertyValue('--p')) }; });
    const hd = await p.evaluate(() => { const h = document.getElementById('masthead'), r = h.getBoundingClientRect(), i = document.querySelector('.ts-pk-ix').getBoundingClientRect(), t = document.querySelector('.ts-pk-top').getBoundingClientRect(); return { pos: getComputedStyle(h).position, top: Math.round(r.top), bottom: Math.round(r.bottom), iconTop: Math.round(i.top), stripBottom: Math.round(t.bottom) }; });
    ok(`${tag} the site header stays on top while reading (Ryan, 2026-11-08)`, hd.pos === 'sticky' && hd.top === 0, JSON.stringify(hd));
    ok(`${tag} the strip sticks right under the header`, 1 >= Math.abs(st.top - hd.bottom) && st.h > 20 && 70 >= st.h, JSON.stringify({ st, hd }));
    ok(`${tag} the index icon stays clear of the header and the strip`, hd.iconTop >= hd.stripBottom + 4, JSON.stringify(hd));
    const mt = p.locator('#ast-mobile-header .menu-toggle, #ast-mobile-header .ast-mobile-menu-trigger-minimal').first();
    if (size === 'phone' && await mt.count() && await mt.isVisible()) {
      await mt.click(); await W(300);
      const fl = await p.evaluate(() => new Promise(res => { const h = document.getElementById('masthead'), c = document.querySelector('#ast-mobile-header .ast-mobile-header-content'), v = []; let n = 0;
        (function tick() { v.push(h.offsetHeight + '/' + (c ? c.offsetHeight : 0) + '/' + getComputedStyle(document.documentElement).getPropertyValue('--pk-head').trim()); if (++n < 40) requestAnimationFrame(tick); else res(v); })(); }));
      ok(`${tag} opening the phone menu does not flicker (header and menu sizes stay still)`, new Set(fl).size === 1, [...new Set(fl)].slice(0, 6).join(' | '));
      await W(300);
      const m = await p.evaluate(() => { const c = document.querySelector('#ast-mobile-header .ast-mobile-header-content'); if (!c) return null; const r = c.getBoundingClientRect(); return { shown: getComputedStyle(c).display !== 'none' && r.height > 0, top: Math.round(r.top), bottom: Math.round(r.bottom), vh: innerHeight }; });
      ok(`${tag} the phone menu opens from the pinned header mid-page and fits the screen`, !!m && m.shown && m.top >= 0 && m.vh + 1 >= m.bottom, JSON.stringify(m));
      await mt.click(); await W(400);
    }
    ok(`${tag} the strip names the current topic and position`, (await strip()) === 'Common reasons / 2 of 5', await strip());
    ok(`${tag} the strip shows progress per section`, st.fills[0] === 1 && st.fills[1] > 0 && 1 > st.fills[1] && st.fills[2] === 0, st.fills.join(' '));
    ok(`${tag} the strip is display only`, st.pe === 'none' && st.controls === 0);
    const ov = await p.evaluate(() => { const rail = document.querySelector('.ts-pk-rail').getBoundingClientRect(), icon = document.querySelector('.ts-pk-ix').getBoundingClientRect(), edge = Math.min(rail.left, icon.left); let max = 0, worst = '';
      const c = document.querySelector('.entry-content'), cr = c.getBoundingClientRect(), column = cr.right - parseFloat(getComputedStyle(c).paddingRight);   // the text column's edge, where any line may reach
      let block = 0, bworst = '';
      document.querySelectorAll('.entry-content p, .entry-content li, .entry-content h2, .entry-content h3').forEach(el => { if (el.closest('.ts-pk') || el.closest('.ts-jump')) return; const r = document.createRange(); r.selectNodeContents(el); [...r.getClientRects()].forEach(b => { if (b.bottom > rail.top - 60 && b.top < rail.bottom && b.width > 0 && b.right > max) { max = b.right; worst = el.tagName + ': ' + el.textContent.trim().slice(0, 30); } });
        const br = el.getBoundingClientRect(); if (br.width) { const e = br.right - (parseFloat(getComputedStyle(el).paddingRight) || 0); if (e > block) { block = e; bworst = el.tagName + ': ' + el.textContent.trim().slice(0, 30); } } });   // where any line of that block could reach
      return { textRight: Math.round(max), blockRight: Math.round(block), column: Math.round(column), railLeft: Math.round(rail.left), iconLeft: Math.round(icon.left), clear: Math.round(edge - Math.max(max, column, block)), worst: block > max ? bworst : worst }; });
    ok(`${tag} the text column stops before the rail and the index icon (no line can run under them)`, ov.clear >= 0, JSON.stringify(ov));
    ok(`${tag} the thin sitewide reading-progress bar is gone (Ryan, 2026-11-08)`, await p.evaluate(() => { const a = getComputedStyle(document.body, '::after'); return !document.querySelector('style#wp-custom-css') || !document.getElementById('wp-custom-css').textContent.includes('ts-reading-progress'); }));
    ok(`${tag} the scroll-to-top button is gone (Ryan, 2026-11-08)`, await p.evaluate(() => !document.getElementById('ast-scroll-top')));
    // first-time hint (Ryan, 2026-11-08: yes); it waits for 1.5 s of reading
    await W(1800);
    const tp = await p.evaluate(() => { const t = document.querySelector('.ts-pk-tip'); return { shown: !t.hidden && t.classList.contains('is-on'), text: t.textContent, wiggle: document.querySelector('.ts-pk-nav').classList.contains('is-hinting'), stored: localStorage.getItem('ts-pk-tip') }; });
    ok(`${tag} first visit: a hint appears beside the rail once reading starts`, tp.shown && tp.text.includes('Slide your thumb') && tp.text.includes('Use it on the left') && tp.wiggle && tp.stored === 'seen', JSON.stringify(tp));
    await p.focus('.ts-pk-tip .ok'); await W(10500);
    ok(`${tag} the hint stays while it has keyboard focus (no 9 s auto-hide under the reader)`, await p.evaluate(() => !document.querySelector('.ts-pk-tip').hidden && document.activeElement === document.querySelector('.ts-pk-tip .ok')));
    await p.keyboard.press('Enter'); await W(500);
    ok(`${tag} "Got it" hides the hint and gives focus to the rail`, await p.evaluate(() => document.querySelector('.ts-pk-tip').hidden && !!document.activeElement.closest('.ts-pk-rail')));
    await p.mouse.wheel(0, 60); await W(250);
    const lab = await p.evaluate(() => { const n = document.querySelector('.ts-pk-nav'), l = n.querySelector('.ts-pk-label'); return { moving: n.classList.contains('is-moving'), text: l.textContent, op: +getComputedStyle(l).opacity }; });
    ok(`${tag} while the page scrolls, the section name shows beside the rail`, lab.moving && lab.text === 'Common reasons' && lab.op > 0.5, JSON.stringify(lab));
    await W(1300); ok(`${tag} ...and fades once scrolling stops`, !(await p.evaluate(() => document.querySelector('.ts-pk-nav').classList.contains('is-moving'))));
    ok(`${tag} the rail sits on the right edge in the thumb zone`, st.rail.top >= st.vh * 0.25 && st.vh >= st.rail.bottom && st.rail.right >= st.vw - 8, JSON.stringify(st.rail));
    ok(`${tag} the bottom "On this page" button is replaced by the rail`, await p.evaluate(() => { const t = document.getElementById('ts-section-guide-trigger'); return !t || getComputedStyle(t).display === 'none'; }));

    let y0 = await Y();
    const sty = await p.$eval('.ts-pk-top', e => { const r = e.getBoundingClientRect(); return Math.round(r.top + r.height / 2); });   // the strip now sits under the pinned header
    await touch('touchStart', 120, sty); await touch('touchEnd'); await W(400);
    ok(`${tag} touching the strip does nothing`, (await Y()) === y0 && !(await card()).on);
    await slideTo(3, false);
    let c = await card();
    ok(`${tag} sliding the rail previews the section: name, description, its real heading and its first lines`, c.on && c.name === 'Questions' && c.desc.length > 20 && c.heading.length > 5 && c.excerpt.length > 30 && c.lines === 3 && c.inView, JSON.stringify(c));
    ok(`${tag} the page does not move while browsing`, (await Y()) === y0);
    await touch('touchEnd'); await W(600);
    ok(`${tag} lifting keeps the preview, no jump`, (await card()).pinned && (await Y()) === y0);
    // the stack (Ryan, 2026-11-08): closed boxes for the sections above and below the open one, numbered, with their headings
    for (const k of [0, 2, 4]) {
      await slideTo(k, false); await W(150);
      const sk = await p.evaluate(() => { const c = document.querySelector('.ts-pk-card'), o = c.querySelector('.open'), rows = [...c.querySelectorAll('.row')], heads = [...document.querySelectorAll('.entry-content h2')].map(h => h.textContent.trim()), r = c.getBoundingClientRect(), orr = o.getBoundingClientRect();
        return { stripBottom: document.querySelector('.ts-pk-top').getBoundingClientRect().bottom, clipped: c.scrollHeight > c.clientHeight + 1, hasName: !!c.querySelector('.open > b') && getComputedStyle(c.querySelector('.open > b')).display !== 'none', hasHint: !!c.querySelector('.open .hint') && getComputedStyle(c.querySelector('.open .hint')).display !== 'none', fit: c.className.match(/fit\d/g) || [], above: rows.filter(x => o.compareDocumentPosition(x) & Node.DOCUMENT_POSITION_PRECEDING).map(x => x.querySelector('b').textContent + ' ' + x.querySelector('span').textContent), below: rows.filter(x => o.compareDocumentPosition(x) & Node.DOCUMENT_POSITION_FOLLOWING).map(x => x.querySelector('b').textContent + ' ' + x.querySelector('span').textContent), heads, top: r.top, bottom: r.bottom, vh: innerHeight, openMid: orr.top + orr.height / 2 }; });
      const want = n => sk.heads.filter(h => h.length).slice(0, 5)[n];
      await touch('touchEnd'); await W(300);
      ok(`${tag} stack, section ${k + 1} open: ${k} closed box(es) above and ${4 - k} below, each "number heading", in order`, sk.above.length === k && sk.below.length === 4 - k && sk.above.every((t, j) => t.startsWith((j + 1) + ' ')) && sk.below.every((t, j) => t.startsWith((k + 2 + j) + ' ')) && [...sk.above, ...sk.below].every(t => t.split(' ').slice(1).join(' ').length > 5), JSON.stringify({ above: sk.above, below: sk.below }));
      ok(`${tag} stack, section ${k + 1} open: the whole stack is on screen, below the strip, nothing cut off`, sk.top >= sk.stripBottom && sk.vh >= sk.bottom && sk.clipped === false && sk.hasName && sk.hasHint, JSON.stringify({ top: Math.round(sk.top), strip: Math.round(sk.stripBottom), bottom: Math.round(sk.bottom), vh: sk.vh, clipped: sk.clipped, fit: sk.fit }));
    }
    await slideTo(2); y0 = await Y();
    const row4 = await p.$eval('.ts-pk-card .row[data-k="3"]', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
    await touch('touchStart', row4.x, row4.y); await touch('touchEnd'); await W(500);
    c = await card(); ok(`${tag} stack: tapping a closed box opens it in the stack (no jump)`, c.pinned && c.name === 'Questions' && (await Y()) === y0, JSON.stringify(c));
    ok(`${tag} stack: now 3 closed boxes above and 1 below`, await p.evaluate(() => { const c = document.querySelector('.ts-pk-card'), o = c.querySelector('.open'), rows = [...c.querySelectorAll('.row')]; return rows.filter(x => o.compareDocumentPosition(x) & Node.DOCUMENT_POSITION_PRECEDING).length === 3 && rows.filter(x => o.compareDocumentPosition(x) & Node.DOCUMENT_POSITION_FOLLOWING).length === 1; }));
    await touch('touchStart', 12, Math.round((await p.evaluate(() => innerHeight)) * 0.6)); await touch('touchEnd'); await W(400);
    for (const k of [0, 1, 2, 4]) { await slideTo(k, false); const ck = await card(); await touch('touchEnd'); await W(300);
      ok(`${tag} preview of "${labels[k]}" has its description, heading and first lines`, ck.name === labels[k] && ck.desc.length > 20 && ck.heading.length > 5 && ck.excerpt.length > 30, JSON.stringify(ck)); }
    await touch('touchStart', 12, Math.round(st.vh * 0.6)); await touch('touchEnd'); await W(400);   // the page's left margin (the top is now the pinned header)
    ok(`${tag} tapping elsewhere closes the preview and keeps the place`, !(await card()).on && (await Y()) === y0);
    await slideTo(3); await p.tap('.ts-pk-card .shot'); await W(300);
    ok(`${tag} the heading glows briefly after the jump`, await p.evaluate(h => document.querySelector(h).classList.contains('ts-pk-flash'), await hrefOf(3)));
    await W(1300);
    let L = await landed(await hrefOf(3)); ok(`${tag} tapping the preview jumps, heading just below the strip`, L.ok, JSON.stringify(L));
    ok(`${tag} the strip follows the jump`, (await strip()) === 'Questions / 4 of 5', await strip());
    ok(`${tag} no "Back to ..." button after a jump (Ryan, 2026-11-08)`, await p.evaluate(() => !document.querySelector('.ts-pk-back')));
    await p.evaluate(y => scrollTo(0, y), y0); await W(800);
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
    await p.evaluate(y => scrollTo(0, y), y0); await W(800);
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
    // index icon (Ryan, 2026-11-08: design 1 "Fan", always at the top of the rail, blended into the page)
    const fanState = () => p.evaluate(() => { const nav = document.querySelector('.ts-pk-nav'), segs = [...document.querySelectorAll('.ts-pk-rail li')].map(l => { const r = l.getBoundingClientRect(); return r.top + r.height / 2; }), rail = document.querySelector('.ts-pk-rail').getBoundingClientRect();
      return { open: nav.classList.contains('is-index'), exp: document.querySelector('.ts-pk-ix').getAttribute('aria-expanded'), hid: document.querySelector('.ts-pk-fan').getAttribute('aria-hidden'), railL: rail.left, railR: rail.right, vw: innerWidth,
        items: [...document.querySelectorAll('.ts-pk-fan li')].map((l, k) => { const r = l.getBoundingClientRect(); return { name: l.querySelector('span').textContent, op: +getComputedStyle(l).opacity, d: Math.round(r.top + r.height / 2 - segs[k]), l: r.left, r: r.right, now: l.classList.contains('is-now') }; }) }; });
    const ixg = await p.evaluate(() => { const b = document.querySelector('.ts-pk-ix'), i = b.getBoundingClientRect(), r = document.querySelector('.ts-pk-rail').getBoundingClientRect(), cs = getComputedStyle(b);
      const probe = document.createElement('span'); probe.style.background = 'var(--pk-ground)'; document.querySelector('.ts-pk').appendChild(probe); const ground = getComputedStyle(probe).backgroundColor; probe.remove();
      return { shown: cs.display !== 'none' && cs.visibility === 'visible' && +cs.opacity === 1, gap: Math.round(r.top - i.bottom), dx: Math.round(Math.abs(i.right - r.right)), w: Math.round(i.width), bg: cs.backgroundColor, ground, shadow: cs.boxShadow, border: cs.borderTopWidth, before: !!(b.compareDocumentPosition(document.querySelector('.ts-pk-rail')) & Node.DOCUMENT_POSITION_FOLLOWING) }; });
    ok(`${tag} index icon: always shown, directly above the rail`, ixg.shown && ixg.gap >= 0 && 16 >= ixg.gap && 2 >= ixg.dx && ixg.w >= 44, JSON.stringify(ixg));
    ok(`${tag} index icon: blended into the page (no background of its own, no border or shadow)`, (ixg.bg === 'rgba(0, 0, 0, 0)' || ixg.bg === 'transparent') && ixg.shadow === 'none' && ixg.border === '0px', JSON.stringify(ixg));
    ok(`${tag} index icon: comes right before the rail for the keyboard`, ixg.before);
    y0 = await Y();
    const ixAt = await p.$eval('.ts-pk-ix', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
    await touch('touchStart', ixAt.x, ixAt.y); await W(800);
    let fs = await fanState();
    ok(`${tag} index: holding the icon fans out every section name beside its piece of the rail`, fs.open && fs.exp === 'true' && fs.hid === 'false' && fs.items.map(i => i.name).join('|') === labels.join('|') && fs.items.every(i => i.op > 0.95 && 8 >= Math.abs(i.d) && i.r <= fs.railL + 2 && i.l >= 0), JSON.stringify(fs.items.map(i => [i.op, i.d, Math.round(i.l)])));
    ok(`${tag} index: the section being read is highlighted`, fs.items.filter(i => i.now).map(i => i.name).join() === (await strip()).split(' / ')[0]);
    ok(`${tag} index: the page does not move`, (await Y()) === y0);
    await touch('touchEnd'); await W(700);
    fs = await fanState();
    ok(`${tag} index: lifting the finger slides the names back into the rail`, !fs.open && fs.exp === 'false' && fs.hid === 'true' && fs.items.every(i => 0.05 >= i.op) && (await Y()) === y0, JSON.stringify(fs.items.map(i => i.op)));
    ok(`${tag} index: a long press cannot open the browser's menu`, await p.evaluate(() => { const ev = new MouseEvent('contextmenu', { bubbles: true, cancelable: true }); document.querySelector('.ts-pk-ix').dispatchEvent(ev); return ev.defaultPrevented; }));
    await p.evaluate(() => document.querySelector('.ts-pk-ix').dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, detail: 1 }))); await W(500);
    ok(`${tag} index: a click without a press (keyboard, screen reader) opens it`, (await fanState()).open);
    await p.keyboard.press('Escape'); await W(500);
    ok(`${tag} index: Escape closes it and focus stays on the icon`, !(await fanState()).open && (await p.evaluate(() => document.activeElement === document.querySelector('.ts-pk-ix'))));
    // press the icon, then slide down onto the rail without lifting: the rail's preview takes over (Ryan, 2026-11-08)
    y0 = await Y();
    const ixAt2 = await p.$eval('.ts-pk-ix', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
    const q = await seg(3);
    await touch('touchStart', ixAt2.x, ixAt2.y); await W(400);
    ok(`${tag} index slide: the names fan out first`, (await fanState()).open);
    for (let k = 1; k <= 10; k++) { await touch('touchMove', ixAt2.x, ixAt2.y + (q.y - ixAt2.y) * k / 10); await W(30); }
    c = await card(); fs = await fanState();
    ok(`${tag} index slide: sliding onto the rail closes the fan and previews the section under the finger`, !fs.open && c.on && !c.pinned && c.name === 'Questions' && c.heading.length > 5, JSON.stringify({ fan: fs.open, c }));
    ok(`${tag} index slide: the page does not move`, (await Y()) === y0);
    const q2 = await seg(1); for (let k = 1; k <= 5; k++) { await touch('touchMove', ixAt2.x, q.y + (q2.y - q.y) * k / 5); await W(30); }
    ok(`${tag} index slide: the preview follows the finger along the rail`, (await card()).name === 'Common reasons');
    await touch('touchEnd'); await W(600);
    c = await card(); ok(`${tag} index slide: lifting keeps the preview, no jump`, c.pinned && c.name === 'Common reasons' && (await Y()) === y0, JSON.stringify(c));
    await p.tap('.ts-pk-card .shot'); await W(1600);
    L = await landed(await hrefOf(1)); ok(`${tag} index slide: tapping the preview then jumps there`, L.ok, JSON.stringify(L));
    await p.evaluate(y => scrollTo(0, y), y0); await W(800);
    await touch('touchStart', ixAt2.x, ixAt2.y); await W(300); await touch('touchMove', ixAt2.x - 6, ixAt2.y + 6); await W(100); await touch('touchEnd'); await W(600);
    ok(`${tag} index slide: a small wobble on the icon, then lifting, just closes the fan (no preview)`, !(await fanState()).open && !(await card()).on && (await Y()) === y0);
    // left-hand option (Ryan, 2026-11-08: yes)
    await slideTo(1); await p.tap('.ts-pk-card .side'); await W(500);
    const lh = await p.evaluate(() => { const r = document.querySelector('.ts-pk-rail').getBoundingClientRect(); return { left: document.documentElement.classList.contains('ts-pk-left'), railLeft: Math.round(r.left), stored: localStorage.getItem('ts-pk-side'), card: document.querySelector('.ts-pk-card').classList.contains('is-on') }; });
    ok(`${tag} "⇆ Left" in the preview moves the rail to the left edge and closes the preview`, lh.left && 8 >= lh.railLeft && lh.stored === 'left' && !lh.card, JSON.stringify(lh));
    await slideTo(3, false); c = await card(); const cr = await p.$eval('.ts-pk-card', e => Math.round(e.getBoundingClientRect().left)); await touch('touchEnd'); await W(400);
    ok(`${tag} on the left, sliding previews as on the right (card beside the rail)`, c.on && c.name === 'Questions' && cr >= 40 && 80 >= cr, `left=${cr}`);
    ok(`${tag} on the left, no sideways page scroll`, await p.evaluate(() => innerWidth + 1 >= document.documentElement.scrollWidth));
    ok(`${tag} on the left, the text column starts after the rail and icon`, await p.evaluate(() => { const c = document.querySelector('.entry-content'), col = c.getBoundingClientRect().left + parseFloat(getComputedStyle(c).paddingLeft), r = document.querySelector('.ts-pk-rail').getBoundingClientRect(), i = document.querySelector('.ts-pk-ix').getBoundingClientRect(); return col >= Math.max(r.right, i.right); }));
    const ixL = await p.$eval('.ts-pk-ix', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2, l: Math.round(r.left) }; });
    await touch('touchStart', ixL.x, ixL.y); await W(800); fs = await fanState(); await touch('touchEnd'); await W(500);
    ok(`${tag} on the left, the index icon and its names mirror to the left edge`, 8 >= ixL.l && fs.open && fs.items.every(i => i.l >= fs.railR - 2 && fs.vw >= i.r), JSON.stringify({ icon: ixL.l, items: fs.items.map(i => Math.round(i.l)) }));
    await p.reload({ waitUntil: 'load' }); await W(500);
    await p.evaluate(() => { const h = document.querySelectorAll('.entry-content .ts-jump a')[1]; const t = document.getElementById(h.getAttribute('href').slice(1)); scrollTo(0, t.getBoundingClientRect().top + scrollY - 100); }); await W(700);
    ok(`${tag} after a reload the rail stays on the left, and the hint does not show again`, await p.evaluate(() => document.documentElement.classList.contains('ts-pk-left') && document.querySelector('.ts-pk-tip').hidden));
    await slideTo(1); await p.tap('.ts-pk-card .side'); await W(500);
    ok(`${tag} "⇆ Right" moves it back`, await p.evaluate(() => !document.documentElement.classList.contains('ts-pk-left') && localStorage.getItem('ts-pk-side') === 'right' && document.querySelector('.ts-pk-rail').getBoundingClientRect().right >= innerWidth - 8));
    await p.evaluate(() => { const h = document.querySelectorAll('.entry-content .ts-jump a')[1]; const t = document.getElementById(h.getAttribute('href').slice(1)); scrollTo(0, t.getBoundingClientRect().top + scrollY - 100); }); await W(600);
    await p.evaluate(() => { const a = document.createElement('a'); a.id = 'pk-inlink'; a.href = '#ts-guide-questions'; a.textContent = 'see the questions'; const para = document.querySelector('.entry-content p'); para.appendChild(a); }); 
    await p.evaluate(() => scrollTo(0, document.getElementById('pk-inlink').getBoundingClientRect().top + scrollY - 300)); await W(400);
    await p.evaluate(() => document.getElementById('pk-inlink').click()); await W(1600);
    L = await landed('#ts-guide-questions'); ok(`${tag} an ordinary in-page link in the text lands the heading below the header and strip`, L.ok, JSON.stringify(L));
    await p.evaluate(() => document.getElementById('pk-inlink').remove());
    ok(`${tag} the hidden row's links are out of the keyboard order`, await p.evaluate(() => [...document.querySelectorAll('.entry-content .ts-jump a')].every(a => { a.focus(); return document.activeElement !== a; })));
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
    const r = await p.evaluate(() => { const el = document.querySelector('.ts-pk-card'), c = el.getBoundingClientRect(); return { top: Math.round(c.top), bottom: Math.round(c.bottom), vh: innerHeight, clipped: el.scrollHeight > el.clientHeight + 1, rows: el.querySelectorAll('.row').length }; });
    ok(`landscape phone: the stack for section ${k + 1} fits on screen with all boxes, nothing cut off`, r.top >= 0 && r.vh >= r.bottom && !r.clipped && r.rows === 4, JSON.stringify(r));
    await touch('touchStart', 10, 10); await touch('touchEnd'); await p.waitForTimeout(500);
  }
  const rb = await p.evaluate(() => ({ rail: Math.round(document.querySelector('.ts-pk-rail').getBoundingClientRect().bottom), vh: innerHeight }));
  ok('landscape phone: the rail ends above the Back button', rb.vh - rb.rail >= 70, JSON.stringify(rb));
  ok('landscape phone: the header is not pinned there (header, strip and rail would not fit)', await p.evaluate(() => getComputedStyle(document.getElementById('masthead')).position !== 'sticky'));
  ok('landscape phone: no JavaScript errors', errors.length === 0, errors.join(' | '));
  await ctx.close();
}
{ // without JavaScript the row is the way around the page
  const ctx = await b.newContext({ ...devices['iPhone 13'], deviceScaleFactor: 1, javaScriptEnabled: false });
  const p = await ctx.newPage(); const r = await p.goto(base + '/thai-massage/', { waitUntil: 'load' }); if (!r || r.status() !== 200) { console.log(`INVALID no-JS answered ${r && r.status()}`); await b.close(); process.exit(2); }
  const nj = await p.evaluate(() => { const row = document.querySelector('.entry-content .ts-jump'); return { shown: !!row && getComputedStyle(row).display !== 'none' && row.getBoundingClientRect().height > 0, links: row ? [...row.querySelectorAll('a')].map(a => a.textContent).join('|') : '' }; });
  ok('without JavaScript the in-page link row still shows, with all five links (fallback)', nj.shown && nj.links === labels.join('|'), JSON.stringify(nj));
  await ctx.close();
}
{ // Kiro, third review: a saved left side is measured correctly on load; using the rail before the hint means the hint never comes
  const ctx = await b.newContext({ ...devices['iPhone 13'], deviceScaleFactor: 1 });
  await ctx.addInitScript(() => { try { localStorage.setItem('ts-pk-side', 'left'); } catch (e) {} });
  const { p } = await open(ctx, '/thai-massage/');
  await p.evaluate(() => { const t = document.querySelectorAll('.entry-content .ts-jump a')[1]; const h = document.getElementById(t.getAttribute('href').slice(1)); scrollTo(0, h.getBoundingClientRect().top + scrollY - 100); }); await p.waitForTimeout(400);
  const g = await p.evaluate(() => { const c = document.querySelector('.entry-content'), col = c.getBoundingClientRect().left + parseFloat(getComputedStyle(c).paddingLeft), r = document.querySelector('.ts-pk-rail').getBoundingClientRect(), i = document.querySelector('.ts-pk-ix').getBoundingClientRect(); return { left: document.documentElement.classList.contains('ts-pk-left'), padded: document.documentElement.classList.contains('ts-pk-pad'), gap: Math.round(col - Math.max(r.right, i.right)), padRight: getComputedStyle(c).paddingRight, padLeft: getComputedStyle(c).paddingLeft }; });
  // either the theme's own left margin is enough (nothing added, gap >= 0), or our padding is on the left and leaves 0-12px; never right-side padding
  ok('saved left-hand side: on load the text column starts after the rail, padded for the left (or not at all), never for the right', g.left && g.gap >= 0 && (g.padded ? 12 >= g.gap : true) && !(g.padded && parseFloat(g.padRight) > parseFloat(g.padLeft)), JSON.stringify(g));
  const cdp = await ctx.newCDPSession(p); const z = await p.evaluate(() => { const r = document.querySelectorAll('.ts-pk-rail li')[2].getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top + r.height / 2 }; });
  await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [z] }); await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] }); await p.waitForTimeout(400);
  await p.mouse.wheel(0, 300); await p.waitForTimeout(2600);
  ok('using the rail before the hint appears means the hint never comes', await p.evaluate(() => document.querySelector('.ts-pk-tip').hidden && localStorage.getItem('ts-pk-tip') === 'seen'));
  await ctx.close();
}
{ // Kiro, third review: above 1100px too, no text under the rail
  const ctx = await b.newContext({ viewport: { width: 1280, height: 860 } });
  const { p } = await open(ctx, '/thai-massage/');
  await p.evaluate(() => { const t = document.querySelectorAll('.entry-content .ts-jump a')[1]; const h = document.getElementById(t.getAttribute('href').slice(1)); scrollTo(0, h.getBoundingClientRect().top + scrollY - 100); }); await p.waitForTimeout(500);
  const d = await p.evaluate(() => { const c = document.querySelector('.entry-content'), cr = c.getBoundingClientRect(), col = cr.right - parseFloat(getComputedStyle(c).paddingRight), r = document.querySelector('.ts-pk-rail').getBoundingClientRect(); let block = 0; document.querySelectorAll('.entry-content p, .entry-content li, .entry-content h2').forEach(el => { if (el.closest('.ts-pk') || el.closest('.ts-jump')) return; const b = el.getBoundingClientRect(); if (b.width) block = Math.max(block, b.right - (parseFloat(getComputedStyle(el).paddingRight) || 0)); }); return { clear: Math.round(r.left - Math.max(col, block)) }; });
  ok('desktop (1280px): the text stays clear of the rail too', d.clear >= 0, JSON.stringify(d));
  await ctx.close();
}
const ctx = await b.newContext({ ...devices['iPhone 13'], deviceScaleFactor: 1 });
const { p } = await open(ctx, '/therapeutic-massage/');
ok('Therapeutic Massage unchanged (no strip, no rail)', await p.evaluate(() => !document.querySelector('.ts-pk') && !document.getElementById('ts-peek-nav-runtime')));
ok('Therapeutic Massage: no reading-progress bar and no scroll-to-top button either (sitewide)', await p.evaluate(() => !document.getElementById('ast-scroll-top') && !(document.getElementById('wp-custom-css') || { textContent: '' }).textContent.includes('ts-reading-progress')));
await b.close();
console.log(`\nchromium: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

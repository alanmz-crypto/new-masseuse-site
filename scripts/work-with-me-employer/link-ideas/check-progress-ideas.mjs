// Read-only browser check of the practice page /progress-nav-ideas/ on staging39: each of the five ideas used as a visitor would, with real touch input.
// node check-progress-ideas.mjs https://staging39.thaisolitude.com     (Chromium; four page loads: phone + tablet, light + dark)
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
    const r = await p.goto(`${base}/progress-nav-ideas/`, { waitUntil: 'load' });
    if (!r || r.status() !== 200) { console.log(`INVALID ${tag}: answered ${r && r.status()} (202 = hosting challenge)`); await b.close(); process.exit(2); }
    const cdp = await ctx.newCDPSession(p);
    const touch = (type, x, y) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ x, y }] });
    const W = ms => p.waitForTimeout(ms);
    const pick = async n => { await p.evaluate(() => scrollTo(0, 0)); await W(200); await p.tap(`.tsp-picker button[data-idea="${n}"]`); await W(250);
      ok(`${tag} idea ${n} is the only one shown`, (await p.$$eval('.tsp-idea', els => els.filter(e => getComputedStyle(e).display !== 'none').map(e => e.dataset.idea).join())) === String(n)); };
    const below = async (id, sel) => p.evaluate(([i, s]) => { const h = document.getElementById(i).getBoundingClientRect().top, bar = s ? document.querySelector(s).getBoundingClientRect().bottom : 0; return { h: Math.round(h), bar: Math.round(bar), ok: h >= bar - 1 && h <= bar + 60 }; }, [id, sel]);
    const into = id => p.evaluate(i => scrollTo(0, document.getElementById(i).getBoundingClientRect().top + scrollY + 60), id);
    ok(`${tag} script ran`, await p.$eval('#tsp-lab', e => e.classList.contains('is-js')));
    if (theme === 'dark') ok(`${tag} site dark theme active`, await p.evaluate(() => document.documentElement.getAttribute('data-ts-theme') === 'dark'));

    // 1. growing segments
    await pick(1);
    await p.tap('.tspA a[href="#tsp-questions"]'); await W(1500);
    let g = await below('tsp-questions', '.tspA'); ok(`${tag} 1: tap lands the heading just below the bar`, g.ok, JSON.stringify(g));
    const A = await p.evaluate(() => ({ now: document.querySelector('.tspA li.is-now .t').textContent, nowW: document.querySelector('.tspA li.is-now').getBoundingClientRect().width, maxOther: Math.max(...[...document.querySelectorAll('.tspA li:not(.is-now)')].map(l => l.getBoundingClientRect().width)), top: document.querySelector('.tspA').getBoundingClientRect().top }));
    ok(`${tag} 1: the current section grows and shows its name`, A.now === 'Questions' && A.nowW > A.maxOther * 1.4, JSON.stringify(A));
    ok(`${tag} 1: bar stays at the top`, Math.abs(A.top) < 2);
    // 2. bar + sheet
    await pick(2); await into('tsp-who-can-benefit'); await W(500);
    ok(`${tag} 2: bar names the section and position`, (await p.$eval('.tspB', e => e.querySelector('.tspB-name').textContent + ' / ' + e.querySelector('.tspB-count').textContent.split(' ·')[0])) === 'Who can benefit / 3 of 6');
    await p.tap('.tspB-open'); await W(450);
    const B = await p.evaluate(() => ({ open: document.querySelector('.tsp-sheet').open, rows: [...document.querySelectorAll('.tsp-sheet a')].map(a => a.querySelector('.num').textContent + '|' + a.querySelector('.t').textContent + '|' + a.querySelector('.d').textContent.length + '|' + a.querySelector('.m').textContent) }));
    ok(`${tag} 2: sheet lists all six with description, length and ticks for read ones`, B.open && B.rows.length === 6 && B.rows[0].startsWith('✓|What it is') && B.rows[2].startsWith('3|Who can benefit') && B.rows.every(x => +x.split('|')[2] > 10 && /min$/.test(x)), B.rows.join(' ; '));
    await p.tap('.tsp-sheet a[href="#tsp-research"]'); await W(1500);
    g = await below('tsp-research', '.tspB'); ok(`${tag} 2: choosing a row closes the sheet and lands below the bar`, g.ok && !(await p.$eval('.tsp-sheet', d => d.open)), JSON.stringify(g));
    // 3. now / next
    await pick(3); await into('tsp-common-reasons'); await W(500);
    ok(`${tag} 3: names now and next`, (await p.$eval('.tspC', e => e.querySelector('.tspC-name').textContent + ' -> ' + e.querySelector('.next b').textContent)) === 'Common reasons -> Who can benefit');
    await p.tap('.tspC .next'); await W(1500);
    g = await below('tsp-who-can-benefit', '.tspC'); ok(`${tag} 3: Next jumps to the next section`, g.ok && (await p.$eval('.tspC-name', e => e.textContent)) === 'Who can benefit', JSON.stringify(g));
    await p.tap('.tspC .now'); await W(500);
    ok(`${tag} 3: tapping the name opens the step list`, (await p.$eval('.tspC .now', b => b.getAttribute('aria-expanded'))) === 'true' && (await p.$eval('.tspC .steps', e => e.getBoundingClientRect().height)) > 200);
    await p.tap('.tspC ol a[href="#tsp-what-it-is"]'); await W(1500);
    ok(`${tag} 3: a step jumps and the list closes`, (await p.$eval('.tspC-name', e => e.textContent)) === 'What it is' && (await p.$eval('.tspC .now', b => b.getAttribute('aria-expanded'))) === 'false');
    // 4. scrubber (real touch)
    await pick(4); await into('tsp-common-reasons'); await W(500);
    const bx = await p.$eval('.tspD ol', e => { const r = e.getBoundingClientRect(); return { x: r.left, y: r.top + r.height / 2, w: r.width }; });
    const y0 = await p.evaluate(() => scrollY);
    await touch('touchStart', bx.x + 4, bx.y); for (let k = 1; k <= 10; k++) { await touch('touchMove', bx.x + 4 + k * bx.w * 0.06, bx.y); await W(25); }
    const D = await p.evaluate(() => { const b = document.querySelector('.tspD .bubble'); const r = b.getBoundingClientRect(); return { on: b.classList.contains('is-on'), name: b.querySelector('b').textContent, blurb: b.querySelector('span').textContent, inView: r.left >= 0 && r.right <= innerWidth }; });
    ok(`${tag} 4: sliding shows the section under the thumb, with what it covers`, D.on && D.name === 'Questions' && D.blurb.length > 10 && D.inView, JSON.stringify(D));
    ok(`${tag} 4: the page does not scroll while sliding`, (await p.evaluate(() => scrollY)) === y0);
    await touch('touchEnd'); await W(1500);
    g = await below('tsp-questions', '.tspD'); ok(`${tag} 4: letting go jumps there`, g.ok && (await p.$eval('.tspD-name', e => e.textContent)) === '4. Questions', JSON.stringify(g));
    // 5. edge rail (real touch)
    await pick(5);
    const rl = await p.$eval('.tspE ol', e => { const r = e.getBoundingClientRect(); return { x: r.left + r.width / 2, y: r.top, h: r.height, right: r.right }; });
    ok(`${tag} 5: rail sits on the right edge`, rl.right <= (await p.evaluate(() => innerWidth)) && rl.right > (await p.evaluate(() => innerWidth)) - 20, JSON.stringify(rl));
    await touch('touchStart', rl.x, rl.y + 10); for (let k = 1; k <= 10; k++) { await touch('touchMove', rl.x, rl.y + 10 + k * rl.h * 0.05); await W(25); }
    const E = await p.evaluate(() => ({ shown: [...document.querySelectorAll('.tspE .tag')].filter(t => +getComputedStyle(t).opacity > 0.5).length, hover: document.querySelector('.tspE li.is-hover .tag') ? document.querySelector('.tspE li.is-hover .tag').textContent : '' }));
    ok(`${tag} 5: sliding fans out every name, the one under the thumb with its description`, E.shown === 6 && E.hover.startsWith('4. Questions') && E.hover.length > 25, JSON.stringify(E));
    await touch('touchEnd'); await W(1500);
    ok(`${tag} 5: letting go jumps there`, (await p.$eval('.tspE li.is-now .tag', t => t.textContent)).startsWith('4. Questions') && (await below('tsp-questions', null)).h < 60);
    ok(`${tag} no sideways page scroll`, await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    ok(`${tag} no JavaScript errors`, errors.length === 0, errors.join(' | '));
    await ctx.close();
  }
}
await b.close();
console.log(`\nchromium: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

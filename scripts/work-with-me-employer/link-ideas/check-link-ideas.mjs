// Read-only browser check of the practice page /in-page-link-ideas/ on staging39: every one of the five ideas is used as a visitor would.
// node check-link-ideas.mjs https://staging39.thaisolitude.com      (TS_BROWSER=chromium|firefox; four page loads: phone + desktop, light + dark)
// Exit 0 = every check passed; 1 = a check failed; 2 = INVALID (the hosting bot challenge answered instead of the page; nothing concluded).
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, firefox, devices } = require('playwright');
const engine = process.env.TS_BROWSER || 'chromium';
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090'].includes(base)) throw new Error('staging39 or localhost:8090 only');
const url = `${base}/in-page-link-ideas/`;
let pass = 0, fail = 0;
const ok = (name, cond, detail = '') => { if (cond) pass++; else fail++; console.log(`${cond ? 'PASS' : 'FAIL'} ${name}${detail !== '' ? `  [${detail}]` : ''}`); };
const browser = await (engine === 'firefox' ? firefox : chromium).launch();
const phone = engine === 'firefox' ? { viewport: { width: 390, height: 844 }, hasTouch: false } : devices['iPhone 13'];
for (const [size, opts] of [['phone', phone], ['desktop', { viewport: { width: 1280, height: 860 } }]]) {
  for (const theme of ['light', 'dark']) {
    const tag = `${size}/${theme}`;
    const ctx = await browser.newContext({ ...opts, deviceScaleFactor: 1 });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const p = await ctx.newPage();
    const errors = [];
    p.on('pageerror', e => errors.push(e.message));
    const res = await p.goto(url, { waitUntil: 'load' });
    if (!res || res.status() !== 200) { console.log(`INVALID ${tag}: ${url} answered ${res && res.status()} (202 = hosting challenge)`); await browser.close(); process.exit(2); }
    const wait = ms => p.waitForTimeout(ms);
    const top = async () => { await p.evaluate(() => scrollTo(0, 0)); await wait(300); };
    ok(`${tag} script ran`, await p.$eval('#tsx-lab', e => e.classList.contains('is-js')));
    if (theme === 'dark') ok(`${tag} site dark theme active`, await p.evaluate(() => document.documentElement.getAttribute('data-ts-theme') === 'dark'));
    ok(`${tag} no sideways page scroll`, await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1));
    const choose = async n => { await top(); await p.click(`.tsx-picker button[data-idea="${n}"]`); await wait(200);
      ok(`${tag} idea ${n} is the only one shown`, (await p.$$eval('.tsx-idea', els => els.filter(e => getComputedStyle(e).display !== 'none').map(e => e.dataset.idea).join())) === String(n)); };
    // 1. pills
    await choose(1);
    await p.click('.tsx-pills a[href="#tsx-questions"]'); await wait(1400);
    ok(`${tag} 1 pills: jump lands on Questions`, await p.$eval('#tsx-questions', s => Math.abs(s.getBoundingClientRect().top) < 140), await p.$eval('#tsx-questions', s => Math.round(s.getBoundingClientRect().top)));
    ok(`${tag} 1 pills: bar stays stuck at the top`, await p.$eval('.tsx-pills', e => Math.abs(e.getBoundingClientRect().top) < 2), await p.$eval('.tsx-pills', e => Math.round(e.getBoundingClientRect().top)));
    ok(`${tag} 1 pills: Questions highlighted`, (await p.$eval('.tsx-pills a.is-active', a => a.textContent)) === 'Questions');
    ok(`${tag} 1 pills: focus on the heading`, (await p.evaluate(() => document.activeElement.textContent)) === 'Questions');
    // 2. segments
    await choose(2);
    await p.click('.tsx-seg__list a[href="#tsx-who-can-benefit"]'); await wait(1400); await p.mouse.wheel(0, 250); await wait(500);
    ok(`${tag} 2 progress: label follows`, (await p.$eval('.tsx-seg', e => e.querySelector('.tsx-seg__label').textContent + ' / ' + e.querySelector('.tsx-seg__count').textContent)) === 'Who can benefit / 3 of 6');
    const fills = await p.$$eval('.tsx-seg__list i', is => is.map(i => +i.style.getPropertyValue('--p')));
    ok(`${tag} 2 progress: bars fill`, fills[0] === 1 && fills[1] === 1 && fills[2] > 0 && fills[2] < 1 && fills[3] === 0, fills.join(' '));
    // 3. cards
    await choose(3);
    await p.click('.tsx-cards a[href="#tsx-what-to-expect"]'); await wait(1400);
    ok(`${tag} 3 cards: Topics button appears`, await p.$eval('.tsx-back', e => e.classList.contains('is-shown') && getComputedStyle(e).opacity === '1'));
    await p.click('.tsx-back'); await wait(1300);
    ok(`${tag} 3 cards: Topics button brings the cards back`, await p.$eval('.tsx-cards', e => { const r = e.getBoundingClientRect(); return r.top < innerHeight && r.bottom > 0; }));
    // 4. search
    await choose(4);
    await p.click('.tsx-cmdk-open'); await wait(300);
    ok(`${tag} 4 search: box opens with the cursor in it`, await p.evaluate(() => document.querySelector('.tsx-cmdk').open && document.activeElement.matches('.tsx-cmdk input')));
    await p.keyboard.type('res'); await wait(150);
    ok(`${tag} 4 search: filters`, (await p.$$eval('.tsx-cmdk [role=option] span', o => o.map(x => x.textContent).join('|'))) === 'What Research Says');
    await p.keyboard.press('Enter'); await wait(1400);
    ok(`${tag} 4 search: Enter jumps and closes`, await p.evaluate(() => location.hash === '#tsx-research' && !document.querySelector('.tsx-cmdk').open));
    if (size === 'desktop') {
      await p.keyboard.press('Control+k'); await wait(200); await p.keyboard.press('ArrowDown'); await p.keyboard.press('ArrowDown'); await p.keyboard.press('Enter'); await wait(1400);
      ok(`${tag} 4 search: Ctrl+K, arrows, Enter`, (await p.evaluate(() => location.hash)) === '#tsx-who-can-benefit');
    }
    // 5. island
    await choose(5);
    await p.evaluate(() => scrollTo(0, document.getElementById('tsx-common-reasons').getBoundingClientRect().top + scrollY + 100)); await wait(600);
    ok(`${tag} 5 island: shows the current section`, (await p.$eval('.tsx-island__label', e => e.textContent)) === '2/6Common reasons');
    ok(`${tag} 5 island: floats at the bottom`, await p.$eval('.tsx-island', e => { const r = e.getBoundingClientRect(); return r.bottom <= innerHeight && r.bottom > innerHeight - 120; }));
    await p.click('.tsx-island__bar'); await wait(500);
    ok(`${tag} 5 island: opens`, (await p.$eval('.tsx-island__bar', b => b.getAttribute('aria-expanded'))) === 'true');
    await p.click('.tsx-island ol a[href="#tsx-research"]'); await wait(1400);
    ok(`${tag} 5 island: jump and close`, (await p.evaluate(() => document.querySelector('.tsx-island__label').textContent + document.querySelector('.tsx-island__bar').getAttribute('aria-expanded'))) === '6/6What Research Saysfalse');
    ok(`${tag} no JavaScript errors`, errors.length === 0, errors.join(' | '));
    await ctx.close();
  }
}
await browser.close();
console.log(`\n${engine}: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

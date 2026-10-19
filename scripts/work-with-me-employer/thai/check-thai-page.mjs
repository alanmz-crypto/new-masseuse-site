// Rendered check of the Thai Massage page after install-thai-evidence.php (read-only; public requests only).
// Usage: node check-thai-page.mjs <base-url> <out-dir> [label]        e.g. node check-thai-page.mjs http://localhost:8090 ~/.cache/wwm-scratch/thai-shots local
//        BROWSER=firefox to use Firefox instead of Chromium.
// Per width (1280 / 390 / 320) in LIGHT and DARK: HTTP 200, no side-scroll, one heading of each name, the 7 FAQ questions once each, the 5 research panels (first open, the others closed),
// every panel opens and closes with the mouse AND with Enter/Space on the keyboard, numbered markers 1..N once each in first-use order with N Sources entries, no console errors, screenshots.
// Exit 1 if any check fails.
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = process.argv[3] || '/tmp/thai-shots';
const label = process.argv[4] || 'run';
fs.mkdirSync(out, { recursive: true });
const FAQ = ['What Should I Wear to a Thai Massage?', 'What Will I Lie On?', 'How Long Is a Session?', 'Do I Need to Be Flexible?', 'Where Do Sessions Take Place?', 'How Is Thai Massage Different From Your Other Services?', 'How Do I Book a Session?'];
const PANELS = ['Reviews, and What They Say About Limits', 'Back Pain', 'Neck, Shoulders, Arms and Feet', 'Stress, Energy and Recovery', 'Sources'];
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const engine = process.env.BROWSER === 'firefox' ? pw.firefox : pw.chromium;
const browser = await engine.launch(process.env.BROWSER === 'firefox' ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
for (const theme of ['light', 'dark']) {
  for (const width of [1280, 390, 320]) {
    const tag = `${theme}-${width}`;
    const ctx = await browser.newContext({ viewport: { width, height: 900 } });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errors.push(m.text()); });
    const resp = await page.goto(base + '/thai-massage/', { waitUntil: 'networkidle' });
    check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
    const dark = await page.evaluate(() => document.documentElement.getAttribute('data-ts-theme'));
    if (theme === 'dark') check(`${tag}: dark mode is on`, dark === 'dark', dark);
    const r = await page.evaluate((faq, panels) => {
      const txt = el => (el.textContent || '').replace(/\s+/g, ' ').trim();
      const h2 = [...document.querySelectorAll('main h2, .entry-content h2')].map(txt);
      const count = s => h2.filter(x => x === s).length;
      const faqQ = [...document.querySelectorAll('.uagb-question')].map(txt);
      const det = [...document.querySelectorAll('details.wp-block-details')].map(d => ({ s: txt(d.querySelector('summary')), open: d.open }));
      const markers = [...document.querySelectorAll('a')].map(a => txt(a)).filter(t => /^\[\d+\]$/.test(t));
      const firstUse = []; markers.forEach(m => { const n = +m.slice(1, -1); if (!firstUse.includes(n)) firstUse.push(n); });
      const cites = document.querySelectorAll('cite.ts-source').length;
      return { h2, headQ: count('Questions About Thai Massage'), headR: count('What the Research Says About Thai Massage'), faqQ, det, firstUse, cites, markerCount: markers.length,
        scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth, faqLinks: document.querySelectorAll('.uagb-faq-content a').length };
    });
    check(`${tag}: no side-scroll`, r.scroll <= 0, r.scroll);
    check(`${tag}: each new heading once`, r.headQ === 1 && r.headR === 1, `${r.headQ},${r.headR}`);
    check(`${tag}: the 7 FAQ questions, once each, in order`, JSON.stringify(r.faqQ.filter(q => FAQ.includes(q))) === JSON.stringify(FAQ), r.faqQ.length);
    check(`${tag}: no links inside FAQ answers`, r.faqLinks === 0, r.faqLinks);
    check(`${tag}: the 5 research panels, in order`, JSON.stringify(r.det.map(d => d.s)) === JSON.stringify(PANELS), r.det.map(d => d.s).join(' | '));
    check(`${tag}: first panel open, the others closed`, r.det.map(d => d.open).join() === 'true,false,false,false,false', r.det.map(d => d.open).join());
    check(`${tag}: markers numbered 1..N in first-use order and N Sources entries`, r.firstUse.length > 0 && r.firstUse.every((n, i) => n === i + 1) && r.cites === r.firstUse.length, `${r.firstUse.length} numbers, ${r.cites} sources, ${r.markerCount} links`);
    if (width === 1280 || width === 390) {
      // mouse: open the 2nd panel, close it; keyboard: focus the 3rd summary, Enter opens, Space closes
      const sums = page.locator('details.wp-block-details > summary');
      await sums.nth(1).scrollIntoViewIfNeeded(); await sums.nth(1).click();
      let st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: click opens a panel`, st[1] === true, st.join());
      await sums.nth(1).click();
      st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: click closes it again`, st[1] === false, st.join());
      await sums.nth(2).focus(); await page.keyboard.press('Enter');
      st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: Enter opens a panel from the keyboard`, st[2] === true, st.join());
      await page.keyboard.press('Space');
      st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: Space closes it again`, st[2] === false, st.join());
    }
    // screenshots: top of the new sections with the first panel open, and everything open
    const h = page.locator('h2.uagb-heading-text', { hasText: 'Questions About Thai Massage' });
    await h.scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(out, `${label}-${tag}-faq.png`), fullPage: false });
    await page.locator('h2.uagb-heading-text', { hasText: 'What the Research Says' }).scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(out, `${label}-${tag}-research.png`), fullPage: false });
    await page.evaluate(() => document.querySelectorAll('details.wp-block-details').forEach(d => { d.open = true; }));
    const sc = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    check(`${tag}: no side-scroll with every panel open`, sc <= 0, sc);
    await page.screenshot({ path: path.join(out, `${label}-${tag}-full.png`), fullPage: true });
    check(`${tag}: no script errors`, errors.length === 0, errors.slice(0, 2).join(' || '));
    await ctx.close();
  }
}
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ./install-thai-evidence.php — the installer whose result this checks; ../therapeutic/check-therapeutic-page.mjs — the sibling check this follows

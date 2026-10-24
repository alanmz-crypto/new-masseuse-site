// Rendered check of the slide-open research panels (read-only; public requests only), after install-research-slide.php.
// Usage: node check-research-slide.mjs <base-url> <out-dir> [label] [firefox]        e.g. node check-research-slide.mjs https://staging36.thaisolitude.com /tmp/slide staging36
//        BROWSER=firefox (or a 4th argument 'firefox') to use Firefox instead of Chromium.
// For the Thai, Therapeutic and Deep Tissue pages at 1280 and 390 px: a panel's height is sampled while it opens and shuts. Where the browser supports the animation (::details-content and interpolate-size) the height must
// pass through in-between values and end at the natural height / the closed height; with reduced motion requested it must jump at once; where the browser cannot animate, the panel must still open and close (reported as INFO).
// Also: closed panels hide their links (not focusable), no side-scroll with every panel open, no script errors. Exit 1 if any check fails.
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = process.argv[3] || '/tmp/slide-shots';
const label = process.argv[4] || 'run';
fs.mkdirSync(out, { recursive: true });
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const info = (m) => console.log('INFO ' + m);
const useFirefox = process.env.BROWSER === 'firefox' || process.argv[5] === 'firefox';
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
const PAGES = ['/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/'];
const heightOf = (page, idx) => page.evaluate((i) => document.querySelectorAll('details.wp-block-details')[i].getBoundingClientRect().height, idx);
const sample = async (page, idx, times) => { const r = []; let last = 0; for (const t of times) { await page.waitForTimeout(t - last); last = t; r.push(Math.round(await heightOf(page, idx))); } return r; };
for (const reduced of [false, true]) {
  for (const width of [1280, 390]) {
    for (const p of PAGES) {
      const tag = `${p} ${width}px${reduced ? ' reduced-motion' : ''}`;
      const ctx = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: reduced ? 'reduce' : 'no-preference' });
      const page = await ctx.newPage();
      const errors = [];
      page.on('pageerror', e => errors.push(String(e)));
      page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errors.push(m.text()); });
      const resp = await page.goto(base + p, { waitUntil: 'networkidle' });
      check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
      const supported = await page.evaluate(() => CSS.supports('selector(::details-content)') && CSS.supports('interpolate-size', 'allow-keywords'));
      const n = await page.evaluate(() => document.querySelectorAll('details.wp-block-details').length);
      check(`${tag}: research panels found`, n >= 5, n);
      const hidden = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details:not([open]) a')].every(a => !a.checkVisibility()));
      check(`${tag}: links inside closed panels are hidden (not focusable)`, hidden);
      const idx = 1;
      const sum = page.locator('details.wp-block-details > summary').nth(idx);
      await sum.scrollIntoViewIfNeeded();
      const h0 = Math.round(await heightOf(page, idx));
      await sum.click();
      const o = await sample(page, idx, [40, 160, 320, 900]);
      const full = o[3];
      check(`${tag}: the panel opens to its full height`, full > h0 + 50, `${h0} -> ${full}`);
      const mid = (v) => v > h0 + 8 && v < full - 8;
      if (reduced) check(`${tag}: reduced motion: no in-between heights while opening`, !o.slice(0, 2).some(mid) && o[0] >= full - 2, o.join(','));
      else if (supported) check(`${tag}: slides open (in-between heights while opening)`, o.slice(0, 3).some(mid), o.join(','));
      else info(`${tag}: this browser cannot animate the panel; it opens at once (${o.join(',')})`);
      await sum.click();
      const c = await sample(page, idx, [40, 160, 320, 900]);
      check(`${tag}: the panel closes back to its closed height`, Math.abs(c[3] - h0) <= 2, `${full} -> ${c[3]}`);
      if (reduced) check(`${tag}: reduced motion: no in-between heights while closing`, !c.slice(0, 2).some(mid) && c[0] <= h0 + 2, c.join(','));
      else if (supported) check(`${tag}: slides shut (in-between heights while closing)`, c.slice(0, 3).some(mid), c.join(','));
      if (width === 390 && !reduced) {
        await page.evaluate(() => document.querySelectorAll('details.wp-block-details').forEach(d => { d.open = true; }));
        await page.waitForTimeout(700);
        const sc = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        check(`${tag}: no side-scroll with every panel open`, sc <= 0, sc);
        await page.screenshot({ path: path.join(out, `${label}-${p.replace(/\//g, '')}-open.png`), fullPage: false });
      }
      check(`${tag}: no script errors`, errors.length === 0, errors.slice(0, 2).join(' || '));
      await ctx.close();
    }
  }
}
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ./install-research-slide.php — the installer whose result this checks; ../deep-tissue/check-deep-tissue-evidence.mjs — the sibling check this follows

// Full-page screenshots of the 9 public pages (the 8 original ones plus /privacy-policy/, added 2026-10-17) at desktop (1280) and mobile (390) widths. Read-only: it only loads public pages.
// usage: node capture-pages.mjs <base-url> <output-dir>
// Playwright is taken from the Mirror project (the same install the earlier restyle checks used).
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [base, out] = process.argv.slice(2);
if (!base || !out) { console.error('usage: node capture-pages.mjs <base-url> <output-dir>'); process.exit(1); }
fs.mkdirSync(out, { recursive: true });
const pages = ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/work-with-me/', '/contact/', '/privacy-policy/'];
const views = [[1280, 900, 'd'], [390, 844, 'm']];
const b = await chromium.launch();
let ok = 0, bad = 0;
for (const [w, h, v] of views) {
  const ctx = await b.newContext({ viewport: { width: w, height: h }, reducedMotion: 'reduce', deviceScaleFactor: 1 });
  for (const p of pages) {
    const page = await ctx.newPage();
    try {
      const r = await page.goto(base + p, { waitUntil: 'networkidle', timeout: 60000 });
      await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' });
      // scroll through so lazy images and reveal-on-scroll blocks reach their final state, then return to the top
      await page.evaluate(async () => { const step = innerHeight * 0.8; for (let y = 0; y < document.body.scrollHeight; y += step) { scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } scrollTo(0, 0); });
      await page.waitForTimeout(600);
      await page.screenshot({ path: `${out}/${(p === '/' ? 'home' : p.replace(/\//g, ''))}-${v}.png`, fullPage: true });
      ok++;
      if (r.status() !== 200) { console.log('status', r.status(), p); bad++; }
    } catch (e) { console.log('FAILED', p, v, String(e).slice(0, 100)); bad++; }
    await page.close();
  }
  await ctx.close();
}
await b.close();
console.log(`${base}: ${ok} screenshots, ${bad} problems -> ${out}`);
process.exit(bad ? 1 : 0);

// ref: ./compare-shots.py — compares two sets of these screenshots; ../post-change-test.sh — runs both

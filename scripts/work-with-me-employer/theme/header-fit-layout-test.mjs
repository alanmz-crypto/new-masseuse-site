// Layout proof that a header change (here: theme/header-fit.css, 2026-10-19) only changes the header: for every element below it, position and size RELATIVE TO THE HEADER'S BOTTOM EDGE must be identical with and without the fix.
// Usage: node below-header-structure.mjs <base-url> <css-file>        (read-only; the css is injected in the browser, nothing is written to the site)
import { createRequire } from 'module'; import fs from 'fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [base, cssFile] = process.argv.slice(2); const css = fs.readFileSync(cssFile, 'utf8');
const pages = ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/work-with-me/', '/contact/', '/privacy-policy/', '/accessibility/'];
const b = await chromium.launch({ executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
const snap = (p) => p.evaluate(() => {
  const hb = document.querySelector('#masthead').getBoundingClientRect().bottom + scrollY; const out = [];
  for (const e of document.body.querySelectorAll('*')) {
    if (e.closest('#masthead')) continue; const cs = getComputedStyle(e); if (cs.display === 'none') continue; let fx = false; for (let a = e; a && a !== document.body; a = a.parentElement) { if (getComputedStyle(a).position === 'fixed') { fx = true; break; } } if (fx) continue; /* fixed bars (and their children) are anchored to the screen, not to the header */
    const r = e.getBoundingClientRect(); if (r.top + scrollY < hb - 0.5) continue; /* only what is BELOW the header (the hidden skip link and the scroll-to-top button sit above it) */ out.push([e.tagName, +(r.top + scrollY - hb).toFixed(3), +r.left.toFixed(3), +r.width.toFixed(3), +r.height.toFixed(3), (e.childElementCount ? '' : (e.textContent || '').trim().slice(0, 40)), (e.className && e.className.toString ? e.className.toString().slice(0, 60) : ''), cs.position]);
  }
  return { hb, total: Math.ceil(document.documentElement.scrollHeight), out };
});
let bad = 0, compared = 0;
for (const w of [375, 320]) {
  const ctx = await b.newContext({ viewport: { width: w, height: 800 }, isMobile: true, hasTouch: true });
  for (const pg of pages) {
    const res = {};
    for (const mode of ['without', 'with']) { const p = await ctx.newPage(); await p.goto(base + pg, { waitUntil: 'networkidle' }); if (mode === 'with') await p.addStyleTag({ content: css }); await p.waitForTimeout(500); res[mode] = await snap(p); await p.close(); }
    const A = res.without.out, B = res.with.out; let maxd = 0, mism = 0;
    if (A.length !== B.length) mism = -1; else for (let i = 0; i < A.length; i++) { if (A[i][0] !== B[i][0] || A[i][5] !== B[i][5]) mism++; for (let k = 1; k <= 4; k++) maxd = Math.max(maxd, Math.abs(A[i][k] - B[i][k])); }
    compared += A.length; const ok = mism === 0 && maxd <= 0.05; if (!ok) bad++;
    console.log(`${ok ? 'ok  ' : 'DIFF'} ${w}px ${pg.padEnd(24)} header ${res.without.hb.toFixed(1)} -> ${res.with.hb.toFixed(1)} px | ${A.length} elements below it: max position/size change ${maxd.toFixed(3)} px, tag/text mismatches ${mism}`);
  }
  await ctx.close();
}
await b.close(); console.log(bad ? `\nRESULT: ${bad} page/width combination(s) changed below the header` : `\nRESULT: nothing below the header moved or changed size on any page (${compared} element comparisons)`); process.exit(bad ? 1 : 0);

// ref: ./header-fit.css — the CSS this proves harmless below the header; ./header-fit-test.mjs — the width/theme test of the header itself (mutation-checked: a stray margin rule is caught)

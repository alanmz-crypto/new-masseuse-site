// Crop check for the three Thai-page illustrations (read-only; public requests only). Usage: node check-image-crop.mjs <base-url> [dark]
// Added 2026-10-19 (Ryan: "the pictures need to be cropped closer to their original square shape"). At ten widths (320-1280 px) every picture's visible frame must have the picture's own proportions
// (512 x 460, within 1%), i.e. no part of the picture cut off, each image loaded, no side-scroll. Run it against a site WITHOUT thai/image-crop.css to see the problem (up to 50% cut off). Exit 1 on failure.
import { createRequire } from 'module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium, firefox } = require('playwright');
const isFF = process.env.BROWSER === 'firefox';   // BROWSER=firefox node check-image-crop.mjs <url>: a second engine
const [base, theme] = process.argv.slice(2);
const ORIG = 512 / 460;
const b = isFF ? await firefox.launch() : await chromium.launch({ executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
let fails = 0; const check = (n, ok, d = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + n + (d !== '' ? '  [' + d + ']' : '')); if (!ok) fails++; };
for (const w of [1280, 1024, 977, 768, 600, 430, 390, 375, 320]) {
  const ctx = await b.newContext(isFF ? { viewport: { width: w, height: 900 } } : { viewport: { width: w, height: 900 }, isMobile: w < 800, hasTouch: w < 800 });
  if (theme === 'dark') await ctx.addInitScript(() => localStorage.setItem('ts-theme', 'dark'));
  const p = await ctx.newPage(); await p.goto(base + '/thai-massage/', { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
  // lazy-loaded pictures below the fold are only fetched once scrolled into view (Firefox is stricter than Chromium): scroll the whole page once, then measure from the top
  await p.evaluate(async () => { const h = document.documentElement.scrollHeight; for (let y = 0; y < h; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 120)); } window.scrollTo(0, 0); }); await p.waitForTimeout(500);
  const r = await p.evaluate(() => [...document.querySelectorAll('.wp-block-uagb-image img')].slice(0, 3).map((i) => { const r = i.getBoundingClientRect(); return { w: r.width, h: r.height, loaded: i.complete && i.naturalWidth > 0, side: document.documentElement.scrollWidth - document.documentElement.clientWidth }; }));
  const lost = r.map((x) => { const a = x.w / x.h; return 100 * (a > ORIG ? 1 - ORIG / a : 1 - a / ORIG); });
  check(`${w}px: three pictures, loaded, no side-scroll`, r.length === 3 && r.every((x) => x.loaded && x.side <= 0));
  check(`${w}px: each frame has the picture's proportions (cut off: ${lost.map((x) => x.toFixed(1) + '%').join(', ')})`, lost.every((x) => x <= 1), r.map((x) => Math.round(x.w) + 'x' + Math.round(x.h)).join(' | '));
  await ctx.close();
}
await b.close(); console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed')); process.exit(fails ? 1 : 0);

// ref: ./image-crop.css — the CSS this checks; ./install-image-crop.php — its installer; ./check-thai-images.mjs — the sibling check for the files and their order

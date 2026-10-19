// Rendered check of the three Thai-page illustrations (read-only; public requests only). Usage: node check-thai-images.mjs <base-url> [out-prefix] [dark]
// Added 2026-10-19 (Ryan: swap the page's three images). Per width (1280/1024/768/600/390/375/320), in the chosen theme: exactly three illustrations, each loaded, object-fit cover, the reviewed
// files in the reviewed order (hero = 00illaCNN512Curve.webp, then thai_massage_immage.webp, then thai_massage_immage_magenta.webp), the box sizes the page design gives (they do not depend on the
// picture), distinct non-empty alt text, no side-scroll. Saves screenshots at 1280/390/320 when an out-prefix is given. Exit 1 on any failure.
import { createRequire } from 'module';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [base, prefix, theme] = process.argv.slice(2);
const FILES = ['00illaCNN512Curve.webp', 'thai_massage_immage.webp', 'thai_massage_immage_magenta.webp'];
const BOX = { 1280: ['358x380', '200x200', '200x200'], 1024: ['289x380', '200x200', '200x200'], 768: ['246x250', '282x194', '282x194'], 600: ['512x300', '552x250', '552x250'], 390: ['302x300', '342x250', '342x250'], 375: ['287x300', '327x250', '327x250'], 320: ['232x300', '272x250', '272x250'] };
const b = await chromium.launch({ executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
let fails = 0; const check = (n, ok, d = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + n + (d !== '' ? '  [' + d + ']' : '')); if (!ok) fails++; };
for (const w of [1280, 1024, 768, 600, 390, 375, 320]) {
  const ctx = await b.newContext({ viewport: { width: w, height: 900 }, isMobile: w < 800, hasTouch: w < 800 });
  if (theme === 'dark') await ctx.addInitScript(() => localStorage.setItem('ts-theme', 'dark'));
  const p = await ctx.newPage(); await p.goto(base + '/thai-massage/', { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
  const r = await p.evaluate(() => [...document.querySelectorAll('.wp-block-uagb-image img')].map((i) => { const r = i.getBoundingClientRect(); return { file: (i.currentSrc || i.src).split('/').pop(), loaded: i.complete && i.naturalWidth > 0, box: Math.round(r.width) + 'x' + Math.round(r.height), fit: getComputedStyle(i).objectFit, alt: i.alt, side: document.documentElement.scrollWidth - document.documentElement.clientWidth }; }));
  check(`${w}px: three illustrations`, r.length === 3, r.length);
  check(`${w}px: the reviewed files in the reviewed order`, JSON.stringify(r.map((x) => x.file)) === JSON.stringify(FILES), r.map((x) => x.file).join(' | '));
  check(`${w}px: all loaded, object-fit cover`, r.every((x) => x.loaded && x.fit === 'cover'));
  check(`${w}px: the page-design box sizes`, JSON.stringify(r.map((x) => x.box)) === JSON.stringify(BOX[w]), r.map((x) => x.box).join(' | '));
  check(`${w}px: distinct non-empty alt text`, r.every((x) => x.alt.trim().length > 10) && new Set(r.map((x) => x.alt)).size === 3);
  check(`${w}px: no side-scroll`, r.every((x) => x.side <= 0));
  if (prefix && [1280, 390, 320].includes(w)) { const im = p.locator('.wp-block-uagb-image img'); await p.screenshot({ path: `${prefix}-${w}-top.png`, clip: { x: 0, y: 0, width: w, height: Math.min(900, w < 800 ? 700 : 900) } }); for (const n of [1, 2]) { await im.nth(n).scrollIntoViewIfNeeded(); await p.waitForTimeout(250); const bb = await im.nth(n).boundingBox(); if (bb) await p.screenshot({ path: `${prefix}-${w}-img${n + 1}.png`, clip: { x: Math.max(0, bb.x - 30), y: Math.max(0, bb.y - 30), width: Math.min(w, bb.width + 60), height: bb.height + 60 } }); } }
  await ctx.close();
}
await b.close(); console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed')); process.exit(fails ? 1 : 0);

// ref: ./install-thai-images.php — the installer whose result this checks; ./check-thai-page.mjs — the sibling check for the sections below the hero

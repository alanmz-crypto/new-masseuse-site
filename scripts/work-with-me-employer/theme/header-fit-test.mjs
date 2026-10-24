// Phone/tablet header fit test (read-only; public requests only). Usage: node header-fit-test.mjs <base-url> [css-file-to-inject]
// Added 2026-10-19 (Ryan: the dark/light button made the phone header wrap "Thai Solitude" and split the word "Solitude" on an iPhone SE). Pass header-fit.css to PROTOTYPE the fix in the
// browser without touching any site; run without it against a site that already has the ts-header-fit block (or has none, to see the problem). Exit 1 if any width/theme fails.
// For every width: the site name on ONE line, no word split, nothing overlapping, tap targets kept (toggle >= 44, menu >= 44), >= 8 px between the two buttons, no side-scroll. Light and dark.
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium, firefox } = require('playwright');
const isFF = process.env.BROWSER === 'firefox';   // BROWSER=firefox node header-fit-test.mjs <url>: a second engine (WebKit, i.e. iOS Safari, does not run on this machine)
const [base, cssFile] = process.argv.slice(2);
const css = cssFile ? fs.readFileSync(cssFile, 'utf8') : '';
const widths = [300, 320, 330, 340, 350, 360, 375, 380, 385, 389, 390, 393, 412, 430, 600, 768, 900];
const b = isFF ? await firefox.launch() : await chromium.launch({ executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
let fails = 0, rows = [];
for (const theme of ['light', 'dark']) for (const w of widths) {
  const ctx = await b.newContext(isFF ? { viewport: { width: w, height: 800 } } : { viewport: { width: w, height: 800 }, isMobile: true, hasTouch: true });
  if (theme === 'dark') await ctx.addInitScript(() => localStorage.setItem('ts-theme', 'dark'));
  const p = await ctx.newPage(); await p.goto(base + '/', { waitUntil: 'networkidle' });
  if (css) await p.addStyleTag({ content: css });
  await p.waitForTimeout(250);
  const r = await p.evaluate(() => {
    const hdr = document.querySelector('#ast-mobile-header'); if (!hdr || getComputedStyle(hdr).display === 'none') return { skip: true };
    const q = (s) => hdr.querySelector(s); const box = (e) => { const r = e.getBoundingClientRect(); return { l: r.left, t: r.top, r: r.right, b: r.bottom, w: r.width, h: r.height }; };
    const title = q('.site-title a'), tg = q('.ts-theme-toggle'), mn = q('.menu-toggle'), logo = q('.custom-logo');
    const range = document.createRange(); range.selectNodeContents(title); const rects = [...range.getClientRects()].filter((x) => x.width > 0);
    const tops = new Set(rects.map((x) => Math.round(x.top)));
    const words = []; const t = title.firstChild; if (t && t.nodeType === 3) { const re = /\S+/g; let m; while ((m = re.exec(t.textContent))) { const rr = document.createRange(); rr.setStart(t, m.index); rr.setEnd(t, m.index + m[0].length); if ([...rr.getClientRects()].filter((x) => x.width > 0).length > 1) words.push(m[0]); } }
    const B = { logo: box(logo), title: box(title), toggle: box(tg), menu: box(mn) };
    const ov = (a, c) => a.l < c.r - 0.5 && c.l < a.r - 0.5 && a.t < c.b - 0.5 && c.t < a.b - 0.5;
    const overlaps = [['logo', 'title'], ['title', 'toggle'], ['toggle', 'menu'], ['title', 'menu']].filter(([x, y]) => ov(B[x], B[y])).map((x) => x.join('/'));
    return { lines: tops.size, broken: words, overlaps, fs: parseFloat(getComputedStyle(title).fontSize), toggle: [Math.round(B.toggle.w), Math.round(B.toggle.h)], menu: [Math.round(B.menu.w), Math.round(B.menu.h)],
      gap: Math.round(B.menu.l - B.toggle.r), titleRight: Math.round(B.title.r), toggleLeft: Math.round(B.toggle.l), headerH: Math.round(hdr.getBoundingClientRect().height), side: document.documentElement.scrollWidth - document.documentElement.clientWidth, right: Math.round(B.menu.r), vw: innerWidth };
  });
  if (r.skip) { rows.push(`${theme} ${w}: (mobile header not shown)`); await ctx.close(); continue; }
  const bad = r.lines !== 1 || r.broken.length || r.overlaps.length || r.toggle[0] < 44 || r.toggle[1] < 44 || r.menu[0] < 44 || r.gap < 8 || r.side > 0 || r.right > r.vw - 8;
  if (bad) fails++;
  rows.push(`${bad ? 'FAIL' : 'ok  '} ${theme.padEnd(5)} ${String(w).padStart(3)}px  lines=${r.lines} broken=${JSON.stringify(r.broken)} overlap=${JSON.stringify(r.overlaps)} title=${r.fs.toFixed(1)}px toggle=${r.toggle.join('x')} menu=${r.menu.join('x')} gap=${r.gap} header-h=${r.headerH} side-scroll=${r.side}`);
  await ctx.close();
}
await b.close(); console.log(rows.join('\n')); console.log(fails ? `\nRESULT: ${fails} width/theme combination(s) FAIL` : '\nRESULT: every width and theme passes');
process.exit(fails ? 1 : 0);

// ref: ./header-fit.css — the fix this tests; ./install-header-fit.php — the guarded installer that puts it into Additional CSS

// Contrast of every text element the Corporate & Group Massage change adds, in the LIGHT and the DARK version, at 1280 and 390 px (read-only; public requests only):
//   the whole new page (.ts-corp, with the first FAQ answer open), the announcement strip (.ts-corp-band, on /about/), the Services tile and its description (the new row), and the new menu item.
// WCAG 2.x ratio of the computed text colour against the effective background (alpha layers composited down the ancestors). An element on a gradient or an image background is listed as
// "not measurable" (never counted as a pass). AA needs 4.5:1, or 3:1 for large text (24 px, or 18.66 px bold). Method copied from accessibility/check-research-contrast.mjs.
// Usage: node check-corporate-contrast.mjs <base-url> [firefox]      Exit 1 if any measured element is below AA or too little was measured. Exit 2 = SiteGround's bot challenge (INVALID).
import { createRequire } from 'module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const useFirefox = process.env.BROWSER === 'firefox' || process.argv[3] === 'firefox';
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
const TARGETS = [['/corporate-massage-portland/', '.ts-corp', 60], ['/about/', '.ts-corp-band', 4], ['/services/', '.ts-corp-tile', 2], ['/services/', '.ts-corp-tile', 0]];
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
for (const theme of ['light', 'dark']) {
  for (const width of [1280, 390]) {
    for (const [p, sel, minCount] of TARGETS.slice(0, 3)) {
      const tag = `${p} ${sel} ${theme} ${width}px`;
      const ctx = await browser.newContext({ viewport: { width, height: 900 } });
      if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
      const page = await ctx.newPage();
      const resp = await page.goto(base + p, { waitUntil: 'networkidle' });
      if (resp && resp.status() === 202) { console.log('INVALID: SiteGround answered the bot challenge (HTTP 202); nothing was tested.'); await browser.close(); process.exit(2); }
      if (!resp || resp.status() !== 200) { check(`${tag}: HTTP 200`, false, resp && resp.status()); await ctx.close(); continue; }
      await page.waitForTimeout(500);
      const r = await page.evaluate((sel) => {
        const parse = (c) => { const m = c.match(/rgba?\(([^)]+)\)/); if (!m) return null; const v = m[1].split(/[ ,\/]+/).filter(Boolean).map(Number); return { r: v[0], g: v[1], b: v[2], a: v.length > 3 ? v[3] : 1 }; };
        const lum = (c) => { const f = (x) => { x /= 255; return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4); }; return 0.2126 * f(c.r) + 0.7152 * f(c.g) + 0.0722 * f(c.b); };
        const roots = [...document.querySelectorAll(sel)];
        // on Services also the new row's description and on every page the new menu item
        const extra = sel === '.ts-corp-tile' ? [...document.querySelectorAll('.ts-corp-tile')].map(t => t.parentElement) : [];
        const menu = [...document.querySelectorAll('.menu-item.ts-corp-nav')];
        const all = new Set(); [...roots, ...extra, ...menu].forEach(root => { all.add(root); root.querySelectorAll('*').forEach(e => all.add(e)); });
        const rows = []; let skipped = 0;
        for (const e of all) {
          const own = [...e.childNodes].filter(n => n.nodeType === 3 && n.textContent.trim()).map(n => n.textContent.trim()).join(' ');
          if (!own) continue;
          const cs = getComputedStyle(e);
          if (cs.visibility === 'hidden' || cs.display === 'none' || !e.getClientRects().length) continue;
          let fg = parse(cs.color); if (!fg) continue;
          const layers = []; let n = e, unmeasurable = false;
          while (n && n.nodeType === 1) { const s = getComputedStyle(n); if (s.backgroundImage && s.backgroundImage !== 'none') { unmeasurable = true; break; } const bg = parse(s.backgroundColor); if (bg && bg.a > 0) { layers.push(bg); if (bg.a >= 1) break; } n = n.parentElement; }
          if (unmeasurable) { skipped++; rows.push({ t: own.slice(0, 40), ratio: null }); continue; }
          let bgc = layers.length && layers[layers.length - 1].a >= 1 ? layers.pop() : { r: 255, g: 255, b: 255, a: 1 };
          while (layers.length) { const l = layers.pop(); bgc = { r: l.r * l.a + bgc.r * (1 - l.a), g: l.g * l.a + bgc.g * (1 - l.a), b: l.b * l.a + bgc.b * (1 - l.a), a: 1 }; }
          if (fg.a < 1) fg = { r: fg.r * fg.a + bgc.r * (1 - fg.a), g: fg.g * fg.a + bgc.g * (1 - fg.a), b: fg.b * fg.a + bgc.b * (1 - fg.a), a: 1 };
          const L1 = lum(fg), L2 = lum(bgc); const ratio = (Math.max(L1, L2) + 0.05) / (Math.min(L1, L2) + 0.05);
          const px = parseFloat(cs.fontSize), bold = parseInt(cs.fontWeight, 10) >= 700;
          rows.push({ t: own.slice(0, 40), ratio: Math.round(ratio * 100) / 100, need: (px >= 24 || (px >= 18.66 && bold)) ? 3 : 4.5, tag: e.tagName.toLowerCase() });
        }
        return { rows, skipped, roots: roots.length };
      }, sel);
      const m = r.rows.filter(x => x.ratio !== null);
      const low = m.slice().sort((a, b) => a.ratio - b.ratio)[0];
      const bad = m.filter(x => x.ratio < x.need);
      check(`${tag}: ${m.length} text elements measured, lowest ${low ? low.ratio + ':1 (' + low.tag + ' "' + low.t + '")' : 'none'}, ${r.skipped} not measurable`, r.roots >= 1 && m.length >= minCount && bad.length === 0, bad.slice(0, 3).map(x => x.ratio + ' "' + x.t + '"').join(' | '));
      await ctx.close();
    }
  }
}
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ../accessibility/check-research-contrast.mjs — the method this copies; ./corporate-massage.css — the colours measured

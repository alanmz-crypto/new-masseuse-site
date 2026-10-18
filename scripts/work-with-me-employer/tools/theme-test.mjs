// Read-only test of the dark/light mode button (theme/install-theme-toggle.php): public GETs only; the browser's own localStorage is the only thing written (a throwaway context).
//   node theme-test.mjs <base-url> [--contrast]       e.g. node theme-test.mjs https://staging33.thaisolitude.com --contrast
// Behaviour: light by default (no data attribute), a visible 44px button on desktop and on a phone, click -> dark + aria-pressed, the choice survives a reload and another page and is already
// set when the DOM is parsed (no light flash), keyboard Enter and Space work, back to light removes the attribute, printing never uses the dark colours, no console errors.
// --contrast: axe color-contrast (WCAG AA) on all 10 pages in light and in dark, desktop and phone; light mode must have no failure that dark mode introduces (reports both).
// Playwright is taken from the Mirror project; axe-core 4.13.0 from ~/.cache/ts-axe (install line in a11y-audit.mjs).
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium, firefox } = require('playwright');
const engine = process.env.BROWSER === 'firefox' ? firefox : chromium;   // BROWSER=firefox node theme-test.mjs <url>: test in more than one engine (WebKit does not run on this machine)
const [base, flag] = process.argv.slice(2);
if (!base) { console.error('usage: node theme-test.mjs <base-url> [--contrast]'); process.exit(1); }
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail ? `  [${detail}]` : '')); if (!ok) fails++; };
const b = await engine.launch();
console.log('engine:', process.env.BROWSER === 'firefox' ? 'firefox' : 'chromium');
const attr = p => p.evaluate(() => document.documentElement.getAttribute('data-ts-theme'));
const vis = p => p.evaluate(() => [...document.querySelectorAll('.ts-theme-toggle')].filter(x => x.offsetParent).map(x => { const r = x.getBoundingClientRect(); return { w: Math.round(r.width), h: Math.round(r.height), pressed: x.getAttribute('aria-pressed'), label: x.getAttribute('aria-label') }; }));

for (const [w, h, v] of [[1280, 900, 'desktop'], [390, 844, 'phone']]) {
  const ctx = await b.newContext({ viewport: { width: w, height: h }, reducedMotion: 'reduce' });
  const p = await ctx.newPage();
  const errors = [];
  p.on('console', m => { if (m.type() === 'error' && !/favicon|Failed to load resource/i.test(m.text())) errors.push(m.text().slice(0, 120)); });
  p.on('pageerror', e => errors.push(String(e).slice(0, 120)));
  await p.goto(base + '/', { waitUntil: 'networkidle', timeout: 60000 });
  check(`[${v}] light by default (no data-ts-theme)`, (await attr(p)) === null);
  let btn = await vis(p);
  check(`[${v}] exactly one visible button, at least 44x44, labelled "Dark mode", aria-pressed=false`, btn.length === 1 && btn[0].w >= 44 && btn[0].h >= 44 && btn[0].label === 'Dark mode' && btn[0].pressed === 'false', JSON.stringify(btn));
  await p.locator('.ts-theme-toggle:visible').first().click();
  btn = await vis(p);
  check(`[${v}] click -> dark and aria-pressed=true`, (await attr(p)) === 'dark' && btn[0].pressed === 'true');
  const bg = await p.evaluate(() => getComputedStyle(document.body).backgroundColor);
  check(`[${v}] dark page background is dark`, /^rgb\((\d+), (\d+), (\d+)\)$/.test(bg) && Math.max(...bg.match(/\d+/g).map(Number)) < 60, bg);
  await p.reload({ waitUntil: 'domcontentloaded' });
  check(`[${v}] dark is already set when the page is parsed (no light flash)`, (await attr(p)) === 'dark');
  await p.goto(base + '/about/', { waitUntil: 'networkidle', timeout: 60000 });
  check(`[${v}] dark survives another page`, (await attr(p)) === 'dark' && (await vis(p))[0].pressed === 'true');
  await p.emulateMedia({ media: 'print' });
  const pbg = await p.evaluate(() => getComputedStyle(document.body).backgroundColor);
  check(`[${v}] print never uses the dark background`, Math.max(...pbg.match(/\d+/g).map(Number)) > 200, pbg);
  await p.emulateMedia({ media: 'screen' });
  await p.locator('.ts-theme-toggle:visible').first().focus();
  await p.keyboard.press('Enter');
  check(`[${v}] keyboard Enter -> light, attribute removed`, (await attr(p)) === null && (await vis(p))[0].pressed === 'false');
  await p.keyboard.press('Space');
  check(`[${v}] keyboard Space -> dark`, (await attr(p)) === 'dark');
  await p.keyboard.press('Space');
  await p.reload({ waitUntil: 'domcontentloaded' });
  check(`[${v}] light choice also survives a reload`, (await attr(p)) === null);
  check(`[${v}] no console or script errors`, errors.length === 0, errors.join(' | '));
  await ctx.close();
}

if (flag === '--contrast') {
  const AXE = fs.readFileSync('/home/lauer/.cache/ts-axe/node_modules/axe-core/axe.min.js', 'utf8');
  const pages = ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/work-with-me/', '/contact/', '/privacy-policy/', '/accessibility/'];
  for (const mode of ['light', 'dark']) {
    let total = 0; const bad = [];
    for (const [w, h, v] of [[1280, 900, 'desktop'], [390, 844, 'phone']]) {
      const ctx = await b.newContext({ viewport: { width: w, height: h }, reducedMotion: 'reduce' });
      await ctx.addInitScript(m => { try { localStorage.setItem('ts-theme', m); } catch (e) { /* ignore */ } }, mode);
      for (const pg of pages) {
        const p = await ctx.newPage();
        await p.goto(base + pg, { waitUntil: 'networkidle', timeout: 60000 });
        await p.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important}' });
        await p.evaluate(AXE);
        const r = await p.evaluate(async () => { const x = await axe.run(document, { runOnly: { type: 'rule', values: ['color-contrast'] } }); return x.violations.flatMap(vv => vv.nodes.map(n => `${n.target.join(' ').slice(0, 60)} ${n.any[0] && n.any[0].data ? n.any[0].data.contrastRatio : ''}`)); });
        total += r.length; r.forEach(x => bad.push(`${v} ${pg} ${x}`));
        await p.close();
      }
      await ctx.close();
    }
    check(`contrast (axe, WCAG AA) in ${mode} mode: 20 page-views, no failures`, total === 0, bad.slice(0, 6).join(' ; '));
  }
}
await b.close();
console.log(fails ? `\n${fails} CHECK(S) FAILED` : '\nALL CHECKS PASSED');
process.exit(fails ? 1 : 0);

// ref: ../theme/install-theme-toggle.php — the installer whose result this tests; ./a11y-audit.mjs — same Playwright/axe setup

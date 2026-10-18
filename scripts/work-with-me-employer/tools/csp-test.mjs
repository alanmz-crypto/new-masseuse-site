// Content-Security-Policy testing without touching the server.
//   node csp-test.mjs <base-url> --discover                       what do the 8 public pages load, and from where? (no policy)
//   node csp-test.mjs <base-url> --check [screenshot-dir]         NO injection: load the pages as a logged-out visitor with whatever header the SERVER really sends; report it and any violations
//   node csp-test.mjs <base-url> "<policy>" [screenshot-dir]      load every page with <policy> ADDED to the document response by the test browser
//                                                                 (enforcing, not report-only) and report every violation; optional screenshots
// Add --browser=firefox (or webkit, if it runs here) to any of the above: RUN IT IN FIREFOX TOO. Chrome and Firefox/Safari judge an <embed> differently (Chrome under frame-src, Firefox under
// object-src for the whole redirect chain), and a Chrome-only pass missed that the policy blocked the Contact page's Google Map in Firefox (found 2026-10-18; fixed in install-headers-csp-v2.php).
// Read-only: it only loads public pages. The header is added inside the browser (request interception), so nothing on any site changes.
// Playwright is taken from the Mirror project, as the other tools here do.
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const engine = ((process.argv.find((a) => a.startsWith('--browser=')) || '--browser=chromium').split('=')[1]);
if (!pw[engine]) { console.error('unknown --browser=' + engine + ' (chromium, firefox or webkit)'); process.exit(1); }
const [base, mode, shotDir] = process.argv.slice(2).filter((a) => !a.startsWith('--browser='));
if (!base || !mode) { console.error('usage: node csp-test.mjs <base-url> --discover | --check | "<policy>" [screenshot-dir]'); process.exit(1); }
const discover = mode === '--discover';
const check = mode === '--check';
const pages = ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/work-with-me/', '/contact/'];
const views = [[1280, 900, 'd'], [390, 844, 'm']];
if (shotDir) fs.mkdirSync(shotDir, { recursive: true });
const b = await pw[engine].launch();
const origins = {}, viol = {}, realCsp = {}, inline = { scripts: 0, styles: 0, styleAttrs: 0, handlers: 0, jsHrefs: 0, iframes: 0, forms: 0, evalLike: 0 };
let bad = 0;
const bump = (o, k) => { o[k] = (o[k] || 0) + 1; };
for (const [w, h, v] of views) {
  const ctx = await b.newContext({ viewport: { width: w, height: h }, reducedMotion: 'reduce', deviceScaleFactor: 1 });
  if (!discover && !check) {
    await ctx.route('**/*', async (route) => {
      if (route.request().resourceType() === 'document') {
        const resp = await route.fetch();
        await route.fulfill({ response: resp, headers: { ...resp.headers(), 'content-security-policy': mode } });
      } else { await route.continue(); }
    });
  }
  for (const p of pages) {
    const page = await ctx.newPage();
    await page.addInitScript(() => { window.__v = []; document.addEventListener('securitypolicyviolation', (e) => window.__v.push([e.violatedDirective, e.blockedURI || '(inline)', (e.sample || '').slice(0, 50)])); });
    page.on('console', (m) => { const t = m.text(); if (/Content-Security-Policy/i.test(t)) bump(viol, 'console (' + engine + ') | ' + t.replace(/^\[JavaScript Error: "?/, '').slice(0, 170)); });   // Firefox reports blocks as console errors
    page.on('request', (r) => { try { bump(origins, r.resourceType() + ' <- ' + new URL(r.url()).origin); } catch (e) { /* data: urls */ } });
    try {
      const r = await page.goto(base + p, { waitUntil: 'networkidle', timeout: 60000 });
      if (r.status() !== 200) { console.log('status', r.status(), p); bad++; }
      bump(realCsp, r.headers()['content-security-policy'] || '(none)');
      await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' }).catch(() => {});
      await page.evaluate(async () => { const step = innerHeight * 0.8; for (let y = 0; y < document.body.scrollHeight; y += step) { scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } scrollTo(0, 0); });
      await page.waitForTimeout(500);
      if (v === 'd') {
        const c = await page.evaluate(() => ({
          scripts: [...document.scripts].filter(s => !s.src).length,
          styles: document.querySelectorAll('style').length,
          styleAttrs: document.querySelectorAll('[style]').length,
          handlers: [...document.querySelectorAll('*')].filter(e => [...e.attributes].some(a => /^on/i.test(a.name))).length,
          jsHrefs: document.querySelectorAll('a[href^="javascript:"]').length,
          iframes: document.querySelectorAll('iframe').length,
          forms: document.querySelectorAll('form').length,
        }));
        for (const k of Object.keys(c)) inline[k] += c[k];
      }
      const vs = await page.evaluate(() => window.__v || []);
      for (const [dir, blocked, sample] of vs) bump(viol, dir + ' | ' + blocked.slice(0, 80) + (sample ? ' | ' + sample : ''));
      if (shotDir) await page.screenshot({ path: `${shotDir}/${p === '/' ? 'home' : p.replace(/\//g, '')}-${v}.png`, fullPage: true });
    } catch (e) { console.log('FAILED', p, v, String(e).slice(0, 100)); bad++; }
    await page.close();
  }
  await ctx.close();
}
await b.close();
const top = (o) => Object.entries(o).sort((a, b) => b[1] - a[1]);
if (discover) {
  console.log('== what the 8 pages load (resource type <- origin; request counts over 16 page-views) ==');
  for (const [k, n] of top(origins)) console.log('  ' + String(n).padStart(5) + '  ' + k);
  console.log('\n== inline code on the 8 desktop page-views (totals) ==');
  console.log('  ' + Object.entries(inline).filter(([k]) => k !== 'evalLike').map(([k, n]) => k + ' ' + n).join(' | '));
} else {
  const n = Object.values(viol).reduce((a, c) => a + c, 0);
  if (check) {
    console.log('Content-Security-Policy header the SERVER really sent, over 16 page-views (' + engine + '):');
    for (const [k, c] of top(realCsp)) console.log('  ' + String(c).padStart(3) + ' x ' + (k === '(none)' ? '(no header)' : k.slice(0, 120) + (k.length > 120 ? ' ...' : '')));
  } else console.log('policy: ' + mode.slice(0, 160) + (mode.length > 160 ? ' ...' : ''));
  console.log('violations across 16 page-views: ' + n + (bad ? ' | load problems: ' + bad : ''));
  for (const [k, c] of top(viol).slice(0, 15)) console.log('  ' + String(c).padStart(4) + ' x ' + k);
}
process.exit(bad ? 1 : 0);

// ref: ../post-change-test.sh — the pixel comparison tools it uses live beside this file; compare-shots.py compares two screenshot sets

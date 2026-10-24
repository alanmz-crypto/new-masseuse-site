// Read-only accessibility audit of the public pages, in a real browser: axe-core (WCAG 2.0/2.1/2.2 A and AA rules) plus checks axe cannot make: language, titles, headings,
// image alt text, landmarks, zoom allowed, small click targets, side-scrolling at 320 px, keyboard order, visible focus, focus hidden behind something, accordion by keyboard.
//   node a11y-audit.mjs <base-url> [out.json]       e.g. node a11y-audit.mjs https://staging33.thaisolitude.com
//   TS_THEME=dark node a11y-audit.mjs <base-url> [out.json]   the same audit with the dark mode switched on (once the dark/light button from theme/ is installed)
//   TS_PAGES=/thai-massage/ node a11y-audit.mjs <base-url> [out.json]   only the named page(s), comma-separated (a one-page change needs far less traffic than the whole site)
// It only loads public pages like a visitor (10 pages x 3 window sizes); it changes nothing. Keep traffic low: SiteGround blocks addresses that send bursts (HTTP 202 = INVALID).
// An automated pass finds only part of what WCAG asks for (roughly a third of the criteria can be tested by a machine): a clean result means "no automatically detectable problems",
// never "conforms". The accessibility statement must say what was actually done.
// One-time install (isolated folder, no install scripts): mkdir -p ~/.cache/ts-axe && cd ~/.cache/ts-axe && npm init -y && npm install axe-core@4.13.0 --ignore-scripts --save-exact
// Playwright is taken from the Mirror project, as the other tools here do.
import { createRequire } from 'module';
import fs from 'fs';
import os from 'os';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [base, outFile] = process.argv.slice(2);
if (!base) { console.error('usage: node a11y-audit.mjs <base-url> [out.json]'); process.exit(1); }
const AXE = os.homedir() + '/.cache/ts-axe/node_modules/axe-core/axe.min.js';
if (!fs.existsSync(AXE)) { console.error('axe-core is not installed in ~/.cache/ts-axe (see the install line at the top of this file)'); process.exit(2); }
const pages = process.env.TS_PAGES ? process.env.TS_PAGES.split(',') : ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/work-with-me/', '/contact/', '/privacy-policy/', '/accessibility/'];   // TS_PAGES=/thai-massage/ audits only the named page(s): keeps traffic to SiteGround low after a one-page change
const views = [{ n: 'desktop', w: 1280, h: 900, dpr: 1 }, { n: 'phone', w: 390, h: 844, dpr: 2, mobile: true }, { n: 'reflow320', w: 320, h: 640, dpr: 1, mobile: true }];
const TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];
const result = [];
const b = await chromium.launch();
for (const v of views) {
  const ctx = await b.newContext({ viewport: { width: v.w, height: v.h }, deviceScaleFactor: v.dpr, isMobile: !!v.mobile, hasTouch: !!v.mobile });
  if (process.env.TS_THEME === 'dark') { await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) { /* ignore */ } }); }   // TS_THEME=dark: audit the dark mode (the header button's saved choice)
  for (const p of pages) {
    const page = await ctx.newPage();
    const r0 = await page.goto(base + p, { waitUntil: 'networkidle', timeout: 90000 });
    if (r0.status() !== 200) { result.push({ page: p, view: v.n, invalid: 'HTTP ' + r0.status() + ' (202 = SiteGround challenge)' }); await page.close(); continue; }
    await page.waitForTimeout(700);
    await page.addScriptTag({ path: AXE });
    const states = [];   // axe runs: the page as loaded, then with each accordion panel opened in turn (closed panels hide their text)
    const runAxe = (label) => page.evaluate(async (tags) => {
      const res = await axe.run(document, { runOnly: { type: 'tag', values: tags }, resultTypes: ['violations', 'incomplete'] });
      const f = (x) => ({ id: x.id, impact: x.impact, help: x.help, nodes: x.nodes.length, sample: x.nodes[0] && x.nodes[0].html.slice(0, 160), target: x.nodes[0] && String(x.nodes[0].target).slice(0, 120) });
      return { violations: res.violations.map(f), incomplete: res.incomplete.map(f) };
    }, TAGS).then((x) => ({ label, ...x }));
    states.push(await runAxe('as loaded'));
    const n = await page.locator('.uagb-faq-item').count();
    for (let i = 0; i < n; i++) {
      await page.locator('.uagb-faq-item .uagb-faq-questions-button').nth(i).click();
      await page.waitForTimeout(700);
      states.push(await runAxe('accordion panel ' + (i + 1) + ' open'));
    }
    // native collapsible panels (core "details" blocks, used on the Thai page since 2026-10-19): audit them all open, then put them back so the keyboard test below sees the page as loaded
    const nDet = await page.locator('details.wp-block-details').count();
    if (nDet) {
      const was = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map((d) => d.open));
      await page.evaluate(() => document.querySelectorAll('details.wp-block-details').forEach((d) => { d.open = true; }));
      await page.waitForTimeout(500);
      states.push(await runAxe('all ' + nDet + ' native panels open'));
      await page.evaluate((w) => document.querySelectorAll('details.wp-block-details').forEach((d, i) => { d.open = w[i]; }), was);
    }
    const extra = await page.evaluate(() => {
      const q = (s) => [...document.querySelectorAll(s)];
      const heads = q('h1,h2,h3,h4,h5,h6').filter((h) => h.offsetParent !== null).map((h) => +h.tagName[1]);
      let jumps = 0; for (let i = 1; i < heads.length; i++) if (heads[i] - heads[i - 1] > 1) jumps++;
      const vp = (document.querySelector('meta[name=viewport]') || {}).content || '';
      const small = q('a,button,[role=button],[role=tab],input,select,textarea,summary').filter((e) => {
        const r = e.getBoundingClientRect(); if (!r.width || !r.height || getComputedStyle(e).visibility === 'hidden') return false;
        const inline = e.tagName === 'A' && getComputedStyle(e).display === 'inline' && e.closest('p,li,span,h1,h2,h3,h4,h5,h6');
        return !inline && (r.width < 24 || r.height < 24);
      }).map((e) => (e.textContent || e.getAttribute('aria-label') || e.className).trim().slice(0, 40) + ' ' + Math.round(e.getBoundingClientRect().width) + 'x' + Math.round(e.getBoundingClientRect().height));
      return {
        lang: document.documentElement.lang, title: document.title, h1: heads.filter((h) => h === 1).length, headingJumps: jumps,
        imgs: q('img').map((i) => ({ alt: i.getAttribute('alt'), src: (i.currentSrc || i.src).split('/').pop().slice(0, 50), w: Math.round(i.getBoundingClientRect().width) })),
        landmarks: { main: q('main,[role=main]').length, nav: q('nav,[role=navigation]').length, header: q('header,[role=banner]').length, footer: q('footer,[role=contentinfo]').length },
        zoomBlocked: /user-scalable\s*=\s*(no|0)/i.test(vp) || /maximum-scale\s*=\s*(0?\.\d|1(\.0)?)(\D|$)/i.test(vp),
        sideScroll: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1, smallTargets: small,
        skipLink: !!q('a[href^="#"]').find((a) => /skip/i.test(a.textContent)),
      };
    });
    // keyboard: Tab through the page; record visible focus and whether something sits on top of the focused element
    const kb = { stops: 0, noVisibleFocus: [], covered: [], trapSuspect: false };
    await page.evaluate(() => { document.activeElement && document.activeElement.blur(); window.scrollTo(0, 0); });
    const seen = new Set();
    for (let i = 0; i < 90; i++) {
      await page.keyboard.press('Tab');
      const s = await page.evaluate(() => {
        const e = document.activeElement; if (!e || e === document.body) return null;
        const r = e.getBoundingClientRect(); const cs = getComputedStyle(e);
        const visible = (cs.outlineStyle !== 'none' && parseFloat(cs.outlineWidth) > 0) || (cs.boxShadow && cs.boxShadow !== 'none') || (e.matches(':focus-visible') && cs.textDecorationLine === 'underline');
        const cx = Math.min(Math.max(r.left + r.width / 2, 0), innerWidth - 1), cy = Math.min(Math.max(r.top + r.height / 2, 0), innerHeight - 1);
        const top = document.elementFromPoint(cx, cy);
        return { x: r.left, y: r.top, w: r.width, h: r.height, id: e.tagName + ':' + (e.textContent || e.getAttribute('aria-label') || e.className).trim().slice(0, 30) + ':' + Math.round(r.top + scrollY), visible, covered: !!top && !(e === top || e.contains(top) || top.contains(e)) && r.width > 0, inView: r.bottom > 0 && r.top < innerHeight };
      });
      if (!s) continue;
      if (seen.has(s.id)) break;
      seen.add(s.id); kb.stops++;
      // A first look can be wrong: themes animate focus styles (measured too early the element looks unstyled), and a ring may be drawn as a border or pseudo-element that
      // the computed-style test does not see. So anything flagged is re-checked once things have settled, by pixels: is the element photographed with focus different from
      // the same spot without focus?
      if (!s.visible && s.inView && s.w > 0 && s.h > 0) {
        await page.waitForTimeout(400);
        const clip = { x: Math.max(0, s.x - 8), y: Math.max(0, s.y - 8), width: Math.min(s.w + 16, v.w - Math.max(0, s.x - 8)), height: Math.min(s.h + 16, v.h - Math.max(0, s.y - 8)) };
        if (clip.width > 1 && clip.height > 1) {
          const withFocus = await page.screenshot({ clip });
          await page.keyboard.press('Shift+Tab'); await page.waitForTimeout(400);
          const without = await page.screenshot({ clip });
          await page.keyboard.press('Tab'); await page.waitForTimeout(150);
          if (!withFocus.equals(without)) s.visible = true;
        }
      }
      if (!s.visible) kb.noVisibleFocus.push(s.id);
      if (s.covered && s.inView) {
        await page.waitForTimeout(450);   // let smooth scrolling finish, then look again
        const still = await page.evaluate(() => { const e = document.activeElement; if (!e) return false; const r = e.getBoundingClientRect(); const cx = Math.min(Math.max(r.left + r.width / 2, 1), innerWidth - 2), cy = Math.min(Math.max(r.top + r.height / 2, 1), innerHeight - 2); const top = document.elementFromPoint(cx, cy); return !!top && !(e === top || e.contains(top) || top.contains(e)); });
        if (still) kb.covered.push(s.id);
      }
    }
    // accordion by keyboard: focus the first panel header, press Enter, then Space, and see whether the open/closed state changes
    let accordion = null;
    if (n > 1) {
      const state = () => page.evaluate(() => [...document.querySelectorAll('.uagb-faq-item')].map((e) => e.classList.contains('uagb-faq-item-active')));
      const before = await state();
      await page.locator('.uagb-faq-item').nth(1).focus();
      await page.keyboard.press('Enter'); await page.waitForTimeout(700); const afterEnter = await state();
      await page.keyboard.press('Space'); await page.waitForTimeout(700); const afterSpace = await state();
      accordion = { before: before.join(','), enter: afterEnter.join(','), space: afterSpace.join(','), enterWorks: before.join() !== afterEnter.join(), spaceWorks: afterEnter.join() !== afterSpace.join() };
    }
    result.push({ page: p, view: v.n, states, extra, kb, accordion });
    await page.close();
    await new Promise((r) => setTimeout(r, 1200));
  }
  await ctx.close();
}
await b.close();
if (outFile) fs.writeFileSync(outFile, JSON.stringify(result, null, 1));
// ---- summary
const agg = {};
for (const r of result) {
  if (r.invalid) { console.log(`!! ${r.view} ${r.page}: ${r.invalid}; nothing measured`); continue; }
  for (const s of r.states) for (const x of s.violations) { const k = x.id; agg[k] = agg[k] || { impact: x.impact, help: x.help, where: new Set(), sample: x.sample }; agg[k].where.add(r.view + ' ' + r.page); }
}
console.log('\n== axe-core 4.13.0, WCAG 2.0/2.1/2.2 A+AA rules: VIOLATIONS ==');
console.log(Object.keys(agg).length ? '' : 'none on any page, any window size, any accordion state');
for (const [k, v] of Object.entries(agg)) console.log(`  ${k} [${v.impact}] ${v.help}\n     on: ${[...v.where].slice(0, 6).join(' | ')}${v.where.size > 6 ? ' ...' : ''}\n     e.g. ${v.sample}`);
const inc = {};
for (const r of result) if (!r.invalid) for (const s of r.states) for (const x of s.incomplete) { inc[x.id] = inc[x.id] || { help: x.help, n: 0 }; inc[x.id].n++; }
console.log('\n== axe "needs manual review" (could not be decided by the machine) ==');
for (const [k, v] of Object.entries(inc)) console.log(`  ${k}: ${v.help} (${v.n} runs)`);
console.log('\n== per page: language, title, h1 count, heading jumps, landmarks, images without alt, zoom blocked, side-scroll, small targets, keyboard ==');
for (const r of result) {
  if (r.invalid) continue; const e = r.extra;
  const noAlt = e.imgs.filter((i) => i.alt === null).length, emptyAlt = e.imgs.filter((i) => i.alt === '').length, hashAlt = e.imgs.filter((i) => i.alt && /^[\w-]{12,}(\.\w+)?$/.test(i.alt)).length;
  console.log(`${r.view.padEnd(9)} ${r.page.padEnd(24)} lang=${e.lang || 'MISSING'} title=${e.title ? 'yes' : 'MISSING'} h1=${e.h1} jumps=${e.headingJumps} main=${e.landmarks.main} nav=${e.landmarks.nav} footer=${e.landmarks.footer} img=${e.imgs.length}(noalt ${noAlt}, decorative ${emptyAlt}, filename-alt ${hashAlt}) zoomBlocked=${e.zoomBlocked} sideScroll=${e.sideScroll} smallTargets=${e.smallTargets.length} skipLink=${e.skipLink} | tab stops ${r.kb.stops}, no visible focus ${r.kb.noVisibleFocus.length}, covered ${r.kb.covered.length}`);
  if (e.smallTargets.length) console.log('     small targets: ' + e.smallTargets.slice(0, 6).join('; '));
  if (r.kb.noVisibleFocus.length) console.log('     no visible focus: ' + r.kb.noVisibleFocus.slice(0, 5).join('; '));
  if (r.kb.covered.length) console.log('     focus covered: ' + r.kb.covered.slice(0, 5).join('; '));
  if (r.accordion) console.log(`     accordion by keyboard: Enter ${r.accordion.enterWorks ? 'works' : 'DOES NOT toggle'}, Space ${r.accordion.spaceWorks ? 'works' : 'DOES NOT toggle'} (${r.accordion.before} -> ${r.accordion.enter} -> ${r.accordion.space})`);
}
process.exit(0);

// ref: ./capture-pages.mjs, ./perf-images.mjs — same Playwright setup and page list; ../post-change-test.sh — the after-change battery this complements

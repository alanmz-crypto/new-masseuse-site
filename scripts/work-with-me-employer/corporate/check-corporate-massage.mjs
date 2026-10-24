// Rendered check of the Corporate & Group Massage additions after install-corporate-massage.php (read-only; public requests only).
// Usage: node check-corporate-massage.mjs <base-url> <out-dir> [label] [firefox] [quick]    e.g. node check-corporate-massage.mjs https://staging37.thaisolitude.com ~/.cache/wwm-scratch/corp-shots staging37
//        BROWSER=firefox (or the argument 'firefox') uses Firefox instead of Chromium. The argument 'quick' makes about 30 page loads instead of about 90 (light mode at 1280 and 390 px, dark only for the page at 1280):
//        for a real site, which answers bursts of requests with SiteGround's bot challenge. The full run is for localhost.
// What it proves (light and dark, 1280 / 820 / 390 / 320 px as stated per check):
//   THE PAGE      HTTP 200, one h1, the section headings in order without a skipped level, 7 FAQ questions with no links in the answers (the first open, one open at a time, click and keyboard),
//                 no side-scroll, no script errors, no picture, no we/our/us, every internal link answers 200, both quote buttons carry the SAME mailto: link whose subject and body prompts decode correctly,
//                 the visible email address is a mailto link, title and meta description, canonical, the in-page anchors exist.
//   THE STRIP     exactly one on each of the 8 client pages, none on Home, Work With Me, the new page or the unrelated pages; directly under the header; compact; its link opens the new page; keyboard focus is visible.
//   SERVICES      four service links in order (the three existing ones unchanged, the new one last), the new row before "Your Session", the tile and its link, no overlap, no side-scroll.
//   MENU          the header, the footer and the phone menu all carry "Corporate Massage" (the header and footer only where there is room: see section 4 of corporate-massage.css) and the original items are still there in order.
// Exit codes: 0 all passed, 1 a check failed, 2 INVALID (SiteGround's bot challenge answered HTTP 202: nothing was tested; try once more later, not in a loop).
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = process.argv[3] || '/tmp/corp-shots';
const label = process.argv[4] || 'run';
fs.mkdirSync(out, { recursive: true });
const SLUG = '/corporate-massage-portland/';
const H2 = ['Chair Massage for Employee Appreciation', 'Table Massage at Suitable Venues', 'Plan the Time That Works for Your Team', 'What I’ll Plan With You', 'Three Steps to a Quote', 'About Butsaba',
  'Questions About Corporate Massage', 'Let’s Plan Your Team’s Massage Event'];
const FAQ = ['What kinds of events do you serve?', 'Do you offer individual home visits?', 'How many employees can receive massage in three hours?', 'Can you do table massage?', 'What does it cost?',
  'What does the host need to provide?', 'How do I reserve a date?'];
const BAND_PAGES = ['/about/', '/services/', '/contact/', '/privacy-policy/', '/accessibility/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/'];
const NO_BAND = ['/', '/work-with-me/', SLUG, '/personal-automation/', '/privacy/', '/terms/'];   // the last three are unrelated pages on this install: skipped when the site does not have them
const SERVICE_LINKS = ['/therapeutic-massage/', '/deep-tissue-massage/', '/thai-massage/', SLUG];
const MENU = ['Home', 'About', 'Services', 'Corporate Massage', 'For Practices', 'Contact'];
const PROMPTS = ['Organization:', 'Contact name and phone:', 'Event date', 'Event location', 'Approximate number of participants:', 'Service hours requested', 'Massage type'];
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const info = (name, detail = '') => console.log('INFO ' + name + (detail !== '' ? '  [' + detail + ']' : ''));
const useFirefox = process.env.BROWSER === 'firefox' || process.argv.includes('firefox');
const quick = process.argv.includes('quick');
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
let challenged = false;

async function open(ctx, p, errors) {
  const page = await ctx.newPage();
  page.on('pageerror', e => errors.push(String(e)));
  page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errors.push(m.text()); });
  const resp = await page.goto(base + p, { waitUntil: 'networkidle' });
  if (resp && resp.status() === 202 && (resp.headers()['sg-captcha'] || '').length) { challenged = true; }
  return { page, resp };
}
const newCtx = async (width, theme) => {
  const ctx = await browser.newContext({ viewport: { width, height: 900 } });
  if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
  return ctx;
};

// ---- THE PAGE ------------------------------------------------------------------------------------------------------------------------------------------
let mailtoHref = '';
const PAGE_VIEWS = quick ? [['light', 1280], ['light', 390], ['dark', 1280]] : ['light', 'dark'].flatMap(t => [1280, 820, 390, 320].map(w => [t, w]));
for (const [theme, width] of PAGE_VIEWS) {
  {
    const tag = `page ${theme}-${width}`;
    const ctx = await newCtx(width, theme);
    const errors = [];
    const { page, resp } = await open(ctx, SLUG, errors);
    if (challenged) { console.log('INVALID: SiteGround answered the bot challenge (HTTP 202); nothing was tested.'); await browser.close(); process.exit(2); }
    check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
    if (theme === 'dark') check(`${tag}: dark mode is on`, (await page.evaluate(() => document.documentElement.getAttribute('data-ts-theme'))) === 'dark');
    const r = await page.evaluate(() => {
      const txt = el => (el.textContent || '').replace(/\s+/g, ' ').trim();
      const heads = [...document.querySelectorAll('.entry-content h1, .entry-content h2, .entry-content h3, .entry-content h4')].map(h => ({ l: +h.tagName[1], t: txt(h) }));
      const faqQ = [...document.querySelectorAll('.uagb-question')].map(txt);
      const cta = [...document.querySelectorAll('.ts-corp a.wp-block-button__link')].filter(a => /Request a Corporate Massage Quote/.test(a.textContent)).map(a => a.getAttribute('href'));
      const mailLinks = [...document.querySelectorAll('.ts-corp a[href^="mailto:"]')].map(a => a.getAttribute('href'));
      const emailPlain = [...document.querySelectorAll('.ts-corp a[href="mailto:butsabad@gmail.com"]')].length;
      const internal = [...new Set([...document.querySelectorAll('.ts-corp a[href]')].map(a => a.getAttribute('href')).filter(h => h && !h.startsWith('mailto:') && !h.startsWith('#') && !h.startsWith('tel:')))];
      const anchors = ['booking-options', 'chair-massage', 'table-massage'].map(id => !!document.getElementById(id));
      const body = (document.querySelector('.ts-corp') || document.body).innerText;
      const seq = heads.map(h => h.l);
      let skipped = false; for (let i = 1; i < seq.length; i++) if (seq[i] - seq[i - 1] > 1) skipped = true;
      return { heads, faqQ, cta, mailLinks, emailPlain, internal, anchors, skipped, h1: heads.filter(h => h.l === 1).map(h => h.t), h2: heads.filter(h => h.l === 2).map(h => h.t),
        faqLinks: document.querySelectorAll('.uagb-faq-content a').length, imgs: document.querySelectorAll('.ts-corp img').length,
        voice: (body.match(/\b(we|our|us)\b/gi) || []).length, scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        title: document.title, desc: (document.querySelector('meta[name="description"]') || {}).content || '', canon: (document.querySelector('link[rel="canonical"]') || {}).href || '',
        robots: (document.querySelector('meta[name="robots"]') || {}).content || '', band: document.querySelectorAll('.ts-corp-band').length,
        faqFirstOpen: [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].map(e => e.classList.contains('uagb-faq-item-active')).join() };
    });
    check(`${tag}: one h1 with the page title`, r.h1.length === 1 && r.h1[0] === 'Corporate & Group Massage in Portland', r.h1.join('|'));
    check(`${tag}: the section headings, in order, no skipped heading level`, JSON.stringify(r.h2) === JSON.stringify(H2) && !r.skipped, r.h2.length + ' h2, skipped=' + r.skipped);
    check(`${tag}: the 7 FAQ questions, once each, in order`, JSON.stringify(r.faqQ) === JSON.stringify(FAQ), r.faqQ.length);
    check(`${tag}: no links inside FAQ answers`, r.faqLinks === 0, r.faqLinks);
    check(`${tag}: the first FAQ item is open, the others closed`, r.faqFirstOpen === 'true,false,false,false,false,false,false', r.faqFirstOpen);
    check(`${tag}: no side-scroll`, r.scroll <= 0, r.scroll);
    check(`${tag}: no picture on the page`, r.imgs === 0, r.imgs);
    check(`${tag}: no we/our/us in the visible text`, r.voice === 0, r.voice);
    check(`${tag}: no announcement strip on the page itself`, r.band === 0, r.band);
    check(`${tag}: both quote buttons carry one identical mailto link`, r.cta.length === 2 && r.cta[0] === r.cta[1] && r.cta[0].startsWith('mailto:butsabad@gmail.com?subject='), r.cta.length + ' buttons');
    check(`${tag}: the visible email address is a mailto link`, r.emailPlain >= 1, r.emailPlain);
    check(`${tag}: the in-page anchors (booking options, chair, table) exist`, r.anchors.every(Boolean), r.anchors.join());
    if (width === 1280 && theme === 'light') {
      check('page: title tag', r.title === 'Corporate Chair Massage in Portland, OR | Thai Solitude', r.title);
      check('page: meta description', r.desc === 'On-site chair massage for employee appreciation days and workplace events in Portland, with table massage at suitable host venues. Request a quote.', r.desc);
      check('page: canonical link points at this page', r.canon.endsWith(SLUG), r.canon);
      if (/noindex/i.test(r.robots)) info('page: robots meta says noindex (expected on a staging copy; must be gone on live)', r.robots); else check('page: indexable (no noindex)', true, r.robots || 'no robots meta');
      mailtoHref = r.cta[0] || '';
      const u = new URL(mailtoHref.replace(/&amp;/g, '&'));
      const body = u.searchParams.get('body') || '';
      check('page: the mailto subject and recipient decode correctly', u.pathname === 'butsabad@gmail.com' && u.searchParams.get('subject') === 'Corporate massage quote request', u.pathname + ' / ' + u.searchParams.get('subject'));
      check('page: the mailto body prompts for organization, date, place, group size, hours and massage type', PROMPTS.every(p => body.includes(p)), body.split('\n').length + ' lines');
      check('page: the mailto body asks for no health information', !/health|medical|injur|pain|condition/i.test(body));
      const bad = [];
      for (const href of r.internal) {
        const url = href.startsWith('http') ? href : base + href;
        if (!url.startsWith(base)) { info('page: external link not fetched', href); continue; }
        const rr = await ctx.request.get(url);
        if (rr.status() !== 200) bad.push(href + ' ' + rr.status());
      }
      check(`page: every internal link answers 200`, bad.length === 0, bad.join(', ') || r.internal.length + ' links');
    }
    // the FAQ: click opens a question and closes the first (one open at a time); the keyboard does the same
    const q = page.locator('.uagb-faq-questions-button', { hasText: FAQ[2] }).first();
    await q.scrollIntoViewIfNeeded(); await q.click(); await page.waitForTimeout(500);
    let openItems = await page.evaluate(() => [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].filter(e => e.classList.contains('uagb-faq-item-active')).map(e => e.textContent.replace(/\s+/g, ' ').trim().slice(0, 60)));
    check(`${tag}: clicking a question opens it, one open at a time`, openItems.length === 1 && /How many employees/.test(openItems[0]), openItems.join(' | '));
    // the keyboard target is the item's wrapper (Spectra gives it role="tab" and tabindex 0), exactly as on the Therapeutic and Thai pages
    const q2 = page.locator('.uagb-faq-child__outer-wrap', { has: page.locator('.uagb-faq-questions-button', { hasText: FAQ[4] }) }).first();
    await q2.scrollIntoViewIfNeeded(); await q2.focus(); await page.keyboard.press('Enter'); await page.waitForTimeout(500);
    openItems = await page.evaluate(() => [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].filter(e => e.classList.contains('uagb-faq-item-active')).map(e => e.textContent.replace(/\s+/g, ' ').trim().slice(0, 40)));
    check(`${tag}: Enter opens a question from the keyboard`, openItems.length === 1 && /What does it cost/.test(openItems[0]), openItems.join(' | '));
    await page.keyboard.press('Space'); await page.waitForTimeout(500);
    openItems = await page.evaluate(() => [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].filter(e => e.classList.contains('uagb-faq-item-active')).length);
    check(`${tag}: Space closes it again`, openItems === 0, openItems);
    await page.screenshot({ path: path.join(out, `${label}-page-${theme}-${width}.png`), fullPage: true });
    check(`${tag}: no script errors`, errors.length === 0, errors.slice(0, 2).join(' || '));
    await ctx.close();
  }
}

// ---- THE STRIP -----------------------------------------------------------------------------------------------------------------------------------------
const STRIP_VIEWS = quick ? [['light', 1280], ['light', 390]] : ['light', 'dark'].flatMap(t => [1280, 390].map(w => [t, w]));
for (const [theme, width] of STRIP_VIEWS) {
  {
    const ctx = await newCtx(width, theme);
    for (const p of [...BAND_PAGES, ...NO_BAND].filter(p => !quick || width === 1280 || ['/about/', '/services/', '/', '/work-with-me/'].includes(p))) {
      const errors = [];
      const { page, resp } = await open(ctx, p, errors);
      if (challenged) { console.log('INVALID: SiteGround answered the bot challenge (HTTP 202); nothing was tested.'); await browser.close(); process.exit(2); }
      const tag = `strip ${theme}-${width} ${p}`;
      if (resp.status() === 404 && ['/personal-automation/', '/privacy/', '/terms/'].includes(p)) { info(tag + ': the page does not exist on this site, skipped'); await page.close(); continue; }
      check(`${tag}: HTTP 200`, resp.status() === 200, resp.status());
      const b = await page.evaluate(() => {
        const bands = [...document.querySelectorAll('.ts-corp-band')];
        const hdr = document.querySelector('#masthead');
        const el = bands[0];
        const r = el && el.getBoundingClientRect(); const hr = hdr && hdr.getBoundingClientRect();
        const link = el && el.querySelector('a');
        return { n: bands.length, h: r ? Math.round(r.height) : 0, gap: r && hr ? Math.round(r.top - hr.bottom) : null, href: link ? link.getAttribute('href') : '', text: el ? el.innerText.replace(/\s+/g, ' ').trim() : '',
          scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth };
      });
      if (BAND_PAGES.includes(p)) {
        check(`${tag}: exactly one strip, directly under the header`, b.n === 1 && b.gap !== null && Math.abs(b.gap) <= 2, `n=${b.n} gap=${b.gap}`);
        check(`${tag}: the strip is compact (${width === 1280 ? '<= 90' : '<= 150'} px tall)`, b.h > 0 && b.h <= (width === 1280 ? 90 : 150), b.h);
        check(`${tag}: the strip links to the corporate page`, b.href.replace(base, '').endsWith(SLUG), b.href);
        check(`${tag}: the strip's words are the approved ones`, b.text.includes('Bring massage to your team.') && b.text.includes('On-site chair massage for employee appreciation days and workplace events. Table massage may be available at suitable host venues.') && b.text.includes('Explore Corporate Massage'), b.text.slice(0, 80));
        check(`${tag}: no side-scroll`, b.scroll <= 0, b.scroll);
        if (width === 1280 && theme === 'light' && p === '/about/') {
          shot: {
            await page.screenshot({ path: path.join(out, `${label}-strip-about-${theme}-${width}.png`), clip: { x: 0, y: 0, width, height: 260 } });
            const link = page.locator('.ts-corp-band a').first();
            await link.focus();
            const f = await link.evaluate(a => { const s = getComputedStyle(a); return { style: s.outlineStyle, w: parseFloat(s.outlineWidth) }; });
            check(`${tag}: keyboard focus on the strip's link is visible`, f.style !== 'none' && f.w >= 2, JSON.stringify(f));
            await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle' }), link.click()]);
            check(`${tag}: clicking the strip's link opens the corporate page`, page.url().endsWith(SLUG), page.url());
          }
        }
      } else {
        check(`${tag}: no strip`, b.n === 0, b.n);
      }
      await page.close();
    }
    await ctx.close();
  }
}

// ---- SERVICES AND MENU ---------------------------------------------------------------------------------------------------------------------------------
for (const width of quick ? [1280, 390] : [1280, 820, 390]) {
  const ctx = await newCtx(width, 'light');
  const errors = [];
  const { page } = await open(ctx, '/services/', errors);
  const tag = `services ${width}`;
  const s = await page.evaluate(() => {
    const links = [...document.querySelectorAll('.entry-content h3.uagb-heading-text a')].map(a => a.getAttribute('href'));
    const h3 = [...document.querySelectorAll('.entry-content h3.uagb-heading-text')].map(h => h.textContent.replace(/\s+/g, ' ').trim());
    const tile = document.querySelector('.ts-corp-tile'); const row = tile && tile.parentElement;
    const rect = e => { const r = e.getBoundingClientRect(); return [r.left, r.top, r.right, r.bottom]; };
    const text = row && row.querySelector('.wp-block-uagb-advanced-heading:last-child');
    const t = tile && rect(tile), x = text && rect(text);
    const overlap = !!(t && x && t[0] < x[2] && x[0] < t[2] && t[1] < x[3] && x[1] < t[3]);
    const rows = [...document.querySelectorAll('.entry-content .uagb-layout-grid')].map(r => (r.querySelector('h3') || {}).textContent || '').map(s => s.trim());
    return { links, h3, tile: t && [Math.round(t[2] - t[0]), Math.round(t[3] - t[1])], overlap, rows, descLink: row ? [...row.querySelectorAll('.uagb-desc-text a')].map(a => a.getAttribute('href')) : [], scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth };
  });
  const rel = l => l.replace(base, '').replace(/^https?:\/\/[^/]+/, '');
  check(`${tag}: four service links in order, the three existing ones unchanged and the new one last`, JSON.stringify(s.links.map(rel)) === JSON.stringify(SERVICE_LINKS), s.links.map(rel).join(' '));
  check(`${tag}: the new row sits before "Your Session"`, s.rows.indexOf('Corporate & Group Massage') >= 0 && s.rows.indexOf('Corporate & Group Massage') + 1 === s.rows.indexOf('Your Session'), s.rows.join(' | '));
  check(`${tag}: the row has a tile, a description link to the corporate page, and nothing overlaps`, !!s.tile && s.descLink.length === 1 && rel(s.descLink[0]) === SLUG && !s.overlap, `tile ${s.tile}, overlap=${s.overlap}`);
  check(`${tag}: no side-scroll`, s.scroll <= 0, s.scroll);
  if (width === 1280) {
    await page.locator('h3 a', { hasText: 'Corporate & Group Massage' }).first().click();
    await page.waitForLoadState('networkidle');
    check(`${tag}: the Services title link opens the corporate page`, page.url().endsWith(SLUG), page.url());
  }
  await page.screenshot({ path: path.join(out, `${label}-services-${width}.png`), fullPage: true });
  await ctx.close();
}
{
  // menu: header and footer at 1280; the phone menu at 390
  let ctx = await newCtx(1280, 'light');
  let errors = [];
  let { page } = await open(ctx, '/about/', errors);
  const m = await page.evaluate(() => {
    const vis = e => e.offsetParent !== null;
    const hdr = [...document.querySelectorAll('#ast-hf-menu-1 > .menu-item > .menu-link')].filter(vis).map(a => a.textContent.trim());
    const ft = [...document.querySelectorAll('#astra-footer-menu > .menu-item > .menu-link')].filter(vis).map(a => a.textContent.trim());
    const c = [...document.querySelectorAll('.menu-item .menu-link')].filter(a => a.textContent.trim() === 'Corporate Massage').map(a => a.getAttribute('href'));
    return { hdr, ft, c };
  });
  check('menu 1280: the header shows the six items in order', JSON.stringify(m.hdr) === JSON.stringify(MENU), m.hdr.join(' | '));
  check('menu 1280: the footer shows the six items in order', JSON.stringify(m.ft) === JSON.stringify(MENU), m.ft.join(' | '));
  check('menu 1280: every "Corporate Massage" menu link points at the corporate page', m.c.length >= 3 && m.c.every(h => h.replace(base, '').endsWith(SLUG)), m.c.join(' '));
  await page.screenshot({ path: path.join(out, `${label}-menu-1280.png`), clip: { x: 0, y: 0, width: 1280, height: 140 } });
  await ctx.close();
  ctx = await newCtx(390, 'light'); errors = [];
  ({ page } = await open(ctx, '/about/', errors));
  await page.locator('.menu-toggle, .ast-mobile-menu-trigger-minimal').first().click();
  await page.waitForTimeout(500);
  const pm = await page.evaluate(() => [...document.querySelectorAll('#ast-hf-mobile-menu .menu-link, .ast-mobile-header-content .menu-link')].filter(a => a.offsetParent !== null).map(a => a.textContent.trim()));
  check('menu 390: the phone menu lists the six items in order', JSON.stringify(pm) === JSON.stringify(MENU), pm.join(' | '));
  await page.screenshot({ path: path.join(out, `${label}-menu-390.png`) });
  await ctx.close();
}
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ./install-corporate-massage.php, ./corporate-massage.css — what this checks; ../deep-tissue/check-deep-tissue-evidence.mjs — the checker this follows

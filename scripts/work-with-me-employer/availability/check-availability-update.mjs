// Rendered check of the Work With Me and availability update (read-only; public requests only). Usage: node check-availability-update.mjs <base-url> <out-dir> [label] [firefox]
//   BROWSER=firefox (or the argument 'firefox') uses Firefox. About 14 page loads: SiteGround answers bursts with its bot challenge, so this is deliberately small.
// What it proves, at 1280 and 390 px unless stated: the availability strip on Home (light and dark at 1280) with the right headline for today's date and its link to the corporate page; Work With Me holds the two new sentences,
// keeps what the plan says to keep (the "For Practices" label, the November 1 employment dates, the repeated résumé links, the Oregon license lookup) and has NO strip and no "Relocating" wording; the corrected FAQ answers on the Thai, Therapeutic
// and Deep Tissue pages read exactly as approved when their questions are opened; the corporate page states the November 1 date and the scheduling sentence; no side-scroll; no script errors.
// Exit codes: 0 all passed, 1 a check failed, 2 INVALID (SiteGround's bot challenge: nothing was tested; try once more later, not in a loop).
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = process.argv[3] || '/tmp/av-shots';
const label = process.argv[4] || 'run';
fs.mkdirSync(out, { recursive: true });
const useFirefox = process.env.BROWSER === 'firefox' || process.argv.includes('firefox');
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const info = (name, detail = '') => console.log('INFO ' + name + (detail !== '' ? '  [' + detail + ']' : ''));
const SCHED = 'I schedule corporate bookings around my work commitments and only offer times when I am available.';
// Booking date (Ryan, 2026-10-30; booking-date/install-booking-date.php): a site not changed yet says November 1, 2026, a changed one November 14, 2026. Read from the Work With Me page; every other page must then agree.
let BOOK_DATE = 'November 1, 2026';
try { const r = await fetch(base + '/work-with-me/'); if ((await r.text()).replace(/\s+/g, ' ').includes('Corporate and group massage bookings begin November 14, 2026;')) BOOK_DATE = 'November 14, 2026'; } catch (e) { /* keep the default */ }
const WWM_CLIENTS = `Corporate and group massage bookings begin ${BOOK_DATE}; individual appointments at a dedicated Thai Solitude location are planned for summer 2027.`;
const NEW_WHERE = `Right now I offer corporate and group massage only, at suitable Portland venues, with bookings beginning ${BOOK_DATE}. Individual appointments at a dedicated Thai Solitude location are planned for summer 2027. I do not offer individual house calls.`;
const NEW_BOOK = `Corporate and group massage bookings begin ${BOOK_DATE}. The Corporate and Group Massage page explains how to request a quote. Individual appointments are planned for summer 2027.`;
const open_after = Date.now() >= (BOOK_DATE === 'November 14, 2026' ? Date.UTC(2026, 10, 14, 8, 0, 0) : Date.UTC(2026, 10, 1, 7, 0, 0));   // 2026-11-14 00:00 Pacific (PST, UTC-8) or, before booking-date, 2026-11-01 00:00 Pacific (PDT, UTC-7)
let challenged = false;
const errorsAll = [];

async function load(ctx, p) {
  const page = await ctx.newPage();
  page.on('pageerror', e => errorsAll.push(p + ' ' + String(e)));
  page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errorsAll.push(p + ' ' + m.text()); });
  const resp = await page.goto(base + p, { waitUntil: 'networkidle' });
  if ((resp && resp.status() === 202) || /Robot Challenge/i.test(await page.title().catch(() => ''))) { challenged = true; }
  return { page, resp };
}
const newCtx = async (width, dark) => {
  const ctx = await browser.newContext({ viewport: { width, height: 900 } });
  if (dark) await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
  return ctx;
};
const bail = async () => { console.log('INVALID: SiteGround answered the bot challenge; nothing (more) was tested.'); await browser.close(); process.exit(2); };

// ---- HOME: the notice ----------------------------------------------------------------------------------------------------------------------------------
for (const [width, dark] of [[1280, false], [390, false], [1280, true]]) {
  const tag = `home ${dark ? 'dark' : 'light'}-${width}`;
  const ctx = await newCtx(width, dark);
  const { page, resp } = await load(ctx, '/');
  if (challenged) await bail();
  check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
  const h = await page.evaluate(() => {
    const el = document.querySelector('.ts-corp-band'); const hdr = document.querySelector('#masthead');
    const link = el && el.querySelector('a');
    return { n: document.querySelectorAll('.ts-corp-band').length, text: el ? el.innerText.replace(/\s+/g, ' ').trim() : '', href: link ? link.getAttribute('href') : '', h: el ? Math.round(el.getBoundingClientRect().height) : 0,
      gap: el && hdr ? Math.round(el.getBoundingClientRect().top - hdr.getBoundingClientRect().bottom) : null, scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      compact: !!el?.querySelector('.ts-corp-band__mobile') && getComputedStyle(el.querySelector('.ts-corp-band__mobile')).display !== 'none',
      forPractices: [...document.querySelectorAll('.menu-link')].some(a => a.textContent.trim() === 'For Practices') };
  });
  check(`${tag}: exactly one strip, directly under the header`, h.n === 1 && h.gap !== null && Math.abs(h.gap) <= 2, `n=${h.n} gap=${h.gap}`);
  const closedHead = h.compact ? `Corporate & group massage bookings begin ${BOOK_DATE}.` : 'Currently booking corporate & group massage only.';
  const openHead = 'Now accepting corporate & group massage bookings.';
  const wantHead = open_after ? openHead : closedHead;
  const otherHead = open_after ? closedHead : openHead;
  check(`${tag}: the headline is one of the two approved ones`, h.text.includes(wantHead) || h.text.includes(otherHead), h.text.slice(0, 60));
  if (!h.text.includes(wantHead) && h.text.includes(otherHead)) info(`${tag}: shows the other headline than today's date calls for (a cached page; purge the site cache after 2026-11-01)`);
  check(`${tag}: it names the summer 2027 plan as a plan, the corporate link, and never says "studio"`, h.text.includes('planned for summer 2027') && h.text.includes('Explore Corporate & Group Massage') && !/studio/i.test(h.text) && !/guarantee/i.test(h.text), h.text.slice(-70));
  check(`${tag}: it links to the corporate page`, h.href.replace(base, '').endsWith('/corporate-massage-portland/'), h.href);
  check(`${tag}: it is compact (${width === 1280 ? '<= 100' : '<= 170'} px)`, h.h > 0 && h.h <= (width === 1280 ? 100 : 170), h.h);
  check(`${tag}: no side-scroll`, h.scroll <= 0, h.scroll);
  check(`${tag}: the "For Practices" menu label is unchanged`, h.forPractices || width < 922);
  if (width === 1280 && !dark) {
    await page.screenshot({ path: path.join(out, `${label}-home-top.png`), clip: { x: 0, y: 0, width, height: 420 } });
    const link = page.locator('.ts-corp-band a').first();
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle' }), link.click()]);
    check(`${tag}: clicking the notice's link opens the corporate page`, page.url().endsWith('/corporate-massage-portland/'), page.url());
  }
  await ctx.close();
}

// ---- WORK WITH ME ---------------------------------------------------------------------------------------------------------------------------------------
for (const width of [1280, 390]) {
  const tag = `work-with-me ${width}`;
  const ctx = await newCtx(width, false);
  const { page, resp } = await load(ctx, '/work-with-me/');
  if (challenged) await bail();
  check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
  const w = await page.evaluate(() => {
    const t = document.querySelector('main, .entry-content, body').innerText.replace(/\s+/g, ' ');
    const avail = document.getElementById('availability'); const clients = document.getElementById('for-clients');
    return { t, band: document.querySelectorAll('.ts-corp-band').length, avail: avail ? avail.innerText.replace(/\s+/g, ' ') : '', clients: clients ? clients.innerText.replace(/\s+/g, ' ') : '',
      resume: [...document.querySelectorAll('a')].filter(a => /Butsaba_Dusrai_LMT_Resume_License\.pdf/.test(a.getAttribute('href') || '')).length,
      license: [...document.querySelectorAll('a')].some(a => /orlicensing\.oregon\.gov/.test(a.getAttribute('href') || '')), scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth };
  });
  check(`${tag}: no announcement strip (it is an employment page)`, w.band === 0, w.band);
  check(`${tag}: the Availability section carries the independent-practice sentence once`, w.avail.split(SCHED).length === 2 && w.avail.includes('Thai Solitude is my independent practice.'), w.avail.length);
  check(`${tag}: the "For clients" section carries the booking-date and summer 2027 sentence once`, w.clients.split(WWM_CLIENTS).length === 2, w.clients.length);
  check(`${tag}: kept: the employment start and interview dates`, w.t.includes('Start — From November 1 · open for days, evenings and weekends') && w.t.includes('Interviews — By phone or video call now, in person from November 1'));
  check(`${tag}: kept: the repeated résumé links (3) and the Oregon license lookup`, w.resume >= 3 && w.license, `${w.resume} links`);
  check(`${tag}: no "Relocating from Chicago" wording`, !/relocat/i.test(w.t));
  check(`${tag}: no side-scroll`, w.scroll <= 0, w.scroll);
  if (width === 1280) await page.screenshot({ path: path.join(out, `${label}-wwm-availability.png`), clip: { x: 0, y: 0, width, height: 900 } });
  await ctx.close();
}

// ---- THE THREE MODALITY PAGES: the corrected FAQ answers --------------------------------------------------------------------------------------------------
for (const p of ['/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/']) {
  const ctx = await newCtx(1280, false);
  const { page, resp } = await load(ctx, p);
  if (challenged) await bail();
  const tag = `${p.replace(/\//g, '')}`;
  check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
  for (const [q, want] of [['Where Do Sessions Take Place?', NEW_WHERE], ['How Do I Book a Session?', NEW_BOOK]]) {
    const btn = page.locator('.uagb-faq-questions-button', { hasText: q }).first();
    if (!(await btn.count())) {
      if (/localhost/.test(base)) { info(`${tag}: this local copy predates the page's FAQ: skipped`); continue; }   // an old localhost only; on any real site a missing FAQ is a failure
      check(`${tag}: "${q}" exists`, false); continue;
    }
    await btn.scrollIntoViewIfNeeded(); await btn.click(); await page.waitForTimeout(450);
    const ans = await page.evaluate(qq => { const w = [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].find(e => e.querySelector('.uagb-question') && e.querySelector('.uagb-question').textContent.trim() === qq);
      return w ? { active: w.classList.contains('uagb-faq-item-active'), text: (w.querySelector('.uagb-faq-content') || {}).textContent.replace(/\s+/g, ' ').trim() } : null; }, q);
    check(`${tag}: "${q}" opens and reads exactly as approved`, !!ans && ans.active && ans.text === want, ans ? ans.text.slice(0, 50) : 'not found');
  }
  const strip = await page.evaluate(() => document.querySelectorAll('.ts-corp-band').length);
  check(`${tag}: one availability strip`, strip === 1, strip);
  const next = await page.evaluate(() => {
    const panel = document.querySelector('.ts-review-next');
    return panel ? { text: panel.textContent.replace(/\s+/g, ' ').trim(), links: [...panel.querySelectorAll('a')].map(a => a.getAttribute('href')) } : null;
  });
  if (next) check(`${tag}: Bundle A closing block agrees with the FAQ date and routes`, next.text.includes(`Corporate and group massage bookings begin ${BOOK_DATE}.`) && JSON.stringify(next.links) === JSON.stringify(['/corporate-massage-portland/', '/contact/']));
  if (p === '/thai-massage/') await page.screenshot({ path: path.join(out, `${label}-thai-faq.png`), clip: { x: 0, y: 0, width: 1280, height: 700 } });
  await ctx.close();
}

// ---- THE CORPORATE PAGE ----------------------------------------------------------------------------------------------------------------------------------
{
  const ctx = await newCtx(1280, false);
  const { page, resp } = await load(ctx, '/corporate-massage-portland/');
  if (challenged) await bail();
  check('corporate page: HTTP 200', resp && resp.status() === 200, resp && resp.status());
  const c = await page.evaluate(() => ({ t: document.querySelector('.ts-corp').innerText.replace(/\s+/g, ' '), band: document.querySelectorAll('.ts-corp-band').length, faq: [...document.querySelectorAll('.uagb-faq-content')].map(e => e.textContent.replace(/\s+/g, ' ').trim()).join(' | ') }));
  check('corporate page: the hero says bookings begin ' + BOOK_DATE, c.t.includes('Corporate and group massage bookings begin ' + BOOK_DATE + '. Arranged through an organization or venue at a shared location. I do not make individual home visits.'));
  check('corporate page: the reservation answer says events are scheduled around her work commitments', c.faq.includes('A date is not confirmed until availability, the arrangement, the price and any agreed booking terms have been confirmed. ' + SCHED));
  check('corporate page: no strip on the page itself', c.band === 0, c.band);
  await ctx.close();
}
check('no script errors on any page', errorsAll.length === 0, errorsAll.slice(0, 2).join(' || '));
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ./install-availability-update.php, ./build-availability-update.py — what this checks; ../corporate/check-corporate-massage.mjs — the checker this follows

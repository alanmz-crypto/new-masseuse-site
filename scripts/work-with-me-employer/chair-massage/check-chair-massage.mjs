// Browser check of the Chair Massage change (read-only; public requests only): the new page in the light and the dark version at 1280 and 390 px (title, H1, strip, jump links and their
// targets, the five pictures loaded, the FAQ by click and by keyboard, the research panels, 32 sources, footer licence, no console errors = no CSP violation), the Services row before the
// Corporate & Group row with its picture loaded, and full-page screenshots of both pages for review. About 8 page loads.
// Usage: node check-chair-massage.mjs <base-url> [outdir] [firefox]      outdir defaults to $RUNNER_TEMP/ops-out/chair-shots (kept as the run's artifact in GitHub Actions) or /tmp/chair-shots.
// Exit 1 if a check fails; exit 2 = SiteGround's bot challenge answered (INVALID, nothing was tested; run once more later).
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = (process.argv[3] && process.argv[3] !== 'firefox') ? process.argv[3] : ((process.env.RUNNER_TEMP ? process.env.RUNNER_TEMP + '/ops-out' : '/tmp') + '/chair-shots');
const useFirefox = process.env.BROWSER === 'firefox' || process.argv.includes('firefox');
fs.mkdirSync(out, { recursive: true });
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
let fails = 0, passes = 0, challenged = false;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (ok) passes++; else fails++; };

async function open(ctx, p, errors) {
  const page = await ctx.newPage();
  page.on('pageerror', e => errors.push(String(e)));
  page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errors.push(m.text()); });
  const resp = await page.goto(base + p, { waitUntil: 'networkidle', timeout: 60000 });
  if (resp && resp.status() === 202 && (resp.headers()['sg-captcha'] || '').length) challenged = true;
  if (/Robot Challenge/i.test(await page.title().catch(() => ''))) challenged = true;
  return { page, resp };
}
const newCtx = async (width, theme) => {
  const ctx = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
  if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
  return ctx;
};
const settle = async (page) => {
  await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' });
  await page.evaluate(async () => { const step = innerHeight * 0.8; for (let y = 0; y < document.body.scrollHeight; y += step) { scrollTo(0, y); await new Promise(r => setTimeout(r, 80)); } scrollTo(0, 0); });
  await page.waitForTimeout(400);
};

for (const theme of ['light', 'dark']) {
  for (const width of [1280, 390]) {
    const tag = `/chair-massage/ ${theme} ${width}px`;
    const errors = [];
    const ctx = await newCtx(width, theme);
    const { page, resp } = await open(ctx, '/chair-massage/', errors);
    if (challenged) break;
    check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
    if (!resp || resp.status() !== 200) { await ctx.close(); continue; }
    await settle(page);
    const r = await page.evaluate(() => {
      const t = (s) => (document.querySelector(s) || {}).textContent || '';
      const imgs = [...document.querySelectorAll('.entry-content img')].map(i => ({ src: i.getAttribute('src') || '', ok: i.complete && i.naturalWidth > 0, alt: i.getAttribute('alt') || '', title: i.getAttribute('title') || '' }));
      const links = [...document.querySelectorAll('nav.ts-jump a')].map(a => a.getAttribute('href'));
      const targets = links.map(h => !!document.getElementById(h.slice(1)));
      const det = [...document.querySelectorAll('.entry-content details')];
      return {
        title: document.title, h1: t('h1'), band: document.querySelectorAll('.ts-corp-band').length, nav: document.querySelectorAll('nav.ts-jump').length, links, targets,
        imgs, faq: document.querySelectorAll('.uagb-faq-item').length, firstOpen: !!document.querySelector('.uagb-faq-item.uagb-faq-item-active'),
        details: det.length, open: det.map(d => d.open), summaries: det.map(d => d.querySelector('summary').textContent.trim()),
        cites: document.querySelectorAll('cite.ts-source').length, research: t('#ts-research') || [...document.querySelectorAll('h2')].map(h => h.textContent).filter(x => /Research Says About Chair/.test(x)).join(''),
        licence: /LMT-29905/.test(document.body.textContent), h2s: [...document.querySelectorAll('.entry-content h2')].map(h => h.textContent.trim()),
        dark: document.documentElement.getAttribute('data-theme') || document.body.className.match(/ts-dark|dark/)?.[0] || '',
        sideScroll: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1,
      };
    });
    check(`${tag}: title tag`, r.title.startsWith('Chair Massage | Butsaba Dusrai, Oregon LMT'), r.title);
    check(`${tag}: H1`, r.h1.trim() === 'Chair Massage in Portland', r.h1.trim());
    check(`${tag}: the strip under the header, once`, r.band === 1, r.band);
    check(`${tag}: jump-links row with four links, every target on the page`, r.nav === 1 && r.links.length === 4 && r.targets.every(Boolean), JSON.stringify(r.links));
    check(`${tag}: five pictures loaded (hero + four tints), tooltip = alt text`, r.imgs.length === 5 && r.imgs.every(i => i.ok && i.alt && i.alt === i.title) && r.imgs.filter(i => /chair-massage-(green|teal|yellow|red)\.webp/.test(i.src)).length === 4 && /corporate-group-massage\.webp/.test(r.imgs[0].src), JSON.stringify(r.imgs.map(i => [i.src.split('/').pop(), i.ok])));
    check(`${tag}: five section headings in order`, JSON.stringify(r.h2s) === JSON.stringify(['What Is Chair Massage?', 'Common Reasons Clients Choose Chair Massage', 'Who Can Benefit From Chair Massage?', 'What To Expect During Your Appointment', 'What the Research Says About Chair Massage']), JSON.stringify(r.h2s));
    check(`${tag}: eleven FAQ items, the first open`, r.faq === 11 && r.firstOpen, r.faq);
    check(`${tag}: five research panels, first open, in order`, r.details === 5 && JSON.stringify(r.open) === '[true,false,false,false,false]' && JSON.stringify(r.summaries) === JSON.stringify(['Reviews, and What They Say About Limits', 'Stress, Anxiety and Blood Pressure', 'Desk Work, Muscle Discomfort and Movement', 'Nurses, Hospital Staff and Carers', 'Sources']), JSON.stringify(r.summaries));
    check(`${tag}: 32 sources`, r.cites === 32, r.cites);
    check(`${tag}: licence number on the page`, r.licence);
    check(`${tag}: no horizontal scroll`, !r.sideScroll);
    // FAQ by click (second item) and by keyboard (third item)
    const items = page.locator('.uagb-faq-item');
    await items.nth(1).locator('.uagb-faq-questions-button').click();
    await page.waitForTimeout(500);
    check(`${tag}: FAQ opens by click (second item active, first closed)`, await items.nth(1).evaluate(e => e.classList.contains('uagb-faq-item-active')) && !(await items.nth(0).evaluate(e => e.classList.contains('uagb-faq-item-active'))));
    await items.nth(2).focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(500);
    check(`${tag}: FAQ opens by keyboard (Enter on the third item)`, await items.nth(2).evaluate(e => e.classList.contains('uagb-faq-item-active')));
    // research panel toggles
    const det2 = page.locator('.entry-content details').nth(1);
    await det2.locator('summary').click();
    await page.waitForTimeout(400);
    check(`${tag}: second research panel opens on click`, await det2.evaluate(d => d.open && d.querySelector('p').getClientRects().length > 0));
    // jump link scrolls
    await page.locator('nav.ts-jump a').nth(3).click();
    await page.waitForTimeout(400);
    check(`${tag}: jump link scrolls to its target`, await page.evaluate(() => { const el = document.getElementById('ts-what-to-expect'); const b = el.getBoundingClientRect(); return b.top >= -5 && b.top < innerHeight; }));
    await page.evaluate(() => scrollTo(0, 0));
    await page.waitForTimeout(300);
    check(`${tag}: no console errors (no CSP violations)`, errors.length === 0, errors.slice(0, 2).join(' | '));
    await page.screenshot({ path: `${out}/chair-${theme}-${width}.png`, fullPage: true });
    await ctx.close();
  }
  if (challenged) break;
}
if (!challenged) {
  const errors = [];
  const ctx = await newCtx(1280, 'light');
  const { page, resp } = await open(ctx, '/services/', errors);
  if (!challenged) {
    check('/services/: HTTP 200', resp && resp.status() === 200, resp && resp.status());
    await settle(page);
    const s = await page.evaluate(() => {
      const h3 = [...document.querySelectorAll('.entry-content h3')].map(h => h.textContent.trim());
      const row = [...document.querySelectorAll('.entry-content h3 a')].find(a => a.textContent.trim() === 'Chair Massage');
      const cont = row && row.closest('.wp-block-uagb-container');
      const img = cont && cont.querySelector('img');
      return { h3, href: row && row.getAttribute('href'), img: img ? { src: img.getAttribute('src'), ok: img.complete && img.naturalWidth > 0, w: img.getBoundingClientRect().width, h: img.getBoundingClientRect().height } : null, top: cont ? cont.getBoundingClientRect().top + scrollY : 0 };
    });
    const i = s.h3.indexOf('Chair Massage'), j = s.h3.indexOf('Corporate & Group Massage');
    check('/services/: the Chair Massage row sits right before the Corporate & Group row', i >= 0 && j === i + 1 && s.href === '/chair-massage/', JSON.stringify(s.h3));
    check('/services/: the row picture (green tint) loads, square like the other service pictures', !!s.img && s.img.ok && /chair-massage-green\.webp/.test(s.img.src) && Math.abs(s.img.w - s.img.h) < 2, JSON.stringify(s.img));
    check('/services/: no console errors', errors.length === 0, errors.slice(0, 2).join(' | '));
    await page.screenshot({ path: `${out}/services-light-1280.png`, fullPage: true });
    // the row itself, for review
    await page.evaluate(y => scrollTo(0, Math.max(0, y - 40)), s.top);
    await page.waitForTimeout(300);
    await page.screenshot({ path: `${out}/services-row-light-1280.png` });
  }
  await ctx.close();
}
await browser.close();
if (challenged) { console.log('INVALID: SiteGround answered the bot challenge; nothing was tested. Run once more later.'); process.exit(2); }
console.log(`\n${passes} passed, ${fails} failed; screenshots in ${out}`);
process.exit(fails ? 1 : 0);

// ref: ../corporate/check-corporate-massage.mjs — the checker this follows (browser launch, challenge detection, dark mode via ts-theme); ../tools/capture-pages.mjs — the screenshot settling steps

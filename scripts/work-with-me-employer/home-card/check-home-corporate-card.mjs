// Home page: the Corporate & Group Massage card at the top of "The Massage I Offer" (read-only; public requests only). For each width and theme it checks that the new card is the FIRST of the four cards,
// carries the reviewed words and link, shows its picture, and is made the same as its neighbour (the Therapeutic card): the same title, description and button fonts, sizes and colours, the same card
// borders and spacing, the same picture frame (size, left edge, rounded corners), the same one-line button height, no sideways scrolling; and that the three existing cards are still there in their order.
// Usage: node check-home-corporate-card.mjs <base-url> [firefox] [quick]   quick = light theme at 1280 and 390 px only, no link click (fewer public requests, for SiteGround's bot challenge).
// Exit 1 = a check failed. Exit 2 = SiteGround's bot challenge answered (HTTP 202 / "Robot Challenge Screen"): INVALID, nothing was tested.
import { createRequire } from 'module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const useFirefox = process.env.BROWSER === 'firefox' || process.argv.includes('firefox');
const quick = process.argv.includes('quick');
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
const widths = quick ? [1280, 390] : [1280, 820, 390];
const themes = quick ? ['light'] : ['light', 'dark'];
const TITLE = 'Corporate & Group Massage';
const DESC = 'On-site chair massage for employee appreciation days and workplace events, with table massage at suitable host venues.';
const LABEL = 'Corporate';
const HREF = '/corporate-massage-portland/';
const ORDER = [TITLE, 'Therapeutic Massage', 'Deep Tissue Massage', 'Thai Massage'];
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const challenged = async (page, resp) => (resp && resp.status() === 202) || /Robot Challenge/i.test(await page.title());
const diff = (a, b, keys) => keys.filter((k) => a[k] !== b[k]).map((k) => `${k}: ${a[k]} vs ${b[k]}`).join('; ');

for (const theme of themes) {
  for (const width of widths) {
    const tag = `Home ${theme} ${width}px`;
    const ctx = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const page = await ctx.newPage();
    const resp = await page.goto(base + '/', { waitUntil: 'networkidle', timeout: 60000 });
    if (await challenged(page, resp)) { console.log('INVALID: SiteGround answered the bot challenge (HTTP 202); nothing was tested.'); await browser.close(); process.exit(2); }
    if (!resp || resp.status() !== 200) { check(`${tag}: HTTP 200`, false, resp && resp.status()); await ctx.close(); continue; }
    await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important}' });
    await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 500) { scrollTo(0, y); await new Promise((r) => setTimeout(r, 120)); } });
    await page.waitForTimeout(600);
    const m = await page.evaluate(() => {
      const wrap = document.querySelector('.uagb-block-883307ab');
      const cards = wrap ? [...wrap.children].filter((e) => e.classList.contains('wp-block-uagb-container')) : [];
      const rect = (e) => { const r = e.getBoundingClientRect(); return { x: Math.round(r.x), w: Math.round(r.width), h: Math.round(r.height) }; };
      const cs = (e, ks) => { const s = getComputedStyle(e); return Object.fromEntries(ks.map((k) => [k, s[k]])); };
      const info = cards.map((c) => {
        const t = c.querySelector('h3'), d = c.querySelector('.uagb-desc-text'), a = c.querySelector('a.uagb-buttons-repeater'), img = c.querySelector('img');
        if (!t || !d || !a || !img) return { missing: true, title: t && t.textContent };
        return {
          title: t.textContent.trim(), desc: d.textContent.trim(), label: a.textContent.trim(), href: a.getAttribute('href'),
          card: cs(c, ['borderBottomWidth', 'borderBottomStyle', 'borderBottomColor', 'paddingBottom', 'columnGap']), cardW: rect(c).w,
          t: { ...cs(t, ['fontSize', 'fontWeight', 'fontFamily', 'color', 'lineHeight']), w: rect(t).w },
          d: { ...cs(d, ['fontSize', 'fontWeight', 'color', 'lineHeight']), w: rect(d).w },
          a: { ...cs(a, ['fontSize', 'color', 'borderTopWidth', 'borderTopLeftRadius', 'paddingTop']), h: rect(a).h, x: rect(a).x, xr: rect(a).x + rect(a).w },
          img: { ...rect(img), radius: getComputedStyle(img).borderTopLeftRadius, figRadius: getComputedStyle(img.parentElement).borderTopLeftRadius, loaded: img.complete && img.naturalWidth > 0, alt: img.alt },
        };
      });
      return { info, hscroll: document.documentElement.scrollWidth > innerWidth };
    });
    const [nw, th] = m.info;
    check(`${tag}: four cards in the order Corporate, Therapeutic, Deep Tissue, Thai`, m.info.length === 4 && ORDER.every((t, i) => m.info[i] && m.info[i].title === t), m.info.map((c) => c.title).join(' | '));
    if (m.info.some((c) => c.missing) || !nw || !th) { check(`${tag}: every card has title, description, button and picture`, false); await ctx.close(); continue; }
    check(`${tag}: reviewed description, button label and link`, nw.desc === DESC && nw.label === LABEL && nw.href === HREF, `${nw.label} -> ${nw.href}`);
    check(`${tag}: the picture is loaded and has its alt text`, nw.img.loaded && nw.img.alt.startsWith('Illustration of a massage therapist giving a shoulder massage'), `${nw.img.w}x${nw.img.h}`);
    check(`${tag}: title, description and button look the same as the Therapeutic card`, !diff(nw.t, th.t, ['fontSize', 'fontWeight', 'fontFamily', 'color', 'lineHeight', 'w']) && !diff(nw.d, th.d, ['fontSize', 'fontWeight', 'color', 'lineHeight', 'w'])
      && !diff(nw.a, th.a, ['fontSize', 'color', 'borderTopWidth', 'borderTopLeftRadius', 'paddingTop', 'h']) && (nw.a.x === th.a.x || nw.a.xr === th.a.xr),   // the button hangs from the left edge of its column, or (stacked rows at tablet width) from the right edge, whatever the label length
      [diff(nw.t, th.t, ['fontSize', 'color', 'w']), diff(nw.d, th.d, ['fontSize', 'color', 'w']), diff(nw.a, th.a, ['color', 'h']), nw.a.x === th.a.x || nw.a.xr === th.a.xr ? '' : `button edges: ${nw.a.x}-${nw.a.xr} vs ${th.a.x}-${th.a.xr}`].filter(Boolean).join(' | '));
    check(`${tag}: picture frame (size, left edge, rounded corners) and card border/spacing match`, !diff(nw.img, th.img, ['x', 'w', 'h', 'radius', 'figRadius']) && !diff(nw.card, th.card, ['borderBottomWidth', 'borderBottomStyle', 'borderBottomColor', 'paddingBottom', 'columnGap']) && nw.cardW === th.cardW,
      [diff(nw.img, th.img, ['x', 'w', 'h', 'radius', 'figRadius']), diff(nw.card, th.card, ['borderBottomWidth', 'borderBottomColor', 'paddingBottom', 'columnGap'])].filter(Boolean).join(' | '));
    check(`${tag}: no sideways scrolling`, !m.hscroll);
    if (!quick && theme === 'light' && width === 1280) {
      // the link works (one extra public request) and the button can take keyboard focus
      const focused = await page.evaluate((href) => { const a = document.querySelector('.uagb-block-883307ab a.uagb-buttons-repeater'); a.focus(); return document.activeElement === a && a.getAttribute('href') === href && a.textContent.trim().length > 0; }, HREF);
      check(`${tag}: the card's button takes keyboard focus and has a visible name`, focused);
      const r2 = await page.goto(base + HREF, { waitUntil: 'domcontentloaded', timeout: 60000 });
      if (await challenged(page, r2)) { console.log('INVALID: SiteGround answered the bot challenge (HTTP 202); nothing more was tested.'); await browser.close(); process.exit(2); }
      const h1 = r2 && r2.status() === 200 ? await page.locator('h1').first().innerText() : '';
      check(`${tag}: the button's link opens the Corporate & Group Massage page`, r2 && r2.status() === 200 && /Corporate/i.test(h1) && /Group/i.test(h1), `HTTP ${r2 && r2.status()} "${h1.replace(/\s+/g, ' ').slice(0, 60)}"`);
    }
    await ctx.close();
  }
}
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ./install-home-corporate-card.php — what the checks verify; ../corporate/check-corporate-massage.mjs — the checker pattern (bot-challenge detection, exit codes)

// Read-only section-guide contract. --fixture uses the actual emitted JS and CSS.
// node check-section-guide.mjs --fixture | http://localhost:8090 | https://staging38.thaisolitude.com [inline]
import { createRequire } from 'node:module';
import { readFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium, firefox } = require('playwright');
const engine = process.env.TS_BROWSER || 'chromium';
if (!['chromium', 'firefox'].includes(engine)) throw new Error('TS_BROWSER must be chromium or firefox');
const base = (process.argv[2] || '').replace(/\/$/, '');
const fixture = base === '--fixture';
if (!fixture && !['http://localhost:8090', 'https://staging38.thaisolitude.com'].includes(base)) throw new Error('Exact localhost/staging38 or --fixture only');
const css = readFileSync(new URL('./section-guide.css', import.meta.url), 'utf8');
const php = readFileSync(new URL('./section-guide.php', import.meta.url), 'utf8');
const script = php.match(/<script id="ts-section-guide-runtime">([\s\S]*?)<\/script>/)?.[1];
if (!script) throw new Error('Cannot find static section-guide runtime script');
const progressCSS = readFileSync(new URL('../reading-progress-sitewide/reading-progress.css', import.meta.url), 'utf8');
const baselineCSS = fixture && process.env.TS_BASELINE_CSS ? readFileSync(process.env.TS_BASELINE_CSS, 'utf8') : '';
const services = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'];
const excluded = ['', 'contact', 'corporate-massage-portland', 'work-with-me'];
const trigger = '#ts-section-guide-trigger';
const panel = '#ts-section-guide-popover';
const browser = await (engine === 'firefox' ? firefox : chromium).launch(engine === 'chromium' && process.env.TS_CHROMIUM_EXECUTABLE ? { executablePath: process.env.TS_CHROMIUM_EXECUTABLE } : {});
console.log(`INFO section guide browser: ${engine} ${browser.version()}`);
let assertions = 0, failures = 0, invalid = false;
function check(label, ok, detail = '') { assertions++; if (!ok) failures++; console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${!ok && detail ? ': ' + detail : ''}`); }
const noScriptContexts = new WeakSet();
const tick = page => noScriptContexts.has(page.context()) ? page.waitForTimeout(40) : page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
async function settle(page) { await tick(page); await page.waitForTimeout(120); await tick(page); }
function fixtureHTML(slug, short = false) {
  const scoped = services.includes(slug);
  const sticky = slug === 'corporate-massage-portland' ? 'ts-corp__sticky' : slug === 'work-with-me' ? 'ts-wwm__sticky' : '';
  return `<!doctype html><html lang="en"><head><title>Section guide fixture</title><style>
  :root{--ts-panel:#fff;--ts-ink:#1c2531;--ts-accent-deep:#7a520a;--ts-rule:#d5d6cf}html[data-ts-theme="dark"]{--ts-panel:#202936;--ts-ink:#f1f0ea;--ts-accent-deep:#e2b866;background:#151c26;color:#f1f0ea}
  body{margin:0;font:1rem/1.6 system-ui}header,footer,main{padding:24px;box-sizing:border-box}main{max-width:1000px;margin:auto}header{height:${short ? '0' : '130px'}}section{min-height:${short ? '0' : '700px'}}footer{height:${short ? '80px' : '280px'}}a{color:inherit}#ast-scroll-top{position:fixed;right:16px;bottom:16px;width:44px;height:44px}
  </style><style>${baselineCSS}</style>${short ? '<style>header{display:none}main{padding:0}.entry-content :is(h1,h2,p){margin:0;font-size:16px;line-height:1.2}footer{height:40px;padding:0}</style>' : ''}<style id="wp-custom-css">${progressCSS}\n${css}</style></head><body><header><a href="#content">Skip to content</a></header>
  <main id="content" class="entry-content"><nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list"><li><a href="#what-it-is">What it is</a></li><li><a href="#common-reasons">Common reasons</a></li><li><a href="#what-to-expect">What to expect</a></li></ul></nav><h1>Massage information</h1><section><h2 id="what-it-is">What it is</h2><p>Existing introductory information.</p><a href="#what-to-expect">Read more</a></section><section><h2 id="common-reasons">Common reasons</h2><p>Existing session information.</p></section><section><h2 id="what-to-expect">What to expect</h2><p>Existing details.</p></section>${sticky ? `<div class="${sticky}"><a href="mailto:example@example.test">Contact</a></div>` : ''}</main><footer id="colophon"><a href="#what-it-is">Footer link</a><p>Oregon License LMT-29905</p></footer><button id="ast-scroll-top" aria-label="Scroll to top">↑</button>${scoped ? `<script id="ts-section-guide-runtime">${script}</script>` : ''}</body></html>`;
}
async function contextFor({ noJS = false, unsupported = false, admin = false, touch = false } = {}) {
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'no-preference', javaScriptEnabled: !noJS, hasTouch: touch });
  if (noJS) noScriptContexts.add(context);
  if (unsupported) await context.addInitScript(() => { delete HTMLElement.prototype.showPopover; delete HTMLElement.prototype.hidePopover; });
  if (admin) await context.addInitScript(() => { const observer = new MutationObserver(() => { if (document.body) { document.body.classList.add('admin-bar'); observer.disconnect(); } }); observer.observe(document, { childList: true, subtree: true }); });
  if (fixture) await context.route('**/*', route => { const url = new URL(route.request().url()); const slug = url.pathname.replace(/^\/+|\/+$/g, ''); route.fulfill({ contentType: 'text/html', body: fixtureHTML(slug, url.searchParams.has('short')) }); });
  return context;
}
async function load(context, slug, suffix = '') {
  const page = await context.newPage();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  const response = await page.goto((fixture ? 'http://section-guide.test' : base) + '/' + (slug ? slug + '/' : '') + suffix, { waitUntil: 'networkidle', timeout: 60000 });
  if (response?.status() === 202 || /Robot Challenge/i.test(await page.title())) { invalid = true; throw new Error('INVALID: hosting challenge, no further requests'); }
  check(`${slug || 'home'}: HTTP 200`, response?.status() === 200);
  if (!noScriptContexts.has(context)) {
    await page.addStyleTag({ content: 'html{scroll-behavior:auto!important}' });
    await page.evaluate(() => document.fonts.ready);
  }
  await settle(page);
  return { page, errors };
}
async function pastNav(page) {
  await settle(page);
  await page.evaluate(() => { document.activeElement?.blur(); const nav = document.querySelector('.entry-content .ts-jump'); scrollTo(0, scrollY + nav.getBoundingClientRect().bottom + 100); });
  await settle(page);
}
async function isOpen(page) { return page.locator(panel).evaluate(el => el.matches(':popover-open')); }
async function entryState(page) { return page.locator('.entry-content').evaluate(el => ({ html: el.innerHTML, text: el.innerText, width: document.documentElement.scrollWidth, rects: [...el.querySelectorAll('*')].map(item => { const r = item.getBoundingClientRect(); return [r.x + scrollX, r.y + scrollY, r.width, r.height]; }) })); }
async function fit(page, label) {
  const result = await page.evaluate(({ trigger, panel }) => {
    const a = document.querySelector(trigger).getBoundingClientRect(), b = document.querySelector(panel).getBoundingClientRect();
    const inside = r => r.left >= 0 && r.top >= 0 && r.right <= innerWidth + 1 && r.bottom <= innerHeight + 1;
    const overlap = (x, y) => x.left < y.right && x.right > y.left && x.top < y.bottom && x.bottom > y.top;
    const scrollTop = document.querySelector('#ast-scroll-top');
    const style = scrollTop && getComputedStyle(scrollTop), r = scrollTop && scrollTop.getBoundingClientRect();
    const scrollVisible = style && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0 && r.width > 0 && r.height > 0;
    return { fits: inside(a) && inside(b), min44: a.height >= 44 && a.width >= 44, separated: !overlap(a, b), avoidsTop: !scrollVisible || (!overlap(a, r) && !overlap(b, r)), a: a.toJSON(), b: b.toJSON(), scrollTop: r?.toJSON() };
  }, { trigger, panel });
  check(`${label}: controls fit viewport and remain separate`, result.fits && result.separated, JSON.stringify(result));
  check(`${label}: trigger at least 44px`, result.min44, JSON.stringify(result));
  check(`${label}: no scroll-to-top overlap`, result.avoidsTop, JSON.stringify(result));
}
const context = await contextFor();
try {
  for (const slug of services) {
    const { page, errors } = await load(context, slug);
    const original = page.locator('.entry-content .ts-jump');
    const links = await original.locator('a[href]').evaluateAll(items => items.map(a => ({ text: a.textContent, href: a.getAttribute('href') })));
    check(`${slug}: original navigation remains present`, links.length > 0);
    check(`${slug}: native auto popover`, await page.locator(panel).getAttribute('popover') === 'auto');
    check(`${slug}: UI outside page content`, await page.locator('.entry-content ' + trigger + ', .entry-content ' + panel).count() === 0);
    check(`${slug}: exact existing labels and fragments`, JSON.stringify(links) === JSON.stringify(await page.locator(panel + ' a').evaluateAll(items => items.map(a => ({ text: a.textContent, href: a.getAttribute('href') })))));
    check(`${slug}: guide hidden before scrolling`, !await page.locator(trigger).isVisible());
    for (const width of [1280, 390, 320]) {
      await page.setViewportSize({ width, height: 844 });
      for (const theme of ['light', 'dark']) {
        await page.evaluate(theme => document.documentElement.setAttribute('data-ts-theme', theme), theme);
        await pastNav(page);
        const label = `${slug} ${width} ${theme}`;
        check(`${label}: original nav above viewport`, await original.evaluate(el => el.getBoundingClientRect().bottom < 0));
        check(`${label}: guide appears after navigation leaves`, await page.locator(trigger).isVisible());
        const before = await entryState(page);
        await page.locator(trigger).click(); await settle(page);
        check(`${label}: opens`, await isOpen(page));
        await fit(page, label);
        const after = await entryState(page);
        check(`${label}: original content and geometry unchanged`, JSON.stringify(before) === JSON.stringify(after));
        await page.keyboard.press('Escape'); await settle(page);
        check(`${label}: Escape closes`, !await isOpen(page));
      }
    }
    await pastNav(page);
    await page.locator(trigger).focus(); await page.keyboard.press('Enter'); await settle(page);
    await page.keyboard.press('Tab');
    check(`${slug}: Tab reaches panel links`, await page.evaluate(panel => Boolean(document.activeElement.closest(panel)), panel));
    await page.keyboard.press('Escape'); await settle(page);
    check(`${slug}: Escape restores trigger focus`, await page.locator(trigger).evaluate(el => document.activeElement === el));
    await page.locator(trigger).click(); await settle(page);
    await page.locator(panel + ' a').last().focus(); await page.keyboard.press('Tab');
    check(`${slug}: Tab can leave panel`, !await page.evaluate(panel => Boolean(document.activeElement.closest(panel)), panel));
    if (await isOpen(page)) await page.keyboard.press('Escape');
    await pastNav(page); await page.locator(trigger).click(); await settle(page);
    await page.mouse.click(300, 40); await settle(page);
    check(`${slug}: outside click closes`, !await isOpen(page));
    const htmlBeforeJump = await original.locator('xpath=..').evaluate(el => el.innerHTML);
    await page.locator(trigger).click(); await settle(page);
    const href = await page.locator(panel + ' a').first().getAttribute('href');
    await page.locator(panel + ' a').first().click(); await settle(page);
    check(`${slug}: selection updates native fragment`, new URL(page.url()).hash === href);
    check(`${slug}: selection focuses destination heading`, await page.evaluate(href => { const target = document.getElementById(decodeURIComponent(href.slice(1))); return document.activeElement === (target.matches('h1,h2,h3,h4,h5,h6') ? target : target.querySelector('h1,h2,h3,h4,h5,h6') || target); }, href));
    check(`${slug}: selection closes panel`, !await isOpen(page));
    await page.evaluate(() => document.activeElement.blur()); await settle(page);
    check(`${slug}: temporary focus attribute cleaned up`, htmlBeforeJump === await original.locator('xpath=..').evaluate(el => el.innerHTML));
    for (const media of [{ reducedMotion: 'reduce' }, { forcedColors: 'active' }]) {
      await page.emulateMedia(media); await pastNav(page); await page.locator(trigger).click(); await settle(page);
      check(`${slug}: functional with ${JSON.stringify(media)}`, await isOpen(page));
      check(`${slug}: no guide animation`, await page.locator(trigger + ', ' + panel).evaluateAll(elements => elements.every(el => el.getAnimations({ subtree: true }).length === 0)));
      await page.keyboard.press('Escape'); await page.emulateMedia({ reducedMotion: 'no-preference', forcedColors: 'none' });
    }
    await pastNav(page); await page.locator(trigger).click(); await page.emulateMedia({ media: 'print' });
    check(`${slug}: print hides both controls`, !await page.locator(trigger).isVisible() && !await page.locator(panel).isVisible());
    await page.emulateMedia({ media: 'screen' }); if (await isOpen(page)) await page.keyboard.press('Escape');
    await pastNav(page); await page.locator(trigger).click(); await settle(page);
    await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight)); await settle(page);
    check(`${slug}: open guide retains focus/visibility near footer`, await isOpen(page) && await page.locator(trigger).isVisible());
    await page.keyboard.press('Escape'); await page.evaluate(() => document.activeElement.blur()); await settle(page);
    check(`${slug}: hides when footer is visible`, !await page.locator(trigger).isVisible());
    await page.evaluate(() => scrollTo(0, 0)); await settle(page);
    check(`${slug}: original nav returns without duplicate guide`, !await page.locator(trigger).isVisible() && await original.isVisible());
    if (slug === 'thai-massage') {
      await page.evaluate(() => document.documentElement.style.fontSize = '200%'); await page.setViewportSize({ width: 320, height: 568 }); await pastNav(page); await page.locator(trigger).click(); await settle(page);
      await fit(page, `${slug} 320×568 200% text`);
      if (process.env.TS_SCREENSHOT_DIR) { mkdirSync(process.env.TS_SCREENSHOT_DIR, { recursive: true }); await page.screenshot({ path: join(process.env.TS_SCREENSHOT_DIR, 'section-guide-thai-200-percent.png') }); }
      await page.keyboard.press('Escape'); await page.evaluate(() => document.documentElement.style.removeProperty('font-size'));
      if (process.argv.includes('inline') || process.env.TS_SCREENSHOT_DIR) {
        await page.setViewportSize({ width: 390, height: 844 }); await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light')); await pastNav(page); await page.locator(trigger).click(); await settle(page);
        if (process.env.TS_SCREENSHOT_DIR) { mkdirSync(process.env.TS_SCREENSHOT_DIR, { recursive: true }); await page.screenshot({ path: join(process.env.TS_SCREENSHOT_DIR, 'section-guide-thai-mobile.png') }); }
        if (process.argv.includes('inline')) console.log(`INLINE section-guide-thai-mobile.jpg base64:${(await page.screenshot({ type: 'jpeg', quality: 70 })).toString('base64')}`);
      }
    }
    check(`${slug}: reading-progress CSS preserved`, await page.locator('style').evaluateAll((styles, expected) => styles.some(s => s.textContent.includes(expected.trim())), progressCSS));
    check(`${slug}: no browser script errors`, errors.length === 0, errors.join('; '));
    await page.close();
  }
  for (const slug of excluded) {
    const { page, errors } = await load(context, slug);
    await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight / 2)); await settle(page);
    check(`${slug || 'home'}: no guide added`, await page.locator(trigger + ', ' + panel).count() === 0);
    check(`${slug || 'home'}: runtime not emitted outside scoped pages`, await page.locator('#ts-section-guide-runtime').count() === 0);
    const styled = await entryState(page);
    await page.locator('style').evaluateAll(styles => { for (const style of styles) style.textContent = style.textContent.replace(/\/\* ts-section-guide:start \*\/[\s\S]*?\/\* ts-section-guide:end \*\//g, ''); });
    await settle(page);
    check(`${slug || 'home'}: content and any fixed CTA geometry unaffected by guide styles`, JSON.stringify(styled) === JSON.stringify(await entryState(page)));
    check(`${slug || 'home'}: no browser errors`, errors.length === 0, errors.join('; '));
    await page.close();
  }
  const touchContext = await contextFor({ touch: true });
  const { page: touchPage } = await load(touchContext, 'thai-massage');
  await touchPage.setViewportSize({ width: 390, height: 844 }); await pastNav(touchPage);
  await touchPage.locator(trigger).tap(); await settle(touchPage);
  check('Thai touch input: tap opens guide', await isOpen(touchPage));
  await touchPage.touchscreen.tap(300, 40); await settle(touchPage);
  check('Thai touch input: outside tap dismisses guide', !await isOpen(touchPage));
  await touchContext.close();
  for (const mode of [{ name: 'unsupported popover', unsupported: true }, { name: 'no JavaScript', noJS: true }, { name: 'admin toolbar', admin: true }]) {
    const fallback = await contextFor(mode);
    const { page } = await load(fallback, 'thai-massage');
    await pastNav(page);
    check(`${mode.name}: no added guide`, await page.locator(trigger + ', ' + panel).count() === 0);
    const link = page.locator('.entry-content .ts-jump a').first(), href = await link.getAttribute('href');
    await link.click(); await settle(page);
    check(`${mode.name}: original navigation still works`, new URL(page.url()).hash === href);
    await fallback.close();
  }
  if (fixture) {
    const { page } = await load(context, 'thai-massage', '?short');
    const shortSize = await page.evaluate(() => ({ height: document.documentElement.scrollHeight, viewport: innerHeight }));
    check('short page: no scroll range', shortSize.height <= shortSize.viewport, JSON.stringify(shortSize));
    check('short page: guide remains hidden', !await page.locator(trigger).isVisible());
    await page.close();
  }
} catch (error) {
  if (!invalid) { failures++; console.error(error.stack); }
  else console.log(error.message);
} finally { await context.close(); await browser.close(); }
console.log(`Section guide (${engine}): ${assertions} browser assertions, ${failures} failures${invalid ? ', INVALID' : ''}`);
process.exitCode = invalid ? 2 : failures ? 1 : 0;

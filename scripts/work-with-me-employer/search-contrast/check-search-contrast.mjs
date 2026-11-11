// Search surfaces in the real rendered Astra template. --inspect is diagnostic:
// it reports existing colors without pretending the old low contrast is a pass.
import { createRequire } from 'node:module';
import { createHash } from 'node:crypto';
import { mkdirSync, mkdtempSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { SEARCH_ROUTES, SEARCH_WIDTHS, SEARCH_THEMES, SEARCH_LIMITATION } from './check-contract.mjs';

const base = process.argv[2], flags = process.argv.slice(3);
if (!['http://localhost:8090', 'https://staging39.thaisolitude.com'].includes(base) || flags.some(f => !['--inspect', '--firefox'].includes(f)) || new Set(flags).size !== flags.length) {
  console.log('INVALID Search contrast: exact isolated localhost:8090 or staging39 only; optional --inspect and --firefox.');
  process.exit(2);
}
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`), pw = require('playwright');
const engine = flags.includes('--firefox') ? 'firefox' : 'chromium', inspect = flags.includes('--inspect');
const widths = inspect ? [390] : SEARCH_WIDTHS, themes = SEARCH_THEMES, routes = SEARCH_ROUTES;
const expectedCases = routes.flatMap(r => widths.flatMap(w => themes.map(t => `${r.name}/${w}/${t}`)));
const sha = text => createHash('sha256').update(text).digest('hex');
const started = Date.now();
let browser, passed = 0, failed = 0, loads = 0, captures = 0, invalid = false;
const completed = [], results = [];
const parent = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out'); mkdirSync(parent, { recursive: true });
const out = mkdtempSync(join(parent, `search-contrast-${inspect ? 'inspect' : 'check'}-${engine}-`));
const check = (label, yes, detail) => {
  yes ? passed++ : failed++;
  console.log(`${yes ? 'PASS' : 'FAIL'} search-contrast ${engine}/${label} [check ${passed + failed}]${!yes && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const settle = async page => { await page.waitForTimeout(300); await page.evaluate(() => new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)))); };

// Evaluate real foreground and background layers; a gradient, filter, blending,
// opacity or missing surface is unmeasurable, never an automatic contrast pass.
async function measure(page) {
  return page.evaluate(() => {
    const main = document.querySelector('#primary') || document.querySelector('#main') || document.querySelector('main');
    if (!main) throw new Error('No original main content');
    const norm = value => value.replace(/\s+/g, ' ').trim();
    const visible = el => {
      if (el.closest('script,style,#masthead,#colophon,footer,.ts-pk,.screen-reader-text,[hidden],[aria-hidden="true"]')) return false;
      for (let node = el; node; node = node.parentElement) {
        const s = getComputedStyle(node);
        if (s.display === 'none' || s.visibility !== 'visible' || Number(s.opacity) === 0) return false;
      }
      return el.getClientRects().length > 0;
    };
    const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1; const ctx = canvas.getContext('2d');
    const rgba = value => {
      if (!CSS.supports('color', value)) return null;
      ctx.clearRect(0, 0, 1, 1); ctx.fillStyle = value; ctx.fillRect(0, 0, 1, 1);
      const c = [...ctx.getImageData(0, 0, 1, 1).data]; return [...c.slice(0, 3), c[3] / 255];
    };
    const over = (a, b) => [...a.slice(0, 3).map((v, i) => v * a[3] + b[i] * (1 - a[3])), 1];
    const luminance = c => c.slice(0, 3).map(v => v / 255).map(v => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4).reduce((s, v, i) => s + v * [.2126, .7152, .0722][i], 0);
    const appearance = (el, pseudo = null) => {
      const s = getComputedStyle(el, pseudo), ink = rgba(s.color), layers = [];
      if (pseudo && ink) ink[3] *= Number(s.opacity);
      let reason = null, surfaceOwner = null;
      for (let n = el; n; n = n.parentElement) {
        const a = getComputedStyle(n);
        if (Number(a.opacity) !== 1 || a.filter !== 'none' || a.mixBlendMode !== 'normal' || (a.backdropFilter && a.backdropFilter !== 'none')) reason = 'opacity/filter/blend';
      }
      for (let n = el; n; n = n.parentElement) {
        const a = getComputedStyle(n), color = rgba(a.backgroundColor);
        if (a.backgroundImage !== 'none') { reason = 'background image/gradient'; break; }
        if (color && color[3]) { layers.push(color); if (color[3] === 1) { surfaceOwner = { tag: n.tagName.toLowerCase(), id: n.id, class: n.className }; break; } }
      }
      if (!ink || !layers.length || layers.at(-1)[3] !== 1) reason ||= 'missing opaque surface or text color';
      let background = layers.length ? layers.pop() : [255, 255, 255, 1];
      while (layers.length) background = over(layers.pop(), background);
      const paint = ink ? over(ink, background) : null, a = paint && luminance(paint), b = luminance(background);
      const ratio = reason ? null : (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
      return { color: s.color, background, surfaceOwner, ratio, reason, fontSize: s.fontSize, fontWeight: s.fontWeight };
    };
    const roots = [main, ...document.querySelectorAll('body.search .ast-archive-description,body.search .ast-archive-entry-banner,body.search .ast-pagination,body.search nav.navigation.pagination')];
    const nodes = new Set(roots.flatMap(root => [root, ...root.querySelectorAll('*')]));
    const rows = [];
    for (const el of nodes) {
      if (!visible(el)) continue;
      const text = norm([...el.childNodes].filter(n => n.nodeType === Node.TEXT_NODE).map(n => n.textContent).join(' '));
      if (!text) continue;
      rows.push({ tag: el.tagName.toLowerCase(), class: el.className, text: text.slice(0, 180), group: el.closest('.ast-article-post') ? 'result' : el.closest('.no-results') ? 'empty' : el.closest('.ast-archive-description,.ast-archive-entry-banner') ? 'heading' : el.closest('.navigation,.pagination,.ast-pagination,.page-numbers') ? 'pagination' : 'other', ...appearance(el) });
    }
    const surfaces = [...nodes].filter(el => el.matches('.ast-article-inner,.ast-archive-description,.ast-archive-entry-banner,.no-results')).map(el => ({ tag: el.tagName.toLowerCase(), class: el.className, color: getComputedStyle(el).color, background: getComputedStyle(el).backgroundColor, image: getComputedStyle(el).backgroundImage, panel: rgba(getComputedStyle(el).getPropertyValue('--ts-panel').trim()), rect: el.getBoundingClientRect().toJSON() }));
    const fields = [...nodes].filter(el => el.matches('form.search-form input:not([type="hidden"]),form.search-form button') && visible(el)).map(el => ({ tag: el.tagName.toLowerCase(), type: el.type, name: el.getAttribute('name'), label: el.getAttribute('aria-label'), placeholder: el.getAttribute('placeholder'), value: el.value, tabIndex: el.tabIndex, focused: document.activeElement === el, outline: getComputedStyle(el).outline, ...appearance(el), placeholderAppearance: el.placeholder ? appearance(el, '::placeholder') : null }));
    return { title: document.title, bodyClass: document.body.className, theme: document.documentElement.dataset.tsTheme || 'light', rows, surfaces, fields,
      counts: { result: main.querySelectorAll('.ast-article-post').length, title: main.querySelectorAll('.ast-article-post .entry-title').length, excerpt: main.querySelectorAll('.ast-article-post .ast-excerpt-container').length, more: main.querySelectorAll('.ast-article-post .read-more a').length, empty: main.querySelectorAll('.no-results').length, search: main.querySelectorAll('form.search-form').length, pagination: document.querySelectorAll('body.search .ast-pagination,body.search .navigation.pagination').length, currentPage: [...nodes].filter(el => el.matches('.page-numbers.current')).length },
      horizontalOverflow: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth) - innerWidth,
      content: { headings: [...main.querySelectorAll('h1,h2,h3')].filter(e => !e.closest('.ts-pk')).map(e => [e.id, norm(e.textContent)]), links: [...main.querySelectorAll('a[href]')].filter(e => !e.closest('.ts-pk')).map(e => [e.getAttribute('href'), norm(e.textContent)]) },
      nav: { config: document.querySelector('#ts-peek-nav-config')?.textContent || '', runtime: document.querySelector('#ts-peek-nav-runtime')?.textContent || '', css: document.querySelector('#ts-peek-nav-css')?.textContent || '', data: document.querySelector('#ts-peek-nav-data')?.textContent || null, strip: document.querySelectorAll('#ts-peek-title-strip').length, marker: document.querySelector('.ts-pk-nav')?.dataset.tsPkUi || null },
      payloadCount: [...document.querySelectorAll('style')].reduce((sum, s) => sum + (s.textContent.match(/ts-search-contrast:start/g) || []).length, 0) };
  });
}

async function capture(page, label) {
  const filename = `search-contrast-${engine}-${label.replaceAll('/', '-')}.jpg`;
  const bytes = await page.screenshot({ type: 'jpeg', quality: 78, fullPage: true });
  writeFileSync(join(out, filename), bytes, { flag: 'wx', mode: 0o600 });
  console.log(`INLINE ${filename} base64:${bytes.toString('base64')}`); captures++;
}
const digestNav = value => Object.fromEntries(Object.entries(value).map(([k, v]) => [k, typeof v === 'string' && ['config', 'runtime', 'css'].includes(k) ? sha(v) : v]));
try {
  browser = await pw[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  for (const route of routes) for (const width of widths) {
    const context = await browser.newContext({ viewport: { width, height: 900 }, colorScheme: 'light', reducedMotion: 'reduce' });
    await context.addInitScript(() => { localStorage.setItem('ts-theme', 'light'); localStorage.setItem('ts-pk-tip', 'seen'); });
    const page = await context.newPage(), errors = []; let requests = 0;
    page.on('pageerror', error => errors.push(error.message));
    page.on('request', request => { if (request.isNavigationRequest() && request.frame() === page.mainFrame()) requests++; });
    try {
      const response = await page.goto(base + route.path, { waitUntil: 'load', timeout: 45000 });
      if (response?.status() !== 200 || new URL(page.url()).origin !== base || /Robot Challenge/i.test(await page.title())) throw new Error(`Expected original HTTP200 Search; got ${response?.status()}; stop without further requests`);
      loads++;
      await page.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(page);
      const original = await measure(page), originalNav = digestNav(original.nav);
      writeFileSync(join(out, `${route.name}-${width}-body.html`), await page.locator('body').evaluate(e => e.outerHTML), { flag: 'wx', mode: 0o600 });
      if (inspect) writeFileSync(join(out, `${route.name}-${width}-page.html`), await page.content(), { flag: 'wx', mode: 0o600 });
      for (const theme of themes) {
        const label = `${route.name}/${width}/${theme}`;
        if (theme === 'dark') {
          const toggle = page.locator('.ts-theme-toggle:visible').first();
          await toggle.click(); await page.waitForFunction(() => document.documentElement.dataset.tsTheme === 'dark'); await settle(page);
        }
        const data = await measure(page), nav = digestNav(data.nav);
        const measured = data.rows.filter(r => r.ratio !== null), bad = data.rows.filter(r => r.ratio === null || r.ratio < 4.5);
        const detail = { ...data, nav, minimum: measured.length ? Math.min(...measured.map(r => r.ratio)) : null, bad };
        results.push({ label, ...detail });
        console.log(`INFO Search contrast ${engine}/${label}: ${JSON.stringify(detail)}`);
        if (!inspect) {
          check(`${label}/original Search template and expected result state`, data.bodyClass.split(/\s+/).includes('search') && data.bodyClass.includes('ast-separate-container') && (route.name === 'results' ? data.counts.result >= 1 && data.counts.title === data.counts.result && data.counts.excerpt === data.counts.result && data.counts.more === data.counts.result && data.counts.empty === 0 : data.counts.result === 0 && data.counts.empty === 1 && data.counts.search >= 1), data.counts);
          check(`${label}/requested real theme and one served contrast block`, data.theme === theme && data.payloadCount === 1, { theme: data.theme, blocks: data.payloadCount });
          check(`${label}/all visible main text is measurable and at least 4.5 to one`, data.rows.length >= (route.name === 'results' ? 4 : 2) && bad.length === 0, { count: data.rows.length, minimum: detail.minimum, bad });
          check(`${label}/panel surfaces are opaque and match the intended theme`, data.surfaces.length >= 1 && data.surfaces.every(s => s.image === 'none' && (theme === 'light' ? s.background === 'rgb(255, 255, 255)' : s.background === `rgb(${s.panel?.slice(0, 3).join(', ')})`)), data.surfaces);
          check(`${label}/theme switch preserves content and all Peek code data and configuration`, JSON.stringify(data.content) === JSON.stringify(original.content) && JSON.stringify(nav) === JSON.stringify(originalNav), { original: originalNav, current: nav });
          check(`${label}/accepted title strip remains present without summary data`, nav.strip === 1 && nav.data === null && nav.config.length > 0 && nav.runtime.length > 0, nav);
          check(`${label}/no horizontal page overflow`, data.horizontalOverflow <= 1, data.horizontalOverflow);
          check(`${label}/one original page load and no JavaScript errors`, requests === 1 && errors.length === 0, { requests, errors });
        }
        await capture(page, label); completed.push(label);
        console.log(`INFO Search contrast completed ${engine}/${label}`);
      }
      // A second real toggle returns to the initial light surfaces without a reload.
      await page.locator('.ts-theme-toggle:visible').first().click();
      await page.waitForFunction(() => (document.documentElement.dataset.tsTheme || 'light') === 'light'); await settle(page);
      const returned = await measure(page);
      if (!inspect) check(`${route.name}/${width}/return-to-light/exact initial colors and content restored`, JSON.stringify(returned.rows) === JSON.stringify(original.rows) && JSON.stringify(returned.surfaces.map(s => [s.class, s.background, s.color])) === JSON.stringify(original.surfaces.map(s => [s.class, s.background, s.color])) && JSON.stringify(returned.content) === JSON.stringify(original.content) && requests === 1 && errors.length === 0);
    } finally { await context.close(); }
    if (base.startsWith('https:')) await new Promise(resolve => setTimeout(resolve, 5500));
  }
} catch (error) { invalid = true; console.log(`INVALID Search contrast ${engine}: ${error.stack || error.message}`); }
finally { if (browser) await browser.close(); }
if (JSON.stringify(completed) !== JSON.stringify(expectedCases) || loads !== routes.length * widths.length || captures !== expectedCases.length) { invalid = true; console.log(`INVALID Search contrast ${engine}: incomplete route/theme/width/load/capture evidence`); }
const summary = { base, engine, inspect, passed, failed, invalid, expectedCases, completed, loads, captures, results };
writeFileSync(join(out, 'results.json'), JSON.stringify(summary, null, 2) + '\n', { flag: 'wx', mode: 0o600 });
console.log(`INFO Search contrast ${engine}: ${inspect ? 'INSPECTION' : 'CHECK'}, ${passed} passed, ${failed} failed; ${completed.length}/${expectedCases.length} cases, ${loads} page loads, ${captures} captures; ${Math.round((Date.now() - started) / 1000)}s; evidence ${out}; ${SEARCH_LIMITATION}`);
process.exitCode = invalid ? 2 : failed ? 1 : 0;

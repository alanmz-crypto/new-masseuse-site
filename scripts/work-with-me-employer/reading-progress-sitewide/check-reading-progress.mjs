// Browser contract for sitewide reading progress. No WordPress writes.
// node check-reading-progress.mjs --fixture
// node check-reading-progress.mjs <http://localhost:8090|https://staging38.thaisolitude.com|https://thaisolitude.com> [inline]
import { createRequire } from 'node:module';
import { readFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium, firefox } = require('playwright');
const engine = process.env.TS_BROWSER || 'chromium';
if (!['chromium', 'firefox'].includes(engine)) throw new Error('TS_BROWSER must be chromium or firefox');
const base = (process.argv[2] || '').replace(/\/$/, '');
const fixture = base === '--fixture';
if (!fixture && !['http://localhost:8090', 'https://staging38.thaisolitude.com', 'https://thaisolitude.com'].includes(base)) {
  throw new Error('Use --fixture, isolated localhost:8090, exact staging38 or the HTTPS live apex');
}
const css = readFileSync(new URL('./reading-progress.css', import.meta.url), 'utf8');
const baselineCSS = fixture && process.env.TS_BASELINE_CSS ? readFileSync(process.env.TS_BASELINE_CSS, 'utf8') : '';
const marker = /\/\* ts-reading-progress:start[^]*?\/\* ts-reading-progress:end \*\//;
const browser = await (engine === 'firefox' ? firefox : chromium).launch(engine === 'chromium' && process.env.TS_CHROMIUM_EXECUTABLE ? { executablePath: process.env.TS_CHROMIUM_EXECUTABLE } : {});
console.log(`INFO reading progress browser: ${engine} ${browser.version()}`);
const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'no-preference' });
if (fixture) await context.route('**/*', route => route.abort());
let assertions = 0, failures = 0, invalid = false;
function check(label, ok) {
  assertions++;
  if (!ok) failures++;
  console.log(`${ok ? 'PASS' : 'FAIL'} ${label}`);
}
const pages = ['', 'about', 'services', 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage', 'corporate-massage-portland', 'work-with-me', 'contact', 'privacy-policy', 'accessibility'];
const researchPages = new Set(['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage']);
const tick = page => page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
async function scroll(page, fraction) {
  await tick(page);
  // Responsive layout and scroll anchoring may change the range after a resize.
  // Settle document geometry and the requested position, never the indicator
  // itself: its transform is still checked independently against actual scroll.
  let stableSamples = 0;
  let last;
  for (let attempt = 0; attempt < 12; attempt++) {
    const before = await page.evaluate(value => {
      const maximum = Math.max(0, document.documentElement.scrollHeight - innerHeight);
      scrollTo(0, maximum * value);
      return { maximum, width: innerWidth, height: innerHeight };
    }, fraction);
    await tick(page);
    await page.waitForTimeout(80);
    last = await page.evaluate(() => ({
      maximum: Math.max(0, document.documentElement.scrollHeight - innerHeight),
      width: innerWidth, height: innerHeight, y: scrollY,
    }));
    const settled = last.maximum === before.maximum && last.width === before.width && last.height === before.height
      && Math.abs(last.y - last.maximum * fraction) <= 1;
    stableSamples = settled ? stableSamples + 1 : 0;
    if (stableSamples >= 2) return;
  }
  throw new Error(`Document scroll did not settle at ${fraction}: ${JSON.stringify(last)} (${page.url()})`);
}
async function state(page) {
  return page.evaluate(() => {
    const style = getComputedStyle(document.body, '::after');
    const maximum = document.documentElement.scrollHeight - innerHeight;
    return {
      visible: style.content !== 'none' && style.content !== 'normal' && style.display !== 'none',
      scale: style.transform === 'none' ? null : new DOMMatrixReadOnly(style.transform).a,
      expected: maximum > 0 ? scrollY / maximum : 0,
      height: style.height, position: style.position, pointer: style.pointerEvents,
      color: style.backgroundColor,
      overflow: document.documentElement.scrollWidth > innerWidth,
      maximum, scrollY, viewportWidth: innerWidth, viewportHeight: innerHeight,
    };
  });
}
async function baselineComparison(page) {
  return page.evaluate(pattern => {
    const block = new RegExp(pattern);
    const style = [...document.querySelectorAll('style')].find(s => block.test(s.textContent));
    const original = style.textContent;
    const measure = () => ({
      width: document.documentElement.scrollWidth,
      text: document.body.innerText,
      rects: [...document.body.querySelectorAll('*')].filter(el => !['SCRIPT', 'STYLE'].includes(el.tagName)).map(el => {
        const r = el.getBoundingClientRect();
        return [r.x + scrollX, r.y + scrollY, r.width, r.height];
      }),
    });
    const applied = measure();
    let baseline;
    try {
      // Remove only this feature from the current DOM. This preserves the
      // same rendered page, assets, viewport, scroll position and theme.
      style.textContent = original.replace(block, '');
      baseline = measure();
    } finally {
      style.textContent = original;
    }
    return {
      appliedWidth: applied.width,
      baselineWidth: baseline.width,
      geometrySame: JSON.stringify(applied.rects) === JSON.stringify(baseline.rects),
      textSame: applied.text === baseline.text,
    };
  }, marker.source);
}
function fixtureHTML(service) {
  return `<!doctype html><html lang="en"><head><title>Reading progress fixture</title><style>
    :root{--ts-accent-deep:#7A520A}html[data-ts-theme="dark"]{--ts-accent-deep:#E2B866;background:#151c26;color:white}
    body{margin:0;font:18px/1.6 system-ui}header,footer,main{padding:24px;box-sizing:border-box}main{max-width:1000px;margin:auto}
    section{min-height:650px}details div{height:1400px}a{color:inherit}h1{overflow-wrap:anywhere}
  </style><style>${baselineCSS}</style><style id="wp-custom-css">${css}</style></head><body><header><a href="#main">Skip to content</a></header>
    <main id="main" class="entry-content"><h1>Massage information</h1><section><a href="#details">Read more</a></section>
    <section>Session information</section>${service ? '<details id="details"><summary>Sources</summary><div><cite class="ts-source">Published source</cite></div></details>' : ''}
    <section>Contact information</section></main><footer>Oregon License LMT-29905</footer></body></html>`;
}
try {
  for (const slug of pages) {
    const service = researchPages.has(slug);
    const label = slug || 'home';
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    if (fixture) await page.setContent(fixtureHTML(service));
    else {
      const response = await page.goto(base + (slug ? `/${slug}/` : '/'), { waitUntil: 'networkidle', timeout: 60000 });
      if (response?.status() === 202 || /Robot Challenge/i.test(await page.title())) {
        invalid = true;
        console.log('INVALID: hosting challenge; nothing further tested');
        await page.close();
        break;
      }
      check(`${label}: HTTP 200`, response?.status() === 200);
    }
    await page.addStyleTag({ content: 'html{scroll-behavior:auto!important}' });
    await page.evaluate(() => document.fonts.ready);
    check(`${label}: exact CSS installed once`, await page.locator('style').evaluateAll((styles, expected) => {
      const all = styles.map(style => style.textContent).join('\n');
      return all.split('ts-reading-progress:start').length === 2 && all.includes(expected.trim());
    }, css));
    const nativeTimeline = await page.evaluate(() => CSS.supports('animation-timeline: scroll(root block)'));
    // Chromium is the required native-animation test lane. An older bundled
    // Firefox instead exercises the real unsupported-browser fallback; it must
    // not be reported as verification of Firefox's newer animation support.
    if (engine === 'chromium') check(`${label}: browser supports native timeline`, nativeTimeline);
    console.log(`INFO ${label}: ${nativeTimeline ? 'native timeline mode' : 'unsupported browser fallback mode (no timeline animation tested)'}`);

    for (const width of [1280, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      for (const theme of ['light', 'dark']) {
        await page.evaluate(theme => document.documentElement.setAttribute('data-ts-theme', theme), theme);
        await scroll(page, 0.5);
        const value = await state(page);
        const view = `${label} ${width} ${theme}`;
        const comparison = await baselineComparison(page);
        check(`${view}: no added side-scroll`, comparison.appliedWidth === comparison.baselineWidth);
        check(`${view}: content and element geometry unchanged`, comparison.geometrySame && comparison.textSame);
        if (value.overflow) console.log(`INFO ${view}: overflow observed; with progress CSS=${comparison.appliedWidth}px, without progress CSS=${comparison.baselineWidth}px, viewport=${width}px`);
        check(`${view}: ${nativeTimeline ? 'correct scope' : 'fallback indicator hidden'}`, value.visible === nativeTimeline);
        if (!nativeTimeline) continue;
        check(`${view}: fixed 3px decoration ignores pointer`, value.position === 'fixed' && value.height === '3px' && value.pointer === 'none');
        const tracks = value.scale !== null && Math.abs(value.scale - value.expected) < 0.025 && (value.maximum <= 0 || Math.abs(value.expected - 0.5) <= Math.max(0.05, 1 / value.maximum));
        check(`${view}: tracks current scroll range`, tracks);
        if (!tracks) console.log(`INFO ${view}: progress mismatch ${JSON.stringify(value)}`);
        check(`${view}: uses theme accent`, await page.evaluate(() => {
          const swatch = document.createElement('span');
          swatch.style.color = 'var(--ts-accent-deep, #7A520A)';
          document.body.append(swatch);
          const accent = getComputedStyle(swatch).color;
          swatch.remove();
          return getComputedStyle(document.body, '::after').backgroundColor === accent;
        }));
      }
    }
    {
      for (const fraction of nativeTimeline ? [0, 1] : []) {
        await scroll(page, fraction);
        const value = await state(page);
        check(`${label}: ${fraction ? 'end matches scroll range' : 'start empty'}`, value.scale !== null && Math.abs(value.scale - (value.maximum > 0 ? fraction : 0)) < 0.025);
      }
      if (await page.locator('.entry-content details').count()) {
        await page.locator('.entry-content details').first().evaluate(detail => { detail.open = false; });
        await scroll(page, 0.5);
        const closed = await state(page);
        if (nativeTimeline) await page.locator('.entry-content details').first().evaluate(detail => { detail.open = true; });
        else {
          await page.locator('.entry-content details summary').first().press('Enter');
          check(`${label}: fallback research panel opens with keyboard`, await page.locator('.entry-content details').first().evaluate(detail => detail.open));
        }
        await page.waitForTimeout(500);
        const expanded = await state(page);
        check(`${label}: expanded panel changes scroll range`, expanded.maximum > closed.maximum);
        if (nativeTimeline) check(`${label}: progress recalculates for expanded panel`, expanded.scale !== null && Math.abs(expanded.scale - expanded.expected) < 0.025);
        else check(`${label}: fallback stays hidden after panel expansion`, !expanded.visible);
      }

      for (const media of [{ reducedMotion: 'reduce' }, { media: 'print' }, { forcedColors: 'active' }]) {
        await page.emulateMedia(media);
        check(`${label}: suppressed for ${JSON.stringify(media)}`, !(await state(page)).visible);
        await page.emulateMedia({ reducedMotion: 'no-preference', media: 'screen', forcedColors: 'none' });
      }
      await page.evaluate(() => document.body.classList.add('admin-bar'));
      check(`${label}: hidden with admin bar`, !(await state(page)).visible);
      await page.evaluate(() => document.body.classList.remove('admin-bar'));
      if (process.env.TS_SCREENSHOT_DIR && slug === '') {
        mkdirSync(process.env.TS_SCREENSHOT_DIR, { recursive: true });
        await page.setViewportSize({ width: 390, height: 844 });
        for (const theme of ['light', 'dark']) {
          await page.evaluate(theme => document.documentElement.setAttribute('data-ts-theme', theme), theme);
          await scroll(page, 0.5);
          await page.screenshot({ path: join(process.env.TS_SCREENSHOT_DIR, `${fixture ? 'fixture' : 'home'}-390-${theme}.png`) });
        }
      }
      if (fixture) {
        await page.locator('#wp-custom-css').evaluate(style => { style.textContent = style.textContent.replace('@supports (animation-timeline: scroll(root block))', '@supports (ts-unsupported-property: unavailable)'); });
        check(`${label}: unsupported-feature fallback hidden`, !(await state(page)).visible);
        check(`${label}: unsupported-feature fallback keeps content`, await page.locator('h1').isVisible());
      }
      if (!fixture && process.argv.includes('inline') && slug === '') {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light'));
        await scroll(page, 0.5);
        const jpg = await page.screenshot({ type: 'jpeg', quality: 70 });
        console.log(`INLINE reading-progress-home-mobile.jpg base64:${jpg.toString('base64')}`);
      }
    }
    check(`${label}: no browser script errors`, errors.length === 0);
    await page.close();
  }
  if (fixture) {
    const page = await context.newPage();
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.setContent(`<!doctype html><html><head><style>body{margin:0}main{padding:16px}</style><style>${css}</style></head><body><main><h1>Short page</h1><a href="#short">Contact</a></main></body></html>`);
    await tick(page);
    const short = await state(page);
    check('short page: no scroll range', short.maximum <= 0);
    check('short page: no misleading filled indicator', !short.visible || short.scale === 0);
    check('short page: content visible', await page.locator('h1').isVisible());
    check('short page: link remains keyboard reachable', await page.locator('a').evaluate(link => { link.focus(); return document.activeElement === link; }));
    await page.close();
  }
} finally {
  await context.close();
  await browser.close();
}
console.log(`Sitewide reading progress (${engine}): ${assertions} browser assertions, ${failures} failures${invalid ? ', INVALID' : ''}`);
process.exitCode = invalid ? 2 : failures ? 1 : 0;

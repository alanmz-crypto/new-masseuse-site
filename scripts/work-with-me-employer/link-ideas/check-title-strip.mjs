// Inventory-driven, read-only title-strip checks. Local response fixtures are
// separate from the actual published-page verdict; no public runtime injection.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if (!local && base !== 'https://staging39.thaisolitude.com') throw new Error('exact staging39 or isolated localhost origin only');
if (flags.some(flag => !['--firefox', '--fixtures', '--fixtures-only'].includes(flag)) || new Set(flags).size !== flags.length) throw new Error('unknown or repeated title-strip flag');
const fixturesOnly = flags.includes('--fixtures-only'), fixtures = fixturesOnly || flags.includes('--fixtures');
if (fixtures && !local) throw new Error('title fixtures are isolated localhost only');
const engine = flags.includes('--firefox') ? 'firefox' : 'chromium';
const enabled = new Set(JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8')).pages.filter(page => page.staging === 'enabled').map(page => '/' + page.slug + '/'));
const minimum = ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/chair-massage/', '/corporate-massage-portland/', '/work-with-me/', '/contact/', '/privacy-policy/', '/accessibility/'];
let pass = 0, fail = 0, captures = 0, visited = 0;
const started = Date.now();
const ok = (name, value, detail) => { value ? pass++ : fail++; console.log(`${value ? 'PASS' : 'FAIL'} title ${engine}/${name}${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`); };
const settle = p => p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
const browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
const state = p => p.evaluate(() => ({ x: scrollX, y: scrollY, hash: location.hash, url: location.href, history: history.length }));
const sameAddress = (a, b) => a.hash === b.hash && a.url === b.url && a.history === b.history;
const still = (a, b) => sameAddress(a, b) && Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1;
const open = async (ctx, path, status = 200) => {
  const p = await ctx.newPage(), errors = [];
  p.on('pageerror', error => errors.push(error.message));
  const response = await p.goto(base + path, { waitUntil: 'load' });
  if (!response || response.status() !== status || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${path}: expected same-origin HTML${status}, got${response?.status()}`);
  await p.waitForSelector('#ts-peek-title-strip', { timeout: 10000 });
  await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(p);
  return { p, errors };
};
// Derive the expected content independently from rendered DOM. A row still owns
// service navigation; otherwise only visible structural h2 headings are titles.
const inspect = p => p.evaluate(() => {
  const norm = text => text.replace(/\s+/g, ' ').trim();
  const main = document.querySelector('#main,main,[role="main"]') || document.querySelector('#primary,#content') || document.body;
  const entries = main.querySelectorAll('.entry-content'), content = entries.length === 1 ? entries[0] : main;
  const strip = document.getElementById('ts-peek-title-strip'), row = content.querySelector('.ts-jump'), rail = !!document.querySelector('.ts-pk-nav');
  const config = JSON.parse(document.getElementById('ts-peek-nav-config').textContent);
  const visible = node => {
    if (!node || !content.contains(node) || node.closest('nav,[role="navigation"],header,footer,dialog,[role="banner"],[role="contentinfo"],[role="dialog"],.ts-pk,.ts-jump,[hidden],[inert],[aria-hidden="true"],.screen-reader-text,.sr-only')) return false;
    const r = node.getBoundingClientRect(); if (r.width < 2 || r.height < 2) return false;
    for (let a = node; a && content.contains(a); a = a.parentElement) {
      const cs = getComputedStyle(a), box = a.getBoundingClientRect();
      if (cs.display === 'none' || ['hidden', 'collapse'].includes(cs.visibility) || cs.contentVisibility === 'hidden') return false;
      if (a.tagName === 'DETAILS' && !a.open && !a.querySelector('summary')?.contains(node)) return false;
      if (a !== node && ['hidden', 'clip'].includes(cs.overflowY) && (a.clientHeight < 2 || r.top >= box.bottom || r.bottom <= box.top)) return false;
    }
    return true;
  };
  let expected = row ? [...row.querySelectorAll('a[href^="#"]')].map(a => {
    let id = a.hash.slice(1); try { id = decodeURIComponent(id); } catch {}
    return { node: document.getElementById(id), label: norm(a.textContent) };
  }).filter(item => item.node && (rail || visible(item.node))) : [...content.querySelectorAll('h2')].filter(visible).map(node => ({ node, label: norm(node.innerText) }));
  expected.sort((a, b) => a.node === b.node ? 0 : a.node.compareDocumentPosition(b.node) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1);
  if (!expected.length) expected = [{ node: content, label: config.title }];
  window.__peekTitleExpected = expected;
  const actual = [...strip.querySelectorAll('.tr > span')].map(node => node.textContent);
  return { expected: expected.map(item => item.label), actual, config, rail,
    ready: document.documentElement.classList.contains('ts-pk-title-ready'), railReady: document.documentElement.classList.contains('ts-pk-ready'),
    strips: document.querySelectorAll('#ts-peek-title-strip,.ts-pk-top').length,
    configs: document.querySelectorAll('#ts-peek-nav-config').length, data: document.querySelectorAll('#ts-peek-nav-data').length,
    row: row ? [...row.querySelectorAll('a[href^="#"]')].map(a => a.getAttribute('href')) : [],
    interactive: [...strip.querySelectorAll('a[href],button,input,select,textarea,summary,[contenteditable="true"],[tabindex]')].filter(node => node.tabIndex >= 0 || !node.hasAttribute('tabindex')).length,
    pointer: getComputedStyle(strip).pointerEvents, position: getComputedStyle(strip).position };
});
const bounds = async (p, label) => {
  const g = await p.evaluate(() => {
    const strip = document.getElementById('ts-peek-title-strip'), head = document.getElementById('masthead'), r = strip.getBoundingClientRect(), h = head?.getBoundingClientRect();
    const single = strip.querySelectorAll('.tr > span').length === 1, text = strip.querySelector('.tr > span'), box = strip.querySelector('.in').getBoundingClientRect();
    const range = document.createRange(); range.selectNodeContents(text);
    const whole = !single || [...range.getClientRects()].every(line => line.left >= box.left - 1 && line.right <= box.right + 1 && line.top >= box.top - 1 && line.bottom <= box.bottom + 1);
    return { strip: { top: r.top, bottom: r.bottom, left: r.left, right: r.right, height: r.height }, header: h && { top: h.top, bottom: h.bottom, position: getComputedStyle(head).position }, width: innerWidth, height: innerHeight, whole, single, current: strip.querySelector('.is-now')?.textContent };
  });
  ok(`${label}: strip stays in the viewport width and clears the visible pinned header`, g.strip.height > 0 && g.strip.left >= -1 && g.strip.right <= g.width + 1 && (!g.header || (g.header.position === 'sticky' && (g.header.bottom <= 0 || g.strip.top >= g.header.bottom - 1))), g);
  ok(`${label}: a single title is fully readable`, g.whole, g);
  return g;
};
const track = async (p, label, count) => {
  const original = await state(p);
  for (const index of [...new Set([0, Math.floor(count / 2), count - 1])]) {
    const candidate = await p.evaluate(index => {
      const node = window.__peekTitleExpected[index].node, strip = document.getElementById('ts-peek-title-strip');
      const line = Math.max(innerHeight / 4, (parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight + 24);
      return { y: scrollY + node.getBoundingClientRect().top - line + 2, max: document.documentElement.scrollHeight - innerHeight };
    }, index);
    // A short page may have no room to align a heading with the reading line.
    // Its complete ordered title list is still checked; do not invent scroll room.
    if (candidate.y < 0 || candidate.y > candidate.max) continue;
    await p.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), candidate.y); await settle(p);
    const current = await p.locator('#ts-peek-title-strip .is-now').textContent();
    const expected = await p.evaluate(index => window.__peekTitleExpected[index].label, index);
    ok(`${label}: scrolling to heading${index + 1} selects its title`, current === expected, { expected, current });
    ok(`${label}: scrolling changes no address or history`, sameAddress(original, await state(p)));
  }
  // Sticky UI belongs to the main-content container, not the following footer.
  // Check its last reading position while that content still fills the viewport.
  await p.evaluate(() => {
    const content = document.querySelector('.ts-pk-title-content') || document.querySelector('#main,main,[role="main"]') || document.body;
    scrollTo({ top: Math.max(0, scrollY + content.getBoundingClientRect().bottom - innerHeight), behavior: 'instant' });
  }); await settle(p);
  await bounds(p, label + '/scrolled');
};
const print = async (p, label) => {
  await p.emulateMedia({ media: 'print' });
  const result = await p.evaluate(() => ({ strip: getComputedStyle(document.getElementById('ts-peek-title-strip')).display,
    header: document.getElementById('masthead') && getComputedStyle(document.getElementById('masthead')).position,
    rows: [...document.querySelectorAll('.ts-jump')].every(row => getComputedStyle(row).display !== 'none') }));
  ok(`${label}: print removes the strip and releases the header while keeping jump rows`, result.strip === 'none' && result.header !== 'sticky' && result.header !== 'fixed' && result.rows, result);
  await p.emulateMedia({ media: 'screen' }); await settle(p);
};
const menu = async (p, label) => {
  const toggle = p.locator('#masthead .menu-toggle:visible').first();
  if (!await toggle.count()) throw new Error(`INVALID ${label}: expected mobile menu toggle`);
  await p.evaluate(() => scrollTo({ top: Math.min(600, document.documentElement.scrollHeight - innerHeight), behavior: 'instant' })); await settle(p);
  const before = await state(p); await toggle.focus(); await settle(p);
  ok(`${label}: focusing the pinned mobile menu keeps the page still`, still(before, await state(p)));
  await toggle.click(); await p.waitForTimeout(150);
  const visible = await p.evaluate(() => {
    const panels = [...document.querySelectorAll('#masthead .ast-mobile-header-content')];
    return panels.some(panel => { const r = panel.getBoundingClientRect(); return r.height > 0 && getComputedStyle(panel).display !== 'none' && r.top >= 0 && r.bottom <= innerHeight + 1; });
  });
  ok(`${label}: mobile menu opens within the viewport without a page jump`, visible && still(before, await state(p)));
  await toggle.click(); await p.waitForTimeout(100);
};
const existingAnchor = async (p, selector, label, faq = false, extraBar = null) => {
  const link = p.locator(selector).first(), href = await link.getAttribute('href');
  if (!href?.startsWith('#')) throw new Error(`INVALID ${label}: missing existing same-page destination`);
  await link.click();
  const measure = () => p.evaluate(({ href, extraBar }) => {
    const target = document.getElementById(decodeURIComponent(href.slice(1))), r = target.getBoundingClientRect(), strip = document.getElementById('ts-peek-title-strip').getBoundingClientRect();
    const ceiling = Math.max(strip.bottom, extraBar ? document.querySelector(extraBar).getBoundingClientRect().bottom : 0);
    return { hash: location.hash, top: r.top, bottom: r.bottom, strip: ceiling, visible: r.height > 0 && r.bottom > ceiling && r.top < innerHeight,
      faq: !target.classList.contains('uagb-faq-item') || target.classList.contains('uagb-faq-item-active'), y: scrollY };
  }, { href, extraBar });
  // Existing theme/guide navigation may animate even with reduced motion. The
  // title strip must leave the actual destination clear when that scroll ends.
  await p.waitForTimeout(700); await settle(p);
  const actual = await measure();
  ok(`${label}: existing link keeps its destination and lands below the strip`, actual.hash === href && actual.visible && actual.top >= actual.strip - 2 && await link.getAttribute('href') === href && (!faq || actual.faq), { href, ...actual });
};
const demoControls = async (p, path, label) => {
  const demos = {
    '/in-page-link-ideas/': { bar: '.tsx-pills.is-on', link: '.tsx-pills a[href="#tsx-questions"]' },
    '/progress-nav-ideas/': { bar: '.tspA.is-on', link: '.tspA a[href="#tsp-questions"]' },
    '/rail-nav-ideas/': { bar: '.tsr-top' },
    '/index-nav-ideas/': { bar: '.tsi-nav' }
  };
  const demo = demos[path]; if (!demo) return;
  await p.evaluate(() => scrollTo({ top: 650, behavior: 'instant' })); await settle(p);
  const geometry = await p.locator(demo.bar).evaluate(bar => {
    const r = bar.getBoundingClientRect(), strip = document.getElementById('ts-peek-title-strip').getBoundingClientRect();
    return { top: r.top, bottom: r.bottom, height: r.height, strip: strip.bottom, viewport: innerHeight };
  });
  const clear = geometry.height > 0 && geometry.top >= geometry.strip - 1 && geometry.bottom <= geometry.viewport + 1;
  ok(`${label}: existing practice navigation remains visible below the shared strip`, clear, geometry);
  if (!clear) { await capture(p, `${label.replaceAll('/', '-')}-existing-control-overlap`); return; }
  if (demo.link) await existingAnchor(p, demo.link, label + '/existing-demo-link', false, demo.bar);
  if (path === '/rail-nav-ideas/') {
    const link = p.locator('.tsr-rail a[href="#tsr-questions"]'), href = await link.getAttribute('href');
    const beforePreview = await state(p);
    await link.focus(); await link.press('Enter'); await p.waitForTimeout(100);
    const go = p.locator('.tsr-card.is-pinned .go'), ready = await go.isVisible();
    ok(`${label}: existing rail preview still offers its Go action`, ready);
    ok(`${label}: opening the existing rail preview keeps page and address still before Go`, still(beforePreview, await state(p)));
    if (ready) {
      await go.click(); await p.waitForTimeout(700); await settle(p);
      const landed = await p.evaluate(href => {
        const target = document.getElementById(href.slice(1)).getBoundingClientRect(), old = document.querySelector('.tsr-top').getBoundingClientRect(), shared = document.getElementById('ts-peek-title-strip').getBoundingClientRect();
        return { hash: location.hash, target: target.top, bottom: target.bottom, ceiling: Math.max(old.bottom, shared.bottom), viewport: innerHeight };
      }, href);
      ok(`${label}: existing rail Go lands below both title strips`, landed.hash === href && landed.target >= landed.ceiling - 2 && landed.target < landed.viewport && landed.bottom > landed.ceiling && await link.getAttribute('href') === href, landed);
    }
  }
};
const capture = async (p, label) => {
  if (engine !== 'chromium') return;
  captures++; console.log(`INLINE title-${label}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`);
};
try {
  if (!fixturesOnly) {
    const file = process.env.TS_PEEK_TITLE_INVENTORY || new URL('./title-strip-pages.json', import.meta.url);
    const raw = JSON.parse(readFileSync(file, 'utf8')), pages = raw.pages;
    if (!Array.isArray(pages) || pages.length < minimum.length || pages.some(page => !Number.isInteger(page.id) || typeof page.title !== 'string' || !/^\/(?:[^?#]*\/)?$/.test(page.path)) || new Set(pages.map(page => page.path)).size !== pages.length || new Set(pages.map(page => page.id)).size !== pages.length || minimum.some(path => !pages.some(page => page.path === path))) throw new Error('INVALID incomplete or malformed published-page inventory');
    console.log(`INFO title inventory: ${pages.length} published pages; ${process.env.TS_PEEK_TITLE_INVENTORY ? 'fresh copy export' : 'reviewed inventory'}; all routes included.`);
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce' });
    await ctx.addInitScript(() => { localStorage.setItem('ts-pk-tip', 'seen'); });
    if (!local) {
      const response = await ctx.request.get(base + '/wp-json/wp/v2/pages?per_page=100&status=publish&_fields=id,link&orderby=id&order=asc');
      if (response.status() !== 200) throw new Error(`INVALID published-page inventory comparison: HTTP${response.status()}`);
      const current = await response.json(), total = Number(response.headers()['x-wp-total']);
      const routes = Array.isArray(current) ? current.map(page => ({ id: page.id, path: new URL(page.link).pathname })).sort((a, b) => a.id - b.id) : [];
      const expected = pages.map(({ id, path }) => ({ id, path })).sort((a, b) => a.id - b.id);
      if (total !== pages.length || JSON.stringify(routes) !== JSON.stringify(expected)) throw new Error(`INVALID published inventory changed: expected${pages.length}, actual${total}; refresh inventory before claiming all-page coverage`);
      ok('inventory: every currently published page is covered', true);
    }
    const nojsRoutes = [];
    for (const page of [...pages, { path: '/?s=massage', status: 200 }, { path: '/peek-title-missing-page-verification-20261109/', status: 404 }]) {
      const { p, errors } = await open(ctx, page.path, page.status || 200); visited++;
      const label = page.path.replace(/^\/$/, 'home').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '');
      for (const width of [390, 768, 1280]) {
        await p.setViewportSize({ width, height: 844 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
        const actual = await inspect(p), prefix = `${label}/${width}`;
        ok(`${prefix}: one read-only title strip uses the actual heading order or complete page title`, actual.ready && actual.strips === 1 && actual.configs === 1 && JSON.stringify(actual.actual) === JSON.stringify(actual.expected), actual);
        ok(`${prefix}: title strip is not interactive or in the tab sequence`, actual.interactive === 0 && actual.pointer === 'none' && actual.position === 'sticky', actual);
        const rail = enabled.has(page.path);
        ok(`${prefix}: only approved service pages expose data and a rail`, actual.rail === rail && actual.railReady === rail && actual.data === (rail ? 1 : 0), actual);
        if (page.title) {
          const decoded = await p.evaluate(text => { const element = document.createElement('textarea'); element.innerHTML = text; return element.value; }, page.title);
          ok(`${prefix}: safe page-title configuration matches the inventory`, actual.config.title === decoded, { expected: decoded, actual: actual.config.title });
        }
        await bounds(p, prefix);
        if ([390, 1280].includes(width)) await capture(p, `${label}-${width}`);
        await track(p, prefix, actual.expected.length);
        await print(p, prefix);
        await demoControls(p, page.path, prefix);
        if (width === 390 && page.path === '/about/') await menu(p, prefix);
        if ([390, 768].includes(width) && !rail && actual.row.length) {
          await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
          const target = page.path === '/privacy-policy/' ? '#ts-client-privacy' : actual.row.at(-1);
          await existingAnchor(p, `.ts-jump a[href="${target}"]`, prefix + '/jump-row', page.path === '/privacy-policy/');
        }
        if ([390, 768].includes(width) && page.path === '/work-with-me/') {
          await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
          const href = await p.$$eval('.entry-content a[href^="#"]', links => links.find(a => !a.closest('.ts-pk,.ts-jump,nav') && a.getBoundingClientRect().height > 0 && document.getElementById(decodeURIComponent(a.hash.slice(1))))?.getAttribute('href'));
          if (!href) throw new Error('INVALID Work With Me: existing hero anchor not found');
          await existingAnchor(p, `.entry-content a[href="${href}"]:not(.ts-pk a,.ts-jump a,nav a)`, prefix + '/hero-link');
        }
        if (width === 390 && actual.row.length && !rail) nojsRoutes.push({ path: page.path, links: actual.row });
      }
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors); await p.close();
    }
    await ctx.close();
    const nojs = await browser.newContext({ viewport: { width: 390, height: 844 }, javaScriptEnabled: false });
    for (const item of nojsRoutes) {
      const p = await nojs.newPage(), response = await p.goto(base + item.path, { waitUntil: 'load' });
      if (response?.status() !== 200) throw new Error(`INVALID no-JS${item.path}: response${response?.status()}`);
      ok(`${item.path}: no-JavaScript row stays visible with existing targets`, await p.evaluate(() => !document.querySelector('#ts-peek-title-strip') && [...document.querySelectorAll('.ts-jump')].every(row => getComputedStyle(row).display !== 'none' && [...row.querySelectorAll('a[href^="#"]')].every(a => document.getElementById(decodeURIComponent(a.hash.slice(1)))))));
      for (const href of [...new Set([item.links[0], item.links.at(-1)])]) {
        await p.locator(`.ts-jump a[href="${href}"]`).first().click(); await p.waitForTimeout(100);
        ok(`${item.path}: native no-JS link reaches${href}`, await p.evaluate(href => { const r = document.getElementById(decodeURIComponent(href.slice(1))).getBoundingClientRect(); return location.hash === href && r.height > 0 && r.bottom > 0 && r.top < innerHeight; }, href));
      }
      await p.close();
    }
    await nojs.close();
    const noPointer = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce' });
    await noPointer.addInitScript(() => { delete window.PointerEvent; });
    const { p, errors } = await open(noPointer, '/thai-massage/');
    if (await p.evaluate(() => 'PointerEvent' in window)) throw new Error('INVALID no-PointerEvent scenario: browser API property could not be removed');
    const actual = await inspect(p);
    ok('no PointerEvent: strip remains ready and the rail falls back to ordinary links', actual.ready && !actual.rail && !actual.railReady && actual.actual.length > 0 && await p.locator('.ts-jump').isVisible(), actual);
    ok('no PointerEvent: no JavaScript errors', errors.length === 0, errors); await noPointer.close();
  }
  if (fixtures) {
    const core = readFileSync(new URL('./peek-nav.php', import.meta.url), 'utf8');
    const css = core.match(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/)[0], js = core.match(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/)[0];
    const title = 'A complete page title that stays readable on a narrow screen without cutting off its final words';
    const block = (id, text) => `<h2 id="${id}">${text}</h2><p>Fixture paragraph.</p><div style="height:360px"></div>`;
    const cases = [
      { name: 'zero', html: '<h1>Fixture heading</h1><p>A short page.</p>', expected: [title] },
      { name: 'one', html: block('one', title), expected: [title] },
      { name: 'headings', noPointer: true, html: '<nav><h2>Navigation heading</h2></nav><header><h2>Embedded header</h2></header><footer><h2>Embedded footer</h2></footer><div hidden><h2>Hidden heading</h2></div><div aria-hidden="true"><h2>Aria-hidden heading</h2></div><div style="height:0;overflow:hidden"><h2>Collapsed heading</h2></div><details><summary>Closed detail</summary><h2>Closed-detail heading</h2></details>' + block('first', 'First content heading') + block('second', 'Second content heading') + block('third', 'Third content heading'), expected: ['First content heading', 'Second content heading', 'Third content heading'] },
      { name: 'row', html: '<nav class="ts-jump"><ul><li><a href="#third">Last label</a></li><li><a href="#missing">Missing</a></li><li><a href="#first">First label</a></li><li><a href="#hidden">Hidden label</a></li></ul></nav>' + block('first', 'First source heading') + '<div hidden><h2 id="hidden">Hidden source heading</h2></div>' + block('third', 'Last source heading'), expected: ['First label', 'Last label'] }
    ];
    for (const spec of cases) {
      const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce' });
      if (spec.noPointer) await ctx.addInitScript(() => { delete window.PointerEvent; });
      const path = '/__peek-title-fixture-' + spec.name + '/';
      await ctx.route(base + path, route => route.fulfill({ status: 200, contentType: 'text/html', body: '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font-family:system-ui}#masthead{height:85px;background:white}.entry-content{padding:0 20px}</style>' + css + '</head><body><header id="masthead">Header</header><main><div class="entry-content">' + spec.html + '</div></main><footer style="height:800px"></footer><script type="application/json" id="ts-peek-nav-config">' + JSON.stringify({ title }) + '</script>' + js + '</body></html>' }));
      const { p, errors } = await open(ctx, path);
      if (spec.noPointer && await p.evaluate(() => 'PointerEvent' in window)) throw new Error('INVALID local no-PointerEvent scenario: browser API property could not be removed');
      for (const width of [390, 768, 1280]) {
        await p.setViewportSize({ width, height: 844 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
        const actual = await inspect(p), label = `local-${spec.name}/${width}`;
        ok(`${label}: expected headings or full fixed title without page data`, JSON.stringify(actual.actual) === JSON.stringify(spec.expected) && actual.data === 0 && !actual.rail && actual.ready && !actual.railReady, actual);
        ok(`${label}: read-only with no interactive/tab destinations`, actual.interactive === 0 && actual.pointer === 'none', actual);
        await bounds(p, label); await track(p, label, actual.expected.length); await print(p, label);
      }
      if (spec.name === 'headings') {
        await p.evaluate(() => { document.getElementById('second').style.display = 'none'; }); await settle(p);
        ok('local hidden change: removed heading leaves the title list', JSON.stringify((await inspect(p)).actual) === JSON.stringify(['First content heading', 'Third content heading']));
        await p.evaluate(() => { document.getElementById('first').setAttribute('aria-hidden', 'true'); document.getElementById('third').hidden = true; }); await settle(p);
        ok('local collapse to zero: complete page title replaces all hidden headings', JSON.stringify((await inspect(p)).actual) === JSON.stringify([title]));
        await p.evaluate(() => { document.getElementById('second').style.display = ''; }); await settle(p);
        ok('local reveal: title list tracks a newly visible heading', JSON.stringify((await inspect(p)).actual) === JSON.stringify(['Second content heading']));
        await p.evaluate(() => { const h = document.getElementById('second'), wrap = document.createElement('div'); h.before(wrap); wrap.append(h); wrap.style.cssText = 'height:0;overflow:hidden'; }); await settle(p);
        ok('local collapsed ancestor: clipped heading does not remain current', JSON.stringify((await inspect(p)).actual) === JSON.stringify([title]));
      }
      if (spec.name === 'zero') {
        await p.setViewportSize({ width: 390, height: 844 });
        await p.evaluate(() => { document.documentElement.style.fontSize = parseFloat(getComputedStyle(document.documentElement).fontSize) * 2 + 'px'; });
        await settle(p); await settle(p);
        ok('local dynamic text: enlarged fixed title preserves every word', JSON.stringify((await inspect(p)).actual) === JSON.stringify([title]));
        await bounds(p, 'local dynamic 200% text');
      }
      ok(`local-${spec.name}: no JavaScript errors`, errors.length === 0, errors); await ctx.close();
    }
  }
} catch (error) {
  console.log(`INVALID title ${engine}: ${error.message}`); process.exitCode = 2;
} finally { await browser.close(); }
console.log(`INFO Peek title strip ${engine}: ${pass} passed, ${fail} failed; ${visited} actual routes, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s${fixturesOnly ? ', local fixtures only' : ''}; browser simulation is not physical-device or assistive-technology validation.`);
process.exit(process.exitCode || (fail ? 1 : 0));

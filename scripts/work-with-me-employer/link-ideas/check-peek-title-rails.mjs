// New title-only rails use the existing gesture implementation, without a page
// summary registration. These checks derive destinations from content, never
// from the rail being tested. Public runs only read staging39.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if (!local && base !== 'https://staging39.thaisolitude.com') throw new Error('exact staging39 or isolated localhost origin only');
if (flags.some(flag => !['--firefox', '--fixtures', '--fixtures-only'].includes(flag)) || new Set(flags).size !== flags.length) throw new Error('unknown or repeated title-rail flag');
const fixturesOnly = flags.includes('--fixtures-only'), fixtures = fixturesOnly || flags.includes('--fixtures');
if (fixtures && !local) throw new Error('title-rail response fixtures are localhost only');
const engine = flags.includes('--firefox') ? 'firefox' : 'chromium';
const manifest = JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8'));
const approved = new Set(manifest.pages.filter(page => page.staging === 'enabled').map(page => '/' + page.slug + '/'));
const legacy = new Set(['/rail-nav-ideas/', '/index-nav-ideas/']);
if (JSON.stringify(manifest.titleRail?.existingRailPages) !== JSON.stringify([...legacy])) throw new Error('title-rail existing-page inventory must match the reviewed two practice rails');
let pass = 0, fail = 0, visited = 0, captures = 0;
const started = Date.now();
const ok = (name, value, detail) => { value ? pass++ : fail++; console.log(`${value ? 'PASS' : 'FAIL'} title-rail ${engine}/${name}${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`); };
const settle = p => p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
const state = p => p.evaluate(() => ({ y: scrollY, x: scrollX, url: location.href, hash: location.hash, history: history.length }));
const still = (a, b) => a.url === b.url && a.history === b.history && Math.abs(a.y - b.y) <= 1 && Math.abs(a.x - b.x) <= 1;
let browser;
try { browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {}); }
catch (error) {
  console.log(`INVALID title-rail ${engine}: browser launch failed: ${error.message}`);
  console.log(`INFO Peek title rails ${engine}: 0 passed, 0 failed; 0 actual routes, 0 captures; browser unavailable.`);
  process.exit(2);
}
const context = async options => {
  const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce', hasTouch: true, ...options });
  await ctx.addInitScript(() => { try { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', 'right'); } catch {} });
  return ctx;
};
const open = async (ctx, path, status = 200) => {
  const p = await ctx.newPage(), errors = [];
  p.on('pageerror', error => errors.push(error.message));
  const response = await p.goto(base + path, { waitUntil: 'load' });
  if (!response || response.status() !== status || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${path}: expected same-origin HTML ${status}, got ${response?.status()}`);
  await p.waitForSelector('#ts-peek-title-strip', { timeout: 10000 });
  await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(p);
  return { p, errors };
};
const source = p => p.evaluate(() => {
  const norm = text => text.replace(/\s+/g, ' ').trim();
  const main = document.querySelector('#main,main,[role="main"]') || document.querySelector('#primary,#content') || document.body;
  const entries = main.querySelectorAll('.entry-content'), content = entries.length === 1 ? entries[0] : main, row = content.querySelector('.ts-jump');
  const config = JSON.parse(document.getElementById('ts-peek-nav-config').textContent);
  const visible = node => {
    if (!content.contains(node) || node.closest('nav,header,footer,dialog,[role="navigation"],[role="banner"],[role="contentinfo"],[role="dialog"],.ts-pk,.ts-jump,[hidden],[inert],[aria-hidden="true"],.screen-reader-text,.sr-only')) return false;
    const r = node.getBoundingClientRect(); if (r.width < 2 || r.height < 2) return false;
    for (let a = node; a && content.contains(a); a = a.parentElement) {
      const cs = getComputedStyle(a), ar = a.getBoundingClientRect();
      if (cs.display === 'none' || ['hidden', 'collapse'].includes(cs.visibility) || cs.contentVisibility === 'hidden') return false;
      if (a.tagName === 'DETAILS' && !a.open && !a.querySelector('summary')?.contains(node)) return false;
      if (a !== node && ['hidden', 'clip'].includes(cs.overflowY) && (a.clientHeight < 2 || r.top >= ar.bottom || r.bottom <= ar.top)) return false;
    }
    return true;
  };
  // A curated row is authoritative even when its native details target is
  // closed. Visibility filtering applies to automatically discovered headings.
  let items = row ? [...row.querySelectorAll('a[href^="#"]')].map(a => {
    let id = a.hash.slice(1); try { id = decodeURIComponent(id); } catch {}
    return { node: document.getElementById(id), label: norm(a.textContent), href: a.getAttribute('href') };
  }).filter(item => item.node) : [...content.querySelectorAll('h2')].filter(visible).map(node => ({ node, label: norm(node.innerText), href: node.id ? '#' + encodeURIComponent(node.id) : null }));
  items.sort((a, b) => a.node === b.node ? 0 : a.node.compareDocumentPosition(b.node) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1);
  if (config.mode === 'fixed' || !items.length) items = [{ node: content, label: config.title, href: null }];
  window.__peekTitleRailSource = items;
  return { labels: items.map(item => item.label), hrefs: items.map(item => item.href), ids: items.map(item => item.node.id), row: !!row, mode: config.mode };
});
const rail = p => p.evaluate(() => {
  const box = document.querySelector('.ts-pk-titles'), nav = document.querySelector('.ts-pk-nav'), controls = [...document.querySelectorAll('.ts-pk-rail > li > :first-child')];
  return { count: controls.length, labels: controls.map(node => node.getAttribute('aria-label')), hrefs: controls.map(node => node.getAttribute('href')), tags: controls.map(node => node.tagName), sections: controls.map(node => node.getAttribute('data-ts-pk-section')),
    titles: !!box, hidden: box?.hidden, ready: document.documentElement.classList.contains('ts-pk-ready'), navs: document.querySelectorAll('.ts-pk-nav').length,
    data: document.querySelectorAll('#ts-peek-nav-data').length, sums: box?.querySelectorAll('.sum').length || 0, role: nav?.getAttribute('aria-label'),
    tabs: controls.filter(node => node.tabIndex === 0).length, overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth + 1 };
});
const controls = p => p.locator('.ts-pk-rail > li > :first-child');
const preview = async (p, index, keyboard = false) => {
  const control = controls(p).nth(index);
  if (keyboard) { await control.focus(); await control.press('Enter'); } else await control.click();
  await settle(p);
};
const checkCard = async (p, expected, index, label) => {
  const actual = await p.evaluate(() => {
    const card = document.querySelector('.ts-pk-card'), open = card.querySelector('.open'), strip = document.getElementById('ts-peek-title-strip'), head = document.getElementById('masthead');
    const rect = node => { const r = node.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, height: r.height }; };
    return { pinned: card.classList.contains('is-pinned'), index: Number(card.dataset.i), title: open?.querySelector(':scope > b')?.textContent, small: open?.querySelector('small')?.textContent,
      sums: card.querySelectorAll('.sum,p,.d').length, rows: [...card.querySelectorAll('.row span')].map(node => node.textContent), card: rect(card), strip: rect(strip), header: head && rect(head),
      hint: open?.querySelector('.hint') && rect(open.querySelector('.hint')), titleRect: open?.querySelector(':scope > b') && rect(open.querySelector(':scope > b')), viewport: { width: innerWidth, height: innerHeight },
      scroll: { top: card.scrollTop, height: card.scrollHeight, client: card.clientHeight } };
  });
  ok(`${label}: opens the selected title with section count and no summary or reading-time copy`, actual.pinned && actual.index === index && actual.title === expected.labels[index] && actual.small === `Section ${index + 1} of ${expected.labels.length}` && actual.sums === 0 && actual.rows.every(text => expected.labels.includes(text)), actual);
  const { card: c, strip: s, header: h, viewport: v } = actual;
  const intersects = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom - 1 && a.bottom > b.top + 1;
  ok(`${label}: preview clears the actual visible title/header and viewport edges`, c.height > 0 && c.left >= -1 && c.right <= v.width + 1 && c.top >= 0 && c.bottom <= v.height + 1 && (s.bottom <= 0 || s.top >= v.height || !intersects(c, s)) && (!h || h.bottom <= 0 || h.top >= v.height || !intersects(c, h)), actual);
  ok(`${label}: ordinary portrait title and jump hint are visible`, actual.titleRect && actual.hint && actual.titleRect.top >= c.top - 1 && actual.titleRect.bottom <= c.bottom + 1 && actual.hint.top >= c.top - 1 && actual.hint.bottom <= c.bottom + 1, actual);
};
const jump = async (p, expected, index, label, pointer = false) => {
  const before = await state(p);
  if (pointer) await p.locator('.ts-pk-card .open > b').click();
  else { await p.locator('.ts-pk-card').focus(); await p.keyboard.press('Enter'); }
  await settle(p); await p.waitForTimeout(100);
  const actual = await p.evaluate(index => {
    const target = window.__peekTitleRailSource[index].node, r = target.getBoundingClientRect(), stripNode = document.getElementById('ts-peek-title-strip'), strip = stripNode.getBoundingClientRect();
    const max = Math.max(0, document.documentElement.scrollHeight - innerHeight), offset = Math.max((parseFloat(getComputedStyle(stripNode).top) || 0) + stripNode.offsetHeight + 12, parseFloat(getComputedStyle(target).scrollMarginTop) || 0);
    const focusTarget = /^H[1-6]$/.test(target.tagName) ? target : target.querySelector('h1,h2,h3,h4,h5,h6,.ts-faq-disclosure') || target;
    let details = true; for (let node = target; node; node = node.parentElement) if (node.tagName === 'DETAILS' && !node.open) details = false;
    return { focus: document.activeElement === focusTarget, hash: location.hash, history: history.length, y: scrollY, max, expectedY: Math.max(0, Math.min(max, scrollY + r.top - offset)),
      target: { top: r.top, bottom: r.bottom, height: r.height }, strip: strip.bottom, offset, viewport: innerHeight, details, pinned: document.querySelector('.ts-pk-card').classList.contains('is-pinned') };
  }, index);
  const hash = expected.hrefs[index] || before.hash;
  ok(`${label}: explicit activation focuses its original destination and preserves ID-less addresses`, actual.focus && actual.hash === hash && actual.history === before.history && actual.details && !actual.pinned, { ...actual, expectedHash: hash });
  // A genuinely short page cannot always align a target under the strip. In
  // that case the browser must be at its true scroll limit and show the target.
  ok(`${label}: jump lands below the strip or at the measured scroll limit`, actual.target.height > 0 && actual.target.bottom > actual.strip - 1 && actual.target.top < actual.viewport && Math.abs(actual.y - actual.expectedY) <= 6 && (actual.target.top >= actual.strip - 2 || actual.expectedY === 0 || actual.expectedY === actual.max), actual);
};
const indexAndSide = async (p, expected, label) => {
  await p.keyboard.press('Escape');
  const before = await state(p);
  await p.locator('.ts-pk-ix').click(); await p.waitForTimeout(180);
  ok(`${label}: quick icon tap keeps the names open without scrolling or changing the address`, await p.locator('.ts-pk-nav').evaluate(node => node.classList.contains('is-index') && node.classList.contains('is-kept')) && still(before, await state(p)));
  const items = await p.locator('.ts-pk-fan li > span').allTextContents();
  ok(`${label}: index names contain only original ordered titles`, JSON.stringify(items) === JSON.stringify(expected.labels), items);
  await p.locator('.ts-pk-fan li').last().click(); await settle(p);
  ok(`${label}: selecting an index name opens its preview without a page jump`, still(before, await state(p)) && await p.locator('.ts-pk-card').getAttribute('data-i') === String(expected.labels.length - 1));
  const wasLeft = await p.evaluate(() => document.documentElement.classList.contains('ts-pk-left'));
  await p.locator('.ts-pk-card .side').click(); await settle(p);
  ok(`${label}: side control changes the rail side without moving the page`, await p.evaluate(() => document.documentElement.classList.contains('ts-pk-left')) !== wasLeft && still(before, await state(p)));
  await preview(p, 0); await checkCard(p, expected, 0, label + '/opposite-side'); await p.keyboard.press('Escape');
  await p.locator('.ts-pk-ix').focus(); await p.keyboard.press('Enter'); await settle(p);
  ok(`${label}: keyboard icon opens the index without page navigation`, await p.locator('.ts-pk-ix').getAttribute('aria-expanded') === 'true' && still(before, await state(p)));
  await p.keyboard.press('Escape');
  ok(`${label}: Escape closes the index and keeps keyboard focus on its button`, await p.locator('.ts-pk-ix').evaluate(node => document.activeElement === node && node.getAttribute('aria-expanded') === 'false'));
};
const iconDrag = async (p, expected, label) => {
  const before = await state(p), icon = await p.locator('.ts-pk-ix').boundingBox();
  const start = { x: icon.x + icon.width / 2, y: icon.y + icon.height / 2 };
  const cdp = engine === 'chromium' ? await p.context().newCDPSession(p) : null;
  const touch = (type, point) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [point] });
  if (cdp) await touch('touchStart', start);
  else { await p.mouse.move(start.x, start.y); await p.mouse.down(); }
  await p.waitForTimeout(150);
  const name = await p.locator('.ts-pk-fan li').last().boundingBox();
  const end = { x: name.x + name.width / 2, y: name.y + name.height / 2 };
  if (cdp) for (let step = 1; step <= 5; step++) { await touch('touchMove', { x: start.x + (end.x - start.x) * step / 5, y: start.y + (end.y - start.y) * step / 5 }); await p.waitForTimeout(16); }
  else await p.mouse.move(end.x, end.y, { steps: 5 });
  ok(`${label}: sliding from the icon across names never scrolls the page`, still(before, await state(p)));
  if (cdp) { await touch('touchEnd'); await cdp.detach(); } else await p.mouse.up();
  await settle(p);
  ok(`${label}: releasing on a name keeps that preview without jumping`, still(before, await state(p)) && await p.locator('.ts-pk-card').evaluate((node, index) => node.classList.contains('is-pinned') && Number(node.dataset.i) === index, expected.labels.length - 1));
  await p.keyboard.press('Escape'); await p.waitForTimeout(460);
};
const nativeFAQ = async (p, expected, href, label) => {
  if (engine !== 'chromium') return;
  const index = expected.hrefs.indexOf(href);
  if (index < 0) throw new Error(`INVALID ${label}: original FAQ destination is absent`);
  const target = p.locator(`[id=${JSON.stringify(decodeURIComponent(href.slice(1)))}]`);
  const panel = target.locator('xpath=ancestor-or-self::*[contains(concat(" ", normalize-space(@class), " "), " uagb-faq-item ")]').last();
  if (!await panel.count()) throw new Error(`INVALID ${label}: destination has no original FAQ panel`);
  if (await panel.evaluate(node => node.classList.contains('uagb-faq-item-active'))) {
    await panel.locator('.uagb-faq-questions-button').click(); await p.waitForTimeout(400);
  }
  await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
  const before = await state(p);
  await controls(p).nth(index).tap(); await settle(p);
  ok(`${label}: native rail tap previews a closed FAQ without navigation`, still(before, await state(p)) && await p.locator('.ts-pk-card .open > b').textContent() === expected.labels[index]);
  await p.locator('.ts-pk-card .open > b').tap(); await p.waitForTimeout(750); await settle(p);
  const result = await target.evaluate(node => {
    const faq = node.closest('.uagb-faq-item'), r = node.getBoundingClientRect(), strip = document.getElementById('ts-peek-title-strip').getBoundingClientRect();
    const focus = /^H[1-6]$/.test(node.tagName) ? node : node.querySelector('h1,h2,h3,h4,h5,h6,.ts-faq-disclosure') || node;
    return { active: faq.classList.contains('uagb-faq-item-active'), focus: document.activeElement === focus, hash: location.hash, top: r.top, bottom: r.bottom, strip: strip.bottom, viewport: innerHeight };
  });
  ok(`${label}: native open-box tap opens the FAQ and lands/focuses below the strip`, result.active && result.focus && result.hash === href && result.top >= result.strip - 2 && result.top < result.viewport && result.bottom > result.strip, result);
};
const capture = async (p, label) => {
  if (engine !== 'chromium') return;
  captures++; console.log(`INLINE title-rail-${label}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`);
};
const exercise = async (p, label, expected, width, detailed = false) => {
  const actual = await rail(p);
  ok(`${label}: one title-only rail follows the source labels with zero page data`, actual.titles && !actual.hidden && actual.ready && actual.navs === 1 && actual.count === expected.labels.length && actual.data === 0 && actual.sums === 0 && JSON.stringify(actual.labels) === JSON.stringify(expected.labels), { actual, expected });
  ok(`${label}: controls preserve existing hrefs and use buttons for ID-less targets`, JSON.stringify(actual.hrefs) === JSON.stringify(expected.hrefs) && actual.tags.every((tag, i) => tag === (expected.hrefs[i] ? 'A' : 'BUTTON')) && actual.sections.every(value => value !== null), { actual, expected });
  ok(`${label}: exactly one rail keyboard stop and no new horizontal overflow`, actual.tabs === 1 && !actual.overflow, actual);
  const before = await state(p);
  await preview(p, 0); await checkCard(p, expected, 0, label + '/pointer-first');
  ok(`${label}: native pointer preview leaves page and address still`, still(before, await state(p)), { before, after: await state(p) });
  if ([390, 1280].includes(width)) await capture(p, label.replaceAll('/', '-'));
  if (expected.labels.length > 1) {
    await p.locator('.ts-pk-card .row').last().click(); await settle(p);
    ok(`${label}: tapping a closed title opens only its preview`, still(before, await state(p)) && await p.locator('.ts-pk-card .open > b').textContent() === expected.labels.at(-1) && await p.locator('.ts-pk-card .sum').count() === 0);
  }
  await p.keyboard.press('Escape');
  await controls(p).first().focus(); await p.keyboard.press('End');
  ok(`${label}: End roves to the last original section`, await controls(p).last().evaluate(node => document.activeElement === node));
  await p.keyboard.press('Enter'); await settle(p);
  await checkCard(p, expected, expected.labels.length - 1, label + '/keyboard-last');
  ok(`${label}: keyboard preview leaves page and address still`, still(before, await state(p)));
  await jump(p, expected, expected.labels.length - 1, label, width === 390);
  if (detailed) {
    await p.evaluate(() => scrollTo({ top: Math.min(500, document.documentElement.scrollHeight - innerHeight), behavior: 'instant' })); await settle(p);
    await indexAndSide(p, expected, label); await iconDrag(p, expected, label);
  }
  await p.emulateMedia({ media: 'print' });
  ok(`${label}: print hides title rails and restores existing jump rows`, await p.evaluate(() => [...document.querySelectorAll('.ts-pk')].every(node => getComputedStyle(node).display === 'none') && [...document.querySelectorAll('.ts-jump')].every(node => getComputedStyle(node).display !== 'none')));
  await p.emulateMedia({ media: 'screen' }); await settle(p);
};
const progressSwitch = async (p, label) => {
  const before = await state(p);
  const button = p.locator('.tsp-picker button[data-idea="5"]');
  if (!await button.count()) throw new Error('INVALID Progress fixture: missing original mode 5 button');
  await button.click(); await settle(p);
  const hidden = await p.evaluate(() => ({ custom: !!document.querySelector('.tsp-lab .tspE.is-on'), hidden: document.querySelector('.ts-pk-titles')?.hidden, ready: document.documentElement.classList.contains('ts-pk-ready'), padded: document.documentElement.classList.contains('ts-pk-pad'), peekFocusable: [...document.querySelectorAll('.ts-pk-titles button,.ts-pk-titles a')].some(node => node.getClientRects().length > 0) }));
  ok(`${label}: Progress E exposes only its existing rail and releases Peek padding/tab controls`, hidden.custom && hidden.hidden && !hidden.ready && !hidden.padded && !hidden.peekFocusable, hidden);
  await p.locator('.tsp-picker button[data-idea="1"]').click(); await settle(p);
  ok(`${label}: switching back to Progress A restores the title rail`, await p.evaluate(() => !document.querySelector('.ts-pk-titles').hidden && document.documentElement.classList.contains('ts-pk-ready')));
  // Mode buttons are existing content controls and may scroll themselves into
  // view; their address must still remain unchanged across the switch.
  ok(`${label}: practice mode switches retain the page address`, before.url === (await state(p)).url);
};
try {
  if (!fixturesOnly) {
    const inventory = JSON.parse(readFileSync(process.env.TS_PEEK_TITLE_INVENTORY || new URL('./title-strip-pages.json', import.meta.url), 'utf8')).pages;
    if (!Array.isArray(inventory) || inventory.length < 19 || new Set(inventory.map(page => page.id)).size !== inventory.length || new Set(inventory.map(page => page.path)).size !== inventory.length || inventory.some(page => !Number.isInteger(page.id) || !/^\/(?!\/)/.test(page.path) || !page.title)) throw new Error('INVALID incomplete published-page inventory');
    const ctx = await context();
    if (!local) {
      const response = await ctx.request.get(base + '/wp-json/wp/v2/pages?per_page=100&_fields=id,link');
      if (response.status() !== 200) throw new Error(`INVALID published-page inventory response ${response.status()}`);
      const live = await response.json(), count = Number(response.headers()['x-wp-total']);
      const routes = live.map(page => ({ id: page.id, path: new URL(page.link).pathname })).sort((a, b) => a.id - b.id);
      const expected = inventory.map(({ id, path }) => ({ id, path })).sort((a, b) => a.id - b.id);
      if (count !== inventory.length || JSON.stringify(routes) !== JSON.stringify(expected)) throw new Error('INVALID published-page inventory changed; refresh before claiming every route');
      ok('inventory: every currently published page is represented', true);
    }
    for (const page of [...inventory, { path: '/?s=massage' }, { path: '/peek-title-missing-page-verification-20261109/', status: 404 }]) {
      const { p, errors } = await open(ctx, page.path, page.status || 200); visited++;
      const name = page.path === '/' ? 'home' : page.path.replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '');
      for (const width of [390, 768, 1280]) {
        await p.setViewportSize({ width, height: 844 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
        const expected = await source(p), label = `${name}/${width}`;
        if (approved.has(page.path)) {
          const actual = await rail(p);
          ok(`${label}: approved summary page keeps its original full rail and current data`, !actual.titles && actual.ready && actual.navs === 1 && actual.data === 1 && actual.count === expected.labels.length, actual);
        } else if (legacy.has(page.path)) {
          const actual = await rail(p);
          ok(`${label}: dedicated practice rail is preserved without a second Peek rail`, !actual.titles && actual.navs === 0 && !actual.ready && actual.data === 0 && await p.locator(page.path === '/rail-nav-ideas/' ? '.tsr-lab .tsr-nav' : '.tsi-lab .tsi-nav').count() === 1, actual);
        } else {
          await exercise(p, label, expected, width, width === 390);
          if (page.path === '/privacy-policy/' && [390, 768].includes(width)) await nativeFAQ(p, expected, '#ts-client-privacy', label + '/closed-FAQ');
          if (page.path === '/progress-nav-ideas/') await progressSwitch(p, label);
        }
      }
      ok(`${name}: no JavaScript errors`, errors.length === 0, errors); await p.close();
    }
    await ctx.close();
    // No source jump row exists on these routes: turning JavaScript off must
    // leave the original page content and links intact, without invented UI.
    const nojs = await context({ javaScriptEnabled: false });
    for (const path of ['/work-with-me/', '/personal-automation/', '/?s=massage']) {
      const p = await nojs.newPage(), response = await p.goto(base + path, { waitUntil: 'load' });
      if (response?.status() !== 200) throw new Error(`INVALID no-JS ${path}: response ${response?.status()}`);
      ok(`${path}: no-JavaScript preserves original content and ordinary links`, await p.evaluate(() => !document.querySelector('.ts-pk-nav,.ts-pk-card,#ts-peek-title-strip') && !!document.querySelector('main h1,#main h1') && [...document.querySelectorAll('main a[href],#main a[href]')].some(node => node.getClientRects().length > 0)));
      await p.close();
    }
    await nojs.close();
  }
  if (fixtures) {
    const core = readFileSync(new URL('./peek-nav.php', import.meta.url), 'utf8'), css = core.match(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/)[0], js = core.match(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/)[0];
    const paragraph = '<p>This is source text for a section. The rail may show its heading but must never extract this paragraph into the preview.</p>'.repeat(7);
    const section = (id, label) => `<section><h2${id ? ` id="${id}"` : ''}>${label}</h2>${paragraph}</section>`;
    const specs = [
      { name: 'zero', html: '<h1>A short original page</h1><p>Only a short paragraph.</p>' },
      { name: 'one-idless', html: section('', 'One existing heading') },
      { name: 'headings', html: '<nav><h2>Navigation title</h2></nav><h2 hidden>Hidden heading</h2><details><summary>Closed content</summary><h2>Closed heading</h2></details>' + section('', 'First heading without an ID') + section('second', 'Second original heading') + section('third', 'Third original heading') },
      { name: 'row-details', html: '<nav class="ts-jump"><a href="#last">Last row label</a><a href="#missing">Missing</a><a href="#first">First row label</a><a href="#inside">Closed row label</a></nav>' + section('first', 'First source heading') + '<details><summary>Outer</summary><details><summary>Inner</summary>' + section('inside', 'Inside source heading') + '</details></details>' + section('last', 'Last source heading') },
      { name: 'fixed', mode: 'fixed', html: section('first', 'A search result') + section('last', 'Another search result') },
      { name: 'faq', html: '<nav class="ts-jump"><a href="#first">First</a><a href="#faq">Original FAQ label</a></nav>' + section('first', 'First') + '<div class="uagb-faq-item" id="faq"><h2><button class="uagb-faq-questions-button" onclick="this.closest(\'.uagb-faq-item\').classList.toggle(\'uagb-faq-item-active\')">Original FAQ title</button></h2><div class="answer">' + paragraph + '</div></div><style>.uagb-faq-item:not(.uagb-faq-item-active) .answer{display:none}</style>' + section('after', 'Following content') },
      { name: 'progress', html: readFileSync(new URL('./progress-ideas.html', import.meta.url), 'utf8') },
      { name: 'no-pointer', noPointer: true, html: '<nav class="ts-jump"><a href="#first">First</a><a href="#last">Last</a></nav>' + section('first', 'First') + section('last', 'Last') }
    ];
    for (const spec of specs) {
      const ctx = await context();
      if (spec.noPointer) await ctx.addInitScript(() => { delete window.PointerEvent; });
      const path = '/__peek-title-rail-' + spec.name + '/';
      await ctx.route(base + path, route => route.fulfill({ status: 200, contentType: 'text/html', body: '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font-family:system-ui}#masthead{height:85px;background:white}.entry-content{padding:0 20px}h2{font-size:1.5rem}p{line-height:1.6}</style>' + css + '</head><body><header id="masthead">Header</header><main><div class="entry-content">' + spec.html + '</div></main><footer>Original footer</footer><script id="ts-peek-nav-config" type="application/json">' + JSON.stringify({ title: 'Complete original page title', mode: spec.mode || 'sections' }) + '</script>' + js + '</body></html>' }));
      const { p, errors } = await open(ctx, path);
      if (spec.noPointer) {
        if (await p.evaluate(() => 'PointerEvent' in window)) throw new Error('INVALID no-PointerEvent fixture: API could not be removed');
        ok('local no-PointerEvent: strip and native jump links remain without a rail', await p.locator('#ts-peek-title-strip').count() === 1 && await p.locator('.ts-pk-nav').count() === 0 && await p.locator('.ts-jump').isVisible());
        await p.locator('.ts-jump a[href="#last"]').click(); await settle(p);
        ok('local no-PointerEvent: existing native link still reaches its target', await p.evaluate(() => { const r = document.getElementById('last').getBoundingClientRect(); return location.hash === '#last' && r.bottom > 0 && r.top < innerHeight; }));
      } else {
        for (const width of [390, 768, 1280]) {
          await p.setViewportSize({ width, height: 844 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
          const expected = await source(p); await exercise(p, `local-${spec.name}/${width}`, expected, width, width === 390);
          if (spec.name === 'faq' && width === 390) await nativeFAQ(p, expected, '#faq', 'local native closed-FAQ');
          if (spec.name === 'progress') await progressSwitch(p, `local-progress/${width}`);
          if (spec.name === 'one-idless' || spec.name === 'headings') ok(`local-${spec.name}/${width}: navigation never invents a heading ID`, await p.locator('section h2').first().getAttribute('id') === null);
        }
        if (spec.name === 'headings') {
          await preview(p, 1); const before = await state(p);
          await p.evaluate(() => { const last = document.getElementById('third').closest('section'); last.parentNode.prepend(last); }); await settle(p);
          let expected = await source(p), actual = await rail(p);
          ok('local dynamic reorder: controls follow source DOM order and keep the open destination/address', JSON.stringify(actual.labels) === JSON.stringify(expected.labels) && await p.locator('.ts-pk-card .open > b').textContent() === 'Second original heading' && before.url === (await state(p)).url, { actual, expected });
          await p.evaluate(() => { document.getElementById('second').hidden = true; }); await settle(p);
          expected = await source(p); actual = await rail(p);
          ok('local dynamic hidden target: hidden heading is removed without a stale preview', JSON.stringify(actual.labels) === JSON.stringify(expected.labels) && !await p.locator('.ts-pk-card.is-on .open > b').filter({ hasText: 'Second original heading' }).count(), { actual, expected });
          await p.evaluate(() => { document.getElementById('second').hidden = false; }); await settle(p);
          expected = await source(p); actual = await rail(p);
          ok('local dynamic reveal: original heading returns without duplicated controls', JSON.stringify(actual.labels) === JSON.stringify(expected.labels) && actual.count === 3, { actual, expected });
        }
        if (spec.name === 'row-details') {
          const expected = await source(p); await preview(p, 1, true); await jump(p, expected, 1, 'local curated closed native details');
        }
        if (spec.name === 'one-idless' && engine === 'chromium') {
          await controls(p).first().tap(); await settle(p);
          const before = await state(p);
          await p.locator('.ts-pk-card .hint > span').tap(); await p.waitForTimeout(150);
          ok('local native ID-less hint tap: source heading keeps focus after compatibility mouse events', await p.locator('section h2').evaluate(node => document.activeElement === node && !node.id) && before.hash === (await state(p)).hash);
        }
      }
      ok(`local-${spec.name}: no JavaScript errors`, errors.length === 0, errors); await ctx.close();
    }
  }
} catch (error) { console.log(`INVALID title-rail ${engine}: ${error.message}`); process.exitCode = 2; }
finally { await browser.close(); }
console.log(`INFO Peek title rails ${engine}: ${pass} passed, ${fail} failed; ${visited} actual routes, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s${fixturesOnly ? ', local fixtures only' : ''}; native browser input is not physical-device or assistive-technology validation.`);
process.exit(fail ? 1 : process.exitCode || 0);

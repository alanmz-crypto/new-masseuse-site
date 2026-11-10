// Immediate neighbours on the four approved summary pages. Viewport changes
// approximate changing browser chrome; they do not emulate Safari or OS scaling.
// Chromium uses trusted CDP touch. Other engines retain real keyboard coverage.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, firefox, webkit } = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if ((!local && base !== 'https://staging39.thaisolitude.com') || flags.length > 1 || flags.some(flag => !['--firefox', '--webkit'].includes(flag))) throw new Error('Exact staging39 or isolated localhost:8090/8091 only; optional --firefox or --webkit');
const engine = flags.includes('--firefox') ? 'firefox' : flags.includes('--webkit') ? 'webkit' : 'chromium';
const slugs = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'];
const manifest = JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8'));
if (slugs.some(slug => !manifest.pages.some(page => page.slug === slug && page.staging === 'enabled'))) throw new Error('Four approved summary pages must be enabled');
let passed = 0, failed = 0, completed = 0, visits = 0, captures = 0, invalid = false, browser;
const started = Date.now();
const ok = (label, value, detail) => {
  value ? passed++ : failed++;
  console.log(`${value ? 'PASS' : 'FAIL'} neighbours ${engine}/${label} [check ${passed + failed}]${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const settle = async p => { await p.waitForTimeout(100); await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); };
const state = p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), sum = card?.querySelector('.sum');
  return { x: scrollX, y: scrollY, hash: location.hash, history: history.length, index: Number(card?.dataset.i),
    pinned: !!card?.classList.contains('is-pinned') && card.classList.contains('is-on'), left: document.documentElement.classList.contains('ts-pk-left'), outer: card?.scrollTop, inner: sum?.scrollTop,
    max: sum ? sum.scrollHeight - sum.clientHeight : null, focusSummary: document.activeElement === sum,
    focusOpen: document.activeElement === card?.querySelector('.open'),
    railFocus: document.activeElement?.closest('.ts-pk-rail a')?.getAttribute('href') || null,
    sameSummary: !!sum && window.__peekNeighbourSummary === sum };
});
const samePage = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.hash === b.hash && a.history === b.history;
const samePreview = (a, b) => samePage(a, b) && b.pinned && a.index === b.index;
const geometry = p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), open = card.querySelector('.open'), sum = card.querySelector('.sum'), v = visualViewport;
  const viewport = { left: v?.offsetLeft || 0, top: v?.offsetTop || 0, width: v?.width || innerWidth, height: v?.height || innerHeight, scale: v?.scale || 1 };
  viewport.right = viewport.left + viewport.width; viewport.bottom = viewport.top + viewport.height;
  const rect = node => { const r = node.getBoundingClientRect(); return { left: r.left, top: r.top, right: r.right, bottom: r.bottom, width: r.width, height: r.height }; };
  const opacity = node => { let value = 1; for (let at = node; at instanceof Element; at = at.parentElement) value *= Number(getComputedStyle(at).opacity); return value; };
  const cr = rect(card), clip = { left: Math.max(cr.left + card.clientLeft, viewport.left), right: Math.min(cr.left + card.clientLeft + card.clientWidth, viewport.right), top: Math.max(cr.top + card.clientTop, viewport.top), bottom: Math.min(cr.top + card.clientTop + card.clientHeight, viewport.bottom) };
  const measure = node => {
    const r = rect(node), visible = { left: Math.max(r.left, clip.left), right: Math.min(r.right, clip.right), top: Math.max(r.top, clip.top), bottom: Math.min(r.bottom, clip.bottom) };
    visible.width = Math.max(0, visible.right - visible.left); visible.height = Math.max(0, visible.bottom - visible.top);
    const x = (visible.left + visible.right) / 2, y = (visible.top + visible.bottom) / 2;
    const hit = visible.width > 2 && visible.height > 2 && [[x, y], [visible.left + 2, y], [visible.right - 2, y], [x, visible.top + 2], [x, visible.bottom - 2]].every(([px, py]) => node.contains(document.elementFromPoint(px, py)));
    return { ...r, visible, hit, opacity: opacity(node), display: getComputedStyle(node).display, visibility: getComputedStyle(node).visibility };
  };
  const rows = [...card.querySelectorAll('.row')].map(row => ({ index: Number(row.dataset.k), ...measure(row) }));
  const title = rect(open.querySelector('b')), hint = rect(open.querySelector('.hint')), strip = rect(document.querySelector('.ts-pk-top')), head = document.getElementById('masthead'), header = head ? rect(head) : null;
  const headBottom = header && header.bottom > viewport.top && header.top < viewport.bottom ? Math.min(viewport.bottom, header.bottom) : viewport.top;
  const stripVisible = strip.top < viewport.bottom && strip.bottom > viewport.top;
  const intervals = viewport.height <= 500 || !stripVisible ? [viewport.bottom - headBottom] : [strip.top - headBottom, viewport.bottom - Math.max(headBottom, strip.bottom)];
  const available = Math.max(0, ...intervals) - 20, rowHeight = rows.reduce((total, row) => total + row.height, 0);
  const readingBudget = { available, title: title.height, hint: hint.height, neighbours: rowHeight, fixedAllowance: 48,
    expected: Math.min(120, sum.scrollHeight, Math.max(0, available - title.height - hint.height - rowHeight - 48)) };
  return { viewport, card: cr, cardOpacity: opacity(card), isOn: card.classList.contains('is-on'), clip, open: rect(open), title, hint, strip, header, readingBudget, rail: rect(document.querySelector('.ts-pk-nav')), left: document.documentElement.classList.contains('ts-pk-left'), outer: card.scrollTop, outerMax: card.scrollHeight - card.clientHeight,
    summary: sum ? { ...measure(sum), scrollTop: sum.scrollTop, max: sum.scrollHeight - sum.clientHeight, total: sum.scrollHeight } : null,
    rows,
    folds: card.querySelectorAll('.more').length, titleOnly: !!card.closest('.ts-pk-titles') };
});
const expectedRows = (index, length) => [index - 1, index + 1].filter(i => i >= 0 && i < length);
const checkNeighbours = async (p, label, index, length) => {
  const g = await geometry(p), expected = expectedRows(index, length), viewport = p.viewportSize();
  if (Math.abs(g.viewport.width - viewport.width) > 1 || Math.abs(g.viewport.height - viewport.height) > 1 || Math.abs(g.viewport.scale - 1) > .001 || Math.abs(g.viewport.left) > 1 || Math.abs(g.viewport.top) > 1) throw new Error(`${label}: viewport scale/origin differs: ${JSON.stringify(g.viewport)}`);
  const left = label.split('/')[1] === 'left', midpoint = g.viewport.left + g.viewport.width / 2;
  ok(`${label}: requested side is applied to the actual rail and card`, g.left === left && (left ? g.rail.right < midpoint && g.card.left >= g.rail.right - 1 : g.rail.left > midpoint && g.card.right <= g.rail.left + 1), g);
  ok(`${label}: only immediate available neighbours, with no fold labels`, !g.titleOnly && g.folds === 0 && JSON.stringify(g.rows.map(row => row.index)) === JSON.stringify(expected), g);
  ok(`${label}: every neighbour is fully visible and hit-testable`, g.isOn && g.cardOpacity > 0 && g.rows.length === expected.length && g.rows.every(row => row.opacity > 0 && row.display !== 'none' && row.visibility === 'visible' && row.height >= 24 && row.visible.height >= row.height - 1 && row.visible.width >= row.width - 1 && row.hit), g);
  ok(`${label}: previous is above and next below the open box`, g.rows.every(row => row.index < index ? row.bottom <= g.open.top + 1 : row.top >= g.open.bottom - 1), g);
  ok(`${label}: card stays inside the real visible viewport`, g.card.top >= g.viewport.top - 1 && g.card.bottom <= g.viewport.bottom + 1 && g.card.left >= g.viewport.left - 1 && g.card.right <= g.viewport.right + 1, g);
  console.log(`INFO neighbours geometry ${engine}/${label}: ${JSON.stringify(g)}`);
  return g;
};
const point = async (p, selector) => {
  const at = await p.locator(selector).evaluate(node => {
    const r = node.getBoundingClientRect(), v = visualViewport;
    const x = r.left + r.width / 2, y = r.top + r.height / 2, left = v?.offsetLeft || 0, top = v?.offsetTop || 0;
    if (r.width < 2 || r.height < 2 || x < left || y < top || x >= left + (v?.width || innerWidth) || y >= top + (v?.height || innerHeight) || !node.contains(document.elementFromPoint(x, y))) throw new Error('Touch target is clipped or not hit-testable: ' + node.className);
    return { x: x - left, y: y - top };
  });
  return at;
};
const capture = async (p, slug, side, kind) => {
  const file = `peek-neighbours-${engine}-${slug}-${side}-${kind}.jpg`;
  console.log(`INFO neighbours capture ${file}: ${JSON.stringify({ state: await state(p), geometry: await geometry(p) })}`);
  console.log(`INLINE ${file} base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`); captures++;
};
try {
  browser = await ({ chromium, firefox, webkit }[engine]).launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  for (const slug of slugs) for (const side of ['right', 'left']) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 1, isMobile: engine !== 'firefox', hasTouch: true, reducedMotion: 'reduce', colorScheme: side === 'left' ? 'dark' : 'light' });
    try {
      await context.addInitScript(side => { localStorage.setItem('ts-pk-side', side); localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-theme', side === 'left' ? 'dark' : 'light'); window.__peekNeighbourTouches = []; for (const type of ['touchstart', 'touchend']) addEventListener(type, e => { if (e.isTrusted) window.__peekNeighbourTouches.push(type); }, true); }, side);
      const p = await context.newPage(), errors = [], label = `${slug}/${side}`;
      p.on('pageerror', error => errors.push(error.message));
      const response = await p.goto(`${base}/${slug}/`, { waitUntil: 'load', timeout: 45000 });
      if (response?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`${label}: expected same-origin HTTP200, got ${response?.status()}; stop without further loads`);
      visits++;
      await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(p);
      const original = await p.evaluate(() => ({ data: document.getElementById('ts-peek-nav-data')?.textContent, links: [...document.querySelectorAll('.ts-jump a')].map(a => ({ href: a.getAttribute('href'), label: a.textContent.trim() })) }));
      const data = JSON.parse(original.data || 'null'), expected = manifest.pages.find(page => page.slug === slug);
      if (data?.slug !== slug || data.approval?.status !== 'approved' || JSON.stringify(original.links.map(a => a.label)) !== JSON.stringify(expected.expectedLabels)) throw new Error(`${label}: approved summaries or original destination order differs`);
      const count = original.links.length, middle = Math.floor(count / 2), research = original.links.findIndex(a => /research/i.test(a.label));
      if (count < 3 || research < 0) throw new Error(`${label}: edge/middle/research test destinations missing`);
      const theme = await p.locator('html').getAttribute('data-ts-theme');
      ok(`${label}: requested real theme preference is active`, theme === (side === 'left' ? 'dark' : 'light'), theme);
      const cdp = engine === 'chromium' ? await context.newCDPSession(p) : null;
      const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
      const tap = async selector => { const at = await point(p, selector); await touch('touchStart', at); await p.waitForTimeout(35); await touch('touchEnd'); await settle(p); };
      const keyActivate = async (selector, key = 'Enter') => { await p.locator(selector).focus(); await p.keyboard.press(key); await settle(p); };
      const rail = index => `.ts-pk-rail li:nth-child(${index + 1}) a`;
      const open = async (index, keyboard = false) => {
        await p.keyboard.press('Escape'); await settle(p);
        const before = await state(p);
        if (cdp && !keyboard) await tap(rail(index)); else await keyActivate(rail(index));
        const after = await state(p);
        ok(`${label}: rail opens section ${index + 1} without moving the page`, samePage(before, after) && after.pinned && after.index === index, { before, after });
        if (!after.pinned || after.index !== index) throw new Error(`${label}: requested preview unavailable`);
      };
      const resize = async (width, height) => {
        await p.setViewportSize({ width, height }); await settle(p);
        const v = (await geometry(p)).viewport;
        if (Math.abs(v.width - width) > 1 || Math.abs(v.height - height) > 1 || Math.abs(v.scale - 1) > .001 || Math.abs(v.left) > 1 || Math.abs(v.top) > 1) throw new Error(`${label}: viewport scale/origin differs: ${JSON.stringify(v)}`);
      };
      // Actual viewport resizing, never an injected visualViewport or CSS shim.
      for (const [width, height] of [[390, 844], [390, 620], [390, 500], [844, 342]]) {
        await p.keyboard.press('Escape');
        await p.setViewportSize({ width, height });
        await p.evaluate(() => scrollTo({ top: 600, behavior: 'instant' })); await settle(p);
        for (const index of [0, middle, count - 1]) {
          await open(index); await checkNeighbours(p, `${label}/${width}x${height}/section-${index + 1}`, index, count);
        }
      }
      await open(research); await checkNeighbours(p, `${label}/research-landscape`, research, count);
      await capture(p, slug, side, 'research-landscape');
      // The visible availability banner/page top is a different fit boundary
      // from a scrolled page. Keep both neighbours accessible in that state too.
      for (const height of [620, 500]) {
        await p.keyboard.press('Escape'); await p.setViewportSize({ width: 390, height });
        await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
        for (const index of [middle, research]) {
          await open(index);
          const topGeometry = await checkNeighbours(p, `${label}/page-top-390x${height}/section-${index + 1}`, index, count);
          ok(`${label}/page-top-${height}/section-${index + 1}: summary keeps useful visible reading space`, topGeometry.summary.hit && topGeometry.summary.opacity > 0 && topGeometry.summary.visible.height >= Math.max(Math.min(60, topGeometry.summary.total), topGeometry.readingBudget.expected) - 1, topGeometry);
          if (height === 500 && index === middle) await capture(p, slug, side, 'middle-short');
        }
      }
      await p.keyboard.press('Escape');
      await p.evaluate(() => scrollTo({ top: 600, behavior: 'instant' })); await settle(p);
      const pageRange = await p.evaluate(() => ({ above: scrollY, below: document.documentElement.scrollHeight - innerHeight - scrollY }));
      if (pageRange.above < 100 || pageRange.below < 100) throw new Error(`${label}: neighbour ownership needs real page scroll range in both directions`);
      await open(middle);
      // Both actual neighbour controls own the gesture/activation. A distant
      // section is selected via the rail, never by inventing a hidden row.
      for (const index of expectedRows(middle, count)) {
        await open(middle);
        const selector = `.ts-pk-card .row[data-k="${index}"]`, before = await state(p);
        if (cdp) {
          const at = await point(p, selector), down = index < middle;
          await touch('touchStart', at);
          for (let n = 1; n <= 8; n++) { await touch('touchMove', { x: at.x, y: at.y + (down ? 1 : -1) * n * 9 }); await p.waitForTimeout(24); }
          await p.waitForTimeout(100); await touch('touchEnd'); await p.waitForTimeout(260);
          const after = await state(p);
          ok(`${label}: swipe on neighbour ${index + 1} moves no page or reading surface`, samePreview(before, after) && Math.abs(after.outer - before.outer) <= 1 && Math.abs(after.inner - before.inner) <= 1, { before, after });
          await p.locator('.ts-pk-card').focus();
          const beforeTap = await state(p); await tap(selector);
          const afterTap = await state(p);
          ok(`${label}: native neighbour ${index + 1} tap opens only its own preview`, samePage(beforeTap, afterTap) && afterTap.pinned && afterTap.index === index && afterTap.focusOpen, { beforeTap, afterTap });
        }
        // Keyboard coverage remains in every engine, including touch Chromium.
        // This is an independent keyboard path, outside the existing touch
        // compatibility-click reservation; no immediate mixed-input claim.
        await p.waitForTimeout(750); await open(middle, true);
        const beforeKeyboard = await state(p);
        await keyActivate(selector, index < middle ? 'Enter' : 'Space');
        const after = await state(p);
        ok(`${label}: keyboard neighbour ${index + 1} opens only its preview and retains focus`, samePage(beforeKeyboard, after) && after.pinned && after.index === index && after.focusOpen, { beforeKeyboard, after });
        await checkNeighbours(p, `${label}/keyboard-section-${index + 1}`, index, count);
        await p.keyboard.press('Escape'); await settle(p);
        const closed = await state(p);
        ok(`${label}: Escape returns focus to the selected original rail link`, samePage(after, closed) && !closed.pinned && closed.railFocus === original.links[index].href, { after, closed });
      }
      await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
      await open(research);
      await p.locator('.ts-pk-card .sum').focus();
      await p.evaluate(() => { window.__peekNeighbourSummary = document.querySelector('.ts-pk-card .sum'); });
      let g = await geometry(p);
      ok(`${label}: short-portrait research has a usable reading area`, g.summary.hit && g.summary.visible.height >= Math.min(120, g.summary.total) - 1, g);
      // Fitting text is valid. Use a genuinely smaller viewport for a shorter
      // research list, without adding content or forcing a CSS scroll range.
      const readingHeight = g.summary.max <= 10 ? 420 : 500;
      if (readingHeight !== 500) { await resize(390, readingHeight); g = await checkNeighbours(p, `${label}/reading-390x${readingHeight}`, research, count); }
      console.log(`INFO neighbours reading surface ${engine}/${label}: ${JSON.stringify({ height: readingHeight, geometry: g })}`);
      if (!g.summary.hit || g.summary.visible.height < 80 || g.summary.max <= 10) throw new Error(`${label}: meaningful research scrolling cannot be exercised`);
      const swipeSummary = async direction => {
        const path = await p.locator('.ts-pk-card .sum').evaluate((sum, direction) => {
          const r = sum.getBoundingClientRect(), card = sum.closest('.ts-pk-card'), c = card.getBoundingClientRect(), v = visualViewport;
          const vx = v?.offsetLeft || 0, vy = v?.offsetTop || 0;
          const top = Math.max(r.top, c.top + card.clientTop, vy), bottom = Math.min(r.bottom, c.top + card.clientTop + card.clientHeight, vy + (v?.height || innerHeight));
          const left = Math.max(r.left, c.left + card.clientLeft, vx), right = Math.min(r.right, c.left + card.clientLeft + card.clientWidth, vx + (v?.width || innerWidth));
          const x = (left + right) / 2, low = bottom - 4, high = top + 4;
          const start = { x, y: direction > 0 ? low : high }, end = { x, y: direction > 0 ? high : low };
          const hit = [start, { x, y: (start.y + end.y) / 2 }, end].every(at => sum.contains(document.elementFromPoint(at.x, at.y)));
          return { start: { x: start.x - vx, y: start.y - vy }, end: { x: end.x - vx, y: end.y - vy }, height: bottom - top, width: right - left, hit };
        }, direction);
        if (!path.hit || path.height < 32 || path.width < 100 || Math.abs(path.end.y - path.start.y) < 24) throw new Error(`${label}: actual summary swipe path is not meaningfully visible/hit-testable: ${JSON.stringify(path)}`);
        const { start, end } = path, x = start.x;
        await touch('touchStart', start);
        for (let n = 1; n <= 8; n++) { await touch('touchMove', { x, y: start.y + (end.y - start.y) * n / 8 }); await p.waitForTimeout(24); }
        await p.waitForTimeout(100); await touch('touchEnd'); await p.waitForTimeout(260);
      };
      let before = await state(p);
      if (cdp) await swipeSummary(1); else { await p.keyboard.press('ArrowDown'); await p.keyboard.press('ArrowDown'); await p.waitForTimeout(260); }
      let after = await state(p);
      ok(`${label}: ${cdp ? 'native swipe' : 'keyboard scrolling'} moves only the research summary`, samePreview(before, after) && after.inner > before.inner + 5 && Math.abs(after.outer - before.outer) <= 1 && after.sameSummary, { before, after });
      // Scrolling input was verified above. Seed an interior reading position
      // precisely: a keyboard line can exceed a short summary's entire range.
      await p.locator('.ts-pk-card .sum').evaluate(sum => { sum.scrollTop = Math.min(40, (sum.scrollHeight - sum.clientHeight) / 2); });
      await settle(p);
      before = await state(p);
      ok(`${label}: research bookmark is inside scrollable content`, before.inner > 0 && before.inner < before.max - 2, before);
      const originalBookmark = before.inner;
      for (const height of [620, readingHeight, 844, readingHeight]) {
        const prior = await state(p); await resize(390, height); after = await state(p);
        ok(`${label}: resize to ${height} preserves summary node, focus and original bookmark`, samePreview(prior, after) && after.sameSummary && after.focusSummary && Math.abs(after.inner - Math.min(originalBookmark, after.max)) <= 2, { originalBookmark, prior, after });
        await checkNeighbours(p, `${label}/bookmark-${height}`, research, count);
      }
      // An actual new reader movement replaces saved intent after a temporary
      // clamp. Home must move the content; a no-movement key is not this case.
      await resize(390, 844); before = await state(p);
      if (before.inner <= 0) { await resize(390, 620); before = await state(p); }
      if (before.inner <= 0) { await resize(390, readingHeight); before = await state(p); }
      console.log(`INFO neighbours reader replacement ${engine}/${label}: ${JSON.stringify({ originalBookmark, temporarilyClamped: before.max < originalBookmark, before })}`);
      if (before.inner <= 0) throw new Error(`${label}: actual Home movement cannot replace the bookmark`);
      await p.keyboard.press('Home'); await p.waitForTimeout(260); after = await state(p);
      ok(`${label}: keyboard Home replaces the prior bookmark with the start`, samePreview(before, after) && after.sameSummary && after.focusSummary && after.inner <= 1, { before, after });
      before = after; await resize(390, readingHeight); after = await state(p);
      ok(`${label}: resize preserves the new reader-selected start`, samePreview(before, after) && after.sameSummary && after.focusSummary && after.inner <= 1, { before, after });
      // End now visibly moves from the start, so this is deliberate endpoint
      // movement rather than a no-op key at a temporarily clamped endpoint.
      await p.keyboard.press('End'); await p.waitForTimeout(260); before = await state(p);
      ok(`${label}: keyboard End reaches the research endpoint`, before.max > 0 && before.max - before.inner <= 2, before);
      for (const height of [620, readingHeight, 844, readingHeight]) {
        const prior = await state(p); await resize(390, height); after = await state(p);
        ok(`${label}: resize to ${height} preserves the endpoint and focus`, samePreview(prior, after) && after.sameSummary && after.focusSummary && after.max - after.inner <= 2, { prior, after });
      }
      if (cdp) {
        before = await state(p); await swipeSummary(1); after = await state(p);
        ok(`${label}: research endpoint swipe never reaches the page behind`, samePreview(before, after) && after.max - after.inner <= 2 && Math.abs(after.outer - before.outer) <= 1, { before, after });
        await resize(390, 844);
        await p.keyboard.press('Escape');
        await p.evaluate(() => scrollTo({ top: 600, behavior: 'instant' })); await settle(p);
        const lengths = original.links.map(link => data.items.filter(item => item[1] === link.href.slice(1)).reduce((sum, item) => sum + item[0].length, 0));
        const short = lengths.indexOf(Math.min(...lengths)); await open(short); g = await geometry(p);
        ok(`${label}: short summary has no internal scroll range`, g.summary.max <= 1 && g.summary.hit, g);
        if (g.summary.max > 1 || !g.summary.hit) throw new Error(`${label}: non-scrollable summary coverage unavailable`);
        before = await state(p); await swipeSummary(1); after = await state(p);
        ok(`${label}: non-scrollable summary swipe never pans the page`, samePreview(before, after) && Math.abs(after.inner - before.inner) <= 1 && Math.abs(after.outer - before.outer) <= 1, { before, after });
        before = await state(p); await swipeSummary(-1); after = await state(p);
        ok(`${label}: reverse non-scrollable summary swipe never pans the page`, samePreview(before, after) && Math.abs(after.inner - before.inner) <= 1 && Math.abs(after.outer - before.outer) <= 1, { before, after });
        const trusted = await p.evaluate(() => window.__peekNeighbourTouches);
        ok(`${label}: browser recorded trusted native touch start/end events`, trusted.includes('touchstart') && trusted.includes('touchend'), trusted);
      }
      const unchanged = await p.evaluate(original => document.getElementById('ts-peek-nav-data')?.textContent === original.data && JSON.stringify([...document.querySelectorAll('.ts-jump a')].map(a => ({ href: a.getAttribute('href'), label: a.textContent.trim() }))) === JSON.stringify(original.links), original);
      ok(`${label}: approved data and original jump destinations remain unchanged`, unchanged);
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors);
      completed++;
    } finally { await context.close(); }
    if (!local) await new Promise(resolve => setTimeout(resolve, 5500));
  }
} catch (error) { invalid = true; console.log(`INVALID Peek neighbours ${engine}: ${error.message}`); }
finally { if (browser) await browser.close(); }
if ((completed !== 8 || visits !== 8 || captures !== 16) && !invalid) { invalid = true; console.log(`INVALID Peek neighbours ${engine}: incomplete page/side/load/capture matrix`); }
console.log(`INFO Peek neighbours ${engine}: ${passed} passed, ${failed} failed; ${completed}/8 completed cases, ${visits} page loads, ${captures} captures; ${Math.round((Date.now() - started) / 1000)}s; ${engine === 'chromium' ? 'trusted CDP touch and keyboard' : 'keyboard only'}; viewport resizing is not Safari, physical-device or OS text-scale validation${invalid ? '; INCOMPLETE/INVALID' : ''}.`);
process.exit(invalid ? 2 : failed ? 1 : 0);

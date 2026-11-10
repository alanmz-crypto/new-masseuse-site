// Actual-page Kiro regressions: useful reading space, complete control hit areas,
// visible text clearance and title-strip identity. No runtime or data injection.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['http://localhost:8090', 'http://localhost:8091', 'https://staging39.thaisolitude.com'].includes(base)) throw new Error('staging39 or isolated localhost only');
const flags = process.argv.slice(3);
if (flags.length > 1 || flags.some(flag => !['--firefox', '--webkit'].includes(flag))) throw new Error('expected at most one of --firefox or --webkit');
const engine = flags.includes('--webkit') ? 'webkit' : flags.includes('--firefox') ? 'firefox' : 'chromium';
const data = JSON.parse(readFileSync(new URL('./peek-data/therapeutic-massage.json', import.meta.url), 'utf8'));
const started = Date.now();
let pass = 0, fail = 0, captures = 0, visits = 0, browser;
const routes = new Set();
const ok = (name, condition, detail) => {
  condition ? pass++ : fail++;
  console.log(`${condition ? 'PASS' : 'FAIL'} kiro-layout ${engine}/${name}${!condition && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const settle = async p => { await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); };
const state = p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), sum = card?.querySelector('.sum');
  return { x: scrollX, y: scrollY, hash: location.hash, history: history.length, pinned: card?.classList.contains('is-pinned'), index: card?.dataset.i, outer: card?.scrollTop, inner: sum?.scrollTop, max: sum ? sum.scrollHeight - sum.clientHeight : null };
});
const samePage = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.hash === b.hash && a.history === b.history;
const samePreview = (a, b) => samePage(a, b) && b.pinned && a.index === b.index;
// A short screen may intentionally defer idle navigation. Reach it by actual
// page input, then carry the same open preview back for the original top checks.
const pageWheel = async (p, delta) => {
  if (engine === 'webkit') {
    // Playwright rejects mouse.wheel in mobile WebKit. This engine's retained
    // contract is keyboard-only: give trusted PageDown to the page itself.
    const body = await p.evaluate(() => {
      const body = document.body, original = body.getAttribute('tabindex');
      document.activeElement?.blur();
      const changed = document.activeElement !== body;
      if (changed) { body.setAttribute('tabindex', '-1'); body.focus({ preventScroll: true }); }
      return { original, changed };
    });
    try { await p.keyboard.press('PageDown'); }
    finally {
      if (body.changed) await p.evaluate(original => {
        if (original === null) document.body.removeAttribute('tabindex'); else document.body.setAttribute('tabindex', original);
      }, body.original);
    }
    await p.waitForTimeout(180); await settle(p); return;
  }
  const at = await p.evaluate(() => {
    const v = visualViewport, left = v?.offsetLeft || 0, top = v?.offsetTop || 0;
    const width = v?.width || innerWidth, height = v?.height || innerHeight;
    for (const [x, y] of [[left + width / 2, top + 4], [left + 3, top + height / 2], [left + width - 3, top + height / 2], [left + width / 2, top + height - 4]]) {
      const hit = document.elementFromPoint(x, y);
      if (hit && !hit.closest('.ts-pk')) return { x: x - left, y: y - top };
    }
    throw new Error('No real page surface is available for the setup wheel input');
  });
  await p.mouse.move(at.x, at.y); await p.mouse.wheel(0, delta);
  await p.waitForTimeout(180); await settle(p);
};
const deferredReadingSpace = async (p, label) => {
  const deferred = await p.locator('.ts-pk-nav').evaluate(nav => {
    const points = [...document.querySelectorAll('#masthead,.ts-corp-band,.ts-pk-top')].flatMap(node => {
      const r = node.getBoundingClientRect();
      return [0.1, 0.5, 0.9].flatMap(fraction => {
        const x = r.left + r.width * fraction, y = Math.max(0, r.top) + (Math.min(innerHeight, r.bottom) - Math.max(0, r.top)) / 2;
        return r.bottom > 0 && r.top < innerHeight && x >= 0 && x < innerWidth ? [{ x, y, blocked: nav.contains(document.elementFromPoint(x, y)) }] : [];
      });
    });
    return { waiting: nav.classList.contains('is-waiting'), hidden: getComputedStyle(nav).visibility === 'hidden', inert: nav.inert, aria: nav.getAttribute('aria-hidden'), points };
  });
  if (!deferred.waiting) return false;
  const valid = deferred.hidden && deferred.inert && deferred.aria === 'true' && deferred.points.length > 0 && deferred.points.every(at => !at.blocked);
  ok(`${label}: intentionally deferred navigation is hidden, inert and does not intercept visible page chrome`, valid, deferred);
  if (!valid) throw new Error(`${label}: deferred navigation is not safely unavailable`);
  console.log(`INFO deferred navigation setup ${engine}/${label}: ${engine === 'webkit' ? 'trusted keyboard page input (PageDown); mobile WebKit has no wheel API' : 'trusted wheel input on the page outside Peek'}`);
  for (let attempt = 0; attempt < 4; attempt++) {
    await pageWheel(p, Math.max(240, p.viewportSize().height));
    if (await p.locator('.ts-pk-nav').evaluate(nav => !nav.inert && getComputedStyle(nav).visibility === 'visible')) return true;
  }
  throw new Error(`${label}: real page scrolling did not restore usable navigation`);
};
const restoreReadingPosition = async (p, wanted) => {
  if (engine === 'webkit') {
    // Keep the now-open preview's focus/selection. This is the original
    // geometry setup scroll, after a real keyboard path reached the rail.
    await p.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), wanted); await settle(p);
    if (Math.abs(await p.evaluate(() => scrollY) - wanted) > 1) throw new Error(`Preview setup did not restore the intended reading position ${wanted}`);
    return;
  }
  for (let attempt = 0; attempt < 4; attempt++) {
    const y = await p.evaluate(() => scrollY);
    if (Math.abs(y - wanted) <= 1) return;
    await pageWheel(p, wanted - y);
  }
  throw new Error(`Real page input did not restore the intended reading position ${wanted}`);
};
const capture = async (p, label) => {
  if (engine !== 'chromium' && engine !== 'webkit') return;
  captures++;
  console.log(`INLINE kiro-layout-${engine === 'webkit' ? 'webkit-' : ''}${label}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 80 })).toString('base64')}`);
};
const open = async (path, status = 200, options = {}) => {
  // Ration public HTML loads. Local rehearsal has no hosting challenge budget.
  if (visits && base.startsWith('https:')) await new Promise(resolve => setTimeout(resolve, 5500));
  const viewport = options.viewport || { width: 390, height: 664 };
  const ctx = await browser.newContext({ viewport, screen: viewport, deviceScaleFactor: 1, hasTouch: true, ...(engine === 'chromium' || engine === 'webkit' ? { isMobile: options.mobile !== false } : {}), reducedMotion: 'reduce' });
  await ctx.addInitScript(hand => { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', hand); }, options.side || 'right');
  const p = await ctx.newPage(), errors = [];
  p.on('pageerror', error => errors.push(error.message));
  const response = await p.goto(base + path, { waitUntil: 'load', timeout: 45000 });
  if (!response || response.status() !== status || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${path}: expected same-origin ${status}, got ${response?.status()}; stop further loads`);
  visits++; routes.add(path);
  await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { state: 'attached', timeout: 10000 });
  await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 });
  await settle(p);
  return { ctx, p, errors };
};
const point = (p, selector) => p.locator(selector).evaluate(node => {
  const r = node.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
  if (r.width < 1 || r.height < 1 || !node.contains(document.elementFromPoint(x, y))) throw new Error(`Input target is not visible/hit-testable: ${node.className}`);
  return { x, y };
});
const tap = async (p, selector) => {
  if (engine === 'chromium') {
    const cdp = await p.context().newCDPSession(p), at = await point(p, selector);
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ ...at, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
    await p.waitForTimeout(35);
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
    await cdp.detach();
  } else if (engine === 'webkit') {
    const at = await point(p, selector);
    await p.touchscreen.tap(at.x, at.y);
  } else await p.locator(selector).click();
  await p.waitForTimeout(100); await settle(p);
};
const close = async p => { await p.keyboard.press('Escape'); await settle(p); };
const viewportDiagnostics = p => p.evaluate(() => {
  const root = document.documentElement, visual = window.visualViewport;
  const visible = { left: visual?.offsetLeft || 0, top: visual?.offsetTop || 0, width: visual?.width || root.clientWidth, height: visual?.height || root.clientHeight, scale: visual?.scale || 1 };
  const overflowing = [...document.body.querySelectorAll('*')].flatMap(node => {
    const r = node.getBoundingClientRect(), style = getComputedStyle(node);
    if (r.width < 2 || r.height < 2 || style.display === 'none' || style.visibility !== 'visible' || node.closest('script,style,noscript,[hidden],[aria-hidden="true"]')) return [];
    const over = Math.max(visible.left - r.left, r.right - visible.left - visible.width);
    if (over <= 1) return [];
    return [{ tag: node.tagName, id: node.id, class: typeof node.className === 'string' ? node.className.slice(0, 180) : '', peek: !!node.closest('.ts-pk'), left: r.left, right: r.right, width: r.width, top: r.top, overflow: over, ownScrollWidth: node.scrollWidth, text: node.childElementCount ? '' : node.textContent.trim().slice(0, 100) }];
  }).sort((a, b) => b.overflow - a.overflow).slice(0, 20);
  return { innerWidth, innerHeight, clientWidth: root.clientWidth, clientHeight: root.clientHeight, scrollWidth: root.scrollWidth, scrollHeight: root.scrollHeight,
    bodyWidth: document.body.getBoundingClientRect().width, bodyScrollWidth: document.body.scrollWidth, x: scrollX, y: scrollY, viewport: visible, rootFont: getComputedStyle(root).fontSize, overflowing };
});
const stableLayout = async (p, label) => {
  await p.evaluate(() => { window.__kiroLayoutStable = null; });
  await p.waitForFunction(() => {
    const rect = selector => { const node = document.querySelector(selector); if (!node) return null; const r = node.getBoundingClientRect(); return [r.left, r.top, r.width, r.height]; };
    const metrics = [scrollX, scrollY, innerWidth, innerHeight, document.documentElement.scrollWidth, document.documentElement.scrollHeight,
      window.visualViewport?.width, window.visualViewport?.height, window.visualViewport?.scale, rect('#masthead'), rect('.ts-pk-top')];
    const key = JSON.stringify(metrics), now = performance.now(), previous = window.__kiroLayoutStable;
    if (!previous || previous.key !== key) window.__kiroLayoutStable = { key, since: now, started: previous?.started || now, changes: (previous?.changes || 0) + 1, first: previous?.first || metrics, last: metrics };
    return document.fonts.status === 'loaded' && now - window.__kiroLayoutStable.since >= 250;
  }, null, { polling: 'raf', timeout: 4000 }).catch(async () => { throw new Error(`INVALID ${label}: layout did not stabilize before input: ${JSON.stringify(await viewportDiagnostics(p))}`); });
  const evidence = await p.evaluate(() => window.__kiroLayoutStable);
  if (evidence.changes > 1) console.log(`INFO kiro-layout ${engine}/${label}: settled responsive layout before input ${JSON.stringify(evidence)}`);
};
const side = async (p, wanted) => {
  if (await p.locator('html').evaluate(root => root.classList.contains('ts-pk-left')) === (wanted === 'left')) return;
  await close(p); await p.setViewportSize({ width: 390, height: 844 });
  await p.evaluate(() => { document.documentElement.style.fontSize = ''; scrollTo({ top: 600, behavior: 'instant' }); }); await settle(p);
  await tap(p, '.ts-pk-rail li:first-child > [data-ts-pk-section]');
  await tap(p, '.ts-pk-card .side');
  if (await p.locator('html').evaluate(root => root.classList.contains('ts-pk-left')) !== (wanted === 'left')) throw new Error(`INVALID side switch to ${wanted} failed`);
};
const scroll = async (p, y) => { await p.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), y); await p.waitForTimeout(100); await settle(p); };
const summaryGeometry = p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum'), title = card.querySelector('.open > b'), hint = card.querySelector('.hint'), strip = document.querySelector('.ts-pk-top'), head = document.getElementById('masthead');
  const rect = node => { const r = node.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, width: r.width, height: r.height }; };
  const c = rect(card), s = rect(sum), t = rect(title), j = rect(hint), bar = rect(strip), h = head ? rect(head) : null;
  const v = window.visualViewport, viewport = { width: v?.width || document.documentElement.clientWidth, height: v?.height || document.documentElement.clientHeight, left: v?.offsetLeft || 0, top: v?.offsetTop || 0, scale: v?.scale || 1 };
  const bottom = viewport.top + viewport.height, right = viewport.left + viewport.width;
  const headBottom = h && h.bottom > viewport.top && h.top < bottom ? Math.min(bottom, h.bottom) : viewport.top;
  // The accepted short-screen preview can temporarily cover the read-only strip,
  // never the site header. Taller screens retain both clearances. This uses
  // visible geometry, not fit classes or the implementation's sizing variable.
  const short = viewport.height <= 500, stripVisible = bar.top < bottom && bar.bottom > viewport.top;
  const intervals = short || !stripVisible ? [bottom - headBottom] : [bar.top - headBottom, bottom - Math.max(headBottom, bar.bottom)];
  const usable = Math.max(0, ...intervals) - 20;
  const expected = Math.min(120, Math.max(0, usable - t.height - j.height - 48));
  const clearStrip = !stripVisible || c.bottom <= bar.top + 1 || c.top >= bar.bottom - 1;
  const visible = { left: Math.max(viewport.left, c.left, s.left), right: Math.min(right, c.right, s.right), top: Math.max(headBottom, c.top, s.top), bottom: Math.min(bottom, c.bottom, s.bottom) };
  const x = (visible.left + visible.right) / 2;
  const start = { x, y: visible.bottom - 12 }, end = { x, y: visible.top + 12 };
  const hit = at => sum.contains(document.elementFromPoint(at.x, at.y));
  const final = sum.querySelector('li:last-child,p:last-child'), range = document.createRange();
  if (final) range.selectNodeContents(final);
  const lines = final ? [...range.getClientRects()].filter(r => r.width > 0 && r.height > 0) : [];
  const last = lines.at(-1);
  const selected = Number(card.dataset.i), count = document.querySelectorAll('.ts-pk-rail a').length;
  const neighbors = { expected: [selected - 1, selected + 1].filter(index => index >= 0 && index < count), folds: card.querySelectorAll('.more').length,
    rows: [...card.querySelectorAll('.row')].map(row => {
      const r = row.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
      return { index: Number(row.dataset.k), near: row.classList.contains('near'), visible: r.width > 0 && r.height > 0 && r.left >= Math.max(viewport.left, c.left + card.clientLeft) - 1 && r.right <= Math.min(right, c.left + card.clientLeft + card.clientWidth) + 1 && r.top >= Math.max(headBottom, c.top + card.clientTop) - 1 && r.bottom <= Math.min(bottom, c.top + card.clientTop + card.clientHeight) + 1 && row.contains(document.elementFromPoint(x, y)) };
    }) };
  return { viewport, layoutViewport: { width: innerWidth, height: innerHeight, clientWidth: document.documentElement.clientWidth, clientHeight: document.documentElement.clientHeight }, card: c, summary: s, title: t, hint: j, strip: bar, header: h, rail: rect(document.querySelector('.ts-pk-nav')), boundary: headBottom, clearStrip, usable, expected,
    visibleHeight: Math.max(0, visible.bottom - visible.top), visibleWidth: Math.max(0, visible.right - visible.left), start, end, hit: hit(start) && hit(end),
    range: sum.scrollHeight - sum.clientHeight, inner: sum.scrollTop, outer: card.scrollTop, faded: sum.classList.contains('has-more'),
    lastVisible: !!last && last.top >= visible.top - 1 && last.bottom <= visible.bottom + 1 && last.left >= visible.left - 1 && last.right <= visible.right + 1,
    sideText: card.querySelector('.side')?.textContent.trim(), words: [...sum.querySelectorAll('li')].map(li => li.textContent), scrollWidth: sum.scrollWidth, clientWidth: sum.clientWidth, neighbors };
});
const dragSummary = async (p, reverse = false) => {
  const g = await summaryGeometry(p);
  if (!g.hit || g.start.y - g.end.y < 32) return false;
  const cdp = await p.context().newCDPSession(p), from = reverse ? g.end : g.start, to = reverse ? g.start : g.end;
  const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
  await touch('touchStart', from);
  for (let step = 1; step <= 8; step++) { await touch('touchMove', { x: from.x, y: from.y + (to.y - from.y) * step / 8 }); await p.waitForTimeout(24); }
  await p.waitForTimeout(100); await touch('touchEnd'); await p.waitForTimeout(260); await cdp.detach();
  return true;
};
try {
  browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  {
    const wanted = data.items.filter(item => item[1] === data.research_anchor).map(item => item[0]);
    const researchSelector = `.ts-pk-rail a[href="#${data.research_anchor}"]`;
    for (const hand of ['right', 'left']) {
      for (const spec of [
        { name: 'portrait-top', width: 390, height: 664, y: 0 },
        { name: 'portrait-scrolled', width: 390, height: 664, y: 600 },
        { name: 'landscape-top', width: 844, height: 342, y: 0 },
        { name: 'landscape-scrolled', width: 844, height: 342, y: 600 },
        { name: 'portrait-150percent', width: 390, height: 664, y: 600, scale: true },
        { name: 'tablet', width: 768, height: 1024, y: 600 }
      ]) {
        const label = `therapeutic/${hand}/${spec.name}`;
        // Each size is a fresh device context. Changing only setViewportSize
        // after mobile text enlargement can carry an altered visual zoom into
        // the next "device". Rotation itself remains in the retained suite.
        const { ctx, p, errors } = await open('/therapeutic-massage/', 200, { viewport: { width: spec.width, height: spec.height }, side: hand });
        if (await p.locator(researchSelector).count() !== 1) throw new Error(`INVALID ${label}: research destination missing`);
        await p.evaluate(scale => { document.documentElement.style.fontSize = scale ? '150%' : ''; }, spec.scale);
        // Resize and text changes can invoke browser scroll anchoring after
        // the first frame. Establish a stable layout before the baseline scroll,
        // and a stable reading position before the tap. Keep the one-pixel
        // gesture contract strict; do not count resize anchoring as tap motion.
        await stableLayout(p, `${label}/resize`);
        if (spec.scale) {
          const stripText = await p.evaluate(() => {
            const strip = document.querySelector('.ts-pk-top'), window = strip.querySelector('.in'), r = window.getBoundingClientRect();
            const labels = [...strip.querySelectorAll('.before,.tr span')].map(node => {
              const range = document.createRange(); range.selectNodeContents(node); const lines = [...range.getClientRects()].filter(line => line.width > 0 && line.height > 0);
              return { text: node.textContent, height: lines.length ? Math.max(...lines.map(line => line.bottom)) - Math.min(...lines.map(line => line.top)) : 0 };
            });
            return { windowHeight: r.height, labels, font: getComputedStyle(document.documentElement).fontSize };
          });
          ok(`${label}: font-only enlargement refits page identity and section title height`, stripText.labels.length > 1 && stripText.labels.every(item => item.height <= stripText.windowHeight + 1), stripText);
          if (hand === 'right') await capture(p, 'therapeutic-font-only-strip');
        }
        await scroll(p, spec.y); await stableLayout(p, `${label}/reading-position`);
        const before = await state(p), deferred = await deferredReadingSpace(p, label);
        const tapBefore = await state(p); await tap(p, researchSelector);
        if (deferred) {
          const tapped = await state(p), summary = await p.locator('.ts-pk-card .sum').elementHandle();
          ok(`${label}: the visible research rail tap itself keeps the page still`, samePage(tapBefore, tapped) && tapped.pinned, { tapBefore, tapped });
          await restoreReadingPosition(p, before.y); await stableLayout(p, `${label}/restored-top`);
          const returned = await state(p), sameNode = await summary.evaluate(node => node === document.querySelector('.ts-pk-card .sum'));
          await summary.dispose();
          ok(`${label}: same preview returns to its original reading position`, samePage(before, returned) && returned.pinned && returned.index === tapped.index && sameNode, { before, returned, sameNode });
          if (!samePage(before, returned) || !returned.pinned || returned.index !== tapped.index || !sameNode) throw new Error(`INVALID ${label}: original preview state was not restored for its existing geometry checks`);
        }
        const opened = await state(p), g = await summaryGeometry(p);
        if (g.layoutViewport.width !== spec.width || g.layoutViewport.height !== spec.height || Math.abs(g.viewport.width - spec.width) > 0.5 || Math.abs(g.viewport.height - spec.height) > 0.5 || Math.abs(g.viewport.scale - 1) > 0.001) console.log(`INFO kiro-layout ${engine}/${label}: viewport overflow diagnostics ${JSON.stringify(await viewportDiagnostics(p))}`);
        ok(`${label}: preview keeps page and original research wording`, samePage(before, opened) && opened.pinned && JSON.stringify(g.words) === JSON.stringify(wanted), { before, opened, words: g.words });
        ok(`${label}: visible viewport is the requested CSS size at normal scale`, Math.abs(g.viewport.width - spec.width) <= 0.5 && Math.abs(g.viewport.height - spec.height) <= 0.5 && Math.abs(g.viewport.scale - 1) <= 0.001, { visible: g.viewport, layout: g.layoutViewport });
        const insideVisible = rect => rect.left >= g.viewport.left - 1 && rect.right <= g.viewport.left + g.viewport.width + 1 && rect.top >= g.viewport.top - 1 && rect.bottom <= g.viewport.top + g.viewport.height + 1;
        const headerFits = !g.header || (g.header.left >= g.viewport.left - 1 && g.header.right <= g.viewport.left + g.viewport.width + 1);
        ok(`${label}: card and rail stay inside visible screen below header`, g.card.top >= g.boundary - 1 && insideVisible(g.card) && insideVisible(g.rail) && headerFits && (g.viewport.height <= 500 || g.clearStrip), g);
        ok(`${label}: only available previous and next controls remain visible and hit-testable`, g.neighbors.folds === 0 && JSON.stringify(g.neighbors.rows.map(row => row.index)) === JSON.stringify(g.neighbors.expected) && g.neighbors.rows.every(row => row.near && row.visible), g.neighbors);
        ok(`${label}: research offers a useful visible finger area`, g.expected >= 60 && g.visibleHeight >= g.expected - 1 && g.visibleWidth >= 200 && g.hit && g.scrollWidth <= g.clientWidth + 1, g);
        ok(`${label}: side label uses readable text`, g.sideText === (hand === 'right' ? 'Left' : 'Right'), g.sideText);
        if (hand === 'right' || spec.name === 'landscape-scrolled') await capture(p, `therapeutic-${hand}-${spec.name}`);
        if (engine === 'chromium') {
          const first = await state(p), swiped = await dragSummary(p), after = await state(p);
          ok(`${label}: native swipe stays in summary and responds`, swiped && samePreview(first, after) && (first.max <= 1 || after.inner > first.inner + 3) && Math.abs(after.outer - first.outer) <= 1, { first, after, swiped });
        }
        await p.locator('.ts-pk-card .sum').evaluate(sum => sum.focus({ preventScroll: true }));
        const first = await state(p); await p.keyboard.press('End');
        await p.waitForFunction(() => { const s = document.querySelector('.ts-pk-card .sum'); return s.scrollHeight - s.clientHeight - s.scrollTop <= 2; }, null, { timeout: 1500 }).catch(() => {});
        await settle(p); const last = await state(p), end = await summaryGeometry(p);
        ok(`${label}: final qualification remains reachable without page motion`, samePreview(first, last) && last.max - last.inner <= 2 && end.lastVisible && !end.faded, { first, last, end });
        if (engine === 'chromium') {
          const first = await state(p), swiped = await dragSummary(p), after = await state(p);
          ok(`${label}: end-of-summary swipe cannot pan page`, swiped && samePreview(first, after) && Math.abs(first.inner - after.inner) <= 1, { first, after, swiped });
        }
        ok(`${label}: no page errors`, errors.length === 0, errors); await ctx.close();
      }
    }
  }
  for (const path of ['/privacy-policy/', '/about/']) {
    const { ctx, p, errors } = await open(path), name = path.replaceAll('/', '');
    const toggles = p.locator('.uagb-faq-questions-button'), count = await toggles.count();
    if (count < 2) throw new Error(`INVALID ${path}: expected actual FAQ controls`);
    for (const hand of ['right', 'left']) {
      await side(p, hand); await p.setViewportSize({ width: 390, height: 664 }); await settle(p);
      for (let index = 0; index < count; index++) {
        let tested = 0;
        // Put each real full-width question across multiple parts of the rail.
        // Test the click handler's entire question row, not just text bounds.
        for (const fraction of [0.2, 0.5, 0.8]) {
          await toggles.nth(index).evaluate((button, fraction) => {
            const b = button.getBoundingClientRect(), rail = document.querySelector('.ts-pk-rail').getBoundingClientRect();
            scrollTo({ top: scrollY + b.top + b.height / 2 - (rail.top + rail.height * fraction), behavior: 'instant' });
          }, fraction); await settle(p);
          const sample = await toggles.nth(index).evaluate(button => {
            const b = button.getBoundingClientRect(), nav = document.querySelector('.ts-pk-nav').getBoundingClientRect(), points = [];
            for (const x of [b.left + 2, b.right - 2]) for (const y of [b.top + 2, b.top + b.height / 2, b.bottom - 2]) {
              if (x < 0 || x >= innerWidth || y < 0 || y >= innerHeight) continue;
              const hit = document.elementFromPoint(x, y);
              points.push({ x, y, own: !!hit && button.contains(hit), rail: !!hit?.closest('.ts-pk-nav'), hit: hit?.className });
            }
            return { text: button.innerText, top: b.top, bottom: b.bottom, left: b.left, right: b.right, besideRail: b.bottom > nav.top && b.top < nav.bottom, points };
          });
          if (!sample.besideRail || sample.points.length !== 6) {
            console.log(`INFO kiro-layout ${engine}/${name}/${hand}/FAQ${index + 1}/${fraction}: scroll clamp leaves this requested sample outside the rail or viewport; ${JSON.stringify(sample)}`);
            continue;
          }
          tested++;
          ok(`${name}/${hand}/FAQ${index + 1}/${fraction}: complete toggle edges clear the rail`, sample.points.every(point => point.own), sample);
        }
        if (!tested) throw new Error(`INVALID ${name}/${hand}/FAQ${index + 1}: no full visible edge sample beside the rail`);
      }
      await capture(p, `${name}-${hand}-FAQ-clearance`);
    }
    ok(`${name}: no page errors`, errors.length === 0, errors); await ctx.close();
  }
  {
    const { ctx, p, errors } = await open('/services/', 200, { viewport: { width: 1280, height: 800 }, mobile: false });
    for (const hand of ['right', 'left']) {
      await side(p, hand); await p.setViewportSize({ width: 1280, height: 800 }); await scroll(p, 0);
      // Text-node ranges measure rendered glyph runs, not padded block borders.
      const report = await p.evaluate(hand => {
        const nav = document.querySelector('.ts-pk-nav').getBoundingClientRect(), content = document.querySelector('.entry-content'), walker = document.createTreeWalker(content, NodeFilter.SHOW_TEXT);
        const bad = []; let count = 0, node;
        while ((node = walker.nextNode())) {
          const parent = node.parentElement;
          if (!node.textContent.trim() || parent.closest('.ts-pk,.ts-jump,script,style,noscript,nav,[hidden],[aria-hidden="true"],.screen-reader-text,.sr-only')) continue;
          const style = getComputedStyle(parent);
          if (style.visibility !== 'visible' || style.display === 'none') continue;
          const clipping = []; let hidden = false;
          for (let ancestor = parent; ancestor && content.contains(ancestor); ancestor = ancestor.parentElement) {
            const cs = getComputedStyle(ancestor), bounds = ancestor.getBoundingClientRect();
            // Services deliberately keeps a semantic H2 clipped to one pixel.
            // Range rectangles describe its glyphs even though they are not
            // painted. Respect ancestor clipping before counting visible text.
            if (/inset\(50%\)/.test(cs.clipPath) || /rect\(0px[, ]+0px[, ]+0px[, ]+0px\)/.test(cs.clip)) { hidden = true; break; }
            if (/hidden|clip|auto|scroll/.test(cs.overflowX) || /hidden|clip|auto|scroll/.test(cs.overflowY)) clipping.push({ left: bounds.left, right: bounds.right, top: bounds.top, bottom: bounds.bottom, x: /hidden|clip|auto|scroll/.test(cs.overflowX), y: /hidden|clip|auto|scroll/.test(cs.overflowY) });
          }
          if (hidden) continue;
          const range = document.createRange(); range.selectNodeContents(node);
          for (const r of range.getClientRects()) {
            if (r.width <= 1 || r.height <= 1) continue;
            let left = r.left, right = r.right, top = r.top, bottom = r.bottom;
            for (const clip of clipping) {
              if (clip.x) { left = Math.max(left, clip.left); right = Math.min(right, clip.right); }
              if (clip.y) { top = Math.max(top, clip.top); bottom = Math.min(bottom, clip.bottom); }
            }
            if (right - left <= 1 || bottom - top <= 1) continue;
            count++;
            if (hand === 'right' ? right > nav.left - 1 : left < nav.right + 1) bad.push({ text: node.textContent.trim().slice(0, 100), left, right, top: top + scrollY, bottom: bottom + scrollY });
          }
        }
        return { count, bad, rail: { left: nav.left, right: nav.right } };
      }, hand);
      ok(`services/1280/${hand}: actual text clears the rail column`, report.count > 20 && report.bad.length === 0, report);
      if (report.bad.length) await scroll(p, Math.max(0, report.bad[0].top - 380));
      await capture(p, `services-1280-${hand}`);
    }
    ok('services: no page errors', errors.length === 0, errors); await ctx.close();
  }
  {
    const { ctx, p, errors } = await open('/');
    const intro = await p.evaluate(() => {
      const config = JSON.parse(document.getElementById('ts-peek-nav-config').textContent), strip = document.querySelector('.ts-pk-top'), rail = document.querySelector('.ts-pk-rail');
      const visible = [...strip.querySelectorAll('*')].filter(node => node.childElementCount === 0 && node.textContent.trim() === config.title && getComputedStyle(node).visibility !== 'hidden' && getComputedStyle(node).display !== 'none' && node.getBoundingClientRect().height > 0);
      const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1; const paint = canvas.getContext('2d');
      paint.fillStyle = getComputedStyle(strip).backgroundColor; paint.fillRect(0, 0, 1, 1);
      return { title: config.title, titleVisible: visible.some(node => { const r = node.getBoundingClientRect(), s = strip.getBoundingClientRect(); return r.left >= s.left && r.right <= s.right && r.top >= s.top && r.bottom <= s.bottom; }), current: rail.querySelector('[aria-current="true"]')?.getAttribute('aria-label'), destinations: [...rail.querySelectorAll('[data-ts-pk-section]')].map(node => node.getAttribute('aria-label')), alpha: paint.getImageData(0, 0, 1, 1).data[3] };
    });
    ok('home: introductory hero shows page identity without a new rail entry', intro.titleVisible && !intro.current && !intro.destinations.includes(intro.title), intro);
    ok('home: title strip background is opaque', intro.alpha === 255, intro);
    await capture(p, 'home-introduction');
    ok('home: no page errors', errors.length === 0, errors); await ctx.close();
  }
  for (const [path, status, name] of [['/?s=massage', 200, 'search'], ['/peek-title-missing-page-verification-20261109/', 404, '404']]) {
    const { ctx, p, errors } = await open(path, status);
    const count = await p.locator('.ts-pk-rail [data-ts-pk-section]').count();
    const railHeight = await p.locator('.ts-pk-nav').evaluate(nav => nav.getBoundingClientRect().height);
    await tap(p, '.ts-pk-rail li:first-child > [data-ts-pk-section]');
    ok(`${name}: single-entry rail remains and has no unapproved summary`, count === 1 && await p.locator('.ts-pk-card.is-pinned').count() === 1 && await p.locator('.ts-pk-card .sum').count() === 0, { count });
    ok(`${name}: single-entry rail is proportionate and still finger-sized`, railHeight >= 44 && railHeight <= 120, { railHeight });
    ok(`${name}: no page errors`, errors.length === 0, errors); await ctx.close();
  }
  {
    const { ctx, p, errors } = await open('/work-with-me/');
    const sources = await p.evaluate(() => {
      const content = document.querySelector('.entry-content'), links = [...content.querySelectorAll('.ts-jump a[href^="#"]')];
      // Work With Me currently has ID-less H2s, not a jump row. Keep the
      // original DOM nodes in the oracle rather than inventing destinations.
      const list = links.length ? links.map(a => ({ label: a.textContent.trim(), target: document.getElementById(decodeURIComponent(a.hash.slice(1))) })).filter(item => item.target)
        : [...content.querySelectorAll('h2')].filter(node => {
          const r = node.getBoundingClientRect();
          return r.width > 2 && r.height > 2 && getComputedStyle(node).visibility === 'visible' && !node.closest('.ts-pk,nav,[hidden],[aria-hidden="true"],details:not([open])');
        }).map(node => ({ label: node.innerText.replace(/\s+/g, ' ').trim(), target: node }));
      window.__kiroWorkTargets = list.map(item => item.target);
      return list.map(item => item.label);
    });
    if (sources.length < 2) throw new Error('INVALID Work With Me original heading sources absent');
    const longest = sources.reduce((best, label, index) => label.length > sources[best].length ? index : best, 0);
    await p.evaluate(index => { const target = window.__kiroWorkTargets[index], strip = document.querySelector('.ts-pk-top'), line = Math.max(innerHeight * 0.25, (parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight + 24); scrollTo({ top: scrollY + target.getBoundingClientRect().top - line + 2, behavior: 'instant' }); }, longest);
    await settle(p);
    const title = await p.evaluate(() => {
      const window = document.querySelector('.ts-pk-top .in'), active = document.querySelector('.ts-pk-top .tr .is-now'), w = window.getBoundingClientRect(), range = document.createRange();
      if (!active) return { text: null, visible: false };
      range.selectNodeContents(active);
      const lines = [...range.getClientRects()].filter(r => r.width > 0 && r.height > 0);
      return { text: active.textContent, visible: lines.length > 0 && lines.every(r => r.left >= w.left - 1 && r.right <= w.right + 1 && r.top >= w.top - 1 && r.bottom <= w.bottom + 1) };
    });
    ok('work-with-me: longest current source title is readable in the strip', title.text === sources[longest] && title.visible, { ...title, expected: sources[longest] });
    await tap(p, `.ts-pk-rail li:nth-child(${longest + 1}) > [data-ts-pk-section]`);
    const preview = await p.locator('.ts-pk-card').evaluate(card => ({ title: card.querySelector('.open > b').textContent, edge: getComputedStyle(card.querySelector('.open')).borderLeftColor,
      rows: [...card.querySelectorAll('.row span')].filter(node => node.getBoundingClientRect().height > 0).map(node => ({ text: node.textContent, clipped: node.scrollWidth > node.clientWidth + 1 || node.scrollHeight > node.clientHeight + 1 })) }));
    ok('work-with-me: longest heading stays exact and rendered closed labels do not ellipsize', preview.title === sources[longest] && preview.rows.length > 0 && preview.rows.every(row => !row.clipped), preview);
    await capture(p, 'work-with-me-full-titles'); await close(p);
    await p.locator('.ts-pk-ix').focus(); await p.keyboard.press('Enter'); await settle(p);
    const current = await p.locator('.ts-pk-fan li.is-now').evaluateAll(nodes => nodes.map(node => getComputedStyle(node).borderLeftColor));
    ok('work-with-me: reading-position edge differs from preview edge', current.length === 1 && current[0] !== preview.edge, { current, preview: preview.edge });
    ok('work-with-me: no page errors', errors.length === 0, errors); await ctx.close();
  }
} catch (error) {
  console.log(`INVALID kiro-layout ${engine}: ${error.message}`); process.exitCode = 2;
} finally { if (browser) await browser.close(); }
console.log(`INFO Peek Kiro layout ${engine}: ${pass} passed, ${fail} failed; ${routes.size} actual routes, ${visits} page loads, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s; ${engine === 'chromium' ? 'simulated native touch' : engine === 'webkit' ? 'trusted Playwright taps and keyboard/mouse; no swipe, Safari, physical-device or assistive-technology claim' : 'keyboard/mouse only'}, root text enlargement is not OS text scale.`);
process.exit(process.exitCode || (fail ? 1 : 0));

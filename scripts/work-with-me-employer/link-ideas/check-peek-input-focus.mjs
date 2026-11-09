// Native Chromium touch focus on the four approved summary rails. This adds
// focus/activation coverage; the complete retained summary/outer-scroll suites
// must still run. CDP touch is not physical-device or screen-reader validation.
// --baseline-v6 substitutes only a LOCAL response with the frozen old core and
// runs the same assertions. Expected old defects remain FAIL, never PASS.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
const baseline = flags.includes('--baseline-v6');
if ((!local && base !== 'https://staging39.thaisolitude.com') || flags.length > 1 || flags.some(flag => flag !== '--baseline-v6') || (baseline && !local)) throw new Error('Exact staging39 or isolated localhost:8090/8091 only; --baseline-v6 is local only');
const manifest = JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8'));
const slugs = ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'];
if (slugs.some(slug => !manifest.pages.some(page => page.slug === slug && page.staging === 'enabled'))) throw new Error('All four approved service pages must be enabled');
let pass = 0, fail = 0, visited = 0, completed = 0, captures = 0, invalid = false, browser;
const started = Date.now();
const ok = (label, value, detail) => {
  value ? pass++ : fail++;
  console.log(`${value ? 'PASS' : 'FAIL'} input focus ${label}${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const frame = p => p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
const state = p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), sum = card?.querySelector('.sum'), active = document.activeElement;
  return { x: scrollX, y: scrollY, hash: location.hash, history: history.length,
    index: Number(card?.dataset.i), pinned: !!card?.classList.contains('is-pinned'), left: document.documentElement.classList.contains('ts-pk-left'),
    outer: card?.scrollTop, inner: sum?.scrollTop, innerMax: sum ? sum.scrollHeight - sum.clientHeight : null,
    active: { tag: active?.tagName, id: active?.id, className: active?.className,
      railHref: active?.closest('.ts-pk-rail a')?.getAttribute('href') || null,
      summary: !!active?.matches('.ts-pk-card .sum'), open: !!active?.matches('.ts-pk-card .open') } };
});
const samePage = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.hash === b.hash && a.history === b.history;
const samePreview = (a, b) => samePage(a, b) && b.pinned && a.index === b.index && a.left === b.left;
const point = (p, selector) => p.locator(selector).evaluate(node => {
  const r = node.getBoundingClientRect(), v = visualViewport;
  const left = v ? v.offsetLeft : 0, top = v ? v.offsetTop : 0, width = v ? v.width : innerWidth, height = v ? v.height : innerHeight;
  const x = r.left + r.width / 2, y = r.top + r.height / 2;
  if (r.width < 2 || r.height < 2 || x < left || x >= left + width || y < top || y >= top + height || !node.contains(document.elementFromPoint(x, y))) throw new Error('INVALID focus touch target is not visible and hit-testable: ' + node.className);
  return { x: x - left, y: y - top };
});
const beginTrace = p => p.evaluate(() => { window.__peekInputFocusEvents = []; window.__peekInputFocusTracing = true; });
const readTrace = p => p.evaluate(() => window.__peekInputFocusEvents);
const capture = async (p, slug, side, kind) => {
  const file = `peek-input-focus-${slug}-${side}-${kind}.jpg`, focus = await state(p);
  const geometry = await p.evaluate(kind => {
    const target = kind === 'jump' ? document.activeElement : document.querySelector('.ts-pk-card .sum');
    const r = target?.getBoundingClientRect(), strip = document.getElementById('ts-peek-title-strip').getBoundingClientRect();
    return { target: r ? { top: r.top, bottom: r.bottom, left: r.left, right: r.right } : null, stripBottom: strip.bottom };
  }, kind);
  console.log(`INFO input focus capture ${file}: ${JSON.stringify({ kind, focus, geometry })}`);
  console.log(`INLINE ${file} base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`);
  captures++;
};
try {
  let frozen = null;
  if (baseline) {
    frozen = readFileSync(new URL('./peek-nav-shared-v6.php', import.meta.url), 'utf8');
    if (createHash('sha256').update(frozen).digest('hex') !== 'ce21afe60e2c1bdd6286ddc1f9a1e3d14d999bfa967dc4109e4ef5f78426b2fa') throw new Error('Frozen v6 checksum differs; no baseline evidence');
  }
  browser = await chromium.launch(process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  for (const slug of slugs) for (const side of ['right', 'left']) {
    const context = await browser.newContext({ ...devices['iPhone 13'], viewport: { width: 390, height: 844 }, deviceScaleFactor: 1, reducedMotion: 'reduce' });
    try {
      await context.addInitScript(side => {
        localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', side);
        window.__peekInputFocusEvents = []; window.__peekInputFocusTracing = false;
        for (const type of ['pointerdown', 'pointerup', 'touchstart', 'touchend', 'mousedown', 'mouseup', 'click', 'focusin', 'focusout']) window.addEventListener(type, event => {
          if (!window.__peekInputFocusTracing) return;
          const entry = { type, at: performance.now(), tag: event.target.tagName, id: event.target.id, className: event.target.className,
            trusted: event.isTrusted, pointerType: event.pointerType, touch: event.sourceCapabilities?.firesTouchEvents,
            prevented: event.defaultPrevented, activeTag: document.activeElement?.tagName, activeId: document.activeElement?.id };
          window.__peekInputFocusEvents.push(entry);
          queueMicrotask(() => { entry.preventedAfterDispatch = event.defaultPrevented; entry.activeAfterDispatch = document.activeElement?.tagName; entry.activeIdAfterDispatch = document.activeElement?.id; });
        }, true);
      }, side);
      if (baseline) await context.route(`${base}/${slug}/`, async route => {
        const response = await route.fetch(), html = await response.text();
        const style = frozen.match(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/)?.[0], runtime = frozen.match(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/)?.[0];
        if (!style || !runtime || !html.includes('id="ts-peek-nav-css"') || !html.includes('id="ts-peek-nav-runtime"')) throw new Error('Local baseline cannot replace an identifiable core');
        await route.fulfill({ response, body: html.replace(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/, () => style).replace(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/, () => runtime) });
      });
      const p = await context.newPage(), errors = [], label = `${slug}/${side}`;
      p.on('pageerror', error => errors.push(error.message));
      const response = await p.goto(`${base}/${slug}/`, { waitUntil: 'load', timeout: 45000 });
      if (response?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`${label}: expected same-origin HTTP200, got ${response?.status()}; no further loads`);
      visited++;
      await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await frame(p);
      const viewport = await p.evaluate(() => visualViewport ? { width: visualViewport.width, height: visualViewport.height, scale: visualViewport.scale, left: visualViewport.offsetLeft, top: visualViewport.offsetTop } : null);
      if (!viewport || Math.abs(viewport.width - 390) > 1 || Math.abs(viewport.height - 844) > 1 || Math.abs(viewport.scale - 1) > .001 || Math.abs(viewport.left) > 1 || Math.abs(viewport.top) > 1) throw new Error(`${label}: native-touch viewport changed scale or origin: ${JSON.stringify(viewport)}`);
      const original = await p.evaluate(() => {
        const links = [...document.querySelectorAll('.ts-jump a')];
        window.__peekFocusTargets = links.map(link => document.getElementById(decodeURIComponent(link.hash.slice(1))));
        return { row: links.map(link => ({ href: link.getAttribute('href'), label: link.textContent.trim() })),
          data: document.getElementById('ts-peek-nav-data')?.textContent,
          headings: window.__peekFocusTargets.map(target => ({ tag: target?.tagName, id: target?.id, text: target?.textContent })) };
      });
      const data = JSON.parse(original.data || 'null'), expected = manifest.pages.find(page => page.slug === slug);
      const research = original.row.findIndex(item => /research/i.test(item.label));
      if (!data || data.slug !== slug || data.approval?.status !== 'approved' || research < 2 || original.row.length !== expected.expectedLabels.length || original.headings.some(heading => heading.tag !== 'H2') || JSON.stringify(original.row.map(item => item.label)) !== JSON.stringify(expected.expectedLabels)) throw new Error(`${label}: reviewed summary/H2 destinations are unavailable`);
      if (baseline && await p.locator('#ts-peek-nav-runtime').textContent() !== frozen.match(/<script id="ts-peek-nav-runtime">([\s\S]*?)<\/script>/)[1]) throw new Error('Frozen v6 was not actually served');
      const cdp = await context.newCDPSession(p);
      const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
      const tap = async selector => { const at = await point(p, selector); await touch('touchStart', at); await p.waitForTimeout(35); await touch('touchEnd'); };
      const settleInput = async () => { await p.waitForTimeout(80); await frame(p); };
      const railSelector = index => `.ts-pk-rail li:nth-child(${index + 1}) a`;
      const open = async (index, focused = false) => {
        await p.keyboard.press('Escape');
        await p.evaluate(() => { history.replaceState(null, '', location.pathname + location.search); scrollTo({ top: Math.min(600, (document.documentElement.scrollHeight - innerHeight) / 2), behavior: 'instant' }); });
        await p.waitForTimeout(100); await frame(p);
        const before = await state(p);
        if (focused) { await p.locator(railSelector(index)).focus(); await p.keyboard.press('Enter'); }
        else await tap(railSelector(index));
        await settleInput();
        const after = await state(p);
        ok(`${label}: ${focused ? 'keyboard' : 'native'} rail opens section ${index + 1} without page movement`, samePage(before, after) && after.pinned && after.index === index, { before, after });
        if (!after.pinned || after.index !== index) throw new Error(`${label}: no requested preview for subsequent focus checks`);
        if (focused && !await p.locator('.ts-pk-card').evaluate(card => card === document.activeElement)) throw new Error(`${label}: keyboard activation did not establish preview ownership`);
        return after;
      };

      // A scrollable summary keeps native focus and scrolling. A blanket mouse
      // suppression on every card tap would fail this reading-surface check.
      let before = await open(research);
      await tap('.ts-pk-card .sum'); await settleInput();
      let after = await state(p);
      ok(`${label}: research text tap focuses its own summary without jumping`, samePreview(before, after) && after.active.summary, { before, after });
      // Reading-state capture precedes the later timed jump/new-input window.
      if (!baseline && slug === 'therapeutic-massage' && side === 'right') await capture(p, slug, side, 'summary');
      const surface = await p.locator('.ts-pk-card .sum').evaluate(sum => {
        const r = sum.getBoundingClientRect(), card = sum.closest('.ts-pk-card'), c = card.getBoundingClientRect();
        const top = Math.max(r.top, c.top + card.clientTop, 0), bottom = Math.min(r.bottom, c.top + card.clientTop + card.clientHeight, innerHeight);
        const x = r.left + r.width / 2, start = { x, y: bottom - 18 }, end = { x, y: top + 18 };
        return { start, end, range: sum.scrollHeight - sum.clientHeight, height: bottom - top, hits: [start, end].every(at => sum.contains(document.elementFromPoint(at.x, at.y))) };
      });
      if (!surface.hits || surface.height < 80 || surface.range < 15) throw new Error(`${label}: research summary lacks genuine visible scroll range: ${JSON.stringify(surface)}`);
      before = await state(p); await touch('touchStart', surface.start);
      for (let n = 1; n <= 8; n++) { await touch('touchMove', { x: surface.start.x, y: surface.start.y + (surface.end.y - surface.start.y) * n / 8 }); await p.waitForTimeout(24); }
      await p.waitForTimeout(100); await touch('touchEnd'); await p.waitForTimeout(260);
      after = await state(p);
      ok(`${label}: native research swipe moves only the summary`, samePreview(before, after) && after.inner > before.inner + 5 && Math.abs(after.outer - before.outer) <= 1, { before, after });

      await beginTrace(p); before = await state(p);
      await tap('.ts-pk-card .open > b'); await settleInput();
      const landed = await state(p), events = await readTrace(p), href = original.row[research].href, targetId = decodeURIComponent(href.slice(1));
      const geometry = await p.evaluate(id => { const h = document.getElementById(id), strip = document.getElementById('ts-peek-title-strip'); return { top: h.getBoundingClientRect().top, stripBottom: strip.getBoundingClientRect().bottom }; }, targetId);
      ok(`${label}: native jump retains the exact H2 after compatibility events`, landed.active.id === targetId && landed.active.tag === 'H2', { landed, events });
      ok(`${label}: native jump keeps the original fragment/history and closes the preview`, landed.hash === href && landed.history === before.history && !landed.pinned, { before, landed });
      ok(`${label}: destination lands below the actual strip`, geometry.top >= geometry.stripBottom + 8 && geometry.top <= geometry.stripBottom + 18, geometry);
      const nativeSequence = ['pointerdown', 'pointerup', 'touchstart', 'touchend'].every(type => events.some(event => event.type === type && event.trusted));
      if (!nativeSequence) throw new Error(`${label}: trusted native activation sequence is missing`);
      // A genuinely new pointer must work immediately, before the existing
      // compatibility-event reservation could have expired by its timer.
      await tap(railSelector(0)); await settleInput();
      after = await state(p);
      const nextEvents = await readTrace(p), end = nextEvents.find(event => event.type === 'touchend' && event.trusted);
      const next = end && nextEvents.find(event => event.type === 'pointerdown' && event.trusted && event.at > end.at);
      const elapsed = next && next.at - end.at;
      if (!(elapsed >= 0 && elapsed < 450)) throw new Error(`${label}: subsequent genuine pointer did not arrive within 450ms: ${elapsed}`);
      ok(`${label}: immediate new native input opens its own preview without moving the page`, samePage(landed, after) && after.pinned && after.index === 0, { landed, after, elapsed });
      console.log(`INFO input focus trace ${label}: ${JSON.stringify({ targetId, landed, geometry, compatibilityMouseObserved: events.some(event => event.type === 'mousedown' && event.trusted), nextPointerAfterLiftMs: elapsed, events })}`);

      // The timed proof above is already finished. Repeat an ordinary native
      // research jump for visual evidence; screenshot latency cannot enlarge
      // the tested interval between the original lift and the new pointer.
      if (!baseline && (slug === 'thai-massage' || (slug === 'therapeutic-massage' && side === 'right'))) {
        await tap(railSelector(research)); await settleInput();
        await tap('.ts-pk-card .open > b'); await settleInput();
        await capture(p, slug, side, 'jump');
        await tap(railSelector(0)); await settleInput(); after = await state(p);
      }

      before = after; await tap('.ts-pk-card .sum'); await settleInput(); after = await state(p);
      ok(`${label}: first-section text retains native summary focus and preview ownership`, samePreview(before, after) && after.active.summary, { before, after });

      before = await open(1, true); await tap('.ts-pk-card .x'); await settleInput(); after = await state(p);
      ok(`${label}: native close returns owned focus to the same rail destination`, samePage(before, after) && !after.pinned && after.active.railHref === original.row[1].href, { before, after });
      before = await open(1, true);
      const closed = await p.locator('.ts-pk-card .row').evaluateAll(rows => {
        const row = rows.find(row => Number(row.dataset.k) !== 1 && row.getBoundingClientRect().height > 0);
        return row ? Number(row.dataset.k) : -1;
      });
      if (closed < 0) throw new Error(`${label}: no visible closed topic for focus transfer`);
      await tap(`.ts-pk-card .row[data-k="${closed}"]`); await settleInput(); after = await state(p);
      ok(`${label}: native closed-topic tap retains focus on its newly opened box`, samePage(before, after) && after.pinned && after.index === closed && after.active.open, { before, after });
      before = await open(1, true); await tap('.ts-pk-card .side'); await settleInput(); after = await state(p);
      ok(`${label}: native side control keeps owned rail focus and changes only side`, samePage(before, after) && !after.pinned && after.left !== before.left && after.active.railHref === original.row[1].href, { before, after });
      const integrity = await p.evaluate(original => ({
        data: document.getElementById('ts-peek-nav-data')?.textContent === original.data,
        row: JSON.stringify([...document.querySelectorAll('.ts-jump a')].map(link => ({ href: link.getAttribute('href'), label: link.textContent.trim() }))) === JSON.stringify(original.row),
        headings: window.__peekFocusTargets.every((node, index) => node.isConnected && node === document.getElementById(original.headings[index].id) && node.textContent === original.headings[index].text)
      }), original);
      ok(`${label}: original destinations and approved page data remain intact`, integrity.data && integrity.row && integrity.headings, integrity);
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors);
      completed++;
    } finally { await context.close(); }
    if (!local) await new Promise(resolve => setTimeout(resolve, 5500));
  }
} catch (error) {
  invalid = true; console.log(`INVALID Peek input focus chromium: ${error.message}`);
} finally { if (browser) await browser.close(); }
if (completed !== slugs.length * 2 && !invalid) { invalid = true; console.log('INVALID Peek input focus chromium: incomplete page/side matrix'); }
console.log(`INFO Peek input focus chromium${baseline ? ' frozen-v6 local baseline' : ''}: ${pass} passed, ${fail} failed; ${completed}/8 completed cases, ${visited} page loads, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s${invalid ? '; INCOMPLETE/INVALID' : ''}; simulated native touch, not physical-device or assistive-technology validation.`);
process.exit(invalid ? 2 : fail ? 1 : 0);

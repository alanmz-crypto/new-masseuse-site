// Native Chromium touch input on actual page geometry. This is browser simulation,
// not a physical-device, Safari, VoiceOver or TalkBack certification.
// --baseline-v1 replaces only a LOCAL fixture's core with the frozen deployed v1.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
import { createHash } from 'node:crypto';
const base = (process.argv[2] || '').replace(/\/$/, '');
const flags = process.argv.slice(3), baseline = flags.includes('--baseline-v1');
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090', 'http://localhost:8091'].includes(base) || flags.some(flag => flag !== '--baseline-v1') || flags.length > 1 || (baseline && !base.startsWith('http://localhost:'))) throw new Error('Use staging39 or localhost:8090/8091; baseline replacement is local only');
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const manifest = JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8'));
const pages = ['thai-massage', 'chair-massage'].map(slug => manifest.pages.find(page => page.slug === slug));
if (pages.some(page => !page || page.staging !== 'enabled')) throw new Error('Thai and Chair must be enabled in the manifest');
let pass = 0, fail = 0;
const started = Date.now();
const ok = (label, value, detail = {}) => { value ? pass++ : fail++; console.log(`${value ? 'PASS' : 'FAIL'} touch ${label}${value ? '' : ' ' + JSON.stringify(detail)}`); return value; };
const browser = await chromium.launch(process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
const state = p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), sum = card?.querySelector('.sum');
  return { x: scrollX, y: scrollY, hash: location.hash, index: Number(card?.dataset.i), pinned: card?.classList.contains('is-pinned'), left: document.documentElement.classList.contains('ts-pk-left'), outer: card?.scrollTop, outerMax: card ? card.scrollHeight - card.clientHeight : null, inner: sum?.scrollTop, innerMax: sum ? sum.scrollHeight - sum.clientHeight : null };
});
const samePage = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.hash === b.hash;
const samePreview = (a, b) => samePage(a, b) && a.index === b.index && b.pinned && a.left === b.left;
const sameControl = (a, b) => samePreview(a, b) && Math.abs(a.outer - b.outer) <= 1 && Math.abs(a.inner - b.inner) <= 1;
const point = (p, selector) => p.locator(selector).evaluate(element => {
  const r = element.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
  if (r.width <= 0 || r.height <= 0 || x < 1 || x > innerWidth - 1 || y < 1 || y > innerHeight - 1 || !element.contains(document.elementFromPoint(x, y))) throw new Error('Touch target is not visible and hit-testable: ' + element.className);
  return { x, y };
});
// A summary's raw rectangle can extend past the outer card's clipping area.
// Native gestures must begin on the visible summary, not on the page behind it.
// This measures only: it does not focus or move either scroller into position.
const summarySurface = (p, margin = 18) => p.locator('.ts-pk-card .sum').evaluate((sum, margin) => {
  const card = sum.closest('.ts-pk-card'), r = sum.getBoundingClientRect(), c = card.getBoundingClientRect();
  const left = Math.max(0, r.left, c.left + card.clientLeft), right = Math.min(innerWidth, r.right, c.left + card.clientLeft + card.clientWidth);
  const top = Math.max(0, r.top, c.top + card.clientTop), bottom = Math.min(innerHeight, r.bottom, c.top + card.clientTop + card.clientHeight);
  const width = Math.max(0, right - left), height = Math.max(0, bottom - top), inset = Math.min(margin, height / 5), x = (left + right) / 2;
  const start = { x, y: bottom - inset }, end = { x, y: top + inset }, middle = { x, y: (top + bottom) / 2 };
  const hits = [start, middle, end].map(at => { const hit = document.elementFromPoint(at.x, at.y); return { summary: sum.contains(hit), tag: hit?.tagName, className: hit?.className }; });
  return { same: sum === window.__peekShortSummary, start, end, width, height, hits,
    raw: r.toJSON(), visible: { left, right, top, bottom }, action: getComputedStyle(sum).touchAction,
    visibleRows: [...card.querySelectorAll('.row')].filter(row => row.getBoundingClientRect().height > 0).length };
}, margin);
const usableSurface = (surface, height) => surface.height >= height && surface.width > 0 && surface.hits.every(hit => hit.summary);
try {
  for (const spec of pages) {
    const data = JSON.parse(readFileSync(new URL(`./peek-data/${spec.slug}.json`, import.meta.url), 'utf8'));
    for (const side of ['right', 'left']) {
      const ctx = await browser.newContext({ ...devices['iPhone 13'], viewport: { width: 390, height: 844 }, deviceScaleFactor: 1, reducedMotion: 'reduce' });
      await ctx.addInitScript(side => { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', side); }, side);
      if (baseline) {
        const frozen = readFileSync(new URL('./peek-nav-shared-v1.php', import.meta.url), 'utf8');
        if (createHash('sha256').update(frozen).digest('hex') !== '5941becfb3ff70e7359ae61a7c4a2cdd7e0a5b88869e58a5c2fd790bd5824cc3') throw new Error('Frozen shared-v1 baseline hash differs');
        const style = frozen.match(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/)[0];
        const runtime = frozen.match(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/)[0];
        await ctx.route(`${base}/${spec.slug}/`, async route => {
          const response = await route.fetch(), html = await response.text();
          if (!html.includes('id="ts-peek-nav-css"') || !html.includes('id="ts-peek-nav-runtime"')) throw new Error('Local baseline fixture has no core to replace');
          await route.fulfill({ response, body: html.replace(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/, () => style).replace(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/, () => runtime) });
        });
      }
      const p = await ctx.newPage(), errors = [], label = `${spec.slug}/390/${side}`;
      p.on('pageerror', error => errors.push(error.message));
      const response = await p.goto(`${base}/${spec.slug}/`, { waitUntil: 'load' });
      if (!response || response.status() !== 200 || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${label}: response ${response?.status()}`);
      await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 });
      if (baseline) {
        const expected = readFileSync(new URL('./peek-nav-shared-v1.php', import.meta.url), 'utf8').match(/<script id="ts-peek-nav-runtime">([\s\S]*?)<\/script>/)[1];
        if (await p.locator('#ts-peek-nav-runtime').textContent() !== expected) throw new Error('Local baseline replacement did not serve frozen v1');
      }
      const cdp = await ctx.newCDPSession(p);
      const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, radiusX: 1, radiusY: 1, force: 1, id: 1 }] });
      const tap = async at => { await touch('touchStart', at); await p.waitForTimeout(35); await touch('touchEnd'); await p.waitForTimeout(180); };
      const drag = async (from, stops) => {
        await touch('touchStart', from); let previous = from;
        for (const to of stops) {
          for (let step = 1; step <= 8; step++) { await touch('touchMove', { x: previous.x + (to.x - previous.x) * step / 8, y: previous.y + (to.y - previous.y) * step / 8 }); await p.waitForTimeout(24); }
          previous = to;
        }
        // Release after deceleration, so the page/summary result is not sampled mid-fling.
        await p.waitForTimeout(100); await touch('touchEnd'); await p.waitForTimeout(260);
      };
      const reset = async (index = 0) => {
        await p.keyboard.press('Escape');
        await p.evaluate(() => scrollTo({ top: Math.min(600, (document.documentElement.scrollHeight - innerHeight) / 2), behavior: 'instant' }));
        await p.waitForTimeout(100);
        await tap(await point(p, `.ts-pk-rail li:nth-child(${index + 1}) a`));
        const s = await state(p);
        ok(`${label}: native rail press opens section ${index + 1}`, s.pinned && s.index === index, s);
      };
      const rowDrag = async (name, destination) => {
        await reset();
        const from = await point(p, '.ts-pk-card .row[data-k="1"]'), before = await state(p);
        await drag(from, await destination(from));
        const after = await state(p);
        ok(`${label}: ${name} stays with the closed row`, sameControl(before, after), { before, after });
      };
      await rowDrag('vertical upward drag', from => [{ x: from.x, y: from.y - 90 }]);
      await rowDrag('vertical downward drag', from => [{ x: from.x, y: Math.min(810, from.y + 70) }]);
      await rowDrag('horizontal drag', from => [{ x: from.x + 65, y: from.y }]);
      await rowDrag('drag ending over the open preview', async () => [await point(p, '.ts-pk-card .open > b')]);
      await rowDrag('drag ending over another closed row', async () => [await point(p, `.ts-pk-card .row[data-k="${spec.expectedLabels.length - 1}"]`)]);
      await rowDrag('drag released outside the card', from => [{ x: side === 'right' ? 8 : 382, y: from.y + 35 }]);
      await rowDrag('out-and-back drag', from => [{ x: from.x + 65, y: from.y }, from]);
      await reset();
      let before = await state(p);
      await tap(await point(p, '.ts-pk-card .row[data-k="1"]'));
      let after = await state(p);
      ok(`${label}: a true closed-row tap opens its preview without scrolling`, samePage(before, after) && after.pinned && after.index === 1 && after.left === before.left, { before, after });
      for (const control of ['x', 'side']) {
        await reset();
        const from = await point(p, `.ts-pk-card .${control}`); before = await state(p);
        // Returning to the origin distinguishes a drag from a true tap even when
        // its final coordinates are identical. Native compatibility clicks matter.
        await drag(from, [{ x: Math.max(12, from.x - 65), y: from.y + 32 }, from]);
        after = await state(p);
        ok(`${label}: ${control} out-and-back drag neither activates nor scrolls`, sameControl(before, after), { before, after });
        await reset(); before = await state(p);
        await tap(await point(p, `.ts-pk-card .${control}`)); after = await state(p);
        ok(`${label}: ${control} true tap still works without scrolling`, samePage(before, after) && !after.pinned && (control === 'side' ? after.left !== before.left : after.left === before.left), { before, after });
        if (control === 'side') { await reset(); await tap(await point(p, '.ts-pk-card .side')); }
      }
      const row = await p.$$eval('.ts-jump a', links => links.map(a => a.getAttribute('href')));
      const research = row.indexOf('#' + data.research_anchor);
      if (research < 0) throw new Error(`INVALID ${label}: no research target`);
      const short = row.indexOf(spec.slug === 'thai-massage' ? '#ts-common-reasons' : '#ts-what-it-is');
      if (short < 0) throw new Error(`INVALID ${label}: no reviewed short-summary target`);
      const checkPanPolicy = async phase => {
        const policy = await p.evaluate(() => {
          const card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum');
          return [card, sum].map(el => ({ range: el.scrollHeight - el.clientHeight, action: getComputedStyle(el).touchAction }));
        });
        ok(`${label}/${phase}: browser pan permission follows each actual scroll range`, policy.every(item => item.action === (item.range > 1 ? 'pan-y' : 'none')), policy);
      };
      await reset(short); await checkPanPolicy('short');
      const shortOrigin = async surface => surface === 'padding' ? p.locator('.ts-pk-card .open').evaluate(open => {
        const r = open.getBoundingClientRect(), x = r.left + 5, y = r.top + r.height / 2;
        if (document.elementFromPoint(x, y) !== open) throw new Error('Open-card padding is not a distinct touch surface');
        return { x, y };
      }) : point(p, surface === 'summary' ? '.ts-pk-card .sum' : surface === 'title' ? '.ts-pk-card .open > b' : '.ts-pk-card .hint > span');
      // A short summary cannot consume a swipe. Exercise its ordinary open-box
      // surfaces with body scroll available in both directions, not at page top.
      for (const surface of ['summary', 'title', 'padding', 'hint']) {
        for (const [direction, dx, dy] of [['up', 0, -75], ['down', 0, 75], ['horizontal', 65, 0]]) {
          await reset(short); before = await state(p);
          const room = await p.evaluate(() => document.documentElement.scrollHeight - innerHeight - scrollY);
          if (before.innerMax > 1 || before.outerMax > 1 || before.y < 100 || room < 100) throw new Error(`INVALID ${label}: short-summary containment requires no card overflow and available page scroll`);
          const from = await shortOrigin(surface);
          await drag(from, [{ x: from.x + dx, y: from.y + dy }]); after = await state(p);
          ok(`${label}: non-overflow ${surface} ${direction} swipe stays in the open box`, sameControl(before, after), { before, after });
        }
      }
      await reset(short);
      await p.locator('.ts-pk-card .sum').evaluate(sum => { window.__peekShortSummary = sum; });
      await p.setViewportSize({ width: 844, height: 320 }); await p.waitForTimeout(150);
      await checkPanPolicy('short-resized');
      before = await state(p);
      const resized = await summarySurface(p, 5);
      if (before.innerMax <= 1 || !usableSurface(resized, 32)) throw new Error(`INVALID ${label}: resized short summary lacks a visible, hit-tested inner scroll surface: ${JSON.stringify(resized)}`);
      await drag(resized.start, [resized.end]); after = await state(p);
      ok(`${label}/844x320: kept short summary gains native scrolling after resize`, resized.same && samePreview(before, after) && after.inner > before.inner + 1 && Math.abs(after.outer - before.outer) <= 1, { before, after, resized });
      if (!after.pinned || after.inner === undefined) throw new Error(`INVALID ${label}: cannot continue the same-summary resize round trip after the preview was lost; preceding assertion records the gesture failure`);
      await p.setViewportSize({ width: 390, height: 844 }); await p.waitForTimeout(150);
      await checkPanPolicy('short-returned');
      before = await state(p);
      const returned = await p.locator('.ts-pk-card .sum').evaluate(sum => sum === window.__peekShortSummary);
      const fromShort = await shortOrigin('summary'); await drag(fromShort, [{ x: fromShort.x, y: fromShort.y - 75 }]); after = await state(p);
      ok(`${label}: same summary stops panning again when it fits after resize`, returned && before.innerMax <= 1 && before.outerMax <= 1 && sameControl(before, after), { before, after });
      before = await state(p); await tap(await point(p, '.ts-pk-card .sum')); after = await state(p);
      ok(`${label}: short-summary tap still reads without jumping`, sameControl(before, after), { before, after });
      before = await state(p); await tap(await point(p, `.ts-pk-card .row[data-k="${research}"]`)); after = await state(p);
      ok(`${label}: tapping research after a short summary opens the long preview`, samePage(before, after) && after.pinned && after.index === research, { before, after });
      const longPath = await summarySurface(p, 16);
      if (!usableSurface(longPath, 45)) throw new Error(`INVALID ${label}: long summary after short preview is not hit-testable: ${JSON.stringify(longPath)}`);
      before = await state(p); await drag(longPath.start, [longPath.end]); after = await state(p);
      ok(`${label}: long summary after short preview retains its native scroll`, before.innerMax > 20 && samePreview(before, after) && after.inner > before.inner + 10, { before, after });
      await reset(research);
      const sumSelector = '.ts-pk-card .open .sum';
      const sumPath = async direction => {
        const surface = await summarySurface(p), current = await state(p);
        if (!usableSurface(surface, 45) || current.innerMax < 20) throw new Error(`Research summary is not a usable native scroll target: ${JSON.stringify(surface)}`);
        return direction === 'up' ? [surface.start, surface.end] : [surface.end, surface.start];
      };
      await p.locator(sumSelector).evaluate(sum => { sum.scrollTop = 0; });
      before = await state(p);
      let path = await sumPath('up'); await drag(path[0], [path[1]]); after = await state(p);
      ok(`${label}: native summary swipe scrolls its own text only`, samePreview(before, after) && after.inner > before.inner + 10, { before, after });
      for (const edge of ['bottom', 'top']) {
        await p.locator(sumSelector).evaluate((sum, edge) => { sum.scrollTop = edge === 'bottom' ? sum.scrollHeight : 0; }, edge);
        before = await state(p); path = await sumPath(edge === 'bottom' ? 'up' : 'down'); await drag(path[0], [path[1]]); after = await state(p);
        ok(`${label}: summary overscroll at ${edge} does not escape to the page`, samePreview(before, after) && Math.abs(before.inner - after.inner) <= 1, { before, after });
        if (edge === 'bottom') await checkPanPolicy('long-at-bottom');
      }
      before = await state(p); await tap(await point(p, sumSelector)); after = await state(p);
      ok(`${label}: a summary tap reads without jumping`, samePreview(before, after), { before, after });
      if (spec.slug === 'chair-massage' && side === 'right') {
        // Preserve the existing native outer-card fallback in a constrained
        // viewport. A gesture beginning on its title may scroll the card body;
        // the closed controls above deliberately may not.
        await p.setViewportSize({ width: 844, height: 320 });
        await p.evaluate(() => { document.documentElement.style.fontSize = (parseFloat(getComputedStyle(document.documentElement).fontSize) * 2) + 'px'; });
        await reset(research);
        await p.locator('.ts-pk-card').evaluate(card => { card.scrollTop = 0; });
        const origin = await p.evaluate(() => {
          const card = document.querySelector('.ts-pk-card'), title = card.querySelector('.open > b'), c = card.getBoundingClientRect(), t = title.getBoundingClientRect();
          const x = Math.max(c.left + 12, t.left + Math.min(30, t.width / 2)), y = Math.min(c.bottom - 14, t.bottom - 6);
          if (card.scrollHeight - card.clientHeight < 20 || y < Math.max(c.top, t.top) + 25 || !title.contains(document.elementFromPoint(x, y))) throw new Error('INVALID constrained card has no hit-testable title scroll surface');
          return { x, y, end: Math.max(c.top + 12, y - 60) };
        });
        before = await state(p); await drag({ x: origin.x, y: origin.y }, [{ x: origin.x, y: origin.end }]); after = await state(p);
        ok(`${spec.slug}/844x320/${side}/double-text: outer-card title swipe still scrolls its own card`, samePreview(before, after) && after.outer > before.outer + 5 && Math.abs(before.inner - after.inner) <= 1, { before, after });
      }
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors);
      await ctx.close();
    }
  }
  // Actual-device report: switching among previews can feel slow to accept the
  // next scroll. Keep this separate from the older tap helper's 180 ms pause:
  // each new finger starts as soon as the preceding native lift has completed.
  // This geometry belongs to the current candidate, not the frozen-v1 probe.
  for (const slug of baseline ? [] : ['thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage']) {
    for (const side of ['right', 'left']) {
      const ctx = await browser.newContext({ ...devices['iPhone 13'], viewport: { width: 390, height: 844 }, deviceScaleFactor: 1 });
      await ctx.addInitScript(side => { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', side); }, side);
      const p = await ctx.newPage(), errors = [], label = `${slug}/390/${side}/rapid`;
      p.on('pageerror', error => errors.push(error.message));
      const response = await p.goto(`${base}/${slug}/`, { waitUntil: 'load' });
      if (!response || response.status() !== 200 || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${label}: response ${response?.status()}`);
      await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 });
      const cdp = await ctx.newCDPSession(p);
      const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, radiusX: 5, radiusY: 5, force: 1, id: 1 }] });
      const tapNow = async at => { await touch('touchStart', at); await p.waitForTimeout(30); await touch('touchEnd'); };
      await p.evaluate(() => scrollTo({ top: 600, behavior: 'instant' }));
      await p.waitForTimeout(100); // Establish page geometry before the rapid sequence.
      const count = await p.locator('.ts-pk-rail a').count();
      const research = await p.locator('.ts-jump a').evaluateAll(links => links.findIndex(a => /research/i.test(a.textContent)));
      if (research < 3 || count < 4) throw new Error(`INVALID ${label}: expected service-page destinations`);
      await tapNow(await point(p, '.ts-pk-rail li:first-child a'));
      for (let round = 0; round < 2; round++) {
        for (const index of [1, 2, 0, research]) {
          const before = await state(p);
          await tapNow(await point(p, `.ts-pk-card .row[data-k="${index}"]`));
          const after = await state(p);
          ok(`${label}/${round}: immediate row ${index + 1} opens without moving the page`, samePage(before, after) && after.pinned && after.index === index, { before, after });
        }
        const before = await state(p);
        const path = await summarySurface(p);
        ok(`${label}/${round}: long summary provides a generous finger stroke with every topic retained`, usableSurface(path, 180) && path.width >= 200 && before.innerMax > 1 && path.action === 'pan-y' && path.visibleRows === count - 1, { before, path });
        if (!usableSurface(path, 180) || path.width < 200) throw new Error(`INVALID ${label}: rapid-scroll gesture has no generous visible summary surface: ${JSON.stringify(path)}`);
        await touch('touchStart', path.start);
        for (let step = 1; step <= 6; step++) {
          await touch('touchMove', { x: path.start.x, y: path.start.y + (path.end.y - path.start.y) * step / 6 });
          await p.waitForTimeout(16);
        }
        // Read while the finger is still down: a deferred response after release
        // cannot satisfy this first-stroke check. Then let native momentum stop.
        const during = await state(p);
        ok(`${label}/${round}: first swipe after rapid switches scrolls immediately inside the summary`, samePreview(before, during) && during.inner > before.inner + 5 && Math.abs(during.outer - before.outer) <= 1, { before, during });
        await p.waitForTimeout(100); await touch('touchEnd'); await p.waitForTimeout(180);
      }
      // At the page top, the real strip/banner can reduce available height.
      // Fold distant controls before sacrificing the summary's reading surface.
      await p.keyboard.press('Escape');
      await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' }));
      await p.waitForTimeout(100);
      await tapNow(await point(p, `.ts-pk-rail li:nth-child(${research + 1}) a`));
      const fit = await p.locator('.ts-pk-card').evaluate(card => {
        const visible = node => !!node && node.getBoundingClientRect().height > 0;
        return { compact: card.classList.contains('fit1'), summary: card.querySelector('.sum').getBoundingClientRect().height,
          neighbors: [...card.querySelectorAll('.row.near')].every(visible), distant: [...card.querySelectorAll('.row:not(.near)')].some(visible),
          more: [...card.querySelectorAll('.more')].some(visible), title: visible(card.querySelector('.open > b')), hint: visible(card.querySelector('.hint')) };
      });
      ok(`${label}: tight fit keeps the summary, nearest topic and jump visible`, fit.summary >= 160 && fit.neighbors && fit.title && fit.hint && (!fit.compact || (!fit.distant && fit.more)), fit);
      if (fit.compact) {
        const before = await state(p);
        const neighbor = await p.locator('.ts-pk-card .row.near').first().getAttribute('data-k');
        await tapNow(await point(p, `.ts-pk-card .row[data-k="${neighbor}"]`));
        const after = await state(p);
        ok(`${label}: folded stack's nearest topic still opens by native tap`, samePage(before, after) && after.pinned && after.index === Number(neighbor), { before, after });
      }
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors);
      await ctx.close();
    }
  }
} catch (error) {
  console.log(`INVALID touch: ${error.message}`); process.exitCode = 2;
} finally { await browser.close(); }
console.log(`INFO Peek native touch${baseline ? ' frozen-v1 local baseline' : ''}: ${pass} passed, ${fail} failed, ${Math.round((Date.now() - started) / 1000)}s; simulated Chromium touch, not physical-device validation.`);
process.exit(process.exitCode || (fail ? 1 : 0));

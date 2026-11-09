// Read-only rendered placement checks. The oracle uses visible rectangles,
// independent of the runtime's fitting formulas. No runtime/data injection.
// --quick is a diagnostic Thai/Chair subset; the rehearsal uses the full matrix.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!['http://localhost:8090', 'http://localhost:8091', 'https://staging39.thaisolitude.com'].includes(base)) throw new Error('staging39 or isolated localhost only');
const flags = process.argv.slice(3);
if (flags.some(flag => !['--firefox', '--quick'].includes(flag))) throw new Error('Expected only --firefox and/or --quick');
const engine = flags.includes('--firefox') ? 'firefox' : 'chromium', quick = flags.includes('--quick');
const manifest = JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8'));
const pages = manifest.pages.filter(page => page.staging === 'enabled' && (!quick || ['thai-massage', 'chair-massage'].includes(page.slug)));
const start = Date.now();
let pass = 0, fail = 0, captures = 0;
const ok = (name, condition, detail) => {
  condition ? pass++ : fail++;
  console.log(`${condition ? 'PASS' : 'FAIL'} ${name}${!condition && detail ? ' ' + JSON.stringify(detail) : ''}`);
};
const browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
const settle = p => p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
const scroll = async (p, y) => { await p.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), y); await settle(p); };
const openCard = async (p, i) => {
  await p.locator('.ts-pk-rail a').nth(i).evaluate(a => a.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true })));
  await settle(p);
};
const closeCard = async p => { await p.keyboard.press('Escape'); await settle(p); };
const snapshot = p => p.evaluate(() => {
  const c = document.querySelector('.ts-pk-card'), o = c?.querySelector('.open'), sum = o?.querySelector('.sum'), strip = document.querySelector('.ts-pk-top'), head = document.getElementById('masthead');
  const rect = e => { if (!e) return null; const r = e.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, width: r.width, height: r.height }; };
  return { viewport: { width: innerWidth, height: innerHeight }, y: scrollY, hash: location.hash, history: history.length,
    card: rect(c), strip: rect(strip), header: head && getComputedStyle(head).display !== 'none' ? rect(head) : null,
    title: rect(o?.querySelector('b')), hint: rect(o?.querySelector('.hint')), summary: rect(sum), name: o?.querySelector('b')?.textContent,
    pinned: c?.classList.contains('is-pinned'), index: Number(c?.dataset.i),
    rows: [...c.querySelectorAll('.row')].map(row => ({ index: Number(row.dataset.k), visible: getComputedStyle(row).display !== 'none', rect: rect(row) })),
    more: [...c.querySelectorAll('.more')].filter(node => getComputedStyle(node).display !== 'none').map(node => node.textContent),
    items: [...c.querySelectorAll('.sum.is-gist li')].map(li => li.textContent),
    summaryScroll: sum && { top: sum.scrollTop, height: sum.scrollHeight, clientHeight: sum.clientHeight, width: sum.scrollWidth, clientWidth: sum.clientWidth },
    fit: c.className.match(/fit\d/g) || [], stickyTop: parseFloat(getComputedStyle(strip).top) || 0 };
});
const overlaps = (a, b) => a && b && a.width > 0 && a.height > 0 && b.width > 0 && b.height > 0 && a.left < b.right - 1 && a.right > b.left + 1 && a.top < b.bottom - 1 && a.bottom > b.top + 1;
const inside = (r, outer) => r && outer && r.height > 0 && r.width > 0 && r.left >= outer.left - 1 && r.right <= outer.right + 1 && r.top >= outer.top - 1 && r.bottom <= outer.bottom + 1;
const outerKey = async (p, key) => {
  await p.evaluate(() => { window.__peekOuterStable = null; });
  await p.keyboard.press(key);
  // Do not send Home while native End scrolling is still decelerating. Require
  // the endpoint to remain unchanged for several animation frames.
  await p.waitForFunction(key => {
    const c = document.querySelector('.ts-pk-card'), target = key === 'Home' ? 0 : Math.max(0, c.scrollHeight - c.clientHeight), before = window.__peekOuterStable;
    const same = before && Math.abs(before.top - c.scrollTop) < 0.1;
    window.__peekOuterStable = { top: c.scrollTop, frames: same ? before.frames + 1 : 0 };
    return Math.abs(c.scrollTop - target) <= 0.5 && window.__peekOuterStable.frames >= 3;
  }, key, { timeout: 1500 }).catch(() => {});
};
const reachCardEnds = async p => {
  const before = await snapshot(p);
  await p.evaluate(() => { window.__peekReachability = { focus: document.activeElement, top: document.querySelector('.ts-pk-card').scrollTop }; });
  await p.locator('.ts-pk-card').evaluate(card => card.focus({ preventScroll: true }));
  await outerKey(p, 'End');
  await settle(p);
  const bottom = await snapshot(p);
  await outerKey(p, 'Home');
  await settle(p);
  const top = await snapshot(p);
  const titleLines = () => p.evaluate(() => {
    const c = document.querySelector('.ts-pk-card'), title = c.querySelector('.open > b'), r = c.getBoundingClientRect(), range = document.createRange();
    range.selectNodeContents(title);
    return [...range.getClientRects()].filter(line => line.width > 0 && line.height > 0).map(line => line.top >= Math.max(0, r.top) - 1 && line.bottom <= Math.min(innerHeight, r.bottom) + 1 && line.left >= r.left - 1 && line.right <= r.right + 1);
  });
  const seen = await titleLines();
  let steps = 0;
  // A multiline heading can be taller than a constrained card. Show that every
  // actual rendered text line can be read using ordinary outer-card ArrowDown,
  // rather than treating a too-tall aggregate heading rectangle as readable.
  while (seen.some(line => !line) && steps < 12) {
    await p.keyboard.press('ArrowDown'); await p.waitForTimeout(180); await settle(p);
    const visible = await titleLines();
    visible.forEach((line, i) => { seen[i] = seen[i] || line; }); steps++;
  }
  await p.evaluate(() => {
    const saved = window.__peekReachability;
    saved.focus.focus({ preventScroll: true }); document.querySelector('.ts-pk-card').scrollTop = saved.top;
    delete window.__peekReachability;
  }); await settle(p);
  const after = await snapshot(p);
  return { reachable: inside(bottom.hint, bottom.card) && seen.length > 0 && seen.every(Boolean) && bottom.pinned && top.pinned && bottom.y === before.y && top.y === before.y && after.y === before.y && bottom.hash === before.hash && top.hash === before.hash && after.hash === before.hash,
    titleLineProof: { lines: seen.length, seen, arrowSteps: steps },
    before: { y: before.y, hash: before.hash }, bottom: { hint: bottom.hint, card: bottom.card, y: bottom.y }, top: { title: top.title, card: top.card, y: top.y } };
};
const checkCard = async (p, label, row, data, i, allRows = false) => {
  const g = await snapshot(p), viewport = { left: 0, top: 0, right: g.viewport.width, bottom: g.viewport.height };
  const wanted = data.items.filter(item => item[1] === row[i].href.slice(1)).map(item => item[0]);
  ok(`${label}: card clears the actual strip/header and stays on screen`, g.pinned && inside(g.card, viewport) && !overlaps(g.card, g.strip) && !overlaps(g.card, g.header), g);
  const visible = inside(g.title, g.card) && inside(g.hint, g.card) && inside(g.summary, g.card) && inside(g.title, viewport) && inside(g.hint, viewport) && inside(g.summary, viewport);
  // Normal portrait states remain strict. In deliberately constrained states,
  // prove the existing outer-card fallback works with actual keyboard scrolling.
  const reach = !allRows && !visible && inside(g.card, viewport) ? await reachCardEnds(p) : null;
  ok(`${label}: section title, summary and jump hint are ${allRows ? 'visible' : 'visible or keyboard-reachable'}`, (visible || reach?.reachable) && g.summary.height > 0 && g.summaryScroll.width <= g.summaryScroll.clientWidth + 1, { ...g, reach });
  // Portrait fitting may now fold distant rows before shrinking the summary.
  // Require the exact neighboring controls and accurate +N counts when folded;
  // all visible controls must still fit. Wording and identity remain exact.
  const folded = g.rows.some(item => !item.visible);
  const wantedMore = [i > 1 ? `+${i - 1} more above` : null, row.length - i > 2 ? `+${row.length - i - 2} more below` : null].filter(Boolean);
  const rowsFit = g.rows.every(item => item.visible === (!folded || Math.abs(item.index - i) === 1) && (!item.visible || (inside(item.rect, g.card) && inside(item.rect, viewport)))) && JSON.stringify(g.more) === JSON.stringify(folded ? wantedMore : []);
  ok(`${label}: approved words, section identity and topic controls stay intact`, g.index === i && g.name === row[i].label && JSON.stringify(g.items) === JSON.stringify(wanted) && g.rows.length === row.length - 1 && (!allRows || rowsFit), g);
  return g;
};
const shot = async (p, label, geometry) => {
  if (captures >= 18 || engine !== 'chromium') return;
  captures++;
  console.log('INFO clearance capture ' + JSON.stringify({ label, ...geometry }));
  console.log(`INLINE clearance-${label}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 80 })).toString('base64')}`);
};
const book = async p => p.evaluate(() => {
  const card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum');
  window.__peekClearanceBookmark = { card, sum, focus: document.activeElement, top: sum.scrollTop, atEnd: sum.scrollTop > 0 && sum.scrollHeight - sum.scrollTop - sum.clientHeight <= 2, hash: location.hash, name: card.querySelector('.open > b').textContent };
});
const checkBookmark = async (p, label) => {
  const state = await p.evaluate(() => {
    const b = window.__peekClearanceBookmark, card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum');
    const focus = document.activeElement, f = focus.getBoundingClientRect(), c = card.getBoundingClientRect();
    return { sameCard: card === b.card, sameSummary: sum === b.sum, sameFocus: focus === b.focus, sameName: card.querySelector('.open > b').textContent === b.name, sameHash: location.hash === b.hash,
      focusedSummaryVisible: focus !== sum || (f.top >= c.top - 1 && f.bottom <= c.bottom + 1 && f.top >= 0 && f.bottom <= innerHeight), focusTop: f.top, focusBottom: f.bottom, cardTop: c.top, cardBottom: c.bottom,
      expectedTop: b.atEnd ? Math.max(0, sum.scrollHeight - sum.clientHeight) : Math.min(b.top, Math.max(0, sum.scrollHeight - sum.clientHeight)), top: sum.scrollTop };
  });
  ok(`${label}: open DOM, visible focus, summary position and destination survive`, state.sameCard && state.sameSummary && state.sameFocus && state.focusedSummaryVisible && state.sameName && state.sameHash && Math.abs(state.top - state.expectedTop) <= 1, state);
  await book(p);
};
const researchEnd = async (p, label) => {
  const before = await snapshot(p);
  await p.locator('.ts-pk-card .sum').focus(); await p.keyboard.press('End');
  // Native keyboard scrolling can animate even with reduced motion selected.
  // Wait for the requested endpoint, then assert it rather than sampling mid-scroll.
  await p.waitForFunction(() => { const sum = document.querySelector('.ts-pk-card .sum'); return sum.scrollHeight - sum.scrollTop - sum.clientHeight <= 2; }, null, { timeout: 1200 }).catch(() => {});
  await settle(p);
  const after = await p.evaluate(() => {
    const card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum'), last = sum.querySelector('li:last-child'), r = sum.getBoundingClientRect(), l = last.getBoundingClientRect(), c = card.getBoundingClientRect();
    return { end: sum.scrollHeight - sum.scrollTop - sum.clientHeight <= 2, lastVisible: l.top >= Math.max(0, r.top, c.top) - 1 && l.bottom <= Math.min(innerHeight, r.bottom, c.bottom) + 1, faded: sum.classList.contains('has-more'), pinned: card.classList.contains('is-pinned'), y: scrollY, hash: location.hash };
  });
  ok(`${label}: keyboard reaches the final research qualification without a page jump`, after.end && after.lastVisible && !after.faded && after.pinned && after.y === before.y && after.hash === before.hash, after);
};
try {
  for (const page of pages) {
    const data = JSON.parse(readFileSync(new URL(`./peek-data/${page.slug}.json`, import.meta.url), 'utf8'));
    if (data.approval.status !== 'approved') throw new Error(`Expected approved data for ${page.slug}`);
    for (const width of [390, 768, 1280]) {
      const ctx = await browser.newContext({ viewport: { width, height: 844 }, reducedMotion: 'reduce' });
      await ctx.addInitScript(() => { localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', 'right'); });
      const p = await ctx.newPage(), errors = [];
      p.on('pageerror', error => errors.push(error.message));
      const response = await p.goto(`${base}/${page.slug}/`, { waitUntil: 'load' });
      if (!response || response.status() !== 200 || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${page.slug}: response ${response?.status()}; no rendered verdict`);
      await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(p);
      const row = await p.$$eval('.entry-content .ts-jump a', nodes => nodes.map(a => ({ href: a.getAttribute('href'), label: a.textContent.trim() })));
      const rail = await p.$$eval('.ts-pk-rail a', nodes => nodes.map(a => a.getAttribute('href')));
      ok(`${engine}/${page.slug}/${width}: reviewed section shape`, JSON.stringify(row.map(a => a.label)) === JSON.stringify(page.expectedLabels) && JSON.stringify(rail) === JSON.stringify(row.map(a => a.href)), { row, rail });
      const research = row.findIndex(item => item.href === '#' + data.research_anchor);
      if (research < 0) throw new Error(`Missing research destination for ${page.slug}`);
      for (const side of quick ? ['right'] : ['right', 'left']) {
        if (side === 'left') { await openCard(p, 0); await p.locator('.ts-pk-card .side').evaluate(button => button.click()); await settle(p); }
        await scroll(p, 0);
        const gap = await p.evaluate(() => { const s = document.querySelector('.ts-pk-top'); return s.getBoundingClientRect().top - (parseFloat(getComputedStyle(s).top) || 0); });
        const prefix = `${engine}/${page.slug}/${width}/${side}`;
        ok(`${prefix}: initial strip is below its sticky position`, gap > 4, { gap });
        const states = [['top', 0], ['transition', gap / 2], ['pinned', gap + 24]];
        for (const [state, y] of states) {
          await scroll(p, y);
          const indices = quick ? [0, research] : row.map((_, i) => i);
          for (const i of indices) {
            const before = await snapshot(p);
            await openCard(p, i);
            const label = `${prefix}/${state}/${i + 1}`, g = await checkCard(p, label, row, data, i, true);
            ok(`${label}: opening a preview keeps the page and address still`, g.y === before.y && g.hash === before.hash && g.history === before.history, { before: { y: before.y, hash: before.hash }, after: { y: g.y, hash: g.hash } });
            if (state === 'top' && side === 'right' && ((quick && [0, research].includes(i)) || (!quick && ((width === 390 && [0, research].includes(i)) || (width !== 390 && row.length === 6 && i === 0))))) await shot(p, `${page.slug}-${width}-${side}-${state}-${i + 1}`, g);
            if (i === research && state === 'pinned') await researchEnd(p, label);
            await closeCard(p);
          }
        }
        // Keep the same open DOM through reverse scrolling; reopening would hide
        // stale-placement and lost-reading-position defects.
        const carried = [{ i: 0, position: 'start' }, { i: research, position: 'end' }, ...(!quick ? [{ i: research, position: 'middle' }] : [])];
        for (const { i, position } of carried) {
          await scroll(p, gap + 24); await openCard(p, i);
          if (position === 'end') await researchEnd(p, `${prefix}/carry`);
          else if (position === 'middle') {
            await p.locator('.ts-pk-card .sum').evaluate(sum => { sum.focus({ preventScroll: true }); sum.scrollTop = Math.floor((sum.scrollHeight - sum.clientHeight) / 2); }); await settle(p);
          }
          else await p.locator('.ts-pk-card .open').focus();
          await book(p);
          for (const [state, y] of [['transition', gap / 2], ['top', 0], ['pinned', gap + 24]]) {
            await scroll(p, y);
            await checkCard(p, `${prefix}/carry-${position}-${state}/${i + 1}`, row, data, i, true);
            await checkBookmark(p, `${prefix}/carry-${position}-${state}/${i + 1}`);
          }
          const stable = await snapshot(p);
          await scroll(p, gap + 72);
          const moved = await snapshot(p);
          ok(`${prefix}/carry-${position}/${i + 1}: unchanged pinned boundary does not jiggle the card`, Math.abs(moved.card.top - stable.card.top) <= 0.5 && Math.abs(moved.card.height - stable.card.height) <= 0.5, { before: stable.card, after: moved.card });
          await checkBookmark(p, `${prefix}/carry-${position}-stable/${i + 1}`);
          await closeCard(p);
        }
        if (!quick && [390, 768].includes(width)) {
          await scroll(p, gap + 24); await openCard(p, research); await researchEnd(p, `${prefix}/resize`); await book(p);
          const resized = width === 390 ? [{ width: 844, height: 390 }, { width: 844, height: 320 }, { width: 390, height: 844 }] : [{ width: 1280, height: 844 }, { width: 768, height: 844 }];
          for (const viewport of resized) {
            await p.setViewportSize(viewport); await settle(p);
            await checkBookmark(p, `${prefix}/resize-${viewport.width}x${viewport.height}`);
            await checkCard(p, `${prefix}/resize-${viewport.width}x${viewport.height}`, row, data, research);
          }
          await closeCard(p);
          if (width === 390) {
            for (const height of [390, 320]) {
              await p.setViewportSize({ width: 844, height }); await scroll(p, 0);
              const shortGap = await p.evaluate(() => { const strip = document.querySelector('.ts-pk-top'); return strip.getBoundingClientRect().top - (parseFloat(getComputedStyle(strip).top) || 0); });
              for (const [state, y] of [['top', 0], ['transition', shortGap / 2], ['pinned', shortGap + 24]]) {
                await scroll(p, y);
                for (const i of [0, research]) {
                  await openCard(p, i); const g = await checkCard(p, `${prefix}/landscape-${height}-${state}/${i + 1}`, row, data, i);
                  if (page.slug === 'chair-massage' && side === 'right' && state === 'top' && i === 0) await shot(p, `${page.slug}-844x${height}-top-${i + 1}`, g);
                  await closeCard(p);
                }
              }
            }
            await p.setViewportSize({ width, height: 844 }); await settle(p);
          }
          if (row.length === 6 && width === 390) {
            const originalFont = await p.evaluate(() => getComputedStyle(document.documentElement).fontSize);
            await p.evaluate(() => { document.documentElement.style.fontSize = (parseFloat(getComputedStyle(document.documentElement).fontSize) * 2) + 'px'; });
            await settle(p); await openCard(p, research);
            await p.locator('.ts-pk-card .sum').evaluate(sum => { sum.focus({ preventScroll: true }); sum.scrollTop = Math.floor((sum.scrollHeight - sum.clientHeight) / 2); }); await settle(p); await book(p);
            for (const viewport of [{ width: 844, height: 390 }, { width: 844, height: 320 }, { width: 390, height: 844 }]) {
              await p.setViewportSize(viewport); await settle(p);
              await checkBookmark(p, `${prefix}/double-text-kept-${viewport.width}x${viewport.height}`);
              await checkCard(p, `${prefix}/double-text-kept-${viewport.width}x${viewport.height}`, row, data, research);
            }
            await closeCard(p);
            for (const viewport of [{ width: 390, height: 844 }, { width: 844, height: 390 }, { width: 844, height: 320 }]) {
              await p.setViewportSize(viewport); await settle(p);
              await scroll(p, 0);
              const distance = await p.evaluate(() => { const s = document.querySelector('.ts-pk-top'); return s.getBoundingClientRect().top - (parseFloat(getComputedStyle(s).top) || 0) + 40; });
              for (const [state, y] of viewport.width === 390 ? [['pinned', distance]] : [['top', 0], ['pinned', distance]]) {
                await scroll(p, y);
                for (const i of [0, research]) {
                  await openCard(p, i); const label = `${prefix}/double-text-${viewport.width}x${viewport.height}-${state}/${i + 1}`;
                  const g = await checkCard(p, label, row, data, i);
                  if (i === research) await researchEnd(p, label);
                  if (page.slug === 'chair-massage' && side === 'right' && viewport.width === 844 && viewport.height === 390 && state === 'pinned') await shot(p, `${page.slug}-844x390-double-text-${i + 1}`, g);
                  await closeCard(p);
                }
              }
            }
            await p.evaluate(font => { document.documentElement.style.fontSize = font; }, originalFont);
            await p.setViewportSize({ width, height: 844 }); await settle(p);
          }
        }
      }
      ok(`${engine}/${page.slug}/${width}: no JavaScript errors`, errors.length === 0, errors);
      await ctx.close();
    }
  }
} catch (error) {
  console.log(`${/INVALID/.test(error.message) ? '' : 'INVALID '}${error.message}`);
  process.exitCode = 2;
} finally { await browser.close(); }
console.log(`INFO Peek clearance ${engine}${quick ? ' diagnostic subset' : ''}: ${pass} passed, ${fail} failed, ${captures} captures, ${Math.round((Date.now() - start) / 1000)}s; browser simulation does not establish physical-device or assistive-technology support.`);
process.exit(process.exitCode || (fail ? 1 : 0));

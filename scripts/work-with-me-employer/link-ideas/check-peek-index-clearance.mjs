// Index/rail clearance on actual rendered pages. The geometry oracle measures
// the visible header, booking notice and title strip independently of Peek's
// CSS variables and cramped-state classes. Browser resizing is an approximation.
import { createRequire } from 'node:module';
import { INDEX_SLUGS, INDEX_SIDES, INDEX_LIMITATION, indexCases } from './core-index-clearance/contract.mjs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if ((!local && base !== 'https://staging39.thaisolitude.com') || flags.length > 1 || flags.some(flag => !['--firefox', '--webkit'].includes(flag))) throw new Error('Exact staging39 or isolated localhost:8090/8091 only; optional --firefox or --webkit');
const engine = flags.includes('--firefox') ? 'firefox' : flags.includes('--webkit') ? 'webkit' : 'chromium';
let passed = 0, failed = 0, completed = 0, visits = 0, captures = 0, invalid = false, browser;
const started = Date.now();
const ok = (label, value, detail) => {
  value ? passed++ : failed++;
  console.log(`${value ? 'PASS' : 'FAIL'} index-clearance ${engine}/${label} [check ${passed + failed}]${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const settle = async p => { await p.waitForTimeout(100); await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); };
const state = p => p.evaluate(() => ({ x: scrollX, y: scrollY, url: location.href, history: history.length }));
const still = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.url === b.url && a.history === b.history;
const measure = p => p.evaluate(() => {
  const v = visualViewport;
  const viewport = { left: v?.offsetLeft || 0, top: v?.offsetTop || 0, width: v?.width || innerWidth, height: v?.height || innerHeight, scale: v?.scale || 1 };
  viewport.right = viewport.left + viewport.width; viewport.bottom = viewport.top + viewport.height;
  const rect = node => { const r = node.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, width: r.width, height: r.height }; };
  const visible = node => { const r = rect(node), s = getComputedStyle(node); return node.getClientRects().length > 0 && s.display !== 'none' && s.visibility === 'visible' && Number(s.opacity) > 0 && r.right > viewport.left && r.left < viewport.right && r.bottom > viewport.top && r.top < viewport.bottom; };
  const clipped = r => ({ left: Math.max(r.left, viewport.left), right: Math.min(r.right, viewport.right), top: Math.max(r.top, viewport.top), bottom: Math.min(r.bottom, viewport.bottom) });
  const hit = node => {
    const r = rect(node), dx = Math.min(2, r.width / 4), dy = Math.min(2, r.height / 4);
    return [[r.left + dx, (r.top + r.bottom) / 2], [r.right - dx, (r.top + r.bottom) / 2], [(r.left + r.right) / 2, r.top + dy], [(r.left + r.right) / 2, r.bottom - dy], [(r.left + r.right) / 2, (r.top + r.bottom) / 2]].map(([x, y]) => ({ x, y, owned: x >= viewport.left && x < viewport.right && y >= viewport.top && y < viewport.bottom && node.contains(document.elementFromPoint(x, y)) }));
  };
  const icon = document.querySelector('.ts-pk-ix'), nav = document.querySelector('.ts-pk-nav'), rail = document.querySelector('.ts-pk-rail');
  const boxes = [...document.querySelectorAll('#masthead,.ts-corp-band,#ts-peek-title-strip')].map(node => ({ name: node.id || node.className, visible: visible(node), ...rect(node), clip: clipped(rect(node)) }));
  const band = document.querySelector('.ts-corp-band'), bandContent = [];
  if (band) {
    const walker = document.createTreeWalker(band, NodeFilter.SHOW_TEXT);
    for (let n = walker.nextNode(); n; n = walker.nextNode()) {
      if (!n.textContent.trim() || !visible(n.parentElement)) continue;
      const range = document.createRange(); range.selectNodeContents(n);
      for (const r of range.getClientRects()) if (r.width > 0 && r.height > 0 && r.bottom > viewport.top && r.top < viewport.bottom) bandContent.push({ text: n.textContent.trim(), left: r.left, right: r.right, top: r.top, bottom: r.bottom });
    }
  }
  const targets = [...rail.querySelectorAll(':scope > li > :first-child')].map(node => ({ label: node.getAttribute('aria-label'), href: node.getAttribute('href'), tag: node.tagName, visible: visible(node), ...rect(node), hit: hit(node) }));
  return { viewport, index: { ...rect(icon), visible: visible(icon), hit: hit(icon), expanded: icon.getAttribute('aria-expanded') }, nav: { ...rect(nav), visible: visible(nav), visibility: getComputedStyle(nav).visibility, inert: !!nav.closest('[inert]'), className: nav.className }, rail: rect(rail), targets, boxes, bandContent, side: document.documentElement.classList.contains('ts-pk-left') ? 'left' : 'right', page: { x: scrollX, y: scrollY, url: location.href, history: history.length }, contentWidth: document.documentElement.scrollWidth, fallbackRows: [...document.querySelectorAll('.ts-jump')].map(node => ({ visible: visible(node), ...rect(node) })) };
});

async function exerciseIndex(p, context, engine, label, ok, native) {
  const settle = async () => { await p.waitForTimeout(100); await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); };
  const state = () => p.evaluate(() => ({ x: scrollX, y: scrollY, url: location.href, history: history.length, pinned: !!document.querySelector('.ts-pk-card.is-pinned.is-on'), index: Number(document.querySelector('.ts-pk-card')?.dataset.i), expanded: document.querySelector('.ts-pk-ix').getAttribute('aria-expanded'), kept: document.querySelector('.ts-pk-nav').classList.contains('is-kept') }));
  const still = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.url === b.url && a.history === b.history;
  const source = (await readSource(p)).labels;
  if (source.length < 1) throw new Error('Expected an original page destination');
  const point = async (selector, hit = true) => p.locator(selector).evaluate((node, hit) => { const r = node.getBoundingClientRect(), x = (r.left + r.right) / 2, y = (r.top + r.bottom) / 2; if (r.width < 2 || r.height < 2 || x < 0 || y < 0 || x >= innerWidth || y >= innerHeight || (hit && !node.contains(document.elementFromPoint(x, y)))) throw new Error('Input target unavailable: ' + node.className); return { x, y }; }, hit);
  const cdp = engine === 'chromium' && native ? await context.newCDPSession(p) : null;
  const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
  const tap = async selector => { const at = await point(selector); await touch('touchStart', at); await p.waitForTimeout(35); await touch('touchEnd'); await settle(); };
  const close = async () => { await p.keyboard.press('Escape'); await settle(); await p.waitForTimeout(460); };
  await close();
  let before = await state(); await p.locator('.ts-pk-ix').focus(); await p.keyboard.press('Enter'); await settle();
  let after = await state();
  ok(`${label}/keyboard: Enter keeps every original ordered name open without scrolling`, after.expanded === 'true' && after.kept && still(before, after) && JSON.stringify(await p.locator('.ts-pk-fan li > span').allTextContents()) === JSON.stringify(source), { before, after, source });
  await p.keyboard.press('Escape'); await settle();
  ok(`${label}/keyboard: Escape closes names and restores icon focus`, await p.locator('.ts-pk-ix').evaluate(node => document.activeElement === node && node.getAttribute('aria-expanded') === 'false'));
  if (!cdp) return;
  try {
    await close(); before = await state(); await tap('.ts-pk-ix'); after = await state();
    ok(`${label}/touch: quick icon tap keeps the index open without navigation`, after.expanded === 'true' && after.kept && still(before, after), { before, after });
    await close(); before = await state();
    let start = await point('.ts-pk-ix'); await touch('touchStart', start); await p.waitForTimeout(2100); await touch('touchEnd'); await settle(); after = await state();
    ok(`${label}/touch: two-second hold keeps names open without navigation`, after.expanded === 'true' && after.kept && still(before, after), { before, after });
    await close(); before = await state(); start = await point('.ts-pk-ix'); await touch('touchStart', start); await p.waitForTimeout(150);
    const name = await point('.ts-pk-fan li:last-child', false);
    // Move into the name column before moving down, preserving the separate
    // straight-down route from the icon onto the rail.
    await touch('touchMove', { x: name.x, y: start.y });
    for (let step = 1; step <= 6; step++) { await touch('touchMove', { x: name.x, y: start.y + (name.y - start.y) * step / 6 }); await p.waitForTimeout(16); }
    await touch('touchEnd'); await settle(); after = await state();
    ok(`${label}/touch: slide to a name and lift keeps its preview without navigation`, still(before, after) && after.pinned && after.index === source.length - 1, { before, after });
    await close(); before = await state(); start = await point('.ts-pk-ix');
    const end = await point('.ts-pk-rail li:last-child > :first-child');
    await touch('touchStart', start); await p.waitForTimeout(150);
    for (let step = 1; step <= 10; step++) { await touch('touchMove', { x: start.x, y: start.y + (end.y - start.y) * step / 10 }); await p.waitForTimeout(16); }
    await touch('touchEnd'); await settle(); after = await state();
    ok(`${label}/touch: straight down from index to rail previews the last section without navigation`, still(before, after) && after.pinned && after.index === source.length - 1, { before, after });
    await close();
  } finally { await cdp.detach(); }
}
const readSource = p => p.evaluate(() => {
  const main = document.querySelector('#main,main,[role="main"]') || document.querySelector('#primary,#content') || document.body;
  const entries = main.querySelectorAll('.entry-content'), content = entries.length === 1 ? entries[0] : main;
  const row = content.querySelector('.ts-jump'), config = JSON.parse(document.querySelector('#ts-peek-nav-config').textContent);
  const norm = text => text.replace(/\s+/g, ' ').trim();
  const visible = node => {
    if (!content.contains(node) || node.closest('nav,header,footer,dialog,[role="navigation"],[role="banner"],[role="contentinfo"],[role="dialog"],.ts-pk,.ts-jump,[hidden],[inert],[aria-hidden="true"],.screen-reader-text,.sr-only')) return false;
    const r = node.getBoundingClientRect(); if (r.width < 2 || r.height < 2) return false;
    for (let at = node; at && content.contains(at); at = at.parentElement) {
      const style = getComputedStyle(at), ar = at.getBoundingClientRect();
      if (style.display === 'none' || ['hidden', 'collapse'].includes(style.visibility) || style.contentVisibility === 'hidden') return false;
      if (at.tagName === 'DETAILS' && !at.open && !at.querySelector('summary')?.contains(node)) return false;
      if (at !== node && ['hidden', 'clip'].includes(style.overflowY) && (at.clientHeight < 2 || r.top >= ar.bottom || r.bottom <= ar.top)) return false;
    }
    return true;
  };
  let items = row ? [...row.querySelectorAll('a[href^="#"]')].map(a => { let id = a.hash.slice(1); try { id = decodeURIComponent(id); } catch {} return { node: document.getElementById(id), label: norm(a.textContent) }; }).filter(item => item.node) : [...content.querySelectorAll('h2')].filter(visible).map(node => ({ node, label: norm(node.innerText) }));
  items.sort((a, b) => a.node === b.node ? 0 : a.node.compareDocumentPosition(b.node) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1);
  const labels = config.mode === 'fixed' || !items.length ? [config.title] : items.map(item => item.label);
  return { data: document.getElementById('ts-peek-nav-data')?.textContent || null, links: [...content.querySelectorAll('.ts-jump a')].map(a => [a.getAttribute('href'), a.textContent]), headings: [...content.querySelectorAll('h1,h2')].filter(node => !node.closest('.ts-pk')).map(node => [node.id, node.textContent]), count: labels.length, labels };
});
const checkGeometry = async (p, label, count) => {
  const g = await measure(p), v = g.viewport;
  // Each rail target keeps22px; the tightest layout may use2px gaps,4px
  // padding at each end,44px icon,8px icon gap and12px at both screen edges.
  const ceiling = Math.max(v.top, ...g.boxes.filter(box => box.visible).map(box => box.clip.bottom));
  const required = 12 + 44 + 8 + count * 22 + Math.max(0, count - 1) * 2 + 8 + 12;
  const room = v.bottom - ceiling, fits = room >= required - .1;
  const inside = r => r.left >= v.left - 1 && r.right <= v.right + 1 && r.top >= v.top - 1 && r.bottom <= v.bottom + 1;
  const intersects = (a, b) => Math.min(a.right, b.right) - Math.max(a.left, b.left) > .5 && Math.min(a.bottom, b.bottom) - Math.max(a.top, b.top) > .5;
  const detail = { ...g, ceiling, required, room, fits };
  ok(`${label}: original destination count and chosen side remain intact`, g.targets.length === count && g.side === label.split('/')[1], detail);
  ok(`${label}: viewport stays at normal scale without horizontal overflow`, Math.abs(v.scale - 1) < .001 && Math.abs(v.left) <= 1 && Math.abs(v.top) <= 1 && g.contentWidth <= v.width + 1, detail);
  if (fits) {
    ok(`${label}: idle controls are available whenever their minimum sizes fit`, g.nav.visible && !g.nav.inert && g.index.visible, detail);
    ok(`${label}: the complete44px index clears header, booking notice and strip`, g.index.width >= 44 - 1 && g.index.height >= 44 - 1 && inside(g.index) && g.index.top >= ceiling + 10 && g.boxes.filter(box => box.visible).every(box => !intersects(g.index, box.clip)), detail);
    ok(`${label}: all rail targets retain22px and stay visible below the icon`, g.targets.every(target => target.visible && target.height >= 22 - .2 && inside(target) && target.top >= g.index.bottom + 7 && target.bottom <= v.bottom - 11), detail);
    ok(`${label}: the index and every rail target own their visible center and edge hits`, g.index.hit.every(point => point.owned) && g.targets.every(target => target.hit.every(point => point.owned)), detail);
  } else {
    ok(`${label}: impossible idle layout temporarily hides and inerts only the navigation`, !g.nav.visible && g.nav.visibility === 'hidden' && g.nav.inert && !g.index.visible, detail);
    const unchanged = await p.evaluate(() => ({ strip: getComputedStyle(document.querySelector('#ts-peek-title-strip')).visibility, rows: [...document.querySelectorAll('.ts-jump')].map(row => getComputedStyle(row).display) }));
    ok(`${label}: cramped navigation keeps the title strip and existing jump-row state`, unchanged.strip === 'visible' && unchanged.rows.every(display => display === 'none'), unchanged);
  }
  console.log(`INFO index-clearance geometry ${engine}/${label}: ${JSON.stringify(detail)}`);
  return detail;
};
const capture = async (p, slug, side, kind) => {
  const file = `peek-index-clearance-${engine}-${slug}-${side}-${kind}.jpg`;
  console.log(`INFO index-clearance capture ${file}: ${JSON.stringify(await measure(p))}`);
  console.log(`INLINE ${file} base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`); captures++;
};
const idle = async p => {
  await p.keyboard.press('Escape');
  await p.evaluate(() => { if (document.activeElement instanceof HTMLElement) document.activeElement.blur(); });
  await settle(p);
};
const setView = async (p, width, height, y = 0) => {
  await idle(p); await p.setViewportSize({ width, height });
  await p.evaluate(y => scrollTo({ top: y, behavior: 'instant' }), y); await settle(p);
};
const menuClearance = async (p, label, count) => {
  for (const height of [620, 500]) {
    await setView(p, 390, height, 0);
    const toggle = p.locator('#masthead .menu-toggle:visible').first();
    if (!await toggle.count()) throw new Error(`${label}: expected original mobile menu toggle`);
    await toggle.click(); await settle(p);
    const open = await toggle.getAttribute('aria-expanded');
    ok(`${label}/menu-${height}: original hamburger opens after the viewport changes`, open === 'true', { expanded: open });
    if (open !== 'true') throw new Error(`${label}: original menu did not open`);
    await checkGeometry(p, `${label}/menu-${height}-open`, count);
    await toggle.click(); await settle(p);
    ok(`${label}/menu-${height}: original hamburger closes without losing its own hit target`, await toggle.getAttribute('aria-expanded') === 'false' && await toggle.evaluate(node => { const r = node.getBoundingClientRect(); return node.contains(document.elementFromPoint((r.left + r.right) / 2, (r.top + r.bottom) / 2)); }));
    await idle(p); await checkGeometry(p, `${label}/menu-${height}-closed`, count);
  }
};
const firstHint = async (p, label, count) => {
  const readingY = await p.locator('.ts-jump a[href^="#"]').first().evaluate(a => { const target = document.getElementById(decodeURIComponent(a.hash.slice(1))); if (!target) throw new Error('Hint test needs an original section'); return scrollY + target.getBoundingClientRect().top + 40; });
  await setView(p, 844, 180, readingY); await p.waitForTimeout(1750);
  const deferred = await p.evaluate(() => ({ seen: localStorage.getItem('ts-pk-tip'), hidden: document.querySelector('.ts-pk-tip').hidden, inert: document.querySelector('.ts-pk-nav').inert }));
  ok(`${label}/first-hint: insufficient room defers the hint without recording it as seen`, deferred.inert && deferred.hidden && deferred.seen !== 'seen', deferred);
  await setView(p, 390, 844, readingY); await p.waitForTimeout(1750);
  const shown = await p.evaluate(() => ({ seen: localStorage.getItem('ts-pk-tip'), visible: !document.querySelector('.ts-pk-tip').hidden && document.querySelector('.ts-pk-tip').classList.contains('is-on'), inert: document.querySelector('.ts-pk-nav').inert }));
  ok(`${label}/first-hint: the still-unseen hint appears once navigation has room`, shown.seen === 'seen' && shown.visible && !shown.inert, shown);
  if (!shown.visible) throw new Error(`${label}: deferred first-time hint never appeared`);
  await p.locator('.ts-pk-tip .ok').focus(); await p.setViewportSize({ width: 844, height: 180 }); await settle(p);
  const held = await p.evaluate(() => ({ focused: document.querySelector('.ts-pk-tip').contains(document.activeElement), hidden: document.querySelector('.ts-pk-tip').hidden, inert: document.querySelector('.ts-pk-nav').inert }));
  ok(`${label}/first-hint: a focused hint keeps its navigation available through resize`, held.focused && !held.hidden && !held.inert, held);
  await p.evaluate(() => document.activeElement?.blur()); await p.waitForTimeout(350); await settle(p);
  ok(`${label}/first-hint: releasing hint focus allows cramped controls and hint to defer`, await p.evaluate(() => document.querySelector('.ts-pk-tip').hidden && document.querySelector('.ts-pk-nav').inert));
  await setView(p, 390, 844, 0);
};
const cardFocusReturn = async (p, label) => {
  const originalSide = await p.evaluate(() => document.documentElement.classList.contains('ts-pk-left'));
  for (const action of ['Escape', 'Close', 'Side']) {
    await setView(p, 390, 620, 0);
    await p.locator(action === 'Side' ? '.ts-pk-rail li:nth-child(2) > :first-child' : '.ts-pk-rail li:last-child > :first-child').focus(); await p.keyboard.press('Enter'); await settle(p);
    await p.locator('.ts-pk-card').focus();
    const original = await p.evaluate(() => { const main = document.querySelector('#main,main,[role="main"]') || document.querySelector('.ts-pk-title-content'); window.__peekIndexReturn = main; return { tabindex: main.getAttribute('tabindex'), hadTabindex: main.hasAttribute('tabindex') }; });
    await p.setViewportSize(action === 'Side' ? { width: 320, height: 420 } : { width: 844, height: 342 }); await settle(p);
    const cramped = await p.evaluate(() => ({ inert: document.querySelector('.ts-pk-nav').inert, focusedCard: document.querySelector('.ts-pk-card').contains(document.activeElement) }));
    if (!cramped.inert || !cramped.focusedCard) throw new Error(`${label}/${action}: focused-card return test did not reach hidden navigation`);
    const before = await state(p);
    if (action === 'Escape') await p.keyboard.press('Escape');
    else { const button = p.locator(action === 'Close' ? '.ts-pk-card .x' : '.ts-pk-card .side'); if (!await button.isVisible()) throw new Error(`${label}/${action}: intended card button is hidden`); await button.focus(); if (!await button.evaluate(node => document.activeElement === node)) throw new Error(`${label}/${action}: intended button did not acquire focus`); await p.keyboard.press('Enter'); }
    await p.waitForTimeout(300); await settle(p);
    const after = await p.evaluate(() => { const main = window.__peekIndexReturn, r = main.getBoundingClientRect(); return { focused: document.activeElement === main, connected: main.isConnected, inert: !!main.closest('[inert]'), tabindex: main.getAttribute('tabindex'), visible: getComputedStyle(main).visibility === 'visible' && r.width > 0 && r.bottom > 0 && r.top < innerHeight, cardClosed: !document.querySelector('.ts-pk-card').classList.contains('is-on'), left: document.documentElement.classList.contains('ts-pk-left') }; });
    ok(`${label}/card-${action}: closing with hidden navigation returns focus to the original reading region`, after.focused && after.connected && !after.inert && after.visible && after.cardClosed && (original.hadTabindex ? after.tabindex === original.tabindex : after.tabindex === '-1') && still(before, await state(p)), { original, before, after, state: await state(p) });
    await p.evaluate(() => window.__peekIndexReturn.blur()); await settle(p);
    ok(`${label}/card-${action}: temporary reading-region tabindex is removed on blur`, await p.evaluate(original => { const main = window.__peekIndexReturn; return main.hasAttribute('tabindex') === original.hadTabindex && main.getAttribute('tabindex') === original.tabindex; }, original), original);
    if (action === 'Side') {
      ok(`${label}/card-Side: side action still changes sides`, after.left !== originalSide, after);
      await setView(p, 390, 620, 0); await p.locator('.ts-pk-rail li:first-child > :first-child').focus(); await p.keyboard.press('Enter'); await settle(p);
      const button = p.locator('.ts-pk-card .side'); if (!await button.isVisible()) throw new Error(`${label}: side restoration button is hidden`); await button.focus(); if (!await button.evaluate(node => document.activeElement === node)) throw new Error(`${label}: side restoration did not acquire focus`); await p.keyboard.press('Enter'); await idle(p);
      ok(`${label}/card-Side: the opposite-side control restores the original side`, await p.evaluate(() => document.documentElement.classList.contains('ts-pk-left')) === originalSide);
    }
  }
};
const preserveCard = async (p, label, summary) => {
  await setView(p, 390, 620, 0);
  const before = await state(p);
  await p.locator('.ts-pk-rail li:last-child > :first-child').focus(); await p.keyboard.press('Enter'); await settle(p);
  await p.evaluate(() => { window.__peekIndexCard = document.querySelector('.ts-pk-card'); window.__peekIndexSummary = document.querySelector('.ts-pk-card .sum'); if (window.__peekIndexSummary) window.__peekIndexSummary.scrollTop = Math.min(40, (window.__peekIndexSummary.scrollHeight - window.__peekIndexSummary.clientHeight) / 2); document.activeElement?.blur(); });
  await settle(p);
  const snapshot = await p.evaluate(() => ({ inner: document.querySelector('.ts-pk-card .sum')?.scrollTop, index: document.querySelector('.ts-pk-card').dataset.i, text: document.querySelector('.ts-pk-card .open > b').textContent, summary: document.querySelector('.ts-pk-card .sum')?.textContent }));
  await p.setViewportSize({ width: 844, height: 342 }); await settle(p);
  const after = await p.evaluate(() => {
    const card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum'), r = card.getBoundingClientRect();
    return { sameCard: card === window.__peekIndexCard, sameSummary: sum === window.__peekIndexSummary, inner: sum?.scrollTop, innerMax: sum ? sum.scrollHeight - sum.clientHeight : null, index: card.dataset.i, text: card.querySelector('.open > b').textContent, summary: sum?.textContent, visible: card.classList.contains('is-pinned') && card.classList.contains('is-on') && r.width > 0 && r.height > 0 && getComputedStyle(card).visibility === 'visible', top: r.top, bottom: r.bottom, viewport: innerHeight, rows: [...card.querySelectorAll('.row')].map(node => Number(node.dataset.k)), count: document.querySelectorAll('.ts-pk-rail li').length };
  });
  ok(`${label}/open-card: resizing into a cramped viewport preserves the same open destination and card`, after.sameCard && after.visible && after.index === snapshot.index && after.text === snapshot.text && after.top >= -1 && after.bottom <= after.viewport + 1, { before, snapshot, after });
  ok(`${label}/open-card: page address and scroll stay unchanged during the resize`, still(before, await state(p)), { before, after: await state(p) });
  if (summary) ok(`${label}/open-card: approved summary node, reading offset and immediate previous neighbour survive`, after.sameSummary && after.summary === snapshot.summary && Math.abs(after.inner - Math.min(snapshot.inner, after.innerMax)) <= 2 && JSON.stringify(after.rows) === JSON.stringify([after.count - 2]), after);
  await idle(p);
};
const releasedOutside = async (p, context, label, count) => {
  await setView(p, 390, 620, 0);
  await p.locator('.ts-pk-ix').focus(); await p.keyboard.press('Enter'); await settle(p);
  await p.evaluate(() => document.activeElement?.blur());
  const name = await p.locator('.ts-pk-fan li:last-child').boundingBox();
  if (!name) throw new Error(`${label}: kept index name missing before outside-release check`);
  await p.mouse.move(name.x + name.width / 2, name.y + name.height / 2); await p.mouse.down();
  await p.setViewportSize({ width: 844, height: 342 }); await settle(p);
  await p.mouse.move(420, 8); await p.mouse.up(); await idle(p);
  await checkGeometry(p, `${label}/fan-released-outside`, count);
  if (engine !== 'chromium') return;
  const cdp = await context.newCDPSession(p);
  try {
    await setView(p, 390, 620, 0);
    const target = await p.locator('.ts-pk-rail li:first-child > :first-child').evaluate(node => { const r = node.getBoundingClientRect(); return { x: (r.left + r.right) / 2, y: (r.top + r.bottom) / 2 }; });
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ ...target, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
    await p.setViewportSize({ width: 844, height: 342 }); await settle(p);
    ok(`${label}/rail-cancel: a pressed rail stays available until cancellation`, await p.locator('.ts-pk-nav').evaluate(node => !node.inert && getComputedStyle(node).visibility === 'visible'));
    await cdp.send('Input.dispatchTouchEvent', { type: 'touchCancel', touchPoints: [] }); await idle(p);
    await checkGeometry(p, `${label}/rail-cancel-released`, count);
  } finally { await cdp.detach(); }
};
const preserveActiveInput = async (p, context, label) => {
  if (engine !== 'chromium') return;
  const cdp = await context.newCDPSession(p);
  const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
  const point = () => p.locator('.ts-pk-ix').evaluate(node => { const r = node.getBoundingClientRect(), x = (r.left + r.right) / 2, y = (r.top + r.bottom) / 2; if (!node.contains(document.elementFromPoint(x, y))) throw new Error('Index is not hit-testable before gesture'); return { x, y }; });
  const active = () => p.locator('.ts-pk-ix').evaluate(node => ({ visible: getComputedStyle(node).visibility === 'visible', inert: !!node.closest('[inert]'), kept: node.parentElement.classList.contains('is-kept'), expanded: node.getAttribute('aria-expanded') }));
  try {
    await setView(p, 390, 620, 0);
    const before = await state(p);
    await touch('touchStart', await point()); await p.waitForTimeout(150);
    await p.setViewportSize({ width: 844, height: 342 }); await settle(p);
    const held = await active();
    ok(`${label}/active-touch: pressed index survives a resize into insufficient room`, held.visible && !held.inert && still(before, await state(p)), { held, before, after: await state(p) });
    await p.waitForTimeout(200); await touch('touchEnd'); await idle(p);
    await checkGeometry(p, `${label}/touch-released`, await p.locator('.ts-pk-rail li').count());
    await setView(p, 390, 620, 0);
    await touch('touchStart', await point()); await p.waitForTimeout(35); await touch('touchEnd'); await settle(p);
    await p.evaluate(() => document.activeElement?.blur());
    await p.setViewportSize({ width: 844, height: 342 }); await settle(p);
    const kept = await active();
    ok(`${label}/kept-index: an intentionally kept-open index survives insufficient room`, kept.visible && !kept.inert && kept.kept && kept.expanded === 'true', kept);
    await idle(p); await checkGeometry(p, `${label}/kept-index-closed`, await p.locator('.ts-pk-rail li').count());
  } finally { await cdp.detach(); }
};
const preserveFocus = async (p, label) => {
  await setView(p, 390, 620, 0);
  const icon = p.locator('.ts-pk-ix'); await icon.focus(); await settle(p);
  await p.setViewportSize({ width: 844, height: 342 }); await settle(p);
  const held = await icon.evaluate(node => ({ focused: document.activeElement === node, inert: !!node.closest('[inert]'), visibility: getComputedStyle(node).visibility }));
  ok(`${label}/focus: a focused index is not hidden or made inert during resize`, held.focused && !held.inert && held.visibility === 'visible', held);
  await icon.evaluate(node => node.blur()); await settle(p);
};
try {
  browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  for (const slug of INDEX_SLUGS) for (const side of INDEX_SIDES) {
    const context = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 1, isMobile: engine !== 'firefox', hasTouch: true, reducedMotion: 'reduce', colorScheme: side === 'left' ? 'dark' : 'light' });
    try {
      await context.addInitScript(({ side, slug }) => { localStorage.setItem('ts-pk-side', side); if (slug === 'thai-massage') localStorage.removeItem('ts-pk-tip'); else localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-theme', side === 'left' ? 'dark' : 'light'); }, { side, slug });
      const p = await context.newPage(), errors = [], label = `${slug}/${side}`;
      p.on('pageerror', error => errors.push(error.message));
      const path = slug === 'search' ? '/?s=massage' : `/${slug}/`;
      const response = await p.goto(base + path, { waitUntil: 'load', timeout: 45000 });
      if (response?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`${label}: expected same-origin HTTP200; got${response?.status()}; stop without further loads`);
      visits++;
      await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(p);
      const source = await readSource(p);
      if (source.count < 1 || (slug === 'search' && source.count !== 1)) throw new Error(`${label}: missing expected source destinations`);
      if (slug === 'thai-massage') await firstHint(p, label, source.count);
      for (const spec of [
        ['tall', 390, 844, 0], ['address-bar', 390, 620, 0], ['sticky-header', 390, 501, 0], ['short-portrait', 390, 500, 0], ['narrow', 320, 420, 0],
        ['landscape-top', 844, 342, 0], ['landscape-transition', 844, 342, 100], ['landscape-reading', 844, 342, 600]
      ]) {
        const [kind, width, height, y] = spec;
        await setView(p, width, height, y); await checkGeometry(p, `${label}/${kind}`, source.count);
        if (['short-portrait', 'landscape-top'].includes(kind)) await capture(p, slug, side, kind);
      }
      await setView(p, 390, 620, 0); await checkGeometry(p, `${label}/restored`, source.count);
      await exerciseIndex(p, context, engine, label, ok, ['thai-massage', 'therapeutic-massage', 'privacy-policy'].includes(slug));
      if (['thai-massage', 'therapeutic-massage', 'privacy-policy'].includes(slug)) await menuClearance(p, label, source.count);
      await preserveCard(p, label, !!source.data);
      if (slug === 'thai-massage') await cardFocusReturn(p, label);
      if (slug === 'thai-massage') { await preserveActiveInput(p, context, label); await releasedOutside(p, context, label, source.count); }
      await preserveFocus(p, label);
      await checkGeometry(p, `${label}/focus-released`, source.count);
      await setView(p, 390, 844, 0);
      const finalSource = await readSource(p);
      ok(`${label}: content, wording, original links and destination count remain unchanged`, JSON.stringify(source) === JSON.stringify(finalSource), { source, finalSource });
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors);
      completed++; console.log(`INFO index-clearance completed ${engine}/${label}`);
    } finally { await context.close(); }
    if (!local) await new Promise(resolve => setTimeout(resolve, 5500));
  }
} catch (error) { invalid = true; console.log(`INVALID index-clearance ${engine}: ${error.message}`); }
finally { if (browser) await browser.close(); }
console.log(`INFO Peek index clearance ${engine}: ${passed} passed, ${failed} failed; ${completed}/${indexCases().length} cases, ${visits} page loads, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s; ${INDEX_LIMITATION}`);
process.exit(invalid ? 2 : failed ? 1 : 0);

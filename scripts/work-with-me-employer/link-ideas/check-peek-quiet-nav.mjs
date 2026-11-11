// Quiet rail reading, a boxed index toggle, and the original mobile menu's
// outside-touch boundary. All navigation input below is trusted browser input.
// The retained index-clearance suite separately covers the full geometry and
// existing gestures; these checks add only the new behavior and its conflicts.
import { createRequire } from 'node:module';
import { QUIET_SLUGS, QUIET_SIDES, QUIET_LIMITATION, quietCases } from './core-quiet-nav/contract.mjs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if ((!local && base !== 'https://staging39.thaisolitude.com') || flags.length > 1 || flags.some(flag => !['--firefox', '--webkit'].includes(flag))) throw new Error('Exact staging39 or isolated localhost:8090/8091 only; optional --firefox or --webkit');
const engine = flags.includes('--firefox') ? 'firefox' : flags.includes('--webkit') ? 'webkit' : 'chromium';
const started = Date.now();
let passed = 0, failed = 0, completed = 0, visits = 0, captures = 0, invalid = false, browser;
const ok = (label, value, detail) => {
  value ? passed++ : failed++;
  console.log(`${value ? 'PASS' : 'FAIL'} quiet-nav ${engine}/${label} [check ${passed + failed}]${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const settle = async p => { await p.waitForTimeout(180); await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); };
const state = p => p.evaluate(() => {
  const nav = document.querySelector('.ts-pk-nav'), icon = nav.querySelector('.ts-pk-ix'), strip = document.querySelector('#ts-peek-title-strip'), card = document.querySelector('.ts-pk-card');
  const visible = node => { if (!node) return false; const s = getComputedStyle(node), r = node.getBoundingClientRect(); return s.display !== 'none' && s.visibility === 'visible' && Number(s.opacity) > .05 && r.width > 0 && r.height > 0; };
  const current = [...nav.querySelectorAll('.ts-pk-rail li > :first-child')].findIndex(node => node.getAttribute('aria-current') === 'true');
  return { x: scrollX, y: scrollY, url: location.href, history: history.length, marker: nav.dataset.tsPkUi, current,
    fills: [...nav.querySelectorAll('.ts-pk-rail i')].map(node => node.style.getPropertyValue('--p')),
    dot: nav.querySelector('.ts-pk-you').style.top, title: strip.querySelector('.tr .is-now')?.textContent || '', titleBefore: strip.classList.contains('is-before'), shift: strip.querySelector('.tr').style.transform,
    labelsVisible: [...document.querySelectorAll('.ts-pk-label')].filter(visible).length, labelSeen: !!window.__quietLabelSeen,
    expanded: icon.getAttribute('aria-expanded'), name: icon.getAttribute('aria-label'), kept: nav.classList.contains('is-kept'), focused: document.activeElement === icon,
    list: visible(icon.querySelector('.ts-pk-ix-list')), close: visible(icon.querySelector('.ts-pk-ix-close')), closePath: icon.querySelector('.ts-pk-ix-close')?.getAttribute('d'),
    card: { on: card.classList.contains('is-on'), pinned: card.classList.contains('is-pinned'), index: Number(card.dataset.i), title: card.querySelector('.open > b')?.textContent || '', label: card.querySelector('.open')?.getAttribute('aria-label') || '', summary: card.querySelector('.sum')?.textContent || '' } };
});
const still = (a, b) => Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1 && a.url === b.url && a.history === b.history;
const point = (p, selector) => p.locator(selector).first().evaluate(node => {
  const r = node.getBoundingClientRect(), x = (r.left + r.right) / 2, y = (r.top + r.bottom) / 2;
  if (r.width < 2 || r.height < 2 || x < 0 || y < 0 || x >= innerWidth || y >= innerHeight || !node.contains(document.elementFromPoint(x, y))) throw new Error('Input target unavailable: ' + node.className);
  return { x, y };
});
const tap = async (p, selector) => { const at = typeof selector === 'string' ? await point(p, selector) : selector; await p.touchscreen.tap(at.x, at.y); await settle(p); };
const trace = async (p, label) => { const rows = await p.evaluate(() => { const rows = window.__quietEvents.splice(0); return rows; }); console.log(`INFO quiet-nav input ${engine}/${label}: ${JSON.stringify(rows)}`); return rows; };
const clearTrace = p => p.evaluate(() => { window.__quietEvents.length = 0; });
const clean = async p => { await p.keyboard.press('Escape'); await p.evaluate(() => document.activeElement?.blur()); await p.waitForTimeout(650); await settle(p); };
const source = p => p.evaluate(() => {
  const main = document.querySelector('#main,main,[role="main"]') || document.querySelector('#primary,#content') || document.body;
  const entries = main.querySelectorAll('.entry-content'), content = entries.length === 1 ? entries[0] : main;
  const row = content.querySelector('.ts-jump'), config = JSON.parse(document.querySelector('#ts-peek-nav-config').textContent), norm = t => t.replace(/\s+/g, ' ').trim();
  const visible = node => {
    if (!content.contains(node) || node.closest('nav,header,footer,dialog,[role="navigation"],[hidden],[inert],[aria-hidden="true"],.screen-reader-text,.sr-only,.ts-pk,.ts-jump')) return false;
    const r = node.getBoundingClientRect(); if (r.width < 2 || r.height < 2) return false;
    for (let at = node; at && content.contains(at); at = at.parentElement) {
      const s = getComputedStyle(at), ar = at.getBoundingClientRect();
      if (s.display === 'none' || ['hidden', 'collapse'].includes(s.visibility) || s.contentVisibility === 'hidden') return false;
      if (at.tagName === 'DETAILS' && !at.open && !at.querySelector('summary')?.contains(node)) return false;
      if (at !== node && ['hidden', 'clip'].includes(s.overflowY) && (at.clientHeight < 2 || r.top >= ar.bottom || r.bottom <= ar.top)) return false;
    }
    return true;
  };
  let items = row ? [...row.querySelectorAll('a[href^="#"]')].map(a => { let id = a.hash.slice(1); try { id = decodeURIComponent(id); } catch {} return { node: document.getElementById(id), label: norm(a.textContent) }; }).filter(item => item.node) : [...content.querySelectorAll('h2')].filter(visible).map(node => ({ node, label: norm(node.innerText) }));
  items.sort((a, b) => a.node === b.node ? 0 : a.node.compareDocumentPosition(b.node) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1);
  if (config.mode === 'fixed' || !items.length) items = [{ node: [...content.children].find(visible) || content, label: config.title }];
  window.__quietTargets = items.map(item => item.node);
  return { labels: items.map(item => item.label), summary: document.querySelector('#ts-peek-nav-data')?.textContent || null,
    headings: [...content.querySelectorAll('h1,h2,h3')].filter(node => !node.closest('.ts-pk')).map(node => [node.id, node.textContent]),
    links: [...content.querySelectorAll('a[href]')].filter(node => !node.closest('.ts-pk')).map(node => [node.getAttribute('href'), node.textContent]),
    menu: [...document.querySelectorAll('#masthead a[href]')].map(node => [node.getAttribute('href'), node.textContent]) };
});

async function readPage(p, label, original) {
  await clean(p);
  const before = await state(p);
  const goal = await p.evaluate(() => { const nodes = window.__quietTargets, target = nodes[Math.min(1, nodes.length - 1)]; return Math.max(300, scrollY + target.getBoundingClientRect().top - innerHeight * .25 + 60); });
  // Trusted keyboard page input also works in mobile WebKit, which does not
  // expose mouse-wheel input. Restore the body's exact tabindex afterward.
  const body = await p.locator('body').evaluate(node => { const saved = node.getAttribute('tabindex'); node.setAttribute('tabindex', '-1'); node.focus({ preventScroll: true }); return saved; });
  await clearTrace(p);
  try {
    for (let i = 0; i < 35; i++) {
      const y = await p.evaluate(() => scrollY);
      await p.keyboard.press('PageDown'); await p.waitForTimeout(180);
      const next = await p.evaluate(() => scrollY);
      if (next >= goal || next <= y + 1) break;
    }
  } finally { await p.locator('body').evaluate((node, old) => { node.blur(); if (old === null) node.removeAttribute('tabindex'); else node.setAttribute('tabindex', old); }, body); }
  await settle(p);
  const after = await state(p), events = await trace(p, `${label}/reading`);
  ok(`${label}/reading/trusted page input moves the original page`, after.y > before.y + 40 && events.some(e => e.type === 'keydown' && e.key === 'PageDown' && e.trusted), { before, after, goal });
  ok(`${label}/reading/automatic scrolling never displays a floating rail label`, !after.labelSeen && after.labelsVisible === 0, after);
  ok(`${label}/reading/title and rail position still follow the page`, after.current >= 0 && original.labels.includes(after.title) && !after.titleBefore && JSON.stringify(after.fills) !== JSON.stringify(before.fills) && after.dot !== before.dot && (original.labels.length === 1 || after.shift !== before.shift), { before, after, labels: original.labels });
  ok(`${label}/reading/page input preserves the address and history`, after.url === before.url && after.history === before.history, { before, after });
}
async function indexAppearance(p, label, open) {
  const look = await p.locator('.ts-pk-ix').evaluate(node => {
    const r = node.getBoundingClientRect(), s = getComputedStyle(node), toggle = [...document.querySelectorAll('#masthead .menu-toggle')].find(n => n.getBoundingClientRect().width > 0), hs = getComputedStyle(toggle);
    return { width: r.width, height: r.height, borders: ['Top', 'Right', 'Bottom', 'Left'].map(side => [parseFloat(s[`border${side}Width`]), s[`border${side}Style`], s[`border${side}Color`]]), radius: parseFloat(s.borderTopLeftRadius), headerRadius: parseFloat(hs.borderTopLeftRadius), visible: s.visibility, hit: node.contains(document.elementFromPoint((r.left + r.right) / 2, (r.top + r.bottom) / 2)) };
  });
  const s = await state(p);
  ok(`${label}/index-${open ? 'open' : 'closed'}/same full-size button has a visible rounded outline`, look.width >= 43 && look.height >= 43 && look.borders.every(([width, style, color]) => width >= .5 && style !== 'none' && !['transparent', 'rgba(0, 0, 0, 0)'].includes(color)) && look.radius > 0 && look.radius < look.width / 2 && look.headerRadius > 0 && look.visible === 'visible' && look.hit, look);
  ok(`${label}/index-${open ? 'open' : 'closed'}/glyph and accessible name match the state`, open ? s.expanded === 'true' && s.close && !s.list && s.closePath === 'M7 7l10 10M17 7L7 17' && s.name === 'Close section index' : s.expanded === 'false' && s.list && !s.close && s.name === 'Show all sections (press and hold)', s);
}
async function capture(p, slug, side, kind) {
  const file = `peek-quiet-nav-${engine}-${slug}-${side}-${kind}.jpg`;
  console.log(`INFO quiet-nav capture ${file}: ${JSON.stringify({ viewport: p.viewportSize(), state: await state(p) })}`);
  console.log(`INLINE ${file} base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`); captures++;
}
async function indexToggle(p, label, original, slug, side) {
  await indexAppearance(p, label, false); await capture(p, slug, side, 'idle-reading');
  await p.locator('.ts-pk-ix').evaluate(node => node.focus({ preventScroll: true }));
  const before = await state(p); await clearTrace(p);
  await tap(p, '.ts-pk-ix'); const opened = await state(p), events = await trace(p, `${label}/index-open`);
  ok(`${label}/index/first trusted tap keeps all original names open without moving the page`, opened.expanded === 'true' && opened.kept && still(before, opened) && events.some(e => e.type === 'pointerdown' && e.pointer === 'touch' && e.trusted) && JSON.stringify(await p.locator('.ts-pk-fan li > span').allTextContents()) === JSON.stringify(original.labels), { before, opened });
  await indexAppearance(p, label, true); await capture(p, slug, side, 'index-open');
  await tap(p, '.ts-pk-ix'); const closed = await state(p);
  ok(`${label}/index/second tap on X closes without scrolling navigation or focus loss`, closed.expanded === 'false' && !closed.kept && closed.focused && !closed.card.on && still(before, closed), { before, closed });
  await indexAppearance(p, `${label}/after-X`, false);
  await tap(p, '.ts-pk-ix');
  const count = original.labels.length, i = Math.min(1, count - 1);
  await tap(p, `.ts-pk-fan li:nth-child(${i + 1})`); const card = await state(p);
  ok(`${label}/index/named fan still opens its own labelled preview without navigation`, card.expanded === 'false' && card.card.on && card.card.pinned && card.card.index === i && card.card.title === original.labels[i] && card.card.label === `Section ${i + 1} preview: ${original.labels[i]}` && still(before, card) && (original.summary ? card.card.summary.length > 0 : card.card.summary === ''), { before, card });
  await clean(p);
}
async function indexFromOpen(p, context, label, original) {
  const cdp = await context.newCDPSession(p);
  const touch = (type, at) => cdp.send('Input.dispatchTouchEvent', { type, touchPoints: type === 'touchEnd' ? [] : [{ ...at, id: 1 }] });
  const before = await state(p);
  try {
    await tap(p, '.ts-pk-ix');
    await touch('touchStart', await point(p, '.ts-pk-ix')); await p.waitForTimeout(2100); await touch('touchEnd'); await settle(p);
    let after = await state(p);
    ok(`${label}/index-open-gestures/holding an already-open X for two seconds keeps names open`, after.expanded === 'true' && after.kept && still(before, after), { before, after });
    // Measure the real kept-open name before pointer capture intentionally
    // turns its independent hit target off during the index's own gesture.
    const end = await point(p, '.ts-pk-fan li:last-child');
    let start = await point(p, '.ts-pk-ix'); await touch('touchStart', start); await p.waitForTimeout(100);
    await touch('touchMove', { x: end.x, y: start.y });
    for (let k = 1; k <= 8; k++) { await touch('touchMove', { x: end.x, y: start.y + (end.y - start.y) * k / 8 }); await p.waitForTimeout(20); }
    await touch('touchEnd'); await settle(p); after = await state(p);
    ok(`${label}/index-open-gestures/sliding from an already-open X onto a name still previews it`, after.expanded === 'false' && after.card.on && after.card.pinned && after.card.index === original.labels.length - 1 && after.card.title === original.labels.at(-1) && still(before, after), { before, after });
    await clean(p); await tap(p, '.ts-pk-ix'); start = await point(p, '.ts-pk-ix');
    const railEnd = await point(p, '.ts-pk-rail li:last-child > :first-child');
    await touch('touchStart', start); await p.waitForTimeout(100);
    for (let k = 1; k <= 10; k++) { await touch('touchMove', { x: start.x, y: start.y + (railEnd.y - start.y) * k / 10 }); await p.waitForTimeout(20); }
    await touch('touchEnd'); await settle(p); after = await state(p);
    ok(`${label}/index-open-gestures/sliding straight down from an already-open X still previews the rail`, after.expanded === 'false' && after.card.on && after.card.pinned && after.card.index === original.labels.length - 1 && still(before, after), { before, after });
  } finally { await cdp.detach(); await clean(p); }
}

const menuState = p => p.evaluate(() => {
  const toggle = [...document.querySelectorAll('#masthead .menu-toggle')].find(node => node.getBoundingClientRect().width > 0), menu = document.querySelector('#masthead .ast-mobile-header-content'), r = menu.getBoundingClientRect();
  return { expanded: toggle?.getAttribute('aria-expanded'), top: r.top, bottom: r.bottom, height: r.height, display: getComputedStyle(menu).display, theme: document.documentElement.dataset.tsTheme || 'light', x: scrollX, y: scrollY, url: location.href, history: history.length };
});
async function openMenuReady(p, label) {
  let previous = await menuState(p), current = previous, stable = 0;
  for (let k = 0; k < 15 && stable < 3; k++) {
    await p.waitForTimeout(100); current = await menuState(p);
    stable = current.expanded === 'true' && current.height > 0 && current.display !== 'none' && Math.abs(current.top - previous.top) < .5 && Math.abs(current.bottom - previous.bottom) < .5 ? stable + 1 : 0;
    previous = current;
  }
  ok(`${label}/setup/original menu is open with settled visible bounds`, stable === 3, current);
  if (stable !== 3) throw new Error(`${label}: expected the original menu open before outside input`);
  return current;
}
const outsidePoint = p => p.evaluate(() => {
  const menu = document.querySelector('#masthead .ast-mobile-header-content').getBoundingClientRect();
  const interactive = 'a,button,input,textarea,select,summary,[role="button"],label';
  const nearby = [...document.querySelectorAll(interactive)].map(node => ({ node, r: node.getBoundingClientRect(), s: getComputedStyle(node) })).filter(({ r, s }) => r.width > 0 && r.height > 0 && s.display !== 'none' && s.visibility === 'visible');
  for (const y of [Math.min(innerHeight - 28, menu.bottom + 60), innerHeight - 28, innerHeight - 90, innerHeight - 160]) for (const x of [innerWidth / 2, 80, innerWidth - 80, 28, innerWidth - 28]) {
    const node = document.elementFromPoint(x, y);
    // Chromium can adjust a touch onto a nearby anchor even when the precise
    // centre hits text. Require real clearance, not just a centre hit test.
    if (node && !node.closest('#masthead,.ts-pk,' + interactive) && !nearby.some(({ r }) => x >= r.left - 25 && x <= r.right + 25 && y >= r.top - 25 && y <= r.bottom + 25)) return { x, y };
  }
  throw new Error('No original noninteractive outside-menu point');
});
async function menuChecks(p, context, label, slug, navigationRequests) {
  await clean(p); await p.setViewportSize({ width: 390, height: 844 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
  const toggle = '#masthead .menu-toggle:visible';
  const before = await menuState(p); await clearTrace(p); await tap(p, toggle);
  let menu = await menuState(p);
  ok(`${label}/menu/original toggle opens with a trusted touch`, menu.expanded === 'true' && menu.height > 0 && menu.display !== 'none' && (await trace(p, `${label}/menu-open`)).some(e => e.type === 'pointerdown' && e.pointer === 'touch' && e.trusted), menu);
  if (menu.expanded !== 'true') throw new Error(`${label}: original mobile menu failed to open`);
  menu = await openMenuReady(p, `${label}/menu/initial`);
  // The real menu has six contiguous link rows and no blank padding. Prevent
  // only this original link's navigation once to test an inside touch without
  // adding another page load. This is explicitly a boundary fixture, not proof
  // of the destination's navigation. Its exact listener is always removed.
  const menuLink = p.locator('#masthead .ast-mobile-header-content a[href]').first();
  await menuLink.evaluate(node => { window.__quietInside = { node, clicks: 0, trusted: false }; const fn = e => { window.__quietInside.clicks++; window.__quietInside.trusted = e.isTrusted; e.preventDefault(); }; window.__quietInside.fn = fn; node.addEventListener('click', fn); });
  try { await clearTrace(p); await tap(p, '#masthead .ast-mobile-header-content a[href]'); }
  finally { await p.evaluate(() => { const d = window.__quietInside; d.node.removeEventListener('click', d.fn); }); }
  const inside = await p.evaluate(() => ({ clicks: window.__quietInside.clicks, trusted: window.__quietInside.trusted }));
  menu = await menuState(p);
  ok(`${label}/menu/inside original-link touch stays open with test-only navigation prevention`, menu.expanded === 'true' && inside.clicks === 1 && inside.trusted && menu.url === before.url, { menu, inside, events: await trace(p, `${label}/inside-navigation-prevented`) });
  if (engine === 'chromium') {
    const cdp = await context.newCDPSession(p);
    try {
      const start = { x: 190, y: menu.bottom - 25 }, end = { x: 190, y: menu.top + 35 };
      await cdp.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ ...start, id: 1 }] });
      for (let k = 1; k <= 8; k++) { await cdp.send('Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [{ x: start.x, y: start.y + (end.y - start.y) * k / 8, id: 1 }] }); await p.waitForTimeout(20); }
      // Pause before lifting so the next distinct tap is not consumed by the
      // browser merely to stop native page-scroll momentum.
      await p.waitForTimeout(140); await cdp.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] }); await settle(p);
      ok(`${label}/menu/native inside swipe leaves the original menu open`, (await menuState(p)).expanded === 'true');
      // A touch during native inertia can stop scrolling without dispatching a
      // click. Wait for that separate gesture to finish before testing a tap.
      let stable = 0, previousY = await p.evaluate(() => scrollY);
      for (let k = 0; k < 30 && stable < 3; k++) {
        await p.waitForTimeout(100); const y = await p.evaluate(() => scrollY);
        stable = Math.abs(y - previousY) < .5 ? stable + 1 : 0; previousY = y;
      }
      ok(`${label}/menu/inside swipe settles before the next distinct tap`, stable === 3);
    } finally { await cdp.detach(); }
  }
  await clearTrace(p); await tap(p, toggle); menu = await menuState(p);
  ok(`${label}/menu/original toggle still closes its own menu`, menu.expanded === 'false', { menu, events: await trace(p, `${label}/menu-toggle-close`) });
  await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p); await tap(p, toggle);
  const open = await openMenuReady(p, `${label}/menu/plain-outside`), outside = await outsidePoint(p); await clearTrace(p); await tap(p, outside);
  const closed = await menuState(p), outsideEvents = await trace(p, `${label}/menu-outside`);
  ok(`${label}/menu/trusted outside page touch closes without navigation or page movement`, closed.expanded === 'false' && still(open, closed) && outsideEvents.some(e => e.type === 'pointerdown' && e.trusted && !e.interactive) && outsideEvents.some(e => e.type === 'click' && e.trusted) && outsideEvents.filter(e => e.trusted && ['pointerdown', 'pointerup', 'touchstart', 'touchend', 'click'].includes(e.type)).every(e => !e.prevented), { open, closed, outside, outsideEvents });
  const idlePoint = await outsidePoint(p), idle = await menuState(p); await tap(p, idlePoint);
  ok(`${label}/menu/outside touch with the menu already closed is a no-op`, still(idle, await menuState(p)) && (await menuState(p)).expanded === 'false');
  await tap(p, toggle); const themeBefore = await openMenuReady(p, `${label}/menu/theme-outside`); await clearTrace(p); await tap(p, '#masthead .ts-theme-toggle:visible');
  const themeAfter = await menuState(p), themeEvents = await trace(p, `${label}/menu-theme`);
  ok(`${label}/menu/outside original theme button keeps its normal action while dismissing`, themeAfter.expanded === 'false' && themeAfter.theme !== themeBefore.theme && still(themeBefore, themeAfter) && themeEvents.filter(e => e.type === 'click' && e.trusted).length === 1, { themeBefore, themeAfter, themeEvents });
  await tap(p, '#masthead .ts-theme-toggle:visible');
  ok(`${label}/menu/theme action also works after dismissal and restores the chosen theme`, (await menuState(p)).theme === before.theme && (await menuState(p)).expanded === 'false');
  if (['thai-massage', 'privacy-policy'].includes(slug)) for (const motion of ['reduce', 'no-preference']) await outsideAnchor(p, label, navigationRequests, motion);
  if (slug === 'thai-massage') await coexistence(p, label);
}
async function outsideAnchor(p, label, navigationRequests, motion) {
  await clean(p); await p.emulateMedia({ reducedMotion: motion }); await p.setViewportSize({ width: 390, height: 500 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p); await tap(p, '#masthead .menu-toggle:visible');
  const previous = await openMenuReady(p, `${label}/menu/anchor-${motion}`), loads = navigationRequests();
  await p.evaluate(() => {
    const target = window.__quietTargets[1]; if (!target?.id) throw new Error('Outside anchor needs the original second destination with its ID');
    const menu = document.querySelector('#masthead .ast-mobile-header-content').getBoundingClientRect(), link = document.createElement('a');
    link.id = 'quiet-outside-anchor-probe'; link.href = '#' + encodeURIComponent(target.id); link.textContent = 'Browser check: original destination';
    link.style.cssText = `position:fixed;left:65px;top:${Math.min(innerHeight - 54, menu.bottom + 8)}px;width:220px;min-height:42px;background:#fff;color:#111;z-index:97;`;
    window.__quietAnchor = { target, link };
    document.querySelector('.ts-pk-title-content').appendChild(link);
  });
  try {
    await clearTrace(p); await tap(p, '#quiet-outside-anchor-probe'); await p.waitForTimeout(1500); await settle(p);
    const landing = await p.evaluate(() => { const d = window.__quietAnchor, r = d.target.getBoundingClientRect(), strip = document.querySelector('#ts-peek-title-strip').getBoundingClientRect(), head = document.querySelector('#masthead').getBoundingClientRect(), clicks = window.__quietEvents.filter(e => e.type === 'click' && e.target === 'A#quiet-outside-anchor-probe'); return { clicks: clicks.length, trusted: clicks.every(e => e.trusted), hash: location.hash, expectedHash: '#' + encodeURIComponent(d.target.id), top: r.top, boundary: Math.max(0, strip.bottom, head.bottom), height: innerHeight, menu: [...document.querySelectorAll('#masthead .menu-toggle')].find(n => n.getBoundingClientRect().width > 0).getAttribute('aria-expanded') }; });
    const after = await menuState(p);
    ok(`${label}/menu/outside anchor with ${motion} motion activates once and lands below the collapsed menu and strip`, landing.clicks === 1 && landing.trusted && landing.hash === landing.expectedHash && landing.menu === 'false' && landing.top >= landing.boundary - 1 && landing.top <= landing.boundary + 40 && landing.top < landing.height && after.history === previous.history && navigationRequests() === loads, { previous, after, landing, events: await trace(p, `${label}/outside-anchor-${motion}`) });
  } finally { await p.evaluate(url => { window.__quietAnchor?.link.remove(); delete window.__quietAnchor; history.replaceState(null, '', url); }, previous.url); await p.emulateMedia({ reducedMotion: 'reduce' }); }
}
async function coexistence(p, label) {
  await clean(p); await p.setViewportSize({ width: 390, height: 844 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
  await p.locator('.ts-pk-rail li:first-child > :first-child').focus(); await p.keyboard.press('Enter'); await settle(p);
  await p.locator('#masthead .menu-toggle:visible').focus(); await p.keyboard.press('Enter'); await settle(p);
  const before = await menuState(p), card = await state(p), outside = await outsidePoint(p);
  ok(`${label}/menu/coexistence setup retains an open preview and open header menu`, before.expanded === 'true' && card.card.on && card.card.pinned, { before, card });
  // A real target at the free page point detects fallthrough. It stays below
  // the original header and Peek in the stacking order, and never cancels input.
  await p.evaluate(at => {
    const probe = document.createElement('button'); probe.type = 'button'; probe.id = 'quiet-coexistence-probe'; probe.setAttribute('aria-label', 'Browser-only activation probe');
    probe.style.cssText = `all:initial;display:block;position:fixed;left:${at.x - 8}px;top:${at.y - 8}px;width:16px;height:16px;z-index:50;`;
    window.__quietCoexistenceClicks = 0; probe.addEventListener('click', () => window.__quietCoexistenceClicks++); document.body.appendChild(probe);
  }, outside);
  try {
    await tap(p, '#quiet-coexistence-probe'); const after = await menuState(p), result = await state(p), clicks = await p.evaluate(() => window.__quietCoexistenceClicks);
    ok(`${label}/menu/outside preview-owned touch closes both without activating the touched page button`, after.expanded === 'false' && !result.card.on && still(before, after) && clicks === 0, { before, after, result, clicks });
    await tap(p, '#quiet-coexistence-probe');
    ok(`${label}/menu/next separate touch reaches the same page button normally`, await p.evaluate(() => window.__quietCoexistenceClicks === 1));
  } finally { await p.evaluate(() => { document.querySelector('#quiet-coexistence-probe')?.remove(); delete window.__quietCoexistenceClicks; }); }
  await clean(p);
}

try {
  browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  for (const slug of QUIET_SLUGS) for (const side of QUIET_SIDES) {
    const context = await browser.newContext({ viewport: { width: 390, height: 620 }, deviceScaleFactor: 1, isMobile: engine !== 'firefox', hasTouch: true, reducedMotion: 'reduce', colorScheme: side === 'left' ? 'dark' : 'light' });
    try {
      await context.addInitScript(({ side }) => {
        localStorage.setItem('ts-pk-side', side); localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-theme', side === 'left' ? 'dark' : 'light');
        window.__quietEvents = []; window.__quietLabelSeen = false;
        const name = node => node ? node.tagName + (node.id ? '#' + node.id : '') + (typeof node.className === 'string' && node.className ? '.' + node.className.trim().replace(/\s+/g, '.') : '') : null;
        for (const type of ['pointerdown', 'pointerup', 'touchstart', 'touchend', 'mousedown', 'mouseup', 'click', 'keydown']) document.addEventListener(type, e => {
          const entry = { type, key: e.key, pointer: e.pointerType, trusted: e.isTrusted, target: name(e.target), interactive: !!e.target.closest?.('a,button,input,textarea,select,summary,[role="button"],label'), prevented: e.defaultPrevented, x: e.clientX, y: e.clientY, scrollY };
          window.__quietEvents.push(entry); if (window.__quietEvents.length > 150) window.__quietEvents.shift();
          setTimeout(() => { entry.prevented = e.defaultPrevented; }, 0);
        }, true);
        window.addEventListener('scroll', () => requestAnimationFrame(() => {
          if ([...document.querySelectorAll('.ts-pk-label')].some(node => { const s = getComputedStyle(node), r = node.getBoundingClientRect(); return s.display !== 'none' && s.visibility === 'visible' && +s.opacity > .05 && r.width > 0 && r.height > 0; })) window.__quietLabelSeen = true;
        }), { passive: true });
      }, { side });
      const p = await context.newPage(), errors = [], label = `${slug}/${side}`; let requests = 0;
      p.on('pageerror', error => errors.push(error.message));
      p.on('request', request => { if (request.isNavigationRequest() && request.frame() === p.mainFrame()) requests++; });
      const path = slug === 'search' ? '/?s=massage' : `/${slug}/`, response = await p.goto(base + path, { waitUntil: 'load', timeout: 45000 });
      if (response?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`${label}: expected same-origin HTTP200; got${response?.status()}; stop without further loads`);
      visits++; await p.waitForSelector('html.ts-pk-ready .ts-pk-nav', { timeout: 10000 });
      await p.waitForFunction(() => document.fonts.status === 'loaded', null, { timeout: 10000 }); await settle(p);
      const original = await source(p), initial = await state(p);
      ok(`${label}/contract/exact quiet navigation marker is present`, initial.marker === 'quiet-v1', initial.marker);
      if (original.labels.length < 1 || initial.marker !== 'quiet-v1') throw new Error(`${label}: expected quiet-v1 and original page destinations`);
      await readPage(p, label, original);
      await indexToggle(p, label, original, slug, side);
      if (engine === 'chromium' && ['thai-massage', 'privacy-policy'].includes(slug)) await indexFromOpen(p, context, label, original);
      await menuChecks(p, context, label, slug, () => requests);
      await clean(p); await p.setViewportSize({ width: 390, height: 620 }); await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
      ok(`${label}/content/original wording headings destinations and menu links stay unchanged`, JSON.stringify(await source(p)) === JSON.stringify(original));
      ok(`${label}/runtime/no extra page loads or JavaScript errors`, requests === 1 && errors.length === 0, { requests, errors });
      completed++; console.log(`INFO quiet-nav completed ${engine}/${label}`);
    } finally { await context.close(); }
    if (!local) await new Promise(resolve => setTimeout(resolve, 5500));
  }
} catch (error) { invalid = true; console.log(`INVALID quiet-nav ${engine}: ${error.message}`); }
finally { if (browser) await browser.close(); }
if (completed !== quietCases().length || visits !== quietCases().length || captures !== quietCases().length * 2) { invalid = true; console.log(`INVALID quiet-nav ${engine}: incomplete fixed case/load/capture matrix`); }
console.log(`INFO Peek quiet nav ${engine}: ${passed} passed, ${failed} failed; ${completed}/${quietCases().length} cases, ${visits} page loads, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s; ${QUIET_LIMITATION}`);
process.exitCode = invalid ? 2 : failed ? 1 : 0;

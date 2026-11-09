// Actual Privacy disclosure regressions from the Kiro assessment. Each landing
// case starts in a fresh context with the large Online panel open and Client
// closed. A previously exercised accordion is not an equivalent initial state.
// Native touch below means Chromium/CDP browser events, not a physical device.
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if (!local && base !== 'https://staging39.thaisolitude.com') throw new Error('exact staging39 or isolated localhost origin only');
if (flags.length > 1 || flags.some(flag => flag !== '--firefox')) throw new Error('only --firefox is supported');
const engine = flags.includes('--firefox') ? 'firefox' : 'chromium';
const targetId = 'ts-client-privacy', precedingId = 'ts-online-privacy';
const control = `.ts-pk-rail [href="#${targetId}"]`;
let pass = 0, fail = 0, visited = 0, captures = 0, invalid = false, browser;
const started = Date.now();
const ok = (name, value, detail) => {
  value ? pass++ : fail++;
  console.log(`${value ? 'PASS' : 'FAIL'} disclosure ${engine}/${name}${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`);
};
const frame = p => p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
const snapshot = p => p.evaluate(({ targetId, precedingId }) => {
  const target = document.getElementById(targetId), preceding = document.getElementById(precedingId);
  const panel = target?.closest('.uagb-faq-item'), previous = preceding?.closest('.uagb-faq-item');
  const button = panel?.querySelector('button.ts-faq-disclosure'), answer = panel?.querySelector('.uagb-faq-content');
  const strip = document.getElementById('ts-peek-title-strip'), r = target?.getBoundingClientRect();
  return {
    y: scrollY, x: scrollX, hash: location.hash, history: history.length,
    active: !!panel?.classList.contains('uagb-faq-item-active'), previousActive: !!previous?.classList.contains('uagb-faq-item-active'),
    expanded: button?.getAttribute('aria-expanded'), answerHidden: answer?.getAttribute('aria-hidden'),
    targetTop: r?.top, targetBottom: r?.bottom, previousHeight: previous?.getBoundingClientRect().height,
    stripBottom: strip?.getBoundingClientRect().bottom, viewport: { width: innerWidth, height: innerHeight },
    focus: document.activeElement === button, focusedTag: document.activeElement?.tagName,
    focusedId: document.activeElement?.id, side: document.documentElement.classList.contains('ts-pk-left') ? 'left' : 'right',
    cardPinned: !!document.querySelector('.ts-pk-card.is-pinned'),
    events: window.__peekDisclosureEvents || []
  };
}, { targetId, precedingId });
const still = (before, after) => Math.abs(before.y - after.y) <= 1 && Math.abs(before.x - after.x) <= 1 && before.hash === after.hash && before.history === after.history;
const tap = async (p, selector) => {
  const point = await p.locator(selector).evaluate(node => {
    const r = node.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
    if (r.width < 2 || r.height < 2 || x < 0 || x >= innerWidth || y < 0 || y >= innerHeight || !node.contains(document.elementFromPoint(x, y))) throw new Error('INVALID native touch target is not visible and unobstructed');
    return { x, y };
  });
  const session = await p.context().newCDPSession(p);
  try {
    await session.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ ...point, id: 1, radiusX: 3, radiusY: 3, force: 1 }] });
    await p.waitForTimeout(35);
    await session.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
  } finally { await session.detach(); }
};
const activatePreview = async (p, mode) => {
  if (mode === 'touch') await tap(p, control);
  else if (mode === 'keyboard') { await p.locator(control).focus(); await p.keyboard.press('Enter'); }
  else await p.locator(control).click();
  await p.waitForTimeout(250); await frame(p);
};
const activateJump = async (p, mode) => {
  await p.evaluate(() => { window.__peekDisclosureEvents = []; window.__peekDisclosureTrace = true; });
  if (mode === 'touch') await tap(p, '.ts-pk-card .open > b');
  else if (mode === 'keyboard') { await p.locator('.ts-pk-card').focus(); await p.keyboard.press('Enter'); }
  else await p.locator('.ts-pk-card .open > b').click();
};
const capture = async (p, label) => {
  if (engine !== 'chromium') return;
  captures++;
  console.log(`INLINE peek-disclosure-${label}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`);
};
const reset = async p => {
  await p.keyboard.press('Escape');
  await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await frame(p);
  // Use the original native control, never class/height mutations, to establish
  // the initial accordion state for additional cancellation/control checks.
  if (!await p.locator(`#${precedingId}`).evaluate(node => node.closest('.uagb-faq-item').classList.contains('uagb-faq-item-active'))) {
    await p.locator(`#${precedingId} button.ts-faq-disclosure`).click();
  }
  await p.waitForTimeout(650);
  await p.evaluate(() => { history.replaceState(null, '', location.pathname + location.search); scrollTo({ top: 0, behavior: 'instant' }); });
  await frame(p);
  const before = await snapshot(p);
  if (!before.previousActive || before.active || before.previousHeight < 250) throw new Error('INVALID reset did not establish the large preceding open panel');
  return before;
};
const buttonOwnership = async p => {
  let before = await reset(p);
  await activatePreview(p, 'keyboard');
  await p.locator('.ts-pk-card .x').focus(); await p.keyboard.press('Enter'); await p.waitForTimeout(300);
  let after = await snapshot(p);
  ok('keyboard/close: Enter closes only the preview and returns focus to its rail piece', still(before, after) && !after.active && !after.cardPinned && await p.locator(control).evaluate(node => node === document.activeElement), after);
  before = await reset(p);
  await activatePreview(p, 'keyboard');
  await p.locator('.ts-pk-card .side').focus(); await p.keyboard.press('Enter'); await p.waitForTimeout(300);
  after = await snapshot(p);
  ok('keyboard/side: Enter changes side without opening the disclosure or scrolling', still(before, after) && !after.active && after.side !== before.side && await p.locator(control).evaluate(node => node === document.activeElement), after);
};
const cancellation = async (p, kind) => {
  await reset(p); await activatePreview(p, 'mouse');
  await activateJump(p, 'mouse');
  // A new input within 500 ms must already cancel settling. A blanket timeout
  // that ignores every event after activation would fail this test.
  await p.waitForTimeout(60);
  const point = await p.evaluate(() => {
    const content = document.querySelector('.entry-content');
    for (const y of [innerHeight - 30, innerHeight * 0.65, innerHeight * 0.5]) {
      const x = innerWidth / 2, node = document.elementFromPoint(x, y);
      if (node && content.contains(node) && !node.closest('a,button,input,select,textarea,summary,.ts-pk,.uagb-faq-questions-button')) return { x, y };
    }
    return null;
  });
  if (!point) throw new Error(`INVALID ${kind}: no non-interactive reading surface for interruption`);
  if (kind === 'wheel') { await p.mouse.move(point.x, point.y); await p.mouse.wheel(0, 50); }
  else if (kind === 'key') await p.keyboard.press('Shift');
  else if (kind === 'pointer') { await p.mouse.move(point.x, point.y); await p.mouse.down(); await p.mouse.up(); }
  else {
    const session = await p.context().newCDPSession(p);
    try {
      await session.send('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ ...point, id: 2, radiusX: 3, radiusY: 3 }] });
      await session.send('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
    } finally { await session.detach(); }
  }
  const inputType = { wheel: 'wheel', key: 'keydown', pointer: 'pointerdown', touch: 'touchstart' }[kind];
  // Playwright's wheel call returns before the browser has delivered its wheel
  // event. Wait for that genuine event, then measure its own timestamp.
  await p.waitForFunction(type => {
    const events = window.__peekDisclosureEvents, activation = events.find(event => event.type === 'click' && !event.trusted);
    return activation && events.some(event => event.type === type && event.trusted && event.at > activation.at);
  }, inputType, { timeout: 1500 });
  const timing = await p.evaluate(type => {
    const events = window.__peekDisclosureEvents;
    const activation = events.find(event => event.type === 'click' && !event.trusted);
    const input = activation && events.find(event => event.type === type && event.trusted && event.at > activation.at);
    return { activation: activation?.at, input: input?.at, elapsed: input && input.at - activation.at };
  }, inputType);
  if (!(timing.elapsed >= 0 && timing.elapsed < 500)) throw new Error(`INVALID ${kind}: genuine interruption did not arrive within the tested 500 ms window: ${JSON.stringify(timing)}`);
  await p.waitForTimeout(850);
  // After the animation ends, an independent scroll exposes any settling that
  // wrongly survived the real input. This is a test displacement, not a claim
  // that mouse-down/Shift itself scrolls the page.
  const displaced = await p.evaluate(() => {
    const target = document.getElementById('ts-client-privacy'), strip = document.getElementById('ts-peek-title-strip');
    const max = document.documentElement.scrollHeight - innerHeight;
    const aligned = target.getBoundingClientRect().top + scrollY - strip.getBoundingClientRect().bottom - 12;
    const wanted = Math.min(max, Math.max(0, aligned + 120));
    scrollTo({ top: wanted, behavior: 'instant' });
    return { y: scrollY, distance: Math.abs(scrollY - aligned) };
  });
  if (displaced.distance < 60) throw new Error(`INVALID ${kind}: insufficient page range to test cancellation`);
  await p.waitForTimeout(450);
  const after = await snapshot(p);
  ok(`cancel/${kind}: real new input stops correction immediately and leaves the later scroll alone`, Math.abs(after.y - displaced.y) <= 2, { timing, displaced, after });
};
try {
  browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  const cases = [];
  for (const motion of ['no-preference', 'reduce']) {
    for (const side of ['right', 'left']) {
      if (engine === 'chromium') cases.push({ mode: 'touch', motion, side });
      cases.push({ mode: 'keyboard', motion, side });
    }
    cases.push({ mode: 'mouse', motion, side: 'right' });
  }
  for (const spec of cases) {
    const label = `${spec.mode}-${spec.motion}-${spec.side}`;
    const context = await browser.newContext({
      ...(engine === 'chromium' && spec.mode !== 'keyboard' ? playwright.devices['iPhone 13'] : { viewport: { width: 1280, height: 800 }, hasTouch: engine === 'chromium' }),
      deviceScaleFactor: 1, reducedMotion: spec.motion
    });
    try {
      await context.addInitScript(side => {
        localStorage.setItem('ts-pk-tip', 'seen'); localStorage.setItem('ts-pk-side', side);
        window.__peekDisclosureEvents = []; window.__peekDisclosureTrace = false;
        for (const type of ['pointerdown', 'pointerup', 'touchstart', 'touchend', 'mousedown', 'click', 'wheel', 'keydown', 'keypress', 'keyup']) window.addEventListener(type, event => {
          if (window.__peekDisclosureTrace) window.__peekDisclosureEvents.push({ type, at: performance.now(), tag: event.target.tagName, id: event.target.id, key: event.key, trusted: event.isTrusted, touch: event.sourceCapabilities?.firesTouchEvents });
        }, true);
      }, spec.side);
      const p = await context.newPage(), errors = [];
      p.on('pageerror', error => errors.push(error.message));
      const response = await p.goto(base + '/privacy-policy/', { waitUntil: 'load', timeout: 45000 });
      if (response?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID ${label}: expected same-origin Privacy HTTP200, got ${response?.status()}; no further page loads`);
      visited++;
      await p.waitForSelector(control, { timeout: 10000 });
      await p.waitForSelector(`#${targetId} button.ts-faq-disclosure`, { timeout: 10000 });
      await p.evaluate(() => document.fonts.ready); await p.waitForTimeout(350);
      const before = await snapshot(p);
      if (!before.previousActive || before.active || before.previousHeight < 250 || before.expanded !== 'false') throw new Error(`INVALID ${label}: Privacy did not begin with large Online panel open and Client closed: ${JSON.stringify(before)}`);
      ok(`${label}: fresh original accordion state and selected rail side`, before.previousActive && !before.active && before.side === spec.side);
      await activatePreview(p, spec.mode);
      const preview = await snapshot(p);
      ok(`${label}: rail activation previews without moving the page or changing panels`, still(before, preview) && preview.cardPinned && preview.previousActive && !preview.active, { before, preview });
      await activateJump(p, spec.mode); await p.waitForTimeout(1600); await frame(p);
      const after = await snapshot(p);
      ok(`${label}: Client remains open and Online closes after activation`, after.active && !after.previousActive && after.expanded === 'true' && after.answerHidden === 'false', after);
      ok(`${label}: heading lands about 12 px below the strip`, after.targetTop >= after.stripBottom + 8 && after.targetTop <= after.stripBottom + 18 && after.targetBottom > after.stripBottom, after);
      ok(`${label}: destination retains focus and original fragment without a history entry`, after.focus && after.hash === '#' + targetId && after.history === before.history, after);
      console.log(`INFO disclosure trace ${engine}/${label}: ${JSON.stringify(after)}`);
      if (spec.side === 'right' && spec.motion === 'no-preference') await capture(p, label);
      if (spec.mode === 'keyboard' && spec.motion === 'no-preference' && spec.side === 'right') await buttonOwnership(p);
      if (spec.mode === 'mouse' && spec.motion === 'no-preference') {
        for (const kind of ['wheel', 'key', 'pointer', ...(engine === 'chromium' ? ['touch'] : [])]) await cancellation(p, kind);
      }
      ok(`${label}: no JavaScript errors`, errors.length === 0, errors);
    } finally { await context.close(); }
    if (!local) await new Promise(resolve => setTimeout(resolve, 5500));
  }
  if (engine !== 'chromium') console.log('INFO disclosure Firefox: native touch is not exercised; mouse and full keyboard event sequences only.');
} catch (error) {
  invalid = true;
  console.log(`INVALID Peek disclosures ${engine}: ${error.message}`);
} finally { if (browser) await browser.close(); }
console.log(`INFO Peek disclosures ${engine}: ${pass} passed, ${fail} failed; ${visited} Privacy loads, ${captures} captures; ${Math.round((Date.now() - started) / 1000)}s${invalid ? '; INCOMPLETE/INVALID' : ''}.`);
process.exit(invalid ? 2 : fail ? 1 : 0);

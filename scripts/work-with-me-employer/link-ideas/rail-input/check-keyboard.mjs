// Real keyboard/mouse checks of the existing five Rail practice ideas. Public
// runs are read-only; baseline/current source fixtures are isolated localhost.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2], flags = process.argv.slice(3);
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if (!local && base !== 'https://staging39.thaisolitude.com') throw new Error('exact staging39 or isolated localhost origin only');
if (flags.some(flag => !['--firefox', '--fixtures-only', '--baseline'].includes(flag)) || new Set(flags).size !== flags.length) throw new Error('unknown or repeated rail input flag');
const fixtures = flags.includes('--fixtures-only'), baseline = flags.includes('--baseline');
if ((fixtures && !local) || (baseline && !fixtures)) throw new Error('baseline/source fixtures are isolated localhost only');
const engine = flags.includes('--firefox') ? 'firefox' : 'chromium';
const executable = process.env.TS_BROWSER_EXECUTABLE || process.env.CHROMIUM_PATH;
const browser = await playwright[engine].launch(engine === 'chromium' && executable ? { executablePath: executable } : {});
const started = Date.now(); let pass = 0, fail = 0, loads = 0, captures = 0;
const ok = (label, value, detail) => { value ? pass++ : fail++; console.log(`${value ? 'PASS' : 'FAIL'} rail ${engine}/${label}${!value && detail !== undefined ? ' ' + JSON.stringify(detail) : ''}`); };
const state = p => p.evaluate(() => ({ x: scrollX, y: scrollY, hash: location.hash, url: location.href, history: history.length,
  card: document.querySelector('.tsr-card').className, name: document.querySelector('.tsr-card b')?.textContent || '',
  focus: document.activeElement?.outerHTML.slice(0, 350), drawer: document.querySelector('.tsr-drawer').open }));
const sameAddress = (a, b) => a.url === b.url && a.hash === b.hash && a.history === b.history;
const still = (a, b) => sameAddress(a, b) && Math.abs(a.x - b.x) <= 1 && Math.abs(a.y - b.y) <= 1;
const settle = async p => { await p.waitForTimeout(150); await p.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))); };
const fixture = () => {
  const core = readFileSync(new URL('../peek-nav-shared-v6.php', import.meta.url), 'utf8');
  const css = core.match(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/)[0], js = core.match(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/)[0];
  const html = readFileSync(new URL(baseline ? '../rail-ideas-v1.html' : '../rail-ideas.html', import.meta.url), 'utf8');
  return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font-family:system-ui}#masthead{height:85px;background:white}.entry-content{padding:0 20px}</style>' + css + '</head><body><header id="masthead">Header</header><main><div class="entry-content">' + html + '</div></main><footer style="height:800px"></footer><script type="application/json" id="ts-peek-nav-config">{"title":"Side rail ideas","mode":"sections"}</script>' + js + '</body></html>';
};
const open = async ctx => {
  if (fixtures) await ctx.route(base + '/rail-nav-ideas/**', route => route.fulfill({ status: 200, contentType: 'text/html; charset=utf-8', body: fixture() }));
  const p = await ctx.newPage(), errors = [];
  p.on('pageerror', error => errors.push(error.message));
  const r = await p.goto(base + '/rail-nav-ideas/', { waitUntil: 'load' }); loads++;
  if (r?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`INVALID rail page: expected same-origin HTML200, got${r?.status()}`);
  return { p, errors };
};
const capture = async (p, label) => {
  if (engine !== 'chromium') return;
  captures++; console.log(`INLINE rail-input-${label}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`);
};
const landed = async (p, label) => {
  await p.waitForTimeout(1250); await settle(p);
  const g = await p.evaluate(() => {
    const target = document.getElementById('tsr-questions'), r = target.getBoundingClientRect();
    const bars = ['.tsr-top', '#ts-peek-title-strip', '#masthead'].map(s => document.querySelector(s)).filter(Boolean).map(n => n.getBoundingClientRect());
    return { hash: location.hash, top: r.top, bottom: r.bottom, ceiling: Math.max(0, ...bars.map(r => r.bottom)), viewport: innerHeight,
      focus: document.activeElement === target.querySelector('h2'), cardClosed: !document.querySelector('.tsr-card').classList.contains('is-on'), drawerClosed: !document.querySelector('.tsr-drawer').open };
  });
  ok(label + ': original destination lands below both strips and owns focus', g.hash === '#tsr-questions' && g.top >= g.ceiling - 2 && g.top <= g.ceiling + 40 && g.bottom > g.ceiling && g.top < g.viewport && g.focus && g.cardClosed && g.drawerClosed, g);
};
try {
  // Fixed-width contexts avoid carrying browser pointer/modal state across a
  // synthetic viewport resize. Each case tests a visitor's actual viewport.
  for (const width of [390, 1280]) {
    const ctx = await browser.newContext({ viewport: { width, height: 844 }, reducedMotion: 'reduce' });
    await ctx.addInitScript(() => { try { localStorage.setItem('ts-pk-tip', 'seen'); } catch {} });
    const { p, errors } = await open(ctx);
    await p.waitForSelector('.tsr-lab.is-js'); await p.waitForSelector('#ts-peek-title-strip');
    await p.evaluate(() => document.fonts.ready); await settle(p);
    const rail = p.locator('.tsr-rail a[href="#tsr-questions"]');
    const reset = async idea => {
      await p.keyboard.press('Escape');
      await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
      const picker = p.locator(`.tsr-picker [data-idea="${idea}"]`);
      await picker.focus(); await p.keyboard.press('Enter'); await settle(p);
      if (await picker.getAttribute('aria-pressed') !== 'true') throw new Error(`INVALID idea${idea}: picker did not establish the requested mode`);
      await p.evaluate(() => scrollTo({ top: 650, behavior: 'instant' })); await settle(p);
    };
    const preview = async (idea, label) => {
      await reset(idea); await rail.focus(); const before = await state(p);
      await p.keyboard.press('Enter'); await settle(p); const after = await state(p);
      const ready = after.card.includes('is-pinned') && after.name === 'Questions';
      ok(label + ': Enter opens the selected preview and preserves page/address', ready && still(before, after), { before, after });
      ok(label + ': focus moves to the preview action', await p.evaluate(idea => document.activeElement === document.querySelector(idea === 1 ? '.tsr-card .go' : '.tsr-card'), idea), after);
      return { ready, before };
    };

    for (const idea of [1, 2, 3, 4]) {
      const label = `${width}/idea${idea}/keyboard`;
      if ([1, 3].includes(idea)) {
        const { ready } = await preview(idea, label);
        if (ready) {
          if (width === 390) await capture(p, `idea${idea}-keyboard-preview`);
          await p.keyboard.press('Enter'); await landed(p, label + '/chosen');
        }
      } else {
        await reset(idea); await rail.focus(); await p.keyboard.press('Enter'); await landed(p, label);
      }
      ok(label + ': existing rail destination is unchanged', await rail.getAttribute('href') === '#tsr-questions');
    }
    // Each dismiss starts from a real focused preview, not manufactured markup.
    for (const [idea, button, key] of [[1, '.stay', 'Enter'], [1, '.stay', 'Space'], [1, null, 'Escape'], [3, '.x', 'Enter'], [3, '.x', 'Space'], [3, null, 'Escape']]) {
      const label = `${width}/idea${idea}/dismiss-${button || 'card'}-${key}`, opened = await preview(idea, label);
      if (!opened.ready) continue;
      if (button) await p.locator('.tsr-card ' + button).focus();
      const before = await state(p); await p.keyboard.press(key); await settle(p); const after = await state(p);
      ok(label + ': dismiss keeps page/address and returns focus to its rail link', !after.card.includes('is-on') && still(before, after) && await rail.evaluate(a => document.activeElement === a), { before, after });
    }
    // Pointer dismissal must let the newly touched control receive focus.
    const opened = await preview(1, `${width}/outside-pointer`);
    if (opened.ready) {
      const outside = p.locator('.tsr-picker [data-idea="3"]');
      await p.evaluate(() => scrollTo({ top: 0, behavior: 'instant' })); await settle(p);
      const beforeOutside = await outside.evaluate(button => {
        const r = button.getBoundingClientRect(); window.__railOutsideEvents = [];
        window.__railOutsideTrace = event => window.__railOutsideEvents.push({ type: event.type, target: event.target.outerHTML?.slice(0, 200), x: event.clientX, y: event.clientY, scroll: scrollY });
        for (const type of ['pointerdown', 'pointerup', 'mousedown', 'mouseup', 'click', 'focusin']) document.addEventListener(type, window.__railOutsideTrace, true);
        return { rect: r.toJSON(), hit: document.elementFromPoint(r.x + r.width / 2, r.y + r.height / 2)?.outerHTML.slice(0, 250), pointerCaptured: document.querySelector('.tsr-rail').hasPointerCapture(1), scroll: scrollY };
      });
      await outside.click(); await settle(p);
      const outsideTrace = await p.evaluate(() => {
        for (const type of ['pointerdown', 'pointerup', 'mousedown', 'mouseup', 'click', 'focusin']) document.removeEventListener(type, window.__railOutsideTrace, true);
        return window.__railOutsideEvents;
      });
      ok(`${width}/outside-pointer: the touched picker keeps focus`, await outside.evaluate(b => document.activeElement === b) && !(await state(p)).card.includes('is-on'), { before: beforeOutside, after: await state(p), events: outsideTrace });
    }
    // Keep the non-pointer click path for software/assistive activation. This
    // is a compatibility simulation, never a claim of screen-reader testing.
    await reset(1); const beforeClick = await state(p); await rail.evaluate(a => a.click()); await settle(p);
    ok(`${width}/non-pointer-click: existing activation opens preview without jumping`, (await state(p)).card.includes('is-pinned') && still(beforeClick, await state(p)), await state(p));
    await p.keyboard.press('Escape');
    // Genuine mouse PointerEvents preserve all four rail gestures.
    for (const idea of [1, 2, 3, 4]) {
      await reset(idea); const before = await state(p), box = await rail.boundingBox();
      if (!box) throw new Error(`INVALID ${width}/idea${idea}: rail has no hit area`);
      const x = box.x + box.width / 2, y = box.y + box.height / 2;
      await p.mouse.move(x, y); await p.mouse.down();
      if (idea === 2) { await p.mouse.move(x - 100, y, { steps: 8 }); await settle(p); }
      if (idea === 4) await p.waitForTimeout(1250);
      else ok(`${width}/idea${idea}/pointer: browsing keeps the page/address still`, still(before, await state(p)), { before, after: await state(p) });
      await p.mouse.up(); await settle(p);
      if ([1, 3].includes(idea)) {
        const after = await state(p), ready = after.card.includes('is-pinned') && after.name === 'Questions';
        ok(`${width}/idea${idea}/pointer: lift keeps the selected preview`, ready && still(before, after), { before, after });
        if (ready) await p.locator(idea === 1 ? '.tsr-card .go' : '.tsr-card .shot').click();
      }
      await landed(p, `${width}/idea${idea}/pointer`);
    }
    // The drawer keeps native button/dialog/link keyboard behavior.
    for (const key of ['Enter', 'Space']) {
      await reset(5); const tab = p.locator('.tsr-tab'); await tab.focus(); const before = await state(p);
      await p.keyboard.press(key); await settle(p); const after = await state(p);
      ok(`${width}/drawer/${key}: tab opens modal and preserves page/address`, after.drawer && still(before, after) && await p.locator('.tsr-drawer').evaluate(d => d.contains(document.activeElement)), { before, after });
      if (!after.drawer) continue;
      await p.keyboard.press('Escape'); await settle(p);
      ok(`${width}/drawer/${key}: Escape closes and returns focus to tab`, !(await state(p)).drawer && still(before, await state(p)) && await tab.evaluate(t => document.activeElement === t), await state(p));
    }
    await reset(5); await p.locator('.tsr-tab').click(); await settle(p);
    const beforeDrawer = await state(p);
    ok(`${width}/drawer/mouse: tab opens the drawer`, beforeDrawer.drawer, beforeDrawer);
    if (beforeDrawer.drawer) {
      const link = p.locator('.tsr-drawer a[href="#tsr-questions"]'); await link.focus(); await p.keyboard.press('Enter'); await landed(p, `${width}/drawer/chosen`);
    }
    ok(`${width}: no JavaScript errors`, errors.length === 0, errors);
    await ctx.close();
  }
  const nojs = await browser.newContext({ viewport: { width: 390, height: 844 }, javaScriptEnabled: false });
  const { p: fallback } = await open(nojs);
  const links = fallback.locator('.tsr-nav noscript a');
  ok('no-JavaScript: six original fallback links remain visible', await links.count() === 6 && await links.evaluateAll(a => a.every(a => a.getBoundingClientRect().height > 0 && document.getElementById(a.hash.slice(1)))));
  for (const name of ['#tsr-questions', '#tsr-research']) {
    const link = fallback.locator(`.tsr-nav noscript a[href="${name}"]`); await link.focus(); await fallback.keyboard.press('Enter');
    // Script-disabled documents do not run a requested animation-frame callback.
    await fallback.waitForTimeout(150);
    const actual = await fallback.evaluate(hash => { const r = document.getElementById(hash.slice(1)).getBoundingClientRect(); return { hash: location.hash, visible: r.height > 0 && r.bottom > 0 && r.top < innerHeight }; }, name);
    ok(`no-JavaScript: native Enter reaches ${name}`, actual.hash === name && actual.visible, actual);
  }
  await nojs.close();
} catch (error) { console.log(`INVALID rail input ${engine}: ${error.message}`); process.exitCode = 2; }
finally { await browser.close(); }
console.log(`INFO Rail input keyboard ${engine}: ${pass} passed, ${fail} failed; ${loads} page loads, ${captures} captures, ${Math.round((Date.now() - started) / 1000)}s${fixtures ? ', local fixtures only' : ''}; no physical-device or assistive-technology claim.`);
process.exit(process.exitCode || (fail ? 1 : 0));

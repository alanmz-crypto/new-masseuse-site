// Manifest-driven rendered checks. Fixture injection is local-only and is explicitly
// separate from deployed-page checks. It changes this browser's DOM, never WordPress.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
const local = ['http://localhost:8090', 'http://localhost:8091'].includes(base);
if (!local && base !== 'https://staging39.thaisolitude.com') throw new Error('staging39 or local rehearsal only');
const fixtures = process.argv.includes('--fixtures');
if (fixtures && !local) throw new Error('Fixture injection is restricted to the isolated localhost copy');
const engine = process.argv.includes('--firefox') ? 'firefox' : 'chromium';
const manifest = JSON.parse(readFileSync(new URL('./peek-pages.json', import.meta.url), 'utf8'));
const dataFor = slug => JSON.parse(readFileSync(new URL(`./peek-data/${slug}.json`, import.meta.url), 'utf8'));
const option = flag => {
  const at = process.argv.indexOf(flag);
  if (at < 0) return null;
  if (process.argv.lastIndexOf(flag) !== at || !process.argv[at + 1] || process.argv[at + 1].startsWith('--')) throw new Error(`${flag} requires one page slug`);
  const slug = process.argv[at + 1];
  if (!manifest.pages.some(page => page.slug === slug)) throw new Error(`${flag}: unknown page ${slug}`);
  return slug;
};
// A rehearsal installs one new registration while the manifest still describes
// staging. This changes expectations only; it never injects or installs data.
const enabledPage = option('--enabled-page');
if (enabledPage && !local) throw new Error('--enabled-page is restricted to the isolated localhost copy');
if (enabledPage && dataFor(enabledPage).approval.status !== 'approved') throw new Error('--enabled-page requires approved wording');
const enabled = page => page.staging === 'enabled' || page.slug === enabledPage;
const capturePage = option('--capture-page');
if (capturePage && (!enabled(manifest.pages.find(page => page.slug === capturePage)) || dataFor(capturePage).approval.status !== 'approved')) throw new Error('--capture-page requires an enabled page with approved wording');
const core = readFileSync(new URL('./peek-nav.php', import.meta.url), 'utf8');
const css = core.match(/<style id="ts-peek-nav-css">([\s\S]*?)<\/style>/)[1];
const js = core.match(/<script id="ts-peek-nav-runtime">([\s\S]*?)<\/script>/)[1];
let pass = 0, fail = 0;
const ok = (name, value, detail = '') => { value ? pass++ : fail++; console.log(`${value ? 'PASS' : 'FAIL'} ${name}${detail ? ' ' + JSON.stringify(detail) : ''}`); };
const b = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
const noJsContexts = new WeakSet();
const context = async (width, options = {}) => {
  const ctx = await b.newContext({ viewport: { width, height: 844 }, reducedMotion: 'reduce', ...options });
  if (options.javaScriptEnabled === false) noJsContexts.add(ctx);
  await ctx.addInitScript(() => { try { localStorage.setItem('ts-pk-tip', 'seen'); } catch {} });
  return ctx;
};
const open = async (ctx, slug, bareFixture = false) => {
  const p = await ctx.newPage(), errors = [];
  p.on('pageerror', e => errors.push(e.message));
  if (bareFixture) {
    if (!fixtures || !local) throw new Error('A bare runtime fixture is restricted to local --fixtures checks');
    // Once every page is enabled, structural fixtures still need a document
    // without already-running listeners. Strip only Peek from this browser's
    // local response before parsing; WordPress and other page assets stay intact.
    await p.route(`${base}/${slug}/`, async route => {
      const response = await route.fetch();
      const body = (await response.text())
        .replace(/<style\b[^>]*\bid=["']ts-peek-nav-css["'][^>]*>[\s\S]*?<\/style>/g, '')
        .replace(/<script\b[^>]*\bid=["']ts-peek-nav-(?:data|runtime)["'][^>]*>[\s\S]*?<\/script>/g, '');
      await route.fulfill({ response, body });
    });
  }
  const r = await p.goto(`${base}/${slug}/`, { waitUntil: 'load' });
  if (!r || r.status() !== 200 || /Robot Challenge/i.test(await p.title())) {
    console.log(`INVALID ${slug}: response ${r?.status()}; no verdict for this run`); await b.close(); process.exit(2);
  }
  await p.waitForTimeout(1200);
  // The load event waits for eager page images/styles. Wait explicitly for fonts
  // before comparing geometry, then allow two layout frames after they settle.
  const fontStatus = noJsContexts.has(ctx) || await p.evaluate(async () => {
    let timer;
    const ready = await Promise.race([document.fonts.ready.then(() => true), new Promise(resolve => { timer = setTimeout(() => resolve(false), 10000); })]);
    clearTimeout(timer);
    if (ready) await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
    return ready;
  });
  if (!fontStatus) { console.log(`INVALID ${slug}: fonts did not settle; no geometry verdict`); await b.close(); process.exit(2); }
  return { p, errors };
};
const rowOf = p => p.$$eval('.entry-content .ts-jump a', nodes => nodes.map(a => ({ href: a.getAttribute('href'), label: a.textContent.trim() })));
const inject = async (p, data) => {
  await p.addStyleTag({ content: css });
  await p.evaluate(data => { const s = document.createElement('script'); s.id = 'ts-peek-nav-data'; s.type = 'application/json'; s.textContent = JSON.stringify(data); document.body.appendChild(s); }, data);
  await p.addScriptTag({ content: js }); await p.waitForTimeout(1300);
};
const preview = async (p, i) => {
  await p.locator('.ts-pk-rail a').nth(i).evaluate(a => a.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true })));
  await p.waitForTimeout(80);
};
const capture = async (p, slug, name) => {
  // INLINE survives the rehearsal report filter and remains in its artifact.
  console.log(`INLINE ${engine}-${slug}-${p.viewportSize().width}-${name}.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 80 })).toString('base64')}`);
};
const verifyCard = async (p, prefix) => {
  const geometry = await p.evaluate(() => {
    const card = document.querySelector('.ts-pk-card'), summary = card.querySelector('.sum'), hint = card.querySelector('.hint'), strip = document.querySelector('.ts-pk-top');
    const rect = e => { const r = e.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, width: r.width, height: r.height }; };
    const stuckBottom = (parseFloat(getComputedStyle(strip).top) || 0) + strip.offsetHeight;
    return { card: rect(card), summary: rect(summary), hint: rect(hint), stuckBottom, viewport: { width: innerWidth, height: innerHeight }, fit: card.className.match(/fit\d/g) || [], summaryScroll: { width: summary.scrollWidth, clientWidth: summary.clientWidth, height: summary.scrollHeight, clientHeight: summary.clientHeight }, font: getComputedStyle(summary).fontSize };
  });
  const { card, summary, hint, stuckBottom, viewport, summaryScroll } = geometry;
  ok(`${prefix}: approved card and jump hint fit below the pinned strip`, card.left >= -1 && card.right <= viewport.width + 1 && card.top >= stuckBottom + 6 && card.bottom <= viewport.height - 10 && hint.top >= card.top && hint.bottom <= card.bottom + 1 && hint.height > 0, geometry);
  ok(`${prefix}: summary remains visible without horizontal text clipping`, summary.height > 0 && summary.top >= card.top && summary.bottom <= hint.top + 1 && summaryScroll.width <= summaryScroll.clientWidth + 1, geometry);
  console.log('INFO approved card geometry ' + JSON.stringify({ check: prefix, ...geometry }));
};
const verifyEnabled = async (p, slug, data, prefix) => {
  const row = await rowOf(p), rail = await p.$$eval('.ts-pk-rail a', nodes => nodes.map(a => a.getAttribute('href')));
  ok(`${prefix}: runtime destinations follow the rendered row`, JSON.stringify(rail) === JSON.stringify(row.map(a => a.href)), rail);
  ok(`${prefix}: one ready runtime`, await p.locator('.ts-pk-nav').count() === 1 && await p.evaluate(() => document.documentElement.classList.contains('ts-pk-ready')));
  const payloads = await p.$$eval('#ts-peek-nav-data', nodes => nodes.map(s => JSON.parse(s.textContent)));
  ok(`${prefix}: exactly the current page's data is emitted`, payloads.length === 1 && payloads[0].slug === slug && !payloads[0].pages && JSON.stringify(payloads[0].items) === JSON.stringify(data.items));
  for (let i = 0; i < row.length; i++) {
    const anchor = decodeURIComponent(row[i].href.slice(1));
    const before = await p.evaluate(() => scrollY);
    await preview(p, i);
    const actual = await p.evaluate(({ anchor, next }) => {
      const c = document.querySelector('.ts-pk-card'), sum = c.querySelector('.sum');
      const h = document.getElementById(anchor), nx = next ? document.getElementById(next) : null, range = document.createRange();
      range.setStartBefore(h); if (nx) range.setEndBefore(nx); else range.setEndAfter(document.querySelector('.entry-content').lastChild);
      const norm = t => t.replace(/\s+/g, ' ').trim();
      const paragraphs = [...range.cloneContents().querySelectorAll('p,li')].map(x => norm(x.textContent));
      const shown = sum ? [...sum.querySelectorAll('p')].map(x => norm(x.textContent)) : [];
      return { title: c.querySelector('.open > b')?.textContent, gist: !!sum?.classList.contains('is-gist'), items: sum ? [...sum.querySelectorAll('li')].map(x => x.textContent) : [], whole: shown.length > 0 && shown.every(t => paragraphs.includes(t)), visible: c.classList.contains('is-pinned'), y: scrollY };
    }, { anchor, next: row[i + 1] ? decodeURIComponent(row[i + 1].href.slice(1)) : null });
    const wanted = data.approval.status === 'approved' ? data.items.filter(item => item[1] === anchor).map(item => item[0]) : [];
    ok(`${prefix}: ${row[i].label} previews without moving the page`, actual.title === row[i].label && actual.visible && actual.y === before, actual);
    ok(`${prefix}: ${row[i].label} uses ${wanted.length ? 'approved summaries' : 'whole source paragraphs'}`, wanted.length ? actual.gist && JSON.stringify(actual.items) === JSON.stringify(wanted) : !actual.gist && actual.whole, actual);
    if (slug === capturePage && data.approval.status === 'approved') {
      await verifyCard(p, `${prefix}/${row[i].label}`);
      if (i === 0) await capture(p, slug, 'first');
      if (anchor === data.research_anchor) {
        await capture(p, slug, 'research-start');
        await p.locator('.ts-pk-card .sum').focus(); await p.keyboard.press('End'); await p.waitForTimeout(250);
        const end = await p.evaluate(() => {
          const card = document.querySelector('.ts-pk-card'), sum = card.querySelector('.sum'), last = sum.querySelector('li:last-child'), r = sum.getBoundingClientRect(), l = last.getBoundingClientRect();
          return { atEnd: sum.scrollHeight - sum.scrollTop - sum.clientHeight <= 2, lastVisible: l.top >= r.top - 1 && l.bottom <= r.bottom + 1, faded: sum.classList.contains('has-more'), pinned: card.classList.contains('is-pinned'), y: scrollY, scrollTop: sum.scrollTop, height: sum.scrollHeight, viewportHeight: sum.clientHeight, last: last.textContent };
        });
        ok(`${prefix}: keyboard reaches the research qualifications without moving the page`, end.atEnd && end.lastVisible && !end.faded && end.pinned && end.y === before, end);
        console.log('INFO research summary end ' + JSON.stringify({ check: prefix, ...end }));
        await capture(p, slug, 'research-end');
      }
    }
    await p.keyboard.press('Escape');
  }
  const measure = () => p.evaluate(() => ({ width: document.documentElement.scrollWidth, viewport: document.documentElement.clientWidth }));
  let edges = await measure(); ok(`${prefix}: right rail has no horizontal overflow`, edges.width === edges.viewport, edges);
  await preview(p, 0); await p.locator('.ts-pk-card .side').click(); await p.waitForTimeout(100);
  edges = await measure(); ok(`${prefix}: left rail has no horizontal overflow`, edges.width === edges.viewport, edges);
  await p.emulateMedia({ media: 'print' });
  const print = await p.evaluate(() => {
    const visible = e => getComputedStyle(e).display !== 'none';
    const row = document.querySelector('.ts-jump'), p = [...document.querySelectorAll('.entry-content p')].find(p => !p.closest('.ts-pk'));
    const padding = getComputedStyle(p).paddingLeft + '/' + getComputedStyle(p).paddingRight;
    document.documentElement.classList.remove('ts-pk-ready');
    const originalPadding = getComputedStyle(p).paddingLeft + '/' + getComputedStyle(p).paddingRight;
    document.documentElement.classList.add('ts-pk-ready');
    return { row: visible(row), peek: [...document.querySelectorAll('.ts-pk')].some(visible), header: getComputedStyle(document.getElementById('masthead')).position, padding, originalPadding };
  });
  ok(`${prefix}: print restores navigation and releases fixed UI`, print.row && !print.peek && print.header !== 'sticky' && print.padding === print.originalPadding, print);
  await p.emulateMedia({ media: 'screen' });
};
try {
  for (const page of manifest.pages) {
    const data = dataFor(page.slug);
    for (const width of page.slug === capturePage ? [390, 768, 1280] : [390, 768]) {
      const ctx = await context(width), { p, errors } = await open(ctx, page.slug), row = await rowOf(p);
      const prefix = `${engine}/${width}/${page.slug}`;
      ok(`${prefix}: actual section order matches the reviewed page`, JSON.stringify(row.map(a => a.label)) === JSON.stringify(page.expectedLabels), row);
      ok(`${prefix}: every fallback destination exists`, await p.evaluate(row => row.length >= 2 && row.every(a => !!document.getElementById(decodeURIComponent(a.href.slice(1)))) && new Set(row.map(a => a.href)).size === row.length, row));
      if (enabled(page)) await verifyEnabled(p, page.slug, data.approval.status === 'approved' ? data : { ...data, items: [], coverage: {} }, prefix);
      else {
        ok(`${prefix}: prepared page remains unchanged`, await p.locator('.ts-pk, #ts-peek-nav-css, #ts-peek-nav-runtime, #ts-peek-nav-data').count() === 0);
        if (fixtures) {
          // A sentinel draft phrase proves that the approval flag suppresses data even when present.
          const draft = { ...data, approval: { ...data.approval, status: 'draft' }, items: [['Unapproved fixture phrase', decodeURIComponent(row[0].href.slice(1))]] };
          await inject(p, draft); await verifyEnabled(p, page.slug, draft, prefix + '/local fixture');
        }
      }
      ok(`${prefix}: no JavaScript errors`, errors.length === 0, errors);
      await ctx.close();
    }
    const ctx = await context(390, { javaScriptEnabled: false }), { p } = await open(ctx, page.slug);
    const nojs = await p.evaluate(() => {
      const row = document.querySelector('.ts-jump');
      return !!row && getComputedStyle(row).display !== 'none' && [...row.querySelectorAll('a')].every(a => !!document.getElementById(decodeURIComponent(a.hash.slice(1)))) && !document.querySelector('.ts-pk');
    });
    ok(`${page.slug}: no-JavaScript jump row and target IDs remain available`, nojs);
    const nojsLinks = await rowOf(p);
    for (const index of [0, nojsLinks.length - 1]) {
      const target = nojsLinks[index].href;
      await p.locator('.ts-jump a').nth(index).click(); await p.waitForTimeout(150);
      const landed = await p.evaluate(hash => { const t = document.getElementById(decodeURIComponent(hash.slice(1))), r = t.getBoundingClientRect(); return location.hash === hash && r.height > 0 && r.bottom > 0 && r.top < innerHeight; }, target);
      ok(`${page.slug}: native no-JavaScript link reaches ${target}`, landed);
    }
    await ctx.close();
  }
  if (fixtures) {
    // Mutate one real prepared page before injecting: filtered/reordered row and
    // nested native details. All assertions use destination identity, not indices.
    const page = manifest.pages.find(p => !enabled(p)) || manifest.pages.find(p => p.slug !== manifest.regression);
    {
      // Keep explicit approval-gating coverage even after every production
      // registration has approved text and no prepared page remains.
      const draftCtx = await context(390), { p: draftPage, errors: draftErrors } = await open(draftCtx, page.slug, true);
      const row = await rowOf(draftPage);
      const draft = { ...dataFor(page.slug), approval: { status: 'draft', source: 'isolated approval-gating fixture' }, items: [['Unapproved fixture phrase', decodeURIComponent(row[0].href.slice(1))]] };
      await inject(draftPage, draft); await verifyEnabled(draftPage, page.slug, draft, `${engine}/${page.slug}/explicit draft fixture`);
      ok(`${engine}: explicit draft fixture has no JavaScript errors`, draftErrors.length === 0, draftErrors); await draftCtx.close();
    }
    {
      const delayedCtx = await context(390), { p: delayed, errors: delayedErrors } = await open(delayedCtx, page.slug, true);
      const before = await rowOf(delayed);
      await delayed.evaluate(() => { const a = document.querySelector('.ts-jump a'), id = decodeURIComponent(a.hash.slice(1)), target = document.getElementById(id); target.removeAttribute('id'); setTimeout(() => { target.id = id; }, 500); });
      await inject(delayed, { ...dataFor(page.slug), approval: { status: 'draft', source: 'delayed ID fixture' }, items: [] });
      ok(`${engine}: a delayed target ID is retained after initialization waits`, JSON.stringify(await delayed.$$eval('.ts-pk-rail a', a => a.map(x => x.getAttribute('href')))) === JSON.stringify(before.map(a => a.href)));
      ok(`${engine}: delayed ID fixture has no JavaScript errors`, delayedErrors.length === 0, delayedErrors); await delayedCtx.close();
    }
    for (const count of [0, 1, 2, 7]) {
      const shapeCtx = await context(390), { p: shape, errors: shapeErrors } = await open(shapeCtx, page.slug, true);
      await shape.evaluate(count => {
        const content = document.querySelector('.entry-content');
        const items = Array.from({ length: count }, (_, i) => ({ id: 'shape-' + i, name: 'Fixture section ' + i }));
        content.innerHTML = (count ? '<nav class="ts-jump"><ul>' + items.map(x => '<li><a href="#' + x.id + '">' + x.name + '</a></li>').join('') + '</ul></nav>' : '') + items.map(x => '<h2 id="' + x.id + '">' + x.name + '</h2><p>A complete fixture paragraph containing 1.5 hours and U.S. notation. A quotation says "keep every sentence."</p><div style="height:350px"></div>').join('');
      }, count);
      await inject(shape, { ...dataFor(page.slug), approval: { status: 'draft', source: 'synthetic section-count fixture' }, items: [] });
      if (count < 2) {
        ok(`${engine}: ${count}-destination fixture keeps the plain fallback`, await shape.locator('.ts-pk-nav').count() === 0 && await shape.evaluate(() => !document.documentElement.classList.contains('ts-pk-ready') && (!document.querySelector('.ts-jump') || getComputedStyle(document.querySelector('.ts-jump')).display !== 'none')));
      } else {
        ok(`${engine}: ${count}-destination fixture initializes the matching rail`, await shape.locator('.ts-pk-rail a').count() === count);
        await preview(shape, count - 1);
        ok(`${engine}: ${count}-destination fixture preserves whole tricky paragraphs`, await shape.locator('.ts-pk-card .sum p').first().textContent() === 'A complete fixture paragraph containing 1.5 hours and U.S. notation. A quotation says "keep every sentence."');
      }
      ok(`${engine}: ${count}-destination fixture has no JavaScript errors`, shapeErrors.length === 0, shapeErrors); await shapeCtx.close();
    }
    const ctx = await context(390), { p, errors } = await open(ctx, page.slug, true);
    const original = await rowOf(p);
    await p.evaluate(() => {
      const list = document.querySelector('.ts-jump ul'); list.prepend(list.lastElementChild);
      const li = document.createElement('li'); li.innerHTML = '<a href="#missing-fixture">Missing target</a>'; list.prepend(li);
      const content = document.querySelector('.ts-jump').parentElement;
      const d = document.createElement('details'); d.innerHTML = '<summary>Native outer</summary><details><summary>Native inner</summary><h2 id="peek-native-target">Native closed target</h2><p>A complete fixture paragraph about 1.5 hours in the U.S. A visitor said "keep it whole."</p><div style="height:1000px"></div></details>'; content.appendChild(d);
      const target = document.createElement('li'); target.innerHTML = '<a href="#peek-native-target">Native detail</a>'; list.appendChild(target);
    });
    await inject(p, { ...dataFor(page.slug), approval: { status: 'draft', source: 'isolated browser fixture' }, items: [] });
    const rail = await p.$$eval('.ts-pk-rail a', nodes => nodes.map(a => a.hash));
    ok(`${engine}: reordered/filtered navigation resolves by target`, JSON.stringify(rail) === JSON.stringify([...original.map(a => a.href), '#peek-native-target']), rail);
    // Programmatic row click exercises the hidden fallback's capture listener.
    const target = original[original.length - 1].href;
    await p.locator(`.ts-jump a[href="${target}"]`).evaluate(a => a.click()); await p.waitForTimeout(100);
    ok(`${engine}: row click after reordering jumps to its own destination`, await p.evaluate(target => location.hash === target && document.activeElement.id === decodeURIComponent(target.slice(1)), target));
    await preview(p, rail.length - 1); await p.keyboard.press('Enter'); await p.waitForTimeout(100);
    const detail = await p.evaluate(() => {
      const h = document.getElementById('peek-native-target'), box = h.getBoundingClientRect(), strip = document.querySelector('.ts-pk-top').getBoundingClientRect();
      let open = true; for (let p = h.parentElement; p; p = p.parentElement) if (p.tagName === 'DETAILS' && !p.open) open = false;
      return { open, visible: box.height > 0, aligned: box.top >= strip.bottom - 2 && box.top <= strip.bottom + 40, focus: document.activeElement === h, targetTop: box.top, stripBottom: strip.bottom, scroll: scrollY, height: document.documentElement.scrollHeight };
    });
    ok(`${engine}: jumping opens every native-details ancestor and aligns/focuses target`, detail.open && detail.visible && detail.aligned && detail.focus, detail);
    ok(`${engine}: structural fixture has no JavaScript errors`, errors.length === 0, errors); await ctx.close();
  }
} finally { await b.close(); }
console.log(`INFO Peek pages ${engine}: ${pass} passed, ${fail} failed; touch/accessibility simulation does not establish physical-device or screen-reader support.`);
process.exit(fail ? 1 : 0);

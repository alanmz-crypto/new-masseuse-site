// Read-only navigation contract. --fixture models rendered server markup and uses the actual native runtime/CSS; WordPress rehearsal verifies PHP emission.
// node check-page-navigation.mjs --fixture | http://localhost:8090 | https://staging38.thaisolitude.com | https://thaisolitude.com [inline]
import { createRequire } from 'node:module';
import { readFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium, firefox } = require('playwright');
const engine = process.env.TS_BROWSER || 'chromium';
if (!['chromium', 'firefox'].includes(engine)) throw new Error('TS_BROWSER must be chromium or firefox');
const base = (process.argv[2] || '').replace(/\/$/, '');
const fixture = base === '--fixture';
if (!fixture && !['http://localhost:8090', 'https://staging38.thaisolitude.com', 'https://thaisolitude.com'].includes(base)) throw new Error('Exact localhost/staging38/HTTPS apex live or --fixture only');
const css = readFileSync(new URL('../section-guide/section-guide.css', import.meta.url), 'utf8');
const php = readFileSync(new URL('./section-guide.php', import.meta.url), 'utf8');
const script = php.match(/<script id="ts-section-guide-runtime">([\s\S]*?)<\/script>/)?.[1];
if (!script) throw new Error('Cannot find static section-guide runtime script');
const progressCSS = readFileSync(new URL('../reading-progress-sitewide/reading-progress.css', import.meta.url), 'utf8');
const baselineCSS = fixture && process.env.TS_BASELINE_CSS ? readFileSync(process.env.TS_BASELINE_CSS, 'utf8') : '';
// Exact labels/headings and order from the current five stored pages.
const item = (label, heading, id, tag = 'h2') => ({ label, heading, id, tag });
const maps = {
  services: [
    item('Chair massage', 'Chair Massage', 'ts-chair-massage', 'h3'),
    item('Corporate & group', 'Corporate & Group Massage', 'ts-corporate-group', 'h3'),
    item('Therapeutic massage', 'Therapeutic Massage', 'ts-therapeutic-massage', 'h3'),
    item('Deep tissue massage', 'Deep Tissue Massage', 'ts-deep-tissue-massage', 'h3'),
    item('Thai massage', 'Thai Massage', 'ts-thai-massage', 'h3'),
    item('Your session', 'Your Session', 'ts-your-session', 'h3'),
    item('What to expect', 'What to Expect During a Massage Session', 'ts-what-to-expect'),
  ],
};
for (const [slug, title, benefits] of [['thai-massage', 'Thai', false], ['therapeutic-massage', 'Therapeutic', true], ['deep-tissue-massage', 'Deep Tissue', false], ['chair-massage', 'Chair', true]]) {
  const questions = item('Questions', `Questions About ${title} Massage`, 'ts-guide-questions');
  const expect = item('What to expect', 'What To Expect During Your Appointment', 'ts-what-to-expect');
  maps[slug] = [item('What it is', `What Is ${title} Massage?`, 'ts-what-it-is'), item('Common reasons', `Common Reasons Clients Choose ${title} Massage`, 'ts-common-reasons'),
    ...(benefits ? [item('Who can benefit', `Who Can Benefit From ${title} Massage?`, 'ts-who-can-benefit'), questions, expect] : [expect, questions]),
    item('What Research Says', `What the Research Says About ${title} Massage`, 'ts-guide-research')];
}
const services = Object.keys(maps);
const legacyMaps = {
  '': [item('Learn about Thai Solitude', 'Learn About Thai Solitude', 'ts-learn-about-thai-solitude'), item('The massage I offer', 'The Massage I Offer', 'ts-the-massage-i-offer'), item('How I work', 'How I Work', 'ts-how-i-work')],
  about: [item('The person behind Thai Solitude', 'The Person Behind Thai Solitude', 'ts-the-person-behind-thai-solitude'), item('About Thai Solitude', 'Learn About Thai Solitude', 'ts-about-thai-solitude'), item('From Chicago to Portland', 'From Chicago to Portland', 'ts-from-chicago-to-portland')],
  contact: [{ label: 'Contact details', id: 'ts-contact-details', kind: 'block', needle: 'Phone' }, item('Corporate quote', 'Corporate & group massage', 'corporate-quote'), item('For employers', 'Hiring a massage therapist?', 'employers')],
  'privacy-policy': [{ label: 'Online privacy', id: 'ts-online-privacy', kind: 'faq' }, { label: 'Client privacy', id: 'ts-client-privacy', kind: 'faq' }],
  accessibility: [item('What has been checked', 'What has been checked', 'ts-what-has-been-checked'), item('Known limitations', 'Known limitations', 'ts-known-limitations'), item('Feedback', 'Feedback', 'ts-feedback')],
};
const excluded = ['', 'about', 'contact', 'privacy-policy', 'accessibility', 'corporate-massage-portland', 'work-with-me'];
const jumpSource = readFileSync(new URL('./jump-links.php', import.meta.url), 'utf8');
const privacyScript = jumpSource.match(/echo '(<script>document\.addEventListener[\s\S]*?<\/script>)';/)?.[1];
if (fixture && !privacyScript) throw new Error('Cannot find the source Privacy FAQ helper');
const trigger = '#ts-section-guide-trigger';
const panel = '#ts-section-guide-popover';
const browser = await (engine === 'firefox' ? firefox : chromium).launch(engine === 'chromium' && process.env.TS_CHROMIUM_EXECUTABLE ? { executablePath: process.env.TS_CHROMIUM_EXECUTABLE } : {});
console.log(`INFO page navigation browser: ${engine} ${browser.version()}`);
let assertions = 0, failures = 0, invalid = false;
function check(label, ok, detail = '') { assertions++; if (!ok) failures++; console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${!ok && detail ? ': ' + detail : ''}`); }
const noScriptContexts = new WeakSet();
const tick = page => noScriptContexts.has(page.context()) ? page.waitForTimeout(40) : page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
async function settle(page) { await tick(page); await page.waitForTimeout(120); await tick(page); }
function fixtureHTML(slug, short = false, tallHeader = false) {
  const scoped = services.includes(slug), entries = maps[slug] || legacyMaps[slug] || [item('Introduction', 'Introduction', 'intro'), item('Details', 'Details', 'details')];
  const escape = text => text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
  const nav = `<nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list">${entries.map(entry => `<li><a href="#${entry.id}">${escape(entry.label)}</a></li>`).join('')}</ul></nav>`;
  const sections = entries.map(entry => entry.kind === 'faq' ? `<div id="${entry.id}" class="uagb-faq-item"><button class="uagb-faq-questions-button"><span class="uagb-question">${escape(entry.label)}</span></button><div class="uagb-faq-content" style="display:none;min-height:180px">Existing privacy information.</div></div>` : entry.kind === 'block' ? `<section id="${entry.id}"><p>Phone: existing contact details.</p></section>` : `<section><${entry.tag} id="${entry.id}">${escape(entry.heading)}</${entry.tag}><p>Existing page information.</p></section>`).join('');
  // Represent Spectra's accordion interaction while exercising the actual PHP-emitted jump helper.
  const privacy = slug === 'privacy-policy' ? `<script>document.querySelectorAll('.uagb-faq-questions-button').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('.uagb-faq-item').forEach(item=>{item.classList.remove('uagb-faq-item-active');item.querySelector('.uagb-faq-content').style.display='none';});const item=button.closest('.uagb-faq-item');item.classList.add('uagb-faq-item-active');item.querySelector('.uagb-faq-content').style.display='block';}));</script>${privacyScript}` : '';

  const sticky = slug === 'corporate-massage-portland' ? 'ts-corp__sticky' : slug === 'work-with-me' ? 'ts-wwm__sticky' : '';
  return `<!doctype html><html lang="en"><head><title>Page navigation fixture</title><style>
  :root{--ts-panel:#fff;--ts-ink:#1c2531;--ts-accent-deep:#7a520a;--ts-rule:#d5d6cf}html[data-ts-theme="dark"]{--ts-panel:#202936;--ts-ink:#f1f0ea;--ts-accent-deep:#e2b866;background:#151c26;color:#f1f0ea}
  body{margin:0;font:1rem/1.6 system-ui}header,footer,main{padding:24px;box-sizing:border-box}main{max-width:1000px;margin:auto}header{height:${short ? '0' : tallHeader ? '1400px' : '130px'}}section{min-height:${short ? '0' : '700px'}}footer{height:${short ? '80px' : '280px'}}a{color:inherit}#ast-scroll-top{position:fixed;right:16px;bottom:16px;width:44px;height:44px}
  </style><style>${baselineCSS}</style>${short ? '<style>header{display:none}main{padding:0}.entry-content :is(h1,h2,h3,p){margin:0;font-size:16px;line-height:1.2}footer{height:40px;padding:0}</style>' : ''}<style id="wp-custom-css">${progressCSS}\n${css}</style></head><body><header><a href="#content">Skip to content</a></header><main id="content" class="entry-content">${nav}<h1>Existing page information</h1>${sections}${sticky ? `<div class="${sticky}"><a href="mailto:example@example.test">Contact</a></div>` : ''}</main><footer id="colophon"><a href="#${entries[0].id}">Footer link</a><p>Oregon License LMT-29905</p></footer><button id="ast-scroll-top" aria-label="Scroll to top">↑</button>${scoped ? `<script id="ts-section-guide-runtime">${script}</script>` : ''}${privacy}</body></html>`;
}
async function contextFor({ noJS = false, unsupported = false, admin = false, touch = false } = {}) {
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'no-preference', javaScriptEnabled: !noJS, hasTouch: touch });
  if (noJS) noScriptContexts.add(context);
  if (unsupported) await context.addInitScript(() => { delete HTMLElement.prototype.showPopover; delete HTMLElement.prototype.hidePopover; });
  if (admin) await context.addInitScript(() => { const observer = new MutationObserver(() => { if (document.body) { document.body.classList.add('admin-bar'); observer.disconnect(); } }); observer.observe(document, { childList: true, subtree: true }); });
  if (fixture) await context.route('**/*', route => { const url = new URL(route.request().url()); const slug = url.pathname.replace(/^\/+|\/+$/g, ''); route.fulfill({ contentType: 'text/html', body: fixtureHTML(slug, url.searchParams.has('short'), url.searchParams.has('tall-header')) }); });
  return context;
}
async function load(context, slug, suffix = '') {
  const page = await context.newPage();
  const errors = []; page.on('pageerror', e => errors.push(e.message));
  const response = await page.goto((fixture ? 'http://page-navigation.test' : base) + '/' + (slug ? slug + '/' : '') + suffix, { waitUntil: 'networkidle', timeout: 60000 });
  if (response?.status() === 202 || /Robot Challenge/i.test(await page.title())) { invalid = true; throw new Error('INVALID: hosting challenge, no further requests'); }
  check(`${slug || 'home'}: HTTP 200`, response?.status() === 200);
  if (!noScriptContexts.has(context)) {
    await page.addStyleTag({ content: 'html{scroll-behavior:auto!important}' });
    await page.evaluate(() => document.fonts.ready);
  }
  await settle(page);
  return { page, errors };
}
async function scrollState(page) {
  return page.evaluate(({ trigger, panel }) => ({ scrollY, viewport: { width: innerWidth, height: innerHeight }, documentHeight: document.documentElement.scrollHeight, nav: document.querySelector('.entry-content .ts-jump')?.getBoundingClientRect().toJSON(), footer: document.querySelector('#colophon')?.getBoundingClientRect().toJSON(), triggerHidden: document.querySelector(trigger)?.hidden, panelOpen: document.querySelector(panel)?.matches(':popover-open') }), { trigger, panel });
}
async function pastNav(page) {
  await settle(page);
  await page.evaluate(() => { document.activeElement?.blur(); const nav = document.querySelector('.entry-content .ts-jump'); scrollTo(0, scrollY + nav.getBoundingClientRect().bottom + 100); });
  await settle(page);
  if (await page.locator(trigger).count() && !await page.locator(trigger).isVisible()) console.log('INFO DIAGNOSTIC hidden guide after pastNav: ' + JSON.stringify(await scrollState(page)));
}
async function isOpen(page) { return page.locator(panel).evaluate(el => el.matches(':popover-open')); }
async function focusState(page) {
  return page.evaluate(panel => { const active = document.activeElement, links = document.querySelectorAll(panel + ' a'); return { documentHasFocus: document.hasFocus(), lastPanelLinkFocused: active === links[links.length - 1], insidePanel: Boolean(active?.closest(panel)), activeElement: active ? { tag: active.tagName.toLowerCase(), id: active.id, class: active.getAttribute('class') || '', href: active.getAttribute('href') } : null }; }, panel);
}
async function entryState(page) {
  return page.locator('.entry-content').evaluate(el => ({
    html: el.innerHTML, text: el.innerText, width: document.documentElement.scrollWidth,
    rects: [...el.querySelectorAll('*')].map(item => {
      const r = item.getBoundingClientRect();
      return { tag: item.tagName.toLowerCase(), id: item.id, class: item.getAttribute('class') || '', rect: [r.x + scrollX, r.y + scrollY, r.width, r.height] };
    }),
  }));
}
function entryDiff(before, after) {
  const changed = [];
  for (let index = 0; index < Math.max(before.rects.length, after.rects.length) && changed.length < 5; index++) {
    if (JSON.stringify(before.rects[index]) !== JSON.stringify(after.rects[index])) changed.push({ index, before: before.rects[index], after: after.rects[index] });
  }
  return { htmlUnchanged: before.html === after.html, textUnchanged: before.text === after.text, width: { before: before.width, after: after.width }, elementCount: { before: before.rects.length, after: after.rects.length }, firstChangedElements: changed };
}
async function stableEntryState(page, label) {
  // Settle independently of the expected popup state or the comparison baseline.
  // Scrolling can start lazy asset requests, so let those begin before network idle.
  const initial = await entryState(page);
  await settle(page);
  await page.waitForLoadState('networkidle', { timeout: 15000 });
  await page.evaluate(() => document.fonts.ready);
  let previous = await entryState(page), unchangedSamples = 0;
  for (let attempt = 0; attempt < 10; attempt++) {
    await settle(page);
    const current = await entryState(page);
    unchangedSamples = JSON.stringify(previous) === JSON.stringify(current) ? unchangedSamples + 1 : 0;
    if (unchangedSamples >= 2) {
      if (JSON.stringify(initial) !== JSON.stringify(current)) console.log(`INFO DIAGNOSTIC layout changed while settling ${label}: ${JSON.stringify(entryDiff(initial, current))}`);
      return current;
    }
    if (attempt === 9) throw new Error(`Layout did not stabilize for ${label}: ${JSON.stringify(entryDiff(previous, current))}`);
    previous = current;
  }
}

async function fit(page, label) {
  const result = await page.evaluate(({ trigger, panel }) => {
    const a = document.querySelector(trigger).getBoundingClientRect(), b = document.querySelector(panel).getBoundingClientRect();
    const inside = r => r.left >= 0 && r.top >= 0 && r.right <= innerWidth + 1 && r.bottom <= innerHeight + 1;
    const overlap = (x, y) => x.left < y.right && x.right > y.left && x.top < y.bottom && x.bottom > y.top;
    const scrollTop = document.querySelector('#ast-scroll-top');
    const style = scrollTop && getComputedStyle(scrollTop), r = scrollTop && scrollTop.getBoundingClientRect();
    const scrollVisible = style && style.display !== 'none' && style.visibility !== 'hidden' && Number(style.opacity) > 0 && r.width > 0 && r.height > 0;
    return { fits: inside(a) && inside(b), min44: a.height >= 44 && a.width >= 44, separated: !overlap(a, b), avoidsTop: !scrollVisible || (!overlap(a, r) && !overlap(b, r)), a: a.toJSON(), b: b.toJSON(), scrollTop: r?.toJSON() };
  }, { trigger, panel });
  check(`${label}: controls fit viewport and remain separate`, result.fits && result.separated, JSON.stringify(result));
  check(`${label}: trigger at least 44px`, result.min44, JSON.stringify(result));
  check(`${label}: no scroll-to-top overlap`, result.avoidsTop, JSON.stringify(result));
}
async function destinationState(page, href, expected) {
  return page.evaluate(({ href, expected }) => {
    const id = decodeURIComponent(href.slice(1)), target = document.getElementById(id);
    const heading = target?.matches('h1,h2,h3,h4,h5,h6') ? target : target?.querySelector('h1,h2,h3,h4,h5,h6');
    const rect = heading?.getBoundingClientRect();
    return { hash: location.hash, correctHeading: heading?.textContent.replace(/\s+/g, ' ').trim() === expected.heading,
      focused: document.activeElement === heading, reached: Boolean(rect && rect.top < innerHeight && rect.bottom > 0),
      unique: [...document.querySelectorAll('[id]')].filter(el => el.id === id).length === 1,
      inContent: Boolean(target && document.querySelector('.entry-content')?.contains(target)), rect: rect?.toJSON() };
  }, { href, expected });
}
async function reachedDestination(page, href, expected) {
  let state;
  for (let attempt = 0; attempt < 16; attempt++) {
    state = await destinationState(page, href, expected);
    if (state.hash === href && state.reached) return state;
    await page.waitForTimeout(100);
  }
  return state;
}
async function inlineContract(page, slug) {
  const expected = maps[slug] || legacyMaps[slug];
  const links = await page.locator('.entry-content .ts-jump a').evaluateAll(anchors => anchors.map(a => ({ text: a.textContent, href: a.getAttribute('href') })));
  check(`${slug}: complete inline labels in page order`, JSON.stringify(links.map(link => link.text)) === JSON.stringify(expected.map(entry => entry.label)), JSON.stringify(links));
  const structure = await page.locator('.entry-content .ts-jump a').evaluateAll((anchors, expected) => {
    const targets = anchors.map(a => { try { return a.getAttribute('href')?.startsWith('#') ? document.getElementById(decodeURIComponent(a.getAttribute('href').slice(1))) : null; } catch { return null; } });
    const headings = targets.map(target => target?.matches('h1,h2,h3,h4,h5,h6') ? target : target?.querySelector('h1,h2,h3,h4,h5,h6'));
    return { unique: targets.every(target => target && document.querySelector('.entry-content').contains(target) && [...document.querySelectorAll('[id]')].filter(el => el.id === target.id).length === 1),
      correct: headings.length === expected.length && headings.every((heading, index) => expected[index].kind === 'faq' ? targets[index]?.querySelector('.uagb-question')?.textContent.trim() === expected[index].label : expected[index].kind === 'block' ? targets[index]?.textContent.includes(expected[index].needle) : heading?.textContent.replace(/\s+/g, ' ').trim() === expected[index].heading),
      ordered: targets.every((target, index) => target && (!index || Boolean(targets[index - 1]?.compareDocumentPosition(target) & Node.DOCUMENT_POSITION_FOLLOWING))) };
  }, expected);
  check(`${slug}: every inline target is unique, in content, and the expected heading`, structure.unique && structure.correct, JSON.stringify(structure));
  check(`${slug}: inline destinations follow document order`, structure.ordered, JSON.stringify(structure));
  if (slug === 'services') {
    const hearts = await page.evaluate(() => ['ts-therapeutic-massage', 'ts-your-session'].map(id => ({ id, count: [...document.querySelectorAll('[id]')].filter(el => el.id === id).length, text: document.getElementById(id)?.textContent.replace(/\s+/g, ' ').trim() })));
    check('Services: legacy heart destinations remain unique and unchanged', hearts[0].count === 1 && hearts[0].text === 'Therapeutic Massage' && hearts[1].count === 1 && hearts[1].text === 'Your Session', JSON.stringify(hearts));
  }
  if (maps[slug] && slug !== 'services') check(`${slug}: prior Questions and Research bookmarks preserved`, links.find(link => link.text === 'Questions')?.href === '#ts-guide-questions' && links.find(link => link.text === 'What Research Says')?.href === '#ts-guide-research', JSON.stringify(links));
  return links;
}
const context = await contextFor();
try {
  for (const slug of services) {
    const { page, errors } = await load(context, slug);
    const original = page.locator('.entry-content .ts-jump');
    const links = await inlineContract(page, slug);
    check(`${slug}: original navigation remains present`, await original.count() === 1);
    check(`${slug}: native auto popover`, await page.locator(panel).getAttribute('popover') === 'auto');
    check(`${slug}: UI outside page content`, await page.locator('.entry-content ' + trigger + ', .entry-content ' + panel).count() === 0);
    const guideLinks = await page.locator(panel + ' a').evaluateAll(items => items.map(a => ({ text: a.textContent, href: a.getAttribute('href') })));
    check(`${slug}: native guide exactly matches every inline label and fragment`, JSON.stringify(links) === JSON.stringify(guideLinks), JSON.stringify(guideLinks));
    check(`${slug}: guide hidden before scrolling`, !await page.locator(trigger).isVisible());
    for (const width of [1280, 390, 320]) {
      await page.setViewportSize({ width, height: 844 });
      for (const theme of ['light', 'dark']) {
        await page.evaluate(theme => document.documentElement.setAttribute('data-ts-theme', theme), theme);
        await pastNav(page);
        const label = `${slug} ${width} ${theme}`;
        check(`${label}: original nav above viewport`, await original.evaluate(el => el.getBoundingClientRect().bottom < 0));
        check(`${label}: guide appears after navigation leaves`, await page.locator(trigger).isVisible());
        const before = await stableEntryState(page, `${label} before opening`);
        await page.locator(trigger).click(); await settle(page);
        check(`${label}: opens`, await isOpen(page));
        await fit(page, label);
        const after = await stableEntryState(page, `${label} after opening`);
        check(`${label}: original content and geometry unchanged`, JSON.stringify(before) === JSON.stringify(after), JSON.stringify(entryDiff(before, after)));
        await page.keyboard.press('Escape'); await settle(page);
        check(`${label}: Escape closes`, !await isOpen(page));
      }
    }
    await pastNav(page);
    await page.locator(trigger).focus(); await page.keyboard.press('Enter'); await settle(page);
    await page.keyboard.press('Tab');
    check(`${slug}: Tab reaches panel links`, await page.evaluate(panel => Boolean(document.activeElement.closest(panel)), panel));
    await page.keyboard.press('Escape'); await settle(page);
    check(`${slug}: Escape restores trigger focus`, await page.locator(trigger).evaluate(el => document.activeElement === el));
    await page.locator(trigger).click(); await settle(page);
    await page.locator(panel + ' a').last().focus();
    const beforeTab = await focusState(page);
    check(`${slug}: Tab exit starts at focused last panel link`, beforeTab.documentHasFocus && beforeTab.lastPanelLinkFocused, JSON.stringify(beforeTab));
    await page.keyboard.press('Tab');
    const afterTab = await focusState(page);
    const firstFooterLink = page.locator('#colophon a[href]:not([tabindex="-1"]):visible').first();
    afterTab.firstFooterLinkFocused = await firstFooterLink.evaluate(el => document.activeElement === el);
    console.log(`INFO ${slug}: Tab focus transition ${JSON.stringify({ before: beforeTab, after: afterTab })}`);
    // A real document successor avoids Firefox's end-of-document focus ambiguity
    // (microsoft/playwright#39268), and verifies the guide's native reading order.
    check(`${slug}: Tab reaches first footer link`, afterTab.documentHasFocus && afterTab.firstFooterLinkFocused, JSON.stringify({ before: beforeTab, after: afterTab }));
    await page.keyboard.press('Shift+Tab');
    const afterReverseTab = await focusState(page);
    check(`${slug}: Shift+Tab returns to last panel link`, afterReverseTab.documentHasFocus && afterReverseTab.lastPanelLinkFocused, JSON.stringify(afterReverseTab));
    if (await isOpen(page)) await page.keyboard.press('Escape');
    await pastNav(page); await page.locator(trigger).click(); await settle(page);
    await page.mouse.click(300, 40); await settle(page);
    check(`${slug}: outside click closes`, !await isOpen(page));
    const htmlBeforeJump = await page.locator('.entry-content').innerHTML();
    for (let index = 0; index < maps[slug].length; index++) {
      const entry = maps[slug][index], href = links[index].href;
      await original.locator('a').nth(index).click(); await settle(page);
      let state = await reachedDestination(page, href, entry);
      check(`${slug}: inline ${entry.label} reaches and focuses its heading`, state.hash === href && state.reached && state.focused && state.correctHeading && state.unique && state.inContent, JSON.stringify(state));
      await page.evaluate(() => document.activeElement.blur()); await settle(page);
      await pastNav(page); await page.locator(trigger).click(); await settle(page);
      await page.locator(panel + ' a').nth(index).click(); await settle(page);
      state = await reachedDestination(page, href, entry);
      check(`${slug}: native ${entry.label} reaches/focuses its heading and dismisses`, state.hash === href && state.reached && state.focused && state.correctHeading && state.unique && state.inContent && !await isOpen(page), JSON.stringify(state));
      await page.evaluate(() => document.activeElement.blur()); await settle(page);
    }
    check(`${slug}: every navigation focus cleanup preserves original content`, htmlBeforeJump === await page.locator('.entry-content').innerHTML());
    await original.locator('a').nth(0).click(); await settle(page);
    await original.locator('a').nth(1).click(); await settle(page);
    await page.goBack(); await settle(page);
    check(`${slug}: native Back restores preceding fragment`, new URL(page.url()).hash === links[0].href);
    await page.goForward(); await settle(page);
    check(`${slug}: native Forward restores selected fragment`, new URL(page.url()).hash === links[1].href);
    await page.evaluate(() => document.activeElement.blur());
    for (const media of [{ reducedMotion: 'reduce' }, { forcedColors: 'active' }]) {
      await page.emulateMedia(media); await pastNav(page); await page.locator(trigger).click(); await settle(page);
      check(`${slug}: functional with ${JSON.stringify(media)}`, await isOpen(page));
      check(`${slug}: no guide animation`, await page.locator(trigger + ', ' + panel).evaluateAll(elements => elements.every(el => el.getAnimations({ subtree: true }).length === 0)));
      await page.keyboard.press('Escape'); await page.emulateMedia({ reducedMotion: 'no-preference', forcedColors: 'none' });
    }
    await pastNav(page); await page.locator(trigger).click(); await page.emulateMedia({ media: 'print' });
    check(`${slug}: print hides both controls`, !await page.locator(trigger).isVisible() && !await page.locator(panel).isVisible());
    await page.emulateMedia({ media: 'screen' }); if (await isOpen(page)) await page.keyboard.press('Escape');
    await pastNav(page); await page.locator(trigger).click(); await settle(page);
    await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight)); await settle(page);
    check(`${slug}: open guide retains focus/visibility near footer`, await isOpen(page) && await page.locator(trigger).isVisible());
    await page.keyboard.press('Escape'); await page.evaluate(() => document.activeElement.blur()); await settle(page);
    check(`${slug}: hides when footer is visible`, !await page.locator(trigger).isVisible());
    await page.evaluate(() => scrollTo(0, 0)); await settle(page);
    check(`${slug}: original nav returns without duplicate guide`, !await page.locator(trigger).isVisible() && await original.isVisible());
    if (slug === 'services') {
      await page.evaluate(() => document.documentElement.style.fontSize = '200%'); await page.setViewportSize({ width: 320, height: 568 }); await pastNav(page); await page.locator(trigger).click(); await settle(page);
      await fit(page, `${slug} 320×568 200% text`);
      await page.locator(panel + ' a').last().focus(); await settle(page);
      const overflowReach = await page.locator(panel).evaluate(el => { const links = el.querySelectorAll('a'), tail = links[links.length - 1].getBoundingClientRect(), box = el.getBoundingClientRect(); return { count: links.length, scrollable: el.scrollHeight > el.clientHeight, lastVisible: tail.top >= box.top && tail.bottom <= box.bottom }; });
      check('Services: all seven links remain reachable in enlarged scrollable panel', overflowReach.count === 7 && overflowReach.scrollable && overflowReach.lastVisible, JSON.stringify(overflowReach));
      if (process.env.TS_SCREENSHOT_DIR) { mkdirSync(process.env.TS_SCREENSHOT_DIR, { recursive: true }); await page.screenshot({ path: join(process.env.TS_SCREENSHOT_DIR, 'page-navigation-services-200-percent.png') }); }
      await page.keyboard.press('Escape'); await page.evaluate(() => document.documentElement.style.removeProperty('font-size'));
      if (process.argv.includes('inline') || process.env.TS_SCREENSHOT_DIR) {
        await page.setViewportSize({ width: 390, height: 844 }); await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light')); await pastNav(page); await page.locator(trigger).click(); await settle(page);
        if (process.env.TS_SCREENSHOT_DIR) { mkdirSync(process.env.TS_SCREENSHOT_DIR, { recursive: true }); await page.screenshot({ path: join(process.env.TS_SCREENSHOT_DIR, 'page-navigation-services-mobile.png') }); }
        if (process.argv.includes('inline')) console.log(`INLINE page-navigation-services-mobile.jpg base64:${(await page.screenshot({ type: 'jpeg', quality: 70 })).toString('base64')}`);
      }
    }
    check(`${slug}: reading-progress CSS preserved`, await page.locator('style').evaluateAll((styles, expected) => styles.some(s => s.textContent.includes(expected.trim())), progressCSS));
    check(`${slug}: no browser script errors`, errors.length === 0, errors.join('; '));
    await page.close();
  }
  for (const slug of excluded) {
    const { page, errors } = await load(context, slug);
    await page.evaluate(() => scrollTo(0, document.documentElement.scrollHeight / 2)); await settle(page);
    if (legacyMaps[slug]) {
      const legacyLinks = await inlineContract(page, slug);
      if (slug === 'privacy-policy') {
        for (let index = 0; index < legacyLinks.length; index++) {
          await page.locator('.entry-content .ts-jump a').nth(index).click(); await page.waitForTimeout(850);
          const state = await page.evaluate(href => { const target = document.getElementById(decodeURIComponent(href.slice(1))); const content = target?.querySelector('.uagb-faq-content'); const rect = target?.getBoundingClientRect(); return { hash: location.hash, active: target?.classList.contains('uagb-faq-item-active'), answerVisible: Boolean(content && content.getBoundingClientRect().height > 0 && getComputedStyle(content).display !== 'none'), reached: Boolean(rect && rect.top < innerHeight && rect.bottom > 0) }; }, legacyLinks[index].href);
          check(`Privacy: ${legacyLinks[index].text} opens its FAQ and reaches the target`, state.hash === legacyLinks[index].href && state.active && state.answerVisible && state.reached, JSON.stringify(state));
        }
      }
    }
    check(`${slug || 'home'}: no guide added`, await page.locator(trigger + ', ' + panel).count() === 0);
    check(`${slug || 'home'}: runtime not emitted outside scoped pages`, await page.locator('#ts-section-guide-runtime').count() === 0);
    const styled = await stableEntryState(page, `${slug || 'home'} with guide styles`);
    await page.locator('style').evaluateAll(styles => { for (const style of styles) style.textContent = style.textContent.replace(/\/\* ts-section-guide:start \*\/[\s\S]*?\/\* ts-section-guide:end \*\//g, ''); });
    await settle(page);
    const unstyled = await stableEntryState(page, `${slug || 'home'} without guide styles`);
    check(`${slug || 'home'}: content and any fixed CTA geometry unaffected by guide styles`, JSON.stringify(styled) === JSON.stringify(unstyled), JSON.stringify(entryDiff(styled, unstyled)));
    check(`${slug || 'home'}: no browser errors`, errors.length === 0, errors.join('; '));
    await page.close();
  }
  const touchContext = await contextFor({ touch: true });
  const { page: touchPage } = await load(touchContext, 'services');
  await touchPage.setViewportSize({ width: 390, height: 844 }); await pastNav(touchPage);
  await touchPage.locator(trigger).tap(); await settle(touchPage);
  check('Services touch input: tap opens guide', await isOpen(touchPage));
  await touchPage.touchscreen.tap(300, 40); await settle(touchPage);
  check('Services touch input: outside tap dismisses guide', !await isOpen(touchPage));
  await touchContext.close();
  for (const mode of [{ name: 'unsupported popover', unsupported: true }, { name: 'admin toolbar', admin: true }]) {
    const fallback = await contextFor(mode);
    const { page } = await load(fallback, 'thai-massage');
    await pastNav(page);
    check(`${mode.name}: no added guide`, await page.locator(trigger + ', ' + panel).count() === 0);
    const link = page.locator('.entry-content .ts-jump a').first(), href = await link.getAttribute('href');
    await link.click(); await settle(page);
    check(`${mode.name}: original navigation still works`, new URL(page.url()).hash === href);
    await fallback.close();
  }
  const noJSContext = await contextFor({ noJS: true });
  for (const slug of services) {
    const { page, errors } = await load(noJSContext, slug);
    await page.setViewportSize({ width: 390, height: 844 });
    const links = await inlineContract(page, slug);
    check(`${slug} without JavaScript: no added guide`, await page.locator(trigger + ', ' + panel).count() === 0);
    for (let index = 0; index < maps[slug].length; index++) {
      await page.locator('.entry-content .ts-jump a').nth(index).click();
      const state = await reachedDestination(page, links[index].href, maps[slug][index]);
      check(`${slug} without JavaScript: ${maps[slug][index].label} reaches the existing target`, state.hash === links[index].href && state.reached && state.correctHeading && state.unique && state.inContent, JSON.stringify(state));
    }
    check(`${slug} without JavaScript: no browser errors`, errors.length === 0, errors.join('; '));
    await page.close();
  }
  await noJSContext.close();
  if (fixture) {
    const { page: jumpPage } = await load(context, 'thai-massage', '?tall-header');
    let jumpState = await scrollState(jumpPage);
    check('jump scroll: original nav starts below viewport', jumpState.nav.top >= jumpState.viewport.height, JSON.stringify(jumpState));
    check('jump scroll: guide initially hidden', !await jumpPage.locator(trigger).isVisible());
    await pastNav(jumpPage);
    jumpState = await scrollState(jumpPage);
    check('jump scroll: one jump moved entire nav above viewport with footer still below', jumpState.nav.bottom < 0 && jumpState.footer.top >= jumpState.viewport.height, JSON.stringify(jumpState));
    check('jump scroll: guide appears without intermediate nav intersection', await jumpPage.locator(trigger).isVisible(), JSON.stringify(jumpState));
    await jumpPage.evaluate(() => { const nav = document.querySelector('.entry-content .ts-jump'); scrollTo(0, scrollY + nav.getBoundingClientRect().top - 100); }); await settle(jumpPage);
    jumpState = await scrollState(jumpPage);
    check('reverse jump setup: original nav intersects viewport', jumpState.nav.top < jumpState.viewport.height && jumpState.nav.bottom > 0, JSON.stringify(jumpState));
    await pastNav(jumpPage);
    check('reverse jump setup: guide visible before reverse jump', await jumpPage.locator(trigger).isVisible(), JSON.stringify(await scrollState(jumpPage)));
    await jumpPage.evaluate(() => scrollTo(0, 0)); await settle(jumpPage);
    jumpState = await scrollState(jumpPage);
    check('reverse jump: original nav moves wholly below viewport', jumpState.nav.top >= jumpState.viewport.height, JSON.stringify(jumpState));
    check('reverse jump: guide hides without intermediate nav intersection', !await jumpPage.locator(trigger).isVisible(), JSON.stringify(jumpState));
    await jumpPage.evaluate(() => scrollTo(0, document.documentElement.scrollHeight)); await settle(jumpPage);
    jumpState = await scrollState(jumpPage);
    check('footer jump: visible footer suppresses closed guide', jumpState.footer.top < jumpState.viewport.height && jumpState.footer.bottom > 0 && !await jumpPage.locator(trigger).isVisible(), JSON.stringify(jumpState));
    await pastNav(jumpPage);
    check('footer jump: leaving footer for main content restores guide', await jumpPage.locator(trigger).isVisible(), JSON.stringify(await scrollState(jumpPage)));
    await jumpPage.evaluate(() => { scrollTo(0, 0); document.documentElement.style.fontSize = '200%'; });
    await jumpPage.setViewportSize({ width: 320, height: 568 }); await settle(jumpPage);
    jumpState = await scrollState(jumpPage);
    check('resized jump: original nav below viewport after 200% text reflow', jumpState.nav.top >= jumpState.viewport.height && !await jumpPage.locator(trigger).isVisible(), JSON.stringify(jumpState));
    await pastNav(jumpPage);
    jumpState = await scrollState(jumpPage);
    check('resized jump: guide appears after jump at 320×568 and 200% text', jumpState.nav.bottom < 0 && await jumpPage.locator(trigger).isVisible(), JSON.stringify(jumpState));
    await jumpPage.close();
    const { page } = await load(context, 'thai-massage', '?short');
    const shortSize = await page.evaluate(() => ({ height: document.documentElement.scrollHeight, viewport: innerHeight }));
    check('short page: no scroll range', shortSize.height <= shortSize.viewport, JSON.stringify(shortSize));
    check('short page: guide remains hidden', !await page.locator(trigger).isVisible());
    await page.close();
  }
} catch (error) {
  if (!invalid) { failures++; console.error(error.stack); }
  else console.log(error.message);
} finally { await context.close(); await browser.close(); }
console.log(`INFO Page navigation (${engine}): ${assertions} browser assertions, ${failures} failures${invalid ? ', INVALID' : ''}`);
process.exitCode = invalid ? 2 : failures ? 1 : 0;

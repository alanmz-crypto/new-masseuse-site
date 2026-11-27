// Run on the public v2 site or an isolated replay bound to loopback.
const assert = require('node:assert/strict');
const {createRequire} = require('node:module');
const loader = process.env.TS_PLAYWRIGHT_ROOT ? createRequire(process.env.TS_PLAYWRIGHT_ROOT) : require;
const {chromium, firefox} = loader('playwright');
const B = process.argv[2] || 'https://thaisolitude.com';
assert(B === 'https://thaisolitude.com' || /^http:\/\/127\.0\.0\.1:\d+$/.test(B));
const log = console.log;

async function kb(browser, name, slug, key) {
  const ctx = await browser.newContext({viewport:{width:390,height:844}, reducedMotion:'reduce', hasTouch:true});
  const page = await ctx.newPage();
  await page.goto(B + (slug === 'home' ? '/' : '/'+slug+'/'));
  const toggle = page.locator('.ts-pk-ix');
  await toggle.waitFor({state:'attached'});
  await page.evaluate(() => scrollTo(0, 800));
  await toggle.focus();
  await page.keyboard.press('Enter');
  await page.keyboard.press('Tab');
  const topic = page.locator('.ts-pk-topic').first();
  assert(await topic.evaluate(b => b === document.activeElement), 'first topic focus');
  await page.keyboard.press(key);
  const card = page.locator('.ts-pk-card');
  assert(await card.evaluate(el => el === document.activeElement && el.classList.contains('is-pinned')), 'card focus+pinned');
  await page.keyboard.press('Escape');
  assert(await page.locator('.ts-pk-rail').evaluate(el => el.contains(document.activeElement)), 'escape returns to rail');
  assert.equal(await page.locator('.ts-pk-topic:enabled').count(), 0, 'topics disabled after escape');
  await toggle.tap();
  await topic.tap();
  assert(await card.evaluate(el => el.classList.contains('is-pinned')), 'touch pin');
  log('PASS', name, slug, key, '+ Escape + touch');
  await ctx.close();
}
async function arrival(browser, name, cancel) {
  const ctx = await browser.newContext({viewport:{width:390,height:844}, reducedMotion:'reduce'});
  const page = await ctx.newPage();
  let release;
  const gate = new Promise(r => release = r);
  await page.route('**/__slow', async r => { await gate; r.abort().catch(()=>{}); });
  await page.route(B + '/thai-massage/**', async r => {
    if (r.request().resourceType() !== 'document') return r.continue();
    const resp = await r.fetch({maxRedirects: 0});
    assert.equal(resp.status(), 200);
    let body = await resp.text();
    body = body.replace('</body>', '<img src="/__slow"></body>');
    r.fulfill({response: resp, body});
  });
  await page.goto(B + '/thai-massage/#ts-guide-research', {waitUntil:'domcontentloaded'});
  await page.locator('.ts-pk-rail').waitFor({state:'attached'});
  if (cancel) { await page.mouse.wheel(0, 3000); await page.waitForTimeout(500); }
  const y = await page.evaluate(() => scrollY);
  if (cancel) assert(y > 100, 'visitor scroll moved the page');
  release();
  await page.waitForLoadState('load');
  await page.waitForTimeout(1500);
  const r = await page.evaluate(() => {
    const strip = document.querySelector('.ts-pk-top');
    return {y: scrollY, top: document.getElementById('ts-guide-research').getBoundingClientRect().top,
            want: parseFloat(getComputedStyle(strip).top) + strip.offsetHeight + 12};
  });
  log(name, cancel ? 'scrolled-before-load' : 'untouched', 'y_before_load=', y, r);
  if (cancel) assert(Math.abs(r.y - y) < 7, 'scroll kept');
  else assert(Math.abs(r.top - r.want) < 7, 'landed on target');
  log('PASS', name, cancel ? 'fragment scrolled before load' : 'fragment untouched');
  await ctx.close();
}
async function cache(browser) {
  const ctx = await browser.newContext();
  try {
    const page = await ctx.newPage();
    for (const route of ['/', '/services/', '/thai-massage/']) {
      assert.equal((await page.goto(B + route)).status(), 200);
      const sizes = await page.evaluate(() => performance.getEntriesByType('resource')
        .filter(e => e.name.includes('/wp-content/ts-peek/v2/'))
        .map(e => [new URL(e.name).pathname.split('/').pop(), e.transferSize, e.encodedBodySize]).sort());
      assert.deepEqual(sizes.map(e => e[0]), ['navigation.css', 'navigation.js']);
      assert(sizes.every(e => e[2] > 0 && (route === '/' ? e[1] > 0 : e[1] === 0)));
      log('PASS asset transfers', route, sizes);
    }
  } finally { await ctx.close(); }
}
async function main() {
  for (const engine of [chromium, firefox]) {
    const browser = await engine.launch(engine === chromium ? {executablePath: process.env.CHROMIUM_PATH} : {});
    try {
      await kb(browser, engine.name(), 'home', 'Enter');
      await kb(browser, engine.name(), 'thai-massage', 'Space');
      await arrival(browser, engine.name(), false);
      await arrival(browser, engine.name(), true);
      if (engine === chromium) await cache(browser);
    } finally { await browser.close(); }
  }
}
main().catch(e => { console.error(e); process.exitCode = 1; });

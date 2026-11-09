// Read-only baseline evidence for the existing theme menu and practice rail.
// No candidate runtime injection or site writes; navigation stays on each page.
import { createRequire } from 'node:module';
import { mkdirSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { createHash } from 'node:crypto';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const playwright = require('playwright');
const base = process.argv[2];
if (base !== 'https://staging39.thaisolitude.com' || process.argv.length !== 3) throw new Error('exact staging39 origin only; no flags or injection');
const out = join(process.env.RUNNER_TEMP || tmpdir(), 'ops-out', 'title-behavior');
mkdirSync(out, { recursive: true });
const files = new Set(), pending = [], info = (name, value) => console.log(`INFO title behavior ${name}: ${JSON.stringify(value)}`);
let invalid = false;
for (const engine of ['chromium', 'firefox']) {
  const browser = await playwright[engine].launch(engine === 'chromium' && process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
  try {
    const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce' });
    await ctx.addInitScript(() => { localStorage.setItem('ts-pk-tip', 'seen'); });
    const open = async path => {
      const p = await ctx.newPage();
      p.on('pageerror', error => info(`${engine}${path} JavaScript error`, error.message));
      p.on('response', response => {
        const url = new URL(response.url());
        if (response.status() !== 200 || url.origin !== base || !/\/themes\/astra\/.*\.js$/.test(url.pathname) || files.has(url.pathname)) return;
        files.add(url.pathname);
        pending.push((async () => {
          const body = await response.body(), sha = createHash('sha256').update(body).digest('hex');
          const file = join(out, `astra-${sha.slice(0, 16)}.js`); writeFileSync(file, body);
          info('publicly referenced Astra script retained', { url: response.url(), bytes: body.length, sha256: sha, file });
        })().catch(error => { invalid = true; info('script retention INVALID', error.message); }));
      });
      const response = await p.goto(base + path, { waitUntil: 'load' });
      if (response?.status() !== 200 || new URL(p.url()).origin !== base || /Robot Challenge/i.test(await p.title())) throw new Error(`${path} expected same-origin200; got${response?.status()}`);
      await p.evaluate(() => document.fonts.ready); return p;
    };
    const snapshot = p => p.evaluate(() => {
      const rect = node => node && ({ ...node.getBoundingClientRect().toJSON(), display: getComputedStyle(node).display, position: getComputedStyle(node).position });
      return { y: scrollY, x: scrollX, hash: location.hash, url: location.href, history: history.length, body: document.body.className, root: document.documentElement.className,
        expanded: [...document.querySelectorAll('#masthead .menu-toggle')].map(node => node.getAttribute('aria-expanded')), header: rect(document.querySelector('#masthead')),
        panels: [...document.querySelectorAll('#masthead .ast-mobile-header-content')].map(rect), content: rect(document.querySelector('.entry-content')),
        focus: document.activeElement?.outerHTML.slice(0, 500), card: document.querySelector('.tsr-card')?.outerHTML.slice(0, 1200), trace: window.__titleBehaviorTrace || [] };
    });
    for (const path of ['/about/', '/thai-massage/']) {
      const p = await open(path), toggle = p.locator('#masthead .menu-toggle:visible').first();
      await p.evaluate(() => scrollTo({ top: 600, behavior: 'instant' })); await p.waitForTimeout(100);
      info(`${engine}${path} menu before focus`, await snapshot(p));
      await toggle.focus(); await p.waitForTimeout(100); info(`${engine}${path} menu after focus`, await snapshot(p));
      await toggle.click(); await p.waitForTimeout(500); info(`${engine}${path} menu open`, await snapshot(p));
      console.log(`INLINE title-behavior-${engine}-${path.slice(1, -1)}-menu.jpg base64:${(await p.screenshot({ type: 'jpeg', quality: 76 })).toString('base64')}`);
      await toggle.click(); await p.waitForTimeout(200); info(`${engine}${path} menu closed`, await snapshot(p)); await p.close();
    }
    const p = await open('/rail-nav-ideas/?idea=1');
    await p.evaluate(() => {
      window.__titleBehaviorTrace = [];
      for (const type of ['keydown', 'keyup', 'click', 'pointerdown', 'pointerup', 'focusin']) document.addEventListener(type, event => {
        const entry = { type, key: event.key, detail: event.detail, trusted: event.isTrusted, target: event.target.tagName + '.' + event.target.className, prevented: event.defaultPrevented };
        window.__titleBehaviorTrace.push(entry); if (window.__titleBehaviorTrace.length > 40) window.__titleBehaviorTrace.shift();
        queueMicrotask(() => { entry.afterPrevented = event.defaultPrevented; entry.card = document.querySelector('.tsr-card').className; entry.focus = document.activeElement?.tagName + '.' + document.activeElement?.className; entry.y = scrollY; entry.hash = location.hash; });
      }, true);
    });
    for (const width of [390, 768, 1280]) {
      await p.setViewportSize({ width, height: 844 }); await p.evaluate(() => scrollTo({ top: 650, behavior: 'instant' })); await p.waitForTimeout(100);
      const link = p.locator('.tsr-rail a[href="#tsr-questions"]');
      await p.evaluate(() => { window.__titleBehaviorTrace = []; });
      info(`${engine}/rail/${width} before Enter`, await snapshot(p));
      await link.focus(); await p.keyboard.down('Enter'); await p.waitForTimeout(50); info(`${engine}/rail/${width} Enter down`, await snapshot(p));
      await p.keyboard.up('Enter'); await p.waitForTimeout(150); info(`${engine}/rail/${width} Enter up`, await snapshot(p));
      await p.keyboard.press('Escape'); await p.evaluate(() => { window.__titleBehaviorTrace = []; });
      await link.click(); await p.waitForTimeout(150); info(`${engine}/rail/${width} pointer preview`, await snapshot(p));
      const go = p.locator('.tsr-card.is-pinned .go');
      if (await go.isVisible()) { await go.click(); await p.waitForTimeout(700); info(`${engine}/rail/${width} pointer Go`, await snapshot(p)); }
      else info(`${engine}/rail/${width} pointer Go unavailable`, await snapshot(p));
      await p.keyboard.press('Escape');
    }
    await Promise.all(pending); await ctx.close();
  } catch (error) { invalid = true; console.log(`INVALID title behavior ${engine}: ${error.message}`); }
  finally { await browser.close(); }
}
await Promise.all(pending);
info('complete', { valid: !invalid, retainedPublicScripts: files.size, directory: out, scope: 'read-only baseline observation; not a candidate pass/fail gate' });
process.exit(invalid ? 2 : 0);

// Read-only actual public DOM inventory; one sequential load per WP permalink.
// Both viewport layouts use that same load. No injected CSS, demo selection,
// link clicks, accordion changes or persistent site writes. Stop on first challenge.
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const [manifest, out] = process.argv.slice(2);
if (!manifest || !out || process.argv.length !== 4) throw new Error('usage: inspect-title-pages.mjs routes.json outdir');
const data = JSON.parse(fs.readFileSync(manifest, 'utf8'));
const origin = 'https://staging39.thaisolitude.com';
if (data.host !== 'staging39.thaisolitude.com' || !Array.isArray(data.routes) || !data.routes.length) throw new Error('Invalid staging39 inventory');
for (const row of data.routes) {
  const u = new URL(row.permalink);
  if (u.origin !== origin || u.username || u.password || !Number.isInteger(row.id) || row.status !== 'publish') throw new Error('Foreign or invalid published route');
}
fs.mkdirSync(out, { recursive: true });
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
// Redirects are recorded, but a mistaken external canonical must not visit live.
await context.route('**/*', async route => {
  const request = route.request();
  if (request.isNavigationRequest() && request.frame().parentFrame() === null && new URL(request.url()).origin !== origin) return route.abort('blockedbyclient');
  return route.continue();
});
const results = []; let invalid = false, failures = 0;
try {
  for (const row of data.routes) {
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(String(error)));
    const record = { id: row.id, slug: row.slug, requested: row.permalink, views: [], errors };
    results.push(record);
    try {
      const response = await page.goto(row.permalink, { waitUntil: 'domcontentloaded', timeout: 60000 });
      record.status = response?.status() ?? null;
      record.finalUrl = page.url();
      const title = await page.title();
      if (record.status === 202 || record.status === 403 || /Robot Challenge/i.test(title) || response?.headers()['sg-captcha']) {
        record.result = 'INVALID'; invalid = true;
        console.log(`INVALID browser stopped at first hosting/access denial: ${row.permalink} HTTP${record.status}`);
        break;
      }
      if (record.status !== 200) { record.result = 'HTTP_FAILURE'; failures++; console.log(`FAIL HTTP${record.status} ${row.permalink}`); continue; }
      await page.waitForLoadState('load', { timeout: 30000 });
      await page.waitForTimeout(600);
      await page.evaluate(() => Promise.race([document.fonts.ready, new Promise(resolve => setTimeout(resolve, 5000))]));
      for (const width of [1280, 390]) {
        await page.setViewportSize({ width, height: width === 1280 ? 900 : 844 });
        await page.evaluate(() => scrollTo(0, 0));
        await page.waitForTimeout(350);
        const imagesDecoded = await page.evaluate(() => Promise.race([
          Promise.all([...document.images].filter(img => { const r = img.getBoundingClientRect(); return r.width > 0 && r.height > 0 && r.bottom > 0 && r.top < innerHeight; }).map(img => img.decode().catch(() => null))).then(() => true),
          new Promise(resolve => setTimeout(() => resolve(false), 10000)),
        ]));
        if (!imagesDecoded) throw new Error(`Visible image decode timed out at ${width}px; no early capture claimed`);
        const view = await page.evaluate(() => {
          const text = el => (el?.textContent || '').replace(/\s+/g, ' ').trim();
          const rect = el => { if (!el) return null; const r = el.getBoundingClientRect(); return { x: r.x, y: r.y, width: r.width, height: r.height }; };
          const describe = el => ({ tag: el.tagName.toLowerCase(), id: el.id, class: el.className, text: text(el), rect: rect(el), visible: Boolean(el.getClientRects().length) && getComputedStyle(el).visibility !== 'hidden', inNav: Boolean(el.closest('nav')), closedDetails: Boolean(el.closest('details:not([open])')) });
          const content = document.querySelector('.entry-content');
          return {
            viewport: { width: innerWidth, height: innerHeight }, title: document.title, url: location.href, bodyClass: document.body.className,
            canonical: document.querySelector('link[rel="canonical"]')?.href || null,
            robots: [...document.querySelectorAll('meta[name="robots"]')].map(el => el.content),
            pageTitles: [...document.querySelectorAll('h1,.entry-title')].map(describe),
            contentCount: document.querySelectorAll('.entry-content').length, contentRect: rect(content),
            contentChildren: content ? [...content.children].map(el => ({ tag: el.tagName.toLowerCase(), id: el.id, class: el.className, rect: rect(el) })) : [],
            headings: content ? [...content.querySelectorAll('h1,h2,h3,h4,h5,h6,summary,.uagb-question')].map(describe) : [],
            jumps: [...document.querySelectorAll('.ts-jump a')].map(a => { let target = null; try { target = document.getElementById(decodeURIComponent(new URL(a.href).hash.slice(1))); } catch {} return { label: text(a), href: a.getAttribute('href'), target: target ? describe(target) : null }; }),
            contentNavigation: content ? [...content.querySelectorAll('nav,[role="navigation"],.ts-wwm__hero-links')].map(el => ({ ...describe(el), links: [...el.querySelectorAll('a')].map(a => ({ text: text(a), href: a.getAttribute('href') })) })) : [],
            sticky: [...document.querySelectorAll('#masthead,.entry-content *')].filter(el => ['sticky', 'fixed'].includes(getComputedStyle(el).position)).map(el => ({ tag: el.tagName.toLowerCase(), id: el.id, class: el.className, position: getComputedStyle(el).position, top: getComputedStyle(el).top, rect: rect(el) })),
            peek: { strips: document.querySelectorAll('.ts-pk-top').length, rails: document.querySelectorAll('.ts-pk-nav').length, data: document.querySelectorAll('#ts-peek-nav-data').length, config: document.querySelectorAll('#ts-peek-nav-config').length },
            details: [...document.querySelectorAll('details')].map(el => ({ id: el.id, open: el.open, summary: text(el.querySelector('summary')) })),
            document: { height: document.documentElement.scrollHeight, width: document.documentElement.scrollWidth, clientWidth: document.documentElement.clientWidth },
          };
        });
        const file = `${row.id}-${width}-top.jpg`;
        await page.screenshot({ path: path.join(out, file), type: 'jpeg', quality: 65 });
        view.screenshot = file; record.views.push(view);
        console.log(`CAPTURE ${row.id} ${width}px headings=${view.headings.length} jumps=${view.jumps.length} strips=${view.peek.strips} ${row.permalink}`);
      }
      record.result = 'CAPTURED';
    } catch (error) {
      record.result = 'ERROR'; record.error = String(error); failures++;
      console.log(`FAIL browser ${row.permalink}: ${String(error).slice(0, 250)}`);
    } finally { await page.close(); }
  }
} finally {
  await context.close(); await browser.close();
  fs.writeFileSync(path.join(out, 'rendered.json'), JSON.stringify({ schema: 1, capturedUtc: new Date().toISOString(), invalid, failures, expectedRoutes: data.routes.length, results }, null, 2) + '\n');
}
console.log(`Title inventory: ${results.filter(r => r.result === 'CAPTURED').length}/${data.routes.length} routes rendered, ${failures} failures, browser ${invalid ? 'INVALID' : 'complete'}; origin capture is separate.`);
process.exitCode = invalid ? 2 : failures ? 1 : 0;

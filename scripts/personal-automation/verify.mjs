// Render both pages and check discovery controls at desktop and phone widths.
// Uses this site's existing Playwright installation; no package download.
import { createRequire } from 'node:module';
import { mkdirSync } from 'node:fs';
import assert from 'node:assert/strict';
const require = createRequire(import.meta.url);
const { chromium } = require(process.env.RPA_PLAYWRIGHT || '/home/lauer/Projects/SubProjects/Mirror/node_modules/playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const output = process.argv[3] || '/tmp/personal-automation-preview';
const slugs = ['personal-automation', 'personal-automation/privacy'];
if (process.env.RPA_TERMS === '1') slugs.push('personal-automation/terms');
mkdirSync(output, { recursive: true });
const browser = await chromium.launch({ headless: true, executablePath: process.env.RPA_CHROMIUM || '/usr/bin/chromium' });
try {
  const context = await browser.newContext();
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  for (const width of [1280, 390]) {
    await page.setViewportSize({ width, height: 900 });
    for (const slug of slugs) {
      const response = await page.goto(`${base}/${slug}/`, { waitUntil: 'networkidle' });
      assert.equal(response.status(), 200, `${slug}: page unavailable or challenged`);
      assert.match(response.headers()['x-robots-tag'] || '', /noindex/i);
      assert.match(await page.locator('meta[name="robots"]').getAttribute('content'), /noindex/i);
      assert.equal(await page.locator('h1').count(), 1);
      assert.match(await page.locator('h1').innerText(), /Ryan Personal Automation/);
      assert.equal(await page.locator('header a[href*="/personal-automation"], footer a[href*="/personal-automation"]').count(), 0, 'Not linked in menus or footer');
      assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No horizontal overflow');
      assert.equal(await page.locator('.entry-content a[href="mailto:ryan.steitz@gmail.com"]').count(), 1);
      await page.screenshot({ path: `${output}/${slug.replaceAll('/', '-')}-${width}.png`, fullPage: true });
      if (slug === 'personal-automation') {
        if (process.env.RPA_TERMS === '1') {
          await page.locator('.entry-content a[href="/personal-automation/terms/"]').click();
          assert.match(page.url(), /\/personal-automation\/terms\/$/);
          await page.goBack({waitUntil: 'networkidle'});
        }
        await page.locator('.entry-content a[href="/personal-automation/privacy/"]').click();
        assert.match(page.url(), /\/personal-automation\/privacy\/$/);
      }
      console.log(`PASS ${slug} at ${width}px: 200, noindex, unlisted, readable`);
    }
  }
  await page.goto(`${base}/?s=Ryan+Personal+Automation`, { waitUntil: 'networkidle' });
  assert.equal(await page.locator('main a[href*="/personal-automation/"]').count(), 0, 'Absent from site search results');
  await page.goto(base, { waitUntil: 'networkidle' });
  assert.equal(await page.locator('a[href*="/personal-automation/"]').count(), 0, 'Not linked from main homepage');
  const queue = [`${base}/wp-sitemap.xml`];
  const visited = new Set();
  let locations = 0;
  while (queue.length) {
    const url = queue.shift();
    if (visited.has(url)) continue;
    visited.add(url);
    assert(visited.size <= 30, 'Sitemap traversal bounded');
    const response = await context.request.get(url);
    assert.equal(response.status(), 200, `Sitemap ${url}`);
    const xml = await response.text();
    assert.match(xml, /<(?:sitemapindex|urlset)\b/);
    const links = [...xml.matchAll(/<loc>(.*?)<\/loc>/g)].map(m => m[1].replaceAll('&amp;', '&'));
    locations += links.length;
    for (const link of links) {
      assert(!link.includes('/personal-automation'), 'Absent from XML sitemaps');
      if (xml.includes('<sitemapindex')) {
        const child = new URL(link);
        assert.equal(child.host, new URL(base).host, 'Same-site sitemap only');
        queue.push(link);
      }
    }
  }
  assert(locations > 0, 'Nonempty sitemap checked');
  assert.deepEqual(errors, [], 'No browser JavaScript errors');
  console.log(`PASS search, homepage, ${visited.size} sitemap files (${locations} entries), browser errors`);
} finally {
  await browser.close();
}
// ref: ./unlisted.php, ./install.php — public behavior of the installed pages and discovery filters

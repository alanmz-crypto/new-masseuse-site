// Median hero/LCP time over N cold-cache loads of ONE url on a throttled phone (390x844 @2x, Slow-4G 1.6 Mbps / 150 ms, 4x slower CPU): the number to compare before and after a speed change.
//   node perf-lcp-runs.mjs <url> [runs=3]        read-only; each run is one page load, so keep runs small (SiteGround blocks bursts)
// Companion: perf-images.mjs (per-image detail). A 202 answer means SiteGround's bot challenge, not the site.
import { createRequire } from 'module';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [url, runs = '3'] = process.argv.slice(2);
const b = await chromium.launch(); const out = [];
for (let i = 0; i < +runs; i++) {
  const ctx = await b.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
  const page = await ctx.newPage(); const cdp = await ctx.newCDPSession(page);
  await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: 150, downloadThroughput: 1.6 * 1024 * 1024 / 8, uploadThroughput: 750 * 1024 / 8 });
  await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
  await page.addInitScript(() => { window.__lcp = 0; new PerformanceObserver((l) => { const e = l.getEntries().pop(); window.__lcp = Math.round(e.startTime); }).observe({ type: 'largest-contentful-paint', buffered: true }); });
  const r = await page.goto(url, { waitUntil: 'load', timeout: 120000 });
  if (r.status() !== 200) { console.log('status', r.status(), '(202 = SiteGround challenge)'); process.exit(1); }
  await page.waitForTimeout(2000);
  const d = await page.evaluate(() => ({ lcp: window.__lcp, imgs: performance.getEntriesByType('resource').filter((e) => e.initiatorType === 'img' || /\.webp/.test(e.name)).length, bytes: Math.round(performance.getEntriesByType('resource').reduce((s, e) => s + (e.transferSize || 0), 0) / 1024) }));
  out.push(d); await ctx.close();
}
await b.close();
const l = out.map((x) => x.lcp).sort((a, c) => a - c);
console.log(`${url}  phone Slow-4G  LCP per run: ${out.map((x) => x.lcp).join(', ')} ms | MEDIAN ${l[Math.floor(l.length / 2)]} ms | image requests in the first ~3 s: ${out.map((x) => x.imgs).join(',')} | KB over the wire: ${out.map((x) => x.bytes).join(',')}`);

// ref: ./perf-images.mjs — the detailed image/font/LCP profile; ../performance/install-lazy-images.php — the change this was used to measure

// Read-only image/loading profile of ONE page, in a real browser (two cold-cache loads: a fast desktop and a throttled phone).
//   node perf-images.mjs <base-url> [path]            default path "/"
// Reports, per profile: requests and bytes by type, first-view versus below-the-fold images, every image's file size against the pixels it is actually shown at
// (waste), format, loading/fetchpriority attributes, cache headers, and the browser's LARGEST CONTENTFUL PAINT element and time.
// It only loads the public page like a visitor (about 2 page views); it changes nothing. Keep traffic low: SiteGround blocks addresses that send bursts.
// Playwright is taken from the Mirror project, as the other tools here do.
import { createRequire } from 'module';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [base, path = '/'] = process.argv.slice(2);
if (!base) { console.error('usage: node perf-images.mjs <base-url> [path]'); process.exit(1); }
const profiles = [
  { name: 'DESKTOP, fast connection', ctx: { viewport: { width: 1280, height: 800 }, deviceScaleFactor: 1 }, net: null, cpu: 1 },
  { name: 'PHONE 390x844 @2x, Slow-4G (1.6 Mbps, 150 ms) + 4x slower CPU', ctx: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true }, net: { latency: 150, down: 1.6 * 1024 * 1024 / 8, up: 750 * 1024 / 8 }, cpu: 4 },
];
const kb = (n) => (n / 1024).toFixed(0).padStart(5) + ' KB';
const short = (u) => { try { const x = new URL(u); return (x.pathname.split('/').slice(-2).join('/') || x.pathname).slice(-46); } catch { return u.slice(-46); } };
const b = await chromium.launch();
let bad = 0;
for (const p of profiles) {
  const ctx = await b.newContext(p.ctx);
  const page = await ctx.newPage();
  const cdp = await ctx.newCDPSession(page);
  if (p.net) await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: p.net.latency, downloadThroughput: p.net.down, uploadThroughput: p.net.up });
  if (p.cpu > 1) await cdp.send('Emulation.setCPUThrottlingRate', { rate: p.cpu });
  const res = [];
  page.on('response', async (r) => {
    try {
      const rq = r.request(); const h = r.headers();
      let size = 0; try { size = (await r.body()).length; } catch { /* redirects / aborted */ }
      res.push({ url: r.url(), status: r.status(), type: rq.resourceType(), ct: h['content-type'] || '', size, enc: h['content-encoding'] || '', cache: h['cache-control'] || (h['expires'] ? 'expires' : ''), t: Date.now() });
    } catch { /* ignore */ }
  });
  await page.addInitScript(() => {
    window.__lcp = null; window.__fcp = null;
    new PerformanceObserver((l) => { const e = l.getEntries().pop(); window.__lcp = { t: Math.round(e.startTime), tag: e.element ? e.element.tagName : '?', url: e.url || '', size: e.size, cls: e.element ? (e.element.className || '').toString().slice(0, 50) : '' }; }).observe({ type: 'largest-contentful-paint', buffered: true });
    new PerformanceObserver((l) => { for (const e of l.getEntries()) if (e.name === 'first-contentful-paint') window.__fcp = Math.round(e.startTime); }).observe({ type: 'paint', buffered: true });
  });
  const t0 = Date.now();
  const r0 = await page.goto(base + path, { waitUntil: 'load', timeout: 120000 });
  if (r0.status() !== 200) { console.log(`!! ${p.name}: page answered ${r0.status()} (a 202 would be SiteGround's challenge); nothing measured`); bad++; await ctx.close(); continue; }
  const loadMs = Date.now() - t0;
  await page.waitForTimeout(1500);
  const firstView = res.filter((x) => x.type === 'image').map((x) => x.url);
  const info = await page.evaluate(() => {
    const vh = innerHeight; const out = [];
    for (const im of document.images) {
      const rc = im.getBoundingClientRect();
      out.push({ src: im.currentSrc || im.src, nw: im.naturalWidth, nh: im.naturalHeight, w: Math.round(rc.width), h: Math.round(rc.height), top: Math.round(rc.top), loading: im.getAttribute('loading') || '', fp: im.getAttribute('fetchpriority') || '', srcset: !!im.getAttribute('srcset'), wh: !!(im.getAttribute('width') && im.getAttribute('height')) });
    }
    const bgs = [];
    for (const el of document.querySelectorAll('*')) {
      const bi = getComputedStyle(el).backgroundImage; const m = bi && bi.match(/url\("?([^")]+)"?\)/);
      if (m && !m[1].startsWith('data:')) { const rc = el.getBoundingClientRect(); bgs.push({ src: m[1], w: Math.round(rc.width), h: Math.round(rc.height), top: Math.round(rc.top) }); }
    }
    return { imgs: out, bgs, vh, lcp: window.__lcp, fcp: window.__fcp, preloads: [...document.querySelectorAll('link[rel=preload]')].map((l) => (l.getAttribute('as') || '') + ':' + (l.getAttribute('href') || '').slice(-50)) };
  });
  // scroll through the page so lazy images load, then summarise everything
  await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += innerHeight * 0.7) { scrollTo(0, y); await new Promise((r) => setTimeout(r, 250)); } scrollTo(0, 0); });
  await page.waitForTimeout(2500);
  const dpr = p.ctx.deviceScaleFactor;
  const byType = {}; for (const x of res) { const k = x.type; byType[k] = byType[k] || { n: 0, bytes: 0 }; byType[k].n++; byType[k].bytes += x.size; }
  console.log(`\n================ ${p.name} ================`);
  console.log(`load event ${loadMs} ms | first contentful paint ${info.fcp ?? '?'} ms | LARGEST CONTENTFUL PAINT ${info.lcp ? info.lcp.t + ' ms: <' + info.lcp.tag + '> ' + (info.lcp.url ? short(info.lcp.url) : '(no url: text or background)') : '(none)'}`);
  console.log('requests/bytes: ' + Object.entries(byType).sort((a, c) => c[1].bytes - a[1].bytes).map(([k, v]) => `${k} ${v.n} (${(v.bytes / 1024).toFixed(0)} KB)`).join(' | ') + ` | TOTAL ${res.length} requests, ${(res.reduce((s, x) => s + x.size, 0) / 1024 / 1024).toFixed(2)} MB`);
  console.log('preload hints in <head>: ' + (info.preloads.length ? info.preloads.join(', ') : 'none'));
  const imgRes = res.filter((x) => x.type === 'image' || /image\//.test(x.ct)).sort((a, c) => c.size - a.size);
  console.log(`\nIMAGES (${imgRes.length} files, ${(imgRes.reduce((s, x) => s + x.size, 0) / 1024).toFixed(0)} KB; "first view" = requested before scrolling)`);
  console.log('  file size  format      natural    shown@dpr   waste  first-view  load/prio     cache         file');
  for (const x of imgRes.slice(0, 14)) {
    const m = info.imgs.find((i) => i.src === x.url) || info.bgs.find((g) => g.src === x.url);
    const fmt = (x.ct.split('/')[1] || '?').split(';')[0].padEnd(10);
    const nat = m && m.nw ? `${m.nw}x${m.nh}` : '?';
    const shown = m ? `${Math.round(m.w * dpr)}x${Math.round(m.h * dpr)}` : '?';
    const waste = m && m.nw && m.w ? Math.max(1, (m.nw * m.nh) / Math.max(1, m.w * dpr * m.h * dpr)).toFixed(1) + 'x' : '?';
    const attr = m ? [m.loading || '-', m.fp || '-'].join('/') : (info.bgs.find((g) => g.src === x.url) ? 'css-bg' : '?');
    console.log(`  ${kb(x.size)}  ${fmt} ${nat.padEnd(10)} ${shown.padEnd(11)} ${waste.padStart(5)}  ${(firstView.includes(x.url) ? 'YES' : 'no').padEnd(10)}  ${attr.padEnd(12)}  ${(x.cache || 'NONE').slice(0, 13).padEnd(13)} ${short(x.url)}`);
  }
  const first = imgRes.filter((x) => firstView.includes(x.url));
  console.log(`\nfirst-view images: ${first.length} files, ${(first.reduce((s, x) => s + x.size, 0) / 1024).toFixed(0)} KB | images with width+height attributes: ${info.imgs.filter((i) => i.wh).length}/${info.imgs.length} | with srcset: ${info.imgs.filter((i) => i.srcset).length}/${info.imgs.length} | lazy: ${info.imgs.filter((i) => i.loading === 'lazy').length} | eager images ABOVE the fold: ${info.imgs.filter((i) => i.top < info.vh && i.loading !== 'lazy').length}`);
  const noGz = res.filter((x) => /text|javascript|json|xml|svg/.test(x.ct) && !x.enc && x.size > 4096).length;
  console.log(`text files over 4 KB served WITHOUT compression: ${noGz}`);
  await ctx.close();
}
await b.close();
process.exit(bad ? 1 : 0);

// ref: ./csp-test.mjs — same Playwright setup; ../post-change-test.sh — the pixel comparison that should be re-run after any image change

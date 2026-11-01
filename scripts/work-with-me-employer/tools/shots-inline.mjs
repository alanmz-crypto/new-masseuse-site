// Full-page screenshots of the public pages printed INLINE as base64 JPEG lines, for a session that cannot download the run's artifact (a cloud session:
// the artifact sits on Azure blob storage, outside its network). Read-only: it only loads public pages like a visitor, 12 pages x 2 widths = 24 page loads.
// Keep traffic low: SiteGround blocks addresses that send bursts (HTTP 202 = INVALID). Desktop shots are taken at half scale (layout review), phone shots at full scale.
//   node shots-inline.mjs <base-url> [pages]        pages: comma-separated paths (default: the 12 sitemap pages)
// Exit 1 if a page did not answer 200; exit 2 = SiteGround's bot challenge answered (INVALID, nothing was captured; run once more later).
import { createRequire } from 'module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!base) { console.error('usage: node shots-inline.mjs <base-url> [pages]'); process.exit(1); }
const pages = process.argv[3] ? process.argv[3].split(',') : ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/chair-massage/', '/corporate-massage-portland/', '/work-with-me/', '/contact/', '/privacy-policy/', '/accessibility/'];
const views = [{ n: 'd', w: 1280, h: 900, dpr: 0.5, q: 32 }, { n: 'm', w: 390, h: 844, dpr: 1, q: 30 }];
const b = await chromium.launch();
let ok = 0, bad = 0, challenged = false;
for (const v of views) {
  const ctx = await b.newContext({ viewport: { width: v.w, height: v.h }, deviceScaleFactor: v.dpr, isMobile: v.n === 'm', hasTouch: v.n === 'm', reducedMotion: 'reduce' });
  for (const p of pages) {
    const page = await ctx.newPage();
    const name = (p === '/' ? 'home' : p.replace(/\//g, '')) + '-' + v.n;
    try {
      const r = await page.goto(base + p, { waitUntil: 'networkidle', timeout: 60000 });
      if (r.status() === 202 && (r.headers()['sg-captcha'] || '').length) challenged = true;
      if (/Robot Challenge/i.test(await page.title().catch(() => ''))) challenged = true;
      if (challenged) { console.log('CHALLENGED', p, v.n); await page.close(); break; }
      if (r.status() !== 200) { console.log('status', r.status(), p, v.n); bad++; }
      await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' });
      // scroll through so lazy images and reveal-on-scroll blocks reach their final state, then return to the top
      await page.evaluate(async () => { const step = innerHeight * 0.8; for (let y = 0; y < document.body.scrollHeight; y += step) { scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } scrollTo(0, 0); });
      await page.waitForTimeout(600);
      const h = await page.evaluate(() => ({ doc: document.documentElement.scrollHeight, w: document.documentElement.scrollWidth, cw: document.documentElement.clientWidth }));
      const jpg = await page.screenshot({ type: 'jpeg', quality: v.q, fullPage: true });
      console.log(`INLINE ${name}.jpg height=${h.doc} scrollWidth=${h.w} clientWidth=${h.cw} base64:${jpg.toString('base64')}`);
      ok++;
    } catch (e) { console.log('FAILED', p, v.n, String(e).slice(0, 120)); bad++; }
    await page.close();
  }
  await ctx.close();
  if (challenged) break;
}
await b.close();
if (challenged) { console.log('INVALID: SiteGround answered its bot challenge; nothing more was captured'); process.exit(2); }
console.log(`${base}: ${ok} screenshots inline, ${bad} problems`);
process.exit(bad ? 1 : 0);

// ref: ./capture-pages.mjs — the same page list and scroll-through, written to files; ../chair-massage/check-chair-massage.mjs — the inline base64 idea

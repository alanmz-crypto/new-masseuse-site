// Read-only D copy/layout check, normally run only in isolated rehearsal.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!/^(https:\/\/staging38\.thaisolitude\.com|http:\/\/localhost:8090)$/.test(base)) throw new Error('staging38 or isolated localhost only');
const expected = JSON.parse(readFileSync(new URL('./checks.json', import.meta.url), 'utf8'));
const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
ctx.setDefaultTimeout(10000);
let total = 0, failures = 0, invalid = false;
const check = (label, ok) => { total++; if (!ok) failures++; console.log(`${ok ? 'PASS' : 'FAIL'} ${label}`); };
try {
  for (const [slug, want] of Object.entries(expected)) {
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    const path = slug === 'home' ? '/' : `/${slug}/`;
    const response = await page.goto(base + path, { waitUntil: 'networkidle', timeout: 60000 });
    if (response?.status() === 202 || /Robot Challenge/i.test(await page.title())) { invalid = true; console.log('INVALID: hosting robot challenge; stop'); await page.close(); break; }
    check(`${slug} HTTP 200`, response?.status() === 200);
    const body = await page.locator('main').evaluate(el => {
      const raw = el.innerHTML.replace(/<!--[\s\S]*?-->/g, '').replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/g, '').replace(/<[^>]*>/g, ' ');
      const area = document.createElement('textarea'); area.innerHTML = raw;
      return area.value.replace(/\s+/g, ' ').trim().replace(/’/g, "'").replace(/[“”]/g, '"');
    });
    for (const item of want.texts) {
      const count = body.split(item.text).length - 1;
      check(`${slug} ${item.label} (count ${count}, expected ${item.count})`, count === item.count);
    }
    const hrefs = await page.locator('main a').evaluateAll(links => links.map(a => a.getAttribute('href')));
    for (const item of want.links) check(`${slug} corrected href ${item.href}`, hrefs.filter(h => h === item.href).length === item.count);
    check(`${slug} footer middot`, (await page.locator('footer').innerText()).replace(/\s+/g, ' ').includes('Butsaba Dusrai, LMT · Oregon License LMT-29905'));
    if (slug === 'home') {
      const ease = page.locator('a.ts-heart-link[aria-label="Ease: Therapeutic massage"]');
      check('Home Ease heart has its existing service target', await ease.count() === 1 && (await ease.getAttribute('href')).endsWith('/services/#ts-therapeutic-massage'));
      check('Home all six heart links remain', await page.locator('a.ts-heart-link').count() === 6);
    }
    if (slug === 'deep-tissue-massage') check('Deep first safety notice retained once', body.split('New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage.').length === 2);
    for (const width of [1280, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      for (const theme of ['light', 'dark']) {
        await page.evaluate(theme => document.documentElement.setAttribute('data-ts-theme', theme), theme);
        check(`${slug} ${width} ${theme} no side-scroll`, await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
      }
      if (width === 390 && ['home', 'about'].includes(slug)) {
        await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light'));
        const selector = slug === 'home' ? '.uagb-block-36c16d4d' : '.uagb-block-c0ed63b6';
        const jpg = await page.locator(selector).screenshot({ type: 'jpeg', quality: 60 });
        console.log(`INLINE bundle-d-${slug}-390.jpg base64:${jpg.toString('base64')}`);
      }
    }
    check(`${slug} no browser script errors`, errors.length === 0);
    await page.close();
    console.log(`INFO Bundle D page done ${slug}`);
  }
} finally { await ctx.close(); await browser.close(); }
console.log(`Bundle D: ${total} browser assertions, ${failures} failures${invalid ? ', INVALID' : ''}`);
process.exitCode = invalid ? 2 : failures ? 1 : 0;

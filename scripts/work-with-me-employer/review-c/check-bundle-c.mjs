// Read-only Bundle C browser checks. `baseline` records heading styles before
// markup edits; `preview` injects only the reviewed accessibility runtime into
// an unmodified browser document and checks FAQ geometry before/after.
// node check-bundle-c.mjs <staging38-or-localhost> [baseline|preview] [inline]
import { createRequire } from 'module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!/^(https:\/\/staging38\.thaisolitude\.com|http:\/\/localhost:8090)$/.test(base)) throw new Error('staging38 or isolated localhost only');
const baseline = process.argv.includes('baseline');
const preview = process.argv.includes('preview');
const inline = process.argv.includes('inline');
const headings = ['/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/chair-massage/', '/corporate-massage-portland/'];
const faqCounts = { '/about/': 3, '/thai-massage/': 7, '/therapeutic-massage/': 10, '/deep-tissue-massage/': 6, '/chair-massage/': 11, '/corporate-massage-portland/': 7, '/work-with-me/': 3, '/privacy-policy/': 2 };
const paths = baseline ? headings : ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/chair-massage/', '/corporate-massage-portland/', '/work-with-me/', '/contact/', '/privacy-policy/', '/accessibility/'];
let checks = 0, fails = 0, invalid = false;
const check = (label, ok, detail = '') => { checks++; if (!ok) fails++; console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${detail ? ' ' + detail : ''}`); };
const runtime = preview ? readFileSync(new URL('./accessibility-runtime.php', import.meta.url), 'utf8') : '';
const style = preview ? runtime.match(/<style id="ts-accessibility-runtime-css">([\s\S]*?)<\/style>/)?.[1] : '';
const postApply = !baseline && !preview;
const contract = postApply ? (await import('./bundle-c-contract.mjs')).createContract(JSON.parse(readFileSync(new URL('./heading-baseline.json', import.meta.url), 'utf8')), check, base) : null;
const script = preview ? runtime.match(/<script id="ts-accessibility-runtime">([\s\S]*?)<\/script>/)?.[1] : '';
if (preview && (!style || !script)) throw new Error('Reviewed runtime style/script not found');
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
const geometry = page => page.evaluate(() => ({
  height: document.documentElement.scrollHeight,
  items: [...document.querySelectorAll('.uagb-faq-item')].map(item => {
    const rect = e => { const r = e.getBoundingClientRect(); return [r.x, r.y, r.width, r.height]; };
    return [rect(item), rect(item.querySelector('.uagb-faq-questions-button'))];
  })
}));
const equalGeometry = (a, b) => a.height === b.height && a.items.length === b.items.length && a.items.every((item, i) => item.every((box, j) => box.every((n, k) => Math.abs(n - b.items[i][j][k]) < 0.1)));
try {
  for (const path of paths) {
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    const response = await page.goto(base + path, { waitUntil: 'networkidle', timeout: 60000 });
    if ((response?.status() === 202 && response.headers()['sg-captcha']) || /Robot Challenge/i.test(await page.title())) {
      invalid = true;
      console.log('INVALID: SiteGround bot challenge; stop without further requests');
      await page.close();
      break;
    }
    check(path + ' HTTP 200', response?.status() === 200);
    await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' });
    await page.evaluate(async () => { await document.fonts.ready; for (let y = 0; y < document.body.scrollHeight; y += innerHeight) { scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); } scrollTo(0, 0); });
    await page.waitForTimeout(150);
    if (baseline) {
      for (const width of [1280, 390]) {
        await page.setViewportSize({ width, height: 900 });
        const state = await page.evaluate(() => [...document.querySelectorAll('p.uagb-heading-text,.ts-corp__card-title')].map(el => {
          const block = el.closest('[class*="uagb-block-"]');
          const cs = getComputedStyle(el), r = el.getBoundingClientRect();
          return { text: el.textContent.trim(), tag: el.tagName, block: block ? [...block.classList].find(c => c.startsWith('uagb-block-')) : null, font: cs.fontFamily, size: cs.fontSize, weight: cs.fontWeight, line: cs.lineHeight, margins: [cs.marginTop, cs.marginBottom], rect: [r.x, r.y, r.width, r.height] };
        }));
        console.log('BASELINE ' + JSON.stringify({ path, width, headings: state }));
      }
      check(path + ' no browser errors', errors.length === 0, errors.join('; '));
      await page.close();
      continue;
    }
    const before = {};
    if (preview) {
      for (const width of [1280, 390]) { await page.setViewportSize({ width, height: 900 }); before[width] = await geometry(page); }
      await page.addStyleTag({ content: style });
      await page.addScriptTag({ content: script });
      await page.waitForTimeout(100);
    }
    for (const width of (postApply ? [1280, 390, 320] : [1280, 390])) {
      await page.setViewportSize({ width, height: 900 });
      if (preview) { await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light')); check(`${path} ${width} FAQ/runtime geometry unchanged`, equalGeometry(before[width], await geometry(page))); }
      for (const theme of ['light', 'dark']) {
        await page.evaluate(t => document.documentElement.setAttribute('data-ts-theme', t), theme);
        const state = await page.evaluate(() => {
          const items = [...document.querySelectorAll('.uagb-faq-item')];
          const buttons = [...document.querySelectorAll('button.ts-faq-disclosure')];
          const menu = document.getElementById('footer-site-navigation');
          const widget = document.querySelector('aside[data-section="sidebar-widgets-footer-widget-1"]');
          const scroll = document.getElementById('ast-scroll-top');
          const pdfs = [...document.querySelectorAll('a[target="_blank"]')].filter(a => /\.pdf(?:[?#]|$)/i.test(a.getAttribute('href') || '') && a.textContent.trim().replace(/\s+/g, ' ') === 'View résumé (PDF)');
          return {
            overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
            count: items.length, buttons: buttons.length,
            oldRoles: [...document.querySelectorAll('.wp-block-uagb-faq[role="tablist"],.wp-block-uagb-faq [role="tab"],.wp-block-uagb-faq [role="tabpanel"],.wp-block-uagb-faq [aria-selected]')].length,
            disclosure: buttons.every(button => {
              const answer = document.getElementById(button.getAttribute('aria-controls'));
              const item = button.closest('.uagb-faq-item');
              const expanded = item.classList.contains('uagb-faq-item-active');
              return button.type === 'button' && answer && item.contains(answer) && answer.getAttribute('aria-labelledby') === button.id && button.getAttribute('aria-expanded') === String(expanded) && !item.hasAttribute('tabindex');
            }),
            icons: items.every(item => [...item.querySelectorAll('.uagb-faq-icon-wrap')].every(icon => icon.getAttribute('aria-hidden') === 'true')),
            menu: menu?.getAttribute('aria-label'), widget: widget?.getAttribute('aria-label'), scroll: scroll?.getAttribute('role'),
            pdfs: pdfs.map(a => a.getAttribute('aria-label')),
          };
        });
        const tag = `${path} ${width} ${theme}`;
        check(tag + ' no side-scroll', !state.overflow);
        check(tag + ' expected FAQ count and one native button per question', state.count === (faqCounts[path] || 0) && state.buttons === state.count, `items=${state.count} buttons=${state.buttons}`);
        check(tag + ' disclosure ARIA, unique controls and no tab roles', state.oldRoles === 0 && state.disclosure && state.icons);
        check(tag + ' distinct footer names and scroll button role', state.menu === 'Footer navigation' && state.widget === 'Footer information' && state.scroll === 'button');
        check(tag + ' résumé links announce new tab', (!['/contact/', '/work-with-me/'].includes(path) || state.pdfs.length > 0) && state.pdfs.every(label => label === 'View résumé (PDF), opens in a new tab'));
        if (contract) await contract.atWidth(page, path, width, theme);
      }
    }
    if (contract) await contract.pageInteractions(page, path);
    const first = page.locator('button.ts-faq-disclosure').first();
    if (faqCounts[path]) {
      const expanded = await first.getAttribute('aria-expanded');
      await first.focus();
      await page.keyboard.press('Enter');
      await page.waitForTimeout(450);
      check(path + ' Enter toggles once', await first.getAttribute('aria-expanded') !== expanded);
      await page.keyboard.press('Space');
      await page.waitForTimeout(450);
      check(path + ' Space toggles once', await first.getAttribute('aria-expanded') === expanded);
      await first.click();
      await page.waitForTimeout(450);
      check(path + ' mouse toggles once', await first.getAttribute('aria-expanded') !== expanded);
      await first.click();
      await page.waitForTimeout(450);
      check(path + ' mouse closes once', await first.getAttribute('aria-expanded') === expanded);
    }
    const answerLink = page.locator('.uagb-faq-content a').first();
    if (await answerLink.count()) {
      const unchanged = await answerLink.evaluate(link => {
        const item = link.closest('.uagb-faq-item');
        const before = item.classList.contains('uagb-faq-item-active');
        link.dispatchEvent(new KeyboardEvent('keyup', { key: 'Enter', keyCode: 13, bubbles: true }));
        return item.classList.contains('uagb-faq-item-active') === before;
      });
      check(path + ' answer-link keyup does not toggle its FAQ', unchanged);
    }
    const scroll = page.locator('#ast-scroll-top');
    await page.evaluate(() => scrollTo(0, document.body.scrollHeight));
    await page.waitForTimeout(150);
    await scroll.focus();
    await page.keyboard.press('Space');
    await page.waitForFunction(() => scrollY < 5, undefined, { timeout: 3000 }).catch(() => {});
    check(path + ' scroll-to-top Space works', await page.evaluate(() => scrollY < 5));
    if (preview) {
      await page.addScriptTag({ content: script });
      check(path + ' runtime re-entry is idempotent', await page.locator('button.ts-faq-disclosure').count() === (faqCounts[path] || 0));
    }
    if (inline && ['/chair-massage/', '/contact/'].includes(path)) {
      await page.setViewportSize({ width: 390, height: 844 });
      await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light'));
      const target = path === '/chair-massage/' ? page.locator('.wp-block-uagb-faq').first() : page.locator('main');
      const jpg = await target.screenshot({ type: 'jpeg', quality: 45 });
      console.log(`INLINE bundle-c-${path.replace(/\//g, '')}.jpg base64:${jpg.toString('base64')}`);
    }
    check(path + ' no browser errors', errors.length === 0, errors.join('; '));
    await page.close();
  }
  if (contract && !invalid) { invalid = await contract.endpoints(context); if (!invalid) contract.finish(); }
} finally { await context.close(); await browser.close(); }
console.log(`Bundle C: ${checks} assertions, ${fails} failures${invalid ? ', INVALID challenge' : ''}`);
process.exitCode = invalid ? 2 : fails ? 1 : 0;

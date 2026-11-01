// One browser run for Bundle A: nine changed pages, phone/tablet/desktop and both
// themes, with three inline screenshots. Exit 2 on SiteGround's challenge.
// node check-bundle-a.mjs https://staging38.thaisolitude.com [inline]
import { createRequire } from 'module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!/^https:\/\/(?:staging38\.)?thaisolitude\.com$/.test(base)) throw new Error('staging38 or live only');
const inline = process.argv.includes('inline');
const browser = await chromium.launch();
let fails = 0;
const check = (label, ok, detail = '') => { console.log(`${ok ? 'PASS' : 'FAIL'} ${label}${detail ? ' ' + detail : ''}`); if (!ok) fails++; };
const paths = ['/', '/about/', '/services/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/chair-massage/', '/corporate-massage-portland/', '/contact/'];
const availability = 'Right now I offer corporate and group massage only, at suitable Portland venues; individual appointments at a dedicated Thai Solitude location are planned for summer 2027.';
try {
  for (const path of paths) {
    const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    const response = await page.goto(base + path, { waitUntil: 'networkidle', timeout: 60000 });
    if ((response?.status() === 202 && response.headers()['sg-captcha']) || /Robot Challenge/i.test(await page.title())) {
      console.log('INVALID: SiteGround bot challenge; stop, no further page requests');
      process.exitCode = 2;
      break;
    }
    check(path + ' HTTP 200', response?.status() === 200);
    await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' });
    // Load lazy pictures once, then all other measurements reuse this page.
    await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += innerHeight) { scrollTo(0, y); await new Promise(r => setTimeout(r, 60)); } scrollTo(0, 0); });
    for (const width of [1280, 768, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      for (const theme of ['light', 'dark']) {
        await page.evaluate(t => document.documentElement.setAttribute('data-ts-theme', t), theme);
        await page.waitForTimeout(150);
        const state = await page.evaluate(({ path, availability }) => {
          const main = document.querySelector('main');
          const next = main.querySelector('.ts-review-next');
          const other = main.querySelector('.ts-review-other');
          const sticky = document.querySelector('.ts-corp__sticky');
          const visible = e => e && getComputedStyle(e).display !== 'none';
          const rect = e => { const r = e.getBoundingClientRect(); return { x: r.x, y: r.y, w: r.width, h: r.height }; };
          const nextLinks = next ? [...next.querySelectorAll('.wp-block-button__link')].map(a => [a.textContent.trim(), a.getAttribute('href')]) : [];
          const jumps = [...document.querySelectorAll('.ts-jump a')].map(a => ({ text: a.textContent.trim(), href: a.getAttribute('href'), target: !!document.getElementById(a.getAttribute('href').slice(1)) }));
          const result = { overflow: document.documentElement.scrollWidth > innerWidth + 1, nextLinks, nextText: next?.textContent || '', otherLinks: other ? [...other.querySelectorAll('a')].map(a => a.getAttribute('href')) : [], jumps, stickyVisible: visible(sticky), stickyLinks: sticky ? [...sticky.querySelectorAll('a')].map(a => ({ box: rect(a), underline: getComputedStyle(a).textDecorationLine, href: a.getAttribute('href') })) : [] };
          if (path === '/services/') {
            result.order = ['8bb921f3','b3c7a91e','1f6539c9','4e23bf70','75fb60e4'].map(id => document.querySelector('.uagb-block-' + id).getBoundingClientRect().top);
            result.availability = main.textContent.includes(availability);
          }
          if (path === '/contact/') result.contact = !!document.querySelector('#corporate-quote') && document.querySelectorAll('.ts-review-contact a[href^="mailto:"]').length === 1;
          if (path === '/chair-massage/') result.chairFaqLinks = [...document.querySelectorAll('.uagb-faq-content a')].map(a => a.getAttribute('href'));
          return result;
        }, { path, availability });
        const tag = `${path} ${width} ${theme}`;
        check(tag + ' no side-scroll', !state.overflow);
        if (path !== '/corporate-massage-portland/') check(tag + ' jump links have targets', state.jumps.length >= 2 && state.jumps.every(j => j.target));
        if (/^\/(thai-massage|therapeutic-massage|deep-tissue-massage|chair-massage|services)\/$/.test(path)) {
          check(tag + ' closing quote and question buttons', JSON.stringify(state.nextLinks) === JSON.stringify([['Request a corporate massage quote','/corporate-massage-portland/'],['Ask a question','/contact/']]) && state.nextText.includes('November 14, 2026'));
          if (path !== '/services/') check(tag + ' four other-service links', state.otherLinks.length === 4 && new Set(state.otherLinks).size === 4 && !state.otherLinks.includes(path));
        }
        if (path === '/services/') check(tag + ' bookable rows first and availability', state.availability && state.order.every((y,i) => i === 0 || y > state.order[i-1]));
        if (path === '/contact/') check(tag + ' corporate card and jump', state.contact && state.jumps.some(j => j.text === 'Corporate quote'));
        if (path === '/chair-massage/') check(tag + ' three Corporate FAQ links', state.chairFaqLinks.length === 3 && state.chairFaqLinks.every(h => h === '/corporate-massage-portland/'));
        if (path === '/corporate-massage-portland/') {
          check(tag + ' sticky shown on phones only', state.stickyVisible === (width <= 600));
          if (width <= 600) check(tag + ' both sticky buttons fit and have no underline', state.stickyLinks.length === 2 && state.stickyLinks.every(a => a.box.w >= 44 && a.box.h >= 44 && a.box.x >= 0 && a.box.x + a.box.w <= width + 1 && a.underline === 'none'));
        }
      }
      // Estimate height added by the new blocks using the SAME rendered page,
      // hiding only additions. This is a DOM measurement, not a pixel proof.
      if ([1280, 390].includes(width)) {
        const height = await page.evaluate(({ availability, path }) => {
          const after = document.documentElement.scrollHeight;
          const additions = [...document.querySelectorAll('.ts-review-next,.ts-review-other,.ts-review-contact')];
          const reading = [...document.querySelectorAll('.ts-corp p')].find(p => p.textContent.startsWith('Read more about chair massage:'));
          if (reading) additions.push(reading);
          const styles = additions.map(e => e.style.display);
          additions.forEach(e => e.style.display = 'none');
          const sticky = document.querySelector('.ts-corp__sticky');
          const parent = sticky?.parentNode, sibling = sticky?.nextSibling;
          sticky?.remove();
          const description = [...document.querySelectorAll('p.uagb-desc-text')].find(e => e.textContent.includes(availability));
          const descriptionHtml = description?.innerHTML;
          if (description) description.textContent = description.textContent.replace(' ' + availability, '');
          const homeLabels = path === '/' ? ['2c16c927','591505c6','416130fd'].map(id => document.querySelector('.uagb-block-' + id + ' .uagb-button__link')) : [];
          const labels = homeLabels.map(e => e.textContent);
          homeLabels.forEach(e => e.textContent = 'Services');
          const before = document.documentElement.scrollHeight;
          additions.forEach((e,i) => e.style.display = styles[i]);
          if (sticky) parent.insertBefore(sticky, sibling);
          if (description) description.innerHTML = descriptionHtml;
          homeLabels.forEach((e,i) => e.textContent = labels[i]);
          return { after, before, added: after - before };
        }, { availability, path });
        console.log('HEIGHT', path, width, JSON.stringify(height));
      }
    }
    if (inline && ['/thai-massage/', '/contact/', '/corporate-massage-portland/'].includes(path)) {
      await page.setViewportSize({ width: 390, height: 844 });
      await page.evaluate(() => document.documentElement.setAttribute('data-ts-theme', 'light'));
      const target = path === '/thai-massage/' ? '.ts-review-next' : path === '/contact/' ? '.ts-review-contact' : '.ts-corp__sticky';
      await page.locator(target).scrollIntoViewIfNeeded();
      const jpg = await page.screenshot({ type: 'jpeg', quality: 55 });
      console.log(`INLINE review-a-${path.replace(/\//g,'')}.jpg base64:${jpg.toString('base64')}`);
    }
    check(path + ' no script errors', errors.length === 0, errors.slice(0,2).join(' | '));
    await context.close();
  }
} finally { await browser.close(); }
if (process.exitCode !== 2) { console.log(`RESULT: ${fails ? fails + ' checks FAILED' : 'all checks passed'}`); process.exitCode = fails ? 1 : 0; }

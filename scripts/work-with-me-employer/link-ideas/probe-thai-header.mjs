// Read-only probe of staging39 /thai-massage/: header structure and sizes, whether a pinned header works there, the current jump-link row.
// node probe-thai-header.mjs https://staging39.thaisolitude.com   (changes nothing on the site; any test styling lives only in the probe's own browser)
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium, devices } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (base !== 'https://staging39.thaisolitude.com') throw new Error('staging39 only');
const b = await chromium.launch();
for (const [size, opts] of [['phone', devices['iPhone 13']], ['desktop', { viewport: { width: 1280, height: 860 } }]]) {
  const p = await (await b.newContext({ ...opts, deviceScaleFactor: 1 })).newPage();
  const r = await p.goto(`${base}/thai-massage/`, { waitUntil: 'load' });
  if (r.status() !== 200) { console.log('INVALID status', r.status()); process.exit(2); }
  const info = await p.evaluate(() => {
    const vis = e => e && getComputedStyle(e).display !== 'none' && e.getBoundingClientRect().height > 0;
    const d = e => e ? `${e.tagName.toLowerCase()}${e.id ? '#' + e.id : ''}.${[...e.classList].join('.')} pos=${getComputedStyle(e).position} h=${Math.round(e.getBoundingClientRect().height)} top=${Math.round(e.getBoundingClientRect().top + scrollY)} vis=${vis(e)}` : 'none';
    const m = document.querySelector('#masthead');
    const anc = []; for (let e = m && m.parentElement; e; e = e.parentElement) { const s = getComputedStyle(e); anc.push(`${e.tagName.toLowerCase()}${e.id ? '#' + e.id : ''} ov=${s.overflow}/${s.overflowX}/${s.overflowY} ${s.transform !== 'none' ? 'TRANSFORM' : ''}${s.contain !== 'none' ? ' contain=' + s.contain : ''}`); }
    const kids = m ? [...m.querySelectorAll(':scope > *, :scope > * > *')].map(d) : [];
    const jump = document.querySelector('.ts-jump');
    const janc = []; for (let e = jump && jump.parentElement; e && e !== document.body; e = e.parentElement) { const s = getComputedStyle(e); janc.push(`${e.tagName.toLowerCase()}${e.id ? '#' + e.id : ''}.${[...e.classList].slice(0, 3).join('.')} ov=${s.overflowX}/${s.overflowY}`); }
    return { body: document.body.className, html: document.documentElement.getAttribute('style'), masthead: d(m), kids, anc, jump: jump ? jump.outerHTML : 'none', jumpBox: d(jump), janc,
      ids: [...document.querySelectorAll('.ts-jump a')].map(a => { const t = document.getElementById(a.getAttribute('href').slice(1)); return `${a.getAttribute('href')} -> ${t ? t.tagName + ' smt=' + getComputedStyle(t).scrollMarginTop : 'MISSING'}`; }),
      scrollPad: getComputedStyle(document.documentElement).scrollPaddingTop, smooth: getComputedStyle(document.documentElement).scrollBehavior,
      guide: d(document.getElementById('ts-section-guide-trigger')), scrollTop: d(document.querySelector('#ast-scroll-top')),
      fixedEls: [...document.querySelectorAll('body *')].filter(e => ['fixed', 'sticky'].includes(getComputedStyle(e).position)).map(d).slice(0, 12) };
  });
  console.log(`===== ${size}\n` + JSON.stringify(info, null, 1));
  const sticky = await p.evaluate(async () => {
    const m = document.querySelector('#masthead'); m.style.position = 'sticky'; m.style.top = '0'; m.style.zIndex = '50';
    scrollTo(0, 1500); await new Promise(r => setTimeout(r, 300));
    return `pinned-header test: masthead top after scrolling 1500px = ${Math.round(m.getBoundingClientRect().top)} (0 means a pinned header works)`;
  });
  console.log(sticky);
}
await b.close();

// Rendered check of the Deep Tissue Massage page after install-deep-tissue-evidence.php (read-only; public requests only).
// Usage: node check-deep-tissue-evidence.mjs <base-url> <out-dir> [label]        e.g. node check-deep-tissue-evidence.mjs https://staging36.thaisolitude.com ~/.cache/wwm-scratch/deep-shots staging36
//        BROWSER=firefox (or a 4th argument 'firefox') to use Firefox instead of Chromium.
// Per width (1280 / 390 / 320) in LIGHT and DARK: HTTP 200, no side-scroll, the research heading and the FAQ heading once, the 6 FAQ questions once each in order with no links inside answers, the 5 research panels
// (first open, the others closed), every panel opens and closes with the mouse AND with Enter/Space, numbered markers 1..N once each in first-use order with 33 Sources entries, the new body paragraph (the three old
// sentences gone), the three pictures with tooltip equal to the alt text and no "by Butsaba" tooltip, "myofascial" only in a verbatim source title, no console errors, screenshots. Exit 1 if any check fails. Exit 2 (INVALID) if the bot challenge answers.
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = process.argv[3] || '/tmp/ther-shots';
const label = process.argv[4] || 'run';
fs.mkdirSync(out, { recursive: true });
const FAQ = ['Does Deep Tissue Massage Have to Hurt?', 'How Is Deep Tissue Massage Different From Therapeutic Massage?', 'Can I Choose Where You Focus?', 'What Should I Tell You Before a Session?',
  'Where Do Sessions Take Place?', 'How Do I Book a Session?'];
const PANELS = ['Reviews, and What They Say About Limits', 'Neck, Shoulders and Back', 'Exercise, Soreness and Recovery', 'Relaxation and the Role of Pressure', 'Sources'];
const OLD_SENTENCES = ['Trials in specific musculoskeletal conditions suggest deep tissue massage', 'A randomized trial in athletes linked repeated deep tissue massage', 'Research suggests deep tissue massage added to conventional rehabilitation may reduce pain intensity'];
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const useFirefox = process.env.BROWSER === 'firefox' || process.argv[5] === 'firefox';   // the 4th argument works where an environment variable cannot be passed (a GitHub Actions request)
const engine = useFirefox ? pw.firefox : pw.chromium;
const browser = await engine.launch(useFirefox ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
for (const theme of ['light', 'dark']) {
  for (const width of [1280, 390, 320]) {
    const tag = `${theme}-${width}`;
    const ctx = await browser.newContext({ viewport: { width, height: 900 } });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errors.push(m.text()); });
    const resp = await page.goto(base + '/deep-tissue-massage/', { waitUntil: 'networkidle' });
    if (resp && resp.status() === 202) { console.log('INVALID: the SiteGround bot challenge (HTTP 202) answered instead of the page; nothing was tested. Run it once more later, not in a loop.'); await browser.close(); process.exit(2); }
    check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
    const dark = await page.evaluate(() => document.documentElement.getAttribute('data-ts-theme'));
    if (theme === 'dark') check(`${tag}: dark mode is on`, dark === 'dark', dark);
    const r = await page.evaluate((oldSentences) => {
      const txt = el => (el.textContent || '').replace(/\s+/g, ' ').trim();
      const h2 = [...document.querySelectorAll('main h2, .entry-content h2')].map(txt);
      const faqQ = [...document.querySelectorAll('.uagb-question')].map(txt);
      const det = [...document.querySelectorAll('details.wp-block-details')].map(d => ({ s: txt(d.querySelector('summary')), open: d.open }));
      const markers = [...document.querySelectorAll('a')].map(a => txt(a)).filter(t => /^\[\d+\]$/.test(t));
      const firstUse = []; markers.forEach(m => { const n = +m.slice(1, -1); if (!firstUse.includes(n)) firstUse.push(n); });
      const cites = document.querySelectorAll('cite.ts-source').length;
      const imgs = [...document.querySelectorAll('.wp-block-uagb-image img')].map(i => ({ alt: i.alt, title: i.title }));
      const body = document.body.innerText;          // what is visible
      const all = document.body.textContent;          // everything in the page, including closed panels and collapsed FAQ answers
      return { h2, headR: h2.filter(x => x === 'What the Research Says About Deep Tissue Massage').length, faqHeadR: h2.filter(x => x === 'Questions About Deep Tissue Massage').length, faqQ, det, firstUse, cites, markerCount: markers.length,
        scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth, faqLinks: document.querySelectorAll('.uagb-faq-content a').length, imgs,
        newBody: body.includes('Research on deep tissue massage is limited and mixed.'), oldGone: oldSentences.every(o => !body.includes(o)),
        myo: (all.match(/myofascial/gi) || []).length, myoInCite: [...document.querySelectorAll('cite.ts-source')].some(c => /myofascial/i.test(c.textContent)),
        byButsaba: document.body.innerHTML.includes('Deep Tissue Massage by Butsaba'), doctorSentence: (all.replace(/\s+/g, ' ').match(/means you should see a doctor before massage\./g) || []).length };
    }, OLD_SENTENCES);
    check(`${tag}: no side-scroll`, r.scroll <= 0, r.scroll);
    check(`${tag}: the research heading and the FAQ heading appear once each`, r.headR === 1 && r.faqHeadR === 1, `${r.headR},${r.faqHeadR}`);
    check(`${tag}: the 6 FAQ questions, once each, in order`, JSON.stringify(r.faqQ) === JSON.stringify(FAQ), r.faqQ.length);
    check(`${tag}: no links inside FAQ answers`, r.faqLinks === 0, r.faqLinks);
    check(`${tag}: the 5 research panels, in order`, JSON.stringify(r.det.map(d => d.s)) === JSON.stringify(PANELS), r.det.map(d => d.s).join(' | '));
    check(`${tag}: first panel open, the others closed`, r.det.map(d => d.open).join() === 'true,false,false,false,false', r.det.map(d => d.open).join());
    check(`${tag}: markers numbered 1..N in first-use order and 33 Sources entries`, r.firstUse.length === 33 && r.firstUse.every((n, i) => n === i + 1) && r.cites === 33, `${r.firstUse.length} numbers, ${r.cites} sources, ${r.markerCount} links`);
    check(`${tag}: the new body paragraph is there and the three old sentences are gone`, r.newBody && r.oldGone, `${r.newBody},${r.oldGone}`);
    check(`${tag}: 'myofascial' appears once, inside a verbatim source title`, r.myo === 1 && r.myoInCite, `${r.myo},${r.myoInCite}`);
    check(`${tag}: the page's safety sentence appears twice (page text and FAQ answer)`, r.doctorSentence === 2, r.doctorSentence);
    check(`${tag}: three pictures, tooltip equal to the alt text`, r.imgs.length === 3 && r.imgs.every(i => i.alt && i.alt === i.title), r.imgs.map(i => i.alt.slice(0, 24)).join(' | '));
    check(`${tag}: no "Deep Tissue Massage by Butsaba" tooltip left`, !r.byButsaba);
    if (width === 1280 || width === 390) {
      const sums = page.locator('details.wp-block-details > summary');
      await sums.nth(1).scrollIntoViewIfNeeded(); await sums.nth(1).click();
      let st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: click opens a panel`, st[1] === true, st.join());
      await sums.nth(1).click();
      st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: click closes it again`, st[1] === false, st.join());
      await sums.nth(2).focus(); await page.keyboard.press('Enter');
      st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: Enter opens a panel from the keyboard`, st[2] === true, st.join());
      await page.keyboard.press('Space');
      st = await page.evaluate(() => [...document.querySelectorAll('details.wp-block-details')].map(d => d.open));
      check(`${tag}: Space closes it again`, st[2] === false, st.join());
    }
    // the FAQ opens like the other accordions on the site (click the second question; it opens, the first-open item closes: "one open at a time")
    const q = page.locator('.uagb-faq-questions-button', { hasText: 'How Is Deep Tissue Massage Different From Therapeutic Massage?' }).first();
    await q.scrollIntoViewIfNeeded(); await q.click(); await page.waitForTimeout(500);
    const open = await page.evaluate(() => [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].filter(e => e.classList.contains('uagb-faq-item-active')).map(e => e.textContent.replace(/\s+/g, ' ').trim().slice(0, 90)));
    check(`${tag}: a FAQ question opens, one open at a time`, open.length === 1 && /Therapeutic/.test(open[0]), open.join(' | '));
    await page.screenshot({ path: path.join(out, `${label}-${tag}-faq.png`), fullPage: false });
    await page.locator('h2.uagb-heading-text', { hasText: 'What the Research Says' }).scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(out, `${label}-${tag}-research.png`), fullPage: false });
    await page.evaluate(() => document.querySelectorAll('details.wp-block-details').forEach(d => { d.open = true; }));
    const sc = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    check(`${tag}: no side-scroll with every panel open`, sc <= 0, sc);
    await page.screenshot({ path: path.join(out, `${label}-${tag}-full.png`), fullPage: true });
    check(`${tag}: no script errors`, errors.length === 0, errors.slice(0, 2).join(' || '));
    await ctx.close();
  }
}
await browser.close();
console.log('\nRESULT: ' + (fails ? fails + ' check(s) FAILED' : 'all checks passed'));
process.exit(fails ? 1 : 0);

// ref: ./install-deep-tissue-evidence.php — the installer whose result this checks; ../therapeutic/check-therapeutic-evidence.mjs — the sibling check this follows

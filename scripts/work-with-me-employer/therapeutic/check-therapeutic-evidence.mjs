// Rendered check of the Therapeutic Massage page after install-therapeutic-evidence.php (read-only; public requests only).
// Usage: node check-therapeutic-evidence.mjs <base-url> <out-dir> [label]        e.g. node check-therapeutic-evidence.mjs http://localhost:8090 ~/.cache/wwm-scratch/ther-shots local
//        BROWSER=firefox to use Firefox instead of Chromium.
// Per width (1280 / 390 / 320) in LIGHT and DARK: HTTP 200, no side-scroll, the research heading once, the 10 FAQ questions once each in order (6 old, 4 new) with no links inside answers, the 5 research panels (first open,
// the others closed), every panel opens and closes with the mouse AND with Enter/Space, numbered markers 1..N once each in first-use order with N Sources entries, the dropped studies (stroke, cancer pain) gone from the page,
// the five pictures with five different alt texts and tooltips and no "by Butsaba" tooltip, the reworded neck/shoulder sentence, no console errors, screenshots. Exit 1 if any check fails.
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');
const base = (process.argv[2] || 'http://localhost:8090').replace(/\/$/, '');
const out = process.argv[3] || '/tmp/ther-shots';
const label = process.argv[4] || 'run';
fs.mkdirSync(out, { recursive: true });
const FAQ = ['What Is the Difference Between Therapeutic and Relaxation Massage?', 'How Is a Therapeutic Session Planned?', 'Will I Feel the Results Right Away?', 'How Often Should I Come In for Therapeutic Massage?',
  'Is Therapeutic Massage Appropriate If I Have No Specific Complaint?', 'Can I Ask You to Focus on My Neck and Shoulders?', 'How Is Therapeutic Massage Different From Deep Tissue Massage?',
  'Can I Change the Pressure During a Session?', 'Where Do Sessions Take Place?', 'How Do I Book a Session?'];
const PANELS = ['Reviews, and What They Say About Limits', 'Neck, Shoulders and Back', 'Exercise, Flexibility and Recovery', 'Stress, Relaxation and Well-Being', 'Sources'];
const DROPPED = ['https://pubmed.ncbi.nlm.nih.gov/33919371/', 'https://pubmed.ncbi.nlm.nih.gov/39558520/'];   // the post-stroke and cancer-pain reviews
let fails = 0;
const check = (name, ok, detail = '') => { console.log((ok ? 'PASS ' : 'FAIL ') + name + (detail !== '' ? '  [' + detail + ']' : '')); if (!ok) fails++; };
const engine = process.env.BROWSER === 'firefox' ? pw.firefox : pw.chromium;
const browser = await engine.launch(process.env.BROWSER === 'firefox' ? {} : { executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
for (const theme of ['light', 'dark']) {
  for (const width of [1280, 390, 320]) {
    const tag = `${theme}-${width}`;
    const ctx = await browser.newContext({ viewport: { width, height: 900 } });
    if (theme === 'dark') await ctx.addInitScript(() => { try { localStorage.setItem('ts-theme', 'dark'); } catch (e) {} });
    const page = await ctx.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(String(e)));
    page.on('console', m => { if (m.type() === 'error' && !/Failed to load resource/.test(m.text())) errors.push(m.text()); });
    const resp = await page.goto(base + '/therapeutic-massage/', { waitUntil: 'networkidle' });
    check(`${tag}: HTTP 200`, resp && resp.status() === 200, resp && resp.status());
    const dark = await page.evaluate(() => document.documentElement.getAttribute('data-ts-theme'));
    if (theme === 'dark') check(`${tag}: dark mode is on`, dark === 'dark', dark);
    const r = await page.evaluate((dropped) => {
      const txt = el => (el.textContent || '').replace(/\s+/g, ' ').trim();
      const h2 = [...document.querySelectorAll('main h2, .entry-content h2')].map(txt);
      const faqQ = [...document.querySelectorAll('.uagb-question')].map(txt);
      const det = [...document.querySelectorAll('details.wp-block-details')].map(d => ({ s: txt(d.querySelector('summary')), open: d.open }));
      const markers = [...document.querySelectorAll('a')].map(a => txt(a)).filter(t => /^\[\d+\]$/.test(t));
      const firstUse = []; markers.forEach(m => { const n = +m.slice(1, -1); if (!firstUse.includes(n)) firstUse.push(n); });
      const cites = document.querySelectorAll('cite.ts-source').length;
      const hrefs = [...document.querySelectorAll('a')].map(a => a.href);
      const imgs = [...document.querySelectorAll('.wp-block-uagb-image img')].map(i => ({ alt: i.alt, title: i.title }));
      return { h2, headR: h2.filter(x => x === 'What the Research Says About Therapeutic Massage').length, faqQ, det, firstUse, cites, markerCount: markers.length,
        scroll: document.documentElement.scrollWidth - document.documentElement.clientWidth, faqLinks: document.querySelectorAll('.uagb-faq-content a').length,
        droppedLinks: dropped.filter(u => hrefs.includes(u)).length, imgs,
        neck: document.body.innerText.includes('Research on massage for neck and shoulder pain is mixed.'),
        encouraging: document.body.innerText.includes('and I adapt the pace and pressure to what suits you.') && document.body.innerText.includes('which suggests that regular sessions may matter.'),
        oldNeck: document.body.innerText.includes('Research suggests massage may be helpful for some people with neck or shoulder pain'),
        byButsaba: document.body.innerHTML.includes('Therapeutic Massage by Butsaba'), doctorCount: (document.body.innerText.match(/doctor/g) || []).length };
    }, DROPPED);
    check(`${tag}: no side-scroll`, r.scroll <= 0, r.scroll);
    check(`${tag}: the research heading appears once`, r.headR === 1, r.headR);
    check(`${tag}: the 10 FAQ questions, once each, in order`, JSON.stringify(r.faqQ) === JSON.stringify(FAQ), r.faqQ.length);
    check(`${tag}: no links inside FAQ answers`, r.faqLinks === 0, r.faqLinks);
    check(`${tag}: the 5 research panels, in order`, JSON.stringify(r.det.map(d => d.s)) === JSON.stringify(PANELS), r.det.map(d => d.s).join(' | '));
    check(`${tag}: first panel open, the others closed`, r.det.map(d => d.open).join() === 'true,false,false,false,false', r.det.map(d => d.open).join());
    check(`${tag}: markers numbered 1..N in first-use order and N Sources entries`, r.firstUse.length > 0 && r.firstUse.every((n, i) => n === i + 1) && r.cites === r.firstUse.length, `${r.firstUse.length} numbers, ${r.cites} sources, ${r.markerCount} links`);
    check(`${tag}: the stroke and cancer-pain studies are no longer linked anywhere`, r.droppedLinks === 0, r.droppedLinks);
    check(`${tag}: neck/shoulder sentence is the reworded one, the old one is gone`, r.neck && !r.oldNeck, `${r.neck},${r.oldNeck}`);
    check(`${tag}: an encouraging sentence on each side of the 'mixed' one`, r.encouraging);
    check(`${tag}: five pictures, five different alt texts, tooltip equal to the alt`, r.imgs.length === 5 && new Set(r.imgs.map(i => i.alt)).size === 5 && r.imgs.every(i => i.alt && i.alt === i.title), r.imgs.map(i => i.alt.slice(0, 24)).join(' | '));
    check(`${tag}: no "Therapeutic Massage by Butsaba" tooltip left`, !r.byButsaba);
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
    // the new FAQ questions open like the old ones (click the first new one; it opens, the first-open item closes: "one open at a time")
    const q = page.locator('.uagb-faq-questions-button', { hasText: 'How Is Therapeutic Massage Different From Deep Tissue Massage?' }).first();
    await q.scrollIntoViewIfNeeded(); await q.click(); await page.waitForTimeout(500);
    const open = await page.evaluate(() => [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].filter(e => e.classList.contains('uagb-faq-item-active')).map(e => e.textContent.replace(/\s+/g, ' ').trim().slice(0, 90)));
    check(`${tag}: a new FAQ question opens, one open at a time`, open.length === 1 && /Deep Tissue/.test(open[0]), open.join(' | '));
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

// ref: ./install-therapeutic-evidence.php — the installer whose result this checks; ../thai/check-thai-page.mjs — the sibling check this follows

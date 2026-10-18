// Read-only check of the rendered Therapeutic Massage page (public GETs only; screenshots go to the folder you name).
//   node check-therapeutic-page.mjs <base-url> <out-dir> [label]       e.g. node check-therapeutic-page.mjs https://staging33.thaisolitude.com ~/.cache/wwm-scratch/ther before
// For desktop (1280) and phone (390 and 320): no sideways scroll, the heading list (and any duplicates), the text of the three edited places, where the NCCIH source markers are and whether
// the article answers, the safety lines, and a screenshot of each edited place (the changed FAQ items are opened first). Prints facts; the PASS/FAIL checks are the ones in push-therapeutic-copy.sh.
// Playwright is taken from the Mirror project.
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
const require = createRequire('/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const [base, outDir, label = 'page'] = process.argv.slice(2);
if (!base || !outDir) { console.error('usage: node check-therapeutic-page.mjs <base-url> <out-dir> [label]'); process.exit(1); }
fs.mkdirSync(outDir, { recursive: true });
const NCCIH = 'https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know';
const b = await chromium.launch();
for (const [w, h, v] of [[1280, 900, 'desktop'], [390, 844, 'phone'], [320, 700, 'narrow']]) {
  const ctx = await b.newContext({ viewport: { width: w, height: h }, reducedMotion: 'reduce' });
  const p = await ctx.newPage();
  const resp = await p.goto(base + '/therapeutic-massage/', { waitUntil: 'networkidle', timeout: 60000 });
  await p.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important}' });
  const info = await p.evaluate(() => {
    const txt = el => el ? el.textContent.replace(/\s+/g, ' ').trim() : null;
    const heads = [...document.querySelectorAll('h1,h2')].map(x => txt(x));
    const dups = heads.filter((x, i) => heads.indexOf(x) !== i);
    const reasons = [...document.querySelectorAll('h2.uagb-heading-text')].find(x => /Common Reasons/.test(x.textContent));
    const para = reasons ? reasons.parentElement.querySelector('.uagb-desc-text') : null;
    const faq = q => { const i = [...document.querySelectorAll('.uagb-faq-child__outer-wrap')].find(x => x.textContent.includes(q)); return i ? { q, a: txt(i.querySelector('.uagb-faq-content')) } : null; };
    const links = para ? [...para.querySelectorAll('a')].map(a => { const r = a.getBoundingClientRect(); return { text: a.textContent, href: a.href, target: a.target || '(same tab)', w: Math.round(r.width), h: Math.round(r.height) }; }) : [];
    return {
      sideScroll: document.documentElement.scrollWidth > window.innerWidth, scrollW: document.documentElement.scrollWidth,
      h1h2: heads, duplicateHeadings: dups,
      reasonsParagraph: txt(para), reasonsLinks: links,
      faqNoComplaint: faq('Is Therapeutic Massage Appropriate If I Have No Specific Complaint?'),
      faqDifference: faq('What Is the Difference Between Therapeutic and Relaxation Massage?'),
      safety: [...document.body.innerText.matchAll(/[^.]*\bdoctor\b[^.]*\./g)].map(m => m[0].replace(/\s+/g, ' ').trim()),
      pageHeight: document.documentElement.scrollHeight,
    };
  });
  console.log(`\n===== ${v} (${w}px)  HTTP ${resp.status()} =====`);
  console.log(JSON.stringify(info, null, 2));
  if (v === 'desktop') {
    const r = await p.request.get(NCCIH, { timeout: 30000, headers: { 'user-agent': 'Mozilla/5.0 (X11; Linux x86_64; rv:130.0) Gecko/20100101 Firefox/130.0' } });
    console.log('NCCIH article: HTTP', r.status(), '| title:', ((await r.text()).match(/<title>(.*?)<\/title>/s) || [])[1]?.replace(/\s+/g, ' ').trim());
  }
  // Screenshots of the three edited places.
  const shot = async (loc, name) => { const e = loc.first(); if (await e.count()) { await e.scrollIntoViewIfNeeded(); await e.screenshot({ path: path.join(outDir, `${label}-${v}-${name}.png`) }); } };
  await shot(p.locator('div.wp-block-uagb-advanced-heading', { has: p.locator('h2', { hasText: 'Common Reasons' }) }), 'reasons');
  for (const [q, name] of [['What Is the Difference Between Therapeutic and Relaxation Massage?', 'faq-difference'], ['Is Therapeutic Massage Appropriate If I Have No Specific Complaint?', 'faq-no-complaint']]) {
    const item = p.locator('.uagb-faq-child__outer-wrap', { hasText: q }).first();
    if (await item.count()) {
      const open = await item.evaluate(e => e.classList.contains('uagb-faq-item-active'));
      if (!open) await item.locator('.uagb-faq-questions-button').click();
      await p.waitForTimeout(300);
      await shot(p.locator('.uagb-faq-child__outer-wrap', { hasText: q }), name);
    }
  }
  await ctx.close();
}
await b.close();

// ref: ./push-therapeutic-copy.sh — runs the pass/fail checks on the public page; ../tools/theme-test.mjs — same Playwright setup

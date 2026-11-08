// Read-only: print the rendered text of each section of a staging39 page (headings, paragraphs, list items, FAQ questions and answers), so short
// section summaries can be written from what a visitor actually sees (the FAQ answers are added by another snippet, not stored in the page content).
// node dump-sections.mjs https://staging39.thaisolitude.com [/thai-massage/]
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, ''), path = process.argv[3] || '/thai-massage/';
if (!['https://staging39.thaisolitude.com', 'http://localhost:8090'].includes(base)) throw new Error('staging39 or localhost:8090 only');
const b = await chromium.launch();
const p = await (await b.newContext()).newPage();
const r = await p.goto(base + path, { waitUntil: 'load' });
if (!r || r.status() !== 200) { console.log(`INVALID ${path} answered ${r && r.status()}`); await b.close(); process.exit(2); }
await p.waitForTimeout(1500);
const out = await p.evaluate(() => {
  const c = document.querySelector('.entry-content'), rows = [];
  c.querySelectorAll('details').forEach(d => { d.open = true; });
  for (const e of c.querySelectorAll('h2, h3, h4, p, li, summary, dt, dd')) {
    if (e.closest('.ts-pk, .ts-jump, nav, script, style')) continue;
    if (/^(P|LI|DD)$/.test(e.tagName) && e.querySelector('p, li')) continue;
    const t = e.textContent.replace(/\s+/g, ' ').trim(); if (t) rows.push((/^H/.test(e.tagName) ? '\n## ' : e.tagName === 'SUMMARY' || e.tagName === 'DT' ? 'Q: ' : '- ') + t);
  }
  return rows.join('\n');
});
console.log(out);
console.log('\nRESULT: dumped');
await b.close();

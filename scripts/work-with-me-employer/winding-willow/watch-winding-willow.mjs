// Read-only watch of Winding Willow's own website (windingwillowacupuncture.com), so additions there are not overlooked on our /winding-willow/ page.
// Usage: node watch-winding-willow.mjs <out-dir> [previous-snapshot.json]
// It loads each page in their menu with a real browser (their host refuses plain curl/python clients), keeps the visible text line by line
// (menu, cookie notice and repeated lines dropped), and writes <out-dir>/snapshot.json and <out-dir>/report.md. The report lists:
//   1. PRACTITIONERS ON THEIR SITE THAT OUR PAGE DOES NOT NAME (from their three team pages, compared with winding-willow-page.html)
//   2. pages added to or removed from their menu
//   3. per page, the lines added and removed since the previous snapshot (when one is given)
// Exit codes: 0 nothing new, 1 something changed since the previous snapshot (or the list of practitioners missing from our page changed), 2 INVALID (a page did not load: nothing proved).
// About a dozen page loads per run; run it once a day at most.
import { createRequire } from 'module';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const pw = require('playwright');

const SITE = 'https://www.windingwillowacupuncture.com';
const here = path.dirname(fileURLToPath(import.meta.url));
const OUR_PAGE = path.join(here, 'winding-willow-page.html');
const TEAM_PAGES = ['/all-providers-and-staff', '/acupuncture-providers', '/massage-therapy-providers'];
// Always watched, even if they drop out of the menu; the menu adds any others.
const BASE_PAGES = ['/', ...TEAM_PAGES, '/our-services-and-blog', '/book-now', '/butsaba-dusrai-lmt', '/insurance', '/cancer-care', '/join-our-team', '/contact-us-and-orders', '/motor-vehicle-work-comp-injury'];
const CRED = /\b(LAc|L\.Ac|LMT|ND|DAOM|MAcOM|DC|LPC|LCSW|RD|CNS)\b/;

const out = process.argv[2] || 'ww-watch';
const prevFile = process.argv[3];
fs.mkdirSync(out, { recursive: true });
const prev = prevFile && fs.existsSync(prevFile) ? JSON.parse(fs.readFileSync(prevFile, 'utf8')) : null;

const browser = await pw.chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();
const failed = [];   // a watched page that did not load: INVALID
const broken = [];   // a link found on their pages that does not answer 200 (their problem, reported, not INVALID)
const pages = {};
let menu = [];

async function load(p) {
  for (let i = 0; i < 2; i++) {
    try {
      const r = await page.goto(SITE + p, { waitUntil: 'domcontentloaded', timeout: 45000 });
      await page.waitForTimeout(1500);
      if (r && r.status() === 200) return true;
      if (r && r.status() >= 400 && r.status() < 500) return r.status();
    } catch (e) { /* retry once */ }
  }
  return false;
}
const ok = v => v === true;

async function grab() {
  return page.evaluate(() => {
    const navText = new Set([...document.querySelectorAll('nav a, header a, .s-nav a, [class*="navbar"] a, [class*="menu"] a')].map(a => a.innerText.trim()).filter(Boolean));
    const links = [...document.querySelectorAll('a[href]')].map(a => { try { const u = new URL(a.href, location.href); return u.host === location.host ? u.pathname.replace(/\/$/, '') || '/' : null; } catch { return null; } }).filter(Boolean);
    const seen = new Set();
    const lines = [];
    for (let l of document.body.innerText.split('\n')) {
      l = l.replace(/\s+/g, ' ').trim();
      if (!l || navText.has(l) || seen.has(l)) continue;
      if (/cookie|We use cookies|Learn More|Copyright \d{4}/i.test(l)) continue;
      seen.add(l); lines.push(l);
    }
    return { title: document.title, lines, links };
  });
}

if (!ok(await load('/'))) failed.push('/');
else {
  const g = await grab();
  pages['/'] = { title: g.title, lines: g.lines };
  menu = [...new Set(g.links)].filter(p => !/^\/(blog|pages|search)\b/.test(p)).sort();
}
const todo = [...new Set([...BASE_PAGES, ...menu])].filter(p => p !== '/');
for (const p of todo) {
  const v = await load(p);
  if (!ok(v)) { if (typeof v === 'number' && !BASE_PAGES.includes(p)) broken.push(`${p} (HTTP ${v})`); else failed.push(p); continue; }
  const g = await grab();
  pages[p] = { title: g.title, lines: g.lines };
}
await browser.close();

// Practitioner lines on the team pages: a line with a credential, cut at the credential list, reduced to the person's name.
const ours = fs.readFileSync(OUR_PAGE, 'utf8').replace(/<[^>]+>/g, ' ').replace(/&[a-z#0-9]+;/g, ' ');
const people = new Map();
for (const p of TEAM_PAGES) for (const l of pages[p]?.lines || []) {
  if (!CRED.test(l) || l.length > 160) continue;
  const m = l.match(/^(?:\*+)?\s*(?:Massage Therapy\s*)?((?:Dr\.\s+)?[A-Z][a-zA-Z'’.-]+(?:\s+'[^']+')?(?:\s+[A-Z][a-zA-Z'’.-]+){1,3}),?\s/);
  if (!m) continue;
  const name = m[1].replace(/\s+'[^']+'/, '').trim();
  if (/^(Email|Book|Call|Read|See|Meet|Order|Contact)\b/.test(name)) continue;
  if (!people.has(name)) people.set(name, { name, line: l, page: p });
}
const lastName = n => n.split(/\s+/).pop();
const missing = [...people.values()].filter(x => !ours.includes(lastName(x.name)));

const snapshot = { taken: new Date().toISOString(), site: SITE, menu, failed, broken, people: [...people.keys()].sort(), missing: missing.map(x => x.name).sort(), pages };
fs.writeFileSync(path.join(out, 'snapshot.json'), JSON.stringify(snapshot, null, 1));

const rep = [];
let changed = false;
rep.push(`# Winding Willow site watch, ${snapshot.taken.slice(0, 16).replace('T', ' ')} UTC`, '');
if (failed.length) rep.push(`**INVALID:** these pages did not load, so nothing is proved for them: ${failed.join(', ')}`, '');
if (broken.length) rep.push(`Links on their site that do not work (their pages, nothing to do on ours): ${broken.join(', ')}`, '');
rep.push('## 1. Practitioners on their team pages that our /winding-willow/ page does not name');
if (missing.length) { if (!prev || JSON.stringify(prev.missing || []) !== JSON.stringify(snapshot.missing)) changed = true; for (const x of missing) rep.push(`- **${x.name}** (${x.page}): ${x.line}`); }
else rep.push(`- none (all ${people.size} found are named: ${[...people.keys()].join(', ')})`);
rep.push('');
if (prev) {
  const added = menu.filter(p => !prev.menu.includes(p)), gone = prev.menu.filter(p => !menu.includes(p));
  rep.push('## 2. Their menu');
  if (added.length || gone.length) { changed = true; added.forEach(p => rep.push(`- NEW page: ${SITE}${p}`)); gone.forEach(p => rep.push(`- removed: ${p}`)); }
  else rep.push('- unchanged');
  const pa = snapshot.people.filter(n => !prev.people.includes(n)), pg = prev.people.filter(n => !snapshot.people.includes(n));
  if (pa.length || pg.length) { changed = true; rep.push('', '## Practitioner list'); pa.forEach(n => rep.push(`- NEW: ${n}`)); pg.forEach(n => rep.push(`- no longer listed: ${n}`)); }
  rep.push('', '## 3. Text changes per page (since the previous run)');
  let any = false;
  for (const p of Object.keys(pages).sort()) {
    const before = prev.pages[p]?.lines;
    if (!before) { if (prev.pages && !failed.includes(p)) { any = true; rep.push(`### ${p} (new to the watch)`); } continue; }
    const b = new Set(before), a = new Set(pages[p].lines);
    const add = pages[p].lines.filter(l => !b.has(l)), del = before.filter(l => !a.has(l));
    if (!add.length && !del.length) continue;
    any = true;
    rep.push(`### ${SITE}${p}`);
    add.slice(0, 40).forEach(l => rep.push(`+ ${l.slice(0, 300)}`));
    del.slice(0, 40).forEach(l => rep.push(`- ${l.slice(0, 300)}`));
    if (add.length > 40 || del.length > 40) rep.push(`(${add.length} added, ${del.length} removed lines in all)`);
  }
  if (any) changed = true; else rep.push('- no text changes');
} else rep.push('(No previous snapshot: this run is the baseline for the next one.)');
fs.writeFileSync(path.join(out, 'report.md'), rep.join('\n') + '\n');
console.log(rep.join('\n'));
process.exit(failed.length ? 2 : changed ? 1 : 0);

// ref: ./winding-willow-page.html — names on our page that the practitioner check compares against
// ref: ../../../.github/workflows/watch-winding-willow.yml — the daily run that keeps the last snapshot and opens the issue

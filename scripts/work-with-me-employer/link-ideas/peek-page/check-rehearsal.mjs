// The page-data step is inert while the verified legacy core is present.
// This rehearsal never changes the website or approves new wording.
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium } = require('playwright');
if (process.argv[2] !== 'http://localhost:8090') throw new Error('isolated rehearsal only');
const manifest = JSON.parse(readFileSync(new URL('../peek-pages.json', import.meta.url), 'utf8'));
const spec = manifest.pages.find(p => p.slug === manifest.regression);
const data = JSON.parse(readFileSync(new URL(`../peek-data/${spec.slug}.json`, import.meta.url), 'utf8'));
const b = await chromium.launch(process.env.TS_BROWSER_EXECUTABLE ? { executablePath: process.env.TS_BROWSER_EXECUTABLE } : {});
const p = await b.newPage({ viewport: { width: 390, height: 844 }, reducedMotion: 'reduce' });
const errors = [];
p.on('pageerror', e => errors.push(e.message));
let pass = 0, fail = 0;
const ok = (name, value) => { value ? pass++ : fail++; console.log(`${value ? 'PASS' : 'FAIL'} page-data rehearsal: ${name}`); };
try {
  await p.addInitScript(() => localStorage.setItem('ts-pk-tip', 'seen'));
  const response = await p.goto(process.argv[2] + '/' + spec.slug + '/', { waitUntil: 'load' });
  if (!response || response.status() !== 200) throw new Error('INVALID: isolated Thai page did not answer 200');
  await p.waitForTimeout(1400);
  ok('one legacy core; no JSON emitted by inert registration', await p.locator('#ts-peek-nav-runtime').count() === 1 && await p.locator('#ts-peek-nav-data').count() === 0);
  const row = await p.$$eval('.ts-jump a', a => a.map(x => ({ text: x.textContent.trim(), hash: x.hash.slice(1) })));
  ok('same section labels and order', JSON.stringify(row.map(x => x.text)) === JSON.stringify(spec.expectedLabels));
  for (let i = 0; i < row.length; i++) {
    await p.locator('.ts-pk-rail a').nth(i).evaluate(a => a.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true })));
    await p.waitForTimeout(50);
    const got = await p.$$eval('.ts-pk-card .sum.is-gist li', li => li.map(x => x.textContent));
    const want = data.items.filter(x => x[1] === row[i].hash).map(x => x[0]);
    ok('unchanged approved list: ' + row[i].text, JSON.stringify(got) === JSON.stringify(want));
    await p.keyboard.press('Escape');
  }
  ok('no JavaScript errors', errors.length === 0);
} finally { await b.close(); }
console.log(`Peek page-data rehearsal: ${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);

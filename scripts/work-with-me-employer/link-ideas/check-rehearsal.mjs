// Real WordPress copy: Thai deployed core plus unapproved page fixtures injected
// in these browsers only. No page registration or wording is deployed by tests.
// The independent generic browser matrices run together, then the timing-sensitive
// Chromium gesture regression runs alone within the rehearsal's 600-second gate.
import { spawn } from 'node:child_process';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'http://localhost:8090') throw new Error('isolated rehearsal only');
const start = Date.now();
const run = (file, flags) => new Promise(resolve => {
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: 'inherit', env: process.env });
  child.on('error', error => { console.error(error); resolve(1); });
  child.on('exit', code => resolve(code ?? 1));
});
const generic = await Promise.all([
  run('./check-peek-pages.mjs', ['--fixtures']),
  run('./check-peek-pages.mjs', ['--fixtures', '--firefox'])
]);
if (generic.some(code => code !== 0)) process.exit(generic.find(code => code !== 0));
const gestures = await run('./check-peek-thai-regression.mjs', []);
if (gestures !== 0) process.exit(gestures);
console.log(`Peek rehearsal: Chromium gestures and Chromium/Firefox page fixtures passed in ${Math.round((Date.now() - start) / 1000)}s.`);

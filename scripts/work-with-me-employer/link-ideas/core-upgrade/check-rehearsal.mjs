// Run independent read-only matrices concurrently, then the timing-sensitive
// Thai gesture suite alone. Keep all gates inside the 600-second rehearsal cap.
import { spawn } from 'node:child_process';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'http://localhost:8090' || process.argv.length !== 3) throw new Error('isolated localhost:8090 rehearsal only');
const start = Date.now();
const run = (file, flags = []) => new Promise(resolve => {
  const child = spawn(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...flags], { stdio: 'inherit', env: process.env });
  child.on('error', error => { console.error(error); resolve(1); });
  child.on('exit', code => resolve(code ?? 1));
});
const matrices = await Promise.all([
  run('../check-peek-clearance.mjs'), run('../check-peek-clearance.mjs', ['--firefox']),
  run('../check-peek-pages.mjs', ['--fixtures']), run('../check-peek-pages.mjs', ['--fixtures', '--firefox']),
  run('../check-peek-touch.mjs')
]);
if (matrices.some(code => code !== 0)) process.exit(matrices.find(code => code !== 0));
const prior = await run('../check-peek-thai-regression.mjs');
if (prior !== 0) process.exit(prior);
console.log(`INFO Peek core-upgrade rehearsal: clearance in Chromium/Firefox, native Chromium touch, and retained page/Thai gesture checks passed in ${Math.round((Date.now() - start) / 1000)}s.`);

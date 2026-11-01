// Run existing C and D contracts, then the focused E contract, on one isolated copy.
// A failure or hosting challenge stops subsequent suites; no public baseline run.
import { spawnSync } from 'node:child_process';
const base = process.argv[2] || '';
const inline = !process.argv.includes('no-inline');
for (const file of ['../review-c/check-bundle-c.mjs', '../review-d/check-bundle-d.mjs', './check-bundle-e.mjs']) {
  const args = [new URL(file, import.meta.url).pathname, base];
  if (file === './check-bundle-e.mjs' && inline) args.push('inline');
  console.log(`INFO Bundle E rehearsal child ${file}`);
  const child = spawnSync(process.execPath, args, { stdio: 'inherit' });
  if (child.error || child.signal || child.status !== 0) {
    if (child.error) console.error('FAIL rehearsal child: ' + child.error.message);
    if (child.signal) console.error('FAIL rehearsal child signal: ' + child.signal);
    process.exitCode = child.status === 2 ? 2 : 1;
    break;
  }
}

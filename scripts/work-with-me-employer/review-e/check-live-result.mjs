// Read-only release proof: exact native ownership, then the existing E browser contract.
// A native failure or public hosting challenge stops this run; no retry loop.
import { spawnSync } from 'node:child_process';
const base = process.argv[2] || '';
if (process.env.S_TARGET !== 'live' || base !== 'https://thaisolitude.com') {
  throw new Error('This release proof requires S_TARGET=live and the exact HTTPS apex');
}
const run = (command, args) => {
  const child = spawnSync(command, args, { stdio: 'inherit' });
  if (child.error) console.error('FAIL release-proof child: ' + child.error.message);
  if (child.signal) console.error('FAIL release-proof child signal: ' + child.signal);
  return Number.isInteger(child.status) ? child.status : 1;
};
const native = run('bash', [new URL('./inspect-live-result.sh', import.meta.url).pathname]);
if (native !== 0) {
  process.exitCode = native;
} else {
  process.exitCode = run(process.execPath, [new URL('./check-bundle-e.mjs', import.meta.url).pathname, base, 'inline']);
}

// Actual server CSP on the changed Contact iframe, desktop/phone in both engines.
import { spawnSync } from 'node:child_process';
const base = process.argv[2];
if (base !== 'https://staging38.thaisolitude.com') throw new Error('staging38 only');
for (const engine of ['chromium', 'firefox']) {
  const result = spawnSync(process.execPath, [new URL('../tools/csp-test.mjs', import.meta.url).pathname, base, '--check', '--pages=/contact/', '--browser=' + engine], { stdio: 'inherit' });
  if (result.status !== 0) process.exit(result.status || 1);
}

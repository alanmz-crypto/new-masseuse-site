// Both suites run solely against the isolated, applied staging copy.
import { spawnSync } from 'node:child_process';
let failed = false;
for (const file of ['../review-c/check-bundle-c.mjs', './check-bundle-d.mjs']) {
  const result = spawnSync(process.execPath, [new URL(file, import.meta.url).pathname, process.argv[2]], { stdio: 'inherit' });
  if (result.status !== 0) failed = true;
}
process.exitCode = failed ? 1 : 0;

// scripts/ci/rehearse.sh invokes this only on its isolated applied WordPress copy.
// Both browser builds are installed by scripts/ci/install-test-tools.sh.
import { spawnSync } from 'node:child_process';
if (process.argv[2] !== 'http://localhost:8090') throw new Error('Isolated localhost rehearsal only');
for (const engine of ['chromium', 'firefox']) {
  const child = spawnSync(process.execPath, [new URL('./check-reading-progress.mjs', import.meta.url).pathname, ...process.argv.slice(2)], {
    stdio: 'inherit', env: { ...process.env, TS_BROWSER: engine },
  });
  if (child.error || child.signal || child.status !== 0) {
    if (child.error) console.error(child.error.message);
    process.exitCode = child.status === 2 ? 2 : 1;
    break;
  }
}

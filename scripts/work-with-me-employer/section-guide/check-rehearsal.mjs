// scripts/ci/rehearse.sh invokes this only on its isolated applied WordPress copy.
// Both browser builds are installed by scripts/ci/install-test-tools.sh.
import { spawnSync } from 'node:child_process';
if (process.argv[2] !== 'http://localhost:8090') throw new Error('Isolated localhost rehearsal only');
for (const engine of ['chromium', 'firefox']) {
  const args = [new URL('./check-section-guide.mjs', import.meta.url).pathname, process.argv[2]];
  if (engine === 'chromium' && !process.argv.includes('no-inline')) args.push('inline');
  const child = spawnSync(process.execPath, args, {
    stdio: 'inherit', env: { ...process.env, TS_BROWSER: engine },
  });
  if (child.error || child.signal || child.status !== 0) {
    if (child.error) console.error(child.error.message);
    process.exitCode = child.status === 2 ? 2 : 1;
    break;
  }
}

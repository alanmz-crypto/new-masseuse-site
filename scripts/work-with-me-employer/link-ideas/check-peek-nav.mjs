// Entry point: manifest-driven page checks plus the retained detailed Thai
// gesture regression. --fixtures injects only into an isolated localhost copy.
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
const base = process.argv[2] || '';
const flags = process.argv.slice(3);
const run = (file, args) => {
  const r = spawnSync(process.execPath, [fileURLToPath(new URL(file, import.meta.url)), base, ...args], { stdio: 'inherit', env: process.env });
  if (r.status !== 0) process.exit(r.status || 1);
};
run('./check-peek-pages.mjs', flags);
if (!flags.includes('--generic-only') && !flags.includes('--firefox')) run('./check-peek-thai-regression.mjs', []);

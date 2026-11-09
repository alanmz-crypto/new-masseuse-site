// Check the actual approved page on this isolated WordPress copy, at three widths.
import { spawnSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';
const base = process.argv[2];
if (base !== 'http://localhost:8090' || process.argv.length !== 3) throw new Error('isolated rehearsal only');
const result = spawnSync(process.execPath, [
  fileURLToPath(new URL('../../check-peek-pages.mjs', import.meta.url)), base,
  '--enabled-page', 'deep-tissue-massage', '--capture-page', 'deep-tissue-massage'
], { stdio: 'inherit', env: process.env });
if (result.error) console.error(result.error);
process.exit(result.status ?? 1);

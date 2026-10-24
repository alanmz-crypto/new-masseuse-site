// Block-editor validity check for a WordPress page (read-only). Opens the real Gutenberg editor in headless Chromium,
// logs in, and lists every block with isValid === false plus the validator's reason - the same
// "Block contains unexpected or invalid content" an editor sees. Zero invalid blocks = the page opens cleanly for editing.
//
// Usage (localhost only; needs a TEMPORARY admin because the editor needs a login):
//   docker compose run --rm -T wpcli wp user create wwmtmp wwmtmp@example.invalid --role=administrator --user_pass="$PW"
//   printf %s "$PW" > ~/.cache/wwm-scratch/.tmpadmin && chmod 600 ~/.cache/wwm-scratch/.tmpadmin
//   TMPDIR=~/.cache/wwm-scratch/tmp node editor-validate.mjs http://localhost:8090 1165
//   docker compose run --rm -T wpcli wp user delete wwmtmp --yes ; shred -u ~/.cache/wwm-scratch/.tmpadmin
// Lessons (2026-10-15): core/button rejects aria-label on its link (use title); core/image rejects width/height on its img
// (reserve space with CSS aspect-ratio). Raw HTML blocks (core/html) accept anything. Spectra (uagb) blocks are validated by its plugin.
import { createRequire } from 'module';
import fs from 'fs';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const pw = fs.readFileSync('/home/lauer/.cache/wwm-scratch/.tmpadmin', 'utf8').trim();
const base = process.argv[2] || 'http://localhost:8090';
const postId = process.argv[3] || '1165';
const b = await chromium.launch({ executablePath: '/usr/bin/chromium', args: ['--no-sandbox'] });
const ctx = await b.newContext({ viewport: { width: 1440, height: 1000 } });
const p = await ctx.newPage();
await p.goto(base + '/wp-login.php', { waitUntil: 'domcontentloaded' });
await p.fill('#user_login', 'wwmtmp'); await p.fill('#user_pass', pw); await p.click('#wp-submit');
await p.waitForLoadState('networkidle');
console.log('after login url:', p.url());
await p.goto(base + `/wp-admin/post.php?post=${postId}&action=edit`, { waitUntil: 'domcontentloaded' });
await p.waitForFunction(() => window.wp && wp.data && wp.data.select('core/block-editor') && wp.data.select('core/block-editor').getBlocks().length > 0, null, { timeout: 90000 });
await p.waitForTimeout(3000);
const res = await p.evaluate(() => {
  const out = []; let total = 0;
  const walk = (blocks, path) => blocks.forEach((bl, i) => {
    total++;
    if (!bl.isValid) out.push({ path: path + '/' + i, name: bl.name, className: bl.attributes && bl.attributes.className, url: bl.attributes && bl.attributes.url, issues: (bl.validationIssues || []).map(x => ({ code: x.code, message: String(x.args ? x.args.map(a => typeof a === 'object' ? JSON.stringify(a).slice(0, 300) : String(a).slice(0, 300)).join(' | ') : x.message || '').slice(0, 700) })), original: (bl.originalContent || '').slice(0, 600) });
    if (bl.innerBlocks && bl.innerBlocks.length) walk(bl.innerBlocks, path + '/' + i);
  });
  walk(wp.data.select('core/block-editor').getBlocks(), '');
  return { total, invalid: out, wpVersion: (window.wp && wp.version) || null };
});
console.log(JSON.stringify(res, null, 1));
fs.writeFileSync('/home/lauer/.cache/wwm-scratch/editor-result-' + postId + '.json', JSON.stringify(res, null, 1));
await b.close();

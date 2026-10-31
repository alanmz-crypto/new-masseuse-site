# Site op `chair-apply-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `chair-massage/push-chair-massage.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `7cedc3f19e88c6c3962853c47917b17e71c811f8` on `feat/2026-10-30-chair-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41171014436 |
| started | 2026-10-31 05:03:21 UTC |

## push-chair-massage.sh --apply(S_TARGET=staging38): FAIL (exit 1)

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-chair-massage (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-chair-massage-20261031-050328.sql (5017909 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready pictures=absent page=absent services_row=absent strip_snippet=ready jump_snippet=ready hero=media#1269
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-chair-massage-20261031-050328.sql -> offsite-2026-10/staging38-before-chair-massage-20261031-050328.sql.age (5019325 bytes, read back identical; plaintext sha256 1ef11035543c...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 0 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Added page #1294 (/chair-massage/), pictures #1290, #1291, #1292, #1293 (hero = media #1269), the Services row, and 'chair-massage' in snippets #1268 and #1232. Before-snapshot: /home/<ssh-user>/private-backups/chair-massage-before-20261031-050339.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging38 is www/staging38.thaisolitude.com/public_html
  200 /chair-massage/
CHECK FAILED: chair should contain '<nav aria-label="On this page" class="ts-jump">' exactly 1 time(s), has 0 (cache?); use --rollback if it persists.
```

**RESULT: FAIL** - push-chair-massage.sh failed (exit 1)

_finished 2026-10-31 05:03:47 UTC_

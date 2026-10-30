# Site op `seo-fixes-apply-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `seo-fixes/push-seo-fixes.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `09b9af46933207c318d303bde366e146e88f032d` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41120018518 |
| started | 2026-10-30 20:02:02 UTC |

## push-seo-fixes.sh --apply(S_TARGET=staging38): FAIL (exit 1)

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-seo-fixes (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-seo-fixes-20261030-200211.sql (4997705 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready services=ready corporate=ready picture=media#1269
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-seo-fixes-20261030-200211.sql -> offsite-2026-10/staging38-before-seo-fixes-20261030-200211.sql.age (4999121 bytes, read back identical; plaintext sha256 73e509f447e3...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 0 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Services description now says "in Portland"; the corporate page has its share picture (media #1269, attached once, no copy made). Before-snapshot: /home/<ssh-user>/private-backups/seo-fixes-before-20261030-200226.json
SiteGround cache purged
[4b] Does the public page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
public check: https://staging38.thaisolitude.com/services/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   Services: the description says 'in Portland' in the meta tag and its social copies (3 or more) (1)
  ok   Services: no 'Mt. Tabor' left anywhere in the page (0)
  ok   Services: title unchanged (1)
  ok   Services: announcement strip still there (1)
public check: https://staging38.thaisolitude.com/corporate-massage-portland/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   Corporate: one og:image tag (1)
  FAIL Corporate: og:image is the corporate illustration: want 1, got 4
  ok   Corporate: title unchanged (1)
  ok   Corporate: no strip on the page itself (0)
  ok   Corporate: bookings date unchanged on the page (1)
  info img tags on the corporate page: 3 (compare with the scan before: the picture must not appear in the page body)
The public pages do not show what was applied (cache?); use --rollback if it persists.
```

**RESULT: FAIL** - push-seo-fixes.sh failed (exit 1)

_finished 2026-10-30 20:02:34 UTC_

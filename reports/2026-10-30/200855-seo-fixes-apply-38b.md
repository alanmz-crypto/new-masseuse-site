# Site op `seo-fixes-apply-38b`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `seo-fixes/push-seo-fixes.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `153ec8bbde22dfb505623478aad3ec67f3403362` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41120875804 |
| started | 2026-10-30 20:08:27 UTC |

## push-seo-fixes.sh --apply(S_TARGET=staging38): FAIL (exit 1)

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-seo-fixes (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-seo-fixes-20261030-200834.sql (4997707 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready services=ready corporate=ready picture=media#1269
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: seo-fixes-before-20261030-200226.json -> offsite-2026-10/seo-fixes-before-20261030-200226.json.age (3584 bytes, read back identical; plaintext sha256 af6bade63343...)
off-site: staging38-before-seo-fixes-20261030-200834.sql -> offsite-2026-10/staging38-before-seo-fixes-20261030-200834.sql.age (4999123 bytes, read back identical; plaintext sha256 267ad778847f...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-10, 1 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Services description now says "in Portland"; the corporate page has its share picture (media #1269, set once in SureRank, no copy made, not drawn on the page). Before-snapshot: /home/<ssh-user>/private-backups/seo-fixes-before-20261030-200848.json
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
  FAIL Corporate: the og:image tag points at an uploads picture of this site: want 1, got 0
  ok   Corporate: the og:image is the corporate illustration (1)
  ok   Corporate: the Twitter image follows it (1)
  ok   Corporate: title unchanged (1)
  ok   Corporate: no strip on the page itself (0)
  ok   Corporate: bookings date unchanged on the page (1)
  info img tags on the corporate page: 2 (was 3 in the first try because a featured image is also drawn on the page; the scan before says how many it must stay at)
The public pages do not show what was applied (cache?); use --rollback if it persists.
```

**RESULT: FAIL** - push-seo-fixes.sh failed (exit 1)

_finished 2026-10-30 20:08:55 UTC_

# Site op `review-e-apply-staging38-20261101`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `review-e/push-bundle-e.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `1612df48fc24d306cce6fdca56866f5d05c537f9` on `codex/2026-11-01-review-bundle-e` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41303403905 |
| started | 2026-11-01 11:47:09 UTC |

## push-bundle-e.sh --apply(S_TARGET=staging38): FAIL (exit 1)

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-bundle-e (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-bundle-e-20261101-114718.sql (6982630 bytes)
page home=ready
page therapeutic-massage=ready
new snippet SiteReviewPerformance=ready
new snippet SiteReviewEmoji=ready
prepared WebP=ready to import once; originals=10 preserved
host=http://staging38.thaisolitude.com state=ready
Success: Preflight ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-bundle-e-20261101-114718.sql -> offsite-2026-11/staging38-before-bundle-e-20261101-114718.sql.age (6984526 bytes, read back identical; plaintext sha256 e87d5ed82f39...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 6 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
page home=ready
page therapeutic-massage=ready
new snippet SiteReviewPerformance=ready
new snippet SiteReviewEmoji=ready
prepared WebP=ready to import once; originals=10 preserved
host=http://staging38.thaisolitude.com state=ready
Error: Generated media dimensions/file metadata incomplete.; old stores restored and only owned resources removed.
```

**RESULT: FAIL** - push-bundle-e.sh failed (exit 1)

_finished 2026-11-01 11:47:34 UTC_

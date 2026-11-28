# Site op `live-corporate-band-anchorfix-20261128-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `corporate-band/push-corporate-band-live.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `0dd328972fdd39680ee409fb0bcdfb7ae8af2d0e` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/46122300007 |
| started | 2026-11-28 07:40:52 UTC |

## push-corporate-band-live.sh --apply(S_TARGET=live): FAIL (exit 1)

```text
Live is www/thaisolitude.com/public_html
Success: host=http://thaisolitude.com state=ready blog_public=1 snippet=#1268 code_sha=537c9a7be28cac1574c9b597f850008a66a7c7be4bb818cf1b69620f9d678363 css=#29 css_sha=7a14b1ff2fff98be296f4d9a4ab34a8669cb95a58eee7248fce4427ec97f064e cache_fallback=no expected_new_code_sha=7298fc1977c11e46a5aa2d9ad4d70f4dc59d0b7049b1b10eda88b2e04f026c7c expected_new_css_sha=2870307aa8248d06166b2bd7dfc8652cc3dae2cad859bdc718e530b593a1d4f5
live is www/thaisolitude.com/public_html; fresh database export: private-backups/live-manual-20261128-074122.sql (4006404 bytes)
Copying the 13 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 13 file(s), identical sha256 on the server and in the copy.
off-site: corporate-band-staging43-before-20261128-043927.json -> offsite-2026-11/corporate-band-staging43-before-20261128-043927.json.age (2654 bytes, read back identical; plaintext sha256 85b625061a0b...)
off-site: corporate-band-staging43-before-20261128-044705.json -> offsite-2026-11/corporate-band-staging43-before-20261128-044705.json.age (2266 bytes, read back identical; plaintext sha256 3262b5eff57a...)
off-site: corporate-band-staging43-before-20261128-045022.json -> offsite-2026-11/corporate-band-staging43-before-20261128-045022.json.age (2624 bytes, read back identical; plaintext sha256 0e2ae34093a0...)
off-site: corporate-band-staging43-before-20261128-045457.json -> offsite-2026-11/corporate-band-staging43-before-20261128-045457.json.age (2415 bytes, read back identical; plaintext sha256 2484b5830aae...)
off-site: corporate-band-staging43-before-20261128-050051.json -> offsite-2026-11/corporate-band-staging43-before-20261128-050051.json.age (2400 bytes, read back identical; plaintext sha256 19d89fce34aa...)
off-site: live-manual-20261128-060320.sql -> offsite-2026-11/live-manual-20261128-060320.sql.age (4010832 bytes, read back identical; plaintext sha256 c889e7d966c2...)
off-site: live-manual-20261128-074122.sql -> offsite-2026-11/live-manual-20261128-074122.sql.age (4007580 bytes, read back identical; plaintext sha256 109dda5b2611...)
off-site: staging43-manual-20261128-043037.sql -> offsite-2026-11/staging43-manual-20261128-043037.sql.age (4084382 bytes, read back identical; plaintext sha256 ae12000ea7e2...)
off-site: staging43-manual-20261128-043909.sql -> offsite-2026-11/staging43-manual-20261128-043909.sql.age (4084382 bytes, read back identical; plaintext sha256 d8cc9fcc0ac6...)
off-site: staging43-manual-20261128-044643.sql -> offsite-2026-11/staging43-manual-20261128-044643.sql.age (4083974 bytes, read back identical; plaintext sha256 5361522cc4e7...)
off-site: staging43-manual-20261128-045004.sql -> offsite-2026-11/staging43-manual-20261128-045004.sql.age (4084315 bytes, read back identical; plaintext sha256 b32cf149821b...)
off-site: staging43-manual-20261128-045440.sql -> offsite-2026-11/staging43-manual-20261128-045440.sql.age (4084124 bytes, read back identical; plaintext sha256 c5820b307e7e...)
off-site: staging43-manual-20261128-050034.sql -> offsite-2026-11/staging43-manual-20261128-050034.sql.age (4084109 bytes, read back identical; plaintext sha256 9c7fddd7e8dc...)
OFF-SITE VERIFIED: 13 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Live is www/thaisolitude.com/public_html
Success: Applied the reviewed booking notice, solid link underline, and refreshed WPCode cache. blog_public remains 1. Rollback snapshot: /home/<ssh-user>/private-backups/corporate-band-live-before-20261128-074154.json
SiteGround cache purged
Copying the 14 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 14 file(s), identical sha256 on the server and in the copy.
off-site: corporate-band-live-before-20261128-074154.json -> offsite-2026-11/corporate-band-live-before-20261128-074154.json.age (75231 bytes, read back identical; plaintext sha256 384b6cacee1e...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 13 already there.

LIVE BOOKING NOTICE CHECK: FAIL
  - https://thaisolitude.com/personal-automation/terms/: noindex found in HTML or response headers
  - https://thaisolitude.com/personal-automation/privacy/: noindex found in HTML or response headers
  - https://thaisolitude.com/personal-automation/: noindex found in HTML or response headers
Live published pages discovered through REST: 16
PASS page /winding-willow/
PASS page /chair-massage/
PASS page /corporate-massage-portland/
PASS page /accessibility/
PASS page /privacy-policy/
PASS page /work-with-me/
PASS page /thai-massage/
PASS page /therapeutic-massage/
PASS page /deep-tissue-massage/
PASS page /contact/
PASS page /services/
PASS page /about/
PASS page /home/
INFO no published posts available for a post-route sample
PASS internal destination /winding-willow/: HTTP 200
PASS internal destination /corporate-massage-portland/: HTTP 200
PASS live sitemap: HTTP 200
PASS category archive /category/general/
PASS representative 404 route
Public verification failed. The update remains applied; inspect first, then rollback with:
bash /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/corporate-band/push-corporate-band-live.sh --rollback '/home/<ssh-user>/private-backups/corporate-band-live-before-20261128-074154.json'
```

**RESULT: FAIL** - push-corporate-band-live.sh failed (exit 1)

_finished 2026-11-28 07:42:12 UTC_

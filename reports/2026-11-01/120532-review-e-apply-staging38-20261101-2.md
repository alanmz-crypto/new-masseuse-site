# Site op `review-e-apply-staging38-20261101-2`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `review-e/push-bundle-e.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `2d0eebc480c7fa8d95cd55cf35bf6abca822b0db` on `codex/2026-11-01-review-bundle-e` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41304509089 |
| started | 2026-11-01 12:01:56 UTC |

## push-bundle-e.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-bundle-e (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-bundle-e-20261101-120203.sql (6713520 bytes)
page home=ready
page therapeutic-massage=ready
new snippet SiteReviewPerformance=ready
new snippet SiteReviewEmoji=ready
prepared WebP=ready to import once; originals=10 preserved
host=http://staging38.thaisolitude.com state=ready
Success: Preflight ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 9 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 9 file(s), identical sha256 on the server and in the copy.
off-site: bundle-e-before-20261101-114734.json -> offsite-2026-11/bundle-e-before-20261101-114734.json.age (158125 bytes, read back identical; plaintext sha256 d105d8118c2d...)
off-site: staging38-before-bundle-e-20261101-120203.sql -> offsite-2026-11/staging38-before-bundle-e-20261101-120203.sql.age (6715352 bytes, read back identical; plaintext sha256 f8324db828f6...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-11, 7 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
page home=ready
page therapeutic-massage=ready
new snippet SiteReviewPerformance=ready
new snippet SiteReviewEmoji=ready
prepared WebP=ready to import once; originals=10 preserved
host=http://staging38.thaisolitude.com state=ready
Success: Applied Bundle E. Before-snapshot: /home/<ssh-user>/private-backups/bundle-e-before-20261101-120216.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 10 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 10 file(s), identical sha256 on the server and in the copy.
off-site: bundle-e-before-20261101-120216.json -> offsite-2026-11/bundle-e-before-20261101-120216.json.age (320618 bytes, read back identical; plaintext sha256 2965d5b777b2...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 9 already there.
[6] Checking rendered copy from the server origin:
staging38 is www/staging38.thaisolitude.com/public_html
PASS home HTTP 200
PASS home two exact anonymous WOFF2 preloads
PASS home emoji settings/loader/styles absent
PASS home image dimensions 2026/06/ComfyUI_01040_curves.webp
PASS home responsive canonical image 2026/06/ComfyUI_01040_curves.webp
PASS home image dimensions 2026/06/ComfyUI_01044_.webp
PASS home responsive canonical image 2026/06/ComfyUI_01044_.webp
PASS home image dimensions 2026/06/00illaCNN512Curve.webp
PASS home responsive canonical image 2026/06/00illaCNN512Curve.webp
PASS home image dimensions 2026/10/corporate-group-massage.webp
PASS services HTTP 200
PASS services two exact anonymous WOFF2 preloads
PASS services emoji settings/loader/styles absent
PASS services image dimensions 2026/10/corporate-group-massage.webp
PASS work-with-me HTTP 200
PASS work-with-me two exact anonymous WOFF2 preloads
PASS work-with-me emoji settings/loader/styles absent
PASS work-with-me image dimensions 2026/09/butsaba_homepage.webp
PASS work-with-me image dimensions 2026/10/thai-massage-therapy-session-cutout.webp
PASS therapeutic-massage HTTP 200
PASS therapeutic-massage two exact anonymous WOFF2 preloads
PASS therapeutic-massage emoji settings/loader/styles absent
PASS therapeutic-massage image dimensions 2026/11/therapeutic-red-400.webp
PASS Therapeutic no old PNG reference
PASS WebP delivered with correct status/type
PASS WebP delivered exact prepared bytes
Bundle E: 26 origin assertions, 0 failures
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-01 12:03 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  Thai evidence content matches its builder
PASS  Therapeutic evidence content matches its builder
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Review Bundle A exact fragments and new wording match their builder
PASS  Review Bundle B source values and approved wording match their builder
PASS  Review Bundle C frozen fragments and runtime sources match their builder
PASS  Review Bundle D frozen copy and research fragments match their builder
PASS  Review Bundle E image fragments, prepared WebP and runtime sources match their builder
PASS  verify-home.py https://staging38.thaisolitude.com
PASS  verify-client-pages.py https://staging38.thaisolitude.com
PASS  posture-check.py https://staging38.thaisolitude.com

ALL GATES PASSED
  -> PASS

[2] pages answer 200
  200 /
  200 /about/
  200 /services/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /wp-sitemap.xml
  200 /robots.txt
  -> PASS

[3] structured data vs the reference
items: candidate 27, reference 23 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /services/, /thai-massage/
DIFFERENT:
  /: candidate has 6 items, reference 5
  /contact/: candidate has 7 items, reference 6
  /services/: candidate has 7 items, reference 6
  /thai-massage/: candidate has 7 items, reference 6
  -> FAIL

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261101-120307/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261101-120307/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2193), candidate (1280, 2572))
  DIFF about-m.png: page height/width differs (reference (390, 2800), candidate (390, 3176))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1627), candidate (1280, 1680))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2635), candidate (390, 2766))
  DIFF contact-d.png: page height/width differs (reference (1280, 2713), candidate (1280, 2990))
  DIFF contact-m.png: page height/width differs (reference (390, 2679), candidate (390, 2993))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5147), candidate (1280, 5674))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8781), candidate (390, 9418))
  DIFF home-d.png: page height/width differs (reference (1280, 4570), candidate (1280, 4624))
  DIFF home-m.png: page height/width differs (reference (390, 5923), candidate (390, 5929))
  DIFF privacy-policy-d.png: 0.0071% of pixels differ, in box x 51-1039, y 447-1314
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2228), candidate (390, 2198))
  DIFF services-d.png: page height/width differs (reference (1280, 3552), candidate (1280, 3906))
  DIFF services-m.png: page height/width differs (reference (390, 5702), candidate (390, 6098))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5328), candidate (1280, 5774))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9102), candidate (390, 9564))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5702), candidate (1280, 6426))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9585), candidate (390, 10367))
  DIFF work-with-me-d.png: 0.0795% of pixels differ, in box x 176-1039, y 1229-5954
  DIFF work-with-me-m.png: 0.1601% of pixels differ, in box x 67-318, y 1698-7824
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging38-20261101-120307)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

2 CHECK(S) FAILED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-bundle-e-20261101-120203.sql    modified 2026-11-01 12:02:03
Unable to find image 'mariadb:10.6@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16' locally
docker.io/library/mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16: Pulling from library/mariadb
20c3783cc497: Pulling fs layer
82b15426938b: Pulling fs layer
b2a602564ec4: Pulling fs layer
bdc186c1268d: Pulling fs layer
5b0e31685a7d: Pulling fs layer
5d5076a693b9: Pulling fs layer
650d43fe7026: Pulling fs layer
4f693d0ae952: Pulling fs layer
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
bdc186c1268d: Waiting
5b0e31685a7d: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
4f693d0ae952: Verifying Checksum
4f693d0ae952: Download complete
5d5076a693b9: Verifying Checksum
5d5076a693b9: Download complete
20c3783cc497: Pull complete
82b15426938b: Pull complete
b2a602564ec4: Pull complete
bdc186c1268d: Pull complete
5b0e31685a7d: Pull complete
5d5076a693b9: Pull complete
650d43fe7026: Pull complete
4f693d0ae952: Pull complete
Digest: sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
Status: Downloaded newer image for mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
import exit code: 0
fingerprints: restored copy 92, real site 94
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 24
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_term_relationships
> rows	iej_term_relationships
< page	home
> page	home
< page	therapeutic-massage
> page	therapeutic-massage
> snippet	1342
> snippet	1343
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-bundle-e.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-01 12:05:32 UTC_

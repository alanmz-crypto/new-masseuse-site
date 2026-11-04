# Site op `live-reading-progress-20261104-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `reading-progress-sitewide/push-reading-progress-sitewide.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `1efeb7e0a437f6d8682509c9545ab4e4efdbee6b` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41796118623 |
| started | 2026-11-04 08:00:00 UTC |

## push-reading-progress-sitewide.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-reading-progress-sitewide (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-reading-progress-sitewide-20261104-080010.sql (3916951 bytes)
Success: Sitewide reading progress: ready-empty; CSS bytes=65418; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 5 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 5 file(s), identical sha256 on the server and in the copy.
off-site: live-before-reading-progress-sitewide-20261104-080010.sql -> offsite-2026-11/live-before-reading-progress-sitewide-20261104-080010.sql.age (3918095 bytes, read back identical; plaintext sha256 3b30badd45f1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 4 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Sitewide reading progress applied. Before-snapshot: /home/customer/private-backups/reading-progress-sitewide-before-20261104-080026-19484792.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 6 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 6 file(s), identical sha256 on the server and in the copy.
off-site: reading-progress-sitewide-before-20261104-080026-19484792.json -> offsite-2026-11/reading-progress-sitewide-before-20261104-080026-19484792.json.age (67824 bytes, read back identical; plaintext sha256 f9c2d44651a9...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 5 already there.
[6] Does the public page show the change?
live is www/thaisolitude.com/public_html
public check: / HTTP 200; sitewide CSS and licence present
public check: /about/ HTTP 200; sitewide CSS and licence present
public check: /services/ HTTP 200; sitewide CSS and licence present
public check: /thai-massage/ HTTP 200; sitewide CSS and licence present
public check: /therapeutic-massage/ HTTP 200; sitewide CSS and licence present
public check: /deep-tissue-massage/ HTTP 200; sitewide CSS and licence present
public check: /chair-massage/ HTTP 200; sitewide CSS and licence present
public check: /corporate-massage-portland/ HTTP 200; sitewide CSS and licence present
public check: /work-with-me/ HTTP 200; sitewide CSS and licence present
public check: /contact/ HTTP 200; sitewide CSS and licence present
public check: /privacy-policy/ HTTP 200; sitewide CSS and licence present
public check: /accessibility/ HTTP 200; sitewide CSS and licence present
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-11-04 08:01 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

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
PASS  verify-home.py https://thaisolitude.com
PASS  verify-client-pages.py https://thaisolitude.com
PASS  posture-check.py https://thaisolitude.com

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
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261104-080127/reference
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261104-080127/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2116), candidate (1280, 2572))
  DIFF about-m.png: page height/width differs (reference (390, 2639), candidate (390, 3176))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1549), candidate (1280, 1680))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2473), candidate (390, 2766))
  DIFF contact-d.png: page height/width differs (reference (1280, 2635), candidate (1280, 2990))
  DIFF contact-m.png: page height/width differs (reference (390, 2518), candidate (390, 2993))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5069), candidate (1280, 5674))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8620), candidate (390, 9418))
  DIFF home-d.png: page height/width differs (reference (1280, 4163), candidate (1280, 4624))
  DIFF home-m.png: page height/width differs (reference (390, 5254), candidate (390, 5929))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1327), candidate (1280, 1405))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2067), candidate (390, 2198))
  DIFF services-d.png: page height/width differs (reference (1280, 2949), candidate (1280, 3906))
  DIFF services-m.png: page height/width differs (reference (390, 4463), candidate (390, 6098))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5250), candidate (1280, 5774))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 8940), candidate (390, 9564))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5624), candidate (1280, 6426))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9424), candidate (390, 10367))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 5990), candidate (1280, 6045))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7801), candidate (390, 7981))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261104-080127)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

2 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-reading-progress-sitewide-20261104-080010.sql    modified 2026-11-04 08:00:11
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
bdc186c1268d: Waiting
5b0e31685a7d: Waiting
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Download complete
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
fingerprints: restored copy 94, real site 94
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 24
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< css	29
> css	29
```

**RESULT: PASS** - push-reading-progress-sitewide.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-04 08:04:19 UTC_

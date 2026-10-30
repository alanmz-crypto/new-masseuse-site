# Site op `booking-date-apply-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `booking-date/push-booking-date.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `af818f54f7a51dbb0020311f902a2b3f1b1878a2` on `feat/2026-10-30-nov14` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41078880142 |
| started | 2026-10-30 15:14:50 UTC |

## push-booking-date.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-booking-date (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-booking-date-20261030-151457.sql (4936991 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready replacements ready=8 applied=0 changed=0; strip snippet=ready
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-booking-date-20261030-151457.sql -> offsite-2026-10/staging38-before-booking-date-20261030-151457.sql.age (4938391 bytes, read back identical; plaintext sha256 7341188f02f2...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 6 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Applied 8 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /home/<ssh-user>/private-backups/booking-date-before-20261030-151511.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging38 is www/staging38.thaisolitude.com/public_html
  200 /
  200 /about/
  200 /services/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /corporate-massage-portland/
public check: every page that said bookings begin November 1 now says November 14 (strip on 9 pages, three FAQ pairs, corporate page, Work With Me); the employment dates and For Practices are unchanged
[5] Copying the new before-snapshot to this computer:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: booking-date-before-20261030-151511.json -> offsite-2026-10/booking-date-before-20261030-151511.json.age (250020 bytes, read back identical; plaintext sha256 eaf2420f85ed...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 7 already there.
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-30 15:16 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  ruff (syntax and undefined names)
PASS  staging installers match their localhost originals; no production host in any allow-list
PASS  structured-data snippet and guard (offline test)
PASS  headers snippet and CSP (offline test)
PASS  posture check self-test (mock site and deliberate breaks)
PASS  wording tiers self-test (deliberate breaks, mutation-checked)
PASS  Thai evidence content matches its builder
PASS  Therapeutic evidence content matches its builder
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Booking date content matches its builder
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
items: candidate 23, reference 23 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261030-151607/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261030-151607/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: 0.2105% of pixels differ, in box x 348-964, y 124-155
  DIFF about-m.png: 0.1618% of pixels differ, in box x 78-323, y 130-140
  DIFF accessibility-d.png: 0.2838% of pixels differ, in box x 348-964, y 124-155
  DIFF accessibility-m.png: 0.1719% of pixels differ, in box x 78-323, y 130-140
  DIFF contact-d.png: 0.1702% of pixels differ, in box x 348-964, y 124-155
  DIFF contact-m.png: 0.1691% of pixels differ, in box x 78-323, y 130-140
  DIFF deep-tissue-massage-d.png: 0.0897% of pixels differ, in box x 348-964, y 124-155
  DIFF deep-tissue-massage-m.png: 0.0516% of pixels differ, in box x 78-323, y 130-140
  DIFF home-d.png: 0.1010% of pixels differ, in box x 348-964, y 124-155
  DIFF home-m.png: 0.0765% of pixels differ, in box x 78-323, y 130-140
  DIFF privacy-policy-d.png: 0.3286% of pixels differ, in box x 348-964, y 124-155
  DIFF privacy-policy-m.png: 0.2034% of pixels differ, in box x 78-323, y 130-140
  DIFF services-d.png: 0.1412% of pixels differ, in box x 348-964, y 124-155
  DIFF services-m.png: 0.0877% of pixels differ, in box x 78-323, y 130-140
  DIFF thai-massage-d.png: 0.0867% of pixels differ, in box x 348-964, y 124-155
  DIFF thai-massage-m.png: 0.0498% of pixels differ, in box x 78-323, y 130-140
  DIFF therapeutic-massage-d.png: 0.0810% of pixels differ, in box x 348-964, y 124-155
  DIFF therapeutic-massage-m.png: 0.0473% of pixels differ, in box x 78-323, y 130-140
  DIFF work-with-me-d.png: 0.0848% of pixels differ, in box x 174-910, y 5465-5480
  DIFF work-with-me-m.png: 0.0733% of pixels differ, in box x 68-320, y 7074-7112
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging38-20261030-151607)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-booking-date-20261030-151457.sql    modified 2026-10-30 15:14:57
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
5b0e31685a7d: Verifying Checksum
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
fingerprints: restored copy 85, real site 85
compared: css 1, option 12, page 14, rows 21, seo 17, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< page	corporate-massage-portland
< page	deep-tissue-massage
> page	corporate-massage-portland
> page	deep-tissue-massage
< page	thai-massage
< page	therapeutic-massage
< page	work-with-me
> page	thai-massage
> page	therapeutic-massage
> page	work-with-me
< snippet	1268
> snippet	1268
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-booking-date.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-30 15:18:38 UTC_

# Site op `review-a-apply-staging38-20261101`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `review-a/push-bundle-a.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `6e5acb4df9820220c615394f1623f50d73b880d5` on `codex/2026-11-01-review-bundle-a` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41264068801 |
| started | 2026-11-01 02:10:31 UTC |

## push-bundle-a.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-bundle-a (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-bundle-a-20261101-021038.sql (5392194 bytes)
home: Therapeutic massage card button=ready
home: Deep tissue massage card button=ready
home: Thai massage card button=ready
home: contact/ root-relative JSON=ready
home: contact/ root-relative anchor=ready
home: about/ root-relative JSON=ready
home: about/ root-relative anchor=ready
thai-massage: other services before research=ready
thai-massage: closing next step=ready
therapeutic-massage: other services before research=ready
therapeutic-massage: closing next step=ready
deep-tissue-massage: other services before research=ready
deep-tissue-massage: closing next step=ready
chair-massage: other services before research=ready
chair-massage: closing next step=ready
chair-massage: three corporate FAQ links=ready
services: chair and corporate rows first=ready
services: Your Session availability=ready
services: closing next step=ready
corporate-massage-portland: chair reading link=ready
corporate-massage-portland: mobile quote shortcuts=ready
contact: corporate quote card after Address=ready
about: licence and next step=ready
A11yPageJumpLinks=ready sha256=1c7a3c5e97905d57ce61d0eeb16a7874349baf0b95693e21335445edaf865b22
Additional CSS=ready
host=http://staging38.thaisolitude.com state=ready
Success: Preflight: ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: created draft release offsite-2026-11
off-site: staging38-before-bundle-a-20261101-021038.sql -> offsite-2026-11/staging38-before-bundle-a-20261101-021038.sql.age (5393706 bytes, read back identical; plaintext sha256 84bfc8dd2dc4...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
home: Therapeutic massage card button=ready
home: Deep tissue massage card button=ready
home: Thai massage card button=ready
home: contact/ root-relative JSON=ready
home: contact/ root-relative anchor=ready
home: about/ root-relative JSON=ready
home: about/ root-relative anchor=ready
thai-massage: other services before research=ready
thai-massage: closing next step=ready
therapeutic-massage: other services before research=ready
therapeutic-massage: closing next step=ready
deep-tissue-massage: other services before research=ready
deep-tissue-massage: closing next step=ready
chair-massage: other services before research=ready
chair-massage: closing next step=ready
chair-massage: three corporate FAQ links=ready
services: chair and corporate rows first=ready
services: Your Session availability=ready
services: closing next step=ready
corporate-massage-portland: chair reading link=ready
corporate-massage-portland: mobile quote shortcuts=ready
contact: corporate quote card after Address=ready
about: licence and next step=ready
A11yPageJumpLinks=ready sha256=1c7a3c5e97905d57ce61d0eeb16a7874349baf0b95693e21335445edaf865b22
Additional CSS=ready
host=http://staging38.thaisolitude.com state=ready
Success: Applied Bundle A to nine pages, jump snippet and Additional CSS. Before-snapshot: /home/<ssh-user>/private-backups/bundle-a-before-20261101-021051.json
SiteGround cache purged
[4b] Does the public page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
public check: home HTTP 200; expected Bundle A content checked
public check: about HTTP 200; expected Bundle A content checked
public check: services HTTP 200; expected Bundle A content checked
public check: thai-massage HTTP 200; expected Bundle A content checked
public check: therapeutic-massage HTTP 200; expected Bundle A content checked
public check: deep-tissue-massage HTTP 200; expected Bundle A content checked
public check: chair-massage HTTP 200; expected Bundle A content checked
public check: corporate-massage-portland HTTP 200; expected Bundle A content checked
public check: contact HTTP 200; expected Bundle A content checked
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: bundle-a-before-20261101-021051.json -> offsite-2026-11/bundle-a-before-20261101-021051.json.age (1033733 bytes, read back identical; plaintext sha256 2ba87d9eaae4...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-01 02:12 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
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
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Review Bundle A exact fragments and new wording match their builder
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
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /services/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261101-021205/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261101-021205/candidate
page-views compared: 20 | pixel-identical: 6 | different: 14 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2193), candidate (1280, 2545))
  DIFF about-m.png: page height/width differs (reference (390, 2800), candidate (390, 3173))
  DIFF contact-d.png: page height/width differs (reference (1280, 2713), candidate (1280, 2990))
  DIFF contact-m.png: page height/width differs (reference (390, 2679), candidate (390, 3045))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5147), candidate (1280, 5567))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8781), candidate (390, 9241))
  DIFF home-d.png: 0.0711% of pixels differ, in box x 729-823, y 2617-3302
  DIFF home-m.png: 0.1134% of pixels differ, in box x 47-173, y 3057-4101
  DIFF services-d.png: page height/width differs (reference (1280, 3552), candidate (1280, 3906))
  DIFF services-m.png: page height/width differs (reference (390, 5702), candidate (390, 6149))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5328), candidate (1280, 5747))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9102), candidate (390, 9561))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5702), candidate (1280, 6132))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9585), candidate (390, 10055))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging38-20261101-021205)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-bundle-a-20261101-021038.sql    modified 2026-11-01 02:10:39
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
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
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
fingerprints: restored copy 88, real site 88
compared: css 1, option 12, page 15, rows 21, seo 19, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< page	about
> page	about
< page	chair-massage
< page	contact
< page	corporate-massage-portland
< page	deep-tissue-massage
< page	home
> page	chair-massage
> page	contact
> page	corporate-massage-portland
> page	deep-tissue-massage
> page	home
< page	services
> page	services
< page	thai-massage
< page	therapeutic-massage
> page	thai-massage
> page	therapeutic-massage
< css	29
> css	29
< snippet	1232
> snippet	1232
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-bundle-a.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-01 02:14:34 UTC_

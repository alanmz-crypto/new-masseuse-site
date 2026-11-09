# Site op `peek-title-rails-apply-20261109-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-nav.sh` `core-title-rail --apply` |
| lane | ops (site-ops.yml) |
| commit | `267c93e94f2b81ae63843be8f2e806805a061bd3` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42666333809 |
| started | 2026-11-09 17:37:03 UTC |

## push-peek-nav.sh core-title-rail --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-peek-nav (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-peek-nav-20261109-173723.sql (4497790 bytes)
Success: core-title-rail ready; nothing written.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-peek-nav-20261109-173723.sql -> offsite-2026-11/staging39-before-peek-nav-20261109-173723.sql.age (4499078 bytes, read back identical; plaintext sha256 6bcb28a1f60b...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Published page paths match reviewed title-strip browser coverage.
Success: Peek core-title-rail applied; WordPress storage only. Before-snapshot: /home/customer/private-backups/peek-template-core-title-rail-before-20261109-173737-cae68f35.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: peek-template-core-title-rail-before-20261109-173737-cae68f35.json -> offsite-2026-11/peek-template-core-title-rail-before-20261109-173737-cae68f35.json.age (210597 bytes, read back identical; plaintext sha256 d236a7d633bd...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
[6] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /
  ok   page-296: 1 x id="ts-peek-nav-css"
  ok   page-296: 1 x id="ts-peek-nav-runtime"
  ok   page-296: 1 x id="ts-peek-nav-config"
  ok   page-296: 0 x id="ts-peek-nav-data"
  ok   page-296: 0 x ts-pill-nav
  200 /about/
  ok   page-298: 1 x id="ts-peek-nav-css"
  ok   page-298: 1 x id="ts-peek-nav-runtime"
  ok   page-298: 1 x id="ts-peek-nav-config"
  ok   page-298: 0 x id="ts-peek-nav-data"
  ok   page-298: 0 x ts-pill-nav
  200 /accessibility/
  ok   page-1230: 1 x id="ts-peek-nav-css"
  ok   page-1230: 1 x id="ts-peek-nav-runtime"
  ok   page-1230: 1 x id="ts-peek-nav-config"
  ok   page-1230: 0 x id="ts-peek-nav-data"
  ok   page-1230: 0 x ts-pill-nav
  200 /chair-massage/
  ok   page-1293: 1 x id="ts-peek-nav-css"
  ok   page-1293: 1 x id="ts-peek-nav-runtime"
  ok   page-1293: 1 x id="ts-peek-nav-config"
  ok   page-1293: 1 x id="ts-peek-nav-data"
  ok   page-1293: 1 x class="ts-jump"
  ok   page-1293: 0 x ts-pill-nav
  200 /contact/
  ok   page-301: 1 x id="ts-peek-nav-css"
  ok   page-301: 1 x id="ts-peek-nav-runtime"
  ok   page-301: 1 x id="ts-peek-nav-config"
  ok   page-301: 0 x id="ts-peek-nav-data"
  ok   page-301: 0 x ts-pill-nav
  200 /corporate-massage-portland/
  ok   page-1266: 1 x id="ts-peek-nav-css"
  ok   page-1266: 1 x id="ts-peek-nav-runtime"
  ok   page-1266: 1 x id="ts-peek-nav-config"
  ok   page-1266: 0 x id="ts-peek-nav-data"
  ok   page-1266: 0 x ts-pill-nav
  200 /deep-tissue-massage/
  ok   page-1091: 1 x id="ts-peek-nav-css"
  ok   page-1091: 1 x id="ts-peek-nav-runtime"
  ok   page-1091: 1 x id="ts-peek-nav-config"
  ok   page-1091: 1 x id="ts-peek-nav-data"
  ok   page-1091: 1 x class="ts-jump"
  ok   page-1091: 0 x ts-pill-nav
  200 /in-page-link-ideas/
  ok   page-1340: 1 x id="ts-peek-nav-css"
  ok   page-1340: 1 x id="ts-peek-nav-runtime"
  ok   page-1340: 1 x id="ts-peek-nav-config"
  ok   page-1340: 0 x id="ts-peek-nav-data"
  ok   page-1340: 0 x ts-pill-nav
  200 /index-nav-ideas/
  ok   page-1354: 1 x id="ts-peek-nav-css"
  ok   page-1354: 1 x id="ts-peek-nav-runtime"
  ok   page-1354: 1 x id="ts-peek-nav-config"
  ok   page-1354: 0 x id="ts-peek-nav-data"
  ok   page-1354: 0 x ts-pill-nav
  200 /personal-automation/
  ok   page-1254: 1 x id="ts-peek-nav-css"
  ok   page-1254: 1 x id="ts-peek-nav-runtime"
  ok   page-1254: 1 x id="ts-peek-nav-config"
  ok   page-1254: 0 x id="ts-peek-nav-data"
  ok   page-1254: 0 x ts-pill-nav
  200 /personal-automation/privacy/
  ok   page-1255: 1 x id="ts-peek-nav-css"
  ok   page-1255: 1 x id="ts-peek-nav-runtime"
  ok   page-1255: 1 x id="ts-peek-nav-config"
  ok   page-1255: 0 x id="ts-peek-nav-data"
  ok   page-1255: 0 x ts-pill-nav
  200 /personal-automation/terms/
  ok   page-1257: 1 x id="ts-peek-nav-css"
  ok   page-1257: 1 x id="ts-peek-nav-runtime"
  ok   page-1257: 1 x id="ts-peek-nav-config"
  ok   page-1257: 0 x id="ts-peek-nav-data"
  ok   page-1257: 0 x ts-pill-nav
  200 /privacy-policy/
  ok   page-1227: 1 x id="ts-peek-nav-css"
  ok   page-1227: 1 x id="ts-peek-nav-runtime"
  ok   page-1227: 1 x id="ts-peek-nav-config"
  ok   page-1227: 0 x id="ts-peek-nav-data"
  ok   page-1227: 0 x ts-pill-nav
  200 /progress-nav-ideas/
  ok   page-1346: 1 x id="ts-peek-nav-css"
  ok   page-1346: 1 x id="ts-peek-nav-runtime"
  ok   page-1346: 1 x id="ts-peek-nav-config"
  ok   page-1346: 0 x id="ts-peek-nav-data"
  ok   page-1346: 0 x ts-pill-nav
  200 /rail-nav-ideas/
  ok   page-1348: 1 x id="ts-peek-nav-css"
  ok   page-1348: 1 x id="ts-peek-nav-runtime"
  ok   page-1348: 1 x id="ts-peek-nav-config"
  ok   page-1348: 0 x id="ts-peek-nav-data"
  ok   page-1348: 0 x ts-pill-nav
  200 /services/
  ok   page-299: 1 x id="ts-peek-nav-css"
  ok   page-299: 1 x id="ts-peek-nav-runtime"
  ok   page-299: 1 x id="ts-peek-nav-config"
  ok   page-299: 0 x id="ts-peek-nav-data"
  ok   page-299: 1 x class="ts-jump"
  ok   page-299: 0 x ts-pill-nav
  200 /thai-massage/
  ok   page-1160: 1 x id="ts-peek-nav-css"
  ok   page-1160: 1 x id="ts-peek-nav-runtime"
  ok   page-1160: 1 x id="ts-peek-nav-config"
  ok   page-1160: 1 x id="ts-peek-nav-data"
  ok   page-1160: 1 x class="ts-jump"
  ok   page-1160: 0 x ts-pill-nav
  200 /therapeutic-massage/
  ok   page-1107: 1 x id="ts-peek-nav-css"
  ok   page-1107: 1 x id="ts-peek-nav-runtime"
  ok   page-1107: 1 x id="ts-peek-nav-config"
  ok   page-1107: 1 x id="ts-peek-nav-data"
  ok   page-1107: 1 x class="ts-jump"
  ok   page-1107: 0 x ts-pill-nav
  200 /work-with-me/
  ok   page-1165: 1 x id="ts-peek-nav-css"
  ok   page-1165: 1 x id="ts-peek-nav-runtime"
  ok   page-1165: 1 x id="ts-peek-nav-config"
  ok   page-1165: 0 x id="ts-peek-nav-data"
  ok   page-1165: 0 x ts-pill-nav
public check: all published pages and home checked; core/data scope matches installed snippets, existing service jump rows retained
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-09 17:38 UTC
the site answered (200) for https://staging39.thaisolitude.com
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
PASS  verify-home.py https://staging39.thaisolitude.com
PASS  verify-client-pages.py https://staging39.thaisolitude.com
PASS  posture-check.py https://staging39.thaisolitude.com

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
items: candidate 27, reference 27 | blocks that failed to parse: candidate 0, reference 0
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261109-173833/reference
https://staging39.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261109-173833/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2572), candidate (1280, 2570))
  DIFF about-m.png: page height/width differs (reference (390, 3176), candidate (390, 3201))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1680), candidate (1280, 1705))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2766), candidate (390, 2873))
  DIFF contact-d.png: page height/width differs (reference (1280, 2990), candidate (1280, 2988))
  DIFF contact-m.png: page height/width differs (reference (390, 2993), candidate (390, 2992))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5674), candidate (1280, 5779))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 9446), candidate (390, 10085))
  DIFF home-d.png: page height/width differs (reference (1280, 4624), candidate (1280, 4622))
  DIFF home-m.png: page height/width differs (reference (390, 5929), candidate (390, 6095))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1405), candidate (1280, 1430))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2198), candidate (390, 2251))
  DIFF services-d.png: page height/width differs (reference (1280, 3906), candidate (1280, 3904))
  DIFF services-m.png: page height/width differs (reference (390, 6125), candidate (390, 6376))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5774), candidate (1280, 5773))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9592), candidate (390, 10165))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 6426), candidate (1280, 6451))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 10367), candidate (390, 11143))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 6045), candidate (1280, 6103))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7981), candidate (390, 8044))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging39-20261109-173833)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging39
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging39 (staging39.thaisolitude.com)    export: staging39-before-peek-nav-20261109-173723.sql    modified 2026-11-09 17:37:23
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
20c3783cc497: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
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
fingerprints: restored copy 104, real site 104
compared: css 1, option 12, page 19, rows 21, seo 21, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< snippet	1380
> snippet	1380
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-peek-nav.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-09 17:41:06 UTC_

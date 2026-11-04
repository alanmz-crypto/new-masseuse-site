# Site op `ui-section-guide-apply-20261104`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `section-guide/push-section-guide.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `2950eef7ef686e17d72e2bad2b6c60c3ded9eeff` on `feat/2026-11-04-section-guide` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41806275637 |
| started | 2026-11-04 09:17:50 UTC |

## push-section-guide.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-section-guide (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-section-guide-20261104-091800.sql (7278947 bytes)
home=http://staging38.thaisolitude.com section-guide=ready CSS bytes=66514
Success: Preflight ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-section-guide-20261104-091800.sql -> offsite-2026-11/staging38-before-section-guide-20261104-091800.sql.age (7280923 bytes, read back identical; plaintext sha256 564b39e4f4d7...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 6 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
home=http://staging38.thaisolitude.com section-guide=ready CSS bytes=66514
Success: Section guide applied. Before-snapshot: /home/customer/private-backups/section-guide-before-20261104-091816-4f642403.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: section-guide-before-20261104-091816-4f642403.json -> offsite-2026-11/section-guide-before-20261104-091816-4f642403.json.age (77257 bytes, read back identical; plaintext sha256 fb6661c101b1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 7 already there.
[6] Does the public page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
public check: /thai-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: /therapeutic-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: /deep-tissue-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: /chair-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: / HTTP 200; CSS, licence and scoped runtime verified
public check: /contact/ HTTP 200; CSS, licence and scoped runtime verified
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): PASS

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-04 09:19 UTC
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
items: candidate 27, reference 27 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /services/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261104-091913/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261104-091913/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-section-guide-20261104-091800.sql    modified 2026-11-04 09:18:00
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
5b0e31685a7d: Waiting
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
bdc186c1268d: Waiting
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
fingerprints: restored copy 94, real site 95
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 25
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_term_relationships
> rows	iej_term_relationships
< css	29
> css	29
> snippet	1349
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-section-guide.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-04 09:22:05 UTC_

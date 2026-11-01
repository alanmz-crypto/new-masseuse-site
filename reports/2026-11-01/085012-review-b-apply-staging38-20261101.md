# Site op `review-b-apply-staging38-20261101`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `review-b/push-bundle-b.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `51a04088066f5105e078799b1971addc31f37254` on `codex/2026-11-01-review-bundle-b` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41290196062 |
| started | 2026-11-01 08:45:49 UTC |

## push-bundle-b.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-bundle-b (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-bundle-b-20261101-084559.sql (5921174 bytes)
metadata therapeutic-massage=ready
metadata deep-tissue-massage=ready
metadata chair-massage=ready
metadata corporate-massage-portland=ready
metadata privacy-policy=ready
metadata accessibility=ready
metadata services=ready
Organization about=ready
Organization contact=ready
Corporate https share image=ready
Home approved share card=ready
global Person=ready
tagline=ready
LocalBusinessInfo=ready
Home by arrangement=ready
approved share-card media=absent (ready to import)
host=http://staging38.thaisolitude.com state=ready
Success: Preflight ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-bundle-b-20261101-084559.sql -> offsite-2026-11/staging38-before-bundle-b-20261101-084559.sql.age (5922814 bytes, read back identical; plaintext sha256 9eace4b56c72...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
metadata therapeutic-massage=ready
metadata deep-tissue-massage=ready
metadata chair-massage=ready
metadata corporate-massage-portland=ready
metadata privacy-policy=ready
metadata accessibility=ready
metadata services=ready
Organization about=ready
Organization contact=ready
Corporate https share image=ready
Home approved share card=ready
global Person=ready
tagline=ready
LocalBusinessInfo=ready
Home by arrangement=ready
approved share-card media=absent (ready to import)
host=http://staging38.thaisolitude.com state=ready
Success: Applied Bundle B and the approved default/Home share card. Before-snapshot: /home/<ssh-user>/private-backups/bundle-b-before-20261101-084613.json
SiteGround cache purged
[4b] Does the public page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
public check: home HTTP 200, Person and licence present
public check: services HTTP 200, Person and licence present
public check: therapeutic-massage HTTP 200, Person and licence present
public check: deep-tissue-massage HTTP 200, Person and licence present
public check: chair-massage HTTP 200, Person and licence present
public check: corporate-massage-portland HTTP 200, Person and licence present
public check: privacy-policy HTTP 200, Person and licence present
public check: accessibility HTTP 200, Person and licence present
public check: about HTTP 200, Person and licence present
public check: contact HTTP 200, Person and licence present
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: bundle-b-before-20261101-084613.json -> offsite-2026-11/bundle-b-before-20261101-084613.json.age (832258 bytes, read back identical; plaintext sha256 0c98a98634f3...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-01 08:47 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
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
PASS  Review Bundle B source values and approved wording match their builder
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261101-084717/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261101-084717/candidate
page-views compared: 20 | pixel-identical: 6 | different: 14 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2193), candidate (1280, 2545))
  DIFF about-m.png: page height/width differs (reference (390, 2800), candidate (390, 3173))
  DIFF contact-d.png: page height/width differs (reference (1280, 2713), candidate (1280, 2990))
  DIFF contact-m.png: page height/width differs (reference (390, 2679), candidate (390, 3045))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5147), candidate (1280, 5567))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8781), candidate (390, 9241))
  DIFF home-d.png: 0.3933% of pixels differ, in box x 207-823, y 2247-3302
  DIFF home-m.png: 0.2249% of pixels differ, in box x 47-325, y 2477-4101
  DIFF services-d.png: page height/width differs (reference (1280, 3552), candidate (1280, 3906))
  DIFF services-m.png: page height/width differs (reference (390, 5702), candidate (390, 6149))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5328), candidate (1280, 5747))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9102), candidate (390, 9561))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5702), candidate (1280, 6132))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9585), candidate (390, 10055))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging38-20261101-084717)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

2 CHECK(S) FAILED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-bundle-b-20261101-084559.sql    modified 2026-11-01 08:45:59
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
fingerprints: restored copy 88, real site 90
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< page	home
> page	home
< snippet	1023
> snippet	1023
< seo	296:surerank_settings_social
> seo	296:surerank_settings_social
< seo	298:surerank_settings_schemas
< seo	299:surerank_settings_general
> seo	298:surerank_settings_schemas
> seo	299:surerank_settings_general
< seo	301:surerank_settings_schemas
< seo	1091:surerank_settings_general
< seo	1107:surerank_settings_general
> seo	301:surerank_settings_schemas
> seo	1091:surerank_settings_general
> seo	1107:surerank_settings_general
> seo	1227:surerank_settings_general
> seo	1230:surerank_settings_general
< seo	1266:surerank_settings_general
< seo	1266:surerank_settings_social
< seo	1302:surerank_settings_general
> seo	1266:surerank_settings_general
> seo	1266:surerank_settings_social
> seo	1302:surerank_settings_general
< option	blogdescription
> option	blogdescription
< option	surerank_settings
> option	surerank_settings
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-bundle-b.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-01 08:50:12 UTC_

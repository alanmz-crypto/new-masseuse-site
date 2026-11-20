# Site op `ww-names-apply-s40-1`

| | |
|---|---|
| operation | `run` on `staging40` |
| script | `winding-willow/push-winding-willow.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `9acf245dddebb4a333ca58a5658abe1af977d989` on `feat/2026-11-20-winding-willow-watch` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/44695356378 |
| started | 2026-11-20 07:29:56 UTC |

## push-winding-willow.sh --apply(S_TARGET=staging40): PASS

```text
Target site: staging40   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-winding-willow (outside every web root) ...
[2] Backup on the server + preflight:
staging40 is www/staging40.thaisolitude.com/public_html
fresh database export: private-backups/staging40-before-winding-willow-20261120-073012.sql (4536008 bytes)
Success: Preflight passed; nothing changed. host=http://staging40.thaisolitude.com state=update page=previous menu_item=exact css_block=exact matching_posts=1344 (package sha256 d93cee0213be59b881281643433cbe83e25c5673fedee2764758fdc942ff49b2)
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: staging40-before-winding-willow-20261120-073012.sql -> offsite-2026-11/staging40-before-winding-willow-20261120-073012.sql.age (4537312 bytes, read back identical; plaintext sha256 f4d6d79ea4c6...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 2 already there.
[4] Applying:
staging40 is www/staging40.thaisolitude.com/public_html
Success: Updated the content of page #1344 (/winding-willow/, 30765 bytes, sha256 d93cee0213be59b881281643433cbe83e25c5673fedee2764758fdc942ff49b2); menu item #1352 and the ts-ww CSS block unchanged. Before-snapshot: /home/<ssh-user>/private-backups/winding-willow-before-20261120-073026.json
SiteGround cache purged
[4b] Does the public page show the change?
staging40 is www/staging40.thaisolitude.com/public_html
  200 /winding-willow/
  200 /services/
  200 /contact/
  200 /about/
public check: the page answers 200 with its opening sentence, H1, title, description, licence line, research heading, booking links and 7 PubMed sources; Services, Contact and About carry only the menu link to it and no link to Winding Willow
[5] Copying the new before-snapshot off the server:
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: winding-willow-before-20261120-073026.json -> offsite-2026-11/winding-willow-before-20261120-073026.json.age (33395 bytes, read back identical; plaintext sha256 b21e3d928d05...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 3 already there.
```

## post-change-test.sh staging40 (pages, structured data, 20 pixel comparisons, PHP log): PASS

```text
Target: staging40 (https://staging40.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-20 07:33 UTC
the site answered (200) for https://staging40.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  Therapeutic evidence content matches its builder
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Winding Willow page matches its builder (frozen staging39 capture + 4 approved changes)
PASS  Review Bundle A exact fragments and new wording match their builder
PASS  Review Bundle B source values and approved wording match their builder
PASS  Review Bundle C frozen fragments and runtime sources match their builder
PASS  Review Bundle D frozen copy and research fragments match their builder
PASS  Review Bundle E image fragments, prepared WebP and runtime sources match their builder
PASS  verify-home.py https://staging40.thaisolitude.com
PASS  verify-client-pages.py https://staging40.thaisolitude.com
PASS  posture-check.py https://staging40.thaisolitude.com

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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging40-20261120-073309/reference
https://staging40.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging40-20261120-073309/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for staging40
```

## prove-restore.sh staging40 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging40 (staging40.thaisolitude.com)    export: staging40-before-winding-willow-20261120-073012.sql    modified 2026-11-20 07:30:13
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
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
4f693d0ae952: Verifying Checksum
4f693d0ae952: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
20c3783cc497: Pull complete
82b15426938b: Pull complete
5d5076a693b9: Verifying Checksum
5d5076a693b9: Download complete
b2a602564ec4: Pull complete
bdc186c1268d: Pull complete
5b0e31685a7d: Pull complete
5d5076a693b9: Pull complete
650d43fe7026: Pull complete
4f693d0ae952: Pull complete
Digest: sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
Status: Downloaded newer image for mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
import exit code: 0
fingerprints: restored copy 102, real site 102
compared: css 1, option 12, page 16, rows 21, seo 22, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< page	winding-willow
> page	winding-willow
```

**RESULT: PASS** - push-winding-willow.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-20 07:42:25 UTC_

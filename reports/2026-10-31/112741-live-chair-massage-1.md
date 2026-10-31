# Site op `live-chair-massage-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `chair-massage/push-chair-massage.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `adbf66276f7528bc5ba53e3bf9a2dcfddbff0542` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41196684922 |
| started | 2026-10-31 11:24:01 UTC |

## push-chair-massage.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-chair-massage (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-chair-massage-20261031-112417.sql (4351766 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready picture=absent page=absent services_row=absent strip_snippet=ready jump_snippet=ready
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: live-before-chair-massage-20261031-112417.sql -> offsite-2026-10/live-before-chair-massage-20261031-112417.sql.age (4353022 bytes, read back identical; plaintext sha256 8e26e63e85ce...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 0 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Added page #1293 (/chair-massage/), picture #1292, the Services row, and 'chair-massage' in snippets #1268 and #1232. Before-snapshot: /home/<ssh-user>/private-backups/chair-massage-before-20261031-112429.json
SiteGround cache purged
[4b] Do the public pages show the change?
live is www/thaisolitude.com/public_html
  200 /chair-massage/
  200 image/webp /wp-content/uploads/2026/10/chair-massage-tree.webp (the picture)
  200 /services/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /thai-massage/
  200 /about/
  200 /corporate-massage-portland/
public check: the page (title, description, strip, jump links, FAQ and research headings, 32 sources, 11 questions, the one picture as the hero), the Services row with the same picture, and the strip/jump links unchanged elsewhere
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: chair-massage-before-20261031-112429.json -> offsite-2026-10/chair-massage-before-20261031-112429.json.age (48764 bytes, read back identical; plaintext sha256 6cb233906a5b...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 1 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-31 11:25 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

[1] code gates and rendered-page verifiers
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
PASS  Chair Massage content matches its builder (and passes its wording gate)
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
items: candidate 23, reference 23 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /services/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261031-112520/reference
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261031-112520/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2116), candidate (1280, 2193))
  DIFF about-m.png: page height/width differs (reference (390, 2639), candidate (390, 2800))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1549), candidate (1280, 1627))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2473), candidate (390, 2635))
  DIFF contact-d.png: page height/width differs (reference (1280, 2635), candidate (1280, 2713))
  DIFF contact-m.png: page height/width differs (reference (390, 2518), candidate (390, 2679))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5069), candidate (1280, 5147))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8620), candidate (390, 8781))
  DIFF home-d.png: page height/width differs (reference (1280, 4163), candidate (1280, 4570))
  DIFF home-m.png: page height/width differs (reference (390, 5254), candidate (390, 5923))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1327), candidate (1280, 1405))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2067), candidate (390, 2228))
  DIFF services-d.png: page height/width differs (reference (1280, 2949), candidate (1280, 3552))
  DIFF services-m.png: page height/width differs (reference (390, 4463), candidate (390, 5702))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5250), candidate (1280, 5328))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 8940), candidate (390, 9102))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5624), candidate (1280, 5702))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9424), candidate (390, 9585))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 5990), candidate (1280, 6045))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7801), candidate (390, 7981))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261031-112520)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-chair-massage-20261031-112417.sql    modified 2026-10-31 11:24:17
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
fingerprints: restored copy 86, real site 88
compared: css 1, option 12, page 15, rows 21, seo 19, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
> page	chair-massage
< page	services
> page	services
< snippet	1232
> snippet	1232
< snippet	1268
> snippet	1268
> seo	1293:surerank_settings_general
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-chair-massage.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-31 11:27:40 UTC_

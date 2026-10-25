# Site op `live-corporate-massage-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `corporate/push-corporate-massage.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `03642ce2fec472427a19d208c02acaf5c493840a` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40147102456 |
| started | 2026-10-25 00:32:45 UTC |

## push-corporate-massage.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-corporate-massage (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-corporate-massage-20261025-003252.sql (3949320 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready page=absent picture=absent services_row=absent menu_item=absent snippet=absent css_block=absent
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: live-before-corporate-massage-20261025-003252.sql -> offsite-2026-10/live-before-corporate-massage-20261025-003252.sql.age (3950480 bytes, read back identical; plaintext sha256 29be25c128ef...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 6 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Added page #1266 (/corporate-massage-portland/), picture #1269, the Services row, menu item #1271, snippet #1268 "CorporateBand" and the ts-corp CSS block. Before-snapshot: /home/<ssh-user>/private-backups/corporate-massage-before-20261025-003302.json
SiteGround cache purged
[4b] Do the public pages show the change?
live is www/thaisolitude.com/public_html
  200 /corporate-massage-portland/
  200 /services/
  200 /about/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 image/webp /wp-content/uploads/2026/10/corporate-group-massage.webp (the picture)
  200 /
  200 /work-with-me/
  200 /personal-automation/ (unrelated page)
  301 /privacy/ (unrelated page)
  301 /terms/ (unrelated page)
public check: the page, the Services row and its picture, the menu link (header, phone menu, footer), the strip on the 8 client pages, and no strip on Home, Work With Me, the new page itself or the unrelated pages
[5] Copying the new before-snapshot to this computer:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: corporate-massage-before-20261025-003302.json -> offsite-2026-10/corporate-massage-before-20261025-003302.json.age (88453 bytes, read back identical; plaintext sha256 3b11a6f2ca17...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 7 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-25 00:33 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  shellcheck, work-with-me-employer (warnings fatal)
PASS  shellcheck, all scripts (errors fatal)
PASS  python syntax
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
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
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
INFO dateModified (WordPress's last-edited time, set aside) differs on: /services/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261025-003358/reference
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261025-003358/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2116), candidate (1280, 2174))
  DIFF about-m.png: page height/width differs (reference (390, 2639), candidate (390, 2766))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1549), candidate (1280, 1608))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2473), candidate (390, 2601))
  DIFF contact-d.png: page height/width differs (reference (1280, 2635), candidate (1280, 2694))
  DIFF contact-m.png: page height/width differs (reference (390, 2518), candidate (390, 2645))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5069), candidate (1280, 5128))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8620), candidate (390, 8747))
  DIFF home-d.png: 0.1553% of pixels differ, in box x 101-983, y 38-4093
  DIFF home-m.png: page height/width differs (reference (390, 5254), candidate (390, 5283))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1327), candidate (1280, 1385))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2067), candidate (390, 2194))
  DIFF services-d.png: page height/width differs (reference (1280, 2949), candidate (1280, 3251))
  DIFF services-m.png: page height/width differs (reference (390, 4463), candidate (390, 5129))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5250), candidate (1280, 5308))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 8940), candidate (390, 9068))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5624), candidate (1280, 5683))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9424), candidate (390, 9551))
  DIFF work-with-me-d.png: 0.1096% of pixels differ, in box x 101-983, y 38-5923
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7801), candidate (390, 7830))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261025-003358)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-corporate-massage-20261025-003252.sql    modified 2026-10-25 00:32:52
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
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
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
fingerprints: restored copy 82, real site 85
compared: css 1, option 12, page 14, rows 21, seo 17, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< rows	iej_term_relationships
> rows	iej_term_relationships
> page	corporate-massage-portland
< page	services
> page	services
< css	29
> css	29
> snippet	1268
> seo	1266:surerank_settings_general
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-corporate-massage.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-25 00:36:13 UTC_

# Site op `live-portland-items-1-3`

| | |
|---|---|
| operation | `run` on `live` |
| script | `portland-live/push-portland-live.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `267ac47c5a312fcec9945685d0cd8424593b552f` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41075214422 |
| started | 2026-10-30 14:50:45 UTC |

## push-portland-live.sh --apply(S_TARGET=live): PASS

```text
=================== portland-meta/push-portland-meta.sh (live, --apply) ===================
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-portland-meta (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-portland-meta-20261030-145102.sql (3778774 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready pages={"home":"ready","thai-massage":"ready","therapeutic-massage":"ready","deep-tissue-massage":"ready"}
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: live-before-portland-meta-20261030-145102.sql -> offsite-2026-10/live-before-portland-meta-20261030-145102.sql.age (3779886 bytes, read back identical; plaintext sha256 bf49b909b608...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 0 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: SEO titles and meta descriptions are set on all four pages (Home, Thai, Therapeutic, Deep Tissue). Before-snapshot: /home/<ssh-user>/private-backups/portland-meta-before-20261030-145114.json
SiteGround cache purged
[4b] Do the public pages show the changes?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   / title tag is the new title (1)
  ok   / meta description is the new description (1)
  ok   / old title gone everywhere (0)
  ok   / old description gone everywhere (meta, og, twitter) (0)
  ok   / page content untouched (the announcement strip is still there) (1)
  ok   / licence number in the footer (1)
public check: https://thaisolitude.com/thai-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /thai-massage/ title tag is the new title (1)
  ok   /thai-massage/ meta description is the new description (1)
  ok   /thai-massage/ old title gone everywhere (0)
  ok   /thai-massage/ old description gone everywhere (meta, og, twitter) (0)
  ok   /thai-massage/ page content untouched (the announcement strip is still there) (1)
  ok   /thai-massage/ licence number in the footer (1)
public check: https://thaisolitude.com/therapeutic-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /therapeutic-massage/ title tag is the new title (1)
  ok   /therapeutic-massage/ meta description is the new description (1)
  ok   /therapeutic-massage/ old description gone everywhere (meta, og, twitter) (0)
  ok   /therapeutic-massage/ page content untouched (the announcement strip is still there) (1)
  ok   /therapeutic-massage/ licence number in the footer (1)
public check: https://thaisolitude.com/deep-tissue-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /deep-tissue-massage/ title tag is the new title (1)
  ok   /deep-tissue-massage/ meta description is the new description (1)
  ok   /deep-tissue-massage/ old description gone everywhere (meta, og, twitter) (0)
  ok   /deep-tissue-massage/ page content untouched (the announcement strip is still there) (1)
  ok   /deep-tissue-massage/ licence number in the footer (1)
public check: all four pages show the new title and description in the head; their content is unchanged
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: portland-meta-before-20261030-145114.json -> offsite-2026-10/portland-meta-before-20261030-145114.json.age (19104 bytes, read back identical; plaintext sha256 349f5c51515c...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 1 already there.
=================== contact-address/push-contact-address.sh (live, --apply) ===================
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-contact-address (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-contact-address-20261030-145134.sql (3778742 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready old_text_count=1 new_text_count=0
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: live-before-contact-address-20261030-145134.sql -> offsite-2026-10/live-before-contact-address-20261030-145134.sql.age (3779854 bytes, read back identical; plaintext sha256 3f28119172ba...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 2 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Page #301 (/contact/): the Address line now says "Portland, OR". Before-snapshot: /home/<ssh-user>/private-backups/contact-address-before-20261030-145146.json
SiteGround cache purged
[4b] Does the public page show the change?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/contact/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   Address line says Portland, OR (1)
  ok   old Address line gone (0)
  ok   phone number still on the page (1)
  ok   email still on the page (1)
  ok   'For employers' block still there (1)
  ok   page title tag unchanged (1)
  ok   meta description unchanged (corporate wording) (1)
  ok   announcement strip still above the page (1)
  ok   licence number in the footer (1)
  info the map was NOT touched (Ryan: skip the map); mentions of the Mt. Tabor map address in the page: 1 (url-encoded) + 0 (plain)
public check: the Contact page shows the new Address line; everything else on it is unchanged
[5] Copying the new before-snapshot off the server:
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: contact-address-before-20261030-145146.json -> offsite-2026-10/contact-address-before-20261030-145146.json.age (16200 bytes, read back identical; plaintext sha256 14b3beeb0c67...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 3 already there.
=================== onpage-portland/push-onpage-portland.sh (live, --apply) ===================
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-onpage-portland (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-onpage-portland-20261030-145205.sql (3795018 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready replacements ready=3 applied=0 changed=0
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 5 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 5 file(s), identical sha256 on the server and in the copy.
off-site: live-before-onpage-portland-20261030-145205.sql -> offsite-2026-10/live-before-onpage-portland-20261030-145205.sql.age (3796130 bytes, read back identical; plaintext sha256 33c3ff6980a1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 4 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Applied 3 replacements on 3 pages (Home licence line, Thai H1, Deep Tissue H1). Before-snapshot: /home/<ssh-user>/private-backups/onpage-portland-before-20261030-145217.json
SiteGround cache purged
[4b] Do the public pages show the change?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   / new text shows once (1)
  ok   / old text gone (0)
  ok   / exactly one H1 (1)
  ok   / announcement strip still there (1)
  ok   / licence number in the footer (1)
  info / remaining 'Mt. Tabor' mentions in the served page: 0 (informational)
public check: https://thaisolitude.com/thai-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /thai-massage/ new text shows once (1)
  ok   /thai-massage/ old text gone (0)
  ok   /thai-massage/ exactly one H1 (1)
  ok   /thai-massage/ title tag unchanged (portland-meta) (1)
  ok   /thai-massage/ announcement strip still there (1)
  ok   /thai-massage/ licence number in the footer (1)
  info /thai-massage/ remaining 'Mt. Tabor' mentions in the served page: 0 (informational)
public check: https://thaisolitude.com/deep-tissue-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /deep-tissue-massage/ new text shows once (1)
  ok   /deep-tissue-massage/ old text gone (0)
  ok   /deep-tissue-massage/ exactly one H1 (1)
  ok   /deep-tissue-massage/ title tag unchanged (portland-meta) (1)
  ok   /deep-tissue-massage/ announcement strip still there (1)
  ok   /deep-tissue-massage/ licence number in the footer (1)
  info /deep-tissue-massage/ remaining 'Mt. Tabor' mentions in the served page: 0 (informational)
public check: the Home licence line and the Thai and Deep Tissue headings show the new wording; the rest of each page is unchanged
[5] Copying the new before-snapshot off the server:
Copying the 6 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 6 file(s), identical sha256 on the server and in the copy.
off-site: onpage-portland-before-20261030-145217.json -> offsite-2026-10/onpage-portland-before-20261030-145217.json.age (201126 bytes, read back identical; plaintext sha256 6410966a8b0b...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 5 already there.
All three steps finished on live.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-30 14:53 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

[1] code gates and rendered-page verifiers
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
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
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
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261030-145309/reference
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261030-145309/candidate
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
  DIFF services-d.png: page height/width differs (reference (1280, 2949), candidate (1280, 3271))
  DIFF services-m.png: page height/width differs (reference (390, 4463), candidate (390, 5164))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5250), candidate (1280, 5328))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 8940), candidate (390, 9102))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5624), candidate (1280, 5702))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9424), candidate (390, 9585))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 5990), candidate (1280, 6045))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7801), candidate (390, 7981))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261030-145309)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-onpage-portland-20261030-145205.sql    modified 2026-10-30 14:52:05
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
fingerprints: restored copy 85, real site 85
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
< page	deep-tissue-massage
< page	home
> page	deep-tissue-massage
> page	home
< page	thai-massage
> page	thai-massage
```

**RESULT: PASS** - push-portland-live.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-30 14:55:28 UTC_

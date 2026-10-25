# Site op `live-contact-meta-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `contact-meta/push-contact-meta.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `db27a9cec95e6d5a1201df6c58befaee2b8cc2de` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40208829594 |
| started | 2026-10-25 15:50:34 UTC |

## push-contact-meta.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-contact-meta (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-contact-meta-20261025-155042.sql (4571834 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready pages_with_slug=1
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 5 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 5 file(s), identical sha256 on the server and in the copy.
off-site: live-before-contact-meta-20261025-155042.sql -> offsite-2026-10/live-before-contact-meta-20261025-155042.sql.age (4573138 bytes, read back identical; plaintext sha256 e8e36368318a...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 4 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Page #301 (/contact/): SEO description is set. Before-snapshot: /home/<ssh-user>/private-backups/contact-meta-before-20261025-155057.json
SiteGround cache purged
[4b] Does the public page show the changes?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/contact/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   title tag unchanged (1)
  ok   meta description (1)
  ok   old description gone everywhere (meta, og, twitter) (0)
  ok   canonical unchanged (1)
  ok   page content untouched (the corporate-only strip is still above the form) (1)
  ok   phone number still on the page (1)
  ok   licence number in the footer (1)
  info og:description shows the new text: 1 time(s); twitter:description: 1 time(s)
public check: the new description shows in the page head, the title and the page itself are unchanged
[5] Copying the new before-snapshot to this computer:
Copying the 6 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 6 file(s), identical sha256 on the server and in the copy.
off-site: contact-meta-before-20261025-155057.json -> offsite-2026-10/contact-meta-before-20261025-155057.json.age (6368 bytes, read back identical; plaintext sha256 aa61d5920af8...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 5 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-25 15:51 UTC
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
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /services/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261025-155153/reference
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261025-155153/candidate
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
  DIFF home-m.png: page height/width differs (reference (390, 5254), candidate (390, 5945))
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
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261025-155153)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-contact-meta-20261025-155042.sql    modified 2026-10-25 15:50:43
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
4f693d0ae952: Waiting
650d43fe7026: Waiting
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
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< seo	301:surerank_settings_general
> seo	301:surerank_settings_general
```

**RESULT: PASS** - push-contact-meta.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-25 15:54:29 UTC_

# Site op `contact-meta-apply-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `contact-meta/push-contact-meta.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `4db73483ce25a14347b5af3b4d15a0636afa9afe` on `feat/2026-10-25-contact-meta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40207466472 |
| started | 2026-10-25 15:33:10 UTC |

## push-contact-meta.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-contact-meta (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-contact-meta-20261025-153317.sql (4536997 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready pages_with_slug=1
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-contact-meta-20261025-153317.sql -> offsite-2026-10/staging38-before-contact-meta-20261025-153317.sql.age (4538301 bytes, read back identical; plaintext sha256 6ce2d84bd430...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 2 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Page #301 (/contact/): SEO description is set. Before-snapshot: /home/<ssh-user>/private-backups/contact-meta-before-20261025-153328.json
SiteGround cache purged
[4b] Does the public page show the changes?
staging38 is www/staging38.thaisolitude.com/public_html
public check: https://staging38.thaisolitude.com/contact/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
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
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: contact-meta-before-20261025-153328.json -> offsite-2026-10/contact-meta-before-20261025-153328.json.age (6418 bytes, read back identical; plaintext sha256 2904983caf7e...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 3 already there.
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): PASS

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-25 15:34 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

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
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261025-153426/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261025-153426/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-contact-meta-20261025-153317.sql    modified 2026-10-25 15:33:17
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
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
< seo	301:surerank_settings_general
> seo	301:surerank_settings_general
```

**RESULT: PASS** - push-contact-meta.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-25 15:36:40 UTC_

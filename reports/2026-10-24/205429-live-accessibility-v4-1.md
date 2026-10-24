# Site op `live-accessibility-v4-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `accessibility/push-accessibility-page-v4.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `12ac5e145f972b153e7958db1e0574e32dab5402` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40131592761 |
| started | 2026-10-24 20:50:49 UTC |

## push-accessibility-page-v4.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-accessibility-v4 (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-accessibility-page-v4-20261024-205102.sql (3942118 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=old pages_with_slug=1 research_sections_present=3_of_3
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 13 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 13 file(s), identical sha256 on the server and in the copy.
off-site: live-before-accessibility-page-v4-20261024-205102.sql -> offsite-2026-10/live-before-accessibility-page-v4-20261024-205102.sql.age (3943278 bytes, read back identical; plaintext sha256 17444b4ca2c1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 12 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Page #1230 (/accessibility/) now holds version 4 (the Deep Tissue page named in the research summaries sentence and the screen reader limit, and the new date). Before-snapshot: /home/<ssh-user>/private-backups/accessibility-page-v4-before-20261024-205114.json
SiteGround cache purged
[4b] Does the public page show version 4?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/accessibility/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
public check: the new research summaries sentence (Deep Tissue named) and the screen reader sentence, the kept dark/light paragraph, the Known limitations and Feedback sections and the new date are on the page
[5] Copying the new before-snapshot to this computer:
Copying the 14 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 14 file(s), identical sha256 on the server and in the copy.
off-site: accessibility-page-v4-before-20261024-205114.json -> offsite-2026-10/accessibility-page-v4-before-20261024-205114.json.age (3559 bytes, read back identical; plaintext sha256 6e926551b448...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 13 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): PASS

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-24 20:52 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  bash -n
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
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261024-205215/candidate
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261024-205215/reference
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-accessibility-page-v4-20261024-205102.sql    modified 2026-10-24 20:51:03
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
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
4f693d0ae952: Verifying Checksum
4f693d0ae952: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
fingerprints: restored copy 82, real site 82
compared: css 1, option 12, page 13, rows 21, seo 16, snippet 19
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< page	accessibility
> page	accessibility
```

**RESULT: PASS** - push-accessibility-page-v4.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-24 20:54:28 UTC_

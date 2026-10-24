# Site op `slide-apply-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `research-slide/push-research-slide.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `8feab691a0db4a98862c0e4716b4936b2b7fd342` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40114819056 |
| started | 2026-10-24 17:09:28 UTC |

## push-research-slide.sh --apply(S_TARGET=staging36): PASS

```text
Target site: staging36   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-research-slide (outside every web root) ...
[2] Backup on the server + preflight:
staging36 is www/staging36.thaisolitude.com/public_html
fresh database export: private-backups/staging36-before-research-slide-20261024-170935.sql (6363594 bytes)
Success: Preflight passed; nothing changed. host=http://staging36.thaisolitude.com state=ready css_bytes=48765
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: staging36-before-research-slide-20261024-170935.sql -> offsite-2026-10/staging36-before-research-slide-20261024-170935.sql.age (6365346 bytes, read back identical; plaintext sha256 a953bddd0070...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 6 already there.
[4] Applying:
staging36 is www/staging36.thaisolitude.com/public_html
Success: Added the ts-research-slide block to the Additional CSS (48765 -> 49805 bytes). Before-snapshot: /home/<ssh-user>/private-backups/research-slide-before-20261024-170947.json
SiteGround cache purged
[4b] Does the public page show the changes?
staging36 is www/staging36.thaisolitude.com/public_html
public check: https://staging36.thaisolitude.com/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   the slide rule is delivered once (1)
  ok   the animated content rules are delivered (the selector appears at least three times) (1)
  ok   the dark/light button rules are still there once (1)
  ok   licence number in the footer (1)
public check: the research-slide block is delivered with the page CSS
[5] Copying the new before-snapshot to this computer:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: research-slide-before-20261024-170947.json -> offsite-2026-10/research-slide-before-20261024-170947.json.age (50388 bytes, read back identical; plaintext sha256 7492737fc562...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 7 already there.
```

## post-change-test.sh staging36 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging36 (https://staging36.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-24 17:10 UTC
the site answered (200) for https://staging36.thaisolitude.com
the site answered (200) for https://thaisolitude.com

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
PASS  verify-home.py https://staging36.thaisolitude.com
PASS  verify-client-pages.py https://staging36.thaisolitude.com
PASS  posture-check.py https://staging36.thaisolitude.com

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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-171039/reference
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-171039/candidate
page-views compared: 20 | pixel-identical: 14 | different: 6 | present in only one set: 0
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 2882), candidate (1280, 5069))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 4127), candidate (390, 8620))
  DIFF thai-massage-d.png: 0.0002% of pixels differ, in box x 57-57, y 3951-4620
  DIFF thai-massage-m.png: 0.0013% of pixels differ, in box x 35-35, y 5663-8179
  DIFF therapeutic-massage-d.png: 0.0001% of pixels differ, in box x 57-57, y 4529-5021
  DIFF therapeutic-massage-m.png: 0.0010% of pixels differ, in box x 35-35, y 6757-8555
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging36-20261024-171039)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging36
```

## prove-restore.sh staging36 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging36 (staging36.thaisolitude.com)    export: staging36-before-research-slide-20261024-170935.sql    modified 2026-10-24 17:09:35
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
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
fingerprints: restored copy 82, real site 82
compared: css 1, option 12, page 13, rows 21, seo 16, snippet 19
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< css	29
> css	29
```

**RESULT: PASS** - push-research-slide.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-24 17:12:51 UTC_

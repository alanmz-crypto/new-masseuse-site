# Site op `deep-evidence-apply-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `deep-tissue/push-deep-tissue-evidence.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `0470cb998ca6f86dde97627359cd20c92f592375` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40111602617 |
| started | 2026-10-24 16:27:07 UTC |

## push-deep-tissue-evidence.sh --apply(S_TARGET=staging36): PASS

```text
Target site: staging36   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-deep-tissue-evidence (outside every web root) ...
[2] Backup on the server + preflight:
staging36 is www/staging36.thaisolitude.com/public_html
fresh database export: private-backups/staging36-before-deep-tissue-evidence-20261024-162714.sql (6151728 bytes)
Success: Preflight passed; nothing changed. host=http://staging36.thaisolitude.com state=ready pages_with_slug=1
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: staging36-before-deep-tissue-evidence-20261024-162714.sql -> offsite-2026-10/staging36-before-deep-tissue-evidence-20261024-162714.sql.age (6153416 bytes, read back identical; plaintext sha256 88bebf62f637...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 2 already there.
[4] Applying:
staging36 is www/staging36.thaisolitude.com/public_html
Success: Page #1091 (/deep-tissue-massage/): body research paragraph, six FAQ questions, research section and picture tooltips are in. Before-snapshot: /home/<ssh-user>/private-backups/deep-tissue-evidence-before-20261024-162727.json
SiteGround cache purged
[4b] Does the public page show the changes?
staging36 is www/staging36.thaisolitude.com/public_html
public check: https://staging36.thaisolitude.com/deep-tissue-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   research heading (no duplicate) (1)
  ok   FAQ heading (no duplicate) (1)
  ok   five collapsible panels (5)
  ok   33 source entries (33)
  ok   FAQ: does it have to hurt (1)
  ok   FAQ: difference from therapeutic (1)
  ok   FAQ: choosing the focus (1)
  ok   FAQ: what to tell me before a session (1)
  ok   FAQ: where sessions take place (1)
  ok   FAQ: no travel to clients (1)
  ok   FAQ: how to book (1)
  ok   new body paragraph opens with the honest summary (1)
  ok   old body sentence gone (conditions) (0)
  ok   old body sentence gone (athletes, as a body sentence) (0)
  ok   the page's first paragraph is still there once (1)
  ok   'myofascial' appears only in the verbatim source title (1)
  ok   no picture tooltip says 'by Butsaba' any more (0)
  ok   safety sentence there twice (page text and FAQ answer) (2)
  ok   licence number in the footer (1)
public check: the new body paragraph, the FAQ, the research section and the picture tooltips show on the page
[5] Copying the new before-snapshot to this computer:
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: deep-tissue-evidence-before-20261024-162727.json -> offsite-2026-10/deep-tissue-evidence-before-20261024-162727.json.age (29580 bytes, read back identical; plaintext sha256 f3ff7622c7e3...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 3 already there.
```

## post-change-test.sh staging36 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging36 (https://staging36.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-24 16:28 UTC
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-162819/reference
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-162819/candidate
page-views compared: 20 | pixel-identical: 18 | different: 2 | present in only one set: 0
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 2882), candidate (1280, 5069))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 4127), candidate (390, 8620))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging36-20261024-162819)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging36
```

## prove-restore.sh staging36 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging36 (staging36.thaisolitude.com)    export: staging36-before-deep-tissue-evidence-20261024-162714.sql    modified 2026-10-24 16:27:15
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
fingerprints: restored copy 82, real site 82
compared: css 1, option 12, page 13, rows 21, seo 16, snippet 19
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< page	deep-tissue-massage
> page	deep-tissue-massage
```

**RESULT: PASS** - push-deep-tissue-evidence.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-24 16:30:35 UTC_

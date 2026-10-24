# Site op `deep-tissue-apply-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `deep-tissue/push-deep-tissue-copy.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `a87c8442f430ba41d846c683e7bf85f011527f23` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40072035421 |
| started | 2026-10-24 06:54:32 UTC |

## push-deep-tissue-copy.sh --apply(S_TARGET=staging36): PASS

```text
Target site: staging36   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-deep-tissue-copy (outside every web root) ...
[2] Backup on the server + preflight:
staging36 is www/staging36.thaisolitude.com/public_html
fresh database export: private-backups/staging36-before-deep-tissue-copy-20261024-065439.sql (6065123 bytes)
Success: Preflight passed; nothing changed. host=http://staging36.thaisolitude.com state=ready pages_with_slug=1
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: staging36-before-deep-tissue-copy-20261024-065439.sql -> offsite-2026-10/staging36-before-deep-tissue-copy-20261024-065439.sql.age (6066795 bytes, read back identical; plaintext sha256 f10540e25cd1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 1 already there.
[4] Applying:
staging36 is www/staging36.thaisolitude.com/public_html
Success: Page #1091 (/deep-tissue-massage/): the research sentence now says "people with neck pain linked to tender spots in the muscles". Before-snapshot: /home/<ssh-user>/private-backups/deep-tissue-copy-before-20261024-065451.json
SiteGround cache purged
[4b] Does the public page show the link?
staging36 is www/staging36.thaisolitude.com/public_html
public check: https://staging36.thaisolitude.com/deep-tissue-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
public check: the page shows the plain-words sentence once, no 'myofascial', the [1] link and the licence number
[5] Copying the new before-snapshot to this computer:
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: deep-tissue-copy-before-20261024-065451.json -> offsite-2026-10/deep-tissue-copy-before-20261024-065451.json.age (29557 bytes, read back identical; plaintext sha256 59ad01926eaa...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 2 already there.
```

## post-change-test.sh staging36 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging36 (https://staging36.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-24 06:55 UTC
the site answered (200) for https://staging36.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  php -l (pinned image)
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-065546/reference
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-065546/candidate
page-views compared: 20 | pixel-identical: 18 | different: 2 | present in only one set: 0
  DIFF deep-tissue-massage-d.png: 0.8288% of pixels differ, in box x 679-1234, y 1074-1252
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 4100), candidate (390, 4127))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging36-20261024-065546)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging36
```

## prove-restore.sh staging36 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging36 (staging36.thaisolitude.com)    export: staging36-before-deep-tissue-copy-20261024-065439.sql    modified 2026-10-24 06:54:40
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

**RESULT: PASS** - push-deep-tissue-copy.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-24 06:57:54 UTC_

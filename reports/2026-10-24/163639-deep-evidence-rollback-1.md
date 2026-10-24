# Site op `deep-evidence-rollback-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `deep-tissue/push-deep-tissue-evidence.sh` `--rollback $HOME/private-backups/deep-tissue-evidence-before-20261024-162727.json` |
| lane | ops (site-ops.yml) |
| commit | `821a6dc914437bd92712c7e54df18417f860efb0` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40112124717 |
| started | 2026-10-24 16:33:51 UTC |

## push-deep-tissue-evidence.sh --rollback $HOME/private-backups/deep-tissue-evidence-before-20261024-162727.json(S_TARGET=staging36): PASS

```text
Target site: staging36   mode: rollback
[1] Connecting and uploading the package to ~/wwm-package-deep-tissue-evidence (outside every web root) ...
[2] Rolling back:
staging36 is www/staging36.thaisolitude.com/public_html
Success: Rollback done: the old page is back and it is byte-identical to the snapshot.
SiteGround cache purged
```

## post-change-test.sh staging36 (pages, structured data, 20 pixel comparisons, PHP log): PASS

```text
Target: staging36 (https://staging36.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-24 16:34 UTC
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-163445/reference
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-163445/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for staging36
```

## prove-restore.sh staging36 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
No export for staging36 in /home/runner/work/_temp/server-backups (run sync-server-backups.sh first).
```

**RESULT: PASS** - push-deep-tissue-evidence.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-24 16:36:39 UTC_

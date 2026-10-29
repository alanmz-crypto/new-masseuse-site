# Site op `portland-gates-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `check-all.sh` `https://staging38.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `bbbb8ccd487641bb65273dfd956f2fbc39074332` on `feat/2026-10-29-portland-meta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40917854297 |
| started | 2026-10-29 17:05:07 UTC |

## check-all.sh https://staging38.thaisolitude.com(S_TARGET=staging38): PASS

```text
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
```

**RESULT: PASS** - check-all.sh succeeded

_finished 2026-10-29 17:06:06 UTC_

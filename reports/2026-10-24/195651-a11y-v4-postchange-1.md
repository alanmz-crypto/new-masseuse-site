# Site op `a11y-v4-postchange-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `post-change-test.sh` `staging36` |
| lane | ops (site-ops.yml) |
| commit | `ff235159eea806fe9e8389105c5400faea390655` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40127364527 |
| started | 2026-10-24 19:54:06 UTC |

## post-change-test.sh staging36(S_TARGET=staging36): FAIL (exit 1)

```text
Target: staging36 (https://staging36.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-24 19:54 UTC
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-195450/reference
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging36-20261024-195450/candidate
page-views compared: 20 | pixel-identical: 18 | different: 2 | present in only one set: 0
  DIFF accessibility-d.png: 1.1561% of pixels differ, in box x 39-1234, y 673-1201
  DIFF accessibility-m.png: page height/width differs (reference (390, 2446), candidate (390, 2473))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging36-20261024-195450)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging36
```

**RESULT: FAIL** - post-change-test.sh failed (exit 1)

_finished 2026-10-24 19:56:51 UTC_

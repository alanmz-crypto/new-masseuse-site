# Site op `live-check-postchange-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `post-change-test.sh` `live` |
| lane | ops (site-ops.yml) |
| commit | `bb76452cbbe68dd7be7af24258081726c9226a41` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40123695792 |
| started | 2026-10-24 19:05:41 UTC |

## post-change-test.sh live(S_TARGET=live): PASS

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-24 19:06 UTC
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261024-190639/candidate
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261024-190639/reference
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for live
```

**RESULT: PASS** - post-change-test.sh succeeded

_finished 2026-10-24 19:08:49 UTC_

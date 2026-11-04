# Site op `ui-progress-postcheck-20261104`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `post-change-test.sh` `staging38` |
| lane | ops (site-ops.yml) |
| commit | `b8363e2ba544fd726280ff6d54cd346d87d60393` on `feat/2026-11-04-reading-progress` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41790247598 |
| started | 2026-11-04 07:09:27 UTC |

## post-change-test.sh staging38(S_TARGET=staging38): PASS

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-04 07:10 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  Thai evidence content matches its builder
PASS  Therapeutic evidence content matches its builder
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Review Bundle A exact fragments and new wording match their builder
PASS  Review Bundle B source values and approved wording match their builder
PASS  Review Bundle C frozen fragments and runtime sources match their builder
PASS  Review Bundle D frozen copy and research fragments match their builder
PASS  Review Bundle E image fragments, prepared WebP and runtime sources match their builder
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
items: candidate 27, reference 27 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /services/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261104-071012/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261104-071012/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

ALL 5 CHECKS PASSED for staging38
```

**RESULT: PASS** - post-change-test.sh succeeded

_finished 2026-11-04 07:12:52 UTC_

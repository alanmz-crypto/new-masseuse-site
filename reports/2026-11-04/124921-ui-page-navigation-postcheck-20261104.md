# Site op `ui-page-navigation-postcheck-20261104`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `post-change-test.sh` `staging38` |
| lane | ops (site-ops.yml) |
| commit | `3dcdf3309568475686859427cf54305c99a178c4` on `feat/2026-11-04-page-navigation` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831845838 |
| started | 2026-11-04 12:46:15 UTC |

## post-change-test.sh staging38(S_TARGET=staging38): FAIL (exit 1)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-04 12:47 UTC
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261104-124700/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261104-124700/candidate
page-views compared: 20 | pixel-identical: 12 | different: 8 | present in only one set: 0
  DIFF deep-tissue-massage-d.png: 0.0184% of pixels differ, in box x 360-580, y 184-196
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 9418), candidate (390, 9446))
  DIFF services-d.png: 0.1068% of pixels differ, in box x 51-900, y 184-196
  DIFF services-m.png: page height/width differs (reference (390, 6098), candidate (390, 6125))
  DIFF thai-massage-d.png: 0.0181% of pixels differ, in box x 360-580, y 184-196
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9564), candidate (390, 9592))
  DIFF therapeutic-massage-d.png: 0.0263% of pixels differ, in box x 379-703, y 184-196
  DIFF therapeutic-massage-m.png: 0.0277% of pixels differ, in box x 116-344, y 179-218
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging38-20261104-124700)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging38
```

**RESULT: FAIL** - post-change-test.sh failed (exit 1)

_finished 2026-11-04 12:49:20 UTC_

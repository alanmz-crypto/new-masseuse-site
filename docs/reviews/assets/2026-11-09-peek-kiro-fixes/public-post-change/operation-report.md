# Site op `peek-kiro-fixes-post-retry-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `post-change-test.sh` `staging39` |
| lane | ops (site-ops.yml) |
| commit | `fdf441d503ab0b6449b3793d0fdda26a7b7d0f19` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42698232542 |
| started | 2026-11-09 21:00:04 UTC |

## post-change-test.sh staging39(S_TARGET=staging39): FAIL (exit 1)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-09 21:00 UTC
the site answered (200) for https://staging39.thaisolitude.com
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
PASS  verify-home.py https://staging39.thaisolitude.com
PASS  verify-client-pages.py https://staging39.thaisolitude.com
PASS  posture-check.py https://staging39.thaisolitude.com

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
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261109-210051/reference
https://staging39.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261109-210051/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2572), candidate (1280, 2570))
  DIFF about-m.png: page height/width differs (reference (390, 3176), candidate (390, 3201))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1680), candidate (1280, 1705))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2766), candidate (390, 2873))
  DIFF contact-d.png: page height/width differs (reference (1280, 2990), candidate (1280, 2988))
  DIFF contact-m.png: page height/width differs (reference (390, 2993), candidate (390, 2992))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5674), candidate (1280, 5779))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 9446), candidate (390, 10117))
  DIFF home-d.png: page height/width differs (reference (1280, 4624), candidate (1280, 4622))
  DIFF home-m.png: page height/width differs (reference (390, 5929), candidate (390, 6095))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1405), candidate (1280, 1430))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2198), candidate (390, 2251))
  DIFF services-d.png: page height/width differs (reference (1280, 3906), candidate (1280, 3904))
  DIFF services-m.png: page height/width differs (reference (390, 6125), candidate (390, 6376))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5774), candidate (1280, 5773))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9592), candidate (390, 10181))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 6426), candidate (1280, 6487))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 10367), candidate (390, 11159))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 6045), candidate (1280, 6103))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7981), candidate (390, 8044))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging39-20261109-210051)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging39
```

**RESULT: FAIL** - post-change-test.sh failed (exit 1)

_finished 2026-11-09 21:03:22 UTC_

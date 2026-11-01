# Site op `review-c-live-postchange-20261101-2`

| | |
|---|---|
| operation | `run` on `live` |
| script | `post-change-test.sh` `live` |
| lane | ops (site-ops.yml) |
| commit | `16c59a6874d59a9994c8995f3ada33101c8c6461` on `codex/2026-11-01-review-c-live-checks` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41336383029 |
| started | 2026-11-01 18:23:49 UTC |

## post-change-test.sh live(S_TARGET=live): FAIL (exit 1)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-11-01 18:24 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

[1] code gates and rendered-page verifiers
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
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Review Bundle A exact fragments and new wording match their builder
PASS  Review Bundle B source values and approved wording match their builder
PASS  Review Bundle C frozen fragments and runtime sources match their builder
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
items: candidate 27, reference 23 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /services/, /thai-massage/
DIFFERENT:
  /: candidate has 6 items, reference 5
  /contact/: candidate has 7 items, reference 6
  /services/: candidate has 7 items, reference 6
  /thai-massage/: candidate has 7 items, reference 6
  -> FAIL

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261101-182436/reference
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261101-182436/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2116), candidate (1280, 2545))
  DIFF about-m.png: page height/width differs (reference (390, 2639), candidate (390, 3122))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1549), candidate (1280, 1680))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2473), candidate (390, 2766))
  DIFF contact-d.png: page height/width differs (reference (1280, 2635), candidate (1280, 2990))
  DIFF contact-m.png: page height/width differs (reference (390, 2518), candidate (390, 2993))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5069), candidate (1280, 5567))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8620), candidate (390, 9190))
  DIFF home-d.png: page height/width differs (reference (1280, 4163), candidate (1280, 4570))
  DIFF home-m.png: page height/width differs (reference (390, 5254), candidate (390, 5848))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1327), candidate (1280, 1405))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2067), candidate (390, 2198))
  DIFF services-d.png: page height/width differs (reference (1280, 2949), candidate (1280, 3906))
  DIFF services-m.png: page height/width differs (reference (390, 4463), candidate (390, 6098))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5250), candidate (1280, 5747))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 8940), candidate (390, 9510))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5624), candidate (1280, 6291))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9424), candidate (390, 10112))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 5990), candidate (1280, 6045))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7801), candidate (390, 7981))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261101-182436)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

2 CHECK(S) FAILED for live
```

**RESULT: FAIL** - post-change-test.sh failed (exit 1)

_finished 2026-11-01 18:26:49 UTC_

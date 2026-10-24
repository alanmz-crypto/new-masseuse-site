# Site op `live-check-deep-chromium-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `deep-tissue/check-deep-tissue-evidence.mjs` `https://thaisolitude.com /tmp/live-dt live` |
| lane | ops (site-ops.yml) |
| commit | `fac5d6cd1b43184692bd55e6db3d2764d08c326d` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40123974166 |
| started | 2026-10-24 19:09:20 UTC |

## check-deep-tissue-evidence.mjs https://thaisolitude.com /tmp/live-dt live(S_TARGET=live): FAIL (exit 1)

```text
FAIL light-1280: HTTP 200  [202]
PASS light-1280: no side-scroll  [0]
FAIL light-1280: the research heading and the FAQ heading appear once each  [0,0]
FAIL light-1280: the 6 FAQ questions, once each, in order  [0]
PASS light-1280: no links inside FAQ answers  [0]
FAIL light-1280: the 5 research panels, in order
FAIL light-1280: first panel open, the others closed
FAIL light-1280: markers numbered 1..N in first-use order and 33 Sources entries  [0 numbers, 0 sources, 0 links]
FAIL light-1280: the new body paragraph is there and the three old sentences are gone  [false,true]
FAIL light-1280: 'myofascial' appears once, inside a verbatim source title  [0,false]
FAIL light-1280: the page's safety sentence appears twice (page text and FAQ answer)  [0]
FAIL light-1280: three pictures, tooltip equal to the alt text
PASS light-1280: no "Deep Tissue Massage by Butsaba" tooltip left
PASS light-1280: click opens a panel  [true,true,false,false,false]
PASS light-1280: click closes it again  [true,false,false,false,false]
PASS light-1280: Enter opens a panel from the keyboard  [true,false,true,false,false]
PASS light-1280: Space closes it again  [true,false,false,false,false]
PASS light-1280: a FAQ question opens, one open at a time  [How Is Deep Tissue Massage Different From Therapeutic Massage?Deep tissue massage uses slo]
PASS light-1280: no side-scroll with every panel open  [0]
PASS light-1280: no script errors
FAIL light-390: HTTP 200  [202]
PASS light-390: no side-scroll  [0]
FAIL light-390: the research heading and the FAQ heading appear once each  [0,0]
FAIL light-390: the 6 FAQ questions, once each, in order  [0]
PASS light-390: no links inside FAQ answers  [0]
FAIL light-390: the 5 research panels, in order
FAIL light-390: first panel open, the others closed
FAIL light-390: markers numbered 1..N in first-use order and 33 Sources entries  [0 numbers, 0 sources, 0 links]
FAIL light-390: the new body paragraph is there and the three old sentences are gone  [false,true]
FAIL light-390: 'myofascial' appears once, inside a verbatim source title  [0,false]
FAIL light-390: the page's safety sentence appears twice (page text and FAQ answer)  [0]
FAIL light-390: three pictures, tooltip equal to the alt text
PASS light-390: no "Deep Tissue Massage by Butsaba" tooltip left
node:internal/modules/run_main:123
    triggerUncaughtException(
    ^

locator.scrollIntoViewIfNeeded: Timeout 30000ms exceeded.
Call log:
  - waiting for locator('details.wp-block-details > summary').nth(1)
    - waiting for "https://thaisolitude.com/.well-known/captcha/?y=ipc:4.236.92.226:1792869066.327&r=%2Fdeep-tissue-massage%2F" navigation to finish...
    - navigated to "https://thaisolitude.com/.well-known/captcha/?y=ipc:4.236.92.226:1792869066.327&r=%2Fdeep-tissue-massage%2F"

    at /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/deep-tissue/check-deep-tissue-evidence.mjs:69:25 {
  log: [
    "  - waiting for locator('details.wp-block-details > summary').nth(1)",
    '    - waiting for "https://thaisolitude.com/.well-known/captcha/?y=ipc:4.236.92.226:1792869066.327&r=%2Fdeep-tissue-massage%2F" navigation to finish...',
    '    - navigated to "https://thaisolitude.com/.well-known/captcha/?y=ipc:4.236.92.226:1792869066.327&r=%2Fdeep-tissue-massage%2F"'
  ],
  name: 'TimeoutError'
}

Node.js v22.23.3
```

**RESULT: FAIL** - check-deep-tissue-evidence.mjs failed (exit 1)

_finished 2026-10-24 19:11:37 UTC_

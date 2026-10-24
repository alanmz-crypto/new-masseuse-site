# Site op `deep-evidence-browser-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `deep-tissue/check-deep-tissue-evidence.mjs` `https://staging36.thaisolitude.com /tmp/ds-shots staging36` |
| lane | ops (site-ops.yml) |
| commit | `82eb4c7789e129adc187dcc6f06e21f6a449874e` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40111949146 |
| started | 2026-10-24 16:31:34 UTC |

## check-deep-tissue-evidence.mjs https://staging36.thaisolitude.com /tmp/ds-shots staging36(S_TARGET=staging36): FAIL (exit 1)

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
node:internal/modules/run_main:123
    triggerUncaughtException(
    ^

locator.scrollIntoViewIfNeeded: Timeout 30000ms exceeded.
Call log:
  - waiting for locator('details.wp-block-details > summary').nth(1)

    at /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/deep-tissue/check-deep-tissue-evidence.mjs:68:25 {
  log: [
    "  - waiting for locator('details.wp-block-details > summary').nth(1)"
  ],
  name: 'TimeoutError'
}

Node.js v22.23.3
```

**RESULT: FAIL** - check-deep-tissue-evidence.mjs failed (exit 1)

_finished 2026-10-24 16:33:07 UTC_

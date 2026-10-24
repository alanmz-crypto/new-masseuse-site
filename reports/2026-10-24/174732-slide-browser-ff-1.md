# Site op `slide-browser-ff-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `research-slide/check-research-slide.mjs` `https://staging36.thaisolitude.com /tmp/slide-ff staging36-ff firefox` |
| lane | ops (site-ops.yml) |
| commit | `b724b146ffe0cc4069fa55e08dfdf1bb76bc297a` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40116535162 |
| started | 2026-10-24 17:32:00 UTC |

## check-research-slide.mjs https://staging36.thaisolitude.com /tmp/slide-ff staging36-ff firefox(S_TARGET=staging36): PASS

```text
PASS /thai-massage/ 1280px: HTTP 200  [200]
PASS /thai-massage/ 1280px: research panels found  [5]
PASS /thai-massage/ 1280px: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 1280px: the panel opens to its full height  [54 -> 429]
INFO /thai-massage/ 1280px: this browser cannot animate the panel; it opens at once (429,429,429,429)
PASS /thai-massage/ 1280px: the panel closes back to its closed height  [429 -> 54]
PASS /thai-massage/ 1280px: no script errors
PASS /therapeutic-massage/ 1280px: HTTP 200  [200]
PASS /therapeutic-massage/ 1280px: research panels found  [5]
PASS /therapeutic-massage/ 1280px: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 1280px: the panel opens to its full height  [54 -> 538]
INFO /therapeutic-massage/ 1280px: this browser cannot animate the panel; it opens at once (538,538,538,538)
PASS /therapeutic-massage/ 1280px: the panel closes back to its closed height  [538 -> 54]
PASS /therapeutic-massage/ 1280px: no script errors
PASS /deep-tissue-massage/ 1280px: HTTP 200  [200]
PASS /deep-tissue-massage/ 1280px: research panels found  [5]
PASS /deep-tissue-massage/ 1280px: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 1280px: the panel opens to its full height  [54 -> 782]
INFO /deep-tissue-massage/ 1280px: this browser cannot animate the panel; it opens at once (782,782,782,782)
PASS /deep-tissue-massage/ 1280px: the panel closes back to its closed height  [782 -> 54]
PASS /deep-tissue-massage/ 1280px: no script errors
PASS /thai-massage/ 390px: HTTP 200  [200]
PASS /thai-massage/ 390px: research panels found  [5]
PASS /thai-massage/ 390px: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 390px: the panel opens to its full height  [52 -> 1270]
INFO /thai-massage/ 390px: this browser cannot animate the panel; it opens at once (1270,1270,1270,1270)
PASS /thai-massage/ 390px: the panel closes back to its closed height  [1270 -> 52]
PASS /thai-massage/ 390px: no side-scroll with every panel open  [0]
PASS /thai-massage/ 390px: no script errors
PASS /therapeutic-massage/ 390px: HTTP 200  [200]
PASS /therapeutic-massage/ 390px: research panels found  [5]
PASS /therapeutic-massage/ 390px: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 390px: the panel opens to its full height  [52 -> 1705]
INFO /therapeutic-massage/ 390px: this browser cannot animate the panel; it opens at once (1705,1705,1705,1705)
PASS /therapeutic-massage/ 390px: the panel closes back to its closed height  [1705 -> 52]
PASS /therapeutic-massage/ 390px: no side-scroll with every panel open  [0]
PASS /therapeutic-massage/ 390px: no script errors
PASS /deep-tissue-massage/ 390px: HTTP 200  [200]
PASS /deep-tissue-massage/ 390px: research panels found  [5]
PASS /deep-tissue-massage/ 390px: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 390px: the panel opens to its full height  [52 -> 2521]
INFO /deep-tissue-massage/ 390px: this browser cannot animate the panel; it opens at once (2521,2521,2521,2521)
PASS /deep-tissue-massage/ 390px: the panel closes back to its closed height  [2521 -> 52]
PASS /deep-tissue-massage/ 390px: no side-scroll with every panel open  [0]
PASS /deep-tissue-massage/ 390px: no script errors
PASS /thai-massage/ 1280px reduced-motion: HTTP 200  [200]
PASS /thai-massage/ 1280px reduced-motion: research panels found  [5]
PASS /thai-massage/ 1280px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 1280px reduced-motion: the panel opens to its full height  [54 -> 429]
PASS /thai-massage/ 1280px reduced-motion: reduced motion: no in-between heights while opening  [429,429,429,429]
PASS /thai-massage/ 1280px reduced-motion: the panel closes back to its closed height  [429 -> 54]
PASS /thai-massage/ 1280px reduced-motion: reduced motion: no in-between heights while closing  [54,54,54,54]
PASS /thai-massage/ 1280px reduced-motion: no script errors
PASS /therapeutic-massage/ 1280px reduced-motion: HTTP 200  [200]
PASS /therapeutic-massage/ 1280px reduced-motion: research panels found  [5]
PASS /therapeutic-massage/ 1280px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 1280px reduced-motion: the panel opens to its full height  [54 -> 538]
PASS /therapeutic-massage/ 1280px reduced-motion: reduced motion: no in-between heights while opening  [538,538,538,538]
PASS /therapeutic-massage/ 1280px reduced-motion: the panel closes back to its closed height  [538 -> 54]
PASS /therapeutic-massage/ 1280px reduced-motion: reduced motion: no in-between heights while closing  [54,54,54,54]
PASS /therapeutic-massage/ 1280px reduced-motion: no script errors
PASS /deep-tissue-massage/ 1280px reduced-motion: HTTP 200  [200]
PASS /deep-tissue-massage/ 1280px reduced-motion: research panels found  [5]
PASS /deep-tissue-massage/ 1280px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 1280px reduced-motion: the panel opens to its full height  [54 -> 782]
PASS /deep-tissue-massage/ 1280px reduced-motion: reduced motion: no in-between heights while opening  [782,782,782,782]
PASS /deep-tissue-massage/ 1280px reduced-motion: the panel closes back to its closed height  [782 -> 54]
PASS /deep-tissue-massage/ 1280px reduced-motion: reduced motion: no in-between heights while closing  [54,54,54,54]
PASS /deep-tissue-massage/ 1280px reduced-motion: no script errors
PASS /thai-massage/ 390px reduced-motion: HTTP 200  [200]
PASS /thai-massage/ 390px reduced-motion: research panels found  [5]
PASS /thai-massage/ 390px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 390px reduced-motion: the panel opens to its full height  [52 -> 1270]
PASS /thai-massage/ 390px reduced-motion: reduced motion: no in-between heights while opening  [1270,1270,1270,1270]
PASS /thai-massage/ 390px reduced-motion: the panel closes back to its closed height  [1270 -> 52]
PASS /thai-massage/ 390px reduced-motion: reduced motion: no in-between heights while closing  [52,52,52,52]
PASS /thai-massage/ 390px reduced-motion: no script errors
PASS /therapeutic-massage/ 390px reduced-motion: HTTP 200  [200]
PASS /therapeutic-massage/ 390px reduced-motion: research panels found  [5]
PASS /therapeutic-massage/ 390px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 390px reduced-motion: the panel opens to its full height  [52 -> 1705]
PASS /therapeutic-massage/ 390px reduced-motion: reduced motion: no in-between heights while opening  [1705,1705,1705,1705]
PASS /therapeutic-massage/ 390px reduced-motion: the panel closes back to its closed height  [1705 -> 52]
PASS /therapeutic-massage/ 390px reduced-motion: reduced motion: no in-between heights while closing  [52,52,52,52]
PASS /therapeutic-massage/ 390px reduced-motion: no script errors
PASS /deep-tissue-massage/ 390px reduced-motion: HTTP 200  [200]
PASS /deep-tissue-massage/ 390px reduced-motion: research panels found  [5]
PASS /deep-tissue-massage/ 390px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 390px reduced-motion: the panel opens to its full height  [52 -> 2521]
PASS /deep-tissue-massage/ 390px reduced-motion: reduced motion: no in-between heights while opening  [2521,2521,2521,2521]
PASS /deep-tissue-massage/ 390px reduced-motion: the panel closes back to its closed height  [2521 -> 52]
PASS /deep-tissue-massage/ 390px reduced-motion: reduced motion: no in-between heights while closing  [52,52,52,52]
PASS /deep-tissue-massage/ 390px reduced-motion: no script errors

RESULT: all checks passed
```

**RESULT: PASS** - check-research-slide.mjs succeeded

_finished 2026-10-24 17:47:31 UTC_

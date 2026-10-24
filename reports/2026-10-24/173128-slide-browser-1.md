# Site op `slide-browser-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `research-slide/check-research-slide.mjs` `https://staging36.thaisolitude.com /tmp/slide-shots staging36` |
| lane | ops (site-ops.yml) |
| commit | `0833d69a09a8723d441d108692b0dad1947a0fda` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40116335724 |
| started | 2026-10-24 17:29:43 UTC |

## check-research-slide.mjs https://staging36.thaisolitude.com /tmp/slide-shots staging36(S_TARGET=staging36): PASS

```text
PASS /thai-massage/ 1280px: HTTP 200  [200]
PASS /thai-massage/ 1280px: research panels found  [5]
PASS /thai-massage/ 1280px: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 1280px: the panel opens to its full height  [54 -> 428]
PASS /thai-massage/ 1280px: slides open (in-between heights while opening)  [118,345,428,428]
PASS /thai-massage/ 1280px: the panel closes back to its closed height  [428 -> 54]
PASS /thai-massage/ 1280px: slides shut (in-between heights while closing)  [395,158,56,54]
PASS /thai-massage/ 1280px: no script errors
PASS /therapeutic-massage/ 1280px: HTTP 200  [200]
PASS /therapeutic-massage/ 1280px: research panels found  [5]
PASS /therapeutic-massage/ 1280px: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 1280px: the panel opens to its full height  [54 -> 537]
PASS /therapeutic-massage/ 1280px: slides open (in-between heights while opening)  [96,402,534,537]
PASS /therapeutic-massage/ 1280px: the panel closes back to its closed height  [537 -> 54]
PASS /therapeutic-massage/ 1280px: slides shut (in-between heights while closing)  [495,189,56,54]
PASS /therapeutic-massage/ 1280px: no script errors
PASS /deep-tissue-massage/ 1280px: HTTP 200  [200]
PASS /deep-tissue-massage/ 1280px: research panels found  [5]
PASS /deep-tissue-massage/ 1280px: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 1280px: the panel opens to its full height  [54 -> 782]
PASS /deep-tissue-massage/ 1280px: slides open (in-between heights while opening)  [118,579,778,782]
PASS /deep-tissue-massage/ 1280px: the panel closes back to its closed height  [782 -> 54]
PASS /deep-tissue-massage/ 1280px: slides shut (in-between heights while closing)  [718,257,58,54]
PASS /deep-tissue-massage/ 1280px: no script errors
PASS /thai-massage/ 390px: HTTP 200  [200]
PASS /thai-massage/ 390px: research panels found  [5]
PASS /thai-massage/ 390px: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 390px: the panel opens to its full height  [52 -> 1269]
PASS /thai-massage/ 390px: slides open (in-between heights while opening)  [158,929,1262,1269]
PASS /thai-massage/ 390px: the panel closes back to its closed height  [1269 -> 52]
PASS /thai-massage/ 390px: slides shut (in-between heights while closing)  [1231,390,58,52]
PASS /thai-massage/ 390px: no side-scroll with every panel open  [0]
PASS /thai-massage/ 390px: no script errors
PASS /therapeutic-massage/ 390px: HTTP 200  [200]
PASS /therapeutic-massage/ 390px: research panels found  [5]
PASS /therapeutic-massage/ 390px: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 390px: the panel opens to its full height  [52 -> 1704]
PASS /therapeutic-massage/ 390px: slides open (in-between heights while opening)  [197,1244,1695,1704]
PASS /therapeutic-massage/ 390px: the panel closes back to its closed height  [1704 -> 52]
PASS /therapeutic-massage/ 390px: slides shut (in-between heights while closing)  [1559,512,60,52]
PASS /therapeutic-massage/ 390px: no side-scroll with every panel open  [0]
PASS /therapeutic-massage/ 390px: no script errors
PASS /deep-tissue-massage/ 390px: HTTP 200  [200]
PASS /deep-tissue-massage/ 390px: research panels found  [5]
PASS /deep-tissue-massage/ 390px: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 390px: the panel opens to its full height  [52 -> 2519]
PASS /deep-tissue-massage/ 390px: slides open (in-between heights while opening)  [268,1832,2507,2519]
PASS /deep-tissue-massage/ 390px: the panel closes back to its closed height  [2519 -> 52]
PASS /deep-tissue-massage/ 390px: slides shut (in-between heights while closing)  [2303,739,64,52]
PASS /deep-tissue-massage/ 390px: no side-scroll with every panel open  [0]
PASS /deep-tissue-massage/ 390px: no script errors
PASS /thai-massage/ 1280px reduced-motion: HTTP 200  [200]
PASS /thai-massage/ 1280px reduced-motion: research panels found  [5]
PASS /thai-massage/ 1280px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /thai-massage/ 1280px reduced-motion: the panel opens to its full height  [54 -> 428]
PASS /thai-massage/ 1280px reduced-motion: reduced motion: no in-between heights while opening  [428,428,428,428]
PASS /thai-massage/ 1280px reduced-motion: the panel closes back to its closed height  [428 -> 54]
PASS /thai-massage/ 1280px reduced-motion: reduced motion: no in-between heights while closing  [54,54,54,54]
PASS /thai-massage/ 1280px reduced-motion: no script errors
PASS /therapeutic-massage/ 1280px reduced-motion: HTTP 200  [200]
PASS /therapeutic-massage/ 1280px reduced-motion: research panels found  [5]
PASS /therapeutic-massage/ 1280px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 1280px reduced-motion: the panel opens to its full height  [54 -> 537]
PASS /therapeutic-massage/ 1280px reduced-motion: reduced motion: no in-between heights while opening  [537,537,537,537]
PASS /therapeutic-massage/ 1280px reduced-motion: the panel closes back to its closed height  [537 -> 54]
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
PASS /thai-massage/ 390px reduced-motion: the panel opens to its full height  [52 -> 1269]
PASS /thai-massage/ 390px reduced-motion: reduced motion: no in-between heights while opening  [1269,1269,1269,1269]
PASS /thai-massage/ 390px reduced-motion: the panel closes back to its closed height  [1269 -> 52]
PASS /thai-massage/ 390px reduced-motion: reduced motion: no in-between heights while closing  [52,52,52,52]
PASS /thai-massage/ 390px reduced-motion: no script errors
PASS /therapeutic-massage/ 390px reduced-motion: HTTP 200  [200]
PASS /therapeutic-massage/ 390px reduced-motion: research panels found  [5]
PASS /therapeutic-massage/ 390px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /therapeutic-massage/ 390px reduced-motion: the panel opens to its full height  [52 -> 1704]
PASS /therapeutic-massage/ 390px reduced-motion: reduced motion: no in-between heights while opening  [1704,1704,1704,1704]
PASS /therapeutic-massage/ 390px reduced-motion: the panel closes back to its closed height  [1704 -> 52]
PASS /therapeutic-massage/ 390px reduced-motion: reduced motion: no in-between heights while closing  [52,52,52,52]
PASS /therapeutic-massage/ 390px reduced-motion: no script errors
PASS /deep-tissue-massage/ 390px reduced-motion: HTTP 200  [200]
PASS /deep-tissue-massage/ 390px reduced-motion: research panels found  [5]
PASS /deep-tissue-massage/ 390px reduced-motion: links inside closed panels are hidden (not focusable)
PASS /deep-tissue-massage/ 390px reduced-motion: the panel opens to its full height  [52 -> 2519]
PASS /deep-tissue-massage/ 390px reduced-motion: reduced motion: no in-between heights while opening  [2519,2519,2519,2519]
PASS /deep-tissue-massage/ 390px reduced-motion: the panel closes back to its closed height  [2519 -> 52]
PASS /deep-tissue-massage/ 390px reduced-motion: reduced motion: no in-between heights while closing  [52,52,52,52]
PASS /deep-tissue-massage/ 390px reduced-motion: no script errors

RESULT: all checks passed
```

**RESULT: PASS** - check-research-slide.mjs succeeded

_finished 2026-10-24 17:31:27 UTC_

# Site op `a11y-contrast-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `accessibility/check-research-contrast.mjs` `https://staging36.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `2b45885e008ec69dbb36ed64da68382536b1eb4c` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40125443470 |
| started | 2026-10-24 19:28:35 UTC |

## check-research-contrast.mjs https://staging36.thaisolitude.com(S_TARGET=staging36): FAIL (exit 1)

```text
FAIL /thai-massage/ light 1280px: HTTP 200  [202]
FAIL /therapeutic-massage/ light 1280px: HTTP 200  [202]
FAIL /deep-tissue-massage/ light 1280px: HTTP 200  [202]
FAIL /thai-massage/ light 390px: HTTP 200  [202]
FAIL /therapeutic-massage/ light 390px: HTTP 200  [202]
FAIL /deep-tissue-massage/ light 390px: HTTP 200  [202]
FAIL /thai-massage/ dark 1280px: HTTP 200  [202]
FAIL /therapeutic-massage/ dark 1280px: HTTP 200  [202]
FAIL /deep-tissue-massage/ dark 1280px: HTTP 200  [202]
FAIL /thai-massage/ dark 390px: HTTP 200  [202]
FAIL /therapeutic-massage/ dark 390px: HTTP 200  [202]
FAIL /deep-tissue-massage/ dark 390px: HTTP 200  [202]

RESULT: 12 check(s) FAILED
```

**RESULT: FAIL** - check-research-contrast.mjs failed (exit 1)

_finished 2026-10-24 19:29:56 UTC_

# Site op `a11y-contrast-3`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `accessibility/check-research-contrast.mjs` `https://staging36.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `a1eabbbdab743b6c9777da6a891e138f8a041618` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40126520833 |
| started | 2026-10-24 19:42:40 UTC |

## check-research-contrast.mjs https://staging36.thaisolitude.com(S_TARGET=staging36): PASS

```text
PASS /thai-massage/ light 1280px: 116 text elements measured, lowest 8.73:1 (p "Research on traditional Thai massage is "), 0 not measurable
PASS /therapeutic-massage/ light 1280px: 119 text elements measured, lowest 8.73:1 (p "Research on massage therapy is large but"), 0 not measurable
PASS /deep-tissue-massage/ light 1280px: 137 text elements measured, lowest 8.73:1 (p "Research on deep tissue massage is limit"), 0 not measurable
PASS /thai-massage/ light 390px: 116 text elements measured, lowest 8.73:1 (p "Research on traditional Thai massage is "), 0 not measurable
PASS /therapeutic-massage/ light 390px: 119 text elements measured, lowest 8.73:1 (p "Research on massage therapy is large but"), 0 not measurable
PASS /deep-tissue-massage/ light 390px: 137 text elements measured, lowest 8.73:1 (p "Research on deep tissue massage is limit"), 0 not measurable
PASS /thai-massage/ dark 1280px: 116 text elements measured, lowest 11.36:1 (summary "Reviews, and What They Say About Limits"), 0 not measurable
PASS /therapeutic-massage/ dark 1280px: 119 text elements measured, lowest 11.36:1 (summary "Reviews, and What They Say About Limits"), 0 not measurable
PASS /deep-tissue-massage/ dark 1280px: 137 text elements measured, lowest 11.36:1 (summary "Reviews, and What They Say About Limits"), 0 not measurable
PASS /thai-massage/ dark 390px: 116 text elements measured, lowest 11.36:1 (summary "Reviews, and What They Say About Limits"), 0 not measurable
PASS /therapeutic-massage/ dark 390px: 119 text elements measured, lowest 11.36:1 (summary "Reviews, and What They Say About Limits"), 0 not measurable
PASS /deep-tissue-massage/ dark 390px: 137 text elements measured, lowest 11.36:1 (summary "Reviews, and What They Say About Limits"), 0 not measurable

RESULT: all checks passed
```

**RESULT: PASS** - check-research-contrast.mjs succeeded

_finished 2026-10-24 19:44:06 UTC_

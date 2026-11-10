# Site op `peek-rail-input-firefox-hold-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/rail-input/check-keyboard.mjs` `https://staging39.thaisolitude.com --firefox --protocol-diagnostic --hold-history-diagnostic --capture-har` |
| lane | ops (site-ops.yml) |
| commit | `8840150a89b5f0b5f723482ca9cbdfe1a7803ccd` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42736953507 |
| started | 2026-11-10 01:36:34 UTC |

## check-keyboard.mjs https://staging39.thaisolitude.com --firefox --protocol-diagnostic --hold-history-diagnostic --capture-har(S_TARGET=staging39): FAIL (exit 2)

```text
INVALID rail input firefox: INVALID rail page: expected same-origin HTML200, got202
INFO Rail input hold history diagnostic firefox: 0 passed, 0 failed; 1 page loads, 0 captures, 1s; no physical-device or assistive-technology claim.
```

**RESULT: INVALID** - check-keyboard.mjs could not test (exit 2, see its output; usually the SiteGround bot challenge): nothing was tested, run it once more later

_finished 2026-11-10 01:37:39 UTC_

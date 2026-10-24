# Site op `deep-evidence-probe-1`

| | |
|---|---|
| operation | `run` on `staging36` |
| script | `deep-tissue/probe-covered.mjs` `https://staging36.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `b1ff3e46be4f5395caafe5dafb0b37a6bfcacd28` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40113388831 |
| started | 2026-10-24 16:50:26 UTC |

## probe-covered.mjs https://staging36.thaisolitude.com(S_TARGET=staging36): FAIL (exit 1)

```text
node:internal/modules/run_main:123
    triggerUncaughtException(
    ^

page.evaluate: Execution context was destroyed, most likely because of a navigation
    at /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/deep-tissue/probe-covered.mjs:15:24 {
  log: [],
  name: 'Error'
}

Node.js v22.23.3
```

**RESULT: FAIL** - probe-covered.mjs failed (exit 1)

_finished 2026-10-24 16:51:10 UTC_

# Site op `review-c-csp-staging38-20261101`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `review-c/check-csp.mjs` `https://staging38.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `b4de86158572ebf3be596a095744bb33b8b71538` on `codex/2026-11-01-review-bundle-c` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41294973273 |
| started | 2026-11-01 09:52:24 UTC |

## check-csp.mjs https://staging38.thaisolitude.com(S_TARGET=staging38): PASS

```text
Content-Security-Policy header the SERVER really sent, over 2 page-views (chromium):
    2 x default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://maps.gstatic.com; style-src 's ...
violations across 2 page-views: 0
Content-Security-Policy header the SERVER really sent, over 2 page-views (firefox):
    2 x default-src 'self'; script-src 'self' 'unsafe-inline' https://maps.googleapis.com https://maps.gstatic.com; style-src 's ...
violations across 2 page-views: 0
```

**RESULT: PASS** - check-csp.mjs succeeded

_finished 2026-11-01 09:53:23 UTC_

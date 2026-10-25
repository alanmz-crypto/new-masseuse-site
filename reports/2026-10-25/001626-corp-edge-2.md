# Site op `corp-edge-2`

| | |
|---|---|
| operation | `run` on `staging37` |
| script | `posture/edge-check.sh` `https://staging37.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `562e34b2e8f5333165e305ed6b17fb791a9345e4` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40146035307 |
| started | 2026-10-25 00:16:24 UTC |

## edge-check.sh https://staging37.thaisolitude.com(S_TARGET=staging37): FAIL (exit 2)

```text
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging37.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
```

**RESULT: FAIL** - edge-check.sh failed (exit 2)

_finished 2026-10-25 00:16:25 UTC_

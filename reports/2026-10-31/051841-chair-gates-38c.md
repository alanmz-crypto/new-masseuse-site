# Site op `chair-gates-38c`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `check-all.sh` `https://staging38.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `e83236ae7ce644bf11b76ef13553aa7bf0fdfaf9` on `feat/2026-10-30-chair-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41172027526 |
| started | 2026-10-31 05:18:08 UTC |

## check-all.sh https://staging38.thaisolitude.com(S_TARGET=staging38): FAIL (exit 1)

```text
PASS  php -l (pinned image)
PASS  bash -n
PASS  shellcheck, work-with-me-employer (warnings fatal)
PASS  shellcheck, all scripts (errors fatal)
PASS  python syntax
PASS  ruff (syntax and undefined names)
PASS  staging installers match their localhost originals; no production host in any allow-list
PASS  structured-data snippet and guard (offline test)
PASS  headers snippet and CSP (offline test)
PASS  posture check self-test (mock site and deliberate breaks)
PASS  wording tiers self-test (deliberate breaks, mutation-checked)
PASS  Thai evidence content matches its builder
PASS  Therapeutic evidence content matches its builder
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Chair Massage tinted pictures match their generator
FAIL  the site is not reachable for testing: BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging38.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
SKIP  rendered-page verifiers and posture check (not run: see the line above (INVALID, not a failure of the site))

1 GATE(S) FAILED
```

**RESULT: FAIL** - check-all.sh failed (exit 1)

_finished 2026-10-31 05:18:41 UTC_

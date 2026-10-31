# Site op `chair-gates-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `check-all.sh` `https://staging38.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `8105fe34ae3e050c17010df0025bbca70a54098c` on `feat/2026-10-30-chair-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41171193915 |
| started | 2026-10-31 05:05:55 UTC |

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
FAIL  Chair Massage tinted pictures match their generator
        Traceback (most recent call last):
          File "/home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/chair-massage/make-tints.py", line 15, in <module>
            from PIL import Image
        ModuleNotFoundError: No module named 'PIL'
PASS  verify-home.py https://staging38.thaisolitude.com
PASS  verify-client-pages.py https://staging38.thaisolitude.com
PASS  posture-check.py https://staging38.thaisolitude.com

1 GATE(S) FAILED
```

**RESULT: FAIL** - check-all.sh failed (exit 1)

_finished 2026-10-31 05:06:48 UTC_

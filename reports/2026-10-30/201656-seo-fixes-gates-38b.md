# Site op `seo-fixes-gates-38b`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `check-all.sh` `https://staging38.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `565ad4022422699741ec5a47729a6229e255f84e` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41121792585 |
| started | 2026-10-30 20:16:06 UTC |

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
PASS  verify-home.py https://staging38.thaisolitude.com
FAIL  verify-client-pages.py https://staging38.thaisolitude.com
        PASS Services returns 200  [200]
        PASS Services title  [Massage Services | Butsaba Dusrai, Oregon LMT]
        FAIL Services meta description  [Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Portland. Every session starts with your goals.]
        FAIL Services og/twitter title and description follow
        PASS Services description fits one snippet (<= 160) and names only services the page lists  [129]
        PASS Services title and descriptions carry no restricted wording
        PASS Therapeutic Massage returns 200  [200]
        PASS page body found
        PASS neck-and-shoulders FAQ present once (question and answer)
        PASS old headache FAQ gone from the rendered page
        PASS Therapeutic Massage: no restricted wording outside the research section
        PASS Therapeutic Massage research section: no claim verbs, no we/our/us/studio
        PASS Therapeutic Massage source titles: no we/our/us/studio
        PASS Therapeutic Massage: FAQ question 'What Is the Difference Between Therapeutic and Relaxation Massage?' appears once
        PASS Therapeutic Massage: FAQ question 'How Is a Therapeutic Session Planned?' appears once
        PASS Therapeutic Massage: FAQ question 'Will I Feel the Results Right Away?' appears once
        PASS Therapeutic Massage: FAQ question 'How Often Should I Come In for Therapeutic Massage?' appears once
        PASS Therapeutic Massage: FAQ question 'Is Therapeutic Massage Appropriate If I Have No Specific Complaint?' appears once
        PASS Therapeutic Massage: FAQ question 'Can I Ask You to Focus on My Neck and Shoulders?' appears once
        PASS Therapeutic Massage: FAQ question 'How Is Therapeutic Massage Different From Deep Tissue Massage?' appears once
        PASS Therapeutic Massage: FAQ question 'Can I Change the Pressure During a Session?' appears once
        PASS Therapeutic Massage: FAQ question 'Where Do Sessions Take Place?' appears once
        PASS Therapeutic Massage: FAQ question 'How Do I Book a Session?' appears once
        PASS Therapeutic Massage: 'Yes. You can ask for lighter pressure, a different focus' once
        PASS Therapeutic Massage: 'Deep tissue massage uses focused strokes and sustained p' once
sed: couldn't write 208 items to stdout: Broken pipe
PASS  posture-check.py https://staging38.thaisolitude.com

1 GATE(S) FAILED
```

**RESULT: FAIL** - check-all.sh failed (exit 1)

_finished 2026-10-30 20:16:56 UTC_

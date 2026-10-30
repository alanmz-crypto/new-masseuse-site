# Site op `seo-fixes-test-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `post-change-test.sh` `staging38` |
| lane | ops (site-ops.yml) |
| commit | `be703d5b6e1dde762bc9003c9420d2ff6b493f8d` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41120987723 |
| started | 2026-10-30 20:09:24 UTC |

## post-change-test.sh staging38(S_TARGET=staging38): FAIL (exit 1)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-30 20:10 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
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
sed: couldn't write 178 items to stdout: Broken pipe
PASS  posture-check.py https://staging38.thaisolitude.com

1 GATE(S) FAILED
  -> FAIL

[2] pages answer 200
  200 /
  200 /about/
  200 /services/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /wp-sitemap.xml
  200 /robots.txt
  -> PASS

[3] structured data vs the reference
items: candidate 23, reference 23 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /contact/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261030-201005/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261030-201005/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging38
```

**RESULT: FAIL** - post-change-test.sh failed (exit 1)

_finished 2026-10-30 20:12:13 UTC_

# Review Bundle B — staging38 verification

Bundle B is applied on staging38. PR:
https://github.com/alanmz-crypto/new-masseuse-site/pull/63
Live is disabled. No production change or live request was made.

Ryan approved the displayed 1200×630 card on 2026-11-01: **“Yes, use this card.”**
It is assigned to SureRank's global fallback and Home social image. Existing
images remain. Source/approved PNG:
`scripts/work-with-me-employer/review-b/assets/thai-solitude-share-preview.png`.
SHA256 `7b769b1a2b82b2cb3ef2bf11a5e6ba65f271b963952fdfe1041c53b6ade5319d`.
Staging attachment **1314**: [approved card](https://staging38.thaisolitude.com/wp-content/uploads/2026/11/thai-solitude-share-card.png).

| Evidence | Result |
|---|---|
| Fresh staging38 capture and frozen source values | PASS; archive checksums in package README |
| Offline and GitHub Gates | PASS |
| Uploads rehearsal `review-b-rehearse-staging38-20261101-2` | PASS: apply, repeat apply, exact rollback, re-apply; rendered Home/client gates PASS; no new PHP errors |
| Dry run `review-b-dry-staging38-20261101` | PASS; every part ready |
| Apply `review-b-apply-staging38-20261101` | PASS; ten changed pages answer 200 with Person/licence |
| Post-change rendered gates and security posture | PASS; all code gates pass |
| Structured data versus live | Expected difference on all four compared pages: one newly enabled global Person, plus enriched LocalBusiness; no JSON parse errors |
| Pixel comparison versus live | Expected differences on 14 views; six identical; zero missing captures |
| Restore proof | Backup imported successfully; 88 restored/90 current fingerprints; only intended page, metadata, options, snippet and related rows/cache differ |
| Strict SEO/entity/share-card scan | PASS: all 12 sitemap pages answer 200; zero expectation failures; approved card SHA/dimensions and Home OG/Twitter match |
| Home desktop/phone visual check | PASS: two captures visually reviewed, text fits; scrollWidth=clientWidth at 1280/390 px |

Reports on `ops-log`:

- [Rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/084347-review-b-rehearse-staging38-20261101-2.md)
- [Dry run](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/084435-review-b-dry-staging38-20261101.md)
- [Apply, automatic tests and restore proof](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/085012-review-b-apply-staging38-20261101.md)
- [Strict semantic scan](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/085100-review-b-seo-staging38-20261101.md)
- [Home desktop/phone captures](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/085229-review-b-home-shots-staging38-20261101.md)

Bundle B changes six titles; Services, Corporate, Privacy and Accessibility
descriptions and social copies; the Corporate HTTPS share URL; About/Contact
Organization phone/languages; the existing global Person; LocalBusinessInfo;
the tagline; Home's “by arrangement” wording; and the approved share card.
The first rehearsal refused a mixed state before apply: the Corporate HTTP→HTTPS
change is a no-op on HTTP localhost. Treating that matching no-op as neutral
fixed the preflight; rehearsal2 then passed with uploads. No staging write was
made by the failed rehearsal.

No opening hours or new service facts were supplied. The five unanswered
Butsaba questions remain pending; her four received answers are already merged
in PR #62. Their website consistency follow-up is separate.

Corporate's exact approved description has **162 characters**. Its verifier
exception is limited to that sentence; other descriptions retain the 160 limit.
The Home card builder and generated content move together. Older builders and
installers describe earlier exact states; roll B back before A or earlier
exact-state rollbacks on the resources B touches.

The comparison against live includes Bundle A, which is still only on staging.
All compared page heights equal the Bundle A record;
Bundle B adds no page height at 1280 or 390 px. Home has small pixel differences
from its revised card links and “by arrangement” text. The other 12 changed
views are About, Contact, Services, Thai, Therapeutic and Deep Tissue at both
widths. Work With Me, Privacy and Accessibility remain pixel-identical in all
six views. There are no new PHP errors, warnings or deprecations from site code.
The automatic comparison exits nonzero for expected entity/pixel differences;
this is not a claim that the candidate is identical to live.

Backup: `staging38-before-bundle-b-20261101-084559.sql` (5,921,174 bytes),
copied off-server with identical SHA256, encrypted into `offsite-2026-11` and
read back identically **before apply**. Snapshot
`bundle-b-before-20261101-084613.json` was also copied, encrypted and verified.

Exact staging rollback through Actions:

```sh
scripts/ci/request.sh run --target staging38 --script review-b/push-bundle-b.sh --args '--rollback $HOME/private-backups/bundle-b-before-20261101-084613.json'
```

Rollback takes a fresh export and verified off-site copy, then requires all
captured page content, SureRank rows, both options and snippet to remain exactly
applied. The snapshot additionally guards the new attachment's raw post/meta
and original/derived files. It restores old settings first, then removes only
that snapshot's unchanged new attachment. It retains any pre-existing identical
asset. Rehearsal proved exact restoration and re-import/re-apply. No staging
rollback or existing-media deletion occurred after the successful apply.

Git CLI authentication is unavailable in this cloud session. All required data
and publication were accessible through the connected GitHub tools. Equivalent
house-protocol Actions JSON requests and leased branch updates were used; no
credential was read or changed. PR #62's merged notes were integrated with a
second parent; PR #63 has no merge conflict.

Next: Ryan reviews staging38 and PR #63. His explicit live go is required before
any live-enable/request follow-up; he alone merges and starts Site ops (LIVE).
Bundle C can proceed independently of the remaining Butsaba answers. Bundle G
still waits until November 14 and covers 15 booking-date sentences after A.

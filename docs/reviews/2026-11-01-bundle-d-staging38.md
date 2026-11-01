# Bundle D: staging38 wording and research fidelity

**Status:** APPLIED on staging38, 2026-11-01 10:45 UTC. Draft PR #65 is stacked on C draft PR #64. Live remains disabled and unchanged. Final whole-site interactive public verification remains pending because of SiteGround’s intermittent robot challenge. The post-D runner’s rendered gates and 20 public screenshot views succeeded; that does not prove a durable hosting fix. No extra public retry loop.

Stacked on Bundle C, draft PR #64; Bundle B PR #63 is merged. The pre-D capture’s nine affected page hashes matched applied C. Source capture PASS: `reports/2026-11-01/102012-review-d-capture-staging38-20261101.md`.

## Scope and review

Nine pages, A11yHeartLinks and only Astra’s footer-copyright-editor scalar change. The installer snapshots the entire Astra option, exact page/snippet content, snippet settings and SureRank rows. It verifies every saved store and refuses rollback after drift. It creates no new posts/snippets/media and changes no credentials or files in WordPress.

This includes review 3.13’s exact three additive research-framing paragraphs, which the handoff omitted from its bundle list. The recorded “fix all” authorization covers them. Butsaba’s five pending answers and confirmed session-length consistency work remain separate; no service fact is inferred. November 14 booking wording is preserved; Bundle G still waits until that date.

## Home and About, before and after

### Home

**Before**

> I’m Butsaba Dusrai, an Oregon Licensed Massage Therapist, and Thai Solitude is my practice. I trained in traditional Thai bodywork and in massage therapy, and I draw on both. I started Thai Solitude in Chicago in 2024, and I have worked in a busy Chicago massage practice where my clients included hotel guests and business travelers. Now I am bringing Thai Solitude to Portland. I am a native Thai speaker and fluent in English.

**After**

> I’m Butsaba Dusrai, an Oregon Licensed Massage Therapist, and Thai Solitude is my practice. I trained in traditional Thai bodywork and in massage therapy, and I draw on both. I started Thai Solitude in Chicago in 2024, and I have worked in a busy Chicago massage practice where my clients included hotel guests and business travelers. Thai Solitude is now in Portland, where I offer corporate and group massage; individual appointments at a dedicated location are planned for summer 2027. I am a native Thai speaker and fluent in English.

### About

**Before**

> I began working in massage in 2020 and started Thai Solitude in Chicago in 2024. I have also worked in a busy Chicago massage practice, where my clients included hotel guests and business travelers. Now I am bringing Thai Solitude to Portland, and I look forward to welcoming clients here.

**After**

> I began working in massage in 2020 and started Thai Solitude in Chicago in 2024. I have also worked in a busy Chicago massage practice, where my clients included hotel guests and business travelers. Thai Solitude is now in Portland, where I offer corporate and group massage; individual appointments at a dedicated location are planned for summer 2027.

## Research source verification

All required abstracts, full texts and author records were accessible through the connected research tools. The five reviewed fidelity groups are included. Citation numbering is unchanged. Thai uses the same complete PubMed-linked Cochrane references as Therapeutic and Deep Tissue; in-text hrefs change with the source registry. The two Thai “et al.” entries now list every author.

- [Ernst 2003](https://pubmed.ncbi.nlm.nih.gov/12777645/): distinguish most adverse effects from serious effects; use the accurate existing Chair sentence on three pages.
- [Mak 2024](https://pubmed.ncbi.nlm.nih.gov/39008297/): restore the moderate-/high-certainty qualifier on two pages.
- [Cochrane 2015](https://pubmed.ncbi.nlm.nih.gov/26329399/) and [Cochrane 2024](https://pubmed.ncbi.nlm.nih.gov/38415786/): name the no-intervention comparator, complete authors and consistent citations.
- Studied techniques: [chair](https://pubmed.ncbi.nlm.nih.gov/20846441/), [Anma](https://pmc.ncbi.nlm.nih.gov/articles/PMC5172615/), [rhythmical](https://pubmed.ncbi.nlm.nih.gov/26365448/), [myofascial release](https://pubmed.ncbi.nlm.nih.gov/19123877/). The last source specifies myofascial release; this is more precise than the review’s proposed sports-massage label. Both rhythmical-study mentions identify that style.
- [Arsovski 2025 full text](https://pmc.ncbi.nlm.nih.gov/articles/PMC12140169/): methods explicitly say twice a week for 8 weeks/16 interventions, resolving “bi-weekly.” Its abstract’s recovery comparison does not match its frequency-count table, and “longer sessions” conflates months/years receiving therapy with session length. The replacement retains the stated 40-minute protocol and reported athlete-group comparison and omits those two conflicting claims. This newly found limitation is a source-fidelity correction, not stronger evidence for the service.

**Exact current/replacement fragments and guarded occurrence counts:** [changes.json](../../scripts/work-with-me-employer/review-d/changes.json). It holds 45 pairs, old/new hashes for staging/isolated localhost, the whole old/new heart-link snippet and exact footer scalar.

Site-wide serial commas, Title Case, number/range-style changes, the italic font and inline CSS are declined. Keep the shortened “What are … talking about?” citation title. Windy City links/reference stay approved.

## Verification and rollback

- Offline gates PASS locally and on both GitHub Gates runs for `1890924ee4e97b2a89154e8dc70283c1bf6e9a36` (runs 41297905967 and 41297902089).
- Independent review PASS: all 45 sequential replacement counts, all nine complete old/new page hashes for both hosts, every generated preview, block-attribute JSON and balanced WordPress block nesting. Frozen snippet hashes and footer source match the fresh stores.
- First rehearsal: apply/reapply/exact rollback/reapply PASS; C browser regression 1,248/1,248 PASS. Two regulated-copy checks rejected the review’s “no treatment” phrase; use the source-faithful “no intervention” to preserve the shared house rules. One D assertion incorrectly expected a verified author list once; two different Thai papers share that complete list, so the correct rendered count is two. No staging write. Report: `reports/2026-11-01/103530-review-d-rehearse-staging38-20261101-1.md`.
- Home/About 390 px paragraph captures from that isolated run visually reviewed: full text present, no clipping or side-scroll.
- Corrected isolated uploads rehearsal PASS: 1,376 browser assertions (C 1,248 + D 128), regulated-copy/Home gates, apply, idempotent repeat apply, exact rollback and reapply; no new PHP errors. Report: `reports/2026-11-01/104227-review-d-rehearse-staging38-20261101-2.md`.

### Applied staging proof

| Check | Result |
| --- | --- |
| Staging dry run | PASS; all nine pages, snippet and footer ready |
| Fresh DB/off-site backup | PASS; exported before apply, encrypted and read back identically before writing |
| Exact apply/read-back | PASS; page/snippet fields, SureRank rows and whole Astra result verified |
| Origin rendered copy/link/licence | PASS: 53 assertions, zero failures |
| Rendered Home/client/posture gates | PASS on staging38 |
| Public page status | All 12 checked routes HTTP 200 |
| Site PHP log | No errors, warnings or deprecations from site code |
| Restore proof | Import exit 0; only the intended nine pages, heart snippet, Astra option, related rows and WPCode cache differ; no SEO/SureRank rows differ |
| Public screenshot capture | 20/20 candidate and reference views captured, zero missing/problems |
| Reference comparisons | Expected differences: all 20 pixel views (A–D still absent on live); schema 27 vs 23 unchanged from B, no parse errors |
| Final whole-site interactive public suite | Still pending; isolated C+D suite passed, no durable AntiBot fix claimed |

Reports:

- Capture: `reports/2026-11-01/102012-review-d-capture-staging38-20261101.md`.
- Rehearsal2: `reports/2026-11-01/104227-review-d-rehearse-staging38-20261101-2.md`.
- Dry: `reports/2026-11-01/104330-review-d-dry-staging38-20261101.md`.
- Apply/restore: `reports/2026-11-01/104934-review-d-apply-staging38-20261101.md` ([run 41298800180](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41298800180)).

Export `staging38-before-bundle-d-20261101-104452.sql` is 6,569,440 bytes, plaintext SHA prefix `f21339cdc9c2`; exact snapshot `bundle-d-before-20261101-104506.json`, plaintext SHA prefix `c5e685d1b41e`. Both are verified encrypted off-site in draft release `offsite-2026-11`. Snapshot is outside every web root and copied off-site before origin checks.

Exact rollback (fresh DB/off-site copy first, unchanged stores required):

```sh
scripts/ci/request.sh run --target staging38 --script review-d/push-bundle-d.sh --args '--rollback $HOME/private-backups/bundle-d-before-20261101-104506.json'
```

Rollback D before C, then B, then A. Older Chair/Corporate/research exact-state installers cannot run against D’s edited content; restore through the bundle snapshots in reverse order first. The historical Chair installer rollback stays retired while D is present.

## Measured height changes in isolated rehearsal

Compared with C’s applied rehearsal, at identical fonts/viewports. All 26 heading metrics and 147 FAQ geometry comparisons still pass; all widths have no side-scroll. Longer research copy and the three framing paragraphs account for the expected growth.

| Page | 1280 px | 390 px | 320 px |
| --- | ---: | ---: | ---: |
| Home | +54 px | +81 px | +82 px |
| About | +27 px | +54 px | +81 px |
| Work With Me | +0 px | +0 px | -44 px |
| Chair | +52 px | +134 px | +162 px |
| Thai | +27 px | +54 px | +82 px |
| Therapeutic | +135 px | +255 px | +282 px |
| Deep Tissue | +107 px | +228 px | +282 px |
| Corporate | +0 px | +0 px | +0 px |
| Accessibility | +0 px | -2 px | +0 px |

## Remaining lanes

SiteGround support must resolve the staging38 false positive through the supported hosting controls; the [ready support message](../inter-model/2026-11-01-siteground-antibot-support.md) has not been sent. security.txt public delivery remains unverified. Ryan owns merges and every live go/run. Butsaba’s pregnancy, setup/clothing, Thai-at-events, payment/cancellation and event availability answers remain pending.

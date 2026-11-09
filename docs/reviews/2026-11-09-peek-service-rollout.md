# Peek: approved service-page rollout

Scope: Therapeutic, Deep Tissue and Chair on staging39 only, using the existing
shared core #1380. No main/live write, core redesign or Services/short-page
eligibility decision is included.

All three approved sets are now deployed and publicly reviewed. Thai remains on
data #1381, and shared core #1380 is unchanged by this rollout.

| Page on staging39 | Data snippet | Focused public run |
| --- | --- | --- |
| [Therapeutic](https://staging39.thaisolitude.com/therapeutic-massage/) | #1382 | 168 passed, 0 failed |
| [Deep Tissue](https://staging39.thaisolitude.com/deep-tissue-massage/) | #1383 | 190 passed, 0 failed |
| [Chair](https://staging39.thaisolitude.com/chair-massage/) | #1384 | 232 passed, 0 failed |

Each focused run also checks the other pages against their deployed state at
that point. These are separate runs, not per-page assertion totals to add together.
**Defect observed during this rollout:** the first Chair card overlapped the
initial title strip and grazed the tablet Corporate CTA. Those passing checks
covered the eventual sticky position, not initial-strip clearance; historical
details and captures remain below. The separate
[clearance/touch correction](2026-11-09-peek-clearance.md) was deployed in place
to #1380, preserving all four data records; its apply job finished 07:15:10 UTC.
Stored inspection passed at 07:17:32 UTC (exact core hash/71,321 bytes; four data
hashes unchanged). Public verification was **INVALID HTTP202** at 07:17:03 UTC
on the initial Thai request: zero assertions/captures, no retry queued. That record
holds fresh-copy proof, the actual upgrade snapshot and recovery. Safari, physical
devices and actual assistive technology remain unverified.

## Approval and starting evidence

Ryan approved all three exact wording sets after the inline review in chat on
2026-11-09. His reply began "Approved:" and quoted the introduction to that
three-page review. The [approved wording tables](2026-11-09-peek-summary-drafts.md)
match the JSON arrays and coverage maps exactly: Therapeutic 27 items/six sections,
Deep Tissue 24/five, Chair 30/six. Approval changes metadata only; research
qualifications and every item remain unchanged. The shared core and Thai data
are outside this wording change.

The requested branch was pulled at `a66c8304be72597c1fb1af2cf5e40fc18607c1e7`,
clean and already current. GitHub connector confirmed read/write access.
The managed environment remains connected with unrestricted/enforced network;
direct Chrome previously encountered SiteGround's challenge. Server and browser
work uses the established Actions route.

[Fresh staging access and snippet inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42554148440),
request `peek-rollout-access-20261109-1`, finished 03:02:43 UTC: core #1380 code
SHA256 `dda8aecd38e417ad37766d2df951c6bdc761bf44997e030bbef5a36e9321f573`,
Thai data #1381 SHA256 `fd67d8f8548706bd8597af87e85380581a18f846a1bf71de4401db80b5f0a7e0`.
At that starting inspection, no data snippet for these three new pages existed.

## Method and current status

The simplification was explained before implementation: reuse the generic
installer and browser checker, with tiny localhost-only page rehearsal adapters.
Each page must prove apply, idempotence, exact rollback and reapply on a fresh
staging39 copy including uploads. Approved cards were checked at 390, 768 and
1280px, including research scrolling and screenshot review. The manifest records
actual deployed pages; a rehearsal-only expectation override allows testing the
one proposed page without pretending the other pages are already deployed.

Local validation passed: wording lint and 40 lint checks, 106 installer/adapter
checks and 10 core-emission checks. Chromium structural mocks: Therapeutic 168/0,
Deep Tissue 156/0, Chair 168/0 at the three widths, plus the all-enabled fixture
matrix 207/0. These are local structural tests, not actual page appearance.
The fixture suite retains explicit unapproved-data fallback coverage after all
production pages become approved. Two independent source reviews found no blocker.

[Actions offline gates passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42555080039),
request `peek-rollout-gates-20261109-1`, source `eb33f945286b900cb65a64d5baaa9a9a777a9734`,
finished 03:13:03 UTC. All code, ownership and wording gates passed; no rendered
verifier was claimed in this offline run.

Each write requires a fresh verified encrypted off-site backup, followed by report
review, restore proof and public verification. Actual snapshots are recorded
below, never inferred from rehearsal names.

The plain jump row remains until each page's data is installed, then remains the
no-JavaScript fallback. There is no core swap or planned fallback-only interruption
to Thai. Safari, physical devices and actual VoiceOver/TalkBack remain unverified.

## Therapeutic

[Fresh-copy rehearsal passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42555107195),
request `peek-therapeutic-rehearse-20261109-1`, finished 03:16:18 UTC. Uploads
included; apply/idempotence/exact rollback/reapply, ten page statuses, PHP and
rendered regulated-copy gates all passed. Browser checks **168/0**. Only the new
page-data snippet and WPCode cache changed, with the expected associated rows.
The primary reviewer viewed actual phone first/research-end cards and desktop
research-start: six-section stack fits; research qualifications and `etc.` are
reachable without losing the jump control or moving the page. Long closed section
headings use the existing ellipsis treatment; no wording or core redesign made.

[Staging preflight passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42555329218),
request `peek-therapeutic-dry-20261109-1`, finished 03:16:50 UTC.
[Staging apply completed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42555606018),
request `peek-therapeutic-apply-20261109-1`, finished 03:22:13 UTC: **#1382**.
Fresh export `staging39-before-peek-nav-20261109-031813.sql` (4,427,596 bytes,
SHA prefix `604aa232ec19`) and actual snapshot
**`peek-template-page-therapeutic-massage-before-20261109-031828-8e66b89d.json`**
(SHA prefix `02eee4541ddd`) were verified encrypted off-site and read back.
Restore import 0, 101/102 fingerprints: only new #1382, WPCode cache and associated
posts/meta/term-relationship row counts differ. Existing snippets, page copy,
CSS and SEO remain unchanged. Public markers passed for Thai/Therapeutic enabled
and Deep Tissue/Chair/Services plain rows.

All code/rendered-copy, HTTP, structured-data and PHP checks passed. The broad
pixel comparison was 16/20 identical with four expected Thai/Therapeutic differences
versus live and zero missing captures; its exit 1 is not an aggregate all-pass.
The manifest now records Therapeutic enabled. The independent second reviewer
also viewed all nine rehearsal captures and found no visual blocker; no claim is
made about every intermediate scroll position.

[Focused public verification passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42556138549),
request `peek-therapeutic-public-20261109-1`, finished 03:24:55 UTC: **168/0**,
including current-page approved JSON, all six lists, three-width card bounds,
research-end keyboard scrolling, print and no-JavaScript links. All nine public
captures are byte-identical to the actual-copy rehearsal. The primary reviewer
also viewed the public phone research start, confirming the full low-certainty
qualification remains visible. Other prepared pages stayed unchanged.

Saved public views: [phone first](assets/2026-11-09-peek-services/therapeutic-massage/chromium-therapeutic-massage-390-first.jpg),
[phone research end](assets/2026-11-09-peek-services/therapeutic-massage/chromium-therapeutic-massage-390-research-end.jpg),
[desktop research start](assets/2026-11-09-peek-services/therapeutic-massage/chromium-therapeutic-massage-1280-research-start.jpg),
and [all nine comparison hashes](assets/2026-11-09-peek-services/therapeutic-massage/comparison.json).

## Deep Tissue

[Fresh-copy rehearsal passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42556394798),
request `peek-deep-rehearse-20261109-1`, finished 03:28:56 UTC. The source already
included enabled Thai and Therapeutic data, with uploads. Browser checks **190/0**;
apply/idempotence/exact rollback/reapply, ten HTTP statuses, PHP and rendered-copy
gates passed. Only the new page-data snippet/cache and expected associated rows
changed in the copy.

The primary reviewer viewed phone first/research-end and desktop research-start;
a second reviewer viewed all nine captures. Cards and controls fit at all three
widths. “Deep pressure optional” remains clear; short-term pain and self-rated
recovery are separate items. Scrolling reaches the rare-harms and case-report
frequency qualifications and unfaded `etc.` without moving the page. Desktop
requires scrolling to finish the recovery item. Exact content checks cover every
approved item; intermediate scroll positions were not all visually captured.

[Staging preflight passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42556529503),
request `peek-deep-dry-20261109-1`, finished 03:29:22 UTC.
[Staging apply completed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42556939986),
request `peek-deep-apply-20261109-1`, finished 03:36:33 UTC: **#1383**.
Fresh export `staging39-before-peek-nav-20261109-033234.sql` (4,436,897 bytes,
SHA prefix `8dce277c5ae1`) and actual snapshot
**`peek-template-page-deep-tissue-massage-before-20261109-033248-2548d1fd.json`**
(SHA prefix `8dc1a62f5795`) were verified encrypted off-site and read back.
Restore import 0, 102/103 fingerprints: only new #1383, WPCode cache and associated
posts/meta/term-relationship row counts differ. Public markers confirm Thai,
Therapeutic and Deep Tissue enabled, Chair/Services plain rows; all rows retained.

All code/rendered-copy, HTTP, structured-data and PHP checks passed. Broad pixels
were 14/20 identical, with six Thai/Therapeutic/Deep Tissue differences versus
live and zero missing captures; this informational exit 1 is not an aggregate
all-pass. The manifest now records Deep Tissue enabled. An independent review of
the complete apply/backup/restore report found no unexpected change.

[Focused public verification passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42557406502),
request `peek-deep-public-20261109-1`, finished 03:38:55 UTC: **190/0**.
All five approved lists, current-page JSON, three-width card bounds, research-end
keyboard scrolling, both rail sides, print and no-JavaScript links passed; Chair
remained unchanged. Five of nine public captures match the rehearsal byte for
byte. The four differences are confined to the rail; all card/content pixels and
reported card bounds match. The existing rail pseudo-element transitions support
capture timing as an explanation, but that cause was not separately reproduced.
The primary reviewer viewed public desktop research-end and tablet research-start.
An independent reviewer compared all four differing captures and found no visual
blocker. No card/content defect was observed, and the core is unchanged.

Saved public views: [phone first](assets/2026-11-09-peek-services/deep-tissue-massage/chromium-deep-tissue-massage-390-first.jpg),
[phone research end](assets/2026-11-09-peek-services/deep-tissue-massage/chromium-deep-tissue-massage-390-research-end.jpg),
[desktop research start](assets/2026-11-09-peek-services/deep-tissue-massage/chromium-deep-tissue-massage-1280-research-start.jpg),
and [all nine comparison hashes/bounds](assets/2026-11-09-peek-services/deep-tissue-massage/comparison.json).

## Chair

[Fresh-copy rehearsal passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42557717328),
request `peek-chair-rehearse-20261109-1`, finished 03:43:51 UTC. Its baseline
included enabled Thai, Therapeutic and Deep Tissue, with uploads. Browser checks
**232/0**; apply/idempotence/exact rollback/reapply, ten HTTP statuses, PHP and
rendered-copy gates passed. Only the new page-data snippet/cache and expected
associated rows changed in the copy. The WP-CLI library emitted a pre-existing
deprecation during import; the applied copy introduced no new PHP errors.

The primary reviewer viewed phone first/research-end, tablet first and desktop research-start.
Six boxes fit, controls remain reachable, and research retains its small-study,
missing-comparison and distinct chair/general-massage harm qualifications. The
research end and unfaded `etc.` are reachable without moving the page.

**Observed design follow-up:** on the phone at page top, Chair's first card starts
at y=187.5 and slightly overlaps the lower edge of the display-only strip, which
is still in normal flow below the availability banner. Header and card controls
remain clear. The existing core and geometry check use the strip's *eventual
sticky position* (bottom=121), not its initial rendered bottom (about 201). Thus
the automated clearance assertion is narrower than initial-strip clearance.
No missing item or inaccessible card/section control was observed. The Corporate
CTA's complete hit area was not tested. This shared placement issue was explained
before the staging write and is retained for a focused
core design fix, not silently claimed as passing actual-strip clearance.
The independent reviewer viewed all nine captures and confirmed this defect:
at 768px the first card's focus ring also grazes the Corporate CTA text/underline,
and the desktop first card covers right-side strip labels. The primary reviewer
also checked the tablet first card. Both reviewers judged the staging rollout
nonblocking because the site header/card controls and all section choices remain
accessible; a future core correction should clamp to the actual rendered strip
rectangle, with matching assertions, before claiming full initial-strip clearance.

[Staging preflight passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42557766307),
request `peek-chair-dry-20261109-1`, finished 03:44:22 UTC.
[Staging apply completed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42558224458),
request `peek-chair-apply-20261109-1`, finished 03:50:18 UTC: **#1384**.
Fresh export `staging39-before-peek-nav-20261109-034655.sql` (4,445,490 bytes,
SHA prefix `de79c9c14e14`) and actual snapshot
**`peek-template-page-chair-massage-before-20261109-034709-6a5b55dd.json`**
(SHA prefix `bf2445f413f6`) were verified encrypted off-site and read back.
Restore import 0, 103/104 fingerprints: only new #1384, WPCode cache and associated
posts/meta/term-relationship row counts differ. Public markers show one core/data
pair on all four service pages, none on Services; plain rows remain everywhere.

All code/rendered-copy, HTTP, structured-data and PHP checks passed. Broad pixels
remain 14/20 identical, six differences on Thai/Therapeutic/Deep Tissue versus
live, zero missing. Chair is not in this broad twenty-view set; its focused public
check supplies that page's rendered evidence. This informational exit 1 is not
an aggregate all-pass. The manifest now records all four pages enabled.

[Focused public verification passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42558629539),
request `peek-chair-public-20261109-1`, finished 03:52:51 UTC: **232/0**.
All six exact approved lists and current-page JSON, card bounds against the
eventual sticky position, research-end keyboard scrolling, both rail sides, print
and no-JavaScript links passed. This does not close the initial-strip overlap
described above. Six of nine captures are byte-identical to the rehearsal; the
three differences are confined to rail pixels. All card/content pixels match.
The primary reviewer viewed public phone research-start, including small-study,
missing-comparison and no-difference qualifications. The complete apply report
also received an independent backup/restore review with no unexpected changes.

Saved public views: [phone first](assets/2026-11-09-peek-services/chair-massage/chromium-chair-massage-390-first.jpg),
[tablet first/overlap](assets/2026-11-09-peek-services/chair-massage/chromium-chair-massage-768-first.jpg),
[phone research end](assets/2026-11-09-peek-services/chair-massage/chromium-chair-massage-390-research-end.jpg),
[desktop research start](assets/2026-11-09-peek-services/chair-massage/chromium-chair-massage-1280-research-start.jpg),
and [all nine comparison hashes/bounds](assets/2026-11-09-peek-services/chair-massage/comparison.json).

## Final cross-page verification

[Final public Chromium check passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42558669623),
request `peek-services-final-chromium-20261109-1`, source
`81c0ceafb425a6ad305c463b5412d1acc5234717`, finished 04:01:18 UTC:
**172/0** manifest checks across all four enabled pages plus **485/0** retained
Thai gesture checks. The operation report keeps only the last 400 of 660 lines;
an independent reviewer read full job `128970299925` and confirmed both summaries,
657 PASS assertions and no FAIL/INVALID lines. Generic checks cover each page's
approved data, actual row order, both rail sides, print and no-JavaScript links.
The Thai suite retains gestures, no icon scrolling, keyboard paths, large-text
and landscape variants. Its synthetic activations do not establish real assistive
technology or physical-device behavior, and do not resolve the initial-strip
placement issue above.

[Final public Firefox check passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42558828529),
request `peek-services-final-firefox-20261109-1`, source
`7314e607efa629a6551011834b4a3362a59143ff`, finished 04:02:56 UTC: **172/0**.
Both final generic runs cover the actual four pages at 390/768px. Three-width
card/research-end evidence belongs to the earlier focused Chromium runs; the
485-assertion detailed gesture suite is Thai/Chromium only. Every operation report
was read; no INVALID result is used as verification.

[Final stored-snippet inspection passed](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42559557625),
request `peek-services-final-snippets-20261109-1`, finished 04:03:23 UTC. Core
**#1380** (66,253 bytes) and Thai data **#1381** (4,376 bytes) retain their starting
SHA256 hashes exactly. New published data: Therapeutic **#1382** (3,728 bytes),
Deep Tissue **#1383** (3,408 bytes), Chair **#1384** (3,923 bytes).
[Full recorded hashes](assets/2026-11-09-peek-services/snippet-hashes.json).
All deployed changes in this phase are WordPress-stored page data; the shared
core, approved Thai wording, existing page bodies and remote plugin/theme files
were unchanged. No fallback-only core-swap interval occurred.

## Page-only recovery

Use the matching package (`854fd1dd` or a later commit with identical core,
installer and approved data payloads), inspect current ownership/state, and use
the actual snapshot for that page. These commands are recorded, not executed:

```sh
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page therapeutic-massage --rollback $HOME/private-backups/peek-template-page-therapeutic-massage-before-20261109-031828-8e66b89d.json'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page deep-tissue-massage --rollback $HOME/private-backups/peek-template-page-deep-tissue-massage-before-20261109-033248-2548d1fd.json'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page chair-massage --rollback $HOME/private-backups/peek-template-page-chair-massage-before-20261109-034709-6a5b55dd.json'
```

Each reversal takes a fresh verified encrypted off-site backup and refuses a
changed, incomplete or foreign snapshot state. Removing a page's registration
returns that page to its plain jump row while preserving the shared core and
other enabled pages. Update the manifest only after verifying an actual reversal.
The original core-migration rollback affects all Peek pages and belongs to the
[separate migration recovery record](2026-11-09-peek-template.md#recovery-for-the-original-core-migration).
The later clearance/touch upgrade must be reversed first using its own actual
snapshot and explicit `core-upgrade --rollback FILE` arguments; see the
[current correction/recovery record](2026-11-09-peek-clearance.md).
Never use an old snapshot to delete a newer payload; future data/core revisions
need their own reviewed exact-state migration.

## Remaining design and validation work

The separate clearance/touch correction is deployed after fresh-copy rehearsal
and visual review, with stored hashes confirmed. Final public verification is
blocked by the initial HTTP202 challenge; no browser assertion or capture ran,
and no retry is queued. Follow its linked record for the evidence. The correction
addresses actual strip/header rectangles and preserves research
reading state across resize. The initial Chromium native-touch baseline did not
reproduce Ryan's page-scroll report, so device confirmation remains open. The
historical research captures in this rollout do not establish every intermediate
scroll position, physical touch behavior, Safari or actual VoiceOver/TalkBack.

Services and short-page eligibility remain individual decisions, not automatic
exclusions or rollout. The pre-existing Chair duration discrepancy (10–20 minutes
in the introduction, 10–15 in the FAQ) remains a separate owner wording decision;
this rollout changes no page-body text. Main/live remain unchanged.

I finished: the three approved wording sets deployed and independently reviewed
on staging39, with exact rollback rehearsals, fresh verified backups, restore
proofs, public Chromium/Firefox checks and stored-hash confirmation.
Next step: focused public browser verification remains blocked by HTTP202;
follow the separate correction record. No retry is queued.
Next lane: Safari/physical-device/assistive-technology validation and individual
Services/short-page eligibility decisions.
See my work: the public captures, comparison hashes, actual recovery snapshots
and operation reports linked in this record.
Arc: none (ad-hoc)

TL;DR: all four massage pages use Peek on staging39. This record preserves the
approved data rollout; the later clearance/touch upgrade has its own evidence
and recovery record. Main/live are untouched.

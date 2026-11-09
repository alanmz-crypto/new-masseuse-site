# Peek: approved service-page rollout

Scope: Therapeutic, Deep Tissue and Chair on staging39 only, using the existing
shared core #1380. No main/live write, core redesign or Services/short-page
eligibility decision is included.

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
No data snippet for these three new pages exists yet.

## Method and current status

The simplification was explained before implementation: reuse the generic
installer and browser checker, with tiny localhost-only page rehearsal adapters.
Each page must prove apply, idempotence, exact rollback and reapply on a fresh
staging39 copy including uploads. Approved cards will be checked at 390, 768 and
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
The manifest now records Therapeutic enabled. Focused public card verification
is next. The independent second reviewer also viewed all nine rehearsal captures
and found no visual blocker; no claim is made about every intermediate scroll position.

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

Actions offline gates and page rehearsals are next. No new page has been
deployed by this phase yet. Each write will require a fresh verified encrypted
off-site backup, followed by report review, restore proof and public verification.
Actual snapshots will be recorded here, never inferred from rehearsal names.

The plain jump row remains until each page's data is installed, then remains the
no-JavaScript fallback. There is no core swap or planned fallback-only interruption
to Thai. Safari, physical devices and actual VoiceOver/TalkBack remain unverified.

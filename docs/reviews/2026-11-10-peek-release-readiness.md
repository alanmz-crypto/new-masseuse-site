# Peek visitor-navigation release readiness

**Decision: prepare the release; do not launch yet.** Ryan asked Codex to decide
when it is a good time to push live and continue. This assessment does not start
a live write or authorize a main merge. Work remains on
`claude/wizardly-bell-xu0rta`; the current visitor navigation is on staging39.

## Recommended scope

- The currently verified shared Peek core, with the title strip and rails on
  visitor pages, plus the four approved service-page summary data snippets.
- Remove the exact superseded reading-progress CSS block and disable Astra's
  scroll-to-top button, matching the reviewed staging design.
- Keep existing jump-row snippet #1232 and native-guide snippet #1338 unchanged.
  Peek hides the native guide only after it initializes; the plain jump row
  remains the destination source and no-JavaScript fallback.
- Keep the four experimental navigation pages on staging. Their separate
  Firefox drawer investigation does not block this visitor release.
- Preserve live page content, SEO, menus, forms and other site data. Use one
  narrowly scoped, reversible WordPress-storage transition, not a database copy.

This scope was explained before preparing a release implementation. No summary
wording, research qualification, jump behavior or product simplification is
proposed here.

## Evidence already available

The [input-focus phase record](2026-11-09-peek-input-focus.md) contains the actual
staging snapshots, full hashes and independent artifact reviews. The deployed
core passed **12,374/0 across 14 public Chromium/Firefox suites**, then exact
snapshot verification before/after all 19 rendered staging routes. Its fresh
staging-copy rehearsal passed **14,386/0 across 15 suites**, including rollback
and post-browser state checks. These are completed prior runs, not new tests in
this readiness phase.

Fresh access checks on November 10 confirmed GitHub pull/push/admin permission,
a current clean branch, and HTTP200 for a plain staging39 Thai Massage request.
The selected environment reports current, enforced unrestricted network policy.
The inner sandbox could not reach its inherited proxy; the approved outer
executor succeeded. This does not prove a public browser session will avoid
SiteGround's separate HTTP202 challenge.

Read-only live fingerprint inspection
[42738048595](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42738048595),
source `86de2e6f97c4e3a0814c728bfc7492b407c6bf19`, finished **01:46:09 UTC,
PASS**. The complete report was read. It lists 15 published visitor pages, no
practice pages and no Peek core/data records. Live's two navigation dependencies
already match the current source and the recorded staging versions:

| Record | Stored-code SHA256 |
|---|---|
| #1232 A11yPageJumpLinks | `027d8a2b78d68b55be139b41f8594501fc2492aff437146364dc1c87bbfda99c` |
| #1338 TSSectionGuide v1 | `ef9ee2fb82fe91be616518b4a02f9ee276855a9b998685e7e5ee13064c84f785` |

The fingerprint is not a full-state capture. Exact records, CSS/Astra bytes,
page identities and route provenance are being inspected separately.

## Conditions for recommending launch

1. Capture and review the exact current live navigation baseline. Existing
   staging upgrade installers require owned snippet #1380 and historical
   payloads; adding the live hostname would not provide a first-install path.
2. Prepare a separate first-install transition and initially allow writes only
   on the isolated localhost rehearsal. Bind its source, ownership, snapshots,
   partial-failure recovery and rollback to reviewed bytes. Preserve older
   staging rollback endpoints. The older cleanup wrapper must not be reused
   unchanged: its rollback lacks a fresh verified backup and its progress-block
   matching is broader than the reviewed exact payload.
3. Rehearse the transition on a fresh isolated copy of current live, including
   media: install, repeat, exact rollback, reapply, regulated-wording and rendered
   browser gates, then post-browser recovery. Test the actual visitor inventory,
   including search/404; staging's 19-page inventory is not the live inventory.
4. Complete supplemental Linux WebKit disclosure/layout checks. Linux WebKit
   remains engine evidence, not a claim of Safari or physical-device testing.
5. Complete a short actual iPhone Safari acceptance check on the frozen staging
   candidate. Ryan confirmed an iPhone with Safari is available. Give the short
   test after automated checks are ready: Therapeutic research scrolling in both
   orientations and at both ends, rapid box changes, short/closed-box touch
   containment, preview/index gestures, left side, Privacy disclosure landing,
   and header use as Safari's browser bars change height. Record device/iOS and
   outcomes. This is a release judgment based on the previously reported finger
   scrolling defects, not an additional repository approval rule. A brief
   VoiceOver focus/jump check is separate from touch acceptance and is not a full
   accessibility audit.
6. Present the exact release and recovery package with fresh verified off-site
   backup requirements and a live dry run. The repository's manual LIVE workflow
   leaves the main merge and live start with Ryan; no live request is prepared or
   dispatched yet.

No arbitrary calendar date or amount of elapsed time makes the release ready.
Recommend a window when these conditions pass and someone can immediately check
the live result and initiate the tested rollback if necessary. Traffic-dependent
timing cannot be inferred from the evidence available here.

## Separate follow-ups

The practice drawer's public Firefox mouse-input failure remains open. The one
later hold-split retry
[42738993282](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42738993282),
source `5a30396fadfb1baab8e0adc6aa24b9023c79c471`, finished **01:55:11 UTC,
26 passed / 1 failed**, two page loads and one capture, with no INVALID. Hold to
go alone reproduces the subsequent missing drawer mouse input; gestures 1–3
pass. Correct commands are acknowledged but no DOM events arrive in the failing
case. The underlying mechanism remains unproved.

The full report, traces, failure screenshot and checksum-verified artifact were
reviewed. Each sanitized HAR contains 11 embedded HTTP200 resources with 760,956
body bytes. Both cases have identical resource bodies and the exact current
core/style. Captured assets support local investigation without further staging
requests. The earlier challenged attempt remains zero evidence, and this
diagnostic does not waive the full failed practice-page regression gate.

Known transient label overlaps, real OS text scaling and broader assistive-
technology coverage remain documented. They should not be described as newly
fixed or silently substituted for the acceptance checks above.

# Peek visitor-navigation release readiness

**Decision: prepare the release; do not launch yet.** Ryan asked Codex to decide
when it is a good time to push live and continue. This assessment does not start
a live write or authorize a main merge. Work remains on
`claude/wizardly-bell-xu0rta`; the current visitor navigation is on staging39.

**Actual iPhone Safari acceptance is not complete.** Ryan reported that the
rail/index step and background-scroll containment passed. Their reply reports
step 4 both passing and failing, and says only containment passed in the
research-box step. The specific research scrolling/size/switching problem,
Privacy versus hamburger outcome, iPhone model and iOS version are being
clarified. Do not infer a complete Safari pass or a confirmed root cause.

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

The fingerprint is not a full-state capture. A separate database-only inventory
[42741958557](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42741958557),
source `9c6e090417c83164b457be16e5dcbd9ac4f6bb8b`, captured the exact live state
at **02:19:48 UTC, PASS**: WordPress 7.1.4, MySQL 8.4.11-11, a read-only
REPEATABLE READ snapshot across seven InnoDB tables, 15 published pages,
25 snippet metadata entries, seven scoped full records and zero issues. No
WordPress bootstrap, public HTTP request or database write occurred.

The complete report and checksum-verified three-file artifact were independently
reviewed. `baseline.json` is **2,047,975 bytes**, SHA256
`a6c6e923d484576d96060ab8d9f84110c5d785f9fec64c5ae7e5b0ef3f37f013`.
Artifact `13730735070` is 645,199 bytes, SHA256
`a0d1022e26be819216bad40bd800074b2db7f5447c55a37cd9615afd9967824e`.
All raw records, option and witness hashes recompute. There are no Peek records
in any status; #1232 and #1338 are active with their exact expected code and
metadata. Their PHP/everywhere taxonomy rows each have count 17, so new owned
snippets must account for precise shared-count changes. All 22 configured
service headings occur once in stored HTML; rendered anchor validation remains
part of the rehearsal.

Live Additional CSS #29 contains one exact reading-progress block (1,093 bytes
without surrounding newlines, SHA256
`f8da6191df763e2f270aafaf322d9c2b1e5050620f67af5c2c2fd3f311c7332b`).
Astra's `scroll-to-top-enable` is boolean true and its option autoload is `on`.
Only scoped public/navigation settings were exported; unknown page metadata is
represented by hashes. Independent inspection found no unintended credential
data in the export.

Staging's equivalent inventory
[42743361208](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42743361208),
source `3ac99e143ca812cf45c96a9b31399ac003840e9a`, finished **02:31:55 UTC,
PASS**. Its 2,425,765-byte baseline has SHA256
`06cbb42ae2b74f145f488c351a6be8cfec17d5981070e9b550adc1024a7df837`;
artifact `13730377390` is 832,281 bytes, SHA256
`d943af1ae6c945596977aa15dfd292354992877f97279086660b1fe33a464b1d`.
Full report, artifact and all row/option/witness hashes were independently
verified. It contains 19 pages, 30 snippet metadata entries, 12 scoped full
records and zero issues; WordPress/MySQL versions match live.

All 15 common page IDs, titles and origin-normalized content hashes match;
eight bodies are identical and seven differ only by the site origin. The four
extra routes are exactly the navigation practice pages. Current #1380 and all
four generated data snippets match repository bytes exactly. The eight existing
navigation metadata rows match; full dependency posts differ only by GUID.
Astra differs only in four image origins and the scroll-to-top boolean, with
autoload unchanged. The release's exact 1,094-byte CSS removal includes the
progress block's following newline and produces staged CSS SHA256
`2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae`.

The service-page metadata witnesses are **not** identical: both sites have 106,
with four IDs unique to each site and four differing hashes among shared IDs.
Only hashes were exported, so those differences are not classified. This is
additional reason to preserve live's own state and rehearse its exact copy;
no staging metadata or database is being promoted.

GitHub masks parts of the large encoded **console** copy. The artifact, leaf log
and published report copy are intact and agree; the console payload is not
canonical. Future inventories retain the full artifact and print findings and
hashes instead of duplicating megabytes of encoded data. The first live capture
itself took seconds; emitting its console copy delayed publication by minutes.

## Conditions for recommending launch

The provisional first-install package now lives in
`scripts/work-with-me-employer/link-ideas/peek-release/`. It accepts only exact
`http://localhost:8090`; no staging/live installer or live request is enabled.
Independent source review cleared installer SHA256
`b9485188ea80f05567cbfb903c28da34148d39c2ff7db6541ceebe553db80a64`
for isolated rehearsal. Its pinned-PHP SQLite/WordPress API model passed **913
checks**, including **106 injected write-boundary failures**. The separate
rehearsal evidence tests passed **135 controls** and detected eleven deliberate
guard removals, including three network-boundary mutations. Adapter/profile
tests passed **46 controls**, then accepted the
actual frozen manifest and independently captured 15-page inventory using stub
browser entry points. These are offline results, not real WordPress or browser
verification.

The rehearsal retains the existing complete visitor browser chain, adds the
supplemental WebKit checks on localhost, and checks original rendered navigation
after each rollback. It binds each phase to the actual immutable snapshot and
five created IDs, including a separate rollback/reapply after browser activity.
The source and destination stores, shared taxonomy counts and unrelated content
must remain exact. A release-only internal Docker network isolates copied
plugins from external integrations before the first container starts. Docker
suppressed port publishing on the tested internal-only network, so an owned
localhost TCP relay forwards browser traffic to the exact inspected container.
The host browser itself is not network-isolated. Fresh-copy execution is still
pending.

Seven synthetic TCP tests passed for the fixed relay, including address/bind
refusals, large duplex transfers, half-close, idle failure and clean termination.
An independent pinned-image Docker probe confirmed internal networking, no
default egress route on all three copy services, PHP-to-database connectivity,
localhost access through the exact relay, unchanged binary transfers and sixteen
concurrent reads. This used synthetic content only, not the site's copied files.
Source Gates runs `42747569094` (`f104e90c`) and `42748065437` (`d0f48692`) each
passed **38/0**, with one expected rendered-URL skip; complete job logs were read.
The relay additions require their own final source Gates run.

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

## Supplemental engine preparation

The focused WebKit paths preserve existing Chromium/Firefox assertions. The new
read-only wrapper pins Playwright 1.63.0, keeps complete logs and requires exact
page/capture counts and matching literal assertion totals. Independent review,
12 offline evidence controls and origin/target refusal checks passed. Source
`392ed10c6fbf10aff48df16a323c694daf4ce94e`
[Gates 42740052057](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42740052057)
passed **36/0 with one rendered-URL skip**.

Public WebKit attempt
[42740074026](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42740074026),
source `b1288c9a7c7f7b956caef61ae2a85f4799346080`, finished **02:08:57 UTC,
INVALID**. Official WebKit 26.6/build 2359 and its dependencies installed and the
browser launched, but the first Privacy request returned HTTP202. There were
**zero assertions, zero successful page loads and zero captures**; layout was
skipped. This establishes runner capability, not site verification. The next
WebKit check belongs on the isolated rendered copy; no public retry is queued.

Ryan's short iPhone Safari check was requested while the unchanged visitor core
remains frozen; the partial/ambiguous result is recorded above. If a subsequent
fix changes that core, the affected acceptance steps must be repeated.

Source review identifies two possible discriminators, not reproduced Safari
causes. An open Peek preview/index consumes the first outside tap, including a
hamburger tap; testing with Peek already closed distinguishes that existing
behavior. Safari browser-bar changes can trigger `visualViewport` refitting,
which currently writes inner scroll positions. Native scroll recognition and
scroll interruption need separate observation before changing either. The
supplemental WebKit layout check uses taps and keyboard End, not finger swipes,
and cannot settle the actual iPhone scrolling complaint.

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

Two offline replays had 29 passing interaction assertions and matched all
captured body hashes and geometry, but both remain **INVALID**: a reject-only
proxy blocked one browser-level CONNECT to staging39 outside Playwright's page
request stream. No traffic escaped. Startup and idle-page controls were clean;
the cause of that extra browser request and the public-only input failure remain
unproved. No allowance, timing workaround or runtime change follows from this.
The next useful discriminator is browser drag/capture state in the failing
environment. This separate prototype investigation is paused while preparing
the visitor release.

Known transient label overlaps, real OS text scaling and broader assistive-
technology coverage remain documented. They should not be described as newly
fixed or silently substituted for the acceptance checks above.

---
name: open-items
description: START HERE. Every open item for thaisolitude.com as of 2026-10-25, sorted by what happens by itself, what Ryan does from a phone (with links), what needs a computer, decisions waiting on Ryan, what needs Butsaba, and how to restart from a phone cloud session
metadata:
  type: project
---

**Written 2026-10-25 (updated by pull request whenever an item changes). Ryan's computer is offline from about 2026-10-25 to 2026-11-08 and he works from his phone only.**
A phone cloud session (https://claude.ai/code, repo `alanmz-crypto/new-masseuse-site`) sees ONLY this repository: not his computer, not his global `~/.claude/CLAUDE.md`, not his local Claude memory, not convmem. Everything that must not be lost is therefore written here and in the notes this index points to.
First message to send in a fresh phone session: **"Read .claude/project-memory/open-items.md and tell me what is due."**

**Live preflight passed; manual release preparation, November 11.** The final
read-only dry run **43055400254** passed at **16:09:22 UTC**, source
`36263729dc0b92ad4885748cd2f2d665cb2bb2ed`. SiteGround's logical-home alias was
resolved before WordPress bootstrap; all strict plugin hashes and live baseline
guards passed unchanged. The selected manual request is
`live-peek-visitor-release-20261111-1`, script `link-ideas/push-peek-release.sh`,
args `--apply`, automatic checks enabled. Finish final Gates and the release PR,
then Ryan merges and starts **Site ops (LIVE)** on **main** with that ID. Live
is unchanged; no live recovery snapshot exists before his actual run.

**Release go received November 11.** After the completed review and explicit
question about enabling the tooling, live dry run and release PR, Ryan replied
“Continue” and asked to defer diminishing-return refinements. This authorizes
the reviewed enablement and PR/request preparation. The exact reviewed patch
has been applied; the read-only live preflight passed. Final source checks and
the release PR are next. No live installation has run. Ryan still merges and
starts Site ops (LIVE).
The earlier disabled/awaiting-go statements below record the preceding stage.

**Current release preparation, November 11: staging and complete live-copy proof passed.**
The [Search phase record](../../docs/reviews/2026-11-11-search-contrast.md)
records final source Gates **51/0**, a fresh staging39 copy with **300/0** browser
checks and exact recovery, followed by backed-up deployment at **06:45:11 UTC**.
Public Chromium/Firefox passed **300/0** at **06:59:24 UTC**. Final actual-snapshot
verification passed at **07:00:26 UTC** (Actions **42976293114**), with exact stored
and served CSS before/after Search and Home renders. Core and approved summaries
are unchanged. The generic comparison against live still reports the existing
20 navigation-related pixel differences; it is not an aggregate visual pass.

Actual CSS recovery snapshot:
`search-contrast-before-20261111-064511-fcba9b49.json`; fresh export:
`staging39-before-search-contrast-20261111-064500-29620.sql`. Both were verified
encrypted off-site in the required order. Use this actual Search snapshot with
its guarded wrapper after exact-state checks; copy snapshots and older Peek
snapshots do not recover this CSS. The deployed Search manifest stays immutable.
The initial apply stopped before mutation because SiteGround's logical home is
an alias of `/home/customer`. The wrapper now supplies canonical private paths,
including recovery; dry preflight checks the same prerequisite as apply.

The first-install release candidate contains the accepted quiet-navigation
design with the minimal observer correction below and this exact Search CSS;
all four approved data payloads are unchanged.
Its refreshed models passed **1,843 installer checks** (138 injected failures)
and **163 evidence controls**. The complete corrected live-copy cycle passed
**24,737/0 across all 28 browser suites**, exact recovery and verified cleanup
at **11:33:22 UTC**, run **43006077712**. The
[readiness record](../../docs/reviews/2026-11-10-peek-release-readiness.md)
contains the concrete copy request. On November 11 Ryan explicitly approved
copying live's database/site files/uploads to a temporary GitHub Actions runner
for installation and rollback testing, then deleting the copy while retaining
logs/screenshots. This supersedes the earlier automatic scope rejection for
that rehearsal; do not ask again for the same scope. Live writes, live enablement
and main merges remain unauthorized. Safari acceptance is complete; fluidity
stays deferred.

The approved fresh live-copy rehearsal `peek-visitor-release-quiet-search-copy-1`
(run **42981371762**) failed on November 11 at **07:44:11 UTC**. First install,
repeat and initial exact rollback passed. Loading the 15 restored pages then
replaced 15 metadata rows, so exact verification stopped the run before browser
checks. Diagnostic copy2 (run **42983523811**, FAIL at **08:02:36 UTC**) identified
exactly one regenerated `_uag_page_assets` row per page: values changed as well
as IDs. Both copies were deleted with verified cleanup. A narrow, source-bound
Spectra filter around the installer's own WPCode save calls is implemented and reviewed;
all exact-state guards remain. Corrected source Gates passed **51/0**. Copy3
(run **42987385913**, FAIL at **08:49:35 UTC**) verified the actual source binding,
install/repeat/exact rollback, unchanged metadata through restored-page visits,
and reapplication. Retained core browsers passed **14,319/0**; WebKit disclosures
passed **70/0**, but enlarged-text Therapeutic layout reported two ResizeObserver
errors (**150/2**). The run stopped at **17/28 suites**; post-browser recovery is
unproved in that failed run. Cleanup verified deletion. The observer correction
and successful fresh proof below resolve these errors. Live and staging39 were
unchanged by copy3. The readiness record contains
the actual copy-only snapshot and artifact bindings; the deleted journal is not
a staging/live recovery file.
The minimal title-strip ResizeObserver timing correction was verified: local
WebKit reproduced the original error in 6/6 trials and passed the queued callback
in 6/6 with identical final geometry. Accepted quiet-navigation bytes are frozen
as shared v10. Complete corrected source Gates passed **55/0**, run **42993487769**,
source `037fec5a6b95ecfcda02db98e8b2125326da9155`. The fresh staging39-copy
rehearsal `peek-title-resize-copy-1` passed at **10:11:22 UTC**, run **42994882056**,
source `95f823413b04fa8f683d77e0a0f82087aac3fa7b`: all 29 browser suites
**25,343/0**, Home/client wording gates **35/0 and 322/0**, exact recovery before
and after browsers, unchanged summaries/Search CSS, and verified cleanup.
Independent raw-evidence and visual review found no new blocker. The
[resize phase record](../../docs/reviews/2026-11-11-peek-title-resize.md) records
actual copy-only snapshots and artifact bindings. Read-only staging preflight
passed at **10:14:56 UTC**, run **43001945737**. Staging39 core #1380 was
updated at **10:18:08 UTC**; apply run **43002377065** passed at **10:28:47 UTC**.
The fresh export and actual snapshot were verified encrypted off-site in order;
all 19 routes served exact core/style/data, with unchanged summaries and CSS.
Public focused run **43004137038** passed **752/0** at **10:39:36 UTC**;
all 60 captures were reviewed and all 24 resize captures match the successful
copy exactly. Final actual-snapshot verification **43005630245** passed at
**10:41:20 UTC**; stored inspection **43005811818** passed at **10:42:37 UTC**,
confirming published core `f152fad9…73566` and unchanged CSS29 `b024acc6…2c69b`.
Staging verification is complete. The already approved fresh live-copy
first-install/browser/recovery cycle `peek-visitor-release-quiet-search-copy-4`
passed at **11:33:22 UTC**, Actions **43006077712**, source
`a2a165a6c1ddeec92a7c1dd9dd2db094f5d93697`: source **55/0**, browser
**24,737/0**, all 28 unique leaves/13 phases, Home/client wording **35/0 and
322/0**, exact repeat, recovery before/after browsers, and final reapply.
All original navigation signatures and 90 metadata observations remained exact.
Cleanup verified the temporary copy and owned resources absent; retained evidence
contains no SQL, private journals or configuration. Independent audits found no
blocker. The [release review](../../docs/reviews/2026-11-11-peek-live-release.md)
is ready for Ryan's release go, then a guarded live dry run and prepared PR/request.
Live is unchanged.
Current guarded recovery starts with `core-title-resize` → frozen v10 using only
`peek-template-core-title-resize-before-20261111-101808-92c3067f.json`, after
fresh verified backup and exact ownership/state checks. Its pre-write export is
`staging39-before-peek-title-resize-20261111-101757-98626.sql`.
The reviewed live-enable proposal is saved as a disabled patch, with offline
models and independent review recorded in the readiness note. It is not applied;
no live request or deployment is authorized. No generic Safari retest or fluidity
expansion is required.

**Current staging39, November 11: four navigation refinements complete and accepted.**
The automatic rail-side scrolling label is removed; interaction names remain.
The index has a solid outlined button and a tappable X while open. Outside taps
close the header menu; the index's existing outside dismissal remains.
See the [quiet-navigation record](../../docs/reviews/2026-11-11-peek-quiet-navigation.md) for exact evidence.
Source gates passed **48/0**; the fresh staging39 copy passed **24,977/0 across
24 browser suites**, with exact recovery after browser activity and all 108
focused screenshots reviewed. Applied to #1380 at **04:38:46 UTC**; exact
core/four-data state passed before and after all 19 rendered routes. Public
browsers passed **19,628/0 across all 20 suites** at **05:32:28 UTC**; raw traces
and all 144 quiet/index captures were audited without a new blocker. Final
read-only verification passed at **05:35:29 UTC** (run **42966700614**), with
exact core/four-data state before and after all 19 rendered routes against the
actual snapshot below. Ryan accepted these new controls
on iPhone Safari on November 11:
“It's correct.” They explicitly deferred fluidity refinements until after the
live push. No repeat of this acceptance check is needed; this does not claim
broader device or assistive-technology coverage or authorize a live operation.

After the title-resize recovery above, historical `core-quiet-nav` recovery
restores frozen v9, using only
`peek-template-core-quiet-nav-before-20261111-043846-d52816fb.json`,
after fresh verified backup and exact ownership/state checks. This actual
snapshot and `staging39-before-peek-quiet-nav-20261111-043831-56757.sql` were
verified encrypted off-site in the required order. Older recovery routes below
apply only after this restoration. Main/live remain unchanged. The separate dark Search
contrast correction is now complete as recorded above.

**Previous accepted baseline, November 11: small-screen index clearance.**
The idle index clears the visible header, booking notice and title strip, and the
rail respects Corporate/Work With Me's fixed bottom actions. Idle navigation
waits only when full-size targets cannot fit; scrolling or resizing restores it.
Accepted summaries, immediate neighbours and gestures remain intact.
See the [index-clearance record](../../docs/reviews/2026-11-10-peek-index-clearance.md) for exact evidence.
Source Gates passed **45/0**; a fresh staging39 copy passed **23,249/0 across
21 browser leaves**, including Chromium/Firefox/WebKit and exact recovery after
browser activity. All 108 focused screenshots were reviewed.
Applied to #1380 at **00:53:57 UTC**; final actual-snapshot verification passed
at **01:30:30 UTC after public browsers**, with exact core/four data state
before/after 19 HTTP200 renders.
The automatic public post-check was **INVALID HTTP202**, with nothing tested.
The single later public-browser run passed **18,460/0 across 18 suites** at
**01:28:49 UTC**; all 72 focused images match the reviewed copy byte-for-byte.
The earlier invalid check remains distinct. Main and live remain unchanged.

At that previous baseline, recovery started with `core-index-clearance` → frozen v8, using only
`peek-template-core-index-clearance-before-20261111-005357-31ca0040.json`,
after fresh verified backup and exact ownership/state checks. This actual
snapshot and `staging39-before-peek-index-clearance-20261111-005342-74246.sql`
were verified encrypted off-site. Older recovery routes below apply only after
this restoration. Ryan accepted the index fix on November 11: **“Looks good.”**
This closes the requested user review; no generic retest remains pending. It
does not establish broader device/assistive-technology coverage or authorize
a live-data copy or deployment.

**Previous accepted baseline, November 10: fixed summary neighbours.** Ryan
reports missing neighbouring links and scrolling trouble across the four service
pages with Safari’s address bar expanded. They requested only the immediate
previous summary above and next below each open box. The
[neighbour-layout record](../../docs/reviews/2026-11-10-peek-summary-neighbours.md)
tracks the deployed core, actual backup/snapshot and browser/recovery checks.
Deployed to staging39 #1380 at 14:32 UTC. Copy rehearsal passed **19,061/0**;
public browsers passed **15,646/0 across 16 suites** at 15:16:58, with complete
leaf evidence audited despite combined-console truncation. Actual snapshot
verification passed at 15:19:21: exact core/four data state before and after
19 HTTP200 renders. Ryan accepted the current iPhone Safari layout on November
10: “Looks good on Safari. No problems I see.” This closes the user acceptance
check; it is not live-copy or deployment permission. Release-package work remains;
the temporary live-copy scope was subsequently approved on November 11, as recorded above.
The separate index overlap recorded in 390×500 captures is addressed by the
November 11 staging update above, accepted by Ryan with “Looks good.”

At that previous baseline, recovery started with `core-neighbours` → frozen v7, using
`peek-template-core-neighbours-before-20261110-143200-81725f33.json`,
after a fresh verified backup and exact ownership/state checks. All older shared-core
recovery sequences below apply only after that restoration.

**Release preparation, November 10: not ready for live.** The [readiness record](../../docs/reviews/2026-11-10-peek-release-readiness.md)
contains the exact scope, new read-only live/staging inventories and current
acceptance blockers. At that inventory, all 15 shared page bodies matched after
origin normalization, and staged core/four data payloads matched source. Live
metadata is preserved. The localhost-only first-install/rollback package is
now refreshed to the accepted quiet-navigation core and Search candidate above; no live enablement or request exists. Ryan's
latest no-problems Safari report accepts the current neighbours layout. The
earlier contradictory Privacy/menu reply is historical, not a renewed generic
retest requirement. No per-route, device-version or assistive-technology
certification is inferred. This acceptance update changes notes only.
The fourth live-sourced disposable-copy rehearsal identified the first draft's
full-state mismatch: both shared WPCode category counts rise from 17 to 18 for
an inactive snippet. The installer incorrectly counted only published records.
That correction passed first-draft creation and attribute initialization in copy5.
Code correction then stopped on WordPress 7.1.4's exact `&` to `&#038;` GUID
conversion, confirmed against official source. A narrowly specified save-phase
expectation passed source CI (39/0); full-state and rollback checks remain exact.
Automatic approval review blocked copy6 before execution because the new live
database/uploads copy needed explicit scope permission beyond staging39. That
scope was subsequently approved on November 11; no copy6 request was committed or dispatched. No complete
install or browser phase has passed in this release-copy lane; see the readiness
record for actual attempts and copy-only journals.

**Previous baseline: input-focus core and Rail practice updates on staging39.**
See the [canonical phase record](../../docs/reviews/2026-11-09-peek-input-focus.md) for complete attempts, hashes, backups
and evidence. Core apply **42729961919** wrote #1380 at **2026-11-10 00:40:21 UTC**
and finished **00:45:55 UTC, PASS**; fresh-copy proof passed **14,386/0 across
15 suites**, with exact pre/post-browser rollback. All 19 served routes and the
core/four data records matched before/after rendering. Public **42731171781**
passed **12,374/0 across 14 unique suites** at **01:04:18 UTC**, including 128
focus checks and all eight summary jump/focus cases. Snapshot verification
**42731765629** passed **01:05:05 UTC**: exact core/four data records before/after
19 HTTP200 renders. Independent public/exact-state audits are complete; all four
focus captures match reviewed copy views. Broad wording/URLs/schema/PHP passed;
**20/20 live-reference pixel differences are not a visual pass**.

Rail apply **42724350260** updated #1348 at **2026-11-09 23:56:52 UTC**; its
fresh copy passed **2,632/0** and snapshot verification **42727758208** passed.
Public Chromium passed **121/0**, but Firefox **115/2** drawer mouse-open failures
remain unresolved. Diagnostic **42734807322 (64/1)** showed gestures 1–4 are
sufficient to reproduce missing mouse input; the underlying cause is unproved.
The first hold split **42736953507** was INVALID HTTP202 (zero assertions).
Its once-later retry **42738993282** finished **01:55:11 UTC, 26/1**, with two
loads and a verified HAR: hold-to-go alone reproduces the later missing mouse
input; gestures 1–3 pass. Offline replay did not reproduce it but remained
INVALID because the isolation proxy blocked one browser-level connection.
The mechanism is unproved; no runtime patch follows. This staging-only practice
investigation is paused and does not block the separate visitor release.

Actual recovery snapshots, both verified encrypted off-site:

- Core: `peek-template-core-input-focus-before-20261110-004021-4570ecbf.json`.
- Rail page: `rail-input-before-20261109-235652-0adc77b9.json`.

Only after `core-neighbours` restores frozen v7, and with fresh verified
backups/full-state checks, may `core-input-focus` restore v6, followed if needed
by older Kiro→v5/title-rail→v4 routes. Rail recovery uses
its independent full-state `rail-input` route, never the old delete/recreate
installer. No fallback-only interval occurred. Safari acceptance was incomplete
at this preceding stage; the current neighbours layout is accepted above.
Official Linux WebKit now installs and launches in Actions, but its public attempt was INVALID HTTP202 with zero
assertions. The later neighbours copy6 passed WebKit **1,304/0**; that is
Linux WebKit evidence, not physical Safari. Real OS text scaling and assistive
technology remain unverified. Main/live remain untouched.

**Previous deployed core: Kiro fixes, frozen v6; historical public/stored proof.**
The [fix and verification record](../../docs/reviews/2026-11-09-peek-kiro-fixes.md)
tracks disclosure touch/Enter corrections, larger short-screen reading areas,
FAQ/text clearance, wrapped titles, shorter single-entry rails and opaque strips.
All-page rails and four approved data sets remain. Apply **42697003089**, source
`8f4a3fb415b73cfe8c81071419c6da428f5b032a`, wrote #1380 at **20:52:52 UTC** and
finished **20:54:46 UTC, PASS**. Previous deployed code: **102,649 bytes**, SHA256
`dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1`.
Gates passed **31/0 + 1 rendered-only skip**. Fresh-copy rehearsal **42693471206**
passed **14,249/0 across 14 complete suites**, including native touch 387/0 and
Thai gestures 491/0. Twenty-two selected captures and the full 133-image manifest
are retained; twelve were freshly viewed, ten match earlier reviewed captures.
All 19 origin routes/scope/plain-row checks passed. Fresh export
`staging39-before-peek-nav-20261109-205236.sql` and actual snapshot
`peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json` were verified
encrypted off-site; export verification preceded the write. Restore import 0,
104/104 fingerprints differ only on #1380/cache. No fallback-only interval.
Automatic post-change checks were **INVALID HTTP202** at **20:54:27 UTC**, with
zero checks. A single later retry **42698232542** finished **21:03:22 UTC**:
both edge requests/all 12 HTTP checks returned 200; code/rendered wording/
posture/PHP passed and schema passed 27/27, zero parse errors. Overall **FAIL**
is 20/20 live pixel dimension differences, zero missing captures, not an
aggregate pass. All 20 deployed full-page captures were independently viewed,
with six v5 comparisons and verified hashes, without a new persistent layout/
image blocker. Existing transient labels and Contact blur/whitespace remain;
these closed-state views alone do not prove focused interactions.

Focused public **42699275642** finished **21:19:05 UTC, PASS**: **12,237/0 across
13 complete unique suites**, with no INVALID. Disclosure checks passed Chromium
66/0 and Firefox 41/0, layout 158/0 and 134/0, native touch 387/0; retained title,
page, clearance and matched-capture suites completed. Chair's native outer-card
swipe moved 0→21 px at scale 1/390×180/zero offset, with page/summary unchanged.

Independent audit verified all 33 streams, matching 12,237 literal/numeric
PASS counts, 16 empty stderr logs and 16 zero exits. The full inventory contains
123 decoded JPEGs. Fifteen of 24 selected public captures were freshly viewed;
nine exactly match earlier reviewed rehearsal captures. No new visible blocker
was found. Transient labels, approximate 150% root-text brand wrapping and one
closed Therapeutic tablet row's ellipsis remain documented in the fix record.

Stored inspection **42697818891** passed **20:57:33 UTC**: published #1380 matches
the exact then-deployed 102,649-byte/hash payload and all four published data records
retain their original identities, titles, bytes and full hashes.

This is historical v6 proof. First restore v6 through the current input-focus
snapshot above, with a fresh verified backup and complete state checks. Only
then use the older `core-kiro-fixes` snapshot to restore v5, followed by the
title-rail route to v4. Frozen endpoints retain their meaning. Practice recovery
uses its separate actual full-state `rail-input` snapshot, never the old installer.
Thai touch-focus-to-BODY and Rail Firefox Enter were caveats at this v6 stage;
see the follow-up deployment and verification status above. Safari, physical
devices, OS text scale and real assistive technology remain unverified.
Rapid-switch delay was not reproduced or claimed fixed. Main/live untouched.

**Separate practice-page follow-up:** the first active pill on In-page Link
Ideas is unreadable in current and earlier title-rail/Kiro phone/desktop captures.
It predates this phase. Source suggests an ink-on-ink CSS-specificity conflict;
computed styles are not yet checked. This separate prototype issue is not fixed
by the Rail or shared-core focus work.

**Historical assessment stage (2026-11-09, before the fixes above):** Ryan returned Kiro's
assessment. The [assessment and Codex verification](../../docs/inter-model/2026-11-09-peek-nav-kiro-assessment.md)
record two independently reproduced staging39 Privacy defects: native touch can
land about 839 px above the viewport, and Enter on the preview toggles the target
disclosure twice. Existing native-touch tests miss the large preceding panel's
open state; normal and reduced motion both need coverage. The new layout probe
stopped on HTTP202 with zero cases, so Therapeutic's small landscape reading
area and FAQ/text overlap remain partly based on Kiro's evidence and source.
Keep the all-page rails; do not silently adopt Kiro's two-entry eligibility rule.
No runtime/wording/ops/site changes were made during the assessment itself.
The subsequent deployed fixes and v5 freeze are tracked above.
Physical-device, assistive-technology and rapid-switch questions remain open.

**2026-11-01 current release lane (supersedes older status):**
A/B/C are LIVE through Ryan's own #68/#69/#64 merges/runs. A 262 browser passes;
B strict 12-page semantic/card verification zero expectations; C origin 12/restore
import0/no SEO, ContactCSP 4 views/0, public C 1,247/1248 with sole security.txt 404.
Initial C post-change INVALID 202; later once-only retry completes rendered Home/
client/posture/PHP checks, expected old-reference schema/pixels FAIL. Do not call
full C suite or hosting all-pass. [C actual proof](../../docs/reviews/2026-11-01-bundle-c-live.md).
Actual C snapshot bundle-c-before-20261101-175205.json and fresh export verified
encrypted off-site; A/B snapshots retained. No static-file/rule/support-message change.

**D is LIVE via Ryan’s #65/run41338572399.** Origin53 and focused public D128
assertions PASS; nine pages/three widths/lightdark/noJSerrors. Automatic postchange
was INVALID202/zero tests. Restore import0/intended differences/noSEO or CSS drift.
Actual snapshot bundle-d-before-20261101-185003.json and fresh export verified
 encrypted offsite. [D actual proof](../../docs/reviews/2026-11-01-bundle-d-live.md).

**E is LIVE via Ryan’s #66/run41340701287, applied19:14UTC.** Origin26 PASS;
Native ownership proof **9 checks PASS**; focused public E browser check **INVALID** (hosting robot challenge on the initial WebP request, zero assertions). It remains pending access; no retry loop. Generic public code/rendered/posture/pages/PHP PASS; historical
stage36 schema27/23 and20 pixels FAIL (zero parseerrors/missing views). Restore
import0/intended differences/noSEO/CSS drift. Exact snapshot
bundle-e-before-20261101-191447.json (SHA0ffe75e3aa18) and fresh DB5976437B
(SHA1e319bac239b) verified encrypted offsite. [E actual proof](../../docs/reviews/2026-11-01-bundle-e-live.md).

**A–E deployed; E focused public check blocked.** Review/merge the completion-proof PR after Gates is green;
no new LIVE request/run is required. The completed E apply ID must not be reused.
[[site-review-release]] / [current record](../../docs/reviews/2026-11-01-review-live-release.md).

Keep F #67 paused: adminHEAD403/no six-option installer/write. C security.txt404/
uninstalled static owner fallback, intermittent hosting protection, pending Butsaba
2/3/4/6/7/length consistency, G November14/15 sentences and owner credential/deletion/
Astra/www/menu tasks remain. Agents never merge main or dispatch LIVE.

- **Previous Peek title-only rails baseline (2026-11-09, frozen shared v5): public/stored checks passed.** #1380 was written **17:37:37 UTC**; apply **42666333809** finished **17:41:06 UTC, PASS**. Previous code: **97,130 bytes**, SHA256 `c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`. Shared title-only rails use existing jump labels/targets, otherwise visible H2 headings or one page-title entry. Keep Rail/Index practice rails; Progress A–D uses the shared rail, E hides it/releases padding for its demo. No new summaries/IDs/hashes; four approved data sets unchanged. [Previous evidence and recovery](../../docs/reviews/2026-11-09-peek-title-rails.md) records Gates **29 PASS / 1 SKIP / 0 FAIL / 0 INVALID**, fresh-copy **13,836/0**, 40 actual title-rail captures compared/reviewed and the earlier behavioral/visual failures. Nineteen origin scope checks and wording/posture/pages/PHP/schema passed; broad live pixel comparison **20/20 different**, not an aggregate pass. Fresh SQL `staging39-before-peek-nav-20261109-173723.sql` and actual snapshot `peek-template-core-title-rail-before-20261109-173737-cae68f35.json` were verified encrypted off-site; restore import 0, 104/104 fingerprints differ only on #1380/cache. No fallback-only interval. Public **42667176429** finished **17:49:09 UTC, PASS**, **11,829/0** across nine unique suites; both title matrices covered 21 actual routes in Chromium/Firefox. Stored **42667209210** passed **17:49:42 UTC**: published #1380 exactly matched those previous bytes/hash and four data records retain their original bytes/hashes. All 20 actual deployed full-page captures were reviewed without a new persistent layout blocker; final public capture/log review is tracked in that previous record. After restoring v6 through the current input-focus snapshot, then reversing `core-kiro-fixes` to v5 using its own actual snapshot, and taking a fresh verified backup/full-state checks, reverse `core-title-rail` using its actual snapshot under `$HOME/private-backups/` to v4, then older routes. Frozen v4/v5/v6 and pinned v3→v4→v5→v6 remain intact; the separate input-focus update is deployed and must be reversed to v6 before this older chain. Page-only data removal now leaves a title-only rail and strip; the native jump row remains without JavaScript. New summary wording needs approval; Safari/physical-device/assistive-technology checks remain open. Thai touch-focus loss and Rail Firefox Enter were historical v5 caveats; see the follow-up above. Practice pointer preview/Go passed at this earlier stage. Reported rapid-switch/loading latency was not reproduced or claimed fixed. Main/live untouched.

- **Historical Thai touch focus finding, superseded by the follow-up status above:** a local native-touch trace reproduced the destination H2 briefly receiving focus, then losing it to `BODY` after the compatibility mousedown; the jump/hash still worked. This older summary-rail behavior is unchanged by the title-only-rail deployment, not a new regression. [Trace and scope](../../docs/reviews/2026-11-09-peek-title-rails.md#retained-local-evidence) are retained; physical-device and assistive-technology confirmation remain open.

- **Earlier Peek all-page title strip and larger summaries (2026-11-09, frozen shared v4): historical public browser check blocked by HTTP202.** Written to #1380 at **10:24:52 UTC**; apply **42600617720** finished **10:28:23 UTC, PASS**. Full fresh-copy Chromium/Firefox suites, visual review, 19 origin scope checks and backup/restore passed. [Earlier evidence/recovery](../../docs/reviews/2026-11-09-peek-title-strip-all-pages.md) and [summary-space behavior](../../docs/reviews/2026-11-09-peek-summary-space.md) preserve the earlier failures and limitations. Public **42601203134** was **INVALID HTTP202** at **10:30:14 UTC**: zero assertions/routes/captures, quick subset skipped; that earlier attempt provided no public verification, with no immediate retry queued. Stored **42601221631** passed **10:30:40 UTC**: exact 86,567-byte core SHA256 `4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`, four approved data records unchanged. Actual snapshot `peek-template-core-title-strip-before-20261109-102452-90630a29.json` and fresh SQL export were verified encrypted off-site; restore import 0, only #1380/cache differences. No fallback-only interval. After input-focus restores v6, Kiro-fixes restores v5 and title-rail restores v4, recovery restores v3 through the exact `core-title-strip` route and this actual snapshot, after a fresh verified backup/current-state checks; older transitions follow only afterward. The payload is now frozen as shared v4 and `core-title-strip` pins v3→v4; later title-rail changes use the separate transition above. Short/unlisted/practice title strips are enabled; additional title rails follow the deployed scope above. The reported loading delay was not reproduced; Safari, physical devices and actual assistive technology remain unverified. Main/live unchanged.

- **Peek shared template (2026-11-09, staging39 only):** all four service pages are deployed: core **#1380**, Thai data **#1381**, Therapeutic **#1382**, Deep Tissue **#1383**, Chair **#1384**. Ryan approved the exact three new wording sets; each page passed its rehearsal, guarded apply and public checks (Therapeutic **168/0**, Deep Tissue **190/0**, Chair **232/0**, recorded at their respective rollout stages). [Service rollout evidence and page-only recovery](../../docs/reviews/2026-11-09-peek-service-rollout.md) preserves those results; the earlier **74/0 + 485/0** and six identical images belong to the [original Thai-only migration](../../docs/reviews/2026-11-09-peek-template.md). **Earlier short-summary correction deployed 07:59:57 UTC (apply job finished 08:04:07):** `core-summary-touch` updated #1380 in place, preserving long-summary/outer-card scrolling and all four approved data records without a fallback-only interval. Fresh-copy/preflight gates passed; actual export/snapshot were verified encrypted off-site and restore import returned 0, with only #1380/cache differences. [Earlier short-summary evidence and recovery](../../docs/reviews/2026-11-09-peek-short-summary-touch.md) records the actual snapshot, public suite PASS at 08:08:52 UTC and exact stored core/data confirmation at 08:09:31 UTC (71,826-byte #1380, four data hashes unchanged). Chromium did not reproduce the reported leak; device confirmation remains open. **Earlier clearance/touch correction deployed; apply job finished 07:15:10 UTC:** #1380 updated in place, all four data records unchanged, no fallback-only interval. Fresh-copy gates passed; fresh export/snapshot were verified encrypted off-site and restore import returned 0, with only #1380/cache differences. [Earlier clearance/touch evidence and recovery](../../docs/reviews/2026-11-09-peek-clearance.md) holds the actual upgrade snapshot and final checks: stored inspection passed at 07:17:32 UTC (exact #1380 hash/71,321 bytes; four data hashes unchanged); public browser verification was **INVALID HTTP202** at 07:17:03 UTC on the initial Thai request, with zero assertions/captures and no retry queued. Chromium native-touch baseline did not reproduce Ryan's reported page scroll; physical-device resolution is not claimed. New summary wording, Safari, physical devices and actual assistive technology remain open. After the current `core-neighbours` snapshot restores frozen v7, reverse `core-input-focus` to v6 using its actual snapshot, then `core-kiro-fixes` to v5 using its own actual snapshot, then title-rail to v4, then the title-strip update to frozen v3; then summary-touch to frozen v2; then `core-upgrade` can restore v1 and `core` can restore legacy, each with its own actual snapshot. Use page-only snapshots for one-page reversal. Main/live unchanged.

<!-- Reading-progress status is maintained with its implementation PR. -->
- **Reading progress (2026-11-04): LIVE on every public page since 08:00:26 UTC.** Ryan merged source [#71](https://github.com/alanmz-crypto/new-masseuse-site/pull/71) and release [#72](https://github.com/alanmz-crypto/new-masseuse-site/pull/72), then ran [41796118623](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41796118623) himself from main `1efeb7e`. Request `live-reading-progress-20261104-1` is completed; never reuse it. All twelve origin route checks returned 200 and code/rendered/posture/page/PHP checks passed. Generic schema 27/23 and 20 pixel differences are against historical staging36, not an aggregate pass. Actual export `live-before-reading-progress-sitewide-20261104-080010.sql` (3,916,951B, SHA prefix 3b30badd45f1) and snapshot `reading-progress-sitewide-before-20261104-080026-19484792.json` (SHA prefix f9c2d44651a9) verified encrypted offsite. Restore import 0, 94/94 fingerprints show only CSS 29/revision rows plus SiteGround Security activity-log row counts; pages, SEO, options and snippets unchanged. Live rollback removes the indicator (`ready-empty` before apply); staging's separate snapshot restores v1. Ryan confirmed tablet/iPhone operation after switching iPhone Reduce Motion off: intentional preference behavior, no code fix. Focused public live check `ui-progress-live-browser-20261104` passed **560/0** across all twelve pages, three widths and both themes; see the [live record](../../docs/reviews/2026-11-04-reading-progress-release.md). Staging remains verified 560/0 across 12 pages plus all five broad checks. Completion-notes merge only; no new live enablement/request/run.

- **Section guide (2026-11-04): LIVE since 11:46:23 UTC.** Ryan merged [#76](https://github.com/alanmz-crypto/new-masseuse-site/pull/76) at `f392749` and ran [41823566103](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41823566103) himself. Request `live-section-guide-20261104-1` is completed; never reuse it. Four service pages have the native guide with Questions and What Research Says, ordered by the actual headings; Reduce Motion is supported. Six origin checks passed. Fresh export `live-before-section-guide-20261104-114610.sql` (4,002,608B, SHA prefix8a33a892f676) and exact snapshot `section-guide-before-20261104-114623-1ca64312.json` (SHA prefix76ca289d81dc) verified encrypted offsite. Restore import0, 94/95 fingerprints: intended CSS29/new snippet1338/WPCode-cache/related row and activity-log differences; no page/SEO changes. Automatic broad public suite INVALID202/zero checks; [focused live browser check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41824272157) passed375/0 at11:53:34 UTC. [Once-only broad follow-up](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41824811088) completed rendered-copy/posture/pages/PHP checks; overall FAIL is the known old staging36 reference (schema27/23,20/20 pixel differences, zero parse failures or missing views). No further retry queued. Fresh live-source rehearsal passed install/no-op/exact removal/reapply and375/0 in each browser. Staging's v2→v3 update had all five broad groups and375/0 public checks pass. See [live proof and exact rollback](../../docs/reviews/2026-11-04-section-guide-release.md) and [historical staging proof](../../scripts/work-with-me-employer/section-guide/README.md). Touch is emulated; Safari/physical devices untested.

- **Page navigation (2026-11-04): LIVE since 13:43:20 UTC.** Ryan merged [release PR #78](https://github.com/alanmz-crypto/new-masseuse-site/pull/78) at 13:41:22 UTC into `5a451e6b6261c69636ae38a2a89edd397801d815`, then personally ran [41840605814](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41840605814), completed 13:46:22 UTC. Request `live-page-navigation-20261104-1` is completed; **never reuse it**. Services has seven destinations, and the four detail pages include Questions/What Research Says inline; the native guides copy the same server-rendered lists. All twelve origin checks passed, including current runtime/full-list checks. Export `live-before-page-navigation-20261104-134308.sql` (4,103,062 bytes, SHA256 prefix `af8f8017f71e`) and exact live snapshot `page-navigation-before-20261104-134320-6e7e4664.json` (SHA256 prefix `e0503a710e0f`) were verified and read back from encrypted offsite storage. Restore import 0; restored/current copies each had 95 fingerprints, with differences limited to snippets 1232/1338, WPCode cache and SiteGround Security activity/visitor log row counts. CSS (69,937 bytes), page copy and SEO stayed unchanged. Broad live checks completed copy/posture/pages/PHP successfully; overall FAIL is historical staging36 schema27/23 and20/20 pixel differences, with zero parse errors or missing captures. No retry queued; not an aggregate pass. Focused public request `ui-page-navigation-live-browser-20261104`, source `3478760acb2fdc2399bd40e689295f7334df1b48`, [run41872918516](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41872918516), finished at17:00:01 UTC **INVALID202/zero assertions**: the first request hit the hosting challenge and the check stopped without additional requests. It is not a feature failure or browser pass; focused public verification remains pending access, with no immediate retry queued. The completed deployment and twelve passing origin checks stand. Staging public checks and fresh live-copy Chromium/Firefox rehearsal each passed572/0; live preflight passed before launch. Local checks passed PHP/Bash syntax,81 installer mocks,14 host guards and39 rendered maps. Touch is emulated; Safari/physical devices untested. Source/staging PR #77 merged at13:20:44 UTC into `aea59a5`. Ryan reported the device loading issue resolved with his VPN; the availability diagnostic was cancelled before execution, with no website outage finding. Undo this paired migration before an older guide rollback, using the actual live snapshot above, never staging's. The exact command that prepares a separate rollback request is in the [live release record](../../docs/reviews/2026-11-04-page-navigation-release.md); no rollback was prepared or run. [Historical staging evidence](../../scripts/work-with-me-employer/page-navigation/README.md) remains recorded. No agent merged main or dispatched LIVE.

- **Historical in-page navigation stage (2026-11-08): staging39 only, nothing on live.** Current rollout, verification and recovery are recorded at the top of this note. Ryan chose **Peek + a display-only top strip** (from the practice pages /in-page-link-ideas/, /progress-nav-ideas/, /rail-nav-ideas/). It is on the real staging39 Thai page https://staging39.thaisolitude.com/thai-massage/ (snippet #1356: every feature of the mock-up, both Kiro reviews fixed, left-hand option, first-time hint, the index icon (design 1 Fan) always above the rail and blended into the page, the old link row under the header hidden (still the no-JavaScript fallback), no Back button, and the site header pinned with the strip right under it (snippet #1367: phone-menu flicker fixed; press the index icon and slide onto the rail; stacked index boxes; Kiro reviews 3-5 fixed; the top strip is now a sliding row of topic titles; then a scrollable summary in the open box instead of the inner preview, the fanned names in the closed boxes' form, and held 2 s the names stay open); sitewide on staging39 the 3px reading-progress bar and Astra's scroll-to-top button are removed (`install-sitewide-cleanup.php`); then a slide from the index icon onto a name opens its box (a page scroll from the icon was tried and dropped); short-idea summary lists ("etc." where ~60% or less), no "You are in"; Chromium 477+/0 on staging39 (snippet #1380; edge bug, Kiro template blockers and wording fixed; saved state #1369 in `link-ideas/saved/20261108-1369/`); desktop hover still open; Kiro handoffs: third `docs/inter-model/2026-11-08-peek-nav-kiro-review-3-handoff.md`, fourth `docs/inter-model/2026-11-08-peek-nav-kiro-review-4-handoff.md`, fifth (summary, kept list, slide onto a name, scroll dropped) `docs/inter-model/2026-11-08-peek-nav-kiro-review-5-handoff.md`, and the template review (Ryan: this design is the template for every page) `docs/inter-model/2026-11-08-peek-nav-kiro-template-review-handoff.md`. On live both still exist: removing them there needs Ryan's go); the earlier pills trial is removed. At that historical stage, Ryan's review and the other service-page rollouts were pending; those staging rollouts are now completed. Live still requires his go. Review handoffs for Kiro (web): first `docs/inter-model/2026-11-08-peek-nav-kiro-review-handoff.md`, second (after the index icon, pinned header and sitewide cleanup) `docs/inter-model/2026-11-08-peek-nav-kiro-review-2-handoff.md` (Kiro works on its own branch, PR into `claude/wizardly-bell-xu0rta`, never `ops/request.json` or `main`). See [[link-ideas-practice-page]].

## A. Happens by itself (nothing to do)
- **BOOKING DATE MOVED to November 14, 2026 (Ryan 2026-10-30: "Change booking begins to Nov 14th. Everywhere."; [[booking-date]]). LIVE since 2026-10-30 15:43 UTC, so the strip flips on 2026-11-14 00:00 Pacific (08:00 UTC, PST), NOT Nov 1; until it is live the text below (Nov 1) is what live does.** ~~2026-11-01 00:00 Pacific (07:00 UTC) the announcement strip flips~~ (old date): from "Currently booking corporate & group massage only." to "Now accepting corporate & group massage bookings." on nine pages. It is PHP in a WPCode snippet; the SiteGround page cache is OFF (checked 2026-10-25), so no purge is needed. See [[availability-update]].
- **The one-time check routine still points at 2026-11-01 07:30 UTC and CANNOT be edited by an agent (created over the API): Ryan edits it at its link to 2026-11-14 08:30 UTC, and the dates in its prompt (flip time, ids `...20261114`, branch `docs/2026-11-14-strip-flip-verified`, `push-strip-flip.sh` already uses Nov 14).** Old text: 2026-11-01 07:30 UTC a one-time cloud routine checks it (read-only): https://claude.ai/code/routines/trig_01PH2Rj5wHuq7vLHx9RuPdcQ . It opens either a one-sentence notes pull request from branch `docs/2026-11-01-strip-flip-verified` (= all nine pages flipped), or, if old text is still served after a retry, a pull request that PREPARES a cache purge (`live-strip-flip-1`). It never merges and never starts a live run. It opens its PR with the GitHub tool, not `gh` (see section G); its final message in the routine log says which method worked, so if no PR shows up, the routine link above still has the result.
- **SEO fixes are LIVE (2026-10-30 21:14 UTC, `live-20261030-210629`; [[seo-fixes]]).** Public checks ok on live. Still open: the live browser post-change test was blocked by the bot challenge (INVALID), rerun once later; Ryan requests Search Console indexing (seven main pages first).

## B. Ryan, from the phone, with dates
1. **Now:** tap the one-tap Google Calendar link Claude gave in chat on 2026-10-25 (event Sunday 2026-11-01, 8:00-8:15 AM Chicago, for Ryan's Gmail). If it is lost, ask a cloud session to rebuild it.
2. **(moves to Saturday 2026-11-14 once booking-date is live; the Nov 1 calendar event should move too)** **Sunday 2026-11-01, 8:00 AM Chicago:** open https://github.com/alanmz-crypto/new-masseuse-site/pulls .
   - A new PR from `docs/2026-11-01-strip-flip-verified`: check the PR page shows the Gates check green, then Merge (notes only).
   - A PR that mentions `live-strip-flip-1`: (a) Merge it FIRST (the live workflow reads the request from `main`; a run started before the merge is refused and uses nothing up), (b) open https://github.com/alanmz-crypto/new-masseuse-site/actions/workflows/site-live.yml > Run workflow > branch `main` > type exactly `live-strip-flip-1` (no spaces) > Run.
   - No PR at all, or the banner at https://thaisolitude.com/about/ still says "Currently booking": open the routine link in section A (its final message has the result), or start a cloud session and say "check the strip".
3. **Handoff ready (2026-11-01, Ryan: "Prepare a handoff to have a new agent fix all"):** start a fresh cloud session and send it: "Read docs/inter-model/2026-11-01-site-review-fix-handoff.md and docs/reviews/2026-10-31-site-review.md, then start with Bundle 0 and Bundle A on staging38. Staging first, rehearse first, live only on my go. Give me the Butsaba message first." The handoff lists seven bundles (each one PR and one live run for Ryan), what stays his (deletions, credentials, settings screens) and what waits for Butsaba.
4. **Any time (optional):** delete finished branches at https://github.com/alanmz-crypto/new-masseuse-site/branches : `feat/2026-10-24-corporate-massage`, `chore/2026-10-25-home-card-live-checks`, `ops/2026-10-25-live-verify`, `ops/2026-10-25-fp-compare`, `ops/2026-10-25-copy-reads`, `ops/2026-10-25-strip-routine-test`. And delete the two used TEST routines ("TEST of the strip check routine" and "TEST: can a cloud session open pull requests?") at https://claude.ai/code/routines . (Claude never deletes branches or routines; that is Ryan's.)

## C. Needs Ryan's computer or a long sit-down (NOT during the two weeks)
- **BEFORE the computer goes offline: copy the off-site backup key into the password manager.** The encrypted DB exports and rollback snapshots in the draft releases `offsite-YYYY-MM` can only be read with the private age key, which exists ONLY on his computer (`~/.config/thai-solitude/offsite-backups.agekey`, one `AGE-SECRET-KEY-...` line). Do it in a separate terminal window (not inside Claude, which would write it into the chat): `cat ~/.config/thai-solitude/offsite-backups.agekey`, select the one line, paste it into the password manager. Never put it in the repo, a chat or a PR. Rolling back a change through GitHub does NOT need the key (it uses the snapshots on the SiteGround server); only reading the off-site copies does. See `CLOUD-SETUP.md`.
- **Credentials rotation (raise from 2026-11-07, once per session, briefly; Ryan deferred it on 2026-10-24):** salts, admin and DB passwords, 2FA, login hardening. Take a SiteGround MANUAL backup first (Site Tools > Backups). GitHub cannot do credential work; this stays with Ryan, best at a computer. Why it matters: old public backup zips once sat on live containing `wp-config.php` and an admin password hash ([[staging31-backend-audit]]).
- Files that exist ONLY on his computer and are untracked in git: `docs/inter-model/00_README_..._PACKAGE.md`, `01_..._Full_Build_Brief.md`, `02_..._Local_Agent_Handoff.md`, `ThaiSolitude_WorkWithMe_Availability_Plan.md`, `thai_solitude_corporate_massage_agent_handoff_prompt.md`, `images/`, `thai-solitude-snapshot*`, `docs/receptionist/`. They are the source briefs for the corporate and availability work. If he wants them safe, a cloud session can commit the text files on his word.

## D. Decisions waiting on Ryan (no deadline; nothing guessed)
- **Wording that goes stale after 2026-11-14 (date and pages corrected 2026-10-31):** the FAQ pairs on the Thai, Therapeutic, Deep Tissue and Chair pages ("...with bookings beginning November 14, 2026." and "Corporate and group massage bookings begin November 14, 2026."), the corporate hero line and the Work With Me "For clients" block: fifteen sentences after Bundle A that read as future once the strip says "Now accepting". Proposal: one installer run on or after the 14th swaps them to "since November 14, 2026" / "are open (since November 14, 2026)", staging38 first ([[site-review-2026-10-31]] item 3.7). Needs his OK (his rule: never reword established copy without asking).
- **Site review 2026-10-31 ([[site-review-2026-10-31]]; report `docs/reviews/2026-10-31-site-review.md`):** ten decisions for Ryan at the top of the report (closing call-to-action blocks on the five long pages; Chair <-> Corporate links and a corporate card on Contact; Home card buttons to their own pages; the "Relief" chip; WordPress Address http -> https; the Services availability sentence; four search titles plus Privacy/Accessibility rows; after Nov 14 the past-tense wording and the page cache; the Nov 7 credentials work and four deletions). The handoff records approval for its listed staging edits. Bundle 0 and A–E are complete on staging38 and live; F is paused for admin access; G waits until November 14. The Butsaba draft now records Ryan's answers to 1, 5, 8 and 9; 2, 3, 4, 6 and 7 remain unanswered. The agent has sent no message.
- **"Mt. Tabor" to Portland (Ryan 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that."; one item at a time):** item 1 SEO titles/descriptions of Home, Thai, Therapeutic, Deep Tissue = MERGED (PR #47), applied on staging38, NOT live ([[portland-meta]]); item 2 Contact Address line = MERGED (PR #48), applied on staging38, NOT live; Ryan: "Skip the map" ([[contact-address]]); item 3 Home licence line + Thai H1 + Deep Tissue H1 = on branch `feat/2026-10-29-onpage-portland`, staging38 first, NOT live ([[onpage-portland]]). STILL to do, one at a time: the map (left as is), About "From Chicago to Portland", "For Practices" rename, the "bookings begin November 1, 2026" wording after Nov 1. **LIVE since 2026-10-30 (run `live-portland-items-1-3`, all checks ok; see the three notes).**
- **About page section "From Chicago to Portland"** ("...started Thai Solitude in Chicago in 2024... Now I am bringing Thai Solitude to Portland..."): LEFT for now. It is Butsaba's own history; the availability plan says not to keep relocation wording. Offer: draft a Portland-only version for side-by-side review, apply nothing until he approves.
- **Home button "Ask about a session"**: left (an inquiry link; the Contact page opens with the corporate-only strip).
- **Rename "For Practices" to "Work With Me" in the menu** (NOT done; needs his OK); a corporate button on Contact (LIVE in Bundle A); a permission-cleared chair-massage photo (the Services picture shows a table; his choice, flagged); five-hour capacity, table-massage room needs, start date, service area, which mailbox gets quotes (<butsaba-gmail> is used because the site already publishes it). [[corporate-group-massage]].
- **Chair Massage service page (2026-10-30; [[chair-massage-page]]):** LIVE since 2026-10-31 11:24 UTC (Ryan's own run `live-chair-massage-1`: page 1293, picture 1292, Services row, slug in snippets 1268/1232; all gates, restore proof and 73/73 browser checks pass on live; rollback snapshot `chair-massage-before-20261031-112429.json`, command in the note). Same change on staging38 (page 1302). Still his or Butsaba's: a Home card for chair massage (not added), a menu item (not added), the "10 to 20 minutes" and "six feet by six feet" figures in the copy, an Accessibility page line for the new research panels.
- **The price is settled:** $100 per service hour, no added fee exists anywhere ([[corporate-rate-100]]).
- Search Console "Request indexing" for `/thai-massage/` (his account, his click) if he wants Google to refresh the snippet.

## E. Butsaba answers and remaining questions

**Received from Ryan, 2026-11-01:** #1 “20”; #5 “60 and 90”; #8 yes; #9 yes.
The [question/answer record](../../docs/inter-model/2026-11-01-butsaba-questions.md)
preserves the exact reply: 20-minute chair length, 60/90-minute Therapeutic,
Deep Tissue and Thai options, call/text permission, and permission for both
Windy City Massage links and the reference offer. This is not a live go.
Do not infer mandatory/usual chair length or recalculate event capacity from
“20” alone. Website consistency work is still to be prepared and rehearsed.

**Still needed from that message:** #2 pregnancy; #3 table/mat, clothing and
draping; #4 Thai at events; #6 payment/deposit/cancellation; #7 event days/times.

- Confirm "No, you do not need to be flexible" (Thai FAQ). The old "Sessions take place in Mt. Tabor" and "I will reply to arrange a time" lines were REPLACED by the availability update, so those two no longer need confirming.
- Still unconfirmed: what the client lies on, clothing/draping, pregnancy and techniques; session lengths are now confirmed above ([[therapeutic-page-evidence]]).
- The three service pages' pictures show a client on a massage table while the FAQ says mat (Ryan said no new images).
- Client-privacy facts left out of the privacy policy ([[privacy-policy-page]]); the published (312) number is now approved for calls/texts; no replacement was requested.

## F. Older audit leftovers (low priority; verify before acting, they date from 2026-10-16 to 19)
- WPvivid is still ACTIVE on staging27/staging30 (deactivate or delete there); SiteGround backup-list glance; June form entry; Ryan's admin click-through on live wp-admin ([[staging31-backend-audit]]).

## G. Rules a cloud session must keep (full list in `CLAUDE.md` and `AGENTS.md`)
- **Live is Ryan's.** Never start "Site ops (LIVE)". Prepare the request, open the pull request, give him the two steps (merge first, then run with the typed id). Staging first; rehearse first; only ADD, never subtract or reword established copy without asking.
- **PR steward duty (Ryan's grant, 2026-10-25):** for every pull request Claude opens: say what it is in plain words, check that the Gates check is green and nothing conflicts, answer comments, give Ryan the merge steps, and keep the notes true after it merges. Claude never merges, force-pushes or pushes to `main`.
- Do not poke the live site with spoofed browser identities or bursts of requests (SiteGround answers 403 or the 202 challenge). Read from the server instead: `scripts/ci/request.sh run|inspect --target live ...` ([[siteground-bot-challenge]]).
- **In a cloud session `gh pr create` and `gh pr list` FAIL** (GitHub GraphQL is blocked, and `GH_TOKEN` reports invalid; tested 2026-10-26). `git push` works. Open pull requests with the GitHub tool `mcp__github__create_pull_request` (repo-scoped), or REST (`gh api repos/alanmz-crypto/new-masseuse-site/pulls -f ...`, or curl), and if none works push the branch and give Ryan the link `https://github.com/alanmz-crypto/new-masseuse-site/pull/new/<branch>`. Say which method worked.
- Routines created through the API attach ALL claude.ai connectors (Gmail, Drive, ...) by default; clear them straight away with `clear_mcp_connections: true`.
- Reply format Ryan asked for: plain language, the four lines "I finished / Next step / Next lane / See my work" (one click: a PR diff URL or one file) at the end of each finished phase, and a TL;DR.

<!-- ref: ./README.md — the notes index (this file is its first entry); ../../CLAUDE.md — how Ryan works and the cloud routine; ../../CLOUD-SETUP.md — the phone/cloud setup and the backup key -->

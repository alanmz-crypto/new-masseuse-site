# Peek shared template

**Current release preparation, November 11: Search contrast complete on staging39.**
The [Search phase record](../../../docs/reviews/2026-11-11-search-contrast.md)
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

The first-install release package now freezes the accepted quiet-navigation
core and this exact Search CSS, with four approved data payloads unchanged.
Its models passed **1,694 installer checks** and **162 evidence controls**, but
its complete first-install/rendered/recovery cycle remains unproved. The
[readiness record](../../../docs/reviews/2026-11-10-peek-release-readiness.md)
contains the concrete copy request. On November 11 Ryan explicitly approved
copying live's database/site files/uploads to a temporary GitHub Actions runner
for installation and rollback testing, then deleting the copy while retaining
logs/screenshots. This supersedes the earlier automatic scope rejection for
that rehearsal; do not ask again for the same scope. Live writes, live enablement
and main merges remain unauthorized. Safari acceptance is complete; fluidity
stays deferred.

**Current staging39, November 11: four navigation refinements complete and accepted.**
The automatic rail-side scrolling label is removed; interaction names remain.
The index has a solid outlined button and a tappable X while open. Outside taps
close the header menu; the index's existing outside dismissal remains.
See the [quiet-navigation record](../../../docs/reviews/2026-11-11-peek-quiet-navigation.md) for exact evidence.
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

Current recovery starts with `core-quiet-nav` → frozen v9, using only
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
See the [index-clearance record](../../../docs/reviews/2026-11-10-peek-index-clearance.md) for exact evidence.
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

**Previous accepted baseline: one summary neighbour above and below.** Ryan
requested this uniform layout after reporting missing links and scrolling
trouble with Safari's expanded address bar. The
[active phase record](../../../docs/reviews/2026-11-10-peek-summary-neighbours.md)
tracks that deployed core, tests and actual recovery snapshot. Its accepted
bytes are now frozen as shared v8; the preceding input-focus core is frozen as
shared v7. Recovery snapshot:
`peek-template-core-neighbours-before-20261110-143200-81725f33.json`.
Copy rehearsal passed **19,061/0**; public browser checks passed **15,646/0**
across all 16 suites. Complete leaf evidence was audited despite truncation of
the combined console log. Actual-snapshot verification passed at 15:19:21 UTC:
exact core/four data state before and after all 19 published HTTP200 renders.
Ryan accepted the current layout on iPhone Safari on November 10: “Looks good
on Safari. No problems I see.” Release-package work remains; its first-install payload is now refreshed to the accepted quiet-navigation core above. The phase
record also tracks the short-viewport index overlap later addressed above.
At that previous phase, its positioning code was unchanged and its rendered
origin was unproved.

At that previous baseline, recovery started with `core-neighbours` → frozen v7, using
`peek-template-core-neighbours-before-20261110-143200-81725f33.json`,
after a fresh verified backup and exact ownership/state checks. All older shared-core
recovery sequences below apply only after that restoration.

**Release preparation, November 10: not ready for live.** The [readiness record](../../../docs/reviews/2026-11-10-peek-release-readiness.md)
contains the exact scope, new read-only live/staging inventories and current
acceptance blockers. At that inventory, all 15 shared page bodies matched after
origin normalization, and staged core/four data payloads matched source. Live
metadata is preserved. The localhost-only first-install/rollback package is
now refreshed to the accepted quiet-navigation core and Search candidate above; no live enablement or request exists. Ryan's
latest Safari report accepts the current neighbours layout; the earlier
ambiguous Privacy/menu reply remains historical. Device/iOS details, per-route
results and assistive-technology certification are not inferred. This acceptance
did not itself authorize a live-data copy or live deployment. The separate
November 11 approval above now covers the temporary copy only.

**Previous baseline: input-focus core and Rail practice updates on staging39.**
See the [canonical phase record](../../../docs/reviews/2026-11-09-peek-input-focus.md) for complete attempts, hashes, backups
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

**Durable page-update lesson:** a script-only `wp_update_post` ran save hooks
that removed Spectra `_uag_css_file_name`/`_uag_page_assets` and changed SureRank's
check timestamp. The reviewed `rail-input` route instead uses byte-exact content
compare-and-swap through WordPress `wpdb`, targeted fresh cache reads and complete
state/snapshot guards. The copy's first unchanged-page GET moved only the known
asset cache (708→713 bytes); its second read was fully stable. Actual staging
kept the original 708-byte value through both purge/render cycles. Backups precede
warming and follow proved stability; install snapshots are never rebased. Exact
post-browser rollback caught the earlier drift even when all browser tests passed.

**Previous deployed core: Kiro fixes, frozen v6; historical public/stored proof.**
See the [completed fix record](../../../docs/reviews/2026-11-09-peek-kiro-fixes.md).
`peek-nav-shared-v6.php` is the previous payload: 102,649 stored code bytes, SHA256
`dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1`.
`core-title-rail` pins v4→v5 and `core-kiro-fixes` pins v5→v6. Frozen
`peek-nav-shared-v7.php` is the preceding input-focus payload, with its separate
v6→v7 route and actual snapshot above. The new `core-neighbours` route uses
v7→current; older snapshots retain their meaning.

Staging39 only. Title-only rails remain on visitor pages without an existing
rail, including one-entry pages; they now use a shorter 120 px maximum there.
The all-page read-only title strip, approved four-page summaries and ordinary
jump-row fallback remain. Neither main nor live was changed.

Existing `.ts-jump` rows remain the sole navigation/destination source where they
exist. Safely encoded `ts-peek-nav-config` JSON supplies the page-title fallback;
valid current-page `ts_pk_pages` registrations alone produce `ts-peek-nav-data`.
Admin/feed requests emit neither. WordPress/WPCode stores all deployed code and
data; the browser never requests repository JSON files.

`peek-data/<slug>.json` stores `[text, anchor]` items, approval provenance and an
editorial coverage classification. Anchors identify existing sections, not a
second navigation order. Thai wording is approved; Ryan approved the Therapeutic,
Deep Tissue and Chair sets in chat on 2026-11-09. All four service pages are deployed
on staging39: core #1380, Thai data #1381, Therapeutic #1382, Deep Tissue #1383,
Chair #1384. Actual snapshots, per-page results and final aggregate verification
are in the [service rollout record](../../../docs/reviews/2026-11-09-peek-service-rollout.md).
Unapproved phrases are stripped before public JSON emission,
and the runtime uses whole source paragraphs. No mechanical sentence splitting
is used for the summary fallback.

Run `python3 -I -B lint-gists.py` and `tests/gists-selftest.py`; both are in
`scripts/check-all.sh`, with PHP ownership/recovery/JSON tests. Lint cannot decide
semantic accuracy, overlap or percentage coverage. Approval must follow rendered
source review. `peek-pages.json` records the browser expectations and explicit
rollout status. `titleStrip` is enabled for all visitor HTML; `titleRail` is
enabled on pages without existing rails, with explicit practice-page exceptions. The old
`railEligibilityPending` list is removed. The four `pages` entries describe
approved summaries, not an allow-list for title strips or title-only rails.
The independently collected published-page inventory drives browser coverage.

## Previous Kiro deployment: frozen v6; historical public/stored proof

Privacy disclosure jumps now ignore a handled touch's compatibility mouse
press during settling; genuine new input still cancels. The preview consumes
Enter before focusing its destination. Short-screen cards prioritize usable
summary space and may cover the strip while staying below the visible header,
a tradeoff explained before implementation. FAQ boxes and Services text clear
the rail. Original headings remain the label source, with wrapping title rows;
preview edges are amber, the strip is opaque and shows page identity before
the first section, and side controls use plain Left/Right. Summaries, research
qualifications, icon gestures and all-page rail scope are unchanged.

Apply **42697003089**, source `8f4a3fb415b73cfe8c81071419c6da428f5b032a`, wrote #1380
at **20:52:52 UTC** and finished **20:54:46 UTC, PASS**. This was an in-place
WordPress update with no fallback-only interval. Gates passed **31/0 + 1
rendered-only skip**. Fresh-copy rehearsal **42693471206** passed **14,249/0
across 14 complete browser suites**, including native touch 387/0 and retained
Thai gestures 491/0. Twenty-two selected captures and the 133-capture manifest
are retained; twelve were freshly viewed and ten match earlier reviewed images.
All 19 published origin routes/scope/plain-row checks passed.

Fresh SQL `staging39-before-peek-nav-20261109-205236.sql` (4,519,440 bytes,
SHA256 prefix `cd6f32c1920a`) had matching server/runner checksums and encrypted
off-site read-back at **20:52:44.990 UTC**, before the **20:52:52.561 UTC** write.
Actual snapshot `peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json`
(SHA256 prefix `3d55f4d1ca91`) was verified encrypted off-site at **20:52:59.673 UTC**.
Restore import returned 0; all 104/104 fingerprints were compared, with changes
only to #1380 and WPCode cache. Automatic post-change checks were **INVALID
HTTP202** at **20:54:27 UTC**, with zero checks. The single later retry
**42698232542**, source `fdf441d503ab0b6449b3793d0fdda26a7b7d0f19`, finished
**21:03:22 UTC**: both edge requests and all 12 HTTP checks returned 200;
code/rendered wording/posture/PHP passed and schema passed 27/27 with zero
parse errors. Its overall **FAIL** is 20/20 live pixel dimension differences,
with zero missing captures; this is not an aggregate pass. All 20 deployed
full-page captures were independently viewed, with six v5 comparisons and
verified hashes, without a new persistent layout/image blocker. Transient labels
still touch the Accessibility strip/Work With Me hero; Contact blur/whitespace
predate this change. These views do not prove gestures or open-card behavior.

Focused public browser run **42699275642**, source
`0c47495e67d06dd5a5caac1479476f5ff4d76d19`, ran **21:06:26–21:19:05 UTC, PASS**:
**12,237/0 across 13 complete unique suites**. Disclosure checks passed Chromium
66/0 and Firefox 41/0; layout 158/0 and 134/0; title rails 1022/0 and 1018/0;
title strips 1110/0 and 1107/0; page data 172/0; clearance 3340/0 per browser;
native Chromium touch 387/0; matched-capture subset 342/0. Every nested wrapper
completed with no INVALID. The actual Chair outer-card native swipe moved
0→21 px at scale 1/390×180/zero viewport offset, while the page stayed at y704
and its summary stayed still. Independent audit checked all 33 streams: the
13 leaf suites total 12,237/0, literal PASS counts match numeric summaries,
16 stderr logs are empty and all 16 exit records are zero. The complete image
inventory contains 123 decoded JPEGs. Of 24 selected actual public captures,
15 were freshly viewed, including the sole changed Services-left view; nine
exactly match earlier reviewed rehearsal images. No new visible blocker was
found. Existing transient labels, approximate 150% root-text brand wrapping
and one closed Therapeutic tablet row's ellipsis remain documented in the
linked fix record. The full-page audit above is also complete.

Stored inspection **42697818891**, source
`3d919579cefcbbbb721ea66a76fd2de47d87548b`, passed **20:57:33 UTC**: published #1380
matches the exact then-deployed 102,649-byte/hash payload; all four published data
identities, titles, bytes and full hashes match the unchanged baseline.

This is historical v6 proof. First restore v6 through the current input-focus
snapshot above, with a fresh verified backup and complete state checks. Only
then use the older `core-kiro-fixes` snapshot to restore v5, followed by the
title-rail route to v4. Frozen endpoints retain their meaning. Practice recovery
uses its separate actual full-state `rail-input` snapshot, never the old installer.
Thai touch-focus-to-BODY and Rail Firefox Enter were caveats at this v6 stage;
see the follow-up deployment and verification status above. Safari, physical
devices, OS text scale and real assistive technology remain unverified.
Rapid-switch delay was not reproduced or claimed fixed. Main/live untouched.

All 22 approved summary destinations are visible H2 headings, so the old
600 ms closed-disclosure branch is not exercised by their current maps;
unifying the two jump engines remains separate work.

## Previous title-only rails: frozen shared v5; public and stored checks passed

The shared core reuses Peek's rail/index gestures and preview/jump controls with
`.ts-pk-titles` boxes containing titles only. Existing row labels/targets come
first, including closed native details; otherwise visible main H2 headings supply
sections, with a single page-title entry for no-heading or fixed-search pages.
Existing IDs remain links; headings without IDs use buttons targeting the actual
DOM node without inventing an ID or hash. The index still does not scroll the page.
The four approved summaries and their paragraph fallbacks remain unchanged.

Preserve the existing rails on `/rail-nav-ideas/` and `/index-nav-ideas/`.
Progress modes A–D gain the title rail; mode E hides it and releases its rail
readiness/padding while the demo rail is active. Without PointerEvent support,
the strip and ordinary links remain. No new summary text or data snippet is added.

`core-title-rail` applied the exact frozen v4→v5 transition to #1380 in place
at **17:37:37 UTC**. Apply **42666333809**, source `267c93e94f2b81ae63843be8f2e806805a061bd3`,
finished **17:41:06 UTC, PASS**. There was no fallback-only interval, and all four
page-data records were retained. Before this write, Actions Gates passed
**29 PASS / 1 SKIP / 0 FAIL / 0 INVALID**; fresh-copy rehearsal **42662818962**
passed **13,836/0** across ten suites. All 40 actual title-rail captures were
compared/reviewed, including both-side Search controls at three widths. The
previous record preserves the failed first rehearsal and second rehearsal's
manual Search-form overlap finding; the third corrected that overlap.

Fresh SQL export `staging39-before-peek-nav-20261109-173723.sql` (4,497,790 bytes,
SHA256 prefix `6bcb28a1f60b`) was verified encrypted off-site before the write.
Actual snapshot `peek-template-core-title-rail-before-20261109-173737-cae68f35.json`
(SHA256 prefix `d236a7d633bd`) was also verified encrypted off-site. All 19 origin
scope checks passed; rendered wording, posture, pages, PHP and schema (27/27)
passed. The broad live comparison had 20/20 pixel differences, so it is not an
aggregate pass. Restore import returned 0; all 104 restored/current fingerprints
were compared, with changes only to #1380 and WPCode cache. All 20 actual deployed
full-page captures were reviewed without a new persistent layout blocker; these
closed-state views do not prove gestures or open-card behavior.

Public run **42667176429** (source `f4a03e677a07e6e488c8aa5b7546c5e306db94ef`)
finished **17:49:09 UTC, PASS**: **11,829/0** across nine unique suites in 358 seconds.
Title rails passed **1022/0 Chromium, 1018/0 Firefox**; title strips **1110/0, 1107/0**,
each over 21 actual routes. Retained clearance passed **3340/0** in each engine,
native Chromium touch **378/0**, pages **172/0**, and matched capture subset **342/0**.
This public run encountered no HTTP202 denial. Stored inspection **42667209210**
(source `eb1ed7143e78a5b1ca7798ddd490189b35ce5872`) passed at **17:49:42 UTC**:
published #1380 exactly matched the previous 97,130-byte payload (SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`); #1381–1384
retain their original bytes/hashes. The previous record tracks the retained-log
and final public capture review.

Safari, physical devices and actual assistive technology remain unverified.
Thai touch-focus loss and Rail Firefox Enter were historical v5 caveats;
see the follow-up above. The earlier practice pointer preview/Go checks passed.
The reported rapid-switch/loading delay was not reproduced or claimed fixed.

For recovery, first take a fresh verified encrypted off-site backup and check
complete current ownership/state with the matching reviewed package. Only after
reversing input-focus to v6, then Kiro-fixes to v5 using their own actual snapshots,
use explicit
`core-title-rail --rollback $HOME/private-backups/peek-template-core-title-rail-before-20261109-173737-cae68f35.json`
arguments to restore v4 before any older route below. `core-title-strip` remains
pinned to exact frozen v3→v4; this previous payload is frozen as shared v5 and
`core-title-rail` pins v4→v5.
Page-only data removal under the current core leaves a title-only rail and strip;
the original jump row remains available without JavaScript.

## Previous title-strip and summary-space deployment: frozen shared v4

The [all-page title-strip record](../../../docs/reviews/2026-11-09-peek-title-strip-all-pages.md)
tracks that earlier deployment, evidence and recovery. The behavior and results
in this section describe v4. Existing `.ts-jump` labels remain the
first title source. Pages without a usable row use visible content headings,
then their page/document title when no heading is available. Search pages keep
their page title fixed because results form columns, not sequential sections.
This is display-only:
it adds no destination IDs, summary wording or rail to unregistered pages.
The plain row remains available where it already exists, including without
JavaScript. `ts-pk-title-ready` governs the shared strip/header behavior;
`ts-pk-ready` continues to govern the rail. No title-strip eligibility cutoff
is applied to short or unlisted visitor pages.

Ryan also requested a larger, usable scrolling area throughout the existing
Peek summaries. The [summary-space record](../../../docs/reviews/2026-11-09-peek-summary-space.md)
tracks this addition to the same update. The regular summary cap increases
from 9.8em to 18em; when the stack does not fit, distant closed rows fold behind
the existing `+N` labels before the summary shrinks. Nearest rows stay visible
through intermediate fits; the final fit uses the existing outer-card fallback.
Further fit levels use 14/10/6.6em caps, then 4.4em on the smallest layout;
whole-paragraph fallback summaries remain visible there too. Native summary
and outer-card scrolling, measured touch permission, approved text and the
settled gestures remain intact. No new summaries or rail are enabled on
title-only pages. Structural fixture results are not actual copied-page or
physical-device verification; no intentional delay or reproduced loading
latency has been established.

`core-title-strip` is the new exact transition from frozen shared v3
(`peek-nav-shared-v3.php`, code SHA256
`a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac`)
to the reviewed combined title-strip/summary-space payload. It changed #1380 in place and retained all four
complete page-data records. Its separate `peek-core-title-strip-v1` bundle
binds old/new core and unchanged data states; drift or foreign ownership refuses.
There was no fallback-only interval. The code was written at **10:24:52 UTC**;
apply run **42600617720** finished **10:28:23 UTC, PASS**. Exact stored inspection
**42601221631** passed at **10:30:40 UTC**: #1380 is **86,567 bytes**, SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`,
and all four data identities/hashes are unchanged. The complete fresh-copy
Chromium/Firefox suites and 19 origin scope checks passed. Public run
**42601203134** was **INVALID HTTP202** at **10:30:14 UTC**: zero assertions,
routes or captures; the quick subset was skipped. That earlier attempt provided
no public browser verification, and no immediate retry was queued.

The new localhost-only adapter has no preparation hook. It checks the untouched
staging39 copy with uploads, exact rollback/reapply, title matrices in Chromium
and Firefox, and the retained rail/clearance/touch/Thai suites. The reviewed
published-path inventory is checked again before a forward staging apply.
These commands record the completed route, including the blocked public check;
do not rerun the apply as a new version:

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/core-title-strip/install-peek-core-title-strip.php --with-uploads
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-title-strip'
# Only after offline, fresh-copy, rendered-wording and visual gates pass:
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-title-strip --apply'
scripts/ci/request.sh run --target staging39 --script link-ideas/core-title-strip/check-public.mjs --args https://staging39.thaisolitude.com
```

Every write, including recovery, requires a fresh verified encrypted off-site
backup. The actual export `staging39-before-peek-nav-20261109-102438.sql` and
snapshot `peek-template-core-title-strip-before-20261109-102452-90630a29.json`
were verified encrypted off-site. Restore import returned 0 with 104/104
fingerprints differing only on #1380/cache. The broad live comparison had 20/20
pixel differences, not an aggregate pass. After input-focus is reversed to v6,
Kiro-fixes to v5 and title-rail to v4, recovery uses the matching reviewed package, current full-state
checks and explicit
`core-title-strip --rollback $HOME/private-backups/peek-template-core-title-strip-before-20261109-102452-90630a29.json`
arguments to restore v3.
Only then can `core-summary-touch` restore v2, `core-upgrade` restore v1, and
`core` restore legacy, each with its own actual snapshot. Old snapshots cannot
reverse this new payload. `core-summary-touch` now pins v2 to frozen v3,
preserving the actual 07:59:57 snapshot's meaning.

This previous payload is frozen as `peek-nav-shared-v4.php`, and
`core-title-strip` is pinned to exact v3→v4. Reverse `core-input-focus` to v6,
then `core-kiro-fixes` to v5 and `core-title-rail` to v4 before this older route.
Safari, physical devices and actual assistive technology
remain unverified; the reported loading delay was not reproduced.

## Completed short-summary touch correction: frozen shared v3

The correction lets an open summary absorb swipes when its text fits,
while long summaries and the constrained outer card retain their own native
scrolling. Card and summary pan permission follow each element's measured scroll
range, including after resize. Approved wording, layout and rail/index gestures
remain unchanged. The
[short-summary correction record](../../../docs/reviews/2026-11-09-peek-short-summary-touch.md)
holds the reported behavior, source assessment and current verification status.
It was written to #1380 at 07:59:57 UTC on 2026-11-09; the apply job finished
08:04:07 UTC. Focused public browser checks passed at 08:08:52 UTC, and exact
stored core/data hashes were confirmed at 08:09:31 UTC. Local Chromium did not reproduce the
reported page scroll, so physical-device resolution is not claimed.

The fresh-copy run finished 07:54:50 UTC with clearance **3340/0** and page checks
**207/0** in each browser, native Chromium touch **266/0**, and retained Thai
gestures **485/0**. Exact rollback/reapply and rendered wording gates passed.
All 16 copied-page captures were independently reviewed: nine match v2 byte for
byte and seven differ only on the rail; card/text/strip/banner pixels match.
The 07:55:56 UTC preflight wrote nothing. See the linked record for full reports.

The five public suites passed with zero failures: clearance **3340/0** in each
browser, native Chromium touch **266/0**, page checks **172/0**, and the matched
capture subset **342/0**. All 16 common card/text/strip/banner captures match the
rehearsal pixels; five images are byte-identical and eleven differ only on the
rail. The linked record tracks the detailed visual review and retained logs.

`core-summary-touch` updates exact frozen shared v2 in `peek-nav-shared-v2.php`
to frozen `peek-nav-shared-v3.php`, keeping #1380 and all four data records intact.
Frozen v2 code SHA256:
`357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8`.
That earlier payload has 71,826 code bytes, SHA256
`a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac`.
Its separate `peek-core-summary-touch-v1` snapshot bundle binds complete old/new
core states and unchanged page-data states. Changed or foreign state refuses.
The update was in place, with no fallback-only interval.

Offline gates passed first. The localhost-only adapter has no preparation hook;
it imports the existing full browser suite map and retains the same 900-second
allowance and complete log. These commands record the completed route; do not
rerun it as a new version:

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/core-summary-touch/install-peek-core-summary-touch.php --with-uploads
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-summary-touch'
# Only after fresh-copy, rendered-wording and visual gates pass:
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-summary-touch --apply'
# Read-only aggregate checks after applying, using the shared suite wrapper:
scripts/ci/request.sh run --target staging39 --script link-ideas/core-upgrade/check-public.mjs --args https://staging39.thaisolitude.com
```

Every write, including recovery, requires the existing fresh verified encrypted
off-site backup. Actual export `staging39-before-peek-nav-20261109-075943.sql` and
snapshot `peek-template-core-summary-touch-before-20261109-075957-5ef2bd29.json`
were verified encrypted off-site. Restore import returned 0; the 104/104
fingerprints differ only on #1380 and WPCode cache. Origin page/scope and rendered
wording checks passed. The broad pixel check retained six differences versus live
(14/20 identical), so the automatic post-change suite is not an aggregate pass.
Recovery must
use the matching package and explicit `core-summary-touch --rollback FILE` with
that snapshot, restoring exact v2 after any later title-strip update is reversed
to v3 first. Only then may the separate `core-upgrade`
snapshot restore v1; only after that may the original `core` snapshot restore the
legacy core. The older snapshots cannot reverse this new payload. Complete
state drift, including a later page-data removal, makes recovery refuse.

## Completed clearance/touch correction: frozen shared v2

The title-strip clearance, closed-topic touch and reading-position corrections
were deployed to #1380 at 07:11 UTC on 2026-11-09 after the fresh staging39-copy
rehearsal, rendered-copy gates, exact rollback and visual review passed.
The [preceding correction record](../../../docs/reviews/2026-11-09-peek-clearance.md)
holds the actual baseline evidence, final public verification status and recovery snapshot. Its pre-fix public
check reproduced strip/card overlap; that diagnostic failure is not a release pass.
The exact deployed core/data hashes are verified. Final public browser checks
were INVALID HTTP202 with zero assertions/captures; no retry is queued. The
fresh-copy browser/visual results do not establish physical-device resolution.

`core-upgrade` remains the exact transition from `peek-nav-shared-v1.php` to
frozen `peek-nav-shared-v2.php` in uniquely owned #1380. It no longer follows the
mutable `peek-nav.php`, preserving the meaning of the actual 07:11:22 snapshot.
Frozen shared-v1 code SHA256:
`dda8aecd38e417ad37766d2df951c6bdc761bf44997e030bbef5a36e9321f573`.
The upgrade preserves the snippet's identity/settings and all four page-data
records (#1381–1384). It checks and snapshots complete old/new core states and
the four unchanged data states, using a separate `peek-core-upgrade-v1` bundle.
Changed, missing or duplicate ownership/data stops an actual upgrade or reversal.
There is no snippet swap or planned fallback-only interval.

The dedicated rehearsal adapter had no preparation hook: it exercised the
untouched four-page staging39 baseline, with uploads. These are historical
commands for that completed transition, not the new summary-touch route:

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/core-upgrade/install-peek-core-upgrade.php --with-uploads
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-upgrade'
# Only after the fresh-copy gates and actual copied-page review pass:
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-upgrade --apply'
# Read-only aggregate checks and matched captures after applying:
scripts/ci/request.sh run --target staging39 --script link-ideas/core-upgrade/check-public.mjs --args https://staging39.thaisolitude.com
```

The rehearsal includes exact rollback/reapply, rendered regulated-copy gates,
the actual-clearance matrix in Chromium/Firefox, native Chromium touch checks, and retained page/Thai gesture
checks. The core-upgrade and core-summary-touch adapters have a 900-second browser allowance; the complete
browser log is retained in the Actions artifact. The public wrapper retains
complete per-suite streams and reports every count/verdict after its captures.
Every staging write takes a fresh verified encrypted off-site backup.
Review the apply, backup, restore and public browser reports afterward, and record
the actual new snapshot in the correction record. The actual staging snapshot is
`peek-template-core-upgrade-before-20261109-071122-958bff3b.json`.

Recovery must name `core-upgrade --rollback FILE` explicitly in the push arguments;
the wrapper's preparatory preflight uses that component. Use the matching reviewed
package and actual upgrade snapshot after checking current ownership and complete state. Any later
core or page-data drift, including a page-only removal, makes that reversal refuse.
Reverse the later summary-touch update to exact v2 first.
Reverse this upgrade before attempting the historical shared-v1-to-legacy recovery
below. Neither an old migration snapshot nor a legacy deletion snapshot can undo
the upgraded payload.

## Historical legacy-to-shared-v1 migration

This section records the completed 2026-11-09 legacy-to-shared migration on
staging39 (#1380 core, #1381 Thai data). Do not repeat it as a new upgrade:
`core` remains tied to frozen `peek-nav-shared-v1.php`, preserving the original
snapshot's meaning. Current revisions use their separate exact upgrade routes above.
Original snapshots and verification are in the
[template record](../../../docs/reviews/2026-11-09-peek-template.md).

The original payload is frozen in `peek-nav-legacy.php`. Original legacy starting code
SHA256: `b7314c8938a728bf64b6dac16c48bba722b4aea59b277c9f56f0760132322c01`.
The core installer preserves the existing snippet ID/title/settings and snapshots
its complete original post/meta/taxonomies. A changed or duplicate record stops
the operation. Old legacy rollback snapshots must match their code hash too.

The completed migration ran offline gates and BOTH staging39-copy rehearsals
before either staging write. Historical commands (not a sequence to rerun against
the already-applied baseline):

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/peek-page/install-peek-thai-data.php
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/install-peek-nav.php --with-uploads
```

The core rehearsal adds the prerequisite Thai data only to its isolated copy,
before its baseline. The data rehearsal has its own browser check: data alone is
inert under the legacy runtime. The core rehearsal tests real copied page shapes;
prepared-page browser fixtures do not register them in WordPress.
Include uploads in the core rehearsal: they contain layout assets as well as
images. The 2026-11-09 asset-incomplete copy produced three geometry failures;
the unchanged package passed with uploads, without relaxing any assertions.

It then dry-ran and applied Thai data, followed by the core:

```sh
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page thai-massage'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page thai-massage --apply'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core --apply'
scripts/ci/request.sh run --target staging39 --script link-ideas/check-peek-nav.mjs --args 'https://staging39.thaisolitude.com'
```

Every write, including rollback, takes and verifies a fresh encrypted off-site
backup first. Read every report section: an overall operation PASS does not make
informational post-change differences pass. INVALID establishes no verification.
Record actual snapshots, not guessed names. Reversing this original migration
requires reversing any core upgrade first. Then restore the legacy core using
`core --rollback FILE`, and remove Thai data using `page thai-massage --rollback FILE`,
with each component's actual snapshot. Core reversal affects every Peek page.
For one service page, reverse only its page data using its actual snapshot in the
current rollout record. The completed migration had no fallback-only swap.
Under that original core, page-data removal restored the plain row. Under the
current deployed core, removal leaves a title-only rail and read-only strip;
the original plain jump row remains the no-JavaScript fallback.

Do not deploy summaries for another page until its wording/rollout is approved. An independently
edited or newer data/core payload is deliberately refused; a future version needs
its own reviewed exact-state migration, not deletion using an old snapshot.

## Completed approved service-page rollout

The three approved pages use the same installer; their rehearsal adapters only
fix a page identity on an isolated copy. The following sequence was completed for
`therapeutic-massage`, `deep-tissue-massage`, then `chair-massage`. Do not rerun it
against their already-applied baselines; future payload revisions need their own
reviewed migration. Historical Therapeutic example:

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/peek-page/therapeutic-massage/install-peek-page-data.php --with-uploads
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page therapeutic-massage'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page therapeutic-massage --apply'
```

Each rehearsal and its approved-card captures were reviewed before the write;
every backup/public/restore section was reviewed afterward. Each page's manifest
state was then changed to `enabled` before its public check, for example:

```sh
scripts/ci/request.sh run --target staging39 --script link-ideas/check-peek-pages.mjs --args 'https://staging39.thaisolitude.com --capture-page therapeutic-massage'
```

Public captures were reviewed before proceeding to the next page. The local-only
`--enabled-page` option changes rehearsal expectations, never public expectations
or WordPress data. `--capture-page` adds first/research-start/research-end captures
at three widths and checks card bounds and access to the final research item.
The current rollout record holds final aggregate-check status and actual per-page
snapshots. A page-only reversal removes only its data without changing the shared
core or other pages. Under the current core, that page keeps a title-only rail
and strip; its original plain jump row remains available without JavaScript.

The service rollout exposed a shared-placement defect: Chair's first preview
overlapped the initial strip and grazed the tablet CTA. The deployed correction
above uses the actual strip rectangle; its fresh-copy matrices passed 3340/0 in
each browser. Very short landscape screens retain the documented scrollable-card
fallback. Start/end research captures
and exact item checks do not claim visual review of every intermediate position.

## Local browser checks

```sh
python3 mock/build-mock.py
python3 -m http.server 8091 -d mock/site
# In another shell, TS_PLAYWRIGHT_ROOT points to a Playwright package.json:
node check-peek-nav.mjs http://localhost:8091 --fixtures
node check-peek-clearance.mjs http://localhost:8091
node check-peek-clearance.mjs http://localhost:8091 --firefox
node check-peek-touch.mjs http://localhost:8091
```

The generic suite is driven by the page manifest; the historical detailed Thai
gesture regression remains explicit in `check-peek-thai-regression.mjs`.
`check-peek-clearance.mjs` measures actual visible strip/header/card rectangles,
independently of the runtime's placement formula. It covers all four enabled
pages, both sides, page top, sticky transitions, kept cards returning to the top,
resize/landscape and enlarged-text cases, approved words and research reachability.
It injects no runtime or page data; local mocks remain structural evidence only.
`--quick` is a Thai/Chair diagnostic subset, not the full rehearsal gate. Public
staging39 runs use the same checker after deployment and require capture review.
`check-peek-touch.mjs` uses native Chromium touch input for control drags, taps,
short-summary/title/padding swipes, short-to-long resize transitions, long-summary
scroll containment and the constrained outer-card fallback. Its optional
`--baseline-v1` injects the frozen old core only into localhost fixtures; it is
refused for public staging39. Browser simulation is not a physical-device result.
Synthetic activation is not VoiceOver/TalkBack validation. Safari and physical-device tests
remain outstanding. See the [review and deployment record](../../../docs/reviews/2026-11-09-peek-template.md).

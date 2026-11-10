# Peek: one neighbouring summary above and below

**In preparation; not deployed.** Ryan reports the same box problem across the
service pages, particularly with Safari's address bar expanded. They requested
one uniform experiment: show only the immediately preceding summary above the
open summary, and the immediately following summary below it. First and last
sections have only their existing neighbour; there is no wraparound.

Work stays on `claude/wizardly-bell-xu0rta` and staging39. The separate live
release preparation is paused. This request does not approve the blocked
live-sourced copy or any live write.

## Evidence and design decision

Ryan's screenshot shows Thai Massage's “What to expect” preview with a left
rail, dark theme and expanded Safari address bar. Neither neighbouring topic
is visible. The screenshot establishes that missing-control state, not the
cause of a scrolling delay or a browser event trace.

The complete current runtime was read before editing. `cardHTML()` renders
every other section and distant-topic counts. Its compact `fit5` CSS hides
every closed row, and the fitter selects that state when a scrollable summary
has less than 120px of reading space. This source behavior explains how the
neighbours can disappear; it does not prove Safari's address bar caused the
reported scrolling problem.

The proposed simplification was explained before building: summary cards
contain only their immediate previous/open/next sequence, with no distant
rows or `+N more` labels. Available neighbours stay visible during compact
fitting. The existing rail and index still reach every section. Approved
summary wording, whole-paragraph fallback and title-only cards retain their
current behavior. Touching a closed neighbour opens its preview without
scrolling the page; touching the open box's jump area still jumps.

Fresh access checks confirmed GitHub pull/push/admin permission and a current
clean branch at `8ef8b663c2cbec7993bfa4c15102bf3d9351d6de`. The attached managed
environment reports a current enforced unrestricted policy. The inner process
could not reach its proxy; the permitted outer request reached staging39 but
received HTTP202. No unchanged public retry was made. Actual rendered evidence
will come from the authorized staging39-copy Actions rehearsal.

## Exact starting point and recovery

Current #1380 was frozen before runtime edits as `peek-nav-shared-v7.php`:
102,756 file bytes, SHA256
`ddd7e3b9df4a3c6cd45b3f7c46bc56c4e2c1df6c4553846879681843e60f639f`;
102,750 stored bytes, SHA256
`a3a503f59dc8a557a24e5a03967481af824f29136668b1158d9cfa47ae3e537c`.

The previous input-focus transition must stay pinned v6→v7, preserving the
meaning of actual staging snapshot
`peek-template-core-input-focus-before-20261110-004021-4570ecbf.json`.
A separate `core-neighbours` transition will update #1380 in place and verify
all four unchanged page-data records. No fallback-only interval is planned.
Any later rollback must use this new transition's actual snapshot, after a
fresh verified backup and exact current-state checks. No new snapshot exists yet.

## Candidate and local observations

The narrow candidate retains the existing fitter. Summary mode builds only its
two available adjacent rows, compact fitting keeps those rows displayed, and
viewport refitting avoids assigning an unchanged scroll position. The reading
record now keeps the reader's intended position separate from a temporary
rendered clamp. No flex-layout rewrite, touch-event rewrite or summary-data
change was needed. Current candidate: 104,522 file bytes, SHA256
`0459b427a2169659ab82b0c1ab9a2e1f839ef6bd1d6b8c5770754bb1c4ba7e27`;
104,516 stored bytes, SHA256
`bff5d9c7e210b5948f31cd8c07daf1390c7f66b00f3915b8d7fb033acf7fe2e5`.

A local structural-fixture probe passed **704/704** neighbour visibility and
hit-test cases: all 22 service sections, both sides, 390×640, 390×500, 844×342
and 320×568, normal/150% root text, and two page positions. These are simplified
fixtures, not captured site layouts or true OS text scaling. The primary agent
viewed Thai middle-section portrait and Therapeutic research portrait/landscape
captures: the previous/open/next hierarchy is clear; the last research section
correctly has only its preceding neighbour. These images do not establish
physical Safari behavior.

The full stress probe was **764/880**, with all 116 failures at 844×180. At page
top its visible 85px fixture header leaves only 75px for the card; simultaneous
controls cannot fit. The existing outer scroller remains. A separate fallback
probe retained eight failing combined assertions: Home/End can reveal the two
neighbours, and native outer scrolling left the page still, but Tab can leave
about 9px of the next row clipped, and the hint and next row cannot both fit in
75px. That extreme-height limitation is recorded rather than called a pass or
used to justify a broader redesign. JavaScript syntax checking passed.

The new deployment route discovers staging39 through configuration and raw
database reads before loading WordPress. It accepts one exact staging39 home,
then restricts normal WordPress commands to that selected installation. The
copy rehearsal uses an internal Docker network before its first WordPress boot.
These changes keep this experiment within the staging39 authorization.

The new transition has its own snapshot bundle and rejects snapshots from
other updates. Its copy gate verifies exact state after apply, repeat apply,
rollback and reapply, then repeats rollback/reapply after browser activity.
Deployment requires a fresh verified off-site backup before every apply or
rollback, and exact served runtime/data checks before and after public reads.
Offline PHP checks passed 1,142 assertions. The new deployment harness passed
190 checks; the historical input-focus harness remains at 157 passing checks.
These are local verification results, not evidence of a staging deployment.

The complete local source gate run passed 41 gates, with Ruff unavailable and
rendered URL checks deferred to Actions. The focused structural browser run
completed all eight page/side cases and 16 captures: **1,376 passed, 2 failed**.
Both failures exposed a real Chair research bookmark defect. After resizing
temporarily clamped an interior reading position to 33px, a later resize
treated that clamp as end intent and jumped to 102px. Keep the reader's original
interior bookmark across temporary clamps; only deliberate scrolling should
replace it. A deliberately chosen endpoint should continue following the end.
The correction passed 208 targeted structural-fixture Chromium checks across
all four pages and both sides, including 24 checks at a genuine zero scroll
range. Chair's 40px bookmark now renders 40→33→0→40 across actual viewport
changes. Deliberately scrolling to End follows the endpoint, while subsequent
ArrowUp/native swipes replace that intent. Summary identity, focus and page
position remain intact. The initial focused run remains recorded as a failure;
its complete matrix was rerun against the corrected candidate. Pressing
End when already clamped at the endpoint produces no movement and retains the
prior intent; the correction adds no keyboard or gesture override for that case.

The reading-intent candidate `5c086969…` passed its complete focused fixture
run **1,394/0**, all eight
page/side cases, eight fixture loads and 16 captures. It verifies original
bookmark retention and a real subsequent Home movement replacing that intent.
The clearance regression now likewise retains its original test bookmark
across resizes instead of mistakenly treating a temporary clamp as new intent.
Final PHP rechecks passed 1,142 installer, 35 data-emission and 59 rendering
checks. Independent source reviews found no remaining blocker in runtime,
browser checks, deployment or rehearsal. The new rehearsal harness passed 100
controls and the evidence wrapper 85; historical guards remain passing.
These local results do not replace actual WordPress or complete browser-engine
verification.

## First actual staging39-copy rehearsal

Source commit `cea4f37239df72cda30447583b537732dc667fac` passed all 42
[CI gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42768109302).
The complete job log was read; the sole skip was rendered checking without a URL.

[Copy rehearsal 1](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42768644282)
used request commit `1833c46ca9ce03549835bc51854f48794f813f0b`, sourced only
staging39, and finished **FAIL** at 06:32:29 UTC. Its first apply, idempotence,
exact original-state rollback, reapply, rendered wording gates and pre-browser
state verification passed. The isolated copy changed only #1380 and WPCode's
snippet-list cache. All four data records retained their exact state hash.

Chromium completed all eight page/side cases and 16 captures: **1,380 passed,
4 failed**. All failures were the new light-theme assertion expecting
`data-ts-theme="light"`; the actual site intentionally represents light mode
with no attribute. `theme-init.js`, `theme-toggle.js` and the existing theme
test establish that contract. The runtime remains unchanged. The corrected
checker requires the saved preference, exact attribute, one visible theme
control with the matching pressed state, and an opaque light/dark body palette.
Its actual-source local theme controls passed **25/0**, including deliberate
wrong and missing states. This failed run is not relabelled
as a pass. Firefox, WebKit, retained suites and post-browser recovery correctly
did not run after the failure. No staging write or staging snapshot exists yet.

The primary agent independently viewed actual-copy captures of Thai and Deep
Tissue middle summaries in left/dark 390×500 layouts, and Therapeutic and Chair
research in right/light 844×342 layouts. Adjacent controls are visibly separate
from the open summary, the real header/banner remain clear, and research keeps
several readable lines. This supports the proposed design but does not replace
the remaining browser gates or physical Safari acceptance.

## Second actual staging39-copy rehearsal

Theme-check commit `0151393f8d24dfbad61eb04cc83348d42ca4b8e5` passed all 42
[CI gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42769928414).
The complete log was read. [Copy rehearsal 2](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42770507508)
used request commit `60920429c6e5dca1c09892c1bb1fc6aef8c89fb4` and finished
**FAIL** at 06:49:04 UTC. Exact native apply/repeat/rollback/reapply, wording
checks and pre-browser state verification passed again.

Chromium passed **1,384/0**, all eight cases and 16 captures. The measured light
background was RGB(245,244,239), dark RGB(21,28,38), with the correct preferences,
attributes and visible controls. Firefox then lost Thai/right research's 40px
bookmark on the first 500→620 resize, returning to 0. Four bookmark assertions
failed; the subsequent Home precondition could not be exercised, so Firefox
stopped **incomplete/INVALID**, 150 passed/4 failed, zero completed cases, one
load and two captures. Those failures are diagnostic evidence, not a completed
Firefox validation. WebKit, retained suites and post-browser recovery were not
attempted. No staging write occurred.

Local Firefox reproduced the reset and isolated its cause. Initial card
placement changed reported `scrollHeight` from 278 to 279 while the same text,
styles, 272×176 client dimensions and fractional content height were unchanged.
The exact-height guard consequently ignored genuine scroll movements and
retained a stale zero bookmark. The correction permits at most one pixel of
total-height rounding, keeping node, client dimensions and viewport checks exact.
No acceptance threshold, gesture listener or recovery guard was relaxed.

The corrected candidate passed **200/0 Firefox** and **208/0 Chromium** local
structural reading-state checks across all four pages and both sides. Before
and after traces retain the original failure and show 40px surviving each
resize after the correction. Chromium also covered 24 genuine zero-range
transitions; the Firefox matrix had no exactly-zero range, so none is claimed.
An independent review of the entire affected reading path and traces found no
blocker. Fresh actual-copy verification is still required.

Local WebKit could not launch because this managed environment lacks six
required libraries. Its official isolated-image fallback hit Docker capacity;
only the task-owned failed image was removed. The Actions WebKit gate
remains mandatory, with no skip substituted for actual browser evidence.

## Third actual staging39-copy rehearsal

Reading-state commit `eeb1371fd06e7a7cbebc0f23d3443be68f3676f2` passed all 42
[CI gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42772039156).
[Copy rehearsal 3](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42772571723)
used request commit `69325ac5d14eaaf3c5cc3064576499226b28b547` and finished
**FAIL** at 07:13:54 UTC. Exact native apply/repeat/rollback/reapply, wording
checks and pre-browser verification passed. Only #1380 and WPCode's cache
changed; all four page-data records remained exact.

The focused neighbour suites completed every page/side case and all 48 planned
captures: Chromium **1,384/0**, Firefox **1,304/0**, WebKit **1,304/0**. Actions
installed and launched WebKit successfully. Retained input-focus **128/0** and
Chromium/Firefox disclosure checks **66/0**, **41/0** also passed.

The retained Chromium Kiro layout suite found a real compact-layout regression:
Therapeutic research at page top in 844×342 landscape provided **116px** of
reading height on both sides, below the retained **120px** requirement. Its
result was **168/2**. The remaining suites and post-browser recovery correctly
stopped. The primary agent viewed the actual right-side capture: the preceding
topic is visible and the site header is clear, but the reading area still needs
space recovered from the compact controls. The acceptance threshold remains
unchanged. This partial run is not a deployment gate pass; staging39 is unchanged.

The next candidate reduces only summary-mode `fit5` vertical open-box padding
from `.45rem` to `.25rem`. The 40px title/close target, 26px neighbouring rows,
horizontal padding and title-only cards retain their dimensions. This recovers
about 5.5px at the observed root text size for the native reading surface.

Independent source review found no blocker. Local structural checks measured
116→122px of research reading height across all four services and both sides;
all 704 practical neighbour cases passed. Reading-state checks passed Chromium
208/0 and Firefox 200/0. The close button remains 40×40 and clear of the summary.
An additional combined 150%-root-text/landscape stress case still falls short
of its reading-height budget, improving 90→98px; neighbouring controls remain
visible and tappable. This existing stress limitation is retained explicitly,
not relabelled as a pass or used to reduce the 120px normal-size requirement.

Commit `5a02bcf284387540a5f9f1f4d058ed3ed21ec375` passed all 42
[source gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42774944954)
at 07:27:12 UTC. The complete log was read. Request
`9608607ecaf9cc928b430fe5187003d8bfe38022` starts
[copy rehearsal 4](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42775499922)
against that exact source.

## Fourth actual staging39-copy rehearsal

Copy 4 finished **FAIL** at 07:49:40 UTC. Native apply/repeat/rollback/reapply
and all four unchanged data records passed exact verification. Focused Chromium
**1,384/0**, Firefox **1,304/0** and WebKit **1,304/0** passed again. The retained
landscape-height failure is corrected: Kiro layout passed Chromium **170/0**
and Firefox **146/0**. Input focus, disclosure and both title-rail suites passed.
The primary agent viewed the corrected actual landscape research capture and
the WebKit Thai middle-card capture; the reading surface and adjacent topics
remain clearly separated, with the site header clear.

The Firefox title-strip matrix then failed one Privacy/1280 assertion: Online
privacy was expected, but Client privacy was current. That leaf completed
**1,341/1**. The aggregate stopped at **10,429/1**; six downstream leaves and
post-browser recovery were not attempted. The complete operation and all native
and browser logs were read from the artifact. The outer runner log could not be
retrieved because the plugin transport closed and its fallback redirect returned
403; that limitation does not omit the retained operation or browser evidence.

A local Firefox trace reproduced the check's cross-scenario interference using
the actual runtime. Its previous 768px Client jump still had an active arrival
settler when the 1280px scenario began. Programmatic alignment placed Online at
209px, then the previous jump realigned Client about 181ms later, putting Online
at 76px. A trusted wheel event before the independent manual-scroll check cancels
that prior arrival and keeps Online at 209px with the correct title. The checker
now starts manual tracking with genuine browser input and samples the title and
heading positions together. The exact title assertion and runtime remain
unchanged. Three paired Firefox traces reproduced the interference without that
input and retained the correct heading with it. Existing isolated title fixtures
passed Chromium **230/0** and Firefox **230/0**. No timeout or threshold was
relaxed. No staging write has occurred.

Checker commit `5bea750821c3bf0be66bfbd9bd59d87166cfd44e` passed all 42
[source gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42778779038)
at 07:58:57 UTC; the complete log was read. Request
`19722cc7b0c5cbfadd591982c39dc4d75d8c8366` starts
[copy rehearsal 5](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42779198077)
with the same runtime SHA256 `56739ffe…7751`.

## Fifth actual staging39-copy rehearsal

Copy 5 finished **FAIL** at 08:16:33 UTC, **18,551/8** across 17 of 18 required
logical suites. All focused engines, input focus, disclosures, Kiro layout,
title rails and title strips passed. The corrected Firefox title matrix passed
**1,342/0**. Page checks passed **295/0** per engine and native Chromium touch
**451/0**. Clearance completed **3,540/4** per engine. Only the retained Thai
suite and post-browser recovery remained unrun. Pre-browser native recovery
and all four unchanged page-data records passed exact checks. The complete
operation and browser evidence, including all 186 images, were audited.

All eight failures were final-item visibility with doubled root text at 844×320
page top: Therapeutic and Chair, both sides and both engines. The inner scroller
reached End without moving the page, but the final single-line “etc.” was clipped.
This check runs that stress case only on six-section pages, so it does not
establish that Thai and Deep Tissue were unaffected. The failed states retained
booleans but no geometry; the checker now includes rectangles, computed spacing,
scroll dimensions and text-line bounds in failure diagnostics, with assertions
and timeouts unchanged.

The first simplified fixture did not reproduce the failure. Matching the actual
89.90625px enlarged header and the site's `.entry-content li {line-height:1.7}`
did: Chair's 791px content height matched the actual capture, and a 42.8px final
line could not fit in a 39–40px reading window. The summary declares 1.3 spacing,
but the page's direct list-item rule overrides that inheritance. The minimal
correction, explained before building, gives only `fit5` gist list items
`line-height:inherit`. Normal cards, text size, wording, margins, gestures and
the fitter remain unchanged. No staging write has occurred.

The matched local fixture passed all eight endpoint cases and 704 practical
neighbour cases with the actual site list-leading rule included. The primary
agent viewed before/after enlarged research and Thai middle-card captures. The
compact ending is fully visible and the ordinary previous/open/next hierarchy
is retained; these are fixture observations, not a new Safari result.

The changed line spacing exposed a separate local Firefox reading-intent failure
in seven of 200 checks. A trace showed saved interior intent 178px, temporarily
clamped to reported 148px while the integer scroll range ended at 147px. Nine
milliseconds later Firefox reported 147px without input or a geometry change;
the exact raw comparison mistakenly recorded deliberate End intent. The narrow
correction compares both rendered offsets within the current valid scroll range,
keeping the original raw rendered record and exact interior movement detection.
It adds no broad one-pixel tolerance and changes no test threshold.

The corrected candidate passed the original strict local reading matrices:
Chromium **208/0** and Firefox **200/0**, each including 16 genuine zero-range
transitions with the site's list-spacing rule present. The focused Firefox
trace passed all 25 checks. Independent source review found no blocker in the
compact spacing or bounded-position comparison. A fresh complete copy gate is
still required before staging writes.

An additional native-wheel diagnostic confirmed all four ±1px movements are
recorded as the exact input positions across Chromium and Firefox. Its initial
integer-only expectation returned 2/4 because Firefox reports fractional offsets;
that diagnostic remains preserved separately. The corrected measurement compares
actual before/after positions and recorded intent, not hardcoded integer starts.
No existing acceptance test was changed.

Commit `83d58b3f2247eb0c7800a8d852ab78e48a79612d` passed all 42
[source gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42783577387)
at 08:36:49 UTC. The complete log was read. Request
`f9ad98babe34f26e42bff86ef1a0d8caed60c6be` starts
[copy rehearsal 6](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42784150848)
against this candidate.

## Sixth actual staging39-copy rehearsal

Copy 6 finished **PASS** at 09:02:15 UTC: **19,061/0**, all 18 required logical
browser suites. Focused Chromium **1,384/0**, Firefox **1,304/0** and WebKit
**1,304/0** passed. All retained suites passed, including clearance **3,544/0**
per engine, native touch **451/0** and retained Thai **502/0**. This is fresh
evidence for the current candidate, not a reused historical Thai report.

Exact state verification after browser activity passed, followed by exact
original-state rollback and reapply with a new owned snapshot. The four data
records remained at `3e9acc4e…cc9`; only #1380 and WPCode's cache changed. The
copy's snapshot files are rehearsal evidence, not staging recovery commands.
The complete 1,174-line operation, native states and all browser suites were
audited from the artifact; all 186 captures validate. The primary agent viewed
the current Therapeutic landscape card, WebKit Thai middle card and enlarged
Chair research ending. Compact summaries are readable, neighbours remain
distinct and the final research qualification is visible. No staging write has
occurred yet.

## Required verification

Check all four service-page structures, both sides, first/middle/last sections,
short portrait and landscape viewports, and viewport shrink/growth. Verify
actual neighbour geometry and hit targets, internal summary scrolling and
endpoints, focus/bookmark retention, rapid switching, background containment,
the existing rail/index gestures and no-JavaScript fallback. Retain the prior
focus and disclosure regression gates; replace only obsolete expectations that
all summary rows or folded topic counts must remain present.

Before staging writes: source gates, independent review, an actual staging39
copy with rendered wording gates, exact apply/repeat/rollback/reapply and
post-browser recovery. Before any apply or rollback: a fresh DB export with a
verified encrypted off-site copy. Read every complete operation report and
retained evidence; INVALID supplies no verification.

Desktop browser emulation and Linux WebKit cannot establish physical iPhone
Safari behavior. Ryan's address-bar and finger-scrolling check remains the
final acceptance of this proposed simplification. Their earlier contradictory
hamburger result remains unresolved; this layout change is not a claimed fix.

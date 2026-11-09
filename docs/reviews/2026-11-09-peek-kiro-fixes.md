# Peek: Kiro assessment fixes

**Deployed on staging39; public verification pending a hosting challenge.** Ryan requested “Fix the kiro assessment
issues.” Work is on `claude/wizardly-bell-xu0rta`, staging39 only. The baseline is
`160360197c298c255eb18ea8179cb72869c87daf`; the
[returned assessment](../inter-model/2026-11-09-peek-nav-kiro-assessment.md)
contains the independently reproduced Privacy failures and Kiro's other findings.

## Access and scope

GitHub metadata confirmed pull/push/admin access; the latest branch pull was
already current. This environment reports an enforced unrestricted network
policy. A fresh staging39 Thai request returned HTTP202, so direct browser loads
were stopped. The guarded Actions rehearsal supplies a fresh actual staging
copy, with uploads, for rendered testing and visual review.

The exact deployed core was frozen as `peek-nav-shared-v5.php` before edits:
full-file SHA256 `602df3dca522b4f6be7db173202c8f64acec3806cffd00705f46fb27ee4815f6`,
code SHA256 `c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.
The old `core-title-rail` transition now ends at that frozen version; the new
`core-kiro-fixes` transition starts there. The existing rollback snapshot
`peek-template-core-title-rail-before-20261109-173737-cae68f35.json` keeps its
original meaning. Never apply an older rollback against the wrong current core.

## Candidate behavior

- F1: animated disclosure settling uses new pointer presses as cancellation on
  Pointer Events browsers. A handled touch's compatibility mousedown no longer
  cancels it. Genuine new wheel, touch, key and pointer input still cancels.
- F2: the preview consumes its handled Enter before moving focus; close and side
  buttons retain their own activation.
- F3: compact cards give the summary the height left after title/controls, fold
  neighboring boxes first, and widen on short screens. Below 501 px viewport
  height, a reading card can temporarily cover the title strip when necessary,
  while staying below the visible header. This tradeoff was explained before
  implementation. The close button retains its own 40 px area above the text.
- F4: clearance includes the entire FAQ box and moves its interactive rectangle
  clear of the rail; text inside is not padded twice. Full-width containing
  columns are unchanged. Actual control-edge checks pass on both sides.
- F5: actual-text-range Services checks pass on both rail sides at 1280 px in
  the first fresh-copy Chromium run; the captures show clear text and controls.
- J1: single-entry rails remain, with a shorter 120 px maximum rail. Search/404
  identity drops only the document title's trailing site-brand suffix.
- J2: original headings remain the only label source. Title-only closed rows
  wrap, and the open title stays complete. Sliding neighboring strip titles can
  still be partially visible by design; no second label/destination map is added.
- J3–J6: preview edges use a separate deep amber, the strip shows page identity
  before the first section, the strip background is opaque, and side buttons
  use plain Left/Right text. Page identity adds no rail destination.

Approved summaries, qualifications and ordinary jump-row fallbacks are unchanged.
No icon scrolling or broad jump-engine refactor is included. The update edits the
existing core snippet in place: no planned fallback-only snippet-swap interval.

## Verification so far

New disclosure regressions establish fresh Online-open/Client-closed Privacy
state, normal/reduced motion, both sides, native Chromium touch, mouse and full
Enter sequences. An isolated fixture using frozen v5 failed all four touch
landing and all four Enter panel-state cases; the focused event correction
passed that fixture. This establishes test sensitivity, not staging results.

Independent local structural probes of the compact card measured a 142 px
research area at 844×342 and 120 px at 568×320, with the close button ending
about 3.4 px above the summary and corner hit-tests reaching summary text. These
are mock results; actual-copy screenshots and measurements remain required.

The local `check-all.sh` run encountered empty Docker bind mounts: three PHP
harnesses could not open their input files. Its apparent PHP-lint pass inspected
no source and is not accepted as evidence. Non-container gates passed, with
shellcheck/ruff unavailable. Streaming source into isolated containers passed
pinned-PHP lint on 131 files, structured-data 22 checks, header/CSP 29 checks,
Peek installer 594 checks and current-page JSON 35 checks. Full Actions Gates
must also pass before any write.

Actions Gates at `4c5632be6e1acca5dbfba0b844cfdc7d7400501f` passed 31 checks,
with one optional skip and no failures (run `42681652317`).

The first fresh-copy rehearsal `peek-kiro-fixes-rehearse-1`, run `42681652458`,
**failed** and authorizes no deployment. Preflight, apply, idempotence, exact
rollback/reapply fingerprints, rendered wording, page inventory and PHP checks
passed. Disclosure regressions passed Chromium 66/0 and Firefox 41/0. Chromium
layout finished 138/7; later Firefox layout and retained suites did not run.
The seven failures were enlarged-text viewport/card containment and small page
position shifts after resizing. Screenshots confirm that enlarged text can
leave the card below the physical screen; this is being corrected, not waived.

The follow-up uses visible viewport bounds for fixed rails/cards, including
pinch offsets, instead of inflated inner dimensions. It also remeasures the
strip when root text changes. The layout checker now establishes a stable
reading position before input, uses fresh device contexts to prevent inherited
zoom between cases, and checks actual visible card/rail bounds at scale 1. Its
one-pixel touch-ownership limit is unchanged. Enlarged text remains an
approximation, not a claim about OS text scaling.

The second rehearsal `peek-kiro-fixes-rehearse-2`, run
`42684101436`, source `054f9a5035ecf20752608fb1a4e0326d95b29062`.
Its offline Gates run `42684101386` passed 31/0 with one rendered-only skip.
The candidate full file is 102,655 bytes, SHA256
`ce21afe60e2c1bdd6286ddc1f9a1e3d14d999bfa967dc4109e4ef5f78426b2fa`;
stored code is 102,649 bytes, SHA256
`dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1`.
It finished **19:37:34 UTC, FAIL**. Root and a separate reviewer read the report
and complete artifact logs. New suites passed: disclosures Chromium 66/0,
Firefox 41/0; layout Chromium 158/0, Firefox 134/0. Retained title rails passed
Chromium 1605/0 but Firefox had 1595/3, all in the local FAQ fixture at 1280 px:
a preview baseline at y0 was compared with y336. Remaining suites were skipped;
3,599/3 executed is not an aggregate pass. Rollback/reapply, rendered wording,
PHP and published-page inventory passed; only #1380/WPCode cache changed.

Actual enlarged-text screenshots now show the complete card and jump hint;
tablet rail/header are visible and the strict one-pixel preview-motion checks
pass. Landscape research shows roughly five complete lines at the page top
and eight when scrolled. Overflow diagnostics locate the enlarged page width
in the existing availability link (`.ts-corp-band__link`), outside Peek; the
visible viewport remains 390×664 at scale 1 and the card now respects it.
A controlled Firefox trace reproduced all three failures: the old FAQ settle
called align at 4284 ms, moving y0 to334, before the first new preview pointerdown
at 4389 ms. The test had programmatically reset scroll while that earlier jump
still owned its four-second settling window. A genuine Escape before the next
viewport/reset establishes a new interaction; runtime settling stays unchanged.
The same paced trace passes 63/0 with Escape (old reset 60/3), and the complete
local Firefox fixture suite passes 581/0. Assertions and runtime bytes are
unchanged. The complete third fresh-copy rehearsal remains required.

The third full rehearsal `peek-kiro-fixes-rehearse-3`, run
`42686456101`, source `b668c36273c873c6a17ba6a66a092e4724e606b6`.
Actions Gates `42686456668` passed 31/0 with one rendered-only skip.
The corrected local fixtures passed Firefox 581/0 and Chromium 584/0.
It finished **19:56:05 UTC, FAIL / browser INVALID**. Disclosures passed
Chromium 66/0, Firefox 41/0; layout 158/0, 134/0; title rails 1605/0, 1598/0;
title strip 1339/0, 1336/0; page data 207/0, 207/0; clearance 3340/0, 3340/0.
Touch stopped after 53/0; retained Thai regression did not run. The aggregate
13,424 passed assertions and zero failed assertions is incomplete evidence,
not a passing rehearsal. Copy/install/idempotence/rollback, rendered wording,
PHP and inventory checks passed. Root and an independent reviewer read the
report and full leaf logs.

The stop exposed a test prerequisite: it required a rotated short summary to
overflow at 844×320. The wider card now fits all its text, so `touch-action:none`
is correct. The revised check must exercise contained native swipes when all
text fits, then find a bounded smaller viewport with an actual usable scroll
range on the same summary, require native inner scrolling, and return to the
original no-pan state. Runtime bytes and the one-pixel containment tolerance
remain unchanged. A complete fourth fresh-copy rehearsal is still required.

The corrected local touch run reached 204/0 before another obsolete geometry
prerequisite stopped it: Chair at 844×320 with doubled root text now fits its
card controls (outer range 0; inner summary range 467 px). The retained outer
scroll check now first proves a fitted title swipe stays contained, then uses
a bounded smaller viewport with real outer overflow and a hit-tested title
stroke. A local 390×180 probe exposes a 21 px outer range. Its assertion still
requires native card movement with the summary and page unchanged. An
independent run of the unchanged later rapid-switch/page-top subset passed
120/0 on all four pages and both sides. These are local structural-fixture
results only; the complete corrected run and actual-copy gate are still needed.

The complete corrected local native-touch run then passed **395/0 in 154 s**
against a mock whose style/runtime exactly match the current candidate. The
Chair outer-card case moved 0→21 px while its summary stayed at 0 and the page
stayed at y1095. Short-summary resize transitions, return-to-fit containment,
all four pages' rapid switches and page-top fit completed. An independent
review found no relaxed movement thresholds or manufactured scroll ranges.
Syntax and diff checks pass; the next fresh-copy rehearsal remains mandatory.

Fourth rehearsal requested as `peek-kiro-fixes-rehearse-4`, run `42689973671`,
source `e97b4f27cfb206d52e9aec5dacf1e8ad44d9b150`; offline Gates `42689973661`.
Offline Gates passed 31/0 with one rendered-only skip. The rehearsal finished
**20:17:59 UTC, FAIL / browser INVALID**. All earlier numeric suites retained
their run-3 counts, but touch stopped at 205/0: the actual Chair outer card had
zero scroll range in all eight bounded viewport attempts, unlike the local
fixture's wrapping title. All title starting points were hit-testable and the
fitted-title containment assertion passed. Thai regression never ran. The
13,576/0 aggregate across 13 reported suites is incomplete evidence. All
non-browser gates passed; the full report and artifact logs were reviewed.
Of 22 selected captures, 21 match run 3 exactly; the changed mouse-disclosure
capture was reviewed and still lands correctly. Runtime remains unchanged.

In parallel, the previously unreached retained Thai suite is running locally.
Its old source-shape check still requires a `color-mix` translucent strip, which
contradicts the requested opaque-strip correction. The unchanged local run
finished with manifest 172/0 and Thai 480/5: four obsolete opacity expectations
and one hidden-row keyboard setup. The opacity check now measures rendered
alpha, opacity and backdrop/filter state, while preserving the separate solid
rail-panel fallback declaration. A focused phone/tablet light/dark comparison
passed 16 expected results: current alpha255/no blur passes; frozen v5 alpha240
with blur is rejected.

An independent focus trace found row k3 is `display:none` at page top. Focusing
it does nothing, so Enter correctly activates the currently focused card and
jumps to Common reasons. The correction will select a genuinely visible closed
row, prove focus reached its button, then require the newly opened box to have
focus without page/hash movement. This is not the separate Thai touch-focus
caveat. The corrected focused keyboard probe passed 3/0.

The outer-card mismatch was reproduced by adding external overflowing text to
a local fixture: requested 390×180 became visual 844×389.54 at scale 0.4621,
despite doubled root text. The title stayed 40 px and the outer range was zero,
matching the actual-copy stop. Resetting CDP page scale to 1 after each resize
restores a 390×180 visible viewport, a 72.75 px title and a 21 px outer range.
A native title swipe then moves the card 0→21 px with summary/page/hash/index
unchanged. A separate native summary swipe also stays contained. The checker
will verify scale and visible geometry explicitly, retaining required outer
scrolling rather than accepting a zoomed-out case. Runtime remains unchanged.

Read-only staging preflight `peek-kiro-fixes-dry-1`, run `42692714615`, source
`8d5822186e0f4e921d2a3d5baf92cc42e1f0d999`, finished **20:25:25 UTC, PASS**.
Published paths and the guarded baseline state match; no snippet was written.
The complete corrected fresh-copy gate is still required before apply.

The corrected complete local native-touch suite passed **395/0 in 155 s**.
Scale 1, requested visual dimensions and near-zero viewport offsets are now
required in the enlarged outer-card case, which retains its mandatory native
scroll assertion. Syntax/diff checks and independent review pass. These are
test-only corrections; the full/stored candidate hashes remain unchanged.

Fifth rehearsal `peek-kiro-fixes-rehearse-5`, run `42693471206`,
source `5159801ee4f0000d014940f1451a0411ea0c28b4`. Actions Gates `42693471226`
passed 31/0 with one rendered-only skip. It finished **20:47:38 UTC, PASS**:
**14,249/0 across 14 complete browser suites**. Every nested wrapper completed;
there is no INVALID or missing final suite. Copy apply/idempotence/exact rollback,
rendered wording, PHP and published-page inventory passed. Only #1380 and the
WPCode snippet-list cache changed on the throwaway copy.

| Browser suite | Chromium | Firefox |
|---|---:|---:|
| Disclosure input/landing | 66/0 | 41/0 |
| Kiro layout | 158/0 | 134/0 |
| Title rails, actual routes + fixtures | 1605/0 | 1598/0 |
| Title strip | 1339/0 | 1336/0 |
| Page data + fixtures | 207/0 | 207/0 |
| Clearance | 3340/0 | 3340/0 |
| Native touch | 387/0 | — |
| Retained Thai gestures | 491/0 | — |

Actual Chair outer-scroll diagnostics confirm 390×180, scale 1, zero viewport
offset, a 72.75 px title and a 21 px range. The native swipe moved the card
0→21 px while page y704, summary position, hash and selection stayed unchanged.
The current strict viewport guard passed; no coordinate-conversion change is
needed for this verified run. Root reviewed the actual landscape research and
Privacy touch-landing captures again. The independent full leaf audit confirms
zero FAIL/INVALID and five empty stderr logs. All four changed selected captures
(Thai research, Services left at 1280, Privacy keyboard/mouse) and seven key
hash-identical captures were freshly viewed without a new blocker. Twenty-two
selected images and the full 133-capture manifest are retained. Twelve selected
captures were freshly viewed in this run; the other ten exactly match prior
reviewed captures. Image bytes/hashes and the runtime hash were verified.
[Complete capture and log manifest](assets/2026-11-09-peek-kiro-fixes/rehearsal/capture-manifest.json),
[landscape research](assets/2026-11-09-peek-kiro-fixes/rehearsal/kiro-layout-therapeutic-right-landscape-top.jpg),
[Privacy touch landing](assets/2026-11-09-peek-kiro-fixes/rehearsal/peek-disclosure-touch-no-preference-right.jpg).
Transient rail labels may briefly cover body copy; enlarged-root text can wrap
the existing brand, and compact landscape cards intentionally cover part of the
strip. These limits are not claims about physical-device or OS text scaling.
Throwaway-copy snapshots are not staging recovery snapshots.

Independent review of the copy captures found the wider landscape research
card readable, the full Privacy FAQ controls clear of the rail, and full Work
With Me headings readable in both the current strip and stacked title boxes.
Home now accurately says Home over its hero. These are actual-copy observations.

## Actual deployment and recovery

`peek-kiro-fixes-apply-1`, run `42697003089`, source
`8f4a3fb415b73cfe8c81071419c6da428f5b032a`, wrote #1380 at **20:52:52 UTC** and
finished **20:54:46 UTC, PASS**. The update was in place, in WordPress storage,
with no fallback-only interval. All 19 published origin routes passed core/data
scope checks, including the existing service jump rows. Apply-source Gates
`42697003372` passed 31/0 with one rendered-only skip.

- Fresh SQL: `staging39-before-peek-nav-20261109-205236.sql`, 4,519,440 bytes,
  SHA256 prefix `cd6f32c1920a`. Server/runner checksums matched; its encrypted
  4,520,728-byte off-site object was read back identically at 20:52:44.990 UTC,
  before the write at 20:52:52.561 UTC.
- Actual snapshot: `peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json`,
  SHA256 prefix `3d55f4d1ca91`. Server/runner checksums and the encrypted
  226,976-byte off-site object's read-back matched at 20:52:59.673 UTC.
- Restore proof used that fresh SQL: import exit 0, 104 restored and 104 actual
  fingerprints. Only snippet #1380 and `wpcode_snippets` cache differed.
  The informational exit 3 reports those intended differences.

The automatic `post-change-test.sh` subsection was **INVALID, HTTP202** at
20:54:27 UTC. None of its code/rendered/pages/schema/pixel/PHP checks ran.
Overall apply PASS does not certify that subsection. A single later retry is
planned after a pause; independent stored-code and focused public checks remain
pending. The full report and timestamped job log were independently audited.

For recovery, first use `core-kiro-fixes --rollback` with this actual new snapshot
under `$HOME/private-backups/`, after current full-state checks and the script's
fresh verified backup. That restores v5. Only then may the earlier actual
`core-title-rail` snapshot `peek-template-core-title-rail-before-20261109-173737-cae68f35.json`
restore v4. Never run an older rollback against the current core. Before a future
runtime edit, freeze this exact payload as v6 and pin `core-kiro-fixes` to v5→v6;
v6 is not yet frozen. Main and live were not changed.

## Remaining validation limits

Physical-device Safari/Android, OS text scale and real assistive technology
remain unverified. All 22 current approved summary-rail destinations are visible
H2 headings, outside disclosures; the legacy 600 ms closed-FAQ branch therefore
is not exercised by those maps. Sharing the two jump engines remains a separate
maintainability task. The earlier Thai touch-focus-to-BODY caveat and Rail
practice-page Firefox Enter caveat are not claimed fixed by this work.

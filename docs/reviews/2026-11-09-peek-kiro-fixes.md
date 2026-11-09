# Peek: Kiro assessment fixes

**Candidate in progress; not deployed.** Ryan requested “Fix the kiro assessment
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
  columns are unchanged. Actual control-edge checks must verify the result.
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

Independent review of the copy captures found the wider landscape research
card readable, the full Privacy FAQ controls clear of the rail, and full Work
With Me headings readable in both the current strip and stacked title boxes.
Home now accurately says Home over its hero. These are actual-copy observations.

Staging remains unchanged; no new staging backup/apply/snapshot has occurred.
Physical-device Safari/Android, OS text scale and real assistive technology
remain unverified. All 22 current approved summary-rail destinations are visible
H2 headings, outside disclosures; the legacy 600 ms closed-FAQ branch therefore
is not exercised by those maps. Sharing the two jump engines remains a separate
maintainability task. The earlier Thai touch-focus-to-BODY caveat and Rail
practice-page Firefox Enter caveat are not claimed fixed by this work.

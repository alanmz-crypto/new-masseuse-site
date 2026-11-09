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

Independent review of the copy captures found the wider landscape research
card readable, the full Privacy FAQ controls clear of the rail, and full Work
With Me headings readable in both the current strip and stacked title boxes.
Home now accurately says Home over its hero. These are actual-copy observations.

Staging remains unchanged; no new staging backup/apply/snapshot has occurred.
Physical-device Safari/Android, OS text scale and real assistive technology
remain unverified.

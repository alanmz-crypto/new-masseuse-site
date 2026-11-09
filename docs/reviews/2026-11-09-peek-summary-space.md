# Peek: more room to scroll every summary

**Candidate only; not deployed.** This correction is included in the pending
[all-page title-strip update](2026-11-09-peek-title-strip-all-pages.md), on
staging39 only. The deployed starting point remains frozen shared v3 from the
[short-summary containment correction](2026-11-09-peek-short-summary-touch.md).
All four approved page-data files are unchanged.

Ryan reports that the Therapeutic research summary has too little room to place
a finger and that scrolling can feel slow after switching quickly among boxes.
They clarified that the larger usable area should apply to every scrollable
summary. The global sizing/fitting change was explained before implementation.

## Evidence and diagnosis

The [actual deployed Therapeutic research capture](assets/2026-11-09-peek-short-summary-touch/public/clearance-therapeutic-massage-390-right-top-6.jpg)
shows the small scrolling region below five closed topic rows. The source caps
the summary at 9.8em and reduces it to 6.6em and 4.4em before folding distant
rows. Increasing only the initial cap can therefore make the summary smaller:
an overflowing enlarged stack falls immediately to its old 6.6em fit.

Row activation renders and fits synchronously on finger lift; it does not fetch
summary text or wait for a loading timer. The existing touch checker covered
Thai/Chair and waited 180ms after each tap, leaving the reported Therapeutic and
rapid-switch paths untested.

The timing and dimensions below are **local structural-fixture evidence**, not
measurements of the captured full site or the reporting device. At 390×844,
pageY600, all four research summaries increased from **123.3px to 226.5px**.
Four successive native row taps followed immediately by a native swipe worked
both before and after the change with 4× CPU throttling. First inner-scroll
events occurred approximately 47–52ms after pointerdown in the compared runs;
the page stayed still. This does **not** reproduce or establish resolution of
the reported loading delay. No artificial gesture delay or scroll override was
added.

## Changed behavior

Every summary can use up to 18em of height. If the stack does not fit, it first
folds distant closed topics into the existing `+N more above/below` labels,
retaining the neighboring topic controls. Later fits reduce the summary to
14em, 10em, 6.6em, then 4.4em. Existing compact controls and the outer-card
scrolling fallback remain available on very short screens. The final fit keeps
whole-paragraph fallback summaries visible as well as approved gist lists.

Short summaries retain their natural height. The measured pan permissions,
native summary and outer-card scrolling, closed-control gesture ownership,
rail/index behavior, open-box jump, focus handling and resize bookmarks are
unchanged. No new content or controls are introduced.

## Local verification and remaining gates

- Final native Chromium touch suite: **386 passed, 0 failed, 149 seconds**.
  It retains the preceding 266 checks and adds all four pages, both sides,
  successive native row taps with no post-tap pause, and first-stroke scrolling
  sampled while the finger is still down. It verifies a generous scrolling
  area with all topics retained where space permits, and the folded stack's
  neighboring control on tighter page-top layouts.
- Final Chromium clearance suite: **3340 passed, 0 failed, 119 seconds**, with
  16 captures. Exact wording, actual strip/header bounds, keyboard access to
  the last qualification, responsive focus/bookmarks and outer-card reachability
  remain checked. Its former portrait expectation that every closed row must
  always be visible was replaced by exact neighboring-row and `+N` count checks
  when folding occurs; geometry and wording assertions were retained.
- Local Firefox could not launch during this initial run because its Playwright
  executable was absent; it was installed later. This initial result was not a
  Firefox pass. Fresh-copy Chromium/Firefox results are recorded below.

Local logs: `/tmp/peek-summary-touch-final.log` and
`/tmp/peek-summary-clearance-final.log`. Local source probes are in
`/tmp/peek-fast-summary-probe.cjs` and its JSONL output. These fixtures do not
replace visual review and browser tests on a fresh staging39 copy with uploads.
Safari, physical devices and actual assistive technology remain unverified.

## Second candidate: actual-copy evidence and an unfinished native gate

Source `27666b83e66f17a0a3e8d6d935b35fce4d83c762` passed
[Actions Gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42592835449)
at **09:22:29 UTC**: **29 PASS, 1 SKIP, 0 FAIL, 0 INVALID**. Only URL-dependent
rendered verification was skipped. The complete job log was read.

The [second staging39-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42592835511)
finished **09:29:02 UTC, FAIL**, on that same source. Exact rollback/reapply,
rendered wording, page responses and PHP checks passed. The actual copied pages
passed clearance **3340/0** and generic page/rail checks **207/0** in each browser;
title checks passed **1223/0 Chromium** and **1220/0 Firefox**. All 58 captures
were independently reviewed, including the 16 clearance/summary views. The
actual Therapeutic research box at 390×844 has a **226.53px summary rectangle
and 227px client height**, confirming the increased space with real page assets.

Native touch reached **53 passed, 1 failed**, then stopped with an **INVALID**
null-summary read. After resizing a kept short Thai summary to 844×320, the
test's swipe moved the page 30px and closed the card. A later local replay of
the reported geometry confirmed that its start point `(636, 181.3125)` fell
below the clipped card's bottom `179.84375` and hit the page's corporate band;
it reproduced the 30px movement and closure. This used captured inline HTML
with imposed measured boundaries, not a new actual-copy pass. The detailed
Thai and matched quick suites did not run.
Passing geometry and captures cannot substitute for this unfinished native gate
or establish resolution of the reported rapid-switch delay.

The full report was read; the [combined record](2026-11-09-peek-title-strip-all-pages.md#second-candidate-actions-gate-and-actual-copy-rehearsal)
holds the exact archive hash, suite results and continuing diagnosis. No staging
write occurred. The checker-only correction must use the visible summary/card
intersection, require at least 32px of usable height, hit-test its stroke, and
retain the same-DOM/native-progress/page/hash/outer-scroll assertions. No runtime
change is justified by this replay. Another successful fresh-copy gate remains
required.

The combined `core-title-strip` transition updates #1380 in place from frozen
v3 while preserving the four data records. No new deployment snapshot exists.
Use the combined release record for the required fresh-copy, wording, visual,
backup and recovery gates; do not reuse an older snapshot for this payload.

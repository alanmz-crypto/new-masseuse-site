# Kiro assessment returned: Peek navigation on staging39

**Two disclosure defects independently confirmed; fixes pending.** Ryan pasted
Kiro's assessment of commit `60b2b8a05436732a4c5c92d59a9cacee6018d73d`, then asked
Codex to continue. This file records that assessment and Codex's verification on
2026-11-09. The Kiro sections are an attributed condensation of the supplied
report, not a verbatim transcript or additional Codex testing.

This phase changed review records only. No runtime, wording, installer, operation
request or website change was made. No workflow was dispatched. Main and live
remain untouched. The [review brief](2026-11-09-peek-nav-kiro-assessment-handoff.md)
and [deployment record](../reviews/2026-11-09-peek-title-rails.md) remain the
baseline; their passing checks did not cover the failing initial states below.

## Codex verdict and next work

The touch landing and keyboard double-toggle are real navigation failures, not
design preferences. Fix them first with focused event handling and regression
cases that establish the initial disclosure state. Then improve the landscape
research reading area and verify clearance at actual text and control edges.

Keep the title strips and rails on every eligible visitor page, including the
single-entry Search and 404 rails: Ryan explicitly requested rails on pages that
lacked them. Kiro's proposed two-entry eligibility rule changes that scope and is
not adopted. No summary wording or destination/order map needs changing.

| Finding | Kiro's evidence | Codex assessment |
|---|---|---|
| F1: touch jump overshoots a closed Privacy panel | Reproduced; heading about 839 px above viewport | Independently reproduced on actual staging39, including a failing reduced-motion case. Highest priority. |
| F2: Enter toggles the disclosure twice | Reproduced; both Privacy panels end closed | Independently reproduced on actual staging39. Fix alongside F1. |
| F3: Therapeutic research has about two lines in landscape | Reproduced; 55 px summary at 342 px viewport height | Source is consistent with the reported constraint. Codex's new rendered check was blocked before reaching this page. Improve the usable area; a fixed 120 px minimum needs fit validation. |
| F4: rail overlaps full-width FAQ controls | Reproduced on Privacy and About, right side | Source-based clearance gap is credible. New Codex hit testing was blocked; verify both sides and the actual interactive rectangle before choosing the correction. |
| F5: Services text extends 6 px under the rail | Reproduced by Kiro at 1280×800 | Pending independent verification of text glyphs, rather than only block bounds. |

## Codex's new evidence

GitHub access and the feature-branch pull worked; the latest commit remained
`60b2b8a`. Four fresh Privacy browser contexts returned HTTP200. In each, the
served `#ts-peek-nav-runtime` JavaScript exactly matched the local core. The full
PHP file SHA256 was
`602df3dca522b4f6be7db173202c8f64acec3806cffd00705f46fb27ee4815f6`.
This is a runtime-source comparison, not a new inspection of WordPress storage.

All four cases began with **Online privacy open and Client privacy closed**.
They used Chromium, right side, first-time hint already seen, and no page errors.
Phone cases used Playwright's iPhone 13 geometry (390×664 CSS pixels), including
CDP touch events; the mouse control used the same geometry. Keyboard used
1280×800. These are emulated inputs, not physical-device or assistive-technology
tests. Measurements were taken 1.4 seconds after activation.

| Fresh case | Result |
|---|---|
| Touch, normal motion | Scroll y=1347; Client target top **−838.625 px**, strip bottom 120.859 px. Client opened and received focus, but its heading was far above the viewport. |
| Mouse, normal motion | Scroll y=375; target top **133.375 px**, about 12.5 px below the strip. Correct landing. |
| Touch, reduced motion | Scroll y=523; target top **−14.625 px**, still hidden above the strip. Reduced motion does not remove the defect. |
| Keyboard, normal motion | Rail Enter, then card Enter: **both panels ended closed**; focus remained on the Client disclosure button. |

The touch trace records pointerup, a programmatic disclosure click, touchend,
then a touch-generated compatibility mousedown/up/click. In the normal case,
mousedown followed pointerup by about 17 ms. The keyboard trace records card
keydown, a programmatic disclosure click, then trusted keypress/click on the newly
focused disclosure button and keyup. Screenshots visually confirm the wrong
touch landing and the two closed panels.

Evidence, exact probes and hashes are retained in the
[evidence manifest](../reviews/assets/2026-11-09-peek-kiro-assessment/manifest.json):
[raw results](../reviews/assets/2026-11-09-peek-kiro-assessment/results.json),
[touch landing](../reviews/assets/2026-11-09-peek-kiro-assessment/privacy-touch-normal.png)
and [keyboard state](../reviews/assets/2026-11-09-peek-kiro-assessment/privacy-keyboard-normal.png).
Probe scripts retain the execution environment's absolute paths as provenance;
they are not portable project gates.

The next layout probe stopped on its **first request**, Privacy HTTP202. It
produced **zero layout cases** and was not retried. Thus there are no new Codex
Therapeutic-height, About-hit-test or Services-text results in this phase.
Parallel reviewers also hit a usage limit; Codex completed the verification here
without treating their unfinished work as evidence.

## Source findings and proposed corrections — not implemented

Paths below are under `scripts/work-with-me-employer/link-ideas/`, at `60b2b8a`.

- **F1:** `peek-nav.php` `titleNavigation.settle()` (around lines 363–390)
  cancels on a window-capture `mousedown`. The compatibility-mouse guard in
  `swallowNextClick()` (around 794–806) runs at document capture, so cancellation
  happens before that guard. The event trace and mouse control support Kiro's
  explanation; no patched rerun has yet proved a correction. Prefer ignoring
  the handled touch's compatibility event, or relying on `pointerdown` for new
  presses where Pointer Events exist, while retaining needed legacy behavior.
  Test that real new wheel/touch/key/pointer input still stops settling. Avoid
  introducing an indiscriminate 500 ms input blackout.
- **F2:** the card Enter handler (around line 853) moves focus through `jumpTo()`
  without cancelling the handled keydown's default activation. Add
  `preventDefault()` for that handled Enter before focus moves; preserve close
  and side buttons' own activation. Verify panel state, focus, hash and landing
  after the complete keydown/keypress/keyup sequence.
- **Test correction:** Kiro's statement that the FAQ check only uses mouse
  input is incomplete. `check-peek-title-rails.mjs` already has `nativeFAQ()`
  using `.tap()` (around 188–208), called for actual Privacy at 390/768 widths.
  It runs **after** `exercise()` has altered the panels, only closes Client,
  and does not reopen the large preceding Online panel. The context defaults
  to reduced motion. Add fresh-state cases with Online open, Client closed,
  normal/reduced motion, plus keyboard activation. These states matter more
  than adding another count to the existing suite.
- **F3:** `fit5` hides surrounding rows and the side control, but caps `.sum`
  at `4.4em` (around 193–197). This explains the narrow reading surface. Kiro's
  proposed side-button/hint consolidation alone cannot recover that space,
  because the side button is already hidden there. Fit the largest useful
  reading area within the visible header/strip boundary, testing top and
  scrolled states, long titles, enlarged text and the outer-card fallback.
  A hard 120 px minimum may overflow when very little height remains. Preserve
  touch containment and access to jump/close controls.
- **F4/F5:** `clearRail()` (around 586–609) measures text blocks and forms,
  not the full FAQ toggle rectangles. Merely adding padding inside a FAQ box
  may leave its full-width interactive hit area under the rail. Validate with
  `elementFromPoint` across both edges and actual text-range rectangles at
  1280 px. Preserve full-width backgrounds; do not pad their containing column.

**Simplification decision:** unifying `titleNavigation.go()` and the older
summary `jumpTo()` could remove the latter's delayed 600 ms FAQ click and share
settling logic. It also touches all four approved summary rails. Defer that
broader refactor until the focused defects are fixed and its benefit and
regression scope have been explained to Ryan. Likewise, suppressing all card
compatibility events at `touchend` needs its own scrolling regression evidence;
it is not necessary to accept that change to address the two confirmed bugs.
Short display labels, if later wanted, can be metadata on the existing source;
they do not inherently require a second destination/order map.

Before a future runtime edit, freeze the exact current core as v5 and pin the
existing `core-title-rail` transition to that frozen version. **That freeze has
not been done in this review.** Any later staging write still needs the guarded
fresh-copy, offline/rendered/wording, backup and rollback procedure. The current
actual snapshot remains
`peek-template-core-title-rail-before-20261109-173737-cae68f35.json`.

## Kiro's reported coverage and findings

Kiro changed nothing and reported GitHub admin/push access used read-only,
roughly 35 staging page loads at least 5.5 seconds apart, all HTTP200. No
workflow, installer, operation request, website, shared-branch or main write was
reported. Their overall verdict: the design reads well; position, preview and
jump are distinguishable; title-only rails are useful on heading-based pages;
touch ownership held in every valid test.

Coverage was headless Chromium only: iPhone 13 portrait/landscape; 320 px for
Search/404 forms; iPad Mini only for Therapeutic research; 1280×800 only for
Services/Privacy; right side generally, left only for Therapeutic research;
dark mode only one Work With Me screenshot. Root `font-size:150%` approximated
enlarged text but was not OS text scale. Their 200% attempt was invalid because
the emulated page zoomed out.

They surveyed all 21 routes closed (19 published, including the four practice
pages, plus Search/404). Open-card interaction and hit testing covered
Therapeutic, Services, Corporate, Work With Me, Privacy, Personal Automation
Terms, Search, Accessibility, Home, About and Contact, with some pages receiving
hit testing only. Not tested: Firefox/WebKit, real devices/AT, Deep Tissue/Chair
summary interactions, Progress E or Rail/Index practice interactions, transient
label overlap, or other tablet/left-side combinations.

Kiro did not fully read `peek-nav.php`, the installer, push wrapper, installer
tests or page-data JSON. They skimmed the README and deployment review before
screenshots and did not read the earlier Kiro addendum. Their design judgment
was therefore only partly independent, and the report is not a complete safety
or source audit.

Additional measurements and design judgments from Kiro:

- F3: at landscape height 342, research card 132 px, summary 55 px with 331 px
  of text; one swipe moved 26 px. Portrait and tested tablet gave 227 px;
  150% root text in portrait gave 97 px. Proposed widening/120 px minimum would
  cover more page content while previewing.
- F4: Privacy right-edge toggle overlap 15×16 px; About 11×16 px at sampled
  scroll positions. No obstruction found on Services, Corporate, Work With Me,
  Contact, Accessibility or Home. Left side was not tested.
- F5: Services at 1280×800 reportedly ended text at x=1240 against rail x=1234;
  larger screens centered content clear of the rail.
- J1: one-entry Search/404 rails feel disproportionate; their cards say
  “Section 1 of 1” and retain the document-title suffix. Kiro suggested removing
  the rail. Codex retains Ryan's all-page scope; proportionality/label polish
  may be discussed separately.
- J2: Work With Me sentence headings truncate in closed rows and the moving
  strip clips words at its edges. Accept or supply short display labels; no
  wording decision was made here.
- J3: current fan item and open preview both use a green edge for different
  meanings. Source-based judgment; Kiro did not capture the fan.
- J4: Home's strip names “Learn about Thai Solitude” at scroll zero even though
  that target is about 1,644 px below the hero; potentially misleading.
- J5: page text is faintly visible through the translucent strip.
- J6: the side-switch “⇆” appeared as a missing-glyph box in their Chromium;
  likely font-dependent. Plain text or an icon is optional polish pending
  device evidence.

Withdrawn or unreproduced findings matter too:

- Closed-row page movement was a probe error: the attempted row had folded and
  the script swiped at (0,0). Valid closed row, “+4 more above” and title swipes
  moved the page zero pixels.
- Rapid-switch delay was not reproduced: 14 taps in about 1.4 seconds, repeated
  at 6× CPU throttling, gave roughly 50–80 ms render response and no long tasks.
  Eighteen card rebuilds did not grow document listeners. Summary scroll began
  about 55–68 ms after touch across immediate, delayed and reopened cases.
  This does not rule out slowness on actual phones.
- The Search/404 form initially looked close to the rail, but actual input
  edges cleared it at 390 and 320 px; that collision claim was withdrawn.

Kiro also found heading jumps about 12 px below the strip on the pages tested,
working ID-less destination buttons and original hashes, no page errors, neat
desktop cards and good contrast in the sampled dark card. Preserve those
behaviors and the successful touch containment while correcting the defects.

## Remaining validation

Fresh-state native touch and keyboard disclosure regressions come first, then
research reading height and complete control/text hit clearance. Actual Safari,
physical devices, OS text scaling and VoiceOver/TalkBack remain open. Source
inspection and simulated clicks do not close those gaps. Existing historical
passing counts remain historical results, not proof that these defects are fixed.

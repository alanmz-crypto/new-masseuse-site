# Peek: more room to scroll every summary

**Historical v4 sizing record.** The later [title-only rails update](2026-11-09-peek-title-rails.md)
is now deployed on staging39 and preserves this summary behavior. Restore that
update to v4 with its own actual snapshot before using the older recovery route.

**Deployed staging39; public browser check blocked by HTTP202.** This correction is included in the
[all-page title-strip update](2026-11-09-peek-title-strip-all-pages.md), on
staging39 only. Its starting point was frozen shared v3 from the
[short-summary containment correction](2026-11-09-peek-short-summary-touch.md).
All four approved page-data files are unchanged.

Ryan reports that the Therapeutic research summary has too little room to place
a finger and that scrolling can feel slow after switching quickly among boxes.
They clarified that the larger usable area should apply to every scrollable
summary. The global sizing/fitting change was explained before implementation.

## Evidence and diagnosis

The [pre-change Therapeutic research capture](assets/2026-11-09-peek-short-summary-touch/public/clearance-therapeutic-massage-390-right-top-6.jpg)
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
were retained; independent review covered the two changed search views and all
16 new clearance/summary views, after the first candidate's 42-view review. The
actual Therapeutic research box at 390×844 has a **226.53px summary rectangle
and 227px client height**, confirming the increased space with real page assets.

Native touch reached **53 passed, 1 failed**, then stopped with an **INVALID**
null-summary read. After resizing a kept short Thai summary to 844×320, the
test's swipe moved the page 30px and closed the card. A later local replay of
the reported geometry confirmed that its start point `(636, 181.3125)` fell
below the clipped card's bottom `179.84375` and hit the page's corporate band;
it reproduced the 30px movement and closure. This used captured inline HTML
with imposed measured boundaries, not a new actual-copy pass. The detailed
Thai suite did not run. The matched quick subset is public-only, not part of
the rehearsal wrapper.
Passing geometry and captures cannot substitute for this unfinished native gate
or establish resolution of the reported rapid-switch delay.

The full report was read; the [combined record](2026-11-09-peek-title-strip-all-pages.md#second-candidate-actions-gate-and-actual-copy-rehearsal)
holds the exact archive hash, suite results and continuing diagnosis. No staging
write occurred. The checker-only correction must use the visible summary/card
intersection, require at least 32px of usable height, hit-test its stroke, and
retain the same-DOM/native-progress/page/hash/outer-scroll assertions. No runtime
change is justified by this replay. Another successful fresh-copy gate remains
required.

## Third rehearsal: native touch passes; landscape stress case remains

The [third actual-copy run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42595399779),
source `03748a861bb4249ca3adce282346fb0be51e1cd9`, finished
**09:54:42 UTC, FAIL**. Its complete report was read. The runtime was unchanged;
the visible-surface checker correction now completes native Chromium touch at
**378/0 in 163s**. Eight conditional folded-row checks needed in the local
386-check layout were not needed on these copied pages; no assertion was waived.
Title checks again passed **1223/0 Chromium**, **1220/0 Firefox**; generic page
checks **207/0** and clearance **3340/0** passed in each engine. Exact rollback,
rendered wording, PHP and page-response gates passed, with copy changes limited
to #1380/WPCode cache.

The retained Thai regression returned **484/1**: its synthetic seven-section,
enlarged-text landscape case failed the combined name/jump-hint visibility
check for section seven. Which element failed and why were then under investigation;
no runtime correction or exception was assumed. The retained wrapper reported
**INVALID** for its missing final summary.
The combined rehearsal remains **FAIL** despite the passing native-touch suite.

The [read-only preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42595445291)
passed at **09:55:20 UTC**, source `4517794fca7c409619eb901f5bcd55f15893269b`:
the 19 published paths and exact current core match the guarded transition.
Its complete report confirms no WordPress write. The
[combined record](2026-11-09-peek-title-strip-all-pages.md#third-candidate-offline-pass-rehearsal-failure-read-only-preflight-pass)
holds the archive identity and full outcome. Staging deployment remained
blocked at that stage by the failed rehearsal; this is not a resolution claim for the
reported device delay.

## Fourth rehearsal: complete pass before deployment

Source `28b440122d13bb7496dea185ba7a1ad0a586aab3` passed
[Actions Gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42598380439)
at **10:06:55 UTC**, with **29 PASS, 1 SKIP, 0 FAIL, 0 INVALID**; the only
skip was URL-dependent rendered verification. The
[fourth actual-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42598380371)
then finished **10:20:13 UTC, PASS**, after **710s** of browser checks.
The complete report was read. Title checks passed **1223/0 Chromium** and
**1220/0 Firefox**, generic pages **207/0** and clearance **3340/0** in each,
native Chromium touch **378/0**, and retained Thai regression **486/0**.
The exact stress case now proves native End/Home/swipe reachability and hint
activation; the unchanged keyboard-focus assertion also passes on the copy.
No runtime change was required for these checker corrections.

Exact rollback/reapply, rendered wording, PHP and all 19 published-page inventory
checks passed. Only #1380/WPCode cache changed in the throwaway copy. The
[combined record](2026-11-09-peek-title-strip-all-pages.md#fourth-rehearsal-complete-pass-before-staging-apply)
holds the verified artifact identity and full evidence. Its 58 retained captures
were compared against earlier evidence: 40 match the second copy byte for byte,
11 differ only on the rail, one differs at a focus-outline corner and six changed
title views were inspected. Changed desktop title views were also checked against
the fully reviewed first-copy set; root directly viewed Therapeutic, Contact and
Home. No visual blocker remained. The earlier read-only preflight matched this
unchanged payload.
The 342-check quick subset is for post-deployment public verification and was
never part of the rehearsal wrapper. No staging write occurred during rehearsal.

## Deployment and remaining verification

The combined `core-title-strip` transition wrote #1380 at **10:24:52 UTC**;
[apply run 42600617720](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42600617720)
finished **10:28:23 UTC, PASS**. The deployed 86,567-byte code has SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`.
It preserves all four data records and had no fallback-only interval. The fresh
export and actual snapshot
`peek-template-core-title-strip-before-20261109-102452-90630a29.json`
were verified encrypted off-site. All 19 origin routes passed scope checks;
wording/PHP checks and restore import passed, with only #1380/cache differences.
The broad 20-image comparison against live had 20 differences, not a pixel pass.

The final public browser run **42601203134** finished **10:30:14 UTC, INVALID
HTTP202**: SiteGround's challenge prevented all assertions, routes and captures;
the quick subset was skipped and no immediate retry was queued. Public browser
verification remains pending access. Stored inspection **42601221631** passed
at **10:30:40 UTC**, confirming the exact deployed core and all four unchanged
data hashes. The [combined deployment/recovery record](2026-11-09-peek-title-strip-all-pages.md#staging-deployment-and-final-verification)
contains the complete reports and actual backup identities.

For recovery, use explicit
`core-title-strip --rollback $HOME/private-backups/peek-template-core-title-strip-before-20261109-102452-90630a29.json`
arguments to restore v3 before any older transition, after a
fresh verified backup and full-state checks. The [title-rail follow-up](2026-11-09-peek-title-rails.md)
has frozen this payload as v4 and pinned this transition; reverse any later
applied title-rail transition to v4 before this recovery. The reported loading delay
has not been reproduced or declared resolved; Safari, physical-device and
assistive-technology validation remain open.

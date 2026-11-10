# Peek index clearance on short screens

**In progress; not deployed.** Ryan authorized resolving the small-screen
index-icon overlap on staging39. The accepted summary-neighbour layout is the
starting point. Work remains on `claude/wizardly-bell-xu0rta`; main and live are
outside this task.

## Evidence and design

Fresh GitHub checks confirmed repository read/write/admin access; the requested
branch was current at `8efe72469`. The attached environment reports an enforced
unrestricted network policy. Its inner process could not reach the inherited
proxy; the permitted outer request and Chromium page load both reached staging39
with HTTP200. Actual Thai assets were captured once for offline browser replay.
No JavaScript errors were observed in that baseline.

Direct Chromium, dark theme, left side, Thai at scroll zero:

| Viewport | Visible title strip bottom | Index top–bottom | Observation |
|---|---:|---:|---|
| 390×500 | 202.22px | 98–142px | Over announcement |
| 844×342 | 223.70px | 50.59–94.59px | Under masthead |

The source positioned the rail from sticky-header height, rather than the
actual visible header, notice and strip. At short heights the masthead is not
sticky, but still occupies the top of the page. Positioning code matches v7;
this task did not render v7 to establish the historical origin of the issue.

The policy was explained before implementation. Place the complete 44px index
below the visible header, booking notice and title strip, with 12px clearance
and an 8px gap to the rail. Preserve rail targets at least 22px high. Reclaim
unused lower space first, retaining the bottom safe area. If necessary, reduce
empty rail gaps from 5px to 2px and end padding from 8px to 4px. If the controls
still cannot fit, defer only the idle rail until scrolling or resizing creates
enough room. The title strip and normal page scrolling remain available.

The existing no-JavaScript jump row remains intact. It is not dynamically
revealed when the JavaScript rail waits: that would shift page content and
change the very space being measured. No destinations or approved summaries
are removed. Active pointer gestures, focused navigation and an intentionally
kept-open index retain their controls until release, blur or close.

An open summary keeps its DOM, selection and reading position independently.
When a keyboard user dismisses a card after its rail was deferred, focus returns
to the existing main reading region without moving the page; an available rail
still receives normal return focus. The visual, aria-hidden title strip does
not become an interactive control. The first-time hint waits for a visible
rail and does not mark itself seen just because the rail was deferred.

## Exact baseline and guarded deployment

Accepted neighbours core frozen as `peek-nav-shared-v8.php`:

- File: 104522 bytes, SHA256
  `0459b427a2169659ab82b0c1ab9a2e1f839ef6bd1d6b8c5770754bb1c4ba7e27`.
- Stored code: 104516 bytes, SHA256
  `bff5d9c7e210b5948f31cd8c07daf1390c7f66b00f3915b8d7fb033acf7fe2e5`.
- Last verified complete deployed core state:
  `c9046dbb963ed0d18297b4b1a4cfda0b94db4969767ee2ddf6f75498c66ca289`.
- Last verified four-page data state:
  `3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`.

The new `core-index-clearance` transition is v8→current and updates #1380 in
place. No fallback-only interval is intended. Its own actual before-snapshot
will be required for verification and recovery. The existing neighbours
transition remains v7→frozen v8, preserving the meaning of
`peek-template-core-neighbours-before-20261110-143200-81725f33.json`.
Never use that older snapshot directly against the new core.

Before a staging write: offline gates, a fresh staging39-sourced copy with
uploads, complete rendered gates and exact apply/repeat/rollback/reapply,
including recovery after browser activity. Then dry run, fresh verified
encrypted off-site DB backup, in-place apply and off-site actual-snapshot
verification. Public browser checks and exact rendered/state verification
follow the write. INVALID evidence proves nothing.

## Validation in progress

Actions [Gates 42922709343](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42922709343)
passed at 23:26:49 UTC for `91604ad6b`: all 45 offline gates, including actual
pinned PHP, shellcheck, ruff, installer recovery and the new browser-evidence
harness. The complete job log was read. Rendered checks correctly remain for
the separate staging-copy rehearsal.

The retained Kiro and neighbour suites now recognize intentionally deferred
idle controls. They reach a visible rail with real page input (wheel in
Chromium/Firefox, keyboard PageDown in mobile WebKit), open the preview, then
return that same preview to the original position for every existing summary
assertion and capture. WebKit uses the suite's original programmatic position
setup for the return because Playwright rejects wheel input in mobile WebKit.
This does not count as physical touch validation. Thai-only retained replay
passed 346 assertions but correctly reported incomplete (2/8 cases); it is a
diagnostic, not a complete retained gate.

Local full `check-all.sh`: 37 passed, five failed from Docker daemon bind paths
being unavailable, three skipped (shellcheck and ruff absent; no rendered URL).
The same PHP sources passed supplemental network-disabled container executions
using streamed files: 144 syntax checks; structured data 22; headers 29; Peek
installer/emission 1423, current-page JSON 35, Rail installer 149; visitor
installer 1666, including 138 injected failures. New deployment/rehearsal
guards passed 190/100; browser-evidence harness passed 109. These local checks
do not replace full Actions gates or actual WordPress/browser recovery.

An independent source review caught and resolved deferred-rail focus return,
hint timing, capture-loss redraw and fan release outside its names. It found no
remaining source-level blocker; rendered checks are recorded separately.

Root visually reviewed actual-asset offline replay captures of the candidate:
[390×500 portrait](assets/2026-11-10-peek-index-clearance/local-replay/peek-index-clearance-chromium-thai-massage-left-short-portrait.jpg)
and [844×342 landscape top](assets/2026-11-10-peek-index-clearance/local-replay/peek-index-clearance-chromium-thai-massage-left-landscape-top.jpg).
The index clears the notice and strip in portrait; landscape defers the idle
rail. These are captured staging HTML/assets with the local candidate injected,
not evidence of a deployed update. Final Thai replay passed **242/0**, both
sides, including focus return, first-time hint and cancellation checks, with
two offline page loads and four captures. No further site requests were made.

Candidate file: 109082 bytes, SHA256
`905a7b528cb4723a0f5d0f637e354ba8cd6c29282b48f8c3f617974a5e63c11e`;
stored code: 109076 bytes, SHA256
`736be774023fec6ab0ecdbeab4a3b215c1a40c38567989325644417371889089`.

The focused matrix measures actual visible rectangles independently of runtime
CSS variables. Eight routes, both sides, short portrait and landscape captures:
Thai, Therapeutic, Deep Tissue, Chair, Services, Work With Me, Privacy and
single-entry Search. It checks full index hit areas, target size, deferral and
return, keyboard and trusted Chromium touch gestures, retained open summaries,
menu access and ownership during resizing. The accepted neighbour and earlier
regressions run once before the new matrix. Rehearsal uses Chromium, Firefox
and WebKit; public verification uses Chromium and Firefox.

Browser viewport resizing is not physical Safari address-bar testing. Ryan's
November 10 “Looks good on Safari. No problems I see” accepts the preceding
neighbours deployment. After this separate index change, only the affected
index position and gesture check remains for iPhone Safari; no new broad
acceptance or assistive-technology certification is inferred.

Actual operation IDs, results, new backup/snapshot and deployed hashes will be
recorded here after those operations complete. Live release preparation remains
separate and paused; no live-copy or deployment authorization is inferred.

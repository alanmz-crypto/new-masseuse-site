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
unused lower space first, retaining the bottom safe area and 8px above the
visible fixed contact bars on Work With Me and Corporate. If necessary, reduce
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

After the retained-test setup update, source commit `38d3899db` also passed all
45 gates at 23:33:53 UTC in
[Actions 42923769979](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42923769979).
The complete job log was independently read; only the intended no-URL rendered
checks were skipped. Runtime and approved data bytes did not change.

The retained Kiro and neighbour suites now recognize intentionally deferred
idle controls. They reach a visible rail with real page input (wheel in
Chromium/Firefox, keyboard PageDown in mobile WebKit), open the preview, then
return that same preview to the original position for every existing summary
assertion and capture. WebKit uses the suite's original programmatic position
setup for the return because Playwright rejects wheel input in mobile WebKit.
This does not count as physical touch validation. Thai-only retained replay
passed 346 assertions but correctly reported incomplete (2/8 cases); it is a
diagnostic, not a complete retained gate.

The exact new setup helpers were then exercised directly against the Thai HAR
at 844×342, both sides, both suites. All four known-waiting branches ran:
trusted wheel moved the page from y=0 to y=342, native touch opened the chosen
preview without a jump, then wheel returned y=0 with the exact same card,
summary and immediate neighbours. 24 independent checks and four helper
checks passed. Mobile WebKit was not locally cached; its actual engine run
remains part of the copy rehearsal.

The cached Firefox engine also passed the unchanged focused Thai assertions:
204/0, two sides, two offline page loads and four captures. This is 2/16 cases
only, not a complete focused engine gate. It used the same captured assets and
candidate bytes without another website request or installation.

Fresh-copy request `peek-index-clearance-rehearse-1`, commit `b8afa20d3`,
[Actions 42923801883](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42923801883),
finished **FAIL / browser evidence INVALID** at 23:58:44 UTC. Source staging39
with uploads; no remote-site mutation. The full browser artifact was downloaded
and checked against GitHub's size (63974633 bytes) and SHA256
`6a8bb3c2c58fcf39c53ef99e29a593439fb097e6cb44ac065eb88c4e37bd38a4`.

The retained 18 leaves reported 19073/0. Focused Chromium completed all 16 cases
and 32 captures with 1206 literal passes and no failures. The new parser counted
1174: it rejected the two terminal assertions per case, whose current checker
labels separate the case and description with `: ` rather than `/`. This is a
reporting-contract defect. The stop guard correctly prevented focused Firefox,
focused WebKit and post-browser recovery. The overall rehearsal is not verified
and cannot authorize a staging write; corrected parsing and a fresh complete
rehearsal are required. No runtime or approved-data change is needed for this
formatting correction.

The parser now accepts exactly `/description` or `: description` after a known
route/side, using the same canonical duplicate key for either form. The offline
harness now includes the actual terminal-label texts and malicious/malformed
variants: **131 cases passed**. Replaying the byte-for-byte captured Chromium
stdout/stderr through the corrected gate accepted all **1206 passes, 16 cases
and 32 captures**; other phases were test doubles, not new browser evidence.
That parser replay used the copy1 runtime SHA256
`905a7b528cb4723a0f5d0f637e354ba8cd6c29282b48f8c3f617974a5e63c11e`.

Independent native-log review confirmed staging39 as source, all 19 reviewed
published pages, and an isolated localhost home/site URL. Apply/repeat verified
against snapshot suffix `0fdb726a`; the first rollback restored the complete
baseline byte-for-byte, including the WPCode cache. Reapply used its own
snapshot suffix `afbbe2eb`, verified before browser activity. Only #1380 and its
expected cache changed; page data stayed unchanged. Home checks passed 35/0,
client wording/structure 322/0, and no new PHP errors were found. This does not
replace the missing post-browser recovery proof.

The screenshot audit found that Work With Me's existing fixed contact bar also
needs its lower-screen space reserved. The prior geometry oracle measured only
the upper chrome. The next candidate measures that visible fixed bar and keeps
the rail at least 8px above it, using the same defer/return policy when necessary.
This was explained before implementation. The focused checker will independently
measure the bar and hit-test its original buttons, including 320×420 on both
sides. Source review identified Corporate's matching bar; one fresh HTTP200 load
of each published page confirmed both fixed bars at 320×420, top 358.59px and
height 61.41px. Both are included in the same clearance rule. This guarantees
idle-navigation clearance; the stated active-interaction exceptions remain.
No contact content, approved summary or frozen baseline is changed.

Exact captured-asset replay reproduced copy1's bottom-button interception on
both pages and sides at 320×420: Work With Me overlap 35.06×26.86px, Corporate
35.06×24.86px. The hit belonged to a Peek button. The revised candidate defers
the idle rail at that position and restores the original contact-button hits.
Root reviewed both corrected narrow-screen captures. These are offline candidate
renders of fresh captured staging HTML/assets, not deployed-site evidence.
The expanded evidence harness passed **133 cases**, including rejection of the
obsolete eight-route matrix; no browser/network/installer was used by the harness.
The exact focused checker then passed **348/0** for the two captured pages and
both sides: 4/18 cases, four offline loads and eight captures. It preserved all
assertions and used no additional website requests. This remains partial local
evidence, not the full fresh-copy gate.

Source candidate `c66784bca` passed all **45 offline Gates** in
[Actions 42929745059](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42929745059)
at 2026-11-11 00:18:10 UTC. The complete job log was independently read;
rendered checks were correctly skipped without a URL. Request
`peek-index-clearance-rehearse-2` at `a8801366d` starts the complete nine-route
rehearsal from another staging39 copy with uploads. No staging write has occurred.

Copy2 [Actions 42929855631](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42929855631)
finished **PASS at 00:47:24 UTC, November 11**. All 21 unique browser leaves
passed **23,249/0**: retained 19,073; focused Chromium 1,428, Firefox 1,374,
WebKit 1,374. Each focused engine completed all 18 cases and 36 captures.
The complete 70,623,793-byte artifact was verified against SHA256
`1b3e657289e8d413989d68009d37db2b1933ce96c2f35fa541d76bb2ad15d11b`.
Independent raw-log review recomputed 636 geometry records, including 84 fixed
contact-bar cases, with no mismatches; all 108 focused JPEGs decoded correctly.
No browser failures or INVALID evidence occurred.

The copy verified the applied state after browser activity, rolled back to the
exact original core/four data state, then reapplied and verified its own fresh
snapshot. Copy-only snapshots end `001742-6c882f4b`, `001746-70bbceb1` and
`004721-44333767`; these are not staging recovery paths. The restored original
core state was `c9046dbb963ed0d18297b4b1a4cfda0b94db4969767ee2ddf6f75498c66ca289`;
four-page data stayed `3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`.
Only #1380 and the expected WPCode cache changed. Existing WP-CLI color-library
deprecation notices are not new website PHP errors.

Root reviewed the actual copy2 WebKit
[Thai short-portrait view](assets/2026-11-10-peek-index-clearance/copy2/peek-index-clearance-webkit-thai-massage-left-short-portrait.jpg)
and [Work With Me narrow view](assets/2026-11-10-peek-index-clearance/copy2/peek-index-clearance-webkit-work-with-me-right-short-portrait.jpg):
the complete index clears the announcement/title strip where space permits,
and the deferred narrow rail leaves both contact buttons unobstructed.

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

Candidate file: 109772 bytes, SHA256
`5c9a447583f22458b619dd5e1a2e75dcc8049739d99bdfafb1d0fedac3f2bd2e`;
stored code: 109766 bytes, SHA256
`955458c81bf136a2789d8f8d495a3e4e8660771014f65db601441a0af6b1b937`.

The focused matrix measures actual visible rectangles independently of runtime
CSS variables. Nine routes, both sides, short portrait and landscape captures:
Thai, Therapeutic, Deep Tissue, Chair, Services, Corporate, Work With Me, Privacy and
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

Separate visual observation from copy1: Search's dark-mode heading/breadcrumb
appear pale on a white hero panel. No baseline comparison established attribution,
and no theme or Search-content change was made in this index-clearance phase.

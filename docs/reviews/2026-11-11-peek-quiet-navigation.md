# Peek reading labels and menu buttons

**Deployed to staging39; dedicated public-browser verification is pending.** Ryan
requested four changes before live: remove the automatic section label beside
the rail while reading; outline the index button; close the header hamburger
menu on an outside tap; and show a tappable X on the open index. Names shown by
rail/index interaction and the existing index outside-touch dismissal remain.
Work is on `claude/wizardly-bell-xu0rta`, staging39 only.

## Access and observed baseline

The branch was pulled at `9572a64cf`. GitHub reports admin and push access. The
attached environment reports current, enforced unrestricted networking. One
fresh plain request to staging39 returned SiteGround HTTP202, not a proxy
denial. No repeated public request followed. Fresh rendered evidence will come
from the established staging39-sourced Actions copy with uploads.

Before editing, the accepted core was replayed against captured actual Thai and
Work With Me HTML/Astra assets, with unmatched requests aborted. At 390×844:

- Trusted page scrolling showed the floating label at opacity 0.92, over page
  content. It faded after idle. The strip already supplied reading position.
- The index was 44×44px, transparent with no border. Native touch and mouse
  second taps left its names open. Outside touch closed them without scrolling.
- A header-menu outside tap left it open. Its own toggle closed it. Tapping
  the theme button changed theme while leaving the menu open.
- A keyboard-opened preview and header menu could coexist. Outside touch
  closed the preview, consumed that click, and left the menu open.

This is offline Chromium evidence, not a fresh public run or physical Safari.
One lazy content image was absent from the Thai HAR. Its recorded SiteGround
click hook prevented ordinary cross-page navigation proof. Actual-copy browser
checks must not inherit that limitation as a claimed pass.

## Implementation and design

The automatic label's DOM, CSS and timer are removed. The title strip, progress
mark, fan names and preview cards retain their roles. The index gains a rounded
outline and solid theme surface, keeps its full 44×44px target in both states, and switches its existing
SVG from lines to an X. Its accessible label becomes “Close section index”
while open. A second tap closes it; slide-to-name, slide-to-rail, two-second
hold and keyboard paths remain covered by regression checks.

The header continues to use Astra's own toggle and state. Only the dropdown
panel and its toggle count as inside the menu; the theme button, logo and page
content count as outside. The final outside click closes the menu through its
native toggle before a page-link jump measures the header. The click already
has its target, so this does not collapse the header during pointerdown. Only
that exact owned, untrusted toggle event bypasses Peek's one-click guard; a tap
reserved for dismissing a preview remains consumed.

Peek's committed jumps also call that same close function before measuring
their destination. This covers preview activation on pointerup or keydown,
which happens before the ordinary click listener. Opening a preview or scrolling
its summary does not close the menu through this path.

Offline rendered checks caught an error in the first candidate's deferred
close: with normal smooth scrolling, Corporate's original `#booking-options`
link landed at −192.97px instead of 143.03px, exactly the 336px menu height
above the desired position. Reduced motion passed through scroll anchoring.
A Thai browser-only anchor probe reproduced the same normal-motion error. The
click-order correction above addresses that measured cause; all subsequent
evidence must use the corrected bytes. The initial diagnostic is not a pass.

These changes were explained before building. No summaries, destinations or
page content are changed. The plain jump-row fallback remains intact.

## Exact baseline and staging route

Accepted index-clearance core is frozen as `peek-nav-shared-v9.php`:

- File: 109772 bytes, SHA256
  `5c9a447583f22458b619dd5e1a2e75dcc8049739d99bdfafb1d0fedac3f2bd2e`.
- Stored code: 109766 bytes, SHA256
  `955458c81bf136a2789d8f8d495a3e4e8660771014f65db601441a0af6b1b937`.
- Last verified complete core state:
  `de8e43f75a8a42c537608c8590e160ba6f061fc27d32c4c83631ae0451b3c7e6`.
- Last verified four-page data state:
  `3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`.

The historical index-clearance route stays v8→frozen v9, preserving its actual
snapshot `peek-template-core-index-clearance-before-20261111-005357-31ca0040.json`.
The new `core-quiet-nav` route is v9→current and updates #1380 in place, without
a fallback-only interval. It requires its own actual before-snapshot.

Before a staging write: source gates, a fresh staging39 copy with uploads,
complete browser gates, exact apply/repeat/rollback/reapply and recovery again
after browser activity. Then dry run, fresh verified encrypted off-site DB
backup, apply and off-site snapshot verification. Public checks and exact
actual-snapshot verification follow. INVALID evidence verifies nothing.

The browser graph retains the accepted index-clearance suites once, then adds
focused checks on nine routes and both sides: Chromium/Firefox/WebKit on the
copy (24 unique leaves), Chromium/Firefox publicly (20 leaves). Each focused
engine must provide all 18 cases and 36 captures, with complete raw logs.
Retained Thai checks select exact old/new expectations using the explicit
`quiet-v1` runtime marker; an unknown marker fails. No either-behavior pass is
allowed. Physical Safari and assistive technology are separate from emulation.

## Evidence

First corrected runtime (copy1 candidate): 110300 file bytes, SHA256
`35ec4879dbe5f9df78f07b57003384eefc4f5592bd8484b9b2676e84e5aacfff`;
110294 stored bytes, SHA256
`aa2000ebf026dfb2c1ec4a91ba0f73caa7c7e10ab5cc3eb1a640da72a3752b79`.
Independent source review found no further issue with the exact owned-toggle
exception, X path or automatic-label removal.

Corrected offline Chromium replay passed **46/0** for Thai and Work With Me on
both sides, plus Corporate's original anchor with reduced motion. The previously
failing Corporate normal-motion anchor then passed **2/0**. This covers actual
section progress, menu outside dismissal, the theme action, native toggle,
inside swipe and the preview-owned outside-click guard. Four corrected reading
captures and eight button/open-index/small-screen views were visually reviewed.
The initial after-swipe test tapped during inertia and produced no click; the
ordinary-tap test now waits for scrolling to settle. No new site requests were
made for these diagnostics.

The final focused checker also passed **76/0** on Thai's two offline sides,
including hold/name-slide/rail-slide starting with the X open, normal and
reduced-motion anchor landing, theme action and an actionable outside-tap probe
that remains consumed when Peek owns it. Its **2/18 cases and four captures are
deliberately INVALID as a full gate**. A separate diagnostic found Chromium
adjusting a nominally blank tap onto a nearby Corporate link on pointerdown,
before dismissal; the checker now requires a clear noninteractive tap area and
verifies the trusted input target. No runtime workaround was added for that
test setup error.

Local `check-all.sh` completed with **40 passes, five failures and three skips**.
All five failures were inaccessible Docker bind mounts; shellcheck and ruff were
unavailable, and rendered verifiers correctly skipped without a URL. Separate
streamed-container checks passed PHP lint (146 files), structured data (22),
headers (29), installer (1705), current-page JSON (35), Rail installer (149) and
historical release installer (1666). The installer model captured the initial
candidate, so it is not evidence for the corrected runtime's behavior. The new
offline gate harness passed 143 cases. Complete final-tree Actions gates remain
required; these local results are not a full source-gate pass.

Source commit `d3a7bd6fe194e74c35775328d101b7bd0d7da987` passed all **48 offline
gates**, with zero failures and only the expected no-URL rendered skip, in
[Actions 42948265339](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42948265339)
at **02:52:11 UTC**. The full job log was independently read. An exact retained
Thai prefix also passed **96/0** in phone/tablet, light/dark offline replay;
its partial scope is not a complete browser gate.

Fresh staging39-with-uploads copy1, request `peek-quiet-nav-rehearse-1`, source
`64080478bd82c816c834893af093a37313609573`, completed **FAIL** in
[Actions 42949099092](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42949099092).
It uses the first corrected runtime above, not the next candidate below.
Its complete browser graph passed **24,767/0 across 24 leaves**: retained
index-clearance 23,249/0; focused Chromium 538/0, Firefox 490/0 and WebKit
490/0, each with all 18 cases and 36 captures. The downloaded artifact was
verified against GitHub's size (97,506,592 bytes) and SHA256
`2e46e02cc2e07e195e5693893cd6d03e0ab439eb2ef4755323ddb4bf3203de2d`.

Post-browser snapshot verification and rollback passed, restoring the exact
original core and all four data records; the original fingerprint matched.
Final reapply returned success, but a PHP warning interleaved with its stdout,
producing `Deprecated: Success:`. The strict snapshot-line parser correctly
refused that malformed result, so final snapshot verification and fingerprint
comparison did not run. This is an incomplete recovery gate, not a staging
write or a successful rehearsal. The output streams need separate capture;
the exact snapshot parser and recovery requirements remain strict. The harness
now keeps stdout and stderr in separate complete files, waits for both writers,
and checks their exit statuses. Targeted tests retain the malformed-line,
duplicate, wrong-path and wrong-lane refusals; fragmented or delayed stderr
cannot supply a snapshot or corrupt valid stdout. Quiet/index/neighbour
rehearsal models each passed 107 checks; historical input-focus passed 39.
The actual cached pinned WP-CLI 2.12.0 / PHP 8.5.11 image also reproduced the
Colors.php line 95 warning without a TTY, network or WordPress bootstrap:
`WP_CLI::success()` returned exit 0, its success line alone on stdout, and the
entire warning on stderr. This directly confirms the stream-separation cause.

All 108 focused screenshots were visually reviewed, with native-size checks
of eight representative or questionable views. The boxed button and X read
clearly on ordinary content. The transparent surface became faint over the
dark Privacy footer and a pale Services illustration. Before changing it,
Ryan was told that the button would use the existing opaque theme surface.
The final candidate uses `var(--pk-panel)` in normal, hover and focus states,
retaining the same geometry and gestures. Still screenshots do not prove
header-menu dismissal.

While copy1 ran, a further actual-Thai replay exposed a reachable mixed-input
case: keyboard-open the Common reasons preview and header, then touch the visible
preview title or hint. The pointerup jump started before final-click dismissal;
normal motion landed 336px too high. Reduced motion passed. The four-case
diagnostic reported 14 passes/two failures and no script errors. This is a real
activation-order issue, distinct from the earlier test setup observations.

The second interaction candidate closes the header through the shared native-toggle helper
before both summary and title-only jumps measure their destinations. File:
110812 bytes, SHA256
`391a1c6fa553c1cf5b2ec5c6a0363d344e969f26f80d9d071d7c6fbcca56b88c`;
stored: 110806 bytes, SHA256
`18ca789e5c9f134b1b7cf8d44a783fabd8cfec084162db9e35e4d2340d4bdf91`.
Independent source review found no further issue. Source commit
`2739210cd97d2d968661b62e5ead326e7e6844d9` passed all **48 offline gates**, zero
failures, in [Actions 42953310227](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42953310227)
at **03:36:59 UTC**; the complete log was read. The recovery-harness correction
still needs its checks and a fresh full copy rehearsal; copy1 cannot authorize
this new payload.
Corrected offline replay passed **28/0**: all four real Thai title/hint and
normal/reduced-motion combinations retain the correct heading focus and hash,
land 12px below the strip, and consume the compatibility click; Corporate's
original anchor passes both motion settings. The expanded focused Thai subset
passed **92/0**, including every prior check and the added actual-preview cases.
Its two cases/four captures still correctly remain INVALID as a full gate.

The final visual candidate adds only the opaque button surface to that runtime:
110787 file bytes, SHA256
`4dfe97943898790b9c000f6dfcc129b4806183a23bc7cb7a55f1cc7db7172952`;
110781 stored bytes, SHA256
`58266413fe0e24e1dc44ef32c98f428d4537a9b63bf8f5299eb8d708a4d032e5`.
Offline actual-Thai image/footer checks passed **12/0**, with the button closed
and open, light/right and dark/left; all eight relevant images were viewed.
The 44px target is unchanged and glyph-to-surface contrast is 7.20:1–14.68:1.
This is cached-page Chromium evidence; Privacy/Services need the fresh copy.
The focused checker now requires an opaque surface and at least 3:1 glyph and
border contrast at its existing closed/open states. Its current actual-Thai
offline subsets passed Chromium **98/0** and Firefox **88/0**, including the
mixed-input jumps. Each remains correctly INVALID as a full gate (2/18 cases,
two loads, four captures); WebKit and the full matrix need the fresh copy.
Final source commit `2df138ff1e7dd0fdf48b11698b0e1a20123a5278` passed all
**48 offline gates**, zero failures, in
[Actions 42954931411](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42954931411),
completed **03:53:00 UTC**. The full log was independently read; only the
expected no-URL rendered skip occurred. Fresh staging39-with-uploads copy2,
request `peek-quiet-nav-rehearse-2`, commit
`741eff9932fbea8f3dc29ba51bd2ff4da759ce47`, completed **PASS** at
**04:31:41 UTC** in
[Actions 42955015715](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42955015715).
The complete browser graph passed **24,977/0 across all 24 leaves**: retained
index-clearance 23,249/0; focused Chromium 608/0, Firefox 560/0, WebKit 560/0.
Each focused engine completed 18 cases, 18 loads and 36 captures. The artifact
was verified against GitHub's 97,672,140-byte size and SHA256
`03aba4ebc0b2349fd03f5f0124ec3fb7403e0490648b8b19d1d027d4706d9a8f`,
plus ZIP integrity and safe extraction paths.

The copy's actual snapshots were first apply
`peek-template-core-quiet-nav-before-20261111-034939-fc2258c2.json`, browser
candidate `peek-template-core-quiet-nav-before-20261111-034944-5b212c76.json`,
and final reapply `peek-template-core-quiet-nav-before-20261111-043135-ef5e22bf.json`.
These are rehearsal evidence, not staging rollback files. Exact first/repeat
apply, original restoration, before/after-browser verification, post-browser
rollback and final reapply verification passed. The final fingerprint matched
the first applied fingerprint without ID normalization. Only #1380 and its
WPCode cache changed; the four data records retained hash
`3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`.
The PHP warning remained in separate stderr; the final snapshot line parsed
and its complete state was verified successfully.
Independent native audit confirmed **28/0** checks and all 87-row fingerprints:
A/C/E were byte-identical; B/B2 were exact, and B/B2/D/F matched with only the
existing WPCode-cache exclusion. The actual stored runtime hash was
`58266413fe0e24e1dc44ef32c98f428d4537a9b63bf8f5299eb8d708a4d032e5`.
All 19 inventory records matched; Home **35/0**, regulated client-page checks
**322/0**, and no new PHP errors. The source was staging39 WordPress 7.1.4 with
uploads, a 684,496-byte compressed DB and 268MB files, on the isolated network.
Independent browser audit checked all 24 unique leaves, 414 input traces, 162
surface records and 108 capture-state records, with no discrepancy. All 216
quiet/index JPEGs decoded; all 108 focused screenshots and six native-size
views were visually reviewed. The opaque index is clear over the actual dark
Privacy footer and pale Services image; idle reading has no floating label.
The minimum recorded glyph/border contrast was 7.1987:1. The completed job's
checkout log independently confirms commit
`741eff9932fbea8f3dc29ba51bd2ff4da759ce47`; a connector transport failure on
the large job log was resolved by reading it through the CLI. No new visual
blocker was found in the four requested changes.

The prior index fix and summary-neighbour layout
remain accepted by Ryan. Live release preparation stays paused at its older
first-install payload and the previously recorded live-copy permission boundary.

## Staging operation record

Dry run `peek-quiet-nav-dry-1`, commit
`0a2bcea76663b2b4323f1c74b1d3d3d9dedddfae`, passed at **04:37:15 UTC** in
[Actions 42960381058](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42960381058).
It selected exact staging39 and confirmed frozen-v9 readiness; no WordPress
write occurred. Guarded apply `peek-quiet-nav-apply-1`, commit
`96ce28e58421eebd8e0005856c3331f3ddf52cbf`, completed **PASS** at
**04:48:03 UTC** in
[Actions 42960510404](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42960510404).
The complete report was read. The actual in-place #1380 apply occurred at
**04:38:46 UTC**, with no fallback-only interval or page-data change.

- Fresh DB export: `staging39-before-peek-quiet-nav-20261111-043831-56757.sql`,
  4,546,217 bytes, verified server/copy SHA256 and encrypted off-site readback
  before the write (reported plaintext hash prefix `9596ac97da62`).
- Actual rollback snapshot:
  `peek-template-core-quiet-nav-before-20261111-043846-d52816fb.json`,
  verified server/copy SHA256 and encrypted off-site readback before purge or
  rendering (reported plaintext hash prefix `5b263fe4a62e`).
- Exact complete core state before/after 19 HTTP200 renders:
  `74ea2dd67eaf86ea065abeba398d41df6fe0f5451e1e0b36d11e2b309d60e56f`.
- Four-page data state remained
  `3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`.

Automatic post-checks passed source/rendered gates, public page/sitemap/robots
responses, structured data (27 items, zero parse errors, identical across the
four compared pages), and PHP-log checks. The pixel comparison against live
reported **20 differences, zero missing captures, zero identical**; this is
not a pixel pass and live has the older navigation. The backup imported
successfully; all 104 fingerprints were available and the only before/after
differences were #1380 and its WPCode cache. Dedicated public-browser checks
and final actual-snapshot verification are still required.

## Separate visual follow-up before live

The dark Search captures show very pale result-card text on white backgrounds,
also visible in the older copy1 capture. This is separate from the four
navigation changes and needs its own contrast correction before live. The
index button itself has good contrast there. No search-card styling changed
in this phase.

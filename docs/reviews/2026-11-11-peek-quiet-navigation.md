# Peek reading labels and menu buttons

**In progress; staging39 still has the accepted index-clearance version.** Ryan
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
outline, keeps its full 44×44px target in both states, and switches its existing
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

Corrected runtime: 110300 file bytes, SHA256
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

No copy rehearsal, staging write, public-browser result or new actual snapshot
exists yet for this phase. The prior index fix and summary-neighbour layout
remain accepted by Ryan. Live release preparation stays paused at its older
first-install payload and the previously recorded live-copy permission boundary.

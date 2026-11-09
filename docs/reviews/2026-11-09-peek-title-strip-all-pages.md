# Peek title strip on every page

**In progress, staging39 only.** Ryan requested: “Put a title strip on every page.
All.” They then reported a small scrolling target and sluggish scrolling after
rapidly changing summary boxes, clarifying that the larger usable scrolling area
should apply to all scrollable boxes. Both requests are included here. Existing
approved wording and the four-page summary-menu rollout remain separate.

## Access and baseline

The requested branch `claude/wizardly-bell-xu0rta` was clean and pulled at
`610a898f63e5ac6dfb14d602fdc77f68c8c9f5a1`, already current. GitHub repository
read/write access is confirmed. The current managed environment reports enforced
unrestricted networking. A fresh staging request at **08:15:03 UTC** received
SiteGround HTTP202 with `sg-captcha: challenge`; direct access is not treated as
working and is not repeatedly retried. The established Actions route supplies
origin inspection, rendered evidence, rehearsals and guarded deployment.

The baseline is the [completed short-summary correction](2026-11-09-peek-short-summary-touch.md):
shared core #1380 code SHA256
`a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac`,
71,826 code bytes, with four approved data records #1381–1384. Its last public
browser and stored inspections passed; those are baseline evidence, not testing
of this new change.

## Design announced before implementation

The current strip is gated by page data, a jump row, at least two destinations
and PointerEvent support. Those requirements belong to the interactive rail;
they prevent a read-only strip from appearing on other pages.

The announced simplification is one shared strip constructor, section tracking,
header measurement and title painting, with independent rail eligibility:

- Reuse the existing jump row's labels and actual destination order wherever a
  row exists. It remains the sole navigation map for those pages.
- Where no row exists, read the page's visible main headings. This does not add
  a second navigation map or alter stored page content.
- With no sections, show the page title as one fixed label. Short pages are
  included. A safe WordPress title fallback avoids treating a personal-name H1
  or SEO document title as an invented section label.
- Use a separate title-ready state for header and focus clearance. Rail-ready
  state continues to control rail padding and replacement of existing controls.
  Title-only pages keep their jump links and existing navigation.
- Include published unlisted and practice pages in the inventory and verification,
  rather than silently limiting “all” to the menu or sitemap. Review their actual
  structures for competing demo navigation. Normal search/404 HTML also receives
  the read-only fallback; WordPress admin/feed responses are not visitor pages.

There is no new summary wording approval or automatic full-rail rollout in this
change. Approved summaries and whole-paragraph fallbacks remain intact.

## Fresh complete inventory and design assessment

[Read-only inventory](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42585875924)
completed **08:27:49 UTC, PASS**, source
`27eb2c3a5cb49c3ab1d62203c81844e2b3e9cf9f`. All **19 published pages** returned
HTTP200 at origin and rendered successfully in Chromium, with **38 actual mobile
and desktop captures**, no failed routes and no browser challenge. Root read the
complete operation report and verified the extracted 2,847,057-byte archive SHA256
`f7d24f95a4f044f79fddacf1569b09a7325e2e3abc0ca3c2e185f323b7ceee9c`.

The reviewed `title-strip-pages.json` records all 19 exact IDs, paths and decoded
WordPress titles with provenance. It is test/deployment coverage, not a runtime
navigation map. It includes the twelve established site pages, the three pages
under `/personal-automation/` (including nested `/privacy/` and `/terms/` children),
and all four practice pages. No page is silently excluded by menu or sitemap scope.

All [38 baseline captures](assets/2026-11-09-peek-title-strip-all-pages/before/)
were independently reviewed. Root also viewed Home, Contact, Work With Me,
Privacy, Personal Automation and the rail practice page directly. Actual DOM and
source confirm these consequential differences:

- Services has seven mixed-level destinations; Contact includes a non-heading
  contact-details card, and Privacy has accordion targets. Their existing rows
  preserve intended labels and targets better than heading-only discovery.
- Work and Corporate have their own main-heading structures and mobile bottom
  CTA docks. Work's visible H1 is the person's name; its page identity is
  “Work With Me.” Some headings contain line breaks that need whitespace when
  reused in the strip, rather than concatenated words.
- Theme-title pages place their H1 outside `.entry-content`; their actual strip
  placement and sticky clearance need rendered review.
- The practice pages have separate demo controls. Their old sticky bars use
  `top:0`, and Progress/Rail jumps omit the new shared header/strip clearance.
  Before building compatibility changes, Ryan was told those controls would sit
  below the shared strip and their existing jumps would retain visible headings.
  Initial-view captures alone do not verify this scrolled behavior.

## Larger scrolling area and rapid-switch report

Ryan specifically named Therapeutic's research summary, then clarified the
problem may affect all boxes. The reported device/browser is not yet identified.
The source caps ordinary summaries at `9.8em` (about 123 px on the mobile fixture)
and shrinks the summary before folding distant closed rows. An independent
native Chromium probe on local structural fixtures of rapid Therapeutic row taps
followed by an immediate swipe, including 4× CPU slowdown, started scrolling;
measured show work was
13–18 ms, with no intentional loading timer. This does **not** disprove the
reported device delay or establish a reproduced latency defect.

The announced correction gives every scrollable summary more height and folds
distant closed boxes before reducing the summary on tight screens. Neighboring
boxes, the section title and jump control remain available. Enlarging the cap
alone is insufficient: it can trigger the old fitter's smaller 6.6em state and
make the target worse. Validation must include all four pages and immediate
tap→swipe sequences without the old test's 180 ms settling delay, preserving
short-box containment, native long-summary scrolling, focus and reading position.

## Candidate checks before the fresh-copy rehearsal

These results are local structural fixtures, not staging or physical-device
results: title-only fixtures **125/0**, retained generic rail **207/0**, native
Chromium touch **386/0** (including all four pages, both sides, immediate
switch→swipe sequences), and Chromium clearance **3,340/0**. The long-summary
phone fixture grows from **123.3 to 226.5 px**. Local Firefox is unavailable;
the fresh-copy Chromium/Firefox gate remains required.

A direct-current-runtime probe covered all twenty practice-demo modes and
expanded Now/Next navigation with normal motion. Demo source/CSS was checked
against the actual captured HTML, but the local header is a fixture. The shared
core offsets those existing sticky controls and adjusts only Progress/Rail
section jumps marked by their existing `go()` paths. Preview/index gestures do
not trigger that adjustment. Full site typography and layout remain for the
fresh-copy gate. Wrapper orchestration separately passed **28 stub cases**;
these validate verdict handling, not rendered pages.

The complete local offline run returned **26 PASS, 3 SKIP, 0 FAIL, 0 INVALID**.
The skips were missing shellcheck/ruff and URL-dependent rendered checks; Actions
must provide the former and the fresh-copy gate the latter. Pinned PHP traversed
**127 files, zero errors**; installer/recovery checks were **382/0**, and safe
current-page emission checks **22/0**. An initial temporary Docker-adapter
permission failure was corrected and is not counted as verification.

## Deployment and recovery boundary

The current payload is frozen in `peek-nav-shared-v3.php`, file SHA256
`6535607a90787db678fad058ab5c6a1e105ac790b3020ab49a6fd4a8634abab4`.
The previous `core-summary-touch` transition remains pinned to that exact v3,
preserving the meaning of actual snapshot
`peek-template-core-summary-touch-before-20261109-075957-5ef2bd29.json`.

The new `core-title-strip` transition updates exact v3 in #1380 in place, binding
the four unchanged data records and complete snippet state. Its own actual
snapshot must be recorded after apply. Recovery reverses this new transition to
v3 first, then any separately intended older transitions with their own snapshots.
No older snapshot can reverse the new payload. No fallback-only interval is
planned. WordPress storage only; main/live remain untouched.

Before deployment: read-only complete inventory and rendered design review,
offline gates, fresh staging39-copy rehearsal with uploads, rendered regulated
wording checks, exact rollback/reapply, all-page strip checks and retained Peek
gesture/touch checks. Afterward: read every apply/backup/restore section, actual
public browser verification, exact stored hashes and final screenshots. INVALID
results establish no verification. Safari, physical devices and actual assistive
technology remain separate validation work.

## Candidate Actions gate

Candidate commit `377eb19c9bab523b31bd36d05a6502c4602b27e4` passed
[Actions Gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42589914194)
at **09:00:04 UTC**: **29 PASS, 1 SKIP, 0 FAIL, 0 INVALID**. The only skip is
rendered-page verification because this offline gate has no URL. Root read the
complete job log. This includes shellcheck and ruff unavailable in the local run.
The actual-copy rehearsal was requested separately at source
`3403c6ccff9fe065d9847e011608b4ff606af5fa`.


## First actual-copy rehearsal: failed; no staging write

[Rehearsal1](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42589939262)
finished **09:03:04 UTC, FAIL**. Root read the complete 219-line operation report
and verified the retained artifact:4,719,086 bytes, SHA256
`44a6aaeacd12e626f6a7305fe02f38429faf635c3a280859c9fc0cca31d167ce`.
The copy used staging39's actual DB and 268 MB files/uploads. Preflight, apply,
idempotence, exact rollback/reapply, page responses, PHP and rendered wording
all passed. Only #1380 and WPCode's cache changed in the copy. Its `/backups/`
snapshots are disposable rehearsal artifacts, not staging recovery snapshots.

The title matrices rendered all 19 published routes plus search/404. Chromium
returned **1092/12**, Firefox **1083/18**, with 42 actual captures. Retained
rail/summary/Thai suites were deliberately **not run** after these failures.
The failures expose ordinary theme link jumps landing beneath the new pinned
header/strip, an About menu assertion needing diagnostic separation, a search
grid assumption, and a legacy Firefox rail-demo preview issue requiring baseline
evidence. No overall browser pass or new summary verification is claimed.

All42 initial-view captures were independently reviewed, with root also viewing
Work, About and the search grid. Strips are readable, leave the existing rows
available, and preserve full-width panels and mobile CTA docks. Those top views
cannot establish open-menu, scrolled-demo or open-summary behavior.

Before further implementation, Ryan was told that title-only pages would reuse
Peek's existing in-page link handling to clear the pinned stack. Root separately
judged parallel search-result cards unsuitable as sequential sections; the
announced simplification keeps a strip with the search-page title there. Normal
pages retain section tracking. Staging remains unchanged while these corrections
and diagnostic checks are prepared.


## Read-only baseline diagnosis after the failed rehearsal

[Behavior inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42591357575)
finished **09:11:48 UTC**, source `ce6258090e9fd4bd1fdeb1cdd4c1ba0e26b2c971`.
The operation succeeded as an observation, not a candidate pass/fail gate. Root
read every report section/record. Artifact 143,269 bytes has verified SHA256
`5623d395cce586203c344cf4316d2df1b0be07d732763f59c2146703a4dccb32`.
No candidate code was injected; no website storage was changed.

On existing pinned Thai, both Chromium and Firefox report scrollY 600→936→600
when the 336px menu opens and closes. The content's viewport position remains
exactly unchanged, and the open menu spans 85–421px inside 844px. The correct gate
therefore checks visible reading position, menu reachability and unchanged
address, plus restoration on close. It must not disable browser scroll anchoring
just to freeze a numeric coordinate.

The old rail practice demo's Firefox Enter emits click detail 1, which its legacy
handler rejects; all three widths reproduce the same failure on unchanged
staging. Chromium Enter and both engines' pointer preview/Go work. Candidate
checks retain an explicit known-baseline observation only for that exact Firefox
state, while requiring pointer preview/Go and unchanged page/address. This does
not claim a Firefox keyboard pass or change the deployed Peek keyboard paths.
The public Astra 4.14.1 script was retained exactly: 26,933 bytes, SHA256
`fe3e5e84d82d3f30b11185995b22935293b7b7234298ddf480b5249a326b9bd7`.

Local Firefox was subsequently installed for meaningful fixture comparisons.
New PHP title-mode emission checks pass 24/0; earlier statements that it was
unavailable describe the first local run, not the current environment.


## Corrected candidate before the second rehearsal

The reviewed runtime is 86,567 code bytes, SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`.
Title-only content links retain their exact encoded fragment and reveal native
closed details/FAQ content, then align below the shared stack. Ordinary links
scroll once. Animated FAQ and initial-fragment correction has the existing
four-second limit, observes only that FAQ group's item sizes where necessary,
and cancels on pointer/touch/wheel/key/mouse/history input. Input before page
load also suppresses a later arrival correction. Repeated FAQ activation was
reproduced locally and corrected; the four rail paths remain unchanged.

Focused local Chromium/Firefox probes pass for encoded links, closed details,
animated and repeated FAQ activation, initial fragments and cancellation, with
zero JavaScript errors. Updated title fixtures are 181/0 per engine; demo gates
are 39/0 with explicit baseline Firefox keyboard observations; the exact menu
gate is 8/0 across both engines using saved markup and captured Astra source.
These remain local fixtures, with external styles/fonts omitted in the menu
probe. Installer checks 382/0 and PHP emission 24/0 pass. The fresh-copy gate also
covers the actual Corporate booking-options hero anchor, alongside Work.

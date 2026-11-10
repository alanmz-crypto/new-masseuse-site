---
name: link-ideas-practice-page
description: Peek navigation on staging39 only; shared core and four approved service-page data snippets, with current evidence and guarded recovery records; original practice-page history retained
metadata:
  type: project
---

**Current task, November 10: fixed summary neighbours, staging39 only.** Ryan
reports missing neighbouring links and scrolling trouble across the four service
pages with Safari’s address bar expanded. They requested only the immediate
previous summary above and next below each open box. The
[neighbour-layout record](../../docs/reviews/2026-11-10-peek-summary-neighbours.md)
tracks the deployed core, actual backup/snapshot and browser/recovery checks.
Deployed to staging39 #1380 at 14:32 UTC. Copy rehearsal passed **19,061/0**;
public browsers passed **15,646/0 across 16 suites** at 15:16:58, with complete
leaf evidence audited despite combined-console truncation. Actual snapshot
verification passed at 15:19:21: exact core/four data state before and after
19 HTTP200 renders. iPhone Safari acceptance remains open. Live-release
preparation is paused; the blocked live-copy permission remains unapproved.
Separate pre-live clearance item: the left index icon overlaps the announcement
in 390×500 captures. Its positioning code is unchanged from v7, but a rendered
v7 baseline and physical Safari hit-testing have not established its origin.

Current recovery starts with `core-neighbours` → frozen v7, using
`peek-template-core-neighbours-before-20261110-143200-81725f33.json`,
after a fresh verified backup and exact ownership/state checks. All older shared-core
recovery sequences below apply only after that restoration.

**Release preparation, November 10: not ready for live.** The [readiness record](../../docs/reviews/2026-11-10-peek-release-readiness.md)
contains the exact scope, new read-only live/staging inventories and current
acceptance blockers. At that inventory, all 15 shared page bodies matched after
origin normalization, and staged core/four data payloads matched source. Live
metadata is preserved. The localhost-only first-install/rollback package is
paused at the preceding core; no live enablement or request exists. Ryan's
iPhone reply confirms rail/index behavior and background containment but leaves research scrolling
and the contradictory Privacy/hamburger result to clarify. No Safari pass is
claimed; no website runtime changed during this preparation.
The fourth live-sourced disposable-copy rehearsal identified the first draft's
full-state mismatch: both shared WPCode category counts rise from 17 to 18 for
an inactive snippet. The installer incorrectly counted only published records.
That correction passed first-draft creation and attribute initialization in copy5.
Code correction then stopped on WordPress 7.1.4's exact `&` to `&#038;` GUID
conversion, confirmed against official source. A narrowly specified save-phase
expectation passed source CI (39/0); full-state and rollback checks remain exact.
Automatic approval review blocked copy6 before execution because the new live
database/uploads copy needs explicit scope permission beyond staging39. That
question is pending; no copy6 request was committed or dispatched. No complete
install or browser phase has passed in this release-copy lane; see the readiness
record for actual attempts and copy-only journals.

**Previous baseline: input-focus core and Rail practice updates on staging39.**
See the [canonical phase record](../../docs/reviews/2026-11-09-peek-input-focus.md) for complete attempts, hashes, backups
and evidence. Core apply **42729961919** wrote #1380 at **2026-11-10 00:40:21 UTC**
and finished **00:45:55 UTC, PASS**; fresh-copy proof passed **14,386/0 across
15 suites**, with exact pre/post-browser rollback. All 19 served routes and the
core/four data records matched before/after rendering. Public **42731171781**
passed **12,374/0 across 14 unique suites** at **01:04:18 UTC**, including 128
focus checks and all eight summary jump/focus cases. Snapshot verification
**42731765629** passed **01:05:05 UTC**: exact core/four data records before/after
19 HTTP200 renders. Independent public/exact-state audits are complete; all four
focus captures match reviewed copy views. Broad wording/URLs/schema/PHP passed;
**20/20 live-reference pixel differences are not a visual pass**.

Rail apply **42724350260** updated #1348 at **2026-11-09 23:56:52 UTC**; its
fresh copy passed **2,632/0** and snapshot verification **42727758208** passed.
Public Chromium passed **121/0**, but Firefox **115/2** drawer mouse-open failures
remain unresolved. Diagnostic **42734807322 (64/1)** showed gestures 1–4 are
sufficient to reproduce missing mouse input; the underlying cause is unproved.
The first hold split **42736953507** was INVALID HTTP202 (zero assertions).
Its once-later retry **42738993282** finished **01:55:11 UTC, 26/1**, with two
loads and a verified HAR: hold-to-go alone reproduces the later missing mouse
input; gestures 1–3 pass. Offline replay did not reproduce it but remained
INVALID because the isolation proxy blocked one browser-level connection.
The mechanism is unproved; no runtime patch follows. This staging-only practice
investigation is paused and does not block the separate visitor release.

Actual recovery snapshots, both verified encrypted off-site:

- Core: `peek-template-core-input-focus-before-20261110-004021-4570ecbf.json`.
- Rail page: `rail-input-before-20261109-235652-0adc77b9.json`.

Only after `core-neighbours` restores frozen v7, and with fresh verified
backups/full-state checks, may `core-input-focus` restore v6, followed if needed
by older Kiro→v5/title-rail→v4 routes. Rail recovery uses
its independent full-state `rail-input` route, never the old delete/recreate
installer. No fallback-only interval occurred. Actual iPhone Safari acceptance
is incomplete; see release readiness above. Official Linux WebKit now installs
and launches in Actions, but its public attempt was INVALID HTTP202 with zero
assertions. The later neighbours copy6 passed WebKit **1,304/0**; that is
Linux WebKit evidence, not physical Safari. Real OS text scaling and assistive
technology remain unverified. Main/live remain untouched.

**Durable page-update lesson:** a script-only `wp_update_post` ran save hooks
that removed Spectra `_uag_css_file_name`/`_uag_page_assets` and changed SureRank's
check timestamp. The reviewed `rail-input` route instead uses byte-exact content
compare-and-swap through WordPress `wpdb`, targeted fresh cache reads and complete
state/snapshot guards. The copy's first unchanged-page GET moved only the known
asset cache (708→713 bytes); its second read was fully stable. Actual staging
kept the original 708-byte value through both purge/render cycles. Backups precede
warming and follow proved stability; install snapshots are never rebased. Exact
post-browser rollback caught the earlier drift even when all browser tests passed.

**Previous deployed baseline:** [Kiro fixes, frozen v6; historical public/stored proof](../../docs/reviews/2026-11-09-peek-kiro-fixes.md).
The earlier practice and deployment stages below are historical; current core recovery
starts with the actual neighbours snapshot at the top, restoring frozen v7
before the input-focus snapshot can restore v6.

**What:** Ryan (2026-11-08, cloud session) asked for a practice page on staging39 with five modern ideas to replace the plain in-page
link row at the top of the service pages, "real code that really works so that we know we can use it on the final version".

**Where:** https://staging39.thaisolitude.com/in-page-link-ideas/ (page #1340; `?idea=1` to `?idea=5` opens one idea directly).
Source: `scripts/work-with-me-employer/link-ideas/` (`link-ideas.html` = the single HTML block with its own CSS and plain JS, no plugin;
`install-link-ideas.php` preflight/apply/rollback, allow-list localhost:8090 + staging39 only; `push-link-ideas.sh`; `check-link-ideas.mjs`).

**The five ideas:** 1 sticky pills with scroll-spy (the highlight follows the section being read); 2 segmented progress bars, one per section;
3 numbered topic cards plus a floating "Topics" back button; 4 a search box (native `<dialog>`, Ctrl/Cmd+K, arrow keys); 5 a floating
"island" pill at the bottom that shows the current section and a progress ring and opens into the full list.

**Proof:** rehearsal on a copy of staging39 passed (only fingerprint moved: the new page). Applied 2026-11-08 00:35 UTC
(export `staging39-before-link-ideas-20261108-003442.sql`, verified encrypted off-site; snapshot `link-ideas-before-20261108-003500-3f7cf593.json`).
Actions browser check `ui-link-ideas-staging39-20261108`: Chromium 96/0, phone + desktop, light + dark. Firefox/Safari and real devices not tested.
post-change-test.sh / prove-restore.sh do not list staging39 yet, so they did not run.

**Lesson (durable):** WordPress rendering of page content turned `&` inside a `[...]` in an inline script into `&#038;`
(the regex `/[&<>"]/g`), which broke the script. Keep `&` out of brackets in inline page scripts; the installer now refuses it.

**Undo:** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-link-ideas.sh --args '--rollback $HOME/private-backups/link-ideas-before-20261108-003500-3f7cf593.json'`.
**Next:** Ryan picks an idea (or a mix); the chosen design then goes into the real jump-links snippet on a staging copy first, live only on his go.

## Ryan's pick: pills + pinned header, on the real Thai Massage page (2026-11-08)
Ryan: "Pills is best. Can we also make the header static. You can practice on a real staging page, now." Asked: "Header stays on top", "Thai Massage".
**Applied on staging39 only** (01:22 UTC): WPCode snippet #1344 "TSPillNav practice (Thai, staging39)" from `link-ideas/pill-nav.php`
(installer `install-pill-nav.php`, push `push-pill-nav.sh`, browser check `check-pill-nav.mjs`). It pins `#masthead` (sticky), restyles the existing
server-rendered `.ts-jump` row as sticky pills right under it with a sliding highlight, and hides the bottom "On this page" guide button on that page.
No page content, Additional CSS or other snippet changed; other pages are untouched (checked). Export `staging39-before-pill-nav-20261108-012223.sql`
verified encrypted off-site; snapshot `pill-nav-before-20261108-012240-f7bbde39.json`. Chromium check `ui-pill-nav-staging39-20261108-5`: **57/0**
(phone + desktop, light + dark: header stays at 0, pills directly below, each tap lands the heading just below the pills, the phone menu opens and fits,
arriving at `#ts-guide-research` lines up, Therapeutic unchanged, no JS errors). Firefox/Safari/real devices not tested.
**Lessons (durable):** (1) Astra's `astraSmoothScroll` (frontend.min.js) scrolls to a `#hash` about 0.9 s after load and ignores any pinned header, so
CSS `scroll-padding`/`scroll-margin` alone is not enough: the runtime re-aligns after scrolling settles (only during the first 4 s, and never once the
visitor touches, scrolls or types). Pill taps are handled in a capture listener on the nav, before Astra or the FAQ handler. (2) The section guide
sets some heading ids (e.g. `ts-guide-research`) only at runtime. (3) In the push scripts, `grep -o ... | wc -l` under `set -o pipefail` stops the
script when the count is 0: use `{ grep ... || true; } | wc -l`.
**Undo:** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-pill-nav.sh --args '--rollback $HOME/private-backups/pill-nav-before-20261108-012240-f7bbde39.json'`.
**Next:** Ryan reviews https://staging39.thaisolitude.com/thai-massage/ . To roll out: widen `is_page()` to the other service pages (or move the CSS
into the jump-links snippet), staging first, then live only on his go (enable-live change in the installer allow-list and push targets).

## Progress ideas practice page (2026-11-08; pills set aside)
Ryan: pills "clunky, but let's put the pills aside"; wants the progress method, but showing what each section is, "phone and the tablet first".
**Applied on staging39 only:** https://staging39.thaisolitude.com/progress-nav-ideas/ (page #1346; `?idea=1..5`). Source `link-ideas/progress-ideas.html`,
`install-progress-ideas.php`, `push-progress-ideas.sh`, `check-progress-ideas.mjs`. Ideas: 1 growing segments (sized by section length; current one grows
and shows its name); 2 bar that opens a bottom sheet (every section with description, reading time, own progress, ticks); 3 Now/Next (ring + "Next: ..."
button + timeline list); 4 touch scrubber (slide along the bar, bubble previews name + description, release jumps); 5 edge rail (right edge; slide to fan out
all names). Reading times are sample `data-mins` values. Export `staging39-before-progress-ideas-20261108-015124.sql` verified off-site; snapshot
`progress-ideas-before-20261108-015138-29c66758.json`. Chromium check `ui-progress-ideas-staging39-20261108`: **98/0** (iPhone 13 + iPad Mini, light + dark,
real touch events for 4 and 5). Safari/real devices untested. The pills snippet #1344 is still on the staging39 Thai page (undo command above).
**Lesson (durable, supersedes the ampersand note above):** in page content, WordPress's shortcode scanner reads any `<` inside an inline script as the start of an
HTML tag and then escapes `&` after it (`&&` became `&#038;&#038;`). Write inline page scripts with no `<` at all (flip comparisons; `<` in strings);
`install-progress-ideas.php` refuses a script that contains one.

## Side rail ideas (2026-11-08; top-bar scrub dropped)
Ryan asked for a scrub that browses topics "and not lose your place" and also jumps "when you choose"; then: reaching the top of a phone is uncomfortable,
"drop the scrub ... on the side not top area, let's use the rail method. Create five possible ways to Scroll and preview and scroll and jump-to that are intuitive."
(The browse-first top scrubber was drafted but never deployed; /progress-nav-ideas/ stays as it was.)
**Applied on staging39 only:** https://staging39.thaisolitude.com/rail-nav-ideas/ (page #1347; `?idea=1..5`). Source `link-ideas/rail-ideas.html`,
`install-rail-ideas.php`, `push-rail-ideas.sh`, `check-rail-ideas.mjs`. Rail on the right edge between 30% and 86% of the screen height (thumb zone).
Ideas: 1 slide, lift, then Go/Stay card; 2 slide, then pull the thumb inwards until the card turns green, let go to jump; 3 slide to peek at a mini preview,
tap it to go; 4 slide, then hold still until a ring fills; 5 a short tab that opens a right-hand drawer (tap or swipe left; swipe right to close).
Every idea: browsing never moves the page, and after a jump "Back to <section>" returns to the exact scroll position.
Export `staging39-before-rail-ideas-20261108-022953.sql` verified off-site; snapshot `rail-ideas-before-20261108-023010-860acf1b.json`.
Chromium check `ui-rail-ideas-staging39-20261108`: **142/0** (iPhone 13 + iPad Mini, light + dark, real touch events). Safari/real devices untested.
**Lessons:** a drawer that should close on a sideways swipe needs `touch-action: pan-y`, or the browser cancels the pointer; buttons tapped right after a
gesture need `touch-action: manipulation` (double-tap-zoom detection can swallow the tap). When writing page HTML with the Write tool, `<` in the
content arrives as a real `<`: escape the script's strings with a script afterwards and re-check that it has no `<`.

**Top strip added (2026-11-08):** Ryan: "leave the scrub in the top, just don't make it interactive ... just there so people know where they're at in the page and what
the topic is." The rail page now has a sticky, display-only strip (current topic, "2 of 6", one bar per section sized by length); `pointer-events: none`, no
controls, `aria-hidden` (the rail has the links). Jumps land just below it. Re-applied as page **#1348** (snapshot `rail-ideas-before-20261108-024214-c7b6cfab.json`,
export `staging39-before-rail-ideas-20261108-024155.sql`); check `ui-rail-ideas-staging39-20261108-2`: **166/0**.
**Undo:** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-rail-ideas.sh --args '--rollback $HOME/private-backups/rail-ideas-before-20261108-024214-c7b6cfab.json'`.

## Ryan's pick: Peek + display-only top strip, on the real Thai Massage page (2026-11-08)
Ryan: "Peek. Let's use this design. Now you can try it on a real staging page."
**staging39 only:** the pills trial (snippet #1344) was rolled back first (its own snapshot; exact undo). Then WPCode snippet **#1349**
"TSPeekNav practice (Thai, staging39)" from `link-ideas/peek-nav.php` (installer `install-peek-nav.php`, push `push-peek-nav.sh`, check `check-peek-nav.mjs`).
Built from the existing `.ts-jump` row (kept as the no-JavaScript fallback): names = row labels; preview = each section's real heading and first paragraph;
reading time = words / 200. Strip + rail appear once the row scrolls away; the row's own taps and `#section` arrivals land the heading below the strip
(capture handler + settle-align after Astra's smooth scroll); the bottom "On this page" guide button is hidden on that page; text gets 26px right padding
under 1100px so the rail does not cover it. Site header scrolls normally (not pinned). Export `staging39-before-peek-nav-20261108-030238.sql` verified
off-site; snapshot `peek-nav-before-20261108-030251-3fb7ea6c.json`. Chromium check `ui-peek-nav-staging39-20261108`: **103/0** (iPhone 13 + iPad Mini,
light + dark, real touch; previews for all five sections; Back exact; row taps; `#ts-guide-research` arrival; Therapeutic unchanged).
**Undo:** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-030251-3fb7ea6c.json'`.
**Next:** Ryan reviews on his phone. Rollout: widen `is_page()` to the other service pages (Therapeutic, Deep Tissue, Chair, Services), staging first, then live
only on his go (enable-live change in the installer allow-list and push targets; live dry run; `request.sh live`).

**Mock-up parity (2026-11-08):** Ryan: "I want you to keep all the features from your peek mock-up." Now snippet **#1350** (replaced #1349): strip in the page right
under the row, sticking to the top as you read (display only; 44px right padding on phones/tablets so the rail never covers it); rail visible from page load at
the mock-up sizes (48px, card 64px from the edge); the section name shows beside the rail while the page scrolls; the heading glows after a jump; the card has a
one-line description (first sentence) plus the preview (real heading, first lines, three placeholder lines); the rail's screen-reader names include the
description. Export `staging39-before-peek-nav-20261108-031610.sql`; snapshot `peek-nav-before-20261108-031625-f507cb73.json`; check
`ui-peek-nav-staging39-20261108-2`: **123/0**.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-031625-f507cb73.json'`.

**Kiro review (2026-11-08):** Kiro (web) reviewed `peek-nav.php` by reading it; outcomes per finding in `docs/inter-model/2026-11-08-peek-nav-kiro-review-response.md`.
Now snippet **#1351** (replaced #1350). Export `staging39-before-peek-nav-20261108-040148.sql`; snapshot `peek-nav-before-20261108-040204-32ffad9a.json`;
check `ui-peek-nav-staging39-20261108-4`: **167/0** (first attempt INVALID 202, one retry). Waiting for Ryan: left-hand option, desktop hover preview, first-time hint.
**Lessons:** after a pointer-captured drag no click follows, so "was this click from a press" must be a short time window, not a flag; a tap right after a
gesture can be dropped before it becomes a click, so act on a short still `pointerup` for touch and keep `click` for mouse/keyboard.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-040204-32ffad9a.json'`.

**Left-hand option + first-time hint + second Kiro review (2026-11-08):** Ryan: "Yes to left-hand option and first-time hint". Now snippet **#1353**
(replaced #1351; #1352 lived ten minutes and was replaced for a theme underline found by the new computed-style check). Export
`staging39-before-peek-nav-20261108-050659.sql`; snapshot `peek-nav-before-20261108-050713-0f6a1da8.json`; check `ui-peek-nav-staging39-20261108-7`: **223/0**.
Details per finding: `docs/inter-model/2026-11-08-peek-nav-kiro-review-response.md`. Desktop hover still open.
**Lessons:** commit messages with double quotes break `git commit -m "..."` in a one-line command: write the message to a file and use `-F`; when that
commit failed, `request.sh` still pushed and ran the previous commit (harmless here: "Already applied"). A resumed cloud session may have Docker stopped:
`sudo -n dockerd &` brings back the three Docker gates of `check-all.sh`.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-050713-0f6a1da8.json'`.

## Index icon ideas (2026-11-08)
Ryan: "Above the rail, directly above it, ... a simple, tasteful, minimalistic index icon that when you press will show all of the indexed items from the rail so
that they slide out and slide back into the rail ... Create five designs"; "when you touch that index, they will slide out, and when you lift your finger up, they
will disappear back into the rail."
**staging39 only:** practice page https://staging39.thaisolitude.com/index-nav-ideas/ (page #1354; `?idea=1..5`). Source `link-ideas/index-ideas.html`,
`install-index-ideas.php`, `push-index-ideas.sh`, `check-index-ideas.mjs`. A 44px icon 10px above the rail; press and hold = names out (page does not move),
lift = names back in; click without a press (keyboard, VoiceOver/TalkBack) toggles; Escape closes; long press menu blocked. Designs: 1 Fan (list icon; names slide
sideways out of their own rail piece, staggered), 2 Unfold (three dots; a dark list unfolds downward from the icon), 3 Drop (four squares; names drop out of the icon
and bounce into place beside their piece), 4 Panel (bookmark; the rail widens into a light panel and the names fade in), 5 Arc (compass; names swing out on a curve).
Current section highlighted, read ones ticked. Export `staging39-before-index-ideas-20261108-052106.sql`; snapshot `index-ideas-before-20261108-052120-93294332.json`;
check `ui-index-ideas-staging39-20261108-2`: **222/0** (iPhone 13 + iPad Mini, light + dark, real touch). Not yet on the Thai page; waiting for Ryan's pick.
**Lesson:** an `<svg>` element has no `hidden` property; toggle the attribute (`setAttribute('hidden','')` / `removeAttribute`).
**Index icon on the Thai page (2026-11-08):** Ryan picked design **1 (Fan)**: "the index icon with the slide open feature can remain persistent at the top of the rail,
but please have it blended to the theme of the page." Now snippet **#1355** (replaced #1353): a 44px list icon 8px above the rail, always shown with the rail,
drawn in the page's muted text colour on the page background (no box, border or shadow; checked against the computed `--pk-ground`), green while held.
Hold = every name fans out beside its rail piece (staggered, current one green, read ones ticked); lift = back in; click without a press toggles; Escape closes;
long press blocked; mirrors with the left-hand option; one keyboard stop right before the rail. The first-time hint now also mentions the icon.
Export `staging39-before-peek-nav-20261108-061042.sql`; snapshot `peek-nav-before-20261108-061058-bfb0e317.json`; check `ui-peek-nav-staging39-20261108-8`: **267/0**.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-061058-bfb0e317.json'`.
**Row removed (2026-11-08):** Ryan: "You can remove the in-page links beneath header." Now snippet **#1356**: with the runtime ready (`html.ts-pk-ready`) the
server-rendered `.ts-jump` row is `display:none` and the strip sits in its place; without JavaScript the row still shows and works (checked with JavaScript
off). The row stays in the HTML as the source of the section list. Export `staging39-before-peek-nav-20261108-062336.sql`; snapshot
`peek-nav-before-20261108-062353-1c40c41c.json`; check `ui-peek-nav-staging39-20261108-9`: **272/0**.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-062353-1c40c41c.json'`.
**Back button, reading-progress bar and scroll-to-top removed (2026-11-08):** Ryan: the thin progress bar is "unnecessary ... That's on every page. Remove it";
"get rid of the back to section button"; "get rid of the scroll to the top button"; where: "Every page on staging39".
- Snippet **#1357** (replaced #1356): no "Back to ..." button. Snapshot `peek-nav-before-20261108-064135-d72debe4.json`; export `staging39-before-peek-nav-20261108-064124.sql`.
- New `link-ideas/install-sitewide-cleanup.php` + `push-sitewide-cleanup.sh` (staging39 only): removed the marked `/* ts-reading-progress:start ... :end */`
  block (1,095 bytes) from the Additional CSS (rest byte-identical; rehearsal proved the exact undo) and set Astra `astra-settings['scroll-to-top-enable']`
  to false. Server checks: six pages 200 with neither. Export `staging39-before-sitewide-cleanup-20261108-064533.sql`; snapshot
  `sitewide-cleanup-before-20261108-064546-26530c7b.json`. Note for live: the reading-progress feature is LIVE sitewide since 2026-11-04 (see its record);
  removing it on live needs Ryan's go and an enable-live change.
- Check `ui-peek-nav-staging39-20261108-10`: **269/0**.
**Undo:** snippet: `... push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-064135-d72debe4.json'`;
cleanup: `scripts/ci/request.sh run --target staging39 --script link-ideas/push-sitewide-cleanup.sh --args '--rollback $HOME/private-backups/sitewide-cleanup-before-20261108-064546-26530c7b.json'`.
**Header pinned (2026-11-08):** Ryan: "Would you make the header persistent too?" Snippet **#1359** (replaced #1357; the Thai page, like the rest of the design):
with the runtime ready `#masthead` is `position:sticky` (admin-bar aware), the strip sticks right under it (`--pk-head` measured with a ResizeObserver),
the rail starts below header + strip + icon on short screens, and the Astra phone menu scrolls within the screen (checked: opens at 85px, fits 664px).
Not pinned below 501px of height (landscape phones), where header, strip and rail would not fit. Export `staging39-before-peek-nav-20261108-065824.sql`;
snapshot `peek-nav-before-20261108-065841-c543ce54.json`; check `ui-peek-nav-staging39-20261108-12`: **280/0** (the first run tapped the pinned header's logo
in a test step and left the page; the test now taps the strip and the page margin).
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-065841-c543ce54.json'`.
**Menu flicker fixed (2026-11-08):** Ryan: "When i open the hamburger menu on this page it flickers, now." Cause (reproduced on staging39 by a new 40-frame check:
header 328/421px alternating): the open Astra phone menu sits inside `#masthead`; `--pk-head` measured the whole header, and the menu's `max-height` used
`--pk-head`, so each set the other. Now only the header bar is measured (header minus `.ast-mobile-header-content`). Snippet **#1360**; export
`staging39-before-peek-nav-20261108-072416.sql`; snapshot `peek-nav-before-20261108-072428-8b5dda0b.json`; check `ui-peek-nav-staging39-20261108-13`: **282/0**
(menu steady at 421/336, `--pk-head` 85px). **Lesson:** never size a child from a measurement of a parent that contains it.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-072428-8b5dda0b.json'`.
**Index-to-rail slide (2026-11-08):** Ryan: "if you press the index, it behaves as you have created it. However, if you press the index and then slide to the rail
without lifting your finger, then the rail options open up." Snippet **#1361**: the icon keeps the pointer (capture); once the finger crosses the rail's top edge the
fan closes and the Peek preview follows the finger along the rail; lifting there pins it (tap to go); cancel hides it; lifting on the icon just closes the fan.
The first-time hint mentions it. Export `staging39-before-peek-nav-20261108-074527.sql`; snapshot `peek-nav-before-20261108-074540-f4aa4369.json`;
check `ui-peek-nav-staging39-20261108-14`: **310/0**. Note: the second Kiro review handoff predates this (it says 282).
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-074540-f4aa4369.json'`.

**Third Kiro review (2026-11-08):** Kiro reviewed #1360 (before Ryan's slide gesture); Ryan: "I prefer my slide from icon idea to kiro's so choose mine."
Finding 1 (staging not serving the Peek) not reproduced: the server check found it served anonymously; Kiro's load most likely fell in a roll-back-then-apply
swap. Fixed: hint focus (no auto-hide while focused; focus to the rail on hide), hint after 1.5 s and not on #hash arrivals, plain in-page links below the
header+strip (scroll-margin + capture handler), text clear of rail/icon by measurement (`clearRail()`, plus a block-measuring pass after staging39 showed a theme
block 4px under the rail), icon background transparent, admin-bar menu height, rove/fan/hint guards; installer: CRLF/umask were reported; the later 2026-11-09 source audit found rollback still ignored its saved hash and could delete CHANGED code (see current template record below).
Not taken: iOS left-edge (device first), header unpin below 600px (Ryan). Now snippet **#1363**; export `staging39-before-peek-nav-20261108-084155.sql`;
snapshot `peek-nav-before-20261108-084212-e58e7d70.json`; check `ui-peek-nav-staging39-20261108-16`: **326/0**; cleanup installer rehearsal passed on a copy of
staging38. Third handoff: `docs/inter-model/2026-11-08-peek-nav-kiro-review-3-handoff.md`.
**Lesson:** measure where text *can* reach (each block's content edge), not where it happens to wrap.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-084212-e58e7d70.json'`.

**Stacked index boxes + fourth Kiro review (2026-11-08):** Ryan: when an index box opens from the rail, show the closed boxes for the sections above and
below it (number + heading). Defaults chosen (Ryan did not answer): full page headings with "…", tapping a closed box opens it in the stack (no jump).
Fit to screen in steps (fit1 placeholder lines/"You are in", fit2 mini preview, fit3 description + compact rows). Kiro fixes: saved side before the first
measurement; rail/icon press = hint seen; clearRail scheduled (rAF, width change, content ResizeObserver), any width, theme padding kept when enough,
measured from the text blocks only (staging39 blocks are inset ~20px). Snippet **#1365**; export `staging39-before-peek-nav-20261108-102015.sql`;
snapshot `peek-nav-before-20261108-102027-0eb240af.json`; check **361/0**.
**Ops lesson:** on 2026-11-08 ~09:15-10:20 UTC GitHub release downloads (age) answered 504 on Actions runners; `install-base-tools.sh` now retries
(8 x 10 s); a failed rollback step leaves the old snippet, so the apply refuses (CHANGED) and nothing breaks, but a rollback that succeeds while the
apply is blocked leaves the page on the no-JavaScript row until the apply goes through.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-102027-0eb240af.json'`.

**Fifth Kiro review + title-row strip (2026-11-08):** Kiro's findings fixed in #1366 (staging39 376/0): fit()/place() measure from where the strip sticks and
within the card's max-height; fit4 folds far rows into "+N more above/below", fit5 keeps only the open box's name and "Tap to go there"; card scrolls as a last
resort; refit on rotation; focus to a newly opened box; rail clearance re-measured only on content width change. Then Ryan: drop the progress "scanning" at the
top and keep only topic titles sliding by, the current one centred with part of the neighbours on each side, in relative space. Snippet **#1367**: the strip is a
row of the section labels, current one bold in the middle, neighbours fading at the edges, sliding continuously toward the next title through the section; no bars,
no count. Export `staging39-before-peek-nav-20261108-164427.sql`; snapshot `peek-nav-before-20261108-164442-bbf19019.json`; check **388/0**.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-164442-bbf19019.json'`.

**Summary box, fan form, kept-open fan (2026-11-08, Ryan):** (1) the open box no longer has a second window inside (the mini page preview and the
one-line description are gone); instead a scrollable summary of the topic built only from the section's own sentences (regulated copy: nothing is
rewritten; `summarize(i)`: paragraphs and list items in order, no questions or fragments, up to ~480 characters or 5 sentences); dragging or tapping it
never jumps. Snippet #1368, staging39 check `ui-peek-nav-s39-29-1` **404/0**. (2) The fanned names from the index icon take the form of the stack's closed
boxes (stone, 10px corners, number in a light circle; the current one has the open box's green edge). (3) Held 2 s, the names stay open after lifting
(buzz + green ring on the icon): tap a name to jump, touch the rail for that box and its summary, tap anywhere else to close; names capped at 48vw so
about 40% of a phone's width stays free. Snippet **#1369**; snapshot `peek-nav-before-20261108-180339-b42020e0.json`; staging39 check `ui-peek-nav-s39-30-1` **428/0** (mock 428/0).
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-180339-b42020e0.json'`.
**Saved state (Ryan, before the next design):** `link-ideas/saved/20261108-1369/` (code copies of #1369 + restore steps); staging39 DB export `staging39-manual-20261108-182958.sql`, verified off-site. Hand-written summaries would need Ryan's (and Butsaba's) approval of new wording.

**Index icon scrolls the page; slide onto a name to keep it (2026-11-08, Ryan + session synthesis):** press the icon (names fan out); slide straight down
the rail = the page scrolls with the thumb (rail = whole page), only the current section's name shows (`.ts-pk-chip`, closed-box form); slide sideways
onto it (green) and lift = it stays; tap = its box with the summary; tap again = go there; lift on the rail = stay where you scrolled; tap elsewhere lets it
go. Slide from the icon onto a list name and lift = its box opens. 2 s hold still keeps the list; a tap on a name opens its box (not a jump). Starting on
the rail still previews without moving the page. Scrolling starts only on a clearly straight slide (24px into the rail, <12% sideways); leaving the rail
sideways first = picking a name (a diagonal to a far name passes over the wide rail). Snippet **#1370**; snapshot
`peek-nav-before-20261108-191607-4d74fdf9.json`; staging39 check `ui-peek-nav-s39-31-1` **456/0**. Saved state to go back to: `link-ideas/saved/20261108-1369/`.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-191607-4d74fdf9.json'`.
**Scroll fix (Ryan: "definitely issues with the scrolling"; chose "catch the dot"; "too fast / jumpy"):** the thumb always enters the rail at its top and
the rail top stood for the page top, so starting mid-page leapt back near the top. Now the page waits until the thumb reaches the reading dot (it grows,
turns green), then follows; the page glides toward the thumb (22% of the distance per frame, capped at 12% of the screen) instead of leaping. Snippet
**#1371**; snapshot `peek-nav-before-20261108-195800-9df0c12b.json`; staging39 check `ui-peek-nav-s39-32-1` **464/0**.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-195800-9df0c12b.json'`.
**Scroll dropped (Ryan: "The scrolling is wonky" ... "Get rid of the scroll idea."):** sliding from the icon straight down onto the rail is the rail
preview again (page still); kept: slide from the icon onto a name and lift = its box; 2 s hold keeps the list and a tap on a name opens its box; the
preview takes over only on a clearly straight slide down (a diagonal to a far name crosses the wide rail). Lesson: a page scrub from a short rail fights
the phone's own scrolling (absolute mapping leaps, easing lags) and duplicates a normal swipe, which already shows the section name beside the rail.
Snippet **#1372**; snapshot `peek-nav-before-20261108-202316-cf244515.json`; staging39 check `ui-peek-nav-s39-33-1` **444/0**.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-202316-cf244515.json'`.
**Short-idea summaries, "etc.", no "You are in" (2026-11-08, Ryan):** each box lists the section's ideas as ~3-word phrases (GISTS by heading id; merged,
no repeats in or across boxes; "etc." when a list holds ~60% or less; written from the rendered page via `dump-sections.mjs`; wording_tiers clean; for
Ryan's approval before live); sections without a list fall back to whole opening paragraphs (`summarize()`, after Kiro found the sentence splitter cut
"1.5"/"U.S."/quotes). Fan names size to the rail spacing. fit() now subtracts the card border. Staging39: #1375 lists (457/0), #1376 "etc." .
**Template:** Ryan: this navigation (rail + read-only strip) is the template for every page. Kiro template-review handoff:
`docs/inter-model/2026-11-08-peek-nav-kiro-template-review-handoff.md`.
Staging39 now snippet **#1377** (no "You are in"; 461/0); snapshot `peek-nav-before-20261108-234541-8cf99e25.json`.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-234541-8cf99e25.json'`.
**Edge bug (Ryan, 2026-11-09, larger iPhone, rail right):** padding `.entry-content` moved full-width Spectra blocks by half the padding (text cut at the left
edge on the right side; page 26px too wide on the left side). Fix: clearance on text blocks + strip, never the column. Lesson: never pad a column that
holds full-width blocks; check `scrollWidth === clientWidth` and text edges on both sides. Staging39 #1378 (469/0); `probe-overflow.mjs` clean at 375/402/440.
**Kiro template review outcomes:** page-keyed GISTS, non-heading targets (FAQ panels open), row validated and sorted, focus margin on content focusables
(page scroll-padding scrolled to top when the header's menu got focus), quick tap keeps the fan, strip opacity .85. Mock 477/0. Next: shared core + per-page
data + gist lint (Kiro's structure); Ryan to decide Kiro's safer phrases.
**Fan names pushed in (Ryan, 2026-11-09, both phones):** the text-clearance rule matched the navigation's own `li`s (fan, rail, summary bullets); now
excludes `.ts-pk`/`.ts-jump`. Kiro's wording applied (hedged research phrases; dated Questions lines dropped). Staging39 **#1380**; snapshot
`peek-nav-before-20261109-010037-0c493281.json`.
**Historical undo (not for the current shared core):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261109-010037-0c493281.json'`.
**Lesson (swaps):** during "rollback, rehearse, apply" (~6 min) the page shows the plain no-JavaScript row; tell Ryan not to judge a reload until the
apply line appears.


## Shared template continuation (2026-11-09, Codex)

Fresh rendered review and starting #1380 inspection completed; legacy code hash matched
`b7314c8938a728bf64b6dac16c48bba722b4aea59b277c9f56f0760132322c01`.
Implementation separates shared core and WordPress-stored `ts_pk_pages` page data;
`.ts-jump` stays the sole navigation source. Thai wording is unchanged. Therapeutic/Deep Tissue/Chair lists were reviewed against fresh rendered text
and subsequently approved by Ryan in chat. All four service pages are now deployed
on staging39: core **#1380**, Thai data **#1381**, Therapeutic **#1382**, Deep Tissue
**#1383**, Chair **#1384**. Their actual snapshots, per-page evidence and final
aggregate verification are tracked in [the service rollout record](../../docs/reviews/2026-11-09-peek-service-rollout.md).
Any unapproved data is still stripped from public JSON. Native details, reordered
row mapping and print have focused coverage. Legacy rollback hash protection was
missing despite the older note above; the new installer checks complete state,
and push rollback now takes a verified fresh off-site backup.

**Original Thai-only migration evidence (before the three-page rollout):**
Offline gates and both installer rehearsals passed. Core rehearsal must include
`--with-uploads`: omitting layout assets caused three geometry failures; the
unchanged code/assertions passed with uploads. Data/core writes each verified a
fresh encrypted off-site export and snapshot; both exports restored (import 0).
Core restore differences are only #1380/WPCode cache. Broad checks passed except
the two expected Thai pixel views versus live (18/20 identical). Fresh public
Chromium checks passed **74/0 manifest + 485/0 Thai regression** (42552255911).
Six before/after images are byte-identical; all five rendered section captures
and measured geometry match (42552270461). Final stored #1380/#1381 hashes
confirmed (42553163541). Design tradeoffs remain the hint's hero obstruction,
scrolling name bubble over text, and research's large rail share; no redesign
was bundled into migration.

**Completed service rollout:** public checks passed at each rollout stage:
Therapeutic **168/0**, Deep Tissue **190/0**, Chair **232/0**. All 27 public captures
are saved with comparison records. Card/content pixels match their rehearsals;
some Deep Tissue/Chair rail pixels differ. The cause was not isolated; timing
remains a hypothesis. Exact item checks and research-start/end captures do
not claim visual review of every intermediate scroll position.

**Shared placement defect observed during the data rollout:** Chair's first card
overlapped the initial display-only strip; at 768px its focus ring grazed the Corporate CTA. Header,
card and section controls remained accessible in the observed states. That core
and checker used the strip's eventual sticky position, so passing bounds checks
did not establish actual-strip clearance. The later correction is recorded below;
no redesign was bundled into the data rollout.

**Clearance/touch correction deployed; apply job finished 2026-11-09 07:15:10 UTC:**
[apply run 42577650950](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42577650950)
updated #1380 in place to 71,321 code bytes, SHA256
`357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8`.
Actual strip/header clearance, kept-summary resize state and closed-control touch
ownership passed the fresh-copy gates before deployment. All four page-data
records and approved wording remain unchanged; no fallback-only interval occurred.
Fresh export `staging39-before-peek-nav-20261109-071106.sql` and actual snapshot
`peek-template-core-upgrade-before-20261109-071122-958bff3b.json` were verified
encrypted off-site; restore import returned 0, with only #1380/WPCode-cache differences.
[Stored inspection 42578355744](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42578355744)
passed at 07:17:32 UTC: #1380 matches the exact hash/bytes above and all four data
hashes are unchanged. [Public run 42578241317](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42578241317)
finished 07:17:03 UTC **INVALID HTTP202** on the initial Thai request, with zero
assertions/captures and no retry queued. The
[preceding clearance/touch record](../../docs/reviews/2026-11-09-peek-clearance.md)
holds the full evidence. Chromium's native-touch baseline on the old core did not
reproduce Ryan's reported page scroll; physical-device resolution remains unverified.

**Short-summary follow-up deployed: written 07:59:57 UTC; apply job finished 08:04:07 UTC.**
Ryan reports that closed controls and long summaries work, but short open
summaries still pan the page. The correction measures card/summary overflow
independently, allowing native panning only for an element with its own scroll
range. Approved wording/layout and the working scroll paths stay unchanged.
Source `659c251a8`: 71,826 code bytes, SHA256
`a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac`.
Rehearsal 42580943410 passed at 07:54:50 UTC: clearance **3340/0** and pages
**207/0** in each engine, native Chromium touch **266/0**, Thai **485/0**, exact
rollback/reapply and rendered wording gates. All 16 captures were reviewed;
nine match v2 byte for byte, seven differ only on the rail. Read-only preflight
42582374039 passed at 07:55:56 UTC.
[Apply 42582749743](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42582749743)
updated #1380 in place, preserving all four approved data records without a
fallback-only interval. Export `staging39-before-peek-nav-20261109-075943.sql`
and actual snapshot `peek-template-core-summary-touch-before-20261109-075957-5ef2bd29.json`
were verified encrypted off-site. Restore import returned 0; 104/104 fingerprints
differ only on #1380/WPCode cache. Origin scope and rendered wording checks passed;
six broad pixel differences versus live remain, so no aggregate post-change pass
is claimed. [Public run 42583371605](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42583371605)
passed at 08:08:52 UTC: clearance **3340/0** in each browser, native touch **266/0**,
pages **172/0**, and matched-capture subset **342/0**. All 16 common card/text/strip/banner
captures match rehearsal pixels; five images are byte-identical and eleven differ
only on the rail. [Stored inspection 42583499757](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42583499757)
passed at 08:09:31 UTC: exact #1380 hash/71,826 bytes and four unchanged data hashes.
Detailed visual review, full evidence and recovery are in the
[earlier record](../../docs/reviews/2026-11-09-peek-short-summary-touch.md).
The reported page-scroll leak was not reproduced by native Chromium probes;
Safari/physical-device resolution remains unverified.

**Previous deployed core: Kiro fixes, frozen v6; historical public/stored proof.**
The [fix and verification record](../../docs/reviews/2026-11-09-peek-kiro-fixes.md)
tracks corrected Privacy touch/Enter jumps, larger short-screen reading areas,
FAQ/text clearance, wrapped titles, shorter single-entry rails and opaque strips.
All-page rails remain; the four approved data sets are unchanged. Compact landscape
cards may cover the strip to preserve reading space, never the visible header;
this tradeoff was explained before implementation. No icon scrolling or broad
jump-engine refactor was added.

Apply **42697003089**, source `8f4a3fb415b73cfe8c81071419c6da428f5b032a`, wrote #1380
at **20:52:52 UTC** and finished **20:54:46 UTC, PASS**. Previous deployed code: **102,649
bytes**, SHA256 `dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1`.
Actions Gates passed **31/0 + 1 rendered-only skip**. Fresh-copy **42693471206**
passed **14,249/0 across 14 complete suites**, including native touch 387/0 and
Thai gestures 491/0. Twenty-two selected captures and the full 133-image manifest
are retained; twelve were freshly viewed, ten match earlier reviewed captures.
All 19 origin routes/scope/plain-row checks passed. Fresh export
`staging39-before-peek-nav-20261109-205236.sql` and actual snapshot
`peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json` were verified
encrypted off-site; export verification preceded the write. Restore import 0,
104/104 fingerprints differ only on #1380/cache. No fallback-only interval.
Automatic post-change checks were **INVALID HTTP202** at **20:54:27 UTC**, with
zero checks. A single later retry **42698232542** finished **21:03:22 UTC**:
both edge requests/all 12 HTTP checks returned 200; code/rendered wording/
posture/PHP passed and schema passed 27/27, zero parse errors. Overall **FAIL**
is 20/20 live pixel dimension differences, zero missing captures, not an
aggregate pass. All 20 deployed full-page captures were independently viewed,
with six v5 comparisons and verified hashes, without a new persistent layout/
image blocker. Existing transient labels and Contact blur/whitespace remain;
these closed-state views alone do not prove focused interactions.

Focused public **42699275642** finished **21:19:05 UTC, PASS**: **12,237/0 across
13 complete unique suites**, with no INVALID. Disclosure checks passed Chromium
66/0 and Firefox 41/0, layout 158/0 and 134/0, native touch 387/0; retained title,
page, clearance and matched-capture suites completed. Chair's native outer-card
swipe moved 0→21 px at scale 1/390×180/zero offset, with page/summary unchanged.

Independent audit verified all 33 streams, matching 12,237 literal/numeric
PASS counts, 16 empty stderr logs and 16 zero exits. The full inventory contains
123 decoded JPEGs. Fifteen of 24 selected public captures were freshly viewed;
nine exactly match earlier reviewed rehearsal captures. No new visible blocker
was found. Transient labels, approximate 150% root-text brand wrapping and one
closed Therapeutic tablet row's ellipsis remain documented in the fix record.

Stored inspection **42697818891** passed **20:57:33 UTC**: published #1380 matches
the exact then-deployed 102,649-byte/hash payload and all four published data records
retain their original identities, titles, bytes and full hashes.

This is historical v6 proof. First restore v6 through the current input-focus
snapshot above, with a fresh verified backup and complete state checks. Only
then use the older `core-kiro-fixes` snapshot to restore v5, followed by the
title-rail route to v4. Frozen endpoints retain their meaning. Practice recovery
uses its separate actual full-state `rail-input` snapshot, never the old installer.
Thai touch-focus-to-BODY and Rail Firefox Enter were caveats at this v6 stage;
see the follow-up deployment and verification status above. Safari, physical
devices, OS text scale and real assistive technology remain unverified.
Rapid-switch delay was not reproduced or claimed fixed. Main/live untouched.

**Previous baseline: title-only rails, frozen shared v5; public/stored checks passed.**
The [title-rail record](../../docs/reviews/2026-11-09-peek-title-rails.md) holds the
actual deployment and failed-attempt history. Shared rails use existing jump
labels/targets, otherwise visible H2 headings or one page-title entry. No new
summaries, IDs or hashes were added; four approved data sets and settled gestures
remain unchanged. Rail/Index practice rails are preserved. Progress A–D uses the
shared rail; E hides it and releases its rail padding while the demo rail is active.

Apply **42666333809** wrote #1380 at **17:37:37 UTC** and finished **17:41:06 UTC,
PASS**: **97,130 code bytes**, SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.
Actions Gates passed **29 PASS / 1 SKIP / 0 FAIL / 0 INVALID**; fresh-copy rehearsal
**42662818962** passed **13,836/0** across ten suites. All 40 actual title-rail
captures were compared/reviewed. Nineteen origin scope checks and rendered
wording/posture/pages/PHP/schema passed. The broad live comparison had 20/20
pixel differences, not an aggregate pass. All 20 actual deployed full-page views
were reviewed without a new persistent layout blocker. Public **42667176429**
finished **17:49:09 UTC, PASS**: **11,829/0** across nine unique suites, with 21
actual routes in both title matrices/engines. Stored **42667209210** passed at
**17:49:42 UTC**: published #1380 exactly matches the bytes/hash above and all
four data records retain their original bytes/hashes. That previous record tracks
full suite counts and final capture/log review. Safari, physical devices and
actual assistive technology remain unverified. Thai touch-focus loss
and Rail Firefox Enter were historical v5 caveats; see the follow-up above. Practice pointer
preview/Go passed. The reported rapid-switch/loading delay was not reproduced
or claimed fixed.

Fresh export `staging39-before-peek-nav-20261109-173723.sql` and actual snapshot
`peek-template-core-title-rail-before-20261109-173737-cae68f35.json` were verified
encrypted off-site. Restore import returned 0; all 104/104 fingerprints were
compared, with differences only on #1380/WPCode cache. No fallback-only interval
occurred. First reverse input-focus to v6, then Kiro-fixes to v5 above. Then,
after a fresh verified backup and complete ownership/state checks, use the
matching package and explicit
`core-title-rail --rollback $HOME/private-backups/peek-template-core-title-rail-before-20261109-173737-cae68f35.json`
arguments to restore v4. This previous payload is frozen as shared v5 and the
transition is pinned to v4→v5.

**Previous deployment: all-page title strip and summary space, frozen shared v4.** The all-page
title strip and larger usable summaries were written to #1380 at **10:24:52 UTC**;
apply run **42600617720** finished **10:28:23 UTC, PASS**. Short, no-heading,
unlisted and practice pages are included; search keeps its page title fixed.
Title-only pages receive no invented destinations, summaries or rail. The four
approved JSON files and data snippets remain unchanged. Distant closed rows fold
before summary space shrinks, with nearby controls at intermediate fits and
whole-paragraph fallbacks throughout. See the [earlier deployment/recovery record](../../docs/reviews/2026-11-09-peek-title-strip-all-pages.md)
and [summary-space evidence](../../docs/reviews/2026-11-09-peek-summary-space.md)
for exact fit behavior, full fresh-copy PASS, reviewed captures and 19 passing
origin scope checks. The reported loading delay was not reproduced.

Public run **42601203134** finished **10:30:14 UTC, INVALID HTTP202**: zero
assertions/routes/captures; the quick subset was skipped. That earlier attempt
provided no public browser verification, with no immediate retry queued. Stored inspection
**42601221631** passed **10:30:40 UTC**: #1380 has **86,567 bytes**, SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`,
and all four data identities/hashes are unchanged. The fresh export
`staging39-before-peek-nav-20261109-102438.sql` and actual snapshot
`peek-template-core-title-strip-before-20261109-102452-90630a29.json` were verified
encrypted off-site. Restore import returned 0, with only #1380/cache differences.
The update was in place with no fallback-only interval. Its payload is now
frozen as `peek-nav-shared-v4.php`; `core-title-strip` pins exact v3→v4, preserving
the actual snapshot. The later title-rail update uses its own exact transition.

Original core snapshot: `peek-template-core-before-20261109-023645-fe38e498.json`.
Original Thai data snapshot: `peek-template-page-thai-massage-before-20261109-023133-3e7b1cf5.json`.
After a fresh verified backup and current full-state checks, and after reversing
input-focus to v6, Kiro-fixes to v5 and title-rail to v4, use the matching package with explicit
`core-title-strip --rollback $HOME/private-backups/peek-template-core-title-strip-before-20261109-102452-90630a29.json`
arguments to restore v3. Then reverse `core-summary-touch` with its own actual
snapshot to restore v2. Then `core-upgrade --rollback FILE` restores v1 from frozen v2;
the original `core` migration remains tied to frozen `peek-nav-shared-v1.php`.
Reversing that original migration affects
every page using Peek. For one service page,
use only its actual page-data snapshot in the service rollout record, leaving the
core and other pages active. Under the current core, that page keeps a title-only
rail and strip; its native jump row remains available without JavaScript.
**All earlier rollback commands in this note are historical.** Do not use
`peek-nav-before-20261109-010037-0c493281.json` to delete the shared core.
Migration installed inert Thai data before changing #1380 in place, with no
fallback-only interval. Main/live remain unchanged.
Ongoing phase: [input and keyboard focus](../../docs/reviews/2026-11-09-peek-input-focus.md).
Current deployment: [input and keyboard focus](../../docs/reviews/2026-11-09-peek-input-focus.md).
Previous core: [Kiro fixes, frozen v6](../../docs/reviews/2026-11-09-peek-kiro-fixes.md).
Previous baseline: [title-only rails](../../docs/reviews/2026-11-09-peek-title-rails.md).
Earlier baseline: [all-page title strip](../../docs/reviews/2026-11-09-peek-title-strip-all-pages.md).
Earlier baseline: [short-summary touch correction](../../docs/reviews/2026-11-09-peek-short-summary-touch.md).
Earlier core correction: [clearance/touch record](../../docs/reviews/2026-11-09-peek-clearance.md).
Earlier page-data rollout: [service rollout record](../../docs/reviews/2026-11-09-peek-service-rollout.md).
Original core migration/recovery: [template record](../../docs/reviews/2026-11-09-peek-template.md).
Approved wording: [three-page review](../../docs/reviews/2026-11-09-peek-summary-drafts.md).
Services/short-page title rails follow the deployed scope above; new summaries
still require wording approval. The title strip includes every visitor page.
Safari, physical devices and actual assistive technology remain untested.

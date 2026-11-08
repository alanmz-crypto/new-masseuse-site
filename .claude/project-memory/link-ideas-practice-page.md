---
name: link-ideas-practice-page
description: 2026-11-08 practice page /in-page-link-ideas/ on staging39 only, with five working replacements for the "On this page" link row; nothing chosen yet; never live as is
metadata:
  type: project
---

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
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-031625-f507cb73.json'`.

**Kiro review (2026-11-08):** Kiro (web) reviewed `peek-nav.php` by reading it; outcomes per finding in `docs/inter-model/2026-11-08-peek-nav-kiro-review-response.md`.
Now snippet **#1351** (replaced #1350). Export `staging39-before-peek-nav-20261108-040148.sql`; snapshot `peek-nav-before-20261108-040204-32ffad9a.json`;
check `ui-peek-nav-staging39-20261108-4`: **167/0** (first attempt INVALID 202, one retry). Waiting for Ryan: left-hand option, desktop hover preview, first-time hint.
**Lessons:** after a pointer-captured drag no click follows, so "was this click from a press" must be a short time window, not a flag; a tap right after a
gesture can be dropped before it becomes a click, so act on a short still `pointerup` for touch and keep `click` for mouse/keyboard.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-040204-32ffad9a.json'`.

**Left-hand option + first-time hint + second Kiro review (2026-11-08):** Ryan: "Yes to left-hand option and first-time hint". Now snippet **#1353**
(replaced #1351; #1352 lived ten minutes and was replaced for a theme underline found by the new computed-style check). Export
`staging39-before-peek-nav-20261108-050659.sql`; snapshot `peek-nav-before-20261108-050713-0f6a1da8.json`; check `ui-peek-nav-staging39-20261108-7`: **223/0**.
Details per finding: `docs/inter-model/2026-11-08-peek-nav-kiro-review-response.md`. Desktop hover still open.
**Lessons:** commit messages with double quotes break `git commit -m "..."` in a one-line command: write the message to a file and use `-F`; when that
commit failed, `request.sh` still pushed and ran the previous commit (harmless here: "Already applied"). A resumed cloud session may have Docker stopped:
`sudo -n dockerd &` brings back the three Docker gates of `check-all.sh`.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-050713-0f6a1da8.json'`.

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
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-061058-bfb0e317.json'`.
**Row removed (2026-11-08):** Ryan: "You can remove the in-page links beneath header." Now snippet **#1356**: with the runtime ready (`html.ts-pk-ready`) the
server-rendered `.ts-jump` row is `display:none` and the strip sits in its place; without JavaScript the row still shows and works (checked with JavaScript
off). The row stays in the HTML as the source of the section list. Export `staging39-before-peek-nav-20261108-062336.sql`; snapshot
`peek-nav-before-20261108-062353-1c40c41c.json`; check `ui-peek-nav-staging39-20261108-9`: **272/0**.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-062353-1c40c41c.json'`.
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
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-065841-c543ce54.json'`.
**Menu flicker fixed (2026-11-08):** Ryan: "When i open the hamburger menu on this page it flickers, now." Cause (reproduced on staging39 by a new 40-frame check:
header 328/421px alternating): the open Astra phone menu sits inside `#masthead`; `--pk-head` measured the whole header, and the menu's `max-height` used
`--pk-head`, so each set the other. Now only the header bar is measured (header minus `.ast-mobile-header-content`). Snippet **#1360**; export
`staging39-before-peek-nav-20261108-072416.sql`; snapshot `peek-nav-before-20261108-072428-8b5dda0b.json`; check `ui-peek-nav-staging39-20261108-13`: **282/0**
(menu steady at 421/336, `--pk-head` 85px). **Lesson:** never size a child from a measurement of a parent that contains it.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-072428-8b5dda0b.json'`.
**Index-to-rail slide (2026-11-08):** Ryan: "if you press the index, it behaves as you have created it. However, if you press the index and then slide to the rail
without lifting your finger, then the rail options open up." Snippet **#1361**: the icon keeps the pointer (capture); once the finger crosses the rail's top edge the
fan closes and the Peek preview follows the finger along the rail; lifting there pins it (tap to go); cancel hides it; lifting on the icon just closes the fan.
The first-time hint mentions it. Export `staging39-before-peek-nav-20261108-074527.sql`; snapshot `peek-nav-before-20261108-074540-f4aa4369.json`;
check `ui-peek-nav-staging39-20261108-14`: **310/0**. Note: the second Kiro review handoff predates this (it says 282).
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-074540-f4aa4369.json'`.

**Third Kiro review (2026-11-08):** Kiro reviewed #1360 (before Ryan's slide gesture); Ryan: "I prefer my slide from icon idea to kiro's so choose mine."
Finding 1 (staging not serving the Peek) not reproduced: the server check found it served anonymously; Kiro's load most likely fell in a roll-back-then-apply
swap. Fixed: hint focus (no auto-hide while focused; focus to the rail on hide), hint after 1.5 s and not on #hash arrivals, plain in-page links below the
header+strip (scroll-margin + capture handler), text clear of rail/icon by measurement (`clearRail()`, plus a block-measuring pass after staging39 showed a theme
block 4px under the rail), icon background transparent, admin-bar menu height, rove/fan/hint guards; installer: CRLF, rollback stops on CHANGED, umask.
Not taken: iOS left-edge (device first), header unpin below 600px (Ryan). Now snippet **#1363**; export `staging39-before-peek-nav-20261108-084155.sql`;
snapshot `peek-nav-before-20261108-084212-e58e7d70.json`; check `ui-peek-nav-staging39-20261108-16`: **326/0**; cleanup installer rehearsal passed on a copy of
staging38. Third handoff: `docs/inter-model/2026-11-08-peek-nav-kiro-review-3-handoff.md`.
**Lesson:** measure where text *can* reach (each block's content edge), not where it happens to wrap.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-084212-e58e7d70.json'`.

**Stacked index boxes + fourth Kiro review (2026-11-08):** Ryan: when an index box opens from the rail, show the closed boxes for the sections above and
below it (number + heading). Defaults chosen (Ryan did not answer): full page headings with "…", tapping a closed box opens it in the stack (no jump).
Fit to screen in steps (fit1 placeholder lines/"You are in", fit2 mini preview, fit3 description + compact rows). Kiro fixes: saved side before the first
measurement; rail/icon press = hint seen; clearRail scheduled (rAF, width change, content ResizeObserver), any width, theme padding kept when enough,
measured from the text blocks only (staging39 blocks are inset ~20px). Snippet **#1365**; export `staging39-before-peek-nav-20261108-102015.sql`;
snapshot `peek-nav-before-20261108-102027-0eb240af.json`; check **361/0**.
**Ops lesson:** on 2026-11-08 ~09:15-10:20 UTC GitHub release downloads (age) answered 504 on Actions runners; `install-base-tools.sh` now retries
(8 x 10 s); a failed rollback step leaves the old snippet, so the apply refuses (CHANGED) and nothing breaks, but a rollback that succeeds while the
apply is blocked leaves the page on the no-JavaScript row until the apply goes through.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-102027-0eb240af.json'`.

**Fifth Kiro review + title-row strip (2026-11-08):** Kiro's findings fixed in #1366 (staging39 376/0): fit()/place() measure from where the strip sticks and
within the card's max-height; fit4 folds far rows into "+N more above/below", fit5 keeps only the open box's name and "Tap to go there"; card scrolls as a last
resort; refit on rotation; focus to a newly opened box; rail clearance re-measured only on content width change. Then Ryan: drop the progress "scanning" at the
top and keep only topic titles sliding by, the current one centred with part of the neighbours on each side, in relative space. Snippet **#1367**: the strip is a
row of the section labels, current one bold in the middle, neighbours fading at the edges, sliding continuously toward the next title through the section; no bars,
no count. Export `staging39-before-peek-nav-20261108-164427.sql`; snapshot `peek-nav-before-20261108-164442-bbf19019.json`; check **388/0**.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-164442-bbf19019.json'`.

**Summary box, fan form, kept-open fan (2026-11-08, Ryan):** (1) the open box no longer has a second window inside (the mini page preview and the
one-line description are gone); instead a scrollable summary of the topic built only from the section's own sentences (regulated copy: nothing is
rewritten; `summarize(i)`: paragraphs and list items in order, no questions or fragments, up to ~480 characters or 5 sentences); dragging or tapping it
never jumps. Snippet #1368, staging39 check `ui-peek-nav-s39-29-1` **404/0**. (2) The fanned names from the index icon take the form of the stack's closed
boxes (stone, 10px corners, number in a light circle; the current one has the open box's green edge). (3) Held 2 s, the names stay open after lifting
(buzz + green ring on the icon): tap a name to jump, touch the rail for that box and its summary, tap anywhere else to close; names capped at 48vw so
about 40% of a phone's width stays free. Snippet **#1369**; snapshot `peek-nav-before-20261108-180339-b42020e0.json`; staging39 check `ui-peek-nav-s39-30-1` **428/0** (mock 428/0).
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-180339-b42020e0.json'`.
**Saved state (Ryan, before the next design):** `link-ideas/saved/20261108-1369/` (code copies of #1369 + restore steps); staging39 DB export `staging39-manual-20261108-182958.sql`, verified off-site. Hand-written summaries would need Ryan's (and Butsaba's) approval of new wording.

**Index icon scrolls the page; slide onto a name to keep it (2026-11-08, Ryan + session synthesis):** press the icon (names fan out); slide straight down
the rail = the page scrolls with the thumb (rail = whole page), only the current section's name shows (`.ts-pk-chip`, closed-box form); slide sideways
onto it (green) and lift = it stays; tap = its box with the summary; tap again = go there; lift on the rail = stay where you scrolled; tap elsewhere lets it
go. Slide from the icon onto a list name and lift = its box opens. 2 s hold still keeps the list; a tap on a name opens its box (not a jump). Starting on
the rail still previews without moving the page. Scrolling starts only on a clearly straight slide (24px into the rail, <12% sideways); leaving the rail
sideways first = picking a name (a diagonal to a far name passes over the wide rail). Snippet **#1370**; snapshot
`peek-nav-before-20261108-191607-4d74fdf9.json`; staging39 check `ui-peek-nav-s39-31-1` **456/0**. Saved state to go back to: `link-ideas/saved/20261108-1369/`.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-191607-4d74fdf9.json'`.
**Scroll fix (Ryan: "definitely issues with the scrolling"; chose "catch the dot"; "too fast / jumpy"):** the thumb always enters the rail at its top and
the rail top stood for the page top, so starting mid-page leapt back near the top. Now the page waits until the thumb reaches the reading dot (it grows,
turns green), then follows; the page glides toward the thumb (22% of the distance per frame, capped at 12% of the screen) instead of leaping. Snippet
**#1371**; snapshot `peek-nav-before-20261108-195800-9df0c12b.json`; staging39 check `ui-peek-nav-s39-32-1` **464/0**.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-195800-9df0c12b.json'`.
**Scroll dropped (Ryan: "The scrolling is wonky" ... "Get rid of the scroll idea."):** sliding from the icon straight down onto the rail is the rail
preview again (page still); kept: slide from the icon onto a name and lift = its box; 2 s hold keeps the list and a tap on a name opens its box; the
preview takes over only on a clearly straight slide down (a diagonal to a far name crosses the wide rail). Lesson: a page scrub from a short rail fights
the phone's own scrolling (absolute mapping leaps, easing lags) and duplicates a normal swipe, which already shows the section name beside the rail.
Snippet **#1372**; snapshot `peek-nav-before-20261108-202316-cf244515.json`; staging39 check `ui-peek-nav-s39-33-1` **444/0**.
**Undo (current):** `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261108-202316-cf244515.json'`.
**Short-idea summaries, "etc.", no "You are in" (2026-11-08, Ryan):** each box lists the section's ideas as ~3-word phrases (GISTS by heading id; merged,
no repeats in or across boxes; "etc." when a list holds ~60% or less; written from the rendered page via `dump-sections.mjs`; wording_tiers clean; for
Ryan's approval before live); sections without a list fall back to whole opening paragraphs (`summarize()`, after Kiro found the sentence splitter cut
"1.5"/"U.S."/quotes). Fan names size to the rail spacing. fit() now subtracts the card border. Staging39: #1375 lists (457/0), #1376 "etc." .
**Template:** Ryan: this navigation (rail + read-only strip) is the template for every page. Kiro template-review handoff:
`docs/inter-model/2026-11-08-peek-nav-kiro-template-review-handoff.md`.


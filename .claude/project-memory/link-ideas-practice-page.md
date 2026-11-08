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

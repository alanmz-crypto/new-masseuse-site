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

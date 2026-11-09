# Handoff for Kiro: template review of the Peek navigation (before it goes on every page)

Written 2026-11-08 by the Claude Code cloud session, for Ryan to hand to Kiro (web). **Review only:** find flaws and propose enhancements; do not deploy
anything and do not change any website (section 8).

**Why this round is different.** Ryan, 2026-11-08: everything built for the Thai Massage page will be the **template for every other page on the site**.
This includes the side rail, its index icon and summary boxes, and the read-only title strip at the top. So this is not a review of the latest change; it
is a **thorough assessment of the whole design as a sitewide template**:
- correctness;
- how it carries to pages it was not built on;
- accessibility, performance and the regulated copy;
- maintainability, test coverage and the rollout plan.

Earlier rounds, for history only (all in this folder): `2026-11-08-peek-nav-kiro-review-handoff.md` (first), `…-review-response.md`,
`…-review-2-handoff.md`, `…-review-3-handoff.md`, `…-review-4-handoff.md`, `…-review-5-handoff.md` (its addendum has the outcomes of your sixth review).

## 1. Where things stand

Branch **`claude/wizardly-bell-xu0rta`**, commit `ebc273f` or later. Nothing is on `main`; live thaisolitude.com is untouched.

- **Code:** one WPCode snippet, `scripts/work-with-me-employer/link-ideas/peek-nav.php` (582 lines). It has CSS in `wp_head` and JavaScript in `wp_footer`,
  and is gated by `is_page( 'thai-massage' )`.
- **Where it runs:** applied on staging39 only, as the latest "TSPeekNav practice" snippet (see the newest `apply-peek-nav-s39-*` report on the `ops-log`
  branch): https://staging39.thaisolitude.com/thai-massage/
- **Browser check:** `link-ideas/check-peek-nav.mjs` (426 lines) gives **461 passed, 0 failed** on the mock. The same version passed on staging39 at each
  swap today; the newest `ui-peek-nav-s39-*` report has the current run.
- **Installer:** `install-peek-nav.php` and `push-peek-nav.sh` run it through GitHub Actions. There is one snippet, an exact before-snapshot, rollback,
  and an allow-list of localhost:8090 and staging39.
- **Saved known-good copy:** `link-ideas/saved/20261108-1369/`, from before the last rounds.
- **Mock:** `link-ideas/mock/build-mock.py`, a local Thai page with the real CSS/JS and an Astra-like header and menu.
- **Read-only tool:** `link-ideas/dump-sections.mjs` prints the rendered text of each section of a page, including FAQ answers added by another snippet.
- **Related, sitewide on staging39 only:** `link-ideas/install-sitewide-cleanup.php` removed the 3px reading-progress bar and Astra's scroll-to-top button.
  Both still exist on live.
- **Project rules:** `AGENTS.md` and `CLAUDE.md`. Design notes and every past decision are in `.claude/project-memory/link-ideas-practice-page.md`.

## 2. The design as it is now (what would become the template)

1. **Data source.** Each page already has a small row of in-page links, `.entry-content .ts-jump`, written by `page-navigation/jump-links.php` from a
   per-page plan (`$plans`, keyed by page slug).
   - The runtime reads its section list from that row, then hides the row once it is ready. The row stays in the HTML as the no-JavaScript fallback and
     as the source.
   - Headings are found by the links' `#id`.
2. **Top strip** (`.ts-pk-top`): read-only, `pointer-events:none`, `aria-hidden`. It sticks under the pinned site header.
   - It shows a row of the section titles: the current one bold in the middle, the neighbours fading at the edges.
   - It slides continuously toward the next title as you read.
3. **Side rail** (`.ts-pk-nav` / `.ts-pk-rail`): one piece per section, with height proportional to section length and a fill for reading progress. A dot
   and a name show while the page scrolls.
   - Sliding a thumb along it opens a **stack** of boxes while the page stays still. The open box is beside the finger; closed boxes ("number + full
     heading") show the sections above and below.
   - Lift to keep it; tap the open box to jump; tap a closed box to open it in place.
   - `fit1`-`fit5` drop detail until the stack fits the screen.
4. **The open box** has the section number and minutes, its name, a **summary**, "Tap to go there" and a left/right switch.
   - The summary is a **hand-written list of short ideas** per section (`GISTS`, keyed by heading id). Each distinct idea is reduced to about three words
     ("subject, verb, object"); overlapping ideas are merged; nothing is said twice in a box or across boxes. A list that holds about 60% or less of its
     section ends with "etc.".
   - It is new wording on a regulated page: checked with `client-pages/wording_tiers.py`, and for Ryan's approval before live.
   - A section without a list falls back to its own opening paragraphs, whole and in order (`summarize()`).
   - The summary scrolls, with a fade while there is more. There is no "You are in…" line (the strip says it).
5. **Index icon** above the rail, blended into the page:
   - **press** and every section name fans out beside its rail piece, in the closed-box form;
   - **slide onto a name and lift**: its box opens;
   - **slide straight down onto the rail**: the rail preview takes over, after 24px into the rail with under 12% sideways drift; leaving the rail
     sideways first means "pick a name" (`lean`);
   - **hold 2 s**: the names stay open; a tap on one opens its box; a tap elsewhere closes them and is swallowed.

   The names shrink to the rail pieces' spacing (`--pk-fan-h`, `is-tight`), and a slide picks the nearest name.
6. **Also part of it:**
   - the site header is pinned (`#masthead` sticky, at 501px of height or more);
   - a left-hand option (`ts-pk-side` in `localStorage`);
   - a first-time hint (`ts-pk-tip`);
   - `clearRail()`, which pads the text column so the text stops before the rail and icon;
   - in-page `#links` that land under the pinned header and strip;
   - the bottom section-guide button is hidden when the rail is present.
7. **Tried and dropped:** a page scroll from the icon (Ryan: "wonky"). Pills, a top-bar scrub, and sentence-cut summaries were also tried and dropped.
   Ryan's settled choices are listed in section 8.

## 3. What a template must survive (please assess each)

1. **Other pages' structure.** Read `page-navigation/jump-links.php`'s `$plans` and the pages on staging39 (read-only browsing is fine):
   - Home, About, Services (seven destinations), Therapeutic, Deep Tissue, Chair, Corporate & Group, Work With Me, and the legal pages;
   - pages with 2 sections, and one with 7 or more;
   - a page whose row links to an id that is **not** an `h2` (a block, an FAQ item, a `details`);
   - a page whose first linked section is far down;
   - pages without a row;
   - pages using the `we/our` voice (Home, Services; see AGENTS.md "The site mixes voices").

   What breaks, and what should the runtime do on each? Should some pages not get it at all?
2. **Moving from one page to many.** Today everything is in one snippet gated to one slug, with `GISTS` hard-coded in the JavaScript.
   - Propose the smallest sound structure for many pages: a slug allow-list and per-page `GISTS`, for example in the same `$plans` or a data attribute
     emitted by PHP.
   - Should the CSS/JS be one shared snippet with per-page data, or one snippet per page?
   - How do the installer, its rollback and the check scale?
   - How are the ids kept in step between `jump-links.php` and `GISTS`?
3. **The summary lists as content.**
   - Are they accurate to their sections? Use `dump-sections.mjs` on staging39 or read the page.
   - Does any phrase read as an outcome claim or as treating a condition? (OAR 334-010-0025; the research box is the sensitive one: "Reviews reported less
     pain", "Massage beat sham devices".)
   - Do they meet Ryan's rules (about three words, merged, no repeats, "etc." at or under about 60%)?
   - Propose a written procedure, or a tool, to produce and lint them for each new page, so the next author follows the same rules.
4. **Robustness.**
   - Theme changes: Astra settings, header layouts (desktop dropdowns, search, a cart), a sticky-header plugin.
   - The admin bar, RTL, and very long section titles.
   - Translated pages (not used today), print styles, and `prefers-reduced-motion`.
   - Dark mode (`html[data-ts-theme="dark"]`).
   - `localStorage` blocked.
   - A page with no JavaScript.
   - A second copy of the snippet loaded by mistake.
5. **Interaction, end to end.** The rail gesture, the index press (four outcomes in one gesture), the kept list, the stack's taps (`activate`, the swallowed
   clicks, `cardTapAt`), keyboard (roving tabindex on the rail, Enter, Escape, focus return), and the first-time hint. Look for:
   - state that can get stuck (a card that won't close, a list that stays open, a page that won't scroll);
   - races between `pointerup` and `click`;
   - multi-touch;
   - iOS Safari specifics: viewport resizing while scrolling, `-webkit-touch-callout`, long press, `color-mix()`, `mask-image`, the left-edge back swipe.
6. **Accessibility.**
   - What do VoiceOver and TalkBack hear and do on the strip (hidden), the rail (a list of links with descriptions), the icon (`aria-expanded`), the fan
     (`aria-hidden` until open), and the card (`tabindex=-1`, the region, the buttons)?
   - Is there a keyboard path to everything a finger can do?
   - Contrast of the faded strip titles, the muted icon and the closed boxes, in light and dark.
   - Target sizes.
   - WCAG 2.2: 2.5.7 Dragging Movements, 2.5.8 Target Size, 2.4.11 Focus Not Obscured (the pinned header and strip).
7. **Performance.**
   - Work per scroll frame (`draw()`), per rail move (`show`, `fit`, `place`), and on resize (`clearRail` with its ResizeObserver).
   - Layout thrash; long pages (the research section has 31 sources).
   - The cost of the CSS on pages that do not use it.
8. **The read-only strip.** Is a non-interactive, `aria-hidden` strip that moves continuously acceptable (motion, distraction, WCAG 2.2.2 Pause, Stop, Hide)?
   Does it ever disagree with the rail? It centres the next title halfway through a section, while the rail marks the section you are in.
9. **The browser check as a template test.**
   - What does `check-peek-nav.mjs` not cover that a sitewide rollout needs (other pages, 2-section and 7-section pages, pages without a row, a
     no-JavaScript run for each page)?
   - Is anything in it tied to the Thai page that should become data?
   - Any check that passes for the wrong reason?
10. **Rollout and safety.**
    - The order of pages.
    - What must be true before live: Ryan's go, his approval of each page's summary lists, the progress bar and scroll-to-top still existing on live, and
      Safari/real-device testing, which nobody has done yet.
    - How to roll back one page without the others.
    - Whether the pinned header and the sitewide cleanup should go with it.

## 4. Testing

```
cd scripts/work-with-me-employer/link-ideas/mock
python3 build-mock.py && python3 -m http.server 8090 -d site &
TS_PLAYWRIGHT_ROOT=<path to a playwright package.json> node ../check-peek-nav.mjs http://localhost:8090   # expect 461 passed, 0 failed (several minutes)
```
For a real page, browse staging39 read-only, or run `node dump-sections.mjs https://staging39.thaisolitude.com /services/` locally if your network reaches
it. SiteGround sometimes answers a bot challenge (HTTP 202): retry later, and do not hammer it.

## 5. What to deliver

1. **Flaws**, most severe first. For each: file:line, how to reproduce, and the smallest fix. Say which you reproduced and which are from reading only.
2. **Template blockers:** what must change before a second page gets the design.
3. **A proposed template structure** (section 3.2), with the per-page data format and how the installer and check scale.
4. **A per-page readiness table** for every page that has a `.ts-jump` row: sections, ids found or missing, anything special, and whether it is ready.
5. **Enhancements**, separated from flaws.

## 6. Known open decisions (not bugs)

- Desktop behaviour.
- A slimmer rail, to give the text column width back on phones.
- Unpinning the header on short portrait screens.
- The iOS left-edge back swipe.
- Live still has the progress bar and the scroll-to-top button.
- Safari and real devices are untested.
- The summary lists need Ryan's (and possibly Butsaba's) approval before live.

## 7. Files to read first

`link-ideas/peek-nav.php` (all of it), `page-navigation/jump-links.php`, `link-ideas/check-peek-nav.mjs`, `link-ideas/install-peek-nav.php`,
`link-ideas/push-peek-nav.sh`, `client-pages/wording_tiers.py`, `AGENTS.md` ("Regulated advertising (OBMT)" and "The site mixes voices"), and
`.claude/project-memory/link-ideas-practice-page.md`.

## 8. Hard rules

- **Never edit `ops/request.json` or `ops/live-request.json`, and never run `scripts/ci/request.sh`.** A push that changes `ops/request.json` on any branch
  runs real commands on the SiteGround server.
- Never push to, merge into or open pull requests against `main`. Never force-push `claude/wizardly-bell-xu0rta`.
- No page-copy changes (regulated wording, `AGENTS.md`). No plugins.
- **Ryan's settled choices; propose changes to details, not replacements:**
  - the rail and its stacked boxes;
  - the read-only title strip;
  - the index icon: press, slide onto a name, the 2 s hold;
  - the short-idea summary lists with "etc.";
  - no page scroll from the icon;
  - no "You are in…" in the box.
- Changes, if any, go on your own branch from `claude/wizardly-bell-xu0rta`, with a pull request into it and checks in `check-peek-nav.mjs`. A report alone is
  the main deliverable this time.

## 9. Paste-ready prompt for Kiro

> Read `docs/inter-model/2026-11-08-peek-nav-kiro-template-review-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site`
> (pull the latest commit first) and follow it. This is a thorough assessment of the Peek navigation as the template for every page of the site, not a
> review of one change. Build the mock and run `check-peek-nav.mjs` (expect 461 passed). Then assess everything in section 3: other pages' structure
> (`page-navigation/jump-links.php`), how to move from one page to many, the summary lists as regulated content, robustness, interaction, accessibility
> (WCAG 2.2), performance, the read-only strip, the browser check, and the rollout. Deliver what section 5 asks: flaws (most severe first, file:line,
> reproduction, smallest fix, reproduced or reading-only), template blockers, a proposed template structure, a per-page readiness table, then
> enhancements. Do not edit `ops/request.json` or `ops/live-request.json`, do not run `scripts/ci/request.sh`, and do not touch `main`. Changes, if
> any, go on a new branch from `claude/wizardly-bell-xu0rta` with a pull request into it.

## Addendum: outcomes of the template review (2026-11-09)

**A flaw Kiro did not report (Ryan, 2026-11-09, screenshot on a larger iPhone, rail on the right): text cut at the left screen edge.**
- `clearRail()` padded `.entry-content` on the rail's side. Full-width theme blocks (Spectra containers) are placed from the middle of the column, so
  they moved by half the padding:
  - with the rail on the right, off the left edge, so their text started off-screen;
  - with the rail on the left, past the right edge, so the page became 26px wider than the screen at every phone width (`probe-overflow.mjs` on staging39).
- **Fixed:** the clearance now goes on the text blocks and the strip, never on the column. The mock has a full-width block, and the check asserts that
  nothing sticks out on either side.
- On staging39 the page now matches the screen width at 375, 402 and 440px, on both sides, before and after jumps.

| # | Finding | Outcome |
| --- | --- | --- |
| 1 | Summary lists keyed by id only | **Fixed**: keyed by page (from its address), then id. Checked: the same page at another address shows no Thai lists. |
| 2 | Non-heading targets | **Fixed**: named by the heading or question inside; a jump opens a closed FAQ panel. Checked with an FAQ-panel target. |
| 3 | Row order / missing ids | **Fixed**: only existing targets, in page order; waits up to 1 s for ids assigned on load; fewer than 2 leaves the row. Checked. |
| 4 | Focus hidden under header + strip | **Fixed differently**: `scroll-margin-top` on the content's focusables. Page-wide `scroll-padding-top`, as suggested, made focusing the pinned header's menu button scroll the page to the top. |
| 5 | Phrases that drop the page's hedging | **For Ryan** (Kiro's table of safer phrases); not changed. |
| 6 | Quick tap on the icon did nothing | **Fixed**: a tap under 250 ms keeps the names open. Checked. |
| 7 | Strip contrast | **Raised** (opacity 0.85); not measured on real theme colours. |
| 8 | Strip vs rail late in a section | Known decision; unchanged. |
| 9 | Installer, push script and check pinned to one page | **Next step**, with Kiro's structure (shared core plus per-page data, a lint for the lists). |

Mock: **477 passed, 0 failed**.

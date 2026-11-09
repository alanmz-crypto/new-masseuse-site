# Session handoff: the Peek navigation (for the next Claude Code session)

Written 2026-11-09 by the Claude Code cloud session that built the Peek navigation over 2026-11-08/09, because its context is running out. Ryan asked
for this handoff to go to a higher-tier model, which **continues the work and keeps assessing it**. Read this first, then the files in section 6.

## 1. What this is

The Peek is an in-page navigation for thaisolitude.com (WordPress + Astra, SiteGround). Ryan has decided it becomes **the template for every page**. It
has three parts:
- a read-only title strip at the top;
- a side rail with stacked summary boxes;
- an index icon above the rail.

It runs **only on staging39**, on `/thai-massage/`. Live is untouched; live is Ryan's to start (CLAUDE.md, AGENTS.md).

- **Code:** `scripts/work-with-me-employer/link-ideas/peek-nav.php`, one WPCode snippet with CSS in `wp_head` and JavaScript in `wp_footer`, gated by
  `is_page( 'thai-massage' )`.
- **On staging39:** snippet **#1380**. Undo: `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261109-010037-0c493281.json'`.
- **Branch:** `claude/wizardly-bell-xu0rta`. Nothing is on `main`; never push there.
- **Browser check:** `link-ideas/check-peek-nav.mjs`, which gives **485 passed, 0 failed** on the mock. #1380's staging39 run is `ui-peek-nav-s39-41-1`
  (read its report on `ops-log`).
- **Saved known-good copy, if Ryan wants to go back:** `link-ideas/saved/20261108-1369/` (README inside), plus a staging39 DB export
  `staging39-manual-20261108-182958.sql`, verified off-site.

## 2. The design now (Ryan's settled choices; change details, not these)

1. **Top strip** (`.ts-pk-top`): read-only, `aria-hidden`, sticky under the pinned header. The section titles slide by, the current one centred, the
   neighbours faded (opacity .85 for contrast).
2. **Side rail:** one piece per section, sized by section length.
   - Slide along it to open a **stack** while the page stays still: the open box beside the finger, closed boxes ("number + heading") above and below.
   - Lift to keep it; tap the open box to jump; tap a closed box to open it.
   - `fit1`-`fit5` shrink the stack to fit the screen.
3. **The open box:** number and minutes, the name, a **summary list**, "Tap to go there", and a left/right switch. There is no "You are in…".
   - **The summary** is a list of short ideas per section (`GISTS`, keyed **by page, then heading id**). Ryan's rules:
     - each distinct idea is about three words ("subject, verb, object");
     - overlapping ideas are merged, or the lesser one is dropped;
     - no minutiae and nothing said twice, in a box or across boxes;
     - a list that holds about 60% or less of its section ends with "etc.";
     - the research box may be much longer.
   - The lists are new wording on a regulated page: check with `client-pages/wording_tiers.py` and keep the page's hedging (Kiro's table). Ryan approved
     Kiro's safer phrases.
   - Sections without a list fall back to their own opening paragraphs, whole (`summarize()`). Never split sentences: that broke at "1.5", "U.S." and quotes.
4. **Index icon:**
   - **press** and the names fan out (closed-box form, sized to the rail spacing);
   - **slide onto a name and lift**: its box opens;
   - **slide straight down onto the rail** (24px in, under 12% sideways): the rail preview;
   - **hold 2 s, or a quick tap**: the names stay open; tap one for its box, tap elsewhere to close.

   There is **no page scroll from the icon**: Ryan tried it and said "Get rid of the scroll idea."
5. **Also part of it:** a pinned site header; a left-hand option; a first-time hint; `clearRail()`, which pads **the text blocks, never the column** (see
   the lessons); `#links` that land below the header and strip; focus margin on the content's focusables.
6. **Sitewide on staging39 only:** the 3px reading-progress bar and Astra's scroll-to-top are removed (`install-sitewide-cleanup.php`). Both still exist on live.

## 3. How work is done here (cloud session)

- No SSH. Server work goes through GitHub Actions: `scripts/ci/request.sh` (see `ops/README.md`). The scratchpad helper `try.sh` retried runs when the
  report said "nothing was run". Recreate it if needed:
  `for n in 1 2 3 4; do out=$(scripts/ci/request.sh "$@" --id "$pre-$n" 2>&1); …`
- **A snippet update** is: roll back the current snippet with its snapshot; `rehearse --installer link-ideas/install-peek-nav.php --source staging39`;
  `run --target staging39 --script link-ideas/push-peek-nav.sh --args "--apply"` (it prints the new snapshot name, which you record); then
  `run … --script link-ideas/check-peek-nav.mjs --args "https://staging39.thaisolitude.com"`.
  - It takes about 6 minutes. During it the page shows the plain row, so tell Ryan not to judge a reload until the apply line appears.
  - Run it in the background.
- **Mock:** `cd link-ideas/mock; python3 build-mock.py; python3 -m http.server 8091 -d site`. Then run the check with its base URL replaced by
  `http://localhost:8091` (sed) and `TS_PLAYWRIGHT_ROOT=$(npm root -g)/playwright/package.json`. A full run takes about 5 minutes.
- **Read-only tools:**
  - `link-ideas/dump-sections.mjs <base> /slug/`: the rendered text per section, including FAQ answers that another snippet adds;
  - `link-ideas/probe-overflow.mjs <base>`: the page width and sideways scroll per phone width and rail side.

  Run them through `request.sh run --target staging39 --script link-ideas/<tool>.mjs --args "https://staging39.thaisolitude.com …"`.
- **Commits:** always `git commit -F <file>` (quotes in `-m` broke). End messages with the attribution lines. Push to `claude/wizardly-bell-xu0rta`. A
  stop hook insists on a clean tree.
- **Ryan:**
  - plain language;
  - ends each finished phase with "I finished / Next step / Next lane / See my work / Arc: none (ad-hoc)" and a TL;DR;
  - he is often on his phone;
  - he likes to be told when something could be simpler ("aha" moments), before the build;
  - live only on his go.
- **Rules for Kiro** (Kiro reviews on its own branch, and Ryan pastes its report here): never `ops/request.json`, `request.sh` or `main`.

## 4. Open work, in order

1. **Confirm #1380 on staging39:** the `ui-peek-nav-s39-41-1` report should show 485 passed. If anything failed, read it first.
2. **The template structure** (Kiro's proposal, template-review handoff section 3, which Ryan has not objected to):
   - one shared core snippet;
   - one small data snippet per page (`ts_pk_pages` filter, current page's data printed as JSON);
   - `peek-data/<slug>.json` with `[text, anchor]` items;
   - `lint-gists.py`, run by `check-all.sh`;
   - an installer that takes `core|page <slug>`;
   - a check driven by `peek-pages.json`.

   Today `install-peek-nav.php:44`, `push-peek-nav.sh` and `check-peek-nav.mjs` assume only Thai has the Peek.
3. **Then the pages Kiro rated nearly ready:** Therapeutic, Deep Tissue, Chair.
   - Each needs its own lists (from `dump-sections.mjs`, Ryan's rules, `wording_tiers`, Kiro's hedging) and **Ryan's approval of the wording**.
   - Services (7 short sections) is Ryan's decision.
   - Home, About, Contact and the legal pages are too short; Kiro's rule is 4 or more sections and about 1,500 words or more.
4. **Kiro's enhancements still open:** cap the rail piece lengths (a long research section squeezes the others); a "draft" flag for unapproved lists;
   per-shape mocks.
5. **Decisions for Ryan:**
   - desktop behaviour;
   - a slimmer rail;
   - unpinning the header on short screens;
   - the strip disagreeing with the rail late in a section;
   - the pinned header and the sitewide cleanup as their own sitewide changes;
   - live.

   Safari and real devices have never been tested by anyone: Ryan's iPhone SE and a newer iPhone are the only devices that have seen it.

## 5. Lessons that cost time (do not repeat)

- **Never pad a column that holds full-width blocks.** They are placed from its middle and shift by half the padding: text gets cut at the left edge, or the
  page becomes wider than the screen.
- **The clearance rule must exclude the navigation's own elements** (`.ts-pk`, `.ts-jump`): its `li`s matched and pushed the fan names in.
- **Page-wide `scroll-padding-top` with a pinned header** scrolls the page to the top when anything in the header gets focus. Put `scroll-margin-top` on
  the content's focusables instead.
- **A scrub from a short rail fights the phone's own scrolling** (an absolute mapping leaps, easing lags). Ryan dropped it.
- `max-height` includes the border; `scrollHeight` does not (`fit()` was 2px off).
- Inline scripts in page content must not contain `<`. The snippet is fine.
- Write each edit script to assert its anchors before writing; a missed anchor then writes nothing.
- SiteGround sometimes answers 202 (a bot challenge): retry once. GitHub release downloads have had 504s; `install-base-tools.sh` retries.

## 6. Files to read

`CLAUDE.md`, `AGENTS.md` (regulated copy, OBMT), `.claude/project-memory/link-ideas-practice-page.md` (full history, every snippet and undo),
`.claude/project-memory/open-items.md`, `link-ideas/peek-nav.php`, `link-ideas/check-peek-nav.mjs`, `page-navigation/jump-links.php` (the per-page rows the
Peek reads), and `docs/inter-model/2026-11-08-peek-nav-kiro-template-review-handoff.md` (Kiro's template review and its outcomes addendum).

## 7. Paste-ready prompt for the next session

> Read `docs/inter-model/2026-11-09-peek-nav-session-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site` (pull
> the latest commit first), then `CLAUDE.md`, `AGENTS.md` and `.claude/project-memory/link-ideas-practice-page.md`. Continue the Peek navigation work
> from section 4: confirm snippet #1380's staging39 check, then build the template structure (shared core plus per-page data, the summary-list lint, and
> an installer and check that handle several pages) on staging39 only, and keep assessing the design as you go. Tell me about any simplification
> before you build it. Never touch `main` or live.

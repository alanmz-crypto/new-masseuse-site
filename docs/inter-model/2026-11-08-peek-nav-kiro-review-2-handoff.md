# Handoff for Kiro: second review of the Peek navigation (staging39), after the latest changes

Written 2026-11-08 by the Claude Code cloud session, for Ryan to hand to Kiro (web). **Review only:** find flaws and propose enhancements. Do not deploy
anything and do not change any website (section 7). Your first review and its outcomes are in `2026-11-08-peek-nav-kiro-review-handoff.md` and
`2026-11-08-peek-nav-kiro-review-response.md` (both in this folder). This review covers what changed **after** them.

## 1. Where things stand

Branch **`claude/wizardly-bell-xu0rta`** (nothing is on `main`). Everything below runs **only on staging39**; live is untouched.

- **The design** is one WPCode PHP snippet, `scripts/work-with-me-employer/link-ideas/peek-nav.php` (402 lines), on the Thai Massage page only, applied
  as snippet **#1360**: https://staging39.thaisolitude.com/thai-massage/
- **A sitewide cleanup** on staging39, `link-ideas/install-sitewide-cleanup.php`, removed two things from every page (section 2.7).
- **Browser check** `link-ideas/check-peek-nav.mjs` (Chromium, real touch through CDP; iPhone 13, iPad Mini, iPhone 13 landscape; light and dark;
  JavaScript off): **282 passed, 0 failed** on staging39 (run `ui-peek-nav-staging39-20261108-13`) and **282/0 on the local mock**.
- Ryan's verdict on the current page: "Looks good."

## 2. What changed since your first review (all at Ryan's request, in this order)

1. **Second-review fixes** (your findings): shared `swallowNextClick()` (card taps and outside taps never click what is underneath; timed from the
   finger lifting; disarmed on `pointercancel`), Enter on × closes, focus returns to the rail, the rail is one keyboard stop with arrow keys,
   `lostpointercapture` cancels, wider host check, theme content styles reset (on staging39 a theme rule underlined the rail links).
2. **Left-hand option:** "⇆ Left / ⇆ Right" in the pinned preview and "Use it on the left/right" in the first-time hint; stored in `localStorage`
   (`ts-pk-side`); mirrors rail, card, label, index icon and fan (`html.ts-pk-left` rules, ~line 75).
3. **First-time hint:** once per visitor (`localStorage` `ts-pk-tip`), when reading starts: a note beside the rail plus two gentle wiggles of the rail;
   leaves after 9 s, on "Got it", or on the first touch of the rail (`maybeTip`, ~line 368).
4. **Index icon, design 1 "Fan"** (chosen from five on https://staging39.thaisolitude.com/index-nav-ideas/): a 44px list icon 8px above the rail,
   always shown with it, **blended into the page** (page background, muted text colour, no box, border or shadow; green while held). **Press and hold**
   = every section name fans out beside its own rail piece (staggered); **lift** = they slide back. A click without a press toggles (keyboard,
   VoiceOver/TalkBack); Escape closes; long press blocked (`fanLayout`/`setIndex`, ~lines 344-365; CSS ~lines 103-120).
5. **The old link row under the header is hidden** once the runtime is ready (`html.ts-pk-ready .entry-content .ts-jump{display:none}`, ~line 65).
   It stays in the HTML: it is the source of the section list and the no-JavaScript fallback (checked with JavaScript off).
6. **No "Back to …" button** any more (Ryan asked to remove it).
7. **Sitewide on staging39:** the 3px reading-progress bar (the marked `/* ts-reading-progress:start … :end */` block in the Additional CSS) is removed,
   and Astra's scroll-to-top button is switched off (`astra-settings['scroll-to-top-enable'] = false`). `install-sitewide-cleanup.php` keeps the rest of
   the CSS byte-identical and has an exact rollback (proved in the rehearsal). On live both still exist.
8. **The site header is pinned** on the Thai page (`#masthead{position:sticky}` under `@media (min-height:501px)`, ~line 123); the strip sticks right under
   it (`--pk-head`, measured in `measureHead`, ~line 194); the rail starts below header + strip + icon on short screens; not pinned on short landscape
   screens.
9. **Phone menu flicker fix:** the open Astra phone menu lives inside `#masthead`; measuring the whole header made the menu's `max-height` depend on itself
   and it flickered between two sizes (reproduced on staging39 and on the mock with the previous code: header 328/421 px alternating). Only the header
   bar is measured now (header minus `.ast-mobile-header-content`).

10. **Added after this handoff was first written:** press the index icon, then slide down onto the rail without lifting: the fan closes and the rail's
    Peek preview follows the finger; lifting there pins it (tap to go). Snippet **#1361**; the check now has **310** assertions (staging39 and mock 310/0).
    Please include the icon's `pointermove` handoff (`handed`) in your review.

## 3. Files

| File (under `scripts/work-with-me-employer/link-ideas/`) | What it is |
| --- | --- |
| `peek-nav.php` | **Under review.** CSS in `wp_head` (lines 10-129), JavaScript in `wp_footer` (from line 138). |
| `install-peek-nav.php`, `push-peek-nav.sh` | Installer (one snippet, exact rollback) and the staging39 push script (GitHub Actions only). |
| `install-sitewide-cleanup.php`, `push-sitewide-cleanup.sh` | **Also under review.** The sitewide removal of the progress bar and the scroll-to-top button. |
| `check-peek-nav.mjs` | The browser check (282 assertions): read it for the intended behaviour. |
| `mock/build-mock.py` | Local mock of the Thai page with the real CSS/JS of `peek-nav.php`, now including a simple Astra-like phone menu inside `#masthead`. |
| `index-ideas.html`, `rail-ideas.html` | The practice pages Ryan chose from (index design 1; rail idea 3). Context only. |

Design notes and lessons: `.claude/project-memory/link-ideas-practice-page.md`. Project rules: `AGENTS.md`, `CLAUDE.md`.

## 4. How to test locally

```
cd scripts/work-with-me-employer/link-ideas/mock
python3 build-mock.py            # writes ./site (git-ignored)
python3 -m http.server 8090 -d site &
TS_PLAYWRIGHT_ROOT=<path to a playwright package.json> node ../check-peek-nav.mjs http://localhost:8090   # expect 310 passed, 0 failed
```
The mock has placeholder text and none of Astra's real styles; staging39 has both. To see a past bug, build the mock from an older commit's
`peek-nav.php` (for example `d184012` shows the menu flicker).

## 5. Please look hardest at

1. **The pinned header** (~line 123) with the real Astra header: sticky `#masthead` inside `#page` (earlier probe: no transformed or clipping ancestors);
   the admin bar (logged-in staging users); the desktop header; the transition between portrait and landscape (the 501px height rule) while reading;
   iOS Safari's toolbar resizing the viewport while scrolling.
2. **`measureHead`** after the flicker fix: is subtracting `.ast-mobile-header-content` enough for every Astra header layout (desktop dropdowns, search,
   a cart or account area), and does any other element inside `#masthead` change its height?
3. **The index icon's press-and-hold** against the rail's own gestures, the outside-tap swallow and the first-time hint (z-order: header 96, tip 94,
   card 92, rail 91, strip 90).
4. **`install-sitewide-cleanup.php`**: the block regex and the newline handling, the `applied` / `ready` / `CHANGED` states, rollback refusing when the CSS
   was edited after the apply, and whether `update_option('astra-settings')` needs an Astra cache refresh on some setups.
5. **Hiding the row** while it remains the data source: what screen readers and search engines see now, and whether hiding it with `display:none` has
   any side effect on the section guide snippet's runtime (its button is hidden on this page).
6. **Anything that would block rolling this out** to Therapeutic, Deep Tissue, Chair and Services (same row, different section ids; Services has seven
   destinations).

## 6. Still open (not bugs, decisions)

- Desktop behaviour (hover preview) is undecided: Ryan said phone and tablet first.
- Safari and real devices are untested by anyone; long press, `color-mix()` on old Safari, VoiceOver/TalkBack clicks and iOS viewport units remain
  device-only checks.
- On live, the progress bar and the scroll-to-top button still exist; removing them there needs Ryan's go.

## 7. Hard rules

- **Never edit `ops/request.json` or `ops/live-request.json`, and never run `scripts/ci/request.sh`.** A push that changes `ops/request.json` on any
  branch starts the "Site ops" workflow, which runs real commands on the SiteGround server.
- Never push to, merge into or open pull requests against `main`. Never force-push `claude/wizardly-bell-xu0rta`.
- Do not change page copy (regulated wording; see `AGENTS.md`): no claim words, no "we/our/us"; the navigation only reuses the page's own words.
- No plugins: plain CSS and JavaScript in the one WPCode snippet.
- If you change code, use your own branch from `claude/wizardly-bell-xu0rta` and open a pull request into `claude/wizardly-bell-xu0rta`, with checks
  in `check-peek-nav.mjs` for what you fix. Reports alone are fine too.

## 8. Paste-ready prompt for Kiro

> Read `docs/inter-model/2026-11-08-peek-nav-kiro-review-2-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site`
> (pull the latest commit first) and follow it. Build the mock (`scripts/work-with-me-employer/link-ideas/mock/build-mock.py`), run
> `check-peek-nav.mjs` (expect 310 passed), then review what changed since your first review: the pinned header and `measureHead`, the index icon
> (press and hold, and press-then-slide onto the rail), the hidden link row, the left-hand option, the first-time hint, the removed Back button, and `install-sitewide-cleanup.php`.
> Report flaws first (most severe first, with file:line, how to reproduce on the mock or in a browser, and the smallest fix), then enhancements;
> say which findings you reproduced and which are from reading only. Do not edit `ops/request.json` or `ops/live-request.json`, do not run
> `scripts/ci/request.sh`, and do not touch `main`. Changes, if any, go on a new branch from `claude/wizardly-bell-xu0rta` with a pull request into it.

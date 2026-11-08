# Handoff for Kiro: third review of the Peek navigation (staging39)

Written 2026-11-08 by the Claude Code cloud session, for Ryan to hand to Kiro (web). **Review only:** find flaws and propose enhancements; do not deploy
anything and do not change any website (section 6). Earlier rounds, in this folder: `2026-11-08-peek-nav-kiro-review-handoff.md` (first),
`2026-11-08-peek-nav-kiro-review-response.md` (outcomes of the first two), `2026-11-08-peek-nav-kiro-review-2-handoff.md` (second). This round covers
what changed after your **second** review.

## 1. Where things stand

Branch **`claude/wizardly-bell-xu0rta`** (nothing on `main`). Everything runs **only on staging39**; live is untouched.

- The design: `scripts/work-with-me-employer/link-ideas/peek-nav.php`, one WPCode snippet, Thai Massage page only, applied as snippet **#1363**:
  https://staging39.thaisolitude.com/thai-massage/
- The sitewide cleanup on staging39 (reading-progress bar and Astra scroll-to-top removed): `link-ideas/install-sitewide-cleanup.php`.
- Browser check `link-ideas/check-peek-nav.mjs`: **326 passed, 0 failed** on staging39 (run `ui-peek-nav-staging39-20261108-16`) and **326 passed, 0 failed on the mock**.

About your finding 1 (staging39 not serving the Peek): at the time of the response the server-side check found the snippet active and served to
anonymous visitors (one copy of `ts-peek-nav-css` and `ts-peek-nav-runtime` on `/thai-massage/`; WPCode lists the snippet as `publish`). Each update is
done as "roll back the old snippet, then apply the new one", which leaves the page without the navigation for a minute or two; several such swaps
happened that hour, so your load most likely fell in one. If you see it again, note the time (UTC): the ops reports on the `ops-log` branch show each swap.

## 2. Your second review: outcomes

| Your # | Finding | Outcome |
| --- | --- | --- |
| 1 | Staging39 not serving the Peek | Not reproduced (section 1). |
| 2 | The hint drops keyboard focus after 9 s | **Fixed**: no auto-hide while the hint has focus (re-checked every 3 s); when it hides with focus inside, focus goes to the rail. Checked (10.5 s with focus on "Got it"). |
| 3 | Plain in-page links land under the pinned header | **Fixed**: `scroll-margin-top` from the measured header + strip, and a capture handler on `.entry-content` for `a[href^="#"]` (the theme's own smooth scroll ignored the offset for `#hash` arrivals before). Checked with an injected in-text link. |
| 4 | Text runs under the icon and rail | **Confirmed** and **fixed by measurement**: `clearRail()` pads the column so it stops 6px before the rail and icon on either side, whatever margin the theme gives at that width. On staging39 a theme block with its own margin still reached 4px under the rail on phones, so `clearRail()` also measures the text blocks themselves and pads a little more (capped at 80px); the mock now has such a block and the check measures block edges (the previous snippet fails it). On staging39: text ends at 340, rail at 344. The icon has no background of its own. |
| 5 | CRLF in the installer | **Fixed**: `(\r?\n)?` around the block and the same kind of line break kept; six LF/CRLF cases pass in PHP. |
| 6 | Rollback reports success on CHANGED | **Fixed**: only `ready` is "nothing to roll back"; `CHANGED` is an error. The strict `true` requirement is documented in the header. |
| 7 | iOS left-edge back swipe | **Not changed yet**: it needs a real iPhone first (the rail's gesture is vertical; Safari's back swipe is horizontal). |
| 8 | Header pinned on short portrait screens | **For Ryan to decide** (cut-off is 501px of height). |
| 9 | rove every frame / admin-bar menu height / hint over a card / fan staleness / snapshot mode | **All fixed** (rove only on section change; menu `max-height` minus the admin bar; the hint never opens over a preview, the fan or a press; `fanLayout()` re-runs while open; `umask(0077)` before writing the snapshot). |
| E1 | Slide over the fanned names to pick | **Not taken: Ryan chose his own gesture** (press the icon, then slide down onto the rail without lifting: the fan closes and the rail's Peek preview follows the finger; lifting pins it). |
| E2 | Hint timing | **Done**: after 1.5 s of reading; not for visitors who arrived at a `#section`. |
| E3-E6 | Wiggle per session; header by height; Services in landscape; installer hardening | E3: the hint and wiggle already show once per device. E4: with finding 8. E5: noted for the rollout. E6: done (5, 6). |

## 3. Please look hardest at (new or changed code)

1. **Press the icon, then slide onto the rail** (`handed`, the icon's `pointermove`, `ixRelease`): the hand-off threshold (the rail's top edge + 4px),
   cancel and `lostpointercapture` paths, multi-touch, and the interplay with the outside-tap swallow and the card's tap-on-`pointerup`.
2. **`clearRail()`** (including the second, block-measuring pass): correctness on real Astra widths (phone, tablet, between 921 and 1100px), after a rotation, after switching sides, with the admin bar,
   and whether changing `padding` on `.entry-content` can shift anything the theme or other snippets position by it.
3. **The in-content link handler**: links inside FAQ answers or the research section, links whose target is outside `.entry-content`, and anything else
   that already listens for clicks on those links (the section guide, the A11yPageJumpLinks snippet).
4. **The hint's new timing and focus rules** (`tipWait`, `autoHide`, `hideTip`).
5. **`install-sitewide-cleanup.php`** after the changes (the `lead`/`trail` groups, rollback states, `umask`). Its full rehearsal passed on a copy of staging38 (staging39 already has the cleanup applied).
6. Anything that would block rolling the design out to Therapeutic, Deep Tissue, Chair and Services.

## 4. Files and local testing

Same as the second handoff (section 3 and 4 there). In short:
```
cd scripts/work-with-me-employer/link-ideas/mock
python3 build-mock.py && python3 -m http.server 8090 -d site &
TS_PLAYWRIGHT_ROOT=<path to a playwright package.json> node ../check-peek-nav.mjs http://localhost:8090   # expect 326 passed, 0 failed (it takes a few minutes)
```
The mock now includes a simple Astra-like phone menu inside `#masthead`. To see an earlier bug, build the mock from an older `peek-nav.php`
(for example `git show d184012:scripts/work-with-me-employer/link-ideas/peek-nav.php`, the menu flicker).

## 5. Still open (decisions, not bugs)

- Desktop behaviour (hover preview); unpinning the header on short portrait screens; the iOS left-edge question (needs a device).
- Safari and real devices are untested by anyone.
- On live, the progress bar and the scroll-to-top button still exist.

## 6. Hard rules

- **Never edit `ops/request.json` or `ops/live-request.json`, and never run `scripts/ci/request.sh`** (a push that changes `ops/request.json` on any branch
  runs real commands on the SiteGround server).
- Never push to, merge into or open pull requests against `main`; never force-push `claude/wizardly-bell-xu0rta`.
- No page-copy changes (regulated wording, `AGENTS.md`); no plugins.
- Changes, if any, on your own branch from `claude/wizardly-bell-xu0rta` with a pull request into it and checks in `check-peek-nav.mjs`. Reports alone are fine.
- Ryan's choices are settled: do not propose replacing the press-the-icon-then-slide gesture.

## 7. Paste-ready prompt for Kiro

> Read `docs/inter-model/2026-11-08-peek-nav-kiro-review-3-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site`
> (pull the latest commit first) and follow it. Build the mock, run `check-peek-nav.mjs` (expect 326 passed), then review what changed after your second
> review: the press-the-icon-then-slide-onto-the-rail gesture, `clearRail()`, the in-content link handler, the hint's timing and focus rules, and
> `install-sitewide-cleanup.php`. Report flaws first (most severe first, with file:line, how to reproduce and the smallest fix), then enhancements; say
> which you reproduced and which are from reading only. Do not edit `ops/request.json` or `ops/live-request.json`, do not run `scripts/ci/request.sh`,
> and do not touch `main`. Changes, if any, go on a new branch from `claude/wizardly-bell-xu0rta` with a pull request into it.

## Addendum: outcomes of your third review (after this handoff), and the stacked boxes

All on staging39 as snippet **#1365**; check **361 passed, 0 failed** on staging39 (run `ui-peek-nav-s39-26-…`) and on the mock.

| Your # | Finding | Outcome |
| --- | --- | --- |
| 1 | `clearRail()` runs before the saved side is applied | **Fixed** (side applied first); checked by loading with the left side saved. That check then found a deeper over-padding on staging39 (the theme's text blocks sit ~20px inside the content edge, so measuring the column edge padded 21px too much). `clearRail()` now starts from the theme's own padding and adds only what the text blocks need; the mock has the same inset wrapper (the previous snippet fails it). |
| 2 | The hint still appears after using the rail | **Fixed**: a rail or icon press marks it seen. Checked. |
| 3 | Resize work | **Fixed**: at most once per frame, only when the width changes, and also on content layout changes (ResizeObserver). |
| 4 | Text under the rail above 1100px | **Fixed**: no width limit; the theme's padding is kept whenever it is enough. Checked at 1280px. |
| 5 | Narrower text column on phones | For Ryan (a slimmer rail is possible). |

New (Ryan, 2026-11-08): **stacked index boxes**. When a box opens from the rail, closed boxes for the sections above and below it show its place on the
page ("number + heading"); the open box sits beside the finger; tapping a closed box opens it in the stack (no jump); the stack fits between the strip and
the bottom of the screen by dropping detail in steps (`fit1`-`fit3`), never the open box's name and "Tap to go there".
Also: `scripts/ci/install-base-tools.sh` now retries the `age` download (GitHub release downloads answered 504 for about an hour on 2026-11-08).

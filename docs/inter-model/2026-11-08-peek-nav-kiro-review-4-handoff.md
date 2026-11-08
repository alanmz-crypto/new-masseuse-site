# Handoff for Kiro: fourth review of the Peek navigation (staging39)

Written 2026-11-08 by the Claude Code cloud session, for Ryan to hand to Kiro (web). **Review only:** find flaws and propose enhancements; do not deploy
anything and do not change any website (section 6). Earlier rounds, all in this folder: `2026-11-08-peek-nav-kiro-review-handoff.md`,
`…-review-response.md`, `…-review-2-handoff.md`, `…-review-3-handoff.md` (its **addendum** at the end lists the outcomes of your third review).
This round covers what changed **after your third review**.

## 1. Where things stand

Branch **`claude/wizardly-bell-xu0rta`** (commit `362be7a` or later; nothing on `main`). Everything runs **only on staging39**; live is untouched.

- `scripts/work-with-me-employer/link-ideas/peek-nav.php` (496 lines), one WPCode snippet, Thai Massage page only, applied as snippet **#1365**:
  https://staging39.thaisolitude.com/thai-massage/
- Browser check `link-ideas/check-peek-nav.mjs`: **361 passed, 0 failed** on staging39 and on the local mock.
- Updates are done as "roll back the old snippet, then apply the new one": during a swap the page briefly shows only the old link row (the no-JavaScript
  fallback). The `ops-log` branch has a timestamped report for every swap, if anything you load looks out of date.

## 2. What changed after your third review

1. **Stacked index boxes (Ryan's design).** When a preview opens from the rail, the card is a stack: closed boxes for the sections **above** the open one,
   then the open box (the full preview, as before), then closed boxes for the sections **below**. Each closed box is a button: "number + full page
   heading", cut off with "…". Ryan's words: "if index box 3 was open, you would see index 1 and 2 above it but closed … below the open box 3, box 4,
   and box 5 would do the same".
   - `rowHTML` / `cardHTML` / `openHTML` (~lines 306-318); CSS ~lines 45-60.
   - `place(y)` (~323) puts the **open box** beside the finger, not the card's middle.
   - `fit()` (~319) drops detail in steps until the whole stack fits between the strip and the bottom of the screen: `fit1` placeholder lines and "You
     are in", `fit2` the mini preview, `fit3` the description and more compact rows (CSS ~lines 140-147). It never drops the open box's name or "Tap to go
     there".
   - Tapping a closed box (`activate`, ~line 350) re-renders the stack with that box open, anchored where the tapped row was, **without jumping**.
     Tapping the open box jumps, as before.
   - Defaults chosen by the session (Ryan has not answered): full headings, and "tap a closed box = open it" rather than "jump".
2. **Your third-review findings:**
   - The saved side is applied before the first measurement.
   - A rail or icon press marks the hint as seen (`tipSeen`, ~line 381).
   - `clearRail` is scheduled (`scheduleClear`, ~247): at most once per frame, on resize only when the width changed, and also on content layout changes
     through the existing ResizeObserver.
   - It runs at any width (no 1100px limit).
3. **`clearRail()` rewritten** (~line 225) after a new check on staging39 showed the left side over-padded by 21px. The theme's text blocks sit about 20px
   inside the `.entry-content` edge, so measuring the column edge first over-padded. It now:
   - removes our padding class, so measuring starts from the theme's own padding;
   - measures the text blocks (`p, li, h1-h6, blockquote, figcaption, dt, dd, td, th`);
   - adds only what is missing, through `--pk-clear` and the class `ts-pk-pad` (CSS ~line 74), up to 3 rounds, capped at 80px.

   The mock now has the same inset wrapper (`.mock-inner`) and a block wider than the column, so both problems show up there with older snippets.
4. **Ops:** `scripts/ci/install-base-tools.sh` retries the `age` download (GitHub's release downloads answered 504 on Actions runners for about an hour
   on 2026-11-08). The sha256 check still decides.

## 3. Please look hardest at

1. **The stack:**
   - its height and `fit()` on short screens, with long headings, at large text sizes (browser zoom or Dynamic Type), with 7 sections (Services);
   - whether `scrollHeight` against the available height is the right test;
   - `place()` when the stack must be clamped;
   - focus and keyboard: closed boxes are buttons inside a card with `tabindex=-1`, Enter on a row, Escape;
   - what screen readers announce for the stack.
2. **Re-rendering on tap:** `activate` re-renders the card while the finger is lifting, with the card-tap swallow, `cardTapAt` and the outside-tap swallow
   all in play. Does a tap on a closed box ever also jump, or reach the page under the card?
3. **`clearRail()`:**
   - the class flip during measurement (no paint in between?);
   - the ResizeObserver feedback (`scheduleClear(true)` from a content resize that our own padding causes);
   - FAQ answers opening and closing;
   - the left and right sides after rotation.
4. **Anything that would block rolling out** to Therapeutic, Deep Tissue, Chair and Services.

## 4. Testing

```
cd scripts/work-with-me-employer/link-ideas/mock
python3 build-mock.py && python3 -m http.server 8090 -d site &
TS_PLAYWRIGHT_ROOT=<path to a playwright package.json> node ../check-peek-nav.mjs http://localhost:8090   # expect 361 passed, 0 failed (several minutes)
```
To see an older bug on the mock, build it with an older `peek-nav.php`. For example, `git show 85e7556:scripts/work-with-me-employer/link-ideas/peek-nav.php`
fails the left-side check (gap 26px).

## 5. Still open (decisions, not bugs)

Desktop behaviour; a slimmer rail to give the text column width back on phones; unpinning the header on short portrait screens; the iOS left-edge
question (needs a device). Safari and real devices are untested by anyone. On live, the progress bar and the scroll-to-top button still exist.

## 6. Hard rules

- **Never edit `ops/request.json` or `ops/live-request.json`, and never run `scripts/ci/request.sh`** (a push that changes `ops/request.json` on any branch
  runs real commands on the SiteGround server).
- Never push to, merge into or open pull requests against `main`; never force-push `claude/wizardly-bell-xu0rta`.
- No page-copy changes (regulated wording, `AGENTS.md`); no plugins.
- Ryan's choices are settled: the press-the-icon-then-slide gesture, the stacked boxes. Propose changes to details, not replacements.
- Changes, if any, on your own branch from `claude/wizardly-bell-xu0rta` with a pull request into it and checks in `check-peek-nav.mjs`. Reports alone are fine.

## 7. Paste-ready prompt for Kiro

> Read `docs/inter-model/2026-11-08-peek-nav-kiro-review-4-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site`
> (pull the latest commit first) and follow it. Build the mock, run `check-peek-nav.mjs` (expect 361 passed), then review what changed after your third
> review: the stacked index boxes (`rowHTML`, `cardHTML`, `fit`, `place`, tapping a closed box), the rewritten `clearRail()` and `scheduleClear`, and
> `tipSeen`. Report flaws first (most severe first, with file:line, how to reproduce and the smallest fix), then enhancements; say which you reproduced
> and which are from reading only. Do not edit `ops/request.json` or `ops/live-request.json`, do not run `scripts/ci/request.sh`, and do not touch `main`.
> Changes, if any, go on a new branch from `claude/wizardly-bell-xu0rta` with a pull request into it.

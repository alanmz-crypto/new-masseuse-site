# Handoff for Kiro: fifth review of the Peek navigation (staging39)

Written 2026-11-08 by the Claude Code cloud session, for Ryan to hand to Kiro (web). **Review only:** find flaws and propose enhancements; do not deploy
anything and do not change any website (section 6). Earlier rounds, all in this folder: `2026-11-08-peek-nav-kiro-review-handoff.md` (first),
`…-review-response.md`, `…-review-2-handoff.md`, `…-review-3-handoff.md` (with an addendum), `…-review-4-handoff.md`. Your fifth review (fit/place,
the "+N more" fold, focus after a re-render) was fixed in snippet #1366. This round covers what changed **after that**.

## 1. Where things stand

Branch **`claude/wizardly-bell-xu0rta`** (commit `c8a687e` or later; nothing on `main`). Everything runs **only on staging39**; live is untouched.

- `scripts/work-with-me-employer/link-ideas/peek-nav.php` (553 lines), one WPCode snippet, Thai Massage page only, applied as snippet **#1372**:
  https://staging39.thaisolitude.com/thai-massage/
- Browser check `link-ideas/check-peek-nav.mjs`: **444 passed, 0 failed** on staging39 (run `ui-peek-nav-s39-33-1`) and on the local mock.
- A frozen known-good copy from before the latest round is in `link-ideas/saved/20261108-1369/` (snippet #1369, 428/0), with restore steps.
- Updates are done as "roll back the old snippet, then apply the new one": during a swap the page briefly shows only the old link row (the no-JavaScript
  fallback). The `ops-log` branch has a timestamped report for every swap.

## 2. What changed (Ryan's requests, in order)

1. **Top strip = sliding title row** (#1367): no progress bars or count; the section labels in a row, the current one bold in the middle, neighbours
   fading at the edges, sliding continuously toward the next title through a section (`draw()`, ~line 420).
2. **No second window inside the open box; a scrollable summary instead** (#1368). The mini page preview and the one-line description are gone.
   - `summarize(i)` (~line 198) builds the summary only from the section's own sentences, because the copy is regulated and must not be rewritten. It
     takes paragraphs and list items in document order and skips the navigation, figure captions, nested blocks, text under 25 characters and questions.
     It stops at about 480 characters or 5 sentences, with an abbreviation guard.
   - `.sum` (CSS ~line 57) is `overflow-y:auto`, `overscroll-behavior:contain`, `touch-action:pan-y`, `tabindex=0`, `role=region`, with an aria-label. It is
     shortened by `fit1`-`fit3` and dropped at `fit5`.
   - `activate()` (~line 374) never jumps for a tap or a drag inside `.sum`.
3. **The fanned names look like the stack's closed boxes**: stone background, thin edge, 10px corners, the number in a light circle. The current section
   has the open box's green edge, and read sections keep their tick (CSS ~lines 114-123).
4. **Held for 2 s, the names stay open** (`keepT`, `keep()`, `is-kept`): a buzz and a green ring on the icon. While kept, the names take taps:
   - a tap on a name opens that name's box in the stack (`fanGo`, ~line 508);
   - a touch on the rail closes the names and carries on as a rail touch;
   - any other tap only closes the names, and its click is swallowed.

   The names are capped at `min(48vw,220px)`, so about 40% of a phone's width stays free.
5. **Slide from the icon onto a name and lift: its box opens** (`hitFan`, `setOver`, `is-over`; ~lines 472-505). The name turns green under the thumb.
   Because the rail is as wide as the icon, a slide toward a far name crosses the rail first. So the rail preview takes over only on a clearly straight
   slide down: at least 24px into the rail and under 12% sideways. Once the thumb leaves the rail sideways, the press is locked to picking a name (`lean`).
6. **Tried and dropped the same day: a page scroll from the icon.** Snippets #1370 and #1371 scrolled the page from the icon. The rail stood for the
   whole page; one name showed beside the dot; a sideways slide onto it kept it; the page waited for the thumb to "catch the dot" and then glided.
   Ryan found it "wonky" and said "Get rid of the scroll idea." It is fully removed: no chip, no glide, no `is-scrub`/`is-caught`. Section 3.3 asks you to
   confirm nothing is left behind.

## 3. Please look hardest at

1. **The summary:**
   - whether `summarize()` gives a fair account of each section on the real Thai page. Check FAQ answers (`details`), research lists, and sections that
     open with a short line or a question;
   - sentence splitting: abbreviations, numbers like "1.5", quotes, a sentence that runs into a list;
   - whether any sentence could come out of context in a way that changes its meaning (regulated wording; see `AGENTS.md`);
   - scrolling inside `.sum` on iOS Safari: the scroll chaining to the card or page, the card's own `overflow-y:auto` at fit5, `-webkit-overflow-scrolling`;
   - screen readers: the region inside a card with `tabindex=-1`.
2. **The index press**, which now has several outcomes in one gesture: lift on a name, a straight slide onto the rail, a 2 s hold, or a wobble then lift.
   - the 24px / 12% thresholds, the `lean` lock, and the left-hand mirror (`onLeft`, `inRailBand`);
   - the `keepT` timer against a slide (it must not keep the list while the finger is on a name or on the rail);
   - `swallowNextClick` after lifting on a name; the click that follows lands on the captured icon, inside its 600 ms ignore window;
   - multi-touch, `pointercancel` and `lostpointercapture` with the new paths.
3. **Leftovers from the dropped scroll:** any stale state, CSS, `scrollBehavior` changes on `<html>`, or checks that still assume it.
4. **The kept list and taps underneath:** a kept list next to page links, a tap between two names, a tap on the icon while it is kept, and Escape.
5. **Anything that would block rolling out** to Therapeutic, Deep Tissue, Chair and Services. Services has 7 sections: do 7 fanned names fit beside the
   rail on a short phone? Also, are the summaries sensible on those pages?

## 4. Testing

```
cd scripts/work-with-me-employer/link-ideas/mock
python3 build-mock.py && python3 -m http.server 8090 -d site &
TS_PLAYWRIGHT_ROOT=<path to a playwright package.json> node ../check-peek-nav.mjs http://localhost:8090   # expect 444 passed, 0 failed (several minutes)
```
The mock has placeholder text, so `summarize()` only proves its mechanics there. The real sentences are on staging39 (read-only browsing is fine). To see
the dropped scroll for comparison, build the mock from `git show 255cdb6:scripts/work-with-me-employer/link-ideas/peek-nav.php`.

## 5. Still open (decisions, not bugs)

- Desktop behaviour.
- A slimmer rail to give the text column width back on phones.
- Unpinning the header on short portrait screens.
- The iOS left-edge back swipe (needs a device).
- Hand-written summaries instead of the page's own sentences (needs Ryan's and Butsaba's approval of new wording).
- The top strip centres the next title halfway through a section, while the rail marks the section you are in.
- Safari and real devices are untested by anyone. On live, the progress bar and the scroll-to-top button still exist.

## 6. Hard rules

- **Never edit `ops/request.json` or `ops/live-request.json`, and never run `scripts/ci/request.sh`** (a push that changes `ops/request.json` on any branch
  runs real commands on the SiteGround server).
- Never push to, merge into or open pull requests against `main`; never force-push `claude/wizardly-bell-xu0rta`.
- No page-copy changes (regulated wording, `AGENTS.md`); no plugins. The summary must stay the page's own sentences.
- Ryan's choices are settled: press the icon and slide; the stacked boxes; the summary in the open box; the 2 s hold; slide onto a name; **no page scroll
  from the icon**. Propose changes to details, not replacements.
- Changes, if any, go on your own branch from `claude/wizardly-bell-xu0rta`, with a pull request into it and checks in `check-peek-nav.mjs`. Reports
  alone are fine.

## 7. Paste-ready prompt for Kiro

> Read `docs/inter-model/2026-11-08-peek-nav-kiro-review-5-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site`
> (pull the latest commit first) and follow it. Build the mock, run `check-peek-nav.mjs` (expect 444 passed), then review what changed after your fifth
> review: the scrollable summary (`summarize`, `.sum`), the fanned names in the closed-box form, the 2-second kept list, sliding from the icon onto a name
> (`hitFan`, `lean`, the 24px/12% hand-off), and that the dropped page scroll left nothing behind. Report flaws first (most severe first, with file:line,
> how to reproduce and the smallest fix), then enhancements; say which you reproduced and which are from reading only. Do not edit `ops/request.json`
> or `ops/live-request.json`, do not run `scripts/ci/request.sh`, and do not touch `main`. Changes, if any, go on a new branch from
> `claude/wizardly-bell-xu0rta` with a pull request into it.

## Addendum: outcomes of this review (Kiro's sixth)

| # | Finding | Outcome |
| --- | --- | --- |
| 1 | `summarize()` drops the start of any sentence with a decimal, an abbreviation or a closing quote | **Fixed by removing the splitter**: the summary is now the section's opening paragraphs, whole and in order (about 320 characters, at most three), shown as separate paragraphs. Nothing is cut mid-sentence. The mock has a paragraph with "1.5", "U.S." and a closing quote; a new check asserts every summary paragraph is a whole page paragraph, word for word, in every section. |
| 2 | Fanned names overlap in landscape / with 7 sections | **Fixed**: `fanLayout()` measures the spacing of the rail pieces and sizes the names to fit (`--pk-fan-h`, `is-tight`, compact like `fit3`); `hitFan` picks the name nearest the finger within half the spacing (no overlaps, no dead gaps). Checked in landscape with 7 sections and with large text: gap 3px, sliding onto name 3 picks it. |
| L1 | "below" out of context in a summary | Whole paragraphs reduce it; Ryan or Butsaba to read the staging39 summaries once. |
| E3 | No sign that the summary scrolls | **Done**: the last line fades (`has-more`) while there is more below; updated on scroll. |
| E4 / E5 | Whole paragraphs; a verbatim check | **Done** (finding 1). |

Mock: **451 passed, 0 failed**.

# Response to Kiro's review of the Peek navigation (2026-11-08)

Kiro reviewed `scripts/work-with-me-employer/link-ideas/peek-nav.php` by reading it (no browser). Every finding was checked against the code;
the accepted ones are fixed in snippet **#1351** on staging39 (Thai Massage only), with a check for each in `check-peek-nav.mjs`.
Result: **167/0** in Chromium on staging39 (run `ui-peek-nav-staging39-20261108-4`; iPhone 13, iPad Mini, landscape phone; light + dark; real touch),
and 167/0 three times in a row on the local mock. Safari / real devices are still untested.

| # | Finding | Outcome |
| --- | --- | --- |
| 1 | Long press opens the link menu and cancels the preview | **Fixed**: `contextmenu` and `dragstart` prevented on the rail, `-webkit-touch-callout:none`, `draggable="false"`. Checked. |
| 2 | Outside tap also activates what was tapped | **Fixed**: the outside `pointerdown` closes the preview and swallows the next click (450 ms). Checked with a probe link: first tap only closes, second works. |
| 3 | `color-mix()` without fallback | **Fixed**: plain background first on the strip, the active rail and the glow. Checked in the CSS. |
| 4 | Screen-reader activation swallowed | **Fixed**, and Kiro's suspicion was confirmed in testing: after a finger slide no click follows, so a "pressed" flag stays on. The rail now ignores only a click within 600 ms of a finger lifting; any other click (keyboard, VoiceOver/TalkBack) opens the preview and focuses it. Checked. |
| 5 | Guide button hidden even if the script stops | **Fixed**: hidden only under `html.ts-pk-ready`. Checked. Desktop keeps the rail for now (PC design pending Ryan). |
| 6 | Card rebuilt on every move; layout thrash in `draw()` | **Fixed**: the card is rebuilt only when section, pinned state or reading position changes; `draw()` does all reads, then all writes; `--len` is written only in `lengths()`; label text only when it changes. |
| 7 | Card off-screen on landscape phones; Back overlapping the rail | **Fixed**: the card's clamp keeps it on screen first; `max-height` + hidden extras under 500px height; the rail ends at least 76px above the bottom. Checked in landscape. |
| 8 | `pushState` per jump with no `popstate` | **Fixed**: `replaceState`; the in-page Back is the way back. Checked (`history.length` unchanged). |
| 9 | `vh` on iOS Safari | **Fixed**: `top:30%`, `bottom:max(14%,76px)`. |
| 10 | Back never cleaned up | **Partly**: Back hides once the reader is back within 40px of where they were (checked). Not hidden when the reader has read on far away: that is when returning is most useful. |
| 11 | Multi-touch | **Fixed**: primary pointer only; moves of other pointers ignored. |
| 11 | Blank card fades out | **Fixed**: content cleared 220 ms after the fade. |
| 11 | Two glow timers | **Fixed**: one timer. |
| 11 | Admin-bar offset | **Fixed**: the offset is the strip's sticky `top` plus its height. |
| 11 | `decodeURIComponent` can throw | **Fixed**: try/catch, the raw id is used. |
| 11 | Reading time counts script/style text | **Fixed**: counted from a clone without `script`, `style`, `noscript`. |
| 11 | Rail hit area vs 26px padding | **Fixed** by narrowing the rail to the 44px minimum at 2px from the edge (46px, close to the ~46px text margin). |
| 11 | First-sentence regex stops at "Dr." / "e.g." | **Fixed**: abbreviation guard; question-only paragraphs and captions are skipped for descriptions. |
| Q3 | Tab order | **Fixed**: rail, card and Back are inserted right after the strip (they are fixed, so they look the same), unless an ancestor has a transform, filter, perspective or containment, in which case they stay at the end of `<body>`. Checked. |
| Q4 | Strip `aria-hidden` | Kept, as Kiro advised. |
| E5 | Reduced motion: no slide-in | **Done**. |
| E7 | Better descriptions | **Done** (see 11). |
| E8 | Pause when hidden | Not needed: `requestAnimationFrame` does not run in hidden tabs. |
| E1 / E3 / E4 | Left-hand option, desktop hover preview, first-time hint | **Waiting for Ryan**: these add visible UI or touch the PC decision. |
| E6 | Haptics on iOS | Noted: `buzz()` is a bonus, nothing depends on it. |

Found while fixing: on the tablet profile a quick tap on the pinned card right after lifting from the rail was sometimes never turned into a click by
the browser (about 1 run in 4). The card now acts on a short, still touch when the finger lifts (mouse and keyboard keep using the click); 0 failures in 12 repeats.

## Addendum: Kiro's browser re-run (same day)

Kiro then ran its probes in Chromium against the mock and confirmed #2, #5, #6, #7 and #8, but on the **pre-fix code**: its baseline was the old 123-check
`check-peek-nav.mjs` (commit `8b00f2c`). The fixes are in `d855049` and later (check now has 167 assertions). Re-running Kiro's own five probes against the
current `peek-nav.php` on the mock gave:

| # | Kiro on the old code | Current code |
| --- | --- | --- |
| 2 | outside tap also clicked the link (1 → 2) | the tap only closes the card (control 1 click; after the closing tap still 1) |
| 6 | 21 rebuilds in 20 moves within one section | 1 rebuild |
| 7 | landscape card 53 → 395 in a 342 px screen | 53 → 305 and 78 → 330 |
| 8 | 3 history entries for 3 jumps | 0 |
| 5 | no rail and the guide button still hidden | no rail and the guide button shown |

So no pull request is needed for these. Useful next work for a reviewer: pull the latest `claude/wizardly-bell-xu0rta`, run `check-peek-nav.mjs` on the
mock (167/0 expected; see `mock/build-mock.py`), and review the **new** code paths: the card's tap-on-`pointerup` activation, the 450 ms click swallow after an
outside tap, the 600 ms "click after a press" window on the rail, and the moved DOM position (rail/card/Back inserted after the strip). Real-device checks
(#1 long press, #3, #4, #9) remain open and need a phone.

## Second review (Kiro ran the current code) and Ryan's two yeses

Kiro confirmed 167/0 on `931efb1` and found four real problems in the new code paths. All four are fixed in snippet **#1353** on staging39, without a pull
request from Kiro (same session, to save a round trip), each with a check:

| # | Finding | Outcome |
| --- | --- | --- |
| 1 | A tap on the pinned card also clicked what was under it (the card turns `pointer-events:none` before the click is hit-tested) | **Fixed**: shared `swallowNextClick()`; the card's own tap installs it. Checked with a full-screen link under the card: 0 clicks. |
| 2 | The 450 ms swallow was timed from `pointerdown`, so slow taps clicked through | **Fixed**: timed from `pointerup`. Checked with a 700 ms press: 0 clicks; the next tap works. |
| 3 | A touch that becomes a scroll left the swallow armed | **Fixed**: `pointercancel` disarms it. |
| 4 | Enter on × jumped instead of closing | **Fixed**: Enter on a card button is that button's own click; focus returns to the rail piece. Checked. |
| – | `lostpointercapture` could leave the rail stuck | **Fixed**: a real loss of capture cancels the press. |
| – | Host check missed `backdrop-filter`, `will-change`, container queries | **Fixed**. |
| – | Theme content rules could reach the moved parts | **Confirmed on staging39** (a theme rule underlined the rail links, invisible because they have no text) and **fixed**: scoped resets, `!important` for link decoration. Checked with computed styles. |
| – | Ten tab stops before the content | **Fixed**: the rail is one keyboard stop (roving `tabindex`), arrow keys / Home / End move along it. Checked. |

Ryan said yes to two of Kiro's enhancements:
- **Left-hand option:** the pinned preview has a "⇆ Left / ⇆ Right" button and the first-time hint has "Use it on the left/right". The choice is stored in
  `localStorage` (`ts-pk-side`) and mirrors the rail, card, label and Back; the right side then keeps the theme's own spacing. Checked, including after a reload.
- **First-time hint:** once per visitor (`localStorage` `ts-pk-tip`), when reading starts: a short note beside the rail ("Slide your thumb along this bar
  to preview each section. Lift, then tap the preview to go there.") with "Got it" and the side switch, and two gentle wiggles of the rail (none under reduced
  motion). It leaves after 9 s, on "Got it", or when the rail is touched. Checked.

Result: **223/0** on staging39 (run `ui-peek-nav-staging39-20261108-7`) and three times on the mock. Desktop hover is still open (PC decision pending).

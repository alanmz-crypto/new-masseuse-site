# Handoff for Kiro: review the "Peek" in-page navigation (staging39)

Written 2026-11-08 by a Claude Code cloud session, for Ryan to hand to Kiro (web). **Your job is review only:** find flaws and propose
enhancements to the design and the code. Do not deploy anything, and do not change any website (see "Hard rules").

## 1. What this is, in plain words

Thai Solitude (thaisolitude.com) is a massage practice site on WordPress (Astra theme) hosted at SiteGround. The long service pages have an
"On this page" row of in-page links at the top. Ryan wanted something better for **phones and tablets first** (desktop is decided later).
After four rounds of practice pages he chose this design, called **Peek**:

- **A top strip, display only.** It sits in the page right under the existing link row, then sticks to the top while you read. It shows the current
  topic ("Common reasons"), the position ("2 of 5") and one bar per section, sized by section length and filling as you read. It takes no taps.
- **A rail on the right edge, in the thumb zone** (from 30% to 86% of the screen height). One piece per section, filling as you read, with a
  "you are here" dot; the section name shows beside the rail while the page scrolls.
- **Peek:** slide your thumb along the rail and a card beside it previews each section: number, reading time, name, a one-line description, and a mini
  preview (the section's real heading and first lines). **The page does not move while you browse.** Lift your thumb and the preview stays; **tap it to
  jump**, or tap anywhere else (or ×) to stay where you are.
- **Back:** after any jump, "↩ Back to <section>" returns you to the exact scroll position.
- The heading you land on glows briefly; jumps land the heading just below the strip.

Ryan's words that set the requirements (in order): "real code that really works so that we know we can use it on the final version"; the progress method
"doesn't show you what would be in the section ... people will know where they're jumping to"; "focus on making this work for the phone and the tablet
first"; "be able to select a new spot and not lose your place if you just want to search the spots topics but not jump to one yet and also jump when you
choose"; "it's uncomfortable for people to reach to the top of their phone with their thumb ... on the side not top area"; the top scrub may stay "just
don't make it interactive ... so people know where they're at in the page and what the topic is"; "Peek. Let's use this design"; "keep all the features
from your peek mock-up".

## 2. Where to look

Branch: **`claude/wizardly-bell-xu0rta`** (all work is pushed there; nothing is on `main` yet).
Compare view: https://github.com/alanmz-crypto/new-masseuse-site/compare/main...claude/wizardly-bell-xu0rta

| File (under `scripts/work-with-me-employer/link-ideas/`) | What it is |
| --- | --- |
| `peek-nav.php` | **The design under review.** One WPCode PHP snippet: CSS in `wp_head` (lines ~5-73), JavaScript in `wp_footer` (lines ~77 onwards). Thai Massage page only (`is_page( 'thai-massage' )`). |
| `install-peek-nav.php` | WP-CLI installer: preflight / apply / rollback (creates or deletes exactly that one snippet; allow-list localhost:8090 + staging39). |
| `push-peek-nav.sh` | Runs the installer on staging39 over SSH (GitHub Actions only): DB export + off-site copy first, then apply, then server-side page checks. |
| `check-peek-nav.mjs` | Playwright browser check (Chromium, iPhone 13 + iPad Mini, light + dark, real touch events through CDP). **123/0 on staging39.** Read it for the intended behaviour. |
| `mock/build-mock.py` | Builds a local mock of the Thai page with the real CSS/JS of `peek-nav.php` (placeholder text), so you can test without SiteGround. |
| `rail-ideas.html` | The practice page Ryan chose from (idea 3 = Peek). The real-page version is meant to keep **every** feature of idea 3 plus the top strip. |
| `link-ideas.html`, `progress-ideas.html`, `pill-nav.php` | Earlier rounds (pills, progress bars). Context only; not under review. |

Project rules: `AGENTS.md` and `CLAUDE.md` at the repo root (regulated wording, how changes reach the sites).
Design notes and lessons: `.claude/project-memory/link-ideas-practice-page.md`.

Live pages to look at (staging, not indexed; SiteGround may show a bot challenge page with HTTP 202 to automated browsers):
- Real page: https://staging39.thaisolitude.com/thai-massage/
- Practice page Ryan chose from: https://staging39.thaisolitude.com/rail-nav-ideas/?idea=3

## 3. How the code works (so you can review it fast)

`peek-nav.php` JavaScript (`init`, run just after DOMContentLoaded so the section guide has assigned its ids first):
- **Source of truth** is the existing server-rendered row `.entry-content .ts-jump` (from the `A11yPageJumpLinks` snippet). Its five links give the
  labels and `#targets`; the row stays as the no-JavaScript fallback. Two targets (`#ts-guide-questions`, `#ts-guide-research`) get their ids only at
  runtime from the separate section-guide snippet, so `el(i)` looks them up lazily.
- `describe()` (~line 97): for each section, the real heading text, the first paragraph after it (excerpt, max ~120 chars), its first sentence
  (description, max ~90 chars), and reading time = words between this heading and the next / 200.
- The strip is inserted into the page right after the row (sticky). The rail, card and Back button are appended to `<body>`.
- Gesture (~line 174): `pointerdown` on the rail captures the pointer; `pointermove` picks the section under the finger and shows the card (no
  scroll); `pointerup` pins the card. `click` on the rail is swallowed; keyboard Enter (click with `detail === 0`) opens the preview and focuses it.
- `jumpTo()` (~line 139): records where the reader was (for Back), scrolls so the heading sits below the strip, focuses the heading, glows it,
  updates the address with `pushState`.
- The row's own taps are intercepted in a capture listener (~line 150), because Astra's own smooth scroll ignores the strip.
- `arrive()` (~line 202): when the page opens with `#section`, Astra's `astraSmoothScroll` scrolls about 0.9 s after load and ignores the strip;
  the script re-aligns the heading after scrolling settles, only for 4 s and never after the visitor touches, scrolls or types.
- `draw()` (~line 185), once per animation frame on scroll: current section, per-section fill, strip text, rail dot and label.
- CSS: the bottom "On this page" guide button (`#ts-section-guide-trigger`) is hidden on this page (~line 69); `.entry-content` gets 26px right padding
  under 1100px so the rail does not cover text (~line 68).

## 4. Known weak spots (please confirm, refute or fix)

These are honest suspicions from the author, not verified bugs:

1. **Not tested on Safari / iOS or real devices.** Everything ran in Chromium with emulated devices. Check pointer capture and `touch-action: none`
   on iOS Safari, `color-mix()` and `backdrop-filter` support, `scrollTo({ behavior: 'instant' })`, and how the slide gesture coexists with iOS
   edge-swipe-back (the rail is near the right edge, the browser's "forward" swipe edge).
2. **"Tap elsewhere to close" may also activate what was tapped.** The close happens on `pointerdown`; the following `click` still reaches the element
   underneath (for example a link or an FAQ toggle). Probably it should swallow that first click.
3. **Tab order:** the rail, card and Back button are appended at the end of `<body>`, so keyboard users reach them after the footer.
4. **Screen readers:** the strip is `aria-hidden` (the rail has the links), so position/topic changes are not announced at all. Is that right?
5. **z-index:** strip 90, rail 91, card 92, Back 93. The Astra mobile menu, the cookie/consent layer (if any) and the sitewide 3px reading-progress
   bar (`body::after`, z 99) may conflict.
6. **Right-handed only.** No left-hand option; the rail also sits over the right 48px of content (padded only by 26px).
7. **Auto descriptions** may read oddly when a section opens with a list, an image caption or a FAQ question instead of a sentence; the
   first-sentence regex is naive (abbreviations like "Dr." or "e.g.").
8. **Robustness to timing:** depends on the section guide having set its ids before `init` (setTimeout 0 after DOMContentLoaded) and on Astra's
   arrival-scroll timing (4 s window).
9. **Performance:** `draw()` calls `getBoundingClientRect()` on every heading each frame; `ResizeObserver` on `.entry-content` re-measures on FAQ
   open/close. Probably fine; please check on a mid-range phone profile.
10. **Back button** stays until used, even after the reader has scrolled far away.
11. **Reduced motion:** transitions are cut, but the card still animates position; smooth scroll falls back to instant.
12. **Desktop (PC)** has no tailored design yet (a mouse can drag the rail; hover-to-preview would be the natural desktop behaviour).

## 5. What I would like from you

1. **Flaws**, most severe first: what breaks, where (`file:line`), a concrete way to reproduce (device, browser, steps), and the smallest fix.
2. **Enhancements** to the design (phone and tablet first), each with why it helps a visitor and its cost. Keep the agreed principles: browsing
   never moves the page; jumping only on an explicit choice; the top strip stays display-only; nothing the thumb must reach at the top.
3. If you change code, do it on **your own branch from `claude/wizardly-bell-xu0rta`** (for example `kiro/peek-review`) and open a pull request into
   `claude/wizardly-bell-xu0rta` (not `main`) so Ryan and the Claude session can review it. Keep `check-peek-nav.mjs` passing, and add checks for what
   you fix.

Test locally without any server:
```
cd scripts/work-with-me-employer/link-ideas/mock
python3 build-mock.py            # writes ./site (git-ignored)
python3 -m http.server 8090 -d site &
TS_PLAYWRIGHT_ROOT=<path to a playwright package.json> node ../check-peek-nav.mjs http://localhost:8090
```
(The two dark-mode checks fail on the mock only, because it has no site theme script.)

## 6. Hard rules (please read before changing anything)

- **Never edit `ops/request.json` or `ops/live-request.json`, and never run `scripts/ci/request.sh`.** A push that changes `ops/request.json`
  on any branch starts the "Site ops" workflow, which runs real commands on the SiteGround server. Staging and live writes are done by the Claude
  session on Ryan's word; live is started only by Ryan.
- Never push to, merge into or open pull requests against `main`. Never force-push `claude/wizardly-bell-xu0rta`.
- Do not change page copy. The site's wording is regulated (Oregon massage board rules, see `AGENTS.md`): no claim words such as heal, cure,
  treat, relieve, guarantee, and no "we/our/us". The navigation must only reuse the page's own words.
- No plugins: the design must stay plain CSS and JavaScript inside one WPCode snippet (`peek-nav.php`).
- If you touch an inline script that goes into page *content* (the practice pages `*-ideas.html`), it must contain no `<` character: WordPress's
  shortcode scanner then escapes its `&&` (see `install-progress-ideas.php`). This does not apply to `peek-nav.php`, which is printed in the footer.

## 7. Paste-ready prompt for Kiro

> Read `docs/inter-model/2026-11-08-peek-nav-kiro-review-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site`
> and follow it. Review `scripts/work-with-me-employer/link-ideas/peek-nav.php` (the "Peek" in-page navigation for phones and tablets) for flaws and
> enhancements. Report flaws first (most severe first, with file:line, how to reproduce and the smallest fix), then enhancements. Do not edit
> `ops/request.json` or `ops/live-request.json`, do not run `scripts/ci/request.sh`, and do not touch `main`. If you make changes, put them on a new
> branch from `claude/wizardly-bell-xu0rta` and open a pull request into `claude/wizardly-bell-xu0rta`.

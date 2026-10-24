> **SUPERSEDED 2026-10-04 — DO NOT USE THE MU APPROACH DESCRIBED BELOW.** The staging14 restyle was first deployed through host-guarded mu-plugins; that was removed everywhere (localhost never had it, staging14 and production were cleaned and audited). The design now lives only in WordPress storage (Additional CSS, `ihaf_insert_header`, `astra-settings`, page content) via `scripts/restyle/native/` (see its README), and production runs it. The MU loader/installer files named below no longer exist. Rule: AGENTS.md "no MU-plugins". Everything below is history.

# Handoff: F5 white flash + missing F5 fade (Thai Solitude restyle, localhost)

**Arc: none (ad-hoc)** · From: Claude Code (Sonnet 5.5) · To: Codex (Astra/WordPress lane) · Date: 2026-10-04
**Resume state: IN_PROGRESS — user still sees the problem; Claude could not reproduce it.**
Repo: `~/WordPress/new-masseuse-site` (branch `main`, nothing committed by Claude) · Site: `http://localhost:8090` (Docker, Astra 4.14.0 + Spectra, WP 7.1.2)

## 1. The problem (user's words)
"F5 isn't fading and I still see the white flash." A "lightning fast" white flash on **F5 / page refresh**, visible only when the browser/OS is in **dark mode** ("you can't see it unless you use a dark mode"). Earlier the user also saw a flash in the **menu** (Home, About, …) during page changes; that one is fixed (see §3). The user uses **Google Chrome** (Linux, Arch, Wayland) and wants an **Apple-website-style soft fade** between pages and on refresh.

## 2. What the site looks like now (all applied to the LOCAL stack only)
Local Additional CSS (`custom_css` post 29, theme `astra`, 21,166 bytes) = original CSS + designer's restyle block + two blocks Claude added:
- `/* TS TRANSITION START … END */` — `@view-transition { navigation: auto; }`, `html { background-color: var(--ts-ground) }` (#F5F4EF), default cross-fade at 0.3s, disabled under `prefers-reduced-motion`. **Works**: page-to-page clicks cross-fade in headed Chrome 154; menu stays steady.
- `/* TS RELOAD FADE START … END */` — `html.ts-fade-in body { animation: ts-fade-in .4s … backwards }`.
- Option `ihaf_insert_header` (Insert Headers and Footers / WPCode) holds this inline script, output in `<head>` **after** the first stylesheet `<link>`:
  `try{var n=performance.getEntriesByType("navigation")[0],o=location.origin;if((n&&n.type==="reload")||!document.referrer||document.referrer.indexOf(o)!==0)document.documentElement.classList.add("ts-fade-in")}catch(e){}`

Files (all under `scripts/restyle/`, untracked): `ts-restyle.css` (designer, untouched), `custom-css-before-LOCAL.css` (original), `custom-css-after.css` (restyle), `-after3.css` (+transition), `-after4.css` (+reload fade = what is live), `ts-transition-addendum.css`, `ts-reload-fade-snippet.html`, `check-*.json`, `shots-local/`, `tools/` (measurement scripts, §6). Designer files are in `inter-model_docs/` (untouched).

## 3. What Claude measured (headed Google Chrome 154 via Playwright, real site, no injection)
- Page change (menu click): `pageswap`/`pagereveal` both have a `viewTransition`; ~38-56 frames of smooth cross-fade; **menu brightness change vs final = 0.1 grey level**; 0 fully white frames. Verified in light mode and `--enable-features=WebContentsForceDark`.
- F5 (`page.reload()`): `html.ts-fade-in` IS set; body fades in over 0.4s (28-31 frames, gradual). 0 fully white frames in the screencast.
- Text `textContent` identical before/after on all five pages; no console errors.
- **Claude's screencast cannot see the browser's own blank/white canvas before first paint**, so "0 white frames" does not prove the user's flash is absent.
- Why the earlier menu dip happened: a body fade-in ran *inside* the view transition, so old+new layers no longer summed to full opacity. Fixed by removing the unconditional body fade and gating it to reload/first-visit with the script above. A CSS-only gate (`html:not(:active-view-transition) body`) was tried and was WORSE (menu went blank). Playwright launches Chrome with `PaintHolding` disabled and its route interception bypasses prefetch; both skew view-transition tests unless allowed for (tools handle it).

## 4. Why the user may still see it — hypotheses for Codex to test (ranked)
1. **The fade is not running in the user's real Chrome profile** although it runs in Playwright's clean profile. Check there, not in a fresh profile: `document.documentElement.classList.contains('ts-fade-in')` after F5; `performance.getEntriesByType('navigation')[0].type` (expect `"reload"`); `getComputedStyle(document.body).animationName` (expect `ts-fade-in` right after load); `matchMedia('(prefers-reduced-motion: reduce)').matches` (GNOME `enable-animations` is `true` on this machine, so OS-level reduced motion is unlikely, but a Chrome flag/extension can force it); extensions that inject CSS/dark-mode (Dark Reader etc.); `chrome://flags` Auto Dark Mode variants.
2. **The white is the browser's pre-first-paint canvas, which CSS cannot style.** On reload there is no paint-holding handoff; between commit and first paint Chrome shows its default canvas (white unless the document opts into a dark color-scheme). On this machine first paint after F5 is ~190-270 ms (TTFB ~100 ms). A body fade-in cannot hide this. Options: add `<meta name="color-scheme" content="light dark">` (or a real dark theme) so the canvas is dark for dark-mode users; **requires a design decision** because the site has no dark styles. Putting `html{background:#F5F4EF}` in an inline `<style>` as the very first thing in `<head>` may help only if Chrome paints before blocking CSS arrives (it generally does not).
3. **Script placement/timing**: the script runs after the first stylesheet; if Chrome paints once before it runs, the fade class arrives too late and the animation (`backwards` fill) never shows. Try emitting the script at `wp_head` priority 0 (earlier than all `<link>`/`<style>`), e.g. as a tiny mu-plugin or WPCode "Site Wide Header" snippet with high priority, and re-test.
4. **Auto Dark Mode (forced dark) interaction**: in forced-dark the page is re-coloured by Chrome; the user's flash may be the white un-inverted frame before the filter applies. Reproduce with `--enable-features=WebContentsForceDark` and try the `color-scheme` meta.
5. Server speed: local Docker WordPress TTFB ~100 ms lengthens the blank. SiteGround page cache on staging/prod will differ; do not conclude from localhost alone.
6. The Google Fonts `@import` (line 1 of Additional CSS) was measured and is **not** a meaningful cause (F5 first paint within noise with/without it). Astra also loads Work Sans from Google independently.

## 5. Constraints (from the user — keep them)
- Local first. **Do NOT touch staging12, staging14, or production (thaisolitude.com).** After the user approves localhost, the target is **staging14 only**. No SiteGround deploys.
- The designer's restyle was **CSS only; page text, images, blocks unchanged**. Claude has already added one non-CSS piece (the head script). Anything further beyond CSS (PHP/JS/meta) needs the user's OK; say so before applying.
- One page at a time when reviewing (home page first).
- DB backup before any DB mutation: `./scripts/backup-site.sh --note <why>`. Apply Additional CSS only via `wp_update_custom_css_post($css, ['stylesheet'=>'astra'])` through the `wpcli` service (it mounts only `public_html/`; pass the CSS inline, e.g. base64 into `wp eval`). Flush: `wp cache flush` + `sg_cachepress_purge_cache()`.
- Never commit credentials; `remote_execution.txt`/`sql_manager.txt` are off limits. Nothing is committed yet; user reviews first.

## 6. Test tools (copy of Claude's session scratch scripts; need `pip install playwright pillow` in a venv; they drive `/usr/bin/google-chrome-stable` headed with a throwaway profile, a window appears briefly)
- `tools/nav-frames-real-chrome.py` — warm/cold navigations, refresh, back button: VT status, white frames, gradual-fade steps.
- `tools/reload-vs-click-gate.py` — F5 vs menu-click behaviour, fade class, menu steadiness. (As committed it uses the real site; the injected variant is `tools/menu-dip-variants-injected.py`.)
- `tools/dark-mode-frames.py` — light vs dark scheme vs `WebContentsForceDark`, saves mid-transition frames.
- `tools/f5-first-paint.py` — first-paint timing on reload with/without the font import.
- `tools/check-text-console.py` — `textContent` + console + marker check per page (needs `scripts/restyle/check-before.json` baseline; `python check-text-console.py <tag> home,about,...`).
- `tools/pixel-contrast-capture.py` — hides text, samples real pixels for WCAG contrast.
Best real-world check Codex can add: record the user's actual Chrome (DevTools → Performance → Screenshots, reload with "Capture screenshots", dark mode on) and inspect the first frames after navigation start.

## 7. Known open defects in the designer's CSS (separate from this issue; report to designer, do not silently patch)
- Home "About Thai Solitude" link is white on stone (1.1:1 contrast).
- `.entry-content .wp-block-uagb-container:not(.alignfull){border-radius:6px}` curls divider ends and leaves faint corner arcs in Firefox.
- The "get in touch" notice link lost its hover darkening.
- Firefox has no cross-document view transitions (page changes won't cross-fade there); Safari/WebKit untested on this machine.

## 8. Acceptance criteria
- In the user's own Chrome, dark mode on: F5 and first load show **no white flash** and the page eases in; menu clicks cross-fade with a steady menu; back button OK.
- `textContent` identical on all five pages vs `check-before.json`; no console errors; no horizontal overflow.
- `prefers-reduced-motion: reduce` users get no animation.
- Rollback documented and tested: empty `ihaf_insert_header`; remove the TS RELOAD FADE and TS TRANSITION blocks (or restore `custom-css-after.css` / `custom-css-before-LOCAL.css`).

## 9. Backups (restic via `./scripts/backup-site.sh --list`; each run saves a files + DB pair)
`pre-restyle` 1f6d40d2 · `pre-transition` 8c0aeda0 + 80066a33 · `pre-transition-v2` 6db54cb3 + 6c811708 · `pre-reload-fade` 3f6dff85 + a6471cba. Restore check: `RESTORE_PROJECT=<x> MASSEUSE_RESTORE_ROOT=/tmp/<x> TEST_PORT=8092 ./scripts/test-restore.sh` (passed once; an older `masseuse-restore-test` stack from a prior session is still present, stopped, untouched).

## 10. What NOT to do
No staging12/staging14/production writes until the user says so. Don't edit `inter-model_docs/ts-restyle.css`. Don't claim the flash is fixed from headless/screencast evidence alone — it needs the user's Chrome. Don't re-add an unconditional body fade (it dips the menu during page changes).

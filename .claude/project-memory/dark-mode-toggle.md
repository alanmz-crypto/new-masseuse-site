---
name: dark-mode-toggle
description: "Dark/light mode button in the header: built on staging33, then LIVE (Ryan ran both applies; seen on live 2026-10-18: ts-theme-init in the home page, Accessibility page v2 text); VERIFIED on live 2026-10-19 (theme-test --contrast ALL PASSED in Chromium, Firefox behaviour ALL PASSED, post-change-test live 20/20); default light, WPCode snippet #1237 + CSS block"
metadata:
  node_type: memory
  type: project
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-19T00:33:31.231Z
---

Ryan (2026-10-18): "would you test a dark/light reader button in the header. the latest staging is fine for that." Built and applied on staging33 ONLY (snippet "ThemeToggle" #1237 + CSS block `ts-theme`); live untouched. Tool: `scripts/work-with-me-employer/theme/` (`install-theme-toggle.php`, `push-theme-toggle.sh`, `theme-init.js`, `theme-toggle.js`, `theme-toggle.css`) and `tools/theme-test.mjs <url> [--contrast]` (`BROWSER=firefox` for Firefox). Rollback on staging33: `S_TARGET=staging33 ./push-theme-toggle.sh --rollback '$HOME/private-backups/theme-toggle-before-20261018-222712.json'`.

**Design decisions (mine, Ryan may change):** default stays LIGHT (OS dark setting does not switch the site; following the OS is a one-line change in theme-init.js); choice kept in localStorage `ts-theme`; button = 44 px round, moon/sun icon, `aria-label="Dark mode"` + `aria-pressed`; desktop: after "Get in Touch", phone: before the hamburger. Dark palette = overrides of `--ts-*` and Astra globals under `html[data-ts-theme="dark"]` inside `@media screen`.

**Gotchas learned:** `--ts-ground` is also used as LIGHT text on the dark footer (and `--ts-ink` as the scroll-to-top background; Work With Me phone bar has a hard-coded #fff): remapping tokens blindly broke those; do NOT remap `--ast-global-color-5` (Astra "white" = light text on dark surfaces). Button needs `:focus/:active` background transparent (the theme's global button focus turns it green).

**Verified:** localhost apply/rollback; staging33: 22 behaviour checks pass in Chromium and Firefox, axe contrast 0 failures light+dark on 20 page-views, light mode pixel-identical below the header, posture 28/28. With a real Dark Reader the button shows in both states and our dark is not re-inverted, only re-tinted slightly (judge palette with Dark Reader off for the site). Google Map stays bright (Google's content).

**Prepared for live (Ryan: "add that and i'll push", 2026-10-18):** live added to the toggle installer/push script; Accessibility page VERSION 2 built (`accessibility/install-accessibility-page-v2.php`, `push-accessibility-page-v2.sh`, v1 text kept as `accessibility-page-v1.html`), applied + rollback-proven on localhost and staging33 (staging33 now has BOTH). The page installer refuses unless snippet "ThemeToggle" exists on the target, so ORDER ON LIVE: button first, then page. Full a11y audit on staging33 light and `TS_THEME=dark`: only the known For Practices item. Live dry runs OK (button state=ready; page state=old, refuses until the button is on live). Ryan runs both live `--apply` himself (classifier blocks agent live deploys); afterwards: post-change-test.sh live (expect 20/20 identical to staging33), theme-test.mjs on live, prove-restore.sh live, update notes. Staging33 rollbacks: toggle `theme-toggle-before-20261018-222712.json`, page `accessibility-page-v2-before-20261018-224348.json`. Related: [[ryan-browses-with-dark-reader]], [[darkreader-white-flash-causes]], [[staging32-fresh-copy]], [[accessibility-page]].

---
name: darkreader-white-flash-causes
description: Two CSS patterns make light areas flash white under Dark Reader (!important light colors, @import in an inline style); fix pattern and test harness proven on Thai Solitude 2026-10-04
metadata:
  type: project
---
On new-masseuse-site (Thai Solitude), Ryan's "white flash in the menu bar" under Dark Reader had two causes. Both came from the designer's restyle CSS, not from fades or timing:

1. **`!important` light surface colors.** Dark Reader's page-load fallback paints everything dark with a low-specificity `!important` rule (`body :not(iframe)`, specificity 0,0,2). Any light `!important` background with a class selector beats it until theming finishes.
2. **`@import` inside an inline `<style>`, for example Google Fonts in Additional CSS.** Dark Reader then themes that whole stylesheet asynchronously, leaving it un-darkened for a frame or two on fast or prefetched navigations.

**Why:** Two Codex rounds tuned fades and view transitions without finding either cause. Reproducing with the same Dark Reader build found both in about an hour.

**How to apply:** For any "only in dark mode" flash on willowyhollow, pavlomassage or Thai Solitude, grep the Additional CSS for `!important` light backgrounds and for `@import`.
- Fix the first by dropping `!important` and raising specificity with an ID such as `#masthead`. Light mode stays identical.
- Fix the second by inlining the `@font-face` rules the import served.
- Do not hard-code a dark background on a light-design site; that breaks plain OS-dark visitors. That approach was right only for willowyhollow, whose own design is dark.
- Measure with `scripts/restyle/tools/darkreader-flash/` in new-masseuse-site. It runs a throwaway Chromium with a copied Dark Reader, logs per-frame computed colors and records the screen at 60 fps.
- Full write-up: the RESOLUTION section of `inter-model/CLAUDE-FABEL-2026-10-04-white-flash-handoff.md`.

Related: [[ryan-browses-with-dark-reader]].

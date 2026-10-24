---
name: phone-header-fit
description: Ryan found the phone header wrapping "Thai Solitude" (word split at 320 px) because of my dark/light button; fix LIVE and VERIFIED since 2026-10-19 05:36 UTC (he confirmed on his phone, applied the live patch himself); live rollback command inside
metadata:
  type: project
---

Ryan (2026-10-19): "something you did on staging33 caused the header to wrap text on phones by breaking the word into pieces ... ideally the text should fit on an iPhone SE without wrapping" then "it was probably the dark light button and i didn't notice till now." **Cause:** the dark/light button I added (live) squeezed the title: live fails at every width <= 380 px (375 SE: 2 lines, 360: 2 lines, 320: "Solitu/de"); fine at 385+ and fine with the button hidden.

**STATUS: LIVE since 2026-10-19 05:36 UTC.** Ryan ("that fix worked. you can push it live") confirmed on his phone on staging34, applied my enable-live patch (commit 3cffd03) and ran the apply. Verified on live: width test passes at all 17 widths, light+dark, Chromium+Firefox; button test; post-change-test live 20/20; restore proof clean. **Live rollback:** `S_TARGET=live ./push-header-fit.sh --rollback '$HOME/private-backups/header-fit-before-20261019-053558.json'`. Only WebKit/iOS Safari remains untestable here.

**Fix:** `scripts/work-with-me-employer/theme/header-fit.css` (one marked Additional CSS block `ts-header-fit`, phones <= 389 px only: tighter gaps, nowrap, name shrinks below 350 px, tap targets kept). Applied first on staging34 with exact rollback `S_TARGET=staging34 ./push-header-fit.sh --rollback '$HOME/private-backups/header-fit-before-20261019-050651.json'`; verified in Chromium+Firefox, layout proof (nothing below the header moves, 2,888 comparisons), button test, post-change-test 20/20, restore proof. Live push done (above). **Not tested in WebKit/iOS Safari** (cannot run here).

**Why it slipped through:** the dark-mode verification measured 390 and 1280 px only. **How to apply:** any change to the shared header gets `header-fit-test.mjs` (300-900 px, light and dark, both engines) before it ships. Related: [[dark-mode-toggle]], [[thai-page-evidence]].

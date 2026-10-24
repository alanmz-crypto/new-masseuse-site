---
name: csp-map-bug
description: My CSP (2026-10-17) blocked the Contact page Google Map in Firefox/Safari; FIXED on live 2026-10-18 (Ryan ran the apply himself); always test CSP in Firefox too
metadata:
  node_type: memory
  type: project
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-18T07:08:27.281Z
---

Ryan (2026-10-18): "the google map doesn't showup on the phone now. it's a white spot" and "i don't see the map on staging31". Cause: the map is an `<embed>` loading maps.google.com then redirecting to www.google.com/maps/embed; Chrome checks that under frame-src (allowed) but Firefox/Safari check it under object-src, which my CSP (snippet 1036, deployed 2026-10-17 after a Chrome-only check) limited to maps.google.com. Reproduced in Firefox (console: "blocked the loading of a resource (object-src) at https://www.google.com/maps/embed"); csp-test.mjs --browser=firefox: 4 violations on staging31 (old), 0 on staging32 (fixed).

Fix: `headers/install-headers-csp-v2.php` + `push-headers-csp-v2.sh` (object-src adds https://www.google.com). Applied on staging32, then on LIVE 2026-10-18 UTC: Ryan ran `S_TARGET=live ./push-headers-csp-v2.sh --apply` himself with `!` (the auto-mode classifier refused me running a production deploy; do not retry it, hand him the command). Backup `live-before-headers-csp-v2-20261018-070721.sql` verified off-server. Verified after: public header has the new object-src, Firefox Contact page 0 violations (was 1), full map visible on a Pixel 7 emulation, Chromium fine. staging31 (throwaway) still has the old policy. Rollbacks: live `S_TARGET=live ./push-headers-csp-v2.sh --rollback '$HOME/private-backups/headers-csp-v2-before-20261018-070731.json'`; staging32 `...-before-20261018-023842.json`.

**Why this matters / How to apply:** any CSP or embed change must be tested in Firefox (`node tools/csp-test.mjs <url> --check --browser=firefox`; Playwright WebKit/Firefox are installed under ~/.cache/ms-playwright, WebKit will not launch on this machine) as well as Chrome, and the Google Map must be looked at, not just "0 violations". A plain `curl` of live /contact/ got SiteGround's edge "403 - Forbidden" page on 2026-10-18 while real browsers got 200: use a browser for live checks, ration requests. Related: [[accessibility-page]], [[staging31-backend-audit]], [[thammasat-link]].

---
name: site-review-bundle-f
description: Bundle F WordPress URL/discussion/avatar options on staging38; source capture pending
metadata:
  type: project
---

Preparation only, stacked on applied E (draft PR #66). No F site changes.
Branch `codex/2026-11-01-review-bundle-f`. Six options only: siteurl/home to
HTTPS on the same staging38 host; default comments/pings closed; automatic
pingback flag0 and avatars0. Never put an apex live URL in a staging option.

Fresh raw values/autoload, effective URL overrides, public core semantics,
discussion/menu digests and origin REST/login responses are being captured
read-only. No config file or credential is read; no authentication performed.
Existing posts/pages retain their discussion fields and menu URLs stay stored
as they are. The installer must preserve autoload and use stable host identity
so HTTP→HTTPS can be rolled back. Final capture/proof required before writing.

Live disabled. G waits until November14; Butsaba facts, AntiBot support and the
owner static security.txt fallback remain separate.

---
name: plugin-checks-behind-the-scenes
description: When Ryan names a plugin (is it safe to remove / keep?), do the hidden-code checks myself with the six-question method and report a plain-language verdict
metadata:
  type: feedback
---

Ryan, 2026-10-17: "when I ask about a plugin I want you to check the behind-the-scenes code that I don't understand, can't see, or would have to waste time checking, like if something depends on it, it running, data, etc."

**Why:** he can't read plugin code and doesn't want to spend time on it; he wants the verdict and the reasoning in plain words, and to learn the method.

**How to apply (read-only over SSH, never print secrets/tokens/IDs, then give a verdict):** (1) is it active; (2) DEPENDENCIES: grep other plugins/theme for its slug and function prefixes, `Requires Plugins` headers, and check pages/snippets/custom CSS/header script; read the matching lines, a mention is not a dependency (detection lists, comments); (3) STILL RUNNING: cron events, .htaccess/wp-config rules, drop-ins, cache folders, generated files; (4) DATA it holds (settings, entries, tokens, user meta) and what its uninstall routine deletes; (5) WHAT IT ACTUALLY DOES FOR THE SITE today (render the home page server-side as a visitor and count its output: tags, meta, scripts; check which modules/features are configured and whether an account is connected); (6) CAN I UNDO + WOULD I NOTICE (backup, rehearse on staging31, rollback). Also check hand-offs: another plugin printing the same thing (e.g. SureRank prints the same Search Console verification tag as Site Kit). Be honest when a test turned out inconclusive. Removals still need asking twice; the classifier makes Ryan run deletes (`!`). Example done: [[staging31-backend-audit]] Autoptimize and Site Kit entries.

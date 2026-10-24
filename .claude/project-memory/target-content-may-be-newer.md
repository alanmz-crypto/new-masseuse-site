---
name: target-content-may-be-newer
description: Staging/production copy for Thai Solitude can hold newer edits than localhost; compare text before any apply and use the CSS-only installer for design fixes
metadata:
  type: feedback
---
Ryan (or another agent) edits copy directly on staging copies (staging15 had a new Home H1, About/Services text and a different footer wording that localhost lacked), and localhost can also gain edits production lacks (Therapeutic FAQ retitle).

**Why:** the full native package overwrites all 8 pages and the footer from localhost; applying it would have reverted real edits (the installer's footer drift guard caught it on 2026-10-05).

**How to apply:** before touching a target, compare its visible text with localhost per page. If they differ, do NOT use `install-native.php`; use `scripts/restyle/native/install-css-native.php` (Additional CSS only) and ask Ryan where copy edits should live (localhost is no longer the single source of truth for content). Never pin drifted hashes just to get past a refusal. See [[no-mu-plugins-rule]].

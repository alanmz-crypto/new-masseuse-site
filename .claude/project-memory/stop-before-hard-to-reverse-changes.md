---
name: stop-before-hard-to-reverse-changes
description: "Ryan's standing rule (2026-10-16) - warn and stop BEFORE any change that becomes difficult to roll back; otherwise keep going without re-asking"
metadata:
  node_type: memory
  type: feedback
  originSessionId: 237489c7-4e49-4987-a46e-f923473d4077
  modified: 2026-10-16T20:01:53.069Z
---

Ryan, 2026-10-16, when allowing a server change: "tell me before we make changes that become difficult to roll back. stop me before that. otherwise continue."

**Why:** he is overwhelmed by long lists of options and wants steady progress on safe work, but wants a hard stop at the point of no return rather than discovering it afterwards. This replaces asking permission for every reversible step.

**How to apply:** keep going on reversible work (local files, git branches, moves with checksums, option changes with a saved snapshot, anything behind a fresh DB export plus a tested rollback). STOP and warn first, with the exact change, for: deleting anything (files, zips, plugins, snippets, posts), any write to LIVE thaisolitude.com (production is his), anything not covered by a DB export or snapshot, options-table edits that a Full Deploy would carry to live, pushing/merging to `main` or force-pushing, removing the hidden restricted-wording block attributes (two-confirmation rule), anything that sends mail or messages to other people. Prove rollback before relying on it (the staging31 backup fix was apply -> rollback -> compare -> apply). Claude Code's auto-mode classifier can still deny a server write even after Ryan says go; do not work around it - say so and let him allow it. See [[staging31-backend-audit]], [[no-mu-plugins-rule]].

---
name: therapeutic-copy-staging33
description: "Therapeutic Massage copy change (pasted handoff) is LIVE since 2026-10-19 00:29 UTC and verified; compliance check vs OAR 334 passed; wording has no \"relief\"; live and staging33 carry the same content"
metadata:
  node_type: memory
  type: project
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-19T00:33:30.155Z
---

Ryan pasted an implementation handoff ("Improve Thai Solitude's Therapeutic Massage Page": 3 copy edits, NCCIH link), said "do this on staging33", then "choose a new thought to express relief, then", then "give it a compliance check. if it passes go live." Tool: `scripts/work-with-me-employer/therapeutic/` (`install-therapeutic-copy.php`, `push-therapeutic-copy.sh` with `S_TARGET=staging33|live`, `check-therapeutic-page.mjs`). Edits (page 1107): "Common Reasons" paragraph, "no specific complaint" FAQ answer, one sentence in the "Therapeutic vs Relaxation" FAQ answer; NCCIH source marker **[11]** on both research sentences.

**Wording:** the pasted "short-term relief" was replaced because `client-pages/verify-client-pages.py` bans the bare word `relief` (also `relieve`, `restore`); final: "Research suggests massage may be helpful for some people with neck or shoulder pain, though the benefits may last only a short time." (NCCIH's phrasing). The installer's wording check mirrors the gate.

**Compliance check PASSED (2026-10-18):** the Board's rules (OAR 334 July 2024 + April 2025, identical for the relevant ones), ORS 687.011; the rules have NO banned-word list; tests are OAR 334-040-0010(7) false/deceptive/misleading advertising, 334-030-0005(1)(d) honest and accurate representation, 334-010-0025(2)(c) no medical diagnosis, (4) scope, (9) licence number in all ads. New sentences are hedged, sourced to NIH/NCCIH, no outcome promise. Caveats: not legal advice or a Board ruling; the intake sentences must stay true to Butsaba's practice. Record in AGENTS.md.

**LIVE:** Ryan gave the go but the auto-mode classifier refused my edit adding the live host to the installer allow-list; I built a tested patch, Ryan applied it (commit c1efef0), dry-ran (`state=ready`) and ran `S_TARGET=live ./push-therapeutic-copy.sh --apply` himself. Live export `private-backups/live-before-therapeutic-copy-20261019-002856.sql` verified off-server; public check 14/14. Verified: `post-change-test.sh live` ALL 5 PASS (20/20 pixel-identical to staging33), `prove-restore.sh live` only the page + revision rows + SG log table, page in Chromium AND Firefox axe 0 violations. Live rollback: `S_TARGET=live ./push-therapeutic-copy.sh --rollback '$HOME/private-backups/therapeutic-copy-before-20261019-002906.json'`.

**Why:** regulated advertising (OBMT); live writes need Ryan's hands. **How to apply:** run the rendered-page gates on the localhost rehearsal BEFORE staging; never claim compliance beyond the published rules; hand Ryan `!` lines (or a tested patch) for anything that touches live. Related: [[staging32-fresh-copy]], [[stop-before-hard-to-reverse-changes]], [[dark-mode-toggle]].

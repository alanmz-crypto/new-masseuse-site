---
name: thammasat-link
description: "Work With Me: Thammasat University link (tu.ac.th/en) is LIVE since 2026-10-18 (Ryan ran the apply); also the lesson that a CSP change needs the posture pin updated and the full battery run"
metadata:
  node_type: memory
  type: project
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-18T07:15:17.780Z
---

Ryan (2026-10-18): "add a link to Thammasat University on the work-with-me page. https://tu.ac.th/en/". The name occurs once on the page (credentials list, BA line), next to plain links for Still Light Center and AHA, so the new link is plain too (no target attribute). Tool: `scripts/work-with-me-employer/links/install-thammasat-link.php` + `push-thammasat-link.sh` (one exact string replacement in page 1165's content; guard = old line once and name nowhere else; rollback keeps later edits).

**State:** LIVE (applied 2026-10-18 on Ryan's "push it"; he ran `S_TARGET=live ./push-thammasat-link.sh --apply` himself with `!`). Backup `live-before-thammasat-link-20261018-071043.sql` verified off-server. After: pages 200, structured data identical, 20/20 page-views pixel-identical to staging32, no PHP errors, `prove-restore.sh live` only the expected diffs (page, revision row, SG log table). Live rollback: `S_TARGET=live ./push-thammasat-link.sh --rollback '$HOME/private-backups/thammasat-link-before-20261018-071052.json'`.

**Agent live deploys are blocked:** the auto-mode classifier refused my `--apply` against live ("Production Deploy"), even after Ryan said "push it". Do not work around it: prepare everything (allow-lists, dry run, tests), then give Ryan the exact `! cd ... && SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock S_TARGET=live ./push-...sh --apply` line to run, and verify afterwards. Staging applies and read-only live checks still work.

**Lesson (CSP pin):** after the live CSP fix `post-change-test.sh live` failed one posture check because `posture-check.py` pins the exact policy string; the pin now follows `TESTED_POLICY_V2` (headers/tests/csp-snippet-test.php) and the offline test proves the v2 installer emits it. A change to anything the posture check pins needs the pin updated in the same change and the whole battery run afterwards.

**Why:** `page-content.html` is the historic staging26 input, not kept in step; do not "fix" it to match. **Gotchas:** in zsh `"$branch:refs/heads/$branch"` is parsed as the `:r` modifier (write `"${branch}:refs/heads/${branch}"`) and a bare `echo ====` fails (`=cmd` expansion); use `echo '----'`. Related: [[accessibility-page]], [[staging32-fresh-copy]], [[csp-map-bug]].

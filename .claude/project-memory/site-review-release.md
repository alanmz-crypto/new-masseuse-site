---
name: site-review-release
description: Ryan authorized A–E release preparation; A live request PR68 ready for Ryan, no live apply; F/admin and hosting follow-ups deferred
metadata:
  type: project
---

Ryan 2026-11-01: “How about we ship this for the moment and you look after we are live?”
Prepare completed A–E sequentially under the existing one-bundle/one-LIVE-run rule.
Ryan still merges and starts every live run; never dispatch LIVE or merge main.
No live content/settings/hosting/credential write has occurred in preparation.

A first: PR #68, codex/2026-11-01-review-a-live, based on main after merged #63.
Prepared request live-review-a-20261101-1, review-a/push-bundle-a.sh --apply, tests auto.
Ryan merges #68 then Actions > Site ops (LIVE) > Run workflow > main > type ID.
Wait for the actual report; read automatic checks and run A public checker once
before B preparation. User can say “A done” or provide the run link.

A's original 262-point staging browser pass/rollback remain. Fresh live preflight1
refused one mobile quote anchor. Exact source proved Corporate has no terminal LF;
remove exactly one LF from BOTH old/new fragment strings. No other plan values,
wording, counts or snippet hashes changed. Previously applied staging output is
byte-identical; no-LF/one-LF/two-LF roundtrips verified. New source plan/generated
payload match; browser checker accepts exactly HTTPS staging38 or apex.
Fresh rehearsal from live with uploads PASS: exact rollback/reapply, rendered
copy gates and no new site PHP errors; actual live dry2 all parts ready PASS.
Proof and report links: docs/reviews/2026-11-01-review-live-release.md.

Important fresh runtime: live rehearsal reports WordPress7.1.4, versus E's earlier
stage capture7.1.3. Do not guess updated API semantics or relax E's version guard.
Refresh installed public source and repeat relevant proof before B–E live prep.
C/D/E need frozen live hashes for actual sequential predecessor stores (including
Corporate's no-LF ending), not just host additions. Their verifier host guards
also need explicit live enablement. Do not precompute from an assumed stage copy.

C–E remain staging-applied drafts; F no installer/write and still paused at actual
admin HEAD403 under user's no-access rule. Bot challenge/final full interactivity,
security.txt staging404/static owner fallback, Butsaba pending facts, G date and
owner-only tasks remain explicit follow-ups. No claim of a durable bot fix.
Stage status/proof hub:
https://github.com/alanmz-crypto/new-masseuse-site/blob/codex/2026-11-01-review-bundle-f/docs/reviews/2026-11-01-review-fix-status.md

After all applies rollback E→D→C→B→A, using each actual live snapshot. Current
staging A rollback needs E/D/C/B undone first. Never use a staging snapshot live.

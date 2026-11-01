---
name: site-review-release
description: A live and verified 262; B live request PR #69 ready for Ryan; ordered C–E follow-up, F/hosting deferred
metadata:
  type: project
---

Ryan's 2026-11-01 “ship this for the moment and you look after we are live” authorizes
A–E release preparation under one bundle/one LIVE run. Ryan alone merges/runs LIVE.
Do not merge main, force-push or dispatch LIVE.

A LIVE: merged PR #68 as fcf68c75b8cd1099630136fc073ffbb9eae7131f; Ryan's request
live-review-a-20261101-1/run 41323953827 applied 15:59 UTC. Rendered/origin/posture/
schema/PHP checks pass, restore import 0/intended 88-fingerprint differences.
Post-change command exit 1 is historical staging36's 20 pixel differences; all 20
captures exist, not a pixel-equality pass. Follow-up live browser run 41324631068
passes 262 assertions across nine pages/four widths/light+dark; three phone captures
visually reviewed. Actual live snapshot bundle-a-before-20261101-155950.json,
export live-before-bundle-a-20261101-155938.sql, both verified encrypted off-site.
Proof: docs/reviews/2026-11-01-bundle-a-live.md.

B PREPARED, NOT APPLIED: PR #69, branch codex/2026-11-01-review-b-live, main after A.
Request live-review-b-20261101-1, review-b/push-bundle-b.sh --apply, tests auto.
Ryan reviews/merges #69, then Actions > Site ops (LIVE) > Run workflow > main > ID.
Tell agent “B done” to inspect full report/restore proof and strict live SEO scan.
No B live write or production snapshot yet. Card/payload unchanged from approved B.

Fresh selected-live settings/SureRank source PASS 41324970622, archive SHA
eb3b76e83f2bf2973099c630091df17f357288d15708d9a83408153797b998f5.
WP 7.1.4; reported PHP 8.5.12 is CLI only, no web version inferred. Logo SHA unchanged;
15 directly relevant API/render files match prior source. Capture now selects the
actual S_TARGET consistently (old plugin half hardcoded staging38) and sources-only
skips redundant public dump. No credentials exported.
Fresh live-source uploads rehearsal PASS 41325234903: import, apply/no-op/exact
rollback/reapply, rendered copy gates, no new site PHP errors.
Actual live dry PASS 41325524577: all parts ready, share-card asset absent.
Final Gates must be green/conflict-free before ready/merge instructions.
Full proof/phone steps: docs/reviews/2026-11-01-review-live-release.md.

C–E still staged drafts #64–66, with actual live predecessor hash/source guards,
verifier host enablement and proof needed in sequence. E's prior source WP 7.1.3
differs; inspect current public source/rehearse, never merely relax its guard.
F/admin 403 remains paused; hosting issue/security.txt staging 404 owner fallback,
pending Butsaba facts, G date and owner tasks remain deferred. No durable bot fix.
After all releases rollback E→D→C→B→A with actual live snapshots; on staging38
A likewise needs later touching bundles undone first. Never use stage snapshot live.

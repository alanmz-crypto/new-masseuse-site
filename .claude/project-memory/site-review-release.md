---
name: site-review-release
description: A/B/C live; C 1,247-pass/known TXT 404; D PR #65 request prepared after fresh 1,376-check rollback rehearsal/dry, E next
metadata:
  type: project
---

Ryan's 2026-11-01 ship-for-now go covers A–E preparation in order, one bundle/one
LIVE run. He alone reviews/merges and dispatches LIVE; agents never merge main,
force-push or run LIVE.

A/B live and verified: A snapshot bundle-a-before-20261101-155950.json; B snapshot
bundle-b-before-20261101-163141.json, all encrypted/off-site verified. Their proof
files docs/reviews/2026-11-01-bundle-[ab]-live.md retain actual evidence.

C LIVE via PR #64/run 41333582236, actual snapshot bundle-c-before-20261101-175205.json,
DB live-before-bundle-c-20261101-175150.sql, both verified encrypted off-site.
Origin 12 PASS; restore import 0 before 90/current 92 intended changes, no SEO drift.
Initial post-change INVALID 202; later one-time retry 41336383029 completes deployed
C rendered Home/client/posture/PHP checks PASS, with expected old-reference schema/
pixels FAIL. Contact CSP 41334346820 four views/zero violations. Public C 41334743122
1248 assertions/1 failure: security.txt 404, hence 1,247 other assertions pass.
No full-suite/all-pass/durable host fix claim. Proof docs/reviews/2026-11-01-bundle-c-live.md.

D PREPARED, NOT APPLIED, existing PR #65 branch codex/2026-11-01-review-bundle-d.
Normal feature merge retains current main 69d2de8a A/B/C live code/records.
Request live-review-d-20261101-1, review-d/push-bundle-d.sh --apply, tests auto.
Ryan reviews/merges #65 only, then Actions > Site ops (LIVE) > Run workflow > main >
type ID. Tell agent “D done” to inspect all origin/post-change/restore sections
and public D behavior before E. No D actual live snapshot/content write yet.

Fresh source 41335028870 SHA 93fae08d968060d5ae1b15af5fd42d378cd7b26e2aa9364c97c8fa71a3be4b35:
WP 7.1.4/Astra 4.14.1, CLI 8.5.12 only (not web PHP). All 9 old page hashes equal C's
verified applied live hashes; front 296, heart snippet code/status/meta/terms and
footer baseline match. Approved45 pairs/9 pages/source decisions unchanged; preserve
Corporate raw serialization with live/LOCAL hash guards; stage hash pairs retained.
Source capture selects actual host and supports sources-only without public requests.
Mandatory verify-bundle-d.py guard also enables exact HTTPS apex, before origin 53
checks after apply. B metadata/C option fields are preserved by exact snapshots.

Fresh live-copy uploads rehearsal 41335527679 PASS 1,376/0 (C 1,248 + D 128), exact
rollback/reapply and regulated gates; Home/About phone captures reviewed.
Actual live dry 41336033180 all parts ready PASS. Final Gates before ready status.
Current proof/phone steps docs/reviews/2026-11-01-review-live-release.md.

E draft #66 needs actual live D predecessor, fresh installed core/media source and
symlink uploads/frame rehearsal; do not just relax older WP 7.1.3 check. F/admin 403
remains paused, no six-option installer/write. C security.txt public 404/static owner
fallback and hosting protection remain deferred, no server-file/rule/support message.
Butsaba 2/3/4/6/7/length-consistency, G November 14/15 sentences and owner tasks remain.
Reverse E→D→C→B→A with actual live snapshots, never stage snapshots.

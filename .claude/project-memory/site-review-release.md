---
name: site-review-release
description: A/B live verified; C PR #64 live request prepared after fresh1248-assertion rehearsal and dry; D/E next, F/hosting deferred
metadata:
  type: project
---

Ryan authorized completed A–E release preparation on 2026-11-01 (“ship this for
the moment and you look after we are live”). One bundle/one LIVE run; he alone
reviews/merges and dispatches LIVE. Agents never merge main, force-push or start LIVE.

A LIVE via PR #68/run 41323953827, verified 262 browser assertions; actual snapshot
bundle-a-before-20261101-155950.json. Proof docs/reviews/2026-11-01-bundle-a-live.md.
B LIVE via PR #69/run 41326724484; strictscan 41329251788 all 12 pages 200, zero expectation
failures, approved default/Home PNG 1305/1200×630/SHA MATCH, exact entity/meta/social.
Actual B snapshot bundle-b-before-20261101-163141.json; DB
live-before-bundle-b-20261101-163126.sql, both encrypted/off-site verified.
Proof docs/reviews/2026-11-01-bundle-b-live.md. Historical staging36 schema/pixel
differences and intended restore command exit 3/import 0 are qualified there.

C PREPARED, NOT APPLIED, existing PR #64, branch codex/2026-11-01-review-bundle-c.
Normal feature merge integrates current main dc4011e1 without regressing A/B.
Request live-review-c-20261101-1, review-c/push-bundle-c.sh --apply, tests auto.
Ryan reviews/merges #64 only, then Actions > Site ops (LIVE) > Run workflow > main >
type ID. Tell agent “C done” for full report/CSP/public-contract review before D.
No C production snapshot/IDs or live write yet.

Fresh C capture 41329640631 SHA c95a7e7eb61311840b8423036bfa5b7534c2dedc2e1e97181597343095310126:
WP 7.1.4/Astra 4.14.1, CLI 8.5.12 only (not web PHP). Privacy 1227/Access 1230 match CSS.
Existing snippet code/meta/terms and blog-meta unchanged; new titles absent.
Fourteen FAQ/Astra source files identical; generated active CSS unchanged except
About host substitutions; live Chair  1293 CSS equals staged Chair  1302.
Eleven raw pages/CSS match stage after host mapping. Corporate HTML tokens and
block attributes match, but whitespace/keyorder/EOF differ. Preserve exact raw bytes;
freeze live old/new hashes, update LOCAL Corporate profile to actual source;
keep stage hash pairs and all transformation/runtime/CSS/snippet payloads unchanged.
Fresh uploads rehearsal 41330272579 PASS 1,248/0, exact rollback/reapply, geometry/
ARIA/keyboard/focus/map/strip/utilities and regulated gates; no new site PHP errors.
Actual live dry 41330864816 all parts ready PASS. Final Gates before ready status.
Full plan/phone proof docs/reviews/2026-11-01-review-live-release.md.

C virtual security.txt strict private test passes; staging hosting delivery 404 remains
owner fallback deferred. Public contract must report actual endpoint after C, never
claim 404 passes. Tracked security-live/security.txt prepared, NOT uploaded by push.
Contact CSP/full public interactivity require checks after own apply; stop at first
challenge, no durable host fix claim. No hosting controls/static files/support message.

D/E staged drafts #65/#66 need each actual live predecessor, source/hash/host/verifier
guards, rehearsal/dry. F/admin HEAD  403 remains paused, no six-option write/installer.
Butsaba 2/3/4/6/7/length-consistency, G November 14/15 sentences and all owner tasks
remain deferred. Rollback E→D→C→B→A using actual live snapshots; never stage snapshot.

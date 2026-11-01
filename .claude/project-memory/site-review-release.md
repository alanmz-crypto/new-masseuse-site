---
name: site-review-release
description: A–D live and verified; E PR #66 prepared after exact core/source and rollback rehearsal/dry; hosting TXT/F remain open
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

D LIVE via Ryan’s PR #65/e357dbb and run41338572399. Origin53 PASS; restore
import0 before/current92 with intended nine pages/sn1234/Astra footer/WPCodecache,
no SEO/CSS diff. Initial automatic postchange INVALID202/zero tests. Focused public
D run41338898883 passes128 assertions, nine pages/three widths/lightdark/noJSerrors.
Snapshot bundle-d-before-20261101-185003.json and DB live-before-bundle-d-20261101-184951.sql
(5562193B, SHA34367bb1e69d) verified encrypted offsite. Snapshot SHA c3d07cde64fe.
Proof docs/reviews/2026-11-01-bundle-d-live.md. No durable hosting-fix claim.

E PREPARED, NOT APPLIED, existing PR #66, normal current-main merge preserves A–D.
Fresh selected-live native source41339677797 confirms all nine D applied page
hashes, front296, ten images/six derivatives, no new snippet-name collision and
byte-identical approved WebP. Archive SHA d9fb4398b88864a4b33f92e9b540504bbecc3fc2734082b87c78423bf77f2df2.
WP7.1.4/Astra4.14.1/CLI8.5.12 only. Emoji/hooks/core-image/three-arg media filter
review supports strict host versions: staging7.1.3, local/apex7.1.4. Original four
pairs, staged hashes, runtime and asset unchanged; exact live page hashes added.
[Fresh live-copy uploads rehearsal, run 41339834933](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/190757-review-e-live-rehearse-20261101.md) **PASS: 1,664 assertions, zero failures** (C 1,248 + D 128 + E 288). The source is WP 7.1.4; isolated uploads symlink preparation, apply/no-op/exact fingerprint rollback/reapply, regulated Home/client gates and no new site PHP errors all pass. Fingerprints move only Home/Therapeutic, the two new snippets and WPCode cache (related media/post/meta/term rows). All eight image frames and page heights remain unchanged at three widths/light+dark (48 image states). Three phone previews—Home Deep/Thai and Therapeutic red—were visually reviewed.
[Actual live read-only preflight, run 41340220974](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/190905-review-e-live-dry-20261101.md) **PASS**: Home/Therapeutic and both new snippets ready; WebP ready to import once, ten originals preserved. No content write occurred.
Request live-review-e-20261101-1, review-e/push-bundle-e.sh --apply, testsauto.
Ryan reviews/merges #66 only, then Actions > Site ops (LIVE) > Run workflow > main >
type ID. Tell agent “E done” to inspect all report sections and public E behavior.
No actual E live snapshot/IDs/write yet. Exact logical media paths/ownership-aware
rollback retained. Current proof/phone steps docs/reviews/2026-11-01-review-live-release.md.

F/admin403 remains paused/no six-option installer/write. C security.txt public404,
uninstalled static owner fallback/hosting protection remain deferred; no server-file/
rule/support message.
Butsaba 2/3/4/6/7/length-consistency, G November 14/15 sentences and owner tasks remain.
Reverse E→D→C→B→A with actual live snapshots, never stage snapshots.

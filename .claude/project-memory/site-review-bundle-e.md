---
name: site-review-bundle-e
description: Bundle E performance applied on staging38; symlink rehearsal, exact media/snippet rollback and pending public/owner lanes
metadata:
  type: project
---

APPLIED on staging38, 2026-11-01 12:02 UTC, draft PR #66 stacked on D (#65).
Branch `codex/2026-11-01-review-bundle-e`. Live disabled/unchanged.

Symlinked-uploads rehearsal 4 PASS: 1,664 browser assertions (C 1,248 + D 128 + E 288),
apply/no-op reapply/exact rollback/reapply, Home/client gates, no new PHP errors.
Dry2/apply2, 26 origin assertions, rendered Home/client/posture gates and restore
import PASS. All 20 public screenshot views captured, zero missing/problems.
Expected A–E pixel differences; B schema 27 vs 23 unchanged, zero parse errors.
Final full interactive public verification remains pending; no durable AntiBot
fix. security.txt is confirmed 404 on the origin; owner static fallback prepared.

Changes: two stored pages/four image-only pairs (Home canonical service photos,
Therapeutic red PNG→prepared WebP); no prose/CSS/SEO/option edits. Rendering
runtime supplies actual dimensions/responsive sources on four pages and two
normal Latin font preloads; another snippet removes frontend emoji output,
preserving admin/email/RSS. New snippet IDs 1342/1343; one new WebP. No attachment
IDs in edited blocks. Installed core width/height attributes are CSS resize
strings, so intrinsic attributes use the render filter to keep saved blocks
compatible. All ten original files and existing responsive files unchanged.

Verified source: PNG 512×400/616,299B; portrait 360×540; cutout 1254² with no
attachment row. Prepared WebP 400×313/21,796B, 96.46% smaller, WP GD quality 82
from a private copy; original unchanged, composition visually reviewed.
Therapeutic Home copy is byte-identical; Home Deep/Thai photos were swapped
relative to their canonical service pages, and E corrects that mapping. Existing
6:5 frames are preserved; geometry/page heights unchanged at three widths/light+dark.
Work With Me contains two content images, not the review’s stale count of three.

Active snapshot `bundle-e-before-20261101-120216.json`; DB export
`staging38-before-bundle-e-20261101-120203.sql` (6,713,520 bytes). Both verified
encrypted off-site before/after apply as appropriate. Exact rollback:
`request.sh run --target staging38 --script review-e/push-bundle-e.sh --args '--rollback $HOME/private-backups/bundle-e-before-20261101-120216.json'`.
Roll E back before D/C/B/A or older exact-state installers on these stores.
No SureRank rows changed. The incomplete 114734 snapshot is diagnostic only.

Durable lesson: configured SiteGround upload paths are logical symlinks;
WordPress media APIs need the logical path to produce relative attachment
metadata. Use realpath only for containment/SHA checks. First staging attempt
recovered all old stores and removed its owned files/media; diagnostics proved
zero leftovers before retry. Isolated-only prepare-rehearsal.php reproduces the
physical/logical distinction before baseline and is never uploaded by staging
push. A moved upload is snapshotted before attachment insertion; every new ID is
saved immediately; full post/meta/terms/file guards protect rollback/cleanup.
A reused existing exact asset is unowned and never removed.

Proof/sources/active reports: `docs/reviews/2026-11-01-bundle-e-staging38.md`.
Rehearsal 4 report 115850, dry2 report 120020, apply2/restore report 120532.
No external message or hosting setting changed. Butsaba pending facts/session
length consistency and November 14/G remain separate. F remains.

# Review Bundle E (staging38 and exact live apex)

Applied on staging38, 2026-11-01 12:02 UTC. Symlinked-uploads rehearsal 4 (1,664 assertions/exact rollback), dry2/apply2, 26 origin checks, rendered gates and restore import PASS. [Scope/proof](../../../docs/reviews/2026-11-01-bundle-e-staging38.md).

`changes.json` freezes four image-only replacements on Home/Therapeutic and all ten original file identities. `build-bundle-e.py` packages those, the 21,796-byte prepared 400px WebP and both PHP runtime sources. Intrinsic image dimensions/responsive attributes are added during rendering so saved core image blocks remain compatible with the editor. Two actual Work With Me images are handled; the review’s count of three was stale.

`install-bundle-e.php` has whole-store guards, exact snapshots, SureRank preservation, and ownership-aware media/snippet cleanup. It records uploaded bytes before attachment insertion, then immediately records every created ID and full state. It preserves all original images and any reused unowned asset. `push-bundle-e.sh` defaults to read-only preflight and verifies a fresh DB off-site before any apply/rollback; it copies the completed snapshot off-site before origin checks.

`check-rehearsal.mjs` runs C, D and E browser contracts on the isolated applied copy, stopping on any failure/INVALID. E measures geometry against frozen original image DOM at three widths/light+dark, verifies image responses/bytes/dimensions/srcsets, preloads and emoji absence. `verify-bundle-e.py` checks rendered origin output and delivered WebP bytes after staging or live apply.

Live preparation uses the actual post-D capture, exact reviewed per-host core versions and a fresh live-copy uploads rehearsal. [Live preparation and phone steps](../../../docs/reviews/2026-11-01-bundle-e-live-preparation.md). Request live-review-e-20261101-1 is for Ryan alone to start after merging PR #66. No live E write yet. No public retry loop. Read all reports. Exact E snapshot must be rolled back before D/C/B/A.

Staging rollback snapshot (never use live): `bundle-e-before-20261101-120216.json`; E before D/C/B/A. Logical WordPress upload paths are preserved for media APIs; realpath is used only for guards.

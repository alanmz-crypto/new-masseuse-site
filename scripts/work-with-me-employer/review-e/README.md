# Review Bundle E (staging38 only)

Prepared, not applied. Uploads rehearsal 3 PASS (exact rollback, C/D regressions and E geometry/assets/preload/emoji contract); dry run next. [Scope/proof](../../../docs/reviews/2026-11-01-bundle-e-staging38.md).

`changes.json` freezes four image-only replacements on Home/Therapeutic and all ten original file identities. `build-bundle-e.py` packages those, the 21,796-byte prepared 400px WebP and both PHP runtime sources. Intrinsic image dimensions/responsive attributes are added during rendering so saved core image blocks remain compatible with the editor. Two actual Work With Me images are handled; the review’s count of three was stale.

`install-bundle-e.php` has whole-store guards, exact snapshots, SureRank preservation, and ownership-aware media/snippet cleanup. It records uploaded bytes before attachment insertion, then immediately records every created ID and full state. It preserves all original images and any reused unowned asset. `push-bundle-e.sh` defaults to read-only preflight and verifies a fresh DB off-site before any apply/rollback; it copies the completed snapshot off-site before origin checks.

`check-rehearsal.mjs` runs C, D and E browser contracts on the isolated applied copy, stopping on any failure/INVALID. E measures geometry against frozen original image DOM at three widths/light+dark, verifies image responses/bytes/dimensions/srcsets, preloads and emoji absence. `verify-bundle-e.py` checks rendered origin output and delivered WebP bytes after staging apply.

No live target/request. No public retry loop. Rehearse with uploads, dry run, apply with verified backup, read all reports. Exact E snapshot must be rolled back before D/C/B/A.

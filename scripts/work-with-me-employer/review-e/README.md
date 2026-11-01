# Review Bundle E (live and staging38)

LIVE since 2026-11-01 19:14 UTC via Ryan’s PR #66/run41340701287; [actual result and rollback](../../../docs/reviews/2026-11-01-bundle-e-live.md). Native ownership proof **9 checks PASS**; focused public E browser check **INVALID** (hosting robot challenge on the initial WebP request, zero assertions). It remains pending access; no retry loop.

Applied on staging38, 2026-11-01 12:02 UTC. Symlinked-uploads rehearsal 4 (1,664 assertions/exact rollback), dry2/apply2, 26 origin checks, rendered gates and restore import PASS. [Scope/proof](../../../docs/reviews/2026-11-01-bundle-e-staging38.md).

`changes.json` freezes four image-only replacements on Home/Therapeutic and all ten original file identities. `build-bundle-e.py` packages those, the 21,796-byte prepared 400px WebP and both PHP runtime sources. Intrinsic image dimensions/responsive attributes are added during rendering so saved core image blocks remain compatible with the editor. Two actual Work With Me images are handled; the review’s count of three was stale.

`install-bundle-e.php` has whole-store guards, exact snapshots, SureRank preservation, and ownership-aware media/snippet cleanup. It records uploaded bytes before attachment insertion, then immediately records every created ID and full state. It preserves all original images and any reused unowned asset. `push-bundle-e.sh` defaults to read-only preflight and verifies a fresh DB off-site before any apply/rollback; it copies the completed snapshot off-site before origin checks.

`check-rehearsal.mjs` runs C, D and E browser contracts on the isolated applied copy, stopping on any failure/INVALID. E measures geometry against frozen original image DOM at three widths/light+dark, verifies image responses/bytes/dimensions/srcsets, preloads and emoji absence. `verify-bundle-e.py` checks rendered origin output and delivered WebP bytes after staging or live apply.

Live release is complete; `live-review-e-20261101-1` is used. The strict per-host core guards, exact post-D page source, original payload and logical-path/ownership safeguards remain unchanged. [Historical preparation](../../../docs/reviews/2026-11-01-bundle-e-live-preparation.md) records 1,664 rehearsal assertions and actual live preflight. No further LIVE request is prepared. Exact E rollback precedes D/C/B/A.

Staging rollback snapshot (never use live): `bundle-e-before-20261101-120216.json`; E before D/C/B/A. Logical WordPress upload paths are preserved for media APIs; realpath is used only for guards.

Actual live rollback snapshot: `bundle-e-before-20261101-191447.json`. Never use the staging snapshot live. Native ownership and public E browser verification run read-only; they do not apply, purge, clean up or import anything.

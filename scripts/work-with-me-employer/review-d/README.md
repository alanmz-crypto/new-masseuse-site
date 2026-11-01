# Review Bundle D — staging verified, live prepared

Applied on staging38 only, 2026-11-01 10:45 UTC. Rehearsal2, 53 origin assertions, rendered gates and restore import PASS. Exact approved copy/research corrections. [Scope, before/after and proof](../../../docs/reviews/2026-11-01-bundle-d-staging38.md).

- `changes.json` freezes 45 exact pairs on 9 pages, the heart-link snippet and footer scalar; `build-bundle-d.py` packages it with staging/isolated-localhost hashes.
- `install-bundle-d.php` preflights every resource, snapshots full stores outside ABSPATH, preserves SureRank rows, changes only reviewed content/footer, and guards exact rollback. No production host. No new/deleted posts/snippets/files/media.
- `push-bundle-d.sh` dry run is read-only; apply/rollback first exports the staging38 DB and verifies its encrypted off-site copy. It copies the completed exact snapshot off-site before the origin-render checks.
- `check-rehearsal.mjs` runs C’s complete accessibility/layout regression suite and D’s text/link/layout checks on the isolated copy; `verify-bundle-d.py` checks origin-served rendered text after apply. Public checks stay pending while challenged.

```sh
scripts/ci/request.sh rehearse --target staging38 --installer review-d/install-bundle-d.php --with-uploads
scripts/ci/request.sh run --target staging38 --script review-d/push-bundle-d.sh
scripts/ci/request.sh run --target staging38 --script review-d/push-bundle-d.sh --args '--apply'
```

Use the exact saved D snapshot for rollback, D before C/B/A. Never run this on live. Ryan still owns live enablement and his own LIVE workflow run.

## Live preparation (supersedes disabled/never-live text above)

Ryan authorized ordered A–E release preparation; A/B/C are now live.
PR #65 integrates main normally, preserving all A/B/C guards/payloads/records.
Fresh actual 9-page hashes equal C's verified applied hashes. Front296, heart snippet
settings/terms/status/code and footer scalar match. Corporate raw serialization
is preserved with actual live/LOCAL hash pairs; stage hashes and approved 45 pairs,
research/citation/source decisions are unchanged. No new fact/copy proposal.
Fresh uploads rehearsal from live PASS 1,376/0 (C 1,248 + D 128), exact rollback/reapply,
regulated gates/no new site PHP errors; actual live dry all 9 pages/snippet/footer ready.
Origin-verifier and browser guards also enable exact apex; 53 origin checks after apply.
Request `live-review-d-20261101-1` is prepared, not applied. Ryan reviews/merges #65,
then personally starts Site ops (LIVE) from main with that exact ID.
[Full proof/phone steps](../../../docs/reviews/2026-11-01-review-live-release.md).
Snapshot off-site copy precedes origin checks. C's known security.txt 404/hosting
follow-up remains explicitly deferred; no static upload/protection change.

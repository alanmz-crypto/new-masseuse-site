# Review Bundle D (staging38 only)

Exact approved copy/research corrections. [Scope, before/after and proof](../../../docs/reviews/2026-11-01-bundle-d-staging38.md).

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

# Site op `search-contrast-apply-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `search-contrast/push-search-contrast.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `47b96327b1a5d54ed8b3ec01c761b09681aaf9b1` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42972709329 |
| started | 2026-11-11 06:29:01 UTC |

## push-search-contrast.sh --apply(S_TARGET=staging39): FAIL (exit 1)

```text
Target site: staging39   component: search-contrast   mode: apply
staging39 is www/staging39.thaisolitude.com/public_html
Success: search-contrast ready; css_sha256=2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae; nothing changed.
SEARCH-BACKUP /home/<ssh-user>/private-backups/staging39-before-search-contrast-20261111-062911-2323.sql 8581a3d5cc7eb0eacc5bcb3fb48fe008c2ec66f4305510e5b7edabb2a2b2e472 4548317
off-site: staging39-before-search-contrast-20261111-062911-2323.sql -> offsite-2026-11/staging39-before-search-contrast-20261111-062911-2323.sql.age (4549621 bytes, read back identical; plaintext sha256 8581a3d5cc7e...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Verified exact backup on server, local copy and encrypted off-site readback: staging39-before-search-contrast-20261111-062911-2323.sql (4548317 bytes, sha256 8581a3d5cc7eb0eacc5bcb3fb48fe008c2ec66f4305510e5b7edabb2a2b2e472)
Error: WWM_BACKUP_DIR must be a real writable private directory outside the web root. Stop and inspect before proceeding.
staging39 is www/staging39.thaisolitude.com/public_html
Success: search-contrast ready; css_sha256=2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae; nothing changed.
Missing or duplicate SEARCH-SNAPSHOT marker; refusing to guess.
Apply failed (1); actual snapshot sync exit 1. No rendered verification claimed.
```

**RESULT: FAIL** - push-search-contrast.sh failed (exit 1)

_finished 2026-11-11 06:29:20 UTC_

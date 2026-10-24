# Site op `backup-check-1`

| | |
|---|---|
| operation | `backup` on `staging36` |
| lane | ops (site-ops.yml) |
| commit | `4b829bc8fc2addfd07a32b54a977e153a9a037f4` on `fix/2026-10-24-secrets-script-mode` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40067057684 |
| started | 2026-10-24 05:38:56 UTC |

## backup of staging36 (DB export on the server + encrypted off-site copy): PASS

```text
staging36 is www/staging36.thaisolitude.com/public_html; fresh database export: private-backups/staging36-manual-20261024-053902.sql (6065123 bytes)
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: created draft release offsite-2026-10
off-site: staging36-manual-20261024-053902.sql -> offsite-2026-10/staging36-manual-20261024-053902.sql.age (6066795 bytes, read back identical; plaintext sha256 da54e63539b7...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 0 already there.
```

**RESULT: PASS** - backup taken and stored off-site

_finished 2026-10-24 05:39:08 UTC_

# Site op `apply-link-ideas-staging39-20261108`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-link-ideas.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `f0c79b91f5977d7e5c2ac792029225720ab1d93a` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42429634695 |
| started | 2026-11-08 00:25:05 UTC |

## push-link-ideas.sh --apply(S_TARGET=staging39): FAIL (exit 1)

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-link-ideas (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-link-ideas-20261108-002515.sql (4049732 bytes)
Success: ready: no page with slug in-page-link-ideas (content 31613 bytes, sha256 be8a4502acbf).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-link-ideas-20261108-002515.sql -> offsite-2026-11/staging39-before-link-ideas-20261108-002515.sql.age (4050908 bytes, read back identical; plaintext sha256 3c17f577e856...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added page #1339 (/in-page-link-ideas/). Before-snapshot: /home/<ssh-user>/private-backups/link-ideas-before-20261108-002530-17bcd97c.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /in-page-link-ideas/
```

**RESULT: FAIL** - push-link-ideas.sh failed (exit 1)

_finished 2026-11-08 00:25:39 UTC_

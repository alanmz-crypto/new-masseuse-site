# Site op `rate-apply-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `corporate/push-corporate-rate.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `92143d17e40f8b544ace59a6b2275584fd0aa028` on `fix/2026-10-25-corporate-rate-100` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40163875088 |
| started | 2026-10-25 04:59:33 UTC |

## push-corporate-rate.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-corporate-rate (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-corporate-rate-20261025-045940.sql (4518792 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready rate line=ready FAQ answer=ready
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 9 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 9 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-corporate-rate-20261025-045940.sql -> offsite-2026-10/staging38-before-corporate-rate-20261025-045940.sql.age (4520080 bytes, read back identical; plaintext sha256 58c418df71ef...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 8 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Changed to $100: rate line, FAQ answer (page #1266). Before-snapshot: /home/<ssh-user>/private-backups/corporate-rate-before-20261025-045952.json
SiteGround cache purged
[4b] Does the public corporate page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
  200 /corporate-massage-portland/
public check: the corporate page says $100 per service hour in the rate line and in the FAQ answer, and $130 nowhere
[5] Copying the new before-snapshot to this computer:
Copying the 10 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 10 file(s), identical sha256 on the server and in the copy.
off-site: corporate-rate-before-20261025-045952.json -> offsite-2026-10/corporate-rate-before-20261025-045952.json.age (18679 bytes, read back identical; plaintext sha256 b13902f5228c...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 9 already there.
```

**RESULT: PASS** - push-corporate-rate.sh succeeded

_finished 2026-10-25 05:00:03 UTC_

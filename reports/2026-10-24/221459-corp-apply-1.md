# Site op `corp-apply-1`

| | |
|---|---|
| operation | `run` on `staging37` |
| script | `corporate/push-corporate-massage.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `61293b81ce19e97e47f4ccd54d6605946924e4ac` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40137685098 |
| started | 2026-10-24 22:14:30 UTC |

## push-corporate-massage.sh --apply(S_TARGET=staging37): FAIL (exit 1)

```text
Target site: staging37   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-corporate-massage (outside every web root) ...
[2] Backup on the server + preflight:
staging37 is www/staging37.thaisolitude.com/public_html
fresh database export: private-backups/staging37-before-corporate-massage-20261024-221437.sql (3954353 bytes)
Success: Preflight passed; nothing changed. host=http://staging37.thaisolitude.com state=ready page=absent services_row=absent menu_item=absent snippet=absent css_block=absent
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 11 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 11 file(s), identical sha256 on the server and in the copy.
off-site: staging37-before-corporate-massage-20261024-221437.sql -> offsite-2026-10/staging37-before-corporate-massage-20261024-221437.sql.age (3955513 bytes, read back identical; plaintext sha256 11512474f511...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 10 already there.
[4] Applying:
staging37 is www/staging37.thaisolitude.com/public_html
Success: Added page #1266 (/corporate-massage-portland/), the Services row, menu item #1270, snippet #1268 "CorporateBand" and the ts-corp CSS block. Before-snapshot: /home/<ssh-user>/private-backups/corporate-massage-before-20261024-221448.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging37 is www/staging37.thaisolitude.com/public_html
  200 /corporate-massage-portland/
  200 /services/
  200 /about/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /
  200 /work-with-me/
  200 /personal-automation/
  301 /privacy/
/privacy/ does not answer 200 (202 would be SiteGround's bot challenge = INVALID, retry later); use --rollback if it persists.
```

**RESULT: FAIL** - push-corporate-massage.sh failed (exit 1)

_finished 2026-10-24 22:14:58 UTC_

# Site op `corp-apply-2`

| | |
|---|---|
| operation | `run` on `staging37` |
| script | `corporate/push-corporate-massage.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `bcd571d28a0ea2195bd16d8034fea11f358e80bb` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40143726026 |
| started | 2026-10-24 23:41:07 UTC |

## push-corporate-massage.sh --apply(S_TARGET=staging37): PASS

```text
Target site: staging37   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-corporate-massage (outside every web root) ...
[2] Backup on the server + preflight:
staging37 is www/staging37.thaisolitude.com/public_html
fresh database export: private-backups/staging37-before-corporate-massage-20261024-234114.sql (4143190 bytes)
Success: Preflight passed; nothing changed. host=http://staging37.thaisolitude.com state=ready page=absent picture=absent services_row=absent menu_item=absent snippet=absent css_block=absent
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 5 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 5 file(s), identical sha256 on the server and in the copy.
off-site: corporate-massage-before-20261024-221448.json -> offsite-2026-10/corporate-massage-before-20261024-221448.json.age (88822 bytes, read back identical; plaintext sha256 1745f64d010f...)
off-site: staging37-before-corporate-massage-20261024-234114.sql -> offsite-2026-10/staging37-before-corporate-massage-20261024-234114.sql.age (4144398 bytes, read back identical; plaintext sha256 9fb4d583754b...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-10, 3 already there.
[4] Applying:
staging37 is www/staging37.thaisolitude.com/public_html
Success: Added page #1273 (/corporate-massage-portland/), picture #1276, the Services row, menu item #1278, snippet #1275 "CorporateBand" and the ts-corp CSS block. Before-snapshot: /home/<ssh-user>/private-backups/corporate-massage-before-20261024-234130.json
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
  200 image/webp /wp-content/uploads/2026/10/corporate-group-massage.webp (the picture)
  200 /
  200 /work-with-me/
  200 /personal-automation/ (unrelated page)
  301 /privacy/ (unrelated page)
  301 /terms/ (unrelated page)
public check: the page, the Services row and its picture, the menu link (header, phone menu, footer), the strip on the 8 client pages, and no strip on Home, Work With Me, the new page itself or the unrelated pages
[5] Copying the new before-snapshot to this computer:
Copying the 6 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 6 file(s), identical sha256 on the server and in the copy.
off-site: corporate-massage-before-20261024-234130.json -> offsite-2026-10/corporate-massage-before-20261024-234130.json.age (88843 bytes, read back identical; plaintext sha256 c358a7cbd701...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 5 already there.
```

**RESULT: PASS** - push-corporate-massage.sh succeeded

_finished 2026-10-24 23:41:46 UTC_

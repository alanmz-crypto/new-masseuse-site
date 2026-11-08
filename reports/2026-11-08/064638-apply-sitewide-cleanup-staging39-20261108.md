# Site op `apply-sitewide-cleanup-staging39-20261108`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-sitewide-cleanup.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `8f4f27ac0b760c1053b2c93595fe6f169f1a011f` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42456021976 |
| started | 2026-11-08 06:45:25 UTC |

## push-sitewide-cleanup.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-sitewide-cleanup (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-sitewide-cleanup-20261108-064533.sql (4288829 bytes)
Success: ready: reading-progress blocks: 1, scroll-to-top-enable: true (CSS 69937 bytes, sha256 1b9d4a6fcc78).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 20 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 20 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-sitewide-cleanup-20261108-064533.sql -> offsite-2026-11/staging39-before-sitewide-cleanup-20261108-064533.sql.age (4290069 bytes, read back identical; plaintext sha256 4dc4b7c66ef9...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 19 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Removed the reading-progress block (1095 bytes) from the Additional CSS and switched off scroll-to-top-enable. Before-snapshot: /home/<ssh-user>/private-backups/sitewide-cleanup-before-20261108-064546-26530c7b.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /
  ok   home: 0 x ts-reading-progress
  ok   home: 0 x id="ast-scroll-top"
  200 /about/
  ok   about: 0 x ts-reading-progress
  ok   about: 0 x id="ast-scroll-top"
  200 /services/
  ok   services: 0 x ts-reading-progress
  ok   services: 0 x id="ast-scroll-top"
  200 /thai-massage/
  ok   thai: 0 x ts-reading-progress
  ok   thai: 0 x id="ast-scroll-top"
  200 /contact/
  ok   contact: 0 x ts-reading-progress
  ok   contact: 0 x id="ast-scroll-top"
  200 /therapeutic-massage/
  ok   therapeutic: 0 x ts-reading-progress
  ok   therapeutic: 0 x id="ast-scroll-top"
public check: six pages answer 200 with no reading-progress bar and no scroll-to-top button
[5] Copying the new before-snapshot off the server:
Copying the 21 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 21 file(s), identical sha256 on the server and in the copy.
off-site: sitewide-cleanup-before-20261108-064546-26530c7b.json -> offsite-2026-11/sitewide-cleanup-before-20261108-064546-26530c7b.json.age (72383 bytes, read back identical; plaintext sha256 0072c856bd79...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 20 already there.
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/post-change-test.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [minutes]
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/prove-restore.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [export.sql]
```

**RESULT: PASS** - push-sitewide-cleanup.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 06:46:37 UTC_

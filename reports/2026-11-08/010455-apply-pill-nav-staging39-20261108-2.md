# Site op `apply-pill-nav-staging39-20261108-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-pill-nav.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `6fab6b2ea1efbe9bb62df6d8583ab9f45080c5e8` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42432555451 |
| started | 2026-11-08 01:03:38 UTC |

## push-pill-nav.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-pill-nav (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-pill-nav-20261108-010346.sql (4088739 bytes)
Success: ready: no pill-nav snippet (code 7878 bytes, sha256 53264f7dcc2f).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: pill-nav-before-20261108-005626-17cd4ca5.json -> offsite-2026-11/pill-nav-before-20261108-005626-17cd4ca5.json.age (461 bytes, read back identical; plaintext sha256 dea9c2e78ad1...)
off-site: staging39-before-pill-nav-20261108-010346.sql -> offsite-2026-11/staging39-before-pill-nav-20261108-010346.sql.age (4089931 bytes, read back identical; plaintext sha256 03b301e78573...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-11, 5 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added WPCode snippet #1342 (TSPillNav practice (Thai, staging39)). Before-snapshot: /home/<ssh-user>/private-backups/pill-nav-before-20261108-010400-f14ff46f.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /thai-massage/
  ok   thai: 1 x id="ts-pill-nav-css"
  ok   thai: 1 x id="ts-pill-nav-runtime"
  ok   thai: 1 x class="ts-jump"
  ok   thai: 1 x #masthead{position:sticky
  200 /therapeutic-massage/
  ok   therapeutic-massage: 0 x ts-pill-nav
  200 /deep-tissue-massage/
  ok   deep-tissue-massage: 0 x ts-pill-nav
  200 /services/
  ok   services: 0 x ts-pill-nav
public check: Thai Massage carries the pill style and script once; the other service pages carry none
[5] Copying the new before-snapshot off the server:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: pill-nav-before-20261108-010400-f14ff46f.json -> offsite-2026-11/pill-nav-before-20261108-010400-f14ff46f.json.age (461 bytes, read back identical; plaintext sha256 0936484d9491...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 7 already there.
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/post-change-test.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [minutes]
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/prove-restore.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [export.sql]
```

**RESULT: PASS** - push-pill-nav.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 01:04:54 UTC_

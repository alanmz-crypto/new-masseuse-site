# Site op `apply-peek-nav-s39-21-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-nav.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `89788218cf0ec3df7cd4dc159d54fbbb7c2a96f8` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42470952515 |
| started | 2026-11-08 10:20:06 UTC |

## push-peek-nav.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-peek-nav (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-peek-nav-20261108-102015.sql (4277494 bytes)
Success: ready: no peek-nav snippet (code 50727 bytes, sha256 516668e363cc).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 21 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 21 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-peek-nav-20261108-102015.sql -> offsite-2026-11/staging39-before-peek-nav-20261108-102015.sql.age (4278734 bytes, read back identical; plaintext sha256 c4ccf384e89f...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 20 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added WPCode snippet #1365 (TSPeekNav practice (Thai, staging39)). Before-snapshot: /home/<ssh-user>/private-backups/peek-nav-before-20261108-102027-0eb240af.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /thai-massage/
  ok   thai: 1 x id="ts-peek-nav-css"
  ok   thai: 1 x id="ts-peek-nav-runtime"
  ok   thai: 1 x class="ts-jump"
  ok   thai: 0 x ts-pill-nav
  200 /therapeutic-massage/
  ok   therapeutic-massage: 0 x ts-peek-nav
  200 /deep-tissue-massage/
  ok   deep-tissue-massage: 0 x ts-peek-nav
  200 /services/
  ok   services: 0 x ts-peek-nav
public check: Thai Massage carries the peek style and script once and no pills; the other service pages carry none
[5] Copying the new before-snapshot off the server:
Copying the 22 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 22 file(s), identical sha256 on the server and in the copy.
off-site: peek-nav-before-20261108-102027-0eb240af.json -> offsite-2026-11/peek-nav-before-20261108-102027-0eb240af.json.age (461 bytes, read back identical; plaintext sha256 e1ea51e40fbb...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 21 already there.
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/post-change-test.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [minutes]
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/prove-restore.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [export.sql]
```

**RESULT: PASS** - push-peek-nav.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 10:21:17 UTC_

# Site op `apply-peek-nav-s39-23-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-nav.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `a127c2bb2dd9be0441e437782c48bf3a27f030ac` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42501389708 |
| started | 2026-11-08 16:44:17 UTC |

## push-peek-nav.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-peek-nav (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-peek-nav-20261108-164427.sql (4277822 bytes)
Success: ready: no peek-nav snippet (code 53403 bytes, sha256 1f55a2d194be).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-peek-nav-20261108-164427.sql -> offsite-2026-11/staging39-before-peek-nav-20261108-164427.sql.age (4279062 bytes, read back identical; plaintext sha256 55bc53792bf3...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 2 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added WPCode snippet #1367 (TSPeekNav practice (Thai, staging39)). Before-snapshot: /home/<ssh-user>/private-backups/peek-nav-before-20261108-164442-bbf19019.json
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
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: peek-nav-before-20261108-164442-bbf19019.json -> offsite-2026-11/peek-nav-before-20261108-164442-bbf19019.json.age (461 bytes, read back identical; plaintext sha256 9536cefffe02...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 3 already there.
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

_finished 2026-11-08 16:45:36 UTC_

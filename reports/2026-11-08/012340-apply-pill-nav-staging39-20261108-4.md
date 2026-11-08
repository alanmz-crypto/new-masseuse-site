# Site op `apply-pill-nav-staging39-20261108-4`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-pill-nav.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `d1d4f966723d333b41857ef65c776581f3bea7b9` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42433944714 |
| started | 2026-11-08 01:22:14 UTC |

## push-pill-nav.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-pill-nav (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-pill-nav-20261108-012223.sql (4089060 bytes)
Success: ready: no pill-nav snippet (code 9274 bytes, sha256 0cc9f53fdb6a).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 11 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 11 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-pill-nav-20261108-012223.sql -> offsite-2026-11/staging39-before-pill-nav-20261108-012223.sql.age (4090252 bytes, read back identical; plaintext sha256 e7e88092d1ba...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 10 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added WPCode snippet #1344 (TSPillNav practice (Thai, staging39)). Before-snapshot: /home/<ssh-user>/private-backups/pill-nav-before-20261108-012240-f7bbde39.json
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
Copying the 12 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 12 file(s), identical sha256 on the server and in the copy.
off-site: pill-nav-before-20261108-012240-f7bbde39.json -> offsite-2026-11/pill-nav-before-20261108-012240-f7bbde39.json.age (461 bytes, read back identical; plaintext sha256 3cb17fa98eae...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 11 already there.
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

_finished 2026-11-08 01:23:40 UTC_

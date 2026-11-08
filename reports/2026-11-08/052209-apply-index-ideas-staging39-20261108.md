# Site op `apply-index-ideas-staging39-20261108`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-index-ideas.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `7e633f747f5b160cf7835d71a3ced930adf3503d` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42450411471 |
| started | 2026-11-08 05:20:59 UTC |

## push-index-ideas.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-index-ideas (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-index-ideas-20261108-052106.sql (4253050 bytes)
Success: ready: no page with slug index-nav-ideas (content 20665 bytes, sha256 1a15327de7ee).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 22 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 22 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-index-ideas-20261108-052106.sql -> offsite-2026-11/staging39-before-index-ideas-20261108-052106.sql.age (4254274 bytes, read back identical; plaintext sha256 bdba83349d97...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 21 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added page #1354 (/index-nav-ideas/). Before-snapshot: /home/<ssh-user>/private-backups/index-ideas-before-20261108-052120-93294332.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /index-nav-ideas/
  ok   1 x id="tsi-lab"
  ok   1 x <h1>Index icon ideas</h1>
  ok   5 x data-idea="
  ok   5 x class="tsi-sec"
  ok   1 x document.getElementById('tsi-lab')
  ok   1 x .tsi-lab .tsi-nav{position:fixed
  ok   the page script is unaltered
public check: the page answers 200 with its heading, 5 designs, 5 sample sections, and its style and script intact
[5] Copying the new before-snapshot off the server:
Copying the 23 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 23 file(s), identical sha256 on the server and in the copy.
off-site: index-ideas-before-20261108-052120-93294332.json -> offsite-2026-11/index-ideas-before-20261108-052120-93294332.json.age (439 bytes, read back identical; plaintext sha256 ba491c7db994...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 22 already there.
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/post-change-test.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [minutes]
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/prove-restore.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [export.sql]
```

**RESULT: PASS** - push-index-ideas.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 05:22:08 UTC_

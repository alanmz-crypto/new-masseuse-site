# Site op `apply-rail-ideas-staging39-20261108-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-rail-ideas.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `14dac1c28dc3562e36de4d2501fb2ab90a0f7db1` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42439607006 |
| started | 2026-11-08 02:41:46 UTC |

## push-rail-ideas.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-rail-ideas (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-rail-ideas-20261108-024155.sql (4153990 bytes)
Success: ready: no page with slug rail-nav-ideas (content 36892 bytes, sha256 0203ac81f0ec).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 19 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 19 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-rail-ideas-20261108-024155.sql -> offsite-2026-11/staging39-before-rail-ideas-20261108-024155.sql.age (4155198 bytes, read back identical; plaintext sha256 f9a6d3b9a9ec...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 18 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added page #1348 (/rail-nav-ideas/). Before-snapshot: /home/<ssh-user>/private-backups/rail-ideas-before-20261108-024214-c7b6cfab.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /rail-nav-ideas/
  ok   1 x id="tsr-lab"
  ok   1 x <h1>Side rail ideas</h1>
  ok   5 x data-idea="
  ok   6 x class="tsr-sec"
  ok   1 x document.getElementById('tsr-lab')
  ok   1 x .tsr-lab .tsr-nav{position:fixed
  ok   the page script is unaltered
public check: the page answers 200 with its heading, 5 ideas, 6 sample sections, and its style and script intact
[5] Copying the new before-snapshot off the server:
Copying the 20 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 20 file(s), identical sha256 on the server and in the copy.
off-site: rail-ideas-before-20261108-024214-c7b6cfab.json -> offsite-2026-11/rail-ideas-before-20261108-024214-c7b6cfab.json.age (438 bytes, read back identical; plaintext sha256 0903c34e69ec...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 19 already there.
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/post-change-test.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [minutes]
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/prove-restore.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [export.sql]
```

**RESULT: PASS** - push-rail-ideas.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 02:43:03 UTC_

# Site op `apply-link-ideas-staging39-20261108-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-link-ideas.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `1ddaa14b2c88c314a06f46bf38e70acee4e83d31` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42430349995 |
| started | 2026-11-08 00:34:31 UTC |

## push-link-ideas.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-link-ideas (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-link-ideas-20261108-003442.sql (4049732 bytes)
Success: ready: no page with slug in-page-link-ideas (content 31664 bytes, sha256 47c30afbc4db).
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: link-ideas-before-20261108-002530-17bcd97c.json -> offsite-2026-11/link-ideas-before-20261108-002530-17bcd97c.json.age (442 bytes, read back identical; plaintext sha256 87e269002cea...)
off-site: staging39-before-link-ideas-20261108-003442.sql -> offsite-2026-11/staging39-before-link-ideas-20261108-003442.sql.age (4050908 bytes, read back identical; plaintext sha256 a55ade9344ee...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Added page #1340 (/in-page-link-ideas/). Before-snapshot: /home/<ssh-user>/private-backups/link-ideas-before-20261108-003500-3f7cf593.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /in-page-link-ideas/
  ok   1 x id="tsx-lab"
  ok   1 x <h1>In-page link ideas</h1>
  ok   10 x data-idea="
  ok   6 x class="tsx-sec"
  ok   1 x document.getElementById('tsx-lab')
  ok   1 x .tsx-lab .tsx-pills{position:sticky
  ok   the page script is unaltered
public check: the page answers 200 with its heading, 5 ideas, 6 sample sections, and its style and script intact
[5] Copying the new before-snapshot off the server:
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: link-ideas-before-20261108-003500-3f7cf593.json -> offsite-2026-11/link-ideas-before-20261108-003500-3f7cf593.json.age (442 bytes, read back identical; plaintext sha256 6961a5081a50...)
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

**RESULT: PASS** - push-link-ideas.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 00:35:55 UTC_

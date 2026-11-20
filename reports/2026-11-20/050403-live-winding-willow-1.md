# Site op `live-winding-willow-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `winding-willow/push-winding-willow.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `48a2d18f083dfd2dfecaa4728c516de20516a0d3` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/44677336157 |
| started | 2026-11-20 04:53:25 UTC |

## push-winding-willow.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-winding-willow (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-winding-willow-20261120-045348.sql (4129258 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready page=absent menu_item=absent css_block=absent matching_posts=none (package sha256 c541d49414355e45186987ac8d08d787a47a68395e2c8a268d803adfa29f7683)
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: live-before-winding-willow-20261120-045348.sql -> offsite-2026-11/live-before-winding-willow-20261120-045348.sql.age (4130466 bytes, read back identical; plaintext sha256 b676bae86577...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 7 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Added page #1344 (/winding-willow/, 30729 bytes, sha256 c541d49414355e45186987ac8d08d787a47a68395e2c8a268d803adfa29f7683) with its SureRank title and description, menu item #1346 "Winding Willow" and the ts-ww CSS block. Before-snapshot: /home/<ssh-user>/private-backups/winding-willow-before-20261120-045404.json
SiteGround cache purged
[4b] Does the public page show the change?
live is www/thaisolitude.com/public_html
  200 /winding-willow/
  200 /services/
  200 /contact/
  200 /about/
public check: the page answers 200 with its opening sentence, H1, title, description, licence line, research heading, booking links and 7 PubMed sources; Services, Contact and About carry only the menu link to it and no link to Winding Willow
[5] Copying the new before-snapshot off the server:
Copying the 9 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 9 file(s), identical sha256 on the server and in the copy.
off-site: winding-willow-before-20261120-045404.json -> offsite-2026-11/winding-willow-before-20261120-045404.json.age (71883 bytes, read back identical; plaintext sha256 b14b181b8ced...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 8 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-11-20 05:03 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-winding-willow-20261120-045348.sql    modified 2026-11-20 04:53:49
Unable to find image 'mariadb:10.6@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16' locally
docker.io/library/mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16: Pulling from library/mariadb
20c3783cc497: Pulling fs layer
82b15426938b: Pulling fs layer
b2a602564ec4: Pulling fs layer
bdc186c1268d: Pulling fs layer
5b0e31685a7d: Pulling fs layer
5d5076a693b9: Pulling fs layer
650d43fe7026: Pulling fs layer
4f693d0ae952: Pulling fs layer
bdc186c1268d: Waiting
5b0e31685a7d: Waiting
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
4f693d0ae952: Download complete
5d5076a693b9: Verifying Checksum
5d5076a693b9: Download complete
20c3783cc497: Pull complete
82b15426938b: Pull complete
b2a602564ec4: Pull complete
bdc186c1268d: Pull complete
5b0e31685a7d: Pull complete
5d5076a693b9: Pull complete
650d43fe7026: Pull complete
4f693d0ae952: Pull complete
Digest: sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
Status: Downloaded newer image for mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
import exit code: 0
fingerprints: restored copy 100, real site 102
compared: css 1, option 12, page 16, rows 21, seo 22, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_term_relationships
> rows	iej_term_relationships
> page	winding-willow
< css	29
> css	29
> seo	1344:surerank_settings_general
```

**RESULT: PASS** - push-winding-willow.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-20 05:04:01 UTC_

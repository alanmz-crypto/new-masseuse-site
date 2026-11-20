# Site op `ww-menu-apply-s40-1`

| | |
|---|---|
| operation | `run` on `staging40` |
| script | `winding-willow/push-winding-willow.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `a5de36f53022abd8c3fd38c43f7c6e6b89117d5d` on `feat/2026-11-20-winding-willow-menu` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/44662248895 |
| started | 2026-11-20 02:38:51 UTC |

## push-winding-willow.sh --apply(S_TARGET=staging40): PASS

```text
Target site: staging40   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-winding-willow (outside every web root) ...
[2] Backup on the server + preflight:
staging40 is www/staging40.thaisolitude.com/public_html
fresh database export: private-backups/staging40-before-winding-willow-20261120-023909.sql (4221150 bytes)
Success: Preflight passed; nothing changed. host=http://staging40.thaisolitude.com state=upgrade page=previous menu_item=absent css_block=absent matching_posts=1344 (package sha256 c541d49414355e45186987ac8d08d787a47a68395e2c8a268d803adfa29f7683)
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: staging40-before-winding-willow-20261120-023909.sql -> offsite-2026-11/staging40-before-winding-willow-20261120-023909.sql.age (4222374 bytes, read back identical; plaintext sha256 2fa2431cabef...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 3 already there.
[4] Applying:
staging40 is www/staging40.thaisolitude.com/public_html
Success: Updated page #1344 (/winding-willow/, 30729 bytes, sha256 c541d49414355e45186987ac8d08d787a47a68395e2c8a268d803adfa29f7683), menu item #1347 "Winding Willow" and the ts-ww CSS block. Before-snapshot: /home/<ssh-user>/private-backups/winding-willow-before-20261120-023925.json
SiteGround cache purged
[4b] Does the public page show the change?
staging40 is www/staging40.thaisolitude.com/public_html
  200 /winding-willow/
  200 /services/
  200 /contact/
  200 /about/
public check: the page answers 200 with its opening sentence, H1, title, description, licence line, research heading, booking links and 7 PubMed sources; Services, Contact and About carry only the menu link to it and no link to Winding Willow
[5] Copying the new before-snapshot off the server:
Copying the 5 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 5 file(s), identical sha256 on the server and in the copy.
off-site: winding-willow-before-20261120-023925.json -> offsite-2026-11/winding-willow-before-20261120-023925.json.age (104544 bytes, read back identical; plaintext sha256 5ddb7f8a6c96...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 4 already there.
```

## post-change-test.sh staging40 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging40 (https://staging40.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-20 02:48 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging40.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging40 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging40 (staging40.thaisolitude.com)    export: staging40-before-winding-willow-20261120-023909.sql    modified 2026-11-20 02:39:09
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
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
bdc186c1268d: Waiting
5b0e31685a7d: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
4f693d0ae952: Verifying Checksum
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
fingerprints: restored copy 102, real site 102
compared: css 1, option 12, page 16, rows 21, seo 22, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_term_relationships
> rows	iej_term_relationships
< page	winding-willow
> page	winding-willow
< css	29
> css	29
```

**RESULT: PASS** - push-winding-willow.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-20 02:48:42 UTC_

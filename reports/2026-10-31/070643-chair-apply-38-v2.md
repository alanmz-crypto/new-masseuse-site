# Site op `chair-apply-38-v2`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `chair-massage/push-chair-massage.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `a3de4ee983e07c426978cd8382edac05649c08ef` on `feat/2026-10-30-chair-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41179170312 |
| started | 2026-10-31 07:05:12 UTC |

## push-chair-massage.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-chair-massage (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-chair-massage-20261031-070520.sql (5021331 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready picture=absent page=absent services_row=absent strip_snippet=ready jump_snippet=ready
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: chair-massage-before-20261031-050339.json -> offsite-2026-10/chair-massage-before-20261031-050339.json.age (49184 bytes, read back identical; plaintext sha256 8761afcd4d7b...)
off-site: staging38-before-chair-massage-20261031-070520.sql -> offsite-2026-10/staging38-before-chair-massage-20261031-070520.sql.age (5022747 bytes, read back identical; plaintext sha256 07f37e963898...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-10, 1 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Added page #1298 (/chair-massage/), picture #1297, the Services row, and 'chair-massage' in snippets #1268 and #1232. Before-snapshot: /home/<ssh-user>/private-backups/chair-massage-before-20261031-070533.json
SiteGround cache purged
[4b] Do the public pages show the change?
staging38 is www/staging38.thaisolitude.com/public_html
  200 /chair-massage/
  200 image/webp /wp-content/uploads/2026/10/chair-massage-tree.webp (the picture)
  200 /services/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /thai-massage/
  200 /about/
  200 /corporate-massage-portland/
public check: the page (title, description, strip, jump links, FAQ and research headings, 32 sources, 11 questions, the one picture as the hero), the Services row with the same picture, and the strip/jump links unchanged elsewhere
[5] Copying the new before-snapshot off the server:
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: chair-massage-before-20261031-070533.json -> offsite-2026-10/chair-massage-before-20261031-070533.json.age (49154 bytes, read back identical; plaintext sha256 03eb60b7fc52...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 3 already there.
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-31 07:06 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging38.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-chair-massage-20261031-070520.sql    modified 2026-10-31 07:05:20
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
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
5b0e31685a7d: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
fingerprints: restored copy 86, real site 88
compared: css 1, option 12, page 15, rows 21, seo 19, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
> page	chair-massage
< page	services
> page	services
< snippet	1232
> snippet	1232
< snippet	1268
> snippet	1268
> seo	1298:surerank_settings_general
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-chair-massage.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-31 07:06:43 UTC_

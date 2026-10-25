# Site op `live-home-corporate-card-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `home-card/push-home-corporate-card.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `4e010dd54a359f177b2baf1e5bc78cecd1e0b278` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40162072141 |
| started | 2026-10-25 04:29:54 UTC |

## push-home-corporate-card.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-home-corporate-card (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-home-corporate-card-20261025-043011.sql (4142640 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready page=#296 card=ready (anchor=1 old-pinned=1 card-in-front=0 card-id=0 heading=1) css_block=absent picture=media#1269
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: live-before-home-corporate-card-20261025-043011.sql -> offsite-2026-10/live-before-home-corporate-card-20261025-043011.sql.age (4143848 bytes, read back identical; plaintext sha256 ad9ec4d46771...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 6 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Inserted the card at the top of "The Massage I Offer" on the Home page #296 and added the ts-home-corp CSS block. Before-snapshot: /home/<ssh-user>/private-backups/home-corporate-card-before-20261025-043027.json
SiteGround cache purged
[4b] Does the public Home page show the change?
live is www/thaisolitude.com/public_html
  200 /
  200 /wp-content/uploads/2026/10/corporate-group-massage.webp
public check: the Corporate & Group Massage card is the first of the four on the Home page (title, description, button, picture, frame CSS), the three existing cards are unchanged in place
[5] Copying the new before-snapshot to this computer:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: home-corporate-card-before-20261025-043027.json -> offsite-2026-10/home-corporate-card-before-20261025-043027.json.age (130506 bytes, read back identical; plaintext sha256 ca9bb60915e1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 7 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-25 04:31 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-home-corporate-card-20261025-043011.sql    modified 2026-10-25 04:30:11
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
5b0e31685a7d: Waiting
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
bdc186c1268d: Waiting
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
fingerprints: restored copy 85, real site 85
compared: css 1, option 12, page 14, rows 21, seo 17, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< page	home
> page	home
< css	29
> css	29
```

**RESULT: PASS** - push-home-corporate-card.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-25 04:31:46 UTC_

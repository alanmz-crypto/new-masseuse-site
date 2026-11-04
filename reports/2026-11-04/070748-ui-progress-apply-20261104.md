# Site op `ui-progress-apply-20261104`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `reading-progress/push-reading-progress.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `f9680d000ee2c473a7bc7e01d69c6a66917e5a4e` on `feat/2026-11-04-reading-progress` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41789895581 |
| started | 2026-11-04 07:06:05 UTC |

## push-reading-progress.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-reading-progress (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-reading-progress-20261104-070616.sql (7141233 bytes)
Success: Reading progress: ready; CSS bytes=65418; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-reading-progress-20261104-070616.sql -> offsite-2026-11/staging38-before-reading-progress-20261104-070616.sql.age (7143161 bytes, read back identical; plaintext sha256 bd5c13f097de...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: Reading progress applied. Before-snapshot: /home/customer/private-backups/reading-progress-before-20261104-070633-b4678593.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: reading-progress-before-20261104-070633-b4678593.json -> offsite-2026-11/reading-progress-before-20261104-070633-b4678593.json.age (67751 bytes, read back identical; plaintext sha256 45525b9f3923...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
[6] Does the public page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
public check: thai-massage HTTP 200; CSS, citation scope and licence present
public check: therapeutic-massage HTTP 200; CSS, citation scope and licence present
public check: deep-tissue-massage HTTP 200; CSS, citation scope and licence present
public check: chair-massage HTTP 200; CSS, citation scope and licence present
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-04 07:07 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging38.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-reading-progress-20261104-070616.sql    modified 2026-11-04 07:06:16
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
fingerprints: restored copy 94, real site 94
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 24
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< css	29
> css	29
```

**RESULT: PASS** - push-reading-progress.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-04 07:07:47 UTC_

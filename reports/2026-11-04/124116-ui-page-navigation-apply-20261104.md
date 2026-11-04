# Site op `ui-page-navigation-apply-20261104`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `page-navigation/push-page-navigation.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `6d0d9f1d06715dbdc058f3fb6e959dd296dd5939` on `feat/2026-11-04-page-navigation` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831164491 |
| started | 2026-11-04 12:39:42 UTC |

## push-page-navigation.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-page-navigation (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-page-navigation-20261104-123950.sql (7371584 bytes)
home=http://staging38.thaisolitude.com page-navigation=ready CSS bytes=69937
Success: Preflight ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 10 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 10 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-page-navigation-20261104-123950.sql -> offsite-2026-11/staging38-before-page-navigation-20261104-123950.sql.age (7373576 bytes, read back identical; plaintext sha256 c04e8ecde7c4...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 9 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
home=http://staging38.thaisolitude.com page-navigation=ready CSS bytes=69937
Success: Page navigation applied to both existing snippets; CSS unchanged. Before-snapshot: /home/customer/private-backups/page-navigation-before-20261104-124003-a437350c.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 11 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 11 file(s), identical sha256 on the server and in the copy.
off-site: page-navigation-before-20261104-124003-a437350c.json -> offsite-2026-11/page-navigation-before-20261104-124003-a437350c.json.age (182332 bytes, read back identical; plaintext sha256 1ddb456421a9...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 10 already there.
[6] Does the public page show the change?
staging38 is www/staging38.thaisolitude.com/public_html
public check: / HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /about/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /services/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /thai-massage/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /therapeutic-massage/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /deep-tissue-massage/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /chair-massage/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /contact/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /privacy-policy/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /accessibility/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /corporate-massage-portland/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
public check: /work-with-me/ HTTP 200; licence, unchanged CSS markers and scoped navigation verified
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-04 12:40 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging38.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-page-navigation-20261104-123950.sql    modified 2026-11-04 12:39:50
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
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
fingerprints: restored copy 95, real site 95
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 25
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< snippet	1232
> snippet	1232
< snippet	1349
> snippet	1349
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-page-navigation.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-04 12:41:16 UTC_

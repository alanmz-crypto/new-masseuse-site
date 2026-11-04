# Site op `live-section-guide-20261104-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `section-guide/push-section-guide.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `f392749efac9310fb5acf9980544b354e097a0e1` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41823566103 |
| started | 2026-11-04 11:46:03 UTC |

## push-section-guide.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-section-guide (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-section-guide-20261104-114610.sql (4002608 bytes)
home=http://thaisolitude.com section-guide=ready CSS bytes=66514
Success: Preflight ready; nothing changed.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 10 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 10 file(s), identical sha256 on the server and in the copy.
off-site: live-before-section-guide-20261104-114610.sql -> offsite-2026-11/live-before-section-guide-20261104-114610.sql.age (4003784 bytes, read back identical; plaintext sha256 8a33a892f676...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 9 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
home=http://thaisolitude.com section-guide=ready CSS bytes=66514
Success: Section guide applied. Before-snapshot: /home/customer/private-backups/section-guide-before-20261104-114623-1ca64312.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 11 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 11 file(s), identical sha256 on the server and in the copy.
off-site: section-guide-before-20261104-114623-1ca64312.json -> offsite-2026-11/section-guide-before-20261104-114623-1ca64312.json.age (78571 bytes, read back identical; plaintext sha256 76ca289d81dc...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 10 already there.
[6] Does the public page show the change?
live is www/thaisolitude.com/public_html
public check: /thai-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: /therapeutic-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: /deep-tissue-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: /chair-massage/ HTTP 200; CSS, licence and scoped runtime verified
public check: / HTTP 200; CSS, licence and scoped runtime verified
public check: /contact/ HTTP 200; CSS, licence and scoped runtime verified
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-11-04 11:47 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-section-guide-20261104-114610.sql    modified 2026-11-04 11:46:11
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
fingerprints: restored copy 94, real site 95
compared: css 1, option 12, page 15, rows 21, seo 21, snippet 25
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< rows	iej_term_relationships
> rows	iej_term_relationships
< css	29
> css	29
> snippet	1338
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-section-guide.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-04 11:47:32 UTC_

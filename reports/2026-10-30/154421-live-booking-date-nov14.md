# Site op `live-booking-date-nov14`

| | |
|---|---|
| operation | `run` on `live` |
| script | `booking-date/push-booking-date.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `34d42e750f7e893095eb8ed1fd79fd781ef949f7` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41083174644 |
| started | 2026-10-30 15:42:43 UTC |

## push-booking-date.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-booking-date (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-booking-date-20261030-154251.sql (4000719 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready replacements ready=8 applied=0 changed=0; strip snippet=ready
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 9 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 9 file(s), identical sha256 on the server and in the copy.
off-site: live-before-booking-date-20261030-154251.sql -> offsite-2026-10/live-before-booking-date-20261030-154251.sql.age (4001895 bytes, read back identical; plaintext sha256 ffd27cbb37c6...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 8 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Applied 8 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /home/<ssh-user>/private-backups/booking-date-before-20261030-154304.json
SiteGround cache purged
[4b] Do the public pages show the change?
live is www/thaisolitude.com/public_html
  200 /
  200 /about/
  200 /services/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /corporate-massage-portland/
public check: every page that said bookings begin November 1 now says November 14 (strip on 9 pages, three FAQ pairs, corporate page, Work With Me); the employment dates and For Practices are unchanged
[5] Copying the new before-snapshot to this computer:
Copying the 10 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 10 file(s), identical sha256 on the server and in the copy.
off-site: booking-date-before-20261030-154304.json -> offsite-2026-10/booking-date-before-20261030-154304.json.age (249354 bytes, read back identical; plaintext sha256 c28065341e61...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 9 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-30 15:44 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-booking-date-20261030-154251.sql    modified 2026-10-30 15:42:51
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
fingerprints: restored copy 85, real site 85
compared: css 1, option 12, page 14, rows 21, seo 17, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< page	corporate-massage-portland
< page	deep-tissue-massage
> page	corporate-massage-portland
> page	deep-tissue-massage
< page	thai-massage
< page	therapeutic-massage
< page	work-with-me
> page	thai-massage
> page	therapeutic-massage
> page	work-with-me
< snippet	1268
> snippet	1268
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-booking-date.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-30 15:44:21 UTC_

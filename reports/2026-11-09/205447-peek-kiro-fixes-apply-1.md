# Site op `peek-kiro-fixes-apply-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-nav.sh` `core-kiro-fixes --apply` |
| lane | ops (site-ops.yml) |
| commit | `8f4a3fb415b73cfe8c81071419c6da428f5b032a` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42697003089 |
| started | 2026-11-09 20:52:17 UTC |

## push-peek-nav.sh core-kiro-fixes --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-peek-nav (outside every web root) ...
[2] Backup on the server + preflight:
staging39 is www/staging39.thaisolitude.com/public_html
fresh database export: private-backups/staging39-before-peek-nav-20261109-205236.sql (4519440 bytes)
Success: core-kiro-fixes ready; nothing written.
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-peek-nav-20261109-205236.sql -> offsite-2026-11/staging39-before-peek-nav-20261109-205236.sql.age (4520728 bytes, read back identical; plaintext sha256 cd6f32c1920a...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 2 already there.
[4] Applying:
staging39 is www/staging39.thaisolitude.com/public_html
Published page paths match reviewed title-strip browser coverage.
Success: Peek core-kiro-fixes applied; WordPress storage only. Before-snapshot: /home/customer/private-backups/peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json
SiteGround cache purged
[5] Copying the new before-snapshot off the server:
Copying the 4 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 4 file(s), identical sha256 on the server and in the copy.
off-site: peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json -> offsite-2026-11/peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json.age (226976 bytes, read back identical; plaintext sha256 3d55f4d1ca91...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 3 already there.
[6] Do the public pages show the change?
staging39 is www/staging39.thaisolitude.com/public_html
  200 /
  ok   page-296: 1 x id="ts-peek-nav-css"
  ok   page-296: 1 x id="ts-peek-nav-runtime"
  ok   page-296: 1 x id="ts-peek-nav-config"
  ok   page-296: 0 x id="ts-peek-nav-data"
  ok   page-296: 0 x ts-pill-nav
  200 /about/
  ok   page-298: 1 x id="ts-peek-nav-css"
  ok   page-298: 1 x id="ts-peek-nav-runtime"
  ok   page-298: 1 x id="ts-peek-nav-config"
  ok   page-298: 0 x id="ts-peek-nav-data"
  ok   page-298: 0 x ts-pill-nav
  200 /accessibility/
  ok   page-1230: 1 x id="ts-peek-nav-css"
  ok   page-1230: 1 x id="ts-peek-nav-runtime"
  ok   page-1230: 1 x id="ts-peek-nav-config"
  ok   page-1230: 0 x id="ts-peek-nav-data"
  ok   page-1230: 0 x ts-pill-nav
  200 /chair-massage/
  ok   page-1293: 1 x id="ts-peek-nav-css"
  ok   page-1293: 1 x id="ts-peek-nav-runtime"
  ok   page-1293: 1 x id="ts-peek-nav-config"
  ok   page-1293: 1 x id="ts-peek-nav-data"
  ok   page-1293: 1 x class="ts-jump"
  ok   page-1293: 0 x ts-pill-nav
  200 /contact/
  ok   page-301: 1 x id="ts-peek-nav-css"
  ok   page-301: 1 x id="ts-peek-nav-runtime"
  ok   page-301: 1 x id="ts-peek-nav-config"
  ok   page-301: 0 x id="ts-peek-nav-data"
  ok   page-301: 0 x ts-pill-nav
  200 /corporate-massage-portland/
  ok   page-1266: 1 x id="ts-peek-nav-css"
  ok   page-1266: 1 x id="ts-peek-nav-runtime"
  ok   page-1266: 1 x id="ts-peek-nav-config"
  ok   page-1266: 0 x id="ts-peek-nav-data"
  ok   page-1266: 0 x ts-pill-nav
  200 /deep-tissue-massage/
  ok   page-1091: 1 x id="ts-peek-nav-css"
  ok   page-1091: 1 x id="ts-peek-nav-runtime"
  ok   page-1091: 1 x id="ts-peek-nav-config"
  ok   page-1091: 1 x id="ts-peek-nav-data"
  ok   page-1091: 1 x class="ts-jump"
  ok   page-1091: 0 x ts-pill-nav
  200 /in-page-link-ideas/
  ok   page-1340: 1 x id="ts-peek-nav-css"
  ok   page-1340: 1 x id="ts-peek-nav-runtime"
  ok   page-1340: 1 x id="ts-peek-nav-config"
  ok   page-1340: 0 x id="ts-peek-nav-data"
  ok   page-1340: 0 x ts-pill-nav
  200 /index-nav-ideas/
  ok   page-1354: 1 x id="ts-peek-nav-css"
  ok   page-1354: 1 x id="ts-peek-nav-runtime"
  ok   page-1354: 1 x id="ts-peek-nav-config"
  ok   page-1354: 0 x id="ts-peek-nav-data"
  ok   page-1354: 0 x ts-pill-nav
  200 /personal-automation/
  ok   page-1254: 1 x id="ts-peek-nav-css"
  ok   page-1254: 1 x id="ts-peek-nav-runtime"
  ok   page-1254: 1 x id="ts-peek-nav-config"
  ok   page-1254: 0 x id="ts-peek-nav-data"
  ok   page-1254: 0 x ts-pill-nav
  200 /personal-automation/privacy/
  ok   page-1255: 1 x id="ts-peek-nav-css"
  ok   page-1255: 1 x id="ts-peek-nav-runtime"
  ok   page-1255: 1 x id="ts-peek-nav-config"
  ok   page-1255: 0 x id="ts-peek-nav-data"
  ok   page-1255: 0 x ts-pill-nav
  200 /personal-automation/terms/
  ok   page-1257: 1 x id="ts-peek-nav-css"
  ok   page-1257: 1 x id="ts-peek-nav-runtime"
  ok   page-1257: 1 x id="ts-peek-nav-config"
  ok   page-1257: 0 x id="ts-peek-nav-data"
  ok   page-1257: 0 x ts-pill-nav
  200 /privacy-policy/
  ok   page-1227: 1 x id="ts-peek-nav-css"
  ok   page-1227: 1 x id="ts-peek-nav-runtime"
  ok   page-1227: 1 x id="ts-peek-nav-config"
  ok   page-1227: 0 x id="ts-peek-nav-data"
  ok   page-1227: 0 x ts-pill-nav
  200 /progress-nav-ideas/
  ok   page-1346: 1 x id="ts-peek-nav-css"
  ok   page-1346: 1 x id="ts-peek-nav-runtime"
  ok   page-1346: 1 x id="ts-peek-nav-config"
  ok   page-1346: 0 x id="ts-peek-nav-data"
  ok   page-1346: 0 x ts-pill-nav
  200 /rail-nav-ideas/
  ok   page-1348: 1 x id="ts-peek-nav-css"
  ok   page-1348: 1 x id="ts-peek-nav-runtime"
  ok   page-1348: 1 x id="ts-peek-nav-config"
  ok   page-1348: 0 x id="ts-peek-nav-data"
  ok   page-1348: 0 x ts-pill-nav
  200 /services/
  ok   page-299: 1 x id="ts-peek-nav-css"
  ok   page-299: 1 x id="ts-peek-nav-runtime"
  ok   page-299: 1 x id="ts-peek-nav-config"
  ok   page-299: 0 x id="ts-peek-nav-data"
  ok   page-299: 1 x class="ts-jump"
  ok   page-299: 0 x ts-pill-nav
  200 /thai-massage/
  ok   page-1160: 1 x id="ts-peek-nav-css"
  ok   page-1160: 1 x id="ts-peek-nav-runtime"
  ok   page-1160: 1 x id="ts-peek-nav-config"
  ok   page-1160: 1 x id="ts-peek-nav-data"
  ok   page-1160: 1 x class="ts-jump"
  ok   page-1160: 0 x ts-pill-nav
  200 /therapeutic-massage/
  ok   page-1107: 1 x id="ts-peek-nav-css"
  ok   page-1107: 1 x id="ts-peek-nav-runtime"
  ok   page-1107: 1 x id="ts-peek-nav-config"
  ok   page-1107: 1 x id="ts-peek-nav-data"
  ok   page-1107: 1 x class="ts-jump"
  ok   page-1107: 0 x ts-pill-nav
  200 /work-with-me/
  ok   page-1165: 1 x id="ts-peek-nav-css"
  ok   page-1165: 1 x id="ts-peek-nav-runtime"
  ok   page-1165: 1 x id="ts-peek-nav-config"
  ok   page-1165: 0 x id="ts-peek-nav-data"
  ok   page-1165: 0 x ts-pill-nav
public check: all published pages and home checked; core/data scope matches installed snippets, existing service jump rows retained
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-09 20:54 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging39.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging39 (staging39.thaisolitude.com)    export: staging39-before-peek-nav-20261109-205236.sql    modified 2026-11-09 20:52:37
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
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
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
fingerprints: restored copy 104, real site 104
compared: css 1, option 12, page 19, rows 21, seo 21, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< snippet	1380
> snippet	1380
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-peek-nav.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-09 20:54:46 UTC_

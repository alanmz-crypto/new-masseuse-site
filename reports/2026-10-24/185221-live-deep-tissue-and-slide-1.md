# Site op `live-deep-tissue-and-slide-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `deep-tissue/push-deep-tissue-and-slide.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `701b7ecd7991786804e2493ca66a40b28e71264f` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40122526720 |
| started | 2026-10-24 18:50:23 UTC |

## push-deep-tissue-and-slide.sh --apply(S_TARGET=live): PASS

```text
================ 1 of 2: Deep Tissue research section, FAQ and body paragraph ================
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-deep-tissue-evidence (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-deep-tissue-evidence-20261024-185036.sql (3768912 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready pages_with_slug=1
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 11 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 11 file(s), identical sha256 on the server and in the copy.
off-site: live-before-deep-tissue-evidence-20261024-185036.sql -> offsite-2026-10/live-before-deep-tissue-evidence-20261024-185036.sql.age (3770024 bytes, read back identical; plaintext sha256 a01e55d8f70b...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 10 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Page #1091 (/deep-tissue-massage/): body research paragraph, six FAQ questions, research section and picture tooltips are in. Before-snapshot: /home/<ssh-user>/private-backups/deep-tissue-evidence-before-20261024-185047.json
SiteGround cache purged
[4b] Does the public page show the changes?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/deep-tissue-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   research heading (no duplicate) (1)
  ok   FAQ heading (no duplicate) (1)
  ok   five collapsible panels (5)
  ok   33 source entries (33)
  ok   FAQ: does it have to hurt (1)
  ok   FAQ: difference from therapeutic (1)
  ok   FAQ: choosing the focus (1)
  ok   FAQ: what to tell me before a session (1)
  ok   FAQ: where sessions take place (1)
  ok   FAQ: no travel to clients (1)
  ok   FAQ: how to book (1)
  ok   new body paragraph opens with the honest summary (1)
  ok   old body sentence gone (conditions) (0)
  ok   old body sentence gone (athletes, as a body sentence) (0)
  ok   the page's first paragraph is still there once (1)
  ok   'myofascial' appears only in the verbatim source title (1)
  ok   no picture tooltip says 'by Butsaba' any more (0)
  ok   safety sentence there twice (page text and FAQ answer) (2)
  ok   licence number in the footer (1)
public check: the new body paragraph, the FAQ, the research section and the picture tooltips show on the page
[5] Copying the new before-snapshot to this computer:
Copying the 12 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 12 file(s), identical sha256 on the server and in the copy.
off-site: deep-tissue-evidence-before-20261024-185047.json -> offsite-2026-10/deep-tissue-evidence-before-20261024-185047.json.age (29360 bytes, read back identical; plaintext sha256 f1f55ac1a81f...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 11 already there.

================ 2 of 2: slide-open CSS for the research panels ================
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-research-slide (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-research-slide-20261024-185104.sql (3884313 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready css_bytes=48765
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 13 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 13 file(s), identical sha256 on the server and in the copy.
off-site: live-before-research-slide-20261024-185104.sql -> offsite-2026-10/live-before-research-slide-20261024-185104.sql.age (3885457 bytes, read back identical; plaintext sha256 219afeaaa8c5...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 12 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Added the ts-research-slide block to the Additional CSS (48765 -> 49805 bytes). Before-snapshot: /home/<ssh-user>/private-backups/research-slide-before-20261024-185115.json
SiteGround cache purged
[4b] Does the public page show the changes?
live is www/thaisolitude.com/public_html
public check: https://thaisolitude.com/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   the slide rule is delivered once (1)
  ok   the animated content rules are delivered (the selector appears at least three times) (1)
  ok   the dark/light button rules are still there once (1)
  ok   licence number in the footer (1)
public check: the research-slide block is delivered with the page CSS
[5] Copying the new before-snapshot to this computer:
Copying the 14 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 14 file(s), identical sha256 on the server and in the copy.
off-site: research-slide-before-20261024-185115.json -> offsite-2026-10/research-slide-before-20261024-185115.json.age (50378 bytes, read back identical; plaintext sha256 429ea440869d...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 13 already there.

Both parts finished (applied).
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-24 18:52 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-research-slide-20261024-185104.sql    modified 2026-10-24 18:51:05
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
fingerprints: restored copy 82, real site 82
compared: css 1, option 12, page 13, rows 21, seo 16, snippet 19
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_posts
> rows	iej_posts
< css	29
> css	29
```

**RESULT: PASS** - push-deep-tissue-and-slide.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-24 18:52:20 UTC_

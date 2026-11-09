# Site op `peek-rail-input-apply-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-rail-input.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `637aeef869b996f2fc7239ca46769298a571c46c` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42724350260 |
| started | 2026-11-09 23:55:59 UTC |

## push-rail-input.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: apply
[1] Uploading the reviewed package outside every web root ...
[2] Original cold-state export:
staging39 is www/staging39.thaisolitude.com/public_html
Private backup directory: configured=/home/<ssh-user>/private-backups canonical=/home/customer/private-backups
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
fresh database export: private-backups/staging39-before-rail-input-cold-20261109-235610-9748.sql (4530739 bytes)
Copying the 5 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 5 file(s), identical sha256 on the server and in the copy.
off-site: rail-input-observe-20261109-234726-256baa80.json -> offsite-2026-11/rail-input-observe-20261109-234726-256baa80.json.age (45312 bytes, read back identical; plaintext sha256 df527f4665a1...)
off-site: staging39-before-rail-input-cold-20261109-235610-9748.sql -> offsite-2026-11/staging39-before-rail-input-cold-20261109-235610-9748.sql.age (4532043 bytes, read back identical; plaintext sha256 aa0e2675c6bb...)
OFF-SITE VERIFIED: 2 file(s) stored encrypted in draft release offsite-2026-11, 3 already there.
[3] Warm the exact original page and prove purge/render stability:
staging39 is www/staging39.thaisolitude.com/public_html
Private backup directory: configured=/home/<ssh-user>/private-backups canonical=/home/customer/private-backups
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation record content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail state recorded read-only. Observation: /home/customer/private-backups/rail-input-observe-20261109-235624-eead56e4.json
SiteGround cache purged
public check: HTTP200, exact stored Rail script/style, five ideas, six sections, shared strip and licence present
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation before content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Rail observation current content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail state unchanged after the baseline read; nothing written.
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation record content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail state recorded read-only. Observation: /home/customer/private-backups/rail-input-observe-20261109-235627-741be55d.json
public check: HTTP200, exact stored Rail script/style, five ideas, six sections, shared strip and licence present
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation before content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Rail observation current content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail full state is exactly stable; nothing written. full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
SiteGround cache purged
public check: HTTP200, exact stored Rail script/style, five ideas, six sections, shared strip and licence present
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation before content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Rail observation current content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail full state is exactly stable; nothing written. full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Settled-observation: /home/customer/private-backups/rail-input-observe-20261109-235627-741be55d.json
[4] Settled-state export:
staging39 is www/staging39.thaisolitude.com/public_html
Private backup directory: configured=/home/<ssh-user>/private-backups canonical=/home/customer/private-backups
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation before content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Rail observation current content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail full state is exactly stable; nothing written. full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
fresh database export: private-backups/staging39-before-rail-input-settled-20261109-235636-10931.sql (4530739 bytes)
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: rail-input-observe-20261109-235624-eead56e4.json -> offsite-2026-11/rail-input-observe-20261109-235624-eead56e4.json.age (45312 bytes, read back identical; plaintext sha256 30c991b7d1a2...)
off-site: rail-input-observe-20261109-235627-741be55d.json -> offsite-2026-11/rail-input-observe-20261109-235627-741be55d.json.age (45312 bytes, read back identical; plaintext sha256 37e5e70209a6...)
off-site: staging39-before-rail-input-settled-20261109-235636-10931.sql -> offsite-2026-11/staging39-before-rail-input-settled-20261109-235636-10931.sql.age (4532043 bytes, read back identical; plaintext sha256 8b856ffe541f...)
OFF-SITE VERIFIED: 3 file(s) stored encrypted in draft release offsite-2026-11, 5 already there.
[5] Apply in place:
staging39 is www/staging39.thaisolitude.com/public_html
Private backup directory: configured=/home/<ssh-user>/private-backups canonical=/home/customer/private-backups
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation before content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Rail observation current content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail full state is exactly stable; nothing written. full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Rail input updated in place on page #1348; only post_content changed. Before-snapshot: /home/customer/private-backups/rail-input-before-20261109-235652-0adc77b9.json
[6] Verify any actual apply snapshot off-site before parsing or rendering:
Copying the 9 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 9 file(s), identical sha256 on the server and in the copy.
off-site: rail-input-before-20261109-235652-0adc77b9.json -> offsite-2026-11/rail-input-before-20261109-235652-0adc77b9.json.age (91012 bytes, read back identical; plaintext sha256 e6fd588c832c...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 8 already there.
[7] Purge, render and verify the exact applied full state:
staging39 is www/staging39.thaisolitude.com/public_html
Private backup directory: configured=/home/<ssh-user>/private-backups canonical=/home/customer/private-backups
home=http://staging39.thaisolitude.com rail-input=applied page=1348 bytes=37750 content_sha256=eb50ba823a668423b0a3408093e707f5cdb59dec3794adcff89885f64a50e2fa full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6
Success: Exact applied Rail state verified read-only; full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6. Snapshot: /home/customer/private-backups/rail-input-before-20261109-235652-0adc77b9.json
SiteGround cache purged
public check: HTTP200, exact stored Rail script/style, five ideas, six sections, shared strip and licence present
home=http://staging39.thaisolitude.com rail-input=applied page=1348 bytes=37750 content_sha256=eb50ba823a668423b0a3408093e707f5cdb59dec3794adcff89885f64a50e2fa full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6
Success: Exact applied Rail state verified read-only; full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6. Snapshot: /home/customer/private-backups/rail-input-before-20261109-235652-0adc77b9.json
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-09 23:57 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging39.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging39 (staging39.thaisolitude.com)    export: staging39-before-rail-input-settled-20261109-235636-10931.sql    modified 2026-11-09 23:56:36
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
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
4f693d0ae952: Verifying Checksum
4f693d0ae952: Download complete
650d43fe7026: Download complete
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
< page	rail-nav-ideas
> page	rail-nav-ideas
```

**RESULT: PASS** - push-rail-input.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-09 23:58:04 UTC_

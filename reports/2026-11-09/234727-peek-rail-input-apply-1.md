# Site op `peek-rail-input-apply-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-rail-input.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `aa336b21d4035fba41fb4b0ea7d4fbaf42d595bc` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42723171410 |
| started | 2026-11-09 23:46:54 UTC |

## push-rail-input.sh --apply(S_TARGET=staging39): FAIL (exit 1)

```text
Target site: staging39   mode: apply
[1] Uploading the reviewed package outside every web root ...
[2] Original cold-state export:
staging39 is www/staging39.thaisolitude.com/public_html
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
fresh database export: private-backups/staging39-before-rail-input-cold-20261109-234709-88168.sql (4530739 bytes)
Copying the 3 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 3 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-rail-input-cold-20261109-234709-88168.sql -> offsite-2026-11/staging39-before-rail-input-cold-20261109-234709-88168.sql.age (4532043 bytes, read back identical; plaintext sha256 f88c8f4034bf...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 2 already there.
[3] Warm the exact original page and prove purge/render stability:
No unique observation path returned; stopping.
staging39 is www/staging39.thaisolitude.com/public_html
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
home=http://staging39.thaisolitude.com rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
Rail observation record content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc _uag_page_assets[0] type=string bytes=708 byte_sha256=50bd6fc0c48893a53cd45fae9e2777044cb26bab9ecaa5a4bb69d009828cabf6 serialize_sha256=2958f944367725e39eb35c1538d0174b2bbce6888401c0d6470cc9d760a13e0d
Success: Original Rail state recorded read-only. Observation: /home/customer/private-backups/rail-input-observe-20261109-234726-256baa80.json
```

**RESULT: FAIL** - push-rail-input.sh failed (exit 1)

_finished 2026-11-09 23:47:26 UTC_

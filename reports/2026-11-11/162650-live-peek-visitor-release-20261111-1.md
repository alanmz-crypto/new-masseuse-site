# Site op `live-peek-visitor-release-20261111-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/push-peek-release.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `4e89eb25f5f5a68afdbb82aba45e3b385ec8a0b6` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43058202011 |
| started | 2026-11-11 16:25:51 UTC |

## push-peek-release.sh --apply(S_TARGET=live): FAIL (exit 1)

```text
Target site: live   component: peek-release   mode: apply
live is /home/customer/www/thaisolitude.com/public_html
RELEASE-SPECTRA-SOURCE {"files":{"ultimate-addons-for-gutenberg.php":"84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265","classes/class-uagb-helper.php":"ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd","classes/class-uagb-rest-api.php":"f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3","classes/class-uagb-loader.php":"7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98","classes/class-uagb-post-assets.php":"565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be","classes/class-uagb-front-assets.php":"329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a"},"header_version":"2.20.6","runtime_version":"2.20.6","loaded":{"main":true,"UAGB_Helper":true,"UAGB_Rest_API":true,"UAGB_Loader":true,"UAGB_Post_Assets":true,"UAGB_Front_Assets":true},"save_post_priority_10":true}
RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://thaisolitude.com","manifest_sha256":"5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5","state":"ready","protected_state_sha256":"51fc7efb4a9cfc36bd205b75bc6bc0acc268d5cbc60d02e9652629a4063c5814","profile_sha256":"49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac"}
Success: Original visitor baseline ready; nothing changed.
RELEASE-INTENT /home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json
RELEASE-BACKUP /home/customer/private-backups/live-before-peek-release-20261111-162615-74851.sql 4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0 3919398
Exact release marker/private journal binding verified.
off-site: live-before-peek-release-20261111-162615-74851.sql -> offsite-2026-11/live-before-peek-release-20261111-162615-74851.sql.age (3920542 bytes, read back identical; plaintext sha256 4f27b4644dca...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Verified exact backup on server, local copy and encrypted off-site readback: live-before-peek-release-20261111-162615-74851.sql (3919398 bytes, sha256 4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0)
live is /home/customer/www/thaisolitude.com/public_html
RELEASE-SPECTRA-SOURCE {"files":{"ultimate-addons-for-gutenberg.php":"84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265","classes/class-uagb-helper.php":"ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd","classes/class-uagb-rest-api.php":"f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3","classes/class-uagb-loader.php":"7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98","classes/class-uagb-post-assets.php":"565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be","classes/class-uagb-front-assets.php":"329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a"},"header_version":"2.20.6","runtime_version":"2.20.6","loaded":{"main":true,"UAGB_Helper":true,"UAGB_Rest_API":true,"UAGB_Loader":true,"UAGB_Post_Assets":true,"UAGB_Front_Assets":true},"save_post_priority_10":true}
RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://thaisolitude.com","state":"applied","snapshot":"/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json","snapshot_sha256":"4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b","manifest_sha256":"5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5","state_sha256":"c1bb9082d44a3cf1aa151678101b2509417d9e1f338e60365e7c8066c02ce874","ids":{"thai-massage":1339,"therapeutic-massage":1340,"deep-tissue-massage":1341,"chair-massage":1342,"core":1343},"profile_sha256":"49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac"}
Success: Applied exact profile-bound visitor release. Snapshot: /home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json
RELEASE-JOURNAL /home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json 4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b 1682009
off-site: peek-release-before-20261111-162615-e00022b6.json -> offsite-2026-11/peek-release-before-20261111-162615-e00022b6.json.age (1682609 bytes, read back identical; plaintext sha256 4038da0f9f77...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Verified exact backup on server, local copy and encrypted off-site readback: peek-release-before-20261111-162615-e00022b6.json (1682009 bytes, sha256 4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b)
Exact release marker/private journal binding verified.
Error: Exact applied state differs (expected c1bb9082d44a3cf1aa151678101b2509417d9e1f338e60365e7c8066c02ce874, actual b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427). No further writes.
```

**RESULT: FAIL** - push-peek-release.sh failed (exit 1)

_finished 2026-11-11 16:26:48 UTC_

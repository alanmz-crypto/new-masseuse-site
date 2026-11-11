# Site op `peek-live30-incident-inspect-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/push-peek-release.sh` `--inspect-live30` |
| lane | ops (site-ops.yml) |
| commit | `b0288b4b80aab6f3c3eec01fac559bc8520ca570` on `codex/peek-live-verification-diagnostics` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43076682138 |
| started | 2026-11-11 18:19:50 UTC |

## push-peek-release.sh --inspect-live30(S_TARGET=live): PASS

```text
Target site: live   component: peek-release   mode: inspect-live30
live is /home/customer/www/thaisolitude.com/public_html
RELEASE-SPECTRA-SOURCE {"files":{"ultimate-addons-for-gutenberg.php":"84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265","classes/class-uagb-helper.php":"ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd","classes/class-uagb-rest-api.php":"f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3","classes/class-uagb-loader.php":"7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98","classes/class-uagb-post-assets.php":"565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be","classes/class-uagb-front-assets.php":"329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a"},"header_version":"2.20.6","runtime_version":"2.20.6","loaded":{"main":true,"UAGB_Helper":true,"UAGB_Rest_API":true,"UAGB_Loader":true,"UAGB_Post_Assets":true,"UAGB_Front_Assets":true},"save_post_priority_10":true}
RELEASE-INCIDENT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://thaisolitude.com","state":"applied","snapshot":"/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json","snapshot_sha256":"4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b","manifest_sha256":"5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5","state_sha256":"b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427","complete":true,"ids":{"chair-massage":1342,"core":1343,"deep-tissue-massage":1341,"thai-massage":1339,"therapeutic-massage":1340},"receipt_sha256":"9202c8fb4e90babcba739a75660af944fc2a5adbfd1881278458312bc9ab9a0c","sql_sha256":"4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0","cache_delta_retained":true,"profile_sha256":"49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac"}
Success: LIVE30 applied: exact release state with the proved external cache delta retained; historical database identity is not claimed. Original journal unchanged.
RELEASE-SPECTRA-SOURCE {"files":{"ultimate-addons-for-gutenberg.php":"84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265","classes/class-uagb-helper.php":"ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd","classes/class-uagb-rest-api.php":"f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3","classes/class-uagb-loader.php":"7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98","classes/class-uagb-post-assets.php":"565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be","classes/class-uagb-front-assets.php":"329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a"},"header_version":"2.20.6","runtime_version":"2.20.6","loaded":{"main":true,"UAGB_Helper":true,"UAGB_Rest_API":true,"UAGB_Loader":true,"UAGB_Post_Assets":true,"UAGB_Front_Assets":true},"save_post_priority_10":true}
RELEASE-INCIDENT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://thaisolitude.com","state":"recoverable","snapshot":"/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json","snapshot_sha256":"4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b","manifest_sha256":"5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5","state_sha256":"b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427","complete":true,"ids":{"chair-massage":1342,"core":1343,"deep-tissue-massage":1341,"thai-massage":1339,"therapeutic-massage":1340},"receipt_sha256":"9202c8fb4e90babcba739a75660af944fc2a5adbfd1881278458312bc9ab9a0c","sql_sha256":"4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0","cache_delta_retained":true,"profile_sha256":"49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac"}
Success: LIVE30 recoverable: exact release state with the proved external cache delta retained; historical database identity is not claimed. Original journal unchanged.
```

**RESULT: PASS** - push-peek-release.sh succeeded

_finished 2026-11-11 18:20:00 UTC_

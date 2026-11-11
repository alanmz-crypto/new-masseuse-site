# Site op `peek-live-state-inspect-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/push-peek-release.sh` `--inspect-state /home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json 4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b` |
| lane | ops (site-ops.yml) |
| commit | `6b846de35f530a2717321b99648626563527983a` on `codex/peek-live-verification-diagnostics` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43064935977 |
| started | 2026-11-11 17:07:00 UTC |

## push-peek-release.sh --inspect-state /home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json 4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b(S_TARGET=live): FAIL (exit 1)

```text
Target site: live   component: peek-release   mode: inspect
live is /home/customer/www/thaisolitude.com/public_html
RELEASE-SPECTRA-SOURCE {"files":{"ultimate-addons-for-gutenberg.php":"84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265","classes/class-uagb-helper.php":"ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd","classes/class-uagb-rest-api.php":"f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3","classes/class-uagb-loader.php":"7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98","classes/class-uagb-post-assets.php":"565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be","classes/class-uagb-front-assets.php":"329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a"},"header_version":"2.20.6","runtime_version":"2.20.6","loaded":{"main":true,"UAGB_Helper":true,"UAGB_Rest_API":true,"UAGB_Loader":true,"UAGB_Post_Assets":true,"UAGB_Front_Assets":true},"save_post_priority_10":true}
RELEASE-STATE-DIAGNOSTIC {"label":"Exact applied state","changes":[{"path":["witnesses","postmeta","4701"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"7b4b8550411e1e461263fd2e270c273b8693ecce1cfe0e58880b53c8026a1463"},"actual":{"present":false}},{"path":["witnesses","postmeta","4702"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"9f71afd8c777db9b02b1426258488a31964165432407315e63fad27844fcf2b5"},"actual":{"present":false}},{"path":["witnesses","postmeta","5089"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"7c7d3169447a31ae354e397d24c88d6397c98a5abd28ef203bd49ee5b7d7fb66"}},{"path":["witnesses","postmeta","5090"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"5e5c40155d90ff4c5a85054fde59fe320e08602563e1df03f529e1a90b80929d"}},{"path":["witnesses","postmeta","5091"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"cb764b12ffa1feec471e0ab9d39dff278243378b775f123a8fece64af85e57f2"}}],"truncated":false}
Error: Exact applied state differs (expected c1bb9082d44a3cf1aa151678101b2509417d9e1f338e60365e7c8066c02ce874, actual b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427). No further writes.
```

**RESULT: FAIL** - push-peek-release.sh failed (exit 1)

_finished 2026-11-11 17:07:09 UTC_

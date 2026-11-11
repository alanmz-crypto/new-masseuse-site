# Site op `peek-visitor-release-quiet-search-copy-1`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `54e2f5a4fa56819adffa7f4f382eb296f2c228d5` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42981371762 |
| started | 2026-11-11 07:42:00 UTC |

## rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live: FAIL (exit 1)

```text
Rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live (thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 540109 bytes gzipped, files 269M
[2] starting the pinned practice images and importing ...
Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  isolated PHP network verified; owned loopback relay ready

[2c] exact visitor inventory and normal asset warm-up on the untouched copy
  200 /
  200 /about/
  200 /services/
  200 /contact/
  200 /deep-tissue-massage/
  200 /therapeutic-massage/
  200 /thai-massage/
  200 /work-with-me/
  200 /privacy-policy/
  200 /accessibility/
  200 /personal-automation/
  200 /personal-automation/privacy/
  200 /personal-automation/terms/
  200 /corporate-massage-portland/
  200 /chair-massage/

[warm-before] wp eval-file link-ideas/peek-release/install-peek-release.php preflight
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state":"ready","protected_state_sha256":"b002dcc5d0fa81a3ac9796c52a5f370a577ac031314ef7f2c1604eeb1f880481"}
    Success: Original visitor baseline ready; nothing changed.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  200 /
  200 /about/
  200 /services/
  200 /contact/
  200 /deep-tissue-massage/
  200 /therapeutic-massage/
  200 /thai-massage/
  200 /work-with-me/
  200 /privacy-policy/
  200 /accessibility/
  200 /personal-automation/
  200 /personal-automation/privacy/
  200 /personal-automation/terms/
  200 /corporate-massage-portland/
  200 /chair-massage/

[warm-after] wp eval-file link-ideas/peek-release/install-peek-release.php preflight
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state":"ready","protected_state_sha256":"b002dcc5d0fa81a3ac9796c52a5f370a577ac031314ef7f2c1604eeb1f880481"}
    Success: Original visitor baseline ready; nothing changed.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
PASS unchanged warm page reads preserve the complete protected release state
PASS release original-before: all15 original pages render without Peek; navigation sources and targets verified

[3] baseline: fingerprint A and the 15 pages
  200 /
  200 /about/
  200 /services/
  200 /contact/
  200 /deep-tissue-massage/
  200 /therapeutic-massage/
  200 /thai-massage/
  200 /work-with-me/
  200 /privacy-policy/
  200 /accessibility/
  200 /personal-automation/
  200 /personal-automation/privacy/
  200 /personal-automation/terms/
  200 /corporate-massage-portland/
  200 /chair-massage/
  -> PASS  pages answer 200 before the change

[preflight] wp eval-file link-ideas/peek-release/install-peek-release.php preflight
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state":"ready","protected_state_sha256":"b002dcc5d0fa81a3ac9796c52a5f370a577ac031314ef7f2c1604eeb1f880481"}
    Success: Original visitor baseline ready; nothing changed.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","state":"applied","snapshot":"/backups/peek-release-before-20261111-074400-a653010f.json","snapshot_sha256":"edb35401a992a55328ef145c279cb9b87cc8c23eeebd1aa4f87998dd2ec9f6e0","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state_sha256":"259e2fb873452ae91e721c84c0fe6d8a0b385619795577036dbbedc0b0106606","ids":{"thai-massage":1339,"therapeutic-massage":1340,"deep-tissue-massage":1341,"chair-massage":1342,"core":1343}}
    Success: Applied visitor release on isolated localhost only. Snapshot: /backups/peek-release-before-20261111-074400-a653010f.json
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  apply

[apply-again] wp eval-file link-ideas/peek-release/install-peek-release.php apply /backups/peek-release-before-20261111-074400-a653010f.json
    RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","state":"applied","snapshot":"/backups/peek-release-before-20261111-074400-a653010f.json","snapshot_sha256":"edb35401a992a55328ef145c279cb9b87cc8c23eeebd1aa4f87998dd2ec9f6e0","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state_sha256":"259e2fb873452ae91e721c84c0fe6d8a0b385619795577036dbbedc0b0106606","ids":{"chair-massage":1342,"core":1343,"deep-tissue-massage":1341,"thai-massage":1339,"therapeutic-massage":1340}}
    Success: Verified applied; no database, cache or snapshot writes.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  strict release repeat using the actual first snapshot
PASS release repeated operation retains the exact verified snapshot binding
  -> PASS  release repeat preserves the exact complete snapshot binding
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/peek-release/install-peek-release.php rollback /backups/peek-release-before-20261111-074400-a653010f.json
    RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","state":"original","snapshot":"/backups/peek-release-before-20261111-074400-a653010f.json","snapshot_sha256":"edb35401a992a55328ef145c279cb9b87cc8c23eeebd1aa4f87998dd2ec9f6e0","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state_sha256":"b002dcc5d0fa81a3ac9796c52a5f370a577ac031314ef7f2c1604eeb1f880481","ids":{"chair-massage":1342,"core":1343,"deep-tissue-massage":1341,"thai-massage":1339,"therapeutic-massage":1340}}
    Success: Exact original state restored; completed snapshot unchanged.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  rollback with peek-release-before-20261111-074400-a653010f.json
PASS release original state binds the same immutable snapshot and owned IDs
PASS release original-after-rollback: all15 original pages render without Peek; navigation sources and targets verified
  -> PASS  first release rollback restores original rendered navigation on all15 pages

[verify-original] wp eval-file link-ideas/peek-release/install-peek-release.php verify /backups/peek-release-before-20261111-074400-a653010f.json original
    RELEASE-STATE-DIAGNOSTIC {"label":"Exact original state","changes":[{"path":["witnesses","postmeta","5069"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"98ba580ebf6a033a1bb817afa120977f9e671d0d45be7b3b24c7646391c2af0f"},"actual":{"present":false}},{"path":["witnesses","postmeta","5070"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"15dd8a1971edba6c2eb6e34e7df371315c2672059fdc4ccb80a1d0b9e7cf34a8"},"actual":{"present":false}},{"path":["witnesses","postmeta","5071"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"a5bdc8041fbc6ae041bcfda7a25547f4949d76038e98000782d7cd2628743616"},"actual":{"present":false}},{"path":["witnesses","postmeta","5072"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"7996c955f0da93484080337cfec60402e2360d889183a707560f5e5c13d3cb50"},"actual":{"present":false}},{"path":["witnesses","postmeta","5073"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"44a211f3a36b1727595b6a2e8bc1b1f847aec2c46eb2804ba8ce0d3fa017ddb6"},"actual":{"present":false}},{"path":["witnesses","postmeta","5074"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"0562edac3eb7256d40ed7cc86643cf954ce5a0e1af4abf006cd744337d67a12f"},"actual":{"present":false}},{"path":["witnesses","postmeta","5075"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"6796b9bfe9e5694aeed081134b63b6fd6aef0a76a81a2e2f78e276f9984aae37"},"actual":{"present":false}},{"path":["witnesses","postmeta","5076"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"e0901c8c022c46400b918956cab4f9da30ce93bdba9dc444fe745a4b523ce373"},"actual":{"present":false}},{"path":["witnesses","postmeta","5077"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"f7c86c2e9b95bfc031abd7caaf65b5bfe8ce8d7dd976cacee3731529c489216c"},"actual":{"present":false}},{"path":["witnesses","postmeta","5078"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"a8bcd5acca791e7df9af895965031c4948634efa710e9cfee6548248ea37f6a6"},"actual":{"present":false}},{"path":["witnesses","postmeta","5079"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"7e387eb1e963135b51583bced8448b4e42bd7427d29fbd13fff7fbbc17f48d2c"},"actual":{"present":false}},{"path":["witnesses","postmeta","5080"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"5fe5e5dc16b8716f3015fff52f2231141c01f0021dd1124bd0f2af530eb38929"},"actual":{"present":false}},{"path":["witnesses","postmeta","5081"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"9629d33824ee1c8f4f913c4f65894b2794d55b4c00f7382c2b20a3a98f9a3854"},"actual":{"present":false}},{"path":["witnesses","postmeta","5082"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"3c8764f18cc1dd07e3dbdd599960d3f8321d6996bb07267d99d2c5a631eba631"},"actual":{"present":false}},{"path":["witnesses","postmeta","5083"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"eb8b345c25cbff7da4a0e3d09eee60810abe73260499096a1604815988b4905a"},"actual":{"present":false}},{"path":["witnesses","postmeta","5104"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"a37fefada7dad0a313301f389141534f586b1f5c00db20c925fb9631acb79e39"}},{"path":["witnesses","postmeta","5105"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"50b3b97e7a5559d45649e77d52dc259cb958d939ef8c4d0f12892075d609df0e"}},{"path":["witnesses","postmeta","5106"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"63240e549e27aa5f7ce466fa85b8561265be2792b4c52a1547b740c4bd3a31a2"}},{"path":["witnesses","postmeta","5107"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"213e2d9a5d8dbb3a73c00ed44d9b952f4fa2992799c61ad97a7b6bc94979ed19"}},{"path":["witnesses","postmeta","5108"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"4cd040115dfa873985d978277b69c35d7ade4b709eca18f64c177adb58856065"}},{"path":["witnesses","postmeta","5109"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"099bdb40839ebd042f04d8e94378babbc59ccd7410a9fae87d516fcffdbdd2a9"}},{"path":["witnesses","postmeta","5110"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"ca209a357449d1e9bf15a68d5f74f975167e54c8e6094a56d6c4483e65133cbc"}},{"path":["witnesses","postmeta","5111"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"6b90dc372359ed435e1eac437952c972e79ed742c4754c662cdc7dad123647bb"}},{"path":["witnesses","postmeta","5112"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"892457ca3311921a3e7cb0edcf82660523a1644c21bc59a2084d4a6118aea9ad"}},{"path":["witnesses","postmeta","5113"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"23bd5403d2b59be9cfcd79947500a8793965c4f8281eb8aa59d384db6cb370f6"}},{"path":["witnesses","postmeta","5114"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"5d08f35ee5ff226313694556dc83ea0ead00a7299e7479853bc445435895a01b"}},{"path":["witnesses","postmeta","5115"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"37f1234218be0afc61b0c6070db8989ebb1ba27f0250ca271599e6a70de7e674"}},{"path":["witnesses","postmeta","5116"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"a21a202a1f212bd12952e0703bb6d4f847d2bc7c4bbd1568e861313fb7674bd6"}},{"path":["witnesses","postmeta","5117"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"74d64fff04da917b5d86ab5a063909c5b8af8962a03216f1f0fecc932a525e8c"}},{"path":["witnesses","postmeta","5118"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"a81e051e50ba70231dc12c432f71568e8953155f4668e56ba10d3212192128f7"}}],"truncated":false}
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
    stderr: Error: Exact original state differs (expected b002dcc5d0fa81a3ac9796c52a5f370a577ac031314ef7f2c1604eeb1f880481, actual ff9603dde7c5c109721c29ad632fcf80140819cb6af359eb18ec8b52f61604ca). No further writes.
    stderr: 
  -> FAIL  exact original release records after rollback
REHEARSAL FAILED: original release state was not restored.
INFO release cleanup: owned relay absent
INFO release cleanup: Compose teardown completed
INFO release cleanup: owned container resources absent
INFO release cleanup: owned volume resources absent
INFO release cleanup: owned network resources absent
INFO release cleanup: owned temporary directory absent
INFO release cleanup: PASSED; owned relay, containers, volumes, networks and temporary directory absent
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-11 07:44:11 UTC_

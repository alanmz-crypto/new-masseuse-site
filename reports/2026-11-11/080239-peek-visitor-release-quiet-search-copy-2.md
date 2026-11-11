# Site op `peek-visitor-release-quiet-search-copy-2`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `e81115f490c11abcf450a33c5a4b928198768496` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42983523811 |
| started | 2026-11-11 07:59:50 UTC |

## rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live: FAIL (exit 1)

```text
Rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live (thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 540125 bytes gzipped, files 269M
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state":"ready","protected_state_sha256":"f6c2b8fbdbc414567bdfb2de3934786e85fd55f6f9c043f24723a331f934315f"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state":"ready","protected_state_sha256":"f6c2b8fbdbc414567bdfb2de3934786e85fd55f6f9c043f24723a331f934315f"}
    Success: Original visitor baseline ready; nothing changed.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
PASS unchanged warm page reads preserve the complete protected release state
INFO release metadata: original-before before /; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /about/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /about/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /services/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /services/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /contact/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /contact/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /deep-tissue-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /deep-tissue-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /therapeutic-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /therapeutic-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /thai-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /thai-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /work-with-me/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /work-with-me/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /privacy-policy/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /privacy-policy/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /accessibility/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /accessibility/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /personal-automation/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /personal-automation/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /personal-automation/privacy/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /personal-automation/privacy/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /personal-automation/terms/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /personal-automation/terms/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /corporate-massage-portland/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /corporate-massage-portland/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before before /chair-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-before after /chair-massage/; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state":"ready","protected_state_sha256":"f6c2b8fbdbc414567bdfb2de3934786e85fd55f6f9c043f24723a331f934315f"}
    Success: Original visitor baseline ready; nothing changed.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","state":"applied","snapshot":"/backups/peek-release-before-20261111-080216-25285ac2.json","snapshot_sha256":"bc0694629095b3798b478c1891173be9b5bec82c1445cfc19c949b749a9e8838","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state_sha256":"f8ba1c792f7188e9260847974b569ab763bc74ce90c848b9f93eeb0b0a858b49","ids":{"thai-massage":1339,"therapeutic-massage":1340,"deep-tissue-massage":1341,"chair-massage":1342,"core":1343}}
    Success: Applied visitor release on isolated localhost only. Snapshot: /backups/peek-release-before-20261111-080216-25285ac2.json
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  apply

[apply-again] wp eval-file link-ideas/peek-release/install-peek-release.php apply /backups/peek-release-before-20261111-080216-25285ac2.json
    RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","state":"applied","snapshot":"/backups/peek-release-before-20261111-080216-25285ac2.json","snapshot_sha256":"bc0694629095b3798b478c1891173be9b5bec82c1445cfc19c949b749a9e8838","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state_sha256":"f8ba1c792f7188e9260847974b569ab763bc74ce90c848b9f93eeb0b0a858b49","ids":{"chair-massage":1342,"core":1343,"deep-tissue-massage":1341,"thai-massage":1339,"therapeutic-massage":1340}}
    Success: Verified applied; no database, cache or snapshot writes.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  strict release repeat using the actual first snapshot
PASS release repeated operation retains the exact verified snapshot binding
  -> PASS  release repeat preserves the exact complete snapshot binding
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/peek-release/install-peek-release.php rollback /backups/peek-release-before-20261111-080216-25285ac2.json
    RELEASE-SNAPSHOT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","state":"original","snapshot":"/backups/peek-release-before-20261111-080216-25285ac2.json","snapshot_sha256":"bc0694629095b3798b478c1891173be9b5bec82c1445cfc19c949b749a9e8838","manifest_sha256":"296318acfd3f086e45fb7bd68548f81393566e1b38b90afb7f065bd14ad0826e","state_sha256":"f6c2b8fbdbc414567bdfb2de3934786e85fd55f6f9c043f24723a331f934315f","ids":{"chair-massage":1342,"core":1343,"deep-tissue-massage":1341,"thai-massage":1339,"therapeutic-massage":1340}}
    Success: Exact original state restored; completed snapshot unchanged.
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
  -> PASS  rollback with peek-release-before-20261111-080216-25285ac2.json
PASS release original state binds the same immutable snapshot and owned IDs
INFO release metadata: original-after-rollback before /; rows=680; sha256=c70280968d4ccb4d1ce58ccda86ab0e40805f38c1962f1e374c1520f362acf0f
INFO release metadata: original-after-rollback after /; rows=680; sha256=bd2a20a4905d1e808d6fadcca1a112d30d4d9e7cdc6f0b027c6dba15a1006d3b
INFO release metadata: original-after-rollback before /about/; rows=680; sha256=bd2a20a4905d1e808d6fadcca1a112d30d4d9e7cdc6f0b027c6dba15a1006d3b
INFO release metadata: original-after-rollback after /about/; rows=680; sha256=1cf2240766554e17f76068c6f01487f8b6f645c3e6676022a66d0962a112d7c9
INFO release metadata: original-after-rollback before /services/; rows=680; sha256=1cf2240766554e17f76068c6f01487f8b6f645c3e6676022a66d0962a112d7c9
INFO release metadata: original-after-rollback after /services/; rows=680; sha256=5fa14f9b4959de86da0f2290061f19592b450bf9d521530fe9d64b37371e7a21
INFO release metadata: original-after-rollback before /contact/; rows=680; sha256=5fa14f9b4959de86da0f2290061f19592b450bf9d521530fe9d64b37371e7a21
INFO release metadata: original-after-rollback after /contact/; rows=680; sha256=6bfce10e9f78a7c153702795782760101fd0425ac5e8a38854f830badc273b54
INFO release metadata: original-after-rollback before /deep-tissue-massage/; rows=680; sha256=6bfce10e9f78a7c153702795782760101fd0425ac5e8a38854f830badc273b54
INFO release metadata: original-after-rollback after /deep-tissue-massage/; rows=680; sha256=29b7e20e7d5d0109162ed3ee715b315a0bb21b6fdf3eb5bd40364c2e41d1efd7
INFO release metadata: original-after-rollback before /therapeutic-massage/; rows=680; sha256=29b7e20e7d5d0109162ed3ee715b315a0bb21b6fdf3eb5bd40364c2e41d1efd7
INFO release metadata: original-after-rollback after /therapeutic-massage/; rows=680; sha256=91a3e78d3c234e1b8c1fdbce5dbd2e3744c56224301cbd995e75dbaf4caa529f
INFO release metadata: original-after-rollback before /thai-massage/; rows=680; sha256=91a3e78d3c234e1b8c1fdbce5dbd2e3744c56224301cbd995e75dbaf4caa529f
INFO release metadata: original-after-rollback after /thai-massage/; rows=680; sha256=de08b48f7b829ae0e74dce8835e50c0ef08e27a7642e23356d44340fe7cf0043
INFO release metadata: original-after-rollback before /work-with-me/; rows=680; sha256=de08b48f7b829ae0e74dce8835e50c0ef08e27a7642e23356d44340fe7cf0043
INFO release metadata: original-after-rollback after /work-with-me/; rows=680; sha256=0dc275dcec8c78e798e92a32f8e81e5a9f5646bf0dbf57d8bac1a629a98385df
INFO release metadata: original-after-rollback before /privacy-policy/; rows=680; sha256=0dc275dcec8c78e798e92a32f8e81e5a9f5646bf0dbf57d8bac1a629a98385df
INFO release metadata: original-after-rollback after /privacy-policy/; rows=680; sha256=ea9ed85377f3d141bbf76b33bda4b5ecbbe229ecd8b610dd5e272d4f111dc065
INFO release metadata: original-after-rollback before /accessibility/; rows=680; sha256=ea9ed85377f3d141bbf76b33bda4b5ecbbe229ecd8b610dd5e272d4f111dc065
INFO release metadata: original-after-rollback after /accessibility/; rows=680; sha256=4c6f14d972ce87c6593fc3ee42f3c05007e510a4b8b92091a195f3f8a2ff1905
INFO release metadata: original-after-rollback before /personal-automation/; rows=680; sha256=4c6f14d972ce87c6593fc3ee42f3c05007e510a4b8b92091a195f3f8a2ff1905
INFO release metadata: original-after-rollback after /personal-automation/; rows=680; sha256=78f57cf11f32893e3db8d6381c764da744d226ccb2f715a8c9d9ea5439d11296
INFO release metadata: original-after-rollback before /personal-automation/privacy/; rows=680; sha256=78f57cf11f32893e3db8d6381c764da744d226ccb2f715a8c9d9ea5439d11296
INFO release metadata: original-after-rollback after /personal-automation/privacy/; rows=680; sha256=89be09eb3191bfe8f2673c9e2cb6211b164f140f6fa1a4ea80a484082e3debed
INFO release metadata: original-after-rollback before /personal-automation/terms/; rows=680; sha256=89be09eb3191bfe8f2673c9e2cb6211b164f140f6fa1a4ea80a484082e3debed
INFO release metadata: original-after-rollback after /personal-automation/terms/; rows=680; sha256=4ec124bab16798f1fc3083028685cbe4861933dc2101179448d30c533f525e9f
INFO release metadata: original-after-rollback before /corporate-massage-portland/; rows=680; sha256=4ec124bab16798f1fc3083028685cbe4861933dc2101179448d30c533f525e9f
INFO release metadata: original-after-rollback after /corporate-massage-portland/; rows=680; sha256=d0d182c3b0e6a70e1458b99feda4fe0bdc26bb6f1cffe4b361358d5de3f05aad
INFO release metadata: original-after-rollback before /chair-massage/; rows=680; sha256=d0d182c3b0e6a70e1458b99feda4fe0bdc26bb6f1cffe4b361358d5de3f05aad
INFO release metadata: original-after-rollback after /chair-massage/; rows=680; sha256=5a7550ee54b70475080a586d23f8b98342f3820ae3b3180c07e0e35df5be3340
PASS release original-after-rollback: all15 original pages render without Peek; navigation sources and targets verified
  -> PASS  first release rollback restores original rendered navigation on all15 pages

[verify-original] wp eval-file link-ideas/peek-release/install-peek-release.php verify /backups/peek-release-before-20261111-080216-25285ac2.json original
    RELEASE-STATE-DIAGNOSTIC {"label":"Exact original state","changes":[{"path":["witnesses","postmeta","5069"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"9d3b5fe5a1ce6d5e997a5233d3279633254733d48e7b3c6274a9c8bc97149a5f"},"actual":{"present":false}},{"path":["witnesses","postmeta","5070"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"ff53bff320bdbb38663a7e3b3e7cbbd8a59f327bc371e8cac2f55a852a973ec7"},"actual":{"present":false}},{"path":["witnesses","postmeta","5071"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"8d038ff365a17100e0c78bab831f590d3da3bf93719e8c4a46b42a72cfd25ad3"},"actual":{"present":false}},{"path":["witnesses","postmeta","5072"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"bbb1b1b4ea4476c4905d16e1421be79848dcc947028fa58e4ff89d042ced2d1c"},"actual":{"present":false}},{"path":["witnesses","postmeta","5073"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"f81146d675121df63846b266ba29a2d1d303c6f945f1ce927795c4aeecf647cf"},"actual":{"present":false}},{"path":["witnesses","postmeta","5074"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"8c138aa02cfe6475af051b326249967a417de41e761c8a4f2fead42cb4574ae3"},"actual":{"present":false}},{"path":["witnesses","postmeta","5075"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"ce10b7f3787f3bca3493c833f7db24d01a6b5e6298bc2d157d918b6a82d2985e"},"actual":{"present":false}},{"path":["witnesses","postmeta","5076"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"cbc82ea99314c69e6a411a1863707d4a2b4f4541c1cfdcb957e2cd94418c4161"},"actual":{"present":false}},{"path":["witnesses","postmeta","5077"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"72164e3c9363d531ef669bfabf2a19e6780b1f40b800ae981a18537c9d0b03fc"},"actual":{"present":false}},{"path":["witnesses","postmeta","5078"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"30c2c0c999836df5576e5d52173e06b880c887d28241d57dea25d595c77bf4ea"},"actual":{"present":false}},{"path":["witnesses","postmeta","5079"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"07de981acc46d4f0ff9e74914b43ea0ed0043a5474baeed6b7f92b5fe9047b9c"},"actual":{"present":false}},{"path":["witnesses","postmeta","5080"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"3245e59ce69bbbc546e5c1d1692fc5798a344c330244b59b5cca38aa2f5d0d11"},"actual":{"present":false}},{"path":["witnesses","postmeta","5081"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"27383603578c9d1c46a2be8d90554d57d0bdc7009e05ce0a09a1fd7d77f51317"},"actual":{"present":false}},{"path":["witnesses","postmeta","5082"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"6869b028da769e21525f91540e410048f7bf303b3f7620b303f5dfc811ad0893"},"actual":{"present":false}},{"path":["witnesses","postmeta","5083"],"kind":"missing","expected":{"present":true,"type":"string","bytes":64,"sha256":"d567e4aef709ebc27758956025dac738044b7c4f63d93d6073822e727ff63bd1"},"actual":{"present":false}},{"path":["witnesses","postmeta","5104"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"760a7783a34360dc00d6d6a3762e5e5a6051b8479efbdb4188f4ba092bd3251e"}},{"path":["witnesses","postmeta","5105"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"d181e2b9a032d94e5e3720e95cb0be460893c4e6a4534cb30c6ad9866a2a43f8"}},{"path":["witnesses","postmeta","5106"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"15353815831b94a26967cdea2e5e88e205e51a3dc3193e0a40230b996f2fd942"}},{"path":["witnesses","postmeta","5107"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"dacbf646c15aa99175ec87f79d0abbd3313f404b62f85c0a842d1675b2ef3a82"}},{"path":["witnesses","postmeta","5108"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"77d5fba81d27e87c73b62a0e7f46be51b04f446ad87f1446dc742e838406670c"}},{"path":["witnesses","postmeta","5109"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"f48316d679fdbe307be82fc5f49cb9492fbb3dc19f8cbb16d72c673c74e53886"}},{"path":["witnesses","postmeta","5110"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"26fc6f723a35b7e3271776c064c8f32fe608d79722a99c31455ebb059ddadf32"}},{"path":["witnesses","postmeta","5111"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"c98537213cc57696f8755d45832967b98d03c93f430e5f460812c276af312e91"}},{"path":["witnesses","postmeta","5112"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"f5aa971d44a6c77e8371792cff73d7fd90907282c02781a1934bc2c44d693e7d"}},{"path":["witnesses","postmeta","5113"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"1cd31ee9b040f69bf1be1416d20b1da2cc3683712efa0dd95af2f12141790349"}},{"path":["witnesses","postmeta","5114"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"4352f04d6a3bddabef51ee3bb227bfe04a5e2e168f25b6fdceb04f6120908cc9"}},{"path":["witnesses","postmeta","5115"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"daca25e8bdb1e4c87d13fa652f47ba0723793868294067a940cf5f7c61eb4fd9"}},{"path":["witnesses","postmeta","5116"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"0d553d9b142daf154f2a8b8ab1e5326264f12ece4932da3b52d5aed40489e380"}},{"path":["witnesses","postmeta","5117"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"f502974e99fa7843c929b2a827e9fd51579b286878c96fecb5b2f9dbc189b576"}},{"path":["witnesses","postmeta","5118"],"kind":"added","expected":{"present":false},"actual":{"present":true,"type":"string","bytes":64,"sha256":"e7027ce77f21762619fa036aa98d6c7a0a82c5b4a315affa8e20d04bd48c157b"}}],"truncated":false}
    stderr: Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
    stderr: Error: Exact original state differs (expected f6c2b8fbdbc414567bdfb2de3934786e85fd55f6f9c043f24723a331f934315f, actual 728db4349cb4a3eaabcda11e0e475d95beae3ce7a02db5bf4c6ca2a658848b25). No further writes.
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

_finished 2026-11-11 08:02:36 UTC_

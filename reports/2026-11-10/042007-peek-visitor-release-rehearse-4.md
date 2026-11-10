# Site op `peek-visitor-release-rehearse-4`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `19dd6a09e1129b62f9830c4a5456dd74fc06e11f` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42754978423 |
| started | 2026-11-10 04:18:13 UTC |

## rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live: FAIL (exit 1)

```text
Rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live (thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 573268 bytes gzipped, files 269M
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"066cd082f28fdc65df5dbe56bce92876478102cf6ae702ceba52f77aac465e2a"}
    Success: Original visitor baseline ready; nothing changed.
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"066cd082f28fdc65df5dbe56bce92876478102cf6ae702ceba52f77aac465e2a"}
    Success: Original visitor baseline ready; nothing changed.
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"066cd082f28fdc65df5dbe56bce92876478102cf6ae702ceba52f77aac465e2a"}
    Success: Original visitor baseline ready; nothing changed.
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    RELEASE-STATE-DIAGNOSTIC {"label":"Inactive creation full state","changes":[{"path":["taxonomies","0","count"],"kind":"value","expected":{"present":true,"type":"string","bytes":2,"sha256":"fab13afe2dc4c2d3f07863a8be575026cffc4be4b24fe3aae9458a8e01322c57"},"actual":{"present":true,"type":"string","bytes":2,"sha256":"e3319f6218654e34042c0191b02eaaa707faa786fb6de9722a7f65e21ae6b6bc"}},{"path":["taxonomies","1","count"],"kind":"value","expected":{"present":true,"type":"string","bytes":2,"sha256":"fab13afe2dc4c2d3f07863a8be575026cffc4be4b24fe3aae9458a8e01322c57"},"actual":{"present":true,"type":"string","bytes":2,"sha256":"e3319f6218654e34042c0191b02eaaa707faa786fb6de9722a7f65e21ae6b6bc"}}],"truncated":false}
    RELEASE-STATE-DIAGNOSTIC {"label":"Recognized state before apply recovery","changes":[{"path":["owned","thai-massage"],"kind":"type","expected":{"present":true,"type":"null","bytes":4,"sha256":"9093792ad91b3e9635903979e299021423dbc6d7b0eaafb89d7a74d058156335"},"actual":{"present":true,"type":"array","bytes":5677,"sha256":"f5449375d0e4b54db4f02b78435af38ebb9453a688e27863401b3843488e38e3","count":3,"shape":"map"}},{"path":["taxonomies","0","count"],"kind":"value","expected":{"present":true,"type":"string","bytes":2,"sha256":"fab13afe2dc4c2d3f07863a8be575026cffc4be4b24fe3aae9458a8e01322c57"},"actual":{"present":true,"type":"string","bytes":2,"sha256":"e3319f6218654e34042c0191b02eaaa707faa786fb6de9722a7f65e21ae6b6bc"}},{"path":["taxonomies","1","count"],"kind":"value","expected":{"present":true,"type":"string","bytes":2,"sha256":"fab13afe2dc4c2d3f07863a8be575026cffc4be4b24fe3aae9458a8e01322c57"},"actual":{"present":true,"type":"string","bytes":2,"sha256":"e3319f6218654e34042c0191b02eaaa707faa786fb6de9722a7f65e21ae6b6bc"}}],"truncated":false}
    Error: Inactive creation full state differs (expected 18e04e2304658f7b24bb6465a2b1cbb2ed4d6b44513f3ec32e9e1602293d97d8, actual 243b71e71f9d7ad78100d1eb523721bddbb9c6c46ad5ef4fc1f44a0b1f85ca3e).; exact recovery stopped: Recognized state before apply recovery differs (expected 066cd082f28fdc65df5dbe56bce92876478102cf6ae702ceba52f77aac465e2a, actual 243b71e71f9d7ad78100d1eb523721bddbb9c6c46ad5ef4fc1f44a0b1f85ca3e). Snapshot: /backups/peek-release-before-20261110-042003-91e0a251.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-10 04:20:06 UTC_

# Site op `peek-visitor-release-rehearse-2`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `0334bc564ff3156cd26220e11c46076d0a17f717` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42751049392 |
| started | 2026-11-10 03:41:33 UTC |

## rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live: FAIL (exit 1)

```text
Rehearsal of link-ideas/peek-release/install-peek-release.php on a throwaway copy of live (thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 573269 bytes gzipped, files 269M
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"98c29768bb4774d18a6b93593c7903a0c2391c68bcfd79689545d95b2b5a4082"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"98c29768bb4774d18a6b93593c7903a0c2391c68bcfd79689545d95b2b5a4082"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"98c29768bb4774d18a6b93593c7903a0c2391c68bcfd79689545d95b2b5a4082"}
    Success: Original visitor baseline ready; nothing changed.
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    RELEASE-OWNED-DIAGNOSTIC {"role":"thai-massage","reasons":["metadata"],"known_meta":[{"key":"_wpcode_auto_insert","expected_count":1,"actual_count":1,"expected":{"type":"string","bytes":1,"sha256":"6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b"},"actual":[{"type":"string","bytes":1,"sha256":"6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b"}]},{"key":"_wpcode_note","expected_count":1,"actual_count":1,"expected":{"type":"string","bytes":98,"sha256":"0e1a33aa1c3a988df6c73e657ed7b727485cccd304c9fa7eb1caf1063020738c"},"actual":[{"type":"string","bytes":98,"sha256":"0e1a33aa1c3a988df6c73e657ed7b727485cccd304c9fa7eb1caf1063020738c"}]},{"key":"_wpcode_priority","expected_count":1,"actual_count":1,"expected":{"type":"string","bytes":2,"sha256":"4a44dc15364204a80fe80e9039455cc1608281820fe2b24f1e5233ade6af1dd5"},"actual":[{"type":"string","bytes":2,"sha256":"4a44dc15364204a80fe80e9039455cc1608281820fe2b24f1e5233ade6af1dd5"}]},{"key":"_wpcode_shortcode_attributes","expected_count":1,"actual_count":0,"expected":{"type":"string","bytes":6,"sha256":"35786c7117b4e38d0f169239752ce71158266ae2f6e4aa230fbbb87bd699c0e3"},"actual":[]}],"unknown_meta":[],"expected_taxonomy_ids":[9,10],"actual_relationship_count":2,"relationships":[{"object_id":"1339","term_taxonomy_id":"9","term_order":"0"},{"object_id":"1339","term_taxonomy_id":"10","term_order":"0"}]}
    Error: Owned metadata/relationships differ: thai-massage; exact recovery stopped: Recognized state before apply recovery differs (expected 98c29768bb4774d18a6b93593c7903a0c2391c68bcfd79689545d95b2b5a4082, actual 0f71e70cf58c26cbb10604251def3e07d5425f1e231509eed24355593ad9aa4d). Snapshot: /backups/peek-release-before-20261110-034344-cf52ca44.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-10 03:43:48 UTC_

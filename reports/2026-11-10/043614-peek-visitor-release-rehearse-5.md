# Site op `peek-visitor-release-rehearse-5`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `44e42dba67861ed799005ef65f59cc444aca7495` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42756230445 |
| started | 2026-11-10 04:30:09 UTC |

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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"b6e124bcd4719256d0b65e529628d08ccfa724bb45b2ab36dd94be7f8d983f5d"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"b6e124bcd4719256d0b65e529628d08ccfa724bb45b2ab36dd94be7f8d983f5d"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"b6e124bcd4719256d0b65e529628d08ccfa724bb45b2ab36dd94be7f8d983f5d"}
    Success: Original visitor baseline ready; nothing changed.
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    RELEASE-STATE-DIAGNOSTIC {"label":"Owned save permitted fields","changes":[{"path":["post","guid"],"kind":"value","expected":{"present":true,"type":"string","bytes":46,"sha256":"7b8db0830c06ead0ec98ec0f86ef34d152bf2e25e82893a1fc3278a7a53dd540"},"actual":{"present":true,"type":"string","bytes":51,"sha256":"0f58bab1e67744561cb66c17a662c2cb9e4e531caabe645d1b2de0e9c8a74325"}}],"truncated":false}
    RELEASE-STATE-DIAGNOSTIC {"label":"Recognized state before apply recovery","changes":[{"path":["owned","thai-massage","post","guid"],"kind":"value","expected":{"present":true,"type":"string","bytes":46,"sha256":"7b8db0830c06ead0ec98ec0f86ef34d152bf2e25e82893a1fc3278a7a53dd540"},"actual":{"present":true,"type":"string","bytes":51,"sha256":"0f58bab1e67744561cb66c17a662c2cb9e4e531caabe645d1b2de0e9c8a74325"}},{"path":["owned","thai-massage","post","post_content"],"kind":"value","expected":{"present":true,"type":"string","bytes":4374,"sha256":"b2dbd7c32c3c8614a7cc8db307defb03c4ff7859d3483c14384c0f69806c4c72"},"actual":{"present":true,"type":"string","bytes":4376,"sha256":"ac5988a1bdbe8d4ec21b5b05ed13ada006346282e057d5e59144b785519af8ef"}},{"path":["owned","thai-massage","post","post_modified_gmt"],"kind":"value","expected":{"present":true,"type":"string","bytes":19,"sha256":"6b881505263be757ad3f1c1a808bb6b161d74fd95b73cf3e436f2ac3a31a382c"},"actual":{"present":true,"type":"string","bytes":19,"sha256":"115e5955c1c30b408867e7f781c077e2d3669ec56fa4d95a4063ed3f8786d946"}}],"truncated":false}
    Error: Owned save permitted fields differs (expected df77cecc14444f15b590febfa1258f9730c97d49cb0788e7e30d753e9c4b0198, actual 4ccff7106f5a72a2502f6e993aa792c16c9ff13706854610cbc8e071ede766fe).; exact recovery stopped: Recognized state before apply recovery differs (expected 2bf577d04902ef286292153f0c69f77a65ef382345c2243f8c2bf70384002880, actual a71eaa546ee6ca67ad8a3b439068b13cca9692eeca501dea80e5346511c6d9a4). Snapshot: /backups/peek-release-before-20261110-043610-c8f1cf19.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-10 04:36:13 UTC_

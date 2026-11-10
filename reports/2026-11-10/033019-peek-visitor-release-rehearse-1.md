# Site op `peek-visitor-release-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `6b03bd5759b1cf28e33e290c4ff13f79f76f92dc` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42749322045 |
| started | 2026-11-10 03:25:26 UTC |

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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"123c4028d107d712fd3d5139c2f74c042b184d9bf9a2f337bf8be2506ac7bfc8"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"123c4028d107d712fd3d5139c2f74c042b184d9bf9a2f337bf8be2506ac7bfc8"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"123c4028d107d712fd3d5139c2f74c042b184d9bf9a2f337bf8be2506ac7bfc8"}
    Success: Original visitor baseline ready; nothing changed.
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    Error: Owned metadata/relationships differ: thai-massage; exact recovery stopped: Recognized state before apply recovery differs (expected 123c4028d107d712fd3d5139c2f74c042b184d9bf9a2f337bf8be2506ac7bfc8, actual 8c4ca37186276aeb450c4b64e3e9877fc6727b7c7a09255b4d3aadf0464c9166). Snapshot: /backups/peek-release-before-20261110-033014-5f460677.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-10 03:30:18 UTC_

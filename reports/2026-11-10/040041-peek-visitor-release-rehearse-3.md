# Site op `peek-visitor-release-rehearse-3`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `link-ideas/peek-release/install-peek-release.php` |
| lane | ops (site-ops.yml) |
| commit | `a5b992df4f513d9740fff69baa03ef699e83b2a5` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42752791077 |
| started | 2026-11-10 03:58:12 UTC |

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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"1170739467ce05222c61b4d31d704b74bac3b3a2a1d6b7a9554613d499b6c39d"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"1170739467ce05222c61b4d31d704b74bac3b3a2a1d6b7a9554613d499b6c39d"}
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
    RELEASE-PREFLIGHT {"schema":1,"bundle":"peek-visitor-release-v1","home":"http://localhost:8090","manifest_sha256":"6c5eab5017479279f938efe8c84d0ff5898b3b8c661da3ae0bd3710df888f4d4","state":"ready","protected_state_sha256":"1170739467ce05222c61b4d31d704b74bac3b3a2a1d6b7a9554613d499b6c39d"}
    Success: Original visitor baseline ready; nothing changed.
  -> PASS  preflight
PASS unchanged warm page reads preserve the complete protected release state
  -> PASS  release state remains exact after baseline page reads

[apply] wp eval-file link-ideas/peek-release/install-peek-release.php apply
    Error: Inactive creation full state differs (expected 3450ac963413f42e957184daefab2eb34f5731d78a4f54c7e4cef78531296005, actual aa5c0de65c10c500c559c57b8a19d17b1c686d65a2c1cfa76d296011d42516f6).; exact recovery stopped: Recognized state before apply recovery differs (expected 1170739467ce05222c61b4d31d704b74bac3b3a2a1d6b7a9554613d499b6c39d, actual aa5c0de65c10c500c559c57b8a19d17b1c686d65a2c1cfa76d296011d42516f6). Snapshot: /backups/peek-release-before-20261110-040037-7a3f5751.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-10 04:00:40 UTC_

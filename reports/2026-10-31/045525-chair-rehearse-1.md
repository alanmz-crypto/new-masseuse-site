# Site op `chair-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `chair-massage/install-chair-massage.php` |
| lane | ops (site-ops.yml) |
| commit | `10091f6180a24579a440d28b72c23ea3ec86a0e6` on `feat/2026-10-30-chair-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41170411428 |
| started | 2026-10-31 04:54:14 UTC |

## rehearsal of chair-massage/install-chair-massage.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of chair-massage/install-chair-massage.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 711205 bytes gzipped, files 316M
[2] starting the pinned practice images and importing ...
Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95

[3] baseline: fingerprint A and the 10 pages
  200 /
  200 /about/
  200 /services/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  -> PASS  pages answer 200 before the change

[preflight] wp eval-file chair-massage/install-chair-massage.php preflight
    Error: Internal error: a snippet insertion is not what was reviewed (band_old).
    
  -> FAIL  preflight

[apply] wp eval-file chair-massage/install-chair-massage.php apply
    Error: Internal error: a snippet insertion is not what was reviewed (band_old).
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-10-31 04:55:25 UTC_

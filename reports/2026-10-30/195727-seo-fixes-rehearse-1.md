# Site op `seo-fixes-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `seo-fixes/install-seo-fixes.php` |
| lane | ops (site-ops.yml) |
| commit | `7a6ca57a28d1494609e8eda48edbdc702d3779e2` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41119246516 |
| started | 2026-10-30 19:56:13 UTC |

## rehearsal of seo-fixes/install-seo-fixes.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of seo-fixes/install-seo-fixes.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 706220 bytes gzipped, files 239M
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

[preflight] wp eval-file seo-fixes/install-seo-fixes.php preflight
    Error: The corporate illustration (corporate-group-massage.webp, reviewed sha256) is not in this site's media library exactly once; nothing was changed.
    
  -> FAIL  preflight

[apply] wp eval-file seo-fixes/install-seo-fixes.php apply
    Error: The corporate illustration (corporate-group-massage.webp, reviewed sha256) is not in this site's media library exactly once; nothing was changed.
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-10-30 19:57:27 UTC_

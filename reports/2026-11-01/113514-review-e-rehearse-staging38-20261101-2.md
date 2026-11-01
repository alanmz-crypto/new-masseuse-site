# Site op `review-e-rehearse-staging38-20261101-2`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `review-e/install-bundle-e.php` |
| lane | ops (site-ops.yml) |
| commit | `3f7b28630ef5cc17399879cc28d4ab5043609319` on `codex/2026-11-01-review-bundle-e` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41302390016 |
| started | 2026-11-01 11:33:24 UTC |

## rehearsal of review-e/install-bundle-e.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of review-e/install-bundle-e.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 1027720 bytes gzipped, files 317M
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

[preflight] wp eval-file review-e/install-bundle-e.php preflight
    page home=ready
    page therapeutic-massage=ready
    new snippet SiteReviewPerformance=ready
    new snippet SiteReviewEmoji=ready
    prepared WebP=ready to import once; originals=10 preserved
    host=http://localhost:8090 state=ready
    Success: Preflight ready; nothing changed.
  -> PASS  preflight

[apply] wp eval-file review-e/install-bundle-e.php apply
    page home=ready
    page therapeutic-massage=ready
    new snippet SiteReviewPerformance=ready
    new snippet SiteReviewEmoji=ready
    prepared WebP=ready to import once; originals=10 preserved
    host=http://localhost:8090 state=ready
    Error: wp_handle_sideload(): Argument #1 ($file) could not be passed by reference; old stores restored and only owned resources removed.
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-01 11:35:13 UTC_

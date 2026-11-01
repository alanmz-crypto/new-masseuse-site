# Site op `review-b-rehearse-staging38-20261101-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `review-b/install-bundle-b.php` |
| lane | ops (site-ops.yml) |
| commit | `18b46b2ba2d08c7debba2b8612f95c5319e399a1` on `codex/2026-11-01-review-bundle-b` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41289068568 |
| started | 2026-11-01 08:29:32 UTC |

## rehearsal of review-b/install-bundle-b.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of review-b/install-bundle-b.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 840900 bytes gzipped, files 239M
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

[preflight] wp eval-file review-b/install-bundle-b.php preflight
    metadata therapeutic-massage=ready
    metadata deep-tissue-massage=ready
    metadata chair-massage=ready
    metadata corporate-massage-portland=ready
    metadata privacy-policy=ready
    metadata accessibility=ready
    metadata services=ready
    Organization about=ready
    Organization contact=ready
    Corporate https share image=applied
    global Person=ready
    tagline=ready
    LocalBusinessInfo=ready
    Home by arrangement=ready
    host=http://localhost:8090 state=CHANGED
    Error: Drift/partial state; nothing changed.
    
  -> FAIL  preflight

[apply] wp eval-file review-b/install-bundle-b.php apply
    metadata therapeutic-massage=ready
    metadata deep-tissue-massage=ready
    metadata chair-massage=ready
    metadata corporate-massage-portland=ready
    metadata privacy-policy=ready
    metadata accessibility=ready
    metadata services=ready
    Organization about=ready
    Organization contact=ready
    Corporate https share image=applied
    global Person=ready
    tagline=ready
    LocalBusinessInfo=ready
    Home by arrangement=ready
    host=http://localhost:8090 state=CHANGED
    Error: Not reviewed state; nothing changed.
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-01 08:30:41 UTC_

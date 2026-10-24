# Site op `rehearse-check-2`

| | |
|---|---|
| operation | `rehearse` on `staging36` |
| installer | `thai/install-image-crop.php` |
| lane | ops (site-ops.yml) |
| commit | `a15ff83797dfdb414119597adf66d932f24ae681` on `fix/2026-10-24-secrets-script-mode` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40067257507 |
| started | 2026-10-24 05:41:59 UTC |

## rehearsal of thai/install-image-crop.php on a throwaway copy of staging36: FAIL (exit 2)

```text
Rehearsal of thai/install-image-crop.php on a throwaway copy of staging36 (staging36.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging36.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging36.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 854622 bytes gzipped, files 238M
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

[preflight] wp eval-file thai/install-image-crop.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=applied css_bytes=48765
  -> PASS  preflight

[apply] wp eval-file thai/install-image-crop.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied css_bytes=48765
  -> PASS  apply

REHEARSAL INVALID: apply moved no fingerprint. The change is probably already on staging36 (preflight said so above), or it touches
something the fingerprint does not cover; either way a rollback test would prove nothing.
```

**RESULT: INVALID** - rehearsal could not be set up

_finished 2026-10-24 05:43:07 UTC_

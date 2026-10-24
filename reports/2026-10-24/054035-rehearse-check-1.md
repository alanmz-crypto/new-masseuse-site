# Site op `rehearse-check-1`

| | |
|---|---|
| operation | `rehearse` on `staging36` |
| installer | `thai/install-image-crop.php` |
| lane | ops (site-ops.yml) |
| commit | `15479a69c01c0dbae5056d001ce691d3719ade05` on `fix/2026-10-24-secrets-script-mode` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40067124604 |
| started | 2026-10-24 05:39:56 UTC |

## rehearsal of thai/install-image-crop.php on a throwaway copy of staging36: FAIL (exit 2)

```text
Rehearsal of thai/install-image-crop.php on a throwaway copy of staging36 (staging36.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging36.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging36.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...

gzip: stdout: Broken pipe
  database 854622 bytes gzipped, files 238M
[2] starting the pinned practice images and importing ...
Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
Error: 'wp' is not a registered wp command. See 'wp help' for available commands.
Did you mean 'cap'?

REHEARSAL INVALID: search-replace https://staging36.thaisolitude.com failed
```

**RESULT: INVALID** - rehearsal could not be set up

_finished 2026-10-24 05:40:35 UTC_

# Site op `deep-evidence-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging36` |
| installer | `deep-tissue/install-deep-tissue-evidence.php` |
| lane | ops (site-ops.yml) |
| commit | `71c135ae9813348bfad4c26e3b2b5771d23ab33e` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40111162097 |
| started | 2026-10-24 16:21:25 UTC |

## rehearsal of deep-tissue/install-deep-tissue-evidence.php on a throwaway copy of staging36: FAIL (exit 1)

```text
Rehearsal of deep-tissue/install-deep-tissue-evidence.php on a throwaway copy of staging36 (staging36.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging36.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging36.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 859689 bytes gzipped, files 238M
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

[preflight] wp eval-file deep-tissue/install-deep-tissue-evidence.php preflight
    Error: Wording check failed in the source titles: "we" (/\b(we|our|us|ours)\b/i)
    
  -> FAIL  preflight

[apply] wp eval-file deep-tissue/install-deep-tissue-evidence.php apply
    Error: Wording check failed in the source titles: "we" (/\b(we|our|us|ours)\b/i)
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-10-24 16:22:53 UTC_

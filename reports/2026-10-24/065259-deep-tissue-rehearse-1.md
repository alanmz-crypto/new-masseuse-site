# Site op `deep-tissue-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging36` |
| installer | `deep-tissue/install-deep-tissue-copy.php` |
| lane | ops (site-ops.yml) |
| commit | `d44dc31aacf758dcf7765e12a525f10c80427a58` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40071860331 |
| started | 2026-10-24 06:51:48 UTC |

## rehearsal of deep-tissue/install-deep-tissue-copy.php on a throwaway copy of staging36: PASS

```text
Rehearsal of deep-tissue/install-deep-tissue-copy.php on a throwaway copy of staging36 (staging36.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging36.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging36.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 854623 bytes gzipped, files 238M
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

[preflight] wp eval-file deep-tissue/install-deep-tissue-copy.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready pages_with_slug=1
  -> PASS  preflight

[apply] wp eval-file deep-tissue/install-deep-tissue-copy.php apply
    Success: Page #1091 (/deep-tissue-massage/): the research sentence now says "people with neck pain linked to tender spots in the muscles". Before-snapshot: /backups/deep-tissue-copy-before-20261024-065251.json
  -> PASS  apply

[apply-again] wp eval-file deep-tissue/install-deep-tissue-copy.php apply
    Success: Nothing to do: the plain-words sentence is already there. host=http://localhost:8090 state=applied pages_with_slug=1
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file deep-tissue/install-deep-tissue-copy.php rollback /backups/deep-tissue-copy-before-20261024-065251.json
    Success: Rollback done: the original sentence is back and the page is byte-identical to the snapshot.
  -> PASS  rollback with deep-tissue-copy-before-20261024-065251.json
  -> PASS  after rollback every fingerprint equals the baseline (A)

[re-apply] wp eval-file deep-tissue/install-deep-tissue-copy.php apply
    Success: Page #1091 (/deep-tissue-massage/): the research sentence now says "people with neck pain linked to tender spots in the muscles". Before-snapshot: /backups/deep-tissue-copy-before-20261024-065254.json
  -> PASS  apply again after the rollback
  -> PASS  re-applied state equals the first applied state (B)

[4] the 10 pages after the change, and PHP errors
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
  -> PASS  pages answer 200 with the change applied
  no PHP error, warning or deprecation that the copy did not already log
  -> PASS  no new PHP errors

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: page	deep-tissue-massage
  rows now: iej_options	447
  rows now: iej_postmeta	617
  rows now: iej_posts	161

REHEARSAL PASSED: deep-tissue/install-deep-tissue-copy.php on a copy of staging36 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-24 06:52:58 UTC_

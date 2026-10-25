# Site op `rate-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `corporate/install-corporate-rate.php` |
| lane | ops (site-ops.yml) |
| commit | `bb97fe08ba7ae3273e831ebe07c44b459ced7bca` on `fix/2026-10-25-corporate-rate-100` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40163663036 |
| started | 2026-10-25 04:56:00 UTC |

## rehearsal of corporate/install-corporate-rate.php on a throwaway copy of staging38: PASS

```text
Rehearsal of corporate/install-corporate-rate.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 621204 bytes gzipped, files 239M
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

[preflight] wp eval-file corporate/install-corporate-rate.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready rate line=ready FAQ answer=ready
  -> PASS  preflight

[apply] wp eval-file corporate/install-corporate-rate.php apply
    Success: Changed to $100: rate line, FAQ answer (page #1266). Before-snapshot: /backups/corporate-rate-before-20261025-045717.json
  -> PASS  apply

[apply-again] wp eval-file corporate/install-corporate-rate.php apply
    Success: Nothing to do: already $100 in both places. host=http://localhost:8090 state=applied rate line=applied FAQ answer=applied
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file corporate/install-corporate-rate.php rollback /backups/corporate-rate-before-20261025-045717.json
    Success: Rolled back: rate line, FAQ answer (the page is byte-identical to the before-snapshot).
  -> PASS  rollback with corporate-rate-before-20261025-045717.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file corporate/install-corporate-rate.php apply
    Success: Changed to $100: rate line, FAQ answer (page #1266). Before-snapshot: /backups/corporate-rate-before-20261025-045719.json
  -> PASS  apply again after the rollback
  -> PASS  re-applied state equals the first applied state (B; ids of re-created snippets and SEO rows set aside)

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
  changed: page	corporate-massage-portland
  rows now: iej_options	447
  rows now: iej_postmeta	631
  rows now: iej_posts	104

REHEARSAL PASSED: corporate/install-corporate-rate.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-25 04:57:23 UTC_

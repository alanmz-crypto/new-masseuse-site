# Site op `slide-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging36` |
| installer | `research-slide/install-research-slide.php` |
| lane | ops (site-ops.yml) |
| commit | `7948ea7b54e189a3435d8c8f05966b09f6446dc1` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40114579432 |
| started | 2026-10-24 17:06:16 UTC |

## rehearsal of research-slide/install-research-slide.php on a throwaway copy of staging36: PASS

```text
Rehearsal of research-slide/install-research-slide.php on a throwaway copy of staging36 (staging36.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging36.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging36.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 894307 bytes gzipped, files 238M
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

[preflight] wp eval-file research-slide/install-research-slide.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready css_bytes=48765
  -> PASS  preflight

[apply] wp eval-file research-slide/install-research-slide.php apply
    Success: Added the ts-research-slide block to the Additional CSS (48765 -> 49805 bytes). Before-snapshot: /backups/research-slide-before-20261024-170723.json
  -> PASS  apply

[apply-again] wp eval-file research-slide/install-research-slide.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied css_bytes=49805
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file research-slide/install-research-slide.php rollback /backups/research-slide-before-20261024-170723.json
    Success: Rollback done: the ts-research-slide block is out of the Additional CSS (exact old CSS restored).
  -> PASS  rollback with research-slide-before-20261024-170723.json
  -> PASS  after rollback every fingerprint equals the baseline (A)

[re-apply] wp eval-file research-slide/install-research-slide.php apply
    Success: Added the ts-research-slide block to the Additional CSS (48765 -> 49805 bytes). Before-snapshot: /backups/research-slide-before-20261024-170726.json
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
  changed: css	29
  rows now: iej_options	447
  rows now: iej_posts	167

REHEARSAL PASSED: research-slide/install-research-slide.php on a copy of staging36 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-24 17:07:30 UTC_

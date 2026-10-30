# Site op `booking-date-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `booking-date/install-booking-date.php` |
| lane | ops (site-ops.yml) |
| commit | `7e59c4d3ca8b89dae891d252d8bc9fec33bc8272` on `feat/2026-10-30-nov14` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41078410764 |
| started | 2026-10-30 15:11:50 UTC |

## rehearsal of booking-date/install-booking-date.php on a throwaway copy of staging38: PASS

```text
Rehearsal of booking-date/install-booking-date.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 671995 bytes gzipped, files 239M
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

[preflight] wp eval-file booking-date/install-booking-date.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready replacements ready=8 applied=0 changed=0; strip snippet=ready
  -> PASS  preflight

[apply] wp eval-file booking-date/install-booking-date.php apply
    Success: Applied 8 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /backups/booking-date-before-20261030-151313.json
  -> PASS  apply

[apply-again] wp eval-file booking-date/install-booking-date.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied replacements ready=0 applied=8 changed=0; strip snippet=applied
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file booking-date/install-booking-date.php rollback /backups/booking-date-before-20261030-151313.json
    Success: Rollback done: the old texts and the old strip code are back (pages byte-identical to the snapshot; snippet byte-identical).
  -> PASS  rollback with booking-date-before-20261030-151313.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file booking-date/install-booking-date.php apply
    Success: Applied 8 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /backups/booking-date-before-20261030-151316.json
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
  changed: option	wpcode_snippets
  changed: page	corporate-massage-portland
  changed: page	deep-tissue-massage
  changed: page	thai-massage
  changed: page	therapeutic-massage
  changed: page	work-with-me
  changed: snippet	1268
  rows now: iej_options	447
  rows now: iej_postmeta	627
  rows now: iej_posts	113

REHEARSAL PASSED: booking-date/install-booking-date.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-30 15:13:21 UTC_

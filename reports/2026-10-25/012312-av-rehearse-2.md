# Site op `av-rehearse-2`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `availability/install-availability-update.php` |
| lane | ops (site-ops.yml) |
| commit | `80e2122ab89f51f67c7b6c8a291626433efad58c` on `feat/2026-10-25-availability-update` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40150327005 |
| started | 2026-10-25 01:21:58 UTC |

## rehearsal of availability/install-availability-update.php on a throwaway copy of staging38: PASS

```text
Rehearsal of availability/install-availability-update.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 552541 bytes gzipped, files 239M
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

[preflight] wp eval-file availability/install-availability-update.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready replacements ready=10 applied=0 changed=0; strip snippet=ready
  -> PASS  preflight

[apply] wp eval-file availability/install-availability-update.php apply
    Success: Applied 10 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /backups/availability-update-before-20261025-012305.json
  -> PASS  apply

[apply-again] wp eval-file availability/install-availability-update.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied replacements ready=0 applied=10 changed=0; strip snippet=applied
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file availability/install-availability-update.php rollback /backups/availability-update-before-20261025-012305.json
    Success: Rollback done: the old texts and the old strip code are back (pages byte-identical to the snapshot; snippet byte-identical).
  -> PASS  rollback with availability-update-before-20261025-012305.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file availability/install-availability-update.php apply
    Success: Applied 10 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /backups/availability-update-before-20261025-012308.json
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
  rows now: iej_postmeta	623
  rows now: iej_posts	101

REHEARSAL PASSED: availability/install-availability-update.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-25 01:23:12 UTC_

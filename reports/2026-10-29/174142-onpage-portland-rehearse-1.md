# Site op `onpage-portland-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `onpage-portland/install-onpage-portland.php` |
| lane | ops (site-ops.yml) |
| commit | `eca03c06ca1a423b23f0ec7b36bb8fc339561630` on `feat/2026-10-29-onpage-portland` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40923516522 |
| started | 2026-10-29 17:40:19 UTC |

## rehearsal of onpage-portland/install-onpage-portland.php on a throwaway copy of staging38: PASS

```text
Rehearsal of onpage-portland/install-onpage-portland.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 627573 bytes gzipped, files 239M
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

[preflight] wp eval-file onpage-portland/install-onpage-portland.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready replacements ready=3 applied=0 changed=0
  -> PASS  preflight

[apply] wp eval-file onpage-portland/install-onpage-portland.php apply
    Success: Applied 3 replacements on 3 pages (Home licence line, Thai H1, Deep Tissue H1). Before-snapshot: /backups/onpage-portland-before-20261029-174133.json
  -> PASS  apply

[apply-again] wp eval-file onpage-portland/install-onpage-portland.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied replacements ready=0 applied=3 changed=0
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file onpage-portland/install-onpage-portland.php rollback /backups/onpage-portland-before-20261029-174133.json
    Success: Rollback done: the three old texts are back (pages byte-identical to the snapshot).
  -> PASS  rollback with onpage-portland-before-20261029-174133.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file onpage-portland/install-onpage-portland.php apply
    Success: Applied 3 replacements on 3 pages (Home licence line, Thai H1, Deep Tissue H1). Before-snapshot: /backups/onpage-portland-before-20261029-174136.json
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
  changed: page	deep-tissue-massage
  changed: page	home
  changed: page	thai-massage
  rows now: iej_options	447
  rows now: iej_postmeta	627
  rows now: iej_posts	108

REHEARSAL PASSED: onpage-portland/install-onpage-portland.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-29 17:41:41 UTC_

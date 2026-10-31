# Site op `chair-rehearse-v2-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `chair-massage/install-chair-massage.php` |
| lane | ops (site-ops.yml) |
| commit | `6d365aa3dc5fdea2c840c5491c08d4e7605dfcd5` on `feat/2026-10-30-chair-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41178985348 |
| started | 2026-10-31 07:02:29 UTC |

## rehearsal of chair-massage/install-chair-massage.php on a throwaway copy of staging38: PASS

```text
Rehearsal of chair-massage/install-chair-massage.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 713154 bytes gzipped, files 316M
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

[preflight] wp eval-file chair-massage/install-chair-massage.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready picture=absent page=absent services_row=absent strip_snippet=ready jump_snippet=ready
  -> PASS  preflight

[apply] wp eval-file chair-massage/install-chair-massage.php apply
    Success: Added page #1298 (/chair-massage/), picture #1297, the Services row, and 'chair-massage' in snippets #1268 and #1232. Before-snapshot: /backups/chair-massage-before-20261031-070348.json
  -> PASS  apply

[apply-again] wp eval-file chair-massage/install-chair-massage.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied picture=exact page=exact services_row=exact strip_snippet=applied jump_snippet=applied
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file chair-massage/install-chair-massage.php rollback /backups/chair-massage-before-20261031-070348.json
    Success: Rollback done: the page and the picture are deleted, the Services row is out and both snippets are back (Services content byte-identical to the snapshot; strip snippet byte-identical; jump-links snippet byte-identical).
  -> PASS  rollback with chair-massage-before-20261031-070348.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file chair-massage/install-chair-massage.php apply
    Success: Added page #1302 (/chair-massage/), picture #1301, the Services row, and 'chair-massage' in snippets #1268 and #1232. Before-snapshot: /backups/chair-massage-before-20261031-070351.json
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
  changed: page	chair-massage
  changed: page	services
  changed: seo	1298:surerank_settings_general
  changed: snippet	1232
  changed: snippet	1268
  rows now: iej_options	447
  rows now: iej_postmeta	644
  rows now: iej_posts	119

REHEARSAL PASSED: chair-massage/install-chair-massage.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-31 07:03:56 UTC_

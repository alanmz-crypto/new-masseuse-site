# Site op `corp-rehearse-5`

| | |
|---|---|
| operation | `rehearse` on `staging37` |
| installer | `corporate/install-corporate-massage.php` |
| lane | ops (site-ops.yml) |
| commit | `ac83b612c015c4d8e1f8be03e1d368aa4e7b8035` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40143596077 |
| started | 2026-10-24 23:39:10 UTC |

## rehearsal of corporate/install-corporate-massage.php on a throwaway copy of staging37: PASS

```text
Rehearsal of corporate/install-corporate-massage.php on a throwaway copy of staging37 (staging37.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging37.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging37.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 556031 bytes gzipped, files 239M
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

[preflight] wp eval-file corporate/install-corporate-massage.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready page=absent picture=absent services_row=absent menu_item=absent snippet=absent css_block=absent
  -> PASS  preflight

[apply] wp eval-file corporate/install-corporate-massage.php apply
    Success: Added page #1273 (/corporate-massage-portland/), picture #1276, the Services row, menu item #1278, snippet #1275 "CorporateBand" and the ts-corp CSS block. Before-snapshot: /backups/corporate-massage-before-20261024-234009.json
  -> PASS  apply

[apply-again] wp eval-file corporate/install-corporate-massage.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied page=exact picture=exact services_row=exact menu_item=exact snippet=exact css_block=exact
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file corporate/install-corporate-massage.php rollback /backups/corporate-massage-before-20261024-234009.json
    Success: Rollback done: the page, snippet, menu item and picture are deleted, the menu order is back, the Services row and the ts-corp block are out (Services content byte-identical to the snapshot; Additional CSS byte-identical).
  -> PASS  rollback with corporate-massage-before-20261024-234009.json
  -> PASS  after rollback every fingerprint equals the baseline (A)

[re-apply] wp eval-file corporate/install-corporate-massage.php apply
    Success: Added page #1281 (/corporate-massage-portland/), picture #1284, the Services row, menu item #1286, snippet #1283 "CorporateBand" and the ts-corp CSS block. Before-snapshot: /backups/corporate-massage-before-20261024-234012.json
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
  changed: css	29
  changed: option	wpcode_snippets
  changed: page	corporate-massage-portland
  changed: page	services
  changed: seo	1273:surerank_settings_general
  changed: snippet	1275
  rows now: iej_options	447
  rows now: iej_postmeta	626
  rows now: iej_posts	100
  rows now: iej_term_relationships	52

REHEARSAL PASSED: corporate/install-corporate-massage.php on a copy of staging37 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-24 23:40:16 UTC_

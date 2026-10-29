# Site op `contact-address-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `contact-address/install-contact-address.php` |
| lane | ops (site-ops.yml) |
| commit | `3dd1fcd0a367f396a977a820129d31a2216d23d4` on `feat/2026-10-29-contact-address` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40919920657 |
| started | 2026-10-29 17:17:47 UTC |

## rehearsal of contact-address/install-contact-address.php on a throwaway copy of staging38: PASS

```text
Rehearsal of contact-address/install-contact-address.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 625215 bytes gzipped, files 239M
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

[preflight] wp eval-file contact-address/install-contact-address.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready old_text_count=1 new_text_count=0
  -> PASS  preflight

[apply] wp eval-file contact-address/install-contact-address.php apply
    Success: Page #301 (/contact/): the Address line now says "Portland, OR". Before-snapshot: /backups/contact-address-before-20261029-171850.json
  -> PASS  apply

[apply-again] wp eval-file contact-address/install-contact-address.php apply
    Success: Nothing to do: the Address line is already there. host=http://localhost:8090 state=applied old_text_count=0 new_text_count=1
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file contact-address/install-contact-address.php rollback /backups/contact-address-before-20261029-171850.json
    Success: Rollback done: the old Address text is back (page byte-identical to the snapshot).
  -> PASS  rollback with contact-address-before-20261029-171850.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file contact-address/install-contact-address.php apply
    Success: Page #301 (/contact/): the Address line now says "Portland, OR". Before-snapshot: /backups/contact-address-before-20261029-171854.json
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
  changed: page	contact
  rows now: iej_options	447
  rows now: iej_postmeta	631
  rows now: iej_posts	105

REHEARSAL PASSED: contact-address/install-contact-address.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-29 17:18:58 UTC_

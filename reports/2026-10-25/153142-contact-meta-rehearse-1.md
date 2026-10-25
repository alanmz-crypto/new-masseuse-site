# Site op `contact-meta-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `contact-meta/install-contact-meta.php` |
| lane | ops (site-ops.yml) |
| commit | `5c8094c9b5a52d395fdd2509d4951892a6221f60` on `feat/2026-10-25-contact-meta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40207243851 |
| started | 2026-10-25 15:30:27 UTC |

## rehearsal of contact-meta/install-contact-meta.php on a throwaway copy of staging38: PASS

```text
Rehearsal of contact-meta/install-contact-meta.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 624812 bytes gzipped, files 239M
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

[preflight] wp eval-file contact-meta/install-contact-meta.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready pages_with_slug=1
  -> PASS  preflight

[apply] wp eval-file contact-meta/install-contact-meta.php apply
    Success: Page #301 (/contact/): SEO description is set. Before-snapshot: /backups/contact-meta-before-20261025-153136.json
  -> PASS  apply

[apply-again] wp eval-file contact-meta/install-contact-meta.php apply
    Success: Nothing to do: the description is already there. host=http://localhost:8090 state=applied pages_with_slug=1
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file contact-meta/install-contact-meta.php rollback /backups/contact-meta-before-20261025-153136.json
    Success: Rollback done: the page has exactly the SureRank rows it had before (content hash unchanged).
  -> PASS  rollback with contact-meta-before-20261025-153136.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file contact-meta/install-contact-meta.php apply
    Success: Page #301 (/contact/): SEO description is set. Before-snapshot: /backups/contact-meta-before-20261025-153138.json
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
  changed: seo	301:surerank_settings_general

REHEARSAL PASSED: contact-meta/install-contact-meta.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-25 15:31:42 UTC_

# Site op `portland-meta-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `portland-meta/install-portland-meta.php` |
| lane | ops (site-ops.yml) |
| commit | `ad79974406682f095a06a061fcf71d87653103ff` on `feat/2026-10-29-portland-meta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40916569962 |
| started | 2026-10-29 16:57:23 UTC |

## rehearsal of portland-meta/install-portland-meta.php on a throwaway copy of staging38: PASS

```text
Rehearsal of portland-meta/install-portland-meta.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 625184 bytes gzipped, files 239M
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

[preflight] wp eval-file portland-meta/install-portland-meta.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready pages={"home":"ready","thai-massage":"ready","therapeutic-massage":"ready","deep-tissue-massage":"ready"}
  -> PASS  preflight

[apply] wp eval-file portland-meta/install-portland-meta.php apply
    Success: SEO titles and meta descriptions are set on all four pages (Home, Thai, Therapeutic, Deep Tissue). Before-snapshot: /backups/portland-meta-before-20261029-165829.json
  -> PASS  apply

[apply-again] wp eval-file portland-meta/install-portland-meta.php apply
    Success: Nothing to do: all four pages already have the new titles and descriptions. host=http://localhost:8090 state=applied pages={"home":"applied","thai-massage":"applied","therapeutic-massage":"applied","deep-tissue-massage":"applied"}
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file portland-meta/install-portland-meta.php rollback /backups/portland-meta-before-20261029-165829.json
    Success: Rollback done: all four pages have exactly the SureRank rows they had before.
  -> PASS  rollback with portland-meta-before-20261029-165829.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file portland-meta/install-portland-meta.php apply
    Success: SEO titles and meta descriptions are set on all four pages (Home, Thai, Therapeutic, Deep Tissue). Before-snapshot: /backups/portland-meta-before-20261029-165832.json
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
  changed: seo	1091:surerank_settings_general
  changed: seo	1107:surerank_settings_general
  changed: seo	1160:surerank_settings_general
  changed: seo	296:surerank_settings_general

REHEARSAL PASSED: portland-meta/install-portland-meta.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-29 16:58:37 UTC_

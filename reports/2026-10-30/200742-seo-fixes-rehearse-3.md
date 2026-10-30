# Site op `seo-fixes-rehearse-3`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `seo-fixes/install-seo-fixes.php` |
| lane | ops (site-ops.yml) |
| commit | `bc4c7647657ef328b9d1926cd8ec7542fc8f96e3` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41120621638 |
| started | 2026-10-30 20:06:34 UTC |

## rehearsal of seo-fixes/install-seo-fixes.php on a throwaway copy of staging38: PASS

```text
Rehearsal of seo-fixes/install-seo-fixes.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 706819 bytes gzipped, files 316M
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

[preflight] wp eval-file seo-fixes/install-seo-fixes.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready services=ready corporate=ready picture=media#1269
  -> PASS  preflight

[apply] wp eval-file seo-fixes/install-seo-fixes.php apply
    Success: Services description now says "in Portland"; the corporate page has its share picture (media #1269, set once in SureRank, no copy made, not drawn on the page). Before-snapshot: /backups/seo-fixes-before-20261030-200734.json
  -> PASS  apply

[apply-again] wp eval-file seo-fixes/install-seo-fixes.php apply
    Success: Nothing to do: both fixes are already there. host=http://localhost:8090 state=applied services=applied corporate=applied picture=media#1269
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file seo-fixes/install-seo-fixes.php rollback /backups/seo-fixes-before-20261030-200734.json
    Success: Rollback done: both pages have exactly their old SureRank rows again.
  -> PASS  rollback with seo-fixes-before-20261030-200734.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file seo-fixes/install-seo-fixes.php apply
    Success: Services description now says "in Portland"; the corporate page has its share picture (media #1269, set once in SureRank, no copy made, not drawn on the page). Before-snapshot: /backups/seo-fixes-before-20261030-200737.json
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
  changed: seo	1266:surerank_settings_social
  changed: seo	299:surerank_settings_general
  rows now: iej_postmeta	637

REHEARSAL PASSED: seo-fixes/install-seo-fixes.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-30 20:07:41 UTC_

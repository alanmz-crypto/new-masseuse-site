# Site op `a11y-v4-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging36` |
| installer | `accessibility/install-accessibility-page-v4.php` |
| lane | ops (site-ops.yml) |
| commit | `631a8214a293f3d18071456496e13081cf4cff25` on `claude/charming-galileo-hb7mf8` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40125241330 |
| started | 2026-10-24 19:25:57 UTC |

## rehearsal of accessibility/install-accessibility-page-v4.php on a throwaway copy of staging36: PASS

```text
Rehearsal of accessibility/install-accessibility-page-v4.php on a throwaway copy of staging36 (staging36.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging36.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging36.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 926655 bytes gzipped, files 238M
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

[preflight] wp eval-file accessibility/install-accessibility-page-v4.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=old pages_with_slug=1 research_sections_present=3_of_3
  -> PASS  preflight

[apply] wp eval-file accessibility/install-accessibility-page-v4.php apply
    Success: Page #1230 (/accessibility/) now holds version 4 (the Deep Tissue page named in the research summaries sentence and the screen reader limit, and the new date). Before-snapshot: /backups/accessibility-page-v4-before-20261024-192733.json
  -> PASS  apply

[apply-again] wp eval-file accessibility/install-accessibility-page-v4.php apply
    Success: Nothing to do: already version 4. host=http://localhost:8090 state=new pages_with_slug=1 research_sections_present=3_of_3
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file accessibility/install-accessibility-page-v4.php rollback /backups/accessibility-page-v4-before-20261024-192733.json
    Success: Rollback done: the page holds version 3 again.
  -> PASS  rollback with accessibility-page-v4-before-20261024-192733.json
  -> PASS  after rollback every fingerprint equals the baseline (A)

[re-apply] wp eval-file accessibility/install-accessibility-page-v4.php apply
    Success: Page #1230 (/accessibility/) now holds version 4 (the Deep Tissue page named in the research summaries sentence and the screen reader limit, and the new date). Before-snapshot: /backups/accessibility-page-v4-before-20261024-192738.json
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
  changed: page	accessibility
  rows now: iej_options	447
  rows now: iej_postmeta	622
  rows now: iej_posts	170

REHEARSAL PASSED: accessibility/install-accessibility-page-v4.php on a copy of staging36 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-24 19:27:44 UTC_

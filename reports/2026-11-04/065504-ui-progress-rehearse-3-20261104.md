# Site op `ui-progress-rehearse-3-20261104`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `reading-progress/install-reading-progress.php` |
| lane | ops (site-ops.yml) |
| commit | `a52d232a2f165b7f34cdd78d8754c7d23def5b72` on `feat/2026-11-04-reading-progress` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41788338874 |
| started | 2026-11-04 06:52:38 UTC |

## rehearsal of reading-progress/install-reading-progress.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of reading-progress/install-reading-progress.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 1052904 bytes gzipped, files 239M
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

[preflight] wp eval-file reading-progress/install-reading-progress.php preflight
    Success: Reading progress: ready; CSS bytes=65418; nothing changed.
  -> PASS  preflight

[apply] wp eval-file reading-progress/install-reading-progress.php apply
    Success: Reading progress applied. Before-snapshot: /backups/reading-progress-before-20261104-065433-0aa98da5.json
  -> PASS  apply

[apply-again] wp eval-file reading-progress/install-reading-progress.php apply
    Success: Nothing to do: reading progress already applied.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file reading-progress/install-reading-progress.php rollback /backups/reading-progress-before-20261104-065433-0aa98da5.json
    Success: Reading progress rolled back; exact original CSS restored.
  -> PASS  rollback with reading-progress-before-20261104-065433-0aa98da5.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file reading-progress/install-reading-progress.php apply
    Success: Reading progress applied. Before-snapshot: /backups/reading-progress-before-20261104-065436-3d70398d.json
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

[4b] rendered copy gates on the applied throwaway copy
  -> PASS  Home copy and links
PASS footer: 'Résumé & experience' links to /work-with-me/
PASS footer: licence line and medical-care disclaimer still present  [Portland, OR butsabad@gmail.com · Résumé & experience · Privacy Policy · Accessibility Home About Services Corporate Massage For Practices C]
PASS no shared restricted wording anywhere in the Home main body
INFO robots.txt first rules: User-agent: * | Disallow: /

RESULT: all checks passed
  -> PASS  client-page regulated copy, structure and links

RESULT: all checks passed

[4c] package browser checks on the applied throwaway copy
  -> FAIL  package browser checks
PASS home 1280 light: correct scope
PASS home 1280 dark: no side-scroll
PASS home 1280 dark: correct scope
FAIL home 390 light: no side-scroll
PASS home 390 light: correct scope
FAIL home 390 dark: no side-scroll
PASS home 390 dark: correct scope
FAIL home 320 light: no side-scroll
PASS home 320 light: correct scope
FAIL home 320 dark: no side-scroll
PASS home 320 dark: correct scope
PASS home: no browser script errors
PASS contact: HTTP 200
PASS contact: exact CSS installed once
PASS contact: citation scope
PASS contact: browser supports native timeline
PASS contact 1280 light: no side-scroll
PASS contact 1280 light: correct scope
PASS contact 1280 dark: no side-scroll
PASS contact 1280 dark: correct scope
PASS contact 390 light: no side-scroll
PASS contact 390 light: correct scope
PASS contact 390 dark: no side-scroll
PASS contact 390 dark: correct scope
PASS contact 320 light: no side-scroll
PASS contact 320 light: correct scope
PASS contact 320 dark: no side-scroll
PASS contact 320 dark: correct scope
PASS contact: no browser script errors
Reading progress (chromium): 210 browser assertions, 4 failures
FAIL home 390 light: no side-scroll
FAIL home 390 dark: no side-scroll
FAIL home 320 light: no side-scroll
FAIL home 320 dark: no side-scroll

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: css	29
  rows now: iej_options	447
  rows now: iej_posts	163

REHEARSAL FAILED: 1 check(s) failed (see -> FAIL above).
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-04 06:55:03 UTC_

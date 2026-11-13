# Site op `ww-page-rehearse-39-1`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `winding-willow/install-winding-willow-page.php` |
| lane | ops (site-ops.yml) |
| commit | `3559e8b2ce1f8c24def9e7bc56565862d2e794be` on `feat/2026-11-13-winding-willow-page` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43404007052 |
| started | 2026-11-13 09:45:53 UTC |

## rehearsal of winding-willow/install-winding-willow-page.php on a throwaway copy of staging39: PASS

```text
Rehearsal of winding-willow/install-winding-willow-page.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 685281 bytes gzipped, files 238M
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

[preflight] wp eval-file winding-willow/install-winding-willow-page.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready posts_with_slug=0
  -> PASS  preflight

[apply] wp eval-file winding-willow/install-winding-willow-page.php apply
    Success: Added page #1385 /winding-willow/ (unlisted: no menu, Services or snippet change). Before-snapshot: /backups/winding-willow-page-before-20261113-094718.json
  -> PASS  apply

[apply-again] wp eval-file winding-willow/install-winding-willow-page.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied posts_with_slug=1
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file winding-willow/install-winding-willow-page.php rollback /backups/winding-willow-page-before-20261113-094718.json
    Success: Rolled back: page #1385 /winding-willow/ deleted; the site is as before the apply.
  -> PASS  rollback with winding-willow-page-before-20261113-094718.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file winding-willow/install-winding-willow-page.php apply
    Success: Added page #1386 /winding-willow/ (unlisted: no menu, Services or snippet change). Before-snapshot: /backups/winding-willow-page-before-20261113-094721.json
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

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: page	winding-willow
  changed: seo	1385:surerank_settings_general
  rows now: iej_options	448
  rows now: iej_postmeta	733
  rows now: iej_posts	108

REHEARSAL PASSED: winding-willow/install-winding-willow-page.php on a copy of staging39 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-13 09:47:30 UTC_

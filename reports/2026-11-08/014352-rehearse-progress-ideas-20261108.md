# Site op `rehearse-progress-ideas-20261108`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/install-progress-ideas.php` |
| lane | ops (site-ops.yml) |
| commit | `190753870e529f1045402493eb19182f74a482ef` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42435393461 |
| started | 2026-11-08 01:42:24 UTC |

## rehearsal of link-ideas/install-progress-ideas.php on a throwaway copy of staging39: PASS

```text
Rehearsal of link-ideas/install-progress-ideas.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 587073 bytes gzipped, files 238M
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

[preflight] wp eval-file link-ideas/install-progress-ideas.php preflight
    Success: ready: no page with slug progress-nav-ideas (content 36725 bytes, sha256 5f1686593644).
  -> PASS  preflight

[apply] wp eval-file link-ideas/install-progress-ideas.php apply
    Success: Added page #1345 (/progress-nav-ideas/). Before-snapshot: /backups/progress-ideas-before-20261108-014340-1b8b5eee.json
  -> PASS  apply

[apply-again] wp eval-file link-ideas/install-progress-ideas.php apply
    Success: Already applied: 1 page(s) with slug progress-nav-ideas, #1345 publish, exactly as written. Nothing written.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/install-progress-ideas.php rollback /backups/progress-ideas-before-20261108-014340-1b8b5eee.json
    Success: Rollback done: page #1345 (/progress-nav-ideas/) is deleted; nothing else was ever changed.
  -> PASS  rollback with progress-ideas-before-20261108-014340-1b8b5eee.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file link-ideas/install-progress-ideas.php apply
    Success: Added page #1346 (/progress-nav-ideas/). Before-snapshot: /backups/progress-ideas-before-20261108-014344-59cbeda9.json
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
  changed: page	progress-nav-ideas
  rows now: iej_options	448
  rows now: iej_postmeta	695
  rows now: iej_posts	100

REHEARSAL PASSED: link-ideas/install-progress-ideas.php on a copy of staging39 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-08 01:43:52 UTC_

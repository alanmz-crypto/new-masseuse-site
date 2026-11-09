# Site op `peek-data-rehearse-20261109-1`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/peek-page/install-peek-thai-data.php` |
| lane | ops (site-ops.yml) |
| commit | `5825a9839931899297d33c8da6622665e9205bbf` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42549075999 |
| started | 2026-11-09 02:06:41 UTC |

## rehearsal of link-ideas/peek-page/install-peek-thai-data.php on a throwaway copy of staging39: PASS

```text
Rehearsal of link-ideas/peek-page/install-peek-thai-data.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 657082 bytes gzipped, files 238M
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

[preflight] wp eval-file link-ideas/peek-page/install-peek-thai-data.php preflight
    Success: page:thai-massage ready; nothing written.
  -> PASS  preflight

[apply] wp eval-file link-ideas/peek-page/install-peek-thai-data.php apply
    Success: Peek page:thai-massage applied; WordPress storage only. Before-snapshot: /backups/peek-template-page-thai-massage-before-20261109-020842-6f072d93.json
  -> PASS  apply

[apply-again] wp eval-file link-ideas/peek-page/install-peek-thai-data.php apply
    Success: page:thai-massage applied; nothing written.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/peek-page/install-peek-thai-data.php rollback /backups/peek-template-page-thai-massage-before-20261109-020842-6f072d93.json
    Success: Exact page rollback complete. Snapshot: /backups/peek-template-page-thai-massage-before-20261109-020842-6f072d93.json
  -> PASS  rollback with peek-template-page-thai-massage-before-20261109-020842-6f072d93.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file link-ideas/peek-page/install-peek-thai-data.php apply
    Success: Peek page:thai-massage applied; WordPress storage only. Before-snapshot: /backups/peek-template-page-thai-massage-before-20261109-020845-d421e8d8.json
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
  -> PASS  package browser checks

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: option	wpcode_snippets
  changed: snippet	1381
  rows now: iej_options	448
  rows now: iej_postmeta	715
  rows now: iej_posts	104
  rows now: iej_term_relationships	66

REHEARSAL PASSED: link-ideas/peek-page/install-peek-thai-data.php on a copy of staging39 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-09 02:08:56 UTC_

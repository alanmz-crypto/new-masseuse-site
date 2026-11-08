# Site op `rehearse-sitewide-cleanup-20261108`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/install-sitewide-cleanup.php` |
| lane | ops (site-ops.yml) |
| commit | `6e2f4f7bd002ce1a493c01f6e63971c82de35bd5` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42455855191 |
| started | 2026-11-08 06:42:48 UTC |

## rehearsal of link-ideas/install-sitewide-cleanup.php on a throwaway copy of staging39: PASS

```text
Rehearsal of link-ideas/install-sitewide-cleanup.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 624907 bytes gzipped, files 238M
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

[preflight] wp eval-file link-ideas/install-sitewide-cleanup.php preflight
    Success: ready: reading-progress blocks: 1, scroll-to-top-enable: true (CSS 69937 bytes, sha256 1b9d4a6fcc78).
  -> PASS  preflight

[apply] wp eval-file link-ideas/install-sitewide-cleanup.php apply
    Success: Removed the reading-progress block (1095 bytes) from the Additional CSS and switched off scroll-to-top-enable. Before-snapshot: /backups/sitewide-cleanup-before-20261108-064403-7594c96e.json
  -> PASS  apply

[apply-again] wp eval-file link-ideas/install-sitewide-cleanup.php apply
    Success: Already applied: reading-progress blocks: 0, scroll-to-top-enable: false. Nothing written.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/install-sitewide-cleanup.php rollback /backups/sitewide-cleanup-before-20261108-064403-7594c96e.json
    Success: Rollback done: the reading-progress block is back (CSS byte-identical to the snapshot) and scroll-to-top-enable is on again.
  -> PASS  rollback with sitewide-cleanup-before-20261108-064403-7594c96e.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file link-ideas/install-sitewide-cleanup.php apply
    Success: Removed the reading-progress block (1095 bytes) from the Additional CSS and switched off scroll-to-top-enable. Before-snapshot: /backups/sitewide-cleanup-before-20261108-064405-11be63f3.json
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
  changed: css	29
  changed: option	astra-settings
  rows now: iej_options	448
  rows now: iej_posts	103

REHEARSAL PASSED: link-ideas/install-sitewide-cleanup.php on a copy of staging39 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-08 06:44:12 UTC_

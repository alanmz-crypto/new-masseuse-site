# Site op `rehearse-link-ideas-20261108-3`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/install-link-ideas.php` |
| lane | ops (site-ops.yml) |
| commit | `79ffdbc808e807285048f94df8666b3d4ab0effc` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42430245804 |
| started | 2026-11-08 00:33:10 UTC |

## rehearsal of link-ideas/install-link-ideas.php on a throwaway copy of staging39: PASS

```text
Rehearsal of link-ideas/install-link-ideas.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 571737 bytes gzipped, files 238M
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

[preflight] wp eval-file link-ideas/install-link-ideas.php preflight
    Success: ready: no page with slug in-page-link-ideas (content 31664 bytes, sha256 47c30afbc4db).
  -> PASS  preflight

[apply] wp eval-file link-ideas/install-link-ideas.php apply
    Success: Added page #1340 (/in-page-link-ideas/). Before-snapshot: /backups/link-ideas-before-20261108-003406-8cb25d3d.json
  -> PASS  apply

[apply-again] wp eval-file link-ideas/install-link-ideas.php apply
    Success: Already applied: 1 page(s) with slug in-page-link-ideas, #1340 publish, exactly as written. Nothing written.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/install-link-ideas.php rollback /backups/link-ideas-before-20261108-003406-8cb25d3d.json
    Success: Rollback done: page #1340 (/in-page-link-ideas/) is deleted; nothing else was ever changed.
  -> PASS  rollback with link-ideas-before-20261108-003406-8cb25d3d.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file link-ideas/install-link-ideas.php apply
    Success: Added page #1341 (/in-page-link-ideas/). Before-snapshot: /backups/link-ideas-before-20261108-003408-686f0450.json
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
  changed: page	in-page-link-ideas
  rows now: iej_options	448
  rows now: iej_postmeta	684
  rows now: iej_posts	98

REHEARSAL PASSED: link-ideas/install-link-ideas.php on a copy of staging39 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-08 00:34:14 UTC_

# Site op `seo-rehearse-live-1`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `seo-booking/install-seo-booking.php` |
| lane | ops (site-ops.yml) |
| commit | `53e96464635b2a920938d40f6bd3d38ba9f86dcd` on `feat/2026-11-20-seo-booking-path` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/44730996274 |
| started | 2026-11-20 11:51:51 UTC |

## rehearsal of seo-booking/install-seo-booking.php on a throwaway copy of live: PASS

```text
Rehearsal of seo-booking/install-seo-booking.php on a throwaway copy of live (thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.5
  source home=http://thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 636578 bytes gzipped, files 239M
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

[preflight] wp eval-file seo-booking/install-seo-booking.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready faq thai-massage=ready faq therapeutic-massage=ready faq deep-tissue-massage=ready description thai-massage=ready description deep-tissue-massage=ready description front=ready snippet=ready
  -> PASS  preflight

[apply] wp eval-file seo-booking/install-seo-booking.php apply
    Success: Applied: 3 FAQ sentences, 3 descriptions, snippet #1023 sameAs. Before-snapshot: /backups/seo-booking-before-20261120-115252.json
  -> PASS  apply

[apply-again] wp eval-file seo-booking/install-seo-booking.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied faq thai-massage=applied faq therapeutic-massage=applied faq deep-tissue-massage=applied description thai-massage=applied description deep-tissue-massage=applied description front=applied snippet=applied
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file seo-booking/install-seo-booking.php rollback /backups/seo-booking-before-20261120-115252.json
    Success: Rollback done: pages byte-identical to the snapshot; SureRank rows identical; snippet code restored.
  -> PASS  rollback with seo-booking-before-20261120-115252.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file seo-booking/install-seo-booking.php apply
    Success: Applied: 3 FAQ sentences, 3 descriptions, snippet #1023 sameAs. Before-snapshot: /backups/seo-booking-before-20261120-115254.json
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
PASS footer: licence line and medical-care disclaimer still present  [Portland, OR butsabad@gmail.com · Résumé & experience · Privacy Policy · Accessibility Home About Services Corporate Massage Winding Willow ]
PASS no shared restricted wording anywhere in the Home main body
INFO robots.txt first rules: User-agent: * | Disallow: /wp-admin/ | Allow: /wp-admin/admin-ajax.php

RESULT: all checks passed
  -> PASS  client-page regulated copy, structure and links

RESULT: all checks passed

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: option	wpcode_snippets
  changed: page	deep-tissue-massage
  changed: page	thai-massage
  changed: page	therapeutic-massage
  changed: seo	1091:surerank_settings_general
  changed: seo	1160:surerank_settings_general
  changed: seo	296:surerank_settings_general
  changed: snippet	1023
  rows now: iej_options	448
  rows now: iej_postmeta	712
  rows now: iej_posts	107

REHEARSAL PASSED: seo-booking/install-seo-booking.php on a copy of live (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-20 11:53:00 UTC_

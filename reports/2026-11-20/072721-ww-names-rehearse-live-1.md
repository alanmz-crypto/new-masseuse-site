# Site op `ww-names-rehearse-live-1`

| | |
|---|---|
| operation | `rehearse` on `live` |
| installer | `winding-willow/install-winding-willow.php` |
| lane | ops (site-ops.yml) |
| commit | `d66d977ba8c13caf8629b9d0f3cfb2fcfe2192a5` on `feat/2026-11-20-winding-willow-watch` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/44694909541 |
| started | 2026-11-20 07:26:06 UTC |

## rehearsal of winding-willow/install-winding-willow.php on a throwaway copy of live: PASS

```text
Rehearsal of winding-willow/install-winding-willow.php on a throwaway copy of live (thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.5
  source home=http://thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 630579 bytes gzipped, files 239M
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

[preflight] wp eval-file winding-willow/install-winding-willow.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=update page=previous menu_item=exact css_block=exact matching_posts=1344 (package sha256 d93cee0213be59b881281643433cbe83e25c5673fedee2764758fdc942ff49b2)
  -> PASS  preflight

[apply] wp eval-file winding-willow/install-winding-willow.php apply
    Success: Updated the content of page #1344 (/winding-willow/, 30765 bytes, sha256 d93cee0213be59b881281643433cbe83e25c5673fedee2764758fdc942ff49b2); menu item #1346 and the ts-ww CSS block unchanged. Before-snapshot: /backups/winding-willow-before-20261120-072708.json
  -> PASS  apply

[apply-again] wp eval-file winding-willow/install-winding-willow.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied page=exact menu_item=exact css_block=exact matching_posts=1344
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file winding-willow/install-winding-willow.php rollback /backups/winding-willow-before-20261120-072708.json
    Success: Rollback done: page #1344 has its previous content back; the menu item and the ts-ww CSS block were not touched.
  -> PASS  rollback with winding-willow-before-20261120-072708.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file winding-willow/install-winding-willow.php apply
    Success: Updated the content of page #1344 (/winding-willow/, 30765 bytes, sha256 d93cee0213be59b881281643433cbe83e25c5673fedee2764758fdc942ff49b2); menu item #1346 and the ts-ww CSS block unchanged. Before-snapshot: /backups/winding-willow-before-20261120-072711.json
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
  changed: page	winding-willow
  rows now: iej_options	448
  rows now: iej_postmeta	714
  rows now: iej_posts	104

REHEARSAL PASSED: winding-willow/install-winding-willow.php on a copy of live (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-20 07:27:19 UTC_

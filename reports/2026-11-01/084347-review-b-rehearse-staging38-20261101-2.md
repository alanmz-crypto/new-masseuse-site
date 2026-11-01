# Site op `review-b-rehearse-staging38-20261101-2`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `review-b/install-bundle-b.php` |
| lane | ops (site-ops.yml) |
| commit | `095d2999f3e6ba4022c95f74409129d5f2ebecfa` on `codex/2026-11-01-review-bundle-b` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41289969039 |
| started | 2026-11-01 08:42:28 UTC |

## rehearsal of review-b/install-bundle-b.php on a throwaway copy of staging38: PASS

```text
Rehearsal of review-b/install-bundle-b.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 840900 bytes gzipped, files 316M
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

[preflight] wp eval-file review-b/install-bundle-b.php preflight
    metadata therapeutic-massage=ready
    metadata deep-tissue-massage=ready
    metadata chair-massage=ready
    metadata corporate-massage-portland=ready
    metadata privacy-policy=ready
    metadata accessibility=ready
    metadata services=ready
    Organization about=ready
    Organization contact=ready
    Corporate https share image=unchanged (HTTP rehearsal)
    Home approved share card=ready
    global Person=ready
    tagline=ready
    LocalBusinessInfo=ready
    Home by arrangement=ready
    approved share-card media=absent (ready to import)
    host=http://localhost:8090 state=ready
    Success: Preflight ready; nothing changed.
  -> PASS  preflight

[apply] wp eval-file review-b/install-bundle-b.php apply
    metadata therapeutic-massage=ready
    metadata deep-tissue-massage=ready
    metadata chair-massage=ready
    metadata corporate-massage-portland=ready
    metadata privacy-policy=ready
    metadata accessibility=ready
    metadata services=ready
    Organization about=ready
    Organization contact=ready
    Corporate https share image=unchanged (HTTP rehearsal)
    Home approved share card=ready
    global Person=ready
    tagline=ready
    LocalBusinessInfo=ready
    Home by arrangement=ready
    approved share-card media=absent (ready to import)
    host=http://localhost:8090 state=ready
    Success: Applied Bundle B and the approved default/Home share card. Before-snapshot: /backups/bundle-b-before-20261101-084334.json
  -> PASS  apply

[apply-again] wp eval-file review-b/install-bundle-b.php apply
    metadata therapeutic-massage=applied
    metadata deep-tissue-massage=applied
    metadata chair-massage=applied
    metadata corporate-massage-portland=applied
    metadata privacy-policy=applied
    metadata accessibility=applied
    metadata services=applied
    Organization about=applied
    Organization contact=applied
    Corporate https share image=unchanged (HTTP rehearsal)
    Home approved share card=applied
    global Person=applied
    tagline=applied
    LocalBusinessInfo=applied
    Home by arrangement=applied
    approved share-card media=exact
    host=http://localhost:8090 state=applied
    Success: Already applied; nothing changed.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file review-b/install-bundle-b.php rollback /backups/bundle-b-before-20261101-084334.json
    metadata therapeutic-massage=applied
    metadata deep-tissue-massage=applied
    metadata chair-massage=applied
    metadata corporate-massage-portland=applied
    metadata privacy-policy=applied
    metadata accessibility=applied
    metadata services=applied
    Organization about=applied
    Organization contact=applied
    Corporate https share image=unchanged (HTTP rehearsal)
    Home approved share card=applied
    global Person=applied
    tagline=applied
    LocalBusinessInfo=applied
    Home by arrangement=applied
    approved share-card media=exact
    host=http://localhost:8090 state=applied
    Success: Exact rollback complete from /backups/bundle-b-before-20261101-084334.json
  -> PASS  rollback with bundle-b-before-20261101-084334.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file review-b/install-bundle-b.php apply
    metadata therapeutic-massage=ready
    metadata deep-tissue-massage=ready
    metadata chair-massage=ready
    metadata corporate-massage-portland=ready
    metadata privacy-policy=ready
    metadata accessibility=ready
    metadata services=ready
    Organization about=ready
    Organization contact=ready
    Corporate https share image=unchanged (HTTP rehearsal)
    Home approved share card=ready
    global Person=ready
    tagline=ready
    LocalBusinessInfo=ready
    Home by arrangement=ready
    approved share-card media=absent (ready to import)
    host=http://localhost:8090 state=ready
    Success: Applied Bundle B and the approved default/Home share card. Before-snapshot: /backups/bundle-b-before-20261101-084338.json
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
PASS no restricted OBMT wording in any new copy
INFO robots.txt first rules: User-agent: * | Disallow: /

RESULT: all checks passed
  -> PASS  client-page regulated copy, structure and links

RESULT: all checks passed

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: option	blogdescription
  changed: option	surerank_settings
  changed: option	wpcode_snippets
  changed: page	home
  changed: seo	1091:surerank_settings_general
  changed: seo	1107:surerank_settings_general
  changed: seo	1227:surerank_settings_general
  changed: seo	1230:surerank_settings_general
  changed: seo	1266:surerank_settings_general
  changed: seo	1302:surerank_settings_general
  changed: seo	296:surerank_settings_social
  changed: seo	298:surerank_settings_schemas
  changed: seo	299:surerank_settings_general
  changed: seo	301:surerank_settings_schemas
  changed: snippet	1023
  rows now: iej_options	447
  rows now: iej_postmeta	656
  rows now: iej_posts	133

REHEARSAL PASSED: review-b/install-bundle-b.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-11-01 08:43:46 UTC_

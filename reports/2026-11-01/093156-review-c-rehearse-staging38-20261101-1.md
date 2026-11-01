# Site op `review-c-rehearse-staging38-20261101-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `review-c/install-bundle-c.php` |
| lane | ops (site-ops.yml) |
| commit | `0036816b395d12ab431fbe253fa5aacb7b0e0338` on `codex/2026-11-01-review-bundle-c` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41292792987 |
| started | 2026-11-01 09:21:43 UTC |

## rehearsal of review-c/install-bundle-c.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of review-c/install-bundle-c.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 849150 bytes gzipped, files 317M
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

[preflight] wp eval-file review-c/install-bundle-c.php preflight
    page chair-massage=ready
    page corporate-massage-portland=ready
    page accessibility=ready
    page privacy-policy=ready
    page work-with-me=ready
    page thai-massage=ready
    page therapeutic-massage=ready
    page deep-tissue-massage=ready
    page contact=ready
    page services=ready
    page about=ready
    page home=ready
    Additional CSS=ready
    snippet CorporateBand=ready
    snippet A11yPageJumpLinks=ready
    Astra blog-meta=ready
    new snippet SiteReviewAccessibility=ready
    new snippet SiteReviewUtilities=ready
    host=http://localhost:8090 state=ready
    Success: Preflight ready; nothing changed.
  -> PASS  preflight

[apply] wp eval-file review-c/install-bundle-c.php apply
    page chair-massage=ready
    page corporate-massage-portland=ready
    page accessibility=ready
    page privacy-policy=ready
    page work-with-me=ready
    page thai-massage=ready
    page therapeutic-massage=ready
    page deep-tissue-massage=ready
    page contact=ready
    page services=ready
    page about=ready
    page home=ready
    Additional CSS=ready
    snippet CorporateBand=ready
    snippet A11yPageJumpLinks=ready
    Astra blog-meta=ready
    new snippet SiteReviewAccessibility=ready
    new snippet SiteReviewUtilities=ready
    host=http://localhost:8090 state=ready
    Success: Applied Bundle C. Before-snapshot: /backups/bundle-c-before-20261101-093037.json
  -> PASS  apply

[apply-again] wp eval-file review-c/install-bundle-c.php apply
    page chair-massage=applied
    page corporate-massage-portland=applied
    page accessibility=applied
    page privacy-policy=applied
    page work-with-me=applied
    page thai-massage=applied
    page therapeutic-massage=applied
    page deep-tissue-massage=applied
    page contact=applied
    page services=applied
    page about=applied
    page home=applied
    Additional CSS=applied
    snippet CorporateBand=applied
    snippet A11yPageJumpLinks=applied
    Astra blog-meta=applied
    new snippet SiteReviewAccessibility=applied
    new snippet SiteReviewUtilities=applied
    host=http://localhost:8090 state=applied
    Success: Already applied; nothing changed.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file review-c/install-bundle-c.php rollback /backups/bundle-c-before-20261101-093037.json
    page chair-massage=applied
    page corporate-massage-portland=applied
    page accessibility=applied
    page privacy-policy=applied
    page work-with-me=applied
    page thai-massage=applied
    page therapeutic-massage=applied
    page deep-tissue-massage=applied
    page contact=applied
    page services=applied
    page about=applied
    page home=applied
    Additional CSS=applied
    snippet CorporateBand=applied
    snippet A11yPageJumpLinks=applied
    Astra blog-meta=applied
    new snippet SiteReviewAccessibility=applied
    new snippet SiteReviewUtilities=applied
    host=http://localhost:8090 state=applied
    Success: Exact rollback complete from /backups/bundle-c-before-20261101-093037.json
  -> PASS  rollback with bundle-c-before-20261101-093037.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file review-c/install-bundle-c.php apply
    page chair-massage=ready
    page corporate-massage-portland=ready
    page accessibility=ready
    page privacy-policy=ready
    page work-with-me=ready
    page thai-massage=ready
    page therapeutic-massage=ready
    page deep-tissue-massage=ready
    page contact=ready
    page services=ready
    page about=ready
    page home=ready
    Additional CSS=ready
    snippet CorporateBand=ready
    snippet A11yPageJumpLinks=ready
    Astra blog-meta=ready
    new snippet SiteReviewAccessibility=ready
    new snippet SiteReviewUtilities=ready
    host=http://localhost:8090 state=ready
    Success: Applied Bundle C. Before-snapshot: /backups/bundle-c-before-20261101-093043.json
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

[4c] package browser checks on the applied throwaway copy
  -> FAIL  package browser checks
LAYOUT {"path":"/","width":1280,"height":4570,"h1Y":328.609375,"h1Height":269.234375,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":"24px","heartRows":[1186,1218]}
LAYOUT {"path":"/","width":390,"height":5848,"h1Y":284.953125,"h1Height":125.734375,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":"12px","heartRows":[1263,1295,1358,1390,1453,1485]}
LAYOUT {"path":"/","width":320,"height":5989,"h1Y":329.9375,"h1Height":162,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":"12px","heartRows":[1290,1322,1385,1417,1480,1511]}
LAYOUT {"path":"/about/","width":1280,"height":2545,"h1Y":348.609375,"h1Height":112.578125,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/about/","width":390,"height":3122,"h1Y":328.890625,"h1Height":53.203125,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/about/","width":320,"height":3419,"h1Y":345.9375,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
FAIL /services/ 1280 light hidden Services H2 remains accessible without layout height
LAYOUT {"path":"/services/","width":1280,"height":3945,"h1Y":348.609375,"h1Height":112.578125,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
FAIL /services/ 1280 dark hidden Services H2 remains accessible without layout height
FAIL /services/ 390 light hidden Services H2 remains accessible without layout height
LAYOUT {"path":"/services/","width":390,"height":6127,"h1Y":328.890625,"h1Height":53.203125,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
FAIL /services/ 390 dark hidden Services H2 remains accessible without layout height
FAIL /services/ 320 light hidden Services H2 remains accessible without layout height
LAYOUT {"path":"/services/","width":320,"height":6659,"h1Y":345.9375,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
FAIL /services/ 320 dark hidden Services H2 remains accessible without layout height
LAYOUT {"path":"/thai-massage/","width":1280,"height":5747,"h1Y":355.453125,"h1Height":190.90625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/thai-massage/","width":390,"height":9510,"h1Y":309.453125,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/thai-massage/","width":320,"height":10905,"h1Y":326.5,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/therapeutic-massage/","width":1280,"height":6813,"h1Y":355.453125,"h1Height":190.90625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/therapeutic-massage/","width":390,"height":10184,"h1Y":337.390625,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/therapeutic-massage/","width":320,"height":11953,"h1Y":354.4375,"h1Height":125.734375,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/deep-tissue-massage/","width":1280,"height":5567,"h1Y":355.453125,"h1Height":190.90625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/deep-tissue-massage/","width":390,"height":9190,"h1Y":309.453125,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/deep-tissue-massage/","width":320,"height":10872,"h1Y":326.5,"h1Height":125.734375,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/chair-massage/","width":1280,"height":6593,"h1Y":348.609375,"h1Height":190.90625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/chair-massage/","width":390,"height":11017,"h1Y":328.890625,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/chair-massage/","width":320,"height":13205,"h1Y":345.9375,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
CONTRAST {"path":"/chair-massage/","theme":"light","tested":181,"minimum":9.133,"failures":[]}
CONTRAST {"path":"/chair-massage/","theme":"dark","tested":181,"minimum":11.365,"failures":[]}
LAYOUT {"path":"/corporate-massage-portland/","width":1280,"height":5892,"h1Y":252.609375,"h1Height":172.25,"h1MarginTop":"9.9px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/corporate-massage-portland/","width":390,"height":7465,"h1Y":216.4375,"h1Height":127.171875,"h1MarginTop":"5.13px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/corporate-massage-portland/","width":320,"height":8556,"h1Y":216.4375,"h1Height":164.109375,"h1MarginTop":"5.13px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/work-with-me/","width":1280,"height":6045,"h1Y":248.671875,"h1Height":120.25,"h1MarginTop":"9.216px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/work-with-me/","width":390,"height":7981,"h1Y":190.34375,"h1Height":125.71875,"h1MarginTop":"5.58144px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/work-with-me/","width":320,"height":9671,"h1Y":190.34375,"h1Height":125.71875,"h1MarginTop":"5.58144px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/contact/","width":1280,"height":2990,"h1Y":348.609375,"h1Height":190.90625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/contact/","width":390,"height":2993,"h1Y":300.953125,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/contact/","width":320,"height":3304,"h1Y":318,"h1Height":125.734375,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/privacy-policy/","width":1280,"height":1405,"h1Y":228.6875,"h1Height":38.390625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/privacy-policy/","width":390,"height":2198,"h1Y":186.875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/privacy-policy/","width":320,"height":2535,"h1Y":203.921875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/accessibility/","width":1280,"height":1680,"h1Y":228.6875,"h1Height":38.390625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/accessibility/","width":390,"height":2766,"h1Y":186.875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
LAYOUT {"path":"/accessibility/","width":320,"height":3267,"h1Y":203.921875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
Bundle C: 1187 assertions, 6 failures

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: css	29
  changed: option	astra-settings
  changed: option	wpcode_snippets
  changed: page	about
  changed: page	accessibility
  changed: page	chair-massage
  changed: page	contact
  changed: page	corporate-massage-portland
  changed: page	deep-tissue-massage
  changed: page	home
  changed: page	privacy-policy
  changed: page	services
  changed: page	thai-massage
  changed: page	therapeutic-massage
  changed: page	work-with-me
  changed: snippet	1232
  changed: snippet	1268
  changed: snippet	1329
  changed: snippet	1330
  rows now: iej_options	447
  rows now: iej_postmeta	645
  rows now: iej_posts	148
  rows now: iej_term_relationships	56

REHEARSAL FAILED: 1 check(s) failed (see -> FAIL above).
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-01 09:31:56 UTC_

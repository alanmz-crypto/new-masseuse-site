# Site op `peek-core-rehearse-20261109-1`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/install-peek-nav.php` |
| lane | ops (site-ops.yml) |
| commit | `b476125008590c89823f3851c6cbd979c5f56501` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42549121660 |
| started | 2026-11-09 02:09:09 UTC |

## rehearsal of link-ideas/install-peek-nav.php on a throwaway copy of staging39: FAIL (exit 1)

```text
Rehearsal of link-ideas/install-peek-nav.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 657081 bytes gzipped, files 238M
[2] starting the pinned practice images and importing ...
Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95
[2b] package preparation on the isolated localhost copy only
Success: Peek page:thai-massage applied; WordPress storage only. Before-snapshot: /backups/peek-template-page-thai-massage-before-20261109-021046-eb2efcbc.json
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

[preflight] wp eval-file link-ideas/install-peek-nav.php preflight
    Success: core ready; nothing written.
  -> PASS  preflight

[apply] wp eval-file link-ideas/install-peek-nav.php apply
    Success: Peek core applied; WordPress storage only. Before-snapshot: /backups/peek-template-core-before-20261109-021049-9164ec6e.json
  -> PASS  apply

[apply-again] wp eval-file link-ideas/install-peek-nav.php apply
    Success: core applied; nothing written.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file link-ideas/install-peek-nav.php rollback /backups/peek-template-core-before-20261109-021049-9164ec6e.json
    Success: Exact core rollback complete. Snapshot: /backups/peek-template-core-before-20261109-021049-9164ec6e.json
  -> PASS  rollback with peek-template-core-before-20261109-021049-9164ec6e.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file link-ideas/install-peek-nav.php apply
    Success: Peek core applied; WordPress storage only. Before-snapshot: /backups/peek-template-core-before-20261109-021051-804037c8.json
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
PASS template: links and controls in the text keep clear of the pinned header and strip when focused (Kiro, WCAG 2.4.11); the page itself has no scroll padding, so focusing the header does not scroll to the top  [{"link":257,"page":"auto"}]
PASS template: a quick tap on the index icon opens the names and they stay (Kiro: a tap did nothing)
PASS template: ...and a tap elsewhere closes them
PASS landscape, 7 sections (7 sections): section 1's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4"],"rows":1,"more":["+5 more below"]}]
PASS landscape, 7 sections (7 sections): section 4's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, 7 sections (7 sections): section 7's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4"],"rows":1,"more":["+5 more above"]}]
PASS landscape, 7 sections: a section without a glance line shows its own whole paragraphs; a line with "1.5", "U.S." and a closing quote stays whole (Kiro, sixth review)  [{"n":3,"whole":true,"tricky":true}]
PASS landscape, 7 sections (7 sections): the fanned names do not overlap (Kiro, sixth review), and sliding onto one picks it  [{"fo":{"gap":3,"h":24},"pick":2}]
PASS landscape, 7 sections: no JavaScript errors
PASS landscape, large text (5 sections): section 1's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, large text (5 sections): section 3's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, large text (5 sections): section 5's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, large text (5 sections): the fanned names do not overlap (Kiro, sixth review), and sliding onto one picks it  [{"fo":{"gap":3,"h":24},"pick":2}]
PASS landscape, large text: no JavaScript errors
PASS landscape, 7 sections and large text (7 sections): section 1's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, 7 sections and large text (7 sections): section 4's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, 7 sections and large text (7 sections): section 7's open box shows its name and "Tap to go there"  [{"ok":true,"fit":["fit1","fit2","fit3","fit4","fit5"],"rows":0,"more":[]}]
PASS landscape, 7 sections and large text: a section without a glance line shows its own whole paragraphs; a line with "1.5", "U.S." and a closing quote stays whole (Kiro, sixth review)  [{"n":3,"whole":true,"tricky":true}]
PASS landscape, 7 sections and large text (7 sections): the fanned names do not overlap (Kiro, sixth review), and sliding onto one picks it  [{"fo":{"gap":3,"h":24},"pick":2}]
PASS landscape, 7 sections and large text: no JavaScript errors
FAIL at the top of the page the preview keeps its full detail (measured from where the strip sticks)  [{"y":0,"fit":["fit1","fit2"],"shot":false}]
PASS keyboard: opening a closed box moves focus to the newly opened box
PASS while the page height animates (an FAQ opening) the rail clearance is not re-measured every frame  [class changes on <html>: 0]
PASS saved left-hand side: on load the text column starts after the rail, padded for the left (or not at all), never for the right  [{"left":true,"padded":true,"gap":6,"padRight":"0px","padLeft":"0px"}]
PASS using the rail before the hint appears means the hint never comes
PASS desktop (1280px): the text stays clear of the rail too  [{"clear":6}]
PASS Therapeutic Massage unchanged (no strip, no rail)
PASS Therapeutic Massage: no reading-progress bar and no scroll-to-top button either (sitewide)

chromium: 482 passed, 3 failed
FAIL phone/light index: the fanned names have the same form as the closed boxes in the stack (Ryan, 2026-11-08)  [{"row":"rgb(236, 235, 228) rgb(28, 37, 49) 10px 11.2176px 600 rgb(250, 249, 246) 50%","fan":["rgb(236, 235, 228) rgb(28, 37, 49) 10px 10.26px 600 rgb(250, 249, 246) 50%","rgb(236, 235, 228) rgb(28, 37, 49) 10px 10.26px 600 rgb(250, 249, 246) 50%","rgb(236, 235, 228) rgb(28, 37, 49) 10px 10.26px 600 rgb(250, 249, 246) 50%","rgb(236, 235, 228) rgb(28, 37, 49) 10px 10.26px 600 rgb(250, 249, 246) 50%"],"now":true}]
FAIL phone/dark index: the fanned names have the same form as the closed boxes in the stack (Ryan, 2026-11-08)  [{"row":"rgb(32, 43, 58) rgb(243, 241, 234) 10px 11.2176px 600 rgb(27, 36, 50) 50%","fan":["rgb(32, 43, 58) rgb(243, 241, 234) 10px 10.26px 600 rgb(27, 36, 50) 50%","rgb(32, 43, 58) rgb(243, 241, 234) 10px 10.26px 600 rgb(27, 36, 50) 50%","rgb(32, 43, 58) rgb(243, 241, 234) 10px 10.26px 600 rgb(27, 36, 50) 50%","rgb(32, 43, 58) rgb(243, 241, 234) 10px 10.26px 600 rgb(27, 36, 50) 50%"],"now":true}]
FAIL at the top of the page the preview keeps its full detail (measured from where the strip sticks)  [{"y":0,"fit":["fit1","fit2"],"shot":false}]

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: option	wpcode_snippets
  changed: snippet	1380

REHEARSAL FAILED: 1 check(s) failed (see -> FAIL above).
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-09 02:18:52 UTC_

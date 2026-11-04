# Site op `ui-section-guide-rehearse-20261104`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `section-guide/install-section-guide.php` |
| lane | ops (site-ops.yml) |
| commit | `c4025d71c1a42af27a364f4439e8a5b4303d68b3` on `feat/2026-11-04-section-guide` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41800602231 |
| started | 2026-11-04 08:35:04 UTC |

## rehearsal of section-guide/install-section-guide.php on a throwaway copy of staging38: FAIL (exit 1)

```text
Rehearsal of section-guide/install-section-guide.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 1081254 bytes gzipped, files 239M
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

[preflight] wp eval-file section-guide/install-section-guide.php preflight
    home=http://localhost:8090 section-guide=ready CSS bytes=66514
    Success: Preflight ready; nothing changed.
  -> PASS  preflight

[apply] wp eval-file section-guide/install-section-guide.php apply
    home=http://localhost:8090 section-guide=ready CSS bytes=66514
    Success: Section guide applied. Before-snapshot: /backups/section-guide-before-20261104-083649-6ed21d26.json
  -> PASS  apply

[apply-again] wp eval-file section-guide/install-section-guide.php apply
    home=http://localhost:8090 section-guide=applied CSS bytes=69937
    Success: Already applied; nothing changed.
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file section-guide/install-section-guide.php rollback /backups/section-guide-before-20261104-083649-6ed21d26.json
    home=http://localhost:8090 section-guide=applied CSS bytes=69937
    Success: Exact rollback complete; only this run's unchanged snippet removed. Snapshot: /backups/section-guide-before-20261104-083649-6ed21d26.json
  -> PASS  rollback with section-guide-before-20261104-083649-6ed21d26.json
  -> PASS  after rollback every fingerprint equals the baseline (A; WPCode's snippet-list cache option, which holds a modified time, set aside)

[re-apply] wp eval-file section-guide/install-section-guide.php apply
    home=http://localhost:8090 section-guide=ready CSS bytes=66514
    Success: Section guide applied. Before-snapshot: /backups/section-guide-before-20261104-083653-ba174afa.json
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
PASS thai-massage: selection closes panel
PASS thai-massage: temporary focus attribute cleaned up
PASS thai-massage: functional with {"reducedMotion":"reduce"}
PASS thai-massage: no guide animation
PASS thai-massage: functional with {"forcedColors":"active"}
PASS thai-massage: no guide animation
PASS thai-massage: print hides both controls
PASS thai-massage: open guide retains focus/visibility near footer
PASS thai-massage: hides when footer is visible
PASS thai-massage: original nav returns without duplicate guide
locator.click: Timeout 30000ms exceeded.
Call log:
  - waiting for locator('#ts-section-guide-trigger')
    - locator resolved to <button hidden="" type="button" popovertargetaction="toggle" id="ts-section-guide-trigger" popovertarget="ts-section-guide-popover">…</button>
  - attempting click action
    2 × waiting for element to be visible, enabled and stable
      - element is not visible
    - retrying click action
    - waiting 20ms
    2 × waiting for element to be visible, enabled and stable
      - element is not visible
    - retrying click action
      - waiting 100ms
    58 × waiting for element to be visible, enabled and stable
       - element is not visible
     - retrying click action
       - waiting 500ms

    at /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/section-guide/check-section-guide.mjs:149:190
Section guide (chromium): 70 browser assertions, 1 failures
INFO section guide browser: chromium 153.0.8010.12

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: css	29
  changed: option	wpcode_snippets
  changed: snippet	1349
  rows now: iej_options	447
  rows now: iej_postmeta	691
  rows now: iej_posts	166
  rows now: iej_term_relationships	62

REHEARSAL FAILED: 1 check(s) failed (see -> FAIL above).
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-04 08:37:43 UTC_

# Site op `search-contrast-apply-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `search-contrast/push-search-contrast.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `9e48056834d2d8920a94b51cfbd5a79384d16b00` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42974559394 |
| started | 2026-11-11 06:44:49 UTC |

## push-search-contrast.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   component: search-contrast   mode: apply
staging39 is www/staging39.thaisolitude.com/public_html
Success: search-contrast ready; css_sha256=2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae; nothing changed.
SEARCH-BACKUP /home/customer/private-backups/staging39-before-search-contrast-20261111-064500-29620.sql 23ac801734d1f1f5c4f4839f9616aaf972f24fba1cf3cb14b99c8bab3092c798 4548317
off-site: staging39-before-search-contrast-20261111-064500-29620.sql -> offsite-2026-11/staging39-before-search-contrast-20261111-064500-29620.sql.age (4549621 bytes, read back identical; plaintext sha256 23ac801734d1...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Verified exact backup on server, local copy and encrypted off-site readback: staging39-before-search-contrast-20261111-064500-29620.sql (4548317 bytes, sha256 23ac801734d1f1f5c4f4839f9616aaf972f24fba1cf3cb14b99c8bab3092c798)
staging39 is www/staging39.thaisolitude.com/public_html
Success: search-contrast ready; css_sha256=2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae; nothing changed.
SEARCH-SNAPSHOT /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json 9da4ca7fe4542276ab8113ddf24dc06694527c52038afbe7c722d93e866a1af6 141536
Success: Applied exact search-contrast CSS. Before-snapshot: /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json
off-site: search-contrast-before-20261111-064511-fcba9b49.json -> offsite-2026-11/search-contrast-before-20261111-064511-fcba9b49.json.age (141768 bytes, read back identical; plaintext sha256 9da4ca7fe454...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Verified exact backup on server, local copy and encrypted off-site readback: search-contrast-before-20261111-064511-fcba9b49.json (141536 bytes, sha256 9da4ca7fe4542276ab8113ddf24dc06694527c52038afbe7c722d93e866a1af6)
staging39 is www/staging39.thaisolitude.com/public_html
SEARCH-SNAPSHOT /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json 9da4ca7fe4542276ab8113ddf24dc06694527c52038afbe7c722d93e866a1af6 141536
Success: Exact search-contrast applied CSS verified read-only; css_sha256=b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b. Snapshot: /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json
SiteGround cache purged
  200 /?s=massage
Exact served applied Additional CSS verified; css_sha256=b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b.
  200 /
Exact served applied Additional CSS verified; css_sha256=b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b.
SEARCH-SNAPSHOT /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json 9da4ca7fe4542276ab8113ddf24dc06694527c52038afbe7c722d93e866a1af6 141536
Success: Exact search-contrast applied CSS verified read-only; css_sha256=b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b. Snapshot: /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-11 06:46 UTC
the site answered (200) for https://staging39.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  Thai evidence content matches its builder
PASS  Therapeutic evidence content matches its builder
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Review Bundle A exact fragments and new wording match their builder
PASS  Review Bundle B source values and approved wording match their builder
PASS  Review Bundle C frozen fragments and runtime sources match their builder
PASS  Review Bundle D frozen copy and research fragments match their builder
PASS  Review Bundle E image fragments, prepared WebP and runtime sources match their builder
PASS  verify-home.py https://staging39.thaisolitude.com
PASS  verify-client-pages.py https://staging39.thaisolitude.com
PASS  posture-check.py https://staging39.thaisolitude.com

ALL GATES PASSED
  -> PASS

[2] pages answer 200
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
  200 /wp-sitemap.xml
  200 /robots.txt
  -> PASS

[3] structured data vs the reference
items: candidate 27, reference 27 | blocks that failed to parse: candidate 0, reference 0
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261111-064609/reference
https://staging39.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261111-064609/candidate
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2572), candidate (1280, 2570))
  DIFF about-m.png: page height/width differs (reference (390, 3176), candidate (390, 3201))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1680), candidate (1280, 1705))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2766), candidate (390, 2873))
  DIFF contact-d.png: page height/width differs (reference (1280, 2990), candidate (1280, 2988))
  DIFF contact-m.png: page height/width differs (reference (390, 2993), candidate (390, 2992))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5674), candidate (1280, 5779))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 9446), candidate (390, 10117))
  DIFF home-d.png: page height/width differs (reference (1280, 4624), candidate (1280, 4622))
  DIFF home-m.png: page height/width differs (reference (390, 5929), candidate (390, 6095))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1405), candidate (1280, 1430))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2198), candidate (390, 2251))
  DIFF services-d.png: page height/width differs (reference (1280, 3906), candidate (1280, 3904))
  DIFF services-m.png: page height/width differs (reference (390, 6125), candidate (390, 6376))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5774), candidate (1280, 5773))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 9592), candidate (390, 10181))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 6426), candidate (1280, 6487))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 10367), candidate (390, 11159))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 6045), candidate (1280, 6103))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7981), candidate (390, 8044))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging39-20261111-064609)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging39
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging39 (staging39.thaisolitude.com)    export: staging39-before-search-contrast-20261111-064500-29620.sql    modified 2026-11-11 06:45:02
Unable to find image 'mariadb:10.6@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16' locally
docker.io/library/mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16: Pulling from library/mariadb
20c3783cc497: Pulling fs layer
82b15426938b: Pulling fs layer
b2a602564ec4: Pulling fs layer
bdc186c1268d: Pulling fs layer
5b0e31685a7d: Pulling fs layer
5d5076a693b9: Pulling fs layer
650d43fe7026: Pulling fs layer
4f693d0ae952: Pulling fs layer
bdc186c1268d: Waiting
5b0e31685a7d: Waiting
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
20c3783cc497: Download complete
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
650d43fe7026: Verifying Checksum
650d43fe7026: Download complete
4f693d0ae952: Verifying Checksum
4f693d0ae952: Download complete
5d5076a693b9: Verifying Checksum
5d5076a693b9: Download complete
20c3783cc497: Pull complete
82b15426938b: Pull complete
b2a602564ec4: Pull complete
bdc186c1268d: Pull complete
5b0e31685a7d: Pull complete
5d5076a693b9: Pull complete
650d43fe7026: Pull complete
4f693d0ae952: Pull complete
Digest: sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
Status: Downloaded newer image for mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
import exit code: 0
fingerprints: restored copy 104, real site 104
compared: css 1, option 12, page 19, rows 21, seo 21, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< css	29
> css	29
```

**RESULT: PASS** - push-search-contrast.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-11 06:54:29 UTC_

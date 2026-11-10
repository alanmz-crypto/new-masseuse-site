# Site op `peek-neighbours-apply-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-neighbours.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `e2fb64c292e9fa84e44b00c7e80531646573c87d` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42834929062 |
| started | 2026-11-10 14:31:36 UTC |

## push-peek-neighbours.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   component: core-neighbours   mode: apply
[1] Uploading the reviewed package outside every web root ...
[2] Fresh export and exact frozen-v7 preflight:
staging39 is www/staging39.thaisolitude.com/public_html
Success: core-neighbours ready; nothing written.
fresh database export: private-backups/staging39-before-peek-neighbours-20261110-143148-33164.sql (4531838 bytes)
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-peek-neighbours-20261110-143148-33164.sql -> offsite-2026-11/staging39-before-peek-neighbours-20261110-143148-33164.sql.age (4533142 bytes, read back identical; plaintext sha256 9447f244b57a...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[3] Apply the neighbours core:
staging39 is www/staging39.thaisolitude.com/public_html
Success: core-neighbours ready; nothing written.
Success: Peek core-neighbours applied; WordPress storage only. Before-snapshot: /home/customer/private-backups/peek-template-core-neighbours-before-20261110-143200-81725f33.json
[4] Verify any actual apply snapshot off-site before parsing or rendering:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: peek-template-core-neighbours-before-20261110-143200-81725f33.json -> offsite-2026-11/peek-template-core-neighbours-before-20261110-143200-81725f33.json.age (234623 bytes, read back identical; plaintext sha256 0bc2fbb59a14...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
[5] Purge, render exact bytes and verify complete core/page state:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Exact core-neighbours applied core and all four page-data records verified read-only; core_state_sha256=c9046dbb963ed0d18297b4b1a4cfda0b94db4969767ee2ddf6f75498c66ca289 pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-neighbours-before-20261110-143200-81725f33.json
SiteGround cache purged
  200 /
  exact served core/style and data scope: home
  200 /about/
  exact served core/style and data scope: about
  200 /accessibility/
  exact served core/style and data scope: accessibility
  200 /chair-massage/
  exact served core/style and data scope: chair-massage
  200 /contact/
  exact served core/style and data scope: contact
  200 /corporate-massage-portland/
  exact served core/style and data scope: corporate-massage-portland
  200 /deep-tissue-massage/
  exact served core/style and data scope: deep-tissue-massage
  200 /in-page-link-ideas/
  exact served core/style and data scope: in-page-link-ideas
  200 /index-nav-ideas/
  exact served core/style and data scope: index-nav-ideas
  200 /personal-automation/
  exact served core/style and data scope: personal-automation
  200 /personal-automation/privacy/
  exact served core/style and data scope: privacy
  200 /personal-automation/terms/
  exact served core/style and data scope: terms
  200 /privacy-policy/
  exact served core/style and data scope: privacy-policy
  200 /progress-nav-ideas/
  exact served core/style and data scope: progress-nav-ideas
  200 /rail-nav-ideas/
  exact served core/style and data scope: rail-nav-ideas
  200 /services/
  exact served core/style and data scope: services
  200 /thai-massage/
  exact served core/style and data scope: thai-massage
  200 /therapeutic-massage/
  exact served core/style and data scope: therapeutic-massage
  200 /work-with-me/
  exact served core/style and data scope: work-with-me
Success: Exact core-neighbours applied core and all four page-data records verified read-only; core_state_sha256=c9046dbb963ed0d18297b4b1a4cfda0b94db4969767ee2ddf6f75498c66ca289 pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-neighbours-before-20261110-143200-81725f33.json
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-10 14:39 UTC
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
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261110-143900/reference
https://staging39.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging39-20261110-143900/candidate
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
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/staging39-20261110-143900)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging39
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging39 (staging39.thaisolitude.com)    export: staging39-before-peek-neighbours-20261110-143148-33164.sql    modified 2026-11-10 14:31:49
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
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
< snippet	1380
> snippet	1380
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-peek-neighbours.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-10 14:44:04 UTC_

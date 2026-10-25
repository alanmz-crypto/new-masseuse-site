# Site op `live-availability-update-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `availability/push-availability-update.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `0f51523dd3d0ed0efd64e713712268c04566c3f6` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40162802261 |
| started | 2026-10-25 04:41:42 UTC |

## push-availability-update.sh --apply(S_TARGET=live): PASS

```text
Target site: live   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-availability-update (outside every web root) ...
[2] Backup on the server + preflight:
live is www/thaisolitude.com/public_html
fresh database export: private-backups/live-before-availability-update-20261025-044149.sql (4295497 bytes)
Success: Preflight passed; nothing changed. host=http://thaisolitude.com state=ready replacements ready=10 applied=0 changed=0; strip snippet=ready
[3] Copying the backup to this computer and verifying it (stops here if it cannot be verified):
Copying the 7 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 7 file(s), identical sha256 on the server and in the copy.
off-site: live-before-availability-update-20261025-044149.sql -> offsite-2026-10/live-before-availability-update-20261025-044149.sql.age (4296737 bytes, read back identical; plaintext sha256 0b793eb158be...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 6 already there.
[4] Applying:
live is www/thaisolitude.com/public_html
Success: Applied 10 replacements on 5 pages and updated the strip snippet #1268. Before-snapshot: /home/<ssh-user>/private-backups/availability-update-before-20261025-044200.json
SiteGround cache purged
[4b] Do the public pages show the change?
live is www/thaisolitude.com/public_html
  200 /
  200 /about/
  200 /services/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /corporate-massage-portland/
public check: the availability strip on the 9 pages (not on Work With Me or the corporate page), the corrected FAQ answers on the three modality pages, the Work With Me and corporate sentences, and the parts the plan says to keep (For Practices, the repeated résumé links, the November 1 employment dates)
[5] Copying the new before-snapshot to this computer:
Copying the 8 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 8 file(s), identical sha256 on the server and in the copy.
off-site: availability-update-before-20261025-044200.json -> offsite-2026-10/availability-update-before-20261025-044200.json.age (247359 bytes, read back identical; plaintext sha256 3c67221d0421...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 7 already there.
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging36 (https://staging36.thaisolitude.com)   2026-10-25 04:42 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging36.thaisolitude.com

[1] code gates and rendered-page verifiers
        PASS no hiring band on Home (tried and dropped at Ryan's request, 2026-10-16)
        PASS Home 'Learn About' paragraph carries the Chicago-to-Portland story and her language, once
        PASS the story matches About's facts (Chicago 2024; busy Chicago practice with hotel guests and business travelers; bringing it to Portland)
        FAIL no move date, countdown or opening date added on Home
        PASS page title  [Thai Solitude | Thai & Therapeutic Massage, Mt. Tabor, Portland]
        PASS og:title follows the title
        PASS meta description is the new wording  [Personalized Thai, therapeutic and deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT.]
        PASS og:description and twitter:description follow it
        PASS meta description fits one search snippet (<= 160 characters)  [149]
        PASS the 'About Thai Solitude' link has a navy colour rule (it was white on cream, 1.1:1)
        PASS every menu link to the hiring page now says 'For Practices' (header, mobile menu, footer menu)  [['For Practices', 'For Practices', 'For Practices']]
        PASS menu link target unchanged (/work-with-me/)
        PASS 'Work With Me' no longer appears as a menu label
        PASS footer: Portland, OR
        PASS footer: visible email address, linked
        PASS footer: 'Résumé & experience' links to /work-with-me/
PASS  verify-client-pages.py https://thaisolitude.com
PASS  posture-check.py https://thaisolitude.com

1 GATE(S) FAILED
  -> FAIL

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
items: candidate 23, reference 23 | blocks that failed to parse: candidate 0, reference 0
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /services/, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261025-044253/candidate
https://staging36.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261025-044253/reference
page-views compared: 20 | pixel-identical: 0 | different: 20 | present in only one set: 0
  DIFF about-d.png: page height/width differs (reference (1280, 2116), candidate (1280, 2193))
  DIFF about-m.png: page height/width differs (reference (390, 2639), candidate (390, 2800))
  DIFF accessibility-d.png: page height/width differs (reference (1280, 1549), candidate (1280, 1627))
  DIFF accessibility-m.png: page height/width differs (reference (390, 2473), candidate (390, 2635))
  DIFF contact-d.png: page height/width differs (reference (1280, 2635), candidate (1280, 2713))
  DIFF contact-m.png: page height/width differs (reference (390, 2518), candidate (390, 2679))
  DIFF deep-tissue-massage-d.png: page height/width differs (reference (1280, 5069), candidate (1280, 5147))
  DIFF deep-tissue-massage-m.png: page height/width differs (reference (390, 8620), candidate (390, 8781))
  DIFF home-d.png: page height/width differs (reference (1280, 4163), candidate (1280, 4570))
  DIFF home-m.png: page height/width differs (reference (390, 5254), candidate (390, 5945))
  DIFF privacy-policy-d.png: page height/width differs (reference (1280, 1327), candidate (1280, 1405))
  DIFF privacy-policy-m.png: page height/width differs (reference (390, 2067), candidate (390, 2228))
  DIFF services-d.png: page height/width differs (reference (1280, 2949), candidate (1280, 3271))
  DIFF services-m.png: page height/width differs (reference (390, 4463), candidate (390, 5164))
  DIFF thai-massage-d.png: page height/width differs (reference (1280, 5250), candidate (1280, 5328))
  DIFF thai-massage-m.png: page height/width differs (reference (390, 8940), candidate (390, 9102))
  DIFF therapeutic-massage-d.png: page height/width differs (reference (1280, 5624), candidate (1280, 5702))
  DIFF therapeutic-massage-m.png: page height/width differs (reference (390, 9424), candidate (390, 9585))
  DIFF work-with-me-d.png: page height/width differs (reference (1280, 5990), candidate (1280, 6045))
  DIFF work-with-me-m.png: page height/width differs (reference (390, 7801), candidate (390, 7981))
  -> FAIL
  (screenshots and red-highlighted diffs kept in /home/runner/.cache/ts-post-change/live-20261025-044253)

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

2 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-availability-update-20261025-044149.sql    modified 2026-10-25 04:41:49
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
fingerprints: restored copy 85, real site 85
compared: css 1, option 12, page 14, rows 21, seo 17, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_postmeta
< rows	iej_posts
> rows	iej_postmeta
> rows	iej_posts
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< page	corporate-massage-portland
< page	deep-tissue-massage
> page	corporate-massage-portland
> page	deep-tissue-massage
< page	thai-massage
< page	therapeutic-massage
< page	work-with-me
> page	thai-massage
> page	therapeutic-massage
> page	work-with-me
< snippet	1268
> snippet	1268
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-availability-update.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-25 04:45:00 UTC_

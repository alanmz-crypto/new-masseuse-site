# Site op `portland-meta-apply-38`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `portland-meta/push-portland-meta.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `21ff1a496992bdccd80956bff6cc6b9f69aa408f` on `feat/2026-10-29-portland-meta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40917008504 |
| started | 2026-10-29 17:00:01 UTC |

## push-portland-meta.sh --apply(S_TARGET=staging38): PASS

```text
Target site: staging38   mode: apply
[1] Connecting and uploading the package to ~/wwm-package-portland-meta (outside every web root) ...
[2] Backup on the server + preflight:
staging38 is www/staging38.thaisolitude.com/public_html
fresh database export: private-backups/staging38-before-portland-meta-20261029-170009.sql (4537043 bytes)
Success: Preflight passed; nothing changed. host=http://staging38.thaisolitude.com state=ready pages={"home":"ready","thai-massage":"ready","therapeutic-massage":"ready","deep-tissue-massage":"ready"}
[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging38-before-portland-meta-20261029-170009.sql -> offsite-2026-10/staging38-before-portland-meta-20261029-170009.sql.age (4538347 bytes, read back identical; plaintext sha256 819ab14243c6...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 0 already there.
[4] Applying:
staging38 is www/staging38.thaisolitude.com/public_html
Success: SEO titles and meta descriptions are set on all four pages (Home, Thai, Therapeutic, Deep Tissue). Before-snapshot: /home/<ssh-user>/private-backups/portland-meta-before-20261029-170021.json
SiteGround cache purged
[4b] Do the public pages show the changes?
staging38 is www/staging38.thaisolitude.com/public_html
public check: https://staging38.thaisolitude.com/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   / title tag is the new title (1)
  ok   / meta description is the new description (1)
  ok   / old title gone everywhere (0)
  ok   / old description gone everywhere (meta, og, twitter) (0)
  ok   / page content untouched (the announcement strip is still there) (1)
  ok   / licence number in the footer (1)
public check: https://staging38.thaisolitude.com/thai-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /thai-massage/ title tag is the new title (1)
  ok   /thai-massage/ meta description is the new description (1)
  ok   /thai-massage/ old title gone everywhere (0)
  ok   /thai-massage/ old description gone everywhere (meta, og, twitter) (0)
  ok   /thai-massage/ page content untouched (the announcement strip is still there) (1)
  ok   /thai-massage/ licence number in the footer (1)
public check: https://staging38.thaisolitude.com/therapeutic-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /therapeutic-massage/ title tag is the new title (1)
  ok   /therapeutic-massage/ meta description is the new description (1)
  ok   /therapeutic-massage/ old description gone everywhere (meta, og, twitter) (0)
  ok   /therapeutic-massage/ page content untouched (the announcement strip is still there) (1)
  ok   /therapeutic-massage/ licence number in the footer (1)
public check: https://staging38.thaisolitude.com/deep-tissue-massage/ -> HTTP 200 (want 200; 202 would be SiteGround's bot challenge = INVALID, retry later)
  ok   /deep-tissue-massage/ title tag is the new title (1)
  ok   /deep-tissue-massage/ meta description is the new description (1)
  ok   /deep-tissue-massage/ old description gone everywhere (meta, og, twitter) (0)
  ok   /deep-tissue-massage/ page content untouched (the announcement strip is still there) (1)
  ok   /deep-tissue-massage/ licence number in the footer (1)
public check: all four pages show the new title and description in the head; their content is unchanged
[5] Copying the new before-snapshot off the server:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: portland-meta-before-20261029-170021.json -> offsite-2026-10/portland-meta-before-20261029-170021.json.age (19184 bytes, read back identical; plaintext sha256 fded9dec6fee...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-10, 1 already there.
```

## post-change-test.sh staging38 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: staging38 (https://staging38.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-10-29 17:01 UTC
the site answered (200) for https://staging38.thaisolitude.com
the site answered (200) for https://thaisolitude.com

[1] code gates and rendered-page verifiers
        PASS neck-and-shoulders FAQ present once (question and answer)
        PASS old headache FAQ gone from the rendered page
        PASS Therapeutic Massage: no restricted wording outside the research section
        PASS Therapeutic Massage research section: no claim verbs, no we/our/us/studio
        PASS Therapeutic Massage source titles: no we/our/us/studio
        PASS Therapeutic Massage: FAQ question 'What Is the Difference Between Therapeutic and Relaxation Massage?' appears once
        PASS Therapeutic Massage: FAQ question 'How Is a Therapeutic Session Planned?' appears once
        PASS Therapeutic Massage: FAQ question 'Will I Feel the Results Right Away?' appears once
        PASS Therapeutic Massage: FAQ question 'How Often Should I Come In for Therapeutic Massage?' appears once
        PASS Therapeutic Massage: FAQ question 'Is Therapeutic Massage Appropriate If I Have No Specific Complaint?' appears once
        PASS Therapeutic Massage: FAQ question 'Can I Ask You to Focus on My Neck and Shoulders?' appears once
        PASS Therapeutic Massage: FAQ question 'How Is Therapeutic Massage Different From Deep Tissue Massage?' appears once
        PASS Therapeutic Massage: FAQ question 'Can I Change the Pressure During a Session?' appears once
        PASS Therapeutic Massage: FAQ question 'Where Do Sessions Take Place?' appears once
        PASS Therapeutic Massage: FAQ question 'How Do I Book a Session?' appears once
        PASS Therapeutic Massage: 'Yes. You can ask for lighter pressure, a different focus' once
        PASS Therapeutic Massage: 'Deep tissue massage uses focused strokes and sustained p' once
PASS  posture-check.py https://staging38.thaisolitude.com

2 GATE(S) FAILED
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
INFO dateModified (WordPress's last-edited time, set aside) differs on: /, /thai-massage/
IDENTICAL: both sites publish the same structured data on all 4 pages.
  -> PASS

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261029-170119/reference
https://staging38.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/staging38-20261029-170119/candidate
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  -> PASS

1 CHECK(S) FAILED for staging38
```

## prove-restore.sh staging38 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging38 (staging38.thaisolitude.com)    export: staging38-before-portland-meta-20261029-170009.sql    modified 2026-10-29 17:00:09
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
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
5b0e31685a7d: Waiting
bdc186c1268d: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
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
fingerprints: restored copy 85, real site 85
compared: css 1, option 12, page 14, rows 21, seo 17, snippet 20
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< seo	296:surerank_settings_general
> seo	296:surerank_settings_general
< seo	1091:surerank_settings_general
< seo	1107:surerank_settings_general
< seo	1160:surerank_settings_general
> seo	1091:surerank_settings_general
> seo	1107:surerank_settings_general
> seo	1160:surerank_settings_general
```

**RESULT: PASS** - push-portland-meta.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-10-29 17:03:35 UTC_

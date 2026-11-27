# Site op `live-peek-navigation-v2-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `peek-navigation-v2-production/push-navigation-v2.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `d3148b235bcb93d1e39e3aad4672894f612b4a32` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/45978197786 |
| started | 2026-11-27 09:59:13 UTC |

## push-navigation-v2.sh --apply(S_TARGET=live): PASS

```text

Running kernel seems to be up-to-date.

No services need to be restarted.

No containers need to be restarted.

No user sessions are running outdated binaries.

No VM guests are running outdated hypervisor (qemu) binaries on this host.

Running kernel seems to be up-to-date.

No services need to be restarted.

No containers need to be restarted.

No user sessions are running outdated binaries.

No VM guests are running outdated hypervisor (qemu) binaries on this host.
TS_PLAYWRIGHT_ROOT=/home/runner/.cache/ts-playwright/package.json
peek-v2.zip: OK
install-v2.php: OK
remote-phase.sh: OK
Success: Preflight passed; nothing changed. host=http://thaisolitude.com snippet=#1343 state=ready navigation.js=absent navigation.css=absent
Success: Exported to '/home/customer/private-backups/live-before-peek-v2-45978197786-1.sql'.
off-site: live-before-peek-v2-45978197786-1.sql -> offsite-2026-11/live-before-peek-v2-45978197786-1.sql.age (4193455 bytes, read back identical; plaintext sha256 17cc29675c53...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Success: Applied v2 to snippet #1343. Rollback snapshot: /home/customer/private-backups/peek-v2-live-45978197786-1/snapshots/peek-v2-before-20261127-100020-dea2ae19-da11-478e-9974-47b226c921ca.json
Recovery snapshot /home/customer/private-backups/peek-v2-live-45978197786-1/snapshots/peek-v2-before-20261127-100020-dea2ae19-da11-478e-9974-47b226c921ca.json SHA-256 7cc05f13fc9950bac9a9d19eb63a006633129c1740d24539917e69d30be64e0f
off-site: peek-v2-before-20261127-100020-dea2ae19-da11-478e-9974-47b226c921ca.json -> offsite-2026-11/peek-v2-before-20261127-100020-dea2ae19-da11-478e-9974-47b226c921ca.json.age (866 bytes, read back identical; plaintext sha256 7cc05f13fc99...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
Warning: Unable to Purge File Cache. Please make sure it is enabled.
Warning: Unable to Purge Dynamic Cache. Please make sure it is enabled.
Success: Speed Optimizer by SiteGround assets folder purged successfully.
Success: Dynamic Cache Successfully Purged.
Success: The cache was flushed.
Success: Preflight passed; nothing changed. host=http://thaisolitude.com snippet=#1343 state=applied navigation.js=exact navigation.css=exact
PASS production robots.txt
PASS apply /
PASS apply /about/
PASS apply /services/
PASS apply /contact/
PASS apply /deep-tissue-massage/
PASS apply /therapeutic-massage/
PASS apply /thai-massage/
PASS apply /work-with-me/
PASS apply /privacy-policy/
PASS apply /accessibility/
PASS apply /corporate-massage-portland/
PASS apply /chair-massage/
PASS apply /winding-willow/
PASS navigation.js sha256
PASS navigation.css sha256
PASS chromium home Enter + Escape + touch
PASS chromium thai-massage Space + Escape + touch
chromium untouched y_before_load= 5172 { y: 5172, top: 132.828125, want: 133 }
PASS chromium fragment untouched
chromium scrolled-before-load y_before_load= 8172 { y: 8172, top: -2867.171875, want: 133 }
PASS chromium fragment scrolled before load
PASS asset transfers / [ [ 'navigation.css', 6621, 6321 ], [ 'navigation.js', 23186, 22886 ] ]
PASS asset transfers /services/ [ [ 'navigation.css', 0, 6321 ], [ 'navigation.js', 0, 22886 ] ]
PASS asset transfers /thai-massage/ [ [ 'navigation.css', 0, 6321 ], [ 'navigation.js', 0, 22886 ] ]
PASS firefox home Enter + Escape + touch
PASS firefox thai-massage Space + Escape + touch
firefox untouched y_before_load= 4707.7333984375 { y: 5182.7333984375, top: 133.0833282470703, want: 133 }
PASS firefox fragment untouched
firefox scrolled-before-load y_before_load= 5392.7333984375 { y: 5392.7333984375, top: -76.91667175292969, want: 133 }
PASS firefox fragment scrolled before load
```

## post-change-test.sh live (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Target: live (https://thaisolitude.com)   reference: staging40 (https://staging40.thaisolitude.com)   2026-11-27 10:00 UTC
the site answered (200) for https://thaisolitude.com
the site answered (200) for https://staging40.thaisolitude.com

[1] code gates and rendered-page verifiers
PASS  Deep Tissue evidence content matches its builder
PASS  Accessibility page text matches its builder
PASS  Home corporate card content and CSS match their builder
PASS  Sandwich update content matches its builders
PASS  Corporate massage content matches its builder
PASS  Availability update content matches its builder
PASS  Privacy policy text matches its builder
PASS  Booking date content matches its builder
PASS  Chair Massage content matches its builder (and passes its wording gate)
PASS  Winding Willow page matches its builder (frozen staging39 capture + 4 approved changes)
PASS  Review Bundle A exact fragments and new wording match their builder
PASS  Review Bundle B source values and approved wording match their builder
PASS  Review Bundle C frozen fragments and runtime sources match their builder
PASS  Review Bundle D frozen copy and research fragments match their builder
PASS  Review Bundle E image fragments, prepared WebP and runtime sources match their builder
PASS  verify-home.py https://thaisolitude.com
PASS  verify-client-pages.py https://thaisolitude.com
PASS  posture-check.py https://thaisolitude.com

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
INFO dateModified (WordPress's last-edited time, set aside) differs on: /thai-massage/
DIFFERENT:
  /: candidate has 6 items, reference 6
  /contact/: candidate has 7 items, reference 7
  /services/: candidate has 7 items, reference 7
  /thai-massage/: candidate has 7 items, reference 7
  -> FAIL

[4] pixel comparison vs the reference (20 page-views, zero tolerance)
https://thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261127-100055/candidate
https://staging40.thaisolitude.com: 20 screenshots, 0 problems -> /home/runner/.cache/ts-post-change/live-20261127-100055/reference
page-views compared: 20 | pixel-identical: 20 | different: 0 | present in only one set: 0
  -> PASS

[5] PHP errors in the server log, last 120 minutes
  no PHP errors, warnings or deprecations logged from the site's code
  NOTE 11 diagnostic(s) came from /usr/local/wp-cli (SiteGround's own CLI tool, e.g. the Site Tools login helper), not from the site; not counted
  -> PASS

1 CHECK(S) FAILED for live
```

## prove-restore.sh live (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: live (thaisolitude.com)    export: live-before-peek-v2-45978197786-1.sql    modified 2026-11-27 10:00:15
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
5b0e31685a7d: Waiting
5d5076a693b9: Waiting
650d43fe7026: Waiting
4f693d0ae952: Waiting
bdc186c1268d: Waiting
82b15426938b: Verifying Checksum
82b15426938b: Download complete
b2a602564ec4: Verifying Checksum
b2a602564ec4: Download complete
bdc186c1268d: Verifying Checksum
bdc186c1268d: Download complete
5b0e31685a7d: Verifying Checksum
5b0e31685a7d: Download complete
650d43fe7026: Download complete
4f693d0ae952: Verifying Checksum
4f693d0ae952: Download complete
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
20c3783cc497: Pull complete
82b15426938b: Pull complete
5d5076a693b9: Verifying Checksum
5d5076a693b9: Download complete
b2a602564ec4: Pull complete
bdc186c1268d: Pull complete
5b0e31685a7d: Pull complete
5d5076a693b9: Pull complete
650d43fe7026: Pull complete
4f693d0ae952: Pull complete
Digest: sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
Status: Downloaded newer image for mariadb@sha256:40153feb479c0da88b5cfe3f50f44c91f7baf05a1b7bcc5beb7eb37a890a8f16
import exit code: 0
fingerprints: restored copy 102, real site 102
compared: css 1, option 12, page 16, rows 21, seo 22, snippet 30
DIFFERENCES (restored export '<' vs the real site now '>'). Expected only for what changed after the export was taken:
< rows	iej_sgs_log_events
< rows	iej_sgs_log_visitors
> rows	iej_sgs_log_events
> rows	iej_sgs_log_visitors
< snippet	1343
> snippet	1343
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-navigation-v2.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-27 10:09:54 UTC_

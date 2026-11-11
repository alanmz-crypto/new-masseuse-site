# Site op `peek-index-clearance-s39-apply-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-index-clearance.sh` `--apply` |
| lane | ops (site-ops.yml) |
| commit | `51cc60ddef6d61635585b3021b8335c0030ee3a5` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42934834303 |
| started | 2026-11-11 00:53:30 UTC |

## push-peek-index-clearance.sh --apply(S_TARGET=staging39): PASS

```text
Target site: staging39   component: core-index-clearance   mode: apply
[1] Uploading the reviewed package outside every web root ...
[2] Fresh export and exact frozen-v8 preflight:
staging39 is www/staging39.thaisolitude.com/public_html
Success: core-index-clearance ready; nothing written.
fresh database export: private-backups/staging39-before-peek-index-clearance-20261111-005342-74246.sql (4535417 bytes)
Copying the 1 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 1 file(s), identical sha256 on the server and in the copy.
off-site: staging39-before-peek-index-clearance-20261111-005342-74246.sql -> offsite-2026-11/staging39-before-peek-index-clearance-20261111-005342-74246.sql.age (4536721 bytes, read back identical; plaintext sha256 4f860c3480e2...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 0 already there.
[3] Apply the index-clearance core:
staging39 is www/staging39.thaisolitude.com/public_html
Success: core-index-clearance ready; nothing written.
Success: Peek core-index-clearance applied; WordPress storage only. Before-snapshot: /home/customer/private-backups/peek-template-core-index-clearance-before-20261111-005357-31ca0040.json
[4] Verify any actual apply snapshot off-site before parsing or rendering:
Copying the 2 export(s)/manifest(s) changed in the last 240 minutes to /home/runner/work/_temp/server-backups ...
VERIFIED: 2 file(s), identical sha256 on the server and in the copy.
off-site: peek-template-core-index-clearance-before-20261111-005357-31ca0040.json -> offsite-2026-11/peek-template-core-index-clearance-before-20261111-005357-31ca0040.json.age (241802 bytes, read back identical; plaintext sha256 631b7cf6cffa...)
OFF-SITE VERIFIED: 1 file(s) stored encrypted in draft release offsite-2026-11, 1 already there.
[5] Purge, render exact bytes and verify complete core/page state:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Exact core-index-clearance applied core and all four page-data records verified read-only; core_state_sha256=de8e43f75a8a42c537608c8590e160ba6f061fc27d32c4c83631ae0451b3c7e6 pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-index-clearance-before-20261111-005357-31ca0040.json
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
Success: Exact core-index-clearance applied core and all four page-data records verified read-only; core_state_sha256=de8e43f75a8a42c537608c8590e160ba6f061fc27d32c4c83631ae0451b3c7e6 pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-index-clearance-before-20261111-005357-31ca0040.json
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 2 (information, see below)

```text
Target: staging39 (https://staging39.thaisolitude.com)   reference: live (https://thaisolitude.com)   2026-11-11 00:55 UTC
BLOCKED: SiteGround's bot challenge (HTTP 202) answered for https://staging39.thaisolitude.com instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site.
STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop.
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 3 (information, see below)

```text
Target: staging39 (staging39.thaisolitude.com)    export: staging39-before-peek-index-clearance-20261111-005342-74246.sql    modified 2026-11-11 00:53:42
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
20c3783cc497: Verifying Checksum
20c3783cc497: Download complete
bdc186c1268d: Verifying Checksum
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
< snippet	1380
> snippet	1380
< option	wpcode_snippets
> option	wpcode_snippets
```

**RESULT: PASS** - push-peek-index-clearance.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-11 00:55:26 UTC_

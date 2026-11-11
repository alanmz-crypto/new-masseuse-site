# Peek visitor-navigation release readiness

**Decision: prepare the release; do not launch yet.** Ryan asked Codex to decide
when it is a good time to push live and continue. This assessment does not start
a live write or authorize a main merge. Work remains on
`claude/wizardly-bell-xu0rta`; the current visitor navigation is on staging39.

**November 11: four further changes requested before live.** Removing the
automatic rail-side scrolling label, outlining the index, adding its open-state
X and dismissing the header menu on outside taps are now deployed on staging39.
See the [quiet-navigation record](2026-11-11-peek-quiet-navigation.md): source
48/0, fresh-copy browsers 24,977/0 and exact recovery, then the actual backed-up
apply with unchanged summaries and 19 exact rendered routes. Public browsers
passed **19,628/0 across all 20 suites**; raw evidence and all 144 quiet/index
captures were audited without a new blocker. Final actual-snapshot verification
passed at **05:35:29 UTC** (run **42966700614**), confirming exact core/four-data
state before and after all 19 rendered routes. The four-change staging phase
is complete. Ryan accepted the new
controls on iPhone Safari on November 11: “It's correct.” They explicitly
deferred fluidity refinements until after the live push. This acceptance and
the earlier ones remain closed; do not ask for a generic retest or add fluidity
work to the pre-live scope. The check did not authorize a live operation.
A separate dark Search contrast issue is now corrected and verified on staging39;
it is recorded separately from the four navigation changes.

**November 11 release source refreshed; complete release cycle still unproved.** The
local first-install candidate now freezes the accepted quiet-navigation core,
including immediate summary neighbours, index clearance and all four controls.
The provisional source now also includes the narrow title-strip observer timing
correction described in the [resize record](2026-11-11-peek-title-resize.md).
Its staging and complete release verification remain pending; the accepted
quiet-navigation bytes are frozen as shared v10 for exact recovery.
Its four approved summary payloads are unchanged. The same reviewed 536-byte
Search contrast fix is frozen as a separate required CSS payload. It is deployed
and verified on staging39; live is unchanged. See the
[completed Search record](2026-11-11-search-contrast.md): source **51/0**, fresh
copy **300/0** with exact recovery, public **300/0**, all 24 public captures
identical to the reviewed copy, and final actual-snapshot verification passed
at **07:00:26 UTC** (Actions **42976293114**). Fresh SQL and the actual snapshot
were verified encrypted off-site in the required order. The single guarded
CSS transition removes the exact legacy progress block and appends one LF plus
the full Search payload, without trimming. Rollback restores the whole exact
original CSS record.

| Current candidate | Bytes | SHA256 |
|---|---:|---|
| Core PHP file | 111,148 | `708b91f4e52025b6813245a38e7c048fff09dd6ae90f644e0b2b5d1f415f7b19` |
| Core stored code | 111,142 | `f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566` |
| Search CSS payload | 536 | `3225b279ae3d20adf696ac9ad6a16dc2be7c7c15c8fbe64ba59e5a74dc14e9e0` |
| Desired combined Additional CSS | 69,380 | `b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b` |

After the Spectra correction below, current installer SHA256 is
`a972ad0c135c7b2da42fe2d1c27efee5396988676393b70bc44fd050e04e4cdf`;
manifest SHA256 is
`5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5`;
source-binding helper SHA256 is
`5b75b761b0146163d54c26e0b7a99265850d7168cbd48438061e2cb45297c02b`.
Earlier attempts used installer `64e95f63…` and manifest `296318ac…`;
copy3 used the corrected installer and manifest `81eb8f26…` with the accepted
quiet-navigation runtime. That manifest is superseded only in the provisional
source candidate; no actual snapshot or historical result is rebased.
Independent source review found no defect in the refreshed CSS transition and
rollback. Before the cache correction, pinned-PHP tests passed **1,694 checks with 138 injected write-boundary
failures**; the release evidence suite passed **162 offline controls**, including
missing/changed Search payload refusals. PHP/Node syntax, exact current/frozen
payload bindings and diff checks passed. Complete repository source Gates then
passed **51/0** in Actions **42971422219** and, after the staging Search wrapper's
canonical-path correction, **42973687235** at **06:43:38 UTC**. These source/model
results do not prove the first WordPress installation or release browser cycle.
The candidate still
accepts only exact `http://localhost:8090`; no live profile or push wrapper is
enabled. The historical evidence below does not validate this refreshed bundle.

**November 10 summary-neighbour experiment accepted on Safari.** Ryan requested
only the previous and next summary controls around each open box;
see the [active staging record](2026-11-10-peek-summary-neighbours.md). At that
stage the provisional release payload was still frozen at the preceding
input-focus core; the November 11 source refresh above supersedes it. The
neighbour core was deployed on staging39: copy **19,061/0**,
public **15,646/0 across 16 suites**, and exact actual-snapshot verification
passed. Complete leaf evidence was audited despite combined-console truncation;
see the staging record. That phase
identified a separate short-viewport index/announcement overlap, addressed by
the November 11 staging update below. Both user acceptance checks are complete;
the refreshed release still needs its own first-install/recovery rehearsal.
Do not dispatch the previously blocked copy6 merely on a later permission reply
without checking that the release package is still intended.

**November 11 index-clearance update is now on staging39.** The
[index-clearance record](2026-11-10-peek-index-clearance.md) supersedes the
clearance status above: source Gates 45/0, fresh-copy browsers 23,249/0 with
exact post-browser recovery, then in-place deployment and exact actual-snapshot
verification across all 19 routes. After the automatic check was INVALID
HTTP202, the single later public-browser run passed **18,460/0 across 18 suites**.
Ryan accepted the index fix on November 11: **“Looks good.”** No generic
acceptance retest remains pending. The first-install source now includes this
fix; the live-data-copy permission boundary below remains unchanged.

**Current iPhone Safari user acceptance passed.** After the neighbours deployment
and requested check, Ryan reported on November 10: **“Looks good on Safari.
No problems I see.”** They accepted the subsequent index fix on November 11:
**“Looks good.”** Record both as user acceptance of the respective deployed changes.
The earlier partial and contradictory Privacy/menu reply remains historical;
do not ask them to repeat the same generic check. No per-route checklist,
iPhone/iOS version, OS text-scale or assistive-technology result was supplied.
Those limits do not negate their acceptance or create another approval hurdle.
This is not authorization for a temporary live-data copy or a live write.

**Temporary live-copy rehearsal approved November 11.** Ryan explicitly approved:
“May I copy live’s database and site files, including uploads, into a temporary
GitHub Actions runner to test installation and rollback, then delete the copy?
Live stays unchanged; test logs and screenshots are retained.” This authorizes
the complete isolated installation/repeat/rollback/reapply, rendered checks and
cleanup lifecycle. It does not authorize a live write, live enablement or main
merge. The pulled feature branch was current at `108e68a8788926393d4914ac0effa6dd696da9d3`.
Payload bindings are unchanged from the final 51/0 source gate; only notes and
operation requests changed. Do not ask for this same rehearsal permission again.

**Earlier copy rehearsal rejection, now superseded for this approved scope.** Automatic
approval review rejected the `peek-visitor-release-rehearse-6` dispatch before
execution because copying live's database and uploads into a disposable Actions
runner exceeded the then-current staging39 scope. The explicit approval above
now covers that additional temporary data copy, while preserving the live-write
prohibition.
No retry or workaround was attempted. At that rejection, HEAD and
`ops/request.json` were unchanged and the request file still named completed
live-sourced copy5. No live-sourced copy6 run, snapshot, browser result or website
change exists. The subsequent Search requests concerned staging39 only.

Concrete approved rehearsal request (dispatch and results recorded below):

```json
{
  "id": "peek-visitor-release-quiet-search-copy-1",
  "op": "rehearse",
  "target": "live",
  "installer": "link-ideas/peek-release/install-peek-release.php",
  "args": ["--with-uploads"]
}
```

This selects the refreshed localhost-only bundle above, after completed Search
staging verification. Permission covers reading live's database and site
files/uploads into the repository's disposable GitHub Actions runner, then
installation, repeat, rollback/reapply, browser tests and cleanup inside that
copy. It does not authorize live writes or a main merge. The whole database can
contain private account/form/plugin data; `wp-config.php`, ordinary logs and
backup archives are excluded from the file copy. Copied WordPress containers
use an isolated network. SQL, source files and private recovery snapshots are
excluded from retained evidence; logs, fingerprints and public-page screenshots
are kept for 30 days, while the operations report persists. Full diagnostic
stderr is retained, so this is not a claim of automatic secret scrubbing.
The explicit approval above is limited to this rehearsal lifecycle.

Pre-dispatch review found and corrected a cleanup-result gap in the rehearsal
runner: Compose removal errors were suppressed, and cleanup could return the
saved test result despite removal failure. No failed cleanup or data leak was
observed. Release cleanup now verifies absence of its owned relay, labelled
Compose containers/volumes/network and exclusively created temporary directory,
including early setup failures. Cleanup failure prevents a successful result;
an earlier nonzero result is preserved. Unproved teardown retains the copy only
inside the disposable runner rather than exporting it or claiming deletion.
Only status evidence enters the retained cleanup log.

The focused cleanup suite passed **51 cases**, integrated into the existing
release-evidence gate (**160 top-level controls**). An actual synthetic Docker
probe also verified removal of the owned container, volume, internal network
and directory while unrelated synthetic resources remained intact. No website
data or requests were used. Bash syntax, required ShellCheck and diff checks
passed; independent review found no blocker. The frozen runtime/installer and
manifest hashes above are unchanged. Complete Actions Gates and the approved
real-copy rehearsal still follow; these synthetic tests are not copy proof.

Complete source Gates then passed **51/0** at **07:40:52 UTC** on November 11,
run **42980390364**, checkout `01dd5dcf9a342404aa1e996c074d1bf3bf496b1d`.
The full job log was read, including the release evidence and cleanup gate.
The approved request above was dispatched from
`54e2f5a4fa56819adffa7f4f382eb296f2c228d5` as Actions **42981371762**.
The operation finished **FAIL at 07:44:11 UTC**; the temporary copy's cleanup
was separately verified successful. Live was unchanged.

The fresh copy used WordPress 7.1.4 and the exact 15-page visitor inventory.
Two warm passes and preflight agreed on protected state
`b002dcc5d0fa81a3ac9796c52a5f370a577ac031314ef7f2c1604eeb1f880481`.
First installation succeeded, creating data IDs 1339–1342 and core 1343.
Repeat installation preserved the exact complete snapshot and state, with
byte-identical B/B2 fingerprints. The actual copy-only snapshot was
`/backups/peek-release-before-20261111-074400-a653010f.json`, SHA256
`edb35401a992a55328ef145c279cb9b87cc8c23eeebd1aa4f87998dd2ec9f6e0`;
applied-state SHA256 was
`259e2fb873452ae91e721c84c0fe6d8a0b385619795577036dbbedc0b0106606`.

Rollback initially restored the exact original protected-state hash. All 15
restored pages then returned matching navigation signatures. A subsequent
exact-state verification found 15 removed postmeta rows (5069–5083) and 15 new
rows (5104–5118), with no other diagnostic differences and no truncation.
This supports read-triggered metadata regeneration, but the existing hashed
witnesses do not identify the keys/posts/values. It is not yet proof of a
particular asset-cache cause. The run stopped before reapplication and browser
checks. No release browser result or complete recovery-cycle pass exists.
The next copy adds read-only, hash-only metadata observations before and after
each original-page request. It uses direct SQL in the owned copied container,
avoiding WordPress bootstrap; raw values travel only in a pipe. Retained logs
include row IDs, key/value hashes and complete-row/without-ID hashes, with only
the known `_uag_page_assets` key labelled. Exact localhost origin, manifest
inventory and pipeline success are required. **38 focused controls and 163
combined release-evidence controls passed**, as did Bash syntax, required
ShellCheck and diff checks. Installer guards and frozen payloads stay unchanged.

Cleanup confirmed the owned relay, Compose containers/volumes/network and exact
temporary directory were absent. The copy-only journal above was deleted with
the copy and is not a staging or live recovery file. The 25-file evidence
artifact was verified: **25,349 bytes**, SHA256
`0a4e2e0f35a4dc2cef55bfe2edeb5d3659ee867ff0f23157e9b8ec17ecf656e1`.
It contains no SQL, private snapshots or copied source. Independent audit agreed
with these results; the full operation report and 390-line job log were read.

The diagnostic source then passed complete **51/0 Gates** at **07:59:01 UTC**,
run **42982828312**, checkout `988b3377a4e34c75bf745dc82de0235c8825d270`.
The same approved copy lifecycle was retried as
`peek-visitor-release-quiet-search-copy-2`, Actions **42983523811**, source
`e81115f490c11abcf450a33c5a4b928198768496`. It finished **FAIL at 08:02:36 UTC**,
with cleanup again verified. No installer, manifest, frozen payload or exact-state
guard changed for this diagnostic attempt.

Independent audit validated **60 captures / 40,800 metadata descriptors**.
All 30 pre-install original-page captures were identical. Immediately after
rollback, the first capture still matched that original state. Each subsequent
page GET replaced exactly that page's `_uag_page_assets` row. Every replacement
had the same byte length but a different value hash and different row hash even
after removing the metadata ID. All other existing rows stayed unchanged; the
capture chain was continuous, and all 30 diagnostic hashes bound the observed
rows. This proves asset-cache regeneration, not merely database-ID churn.

The copy-only snapshot was
`/backups/peek-release-before-20261111-080216-25285ac2.json`, SHA256
`bc0694629095b3798b478c1891173be9b5bec82c1445cfc19c949b749a9e8838`.
Original protected-state SHA256 was
`f6c2b8fbdbc414567bdfb2de3934786e85fd55f6f9c043f24723a331f934315f`;
applied SHA256 was
`f8ba1c792f7188e9260847974b569ab763bc74ce90c848b9f93eeb0b0a858b49`.
The five owned IDs again were data 1339–1342 and core 1343. Apply/repeat/initial
rollback bindings and all 15 original navigation signatures matched. No reapply
or browser phase ran. The journal was deleted with the copy. The full report
and 450-line job log were read. The verified **85-file / 4,679,549-byte** artifact
has SHA256 `ae3e5400e05161df2741bcb292e2693456144467602785ee7f87dd36b1a1e307`;
it excludes raw metadata values, SQL, private snapshots and copied source.

Public Spectra 2.20.6 source at commit
`d1852cd27c9ac93e8fd7606261c2a023c5c253da` explains a matching mechanism:
`save_post` calls its asset invalidator, which changes `__uagb_asset_version`;
later GETs delete/rebuild page assets when their `uag_version` differs. The
documented `uagb_delete_page_assets_skip_post_types` filter returns before those
writes. The proposed correction was explained before implementation: append
`wpcode` to existing exclusions only during the installer's factory/correction/
publication calls, then remove that exact callback in `finally`. This is temporal
post-type scope; the filter has no individual post-ID argument. Preserve every
exact-state guard and also protect the global asset-version row. Bind the actual
installed Spectra files/version to reviewed published source before any writes.
Do not normalize asset values/IDs, restore cache timestamps, remove general
save hooks, or install a persistent filter. The copied runtime disables WP-Cron;
the separate deferred asset callback needs its own source/model review, not a
claim that the browser rehearsal exercises cron. Actual installed-source binding
and the corrected complete rehearsal remain pending.

The official WordPress.org Spectra 2.20.6 package was verified: **8,617,905 bytes**,
SHA256 `d6ec81341a6e8351db21497f213e40f08f2f8bf8300098cd04fc94800a980caf`.
Its main header and five relevant class files match the pinned GitHub source
byte for byte. The correction is implemented with that source profile and no
changes to the frozen runtime/data/CSS. Expanded pinned-PHP models passed
**1,843 checks**, retaining all **138 injected write-boundary failures**. They
include source mismatch refusals, callback cleanup, existing-filter preservation,
the unscoped invalidation positive control and the deferred callback's behaviour
on owned/deleted IDs. The **163 rehearsal-evidence controls** also passed.
Independent review found no blocker and separately exercised the actual shipped
deferred-callback class with synthetic metadata: no writes for five asset-free
owned IDs or five deleted IDs; a matching-page positive control wrote once.
This is isolated class execution, not real cron or WordPress execution. Full
Actions Gates and the corrected copy remain next.

Corrected source Gates passed **51/0** at **08:28:08 UTC**, run **42986476601**,
checkout `8da6c988b0af039edd872c70bc9b83e96ef138f2`; the full job log was read.
The approved corrected rehearsal was dispatched as
`peek-visitor-release-quiet-search-copy-3`, Actions **42987385913**, source
`ce634995807e2b024819e3fc0f995fa49dc7440d`. It finished **FAIL at 08:49:35 UTC**.
The actual copied Spectra version, all six source hashes, loaded classes and
save hook matched the reviewed profile. Installation, repeat, exact rollback,
all 15 restored-page visits, subsequent exact verification and reapplication
passed. All 60 metadata observations retained the same 680-row fingerprint,
`a517909b3430762c5f5624a8debb380d72ceb8341fedf618661cb203bdc8237c`.
This verifies the correction through the first recovery/render cycle; it is
not complete post-browser recovery proof.

Copy3's first private snapshot was
`/backups/peek-release-before-20261111-083140-f34b5e89.json`, SHA256
`2f156b0ab3d293ceff2e51e53b9f5f5861e88b33bb3329be0ca57eefa99ca4a8`;
owned data IDs were 1339–1342 and core 1343. Original protected state was
`286072de65cf8584b9f011ee8f325b3f35e9f61dc176d15471761616632b4d74`,
applied state `9de088fbafdff51a5b8404446e3388a6e3b01132fef3d69814cd5295f106fb6f`.
The reapplication snapshot was
`/backups/peek-release-before-20261111-083156-7b4da73d.json`, SHA256
`8dc7172af75798ef4136609fc0f6bb24599f0ba52969d6f183a3f9d7db42829e`,
with IDs 1344–1348 and protected state
`490818984527d82ffb285a327100e6a2406a0a773d0900cdd8204af7628c4648`.
These were deleted with the temporary copy and are not staging/live recovery files.

All 15 retained core browser suites passed **14,319/0**. WebKit disclosures
passed **70/0**, but WebKit layout was **150/2**: both Therapeutic portrait
150%-root-text cases reported `ResizeObserver loop completed with undelivered
notifications.` The complete run is **FAIL/incomplete: 17/28 leaf suites,
2/13 phases**, with 14,539 passing assertions and two failures. The remaining
phases and post-browser rollback/reapply were not run. The enlarged-text errors
are under investigation; do not filter them out or infer a physical Safari defect.
Cleanup independently verified absence of the owned relay, containers, volumes,
networks and temporary directory. Live and staging39 were unchanged.

Retained artifact **13841547127** is **52,121,603 bytes**, SHA256
`bf5e262f33f89046164088c085d2c8a739f2c43eb7cfa890ca40f56e473c8df1`;
download size, digest and ZIP CRC were verified. Independent native audit
confirmed every source/marker binding, byte-identical A/C and B/B2 fingerprints,
the scoped B/D comparison, all 40,800 metadata descriptors and identical original
navigation signatures. All 127 artifact files were checked: no SQL, private
snapshots, copied PHP tree, environment files or nested archives were retained.
Independent browser audit confirmed the exact leaf totals and complete WebKit
evidence; all 145 unique JPEGs decoded. All 16 Chromium layout captures match the
accepted staging public captures byte for byte. The WebKit research box remained
legible, but the two page errors remain release blockers.

The observer correction subsequently reproduced the error in six original-runtime
WebKit structural-fixture trials and eliminated it in six corresponding trials,
with identical final geometry. It changes only the title-strip observer callback
to coalesce layout work into the next frame. It does not filter errors. Source
and focused-checker review found no blocker. Refreshed release models passed
1,843 checks with all 138 injected failures; 163 evidence controls passed.
These are offline results. The current candidate above awaits its own full
source Gates, actual staging39-copy/deployment verification and complete
live-sourced copy cycle. Live enablement remains disabled.

## Recommended scope

- The currently verified shared Peek core, with the title strip and rails on
  visitor pages, plus the four approved service-page summary data snippets.
- Remove the exact superseded reading-progress CSS block and disable Astra's
  scroll-to-top button, matching the reviewed staging design.
- Include the exact Search contrast CSS now verified on staging39, frozen in
  the release payload above.
- Keep existing jump-row snippet #1232 and native-guide snippet #1338 unchanged.
  Peek hides the native guide only after it initializes; the plain jump row
  remains the destination source and no-JavaScript fallback.
- Keep the four experimental navigation pages on staging. Their separate
  Firefox drawer investigation does not block this visitor release.
- Preserve live page content, SEO, menus, forms and other site data. Use one
  narrowly scoped, reversible WordPress-storage transition, not a database copy.

This scope was explained before preparing a release implementation. No summary
wording, research qualification, jump behavior or product simplification is
proposed here.

## Evidence already available

The [input-focus phase record](2026-11-09-peek-input-focus.md) contains the actual
staging snapshots, full hashes and independent artifact reviews. The deployed
core passed **12,374/0 across 14 public Chromium/Firefox suites**, then exact
snapshot verification before/after all 19 rendered staging routes. Its fresh
staging-copy rehearsal passed **14,386/0 across 15 suites**, including rollback
and post-browser state checks. These are completed prior runs, not new tests in
this readiness phase.

Fresh access checks on November 10 confirmed GitHub pull/push/admin permission,
a current clean branch, and HTTP200 for a plain staging39 Thai Massage request.
The selected environment reports current, enforced unrestricted network policy.
The inner sandbox could not reach its inherited proxy; the approved outer
executor succeeded. This does not prove a public browser session will avoid
SiteGround's separate HTTP202 challenge.

Read-only live fingerprint inspection
[42738048595](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42738048595),
source `86de2e6f97c4e3a0814c728bfc7492b407c6bf19`, finished **01:46:09 UTC,
PASS**. The complete report was read. It lists 15 published visitor pages, no
practice pages and no Peek core/data records. Live's two navigation dependencies
already match the current source and the recorded staging versions:

| Record | Stored-code SHA256 |
|---|---|
| #1232 A11yPageJumpLinks | `027d8a2b78d68b55be139b41f8594501fc2492aff437146364dc1c87bbfda99c` |
| #1338 TSSectionGuide v1 | `ef9ee2fb82fe91be616518b4a02f9ee276855a9b998685e7e5ee13064c84f785` |

The fingerprint is not a full-state capture. A separate database-only inventory
[42741958557](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42741958557),
source `9c6e090417c83164b457be16e5dcbd9ac4f6bb8b`, captured the exact live state
at **02:19:48 UTC, PASS**: WordPress 7.1.4, MySQL 8.4.11-11, a read-only
REPEATABLE READ snapshot across seven InnoDB tables, 15 published pages,
25 snippet metadata entries, seven scoped full records and zero issues. No
WordPress bootstrap, public HTTP request or database write occurred.

The complete report and checksum-verified three-file artifact were independently
reviewed. `baseline.json` is **2,047,975 bytes**, SHA256
`a6c6e923d484576d96060ab8d9f84110c5d785f9fec64c5ae7e5b0ef3f37f013`.
Artifact `13730735070` is 645,199 bytes, SHA256
`a0d1022e26be819216bad40bd800074b2db7f5447c55a37cd9615afd9967824e`.
All raw records, option and witness hashes recompute. There are no Peek records
in any status; #1232 and #1338 are active with their exact expected code and
metadata. Their PHP/everywhere taxonomy rows each have count 17, so new owned
snippets must account for precise shared-count changes. All 22 configured
service headings occur once in stored HTML; rendered anchor validation remains
part of the rehearsal.

Live Additional CSS #29 contains one exact reading-progress block (1,093 bytes
without surrounding newlines, SHA256
`f8da6191df763e2f270aafaf322d9c2b1e5050620f67af5c2c2fd3f311c7332b`).
Astra's `scroll-to-top-enable` is boolean true and its option autoload is `on`.
Only scoped public/navigation settings were exported; unknown page metadata is
represented by hashes. Independent inspection found no unintended credential
data in the export.

Staging's equivalent inventory
[42743361208](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42743361208),
source `3ac99e143ca812cf45c96a9b31399ac003840e9a`, finished **02:31:55 UTC,
PASS**. Its 2,425,765-byte baseline has SHA256
`06cbb42ae2b74f145f488c351a6be8cfec17d5981070e9b550adc1024a7df837`;
artifact `13730377390` is 832,281 bytes, SHA256
`d943af1ae6c945596977aa15dfd292354992877f97279086660b1fe33a464b1d`.
Full report, artifact and all row/option/witness hashes were independently
verified. It contains 19 pages, 30 snippet metadata entries, 12 scoped full
records and zero issues; WordPress/MySQL versions match live.

All 15 common page IDs, titles and origin-normalized content hashes match;
eight bodies are identical and seven differ only by the site origin. The four
extra routes are exactly the navigation practice pages. Current #1380 and all
four generated data snippets match repository bytes exactly. The eight existing
navigation metadata rows match; full dependency posts differ only by GUID.
Astra differs only in four image origins and the scroll-to-top boolean, with
autoload unchanged. The release's exact 1,094-byte CSS removal includes the
progress block's following newline and produces staged CSS SHA256
`2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae`.

The service-page metadata witnesses are **not** identical: both sites have 106,
with four IDs unique to each site and four differing hashes among shared IDs.
Only hashes were exported, so those differences are not classified. This is
additional reason to preserve live's own state and rehearse its exact copy;
no staging metadata or database is being promoted.

GitHub masks parts of the large encoded **console** copy. The artifact, leaf log
and published report copy are intact and agree; the console payload is not
canonical. Future inventories retain the full artifact and print findings and
hashes instead of duplicating megabytes of encoded data. The first live capture
itself took seconds; emitting its console copy delayed publication by minutes.

## Conditions for recommending launch

The refreshed first-install package lives in
`scripts/work-with-me-employer/link-ideas/peek-release/`. It accepts only exact
`http://localhost:8090`; no staging/live installer or live request is enabled.
Its current hashes and **1,694 / 162** offline check totals are recorded above.
The release browser adapter retains the original 15 visitor suites and two
supplemental WebKit suites, then directly runs the existing neighbour,
index-clearance and quiet-navigation suites in Chromium/Firefox/WebKit and the
Search contrast suite in Chromium/Firefox: **28 unique leaves across 13 phases**.
It requires the exact current/frozen core, four summaries and Search payload,
the fresh 15-page visitor inventory plus search/404, complete literal assertions,
case/load/capture evidence and all child streams. A failed or incomplete phase
stops later work. Existing staging aggregate guards remain unchanged. Search
uses its shared pure parser with 150 exact assertions per engine, 12 cases,
six page loads and 12 captures. The larger browser graph has a 3,900-second
rehearsal budget; this is a limit, not a completed run or measured duration.

The following November 10 preparation results are historical. Independent source
review cleared the attribute-initialization installer SHA256
`63dc97c39e551349f2b3c79d1e1778d387cae9bc496ef8ffa1bc482332b43d5b`
for isolated rehearsal. Its pinned-PHP SQLite/WordPress API model passed **1,325
checks**, including **138 injected write-boundary failures**. The separate
rehearsal evidence tests passed **135 controls** and detected seventeen deliberate
guard removals, including network and relay orchestration mutations. Adapter/profile
tests passed **46 controls**, then accepted the
actual frozen manifest and independently captured 15-page inventory using stub
browser entry points. These are offline results, not real WordPress or browser
verification.

The rehearsal retains the existing complete visitor browser chain, adds the
supplemental WebKit checks on localhost, and checks original rendered navigation
after each rollback. It binds each phase to the actual immutable snapshot and
five created IDs, including a separate rollback/reapply after browser activity.
The source and destination stores, shared taxonomy counts and unrelated content
must remain exact. A release-only internal Docker network isolates copied
plugins from external integrations before the first container starts. Docker
suppressed port publishing on the tested internal-only network, so an owned
localhost TCP relay forwards browser traffic to the exact inspected container.
The host browser itself is not network-isolated.

Seven synthetic TCP tests passed for the fixed relay, including address/bind
refusals, large duplex transfers, half-close, idle failure and clean termination.
An independent pinned-image Docker probe confirmed internal networking, no
default egress route on all three copy services, PHP-to-database connectivity,
localhost access through the exact relay, unchanged binary transfers and sixteen
concurrent reads. This used synthetic content only, not the site's copied files.
Source Gates runs `42747569094` (`f104e90c`) and `42748065437` (`d0f48692`) each
passed **38/0**, with one expected rendered-URL skip; complete job logs were read.
Final source `ff5499dfadfe841fc633d1f289fbdb2f216a40c2` passed
[Gates 42748849495](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42748849495):
**39 passed, 0 failed, one expected rendered-URL skip**, finished 03:23:20 UTC.
All 202 job-log lines were read independently and by the primary agent. This
includes the new loopback transport gate; it is still offline evidence.

The first actual copy request `peek-visitor-release-rehearse-1`,
[run 42749322045](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42749322045),
source `6b03bd5759b1cf28e33e290c4ff13f79f76f92dc`, finished **03:30:18 UTC,
FAIL**. Live was read only; the 269 MB copy ran on the internal network through
the verified relay. Its exact 15-page inventory, original navigation signatures
and three identical preflight state hashes passed before installation.

The first newly created inactive Thai data snippet failed the combined expected
metadata/relationship guard. Exact-state recovery also refused that unrecognized
state. The private copy-only journal was
`/backups/peek-release-before-20261110-033014-5f460677.json`; it is not a staging
recovery snapshot. There was no successful release marker, repeated apply,
verified rollback or browser execution: **zero browser suites/assertions/captures**.
The aggregate error does not identify the actual unexpected metadata or terms;
do not infer a cause or relax the guard. Hash-only mismatch diagnostics are now
prepared for another fresh copy after checks. They report counts and typed value
hashes from the same captured record, plus numeric relationship fields, while
retaining the original rejection. A separate source-proven NULL-first duplicate
metadata hole was closed with `array_key_exists`; this is not evidence of the
actual copy failure's cause. Nine new synthetic failure controls verify redaction,
refusal and preservation of unexpected draft state. The cleanup trap ran, but its suppressed
removal output does not independently enumerate resource absence.

The complete report, full job stream and checksum-verified 9,238-byte artifact
were reviewed. Artifact SHA256 is
`39dc4cf82e9736fc7111e92303c92a54462a85dd01832545e855fe8655e1d4d1`.
All eleven retained files are native logs, public navigation signatures,
inventory and hash fingerprints; no private snapshot, SQL, source files or
browser output was exported. No live or staging store changed.

Diagnostic source `5b70d7d6ae55e24afaa66a35bc2bf44cdc56d94e` passed
Gates `42750721428` at 03:40:08 UTC: **39/0**, one expected rendered-URL skip;
the complete job log was independently read. Fresh request
`peek-visitor-release-rehearse-2`, run `42751049392`, source
`0334bc564ff3156cd26220e11c46076d0a17f717`, finished **03:43:48 UTC, FAIL**.
The diagnostic proves that the first inactive Thai draft #1339 has the exact
three configured metadata values and taxonomy relationships #9/#10, with no
unknown metadata. Only `_wpcode_shortcode_attributes` is absent. This identifies
the first guard's failure; full post-creation state comparison was not reached.
Its copy-only journal was
`/backups/peek-release-before-20261110-034344-cf52ca44.json`. No browser phase ran.

The complete report and checksum-verified 10,141-byte artifact were reviewed;
artifact SHA256 is
`f0fd19c54657b3edb2b1cf9235799c47af9b8d7ffe49a0f68c03b454b2fc4cb7`.
The baseline fingerprint, all 15 original navigation signatures and inventory
are byte-identical to copy 1. Each run's warm/preflight hashes match internally;
their aggregate hashes differ across copies, which these retained hash-only
records do not explain. Comparisons remain bound to each copy's own baseline.

The next change was explained before implementation: recognize and journal only
that exact three-key new inactive draft, uniquely add the approved empty-array
attribute field, and verify all old rows plus the returned new metadata ID.
Strict four-key records remain required before correction/activation and in
completed snapshots. An unreturned or concurrent insertion must remain unknown
and stop recovery. No visitor runtime or wording change follows from this fix.
This initialization is now implemented and independently reviewed. The expanded
fixture uses the actual three-key factory profile; its 1,325 checks distinguish
verified recovery from refused unreturned-ID writes, preserve all old raw rows,
and reject any partial journal that skips initialization. A fresh real-copy run
is still required.

That source, `e5a86816ed15e4a6dbfdd68681116ff6418e96ef`, passed Gates
`42752406115` at **03:56:51 UTC: 39/0**, with the expected rendered-URL skip.
The complete 202-line job log was independently read. Fresh copy request
`peek-visitor-release-rehearse-3`, run `42752791077`, source
`a5b992df4f513d9740fff69baa03ef699e83b2a5`, finished **04:00:40 UTC, FAIL**.
The first draft passed its exact identity, three metadata keys and two
relationships, then failed the following full-state comparison. Attribute
initialization was not reached. The aggregate hashes do not identify which
surrounding field changed; taxonomy, hooks and other causes remain hypotheses.
Recovery refused the unaccepted state. The private copy-only journal was
`/backups/peek-release-before-20261110-040037-7a3f5751.json`.

Full report, job and all eleven artifact files agree. The 9,309-byte artifact
SHA256 is `ab9dfceea4637f1270e6193828d7b5dd60918db96169776d9837590da5ca8033`.
The original navigation signatures, fingerprint, inventory and paths match copy
2 byte for byte; all three preflight hashes agree within this run. There were
**zero browser suites, assertions or screenshots; WebKit did not start**.
No verified rollback or later release phase ran. Only the disposable copy was
written; staging and live remain unchanged.

The next diagnostic compares the already captured expected and actual values,
reporting bounded changed paths with types, lengths and hashes. Unknown path
components must be hashed. It must retain the original strict rejection and
recovery refusal, without new database reads, state adoption or normalization.
This is evidence gathering for the first-install package, not a visitor-runtime
fix or a completed release rehearsal. The diagnostic-only installer SHA256
`2cf3687e5bd6967fb69882ad3d230222db7b8eb4f248a4aac5af2a51fb387a0b`
has passed independent source review and pinned-PHP syntax checking. It limits
reports to 32 changes, eight path levels and 2,048 scanned child positions;
unrecognized numeric field names are hashed, while actual list indexes and
record/witness IDs remain identifiable. Failure to produce a diagnostic cannot
replace the original guard error or permit a later write.
The expanded offline installer suite passed **1,448 checks / 138 injected
write-boundary failures**. It checks exact count/witness/content paths, raw-value
and unknown-key redaction, missing/null/type/shape differences, combined output
limits and logging failures. Integration controls retain the unknown draft and
reject later writes or deletion. These synthetic results do not identify the
actual copy mismatch.
The full local source check then passed **38/0**, with Ruff unavailable locally
and rendered-URL checks intentionally skipped. The first restricted-shell
attempt could not use Docker or local test sockets and failed eleven gates;
rerunning in the permitted outer executor resolved those setup failures. PHP
containers used UID 0 only to read the existing private checkout mount, which
remained read-only. CI must still supply Ruff and verify the committed source.
CI then verified that source, `d8824c6e0bac2f36e88abebceaaff230dede2ba7`, in
Gates `42754592464`: **39 passed / 0 failed**, with only the expected rendered-URL
skip, finished **04:16:50 UTC**. All 202 log lines were read by the primary agent
and independently reviewed. Log SHA256 is
`455de4688eb7b391e64f4b36dbd4688c2b2c6a8f599dd559f968d56a01969c6f`.

Diagnostic copy request `peek-visitor-release-rehearse-4`, run `42754978423`,
source `19dd6a09e1129b62f9830c4a5456dd74fc06e11f`, finished **04:20:06 UTC,
FAIL**. Its primary diagnostic is untruncated and identifies exactly two
differences: the counts on shared taxonomy rows #9 and #10. Both changed from
**17 to 18 on first inactive creation**. The typed value hashes independently
recompute to those strings. All other compared fields match the proposed
creation state. The three preflight hashes match before the write.

The installer incorrectly expected only published owned snippets to contribute
to those counts. The next correction was explained before implementation:
account for each owned snippet while its two relationships exist, including
drafts; normalization, code correction and activation should not add another
count. Removal must subtract each owned relationship exactly. Full-state
comparison and unknown-state refusal remain required. The observed first-create
increment supports this correction; the complete creation/activation/deletion
cycle still needs fresh-copy verification.

The copy-only journal was
`/backups/peek-release-before-20261110-042003-91e0a251.json`. Recovery refused
the unaccepted state, and no later installer or browser phase ran. This attempt
is not a rollback pass or browser verification. No staging/live store changed.
The checksum-verified artifact is 10,101 bytes, SHA256
`8291661f722daab258bf3a4cfe42f6c3166030d340981833fe435e014eb93a89`,
with eleven native/baseline files and no browser output or private snapshots.

The count correction is implemented in installer SHA256
`fd137b1b6d4e532dd77e94fa1678473cfcddc1810979827df24f74c5a4897457`.
The pinned-PHP fixture passed **1,556 checks / 138 injected write-boundary
failures**, including intermediate draft counts, unchanged normalization and
publication counts, deletion decrements, exact partial recovery and rejection
of rehashed journals using the superseded published-only model. These remain
offline model results; actual activation and deletion are not yet verified.
Independent review cleared the count correction and full test diff. The full
local source check passed **38/0**, with the same Ruff and rendered-URL skips.
All full-state and ownership checks remain enabled. Only the shared-count
expectation changed; the other field expectations and runtime payload are exact.
Committed source `59fa6c80fde4a1e8ba29e1da068fe4e432607ef8` then passed
Gates `42755890311`: **39/0**, only the expected rendered-URL skip, at
**04:28:59 UTC**. The complete 202-line log was read by the primary agent and
independently reviewed; SHA256
`fef597af85a50cfc66d8d1c105e4dc90c035719ad48ff42cef0c97c9d3f80851`.

Fresh copy request `peek-visitor-release-rehearse-5`, run `42756230445`, source
`44e42dba67861ed799005ef65f59cc444aca7495`, finished **04:36:13 UTC, FAIL**.
The first inactive Thai record and explicit attribute initialization now passed.
Its code correction stopped at the owned-save field check: the only unexpected
field was the new record's GUID, changing from
`http://localhost:8090/?post_type=wpcode&p=1339` to
`http://localhost:8090/?post_type=wpcode&#038;p=1339`. Both exact strings were
independently reconstructed and verified against the diagnostic's typed hashes.
The intended code and modified-date changes are the only additional differences
in the subsequent recovery diagnostic. The changed save was not accepted, and
recovery refused it. No later creation, activation, deletion or browser phase ran.

The actual copy-only journal was
`/backups/peek-release-before-20261110-043610-c8f1cf19.json`. The complete report,
356-line job log and eleven-file artifact agree. Artifact size is 10,382 bytes,
SHA256 `63af1f0838e21be621fdeee4c35f7c596e4090e818725abb0d5119f2ec7ceaed`.
All original navigation signatures and baseline fingerprints match copy4;
preflight state is stable within this run. **Zero browser suites, assertions or
screenshots; WebKit did not start.** The long setup interval was dependency
installation, not browser verification. No staging/live store changed.

Local source inspection found the pinned Apache image contains WordPress 7.1.3;
the rehearsal overlays the actual copied 7.1.4 files. Matching official 7.1.4
source was then read through GitHub by the implementation agent and primary
agent. [`wp_insert_post`](https://github.com/WordPress/WordPress/blob/7.1.4/wp-includes/post.php#L4653)
reloads an update's GUID through `get_post_field`'s default display context;
[`post_guid`](https://github.com/WordPress/WordPress/blob/7.1.4/wp-includes/default-filters.php#L91)
uses `esc_url`, whose display path emits
[`&#038;`](https://github.com/WordPress/WordPress/blob/7.1.4/wp-includes/formatting.php#L4585).
The initial insert directly stores `get_permalink`. Relevant upstream blob SHAs:
post `aa6e4f9999ae284cd0edff011b0ce9409a614236`, filters
`12ca0045b98b4133d31c671897f94c5de7d9ff55`, formatting
`d7b0e2a67189a55bd3c2e2853d9566ae3dcadf25`.

The proposed correction was explained before implementation: construct only the
two exact origin/ID-bound GUID forms, with raw factory/attribute states and the
entity form after code correction or activation. Published snapshots require
the entity form; uncorrected PHP and three-key factories require raw. A four-key
exact-code draft has one legitimate journal ambiguity: the existing factory
contract allows already-correct PHP, so it may still be raw until activation,
or may be encoded after correction. Only those two exact constants can be
accepted there. Every actual save and full-current-state comparison remains
exact. Do not use URL decoding/filter output to accept arbitrary variants, or
reinterpret the unaccepted copy5 journal as a recognized rollback state.
That narrow correction is implemented in installer SHA256
`4452c637d2dfdb4b673b1cae90ba3b233d2b336f2ac303bdb8b3ad3766a7243e`
and passed independent source review and pinned-PHP syntax checking. The updated
fixture passed **1,666 checks / 138 injected write-boundary failures**. It covers
the already-correct factory path, raw versus saved partial journals, exact
first/later saves, wrong-phase rejection and foreign host/ID/entity variants.
No generalized GUID equivalence or new database write was introduced. A complete
actual WordPress cycle still remains unverified.
The complete local source check then passed **38/0**; Ruff was unavailable
locally and rendered-page checks require the separate copy URL. Those two skips
do not supply browser evidence. Repository Gates and a fresh copy remain next.
Committed source `a42620c030f8db8f043407ff8795f321803e2487` then passed
Gates `42758745983`: **39/0**, with only the expected rendered-URL skip, at
**04:56:27 UTC**. The primary agent and independent reviewer read the complete
202-line log, SHA256
`aa19783b3a64f8a92d9fb78fb0893e67d65a2925dde7628a36e1eae68c3f41a8`.
The subsequent copy6 dispatch was rejected before execution as recorded above.

1. **Complete:** Search's staging39 source, fresh-copy, deployment, public-browser
   and actual-snapshot gates passed as recorded above. Its exact CSS is frozen
   in this package. Earlier failed path-preflight attempts remain documented.
2. **Source CI and copy permission complete:** reconfirm the captured
   baseline against the actual copy; do not silently rebase changed stores or
   retry the rejected copy6 request unchanged.
3. Rehearse this exact transition on a fresh isolated copy of current live, including
   media: install, repeat, exact rollback, reapply, regulated-wording and rendered
   browser gates, then post-browser recovery. Test the actual visitor inventory,
   including search/404; staging's 19-page inventory is not the live inventory.
   Require all 28 leaf suites, including the supplemental Linux WebKit checks.
   Linux WebKit remains engine evidence, not physical Safari testing. In
   particular, actual creation, later saves, activation, exact shared-count
   changes and deletion must pass: copy5 stopped during first code correction.
4. Prepare and independently review the live profile and dedicated first-install
   push/recovery wrapper described below. Bind a live dry run to the exact current
   baseline, require fresh verified off-site backups before apply and rollback,
   retain the actual journal, and make purge/rendered/full-state checks decisive.
   Preserve older staging rollback endpoints. Adding a live hostname to a
   staging installer or reusing the older cleanup wrapper does not meet this gate.
5. Present the concrete tested release and recovery package for Ryan's explicit
   live go. The repository's manual LIVE workflow leaves the main merge and live
   start with Ryan; no live request is prepared or dispatched yet.

Safari acceptance is complete for neighbours, index clearance and the four quiet
controls. Fluidity is deferred until after live. Only a subsequent change to an
accepted behavior warrants checking that affected path; no generic Safari retest
is pending. VoiceOver and OS text scaling remain separate unverified coverage.

No arbitrary calendar date or amount of elapsed time makes the release ready.
Recommend a window when these conditions pass and someone can immediately check
the live result and initiate the tested rollback if necessary. Traffic-dependent
timing cannot be inferred from the evidence available here.

## Remote package still to prepare

A source-only review identified these remaining implementation requirements;
none enables live or changes an Actions workflow:

- Keep the shared frozen payloads and select explicit copy/live profiles. The
  existing manifest pins localhost-mapped post/option hashes and the mapped
  Astra result. Live needs its own raw captured hashes. Preserve its recorded
  `home` and `siteurl` (`http://thaisolitude.com`) while checking public pages over
  HTTPS. The marker parser also needs a precise live-origin/private-path profile;
  its current contract accepts only localhost and `/backups/`.
- Prepare one dedicated first-install push wrapper using the input-focus
  wrapper's backup sequence. Require a fresh DB export and verified encrypted
  off-site copy before apply and rollback. Preserve the actual journal off-site
  even after failure; bind successful verification to its returned path, hash,
  manifest and five IDs, never a newest-file guess.
- Make exact before/after state verification, successful purge and correct
  rendered core/data scope across all 15 visitor routes decisive gates. Rollback
  must also restore original fallback navigation. `scripts/ci/run-op.sh` does
  not take the backup itself, and its automatic post-change/restore sections
  are informational; its overall PASS alone cannot establish these results.
- Preserve recognized partial rollback prefixes. Snapshot-free preflight refuses
  any existing Peek; completed-state verification refuses incomplete journals.
  Neither can be copied into a blanket rollback prerequisite. The installer
  already validates exact reverse prefixes before writing. Keep that behavior
  behind the fresh-backup step, or add a genuinely read-only prefix inspection.

These findings were independently reviewed against `push-peek-input-focus.sh`,
`run-op.sh`, the installer and evidence parser. The existing manual LIVE route
remains sufficient after acceptance and Ryan's go; no new workflow, live request
or live enablement has been prepared.

## Supplemental engine preparation

The focused WebKit paths preserve existing Chromium/Firefox assertions. The new
read-only wrapper pins Playwright 1.63.0, keeps complete logs and requires exact
page/capture counts and matching literal assertion totals. Independent review,
12 offline evidence controls and origin/target refusal checks passed. Source
`392ed10c6fbf10aff48df16a323c694daf4ce94e`
[Gates 42740052057](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42740052057)
passed **36/0 with one rendered-URL skip**.

Public WebKit attempt
[42740074026](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42740074026),
source `b1288c9a7c7f7b956caef61ae2a85f4799346080`, finished **02:08:57 UTC,
INVALID**. Official WebKit 26.6/build 2359 and its dependencies installed and the
browser launched, but the first Privacy request returned HTTP202. There were
**zero assertions, zero successful page loads and zero captures**; layout was
skipped. This establishes runner capability, not site verification. The next
WebKit check belongs on the isolated rendered copy; no public retry is queued.

At this earlier input-focus stage, Ryan's short Safari reply was partial and
ambiguous. The later neighbours layout received their no-problems acceptance,
recorded above. Recheck affected behavior only if a subsequent runtime fix
changes it.

Source review identifies two possible discriminators, not reproduced Safari
causes. An open Peek preview/index consumes the first outside tap, including a
hamburger tap; testing with Peek already closed distinguishes that existing
behavior. Safari browser-bar changes can trigger `visualViewport` refitting,
which currently writes inner scroll positions. Native scroll recognition and
scroll interruption need separate observation before changing either. The
supplemental WebKit layout check uses taps and keyboard End, not finger swipes,
and cannot settle the actual iPhone scrolling complaint.

## Separate follow-ups

The practice drawer's public Firefox mouse-input failure remains open. The one
later hold-split retry
[42738993282](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42738993282),
source `5a30396fadfb1baab8e0adc6aa24b9023c79c471`, finished **01:55:11 UTC,
26 passed / 1 failed**, two page loads and one capture, with no INVALID. Hold to
go alone reproduces the subsequent missing drawer mouse input; gestures 1–3
pass. Correct commands are acknowledged but no DOM events arrive in the failing
case. The underlying mechanism remains unproved.

The full report, traces, failure screenshot and checksum-verified artifact were
reviewed. Each sanitized HAR contains 11 embedded HTTP200 resources with 760,956
body bytes. Both cases have identical resource bodies and the exact current
core/style. Captured assets support local investigation without further staging
requests. The earlier challenged attempt remains zero evidence, and this
diagnostic does not waive the full failed practice-page regression gate.

Two offline replays had 29 passing interaction assertions and matched all
captured body hashes and geometry, but both remain **INVALID**: a reject-only
proxy blocked one browser-level CONNECT to staging39 outside Playwright's page
request stream. No traffic escaped. Startup and idle-page controls were clean;
the cause of that extra browser request and the public-only input failure remain
unproved. No allowance, timing workaround or runtime change follows from this.
The next useful discriminator is browser drag/capture state in the failing
environment. This separate prototype investigation is paused while preparing
the visitor release.

Known transient label overlaps, real OS text scaling and broader assistive-
technology coverage remain documented. They should not be described as newly
fixed or silently substituted for the acceptance checks above.

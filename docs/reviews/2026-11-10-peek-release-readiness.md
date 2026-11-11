# Peek visitor-navigation release readiness

**Final post-check inspection PASS, November 11 at 22:22:34 UTC.**
`peek-live-finished-inspect-20261111-2`, Actions **43111166451**, source
`399ed4b2751034b78948f3d848dda094c309d0d0` directly descends from merged PR #81.
Read-only `--inspect-live30` confirmed both applied and recoverable states at
exact `b34e86f7…af4427`, with the same journal, original SQL, receipt, IDs,
manifest/profile and six Spectra sources. The full report was read. No purge,
page request, installation or rollback ran. This closes the post-deployment
verification after the automatic checks; no further LIVE action is pending.

The earlier extra inspection **43111020439**, request
`peek-live-finished-inspect-20261111-1`, failed on an unsupported argument before
contacting the site. Its local branch was incorrectly based on stale
`origin/main` (4e89…) despite a fetch: this checkout's configured refspec tracks
only the older feature branch, so `git fetch origin main` did not update that
tracking ref. The corrected inspection used the explicit verified merge SHA
`dd1b0cc001e4557360cae6dea93071588a9f6f17`. That tooling mistake changed no site
state and does not invalidate successful LIVE #32. Both reports were read.

**LIVE finish PASS, November 11 at 19:34:34 UTC.** Ryan merged PR #81 at
19:04:28 UTC, main `dd1b0cc001e4557360cae6dea93071588a9f6f17`; branch and merged
source Gates passed. LIVE #31 (**43083839402**) refused the old confirmation at
19:05:18 UTC before any operation. Ryan then started LIVE #32 (**43086291426**),
request `live-peek-finish-20261111-1`, at 19:20:47 UTC. Full reports were read.

The finish wrapper passed applied/recoverable checks, verified fresh SQL plus
the original journal and receipt encrypted off-site before purge, purged the
SiteGround cache, verified 16 HTTP200 responses and the intended HTTP404, and
passed final exact applied-state verification. State remained
`b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427`, IDs 1339–1343,
manifest/profile/source bindings unchanged. There was no reinstall/live rollback.

| Actual private file | Bytes | SHA256 |
|---|---:|---|
| `live-before-peek-release-20261111-192059-11005.sql` | 4190422 | `cf8132bb1da1a5abde9e813a60b96c366fdcb99220ffc5f4ef67e12ae552b332` |
| `peek-release-before-20261111-162615-e00022b6.json` | 1682009 | `4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b` |

Receipt `peek-release-live30-receipt.json`, SHA256
`9202c8fb4e90babcba739a75660af944fc2a5adbfd1881278458312bc9ab9a0c`, was also
preserved encrypted in `offsite-2026-11` with exact readback. The fresh SQL is an
installed-state export; it does not replace the original rollback journal/SQL.

Automatic post-change checks: rendered content/wording/posture PASS, all listed
pages/sitemap/robots HTTP200, no site PHP errors/warnings/deprecations. Generic
reference comparison exits 1: schema counts 27 versus 23 (four route differences,
zero malformed blocks), and 20/20 pixel differences versus historical staging36,
zero missing captures. This is not an aggregate visual pass and does not prove
every pixel difference harmless. Backup import exits 0, fingerprints 100/100;
restore comparison exits 3 with only `iej_sgs_log_events` and
`iej_sgs_log_visitors` differences. No CSS/option/page/SEO/snippet differences
were reported. These ancillary differences are recorded separately from the
successful release-specific gates. No further LIVE operation is pending.

**Live incident verification PASS, November 11 at 18:20:00 UTC.**
`peek-live30-incident-inspect-1`, Actions **43076682138**, source
`b0288b4b80aab6f3c3eec01fac559bc8520ca570`, ran only `--inspect-live30`.
Both applied and recoverable markers passed against exact current protected
state `b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427`,
actual IDs 1339–1343, the unchanged original journal/SQL, manifest/profile and
receipt `9202c8fb…b9a0c`. All six live Spectra sources matched. No installer,
purge or page requests ran. The full report was read. This and recovery copy 2
support the new finish-only request `live-peek-finish-20261111-1`,
`--apply --finish-live30`, with automatic post-change/restore checks. Ryan alone
merges and starts it after final Gates passes. #30 must never be rerun.

**Actual-state recovery copy 2 PASS, November 11 at 18:18:45 UTC.**
`peek-live30-recovery-copy-2`, Actions **43075719186**, source
`b31dc65d2b984edd2a893f76239924ec58343af1`, imported fresh current live SQL
unchanged (4,189,962 bytes, SHA256
`e2800ea70194fef1688c86b0271383dc95fbb64ec20594a690d191789fcbb14e`), with raw
live URLs and zero collation substitutions. The actual immutable journal,
actual snippet IDs 1339–1343 and all six pinned Spectra sources matched.

All seven incident verification/recovery steps passed. All **17 HTML responses
passed with Peek installed and all 17 passed after rollback**, including Search
and the expected 404. These are exact static response checks, not a new browser
matrix. Exact protected-state verification passed after each response phase:
applied `b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427`;
restored `629c1e66eece68a9d7c91b6de5d7b780582a688e4d885a14aa389e053dcf05a4`.
Eight SQL observations independently confirmed the five cache-row identities,
global asset version and raw origin stayed exact. Recovery retains the three
independently generated cache rows; it does not recreate the two stale rows or
claim historical whole-database identity. The incident receipt is SHA256
`9202c8fb4e90babcba739a75660af944fc2a5adbfd1881278458312bc9ab9a0c`.

The original journal and original SQL remained byte-identical and mode 0600.
All owned relay/container/volume/network/temporary-directory resources were
deleted with verified cleanup. The full report was read. Live was read-only;
no live purge, rollback or reinstallation occurred. The successful run resolves
the response-loop failure below. Focused source checks include 2,203 installer
controls, 286 wrapper controls, 315 runner controls, 44 marker controls and 12
recovery-evidence tests. The accepted visitor runtime and summaries are unchanged.

**Actual-state recovery copy 1 stopped in its response loop, November 11 at
18:08:52 UTC.** `peek-live30-recovery-copy-1`, Actions **43074759786**, source
`9fb36811170675f766add419e49f160cac5e7f5a`, imported fresh current live SQL
without data/URL changes (4,189,962 bytes, SHA256
`8c3f6ba96d5ef0367e33df00dce56d899506f87deaa19d3b3d0a2e1dd90df5ad`).
The initial incident verification passed with the actual journal, actual IDs
and exact `b34e86f7…af4427` state. The homepage response also passed, but Compose
inherited the route-list stdin, leaving only one of 17 responses checked. The
explicit count guard failed; no rollback was reached and no full response or
recovery success is claimed. All copy resources were deleted with verified
cleanup; live was read-only. The correction redirects only the verifier child's
stdin to `/dev/null`, with a fixture that drains stdin and reproduces the broken
case without that redirection. A new isolated-copy run follows; never rerun #30.

**Cold reproduction PASS, November 11 at 17:57:32 UTC.**
`peek-live30-cold-proof-1`, Actions **43072850520**, source
`f13343b584253892c5797c2b70fd71f8f9fdadc6`, used the exact pre-apply SQL and
current source on an internal-network copy. SQL imported byte-for-byte with
**zero collation substitutions**; raw live URLs were preserved. All six pinned
Spectra files matched. Before any frontend request, the explicit WordPress
bootstrap reproduced the original protected-state hash `51fc7efb…c5814` and
changed none of the six protected tables or ten protected options.

Three individual HTTP200 requests reproduced the complete mismatch without any
Peek installation: post 1254 acquired its absent cache; posts 1255 and 1257
replaced their stale caches. The generated value bytes/SHA256s exactly match the
three live values. Mapping only the known live metadata IDs onto those generated
rows also reproduces every earlier live witness-descriptor hash:

| Actual live ID (copy ID) | Canonical row SHA256 with actual live ID |
|---|---|
| 5089 (5069) | `f01994fe793dd916e928f676e36e8500d3e1c68809df3262cab61a0e21bded1f` |
| 5090 (5070) | `edbb65d903e2b60ad9a2a17d4c4a85aa1ec3eec32e13bb1ad722102ea3ec80c6` |
| 5091 (5071) | `c272a43305be52f9510c44506d9f6060c80341e8eb44bc8226b0449ba657512f` |

Each first request changed only its own cache within protected state; all three
repeat requests were stable, including all observed option rows. Across the
entire copy run, five option rows outside the installer's existing ten-option
contract were added and separately reported. Two names match `nonce_key` and
`nonce_salt` (the isolated config does not copy production salts); the other
three remain identified by hashes. No blanket whole-database equality or new
option exclusion is claimed. The global Spectra version row remained exact.

The full report was read, and cleanup proved the owned relay, containers,
volumes, networks and temporary directory absent. The redacted final receipt is
`peek-release/incident-live30-proof.json`, SHA256
`c09de2231c44290b89d3c47935d2c8e2339879c2be2f7b86b3f9f725b7522450`.
Focused source checks: **28 observer tests / 231 orchestration controls**.
This proves ordinary page rendering can produce the exact transition; it does
not identify which actual live request did so. A pinned incident adapter and
targeted recovery test on a copy of the actual installed state are next. Live
has not been purged, reinstalled or rolled back, and #30 will not be rerun.

**LIVE #30 removed-row identification PASS, November 11 at 17:45:57 UTC.**
`peek-live30-before-1`, Actions **43071249809**, source
`9cee6838ddc75342adbf92846338605e0e8e5977`, read only the exact SHA/size-bound
pre-write SQL backup on the server. The temporary PHP reader was deleted;
no SQL was imported, WordPress bootstrapped, page requested or site changed.
Its parser passed **58 synthetic controls** before use. The full report was read.

| Removed meta ID | Post / key | Value bytes / SHA256 | Canonical row SHA256 |
|---|---|---|---|
| 4701 | 1255 / `_uag_page_assets` | 735 / `a5e39e98f6d20e1f0f41994f0ee66c28ad6d882c09a2cf2f14b62f5534dd7e7d` | `8a204a7db18edf9be297689861b987ec19a2cb373d65fb5b7a8ffab4419b2fe0` |
| 4702 | 1257 / `_uag_page_assets` | 688 / `07565e74285b1b9a9ec702f536431fc67560c54775411ed596d8ce48b37b37bb` | `80efacd2d6270111b8e880f396bf42e5fc77dcab94a44e8adb7e1354f2bbbef3` |

Both witness-descriptor hashes exactly match the missing-row diagnostic.
All five differences are therefore identified as Spectra page-asset metadata.
Pinned Spectra source supports deletion/recreation of stale caches and insertion
of missing caches during ordinary frontend rendering (Search can generate caches
for several result pages). This is a supported mechanism, not proof of the
particular request or requester. The earlier rehearsal warmed pages before its
first protected-state baseline, so it did not exercise these cold-cache changes.

The authorized next work is a narrow isolated proof using the exact original
SQL, pinned current source, raw live URLs and no initial warm-up. It must reproduce
the observed value hashes while keeping all other protected rows exact. Only
then can an incident-specific receipt account for these exact five witnesses in
verification and recovery. The immutable actual journal stays unchanged, normal
guards remain exact, and any sixth or different change must refuse. No #30 retry,
new live installation, purge or rollback is authorized by this diagnosis alone.

**LIVE #30 diagnosis, November 11 at 17:15 UTC: installed, verification incomplete.**
State-only inspection `peek-live-state-inspect-1`, Actions **43064935977**, source
`6b846de35f530a2717321b99648626563527983a`, completed at **17:07:09 UTC** with
the expected exact-state refusal and a complete, untruncated difference list.
Only original-row witnesses differ: postmeta **4701 / 4702 missing**, and
**5089 / 5090 / 5091 added**. The actual state remains exactly `b34e86f7…af4427`,
the same mismatch recorded at 16:26:48. All owned Peek records, page content and
other protected stores match the applied snapshot. No purge/render/rollback or
reinstallation was attempted by the diagnostic.

The narrow SQL-only query `peek-live30-postmeta-2`, Actions **43066327533**,
source `20e9af7a84783d99d133c80f3d062985ad300a39`, passed at **17:15:30 UTC**.
It read five fixed IDs in a verified read-only repeatable-read transaction,
without WordPress bootstrap. The earlier query **43065850949** failed its output
validator because MySQL emitted JSON booleans; the corrected parser accepts
only booleans or integer 0/1. That earlier result is not identification evidence.

| Added meta ID | Post ID / reviewed route | Key | Value bytes | Value SHA256 |
|---|---|---|---:|---|
| 5089 | 1254 `/personal-automation/` | `_uag_page_assets` | 740 | `30f619157647f3a5cd68219525b5b12a8dc210e828f0a130cf4f3fc9ed7d76ba` |
| 5090 | 1255 `/personal-automation/privacy/` | `_uag_page_assets` | 740 | `30f619157647f3a5cd68219525b5b12a8dc210e828f0a130cf4f3fc9ed7d76ba` |
| 5091 | 1257 `/personal-automation/terms/` | `_uag_page_assets` | 693 | `65156d9a7ff627f3726c259b781bbb272f6b3bc0eed91220fc6db1e65303bff6` |

Key identity was established locally by matching SHA256
`2e9207df9631808ef7779f3a1abfdd1c2cdd1668b3cef4085730edb9e1fa26a4`
against the reviewed Spectra source's literal `_uag_page_assets`; page IDs/routes
match the previously captured live inventory. No raw metadata values were
disclosed. The two removed rows remain unclassified. The new rows are generated
asset-cache metadata, but their producer/timing and the removed-row identities
are not yet proven. Do not treat this as permission to normalize or overwrite
all metadata, edit the immutable live journal, rerun apply, or bypass recovery
guards. Next essential work is those two identities and a narrowly proved way
to complete verification/recovery; deployment is not yet declared successful.

**LIVE #30: applied, then verification FAIL (November 11, 16:26:48 UTC).** Ryan
merged PR #80 at 16:24:35 UTC and manually started request
`live-peek-visitor-release-20261111-1`, Actions **43058202011**, main
`4e89eb25f5f5a68afdbb82aba45e3b385ec8a0b6`. The report and complete job log were
read. Preflight and all plugin bindings passed. The install completed, with
Thai 1339 / Therapeutic 1340 / Deep Tissue 1341 / Chair 1342 / core 1343.
The first subsequent exact verification failed before purge or wrapper-render
requests. Do not rerun apply or claim deployment verified.

Actual private recovery files under `/home/customer/private-backups/`:

| File | Plaintext bytes | SHA256 |
|---|---:|---|
| `live-before-peek-release-20261111-162615-74851.sql` | 3,919,398 | `4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0` |
| `peek-release-before-20261111-162615-e00022b6.json` | 1,682,009 | `4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b` |

Both exact files were verified on server/local transfer/encrypted off-site
readback. SQL preceded mutation; the actual journal was preserved before
verification. Applied state was
`c1bb9082d44a3cf1aa151678101b2509417d9e1f338e60365e7c8066c02ce874`;
first subsequent state was
`b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427`.
The expected manifest/profile remain `5255d85c…8269b5` / `49ed3154…6c3cac`.
The mismatch's changed-row diagnostic was swallowed by command-substitution
failure handling; no cause is established from the aggregate hash alone.

A state-only wrapper mode is prepared on `codex/peek-live-verification-diagnostics`:
`--inspect-state FILE SHA256`. It invokes existing exact `verify` against the
caller-bound actual snapshot, streams diagnostics including failures, and does
not apply, roll back, purge, render or transfer a private journal. Installer,
runtime and all source/state guards are unchanged. Focused wrapper tests pass
**114 controls**, including successful/failed state-only behavior and malformed
hash refusal. A read-only Actions diagnostic is next; no additional live write
or weakened rollback is implied.

**Release PR prepared: [#80](https://github.com/alanmz-crypto/new-masseuse-site/pull/80).**
The manual request is committed; nothing has run on live. GitHub records final
Gates and merge status. Once green, Ryan merges and starts Site ops (LIVE) on
main with `live-peek-visitor-release-20261111-1`. After that actual run, inspect
its full report and record its own live backup and journal before claiming a
completed deployment. No further preparation permission is pending.

**Final live preflight PASS, November 11 at 16:09:22 UTC.** Request
`peek-visitor-release-live-dry-3`, Actions **43055400254**, source
`36263729dc0b92ad4885748cd2f2d665cb2bb2ed`, passes after resolving the selected
document root physically. All six pinned plugin hashes, loaded classes and hook
match; the raw-live baseline and marker validation pass without rebasing.
Protected-state SHA256:
`51fc7efb4a9cfc36bd205b75bc6bc0acc268d5cbc60d02e9652629a4063c5814`;
profile SHA256:
`49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac`.
Enabled installer SHA256:
`aa2ab6618a294861fbbe1f8f4f6e0301a6be28a1656e2b7e3db1c48c810da04d`;
wrapper SHA256:
`3be46e9518a85d75a204d31b253b26341d91622199e6d0ea54835ae988337406`.
The report was read in full. No release database/cache write or installation
occurred. Independent review cleared the wrapper-only correction and its 104
offline controls. Final CI and PR preparation follow, with manual request
`live-peek-visitor-release-20261111-1`. Ryan retains merge/manual LIVE start.

Local `check-all.sh` could not supply a complete pass: seven PHP/container gates
failed because the container user could not read this workspace's private-mode
files; ShellCheck/Ruff were absent. Actual local-marker compatibility and the
wrapper tests passed. The clean GitHub Actions checkout supplies authoritative
full source Gates; do not report the local attempt as green. No additional
browser rehearsal is needed for the enablement/wrapper-only changes: runtime,
manifest and source-binding bytes remain identical to the successful copy4.

**Earlier live preflight stopped safely, November 11 at 16:04:50 UTC.** Read-only request
`peek-visitor-release-live-dry-1`, Actions **43054648677**, source
`07ddb2bd2b69a9d4a0c24138d905534c1ee4d611`, refused the installed/loaded Spectra
source binding before any release write. Its stdout diagnostic was swallowed by
shell failure handling. The dry wrapper now prints that diagnostic while
preserving the failing exit and stopping; no source guard or hash is changed.
A focused failure-output test covers this. Diagnosis precedes a LIVE request.

Diagnostic dry2, Actions **43055031280**, source
`ae5172f2e25738cbc32ef3d6df1664898d323c10`, stopped at **16:07:10 UTC**:
all six source paths were `missing-or-noncanonical`, with expected header/runtime
2.20.6 and the expected save hook, but mismatched loaded-file paths. The wrapper
now resolves the uniquely selected live document root with `pwd -P` before
WordPress bootstrap, addressing SiteGround's logical-home ancestor alias.
No source-binding, expected plugin hash, manifest or runtime is changed. The
wrapper suite passes **104 controls**, including retained failing diagnostics,
real-directory/ancestor-alias equivalence and missing-root refusal. A fresh
read-only dry run must pass before request preparation.

**Release go received November 11; guarded live dry run is next.** Ryan replied
“Continue” to the explicit enablement/dry-run/PR question and asked to defer
minutiae. The exact independently reviewed patch `96c2d71d…720a` is now applied.
Required source checks and the read-only live preflight precede the PR/request.
No live installation has run; Ryan retains the merge and manual LIVE start.
Earlier disabled/awaiting-go statements in this chronological record describe
the preceding stage, not another permission request.

**Completed readiness decision:**
The accepted staging design and complete fresh live-copy installation, browser
and recovery cycle have passed. The copy was deleted with verified cleanup.
This assessment does not start a live write or authorize a main merge. Work remains on
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

**November 11 release source refreshed; complete release cycle passed.** The
local first-install candidate now freezes the accepted quiet-navigation core,
including immediate summary neighbours, index clearance and all four controls.
The tested source also includes the narrow title-strip observer timing
correction described in the [resize record](2026-11-11-peek-title-resize.md).
Its staging verification is complete: copied-site browsers **25,343/0** with
exact recovery, public browsers **752/0**, and final actual-snapshot/stored
verification at **10:42:37 UTC**. The accepted quiet-navigation bytes are frozen
as shared v10 for exact recovery. The corrected complete live-copy release cycle
passed as **43006077712** at **11:33:22 UTC**: **24,737/0 across all 28 browser
suites**, installation/repeat/recovery before and after browsers, and verified
cleanup. No live launch is enabled. The [release review](2026-11-11-peek-live-release.md)
states the exact scope and the remaining manual launch sequence.
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
the refreshed release's separate first-install/recovery rehearsal later passed
as copy4 (43006077712), recorded below.
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
merge. At that approval, the pulled feature branch was current at
`108e68a8788926393d4914ac0effa6dd696da9d3`, with payload bindings unchanged
from the then-final 51/0 source gate. Subsequent source corrections and their
fresh evidence are recorded below. Do not ask for this same rehearsal permission
again.

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
legible, but the two page errors blocked that candidate until the correction and
successful fresh evidence below.

The observer correction subsequently reproduced the error in six original-runtime
WebKit structural-fixture trials and eliminated it in six corresponding trials,
with identical final geometry. It changes only the title-strip observer callback
to coalesce layout work into the next frame. It does not filter errors. Source
and focused-checker review found no blocker. Refreshed release models passed
1,843 checks with all 138 injected failures; 163 evidence controls passed.
These were offline results. At that stage the candidate still awaited full
source Gates, actual staging39-copy/deployment verification and the complete
live-sourced copy cycle; each subsequently passed as recorded below.
Live enablement remains disabled.

The corrected complete source Gates subsequently passed **55/0** at
**09:23:32 UTC**, run **42993487769**, source
`037fec5a6b95ecfcda02db98e8b2125326da9155`. Fresh staging39-copy rehearsal
`peek-title-resize-copy-1`, **42994882056**, source
`95f823413b04fa8f683d77e0a0f82087aac3fa7b`, subsequently passed at **10:11:22 UTC**:
all 29 browser leaves **25,343/0**, rendered Home/client gates **35/0 and 322/0**,
exact recovery before/after browsers, unchanged four-page data/CSS and decisive
cleanup. Independent raw-evidence and visual review found no new blocker.
The [resize phase record](2026-11-11-peek-title-resize.md) binds the actual
copy snapshots, state hashes and verified artifact. Read-only staging preflight
passed at **10:14:56 UTC**, run **43001945737**. The backed-up staging update
passed at **10:28:47 UTC**, run **43002377065**: actual snapshot
`peek-template-core-title-resize-before-20261111-101808-92c3067f.json`, exact
core/four-data state before and after all 19 route renders, unchanged Search CSS,
and fresh-export restore proof. Complete source Gates on that request passed
55/0 at 10:26:32 UTC. Public focused checks **43004137038** passed **752/0**
at **10:39:36 UTC**, with all four leaves and all 60 captures reviewed. All 24
resize captures match the successful copy exactly. Final actual-snapshot
verification **43005630245** passed at **10:41:20 UTC**, then stored inspection
**43005811818** at **10:42:37 UTC** confirmed published core code `f152fad9…73566`
and unchanged CSS29 `b024acc6…2c69b`. Staging verification is complete; the full
new live-sourced first-install/browser/recovery cycle passed as recorded next. The resize
record contains exact source bindings, backup evidence, state hashes, artifacts
and informational comparison limits.

The already approved fresh live-copy cycle `peek-visitor-release-quiet-search-copy-4`
was dispatched as Actions **43006077712**, source
`a2a165a6c1ddeec92a7c1dd9dd2db094f5d93697`, after final staging verification.
It uses the unchanged corrected runtime, installer and manifest recorded above.
It passed at **11:33:22 UTC**. Its complete 28-leaf browser matrix,
first-install/repeat/recovery sequence, post-browser recovery and verified cleanup
all completed. No live website write or active live enablement was performed.
Complete source Gates for that exact request commit passed **55/0** at
**10:52:41 UTC**, Actions **43006077978**. All gate outcomes and the source
binding were read; the separate rendered operation passed **24,737/0**, all
**13/13 phases and 28/28 unique leaf suites**. Home and client-page regulated
wording/structure/link checks passed, with no new PHP errors.

The first apply and read-only repeat preserve the exact first snapshot binding.
Both rollback cycles restore original state
`b6a5bdc6415078fb7f1a68886309206194bb15fc91dee610233be36778227d56`,
including after the complete browser matrix and restored-page GETs. All 15
original navigation signatures return without Peek. Fingerprints A=C=E are
byte-identical, B=B2 is identical, and B/D/F differ only in the five newly owned
snippet IDs and derived WPCode cache. The actual second snapshot and its complete
applied state are identical before/after browser activity. Final reapply uses a
third distinct snapshot and five distinct IDs, verified in full.

| Copy-only snapshot under `/backups/` | Snapshot SHA256 | IDs: Thai / Therapeutic / Deep / Chair / core | Applied state SHA256 |
|---|---|---|---|
| `peek-release-before-20261111-104623-a12201c1.json` | `4d3ab9ab233d807e7e3229b3108eb7f79dbe563a315d328a024dd51e1298c8a1` | 1339 / 1340 / 1341 / 1342 / 1343 | `47c2f59b08ec5cd44ac0b6be432602e517be7d57c1964f5510329f50b9b4ee0c` |
| `peek-release-before-20261111-104641-86b32b22.json` | `69ef7264fb3086141d86c45858b3097e9202208e4e42459e321df59556e11bc2` | 1344 / 1345 / 1346 / 1347 / 1348 | `d4c0857f1d78bb7c5868f0db7c96feb74a93356895e80347abbb423eafbb2207` |
| `peek-release-before-20261111-113314-7b9ee1d6.json` | `659d34619ee3c94d0267f00217343dd65fdaba79b362a2dfbc8c7458861e82a7` | 1349 / 1350 / 1351 / 1352 / 1353 | `37d6913208e4bec7da0dbc1515eb46d19b086f32db84225278dc86fd9690d34e` |

These journals were deleted with the temporary copy; none is a staging or live
recovery file. Cleanup verified the owned relay, containers, volumes, networks
and temporary directory absent. Artifact **13850624672**, **98,045,226 bytes**,
SHA256 `dbd27f04705ff45b087f6c9266b516444b7e61c0451d66c6b549df8ee961296a`,
passed exact size/digest, ZIP integrity and safe-path checks. The full operation
log was read instead of relying on the report's truncated last 400 lines.

Independent native and browser audits found no blocker. All **90** before/after
metadata observations contain the same **680 rows**, including 17 asset-cache
rows: **61,200** validated descriptors, set SHA256
`3792e5f62ab36c1e4d52a44dd3ae9b5470ca05f2e3b97369617f96931afb5fa8`.
All three 15-route original-navigation records are byte-identical, SHA256
`283c52115b172d4dbf808a0f51f81a75caa245febc3547f7091b22fa76bb33fa`.
All 14 Spectra source witnesses match. Home gates passed **35/0** and client
wording/structure/link gates **322/0**; PHP diagnostics remain identical, with
only the pre-existing WP-CLI deprecation. The 230 regular retained files contain
no private SQL, configuration, recovery journals, PHP source or backups. Twelve
public Search HTML captures are intentional rendered evidence.

The browser audit recomputed the unique leaf totals and replayed the strict
focused/WebKit/Search parsers; browser stderr is empty. Both previously failing
WebKit enlarged-text Therapeutic cases pass **9/9**. All **433 unique JPEGs**
decode without conflicts. All 24 Search captures match accepted public staging
byte for byte. Of 409 captures shared with the fresh staging rehearsal, 310 are
byte-identical; reviewed differences show small rail/text-rendering variations,
without a new visual regression. The enlarged research card itself is unchanged;
its compared image differs in only 76 rail pixels. The existing artificial
150% root-size fixture still wraps the site title; this is not new and does not
claim OS text-scale coverage. Standalone resize tests belong to the earlier
staging evidence, not this 28-leaf release count. Linux WebKit and simulated touch
do not replace real-device or assistive-technology testing; Ryan's Safari
acceptance is separately recorded above.

## Disabled live-release proposal

The reviewable preparation patch is
[`peek-visitor-release-enable-live.patch`](../inter-model/peek-visitor-release-enable-live.patch),
SHA256 `96c2d71d2fab901ad0102bbb83422fc042af2f5c843504b57a115545b447720a`.
It applies cleanly to the corrected source and was applied/tested only in scratch.
The active installer remains localhost-only; `ops/live-request.json` is unchanged.
It is not release approval or actual deployment evidence.

The proposal adds exact raw-live profile selection using the dated November 10
baseline, a dedicated backup/apply/verify/rollback wrapper, preselected private
recovery journals, and read-only checks for recognized partial recovery states.
Every write requires fresh verified off-site backup. The actual journal is
preserved even if apply fails; snapshot-dependent raw-live writes require the
caller-preserved SHA. Purge, each requested response's identity/core/data/CSS,
and final exact-state verification are decisive. Local marker shapes remain
compatible with the existing rehearsal parser. No existing live data is rebased.

Offline proposal checks passed **1,914 installer controls** with all **138 fault
boundaries**, **98 wrapper/marker controls**, and **23 response fixtures**.
Actual PHP-emitted local markers passed the unchanged Python rehearsal parser.
Raw-profile derivation, syntax, ShellCheck, existing installer-drift gates and
patch applicability passed; independent review cleared the exact patch hash.
These tests model execution and do not prove live SSH, backup-store availability
or installation. The corrected staging and complete release-copy evidence above
now supply the required site proof, with their exact source bindings.
After that go, the repository's required sequence remains: enable the reviewed
patch, run a read-only live dry run that refuses drift, prepare the live request
and PR, then Ryan personally merges and starts Site ops (LIVE).

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
Its current hashes and **1,843 installer / 163 evidence** offline check totals
are recorded above. The complete source Gates passed **55/0**, and the actual
copy cycle passed **24,737/0** with exact recovery as recorded above.
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
2. **Complete:** source CI, explicit copy permission and the exact captured
   baseline comparison against the fresh copy passed without rebasing stores.
3. **Complete in copy4 (43006077712):** rehearsed this exact transition on a fresh isolated copy of current live, including
   media: install, repeat, exact rollback, reapply, regulated-wording and rendered
   browser gates, then post-browser recovery. Tested the actual visitor inventory,
   including search/404; staging's 19-page inventory is not the live inventory.
   All 28 leaf suites passed, including the supplemental Linux WebKit checks.
   Linux WebKit remains engine evidence, not physical Safari testing. In
   particular, actual creation, later saves, activation, exact shared-count
   changes and deletion passed; the historical November 10 copy5 had stopped
   during first code correction.
4. **Prepared and independently reviewed, still disabled:** the live profile and
   dedicated first-install push/recovery wrapper described above. After Ryan's go,
   bind a live dry run to the exact current
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

## Remote-package requirements, now implemented in the disabled proposal

A source-only review identified these implementation requirements. The disabled
proposal above implements and tests them; no active live tooling or Actions
workflow is changed:

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
or active live enablement has been performed. The reviewed preparation remains
in the disabled patch above.

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

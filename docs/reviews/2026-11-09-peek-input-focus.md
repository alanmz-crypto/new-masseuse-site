# Peek input and keyboard focus follow-up

**Core and Rail repairs deployed; core verified, Rail Firefox drawer investigation open.** Ryan said “Continue” after
the completed Kiro fixes and the named next lane of older Thai/practice-page
focus issues. Work stays on `claude/wizardly-bell-xu0rta`, staging39 only.
The [previous Kiro fix record](2026-11-09-peek-kiro-fixes.md) describes frozen v6,
now superseded by the input-focus update. Current core public checks passed
**12,374/0 across 14 suites**, followed by exact actual-snapshot verification.
The chronology below preserves earlier failures and superseded pending states.

## Access and baseline

Fresh GitHub metadata confirmed pull/push/admin permission. The branch was
already current at `14c696d783cea9a9c57f32e8e0761c4f0a9423c4`; checkout clean.
The attached environment reports current, enforced unrestricted network policy.
The process sandbox could not reach the supplied proxy; the same request through
the approved outer executor returned staging39 HTTP200. Public browser testing
continues through the established Actions route, with challenge stops respected.

Core #1380 is frozen as `peek-nav-shared-v6.php` before editing:
102,655 full-file bytes, SHA256
`ce21afe60e2c1bdd6286ddc1f9a1e3d14d999bfa967dc4109e4ef5f78426b2fa`;
102,649 stored-code bytes, SHA256
`dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1`.
The previous `core-kiro-fixes` transition is pinned v5→v6. Its actual snapshot
`peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json` keeps its
meaning. A separate `core-input-focus` transition starts at v6.

Read-only practice-page inspection `peek-input-rail-inspect-1`,
[run 42709406091](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42709406091),
source `666c9e9219bd73c7ad08f1e84bb8b73c862bc633`, finished **22:10:57 UTC, PASS**.
Page #1348 is published, last modified 2026-11-08 02:42:14 UTC. Its exact stored
content is 36,892 bytes, SHA256
`0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748`.
It exactly matches the fully read single-block wrapper around frozen
`rail-ideas-v1.html` (36,858 source bytes, SHA256
`26f3472e8032cb89d99b923c23999fc6281537a32d0eed07a54f61347af8d38d`).
The report contains content, ID/status/date, not full metadata or terms; those
must be checked and preserved by the update preflight and snapshot.

## Findings and proposed correction

- Native Chromium touch on all four summary pages, both sides, focuses the
  destination and then loses focus to BODY on the compatibility mousedown.
  The existing focus reservation is enabled only for title-only cards.
  Reuse that reservation according to the touched element: navigation controls
  own their focus change; `.sum` keeps normal native reading focus. A blanket
  reservation would break summary-tap focus and was rejected in a local probe.
- Firefox generates a trusted Enter click with `detail:1` on the practice rail.
  The existing `detail:0` keyboard test ignores it in all four rail modes.
  Handle Enter explicitly with the existing per-mode keyboard behavior.
- In both engines, Enter on the practice Peek Close button bubbles into the
  card jump handler. Restrict that handler to the card itself and consume its
  handled Enter. Close, Stay and Escape return keyboard focus to the owning
  rail link; an outside pointer dismissal must leave the new target alone.
- The old practice-page installer is create/delete-only. Its rollback records
  `content_sha256` but does not check it before deletion. Do not roll back and
  recreate the existing page. Pin that original installer to frozen v1 and
  guard its old deletion path; use a new full-state-checked in-place update
  and restoration snapshot for this correction.

These choices were explained before implementation. No summary wording,
research qualification, layout redesign or jump-engine consolidation is planned.
Practice-page correction comes first; the summary-core correction follows with
its own rehearsal, backup, apply and recovery record. Both updates are in place,
so no fallback-only interval is planned.

## Verification record

The initial local native-touch probe produced 30 expected-result assertions,
including negative controls for the old runtime and the rejected blanket guard.
That is not 30 passing baseline behaviors. All eight old summary jumps lost
focus; the target-based guard retained destination focus in all eight and
preserved native summary focus. Close/side/row focus traces also distinguish the
targeted correction. These are structural-fixture results, not staging or
physical-device validation.

Local native Chromium/Firefox practice probes reproduced ignored Firefox Enter
and Close-triggered jumps in both engines. Explicit Enter handling and a
card-target-only jump handler correct those cases locally. The new complete
checks, fresh actual-copy rehearsal, regulated-wording gates and exact rollback
proof remain required before either staging write.

The complete new summary-focus checker passed **128/0**, eight page/side cases,
on current local fixtures. Frozen v6 produced **96/32**, with genuine failures
for destination, close, row and side-control focus. All cases completed without
INVALID. Four local captures were retained; the Thai right-hand landing and
Therapeutic research card were visually reviewed. Their structure and reading
area remain sound, but placeholder fixtures do not establish actual site design.

The candidate core has 102,756 file bytes, SHA256
`ddd7e3b9df4a3c6cd45b3f7c46bc56c4e2c1df6c4553846879681843e60f639f`;
102,750 stored bytes, SHA256
`a3a503f59dc8a557a24e5a03967481af824f29136668b1158d9cfa47ae3e537c`.
The initial practice candidate (before the reading-line correction below) has 37,595 source bytes, SHA256
`e5cf6d6613cd575b3953400fb340bdc38a02edec2e9775a43fa23dc68e930e6c`;
37,629 wrapped stored bytes, SHA256
`b0cfe89812390e3a20a1d0688992c5a84569152610ab977be671b10c069ab1a5`.

Local `check-all.sh` exited 1: 25 substantive gates passed, three PHP harness
gates could not read Docker-mounted files (structured-data, headers and Peek),
and shellcheck, ruff and rendered-page gates skipped. Its mounted PHP-lint PASS
does not establish lint coverage because zero files may have been visible.
Streaming the actual files into the pinned isolated PHP container separately
passed **706 core-installer checks**, **90 Rail-installer checks** and PHP lint.
The new browser-wrapper harness passed **82 checks**. Independent read-only
reviews found no deployment or evidence-aggregation blocker. Full Actions Gates
and fresh actual-copy browser/WordPress rehearsals remain mandatory.

Final practice fixtures passed **103/0 Chromium and 103/0 Firefox**, each with
three loads; Chromium retained two captures. This covers all five ideas,
Enter/Space/Escape, native mouse, software-click compatibility and no-JavaScript
links. Earlier attempts exposed harness setup errors (script-disabled animation
frame waits and failed mode selection). A reused-context/viewport-resize sequence
also lost a Firefox pointer click entirely: no pointer or mouse event reached
the document. Frozen v1 reproduced the same issue. Independent fixed-width
contexts pass the unchanged strict outside-click focus/dismiss assertion. The
earlier baseline comparison had genuine failures: Chromium 90/12, Firefox 44/40;
these used the earlier harness and are not directly comparable final counts.
The full retained native-touch suite remains required in the actual rehearsal.

The PHP-lint gate now refuses a zero-file scan, so an inaccessible mount cannot
produce a misleading PASS in future runs. This guard does not fix local mounts.

Actions Gates [42712388379](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42712388379)
passed at 22:30:49 UTC on source `3b42594053d926ee09a4cb78a55d2749bfffb872`:
**32 PASS / 0 FAIL / 1 rendered-only SKIP**. Full PHP harnesses, positive-file
PHP lint, shellcheck and ruff passed in Actions.

First rehearsal [42712427137](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42712427137),
request `peek-rail-input-rehearse-1`, source `cd9837921eba4938f1e349368d826fd77a810dc6`,
finished 22:31:19 UTC **INVALID before copy/setup**. Its static eligibility check
requires a separately quoted `localhost:8090`; the new installer listed the
equivalent full HTTP origin. Declare the local host explicitly and concatenate
the same exact origin. No allowed address changes; no site write or rehearsal
conclusion follows from this invalid attempt.

Corrected-source Gates [42712792983](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42712792983)
passed **32/0 + 1 rendered-only skip** at 22:33:42 UTC on
`e21218f77e22a1209c2d3919b6556b4f2b22c336`; installer lint and all 90 focused
mock checks also passed again after the host declaration adjustment.

Second rehearsal [42712817404](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42712817404),
request `peek-rail-input-rehearse-2`, source `c42e1611780937fba34a57a8dfa5fbe67655c19f`,
finished 22:35:12 UTC **FAIL**. The fresh copy had all ten baseline pages HTTP200
and exact ownership preflight. Real WordPress apply then failed full-state
read-back; recovery refused the unrecognized state. No idempotence, rollback,
reapply, rendered-copy or browser check ran. Zero browser suites/captures. The
throwaway snapshot `rail-input-before-20261109-223509-91cc2e2e.json` is not a
staging recovery snapshot. Complete artifact/log reviewed; staging unchanged.
Add changed-field/type/hash diagnostics without raw values or relaxed equality
to identify the actual WordPress side effect in the next isolated rehearsal.

Diagnostic-source Gates [42713676176](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42713676176)
passed **32/0 + 1 rendered-only skip**, 22:39:31 UTC, source
`be124988d73ca5e66edc4c411064444b6134999b`. The expanded diagnostic harness
passed 99 checks with no raw stored values exposed.
Third rehearsal [42713697500](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42713697500),
source `696d1020a762644a73980e21c039787337720a82`, finished 22:41:09 UTC **FAIL**.
It reproduced the same guarded local failure and identified only these changes:
`_uag_css_file_name` and `_uag_page_assets` removed; SureRank's
`surerank_seo_checks_last_updated` changed. No content-field difference appeared.
Again, no recovery/browser/rendered-copy proof followed. Complete artifact
reviewed; the local snapshot `rail-input-before-20261109-224106-2f5b6814.json`
is not a staging snapshot.

**Simplification explained before building:** because this update changes only
the inline script, use a byte-exact content-only compare-and-swap through
WordPress's database interface, then invalidate the page content cache. Avoid
general page-save hooks that rewrite Spectra assets and SureRank timestamps.
Keep the exact full-state preflight/read-back, unchanged metadata/dates/terms,
private snapshot and refusal on unknown recovery state. This requires another
fresh actual-copy apply/idempotence/rollback/reapply and rendered test run.

Revised installer lint and **93 mock checks passed**. This replaces the previous
save-hook/date-filter checks with byte-sensitive compare-and-swap, zero/error/
unexpected row counts, fresh cached-state detection, late duplicate ownership,
recognized failure recovery and exact reverse rollback. The diagnostic guard
still rejects unknown concurrent changes. Independent review found no blocker.
Targeted post and metadata cache reads and uncached ownership/taxonomy queries
prevent stale data from concealing a full-state difference. Browser payload
hashes and approved summaries are unchanged.

CAS-source Gates [42714913771](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42714913771)
passed **32/0 + 1 rendered-only skip**, 22:48:02 UTC, source
`ca62df42dd0f06334677e7fe2b2262a0cc1e415a`.
Fourth rehearsal [42714937933](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42714937933),
source `e44dc3d441f9c44ecb9f186023b79beacb83b806`, finished 22:53:26 UTC **FAIL**.
Actual WordPress content-only apply, idempotence, exact full-state rollback,
baseline fingerprints and reapply all passed. Only `page rail-nav-ideas` moved.
All ten pages stayed HTTP200; Home/client wording and no-new-PHP-error gates
passed. Local snapshots `rail-input-before-20261109-224855-0d61b3ee.json` and
`rail-input-before-20261109-224858-9ef2a686.json` are throwaway evidence only.

Complete browser leaves confirmed **370/2** across three suites: Chromium
keyboard **103/0**, Firefox keyboard **103/0**, native touch **164/2**. No INVALID
or stderr output. Both title matrices correctly stopped before running. The
two native failures are the phone light/dark practice strip naming the previous
section after a correct Questions jump; tablet passes. Two actual keyboard
preview captures were reviewed: visible focus, intact header/strips and existing
card layout. This is partial proof, not a passed rehearsal or staging write.
The raw log's isolated `95` also exists in the Actions log; it is consistent
with filtering the known WP-CLI Colors.php line-95 deprecation, but unfiltered
provenance is unavailable. Applied state hashes and independent idempotence
fingerprint verification passed.

**Additional correction explained before building:** the practice reading-line
calculation counts the demo bar's height but omits its sticky offset beneath
the site header/shared strip. Include the computed sticky top in that existing
calculation, preserving jump placement, gestures and layout. Keep the failing
native label assertion and add the smaller phone viewport to focused coverage.

The corrected reading line was proved with native CDP touch at the actual
iPhone13 viewport **390×664**: frozen v1 and the earlier candidate both landed
Questions at 183.14 px but named Who can benefit; the new calculation names
Questions at the identical landing position. Tablet already passed. Final local
keyboard/mouse suites passed **121/0 each in Chromium and Firefox**, now checking
the practice strip/count and `aria-current` after every chosen jump. The full
retained native suite passed **166/0**, phone/tablet light/dark, all five ideas.
These remain fixture results pending another actual-copy rehearsal.

The revised page source is **37,716 bytes**, SHA256
`41f1d5ff3635f9038f77838c546a4d595b695b2c1917298c2d9de3fa60759d5f`;
wrapped stored content **37,750 bytes**, SHA256
`eb50ba823a668423b0a3408093e707f5cdb59dec3794adcff89885f64a50e2fa`.
Frozen v1, markup, CSS and visible wording remain exact. Core hash is unchanged.

The Rail-only rehearsal now also restores the original snapshot **after** a
complete browser pass, compares baseline A, reapplies and compares the already
tested B without ID normalization. It refuses missing prerequisites and stops
on recovery or fingerprint failure. This checks that frontend asset/SEO activity
has not invalidated full-state recovery; no extra browser rerun is needed after
an exact reapply. Only the existing WPCode-cache exclusion remains.

Reading-line-source Gates [42716785059](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42716785059)
passed **32/0 + 1 rendered-only skip** at 23:01:21 UTC, source
`24a79e4746376286bea315c0b69a33114067d280`.
Fifth rehearsal [42716810354](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42716810354),
source `f42418a26b6dce2aa632fc2185362bc3a36b0eee`, finished 23:08:28 UTC **FAIL**.
All five browser suites completed **2,632/0**: keyboard Chromium/Firefox **121/0
each**, native touch **166/0**, title matrices Chromium/Firefox **1,112/0 each**,
all 21 actual routes. Complete leaf logs and 44 decoded captures were audited;
no browser INVALID/FAIL or stderr. Initial full-state apply/idempotence/rollback/
reapply and wording gates passed. The added post-browser rollback then refused
one changed field, `_uag_page_assets[0]`, and correctly did not reapply. Initial
local snapshot `rail-input-before-20261109-230222-b72c2099.json` and reapply
`rail-input-before-20261109-230224-63022cd7.json` are not staging recovery.

All 21 phone and 21 desktop title captures were reviewed, plus both practice
previews. No new customer-page obstruction appeared. The first selected pill on
the separate In-page Link Ideas prototype is unreadable in both phone/desktop
captures; comparison with the earlier title-rails and Kiro public captures
confirms it predates this phase. Source suggests the site's more specific link
color rule overrides its white active text. Computed-style confirmation and a
separate correction remain open. The current Rail prototype, Thai/Therapeutic
hero states and Privacy disclosure clearance were also visually reviewed.

**Next diagnostic sequence, explained before building:** establish an unchanged
old-page baseline with two local HTTP reads. A private observation permits only
the known `_uag_page_assets` movement on the first read and requires the entire
state to be exactly stable on the second. It never changes or rebases an install
snapshot. Retain the post-browser exact rollback. An added read-only `verify FILE`
compares the complete applied state against its actual install snapshot after
rendering. The cause of the cache movement is not yet proved; this copy included
uploads, so missing uploads must not be assumed. No staging write occurred.

The observer/verify harness passes **144 checks** and all three PHP files lint.
The baseline orchestration passes **13 offline stub cases**, Bash syntax and
whitespace checks. Independent review found no blocker for the next copy run;
staging purge/render and full-snapshot verification still need their own proof.

## Actual applies and recovery

Final Rail-wrapper Gates [42721576901](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42721576901)
passed **33/0 + 1 rendered-only skip** at 23:35:52 UTC, source
`e6d28098ccdbbe33b5aeb3df266a035b5f119412`. Sixth Rail copy rehearsal
[42721599833](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42721599833)
uses request source `1d038a84906917b5324d15b5310fe7c03424b96b`; it finished
23:44:23 UTC **PASS**. All five suites passed **2,632/0**; full leaf logs and
44 captures audited, zero FAIL/INVALID and empty stderr. The unchanged original
page's first GET changed only `_uag_page_assets` (708 to 713 raw bytes), and the
second GET preserved its entire warm state. Warm full-state SHA256
`bf54da645d20d2bc61f261ef13b792502cf6c3a603fec52daef6e745e7459b68`
remained exact at preflight/apply. Applied full-state SHA256
`f1800142c2e2b5000ece5c7c82d2e80cd514445bd5158be5e0bcbc58bd87a4d5`
remained exact after browser activity. Both rollback cycles restored A and the
final reapply reproduced B without ID normalization. This proves the cache
movement precedes this content change; the underlying plugin trigger is not
identified. The original copy snapshot was
`rail-input-before-20261109-233737-bd1c2e73.json`, never staging recovery.

Rail dry run [42722968261](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42722968261)
passed at 23:45:42 UTC, request `peek-rail-input-dry-1`, source
`b780a9247b3ca6052896f9d245d91465f17413e3`. The original complete staging state
matched the cold copy. Apply attempt [42723171410](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42723171410),
request `peek-rail-input-apply-1`, source `aa336b21d4035fba41fb4b0ea7d4fbaf42d595bc`,
finished 23:47:26 UTC **FAIL before purge/render or content writes**. Cold SQL
`staging39-before-rail-input-cold-20261109-234709-88168.sql` (4,530,739 bytes)
was SHA-verified and encrypted off-site. Read-only observation
`rail-input-observe-20261109-234726-256baa80.json` was saved. Its returned canonical
private path failed comparison with literal `$HOME/private-backups`. The raw
artifact has no ANSI/CR and the exact parser yields one valid path; the remaining
parent-equality clause failed. No settled dump, apply snapshot, post-change test
or restore proof exists for this attempt. Canonicalize the private directory for
the comparison; preserve unique-path/private-location guards and prove it in a
new dry run before retrying.
The wrapper now resolves the existing private directory with `pwd -P`, exports
that same path to PHP, and compares the returned parent against it. The exact
filename and unique-return guards remain. Its executable harness passes
**146 cases**, including home symlink/trailing-slash cases, a foreign canonical
parent refusal before purge, and a negative control reproducing the old literal
home-path failure. Canonical/configured paths are logged by the new dry run.

The shared-core deployment review found the old public checker also accepted
frozen v6, and did not bind its result to an actual recovery snapshot. Explained
before building: scope a dedicated deployment wrapper to `core-input-focus`,
retaining historical component dispatch. It requires verified encrypted backup,
syncs snapshots even after a failed apply before any purge/render, binds the
unique actual snapshot, requires purge success, compares exact served runtime
and CSS bytes on every published route, and verifies the complete core and all
four data records before/after rendering. Snapshot-free `--check` is refused for
this new lane; use `--verify FILE`. Recovery checks frozen v6 and exact original
snapshot state. This changes deployment verification, not runtime behavior.

The shared PHP harness now passes **851 checks**, including 118 read-only
snapshot-verifier cases and 27 actual embedded PHP renderer cases. The separate
executable core wrapper harness passes **150 cases**, including failed-apply
sync/error preservation and historical dispatch regression. Three PHP files lint;
independent source review found no blocker. The new wrapper test is a permanent
offline gate. Fresh Actions Gates and the complete core copy rehearsal remain
required before deployment.
The core copy sequence now binds the first, active re-apply and final reapply to
their own returned snapshots: each core apply intentionally changes modified
dates. It verifies exact core/four-page state before and after the browser pass,
then restores A and reapplies B without ID normalization. Earlier gate failures
stop before browser execution; failed recovery prerequisites stop later writes.
The two existing Rail blocks remain byte-identical. Its executable orchestration
harness passes **39 cases**. Final local full gates: **28 PASS, four known Docker
mount FAIL, three SKIP**; Actions must still run all mounted PHP gates.

Observer-source Gates [42719432850](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42719432850)
passed **32/0 + 1 rendered-only skip**, source `8c127c7f31d03c39976ebae6f45a0c2444f4940c`.
The final staging wrapper now requires verified encrypted off-site backups before
warming and again before applying. It proves exact unchanged-page stability
through a second purge/render, binds the returned observation and actual install
snapshot, and verifies the full applied state again after rendering. Every apply
attempt syncs available snapshots before output parsing, including failed applies;
failure stops later rendering. Rollback verifies the exact original snapshot state
after purge/render. No remote process substitution is used on SiteGround.

The updated PHP harness passes **149 checks**. The new permanent executable
wrapper harness passes **139 cases**, exercising the actual outer and remote Bash
with local command doubles, failure at each reached gate, snapshot binding,
malformed output, failed-apply backup synchronization, original error preservation,
and literal `$HOME` paths. This is offline ordering evidence, not a deployment or
rendered-page result. Both independent source reviews found no remaining blocker.
The final local `check-all.sh` has 26 PASS, four Docker-mount-related FAIL
(PHP lint and three PHP harness gates cannot read mounted files here), and three
SKIP (missing shellcheck/ruff, no rendered URL). Streamed PHP harness evidence
above is separate; Actions must complete the full gates before deployment.

Final deployment-source Gates [42723931080](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42723931080)
passed **35/0 + 1 rendered-only skip**, source
`7e19f0b232dbe3c8105aa6ead362230d3a3e0eb5`. Dry run 2
[42723968704](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42723968704)
passed at 23:53:17 UTC and confirmed the configured SSH home is an alias for
`/home/customer`, explaining the first attempt's path comparison failure.

Rail apply 2 [42724350260](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42724350260),
source `637aeef869b996f2fc7239ca46769298a571c46c`, finished 23:58:04 UTC **PASS
for deployment**. Actual content write was 23:56:52 UTC on page #1348 only.
Actual recovery snapshot:
**`rail-input-before-20261109-235652-0adc77b9.json`**.
Fresh cold SQL `staging39-before-rail-input-cold-20261109-235610-9748.sql` and
settled SQL `staging39-before-rail-input-settled-20261109-235636-10931.sql`
were each 4,530,739 bytes, copied with identical hashes and stored encrypted
off-site before apply. The actual JSON snapshot was likewise verified off-site
before purge/render. Unlike the localhost copy, staging's old asset metadata
remained at 708 bytes and its entire original state stayed
`d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc`
through all three old-page reads and both purges.

The applied content is 37,750 bytes, SHA256
`eb50ba823a668423b0a3408093e707f5cdb59dec3794adcff89885f64a50e2fa`.
Full state `22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6`
matched the actual snapshot before and after purge/HTTP200 rendering; exact Rail
script/style, five ideas, six sections, shared strip and licence passed.
Restore proof imported the settled SQL successfully, compared 104/104
fingerprints, and found only the intended `rail-nav-ideas` page difference.
The complete report and integrity-verified artifact were independently audited.

Automatic `post-change-test.sh` received **HTTP202 INVALID: zero browser checks
or captures**. This is neither a visual-reference failure nor browser proof.
The fresh core copy rehearsal runs while the challenge clears; a later focused
Rail public check and exact snapshot verification remain pending. No core write
or new core recovery snapshot exists yet. Never substitute a throwaway snapshot
or the earlier Kiro snapshot for the new core transition.

Core copy rehearsal [42724735055](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42724735055),
request `peek-core-input-focus-rehearse-1`, source
`650fccb9cd7b25d53425e5356116a26730143780`, finished **2026-11-10 00:16:35 UTC,
PASS**. All **15 distinct numeric suites passed 14,386/0**: 128 new native-focus
assertions plus 14,258 retained Kiro/strip/rail/clearance/summary/Thai assertions.
The 925-second browser run retained complete leaf logs with empty stderr, zero
FAIL/INVALID and zero wrapper exits. The full artifact also contains the 27
setup/baseline lines omitted from the published report: ten HTTP200 routes and
ready preflight. Its SHA was verified; 73 JSON diagnostics parsed and 137 captures
matched decoded bytes. Both wording gates and the PHP-error comparison passed.

The actual re-apply snapshot on the throwaway copy,
`peek-template-core-input-focus-before-20261110-000057-1afff167.json`, bound
core state `52ca2f1514dead2439daca6a7ca9f06bb1ccaef0476b7c0378c12c568b5ed61a`
before and after browsers. Rollback restored full state
`ef655025b75b7f6d8f5f039de9218222135f279064218c9c75b24b83964eed58`
and fingerprint A. Final reapply snapshot
`peek-template-core-input-focus-before-20261110-001630-ea94b68f.json` bound
state `c05aa64cd123004e9c2e7ea81c44daa74d34de64d9b2bda54815b58c62f1ade5`
and reproduced B without ID normalization. All four page records retained hash
`3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`.
These are **copy snapshots, never staging recovery**. Only core #1380 and its
expected WPCode cache moved; the four page-data snippets stayed exact.

All eight real-page native-focus traces retain the research H2 through the
compatibility mouse events, land 11.86–12.53 px below the strip, and accept a
genuine next pointer 130.4–131.1 ms later. The four new focus captures were
reviewed; root additionally reviewed Therapeutic landscape research. The reading
area remains substantial. Existing transient section labels can cover a small
patch of body text in the captured jump state; that separate issue remains open.
The delayed Rail public check and read-only core dry run followed this rehearsal.

Rail public check [42724912292](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42724912292),
source `d40d1de5b4ab2337c5e4a593a513747d3fb940b8`, finished **2026-11-10 00:19:55
UTC, FAIL**. Chromium passed **121/0**; Firefox had **115/2**, both failures on
drawer mouse opening at 390/1280 px after keyboard drawer tests passed. This is
**236/2 partial evidence**, six loads and two Chromium captures. The remaining
native-touch and both title suites stopped. Full integrity-verified artifact
audit found empty stderr and no INVALID/HTTP202. Existing output does not record
the failing click's events, so neither a product defect nor a harness cause is
established. A full local Firefox sequence passed 121/0, and 32 repeated drawer
activations across frozen v1/current code passed. Those local results cannot
overrule the public failure. Add bounded event/hit-test/focus/modal tracing to
the unchanged checks, then run one read-only Firefox diagnostic; no runtime
workaround or assertion waiver is justified by current evidence.

Core dry run [42727229928](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42727229928),
source `00e0acd4a24bf6d12b7c194904f5891a192118cd`, passed **00:20:33 UTC**:
exact frozen-v6 preflight is ready. Core deployment remains held during the Rail
failure investigation. Rail snapshot verification
[42727758208](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42727758208),
source `d8b545c2dc4001c1ad372b80ea8c497055455057`, passed **00:22:33 UTC**.
The actual `0adc77b9` snapshot still matches the entire applied page state
`22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6`
before and after an HTTP200 read; exact served script/style and scope passed.
This rules out a different stored/served Rail payload; it does not resolve the
Firefox interaction failure.

Read-only Firefox diagnostic [42728885867](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42728885867),
source `983c8970fa3395c19bd3c14c2a0359d537433079`, again reported **115/2**.
Both failed drawer activations produced zero events at the early window,
document and tab listeners, and zero dialog-open mutations. The tab was visible,
enabled and unobstructed, and the document had focus. Both failure screenshots
and the complete integrity-verified artifact were reviewed. This locates the
failure before normal drawer handling; it does not establish its underlying
cause or waive the failures. Source `520519296` Gates passed **35/0, one skip**
(rendered checks without a URL).

The local comparison used Playwright 1.62.1, whereas Actions pins 1.63.0.
An actual-page local Firefox comparison stopped before loading on certificate
trust. A temporary diagnostic accommodating the environment proxy certificate
then received HTTP202 on its first page: zero assertions, no usable page capture.
No further local public retries were made. Matching the Actions browser version
is the next diagnostic, initially against isolated fixtures.

The core deployment hold was reassessed against its exact executable diff:
the Rail practice page takes the unchanged `titleOnly` path and returns before
the modified shared-card handler is created. The core change affects summary
navigation focus only, and its fresh-copy rehearsal, rollback and dry run passed.
The independent core deployment was therefore requested while the separate Rail
Firefox failure remains unresolved; this is not a claim that Rail public gates
passed.

Core apply [42729961919](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42729961919),
source `c5bd7226988938f9f25cb027e33925323bb9f22e`, finished **2026-11-10
00:45:55 UTC, PASS**. The fresh SQL export
`staging39-before-peek-input-focus-20261110-004003-18454.sql` (4,531,623 bytes)
was checksum-verified and read back from encrypted off-site storage before the
write. Actual recovery snapshot
**`peek-template-core-input-focus-before-20261110-004021-4570ecbf.json`**
was likewise verified off-site before purge/render. All 19 published routes
returned HTTP200 with exact candidate runtime/style, correct data scope and
retained jump rows. Full core state
`d6cc25d5ca5f15cb0ef322aef5221535cb1ec293a5475eae9a9cbf8c28e0e6d0`
and four-page state
`3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`
matched before and after rendering.

Automatic post-change code/rendered wording, URLs, structured data and PHP-log
checks passed. Its zero-tolerance comparison against live reported **20/20
different views**, not a visual pass: live has the earlier navigation, while
staging contains this project's accumulated changes. The SQL restore imported
successfully; all 104 fingerprint records compared, with differences confined
to #1380 and `wpcode_snippets`. Focused public interaction verification and
post-browser snapshot verification remain pending. No fallback-only interval
occurred. Main/live were not changed. Recovery must use this actual input-focus
snapshot to return to v6 before considering the older Kiro recovery chain.

The matching Actions package (Playwright 1.63.0, Firefox 155/revision 1543)
also passed the unchanged isolated fixture sequence **121/0**. Its tab geometry
matches the public failures, but the fixture emits normal trusted mouse events.
An opt-in `--protocol-diagnostic` now observes the existing Firefox protocol
logger around only the unchanged drawer click. It forwards/restores the original
logger, records only content quads and mouse-dispatch commands/replies, and
rejects unsupported versions or incomplete traces as INVALID. No runtime,
input, wait or behavioral assertion changed. The local smoke passed **9/0**,
with eight complete protocol records, 31 DOM events, no dropped/pending records
and confirmed cleanup. Filtering and pre-launch refusal checks passed. The next
single public diagnostic will distinguish actual dispatch coordinates and
acknowledgements; earlier public failures remain unresolved.

Public protocol diagnostic [42731095161](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42731095161),
source `13d90fe6197e0aadbf5a40288198f37895e80ec4`, finished **00:50:13 UTC,
FAIL 115/2**. Both traces contain the correct tab quads and input coordinates
((362,481) and (1252,585)); Firefox acknowledged all mousemove/down/up commands.
Nevertheless, early page listeners recorded zero events and no drawer transition.
The complete integrity-verified artifact, report and two screenshots agree.
Traces had no dropped, missing or pending records and restored the logger.
This rules out a misplaced protocol coordinate; acknowledgement alone does not
prove input delivery or establish the cause. Source `1d1fd3acb` Gates passed
**35/0 plus one rendered skip**. No runtime patch or assertion waiver follows.

The next bounded `--drawer-history-diagnostic` compares two fresh 390px contexts:
mouse-first, versus Enter/Escape and Space/Escape before mouse. It retains the
same reset, click and traces, omits unrelated gesture history only in diagnostic
mode, and leaves default checks unchanged. Loads are spaced at least 5.5 seconds
apart, outside the measured interaction. A distinct final summary prevents this
probe being mistaken for a full keyboard suite. The isolated fixture passed
**12/0, two loads**, with complete protocol/DOM traces in both cases; required-flag
refusals and independent source comparison passed. This does not erase the
public failures. Core public checks and subsequent actual-snapshot verification
are running/queued independently of this diagnostic.

Core public verification [42731171781](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42731171781),
source `66563c1a9c89ff09bef9d21ae45f8827ce6a0dee`, finished **01:04:18 UTC,
PASS 12,374/0 across 14 unique suites**: 128 input-focus checks and 12,246 retained
Kiro checks. The integrity-verified artifact's literal PASS counts match every
summary; all stderr logs are empty, all wrapper exits are zero, and no FAIL or
INVALID appears. All eight focus traces retain the research H2 through trusted
compatibility mouse events, land 11.86–12.53 px below the strip, and accept a new
pointer 129.9–130.3 ms later. Root also checked all 16 disclosure traces and
reviewed the actual Thai jump and Therapeutic open-summary captures. The four
new focus images match their previously reviewed actual-copy captures exactly.
All **133 capture occurrences** are retained by leaf; there are 127 unique
filenames because the diagnostic clearance subset repeats six names, two with
different bytes. Flattened image folders do not replace original leaf evidence.

Post-browser verification [42731765629](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42731765629),
source `d420c926b0ee7b2ef9c516d12f1e18471fd3481f`, finished **01:05:05 UTC,
PASS**. The actual `4570ecbf` snapshot still matches full core state
`d6cc25d5ca5f15cb0ef322aef5221535cb1ec293a5475eae9a9cbf8c28e0e6d0`
and four-page state
`3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9`
before and after all 19 HTTP200 exact-byte renders. Its complete report and
integrity-verified artifact were independently audited. This read-only check
did not purge, write records or alter snapshots.

All 20 deployed full-page captures from the apply operation were visually
reviewed. No new persistent layout blocker was visible in their closed/default
states; this is not interaction proof or a passing pixel comparison against live.
Existing transient section labels overlap Accessibility's phone strip and Work
With Me's phone hero heading; the Thai jump capture also shows a label over body
text. Their duration and physical-device impact remain a separate follow-up.
The core lane is complete; the independent Rail drawer diagnostic remains open.

## Limits

Native input is browser emulation. Physical devices, true OS text scale and real
assistive technology remain unverified. Any Linux WebKit result is supplemental
engine evidence, not a Safari or iOS claim. Previously recorded transient-label
overlaps, enlarged-text branding and jump-engine consolidation remain separate.
The official WebKit 26.5/v2336 binary downloaded, but launch exited 2 because
GTK4, Graphene, Harfbuzz ICU, Manette, Hyphen and GLES libraries are missing.
This executor has neither sudo nor the managed package helper. Zero WebKit
pages or assertions ran; Chromium and Firefox evidence cannot close that gap.

# Search contrast before the Peek release

**Complete on staging39; live remains unchanged.** Ryan asked to continue the
remaining release preparation after accepting the four navigation controls on
iPhone Safari. Fluidity refinements remain deferred until after live. This
phase corrects Search contrast on staging39 and refreshes the separate release
candidate; it does not authorize a live write or a main merge.

## Access and actual baseline

The feature branch was current at `174f80c7b18c8480dc502a5cf3691badda452242`.
GitHub reports admin/push/read access. The attached environment reports current,
enforced unrestricted networking. The inner sandbox could not reach its proxy;
one approved outer request to staging39 Search returned SiteGround HTTP202.
No repeated local public requests followed. Actions supplies actual inspection
and rendered-copy evidence.

Read-only request `peek-search-css-inspect-1`, commit
`558c13a27467356ec984199b15acad92fd4a6133`, passed at **05:51:19 UTC** in
[Actions 42968382007](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42968382007).
The report contains only the final 400 of 1,330 lines. The full job log was
retrieved separately and its CSS extracted with the exact reported length and
SHA256: Additional CSS **#29**, **68,843 bytes**,
`2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae`.
The actual stylesheet has no staging39 origins to rewrite for localhost.

The preceding public navigation captures show pale text on white Search result
cards in dark mode. Captured Astra 4.14.1 CSS gives `.ast-article-post` a white
background. The site's dark theme changes body/headings to pale colors without
overriding that surface. A CSS-only fixture using captured styles measured body
contrast **1.37:1** and heading contrast **1.13:1**. Switching the fixture's
surface to the existing `--ts-panel` gives **11.36:1** and **13.81:1**, with green
links **8.04:1**. These are diagnostic fixture measurements, not actual-page
browser results or a deployed correction.

## Initial rendered diagnostic (incomplete)

Read-only `peek-search-render-inspect-1`, commit
`5c6660b6d5dc8fef0758a2cdb404d5e930f48353`, finished **INVALID** at
**05:57:56 UTC** in Actions **42968966786**. Its diagnostic reached the real
populated Search page and saved light/dark measurements and two captures, then
stopped before empty Search. There were zero assertions; it is not a browser
gate pass. The checker incorrectly required `data-ts-theme="light"` after the
return toggle, while the site's light mode removes that attribute. The actual
theme switch worked. The corrected diagnostic normalizes that existing state.

The downloaded 458,616-byte artifact matched SHA256
`7e6d70aca69e55236deb65132b26a758dfe71f017418659cfba23f51092be5af`
and ZIP integrity checks. Its partial observations refine the CSS proposal:
the outer result article is transparent and the white surface is on its nested
`.ast-article-inner`. The full-page image also shows the affected Search title
panel and selected pagination number outside the initial measurement root.
Actual result headings measured 1.13:1, excerpts 1.37:1, green links on white
1.94:1. The dark Search submit has white text on green at 1.94:1; its white
input's placeholder is 2.54:1 in both themes. These are individual observations
from an incomplete diagnostic, not verification of a correction. The expanded
Search-only fix and placeholder change were explained before implementation.

Corrected read-only `peek-search-render-inspect-2`, commit
`9f1a6e8a22b30411e8f7f3846274cb2b58ebf543`, completed the diagnostic at
**06:02:56 UTC** in Actions **42969567826**: populated/empty Search in both
themes, **four cases, two loads and four captures**. There are intentionally
zero contrast assertions in inspection mode; this is complete baseline
evidence, not a correction pass. The 822,485-byte archive matched SHA256
`c551b680df841a208ef6a2dc11fc611b252c4a36e3ea54ec499cd5e4593b6a42`
and ZIP checks. Full actual page/body HTML and measured painted surface owners
are retained in the artifact. The full report and measurements were read.

The actual surfaces are `.ast-archive-description`, `.ast-article-inner` and
`.no-results`. All are white in dark mode. The selected pagination number also
has white text on green at 1.94:1. Both themes' empty-result screenshot and
actual controls are considered separately: a submit element's computed text
color does not establish its icon's contrast when the rendered control is an
SVG magnifier. Root visually inspected the populated and empty dark captures.
The final candidate uses the existing panel/night colors for these specific
Search surfaces and controls; the placeholder uses the input's existing dark
text color. No further public baseline retry is needed.

## Proposed change and verification

The proposal was explained before implementation: use the existing dark panel
background on Search's affected surfaces, in a small marked Additional CSS
block. Scope it to dark mode and screen media so light mode and printing retain
their existing rules. Actual populated and empty Search markup will determine
the exact selectors before the payload is finalized. No Peek gestures, summary
words, destinations, page content or global palette tokens are changed.

The finalized payload is **536 bytes**, SHA256
`3225b279ae3d20adf696ac9ad6a16dc2be7c7c15c8fbe64ba59e5a74dc14e9e0`.
Appending exactly one LF and that payload produces **69,380 bytes**, SHA256
`b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b`.
The title, nested result-card and empty-result panels use `--ts-panel` only
in dark screen media. Placeholders use `#475569` in both themes. The populated
form's text button and selected page number use `--ts-night` in dark mode.
The empty form's existing magnifier and transparent submit input are preserved.

Cached actual-page replay passed **150/0 in each of Chromium and Firefox**:
populated/empty routes, three widths, both themes, 12 captures per engine.
Every request was fulfilled from captured HTML/assets or blocked; this is
fixture evidence, not a fresh WordPress copy or public deployment test.
Root reviewed both dark phone captures. The retained diagnostic failures were
checker errors: serialized generated controls needed rebuilding in the replay,
and transparent submit text had incorrectly been treated as painted text.
The corrected checker reports that text as N/A, retains its decorative shadow
in evidence, and measures the actual magnifier independently. It also checks
placeholder, hover, focus, print, light-mode scope and unchanged Peek bytes.

The installer model passed **89 checks**, deployment-wrapper doubles **37**,
and the rehearsal harness **130**. These cover one binary CSS compare-and-swap,
changed-state refusal, exact snapshot binding and backup failure stops. They
do not replace the fresh WordPress and off-site checks below.

The staging installer must bind the exact baseline and payload, retain its
actual private before-snapshot, refuse changed-state rollback, and verify exact
readback. A fresh staging39 copy with uploads must pass source, regulated-copy,
rendered Search, idempotence and exact recovery checks before any staging write.
Apply and rollback require a fresh DB export with verified encrypted off-site
readback; the actual snapshot must also be verified off-site before purge and
rendered verification. Public checks and final actual-snapshot verification
follow. INVALID evidence verifies nothing.

The release candidate now freezes the accepted quiet-navigation runtime and the
same finalized Search CSS. Its local PHP model passed **1,694 checks**, including
**138 injected write-boundary failures**; the evidence harness passed **162**.
Its 28 browser leaves across 13 phases have not run on a fresh first-install
copy. Its first-install rehearsal remains separate from
the completed in-place staging upgrade. The earlier automatic rejection of a
temporary live-data copy is not bypassed by this preparation; no new live-copy
request or live enablement has been dispatched.

## Current source and copy gates

Source commit `295d752f0a04455d594fc74b7a716bfa7971a0ee` is pushed only on the
feature branch. Local `check-all.sh` completed with 42 passing groups, six
Docker-bind-mount failures (source paths unavailable to the managed daemon),
and missing ShellCheck/ruff; it is not a full gate pass. Targeted pinned PHP
models used streamed files instead. Complete Actions Gates **42971422219**
passed **51/0** at **06:23:07 UTC**. The full job log was read, including PHP,
both ShellCheck gates, ruff, all new Search guards and existing wording/builders.
Rendered checks are separate and were not claimed by this offline run.

Rehearsal request `search-contrast-rehearse-1`, commit
`9a15043534a70fb39621493232c2f25eddf3a282`, runs in Actions **42971730830** on
a disposable staging39 copy with uploads. No server apply is requested.
Automatic approval initially rejected this staging copy, interpreting it as
unapproved external transfer. No command/commit occurred on that rejection.
After verifying the exact staging guard and citing Ryan's explicit instruction
to use Actions and rehearse a throwaway staging39 copy, approval accepted the
same scoped request. This does not override the separate live-copy rejection.

The fresh staging39 rehearsal completed **PASS at 06:24:29 UTC**. Root read the
operation report. The isolated copy used WordPress 7.1.4, a 685,148-byte compressed
database and 268 MB of source files. Initial populated/empty inspection completed
all four baseline views. Apply changed only **Additional CSS #29**. Repeat
preserved every fingerprint; rollback exactly restored A; reapply exactly
matched B. Home and client-page regulated-copy gates passed. No new PHP errors,
warnings, notices or deprecations occurred (the retained WP-CLI Colors warning
was already present before the change).

Applied-copy Chromium and Firefox each passed **150/0**, all 12 cases and
12 captures per engine, in 92 seconds combined. Actual CSS and the same private
snapshot were verified before and after the browsers. Post-browser rollback
again restored every A fingerprint, and final reapply restored every B
fingerprint, with no cache or ID exclusions. Each new apply used its own snapshot:
`search-contrast-before-20261111-062234-4fdc7818.json`,
`search-contrast-before-20261111-062240-1f960dd4.json`, and
`search-contrast-before-20261111-062424-ce54e992.json`. These paths belong only
to the destroyed copy and must never be used as staging recovery commands.

The retained rehearsal artifact is **10,955,265 bytes**, SHA256
`213b6f585b908431745e0b6b0f8aec79ebed8b2ab3276c9167d12e57a78e5280`;
download size/hash, ZIP CRC and safe extraction paths were verified. Root
visually reviewed the actual applied-copy populated and empty dark phone pages.
Independent audit validated complete raw streams: 150/0 per engine, all required
cases/captures, empty browser stderr and no INVALID evidence. All 28 JPEGs decode
(24 applied and four baseline). All **87 fingerprints** satisfy A=C=E and
B=B2=D=F exactly; only CSS #29 changes. Core #1380 and all four data records are
unchanged. The actual rendered minimum regular-text contrast is **8.04:1** and
field/placeholder contrast **7.26:1**. Full retained Home **35/0** and client-page
**322/0** logs pass. The 15 separate native stderr files contain only the known
WP-CLI Colors deprecation; original/applied PHP-error sets match.
Independent visual review covered all 24 applied full-page captures and four
baseline views, with no new clipping, collision or readability blocker. The
390-pixel light comparisons differ only at the intended placeholder text.

Read-only staging preflight `search-contrast-dry-1`, commit
`85381d9547ce89d7feb36c5b882cb6e79f3d8a2b`, passed in Actions **42972323425**
at **06:25:49 UTC**. It confirmed the exact original CSS hash and ready state;
the complete report was read. No WordPress apply occurred in this request.

## First staging apply stopped before mutation

`search-contrast-apply-1`, commit `47b96327b1a5d54ed8b3ec01c761b09681aaf9b1`,
Actions **42972709329**, finished **FAIL at 06:29:20 UTC**. The whole report was
read. Original CSS was still exact and ready. Fresh export
`staging39-before-search-contrast-20261111-062911-2323.sql`, **4,548,317 bytes**,
SHA256 `8581a3d5cc7eb0eacc5bcb3fb48fe008c2ec66f4305510e5b7edabb2a2b2e472`,
was copied with matching size/hash and verified encrypted off-site before apply.
The installer then refused its backup-directory prerequisite, before creating
any snapshot or writing CSS. The wrapper stopped on the missing snapshot marker;
no rendered or deployment pass is claimed. The pre-write state line confirms
the original CSS hash remained ready. This export is retained; a future apply
must take its own fresh verified export.

The original dry-run returned before checking this directory prerequisite.
The next source change moves that check into preflight and reports its exact
safe path predicates for a read-only diagnosis. A server path alias is only a
hypothesis until observed; no path guard is loosened or blind retry requested.

Diagnostic source `8ed0776ba` passed **95 PHP model checks**. Read-only request
`search-contrast-path-preflight-1`, commit
`3889be43cfa49ac366d6a437e84175c5d9ae0190`, Actions **42973176898**, failed its
strict preflight as intended at **06:33:05 UTC**, without a write. The full
report establishes the cause: the supplied account-home path resolves to
`/home/customer/private-backups`. Its final component is not a symlink; it is
a writable directory with mode **0700**, owned by UID/GID **3732**, matching
PHP's effective UID. The real web root is
`/home/customer/www/staging39.thaisolitude.com/public_html`. The only failed
predicate is the literal-versus-canonical path comparison. The wrapper will
pass the physical directory path; the installer's strict guard remains intact.

The wrapper now resolves that home ancestor with `pwd -P` and passes the
physical private directory to the unchanged installer guard. Recovery accepts
only the existing strict filename/path grammar, rejects directory/file leaf
symlinks, resolves only the parent and requires it to equal that physical
directory. Exports also use the physical directory. Real filesystem alias tests
and the existing backup/refusal tests passed **48/0**; the installer model passed
**98/0**, including dry-run refusal and canonicalized-path acceptance. Payload
and manifest bytes are unchanged. New source, dry-run and copy gates follow.

Correction source is `1e3dd69866cc2e41918c0ef0a898b218d584ed43`.
`search-contrast-dry-2`, commit `ef98506a897877a7e6a7dc49bbfbd98fab139ac9`,
Actions **42973687236**, passed **06:37:27 UTC**, now including the actual
private-directory prerequisite. Original CSS remained exact and ready. Source
Gates **42973687235** and fresh staging39 copy `search-contrast-rehearse-2`
(commit `ba94bb55a8c9bd19cb00594e3d7cb398f0b21c77`, Actions **42973788876**)
were started for the corrected source; staging apply remains blocked until both
pass and evidence is read.

Second rehearsal completed **PASS at 06:41:51 UTC**. Its full operation report
was read. Independent raw-evidence review again confirmed **150/0 per browser**,
all 12 cases, six loads and 12 captures per engine, with no INVALID/stderr.
All 87 fingerprints satisfy exact A=C=E and B=B2=D=F, only CSS #29 changes,
and all snapshot/readback bindings match. Home **35/0**, client **322/0**, and
unchanged PHP errors pass. All **24 applied JPEGs match the first copy byte for
byte**, so its full visual review applies without differences. The archive is
**10,955,276 bytes**, SHA256
`a1f27020f75f7de13712ba834548039f172aaf6856827bc1fdfa07fef514f210`,
with verified download size/hash, ZIP CRC and safe extraction. Root and the
independent reviewer confirmed the exact checked-out request commit.

Final corrected source Gates **42973687235** passed **51/0 at 06:43:38 UTC**.
The complete relevant log was read and checkout verified against
`ef98506a897877a7e6a7dc49bbfbd98fab139ac9`; all PHP, ShellCheck, Python, wording,
builder and recovery gates passed. The final staging apply may now proceed with
a new export; the earlier export is not reused.

## Actual staging deployment

`search-contrast-apply-2`, commit `9e48056834d2d8920a94b51cfbd5a79384d16b00`,
Actions **42974559394**, applied at **06:45:11 UTC** and completed its report
**PASS at 06:54:29 UTC**. The complete report was read. Exact CSS is now
**69,380 bytes**, SHA256
`b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b`.

Fresh actual export, verified on server, in the copied file and through
encrypted off-site readback **before the write**:
`staging39-before-search-contrast-20261111-064500-29620.sql`, **4,548,317 bytes**,
SHA256 `23ac801734d1f1f5c4f4839f9616aaf972f24fba1cf3cb14b99c8bab3092c798`.

Actual recovery snapshot, verified off-site **before purge/render**:
`/home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json`,
**141,536 bytes**, SHA256
`9da4ca7fe4542276ab8113ddf24dc06694527c52038afbe7c722d93e866a1af6`.
Use this actual file for Search verification/recovery, never either copy's
snapshot or an older Peek core snapshot. The installed manifest stays frozen.

Stored CSS matched before and after the required cache purge and HTTP200
Search/Home renders; both renders served the exact applied CSS. Automatic
post-change source/rendered/wording/posture gates, 12 HTTP responses, structured
data (27/27, zero parse errors) and PHP-error checks passed. The broad pixel
comparison is **not an aggregate pass**: all 20 views differ between staged
Peek navigation and the unchanged live site, as already recorded for that
separate accepted navigation release. Both sets had all 20 captures with zero
capture problems. No new non-Search content change is attributed to this CSS.

Automatic restore import exited **0**, comparing **104 fingerprints** on each
side: CSS1/options12/pages19/rows21/SEO21/snippets30. Its only difference was
**CSS #29**, exactly the intended change. Subsequent public and final
actual-snapshot results are recorded below.

Independent deployment review read the full report and complete **425-line**
job log, confirmed the exact checkout, and verified timestamp ordering:
DB off-site **06:45:07**, CSS apply **06:45:11**, snapshot off-site **06:45:17**,
purge **06:45:21**, exact Search/Home renders and stored recheck **06:45:22**.
No deployment blocker was found. The restore totals above come from its report;
the full row files are not retained there, so no second row-by-row computation
of those 104 fingerprints is claimed.

Public request `search-contrast-public-1`, commit
`2084fe258210bcec54cde527ce4cc9d7e80f0d13`, Actions **42975819331**, ran
the focused Chromium/Firefox checks without writing to WordPress.

## Completed public and final verification

That public request completed **PASS at 06:59:24 UTC**: Chromium **150/0** and
Firefox **150/0**, all 12 cases, six actual public loads and 12 captures per
engine. Total **300/0**, in 163 seconds. Populated and empty Search, 320/390/1280
widths, both themes, actual controls, print/light scope and preserved Peek code
were checked. The complete operation report was read. These automated browsers
do not add physical Safari or assistive-technology coverage; prior Safari
acceptance of the unchanged Peek navigation remains closed.

The public artifact is **7,419,725 bytes**, SHA256
`fbdd6a65b3b4fd75d3ca1cd6bd66074f13f8f81785c6de57813b6eb231747b22`;
download hash/size, ZIP CRC and extraction paths were verified.
Independent audit of the complete separate stdout/stderr streams confirmed the
exact 150 assertions per engine, all cases/captures, no INVALID evidence, and
served core/style hashes matching the checked-out source in every case. All
**24 public JPEGs are byte-identical to the reviewed second copy**. Minimum
regular-text and field/placeholder contrast remain **8.04:1** and **7.26:1**.
No extra public request or expanded test was needed for this artifact review.

Final read-only request `search-contrast-verify-1`, commit
`e03e75e39f321baad53d41ef48c0f0e1100cecd1`, Actions **42976293114**, passed at
**07:00:26 UTC**, after public browsers. The whole report was read. The actual
snapshot's path, SHA256 and 141,536-byte size matched before and after exact
Search/Home HTTP200 renders; stored and served CSS remained
`b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b`.

Recovery, only after checking this exact current state, uses
`search-contrast/push-search-contrast.sh --rollback /home/customer/private-backups/search-contrast-before-20261111-064511-fcba9b49.json`
through a **new staging39-only operations request**. The wrapper requires its
own fresh verified off-site export and exact snapshot/state checks. No rollback
has been requested on staging. Keep the deployed manifest immutable; do not
replace this snapshot with a copy-rehearsal file or guess the latest filename.

The release bundle now contains the accepted core, four approved data payloads
and this exact staged Search CSS. It still needs its own first-install,
rendered and recovery rehearsal. The concrete live-data-copy request and
permission boundary are recorded in the [release readiness record](2026-11-10-peek-release-readiness.md).
No live-data copy, live enablement, live request or main merge was performed
in this phase. Fluidity remains deferred until after live.

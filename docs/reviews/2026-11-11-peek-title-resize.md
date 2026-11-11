# Peek title-strip resize correction

**Preparing on the feature branch; no website change yet.** The approved
live-sourced temporary-copy rehearsal `peek-visitor-release-quiet-search-copy-3`
(Actions `42987385913`, source `ce634995807e2b024819e3fc0f995fa49dc7440d`)
failed on two WebKit page errors during Therapeutic's 150%-root-text checks.
The release remains on hold. Live and staging39 are unchanged by this work.

## Observed failure

Both right- and left-side cases reported
`ResizeObserver loop completed with undelivered notifications.` The underlying
WebKit layout leaf completed all 19 loads across eight routes: 150 passed,
two failed. Reading space, control visibility and research qualification
reachability passed. The right-side screenshot remains legible; no separate
left-side enlarged-text capture was retained. This is CSS root-text enlargement
in Linux WebKit, not physical Safari or OS text scaling.

The 15 retained core suites passed 14,319/0 and WebKit disclosures passed 70/0.
Only 17/28 release leaves ran. Nine neighbour/index/quiet leaves and both Search
leaves were skipped; no post-browser recovery proof exists. All 145 unique
JPEGs decoded successfully. All 16 Chromium layout captures are byte-identical
to the accepted staging quiet-navigation captures. The enlarged native branding
wrap is pre-existing, outside this narrow correction.

The same copy verified actual Spectra source binding, first installation,
repeat, exact rollback, unchanged metadata through all restored-page visits,
and reapplication. Cleanup verified deletion of the copy and all owned
resources. Full native bindings are in the release-readiness record.

## Proposed correction

Source inspection finds that the title-strip observer watches its own border
box, then synchronously changes the inner title window's height during the
notification. The proposed fix coalesces that observer's layout work into the
next animation frame. It retains the same measurements, title labels, visual
layout and gestures. This approach was explained before implementation.
Local WebKit reproduced the same error in all six original-runtime trials
(three per side). The queued callback passed all six corresponding trials:
zero page errors, no size changes during observer delivery, and identical final
geometry. The trace identifies the strip growing inside the original callback
after root-font enlargement. This is exact-runtime structural-fixture evidence,
not an actual WordPress-page or Safari result. Errors remain decisive in checks.

The unchanged focused checker was also run against both offline structural
fixtures: frozen v10 produced **44 passed / 4 failed**, with only the observer
errors failing; corrected source produced **48/0**. Both runs supplied all four
page/side cases and eight captures, accepted by the strict evidence parser.
All eight old/new screenshot pairs are byte-identical. Independent review
replayed that parsing and comparison. The checker covers Therapeutic and Work
With Me, both sides, three enlarge/restore cycles, shorter/restored viewport,
native menu open/close, exact route and side identity, and all page errors.

The only runtime change is the observer callback. Frozen v10 remains
110,787 bytes, SHA256
`4dfe97943898790b9c000f6dfcc129b4806183a23bc7cb7a55f1cc7db7172952`.
Corrected source is 111,148 bytes, SHA256
`708b91f4e52025b6813245a38e7c048fff09dd6ae90f644e0b2b5d1f415f7b19`;
stored-code SHA256 is
`f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566`.

Freeze the accepted quiet-navigation runtime as shared v10 and keep its
historical v9-to-v10 installer immutable. Use a separate `core-title-resize`
transition for the correction, preserving all four approved data snippets and
Additional CSS. Required sequence: source gates, guarded staging39-copy
rehearsal, fresh verified off-site backup, staging39 apply with its actual
snapshot, bounded public checks, then exact stored/rendered verification.
The provisional first-install payload must track the corrected source before
source Gates, preserving the existing exact source/payload check. Its status
remains unverified until staging and the complete temporary-copy release cycle
pass. Run that full release cycle after staging verification. No live enablement
or main merge is authorized here.

Ryan's accepted Safari checks remain complete. Do not request a generic
repeat or add deferred fluidity refinements to this correction.

## Source verification

The prepared staging lane passed **1,967 shared-installer model checks**,
**255 deployment controls**, **108 rehearsal controls**, and **56 browser
orchestration controls**. The existing decisive cleanup helper now also covers
this new lane: both fixed release and resize profiles passed all **51 cleanup
controls**. It requires exclusive directory ownership, successful removal and
verified absence of the owned relay, containers, volumes, networks and temporary
directory, including early setup failures. Cleanup failure cannot become a pass.
Historical cleanup paths remain unchanged. No copied SQL, private snapshots or
source files are retained as evidence.

Historical quiet deployment/rehearsal controls passed 190/107, and input-focus
deployment controls passed 157. Required Bash syntax, ShellCheck and diff checks
passed. The new wrapper preserves Additional CSS29 against its pre-write hash
through apply, purge and rendered verification; it never writes CSS. Summary
JSON payloads remain byte-identical. The release candidate's separate models
passed 1,843 checks including 138 injected write-boundary failures, plus 163
rehearsal-evidence controls. These are local source/model results, not website
verification. Full Actions Gates and the staging39-copy rehearsal follow.

Complete Actions source Gates passed **55/0** at **09:23:32 UTC**, run
**42993487769**, checkout `037fec5a6b95ecfcda02db98e8b2125326da9155`.
The entire job log was read. Rendered-page checks were intentionally not part of
that offline job; they remain required in the actual-copy operation.

The fresh staging39-sourced rehearsal with uploads was dispatched as
`peek-title-resize-copy-1`, Actions **42994882056**, source
`95f823413b04fa8f683d77e0a0f82087aac3fa7b`. It exercises the focused resize
matrix in three engines, retained WebKit layout/disclosures and the full
quiet-navigation rehearsal, followed by exact post-browser recovery and decisive
cleanup. Results are pending. Neither staging39 nor live has been changed.

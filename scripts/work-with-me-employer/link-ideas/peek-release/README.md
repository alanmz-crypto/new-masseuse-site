# Provisional visitor release: isolated live copy only

This package contains the accepted shared Peek runtime plus a provisional
title-strip observer timing correction, and four unchanged approved summaries.
The observer correction still requires staging39 verification. It is a first-install candidate for a
fresh copy of live, not authorization or tooling to change live. The installer
accepts only `http://localhost:8090`; staging and live origins are refused.
Ryan accepted the current controls on iPhone Safari on November 11: “It's
correct.” Fluidity refinements are deferred until after live; no repeat generic
Safari check is required. The fresh first-install and recovery gates remain
required. A prior live-data copy request was rejected before execution because
it exceeded the then-current staging39-only scope. On November 11 Ryan explicitly
approved the temporary GitHub Actions copy of live's database/site files/uploads,
installation and recovery testing, cleanup, and retained logs/screenshots. That
approval is recorded in the release-readiness notes; it does not enable or
authorize live writes or a main merge.
The copy's containers use an internal Docker network from first boot, so copied
plugins cannot call external integrations. An owned host TCP relay binds only
127.0.0.1:8090 and forwards unchanged bytes to port 80 of the exact inspected
WordPress container on that internal network. It stops during copy cleanup.
The host browser itself is not a network-isolated process.
Release cleanup is part of the result: it verifies removal of the owned relay,
project-labelled containers/volumes/network and exact temporary directory,
including early setup exits. Failure cannot produce a successful rehearsal.
The retained cleanup log contains fixed statuses only. The 51-case cleanup
fixture is included in the existing release-evidence gate; a separate synthetic
Docker probe verified actual removal while preserving unrelated resources.

The approved November 11 first-install attempt (`42981371762`) passed install,
repeat and immediate exact rollback, then failed after the 15 original-page
requests replaced 15 metadata rows. It stopped before reapply/browser checks;
cleanup verified deletion. `observe-render-meta.py` now brackets each original
page request with direct, read-only queries against the owned copied database,
without bootstrapping WordPress. Raw HEX travels only through a pipe. Retained
logs contain row IDs, key/value hashes and row hashes with/without metadata ID;
only the known `_uag_page_assets` key can be labelled. Exact localhost origin,
manifest inventory, capture binding and pipeline success are required. These
observations identify drift; they never excuse it or rebase installer guards.

Copy2 confirmed that all 15 replaced rows were `_uag_page_assets`, including
changed values. The candidate now uses Spectra's documented skip-post-types
filter only around its own WPCode factory save, code correction and publication.
It appends `wpcode` to existing exclusions and removes the exact callback in
`finally`; no persistent filter or general hook removal is installed. The whole
`__uagb_asset_version` option row is now protected along with every original
metadata row. Neither cached values nor row IDs are normalized or restored.

Before any write, `source-binding.php` requires the shipped Spectra 2.20.6 header,
six exact file hashes, loaded class identities and the expected save hook. The
official WordPress.org ZIP (SHA256
`d6ec81341a6e8351db21497f213e40f08f2f8bf8300098cd04fc94800a980caf`)
matches the reviewed GitHub commit's files byte for byte. The manifest records
those bindings. A mismatch refuses the copy operation rather than guessing.
The separate deferred asset callback is reviewed/model-tested; copied WP-Cron
is disabled, so the browser rehearsal does not exercise cron. Frozen runtime,
summaries and CSS remain unchanged. Copy3 (`42987385913`) verified those source
bindings and passed installation, repeat, exact rollback, restored-page visits
with unchanged metadata, and reapplication. It remains **FAIL**: retained core
browsers passed 14,319/0 and WebKit disclosures 70/0, but WebKit enlarged-text
Therapeutic layout reported two ResizeObserver errors (150/2). Only 17/28 leaf
suites ran; post-browser recovery was skipped. Verified cleanup deleted the copy.
Investigate these errors before another full release proof; live is unchanged.

The release creates five WPCode snippets, removes the exact old reading-progress
CSS block and appends the frozen Search contrast block in one guarded update to
Additional CSS #29, and changes Astra's `scroll-to-top-enable` boolean to false.
It preserves jump snippet #1232 and section-guide #1338. The
runtime hides the guide only after initialization. Existing page content,
metadata, dates, SEO and route identities remain unchanged. The four experimental
navigation pages are absent from the reviewed 15-page visitor inventory.

## Source and copied baseline

The November 11 provisional candidate includes the title-strip observer fix:
111148 file bytes, SHA256
`708b91f4e52025b6813245a38e7c048fff09dd6ae90f644e0b2b5d1f415f7b19`;
111142 stored-code bytes, SHA256
`f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566`.
The accepted preceding runtime remains immutable as `peek-nav-shared-v10.php`.
The corrected source passed local causal tests and independent review; it has
not yet passed actual staging or the complete release cycle.
The four approved data payloads are unchanged. This includes immediate summary
neighbours, small-screen index clearance and the four quiet-navigation changes.
The prior payload and its historical evidence remain in git; they cannot verify
this refreshed first-install candidate. The separate mandatory Search contrast
payload is the exact 536-byte correction now verified on staging39, SHA256
`3225b279ae3d20adf696ac9ad6a16dc2be7c7c15c8fbe64ba59e5a74dc14e9e0`.
It changes only screen styles for Search results/no-results panels, placeholders,
the populated form's text button and current pagination. Search's separate
staging copy and public checks each passed 300/0, with final actual-snapshot
verification at 07:00:26 UTC on November 11. Its inclusion in this release still
needs the fresh first-install and rendered-copy gates; in-place staging proof
does not replace those.

`manifest.json` records the actual read-only live capture, the 15 route identities,
all payload hashes and exact baseline hashes. The capture was operation
`peek-live-navigation-baseline-1`, run `42741958557`, source
`9c6e090417c83164b457be16e5dcbd9ac4f6bb8b`, completed 2026-11-10 02:19:48 UTC.
The decoded baseline was 2,047,975 bytes, SHA-256
`a6c6e923d484576d96060ab8d9f84110c5d785f9fec64c5ae7e5b0ef3f37f013`.
Private raw option/source content is not duplicated in the repository.

The copy profile follows the existing four precise WP-CLI replacements from live
HTTP/HTTPS and escaped origins to localhost, excluding GUID. Ordinary reviewed
post fields are mapped literally. The serialized Astra row was mapped using the
actual `WP_CLI\SearchReplacer` in the repository-pinned CLI image, with the same
exact-byte row-selection condition and nested-serialization handling. The four
step hashes are recorded. A simple serialize/unserialize round trip is **not**
byte-preserving for the actual Astra option and is never used to write it.

The progress CSS payload includes the original 1,093-byte block plus exactly one
following LF (1,094 bytes total). Removing it produces the 68,843-byte staging
baseline, SHA256
`2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae`.
The release appends exactly one LF and the entire Search payload, without
trimming either input, for 69,380 bytes, SHA256
`b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b`.
Rollback restores the whole original CSS record; it does not reconstruct it.
Astra changes only the unique serialized top-level boolean token. Decoding before
and after proves that only that boolean changes; all other raw bytes and autoload
are preserved.

Four source service-page full raw posts are pinned to the reviewed copy mapping.
Their unknown metadata values are not copied into the manifest or snapshot. The
installer captures actual copied metadata/relationship hashes after normal page
warming and requires those hashes to remain identical thereafter. It does not
claim that hash-only live metadata can be mapped without its original values.

## Interface

Run through WordPress CLI as the administrator, with `WWM_BACKUP_DIR` set to an
existing absolute private directory outside the webroot:

```text
wp eval-file install-peek-release.php preflight
wp eval-file install-peek-release.php apply
wp eval-file install-peek-release.php apply /backups/peek-release-before-....json
wp eval-file install-peek-release.php verify /backups/peek-release-before-....json
wp eval-file install-peek-release.php rollback /backups/peek-release-before-....json
wp eval-file install-peek-release.php verify /backups/peek-release-before-....json original
```

`preflight` is read-only and requires the exact original, absent-Peek baseline.
The rehearsal warms all 15 original routes, compares two complete preflight
markers around a second warm pass, and only then captures fingerprint A. Unstable
read-side metadata is a failure, not permission to weaken or rebase the snapshot.

A first `apply` creates a private 0600 journal before writes, using atomic
no-overwrite publication. Later journal replacements require this invocation’s
previously verified bytes and permissions to remain exact. Each snippet is
created inactive and checked in full. The actual second copy rehearsal
(run `42751049392`, completed 2026-11-10 03:43:48 UTC) showed the first factory
draft had exactly auto-insert, priority and note metadata, with correct PHP and
everywhere relationships; it omitted `_wpcode_shortcode_attributes`. The initial
three-key profile is therefore journalled explicitly. A unique `add_post_meta`
then creates the approved empty-array attributes and must return its exact new
metadata ID. The old three raw rows, post, dates, relationships and all other
protected state must remain unchanged. Four exact metadata keys are required
before code correction, any later role, cleanup or activation. An unexplained
four-key initial factory record is refused rather than silently adopted.
WPCode's initial unslashing is corrected only after this inactive initialization. Data snippets activate first; the core activates last. Original
jump links and the guide remain available throughout. The installer never reads
remote JSON and changes only WordPress database storage.

Copy5 (run `42756230445`, completed 2026-11-10 04:36:13 UTC) accepted the first
factory record and its attribute initialization. Its first code correction then
changed the new GUID from the exact raw `&p=ID` form to `&#038;p=ID`; that was the
only unexpected field in the owned-save comparison. Matching official WordPress
7.1.4 source explains it: [post updates reload the GUID in display context](https://github.com/WordPress/WordPress/blob/7.1.4/wp-includes/post.php#L4653),
whose [default filter is `esc_url`](https://github.com/WordPress/WordPress/blob/7.1.4/wp-includes/default-filters.php#L91),
which [uses this numeric entity](https://github.com/WordPress/WordPress/blob/7.1.4/wp-includes/formatting.php#L4585).
Initial insertion writes the raw permalink directly. The installer now predicts
only those two exact home/ID-bound constants: factory creation and attribute
initialization require raw; code correction and activation require the entity
form, including repeated saves. It never accepts arbitrary decoded-equivalent
GUIDs or uses a current filter result as its acceptance rule.

An incomplete snapshot's three-key factory or uncorrected-PHP draft requires the
raw GUID; a published record requires the entity form. A four-key draft with
final PHP can represent either skipped correction (the factory already preserved
the code) or completed correction, so that one snapshot phase permits the two
exact forms. Current state must still equal the entire recorded snapshot.
Actual activation and later saves remain to be proved by the fresh-copy run.

`apply FILE` is a strict read-only repeat. `verify FILE [original]` uses the same
complete snapshot validation and performs no database, cache or file writes.
A snapshot-free apply refuses existing Peek records, including drafts and trash.
Independent reapplication after rollback creates new IDs and a new snapshot.

Rollback removes only the snapshot's five exact owned records, restores the old
CSS/Astra stores, and verifies the complete original state. Every reverse prefix
has an exact expected state, allowing a retry after a known interrupted step.
A valid incomplete journal also supports `rollback FILE`, after checking the
recorded creation/attribute-initialization/correction/cleanup/activation order and
every full known record. A three-key factory record is allowed only as the last
created inactive role; it cannot precede another role or any cleanup/publication.
It emits no completed-release marker. If interruption occurs after a write but
before its full state was journalled, that unrecorded state is refused and needs
inspection; the installer never guesses ownership to delete it. This includes
an attribute insert that throws or returns false/invalid data before returning
its new metadata ID: even a matching empty-array row is retained as unrecorded
state. A before-write failure can recover the known three-key draft; a later
failure after a returned-ID, verified four-key journal can recover that state. Unknown edits
or foreign/reoccupied IDs stop the operation. It does not recreate
deleted snippets or overwrite unknown state. A rollback retry may regenerate the
derived WPCode cache even when stores are already original, so it can recover a
prior failed cache refresh. Completed snapshots remain byte-for-byte immutable.

## Exact guards and limitations

Captures use ordered raw SQL rows, bypassing WordPress object/query caches and
virtual fields. Full selected post/meta/relationship records, selected option
rows, and each owned record are retained. Other posts, metadata, relationships,
terms and term metadata are represented by per-row hashes. Unknown metadata
values are not logged. All currently existing content and metadata witnesses stay
exact; only the five new owned IDs are excluded from those original witnesses.
Rejected newly owned records emit counts and typed value hashes, hashed unknown
metadata names, and numeric relationship fields to identify storage mismatches.
That diagnostic never authorizes adoption or deletion of unexpected state.
Full-state equality failures also emit `RELEASE-STATE-DIAGNOSTIC` from the same
captured values: the exact comparison phase, changed paths, presence/type/length
and typed hashes. Only known structural names and numeric IDs appear in paths;
unknown key names are hashed. Missing values, nulls and list/map shapes remain
distinct. Output is bounded to 32 changes, eight path levels and 2,048 scanned
child positions; a truncated branch is represented by hashes. The original
comparison and refusal remain unchanged, with no extra database reads or writes.
Copy3's aggregate hashes could not identify its first-draft full-state mismatch.
Copy4 (run `42754978423`, completed 2026-11-10 04:20:06 UTC) identified exactly
two changed fields, with no truncation: shared WPCode taxonomy rows #9 and #10
each changed from count 17 to 18 on the first inactive draft. All other captured
state matched the expected creation. The old published-only count assumption
was therefore wrong.

The expected shared count is now the original count plus every surviving owned
record, whether draft or published. Each has the two exact reviewed relationships.
The installer requires activation to preserve those counts and deletion to
reduce them by one, returning to 17 after full rollback. Those later transitions
remain to be verified on the actual fresh copy; copy4 stopped before attribute
initialization, activation or deletion. All other fields and relationships remain
exact. Counts are not normalized away or accepted from the current state.
CSS and Astra use prepared binary compare-and-swap updates and require exactly
one changed row. Existing posts' dates, metadata and revision history are not
rewritten. New-snippet save calls permit only their documented content/status,
deterministic first published slug/GMT date, and actual call-time modified dates.
The API-only `edit_date` flag prevents WordPress from resetting a draft’s local
creation date during correction or activation.

The derived `wpcode_snippets` cache is regenerated, not restored from obsolete
raw bytes. It is excluded from protected options. The rehearsal must therefore
also render all original routes after rollback and verify that Peek is absent
and the original jump/guide behavior is present. Unrelated options are outside
this scoped snapshot and are never intentionally written by this installer.

Offline tests model WordPress/WPCode APIs against isolated SQLite tables and test
real prepared-query/capture behavior, fault recovery and refusal paths. They do
not prove actual WPCode hooks, MySQL affected-row semantics or browser behavior.
Those require the fresh live-sourced copy with uploads and retained operation
reports. No successful local fixture result authorizes a live release.

## Machine evidence

The browser adapter requires the exact current runtime/data bytes and fresh
15-page visitor inventory, plus search and 404. It retains the original 15
visitor suites and two supplemental WebKit suites, then runs the existing
neighbour, index-clearance and quiet-navigation checks in Chromium, Firefox
and WebKit, plus Search contrast in Chromium and Firefox: **28 unique leaves
across 13 phases**. Search uses the same pure evidence parser as its staging
gate, requiring the full result/no-result, theme/width and return-to-light
matrix. The newer staging aggregate
guards remain unchanged; this release-owned adapter invokes their focused
checkers with complete literal assertion, case, load and capture checks. Every
child stdout/stderr stream is retained. Failed or incomplete evidence stops
later phases. Linux WebKit is engine evidence, not a physical Safari claim.

Successful preflight emits one `RELEASE-PREFLIGHT` JSON line with schema, bundle,
home, manifest SHA, ready state and complete protected-state SHA. The rehearsal
requires exact equality around its second original-page warm pass.

Successful apply, repeat, verify and rollback emit one `RELEASE-SNAPSHOT` JSON
line: schema, bundle, home, applied/original state, exact canonical snapshot path,
snapshot SHA, manifest SHA, current state SHA, and the five role-to-ID bindings.
No success marker is emitted for a partial or refused state. Snapshot path/hash
and IDs remain identical when verifying or restoring original state. The parent
rehearsal reads these markers and never guesses the newest snapshot or reads a
private snapshot from the host. Across independent installs it may normalize
only these five bound IDs in its generic fingerprint; full per-cycle verification
remains the installer's responsibility.

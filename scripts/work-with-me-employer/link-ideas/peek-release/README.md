# Provisional visitor release: isolated live copy only

This package freezes the shared Peek runtime and four approved summary payloads
already verified in WordPress on staging39. It is a first-install candidate for a
fresh copy of live, not authorization or tooling to change live. The installer
accepts only `http://localhost:8090`; staging and live origins are refused.
Actual iPhone Safari acceptance and the fresh-copy gates remain release blockers.
The copy's containers use an internal Docker network from first boot, so copied
plugins cannot call external integrations. An owned host TCP relay binds only
127.0.0.1:8090 and forwards unchanged bytes to port 80 of the exact inspected
WordPress container on that internal network. It stops during copy cleanup.
The host browser itself is not a network-isolated process.

The release creates five WPCode snippets, removes the exact old reading-progress
CSS block from Additional CSS #29, and changes Astra's `scroll-to-top-enable`
boolean to false. It preserves jump snippet #1232 and section-guide #1338. The
runtime hides the guide only after initialization. Existing page content,
metadata, dates, SEO and route identities remain unchanged. The four experimental
navigation pages are absent from the reviewed 15-page visitor inventory.

## Source and copied baseline

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

The CSS payload includes the original 1,093-byte block plus exactly one following
LF (1,094 bytes total). Removing it produces the exact staged CSS bytes, SHA-256
`2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae`.
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

# Dark Search result-card contrast

This package appends one marked Additional CSS block to Astra CSS record #29.
On screen, dark-mode Search result cards, the Search heading panel and the
empty-result panel receive the existing `--ts-panel` background. Search-field
placeholder text gains readable contrast in both themes. The dark populated
Search form's text button and the current pagination marker use `--ts-night`
text against their existing green background. The empty form's magnifier/hidden
submit text is left intact. Print styling, page content, Peek runtime, summaries
and navigation behavior are unchanged. The browser gates must establish the
actual selector and contrast; source/model checks alone do not establish the
rendered result.

The installer accepts only exact `http://localhost:8090` in a local environment
and staging39's HTTP/HTTPS origin. Live is refused. `manifest.json` pins the
actual 68,843-byte staging CSS hash and the reviewed payload hash. Its CSS has
no staging-origin URLs, so the isolated mapped copy has the same baseline.
Every pre-existing CSS byte is preserved, including trailing whitespace.

## Operation and recovery

`install-search-contrast.php` supports `preflight`, `apply`, `rollback FILE`
and read-only `verify FILE [original]`. Apply creates an exclusive private
0600 before-snapshot outside the web root, then saves the CSS using one binary
compare-and-swap against its exact previous content and record identity. Only
`post_content` changes; dates, other fields and revision history stay unchanged.
It clears the post cache and reads back the exact result. A concurrent edit
causes the compare-and-swap to refuse. Failure recovery can restore only the
exact bytes this invocation saved, through the same atomic check.
Repeat apply is a verified no-op. Recovery checks the exact original/applied
CSS, manifest, origin, record identity and snapshot hashes. Changed CSS, foreign
snapshots, symlinks, public snapshot permissions or ambiguous paths are refused.
Rollback restores the exact previous CSS, with no partial block removal or
adoption of unrelated later edits. Reapply creates a distinct snapshot.

An immutable `SEARCH-SNAPSHOT PATH SHA256 BYTES` marker is emitted before the
apply write and after verification of a supplied snapshot. The successful apply
line must name that same snapshot. Rehearsal and deployment bind to this actual
file; neither may choose the newest file in a directory.

`push-search-contrast.sh` is dry by default and accepts `--apply`, `--verify FILE`
or `--rollback FILE`, with `S_TARGET=staging39` only. Apply and rollback export a
fresh database, copy that exact export, compare its size/hash against the server,
and require the configured encrypted off-site hook to verify its readback before
writing. The actual apply snapshot is copied and verified off-site even when the
apply reports failure, before any purge/render. Rollback re-verifies both its
fresh export and actual recovery snapshot off-site before mutation.

Cache purge is required. Search and Home must return HTTP200 and contain the
exact snapshot CSS (only surrounding `<style>` framing whitespace is ignored),
with stored-state verification before and after those renders. HTTP202 or a
missing/different style is a failed verification, never a pass. The full
rendered browser and regulated-copy gates belong on the fresh staging39 copy
before an actual staging write. The final browser run and actual-snapshot check
follow deployment. Record actual backup/snapshot paths after the operation.

## Source checks

`test-installer.php` runs a WordPress API model covering exact byte preservation,
snapshot ownership and path guards, CSS drift refusal, save failures and exact
rollback. `test-push.py` executes the real Bash wrapper with local SSH/off-site
command doubles to check backup ordering, fail-closed markers and failure stops.
Neither replaces the actual WordPress rehearsal or encrypted off-site evidence.
Tests for the rehearsal integration and rendered browsers are separate files.

No remote plugin/theme file, workflow dispatch or live permission is added by
this package. The root release record owns deployment status and user approval.

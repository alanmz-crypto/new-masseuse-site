# Reviewed enable-live proposal, applied after release go

November 11: the staging and complete live-copy proof passed; Ryan then replied
“Continue” to the explicit release-go question. The exact reviewed patch is now
applied. Required source checks, read-only live preflight and PR/request
preparation follow; no live installation has run. Ryan retains the merge and
manual LIVE start. The original proposal description below is historical.


The companion patch is preparation only. It adds no operation request and does
not authorize live writes. Before it is applied, the active installer remains
localhost-only. The refreshed runtime still needs its staging deployment and
complete fresh live-copy proof, including post-browser recovery. The patch must
be rechecked against that exact successful source before release approval.

Once separately reviewed and explicitly enabled, the additional installer
profile is selected only by `TS_PEEK_RELEASE_PROFILE=raw-live-v1` on the exact
stored origin `http://thaisolitude.com`. Public responses use HTTPS. Neither an
HTTPS stored origin, staging origin nor a copied-site profile is substituted.
`live-profile.json` contains only public provenance and raw-row hashes derived
from the November 10 02:19:48 read-only live baseline. Serialized Astra bytes are
changed at the single reviewed boolean token; the profile does not reserialize
or remap live values. Any later mismatch stops the preflight; it is not rebased.
The original copied manifest and Spectra source bindings are unchanged.

The dedicated `../push-peek-release.sh` first-install wrapper requires a fresh
exact database export and successful encrypted off-site readback before writes.
It chooses an unused private journal path before apply and retrieves that exact
file even if apply fails or supplies no completion marker. Existing local
backup bytes are never replaced. Both successful installation and recovery bind
the private file's exact SHA, actual role IDs, manifest and raw-profile hashes.
The preserved SHA is passed back into the installer before snapshot-dependent
writes, so replacement between backup and rollback is refused. Raw-live snapshot
write modes require that hash even when invoked outside the wrapper.

`recovery-check FILE [original]` is read-only and accepts only exact known reverse
prefixes. It supports the initial empty journal, partial installation, interrupted
rollback and completed installation. Rollback does not require snapshot-free
preflight or completed-apply verification. Unknown edits refuse recovery rather
than adopting IDs or reconstructing state. After mutation, a decisive purge,
all 15 public routes plus Search/404, exact page identity, runtime/data/CSS response checks and
another exact state check must succeed. These response checks supplement the
full browser rehearsal; they do not replace browser or Safari evidence.

Offline proposal checks (synthetic data, no live access):

```sh
php peek-release/test-installer.php peek-release
python3 peek-release/test-push.py
php peek-release/test-render.php
```

The installer test uses PDO SQLite and modeled WordPress APIs; wrapper tests use
local SSH/offsite command doubles, including executing the actual remote phase
case body for purge/render/final-state failures. These tests do not establish real
SSH deployment, encrypted-store availability or live first-install behavior.

The existing localhost marker and snapshot field sets remain unchanged. The
`local-marker-contract` PHP model mode emits actual preflight/apply/rollback
markers at `/backups` in a disposable offline container. Run
`python3 peek-release/test-local-markers.py EMITTED_DIRECTORY` against its emitted
logs and fixture manifest to exercise the unchanged rehearsal parser; no marker
fields or paths are rewritten. Do not run the emitting mode on a website host.

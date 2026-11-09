# Peek input and keyboard focus follow-up

**In progress; no website write in this phase yet.** Ryan said “Continue” after
the completed Kiro fixes and the named next lane of older Thai/practice-page
focus issues. Work stays on `claude/wizardly-bell-xu0rta`, staging39 only.
The [previous Kiro fix record](2026-11-09-peek-kiro-fixes.md) remains the deployed
baseline until the actual apply records below establish a new state.

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
The candidate practice page has 37,595 source bytes, SHA256
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

## Actual applies and recovery

No apply or new staging recovery snapshot is recorded yet. Never substitute a
throwaway-copy snapshot or an older core snapshot. The next update must record
the actual guarded apply, fresh verified encrypted off-site backup, exact
recovery snapshot, restored fingerprints and public/stored verification.

## Limits

Native input is browser emulation. Physical devices, true OS text scale and real
assistive technology remain unverified. Any Linux WebKit result is supplemental
engine evidence, not a Safari or iOS claim. Previously recorded transient-label
overlaps, enlarged-text branding and jump-engine consolidation remain separate.
The official WebKit 26.5/v2336 binary downloaded, but launch exited 2 because
GTK4, Graphene, Harfbuzz ICU, Manette, Hyphen and GLES libraries are missing.
This executor has neither sudo nor the managed package helper. Zero WebKit
pages or assertions ran; Chromium and Firefox evidence cannot close that gap.

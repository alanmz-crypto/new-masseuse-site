# Peek: clear the initial title strip

Scope: the shared preview-card placement on staging39 only, on branch
`claude/wizardly-bell-xu0rta`. Ryan's “Next step” continues the explicitly recorded
first-card/title-strip correction. Approved summaries, page data, gestures,
destination landing and other-page eligibility are outside this change.

## Starting evidence and access

The branch was pulled at `1a73c318a5d17fe78aec775657eb47666aa069c3`, clean and
already current. Project instructions and the recorded implementation were
unchanged from the preceding phase; the placement runtime, installer, browser
checks and operation instructions were reviewed for this correction.

GitHub read/write access is confirmed. The managed environment reports current,
enforced unrestricted network policy. A fresh staging39 request returns HTTP202
with `sg-captcha: challenge`; direct browsing supplies no verification. The
established Actions route provides actual rendered pages and staging operations.

[Fresh staging ownership inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42569542165),
request `peek-clearance-access-20261109-1`, finished 05:50:42 UTC, confirms shared
core **#1380**, 66,253 bytes, SHA256
`dda8aecd38e417ad37766d2df951c6bdc761bf44997e030bbef5a36e9321f573`.
Data **#1381–1384** retain the hashes from the
[completed service rollout](2026-11-09-peek-service-rollout.md).

## Observation and proposed correction

The preceding actual Chair captures show its first preview overlapping the
display-only strip at page top; the tablet focus ring also grazes the Corporate
CTA. Its six section choices and card controls remain visible. Three reviewers
confirmed the visual defect. The current browser assertion repeats the runtime's
eventual sticky-position formula, so its passing result does not establish
clearance from the strip's initial rendered rectangle.

The source also leaves an already-open card at its old position while scrolling
back toward the top. Resizing re-centers using the whole card's midpoint rather
than preserving the open card's position.

The [fresh public baseline](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42570858557),
source `14813aa22efa4c6312a06d0b09589b0d93032032`, finished 06:05:49 UTC:
**326 passed, 16 failed, zero INVALID**, with 12 actual Thai/Chair captures at
390/768/1280. All 16 failures are measured strip/card overlap. This is an expected
diagnostic failure, not a passing release gate. Root and an independent reviewer
viewed the actual captures: Thai's first card hides much of the phone strip and
the tablet Corporate CTA, and also overlaps during the tablet/desktop sticky
transition. Chair's top/carry-back overlap is confirmed. Research cards clear the
strip. This supports a shared geometric correction rather than a Chair-only offset.

The correction was explained before implementation: use one visible boundary for
both fitting and placement, with the existing 8px gap for the focus ring; re-clamp
kept cards as that boundary or the viewport changes, preserving their DOM and
reading state. Destination jumps retain their eventual sticky offset. Ordinary
viewports should retain the complete stack; the existing compact layout and
scrollable-card fallback handle tighter space. No page scroll is introduced to
make room for a preview.

Local structural tests then showed a short landscape viewport can leave almost
no usable room below the initial strip. Before extending the implementation, the
user was told that a card may use the larger free interval above the unpinned
strip when even the most compact below-strip layout overflows. That interval
clears the actual visible header, including a nonsticky header still on screen.
Ordinary screens keep the below-strip placement. Long research words may wrap
at enlarged text sizes; no approved words change. Actual-copy verification of
these constrained cases remains required.

A kept, focused research summary also needs its outer card scrollbar adjusted
after rotation when the summary would become clipped. This adjustment preserves
the summary's own scroll position, its DOM and focus, and never scrolls the page.

## Upgrade and recovery design

The deployed shared version is frozen byte for byte as `peek-nav-shared-v1.php`:
file SHA256 `5941becfb3ff70e7359ae61a7c4a2cdd7e0a5b88869e58a5c2fd790bd5824cc3`.
Its code hash is the #1380 hash above. The original `core` migration remains tied
to that frozen payload so historical snapshot meaning is preserved.

An explicit `core-upgrade` operation updates #1380 in place from that exact shared
version. It snapshots complete old/new states and binds the four unchanged data
records. It refuses changed or foreign state and uses the existing verified
encrypted off-site backup sequence. Its separate rehearsal adapter has no
preparation hook: the baseline must be the real four-page staging state.
There is no snippet swap or planned fallback-only interval. Actual upgrade
snapshots will be recorded only after the guarded write.

## Verification in progress

The independent regression matrix will compare actual strip/header/card rectangles
at page top, through the sticky transition, after returning to the top with a
kept card, and after resizing. It will check the actual 5/6/5/6 section structures,
both sides, ordinary and constrained viewports, intact summaries and reachable
research endings. A pre-fix baseline must demonstrate that the new assertion
detects the observed defect.

Required before staging: offline gates, a fresh staging39-copy rehearsal with
uploads, rendered regulated-copy gates, exact rollback/reapply, source review and
visual review of actual copied pages. Required afterward: all report sections,
backup restore proof, public browser/capture review and stored-hash inspection.
No new staging website write has occurred yet. Main/live remain unchanged.

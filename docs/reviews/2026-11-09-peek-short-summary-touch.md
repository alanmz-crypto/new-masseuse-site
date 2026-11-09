# Peek: contain swipes on short open summaries

Staging39 only, branch `claude/wizardly-bell-xu0rta`. Ryan reports that closed
topic buttons now contain swipes and long summaries correctly scroll their own
text, but an open summary without enough text to scroll still pans the page.
This correction preserves those working paths and the approved wording/layout.

## Access and starting state

The clean branch was pulled at `569f9ce958d15e535123cf5fca98796f8762c81c` and was
already current. GitHub read/write access is confirmed. The current managed
environment reports enforced unrestricted networking, but a fresh direct
staging39 request returns HTTP202 with `sg-captcha: challenge`. No direct browser
verification is claimed; the guarded Actions route remains available.

[Fresh read-only inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42579762288),
source `0e1903348fb9a037f7c0420236066d944a4d614c`, finished **07:30:46 UTC, PASS**.
Core #1380 is the previously deployed **71,321-byte** payload, code SHA256
`357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8`.
All four data snippets retain their hashes from the
[preceding correction record](2026-11-09-peek-clearance.md).

## Diagnosis and announced behavior

The source gives every summary native vertical panning permission, including
summaries without a scroll range. The outer card also permits panning when it
has no overflow. Containment alone does not reliably reserve those gestures for
an area that cannot scroll. This matches Ryan's distinction; it is not a claimed
local reproduction. Native Chromium probes of the immutable deployed payload
kept pageY600 unchanged in **12 short-summary swipes** across Thai/Chair, both
sides and upward/downward/horizontal directions. The expanded baseline then
covered **48 swipes** across summary text, title, padding and hint, with no changed
page/preview/scroll state. The reported leak remains unreproduced in Chromium.

Before building, Ryan was told that short summaries would absorb swipes, long
summaries would keep their native scrolling, and title/padding surfaces would be
checked for the same leak. No wording, geometry, rail/index gesture or destination
change is proposed.

The implementation gives the card and its inner summary independent measured
pan permission. Both default to `touch-action:none`; `can-scroll` enables `pan-y`
only when that element's own scroll range exceeds the rounding tolerance. The
existing show/refit/hint update measures this before the next gesture. The flag
is independent of `has-more`, so reaching the end does not disable scrolling
back up. The title/padding can scroll the outer card only in its existing
constrained fallback. Closed controls retain their established behavior.

Independent native Chromium probes confirmed that a long summary can still
scroll inside a nonpanning outer card, and a constrained outer card still scrolls
from its title without moving the page. No extra touch listener or observer was
needed. These are browser simulations, not Safari or physical-device validation.

Candidate: **71,826 code bytes**, SHA256
`a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac`.

## Exact update and recovery chain

The currently deployed core is frozen as `peek-nav-shared-v2.php`, file SHA256
`a270f074640714b8524a60c13971766898bb7d7c82f36c2148de1fd9ebe2c8b4`.
The original `core-upgrade` remains exactly shared v1→that frozen v2. This keeps
the meaning of actual snapshot
`peek-template-core-upgrade-before-20261109-071122-958bff3b.json` unchanged.

New component `core-summary-touch` updates exact v2→the current core in place,
binding all four unchanged approved data records and complete old/new snippet
states. Changed or foreign state refuses. It uses a separate snapshot bundle,
the existing fresh encrypted off-site backup sequence and a localhost-only
rehearsal adapter with no preparation hook. Its browser hook imports the existing
full suite map; the same narrowly scoped 900-second allowance and complete-log
retention apply. There is no snippet swap or fallback-only interval.

Recovery must reverse `core-summary-touch` with its own actual snapshot before
any separate v2→v1 `core-upgrade` reversal. The new snapshot will be recorded
after the guarded staging write; no guessed or old rollback command is used.

Local installer checks passed **256/0**, retaining all prior 184 checks and adding
the new exact transition, full reverse chain, drift/snapshot refusal and failure
recovery cases. Current-page JSON emission checks passed **10/0**.

## Verification in progress

The final local native Chromium suite passed **266/0** in 127 seconds, with no
INVALID result and all prior 126 checks retained. Its 140 additional assertions
cover the four nonoverflow surfaces, three directions, both sides, the same
summary gaining/losing overflow on resize, short→research scrolling and long
summaries at their bottom. Computed-policy assertions are labeled separately
from native behavioral checks. This is not a claimed native red→green reproduction.
The independent source reviewer found no blocker in the 17-line runtime diff.

Required before staging: final offline gates, fresh staging39-copy rehearsal with
uploads, rendered wording gates, exact rollback/reapply, native short/long touch
checks and visual comparison. Required afterward: every apply/backup/restore
report section, public browser attempt and exact stored-core/data inspection.
No staging website write for this correction has occurred yet. Main/live remain
unchanged.

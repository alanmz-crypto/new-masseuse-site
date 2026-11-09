# Peek: contain swipes on short open summaries

**Deployed to staging39 at 07:59:57 UTC, 2026-11-09.** Shared core #1380 is
updated in place; the four approved data records are unchanged. Fresh-copy,
rollback and final public browser gates passed. Exact stored hashes are verified.
The reported leak was not reproduced in Chromium; physical-device resolution is
not claimed.

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
any separate v2→v1 `core-upgrade` reversal. The actual staging snapshot is
`peek-template-core-summary-touch-before-20261109-075957-5ef2bd29.json`, under
`/home/customer/private-backups/`. Use explicit `core-summary-touch --rollback FILE`
with that snapshot and the matching reviewed package, after inspecting current
ownership and complete state. This restores exact v2; it does not remove any
page's data. Never substitute an older core or legacy-deletion snapshot.

Local installer checks passed **256/0**, retaining all prior 184 checks and adding
the new exact transition, full reverse chain, drift/snapshot refusal and failure
recovery cases. Current-page JSON emission checks passed **10/0**.

## Verification

The final local native Chromium suite passed **266/0** in 127 seconds, with no
INVALID result and all prior 126 checks retained. Its 140 additional assertions
cover the four nonoverflow surfaces, three directions, both sides, the same
summary gaining/losing overflow on resize, short→research scrolling and long
summaries at their bottom. Computed-policy assertions are labeled separately
from native behavioral checks. This is not a claimed native red→green reproduction.
The independent source reviewer found no blocker in the 17-line runtime diff.

[Candidate offline Gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42580785492)
completed **07:41:18 UTC, PASS**, exact source
`659c251a88d3cb2d51fa76936fe18c364d102238`: **29 PASS, 1 SKIP, 0 FAIL, 0 INVALID**.
The full log was independently read; only rendered-page checks without a URL were
skipped. Those run on the fresh copy. All four data JSON files and `peek-pages.json`
are byte-identical to `569f9ce95`.

[Fresh staging39-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42580943410)
completed **07:54:50 UTC, PASS**, source
`6be2a05722f07d68fe845a6ac7c908a42feef5f6`. The full operation report and retained
browser log were read. The copy included uploads (268 MB of files) and a fresh
664,404-byte compressed database. Apply, idempotence, exact rollback and reapply
passed. All ten pages returned HTTP200 before/after, with no new PHP errors;
rendered Home/client regulated-wording gates passed. Only #1380 and the WPCode
cache changed in the fingerprints.

The browser package completed in **566 seconds**: clearance **3340/0 in each of
Chromium and Firefox**, page fixtures **207/0 in each**, native Chromium touch
**266/0**, and the retained Thai suite **485/0**. No INVALID result. The exact Thai
total was confirmed in artifact `site-ops-42580943410`, ID `13657378781`, retained
`peek-core-summary-touch-browser.log`, rather than inferred from the shortened
report. Throwaway-copy snapshots are not staging recovery snapshots.

All [16 fresh-copy captures](assets/2026-11-09-peek-short-summary-touch/rehearsal/)
were independently reviewed against the preceding v2 copy. Nine are byte-identical;
the other seven differ only in rail pixels. Card, text, strip and banner pixels
are unchanged. Root also inspected the actual Thai research and Therapeutic short
summary captures. There is no new visual blocker. The previously documented
844×320 outer-scroll fallback remains constrained; no geometry change was made.
Screenshots establish appearance, not native touch behavior.

[Final read-only preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42582374039)
completed **07:55:56 UTC, PASS**, source
`ea335f2918f62507e3ff69c55c239eaa471b3952`. The full report confirms
`core-summary-touch ready; nothing written`.

## Actual staging update

[Guarded apply](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42582749743)
completed **08:04:07 UTC, PASS**, source
`354af3a1c64c2a396935ab2d315210c17b5e6951`. Root and the independent safety reviewer
read every report section. Rehearsal through apply differ only in the operation
request; the verified runtime/tooling are unchanged.

- Fresh export: `staging39-before-peek-nav-20261109-075943.sql`, **4,465,543 bytes**,
  plaintext SHA256 prefix `71a64b5fc50e`. Server and runner copies matched, then
  encrypted off-site storage was read back identically **before** the write.
- Actual before-snapshot: `peek-template-core-summary-touch-before-20261109-075957-5ef2bd29.json`,
  plaintext SHA256 prefix `fb2c35e6a31f`. It was also copied, verified and read back
  encrypted off-site. #1380 was updated in WordPress storage at **07:59:57 UTC**;
  the SiteGround cache was purged. No snippet swap or fallback-only interval.
- Origin checks returned HTTP200 for all four enabled pages and Services. Each
  enabled page has exactly one core CSS/runtime/data block and its plain jump row;
  Services retains its plain row without Peek. No obsolete pill navigation.
- Post-change code/rendered wording/posture checks, all twelve response checks,
  structured data (27/27, no parse errors) and PHP log checks passed.
- The broad pixel comparison against **live** was **14 identical / 6 different**,
  not an aggregate pixel pass. The six Thai/Therapeutic/Deep Tissue dimensions
  exactly match the preceding v2 deployment's differences. The direct copy-to-copy
  visual review above is the relevant before/after evidence for this correction.
- Fresh database restore import returned **0**. Restored and current site each
  had **104 fingerprints**; only snippet #1380 and the `wpcode_snippets` cache
  differ. The restore comparison's exit 3 records those intended differences,
  not an import failure.

## Final public and stored verification

[Public browser verification](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42583371605)
completed **08:08:52 UTC, PASS**, source
`b17c71d930c8c3e1f478e9bcfeb75dc26d1a5c76`. This attempt reached the actual public
staging pages and completed all five suites in **150 seconds**. Each child exited
0; no FAIL or INVALID result:

| Public suite | Passed | Failed |
| --- | ---: | ---: |
| Chromium clearance | 3340 | 0 |
| Firefox clearance | 3340 | 0 |
| Native Chromium touch | 266 | 0 |
| Manifest-driven page checks | 172 | 0 |
| Matched-capture diagnostic subset | 342 | 0 |

The full operation report was read. The ten complete stdout/stderr streams are
retained in Actions artifact `site-ops-42583371605`, ID `13658755882`, digest
`0740e2a950c2a1ff1c16ef4832ec3a0f0e290fbc26330f232b6957d9671d81dd`.
An independent audit read all ten streams: individual PASS counts match each
summary, all stderr streams are empty, and no hidden error/FAIL/INVALID appears.
There were 28 capture emissions (16 full-matrix plus 12 diagnostic), yielding
[22 unique public images](assets/2026-11-09-peek-short-summary-touch/public/).
The six duplicate labels retain their later diagnostic capture. Root inspected
the actual public Chair short summary and Thai research summary. Independent
review of all 22 files found no new visual blocker. All 16 rehearsal-common
captures retain identical card/text/strip/banner pixels: five files are
byte-identical and eleven differ only inside the rail. The six additional
tablet/desktop states preserve strip clearance and readable research openings.
The existing 844×320 outer-scroll tradeoff is unchanged.
Screenshot comparison cannot establish touch
behavior; the native-touch suite supplies that simulated-browser evidence.

[Final read-only stored inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42583499757)
completed **08:09:31 UTC, PASS**, source
`8b6798d57735aba0e1ddfeedb292dd27ae3f9056`. The full report confirms all five
published records:

| Snippet | Code bytes | SHA256 |
| --- | ---: | --- |
| #1380 shared core | 71826 | `a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac` |
| #1381 Thai | 4376 | `fd67d8f8548706bd8597af87e85380581a18f846a1bf71de4401db80b5f0a7e0` |
| #1382 Therapeutic | 3728 | `3b12f5e09f6937f41f04122cbed3c6ef6244c9359115ce36ed31d2d6b7336ffd` |
| #1383 Deep Tissue | 3408 | `c88e05ae019076e9a173dc239ffa2b091007864087067ef0444efca2acc00101` |
| #1384 Chair | 3923 | `b99558619488cf1506c8b0e8ca2c59066068eaa0727233c90b51f599798a121c` |

All four data hashes match the starting inspection. No public retry or further
staging write is queued. Main/live remain unchanged. Safari, physical devices
and actual assistive technology remain unverified. The next check is Ryan's
short-box swipe on the reporting device, together with a long-summary scroll.

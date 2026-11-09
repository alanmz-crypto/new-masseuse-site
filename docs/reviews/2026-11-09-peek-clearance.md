# Peek: title-strip clearance and touch ownership

**Deployed on staging39, 2026-11-09 07:11 UTC.** Core #1380 now has the
clearance, control-touch and reading-position correction. Approved data #1381–1384
are unchanged. Exact stored hashes are verified. The final public browser run was
**INVALID (HTTP202 hosting challenge, zero assertions)**; no retry is queued.
Fresh-copy browser and visual checks passed; physical-device confirmation remains open.

Scope: shared preview-card placement and touch ownership on staging39 only, on branch
`claude/wizardly-bell-xu0rta`. Ryan's “Next step” continues the explicitly recorded
first-card/title-strip correction. His follow-up also requires a finger sliding
over a closed topic button to stay within that control, without scrolling the
page. Approved summaries, page data, destination landing and other-page
eligibility remain unchanged.

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

## Touch ownership correction

Ryan reported that sliding a finger over a closed topic button after opening the
rail menu scrolls the page. Before implementation, the correction was explained:
closed topic buttons and close/side controls suppress native panning; open
summaries retain their own scrolling. The original pointerdown target owns the
touch, movement beyond the tap tolerance stays a drag even when it returns to
the starting point, and a compatibility click cannot activate a dragged control.
Control pointers are captured until release/cancellation; unrelated pointer IDs
cannot clear the owner. Native browser touch checks compare old and proposed
behavior; dispatched JavaScript pointer events alone do not establish that
native page scrolling is prevented. The initial frozen-v1 local comparison
passed 116/0, and the final expanded identical local suites passed **126/0** for
both frozen v1 and the correction. That Chromium structural fixture did **not**
reproduce Ryan's page scrolling report.

The [actual public native-touch baseline](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42573040003),
source `00a6682e1f915fc1358715fcf133ba04ad023dc2`, finished 06:28:33 UTC:
**126/0**, zero INVALID, 66 seconds, on the still-deployed old core. Thus Chromium
simulation on the real theme also did not reproduce the reported defect. The
correction removes native panning permission from topic controls and preserves
tested scrolling/taps, but no physical-device resolution is claimed. Device and
browser details were requested while the guarded rehearsal continued.

## Upgrade and recovery design

The previously deployed shared v1 is frozen byte for byte as `peek-nav-shared-v1.php`:
file SHA256 `5941becfb3ff70e7359ae61a7c4a2cdd7e0a5b88869e58a5c2fd790bd5824cc3`.
Its code hash is the #1380 hash above. The original `core` migration remains tied
to that frozen payload so historical snapshot meaning is preserved.

An explicit `core-upgrade` operation updates #1380 in place from that exact shared
version. It snapshots complete old/new states and binds the four unchanged data
records. It refuses changed or foreign state and uses the existing verified
encrypted off-site backup sequence. Its separate rehearsal adapter has no
preparation hook: the baseline must be the real four-page staging state.
There is no snippet swap or planned fallback-only interval. The actual upgrade
snapshot is recorded below.

## Verification before final rehearsal

The [Actions offline gate](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42571365218)
passed at 06:10:44 UTC for `7ab9cfc4790a7e7256e29f535a192bef161adf5d`, including
PHP, shellcheck, ruff, installer/recovery and regulated-wording checks.
The [first candidate's automatic Gates run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42573039882),
source `00a6682e1f915fc1358715fcf133ba04ad023dc2`, completed 06:27:25 UTC:
**29 PASS, 1 SKIP, 0 FAIL, 0 INVALID**. Its full job log was independently read;
only rendered-page verifiers were skipped because no URL was supplied. The tested
core hash equals the first candidate hash below. All four data JSON files and
`peek-pages.json` are byte-identical to prior completion commit `1a73c318`.
Local source checks passed 184 installer/guard cases and 10 JSON-emission cases;
local shellcheck/ruff were unavailable, so the Actions results supply those gates.
An independent resize probe passed all 40 normal/enlarged-text states while
preserving focused-summary identity and reading position. These local probes are
not actual staging renders.

[Read-only staging preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42571863355),
source `8e48502d4e0469c375bf758e5ab470da178133aa`, finished 06:15:22 UTC:
`core-upgrade ready; nothing written.` It accepts the current exact v1 core and
four approved data records. That preflight preceded the touch correction.
That first touch/clearance candidate had 70,135 code bytes and SHA256
`f045d957e3e28fa8448dfd4089fa1f811ff158a40aa8c0524bb0aa482030f234`.

Final local Chromium clearance passed **3340/0** in 100 seconds, including actual
closed-row containment and 16 structural captures. Native Chromium touch passed
**126/0** in 63 seconds: closed-control drags preserve page, preview and both
scrollbars; taps work; summary/outer-card scrolling remains local to the card.
The expanded final native suite is not the same 116-case baseline above.
The original Thai top test now asserts real strip separation, intact approved
items and visible name/summary/hint instead of forbidding compact fit classes:
its 390×664 mock viewport genuinely needs compaction under the initial strip.
No settled gesture assertion was removed. Local Firefox is unavailable; the
fresh Actions rehearsal must supply its first final-payload result.

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
No new staging website write had occurred at that point. Main/live remain unchanged.

## Actual-copy findings and responsive correction

The [first complete rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42573090455),
source `c2c3ae119d5ed23e994c3aff24348e2367e60999`, finished 06:34:25 UTC:
**FAIL**, not a release pass. The fresh staging39 copy included uploads. Apply,
idempotence, exact rollback, reapply, ten HTTP200 pages, rendered Home/client
wording gates and PHP-error checks passed. Only #1380 and WPCode cache changed;
all four data snippets stayed intact. Generic browser fixtures passed **207/0 in
each engine**, and native Chromium touch passed **126/0**.

Clearance matrices reported **3327/13 Chromium** and **3332/8 Firefox**. All 21
failures concerned the research summary's reading position across 768↔1280
resizes. Clearance, focused-node identity and summary text passed. The retained
Thai suite correctly did not run after those failures. Rehearsal snapshots are
throwaway-copy evidence, not staging recovery files.

Actual theme typography reflows before the resize handler can read the old
scrolling endpoint. The correction remembers the reading position while the
summary scrolls and restores that intent after reflow; when dimensions have not
changed it uses the live position. Programmatic reflow clamps cannot overwrite
the bookmark. The mock now reproduces the observed responsive root font sizes.
The full responsive local matrix passed **3340/0**; the final cache refinement
then passed **16/16** focused end/middle resize states across all four pages.
Review additionally reproduced stale intent after a late font change followed
by a genuine Home keypress. Refit now also checks summary dimensions, and scroll
events at an unchanged viewport can refresh the bookmark. Focused probes confirm
Home remains at zero after both summary-only and inherited font changes, while
all four pages retain their responsive end/middle positions and focus. There is
no new scroll-triggered refit. The reviewed candidate is **71,321 code bytes**,
SHA256 `357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8`.

An independent review of the 16 actual-copy captures confirms ordinary cards
clear the strip, expose the CTA, and retain the full section stack. At the
deliberately constrained Chair 844×320 viewport, the above-strip interval covers
some banner content and initially clips about 5px of the hint; the retained
outer-card scroll and keyboard paths reach it. This is a constrained-layout
tradeoff, not a claim that everything fits simultaneously.

Before changing the rehearsal runner, the user was told its time allowance would
increase to retain every check. Only this exact core-upgrade package gets 900s;
other packages retain 600s. The runner also saves the complete browser log in the
Actions artifact before deleting its throwaway workspace. The public wrapper
retains complete per-suite streams and repeats every verdict/count after matched
captures; it is read-only and accepts only the exact staging39 origin.

The [final candidate's automatic Gates](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42576069430)
finished 06:56:29 UTC at `bd4b3fb0b15223dc9ca491120d55c7b2d05a1c23`:
**29 PASS, 1 SKIP, 0 FAIL, 0 INVALID**. The only skip is rendered-page verification
without a URL; the fresh-copy rehearsal supplies that gate. The entire job log
was independently reviewed. Its core exactly matches the 71,321-byte candidate
above, and all four data JSON files plus the manifest remain byte-identical to
`1a73c318`.

## Passing fresh-copy rehearsal

The [repeat rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42576069364)
at the same exact candidate commit finished **07:07:54 UTC, PASS**. It copied
staging39 with uploads (268MB files, 661,493-byte compressed database), checked
the real four-page starting state without a preparation hook, and passed apply,
idempotence, exact rollback, reapply, HTTP200 pages, no-new-PHP-errors and rendered
Home/client regulated-copy gates. Fingerprints changed only **#1380** and the
WPCode snippet cache. The before-snapshots in this report belong to the throwaway
copy and must not be used as staging recovery files.

Browser results, with zero failures or INVALID results:

| Suite on the actual copy | Result |
| --- | --- |
| Clearance, Chromium | 3340/0 |
| Clearance, Firefox | 3340/0 |
| Page/structure fixtures, Chromium | 207/0 |
| Page/structure fixtures, Firefox | 207/0 |
| Native Chromium touch | 126/0 |
| Retained Thai gesture suite | 485/0 |

The browser package completed in **598 seconds**. The retained artifact's full
browser log confirms the Thai total (the report's filtered output omits that
older suite's unprefixed total). Root read the complete operation report and the
retained suite summaries; the independent visual reviewer viewed all 16 fresh
captures. Root also reviewed fresh Thai phone first/research and Chair tablet captures. No new
visual blocker was found; the constrained-height tradeoff above remains explicit.
Seven captures are byte-identical to the preceding copy; the remaining nine
differ only on the rail. Timing is a plausible cause, not an isolated diagnosis.

## Guarded staging deployment and recovery

[Final read-only preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42577490238)
finished 07:09:50 UTC at `f290fcce11c447ab767f3057b153e7cd0ba4dc93`:
`core-upgrade ready; nothing written.`

[Staging apply](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42577650950),
source `67f09e27f4faaa78705ea22b2534a4d4300fab1e`, finished **07:15:10 UTC, PASS**.
Root and an independent reviewer read every section, including informational
nonzero results rather than treating the report's overall PASS as every-test PASS.

- Fresh SQL: `staging39-before-peek-nav-20261109-071106.sql`, **4,455,207 bytes**,
  plaintext SHA256 prefix `94fa78bb2ee2`; encrypted off-site copy read back and
  verified **before** the write.
- Actual staging snapshot: **`peek-template-core-upgrade-before-20261109-071122-958bff3b.json`**,
  plaintext SHA256 prefix `f02193e90e10`; encrypted off-site copy also verified.
- #1380 updated in place at 07:11 UTC, WordPress storage only; cache purged.
  No fallback-only interval or page-data write occurred.
- Origin checks: all four service routes HTTP200 with exactly one core/data
  emission and the retained plain jump row; Services retains only its plain row.
- Post-change: rendered wording, posture, HTTP200 pages, structured data and PHP
  log checks passed. Pixels versus live are **14 identical / 6 different**, zero
  missing captures; the differences are the Thai/Therapeutic/Deep desktop/mobile
  views. This comparison is not an aggregate pixel pass; live has no Peek rollout.
- Restore proof: import exit **0**, **104 fingerprints on each side**; only snippet
  **#1380** and the WPCode cache differ. Data #1381–1384, pages, CSS, SEO and row
  counts are unchanged. Its exit3 reports those intended differences.

After checking current ownership and complete state, use this upgrade's actual
snapshot with the matching reviewed package to restore frozen shared v1:

```sh
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-upgrade --rollback $HOME/private-backups/peek-template-core-upgrade-before-20261109-071122-958bff3b.json'
```

This command is recorded, **not run**. The wrapper takes another fresh verified
off-site backup before any reversal. Changed core or page data makes reversal
refuse. Reverse this upgrade before using the older shared-core migration
snapshot; never use the legacy deletion snapshot against the shared core.

## Final verification and remaining access limit

[Public browser run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42578241317),
source `14c294ea456f757b379d7ccdc3150f846afb5320`, finished **07:17:03 UTC, INVALID**.
SiteGround returned HTTP202 to the initial Thai request in each suite. Every
suite stopped; **zero assertions and zero captures** were produced. Matched
captures were skipped. This establishes no public browser result and is not a
feature failure. The complete report was read; no repeat of the blocked request
is queued. The earlier native-touch baseline did not reproduce the reported
scrolling, so the physical-device outcome remains unverified.

[Stored-snippet inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42578355744),
source `2e2a1f2ceace32c287d5f6e5306c9100d6165d4e`, finished **07:17:32 UTC, PASS**.
Published #1380 has **71,321 code bytes**, SHA256
`357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8`, exactly matching
the rehearsed source. All four published data hashes are unchanged:

| Snippet | SHA256 |
| --- | --- |
| #1381 Thai | `fd67d8f8548706bd8597af87e85380581a18f846a1bf71de4401db80b5f0a7e0` |
| #1382 Therapeutic | `3b12f5e09f6937f41f04122cbed3c6ef6244c9359115ce36ed31d2d6b7336ffd` |
| #1383 Deep Tissue | `c88e05ae019076e9a173dc239ffa2b091007864087067ef0444efca2acc00101` |
| #1384 Chair | `b99558619488cf1506c8b0e8ca2c59066068eaa0727233c90b51f599798a121c` |

The saved evidence distinguishes the 12 actual public baseline captures from
the 16 passing rehearsal captures. There are no post-deployment popup captures:

| View | Public before | Fresh staging-copy after |
| --- | --- | --- |
| Thai, phone, first box | [Before](assets/2026-11-09-peek-clearance/before/clearance-thai-massage-390-right-top-1.jpg) | [After](assets/2026-11-09-peek-clearance/rehearsal-after/clearance-thai-massage-390-right-top-1.jpg) |
| Chair, tablet, first box | [Before](assets/2026-11-09-peek-clearance/before/clearance-chair-massage-768-right-top-1.jpg) | [After](assets/2026-11-09-peek-clearance/rehearsal-after/clearance-chair-massage-768-right-top-1.jpg) |
| Chair, desktop, first box | [Before](assets/2026-11-09-peek-clearance/before/clearance-chair-massage-1280-right-top-1.jpg) | [After](assets/2026-11-09-peek-clearance/rehearsal-after/clearance-chair-massage-1280-right-top-1.jpg) |

Very short landscape retains the tested constrained fallback described above.
Safari, physical touch and actual VoiceOver/TalkBack remain unverified. Services
and short-page eligibility remain separate decisions. Main/live are unchanged.

I finished: the shared touch/clearance correction is deployed on staging39 with
fresh-copy browser/visual gates, verified backups, exact rollback proof and stored hashes.
Next step: reload staging39 and retry the reported swipe on the original device.
Next lane: Ryan's device confirmation; public automation once hosting access returns.
See my work: the operation reports and before/rehearsal-after captures in this record.
Arc: none (ad-hoc)

TL;DR: topic controls now block native panning while summaries keep local scrolling;
the tested correction is deployed, with public browser verification blocked by HTTP202.

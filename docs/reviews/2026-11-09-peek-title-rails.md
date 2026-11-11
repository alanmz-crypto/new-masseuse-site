# Peek rails without new summaries

**Later assessment, 2026-11-09:** The [Kiro findings and Codex verification](../inter-model/2026-11-09-peek-nav-kiro-assessment.md)
confirm a native-touch disclosure landing failure and keyboard double-toggle on
Privacy that the initial-state coverage below missed. Fixes are pending; the
deployment results below are historical evidence, not proof those defects are
absent. No runtime or website changes were made during that assessment.

**Deployed and verified on staging39.** Ryan requested shared Peek rails on pages
that do not already have rails, without adding summaries. This follows the
[deployed all-page title strip and larger summaries](2026-11-09-peek-title-strip-all-pages.md).
Staging39 is the only deployment target; main and live remain untouched.
The third fresh-copy rehearsal, complete browser-log audit and actual-page visual
review passed after correcting the Search-button collision. The guarded apply
passed at 17:41:06 UTC, with verified off-site backups, 19 origin checks and a
successful restore import. The failed first run and visually blocked second run
remain below. Final public browser checks passed 11,829/0; stored inspection
confirmed the exact reviewed core and unchanged four data records. Main/live
were not changed.

## Baseline and access

The feature branch was pulled at
`903adaff3f756ef4126bd9e4093f2d9c36014bc2`. GitHub repository metadata confirmed
read/push/admin permission. A fresh Thai-page GET from the current execution
environment returned HTTP200 at the start of this follow-up. A subsequent
Playwright capture batch stopped on its first Home request at **16:15:49 UTC**
with HTTP202 and zero images (`/tmp/peek-title-rail-baseline/report.json`); it was
not retried. One separate Home response obtained with a TLS
override supplies no reliable rendered-browser review. A successful HTTP request
does not establish browser access or a feature pass.

Before this update, core #1380 had **86,567 code bytes**, SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`.
It supplies title strips to visitor pages and summary rails to the four approved
service pages. Stored inspection **42601221631** confirmed that exact core and
unchanged data #1381–1384 at **10:30:40 UTC**. The preceding final focused public
suite was **INVALID HTTP202**, with zero assertions/captures. Its passing
fresh-copy suite and separate 20 deployed comparison captures remain baseline
evidence, not checks of this candidate.

## Shared behavior

- Reuse the settled Peek rail, index and preview gestures. A new
  `.ts-pk-titles` mode displays section titles and the jump control without a
  summary body. The index continues to preview without scrolling the page.
- Existing `.ts-jump` labels and destinations remain the sole order source where
  a usable row exists, including curated targets inside closed native details.
  Otherwise use visible main H2 headings, excluding hidden/collapsed content,
  then one page-title entry when there are no sections. Search keeps its fixed
  page identity. Zero/one-section pages still receive one rail segment.
- Existing destination IDs remain usable links. Headings without IDs use semantic
  buttons targeting their actual DOM nodes; do not invent IDs, hashes or a second
  navigation map. Keep existing plain jump rows usable without JavaScript.
- Do not create new data snippets, summary JSON or marketing wording. The four
  approved summary sets and their whole-paragraph fallback behavior remain intact.

The practice-page decision was explained before implementation: preserve the
existing rails on `/rail-nav-ideas/` and `/index-nav-ideas/`. Progress modes A–D
receive the shared title rail; mode E hides that new box and releases its rail
readiness/padding while the existing demo rail is active. Switching back restores
the shared rail. All other visitor HTML pages without approved summary data use
the title-rail mode. Without PointerEvent support, the read-only strip and
ordinary links remain available.

`peek-pages.json` now records this enabled `titleRail` scope separately from the
enabled title strip and four approved summary pages. It names the two existing
rail paths and the conditional Progress E rule explicitly. The old undecided
rail-eligibility list is removed; the four `pages` entries and all summary data
remain unchanged. Browser checks must verify these exceptions rather than infer
eligibility from whether a rail happened to appear.

## Verification and guarded transition

The preceding code is frozen as `peek-nav-shared-v4.php`; its code bytes and
hash match the stored baseline above. The `core-title-rail` transition
starts from exact v4, while `core-title-strip` is now pinned to v3→v4 so its
actual 10:24:52 snapshot retains its recovery meaning. The installer and runtime
passed the complete gates recorded below before deployment. The new actual
staging snapshot is recorded in the deployment section.

Read-only data compatibility checks passed: all four summary files pass the
existing lint, all **40 mutation checks** pass, and the four JSON files are byte
identical to the pulled baseline. This lint does not validate rendered rails or
semantic summary accuracy. No lint change is needed merely to add title rails.
The mock builder also accepts the new manifest: nine structural routes were
generated, with page-data JSON only on the four approved routes. This is build/
data-emission compatibility, not a rendered browser result.

Before any staging write, require complete offline gates, an untouched fresh
staging39-copy rehearsal with uploads, rendered wording checks, exact rollback/
reapply, new title-rail browser checks, retained summary/clearance/touch regressions
and actual-page visual review. The write needs a fresh verified encrypted off-site
backup and its own actual snapshot. Review every report afterward; INVALID proves
nothing. Any later reversal must restore v4 with the new transition's own snapshot
before older recovery. No fallback-only interval is planned.

Safari, physical devices and actual assistive technology remain unverified. The
previously reported rapid-switch loading delay was not reproduced and is not
declared resolved by this scope change.

## Initial candidate and local checks

The reviewed candidate contains **95,886 code bytes**, SHA256
`33f8ad642473750622ee1f28f6e03c554214a711315395f28fd07be849e568d2`.
New title-rail fixtures passed **437/0 Chromium, 434/0 Firefox**. Existing title
fixtures passed **230/0 each**, and generic fixtures **207/0 each**. Wrapper
verification passed **22/0**. These are local fixtures, not actual-site results.
An independent native Chromium probe passed **31/0**, including both sides,
gesture containment, target/focus identity after reordering and exclusive
Progress rails. Root viewed the structural seven-section, long-title and
no-heading captures: titles and jump controls fit without summary space.

Earlier new-rail fixtures were **366/1 Chromium, 365/0 Firefox**; the independent
probe was **29/2**. Native title-card taps reached the destination but the
compatibility mousedown then blurred its heading. The new mode now consumes that
same touch's compatibility mouse events, disarming on a fresh pointer press.
The final trace keeps focus on the original heading. The existing Thai mode also
reproduced that older native-touch focus loss and remains unchanged; its device/
assistive-technology follow-up remains open. A separate source review caught the
FAQ opening click being swallowed; a narrowly scoped exemption now permits only
that synchronous programmatic disclosure click. Native closed-FAQ tests pass.

The original local `check-all.sh` run had **three failures** because Docker bind
mounts did not expose the test files; shellcheck and ruff were unavailable and
skipped. Its apparent PHP lint pass did not establish that files were checked.
Replacement verification streamed the reviewed source into the pinned container
with networking disabled and asserted **129 PHP files, zero syntax failures**;
installer/recovery **487/0**, current-page emission **24/0**, structured-data and
headers/CSP tests passed. This does not relabel the original command as passing.
These local replacements did not complete the rendered-copy requirement; the
subsequent Actions result is recorded below.

## Retained local evidence

These are **structural-fixture results**, not a rendered staging39-copy or
physical-device result. The [compact native probe](assets/2026-11-09-peek-title-rails/checks/native-adversarial-compact.jsonl)
retains all **31 PASS assertions**, original geometry and focus identities. Only
20 long `BODY` focus-text strings containing repeated inline runtime were replaced
with their UTF-8 byte counts and SHA256 hashes; every other original value was
checked for exact retention. Its metadata records the full source path
`/tmp/peek-title-rail-adversarial-final.jsonl`, **1,460,297 bytes**, SHA256
`9f1c24aaad403783e567553141934e7f7e67d32e9f26854f9d1fb0c7379d12fc`.
The logged `runtimeSHA` identifies the whole PHP file, including its opening tag;
the deployed-code hash excludes that tag, as recorded above.

The [final focus trace](assets/2026-11-09-peek-title-rails/checks/focus-trace-final.jsonl)
is copied byte for byte: **2,287 bytes**, SHA256
`70031ae779349c68defb42082e422a642d21ea0e681490f1bced065bd3365fb7`.
The new ID-less title-rail target retains H2 focus after compatibility mouse
events. The existing Thai target receives focus, then loses it to `BODY` after
the compatibility mousedown; its hash and destination remain correct. This
locally reproduced behavior is unchanged by the candidate and remains an open
focus/accessibility issue, not a new regression or a physical-device finding.

Root reviewed these three unchanged fixture images: [seven compact sections](assets/2026-11-09-peek-title-rails/checks/services-seven-structural.png),
[long complete title](assets/2026-11-09-peek-title-rails/checks/long-heading-structural.png)
and [single no-heading entry](assets/2026-11-09-peek-title-rails/checks/no-heading-structural.png).
The compact cards retain their title and jump controls without summary space.
They do not replace the required actual-page review.

## Actions Gates and first rehearsal failure

[Actions Gates 42656502403](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42656502403)
passed at **16:37:23 UTC** for source
`9996b172bcbf9cbb4354431189d965edb1d87a70`: **29 PASS, 1 SKIP, 0 FAIL,
0 INVALID**. The complete 193-line job log was read
(`/tmp/peek-title-rails-actions-gates-1.log`). Shellcheck, ruff, pinned PHP,
ownership/recovery/current-page emission, wording and builder gates passed.
Only URL-dependent rendered verification was skipped.

The [first fresh-copy rehearsal, 42656502274](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42656502274)
used that source and finished **16:40:21 UTC, FAIL**. The complete 203-line decoded
report was read (`/tmp/peek-title-rails-rehearse-1-text.md`). Exact rollback/reapply,
idempotence, rendered regulated wording, PHP and published-page inventory checks
passed. Only #1380 and WPCode cache changed in the throwaway copy. Its `/backups/`
snapshots are disposable rehearsal artifacts, not staging recovery snapshots.

The title-rail matrices returned **1364 passed, 7 failed in Chromium** and
**1357 passed, 7 failed in Firefox**, each covering **21 actual routes**. Chromium
produced **44 captures: 30 actual-page views and 14 local fixtures**. The retained
title-strip/core suites were **skipped** because the new matrices failed; their
earlier fixture results do not substitute for this missing rendered-copy gate.

Each engine failed the Privacy mobile jump-clearance assertion, Search jump
clearance at three widths, Search's no-JavaScript content assertion, and two
single-ID-less-fixture assertions for side switching and keyboard index opening.
Runtime work is addressing the one-entry fallback's first reading target and
transient clearance clamping. Checker corrections under review cover bounded FAQ
settling, explicit UTF-8 fixture encoding and Search's actual H1 outside `main`.
At that point these corrections were pending, not a passing result; the failed run remains failed.

All **30 actual-page captures were reviewed** by the visual-review agent, with no
additional visual blocker in those pictured open-first-card layouts. Work With
Me's desktop seventh closed row uses an ellipsis; no open seventh-title capture
was reviewed in this set. Its full title remains an explicit browser-check
requirement when opened. This visual
judgment does not waive the failed navigation/clearance checks. Images are retained
unchanged in the clearly labeled [failed first-rehearsal folder](assets/2026-11-09-peek-title-rails/rehearsal-1-failed/),
with per-image hashes in its [capture manifest](assets/2026-11-09-peek-title-rails/rehearsal-1-failed/capture-manifest.json).
The source artifact is **5,563,004 bytes**, SHA256
`fb43ca453529cbe8803ce331a4ba950d0f59ed2cf850e67e9857fe4a4de470ed`.

## Read-only preflight after the failed rehearsal

[Dry run 42656886956](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42656886956),
source `a444ac9076fb49a38f1ce2aeee55229d7528308f`, finished
**16:40:57 UTC, PASS**. Its complete report was read
(`/tmp/peek-title-rails-dry-1-text.md`): published paths match the reviewed
inventory, and the exact core guard reports ready. It uploaded the package outside
the web root and wrote no WordPress state. This preflight does not override the
failed rehearsal. At that point staging writes remained blocked pending a complete passing
fresh-copy/visual gate and a fresh verified off-site backup; no new staging
recovery snapshot exists.

## Corrected candidate for a second fresh-copy rehearsal

The second candidate contained **96,717 code bytes**, SHA256
`a580559856501092595ed3d623a74e05e133463706e118ec0338fc28948d92af`
(full PHP file:96,723 bytes,
`382c7805cae8495e4a88c0bf81b9250c4bd44e92225a4632f7255f62e14e482c`).
A single page-title destination now uses the first visible original reading block,
excluding Peek/navigation nodes, with no invented ID or address change. Measuring
rail clearance preserves the title-rail reader's position after temporary padding
removal: DejaVu Sans reproduced a179–180px jump on a short page even though final
line wrapping and scroll limits were unchanged. The four summary pages retain
their preceding behavior.

The checker now waits for an aligned, stable animated FAQ landing within3.2s,
retaining its focus/hash/clearance requirements. It compares original headings
and links with JavaScript disabled rather than assuming Search's H1 is inside
main. Fixture encoding is explicitly UTF-8, and the short-page fixture uses the
font that exposed the real clearance clamp.

Candidate2 local title-rail fixtures: **440/0 Chromium,437/0 Firefox**, with14 local
Chromium images and no actual routes. PHP syntax, **487/0 installer guards** and
**24/0 current-page emission** passed on the exact candidate in pinned PHP.
These local passes do not replace the complete fresh-copy rehearsal or establish
that Privacy/Search now pass on their actual page structures.

The final local rerun retained **440/0 Chromium,437/0 Firefox**, now also checking
all open-title text line boxes fit within the card. Retained title-strip fixtures
passed **230/0 in each engine**; the independent native-touch suite passed31/0.
Search target/focus identity and four actual landed views will be retained in the
next copy report. The Work seventh title is checked fully open at all three widths.

The independent candidate2 native replay passed **31/0 +48/0 local assertions**.
Both DejaVu side positions stayed atY198 during all sampled title/side/close/hint
swipes and side taps; four-service smoke states matched candidate1 exactly.
The [compact evidence manifest](assets/2026-11-09-peek-title-rails/checks/candidate2-native-evidence.json)
records hashes and scope. Earlier probe mistakes are retained: hidden hint text
was mistakenly measured as a source paragraph; the side action was incorrectly
expected to leave its card open; and a SECTION destination was incorrectly
expected to retain focus instead of its original H2. These probe corrections
required no further runtime edits and are not presented as runtime fixes.

[Candidate2 Actions Gates42658841182](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42658841182)
passed **16:51:41 UTC** for
`362e92fa87da1e1aa93ba0de39e32b12d8da52c0`: **29 PASS,1 SKIP,0 FAIL,0 INVALID**.
The full job log was read and retained at `/tmp/peek-title-rails-actions-gates-2.log`.
Only URL-dependent rendered verification was skipped. The separate
[fresh-copy rehearsal42658841171](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42658841171)
used the same source and completed as recorded below; no staging write was requested.

## Second rehearsal: automated pass; visual blocker prevents deployment

[Rehearsal 42658841171](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42658841171),
source `362e92fa87da1e1aa93ba0de39e32b12d8da52c0`, finished
**17:06:32 UTC, PASS**. The complete **286-line** decoded report was read
(`/tmp/peek-title-rails-rehearse-2-text.md`), and the full browser log is retained
in the extracted artifact. Exact rollback/reapply, idempotence, regulated wording,
PHP and published-page inventory checks passed. Only #1380 and WPCode cache
changed in the throwaway copy; its snapshots are not staging recovery snapshots.

The ten unique browser suites total **13,380 passed, 0 failed**, without counting
the repeated wrapper summaries twice:

| Suite | Chromium | Firefox |
|---|---:|---:|
| New title rails | 1377/0 | 1370/0 |
| Retained title strip | 1339/0 | 1336/0 |
| Generic pages | 207/0 | 207/0 |
| Clearance | 3340/0 | 3340/0 |
| Native touch | 378/0 | Not a Firefox suite |
| Retained Thai gestures | 486/0 | Not a Firefox suite |

The complete wrapper ran **784 seconds**. New rails and retained strips each
covered **21 actual routes**, including search and 404. The report produced
**106 captures: 92 actual-page views and 14 local fixtures**. Its verified archive
is **12,346,738 bytes**, SHA256
`d8e993b21b5f57a59cd8a0fc3104abf0cdcf647230b258170a2aaa29790a5493`.
The 34 new actual-page title-rail views are retained unchanged in
[rehearsal-2-visual-blocked](assets/2026-11-09-peek-title-rails/rehearsal-2-visual-blocked/),
with exact identities in the [capture manifest](assets/2026-11-09-peek-title-rails/rehearsal-2-visual-blocked/capture-manifest.json).
The remaining captures are retained in the full operation artifact, not copied
into this folder.

Manual visual review found a deployment blocker despite the passing assertions:
the **44px left index icon overlaps the Search button** in the landed
[768px view](assets/2026-11-09-peek-title-rails/rehearsal-2-visual-blocked/title-rail-s-massage-768-landed.jpg)
and [1280px view](assets/2026-11-09-peek-title-rails/rehearsal-2-visual-blocked/title-rail-s-massage-1280-landed.jpg).
These views were also independently inspected while recording this result. The
current clearance checks did not cover this form-control collision, so the
automated pass is not a visual or deployment approval.

A narrow correction is in progress to reserve clearance around the affected
form block without padding the page column. It needs its own complete fresh-copy
gate and visual review. No staging write or new recovery snapshot exists; the
manifest remained pending at that point. Physical-device, Safari and assistive-technology limits
remain unchanged.

## Form-clearance correction before the third rehearsal

Candidate3 has **97,130 code bytes**, SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`
(full PHP:97,136 bytes,
`602df3dca522b4f6be7db173202c8f64acec3806cffd00705f46fb27ee4815f6`).
Only title-rail content gains a form-clearance class. The form itself receives
measured spacing and border-box sizing; its descendants avoid duplicate padding.
Columns and full-width blocks remain unchanged. `:where()` preserves the existing
selector specificity and the four summary pages keep their earlier behavior.

A new structural form reproducer exposed **46 overlap/hit failures** in candidate2
(528 passing assertions). The corrected final fixtures pass **584/0 Chromium and
581/0 Firefox**, including both sides at390/768/1280, full icon/rail hit rectangles,
visible control corners/centers, native search-field focus, horizontal overflow
and unchanged hero/column geometry. The new actual-page gate checks Search at
top, landing and positions aligning each control with the icon/rail, and retains
six additional form landing captures. This is still local evidence until the
next fresh-copy run completes.

Pinned PHP syntax, **487/0 installer guards** and **24/0 emission checks** passed
on the exact new candidate. No staging write has occurred.

Bounded local form-check evidence is retained as the
[candidate2 baseline excerpt](assets/2026-11-09-peek-title-rails/checks/candidate3-form-baseline-chromium.log)
and final [Chromium](assets/2026-11-09-peek-title-rails/checks/candidate3-form-final-chromium.log)
and [Firefox](assets/2026-11-09-peek-title-rails/checks/candidate3-form-final-firefox.log)
excerpts. The separate native-input [baseline probe](assets/2026-11-09-peek-title-rails/checks/candidate3-form-independent-baseline.json)
and [candidate3 probe](assets/2026-11-09-peek-title-rails/checks/candidate3-form-independent-candidate3.json)
cover 12 local combinations of engine, width and rail side. Baseline had 12
overlapping buttons and 24 blocked sampled hit points; candidate3 had zero of
either. Both preserve the measured hero/column geometry in all 12 combinations.
These use structural forms, prevent fixture submissions and make no external
requests; they do not reproduce the actual Astra page or establish a copied-site
visual pass.

[Candidate3 Actions Gates 42662819268](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42662819268)
passed at **17:16:13 UTC**, source
`751083e3afddb67a2f56e2cea374a6046b88e3f4`: **29 PASS, 1 SKIP, 0 FAIL,
0 INVALID**. The complete job log was read
(`/tmp/peek-title-rails-actions-gates-3.log`); only URL-dependent rendered
verification was skipped. The candidate remains exactly **97,130 code bytes**,
SHA256 `c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.
The [third fresh-copy rehearsal, 42662818962](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42662818962)
started **17:15:20 UTC** and completed as recorded below. At that point the manifest
remained pending until an actual guarded staging apply.

## Third rehearsal: full browser and visual gates pass

[Rehearsal 42662818962](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42662818962),
source `751083e3afddb67a2f56e2cea374a6046b88e3f4`, finished
**17:31:25 UTC, PASS**. The complete **300-line** report was read
(`/tmp/peek-title-rails-rehearse-3-text.md`). Exact rollback/reapply, idempotence,
rendered wording, PHP and the complete published-page inventory passed. Only
#1380 and WPCode cache changed in the throwaway copy. Its `/backups/` snapshots
are rehearsal artifacts, not staging recovery snapshots.

The ten unique browser suites total **13,836 passed, 0 failed**:

| Suite | Chromium | Firefox |
|---|---:|---:|
| New title rails | 1605/0 | 1598/0 |
| Retained title strip | 1339/0 | 1336/0 |
| Generic pages | 207/0 | 207/0 |
| Clearance | 3340/0 | 3340/0 |
| Native touch | 378/0 | Not a Firefox suite |
| Retained Thai gestures | 486/0 | Not a Firefox suite |

The browser agent completed an independent audit of the **11,642,473-byte,
14,032-line** full log, SHA256
`2fea74be57303971aa338f08a8da4fea938f9bc25ae5af37cecd13f0549e90d4`:
exactly **13,836 PASS lines, zero FAIL and zero INVALID**,
all ten numeric suite results and all nested child/effective exits 0. The actual
Search form checks passed **168/168** across both engines, three widths, both
rail sides, page top/landing/control-aligned positions, complete hit rectangles,
sampled control hit points, native input focus and overflow. Earlier Privacy,
Search, no-JavaScript and short-page side-switch failures now pass. The wrapper
completed in **824 seconds**, within its 1500-second budget. The existing Firefox
practice-rail Enter issue remains explicit INFO at three widths; its required
pointer/Go checks passed. This is fresh-copy evidence, not public verification
after deployment.

The verified artifact is **13,171,598 bytes**, SHA256
`c139caefb19fa990fdf6a49b6d1396d773635586041bc796d92d4f3765f0794d`.
Its **114 unique captures** comprise **98 actual-page views and 16 local fixtures**.
The final **40 actual title-rail views** are retained unchanged under
[rehearsal-3-passed](assets/2026-11-09-peek-title-rails/rehearsal-3-passed/), with
per-image hashes in the [capture manifest](assets/2026-11-09-peek-title-rails/rehearsal-3-passed/capture-manifest.json).
The other 74 captures remain in the complete operation artifact.

Visual review covered those 40 views: **11 common images are byte-identical** to
the reviewed second copy; the visual-review agent viewed all **23 changed common
images and all six new Search form views**, covering both sides at 390/768/1280.
Root separately viewed all six Search control captures. The previous overlap is
resolved: on the left, the Search controls begin around x52 beyond the index
hit area's x46 edge; field widths and control usability remain intact. No visual
blocker remains. Work's seventh closed-row ellipsis is unchanged; complete open
titles remain covered by the browser assertions.

The reviewed runtime is unchanged at **97,130 code bytes**, SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.
The guarded staging39 request `peek-title-rails-apply-20261109-1` was submitted
at source `267c93e9`; its completed result is recorded below. Safari, physical
devices and actual assistive technology remain unverified.

## Actual staging39 deployment and recovery

[Apply 42666333809](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42666333809),
source `267c93e94f2b81ae63843be8f2e806805a061bd3`, wrote #1380 at
**17:37:37 UTC** and finished **17:41:06 UTC, PASS**. Root read the complete
302-line decoded report (`/tmp/peek-title-rails-apply-1-text.md`). The corresponding
offline Gates run **42666333838** passed at **17:37:48 UTC**: 29 PASS, one
URL-dependent SKIP, zero FAIL/INVALID; its complete log was read. Runtime code
remained exactly the reviewed **97,130 bytes**, SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.

Before the write, fresh export **`staging39-before-peek-nav-20261109-173723.sql`**
(4,497,790 bytes, plaintext SHA256 prefix `6bcb28a1f60b`) was copied, verified by
matching SHA256, encrypted and read back identically from off-site storage
(4,499,078 encrypted bytes). The actual recovery snapshot is
**`peek-template-core-title-rail-before-20261109-173737-cae68f35.json`**
(plaintext SHA256 prefix `d236a7d633bd`); its encrypted 210,597-byte copy was also
verified off-site. These are actual staging artifacts, not rehearsal snapshots.

All **19 published-page origin checks** returned 200 with exactly one shared CSS,
runtime and config, data only on the four approved pages, and the existing service
jump rows intact. Rendered wording, posture, page responses, structured data
(27/27 identical, no parse failures) and PHP-log checks passed. The broad pixel
comparison against live had **20/20 differences**, with all views present; its
exit 1 is not an aggregate pass or a comparison against the immediately preceding
staging version. The restore test imported the actual export successfully
(exit 0), compared **104/104 fingerprints**, and found only the intended #1380
and `wpcode_snippets` cache differences. No fallback-only interval occurred.

The visual agent reviewed all **20 deployed candidate captures**, ten routes at
desktop/phone widths. Root separately viewed [About on phone](assets/2026-11-09-peek-title-rails/deployed/about-m.png)
and [Privacy on phone](assets/2026-11-09-peek-title-rails/deployed/privacy-policy-m.png).
No new persistent layout blocker was seen. Existing transient section labels can
cover part of the display strip or hero in a captured instant. These closed-state
images establish neither open-card behavior nor physical-device support. Exact
identities for all 20 views and the two retained images are in the
[deployed capture manifest](assets/2026-11-09-peek-title-rails/deployed/capture-manifest.json).
All 20 originals remain in apply artifact **13697715744**, 29,659,179 bytes,
SHA256 `7ebdfa7f948b9ec08e49412f2ecbfaf4f440ad7510c34859c444288a603c54f3`.
The other 18 PNGs are not duplicated in this repository. Live references were
not part of this visual review, and not every changed pixel is attributed to
this particular update.

`titleRail.staging` is now `enabled`. The separate final public browser and
stored-snippet inspections passed as recorded below.

Recovery must use the matching reviewed package and a fresh verified backup,
with current full-state/ownership checks. The explicit component arguments are:

```sh
core-title-rail --rollback $HOME/private-backups/peek-template-core-title-rail-before-20261109-173737-cae68f35.json
```

This restores exact v4 while preserving all four data records. Only afterward
may the older title-strip snapshot restore v3, summary-touch restore v2,
core-upgrade restore v1 and original core restore legacy. No rollback was run
on staging during this deployment. Removing one service page's data now leaves
its title-only rail and title strip; the existing plain row remains the source
and no-JavaScript fallback. Before any future runtime edit, freeze this exact
deployed code as v5 and pin `core-title-rail` to it. That freeze is not yet made.

## Final public browser and stored-code verification

[Public run 42667176429](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42667176429),
source `f4a03e677a07e6e488c8aa5b7546c5e306db94ef`, finished
**17:49:09 UTC, PASS**. Root read all **231 decoded report lines**, including the
geometry records and every nested result (`/tmp/peek-title-rails-public-1-text.md`).
This run reached the actual staging39 pages without a hosting challenge.
The nine separately executed suites total **11,829 passed, zero failed**:

| Suite | Chromium | Firefox |
|---|---:|---:|
| New title rails | 1022/0 | 1018/0 |
| Retained title strip | 1110/0 | 1107/0 |
| Clearance | 3340/0 | 3340/0 |
| Native touch | 378/0 | Not a Firefox suite |
| Generic approved pages | 172/0 | Not run in this public wrapper |
| Matched-capture diagnostic subset | 342/0 | Not a Firefox suite |

The title and rail matrices each covered **21 actual routes**. All child and
effective exit codes were zero; the complete public wrapper ran **358 seconds**.
The diagnostic subset repeats selected clearance scenarios in a separate run;
this total is not a claim of 11,829 distinct behaviors. Replayed wrapper summaries
are counted only once. Local structural fixtures and the full Thai 486/0 suite
belong to the preceding fresh-copy rehearsal, not this public run. The known
baseline Firefox practice-rail Enter issue remains explicit INFO at three widths.

The independent artifact audit read all 23 archived logs: nine leaf stdout logs,
two aggregate stdout logs, eleven empty stderr logs and the runner log. All
11 distinct wrapper/suite verdicts have zero child/effective exit codes; no
FAIL, INVALID, uncaught error, timeout or hosting-challenge marker was found.
Actual Search form checks pass **168/168**. The public archive is **18,285,336
bytes**, artifact **13697746939**, SHA256
`77625abd4bfb032c0ccde404cf2326a63c31c19ed6d100fb13aa8a956ebe83e5`.

There are **110 capture events and 104 distinct filenames**: six clearance
names recur in the full and diagnostic suites. Both original events remain in
the complete leaf logs. All 104 final images were assessed against rehearsal3:
47 are byte-identical to already reviewed images; the visual agent inspected all
57 changed/new images, with no new persistent layout blocker. The 40 title-rail
views comprise 11 identical and 29 changed images; all six Search form states
remain usable on both sides. All 12 changed retained clearance images differ
only within the narrow rail bounds. Six additional clearance views and ten
changed initial/title/demo views were also inspected.

The [public capture manifest](assets/2026-11-09-peek-title-rails/public/capture-manifest.json)
records every image hash and comparison. It retains **41 original images** here:
all 40 title-rail views and the Therapeutic phone research view. The remaining
63 files stay in the full artifact. Root separately viewed
[Services on phone](assets/2026-11-09-peek-title-rails/public/title-rail-services-390.jpg),
[Search with the left rail](assets/2026-11-09-peek-title-rails/public/title-rail-s-massage-768-form-left-form-landed.jpg)
and [Therapeutic research on phone](assets/2026-11-09-peek-title-rails/public/clearance-therapeutic-massage-390-right-top-6.jpg).
These show the compact title-only box, clear form controls and retained research
scroll area; they do not establish physical-device behavior.

[Stored inspection 42667209210](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42667209210),
source `eb1ed7143e78a5b1ca7798ddd490189b35ce5872`, finished
**17:49:42 UTC, PASS**. Root read the complete report. It confirms published
#1380 at **97,130 code bytes**, SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`,
and all four original published data records:

| Data | Snippet | Bytes | SHA256 |
|---|---:|---:|---|
| Thai | 1381 | 4376 | `fd67d8f8548706bd8597af87e85380581a18f846a1bf71de4401db80b5f0a7e0` |
| Therapeutic | 1382 | 3728 | `3b12f5e09f6937f41f04122cbed3c6ef6244c9359115ce36ed31d2d6b7336ffd` |
| Deep Tissue | 1383 | 3408 | `c88e05ae019076e9a173dc239ffa2b091007864087067ef0444efca2acc00101` |
| Chair | 1384 | 3923 | `b99558619488cf1506c8b0e8ca2c59066068eaa0727233c90b51f599798a121c` |

Physical-device, Safari and actual assistive-technology checks remain open. The
older Thai compatibility-mouse focus issue is unchanged, and the previously
reported rapid-switch loading delay is not claimed resolved by this rollout.

# Peek rails without new summaries

**Candidate only; not deployed.** Ryan requested shared Peek rails on pages
that do not already have rails, without adding summaries. This follows the
[deployed all-page title strip and larger summaries](2026-11-09-peek-title-strip-all-pages.md).
Staging39 is the only deployment target; main and live remain untouched.

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

The deployed core #1380 has **86,567 code bytes**, SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`.
It supplies title strips to visitor pages and summary rails to the four approved
service pages. Stored inspection **42601221631** confirmed that exact core and
unchanged data #1381–1384 at **10:30:40 UTC**. The preceding final focused public
suite was **INVALID HTTP202**, with zero assertions/captures. Its passing
fresh-copy suite and separate 20 deployed comparison captures remain baseline
evidence, not checks of this candidate.

## Proposed shared behavior

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

`peek-pages.json` now records this pending `titleRail` scope separately from the
enabled title strip and four approved summary pages. It names the two existing
rail paths and the conditional Progress E rule explicitly. The old undecided
rail-eligibility list is removed; the four `pages` entries and all summary data
remain unchanged. Browser checks must verify these exceptions rather than infer
eligibility from whether a rail happened to appear.

## Verification and guarded transition

The deployed code has been frozen as `peek-nav-shared-v4.php`; its code bytes and
hash match the stored baseline above. The proposed `core-title-rail` transition
starts from exact v4, while `core-title-strip` is now pinned to v3→v4 so its
actual 10:24:52 snapshot retains its recovery meaning. New installer checks and
runtime behavior are still under implementation/review. The first rehearsal
failed as recorded below; there is no staging apply or new staging snapshot.

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
These are pending corrections, not a passing result; the failed run remains failed.

All **30 actual-page captures were reviewed**, with no additional visual blocker
reported. Work With Me's desktop seventh closed row uses an ellipsis; its full
title remains an explicit browser-check requirement when opened. This visual
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
failed rehearsal. Staging writes remain blocked pending a complete passing
fresh-copy/visual gate and a fresh verified off-site backup; no new staging
recovery snapshot exists.

## Corrected candidate for a second fresh-copy rehearsal

The current core has **96,717 code bytes**, SHA256
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

Current local title-rail fixtures: **440/0 Chromium,437/0 Firefox**, with14 local
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
uses the same source and is still running; no staging write has been requested.

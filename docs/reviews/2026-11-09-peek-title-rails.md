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
runtime behavior are still under implementation/review. There is no new staging
snapshot or claimed rehearsal/deployment result.

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

## Actions Gates and first rehearsal request

[Actions Gates 42656502403](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42656502403)
passed at **16:37:23 UTC** for source
`9996b172bcbf9cbb4354431189d965edb1d87a70`: **29 PASS, 1 SKIP, 0 FAIL,
0 INVALID**. The complete 193-line job log was read
(`/tmp/peek-title-rails-actions-gates-1.log`). Shellcheck, ruff, pinned PHP,
ownership/recovery/current-page emission, wording and builder gates passed.
Only URL-dependent rendered verification was skipped.

The [first fresh-copy rehearsal, 42656502274](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42656502274)
is running; no rehearsal verdict or actual-page capture review is claimed yet.
Staging writes remain pending the rendered-copy and visual gates plus a fresh
verified off-site backup. No new staging snapshot exists.

# Peek template: independent review and migration record

Work is confined to staging39 and `claude/wizardly-bell-xu0rta`. No live change or main merge/push is authorized. This record separates observed evidence from design judgment and pending verification.

## Fresh starting evidence

- Pulled the requested branch: latest starting commit `4bc0fc81468a0674f7667cb0068a759d2dd49f1e`; clean checkout.
- GitHub connector confirmed repository pull/push permission. Current cloud instance reports unrestricted/enforced network policy. A fresh connection reached SiteGround but returned HTTP 202 `sg-captcha: challenge`; direct Chromium produced no usable page capture. No repeated denial loop was used.
- [Fresh full-page screenshots](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42547556009), request `peek-review-s39-20261109-1`: two real staging39 captures, no capture errors. This is independent evidence, not the previous session's historical 485/0 suite.
- [Exact snippet inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42547837118), request `peek-owned-s39-20261109-1`: #1380 published, title `TSPeekNav practice (Thai, staging39)`, 66,069 code bytes, SHA256 `b7314c8938a728bf64b6dac16c48bba722b4aea59b277c9f56f0760132322c01`. The full job log's hash matches the frozen legacy payload. The summary report truncates the first part, so it alone is insufficient for this comparison.
- [Reading/index/research preview captures and rendered section text](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42548017902), request `peek-design-s39-20261109-1`: Thai at 390px/1280px and the three rollout pages. No JavaScript errors in these five loads. Thai scrollWidth equals viewport width in both views. These are focused captures, not a full interaction suite.

## Independent design judgment

The muted rail, warm neutral palette and readable typography fit the site. On the phone, the open research stack stays within the viewport and preserves its key qualifications. The fan gives a compact overview while retaining substantial free space. The header and title strip make the reading position clear at section starts.

The first-time hint covers a substantial part of the hero image. Repeated large illustrations lengthen the route to text on the phone. While scrolling, the temporary current-section name bubble overlays body text even though the rail itself is clear. These are observed tradeoffs; no redesign of the hint, imagery or settled gestures is bundled into the core migration.

Research occupies about half the measured mobile rail (219px of 437px of segment heights); the shorter sections still have 38–71px segments in this capture. This supports reviewing capped segment proportions later, but does not establish that every device or expanded-panel state needs that change. The strip's progression toward the next title remains the known separate design decision.

![Phone reading state](assets/2026-11-09-peek/thai-massage-390-reading.jpg)
![Phone section index](assets/2026-11-09-peek/thai-massage-390-index.jpg)
![Phone research preview](assets/2026-11-09-peek/thai-massage-390-research.jpg)

[Desktop index](assets/2026-11-09-peek/thai-massage-1280-index.jpg) · [Desktop research preview](assets/2026-11-09-peek/thai-massage-1280-research.jpg)

## Synthesis with prior findings

The earlier text-block clearance fix is supported at the two captured widths; that does not establish all widths. The source concerns about rollback, native details, row indexing and print were worth addressing. The old installer really did ignore its snapshot's code hash before deletion; the old push rollback also omitted a fresh backup. Existing broad post-change/restore tools did not accept staging39, and an overall operation PASS did not establish those informational checks passed.

The shared design retains `.ts-jump` as the only destination/order source. Per-page JSON provides wording, approval, coverage judgment and provenance. It is stored in WPCode, not fetched from remote JSON. Draft phrases remain in storage and are removed from public JSON; whole paragraphs remain the fallback. Exact-duplicate/schema/wording lint cannot establish semantic accuracy, non-overlap or percentage coverage.

A safer migration installs inert Thai data first, then changes the exact existing core in place. The complete original snippet record is snapshotted for rollback. There is no planned fallback-only swap interval; if a gate blocks migration, the old Thai Peek remains active. Removing page data later deliberately restores the plain jump-row fallback for that page.

## Rollout decisions

Thai remains the only enabled page. Therapeutic and Chair have six sections, with Questions before What to Expect; Deep Tissue has five. They require their own wording approval. [Draft wording and rendered-source review](2026-11-09-peek-summary-drafts.md) records that separate decision.

Services and short pages are pending individual assessment, not automatically excluded or enabled. Seven service choices can make destination-finding useful despite short text. Conversely, a compact two-panel page may already work well with its plain row. A 1,500-word cutoff cannot decide this by itself.

## Verification and deployment

Implementation and checks are in progress. No staging write has been performed by this session yet. The current recorded staging snapshot remains `peek-nav-before-20261109-010037-0c493281.json`. Do not run an old rollback without checking present ownership/state.

Safari, physical devices, VoiceOver and TalkBack remain unverified. Synthetic clicks are not screen-reader tests.

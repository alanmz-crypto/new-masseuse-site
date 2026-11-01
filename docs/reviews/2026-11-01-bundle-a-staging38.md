# Review Bundle A — staging38 verification

Bundle 0 and Bundle A are complete on staging38. PR:
https://github.com/alanmz-crypto/new-masseuse-site/pull/61
Live is disabled. No production change or live request was made.

The Butsaba draft is `docs/inter-model/2026-11-01-butsaba-questions.md`.
Ryan sends it; no answers have been assumed and no message was sent by the agent.

| Evidence | Result |
|---|---|
| Fresh staging38 dump and independent snippet hashes | PASS; archive checksum and frozen snippet verified |
| Offline code gates and GitHub Gates | PASS |
| Rehearsal `review-a-rehearse-20261101-4` | PASS: apply, repeat apply, exact rollback, re-apply, pages 200, no new PHP errors; rendered Home and client-page gates PASS |
| Dry run `review-a-dry-staging38-20261101` | PASS; every part ready |
| Apply `review-a-apply-staging38-20261101` | PASS; server checks on all nine changed pages |
| Post-change rendered gates and security posture | PASS |
| Structured data on Home, Contact, Services and Thai | Identical apart from dateModified |
| Zero-tolerance pixel comparison | Expected differences: 14 changed views; 6 identical; no missing captures |
| Restore proof | Export imported successfully; 88 fingerprints per side; only intended resources, revision rows and WPCode cache differ |
| Browser `review-a-browser-staging38-20261101` | PASS: 262 assertions, nine pages, 1280/768/390/320 px, light and dark; no side-scroll or script errors |

Reports on `ops-log`:

- [Rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/020921-review-a-rehearse-20261101-4.md)
- [Dry run](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/020953-review-a-dry-staging38-20261101.md)
- [Apply, post-change tests and restore proof](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/021434-review-a-apply-staging38-20261101.md)
- [Browser checks and inline screenshots](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/021615-review-a-browser-staging38-20261101.md)

The phone screenshots of the Thai closing block, Contact corporate card and
Corporate sticky bar were decoded and visually reviewed. No clipped text or
layout problem was seen. Browser checks cover jump targets, cross-links,
Services order/availability, closing buttons, phone-only sticky visibility,
button size and underline styling.

Added height in pixels (same DOM with additions shown/hidden, not a pixel proof).
The seven pages in the automatic comparison agree with its independent live
reference heights. Chair and Corporate are covered by the custom checker.

| Page | 1280 px | 390 px |
|---|---:|---:|
| Home | 0 | 0 |
| About | 352 | 373 |
| Services | 354 | 447 |
| Thai | 419 | 459 |
| Therapeutic | 430 | 470 |
| Deep Tissue | 420 | 460 |
| Chair | 430 | 469 |
| Corporate | 52 | 135 |
| Contact | 277 | 366 |

The 14 pixel differences are Home, About, Services, Thai, Therapeutic, Deep
Tissue and Contact at desktop/phone widths. Work With Me, Privacy and
Accessibility are pixel-identical in all six views. No claim that all pixels
are identical is made. No PHP errors, warnings or deprecations from site code.

Backup: `staging38-before-bundle-a-20261101-021038.sql` (5,392,194 bytes), copied
off-server with identical SHA256, encrypted into `offsite-2026-11` and read back
identically before any site write. Snapshot
`bundle-a-before-20261101-021051.json` also copied, encrypted and verified.

Exact staging rollback through Actions:

```sh
scripts/ci/request.sh run --target staging38 --script review-a/push-bundle-a.sh --args '--rollback $HOME/private-backups/bundle-a-before-20261101-021051.json'
```

Rollback takes another fresh export and verified off-site copy. It refuses
unless every whole page, snippet and CSS post still holds exactly the applied
bytes. Rehearsal proved exact restoration. No staging rollback/delete occurred
after the successful apply.

Boundaries:

- Footer widget deferred: no unchanged-layout proof at 30 widths.
- Existing Spectra FAQ tab roles remain until Bundle C; the three approved Chair
  links make that existing limitation relevant there too.
- Older builders/rollback installers describe earlier exact states. Roll A back
  before using them against changed staging resources. Live remains unchanged.
- No Butsaba facts, credentials, deletions, theme update, www redirect or menu
  label change.
- Bundle G must refresh **15** date sentences after A, including Services and the
  five new closing blocks, only on/after November 14.

Next: Ryan reviews staging38/PR #61 and sends the Butsaba draft. His live go is
required before adding the live host/request to this same PR. He alone merges
and starts Site ops (LIVE).

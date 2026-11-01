# Bundle E: historical live release preparation

This records preparation reviewed before Ryan merged #66 and applied E at 19:14 UTC on November 1. See the [actual live result, snapshot and verification](2026-11-01-bundle-e-live.md); never rerun the completed apply ID. At preparation time A/B/C/D were live; [D’s actual proof](2026-11-01-bundle-d-live.md) records 53 origin and 128 public browser assertions passing, with the automatic HTTP 202 attempt separately INVALID.

## Approved scope

The original [staging scope and proof](2026-11-01-bundle-e-staging38.md) remain unchanged: four image-only replacements on Home/Therapeutic, the same 400×313 WebP, verified intrinsic/responsive attributes on four pages, two existing font preloads and frontend-only emoji suppression. No prose, CSS, SEO or site-setting edit; the normal WPCode cache refresh is recorded in the live proof. The Home Deep/Thai illustrations now match their service pages. All ten originals and existing responsive derivatives remain unchanged. Work With Me has two actual content images; no third image was invented.

The prepared red WebP remains 21,796 bytes, SHA256 `e795b73e4272e90f92db59b2734be41dd06365ac8ca6f0e8aa5e02d11ee1a756`, versus the unchanged 616,299-byte PNG: 96.46% smaller. No re-encoding or substituted asset is included in this release.

## Fresh live source and compatibility

[Read-only capture, run 41339677797](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/190302-review-e-live-source-20261101.md), archive SHA256 `d9fb4398b88864a4b33f92e9b540504bbecc3fc2734082b87c78423bf77f2df2`, consistently selected the live apex without public requests. It reports WordPress 7.1.4, Astra 4.14.1, CLI PHP 8.5.12 (not web PHP), front page 296 and fifteen published pages. All nine D page hashes match the verified applied D profiles. Ten original file SHA identities and attachment metadata match; six responsive derivatives retain the same filenames, dimensions, MIME and byte sizes; the privately generated conversion is byte-identical to the packaged WebP. Both new snippet names are absent.

The frozen four pairs, staged hashes, runtime files and approved asset are unchanged. Actual live Home/Therapeutic before/after hashes were computed from this capture; existing LOCAL profiles match without normalization. No attachment ID is used to select a new image. Exact-apex guards were added consistently to installer, push, mandatory origin verifier and focused browser checker.

The installed public core was reviewed against the original 7.1.3 source. Emoji functions/registration excerpts, hook priorities/arguments and recorded source hashes match byte-for-byte. Image block metadata is identical; image.php changes only lightbox-button insertion. The ID-free image path remains compatible. Fresh media.php:2021 retains images without attachment IDs as ID 0 and line 2072 calls the three-argument wp_content_img_tag filter, so the approved rendering callback receives them. Strict version profiles remain staging38=7.1.3, localhost/apex=7.1.4; later drift refuses rather than accepting a range.

## Validation

[Fresh live-copy uploads rehearsal, run 41339834933](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/190757-review-e-live-rehearse-20261101.md) **PASS: 1,664 assertions, zero failures** (C 1,248 + D 128 + E 288). The source is WP 7.1.4; isolated uploads symlink preparation, apply/no-op/exact fingerprint rollback/reapply, regulated Home/client gates and no new site PHP errors all pass. Fingerprints move only Home/Therapeutic, the two new snippets and WPCode cache (related media/post/meta/term rows). All eight image frames and page heights remain unchanged at three widths/light+dark (48 image states). Three phone previews—Home Deep/Thai and Therapeutic red—were visually reviewed.

[Actual live read-only preflight, run 41340220974](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/190905-review-e-live-dry-20261101.md) **PASS**: Home/Therapeutic and both new snippets ready; WebP ready to import once, ten originals preserved. No content write occurred.

Final Gates 41340334386 passed on 32b8ddb; mergeability/comments were checked before #66 became ready. Source compatibility is supported by the full runtime rehearsal, including exact rollback and the logical-versus-physical uploads path hook.

## Completed handoff to Ryan

Ryan reviewed/merged #66 and personally dispatched `live-review-e-20261101-1` from main. The request is completed and must not be reused. The actual before-snapshot is `bundle-e-before-20261101-191447.json`; live verification and created IDs are recorded in the [live proof](2026-11-01-bundle-e-live.md). No further live apply is required.

## Exact rollback and remaining work

WordPress-returned logical upload paths are passed to media APIs; realpath serves containment/hash checks only. The rehearsal creates the uploads symlink before baseline and never deploys this helper. Prefix collisions refuse. Uploaded bytes and each created ID are snapshotted immediately. Rollback requires exact full post/meta/terms/file matches and removes only unchanged resources owned by this snapshot; existing/reused assets are preserved. SureRank rows and all unrelated stores remain protected. After apply, reverse E→D→C→B→A using actual live snapshots and a separate Ryan-owned LIVE request.

C’s strict public security.txt test still fails 404/text-html; its approved static payload is uninstalled, owner-hosting follow-up. Intermittent AntiBot remains open; passing focused tests does not establish a durable fix. No static server-file/hosting-rule/protection change or support message. F/admin access, Butsaba’s pending facts and session-length consistency, G after November 14 and owner-only credential/deletion/Astra/www/menu work remain separate.

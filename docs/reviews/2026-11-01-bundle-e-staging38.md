# Bundle E: staging38 performance

**Status:** prepared; no E changes remain applied after the first staging attempt recovered. Draft PR #66 is stacked on applied D (draft PR #65). Live remains disabled. Offline gates and isolated uploads rehearsal 3 pass; staging dry run is next.

## Verified source and changes

Fresh capture confirms all nine applied D page hashes. Read-only image metadata and installed core source were accessible. A private copy of the PNG was converted with WordPress’s GD image editor, quality 82, outside the webroot; its original SHA is unchanged.

| Item | Verified change |
| --- | --- |
| Therapeutic PNG | 616,299 bytes, 512×400 → WebP 21,796 bytes, 400×313 (96.46% smaller) |
| Home Therapeutic card | Reuse its existing service-page illustration; same bytes |
| Home Deep Tissue/Thai cards | Their current pictures are swapped; correct each to its own service-page original |
| Corporate illustration, Home/Services | Add rendered intrinsic dimensions 512×416 |
| Work With Me portrait | Actual file 360×540, despite the old Home block’s 360×420 declaration |
| Work With Me cutout | Actual file 1254×1254; public upload has no attachment row |
| Fonts | Preload the two already-used normal Latin Hanken Grotesk/Newsreader WOFF2 files |
| Emoji | Remove verified frontend head/footer detection and styles; preserve admin, mail and RSS behavior |

There are two Work With Me content images, versus the review’s stale count of three. The two header-logo copies already have dimensions. No third image is invented. All ten originals and existing thumbnail files are preserved.

Only two stored pages change: Home’s three core image blocks and Therapeutic’s red image block. Four exact pairs use relative URLs and remove stale attachment IDs/classes. Existing display sizes, crops, alt text, borders and every word of prose stay unchanged. A source gate proves each old/new pair differs only in image markup. The existing Home frames remain 6:5.

Installed core/image width/height attributes represent CSS resize strings. Intrinsic dimensions and responsive attributes therefore use WordPress’s image-render filter in a new WPCode snippet, keeping the saved core blocks compatible with the editor. The filter touches the reviewed four pages only, preserves existing dimensions/attributes, and uses verified public paths without database lookups. It adds lazy loading to below-fold placements; portrait/service heroes are preserved. A second PHP snippet removes only frontend emoji output using the installed 7.1.3 callbacks, including the late footer callback.

The PNG source SHA is `ffbb797aee0364a4d2146ab1d82acf43916cad0b5fa0b9fa73faedf6f10ed080`; prepared WebP SHA is `e795b73e4272e90f92db59b2734be41dd06365ac8ca6f0e8aa5e02d11ee1a756`. The prepared original/WebP were visually compared: same red illustration, preserved composition. The original PNG is not edited or deleted.

## Safety and verification

The installer checks the frozen two-page hashes, all ten original file hashes, existing responsive file dimensions, exact prepared WebP bytes and runtime syntax. All apply/rollback work uses staging38 or isolated localhost only. The whole old page stores and SureRank rows are snapshotted; no existing snippet, CSS, SEO row or option is edited. New media/snippets are captured immediately. A moved upload is snapshotted before attachment creation, so failure before insertion remains recoverable. Existing exact WebP reuse is unowned and is never deleted by rollback.

Rollback refuses changed stores or owned post/meta/terms/files, restores/read-backs old pages and SureRank rows first, then removes only unchanged resources created by that snapshot. Fresh DB export and verified encrypted off-site copy precede any staging apply/rollback.

First rehearsal stopped before any apply: old image fragments needed host templates for the localized localhost copy. The per-host page hashes matched, but fragment URLs had retained the staging hostname. Corrected without any staging write. Report: `reports/2026-11-01/112935-review-e-rehearse-staging38-20261101-1.md`.

Second rehearsal exercised recovery: WordPress’s upload handler requires its file array by reference; the temporary literal caused apply to stop before an upload. Old stores were restored/read back, private temp cleaned, and no staging write occurred. The handler now receives a standard named upload array. Report: `reports/2026-11-01/113514-review-e-rehearse-staging38-20261101-2.md`.

Rehearsal 3 PASS: apply, no-op reapply, exact rollback, reapply, rendered regulated-copy/Home gates and no new PHP errors. C’s 1,248 and D’s 128 assertions pass, followed by the full E contract on four pages: all eight image placements at 1280/390/320 light+dark, unchanged image/figure/page geometry against original image DOM, responsive sources, delivered exact WebP, two font preloads and no emoji DOM/network output. The E child completed all four pages with exit 0; the report filter initially omitted its numeric summary, so no unsupported exact E assertion count is claimed. The filter now retains C/D/E/F summaries without changing test behavior. Report: `reports/2026-11-01/114001-review-e-rehearse-staging38-20261101-3.md`.

Home Deep/Thai and Therapeutic red 390px figure captures were visually reviewed: correct canonical pictures, preserved frames, no clipping. Existing Home/Services/Work With Me geometry is unchanged. Both GitHub Gates checks for source `b080f765dc69a55c08b99857519d87a4e698ae61` pass (41302663732/41302659604).

Pending: staging dry/apply/origin checks and restore proof. Public interactive checks remain pending while the hosting challenge is intermittent. No public retry loop or live action.

Source reports:

- `reports/2026-11-01/105430-review-e-capture-staging38-20261101.md`.
- `reports/2026-11-01/110142-review-e-source-webp-staging38-20261101.md`.

The first emoji inspection returned no callbacks because a physical-path check did not account for symlinked core files. A realpath-based follow-up captured actual callbacks and public core source before implementation. Raw binary SHA comparisons also corrected the Home image mapping; no same-card identity claim is made for Deep Tissue/Thai.

## Remaining lanes

Fresh origin probing confirms `/.well-known/security.txt` still returns 404 on staging38: C’s virtual route works in Apache rehearsal but is not being served through SiteGround’s path handling. The prepared static payload in review-c/security-staging38/security.txt remains a Ryan-owned upload/fallback under the handoff; the approved mailbox stays butsabad@gmail.com. The SiteGround AntiBot support draft has not been sent and no hosting setting changed.

Butsaba’s unanswered facts and confirmed session-length consistency work remain separate. F remains, G waits until November 14, and every live go/run remains Ryan’s. After E is applied, roll E back before D/C/B/A or older exact-state installers.

## Staging-path correction before retry

The first staging apply stopped at metadata validation and recovered exactly:
`reports/2026-11-01/114735-review-e-apply-staging38-20261101.md`. Fresh export
`staging38-before-bundle-e-20261101-114718.sql` (6,982,630 bytes) was verified
encrypted off-site before the attempt. Incomplete snapshot
`bundle-e-before-20261101-114734.json` remains private.

Read-only diagnosis PASS:
`reports/2026-11-01/115017-review-e-upload-paths-staging38-20261101.md`.
The configured logical uploads base differs from realpath on SiteGround;
WordPress’s relative-path helper recognizes the logical path but leaves the
physical path absolute. The failed attachment metadata had an absolute path.
Home/Therapeutic still match their exact old bytes, the temporary attachment
and both snippets are absent, and zero of the three owned upload files remain.

The installer now passes the logical path returned by WordPress to its media
APIs, using realpath only for containment/SHA guards. A package preparation hook
reproduces that symlink distinction only inside the isolated localhost rehearsal;
its PHP guard requires localhost and the local environment, and the staging push
does not upload this preparation file. Rehearsal 4 must pass before any retry.

Rehearsal 4 PASS with the observed symlink setup:
`reports/2026-11-01/115850-review-e-rehearse-staging38-20261101-4.md`.
Both logical/physical path conditions are reproduced; apply, no-op reapply,
exact rollback, reapply, regulated-copy gates and no new PHP errors pass.
C 1,248 + D 128 + E 288 = 1,664 browser assertions pass. All 8 reviewed placements
retain geometry across three widths/light+dark; delivered WebP bytes,
responsive images, preloads and emoji absence pass. Fresh dry run before retry.

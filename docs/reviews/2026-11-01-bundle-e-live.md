# Bundle E: live verification

Ryan merged PR #66 as `b1bb48b1a30a848de94b5ff720319e2bd92729c1` and personally started `live-review-e-20261101-1`. E applied on 2026-11-01 at 19:14 UTC. A–E have now shipped through Ryan’s own runs; F and the owner/fact/date lanes remain separate.

E delivers the approved four image-only replacements on Home/Therapeutic, the same 21,796-byte red WebP, verified intrinsic/responsive attributes, two existing font preloads and frontend emoji suppression. All ten originals remain unchanged. No prose, CSS, SEO or site-setting change; WPCode’s normal cache refresh is recorded below. The [staging proof](2026-11-01-bundle-e-staging38.md) and [historical release preparation](2026-11-01-bundle-e-live-preparation.md) retain the reviewed scope and 1,664-assertion rollback rehearsal.

## Actual results and limits

| Check | Actual result |
| --- | --- |
| Ryan’s live apply | [PASS, run 41340701287](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/191802-live-review-e-20261101-1.md), including verified encrypted off-site DB backup before writing |
| Origin E checks | **26 assertions, zero failures**: four pages, image attributes, font preloads, emoji absence and exact delivered WebP bytes |
| Generic public post-change | **FAIL**, limited to historical staging36 schema/pixel comparisons; the site answered 200 and testing completed |
| Generic code/rendered/posture/pages/PHP checks | PASS, including regulated copy and links; ten pages plus sitemap/robots answer 200; no site-code PHP errors/warnings/deprecations in the last 120 minutes |
| Historical schema comparison | 27 candidate items versus 23 reference, zero parse errors; the four B-era differences are unchanged |
| Historical pixel comparison | All 20 views differ from the older reference; zero missing captures/problems. This is not a pixel-identical pass |
| Restore import | Exit 0; 92 before/94 current fingerprints; intended E differences only, no SEO or Additional CSS differences |
| Native ownership and focused public browser check | Native ownership proof **9 checks PASS**; focused public E browser check **INVALID** (hosting robot challenge on the initial WebP request, zero assertions). It remains pending access; no retry loop. |

The restore differences are Home/Therapeutic, two new snippet rows, WPCode cache, related post/meta/term rows and security visitor/log rows. The native proof below confirms SiteReviewPerformance 1332, SiteReviewEmoji 1333 and media 1331. No unrelated page, SEO, CSS or site-setting fingerprint moved.

The generic command remains FAIL for the historical comparisons. The native/focused E result is a separate verification; it does not turn the comparison result into all-pass or establish a durable hosting AntiBot fix. C’s known public security.txt 404 remains an uncorrected hosting follow-up.

## Native records and public appearance

[Read-only result check, run 41342103314](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/193208-review-e-live-verify-20261101.md): **all nine native checks pass**. Current Home 296 and Therapeutic 1107 equal the frozen applied whole-page hashes and exact snapshot stores, with raw SureRank rows unchanged. All 16 original/previous responsive file SHA/dimension/MIME/byte states match both snapshot sides. SiteReviewPerformance **1332** and SiteReviewEmoji **1333** match full snapshot posts/meta/terms and approved runtime hashes. New WebP attachment **1331** matches full snapshot post/meta/provenance, relative logical upload path, prepared bytes and all **three owned files**. The exact completed snapshot, canonical package checksum and owned prefix/file set match; no content writes were attempted.

The same run then reached SiteGround’s Robot Challenge on its initial WebP request, before any page navigation. The focused E suite stopped immediately with **0 assertions, INVALID**; no image-frame, interactive or emoji-network assertion ran on the public site. Its previously reviewed **288 assertions** passed only in the live-derived rehearsal, within 1,664 total. The report’s overall INVALID does not erase the nine native passes, and those passes do not establish a public browser pass. No public retry was attempted. Per Ryan’s stop-on-access instruction, further public verification waits for SiteGround access that permits the GitHub Actions checker.

The WebP is 400×313 and 21,796 bytes, SHA256 `e795b73e4272e90f92db59b2734be41dd06365ac8ca6f0e8aa5e02d11ee1a756`: 96.46% smaller than the unchanged 616,299-byte source PNG. The Home illustrations match their canonical service pages. The live-derived rehearsal verified the existing image frames; focused public geometry remains untested. No saved attachment IDs were substituted.

## Actual backups and exact rollback

| Resource | File under private-backups | Verification |
| --- | --- | --- |
| DB export | `live-before-bundle-e-20261101-191435.sql` | 5,976,437 bytes; plaintext SHA prefix `1e319bac239b`; verified encrypted off-site before writing |
| Before-snapshot | `bundle-e-before-20261101-191447.json` | plaintext SHA256 `0ffe75e3aa18c34d5132670f812224b6cc3e152d854cb5b335cafc31f1720f75`; verified encrypted off-site before origin verification |

For any rollback, prepare a separate Ryan-owned LIVE request for `review-e/push-bundle-e.sh --rollback $HOME/private-backups/bundle-e-before-20261101-191447.json`. The push takes another fresh verified export first. The installer refuses exact-store or ownership drift, restores saved pages/SureRank rows, and removes only unchanged media/files/snippets owned by this snapshot. Existing/reused resources are preserved. Reverse E→D→C→B→A; never substitute a staging snapshot. If a later settings bundle changes the home URL, undo that bundle first so E’s exact captured home still matches.

## Remaining work

The A–E deployment is complete; focused E public verification remains blocked. No further LIVE request is prepared by this proof update. F #67 remains paused for admin access. C’s approved static security.txt is uninstalled; hosting/AntiBot remediation, Butsaba’s pending answers and session-length consistency, G after November 14 and owner-only credential/deletion/Astra/www/menu work remain open. No hosting rule/static-file/protection change or external message was made. [Current release record](2026-11-01-review-live-release.md).

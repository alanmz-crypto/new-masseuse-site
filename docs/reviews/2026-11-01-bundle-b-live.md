# Bundle B: live and verified

Ryan merged release PR #69 as dc4011e1acb918dc490dc5483e246a71285e9079 and personally ran `live-review-b-20261101-1`. B applied on 2026-11-01 at 16:31 UTC; strict live verification completed at 17:01. The agent did not merge main or dispatch LIVE.

B releases the reviewed search/social copies, Person/LocalBusiness links, tagline, Corporate HTTPS sharing URL, Home's “by arrangement” wording and the approved default/Home preview card. No pending service fact or hosting control changed.

## Evidence

| Check | Result |
| --- | --- |
| Staging proof | [PASS](2026-11-01-bundle-b-staging38.md): uploads rehearsal, dry/apply, rendered gates, restore import, strict 12-page semantic scan, Home captures |
| Fresh source compatibility | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/161110-review-b-live-source-20261101.md): WP 7.1.4; all 15 directly relevant SureRank API/render files match reviewed source; current logo SHA unchanged |
| Fresh uploads rehearsal from live | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/161516-review-b-live-rehearse-20261101.md): media import, repeat apply, exact fingerprint rollback/reapply, pages 200, regulated Home/client gates, no new site PHP errors |
| Actual live dry | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/161722-review-b-live-dry-20261101.md): every part ready |
| Ryan's live apply | [PASS, run 41326724484](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/163602-live-review-b-20261101-1.md): fresh export verified encrypted off-site before writing |
| Origin/rendered/security/PHP | Ten changed origin pages 200 with Person/licence; Home/client/posture gates PASS; no site PHP errors |
| Strict live semantic scan | [PASS, run 41329251788](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/170126-review-b-live-semantic-20261101.md): all 12 pages 200, zero expectation failures |
| Card | Live attachment 1305; approved PNG 1200×630; default/Home OG+Twitter use exact HTTPS URL and dimensions; SHA256 MATCH |
| Restore import | Exit 0; before 88/current 90 fingerprints; intended differences reviewed below |

Approved card URL: `https://thaisolitude.com/wp-content/uploads/2026/11/thai-solitude-share-card.png`. SHA256: `7b769b1a2b82b2cb3ef2bf11a5e6ba65f271b963952fdfe1041c53b6ade5319d`. The card and B payload were unchanged from Ryan's “Yes, use this card” approval. Existing media remain intact.

Strict scan confirms exact metadata/social copies, canonical/robots, Person identity/jobTitle/links, LocalBusiness fields/founder, global fallback and Home image bytes/attachment dimensions. It reports zero parse errors or expectation mismatches. Live robots and sitemap answer 200 and advertise the HTTPS sitemap.

The automatic post-change command exits 1 because expected structured-data and historical-reference pixel comparisons differ. Candidate schema 27 vs reference 23 is the approved new Person/business enrichment on four checked pages, with zero parse errors. All 20 screenshots exist, none missing, against older staging36. Home dimensions remain exactly the same as A's preceding live automatic captures: 1280×4570 and 390×5923. This is not a pixel-equality or aggregate all-pass claim.

Restore import itself exits 0; the command exits 3 because intended changes differ from its before-export. Reviewed store differences: Home, LocalBusinessInfo 1023, seven general metadata groups (including new Privacy/Accessibility rows), About/Contact schemas, Home and Corporate social rows, tagline/global SureRank settings/WPCode cache, new-media/revision/postmeta rows and SiteGround visitor/security-log activity. No Additional CSS or other page/snippet fingerprints differ.

## Actual live backup and rollback

| Resource | File under private-backups | Verification |
| --- | --- | --- |
| DB export | live-before-bundle-b-20261101-163126.sql | 4,908,851 bytes; plaintext SHA prefix 4e5befad4d01; verified encrypted off-site before apply |
| Before-snapshot | bundle-b-before-20261101-163141.json | plaintext SHA prefix 199d64fc1e31; verified encrypted off-site after apply |

No live rollback was run. Exact rollback was rehearsed before release on the fresh isolated copy. If needed, prepare a separate Ryan-owned LIVE request for `review-b/push-bundle-b.sh --rollback $HOME/private-backups/bundle-b-before-20261101-163141.json`. It takes/verifies another export first; raw-store or new-media post/meta/file drift refuses. Restore old content and exact metadata/options before removing only this snapshot's unchanged new attachment. Do not use staging's snapshot.

After C/later touching bundles apply, undo those before B; undo B before A. Current planned reverse order is E→D→C→B→A. Both actual A/B live snapshots remain private and verified off-site.

C preparation continues in draft #64 with fresh live predecessor bytes and current main integrated. B verification succeeding on this run does not establish a durable hosting AntiBot fix. F/admin options, static security.txt delivery, pending service facts, G timing and owner-only work remain deferred. [Current release plan](2026-11-01-review-live-release.md).

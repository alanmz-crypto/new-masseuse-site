# Review release: A live, B ready for Ryan

**A is live and verified. B is prepared, not applied.** Review and merge [B release PR #69](https://github.com/alanmz-crypto/new-masseuse-site/pull/69), then personally start its LIVE workflow. Keep staging drafts #64–#67 open.

Ryan, 2026-11-01: “How about we ship this for the moment and you look after we are live?” This authorizes preparing completed A–E in the handoff's order, one bundle/one live run. Ryan still owns every merge and LIVE run. F's access-dependent option work and hosting protection remain deferred; G and pending service facts retain their boundaries.

## A completed

Ryan merged release PR #68 and started `live-review-a-20261101-1`. A applied at 15:59 UTC; the follow-up public browser run passed 262 assertions at 16:08. Nine changed origin pages, rendered Home/client/posture gates, structured-data parse/comparison and site PHP logs pass. Backup import succeeds with intended differences; the pixel checker differs against historical staging36, with all 20 views captured. Three phone captures were visually reviewed.

[Complete A live proof and exact rollback](2026-11-01-bundle-a-live.md). Actual private live snapshot: `bundle-a-before-20261101-155950.json`; export `live-before-bundle-a-20261101-155938.sql`, both verified encrypted off-site. No agent merge or LIVE dispatch.

## B scope and fresh proof

B releases the previously reviewed search titles/descriptions, linked global Person/LocalBusiness information, tagline, Corporate HTTPS social image, Home's “by arrangement” wording, and the approved default/Home 1200×630 share card. Ryan's card approval remains **“Yes, use this card.”** No B payload, image or metadata proposal changed during live preparation; only exact host guards and the read-only source selector changed.

Approved PNG: 110,373 bytes, SHA256 `7b769b1a2b82b2cb3ef2bf11a5e6ba65f271b963952fdfe1041c53b6ade5319d`. Existing images remain intact. Pages/snippet/media use slug/title/URL/hash lookup, not staging attachment IDs.

| Check | Result |
| --- | --- |
| Original staging38 B proof | [PASS](2026-11-01-bundle-b-staging38.md): uploads rehearsal2, dry/apply, rendered checks, restore import, strict 12-page semantic scan and approved card |
| Fresh selected-live source | [PASS, run 41324970622](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/161110-review-b-live-source-20261101.md): WordPress 7.1.4, unchanged logo SHA, installed SureRank source/settings |
| Source compatibility | All 15 directly relevant saver/defaults/cache/schema/image/social-render files are byte-identical to the earlier capture; no B API adaptation needed |
| Fresh uploads rehearsal from live | [PASS, run 41325234903](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/161516-review-b-live-rehearse-20261101.md): media import, apply/no-op, exact fingerprint rollback/reapply, pages 200, regulated Home/client gates, no new site PHP errors |
| Actual live preflight | [PASS, run 41325524577](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/161722-review-b-live-dry-20261101.md): every metadata/schema/social/option/snippet/Home part ready; approved media absent and ready for one import |
| Code checks | Gates pass on rehearsed code; final request/documentation commit is checked before this PR is marked ready |

Source archive SHA256 `eb3b76e83f2bf2973099c630091df17f357288d15708d9a83408153797b998f5`. The capture originally hardcoded staging38 for the plugin half; it now consistently selects the exact requested host and supports `--sources-only`, avoiding a redundant public dump. No credentials/config are exported. Its reported PHP 8.5.12 is **CLI**, not a web-PHP reading. No core/plugin/runtime was changed by the agent.

Localhost's HTTP rehearsal makes Corporate's HTTP→HTTPS social URL a neutral row. The real live preflight validates both actual stored URLs; strict live scan after Ryan's apply must confirm HTTPS and the approved PNG/default/Home tags.

## Phone steps for B

1. Review [PR #69](https://github.com/alanmz-crypto/new-masseuse-site/pull/69). With Gates green and no conflicts, tap **Merge pull request → Confirm merge**.
2. Open [Site ops (LIVE)](https://github.com/alanmz-crypto/new-masseuse-site/actions/workflows/site-live.yml). Tap **Run workflow**, branch **main**, enter exactly `live-review-b-20261101-1`, then tap **Run workflow**.
3. Tell this session **“B done”** or send the run link. The agent reads the full apply/post-change/restore sections and runs the strict live semantic scan before C preparation. If the run fails, stop and share its link; never retry a used ID.

The request in `ops/live-request.json` runs only `review-b/push-bundle-b.sh --apply`, target live, tests auto. Preparing or merging it does not apply content. The agent has made no B live write or LIVE dispatch.

## Backup, rollback and follow-up

The push takes a fresh DB export and verifies an encrypted off-site copy before applying. B snapshots exact page content/SureRank rows, options, snippet and any new media's post/meta/files. Rollback refuses drift, restores page content before exact metadata, then removes only this run's unchanged newly created attachment; pre-existing identical assets remain owned by their original context. The actual B live snapshot name is unknown until Ryan's run and must be recorded afterward.

If needed later, prepare a separate Ryan-owned LIVE rollback request with that actual live B snapshot. Once B is applied, roll it back before A. After all releases, reverse order is E→D→C→B→A. Never use a staging snapshot on live.

Automatic reference pixels/schema can differ from old staging36. Inspect those differences and actual rendered/entity/card assertions; an overall write PASS does not turn an INVALID browser test into a pass. Stop at a hosting challenge without spoofing or retry loops. A's successful run does not establish a durable hosting fix.

C/D/E remain staging-applied drafts #64/#65/#66. Each needs fresh actual live predecessor hashes, exact live host/verifier guards, rehearsal and dry-run before its release. Their old hash maps cannot simply be reused by adding an allowed host. Refresh E's WordPress-version requirement against installed source; do not guess or weaken it.

F remains paused at admin HEAD 403; no six-option installer/write. Staging security.txt is confirmed 404 with owner static fallback pending. Butsaba 2/3/4/6/7, confirmed-length consistency, G's November 14 boundary and owner-only tasks remain separate. No hosting-control/support-message, credential or existing-resource deletion occurred.

[Full staging status and snapshots](https://github.com/alanmz-crypto/new-masseuse-site/blob/codex/2026-11-01-review-bundle-f/docs/reviews/2026-11-01-review-fix-status.md).

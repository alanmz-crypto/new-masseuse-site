---
name: site-review-bundle-b
description: 2026-11-01 Bundle B search/sharing on staging38; fresh capture, metadata and linked Person/LocalBusiness, approved default/Home brand card
metadata:
  type: project
---

Ryan: “Butsaba will respond later. Next?” Continue independent Bundle B; leave
questions 2, 3, 4, 6 and 7 pending. Answers 1/5/8/9 remain in the question record.

Tooling `scripts/work-with-me-employer/review-b/`; README describes every store,
guard, exact rollback and capture provenance. Source capture PASS, verified
archives, all required information accessible through GitHub. Git CLI credentials
are unavailable; connected GitHub tools publish equivalent Actions JSON requests.
Bundle A and the partial-answer notes are merged (PRs #61/#62). Their exact
notes are retained; the branch integrates main without regressing the answers.

Includes Home's “by arrangement” wording as required by review3.9, not only the
Corporate description. Exact approved Corporate description is 162 characters;
the gate exception is limited to it. Existing global Person is enabled on all
pages with fixed `/#person`, About URL, published avatar and jobTitle. About and
Contact inherit it; their Organization phone/languages are aligned separately.
LocalBusiness gains image/logo/email/description/Portland/founder; ZIP removed;
no hours invented. New titles/descriptions/social copies and tagline are checked.

Brand share-card preview: 1200×630, existing logo plus requested text and the
existing licence number. No new illustration. Ryan explicitly approved it on 2026-11-01: **“Yes, use this card.”**
Import once by SHA; assign global fallback and Home URL. Retain existing images;
exact rollback removes only this snapshot's newly created attachment after restoring
old settings. Rehearsal requires uploads. This is staging authorization, not live go.

State: **APPLIED on staging38**, 2026-11-01 08:46 UTC, PR #63.
Uploads rehearsal2, dry run, apply and strict semantic scan PASS. All 12 pages
answer 200; zero metadata/entity/card expectation failures. Home OG/Twitter and
global fallback reference approved attachment 1314 at
`/wp-content/uploads/2026/11/thai-solitude-share-card.png`; PNG SHA256 matches.
Post-change rendered gates/posture PASS; no new PHP errors. Restore import PASS;
only intended stores/related rows/cache move. Four structured-data comparisons
differ as expected (global Person + enriched LocalBusiness), and 14 pixel views
differ from live because A/B are only on staging; six remain identical. No
compared page height changed after A. Home desktop/phone captures visually reviewed: no clipped text or side-scroll.

Final proof/report links: `docs/reviews/2026-11-01-bundle-b-staging38.md`.
Before export `staging38-before-bundle-b-20261101-084559.sql` (5,921,174 bytes)
verified off-site before apply; exact snapshot `bundle-b-before-20261101-084613.json`
also encrypted/read back identically. Rollback:
`request.sh run --target staging38 --script review-b/push-bundle-b.sh --args '--rollback $HOME/private-backups/bundle-b-before-20261101-084613.json'`.
Rollback refuses any captured-store or new-media post/meta/file drift and restores
settings before removing only this snapshot's new attachment. Roll B back before
A or earlier exact-state installers on B's resources.

Live disabled. No live request. Ryan reviews and gives his explicit live go
before any live-enable/request preparation; he alone merges/runs live. Next
independent lane: Bundle C; remaining five Butsaba answers stay pending.

**LIVE PREPARATION, 2026-11-01 (supersedes disabled/next-C status above):** Ryan's
A–E release go is recorded in [[site-review-release]]. A is now live/verified 262.
PR #69 enables only B's exact live guards; approved payload/card unchanged.
Fresh selected-live SureRank/settings source PASS, 15 relevant files identical;
source WP 7.1.4 and CLI 8.5.12 (not web PHP). Capture now selects live consistently
and supports sources-only without the redundant public dump.
Uploads rehearsal from actual live PASS 41325234903: media import, exact rollback,
no-op/reapply, pages/copy gates and no new site PHP errors. Actual dry PASS 41325524577
all parts ready, approved asset absent. Request live-review-b-20261101-1 prepared,
not applied; Ryan reviews/merges #69 then personally runs LIVE on main. After “B done”,
read all automatic checks/restore differences and strict live 12-page semantic scan.
Actual live snapshot is not yet known. See docs/reviews/2026-11-01-review-live-release.md.
C/D/E are already applied on staging38; roll E/D/C back before B there.

**LIVE and verified, 2026-11-01 (supersedes preparation status above):** Ryan merged
PR #69 and ran live-review-b-20261101-1/run 41326724484. Snapshot
bundle-b-before-20261101-163141.json; DB live-before-bundle-b-20261101-163126.sql,
both encrypted off-site verified. Strict live scan 41329251788:12 pages 200, zero
expectation failures; approved PNG 1305/1200×630/SHA MATCH, meta/entity/social exact.
Rendered/origin/posture/site-error and import 0/intended 88→90 differences reviewed.
Historical staging36 schema 27/23 and pixels differ; all 20 captures exist, no aggregate
all-pass. Full proof docs/reviews/2026-11-01-bundle-b-live.md. C is prepared in #64,
not applied; once C applies undo C before B, and B before A. See [[site-review-release]].

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

State: **APPLIED on staging38**, 2026-11-01 08:46 UTC, PR #63 is merged (Ryan reported it; GitHub confirmed merge52e7d6a on 2026-11-01).
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

Live disabled. No live request. PR #63 is merged; live-enable/request preparation now needs a follow-up PR
after Ryan's explicit live go. He alone merges/runs live. Next
independent lane: Bundle C; remaining five Butsaba answers stay pending.

**Bundle C is now applied on staging38:** roll C back before B/A on affected
stores. C's public browser verification is blocked by the host challenge, but
its exact snapshot is recorded in [[site-review-bundle-c]].

Bundle D is now applied on staging38 (draft PR #65). Roll D back before C/B/A;
its exact snapshot and proof are in [[site-review-bundle-d]]. Live unchanged.

Bundle E is applied on staging38 (draft PR #66). Roll E back before D/C/B/A;
active snapshot/proof in [[site-review-bundle-e]]. Live remains unchanged.

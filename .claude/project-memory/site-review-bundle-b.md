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

State: built; offline gates PASS. Rehearsal/staging/verification pending.
Live disabled. No live request; fresh backup and exact rollback required.

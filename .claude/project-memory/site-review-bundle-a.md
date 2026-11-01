---
name: site-review-bundle-a
description: 2026-11-01 review Bundles 0 and A — Butsaba draft, fresh staging38 dump, booking-path installer; staging only, live disabled
metadata:
  type: project
---

Ryan assigned the Claude role from the 2026-11-01 handoff to Codex and required
staging38 and a stop if required information/access is unavailable.
The handoff records approval for its listed established-copy edits. Its next
agent lane is Bundle 0 and Bundle A; live remains Ryan's own run after his go.

Bundle 0: drafted `docs/inter-model/2026-11-01-butsaba-questions.md` and gave
Ryan the same message in chat. Not sent. No service facts are assumed.
Read-only staging38 dump `review-a-dump-staging38-20261101` PASS; archive SHA256
`29978f3af1e37d0ec2d5bc8661f24cbb84c3e1b7ca43c376c2899f5d47062487`.
Decoded data stays outside git. Snippet-hash inspection
`review-a-snippet-hashes-20261101` independently confirms the jump snippet bytes.

Bundle A tooling: `scripts/work-with-me-employer/review-a/` (README has every
change, the source fragments, pattern, checks and rollback command). One
staging-only installer updates nine pages, A11yPageJumpLinks and an appended
CSS block, with all-or-nothing preflight and exact whole-resource rollback.
Home, client-page, availability and builder gates move with the package.
Rehearsal now runs the rendered Home and client-page copy gates before cleanup,
so they must pass on the throwaway copy before a staging apply.

Footer services widget deferred under the handoff's condition: no 30-width
geometry proof. Chair's three FAQ links are approved here; the existing Spectra
tab roles remain until Bundle C. Older page builders describe historical
releases; roll Bundle A back before using their exact-state rollback installers
or rebuilding a page from them. Current-state rollback is Bundle A's snapshot.

State: **APPLIED and verified on staging38**, 2026-11-01 02:10–02:16 UTC.
PR #61 has been merged (confirmed through GitHub when recording the answers below); live remains disabled for Bundle A. Final record:
`docs/reviews/2026-11-01-bundle-a-staging38.md` (all report links, checks,
expected pixels, heights, exact rollback). Rehearsal 4, dry run and apply PASS;
rendered gates and posture PASS, structured data identical, no PHP errors.
Fourteen expected pixel differences; six identical views. Restore proof imported
successfully and compared 88 fingerprints, with only intended differences.
Browser: 262 assertions PASS, nine pages, four widths, light/dark. Three phone
screenshots visually reviewed.

Export `staging38-before-bundle-a-20261101-021038.sql` verified off-site before
apply; snapshot `bundle-a-before-20261101-021051.json` also encrypted and verified.
Exact rollback: `request.sh run --target staging38 --script review-a/push-bundle-a.sh --args '--rollback $HOME/private-backups/bundle-a-before-20261101-021051.json'`.
No live write/request was made in this work. PR #61 is now merged, so any live-enable/request preparation requires a follow-up PR after Ryan's go; he alone merges and starts the live run.

Lesson: WP 7.1 adds `wp-block-paragraph` and group layout classes at render time.
Match class tokens, not entire quoted attributes; rehearsal caught new brittle
assertions before staging.

Bundle G follow-up: A adds five closing “bookings begin November 14, 2026”
sentences. After A, its date refresh must cover **15**, not the original ten,
including the Services closing block. Apply G only on/after November 14.


**Partial answers, 2026-11-01:** Ryan replied `#1 20 # 5 60 and 90 #8 yes #9 yes`.
Recorded in `docs/inter-model/2026-11-01-butsaba-questions.md` and open items:
20-minute chair length (do not infer mandatory/usual duration or capacity);
60/90-minute Therapeutic, Deep Tissue and Thai options; call/text permission;
both Windy City Massage links and the reference offer may stay.
Questions 2, 3, 4, 6 and 7 remain unanswered. This is a documentation update,
not a site apply or live go; the applied installer/package is unchanged.

**Bundle B applied afterwards (2026-11-01):** roll B back first before this
Bundle A exact-state rollback on staging38. B changes Home/metadata/options and
LocalBusinessInfo; see [[site-review-bundle-b]] for its snapshot and proof.

**Bundle C is now applied on staging38:** roll C back before B/A on affected
stores. C's public browser verification is blocked by the host challenge, but
its exact snapshot is recorded in [[site-review-bundle-c]].

Bundle D is now applied on staging38 (draft PR #65). Roll D back before C/B/A;
its exact snapshot and proof are in [[site-review-bundle-d]]. Live unchanged.

Bundle E is applied on staging38 (draft PR #66). Roll E back before D/C/B/A;
active snapshot/proof in [[site-review-bundle-e]]. Live remains unchanged.

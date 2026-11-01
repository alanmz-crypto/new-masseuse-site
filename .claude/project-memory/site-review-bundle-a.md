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

State: built, offline gates PASS; rehearsal and staging verification pending.
No staging or live write yet. Live is explicitly absent from both allow-lists.

Bundle G follow-up: A adds five closing “bookings begin November 14, 2026”
sentences. After A, its date refresh must cover **15**, not the original ten,
including the Services closing block. Apply G only on/after November 14.

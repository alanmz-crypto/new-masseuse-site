# Review fixes: current status and next access

Updated 2026-11-01. All work is on staging38. **No live write or live request.**

## What is done

| Bundle | Staging38 status | Pull request |
| --- | --- | --- |
| 0 | Questions/answer log and fresh captures complete | [Butsaba record](../inter-model/2026-11-01-butsaba-questions.md) |
| A | Booking paths/internal links applied; 262 browser assertions and rollback pass | [#61, merged](https://github.com/alanmz-crypto/new-masseuse-site/pull/61) |
| B | Search/sharing applied and verified; approved default/Home card | [#63, merged](https://github.com/alanmz-crypto/new-masseuse-site/pull/63) |
| C | Markup/accessibility applied; 1,248 isolated browser assertions, rollback/CSP pass; final public/owner items below | [#64, draft](https://github.com/alanmz-crypto/new-masseuse-site/pull/64) |
| D | Approved wording/research corrections applied; 1,376 rehearsal assertions, 53 origin checks, rendered gates/restore import pass | [#65, draft](https://github.com/alanmz-crypto/new-masseuse-site/pull/65) |
| E | Performance applied; 1,664 host-matching rehearsal assertions, 26 origin checks, rendered gates/restore import pass | [#66, draft](https://github.com/alanmz-crypto/new-masseuse-site/pull/66) |
| F | **Not applied: required admin verification gets 403**; six-row proposal captured | [#67, preparation draft](https://github.com/alanmz-crypto/new-masseuse-site/pull/67) |
| G | Wait until November 14; 15 booking-date sentences after A, plus owner cache step | Not applied |

D corrects the Home Ease chip, Portland/employment copy, small FAQ/footer wording, source fidelity/full citations and the BLS link. E reduces the 616,299-byte PNG to a 21,796-byte WebP (96.46% smaller), corrects Home’s swapped Deep/Thai image mapping, supplies verified image dimensions/responsive sources, preloads two existing fonts and removes frontend emoji loading. All original images, established service facts and live content remain intact.

The D and E staging runners captured all 20 public screenshot views successfully and passed rendered Home/client/posture gates. That does not establish a durable hosting AntiBot fix or a full interactive public pass. Reference comparison differences are expected because A–E are not live; B’s schema 27 vs 23 difference remains, with no parse errors. No site PHP errors were logged.

## Access needed now

F’s read-only HEAD request to staging38 /wp-admin/ returns **HTTP 403 from nginx**. REST and the public login form return 200, but admin access cannot be verified. **The agent has stopped F under Ryan’s explicit stop-for-access rule. No F settings changed.**

Provide an authorized way to verify the staging admin page before URL settings are changed. [Exact six proposed values, blocked-request evidence and support text](2026-11-01-bundle-f-staging38.md). No support message has been sent.

## Other remaining boundaries

- security.txt is confirmed 404 through SiteGround on staging38. C’s virtual route passes Apache rehearsal; the [prepared static payload](../../scripts/work-with-me-employer/review-c/security-staging38/security.txt) remains Ryan’s upload/fallback under the handoff. Approved mailbox: butsabad@gmail.com. No server file was written.
- Full interactive public verification remains pending. The [verified SiteGround support route/draft](../inter-model/2026-11-01-siteground-antibot-support.md) is ready for Ryan; no hosting-control change was made.
- Butsaba questions 2/3/4/6/7 remain pending. Her confirmed 20-minute chair and 60/90-minute options are recorded for a combined consistency pass; no usual/mandatory chair length or event capacity is inferred. Call/text and Windy City links/reference are approved. No message was sent to her.
- G waits until November 14. Credentials/2FA from November 7, deletions, Astra update, www redirect and For Practices label remain Ryan’s lane. No agent deletion of an existing file/plugin/media, credential work or live write occurred.

## Review and merge order

C–F remain drafts. D is based on C, E on D, and F on E so each diff stays focused. **Do not merge a stacked PR into an already-merged feature branch.** Once its prerequisites/final checks are resolved, merge C #64 first; tell the agent it merged so D #65 can be retargeted to main and checked before merging. Repeat for E #66, then F #67 after it has an implementation and passing proof. Every live enable/run still needs Ryan’s explicit go and his own LIVE workflow run; no live request is currently prepared.

## Exact rollback/proof

| Bundle | Active private before-snapshot | Proof |
| --- | --- | --- |
| A | bundle-a-before-20261101-021051.json | [A](2026-11-01-bundle-a-staging38.md) |
| B | bundle-b-before-20261101-084613.json | [B](2026-11-01-bundle-b-staging38.md) |
| C | bundle-c-before-20261101-094545.json | [C](2026-11-01-bundle-c-staging38.md) |
| D | bundle-d-before-20261101-104506.json | [D](2026-11-01-bundle-d-staging38.md) |
| E | bundle-e-before-20261101-120216.json | [E](2026-11-01-bundle-e-staging38.md) |

Fresh DB exports and active snapshots are verified encrypted off-site. Roll E back before D, then C, B and A. Exact guards refuse drift. E’s earlier incomplete 114734 snapshot is diagnostic only; do not use it for rollback. The media-path correction is rehearsed with SiteGround’s symlink distinction; logical paths go to WordPress media APIs and realpath is reserved for containment/hash guards.

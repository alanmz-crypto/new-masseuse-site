---
name: site-review-bundle-c
description: 2026-11-01 Bundle C markup/accessibility on staging38; source/geometry capture, exact transactional installer and security.txt approval
metadata:
  type: project
---

PR #63 merged, confirmed merge `52e7d6a`. C continues on staging38 only, using a new branch
`codex/2026-11-01-review-bundle-c`. Live disabled; no live request.

Read `scripts/work-with-me-employer/review-c/README.md` for the scope/provenance.
Fresh native stores/public Spectra/Astra source capture PASS; actual generated
CSS lives one directory below assets (`assets/0`), captured in the second read.
The older dump intentionally captures asset HEADs only; those .body files are
headers, not script/CSS source. Stored sources are accessible; the final public browser run is blocked by the hosting challenge (record below).

Baseline measurements on seven pages at 1280/390 px: all 23 step/row headings use 24/20 px text (line height 1.3),
not the global paragraph 1.2 rule. Generated CSS confirms tablet 22 px and actual
976/767 breakpoints; scoped H3 preservation uses these measured values. There
are 23 Spectra conversions + 3 Corporate = 26, despite the handoff's stale count.
Services Chair/Corporate rows already have H3s and stay intact.

C covers headings/hiddenServices H2/Therapeutic FAQ H2, five focus declarations,
all 49 FAQ icons on eight pages, native disclosure adapter, booking region + shorter phone
text, named lazy iframe with SAME Mt. Tabor URL, Contact phone underline, runtime
résumé labels, scrolltop/footer names, phone H1/hearts spacing, search/404/head
cleanup, Accessibility statement v5 and virtual security.txt. Core Button blocks
reject stored aria-label in the existing validator, so labels are runtime-only.
Spectra handlers stay on their original nodes; native child buttons prevent old
ancestor Enter/Space handlers from double toggling. Research details stay native.

Ryan approved published `butsabad@gmail.com` for security.txt in chat. No server
file/rewrite rule is added. Expiry 2027-10-31T23:59:59Z needs owner review before
that date. New snippet titles SiteReviewAccessibility and SiteReviewUtilities.
Rollback removes only this run's exact unchanged new snippets, after restoring
old stores; no existing snippet/file/media is removed.

State: **APPLIED on staging38**, 2026-11-01 09:45 UTC, draft PR #64.
Uploads rehearsal 2 PASS: 1,248 assertions, exact apply/reapply/rollback/re-apply,
rendered copy gates;26 heading metrics match baseline and147 FAQ comparisons show
zero geometry delta. New Therapeutic heading captures visually reviewed.
Staging dry/apply, off-site backup, rendered gates/posture and restore import
PASS. No new PHP errors. Contact CSP PASS in Chromium and Firefox (four views).
Final whole-site browser attempt was INVALID at Home: zero assertions, robot
challenge. This is still pending, not a pass. See the proof record.

Snapshot `bundle-c-before-20261101-094545.json`; export
`staging38-before-bundle-c-20261101-094534.sql` (5,995,265 bytes), both copied and
verified encrypted off-site. Exact rollback through the house request:
`request.sh run --target staging38 --script review-c/push-bundle-c.sh --args '--rollback $HOME/private-backups/bundle-c-before-20261101-094545.json'`.
C must be rolled back before B/A or older installers on its resources.

Ryan now asks for help removing the hosting challenge. Official support route
and a ready message are in `docs/inter-model/2026-11-01-siteground-antibot-support.md`.
No SiteGround admin/support session is connected. Seek a supported staging38
exception; no guessed site-tools writes or production change. No message sent.
security.txt public delivery remains unverified; pre-C HEAD was a 93-byte nginx 404,
so the virtual WordPress route may require a Ryan-owned static-file fallback.
The prepared staging payload is `review-c/security-staging38/security.txt`;
do not claim it is served or alter the applied snippet/snapshot to hide a failure.

 C's package opts in to
`check-rehearsal.mjs`, run on the throwaway copy with uploads before cleanup;
this proves browser behavior as well as the required regulated-copy gates before
staging. Full Astra option, page SureRank rows and active-snippet settings are
snapshotted/restored; only Astra blog-meta is changed. Page/CSS hashes are fixed
per allowedhost, and every replacement count is exact. No metadata/copy from B
is regressed. Butsaba questions 2/3/4/6/7 remain pending.

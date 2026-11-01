# Review Bundle C — staging38 verification

PR: https://github.com/alanmz-crypto/new-masseuse-site/pull/64
Bundle B's PR #63 is merged (confirmed `52e7d6a`). C remains staging38 only;
live is disabled and no live request has been prepared.

Status: **APPLIED on staging38** at 09:45 UTC. Final whole-site public browser
verification is blocked by SiteGround's robot challenge. PR #64 remains a draft;
live is unchanged. The later focused Contact CSP check passed.

| Evidence | Result |
|---|---|
| Fresh stored/public-source capture | PASS; exact native stores, public Spectra/Astra handlers |
| Generated CSS capture | PASS; correct responsive source under assets/0 |
| Typography baseline | PASS; seven pages at 1280/390, 26 target headings |
| Offline code gates | PASS |
| Final uploads rehearsal | PASS: rehearsal 2; exact apply/rollback/reapply and 1,248 browser assertions |
| Staging dry run/apply | PASS; all 12 pages return 200 with runtime/licence |
| Full staging browser contract | INVALID: challenge on first Home page; zero assertions, no pass claimed |
| Contact CSP, Chromium/Firefox | PASS: four page views, zero policy violations |

Capture reports:

- [Fresh stores and source](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/090122-review-c-capture-staging38-20261101.md)
- [Generated CSS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/090904-review-c-css-source-staging38-20261101.md)
- [Heading baseline](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/090512-review-c-baseline-staging38-20261101.md)
- [Final rehearsal and Therapeutic captures](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/094220-review-c-rehearse-staging38-20261101-2.md)
- [Staging dry run](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/094437-review-c-dry-staging38-20261101.md)
- [Apply, post-change checks and restore proof](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/094907-review-c-apply-staging38-20261101.md)
- [Blocked full browser attempt](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/095152-review-c-browser-staging38-20261101.md)
- [Contact CSP in both engines](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/095324-review-c-csp-staging38-20261101.md)

The stored scope is **23 Spectra paragraph headings and three Corporate card
headings**, despite the handoff's stale count. Their words/IDs remain and the
measured 24/22/20px styling is preserved. Services Chair/Corporate rows were
already H3s. One hidden Services H2 and the missing Therapeutic FAQ H2 are added.
The latter shares the original FAQ grid cell with its questions.

All 49 FAQ icons now point down when closed/up when open. A native child button
keeps Spectra's original question node/click handler and owns expanded/controls
state; old item roles/key handlers no longer double-toggle. The original button
and child nodes are restored after temporary browser-only geometry comparisons.
The native research details are untouched. Chair's four research panels plus
Sources are tested with mouse/Enter/Space and for contrast in both themes.

Other reviewed changes: five focus-token replacements; booking landmark and
shorter phone text retaining both date facts; named lazy iframe with identical
Mt. Tabor URL and 500/350/325 geometry; Contact phone underline; runtime résumé
labels; footer/scrolltop accessibility; phone H1/heart spacing; search/404/head
cleanup; Accessibility statement v5 with its prior limits/no-screenreader notice;
and virtual security.txt using the mailbox Ryan explicitly approved.

security.txt: Contact `mailto:butsabad@gmail.com`; Expires
`2027-10-31T23:59:59Z`; Canonical uses the site's own HTTPS address (HTTP on the
isolated rehearsal). No file, rewrite rule or flush is added. Review the contact
and expiry before 2027-10-31.

The first rehearsal proved apply/idempotency/exact rollback/re-apply, all rendered
copy gates and 1,181 of 1,187 browser assertions. Six failures exposed the hidden
Services H2's overridden hiding rule. Layout review also found that the new
Therapeutic H2 needed to share the existing third grid cell instead of consuming
a fourth cell. Both were corrected before any staging write; the final package
passed rehearsal 2: all 1,248 assertions, zero failures. All 26 converted heading
metrics match the pre-C baseline at desktop/phone widths. The FAQ adapter has
zero geometry delta in 147 comparisons (49 questions × 3 widths). The Therapeutic
heading/FAQ captures were decoded and visually reviewed at 1280/390 px. Chair
panel text minimum contrast: 9.133:1 light, 11.365:1 dark; 181 elements per theme. New FAQ styles are stored in Additional CSS, matching the
house storage rule. Browser font/action/scroll waits are bounded.

No opening hours, service facts, capacity, credentials, existing-content deletion,
Astra update, www redirect or menu-label decision. Butsaba's five unanswered
questions remain pending. Bundle G waits until November 14 and covers 15 booking
date sentences after A.

Exact rollback restores all affected content, page SureRank rows, active-snippet
settings and the entire Astra option. It refuses independent changes to any
captured store or new snippet's post/meta/terms, restores prior stores, then
removes only this run's unchanged new snippets. Roll C back before B/A or older
exact-state installers on affected resources. New/old Chair and Corporate
builders move together; historical installers are superseded on those resources.


## Applied-state proof and limits

Before apply, `staging38-before-bundle-c-20261101-094534.sql` (5,995,265 bytes)
was copied off-server with identical SHA256, encrypted in `offsite-2026-11` and
read back identically. Snapshot `bundle-c-before-20261101-094545.json` was also
copied/encrypted/verified. The export imported successfully: 90 restored versus
92 current fingerprints. Only the intended 12 pages, CSS, two edited/two new
snippets, Astra option and related rows/cache differ; no SureRank row changed.

Exact staging rollback:

```sh
scripts/ci/request.sh run --target staging38 --script review-c/push-bundle-c.sh --args '--rollback $HOME/private-backups/bundle-c-before-20261101-094545.json'
```

Rendered Home/client-page gates and posture PASS; all 12 pages answer 200; no new
PHP errors, warnings or deprecations from site code. The live reference still
lacks A/B/C: 27 versus 23 structured-data items is the same expected B difference
(one global Person per compared page); JSON parse errors remain zero. All 20
pixel views differ as intended; none is missing. Work With Me/Privacy desktop
changes are tiny chevron-only pixel differences. This is not an identical-site
claim.

Measured height change from the recorded post-B state (1280/390 px):

| Page | Desktop delta | Phone delta |
|---|---:|---:|
| Home |0|-75|
| About |0|-51|
| Services |0|-51|
| Thai |0|-51|
| Therapeutic |+159|+57|
| Deep Tissue |0|-51|
| Contact |0|-52|
| Privacy |0|-30|
| Accessibility |+53|+131|
| Work With Me |0|0|

The Therapeutic addition is its new FAQ heading. Phone reductions follow the
shorter strip and Home heart spacing; Accessibility gains its v5 text and phone
H1 spacing. Heading-tag and native-FAQ-wrapper geometry were independently
proved unchanged in rehearsal (26 targets,147 FAQ comparisons).

Public final verification remains pending. The full check stopped at its first
challenged page; a subsequent Contact CSP check passed from another runner.
See `docs/inter-model/2026-11-01-siteground-antibot-support.md` for the supported
owner support route. Ryan now asks for help resolving the false positive; no
hosting-control change or external message has been made.

security.txt: PHP delivery passes the Apache rehearsal but public SiteGround
GET delivery has not been verified. Pre-C HEAD was a 93-byte nginx 404 without
WordPress headers, so a static-file fallback may be required. The approved
staging payload is prepared in `review-c/security-staging38/security.txt` and is
not uploaded. Do not claim its public URL works until checked. The handoff
reserves a required static upload to Ryan.

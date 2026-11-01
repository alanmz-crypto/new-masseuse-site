# Bundle C: live accessibility checks and one hosting follow-up

**C is LIVE.** Targeted live browser verification passes 1,247 of 1,248 assertions; the sole failure is the known **security.txt 404** hosting-delivery follow-up. Do not call the full suite or automatic post-change command all-pass.

Ryan merged PR #64 as 69d2de8a221946f0aaddfa3cd1b4dab4cdc4dfe6 and ran `live-review-c-20261101-1` personally. C applied on 2026-11-01 at 17:52 UTC. No agent main merge, force-push or LIVE dispatch.

C adds reviewed heading semantics, native FAQ controls/focus, booking landmark/phone text, named lazy map, résumé/footer/scroll labels, phone spacing, search/404/head cleanup and Accessibility statement v5. Original maps query and service facts stay intact. B metadata and unrelated option fields remain unchanged.

## Evidence and its limits

| Check | Actual result |
| --- | --- |
| Pre-release fresh live-copy uploads rehearsal | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/171531-review-c-live-rehearse-20261101.md): 1,248/0, exact fingerprint rollback/reapply, regulated Home/client gates, no new site PHP errors |
| Actual live preflight | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/171934-review-c-live-dry-20261101.md): all parts ready |
| Ryan's live apply | [PASS, run 41333582236](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/175330-live-review-c-20261101-1.md): fresh DB verified encrypted off-site before writing |
| Origin verification | All 12 changed pages 200 with C runtime and licence |
| Initial automatic public post-change command | **INVALID**, exit 2 at first Home request: hosting Robot Challenge 202, nothing tested |
| One later post-change retry | [Completed, run 41336383029](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/182649-review-c-live-postchange-20261101-2.md): deployed C code/rendered Home/client/posture gates, pages and PHP-log checks PASS; overall FAIL only on expected reference schema/pixels |
| Restore import | Exit 0, before 90/current 92 fingerprints; intended differences only, no SureRank SEO changes |
| Focused Contact CSP | [PASS, run 41334346820](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/180152-review-c-live-csp-20261101.md): four desktop/phone views in Chromium/Firefox, zero policy violations |
| Full targeted public C contract | [FAIL, run 41334743122](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/180651-review-c-live-browser-20261101.md): 1,248 assertions, 1 failure, only security.txt 404/text-html |
| Functional accessibility/layout checks | Remaining 1,247 assertions pass: all 26 heading geometry checks, 147 FAQ wrapper comparisons, mouse/Enter/Space/ARIA, focus/strip/map, labels/spacing, no side-scroll or browser errors, light/dark |
| Utility pages | Actual 404 navigation/four links/search form and search's form/no bylines/View page labels pass |
| Chair panels | Public minimum 9.133:1 and both-theme controls/contrast checks pass; private pre-release values 9.133:1 light / 11.365:1 dark |

The original automatic public attempt stopped immediately at its challenge. Subsequent focused Contact and full targeted C runs completed. One later generic post-change retry from the deployed C source also completed: all code/regulated Home/client/posture gates and checked routes pass, no site PHP errors/warnings/deprecations are logged, and all 20 screenshot pairs exist. Its two comparison failures are the approved B schema 27/reference 23 enrichment (zero parse errors) and historical staging36 pixel differences. This retry is not an aggregate all-pass; it resolves the initially untested rendered/security/log sections. No browser identity spoofing, copied challenge cookies or retry loop was used. These successes do not establish a durable hosting AntiBot fix, and the initial INVALID remains INVALID.

The targeted browser report's retained tail contains the only failing assertion: `{"status":404,"type":"text/html"}` for `/.well-known/security.txt`. Its global summary reports 1,248 assertions/1 failure. It is not a zero-assertion challenge run; the other C behavior is verified on the actual HTTPS site.

Restore command exit 3 reports post-apply differences; import itself succeeds. Reviewed differences are the twelve intended pages, Additional CSS 29, existing snippets 1232/1268, new snippets 1320/1321, Astra settings/WPCode cache and related post/meta/term rows. SEO row fingerprints are unchanged, preserving B's search/share settings. The actual new snippets are SiteReviewAccessibility 1320 and SiteReviewUtilities 1321.

## Actual live backups and rollback

| Resource | File under private-backups | Verification |
| --- | --- | --- |
| DB export | live-before-bundle-c-20261101-175150.sql | 4,985,311 bytes; plaintext SHA prefix 1772759f6877; encrypted off-site read-back identical before apply |
| Before-snapshot | bundle-c-before-20261101-175205.json | plaintext SHA prefix 54fcebf853b4; encrypted off-site read-back identical after apply |

No live rollback was performed; the exact operation was rehearsed before release. If needed, prepare a separate Ryan-owned LIVE request for `review-c/push-bundle-c.sh --rollback $HOME/private-backups/bundle-c-before-20261101-175205.json`. It takes/verifies another export first, refuses any captured-store/new-snippet drift, restores old stores before removing only this snapshot's unchanged newly created snippets. Never use a staging snapshot on live.

D/E must be undone before C once applied; C before B/A. Current planned reverse order E→D→C→B→A. A/B's actual live snapshots remain recorded in their own proof files.

## Open hosting item and next release

security.txt is the public contact notice for security reports. The virtual WordPress endpoint passes isolated Apache verification but SiteGround's actual public path returns 404. [Exact approved live static payload](../../scripts/work-with-me-employer/review-c/security-live/security.txt) is prepared for Ryan/SiteGround. It has **not** been uploaded; neither C's push nor D preparation writes a static server file or hosting rule. Keep this finding open under Ryan's ship-for-now direction; never relabel 404 as success.

D preparation continues in draft #65, preserving A/B/C code and facts. F/admin options remain paused; the hosting issue, pending Butsaba answers/length consistency, G's date and owner-only tasks remain separate. No support message or credential work. [Current release plan](2026-11-01-review-live-release.md).

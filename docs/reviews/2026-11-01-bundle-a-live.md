# Bundle A: live and verified

Applied by Ryan's own confirmed LIVE workflow on **2026-11-01 15:59 UTC**; public browser verification completed at 16:08 UTC. Release PR #68 is merged as fcf68c75b8cd1099630136fc073ffbb9eae7131f. The agent did not merge, push main or dispatch LIVE.

A adds booking next steps and service/quote links on nine pages, corrects Home card destinations, reorders Services, and adds the Contact corporate card and Corporate phone shortcuts. No new service fact, hosting control or WordPress URL/discussion/avatar option was changed.

## Evidence

| Check | Result |
| --- | --- |
| Staging38 browser proof | 262 assertions PASS; [original staging record](2026-11-01-bundle-a-staging38.md) |
| Fresh rehearsal from live with uploads | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/152945-review-a-live-rehearse-20261101.md): apply/repeat/exact rollback/reapply, rendered Home/client gates, pages 200, no new site PHP errors |
| Actual live dry2 | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/153137-review-a-live-dry-20261101-2.md): all 23 page fragments, jump snippet and CSS ready |
| Ryan's live apply | [PASS, run 41323953827](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/160311-live-review-a-20261101-1.md); fresh export verified encrypted off-site before writing |
| Origin verification | Nine changed pages 200, with expected A content and licence |
| Automatic rendered/security checks | Home, client-page and posture gates PASS; checked public pages/sitemap/robots200 |
| Structured data | Identical to reference, 23 items/23 items, zero parse errors |
| PHP log | No site PHP errors, warnings or deprecations |
| Restore import | Exit0; 88 fingerprints compared; intended changes reviewed below |
| Follow-up live browser suite | [PASS, run 41324631068](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/160809-review-a-live-browser-20261101.md): 262 assertions, nine pages, widths 1280/768/390/320, light/dark |
| Phone captures | Thai closing block, Corporate sticky buttons and Contact corporate card visually reviewed; text/buttons fit without clipping or side-scroll |

All 20 reference and live screenshots were captured with none missing. The pixel checker reports 20 differences against historical **staging36**, which predates A and other prior live changes; this is **not** a pixel-equality pass. The fresh live-copy rehearsal, same-page addition measurements and the targeted live browser run provide the relevant A checks. Do not describe the aggregate post-change command (exit1 on those pixels) as all-pass.

The restore command returns3 because the restored before-export differs from live after A. Its import itself succeeds. Reviewed differences are exactly the nine intended pages, Additional CSS 29, A11yPageJumpLinks 1232, WPCode snippet cache, related post/revision/meta row counts and SiteGround visitor/security-log activity from the checks. No SureRank SEO rows or unrelated option/store fingerprints differ.

## Live backup and exact rollback

| Resource | Actual live file | Verification |
| --- | --- | --- |
| DB export | live-before-bundle-a-20261101-155938.sql | 4,378,005 bytes; plaintext SHA prefix 6501c4e3a219; encrypted off-site read-back identical before apply |
| Before-snapshot | bundle-a-before-20261101-155950.json | plaintext SHA prefix 1afc89ff0e90; encrypted off-site read-back identical after apply |

These are the live files under `$HOME/private-backups/`. Do not substitute staging's snapshot. No production rollback was run. Exact rollback was rehearsed on the isolated fresh copy before release.

If needed, prepare a separate Ryan-owned LIVE request for `review-a/push-bundle-a.sh --rollback $HOME/private-backups/bundle-a-before-20261101-155950.json`. The push first takes/verifies another DB export; the installer refuses if any whole resource changed since A. Once B or another touching bundle is applied, undo that bundle before A. After all planned releases, reverse order is E → D → C → B → A.

## Portability and remaining work

The first live preflight safely refused the Corporate mobile insertion because live's page has no terminal LF. Exact source SHA f5a3a8f364730c4ee8295ff374efdd63730c1e0b59498dff6cdcc1871ea8241d confirmed it. Removing exactly one LF from BOTH sides of that pair preserves staging's applied output byte-for-byte and live's ending convention. All wording, links, counts and frozen snippet hashes remain unchanged; no-LF/one-LF/two-LF roundtrips pass. The source plan/generated payload and exact-host checker guard move together.

Fresh live-copy rehearsal reports WordPress 7.1.4. B's subsequent selected-live source capture also reports 7.1.4 and CLI PHP 8.5.12; the latter is not a reading of the web PHP version. No runtime version was changed by the agent. Later bundles must inspect their actual predecessor/runtime requirements before enabling live.

A verification succeeded without a challenge on this run. It does not establish a durable hosting AntiBot fix. B is next; C–E remain later. F's admin-access pause, staging security.txt 404/owner fallback, full later-bundle public interactivity, pending Butsaba answers and G's date/owner-only work remain separate. [Current release plan](2026-11-01-review-live-release.md).

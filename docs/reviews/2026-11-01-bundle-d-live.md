# Bundle D: live and verified

Ryan merged PR #65 as e357dbb09fd6b533978371eaab1747cc329f183e and personally ran `live-review-d-20261101-1`. D applied on 2026-11-01 at 18:50 UTC. Focused public browser verification subsequently passes all 128 D assertions.

D releases the reviewed wording and research-fidelity corrections on nine pages, updates existing A11yHeartLinks snippet 1234 and changes Astra’s footer punctuation. No hosting, credential, plugin or WordPress site-file change was made.

## Evidence and its limits

| Check | Actual result |
| --- | --- |
| Ryan’s live apply | [PASS, run 41338572399](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/185110-live-review-d-20261101-1.md): fresh DB verified encrypted off-site before writing |
| Origin verification | 53 assertions PASS across the nine changed pages, heart links and footer |
| Automatic public post-change command | **INVALID**: hosting Robot Challenge 202; zero tests completed |
| Restore import | Exit 0; before/current 92 fingerprints; intended differences only, no SEO or Additional CSS changes |
| Focused public D contract | [PASS, run 41338898883](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/185420-review-d-live-browser-20261101.md): 128 assertions, all nine pages, 1280/390/320, light/dark, expected links/layout and no JavaScript errors |

The automatic attempt remains INVALID. The later focused D run verifies the actual public site; it does not establish a durable hosting AntiBot fix. Do not describe the automatic post-change command as all-pass.

The restore import succeeds. Reviewed differences are the nine intended pages, A11yHeartLinks 1234, the footer/Astra option, WPCode cache and related postmeta. SEO and Additional CSS fingerprints remain unchanged, preserving the earlier bundles’ search/share settings and styling.

## Actual live backups and rollback

| Resource | File under private-backups | Verification |
| --- | --- | --- |
| DB export | live-before-bundle-d-20261101-184951.sql | 5,562,193 bytes; plaintext SHA prefix 34367bb1e69d; verified encrypted off-site before writing |
| Before-snapshot | bundle-d-before-20261101-185003.json | plaintext SHA prefix c3d07cde64fe |

If rollback is needed, prepare a separate Ryan-owned LIVE request for `review-d/push-bundle-d.sh --rollback $HOME/private-backups/bundle-d-before-20261101-185003.json`. The push takes and verifies another export first; the installer refuses captured-store drift and restores the exact snapshot. Never substitute a staging snapshot. After E applies, undo E before D; D precedes C/B/A in rollback order.

## Remaining work

C’s known public security.txt 404 remains a hosting follow-up; the approved static fallback has not been uploaded. E’s live preparation, source compatibility and rehearsal evidence are recorded separately before release. F/admin options, pending Butsaba answers, G’s date and owner-only work remain separate. [Current release plan](2026-11-01-review-live-release.md).

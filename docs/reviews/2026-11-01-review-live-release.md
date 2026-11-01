# Review release: A first, then B–E

**Prepared, not applied.** Ryan's next action is to merge [release PR #68](https://github.com/alanmz-crypto/new-masseuse-site/pull/68) and personally start its LIVE workflow. No live content, database setting, hosting control or credential has been changed by this release preparation.

## Authorization and scope

Ryan, 2026-11-01: “How about we ship this for the moment and you look after we are live?”

This is the go to prepare the completed A–E review changes for release, keeping the handoff's one bundle/one live run and Ryan-owned merge/run boundaries. A is first. B–E need their own ordered preparation and proof after each preceding live result; they are not enabled by this request. The original no-access rule continues for dependent work: **F is paused**, because its required admin verification returns 403. The hosting challenge, static security.txt delivery and unanswered service facts remain follow-ups; G retains the handoff's November 14 boundary.

A adds the approved booking next steps and internal links on nine pages, fixes Home card destinations, reorders Services and adds the Corporate mobile quote shortcuts and Contact corporate card. Existing form, phone, mail and service facts stay intact. The footer widget addition remains deferred.

## Evidence before the live request

| Check | Result |
| --- | --- |
| Original staging38 rehearsal and browser proof | PASS, including 262 browser assertions; [full staging record](2026-11-01-bundle-a-staging38.md) |
| Exact live corporate source | Read-only capture; 17,902 bytes, SHA256 f5a3a8f364730c4ee8295ff374efdd63730c1e0b59498dff6cdcc1871ea8241d |
| Portable terminal-newline anchor | Only one final LF removed from old/new mobile-quote fragments; staging applied output is byte-identical; live no-LF/one-LF/two-LF roundtrips pass |
| Fresh rehearsal from live, with uploads | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/152945-review-a-live-rehearse-20261101.md): apply, repeat apply, exact fingerprint rollback, reapply, pages 200, rendered Home/client copy gates, no new site PHP errors |
| Actual live preflight after rehearsal | [PASS](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/153137-review-a-live-dry-20261101-2.md): all 23 page fragments, jump snippet and CSS ready; no content write |
| Code checks | Gates passed for the rehearsed code; rechecked on the final request/documentation commit |

The first [live preflight](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/152210-review-a-live-dry-20261101.md) safely refused the mobile insertion because live lacks staging's terminal LF. The [source read](https://github.com/alanmz-crypto/new-masseuse-site/blob/ops-log/reports/2026-11-01/152401-review-a-live-corporate-source-20261101.md) confirmed the difference. The source plan and generated payload now remove exactly one LF from BOTH sides of that pair; all wording, links, replacement counts and snippet hashes remain unchanged. The original LF stays outside the replacement on staging, preserving its previously verified result. No global whitespace normalization or relaxed drift acceptance was added.

The live-source rehearsal reports WordPress **7.1.4**, whereas E's earlier staging source was captured on 7.1.3. This is a read-only observation, not an agent update. A has passed on the fresh live copy. Before B–E release preparation, refresh installed source/requirements; E's explicit version guard must not be broadened without matching source inspection and rehearsal.

## Exact phone steps

1. Open [release PR #68](https://github.com/alanmz-crypto/new-masseuse-site/pull/68), confirm **Gates** is green and there are no conflicts, then tap **Merge pull request → Confirm merge**.
2. Open [Site ops (LIVE)](https://github.com/alanmz-crypto/new-masseuse-site/actions/workflows/site-live.yml). Tap **Run workflow**, select branch **main**, type exactly `live-review-a-20261101-1` in the confirmation field, then tap **Run workflow**.
3. When it finishes, tell this session **“A done”** or send the run link. The agent reads the full report before B is prepared. If the run fails, stop there; do not retry a used ID or treat partial verification as a successful release.

The prepared request is `ops/live-request.json`: `review-a/push-bundle-a.sh --apply`, target live, tests auto. Preparing or merging it does not apply content; only Ryan's confirmed LIVE workflow does.

## Backup, rollback and post-release checks

The push takes a fresh live DB export and verifies its encrypted off-site copy **before** applying. The installer writes a private before-snapshot with exact prior whole stores, rejects changed anchors/mixed state, reads back writes and restores resources on save failure. The real live snapshot filename is only known after Ryan's run; never substitute staging's snapshot.

After the run, read both automatic post-change and restore-import sections, verify the intended page/snippet/CSS differences, and run the live-enabled A browser checker once. Expected reference pixels may differ because the reference copy predates these releases. A bot challenge is INVALID verification, never a pass; stop at its first appearance and retain the uncompleted check as an explicit follow-up.

If rollback is needed, prepare a separate Ryan-owned LIVE request for `review-a/push-bundle-a.sh --rollback $HOME/private-backups/<actual-live-snapshot>`, with a fresh export/off-site copy and exact guards. Do not roll A back through later bundle edits. After all bundles, reverse order is E → D → C → B → A.

## Next releases and deferred work

- B follows verified A; it includes the already-approved default/Home share card.
- C, D and E remain staging-applied drafts (#64, #65, #66). Refresh each actual live predecessor's bytes, prepare its live hashes/host guards, rehearse and dry-run, then give Ryan that bundle's merge/run steps. C/D/E are not portable by host-allow-list changes alone. C/D/E origin/browser verifiers also need the exact live base enabled.
- The final whole-site public interactive suite remains incomplete. D/E's successful staging public captures do not establish a durable AntiBot fix. No hosting support message has been sent or security control changed.
- Staging security.txt is confirmed 404 through SiteGround; C's virtual route passing Apache rehearsal does not prove live delivery. The static fallback remains Ryan's lane under the handoff.
- F's six WordPress option changes have not run. Keep it paused until its admin verification is accessible. This release does not require changing admin access or URL options.
- Butsaba questions 2/3/4/6/7 remain pending; confirmed session-length consistency is separate. G and all owner-only work retain their existing boundaries.

[Complete staging status, snapshots and follow-up evidence](https://github.com/alanmz-crypto/new-masseuse-site/blob/codex/2026-11-01-review-bundle-f/docs/reviews/2026-11-01-review-fix-status.md).

# Finish Peek live verification

**LIVE #30 installed Peek successfully, then stopped at an exact-state mismatch. Do not rerun #30 or reinstall the release.** The mismatch is now reproduced and identified: ordinary page loads generated three Spectra asset caches, replacing two stale rows. The Peek snippets, page content and every other protected row matched the installation snapshot.

The original recovery journal stays byte-for-byte unchanged. The correction accepts only the five proved metadata changes, bound to that journal, the original SQL backup and the exact release. Another changed row, altered cache value or changed source still stops verification. Recovery removes only the owned Peek release and restores its CSS/settings while retaining the independently generated caches; it does not claim historical whole-database identity.

The cold-copy proof passed at **17:57:32 UTC** on November 11, Actions **43072850520**. It restored the exact original SQL without URL or data changes, reproduced the actual cache values through three normal page requests, proved stable repeats, and verified deletion of the temporary copy. The particular live request that generated the caches is unknown.

The corrected actual-state copy **43075719186** passed at **18:18:45 UTC**: all seven incident checks, all 17 HTML responses with Peek installed and all 17 after rollback, exact state after each response phase, unchanged private recovery files, and verified deletion of the copy. The first attempt stopped in the test runner; its one-line stdin correction has a regression test. These are static response checks, not a new browser matrix.

The subsequent live read-only check **43076682138** passed at **18:20:00 UTC**, source `b0288b4b80aab6f3c3eec01fac559bc8520ca570`. Both applied and recoverable markers matched the exact state `b34e86f7…af4427`, original journal, manifest/profile, owned IDs and pinned incident receipt. There has been no live purge, rollback or reinstallation since #30.

The separate finish operation is `push-peek-release.sh --apply --finish-live30`. It cannot call installation or preflight. It verifies the existing installation and recovery path, preserves a fresh SQL backup, the immutable journal and the pinned incident receipt with verified encrypted off-site readback, then purges the page cache and verifies all 15 visitor pages plus Search and 404. Final exact-state verification is mandatory. Failed output is retained instead of being swallowed.

The accepted navigation, summaries and research wording are unchanged. Your iPhone Safari acceptance still applies; no new phone test or broad browser matrix is required for this verification/recovery correction. Fluidity refinements remain deferred.

The new request is **`live-peek-finish-20261111-1`**, with automatic post-change and backup-restore checks enabled. Once final Gates is green and the PR has no conflicts, Ryan takes these two steps:

1. Merge the finish-verification PR from `codex/peek-live-verification-diagnostics` into `main`.
2. Open GitHub → Actions → **Site ops (LIVE)** → **Run workflow** → branch **main** → confirm **`live-peek-finish-20261111-1`**.

The repository's `CLAUDE.md` says: “Live: never start it.” Ryan retains the merge and manual LIVE start. The earlier first-install steps are superseded. The agent then reads the new operation report, records the fresh backup and verification results, and reports completion only if the finish checks pass.

The [detailed readiness record](2026-11-10-peek-release-readiness.md) preserves the original rehearsal, #30 report, actual recovery files, cold proof, exact hashes and known limitations.

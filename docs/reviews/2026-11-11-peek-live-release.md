# Finish Peek live verification

**LIVE #30 installed Peek successfully, then stopped at an exact-state mismatch. Do not rerun #30 or reinstall the release.** The mismatch is now reproduced and identified: ordinary page loads generated three Spectra asset caches, replacing two stale rows. The Peek snippets, page content and every other protected row matched the installation snapshot.

The original recovery journal stays byte-for-byte unchanged. The correction accepts only the five proved metadata changes, bound to that journal, the original SQL backup and the exact release. Another changed row, altered cache value or changed source still stops verification. Recovery removes only the owned Peek release and restores its CSS/settings while retaining the independently generated caches; it does not claim historical whole-database identity.

The cold-copy proof passed at **17:57:32 UTC** on November 11, Actions **43072850520**. It restored the exact original SQL without URL or data changes, reproduced the actual cache values through three normal page requests, proved stable repeats, and verified deletion of the temporary copy. The particular live request that generated the caches is unknown.

The first targeted actual-state copy passed initial exact verification, then stopped because its test runner consumed the route list after the homepage. The runner is corrected; a new copy test will check all 17 visitor responses with Peek installed and after rollback, with exact-state checks after each response phase. A subsequent read-only check on live must pass before the separate finish request is prepared. **No new LIVE request is ready yet.**

The planned finish operation is `push-peek-release.sh --apply --finish-live30`. It cannot call installation or preflight. It verifies the existing installation and recovery path, preserves a fresh SQL backup, the immutable journal and the pinned incident receipt with verified encrypted off-site readback, then purges the page cache and verifies all 15 visitor pages plus Search and 404. Final exact-state verification is mandatory. Failed output is retained instead of being swallowed.

The accepted navigation, summaries and research wording are unchanged. Your iPhone Safari acceptance still applies; no new phone test or broad browser matrix is required for this verification/recovery correction. Fluidity refinements remain deferred.

The repository's `CLAUDE.md` says: “Live: never start it.” Ryan retains the merge and manual LIVE start. After the copy proof, live read-only check and final Gates pass, the release PR will contain the exact two steps and a new confirmation ID. The earlier first-install steps are superseded.

The [detailed readiness record](2026-11-10-peek-release-readiness.md) preserves the original rehearsal, #30 report, actual recovery files, cold proof, exact hashes and known limitations.

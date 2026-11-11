# Peek release review

**Ready for your release go. Live is unchanged.** The accepted staging design
and the complete installation/browser/rollback rehearsal on a fresh copy of
live have passed. The temporary copy was deleted with verified cleanup.

This release brings the accepted navigation to the 15 live visitor pages,
with the title strip also covering Search and the 404 page:

- Title strips and section rails; approved summaries on Thai, Therapeutic,
  Deep Tissue and Chair.
- One previous and one next link around each summary, with contained scrolling.
- Boxed index button with an X; previews stay put until you tap to jump.
- No automatic floating section label beside the rail; outside taps close the
  header menu.
- Remove the old reading-progress bar and scroll-to-top button; improve dark
  Search contrast.

The final core also fixes a title-strip resize timing error found during testing.
Page wording, research qualifications, SEO, menu links and forms are preserved.
Existing jump links and the no-JavaScript fallback remain available. The four
experimental navigation pages stay on staging; no staging database is promoted.

Your iPhone Safari acceptance is recorded, including acceptance of the final
controls. Fluidity refinements remain deferred until after launch. No generic
Safari retest is pending.

The final evidence is complete:

| Check | Result |
|---|---|
| Exact release source Gates | 55 passed, 0 failed |
| Corrected staging-copy browsers | 25,343 passed, 0 failed; exact recovery |
| Staging39 deployment and final verification | Fresh backups verified off-site; exact core, summaries and CSS |
| Focused public staging browsers | 752 passed, 0 failed |
| Fresh live-copy browsers | 24,737 passed, 0 failed; all 28 suites/13 phases |
| Installation and recovery on the copy | Install, repeat, exact rollback, reapply, recovery after browsers and final reapply passed |
| Copy cleanup | Temporary site, database and owned resources removed; only test evidence retained |

The final [live-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43006077712)
passed on November 11 at 11:33:22 UTC. Independent audits verified the raw counts,
433 screenshots, unchanged protected data and recovery records. Chromium,
Firefox and Linux WebKit were tested; this does not claim assistive-technology
or OS text-scale validation. The [detailed readiness record](2026-11-10-peek-release-readiness.md)
holds exact source, artifact and snapshot hashes, earlier failures and their fixes.

Before installation or rollback, the prepared release wrapper requires a fresh
database export and verified encrypted off-site backup. It preserves the exact
recovery journal even if installation fails. Recovery restores the original
navigation/settings and removes only this release's exact records; unexpected
changes stop recovery for inspection.

The reviewed [live-enable patch](../inter-model/peek-visitor-release-enable-live.patch)
is still disabled. Your go permits applying it, running the read-only live drift
check, and preparing the release PR and exact LIVE request. That check must pass
against the current live stores; the copied-site proof does not waive it.

Then you retain both launch actions: merge the reviewed PR after Gates passes,
and start **Actions → Site ops (LIVE) → Run workflow** on **main**, using the
prepared request's confirmation ID. I will supply the exact PR and ID after the
dry run. Choose a window when we can immediately review the live report and use
the tested recovery if needed. No date or traffic window is inferred here.

The repository's [CLAUDE.md, Cloud sessions](../../CLAUDE.md#cloud-sessions-claudeaicode-the-claude-app-claude_code_remotetrue)
requires: “Show Ryan the exact change and wait for his go.” Your temporary-copy
approval completed the rehearsal; the distinct live-release go is the next step.

I finished: the complete release rehearsal and review.
Next step: Ryan gives the release go; Codex runs the guarded live dry run and prepares the PR/request.
Next lane: Codex prepares; Ryan merges and starts live.
See my work: [Detailed release evidence](2026-11-10-peek-release-readiness.md).
Arc: none (ad-hoc)
TL;DR: Ready for the guarded live-release sequence; live remains unchanged.

## Why this PR exists

PR #20 merged to `main` with the first 24 commits only. The branch's last **8 commits were never pushed** (the push did not reach GitHub), so `main` still lacks the code and notes for the research-panel update that went live on 2026-10-19. **Nothing here changes a website: everything below is already live.**

## What is in it (8 commits)

- **Sandwich update of the Thai and Therapeutic research sections** (live 16:22 UTC): the discouraging results the audit found missing (a harm review, a Thai-massage case report and commentary, null and negative trials), each panel now positives, then the weaker and negative results in full, then an encouraging close. New `evidence-update/` installer, builder and push script (swaps only the research section of both pages; both-or-neither guard; exact rollback).
- **Safety paragraph on who was involved** (massage therapists were the practitioners in 8 of 138 reported events; chiropractors 33; the rest unregistered people or other health professionals), worded as the 2014 review supports it, not as "the untrained caused it".
- **Thai and Therapeutic installers recognise the version-1 research text**, so the live rollback commands recorded in AGENTS.md keep working until, and after, the update is applied.
- **Live-enable commit** for the update (allow-list only; applied by Ryan).
- **staging36 is the working copy**: the read-only tools accept it and `post-change-test.sh live` uses it as its reference; parity with live verified over SSH.
- AGENTS.md entries with every rollback command.

## Verification

On live: server-side public checks 27/27 on both pages, `prove-restore.sh live` only the two pages and row counts. On staging34 and localhost: real editor 0 invalid blocks, Chromium and Firefox, axe 0 violations, contrast 9.13:1 or better, rollback byte-identical. This computer's own browser checks of live are waiting on a SiteGround bot challenge (not a failure). `check-all.sh`: all gates pass.

🤖 Generated with [Claude Code](https://claude.com/claude-code)

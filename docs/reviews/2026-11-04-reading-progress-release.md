# Reading progress: owner-run live release

**Prepared, not deployed.** Ryan merged [source PR #71](https://github.com/alanmz-crypto/new-masseuse-site/pull/71)
at `249c091` and said “Merged. Next.” [Release PR #72](https://github.com/alanmz-crypto/new-masseuse-site/pull/72)
is on `release/2026-11-04-reading-progress`. No live apply has run and no agent starts
the LIVE workflow.

The release adds the same three-pixel gold reading-progress indicator already
verified on all twelve staging pages. Future public theme pages also receive it.
Reduced motion, forced colors, printing, unsupported browsers and the admin
toolbar still suppress it. Page content, SEO, booking links and settings are
unchanged. Sitewide CSS SHA256 remains
`f7cbdd8c307ca1fb270b3109d1b705847dba835926b2f7cca2f4df88a3dc0072`.

## Evidence and preparation status

- [Staging record and exact rollback](../../scripts/work-with-me-employer/reading-progress-sitewide/README.md): deployed at 07:35 UTC; [focused public check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41793411463) **560/0** across all twelve pages; [apply/post-change/restore report](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41793273857) all five groups passed, with encrypted offsite backups and intended CSS/revision differences only.
- [Read-only live inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41795017487) established the exact stored live origin `http://thaisolitude.com`. The installer allows only that live origin; public checks use the HTTPS apex. This is target recognition, not an address-setting change.
- **PASS:** [fresh live-source rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41795168279), request `ui-progress-live-rehearse-20261104`, finished 07:55 UTC. WordPress 7.1.4, `ready-empty`, 65,418 original CSS bytes. Apply, repeat apply, exact rollback, re-apply, rendered content gates and all twelve pages' browser checks passed. Only CSS post 29 and expected revision rows changed. Chromium 153 verified native animation; Firefox 155 verified fallback. The upload-free copy had identical Home overflow with/without the feature, so this is not evidence of new overflow on the public site.
- **PASS:** [read-only live dry run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41795305199), request `ui-progress-live-dry-20261104`, finished 07:56 UTC: `ready-empty`, 65,418 CSS bytes, nothing changed.
- **PREPARED:** request `live-reading-progress-20261104-1` in `ops/live-request.json` (`c05fed7`), with automatic post-change and restore checks. Preparing this file does not run it. Gates must be green on release PR #72 before merging.
- **NOT RUN:** owner LIVE dispatch, actual live backup/snapshot, public post-change checks and focused live verification.

## Owner steps after preparation passes

1. Merge [release PR #72](https://github.com/alanmz-crypto/new-masseuse-site/pull/72) to `main` after its Gates check is green.
   Source PR #71 is already merged; this is the separate enable-live/request PR.
2. Open [Site ops (LIVE)](https://github.com/alanmz-crypto/new-masseuse-site/actions/workflows/site-live.yml),
   choose **Run workflow**, select **main**, enter exactly
   `live-reading-progress-20261104-1` as confirmation, then run it.

Merge first: the workflow reads the request from `main`. The run takes and
verifies an encrypted offsite database export before writing, applies the block,
secures the exact rollback snapshot offsite, checks the twelve public routes,
and runs the standard post-change and restore checks.

## Follow-up after the owner's run

Read the owner-run report before claiming deployment or success. Then, from a
non-main branch containing this release, run the focused **read-only** check:

```sh
scripts/ci/request.sh run --id ui-progress-live-browser-20261104 --target live --script reading-progress-sitewide/check-reading-progress.mjs --args 'https://thaisolitude.com inline'
```

Record the actual live backup, exact snapshot, focused browser result, broad
post-change result and restore differences here and in the open-items note.
A hosting challenge is INVALID, not a pass; follow the repository's single-later-
retry rule instead of looping. Pinned Firefox 155 verifies fallback, not native
animation in newer Firefox; Chromium verifies the native indicator.

## Rollback distinction

Live begins in `ready-empty`: apply appends the sitewide block, and rollback
using the snapshot from the **actual live run** removes the indicator entirely
and restores every prior CSS byte. Staging began with v1, so its saved rollback
restores the original four-page indicator. Do not use a staging snapshot on live.

The future live snapshot is **PENDING** until apply prints it. Any rollback is a
separate owner-run LIVE request with that exact snapshot and a fresh verified
backup. The installer refuses later CSS drift rather than overwriting it.

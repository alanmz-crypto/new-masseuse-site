# Reading progress: live release

**LIVE on every public page since November 4, 2026, 08:00:26 UTC.** Ryan merged
[source PR #71](https://github.com/alanmz-crypto/new-masseuse-site/pull/71), then
[release PR #72](https://github.com/alanmz-crypto/new-masseuse-site/pull/72) to
`main` at `1efeb7e0a437f6d8682509c9545ab4e4efdbee6b`, and personally started
[the LIVE run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41796118623).
Request `live-reading-progress-20261104-1` is completed; do not reuse it.
No agent merged main or dispatched LIVE.

The three-pixel gold progress indicator uses the same frozen CSS verified on
staging, including future public theme pages. Reduced motion, forced colors,
printing, unsupported browsers and the admin toolbar still suppress it. Page
content, SEO, booking links and settings are unchanged. Sitewide CSS SHA256:
`f7cbdd8c307ca1fb270b3109d1b705847dba835926b2f7cca2f4df88a3dc0072`.

## Actual live results and limits

- All twelve origin/public route checks returned HTTP 200 with the sitewide CSS
  and license after apply. Preflight was `ready-empty`, 65,418 original CSS bytes.
- Code gates, rendered Home/client verifiers, posture, page statuses and PHP-log
  checks passed. No site PHP errors, warnings or deprecations were logged.
- The generic comparison uses the historical **staging36** reference. It reports
  structured-data counts **27 versus 23** (zero parsing failures) and **20 of 20
  pixel views different** (zero missing captures). These are known old-reference
  differences, not an aggregate pass; the broad command exited 1. Do not describe
  the complete generic live suite as passing.
- Restoring the actual pre-apply export imported successfully (exit 0), comparing
  **94 fingerprints on each side**. Differences were CSS post 29, its expected
  post revision rows and SiteGround Security activity-log row counts. Page
  content, SEO, option values and snippets were unchanged.
- **PASS:** [focused public live check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41797118924), `ui-progress-live-browser-20261104`, finished 08:10 UTC: **560 assertions, zero failures** across all twelve pages, 1280/390/320 widths and both themes. Native progress, changing disclosure heights, unchanged content/geometry, no added side-scroll and reduced-motion/print/forced-colors/admin-toolbar exclusions passed.
- Ryan reported the indicator working on a tablet and on the iPhone after
  turning off **Reduce Motion**. Its initial absence on the iPhone was the
  intended reduced-motion exclusion. This is owner-reported device verification;
  no CSS or behavior change was needed. Automated newer Firefox/Safari animation
  coverage is not claimed; pinned Firefox 155 tests fallback and Chromium tests
  native animation.

## Backups and exact rollback

The [owner-run report](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41796118623)
verifies the fresh database export and exact before-snapshot were stored encrypted
in `offsite-2026-11`, read back and matched to the server copies:

- Database: `live-before-reading-progress-sitewide-20261104-080010.sql`,
  **3,916,951 bytes**, plaintext SHA256 prefix `3b30badd45f1`.
- Exact live snapshot: `reading-progress-sitewide-before-20261104-080026-19484792.json`,
  plaintext SHA256 prefix `f9c2d44651a9`, under `$HOME/private-backups/`.

Live began in `ready-empty`: an exact rollback using this **live snapshot**
removes the indicator entirely and restores every prior CSS byte. The staging
snapshot `reading-progress-sitewide-before-20261104-073535-361f0228.json` instead
restores the four-page v1 indicator. Never use a staging snapshot on live.
Any rollback is a separate owner-run LIVE request with a fresh verified backup;
the installer refuses later CSS drift rather than overwriting it.

## Preparation evidence

- [Staging record](../../scripts/work-with-me-employer/reading-progress-sitewide/README.md): deployed at 07:35 UTC; [focused public check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41793411463) **560/0** across twelve pages; [staging apply/post-change/restore report](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41793273857) all five groups passed.
- [Read-only live inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41795017487): exact stored live origin `http://thaisolitude.com`; public checks use HTTPS. The stored address was not changed.
- [Fresh live-source rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41795168279): WordPress 7.1.4, pristine append, repeat apply, exact rollback, re-apply, content gates and all twelve pages' browser checks passed. Chromium 153 verified native animation; Firefox 155 verified fallback. The upload-free copy's Home overflow was identical with/without the feature.
- [Read-only live dry run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41795305199): `ready-empty`, 65,418 CSS bytes, nothing changed.

## Next lane

Merge the completion-notes PR after Gates is green. That PR records evidence only; it needs no new live enablement,
request or LIVE run. Keep the existing reduced-motion behavior.

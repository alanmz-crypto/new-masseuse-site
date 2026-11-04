# Section guide: live release

**READY FOR OWNER LAUNCH; not live.** Ryan authorized the live release on November 4, 2026
("Push to live"). The source and staging evidence merged in
[PR #74](https://github.com/alanmz-crypto/new-masseuse-site/pull/74) at
`47536cd06c1d81733d5cd312c09c2c0fe486caf3`, 10:47:28 UTC.

This release installs the same native “On this page” guide reviewed on staging
on Thai, Therapeutic, Deep Tissue and Chair Massage. It preserves each page's
original destinations and adds **Questions** and **What Research Says**, ordered
by the actual headings. Thai/Deep Tissue have five items; Therapeutic/Chair have
six. No page copy, SEO, research sources or booking links change. The sitewide
reading-progress indicator remains intact. The guide works with Reduce Motion.

## Frozen release payload

- CSS SHA256: `3b74dcdef902a484d9c3c2abf8f7070dd2a8956f30b3b7d3c7295b2d00ea5eb7`.
- Current runtime SHA256: `071149c634d51bb6d2b7e3f4de59b695a5802a3e63c7da1939b3fa1684210e2b`.
- The only production code changes add exact HTTP/HTTPS apex `thaisolitude.com`
  origins to the installer and `live` to the guarded push script. The browser
  verifier accepts HTTPS apex for the later read-only live check.
- `www`, alternate ports and unrelated hosts remain refused. Live's stored
  `http://thaisolitude.com` address is not changed; public verification uses HTTPS.

## Preparation evidence

- [Staging package evidence](../../scripts/work-with-me-employer/section-guide/README.md):
  current Questions version applied at 10:22:43 UTC; fresh public Chromium
  **375/0**, rehearsal Chromium and Firefox **375/0 each**, all five broad checks
  passed. Snapshot and fresh export verified encrypted offsite; restore import
  succeeded and only the intended snippet/cache changed for that staging upgrade.
- [Fresh live-source rehearsal with uploads](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41822042344)
  (`ui-section-guide-live-rehearse-20261104`, commit `6194854`, finished
  **11:40:24 UTC**): **PASS** on WordPress **7.1.4**, copied read-only from live
  with **268 MB** of files. Preflight was `ready` with **66,514 CSS bytes**;
  installation produced **69,937 bytes**. Repeat apply was a no-op. Exact rollback
  removed only the created guide snippet and restored every original CSS byte;
  baseline fingerprints matched with WPCode's derived cache set aside. Reapply
  matched the first applied state with new snippet/SEO IDs normalized. Only CSS
  post **29**, the new guide snippet and WPCode's cache changed in the tracked
  content fingerprints. Page statuses, regulated copy gates and PHP checks passed.
- The same rehearsal passed **375 browser assertions, zero failures each** in
  Chromium **153.0.8010.12** and Firefox **155.0**. The 390-pixel Thai screenshot
  was visually reviewed: all five entries fit, follow page order and avoid the
  scroll-to-top control. This screenshot is from the isolated live copy, not live.
- [Read-only live preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41822180450)
  (`ui-section-guide-live-dry-20261104`, commit `a03f783`, finished
  **11:40:57 UTC**): **PASS**, exact stored origin `http://thaisolitude.com`,
  `ready`, **66,514 CSS bytes**. No WordPress data changed.
- Pinned PHP **8.5** syntax, Bash syntax, Node syntax and **15 actual origin-guard
  cases** passed, including rejection of www, alternate ports/paths and unrelated
  hosts. An independent code review found no blocker; frozen hashes are unchanged.
  GitHub Gates passed the rehearsal and preflight source commits; final release
  HEAD must also show a green Gates check before merge.

Staging rehearsed a v2-to-v3 upgrade; the live-source rehearsal verified a fresh
installation and full removal. These are different rollback paths. Rehearsal
browser checks ran after reapply, not while rolled back; installer read-back and
baseline fingerprints verify rollback. The generic PHP scan precedes the browser
suite; browser page-error checks still ran. The generic ten-page status list
omits Chair/Corporate, which the package browser suite loaded separately.

## Backup and rollback

The live apply must create a fresh database export and verify its encrypted
offsite copy before writing. It then appends the frozen CSS and creates one owned
WPCode snippet. The exact before-snapshot must also be verified encrypted offsite
before the six origin checks. The workflow runs broad post-change checks and a
restore proof automatically.

The actual live export and snapshot names do not exist yet. Record them from the
owner's run; never substitute a staging snapshot. A fresh-install rollback restores
every prior CSS byte and removes only this run's unchanged owned snippet. It refuses
later CSS, snippet or identity drift. Rollback uses a separate owner-run live
request and a new verified database backup.

The generic live comparison still uses historical staging36: previous releases
showed schema-count and pixel differences against that old reference. Read each
result; do not call the broad suite all-pass if those comparisons differ or if
hosting returns a robot challenge. HTTP 202 is INVALID, not a pass.

## Owner launch and verification

The request **`live-section-guide-20261104-1`** is prepared in
`ops/live-request.json`, targeting `section-guide/push-section-guide.sh --apply`
with automatic post-change and restore tests. It has not run. Do not reuse this
ID after the actual live run completes.

1. Merge [release PR #76](https://github.com/alanmz-crypto/new-masseuse-site/pull/76)
   after Gates is green. It includes the notes from superseded PR #75; only this
   release PR needs to be merged.
2. Open [Site ops (LIVE)](https://github.com/alanmz-crypto/new-masseuse-site/actions/workflows/site-live.yml),
   choose **Run workflow**, select **main**, enter **`live-section-guide-20261104-1`**
   in the confirmation field, then choose **Run workflow**.

No agent merged main or started that workflow. The repository's `CLAUDE.md` and
`ops/README.md` reserve the actual live launch to Ryan.

After the run, read its report, record the verified backup/snapshot and restore
evidence, and run the read-only browser verifier against `https://thaisolitude.com`:

```sh
scripts/ci/request.sh wait live-section-guide-20261104-1 1440
scripts/ci/request.sh run --target live --script section-guide/check-section-guide.mjs --args 'https://thaisolitude.com inline' --id ui-section-guide-live-browser-20261104 --no-wait
```

The browser request must wait until the live apply succeeds; a merge alone is
not evidence of installation.
Touch coverage is emulated; Safari and physical devices are not directly tested.

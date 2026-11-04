# Section guide: live release

**PREPARING; not live.** Ryan authorized the live release on November 4, 2026
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
- Fresh live-source rehearsal with uploads: pending.
- Read-only live preflight: pending.
- Local syntax and GitHub Gates: pending.

Staging rehearsed a v2-to-v3 upgrade. Live is expected to be a fresh installation:
its rehearsal must prove the current payload can be added, repeated as a no-op,
rolled back completely and reapplied, with all baseline fingerprints restored
apart from WPCode's derived cache. These are different rollback paths.

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

The reviewed live request will be prepared after the rehearsal and preflight pass.
Ryan merges the release PR and starts **Site ops (LIVE)** on **main**, confirming
the exact request ID. No agent merges main or starts that workflow.

After the run, read its report, record the verified backup/snapshot and restore
evidence, and run the read-only browser verifier against `https://thaisolitude.com`.
Touch coverage is emulated; Safari and physical devices are not directly tested.

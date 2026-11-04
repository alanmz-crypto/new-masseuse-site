# Section guide: live release

**LIVE since November 4, 2026, 11:46:23 UTC.** Ryan merged
[release PR #76](https://github.com/alanmz-crypto/new-masseuse-site/pull/76) at
`f392749efac9310fb5acf9980544b354e097a0e1`, then personally ran
[41823566103](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41823566103).
Request `live-section-guide-20261104-1` is completed; never reuse it.
The source and staging evidence merged earlier in PR #74 at `47536cd`.
No agent merged main or dispatched LIVE.

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
  GitHub Gates passed the rehearsal and preflight source commits and both final
  release checks at `b74b631`; the owner merged that reviewed head.

Staging rehearsed a v2-to-v3 upgrade; the live-source rehearsal verified a fresh
installation and full removal. These are different rollback paths. Rehearsal
browser checks ran after reapply, not while rolled back; installer read-back and
baseline fingerprints verify rollback. The generic PHP scan precedes the browser
suite; browser page-error checks still ran. The generic ten-page status list
omits Chair/Corporate, which the package browser suite loaded separately.

## Actual live results and rollback

The owner-run apply succeeded. All six origin checks returned HTTP 200 with
license/CSS and the expected runtime scope. The fresh export and exact snapshot
were verified encrypted offsite in `offsite-2026-11` and read back identical:

- Export: `live-before-section-guide-20261104-114610.sql`, **4,002,608 bytes**,
  plaintext SHA256 prefix **`8a33a892f676`**, verified before mutation.
- Snapshot: `section-guide-before-20261104-114623-1ca64312.json`, plaintext
  SHA256 prefix **`76ca289d81dc`**, verified before public checks.
- The installer appended the frozen CSS and created guide snippet **1338**.
- Restore import **exit 0**, **94 restored / 95 current fingerprints**. Expected
  differences: CSS post29, new snippet1338, WPCode cache, related post/meta/term
  rows and SiteGround Security activity-log row counts. Pages, SEO and unrelated
  options/snippets were unchanged.
- The automatic broad public suite was **INVALID: HTTP 202 robot challenge**;
  it ran no page/schema/pixel/PHP checks. This is not an aggregate pass. A later
  bounded read-only broad follow-up completed separately below.
- [Once-only broad follow-up](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41824811088)
  (`ui-section-guide-live-postcheck-20261104`, finished **11:59:23 UTC**) completed
  public verification: code/rendered-copy/posture gates, page/sitemap/robots HTTP
  checks and site PHP logs passed. Its overall result is **FAIL** because the
  historical staging36 comparison has **27 versus 23 schema items** and **20/20
  different pixel views** (zero schema parse failures or missing captures).
  These match the old-reference limitations documented for prior live releases;
  they are not an aggregate pass and are separate from the focused guide checks.
- [Focused public live browser check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41824272157)
  (`ui-section-guide-live-browser-20261104`, finished **11:53:34 UTC**) passed
  **375 assertions, zero failures**, Chromium153.0.8010.12. It covers the four
  guides, Questions/Research, exact destinations/order, keyboard/focus, responsive
  layout, themes, reduced motion, touch emulation and excluded-page fallbacks.

An exact rollback uses **this live snapshot**, restores every prior CSS byte and
removes only this run's unchanged guide snippet. It refuses later CSS, snippet
or identity drift. Rollback requires a separate owner-run LIVE request and a new
verified database backup. Never substitute one of staging's upgrade snapshots.
The new paired navigation migration must be rolled back first if it has updated
this guide, restoring this run's exact snippet state before its removal.

The generic live comparison still uses historical staging36: previous releases
showed schema-count and pixel differences against that old reference. Read each
result; do not call the broad suite all-pass if those comparisons differ or if
hosting returns a robot challenge. HTTP 202 is INVALID, not a pass.

## Follow-up verification

The owner launch, focused public browser verification and once-only broad
follow-up are complete. Preserve the initial HTTP202 result and the later
old-reference schema/pixel differences; no further retry is queued.
Touch coverage is emulated; Safari and physical devices are not directly tested.

The subsequent page-navigation change is separate: it fills the inline links,
adds Services to the native guide, and gives both controls one shared list.
See [page-navigation](../../scripts/work-with-me-employer/page-navigation/README.md).

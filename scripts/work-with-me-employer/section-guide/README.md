# Section guide for the four research pages

**Guide with “Questions” and “What Research Says” is applied and verified on staging38; not live.**
Questions was added at **10:22:43 UTC on November 4, 2026**; focused public
browser checks passed at **10:31:33 UTC**.
Source merged via [PR #74](https://github.com/alanmz-crypto/new-masseuse-site/pull/74)
at `47536cd` on November 4, 2026, at **10:47:28 UTC**. Deployment remains
staging38 only. Ryan authorized the live release on November 4, 2026;
production enablement and its owner-run request are being prepared. See the
[live release record](../../../docs/reviews/2026-11-04-section-guide-release.md).
[Preview Thai Massage](https://staging38.thaisolitude.com/thai-massage/) and scroll
past the original section links to open the guide.
This adds a compact “On this page” control to Thai, Therapeutic, Deep Tissue and
Chair Massage. It appears after the existing jump-link row scrolls above the
viewport and hides near the footer. Opening it shows those same labels and
fragment destinations in a native, nonmodal popover. The latest update adds
“Questions” beside the existing “What Research Says” shortcut, targeting each
page's FAQ and research headings. It reuses their IDs or assigns collision-free
IDs only in the rendered DOM; no heading text or stored page HTML changes. All
entries follow the headings' document order. Existing labels and destinations
remain intact, including “Who can benefit” on Therapeutic and Chair Massage;
the guide therefore has five entries on Thai/Deep Tissue and six on
Therapeutic/Chair. The original links remain
in place as the no-JavaScript and unsupported-browser fallback.

The purpose is to make the existing section navigation available while reading
these long pages. The popover uses native Escape and outside-click dismissal,
44-pixel controls, visible keyboard focus, safe-area spacing and a scrollable
panel on short screens. Selecting a destination follows the original fragment
link and moves focus to its heading. A focused or open control stays available
when the original row or footer enters view. The guide is inserted before the
footer in document order so Tab can continue naturally to footer links. If the
footer is absent, it is appended to the body instead. Corporate and Work With Me keep
their existing fixed mobile controls; other pages do not receive the runtime.

This navigation remains usable with Reduce Motion enabled: it adds no animated
transition or smooth-scroll behavior. It supports forced colors and is hidden
when printing or when the admin toolbar is present. Existing page HTML, copy,
heading IDs, citations, booking links, SEO, other snippets and reading-progress
behavior are preserved.

## Modern browser feature and fallback

The [Popover API](https://developer.mozilla.org/en-US/docs/Web/API/Popover_API)
provides the top layer, native light dismissal and keyboard behavior without a
custom focus-trapping dialog. The implementation uses `popover="auto"` and a
`popovertarget` button, following the [HTML popover specification](https://html.spec.whatwg.org/multipage/popover.html).
The [cross-browser feature record](https://web-platform-dx.github.io/web-features-explorer/features/popover/)
dates Baseline availability to January 27, 2025, including the iOS 18.3 dismissal
fix. The guide uses this native UI primitive without requiring anchor positioning.
It feature-detects native popover support before adding controls. Visibility
uses current geometry, with passive scroll updates limited to one per frame, so
instant jumps work as well as gradual scrolling. It does not depend on CSS anchor positioning, scroll-target-group or
an external JavaScript library. Actual browser coverage belongs in the evidence
record below; native support alone is not a claim of tested device behavior.

## Installation and exact rollback

`install-section-guide.php` permits only `http://localhost:8090` and the exact
HTTP/HTTPS staging38 and apex `thaisolitude.com` origins. No other hostname or
port is allowed. It checks Astra,
WPCode, administrator permissions, frozen CSS/PHP hashes and PHP syntax before
writing. The runtime PHP's leading opening tag is removed for the WPCode record.
Frozen payload SHA256 values:

- CSS: `3b74dcdef902a484d9c3c2abf8f7070dd2a8956f30b3b7d3c7295b2d00ea5eb7`.
- Current Questions PHP: `071149c634d51bb6d2b7e3f4de59b695a5802a3e63c7da1939b3fa1684210e2b`.
- Frozen research v2 PHP (`section-guide-v2.php`): `faf54532012660a640fa2d7ff51d87f8e21e781f256e1361000d68de5d35249a`.
- Frozen deployed v1 PHP (`section-guide-v1.php`): `c59264212eba983602621961eb453443cac1993149f55249a6d32af3c59cc777`.

On a fresh installation, the only persistent additions are one marked CSS block
and one active PHP snippet titled `TSSectionGuide v1`. Every existing CSS byte is
preserved before the appended block. On either exact registered prior version,
preflight reports `ready-upgrade`: the installer replaces only that frozen
runtime in the same snippet, preserving its ID, metadata and terms. It changes
no CSS bytes. No page HTML is persisted or rewritten.
The installer refuses partial or duplicate blocks, a conflicting or renamed
runtime snippet, and changed snippet settings. A complete repeat apply is a
no-op. Its private snapshot records all prior CSS bytes and hashes, the exact
site/CSS identity, expected new snippet state, and the created snippet's full
post record, metadata and taxonomy terms. Writes are read back before success;
failed applies attempt to restore the recorded original state.

Rollback requires the exact private snapshot and unchanged applied CSS and
snippet state. A **runtime-upgrade snapshot** restores the full original snippet
record, including code and modified timestamps, and leaves CSS untouched. A
compact frozen registry validates each snapshot's source and destination bytes,
so the rollback chain remains **Questions → research → original guide → remove**. A
**fresh-install snapshot** restores every original CSS byte and removes only its
unchanged newly created snippet, following the
[review-c owned-snippet pattern](../review-c/install-bundle-c.php). Both paths
refuse later edits. The earlier research and v1 removal snapshots remain usable after their
later runtime upgrades are rolled back; the unchanged earlier package at commit
`60a4a49` also retains that removal path. The progress indicator and all earlier
snippets remain part of either restored state.

`push-section-guide.sh` is dry by default and permits staging38 or live. An apply
exports a fresh database, copies it encrypted offsite and verifies the copy
before writing; it then copies and verifies the exact before-snapshot offsite
before public checks. Four service pages must return HTTP 200 with the license,
CSS, scoped runtime marker and both requested shortcut labels. Home and Contact must return 200 with the license
and CSS, without the runtime. HTTP 202 is an invalid bot challenge, not a pass.

## Workflow

Use unique request IDs with the existing Actions request workflow:

```sh
scripts/ci/request.sh rehearse --source staging38 --installer section-guide/install-section-guide.php --with-uploads
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--apply'
```

This exact staging command undoes only the Questions addition, restoring the
research version's exact code, identity, dates and settings with CSS unchanged:

```sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--rollback $HOME/private-backups/section-guide-before-20261104-102243-126c4626.json'
```

After Questions rollback, this existing research rollback command restores the
original guide without changing CSS:

```sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--rollback $HOME/private-backups/section-guide-before-20261104-095607-f0075705.json'
```

The original 09:18 installation snapshot remains valid after the research
rollback. Follow the snapshot chain in reverse order; do not skip an upgrade.

After runtime rollback, the original staging removal command below restores the
pre-guide CSS (including reading progress) and removes the owned snippet. It is
also available using the original package at commit `60a4a49`:

```sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--rollback $HOME/private-backups/section-guide-before-20261104-091816-4f642403.json'
```

Git source changes do not update WordPress. The authorized live release uses its
own reviewed enablement and owner-run workflow; see the
[live release record](../../../docs/reviews/2026-11-04-section-guide-release.md).
A fresh live installation has its own snapshot and rollback; never use a staging
snapshot to undo the live installation.

## Questions verification — November 4, 2026

- Registry/installer PHP 8.5 lint, push-script `bash -n` and `git diff --check`:
  PASS. Frozen runtime files preserve the deployed v1 and research v2 bytes.
- Local Chromium 154 fixture: **391 assertions, zero failures**. The reviewed
  390-pixel mobile fixture fits all five Thai entries in section order. The
  runtime follows each actual page's section order. This is browser emulation,
  not physical-device verification.
- [Current-stage Questions rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41812945785)
  (`ui-section-questions-rehearse-20261104`, commit `fb24d4c`, finished 10:20:36 UTC):
  **PASS** on a fresh WordPress 7.1.3 staging38 copy with uploads. The actual
  **research v2 → Questions v3 → research v2 → Questions v3** migration preserved
  snippet **1349**, passed no-op reapply and restored its full code, identity,
  modified dates and settings on rollback. All **69,937 CSS bytes stayed
  unchanged**. Only snippet 1349 and WPCode's cache fingerprints moved; rollback
  matched baseline with that cache set aside. Page statuses, content gates and
  PHP checks passed. Chromium **153.0.8010.12** and Firefox **155.0** each passed
  **375 assertions, zero failures**. The older rollback chain was reviewed for
  compatibility, not rerun here; its historical proofs below remain separate.
- [Questions staging dry run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41813059181)
  (`ui-section-questions-dry-20261104`, commit `0472c94`, finished 10:21:06 UTC):
  **PASS**, `ready-upgrade`, **69,937 CSS bytes**, no WordPress changes. Both the
  request and report-publication steps completed successfully.
- [Questions staging apply, broad checks and restore proof](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41813690976)
  (`ui-section-questions-apply-20261104`, commit `12614e5`): **PASS**, applied at
  **10:22:43 UTC** in existing snippet **1349**. All six origin checks returned
  HTTP 200 with the expected license, CSS and scoped runtime. Service pages
  included the Questions runtime call and research label; Home and Contact
  remained outside the runtime scope.
- Fresh database export: `staging38-before-section-guide-20261104-102220.sql`,
  **7,370,684 bytes**, plaintext SHA256 prefix `4ce8ccbae2d3`. Exact Questions
  snapshot: `section-guide-before-20261104-102243-126c4626.json`, plaintext SHA256
  prefix `367fa1a875d1`. Both were stored encrypted in `offsite-2026-11`, read back
  and verified identical. The export was verified before mutation and the
  snapshot before public checks.
- **All five broad post-change groups passed** against live: code/rendered/posture
  checks, page/sitemap/robots statuses, structured data **27/27** with zero parse
  failures (modification dates set aside), **20/20 pixel-identical** baseline
  views at zero tolerance, and no site PHP errors/warnings/deprecations.
- Restore proof: **import exit 0**, **95 fingerprints on each side**. Only
  snippet **1349** and WPCode's cache changed; CSS, pages, SEO, other settings and
  row counts were unchanged. The informational comparison exit 3 records those
  expected runtime/cache differences.
- [Focused public Questions check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41814478901)
  (`ui-section-questions-browser-20261104`, commit `d2f2cc3`, finished 10:31:33 UTC):
  **PASS**, Chromium **153.0.8010.12**, **375 assertions, zero failures**. Request
  and report publication both succeeded. All four pages have one Questions item
  and one What Research Says item; each follows a unique native fragment to the
  existing matching heading, closes the panel and preserves original content
  after focus cleanup. Entries follow each actual page's section order; all
  original labels/destinations remain, including Who can benefit where present.
  The suite also covered keyboard navigation, themes, narrow screens, preferences
  and Chromium touch emulation. The actual staging mobile screenshot was visually
  reviewed: all five Thai entries fit cleanly and follow the page's section order.
  Safari and physical devices remain untested.

## Research shortcut verification — November 4, 2026

These results cover research v2 (`faf54532…`), before the Questions addition.

- Research-version local Chromium 154 fixtures: **367 assertions, zero failures**; focused
  research-shortcut checks: **20 assertions, zero failures**. The mobile fixture
  screenshot with the extra link was visually reviewed. These are local results,
  not proof of staging application or physical-device testing.
- [Current-stage upgrade rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41808608914)
  (`ui-section-research-rehearse-20261104`, commit `2691e47`, finished 09:39:56 UTC):
  **PASS** on a fresh WordPress 7.1.3 staging38 copy with uploads. Preflight found
  `ready-upgrade`; apply updated existing snippet **1349**, repeat apply was a
  no-op, rollback restored its full original code, identity, modified dates and
  settings, and reapply passed. All **69,937 CSS bytes stayed unchanged**. Only
  snippet 1349 and WPCode's cache fingerprints changed; rollback matched the
  baseline with that cache set aside. Page statuses, content gates and PHP checks
  passed. Chromium **153.0.8010.12** and Firefox **155.0** each passed **351
  assertions, zero failures**, including the research shortcut.
- [Staging dry-run job](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41808718641/job/126504086471)
  (`ui-section-research-dry-20261104`, commit `76a4abd`): the request step **passed**
  at 09:41:07 UTC, `ready-upgrade`, **69,937 CSS bytes**, no WordPress changes.
  Direct Actions logs confirm that result. The **workflow failed at report
  publication only**: GitHub returned Internal Server Error while pushing to
  `ops-log`, so no published ops-log report exists for this run.
- [First apply attempt](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41809535022/job/126505018884)
  (`ui-section-research-apply-20261104`, commit `3ece9f1`) **stopped before any
  WordPress mutation** at 09:44:20 UTC: the encrypted offsite backup service
  returned HTTP 502 and verification failed. The installer never reached its
  apply phase. The [queued public check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41809702630)
  therefore saw the pre-research guide (**65 assertions, 2 failures**); it does
  not validate the research update. Report publication also failed with GitHub
  Internal Server Errors. These outcomes are recorded from direct job logs.
- A later branch push succeeded, and the
  [protected retry](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41810759635)
  (`ui-section-research-apply-2-20261104`, commit `d0b3c59`) **passed**, upgrading
  existing snippet **1349** at **09:56:07 UTC**. All six origin checks returned
  HTTP 200 with the expected license, unchanged CSS and scoped runtime; each
  service page included the research shortcut label. Home and Contact remained
  outside the runtime scope. This successful retry resolves the earlier blocked
  application; it used the same rehearsed payload.
- Fresh database export: `staging38-before-section-guide-20261104-095544.sql`,
  **7,368,900 bytes**, plaintext SHA256 prefix `e969474168ba`. Exact upgrade
  snapshot: `section-guide-before-20261104-095607-f0075705.json`, plaintext SHA256
  prefix `e0d24d9588c9`. Both were stored encrypted in `offsite-2026-11`, read back
  and verified identical. The export was verified before mutation; the snapshot
  was verified before public checks.
- **All five broad post-change groups passed** against live: code/rendered/posture
  checks, page/sitemap/robots statuses, structured data **27/27** with zero parse
  failures (modification dates set aside), **20/20 pixel-identical** baseline
  views at zero tolerance, and no site PHP errors/warnings/deprecations.
- Restore proof: **import exit 0**, **95 fingerprints on each side**. Only
  snippet **1349** and WPCode's cache changed. CSS, pages, SEO, other settings and
  row counts were unchanged. The comparison's informational exit 3 records those
  expected runtime/cache differences.
- [Fresh focused public browser job](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41811309488/job/126510963652)
  (`ui-section-research-browser-2-20261104`, commit `7ac3171`): the browser request
  **passed at 10:03:09 UTC**, Chromium **153.0.8010.12**, **351 assertions, zero
  failures**. All four pages had exactly one added “What Research Says” item;
  activation updated the native fragment, focused the existing research heading
  through a unique anchor, closed the panel and preserved original content after
  focus cleanup. The suite also covered keyboard navigation, themes, narrow
  screens, preferences and Chromium touch emulation. The actual staging mobile
  screenshot from this job was visually reviewed: the fourth item is visible,
  fits the panel and does not overlap other controls.
  The **workflow failed only when publishing the report**: GitHub returned
  Internal Server Error while pushing `ops-log`. Direct job logs establish the
  browser pass; no published ops-log report exists for this run. Deployment and
  successful browser verification remain intact despite that publication error.

## Original guide verification — November 4, 2026

These results cover the original deployed v1 runtime (`c592642…`), before the
research shortcut update.

- Static validation: installer and runtime pass PHP 8.5 lint in the repository's
  digest-pinned CLI image; push script passes `bash -n`; `git diff --check` passes.
- Local Chromium 154 fixtures: **347 assertions, zero failures**, including
  native keyboard and Chromium emulated-touch dismissal, no-JavaScript/unsupported fallbacks,
  themes, preference exclusions, footer behavior and unchanged content/CTAs.
  Both normal mobile and 320×568 at 200% text screenshots were visually reviewed.
  The initial WordPress rehearsal exposed missed visibility updates when an
  instant jump skips the navigation row. A fixture reproduced both directions;
  current-position checks fixed both directions. The panel also follows the
  trigger's measured height for the enlarged-text reflow case.
- Geometry comparisons remain exact. Each sample independently waits for lazy
  asset requests, fonts and repeated stable layout measurements before comparing
  content and every element's position and dimensions; it does not wait for the
  expected before/after result.
- Firefox's end-of-document Tab behavior matched the automation boundary in
  [Playwright #39268](https://github.com/microsoft/playwright/issues/39268).
  The final guide sits before the footer, and the test requires Tab to focus the
  first footer link and Shift+Tab to return to the last popover link. This tests a
  concrete page successor instead of interpreting focus leaving the document.
  Safari and physical-device behavior have not been tested for this feature.
- [Final staging-source rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41805372496)
  (`ui-section-guide-rehearse-5-20261104`, commit `bacf37e`, finished 09:16 UTC):
  **PASS** on an isolated copy of WordPress 7.1.3 with uploads. Apply, no-op
  reapply, exact rollback, reapply, page statuses, content gates and absence of new
  PHP errors passed. Rollback restored every baseline fingerprint apart from
  WPCode's disposable snippet-list cache; reapply matched with regenerated snippet
  IDs set aside. Only CSS post 29, the new snippet and WPCode's cache changed.
  Chromium **153.0.8010.12** and Firefox **155.0** each passed **331 assertions,
  zero failures**, including exact forward/reverse Tab navigation through the
  footer. Both engines tested the native popover.
- [Final staging dry run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41805476957)
  (`ui-section-guide-dry-3-20261104`, commit `753ad9d`, finished 09:16:57 UTC):
  **PASS**, `ready`, 66,514 original CSS bytes; nothing changed in WordPress.
- [Staging apply, post-change checks and restore proof](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41806275637)
  (`ui-section-guide-apply-20261104`, commit `2950eef`): **PASS**, applied at
  **09:18:16 UTC**. All six origin checks returned HTTP 200 with the license and
  CSS; the runtime appeared on the four service pages and stayed absent on Home
  and Contact. **All five broad post-change groups passed** against live:
  code/rendered/posture checks, pages/sitemap/robots, structured data **27/27**
  (zero parse failures; modification dates set aside), **20/20 pixel-identical**
  baseline views at zero tolerance, and no site PHP errors/warnings/deprecations.
- Fresh database export: `staging38-before-section-guide-20261104-091800.sql`,
  **7,278,947 bytes**, plaintext SHA256 prefix `564b39e4f4d7`. Exact before-snapshot:
  `section-guide-before-20261104-091816-4f642403.json`, plaintext SHA256 prefix
  `fb6661c101b1`. Both were stored encrypted in `offsite-2026-11`, read back and
  verified identical to the server copies. The database was verified before
  apply; the snapshot was verified before public checks.
- The actual export restored successfully (**import exit 0**): **94 fingerprints
  in the restored copy, 95 on staging**. Differences were only Additional CSS
  post **29**, new snippet **1349**, WPCode's snippet-list cache and related post,
  postmeta and term-relationship row counts. Page content, SEO and other settings
  were unchanged. The comparison's informational exit 3 represents these expected
  additions, not an import failure.
- [Focused public staging check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41806377819)
  (`ui-section-guide-browser-20261104`, commit `0a01f67`, finished 09:24:45 UTC):
  Chromium **153.0.8010.12**, **331 assertions, zero failures**. The four service
  pages passed at 1280/390/320 widths in both themes, including exact unchanged
  content/geometry, keyboard dismissal and destination focus, forward/reverse Tab,
  reduced motion, forced colors, print/admin exclusions and unsupported/no-script
  fallbacks. Chromium touch emulation checked tap dismissal; 320×568 at 200% text
  checked reflow. Other pages and their existing fixed controls stayed unaffected.
  The actual staging mobile screenshot was visually reviewed: readable links,
  clean panel spacing and separation from the existing scroll-to-top control.

The broad baseline views do not exercise the open popover. Focused feature
coverage is recorded separately; Safari and physical devices remain untested.

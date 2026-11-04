# Section guide for the four research pages

**Original guide applied and verified on staging38; not live.**
The requested “What Research Says” shortcut passed upgrade rehearsal but is
**not deployed**: backup verification and GitHub service errors blocked release.
The original three-link guide remains on staging.
[Preview Thai Massage](https://staging38.thaisolitude.com/thai-massage/) and scroll
past the original section links to open the guide.
This adds a compact “On this page” control to Thai, Therapeutic, Deep Tissue and
Chair Massage. It appears after the existing jump-link row scrolls above the
viewport and hides near the footer. Opening it shows those same labels and
fragment destinations in a native, nonmodal popover. The update also adds “What
Research Says” to the existing research heading on each of the four pages. It
reuses the heading's ID or assigns a collision-free ID only in the rendered DOM;
no heading text or stored page HTML changes. The original links remain
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
HTTP/HTTPS staging38 origins. **Production is not allowed.** It checks Astra,
WPCode, administrator permissions, frozen CSS/PHP hashes and PHP syntax before
writing. The runtime PHP's leading opening tag is removed for the WPCode record.
Frozen payload SHA256 values:

- CSS: `3b74dcdef902a484d9c3c2abf8f7070dd2a8956f30b3b7d3c7295b2d00ea5eb7`.
- Current PHP file: `faf54532012660a640fa2d7ff51d87f8e21e781f256e1361000d68de5d35249a`.
- Frozen deployed v1 PHP (`section-guide-v1.php`): `c59264212eba983602621961eb453443cac1993149f55249a6d32af3c59cc777`.

On a fresh installation, the only persistent additions are one marked CSS block
and one active PHP snippet titled `TSSectionGuide v1`. Every existing CSS byte is
preserved before the appended block. On the already-deployed original version,
preflight reports `ready-upgrade`: the installer replaces only the exact frozen
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
**fresh-install snapshot** restores every original CSS byte and removes only its
unchanged newly created snippet, following the
[review-c owned-snippet pattern](../review-c/install-bundle-c.php). Both paths
refuse later edits. The original v1 removal snapshot remains usable after the
runtime upgrade is rolled back; the unchanged earlier package at commit
`60a4a49` also retains that removal path. The progress indicator and all earlier
snippets remain part of either restored state.

`push-section-guide.sh` is dry by default and permits staging38 only. An apply
exports a fresh database, copies it encrypted offsite and verifies the copy
before writing; it then copies and verifies the exact before-snapshot offsite
before public checks. Four service pages must return HTTP 200 with the license,
CSS, scoped runtime marker and research shortcut label. Home and Contact must return 200 with the license
and CSS, without the runtime. HTTP 202 is an invalid bot challenge, not a pass.

## Workflow

Use unique request IDs with the existing Actions request workflow:

```sh
scripts/ci/request.sh rehearse --source staging38 --installer section-guide/install-section-guide.php --with-uploads
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--apply'
```

Use the runtime-upgrade snapshot printed by its apply to undo only the research
shortcut after a successful future apply. **No upgrade snapshot exists yet**:
its first apply stopped before the installer ran. The original 09:18 installation
snapshot below remains valid. Do not use it to undo a future runtime upgrade
directly; first use that upgrade's own snapshot.

After runtime rollback, the original staging removal command below restores the
pre-guide CSS (including reading progress) and removes the owned snippet. It is
also available using the original package at commit `60a4a49`:

```sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--rollback $HOME/private-backups/section-guide-before-20261104-091816-4f642403.json'
```

Git source changes do not update WordPress. A future live release requires its
own reviewed enablement and owner-run workflow; this package has no live target.

## Research shortcut verification — staging application blocked

- Current local Chromium 154 fixtures: **367 assertions, zero failures**; focused
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
  therefore saw the old three-link guide (**65 assertions, 2 failures**); it does
  not validate the research update. Report publication also failed with GitHub
  Internal Server Errors. These outcomes are recorded from direct job logs.
- The protected retry request `ui-section-research-apply-2-20261104` is prepared
  **locally only; it has not been submitted or started**. GitHub rejected the
  branch push with Internal Server Error; the connected API fallback timed out,
  and a subsequent read confirmed the request was absent remotely. There is no
  retry run to report. The research shortcut remains **not deployed**. A
  successful apply, its actual upgrade snapshot, verified offsite backup and
  fresh public checks are still required.

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

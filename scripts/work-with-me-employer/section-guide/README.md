# Section guide for the four research pages

**Prepared for staging38; verification and deployment evidence are PENDING.**
This adds a compact “On this page” control to Thai, Therapeutic, Deep Tissue and
Chair Massage. It appears after the existing jump-link row scrolls above the
viewport and hides near the footer. Opening it shows those same labels and
fragment destinations in a native, nonmodal popover. The original links remain
in place as the no-JavaScript and unsupported-browser fallback.

The purpose is to make the existing section navigation available while reading
these long pages. The popover uses native Escape and outside-click dismissal,
44-pixel controls, visible keyboard focus, safe-area spacing and a scrollable
panel on short screens. Selecting a destination follows the original fragment
link and moves focus to its heading. A focused or open control stays available
when the original row or footer enters view. Corporate and Work With Me keep
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
- PHP file: `cb68b9a5aa1159c85d6f86096278e13f6819d9b12e27e14baee483b8c6002d23`.

The only persistent additions are one marked CSS block in Additional CSS and
one active PHP snippet titled `TSSectionGuide v1`. Every existing CSS byte is
preserved before the appended block. No page HTML is persisted or rewritten.
The installer refuses partial or duplicate blocks, a conflicting or renamed
runtime snippet, and changed snippet settings. A complete repeat apply is a
no-op. Its private snapshot records all prior CSS bytes and hashes, the exact
site/CSS identity, expected new snippet state, and the created snippet's full
post record, metadata and taxonomy terms. Writes are read back before success;
failed applies attempt to restore the recorded original state.

Rollback requires that exact private snapshot and unchanged applied CSS and
snippet state. It restores every original CSS byte and permanently removes only
this run's unchanged newly created snippet, following the existing
[review-c owned-snippet pattern](../review-c/install-bundle-c.php). It refuses
later edits instead of overwriting them. The existing progress indicator and
all earlier snippets remain part of the restored original state.

`push-section-guide.sh` is dry by default and permits staging38 only. An apply
exports a fresh database, copies it encrypted offsite and verifies the copy
before writing; it then copies and verifies the exact before-snapshot offsite
before public checks. Four service pages must return HTTP 200 with the license,
CSS and scoped runtime marker. Home and Contact must return 200 with the license
and CSS, without the runtime. HTTP 202 is an invalid bot challenge, not a pass.

## Workflow

Use unique request IDs with the existing Actions request workflow:

```sh
scripts/ci/request.sh rehearse --source staging38 --installer section-guide/install-section-guide.php --with-uploads
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--apply'
```

Use only the exact snapshot path printed by the successful apply:

```sh
scripts/ci/request.sh run --target staging38 --script section-guide/push-section-guide.sh --args '--rollback $HOME/private-backups/section-guide-before-TIMESTAMP-SUFFIX.json'
```

Git source changes do not update WordPress. A future live release requires its
own reviewed enablement and owner-run workflow; this package has no live target.

## Verification evidence — PENDING

- Static validation: installer and runtime pass PHP 8.5 lint in the repository's
  digest-pinned CLI image; push script passes `bash -n`; `git diff --check` passes.
- Local Chromium 154 fixtures: **339 assertions, zero failures**, including
  native keyboard and touch dismissal, no-JavaScript/unsupported fallbacks,
  themes, preference exclusions, footer behavior and unchanged content/CTAs.
  Both normal mobile and 320×568 at 200% text screenshots were visually reviewed.
  The initial WordPress rehearsal exposed missed visibility updates when an
  instant jump skips the navigation row. A fixture reproduced both directions;
  current-position checks fixed them and the enlarged-text reflow case.
- Fresh staging-source rehearsal, including apply, no-op reapply, exact rollback
  and reapply: PENDING.
- Staging dry run, database export and encrypted offsite verification: PENDING.
- Staging apply, exact snapshot, public route checks, post-change comparisons and
  restore proof: PENDING.
- Focused public browser checks, native keyboard/dismissal/focus behavior,
  narrow screens, themes, preferences and unchanged page geometry: PENDING.

Record actual run links and results here before describing staging as deployed
or verified. Browser/device coverage and broad-check limitations must be stated
separately from the focused feature checks.

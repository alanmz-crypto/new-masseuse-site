# Reading progress on every public page

Ryan's follow-up on November 4, 2026: “You can put that on every page.”
This extends the existing three-pixel gold scroll-progress indicator from the
four research pages to every public page rendered by the site's theme, including
Home, About, Services, Corporate Massage, For Practices, Contact, Privacy Policy
and Accessibility. The body selector has no page IDs, routes or citation
dependency, so future public pages also receive it.

The indicator uses the existing light/dark color token, takes no layout space,
does not intercept pointers or add keyboard stops, and adds no JavaScript or
asset requests. It remains absent for reduced motion, forced colors, printing,
unsupported browsers and logged-in views with the admin toolbar. A page without
a scroll range has an inactive timeline and an empty indicator.

## Integration and rollback

The previous `reading-progress/` CSS and installer are preserved unchanged.
`reading-progress-v1.css` here is a frozen copy of the exact deployed CSS.
The new installer verifies hashes of both payloads, then either replaces that
exact v1 block once or appends the v2 block when no indicator exists. Partial,
changed or duplicate blocks are refused. Repeat apply does nothing.

Before writing, it saves and verifies a private snapshot of the complete current
Additional CSS. Rollback verifies the snapshot identity, site, CSS post, payload
hashes and complete applied bytes before restoring the exact previous CSS. Thus
on current staging it restores the four-page version. Later CSS edits block the
rollback. To remove the original indicator too, roll back this update first and
then use the original package's original snapshot.

Only `localhost:8090` and the exact HTTP/HTTPS staging38 origins are allowed.
Production is not enabled. The push script takes a fresh DB export, verifies its
encrypted offsite copy, applies the update, then copies the exact rollback
snapshot offsite before checking all twelve public routes. Page content, booking
links, SEO, snippets and site settings are not edited.

## Workflow

Use unique request IDs with the existing Actions workflow:

```sh
scripts/ci/request.sh rehearse --source staging38 --installer reading-progress-sitewide/install-reading-progress-sitewide.php
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh --args '--apply'
```

Use the exact snapshot printed by apply for rollback:

```sh
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh --args '--rollback $HOME/private-backups/reading-progress-sitewide-before-TIMESTAMP-SUFFIX.json'
```

The original [feature rationale and browser compatibility references](../reading-progress/README.md)
still apply. Verification of this sitewide update is recorded below. Git source
changes alone do not update WordPress or production.

## Staging verification — November 4, 2026

**Applied on staging38 at 07:35 UTC. Production is unchanged.**
[Preview Home](https://staging38.thaisolitude.com/) by scrolling;
[PR #71](https://github.com/alanmz-crypto/new-masseuse-site/pull/71).

- Local Chromium 154: 576 fixture assertions passed, including an unscrollable
  page with an empty bar. Pinned PHP syntax and shell/JavaScript syntax passed.
- [Isolated WordPress rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41792650114): v1 upgrade, idempotent apply, exact rollback to v1 and re-apply all passed; content gates and browser checks passed on all 12 routes. Chromium 153 tested native animation; pinned Firefox 155 tested the unsupported-browser fallback. Newer Firefox native animation and Safari were not directly tested.
- [Staging dry run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41791955223): exact v1 recognized, 66,639 original CSS bytes.
- [Apply, post-change checks and restore proof](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41793273857): all 12 public routes returned 200 with the sitewide CSS and license. All five post-change groups passed, including rendered/posture/code gates, structured-data parity, 20/20 pixel-identical reduced-motion baseline views and no PHP errors. The baseline screenshots intentionally hide the indicator; normal-motion behavior is checked separately.
- [Focused public staging browser check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41793411463): **560 assertions, zero failures** across all 12 pages, three widths (1280/390/320) and both themes. Native scroll position, dynamic disclosure heights, unchanged content/geometry, no added side-scroll, pointer behavior, preference exclusions and browser errors all passed.
- Fresh database export and before-snapshot verified encrypted offsite. Restored export imported successfully; 94 fingerprints on each side differ only in CSS post 29 and its expected revision rows, not pages, SEO, snippets or settings.

Actual database export: `staging38-before-reading-progress-sitewide-20261104-073522.sql`
(7,210,843 bytes). Exact rollback snapshot:
`reading-progress-sitewide-before-20261104-073535-361f0228.json`.

```sh
scripts/ci/request.sh run --target staging38 --script reading-progress-sitewide/push-reading-progress-sitewide.sh --args '--rollback $HOME/private-backups/reading-progress-sitewide-before-20261104-073535-361f0228.json'
```

This restores the four-page version. Its original installer and snapshot remain
available afterward. No live request or production enablement is prepared.

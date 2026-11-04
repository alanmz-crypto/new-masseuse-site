# Reading progress on long service pages

One interface improvement requested on 2026-11-04: a quiet, three-pixel position
indicator across the top of the viewport while reading Thai, Therapeutic, Deep
Tissue and Chair Massage. It fills as the visitor scrolls toward the bottom of
the page, and follows the new page height when a research panel opens.

The existing `cite.ts-source` markup identifies these four long pages. The CSS
does not rely on deployment-specific page IDs or the JavaScript jump links.
It uses the existing light/dark accent token, adds no layout space, cannot
intercept clicks, and adds no announced text or tab stop. It is absent in print,
forced-colors mode, reduced-motion mode, browsers without scroll timelines, and
logged-in views with the WordPress admin toolbar. Essential content remains
visible without this enhancement.

## Why this capability

CSS scroll-driven animations became Baseline Newly Available on October 27,
2026. They let the browser tie an animation directly to the root scroll position
without a JavaScript scroll listener or animation dependency. This site already
has page transitions, so this is an additional navigation cue for its unusually
long research pages rather than another page-entry animation.

- [Web Features Explorer: availability and browser support](https://web-platform-dx.github.io/web-features-explorer/features/scroll-driven-animations/)
- [MDN: animation-timeline](https://developer.mozilla.org/en-US/docs/Web/CSS/animation-timeline)
- [MDN: scroll()](https://developer.mozilla.org/en-US/docs/Web/CSS/animation-timeline/scroll)

The `animation-timeline` declaration follows `animation` because the shorthand
resets the timeline. The whole effect is inside `@supports` and the applicable
media conditions. No polyfill or hidden-content fallback is needed.

## WordPress integration

`install-reading-progress.php` appends `reading-progress.css` as one marked block
to Astra's Additional CSS. It preserves every original CSS byte, records a
private verified before-snapshot, verifies the write, and restores the original
on write failure. Repeat apply does nothing. Partial/duplicate/changed markers
fail. Rollback requires the exact original snapshot and exact applied CSS;
later CSS changes are refused rather than overwritten.

Only `http://localhost:8090` and staging38's exact HTTP/HTTPS origins are allowed
(staging currently stores an HTTP home URL, while visitors use HTTPS).
Production is not enabled. Nothing is installed as a plugin or remote theme
file, and page text, SEO fields, booking links and existing interactions are not
edited. Git source changes alone do not alter WordPress.

## Rehearse, review, then stage

Use the repository's Actions workflow, with unique request IDs:

```sh
scripts/ci/request.sh rehearse --source staging38 --installer reading-progress/install-reading-progress.php
scripts/ci/request.sh run --target staging38 --script reading-progress/push-reading-progress.sh
scripts/ci/request.sh run --target staging38 --script reading-progress/push-reading-progress.sh --args '--apply'
```

The push script takes a fresh database export and verifies an encrypted off-site
copy before either apply or rollback. The Actions write lane also runs the
standard post-change checks and restore proof. A bot challenge is an invalid
public check, never a pass. Review the focused browser checks on the actual
staging pages at desktop and phone widths, both themes, with research panels
opened and with reduced motion enabled.

Rollback uses the exact private snapshot printed by apply:

```sh
scripts/ci/request.sh run --target staging38 --script reading-progress/push-reading-progress.sh --args '--rollback $HOME/private-backups/reading-progress-before-TIMESTAMP-SUFFIX.json'
```

Later Additional CSS releases must be undone before this exact rollback. Live
enablement and the owner's live run remain a separate release step under
`CLAUDE.md`; this package cannot write to live.

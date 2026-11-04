# Complete page navigation

**In progress; not applied.** Ryan requested that in-page jump links cover the
important context, especially Services, and match the native section guides.

The inline row is the single source of destinations. It is rendered on the
server, so links and anchors work without JavaScript. On the five pages below,
the native popover copies the same labels, fragments and order. The older
four-link cap and independently added native Questions/Research links are removed.

| Page | Links in document order |
| --- | --- |
| Services | Chair massage; Corporate & group; Therapeutic massage; Deep tissue massage; Thai massage; Your session; What to expect |
| Thai Massage | What it is; Common reasons; What to expect; Questions; What Research Says |
| Therapeutic Massage | What it is; Common reasons; Who can benefit; Questions; What to expect; What Research Says |
| Deep Tissue Massage | What it is; Common reasons; What to expect; Questions; What Research Says |
| Chair Massage | What it is; Common reasons; Who can benefit; Questions; What to expect; What Research Says |

Fresh staging source confirms the Services heading is **What to Expect During a
Massage Session**. Its old target matcher is valid; omissions and the hard
four-link cap caused the coverage gap. Your Session is a separate service-context
heading. The screen-reader-only Massage services heading is not a visible
navigation destination.

Existing Home, About, Contact, Privacy and Accessibility navigation is retained.
Corporate and Work With Me keep their existing navigation/booking controls and
remain outside the native guide. No page copy, SEO, research content, images,
booking links or CSS changes are part of this update.

## Compatibility and rollback

The migration updates the existing `A11yPageJumpLinks` and `TSSectionGuide v1`
WPCode snippets together. It refuses unknown, duplicate or partially migrated
states. Its snapshot captures both complete prior records, all metadata/taxonomy
terms and unchanged CSS identity/bytes. Rollback restores both records including
their original modified timestamps, allowing the earlier guide snapshots to work
again after this paired migration has been undone.

The frozen original guide package remains under `../section-guide/`. Never run
its old installer to overwrite this newer runtime. Undo the paired migration
first, using this migration's exact snapshot from the same site.

Existing bookmarks are preserved, including `ts-therapeutic-massage` and
`ts-your-session` used by the Home heart links, and `ts-guide-questions` /
`ts-guide-research` used by the native guide. New target IDs are collision-safe.
Navigation order follows actual target positions. Inline and popover links use
native fragment navigation with focus moving to the heading when JavaScript is
available. No-JavaScript navigation remains native anchor scrolling.

## Verification record

- Fresh staging capture: request `ui-page-navigation-sources-20261104`,
  [run41823764392](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41823764392).
  WordPress7.1.3; inline snippet1232, guide1349; CSS69,937bytes.
- The original v3 guide is now live in snippet1338, from the owner-run
  [release](../../../docs/reviews/2026-11-04-section-guide-release.md). Its public
  browser follow-up passed **375/0**, with the broad old-reference limitations
  tracked there separately.
- Local server-rendering harness: **36/0**, including exact maps/order, preservation
  of existing markup, legacy IDs, collision suffixes, ambiguous/duplicate targets
  and quoted/bare-attribute edge cases. Actual WordPress rendering remains covered
  by the fresh rehearsal below.
- Paired-installer mock: **38/0**, including failure after the second write has
  mutated, final snapshot-save failure, exact record/date rollback, safe retry and
  refusal of unknown concurrent metadata. PHP8.5/Bash syntax and diff checks passed.
- Local Chromium browser fixture: **588 assertions, zero failures**; Services'
  390-pixel screenshot and 320-pixel/200% text screenshot were visually reviewed.
  The fixture models server markup using the actual runtime/CSS; it is not a
  substitute for the WordPress rehearsal of PHP output.
- Fresh rehearsal, staging preflight/apply and public checks pending.

Frozen payload SHA256:

- Inline: `bea04499abbe10f177282ce25a69f8ab8801312faa4b334d0be85f958e789b9f`.
- Native: `9c1aa136e0f61136d9f897fd44b241e6410b6cf6d2190c1070e5263a72b0f258`.
- Prior inline file: `1034cd2011adb1991392f26cf4b6d2fe04ef8916af9f7ce045c1023ecd4224c4`.
- Prior native file and unchanged CSS are pinned to the original guide package.

No production mutation is part of the staging-only installer. A later exact-live
enablement requires its own fresh rehearsal/preflight and owner-run request.

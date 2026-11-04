# Complete page navigation

**LIVE since 13:43:20 UTC on November 4, 2026.** Ryan merged
[release PR #78](https://github.com/alanmz-crypto/new-masseuse-site/pull/78) at
13:41:22 UTC into `5a451e6b6261c69636ae38a2a89edd397801d815`, then personally ran
[41840605814](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41840605814).
Request `live-page-navigation-20261104-1` is completed; **never reuse it**.
All twelve origin checks passed. Verified encrypted export/snapshot, exact live
rollback and restore proof are in the
[live release record](../../../docs/reviews/2026-11-04-page-navigation-release.md).
The broad live suite passed copy/posture/pages/PHP checks but retains the known
historical staging36 schema/pixel differences; it is not an aggregate pass.
Focused public request `ui-page-navigation-live-browser-20261104` finished
**INVALID202, zero assertions** at 17:00:01 UTC: the first request received the
hosting challenge, then the check stopped. Verification remains pending access;
no immediate retry is queued. This does not undo the completed deployment or
twelve passing origin checks.

The source/staging change merged in
[PR #77](https://github.com/alanmz-crypto/new-masseuse-site/pull/77) at 13:20:44 UTC.
Staging applied at 12:40:03 UTC and passed 572/0 public browser checks. The fresh
live-copy rehearsal passed 572/0 each in Chromium/Firefox; live preflight passed
before the owner launch. Ryan requested that in-page links cover the important
context, especially Services, and match the native section guides.

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

This exact staging rollback restores both prior snippet records and leaves CSS
unchanged:

```sh
scripts/ci/request.sh run --target staging38 --script page-navigation/push-page-navigation.sh --args '--rollback $HOME/private-backups/page-navigation-before-20261104-124003-a437350c.json'
```

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
- Local server-rendering harness: **39/0**, including exact maps/order, preservation
  of existing markup, legacy IDs, collision suffixes, ambiguous/duplicate targets
  and quoted/bare-attribute edge cases. Actual WordPress rendering remains covered
  by the fresh rehearsal below.
- Paired-installer mock: **38/0**, including failure after the second write has
  mutated, final snapshot-save failure, exact record/date rollback, safe retry and
  refusal of unknown concurrent metadata. PHP8.5/Bash syntax and diff checks passed.
- Local Chromium browser fixture: **593 assertions, zero failures**; Services'
  390-pixel screenshot and 320-pixel/200% text screenshot were visually reviewed.
  The fixture models server markup using the actual runtime/CSS; it is not a
  substitute for the WordPress rehearsal of PHP output.
- First fresh WordPress rehearsal (`ui-page-navigation-rehearse-20261104`,
  [run41826610536](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41826610536))
  passed paired apply/idempotence/exact rollback, unchanged-page fingerprints and
  regulated-copy gates, but failed browser verification. No staging write followed.
  Astra prevents native inline clicks and updates the fragment itself; the focus
  helper now follows that confirmed destination. Contact keeps the legacy first
  matching group despite WordPress's nested wrappers. A no-JavaScript test now
  uses native mouse input after checking stable, visible, unobscured geometry,
  avoiding an automation click-retry stall. The fixture includes the theme-event
  regression and refusal to move focus when the destination hash differs.
- Corrected fresh staging38-copy rehearsal, request
  `ui-page-navigation-rehearse-2-20261104`,
  [run41829646501](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41829646501),
  source `d7f73cee435874b5eab7b9225c7ce70933dcd8f0`, finished12:38:23 UTC:
  **PASS**, including **572/0 Chromium and 572/0 Firefox**; paired apply,
  idempotence, exact full-record/date rollback, reapply, page status, PHP and both
  rendered-copy gates. Only snippets1232/1349 and WPCode's cache changed.
- Corrected staging preflight, request `ui-page-navigation-dry-2-20261104`,
  [run 41829739285](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41829739285),
  source `577e2ef9ea20d53bad5243aa62cc1e183cd63262`, finished 12:39:02 UTC:
  **PASS**, ready with no site change.
- Staging apply, request `ui-page-navigation-apply-20261104`,
  [run 41831164491](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831164491),
  source `6d0d9f1d06715dbdc058f3fb6e959dd296dd5939`: **applied at 12:40:03 UTC**.
  All twelve origin route checks returned HTTP 200 and verified the licence,
  unchanged CSS markers and scoped navigation. Fresh export
  `staging38-before-page-navigation-20261104-123950.sql` is 7,371,584 bytes
  (SHA256 prefix `c04e8ecde7c4`); exact rollback snapshot
  `page-navigation-before-20261104-124003-a437350c.json` has SHA256 prefix
  `1ddb456421a9`. Both were copied, hash-verified and read back identically from
  encrypted offsite storage. Restore import exited 0; restored and current copies
  each had 95 fingerprints. Only snippets 1232/1349 and WPCode's
  `wpcode_snippets` cache option differed.
- Automatic broad public checks were **INVALID: HTTP 202 hosting challenge,
  zero checks**. The once-only follow-up, request
  `ui-page-navigation-postcheck-20261104`,
  [run 41831845838](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831845838),
  source `3dcdf3309568475686859427cf54305c99a178c4`, finished 12:49:20 UTC,
  completed all five groups. Code/rendered-copy/posture, pages/sitemap/robots,
  structured data (27/27, zero parse errors) and PHP checks passed. Overall
  **FAIL** is the zero-tolerance pixel comparison: 12/20 identical, eight
  differences on Services, Thai, Therapeutic and Deep Tissue desktop/mobile
  views. Desktop differences are confined to the jump row; mobile rows either
  wrap by one additional line (27–28 pixels) or change within the row. These
  match the intended new links against the unchanged live navigation. There
  were no missing captures or screenshot problems. Chair is outside that broad
  screenshot set and is covered by the focused checks. No further retry queued;
  do not describe the broad suite as an aggregate pass.
- Focused public staging verification, request `ui-page-navigation-browser-20261104`,
  [run 41831499758](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831499758),
  source `4e6920a62a1cd33e52e77aadd54e44ed2866baa8`, finished 12:45:59 UTC:
  **572 Chromium assertions, zero failures**. Complete maps, matching controls,
  every fragment/focus destination, retained links, keyboard/history behavior,
  mobile/light/dark layouts, preferences, fallback and all 29 no-JavaScript
  destinations passed. The actual 390-pixel Services inline row and native panel
  screenshots were visually reviewed: seven links, wrapping without clipping,
  identical order. Touch is emulated; Safari and physical devices are untested.

Frozen payload SHA256:

- Inline: `06353fa604ec35a389cf79a3e3904877e105a7a07d66f7eca752fe24cecede83`.
- Native: `d5646a3339d7c9611d255bf995a321d44e067b7378d4bac5d0768c5aa607bd19`.
- Prior inline file: `1034cd2011adb1991392f26cf4b6d2fe04ef8916af9f7ce045c1023ecd4224c4`.
- Prior native file and unchanged CSS are pinned to the original guide package.

Live preparation and owner launch are complete. Focused public live verification
is pending hosting access after INVALID202; no further apply or immediate retry
is queued. A live rollback
must use the actual live snapshot recorded above, never the staging snapshot.

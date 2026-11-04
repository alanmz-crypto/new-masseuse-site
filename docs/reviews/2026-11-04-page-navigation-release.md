# Complete page navigation: live release

**Prepared for owner launch; not applied to live.** Ryan requested “Publish
live” after merging [source/staging PR #77](https://github.com/alanmz-crypto/new-masseuse-site/pull/77)
at **13:20:44 UTC on November 4, 2026**, main commit
`aea59a5b847f0a7b6a743bf6f87426b9762e2007`.
Request `live-page-navigation-20261104-1` is **prepared, not run** in
[`ops/live-request.json`](../../ops/live-request.json), for the paired navigation
push with `--apply` and automatic checks. Fresh live-copy rehearsal and read-only
live preflight have passed; no live application is recorded.
[Release PR #78](https://github.com/alanmz-crypto/new-masseuse-site/pull/78) carries the release; check its final Gates status before merging. The request was
prepared at source `e4a404b72d3424e2e5721f8e9d156709c3a6e3be`.

Ryan reported that the apparent loading issue on his device was resolved with
his VPN. The availability diagnostic was cancelled before execution. No website
outage, site repair or extra availability operation is claimed.

## Exact release scope

The existing inline navigation becomes the single server-rendered list copied
by the native “On this page” guide. Services gains the native guide and all seven
of its visible destinations, in page order:

1. Chair massage
2. Corporate & group
3. Therapeutic massage
4. Deep tissue massage
5. Thai massage
6. Your session
7. What to expect

Thai, Therapeutic, Deep Tissue and Chair Massage gain **Questions** and **What
Research Says** in their inline navigation. Their native guides copy the same
labels, fragments and document order. Thai/Deep Tissue have five links;
Therapeutic/Chair have six. Existing Home, About, Contact, Privacy and
Accessibility navigation is preserved, including the Privacy accordion helper.
Corporate and Work With Me remain outside the native guide.

The paired migration updates only the existing `A11yPageJumpLinks` and
`TSSectionGuide v1` snippets and their derived WPCode cache. Existing bookmark IDs
are retained, including the Services heart-link targets and Questions/Research
fragments. Inline focus follows the actual fragment selected by Astra; the
native guide copies the server list without adding another set of destinations.
Links and target IDs are present without JavaScript. Page copy, research content,
SEO, images, booking links and CSS do not change.

Frozen payload SHA256:

- Inline: `06353fa604ec35a389cf79a3e3904877e105a7a07d66f7eca752fe24cecede83`.
- Native: `d5646a3339d7c9611d255bf995a321d44e067b7378d4bac5d0768c5aa607bd19`.

## Staging evidence

The [package record](../../scripts/work-with-me-employer/page-navigation/README.md)
contains the full source, browser and rollback evidence.

- Corrected [staging-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41829646501)
  passed paired apply, repeat no-op, exact record/date rollback and reapply,
  unchanged-page/content gates and **572/0 each in Chromium and Firefox**.
- Corrected [staging preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41829739285)
  passed before the write.
- [Staging apply](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831164491),
  source `6d0d9f1d06715dbdc058f3fb6e959dd296dd5939`, completed at **12:40:03 UTC**.
  All twelve origin route checks passed. Export
  `staging38-before-page-navigation-20261104-123950.sql` (7,371,584 bytes,
  SHA256 prefix `c04e8ecde7c4`) and snapshot
  `page-navigation-before-20261104-124003-a437350c.json` (SHA256 prefix
  `1ddb456421a9`) were verified and read back identically from encrypted offsite
  storage. Restore import exited 0; restored/current copies each had 95
  fingerprints, with only snippets 1232/1349 and WPCode's cache differing.
- [Focused public staging check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831499758)
  passed **572/0**, including all 29 inline/native destinations, no-JavaScript
  links, focus/history/keyboard behavior, mobile layouts, themes and preferences.
  Actual mobile Services inline and native controls were visually reviewed.
- The automatic broad check was initially **INVALID202, zero checks**. Its
  [once-only follow-up](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41831845838)
  passed copy/posture, pages/sitemap/robots, structured data **27/27** and PHP.
  Overall **FAIL** was the zero-tolerance pixel comparison: 12/20 views identical,
  eight expected navigation-row differences, no missing/problem captures. This
  is not an aggregate pass. No further staging retry is queued.

Touch coverage is emulated; Safari and physical devices are untested.

## Fresh live preparation evidence

- [Live-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41838099630),
  request `ui-page-navigation-live-rehearse-20261104`, source
  `a4b79d6fa38134d300aa5d2e60be54aecb18005b`, finished **13:34:35 UTC**: **PASS**.
  It copied WordPress **7.1.4** and 268 MB of files read-only from live, whose
  stored origin remains `http://thaisolitude.com`, into isolated localhost.
  Paired apply, repeat no-op, exact full-record/date rollback and reapply passed.
  All **69,937 CSS bytes** stayed unchanged; baseline fingerprints matched after
  rollback with the derived WPCode cache set aside. Only snippets **1232/1338**
  and WPCode's cache changed in the tracked fingerprints after apply. Page copy
  and SEO fingerprints did not drift. Page statuses, regulated-copy gates and
  PHP checks passed.
- The same rehearsal passed **572 browser assertions, zero failures each** in
  Chromium **153.0.8010.12** and Firefox **155.0**, including all scoped fragment,
  focus and no-JavaScript destinations. These checks used the isolated live copy;
  they are not post-deployment public results.
- [Read-only live preflight](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41838471144),
  request `ui-page-navigation-live-dry-20261104`, source
  `d657728d65e229d1fac0fecc912d0f7d341119fe`, finished **13:35:04 UTC**: **PASS**.
  Exact stored origin `http://thaisolitude.com`, state `ready`, CSS **69,937
  bytes**; no WordPress data changed.
- Local PHP/Bash syntax checks, **81 paired-installer mock checks**, **14 host
  guard cases** and **39 rendered-map checks** passed. The origin verifier now
  requires the current runtime markers and exact complete inline list on each
  scoped page, using its existing responses without additional page requests.

## Preparation and owner launch

- Fresh live-copy rehearsal: **PASS**, including Chromium/Firefox **572/0 each**.
- Read-only live preflight: **PASS**, ready with no write.
- [Release PR #78](https://github.com/alanmz-crypto/new-masseuse-site/pull/78):
  contains the release and its final Gates check; merge only with Gates green.
- Request `live-page-navigation-20261104-1`: **prepared, not run**.
- Actual live export, snapshot, apply and public verification: **not available**.

[CLAUDE.md](../../CLAUDE.md) states: **“Live: never start it.”** After successful
preparation, Ryan merges the release PR, then starts **Actions → Site ops (LIVE)
→ Run workflow on main**, confirming the exact prepared request ID. No agent
merges main or dispatches LIVE. Record the actual run and its evidence before
changing this document to applied.

## Exact live rollback

The future live apply must create and verify its own database export and paired
before-snapshot. No live snapshot path exists yet. A live rollback must use that
actual live snapshot; **never substitute the staging snapshot listed above**.
It restores both complete prior snippet records, metadata, taxonomy and original
modified timestamps, with CSS unchanged, and refuses later drift. Undo this
paired migration before attempting an older section-guide rollback.

Rollback is a separate owner-run LIVE request with a fresh verified backup.
Record its exact command only after the actual live snapshot path is known.

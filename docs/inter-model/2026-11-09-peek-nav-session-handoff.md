# Session handoff: the Peek navigation (for the next Claude Code session)

**Input and keyboard focus follow-up in progress; no website write in this phase.**
The [new phase record](../reviews/2026-11-09-peek-input-focus.md) tracks the remaining summary touch-focus and
Rail practice keyboard issues. The deployed Kiro core is now frozen as
`peek-nav-shared-v6.php`; `core-kiro-fixes` pins v5→v6. Source `peek-nav.php`
contains the pending `core-input-focus` candidate, starting from v6, not the
deployed payload. The existing actual Kiro snapshot retains its meaning.

The sequence is the Rail practice page **#1348 first**, updated in place for
Enter/Close/Stay/Escape behavior, then the shared core's summary touch-focus
correction. Never delete/recreate the practice page through the old installer;
the new `rail-input` route will preserve complete page state in a private
snapshot. Read-only inspection **42709406091** passed **22:10:57 UTC**:
published #1348 is 36,892 bytes, SHA256
`0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748`, exactly the
frozen v1 source wrapper. It did not inspect metadata/terms; the new update must
check and preserve those. Local Chromium/Firefox probes are fixture evidence,
not staging verification. WebKit could not run because required libraries are
missing; no Safari/device result is claimed. Offline gates, fresh actual-copy
rehearsals, wording checks and exact rollback proof remain required before
writes. No new staging recovery snapshot exists yet. Main/live stay untouched.

**Current deployed core: Kiro fixes, frozen v6; public and stored checks passed.**
The [fix and verification record](../reviews/2026-11-09-peek-kiro-fixes.md) tracks
corrected disclosure touch/Enter behavior, larger short-screen reading areas,
FAQ/text clearance, wrapped title rows, shorter single-entry rails, distinct
preview edges and an opaque strip. All-page rails and four approved data sets
remain; no icon scrolling or broad jump-engine refactor was added.

Apply **42697003089**, source `8f4a3fb415b73cfe8c81071419c6da428f5b032a`, wrote #1380
at **20:52:52 UTC** and finished **20:54:46 UTC, PASS**. Current deployed code is **102,649
bytes**, SHA256 `dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1`.
Gates passed **31/0 + 1 rendered-only skip**. Fresh-copy rehearsal **42693471206**
passed **14,249/0 across 14 complete suites**, including native touch 387/0 and
Thai gestures 491/0. Twenty-two selected images and the 133-capture manifest are
retained; twelve were freshly viewed and ten match earlier reviewed captures.
All 19 published origin routes/scope/plain rows passed. Short-screen cards can
cover the strip to preserve reading space, while staying below the visible
header; that tradeoff was explained before implementation.

Fresh export `staging39-before-peek-nav-20261109-205236.sql` and actual snapshot
`peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json` were verified
encrypted off-site; export verification preceded the write. Restore import 0,
104/104 fingerprints differ only on #1380/WPCode cache. No fallback-only interval.
Automatic post-change checks were **INVALID HTTP202** at **20:54:27 UTC**, with
zero checks. A single later retry **42698232542** finished **21:03:22 UTC**:
both edge requests/all 12 HTTP checks returned 200; code/rendered wording/
posture/PHP passed and schema passed 27/27, zero parse errors. Overall **FAIL**
is 20/20 live pixel dimension differences, zero missing captures, not an
aggregate pass. All 20 deployed full-page captures were independently viewed,
with six v5 comparisons and verified hashes, without a new persistent layout/
image blocker. Existing transient labels and Contact blur/whitespace remain;
these closed-state views alone do not prove focused interactions.

Focused public **42699275642** finished **21:19:05 UTC, PASS**: **12,237/0 across
13 complete unique suites**, with no INVALID. Disclosure checks passed Chromium
66/0 and Firefox 41/0, layout 158/0 and 134/0, native touch 387/0; retained title,
page, clearance and matched-capture suites completed. Chair's native outer-card
swipe moved 0→21 px at scale 1/390×180/zero offset, with page/summary unchanged.

Independent audit verified all 33 streams, matching 12,237 literal/numeric
PASS counts, 16 empty stderr logs and 16 zero exits. The full inventory contains
123 decoded JPEGs. Fifteen of 24 selected public captures were freshly viewed;
nine exactly match earlier reviewed rehearsal captures. No new visible blocker
was found. Transient labels, approximate 150% root-text brand wrapping and one
closed Therapeutic tablet row's ellipsis remain documented in the fix record.

Stored inspection **42697818891** passed **20:57:33 UTC**: published #1380 matches
the exact current 102,649-byte/hash payload and all four published data records
retain their original identities, titles, bytes and full hashes.
Do not present the passing throwaway-copy suites as deployed public evidence.

Recovery requires a fresh verified backup, complete current-state checks and
the matching package: first use explicit
`core-kiro-fixes --rollback $HOME/private-backups/peek-template-core-kiro-fixes-before-20261109-205252-9078fe24.json`
to restore frozen v5. Only then may the older title-rail snapshot below restore
v4. Frozen v5/v6 and pinned v4→v5→v6 now exist. The pending `core-input-focus`
route has not been applied and does not alter current staging recovery. After
its future apply, first reverse its own actual snapshot to v6 before this older
chain. Practice-page recovery uses its separate full-state `rail-input` route,
never the old delete/recreate route. Thai touch-focus-to-BODY and Rail-practice Firefox Enter are not
claimed fixed. Safari, physical devices, actual assistive technology and OS
text scaling remain unverified; rapid-switch delay is not claimed fixed.
Main/live remain unchanged.

**Historical assessment stage, before the fixes above:** The [Kiro report and Codex verification](2026-11-09-peek-nav-kiro-assessment.md)
record independently reproduced touch overshoot and keyboard double-toggle on
Privacy. Existing passing checks missed the relevant initial disclosure state.
Further layout verification stopped on HTTP202; do not claim the reported
landscape and overlap findings were independently reproduced by Codex. This
review changed records only. Preserve all-page rails; the subsequent v5 freeze
and fix work are tracked above.

**Previous baseline: title-only Peek rails, frozen shared v5; public browser and
stored-code checks passed.** The [title-rail record](../reviews/2026-11-09-peek-title-rails.md)
tracks the actual deployment and earlier failed attempts. Shared rails use
existing jump-row labels/targets, otherwise visible H2 headings or one page-title
entry, without new summaries, IDs or hashes. Rail/Index practice rails are kept;
Progress A–D uses the shared rail, while E hides it and releases its rail padding
for the demo. The four approved data sets and settled gestures remain unchanged.

Apply **42666333809**, source `267c93e94f2b81ae63843be8f2e806805a061bd3`, wrote #1380
at **17:37:37 UTC** and finished **17:41:06 UTC, PASS**. The payload has **97,130 code
bytes**, SHA256 `c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.
Actions Gates passed **29 PASS / 1 SKIP / 0 FAIL / 0 INVALID**; fresh-copy rehearsal
**42662818962** passed **13,836/0** across ten suites, with 40 actual title-rail
captures compared/reviewed. Origin scope passed on all 19 published pages;
rendered wording/posture/pages/PHP and schema 27/27 passed. The broad live pixel
comparison had 20/20 differences, not an aggregate pass. All 20 actual deployed
full-page captures were reviewed without a new persistent layout blocker.
Public run **42667176429** finished **17:49:09 UTC, PASS**: **11,829/0** across nine
unique suites; both title matrices covered 21 actual routes in Chromium/Firefox.
Stored inspection **42667209210** passed **17:49:42 UTC**: published #1380 exactly
matches the bytes/hash above and #1381–1384 retain their original bytes/hashes.
That previous record tracks the full suite counts and final capture/log review.
Safari, physical devices and actual assistive technology remain unverified.
Existing Thai touch-focus loss and the old Rail practice page's Firefox Enter
issue remain open; practice pointer preview/Go passed. The reported rapid-switch
or loading delay was not reproduced or claimed fixed.

Fresh export `staging39-before-peek-nav-20261109-173723.sql` and actual snapshot
`peek-template-core-title-rail-before-20261109-173737-cae68f35.json` were verified
encrypted off-site. Restore import returned 0; 104/104 fingerprints differ only
on #1380/WPCode cache. No fallback-only interval occurred. After a fresh verified
backup and full current-state checks, and only after reversing the current
Kiro-fixes transition to v5 above, use the matching package and explicit
`core-title-rail --rollback $HOME/private-backups/peek-template-core-title-rail-before-20261109-173737-cae68f35.json`
arguments to restore v4. This route now pins frozen v4→v5; the older
`core-title-strip` route pins frozen v3→v4. Page-only data removal leaves
a title-only rail and strip, with the original jump row available without JavaScript.

**Previous deployment: all-page title strip and summary space, frozen shared v4.** The all-page
read-only title strip and globally enlarged scrolling summaries were written to
#1380 at **10:24:52 UTC**; apply run **42600617720** finished **10:28:23 UTC, PASS**.
Short, no-heading, unlisted and practice pages are included. Search uses a fixed
page title; other pages use existing row labels, visible headings or their page
identity. Title-only pages receive no invented destinations, summaries or rail.
The four approved data records and settled rail/index gestures remain intact.

The [earlier deployment/recovery record](../reviews/2026-11-09-peek-title-strip-all-pages.md)
holds the complete fresh-copy PASS, 58 reviewed/comparison captures, 19 origin
scope checks and actual backup identities. The [summary-space record](../reviews/2026-11-09-peek-summary-space.md)
explains larger summaries, earlier folding of distant rows and the constrained
outer-card fallback. The reported initial loading delay was not reproduced.
Public run **42601203134** finished **10:30:14 UTC, INVALID HTTP202**: zero
assertions, actual routes or captures, with the quick subset skipped. That earlier
attempt provided no public browser verification; no immediate retry was queued.
Stored inspection **42601221631** passed **10:30:40 UTC**: exact #1380 SHA256
`4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5`,
**86,567 bytes**, and all four unchanged data identities/hashes.

Fresh export `staging39-before-peek-nav-20261109-102438.sql` and actual snapshot
`peek-template-core-title-strip-before-20261109-102452-90630a29.json` were verified
encrypted off-site. Restore import returned 0, with only #1380/cache differences.
There was no fallback-only interval. Recovery requires a fresh verified backup,
current full-state checks and the matching package: after restoring v5 through
the new Kiro-fixes snapshot, then v4 through the title-rail snapshot above, use explicit
`core-title-strip --rollback $HOME/private-backups/peek-template-core-title-strip-before-20261109-102452-90630a29.json`
arguments to restore frozen v3 before any older transition. The v4 freeze and
v3→v4 pin are complete; new versions use separate exact transitions.
Services/short-page title rails now follow the deployed scope above; new summary
wording remains subject to approval.
Safari, physical devices and actual assistive technology remain open. Main/live
remain untouched.

**Previous deployed baseline: short open summaries (frozen shared v3).** The
[short-summary touch record](../reviews/2026-11-09-peek-short-summary-touch.md)
tracks the exact `core-summary-touch` transition from frozen shared v2, written
to #1380 at 07:59:57 UTC; its apply job finished 08:04:07 UTC. The correction reserves short-summary swipes
while preserving long-summary and constrained-card scrolling. Chromium did not
reproduce Ryan's reported leak; physical-device confirmation remains open.
Fresh-copy/wording gates and preflight passed. Actual snapshot
`peek-template-core-summary-touch-before-20261109-075957-5ef2bd29.json` and its
fresh export were verified encrypted off-site; restore import returned 0 with
only #1380/cache differences. Four approved data records remain unchanged and
there was no fallback-only interval. Public verification passed at 08:08:52 UTC:
clearance **3340/0** in Chromium/Firefox, native touch **266/0**, pages **172/0**,
matched-capture subset **342/0**. Stored inspection passed at 08:09:31 UTC:
exact #1380 hash/71,826 bytes and all four unchanged data hashes. The linked
record holds the public evidence, detailed visual review and recovery.

**Continuation update, 2026-11-09 (Codex):** all four service pages are deployed on
staging39: shared core **#1380**, Thai data **#1381**, Therapeutic **#1382**, Deep
Tissue **#1383**, Chair **#1384**. Ryan approved the exact three new wording sets.
Their public checks passed at the respective rollout stages: **168/0**, **190/0**,
**232/0**. Read the [current rollout evidence, actual page snapshots and final aggregate status](../reviews/2026-11-09-peek-service-rollout.md),
[original core migration and its recovery scope](../reviews/2026-11-09-peek-template.md)
and [shared-template instructions](../../scripts/work-with-me-employer/link-ideas/README-peek.md)
before any operation. Core rehearsals need `--with-uploads`; do not weaken geometry
assertions to accommodate missing layout assets. The earlier **74/0 manifest +
485/0 Thai regression** and six identical before/after images describe the
original Thai-only migration, not final four-page verification.

**Earlier clearance/touch correction deployed; apply job finished 07:15:10 UTC:** the guarded
[core upgrade](../reviews/2026-11-09-peek-clearance.md) updated #1380 in place after
passing fresh-copy rehearsal and visual review. It measures the actual visible
strip/header, preserves kept-summary reading state across resize, and gives closed
topic controls ownership of touch drags. All four data records remain unchanged;
there was no fallback-only interval. Fresh export and actual snapshot
`peek-template-core-upgrade-before-20261109-071122-958bff3b.json` were verified
encrypted off-site; restore import returned 0 with only #1380/cache differences.
Stored inspection passed at 07:17:32 UTC: exact #1380 hash/71,321 bytes and four
unchanged data hashes. Public browser verification was **INVALID HTTP202** at
07:17:03 UTC on the initial Thai request: zero assertions/captures, no retry queued.
Follow the correction record for evidence and exact recovery. Chromium native
touch on the old core did not reproduce Ryan's reported page scrolling, so no
physical-device resolution is claimed. New summary wording, Safari,
physical devices and actual assistive technology remain open. Main/live are untouched.

The original `core` migration targets frozen `peek-nav-shared-v1.php`;
`core-upgrade` is now pinned to v1→frozen `peek-nav-shared-v2.php`, preserving its
07:11:22 snapshot. `core-summary-touch` now pins v2→frozen `peek-nav-shared-v3.php`,
preserving the 07:59:57 snapshot. Reverse the current Kiro-fixes update to v5
first, then title-rail to v4, then title-strip to v3, then summary-touch using its
own actual snapshot and explicit `core-summary-touch --rollback FILE` arguments.
Only then may `core-upgrade --rollback FILE` restore v1, followed by the original
`core --rollback FILE` recovery to legacy. Never substitute an older snapshot.

The original handoff below, including its Thai-only state and swap/rollback
commands, is historical. Do not rerun the completed migrations or use core
recovery to reverse only one page; use the current rollout record instead.

Written 2026-11-09 by the Claude Code cloud session that built the Peek navigation over 2026-11-08/09, because its context is running out. Ryan asked
for this handoff to go to a higher-tier model, which **continues the work and keeps assessing it**. Read this first, then the files in section 6.

## 1. What this is

The Peek is an in-page navigation for thaisolitude.com (WordPress + Astra, SiteGround). Ryan has decided it becomes **the template for every page**. It
has three parts:
- a read-only title strip at the top;
- a side rail with stacked summary boxes;
- an index icon above the rail.

It runs **only on staging39**, on `/thai-massage/`. Live is untouched; live is Ryan's to start (CLAUDE.md, AGENTS.md).

- **Code:** `scripts/work-with-me-employer/link-ideas/peek-nav.php`, one WPCode snippet with CSS in `wp_head` and JavaScript in `wp_footer`, gated by
  `is_page( 'thai-massage' )`.
- **On staging39:** snippet **#1380**. Undo: `scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args '--rollback $HOME/private-backups/peek-nav-before-20261109-010037-0c493281.json'`.
- **Branch:** `claude/wizardly-bell-xu0rta`. Nothing is on `main`; never push there.
- **Browser check:** `link-ideas/check-peek-nav.mjs`, which gives **485 passed, 0 failed** on the mock. #1380's staging39 run is `ui-peek-nav-s39-41-1`
  (read its report on `ops-log`).
- **Saved known-good copy, if Ryan wants to go back:** `link-ideas/saved/20261108-1369/` (README inside), plus a staging39 DB export
  `staging39-manual-20261108-182958.sql`, verified off-site.

## 2. The design now (Ryan's settled choices; change details, not these)

1. **Top strip** (`.ts-pk-top`): read-only, `aria-hidden`, sticky under the pinned header. The section titles slide by, the current one centred, the
   neighbours faded (opacity .85 for contrast).
2. **Side rail:** one piece per section, sized by section length.
   - Slide along it to open a **stack** while the page stays still: the open box beside the finger, closed boxes ("number + heading") above and below.
   - Lift to keep it; tap the open box to jump; tap a closed box to open it.
   - `fit1`-`fit5` shrink the stack to fit the screen.
3. **The open box:** number and minutes, the name, a **summary list**, "Tap to go there", and a left/right switch. There is no "You are in…".
   - **The summary** is a list of short ideas per section (`GISTS`, keyed **by page, then heading id**). Ryan's rules:
     - each distinct idea is about three words ("subject, verb, object");
     - overlapping ideas are merged, or the lesser one is dropped;
     - no minutiae and nothing said twice, in a box or across boxes;
     - a list that holds about 60% or less of its section ends with "etc.";
     - the research box may be much longer.
   - The lists are new wording on a regulated page: check with `client-pages/wording_tiers.py` and keep the page's hedging (Kiro's table). Ryan approved
     Kiro's safer phrases.
   - Sections without a list fall back to their own opening paragraphs, whole (`summarize()`). Never split sentences: that broke at "1.5", "U.S." and quotes.
4. **Index icon:**
   - **press** and the names fan out (closed-box form, sized to the rail spacing);
   - **slide onto a name and lift**: its box opens;
   - **slide straight down onto the rail** (24px in, under 12% sideways): the rail preview;
   - **hold 2 s, or a quick tap**: the names stay open; tap one for its box, tap elsewhere to close.

   There is **no page scroll from the icon**: Ryan tried it and said "Get rid of the scroll idea."
5. **Also part of it:** a pinned site header; a left-hand option; a first-time hint; `clearRail()`, which pads **the text blocks, never the column** (see
   the lessons); `#links` that land below the header and strip; focus margin on the content's focusables.
6. **Sitewide on staging39 only:** the 3px reading-progress bar and Astra's scroll-to-top are removed (`install-sitewide-cleanup.php`). Both still exist on live.

## 3. How work is done here (cloud session)

- No SSH. Server work goes through GitHub Actions: `scripts/ci/request.sh` (see `ops/README.md`). The scratchpad helper `try.sh` retried runs when the
  report said "nothing was run". Recreate it if needed:
  `for n in 1 2 3 4; do out=$(scripts/ci/request.sh "$@" --id "$pre-$n" 2>&1); …`
- **A snippet update** is: roll back the current snippet with its snapshot; `rehearse --installer link-ideas/install-peek-nav.php --source staging39`;
  `run --target staging39 --script link-ideas/push-peek-nav.sh --args "--apply"` (it prints the new snapshot name, which you record); then
  `run … --script link-ideas/check-peek-nav.mjs --args "https://staging39.thaisolitude.com"`.
  - It takes about 6 minutes. During it the page shows the plain row, so tell Ryan not to judge a reload until the apply line appears.
  - Run it in the background.
- **Mock:** `cd link-ideas/mock; python3 build-mock.py; python3 -m http.server 8091 -d site`. Then run the check with its base URL replaced by
  `http://localhost:8091` (sed) and `TS_PLAYWRIGHT_ROOT=$(npm root -g)/playwright/package.json`. A full run takes about 5 minutes.
- **Read-only tools:**
  - `link-ideas/dump-sections.mjs <base> /slug/`: the rendered text per section, including FAQ answers that another snippet adds;
  - `link-ideas/probe-overflow.mjs <base>`: the page width and sideways scroll per phone width and rail side.

  Run them through `request.sh run --target staging39 --script link-ideas/<tool>.mjs --args "https://staging39.thaisolitude.com …"`.
- **Commits:** always `git commit -F <file>` (quotes in `-m` broke). End messages with the attribution lines. Push to `claude/wizardly-bell-xu0rta`. A
  stop hook insists on a clean tree.
- **Ryan:**
  - plain language;
  - ends each finished phase with "I finished / Next step / Next lane / See my work / Arc: none (ad-hoc)" and a TL;DR;
  - he is often on his phone;
  - he likes to be told when something could be simpler ("aha" moments), before the build;
  - live only on his go.
- **Rules for Kiro** (Kiro reviews on its own branch, and Ryan pastes its report here): never `ops/request.json`, `request.sh` or `main`.

## 4. Open work, in order

1. **#1380 on staging39 is confirmed:** `ui-peek-nav-s39-41-1` gave **485 passed, 0 failed**.
2. **The template structure** (Kiro's proposal, template-review handoff section 3, which Ryan has not objected to):
   - one shared core snippet;
   - one small data snippet per page (`ts_pk_pages` filter, current page's data printed as JSON);
   - `peek-data/<slug>.json` with `[text, anchor]` items;
   - `lint-gists.py`, run by `check-all.sh`;
   - an installer that takes `core|page <slug>`;
   - a check driven by `peek-pages.json`.

   Today `install-peek-nav.php:44`, `push-peek-nav.sh` and `check-peek-nav.mjs` assume only Thai has the Peek.
3. **Then the pages Kiro rated nearly ready:** Therapeutic, Deep Tissue, Chair.
   - Each needs its own lists (from `dump-sections.mjs`, Ryan's rules, `wording_tiers`, Kiro's hedging) and **Ryan's approval of the wording**.
   - Services (7 short sections) is Ryan's decision.
   - Home, About, Contact and the legal pages are too short; Kiro's rule is 4 or more sections and about 1,500 words or more.
4. **Kiro's enhancements still open:** cap the rail piece lengths (a long research section squeezes the others); a "draft" flag for unapproved lists;
   per-shape mocks.
5. **Decisions for Ryan:**
   - desktop behaviour;
   - a slimmer rail;
   - unpinning the header on short screens;
   - the strip disagreeing with the rail late in a section;
   - the pinned header and the sitewide cleanup as their own sitewide changes;
   - live.

   Safari and real devices have never been tested by anyone: Ryan's iPhone SE and a newer iPhone are the only devices that have seen it.

## 5. Lessons that cost time (do not repeat)

- **Never pad a column that holds full-width blocks.** They are placed from its middle and shift by half the padding: text gets cut at the left edge, or the
  page becomes wider than the screen.
- **The clearance rule must exclude the navigation's own elements** (`.ts-pk`, `.ts-jump`): its `li`s matched and pushed the fan names in.
- **Page-wide `scroll-padding-top` with a pinned header** scrolls the page to the top when anything in the header gets focus. Put `scroll-margin-top` on
  the content's focusables instead.
- **A scrub from a short rail fights the phone's own scrolling** (an absolute mapping leaps, easing lags). Ryan dropped it.
- `max-height` includes the border; `scrollHeight` does not (`fit()` was 2px off).
- Inline scripts in page content must not contain `<`. The snippet is fine.
- Write each edit script to assert its anchors before writing; a missed anchor then writes nothing.
- SiteGround sometimes answers 202 (a bot challenge): retry once. GitHub release downloads have had 504s; `install-base-tools.sh` retries.

## 6. Files to read

`CLAUDE.md`, `AGENTS.md` (regulated copy, OBMT), `.claude/project-memory/link-ideas-practice-page.md` (full history, every snippet and undo),
`.claude/project-memory/open-items.md`, `link-ideas/peek-nav.php`, `link-ideas/check-peek-nav.mjs`, `page-navigation/jump-links.php` (the per-page rows the
Peek reads), and `docs/inter-model/2026-11-08-peek-nav-kiro-template-review-handoff.md` (Kiro's template review and its outcomes addendum).

## 7. Paste-ready prompt for the next session

> Read `docs/inter-model/2026-11-09-peek-nav-session-handoff.md` on branch `claude/wizardly-bell-xu0rta` of `alanmz-crypto/new-masseuse-site` (pull
> the latest commit first), then `CLAUDE.md`, `AGENTS.md` and `.claude/project-memory/link-ideas-practice-page.md`. Continue the Peek navigation work
> from section 4: confirm snippet #1380's staging39 check, then build the template structure (shared core plus per-page data, the summary-list lint, and
> an installer and check that handle several pages) on staging39 only, and keep assessing the design as you go. Tell me about any simplification
> before you build it. Never touch `main` or live.

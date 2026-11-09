# Kiro handoff: assess the current Peek navigation for Codex

Ryan reviewed the latest staging result, said **“Looks good”**, and asked Codex
to prepare this handoff so Kiro can give an assessment. This is an independent
review of the integrated design and implementation, not an instruction to build
or deploy another version. Written 2026-11-09.

## Start here

- Repository: `alanmz-crypto/new-masseuse-site`.
- Branch: **`claude/wizardly-bell-xu0rta`**. Pull its latest commit without
  overwriting local work. The completed deployment/evidence baseline is
  **`bfa5ef0bb2cd1daff64650cbd660d8285fbb1e83`**; this handoff follows it.
- Site: **https://staging39.thaisolitude.com/**. Staging39 is the only site to
  browse for this assessment. Main and live have not received this Peek rollout.
- Deliver an assessment for Codex to evaluate and discuss with Ryan. Do not
  implement recommendations, alter page copy, install anything or change a website.
- Do not edit `ops/request.json` or `ops/live-request.json`, dispatch Actions,
  run `scripts/ci/request.sh`, or run installers/push/rollback scripts. Existing
  reports and artifacts may be read. Never push/merge main, force-push, or modify
  the shared feature branch as part of this review.

Check your GitHub and staging browser access first and disclose limitations.
Earlier direct browsing sometimes received SiteGround HTTP202 or a proxy denial;
the final Actions public check succeeded. That does not guarantee access from
your environment. Stop on a challenge/denial rather than repeatedly retrying it.
Use the retained actual-page captures if necessary, clearly distinguishing image
inspection from interaction testing. No SSH credentials are needed to read the
repository's existing Actions evidence.

Read the project instructions and this brief first. Then assess actual rendered
Thai Massage and the other representative pages below, recording your own design
judgment before reading the previous agents' conclusions. Afterward compare your
judgment with their findings and read the relevant implementation fully. Passing
checks and Ryan's positive visual feedback are evidence, not reasons to withhold
a well-supported criticism.

## What is deployed

One shared WordPress/WPCode core, snippet **#1380**, serves the navigation. Its
stored code is **97,130 bytes**, SHA256
`c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e`.
The PHP file including its opening tag is 97,136 bytes, SHA256
`602df3dca522b4f6be7db173202c8f64acec3806cffd00705f46fb27ee4815f6`.
Do not confuse these two hashes, or frozen shared v4, with another deployed version.

| Part | Current behavior |
|---|---|
| Title strip | Read-only sliding section titles under the pinned header on all visitor HTML pages; admin/feed contexts excluded. |
| Four approved service pages | Thai, Therapeutic, Deep Tissue and Chair retain summary rails and approved page data, snippets #1381–1384. |
| Pages previously without rails | Title-only rail boxes: section number, existing title, jump/close/side controls; no summary body or reading-time minutes. No-heading pages receive one page-title entry; short pages remain eligible and may have multiple sections. |
| Navigation source | Existing `.ts-jump` labels/targets in actual page order first; otherwise visible main H2s, then page identity. Existing IDs stay links; ID-less destinations use semantic buttons and original DOM nodes, without invented IDs/hashes. Search keeps one fixed page identity. |
| Existing practice rails | `/rail-nav-ideas/` and `/index-nav-ideas/` keep their own rails. Progress modes A–D show the shared rail; E hides it and releases its padding while the demo rail is active. |
| Data | Small current-page JSON registrations through `ts_pk_pages`; approved `[text, anchor]` data/provenance lives in the repo and is installed in WordPress. Browsers do not fetch remote repository JSON. |
| Fallbacks | Existing ordinary jump links work without JavaScript. Without PointerEvent support, the title strip and ordinary links remain. No-JavaScript is not a promise of newly invented links on pages that never had a jump row. |

Settled behavior to preserve: sliding previews do not move the page; lifting
keeps a preview; tapping the open title/jump area jumps; tapping a closed box
opens it. Summary text remains a reading/scrolling surface, and the close/side
controls perform their own actions rather than navigating. The
index supports press/fan, slide onto a name and lift, slide down onto the rail,
quick tap to keep names open and a two-second hold. **No page scrolling from the
index icon**, and no “You are in…” line. Keep the left-hand option, keyboard
paths and first-time guidance. Staging39 has no sitewide reading-progress bar
or scroll-to-top button; live still has them.

Recent requests also required swipes to stay with the touched control: closed
topics and non-scrollable summaries must not pan the page; scrollable summaries
and constrained outer cards must retain their own scrolling. Summary space was
enlarged globally, with distant closed rows folding before the summary shrinks.
The reported delay when switching rapidly between boxes was **not reproduced**
and is not claimed fixed. Assess both the remaining responsiveness question and
the usable finger area in Therapeutic's research box.

No new summary wording was added in the latest rail rollout. The four approved
data sets are unchanged. Short summaries use distinct ideas of about three words,
merge overlap and avoid repetition/minutiae; add “etc.” at approximately 60% or
less section coverage. Research summaries may be longer and must retain the
source's qualifications. Other pages need their own wording approval before
summaries are added. Preserve whole-paragraph fallbacks; never split sentences
mechanically. Lint cannot prove semantic accuracy, non-overlap or coverage.

## Read in this order

1. `CLAUDE.md`, `AGENTS.md`, `.claude/project-memory/open-items.md`, and
   `ops/README.md`. The explicit review-only limits above apply to this task.
2. This brief and the two current scope inventories:
   `scripts/work-with-me-employer/link-ideas/peek-pages.json` and
   `scripts/work-with-me-employer/link-ideas/title-strip-pages.json`.
3. Inspect the representative pages below and write your initial observations.
4. [Current deployment, design history, evidence and recovery](../reviews/2026-11-09-peek-title-rails.md),
   [shared-template guide](../../scripts/work-with-me-employer/link-ideas/README-peek.md),
   and `.claude/project-memory/link-ideas-practice-page.md`.
5. [Previous Kiro template review, including its outcomes addendum](2026-11-08-peek-nav-kiro-template-review-handoff.md),
   [current session handoff](2026-11-09-peek-nav-session-handoff.md),
   [summary-space record](../reviews/2026-11-09-peek-summary-space.md) and
   [approved wording](../reviews/2026-11-09-peek-summary-drafts.md).
   Historical Thai-only architecture/counts/rollback commands are not current.

Fully read the relevant source before proposing implementation changes. Paths
below are relative to `scripts/work-with-me-employer/`:

- Shared behavior: `link-ideas/peek-nav.php`, `page-navigation/jump-links.php`.
- Data/wording: `link-ideas/peek-data/*.json`, `link-ideas/lint-gists.py`,
  `client-pages/wording_tiers.py`, plus the source sections on the actual pages.
- Guards: `link-ideas/install-peek-nav.php`, `link-ideas/push-peek-nav.sh`,
  `link-ideas/test-installer.php`,
  `link-ideas/tests/core-data-test.php`, `link-ideas/tests/gists-selftest.py`.
- Browser coverage: `link-ideas/check-peek-title-rails.mjs`,
  `link-ideas/check-title-strip.mjs`, `link-ideas/check-peek-pages.mjs`,
  `link-ideas/check-peek-clearance.mjs`, `link-ideas/check-peek-touch.mjs`,
  `link-ideas/check-peek-nav.mjs`, `link-ideas/check-peek-thai-regression.mjs`.
- Orchestration/fidelity: `link-ideas/core-title-rail/check-public.mjs`,
  `link-ideas/core-title-rail/check-rehearsal.mjs`, their retained wrapper calls,
  `link-ideas/core-title-rail/install-peek-core-title-rail.php`,
  `link-ideas/mock/build-mock.py`, and `scripts/check-all.sh` from repository root.

## Pages and situations to assess

Use phone, tablet and desktop widths, both rail sides, light/dark themes,
enlarged text, normal/reduced motion and first-use/returning states. Report which
combinations you actually inspected; do not imply a complete matrix by sampling.

| Pages on staging39 | Focus |
|---|---|
| `/thai-massage/` | Independently assess the original design, normal/research boxes, settled gestures and focus. |
| `/therapeutic-massage/` | Research-box finger area, inner/outer scrolling, rapid topic switching; distinguish perceived delay from measured timing. |
| `/deep-tissue-massage/`, `/chair-massage/` | Qualified research wording, short/long cards, short landscape and enlarged-text fallback. |
| `/services/`, `/work-with-me/` | Seven sections, complete open titles, closed-row ellipses, CTA clearance, original jump sources. |
| `/`, `/about/`, `/contact/`, `/corporate-massage-portland/` | Automatically collected headings, ID-less destinations, hero/full-width blocks, first target far down, menus and ordinary links. |
| `/privacy-policy/`, `/accessibility/`, `/personal-automation/` and its `/privacy/`, `/terms/` pages | Native closed disclosures, visible-heading filtering, short/no-heading content and proportionality of the navigation. |
| `/?s=massage`, `/peek-title-missing-page-verification-20261109/` | Search form hit areas, first original reading-block target, fixed page identity, 404 fallback. |
| Four `*-nav-ideas`/`in-page-link-ideas` practice pages listed in the inventory | Existing rails remain exclusive; Progress A–E transitions; interaction with the existing bottom control. |

Do not follow booking/contact links into submissions or external actions.
Read-only browsing, existing artifacts and isolated local probes are sufficient
for this review; a new server operation is not part of the handoff.

## Questions that matter most

1. **Design clarity:** Can a new visitor understand current position, preview,
   keep-open and jump without trial and error? Do title-only cards feel coherent
   beside summary cards? Are one-entry rails useful and unobtrusive? Assess visual
   hierarchy and discoverability, not just bounds and passing selectors.
2. **Touch ownership and responsiveness:** Test starts on title, closed row,
   summary text, blank box area, controls and scroll boundaries. Check rapid
   switching, direction changes, resize, menu opening and interruption. If you
   find latency, record input/paint timing and device/browser rather than infer a
   network fetch. Do not confuse browser-generated touch with a real phone.
3. **Navigation correctness and accessibility:** Check source/destination order,
   filtered/reordered headings, missing IDs, native `details` and animated FAQ
   targets, focus/hash/history after jumps, keyboard escape/return, print and
   no-JavaScript. Assess focus visibility, target sizes, contrast, screen-reader
   semantics and the moving strip. Identify missing VoiceOver/TalkBack/Safari
   evidence rather than claim those were tested by click simulation.
4. **Layout:** Look for obstruction at top, landed and scrolled states, including
   the full index hit rectangle beside Search controls, transient labels over the
   strip/hero, long titles, header/menu changes and viewport resizing. Preserve
   the learned rule: pad text blocks/forms where needed, never the full-width
   block's containing column; exclude navigation. Content focus uses scroll
   margins, not page-wide scroll padding.
5. **Code and safety:** Is shared behavior properly separated from page data?
   Audit safe JSON/attribute encoding, current-page emission, observer/frame work,
   forced layout, gesture/click state, disposal and duplicate initialization.
   Review full ownership/state/hash guards and one-page recovery. Suggest a
   simplification only with a concrete benefit and regression risk; Codex must
   explain it to Ryan before building it.
6. **Evidence quality:** Could an oracle mirror the implementation or a fixture
   omit an actual Astra structure? Which missing test would catch a real failure?
   A visually discovered Search-button collision blocked candidate2 despite all
   automated checks passing; candidate3 corrected it and added hit-area checks.
7. **Next priorities:** Separate issues worth fixing now from optional polish,
   device validation and any future live-readiness work. This assessment does
   not authorize a live rollout or new summary wording.

## Existing evidence and limits

These are **Codex's completed results**, not Kiro's testing. Read reports rather
than infer success from job colour or a large assertion count.

| Evidence | Recorded result |
|---|---|
| [Final fresh-copy rehearsal](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42662818962) | 13,836/0 across ten suites, finished 17:31:25 UTC. Includes local fixtures and full Thai 486/0; untouched staging39 copy with uploads, exact rollback/reapply and wording gates. |
| [Guarded staging apply](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42666333809) | Written 17:37:37; finished 17:41:06 UTC PASS. Fresh encrypted off-site export/snapshot verified; 19 origin scope checks; restore import 0, 104 fingerprints with only #1380/cache changes. |
| [Final public browser run](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42667176429) | 11,829/0 across nine executed suites, finished 17:49:09 UTC. Includes 342 repeated diagnostic-subset assertions; not 11,829 distinct behaviors. C/F title/rail matrices cover 19 published routes plus search and 404. |
| [Stored inspection](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42667209210) | Finished 17:49:42 UTC PASS: exact core hash above and unchanged four published data records. |
| [Public image manifest](../reviews/assets/2026-11-09-peek-title-rails/public/capture-manifest.json) | 110 capture events, 104 distinct filenames; 47 identical to reviewed copy images, 57 changed/new images inspected. 41 images retained here; remaining originals in the artifact. |

The current record links complete counts, artifact hashes and failed earlier
rehearsals. The broad post-change comparison had **20/20 pixel differences against
live**, not an aggregate pass or a comparison with the immediately preceding
staging version. Origin emission checks are not gesture tests.

Known open limitations: physical Safari/iOS/Android and real assistive technology;
the unreproduced rapid-switch delay; the older Thai native-touch jump briefly
focusing its heading then losing focus to `BODY` on compatibility mousedown;
and the existing Firefox practice-rail Enter issue (explicit INFO, not a passing
keyboard claim). Transient labels sometimes cover the display strip/hero in a
captured instant; closed long rows may ellipsize. Assess their impact without
misreporting them as newly reproduced regressions.

Recovery context only: the actual snapshot
`peek-template-core-title-rail-before-20261109-173737-cae68f35.json` restores
current core to frozen v4. Older recovery must follow only afterward, with a
fresh verified backup and full current-state checks. No staging rollback was
performed during deployment. Removing one service page's data now leaves its
title-only rail/strip and original plain-row fallback. Before any future runtime
edit, freeze current code as v5 and pin this transition; **v5 does not yet exist**.
Do not run recovery for this assessment.

## Return to Codex

Return a report, preferably named
`docs/inter-model/2026-11-09-peek-nav-kiro-assessment.md`, or paste its Markdown for
Ryan to bring back. A report-only handback may use your own branch based on this
feature branch; no implementation patch is requested.

Start with a concise verdict on usability/design and the three most useful next
actions. Then list findings by severity with: page and file:line at the reviewed
commit; exact viewport/side/input/state; expected versus actual behavior; evidence;
classification (**reproduced**, **source-based concern**, or **design judgment**);
and the smallest proposed correction with its tradeoffs. State what works well
and should remain. Separate defects, enhancements and validation gaps. Include
coverage/access limits, new tests actually run and confidence in each conclusion.
If nothing material is found, say that plainly rather than manufacture issues.

End with an ordered recommendation for Codex, identifying any proposed
simplification that needs explaining to Ryan before implementation. Leave code,
wording and deployment decisions for the next Codex/Ryan discussion.

## Paste-ready prompt

> Read `docs/inter-model/2026-11-09-peek-nav-kiro-assessment-handoff.md` on the latest
> `claude/wizardly-bell-xu0rta` branch of `alanmz-crypto/new-masseuse-site` and follow
> it. Independently assess the actual staging39 Peek design, then review its code
> and prior evidence. Return a prioritized assessment for Codex, distinguishing
> reproduced defects, source-based concerns and design judgments. Review only:
> no implementation, website changes, operations requests, main or live changes.

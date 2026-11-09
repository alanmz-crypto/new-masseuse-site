# Peek shared template

Staging39 only. `.ts-jump` is the sole navigation/destination source. The core
`peek-nav.php` reads the current page's `ts_pk_pages` registration and prints one
safely encoded `ts-peek-nav-data` JSON element. WordPress/WPCode stores all deployed
code and data; the browser never requests repository JSON files.

`peek-data/<slug>.json` stores `[text, anchor]` items, approval provenance and an
editorial coverage classification. Anchors identify existing sections, not a
second navigation order. Thai wording is approved; Ryan approved the Therapeutic,
Deep Tissue and Chair sets in chat on 2026-11-09. All four service pages are deployed
on staging39: core #1380, Thai data #1381, Therapeutic #1382, Deep Tissue #1383,
Chair #1384. Actual snapshots, per-page results and final aggregate verification
are in the [service rollout record](../../../docs/reviews/2026-11-09-peek-service-rollout.md).
Unapproved phrases are stripped before public JSON emission,
and the runtime uses whole source paragraphs. No mechanical sentence splitting
is used for the summary fallback.

Run `python3 -I -B lint-gists.py` and `tests/gists-selftest.py`; both are in
`scripts/check-all.sh`, with PHP ownership/recovery/JSON tests. Lint cannot decide
semantic accuracy, overlap or percentage coverage. Approval must follow rendered
source review. `peek-pages.json` records the browser expectations and explicit
rollout status; Services/short-page eligibility remains undecided.

## Current core correction: awaiting rehearsal and deployment

The title-strip clearance and closed-topic touch corrections are implemented, but still await a fresh
staging39-copy rehearsal, rendered-copy and visual review, and guarded deployment.
The [current correction record](../../../docs/reviews/2026-11-09-peek-clearance.md)
holds the actual baseline evidence and verification status. Its pre-fix public
check reproduced strip/card overlap; that diagnostic failure is not a release pass.

`core-upgrade` updates the uniquely owned #1380 in place from the exact deployed
shared version frozen in `peek-nav-shared-v1.php` to the reviewed `peek-nav.php`.
Frozen shared-v1 code SHA256:
`dda8aecd38e417ad37766d2df951c6bdc761bf44997e030bbef5a36e9321f573`.
The upgrade preserves the snippet's identity/settings and all four page-data
records (#1381–1384). It checks and snapshots complete old/new core states and
the four unchanged data states, using a separate `peek-core-upgrade-v1` bundle.
Changed, missing or duplicate ownership/data stops an actual upgrade or reversal.
There is no snippet swap or planned fallback-only interval.

The dedicated rehearsal adapter has no preparation hook: it must exercise the
untouched four-page staging39 baseline, with uploads. Run offline gates first;
then this is the guarded route, with each report reviewed before the next step:

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/core-upgrade/install-peek-core-upgrade.php --with-uploads
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-upgrade'
# Only after the fresh-copy gates and actual copied-page review pass:
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core-upgrade --apply'
```

The rehearsal includes exact rollback/reapply, rendered regulated-copy gates,
the actual-clearance matrix in Chromium/Firefox, native Chromium touch checks, and retained page/Thai gesture
checks. Every staging write takes a fresh verified encrypted off-site backup.
Review the apply, backup, restore and public browser reports afterward, and record
the actual new snapshot in the correction record. No upgrade snapshot exists yet.

Recovery must name `core-upgrade --rollback FILE` explicitly in the push arguments;
the wrapper's preparatory preflight uses that component. Use the matching reviewed
package and actual upgrade snapshot after checking current ownership and complete state. Any later
core or page-data drift, including a page-only removal, makes that reversal refuse.
Reverse this upgrade before attempting the historical shared-v1-to-legacy recovery
below. Neither an old migration snapshot nor a legacy deletion snapshot can undo
the upgraded payload.

## Historical legacy-to-shared-v1 migration

This section records the completed 2026-11-09 legacy-to-shared migration on
staging39 (#1380 core, #1381 Thai data). Do not repeat it as a new upgrade:
`core` remains tied to frozen `peek-nav-shared-v1.php`, preserving the original
snapshot's meaning. Current revisions use the separate upgrade route above.
Original snapshots and verification are in the
[template record](../../../docs/reviews/2026-11-09-peek-template.md).

The original payload is frozen in `peek-nav-legacy.php`. Original legacy starting code
SHA256: `b7314c8938a728bf64b6dac16c48bba722b4aea59b277c9f56f0760132322c01`.
The core installer preserves the existing snippet ID/title/settings and snapshots
its complete original post/meta/taxonomies. A changed or duplicate record stops
the operation. Old legacy rollback snapshots must match their code hash too.

The completed migration ran offline gates and BOTH staging39-copy rehearsals
before either staging write. Historical commands (not a sequence to rerun against
the already-applied baseline):

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/peek-page/install-peek-thai-data.php
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/install-peek-nav.php --with-uploads
```

The core rehearsal adds the prerequisite Thai data only to its isolated copy,
before its baseline. The data rehearsal has its own browser check: data alone is
inert under the legacy runtime. The core rehearsal tests real copied page shapes;
prepared-page browser fixtures do not register them in WordPress.
Include uploads in the core rehearsal: they contain layout assets as well as
images. The 2026-11-09 asset-incomplete copy produced three geometry failures;
the unchanged package passed with uploads, without relaxing any assertions.

It then dry-ran and applied Thai data, followed by the core:

```sh
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page thai-massage'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page thai-massage --apply'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'core --apply'
scripts/ci/request.sh run --target staging39 --script link-ideas/check-peek-nav.mjs --args 'https://staging39.thaisolitude.com'
```

Every write, including rollback, takes and verifies a fresh encrypted off-site
backup first. Read every report section: an overall operation PASS does not make
informational post-change differences pass. INVALID establishes no verification.
Record actual snapshots, not guessed names. Reversing this original migration
requires reversing any core upgrade first. Then restore the legacy core using
`core --rollback FILE`, and remove Thai data using `page thai-massage --rollback FILE`,
with each component's actual snapshot. Core reversal affects every Peek page.
For one service page, reverse only its page data using its actual snapshot in the
current rollout record. The completed migration had no fallback-only swap;
deliberate page-data removal under the shared core restores that page's plain row.

Do not deploy another page until its wording/rollout is approved. An independently
edited or newer data/core payload is deliberately refused; a future version needs
its own reviewed exact-state migration, not deletion using an old snapshot.

## Completed approved service-page rollout

The three approved pages use the same installer; their rehearsal adapters only
fix a page identity on an isolated copy. The following sequence was completed for
`therapeutic-massage`, `deep-tissue-massage`, then `chair-massage`. Do not rerun it
against their already-applied baselines; future payload revisions need their own
reviewed migration. Historical Therapeutic example:

```sh
scripts/ci/request.sh rehearse --source staging39 --installer link-ideas/peek-page/therapeutic-massage/install-peek-page-data.php --with-uploads
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page therapeutic-massage'
scripts/ci/request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args 'page therapeutic-massage --apply'
```

Each rehearsal and its approved-card captures were reviewed before the write;
every backup/public/restore section was reviewed afterward. Each page's manifest
state was then changed to `enabled` before its public check, for example:

```sh
scripts/ci/request.sh run --target staging39 --script link-ideas/check-peek-pages.mjs --args 'https://staging39.thaisolitude.com --capture-page therapeutic-massage'
```

Public captures were reviewed before proceeding to the next page. The local-only
`--enabled-page` option changes rehearsal expectations, never public expectations
or WordPress data. `--capture-page` adds first/research-start/research-end captures
at three widths and checks card bounds and access to the final research item.
The current rollout record holds final aggregate-check status and actual per-page
snapshots. A page-only reversal removes only its data and restores its plain jump
row without changing the shared core or other pages.

Known shared-placement defect: Chair's first preview overlaps the strip before it
sticks, and its tablet focus ring grazes the Corporate CTA. No inaccessible
card/section control was observed in that rollout. Its geometry checker used the
strip's eventual sticky position. The correction and actual-rectangle assertions
above await fresh-copy verification and deployment. Start/end research captures
and exact item checks do not claim visual review of every intermediate position.

## Local browser checks

```sh
python3 mock/build-mock.py
python3 -m http.server 8091 -d mock/site
# In another shell, TS_PLAYWRIGHT_ROOT points to a Playwright package.json:
node check-peek-nav.mjs http://localhost:8091 --fixtures
node check-peek-clearance.mjs http://localhost:8091
node check-peek-clearance.mjs http://localhost:8091 --firefox
node check-peek-touch.mjs http://localhost:8091
```

The generic suite is driven by the page manifest; the historical detailed Thai
gesture regression remains explicit in `check-peek-thai-regression.mjs`.
`check-peek-clearance.mjs` measures actual visible strip/header/card rectangles,
independently of the runtime's placement formula. It covers all four enabled
pages, both sides, page top, sticky transitions, kept cards returning to the top,
resize/landscape and enlarged-text cases, approved words and research reachability.
It injects no runtime or page data; local mocks remain structural evidence only.
`--quick` is a Thai/Chair diagnostic subset, not the full rehearsal gate. Public
staging39 runs use the same checker after deployment and require capture review.
`check-peek-touch.mjs` uses native Chromium touch input for control drags, taps,
summary scroll containment and the constrained outer-card fallback. Its optional
`--baseline-v1` injects the frozen old core only into localhost fixtures; it is
refused for public staging39. Browser simulation is not a physical-device result.
Synthetic activation is not VoiceOver/TalkBack validation. Safari and physical-device tests
remain outstanding. See the [review and deployment record](../../../docs/reviews/2026-11-09-peek-template.md).

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

## Guarded migration from #1380

This section records the completed 2026-11-09 legacy-to-shared migration on
staging39 (#1380 core, #1381 Thai data). Do not repeat it as a new upgrade:
future payload revisions need their own reviewed exact-state migration. Current
snapshots and verification are in the linked review record below.

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
restores the legacy core first, then removes Thai data, with the respective actual snapshots via
`push-peek-nav.sh --rollback FILE`; core reversal affects every Peek page.
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
card/section control was observed. The geometry checker uses the strip's eventual
sticky position; a focused core correction and actual-rectangle assertions remain
open. Start/end research captures and exact item checks do not claim visual review
of every intermediate scroll position.

## Local browser checks

```sh
python3 mock/build-mock.py
python3 -m http.server 8091 -d mock/site
# In another shell, TS_PLAYWRIGHT_ROOT points to a Playwright package.json:
node check-peek-nav.mjs http://localhost:8091 --fixtures
```

The generic suite is driven by the page manifest; the historical detailed Thai
gesture regression remains explicit in `check-peek-thai-regression.mjs`. Synthetic
activation is not VoiceOver/TalkBack validation. Safari and physical-device tests
remain outstanding. See the [review and deployment record](../../../docs/reviews/2026-11-09-peek-template.md).

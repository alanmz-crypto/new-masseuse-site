# Peek shared template

Staging39 only. `.ts-jump` is the sole navigation/destination source. The core
`peek-nav.php` reads the current page's `ts_pk_pages` registration and prints one
safely encoded `ts-peek-nav-data` JSON element. WordPress/WPCode stores all deployed
code and data; the browser never requests repository JSON files.

`peek-data/<slug>.json` stores `[text, anchor]` items, approval provenance and an
editorial coverage classification. Anchors identify existing sections, not a
second navigation order. Thai wording is approved; Therapeutic, Deep Tissue and
Chair remain drafts. Unapproved phrases are stripped before public JSON emission,
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

Complete offline gates and BOTH staging39-copy rehearsals before either staging
write (from the repository root):

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

Then dry-run and apply the Thai data, followed by the core:

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
Record actual snapshots, not guessed names. Reverse core first (restores the
exact legacy record), then page data, with the respective actual snapshots via
`push-peek-nav.sh --rollback FILE`. No fallback-only interval is planned for this
in-place migration; deliberate page-data removal under the shared core restores
the plain jump row.

Do not deploy another page until its wording/rollout is approved. An independently
edited or newer data/core payload is deliberately refused; a future version needs
its own reviewed exact-state migration, not deletion using an old snapshot.

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

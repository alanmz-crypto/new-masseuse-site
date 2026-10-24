# ops/ — asking GitHub Actions to do server work

A cloud session (claude.ai/code or the Claude app) has no SSH and no localhost:8090 stack. GitHub Actions has both: it holds a
GitHub-only SiteGround key and can start the practice images. This folder is how a session asks it for work.

- `ops/request.json` — a request for **site-ops.yml**: staging writes, rehearsals, read-only inspection and tests, backups. Committing a
  change to this file on any branch except `main` and pushing it starts the run. Use `scripts/ci/request.sh` instead of editing it by hand.
- `ops/live-request.json` — a **live write** for **site-live.yml**. It runs only after Ryan merges it to `main` and starts
  "Site ops (LIVE)" himself, typing the request id as confirmation. Agents prepare it (`request.sh live ...`) and never start the run.

Results: every run commits a report to the **ops-log** branch (`reports/YYYY-MM-DD/HHMMSS-<id>.md`, newest first in `INDEX.md`) and shows
the same report on the run's summary page. `scripts/ci/request.sh wait <id>` fetches it. Screenshots and full logs are the run's artifact.

## Request format

```json
{ "id": "thai-faq-apply-1", "op": "run", "target": "staging36", "script": "thai/push-thai-evidence.sh", "args": ["--apply"], "tests": "auto" }
```

| field | meaning |
|---|---|
| `id` | 3-60 characters of `a-z 0-9 -`. Each id runs once: a request whose id already has a report is refused (so re-pushing a branch never repeats an apply). |
| `op` | `run`, `rehearse`, `inspect` or `backup` (below). |
| `target` | `staging36` (the working copy), another `stagingNN`, or `live`. For `run` it is also passed as `S_TARGET`. |
| `script` | `run` only: a path under `scripts/work-with-me-employer/` (`.sh`, `.py`, `.mjs`), or `check-all.sh`. |
| `args` | Array of arguments for the script. `--apply` / `--rollback FILE` make it a **write**: allowed only for `push-*.sh`, and on `live` only in the live lane. |
| `tests` | `auto` (default): after a write, run `post-change-test.sh <target>` and `prove-restore.sh <target>`. `none` skips them. |
| `installer` | `rehearse` only: an `install-*.php` under `scripts/work-with-me-employer/`. |
| `query`, `arg` | `inspect` only: see below. |

### Operations

- **rehearse** — the localhost rehearsal, on a throwaway copy of `target` (default `staging36`) built inside the runner from a streamed
  DB export and the site's files (nothing is written on the server): fingerprint, preflight, apply, apply again, rollback with the
  snapshot apply wrote, re-apply; every fingerprint must return exactly; the 10 pages must answer 200; no new PHP errors. The report lists
  what the change touches. `"args": ["--with-uploads"]` also copies the media library (slower). Script: `scripts/ci/rehearse.sh`.
- **run** — a push script (a dry run without `args`, `--apply`, `--rollback '$HOME/private-backups/<snapshot>.json'`), or a read-only
  tool such as `post-change-test.sh` (args `["staging36"]`), `prove-restore.sh`, `check-all.sh` (args `["https://staging36.thaisolitude.com"]`),
  `tools/a11y-audit.mjs`, `thai/check-thai-page.mjs`. Every push script's `--apply` takes a DB export on the server first, and the copy is
  verified and stored encrypted off the server (draft release `offsite-YYYY-MM`) **before** anything changes, or nothing changes.
- **inspect** — read-only queries, for writing an installer against the exact stored bytes: `page <slug|id>`, `seo <slug|id>`, `snippets`,
  `snippet <id>`, `css`, `option <name>` (a fixed list of non-secret options), `plugins`, `fingerprint`. Script: `scripts/ci/inspect.sh`.
- **backup** — a DB export of `target` now, plus the verified encrypted off-site copy. Script: `scripts/ci/backup-now.sh`.

Not available through GitHub at all: anything under `cleanup/` (deletions: Ryan, asked twice), `mirror-versions.sh`, `secure-backups*.sh`,
`php-runtime-test.sh`, removal and quarantine tools.

## Examples (from a cloud session)

```bash
scripts/ci/request.sh rehearse --installer thai/install-thai-faq.php           # 1. rehearse on a throwaway copy of staging36
scripts/ci/request.sh run --target staging36 --script thai/push-thai-faq.sh   # 2. dry run on staging36 (preflight only)
scripts/ci/request.sh run --target staging36 --script thai/push-thai-faq.sh --args "--apply"   # 3. apply + tests
scripts/ci/request.sh inspect --target live --query page --arg deep-tissue-massage
scripts/ci/request.sh run --target live --script thai/push-thai-faq.sh         # live DRY RUN (read-only) is fine here
scripts/ci/request.sh live --script thai/push-thai-faq.sh --args "--apply"    # 4. PREPARE the live write for Ryan; runs nothing
scripts/ci/request.sh wait live-20261024-101500 1440                          #    then wait for his run's report
```

Commit your code first: a request runs the code of the commit it is in, and `request.sh` commits only `ops/request.json`.
Runs take 2-15 minutes (a rehearsal about 5; a write with tests about 10). One SiteGround operation runs at a time.

## Testing the rehearsal without SiteGround

`TS_REHEARSAL_FROM_DIR=DIR` makes `rehearse.sh` read `DIR/info.txt`, `DIR/db.sql.gz` and `DIR/files.tar` instead of SSH, and
`TS_REHEARSAL_TEST_PREP='<php>'` runs PHP on the copy before the baseline (for example to take a change out so it can be rehearsed).
First run (2026-10-24): a copy of localhost disguised as staging36, with the `ts-thai-image-crop` block taken out; `thai/install-image-crop.php`
passed every check and the report named exactly `css 29` as touched, as the real live change did on 2026-10-19.

<!-- ref: ../scripts/ci/request.sh, ../scripts/ci/run-op.sh — the helper and the rules; ../.github/workflows/site-ops.yml, ../.github/workflows/site-live.yml — the two lanes; ../CLOUD-SETUP.md — the one-time setup -->

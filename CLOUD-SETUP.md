# Working on Thai Solitude from a phone (cloud session + GitHub Actions)

Claude in a cloud session (claude.ai/code, or the Code tab of the Claude app) edits this repository and pushes branches. It cannot reach
SiteGround, so **GitHub Actions does the server work** with the same scripts this computer uses: rehearsal on a throwaway copy, dry runs,
staging applies with a verified backup first, the after-change tests, and live applies that only you can start. Nothing needs this computer
to be on. The request format and examples are in `ops/README.md`; the rules a cloud session follows are in `CLAUDE.md`.

## Already done (2026-10-24, from this computer)

- GitHub environments **staging** (secrets for `site-ops.yml`) and **production** (secrets for `site-live.yml`; GitHub gives them only to
  runs from `main`).
- Workflows `gates.yml` (code gates on every push), `site-ops.yml`, `site-live.yml`; scripts in `scripts/ci/`; `CLAUDE.md`; the project
  notes in `.claude/project-memory/` (username, login email, Butsaba's Gmail and your home IP masked).
- The off-site backup key. Public half: `scripts/ci/offsite-recipient.txt`. **Private half: `~/.config/thai-solitude/offsite-backups.agekey`
  on this computer only. Copy that one line into your password manager now**: without it the off-site copies cannot be read.
- Tested here: request checks (16 refusal cases), the backup copy (6 cases against a simulated server), the encrypted off-site copy
  against GitHub itself (stored, read back, decrypted identical; test files deleted afterwards), the rehearsal on a copy of localhost
  (passed, named exactly what the change touches), all gates. **Not yet tested: anything that needs the SiteGround key** (steps 2-4).

## Your one-time steps (about 10 minutes, on this computer)

1. **Push and merge.** `! git push https://github.com/alanmz-crypto/new-masseuse-site.git feat/2026-10-24-cloud-ops`, then merge
   the pull request into `main` (the workflows start by hand only once they are on `main`). Wait for its **Gates** check to pass first.
2. **Make the GitHub key and store the secrets:** `! scripts/ci/setup-github-secrets.sh`. It prints a public key (one line, `ssh-ed25519 ...`).
3. **Switch it on in SiteGround:** Site Tools > Devs > SSH Keys Manager > Import. Name: `github-actions`; paste the public key.
4. **First run (a phone is fine):** GitHub > Actions > **Site ops** > Run workflow > branch `main` > request:
   `{"id":"first-check","op":"inspect","target":"staging36","query":"plugins"}`. Expect **RESULT: PASS** with the plugin list.
   "SSH to SiteGround: REFUSED" means step 3 is not done yet.
5. **Claude cloud:** at claude.ai/code connect GitHub and allow the Claude GitHub App on `new-masseuse-site`. In the cloud environment pick
   network access **Custom**, tick "include the default list", and add `thaisolitude.com` and `*.thaisolitude.com`. No secrets go there.

## Day to day, from the phone

- Ask Claude in a cloud session as you do here. It rehearses and applies on **staging36** through GitHub and shows you the reports.
- **Live** is always two taps of yours: (1) merge the pull request Claude prepares (it contains the change and `ops/live-request.json`),
  (2) GitHub > Actions > **Site ops (LIVE)** > Run workflow > branch `main` > type the id Claude gives you (starts with `live-`).
  The run takes the DB export, stores the verified encrypted copy, applies, runs the tests, and posts the report.
- Reports: the run's summary page in the Actions tab, and the `ops-log` branch (`INDEX.md`, newest first).
- Back at the computer: `scripts/ci/fetch-offsite.sh` brings the encrypted off-site copies into `~/WordPress/thai-solitude-server-backups`, decrypted and checked.

## The guards, and their limits

- **Live writes** run only from `site-live.yml`, only on `main`, only when started by hand with the request id typed in, and only for a
  `push-*.sh` script. GitHub's "required reviewers" approval button is not available for private repositories on your plan (GitHub
  refused it with "the billing plan does not support"), so **starting that run is the approval**; Claude never starts it.
- **One SiteGround key opens the whole account**, live included; SiteGround cannot limit a key to one site. The staging lane runs the
  code of whatever branch asked, so a deliberately altered script on a branch could reach live through it. That is the same exposure as
  the ssh-agent on this computer today. What limits it: every request is a commit you can see, deletions and plugin/version tools are not
  available through GitHub at all, and the key can be switched off in one click (delete it in SiteGround's SSH Keys Manager).
- **Backups:** every write first takes a DB export on the server, then a verified copy is stored encrypted (age) in a draft GitHub release
  `offsite-YYYY-MM`, read back and compared, before anything changes. Only your private key can read them.
- **SiteGround's bot challenge** can still answer a test run with HTTP 202; runners get a new IP each run, so it is rarer than at home.
  The tools report INVALID, never a pass.
- **Cost:** GitHub Actions minutes come from your plan's monthly allowance (a rehearsal about 5 minutes, a write with tests 10-15).
- **Not in the cloud:** convmem, your global `~/.claude/CLAUDE.md` and this computer's Claude memory. The repo now carries what the
  project needs (`CLAUDE.md`, `.claude/project-memory/`, `docs/inter-model/`).

## Turning it off

SiteGround: delete the `github-actions` key (cuts all server access at once). GitHub: Settings > Environments > delete the secrets, or
disable the workflows in the Actions tab. To replace the key later: `scripts/ci/setup-github-secrets.sh --rotate`, import the new public key,
delete the old one.

<!-- ref: ./ops/README.md — requests and examples; ./CLAUDE.md — the cloud routine; ./scripts/ci/setup-github-secrets.sh — step 2 -->

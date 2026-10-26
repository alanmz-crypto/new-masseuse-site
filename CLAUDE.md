# CLAUDE.md — Thai Solitude (new-masseuse-site)

`AGENTS.md` is the project manual (Claude Code loads it with this file; if it is not in your context, read it before any work).
`.claude/project-memory/README.md` indexes the project notes: read the entries that touch your task (on Ryan's computer the same notes
are also loaded as memory). This file adds how Ryan likes to work, and how to work from a **cloud session**.

## Working with Ryan

- Plain language. When you name a PR, commit, page ID or file, say in a few words what it is and why it matters now; never lead with bare ids.
- End every reply with a TL;DR (one sentence for short replies; a `## TL;DR` with 2-4 bullets for long ones).
- Use they/them for anyone whose pronouns you have not been told.
- **Open items:** read `.claude/project-memory/open-items.md` first in every session and keep it true (update it in the same pull request as any change that closes, opens or moves an item). Ryan is often on his phone only: give links and tap-by-tap steps.
- End each finished phase with four lines, then the TL;DR: `I finished:` (plain words), `Next step:` (his exact steps if it is a merge or a live run), `Next lane:` (who), `See my work:` (ONE click: a pull request diff URL or a single file). Stamp `Arc: none (ad-hoc)` for routine site work.
- **PR steward** (Ryan granted it on 2026-10-25): for every pull request you open, say what it is, check the Gates check is green and nothing conflicts, answer comments, give Ryan the merge steps, and keep the notes true after it merges. Never merge, force-push or push to `main`.
- Keep going on reversible work. **Stop and warn first** before anything hard to undo: deleting anything, any write to live
  thaisolitude.com, anything without a fresh DB export and a tested rollback, pushing or merging to `main`, messages to other people.
- Live is Ryan's: he decides each live change and starts it himself. Bots and crawlers are welcome; harden only against malicious behaviour.
- The copy is regulated (OBMT, see AGENTS.md): run `client-pages/verify-client-pages.py` gates on a rehearsal before any staging write.

## Cloud sessions (claude.ai/code, the Claude app; `CLAUDE_CODE_REMOTE=true`)

You have no SSH to SiteGround, no `remote_execution.txt`, no ssh-agent and no localhost:8090 stack. GitHub Actions has them and runs the
same scripts for you; `ops/README.md` is the full guide. The routine of AGENTS.md ("change on staging first, then live on Ryan's go")
becomes:

1. Edit and build on your branch (never `main`); run `scripts/check-all.sh` (offline gates) and commit. A request runs the code of its commit.
2. Need the exact stored bytes of a page, snippet or the CSS? `scripts/ci/request.sh inspect --target staging36 --query page --arg <slug>`.
3. Rehearse: `scripts/ci/request.sh rehearse --installer <dir>/install-<name>.php` (throwaway copy of staging36; apply, re-apply,
   exact rollback, re-apply, pages 200, no new PHP errors).
4. Staging: `scripts/ci/request.sh run --target staging36 --script <dir>/push-<name>.sh` (dry run), then with `--args "--apply"`
   (DB export, verified encrypted off-site copy, apply, then `post-change-test.sh` and `prove-restore.sh` automatically).
5. Live: **never start it.** Show Ryan the exact change and wait for his go. Then make the enable-live change (live host in the
   installer's allow-list and the push script's targets, as in the earlier `docs/inter-model/*-enable-live.patch` files), run a live
   dry run (`request.sh run --target live --script ...`, read-only), and `request.sh live --script ... --args "--apply"`; open a pull
   request (in a cloud session `gh pr create` fails: GraphQL is blocked and its token is invalid; use the GitHub tool
   `mcp__github__create_pull_request` or REST, else push and share the `pull/new/<branch>` link) and give Ryan the two steps it prints (merge; then Actions > "Site ops (LIVE)" > Run workflow on main > confirm with the id).
6. Read every report (`request.sh wait <id>`; reports live on the `ops-log` branch). A report that says INVALID proves nothing.

Long waits: run `request.sh` with a long timeout or in the background; it polls the ops-log branch until the report appears.
Public browser tests run in Actions (a fresh IP each run), not from the cloud VM; still ration them (SiteGround's bot challenge).
Deletions, plugin/theme version changes and credential work are not available through GitHub: they stay with Ryan.
When you learn something durable, add it to `.claude/project-memory/` and commit it; update AGENTS.md for project facts as the
existing entries do.

<!-- ref: ./AGENTS.md — the project manual; ./ops/README.md — the GitHub request guide; ./CLOUD-SETUP.md — the one-time setup and the guards -->

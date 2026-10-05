# AGENTS.md — new-masseuse-site

Local practice environment for a new client, migrated from `thaisolitude.com`.
Single-tier practice stack only — unlike willowyhollow, there is currently no
Podman preview stack or GitClones dev repo mirroring this. If that changes,
document the added tier here the way `~/WordPress/willowyhollow-practice/AGENTS.md`
documents its practice/preview split.

## Stack

| Service | Port | Runtime | Container |
|---------|------|---------|-----------|
| WordPress | 127.0.0.1:8090 | Docker | `new-masseuse-wordpress-1` |
| MariaDB | — | Docker | `new-masseuse-db-1` |
| WP-CLI | — | Docker (run-and-remove) | `new-masseuse-wpcli` |

```bash
docker compose up -d      # start db + wordpress
docker compose ps         # check status
docker compose down       # stop (add -v to also destroy the DB volume)
```

Images are digest-pinned (see the dated comment at the top of `docker-compose.yml`)
so rebuilds resolve the same bits a floating tag could later drift away from.
Re-pin deliberately with `docker buildx imagetools inspect <name:tag>`.

## Backup / restore

```bash
./scripts/backup-site.sh                 # snapshot local files + DB now
./scripts/backup-site.sh --note wip      # tag the snapshot
./scripts/backup-site.sh --list          # list snapshots
./scripts/copy-restic-offsite.sh --destination /run/media/lauer/rszl-larch-5t-fs/thai-solitude-restic-repo --apply
./scripts/test-restore.sh                # restore latest into an isolated throwaway stack on :8091
./scripts/test-restore.sh --down         # tear down the throwaway stack
```

The source restic repo is local under `backups/` (gitignored). Its encrypted
copy lives on the separate `rszl-larch-5t-fs` drive. The destination directory
is owned by `lauer`; copying and checking it require no `sudo`. The password
file stays outside that copy and needs its own independent safe storage.
Nothing is sent to SiteGround, WordPress, or a git remote. `test-restore.sh`
never touches the live :8090 stack; it restores into a separate compose project
and separate DB volume. See `scripts/LOCAL-WORKFLOW.md` for a restore check
using the external repository.

## What's tracked in git vs. not

Only `docker-compose.yml`, `scripts/`, `.gitignore`, and this file are tracked.
Everything else is disposable, secret, or both:

| Path | Why excluded |
|------|-------------|
| `public_html/` | Runtime WordPress tree — restore via `test-restore.sh` or a fresh install, not git |
| `deploy/` | Production DB dump, a 79MB production files tarball, `wp-config.php.production` with real credentials |
| `source-files/` | Raw pull from the live site, including its actual `wp-config.php` |
| `source-db.sql`, `new-masseuse-db-fixed.sql` | Database dumps |
| `backups/` | restic repo + password file |
| `remote_execution.txt`, `sql_manager.txt` | Plaintext SSH/DB credentials for the live `thaisolitude.com` site |

**Never commit credentials.** If a new file with SSH keys, DB passwords, or API
tokens shows up in this directory, add it to `.gitignore` before it's ever
staged — check `git status` after any broad `git add`.

## Migration context

This site is mid-migration from `thaisolitude.com` (see `deploy/DEPLOY-NOTES.md`,
not tracked in git). `public_html/mystaging01/` is a **separate, self-contained
WordPress install** nested inside `public_html/` — it has its own `wp-config.php`
and its own database name (different from `new_masseuse_db`), with `DB_HOST`
set to `127.0.0.1`, which is meaningless inside this stack (no second DB
service exists for it). It came along with the migration pull — most likely a
shared-hosting staging clone from the original host — and is orphaned and
non-functional as-is inside this Docker stack. It is client data: don't delete
it without asking, but don't treat it as part of the practice site either.

The active theme is stock **Astra** (parent theme only) — no child theme has
been created yet. Once custom theme work starts, track it explicitly the way
`willowyhollow-practice` ancestor-unignores `public_html/wp-content/themes/astra-child/`
inside an otherwise-ignored `public_html/`.

## PHP-parity check

```bash
./scripts/practice-image-parity.sh          # exit 1 on drift or unverifiable input
./scripts/practice-image-parity.sh --warn   # drift only warns; unpinned/unverifiable still fails
```

Compares the PHP version baked into the pinned `wordpress` image against
`thaisolitude.com`'s actual production PHP (`scripts/practice-expected-php.txt`,
each reading dated with its exact refresh command). Read-only, unwired — no
sync script exists yet in this single-tier project to call it from.

**Known, real drift as of 2026-09-28:** practice runs PHP 8.3.35;
thaisolitude.com runs 8.2.34. `wordpress:latest`/`wordpress:cli` were retagged
to `wordpress:php8.3-apache`/`wordpress:cli-php8.3` (same digest, zero behavior
change) so the checker could verify the pin at all — a version-agnostic tag is
rejected outright, not assumed correct. Re-pinning practice to php8.2 to close
the drift is a separate, deliberate task (same pattern as
`willowyhollow-practice`'s `e803f0c`), not done as part of adding this check.

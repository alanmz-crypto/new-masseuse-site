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
./scripts/backup-site.sh                 # snapshot files + DB now (restic, local-only)
./scripts/backup-site.sh --note wip      # tag the snapshot
./scripts/backup-site.sh --list          # list snapshots
./scripts/test-restore.sh                # restore latest into an isolated throwaway stack on :8091
./scripts/test-restore.sh --down         # tear down the throwaway stack
```

Backups are local-only (restic repo under `backups/`, gitignored) — nothing is
sent to SiteGround, WordPress, or any git remote. `test-restore.sh` never
touches the live :8090 stack; it restores into a separate compose project and
separate DB volume.

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

## No PHP-parity check yet

`willowyhollow-practice` has `scripts/practice-image-parity.sh` comparing its
pinned PHP version against staging2/production. That doesn't exist here yet —
it needs a verified reading of `thaisolitude.com`'s actual production PHP
version first, which hasn't been checked in this pass.

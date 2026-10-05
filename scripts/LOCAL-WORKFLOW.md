# Thai Solitude local workflow

## After a reboot

From this checkout, run `bash scripts/local-ready.sh`, then open
`http://localhost:8090/`. The script starts the database and WordPress if
needed and waits for the homepage to respond. It does not edit WordPress
content or contact a remote site.

The database is stored in the Docker `new_masseuse_db_data` volume. WordPress
files are stored in this checkout's `public_html/`. Both survive an ordinary
reboot, as do the reviewed changes already in the local database. Do not use
`docker compose down -v` when stopping the site: `-v` deletes the database
volume. `docker compose down` without `-v` preserves it.

## Before each new editing session

Run `./scripts/backup-site.sh --note before-edit` after the local site is up.
This saves the local files and a logical database dump to the ignored
`backups/` directory. These snapshots stay on this computer. Use
`./scripts/backup-site.sh --list` to see them.

Make and review edits on localhost. WordPress page content, site settings,
and SEO metadata live in the database, so source files alone do not capture
all changes. Keep the edit source and its application steps under `scripts/`
for each release, and take another snapshot once the local result is reviewed.
The current credibility-pass source is in `scripts/credibility-pass/`; the
current résumé source is in `scripts/assets/`. Review files for private data
before adding them to Git. The live WordPress tree, database dumps, backup
repository, and credentials remain excluded from Git.

### Keep Git edits separate from the runtime checkout

The checkout that owns `public_html/` is the **runtime checkout**. It may have
unrelated local files. Do not broadly stage, stash, clean, or pull over them.
Fetch `origin/main`, then make a clean Git worktree for the next source change:

```bash
git fetch origin
git worktree add -b docs/YYYY-MM-DD-short-name /tmp/thai-source-edit origin/main
```

Edit and commit only the intended source files in that worktree. When a WP-CLI
script in the clean worktree needs to read the running localhost database,
mount its scripts read-only into a one-shot WP-CLI container while using the
runtime checkout's Compose file:

```bash
RUNTIME=/home/lauer/WordPress/new-masseuse-site
SOURCE=/tmp/thai-source-edit
docker compose -f "$RUNTIME/docker-compose.yml" run --rm \
  -v "$SOURCE/scripts:/edit-scripts:ro" wpcli \
  wp eval-file /edit-scripts/credibility-pass/verify-local.php
```

Take the database backup from the runtime checkout before any installer or
other DB mutation. Push each source commit to its own branch; Git never
captures a WordPress database change by itself. Review the rendered localhost
page as well as the source diff.

For the current local credibility pass, the six `source/page-*.html` files in
`scripts/credibility-pass/` are the packaged staging12 inputs. Run
`python3 scripts/credibility-pass/build-content.py` to regenerate the six
reviewed `page-*.html` outputs; it uses only Python's standard library and
does not change WordPress. `install-local.php` applies those outputs only to a
matching localhost copy after a database backup. The packaged inputs and
outputs omit transient WordPress editor nonces. They still contain WordPress
editor metadata, so inspect them before deciding what belongs in a remote Git
repository. The complete current site state is in the local database and
files backup.

The sitewide license and SEO-title follow-up is stored in
`scripts/credibility-pass/sitewide-license-titles.php`. It checks four
SureRank page titles and Astra's copyright footer without writing by default.
After a local database backup, run it through WP-CLI with `apply-local` to
apply only the reviewed values to localhost. It accepts `apply-staging12` only
on the exact staging12 host; that remote action requires its own SiteGround
backup and authorization. The script refuses independent title or footer edits
instead of replacing them. It has no production apply mode.

## If local data is lost or reset

Use `./scripts/test-restore.sh` to check a chosen local snapshot in an
isolated stack on port 8091. Restore the practice stack only after selecting
and checking the snapshot. A reboot does not require a restore or rerunning
the WordPress installers. The files in `scripts/credibility-pass/` are an
edit source for the staging12-based local credibility pass, not a substitute
for a complete site backup.

### Recovery on another computer

A private Git checkout contains the scripts and approved edit sources, but it
does **not** contain the WordPress files or database. To recreate the full
practice site, retain all three: a Git checkout, a copy of the encrypted
`backups/restic-repo/`, and its `backups/restic.password` secret. Store the
password separately from the backup copy, such as in a password manager.
The source restic repository is local. An encrypted copy was created on
2026-10-05 at
`/run/media/lauer/rszl-larch-5t-fs/thai-solitude-restic-repo` and restored
successfully into an isolated test stack. Keep that copy current after new
local backups.

Take a fresh complete backup and let it finish. From this checkout, use
`scripts/copy-restic-offsite.sh --destination
/run/media/lauer/rszl-larch-5t-fs/thai-solitude-restic-repo` to preview the
source and target; add `--apply` to copy and verify. The destination directory
is already owned by `lauer` with mode 700, so routine copy and checks do not
need `sudo`. If running the script from a clean Git worktree without the local
backup, prefix the command with
`SITE_DIR=/home/lauer/WordPress/new-masseuse-site`. The script initializes an encrypted
destination restic repository with the source chunker parameters, copies
missing snapshots with `restic copy`, reads every destination pack with
`restic check --read-data`, and confirms every source snapshot is present
by matching its time, content tree, paths, and tags. It refuses a destination
on the source filesystem. Later
runs copy only snapshots that are missing. This backup copy does not contain
the password file; store that secret separately and verify that it can be
retrieved during recovery. A restic repository on a second drive protects
against loss of the source drive; remove or replicate that drive separately
for protection against loss of this whole computer.

On a new computer with Docker, Compose, restic, Python 3, and curl installed,
clone the private repository, put the encrypted repository and password at
separate readable paths, then run the isolated recovery check:

```bash
MASSEUSE_RESTIC_REPO=/path/to/copied/restic-repo \
MASSEUSE_RESTIC_PASSWORD_FILE=/path/to/restic.password \
./scripts/test-restore.sh
```

To test the copy itself, set `MASSEUSE_RESTIC_REPO` to the external path above
and `MASSEUSE_RESTIC_PASSWORD_FILE` to the separately held password file when
running `test-restore.sh`.

The restore check uses digest-pinned images from the checked-out Compose file,
rewrites URLs only in its throwaway database, checks routes and media, and
leaves the original practice stack untouched. It does not replace the live
runtime automatically. Select a known files snapshot and its paired DB
snapshot with `RESTORE_FILES_SNAPSHOT` and `RESTORE_DB_SNAPSHOT` when needed.

## Staging and production

Staging12 and production are separate SiteGround WordPress installations.
Starting localhost never changes them. Review a specific release on staging
before a separately authorized production promotion. Keep staging crawlable
with `noindex` and check that production remains crawlable and indexable.
Never copy localhost's `siteurl`, `home`, or localhost media URLs directly to
a remote database.

Run `python3 scripts/release-preflight.py` for read-only public checks of
staging12 and production, then follow `scripts/RELEASE-WORKFLOW.md` for the
manual SiteGround backup, claim review, Custom Deploy selection, and post-deploy
checks. The script cannot establish that the public claims are accurate or
that a SiteGround backup exists.

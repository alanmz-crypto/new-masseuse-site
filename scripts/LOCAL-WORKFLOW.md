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

## If local data is lost or reset

Use `./scripts/test-restore.sh` to check a chosen local snapshot in an
isolated stack on port 8091. Restore the practice stack only after selecting
and checking the snapshot. A reboot does not require a restore or rerunning
the WordPress installers. The files in `scripts/credibility-pass/` are an
edit source for the staging12-based local credibility pass, not a substitute
for a complete site backup.

## Staging and production

Staging12 and production are separate SiteGround WordPress installations.
Starting localhost never changes them. Review a specific release on staging
before a separately authorized production promotion. Keep staging crawlable
with `noindex` and check that production remains crawlable and indexable.
Never copy localhost's `siteurl`, `home`, or localhost media URLs directly to
a remote database.

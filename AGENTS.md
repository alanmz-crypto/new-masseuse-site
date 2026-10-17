# AGENTS.md — new-masseuse-site

Thai Solitude, the practice site of Butsaba Dusrai, LMT (Oregon license
LMT-29905). Local practice stack plus a remote SiteGround staging site and live
site. There is no local Podman preview stack and no GitClones dev mirror; see
"Remote staging and promotion" for the boundary between the local practice
stack and the remote release path.

**The marketing copy on this site is regulated.** Most of the non-obvious rules
below exist because of Oregon Board of Massage Therapists (OBMT) advertising
rules, not because of WordPress. Read `inter-model/Thai Solitude Fix Checklist.md`
before rewriting any page text — it is the canonical audit and remediation
backlog, with suggested replacement copy already written.

**Current October 5 staging19 task boundary:** Ryan authorized read-only chore
verification and status-file updates. Any further website write needs the exact
change and placement shown to Ryan for approval, one change at a time, with a
fresh staging database dump before the write. Ask twice before removing
anything. Do not edit localhost, production, or other staging sites; do not
promote, run installers, copy MU-plugins, commit, or push. The staging5 procedure
below is historical for this task. See the current verification at the top of
`inter-model/Thai Solitude Fix Checklist.md`.
Ryan confirmed the practice does not use Twitter. The SEO plugin still emits
Twitter-tag metadata; inspect it as rendered metadata only.

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
bash scripts/local-ready.sh   # start if needed and wait for the homepage
```

Images are digest-pinned (see the dated comment at the top of `docker-compose.yml`)
so rebuilds resolve the same bits a floating tag could later drift away from.
Re-pin deliberately with `docker buildx imagetools inspect <name:tag>`.

WP-CLI is a one-shot container, not a service, so run it with `run --rm`:

```bash
docker compose run --rm wpcli wp <command>
```

## Local editing workflow

Read `scripts/LOCAL-WORKFLOW.md` — it is the authoritative procedure. Summary:

1. Bring up the stack, then run `./scripts/backup-site.sh --note before-edit`.
2. Edit on localhost:8090 and review.
3. WordPress page content, site settings, and SEO metadata live **in the
   database**, so source files alone do not capture all changes.
4. Keep each release's edit source and its application steps under `scripts/`.

The current passes:

- **Credibility pass** — `scripts/credibility-pass/`. Six `source/page-*.html`
  files are the packaged staging12 inputs; `python3 scripts/credibility-pass/build-content.py`
  regenerates the six reviewed `page-*.html` outputs using only the standard
  library. `install-local.php` applies the outputs to a matching localhost copy
  (after a DB backup) and **refuses if the page changed since the reviewed edit**.
  `verify-local.php` is the read-only comparison; `--allow-legacy-content`
  normalizes transient editor nonces/whitespace.
- **Résumé asset** — `scripts/assets/Butsaba_Dusrai_LMT_Resume_License.pdf`.
- **Restyle** — `scripts/restyle/` (CSS candidates plus before/after check JSON
  and screenshots under `shots-local/`). `inter-model/ts-restyle.css` and
  `thai-solitude-restyle-preview.png` are the same work-in-progress, not applied
  to the theme yet.
- **Page installers** — `scripts/install-work-with-me.php` (and the staging10/
  staging12 variants) are idempotent, host-guarded WP-CLI `eval-file` scripts
  that refuse to overwrite independently edited content. They hard-check the
  target host and the Astra theme; do not loosen those guards.

Inspect pages and media through WP-CLI or the REST API:

```bash
docker compose run --rm wpcli wp post list --post_type=page --fields=ID,post_name,post_title
docker compose run --rm wpcli wp post get <ID> --field=post_content
docker compose run --rm wpcli wp search-replace '<old>' '<new>' --all-tables --precise --dry-run
curl -s http://localhost:8090/wp-json/wp/v2/pages
curl -s http://localhost:8090/wp-json/wp/v2/media
```

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

The restic repo is configured by env vars the script sets itself
(`MASSEUSE_RESTIC_REPO`, `MASSEUSE_RESTIC_PASSWORD_FILE`, `MASSEUSE_RESTIC_CACHE_DIR`,
`MASSEUSE_DB_CONTAINER`, `MASSEUSE_DB_NAME`, `MASSEUSE_DB_ROOT_PW`); `KEEP=10`
controls retention. `--files-only` and `--db-only` skip one side.

### Taking a DB dump before bulk edits

`backup-site.sh` covers this, but for an ad-hoc dump:

```bash
docker compose exec db mysqldump -u root -pnewrootpassword new_masseuse_db > /tmp/pre-edit.sql
```

The DB is also stored in the Docker `new_masseuse_db_data` volume and WordPress
files in this checkout's `public_html/`; both survive an ordinary reboot.
**Never use `docker compose down -v`** when stopping the site — `-v` deletes the
database volume.

## Remote staging and promotion

| Role | Address | Purpose |
|------|---------|---------|
| Local practice | `http://localhost:8090` | Isolated local development and restore checks |
| Remote staging | `https://staging5.thaisolitude.com` | Working copy for review before release |
| Live production | `https://thaisolitude.com` | Public site |

`staging5` is the replicated working copy of `staging4`; both resolve and serve
the WordPress site. `staging2.thaisolitude.com` was observed returning NXDOMAIN.
Use staging5 for the current layout work and keep staging4 as its pre-edit
fallback. Confirm staging copies in Site Tools → WordPress → Staging before
relying on their promotion path. If a copy is absent or points at another
install, stop and verify the selected site before creating or replacing a copy.

Use the SiteGround Staging tool for the remote stage-to-live path. The local
Restic repository and `./scripts/backup-site.sh` cover only this checkout's
local :8090 files and database; they do not back up either remote site. Before
editing or promoting remote content, confirm there is a usable SiteGround backup
for the relevant remote site.

Work on `staging5` and review the rendered pages, media, links, and forms there.
Keep staging excluded from search crawling. Before a live promotion, use
SiteGround's **Custom Deploy** by default and review every selected file and
database table, preserving live-only submissions or other new live data. A
**Full Deploy** replaces the live files and database with staging; use it only
for a specifically authorized release after reviewing that overwrite. SiteGround
documents both modes and its post-deploy live-site restore point in its
[staging deployment guide](https://www.siteground.com/tutorials/staging/deploy)
and [restore guide](https://www.siteground.com/tutorials/staging/restore).

After promotion, check the live homepage and changed routes, media, forms,
canonical URLs, and `https://thaisolitude.com/robots.txt`. Staging returns
`Disallow: /`; live allows crawling and advertises its sitemap. Preserve that
split so a staging setting does not accidentally hide the live site.

A standing request to use staging does not authorize a particular live deploy.
For each promotion, identify the exact release and approve the SiteGround
operation against `thaisolitude.com`; do not promote merely because staging
looks ready. The ignored `deploy/DEPLOY-NOTES.md` and its prepared archive
describe an earlier local migration package, not a verified remote deploy
pipeline.

Staging12 and production are separate SiteGround installs. Never copy
localhost's `siteurl`, `home`, or localhost media URLs directly to a remote
database.

## What's tracked in git vs. not

Only `docker-compose.yml`, `scripts/`, `.gitignore`, and this file are tracked.
Everything else is disposable, secret, or both:

| Path | Why excluded |
|------|-------------|
| `public_html/` | Runtime WordPress tree — restore via `test-restore.sh` or a fresh install, not git |
| `deploy/` | Production DB dump, files tarball, `wp-config.php.production` with real credentials |
| `source-files/` | Raw pull from the live site, including its actual `wp-config.php` |
| `source-db.sql`, `new-masseuse-db-fixed.sql` | Database dumps |
| `backups/` | restic repo + password file |
| `remote_execution.txt`, `sql_manager.txt` | Plaintext SSH/DB credentials for the live site |

**Never commit credentials.** If a new file with SSH keys, DB passwords, or API
tokens appears in this directory, add it to `.gitignore` before it is ever
staged — check `git status` after any broad `git add`.

`inter-model/` and the `thai-solitude-*` snapshot/zips are currently untracked.
They are agent handoff material and point-in-time captures, not site code. Never
deploy `inter-model/`.

## Migration context

This site is mid-migration from `thaisolitude.com` (see `deploy/DEPLOY-NOTES.md`,
not tracked in git). A previous revision of this file documented
`public_html/mystaging01/` as a separate, self-contained WordPress install
nested inside `public_html/` (its own `wp-config.php`, its own database name,
`DB_HOST=127.0.0.1`) left over from the migration pull. That directory is no
longer present in this checkout. If it reappears it is client data: don't delete
it without asking, but don't treat it as part of the practice site either.

The active theme is stock **Astra** (parent theme only) — no child theme yet.
Once custom theme work starts, track it explicitly the way
`willowyhollow-practice` ancestor-unignores
`public_html/wp-content/themes/astra-child/` inside an otherwise-ignored
`public_html/`.

## PHP-parity check

```bash
./scripts/practice-image-parity.sh          # exit 1 on drift or unverifiable input
./scripts/practice-image-parity.sh --warn   # drift only warns; unpinned/unverifiable still fails
```

Compares the PHP version baked into the pinned `wordpress` image against
`thaisolitude.com`'s actual production PHP (`scripts/practice-expected-php.txt`,
each reading dated with its exact refresh command). Read-only, unwired — no
sync script exists yet in this single-tier project to call it from. As of
2026-09-28 both sides are PHP 8.2, closing the earlier 8.3-vs-8.2 drift.

A version-agnostic tag (`wordpress:latest`) is **rejected outright** by this
checker, not assumed correct. If you retag images, use a version-stating tag
and update the pin digest deliberately.

## Non-obvious rules

### Regulated advertising (OBMT) — the big one

- Every advertisement, including the site, **must display the LMT license
  number** (Oregon License LMT-29905) — footer of every page, plus ads,
  listings, email signature (OAR 334-010-0025(9)).
- **Forbidden terms** on this site (rewrite or delete): healing/heal, proof,
  measurable, effective/effectiveness, pain relief/relieve pain, pain
  management, sciatica, headache(s), chronic, adhesions, corrective, root of
  the restriction, accelerate, restore balance, nervous system, circulation,
  anxiety, cancer, neurological, disabilities, prevention, lasting, long-term
  wellness, overall health, real results, studio, our/we/us.
  A search pass must cover **page text, title tags, meta descriptions, OG/
  Twitter fields, and image alt text** — verify by `view-source`, not the editor.
- Massage is not diagnosis (OAR 334-010-0025(2)(c)). Do not write or keep copy
  implying diagnosis, treatment of conditions, or clinical populations.
- Never invent client testimonials or outcome claims. Ryan clarified that Butsaba
  has had clients, just not in Portland. Do not describe her as having no client
  history or imply she has served Portland clients. Check any client-result copy
  against her actual prior work and location.
- Match every research sentence to a real source that supports its exact wording.
  Numbered links render on Therapeutic and Deep Tissue on staging19. The current
  qualified wording fits the linked studies but differs from Ryan's original
  claims. Thai Massage has no research sentence requiring a citation. Show Ryan
  the exact wording, placement, and links before any further website edit.
- "LMT" is the Board's abbreviation for licensed massage therapists
  (OAR 334-010-0025(6)). "Registered Therapist" / "licensed therapist" are wrong
  on this site and are on the replacement list.
- `[ASK]` items in the checklist **block the copy work**: timeline (Chicago vs
  Portland), location, Illinois license, service model (mobile-only?), real
  specialty, phone number, session lengths, photos. Do not guess these — the
  site currently contradicts the résumé.

### Site hygiene gotchas

- **Author identity status on staging19:** display name, nickname, and public
  author slug are `Butsaba`; author archive requests redirect home. The public
  users REST endpoint still enumerates the Butsaba record. Do not change login
  name, login email, or other login information.
- **Duplicate block IDs** across pages: Services, Contact and Deep Tissue share
  `bfe052b1`, `4de6aee5`, `259aae54`; service templates use hand-made
  placeholders (`c1a2b3c4`...). Regenerating avoids CSS collisions.
- **Duplicate images**: `ComfyUI_01044_.webp` is the hero for both Thai and Deep
  Tissue; an OG image is a service image in two places. One unique image per service.
- **The "Coming soon" banner is pasted into every page**, not a reusable block.
  It should become one block/pattern so launch is a single edit.
- **Attachment pages** exist and are indexable
  (e.g. `/services/butsaba_avatar-2/butsaba-avatar/`).
- Leftover code-editor artifacts: an HTML comment block
  (`SERVICE PAGE TEMPLATE - TO ADAPT FOR OTHER SERVICES, SWAP...`) and empty
  `<p>` wrappers on Thai, Therapeutic and Deep Tissue.
- Alt text is frequently the raw hash filename, one caption is a raw URL, and
  AI-generated art is presented as depicting a named real person.

### Page content is serialized

Page builder blocks (Spectra/UAGB `uagb-block-*`, `wp:uagb/faq-child`) are
stored as JSON inside `post_content`. Naive string replacement can corrupt
serialized data — always `--precise --dry-run` a `search-replace` first, and
prefer the reviewed `scripts/credibility-pass` pipeline over ad-hoc edits.

### Voice and scope

The site mixes voices: Home and Services use "we/our" (implying a team or studio
that may not exist), About uses "I", elsewhere "Butsaba/she". Pick one and make
all pages match. "studio" itself is on the removal list; the service model may be
mobile/outcall only. "Work With Me" is a hiring page on a client-facing site and
reads as a client call to action; its place in the nav is an open `[ASK]`/P1 item.

## Content model

- Pages: Home `/`, About `/about/`, Services `/services/`,
  Therapeutic Massage `/therapeutic-massage/`,
  Deep Tissue Massage `/deep-tissue-massage/`, Thai Massage `/thai-massage/`,
  Work With Me `/work-with-me/`, Contact `/contact/`.
- The credibility-pass page IDs are 298, 296, 299, 1091, 1107, 301; Work With Me
  is 1165.
- **`/thai-massage/` is the cleanest page — use it as the template** for the
  others. Its meta description is still broken (banner text cut off mid-sentence).
- Brand: green palette, "Listen. Assess. Adapt." structure on Work With Me.

## Verification habits that matter here

- Audit the **rendered page**, not the editor — title tags, meta descriptions,
  OG/Twitter fields, and `view-source` are where forbidden terms hide.
- Re-fetch the REST API and the author URL after security fixes to confirm the
  email is gone.
- External links to verify: Estelle Medical Academy, Still Light Center, Windy
  City Massage, plus the OBMT license lookup link on Work With Me.
- `[VERIFY]` items need a real source: OBMT license lookup for LMT-29905,
  OAR 334-010-0031 (outcall), the facility-permit question, any cited research.
- `scripts/credibility-pass/verify-local.php` is the read-only content check;
  run it after applying edits.

## Rule: no MU-plugins, no site files — keep changes in WordPress

**Never deliver site design or behavior through `wp-content/mu-plugins/` (or any other plugin/theme file written on
a remote host).** Everything must live in WordPress's own storage so a SiteGround stage-to-live push carries it:

- Styles: Appearance > Customize > Additional CSS (`custom_css` post for the `astra` theme).
- Head/footer snippets: the Insert Headers and Footers option (`ihaf_insert_header` / `ihaf_insert_footer`).
- Theme settings (for example the footer license line): the `astra-settings` option.
- Page content: `post_content` of the page.

Why: on 2026-10-04 the staging14 restyle was deployed with host- and page-guarded MU plugins. A push to live copied
the page text but the guards (`home_url() === staging14`) kept the design inert, so live showed new copy on the old
design. Per-page "scoping" via MU files is not acceptable; scope with CSS selectors in Additional CSS instead.

A push must include the **posts table** (Additional CSS and pages) and the **options table** (header script, Astra
settings). Rehearse any change on localhost (or the isolated restore stack) first, then staging, then live.

## Related conventions in this fleet

Other WordPress projects on this machine (`willowyhollow-practice`,
`pavlomassage-practice`) follow the same shape: Docker local stack, DB dump
before mutations, no build step, handoff docs kept out of the deployed tree.
A project-wide `~/WordPress/scripts` repo documents the cross-site Docker/Podman
port map and sync pipelines. `convmem ask "…"` may already have the answer to a
project-specific question.

## Tooling and the change routine (added 2026-10-16)

Everything below lives in `scripts/work-with-me-employer/` unless noted. Each remote tool reads `remote_execution.txt` (gitignored, mode 600)
plus an ssh-agent holding the deploy key, finds the install by `wp option get home` (never by folder name), and is a **dry run unless you pass
`--apply`**. Every tool names its allowed targets in the script; the SSH account also holds live, so none of this is a licence to touch live:
**a write to live still needs Ryan's explicit go**, and `main` is merged by Ryan.

- **Code gates:** `scripts/check-all.sh [URL]` (PHP lint on the pinned PHP 8.2 image, bash/shellcheck, Python, installer-drift and allow-list test,
  structured-data and headers guard tests; with a URL it also runs the rendered-page verifiers).
- **Before a change:** a fresh DB export that is also copied OFF the server and verified (`sync-server-backups.sh`; copies live in
  `~/WordPress/thai-solitude-server-backups`, outside git). A DB export does not hold plugin/theme files: for those use `mirror-versions.sh`
  (tarball + export) or a SiteGround backup.
- **After a change ("push, then test"):** `post-change-test.sh <target>` (gates, page statuses, structured data and 16 pixel comparisons against the
  reference site, new PHP errors) and `prove-restore.sh <target>` (restores the newest export into a throwaway database and shows exactly which
  fingerprints moved). Both refuse to report a result when nothing was compared.
- **Tools:** `secure-backups.sh` (public WPvivid zips), `structured-data/push-structured-data.sh`, `mirror-versions.sh` (staging31 to live's plugin/theme
  versions), `check-no-new-zips.sh`, `php-compat-scan.sh` (read-only PHP 8.3/8.4 lint), `push-human-facing-staging.sh` (staging26/27).
- **Security headers / CSP:** snippet 1036 "HeaderSecurityProduction" sets the headers; `headers/push-headers-csp.sh` adds a Content-Security-Policy
  sent only to logged-out visitors (staging31 only until Ryan's second go for live; rollback proven). `tools/csp-test.mjs <url> --check` reads the REAL
  server header, reports violations and takes screenshots. Policy keeps `'unsafe-inline'` (Astra/Spectra need it), so it limits other-origin loads,
  objects, base/form hijack and framing, not injected inline script. **A new embed (video, other map or font provider, analytics) is blocked until it
  is added to the policy: re-run `csp-test.mjs --check` after any content change that adds one.** wp-login.php carries only core's own `frame-ancestors 'self'`.
- **Hard-won rules:** a comparison that matched nothing is INVALID, never "identical". A tool run from a site's folder can append to that site's
  `php_errorlog` (the server's `error_log` is a relative name), so disable PHP logging in scans. Under WP-CLI `home_url('/', 'https')` returns the
  site's own scheme; use `set_url_scheme()`. Guard on exact stored bytes (the WPCode snippet is stored with Windows line endings; read raw bytes).
- The "October 5 staging19 task boundary" paragraph near the top is historical; it does not describe the current work.

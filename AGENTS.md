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
2026-10-17 both sides are PHP 8.5.11 (live moved 8.2 -> 8.3 -> 8.5.11 on 2026-10-16; the recorded value and the images were refreshed the next day).
The check compares RECORDED values, so it only stays honest if `thaisolitude_php` is refreshed after any PHP change on live; read the web PHP
from `site-tools-client -j location-phpver list` (a bare `php`/`wp` on the server follows the CLI setting, not necessarily what visitors get).

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

- **Code gates:** `scripts/check-all.sh [URL]` (PHP lint on the pinned PHP 8.5 image, bash/shellcheck, Python, installer-drift and allow-list test,
  structured-data and headers guard tests; with a URL it also runs the rendered-page verifiers).
- **Before a change:** a fresh DB export that is also copied OFF the server and verified (`sync-server-backups.sh`; copies live in
  `~/WordPress/thai-solitude-server-backups`, outside git). A DB export does not hold plugin/theme files: for those use `mirror-versions.sh`
  (tarball + export) or a SiteGround backup.
- **After a change ("push, then test"):** `post-change-test.sh <target>` (gates, page statuses, structured data and 18 pixel comparisons (9 pages x 2 widths, /privacy-policy/ included since 2026-10-17) against the
  reference site, new PHP errors) and `prove-restore.sh <target>` (restores the newest export into a throwaway database and shows exactly which
  fingerprints moved). Both refuse to report a result when nothing was compared.
- **Tools:** `secure-backups.sh` (public WPvivid zips), `structured-data/push-structured-data.sh`, `mirror-versions.sh` (staging31 to live's plugin/theme
  versions), `check-no-new-zips.sh`, `php-compat-scan.sh` (read-only PHP 8.3/8.4 lint), `push-human-facing-staging.sh` (staging26/27).
- **Security headers / CSP:** snippet 1036 "HeaderSecurityProduction" sets the headers; `headers/push-headers-csp.sh` adds a Content-Security-Policy
  sent only to logged-out visitors (staging31 first, then live on Ryan's "go live" 2026-10-17; rollback proven). `tools/csp-test.mjs <url> --check` reads the REAL
  server header, reports violations and takes screenshots. Policy keeps `'unsafe-inline'` (Astra/Spectra need it), so it limits other-origin loads,
  objects, base/form hijack and framing, not injected inline script. **A new embed (video, other map or font provider, analytics) is blocked until it
  is added to the policy: re-run `csp-test.mjs --check` after any content change that adds one.** wp-login.php carries only core's own `frame-ancestors 'self'`.
- **Posture check:** `posture/posture-check.py <url> [-v]` (read-only, public requests only; runs from `check-all.sh <url>`) pins the headers/CSP, exposure
  paths (backups, config, logs, VCS, listings, xmlrpc), install.php, author/user leaks, robots (staging blocked, live crawlable), http->https and cert
  expiry. `--csp skip` for a site cloned before the 2026-10-17 CSP deploy (staging27/30). Its self-test (`posture/tests/selftest.py`, a mock site broken
  one way at a time, mutation-checked) runs in every `check-all.sh`. **SiteGround answers a burst of requests from one IP with HTTP 202 +
  `sg-captcha: challenge`**: the tool reports BLOCKED / INVALID (exit 2), never pass and never "exposed". Wait (tens of minutes) and repeat; do not
  hammer the site while challenged. `posture/edge-check.sh <url>` (one request; exit 2 = challenged) runs first in `check-all.sh <url>` and
  `post-change-test.sh`, so a challenged site gives one clear message instead of a pile of failures.
- **WP-CLI exit codes are unreliable on live under PHP 8.5 (found 2026-10-17):** SiteGround Security 1.6.9 registers a shutdown handler whose `exit;`
  (when `WP_CLI` is defined) overwrites the real exit status with 0 on PHP 8.5, but only where `WP_ENVIRONMENT_TYPE` is not `staging` and
  `sg_security_disable_activity_log` is not 1. On **live** the bare `wp` (wrapper -> PHP 8.5.11) therefore exits 0 for every failure, while staging copies
  look fine. Never trust `wp`'s exit status on live: check OUTPUT and FILES (as `remove-dead-plugins.sh` does), or run with `--skip-plugins=sg-security`
  or `PHP_BIN=php82` (a name under /usr/local/bin, not a path). Installer guards still stop writes, because `WP_CLI::error` ends the PHP script before any write.
  **Our live-capable scripts now define `wp() { command wp --skip-plugins=sg-security "$@"; }` at the top of their remote section** (headers/structured-data pushes,
  secure-backups, mirror-versions, check-no-new-zips) so their exit statuses are true; any NEW script that calls `wp` on the server must do the same.
  `check-wp-exit-codes.sh [live|staging31]` is the read-only canary: it says BUG ACTIVE / NOT PRESENT and whether the workaround holds. Re-run it after any
  SiteGround Security update; when live shows NOT PRESENT the workaround can be retired.
  The local practice stack has the same plugin: `sg_security_disable_activity_log` is set to 1 in its DB (redo after refreshing the local DB from production).
- **Dead-plugin cleanup:** `cleanup/remove-dead-plugins.sh [--apply | --rollback TARBALL]` removes inactive autoptimize 3.1.16, siteground-migrator 2.1.2 and
  autoptimize's leftover `wp-content/autoptimize_404_handler.php` (targets: staging31 and live). Applied and rollback-proven on staging31 2026-10-17; on live
  Ryan removed the same plugins through the WordPress admin the same day (no tool backup; live verified healthy afterwards), so it stays as the reviewed way to
  remove inactive plugins. Backs up first (DB export + tarball of exactly those files + manifest fingerprint, all copied off-server and verified), refuses unless
  each plugin is inactive at the reviewed version, restores itself if its post-removal check fails. The auto-mode classifier blocks an agent from running
  `--apply` (deletion): Ryan runs it with `!`. Still open on live: Site Kit and Starter Templates are inactive (delete later, then clear their leftover
  settings), SureForms leftovers (1 unread entry from 2026-06-08), 11 draft snippets; each removal is asked twice.
- **WPvivid leftovers:** WPvivid was deleted on live 2026-10-17 (staging31 only deactivated; staging27/30 still ACTIVE). Its uninstall leaves `wp-content/wpvividbackups`
  (22 log files, SERVED publicly), `wpvivid_uploads`, `wpvivid_staging`, 5 options and a 1-row table. `cleanup/quarantine-wpvivid-leftovers.sh <live|staging27|staging30|staging31> [--apply | --rollback MANIFEST]`
  MOVES the three folders into `~/private-backups/<target>-wpvivid-leftovers-<ts>/` (sha256 of every file checked, manifest, exact rollback; refuses while the plugin is active or a zip is present).
  Done on live and staging31 2026-10-17 (rollback proven on staging31). `check-no-new-zips.sh` flags any leftover file in the public folder. Quarantined zips + logs sit in the vault until the
  credentials are rotated; deleting them is asked twice.
- **Hard-won rules:** a comparison that matched nothing is INVALID, never "identical". A tool run from a site's folder can append to that site's
  `php_errorlog` (the server's `error_log` is a relative name), so disable PHP logging in scans. Under WP-CLI `home_url('/', 'https')` returns the
  site's own scheme; use `set_url_scheme()`. Guard on exact stored bytes (the WPCode snippet is stored with Windows line endings; read raw bytes).
- **Privacy policy page (2026-10-17):** `privacy/install-privacy-page.php` + `push-privacy-page.sh` (`--apply` / `--rollback FILE`; `S_TARGET=staging31|live`, default staging31) add a very short `/privacy-policy/`
  page (version 1, privacy-policy-v1.html), set it as WordPress's privacy page (`wp_page_for_privacy_policy`) and add "Privacy Policy" to the centered footer row (widget block 7, options table). The copyright/license lines are not touched.
  Written for what the site really does (6 live pages checked: 0 forms, 0 iframes, no cookies, no analytics, only Google Fonts as an outside load): **if a form, booking widget, analytics or a map embed is ever added, update the
  page first.** The installer refuses any forbidden OBMT term and we/our/us. Applied on staging31 (rollback proven there and locally), then on LIVE the same day on Ryan's "publish that. add the slug and go" (live page #1227; `post-change-test.sh live` ALL 5 PASS with 18/18 pixel-identical to staging31;
  `prove-restore.sh live` showed only the expected differences). Live rollback: `S_TARGET=live ./push-privacy-page.sh --rollback '$HOME/private-backups/privacy-page-before-20261017-194902.json'`. Ryan's longer generic template, with review notes, is saved at:
  `docs/inter-model/Thai Solitude - Privacy Policy - Longer Draft (review later).md`.
  **Version 2 (2026-10-17, Ryan: "return to the privacy policy directed at clients ... make that a second section ... an accordion for both online privacy and client based privacy ... slide open like the About page"):**
  `privacy/build-privacy-page.py` builds `privacy-policy.html` (one Spectra FAQ accordion, same block and settings as About's "My Commitment to Your Wellness": first panel open, one open at a time, slide animation; panels "Online privacy" and
  "Client privacy"), `install-privacy-page-v2.php` + `push-privacy-page-v2.sh` move a page that holds EXACTLY version 1 to version 2 (snapshot, read-back, rollback restores version 1; allow-list localhost + staging31 only).
  A Spectra FAQ answer is rendered as `<p>ANSWER</p>` through wp_kses_post, so answers use `<strong>` sub-headings and `<br><br>`, not real lists. Applied on staging31 and localhost (rollback proven on both), then on LIVE the same day on Ryan's "go live" (live page #1227; `post-change-test.sh live` ALL 5 PASS, 18/18 page-views pixel-identical to staging31; `prove-restore.sh live` showed only the expected differences; accordion tested on the live page). Live rollback to version 1: `S_TARGET=live ./push-privacy-page-v2.sh --rollback '$HOME/private-backups/privacy-page-v2-before-20261017-220612.json'`. Ryan ("include what we can from this list while being completely true") had the Client privacy panel cut down to what the site itself says (service pages: tell me about health conditions, medications, pregnancy, injury or surgery before a session; About: goals, tailored sessions; footer: not a substitute for medical care) or what is plainly true; one commitment ("I do not sell the information you share with me or use it for marketing", the same promise live already makes for contact details) is for Butsaba to be comfortable with. Left out until Butsaba confirms them: payment details and processor, how records are kept, retention, service providers, promotional messages, mailing address.
  Fresh sites: run version 1 first, then version 2.
  **Footer menu item: tried and DROPPED (2026-10-17, Ryan: "Drop the footer so we can avoid the side effects").** Ryan first asked for the link in the menu ("last item", "not the main menu", "in the footer"). One menu ("Primary Menu")
  feeds the header, the mobile menu and the footer, so a footer-only item needed a separate "Footer Menu" (5 copied items + Privacy Policy last, only `footer_menu` repointed). It worked on staging31 and localhost (rollback proven) but had side
  effects: items added to the main menu would no longer reach the footer by themselves, the sixth item wrapped to a second line on desktop, and it duplicated the footer-row link. It was never applied on live and was rolled back on staging31 and
  localhost; the scripts were removed (still in git history at commit 596a8f9 if wanted). The privacy link therefore lives only in the centered footer row, on live and staging31. The menus are unchanged.
- **Accessibility (2026-10-17, Ryan: "work on an accessibility page ... use web research tools to confirm anything we missed"):** `tools/a11y-audit.mjs <url>` is the read-only audit (axe-core 4.13.0, WCAG 2.0/2.1/2.2 A+AA rules, 10 pages x 1280/390/320 px, each accordion panel opened,
  plus Tab-through for visible focus and focus hidden behind something, language, titles, headings, landmarks, alt text, zoom, side-scroll, target size; install line at the top of the file). A clean automated run means "no automatically detectable problems", never "conforms".
  W3C WAI guidance (researched): a statement names a standard only if it was really applied, is "not a technical assessment or declaration of conformity", lists known limitations in plain words and is linked from the footer; private businesses have no
  specific legal technical standard (WCAG 2.1/2.2 AA is the benchmark in practice). **Findings on staging32 (a copy of live):** (1) the scroll-to-top button had no visible keyboard focus, (2) on phones the For Practices page's sticky in-page menu (fixed to the bottom, 61 px) hid
  16 of 59 focused items (WCAG 2.2 2.4.11), both FIXED by a marked block in Additional CSS (`accessibility/install-focus-css.php`; 0 of 59 hidden afterwards); (3) axe `nested-interactive`: two links (Windy City Massage, Thai Solitude) sit inside the expandable sections on For Practices,
  whose Spectra FAQ markup is role=tab, NOT fixed (listed as a known limitation on the page; fix = move the two links out of the answers); (4) the Google Map on Contact is Google's content (its focus ring is not ours to style); (5) contrast "needs review" was only the footer row on its gradient,
  measured from pixels at 11.9:1 or better. Also verified: lang, titles, one h1 per page, landmarks, Skip to content link, alt text on every image, zoom allowed, no side-scroll at 320 px, accordions work with Enter and Space.
  **The Accessibility page** (`accessibility/build-accessibility-page.py` -> `accessibility-page.html`, `install-accessibility-page.php`, `push-accessibility.sh <page|css>`) says only what was tested (automated WCAG 2.2 A/AA check + keyboard, contrast, phone and zoom checks, "not a full audit"); no "compliant"
  claims, no "disabilities" (OBMT list). Its footer link is added after Privacy Policy in the same footer row. Applied on staging32 and localhost (rollback proven on both); NOT live. After it is live add `/accessibility/` to `post-change-test.sh` and `capture-pages.mjs`; re-run the audit and
  update the page's date and limitations after any content or layout change. **Privacy page v3 (correction):** the audit found the Contact page embeds a Google Map (inserted by script, missed by the earlier raw-HTML check), so version 2 was wrong that fonts were the only outside load and that Maps was only a link;
  `privacy/install-privacy-page-v3.php` + `push-privacy-page-v3.sh` correct it (v2 -> v3, staging32 only so far; live still shows the wrong v2 wording until Ryan says go or a Custom Deploy carries it).
- **staging32 (fresh copy of live, pulled by Ryan 2026-10-17 after the privacy page v2 went live):** verified read-only the same day: one install, environment type `staging`, robots `Disallow: /`, PHP 8.5.11, same plugins and versions, same page/snippet/menu IDs
  (privacy page 1227, snippets 1225/1226), same row counts, and every page, the theme settings (2046 keys) and the Additional CSS identical once the site address is normalised; `post-change-test.sh staging32` ALL 5 PASS with 18/18 page-views
  pixel-identical to live. Ryan's plan: make changes on staging32, then push staging to live with SiteGround **Custom Deploy** (review every file and table; Full Deploy only for a specifically authorised release). A table deploy
  overwrites whatever changed on live after the copy, so **make no direct live writes while a staging32 change is pending**, and each promotion still needs Ryan's explicit go. The read-only tools (`post-change-test.sh`, `prove-restore.sh`,
  `secure-backups.sh`, `check-wp-exit-codes.sh`, `php-compat-scan.sh`) accept staging32; the write tools (push scripts, installers, mirror-versions, remove-dead-plugins) still allow only staging31/live until they are needed on staging32.
- The "October 5 staging19 task boundary" paragraph near the top is historical; it does not describe the current work.

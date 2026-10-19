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
  **Exception for cited research (Ryan, 2026-10-19: "If something is on my banned words list but evidence says those words should be used, then update my banned words
  to accept it").** The list now has three tiers, shared by `scripts/work-with-me-employer/client-pages/wording_tiers.py` (used by `verify-client-pages.py`; self-tested and
  mutation-checked in `check-all.sh`) and mirrored in `thai/install-thai-evidence.php`. (1) NEVER accepted anywhere: we/our/us, studio, Portland-area/metro, and the claim verbs
  heal, cure, treat, diagnose, guarantee, detox, inflammation. (2) The rest (chronic, anxiety, disability, effective, relief, headache...) is accepted ONLY inside a page's research
  section, i.e. from the heading "What the Research Says About Thai Massage" to the end of the page, where a study's own subject has to be named accurately; the same words stay refused
  everywhere else on the page and in the meta fields. (3) Verbatim paper titles in the Sources panel (`<cite class="ts-source">`) are accepted as published, apart from the tier-1 voice
  words. Without that heading nothing is exempt, and a `<cite>` before it is scanned like any other text. The other installers' own copies of the list are unchanged (Services,
  Therapeutic and Home still get the whole list). The exemption is a lint, not a licence: a sentence must still match its source's abstract; the research section may say who a
  study enrolled but must never read as who she treats, so studies of massage as a treatment for a disease (headache disorders, knee osteoarthritis, cerebral palsy, autism, cancer...)
  are not used (the next rule; OAR 334-010-0025(2)(c) and (4); the footer says she does not diagnose or treat). Prefer plain words for study populations ("shoulder-blade pain",
  not "scapulocostal syndrome") and quote clinical labels only inside verbatim titles.
- Massage is not diagnosis (OAR 334-010-0025(2)(c)). Do not write or keep copy
  implying diagnosis, treatment of conditions, or clinical populations.
- Never invent client testimonials or outcome claims. Ryan clarified that Butsaba
  has had clients, just not in Portland. Do not describe her as having no client
  history or imply she has served Portland clients. Check any client-result copy
  against her actual prior work and location.
- Match every research sentence to a real source that supports its exact wording.
  Numbered links render on Therapeutic and Deep Tissue on staging19. The current
  qualified wording fits the linked studies but differs from Ryan's original
  claims. Thai Massage got its own cited research section on 2026-10-19 (25 numbered
  sources; see the Thai Massage entry in the tooling list below). Show Ryan
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
  plus Tab-through for visible focus and focus hidden behind something, language, titles, headings, landmarks, alt text, zoom, side-scroll, target size; install line at the top of the file). A clean automated run means "no automatically detectable problems", never "conforms". **Measure keyboard focus only after the theme's focus animation has finished** (a reading taken straight after Tab can show a half-animated state, which made the skip link look unstyled); the tool now re-checks any flagged element after waiting and compares pixels with and without focus before reporting it.
  W3C WAI guidance (researched): a statement names a standard only if it was really applied, is "not a technical assessment or declaration of conformity", lists known limitations in plain words and is linked from the footer; private businesses have no
  specific legal technical standard (WCAG 2.1/2.2 AA is the benchmark in practice). **Findings on staging32 (a copy of live), CORRECTED 2026-10-18:** (1) the scroll-to-top button was first reported as having no visible keyboard focus, which was a FALSE ALARM (its focus is a thin dotted white border that the checker ignored; the CSS rule for it is kept as a harmless strengthening); (2) on phones the For Practices page's sticky in-page menu (fixed to the bottom, 61 px) sat over the centre of 16 of 59 focused items (re-confirmed after scrolling settled; WCAG 2.2 2.4.11 only counts items hidden entirely, so a near-miss, and that page is not client-facing), FIXED by a marked block in Additional CSS (`accessibility/install-focus-css.php`; 0 of 59 covered afterwards); (3) axe `nested-interactive`: two links (Windy City Massage, Thai Solitude) sit inside the expandable sections on For Practices,
  whose Spectra FAQ markup is role=tab, NOT fixed (listed as a known limitation on the page; fix = move the two links out of the answers); (4) the Google Map on Contact is Google's content (its focus ring is not ours to style); (5) contrast "needs review" was only the footer row on its gradient,
  measured from pixels at 11.9:1 or better. Also verified: lang, titles, one h1 per page, landmarks, a Skip to content link that is the FIRST Tab stop (before the header: it must stay there, under the header it would defeat its purpose; Enter jumps to #content and the next Tab lands in the page, not the header; when focused it is a clear grey button, 7.2:1 contrast), alt text on every image, zoom allowed, no side-scroll at 320 px, accordions work with Enter and Space.
  **The Accessibility page** (`accessibility/build-accessibility-page.py` -> `accessibility-page.html`, `install-accessibility-page.php`, `push-accessibility.sh <page|css>`) says only what was tested (automated WCAG 2.2 A/AA check + keyboard, contrast, phone and zoom checks, "not a full audit"); no "compliant"
  claims, no "disabilities" (OBMT list). Its footer link is added after Privacy Policy in the same footer row. Applied on staging32 and localhost (rollback proven on both), then on LIVE the same day on Ryan's "push it" (live page #1230 + the CSS block; backups verified off-server; `post-change-test.sh live` ALL 5 PASS with 20/20 page-views
  pixel-identical to staging32; `prove-restore.sh live` only the expected differences; the audit re-run on live matches staging32). Live rollback: `S_TARGET=live ./push-accessibility.sh page --rollback '$HOME/private-backups/accessibility-page-before-20261018-002439.json'` and `... css --rollback '$HOME/private-backups/focus-css-before-20261018-002502.json'`.
  `/accessibility/` is now in `post-change-test.sh` and `capture-pages.mjs` (20 page-views) and the reference for `post-change-test.sh live` is now staging32. Re-run the audit and update the page's date and limitations after any content or layout change. **Privacy page v3 (correction, also live):** the audit found the Contact page embeds a Google Map (inserted by script, missed by the earlier raw-HTML check), so version 2 was wrong that fonts were the only outside load and that Maps was only a link;
  `privacy/install-privacy-page-v3.php` + `push-privacy-page-v3.sh` correct it (v2 -> v3). **Applied on LIVE on Ryan's "fix the privacy correction on live now"** (live page #1227; corrected wording verified in the public HTML; `post-change-test.sh live`: 16/18 page-views identical and the only 2 differences are the privacy page vs staging31's old text, everything else PASS; `prove-restore.sh live` only the expected differences; accordion tested). Live rollback to v2: `S_TARGET=live ./push-privacy-page-v3.sh --rollback '$HOME/private-backups/privacy-page-v3-before-20261018-001411.json'`. staging32 holds the same v3 text, so a later Custom Deploy of the posts table stays consistent with live.
- **"On this page" jump links (2026-10-18, Ryan: "I don't see a skip to content on any page except work with me ... Can you put those under the header of every page. discreetly. Try to keep the number of those at two or three, four max."):** Ryan means the small row in the For Practices hero
  ("Availability . Experience . Credentials . For clients"), NOT the hidden keyboard "Skip to content" link (that one exists on every page, is the first Tab stop and must stay before the header). `accessibility/install-jump-links.php` (+ `push-accessibility.sh jump`) installs a WPCode snippet
  "A11yPageJumpLinks" and a marked Additional CSS block (`ts-a11y-jump`): per page an explicit list of 2-4 links is put at the very top of the page content (directly under the header; on Privacy and Accessibility, which show their own title, just below it) and each target gets an id:
  Home (3), About (3), Services (4: the three services + What to expect), Thai and Deep tissue (3: What it is, Common reasons, What to expect), Therapeutic (4: adds Who can benefit), Contact (3: Contact details, Map, For employers), Privacy (2: Online/Client privacy; a click also opens that panel and scrolls it into view), Accessibility (3).
  For Practices keeps its own row. Links are small and muted (<nav class="ts-jump" aria-label="On this page">, 31 px tall targets, focus ring, no label text, middots on desktop and none on phones so wrapped lines stay clean). A link whose target is not found is left out; fewer than 2 links = no row. To change a page's links edit the `$plans` array in the snippet text inside the installer
  (the heading wording the links look for is in it, so a renamed heading needs the plan updated). Gotchas learned: WPCode's `save()` syntax-checks and then un-slashes the code, so a snippet containing backslashes must be created from plain code and rewritten with `wp_update_post( wp_slash( ... ) )`; **roll back BEFORE editing an installer** (the guard compares against the installer's current text, so an applied older version reads as CHANGED and rollback refuses).
  Applied on localhost and staging32 (rollback proven; every link has a target; Enter and click work; privacy panels open; no duplicate ids; audit on staging32: no new problems), then on LIVE the same day on Ryan's "push it" (live snippet #1232; backup verified off-server; every link and the click/Enter/accordion tests pass on live;
  `post-change-test.sh live` ALL 5 PASS with 20/20 page-views identical to staging32; `prove-restore.sh live` only the expected differences; the audit on live: only the known For Practices item). Live rollback: `S_TARGET=live ./push-accessibility.sh jump --rollback '$HOME/private-backups/jump-links-before-20261018-015819.json'`.
- **Heart links on the Home page (2026-10-18, Ryan: "make these link to the proper style of massage on the services page ... the hearts at: Every session starts with your goals ... There are six hearts ... that change broke the layout though"):** the six hearts
  (Personalized, Relief, Mobility, Balance, Comfort, Relaxation) are a Spectra icon list. `accessibility/install-heart-links.php` (+ `push-accessibility.sh heart`) installs a WPCode snippet "A11yHeartLinks" and a marked Additional CSS block (`ts-a11y-heart`). Ryan's mapping:
  Personalized -> the Services page's "Your Session" section; Relief, Mobility, Balance, Comfort, Relaxation -> "Therapeutic Massage". The snippet turns each item into the link by changing its outer tag from `<div>` to `<a>` and keeping every class and child (aria-label starts with the visible word,
  e.g. "Relief: Therapeutic massage"), and on the Services page gives those two headings the ids `ts-therapeutic-massage` (same id the jump row uses) and `ts-your-session`. No page content is edited; the editor does not show these links. **FIRST VERSION BROKE THE LAYOUT and was rolled back the same day:**
  wrapping the item's contents in an extra `<a>` made the theme's content-sized flex row collapse the labels into vertical strips (I had seen 18x9 px boxes in my own test and a bad screenshot before Ryan did, and still left it up while fixing it: never leave a visibly broken change on staging). Lesson: for Spectra icon lists swap the outer tag instead of wrapping,
  and test the change in the browser first without touching the site (inject the markup and CSS with Playwright and compare item positions). Verified on staging32: 0 of 6 items moved, page height identical, only the theme's thin gold link underline appears under each word, every link lands on the right heading, Enter works, a gold focus ring shows,
  the click area extends 5 px above/below and 10 px beside each item (the audit heuristic still lists them as 21 px tall because it cannot see the pseudo-element), axe shows no violations on Home and Services; rollback restores the original pixel for pixel. Then on LIVE the same day on Ryan's "push it" (live snippet #1234; backup verified off-server; right after the apply the live section was the same size as its baseline with only the thin underline rows different, and the click, Enter and focus-ring tests passed on live; `post-change-test.sh live` ALL 5 PASS with 20/20 page-views identical to staging32; `prove-restore.sh live` only the expected differences; axe on live Home and Services: no violations). Live rollback: `S_TARGET=live ./push-accessibility.sh heart --rollback '$HOME/private-backups/heart-links-before-20261018-022253.json'`.
- **CSP BUG: the Contact page's Google Map was blocked in Firefox and Safari (found 2026-10-18; Ryan: "the google map doesn't showup on the phone now. it's a white spot where the map should be", then "i don't see the map on staging31"):** the map is an `<embed>` that loads
  `https://maps.google.com/maps?...&output=embed` and is redirected to `https://www.google.com/maps/embed?...`. Chrome checks that frame under `frame-src` (allowed), but Firefox (and Safari) check an `<embed>`'s whole redirect chain under `object-src`, which listed only maps.google.com, so they
  blocked it: a blank box. Reproduced in Firefox ("Content-Security-Policy: The page's settings blocked the loading of a resource (object-src) at https://www.google.com/maps/embed..."). **My CSP of 2026-10-17 went live after a Chrome-only check**; every Firefox/Safari visitor lost the map from then on.
  FIX: `headers/install-headers-csp-v2.php` + `push-headers-csp-v2.sh` change one line, `object-src https://maps.google.com` -> `object-src https://maps.google.com https://www.google.com` (guarded: only applies to exactly the version-1 snippet). Verified on staging32: Firefox shows the full map and the policy test across all pages in Firefox
  goes from 4 violations (staging31, old policy) to 0; Chromium still fine. **RULE: run `node tools/csp-test.mjs <url> --check --browser=firefox` as well as the Chrome pass after ANY CSP change or new embed** (the tool now supports `--browser=firefox`; WebKit does not run on this machine). Same lesson as the layout break: test in more than one engine
  before calling a change done. Status: applied on staging32, then on LIVE 2026-10-18 (Ryan ran `S_TARGET=live ./push-headers-csp-v2.sh --apply` himself with `!` after the auto-mode classifier refused an agent-run production deploy; backup `private-backups/live-before-headers-csp-v2-20261018-070721.sql` verified off-server; public header shows the fixed `object-src`; live Contact page in Firefox: 0 policy violations (was 1) and the full map shows on a phone-sized screen; Chromium still fine). Live rollback: `S_TARGET=live ./push-headers-csp-v2.sh --rollback '$HOME/private-backups/headers-csp-v2-before-20261018-070731.json'`. staging31 (a throwaway) still has the old policy.
  **The posture check pins the policy, so it had to move with it:** right after the live apply `post-change-test.sh live` failed its one posture check (27 passed, 1 failed: the CSP was no longer byte-equal to the pinned version-1 string). `posture/posture-check.py` now pins `TESTED_POLICY_V2` (in `headers/tests/csp-snippet-test.php`), and that offline test now also proves the version-2 installer turns the version-1 snippet into exactly that policy with every other header unchanged
  (a tampered pin fails 4 tests; checked). A site still on version 1 (staging31) now fails with a message naming the Firefox/Safari map bug. Live posture check after: 28 passed, 0 failed. LESSON: a change to something the posture check pins needs the pin updated in the same change, and the full battery run after it, not just the page checks.
- **Thammasat University link on Work With Me (2026-10-18, Ryan: "add a link to Thammasat University on the work-with-me page. https://tu.ac.th/en/"):** the name appears once on the page, in the credentials list ("BA in Liberal Arts (English / Communication Arts) — Thammasat University");
  `links/install-thammasat-link.php` + `links/push-thammasat-link.sh` (`--apply` / `--rollback FILE`; `S_TARGET=staging32`, live added only on his go) make those two words a plain link to `https://tu.ac.th/en/`, like the Still Light Center and American Heart Association links beside it (no new-window attribute; same
  theme underline and colour, measured). One exact replacement inside the stored page content: ready only if the old line occurs exactly once and the name nowhere else, so edits elsewhere on the page survive and a rollback keeps them. `page-content.html` is the historic staging26 input and is NOT kept in step with later edits.
  Verified on localhost and staging32 (apply, read-back, rollback byte-identical, re-apply; DB export copied off-server and verified each time). Status: applied on staging32, then on LIVE 2026-10-18 on Ryan's "push it" (he ran `S_TARGET=live ./push-thammasat-link.sh --apply` himself with `!`: the auto-mode classifier refuses an agent-run production deploy, so hand him the command; backup `private-backups/live-before-thammasat-link-20261018-071043.sql` verified off-server; the public page shows exactly one link). `post-change-test.sh live`: pages, structured data, 20/20 page-views pixel-identical to staging32 (staging32 carries the same link and CSP fix), no PHP errors; `prove-restore.sh live`: only the expected differences (the work-with-me page, the new revision row, SiteGround's own log table). Live rollback: `S_TARGET=live ./push-thammasat-link.sh --rollback '$HOME/private-backups/thammasat-link-before-20261018-071052.json'`.
- **staging32 vs live parity (checked 2026-10-18, before Ryan refreshes staging from live):** a read-only fingerprint of both sites with each site's own address normalised out (54 lines: every page, snippet, the Additional CSS, widgets, menus, the theme settings and the key options) is IDENTICAL, and the SEO schema/social values on Home, About and Contact are identical when printed in full. Only generated caches differ (Spectra `_uag_page_assets`/`_uag_css_file_name`, SureRank `surerank_seo_checks*`, and hashes of PHP-serialized values, whose stored string lengths change with the longer staging address). **Compare actual values, not hashes of serialized meta.**
  So nothing exists only on staging32 and refreshing it loses nothing. `prove-restore.sh <staging> <live export>` is NOT a staging-vs-live comparison: it normalises only the target's own address, so it flags almost every page.
- **staging32 (fresh copy of live, pulled by Ryan 2026-10-17 after the privacy page v2 went live):** verified read-only the same day: one install, environment type `staging`, robots `Disallow: /`, PHP 8.5.11, same plugins and versions, same page/snippet/menu IDs
  (privacy page 1227, snippets 1225/1226), same row counts, and every page, the theme settings (2046 keys) and the Additional CSS identical once the site address is normalised; `post-change-test.sh staging32` ALL 5 PASS with 18/18 page-views
  pixel-identical to live. As of 2026-10-18 UTC staging32 and live carry the same privacy v3, Accessibility page, footer link and focus CSS (applied to live with my guarded installers, not a deploy). Ryan's plan for further changes: make them on staging32, then push staging to live with SiteGround **Custom Deploy** (review every file and table; Full Deploy only for a specifically authorised release). A table deploy
  overwrites whatever changed on live after the copy, so **make no direct live writes while a staging32 change is pending**, and each promotion still needs Ryan's explicit go. The read-only tools (`post-change-test.sh`, `prove-restore.sh`,
  `secure-backups.sh`, `check-wp-exit-codes.sh`, `php-compat-scan.sh`) accept staging32; the write tools (push scripts, installers, mirror-versions, remove-dead-plugins) still allow only staging31/live until they are needed on staging32.
- **staging33 is the working copy from 2026-10-18 (Ryan: "From now we have staging33 to work on"; a fresh copy of live pulled after the map fix and the Thammasat link went live; staging32 is the previous one and is no longer the reference).** Verified read-only the same day: install at `www/staging33.thaisolitude.com/public_html`, environment type `staging` (live: `production`), PHP 8.5.11, WordPress 7.1.3, 10 pages, 5 active plugins (same as live); the 54-line content fingerprint (every page, snippet, Additional CSS, widgets, menus, theme settings, key options) and the SEO schema/social values are IDENTICAL to live;
  `posture-check.py` 28 passed, 0 failed (robots blocked, headers, the version-2 CSP); `post-change-test.sh staging33` ALL 5 PASS with 20/20 page-views pixel-identical to live. The read-only tools (`post-change-test.sh`, `prove-restore.sh`, `secure-backups.sh`, `check-wp-exit-codes.sh`, `php-compat-scan.sh`) now accept staging33, and **`post-change-test.sh live` now uses staging33 as its reference**. The already-applied installers and push scripts (privacy v3, accessibility, jump/heart links, CSP v2, Thammasat link) were NOT extended: they are finished; a NEW installer for staging33 lists `localhost:8090`, `staging33.thaisolitude.com` and, only after Ryan's go, `thaisolitude.com`.
  Same rules as before: change on staging33 first, a write to live needs his explicit go and he runs the live `--apply` himself (agent-run production deploys are refused), no direct live writes while a staging33 change is pending if he plans a Custom Deploy, verified off-server backup before every write. **Parity check recipe (read-only, compare actual values):** fingerprint both sites over SSH with each site's own address normalised (pages, snippets, CSS, widgets, menus, options), then diff; hashes of serialized meta differ with the host length and are not meaningful. The scratch script lives in `~/.cache/wwm-scratch/fp/` (not in the repo).
- **Dark / light mode button in the header (2026-10-18, Ryan: "would you test a dark/light reader button in the header. the latest staging is fine for that."): TEST on staging33 only, not on live.** `theme/install-theme-toggle.php` + `push-theme-toggle.sh` (`--apply` / `--rollback FILE`; `S_TARGET=staging33`; live only on his go and his own `!` run) create the WPCode snippet "ThemeToggle" (staging33 snippet #1237) and a marked Additional CSS block `ts-theme`; sources are `theme/theme-init.js`, `theme-toggle.js`, `theme-toggle.css` (no page content, no theme setting, no MU-plugin).
  How it works: a one-line script in `<head>` sets `data-ts-theme="dark"` on `<html>` before first paint when the visitor chose dark (no flash); a script hooked on Astra's `astra_header_after` adds a 44 px round toggle button (moon/sun icon, `aria-label="Dark mode"`, `aria-pressed`), one in the desktop header after "Get in Touch" and one in the phone header before the hamburger (the hidden one is skipped by keyboards); the choice is kept in `localStorage["ts-theme"]`, other tabs follow, and **the DEFAULT STAYS LIGHT** (the OS dark setting does NOT switch the site, so nobody sees a change unless they press it; following the OS would be a one-line change in `theme-init.js`, Ryan's call).
  The dark palette only overrides the site's own variables (`--ts-*`) and Astra's global colours under `html[data-ts-theme="dark"]` inside `@media screen` (light mode and printing are unaffected), plus explicit rules where a token is used in the opposite role: the footer text uses `--ts-ground` as its LIGHT colour (overriding it blindly made the footer link row invisible), the scroll-to-top button uses `--ts-ink` as its background, the Work With Me phone bar had a hard-coded `#fff`, and button text on the green must be dark. Do NOT remap `--ast-global-color-5` (Astra's "white", used as light text on dark surfaces). The Google Map stays bright (Google's content).
  Prototype first in a browser (inject CSS/JS with Playwright, no site write), found and fixed those four defects before anything was installed. Verified: localhost apply/rollback/re-apply; staging33 apply (DB export copied off-server, verified); `tools/theme-test.mjs <url> [--contrast]` (22 behaviour checks: light default, button >= 44 px, click, aria-pressed, survives reload and another page, set before first paint, keyboard Enter/Space, print stays light, no console errors) passes in Chromium AND Firefox (`BROWSER=firefox`), and axe color-contrast (WCAG AA) has 0 failures in light AND dark on all 20 page-views; light mode is pixel-identical below the header on all 20 page-views (only the header strip gained the button). With a real Dark Reader (throwaway Chromium, `scripts/restyle/tools/darkreader-flash/env.sh`, `TS_FLASH_WORK=~/.cache/wwm-scratch/dr`) the button is visible in both states and our dark mode is not inverted back; Dark Reader still re-tints it slightly (bg #11161E instead of #151C26), so judge the true palette with Dark Reader off for the site.
  Staging33 rollback: `S_TARGET=staging33 ./push-theme-toggle.sh --rollback '$HOME/private-backups/theme-toggle-before-20261018-222712.json'`.
  **Prepared for live on Ryan's "add that and i'll push" (2026-10-18):** (1) `theme/install-theme-toggle.php` and `push-theme-toggle.sh` now allow live. (2) The Accessibility page is updated to VERSION 2 (`accessibility/build-accessibility-page.py` -> `accessibility-page.html`; `accessibility-page-v1.html` is the exact old text; `install-accessibility-page-v2.php` + `push-accessibility-page-v2.sh`, `S_TARGET=staging33|live`): one new paragraph (a header button switches between light and dark colors, the choice is saved in the browser, it works with a keyboard, text contrast and keyboard focus were checked in both versions), "it keeps its own light colors in dark mode" added to the map limitation, date October 18, 2026; still no conformance claim. Every sentence rests on what was run: `theme-test.mjs --contrast` (22 behaviour checks in Chromium and Firefox; axe contrast 0 failures light and dark, 20 page-views) and the full `a11y-audit.mjs` on staging33 in light and with `TS_THEME=dark` (10 pages x 3 sizes: only the known For Practices nested-interactive item, 0 elements without visible focus, 0 covered).
  The page installer REFUSES unless the published snippet "ThemeToggle" exists on that site (proved on localhost: preflight and apply both refuse, nothing changed), so ORDER ON LIVE: button first, then the page. Both applied and rollback-proven on localhost and staging33 (staging33 page rollback: `S_TARGET=staging33 ./push-accessibility-page-v2.sh --rollback '$HOME/private-backups/accessibility-page-v2-before-20261018-224348.json'`). Live dry runs: button `state=ready`, page `state=old` (refuses until the button is there). LIVE (Ryan ran both applies on 2026-10-18/19; verified 2026-10-19: `theme-test.mjs https://thaisolitude.com --contrast` ALL PASSED in Chromium (22 behaviour checks, axe contrast 0 failures in light AND dark on 20 page-views), `BROWSER=firefox` behaviour ALL PASSED, and `post-change-test.sh live` was 20/20 identical to staging33). Original plan: Ryan runs the live applies himself (`! cd ... && SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock S_TARGET=live ./push-theme-toggle.sh --apply`, then `./push-accessibility-page-v2.sh --apply`); afterwards `post-change-test.sh live` (staging33 already carries both, so expect 20/20 identical), `tools/theme-test.mjs https://thaisolitude.com --contrast`, `prove-restore.sh live`.
- **Therapeutic Massage copy (2026-10-18, a handoff Ryan pasted: "Improve Thai Solitude's Therapeutic Massage Page", then "do this on staging33"): LIVE since 2026-10-19 00:29 UTC (Ryan ran the apply).** `therapeutic/install-therapeutic-copy.php` + `push-therapeutic-copy.sh` (`--apply` / `--rollback FILE`; `S_TARGET=staging33` is the only target; allow-list localhost:8090 + staging33) make three exact replacements inside page 1107's stored content, each anchored to its heading/question so it cannot hit another place: (1) the "Common Reasons Clients Choose Therapeutic Massage" paragraph (neck/shoulder research with its limits, low-back evidence "limited", both research sentences carrying the page's own numbered marker **[11]** = NCCIH "Massage Therapy: What You Need To Know", the next number after the page's [1]-[10]; verified the article says neck/shoulder "may be helpful ... benefits may only last for a short time" and low-back "only weak evidence"); (2) the "no specific complaint" FAQ answer; (3) one added sentence in the "Therapeutic vs Relaxation" FAQ answer ("It does not have to mean intense or painful pressure."). Guard: all three old texts exactly once (ready) or all three new texts once with exactly two NCCIH links (applied), anything else stops. Verified: localhost apply/idempotent/rollback byte-identical/re-apply, host refusal (live, staging31, staging32), half-applied page refused; staging33 apply with verified off-server export (`staging33-before-therapeutic-copy-20261018-232931.sql`), public check 13/13, rollback proven (content hash identical to the pre-change read), re-applied; rendered check `therapeutic/check-therapeutic-page.mjs <url> <outdir> [label]` (desktop/phone/320: no side-scroll, no duplicate headings, text fits, NCCIH answers 200, safety sentences intact); `post-change-test.sh staging33`: pages 200, structured data identical, **18/20 page-views pixel-identical to live, the 2 that differ are the Therapeutic page itself (+136 px desktop, +190 px phone)**, no PHP errors; `prove-restore.sh staging33`: only page therapeutic-massage and the new revision rows moved. **WORDING DECIDED (Ryan: "choose a new thought to express relief, then"):** the pasted sentence said "may provide short-term relief for some people with neck or shoulder pain", and `client-pages/verify-client-pages.py` bans the bare word "relief" (also "relieve", "restore"), so the first apply failed that one gate. It now reads "Research suggests massage may be helpful for some people with neck or shoulder pain, though the benefits may last only a short time." (NCCIH's own phrasing); the installer's wording check now mirrors the gate (a mutation test proves it refuses the old sentence). Redone: rolled back first (hash identical to the original), localhost rehearsal WITH the gate (passes), staging33 applied (export `staging33-before-therapeutic-copy-20261018-235345.sql` verified off-server), `verify-client-pages.py` on staging33 passes, `post-change-test.sh staging33`: ALL GATES PASSED, pages 200, structured data identical, 18/20 page-views identical (the 2 differences are this page, until it is on live), no PHP errors; axe WCAG A/AA 0 violations in Chromium AND Firefox at 1280/390/320; rollback proven again (original hash) and re-applied (same final hash); `prove-restore.sh`: only the page and its revision rows. **Readiness for live (read-only check, 2026-10-18): live and staging33 differ ONLY in this page** (Ryan had already run both dark-mode live applies); live holds all three old texts exactly once (installer would find it `ready`); snippet IDs identical on both. **COMPLIANCE CHECK: PASSED (2026-10-18, Ryan: "give it a compliance check. if it passes go live.").** Checked against the Board's own rule text (OAR chapter 334, July 2024 and April 2025 editions, the relevant rules word-for-word identical; ORS 687.011; the Board's FAQ page has no advertising guidance). The rules contain NO banned-word list; the operative tests are OAR 334-040-0010(7) (discipline for "false, deceptive, or misleading advertising"), 334-030-0005(1)(d) (represent services "honestly and accurately"), 334-010-0025(2)(c) (massage does not include "making a medical diagnosis"), (4) (no services other than massage), (9) (licence number in ALL advertisements) and ORS 687.011(4) (massage is for "maintaining good health and establishing and maintaining good physical condition"). Result per sentence: the reasons are descriptive; the neck/shoulder research sentence is hedged ("suggests", "may", "some people", "benefits may last only a short time"), sourced to NIH/NCCIH and does not promise an outcome, claim superiority or diagnose; the low-back sentence says the evidence is limited (NCCIH: "only weak evidence"); the FAQ answers make no health claim; the licence number stays in the footer (public check). The site's own banned-word list is stricter than the rules (a house standard). Limits: this is a check against the published rules, not legal advice or a Board ruling, and the Board decides case by case what is "misleading"; the sentences describing Butsaba's intake ("I ask about your preferences and anything I should know about your health") must stay true to what she does. **LIVE (2026-10-19 00:29 UTC): Ryan gave the go ("if it passes go live"). The auto-mode classifier REFUSED my edit adding the live host to the installer allow-list ("Production Deploy"), so I did not enable live myself: I built a tested patch (`git apply --check` clean; patched installer accepts live and staging33, refuses staging31/32 and unknown hosts), Ryan applied it with `!` (commit c1efef0 records it), ran the dry run (`state=ready`) and then `S_TARGET=live ./push-therapeutic-copy.sh --apply`.** Live backup `private-backups/live-before-therapeutic-copy-20261019-002856.sql` verified off-server first; public check 14/14. Verified on live afterwards: `post-change-test.sh live` ALL 5 PASS (all gates incl. verify-client-pages and posture, pages 200, structured data identical, **20/20 page-views pixel-identical to staging33**, no PHP errors); `prove-restore.sh live`: only page therapeutic-massage, the new revision rows and SiteGround's own log table moved; the page in Chromium AND Firefox at 1280/390/320: axe WCAG A/AA 0 violations, no script errors, both NCCIH links present. Live rollback: `S_TARGET=live ./push-therapeutic-copy.sh --rollback '$HOME/private-backups/therapeutic-copy-before-20261019-002906.json'`. staging33 and live now carry the same content. **Because staging33 is `post-change-test.sh live`'s reference and carries this change, expect exactly 2 differing page-views (the Therapeutic page) on live until it is pushed.** Staging33 rollback: `S_TARGET=staging33 ./push-therapeutic-copy.sh --rollback '$HOME/private-backups/therapeutic-copy-before-20261018-235354.json'`. LESSON: run the rendered-page gates (`client-pages/verify-client-pages.py`) on the localhost rehearsal BEFORE writing to staging, not only after.
- **Thai Massage page: heritage line, generic FAQ and cited research section (2026-10-19; Ryan, after `docs/inter-model/thai-massage-page-enhancement-plan.md`: "add all the evidence you can ... No new images ... Keep the FAQ generic enough we can finish it later. She uses a mat. Lengths 60 and 90 min. She doesn't have plans to travel to clients"): LIVE since 2026-10-19 04:18 UTC (Ryan: "push it live"; he ran both applies himself); also on staging33 (03:32 UTC) and rehearsed on localhost.** `thai/build-thai-evidence.py` generates `thai/thai-evidence-content.php` (never hand-edit: `check-all.sh` fails on a stale file); `thai/install-thai-evidence.php` + `push-thai-evidence.sh` (`--apply` / `--rollback FILE`; `S_TARGET=staging33|live`; allow-list localhost:8090 + staging33 + live: live was added by a tested patch (`docs/inter-model/thai-enable-live.patch`) that Ryan applied himself, commit f75e171, because the auto-mode classifier refuses an agent adding a live host) change page 1160 (/thai-massage/): (1) one paragraph appended to "What Is Thai Massage?" (UNESCO 2019 Nuad Thai listing, source [1]; "I trained in traditional Thai bodywork at Still Light Center", already on About); (2) two sections APPENDED after the last block, so nothing above moves: "Questions About Thai Massage" (Spectra FAQ like Therapeutic's, 7 questions, **answers carry no links**: Spectra wraps question and answer in `role="tab"`, so a link inside is axe `nested-interactive`) and "What the Research Says About Thai Massage" (intro plus five native `core/details` panels: Reviews and limits (first, open), Back Pain, Neck/Shoulders/Arms/Feet, Stress/Energy/Recovery, Sources; **25 numbered sources** in reading order from a registry in the builder, so markers and list cannot drift; each source's abstract was read on Europe PMC/Cochrane/NCCIH/UNESCO and all 20 PubMed IDs were re-checked against their registry titles). FAQ facts: mat, 60 or 90 minutes, no travel to clients (Ryan), "Sessions take place in Mt. Tabor, Portland" and the "no, you do not need to be flexible" and "I will reply to arrange a time" answers only restate the page/Contact (**ask Butsaba to confirm those three**; prices were left out on purpose). Panel look = the FAQ card made from block attributes and the site's `--ts-panel` / `--ts-rule` tokens (they flip in dark mode), so **no Additional CSS and no new colours**. **Evidence used, and not used:** every Thai-massage trial or review of massage-scope outcomes (back, neck, shoulder, arm, foot pain; stress, energy, recovery) including the unfavourable and mixed results (ultrasound beat Thai massage for heel pain; rest lowered stress as much as massage; adding an herbal compress or acupuncture changed nothing; the Juntakarn 2017 trial had no untreated group; the 2024 Cochrane neck review found little or no difference against placebo massage). NOT used: studies of Thai massage as a treatment for a disease (headache disorders, knee osteoarthritis vs ibuprofen, cerebral palsy, autism, stroke, cancer, urinary symptoms, constipation, bone density, immunity: they would read as promising to treat illness; the Therapeutic handoff also removed its headache FAQ) and Yoopat 2015 (its abstract does not say what was compared). **Ryan can override by saying so; adding them means adding sources to the builder.** Wording: the three tiers in the Forbidden-terms bullet above; clinical labels are kept out of the prose. **Verified (staging33, final state):** localhost apply/idempotent/rollback byte-identical/re-apply, real editor opens the page with 0 invalid blocks (`editor-validate.mjs`: 84 blocks), `check-all.sh https://staging33...` ALL GATES PASSED (incl. the new Thai checks in `verify-client-pages.py`, 45 checks locally), `thai/check-thai-page.mjs <url> <outdir> [label]` (7 FAQ questions once each, 5 panels first-open, 25 numbered markers 1..N, click/Enter/Space, no side-scroll at 1280/390/320, light and dark) passes in Chromium AND Firefox, `TS_PAGES=/thai-massage/ a11y-audit.mjs` (now audits native panels open) 0 axe violations light and dark, own contrast measurement of every text element in the new sections (133 per theme) lowest 9.13:1 light / 11.36:1 dark, `post-change-test.sh staging33`: pages 200, structured data identical, **18/20 page-views identical, the 2 that differ are the Thai page (desktop +1855 px, phone +2795 px)**, no PHP errors; `prove-restore.sh staging33`: only page thai-massage and row counts moved; staging33 rollback proven byte-identical TWICE (the second cycle followed a wording edit). Export before the final apply: `staging33-before-thai-evidence-20261019-033247.sql` (verified off-server). **Staging33 rollback: `S_TARGET=staging33 ./push-thai-evidence.sh --rollback '$HOME/private-backups/thai-evidence-before-20261019-033257.json'`.** **Meta title and description (Ryan, 2026-10-19: "I like yours. Go with that meta."; on staging33 04:08 UTC, LIVE 04:18 UTC):** `thai/install-thai-meta.php` + `push-thai-meta.sh` (`--apply` / `--rollback FILE`; allow-list localhost + staging33 + live) set SureRank's page title `Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT` (54 chars) and description `Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.` (150 chars) through `\SureRank\Inc\API\Post::update_post_meta_common` (the Services precedent); the og and twitter fields follow. The page had none (SureRank had auto-built a description from the first words of the page text, cut off mid-sentence, heading text included). Guard: ready = the page has no title/description of its own; applied = exactly these two; anything else (someone set a different one) stops (proved on localhost by planting a different description: preflight and apply both refuse). Rollback is EXACT: the snapshot holds every `surerank*` row the page had (raw values) and rollback puts exactly those back (proved: the rendered tags returned to the old ones). `verify-client-pages.py` checks it strictly where it is in (title, description, og/twitter follow, <= 160 and <= 60, a whole sentence) and prints an INFO line where it is not. Verified on staging33: public check 10/10, `post-change-test.sh staging33` (same 18/20, the 2 differences are the research section; the meta adds no pixels), `prove-restore.sh staging33`: only `seo 1160:surerank_settings_general` and the postmeta row count moved (the page content fingerprint did not). Export `staging33-before-thai-meta-20261019-040843.sql` (verified off-server). **Rollback: `S_TARGET=staging33 ./push-thai-meta.sh --rollback '$HOME/private-backups/thai-meta-before-20261019-040854.json'`.** **LIVE (2026-10-19 04:18 UTC, Ryan: "push it live"):** before preparing anything I read live's Thai page read-only (page 1160 published, WP 7.1.3 production; content equal to staging33's pre-change content modulo host; anchor once; none of the new block ids anywhere; no SEO title/description of its own; saver and blocks available). I built the enable-live patch in scratch copies (allow-lists, target switch, comments only; `git apply --check` clean; all offline gates pass on the patched tree; patched installers accept live and staging33 and refuse staging31/32/strangers, unpatched refuse live: tested on localhost with a faked site address), Ryan applied it (commit f75e171) and ran both applies: evidence section (export `live-before-thai-evidence-20261019-041822.sql`, public check 13/13), then meta (export `live-before-thai-meta-20261019-041848.sql`, public check 10/10), each verified off-server first. **Verified on live afterwards:** `post-change-test.sh live` ALL 5 PASS (gates incl. the strict Thai checks and posture, pages 200, structured data identical (dateModified set aside), **20/20 page-views pixel-identical to staging33**, no PHP errors); `prove-restore.sh live` against the pre-change export: only page thai-massage, its SEO row, revision/postmeta row counts and SiteGround's own log table moved; `check-thai-page.mjs` passes in Chromium AND Firefox (light and dark, 1280/390/320, click/Enter/Space, no script errors, so no CSP violations); `TS_PAGES=/thai-massage/ a11y-audit.mjs` 0 axe violations light and dark, 41 tab stops, no focus problems; own contrast measurement lowest 9.13:1 light / 11.36:1 dark; public head shows the approved title and description with `index, follow` and the canonical unchanged. **Live rollback (not exercised on live; proven byte-identical on staging33 and localhost):** `S_TARGET=live ./push-thai-evidence.sh --rollback '$HOME/private-backups/thai-evidence-before-20261019-041832.json'` and `S_TARGET=live ./push-thai-meta.sh --rollback '$HOME/private-backups/thai-meta-before-20261019-041858.json'` (roll back the meta first only if you want the research section kept; the two are independent). staging33 and live now carry the same content again. **NEXT, NOT STARTED (Ryan, 2026-10-19: "your work for this page was high quality and eventually i'll want to emulate it on the other services pages"; then "not now"):** copy this treatment to Therapeutic and Deep Tissue (maybe Services) when he asks. Order: (1) their cut-off auto-built meta descriptions (same `install-thai-meta.php` pattern); (2) decide the Therapeutic neck/shoulder sentence against the Feb 2024 Cochrane review; (3) Therapeutic, then Deep Tissue. Generalise first: `client-pages/wording_tiers.py` `RESEARCH_HEADING` is hard-coded to the Thai heading (use a page-neutral marker), the installers' anchors/tails/block ids, the push scripts' public-check strings and the Thai section of `verify-client-pages.py`. Per page: re-read every abstract (Europe PMC API; PubMed blocks curl), get FAQ facts from Butsaba, and merge source numbering (Therapeutic already has [1]-[11], Deep Tissue [1]-[4]). Do not start without his go. **Open:** the Therapeutic and Deep Tissue pages still carry auto-built descriptions too (same fix is a follow-on, Ryan's call); the page's three images are one illustration in three tints, shared with Deep Tissue, and show a client on a massage table while the FAQ now says "a mat" (Ryan: no new images, so this is a known mismatch for him to decide); the Therapeutic page's neck/shoulder sentence vs the Feb 2024 Cochrane review (flagged, unchanged); the Accessibility page date/limitations if this goes live (the audit now covers the panels). **Gotchas learned:** roll back BEFORE editing the builder (an applied page no longer matches the changed text; I had to restore localhost from the snapshot by hand once); the stored page ends with ONE newline (the first tail anchor assumed two and the guard correctly refused); `uagb/advanced-heading` is a static block (not registered server-side, so do not require it in a registry check); WordPress keeps a revision of each apply, so the "block id already used" check must skip revisions; the local backup folder must be world-writable for the WP-CLI container; `post-change-test.sh` step 3 compared `dateModified`, which any edit changes, so `tools/jsonld-compare.py` now sets ONLY that field aside and says so on an INFO line (other differences still fail; checked).
- **Phone header wrapped "Thai Solitude" and split the word (2026-10-19; Ryan: "something you did on staging33 caused the header to wrap text on phones by breaking the word into pieces ... ideally the text should be small enough to fit on an iphone SE without wrapping"; then "it was probably the dark light button and i didn't notice till now"): FIXED and LIVE since 2026-10-19 05:36 UTC (Ryan, after looking at staging34 on his phone: "that fix worked. you can push it live"; he applied my enable-live patch, commit 3cffd03, and ran the live apply himself); also on staging34.** Cause (measured): the dark/light button I added to the phone header (`theme/`, live since 2026-10-18/19) added 44 px plus ~28 px of padding and margin to the right column of a two-column header grid with a 20 px gap, leaving 189 px at 375 px for a logo and title that need 196; `theme/header-fit-test.mjs` fails live at EVERY width of 380 px and below (iPhone SE 375: two lines; common Android 360: two lines; 320: three lines and "Solitu / de"), passes from 385 px up, and passes at all widths with the button hidden, in Chromium AND Firefox. Fix: `theme/header-fit.css`, ONE marked block `ts-header-fit` appended to Additional CSS after `ts-theme` (phones only: at <= 389 px column gap 8 px, button margin 8 px, no extra padding around the buttons, `white-space: nowrap` on the name; below 350 px the name shrinks smoothly via `calc((100vw - 222px) / 5.4)`, which equals the current 23.712 px at exactly 350 px so there is no jump; from 390 px up nothing changes; tap targets kept: button 44 px, menu 46 px, 8 px between them). Tooling: `theme/install-header-fit.php` + `push-header-fit.sh` (`--apply` / `--rollback FILE`; allow-list localhost:8090 + staging34 + live; the guard refuses unless the known site CSS AND the ts-theme block are present, and the block must be the reviewed single marked block with no url(), @import or !important); `theme/header-fit-test.mjs <url> [css]` (17 widths 300-900 px x light and dark: name on one line, no word split, no overlap, tap targets, >= 8 px between buttons, no side-scroll; `BROWSER=firefox` for Firefox; pass the CSS file to PROTOTYPE without touching a site); `theme/header-fit-layout-test.mjs <url> <css>` (every element below the header keeps its position and size relative to the header's bottom edge on all 10 pages at 375 and 320 px: 2,888 comparisons, 0 change; mutation-checked: a stray margin rule is caught; fixed bars and what sits above the header are excluded). **Verified:** localhost apply/idempotent/rollback exact (46,887 bytes back)/re-apply; staging34 (a fresh copy of live pulled 2026-10-19) layout proof BEFORE applying, then apply with verified export `staging34-before-header-fit-20261019-050640.sql`, public check 5/5, width test passes in Chromium AND Firefox on the applied page, button behaviour test ALL PASSED, `post-change-test.sh staging34` ALL 5 PASS (20/20 identical to live: the fix acts only at <= 389 px and those captures are 390 and 1280), `prove-restore.sh staging34`: only css 29 and the posts row count moved, axe 0 violations on Home. **Staging34 rollback: `S_TARGET=staging34 ./push-header-fit.sh --rollback '$HOME/private-backups/header-fit-before-20261019-050651.json'`.** NOT tested: WebKit (iOS Safari does not run on this machine); the 6 px safety margin in the formula and `nowrap` (an over-wide title would overlap rather than wrap) are what cover a slightly different Safari text width, so Ryan should look at a real iPhone SE after it goes live. LESSON: a change that adds an element to a shared header must be width-tested down to 320 px in light and dark before it ships (the dark-mode work verified 20 page-views at 390 and 1280 only, so a 380 px failure was invisible). `post-change-test.sh`, `prove-restore.sh`, `php-compat-scan.sh` and `check-wp-exit-codes.sh` now accept staging34. **LIVE (2026-10-19 05:36 UTC):** before preparing the patch I read live read-only: Additional CSS 46,887 bytes, equal to staging34's pre-change CSS modulo host, `ts-theme` block once, no `ts-header-fit` yet, ThemeToggle snippet 1237 published, env production. The patch (allow-list, target switch, comments only; `git apply --check` clean; all offline gates pass patched; patched installer accepts live and staging34 and refuses staging33/32/strangers, unpatched refuses live: tested with a faked site address on localhost) is `docs/inter-model/header-fit-enable-live.patch`. Live export `live-before-header-fit-20261019-053547.sql` (verified off-server), public check 5/5. **Verified on live afterwards:** `header-fit-test.mjs https://thaisolitude.com` passes at all 17 widths in light and dark in Chromium AND Firefox on the real served page (320 px: title 18.1 px, one line, `header-h` back to 85 px; 360 and 375 px: 23.7 px, one line), `theme-test.mjs` ALL CHECKS PASSED, `post-change-test.sh live` ALL 5 PASS (**20/20 identical to staging33**: the fix acts only at <= 389 px and those captures are 390 and 1280), `prove-restore.sh live`: only css 29, the posts row count (one custom_css revision) and SiteGround's own log table moved. **Live rollback (not exercised on live; proven exact on localhost and staging34):** `S_TARGET=live ./push-header-fit.sh --rollback '$HOME/private-backups/header-fit-before-20261019-053558.json'`. Ryan looked at staging34 on a real phone and confirmed it; WebKit/iOS Safari is still not testable here.
- **Thai Massage page images (2026-10-19; Ryan: "take the image that matches Thai Massage on the services page and apply it to the first image on the thai massage page. then replace the next two images with these thai_massage_immage.webp thai_massage_immage_magenta.webp. doing this on staging34" (he also wrote "staging44" once; only staging34 exists, so I used 34)): ON staging34 ONLY, NOT on live.** `thai/install-thai-images.php` + `push-thai-images.sh` (`--apply` / `--rollback FILE`; allow-list localhost:8090 + staging34; sources and sha256 checks in `thai/images/`) change page 1160's three Spectra image blocks in place: image 1 (hero, block 5b6bb4f3) now REUSES media item 915 = the Services page's Thai image (`00illaCNN512Curve.webp`, no copy); images 2 and 3 (blocks 04d0e681, f48b2ed7) are the two files, IMPORTED into the media library (staging34 items 1245 and 1246, original file names kept, so "immage" shows in their URLs). The blocks keep their ids, sizes, `object-fit: cover`, radius and shadow; only url/urlTablet/urlMobile, id, alt, class and natural size (512 x 460) change. New alt text (the old text described a different picture and was identical on all three): 1 "Illustration of a massage therapist working on the back of a client lying on a table." 2 "...giving a back massage in a calm room with plants." 3 "Illustration of a client lying on a massage table while a therapist works on their back, with plants behind them." Side effect: the page's link-preview image (`og:image`, SureRank) follows the first content image, so it is now the Services Thai image. **GOTCHA found by the guard:** the second block says `"id":1107`, which is NOT a media item (1107 is the Therapeutic PAGE id): the block's real image is identified only by its url, so the installer recognises the old state from the block text itself (ids are markers, exactly 7 url occurrences, alt, natural size) and keeps the exact old/new strings in the snapshot for rollback. Rollback removes ONLY the media items the apply imported (and their files) and only if nothing else references them; old media items are never deleted (Deep Tissue still uses them). **Verified:** localhost apply/idempotent/rollback (page byte-identical, media count back to 38, uploaded files removed)/re-apply; staging34 export `staging34-before-thai-images-20261019-052041.sql` (verified off-server; it holds the media ROWS, not files), public check 11/11, `thai/check-thai-images.mjs <url> [prefix] [dark]` passes at 7 widths (1280-320) in light and dark (3 illustrations, reviewed files in order, loaded, cover, the page-design box sizes unchanged, distinct alt, no side-scroll), `check-thai-page.mjs` passes in Chromium AND Firefox, axe 0 violations light and dark, `post-change-test.sh staging34`: 18/20 identical to live, the 2 that differ are the Thai page and the changed pixels are EXACTLY three bands = the three image boxes (380/200/200 px desktop, 300/250/250 phone) with identical page height, no PHP errors; `prove-restore.sh staging34`: only page thai-massage and posts/postmeta row counts moved; image files served 200 image/webp. **Staging34 rollback: `S_TARGET=staging34 ./push-thai-images.sh --rollback '$HOME/private-backups/thai-images-before-20261019-052051.json'`.** **Observations for Ryan (not changed):** images 1 and 2 are the SAME illustration (the Services image vs thai_massage_immage.webp differ only by a slight colour grade), so the page shows one picture in three tints; all three show a client on a massage TABLE with a bare back under a sheet, while the page says "fully clothed" and the FAQ says "a mat" (the mismatch noted earlier now applies to the new pictures); on phones the 342 x 250 crop of images 2 and 3 trims the top of the therapist's hair (centered cover crop; an `object-position` tweak would fix it if wanted). Live needs Ryan's explicit go (patch pattern as before).
- The "October 5 staging19 task boundary" paragraph near the top is historical; it does not describe the current work.

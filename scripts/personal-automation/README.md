# Ryan Personal Automation OAuth pages

Information pages for Ryan's private Google tools and authorized personal or
business workflows. The base sources preserve the original Drive-only release;
the disclosure extension covers the connected tools and optional read-only
Search Console reporting. Publishing page text does not enable an API or grant
Google permissions.

- Homepage: `/personal-automation/`
- Privacy policy: `/personal-automation/privacy/`
- Terms of service: `/personal-automation/terms/` (separate extension below)

The pages are public by direct URL, without a password. SureRank metadata and a
small WPCode snippet mark them `noindex` and exclude them from site search,
WordPress page menus and XML sitemaps. No links are added to the main site's
navigation or footer. This is unlisted publishing, not access control: anyone
with a URL can read or share it. The existing site privacy page stays separate.

## Local preview

Use the existing runtime checkout, not a second WordPress stack:

```bash
export RPA_RUNTIME=/home/lauer/WordPress/new-masseuse-site
bash scripts/personal-automation/local.sh preflight
bash scripts/personal-automation/local.sh apply
node scripts/personal-automation/verify.mjs http://localhost:8090
```

Open `http://localhost:8090/personal-automation/`. Every local mutation takes a
fresh SQL export under the runtime's ignored `backups/personal-automation/`.
Rollback uses the `/private/personal-automation-before-*.json` path printed by
the apply: `bash scripts/personal-automation/local.sh rollback MANIFEST`.

## Staging and publication

Staging36 comes first. The site instructions require Ryan to run the live apply
himself after staging review. The following commands assume this source checkout
is the current directory and the Thai Solitude SSH key is unlocked:

```bash
export S26_CREDS=/home/lauer/WordPress/new-masseuse-site/remote_execution.txt
bash scripts/personal-automation/deploy.sh
bash scripts/personal-automation/deploy.sh --apply
node scripts/personal-automation/verify.mjs https://staging36.thaisolitude.com
```

The deployer finds the target by its exact WordPress home URL, exports its DB,
copies the export off-server and verifies checksums before applying. It creates
only two pages and one snippet; it refuses conflicting or edited objects.
It refreshes the cache and copies the rollback manifest off-server afterward.
The site-wide checks are in the runtime checkout's
`scripts/work-with-me-employer/post-change-test.sh staging36` and
`scripts/work-with-me-employer/prove-restore.sh staging36` (the current runtime
versions know staging36). HTTP 202 is a SiteGround challenge, not a passing test.

After staging passes, Ryan's production command is:

```bash
S_TARGET=live bash scripts/personal-automation/deploy.sh --apply
```

Then run the public page verifier against `https://thaisolitude.com` and the
site-wide post-change checks. A guarded rollback is available with
`S_TARGET=live bash scripts/personal-automation/deploy.sh --rollback MANIFEST`;
it also requires a fresh verified backup and Ryan's own live execution.

## Terms extension

The base package is preserved for its original rollback. The terms extension
creates one child page, adds its link to the app homepage, and extends the
existing visibility snippet to cover the new path. It checks the original
content exactly and does not change the privacy policy or marketing pages.

```bash
RPA_RELEASE=terms bash scripts/personal-automation/local.sh apply
RPA_TERMS=1 node scripts/personal-automation/verify.mjs http://localhost:8090
RPA_RELEASE=terms bash scripts/personal-automation/deploy.sh --apply
RPA_TERMS=1 node scripts/personal-automation/verify.mjs https://staging36.thaisolitude.com
```

After staging review, Ryan runs:

```bash
RPA_RELEASE=terms S_TARGET=live bash scripts/personal-automation/deploy.sh --apply
```

Use `RPA_RELEASE=terms` for this extension's preflight and rollback too. Roll it
back before attempting to remove the base package; the original installer
deliberately refuses the extended homepage/snippet rather than overwriting it.

## Expanded disclosures

`RPA_RELEASE=disclosures` updates only the existing homepage, privacy and terms
page contents. It requires the exact terms release and refuses changed pages or
visibility controls. The original sources and installers stay frozen for their
existing rollback contracts. The new texts are `home-tools.html`,
`privacy-tools.html` and `terms-tools.html`.

The update covers Drive, Docs, Sheets, Gmail, Calendar, Contacts, optional
read-only Search Console, and the distinction between a local backup and an
AI-assisted task that sends selected data to the chosen assistant provider.
It preserves page titles, URLs, noindex metadata and the existing visibility
snippet. Roll this extension back before attempting the terms/base rollback.

```bash
RPA_RELEASE=disclosures RPA_RUNTIME=/home/lauer/WordPress/new-masseuse-site \
  bash scripts/personal-automation/local.sh apply
RPA_RELEASE=disclosures S26_CREDS=/home/lauer/WordPress/new-masseuse-site/remote_execution.txt \
  bash scripts/personal-automation/deploy.sh --apply
RPA_TERMS=1 node scripts/personal-automation/verify.mjs https://staging36.thaisolitude.com
```

After staging review, Ryan runs the production apply himself:

```bash
RPA_RELEASE=disclosures S_TARGET=live \
S26_CREDS=/home/lauer/WordPress/new-masseuse-site/remote_execution.txt \
bash /tmp/thai-oauth-pages/scripts/personal-automation/deploy.sh --apply
```

Rollback uses the same release selector and `--rollback MANIFEST`; it requires a
fresh verified database backup and exact installed text. No existing page is
deleted by either applying or rolling back this extension.

## Read-only Search Console tool

`search-console.py` uses Butsaba's existing Production desktop client from
`/home/lauer/butsaba-contact-tool/credentials.json`. It requests Search Console
read-only access and basic Google account identity, verifies
`butsabad@gmail.com`, and saves a private token under
`/home/lauer/.config/butsaba-search-console/`. No token is stored in this repo or
sent to WordPress. It uses the already-installed Contacts Python environment.

Enable the API in the existing project, then authorize after publishing the
updated disclosures:

```text
https://console.cloud.google.com/apis/library/searchconsole.googleapis.com?project=temporal-wares-512003-m9
```

```bash
/home/lauer/butsaba-contact-tool/.venv/bin/python scripts/personal-automation/search-console.py authorize
/home/lauer/butsaba-contact-tool/.venv/bin/python scripts/personal-automation/search-console.py status
/home/lauer/butsaba-contact-tool/.venv/bin/python scripts/personal-automation/search-console.py report
/home/lauer/butsaba-contact-tool/.venv/bin/python scripts/personal-automation/search-console.py sitemaps
/home/lauer/butsaba-contact-tool/.venv/bin/python scripts/personal-automation/search-console.py inspect https://thaisolitude.com/
```

Google's read-only scope covers properties the account can access; this tool
filters to Thai Solitude's domain or homepage URL-prefix properties and prefers
the domain property. It has no sitemap submission, property mutation, user
management or website publishing command. Reports default to final web-search
data for 28 days ending three days ago (Search Console's Pacific date basis),
with a 250-row limit. An empty report is not an API failure. A missing property
requires the owner to add/share it through Search Console; OAuth alone does not
grant property access. This is a local command-line tool, not an MCP registration.
An identical executable copy is installed at
`/home/lauer/.local/bin/butsaba-search-console`; it accepts the same subcommands.

## Google Branding values after publication

- App name: `Ryan Personal Automation`
- Support and developer contact: `ryan.steitz@gmail.com`
- Authorized domain: `thaisolitude.com`
- Homepage: `https://thaisolitude.com/personal-automation/`
- Privacy policy: `https://thaisolitude.com/personal-automation/privacy/`
- Terms of service, after the extension is published: `https://thaisolitude.com/personal-automation/terms/`

Use these only after the production URLs work. Domain verification and Google's
branding checks remain separate steps. The page installers do not change
rclone clients; each Google connection is authorized and tested separately.
Update the privacy disclosure before enabling a different data use or sharing
destination. Do not request permissions for integrations that do not yet exist.

## Current deployment

As of October 20, 2026 (local time), all three original pages are live. Ryan
ran the base live apply at 23:59 UTC and the terms extension at 00:12 UTC on
October 21. Ryan published the expanded disclosures at 01:47 UTC on October 21;
they are now applied on both staging36 and production. Desktop and phone checks
pass on all three updated pages on both sites for direct access, noindex,
navigation exclusion, search exclusion and both XML sitemap files. Local
rollback/reapply passes for all three releases. All five post-change checks pass
on both staging and production: the 20 existing public page-views are
pixel-identical, structured data matches, and no new PHP errors were found.

Disclosure staging export: `staging36-before-personal-automation-20261021-012802.sql`.
Its off-server copy is checksum-verified and restores successfully. Comparison
with staging shows only the three intended page contents and the posts row
count (revisions); CSS, snippets, SEO metadata and compared settings match.
Staging rollback manifest:
`/home/customer/private-backups/personal-automation-disclosures-before-20261021-012813-mXD4fX.json`.
Production disclosure export: `live-before-personal-automation-20261021-014704.sql`.
Its off-server copy is checksum-verified and restores successfully. The
comparison shows the three intended page contents, three additional post rows
(revisions), four postmeta rows and seven SiteGround security-log rows. Compared
CSS, snippets, SEO values and settings are unchanged. The completed production
rollback manifest is
`/home/customer/private-backups/personal-automation-disclosures-before-20261021-014715-cMkbXu.json`.
Search Console authorization is pending API enablement confirmation from Ryan.

Terms on staging: page 1257, with a new link on homepage 1254 and visibility
snippet 1256 extended to the terms path. Export
`staging36-before-personal-automation-20261021-000842.sql` restores successfully;
only those expected objects and associated metadata/cache differ. Export and
manifest are verified off-server. The terms rollback manifest is
`/home/customer/private-backups/personal-automation-terms-before-20261021-000852-ax45kI.json`.

The production export `live-before-personal-automation-20261020-235852.sql` and
rollback manifest were verified off-server. The export restores successfully;
the comparison shows the two new pages, snippet, associated metadata/cache
changes and six additional SiteGround security log rows. Existing page content,
CSS, snippets and compared site settings are unchanged. The live rollback
manifest is
`/home/customer/private-backups/personal-automation-before-20261020-235910-ExVtLZ.json`.
Production IDs are 1254 (homepage), 1255 (privacy), 1256 (visibility snippet),
1257 (terms). The terms live export and completed rollback manifest were also
checksum-verified off-server:
`live-before-personal-automation-20261021-001208.sql` and
`/home/customer/private-backups/personal-automation-terms-before-20261021-001219-Hii7by.json`.

The staging export `staging36-before-personal-automation-20261020-235156.sql`
was verified off-server and restored successfully into a disposable database.
Its comparison with staging shows only the two new pages, snippet and associated
metadata/cache changes. The rollback manifest is
`/home/customer/private-backups/personal-automation-before-20261020-235221-ItU9wx.json`,
also copied off-server. Installed staging IDs are 1254 (homepage), 1255 (privacy)
and 1256 (visibility snippet).

<!-- ref: ./install.php, ./install-disclosures.php, ./deploy.sh, ./local.sh, ./verify.mjs, ./search-console.py — installation, rollback, checks and read-only website reports -->
<!-- src: ../../scripts/LOCAL-WORKFLOW.md, /home/lauer/WordPress/new-masseuse-site/AGENTS.md — isolated worktree and staging-first, Ryan-run production workflow -->

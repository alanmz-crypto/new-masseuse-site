# Ryan Personal Automation OAuth pages

Minimal information pages for Ryan's personal Google OAuth app. They describe
the initial rclone Drive workflow accurately; they do not enable additional APIs
or change the existing rclone connection.

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

## Google Branding values after publication

- App name: `Ryan Personal Automation`
- Support and developer contact: `ryan.steitz@gmail.com`
- Authorized domain: `thaisolitude.com`
- Homepage: `https://thaisolitude.com/personal-automation/`
- Privacy policy: `https://thaisolitude.com/personal-automation/privacy/`
- Terms of service, after the extension is published: `https://thaisolitude.com/personal-automation/terms/`

Use these only after the production URLs work. Domain verification and Google's
branding checks remain separate steps. The existing rclone client is unchanged;
replacing it still needs authorization and verification of the new connection.
Update the privacy disclosure before enabling a different data use or sharing
destination. Do not request permissions for integrations that do not yet exist.

## Current deployment

As of October 20, 2026 (local time), the homepage and privacy page are on both
staging36 and production. Ryan ran the base live apply at 23:59 UTC. The terms
extension is on staging36 only, pending Ryan's live apply. Desktop and phone
checks pass on both production pages and all three staging pages for direct
access, noindex, navigation exclusion, search exclusion and both XML sitemap
files. Local rollback/reapply checks pass for the base and terms packages.

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
Production IDs are 1254 (homepage), 1255 (privacy), 1256 (visibility snippet).

The staging export `staging36-before-personal-automation-20261020-235156.sql`
was verified off-server and restored successfully into a disposable database.
Its comparison with staging shows only the two new pages, snippet and associated
metadata/cache changes. The rollback manifest is
`/home/customer/private-backups/personal-automation-before-20261020-235221-ItU9wx.json`,
also copied off-server. Installed staging IDs are 1254 (homepage), 1255 (privacy)
and 1256 (visibility snippet).

<!-- ref: ./install.php, ./deploy.sh, ./local.sh, ./verify.mjs — installation, rollback and checks -->
<!-- src: ../../scripts/LOCAL-WORKFLOW.md, /home/lauer/WordPress/new-masseuse-site/AGENTS.md — isolated worktree and staging-first, Ryan-run production workflow -->

# Ryan Personal Automation OAuth pages

Two minimal information pages for Ryan's personal Google OAuth app. They describe
the initial rclone Drive workflow accurately; they do not enable additional APIs
or change the existing rclone connection.

- Homepage: `/personal-automation/`
- Privacy policy: `/personal-automation/privacy/`

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

## Google Branding values after publication

- App name: `Ryan Personal Automation`
- Support and developer contact: `ryan.steitz@gmail.com`
- Authorized domain: `thaisolitude.com`
- Homepage: `https://thaisolitude.com/personal-automation/`
- Privacy policy: `https://thaisolitude.com/personal-automation/privacy/`

Use these only after the production URLs work. Domain verification and Google's
branding checks remain separate steps. The existing rclone client is unchanged;
replacing it still needs authorization and verification of the new connection.
Update the privacy disclosure before enabling a different data use or sharing
destination. Do not request permissions for integrations that do not yet exist.

<!-- ref: ./install.php, ./deploy.sh, ./local.sh, ./verify.mjs — installation, rollback and checks -->
<!-- src: ../../scripts/LOCAL-WORKFLOW.md, /home/lauer/WordPress/new-masseuse-site/AGENTS.md — isolated worktree and staging-first, Ryan-run production workflow -->

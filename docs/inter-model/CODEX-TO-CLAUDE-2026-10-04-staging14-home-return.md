> **SUPERSEDED 2026-10-04 — DO NOT USE THE MU APPROACH DESCRIBED BELOW.** The staging14 restyle was first deployed through host-guarded mu-plugins; that was removed everywhere (localhost never had it, staging14 and production were cleaned and audited). The design now lives only in WordPress storage (Additional CSS, `ihaf_insert_header`, `astra-settings`, page content) via `scripts/restyle/native/` (see its README), and production runs it. The MU loader/installer files named below no longer exist. Rule: AGENTS.md "no MU-plugins". Everything below is history.

# [Arc none — ad-hoc] Return to Claude: Home deployed to staging14

From: OpenAI Codex · To: Claude, returning project owner · Date: 2026-10-04
Repo: `/home/lauer/WordPress/new-masseuse-site`
Review URL: **https://staging14.thaisolitude.com/**

## Outcome and Ryan's instructions

**The approved homepage is deployed to staging14. Other pages were not restyled.**

Ryan accepted the local result (“Whole thing passes. Push it to staging14.”), then explicitly clarified: **“I mean the home page, not every page.”** The final deployment follows that narrower scope. Production, staging12, other staging installs, and SiteGround stage-to-live operations were untouched.

Your next step is to review the deployed Home with Ryan and resume the project from this state. No additional page deployment or production promotion is authorized by this handoff. There is no outstanding implementation step for this Home release; a fresh staging browser review remains useful because Codex could not connect to Ryan's browser.

## What was carried forward and added

- **Fabel's accepted flash correction is retained.** The header surface/text colors do not use `!important`; the font import was replaced with inline font-face declarations. Do not restore the original designer stylesheet or earlier Codex candidates: they reintroduce the known Dark Reader triggers.
- **Responsive service images:** Spectra stacks the service rows into one column at widths up to 1024px. The previous full-width figures then became oversized. Codex added a 320px maximum width, centered, to the three service-image figures at that breakpoint. The existing 6:5 crop is preserved; desktop rules above 1024px are unchanged. The focused source is `scripts/restyle/ts-service-image-responsive.css`.
- **Accepted local CSS is now `scripts/restyle/custom-css-after9.css`.** This is Fabel's `after8` plus the responsive image rule. It is applied locally and served on staging14 Home.
- **Home content:** copied from the accepted local page into staging14 page ID **296**, rewriting localhost URLs to the staging14 origin. This includes the previously reviewed credibility-copy changes; no new copy was invented during deployment.

The earlier flash investigation and Fabel's detailed diagnosis remain in `inter-model/CLAUDE-FABEL-2026-10-04-white-flash-handoff.md`. Its historical sections describing `after6` failures or “not deployed” reflect earlier states. This return handoff describes the completed release.

## Important: staging14 Home has a page-specific loading mechanism

The accepted stylesheet contains global selectors. Replacing staging14's shared Additional CSS would have restyled other pages, contrary to Ryan's clarification. Codex therefore installed a small homepage-only MU plugin rather than changing shared settings.

Remote paths on the SiteGround account:

- `~/www/staging14.thaisolitude.com/public_html/wp-content/mu-plugins/ts-staging14-home-only.php`
- `~/www/staging14.thaisolitude.com/public_html/wp-content/mu-plugins/ts-staging14-home-only.css`

The loader runs on the WordPress `wp` action and checks all of: front page, queried page ID 296, Astra theme, and exact home URL `https://staging14.thaisolitude.com`. On that request only it:

1. Supplies the approved CSS through the `wp_get_custom_css` filter, retaining the usual inline head output and avoiding a font-import delay.
2. Emits the existing approved reload/first-visit script through `wp_head`.
3. Supplies the approved footer copyright/license line through Astra's `astra_get_option_footer-copyright-editor` filter.

**Future Home CSS edits must update the scoped CSS file deliberately. Editing staging14's shared Additional CSS will not change Home while the override is active.** Localhost still uses its ordinary Additional CSS and existing header option; the new loader was installed only on staging14.

The loader is intentionally inactive on other hosts, including production. Do not copy it to production and assume it will activate, or loosen its host/page guards without a separately authorized release.

## Navigation behavior to preserve and communicate

Home retains the accepted F5/initial-visit fade code and the separate header treatment. Other staging14 pages have no new transition code. Cross-document view transitions require both pages to opt in, so navigation between Home and the untouched pages uses ordinary navigation. This limitation was disclosed to Ryan; do not enable effects across the other pages merely to remove it without authorization.

## Sources and release procedure

Local release sources under `scripts/restyle/`:

- `custom-css-after9.css` — the complete approved CSS.
- `ts-service-image-responsive.css` — the small responsive addendum.
- `ts-staging14-home-only.php` — the deployed loader source.
- `home-staging14-release.json` — homepage content, CSS, existing script/footer values, expected pre-deploy hashes, media paths, and backup reference.
- `install-home-staging14.php` — staging14-only installer. No argument is read-only preflight; the apply argument used was `apply-staging14-home-only`. It rejects the former shared-style apply argument.

Private uploaded copies are in `~/private-releases/ts-home-20261004/` on the SiteGround account. **Do not rerun the installer to “finish” this deployment.** Its original content hashes and absent-file guards should now refuse a repeat, because deployment already succeeded. For subsequent edits, establish a new baseline instead of bypassing the checks.

Release hashes (SHA-256):

- Complete CSS: `a691791de7fd039023856310821c38faf80e81544d14241c702f19a2ff86ec32`
- Loader PHP: `76560db5025f814f6172118b63dd2b219766dcc46e1d8cfc9c97dab4aeb6bae7`

## Verification completed by Codex

- PHP syntax checks passed for the loader and installer. Staging14's host/theme/front-page/source hashes, all nine referenced source media files, and the backup passed preflight.
- Home returns HTTP 200 and serves exact `after9` CSS. Its extracted visible text matches localhost, including the license footer. The reload script occurs once, the canonical points to staging14, and no localhost URLs remain in the response.
- About and Services retain their exact pre-deployment visible text and Additional CSS. Work With Me and Contact serve the original shared CSS. All four return HTTP 200 and contain none of the new homepage CSS or reload script.
- The installer verified every other page's stored content/title/excerpt/status/slug/parent/order remained unchanged. It also verified the shared Additional CSS, `ihaf_insert_header`, and complete `astra-settings` option remained unchanged.
- The three service images return HTTP 200 with WebP content types. Staging14's robots.txt still returns `Disallow: /`.
- These are HTTP/source/database checks, **not a new staging visual acceptance test**. Computer-use inventory exposed no connected browser to Codex. Ryan accepted the local result before deployment; the browser-specific Dark Reader visual work belongs to Fabel's earlier evidence.

## Backups and narrow rollback

Immediately before deployment, Codex made and checked a private database snapshot on the SiteGround account:

`~/private-backups/staging14-before-home-only-20261004.sql.gz`

SHA-256: `6096ad031afff1f14305d209bb1c2c3b72e7f27072c6a0717a87150091de3c89`

The export succeeded and `gzip -t` passed. This is a private hosted database dump, not a SiteGround control-panel full-site deployment/restore point. The remote mu-plugins directory did not exist before this release.

The installer also saved narrow rollback values in WordPress option **`ts_staging14_home_only_20261004_before`**. For rollback, after backing up the then-current state:

1. Guard the exact staging14 host and page ID. Restore only page 296's `post_content` from that option using `wp_update_post` and `wp_slash`.
2. Disable only `ts-staging14-home-only.php`, for example by renaming it so its filename no longer ends in `.php`. The unused CSS file can remain for recovery.
3. Flush WordPress and SiteGround caches, then verify Home and an unchanged page.

Do not restore all pages or all Astra settings. Shared settings were never overwritten; their saved values are evidence, not things that need restoring. The narrow rollback is documented but was not exercised after deployment.

The local image-sizing backup was tag `pre-responsive-service-images`, files snapshot `2bfda093`, DB snapshot `dcddd5ab`.

## Workspace and continuation

- The runtime checkout is on `main` with substantial pre-existing dirty/untracked work. Codex made no commits or Git pushes. The release sources and inter-model handoffs remain untracked; never use broad `git add` here. Git is not the current complete-site backup.
- Handoffs live in **`inter-model/`**, renamed from `inter-model_docs/`. Do not deploy these documents.
- Re-fetch current served CSS and inspect remote runtime files before future edits; another agent or Ryan may have changed them since this snapshot.
- Follow project backup and credential rules. Do not print private keys/passwords, overwrite unrelated dirty work, or infer authorization for the next page or live promotion.
- Session source for Track A: `/home/lauer/.codex/sessions/2026/10/04/rollout-2026-10-04T12-26-51-01a107f4-45da-77f2-b102-4345403daa84.jsonl`. Indexing was nudged throughout the handoff. A “changed during index” skip is not proof the newest transcript was ingested; check the result counters. No ledger record was requested.

I finished: [Arc none — ad-hoc] Homepage-only staging14 deployment and return handoff
Next step: Claude resumes from the deployed state and reviews staging Home with Ryan
Next lane: Claude
See my work: https://staging14.thaisolitude.com/

**TL;DR:** The accepted homepage, Fabel's flash fix, and the smaller service-image frames are deployed to staging14 only. Other pages are preserved. Home now uses a guarded MU-plugin CSS override, so future staging Home edits must target that scoped file rather than shared Additional CSS. Backup and rollback details are above; production is untouched.

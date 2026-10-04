# Thai Solitude remote release procedure

This is a decision procedure, not a deploy command. Localhost, staging12, and
production are separate WordPress installations. The Git branch contains edit
sources and scripts; merging it does not publish WordPress content.

## Identify the exact release

1. Name the pages, assets, settings, and source revision intended for release.
2. Confirm in SiteGround Site Tools → WordPress → Staging that
   `staging12.thaisolitude.com` is the copy attached to `thaisolitude.com` and
   that it contains exactly those changes. Do not infer this from the URL.
3. Compare the rendered staging pages with the reviewed localhost result on
   desktop and mobile. Check navigation, contact, résumé PDF, and booking.
4. Confirm Butsaba's actual service plans, Portland location, credential and
   license number, and any required facility details against approved source
   material. Resolve uncertain public claims before a release decision.

## Read-only public preflight

Run `python3 scripts/release-preflight.py`. It checks the public routes on
staging12 and production for HTTP responses, titles, canonicals, the visible
Oregon license number, staging `noindex`, production indexability, the
production sitemap, and Work With Me contact and résumé actions. It prints
every failed check and exits nonzero. It never logs in or writes to either
site. A passing result is **not** proof that the claims are true, the contact
form delivers, or a remote backup exists.

On 2026-10-04, this preflight found release blockers on both staging12 and
production: the license number was visible on Work With Me but absent from
the other checked pages; About and Contact titles said "Registered Therapist";
Home and Services titles contained outcome language requiring review. Re-run
the preflight after corrections rather than relying on this dated observation.
The locally reviewed license/footer and title values are captured in
`scripts/credibility-pass/sitewide-license-titles.php`. Its default `check`
mode is read-only; applying it to staging12 is a separate authorized step
after a remote backup. It does not resolve the remaining service, location, or
facility facts.

Separately confirm the contact form or email reaches Butsaba and a test booking
can be completed without disturbing real appointments. Verify pages in a
mobile browser. Inspect production and staging search snippets/metadata where
relevant. A public GET cannot establish form delivery or authenticated flows.

## Remote backup and deploy selection

Before changing staging or production, check for a usable SiteGround backup of
the relevant remote site. Local `backups/` covers only localhost. Record the
backup timestamp and exact release scope in the review handoff.

For promotion, use SiteGround **Custom Deploy** by default. Review each file
and database table marked to be added, updated, or deleted. WordPress page
content shares database tables with other live content: a table-level update
can overwrite changes made on production since staging was copied. If the
selection cannot preserve live-only data, stop and plan a narrower migration.
Use **Full Deploy** only with separate authorization for replacing the entire
live files and database. SiteGround describes both modes in its
[deployment guide](https://www.siteground.com/tutorials/staging/deploy/).

Only after the exact release, backup, and selection are reviewed should the
owner authorize the SiteGround operation. This document and the public
preflight are not authorization to deploy.

## After an authorized deployment

Check the production homepage and every changed route on desktop and mobile;
open the résumé, use the contact action, and check media, booking, titles,
canonicals, `robots.txt`, and the sitemap. Run the public preflight again.
Keep staging `noindex` and production indexable. If a release causes a problem,
SiteGround documents its deployment restore point in its
[restore guide](https://www.siteground.com/tutorials/staging/restore/); review
any live data added since deployment before restoring.

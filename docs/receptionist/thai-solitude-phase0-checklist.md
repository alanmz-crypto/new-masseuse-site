# Thai Solitude Phase 0 release checklist — v2

> Planning attachment to [receptionist plan v3](thai-solitude-receptionist-plan.md). Complete on the actual release candidate and record evidence before asking Ryan to approve a live promotion. This is the AI-free FAQ and contact release; the [chat suite](thai-solitude-receptionist-acceptance.md) is a later gate.

## Practice and source

- [ ] Butsaba confirms the practice accepts private clients. Ryan reviews the **actual employment agreement** for the FAQ, contact, and booking path. If either point is unresolved, do not publish a client-booking FAQ.
- [ ] Questions come from Butsaba's actual experience. Unanswered health-suitability questions use one approved boundary entry and contact path, with no individual contraindication advice.
- [ ] Each published registry entry has a named editor, source, dated `approval_ref`, compliance checklist SHA-256/reviewer/date, and future `review_by`. An expired entry is removed or replaced before release.
- [ ] Approved wording in the authoring registry matches the WordPress page and the release record names both revisions.

## Regulated copy and rendered output

- [ ] Review FAQ questions and answers, contact prompts, fixed site notices, navigation labels, link text, title and meta descriptions, OG/Twitter fields, image alt text, and any SureRank or Spectra-generated schema against the current canonical `inter-model/Thai Solitude Fix Checklist.md` in the local site workspace. That checklist is untracked: calculate and record its SHA-256 at check time and recheck affected entries if the bytes change. Search the rendered page **and view-source**, including `FAQPage` JSON-LD, for forbidden terms, unsupported outcomes, diagnosis/treatment implications, and unverified credentials.
- [ ] Confirm the Oregon LMT license number `LMT-29905` is visible in the rendered footer on every released page and on any standalone promotional surface.
- [ ] Voice is singular and consistent; no “we,” “our,” “us,” “studio,” or wording implying staff who do not exist.
- [ ] Ryan signs the regulated-copy check separately from Butsaba's approval of facts and tone. If Ryan edited the entry, name another independent human reviewer. Neither the editor nor a model self-approves compliance.

## Contact and visitor data

- [ ] Inspect the **rendered live Contact page and source** first. The local database has one published SureForms definition but no local page embed, and the plugin is inactive locally; that does not establish a working live form. If no form is exposed, plan separately authorized, backup-first removal of the unused form and its entries. If a live form is exposed and retained, verify remote plugin status, form settings, and notification behavior.
- [ ] For a retained form, turn entry storage **off for new submissions** and verify no new WordPress entry row appears after a non-sensitive test. Document inbox delivery and retention. “No database entry” does not mean “no stored message.”
- [ ] Start an **existing-copy inventory**: local database (one entry dated 2026-06-08, content unclassified), `source-db.sql`, `new-masseuse-db-fixed.sql`, `deploy/10-production-thaisolitude.sql`, production, staging4/staging5, local Restic, SiteGround backups, and notification inboxes. Use counts/dates/metadata only. Ryan or Butsaba privately classifies the entry as test or visitor data. For each copy, record owner, access, retention or purge decision, and completion evidence. No data mutation or purge without a backup and explicit authorization.
- [ ] For **future** SiteGround staging and local database copies, document how visitor-entry tables and related metadata are excluded or scrubbed. If the tool clones the full database first, restrict access and scrub before using the copy; verify with counts and non-sensitive metadata, without printing visitor text.
- [ ] Review notice, consent, privacy policy, and the unresolved legal question about health detail from Washington visitors for **both** a form and direct email. Record the inbox provider, recipients, forwarding, access, retention, and deletion before publishing a new email link; a mailto link can still collect health detail.
- [ ] Verify Google Site Kit/Analytics behavior on the actual remote page. Confirm form values, health text, URL query parameters, and page paths containing such values do not reach analytics. Do not infer Analytics activation from an installed plugin alone.
- [ ] If a form remains, a non-sensitive staging submission reaches the intended inbox and no staging notification goes to an unintended live recipient. If the contact path uses direct links, verify each address/number and the destination inbox policy with Butsaba.

## Release and use

- [ ] Booking link points to Butsaba's confirmed platform page if booking is active; no FAQ text implies an appointment is confirmed by the site.
- [ ] FAQ and contact path work by keyboard and on mobile; links, focus, headings, and layout remain usable.
- [ ] Staging returns `Disallow: /`; live `robots.txt` still allows crawling and advertises the sitemap after promotion.
- [ ] A usable SiteGround backup for the relevant remote site is confirmed. Review the exact Custom Deploy file/table selections, preserving live-only submissions. Ryan approves the named live promotion.

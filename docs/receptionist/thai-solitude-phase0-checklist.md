# Thai Solitude Phase 0 release checklist

> Planning attachment to [receptionist plan v2](thai-solitude-receptionist-plan.md). Complete on the actual release candidate and record evidence before asking Ryan to approve a live promotion. This is the AI-free FAQ and contact release; the [chat suite](thai-solitude-receptionist-acceptance.md) is a later gate.

## Practice and source

- [ ] Butsaba confirms the practice accepts private clients and that the FAQ, contact, and booking path fit any employer arrangement. If this is unconfirmed, do not publish a client-booking FAQ.
- [ ] Questions come from Butsaba's actual experience. Unanswered health-suitability questions use one approved boundary entry and contact path, with no individual contraindication advice.
- [ ] Each published registry entry has a named editor, source, dated `approval_ref`, compliance checklist revision/reviewer/date, and future `review_by`. An expired entry is removed or replaced before release.
- [ ] Approved wording in the authoring registry matches the WordPress page and the release record names both revisions.

## Regulated copy and rendered output

- [ ] Review FAQ questions and answers, contact prompts, fixed site notices, navigation labels, link text, title and meta descriptions, OG/Twitter fields, image alt text, and any SureRank or Spectra-generated schema against the current canonical `inter-model/Thai Solitude Fix Checklist.md` in the local site workspace. That checklist is untracked, so obtain its current version before release rather than assuming it is in this branch. Search the rendered page **and view-source**, including `FAQPage` JSON-LD, for the project's forbidden terms, unsupported outcomes, diagnosis/treatment implications, and unverified credentials.
- [ ] Confirm the Oregon LMT license number `LMT-29905` is visible in the rendered footer on every released page and on any standalone promotional surface.
- [ ] Voice is singular and consistent; no “we,” “our,” “us,” “studio,” or wording implying staff who do not exist.
- [ ] A reviewer signs the regulated-copy check separately from Butsaba's approval of facts and tone.

## Contact and visitor data

- [ ] Decide whether the existing live contact form remains. Default for this phase is direct email/phone links and no new free-text form. If a SureForms form remains, verify its **actual remote** active status, form-level compliance settings, entry storage setting, notification recipient, and behavior with a non-sensitive test submission.
- [ ] For a retained form, turn entry storage **off for new submissions** and verify that no new WordPress entry row appears. Document where the notification email lands and its retention. Do not treat “no database entry” as “no stored message.”
- [ ] Inventory historical entries and backups before any copy. Document how visitor-entry tables and related metadata are excluded or scrubbed for SiteGround staging and local database pulls. If a tool clones the full database first, restrict access and scrub the copy before using it; verify with counts and non-sensitive metadata, without printing visitor text.
- [ ] Review the contact notice and consent, privacy policy, and the unresolved legal question about health detail from Washington visitors before allowing any open-text field.
- [ ] Verify Google Site Kit/Analytics behavior on the actual remote page. Confirm form values, health text, URL query parameters, and page paths containing such values do not reach analytics. Do not infer Analytics activation from an installed plugin alone.
- [ ] A non-sensitive staging submission reaches the intended inbox; no staging notification goes to an unintended live recipient. If the contact path uses direct links, verify each address/number with Butsaba.

## Release and use

- [ ] Booking link points to Butsaba's confirmed platform page if booking is active; no FAQ text implies an appointment is confirmed by the site.
- [ ] FAQ and contact path work by keyboard and on mobile; links, focus, headings, and layout remain usable.
- [ ] Staging returns `Disallow: /`; live `robots.txt` still allows crawling and advertises the sitemap after promotion.
- [ ] A usable SiteGround backup for the relevant remote site is confirmed. Review the exact Custom Deploy file/table selections, preserving live-only submissions. Ryan approves the named live promotion.

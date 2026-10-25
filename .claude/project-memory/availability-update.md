---
name: availability-update
description: Work With Me and availability update (2026-10-25) - ON staging38 only; what the scan found (resume already clean, no Coming Soon copy), the 10 exact replacements, the date-aware strip, rollback, what is left for Ryan
metadata:
  type: project
---

Ryan, 2026-10-25: "Getting our facts straight. Not disrupting the site. Again, go staging first. We're using staging38 now.", from docs/inter-model/ThaiSolitude_WorkWithMe_Availability_Plan.md (corporate/group bookings begin 2026-11-01; individual appointments at a dedicated location are only PLANNED for summer 2027; no house calls; runs alongside her employment; leave For Practices, the repeated links and the work-history dates alone).

**State: ON staging38 ONLY (applied 2026-10-25 01:24 UTC, request av-apply-1), NOT live.** Tooling scripts/work-with-me-employer/availability/ (scan, builder, installer, push script with --check, browser checker). Rollback: request.sh run --target staging38 --script availability/push-availability-update.sh --args '--rollback $HOME/private-backups/availability-update-before-20261025-012451.json' --no-tests.

**Facts found:** no page says Relocating from Chicago and the served resume (sha c0c8b7c6, the repo's proposed.pdf) says only Portland, OR (the older ONLYOFFICE resume in scripts/assets does say it but is not deployed); no Coming Soon copy exists; the misleading bits were the Where Do Sessions Take Place? and How Do I Book a Session? FAQ answers on the Thai, Therapeutic and Deep Tissue pages. **Changed:** the CorporateBand strip is now the availability notice, includes Home, and flips itself to Now accepting on 2026-11-01 Pacific (purge the SiteGround cache that day); two sentences on the corporate page; two on Work With Me; the two FAQ answers per modality page. The word studio is on the OBMT house list, so the plan's dedicated studio reads dedicated location (Ryan can override).

**Lessons:** MySQL SHA2() and PHP/Python hashes disagree on the three research pages (Unicode), so prove changes against the decrypted before-snapshot (scripts/ci/fetch-offsite.sh), not the fingerprint; wp_update_post as no user silently fails on a WPCode snippet (use --user=1 in tests); WPCode's wpcode_snippets option holds a modified time, so it is set aside in the rehearsal comparisons; the older corporate and evidence installers' exact-state checks no longer match the pages this update edited, so roll this update back first. **Left for Ryan:** About heading From Chicago to Portland, the Mt. Tabor H1s/SEO titles, the Contact description Text ... to book a massage, the Ask about a session button; going live needs his go. Related: [[corporate-group-massage]], [[add-only-never-subtract]], [[service-pages-replication]].

---
name: seo-booking-path
description: "2026-11-20 SEO batch (Ryan: 'make changes so the SEO gets better scores'): FAQ sentence 'book at Winding Willow' on Thai/Therapeutic/Deep Tissue, new Thai/Deep Tissue/Home descriptions, LocalBusiness sameAs; ON staging40, live on Ryan's go; Search Console baseline and the off-site plan"
metadata:
  type: project
---

**Why (Search Console, 2026-08-20..11-17):** the site has shown in Google only since 2026-10-03: ~540 impressions, 16 clicks. 11 of 12 sitemap pages are indexed (`/services/` "Discovered – currently not indexed"; Ryan can Request indexing). The searches are booking searches ("thai massage portland" pos ~23, "massage near me" pos ~24); the corporate page is best placed (pos ~12). The only referring sites are two spam pages (ignore, no disavow). Winding Willow's `/butsaba-dusrai-lmt` page does not link to thaisolitude.com (ask in the pinned email). The service pages said individual appointments are "planned for summer 2027" while Sa books individual clients now at Winding Willow.

**What changed (`scripts/work-with-me-employer/seo-booking/`, exact-state installer, rollback, push script):**
1. Appended to the "How Do I Book a Session?" FAQ answer on Thai, Therapeutic, Deep Tissue: "Until then, you can book an individual Thai, deep tissue or Swedish massage with me at Winding Willow Acupuncture & Wellness, 1540 SE Clinton St, Portland." Plain text, NO link (Ryan 2026-11-20: the menu item is the one link to /winding-willow/; the strip link Claude first proposed was dropped for that reason).
2. SureRank descriptions: Thai "...compression, rocking and assisted stretching. Book with her at Winding Willow on SE Clinton." (157), Deep Tissue "...pressure on tight muscles. Book with her at Winding Willow on SE Clinton St." (151), Home "Thai, therapeutic and deep tissue massage in Portland with Butsaba Dusrai, LMT. Book at Winding Willow, or ask about corporate and group massage." (145). Titles and Therapeutic unchanged.
3. Snippet 1023 LocalBusinessInfo: founder gains `sameAs` = her Winding Willow profile (so their address appears once in every page's structured data; still no link to their site).
Gates: `verify-client-pages.py` and `home/verify-home.py` accept both states; `check-all.sh` checks the builder.

**State:** rehearsals PASS on copies of staging40 (`seo-rehearse-s40-1`) and live (`seo-rehearse-live-1`), touching only those 3 pages, 3 SEO rows and snippet 1023. **staging40 applied 2026-11-20 11:54 UTC** (`seo-apply-s40-1`; export off-site verified first; the run reported FAIL only because Claude's own public check expected wrong counts; fixed, `seo-check-s40-2` PASS). post-change-test staging40 vs live (`seo-pct-s40-2`): all gates PASS, 20/20 views pixel-identical, no PHP errors; structured data differs from live on the 4 sampled pages = the intended sameAs/descriptions (expected until live has them). Staging40 rollback: `request.sh run --target staging40 --script seo-booking/push-seo-booking.sh --args '--rollback $HOME/private-backups/seo-booking-before-20261120-115448.json' --no-tests`. **Live: not enabled.** On Ryan's go: add `thaisolitude.com` to the installer allow-list and `live` to the push script targets, live dry run, `request.sh live`, PR; Ryan merges and runs Site ops (LIVE).

**Off-site plan Claude gave Ryan (his/Butsaba's to do):** Google Business Profile exists (Ryan 2026-11-20): exact name, categories Massage therapist + Thai massage therapist, hidden address + Portland service area, same phone/website, genuine client reviews (Claude declined to write a review from her husband: Google conflict-of-interest rule and FTC insider-review rule), services, photos, monthly posts. Also Bing Places, Apple Business Connect, Bing Webmaster Tools import, a link from Winding Willow's profile page.

<!-- ref: ../../scripts/work-with-me-employer/seo-booking/build-seo-booking.py — the exact text; ./winding-willow-page.md — the page the sentences point to -->

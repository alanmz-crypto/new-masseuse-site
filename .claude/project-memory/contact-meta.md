---
name: contact-meta
description: Contact page SEO description changed to corporate and group massage wording (Ryan 2026-10-25); staging38 applied, live prepared; before/after, guard, exact rollback; the Mt. Tabor titles and About story he chose to leave
metadata:
  type: project
---

Ryan, 2026-10-25, after the availability plan made individual appointments "planned for summer 2027" ([[availability-update]]): I showed him the Contact page's search-result description and he chose **"Change to corporate wording"**.

**Change (one SureRank value, nothing else):** BEFORE `Text (312) 539-4328 to book a massage with Butsaba Dusrai, Portland | butsabad@gmail.com`; AFTER `Text (312) 539-4328 or email butsabad@gmail.com to ask about corporate and group massage in Portland with Butsaba Dusrai, Oregon LMT.` (133 characters). Title `Contact Butsaba Dusrai | Oregon LMT`, the phone and the email are unchanged. The (312) number is the one the old description already published (Chicago area code: still an open item from [[staging31-backend-audit]]).

**Tooling:** `scripts/work-with-me-employer/contact-meta/` = `install-contact-meta.php` (allow-list localhost:8090, staging38, live; ready only when title AND description are exactly the before values; applied when exactly the after values; anything else stops; snapshot of every SureRank row of the page; exact rollback; content hash compared before and after) and `push-contact-meta.sh` (`--apply | --rollback FILE`; export, verified encrypted off-site copy, apply, purge, public check of 7 things from the server). Pattern copied from `thai/install-thai-meta.php`.

**State:** rehearsal on a copy of staging38 PASS (`contact-meta-rehearse-1`); staging38 APPLIED 2026-10-25 15:33 UTC (`contact-meta-apply-38`; export `staging38-before-contact-meta-20261025-153317.sql`; snapshot `contact-meta-before-20261025-153328.json`; public check 7/7; post-change-test 5/5 with 20 of 20 views pixel-identical to live; restore proof moved only the Contact SEO row). Live dry run `contact-meta-dry-live`: state=ready. **Live is prepared, not run:** request `live-contact-meta-1`; Ryan merges the pull request, then Actions > "Site ops (LIVE)" > Run workflow > main > confirm that id (exactly, no leading space).

**Rollback:** staging38: `request.sh run --target staging38 --script contact-meta/push-contact-meta.sh --args '--rollback $HOME/private-backups/contact-meta-before-20261025-153328.json' --no-tests`. Live: the same with `--target live` and the snapshot name in the live apply's report.

**Decided to leave (Ryan, 2026-10-25):** the "in Mt. Tabor" SEO titles and descriptions (Thai, Therapeutic, Deep Tissue; the Home title and description say "in Mt. Tabor, Portland" and "Ask about a session" too) stay until the new location is known, because each page opens with the corporate-only strip and a rewritten FAQ and the new location may not be in Mt. Tabor; the About section "From Chicago to Portland" stays (Butsaba's own history); my own call: the Home button "Ask about a session" stays (inquiry link; the Contact page opens with the strip). See [[add-only-never-subtract]], [[keep-butsaba-values-statements]].

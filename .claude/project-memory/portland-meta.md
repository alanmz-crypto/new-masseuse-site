---
name: portland-meta
description: Item 1 of "reconcile the site to Portland, corporate and group" (Ryan 2026-10-29) - the "in Mt. Tabor" SEO titles/descriptions on Home, Thai, Therapeutic and Deep Tissue become Portland; exact before/after wording, guard, rollback, state (prepared on a branch; staging38 first; NOT live)
metadata:
  type: project
---

Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." and "One at a time." I proposed the exact wording for item 1 in chat; he answered "Accepted. All your changes". This ends the hold in [[contact-meta]] ("in Mt. Tabor" titles/descriptions left "until the new location is known"). Individual appointments at a dedicated location are still only PLANNED for summer 2027 ([[availability-update]]), so the Home description now invites a corporate or group inquiry instead of "a session".

**Change (SureRank per-page title/description only; page content untouched, hash-compared before and after):**

| page | BEFORE | AFTER |
|---|---|---|
| Home title | `Thai Solitude \| Thai & Therapeutic Massage, Mt. Tabor, Portland` | `Thai Solitude \| Thai & Therapeutic Massage in Portland, OR` |
| Home description | `Personalized Thai, therapeutic and deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT.` | `Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.` |
| Thai title | `Thai Massage in Mt. Tabor \| Butsaba Dusrai, Oregon LMT` | `Thai Massage in Portland \| Butsaba Dusrai, Oregon LMT` |
| Thai description | `...Thai massage in Mt. Tabor, Portland with...` | `...Thai massage in Portland with...` |
| Therapeutic description | `...massage in Mt. Tabor, Portland with...` | `...massage in Portland with...` (title unchanged) |
| Deep Tissue description | `...massage in Mt. Tabor, Portland with...` | `...massage in Portland with...` (title unchanged) |

**Tooling:** `scripts/work-with-me-employer/portland-meta/` = `install-portland-meta.php` (allow-list localhost:8090 and staging38 ONLY; ready only when ALL four pages hold exactly the before title and description, applied when exactly the after values, anything else stops; Home must also be `page_on_front`; snapshot of every SureRank row of the four pages; exact rollback; content hash compared before and after; banned-word list incl. "Mt. Tabor" on the new text) and `push-portland-meta.sh` (`--apply | --rollback FILE`; export, verified encrypted off-site copy, apply, purge, public check of the four pages from the server). Pattern: [[contact-meta]] and `services-meta/`.

**State: APPLIED on staging38 (2026-10-29 17:00 UTC, `portland-meta-apply-38`; rehearsal on a copy of staging38 PASS `portland-meta-rehearse-1`; dry run `portland-meta-dry-38`: all four pages "ready").** Export `staging38-before-portland-meta-20261029-170009.sql` (verified, encrypted off-site copy read back identical); snapshot `portland-meta-before-20261029-170021.json`. Public check from the server 24/24 ok (title tag, meta description, old text gone, strip and licence still there, all four pages). Post-change test: 20 of 20 views pixel-identical to live, structured data identical, no PHP errors; restore proof moved only the four SEO rows (296, 1091, 1107, 1160). `check-all.sh` against staging38: ALL 22 GATES PASS (`portland-gates-38`). **LIVE is not enabled and not started:** Ryan decides, then the enable-live change (live host in the installer's allow-list and the push script's targets), a live dry run, and his own "Site ops (LIVE)" run.
**Staging38 rollback:** `request.sh run --target staging38 --script portland-meta/push-portland-meta.sh --args '--rollback $HOME/private-backups/portland-meta-before-20261029-170021.json' --no-tests`.
**Lesson:** `verify-home.py` and `client-pages/verify-client-pages.py` hard-coded the old Home/Thai/Therapeutic/Deep Tissue meta wording, so the first post-change test said "2 gates failed" although the change was right. They now accept the old wording on a site not yet changed and enforce the new wording once a site holds it. Read the gates section of a post-change report even when the run says PASS.

**Not in this item (next items, one at a time):** the Contact page "Address: Mt. Tabor, Portland" line and the Google map embed; the on-page "Mt. Tabor" headings and trust lines (Home trust line, H1s); the footer; the About "From Chicago to Portland" section; "For Practices" rename; refreshing the "bookings begin November 1, 2026" wording after 2026-11-01. See [[open-items]] section D.

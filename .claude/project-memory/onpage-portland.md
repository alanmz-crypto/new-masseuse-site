---
name: onpage-portland
description: Item 3 of "reconcile the site to Portland, corporate and group" (Ryan 2026-10-29, "Go") - Home licence line, Thai H1 and Deep Tissue H1 say Portland instead of Mt. Tabor; exact before/after, guard, rollback, state (staging38 first, NOT live); what is deliberately left
metadata:
  type: project
---

Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." and "One at a time." Item 1 = [[portland-meta]] (SEO titles/descriptions, PR #47 merged); item 2 = [[contact-address]] (Contact Address line, PR #48 merged; map left alone: "Skip the map"); item 3 = this note. I listed the three visible spots and the exact wording in chat; he answered **"Go"**.

**Change (three exact replacements, each old text occurs exactly once in its page on staging38, read 2026-10-29):**
| page | BEFORE | AFTER |
|---|---|---|
| Home (#296) licence line | `</a>) · Mt. Tabor, Portland</p>` (i.e. "Oregon License LMT-29905 (verify) · Mt. Tabor, Portland") | `</a>) · Portland, OR</p>` (matches the footer) |
| Thai Massage (#1160) H1 | `<h1 class="uagb-heading-text">Thai Massage in Mt. Tabor</h1>` | `<h1 class="uagb-heading-text">Thai Massage in Portland</h1>` |
| Deep Tissue (#1091) H1 | `<h1 class="uagb-heading-text">Deep Tissue Massage in Mt. Tabor</h1>` | `<h1 class="uagb-heading-text">Deep Tissue Massage in Portland</h1>` |
The Therapeutic page's H1 already said "Therapeutic Massage in Portland"; this makes the other two match it and the search titles from item 1.

**Left alone on purpose:** the Contact page's Google map (still centered on Mt. Tabor; Ryan: "Skip the map"); the About "From Chicago to Portland" section (Butsaba's own history); every SEO title and description; the research sections; the strip; menus.

**Tooling:** `scripts/work-with-me-employer/onpage-portland/` = `install-onpage-portland.php` (allow-list localhost:8090 and staging38 ONLY; ready only when ALL three old texts occur exactly once and no new text is there; applied when all three new texts occur exactly once; anything else, including only some done, stops; Home must be `page_on_front`; snapshot of each page's whole old content with sha256; rollback turns only the three new texts back, keeping other edits) and `push-onpage-portland.sh` (`--apply | --rollback FILE`; export, verified encrypted off-site copy, apply, purge, public check of the three pages from the server). `home/verify-home.py` now accepts the old or the new licence line (the repo's own before/after pattern), so a site not changed yet still passes.

**State: APPLIED on staging38 (2026-10-29 17:43 UTC, `onpage-portland-apply-38`; rehearsal `onpage-portland-rehearse-1` PASS; dry run `onpage-portland-dry-38`: 3 ready).** Export `staging38-before-onpage-portland-20261029-174306.sql` (verified, encrypted off-site copy); snapshot `onpage-portland-before-20261029-174319.json`. Public check ok on all three pages (new text once, old gone, one H1, strip and licence still there, Thai/Deep Tissue title tags unchanged, 0 "Mt. Tabor" left in the served pages). All gates passed, structured data identical, no PHP errors; restore proof moved only pages home, thai-massage, deep-tissue-massage. Pixel test says FAIL as expected: small boxes on Home, Thai, Deep Tissue (desktop and mobile), plus the Contact address box (item 2, staging only until live); Home mobile is 22px shorter because the licence line wraps onto fewer lines. LIVE is not enabled and not started: Ryan looks, says go, then the enable-live change, a live dry run, and his own "Site ops (LIVE)" run.
**Staging38 rollback:** `request.sh run --target staging38 --script onpage-portland/push-onpage-portland.sh --args '--rollback $HOME/private-backups/onpage-portland-before-20261029-174319.json' --no-tests`.

**Still to do, one at a time (see [[open-items]] section D):** the map (left as is for now); the About "From Chicago to Portland" section; "For Practices" rename; refreshing the "bookings begin November 1, 2026" wording after 2026-11-01; and taking items 1, 2 and 3 LIVE (Ryan decides; one live run per item, or the enable-live change for all three).

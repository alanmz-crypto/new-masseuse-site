---
name: booking-date
description: Ryan 2026-10-30 - corporate/group bookings begin November 14, 2026 instead of November 1, everywhere; what moved, what deliberately did not, guard, rollback, state (staging38 applied; live pending his run)
metadata:
  type: project
---

Ryan, 2026-10-30: "Change booking begins to Nov 14th. Everywhere." Also: the About "From Chicago to Portland" section stays as is ("#1 stays in about page"), and "For Practices" stays (it is the employer-facing label, not for customers).

**Moved (exact text swap "November 1, 2026" -> "November 14, 2026", nothing else in the sentences):** the announcement strip code (WPCode snippet "CorporateBand": now flips to "Now accepting" at 2026-11-14 00:00 Pacific and says Nov 14 until then), corporate page hero fine print, Work With Me "For clients" sentence, and the "Where Do Sessions Take Place?" and "How Do I Book a Session?" FAQ answers on Thai, Therapeutic and Deep Tissue (8 replacements on 5 pages + the snippet).
**NOT moved (needs Ryan's word):** Work With Me "Start - From November 1 - open for days, evenings and weekends" and "in person from November 1" (interviews). They are her employment start, not client bookings.

**Tooling:** `scripts/work-with-me-employer/booking-date/` = `build-booking-date.py` (reads what the availability update put live) -> `booking-date-content.php`, `install-booking-date.php` (allow-list localhost, staging38, live; guard exact once; snapshot; exact rollback; PHP syntax check of the strip), `push-booking-date.sh` (`--apply | --check | --rollback FILE`). Checkers (`verify-client-pages.py`, `availability/check-availability-update.mjs`) now read the booking date a site holds and require every page to agree; `availability/push-strip-flip.sh` now uses Nov 14. New gate "Booking date content matches its builder".

**State: APPLIED on staging38 (2026-10-30 15:15 UTC, `booking-date-apply-38`; rehearsal on a copy of staging38 PASS `booking-date-rehearse-1`; dry run 8 ready + strip ready).** Export `staging38-before-booking-date-20261030-151457.sql`; snapshot `booking-date-before-20261030-151511.json`. Public check ok, all gates passed, structured data identical, no PHP errors. Pixel diffs only in the strip box on 9 pages and the Work With Me "For clients" line (expected).
**Staging38 rollback:** `request.sh run --target staging38 --script booking-date/push-booking-date.sh --args '--rollback $HOME/private-backups/booking-date-before-20261030-151511.json' --no-tests`.
**Deadline:** live must carry this before 2026-11-01 00:00 Pacific or the strip flips on the old date. LIVE is not enabled for a run yet: needs Ryan's go, the live dry run, and his own "Site ops (LIVE)" run.
**Not editable by an agent:** the one-time strip check routine (created over the API); Ryan edits its date (see [[open-items]] section A) and the Nov 1 calendar event.
**LIVE PREPARED 2026-10-30 (Ryan: "Merged. Go live"):** live dry run `booking-date-dry-live` PASS (8 replacements ready + strip ready); request `live-booking-date-nov14` waits for Ryan to merge its PR and run "Site ops (LIVE)" on main. Must be done before 2026-11-01 00:00 Pacific.

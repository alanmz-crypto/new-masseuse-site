---
name: contact-address
description: Item 2 of "reconcile the site to Portland, corporate and group" (Ryan 2026-10-29) - the Contact page Address line "Mt. Tabor, Portland" becomes "Portland, OR"; the map is deliberately NOT touched ("Skip the map"); exact before/after, guard, rollback, state (staging38 first, NOT live)
metadata:
  type: project
---

Ryan, 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that." and "One at a time." Item 1 was [[portland-meta]] (SEO titles/descriptions; PR #47 merged). For item 2 I proposed two changes (the Address line and re-centering the Google map); he answered **"Skip the map"**, so ONLY the address line changes.

**Change (one paragraph of the Contact page's content, page #301; nothing else):**
- BEFORE `<p class="uagb-desc-text">Address</p><p class="uagb-heading-text">Mt. Tabor, Portland</p>`
- AFTER  `<p class="uagb-desc-text">Address</p><p class="uagb-heading-text">Portland, OR</p>`

**Left alone on purpose:** the Google map block on the same page (its own `"address":"Mt. Tabor, Portland"` setting), so the map still centers on Mt. Tabor while the address line says Portland, OR (Ryan's call; the footer already says "Portland, OR"). The privacy policy's Google Map wording is unaffected. Also untouched: phone, email, the "For employers" block, the page's SEO title and description ([[contact-meta]]), the Home trust line and the H1s ("Mt. Tabor, Portland" in on-page headings is a later item).

**Tooling:** `scripts/work-with-me-employer/contact-address/` = `install-contact-address.php` (allow-list localhost:8090 and staging38 ONLY; ready only when the old text occurs exactly once and the new zero times; applied when the new text occurs exactly once; anything else stops; snapshot of the whole old page content with its sha256; rollback turns only the new text back, keeping other edits) and `push-contact-address.sh` (`--apply | --rollback FILE`; export, verified encrypted off-site copy, apply, purge, public check of the Contact page from the server). Pattern: `availability/install-availability-update.php` reduced to one replacement.

**State:** on branch `feat/2026-10-29-contact-address`; staging38 first, then Ryan looks, then his go for live. LIVE is not enabled and not started.

**Still to do, one at a time (see [[open-items]] section D):** the on-page "Mt. Tabor" headings and trust lines (Home trust line, H1s on the service pages); the map (Ryan said skip it for now); the About "From Chicago to Portland" section; "For Practices" rename; refreshing the "bookings begin November 1, 2026" wording after 2026-11-01.

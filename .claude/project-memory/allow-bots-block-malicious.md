---
name: allow-bots-block-malicious
description: Ryan welcomes bots/crawlers (incl. AI) on thaisolitude.com; security work should target malicious behavior only, never blanket bot-blocking
metadata:
  type: feedback
---

Ryan, 2026-10-17: "I don't have a problem with bots on my site. I allow it. just keep it from being malicious."

**Why:** he wants the site discoverable by search engines and AI crawlers; the goal of security work is stopping attacks, not reducing automated visitors.

**How to apply:** Don't add robots.txt disallows for AI/search crawlers, user-agent blocks, or blanket "bot protection" recommendations. Aim hardening at malicious behavior: login brute force, xmlrpc abuse, scanners probing for backups/config/.git/.env, user enumeration, injection, exposed files. `posture-check.py` already matches this (live robots must stay crawlable with the sitemap). SiteGround's own 202 `sg-captcha` challenge is a host-side rate limit that I did not set and must not change (no site-tools write commands); it only matters for how fast my own tests may run, see [[siteground-bot-challenge]]. Attack-surface items on the list (#4 public users list, #5 cleanup, #6 login/REST limits) should be framed as malicious-actor defenses.

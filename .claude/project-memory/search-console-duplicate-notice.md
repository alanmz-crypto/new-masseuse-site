---
name: search-console-duplicate-notice
description: "Search Console 'Duplicate without user-selected canonical' notice (Oct 2026): only example was the homepage, last crawled Sept 13; live site checked clean; URL Inspection said 'page can be indexed'; Ryan requested indexing"
metadata:
  node_type: memory
  type: project
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-19T01:42:50.389Z
---

Ryan pasted a Google notice (2026-10-18): new reason "Duplicate without user-selected canonical". His only example URL was `https://thaisolitude.com/`, last crawled Sept 13, 2026 (a stale crawl: Google's list lags).

**Checked read-only from outside (no site changes):** all 10 real pages return 200, index/follow, and carry a self-referencing `<link rel="canonical">`; the core sitemap (`/wp-sitemap.xml`) lists exactly those 10; http, www, no-trailing-slash, `/index.php`, `/home/`, `?p=ID`, `?page_id=ID` all 301 to the right page; `?utm_*` and `?amp` keep the clean canonical; search/category pages are noindex; all 38 image attachment pages redirect (none indexable); `wp-json` and oEmbed endpoints send `X-Robots-Tag: noindex`; staging hosts serve `Disallow: /`. The only file link is the résumé PDF (one URL, linked from Contact and Work With Me; a PDF cannot declare a canonical). The production dump of Sept 27 already had SureRank active; the older Wayback snapshot (Dec 2024) is not informative.

**Outcome:** Ryan ran URL Inspection on the homepage: live test said "page can be indexed", and he clicked Request indexing. Nothing on the site was changed.

**Why:** by definition the report lists URLs that declare no canonical when Google fetched them, so the 10 real pages (which all declare one) are unlikely to be the cause; a stale pre-fix crawl or a stray duplicate address fits. **How to apply:** if the notice returns, ask Ryan for the "Google-selected canonical" line from URL Inspection and the full example list; a staging address or a SiteGround bot-check page (HTTP 202 + sg-captcha, see [[siteground-bot-challenge]]) are the two cases that would need action. Do not hammer the live site while testing.

**Also (2026-10-18):** Ryan then saw "Page with redirect" for `http://www.`, `http://` and `https://www.thaisolitude.com/`. EXPECTED, not a fault: http->https and www->apex are 301s (http://www takes 2 hops via https://www; the others 1), HSTS on. Google lists redirecting URLs as not indexed by design; do not "fix" or validate. Collapsing the 2-hop chain would mean touching SiteGround redirect settings: not worth it.

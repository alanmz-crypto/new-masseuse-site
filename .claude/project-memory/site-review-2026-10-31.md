---
name: site-review-2026-10-31
description: Ryan 2026-10-31 ("Take a look around this website for improvements ... find things we missed") - read-only review of live from a server-side dump and screenshots; the report, its top ten, the two new read-only tools, what was deliberate, and what a next session prepares once Ryan picks
metadata:
  type: project
---

Ryan, 2026-10-31 (cloud session): "Thaisolitude.com. Take a look around this website for improvements that can be made. Consider that you should be
able to find things we missed."

**What was done (nothing on any site changed):** the cloud VM cannot reach thaisolitude.com (network policy denies the host; WebFetch and curl fail,
external fetchers such as Tavily return stale cached copies), so live was captured from the server by two read-only Actions runs:
`site-dump-live-2` (12 sitemap pages as served, 30 probes, 60 asset HEADs, read-only WP-CLI listings incl. stored page content, SureRank rows, snippets,
Additional CSS) and `site-shots-live-1` (24 full-page screenshots printed inline as base64, because the run artifact on Azure blob storage is out of
reach). Eight review lenses produced 139 findings; the P1/P2 ones were verified by hand against the capture and the notes. Report:
`docs/reviews/2026-10-31-site-review.md` (top ten first, then by theme, lanes at the end). Four more lenses (research integrity, outbound links,
visual pass, stored content/CSS) were cut short by the account's usage limit twice; a second run was started and its results, if any, get appended.

**Top findings (all need Ryan's word; nothing prepared yet):** the four service pages and Services end with no call to action; Chair and Corporate
never link to each other and Contact has no corporate path; Home's three "Services" card buttons still go to the overview (Fix Checklist P1 since
Oct 4) and the service pages receive one internal link each; "Relief" chip on Home is an evidence-tier word the Home gate does not test; WordPress
Address is still http:// (REST index `"url":"http://..."`), which is why the corporate og:image is http; the Services page lacks the availability
sentence and lists the bookable offers 4th/5th; titles of Therapeutic/Deep Tissue/Chair omit Portland and the corporate title "Corporate Chair
Massage" competes with the Chair page; Privacy/Accessibility titles and descriptions are auto-cut; five unconfirmed facts in the chair/Thai copy
(10-20 vs 10-15 minutes, pregnancy "Often yes", table for therapeutic/deep tissue, Thai massage at events, session lengths) need Butsaba.

**Deliberate (do not re-report):** map on Mt. Tabor; "Ask about a session"; "For Practices"; (312) number; no chair Home card/menu item; the
"Ryan Personal Automation" pages (noindex + unlisted via snippet 1256); fonts; page cache off until the strip flips; Work With Me "From November 1"
lines (employment start, not bookings).

**Tools added:** `seo-scan/dump-site.sh` (server-side curl of every sitemap page + probes + `wp` listings, one base64 tar.gz in the report; the
server's shell has no /dev/fd, so no process substitution) and `tools/shots-inline.mjs` (12 pages x 2 widths, JPEG base64 lines; exit 2 on the bot
challenge). Both ran clean on live in one try (24 page loads from a runner did not trip SiteGround).

**Lesson:** the Workflow tool caps concurrency at CPUs-2 = 2 on this VM, and twelve finder agents reading 200 KB pages exhaust the 5-hour usage
window; batch the verification and point agents at the derived files (`site/x/*.txt`, `.tsv`, `.meta.json`), not the raw HTML.

**Next:** Ryan picks from the top ten; a session then builds one installer per pick (exact-anchor insertions, rehearse, staging38, live on his go)
and sends Butsaba the one message in section 9. After 2026-11-14: the past-tense installer for the ten "bookings begin" sentences and the cache switch.

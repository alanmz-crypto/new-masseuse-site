# Site op `review-b-seo-staging38-20261101`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `seo-scan/scan-seo.sh`  |
| lane | ops (site-ops.yml) |
| commit | `275f280e4bcf39d157defb3c367c9dbbfb26e74e` on `codex/2026-11-01-review-bundle-b` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41290538959 |
| started | 2026-11-01 08:50:47 UTC |

## scan-seo.sh (S_TARGET=staging38): PASS

```text
staging38 is www/staging38.thaisolitude.com/public_html   (read-only SEO scan)
-- /robots.txt -> HTTP 200
   User-agent: *
   Disallow: /

-- /wp-sitemap.xml -> HTTP 200
   <?xml version="1.0" encoding="UTF-8"?>
   <?xml-stylesheet type="text/xsl" href="https://staging38.thaisolitude.com/wp-sitemap-index.xsl" ?>
   <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>https://staging38.thaisolitude.com/wp-sitemap-posts-page-1.xml</loc></sitemap></sitemapindex>

-- /wp-sitemap-posts-page-1.xml -> HTTP 200
   <?xml version="1.0" encoding="UTF-8"?>
   <?xml-stylesheet type="text/xsl" href="https://staging38.thaisolitude.com/wp-sitemap.xsl" ?>
   <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://staging38.thaisolitude.com/</loc><lastmod>2026-11-01T08:46:13+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/about/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/services/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/contact/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/deep-tissue-massage/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/therapeutic-massage/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/thai-massage/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/work-with-me/</loc><lastmod>2026-10-30T15:15:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/privacy-policy/</loc><lastmod>2026-10-18T00:14:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/accessibility/</loc><lastmod>2026-10-24T20:51:14+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/corporate-massage-portland/</loc><lastmod>2026-11-01T02:10:51+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/chair-m
Bundle B detected: exact metadata, social copies and entity links are required.
Bundle B share card: https://staging38.thaisolitude.com/wp-content/uploads/2026/11/thai-solitude-share-card.png; attachment 1314; 1200x630; approved SHA256 MATCH

== https://staging38.thaisolitude.com/  (HTTP 200, 223048 bytes)
  title (58): Thai Solitude | Thai & Therapeutic Massage in Portland, OR
  description (156): Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.
  canonical: https://staging38.thaisolitude.com/
  robots meta: index, follow
  H1 (1): Thai & Therapeutic Massage with Butsaba Dusrai, Oregon LMT
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 9, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/about/  (HTTP 200, 206915 bytes)
  title (33): About Butsaba Dusrai | Oregon LMT
  description (124): Meet Butsaba Dusrai, an Oregon licensed massage therapist. Learn about her background and massage services at Thai Solitude.
  canonical: https://staging38.thaisolitude.com/about/
  robots meta: index, follow
  H1 (1): Meet Butsaba Dusrai
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, AboutPage, WebSite, LocalBusiness, PostalAddress, City
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/accessibility/  (HTTP 200, 197175 bytes)
  title (29): Accessibility | Thai Solitude
  description (146): What has been checked on thaisolitude.com for accessibility (WCAG 2.2 A and AA, October 2026), its known limitations, and how to report a barrier.
  canonical: https://staging38.thaisolitude.com/accessibility/
  robots meta: index, follow
  H1 (1): Accessibility
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 2, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/chair-massage/  (HTTP 200, 254037 bytes)
  title (54): Chair Massage in Portland | Butsaba Dusrai, Oregon LMT
  description (150): Chair massage in Portland with Butsaba Dusrai, LMT: short, seated, fully clothed sessions for workplaces and events, with pressure adjusted as you go.
  canonical: https://staging38.thaisolitude.com/chair-massage/
  robots meta: index, follow
  H1 (1): Chair Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/contact/  (HTTP 200, 208014 bytes)
  title (35): Contact Butsaba Dusrai | Oregon LMT
  description (133): Text (312) 539-4328 or email butsabad@gmail.com to ask about corporate and group massage in Portland with Butsaba Dusrai, Oregon LMT.
  canonical: https://staging38.thaisolitude.com/contact/
  robots meta: index, follow
  H1 (1): Get in Touch with Thai Solitude
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, ContactPage, WebSite, LocalBusiness, PostalAddress, City
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 1; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/corporate-massage-portland/  (HTTP 200, 231116 bytes)
  title (57): Corporate & Group Massage in Portland, OR | Thai Solitude
  description (162): On-site chair massage for employee appreciation days and workplace events in Portland, with table massage by arrangement at suitable host venues. Request a quote.
  INFO exact approved 162-character Corporate description
  canonical: https://staging38.thaisolitude.com/corporate-massage-portland/
  robots meta: index, follow
  H1 (1): Corporate & Group Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/deep-tissue-massage/  (HTTP 200, 243850 bytes)
  title (53): Deep Tissue Massage in Portland | Butsaba Dusrai, LMT
  description (129): Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.
  canonical: https://staging38.thaisolitude.com/deep-tissue-massage/
  robots meta: index, follow
  H1 (1): Deep Tissue Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 5, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/privacy-policy/  (HTTP 200, 198934 bytes)
  title (30): Privacy Policy | Thai Solitude
  description (150): How Thai Solitude handles your information online and as a client of Butsaba Dusrai, LMT: no forms, no tracking, nothing sold or shared for marketing.
  canonical: https://staging38.thaisolitude.com/privacy-policy/
  robots meta: index, follow
  H1 (1): Privacy Policy
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 2, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/services/  (HTTP 200, 218222 bytes)
  title (45): Massage Services | Butsaba Dusrai, Oregon LMT
  description (157): Thai, therapeutic, deep tissue and chair massage in Portland with Butsaba Dusrai, LMT, plus corporate and group events. Every session starts with your goals.
  canonical: https://staging38.thaisolitude.com/services/
  robots meta: index, follow
  H1 (1): My Massage Services
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 9, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/thai-massage/  (HTTP 200, 240371 bytes)
  title (53): Thai Massage in Portland | Butsaba Dusrai, Oregon LMT
  description (139): Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.
  canonical: https://staging38.thaisolitude.com/thai-massage/
  robots meta: index, follow
  H1 (1): Thai Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 5, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/therapeutic-massage/  (HTTP 200, 248179 bytes)
  title (53): Therapeutic Massage in Portland | Butsaba Dusrai, LMT
  description (135): Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.
  canonical: https://staging38.thaisolitude.com/therapeutic-massage/
  robots meta: index, follow
  H1 (1): Therapeutic Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 7, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/work-with-me/  (HTTP 200, 226776 bytes)
  title (50): Butsaba Dusrai, LMT | Thai Bodywork | Portland, OR
  description (149): Butsaba Dusrai, Oregon LMT-29905, is based in Portland and open to full-time or part-time roles. Explore her Thai and deep tissue massage experience.
  canonical: https://staging38.thaisolitude.com/work-with-me/
  robots meta: index, follow
  H1 (1): Butsaba Dusrai
  JSON-LD blocks: 2, unparseable: 0, types: Person, SearchAction, BreadcrumbList, ListItem, Organization, WebPage, WebSite, LocalBusiness, PostalAddress, City
  images: 4, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

12 pages scanned; pages not answering 200: 0; expectation failures: 0
```

**RESULT: PASS** - scan-seo.sh succeeded

_finished 2026-11-01 08:50:59 UTC_

# Site op `seo-scan-38-after`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `seo-scan/scan-seo.sh`  |
| lane | ops (site-ops.yml) |
| commit | `e42fe169ad63270fe78368f3563a64c95e19c6fc` on `feat/2026-10-30-seo-fixes` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41120183333 |
| started | 2026-10-30 20:03:14 UTC |

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
   <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://staging38.thaisolitude.com/</loc><lastmod>2026-10-29T17:43:19+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/about/</loc><lastmod>2026-10-16T04:19:47+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/services/</loc><lastmod>2026-10-25T00:33:03+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/contact/</loc><lastmod>2026-10-29T17:20:56+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/deep-tissue-massage/</loc><lastmod>2026-10-30T15:15:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/therapeutic-massage/</loc><lastmod>2026-10-30T15:15:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/thai-massage/</loc><lastmod>2026-10-30T15:15:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/work-with-me/</loc><lastmod>2026-10-30T15:15:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/privacy-policy/</loc><lastmod>2026-10-18T00:14:11+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/accessibility/</loc><lastmod>2026-10-24T20:51:14+00:00</lastmod></url><url><loc>https://staging38.thaisolitude.com/corporate-massage-portland/</loc><lastmod>2026-10-30T15:15:11+00:00</lastmod></url></urlset>


== https://staging38.thaisolitude.com/  (HTTP 200, 219215 bytes)
  title (58): Thai Solitude | Thai & Therapeutic Massage in Portland, OR
  description (156): Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.
  canonical: https://staging38.thaisolitude.com/
  robots meta: index, follow
  H1 (1): Thai & Therapeutic Massage with Butsaba Dusrai, Oregon LMT
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 9, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/about/  (HTTP 200, 196600 bytes)
  title (33): About Butsaba Dusrai | Oregon LMT
  description (124): Meet Butsaba Dusrai, an Oregon licensed massage therapist. Learn about her background and massage services at Thai Solitude.
  canonical: https://staging38.thaisolitude.com/about/
  robots meta: index, follow
  H1 (1): Meet Butsaba Dusrai
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, AboutPage, WebSite, LocalBusiness, PostalAddress
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/accessibility/  (HTTP 200, 193288 bytes)
  title (29): Accessibility - Thai Solitude
  description (160): Thai Solitude aims to make this website accessible and usable for everyone. I’ve reviewed the site for accessibility and will continue to make improvements as
  canonical: https://staging38.thaisolitude.com/accessibility/
  robots meta: index, follow
  WARN missing og:image
  H1 (1): Accessibility
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 2, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/contact/  (HTTP 200, 202249 bytes)
  title (35): Contact Butsaba Dusrai | Oregon LMT
  description (133): Text (312) 539-4328 or email butsabad@gmail.com to ask about corporate and group massage in Portland with Butsaba Dusrai, Oregon LMT.
  canonical: https://staging38.thaisolitude.com/contact/
  robots meta: index, follow
  H1 (1): Get in Touch with Thai Solitude
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, ContactPage, WebSite, LocalBusiness, PostalAddress
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 1; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/corporate-massage-portland/  (HTTP 200, 217553 bytes)
  title (55): Corporate Chair Massage in Portland, OR | Thai Solitude
  description (147): On-site chair massage for employee appreciation days and workplace events in Portland, with table massage at suitable host venues. Request a quote.
  canonical: https://staging38.thaisolitude.com/corporate-massage-portland/
  robots meta: index, follow
  H1 (1): Corporate & Group Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 3, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/deep-tissue-massage/  (HTTP 200, 233880 bytes)
  title (48): Deep Tissue Massage | Butsaba Dusrai, Oregon LMT
  description (129): Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.
  canonical: https://staging38.thaisolitude.com/deep-tissue-massage/
  robots meta: index, follow
  H1 (1): Deep Tissue Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 5, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/privacy-policy/  (HTTP 200, 195034 bytes)
  title (30): Privacy Policy - Thai Solitude
  description (158): Thai Solitude is the practice of Butsaba Dusrai, LMT (Oregon License LMT-29905). This page explains what happens to your information, first online and then as
  canonical: https://staging38.thaisolitude.com/privacy-policy/
  robots meta: index, follow
  WARN missing og:image
  H1 (1): Privacy Policy
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 2, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/services/  (HTTP 200, 207054 bytes)
  title (45): Massage Services | Butsaba Dusrai, Oregon LMT
  description (118): Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Portland. Every session starts with your goals.
  canonical: https://staging38.thaisolitude.com/services/
  robots meta: index, follow
  H1 (1): My Massage Services
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 8, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/thai-massage/  (HTTP 200, 230367 bytes)
  title (53): Thai Massage in Portland | Butsaba Dusrai, Oregon LMT
  description (139): Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.
  canonical: https://staging38.thaisolitude.com/thai-massage/
  robots meta: index, follow
  H1 (1): Thai Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 5, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/therapeutic-massage/  (HTTP 200, 238209 bytes)
  title (48): Therapeutic Massage | Butsaba Dusrai, Oregon LMT
  description (135): Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.
  canonical: https://staging38.thaisolitude.com/therapeutic-massage/
  robots meta: index, follow
  H1 (1): Therapeutic Massage in Portland
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 7, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

== https://staging38.thaisolitude.com/work-with-me/  (HTTP 200, 222970 bytes)
  title (50): Butsaba Dusrai, LMT | Thai Bodywork | Portland, OR
  description (149): Butsaba Dusrai, Oregon LMT-29905, is based in Portland and open to full-time or part-time roles. Explore her Thai and deep tissue massage experience.
  canonical: https://staging38.thaisolitude.com/work-with-me/
  robots meta: index, follow
  H1 (1): Butsaba Dusrai
  JSON-LD blocks: 2, unparseable: 0, types: SearchAction, BreadcrumbList, ListItem, Organization, Person, WebPage, WebSite, LocalBusiness, PostalAddress
  images: 4, without an alt attribute: 0
  leftovers: 'Mt. Tabor' in visible text 0, anywhere in the HTML 0; 'November 1,' in visible text 0

11 pages scanned; pages not answering 200: 0
```

**RESULT: PASS** - scan-seo.sh succeeded

_finished 2026-10-30 20:03:25 UTC_

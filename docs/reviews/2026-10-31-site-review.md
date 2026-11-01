# Thai Solitude site review, 2026-10-31

**What this is.** Ryan asked for a fresh look at thaisolitude.com for improvements that earlier passes missed. This is a read-only review:
nothing on any site was changed. Every item names the page and the exact text, says why it matters, proposes the smallest change, and says who
decides. Nothing goes live without Ryan, and most items touch established copy, so they need his OK first (his rule: add, never subtract
without asking).

**How it was done.** The cloud session cannot reach thaisolitude.com (the environment's network policy denies the host), so the live site was
captured from the server itself by two read-only GitHub Actions runs on 2026-10-31 (12:46 and 12:49 UTC): `site-dump-live-2` (every sitemap
page exactly as a visitor gets it, 30 probe paths, 60 asset headers, and read-only WP-CLI listings of pages, media, menus, snippets, CSS and
options) and `site-shots-live-1` (full-page screenshots of the 12 pages at 1280 px and 390 px). Eight reviewers then each took one lens over that
material (regulated copy, facts and consistency, search, accessibility, visitor journey, performance, security and hygiene, language), producing
139 findings; I verified the P1 and P2 items against the captured files myself and checked each against the project notes (open items, the
Fix Checklist, the decisions register). Four more lenses (research-section integrity, every outbound link, a visual pass over the screenshots,
and the stored content and CSS) are in a second run and will be added to this file when they finish. Two new read-only tools were added for
this and stay in the repo: `scripts/work-with-me-employer/seo-scan/dump-site.sh` and `scripts/work-with-me-employer/tools/shots-inline.mjs`.

**How to read it.** P1 = hurts visitors, trust, search or compliance before the November 14 launch. P2 = should be fixed, no rush. P3 = polish
and housekeeping. "Decider" says whose call it is. "Known" says whether it was already on a list (open-items section letter, Fix Checklist) or is
new. "Prepare now" means a cloud session can build and rehearse the installer on staging38 as soon as Ryan says go.

## The ten decisions that matter most (for Ryan, from the phone)

1. **Give every long page a next step.** The Thai, Therapeutic, Deep Tissue and Chair pages (3,300 to 4,800 words each) and the Services page
   end at the footer with no button to a quote or a question; the only booking link is the strip at the very top. Add one closing block
   ("Next step", the existing sentence "Corporate and group massage bookings begin November 14, 2026.", two buttons: Request a corporate massage
   quote, Ask a question). Additive. [1.1]
2. **Connect the two pages that sell what is bookable now.** The Chair Massage page names "the Corporate and Group Massage page" three times
   without a link, the Corporate page never links to the Chair page, and the Contact page has no corporate path at all (a known open item).
   Additive links and one Contact card. [1.2, 1.3]
3. **Point the Home service cards at their own pages.** Three cards say "Services" and all go to the overview; the Therapeutic, Deep Tissue and
   Thai pages are linked from exactly one place on the whole site (the Services page). Edit of established links, on the Fix Checklist since
   October 4. [2.1]
4. **Five facts for Butsaba before November 14** (one message): chair session length (the page says "10 to 20 minutes", its FAQ and the corporate
   page say 10 to 15); the chair FAQ's "Often yes, with changes" for pregnant clients; the chair FAQ's statement that therapeutic and deep tissue
   sessions are on a table (the two pages themselves never say it); whether Thai massage on a mat is offered at corporate or group events (the
   Thai FAQ sends people to the corporate page, which offers chair and table only); session lengths for Therapeutic and Deep Tissue (only Thai
   says 60 or 90 minutes). [3.1 to 3.5]
5. **"Relief" on the Home page.** The hero chip "Relief" is on the house list of evidence-only words (allowed only inside a research section);
   the Home gate has a narrower list, so it never flagged it. Suggested: "Ease". One word, Ryan's OK. [4.1]
6. **WordPress Address is still http://.** The REST index shows `"url":"http://thaisolitude.com"`; that is why the corporate page's share image
   is advertised as http:// and why WP-CLI prints http menu links. Two fields in Settings > General, reversible. [5.1, 5.2]
7. **The Services page assumes individual bookings.** It is the one service page without the availability answer, and it lists the two bookable
   offers fourth and fifth, below three services that open in summer 2027; its jump-link row does not name them. Add the sentence (additive);
   reorder only if Ryan wants. [3.6, 1.4]
8. **Search titles.** Therapeutic, Deep Tissue and Chair titles omit "Portland" (Thai has it); the corporate page's title says "Corporate Chair
   Massage", which now competes with the Chair Massage page; Privacy Policy and Accessibility have auto-generated titles and descriptions cut
   off mid-sentence. Six SureRank rows. [6.1 to 6.3]
9. **After November 14:** refresh the ten "bookings begin November 14, 2026" sentences (known open item D; now four service pages plus Corporate
   and Work With Me), and turn the SiteGround page cache on once the strip has flipped (every visit is a full PHP render today). [3.7, 7.1]
10. **At a computer (from November 7, known):** rotate credentials and turn on 2FA; and four deletions only Ryan can do: readme.html (back after
    the October 28 core update), ten draft WPCode snippets (cal.com embeds for another account among them), the two inactive plugins, and an
    unknown 90 KB `Default.html` in the web root. [8.1 to 8.4]

## 1. The booking path (P1)

**1.1 Four service pages and Services end with no call to action.** `/thai-massage/`, `/therapeutic-massage/`, `/deep-tissue-massage/`,
`/chair-massage/` (3,334 to 4,836 words; 8,800 to 10,600 px tall on a phone) and `/services/`. Apart from the header and footer menus, the body
links are the strip, the jump links and the research citations; no link to Contact or to the corporate page anywhere below the strip, and on
phones the header's "Get in Touch" button is inside the hamburger menu. *Proposal:* one closing block before the footer on the five pages (after
the research panels; on Services after "After the Session"): eyebrow "Next step", the sentence each FAQ already uses ("Corporate and group
massage bookings begin November 14, 2026."), two outlined buttons in the Work With Me "For clients" style: [Request a corporate massage quote]
to `/corporate-massage-portland/` and [Ask a question] to `/contact/`. Pure addition. *Decider:* Ryan approves the block; prepare now. *Known:* new.

**1.2 Chair Massage and Corporate never link to each other.** The chair FAQ says three times "The Corporate and Group Massage page explains ..."
as plain text; the corporate page says "chair massage" without a link to `/chair-massage/`; site-wide the only in-body link to the chair page is
the Services row. *Proposal:* (a) wrap the existing words "Corporate and Group Massage page" in a link in the three chair FAQ answers (adds
markup, changes no words); (b) on the corporate page add one sentence after "Planning a five-hour block? ...": "Read more about chair massage:
what it is, what to expect and what the research says." with the link. *Decider:* Ryan's yes for (a); (b) is additive; prepare now. *Known:* new.

**1.3 The Contact page gives a corporate buyer nothing.** Its search description promises "to ask about corporate and group massage", but the
body is Phone, Email, Address and "Hiring a massage therapist?"; the header button, Home's "Ask about a session" and Work With Me's "Ask a
question" all land here. *Proposal:* a fourth card after Address: label "Corporate & group massage", one line "Request a quote for an employee
appreciation day or workplace event.", the same prefilled mailto button the corporate page uses, and "Learn more" to the corporate page; add
"Corporate quote" to the page's jump links (the row holds four; "Map" could give way). *Decider:* Ryan (his open item); prepare now. *Known:*
open-items D ("a corporate button on the Contact page").

**1.4 Services lists the bookable offers fourth and fifth and leaves them out of the jump row.** Order today: Therapeutic, Deep Tissue, Thai,
Chair, Corporate & Group, Your Session; the jump links read "Therapeutic massage · Deep tissue massage · Thai massage · What to expect". On a
phone the Chair row starts about 2,700 px down. *Proposal (edit, Ryan's OK):* move the Chair and Corporate rows above Therapeutic and make the
jump row "Corporate & group · Chair massage · Thai massage · What to expect". Additive alternative: only the closing block from 1.1. *Known:* new.

**1.5 Corporate page on phones: 5,900 px between the two quote buttons.** The quote button sits at the top and at the very bottom of a 7,300 px
page; this page has no strip and no phone number. *Proposal:* a mobile sticky bar like the one Work With Me already has (its CSS can be copied):
[Request a quote] and [Email butsabad@gmail.com]. Additive. *Decider:* Ryan (design); prepare now. *Known:* new.

## 2. Internal links (P1)

**2.1 Home cards: three "Services" buttons go to the overview via a relative link.** Under Therapeutic, Deep Tissue and Thai the button is
`href="services/"`, label "Services", three times; the corporate card goes to its own page in one tap. "about/" and "contact/" are relative
too (SureRank's own link check resolved them to `/home/contact/`). *Proposal:* `/therapeutic-massage/`, `/deep-tissue-massage/`,
`/thai-massage/` with labels "Therapeutic massage", "Deep tissue massage", "Thai massage" (or keep the label and change only the link); make
the two other links root-relative. *Decider:* Ryan (edits established links). *Known:* Fix Checklist, Home P1 and P2, since October 4.

**2.2 The four deepest pages get one link each.** Across all 792 links on the site, `/thai-massage/`, `/therapeutic-massage/` and
`/deep-tissue-massage/` are linked once (from Services) and `/chair-massage/` twice (Services); nothing from Home, the menu, the footer, About,
Contact or the corporate page. The corporate page receives links from 11 pages. Search engines weigh internal links; a Search Console request
does not change that. *Proposal:* (a) a footer services menu (Appearance > Menus, new menu; Astra footer builder, Menu element) with the five
services; (b) one "Other services:" line at the end of each service page before the research heading, linking the other four. *Decider:* Ryan
for the footer; (b) prepare now. *Known:* new.

## 3. Facts, consistency and the regulated copy

**3.1 Chair session length (P1).** The chair page says "A session usually lasts 10 to 20 minutes."; its own FAQ says "about 10 to 15 minutes per
person", and the corporate page says "about 10-15 minutes per person" and "About 12-15 short chair-massage appointments" for a 3-hour block.
*Proposal:* Butsaba picks the figure; the odd one out is changed (chair page builder, staging38 first). *Decider:* Butsaba. *Known:* the chair
note flags the 10-20 figure as unconfirmed.

**3.2 Chair FAQ on pregnancy (P1).** "Can I Have a Chair Massage If I'm Pregnant or Have a Health Condition? Often yes, with changes. ..." is an
assurance to pregnant clients that Butsaba has not stated (open-items E lists pregnancy among facts nobody has stated). *Proposal:* Butsaba
confirms; until then drop the first two words so the answer is only the intake sentence ("Tell me before I start about any health conditions,
medications, pregnancy, recent injury or surgery. I may adjust the work, leave an area out, or suggest you check with your clinician first.").
*Decider:* Butsaba. *Known:* open-items E.

**3.3 What clients lie on (P2).** The chair FAQ states "Therapeutic massage and deep tissue massage take place on a table, last longer and can
cover the whole body. Thai massage is done on a mat ..."; the Therapeutic and Deep Tissue pages themselves never mention table, mat, clothing or
draping, and the Thai page's pictures are captioned as table scenes while its FAQ says mat. *Proposal:* Butsaba confirms table/mat/clothing per
service; then add the Thai page's "What Will I Lie On?" / "What Should I Wear?" FAQ pair to Therapeutic and Deep Tissue (additive). If she does
not confirm, the chair sentence drops "take place on a table,". *Decider:* Butsaba. *Known:* open-items E.

**3.4 Thai FAQs send people to an offer that does not include Thai massage (P2).** "How Do I Book a Session? ... The Corporate and Group Massage
page explains how to request a quote." The corporate page offers chair massage and, by arrangement, table massage; its quote email asks for
"chair massage, table massage, or not sure yet". Neither "Thai" nor "mat" appears there. *Proposal:* if Thai massage on a mat can be offered at
events, add one sentence to the corporate "Table Massage at Suitable Venues" block and "Thai massage on a mat" to the quote email's massage-type
line; if not, the Thai FAQ answer should say events use chair or table massage and Thai sessions open with individual appointments (summer
2027). *Decider:* Butsaba (the fact), then Ryan. *Known:* new.

**3.5 Session lengths (P2).** Only the Thai page publishes "Sessions are 60 or 90 minutes."; Therapeutic and Deep Tissue say nothing; the
corporate page sets lengths "in your quote". The Fix Checklist still has "Confirm 60 or 90 minutes with Butsaba" open. *Proposal:* Butsaba
confirms per service; add the FAQ to the two pages; on Thai append "at corporate and group events the session length is set in the quote."
*Decider:* Butsaba. *Known:* Fix Checklist (Therapeutic P1).

**3.6 The Services page is the one service page without the availability answer (P1).** "Every session begins with a conversation about what you
want ..." and "If you are thinking about another session, I am happy to talk it through with you." read as bookable now; the Therapeutic FAQ's
"You don't need a particular area of concern to book." sits above that page's availability answer. *Proposal (additive):* one sentence at the end
of the Services "Your Session" block: "Right now I offer corporate and group massage only, at suitable Portland venues; individual appointments
at a dedicated Thai Solitude location are planned for summer 2027." (the wording the service pages already use). *Decider:* Ryan; prepare now.
*Known:* new.

**3.7 Ten "bookings begin November 14, 2026" sentences go stale on the 14th (P2, after the flip).** FAQ pairs on Thai, Therapeutic, Deep Tissue
and Chair, the corporate hero line and the Work With Me "For clients" block. The strip flips itself to "Now accepting ..."; nothing else does.
*Proposal:* one installer, run on or after November 14: "with bookings beginning November 14, 2026." becomes "since November 14, 2026." and
"Corporate and group massage bookings begin November 14, 2026." becomes "Corporate and group massage bookings are open (since November 14,
2026)."; the checkers accept both. *Decider:* Ryan; prepare now, run after the 14th. *Known:* open-items D (its text still said November 1 and
three pages; corrected in this change).

**3.8 Work With Me "Start - From November 1" and "in person from November 1" (P2).** No year; from today they read as future. The booking-date
note says these are her employment start, not client bookings, and were left on purpose pending Ryan's word. *Proposal:* "Start - Available now
· days, evenings and weekends" and "Interviews - By phone, video call or in person". *Decider:* Ryan. *Known:* booking-date note.

**3.9 Table massage: firm in two places, conditional on the page (P2).** Home card and the corporate search description say "with table massage
at suitable host venues."; the corporate page says "Table massage by arrangement / Considered when the host can provide a suitable private room
and the details are agreed in advance." *Proposal:* "with table massage by arrangement at suitable host venues." in both places. *Decider:* Ryan.
*Known:* new.

**3.10 "Relief" on the Home page (P1).** Hero chip "Relief" (the heart link reads "Relief: Therapeutic massage"). `wording_tiers.py` lists
`relief` as an evidence-only word; Home has no research section, so no exemption applies; `home/verify-home.py` lists only "pain relief" and
"relieve pain", so it passes the bare word. *Proposal:* chip "Relief" becomes "Ease" (page block and the heart-links snippet key), and
verify-home.py imports the shared list. *Decider:* Ryan (he mapped the hearts on 2026-10-18). *Known:* Fix Checklist Home P0 (chips) partly done.

**3.11 "Now I am bringing Thai Solitude to Portland" (P2).** Home and About say "bringing" and About adds "I look forward to welcoming clients
here"; Work With Me and Contact say "I am based in Portland"; Ryan on 2026-10-29: "We are in Portland". *Proposal:* a side-by-side draft of
the Home paragraph and the About section with the last sentence "Thai Solitude is now in Portland, where I offer corporate and group massage;
individual appointments at a dedicated location are planned for summer 2027." Apply nothing until he approves. *Decider:* Ryan. *Known:*
open-items D (About "From Chicago to Portland", left for now; the Home paragraph was not listed).

**3.12 Mt. Tabor lives on in two hidden places (P2).** The Contact map still centres on "Mt. Tabor, Portland" (Ryan: "Skip the map", deliberate)
and the LocalBusiness structured data on every page carries `postalCode 97213`, a Mt. Tabor ZIP for premises that do not exist. *Proposal:*
remove the postal code line from snippet 1023 (keep locality and region); the map stays unless Ryan changes his mind. *Decider:* Ryan. *Known:*
open-items D (map); the ZIP is new.

**3.13 Research sentences above the research heading (P3).** On Therapeutic, Deep Tissue and Chair, the "What Is ..." and "Common Reasons ..."
sections cite trials (neck-pain, blood-pressure, oncology-nursing populations) above the "What the Research Says" heading that carries the
house exemption and the "weaker results" caveat. Each sentence was written to its source and Ryan asked for honest wording, so this is a
framing question, not an error. *Proposal (additive):* one sentence after the first research paragraph on each page: "These are general
findings, not medical advice or a promise of any result; the full summaries, including the weaker results, are in the research section below."
*Decider:* Ryan. *Known:* therapeutic note (method sentence).

**3.14 Smaller wording items (P3).** "What Is the Massage Chair Like?" is followed by "A massage chair is a machine with rollers" (retitle "What
Is the Chair Like?"). "I do not offer individual house calls." on four FAQs vs "home visits" on the corporate page. The corporate offer is named
five ways ("Corporate & Group Massage", "Corporate and Group Massage page", menu "Corporate Massage", "Corporate Chair Massage" in the title).
British usages: "agree a realistic schedule", "pack-down", the heading "Nurses, Hospital Staff and Carers" (US: caregivers). Deep Tissue sources:
"talking about?." ; "bi-weekly" is ambiguous (check the abstract: twice weekly or every two weeks). The Deep Tissue health notice appears twice
on the page. Serial commas and Title Case vs sentence case vary between pages; numbers are written "3-hour", "three-hour", "10-15" and "10 to
15". The Accessibility page says "please contact Butsaba" on a first-person site and still names three research pages (Chair is the fourth; the
date should move too). About says "I also offer therapeutic and relaxation massage" and Work With Me names Swedish massage; no service page or
card names either. Contact says "Phone", its description says "Text", the policies say "call" (Butsaba: which is true?). Footer licence line uses
"LMT - Oregon" where the site uses middots. *Decider:* Ryan for each (edits), Butsaba for the phone question.

## 4. Search and sharing (SEO)

**4.1 Titles (P2).** "Therapeutic Massage | Butsaba Dusrai, Oregon LMT", "Deep Tissue Massage | ...", "Chair Massage | ..." omit Portland while
their H1s and the Thai title have it. The corporate title "Corporate Chair Massage in Portland, OR | Thai Solitude" was set before the Chair page
existed and now competes with it; the H1 and menu say Corporate & Group. *Proposal:* "Therapeutic Massage in Portland | Butsaba Dusrai, LMT",
"Deep Tissue Massage in Portland | Butsaba Dusrai, LMT", "Chair Massage in Portland | Butsaba Dusrai, Oregon LMT", "Corporate & Group Massage in
Portland, OR | Thai Solitude". *Decider:* Ryan; prepare now (portland-meta pattern). *Known:* new.

**4.2 Privacy Policy and Accessibility (P2).** Titles use " - " where every other page uses " | "; descriptions are auto-cut: "... will continue
to make improvements as" and "... first online and then as". Their SureRank rows are empty, so this is additive. *Proposal:* "Privacy Policy |
Thai Solitude" with "How Thai Solitude handles your information online and as a client of Butsaba Dusrai, LMT: no forms, no tracking, nothing
sold or shared for marketing." and "Accessibility | Thai Solitude" with "What has been checked on thaisolitude.com for accessibility (WCAG 2.2 A
and AA, October 2026), its known limitations, and how to report a barrier." *Decider:* Ryan approves wording; prepare now. *Known:* new.

**4.3 Services description names three of five services (P3).** "Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in
Portland. Every session starts with your goals." omits chair and corporate. *Proposal:* "Thai, therapeutic, deep tissue and chair massage in
Portland with Butsaba Dusrai, LMT, plus corporate and group events. Every session starts with your goals." *Decider:* Ryan. *Known:* new.

**4.4 Structured data (P2).** Every page's WebSite and WebPage nodes point author, contributor and copyrightHolder at a `#person` node that does
not exist (SureRank's Person entity is not set); two unlinked business nodes exist (SureRank's Organization with phone, logo, founder, and the
snippet's LocalBusiness with address and ZIP but no image, email, description, areaServed or hours); the Organization on About and Contact lacks
the phone and languages the other pages carry; the Organization's slogan and the WebSite description are the old tagline "Professional Deep
Tissue and Thai Massage" (Settings > General > Tagline). *Proposal:* enable SureRank's Person (Butsaba Dusrai, Licensed Massage Therapist, the
About URL, the avatar) with a fixed `#person` id; fill snippet 1023 (image, logo, email, description, areaServed Portland, founder) and drop the
ZIP; set the tagline to "Thai, Therapeutic and Deep Tissue Massage with Butsaba Dusrai, LMT" (or similar); align the About/Contact rows.
*Decider:* Ryan (settings); the snippet can be prepared now. *Known:* new.

**4.5 Share images (P2).** Home's share image is a 512 x 512 AI file (`ComfyUI_01084_.webp`) not shown on Home; most pages use their 396 x 380
hero; Privacy and Accessibility have none; the corporate one is stored as `http://`. *Proposal:* one 1200 x 630 brand image as SureRank's
default share image and Home's; fix the corporate URL to https (one value in the seo-fixes installer). *Decider:* Ryan (a new image file). *Known:*
seo-fixes note (the two missing ones); the http scheme is new.

**4.6 Smaller (P3).** BreadcrumbList schema on every inner page but no visible breadcrumb trail (Astra can show one, or turn the schema off).
`/favicon.ico` answers 200 with an HTML page (upload a real icon file). `http://www.thaisolitude.com` reaches the site in two hops (Site Tools
redirect for www). The search results page looks like a blog ("Read Post »", bylines "Butsaba", no search box) and the 404 page is Astra's
default text with only a search box (a line of links to the main pages would help). `/work-with-me/` has three labels: "For Practices" (menu),
"Résumé & experience" (footer), "Butsaba Dusrai" (H1); the footer label reads like a job seeker's site ("Credentials & experience"?). Image
tooltips still show file names ("butaba web poses"; library alt text raw on ten unused items).

## 5. WordPress settings and security

**5.1 WordPress Address is http:// (P1).** `/wp-json/` shows `"url":"http://thaisolitude.com"` next to `"home":"https://thaisolitude.com"`; the
menu items are stored as http links; the files added over SSH carry http guids; the corporate page's share image is advertised as http://.
SiteGround's HTTPS enforcement hides it on the page, which is why the tools passed. *Proposal:* Settings > General: WordPress Address (URL) and
Site Address (URL) both `https://thaisolitude.com` (take `request.sh backup --target live` first; reversible; staging38 first to see the admin
still loads). *Decider:* Ryan. *Known:* new.

**5.2 Corporate og:image http:// (P2).** `facebook_image_url` in the page's SureRank row is `http://thaisolitude.com/wp-content/uploads/2026/10/
corporate-group-massage.webp`. Social scrapers on an https page may ignore it. *Proposal:* same URL with https (one value in
`seo-fixes/install-seo-fixes.php`, rehearse, staging38, Ryan's live run). *Decider:* prepare now. *Known:* new.

**5.3 Credentials rotation and 2FA (P1, known).** Open-items C, deferred by Ryan to November 7: manual SiteGround backup, new admin password,
SG Security two-factor on, rotate salts, change the DB password. Raised once here as the notes ask. *Decider:* Ryan, at a computer.

**5.4 Housekeeping only Ryan can do (P3, deletions).** `readme.html` answers 200 again after the October 28 core update (SG Security's "delete
readme" is one-off). Ten draft WPCode snippets sit one tap from live (cal.com embeds for another account, placeholder LocalBusiness JSON, "Untitled
Snippet" x2, each set to auto-insert site-wide if activated). Site Kit and Starter Templates are installed but inactive, with import leftovers
under uploads. A 90 KB `Default.html` (October 18) sits in the web root, origin unknown. `php_errorlog` grew to 10 KB on core-update day (read
it once). Ten unused media files (other-project and AI test renders) are public via REST and the uploads folder.

**5.5 Small hardening, all additive (P3).** Settings > Discussion: comments and pingbacks are open by default for any future page or post (no
comments exist; the "Completely Disable Comments" draft snippet could replace the setting). Untick "Show Avatars" so `/wp-json/wp/v2/users` stops
publishing the admin's Gravatar hash. Remove the RSD, oEmbed discovery and shortlink tags from the head (one snippet). Add a
`/.well-known/security.txt`. HSTS could add `preload` (one-way door, Ryan's call). The CSP still carries `'unsafe-inline'` for scripts and
styles; a nonce version can be rehearsed later. The REST pages list still exposes the three "Ryan Personal Automation" titles (extend snippet
1256). The résumé PDF is a crawlable static file (it carries the licence, phone and email that are on the Contact page anyway): leave it, or a
robots line for the one file. The business email is a personal Gmail on every page and in the structured data; a domain mailbox
(hello@thaisolitude.com, forwarded) is a decision for Ryan. Astra 4.14.2 is pending (theme auto-update is off; backup first).

## 6. Accessibility and semantics

**6.1 Nineteen headings that are not headings (P2).** The "How I Work" rows on Home, the four "What to Expect" step titles on Services, Thai,
Therapeutic, Deep Tissue and Chair, and the corporate page's three card titles are `<p class="uagb-heading-text">` styled as headings, so screen
readers' heading navigation skips them. The Services "Chair Massage" and "Corporate & Group Massage" row titles are links, not H3s like the other
rows, and Services jumps from H1 to H3s with its first H2 last. *Proposal:* markup only, no copy change: set the HTML tag of those Spectra
heading blocks to h3 (and one screen-reader-only H2 above the Services rows), with one CSS rule so the sizes stay exactly as they are.
*Decider:* Ryan (edits block markup on seven pages); prepare now. *Known:* new.

**6.2 Therapeutic page has no "Questions About Therapeutic Massage" heading (P2).** Thai, Deep Tissue, Chair and Corporate have one; on
Therapeutic the accordion sits under "Who Can Benefit ...". *Proposal:* insert the same H2 above the first question. Additive; prepare now.

**6.3 Keyboard focus ring at 2.4:1 (P2).** Buttons, menu, jump links, heart links, strip and corporate CTA use `outline: ... var(--ts-accent)`
(gold #C8963E on cream), below the 3:1 non-text contrast minimum. *Proposal:* `var(--ts-accent-deep)` in those five outline rules (6.3:1 on
cream; the dark palette already remaps it). CSS-only installer. *Decider:* Ryan (visible only while an element has keyboard focus); prepare now.

**6.4 FAQ accordions use tab roles (P2).** Every Spectra FAQ is `role="tablist"` with items `role="tab"` and no expanded state (49 items on 8
pages); the native `<details>` research panels are fine. *Proposal:* an additive footer script that swaps the roles for button + aria-expanded
+ aria-controls, no visual change. *Decider:* prepare now.

**6.5 Smaller (P3).** The Contact map `<embed>` is named "Google Map for " (blank) and carries `loading="lazy"`, which browsers ignore on
`<embed>` (a Custom HTML iframe with a title and real lazy loading is the clean fix; the map itself is Ryan's to keep). The Contact phone number
is a link styled as plain text while the email beside it is underlined. "View résumé (PDF)" opens a new tab and says so only in a tooltip. The
strip has no landmark or name (`role="region" aria-label="Booking notice"` is one attribute). Home has two "About Thai Solitude" links with
different targets (the jump link could say "Learn about Thai Solitude"). The scroll-to-top control is a div with no role. Footer menu shares the
mobile menu's name; the footer widget region is called "Footer Widget 1". The FAQ accordions show an up chevron on closed items and a down
chevron on the open one (the icon attributes are swapped). The accessibility statement must gain "and Chair Massage" and a new date.

## 7. Performance

**7.1 Page cache off (P2, after the flip).** Every page answers `x-cache-enabled: False`, `x-proxy-cache: MISS`; the object cache drop-in is
there, only the page layer is off (so the strip's date flip needs no purge). *Proposal:* after November 14 turn SiteGround Dynamic Cache on
(Site Tools > Speed > Caching, then SG Optimizer > Caching) and re-run the dump to see HITs; a later date change then needs one purge.
*Decider:* Ryan. *Known:* the notes record the cache as off.

**7.2 A 616 KB PNG for a 200 px thumbnail (P2).** Therapeutic's "The same illustration in red tones" is `m0KN9hXm..._-1.png`, 616,299 bytes,
the largest file on the site; its three sibling tints are 20 to 54 KB WebP. *Proposal:* the same picture as a 400 px WebP, pointed at by the
block (server-side resize, no new image). Prepare now.

**7.3 Home cards carry duplicate uploads (P3).** `deep-tissue-massage-illustration.webp` is byte-identical to the Thai page's hero and
`thai-massage-illustration.webp` to the Deep Tissue hero, so the Home Thai card shows the Deep Tissue page's picture and vice versa, and the
Therapeutic card is a third copy of its page's picture. *Proposal:* point the cards at the pages' own attachments (keeps srcset). *Decider:* Ryan.

**7.4 Smaller (P3).** The corporate illustration on Home and Services (and three Work With Me images) has no width/height, so it cannot be
lazy-loaded or sized before it loads. No preload for the two font files every page uses. Home loads the 147 KB Newsreader Italic for one
sentence (Ryan declined font work; a subset font would be invisible to the eye). The Home byline shows the 512 px avatar at 50 px. About 150 KB
of identical CSS is inlined into every page (73 percent of each HTML) and Spectra's per-page stylesheet is render-blocking (183 KB on Home):
nothing safe to change there now; the page cache is the real lever. WordPress's emoji script (3.6 KB) could go. Verified fine: the lazy-load and
font snippets, the dark-mode init, asset caching (one year), HTTP/2, robots, sitemap.

## 8. Visitor experience and design (P2 and P3)

**8.1 About page has no next step.** It ends "I look forward to welcoming clients here." then the footer; no link to Services, Corporate or
Contact, and the licence line appears only in the footer. *Proposal:* add the Home licence line (with the Board link) and three outlined buttons.
Additive; prepare now.

**8.2 Contact hero is a blurred photo.** `ComfyUI_01009_GIMP.webp`, "Blurred image of two people facing each other indoors", is the page's main
image; About's hero is the cartoon avatar; the real portrait from Home is already published. *Proposal:* use the portrait on Contact (and About
if Ryan likes). No new image. *Decider:* Ryan.

**8.3 Buyers' practical questions.** No page says how to pay, whether a deposit or cancellation terms exist, or which days or evenings she is
available; Therapeutic and Deep Tissue say nothing about clothing, draping or length (known E). *Proposal:* one corporate FAQ item and two FAQ
items per service page, with Butsaba's answers only. *Decider:* Butsaba.

**8.4 Smaller.** On phones the strip and the jump links push the H1 to 330 to 400 px (a shorter strip on phones is one edit of the snippet's
echo). Privacy and Accessibility H1s sit about 10 px under the strip on phones (one CSS rule). The Home hearts list breaks into 2+2+2 with blank
bands on phones (row gap). "For Practices" as a menu label does not say what the page is (known D). The Accessibility statement is now behind
the site (Chair). Search results and the 404 page (see 4.6).

## 9. Lanes

- **Ryan, from the phone, no code:** decisions 3, 5, 6, 8 above; Settings > General (https); Settings > Discussion; the deletions (5.4); the
  cache switch after November 14; a reply on 3.11 and on the share image.
- **Butsaba, one message (Claude drafts it):** chair session length, pregnancy at events, table/mat/clothing per service, Thai massage at
  events yes/no, session lengths, how to pay and cancellation, days available, "phone or text".
- **Prepare now on staging38 (installers with exact rollback, rehearsed first), each after a one-word go from Ryan:** 1.1, 1.2(b), 1.3, 2.2(b),
  3.6, 3.7 (run after the 14th), 4.1, 4.2, 5.2, 6.1, 6.2, 6.3, 6.4, 7.2, 8.1, and the snippet edits in 4.4 and 3.12.
- **Needs a computer (known):** 5.3 credentials and 2FA; the age backup key copy.

## 10. Checked and found fine

Licence number and scope line on all 12 pages, the 404 page and the search page; one H1 per page; canonicals, robots and the sitemap; every
page 200; HTTPS redirects from http and www; security headers present (HSTS, CSP, nosniff, frame-options, referrer and permissions policies);
`.env`, `.git`, `wp-config.php.bak` and directory listings refused; xmlrpc blocked; feeds redirected home; author archives redirected; attachment
pages redirected; the strip's date logic (America/Los_Angeles, flips 2026-11-14 00:00); the lazy-load and font snippets do what their comments
say; hero images eager with high fetch priority; the three "Ryan Personal Automation" pages are noindexed and unlisted (deliberate); the
research-section word exemption is honoured on the four pages that have one.

## 11. Material and tools

- Reports on the `ops-log` branch: `reports/2026-10-31/124734-site-dump-live-1.md` (home page and probes), `reports/2026-10-31/124940-site-shots-live-1.md`
  (24 screenshots, inline), and the second dump `site-dump-live-2` (all 12 pages, same day). Decode: see the header of `dump-site.sh`.
- `scripts/work-with-me-employer/seo-scan/dump-site.sh` (read-only server-side dump) and `scripts/work-with-me-employer/tools/shots-inline.mjs`
  (inline screenshots), both runnable through `scripts/ci/request.sh run --target live|staging38`.
- The raw findings with full evidence (139 items, eight lenses) are in the session's scratchpad; the ones that matter are above.

## 12. Added 2026-11-01: research integrity and outbound links

Two of the four late lenses finished (the visual pass over the screenshots and the stored-content/CSS pass are still pending; they get appended
here if they produce anything).

**Research sections: verified sound, with five small fidelity fixes (P3).** Every research sentence on the Thai, Therapeutic, Deep Tissue and
Chair pages was read against its cited abstract (94 distinct PubMed records, the two Cochrane summaries, NCCIH, UNESCO, one PMC full text); no
dead, redirected or retracted source. Five sentences could match their sources more exactly:
- "most serious events followed unusual techniques or massage by laypeople" (Thai [8], Therapeutic [14], Deep Tissue [13]): the 2003 review says
  most *adverse effects* came from exotic techniques or laymen, and serious ones mostly from non-Swedish techniques; the Chair page already
  carries the accurate wording. *Proposal:* use the Chair page's sentence on the three pages.
- "conclusions that massage was better than other active approaches were rare" (Therapeutic [1], Deep Tissue [4]): the 2024 review says
  conclusions *backed by moderate- or high-certainty evidence* were rare. *Proposal:* add the qualifier, as the Chair page has it.
- "found no benefit over the long term" (Therapeutic [15], Deep Tissue [17], the 2015 Cochrane low-back review): the review found no long-term
  benefit over *no treatment*, but a low-quality long-term pain benefit over active controls. *Proposal:* "found no long-term benefit over no
  treatment".
- Therapeutic [20], [7], [27], [24] describe a chair-massage, an Anma, a rhythmical and a sports-massage study as plain "massage". *Proposal:*
  name the style in two or three words each ("weekly 30-minute chair massages", "eight Anma (Japanese) massage sessions", "a single rhythmical
  massage", "a 40-minute whole-body sports massage").
- The two Cochrane reviews are cited in two forms (authors vs "Cochrane evidence summary") and two Thai entries say "et al." while every other
  entry lists all authors. *Proposal:* one form per review, full author lists.
*Decider:* Ryan (edits of established research copy; one installer across the three pages; the builders' content files change in the same commit).

**Outbound links: all 105 resolve (P3).** None dead or redirected; one DOI (Chatchawan 2005) sits behind a publisher bot wall but resolves.
Three things to fix or ask:
- "American Heart Association" in the Work With Me credentials links to the AHA donation homepage; the BLS course page
  (`https://cpr.heart.org/en/courses/basic-life-support-course-options`) explains the certification in one click. *Proposal:* change the href
  only. *Decider:* Ryan.
- Windy City Massage is named and linked twice and offered as a reference; the October 4 checklist never got Butsaba's permission. *Proposal:*
  one line in the Butsaba message; if no, plain text instead of links. *Decider:* Butsaba.
- The one shortened citation title ("What are … talking about?") is deliberate (the house voice gate); leaving it is fine; the alternative is a
  narrow gate exemption for quoted titles. *Decider:* Ryan.
The corporate share image http scheme, the Mt. Tabor map and the Home relative links were confirmed again by this lens (sections 5.2, 3.12, 2.1).

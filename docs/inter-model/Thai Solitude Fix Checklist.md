# Thai Solitude Fix Checklist

Based on an audit run 2026-10-04 of thaisolitude.com (all 8 pages), the resume PDF, the WordPress REST output, and Oregon Board rules. Not legal advice.

**Legend:** P0 = fix before any marketing or booking. P1 = fix before the November launch. P2 = polish. \[ASK\] = needs Butsaba's answer. \[VERIFY\] = check an official source. Replacement copy is a suggestion; every sentence must be true for Butsaba before it goes live.

**Verified staging19 update (October 5, 2026):** A1-A6 have numbered direct-source links and the current qualified sentences match the studied treatment, population, and outcomes. They differ from Ryan's originals, and Ryan has decided to keep the improved staging19 sentences; see `Thai Solitude - Citation Worklist.md` for the exact comparison. Thai Massage has no research claim needing a marker. The status below is based on the current page output and settings, rather than on unchecked plan boxes farther down. Website changes still require an exact proposal, Ryan's approval, and a fresh DB dump for each write.

## Current staging19 chore status, verified October 5, 2026

- **License — partly complete.** Ryan supplied and confirmed Oregon LMT-29905, issued September 28, 2026 and expiring March 31, 2028; do not retry the robot-blocked lookup. The exact footer line renders on the seven publicly accessible page routes checked; automated Contact requests returned HTTP 403, so its rendered footer could not be confirmed. Work With Me displays LMT-29905 with one adjacent “Verify license” link to the Oregon Board's public search form; the served résumé PDF visibly displays the number beside “Licensed Massage Therapist — Oregon.” Off-site advertisement/signature updates were not verified.
- **Public author identity — partly complete.** WordPress user 1 has public display name and nickname `Butsaba` and public slug `butsaba`. The `/author/butsaba/` and `?author=1` requests redirect to the homepage. The public `/wp-json/wp/v2/users` endpoint still returns the Butsaba user record, so enumeration blocking remains pending. No login name, login email, or login setting was changed in this audit; do not change them in follow-up.
- **Page body — partly complete.** Home, About, Services, Therapeutic, Deep Tissue, Thai, Work With Me, and the raw Contact page content contain substantial earlier copy edits. The old (224) phone number did not appear in the seven rendered captures or raw Contact content; Contact lists (312) 539-4328. About title and meta now say Oregon LMT. Ryan's prior-client wording remains; no Portland client history is asserted in the captured About and Work With Me text. The requested first-person “I” voice is still mixed with third-person copy. The approved medical-scope line now renders once below the license in the shared footer on seven reachable routes; Contact still returns HTTP 403, so its rendered footer could not be checked. The approved health note now renders once under the What to Expect heading on Services, Thai Massage, Therapeutic Massage, and Deep Tissue. Deep Tissue also has a separate severe-pain sentence there; the original two Common Reasons sentences remain verbatim. The coming-soon banner remains as Ryan requested.
- **Research citations — inserted; improved wording retained.** Therapeutic A1-A3 and Deep Tissue A4-A6 render markers beside the improved research sentences; no bottom References block appears. The revised text is narrower than the original and source-matched; Ryan decided to keep it. Thai has no research sentence requiring a citation. Services lacks Codex's withdrawn temporary research paragraph and reference block.
- **Copy and metadata cleanup — partly complete.** The seven accessible rendered bodies have much of the high-risk language removed, and the service image alt text mostly describes illustrations. Home's approved SEO title and description now render in title, meta, and generated social fields. Services' approved SEO title now renders in the title and generated social title fields; its description still says “relieve pain” and “recovery,” and an exact replacement is awaiting Ryan's decision. Therapeutic, Deep Tissue, Thai, and Work With Me descriptions still begin with the banner; Thai ends mid-phrase at “What Is.” The sitewide page-text, title, meta, OG, and alt-text pass is unfinished. This site does not use Twitter; Twitter metadata emitted by the SEO plugin was observed but is not an instruction to create or use an account.
- **Images and links — partly complete.** Thai and Deep Tissue currently reuse the same three service images. The Home service-card buttons still say “Services” and point to `services/`. Home also has relative links such as `about/` and `contact/`; the approved root-relative conversion remains open. The Home image description “Illustration of Butsaba giving a back massage” still needs a real-versus-illustration identity check. Media inventory and unique service-image work remain pending. The planned serialized-block chrome cleanup and duplicate-ID work require a local-first rehearsal under the older plan, but Ryan's current boundary forbids localhost changes; they are blocked for this staging19-only pass.
- **Indexing and résumé — partly complete.** `robots.txt` on staging19 says `Disallow: /`, while seven accessible pages emit `index, follow` metadata and WordPress `blog_public` is 1. The served résumé PDF returns HTTP 200 without `X-Robots-Tag: noindex`; its specific noindex chore remains. Work With Me stays in navigation and is indexable in page metadata, consistent with Ryan's dual-purpose site decision. The PDF still lists the two Chicago roles as “Present” and names the salon that the decisions register says to omit; the date and résumé-copy reconciliation remains pending. Production indexing was neither changed nor assessed here.
- **Verifier and other approved or deferred work.** `scripts/credibility-pass/verify-local.php` contains the requested license, forbidden-term, single-phone, and eight-banner assertions, but it was not run here because this pass cannot touch localhost. Its current forbidden-term list includes words deliberately present in the cited A2/A3 research text, so that assertion needs a reviewed exception or wording decision before it can pass on the current copy. Automated Contact requests returned HTTP 403 twice, blocking rendered verification of that page and its metadata; this does not establish what a normal visitor sees. The contact, SEO, privacy/analytics, service offer, location, credential detail, and off-site advertising chores are not complete merely because they appear in the plan. Pricing, session mechanics, room address, permit details, and credentials requiring Butsaba's answers remain deferred. The Google Business Profile is being watched by Ryan and was not changed. No installer, plugin, staging promotion, local site, or production write occurred in this verification pass.

**Client-history correction:** Ryan clarified that Butsaba has had clients, just not in Portland, and said to leave the relevant client-experience copy. Do not describe her as having no client history or imply she has served Portland clients. Verify its location and accuracy against her prior work.

## 0. Rules this list is built on

- OAR 334-010-0025(9): active LMTs must put their license number in all advertisements, including electronic ones.
- OAR 334-010-0025(2)(c): massage treatment does not include making a medical diagnosis.
- OAR 334-040-0010(7) and (14): discipline grounds include false, deceptive or misleading advertising, and practicing or offering to practice beyond the legal scope.
- OAR 334-010-0025(7): notify the Board in writing within 30 days of a change of residence, business, email or mailing address.
- OAR 334-010-0025(6): the abbreviation 'LMT' is for licensed massage therapists.
- Board contact for rule questions: 503-365-8657, OBMT.INFO@obmt.oregon.gov

## 1. Decisions and answers needed first (they block the copy work)

- [ ] P0 \[ASK\] Timeline. The resume says Thai Solitude has operated in Chicago since Jan 2024 and Butsaba is still an LMT at Windy City Massage (Apr 2025 to present). The site says sessions begin in November, and Work With Me puts Windy City in the past tense. Write one true timeline sentence and use it on every page and the resume.
- [ ] P0 \[ASK\] Location. Site says 'Mt. Tabor, Portland'; resume says 'Relocating from Chicago.' What is true today, and from what date? Decide what the site says until she is in Portland.
- [ ] P0 \[ASK\] Illinois license. The resume lists only an Oregon license but shows Chicago LMT employment. Does she hold a current Illinois license? If yes, add it; if not, rewrite the Chicago entries accurately.
- [ ] P0 \[ASK\] Service model. Mobile/outcall only (home or hotel), or any fixed location? Remove 'studio' unless a real, permitted location exists.
- [ ] P1 \[ASK\] Site purpose: client bookings, getting hired, or both? Decide whether Work With Me stays on this site and in the main nav.
- [ ] P1 \[ASK\] Real specialty. About says she specializes in therapeutic and deep tissue; the resume lists Thai massage and Ayurvedic Thai stretching as core and deep tissue as additional training. Lead with what she is actually strongest in (the brand is 'Thai Solitude').
- [ ] P1 \[ASK\] Training details: Still Light Center course name, hours, year, certificate (their site advertises short and online courses); Estelle diploma name and year; Thai Lotus Bodywork dates; permission to name and link Windy City Massage.
- [ ] P1 \[ASK\] Does she have written client testimonials with permission? If not, remove all outcome language (see section 2).
- [ ] P1 \[ASK\] Which phone is correct: (312) 539-4328 or (224) 275-1887? Which email to show? Instagram handle?
- [ ] P1 \[ASK\] Session lengths (Therapeutic page says 60 or 90 min; resume says 50/80/110 at Windy City), pricing, cancellation policy, service area and travel radius, hotel outcall policy.
- [ ] P1 \[ASK\] Clothing, mat/table and draping practice for each service. Oregon has draping rules in OAR 334-010-0025(3).
- [ ] P1 \[ASK\] Real photos available? Which current images are AI-generated?
- [ ] P0 \[VERIFY\] Ryan confirmed LMT-29905, its issue date and expiry from his screenshot. Do not retry the robot-blocked Board lookup. Discipline status was not supplied or independently checked.
- [ ] P0 \[VERIFY\] Ask the Board: does a sole LMT doing outcall at clients' homes/hotels need a facility permit? The Board's 2023 facility letter says facilities owned by Oregon-licensed practitioners are exempt, and a 2013 legislative summary says individual practitioners are exempt; confirm for her case. Also read OAR 334-010-0031 (Outcall Massage) and confirm the address on file.

## 2. Sitewide

- [ ] P0 The license line renders in the shared footer on seven reachable routes; Contact returns HTTP 403 to automated checks, so its footer remains unverified. The “Verify license” link beside LMT-29905 on Work With Me is complete and points to the Board's public search form.
- [ ] P0 Replace every 'Registered Therapist', 'registered massage therapist', 'licensed therapist' and 'Butsaba's Therapy' with 'Oregon Licensed Massage Therapist' or 'LMT'. Known spots: About title tag, About meta description, About H1, Contact title tag, Home 'Founded by licensed therapist...'.
- [ ] P0 Search-and-replace pass (page text, titles, meta descriptions, alt text) for each term; rewrite or delete: healing / heal, proof, measurable, effective / effectiveness, pain relief / relieve pain, pain management, sciatica, headache(s), chronic, adhesions, corrective, root of the restriction, accelerate, restore balance, nervous system, circulation, anxiety, cancer, neurological, disabilities, prevention, lasting, long-term wellness, overall health, real results, studio, our / we / us.
- [ ] P0 Every 'Research suggests / Studies have / Current research' sentence: match a real source to the exact wording. Sol has added numbered direct-source links on Therapeutic and Deep Tissue; verify wording and source fit against the original claims before marking complete. If they do not match, show Ryan the exact proposed change and wait for approval rather than deleting or rewriting automatically.
- [ ] P0 Review client-result statements for accuracy and location. Ryan says Butsaba has had clients, just not in Portland. Do not imply Portland client history or publish an unverified outcome.
- [ ] P1 Pick one voice. Home and Services use 'we/our' (implies a team or studio); About uses 'I'; elsewhere 'Butsaba/she'. Choose one and make all pages match.
- [x] P1 Add one shared scope line to the footer or Services page: 'Massage is not a substitute for medical care. Butsaba does not diagnose or treat medical conditions.' Added to the shared staging19 footer after the license line on October 5; seven reachable routes render it once each. Contact returns HTTP 403, so its rendered footer remains unverified.
- [x] P1 Add the approved 'Before your session' note to Services and all three service detail pages, in the same position. All four render it once below their What to Expect headings. Deep Tissue additionally retains the severe-pain advice in a separate sentence there; Ryan approved moving the older warning out of Common Reasons while keeping its preceding two sentences unchanged. Each page had its own approval and fresh pre-write database dump.
- [ ] P1 Remove the leftover HTML comment blocks ('SERVICE PAGE TEMPLATE - TO ADAPT FOR OTHER SERVICES, SWAP...') and empty `<p>` wrappers from the Thai, Therapeutic and Deep Tissue pages (code editor view).
- [ ] P1 The 'Coming soon' banner is pasted into every page. Turn it into one reusable block/pattern so launch day is a single edit; add a launch-day task to remove it.
- [ ] P2 Replace relative links on Home (`contact/`, `about/`, `services/`) with root-relative `/contact/` etc.

## 3. Page by page

### Home (/)

- [ ] P0 H1 'Relaxation Recovery Relief Mt. Tabor, Portland' -> suggest 'Thai & Therapeutic Massage in Portland, Oregon' with subline 'Mobile massage by Butsaba Dusrai, LMT' (only if mobile-only is confirmed; keep 'Mt. Tabor' only if true).
- [ ] P0 Hero text 'designed to ease tension, accelerate recovery, and restore balance - delivered professionally at your home or in our tranquil studio' -> 'Personalized massage designed to ease tension and help you relax, at your home or hotel in Portland.'
- [ ] P0 'Discover the healing power of massage therapy.' -> 'Relaxation, comfort, and easier movement.'
- [ ] P0 Six chips Pain Relief, Recovery, Mobility, Balance, Comfort, Relaxation -> Relaxation, Comfort, Tension relief, Stress relief, Comfortable movement, Personalized.
- [ ] P0 'Founded by licensed therapist Butsaba Dusrai... We specialize in customized therapeutic and recovery-focused treatments...' -> 'Founded by Butsaba Dusrai, Oregon LMT. Traditional Thai bodywork, therapeutic and deep tissue massage, shaped around your comfort and goals.' Also soften 'every session should leave you feeling restored.'
- [ ] P0 Deep Tissue card 'targeting chronic tightness, adhesions, and persistent pain for lasting muscle relief' -> 'Focused, firmer pressure for areas of tightness such as shoulders, neck and low back.'
- [ ] P1 Therapeutic card 'melt away tension, reduce stress' -> 'ease tension and promote relaxation.'
- [ ] P0 Why Choose: 'relaxation and recovery needs' -> drop 'recovery'. Holistic Approach 'promote overall health and healing' -> delete or 'a blend of techniques tailored to you.' Keep Mobile Service and add the service area.
- [ ] P1 The three service cards each have a button labelled 'Services' going to /services/. Point each to its own page (Therapeutic, Deep Tissue, Thai) with a descriptive label.
- [ ] P1 Alt text: 'butsaba homepage', 'butaba web poses' (typo and filename), 'butsaba avatar' -> real descriptions (e.g. 'Butsaba Dusrai'); label AI art as illustration.
- [x] P0 Home title and description rewritten without recovery/relief/balance. Staging19's rendered title, meta, and generated social fields verified October 5, 2026.

### About (/about/)

- [ ] P0 H1 'Experience Deep Relaxation with Butsaba's Therapy' -> 'About Butsaba Dusrai, LMT'.
- [ ] P0 Title tag 'About Butsaba Dusrai | Registered Therapist in Mt. Tabor, Portland' and the meta description ('registered massage therapist... relieve pain... lasting wellness') -> rewrite.
- [ ] P0 H3 'licensed massage therapist dedicated to your relaxation and recovery' -> 'Oregon licensed massage therapist...'.
- [ ] P0 Paragraph 'I specialize in therapeutic and deep tissue techniques designed to release chronic tension, support recovery, and restore balance to both body and mind. Clients consistently leave with lighter musculature, clearer focus, and renewed vitality - proof that... truly transforms well-being.' -> replace. Suggested: 'Butsaba Dusrai is an Oregon licensed massage therapist (License LMT-29905) offering traditional Thai bodywork, therapeutic massage and deep tissue massage. Each session starts with a conversation about what you would like, and she adapts pressure, pace and movement to your comfort.' Adjust the specialties per section 1.
- [ ] P0 Delete 'Clients regularly experience less tension and improved well-being, demonstrating the effectiveness of my tailored massage services in achieving overall health.'
- [ ] P0 Accordion: 'relaxation meets real results' -> 'relaxation meets care'; 'encourage healing' -> delete; 'live with less pain and more ease' -> 'more ease'; 'effective therapy begins with listening' -> 'good massage begins with listening'.
- [ ] P0 'What began as small-scale, in-home sessions born from a deep passion for hands-on healing has grown into a dedicated practice' -> match the true timeline from section 1; 'hands-on healing' -> 'hands-on care'.

### Services (/services/)

- [x] P0 Services title/OG title -> 'Massage Services | Thai Solitude'; staging19 rendered title and generated social fields verified October 5, 2026.
- [ ] P0 Services meta description 'Treatments to relieve pain and stress, and support recovery' -> rewrite; exact replacement awaits Ryan's decision.
- [ ] P0 Therapeutic blurb 'dissolve everyday tension, ease chronic discomfort, and restore balance to both body and nervous system... measurable muscle recovery. Particularly effective for stress-related tension, headaches...' -> 'Targeted pressure with mindful technique to ease everyday tension and help you relax. A good fit for stress-related tension and tight neck or shoulders.'
- [ ] P0 Deep Tissue blurb 'The result? Restored flexibility, reduced stiffness, and a noticeable shift toward long-term ease. Often chosen for chronic back pain, sciatica...' -> 'Focused, deliberate strokes and firmer pressure for areas of persistent tightness. Often requested for tight shoulders, stiff necks, low-back tension and post-workout soreness.'
- [ ] P0 Thai image alt 'supporting mobility, circulation and overall wellness' -> 'Illustration of a Thai massage session'. All other Services alt text with 'pain management / pain relief' -> plain description.
- [ ] P1 The 'Specialized' card names no service. Name it (e.g. 'Customized session') or delete it.
- [ ] P0 Process steps: 'Customized Treatment Plan... ensuring the most effective approach for relaxation, recovery, and everyday aches' -> 'Personalized Session Plan - shaped around your goals and comfort.' 'Post-Treatment Care and Follow-Up... maintain long-term wellness and reduce recurring soreness' -> 'Aftercare Suggestions - simple tips for the hours after your session; follow-up sessions if you would like them.' ('Treatment' itself is the Board's own word; the problem is 'effective', 'long-term wellness' and 'reduce recurring soreness'.)
- [ ] P1 'Thai Solitude begins with a thorough consultation' -> 'Butsaba begins...'.

### Therapeutic Massage (/therapeutic-massage/)

- [ ] P0 Alt text on all 5 images says 'stress relief, pain management, and muscle recovery' -> describe the image.
- [ ] P0 'addresses patterns of tension throughout the body, helping restore balance, improve mobility' -> 'focuses on areas of tension to help you relax and move more comfortably.'
- [ ] P0 'not only to provide temporary relief but to help clients feel more comfortable, resilient, and connected... long after the session ends' -> 'to help you feel comfortable and at ease.'
- [ ] P0 A1-A3: numbered direct-source citations have been added on staging19. Compare against the original passages in the citation files and check exact source fit; obtain Ryan's approval before changing the wording or placement.
- [ ] P0 'relief from stress, anxiety, mental exhaustion' -> 'stress and mental fatigue'; 'time for the body and nervous system to slow down and recover' -> 'time to slow down'; 'emotional relief' -> delete.
- [ ] P0 'Many of Butsaba's clients seek massage to address persistent muscle tension, headaches, fatigue... stress-related symptoms' -> 'Some people book massage for muscle tension, neck and shoulder tightness, or stress.'
- [x] P0 A2-A3: staging19 uses improved, cited wording. Ryan directed that Codex's already improved sentences remain. The original passages and later evidence are recorded in the Citation Worklist; do not revert or delete the current sentences.
- [ ] P0 FAQ 'adds a corrective element... The result is more lasting' -> 'Relaxation massage keeps pressure light and the pace calm throughout. Therapeutic massage uses more focused technique on areas where you feel tension, within your comfort.'
- [ ] P0 FAQ 'Will I feel the results right away?... meaningful difference... calmer nervous system' -> 'Many people feel more relaxed afterward. How long that lasts varies from person to person.'
- [ ] P0 FAQ 'How often...' ('more frequent sessions... can help reset the pattern more quickly') -> 'Some clients book every few weeks as part of a self-care routine; others come occasionally. Butsaba can suggest a rhythm after your first session.'
- [ ] P0 FAQ 'Appropriate if I have no specific complaint?... Prevention and maintenance are just as valid a reason as pain relief' -> 'Yes. Many people book simply to relax.'
- [ ] P0 FAQ 'Can Therapeutic Massage Help With Tension Headaches? Yes... reduce frequency and intensity' -> delete, or retitle 'Is massage okay if I get tension headaches?': 'Many people with neck and shoulder tightness enjoy massage. Tell Butsaba at intake. If headaches are new, severe or worsening, see a doctor first.'
- [ ] P0 Process: 'Personalized Treatment Plan' -> 'Personalized Session Plan'; 'to extend the benefit and support your recovery' -> 'to help you feel your best afterward.'
- [ ] P1 Confirm '60 or 90 minutes' with Butsaba.

### Deep Tissue Massage (/deep-tissue-massage/)

- [ ] P0 Alt text on 3 images ('targeting chronic muscle tension, pain relief, and improved mobility') -> plain description.
- [ ] P0 A4-A6: numbered direct-source citations have been added on staging19. Compare against the original passages in the citation files and check exact source fit; obtain Ryan's approval before changing the wording or placement.
- [ ] P0 'symptoms involving tendons, inflammatory conditions, or nerve-related pain, massage is often most beneficial as part of a broader recovery plan' -> 'For tendon, inflammatory or nerve-related symptoms, check with your clinician before deep tissue work.'
- [x] KEEP the red-flag advice. The approved shared note now appears below What to Expect on all four service pages. On Deep Tissue, Ryan also approved the separate severe-pain sentence there; the overlapping older warning was removed from Common Reasons, leaving its preceding two sentences verbatim.
- [ ] P0 'can support recovery, improve flexibility, and help you return to comfortable movement with greater confidence' -> 'can help you feel looser and move more comfortably.'
- [ ] P0 Process: 'Targeted Treatment Plan' -> 'Session Plan'; 'to address the root of the restriction' -> 'to work on areas of tightness'; 'to reduce next-day soreness' -> 'to help you feel comfortable afterward.'
- [ ] P1 H1 says 'in Mt. Tabor' while Therapeutic says 'in Portland'; use one location wording sitewide.

### Thai Massage (/thai-massage/) - cleanest page; use as the template

- [ ] P0 Alt 'Illustration of Butsaba providing a Thai massage session in Portland' (x3) -> 'Illustration of a Thai massage session'. Do not present AI art as depicting a named real person.
- [ ] P0 The hero image (`ComfyUI_01044_.webp`) is the same file used for Deep Tissue, and the second image (`mcdZCAyR9U1O...webp`) is also on Deep Tissue. Use unique images per service.
- [ ] P1 The approved 'Before your session' health note is present on Thai Massage. The separate clothing/mat/draping and session-mechanics explanation from section 1 remains pending Butsaba's answers.
- [ ] P1 Write a real meta description (currently the banner text, cut off mid-sentence at 'What Is').

### Work With Me (/work-with-me/)

- [ ] P1 Nav label 'Work With Me' reads as a client call to action. Rename ('For Practices') or move to the footer; consider noindex if it is only for hiring.
- [ ] P1 Meta description is the coming-soon banner; write a real one.
- [ ] P0 Make the timeline match the resume and the rest of the site (Windy City past vs present; Thai Solitude launch story).
- [ ] P1 Credentials: add course name, hours and year for Still Light Center; diploma name and year for Estelle (confirm the 'European Massage Therapy School' naming against the diploma); Illinois license if held; BLS issue date.
- [x] P0 Add the 'Verify license' link next to LMT-29905. On staging19 Work With Me, it renders once in the Oregon credential list item and links to https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx.
- [ ] P1 Name the Thai studio consistently with the resume (Thai Lotus Bodywork) or keep it unnamed on both.
- [ ] P1 Confirm she is comfortable linking to Windy City Massage (a corporate and mobile-spa staffing company) and that 'her clients included hotel guests and business travelers' is accurate.
- [ ] P2 The 'For clients' section at the bottom mixes audiences; shorten or remove.

### Contact (/contact/)

- [ ] P0 Title tag 'Contact Butsaba Dusrai | Registered Therapist, Mt. Tabor, Portland' -> fix.
- [ ] P0 Meta description shows (224) 275-1887 but the page body shows (312) 539-4328; use one number everywhere.
- [ ] P1 SMS link is `sms:+13125394328&body=...`; use `?body=` and test on iPhone and Android.
- [ ] P1 Instagram link goes to instagram.com (not a profile) and has aria-label 'List item'; fix or remove.
- [ ] P1 Add a visible email line. Do not ask for health details in a form or text.
- [ ] P1 'Address: Mt. Tabor, Portland' plus a Google Map embed of Mt. Tabor implies a location -> relabel 'Service area', remove or replace the map (its title attribute is empty).
- [ ] P1 Hero image alt 'Client meeting with a massage therapist for a personalized massage therapy session' -> describe it; label as illustration if AI.
- [ ] P2 Add hours or response time and a cancellation policy link.

### Resume PDF (Butsaba\_Dusrai\_LMT\_Resume\_License.pdf)

- [ ] P0 Reconcile with the section 1 timeline: 'Owner, Thai Solitude - Chicago, Jan 2024 to present', 'Windy City Massage - Apr 2025 to present', 'Portland, OR (Relocating from Chicago)'.
- [ ] P0 Move the license number to the top, next to 'Licensed Massage Therapist - Oregon' (currently only at the very bottom). Add Illinois if held.
- [ ] P1 List training specifics (course, hours, year) for Ayurvedic Thai stretching, PNF assisted stretching, hot stone, reflexology, aromatherapy; claim only what was actually trained.
- [ ] P1 Cross-check BLS 'valid through September 2028' against the card.
- [ ] P2 Be ready for the 2017-2020 gap question; set the PDF title metadata.
- [ ] P2 The PDF is public and indexable and contains a phone number and email; decide if that is intended.

## 4. Metadata and SEO

- [ ] P1 Unique title and meta description for all 8 pages in the SEO plugin; nothing duplicates the banner text.
- [ ] P0 Remove 'Pain Relief', 'Recovery' and 'registered' from title, OG and Twitter fields.
- [ ] P1 OG images: Home uses `ComfyUI_01084_.webp` and Thai uses the Deep Tissue image. Use a real photo or logo.
- [ ] P1 Decide on indexing before launch (the site is currently index,follow with a 'coming soon' banner).
- [ ] P2 Add structured data only after address, hours and claims are final; do not mark up an address or studio that does not exist.
- [ ] P2 Attachment pages exist (e.g. `/services/butsaba_avatar-2/butsaba-avatar/`); redirect them to the parent or noindex them.

## 5. Images and media

- [ ] P1 Inventory every image: real photo or AI. Label AI images as illustrations; never present them as her space, her clients or real sessions.
- [ ] P1 One unique image per service (see the duplicates above).
- [ ] P1 Media library cleanup: many items have junk alt/title text equal to the hash filename (e.g. 'ycn9fko5cuothrjovxn2v 2409a2f0...'); one caption is a raw URL (`butsaba_avatar-1`).
- [ ] P2 Delete unused media after confirming they are unused (e.g. `butsaPro.webp`, `ComfyUI_01079_GIMPsq256.webp`).
- [ ] P2 Rename generator-style filenames (`ComfyUI_...`, random hashes) to descriptive names.

## 6. Security, privacy, WordPress hygiene

- [ ] P0 The author display name is an email address (`<admin-login-email>`, masked 2026-10-24 before this file went into git) and it is exposed publicly through the REST API and the author archive URL `/author/<admin-login-slug>/`. Change the display name and nickname, change the author slug, disable author archives, and block user enumeration (`/wp-json/wp/v2/users`, `?author=1`).
- [ ] P1 Re-fetch the REST API and the author URL afterwards to confirm the email no longer appears.
- [ ] P1 Confirm the admin login username differs from the display name and that 2FA is on.
- [ ] P1 Google Site Kit (analytics) is installed: add a privacy policy and a cookie/analytics notice as needed.
- [ ] P2 Services, Contact and Deep Tissue share the same block IDs (`bfe052b1`, `4de6aee5`, `259aae54`) from duplicated pages, and the service-page templates use hand-made IDs (`c1a2b3c4`...). Regenerate to avoid CSS collisions.
- [ ] P1 Snapshot or stage before bulk edits.

## 7. Missing pages and content

- [ ] P1 Privacy policy (analytics, texts and email) linked in the footer.
- [ ] P1 Cancellation and late policy, payment, session lengths, pricing (if she wants it public).
- [ ] P1 'Before your session' and scope page or section (see section 2).
- [ ] P1 Service-area and outcall policy: travel radius, hotel arrival, space needed.
- [ ] P2 Accessibility: heading order (Services jumps from h1 to h3), repeated link text ('Services' x3, 'Get in Touch' x2), generic aria-labels, contrast check, mobile check.

## 8. Off-site (same rules apply)

- [ ] P0 Put the LMT license number in every ad or listing: Google Business Profile, Instagram bio, directories, email signature, flyers and cards.
- [ ] P0 Google Business Profile: if mobile, set it up as a service-area business and hide the street address; do not list 'Mt. Tabor' as a location she does not operate from.
- [ ] P1 Use the same title wording ('LMT') everywhere.
- [ ] P1 Send the Board a written address update within 30 days of any change once she is in Portland.

## 9. Final QA pass

- [ ] P0 Re-run the section 2 term search across all pages, titles, meta descriptions and alt text; zero hits except where intentional.
- [ ] P0 License number visible on every page and on the resume.
- [ ] P0 One phone number everywhere; test the SMS and email links.
- [ ] P1 Check view-source for 'Registered', 'Pain Relief', 'pain management', 'accelerate', 'healing', 'studio', 'proof'.
- [ ] P1 Verify external links (Estelle, Still Light Center, AHA, Windy City all loaded on 2026-10-04) and the new Board lookup link.
- [ ] P1 Mobile and accessibility check.
- [ ] P0 Have Butsaba read the final copy and confirm every sentence about her is true; save a dated copy of the final text.

## 10. What was and was not verified

- Verified (2026-10-04): live text of all 8 pages and their metadata, the REST API page and media output, the resume PDF text, the OAR 334-010-0025 and 334-040-0010 text, and that the Estelle, Still Light Center and Windy City sites load.
- Not verified: license LMT-29905 itself; the text of OAR 334-010-0031 (outcall); any Illinois license; whether any cited research exists; what the images actually depict (not viewed); anything behind the WordPress login.

## 11. Review issues left open (2026-10-04)

Issues raised by review of the answers file that are **not yet resolved**. These are
open items, not answers. Each names what it blocks.

**Sources**

| Issue | Blocks |
|-------|--------|
| Facility-permit reading was too narrow. OAR 334-020-0005(1)(C)(i) exempts a facility owned **or operated** by a person licensed by a health professional regulatory board, and **ORS 676.160(6) names the State Board of Massage Therapists as such a board**. A sole LMT's own room is therefore likely exempt. The real question is who owns or operates a **rented** room in a collective. Reword the Board question and have it confirm OBMT counts under ORS 676.160. | The whole location/permit conclusion |
| The permit application needs the facility's **Oregon Secretary of State business registration**, and the permit is issued in the **facility's name**. "Thai Solitude" may not be usable if she rents inside someone else's permitted facility. Add to the sequence: **name first, then room, then permit**. | The room search order of operations |

**Timing and identity**

| Issue | Blocks |
|-------|--------|
| **"Chicago past tense" is premature.** Today is Oct 4 and the roles run to ~Oct 27, so past tense is false on a live page right now. Use `Apr 2025 - Oct 2026` or "through October 2026". | The sitewide timeline rewrite |
| **"Based in Mt. Tabor" is untrue until she arrives.** Use "Relocating to Portland (Mt. Tabor area)" until then. | Home, About, Contact location lines |
| **The Sa Rai / Butsaba Dusrai split is not a later question.** Practising massage under a false or assumed name is a discipline ground (OAR 334-040-0010(10)). Advertise under the name on her licence; a nickname in parentheses only if it is hers, and confirm with the Board. | Work With Me, and any site copy using a name |

**Claims on the site today**

| Issue | Blocks |
|-------|--------|
| **"Cite, do not delete" does not cover three things.** The sentence listing cancer and neurological populations; the headache FAQ promising fewer headaches; and any other "may help" sentence with no source yet. **A citation does not make a practitioner-specific outcome promise acceptable, and she has no stated oncology training. Delete the first two regardless.** | Therapeutic and Deep Tissue copy |
| **No sources have been supplied, so pull the uncited sentences now** rather than leaving them live while the sources are gathered. | Same |
| **Inconsistent publishing rule.** The plan says publish nothing about session mechanics or length until the room is ready, yet the Therapeutic FAQ publishes "60 or 90 minutes" today. Pick one. | Therapeutic page |

**Do now rather than deferring**

- [ ] **Verify LMT-29905.** Two minutes at the public lookup. The number is **already live on Work With Me and the resume**, so the "nothing publishes before verification" rule is already broken. Also run the **discipline lookup** and check the **expiry date**.
- [ ] **Fix the author email leak.** P0, independent of everything else, about ten minutes. **Check whether the login username equals the exposed email.**
- [ ] **Ship every edit that needs no new information:** remove "Registered"; remove "studio" and the mobile/hotel lines; remove the outcome and "pain relief" claims; remove the (224) number; add the licence footer. All already decided, all live risks.
- [ ] **Noindex the resume PDF** via an `X-Robots-Tag` header or robots rules. Section 6 of the answers file asks whether it is indexable but does not say how to fix it.

**Gaps**

- **Privacy policy and the health note** are "needed soon" in the text but absent from the Next pass list.
- **Add business registration, and any Portland/Multnomah tax registration, to the room sequence.** Not yet verified.
- **Scope drift:** keep "Ayurvedic" and any Thai "energy line" wording to a **plain description of the stretching**. OAR 334-010-0025(4) bars an LMT from offering services other than massage without extra training or licensure.
- **Extend `verify-local.php`:** assert on every page that the licence string is present, the banned terms are absent, there is exactly one phone number, and `ts-coming-soon` appears on all 8 pages. Run it local, then staging, then live. That also makes banner removal a one-query check at launch.

**One call with Butsaba** - batch the ask list into a single call:

1. Still Light hours, years, and any Thailand study.
2. Estelle diploma name and year.
3. Table or mat, clothing, draping, and anything she will not offer.
4. Her facility-permit knowledge and room plans.
5. Which email and Instagram to publish.
6. Pricing.
7. Her licensed name vs "Sa Rai".
8. Consent for the photos and the public phone and email.
9. A final "every sentence about me is true" sign-off.

---

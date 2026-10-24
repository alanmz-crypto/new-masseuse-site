# Thai Solitude Fix Checklist - Answers and Plan

> **SUPERSEDED 2026-10-04. Do not work from this file.**
>
> It has been split by document role into three registers:
>
> - `Thai Solitude - Compliance Register.md` - rules, findings, corrections
> - `Thai Solitude - Decisions Register.md` - every decision Ryan made
> - `Thai Solitude - Evidence Register.md` - the source and bundle map
>
> It is kept unchanged as the record of how the work developed. **The registers are
> current; this file is history.** Where they disagree, the registers win.
Date answered: October 4, 2026. Answers supplied by Ryan.

Scope: the Thai Solitude site project (`~/WordPress/new-masseuse-site`).

Nothing here has been applied to any website, database, host, or DNS record.
Planning only.

Companion to `Thai Solitude Fix Checklist.md` (the audit and backlog). The
checklist holds the issues; this file holds the answers to its `[ASK]` and
`[VERIFY]` items, and the corrections to the checklist those answers produced.

**Freshness.** Rules, fees and licence status change. Every Oregon rule cited here
was read on 2026-10-04 from the OBMT OAR chapter 334, July 1 2024 edition. Reconfirm
against the live source before acting on any of it.

## Sources

Every external fact in this file comes from one of these. Oregon rules were read
verbatim on 2026-10-04; the workspace documents were read on the same date.

**Oregon Board of Massage Therapists, OAR chapter 334 (July 1, 2024 edition):**
https://www.oregon.gov/obmt/Documents/Effective%2007.01.2024%20OAR.WC.pdf

- `Source - ORS 676.160 (Definitions for ORS 676.165 to 676.180):` "health
  professional regulatory board" list. **(6) State Board of Massage Therapists.**
  https://oregon.public.law/statutes/ors_676.160
- `Source - OAR 334-010-0025 (Practice of Massage):` licence number in all
  advertisements (9); display of licence (8); LMT abbreviation reserved (6); misuse
  of title (5); scope limits (4); draping (3); no medical diagnosis (2)(c).
- `Source - OAR 334-010-0006(2) (Facility Permits):` a permit is valid only for the
  location and business name printed on it; cannot be used at multiple locations.
- `Source - OAR 334-010-0033 (Fees):` $200 initial facility permit.
- `Source - OAR 334-020-0005(1) (Facilities and Sanitation):` facility permit
  required (1)(a)(B); the three permit exemptions (1)(C); permit number in
  advertising (1)(a)(B)(iv).
- `Source - OAR 334-020-0005(2) (Outcall/On-site):` equipment, linen and privacy
  practices for any temporary location. **This is the outcall rule; there is no OAR
  334-010-0031 in the current set.**
- `Source - OAR 334-020-0015 (Equipment):` cleaning between uses; impervious
  coverings; topical preparation handling.
- `Source - OAR 334-020-0050 (Linens):` sanitary linen practice.
- `Source - OAR 334-040-0010 (Discipline):` false, deceptive or misleading
  advertising (7); practising beyond scope (14); failure to obtain required permits
  (21); gift certificate and prepaid package refund (25)(i)(C).

**Oregon Board of Massage Therapists, public lookup and guidance:**

- `Source - OBMT public licence and permit search:`
  https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx
- `Source - OBMT disciplinary action lookup:`
  https://public.orlicensing.oregon.gov/OBMTDiscipline/LPRBrowser.aspx
- `Source - OBMT FAQ (licence number format):` licence numbers are written `LMT-`
  followed by the digits, no spaces. https://www.oregon.gov/obmt/Pages/FAQs.aspx

**Workspace documents (read, not owned, by this file):**

- `Source - Butsaba_Dusrai_Resume_Source_Of_Truth_2026-09-25.md:` dates, focus
  specialties, experience span, BLS, competencies. In
  `~/Projects/SubProjects/ButsabaJobScout/`.
- `Source - Butsaba_Dusrai_Resume_Site_Material_2026-09-25.md:` the two-site split,
  the `Sa Rai` / `Butsaba Dusrai` name rule, the do-not-publish list.
- `Source - Butsaba_Dusrai_Practice_Readiness_Portland_2026-09-26.md:` no-car plan,
  practice location options, website and Google profile readiness.
- `Source - Satellite_LMT_Scouting_Portland_2026-09-18.md:` facility-permit reading,
  transit screening, Portland business-tax registration.
- `Source - Butsaba_Dusrai_Portland_LMT_Resume_Audit_2026-09-25.md:` Still Light
  wording rules, credential mislabelling, the two date errors.
- `Source - Thai Solitude Fix Checklist.md:` the audit and backlog this file answers.

**Not a source, and not verified here:** licence LMT-29905 itself; any Illinois
licence; ORS 687.021 verbatim; any research cited on the site. See section 10.

---
## Status at a glance

Answered by Ryan on 2026-10-04 unless noted.

### Answered - can be written

| # | Item | Answer |
|---|------|--------|
| Q-A | Site purpose | Bookings + hiring + team building; Work With Me stays |
| Q-B | Service model | Rented room, clients come to her, room not yet found |
| Q-C | Site role | Thai Solitude is the live authoritative site; not retired |
| 1.1 | Timeline | Arrives Oct 28; client work from late Nov. **CORRECTED: still present tense until Oct 27** |
| 1.1a | Chicago end dates | Both roles end approx Oct 27, 2026 |
| 1.2 | Location | Mt. Tabor confirmed; region map OK; no street address. **CORRECTED: "relocating to" until arrival** |
| 1.3 | Illinois licence | Held (both states); moot for the site |
| 1.5 | Specialty | **Thai massage** leads |
| 1.7 | Testimonials | None; all outcome language comes out |
| 1.8 | Phone | **312-539-4328**; remove (224) 275-1887 |
| 1.10 | Images | Real photos of her, an avatar of her, and images that are not her |
| 1.4 | Service detail | 60/90 min; publish nothing until the room is ready |
| 2.x | Voice | First person **"I"** sitewide |
| 2.x | Scope line | Accepted |
| 2.x | Health note | Add to all service pages, same spot, in "I" |
| 2.x | Coming-soon banner | **No change** (layout risk) |
| 2.x | Chrome cleanup | Yes, **local Docker attempt first** |
| 2.x | Relative links | Yes, root-relative, local first |
| 4.1 | Google Business Profile | Shows the address |
| 4.2 | Listings | Do when we can; Ryan watches Google |
| 4.3 | Email signature | `Butsaba Dusrai, LMT - Oregon License LMT-29905` |
| 1.6 | Windy City | May be named as a historical record |
| 7.1 | Research citations | Cite, **but 3 items deleted regardless** |
| 7.2 | Term pass | Run it, converted to "I" |

### Action for Ryan

| Item | What |
|------|------|
| 6 | **Check the resume PDF served from the site** - 7-point list |
| 7.3 | **Author email leak** - Ryan is tracking it as a todo |

### Ask Butsaba

| Item | Question |
|------|----------|
| 1.6 | Still Light hours, and whether any study was in Thailand |
| 1.6 | Estelle diploma name and year |
| 1.9 | Session mechanics: table or mat, clothing, draping |
| 1.12 | Facility permit for a rented room (permit facts recorded here if she does not know) |
| 1.8 | Email address and Instagram handle |
| 1.4 | Pricing (to be double-checked) |

### Corrections applied (review, 2026-10-04)

| # | What changed |
|---|--------------|
| 1 | **Facility permit - reversed.** ORS 676.160(6) lists OBMT as a health professional regulatory board, so OAR 334-020-0005(1)(C)(i) likely exempts a sole LMT's own room. The question is now **who owns or operates the rented room**. |
| 2 | **Sequence is name, then room, then permit.** The permit is issued in the facility's name and needs the Oregon SoS business registration (OAR 334-010-0005(13)). |
| 3 | **"Chicago past tense" was premature.** Still current until ~Oct 27. Use `Apr 2025 - Oct 2026`. |
| 4 | **"Based in Mt. Tabor" was premature.** Use "Relocating to Portland (Mt. Tabor area)" until arrival. |
| 5 | **The name split is not a later question.** OAR 334-040-0010(10) makes practising under an assumed name a discipline ground. Settle it before writing copy. |
| 6 | **"Cite, do not delete" does not cover 3 items** - delete them regardless. |
| 7 | **Publishing rule was inconsistent** - the Therapeutic FAQ publishes "60 or 90 minutes" while session length was deferred. |
| 8 | **Scope drift** - "Ayurvedic" and any Thai "energy line" wording describe the stretching, not a separate service (OAR 334-010-0025(4)). |

### One call with Butsaba (batch the ask list)

1. Still Light hours, years, and any Thailand study.
2. Estelle diploma name and year.
3. Table or mat, clothing, draping, and anything she will not offer.
4. Her facility-permit knowledge and room plans.
5. Which email and Instagram to publish.
6. Pricing.
7. Her licensed name vs "Sa Rai".
8. Consent for the photos and the public phone and email.
9. A final "every sentence about me is true" sign-off.

### Open / deferred

| Item | Status |
|------|--------|
| 1.11 | Licence lookup LMT-29905 - verification step, not a question |
| 7.1 | The 6 citation sources have not been supplied |
| 4.2 | Directory list not assembled |
| -- | Pricing, cancellation, session lengths, service area - **deferred until the room is ready** |

---
## How to use this

Each item below is a question the checklist raised, with its answer recorded.

| Field | Meaning |
|-------|---------|
| **Question** | What the checklist needs to know |
| **Why it blocks** | What cannot be written until this is answered |
| **Answer** | ← fill in |
| **Answered by / date** | Who decided it, and when |
| **Then** | What becomes possible once it is answered |

Do not resolve an item by picking the more flattering value. If an answer is
"unknown", write "unknown" - an open question is safer than a wrong fact on a
regulated site.

## 0. Questions that decide the shape of everything else

These are not in the checklist as headline items, but they gate most of the
sections below.

### Q-A. Site purpose

**Question:** Is this site for client bookings, for getting hired, or both? Does
Work With Me stay on the site and in the main nav?

**Why it blocks:** Sections 2, 3, 6 and 7. A booking site and a hiring portfolio
need different pages, different voice, and different calls to action.

**Answer:** Both - client bookings, being hired, and building teams. Work With Me
stays on the site and in the main nav.

**Answered by / date:** Ryan, 2026-10-04

**Then:** the page inventory and the nav can be settled. Work With Me is
confirmed as a permanent top-level page, so it gets the full compliance pass
rather than a noindex or a footer demotion.

### Q-B. Service model

**Question:** Mobile/outcall only, or do clients come to her (home studio or
rented room)? Is there any fixed location at all?

**Why it blocks:** the entire copy rewrite. Every "at your home or hotel" line,
the service-area page, travel radius, hotel policy, and the "studio" wording all
depend on this. It also determines whether the location question in §1 is
answered by deletion or by correction.

**Answer:** Rented room. Clients come to her. The room has not been found yet.
She will not travel to clients except by exception.

**Answered by / date:** Ryan, 2026-10-04

**Then:** sections 1.2, 2, 3 and 7 can be written. Consequences that follow:

- Every "mobile", "at your home or hotel", "travel radius" and "hotel outcall"
  line comes out. The Home hero and Contact page are rewritten to "clients come
  to her".
- "studio" must not be used while there is no room. The checklist already bans
  the word; this makes the reason concrete.
- Section 3 (service area, outcall policy, space needed) is **skipped**.
- The address question in 1.2 becomes sharper, not easier: there is a **future**
  room location that cannot be published until it exists and its permit status is
  settled.
- The outcall/facility question (1.12) still matters, but only for the exceptions.
  A room she rents still raises the facility-permit question directly.
- The site needs language that is true **now** (no room, no address) and stays
  true after the room is found, so launch is not a second rewrite.

### Q-C. Site relationship to the other project

**Question:** Is `thaisolitude.com` a client-facing site, a hiring portfolio, or
a site to retire once Willowy Hollow goes live? The two projects currently show
different names and different service areas.

**Why it blocks:** whether this site gets the full compliance pass at all, or a
minimal pass and a redirect.

**Answer:** Thai Solitude is the live, authoritative site. Willowy Hollow ended up
being more of the practice concept, but Thai Solitude carries the stronger and
more current facts.

Thai Solitude is:
1. **A hiring portfolio and networking/team tool - active now.**
2. **A client-facing practice site - later, once the business is set up** (rented
   room, licence in place, business registration).

So it is not being retired, and Willowy Hollow is not the successor that replaces
it.

**Answered by / date:** Ryan, 2026-10-04

**Then:** the scope of the whole effort is fixed.

- Thai Solitude gets the **full compliance pass**. It is the site carrying the
  public facts, so it is the one that must be accurate.
- The ordering changes: the **hiring/networking job is the immediate one**, the
  client-facing job is the deferred one. Copy that only makes sense for a booking
  site (session pricing, booking, travel, cancellation) is written **later**, when
  the room and the business exist.
- **Correction to earlier assumption:** the workspace treated Willowy Hollow as
  the future practice and Thai Solitude as prior Chicago practice. That is now
  reversed for site purposes - Thai Solitude is the site that survives.
- **The `Sa Rai` / `Butsaba Dusrai` split is NOT a later question. CORRECTED**
  **2026-10-04 (review).** Practising massage under a false or assumed name is a
  **discipline ground** - OAR 334-040-0010(10), verbatim: "Practicing massage under a
  false or assumed name." So the name shown must be settled **before** any copy is
  written, not deferred.
  - **Rule to apply:** advertise under the name on her licence. `LMT-29905` is issued
    to her licensed name, so that name governs the advertising.
  - **A nickname in parentheses is acceptable only if it is genuinely hers**, and it
    should be **confirmed with the Board** before it goes on the site.
  - **Thai Solitude currently shows `Butsaba Dusrai`,** which matches the licence -
    so the live site is on the safe side today. The risk would be introducing `Sa Rai`
    onto this site without that confirmation.
  - The Willowy Hollow site is a separate question and its own project's call.
- **Freshness check needed:** if Thai Solitude holds the stronger, more current
  facts, the Willowy Hollow staging site is now the stale copy. Flag it, do not
  change it - that project has its own protocol.

## 1. Decisions and answers needed (checklist section 1)

### 1.1 Timeline

**Question:** One true timeline sentence, true on every page and the resume.
Conflicts noted by the checklist: Chicago since Jan 2024 vs "sessions begin in
November"; Work With Me puts Windy City in the past tense while the resume shows
it as present.

**Why it blocks:** the resume needs accurate end dates; the site and the PDF will
be checked against each other.

**Answer:** The site is a Chicago → Portland move site. All Chicago roles go
**past tense**. Dates:

| Fact | Value |
|------|-------|
| Arrival in Portland | **October 28, 2026** |
| Target for working with her own clients | **Late November 2026** |
| Now until then | The site is a hiring and networking tool |
| Chicago roles (Thai Solitude, Thai Lotus, Windy City) | Past tense on the site |

**Answered by / date:** Ryan, 2026-10-04

**Then:** the resume PDF, Work With Me, and the About copy can be reconciled in
one pass.

⚠️ **Two cautions before this is written onto the site:**

1. **The arrival date is a private move date.** The Willowy Hollow site project's
   do-not-publish list forbids "a Portland address, **exact move date**, replacement
   phone number, or unsupported local-service claim". This site should follow the
   same rule. **Publish "Portland" and "late November", not "October 28".** The
   move date works as internal planning context and does not go on the page.
2. **"Chicago roles go past tense" conflicts with the current resume**, which
   shows Windy City Massage as **Apr 2025 – Present** and Thai Solitude as
   **Jan 2024 – Present**. Past tense requires **end dates**. Until those are
   supplied, the site and resume disagree. This is question 1.1's outstanding
   half - see below.

**CORRECTION 2026-10-04 (review).** "Chicago past tense" is **premature and false on**
**a live page today.** Today is Oct 4 and the roles run to ~Oct 27, so they are still
current. Past tense only becomes true after Oct 27.

**Use `Apr 2025 - Oct 2026` or "through October 2026", not "ended".** The dates can
be written now; the tense cannot.

**Also corrected:** "Based in Mt. Tabor" is untrue until she arrives. Until then use
**"Relocating to Portland (Mt. Tabor area)."**

**One true timeline sentence (draft, valid today):**

> Butsaba Dusrai is an Oregon licensed massage therapist relocating to Portland from
> Chicago, where she has practised massage since 2020 and continues to practise
> through October 2026.

**Still needed:** the end dates for the Chicago roles (see Q 4.1).

### 1.1a Chicago role end dates

**Question:** Do the Chicago roles have end dates? Windy City Massage and Thai
Solitude Chicago are both shown as "Present". If the practice has ended or is
winding down, the resume and the site need the real end dates.

**Why it blocks:** the past-tense rewrite on the site, the resume PDF, the
Illinois licence question (1.3), and the "am I still practising in Illinois"
credential accuracy.

**Answer:** Yes, both end approximately the day before the move - **Oct 27, 2026**.
She is no longer taking paid Chicago sessions after that.

| Role | Dates |
|------|-------|
| Windy City Massage | Apr 2025 – **Oct 2026** |
| Thai Solitude (Chicago) | Jan 2024 – **Oct 2026** |
| Thai Lotus Bodywork | Sep 2020 – Feb 2025 |
| Portland | from late Oct 2026 |

**Answered by / date:** Ryan, 2026-10-04

**Then:** the timeline can be written in one accurate pass.

- The resume's `Present` entries become end dates. This is a live inconsistency
  to fix in the resume `.docx`, the regenerated PDF, and the LinkedIn deliverable.
- The **"since 2020 / six years" span is unaffected** - it is anchored to the Thai
  Lotus start, not to the end dates.
- The **Illinois licence question (1.3) may close itself**: if paid Illinois work
  stopped in Oct 2026 and she is not returning to Illinois practice, a current
  Illinois licence is not required to describe the roles. The entries become
  historical employment, and the site's Work With Me timeline matches.
- Confirm the resume's own record is updated; this file does not edit it.

### 1.2 Location

**Question:** What is true today, and from what date? What should the site say
until she is in Portland?

**Why it blocks:** the address line, the Contact page, the map embed, and the
Home H1 and title tag.

**Answer:** Mt. Tabor is real, not a placeholder. Butsaba will live in Mt. Tabor,
and it is roughly 95% likely to be where the business room is found. Ryan's
ruling: **that is safe to state.**

**Answered by / date:** Ryan, 2026-10-04

**Then:** the location copy can be resolved, with a distinction to preserve:

- **As a residence / where she lives:** "Mt. Tabor, Portland" is safe.
- **As a business location:** it is a **neighbourhood, not an address**, and that
  is the correct form. Do not publish a street address - there is no room yet,
  and OAR 334-010-0006(2) does not permit a facility permit to cover multiple
  locations or an unpublished one.
- **The Google Map embed of Mt. Tabor is acceptable as-is** (Ryan, 2026-10-04):
  it shows a region, not a street address, so it does not assert a premises.
  Widening the zoom to the neighbourhood is consistent with this ruling.
- **Wording that stays true now and after the room is found:** while she is still in
  Chicago use **"Relocating to Portland (Mt. Tabor area)"**; once she has arrived,
  "Based in Mt. Tabor, Portland" or "Serving Portland from Mt. Tabor" both work and
  neither asserts a facility. See the timing correction under 1.1.
- **The 5% risk is cheap to keep:** avoid "Visit our Mt. Tabor studio" or any
  phrasing that pins a specific premises. If the room lands elsewhere, only the
  word Mt. Tabor needs changing, not the sentence structure.
- **Still not published:** the October 28 move date (see 1.1).

### 1.3 Illinois licence

**Question:** Does she hold a current Illinois licence? If yes, add it. If no,
the Chicago entries on the resume and Work With Me need rewriting.

**Why it blocks:** the resume's Chicago entries and the Work With Me credential
list.

**Answer:** Yes - Butsaba holds **both an Illinois and an Oregon licence**.

Moot for site purposes: Chicago paid practice ends ~Oct 27, 2026, so a current
Illinois licence is not required to describe the historical roles.

**Answered by / date:** Ryan, 2026-10-04

**Then:** the credential sections can be written truthfully.

- **The Illinois licence does not go on the site.** Thai Solitude is an Oregon
  practice; Work With Me presents Oregon credentials. An Illinois number would
  imply practice in a state she is leaving.
- **Oregon licence number stays in scope** for the footer rule (checklist §2).
- **The Chicago entries become historical employment** with end dates, which the
  Illinois licence supports but does not need to be disclosed for.
- **Resume choice, not a site choice:** whether to list the Illinois licence on
  the resume is Butsaba's call. The resume workspace notes omitting a licence
  number is normal practice.
- ✅ **This closes the checklist's `[P0] [ASK]` Illinois item.**

### 1.4 Service model detail

**Question:** Session lengths, pricing, cancellation policy, service area and
travel radius, hotel outcall policy.

**Why it blocks:** Services, Contact, and any booking copy.

**Answer:** Partly known, and **none of it is published yet.**

| Item | Answer | Publish now? |
|------|--------|--------------|
| Session lengths | **60 and 90 minutes** | No - publish when the room is ready |
| Pricing | **To be double-checked** | No |
| Cancellation | **Same fees the payment processor charges everyone** | No |
| Service area | **Mt. Tabor and surrounding neighbourhoods** | No - see below |
| Hotel outcall | **Not applicable** - she does not travel to clients | Drop entirely |

**Answered by / date:** Ryan, 2026-10-04

**Then:** the service pages and any policy content can be written **later**.

**The governing rule for this whole group: nothing here is published until the
"brick and mortar" (the rented room) is ready.** So the checklist's P1 items -
pricing page, cancellation policy, session-length lines, service-area page - are
**deferred, not deleted.**

**Consequences:**

- **Do not write session lengths, pricing or cancellation onto the site now.** A
  price or a policy published before the business exists is a commitment she has
  not made.
- **"Mt. Tabor and surrounding neighbourhoods" is the service area to use when it
  is published.** It pairs with the location answer (Q 5) and is the correct form:
  a neighbourhood description, not an address.
- **The checklist's §7 pages reduce to:** privacy policy and the before-your-session
  / scope section are still needed soon; pricing, cancellation, session lengths and
  the service-area page are deferred to launch.
- **The checklist's §3 (service area, outcall, space needed) is now fully closed** -
  mobile is out, so those items do not apply.
- ⚠️ **Cancellation note for later:** "whatever fees the bank charges everyone"
  means the policy is the processor's, not hers. When it is written, describe it as
  the payment processor's fees rather than inventing a cancellation window. OAR
  334-040-0010(25)(i)(C) also requires gift certificates and prepaid packages to be
  honoured or refunded, so do not publish a no-refund policy that conflicts with it.

**Sub-question (answer when checked):**

**Q 12.1 - Pricing**

**Question:** What is the confirmed price for 60 and 90 minute sessions?

**Answer:**

**Answered by / date:** - (open)

### 1.5 Real specialty

**Question:** What does she actually lead with? The checklist notes About says
therapeutic and deep tissue while the resume treats Thai as core.

**Why it blocks:** the Home H1, the service card order, the Services page, and
the brand fit with the name "Thai Solitude".

**Answer:** **Thai massage is her specialty.** That is the lead offer.

The site currently leads with "therapeutic and deep tissue", which is backwards.

**Answered by / date:** Ryan, 2026-10-04

**Then:** the site's lead offer can be fixed.

- **Lead with Thai massage** on Home, in the service card order, in the title tag,
  the meta description, and About. The brand name "Thai Solitude" and the offer
  finally agree.
- **Ayurvedic Thai stretching** is the specialty *of* Thai Solitude and remains
  the most distinctive single line.
- **Ordering:** Thai massage → Ayurvedic Thai stretching → therapeutic bodywork →
  relaxation massage.
- ⚠️ **`Ayurvedic Thai stretching` is a service description, never a credential
  name.** Do not write it as a certification.
- **Deep tissue moves down or out of the lead.** It is a supporting modality, not
  the headline. This also removes the tension with the checklist's other finding
  that the site's deep-tissue page carries the most unsupported claims.
- **The Mt. Tabor line in the Deep Tissue H1** already conflicts with Therapeutic's
  "in Portland"; the location wording gets unified sitewide anyway.
- **Correction to the checklist's §1 framing:** the checklist asked whether to lead
  with Thai or with therapeutic/deep tissue. It is Thai.

### 1.6 Training details

**Question:** Still Light Center course name, hours, year, certificate. Estelle
diploma name and year. Thai Lotus Bodywork dates. Permission to name and link
Windy City Massage.

**Why it blocks:** Work With Me credentials.

**Answer:** **UNKNOWN - must be asked of Butsaba.** Searched both named locations
in this session and the details are **not recorded anywhere**:

- `~/Projects/SubProjects/ButsabaJobScout/` - the detail exists only as **open
  questions** (`G12a` in the Source of Truth and the resume audit; worksheet item 5).
- `~/Projects/SubProjects/Relocating_Habitat/` - no Still Light, Estelle or Thai
  Lotus program detail.

**Answered by / date:** - (open). **Action: ask Butsaba.**

**Then:** the credentials block can be written.

**What is known, and what is not:**

| Item | Status |
|------|--------|
| Estelle Medical Academy (European Massage Therapy School), Skokie IL - Diploma in Massage Therapy | Institution known; **diploma name and year unknown** |
| Still Light Center - Traditional Thai bodywork | Institution known; **course name, hours, year, teacher, Thailand study unknown** |
| Thai Lotus Bodywork dates | ✅ **Known: Sep 2020 – Feb 2025** |
| AHA BLS Provider | ✅ **Known: issued Sep 2026, renewal Sep 2028** (eCard inspected by Ryan) |
| Windy City naming/link permission | **May be mentioned as a historical record** (Ryan, 2026-10-04) |

**Corrections carried in from the resume audit, apply here too:**

- **Spelling: "Still Light Center", not "Centre."**
- **Never label Still Light as a "Certification."** The site prohibition is explicit:
  use "trained in" or "graduate of". `Traditional Thai bodywork - Still Light Center`
  is the approved form.
- **Never write "PNF certified" or "PNF therapy"** - "PNF stretching" is the correct term.
- **`Ayurvedic Thai stretching` is not a credential name.**

**The single highest-value question to ask her:** how many hours at Still Light Center,
and whether any study was completed **in Thailand**. Thai-specific hours are the most
persuasive number available for the Thai-first positioning, and Thailand study would
strengthen the brand claim directly.

**Status: on the ask-Butsaba list** (Ryan, 2026-10-04). Not answerable from the
workspace - both named directories were searched this session and record it only as
open question `G12a`.

**Goes on the ask list as two questions:**

1. How many hours of training at Still Light Center, and in what years?
2. Was any of that study completed **in Thailand**?

**Why these two are worth asking at all, in a way most credentials are not:**

- A **Thai-hours number** is the strongest single fact available for a site that
  now leads on Thai massage (Q 7). It converts "trained in Thai bodywork" into
  something a reader can weigh.
- **Thailand study** is the one credential that would support the brand name
  directly, rather than by association.
- Both are honest additions, not embellishment: the site project prohibits invented
  teachers, schools, hours or lineage, so these are asked, never assumed.

**Wording constraints that apply when the answer lands:**

- "Still Light **Center**" - not "Centre".
- **Never** "Certification". Use "trained in" or "graduate of".
- `Traditional Thai bodywork - Still Light Center` is the approved form.

**Windy City Massage naming - approved as a historical record.**

Ryan: Windy City **may be mentioned as a historical record**. This resolves the
checklist's Work With Me permission question.

**What "historical record" permits, and what it does not:**

| Allowed | Not allowed |
|---------|-------------|
| "Licensed Massage Therapist, Windy City Massage, Chicago (Apr 2025 - Oct 2026)" | Any client-outcome claim drawn from Windy City |
| The role, the employer, the dates | Windy City's reviews or testimonials |
| Factual description of the work | Language implying Windy City endorses Thai Solitude |
| A link, if one is wanted | Anything suggesting she still works there |

**Why the past tense matters here:** with the Chicago roles ending ~Oct 27, 2026,
Windy City is a **prior employer**. Present-tense phrasing would misstate her
current situation and contradict the site's own relocation framing.

**Two open sub-points, both minor:**

1. **Link or no link?** Naming is approved; whether Work With Me links to
   windycitymassage.com is a separate cosmetic choice.
2. **The "hotel guests and business travellers" line** - if used, it describes her
   Windy City clientele, not Thai Solitude's. Factual only, and it should not appear
   on a page that reads as advertising her own practice.

**Consistency:** the same Apr 2025 - Oct 2026 dates must appear on the site, the
resume `.docx`, the regenerated PDF, and the LinkedIn deliverable. Fixing one and
leaving the others is the error pattern this workspace has already hit twice.

### 1.7 Testimonials

**Question:** Does she have written client testimonials with permission?

**Why it blocks:** if the answer is no, every outcome sentence on the site must
be removed rather than rewritten.

**Answer:** **No client testimonials available.** The only testimonials that exist
are reviews about her **at Windy City Massage**, and those belong to Windy City,
not to Thai Solitude.

**Answered by / date:** Ryan, 2026-10-04

**Then:** the outcome-language pass can begin.

**Rules that follow, and they are strict on a regulated site:**

- **All outcome and client-result language comes out**, not rewritten:
  "clients consistently leave...", "clients regularly experience...", "Many of
  Butsaba's clients seek...", "often report a noticeable difference within a few
  sessions".
- **No testimonials are published.** Nothing on the site is written to imply
  client outcomes for a practice with no clients yet.
- **The Windy City reviews are not usable here.** They are Windy City's, they were
  earned in an employment context, and republishing them would also raise the
  non-delegation and non-solicitation issues the job-search work already tracks.
  Do not quote, link, screenshot or paraphrase them.
- **Do not write "reviews available on request" or similar.** That implies they
  exist for Thai Solitude.
- **The one defensible exception** would be a factual statement about prior
  employment, e.g. that she worked as an LMT at Windy City Massage. That is a
  credential line, not an outcome claim - and it still needs the Windy City naming
  permission from Q 1.6.

⚠️ **This closes the checklist's `[P1] [ASK]` testimonials item.** Its "if they
come from her prior work, say so explicitly and keep it factual" escape hatch is
**not available** - the prior-work reviews are the employer's, so the blanket
removal applies.

### 1.8 Contact details

**Question:** Which phone is correct - (312) 539-4328 or (224) 275-1887? Which
email should be shown? Instagram handle?

**Why it blocks:** the Contact page, the footer, the resume PDF, and every meta
description that carries a number.

**Answer:** **312-539-4328** is the correct number. The **(224) 275-1887** in the
meta description is wrong and must be removed.

**Answered by / date:** Ryan, 2026-10-04

**Then:** the contact pass can be done once, correctly.

- **Fix the meta description.** The Contact page title/meta currently advertises a
  number that is not the contact number - on a regulated site that is a
  correctness problem, not only an inconsistency.
- The page body and the `sms:+13125394328` link already use the right number, so
  the SMS link only needs its `&body=` → `?body=` fix and device testing
  (checklist P1).
- **Still open, and still P1:** the email address to display, and the Instagram
  handle. The checklist also flags that the current Instagram link points at
  `instagram.com` rather than a profile and carries an `aria-label` of
  "List item" - fix or remove.
- **Do not publish an email she has not confirmed.** This is a replacement-contact
  decision and the site project's do-not-publish rule bars an unsupported
  replacement contact detail.
- Uses to update in one pass: Contact page, footer, meta descriptions, the resume
  PDF, and any `tel:` link.

**Sub-question (answer when known):**

**Q 10.1 - Email and Instagram**

**Question:** Which email should be displayed, and what is the Instagram handle?

**Answer:**

**Answered by / date:** - (open)

### 1.9 Session and draping practice

**Question:** Clothing, mat/table and draping practice for each service.

**Why it blocks:** the service pages and the before-your-session note.

**Answer:** **UNKNOWN - needs research with Butsaba.** Ryan is not Butsaba and
cannot answer this yet. Recorded as unanswered rather than guessed.

**Answered by / date:** - (open)

**Then:** the service pages can describe the session accurately.

⚠️ **Do not write session mechanics until this is answered.** Two reasons:

1. **It is a claim about her practice**, and the resume workspace's own rule is
   that unverified competencies stay off until Butsaba confirms them. The same
   standard applies to a public service page.
2. **It has a downstream consequence the research should capture:** whether she
   uses a mat or a table, and what draping requires, **determines what the rented
   room must provide**. Table storage, mat space, a covered surface, a private
   changing area and a lavatory are facility requirements under OAR 334-020-0005.
   So answering this helps the room search, not just the copy.

**What to research with Butsaba:**

| Question | Why it matters |
|----------|----------------|
| Table, mat, or both, and per service? | Room space and equipment; service page accuracy |
| Clothed or disrobed per service? | Draping rules under OAR 334-010-0025(3) |
| Pressure and stretching practice for Thai work | The mechanism wording the site should use |
| Anything she will *not* offer | Keeps the scope line honest |

Related but separate: **Q 1.4** (session lengths, pricing, cancellation) is also
still open and is the same conversation with Butsaba.

### 1.10 Images

**Question:** Which images are real photographs and which are AI-generated?

**Why it blocks:** every alt text, every caption, and the OG image fields. AI art
must be labelled as illustration and never presented as her space or her clients.

**Answer:** Mixed, and the distinction matters for alt text:

| Image | Truth | Alt-text implication |
|-------|-------|---------------------|
| **The photographs** | **Real photos of Butsaba** | May be described as her, plainly |
| **The two similar-looking images** | **An avatar of her** - a rendering, not a photograph | Describe as an illustration/avatar. Never present as a real session |
| **Everything else** | **Not her** - generic or generated imagery | Describe the scene generically. **Never name her in the alt text** |

**Answered by / date:** Ryan, 2026-10-04

**Then:** the media and alt-text pass can be done.

**The rule this settles:**

> Alt text may name Butsaba **only on the real photographs**. On the avatar and on
> every other image, alt text describes the picture without asserting she is in it.

**Specific corrections already identified by the checklist, now confirmed:**

- Thai Massage alt text reads *"Illustration of Butsaba providing a Thai massage
  session in Portland"* (×3). These are not her, so the sentence is false.
  Reassign to a plain description of the image.
- Home `butsaba homepage`, `butaba web poses` (typo), `butsaba avatar` - the avatar
  ones may be labelled as an avatar; the others need real descriptions.
- **Still to do:** match this ruling to actual filenames. The `ComfyUI_*` and
  hash-named files need a pass to sort photo / avatar / other, since the ruling is
  by category and the library is not yet labelled.
- **Duplicate images still stand** as an issue (the Thai and Deep Tissue hero
  share one file). That is a separate P1 and is unaffected by this answer.
- **One open sub-question:** is the avatar used as an OG/social image, or as a
  stand-in practitioner photo anywhere it could read as a real photo? Check during
  the pass.

### 1.11 Verify LMT-29905

**Question:** Does the Oregon Board lookup show the licence as active, in her
name, with no discipline?

**Why it blocks:** everything. The checklist's P0 licence-footer line, the resume
credential line, and Work With Me all depend on it. Until it is checked, the
claim is assumed, not verified.

**Answer:**

**Answered by / date:**

**Then:** the licence line can be published with a screenshot on file.

### 1.12 Facility permit and outcall

**Question:** Does a sole LMT doing outcall at clients' homes or hotels need a
facility permit? Confirm her case with the Board.

**Why it blocks:** whether the address may appear at all, and whether outcall
copy is permissible.

**Answer:** **Open - ask Butsaba.** If she does not know, the permit facts below are
recorded here so the question can be answered from source rather than guessed.

**Answered by / date:** - (open). **Action: ask Butsaba; record her answer here.**

### The permit facts, recorded from the rule text

Read from the OBMT OAR chapter 334, July 1 2024 edition (fetched this session).

**CORRECTED 2026-10-04.** An earlier version of this file read the exemption too
narrowly. The correction reverses the conclusion. **ORS 676.160 was read verbatim in
this session.**

**A facility permit is required for a permanent structure where massage is routinely
conducted.** OAR 334-020-0005(1)(a)(B), verbatim: all permanent structures where the
practice of massage and bodywork is routinely conducted must "Obtain a facility
permit to operate."

**But a sole LMT's own room is likely EXEMPT.** OAR 334-020-0005(1)(C), verbatim -
facilities exempted from the permit process are:

> (i) Clinic or facility owned or operated by a person authorized to practice a
> profession by a health professional regulatory board, as defined in ORS 676.160;
> (ii) A career school licensed under ORS 345.010 to 345.450; and (iii) Clinics of
> a board approved massage therapy program.

**The exemption turns on whether OBMT is a "health professional regulatory board"
under ORS 676.160. It is.** ORS 676.160, verbatim, lists among the boards:

> ... (6) State Board of Massage Therapists; ...

So exemption (i) covers a facility **owned or operated** by a person licensed by
OBMT. The clause is not limited to clinicians of other health professions.

**What that means:**

| Situation | Reading |
|-----------|---------|
| A sole LMT owns and operates the room she practises in | **Likely exempt** under (1)(C)(i) |
| A sole LMT rents a room inside someone else's facility | **Turn on who owns or operates the room.** If the room is owned or operated by her, exemption (i) is arguable; if the premises are the landlord's facility, the landlord's permit status governs |
| A collective run by a practitioner of another regulated health profession | Exempt under (1)(C)(i) on that practitioner's board |

The earlier version of this file said none of the three categories is "an
individual massage therapist." **That was wrong**, and it is corrected here.

**Outcall is separate and permissive.** OAR 334-020-0005(2), verbatim - for
"any temporary location where the LMT conducts massage and bodywork," the LMT
must provide safe sanitized equipment, sanitary linen practices, and client
privacy practices. **No permit is required for outcall.** Since she does not
travel to clients (Q-B), this applies only to the exceptions.

**Penalty if a permit is in fact required.** OAR 334-040-0010(21) makes
**"Failure to obtain the required permits for facilities or in violation of OAR
334-020-0005 (1)(a)(B)"** a discipline ground.

**The permit name is the facility's name, and the application needs the business
registration.** OAR 334-010-0005(13) requires, for a natural person, a copy of a
valid government photo ID and verification of Oregon Secretary of State business
registration confirming registration of the assumed business name, including the
business registry number and the entity name exactly as it appears on the permit
application. So **register the business name first** - the permit is issued in
that name.

**The sequence is therefore: name, then room, then permit (if needed).**

**If a permit is obtained, the number must be advertised too.**
OAR 334-020-0005(1)(a)(B)(iv) requires a permitted facility to include its
**permit number** in all massage therapy advertisements including websites. So the
footer may eventually need **both** `LMT-29905` and a facility permit number.

**A permit is fixed to one location and name.** OAR 334-010-0006(2):

> "A Facility Permit is valid only for the location and business name printed on
> the Facility Permit. An issued Facility Permit cannot be used at a different
> location, at multiple locations, or for a facility at the same location under a
> different name."

**Fee:** $200 initial facility permit (OAR 334-010-0033).

### Correct the checklist

1. **The rule cited as governing outcall does not exist in the current rule set.**
   The OBMT index lists no `334-010-0031`. The correct citation is
   **OAR 334-020-0005(2)**.
2. **The facility-permit exemption is BROADER than this file first stated, and the
   checklist's instinct was right.** The Board's 2023 facility letter naming OBMT as
   a health professional regulatory board is consistent with **ORS 676.160(6)**, read
   verbatim. A sole LMT's own room is likely exempt.
3. **Still confirm with the Board**, because the operative question is now "who owns
   or operates the rented room," not "is a sole LMT exempt."

### The question to send the Board

> I am an Oregon-licensed LMT opening a practice in a rented room in Portland,
> possibly inside a multi-practitioner collective. ORS 676.160(6) lists the State
> Board of Massage Therapists as a health professional regulatory board, and OAR
> 334-020-0005(1)(C)(i) exempts a facility owned or operated by a person licensed by
> such a board. Please confirm: (1) that OBMT counts as a health professional
> regulatory board for this exemption; and (2) whether the exemption applies where I
> rent a room from a landlord or collective and operate my practice there, or whether
> the exemption turns on who owns or operates the premises.

Board contact: **503-365-8657, OBMT.INFO@obmt.oregon.gov**

**Then:** the address and outcall questions are closed, and the room search gains a
hard requirement - confirm ownership and permit status before signing anything.
hard requirement - confirm permit status before signing anything.

## 2. Answers that will be needed during the copy pass

These are not single questions; note them here as they arise so section 1 does
not have to be reopened.

| Item | Question | Answer | Answered by / date |
|------|----------|--------|--------------------|
| Voice | First person "I" sitewide | Ryan, 2026-10-04 |
| Scope line | **Accepted.** Use the proposed wording | Ryan, 2026-10-04 |
| Before-your-session | **Accepted.** Reuse the Deep Tissue health note on all service pages | Ryan, 2026-10-04 |
| Coming-soon banner | **No change.** Leave the banner as-is; converting risks layout disruption | Ryan, 2026-10-04 |
| Chrome cleanup | **Yes, remove** - but not without its own local Docker attempt first | Ryan, 2026-10-04 |
| Relative links | **Yes** - `contact/`, `about/`, `services/` to root-relative `/contact/` etc. Local first | Ryan, 2026-10-04 |

**Approved scope line (verbatim):**

> Massage is not a substitute for medical care. Butsaba does not diagnose or treat
> medical conditions.

**Placement:** the footer, or the Services page, sitewide.

**Voice interaction:** the approved wording is third person. If the sitewide voice
is first person ("I"), the approveable variant is:

> Massage is not a substitute for medical care. I do not diagnose or treat medical
> conditions.

Ryan approved "the proposed" wording; confirm which of the two sentences ships so
the page does not mix voices. Both carry the same rule basis:
OAR 334-010-0025(2)(c) - massage treatment does not include making a medical
diagnosis.

**Approved health note - reuse on every service page (verbatim, in "I"):**

> Before your session, tell me about any health conditions, medications,
> pregnancy, recent injury or surgery. I may adjust the work, or suggest you check
> with your clinician first. New numbness, weakness, unexplained swelling or
> radiating pain means you should see a doctor before massage.

**Scope of the change:**

| Where | Action |
|-------|--------|
| Deep Tissue (/deep-tissue-massage/) | Source copy. Keep, rewrite to "I" |
| Thai Massage (/thai-massage/) | **Add** |
| Therapeutic Massage (/therapeutic-massage/) | **Add** |
| Services (/services/) | **Add** |
| Home, About, Contact, Work With Me | Not service pages - no addition |

**Placement:** the same position on each service page, so the three pages read
consistently.

**Related, still open:** the session-mechanics question (Q 8) is a separate item
and is unchanged by this - this note is health disclosure, not a description of
what happens in the session.

**Coming-soon banner - declined.**

Ryan: leave it as-is; converting to a reusable block/pattern "may disrupt layouts."

**Consequences to carry forward:**

- **The checklist’s P1 item is closed as "will not do."** Do not convert the banner.
- **Removal at launch becomes a multi-page edit** rather than a single switch. When
  the banner comes down it must be removed from **every page it appears on** - the
  checklist says that is every page. Verify per-page at launch rather than assuming
  one pass covered it.
- **Each page’s banner copy is independent**, so any wording change must be applied
  page by page.
- **No shared block means no single source of truth** for the banner text; the
  per-page verification above is the guard against a straggler.

**Chrome cleanup - approved, with a process requirement.**

Ryan: this is a **higher-level risk** than it looks, so it is not done as a direct
edit. **It gets its own Docker/local attempt first.**

**What is being removed:**

- The HTML comment block `SERVICE PAGE TEMPLATE - TO ADAPT FOR OTHER SERVICES, SWAP...`
- Empty `<p>` wrappers
- On: Thai Massage, Therapeutic Massage, Deep Tissue Massage

**Why it is higher-risk than a comment deletion:** page content is **serialized
page-builder blocks** (Spectra/UAGB). Removing a comment or an empty wrapper inside
serialized JSON can break the block, and a broken block can take the page layout
with it.

**Required process (not optional):**

1. `./scripts/backup-site.sh --note before-chrome-cleanup`
2. Make the change on **local Docker (:8090) only** - never straight to staging or live
3. Open all three pages and check the layout renders and the blocks survived
4. Re-run the content verifier: `scripts/credibility-pass/verify-local.php`
5. Only then consider staging, per the normal promotion rules

**Related:** the checklist also flags duplicate block IDs across Services, Contact
and Deep Tissue (`bfe052b1`, `4de6aee5`, `259aae54`) and hand-made IDs (`c1a2b3c4`...).
That is the same class of risk and should be handled in the same local-first pass,
not separately.

**Relative links - approved, local first.**

- Home's `contact/`, `about/`, `services/` become **root-relative**: `/contact/`,
  `/about/`, `/services/`.
- Because this edits Home page content, the **same local-first process applies**
  (backup, change on :8090, render check, verify-local, then staging).
- **Check the rest of the site in the same pass.** Relative links on Home suggest
  the pattern may repeat on other pages copied from the same template; sweep all
  pages rather than fixing Home alone.
- Low severity on its own - relative links usually resolve - but Home is the page
  most likely to be linked to from an odd depth, so this is worth doing.

**Google Business Profile - address is shown.**

Ryan: the profile shows the address, and Google is still updating things, so this
is left alone for now.

**Recorded because it interacts with three other answers:**

- **It cannot be created before the room exists.** The address shown must be the
  real practice location, and the room has not been found (Q-B).
- **The address must match the facility permit.** OAR 334-010-0006(2) fixes a permit
  to one location and business name, so the profile address and the permit address
  have to be the same one - decide the room first, then both.
- **The licence number goes on the profile** (OAR 334-010-0025(9) - licence number
  in all advertisements). If a facility permit is issued, its number must appear in
  advertising too (OAR 334-020-0005(1)(a)(B)(iv)).

**Privacy point, for when the room is chosen:** showing the address is the correct
choice under Google rules because clients visit, but it publishes where she works.
That is a decision to make deliberately once the location is real, not a default.

**Listings and directories - what the law actually says.**

Ryan: "I’m not sure how the law works." Recorded here so the rule is available
rather than guessed. Do the listings when we can; **Ryan watches Google**.

**The rule, verbatim (OAR 334-010-0025(9)):**

> "Active licensed massage therapists are required to include their license number
> in all advertisements, including but not limited to: written, electronic,
> televised and audio."

**How to read it:** the obligation is on **advertisements**, worded openly
("including but not limited to"). A directory listing, a flyer, a business card, a
social bio, a booking-page profile or an email signature that promotes massage is an
advertisement. Practical rule: **if a person could find her through it and book her,
it needs `LMT-29905` on it.**

**What that means in order of priority:**

| Channel | Licence number | Notes |
|---------|---------------|-------|
| Thai Solitude website | Required | P0, every page footer |
| Google Business Profile | Required | Ryan is watching this one |
| Social profiles (Instagram etc.) | Required | Bio or pinned line |
| Email signature | Required | One-line change, cheap to do |
| Flyers, cards, print | Required | When printed |
| Directories we do not control | Do when we can | Update the listing, or remove it |

**Two honest limits on this:**

1. **The rule follows the advertisement, not the platform.** Where a directory lets
   us edit, add the number; where it does not, the compliant move is to correct or
   remove the listing rather than leave a pre-licence ad up.
2. **Nothing is advertised until the licence is confirmed active (Q 1.11).** The
   checklist's own gate: Oregon bars practising or advertising without an active
   licence. So the order is: confirm the licence, then add the number everywhere,
   and prefer to publish nothing new before that.

**Not yet scoped:** the list of directories is not assembled. That is a separate
task, and it belongs with the launch work rather than the copy pass.

**LMT line - approved.**

Approved line (verbatim):

> Butsaba Dusrai, LMT - Oregon License LMT-29905

Applies to: email signature, business cards, flyers, and any other print or
digital advertisement.

**Accuracy note:** Ryan confirms the licence is known. This file has **not**
independently looked the number up or inspected a licence record - that is
checklist item 1.11, and it stays open as a verification step rather than a
question. Do not read anything here as this session having verified LMT-29905.

**Format check, from the OBMT FAQ:** licence numbers are written `LMT-` followed by
the digits, **no spaces** - so `LMT-29905` is correct as approved.

**Two related rules for the same line:**

- **Titles.** OAR 334-010-0025(6) reserves the abbreviation **LMT** for licensed
  massage therapists only. Do not use "Registered Therapist" or "licensed
  therapist" in place of LMT.
- **If a facility permit is later issued**, the **permit number** must also appear in
  advertising (OAR 334-020-0005(1)(a)(B)(iv)) - the signature becomes a two-number
  line at that point; the LMT number is not replaced.

## 3. Answers needed for the sections that depend on Q-B

Fill these only if the service model makes them applicable.

| Item | Question | Answer | Answered by / date |
|------|----------|--------|--------------------|
| Service area | Travel radius, if mobile | | |
| Outcall policy | Hotel arrivals, space needed, if mobile | | |
| Space needed | Room and setup requirements | | |
| Location page | Whether a fixed location page exists | | |

## 4. Off-site answers

| Item | Question | Answer | Answered by / date |
|------|----------|--------|--------------------|
| Google Business Profile | **Shows the address** (visibility deferred while Google updates) | Ryan, 2026-10-04 |
| Listings | Do it when we can; **Ryan watches Google**. Directories not yet scoped | Ryan, 2026-10-04 |
| Email signature | **Approved:** `Butsaba Dusrai, LMT - Oregon License LMT-29905` | Ryan, 2026-10-04 |

## 5. Answers carried in from the resume work

Facts already settled elsewhere, listed so they are not re-derived or
contradicted. Verify against the owning document before use; do not edit here.

| Fact | Settled value | Owning source |
|------|---------------|---------------|
| Windy City Massage dates | Apr 2025 – Present | Resume Source of Truth |
| Thai Lotus Bodywork dates | Sep 2020 – Feb 2025 | Resume Source of Truth |
| Experience span | since 2020 / six years | Resume Source of Truth |
| Focus specialties | Thai, Ayurvedic Thai stretching, therapeutic, relaxation | Resume Source of Truth |
| Sports massage | Out of scope | Resume Source of Truth |
| BLS | AHA BLS Provider, Sep 2026 – Sep 2028 | Resume Source of Truth |
| Practitioner name | `Butsaba Dusrai` on the resume; `Sa Rai` on the Willowy Hollow site | Site Material |

## 5a. Do now rather than deferring (CORRECTION 2026-10-04, review)

The review found several items the plan deferred that can be done today. These are
live risks, not future work.

### Verify LMT-29905 - do this now

**The plan said nothing publishes before verification. It is already broken.** The
number is **live on Work With Me and on the resume**.

- Look it up at `https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx`.
- **Also run the discipline lookup:**
  `https://public.orlicensing.oregon.gov/OBMTDiscipline/LPRBrowser.aspx`.
- **And check the expiry date**, not just that it is active.
- Takes about two minutes. It gates the licence footer, the resume line and Work
  With Me.

### Fix the author email leak - do this now

**P0, independent of everything else, about ten minutes.**

- Change display name and nickname, change the author slug, disable author archives,
  block user enumeration.
- **Also check whether the login username equals the exposed email.** The leak is
  the display name; a login username identical to it is a separate problem.
- Re-fetch `/wp-json/wp/v2/users` and the old author URL to confirm it is gone.

### Ship every edit that needs no new information

All of these are already decided, and each is a live risk:

- [ ] Remove **"Registered"**
- [ ] Remove **"studio"** and the **mobile/hotel** lines
- [ ] Remove the **outcome** and **"pain relief"** claims
- [ ] Remove the **(224)** number
- [ ] Add the **licence footer**

### Noindex the resume PDF

Section 6 asks whether it is indexable but does not say how to fix it. **Fix:**
an `X-Robots-Tag: noindex` header on the PDF, or a robots rule, whichever the
server allows. Do this before deciding whether the PDF stays linked at all.

### Extend `verify-local.php`

Turn the manual QA pass into one query. Assert on **every page**:

1. the licence string is **present**
2. the **banned terms are absent**
3. there is **exactly one phone number**
4. `ts-coming-soon` appears on **all 8 pages**

**Run it local, then staging, then live.** This also makes banner removal at launch a
one-query check rather than a page-by-page walk - which matters because the banner
stays pasted in (Q 18).

---
## 6. Ryan to check: the resume PDF served from the site

**ACTION FOR RYAN - check exactly this item.**

The site serves `Butsaba_Dusrai_LMT_Resume_License.pdf` and links it from Work With Me.
Copies exist in `scripts/assets/` and in `thai-solitude-snapshot/`. Ryan asked for this
flagged precisely so it is not missed.

**What to check, item by item:**

| # | Check | Why |
|---|-------|-----|
| 1 | **Timeline** - does the PDF still show Chicago as present tense, or the wrong Windy City / Thai Lotus dates? | The site says Chicago is past; the PDF must agree |
| 2 | **Licence number placement** - is it buried at the bottom? | It should be at the top, next to "Licensed Massage Therapist - Oregon" |
| 3 | **PDF title metadata** - does it still say "Relocating from Chicago"? | Visible in browser tabs and search results |
| 4 | **Is it public and indexable?** | It contains a phone number and an email address |
| 5 | **Which file is actually served** - `scripts/assets/` or `thai-solitude-snapshot/`? | Two copies exist and may differ |
| 6 | **Does it match the current resume `.docx`?** | The resume project regenerates the PDF from source; a site-local copy can go stale |
| 7 | **Does the link still resolve** from Work With Me? | A 404 on a hiring page is a bad look |

**Scope question this answers:** the resume PDF is **in scope to check, but the fix
belongs to the resume project**, not to this site. The site should serve whatever the
resume project publishes rather than keep its own copy that can drift.

**Recommended resolution once checked:** serve the canonical PDF from the resume
project's output rather than a site-local copy, so the two cannot diverge.

## 7. Sitewide items - answers

### 7.1 Research citations - CITE, do not delete

**Answer:** Keep the claims and **add the citations.** The research is true; the
omission was Ryan's oversight, not a false claim.

**Answered by / date:** Ryan, 2026-10-04

**This reverses the checklist's suggested action.** The checklist said "cite a real
named source that supports the exact wording, or delete it," and treated deletion as
the likely path. The answer is: cite.

### CORRECTION 2026-10-04 (review) - "cite" does not cover three things

**A citation does not make a practitioner-specific outcome promise acceptable.** So
the blanket "cite, do not delete" answer is **amended**: three items come out
regardless of whether a source exists.

| Item | Action | Why |
|------|--------|-----|
| The sentence listing **cancer and neurological populations** | **DELETE** | A citation cannot make this acceptable; she has **no stated oncology training**, and it implies she serves clinical populations. Also collides with OAR 334-010-0025(4) (services beyond massage need additional training or licensure) |
| The **headache FAQ promising fewer headaches** | **DELETE** | A practitioner-specific outcome promise. Same reason |
| Any other **"may help" sentence with no source yet** | **DELETE NOW** | Sources have not been supplied, so these are live and uncited |

**Pull the uncited sentences now rather than leaving them live while the sources are
gathered.** "Cite later" is not a plan for a claim that is wrong today.

**What survives the correction:** the genuinely factual "research suggests" statements
that describe general findings rather than her results - those get real sources when
Ryan supplies them.

**Sites to fix (6 total, per the audit):**

| Page | Count |
|------|-------|
| Therapeutic Massage | 3 |
| Deep Tissue Massage | 3 |

**The standard still applies, and it is the whole point of citing:** each sentence
must be supported by the source attached to it. OAR 334-040-0010(7) makes
*false, deceptive or misleading advertising* a discipline ground, so a citation that
does not actually support the sentence is worse than no citation - it looks
authoritative while being wrong.

**To do per claim:**

1. Identify the exact source Ryan had in mind for each sentence.
2. Check the source actually supports that wording (not a nearby claim).
3. Cite it in the page - named source, and a link where one exists.
4. If any single claim cannot be matched to a real source, delete that one claim.

**Open:** the source list has not been supplied. This needs Ryan to point at the
research behind each of the six sentences.

### CORRECTION 2026-10-04 (review) - inconsistent publishing rule

The plan says publish nothing about **session mechanics or length** until the room is
ready (Q 12), **yet the Therapeutic FAQ publishes "60 or 90 minutes" today.**

**Pick one.** The two positions cannot both stand. The consistent options:

| Option | Consequence |
|--------|-------------|
| **Defer the session-length line too** (matches Q 12) | Remove "60 or 90 minutes" from the Therapeutic FAQ now; restore at launch |
| **Publish session lengths** (overrides part of Q 12) | Session length is not a commitment in the way a price or policy is, so it may be defensible to state it - but then the "publish nothing" rule needs narrowing to price, cancellation and service area only |

**Recommendation:** treat session length like price. Nothing about the offer is
published until the room and the business exist. That keeps one rule instead of two.

### 7.2 Term search-and-replace - YES, to first person

**Answer:** Run the pass, and "we/our/us" becomes **"I"**.

**Answered by / date:** Ryan, 2026-10-04

**Two jobs in one pass, and they are different:**

1. **Voice conversion** - "we/our/us" to "I", sitewide. Mechanical.
2. **Term removal** - the checklist's risk-reduction list (healing/heal, proof,
   measurable, effective, pain relief, pain management, sciatica, chronic,
   corrective, accelerate, restore balance, nervous system, circulation, anxiety,
   prevention, lasting, studio, and the rest).

**Search across all of:** page text, title tags, meta descriptions, OG and Twitter
fields, and image alt text. Verify by `view-source`, not the editor.

**Standing caveat from the rule reading:** the term list is a **risk-reduction style
guide**, not a verbatim statutory ban. Only the scope and diagnosis items are directly
rule-backed (OAR 334-010-0025(2)(c) and (4)). Apply the list, but do not treat every
word on it as illegal.

**Specific removals already confirmed elsewhere in this file:** "studio" (no room
yet, Q-B), and all outcome/testimonial language (Q on testimonials).

### 7.3 Author email leak - ACTION FOR RYAN (todo)

**Answer:** Ryan will make this a todo. Recorded here so it is not lost.

**The problem (checklist P0):** the WordPress author display name is an email
address, `<admin-login-email>`, exposed publicly through:

- `/wp-json/wp/v2/users` (REST API)
- the author archive URL `/author/<admin-login-slug>/`

**The fix, when done:**

1. Change the author display name and nickname off the email address
2. Change the author slug
3. Disable author archives
4. Block user enumeration (`/wp-json/wp/v2/users`, `?author=1`)
5. Re-fetch the REST API and the old author URL to confirm the address is gone

**Status:** NOT DOING IN THIS SESSION. Ryan is tracking it as his own todo.
Note that it is unrelated to the copy work - it can be fixed independently, at any
time, and does not wait on any other answer in this file.
- `Ayurvedic Thai stretching` stays a service description, never a credential name.
- **Scope drift - keep both phrasings to a plain description of the stretching
  (CORRECTION 2026-10-04, review).** "Ayurvedic" and any Thai "energy line" wording
  must describe the **stretching technique**, not a separate modality or service.
  **OAR 334-010-0025(4), verbatim:** "A Licensed massage therapist must not perform or
  offer to perform any services for clients other than those connected with giving
  massage therapy treatments as defined in ORS 687, unless the LMT has additional
  training and/or licensure." Describing a modality she is not licensed or trained in
  is the drift to avoid.

## 8. Open items not answerable by Ryan

| Item | Needs | Status |
|------|-------|--------|
| Licence lookup screenshot | A browser session | Not done |
| Illinois licence and its verification path | IDFPR, not OBMT | Not checked |
| Licence number publish-or-omit choice | Butsaba | Open |
| Move and availability dates | `Relocating_Habitat/` | Owned there; do not restate |

## 9. Corrections for the checklist

Recorded so the checklist does not propagate them. These are source-accuracy
notes, not answers.

1. The rule cited as governing outcall does not appear in the current OBMT rule
   index. Confirm the correct rule number against the OBMT OAR chapter 334
   (July 1, 2024 edition) before relying on it.
2. The facility-permit exemption and its scope should be read from the rule text
   directly, not from the secondary summary the checklist cites.
3. The sitewide search term list is a risk-reduction style guide. Only the scope
   and diagnosis items are backed by the rules cited in checklist section 0.
4. The checklist treats this project as Butsaba's only site. It is one of two,
   with different names and different service areas.
5. The resume-source audit's "no stale location terms" observation describes the
   other project, not `thaisolitude.com`.

## 10. What was and was not done

**Done.** All 12 `[ASK]` and both `[VERIFY]` items in `Thai Solitude Fix Checklist.md`
are answered or recorded as open. Oregon rules were read from the primary OAR text,
which corrected two citations in the checklist. Two workspace directories were
searched for the training detail and it is not recorded anywhere in them.

**Not done, and deliberately.** No website, database, host or DNS change. No file in
this project edited other than this one and `AGENTS.md`. The resume project's own
documents were read, not edited.

**Not verified in this session,** so not to be treated as settled:

- licence LMT-29905 (lookup not run; Ryan confirms it is known)
- any Illinois licence
- ORS 687.021 verbatim (the checklist's characterisation of it conflicts with the
  facility-permit rule text, and the rule text is what was read)
- any research cited on the site (section 7.1 asks for the sources)
- what the images depict (Ryan classified them; no files were opened)
- anything behind the WordPress login

**Time-sensitive items:** the facility-permit question to the Board, and the licence
lookup, both gate the address and the footer. The room search and the permit
application have to agree on one location and one business name before either is
committed.

## Next pass

0. **Do now (section 5a):** verify LMT-29905 plus the discipline lookup and expiry;
   fix the author email leak; ship the five no-new-information edits; noindex the
   resume PDF; extend `verify-local.php`.
1. **Ryan:** the resume PDF check (section 6).
2. **Butsaba:** one batched call - the nine items in the status table.
3. **Send the Board** the rewritten facility-permit question (section 1.12).
4. **Privacy policy and the health note** - needed soon, previously missing from this
   list.
5. **Add business registration, and any Portland/Multnomah tax registration, to the
   room sequence.** Not yet verified.
6. **Then** the copy pass, in the order the answers allow - licence line first, so
   nothing publishes before the licence is confirmed active.
# Thai Solitude - Compliance Register

Date compiled: October 4, 2026.

> **Status: planning only.** No website, database, host or DNS change has been made.
> Rules and sources carry their verified date; reconfirm before acting.

**What this file is.** The site's compliance rules and what is wrong on the site
against them. It owns *findings and rules* and nothing else:

| Question | File |
|----------|------|
| What are the rules, and what breaks them? | **this file** |
| What did Ryan decide? | `Thai Solitude - Decisions Register.md` |
| Which sources support the medical claims? | `Thai Solitude - Evidence Register.md` |
| The original page-by-page item list | `Thai Solitude Fix Checklist.md` |

The three registers are views of one body of work. Where this file and the original
checklist disagree, **this file is current** and the checklist is the historical audit.

## Regulatory rules

Read verbatim from the OBMT OAR chapter 334, July 1 2024 edition, on 2026-10-04.

### Sources

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

## Findings

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

## Corrections to the earlier checklists

Recorded so the earlier documents stop propagating them. **Rulings, not open questions.**

| # | The earlier claim | Current position |
|---|-------------------|------------------|
| 1 | OAR 334-010-0031 governs outcall | **Wrong - the rule does not exist.** Confirmed against the OBMT rule index. Outcall is **OAR 334-020-0005(2)** |
| 2 | The facility-permit exemption does not reach a sole LMT | **Wrong - reversed.** **ORS 676.160(6)** lists the State Board of Massage Therapists as a health professional regulatory board, so OAR 334-020-0005(1)(C)(i) likely exempts a sole LMT's own room. The live question is **who owns or operates a rented room** |
| 3 | The sitewide search-term list is rule-backed | **Partly.** Only the **scope and diagnosis** items are backed by OAR 334-010-0025(2)(c) and (4). The rest is a risk-reduction style guide |
| 4 | This project is Butsaba's only site | **Wrong.** It is one of two, with different names (`Butsaba Dusrai` / `Sa Rai`) and different service areas |
| 5 | The resume-source audit found "no stale location terms" | **Wrong project.** That observation describes the Willowy Hollow site, not `thaisolitude.com` |
| 6 | Chicago is past tense | **Premature until Oct 27, 2026.** Still current today |
| 7 | "Based in Mt. Tabor" | **Premature until arrival.** Use "Relocating to Portland (Mt. Tabor area)" |
| 8 | The name split is a later question | **It is a compliance question now.** OAR 334-040-0010(10) makes practising under a false or assumed name a discipline ground |

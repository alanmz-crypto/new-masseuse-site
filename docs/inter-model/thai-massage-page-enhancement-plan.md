# Plan: enhance https://thaisolitude.com/thai-massage/

Written 2026-10-19 for Ryan. **Arc: none (ad-hoc).** Input: `deep-research-report.md` (same folder).
**PLAN ONLY. Nothing was changed on localhost, staging33, live or in Search Console.** Every step below is a proposal that needs your go.

## 1. Bottom line

- The report's direction is sound (add cited, hedged research to the Thai page, keep disclaimers plain). Its **execution does not fit this site**, and its **study table has errors**. I checked every study against the primary abstract (Europe PMC / PubMed / Cochrane / NCCIH / UNESCO).
- Its own sample wording ("may help relieve pain", "headache intensity", "chronic low back pain") **fails the site's wording gate** (`relieve`, `headache`, `chronic`), so it could not be used as written.
- The right-sized version is the same pattern the **Therapeutic page already uses**: 2 to 3 hedged sentences with numbered PubMed links, no new menu, no search widget, no styled box.
- Checking the page itself turned up bigger practical gaps than "no research": a **broken meta description**, and **images that contradict the text** (section 2).

## 2. The page today (cached copy from 01:31 UTC 2026-10-19; page ID 1160; last modified 2026-10-16)

| Item | State |
|---|---|
| Sections | "What Is Thai Massage?", "Common Reasons Clients Choose Thai Massage", "What To Expect During Your Appointment" (4 steps). Jump row: 3 links. |
| Research / sources | **None.** (Therapeutic has numbered PubMed links [1]-[11]; Deep Tissue has some too.) |
| FAQ accordion | **None.** (Therapeutic has 6 questions.) |
| In-page link to Contact / Services / About | **None** (only the jump row). No in-page call to action. |
| Meta description | **Broken**: "Thai Massage in Mt. Tabor What Is Thai Massage? Thai massage brings movement and massage together through rhythmic compression, gentle rocking, and assisted" (heading text bleeds in, cut off mid-sentence). Same text in og/twitter description. |
| Images | **3 files, all the same illustration in three colour tints** (client lying on a massage table under a sheet, therapist beside). **Identical set to the Deep Tissue page.** The page text says "you stay fully clothed" and "assisted stretching". All three share one alt text. 512 px wide. |
| Structured data | WebPage + LocalBusiness only (no service entry). Fine; low priority. |
| What the site already says about Butsaba's Thai training | About page: "I trained in traditional Thai bodywork at Still Light Center and in massage therapy at Estelle Medical Academy. Thai massage is my specialty." Not repeated on the Thai page. |

## 3. The report checked against sources

| Report says | What the source actually says | Verdict |
|---|---|---|
| Juntakarn 2017, N=120, Thai massage = joint mobilization for low-back pain | Correct (PMID 28690703, Int J Ther Massage Bodywork). But the paper says plainly: "randomized study **without control group**". It cannot show Thai massage beats doing nothing. The report's sample text ("reduced low back pain as much as joint mobilization") over-reads it. Thai protocol also included stretching and herbal hot packs. | Usable only with the no-control caveat |
| Chiranthanut 2014: "Thai massage + compress = ibuprofen" | **Three arms**: Thai massage alone, herbal compress alone, ibuprofen; 60 patients; 3 weeks; "nearly comparable" results (PMID 25254207, BioMed Res Int). | Wrong as written. Knee-arthritis topic: do not use. |
| Damapong 2015: N~30, J Altern Complement Med, headache frequency, "= amitriptyline" | **60 patients (30 v 30)**; journal is Evid Based Complement Alternat Med; outcomes were pain scale, tissue hardness, pressure threshold, heart-rate variability; between-group differences favoured the massage group (PMID 26472986). | Four details wrong. Headache is a banned topic here: do not use. |
| Sripongngam 2015: J Altern Complement Med, N=20, Thai massage lowered stress more than control | Journal is **Med Sci Monit Basic Res**; **29** healthy adults, crossover; "**both TTM and rest** can reduce psychological stress"; only one of the markers (salivary alpha-amylase) differed between groups, "a modest effect" (PMID 26436433). | Overstated. Only usable as "both lowered stress markers". |
| MacSween 2018: N~60, Thai beat Swedish for energy | **20** participants, crossover, self-report plus interviews. Thai more energising; **Swedish larger for relaxation and sleep** (PMID 30100318). | One-sided and wrong N |
| Cochrane low-back pain (Furlan 2015) | Correct: 25 trials, 3,096 people, short-term pain/function benefit versus inactive controls, **"very little confidence"** (low or very-low quality). Not Thai-specific. | Correct; use only for a limits sentence, if at all |
| Cochrane neck pain "2023" | Published **Feb 2024** (searched to Oct 2023); 33 studies, 1,994 people; massage versus placebo massage "probably results in little to no difference" at about 12 weeks; low certainty. | Right finding, wrong year. See section 8. |
| "No reports of serious adverse events" as a safety line | True only inside the Cochrane back-pain trials. **NCCIH says rare serious events have been reported** (blood clot, nerve injury, bone fracture), mostly with vigorous massage or people at higher risk. | Do not use as a general safety claim |
| Hongsuwan 2015, Chatchawan 2014 (headache), Peungsuwan 2014 | Taken from Healthline in the report. I could not confirm the findings it gives. | Unverified: do not use |
| NCCIH page as the anchor | Real and consumer-friendly, but **last updated May 2019** and it **does not mention Thai massage**. | Fine as general background |

**Best Thai-specific source found (not in the report):** Keeratitanont et al. 2015, "The efficacy of traditional Thai massage for the treatment of chronic pain: a systematic review", Complement Ther Clin Pract (PMID 25682523). Six studies; all reported less pain after a course of sessions (25 to 80 percent, before-and-after); authors say more research is needed to compare with control conditions.

## 4. What to keep, adapt or drop from the report

| Report idea | Recommendation | Why |
|---|---|---|
| "What Research Says" with a condition list (back, neck, headaches, arthritis, stress, flexibility) | **Adapt**: 2-3 sentences, no condition list | Headache and arthritis claims are outside OAR 334 (no diagnosis, no condition treatment) and the site gate; Therapeutic page already sets the safer level |
| Styled evidence box with icon | **Drop** | Needs new CSS, dark-mode colours and re-audits for no gain; a plain paragraph with numbered links matches the other pages |
| Plain-language disclaimer | **Adapt**: one sentence | Footer already carries "not a substitute for medical care" |
| Safety bullet list | **Keep what exists** | "What To Expect" already covers conditions, medications, pregnancy, injury, surgery, and red-flag symptoms |
| "Massage Research" item in the header menu | **Drop** | One menu feeds header, phone menu and footer; a sixth footer item already wrapped (the footer-menu experiment you dropped on 2026-10-17) |
| Google Programmable Search box | **Drop** | Loads Google scripts and cookies (the Privacy page says no cookies, outside loads only fonts and the map); free version shows ads; would need the CSP loosened and Privacy and Accessibility pages rewritten; invites health-advice searching |
| Separate Research / Resources page and footer "Resources" links | **Drop for now** | Off-purpose for a booking site; the footer is one centred row |
| Consent checkbox on booking forms | **Not applicable** | The site has no forms |
| "Book Now" button | **Optional**: a closing "Get in Touch" line to /contact/ | The site's call to action is "Get in Touch" |
| "Use third person, avoid I/we" | **Conflict** | Service pages speak in Butsaba's "I" by design; the rule in force is no we/our/us |
| FTC angle | **Adapt** | The operative standard is OBMT / OAR 334 (same compliance check as the Therapeutic page) |
| Search queries and data extraction | **Done** | This plan is that work; table in section 3 |
| 12-15 hour estimate and Gantt | **Replace** | See section 6 |

## 5. Sources that can be cited (all checked against the primary page)

| # | Source | Link | Safe claim |
|---|---|---|---|
| A | UNESCO, "Nuad Thai, traditional Thai massage" | https://ich.unesco.org/en/RL/nuad-thai-traditional-thai-massage-01384 | Added in 2019 to the Representative List of the Intangible Cultural Heritage of Humanity; roots in everyday village self-care |
| B | Keeratitanont 2015, systematic review | https://pubmed.ncbi.nlm.nih.gov/25682523/ | Six studies; people reported less pain after a course; more research needed vs other approaches |
| C | MacSween 2018, crossover trial (n=20) | https://pubmed.ncbi.nlm.nih.gov/30100318/ | Both Thai and Swedish massage reported to support well-being; Thai more energising, Swedish more relaxing |
| D (optional) | Juntakarn 2017, trial (n=120) | https://pubmed.ncbi.nlm.nih.gov/28690703/ | Thai massage and joint mobilization improved about equally; **no untreated comparison group** |
| E (optional) | Sripongngam 2015 (n=29) | https://pubmed.ncbi.nlm.nih.gov/26436433/ | Both massage and rest lowered stress markers; one marker differed modestly |
| F (optional, safety) | NCCIH fact sheet | https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know | "Don't use massage therapy to postpone seeing a health care provider about a medical problem." Risk "appears to be low" with rare serious events reported |

Practical note for testing: PubMed answers scripted requests with a bot challenge (HTTP 203), so a link checker must treat that as "reachable" or check through Europe PMC.

## 6. The plan, in order

Everything is made on **staging33 first** (it is currently identical to live). A live write needs your explicit go, and you run the live `--apply` yourself with `!`, as with the Therapeutic page.

**Phase 0: your decisions (section 7).** Nothing starts until Q1 to Q3 are answered.

**Phase 1: meta title and description (small, reversible).**
- Proposed title: `Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT` (54 characters).
- Proposed description: `Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.` (150 characters; both pass the wording gate).
- How: a guarded installer for page 1160 through SureRank's own saver (`update_post_meta_common`, which also fixes the og and twitter copies); precedent is the Services page in `client-pages/install-client-pages-staging.php`. Undo = delete the six rows it writes.
- Verify: rendered HTML (title, description, og, twitter), `client-pages/verify-client-pages.py` extended to cover the Thai page. The Therapeutic and Deep Tissue descriptions are also still auto-built from page text; offer the same fix for them as a follow-on.

**Phase 2: copy (medium). Heritage + a short research paragraph + one caution sentence** (draft wording in section 8a; all gate-tested).
- Placement avoids new headings, so the jump row (limit of four) and its installer do not change: heritage as the last short paragraph of "What Is Thai Massage?", research as the second paragraph of "Common Reasons".
- How: a copy of `therapeutic/install-therapeutic-copy.php` and `push-therapeutic-copy.sh` for page 1160 (exact-replacement guard, host allow-list localhost and staging33 only, snapshot outside the web root, rollback, wording check that mirrors the gate), numbered `[1]`.. links with the same markup as the Therapeutic page.
- Compliance: repeat the check done for the Therapeutic page (OAR 334-040-0010(7), 334-030-0005(1)(d), 334-010-0025(2)(c), (4), (9); ORS 687.011). Limits are the same: not legal advice or a Board ruling.

**Phase 3: FAQ accordion (medium, blocked on Butsaba's answers).** Same Spectra accordion as Therapeutic and About. Answerable today from what the site already says: what to wear (loose, comfortable, fully clothed); asking for lighter pressure or skipping a movement; telling me about health concerns first. **Needs Butsaba** (see Q3): mat or table, session lengths and prices, "do I need to be flexible", how Thai differs from her other styles, whether she travels to clients. No FAQ structured data (no benefit expected, and it would have to match the page exactly).

**Phase 4: images (depends on art).** Today's three images are one illustration, shared with Deep Tissue, and show a table session under a sheet. Options: (a) new Thai-specific illustrations from you or photos from Butsaba, shown as she really works; (b) as an interim, unique and accurate alt text, with `alt=""` for the two repeats that add nothing. I will not guess what a replacement should show.

**Phase 5: verification and release (the usual routine).**
1. `scripts/check-all.sh`; `post-change-test.sh staging33` (expect 18 of 20 page-views identical, the Thai page differing as intended at desktop and phone width); `prove-restore.sh staging33` (only page 1160 and its revision rows move).
2. Rendered-page gates: wording gate on the Thai page, every cited link reachable, no side-scroll at 1280/390/320, no duplicate headings, jump row intact.
3. `tools/a11y-audit.mjs` and `tools/theme-test.mjs --contrast` (light and dark; no new styled element, so no new colours to check); Firefox as well as Chromium. `csp-test.mjs` only matters if an embed is added (none planned: outbound links only, so the Privacy page stays true).
4. Update the Accessibility page date only if the audit or its limitations change (a separate live apply; batch it with this release).
5. Live: your explicit go; I prepare a tested allow-list patch (the auto-mode classifier refuses an agent adding a live host); verified off-server backup first; you run the apply with `!`; then `post-change-test.sh live`, `prove-restore.sh live`, and optionally "Request indexing" for /thai-massage/ in Search Console.

**Effort (replacing the report's 12-15 hours):** Phase 1 about one short session; Phase 2 about one session (same size as the Therapeutic copy); Phase 3 about one session after the answers; Phase 4 depends on the art.

## 7. Questions for you (and Butsaba)

1. **Research depth.** A) heritage line only; **B) recommended**: heritage + research paragraph (sources A, B, C) + caution; C) B plus the optional Juntakarn and Sripongngam sentences.
2. **Images.** New Thai-specific art or photos, or the interim alt-text fix?
3. **Facts only Butsaba knows** (for the FAQ): mat or table; session lengths and prices; whether she comes to clients; reassurance wording about flexibility. The site currently says none of these for Thai Solitude.
4. **Therapeutic page sentence** (section 9): leave, soften, or reword?
5. **Meta title and description** in Phase 1: approve or edit?
6. **Optional closing line** linking to Contact ("Get in Touch"): yes or no?

## 8. Draft wording (gate-tested, nothing applied)

a) Passes the site's banned-word gate (same patterns as `verify-client-pages.py`):

- **Heritage** (end of "What Is Thai Massage?"): "Traditional Thai massage, known in Thai as nuad Thai, grew out of everyday care in Thai villages. In 2019 UNESCO added it to its Representative List of the Intangible Cultural Heritage of Humanity.[1] I trained in traditional Thai bodywork at Still Light Center." (training sentence already on About)
- **S1** (Common Reasons, paragraph 2): "Research on traditional Thai massage is still limited. A 2015 review of six studies found that people reported less pain after a course of sessions, and its authors call for more research on how Thai massage compares with other approaches.[2]"
- **S2**: "In a small crossover study of 20 adults who felt tired or low on energy, both Thai and Swedish massage were reported to support well-being, with Thai massage described as more energising and Swedish massage as more relaxing.[3]"
- **S3 (option C only)**: "In a hospital trial in Bangkok, 120 adults with low-back pain received either Thai massage or joint mobilization twice a week for four weeks. Both groups improved by similar amounts on pain and daily-movement scores, and the trial had no comparison group that received neither."
- **Caution**: "This is general information about published studies, not medical advice, and people respond differently."

b) Wording I avoided on purpose: "relief/relieve", "headache", "chronic" (site gate); "disability" (site gate; the first draft of S3 failed on it); "treatment/treat" and condition names such as arthritis (my extra caution under OAR 334-010-0025(2)(c)).

## 9. Side finding about a page that is already live (no change made)

The live **Therapeutic Massage** page says: "Research suggests massage may be helpful for some people with neck or shoulder pain, though the benefits may last only a short time" with NCCIH [11] as the source. That wording matches NCCIH, but NCCIH's page was last updated in **May 2019**. The newer **Cochrane neck-pain review (published Feb 2024)** found that massage versus a placebo massage "probably results in little to no difference" in pain at about 12 weeks (low-certainty; it also notes a possible benefit with higher doses, 8 or more sessions of 30 minutes or more over four weeks). The live sentence is hedged and still traceable to its source, so this is **not an error**, but it is worth a decision: leave it, soften it, or add the Cochrane finding. If you want a change, it goes through staging33 like everything else.

## 10. Standing rules this plan relies on

Regulated advertising wording (OBMT) and the house gate; no MU-plugins (everything in page content, options, Additional CSS); change on staging33 first; verified off-server backup before any write; live only on your explicit go with your own `!` run; no direct live writes while a staging change is pending if you plan a Custom Deploy.

<!-- src: ./deep-research-report.md — the report this plan checks and adapts -->
<!-- ref: ../../scripts/work-with-me-employer/therapeutic/install-therapeutic-copy.php — the guarded exact-replacement pattern Phase 2 would copy -->
<!-- ref: ../../scripts/work-with-me-employer/client-pages/verify-client-pages.py — the wording gate used for section 8 -->

# Plan: bring https://thaisolitude.com/therapeutic-massage/ up to the standard of https://thaisolitude.com/thai-massage/

Written 2026-10-19 for Ryan. **Arc: none (ad-hoc).** Input: `~/Downloads/deep-research-report (1).md` (Ryan: "these are the instructions we have to follow").
**STATUS UPDATE (2026-10-19, later): BUILT on localhost and staging34 only; live NOT touched.** Ryan answered the six questions in section 7 with "use your judgement to make it honest", so I decided them as follows: Q1 drop the post-stroke and cancer-pain studies (and renumber); Q2 soften the neck/shoulder sentence to "mixed" and name both sources; Q3 the Thai rules (all massage-scope evidence incl. unfavourable results, no disease-treatment studies, no new pictures); Q4 only facts the site already states go into the FAQ (what she lies on, clothing, session lengths, pregnancy handling and techniques are NOT stated); Q5 the staging34 meta pair stands; Q6 no new call-to-action line and no body links. Two deviations from the plan below: the shared Thai helpers are IMPORTED by the new builder instead of being moved into a shared module (zero risk to the Thai output), and the research section grew to 25 sources (the wider search added soreness, flexibility, workplace, cortisol and well-being studies, each abstract read). Where it stands, what was verified and the rollback command: AGENTS.md ("Therapeutic Massage page: cited research section ...") and `scripts/work-with-me-employer/therapeutic/`. Below is the original plan, unchanged.

**ORIGINAL STATUS (at the time of writing): PLAN ONLY. Nothing was changed on localhost, staging34, live or in Search Console.** I read the live pages (Therapeutic, About, Services, Contact) and checked the report and every study I propose against the primary abstracts (Europe PMC API) and, for the Cleveland Clinic quote, the page itself.

## 1. Bottom line

- The report is the same kind of document as the Thai one: sound goals (clearer structure, a FAQ, research, meta tags, a call to action), but **it cannot be followed as written**. Roughly a third of it fails the site's own rules (we/our/us, "relief", "chronic", "headaches", "anxiety", "nervous system", "accelerates recovery"), a few claims are invented (clients "report relief", insurance with a CPT code, a pregnancy service, "6+ years of training"), and two statements about the site are false (it says neither page has structured data; it compares against Deep Tissue, not the Thai page you named).
- What it asks for that is **already true today**: reading ease. The Therapeutic page scores about **63 on the Flesch scale** (15 words per sentence), inside the report's own 60 to 70 target. It already has an FAQ accordion (6 questions), inline numbered research links ([1] to [11]), a jump row, one H1 and four H2s, and structured data.
- "Emulating the Thai page" therefore means the parts the Thai page has and this one does not: **a cited "What the Research Says" section in collapsible panels with a Sources list, a fuller generic FAQ, a proper meta title and description, and unique alt text.** The research is where the report adds nothing usable; I would build it from the same kind of sources as the Thai page (section 5).
- **Two findings need your decision before anything is built (section 7, Q1 and Q2):** the page already cites a **stroke** study and a **cancer-pain** study, and its neck and shoulder sentence now sits against the **2024 Cochrane review**, which the Thai page already reports.

## 2. The page today (live copy fetched 2026-10-19; page ID 1107; last modified 2026-10-19 00:29 UTC, my copy change)

| Item | State |
|---|---|
| Headings | H1 "Therapeutic Massage in Portland"; H2 What Is Therapeutic Massage? / Common Reasons Clients Choose Therapeutic Massage / Who Can Benefit From Therapeutic Massage? / What To Expect During Your Appointment. Jump row: 4 links (the limit). |
| Length and reading level | About 660 words of prose, 43 sentences, 15.3 words per sentence, **Flesch reading ease 62.7** (my count, simple syllable rule). |
| FAQ | **Already an accordion** (same Spectra block as About and Thai): difference from relaxation massage; how a session is planned; results right away; how often; no specific complaint; neck and shoulders. Answers hold no links. |
| Research | Inline numbered links [1] to [11] in "What Is" and "Common Reasons" (PubMed links, NCCIH for [11]). **No Sources list, no research section, no titles shown.** |
| Meta title / description | Title `Therapeutic Massage - Thai Solitude`. Description is **auto-built and cut off**: "Therapeutic Massage in Portland What Is Therapeutic Massage? Therapeutic massage combines focused work with time to relax. I start with what brought you in." (same in og and twitter). A gate-tested pair is already **on staging34 only, awaiting your approval** (section 6, Phase 1). |
| Images | 5 pictures in the page, **all with the same alt text** "Illustration of a massage therapist working on a client lying face down." They are the illustrations shared with Deep Tissue. The link-preview image (og:image) is `ComfyUI_01040_curves.webp`. |
| Structured data | **Present**: WebSite, WebPage, Organization (email, phone, logo), BreadcrumbList, LocalBusiness (Portland, OR 97213). |
| Call to action | None in the text. The header has "Get in Touch" and the Contact page lists phone and email; there is **no booking system and no form**. |
| What the site says about her | About: "I have practiced massage since 2020"; trained at Still Light Center (Thai bodywork) and Estelle Medical Academy (massage therapy); "Thai massage is my specialty, and I also offer therapeutic and relaxation massage"; independent practice; Portland is new ("I look forward to welcoming clients here"). |

## 3. The report checked against the site and the sources

| Report says | What is true | Verdict |
|---|---|---|
| Compare with the **Deep Tissue** exemplar | You named the Thai page as the model. Deep Tissue has no FAQ and no research section (the report itself says so). | Re-aimed at the Thai page |
| "Neither page has structured data" | Both pages carry WebSite, WebPage, Organization, BreadcrumbList and LocalBusiness (read from the live page). | **False.** Drop the JSON-LD task |
| Proposed JSON-LD (`HealthAndBeautyBusiness`, `image: .../logo.png`, offers "reduce pain", "release chronic tightness") | Would duplicate the existing LocalBusiness under another type, points at a logo file that does not exist (the real one is `cropped-000LogoCubesPx256.webp`), and puts banned and claim wording in a description. It also repeats the (312) number, which is still an open question for Butsaba. | Drop |
| "Therapeutic is ~400+ words; aim for 600 to 800 and Flesch 60 to 70" | About 660 words already; Flesch 62.7 already. A research section in collapsed panels adds length the way the Thai page did, without lengthening the main read. | Target already met |
| FAQ "styled as bold text, not headings" | They are already a Spectra accordion. | Report misread the page |
| Voice: "our licensed therapist", "we use", "we offer", "Thai Solitude provides", "contact us" | The site speaks as Butsaba ("I"); **we/our/us is never accepted** (tier 1 of your banned-word rule). It is her independent practice. | Rewrite in "I" |
| "Many clients report relief from tension and improved well-being" | An invented outcome claim. She has no Portland clients yet; the site must not imply outcomes (OAR 334-040-0010(7); the house rule on testimonials). | Drop |
| Bullets: "Relieves muscle tension and chronic pain", "Accelerates injury recovery", "Headaches and TMJ", "stress or anxiety ... relax their nervous system", "post-surgery clients ... to aid recovery", "athletes with chronic tightness" | Banned words (relief, chronic, headache, accelerate, anxiety, nervous system) **and** they read as treating conditions (OAR 334-010-0025(2)(c) and (4)). The research-section exception does not cover marketing copy. | Drop. The evidence goes in the research section, where study subjects may be named |
| "6+ years of training" | About says she has **practiced** since 2020; training is Still Light Center and Estelle. | Use About's wording only |
| "A blend of techniques (Swedish, deep work, myofascial release, trigger-point release, stretching)" | No page says she uses these. | Needs Butsaba |
| "Yes, we offer pregnancy-safe positioning (on side, with pillows)" and a pregnancy FAQ | The site only says: tell me about pregnancy; I may adjust or suggest you check with your clinician. Whether she has the training and set-up is hers to say. | Needs Butsaba. Until then the FAQ restates the existing sentence |
| Insurance, HSA/FSA, "a receipt with the CPT code (assuming Oregon covers massage)" | Nothing on the site says this, and "assuming" shows it is a guess. Billing codes also imply a health service. | **Drop** (false or misleading advertising risk) |
| "Many remove all clothing", sheet draping, oils or lotions, music, hydration advice | Practice details only she knows. | Needs Butsaba |
| "After-care tips to maximize your results" | Implies a result. The site says she answers questions about future sessions. | Adapt |
| "Book online" / "Schedule your appointment" | No booking system exists; the call to action is "Get in Touch" (Contact: phone, email). | A "Get in Touch" link only, if wanted |
| Cleveland Clinic: "a gentle, soothing massage may help reduce stress, relieve achy muscles, improve mood and even sleep better" | **Real** (health.clevelandclinic.org, "Therapeutic Massage: What It Is and 4 Benefits", 21 April 2025). But it is a consumer article filed under *Chronic Pain*, subtitled "Medical massage is tailored to your health needs to help you recover", and uses "relieve". The second quote ("involves focused work on your body's soft tissue") I could not find. | Do not quote. Use trial and review sources |
| Meta: "Relieve tension and pain ... Book online today!" / "Our certified LMT ... relief" / title "Relief from Pain & Tension" | Fail the site gate (`relieve`, `relief`, `our`), say "certified" (the Board's word is licensed) and promise online booking. | Replace with the staging34 pair |
| Keywords "pain relief massage", "massage for anxiety stress Portland", "near me" | Banned terms or a location claim beyond Mt. Tabor. The H1 already says "Therapeutic Massage in Portland". | Skip |
| Alt text "Therapeutic Massage by Butsaba" / add "Portland" | Today all five alts are the identical sentence. Illustrations should be described as what they show. | Adapt: five distinct, accurate alts |
| Competitor table (four named local businesses) | Unverified and not needed for copy on a regulated site. | Not used |
| Internal links to Deep Tissue, Thai, Contact | Fine in principle. FAQ answers cannot hold links (the accordion makes them an accessibility failure); in body text a plain link is possible. | Optional, Q6 |
| 2-week checklist, rankings monitoring | Replaced by section 6. | Replace |

## 4. What I keep, adapt or drop from the report

- **Keep (already true or already planned):** one H1, short paragraphs, plain words; an in-page call to action as a "Get in Touch" link; unique alt text; a proper meta pair; an FAQ with the comparison "Therapeutic vs Deep Tissue" as the first new question.
- **Adapt:** every benefit becomes a study summary in the research section with its limits, not a promise; the pregnancy and clothing answers wait for Butsaba; "Mt. Tabor" appears in the meta and FAQ (the report's own point about local wording).
- **Drop:** JSON-LD, insurance and CPT, invented client outcomes, "training" years, the benefit bullets with banned words, the competitor table, keyword lists with banned terms, "Book online".

## 5. The research section: sources (all abstracts read)

Plan: the same shape as the Thai page, appended after the last block so nothing above moves: **"What the Research Says About Therapeutic Massage"**, an intro and five native collapsible panels (Reviews and limits [open]; Neck, shoulders and back; Exercise, flexibility and recovery; Stress, relaxation and well-being; Sources with verbatim titles). Mixed and unfavourable results are included. Studies of massage as a **treatment for a disease** are not used.

**Already cited in the body (numbers stay the same):**

| # | Source | What it actually says | Keep? |
|---|---|---|---|
| 1 | Mak 2024, JAMA Netw Open (PMID 39008297) | Mapped 17 reviews of massage for pain; **none of its conclusions was high-certainty**; 7 were moderate-certainty, all favouring massage for pain; conclusions that massage beat other active approaches were rare. | Yes |
| 2 | Crawford 2016, Pain Med (27165971) | 67 trials; lower pain than sham (SMD -0.44), no treatment (-1.14), active comparators (-0.26); strong recommendation only against no treatment, weak against others. | Yes (with the weak-recommendation caveat) |
| 3 | Davis 2020, BMJ Open Sport Exerc Med (32426160) | 29 studies, 1,012 people: no improvement in strength, jump, sprint, endurance or fatigue; small improvements in flexibility and muscle soreness. | Yes |
| 4 | Meier 2020 (32901072) | 60 healthy women; both massage protocols raised heart-rate variability more than rest, but **relaxation and lower stress rose in every group, rest included**. | Yes (state the rest result) |
| 5 | Sharpe 2007 (17709060) | Pilot, 54 adults 60 and over, twice weekly for 4 weeks; massage beat guided relaxation on well-being and perceived stress. | Yes (it is small) |
| **6** | **Cabanas-Valdes 2021 (33919371)** | Massage (mostly **Tuina**) plus usual rehab for **post-stroke** limb function and spasticity. | **See Q1: a disease-treatment study** |
| **7** | **Win Myint 2025 (39558520)** | Massage for **cancer pain**, quality of life and anxiety in **patients with cancer**; very low-certainty evidence. | **See Q1: a disease-treatment study** |
| 8 | Williams 2019 (31003683) | 38 family caregivers; scores lower after 1 or 3 massages a week, **no non-massage comparison group**. | Yes (state that) |
| 9 | Kurebayashi 2016 (27901219) | 122 adults; massage with rest, massage with **Reiki**, or nothing; stress lower in the treated groups. **One arm included Reiki**, which is not massage. | Yes, saying so |
| 10 | Kurebayashi 2020 (33053005) | Same trial, stress and quality of life; same caveat. | Yes, saying so |
| 11 | NCCIH, "Massage Therapy: What You Need To Know" | General background and safety. | Yes |

**New sources, verified against their abstracts (numbered from 12 in the order the section first uses them):**

| Source | Safe summary |
|---|---|
| Gross 2024, Cochrane, *Massage for neck pain* (38415786) | 33 studies, 1,994 people; versus placebo massage, massage **probably made little or no difference** in pain, function or quality of life at up to 12 weeks (low-certainty); a high-dose subgroup (8 or more sessions of 30 minutes or more over 4 weeks) favoured massage; adverse events unclear. (Same review the Thai page cites.) |
| Furlan 2015, Cochrane, *Massage for low-back pain* (26329399) | 25 trials, 3,096 people; short-term benefit against inactive controls, none long-term; evidence low to very low quality; authors had "very little confidence"; no serious adverse events, increased pain the commonest minor one. (Also on the Thai page.) |
| van den Dolder 2014, Br J Sports Med (22844035) | 20 trials; **low-quality** evidence that soft-tissue massage improved shoulder movement, pain and function in the short term; exercise gave small pain gains below the clinically worthwhile size. |
| van den Dolder 2015, Phys Ther (26023217) | 80 people with shoulder pain: **adding massage to exercise gave no extra benefit**; a small difference favoured exercise alone for pain at 12 weeks. (An unfavourable result, kept on purpose.) |
| Poppendieck 2016, Sports Med (26744335) | 22 trials; effects of massage on performance recovery "rather small and partly unclear"; larger after short massage and short recovery; the authors question whether the limited effects justify its widespread use. |
| Moyer 2004, Psychol Bull (14717648) | 37 randomized studies; a single massage was followed by lower state anxiety, blood pressure and heart rate, **not** by lower cortisol or less immediate pain. (I would quote only these findings; the paper's depression and psychotherapy comparison reads as treating a condition.) |
| Ernst 2003, Rheumatology (12777645) | Safety review: serious adverse effects were rare and mostly tied to "exotic" techniques or laypeople, not to Swedish massage by therapists; "Massage is not entirely risk free." Optional beside NCCIH. |

A wider search in the build step (sleep, blood pressure, workplace, soreness reviews) will add any further trial or review that passes the same test, as I did for the Thai page (25 sources). I read each abstract before using a sentence. PubMed blocks scripted requests, so I use Europe PMC.

## 6. The plan, in order

Everything is built and rehearsed on **localhost, then staging34** (the working copy; it equals live except for the Therapeutic and Deep Tissue meta pair). A live write needs your explicit go and you run the live `--apply` yourself with `!`; I prepare a tested enable-live patch (the auto-mode classifier refuses an agent adding a live host).

**Phase 0: your decisions** (section 7). Nothing is built until Q1 to Q4 are answered.

**Phase 1: meta title and description (already built, on staging34 only, wording waiting for you).**
- Title `Therapeutic Massage | Butsaba Dusrai, Oregon LMT` (48 characters). Description `Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.` (146). Each sentence only restates the page. Both pass the gate; every report version fails it (relieve, relief, our).
- Needs your yes. Rollback on staging34: `S_TARGET=staging34 ./push-service-meta.sh --rollback '$HOME/private-backups/service-meta-before-20261019-054839.json'`.

**Phase 2: generalise the Thai tooling** (no site change). `wording_tiers.RESEARCH_HEADING` is the Thai heading only: make the marker page-neutral (the heading "What the Research Says About X Massage", exactly once); update its self-test and the mutation checks. Move the shared helpers of `thai/build-thai-evidence.py` (source registry, escaping, panel and FAQ blocks) into one module that both pages use; **proof of no side effect: the Thai builder's `--check` output stays byte-identical.** Generalise the push-script public checks and the Thai section of `verify-client-pages.py` into per-page tables.

**Phase 3: the Therapeutic content** (installer `therapeutic/install-therapeutic-evidence.php`, push script, builder, checker, same guards as Thai: host allow-list, exact-text anchors, snapshot outside the web root, read-back, exact rollback, no new CSS, no MU-plugin).
1. Append the research section (intro and five panels) after the last block.
2. Add generic FAQ questions to the existing accordion (section 8), answers link-free.
3. Body edits by exact replacement, only if you choose them in Q1 and Q2 (and the markers renumber so the list has no gaps).
4. Five distinct, accurate alt texts (by exact replacement).
5. Block ids new and checked against the other pages (the Services, Contact and Deep Tissue collision lesson).

**Phase 4: verification** (the Thai routine). `check-all.sh`; real-editor validation (0 invalid blocks); `therapeutic/check-therapeutic-page.mjs` extended for panels, FAQ count and marker numbering; Chromium **and** Firefox; light and dark; 1280/390/320 px; axe 0 violations; my own contrast measurement of every new text element; `post-change-test.sh staging34` (expect 18 of 20 page-views identical and the 2 Therapeutic ones changed by height only); `prove-restore.sh staging34`; rollback proven byte-identical **before** any edit of the builder (lesson recorded).

**Phase 5: live** on your explicit go: tested patch, verified off-server backup, you run the apply, then `post-change-test.sh live` and `prove-restore.sh live`; optional "Request indexing" in Search Console for /therapeutic-massage/ (your account). The Accessibility page's date and limits are updated in the same release if the audit changes.

**Effort:** Phase 2 about one session; Phase 3 and 4 about two sessions (the evidence pass is the long part); Phase 5 one short session. Deep Tissue then reuses the same module.

## 7. Questions for you

1. **The two older studies cited on this page ([6] stroke, [7] cancer pain).** Under the rule I used for the Thai page (no study of massage as a treatment for a disease; OAR 334-010-0025(2)(c) and (4)), these two should not be cited. They are listed in the body as "several adult groups" without naming them, but the link opens a stroke and a cancer-pain paper. **Recommended: remove them**, renumber the body markers, and replace the sentence with sources about caregivers and healthy adults. Alternative: keep them as they are (you approved that wording on 2026-10-18, before the rule existed).
2. **Neck and shoulder sentence** ("Research suggests massage may be helpful for some people with neck or shoulder pain, though the benefits may last only a short time.[11]"). The 2024 Cochrane review (which the research section, like the Thai page, will report) found massage "probably results in little to no difference" against placebo massage. **Recommended: soften to "Research on massage for neck or shoulder pain is mixed.[11][12]"** Alternatives: leave it, or delete it.
3. **Depth.** I will apply your Thai rules unless you say otherwise: all massage-scope evidence including unfavourable and mixed results, no disease-treatment studies, no new images, the three-tier banned-word rule (words in the list are allowed only inside the research section). Say so if any should differ for this page.
4. **Facts for the FAQ.** Ones I can answer from the site today are in section 8. Ones only Butsaba (or you) can answer: what the client lies on (table or mat) and how they are covered or dressed; session lengths (Thai: 60 and 90 minutes, same here?); whether she travels to clients (for Thai: no); how she handles pregnancy; techniques she uses (the report lists myofascial and trigger-point work). I will leave these as clearly marked gaps that do not go live, as for Thai. I can draft a short message for her.
5. **Meta pair** (Phase 1): approve or edit.
6. **A "Get in Touch" line to /contact/ and plain links to Deep Tissue and Thai in the body?** The Thai page has neither. Default: no.

## 8. Draft FAQ additions and research intro (all gate-tested; nothing applied)

Questions I can answer from what the site already says (full banned-word list applies outside the research section; all pass):

- **How Is Therapeutic Massage Different From Deep Tissue Massage?** "Therapeutic massage gives steady attention to the places where you feel tension, at a pace and pressure that suit you. Deep tissue massage uses focused strokes and sustained pressure on areas that stay tight. I check in about pressure in both, and each is described on its own page." (Services wording.)
- **Can I Ask for Lighter Pressure or Skip an Area?** "Yes. You can ask for lighter pressure, a different focus, or a pause at any time, and I will adjust."
- **Where Do Sessions Take Place?** "Sessions take place in Mt. Tabor, Portland." (travel sentence waits for Q4)
- **How Do I Book a Session?** "Use the Contact page to reach me by phone or email, and tell me what you would like. I will reply to arrange a time." (the Thai answer; Butsaba to confirm)
- Waiting on Q4 (not drafted as facts): what to wear and how you are covered; what you lie on; how long a session is; pregnancy.

Research intro: "Research on massage therapy is large but mixed. Many studies are small, results often depend on what massage is compared with, and many follow people for only a few weeks. The summaries below describe what the studies reported. They are general information, not medical advice or a promise of any result, and people respond differently."

Sample panel sentences (voice and claim-verb tiers only, as for the Thai section; each matches its abstract): "A 2024 review that mapped 17 systematic reviews of massage for pain found that none of its conclusions was rated high-certainty. Seven were rated moderate-certainty, all favoring massage for pain, and conclusions that massage beat other active approaches were rare." / "A 2020 review of 29 randomized studies with 1,012 people found no evidence that massage improved strength, jumping, sprinting, endurance or fatigue, but small improvements in flexibility and delayed-onset muscle soreness." / "A 2004 meta-analysis of 37 randomized studies found that a single massage was followed by lower state anxiety, blood pressure and heart rate, but not by lower cortisol or less immediate pain."

## 9. Limits

Not legal advice or a Board ruling; the Board decides case by case what is misleading. The Flesch figure uses a simple syllable rule, so treat it as approximate. The wider evidence search happens in the build step. The page's three-way image mismatch (illustrations of a table session shared with other pages) is unchanged unless you decide otherwise.

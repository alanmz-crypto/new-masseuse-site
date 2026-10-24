# Thai Solitude - Citation Worklist

Date compiled: October 4, 2026. Scope: the Thai Solitude site project
(`~/WordPress/new-masseuse-site`).

Every sentence on the site that makes a claim a reader could check, and what it
needs. Companion to `Thai Solitude Fix Checklist.md` (source of the passages) and
`Thai Solitude Fix Checklist - Answers and Plan.md` (the decisions).

**Verified staging19 status (October 5, 2026):** Numbered, direct PubMed links render beside A1-A6 on Therapeutic Massage and Deep Tissue Massage. The current sentences are narrower than Ryan's original passages. Ryan's latest direction is to keep the sentences Codex already improved, even where later evidence may support earlier wording. No A1-A6 restoration or rewrite is pending. Thai Massage contains no research sentence requiring a citation.

**Ryan's later-evidence rule and clarification (October 5):** If a sentence change was only proposed for lack of evidence and later evidence supports the original, dismiss that proposed change. Ryan clarified that this does not call for undoing any sentence Codex has already improved on staging19. Keep the current improved A1-A6 sentences. The Deep Dive's later research supports original A2 and A3 as research-existence statements, but this does not authorize reverting the current improved copy. Full original A1, A4, A5, and A6 still exceed their sources in at least one material clause or population. No staging19 write has been made under this update; the proposed A2 restoration is withdrawn.

Evidence review: The existing pain, stroke/disability, cancer, and caregiver sources support the research-existence scope of original A2. Kang et al. 2022 (https://pubmed.ncbi.nlm.nih.gov/36133798/) additionally documents therapeutic-massage research in Parkinson's disease. Kurebayashi 2016/2020 and Sharpe 2007 support A3's stress, anxiety, and quality-of-life scope; a brain-tumour distress pilot (https://pubmed.ncbi.nlm.nih.gov/24654222/) documents emotional distress in a difficult period. These findings do not alter Ryan's direction to retain current improved copy, and do not establish that Butsaba provides oncology or neurological services.

## Current execution record (October 5, 2026)

This record supersedes older status and action items below where they conflict with the current staging19 state or Ryan's current instructions for A1-A6.

| Page / items | Observed staging19 status | Remaining check |
|---|---|---|
| Therapeutic Massage, A1-A3 | Direct numbered links render beside the improved claims. Original A2/A3 are supported as research-existence statements by later evidence; full original A1 is not. | Keep the current improved wording; the A2 restoration proposal is withdrawn. |
| Deep Tissue Massage, A4-A6 | Direct numbered links render beside improved claims; original A4-A6 remain too broad for the cited populations and outcomes. | Keep the current improved wording. |
| Thai Massage | The rendered body and title/meta/OG fields contain no research claim; no numbered marker is needed. | No citation edit. The meta description has a separate SEO defect. |
| Services page, ID 299 | The temporary Codex-written research paragraph and bottom References block are absent. | Do not re-add them. The pre-cleanup DB dump is /tmp/pre-citation-cleanup-staging19-20261005.sql. |

Ryan initially directed restoration of his original research claims with direct-linked numbered markers beside them and no bottom reference list. He later clarified that Codex's already improved staging19 sentences should remain. Ryan also clarified that Butsaba has had clients, just not in Portland, and said to leave the relevant client-experience copy. Do not describe her as having no client history or imply prior Portland clients.

Before any further site change, show Ryan the exact sentence, placement and source links. Work one change at a time, wait for Ryan's yes or no, and take a fresh database dump before each staging database write. Do not edit localhost or production. The earlier A2 approval question was superseded by Ryan's instruction to keep the improved sentences.

The source text is recorded in the strict audit and research bundle for A1, the Deep Dive for A2/A3, and the Deep Dive and research bundle for A4-A6. The exact comparison follows. This verifies evidence fit for the current wording; it does not approve the wording change for Ryan.

### Exact wording comparison and source fit, staging19 (October 5)

All sentences below occur in the introductory description block on the named service page. Each bracketed marker is immediately after the sentence it supports. The 14 distinct PubMed destinations correspond to the cited papers. Automated direct requests returned PubMed HTTP 203 challenge responses on this check; the records and abstracts were independently found through PubMed/PMC search. Thus the href targets and paper identity are verified, while a normal browser click-through was not observed in this pass.

- **A1, Therapeutic. Original:** “Research suggests that massage therapy may help reduce pain, improve mobility, support recovery from physical activity, and promote relaxation. Regular massage has also been associated with improvements in overall well-being, making it a valuable part of a long-term self-care and recovery routine.” **Current:** “Research suggests that massage therapy may help reduce pain in some conditions and may improve certain movement-related outcomes in studied pain populations.” [1] Mak, https://pubmed.ncbi.nlm.nih.gov/39008297/; [2] Crawford, https://pubmed.ncbi.nlm.nih.gov/27165971/. “Studies of sports massage have found small improvements in flexibility and exercise-related muscle soreness.” [3] Davis, https://pubmed.ncbi.nlm.nih.gov/32426160/. “Brief massage has been studied for short-term relaxation.” [4] Meier, https://pubmed.ncbi.nlm.nih.gov/32901072/. “Regular massage was associated with improved well-being in a small four-week study of older adults.” [5] Sharpe, https://pubmed.ncbi.nlm.nih.gov/17709060/. **Fit:** the current clauses match their stated populations and outcomes. The original long-term routine recommendation and unqualified breadth do not follow from these sources.
- **A2, Therapeutic. Original:** “Research has explored the use of therapeutic massage among people living with chronic pain, neurological conditions, cancer-related symptoms, disabilities, and the demands of caregiving.” **Current:** “Research has explored massage among people living with chronic pain, people recovering from stroke with related disability, people with cancer-related symptoms, and family caregivers.” [2] Crawford, https://pubmed.ncbi.nlm.nih.gov/27165971/; [6] Cabanas-Valdés, https://pubmed.ncbi.nlm.nih.gov/33919371/; [7] Win Myint, https://pubmed.ncbi.nlm.nih.gov/39558520/; [8] Williams, https://pubmed.ncbi.nlm.nih.gov/31003683/. **Fit:** the current sentence names the studied groups more precisely; the stroke review includes Tuina alongside conventional therapy. These studies do not establish Butsaba serves these groups.
- **A3, Therapeutic. Original:** “It has also been studied in individuals seeking support for stress, anxiety, emotional strain, and reduced quality of life during particularly difficult periods.” **Current:** “Research has also examined massage in relation to stress, anxiety, and quality-of-life measures in specific adult groups.” [9] Kurebayashi 2016, https://pubmed.ncbi.nlm.nih.gov/27901219/; [10] Kurebayashi 2020, https://pubmed.ncbi.nlm.nih.gov/33053005/; [5] Sharpe, https://pubmed.ncbi.nlm.nih.gov/17709060/. **Fit:** current wording confines itself to measured outcomes and study groups; “emotional strain” and “particularly difficult periods” were not established by this set.
- **A4, Deep Tissue. Original:** “Research suggests deep tissue massage may help reduce pain intensity, improve mobility, and support quality of life in people experiencing ongoing musculoskeletal discomfort.” **Current:** “Research suggests deep tissue massage added to conventional rehabilitation may reduce pain intensity and improve some neck-movement and quality-of-life measures in people with myofascial pain syndrome.” [1] Bingölbali, https://pubmed.ncbi.nlm.nih.gov/36645811/. **Fit:** the current sentence reflects the trial's add-on treatment, population, and mixed neck-motion results; the original generalized beyond them.
- **A5, Deep Tissue. Original:** “Studies have also linked deep tissue massage with improvements in flexibility, muscle recovery, and functional movement, though outcomes vary from person to person.” **Current:** “A randomized trial in athletes linked repeated deep tissue massage with improvements in flexibility and recovery-related measures, though the results may not apply to every client.” [2] Arsovski, https://pubmed.ncbi.nlm.nih.gov/40510040/. **Fit:** the study reports repeated treatment and those measures in athletes; the current sentence does not generalize functional movement to all clients.
- **A6, Deep Tissue. Original:** “Current research suggests deep tissue massage can be an effective option for many types of muscular discomfort and movement restrictions, though results depend on the underlying cause.” **Current:** “Trials in specific musculoskeletal conditions suggest deep tissue massage may improve pain or movement-related outcomes for some people, but results vary by condition and outcome, and long-term pain benefits remain uncertain.” [1] Bingölbali, https://pubmed.ncbi.nlm.nih.gov/36645811/; [3] Skillgate, https://pubmed.ncbi.nlm.nih.gov/31655314/; [4] Koren and Kalichman, https://pubmed.ncbi.nlm.nih.gov/29861215/. **Fit:** specific-trial outcomes plus the review's heterogeneity and the STONE trial's lack of long-term pain superiority support the current limitation; “many types” is not established.

No rendered page or page metadata reviewed in this pass contains an additional uncited *research-existence* sentence. Other health or outcome language in metadata remains a separate copy-review item. Ryan's earlier instruction permits bending an unsupported original sentence to match evidence; this audit does not assume he accepts these six specific revisions.

## Why this file exists

The site carries several sentences that cite research without naming a source. The
answer from Ryan (2026-10-04) is **cite, do not delete** - the research is real and
the omission was his. But the review that followed found that "cite" does not cover
everything, so this file sorts the claims into three groups rather than treating
them as one job.

The working rule: **a citation is not a cure for a promise about her results.**
Research about massage in general can be cited. A sentence that says *you* will feel
better, or that she works with a clinical population, cannot be fixed by adding a
source.

## The three groups

| Group | What it is | Action |
|-------|-----------|--------|
| **A. Cite** | General statements about massage research, which a named source can support | Attach a real source to each |
| **B. Delete regardless** | Practitioner-specific outcome promises, and claims implying clinical populations she has no training for | **Remove. A citation does not make these acceptable** |
| **C. Not a research claim** | Words that need rewording for compliance, not sourcing | Handled in the term pass, listed here so they are not confused with citations |

**Group B's older deletion direction is historical.** A citation alone cannot
support a practitioner-specific promise, but Ryan's prior-client correction and
current twice-before-removal rule govern any next edit. Show the exact sentence
and location; do not act on this older group label automatically.

## Source rule (applies to every Group A item)

1. **The source must support the exact wording.** Not a nearby claim, not the general
   topic. OAR 334-040-0010(7) makes false, deceptive or misleading advertising a
   discipline ground, so a citation that overstates its source is worse than none.
2. **Current Ryan format:** put a numbered marker immediately beside its claim,
   and link the number directly to the outside source. No bottom reference list.
3. **If no source matches the original wording,** show Ryan the evidence gap and
   a narrower, evidence-matched sentence. Do not silently rewrite or remove it;
   every proposed website write requires exact per-change approval, and removal
   requires asking twice.
4. **Prefer sources that are checkable by a reader** - peer-reviewed, or a recognised
   professional body. Not a supplement retailer, not an AI summary.
5. **Her licence does not cover research claims.** Research citation is about accuracy,
   not scope; scope is a separate rule (OAR 334-010-0025(4)).

## Group A - cite these

The checklist counts **six** "Research suggests / Studies have / Current research"
sentences: **3 on Therapeutic Massage, 3 on Deep Tissue**.

| # | Page | The sentence, as the checklist records it | Source to supply | Current staging19 status |
|---|------|------------------------------------------|------------------|--------|
| A1 | Therapeutic | "Research suggests that massage therapy may help reduce pain... making it a valuable part of a long-term self-care and recovery routine" | See exact comparison above | Current source fit checked; Ryan chose to keep the improved sentence |
| A2 | Therapeutic | The second research sentence on the page (not quoted by the checklist) | See exact comparison above | Original recovered; Ryan chose to keep the improved sentence |
| A3 | Therapeutic | The third research sentence on the page (not quoted by the checklist) | See exact comparison above | Original recovered; Ryan chose to keep the improved sentence |
| A4 | Deep Tissue | "Research suggests deep tissue massage may help reduce pain intensity..." | See exact comparison above | Current source fit checked; Ryan chose to keep the improved sentence |
| A5 | Deep Tissue | "Studies have also linked... flexibility, muscle recovery, and functional movement" | See exact comparison above | Current source fit checked; Ryan chose to keep the improved sentence |
| A6 | Deep Tissue | "Current research suggests... can be an effective option" | See exact comparison above | Current source fit checked; Ryan chose to keep the improved sentence |

**Note on A2 and A3:** their exact original wording is recorded in the Medical
Citations Deep Dive. The staging19 copy has since been revised; compare it with
those originals rather than treating the older capture note as current.

**SOURCES SUPPLIED (2026-10-04).** Ryan provided
`Thai_Solitude_Medical_Citations_Research_Bundle.md`. All five primary sources were
**independently verified in this session** - each exists and the citation details match.
See section 5A.

The bundle also supplies **evidence-calibrated rewrites** for A1, A4, A5 and A6, and
judges the current wording of A4, A5 and A6 as **too broad as written**. So for those
three the fix is to **rewrite the sentence and then cite it**, not to bolt a citation
onto wording the evidence does not support. The bundle says this explicitly.

## 5A. The supplied source set

From `Thai_Solitude_Medical_Citations_Research_Bundle.md` (Ryan, 2026-10-04).
**Verified column = independently checked in this session, not taken on trust.**

| ID | Source | Verified | Supports |
|----|--------|----------|----------|
| **S1** | Mak et al. (2024). Use of Massage Therapy for Pain, 2018-2023: A Systematic Review. JAMA Netw Open 7(7):e2422259. doi:10.1001/jamanetworkopen.2024.22259. PMID 39008297 | **Yes** - full text read | Broad massage-for-pain; also a limitation source - most conclusions are low or very-low certainty |
| **S2** | Crawford et al. (2016). The Impact of Massage Therapy on Function in Pain Populations (Part I). Pain Med 17(7):1353-1375. doi:10.1093/pm/pnw099. PMID 27165971 | **Yes** - full text read | Pain, activity/function, anxiety, quality of life in pain populations |
| **S3** | Davis, Alabed & Chico (2020). Effect of sports massage on performance and recovery. BMJ Open Sport Exerc Med 6(1):e000614. doi:10.1136/bmjsem-2019-000614. PMID 32426160 | **Yes** - full text read | Small improvements in flexibility and DOMS. NO evidence for strength, sprint, jump, endurance or fatigue |
| **S4** | Bingolbali et al. (2024). The effectiveness of deep tissue massage... myofascial pain syndrome. Somatosens Mot Res 41(1):11-17. doi:10.1080/08990220.2023.2165054. PMID 36645811 | **Yes** - abstract read verbatim. 80 patients (40/40), 12 sessions | An RCT in myofascial pain syndrome only. Do NOT generalise to all deep-tissue clients. Neck flexion showed no significant difference |
| **S5** | Koren & Kalichman (2018). Deep tissue massage: What are we talking about? J Bodyw Mov Ther 22(2):247-251. doi:10.1016/j.jbmt.2017.05.006. PMID 29861215 | **Yes** - abstract read verbatim. Narrative review | Documents heterogeneity, no accepted definition, and rare serious adverse events from forceful application. Limitation source |
| **S4b** | Skillgate et al. (2020). Deep tissue massage therapy and supervised strengthening/stretching for subacute or persistent disabling neck pain. The Stockholm Neck (STONE) RCT. Musculoskelet Sci Pract 45:102070. doi:10.1016/j.msksp.2019.102070. PMID 31655314 | **Yes** - abstract read verbatim | **619 adults, neck pain.** Massage beat advice on pain intensity at 7 and 26 weeks only, **not at 12 months**; no consistent disability difference. Supports **short-term** language only |

**Additional sources from the expanded peer-reviewed list (2026-10-04).**
All five were **verified against their abstracts in this session.**

| ID | Source | Verified | Constrains |
|----|--------|----------|-----------|
| **S8** | Bervoets et al. (2015). Massage therapy has short-term benefits for people with common musculoskeletal disorders compared to no treatment: a systematic review. *J Physiother* 61(3):106-16. doi:10.1016/j.jphys.2015.05.018. PMID 26093806 | **Yes** - abstract read verbatim. 26 RCTs, 2565 participants | **Short-term benefits only.** Reduced pain in **shoulder pain and knee osteoarthritis** - **NOT in low back or neck pain**. Improved function in low back, knee arthritis and shoulder pain. **No clear benefit versus another active treatment** |
| **S9** | Dupuy et al. (2018). An Evidence-Based Approach for Choosing Post-exercise Recovery Techniques... *Front Physiol* 9:403. doi:10.3389/fphys.2018.00403. PMID 29755363 | **Yes** - abstract read verbatim. 99 studies | Massage for **DOMS and perceived fatigue** after exercise. Also reduced creatine kinase, IL-6 and CRP |
| **S10** | Zhang et al. (2021). Physical relaxation for occupational stress in **healthcare workers**. *J Occup Health* 63(1):e12243. doi:10.1002/1348-9585.12243. PMID 34235817 | **Yes** - confirmed via index and PMC | Relaxation/stress. **Population is healthcare workers** - do NOT generalise to all clients |
| **S11** | Williams et al. (2019). Therapeutic Massage to Enhance **Family Caregivers'** Well-Being. *Complement Ther Clin Pract*. PMID 31003683 | **Yes** - confirmed via index and publisher | Psychological well-being. **Population is family caregivers**, n=38, one or three massages/week for **two weeks**. Small and short |
| **S12** | Nemati et al. (2024). Massage Therapy as a **Self-Management** Strategy for Musculoskeletal Pain and Chronic Conditions. *J Integr Complement Med*. doi:10.1089/jicm.2023.0271. PMID 37878283 | **Yes** - confirmed via index and publisher | **Intervention is largely SELF-administered massage**, not therapist-delivered. Cannot support a therapist's ongoing self-care claim |

**Still to verify:** S6, S7 and Poppendieck were not fetched (S7 abstract was retrieved - it is a 2023 VA Evidence Map update, confirming the citation).
**Supporting sources also supplied (context, not primary claims support):**

| ID | Source | Role |
|----|--------|------|
| S6 | Miake-Lye et al. (2019). Massage for Pain: An Evidence Map. J Altern Complement Med 25(5):475-502 | Context / uncertainty |
| S7 | Shekelle et al. (2023). Evidence Map of Massage Therapy for Painful Conditions 2018-2023. VA ESP | Context / uncertainty |
| - | Poppendieck et al. (2016). Massage and performance recovery: a meta-analytical review | Optional support for exercise recovery |

**Caveat.** S1-S5 were checked against the actual papers. S6, S7 and Poppendieck were
NOT independently fetched in this session.

## 5B. Claim by claim: what the supplied evidence supports

| Claim | Bundle judgment | Action |
|-------|-----------------|--------|
| **A1** | Partially supportable. Pain and some function/quality-of-life supported; "valuable part of a long-term self-care and recovery routine" is an interpretive recommendation, not a review finding | Rewrite to the bundle A1 draft, then cite S1, S2, S3 |
| **A2, A3** | Original text recovered in the Medical Citations Deep Dive; current staging19 wording has citations | Compare each current sentence with Ryan's original wording and verify exact source fit |
| **A4** | Supportable only with narrower wording. The trial is in myofascial pain syndrome | Rewrite to the bundle A4 draft, then cite S4, S5 |
| **A5** | Too broad. The flexibility and DOMS evidence is for massage generally, not deep tissue specifically | Rewrite to the bundle A5 draft (it drops the deep-tissue attribution), then cite S3, S5 |
| **A6** | Too broad. "Effective option for many types" is not supported | Rewrite to the bundle A6 draft, then cite S4, S5, S1 |

**Current execution:** citation markers are present for A1-A6 on staging19. The
current wording has been compared with the originals and with each linked source.
Ryan directed that the improved staging19 sentences remain.

**Two constraints the bundle is explicit about, both aligning with OAR 334-040-0010(7):**

1. Do not broaden a study population. S4 is people with myofascial pain syndrome, not
   "many types of muscular discomfort."
2. Do not use a citation to rescue a Group B promise. The bundle section 8 lists the
   same exclusions this file Group B does, including the oncology-population claim.

**S3 is the most restrictive source in the set**, and it limits what massage can claim
generally: NO improvement in strength, sprint, jump, endurance or fatigue, and only
small improvements in flexibility and DOMS. Do not write "supports recovery" or
"improves performance."
## 5C. The four-part strict-match test (from the strict audit)

The stricter audit replaces the vaguer "does this source support it" question with a
**four-part test**. A source passes only if **all four** hold:

| # | Test |
|---|------|
| 1 | **Intervention** - the study evaluates the intervention being claimed, not a related one |
| 2 | **Population** - the study population does not materially differ from what the page implies |
| 3 | **Outcome** - the study measured the outcome being claimed |
| 4 | **Strength of wording** - the page does not express more certainty, breadth, duration or generality than the study supports |

**When any of the four fails: rewrite the claim rather than forcing the citation to fit.**

**Governing principle, verbatim from the strict audit:**

> Use the source to constrain the wording - not the wording to stretch the source.

**Implementation rule:** do not leave the original claim live merely because a related
PubMed source has been found. Each citation preserves the chain **claim -> exact outcome ->
population -> intervention -> citation -> limitation**. If any link fails, narrow or remove
the sentence.

## 5D. Three findings that constrain the whole page set

### 1. S3 is about SPORTS massage, not therapeutic massage

The strict audit flags this as a silent-relabelling risk: do not take S3's flexibility and
DOMS findings and present them as "therapeutic massage" results. The intervention in that
review is sports massage, in exercise settings.

### 2. S4b removes any long-term claim

The STONE trial (619 adults, neck pain) found massage beat advice on pain intensity at
**7 and 26 weeks only** - and **not at 12 months** - with no consistent disability
difference. So short-term improvement wording is supportable, and durable or long-term
effectiveness wording is not. This bears on A6's "effective option" framing and on the
removal of "lasting"/"long-term" elsewhere.

### 3. S5 is an adverse-event source, not only a limitation source

Koren & Kalichman report **rare serious adverse events** from deep tissue massage,
**probably from forceful application**. That is relevant beyond citations: it is a reason
the deep-tissue page should stay cautious in tone, and it pairs with the red-flag paragraph
the checklist already says to keep and copy to the other service pages.
## 5E. FINAL position on the weak claims

**Updated 2026-10-04 after `Thai_Solitude_Final_Long_Term_Self_Care_and_A6_Audit.md`.**
This is the last of four citation passes. The position below supersedes all earlier
versions in this file.

### The short answer

**Nothing is blocked any more.** Both remaining dead ends moved:

| Was | Now |
|-----|-----|
| "Long-term self-care" - no therapist-delivered evidence exists | **Correction: it does exist.** Perlman 2019 studied 52-week maintenance massage. The phrase still fails, but for a **narrower** reason |
| A6 - too broad, unsupported | **Still too broad**, but now has multiple condition-specific DTM trials behind a narrowed version |

### The only genuine dead end left

**One phrase, not a claim:** "valuable part of a long-term self-care and recovery routine". The
final audit's precise wording:

> "valuable part of a long-term self-care and recovery routine" is **not a directly
> established scientific conclusion**.

The final audit's own three-line summary, verbatim: recurring therapist-delivered massage
**has been studied**; 52-week maintenance **has been studied**; booster treatment **has been**
studied. What has **not** been established is that this makes massage a valuable long-term
self-care routine **for the general public**. That last step is a **practice philosophy**, not
an empirical finding.

### Final audit's verdict on each weak claim

| Weak claim | Final verdict | Wording to use |
|-----------|---------------|----------------|
| **Long-term self-care routine** | Fails as a research conclusion. **But recurring therapist-delivered massage is directly studied** (Perlman 52 weeks; Cook; Munk 12 weeks) | *"Regular massage has been studied as an ongoing treatment approach, with effects varying by condition, treatment schedule, and duration."* Or make it clearly non-research practice guidance: *"Regular massage can be part of an ongoing wellness routine, depending on your needs and preferences."* **Never prepend "research shows"** to the second form |
| **A6 - "effective option for many types"** | Fails. The problem is the **breadth** of "many types" and the **confidence** of "effective option" - not a lack of positive evidence | *"Evidence suggests that deep tissue massage may be helpful for pain and movement-related problems in some musculoskeletal conditions, although results vary by condition and individual."* |
| **Relaxation** | Supportable, or better as non-research wording | Plain session-experience language needs no citation |
| **Overall well-being** | Supportable with qualification | "...in some studied populations" |
| **Back/neck pain** | Constrained, not contradicted | Bervoets: no short-term pain reduction vs no treatment for back/neck. Gross 2024 Cochrane: little-to-no difference vs placebo |
| **Strength/endurance/performance/fatigue** | Do not claim | Davis 2020 found none |
| **A2 / A3** | ✅ Recovered. Research existence supportable | Scope/positioning question stays separate - cancer and neurological populations are an OAR 334-010-0025(4) issue |
| **A4** | Strong in specific populations | Narrow the population language |
| **A5** | Improved - direct DTM evidence in athletes exists | Keep the population and technique qualifiers visible |

### Three prohibitions the final audit states explicitly

**Do not** keep "effective option for many types of muscular discomfort" and simply add citations -
that makes the citations look stronger than they are.

**Do not** convert:

- myofascial pain evidence -> all muscular discomfort
- neck pain evidence -> all movement restrictions
- athletic recovery evidence -> general therapeutic massage
- one DTM protocol -> all "deep tissue massage"

### New sources added by the final audit (verified in this session)

| ID | Source | Verified | Constrains |
|----|--------|----------|-----------|
| **S16** | Perlman et al. (2019). Efficacy and Safety of Massage for Osteoarthritis of the Knee: a Randomized Clinical Trial. *J Gen Intern Med* 34(3):379-386. doi:10.1007/s11606-018-4763-5. PMID 30543021 | **Yes** - abstract read verbatim. **222 enrolled**, 60-min whole-body massage, 8 weekly then randomized to biweekly to week 52; 175 completed 52 weeks | **Therapist-delivered**, not self-massage. At 8 weeks: significant WOMAC improvement. **At 52 weeks: no significant between-group difference.** Maintenance maintained improvement but did **not** add benefit beyond usual care |
| **S17** | Munk et al. (2026). Therapist delivered massage for Veterans with chronic neck pain: a randomized control trial. *Pain Med* 27(2):160. | **Yes** - confirmed via publisher and index records. PMID 40875586 | Twice-weekly 60-min therapist-delivered massage for **12 weeks**, in **Veterans**. Less robust outcomes at 6 months; authors suggest longer windows or booster sessions |
| **S18** | Cook et al. (2015). Recurring massage / booster treatment for chronic neck pain | **Partly** - the final audit cites it; **not independently located in this session** | Cited for recurring/booster treatment evidence |

**Verification note: S18 could not be independently confirmed.** The PubMed search for it
returned no match on the terms used. Treat it as unverified until located.

### What this means for the copy pass

**Every weak claim now has an approved wording, or a decision to make.** There is no
claim left that needs more research. The remaining choices are editorial:

1. **Which form for the self-care idea** - research framing, practice guidance, or cut.
2. **Whether A2/A3 stay at all**, given the scope issue, independent of their citability.
3. **Whether relaxation/wel-lbeing stay as research claims** or become plain experience
   language that needs no source.
## Group B - October 4 review classifications (check current update for changes)

The October 4 review classified B1 as delete regardless. Ryan's October 5
instructions supersede that action for A2: keep the already improved, cited
staging19 wording, and show him any future wording or scope concern before changing it.
Other rows below retain their separate review questions, with the client-history
correction stated in the current execution record.

| # | Page | The passage | Why it goes |
|---|------|-------------|-------------|
| B1 | Therapeutic | "Research has explored the use of therapeutic massage among people living with chronic pain, neurological conditions, cancer-related symptoms, disabilities... anxiety..." | Historical original. Ryan's latest direction is to keep the improved, cited staging19 A2/A3 sentences. Show any scope concern to Ryan rather than deleting or reverting them. |
| B2 | Therapeutic (FAQ) | "Can Therapeutic Massage Help With Tension Headaches? Yes... reduce frequency and intensity" | A practitioner-specific outcome promise. Retitle to "Is massage okay if I get tension headaches?" and use the non-promissory answer, or delete |
| B3 | Therapeutic | "Many of Butsaba's clients seek massage to address persistent muscle tension, headaches, fatigue... stress-related symptoms" | Ryan says Butsaba has had clients, just not in Portland. Verify the actual prior-client context, location, and wording; do not assume she has no client history or imply Portland clients. |
| B4 | Any remaining | Any other "may help" sentence with no source yet | Match the wording to a source. Do not delete automatically; show Ryan any unsupported claim and proposed change for approval. |

**Related Group B items already on the checklist's removal list** (client-result
language, listed here so the two lists do not diverge):

- "clients consistently leave..."
- "clients regularly experience..."
- "often report a noticeable difference within a few sessions"
- "the result is more lasting"
- "Will I feel the results right away?... meaningful difference"

**One trimming note, not a deletion:** "navigating an ongoing health challenge" -
either trim it or pair it with "check with your clinician". It is not a research
claim but it sits in the same paragraph as B1.

## Group C - not research claims (do not confuse these with citations)

These appear in the checklist's search-and-replace list. They need rewording, not
sourcing. Sourcing them would be a category error.

**Scope and diagnosis** - rule-backed, must go (OAR 334-010-0025(2)(c) and (4)):

- "cancer", "neurological", "disabilities", "sciatica", "chronic", "adhesions",
  "corrective", "root of the restriction", "prevention"

**Outcome-adjacent phrasing** - risk-reduction, not statutory:

- "pain relief", "relieve pain", "pain management", "healing", "heal", "effective",
  "effectiveness", "measurable", "proof", "lasting", "long-term wellness", "overall
  health", "real results", "restore balance", "accelerate"

**Other** - voice, identity, or accuracy rather than research:

- "headache(s)" in a promise context (see B2)
- "nervous system", "circulation", "anxiety" - body-claim phrasing
- "studio" - no room yet
- "we / our / us" - the voice pass; site voice is now first person "I"

**Person-claim sentences that need accuracy, not a source:**

- "Founded by licensed therapist..." - "Registered"/"licensed therapist" is the wrong
  title; OAR 334-010-0025(6) reserves "LMT"
- "Butsaba's Therapy" (About H1) - the H1 pattern
- Windy City / timeline lines - covered by the date corrections, not citations

## Also flagged, and easy to confuse with citations

| Item | Why it is not a citation | Where it is handled |
|------|--------------------------|---------------------|
| "60 or 90 minutes" on the Therapeutic FAQ | A factual offer, not a research claim. The issue is an inconsistent publishing rule | Answers file, section 7.1 correction |
| Alt text reading "stress relief, pain management, and muscle recovery" | Image description, not a claim. Replace with a plain description | Checklist section 5 |
| Facebook/OG descriptions with outcome words | Metadata copy, same term rules as body text | Checklist section 4 |
| The licence footer line | Compliance, not research | Answers file, section 2.1 |

## Original planned sequence (historical; current execution status is above)

1. **Pull the Group B sentences now** (B1-B4). They are live and wrong today;
   sourcing is irrelevant to them, and the bundle's section 8 agrees.
2. **Capture the full text of A2 and A3** from the live Therapeutic page. **Still the
   biggest gap** - neither the checklist nor the bundle could identify them.
3. ~~Ryan supplies the sources~~ **DONE 2026-10-04.** Verified; see 5A.
4. **Rewrite A4, A5 and A6 before citing them.** The bundle judges the current wording
   too broad for the evidence. The bundle's drafts are in its section 7.
5. **Rewrite A1** to the bundle draft (drop the "valuable part of a long-term routine"
   interpretive claim), then cite S1, S2, S3.
6. **Write the citations onto the pages**, with the source named on the page.
7. **Verify by `view-source`**, not the editor - the claims may also appear in titles,
   meta descriptions or OG fields.

**One wording trap to watch:** S3 found **no** effect on strength, sprint, jump,
endurance or fatigue. "Supports recovery" and "improves performance" are therefore
not supported by the supplied evidence and must not appear.

## Earlier open questions (historical; check the current execution status above)

- ~~Which source is behind each of the six sentences?~~ **Answered** - bundle, 5A.
- **A2/A3 wording comparison** - original passages are recovered in the Deep Dive; compare them with the current staging19 copy.
- **Does any research claim also appear in metadata?** Not yet checked. **Open.**
- **Are S6, S7 and Poppendieck needed at all?** They are optional context; the six
  claims are supported without them. **Open.**

## Sources

- `Source - Thai Solitude Fix Checklist.md:` the passages and the counts (3 + 3).
- `Source - Thai Solitude Fix Checklist - Answers and Plan.md:` the "cite" decision,
  and the correction that three items are deleted regardless.
- `Source - Thai_Solitude_Final_Long_Term_Self_Care_and_A6_Audit.md:` the fourth and final
  citation pass. Corrects the self-care evidence gap (Perlman 52-week maintenance) and
  settles A6. **Preferred where the bundles differ.**
- `Source - Thai_Solitude_Medical_Citations_Deep_Dive.md:` the deepest audit. Recovers A2/A3
  from the REST API, adds S13-S15, and reinterprets Bervoets. **Preferred where the bundles
  differ.**
- `Source - Thai_Solitude_Medical_Citations_Strict_Audit.md:` the strict claim-match audit.
  Source of the four-part test, the STONE trial (S4b), and the finding that S3 is about
  sports massage. **Preferred over the first bundle** where the two differ.
- `Source - Thai_Solitude_Medical_Citations_Research_Bundle.md:` the supplied source set,
  the evidence judgments, and the recommended rewrites. Primary sources S1-S5 verified
  in this session against the papers themselves.
- `Source - review of the answers file, 2026-10-04:` "A citation doesn't make a
  practitioner-specific outcome promise acceptable, and she has no stated oncology
  training. Delete the first two regardless."
- `Source - OAR 334-040-0010(7) (Discipline):` false, deceptive or misleading
  advertising.
- `Source - OAR 334-010-0025(4) (Practice of Massage):` services other than massage
  require additional training or licensure.

## What this file does not do

This file does not edit the site. Sol has added direct-linked numbered citations to
the staging19 Therapeutic and Deep Tissue page content. A1-A6 have been compared
with Ryan's original passages and each link has been checked against the current
sentence it supports. Ryan chose to retain the improved sentences. The older Group B
classification of A2 and the older no-clients assumption in B3 are superseded by
the current execution record above.

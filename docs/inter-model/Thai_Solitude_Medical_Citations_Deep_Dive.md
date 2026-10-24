# Thai Solitude — Deep-Dive Medical Citation Audit
## Strict peer-reviewed evidence matching, expanded beyond PubMed

**Date:** 2026-10-04

## Purpose

This is a deeper forensic evidence pass over the weak spots identified in the earlier citation bundles.

The standard remains strict:

- peer-reviewed scientific paper;
- preferably systematic review/meta-analysis or randomized controlled trial;
- exact intervention match;
- exact outcome match;
- population match or explicitly stated population limitation;
- wording no stronger than the evidence;
- no citation used to rescue practitioner-specific promises or Oregon scope issues.

Publisher pages, PMC, PubMed, ScienceDirect, Wiley, Frontiers, Nature, BMJ, SAGE, etc. may be used to retrieve the peer-reviewed paper. A general medical webpage is not accepted as the scientific citation.

---

# 1. Important discovery: A2 and A3 are now recovered

The previous bundles treated A2 and A3 as unrecoverable.

That is no longer correct.

The public WordPress REST API for the current Therapeutic Massage page returns page ID 1107 and exposes the exact text.

Current endpoint:

https://thaisolitude.com/wp-json/wp/v2/pages/1107

Current live page:

https://thaisolitude.com/therapeutic-massage/

The public revision endpoint is not accessible without authentication, so this establishes the **current text**, not historical versions.

## A2 — exact current text

> “Research has explored the use of therapeutic massage among people living with chronic pain, neurological conditions, cancer-related symptoms, disabilities, and the demands of caregiving.”

## A3 — exact current text

> “It has also been studied in individuals seeking support for stress, anxiety, emotional strain, and reduced quality of life during particularly difficult periods.”

### Important distinction

These are **research-existence statements**: they say the research literature has studied massage in these populations.

That is scientifically supportable.

However, the Thai Solitude worklist separately classifies the surrounding clinical-population language as a **Group B scope/positioning problem**. A citation can establish that such research exists; it does not establish that this individual practice serves those populations.

Therefore:

- **Scientific citation status:** supportable.
- **Website/scope status:** still requires the separate term/scope decision.

Do not confuse those two questions.

---

# 2. A2 — Chronic pain, neurological conditions, cancer-related symptoms, disabilities, caregiving

## A2 wording

> “Research has explored the use of therapeutic massage among people living with chronic pain, neurological conditions, cancer-related symptoms, disabilities, and the demands of caregiving.”

This sentence is unusual because it is not claiming a treatment effect. It is claiming that the literature has **studied massage in these populations**.

That is easier to support accurately than a blanket efficacy claim.

## Chronic pain

### Crawford et al. (2016)

**The Impact of Massage Therapy on Function in Pain Populations—A Systematic Review and Meta-Analysis of Randomized Controlled Trials: Part I, Patients Experiencing Pain in the General Population**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/27165971/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC4925170/
- DOI: https://doi.org/10.1093/pm/pnw099
- PMID: 27165971

The review included 67 RCTs covering diverse pain populations. The included studies explicitly included musculoskeletal pain, headache, visceral pain, chronic pain including fibromyalgia, spinal cord pain, and venous insufficiency. Massage techniques were delivered by practitioners/therapists.

### Match

A2's **chronic pain** portion is directly supported.

---

## Neurological conditions

### Cabanas-Valdés et al. (2021)

**The Effectiveness of Massage Therapy for Improving Sequelae in Post-Stroke Survivors: A Systematic Review and Meta-Analysis**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/33919371/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC8122530/
- DOI: https://doi.org/10.3390/ijerph18094424
- Journal: International Journal of Environmental Research and Public Health

18 RCTs involving 1,989 stroke survivors were reviewed.

The review evaluated motor function, spasticity, activities of daily living, anxiety, pain, balance, gait, disability, and quality of life.

The literature supports the statement that therapeutic massage has been studied in a neurological population.

### Additional neurological example

### Kang et al. (2022)

**Effectiveness of therapeutic massage for improving motor symptoms in Parkinson's disease: A systematic review and meta-analysis**

- Frontiers in Neurology: https://www.frontiersin.org/journals/neurology/articles/10.3389/fneur.2022.915232/full
- DOI: https://doi.org/10.3389/fneur.2022.915232

Seven RCTs and one randomized pilot study involving 363 Parkinson's disease participants were included.

The meta-analysis reported improvement in motor symptoms/UPDRS-III compared with control, while activities of daily living were not significantly different.

This is additional direct evidence that the therapeutic-massage literature includes neurological populations.

---

## Cancer-related symptoms

### Win Myint et al. (2025)

**Effectiveness of Massage Therapy for Cancer Pain, Quality of Life and Anxiety Levels: A Systematic Review and Meta-Analysis**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/39558520/
- Wiley: https://onlinelibrary.wiley.com/doi/10.1111/jocn.17547
- DOI: https://doi.org/10.1111/jocn.17547
- PMID: 39558520

36 RCTs involving 3,671 participants with cancer were included.

The review found post-intervention improvements in:

- pain;
- quality of life;
- anxiety.

### Critical limitation

The certainty of evidence for all outcomes was **very low**, and most studies had unclear or high risk of bias.

### Match

A2's assertion that massage has been studied in people with cancer-related symptoms is fully supported.

Do **not** convert this source into a strong marketing claim such as “massage treats cancer symptoms effectively.”

---

## Disabilities / stroke-related disability

Cabanas-Valdés et al. is sufficient for the **existence of research involving disability after stroke**.

The paper explicitly examines stroke disability and functional outcomes and is located in the journal section “Disabilities.”

This supports the research-existence portion of A2.

It does not justify the broader claim that massage is effective for people with “disabilities” in general.

---

## Caregiving

### Williams et al. (2019)

**Therapeutic massage to enhance family caregivers' well-being in a rehabilitation hospital**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/31003683/
- DOI: https://doi.org/10.1016/j.ctcp.2019.03.020
- PMID: 31003683

38 family caregivers were randomized to massage once per week or three times per week for two weeks.

The study reported improvements in symptom measures and high acceptability.

### Match

This directly supports A2's statement that massage has been studied in the **caregiving population**.

### Limitation

Small sample and only two weeks. Do not use it to support a general “long-term caregiver wellness” claim.

---

# 3. A2 overall verdict

### Scientific wording

A2 can be cited accurately as written **for the proposition that massage has been studied in these populations**.

### Best citation package

Use:

1. Crawford 2016 — chronic pain
2. Cabanas-Valdés 2021 — neurological condition / disability
3. Win Myint 2025 — cancer
4. Williams 2019 — caregiving

### But:

The worklist's separate scope decision still stands.

Do not turn:

> “research has explored…”

into:

> “Butsaba treats people with…”

The citation establishes the existence of research, not the practitioner's scope.

---

# 4. A3 — Stress, anxiety, emotional strain, reduced quality of life

## A3 wording

> “It has also been studied in individuals seeking support for stress, anxiety, emotional strain, and reduced quality of life during particularly difficult periods.”

This is also scientifically supportable as a **literature-existence statement**, but the evidence is heterogeneous.

## Stronger evidence for stress

### Kurebayashi et al. (2020)

**Massage and Reiki to reduce stress and improve quality of life: a randomized clinical trial**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/33053005/
- DOI: https://doi.org/10.1590/S1980-220X2018059103612
- PMID: 33053005

Initial sample: 122 adults.

Final analyzed sample: 101.

Groups:

- massage followed by rest;
- massage followed by Reiki;
- no-intervention control.

After the intervention:

- massage + rest and massage + Reiki reduced stress;
- both improved quality of life versus control.

### Important limitation

The interventions were **massage + rest** or **massage + Reiki**, not massage alone.

Therefore this is strong evidence that massage has been studied in relation to stress/QOL, but not a clean massage-alone efficacy study.

---

## Kurebayashi et al. (2016)

**Massage and Reiki used to reduce stress and anxiety: Randomized Clinical Trial**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/27901219/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC5172615/
- DOI: https://doi.org/10.1590/1518-8345.1614.2834
- PMID: 27901219

Initial sample: 122.

Eight sessions over one month.

Massage + rest and massage + Reiki were compared with no intervention.

Stress and anxiety were reduced in the intervention groups.

### Limitation

Again, the interventions include rest or Reiki, so this should support A3 as a statement about the research literature rather than being used as sole evidence that massage alone reduces stress.

---

# 5. Stronger general-population relaxation evidence

This is where the previous citation bundle was too narrow.

Zhang 2021 is not the only relevant evidence.

## Meier et al. (2020)

**Standardized massage interventions as protocols for the induction of psychophysiological relaxation in the laboratory: a block randomized, controlled trial**

- Scientific Reports: https://www.nature.com/articles/s41598-020-71173-w
- DOI: https://doi.org/10.1038/s41598-020-71173-w

### Population

60 healthy women.

Groups:

- vagus nerve massage;
- soft shoulder massage;
- resting control.

### Outcomes

The study measured:

- subjective relaxation;
- subjective stress;
- high-frequency heart-rate variability.

Both massage protocols were associated with increases in HF-HRV compared with the resting control.

### Important nuance

Subjective relaxation increased over time across all groups; the massage groups did not show a statistically significant advantage over the rest group for subjective relaxation itself.

The strongest massage-specific between-group signal was physiological.

### Verdict

This is useful for **“massage can induce psychophysiological relaxation”**, but it should not be inflated into:

> “Massage relieves stress for everyone.”

---

# 6. Stronger evidence for well-being / perceived stress in a broader adult population

## Sharpe et al. (2007)

**A randomised study of the effects of massage therapy compared to guided relaxation on well-being and stress perception among older adults**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/17709060/
- DOI: https://doi.org/10.1016/j.ctim.2007.01.004
- PMID: 17709060

### Population

54 community-dwelling adults aged 60+.

### Intervention

50-minute massage twice weekly for 4 weeks.

Massage included:

- Swedish;
- neuromuscular;
- myofascial techniques.

Comparator:

- guided relaxation.

### Results

Massage participants had significant improvements versus guided relaxation in:

- anxiety;
- depression;
- vitality;
- general health;
- positive well-being;
- perceived stress.

### Why this matters

This is much stronger for a broad “well-being / stress” discussion than the n=38 caregiver study because:

- larger sample;
- community-dwelling adults;
- four-week repeated intervention;
- active relaxation comparator;
- multiple well-being measures.

### Limitation

Still a specific population: older adults.

Therefore the safe statement is:

> “A randomized study in community-dwelling older adults found improvements in several well-being measures and perceived stress following four weeks of massage.”

Not:

> “Massage improves well-being for everyone.”

---

# 7. A1 — “Promote relaxation” can now be supported, but the website does not need to make it a medical claim

The previous critique was correct that Zhang 2021 alone was too narrow.

The broader literature now includes:

- Meier 2020 — healthy women / physiological relaxation;
- Sharpe 2007 — community-dwelling older adults / perceived stress and well-being;
- Kurebayashi 2020 — 101 analyzed adult participants / stress and QOL, though massage was paired with rest or Reiki;
- Crawford 2016 — anxiety and HRQoL in pain populations.

### Strict scientific wording

A defensible research sentence would be:

> “Research has found associations between massage and relaxation, stress, or well-being outcomes in several studied adult populations, although results depend on the population and intervention.”

But for a consumer-facing service page, a stronger strategic choice is:

> “A massage session can be a quiet space to slow down, settle in, and focus on your comfort.”

That is a description of the experience, not a medical efficacy claim, and therefore does not require a research citation.

### Recommendation

**Drop “promote relaxation” from the research sentence and describe the session experience separately.**

This eliminates unnecessary evidentiary burden.

---

# 8. “Improve overall well-being” — the evidence is better than the previous bundle suggested

The old bundle leaned too heavily on Williams et al. (n=38, 2 weeks).

That was too narrow.

Better evidence includes:

## Crawford et al. 2016

The systematic review/meta-analysis found a benefit for **health-related quality of life** compared with active comparators.

- PubMed: https://pubmed.ncbi.nlm.nih.gov/27165971/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC4925170/

This is stronger evidence than the caregiver pilot for a general “quality of life” concept.

## Sharpe et al. 2007

54 community-dwelling older adults, four weeks, repeated massage.

The study found improvements in:

- positive well-being;
- general health;
- vitality;
- anxiety;
- depression;
- perceived stress.

PubMed:
https://pubmed.ncbi.nlm.nih.gov/17709060/

## Kurebayashi et al. 2020

101 analyzed adults, massage + rest or massage + Reiki, compared with no intervention.

Massage-containing interventions improved quality of life and stress.

PubMed:
https://pubmed.ncbi.nlm.nih.gov/33053005/

### Strict verdict

The phrase:

> “Regular massage has also been associated with improvements in overall well-being”

is **still broader than the literature**.

The science supports:

> “Massage has been associated with improvements in measures of quality of life and well-being in some studied populations.”

That is a much better evidence match.

---

# 9. “Long-term self-care routine” — deeper search changes one part, but not the conclusion

The previous bundle correctly rejected Nemati 2024 as direct evidence for therapist-delivered long-term self-care.

That remains correct.

However, the deeper search found **therapist-delivered repeated massage trials with follow-up**, which means the evidence is not “there are no recurring-therapist studies.”

There are.

## Sherman et al. (2014)

**Five-Week Outcomes From a Dosing Trial of Therapeutic Massage for Chronic Neck Pain**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/24615306/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC3948757/
- DOI: https://doi.org/10.1370/afm.1602

228 participants with chronic nonspecific neck pain.

Massage was delivered over four weeks at varying doses.

At five weeks:

- 60-minute massage 2× or 3× weekly increased the likelihood of clinically meaningful improvement in neck dysfunction and pain compared with wait-list control.
- 30-minute massage did not show the same benefit.

### What this establishes

Repeated therapist-delivered massage has been studied over several weeks.

### What it does NOT establish

It does not establish that:

> regular massage is a valuable long-term self-care routine for healthy general clients.

---

## Cook et al. (2015)

**Randomized clinical trial assessing whether additional massage treatments for chronic neck pain improve 12- and 26-week outcomes**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/26096474/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC4596391/
- DOI: https://doi.org/10.1016/j.spinee.2015.06.049

179 participants with chronic nonspecific neck pain.

After 4 weeks of massage, some participants received an additional 6 weekly massages.

### Results

At 12 weeks:

- booster massage improved neck dysfunction;
- the pain result was weaker / not statistically significant at the conventional threshold.

At 26 weeks:

- booster effects were no longer statistically significant.

### This is especially important

It gives direct evidence for the word **“longer treatment period”**, but it also demonstrates why the website should not say **“long-term”** as if lasting effects are established.

The benefits diminished after treatment stopped.

### Verdict on A1’s “long-term self-care routine”

**Still unsupported as written.**

A scientifically defensible alternative:

> “Regular massage has been studied as a repeated treatment schedule, with outcomes depending on the condition, dose, and duration of treatment.”

But for a consumer service page, this is arguably too clinical and adds little value.

### Recommendation

**Cut “long-term self-care and recovery routine” from the research sentence.**

---

# 10. Bervoets 2015 — important constraint, but not a universal contradiction

The earlier critique was directionally right, but this needs precision.

## Bervoets et al. 2015

- PubMed: https://pubmed.ncbi.nlm.nih.gov/26093806/
- DOI: https://doi.org/10.1016/j.jphys.2015.05.018

The review found short-term pain reduction compared with no treatment for:

- shoulder pain;
- knee osteoarthritis.

It did **not** find a significant pain reduction versus no treatment for:

- low-back pain;
- neck pain.

However, functional improvement was reported in some of those conditions.

### Correct interpretation

Bervoets does NOT prove:

> “massage doesn't work for back or neck pain.”

It means:

> this particular systematic review found insufficient evidence of short-term pain reduction versus no treatment for those conditions.

That distinction matters.

---

# 11. Stronger neck-pain constraint — 2024 Cochrane Review

## Gross et al. (2024)

**Massage for neck pain**

- Cochrane: https://www.cochrane.org/evidence/CD004871_massage-neck-pain
- PubMed: https://pubmed.ncbi.nlm.nih.gov/38415786/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC10900303/

### Evidence

33 studies involving 1,994 participants.

Compared with placebo, massage probably makes little-to-no difference in pain, function, or quality of life at up to 12 weeks, with low-certainty evidence.

Compared with no treatment, massage may produce larger short-term pain/function benefits, but the evidence has important limitations including risk of bias.

### Website implication

This is a strong reason not to make an uncaveated statement that massage “relieves neck pain.”

---

# 12. Davis 2020 — the negative outcomes are part of the evidence

## Davis et al. 2020

- PubMed: https://pubmed.ncbi.nlm.nih.gov/32426160/
- PMC: https://pmc.ncbi.nlm.nih.gov/articles/PMC7228568/
- BMJ Open Sport & Exercise Medicine:
  https://bmjopensem.bmj.com/content/6/1/e000614

29 randomized studies; 1,012 participants.

No evidence of improvement in:

- strength;
- jumping;
- sprinting;
- endurance;
- fatigue.

Small improvements were detected in:

- flexibility;
- DOMS.

### Website implication

Do not turn “recovery” into:

> “improves strength, performance, endurance, or fatigue.”

The evidence does not support that.

---

# 13. Deep Tissue A5 — the new finding that helps most

The earlier bundle was too conservative about “muscle recovery.”

There is now direct deep-tissue research in an athletic population.

## Arsovski et al. (2025)

**International Journal of Therapeutic Massage & Bodywork**

PubMed:
https://pubmed.ncbi.nlm.nih.gov/40510040/

PMC:
https://pmc.ncbi.nlm.nih.gov/articles/PMC12140169/

Journal:
https://ijtmb.org/index.php/ijtmb/article/view/1139

DOI:
https://doi.org/10.3822/ijtmb.v18i2.1139

### Study

150 athletes, ages 18–45.

Deep tissue massage was performed twice weekly over eight weeks.

The study reported benefits involving recovery/performance and flexibility, with longer sessions producing greater flexibility changes.

### Strict limitation

This is:

- athletic population;
- defined DTM protocol;
- 8-week intervention;
- one randomized study.

It does not establish the same effects for ordinary Portland clients.

### Therefore

The original A5 can be made more defensible by explicitly separating:

- general massage evidence for flexibility/DOMS;
- direct DTM evidence in athletes for recovery/flexibility;
- specific musculoskeletal evidence for ROM/function.

---

# 14. A5 — better evidence-matched wording

Original:

> “Studies have also linked deep tissue massage with improvements in flexibility, muscle recovery, and functional movement, though outcomes vary from person to person.”

### Strict verdict

Still too broad.

### Better version

> “Research on massage has found small improvements in flexibility and exercise-related muscle soreness, while research on deep tissue massage has also reported improvements in flexibility and recovery-related outcomes in some athletic populations.”

This is substantially more accurate because it keeps the intervention/population boundaries visible.

If this is too long for the page:

> “Research has found small improvements in flexibility and exercise-related muscle soreness following massage.”

This is the cleanest version.

---

# 15. Deep Tissue A4 — best version

Original:

> “Research suggests deep tissue massage may help reduce pain intensity, improve mobility, and support quality of life in people experiencing ongoing musculoskeletal discomfort.”

### Strong direct evidence

Bingölbali et al. 2024:

- myofascial pain syndrome;
- 80 participants;
- randomized controlled trial;
- pain;
- disability;
- quality of life;
- several neck ROM measures.

PubMed:
https://pubmed.ncbi.nlm.nih.gov/36645811/

### Additional deep-tissue evidence

Skillgate et al. / STONE:

- 619 adults with subacute/persistent disabling neck pain;
- short-term pain/recovery effects;
- no convincing long-term pain superiority.

PubMed:
https://pubmed.ncbi.nlm.nih.gov/31655314/

### Best website wording

> “Research on deep tissue massage suggests potential benefits for pain and movement-related outcomes in specific musculoskeletal conditions.”

This is safer than claiming “people experiencing ongoing musculoskeletal discomfort” generally.

---

# 16. Deep Tissue A6 — best version

Original:

> “Current research suggests deep tissue massage can be an effective option for many types of muscular discomfort and movement restrictions, though results depend on the underlying cause.”

### Evidence

The deep-tissue literature is heterogeneous.

Koren & Kalichman:

- PubMed: https://pubmed.ncbi.nlm.nih.gov/29861215/
- DOI: https://doi.org/10.1016/j.jbmt.2017.05.006

There is no universally accepted definition of deep tissue massage and protocols vary.

Direct RCTs show benefits in selected populations, including:

- myofascial pain syndrome;
- chronic neck pain;
- chronic low-back pain.

### Best website wording

> “Evidence from randomized trials suggests that deep tissue massage may improve pain, range of motion, or function in some specific musculoskeletal conditions, but results vary by condition and outcome.”

This is the strongest broad claim I would approve.

---

# 17. Recommended final research language

## Therapeutic page

### Research paragraph

> “Research suggests that massage therapy may help reduce pain in some conditions and may improve certain function-related outcomes. Research has also found improvements in measures of quality of life, well-being, and stress in some studied adult populations.”

### Then separately, non-research experience language

> “A massage session can also be a quiet space to slow down, settle in, and focus on your comfort.”

### Remove from the research sentence

- “support recovery from physical activity” unless specifically tied to sports/exercise evidence;
- “promote relaxation” if the goal is to minimize medical claims;
- “valuable part of a long-term self-care and recovery routine.”

---

# 18. Recommended final research language — Deep Tissue page

> “Research on deep tissue massage suggests potential benefits for pain and movement-related outcomes in specific musculoskeletal conditions. Studies in exercise settings have also reported improvements in flexibility and recovery-related outcomes, although findings vary by population, technique, and outcome.”

This is a deliberately calibrated statement.

---

# 19. A2/A3 citation package

## A2

**Chronic pain**
- Crawford 2016
- https://pubmed.ncbi.nlm.nih.gov/27165971/

**Neurological conditions / disability**
- Cabanas-Valdés 2021
- https://pubmed.ncbi.nlm.nih.gov/33919371/
- https://pmc.ncbi.nlm.nih.gov/articles/PMC8122530/

**Cancer-related symptoms**
- Win Myint 2025
- https://pubmed.ncbi.nlm.nih.gov/39558520/
- https://onlinelibrary.wiley.com/doi/10.1111/jocn.17547

**Caregiving**
- Williams 2019
- https://pubmed.ncbi.nlm.nih.gov/31003683/

### A2 verdict

**Scientifically supported as a research-existence statement.**

**Not a scope endorsement for Thai Solitude.**

---

## A3

**Stress / quality of life**
- Kurebayashi 2020
- https://pubmed.ncbi.nlm.nih.gov/33053005/

**Stress / anxiety**
- Kurebayashi 2016
- https://pubmed.ncbi.nlm.nih.gov/27901219/

**Well-being / stress in community-dwelling adults**
- Sharpe 2007
- https://pubmed.ncbi.nlm.nih.gov/17709060/

**Physiological/psychological relaxation**
- Meier 2020
- https://www.nature.com/articles/s41598-020-71173-w

### A3 verdict

**Scientifically supportable as a research-existence statement.**

Do not translate it into a promise of benefit for an individual client.

---

# 20. Final disposition

| Item | Strict result | Action |
|---|---|---|
| **A2** | ✅ Research existence is supportable | Citations can be attached, but scope issue remains separate |
| **A3** | ✅ Research existence is supportable | Citations can be attached, but scope issue remains separate |
| **“Promote relaxation”** | ✅ Better evidence exists than previously supplied | Prefer plain session-experience wording on consumer page |
| **“Improve overall well-being”** | ⚠️ Supportable only with qualification | Use “measures of quality of life/well-being in some studied populations” |
| **“Long-term self-care routine”** | ❌ Still not supported as written | Cut or make clearly non-research practice guidance |
| **Bervoets / back-neck limitation** | ✅ Important constraint | Do not generalize pain benefit across all musculoskeletal conditions |
| **Davis negative outcomes** | ✅ Important constraint | Do not claim strength/endurance/performance/fatigue benefit |
| **A4 DTM pain/mobility/QOL** | ⚠️ Strong in specific populations | Narrow population/condition language |
| **A5 DTM flexibility/recovery** | ⚠️ Now some direct DTM evidence exists | Keep population/technique qualifiers |
| **A6 DTM broad effectiveness** | ❌ Too broad | Rewrite around specific conditions/outcomes |

---

# 21. Core evidence hierarchy for implementation

### Tier 1 — Best broad calibration

1. Mak et al. 2024 — JAMA Network Open
   - https://pubmed.ncbi.nlm.nih.gov/39008297/

2. Crawford et al. 2016 — Pain Medicine
   - https://pubmed.ncbi.nlm.nih.gov/27165971/

3. Gross et al. 2024 — Cochrane neck-pain review
   - https://pubmed.ncbi.nlm.nih.gov/38415786/

### Tier 2 — Best targeted RCT / condition evidence

4. Sherman et al. 2014 — therapeutic massage dosing, chronic neck pain
   - https://pubmed.ncbi.nlm.nih.gov/24615306/

5. Cook et al. 2015 — repeated/booster massage, chronic neck pain
   - https://pubmed.ncbi.nlm.nih.gov/26096474/

6. Bingölbali et al. 2024 — deep tissue, myofascial pain syndrome
   - https://pubmed.ncbi.nlm.nih.gov/36645811/

7. Skillgate et al. 2020 — deep tissue, persistent neck pain
   - https://pubmed.ncbi.nlm.nih.gov/31655314/

### Tier 3 — Stress / well-being / relaxation

8. Sharpe et al. 2007 — community-dwelling older adults
   - https://pubmed.ncbi.nlm.nih.gov/17709060/

9. Kurebayashi et al. 2020 — massage + rest/Reiki, stress and QOL
   - https://pubmed.ncbi.nlm.nih.gov/33053005/

10. Meier et al. 2020 — healthy women, psychophysiological relaxation
    - https://www.nature.com/articles/s41598-020-71173-w

### Tier 4 — Population-specific literature supporting A2

11. Cabanas-Valdés et al. 2021 — stroke survivors
    - https://pubmed.ncbi.nlm.nih.gov/33919371/

12. Win Myint et al. 2025 — cancer
    - https://pubmed.ncbi.nlm.nih.gov/39558520/

13. Williams et al. 2019 — family caregivers
    - https://pubmed.ncbi.nlm.nih.gov/31003683/

---

# 22. Final implementation rule

For every research sentence placed on the site, keep an internal record:

```text
EXACT WEBSITE WORDING
    ↓
INTERVENTION
    ↓
POPULATION
    ↓
OUTCOME
    ↓
STUDY DESIGN
    ↓
RESULT
    ↓
LIMITATION
    ↓
CITATION
```

If the population is narrower than the website claim, narrow the website claim.

If the intervention is different, narrow the website claim.

If the outcome is different, narrow the website claim.

If the study is short-term, do not call the result long-term.

If the evidence is low certainty, do not use strong efficacy language.

If a statement only says that research exists, cite the research literature—but do not convert that statement into a claim that the individual practitioner treats that population.

## Bottom line

The deeper search does not rescue the original copy wholesale.

It does something better:

**It gives us enough high-quality evidence to write accurate, useful claims without pretending the science is broader or stronger than it is.**
# Thai Solitude — Medical Citation Research Bundle (Strict Claim-Match Audit)

**Date:** 2026-10-04  
**Purpose:** Agent-ready evidence bundle for the medical/health-related claims on the Thai Solitude website.

## Operating rule

Do **not** treat a paper as a valid citation merely because it discusses massage, pain, recovery, or deep tissue massage.

A source passes only when the evidence matches:

1. **Intervention** — the study actually evaluates the intervention being claimed.
2. **Population** — the study population does not materially differ from the population implied on the page.
3. **Outcome** — the study actually measured the outcome being claimed.
4. **Strength of wording** — the website wording does not express more certainty, breadth, duration, or generality than the study supports.

When any of those fail, **rewrite the website claim rather than forcing the citation to fit.**

---

# 1. Current claims to audit

These are the six Group A research items from the Thai Solitude citation worklist.

## A1 — Therapeutic Massage

Current website text:

> “Research suggests that massage therapy may help reduce pain, improve mobility, support recovery from physical activity, and promote relaxation. Regular massage has also been associated with improvements in overall well-being, making it a valuable part of a long-term self-care and recovery routine.”

### Strict verdict

**FAIL as a single fully supported claim.**

The sentence contains several distinct assertions that require different evidence:

- pain reduction — supported by systematic-review evidence
- improved mobility / range of motion — supported in some populations
- recovery from physical activity — evidence exists, but the strongest source here is specifically sports/exercise massage
- relaxation — evidence exists, but should be sourced separately if retained
- overall well-being — some health-related quality-of-life evidence exists
- “valuable part of a long-term self-care and recovery routine” — interpretive recommendation, not a direct research finding

### Safer evidence-matched version

> “Research suggests that massage therapy may reduce pain in some conditions and may improve certain function-related outcomes, including range of motion. Research in exercise settings has also found small improvements in flexibility and delayed-onset muscle soreness.”

This version intentionally avoids claiming that one body of evidence establishes long-term self-care value.

### Primary sources

**Crawford et al. (2016) — systematic review and meta-analysis**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/27165971/
- PMCID/full text: https://pmc.ncbi.nlm.nih.gov/articles/PMC4925170/
- DOI: https://doi.org/10.1093/pm/pnw099
- PMID: 27165971
- PMCID: PMC4925170

**What it supports:** massage therapy research in pain populations; reduced pain versus several comparators; some function-related outcomes including range of motion; anxiety; health-related quality of life.

**Important limitation:** this does not support every element of the website sentence, and it does not establish the “long-term self-care and recovery routine” language.

**Davis et al. (2020) — systematic review and meta-analysis of sports massage**

- PubMed: https://pubmed.ncbi.nlm.nih.gov/32426160/
- PMCID/full text: https://pmc.ncbi.nlm.nih.gov/articles/PMC7228568/
- DOI: https://doi.org/10.1136/bmjsem-2019-000614
- PMID: 32426160
- PMCID: PMC7228568

**What it supports:** in 29 randomized studies involving 1,012 participants, sports massage was associated with small statistically significant improvements in flexibility and delayed-onset muscle soreness (DOMS).

**Important limitation:** this is **sports massage**, not a general study of therapeutic massage. Do not silently relabel the intervention.

---

# 2. Evidence-quality anchor for general massage claims

## Mak et al. (2024) — JAMA Network Open

**Title:** Use of Massage Therapy for Pain, 2018-2023: A Systematic Review

- PubMed: https://pubmed.ncbi.nlm.nih.gov/39008297/
- PMCID/full text: https://pmc.ncbi.nlm.nih.gov/articles/PMC11250267/
- DOI: https://doi.org/10.1001/jamanetworkopen.2024.22259
- PMID: 39008297
- PMCID: PMC11250267

### Why this source matters

This is the strongest recent high-level source for **how confidently the website should talk about massage evidence**.

It mapped 129 systematic reviews of massage therapy for painful adult health conditions. Of the reviews that formally rated certainty/quality, none had conclusions rated high-certainty. Seven conclusions were rated moderate-certainty, and those moderate-certainty conclusions were beneficial associations with pain.

### Website implication

Avoid:

- “proven”
- “effective” without qualification
- “works for many conditions”
- language implying uniformly strong evidence
- broad claims that extrapolate from one condition to all clients

Prefer:

- “research suggests”
- “evidence indicates”
- “may”
- “in some conditions”
- “findings vary”
- specific populations/outcomes where possible

---

# 3. A2 and A3 — Therapeutic Massage

The citation worklist identifies two additional Therapeutic research sentences but did not capture their text.

The currently inspected live Therapeutic Massage page does **not** expose two additional Group A research sentences that can safely be reconstructed from the worklist.

**Do not invent A2/A3.**

### Required agent action

Before sourcing A2/A3:

1. retrieve the current live page;
2. inspect rendered text;
3. inspect page source / WordPress content if necessary;
4. capture the exact sentence;
5. search PubMed using the exact medical claim;
6. apply the four-part strict-match test above.

If the exact text cannot be recovered, do not create a citation placeholder.

---

# 4. A4 — Deep Tissue: pain, mobility, quality of life

## Current website text

> “Research suggests deep tissue massage may help reduce pain intensity, improve mobility, and support quality of life in people experiencing ongoing musculoskeletal discomfort.”

### Strict verdict

**FAIL as written.**

The evidence is promising, but the current sentence generalizes beyond the studied populations.

### Strongest direct deep-tissue RCT

**Bingölbali et al. (2024)**

Title: *The effectiveness of deep tissue massage on pain, trigger point, disability, range of motion and quality of life in individuals with myofascial pain syndrome*

- PubMed: https://pubmed.ncbi.nlm.nih.gov/36645811/
- DOI: https://doi.org/10.1080/08990220.2023.2165054
- PMID: 36645811

### Study fit

- Intervention: deep tissue massage
- Population: adults with myofascial pain syndrome
- Sample: 80 participants
- Design: randomized controlled trial
- Comparator: conventional rehabilitation
- Outcomes: pain, neck disability, neck range of motion, trigger point count, quality of life

### What it supports

The deep-tissue group had statistically greater improvement than the control group for pain, disability, and quality of life, with significant differences for several neck range-of-motion measures.

### What it does NOT support

It does not establish that deep tissue massage improves those outcomes in:

- all people with musculoskeletal discomfort
- all causes of pain
- all body regions
- people without myofascial pain syndrome

### Evidence-matched rewrite

> “In people with myofascial pain syndrome, a randomized controlled trial found that deep tissue massage added to conventional rehabilitation improved pain, disability, quality of life, and several measures of neck range of motion.”

That is much closer to the actual evidence.

---

# 5. A4 additional source — STONE randomized trial

## Skillgate et al. (2020)

Title: *Effectiveness of deep tissue massage therapy, and supervised strengthening and stretching exercises for subacute or persistent disabling neck pain. The Stockholm Neck (STONE) randomized controlled trial*

- PubMed: https://pubmed.ncbi.nlm.nih.gov/31655314/
- DOI: https://doi.org/10.1016/j.msksp.2019.102070
- PMID: 31655314
- Full text is listed by PubMed as available.

### Study fit

- 619 adults
- subacute or persistent disabling neck pain
- randomized controlled trial
- deep tissue massage compared with advice to stay active, plus exercise and combined-treatment arms
- outcomes measured through 52 weeks

### What it supports

Massage produced higher rates of improvement in pain intensity than advice at some short-term follow-ups, and perceived recovery was better in the massage/exercise groups.

### Critical limitation

At 12 months, none of the interventions was more effective than advice for long-term pain intensity, and there were no consistent differences in pain-related disability.

### Website implication

Use this to support **short-term** improvement language, not claims of durable long-term effectiveness.

---

# 6. A5 — Deep Tissue + flexibility + recovery + functional movement

## Current website text

> “Studies have also linked deep tissue massage with improvements in flexibility, muscle recovery, and functional movement, though outcomes vary from person to person.”

### Strict verdict

**FAIL. Do not simply attach a citation.**

The strongest systematic-review evidence identified here is about **massage in sports/exercise settings**, not specifically deep tissue massage.

### Davis et al. (2020)

- PubMed: https://pubmed.ncbi.nlm.nih.gov/32426160/
- PMCID/full text: https://pmc.ncbi.nlm.nih.gov/articles/PMC7228568/
- DOI: https://doi.org/10.1136/bmjsem-2019-000614
- PMID: 32426160
- PMCID: PMC7228568

### What it actually found

Across 29 randomized studies involving 1,012 participants:

- no evidence that massage improved strength
- no evidence that massage improved jumping
- no evidence that massage improved sprinting
- no evidence that massage improved endurance
- no evidence that massage improved fatigue
- **small statistically significant improvements in flexibility**
- **small statistically significant improvements in DOMS**

### What it does NOT establish

It does not establish:

> deep tissue massage → flexibility + muscle recovery + functional movement

as a general proposition.

### Evidence-matched replacement

> “Systematic review evidence in exercise settings has found small improvements in flexibility and delayed-onset muscle soreness following massage, although results vary.”

Note that this sentence deliberately says **massage**, not **deep tissue massage**.

---

# 7. A6 — Deep Tissue “effective option”

## Current website text

> “Current research suggests deep tissue massage can be an effective option for many types of muscular discomfort and movement restrictions, though results depend on the underlying cause.”

### Strict verdict

**FAIL as written. Rewrite rather than cite.**

## Koren & Kalichman (2018)

Title: *Deep tissue massage: What are we talking about?*

- PubMed: https://pubmed.ncbi.nlm.nih.gov/29861215/
- DOI: https://doi.org/10.1016/j.jbmt.2017.05.006
- PMID: 29861215

### Why this paper is important

This review found:

- there is no commonly accepted definition of “deep tissue massage” in the literature;
- published studies use heterogeneous techniques and protocols;
- this heterogeneity makes clear conclusions difficult;
- favorable outcomes may occur in pain populations and people with decreased range of motion;
- rare serious adverse events have been reported with forceful massage.

### What this means for the site

The review itself does **not** justify:

> “deep tissue massage can be an effective option for many types…”

because “many types” is broader and more confident than the evidence permits.

### Evidence-matched rewrite

> “Evidence suggests that deep tissue massage may improve pain and some movement-related outcomes in certain musculoskeletal conditions, but findings vary and the evidence is not uniform.”

This wording better matches both the positive studies and the limitations identified by the review.

---

# 8. Best source hierarchy for the agent

Use sources in this order when deciding what language can remain:

## Tier 1 — Highest-value sources

1. **Mak et al., 2024 — JAMA Network Open**
   - broad evidence-quality anchor
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/39008297/

2. **Crawford et al., 2016 — Pain Medicine**
   - systematic review/meta-analysis
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/27165971/

3. **Davis et al., 2020 — BMJ Open Sport & Exercise Medicine**
   - systematic review/meta-analysis
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/32426160/

## Tier 2 — Direct deep-tissue evidence

4. **Bingölbali et al., 2024 — Somatosensory & Motor Research**
   - randomized controlled trial
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/36645811/

5. **Skillgate et al., 2020 — Musculoskeletal Science and Practice**
   - large randomized controlled trial
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/31655314/

6. **Koren & Kalichman, 2018 — Journal of Bodywork & Movement Therapies**
   - deep-tissue definition/evidence review
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/29861215/

## Tier 3 — Broader evidence maps

7. **Shekelle et al. / VA Evidence-based Synthesis Program**
   - Evidence Map of Massage Therapy for Painful Conditions
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/39163416/
   - NCBI Bookshelf: https://www.ncbi.nlm.nih.gov/books/NBK606160/

8. **Miake-Lye et al., 2019**
   - Massage for Pain: An Evidence Map
   - PubMed: https://pubmed.ncbi.nlm.nih.gov/30892910/

These broader evidence maps are useful when the agent needs to check whether a claim is being generalized beyond the evidence base.

---

# 9. Agent download checklist

For each retained source, download at minimum:

- PubMed page
- PubMed abstract
- PMC full text when a PMCID exists
- DOI landing page when needed
- NCBI Bookshelf full text for the VA evidence map

Suggested filenames:

```text
PMID-39008297-Mak-2024-Massage-Pain-JAMA-Network-Open
PMID-27165971-Crawford-2016-Massage-Function-Pain-MetaAnalysis
PMID-32426160-Davis-2020-Sports-Massage-Recovery-MetaAnalysis
PMID-36645811-Bingolbali-2024-Deep-Tissue-Myofascial-Pain-RCT
PMID-31655314-Skillgate-2020-STONE-Deep-Tissue-Neck-Pain-RCT
PMID-29861215-Koren-2018-Deep-Tissue-Review
PMID-39163416-Shekelle-VA-Evidence-Map-2023
PMID-30892910-MiakeLye-2019-Massage-Pain-Evidence-Map
```

## Direct machine-readable PubMed abstract endpoints

These are useful for automated extraction:

```text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=39008297&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=27165971&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=32426160&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=36645811&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=31655314&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=29861215&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=39163416&rettype=abstract&retmode=text
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=30892910&rettype=abstract&retmode=text
```

---

# 10. Final disposition table

| Claim | Current wording | Citation-only fix? | Recommended action |
|---|---|---:|---|
| A1 | Broad pain + mobility + recovery + relaxation + long-term self-care | **No** | Rewrite, then cite Crawford + appropriate narrower supporting source |
| A2 | Not captured | **Unknown** | Recover exact text first |
| A3 | Not captured | **Unknown** | Recover exact text first |
| A4 | Deep tissue broadly helps ongoing musculoskeletal discomfort | **No** | Narrow to studied populations; cite Bingölbali and/or STONE |
| A5 | Deep tissue linked to flexibility/recovery/functional movement | **No** | Rewrite as general massage/exercise evidence; cite Davis |
| A6 | Deep tissue effective for many muscular discomfort/movement restrictions | **No** | Rewrite with explicit uncertainty and condition-specific wording; cite Koren + direct RCT evidence |

---

# 11. Non-negotiable rule for implementation

Do **not** leave the original claim live merely because a related PubMed source has been found.

For every citation that gets added to the WordPress page, the agent should preserve an internal mapping:

```text
WEBSITE CLAIM
    ↓
EXACT EVIDENCE OUTCOME
    ↓
POPULATION
    ↓
INTERVENTION
    ↓
CITATION
    ↓
LIMITATION / QUALIFIER
```

If any link in that chain fails, the website sentence must be narrowed or removed.

The governing principle is:

> **Use the source to constrain the wording — not the wording to stretch the source.**
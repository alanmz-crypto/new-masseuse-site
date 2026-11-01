#!/usr/bin/env python3
"""Build therapeutic-evidence-content.php: the exact text and block markup that install-therapeutic-evidence.php puts on the Therapeutic Massage page (ID 1107). Standard library only.

    python3 -I -B scripts/work-with-me-employer/therapeutic/build-therapeutic-evidence.py          write therapeutic-evidence-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/therapeutic/build-therapeutic-evidence.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/therapeutic/build-therapeutic-evidence.py --text   print the visible text (for review)

SOURCE OF THE REQUEST (Ryan, 2026-10-19): "that is the page we want to emulate [the Thai page] ... the Therapeutic page is the page we are working on ... these are the instructions we have to follow [deep-research-report (1).md]",
then, to the six questions in docs/inter-model/therapeutic-massage-page-enhancement-plan.md: "use your judgement to make it honest". Decisions taken on that basis (the plan's section 7):
  Q1 the post-stroke review and the cancer-pain review that the page cited are DROPPED (studies of massage as a treatment for a disease are not used, as on the Thai page; OAR 334-010-0025(2)(c) and (4)); the body markers renumber.
  Q2 the neck-and-shoulder sentence is made honest against the 2024 Cochrane review ("mixed"), with both sources named; Ryan then asked for an encouraging idea on each side of it (a pace-and-pressure sentence before, the review's dose subgroup after).
  Q3 the Thai rules apply: every massage-scope trial or review found (pain, movement, soreness, recovery, stress, relaxation, well-being), unfavourable and mixed results included, no disease-treatment studies, no new pictures.
  Q4 only facts the site already states go into the FAQ (Services, Contact, the page's own text, Ryan's "no travel to clients"); what she lies on, clothing, session lengths, pregnancy and techniques are NOT stated: nobody has told us.
  Q5 the meta pair already on staging34 stands (services-meta/); Q6 no new call-to-action line and no new body links (the Thai page has none).

WHAT CHANGES on the page (everything by exact replacement; the installer refuses if any old text is not there exactly once):
  1. The two body paragraphs that carry numbered research links ("What Is Therapeutic Massage?", "Common Reasons ..."): stroke and cancer studies out, caregiver sentence reworded, the neck/shoulder sentence reworded, numbers re-derived from the registry below.
  2. Four generic questions appended to the existing FAQ accordion (answers carry no links: Spectra wraps question and answer in role="tab", so a link inside is an axe nested-interactive failure).
  3. "What the Research Says About Therapeutic Massage": an intro and five native WordPress "details" panels, appended after the last block so nothing above moves. 30 numbered sources, numbered by first use over
     the body paragraphs and then the section, so markers and list cannot drift apart.
  4. The five pictures are one illustration in five colour tints, all with the same alt text and a "Therapeutic Massage by Butsaba" tooltip (the house hygiene list: art presented as depicting a named person):
     each gets an alt text and tooltip that say what it shows.
EVERY SENTENCE was checked against the paper's own abstract (Europe PMC / Cochrane / NCCIH, 2026-10-19).
DIAGNOSIS VOCABULARY: study populations are described in plain words ("people with shoulder pain", "people with muscle and joint problems"); the Sources panel quotes titles exactly as published.
WORDING RULES (AGENTS.md, as updated 2026-10-19): first person for the practice, never we/our/us; the claim verbs (cure, treat, heal, diagnose, guarantee, detox, inflammation) never appear in prose (so a comparison with
"no treatment" is written "no intervention" or "no massage"); the OBMT-derived "evidence vocabulary" (chronic, anxiety, effective...) only inside the research section; verbatim titles in Sources as published.
Shared helpers (block markup, escaping, panels) are imported from ../thai/build-thai-evidence.py, which this does not modify: its own --check output is the proof nothing there moved.
"""
import importlib.util
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'therapeutic-evidence-content.php')

_spec = importlib.util.spec_from_file_location('thai_evidence_builder', os.path.join(HERE, '..', 'thai', 'build-thai-evidence.py'))
tb = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(tb)
attrs, esc, para, heading, details, faq_child = tb.attrs, tb.esc, tb.para, tb.heading, tb.details, tb.faq_child

# ---- block ids (8 hex characters; install-therapeutic-evidence.php refuses any that already occur in another post) ----------------------------------
ID_RES_HEAD = '6c2e9b47'
ID_FAQ_CHILD = ['3d8a5f21', 'a94c7e03', '5b1f6d98', 'e07a3c52']

# ---- the five pictures: block id -> (alt and tooltip). They are ONE illustration (a bearded therapist, a client lying face down on a table under a sheet, plants behind) in five colour tints. ------------
OLD_ALT = 'Illustration of a massage therapist working on a client lying face down.'
OLD_TITLE = 'Therapeutic Massage by Butsaba'
IMAGES = [
    ('e3c4d5e6', 'Illustration of a bearded massage therapist working on the back of a client who lies face down on a table, with plants behind them.'),
    ('b6f7a8b9', 'The same illustration in green tones.'),
    ('f0d1e2f3', 'The same illustration in blue and teal tones.'),
    ('d4b3c4d5', 'The same illustration in yellow tones.'),
    ('b8f7a8b9', 'The same illustration in red tones.'),
]

# ---- source registry: key -> (url, authors, title (verbatim), venue and year) ------------------------------------------------------------------
PM = 'https://pubmed.ncbi.nlm.nih.gov/%s/'
SRC = {
    'mak': (PM % '39008297', 'Mak S, Allen J, Begashaw M, Miake-Lye I, Beroes-Severin J, De Vries G, Lawson E, Shekelle PG.', 'Use of Massage Therapy for Pain, 2018-2023: A Systematic Review', 'JAMA Netw Open, 2024.'),
    'crawford': (PM % '27165971', 'Crawford C, Boyd C, Paat CF, Price A, Xenakis L, Yang E, Zhang W; Evidence for Massage Therapy (EMT) Working Group.', 'The Impact of Massage Therapy on Function in Pain Populations-A Systematic Review and Meta-Analysis of Randomized Controlled Trials: Part I, Patients Experiencing Pain in the General Population', 'Pain Med, 2016.'),
    'davis': (PM % '32426160', 'Davis HL, Alabed S, Chico TJA.', 'Effect of sports massage on performance and recovery: a systematic review and meta-analysis', 'BMJ Open Sport Exerc Med, 2020.'),
    'meier': (PM % '32901072', 'Meier M, Unternaehrer E, Dimitroff SJ, Benz ABE, Bentele UU, Schorpp SM, Wenzel M, Pruessner JC.', 'Standardized massage interventions as protocols for the induction of psychophysiological relaxation in the laboratory: a block randomized, controlled trial', 'Sci Rep, 2020.'),
    'sharpe': (PM % '17709060', 'Sharpe PA, Williams HG, Granner ML, Hussey JR.', 'A randomised study of the effects of massage therapy compared to guided relaxation on well-being and stress perception among older adults', 'Complement Ther Med, 2007.'),
    'williams': (PM % '31003683', 'Williams NA, Burnfield JM, Springer P, Wolf K, Buster T.', "Therapeutic massage to enhance family caregivers' well-being in a rehabilitation hospital", 'Complement Ther Clin Pract, 2019.'),
    'kurebayashi16': (PM % '27901219', 'Kurebayashi LF, Turrini RN, Souza TP, Takiguchi RS, Kuba G, Nagumo MT.', 'Massage and Reiki used to reduce stress and anxiety: Randomized Clinical Trial', 'Rev Lat Am Enfermagem, 2016.'),
    'kurebayashi20': (PM % '33053005', 'Kurebayashi LFS, Gnatta JR, Kuba G, Giaponesi ALL, Souza TPB, Turrini RNT.', 'Massage and Reiki to reduce stress and improve quality of life: a randomized clinical trial', 'Rev Esc Enferm USP, 2020.'),
    'nccih': ('https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know', 'National Center for Complementary and Integrative Health (NIH).', 'Massage Therapy: What You Need To Know', 'Last updated May 2019.'),
    'cochrane_neck': (PM % '38415786', 'Gross AR, Lee H, Ezzo J, Chacko N, Gelley G, Forget M, Morien A, Graham N, Santaguida PL, Rice M, Dixon C.', 'Massage for neck pain', 'Cochrane Database Syst Rev, 2024 (searches to October 2023).'),
    'miake': (PM % '30892910', 'Miake-Lye IM, Mak S, Lee J, Luger T, Taylor SL, Shanman R, Beroes-Severin JM, Shekelle PG.', 'Massage for Pain: An Evidence Map', 'J Altern Complement Med, 2019.'),
    'bervoets': (PM % '26093806', 'Bervoets DC, Luijsterburg PA, Alessie JJ, Buijs MJ, Verhagen AP.', 'Massage therapy has short-term benefits for people with common musculoskeletal disorders compared to no treatment: a systematic review', 'J Physiother, 2015.'),
    'ernst': (PM % '12777645', 'Ernst E.', 'The safety of massage therapy', 'Rheumatology (Oxford), 2003.'),
    'cochrane_lbp': (PM % '26329399', 'Furlan AD, Giraldo M, Baskwill A, Irvin E, Imamura M.', 'Massage for low-back pain', 'Cochrane Database Syst Rev, 2015.'),
    'vdd14': (PM % '22844035', 'van den Dolder PA, Ferreira PH, Refshauge KM.', 'Effectiveness of soft tissue massage and exercise for the treatment of non-specific shoulder pain: a systematic review with meta-analysis', 'Br J Sports Med, 2014.'),
    'vdd15': (PM % '26023217', 'van den Dolder PA, Ferreira PH, Refshauge KM.', 'Effectiveness of Soft Tissue Massage for Nonspecific Shoulder Pain: Randomized Controlled Trial', 'Phys Ther, 2015.'),
    'domingo': (PM % '27977513', 'Domingo AR, Diek M, Goble KM, Maluf KS, Goble DJ, Baweja HS.', 'Short-duration therapeutic massage reduces postural upper trapezius muscle activity', 'Neuroreport, 2017.'),
    'engen': (PM % '20846441', 'Engen DJ, Wahner-Roedler DL, Nadolny AM, Persinger CM, Oh JK, Spittell PC, Loehrer LL, Cha SS, Bauer BA.', 'The effect of chair massage on muscular discomfort in cardiac sonographers: a pilot study', 'BMC Complement Altern Med, 2010.'),
    'poppendieck': (PM % '26744335', 'Poppendieck W, Wegmann M, Ferrauti A, Kellmann M, Pfeiffer M, Meyer T.', 'Massage and Performance Recovery: A Meta-Analytical Review', 'Sports Med, 2016.'),
    'brooks': (PM % '16398602', 'Brooks CP, Woodruff LD, Wright LL, Donatelli R.', 'The immediate effects of manual massage on power-grip performance after maximal exercise in healthy adults', 'J Altern Complement Med, 2005.'),
    'huang': (PM % '20543728', 'Huang SY, Di Santo M, Wadden KP, Cappa DF, Alkanani T, Behm DG.', 'Short-duration massage at the hamstrings musculotendinous junction induces greater range of motion', 'J Strength Cond Res, 2010.'),
    'moyer04': (PM % '14717648', 'Moyer CA, Rounds J, Hannum JW.', 'A meta-analysis of massage therapy research', 'Psychol Bull, 2004.'),
    'moyer11': (PM % '21147413', 'Moyer CA, Seefeldt L, Mann ES, Jackley LM.', 'Does massage therapy reduce cortisol? A comprehensive quantitative review', 'J Bodyw Mov Ther, 2011.'),
    'kanitz': (PM % '26365448', 'Kanitz JL, Reif M, Rihs C, Krause I, Seifert G.', 'A randomised, controlled, single-blinded study on the impact of a single rhythmical massage (anthroposophic medicine) on well-being and salivary cortisol in healthy adults', 'Complement Ther Med, 2015.'),
    'yin': (PM % '25197310', 'Yin P, Gao N, Wu J, Litscher G, Xu S.', 'Adverse events of massage therapy in pain-related conditions: a systematic review', 'Evid Based Complement Alternat Med, 2014.'),
    'arroyo08': (PM % '19123877', 'Arroyo-Morales M, Olea N, Martínez MM, Hidalgo-Lozano A, Ruiz-Rodríguez C, Díaz-Rodríguez L.', 'Psychophysiological effects of massage-myofascial release after exercise: a randomized sham-control study', 'J Altern Complement Med, 2008.'),
    'monteiro': (PM % '41154233', 'Monteiro ER, Pescatello LS, Garcia GH, de Meirelles AG, de Oliveira F, de Souza RC, Nogueira LAC, Lopes AJ, Moreira-Gonçalves D.', 'Upper Cervical Manipulation and Manual Massage Do Not Modulate Sympatho-Vagal Balance or Blood Pressure in Women: A Randomized, Placebo-Controlled Clinical Trial', 'Healthcare (Basel), 2025.'),
    'ucurum': (PM % '40954653', 'Ucurum SG, Celenay ST, Keskin M, Kaya DO.', 'Short-term effects of classical massage added to a conventional physiotherapy on non-specific chronic low back Pain: A randomized, placebo-controlled study', 'J Bodyw Mov Ther, 2025.'),
    'visconti': (PM % '31956433', 'Visconti L, Forni C, Coser R, Trucco M, Magnano E, Capra G.', 'Comparison of the effectiveness of manual massage, long-wave diathermy, and sham long-wave diathermy for the management of delayed-onset muscle soreness: a randomized controlled trial', 'Arch Physiother, 2020.'),
    'bello': (PM % '18576922', 'Bello D, White-Traut R, Schwertz D, Pournajafi-Nazarloo H, Carter CS.', 'An exploratory study of neurohormonal responses of healthy men to massage', 'J Altern Complement Med, 2008.'),
}

# ---- the two body paragraphs: OLD is the stored text exactly as it is on the page today (live, staging34 and localhost agree); NEW is built from the registry. ------------------------------------------------
A_FIRST = ('Therapeutic massage combines focused work with time to relax. I start with what brought you in. You can ask me to stay with one area, such as your neck and shoulders, or to work more broadly, '
           'and the plan can change as you give me feedback.<br><br>')


def _a(n, pm):
    return '<a href="https://pubmed.ncbi.nlm.nih.gov/%s/">[%d]</a>' % (pm, n)


NCCIH_A = '<a href="https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know">[11]</a>'
BODY_A_OLD = (A_FIRST
              + 'Research suggests that massage therapy may help reduce pain in some conditions and may improve certain movement-related outcomes in studied pain populations.' + _a(1, '39008297') + _a(2, '27165971')
              + ' Studies of sports massage have found small improvements in flexibility and exercise-related muscle soreness.' + _a(3, '32426160')
              + ' Brief massage has been studied for short-term relaxation.' + _a(4, '32901072')
              + ' Regular massage was associated with improved well-being in a small four-week study of older adults.' + _a(5, '17709060') + '<br><br>'
              + 'Research has explored massage in several adult groups, including family caregivers.' + _a(2, '27165971') + _a(6, '33919371') + _a(7, '39558520') + _a(8, '31003683')
              + ' Research has also examined massage in relation to stress and quality-of-life measures in specific adult groups.' + _a(9, '27901219') + _a(10, '33053005') + _a(5, '17709060') + '</p>')
BODY_A_NEW = (A_FIRST
              + 'Research suggests that massage therapy may help reduce pain in some conditions and may improve certain movement-related outcomes in studied pain populations.{mak}{crawford}'
              + ' Studies of sports massage have found small improvements in flexibility and exercise-related muscle soreness.{davis}'
              + ' Brief massage has been studied for short-term relaxation.{meier}'
              + ' Regular massage was associated with improved well-being in a small four-week study of older adults.{sharpe}<br><br>'
              + 'Some studies have looked at massage in family caregivers.{williams}'
              + ' Research has also examined massage in relation to stress and quality-of-life measures in specific adult groups.{kurebayashi16}{kurebayashi20}{sharpe}</p>')

B_FIRST = ('People often book therapeutic massage for everyday muscle tension and stiffness, or simply to make time to slow down. Some also seek focused work for neck or shoulder discomfort. ')
B_LAST = (' Before I begin, I ask about your preferences and anything I should know about your health. You can request lighter pressure, a different focus, or a pause at any time.</p>')
BODY_B_OLD = (B_FIRST + 'Research suggests massage may be helpful for some people with neck or shoulder pain, though the benefits may last only a short time.' + NCCIH_A
              + ' Massage has also been studied for low-back pain, although the evidence is limited.' + NCCIH_A + B_LAST)
# Ryan, 2026-10-19 ("This is fine, it's honest, however edit it so that the idea before you say this and the idea after you say this says something encouraging about therapeutic massage"): the honest "mixed" sentences stay
# exactly as they were; an encouraging idea now sits on each side, and both are true to their source (what the Services page says about pace and pressure; the Cochrane review's own dose subgroup, hedged as the review hedges it).
B_FIRST_NEW = ('People often book therapeutic massage for everyday muscle tension and stiffness, or simply to make time to slow down. Some also seek focused work for neck or shoulder discomfort, '
               'and I adapt the pace and pressure to what suits you. ')
BODY_B_NEW = (B_FIRST_NEW + 'Research on massage for neck and shoulder pain is mixed. It may help some people for a short time,{nccih} but a 2024 Cochrane review found that, for neck pain, massage probably makes little or no '
              'difference compared with a placebo massage.{cochrane_neck} A subgroup analysis in that review favored higher doses (eight or more sessions of at least 30 minutes over four weeks), which suggests that '
              'regular sessions may matter.{cochrane_neck} Massage has also been studied for low-back pain, although the evidence is limited.{nccih}' + B_LAST)

# ---- the FAQ: four generic questions appended to the existing accordion. Every statement restates what the site already says (Services, Contact, this page) or what Ryan said (no travel to clients). ------------------
FAQ = [
    ('How Is Therapeutic Massage Different From Deep Tissue Massage?',
     'Therapeutic massage gives steady attention to the places where you feel tension, at a pace and pressure that suit you. Deep tissue massage uses focused strokes and sustained pressure on areas that stay tight. '
     'I check in about pressure in both, and each is described on its own page.'),
    ('Can I Change the Pressure During a Session?',
     'Yes. You can ask for lighter pressure, a different focus, or a pause at any time, and I will adjust.'),
    ('Where Do Sessions Take Place?',
     'Sessions take place in Mt. Tabor, Portland. I do not currently travel to clients.'),
    ('How Do I Book a Session?',
     'Use the Contact page to reach me by phone or email, and tell me what you would like. I will reply to arrange a time.'),
]
FAQ_LAST_OLD = ('<!-- wp:uagb/faq-child {"block_id":"d6b5c6d7","question":"Can I Ask You to Focus on My Neck and Shoulders?","answer":"Yes. Tell me at the start where you would like the focus, and you can change it at any point '
                'during the session. For anything new, severe, or worrying, please see a doctor first.","icon":"chevron-up","iconActive":"chevron-down"} /--></div>\n<!-- /wp:uagb/faq -->')

RES_INTRO = ('Research on massage therapy is large but mixed. Many studies are small, results often depend on what massage is compared with, and many follow people for only a few weeks. '
             'The summaries below describe what the studies reported. They are general information, not medical advice or a promise of any result, and people respond differently. '
             'This section covers studies of massage for pain, movement, soreness, stress and well-being; studies of massage used for specific medical conditions are not summarized here. '
             'Each part starts with what the studies found in massage’s favor, then gives the results that were weaker, mixed or negative, and ends on what is encouraging: I included the studies I found '
             'with disappointing results, not only the favorable ones.')

# Ryan, 2026-10-19 ("make sure I didn't miss any discouraging or poor results ... so we can sandwich the discouraging results between two assertive positive claims", then "you can sandwich with the method you
# used on the Therapeutic page"): every panel runs positive findings, then the weaker, mixed and negative ones IN FULL, then a positive close. Every claim on each side is sourced and hedged as its source hedges it.
# New in this version (found by the audit): a safety review (Yin 2014), two sham-controlled trials after exercise (Arroyo-Morales 2008: lower muscle activity and vigor; Visconti 2020: no clinically relevant
# difference), a null heart-rate and blood-pressure trial (Monteiro 2025) and a trial in which placebo massage did as well as real massage (Ucurum 2025).
PANELS = [
    ('Reviews, and What They Say About Limits', True, [
        'Reviews that rated how certain their evidence was reached a consistent point in massage’s favor: every conclusion rated moderate-certainty found massage associated with less pain, and a meta-analysis of '
        '67 randomized trials found lower pain scores after massage than after sham massage, no massage or other approaches.{mak}{crawford}',
        (
            'The same reviews are careful about limits. None of the conclusions in the 2024 review was rated high-certainty, and conclusions backed by '
            'moderate- or high-certainty evidence that massage was better than other active approaches were rare. An earlier evidence map of 49 systematic '
            'reviews found that the high-quality reviews reached conclusions of low strength of evidence, because few large, rigorous studies had been '
            'done.{miake} The meta-analysis’s authors made a strong recommendation only for the comparison with no massage and a weak one for sham massage and'
            ' other approaches.{crawford} A 2015 review of 26 randomized trials with 2,565 people with muscle and joint problems found low-to-moderate-level '
            'evidence that massage reduced pain in the short term compared with no intervention in some groups, such as people with shoulder pain, but not in '
            'people with neck or low-back pain. Function improved in the short term for people with low-back and shoulder pain. Compared with another active '
            'approach, no clear benefit was seen.{bervoets}'
        ),
        (
            'Massage is generally described as low risk, and where serious problems on record can be traced to who gave the massage, they are mostly tied to '
            'people who were not trained massage therapists. The U.S. National Center for Complementary and Integrative Health notes that the risk of harm '
            'appears to be low, although rare serious side effects such as a blood clot, nerve injury or bone fracture have been reported.{nccih} A 2014 '
            'review of 40 published reports found 138 adverse events over 11 years, mainly disc herniation, soft-tissue injury, nerve problems, spinal cord '
            'injury and tears in the neck arteries, with spinal manipulation inside a massage repeatedly linked to the serious ones. Massage therapists were '
            'the practitioners in only 8 of the 138 events (5.8 percent) and chiropractors in 33 (23.9 percent); the rest (70.3 percent) involved unregistered'
            ' people or other health professionals, and the authors concluded that massage by nonprofessionals and forceful techniques is often associated '
            'with adverse events.{yin} A 2003 review of 16 case reports and four case series likewise found that most adverse effects followed exotic '
            'techniques or massage by laypeople, with massage therapists rarely implicated, that serious effects were mostly tied to techniques other than '
            'Swedish massage, and concluded that serious events are probably true rarities.{ernst} The U.S. center also advises against using massage to put '
            'off seeing a health care provider about a medical problem. Taken together, in the reports that say who gave the massage, trained massage '
            'therapists were rarely involved: the serious injuries were mostly linked to people who were not massage therapists, including laypeople and '
            'unregistered practitioners, and to forceful or unusual techniques. Serious events are rare: I ask about your health, medications, pregnancy and '
            'recent injuries before each session, and you can ask for lighter pressure or a pause at any time.'
        ),
    ]),
    ('Neck, Shoulders and Back', False, [
        'Several reviews report short-term gains from massage for neck, shoulder and back pain. A 2015 Cochrane review of 25 trials found that massage was better than no intervention for low-back pain in the short '
        'term.{cochrane_lbp} A 2014 review of 20 trials found low-quality evidence that soft-tissue massage improved shoulder movement, pain and function compared with no intervention in the short term, right after '
        'the sessions ended.{vdd14} In the 2024 Cochrane review of neck pain, a subgroup analysis favored higher doses of eight or more sessions of at least 30 minutes over four weeks.{cochrane_neck}',
        (
            (
                'The same reviews also report the limits. The low-back review rated the quality of the evidence low to very low, found no long-term benefit over '
                'no intervention, and its authors said they had “very little confidence” in the results. No serious side effects were reported; the most common '
                'minor one was more pain, in 1.5 to 25 percent of people.{cochrane_lbp} The 2024 neck review of 33 studies with 1,994 people found that, compared '
                'with a placebo massage, massage probably makes little or no difference to pain, function or quality of life at up to 12 weeks (low-certainty '
                'evidence), and adverse events were too poorly reported to judge.{cochrane_neck} In a trial of 80 adults with shoulder pain who were all doing '
                'exercises, adding four weeks of massage gave no extra benefit for pain, disability or movement, and a small difference in pain at 12 weeks '
                'favored exercise alone.{vdd15} In 41 people with low-back pain who were all having physiotherapy, adding real massage or a placebo massage for '
                'three weeks both reduced pain and disability and improved mobility, and only the real-massage group improved on a quality-of-life '
                'measure.{ucurum}'
            )
        ),
        (
            'Smaller studies point the same encouraging way for tight shoulder muscles. In a crossover study of 17 healthy adults, muscle activity in the '
            'upper-shoulder muscles fell by 19 percent after a short, moderate-pressure massage of the shoulders and neck, compared with 1 percent after '
            'sitting quietly; the study measured muscle activity, not pain.{domingo} In a pilot study of 45 cardiac sonographers with work-related muscle '
            'discomfort, weekly 30-minute chair massages for 10 weeks, with or without stretching, were followed by some improvement in discomfort scores '
            'compared with no intervention, and the authors called for larger trials.{engen}'
        ),
    ]),
    ('Exercise, Flexibility and Recovery', False, [
        'Of the exercise outcomes reviewed, flexibility and soreness are where massage shows benefit. A 2020 review of 29 randomized studies with 1,012 people found small improvements in flexibility and in '
        'delayed-onset muscle soreness.{davis} In 10 recreationally active women, 30 seconds of massage at the back of the thigh was followed by about a 7 percent larger hip-bend range than no massage, with no '
        'change in muscle tension or muscle activity.{huang} In 52 healthy volunteers whose grip had been tired by exercise, five minutes of forearm and hand massage was followed by better grip performance than '
        'five minutes of rest or of passive joint movement; the study was small and measured only the minutes right afterward.{brooks}',
        (
            'The same 2020 review found no evidence that massage improved strength, jumping, sprinting, endurance or fatigue.{davis} A 2016 review of 22 '
            'trials concluded that the effects of massage on performance recovery are rather small and partly unclear, and its authors questioned whether the '
            'limited effects justify using massage as a recovery method in competitive athletes.{poppendieck} In 62 healthy active students who had a '
            '40-minute whole-body myofascial-release massage after hard exercise, massage was followed by lower muscle activity in a thigh muscle and a lower '
            'mood score for vigor than a sham session, which the authors said may reflect a transient loss of muscle strength or a state of '
            'relaxation.{arroyo08} In people with sore leg muscles after exercise, manual massage, real diathermy (a heating device) and sham diathermy '
            'produced no clinically relevant differences in pain scores right after the session, although scores on a patient-rated scale of global change did'
            ' differ between the groups.{visconti}'
        ),
        'Where massage has been tested for short-term recovery after intense mixed training, the effects were medium in size: the 2016 review found medium positive effects after high-intensity mixed exercise, and '
        'shorter massage (5 to 12 minutes) tended to show larger effects than longer massage.{poppendieck}',
    ]),
    ('Stress, Relaxation and Well-Being', False, [
        (
            'Several studies point to short-term relaxation after massage. A 2004 meta-analysis of 37 randomized studies found that a single massage was '
            'followed by lower state anxiety, blood pressure and heart rate.{moyer04} In 60 healthy women, the two massage groups showed higher heart-rate '
            'variability than a resting group.{meier} In 118 healthy adults who first took a stressful laboratory test, most people who had a real rhythmical '
            'massage described it as relaxing (82 to 100 percent, compared with 42 percent after a sham massage).{kanitz}'
        ),
        (
            'The picture is mixed, though. The same 2004 meta-analysis found no lower negative mood, cortisol or less immediate pain after a single '
            'massage,{moyer04} and a 2011 review found that the effect on the stress hormone cortisol was generally very small and in most analyses not '
            'statistically distinguishable from zero.{moyer11} In the 60 healthy women, relaxation rose and stress fell in the resting group too.{meier} In '
            'the 118 healthy adults, a single rhythmical massage of about an hour was not followed by significantly better well-being scores or cortisol '
            'levels than a sham massage.{kanitz} In 14 healthy men, a 20-minute massage and a 20-minute reading period were each followed by a rise in the '
            'hormone oxytocin, which led the researchers to suggest that touch is not needed for that rise.{bello} In 15 healthy women, a single two-minute '
            'manual massage was not followed by significant differences in heart-rate variability or blood pressure compared with rest or a sham, although the'
            ' authors noted changes of moderate to large size within the massage condition.{monteiro} In 38 family caregivers at a rehabilitation hospital who'
            ' had either one or three massages a week for two weeks, psychological symptom scores were lower afterward in both groups, but because everyone '
            'had massage the study cannot show how much of the change was due to the massage itself.{williams}'
        ),
        (
            'Courses of massage have the more encouraging results: the largest effects in the 2004 meta-analysis came after a course of sessions rather than a'
            ' single one, on measures of anxiety and mood.{moyer04} In a pilot study of 54 adults aged 60 and over, twice-weekly massage for four weeks was '
            'followed by better scores for well-being and perceived stress than guided relaxation.{sharpe} In a trial of 122 adults at an integrative-therapy '
            'clinic in Brazil, people had eight Anma (Japanese) massage sessions followed by rest, massage followed by Reiki, or no intervention. Stress fell '
            'more in the massage-plus-Reiki group than in the no-intervention group, and the authors called for a placebo group in later '
            'trials.{kurebayashi16} The same trial’s report on stress and quality of life (101 people analyzed) found that both massage groups did better than'
            ' no intervention.{kurebayashi20}'
        ),
    ]),
]


class Cite:
    """Numbers sources in order of first use (reading order) and turns {key} markers into the page's own numbered links."""
    def __init__(self):
        self.order = []

    def mark(self, text):
        out, i = '', 0
        while True:
            j = text.find('{', i)
            if j < 0:
                return out + text[i:]
            k = text.index('}', j)
            key = text[j + 1:k]
            if key not in SRC:
                raise SystemExit('unknown source key: ' + key)
            if key not in self.order:
                self.order.append(key)
            out += text[i:j] + '<a href="%s">[%d]</a>' % (SRC[key][0], self.order.index(key) + 1)
            i = k + 1


def build():
    cite = Cite()
    body_a_new = cite.mark(BODY_A_NEW)
    body_b_new = cite.mark(BODY_B_NEW)
    # The five numbers the unchanged first half of the body paragraph already carries must be what the registry gives them (1..5), or the page would renumber text this change does not touch.
    if cite.order[:5] != ['mak', 'crawford', 'davis', 'meier', 'sharpe']:
        raise SystemExit('the unchanged part of the body paragraph would be renumbered: ' + str(cite.order[:5]))

    faq_children = '\n\n'.join(faq_child(ID_FAQ_CHILD[i], q, a) for i, (q, a) in enumerate(FAQ))
    faq_new = FAQ_LAST_OLD.replace('/--></div>\n<!-- /wp:uagb/faq -->', '/-->\n\n' + faq_children + '</div>\n<!-- /wp:uagb/faq -->')
    if faq_new == FAQ_LAST_OLD:
        raise SystemExit('the FAQ anchor did not change')

    panels = []
    for title, opened, paras in PANELS:
        panels.append(details(title, opened, [para(cite.mark(p), plain=True) for p in paras]))
    items = []
    for n, key in enumerate(cite.order, 1):
        url, authors, title, venue = SRC[key]
        items.append('<!-- wp:list-item -->\n<li><cite class="ts-source">%s <a href="%s">%s</a>. %s</cite></li>\n<!-- /wp:list-item -->' % (esc(authors), url, esc(title), esc(venue)))
    sources_list = '<!-- wp:list {"ordered":true,"style":{"typography":{"fontWeight":"400"}}} -->\n<ol class="wp-block-list" style="font-weight:400">%s</ol>\n<!-- /wp:list -->' % '\n\n'.join(items)
    panels.append(details('Sources', False, [para('Each number above links to the source. Titles are quoted as published.', plain=True), sources_list]))

    # The stored page ends with the last block and NO newline (read from the database), so the appended text starts with the blank line that separates blocks.
    sections = '\n\n' + '\n\n'.join([heading(ID_RES_HEAD, 'What the Research Says About Therapeutic Massage'), para(esc(RES_INTRO)), '\n\n'.join(panels)]) + '\n'

    unused = [k for k in SRC if k not in cite.order]
    if unused:
        raise SystemExit('registry has sources the text never cites: %s' % unused)

    images = []
    for block_id, alt in IMAGES:
        images.append({'id': block_id, 'old_alt': OLD_ALT, 'new_alt': alt, 'old_title': OLD_TITLE, 'new_title': alt})
    return {
        'ids': [ID_RES_HEAD] + ID_FAQ_CHILD,
        'body_a_old': BODY_A_OLD, 'body_a_new': body_a_new,
        'body_b_old': BODY_B_OLD, 'body_b_new': body_b_new,
        'faq_old': FAQ_LAST_OLD, 'faq_new': faq_new,
        'images': images,
        'tail': '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container --></div></div>\n<!-- /wp:uagb/container -->',
        'sections': sections,
        'research_start': '<!-- wp:uagb/advanced-heading %s -->' % (tb.HEAD_ATTRS % ID_RES_HEAD),
        'source_count': len(cite.order),
        'source_urls': [SRC[k][0] for k in cite.order],
        'faq_questions': [q for q, _ in FAQ],
        'faq_text': ' '.join(q + ' ' + a for q, a in FAQ),   # the FAQ lives in block attributes, which a tag-stripper cannot see; the installer's wording check reads this copy
        'image_text': ' '.join(alt for _, alt in IMAGES),     # same for the alt texts and tooltips
        'body_marker_count': len(cite.order),
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'THERJSON' not in js
    return ('<?php\n// GENERATED by build-therapeutic-evidence.py: do not edit by hand (edit the builder and run it again). install-therapeutic-evidence.php loads this.\n'
            'return json_decode( <<<\'THERJSON\'\n%s\nTHERJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        import re
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['body_a_new'] + '\n' + data['body_b_new'])))
        print()
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['sections'])))
        sys.exit(0)
    body = php(data)
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('therapeutic-evidence-content.php matches the builder' if have == body else 'therapeutic-evidence-content.php is OUT OF DATE: run the builder')
        sys.exit(0 if have == body else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(body)
    print('wrote %s (%d bytes); %d numbered sources' % (OUT, len(body.encode('utf-8')), data['source_count']))

# ref: ../thai/build-thai-evidence.py — the Thai builder whose helpers this imports (and which it must never change); ./install-therapeutic-evidence.php — loads the generated file
# ref: ../../../docs/inter-model/therapeutic-massage-page-enhancement-plan.md — the plan and the six decisions

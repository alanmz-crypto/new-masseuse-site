#!/usr/bin/env python3
"""Build deep-tissue-evidence-content.php: the exact text and block markup that install-deep-tissue-evidence.php puts on the Deep Tissue Massage page (ID 1091). Standard library only.

    python3 -I -B scripts/work-with-me-employer/deep-tissue/build-deep-tissue-evidence.py          write deep-tissue-evidence-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/deep-tissue/build-deep-tissue-evidence.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/deep-tissue/build-deep-tissue-evidence.py --text   print the visible text (for review)

SOURCE OF THE REQUEST (Ryan, 2026-10-24): "Give the Deep Tissue Massage page the same treatment the Thai and Therapeutic pages got ... a cited research section with collapsible panels in the sandwich order, a short generic FAQ,
honest body sentences; same rules: wording tiers, no disease-treatment studies, every sentence matched to its source's abstract." The plan (docs/inter-model/deep-tissue-massage-page-enhancement-plan.md) was approved
("Approved, go ahead and build it") with every default: the 2025 athletes trial stays in the research section with its limits stated, the safety paragraph keeps the case reports, the six FAQ questions as written.

WHAT CHANGES on the page (everything by exact replacement or append; the installer refuses if an old text is not there exactly once):
  1. The research paragraph of "What Is Deep Tissue Massage?" (three sentences, markers [1]-[4]) becomes one honest "limited and mixed" paragraph in the sandwich order, markers renumbered from the registry. The first paragraph is untouched.
  2. APPENDED after the last block, so nothing above moves: the heading "Questions About Deep Tissue Massage" with a six-question accordion (the page had none; link-free answers, because Spectra wraps question and answer in
     role="tab" and a link inside is an axe nested-interactive failure), then the heading "What the Research Says About Deep Tissue Massage", its intro and four native "details" panels plus a Sources panel (33 numbered sources).
  3. The three pictures keep their file, size and alt text; the tooltip "Deep Tissue Massage by Butsaba" (art presented as depicting a named person: the house hygiene list) becomes the picture's own alt text, as on the Therapeutic page.
     (This computer cannot fetch the images, so no new description of what they show is invented: the alt texts already on the page stay.)
EVERY SENTENCE was written against the paper's own abstract (Europe PMC, read 2026-10-24); the 14 sources shared with the Therapeutic page use that page's sentences (checked 2026-10-19) and their registry entries.
DIAGNOSIS VOCABULARY: study populations are in plain words ("neck pain linked to tender spots in the muscles"); the Sources panel quotes titles exactly as published.
WORDING RULES (AGENTS.md): first person for the practice, never we/our/us; the claim verbs (cure, treat, heal, diagnose, guarantee, detox, inflammation) never appear in prose (so "no massage", not "no treatment");
the OBMT-derived evidence vocabulary (chronic, anxiety, effective, disability...) only inside the research section; verbatim titles in Sources as published.
Shared helpers (block markup, escaping, panels) come from ../thai/build-thai-evidence.py and the reused source entries from ../therapeutic/build-therapeutic-evidence.py, which this does not modify.
"""
import importlib.util
import json
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'deep-tissue-evidence-content.php')


def _load(name, rel):
    spec = importlib.util.spec_from_file_location(name, os.path.join(HERE, rel))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


tb = _load('thai_evidence_builder', '../thai/build-thai-evidence.py')
th = _load('therapeutic_evidence_builder', '../therapeutic/build-therapeutic-evidence.py')
attrs, esc, para, heading, details, faq_child = tb.attrs, tb.esc, tb.para, tb.heading, tb.details, tb.faq_child

# ---- block ids (8 hex characters; install-deep-tissue-evidence.php refuses any that already occur in another post) ------------------------------
ID_FAQ_HEAD, ID_RES_HEAD, ID_FAQ = 'c1df0962', '74d424ce', '17dd836e'
ID_FAQ_CHILD = ['445eceec', 'ee42031f', '9bc1a6c7', 'da3bd1d7', 'd86a7fe0', '854241e4']

# ---- the three pictures: block id -> alt text (kept). The tooltip becomes the alt text. ------------------------------------------------------
OLD_TITLE = 'Deep Tissue Massage by Butsaba'
IMAGES = [
    ('259aae54', 'Illustration of a massage therapist working with a client.'),
    ('22bf66dd', 'Illustration of a therapist massaging a client on a green background.'),
    ('5975b480', 'Illustration of a massage therapist working with a client on a bright green background.'),
]

# One verbatim title contains "we" (Koren and Kalichman 2018: "What are we talking about?"), a word the site never uses, even in a title (wording_tiers.py tier 1). It is written with an ellipsis, and the note says so.
SOURCES_NOTE = 'Each number above links to the source. Titles are quoted as published, except one that is shortened with an ellipsis because it contains a word this site does not use.'

# ---- source registry: key -> (url, authors, title (verbatim), venue and year). New for this page first, then the ones shared with the Therapeutic page. ----------------
PM = 'https://pubmed.ncbi.nlm.nih.gov/%s/'
SRC = {
 'bingolbali': (PM % '36645811', 'Bingölbali Ö, Taşkaya C, Alkan H, Altındağ Ö.', 'The effectiveness of deep tissue massage on pain, trigger point, disability, range of motion and quality of life in individuals with myofascial pain syndrome', 'Somatosens Mot Res, 2024.'),
 'skillgate': (PM % '31655314', 'Skillgate E, Pico-Espinosa OJ, Côté P, Jensen I, Viklund P, Bottai M, Holm LW.', 'Effectiveness of deep tissue massage therapy, and supervised strengthening and stretching exercises for subacute or persistent disabling neck pain. The Stockholm Neck (STONE) randomized controlled trial', 'Musculoskelet Sci Pract, 2020.'),
 'picoespinosa': (PM % '31989965', 'Pico-Espinosa OJ, Aboagye E, Côté P, Peterson A, Holm LW, Jensen I, Skillgate E.', 'Deep tissue massage, strengthening and stretching exercises, and a combination of both compared with advice to stay active for subacute or persistent non-specific neck pain: A cost-effectiveness analysis of the Stockholm Neck trial (STONE)', 'Musculoskelet Sci Pract, 2020.'),
 'koren': (PM % '29861215', 'Koren Y, Kalichman L.', 'Deep tissue massage: What are … talking about?', 'J Bodyw Mov Ther, 2018.'),
 'arsovski': (PM % '40510040', 'Arsovski D.', 'Deep Tissue Massage Therapy: Effects on Muscle Recovery and Performance in Athletes', 'Int J Ther Massage Bodywork, 2025.'),
 'zheng': (PM % '23427384', 'Zheng Z, Wang J, Gao Q, Hou J, Ma L, Jiang C, Chen G.', 'Therapeutic evaluation of lumbar tender point deep massage for chronic non-specific low back pain', 'J Tradit Chin Med, 2012.'),
 'romanowski': (PM % '22744541', 'Romanowski M, Romanowska J, Grześkowiak M.', 'A comparison of the effects of deep tissue massage and therapeutic massage on chronic low back pain', 'Stud Health Technol Inform, 2012.'),
 'wendt': (PM % '41270086', 'Wendt M, Rubach J, Waszak M.', 'Short-term effect after soft tissue manipulation session on subjective and objective parameters in office workers with chronic low back pain: A randomized clinical trial', 'PLoS One, 2025.'),
 'majchrzycki': (PM % '24707200', 'Majchrzycki M, Kocur P, Kotwicki T.', 'Deep tissue massage and nonsteroidal anti-inflammatory drugs for low back pain: a prospective randomized trial', 'ScientificWorldJournal, 2014.'),
 'thomson': (PM % '26396740', 'Thomson D, Gupta A, Arundell J, Crosbie J.', 'Deep soft-tissue massage applied to healthy calf muscle has no effect on passive mechanical properties: a randomized, single-blind, cross-over study', 'BMC Sports Sci Med Rehabil, 2015.'),
 'ce': (PM % '23256711', 'Cè E, Limonta E, Maggioni MA, Rampichini S, Veicsteinas A, Esposito F.', 'Stretching and deep and superficial massage do not influence blood lactate levels after heavy-intensity cycle exercise', 'J Sports Sci, 2013.'),
 'carvalho': (PM % '36767051', 'Carvalho FA, Batista NP, Diniz FP, Machado AF, Micheletti JK, Pastre CM.', "Repeated Massage Improves Swimmers' Perceptions during Training Sessions but Not Sprint and Functional Performance: A Randomized Controlled Trial", 'Int J Environ Res Public Health, 2023.'),
 'freylaw': (PM % '18455480', 'Frey Law LA, Evans S, Knudtson J, Nus S, Scholl K, Sluka KA.', 'Massage reduces pain perception and hyperalgesia in experimental muscle pain: a randomized, controlled trial', 'J Pain, 2008.'),
 'kaye': (PM % '18315516', 'Kaye AD, Kaye AJ, Swinford J, Baluch A, Bawcom BA, Lambert TJ, Hoover JM.', 'The effect of deep-tissue massage therapy on blood pressure and heart rate', 'J Altern Complement Med, 2008.'),
 'goldberg': (PM % '1589464', 'Goldberg J, Sullivan SJ, Seaborne DE.', 'The effect of two intensities of massage on H-reflex amplitude', 'Phys Ther, 1992.'),
 'truong': (PM % '41727343', 'Truong MH, Tran VQ, Tran SD, Nguyen CT, Pham PC, Pham QG.', 'Case Report: Kidney injury following non-medical massage', 'Front Nephrol, 2026.'),
 'sanborn': (PM % '40995160', 'Sanborn M, Koury M, Maity A, Ali Z.', 'A Rare Cause of Internal Jugular Vein Thrombosis: Deep Tissue Massage', 'Ochsner J, 2025.'),
 'crump': (PM % '21150153', 'Crump C, Paluska SA.', 'Venous thromboembolism following vigorous deep tissue massage', 'Phys Sportsmed, 2010.'),
 'aksoy': (PM % '19887226', 'Aksoy IA, Schrader SL, Ali MS, Borovansky JA, Ross MA.', 'Spinal accessory neuropathy associated with deep tissue massage: a case report', 'Arch Phys Med Rehabil, 2009.'),
    # shared with the Therapeutic page: taken from its registry, so the entry cannot drift
    **{k: th.SRC[k] for k in ('mak', 'crawford', 'miake', 'nccih', 'yin', 'ernst', 'cochrane_neck', 'cochrane_lbp', 'davis', 'poppendieck', 'visconti', 'moyer04', 'meier', 'sharpe')},
}

# ---------------- body: the research paragraph of "What Is Deep Tissue Massage?" (the first paragraph stays word for word) ----------------
BODY_FIRST = ('Deep tissue massage uses slow, deliberate pressure on muscles that stay tight. I begin by asking about your goals and how much pressure you like, then check in as I work. '
              'Deep pressure is never required: you can ask me to lighten up at any time, and I will.')
BODY_OLD = ('Research suggests deep tissue massage added to conventional rehabilitation may reduce pain intensity and improve some neck-movement and quality-of-life measures in people with neck pain linked to tender spots in the muscles.{bingolbali} '
            'A randomized trial in athletes linked repeated deep tissue massage with improvements in flexibility and recovery-related measures, though the results may not apply to every client.{arsovski} '
            'Trials in specific musculoskeletal conditions suggest deep tissue massage may improve pain or movement-related outcomes for some people, but results vary by condition and outcome, and long-term pain benefits remain uncertain.{bingolbali}{skillgate}{koren}')
BODY_NEW = ('Research on deep tissue massage is limited and mixed. In a trial of 80 adults with neck pain linked to tender spots in the muscles, adding deep tissue massage to conventional rehabilitation was followed by more improvement in pain, '
            'quality-of-life scores and most neck movements than rehabilitation alone.{bingolbali} '
            'A larger trial of 619 adults with ongoing neck pain found that deep tissue massage led to more improvement in pain than advice to stay active in the short term but not by 12 months, '
            'and a review of the research found that studies differ too much in what they call deep tissue massage and how they do it to draw clear conclusions.{skillgate}{koren} '
            'People given massage, exercise or both in that larger trial were also more likely than those given advice to say they had recovered, at every follow-up through one year.{skillgate}')

# ---------------- FAQ: six generic questions, every statement restates what the page or the site already says ----------------
FAQ = [
 ('Does Deep Tissue Massage Have to Hurt?',
  'No. Deep pressure is never required. Tell me how much pressure you like, and ask me to lighten up at any time; I will.'),
 ('How Is Deep Tissue Massage Different From Therapeutic Massage?',
  'Deep tissue massage uses slow, deliberate pressure on muscles that stay tight. Therapeutic massage gives steady attention to the places where you feel tension, at a pace and pressure that suit you. I check in about pressure in both, and each is described on its own page.'),
 ('Can I Choose Where You Focus?',
  'Yes. Tell me where you would like me to spend the most time, and you can change it at any point during the session.'),
 ('What Should I Tell You Before a Session?',
  'Tell me about any health conditions, medications, pregnancy, recent injury or surgery. I may adjust the work, or suggest you check with your clinician first. New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage.'),
 ('Where Do Sessions Take Place?',
  'Sessions take place in Mt. Tabor, Portland. I do not currently travel to clients.'),
 ('How Do I Book a Session?',
  'Use the Contact page to reach me by phone or email, and tell me what you would like. I will reply to arrange a time.'),
]

# The research paragraph as it is stored on the page today (staging36 and live; the numbers [1]-[4] and their PubMed ids are the page's own).
_OLD_MARKS = {'bingolbali': (1, '36645811'), 'arsovski': (2, '40510040'), 'skillgate': (3, '31655314'), 'koren': (4, '29861215')}
BODY_OLD_STORED = re.sub(r'\{(\w+)\}', lambda m: '<a href="https://pubmed.ncbi.nlm.nih.gov/%s/">[%d]</a>' % (_OLD_MARKS[m.group(1)][1], _OLD_MARKS[m.group(1)][0]), BODY_OLD)

RES_INTRO = ('Research on deep tissue massage is limited, and the studies are hard to compare: a 2018 review found no commonly accepted definition of deep tissue massage, and the studies used different techniques and protocols.{koren} '
             'Many studies are small, results often depend on what massage is compared with, and many follow people for only a few weeks. '
             'The summaries below describe what the studies reported. They are general information, not medical advice or a promise of any result, and people respond differently. '
             'This section covers studies of massage for pain, movement, soreness, recovery and relaxation; studies of massage used for specific medical conditions are not summarized here. '
             'Each part starts with what the studies found in massage’s favor, then gives the results that were weaker, mixed or negative, and ends on what is encouraging: I included the studies I found '
             'with disappointing results, not only the favorable ones.')

PANELS = [
 ('Reviews, and What They Say About Limits', True, [
  'Reviews of massage in general, not only deep tissue massage, reached a consistent point in massage’s favor: every conclusion rated moderate-certainty found massage associated with less pain, and a meta-analysis of '
  '67 randomized trials found lower pain scores after massage than after sham massage, no massage or other approaches.{mak}{crawford} A 2018 review of the research on deep tissue massage itself reported that favorable outcomes '
  'may result in people with pain and in people with decreased range of motion.{koren}',
  'The same reviews are careful about limits. None of the conclusions in the 2024 review was rated high-certainty, and conclusions that massage was better than other active approaches were rare.{mak} An earlier '
  'evidence map of 49 systematic reviews found that the high-quality reviews reached conclusions of low strength of evidence, because few large, rigorous studies had been done.{miake} The meta-analysis’s '
  'authors made a strong recommendation only for the comparison with no massage and a weak one for sham massage and other approaches.{crawford} The 2018 review of deep tissue massage found that the techniques and protocols '
  'used in published studies differed so much that it was difficult to draw any clear conclusions, and its authors asked for future research to use a common definition and common comparison groups.{koren}',
  'Massage is generally described as low risk, but deep pressure deserves a plain account of the rare serious events on record. The U.S. National Center for Complementary and Integrative Health notes that the risk of harm '
  'appears to be low, although rare serious side effects such as a blood clot, nerve injury or bone fracture have been reported.{nccih} The 2018 review of deep tissue massage found several rare serious adverse events '
  'related to it, probably as a result of the forceful application of massage therapy.{koren} Published case reports describe a blocked artery to part of a kidney in a healthy man about four hours after a deep tissue massage '
  'for low back pain given by an unlicensed therapist,{truong} a blood clot in a neck vein after a deep tissue massage focused on the neck,{sanborn} a blood clot in a vein after vigorous deep tissue massage in a previously healthy '
  '67-year-old man, whose authors suggested that vigorous massage of the legs be considered a possible risk factor for such clots, especially in older adults,{crump} and an injury to a nerve that left a shoulder muscle weak '
  'after deep tissue massage.{aksoy} Single case reports show that something happened, not how often. A 2014 review of 40 published reports of massage found 138 adverse events over 11 years, mainly disc herniation, soft-tissue injury, '
  'nerve problems, spinal cord injury and tears in the neck arteries, with spinal manipulation inside a massage repeatedly linked to the serious ones. Massage therapists were the practitioners in only 8 of the 138 events (5.8 percent) '
  'and chiropractors in 33 (23.9 percent); the rest (70.3 percent) involved unregistered people or other health professionals, and the authors concluded that massage by nonprofessionals and forceful techniques is often associated '
  'with adverse events.{yin} A 2003 review of published case reports likewise found that most serious events followed unusual techniques or massage by laypeople rather than by massage therapists, and concluded that serious '
  'events are probably true rarities.{ernst} The U.S. center also advises against using massage to put off seeing a health care provider about a medical problem. Taken together, serious events are rare and, where the reports say who '
  'gave the massage, they were mostly linked to people who were not massage therapists and to forceful or unusual techniques. Deep pressure is never required: before your session I ask you to tell me about health conditions, '
  'medications, pregnancy and recent injury or surgery, and you can ask for lighter pressure or a pause at any time.',
 ]),
 ('Neck, Shoulders and Back', False, [
  'Several trials of deep tissue massage report short-term gains for neck and back pain. In 80 adults with neck pain linked to tender spots in the muscles, one group had 12 sessions of deep tissue massage on top of electrical '
  'stimulation, hot packs and ultrasound, and the other had only those three; the massage group improved more on pain, a neck-disability score and a quality-of-life score, and on neck extension, side bending and turning, but not on '
  'forward bending.{bingolbali} In 64 adults, 60 of whom finished, with chronic low back pain that had no specific cause, tender-point deep tissue massage plus lumbar traction was followed by a higher pressure pain threshold, less muscle hardness and less pain '
  'than traction alone: pain scores fell by 1.9 with massage and 1.4 with traction alone.{zheng} In 26 adults aged 60 to 75 with chronic low back pain, ten days of deep tissue massage and ten days of therapeutic massage both '
  'improved pain and disability scores, and deep tissue massage did better on one disability score and on pain.{romanowski} In 40 office workers with chronic low back pain, four 45-minute deep tissue massage sessions over two weeks were '
  'followed by better lower-back movement and lower pain ratings in the massage group, while the group given no therapy showed no significant changes.{wendt} A 2015 Cochrane review of 25 trials found that massage was better than no '
  'intervention for low-back pain in the short term.{cochrane_lbp}',
  'The larger and more carefully designed studies are more cautious. In the Stockholm Neck trial of 619 adults with ongoing neck pain, deep tissue massage led to a higher chance of improvement in pain than advice to stay active in the '
  'short term but not at later follow-ups, and at 12 months none of the therapies tested was more effective than advice for pain intensity or for pain-related disability.{skillgate} A cost analysis of the same trial found that massage '
  'and the combined therapy cost more and were less beneficial than advice, that differences in health-related quality of life were minimal, and its authors concluded that massage and the combined therapy are not cost-effective.{picoespinosa} '
  'The 2024 Cochrane review of massage for neck pain, which included 33 studies with 1,994 people, found that, compared with a placebo massage, massage probably makes little or no difference to pain, function or quality of life at up to '
  '12 weeks (low-certainty evidence), and adverse events were too poorly reported to judge.{cochrane_neck} The 2015 Cochrane review of low-back pain rated the quality of the evidence low to very low, found no benefit over the long term, '
  'and its authors said they had “very little confidence” in the results.{cochrane_lbp} In 59 adults with chronic low back pain who had two weeks of deep tissue massage with or without a nonsteroidal anti-inflammatory drug, pain and function '
  'improved in both groups with no difference between them; because everyone had massage, the trial cannot show how much of the change was due to the massage itself.{majchrzycki} In the office-worker trial, the two groups differed '
  'significantly in only one lower-back movement, and the authors said larger studies and an assessment of long-term effects are needed.{wendt} The small trial of 26 older adults ended by saying that further research is needed.{romanowski}',
  'Still, people in the Stockholm Neck trial who had massage, exercise or both were more likely than those given advice to say they had recovered, at every follow-up through one year.{skillgate} A subgroup analysis in the 2024 Cochrane '
  'review favored higher doses (eight or more sessions of at least 30 minutes over four weeks), which suggests that regular sessions may matter.{cochrane_neck}',
 ]),
 ('Exercise, Soreness and Recovery', False, [
  'Deep massage has shown benefit in a laboratory model of muscle soreness: in a double-blinded randomized trial in volunteers whose forearm muscles were made sore with exercise, deep tissue massage reduced pain during a muscle stretch '
  '(about a 48 percent reversal of the soreness), and both deep massage and light touch reduced sensitivity to pressure compared with a group that had no massage.{freylaw} A 2020 review of 29 randomized studies with 1,012 people found '
  'small improvements in flexibility and in delayed-onset muscle soreness after sports massage.{davis} A 2025 trial of 150 athletes who received bi-weekly 40-minute deep tissue massage for 8 weeks reported the greatest performance '
  'improvement in team and strength athletes, better recovery outcomes with bi-weekly sessions, and more flexibility with longer sessions.{arsovski}',
  'Several studies found little or nothing. In the soreness trial, resting pain did not differ between the groups.{freylaw} In 29 healthy volunteers, ten minutes of deep massage to the calf produced no change in calf muscle stiffness or ankle movement, and the authors concluded that using massage to increase tissue '
  'flexibility before activity is not justified.{thomson} In 9 people who did a hard cycling effort, deep massage, superficial massage and stretching did not change blood lactate levels compared with passive rest, and the authors concluded that '
  'the pressure used did not play a significant role.{ce} In 19 competitive swimmers aged 12 to 20, deep massage and superficial (light-touch) massage between training sessions had no effect on sprint or functional performance, and the swimmers reported '
  'worse feelings of heaviness and pain at the main stages of swim training although they also reported less tiredness; the authors suggested the swimmers were able to train harder with no harm to recovery.{carvalho} The 2020 review '
  'found no evidence that massage improved strength, jumping, sprinting, endurance or fatigue.{davis} A 2016 review of 22 trials concluded that the effects of massage on performance recovery are rather small and partly unclear, and its '
  'authors questioned whether the limited effects justify using massage as a recovery method in competitive athletes.{poppendieck} In people with sore leg muscles after exercise, manual massage, real diathermy (a heating device) and sham '
  'diathermy produced no clinically relevant differences in pain scores right after the session, although scores on a patient-rated scale of global change did differ between the groups.{visconti} The 2025 athlete trial’s abstract '
  'describes no comparison group, and it measured outcomes with both objective and subjective assessments.{arsovski}',
  'Where massage has been tested for short-term recovery after intense mixed training, the effects were medium in size: the 2016 review found medium positive effects after high-intensity mixed exercise, and shorter massage (5 to 12 '
  'minutes) tended to show larger effects than longer massage.{poppendieck}',
 ]),
 ('Relaxation and the Role of Pressure', False, [
  'Several studies point to short-term relaxation after massage. A 2004 meta-analysis of 37 randomized studies found that a single massage was followed by lower state anxiety, blood pressure and heart rate.{moyer04} In 263 volunteers with '
  'moderate or severe muscle spasm or strain who had a 45 to 60 minute deep tissue massage, average heart rate was 10.8 beats per minute lower and diastolic blood pressure 5.3 mm Hg lower afterward.{kaye} In 20 healthy adults, a reflex '
  'response in the calf muscle was weaker during massage than at rest, and weaker still during deep massage than during light massage; the study measured a laboratory reflex, not pain or movement.{goldberg} In 60 healthy women, the two massage groups showed '
  'higher heart-rate variability than a resting group.{meier}',
  'The picture is mixed, though. The same 2004 meta-analysis found no lower negative mood, cortisol or less immediate pain after a single massage.{moyer04} In the 263-volunteer study, the lower systolic and mean arterial pressure did not clearly '
  'reach statistical significance, the abstract describes no comparison group, and its authors said additional research is warranted.{kaye} In the 60 healthy women, relaxation rose and stress fell in the resting group too.{meier}',
  'Courses of massage have the more encouraging results: the largest effects in the 2004 meta-analysis came after a course of sessions rather than a single one, on measures of anxiety and mood.{moyer04} In a pilot study of 54 adults aged 60 and '
  'over, twice-weekly massage for four weeks was followed by better scores for well-being and perceived stress than guided relaxation.{sharpe}',
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
    # Numbering is by first use, in reading order: the body paragraph, then the research intro and the panels (the FAQ carries no sources).
    body_new = cite.mark(BODY_NEW)
    intro = cite.mark(RES_INTRO)

    faq_attrs = {'block_id': ID_FAQ, 'borderStyle': '', 'borderWidth': '', 'borderRadius': '', 'borderColor': '',
                 'overallBorderTopWidth': 1, 'overallBorderLeftWidth': 1, 'overallBorderRightWidth': 1, 'overallBorderBottomWidth': 1,
                 'overallBorderTopLeftRadius': 2, 'overallBorderTopRightRadius': 2, 'overallBorderBottomLeftRadius': 2, 'overallBorderBottomRightRadius': 2,
                 'overallBorderStyle': 'solid', 'overallBorderColor': '#D2D2D2'}   # the same settings as the Thai, Therapeutic and About accordions
    faq = ('<!-- wp:uagb/faq %s -->\n'
           '<div class="wp-block-uagb-faq uagb-faq__outer-wrap uagb-block-%s uagb-faq-icon-row uagb-faq-layout-accordion uagb-faq-expand-first-true '
           'uagb-faq-inactive-other-true uagb-faq-equal-height" data-faqtoggle="true" role="tablist">%s</div>\n<!-- /wp:uagb/faq -->') % (
        attrs(faq_attrs), ID_FAQ, '\n\n'.join(faq_child(ID_FAQ_CHILD[i], q, a) for i, (q, a) in enumerate(FAQ)))

    panels = []
    for title, opened, paras in PANELS:
        panels.append(details(title, opened, [para(cite.mark(p), plain=True) for p in paras]))
    items = []
    for n, key in enumerate(cite.order, 1):
        url, authors, title, venue = SRC[key]
        items.append('<!-- wp:list-item -->\n<li><cite class="ts-source">%s <a href="%s">%s</a>. %s</cite></li>\n<!-- /wp:list-item -->' % (esc(authors), url, esc(title), esc(venue)))
    sources_list = '<!-- wp:list {"ordered":true,"style":{"typography":{"fontWeight":"400"}}} -->\n<ol class="wp-block-list" style="font-weight:400">%s</ol>\n<!-- /wp:list -->' % '\n\n'.join(items)
    panels.append(details('Sources', False, [para(SOURCES_NOTE, plain=True), sources_list]))

    # The stored page ends with the last block and NO newline (read from the database), so the appended text starts with the blank line that separates blocks.
    sections = '\n\n' + '\n\n'.join([
        heading(ID_FAQ_HEAD, 'Questions About Deep Tissue Massage'), faq,
        heading(ID_RES_HEAD, 'What the Research Says About Deep Tissue Massage'), para(intro), '\n\n'.join(panels),
    ]) + '\n'

    unused = [k for k in SRC if k not in cite.order]
    if unused:
        raise SystemExit('registry has sources the text never cites: %s' % unused)

    images = [{'id': block_id, 'alt': alt, 'old_title': OLD_TITLE, 'new_title': alt} for block_id, alt in IMAGES]
    return {
        'ids': [ID_FAQ_HEAD, ID_RES_HEAD, ID_FAQ] + ID_FAQ_CHILD,
        'body_old': BODY_OLD_STORED, 'body_new': body_new,
        'images': images,
        'tail': '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container --></div></div>\n<!-- /wp:uagb/container -->',
        'sections': sections,
        'research_start': '<!-- wp:uagb/advanced-heading %s -->' % (tb.HEAD_ATTRS % ID_RES_HEAD),
        'source_count': len(cite.order),
        'source_urls': [SRC[k][0] for k in cite.order],
        'faq_questions': [q for q, _ in FAQ],
        'faq_text': ' '.join(q + ' ' + a for q, a in FAQ),   # the FAQ lives in block attributes, which a tag-stripper cannot see; the installer's wording check reads this copy
        'image_text': ' '.join(alt for _, alt in IMAGES),     # same for the alt texts and tooltips
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'DEEPJSON' not in js
    return ('<?php\n// GENERATED by build-deep-tissue-evidence.py: do not edit by hand (edit the builder and run it again). install-deep-tissue-evidence.php loads this.\n'
            'return json_decode( <<<\'DEEPJSON\'\n%s\nDEEPJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        import re
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['body_new'])))
        print()
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['sections'])))
        sys.exit(0)
    body = php(data)
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('deep-tissue-evidence-content.php matches the builder' if have == body else 'deep-tissue-evidence-content.php is OUT OF DATE: run the builder')
        sys.exit(0 if have == body else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(body)
    print('wrote %s (%d bytes); %d numbered sources' % (OUT, len(body.encode('utf-8')), data['source_count']))

# ref: ../thai/build-thai-evidence.py — the Thai builder whose helpers this imports (and which it must never change); ../therapeutic/build-therapeutic-evidence.py — the shared source entries
# ref: ./install-deep-tissue-evidence.php — loads the generated file; ../../../docs/inter-model/deep-tissue-massage-page-enhancement-plan.md — the approved plan

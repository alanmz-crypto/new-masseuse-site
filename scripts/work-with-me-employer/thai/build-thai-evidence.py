#!/usr/bin/env python3
"""Build thai-evidence-content.php: the exact text and block markup that install-thai-evidence.php puts on the Thai Massage page (ID 1160). Standard library only.

    python3 -I -B scripts/work-with-me-employer/thai/build-thai-evidence.py          write thai-evidence-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/thai/build-thai-evidence.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/thai/build-thai-evidence.py --text   print the visible text (for review)

SOURCE OF THE REQUEST (Ryan, 2026-10-19, after the plan in docs/inter-model/thai-massage-page-enhancement-plan.md): "You should add all the evidence you can. No new images.
Keep the FAQ generic enough we can finish it later. She uses a mat. Lengths 60 and 90 min. She doesn't have plans to travel to clients. If something is on my banned words
list but evidence says those words should be used, then update my banned words to accept it."

WHAT IS ADDED (nothing already on the page is moved or reworded, apart from one added paragraph):
  1. Heritage: one paragraph at the end of "What Is Thai Massage?" (UNESCO 2019 listing, source [1]; her training, which the About page already states).
  2. "Questions About Thai Massage": a Spectra FAQ accordion like the Therapeutic page's, 7 questions. ANSWERS CARRY NO LINKS: Spectra wraps a whole question and its answer in role="tab",
     so a link inside an answer is an axe "nested-interactive" failure (the known For Practices item). Facts: mat (Ryan), 60 or 90 minutes (Ryan), no travel to clients (Ryan); everything else restates the page.
  3. "What the Research Says About Thai Massage": an intro and five native WordPress "details" panels (keyboard- and screen-reader-native, links allowed inside): reviews and limits; back pain; neck, shoulders,
     arms and feet; stress, energy and recovery; Sources. 31 numbered sources, numbered in reading order by the registry below so the markers and the list cannot drift apart.
EVERY SENTENCE was checked against the paper's own abstract (Europe PMC / Cochrane / NCCIH / UNESCO pages, 2026-10-19). Studies of Thai massage as a treatment for a disease (headache disorders, knee
osteoarthritis against ibuprofen, cerebral palsy, autism, stroke, cancer, urinary symptoms, constipation, bone density, immunity) are NOT used: that would make the page read as promising to treat illness
(OAR 334-010-0025(2)(c) and (4); 334-040-0010(7)). Unfavourable and mixed results ARE used, so the summary stays honest.
Yoopat 2015 (30 office workers) is now quoted in the authors' own words, because its abstract is ambiguous about what was compared (the audit of 2026-10-19: a null shoulder-strength result belongs on the page).

DIAGNOSIS VOCABULARY (OAR 334-010-0025(2)(c): massage does not include making a medical diagnosis; the footer says she does not diagnose): study populations are described in plain words in the prose
("shoulder-blade pain", "tender spots in the muscles"), not by clinical label (scapulocostal syndrome, myofascial, trigger points). The Sources panel quotes titles exactly as published.

WORDING RULES (AGENTS.md, as updated 2026-10-19): first person for the practice, never we/our/us; the claim verbs (cure, treat, heal, diagnose, guarantee, detox, inflammation) never appear in prose; the
OBMT-derived "evidence vocabulary" (chronic, anxiety, disability, effective...) is accepted only inside the research section; verbatim paper titles in the Sources panel are accepted as quoted.
"""
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'thai-evidence-content.php')

# ---- block ids (8 hex characters; install-thai-evidence.php refuses any that already occur in another post) ----------------------------------
ID_FAQ_HEAD, ID_RES_HEAD, ID_FAQ = '3b8d5f60', '7e4a91c2', 'c91e27a4'
ID_FAQ_CHILD = ['5a3f8e12', 'd7b04c69', '2e9c61fa', '8f15a3d7', '41c7e0b5', 'b63d92e8', '9a2f5c04']

# Same settings as the "What To Expect During Your Appointment" heading on this page (centered, separator above), only the id differs.
HEAD_ATTRS = ('{"block_id":"%s","classMigrate":true,"headingDescToggle":false,"headingDescPosition":"above-heading","headingAlign":"center","separatorColor":"var(--ast-global-color-0)",'
              '"seperatorStyle":"solid","seperatorPosition":"above-heading","separatorWidth":64,"separatorWidthType":"px","headSpace":0,"separatorSpace":14,"blockTopPadding":100,'
              '"blockRightPadding":220,"blockLeftPadding":220,"blockBottomPadding":28,"blockTopPaddingTablet":80,"blockRightPaddingTablet":40,"blockLeftPaddingTablet":40,'
              '"blockBottomPaddingTablet":24,"blockTopPaddingMobile":60,"blockRightPaddingMobile":0,"blockLeftPaddingMobile":0,"blockBottomPaddingMobile":20}')

# ---- source registry: key -> (url, authors, title (verbatim), venue and year) ------------------------------------------------------------------
PM = 'https://pubmed.ncbi.nlm.nih.gov/%s/'
SRC = {
    'unesco': ('https://ich.unesco.org/en/RL/nuad-thai-traditional-thai-massage-01384', 'UNESCO Intangible Cultural Heritage.', 'Nuad Thai, traditional Thai massage', 'Representative List of the Intangible Cultural Heritage of Humanity, inscribed 2019.'),
    'keeratitanont': (PM % '25682523', 'Keeratitanont K, Jensen MP, Chatchawan U, Auvichayapat P.', 'The efficacy of traditional Thai massage for the treatment of chronic pain: A systematic review', 'Complement Ther Clin Pract, 2015.'),
    'netchanok': (PM % '23059437', 'Netchanok S, Wendy M, Marie C, Siobhan O.', 'The effectiveness of Swedish massage and traditional Thai massage in treating chronic low back pain: a review of the literature', 'Complement Ther Clin Pract, 2012.'),
    'cochrane_lbp': ('https://www.cochrane.org/evidence/CD001929_massage-low-back-pain', 'Furlan AD, Giraldo M, Baskwill A, Irvin E, Imamura M.', 'Massage for low-back pain', 'Cochrane Database of Systematic Reviews, 2015, issue 9, CD001929.'),
    'cochrane_neck': ('https://www.cochrane.org/evidence/CD004871_massage-neck-pain', 'Cochrane.', 'Massage for neck pain', 'Cochrane evidence summary, 2024, CD004871 (searches to October 2023).'),
    'nccih': ('https://www.nccih.nih.gov/health/massage-therapy-what-you-need-to-know', 'National Center for Complementary and Integrative Health (NIH).', 'Massage Therapy: What You Need To Know', 'Last updated May 2019.'),
    'juntakarn': (PM % '28690703', 'Juntakarn C, Prasartritha T, Petrakard P.', 'The Effectiveness of Thai Massage and Joint Mobilization', 'Int J Ther Massage Bodywork, 2017.'),
    'laosee': (PM % '31987237', 'Laosee O, Sritoomma N, Wamontree P, Rattanapan C, Sitthi-Amorn C.', 'The effectiveness of traditional Thai massage versus massage with herbal compress among elderly patients with low back pain: A randomised controlled trial', 'Complement Ther Med, 2020.'),
    'siriyong': (PM % '42142427', 'Siriyong T, Mangkang J, Yotmadee P.', 'Clinical efficacy of court-type traditional Thai massage and acupuncture for low back pain management: a randomized controlled trial', 'Explore (NY), 2026.'),
    'chatchawan': ('https://doi.org/10.1016/j.jbmt.2005.02.001', 'Chatchawan U, Thinkhamrop B, Kharmwan S, Knowles J, Eungpinichpong W.', 'Effectiveness of traditional Thai massage versus Swedish massage among patients with back pain associated with myofascial trigger points', 'J Bodyw Mov Ther, 2005.'),
    'kumnerddee': (PM % '21299184', 'Kumnerddee W.', 'Effectiveness comparison between Thai traditional massage and Chinese acupuncture for myofascial back pain in Thai military personnel: a preliminary report', 'J Med Assoc Thai, 2009.'),
    'buttagat2016': (PM % '27670864', 'Buttagat V, Narktro T, Onsrira K, Pobsamai C.', 'Short-term effects of traditional Thai massage on electromyogram, muscle tension and pain among patients with upper back pain associated with myofascial trigger points', 'Complement Ther Med, 2016.'),
    'buttagat2011': (PM % '21147414', 'Buttagat V, Eungpinichpong W, Chatchawan U, Kharmwan S.', 'The immediate effects of traditional Thai massage on heart rate variability and stress-related parameters in patients with back pain associated with myofascial trigger points', 'J Bodyw Mov Ther, 2011.'),
    'buttagat2021': (PM % '34391301', 'Buttagat V, Muenpan K, Wiriyasakunphan W, Pomsuwan S, Kluayhomthong S, Areeudomwong P.', 'A comparative study of Thai massage and muscle energy technique for chronic neck pain: A single-blinded randomized clinical trial', 'J Bodyw Mov Ther, 2021.'),
    'areeudomwong': (PM % '35576853', 'Areeudomwong P, Nakrit R, Seephung T, Ketsawad A, Buttagat V.', 'A randomized comparative study of traditional Thai massage and Thai boxing exercise on clinical-based outcomes in patients with scapulocostal syndrome', 'Complement Ther Clin Pract, 2022.'),
    'boonruab': (PM % '34293959', 'Boonruab J, Poonsuk P, Damjuti W, Supamethangkura W.', 'Myofascial Pain Syndrome Focused on the Upper Trapezius Muscle: A Comparative Randomized Controlled Trial of the Court-Type Traditional Thai Massage versus the Thai Hermit', 'J Evid Based Integr Med, 2021.'),
    'buttagat2012': (PM % '22196428', 'Buttagat V, Eungpinichpong W, Chatchawan U, Arayawichanon P.', 'Therapeutic effects of traditional Thai massage on pain, muscle tension and anxiety in patients with scapulocostal syndrome: a randomized single-blinded pilot study', 'J Bodyw Mov Ther, 2012.'),
    'buttagat2024w': (PM % '39593435', 'Buttagat V, Kluayhomthong S, Areeudomwong P.', 'The beneficial effects of traditional Thai massage on young patients with latent myofascial trigger points in the wrist extensor muscles: A randomized controlled trial', 'J Bodyw Mov Ther, 2024.'),
    'somphai': (PM % '39905464', 'Somphai S, Sucharit W, Peungsuwan P, Roberts N, Chatchawan U.', 'Randomised control trial to compare the efficacy of traditional Thai massage and ultrasound therapy for treating plantar heel pain', 'BMC Complement Med Ther, 2025.'),
    'fhp': (PM % '37605282', 'Buttagat V, Kluayhomthong S, Areeudomwong P.', 'Effects of the combination of traditional Thai massage, scapular stabilization exercise, and chest mobilization in subjects with forward head posture: a single-blinded randomized clinical trial', 'Chiropr Man Therap, 2023.'),
    'sripongngam_msm': (PM % '26436433', 'Sripongngam T, Eungpinichpong W, Sirivongs D, Kanpittaya J, Tangvoraphonkchai K, Chanaboon S.', 'Immediate Effects of Traditional Thai Massage on Psychological Stress as Indicated by Salivary Alpha-Amylase Levels in Healthy Persons', 'Med Sci Monit Basic Res, 2015.'),
    'sripongngam_jmat': (PM % '26387408', 'Sripongngam T, Eungpinichpong W, et al.', 'Psychological Stress Can Be Decreased by Traditional Thai Massage', 'J Med Assoc Thai, 2015.'),
    'bennett': (PM % '27210845', 'Bennett S, Bennett MJ, Chatchawan U, et al.', 'Acute effects of traditional Thai massage on cortisol levels, arterial blood pressure and stress perception in academic stress condition: A single blind randomised controlled trial', 'J Bodyw Mov Ther, 2016.'),
    'macsween': (PM % '30100318', 'MacSween A, Lorrimer S, van Schaik P, Holmes M, van Wersch A.', 'A randomised crossover trial comparing Thai and Swedish massage for fatigue and depleted energy', 'J Bodyw Mov Ther, 2018.'),
    'yin': (PM % '25197310', 'Yin P, Gao N, Wu J, Litscher G, Xu S.', 'Adverse events of massage therapy in pain-related conditions: a systematic review', 'Evid Based Complement Alternat Med, 2014.'),
    'ernst': (PM % '12777645', 'Ernst E.', 'The safety of massage therapy', 'Rheumatology (Oxford), 2003.'),
    'hematoma': (PM % '30691861', 'Vanichkulbodee A, Issaragrisil S, Inboriboon PC.', 'Massage-induced spinal epidural hematoma presenting with delayed paraplegia', 'Am J Emerg Med, 2019.'),
    'wiwanitkit': (PM % '26865746', 'Wiwanitkit V.', 'Thai traditional massage: Issues causing possible adverse effects', 'Anc Sci Life, 2015.'),
    'hongsuwan': (PM % '25729203', 'Hongsuwan C, Eungpinichpong W, Chatchawan U, Yamauchi J.', 'Effects of Thai massage on physical fitness in soccer players', 'J Phys Ther Sci, 2015.'),
    'yoopat': (PM % '25892379', 'Yoopat P, Maes C, Poriau S, Vanwonterghem K.', 'Thai traditional massage: efficiency-assessment of three traditional massage methods on office workers: an explorative study', 'J Bodyw Mov Ther, 2015.'),
    'buttagat2024g': (PM % '38796061', 'Buttagat V, Kluayhomthong S, Areeudomwong P.', 'The influence of traditional Thai massage on recovery from gastrocnemius muscle fatigue: A single-blind randomised controlled trial', 'Complement Ther Med, 2024.'),
}

HERITAGE = ('Traditional Thai massage traces its roots to everyday self-care in Thai villages, and in 2019 UNESCO added Nuad Thai, traditional Thai massage, to its Representative List of the '
            'Intangible Cultural Heritage of Humanity.{unesco} I trained in traditional Thai bodywork at Still Light Center.')

FAQ = [
    ('What Should I Wear to a Thai Massage?',
     'You stay fully clothed, so wear something loose and comfortable that lets you move easily.'),
    ('What Will I Lie On?',
     'Thai massage is done on a mat. I guide each movement and check in with you as I go.'),
    ('How Long Is a Session?',
     'Sessions are 60 or 90 minutes. If you are not sure which to choose, tell me what you would like from the session and I will help you decide.'),
    ('Do I Need to Be Flexible?',
     'No. I set the range of each stretch by what is comfortable for you, and I never force one. You can ask me to change or skip any movement.'),
    ('Where Do Sessions Take Place?',
     'Sessions take place in Mt. Tabor, Portland. I do not currently travel to clients.'),
    ('How Is Thai Massage Different From Your Other Services?',
     'Thai massage is done fully clothed on a mat and combines steady pressure and gentle rocking with assisted stretching. Therapeutic Massage and Deep Tissue Massage are described on their own pages.'),
    ('How Do I Book a Session?',
     'Use the Contact page to reach me by phone or email, and tell me what you would like. I will reply to arrange a time.'),
]

RES_INTRO = ('Research on traditional Thai massage is still limited. Most studies are small, many compare Thai massage with another approach rather than with no massage, and most follow people for '
             'only a few weeks. The summaries below describe what the studies reported. They are general information, not medical advice or a promise of any result, and people respond differently. '
             'This section covers studies of massage for pain, movement, stress, energy and recovery; studies of massage used for specific medical conditions are not summarized here. '
             'Each part starts with what the studies found in massage’s favor, then gives the results that were weaker, mixed or negative, and ends on what is encouraging: I included the studies I found '
             'with disappointing results, not only the favorable ones.')

# Ryan, 2026-10-19 ("make sure I didn't miss any discouraging or poor results ... so we can sandwich the discouraging results between two assertive positive claims", then "you can sandwich with the method you
# used on the Therapeutic page"): every panel now runs positive findings, then the weaker, mixed and negative ones IN FULL, then a positive close. Every claim on each side is sourced and hedged as its source hedges it.
# New in this version (found by the audit): a safety review, one Thai-massage case report of a serious injury and one commentary, a trial with a null shoulder-strength result, and a favorable soccer-player trial.
PANELS = [
    ('Reviews, and What They Say About Limits', True, [
        'A 2015 systematic review of six studies of traditional Thai massage in people with long-lasting pain found that every study reported less pain after a course of sessions, with the reduction '
        'appearing to last for up to 15 weeks.{keeratitanont} A 2012 review of six studies of Swedish and Thai massage for low-back pain reported that the two appeared to be about equally helpful.{netchanok}',
        'The reviews also say what they could not show. The 2015 review’s authors said more research is needed to learn how Thai massage compares with control conditions over the longer term, and the 2012 review '
        'noted that weaknesses in the studies mean more direct comparisons are needed. Reviews of massage in general are cautious, too. A 2015 Cochrane review of 25 trials with 3,096 people with low-back pain found '
        'some short-term benefits, but the studies were small and of low quality, and its authors said they had “very little confidence” in the results.{cochrane_lbp} A 2024 Cochrane review of 33 studies with '
        '1,994 people with neck pain found that massage probably made little or no difference compared with a placebo massage after about 12 weeks (low-certainty evidence).{cochrane_neck}',
        'That neck review also found that higher doses, meaning eight or more sessions of at least 30 minutes over four weeks, favored massage in a subgroup analysis, which suggests that regular sessions may matter.{cochrane_neck}',
        'Massage is generally described as low risk, and where serious problems on record can be traced to who gave the massage, they are mostly tied to people who were not trained massage therapists. The U.S. '
        'National Center for Complementary and Integrative Health notes that the risk of harm appears to be low, although rare serious side effects such as a blood clot, nerve injury or bone fracture have been '
        'reported.{nccih} A 2014 review of 40 published reports found 138 adverse events over 11 years, mainly disc herniation, soft-tissue injury, nerve problems, spinal cord injury and tears in the neck arteries, '
        'with spinal manipulation inside a massage repeatedly linked to the serious ones. Massage therapists were the practitioners in only 8 of the 138 events (5.8 percent) and chiropractors in 33 (23.9 percent); '
        'the rest (70.3 percent) involved unregistered people or other health professionals, and the authors concluded that massage by nonprofessionals and forceful techniques is often associated with adverse '
        'events.{yin} A 2003 review of published case reports likewise found that most serious events followed unusual techniques or massage by laypeople rather than by massage therapists, and concluded that serious '
        'events are probably true rarities.{ernst} For Thai massage specifically, a 2019 case report described a 20-year-old man who developed bleeding around the spinal cord (a spinal epidural hematoma) with '
        'delayed leg weakness three days after a Thai massage given by a layperson, a friend, and who needed emergency surgery.{hematoma} A 2015 commentary also discussed a patient who had a heart attack around '
        'the time of a Thai massage and the possible adverse effects of Thai massage.{wiwanitkit} The U.S. center also advises against using massage to put off seeing a health care provider about a medical '
        'problem. Taken together, in the reports that say who gave the massage, trained massage therapists were rarely involved: the serious injuries were mostly linked to people who were not massage therapists, '
        'including laypeople and unregistered practitioners, and to forceful or unusual techniques. Serious events are rare: I ask about your health, medications and recent injuries before each session, and I set '
        'the pressure and the range of each stretch by what is comfortable for you.',
    ]),
    ('Back Pain', False, [
        'Two trials with a comparison that was not another massage reported clear differences. In 50 adults with upper-back pain, one 30-minute session of Thai massage was followed by larger reductions in pain, '
        'muscle tension and muscle activity than a sham device.{buttagat2016} In 36 adults with back pain, one 30-minute session was followed by lower self-reported pain, anxiety and muscle tension and better '
        'flexibility than resting on a bed.{buttagat2011}',
        'Trials that compared Thai massage with another approach found similar results rather than a winner. In a hospital trial in Bangkok, 120 adults had Thai massage or joint mobilization twice a week for four '
        'weeks, and both groups improved by similar amounts on pain and everyday-activity scores. The trial had no comparison group that received neither, so it cannot show how much of the change was due to the '
        'sessions themselves.{juntakarn} In 140 older adults, Thai massage alone and Thai massage with an herbal compress led to similar improvements over 15 weeks, so the compress added no clear benefit.{laosee} '
        'In another trial of 140 adults, adding acupuncture to court-type Thai massage made no clear difference after one month, and both groups reported less pain and more movement.{siriyong} A preliminary trial '
        'of 18 military personnel with back pain found less pain after both Thai massage and acupuncture, with one pain-questionnaire score lower after acupuncture.{kumnerddee}',
        'An earlier trial that compared Thai and Swedish massage for back pain with tender spots in the muscles found that pain fell by more than half in both groups after three weeks and stayed lower for up to a '
        'month, with no significant difference between them.{chatchawan}',
    ]),
    ('Neck, Shoulders, Arms and Feet', False, [
        'In 45 adults with neck pain, eight sessions of Thai massage or of a muscle energy technique over two weeks were each followed by more improvement in pain and neck movement than in a control group.{buttagat2021} '
        'In 78 adults with shoulder-blade pain, four weeks of Thai massage or Thai boxing exercise both reduced pain, and Thai massage did better on several measures, including pain and neck movement, at the end of '
        'the program and at one month.{areeudomwong} In 46 adults with upper-shoulder pain, court-type Thai massage and a Thai hermit exercise routine both improved neck movement and pain, and the massage group '
        'reported a clearer reduction in pain.{boonruab} In 62 young adults with tender spots in the forearm muscles, nine short sessions of Thai massage were followed by greater improvement in pain, tenderness '
        'and wrist movement than sham ultrasound.{buttagat2024w}',
        'Not every comparison favored Thai massage. In the neck trial there was no clear difference between Thai massage and the muscle energy technique. In 60 people with heel pain, both Thai massage and ultrasound '
        'were followed by less pain, but ultrasound with stretching produced larger gains in pain and ankle movement.{somphai} In 48 adults with a forward head posture, a four-week program that combined Thai '
        'massage with shoulder-blade exercises and chest mobilization improved head position and neck movement more than lying at rest. Because three approaches were combined, the trial cannot show what Thai '
        'massage added by itself.{fhp} In 30 healthy female office workers who each tried three styles of Thai massage, the authors reported that “strength in shoulders was not significantly different, but some '
        'forearm fatigue was decreased significantly among the three massage techniques.”{yoopat}',
        'A pilot trial of 20 people with shoulder-blade pain found lower pain and muscle tension after Thai massage than after a physical-therapy package of ultrasound and a hot pack.{buttagat2012}',
    ]),
    ('Stress, Energy and Recovery', False, [
        'In 54 healthy adults whose calf muscles were tired by heel-raise exercise, 30 minutes of Thai massage was followed by faster recovery on muscle measures than 30 minutes of mostly rest.{buttagat2024g} '
        'In 34 soccer players who were given Thai massage or rest, all seven fitness tests (sit-and-reach, hand grip strength, agility, a 50-meter sprint, sit-ups, push-ups and a test of aerobic fitness) improved '
        'in the massage group, compared with two in the rest group.{hongsuwan}',
        'The results on stress are mixed. Two small trials in healthy adults first raised stress with a mental-arithmetic test and then gave either one hour of Thai massage or one hour of rest. In both trials, '
        'stress measures fell after massage and after rest. In one trial (29 people, each trying both), one marker, salivary amylase, fell more after Thai massage; in the other (30 people), no measure differed '
        'between the groups.{sripongngam_msm}{sripongngam_jmat} In 36 physiotherapy students under academic stress, saliva cortisol and heart rate fell after one Thai massage session and in a control group, with '
        'no significant difference in cortisol between the groups.{bennett} In a small crossover trial, 20 adults who felt tired or low on energy had three weekly sessions of Thai massage and three of Swedish '
        'massage, and Swedish massage was described as having the larger effect on relaxation and sleep.{macsween}',
        'Even so, the results point the same encouraging way on the outcomes that matter to many people: a marker of stress, salivary amylase, fell more after Thai massage than after rest in one trial,{sripongngam_msm} '
        'and in the trial of tired adults Thai massage was described as the more energizing of the two styles.{macsween}',
    ]),
]


# ---- helpers -------------------------------------------------------------------------------------------------------------------------------------
def attrs(d):
    """Serialise block attributes exactly like WordPress's serialize_block_attributes()."""
    s = json.dumps(d, ensure_ascii=False, separators=(',', ':'))
    for a, b in (('--', '\\u002d\\u002d'), ('<', '\\u003c'), ('>', '\\u003e'), ('&', '\\u0026'), ('\\"', '\\u0022')):
        s = s.replace(a, b)
    return s


def esc(t):
    return t.replace('&', '&amp;').replace('<', '&lt;').replace('>', '&gt;')


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


def para(html, plain=False):
    """plain=True: normal weight inside a panel whose summary is semi-bold (the panel's font-weight would otherwise be inherited by the text)."""
    if plain:
        return '<!-- wp:paragraph {"style":{"typography":{"fontWeight":"400"}}} -->\n<p style="font-weight:400">%s</p>\n<!-- /wp:paragraph -->' % html
    return '<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->' % html


def heading(block_id, text):
    return ('<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><div class="uagb-separator"></div>'
            '<h2 class="uagb-heading-text">%s</h2></div>\n<!-- /wp:uagb/advanced-heading -->') % (HEAD_ATTRS % block_id, block_id, esc(text))


def faq_child(block_id, question, answer):
    if '<a ' in answer or 'href' in answer:
        raise SystemExit('FAQ answers must not contain links (Spectra wraps question and answer in role="tab"): ' + question)
    return '<!-- wp:uagb/faq-child %s /-->' % attrs({'block_id': block_id, 'question': question, 'answer': answer, 'icon': 'chevron-up', 'iconActive': 'chevron-down'})


# Panel look: the same card as the FAQ items (the site's --ts-panel fill and --ts-rule line, 4 px corners), written as block attributes so it lives in the page content.
PANEL_STYLE = {'border': {'width': '1px', 'style': 'solid', 'color': 'var(--ts-rule)', 'radius': '4px'},
               'color': {'background': 'var(--ts-panel)'},
               'spacing': {'padding': {'top': '0.85rem', 'right': '1.1rem', 'bottom': '0.85rem', 'left': '1.1rem'}, 'margin': {'bottom': '0.7rem'}},
               'typography': {'fontWeight': '600'}}
PANEL_CSS = ('border-color:var(--ts-rule);border-width:1px;border-style:solid;border-radius:4px;background-color:var(--ts-panel);'
             'padding-top:0.85rem;padding-right:1.1rem;padding-bottom:0.85rem;padding-left:1.1rem;margin-bottom:0.7rem;font-weight:600')


def details(summary, opened, inner):
    a = {'showContent': True, 'style': PANEL_STYLE} if opened else {'style': PANEL_STYLE}
    return ('<!-- wp:details %s -->\n<details class="wp-block-details has-border-color has-background" style="%s"%s><summary>%s</summary>\n%s</details>\n<!-- /wp:details -->'
            % (attrs(a), PANEL_CSS, ' open' if opened else '', esc(summary), '\n\n'.join(inner) + '\n'))


def build():
    cite = Cite()
    heritage = cite.mark(HERITAGE)

    faq_attrs = {'block_id': ID_FAQ, 'borderStyle': '', 'borderWidth': '', 'borderRadius': '', 'borderColor': '',
                 'overallBorderTopWidth': 1, 'overallBorderLeftWidth': 1, 'overallBorderRightWidth': 1, 'overallBorderBottomWidth': 1,
                 'overallBorderTopLeftRadius': 2, 'overallBorderTopRightRadius': 2, 'overallBorderBottomLeftRadius': 2, 'overallBorderBottomRightRadius': 2,
                 'overallBorderStyle': 'solid', 'overallBorderColor': '#D2D2D2'}   # the same settings as the Therapeutic and About accordions
    faq = ('<!-- wp:uagb/faq %s -->\n'
           '<div class="wp-block-uagb-faq uagb-faq__outer-wrap uagb-block-%s uagb-faq-icon-row uagb-faq-layout-accordion uagb-faq-expand-first-true '
           'uagb-faq-inactive-other-true uagb-faq-equal-height" data-faqtoggle="true" role="tablist">%s</div>\n<!-- /wp:uagb/faq -->') % (
        attrs(faq_attrs), ID_FAQ, '\n\n'.join(faq_child(ID_FAQ_CHILD[i], q, a) for i, (q, a) in enumerate(FAQ)))

    panels = []
    for title, opened, paras in PANELS:
        panels.append(details(title, opened, [para(cite.mark(p), plain=True) for p in paras]))
    # Sources panel last, so every marker above already has its number.
    items = []
    for n, key in enumerate(cite.order, 1):
        url, authors, title, venue = SRC[key]
        items.append('<!-- wp:list-item -->\n<li><cite class="ts-source">%s <a href="%s">%s</a>. %s</cite></li>\n<!-- /wp:list-item -->' % (esc(authors), url, esc(title), esc(venue)))
    sources_list = '<!-- wp:list {"ordered":true,"style":{"typography":{"fontWeight":"400"}}} -->\n<ol class="wp-block-list" style="font-weight:400">%s</ol>\n<!-- /wp:list -->' % '\n\n'.join(items)
    panels.append(details('Sources', False, [para('Each number above links to the source. Titles are quoted as published.', plain=True), sources_list]))

    sections = '\n' + '\n\n'.join([
        heading(ID_FAQ_HEAD, 'Questions About Thai Massage'), faq,
        heading(ID_RES_HEAD, 'What the Research Says About Thai Massage'), para(esc(RES_INTRO)), '\n\n'.join(panels),
    ]) + '\n\n'

    unused = [k for k in SRC if k not in cite.order]
    if unused:
        raise SystemExit('registry has sources the text never cites: %s' % unused)
    return {
        'ids': [ID_FAQ_HEAD, ID_RES_HEAD, ID_FAQ] + ID_FAQ_CHILD,
        'heritage_old': 'so the work can be restful, more active, or a blend of both.</p>',
        'heritage_new': 'so the work can be restful, more active, or a blend of both.<br><br>' + heritage + '</p>',
        'heritage_text': heritage,
        'tail': '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container --></div></div>\n<!-- /wp:uagb/container -->\n',
        'sections': sections,
        'research_start': '<!-- wp:uagb/advanced-heading %s -->' % (HEAD_ATTRS % ID_RES_HEAD),
        'source_count': len(cite.order),
        'source_urls': [SRC[k][0] for k in cite.order],
        'faq_questions': [q for q, _ in FAQ],
        'faq_text': ' '.join(q + ' ' + a for q, a in FAQ),   # the FAQ lives in block attributes, which a tag-stripper cannot see; the installer's wording check reads this copy
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'THAIJSON' not in js
    return ('<?php\n// GENERATED by build-thai-evidence.py: do not edit by hand (edit the builder and run it again). install-thai-evidence.php loads this.\n'
            'return json_decode( <<<\'THAIJSON\'\n%s\nTHAIJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        import re
        print(re.sub(r'<[^>]+>', ' ', data['heritage_new'])[-500:]); print()
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['sections'])))
        sys.exit(0)
    body = php(data)
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('thai-evidence-content.php matches the builder' if have == body else 'thai-evidence-content.php is OUT OF DATE: run the builder')
        sys.exit(0 if have == body else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(body)
    print('wrote %s (%d bytes); %d numbered sources' % (OUT, len(body.encode('utf-8')), data['source_count']))

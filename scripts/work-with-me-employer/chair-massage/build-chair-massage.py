#!/usr/bin/env python3
"""Build chair-massage-content.php: the exact text and block markup that install-chair-massage.php ADDS for the Chair Massage service page. Standard library only.

    python3 -I -B scripts/work-with-me-employer/chair-massage/build-chair-massage.py          write chair-massage-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/chair-massage/build-chair-massage.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/chair-massage/build-chair-massage.py --text   print the visible text (for review)

SOURCE OF THE REQUEST (Ryan, 2026-10-30, cloud session): "look at the services page and the way that all the services pages are designed, such as Thai massage or therapeutic massage
... recreate those pages ... this time the service is chair massage. Do all the needed research for chair massage. Use web research tools. Look for high quality information. Try to use the
sources that you see on the other service pages. This new page should resemble those other pages: the experience should feel seamless." Then: "Don't forget your original attempt at work begins
on staging38." His three decisions the same evening (asked, answered): pictures = his corporate illustration recoloured (hero = the copy already in the media library, four duotone tints as the
section pictures; make-tints.py); the strip and the jump links = add 'chair-massage' to both snippets' lists; the Services page = a Chair Massage row before the Corporate & Group row.

EVERYTHING IS ADDED (Ryan's standing rule, 2026-10-24: "you can add, don't subtract without asking"); the two snippet edits are one-line INSERTIONS into lists, by exact replacement:
  1. a NEW page /chair-massage/ built in the Therapeutic page's own block markup: every block attribute string below was copied from page 1107 as stored on staging38 on 2026-10-31
     (inspect `chair-insp-ther-38`, sha256 1e54f7ff...), only the block ids are new (8 hex characters each, refused by the installer if they already occur in any post) and the
     editor's leftover template comments are not carried over. Pictures: {{IMG_URL_n}} is filled in by the installer from the media library (hero = the corporate illustration
     found by sha256; tints = imported by the installer, found again by their sha256 on a repeat run); no media ids in the markup, so applying again after a rollback gives byte-identical content;
  2. a Services row (the same grid as the three service rows; the green tint; relative links) inserted before the Corporate & Group row, by its block id;
  3. 'chair-massage' appended to the CorporateBand page list and a 'chair-massage' plan line added to A11yPageJumpLinks (both snippets' current code is frozen below: the band from
     booking-date/booking-date-content.php `band_new`, the jump links from accessibility/install-jump-links.php; sha256 of both checked against staging38 on 2026-10-31);
  4. SureRank title and description for the new page.
RESEARCH: every study sentence was written against the paper's own PubMed abstract (read 2026-10-30/31) and then checked by two separate review passes; the eleven sources shared with
the Therapeutic page use that page's registry entries. Chair, seated and brief on-site massage only: studies of mechanical massage chairs, and of massage given to patients as part
of treatment for a condition (an addiction-withdrawal trial, a cancer-outpatient service), are NOT used (AGENTS.md: the research section may say who a study enrolled but must never
read as who she treats). Populations are in plain words; titles verbatim in Sources.
WORDING RULES (AGENTS.md, wording_tiers.py): first person, never we/our/us; the claim verbs never in prose; the OBMT evidence vocabulary (anxiety, chronic, effective, relief...) only
inside the research section ("What the Research Says About Chair Massage" to the end of the page); this builder refuses to write the file if those rules are broken.
"""
import hashlib
import html as html_mod
import importlib.util
import json
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'chair-massage-content.php')


def _load(name, rel):
    spec = importlib.util.spec_from_file_location(name, os.path.join(HERE, rel))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


tb = _load('thai_evidence_builder', '../thai/build-thai-evidence.py')
th = _load('therapeutic_evidence_builder', '../therapeutic/build-therapeutic-evidence.py')
wt = _load('wording_tiers', '../client-pages/wording_tiers.py')
attrs, esc, para, details = tb.attrs, tb.esc, tb.para, tb.details

SLUG = 'chair-massage'
PAGE_TITLE = 'Chair Massage'
H1 = 'Chair Massage in Portland'
SEO_TITLE = 'Chair Massage | Butsaba Dusrai, Oregon LMT'
SEO_DESC = 'Chair massage in Portland with Butsaba Dusrai, LMT: short, seated, fully clothed sessions for workplaces and events, with pressure adjusted as you go.'
CORP = '/corporate-massage-portland/'
RESEARCH_HEADING = 'What the Research Says About Chair Massage'


def bid(name):
    """A fresh 8-hex block id, deterministic for this page (the installer refuses any id that already occurs in another post)."""
    return hashlib.sha1(('ts-chair-2026-10-30:' + name).encode()).hexdigest()[:8]


# ---- pictures: 0 = hero (already in the media library), 1-4 = the tints made by make-tints.py ---------------------------------------------------------
HERO = {'name': 'corporate-group-massage.webp', 'sha256': '764a7ef428cbd2985156b584116f8bc196dd5c3a900e2426ff4f49f8582a747e',
        'alt': 'Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.', 'w': 512, 'h': 416}
TINTS = [('green', 'The same illustration in green tones.'), ('teal', 'The same illustration in blue and teal tones.'), ('yellow', 'The same illustration in yellow tones.'), ('red', 'The same illustration in red tones.')]


def tint_images():
    out = []
    for tone, alt in TINTS:
        name = 'chair-massage-%s.webp' % tone
        with open(os.path.join(HERE, 'images', name), 'rb') as f:
            sha = hashlib.sha256(f.read()).hexdigest()
        out.append({'name': name, 'sha256': sha, 'alt': alt, 'title': alt, 'w': 512, 'h': 416})
    return out


# ---- sources: the Therapeutic registry for the shared ones (so an entry cannot drift), new entries for the chair massage studies ------------------------
PM = 'https://pubmed.ncbi.nlm.nih.gov/%s/'
SRC = {
 'cady': (PM % '9132704', 'Cady SH, Jones GE.', 'Massage therapy as a workplace intervention for reduction of stress', 'Percept Mot Skills, 1997.'),
 'engen12': (PM % '23059434', 'Engen DJ, Wahner-Roedler DL, Vincent A, Chon TY, Cha SS, Luedtke CA, Loehrer LL, Dion LJ, Rodgers NJ, Bauer BA.', 'Feasibility and effect of chair massage offered to nurses during work hours on stress-related symptoms: a pilot study', 'Complement Ther Clin Pract, 2012.'),
 'desouza': (PM % '34484493', 'de Souza TPB, Kurebayashi LFS, de Souza-Talarico JN, Turrini RNT.', 'The effectiveness of Chair Massage on Stress and Pain in Oncology', 'Int J Ther Massage Bodywork, 2021.'),
 'cabak16': (PM % '28157084', 'Cabak A, Kotynia P, Banasiński M, Obmiński Z, Tomaszewski W.', 'The Concept of “Chair Massage” in the Workplace as Prevention of Musculoskeletal Overload and Pain', 'Ortop Traumatol Rehabil, 2016.'),
 'sisko': (PM % '21688984', 'Siško PK, Videmšek M, Karpljuk D.', 'The effect of a corporate chair massage program on musculoskeletal discomfort and joint range of motion in office workers', 'J Altern Complement Med, 2011.'),
 'cabak17': (PM % '28721578', 'Cabak A, Mikicin M, Łyp M, Stanisławska I, Kaczor R, Tomaszewski W.', 'Preventive Chair Massage with Algometry to Maintain Psychosomatic Balance in White-Collar Workers', 'Adv Exp Med Biol, 2017.'),
 'zhang': (PM % '34235817', 'Zhang M, Murphy B, Cabanilla A, Yidi C.', 'Physical relaxation for occupational stress in healthcare workers: A systematic review and network meta-analysis of randomized controlled trials', 'J Occup Health, 2021.'),
 'li': (PM % '29964364', 'Li H, Zhao M, Shi Y, Xing Z, Li Y, Wang S, Ying J, Zhang M, Sun J.', 'The effectiveness of aromatherapy and massage on stress management in nurses: A systematic review', 'J Clin Nurs, 2019.'),
 'field': (PM % '8884390', 'Field T, Ironson G, Scafidi F, Nawrocki T, Goncalves A, Burman I, Pickens J, Fox N, Schanberg S, Kuhn C.', 'Massage therapy reduces anxiety and enhances EEG pattern of alertness and math computations', 'Int J Neurosci, 1996.'),
 'day': (PM % '20518225', 'Day AL, Gillan L, Francis L, Kelloway EK, Natarajan M.', 'Massage therapy in the workplace: reducing employee strain and blood pressure', 'G Ital Med Lav Ergon, 2009.'),
 'jaworska': (PM % '29122258', 'Jaworska-Burzyńska L, Sekułowicz M, Cieślik B, Kowalska J, Szczepańska-Gieracha J.', 'The role of massage in reducing the risk of burnout in employees of large corporations', 'Complement Ther Clin Pract, 2017.'),
 'fricker': (PM % '38318420', 'Fricker F, Barbotte MV, Pallot G, Radoua N, Sorci G, Heitz M, Brison G, Sales-Vuillemin E, Connat JL.', 'Positive psychological effects of seated acupressure massage are associated with a rise in plasma oxytocin without affecting CGRP levels or circulating IL-6', 'Compr Psychoneuroendocrinol, 2023.'),
 'bost': (PM % '16800217', 'Bost N, Wallis M.', 'The effectiveness of a 15 minute weekly massage in reducing physical and psychological stress in nurses', 'Aust J Adv Nurs, 2006.'),
 'cooke': (PM % '17727588', 'Cooke M, Holzhauser K, Jones M, Davis C, Finucane J.', 'The effect of aromatherapy massage with music on the stress and anxiety levels of emergency nurses: comparison between summer and winter', 'J Clin Nurs, 2007.'),
 'keller': (PM % '22196566', 'Keller SR, Engen DJ, Bauer BA, Holmes DR Jr, Rihal CS, Lennon RJ, Loehrer LL, Wahner-Roedler DL.', 'Feasibility and effectiveness of massage therapy for symptom relief in cardiac catheter laboratory staff: a pilot study', 'Complement Ther Clin Pract, 2012.'),
 'cyganska': (PM % '32708600', 'Cygańska A, Truszczyńska-Baszak A, Tomaszewski P.', 'Impact of Exercises and Chair Massage on Musculoskeletal Pain of Young Musicians', 'Int J Environ Res Public Health, 2020.'),
 'yoopat': (PM % '25892379', 'Yoopat P, Maes C, Poriau S, Vanwonterghem K.', 'Thai traditional massage: efficiency-assessment of three traditional massage methods on office workers: an explorative study', 'J Bodyw Mov Ther, 2015.'),
 'back': (PM % '19104272', 'Back C, Tam H, Lee E, Haraldsson B.', 'The effects of employer-provided massage therapy on job satisfaction, workplace stress, and pain and discomfort', 'Holist Nurs Pract, 2009.'),
 'turkeltaub': (PM % '25140587', 'Turkeltaub PC, Yearwood EL, Friedmann E.', 'Effect of a brief seated massage on nursing student attitudes toward touch for comfort care', 'J Altern Complement Med, 2014.'),
 'katz': (PM % '10603815', 'Katz J, Wowk A, Culp D, Wakeling H.', 'Pain and tension are reduced among hospital nurses after on-site massage treatments: a pilot study', 'J Perianesth Nurs, 1999.'),
 'mackereth05': (PM % '15944109', 'Mackereth P, Sylt P, Weinberg A, Campbell G.', 'Chair massage for carers in an acute cancer hospital', 'Eur J Oncol Nurs, 2005.'),
 'nightingale': (PM % '23890457', 'Nightingale L, Stringer J.', 'Complementary therapy for carers on a transplant unit', 'Complement Ther Clin Pract, 2013.'),
 'millspaugh': (PM % '42709485', 'Millspaugh J, Jameson BE, Cooper L.', 'Advancing the Quadruple Aim Through Workplace-Integrated Holistic Wellness Interventions: A Quality Improvement Evaluation', 'J Holist Nurs, 2026.'),
    # shared with the Therapeutic page: taken from its registry
    **{k: th.SRC[k] for k in ('mak', 'crawford', 'miake', 'moyer04', 'nccih', 'yin', 'ernst', 'domingo', 'engen')},
}

# ---------------- body copy (outside the research section: the whole house list applies) ----------------
WHAT_IS = [
 'Chair massage is a short massage you receive sitting up, fully clothed, in a padded portable chair that supports your chest, arms and head as you lean forward. I work through your clothing without oil, mostly on the back, shoulders, neck, arms and hands. A session usually lasts 10 to 20 minutes. The chair folds up and fits in a quiet corner, which is why chair massage is what I bring to employee appreciation days and workplace events.',
 'Most research on chair massage comes from small studies in workplaces and hospitals, usually with 15-minute sessions. In 52 employees, a 15-minute chair massage at work was followed by lower blood pressure, although the study had no comparison group.{cady} In 38 nurses offered a weekly 15-minute chair massage for 10 weeks, perceived-stress scores were lower at the end.{engen12} In a randomized trial of 60 women on oncology nursing teams, 15-minute chair massages twice a week for three weeks were followed by lower stress scores, and less interference from pain in daily life, than no massage.{desouza} Studies of office workers given short chair massages over a month report less neck and back discomfort,{sisko}{cabak16} and one found a higher tolerance for pressure on tender muscle points.{cabak16} These studies are small and short. I summarize them, including the weaker results, in the research section below.',
 'Chair massage is hands-on work by a licensed massage therapist, adapted to you as you give feedback. It is not a mechanical massage chair, and studies of those devices are not included here.',
]
COMMON = [
 'People often choose chair massage when hours at a desk, on the phone or on their feet leave the neck, shoulders and upper back tight, and they want attention to those areas without changing clothes or taking an hour away from work. Others like it as a pause in a busy day, or as a first massage, since you stay dressed and the session is short. Employers book it for appreciation days and events, where each person takes a short turn in the chair.',
 'Research on chair massage for desk work points the same way, with limits. In 50 office workers, half had eight 15-minute chair massages during work breaks over four weeks and half had none; the massage group reported less pain in the upper and lower spine and the right arm, with smaller improvements in the right shoulder and left forearm, and tolerated more pressure on the muscle points tested before it hurt, most of all in the trapezius and supraspinatus muscles, while changes in the control group were not statistically significant.{cabak16} In 124 computer workers randomly split among four weeks of chair massage, relaxing-music sessions or no intervention, the massage group had higher pressure-pain thresholds in every neck muscle tested and a higher relaxation level measured from heart-rate variability; the music group improved on relaxation but its pain thresholds were unchanged, falling in one muscle, and changes in the no-intervention group were negligible.{cabak17} In 19 women in administration and management who had chair massage twice a week for a month, neck and upper-back discomfort scores fell and the neck bent further to the side and backward, although all 19 received the massage, with no separate control group, and only 15 completed the study.{sisko}',
 'Before I begin, I ask about your preferences and anything I should know about your health. You can request lighter pressure, a different focus, or a pause at any time.',
]
WHO = [
 'Chair massage suits people who want focused attention on the back, neck and shoulders in a short session, and groups where many people will take a turn. You do not need to undress or to have had a massage before, and you can ask me to stay with one area or to work more lightly. If you have an ongoing health condition, a recent injury or surgery, or you are pregnant, tell me at the start. I will keep the work within the scope of massage, adapt it or leave parts out, and I will tell you if I think you should check with a doctor first. If the chair itself is not comfortable for you, say so: I can adjust it or work with you seated upright in an ordinary chair.',
]
# FAQ answers may not contain links (Spectra wraps question and answer in role="tab"; a link inside is an axe nested-interactive failure, the house rule since the Thai page).
FAQ = [
 ('Do I Need to Undress for Chair Massage?',
  'No. You stay fully clothed, and I do not use oil. Light layers are easiest to work through; a thick coat or a bulky hood is better taken off.'),
 ('How Long Is a Chair Massage?',
  'At corporate and group events I plan about 10 to 15 minutes per person, depending on the schedule and how many people take part. The Corporate and Group Massage page explains how the time block is planned. The studies summarized below mostly used 15-minute sessions.'),
 ('What Is the Massage Chair Like?',
  'It is a padded folding chair that you sit on facing forward, with your knees on supports, your chest against a cushion and your face in a cradle, so your back, neck and shoulders are easy to reach. I adjust it to your height. If kneeling or leaning forward is uncomfortable, I can work with you seated upright in an ordinary chair.'),
 ('Is Chair Massage the Same as a Mechanical Massage Chair?',
  'No. A massage chair is a machine with rollers. Chair massage is hands-on work by a licensed massage therapist in a portable chair, adapted to you as you give feedback.'),
 ('How Much Pressure Will You Use?',
  'Enough to reach the tight muscles, and no more than you want. Tell me what you like, and ask me to lighten up or press more firmly at any time; I will adjust.'),
 ('How Is Chair Massage Different From Your Other Sessions?',
  'Chair massage is seated, clothed and short, and it focuses on the back, shoulders, neck, arms and hands. Therapeutic massage and deep tissue massage take place on a table, last longer and can cover the whole body. Thai massage is done on a mat and adds assisted stretching. Each is described on its own page.'),
 ('Can I Have a Chair Massage If I’m Pregnant or Have a Health Condition?',
  'Often yes, with changes. Tell me before I start about any health conditions, medications, pregnancy, recent injury or surgery. I may adjust the work, leave an area out, or suggest you check with your clinician first.'),
 ('Will I Feel the Results Right Away?',
  'People respond differently to massage, so I can’t promise a particular result or timeline. I will ask how the session feels and adjust as I go.'),
 ('What Does the Host Need to Provide?',
  'A quiet corner with roughly six feet by six feet of floor space, somewhere for people to wait, and a way for them to sign up for a time. I bring the chair and everything else. The Corporate and Group Massage page lists what I plan with you before the event.'),
 ('Where Do Sessions Take Place?',
  'Right now I offer corporate and group massage only, at suitable Portland venues, with bookings beginning November 14, 2026. Individual appointments at a dedicated Thai Solitude location are planned for summer 2027. I do not offer individual house calls.'),
 ('How Do I Book a Session?',
  'Corporate and group massage bookings begin November 14, 2026. The Corporate and Group Massage page explains how to request a quote. Individual appointments are planned for summer 2027.'),
]
EXPECT_INTRO = 'Before your session, tell me about any health conditions, medications, pregnancy, recent injury or surgery. I may adjust the work, or suggest you check with your clinician first. New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage.'
STEPS = [
 ('Quick Check-In', 'Before you sit down, I ask where you feel tension, how much pressure you like, and anything about your health I should know. At a group event this takes a minute or two.'),
 ('Settling Into the Chair', 'I adjust the chair to your height and show you how to rest your chest, arms and face. If anything is uncomfortable, I change it before I begin.'),
 ('Chair Massage Work', 'I work through your clothing on the back, shoulders, neck, arms and hands, starting with broad pressure and spending more time where you asked. You can ask for a change at any point.'),
 ('Before You Go', 'At the end, I ask how the work felt, and you can take a moment before standing up.'),
]

# ---------------- research section (the three tiers apply from the heading to the end of the page) ----------------
RES_INTRO = ('Research on chair massage is smaller than the research on massage in general, and it has the same limits: most studies are small, many have no comparison group, results often depend on what massage is compared with, '
             'and few follow people for more than a few weeks. Most were done in workplaces and hospitals, usually with sessions of about 15 minutes, though some used 20- or 30-minute sessions and several reports do not give the length. '
             'The summaries below describe what the studies reported. They are general information, not medical advice or a promise of any result, and people respond differently. This section covers hands-on chair and seated '
             'massage and brief massage given at work; studies of mechanical massage chairs, and of massage given to patients as part of their care, are not summarized here. Each part starts with what the studies found in '
             'massage’s favor, then gives the results that were weaker, mixed or negative, and ends on what is encouraging: I included the studies I found with disappointing results, not only the favorable ones.')
PANELS = [
 ('Reviews, and What They Say About Limits', True, [
  'I did not find a systematic review of chair massage on its own, so the reviews that apply are of massage in general and of massage for people under stress at work. Reviews of massage for pain that rated how certain their evidence was reached a consistent point in massage’s favor: every conclusion rated moderate-certainty found massage associated with less pain, and a meta-analysis of 67 randomized trials found lower pain scores after massage than after sham massage, no massage or other approaches.{mak}{crawford} A 2021 network meta-analysis of 15 randomized trials with 688 health care workers found that physical relaxation methods as a group reduced measures of occupational stress compared with no intervention, and that massage therapy on its own was one of only two methods, with yoga, that did better than control.{zhang} A 2004 meta-analysis of 37 randomized studies found that a single massage was followed by lower state anxiety, blood pressure and heart rate, with the largest effects, on trait anxiety and depression, coming after a course of sessions.{moyer04}',
  'The same reviews are careful about limits. None of the conclusions in the 2024 review was rated high-certainty, and conclusions backed by moderate- or high-certainty evidence that massage was better than other active approaches were rare.{mak} An earlier evidence map of 49 systematic reviews found that the high-quality reviews reached conclusions of low strength of evidence, because few large, rigorous studies had been done.{miake} In the network meta-analysis, yoga was identified as the best method and massage’s effect was smaller.{zhang} A 2019 systematic review of 10 studies of aromatherapy and massage for stress in nurses, four of them of massage alone, found that most reported a benefit but that the studies were few and at high or unclear risk of bias, and concluded that the evidence does not sufficiently prove that massage reduces nurses’ job-related stress.{li} The 2004 meta-analysis found no lower negative mood, cortisol or less immediate pain after a single massage.{moyer04}',
  'Massage is generally described as low risk, and where serious problems on record can be traced to who gave the massage, they are mostly tied to people who were not trained massage therapists. The U.S. National Center for Complementary and Integrative Health notes that the risk of harm appears to be low, although rare serious side effects such as a blood clot, nerve injury or bone fracture have been reported.{nccih} A 2014 review of 40 published reports found 138 adverse events over 11 years, mainly disc herniation, soft-tissue injury, nerve problems, spinal cord injury and tears in the neck arteries, with spinal manipulation inside a massage repeatedly linked to the serious ones. Massage therapists were the practitioners in only 8 of the 138 events (5.8 percent) and chiropractors in 33 (23.9 percent); the rest (70.3 percent) involved unregistered people or other health professionals, and the authors concluded that massage by nonprofessionals and forceful techniques is often associated with adverse events.{yin} A 2003 review of 16 case reports and four case series likewise found that most adverse effects followed exotic techniques or massage by laypeople, with massage therapists rarely implicated, that serious effects were mostly tied to techniques other than Swedish massage, and concluded that serious events are probably true rarities.{ernst} The U.S. center also advises against using massage to put off seeing a health care provider about a medical problem. The chair massage studies summarized below mostly did not report on adverse events one way or the other. Chair massage is short, done through clothing with moderate pressure, and does not include spinal manipulation. Serious events are rare: I ask about your health, medications, pregnancy and recent injuries before each session, even a short one at an event, and you can ask for lighter pressure or a pause at any time.',
 ]),
 ('Stress, Anxiety and Blood Pressure', False, [
  'Several workplace studies report less stress and lower blood pressure after chair massage. In 52 employees, systolic and diastolic blood pressure were lower after a 15-minute chair massage at work.{cady} In a study from the Touch Research Institute in Miami, 26 adults had a 15-minute chair massage and 24 sat in the massage chair to relax, twice a week for five weeks; anxiety was lower after the massage sessions but not the control sessions, brain-wave recordings in the massage group showed a pattern the authors read as alertness, the massage group became faster and more accurate at arithmetic, and at five weeks job-stress scores were lower only in the massage group.{field} In 28 employees randomly assigned to weekly massage at work for four weeks or to no massage, strain and blood pressure fell during the four massage weeks in the massage group but not in the control group.{day} In 48 insurance brokers at one company, 16 had a series of massages seated in a massage chair, 16 had them lying on a table and 16 had none; both massage groups improved on every burnout and anxiety measure, and the authors concluded that the position did not matter.{jaworska} In 59 young healthy adults, a 17-minute seated acupressure massage was followed by lower anxiety and higher self-confidence scores than sitting in the chair without massage, with a slightly lower heart rate and a rise in the hormone oxytocin.{fricker}',
  'The limits are real. The 52-employee study had no comparison group, so the lower blood pressure cannot be separated from the effect of sitting quietly for 15 minutes.{cady} In the Miami study, salivary cortisol was lower after massage only on the first day, and depression scores fell in both groups.{field} The 28-employee trial was small, and its abstract gives no figures.{day} The seated-acupressure study found no link between cortisol levels and how people felt, and no change in CGRP or in the marker IL-6.{fricker} In 60 nurses randomly assigned to a weekly 15-minute back massage for five weeks or to nothing, anxiety scores fell in the massage group and rose in the control group, but urinary cortisol and blood pressure did not differ significantly between the groups.{bost} In 365 on-site aromatherapy massages with music given to emergency nurses over two 12-week periods, anxiety measured before and after the sessions was lower afterward, but occupational stress scores did not change over the 12 weeks and sick leave did not differ.{cooke} In 50 cardiac catheterization laboratory staff randomly assigned to 5 or 10 weekly 30-minute massages, with 10 further staff as controls, no significant effects were found on fatigue, pain, relaxation, stress or tension, even though 90 percent of appointments were used.{keller}',
  'What is encouraging is how consistently the short-term effect appears: in every study above that measured anxiety before and after sessions, it was lower afterward,{field}{fricker}{cooke} and where massage was compared with sitting quietly in the same chair, the massage group did better.{field}{fricker} Studies with a course of sessions over several weeks more often found changes in stress or burnout scores than single sessions did.{field}{day}{jaworska}',
 ]),
 ('Desk Work, Muscle Discomfort and Movement', False, [
  'Several chair massage studies looked at the discomfort that comes with sitting at a computer. In 50 office workers, eight 15-minute chair massages over four weeks were followed by less pain in the upper and lower spine and the right arm and by higher pressure-pain thresholds at every muscle point tested, with the largest change in the shoulder muscles on the left side, while changes in the control group were not statistically significant.{cabak16} In 124 computer workers randomly assigned to four weeks of chair massage, relaxing music or nothing, chair massage raised the pressure-pain threshold in all the neck muscles tested and the relaxation level measured from heart-rate variability from 31.9 to 41.6 percent; music raised relaxation but left pain thresholds unchanged, with the threshold in one shoulder muscle falling, and changes in the control group were negligible.{cabak17} In 19 women in administration and management who had chair massage twice a week for a month, neck side-bending improved from 42.4 to 48.3 degrees, neck extension from 63.2 to 67.2 degrees, and discomfort scores for the neck and upper back fell.{sisko} In 44 music students, eight 15-minute sessions of chair massage or exercises over four weeks raised the pressure-pain threshold at the most sensitive points in the upper shoulder muscles by 25 to 34 percent, most visibly in the massage group and from the fourth session on.{cyganska} In a crossover study of 17 healthy adults, muscle activity in the upper shoulder muscles fell by 19 percent after a short, moderate-pressure massage of the shoulders and neck, compared with 1 percent after sitting quietly.{domingo} In a study from Thailand of 30 female administrative employees, 30 minutes of traditional Thai massage in each of three styles was followed by less forearm fatigue on muscle recordings.{yoopat}',
  'The limits: all 19 women in the Ljubljana study received the massage, only 15 completed it, and its authors’ claim that twice-weekly sessions beat a single session rests on comparing phases within the same small group.{sisko} The 17-adult study measured muscle activity, not pain, and in the Thai study shoulder strength was not significantly different after massage.{domingo}{yoopat} In 45 cardiac sonographers randomly assigned to weekly 30-minute chair massage, massage plus stretching, or nothing for 10 weeks, discomfort scores improved somewhat in both massage groups, and the authors called for larger trials.{engen} An employer-funded program of 20-minute on-site massages for long-term care staff showed possible improvements in job satisfaction and an early reduction in pain severity, with the greatest benefit in people who already had symptoms, but no long-term effect was demonstrated.{back} None of the studies that give their length followed people for longer than ten weeks.',
  'Still, the two office-worker studies are among the better-designed chair massage studies, with randomized control groups and an objective measure of pain sensitivity,{cabak16}{cabak17} and together with the music-student study they agree that four weeks of short sessions changed how much pressure tender neck and shoulder muscles could take.{cabak16}{cabak17}{cyganska} In 29 nursing students who had both a firm and a very light seated massage, the firm one was rated better for energy, pain, stress and feeling physically tense, which is why I ask you about pressure rather than keeping it light by default.{turkeltaub}',
 ]),
 ('Nurses, Hospital Staff and Carers', False, [
  'Hospitals have studied chair massage for their own staff and for visiting families. In 38 nurses offered a weekly 15-minute chair massage during work for 10 weeks, 329 of 400 appointments were used, and at 10 weeks perceived-stress scores had fallen from 17.85 to 14.92 and anxiety scores from 49.45 to 40.95, with better scores on a self-assessment scale and a symptom scale.{engen12} In the randomized trial of 60 women on oncology nursing teams in São Paulo, 15-minute chair massages twice a week for three weeks were followed by lower stress scores than no massage, with an effect size (a Cohen’s d of 1.21) that counts as large, and less interference from pain in general activity, mood, work and sleep.{desouza} In 12 hospital staff, mostly nurses, who had up to eight 15-minute Swedish massages at work, pain and tension were significantly lower, and relaxation and mood better, after massage, measured before and after each session.{katz} Evaluations of chair massage services for carers of inpatients at a cancer hospital in Manchester, England, reported better physical and emotional scores that were retained to the next day, better sleep, and feel-good scores that rose from 5 to 8 out of 10 on a transplant unit.{mackereth05}{nightingale} In a quality improvement project at an academic medical center in New Jersey, chair massage was among the brief on-site practices offered to nursing staff over 11 months, and its use rose over the period.{millspaugh}',
  'Most of these are pilot studies or service evaluations without a comparison group, so they cannot show how much of the change came from the massage itself: the 38-nurse study, the 12-staff study and the two Manchester evaluations all compared people’s scores before and after massage, with no separate control group described.{engen12}{katz}{mackereth05}{nightingale} The 2021 network meta-analysis found that massage reduced occupational stress in health care workers but that yoga did more, and the 2019 systematic review of aromatherapy and massage in nurses found that most studies reported a benefit but judged them too few and at too high a risk of bias to prove the effect.{zhang}{li} In the 11-month project, staff perceptions of self-care opportunities and of a restorative work environment did not change significantly, and staffing and scheduling limited who could take part.{millspaugh} In the cardiac catheterization laboratory study described above, weekly massages produced no statistically significant effects, although the authors’ feasibility test supported further research on fatigue, pain, relaxation and tension.{keller}',
  'The randomized oncology-nursing trial is the strongest chair massage study I found, and it is encouraging: six 15-minute sessions over three weeks were enough to show a large effect-size difference in stress between the groups.{desouza} The uptake numbers say something on their own: 329 of 400 appointments used by the nurses, 90 percent of appointments used by the catheterization laboratory staff, and 227 chair massages given to carers over two years on one transplant unit.{engen12}{keller}{nightingale}',
 ]),
]
SOURCES_NOTE = 'Each number above links to the source. Titles are quoted as published.'

# ---------------- block attribute strings, copied from page 1107 as stored on staging38 (only %s = the block id is new) ----------------
A_HERO_WRAP = ('{"block_id":"%s","contentWidth":"default","innerContentWidth":"alignfull","minHeightDesktop":0,"backgroundType":"color","backgroundColor":"var(\\u002d\\u002dast-global-color-4)","topPaddingDesktop":120,"bottomPaddingDesktop":80,"leftPaddingDesktop":40,"rightPaddingDesktop":40,"topPaddingTablet":100,"bottomPaddingTablet":40,"leftPaddingTablet":32,"rightPaddingTablet":32,"topPaddingMobile":80,"bottomPaddingMobile":0,"leftPaddingMobile":24,"rightPaddingMobile":24,"paddingLink":false,"variationSelected":true,"rowGapMobile":30,"columnGapDesktop":80,"columnGapTablet":40,"isBlockRootParent":true,"layout":"grid","gridColumnDesktop":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":32}}],"gridColumnTablet":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":37}}],"gridAlignItemsDesktop":"start","gridAlignContentDesktop":"end"}')
A_H1 = '{"block_id":"%s","classMigrate":true,"headingDescToggle":false,"headingDescPosition":"above-heading","separatorColor":"var(\\u002d\\u002dast-global-color-0)","headingTag":"h1","seperatorPosition":"above-heading","separatorWidth":64,"separatorWidthType":"px","headSpace":0,"headFontSizeMobile":26,"separatorSpace":14,"blockTopPadding":0,"blockRightPadding":0,"blockLeftPadding":0,"blockBottomPadding":0}'
A_MAIN_WRAP = '{"block_id":"%s","topPaddingDesktop":180,"bottomPaddingDesktop":60,"leftPaddingDesktop":40,"rightPaddingDesktop":40,"topPaddingTablet":140,"bottomPaddingTablet":0,"leftPaddingTablet":32,"rightPaddingTablet":32,"topPaddingMobile":140,"bottomPaddingMobile":0,"leftPaddingMobile":24,"rightPaddingMobile":24,"paddingLink":false,"variationSelected":true,"rowGapDesktop":40,"rowGapTablet":40,"rowGapMobile":30,"isBlockRootParent":true}'
_GRID_COLS = ('"layout":"grid","gridColumnDesktop":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":40}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":8}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}],'
              '"gridColumnTablet":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":40}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":7}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}],"gridAlignItemsDesktop":"center","gridAlignItemsTablet":"start"}')
A_ROW = ('{"block_id":"%s","bottomPaddingDesktop":40,"topPaddingTablet":0,"bottomPaddingTablet":40,"leftPaddingTablet":0,"rightPaddingTablet":0,"topPaddingMobile":0,"bottomPaddingMobile":30,"leftPaddingMobile":0,"rightPaddingMobile":0,"paddingLink":false,"rowGapMobile":12,"columnGapDesktop":32,"columnGapTablet":20,"containerBorderTopWidth":0,"containerBorderLeftWidth":0,"containerBorderRightWidth":0,"containerBorderBottomWidth":1,"containerBorderLink":false,"containerBorderStyle":"solid","containerBorderColor":"var(\\u002d\\u002dast-global-color-6)",' + _GRID_COLS)
A_ROW_LAST = ('{"block_id":"%s","bottomPaddingDesktop":0,"paddingLink":false,"rowGapMobile":12,"columnGapDesktop":32,"columnGapTablet":20,"containerBorderTopWidth":0,"containerBorderLeftWidth":0,"containerBorderRightWidth":0,"containerBorderBottomWidth":0,"containerBorderLink":false,"containerBorderStyle":"none",' + _GRID_COLS)
A_SPACER = '{"block_id":"%s","classMigrate":true,"headingDescToggle":false,"headingColor":"var(\\u002d\\u002dast-global-color-0)","blockLeftPadding":28,"blockBottomPadding":100,"blockTopPaddingTablet":0,"blockRightPaddingTablet":0,"blockLeftPaddingTablet":0,"blockBottomPaddingTablet":0,"blockTopPaddingMobile":8,"blockRightPaddingMobile":0,"blockLeftPaddingMobile":0,"blockBottomPaddingMobile":0}'
A_SECTION_TEXT = '{"block_id":"%s","classMigrate":true,"headingDescToggle":true,"subHeadingColor":"","headSpace":8,"subHeadSpace":0}'
A_FAQ = '{"block_id":"%s","borderStyle":"","borderWidth":"","borderRadius":"","borderColor":"","overallBorderTopWidth":1,"overallBorderLeftWidth":1,"overallBorderRightWidth":1,"overallBorderBottomWidth":1,"overallBorderTopLeftRadius":2,"overallBorderTopRightRadius":2,"overallBorderBottomLeftRadius":2,"overallBorderBottomRightRadius":2,"overallBorderStyle":"solid","overallBorderColor":"#D2D2D2"}'
A_EXPECT_HEAD = '{"block_id":"%s","classMigrate":true,"headingDescToggle":false,"headingDescPosition":"above-heading","headingAlign":"center","separatorColor":"var(\\u002d\\u002dast-global-color-0)","seperatorStyle":"solid","seperatorPosition":"above-heading","separatorWidth":64,"separatorWidthType":"px","headSpace":0,"separatorSpace":14,"blockTopPadding":100,"blockRightPadding":220,"blockLeftPadding":220,"blockBottomPadding":0,"blockTopPaddingTablet":80,"blockRightPaddingTablet":40,"blockLeftPaddingTablet":40,"blockBottomPaddingTablet":0,"blockTopPaddingMobile":60,"blockRightPaddingMobile":0,"blockLeftPaddingMobile":0,"blockBottomPaddingMobile":0}'
A_STEPS_WRAP = '{"block_id":"%s","topPaddingDesktop":0,"bottomPaddingDesktop":100,"leftPaddingDesktop":40,"rightPaddingDesktop":40,"topPaddingTablet":40,"bottomPaddingTablet":80,"leftPaddingTablet":32,"rightPaddingTablet":32,"topPaddingMobile":40,"bottomPaddingMobile":64,"leftPaddingMobile":24,"rightPaddingMobile":24,"paddingLink":false,"variationSelected":true,"rowGapDesktop":30,"rowGapTablet":20,"rowGapMobile":20,"columnGapDesktop":30,"columnGapTablet":20,"isBlockRootParent":true,"layout":"grid","gridColumnDesktop":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}],"gridColumnTablet":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}]}'
A_STEP_CARD = '{"block_id":"%s","justifyContentDesktop":"flex-start","topPaddingDesktop":40,"bottomPaddingDesktop":40,"leftPaddingDesktop":40,"rightPaddingDesktop":40,"topPaddingTablet":32,"bottomPaddingTablet":32,"leftPaddingTablet":32,"rightPaddingTablet":32,"topPaddingMobile":24,"bottomPaddingMobile":24,"leftPaddingMobile":24,"rightPaddingMobile":24,"containerBorderTopWidth":1,"containerBorderLeftWidth":1,"containerBorderRightWidth":1,"containerBorderBottomWidth":1,"containerBorderTopLeftRadius":20,"containerBorderTopRightRadius":20,"containerBorderBottomLeftRadius":20,"containerBorderBottomRightRadius":20,"containerBorderTopLeftRadiusTablet":16,"containerBorderTopRightRadiusTablet":16,"containerBorderBottomLeftRadiusTablet":16,"containerBorderBottomRightRadiusTablet":16,"containerBorderTopLeftRadiusMobile":14,"containerBorderTopRightRadiusMobile":14,"containerBorderBottomLeftRadiusMobile":14,"containerBorderBottomRightRadiusMobile":14,"containerBorderStyle":"solid","containerBorderColor":"var(\\u002d\\u002dast-global-color-6)","isGridCssInParent":true}'
A_STEP_TEXT = '{"block_id":"%s","classMigrate":true,"headingDescToggle":true,"headingColor":"var(\\u002d\\u002dast-global-color-2)","subHeadingColor":"","headingTag":"p","headSpace":10,"subHeadSpace":0,"headFontSize":24,"headFontSizeTablet":22,"headFontSizeMobile":20,"headLineHeight":1.3000000000000000444089209850062616169452667236328125}'
# the picture blocks: the Therapeutic page's attribute set, with the picture's own URL, id, alt and natural size; hero and section pictures differ only in the size/margin attributes
_RADII = '"imageBoxShadowVOffset":2,"imageBoxShadowBlur":5,"imageBoxShadowSpread":-1,"customHeightSetDesktop":true,"customHeightSetTablet":true,"customHeightSetMobile":true,"imageBorderTopLeftRadius":20,"imageBorderTopRightRadius":20,"imageBorderBottomLeftRadius":20,"imageBorderBottomRightRadius":20,"imageBorderTopLeftRadiusTablet":16,"imageBorderTopRightRadiusTablet":16,"imageBorderBottomLeftRadiusTablet":16,"imageBorderBottomRightRadiusTablet":16,"imageBorderTopLeftRadiusMobile":14,"imageBorderTopRightRadiusMobile":14,"imageBorderBottomLeftRadiusMobile":14,"imageBorderBottomRightRadiusMobile":14}'
A_IMG_HERO = ('{"block_id":"%(id)s","disableLazyLoad":true,"url":"%(url)s","urlTablet":"%(url)s","urlMobile":"%(url)s","alt":"%(alt)s","linkDestination":"none","title":"%(alt)s","width":396,"widthTablet":260,"widthMobile":800,"height":380,"heightTablet":250,"heightMobile":300,"naturalWidth":%(w)d,"naturalHeight":%(h)d,"sizeSlug":"custom","sizeSlugTablet":"custom","sizeSlugMobile":"custom","imageTopMargin":0,"imageRightMargin":0,"imageLeftMargin":0,"imageBottomMargin":-180,"imageTopMarginTablet":0,"imageRightMarginTablet":0,"imageLeftMarginTablet":0,"imageBottomMarginTablet":-120,"imageTopMarginMobile":0,"imageRightMarginMobile":0,"imageLeftMarginMobile":0,"imageBottomMarginMobile":-80,"imageMarginLink":false,"objectFit":"cover","objectFitTablet":"cover","objectFitMobile":"cover",' + _RADII)
A_IMG_SECTION = ('{"block_id":"%(id)s","disableLazyLoad":true,"url":"%(url)s","urlTablet":"%(url)s","urlMobile":"%(url)s","alt":"%(alt)s","linkDestination":"none","title":"%(alt)s","width":200,"widthTablet":282,"widthMobile":800,"height":200,"heightTablet":194,"heightMobile":250,"naturalWidth":%(w)d,"naturalHeight":%(h)d,"sizeSlug":"custom","sizeSlugTablet":"custom","sizeSlugMobile":"custom","objectFit":"cover","objectFitTablet":"cover","objectFitMobile":"cover",' + _RADII)
A_PARA_400 = '{"style":{"typography":{"fontWeight":"400"}}}'

# ---- block ids: one per block, derived from a name ----
ID = {k: bid(k) for k in ['hero', 'h1', 'img0', 'main', 'row1', 'img1', 'sp1', 'txt1', 'row2', 'img2', 'sp2', 'txt2', 'row3', 'img3', 'sp3', 'txt3', 'row4', 'img4', 'sp4', 'faq', 'expect', 'steps',
                           'step1', 'steph1', 'step2', 'steph2', 'step3', 'steph3', 'step4', 'steph4', 'research', 'svcrow', 'svcimg', 'svcsp', 'svctxt'] + ['faq%d' % i for i in range(len(FAQ))]}
assert len(set(ID.values())) == len(ID), 'block id collision'


class Cite:
    def __init__(self):
        self.order = []

    def mark(self, text):
        def repl(m):
            key = m.group(1)
            if key not in SRC:
                raise SystemExit('unknown source key: ' + key)
            if key not in self.order:
                self.order.append(key)
            return '<a href="%s">[%d]</a>' % (SRC[key][0], self.order.index(key) + 1)
        return re.sub(r'\{([a-z0-9_]+)\}', repl, text)


def jattr(s):
    """A value for use inside an attribute JSON string (quotes and backslashes escaped, as WordPress stores them)."""
    return json.dumps(s, ensure_ascii=False)[1:-1]


def image_block(kind, n, alt, w, h):
    """A Spectra image block whose URL is a template marker ({{IMG_URL_n}}); the installer fills it from the media library.
    No media id in the attributes or the img class (the corporate row and the Work With Me photo do the same): the block then depends only on the file's URL, so applying again after
    a rollback (which deletes and re-imports the tints under new ids) gives byte-identical content, and the rehearsal's re-apply check holds."""
    url = '{{IMG_URL_%d}}' % n
    a = (A_IMG_HERO if kind == 'hero' else A_IMG_SECTION) % {'id': ID['img%d' % n], 'url': url, 'alt': jattr(alt), 'w': w, 'h': h}
    size = 'width="396" height="380"' if kind == 'hero' else 'width="200" height="200"'
    return ('<!-- wp:uagb/image %s -->\n'
            '<div class="wp-block-uagb-image uagb-block-%s wp-block-uagb-image--layout-default wp-block-uagb-image--effect-static wp-block-uagb-image--align-none"><figure class="wp-block-uagb-image__figure">'
            '<img srcset="%s ,%s 780w, %s 360w" sizes="(max-width: 480px) 150px" src="%s" alt="%s" %s title="%s" role="img"/></figure></div>\n'
            '<!-- /wp:uagb/image -->') % (a, ID['img%d' % n], url, url, url, url, esc(alt), size, esc(alt))


def section_row(n, heading, paras, cite, last=False):
    alt = TINTS[n - 1][1]
    body = '<br><br>'.join(cite.mark(esc(p)) for p in paras)
    a_row = (A_ROW_LAST if last else A_ROW) % ID['row%d' % n]
    return ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s">%s\n\n'
            '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"></div>\n<!-- /wp:uagb/advanced-heading -->\n\n'
            '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h2 class="uagb-heading-text">%s</h2><p class="uagb-desc-text">%s</p></div>\n'
            '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container -->') % (
        a_row, ID['row%d' % n], image_block('section', n, alt, 512, 416), A_SPACER % ID['sp%d' % n], ID['sp%d' % n], A_SECTION_TEXT % ID['txt%d' % n], ID['txt%d' % n], esc(heading), body)


def faq_block():
    children = []
    for i, (q, a) in enumerate(FAQ):
        if '<a ' in a or 'href' in a:
            raise SystemExit('FAQ answers must not contain links: ' + q)
        children.append('<!-- wp:uagb/faq-child %s /-->' % attrs({'block_id': ID['faq%d' % i], 'question': q, 'answer': a, 'icon': 'chevron-up', 'iconActive': 'chevron-down'}))
    return ('<!-- wp:uagb/faq %s -->\n<div class="wp-block-uagb-faq uagb-faq__outer-wrap uagb-block-%s uagb-faq-icon-row uagb-faq-layout-accordion uagb-faq-expand-first-true uagb-faq-inactive-other-true uagb-faq-equal-height" data-faqtoggle="true" role="tablist">'
            '%s</div>\n<!-- /wp:uagb/faq -->') % (A_FAQ % ID['faq'], ID['faq'], '\n\n'.join(children))


def centered_heading(key, text, bottom=False):
    a = (tb.HEAD_ATTRS if bottom else A_EXPECT_HEAD) % ID[key]   # tb.HEAD_ATTRS = the same heading with bottom padding (the research heading of the other pages)
    return '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><div class="uagb-separator"></div><h2 class="uagb-heading-text">%s</h2></div>\n<!-- /wp:uagb/advanced-heading -->' % (a, ID[key], esc(text))


def steps_block():
    cards = []
    for i, (t, d) in enumerate(STEPS, 1):
        cards.append('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-block-%s"><!-- wp:uagb/advanced-heading %s -->\n'
                     '<div class="wp-block-uagb-advanced-heading uagb-block-%s"><p class="uagb-heading-text">%s</p><p class="uagb-desc-text">%s</p></div>\n'
                     '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container -->' % (A_STEP_CARD % ID['step%d' % i], ID['step%d' % i], A_STEP_TEXT % ID['steph%d' % i], ID['steph%d' % i], esc(t), esc(d)))
    return '<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s alignfull uagb-is-root-container"><div class="uagb-container-inner-blocks-wrap">%s</div></div>\n<!-- /wp:uagb/container -->' % (
        A_STEPS_WRAP % ID['steps'], ID['steps'], '\n\n'.join(cards))


def research(cite):
    panels = []
    for title, opened, paras in PANELS:
        panels.append(details(title, opened, [para(cite.mark(esc(p)), plain=True) for p in paras]))
    items = []
    for key in cite.order:
        url, authors, title, venue = SRC[key]
        items.append('<!-- wp:list-item -->\n<li><cite class="ts-source">%s <a href="%s">%s</a>. %s</cite></li>\n<!-- /wp:list-item -->' % (esc(authors), url, esc(title), esc(venue)))
    sources_list = '<!-- wp:list {"ordered":true,"style":{"typography":{"fontWeight":"400"}}} -->\n<ol class="wp-block-list" style="font-weight:400">%s</ol>\n<!-- /wp:list -->' % '\n\n'.join(items)
    panels.append(details('Sources', False, [para(SOURCES_NOTE, plain=True), sources_list]))
    return '\n\n'.join([centered_heading('research', RESEARCH_HEADING, bottom=True), para(esc(RES_INTRO))] + panels)


def build_page():
    cite = Cite()
    hero = ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s default uagb-is-root-container"><!-- wp:uagb/advanced-heading %s -->\n'
            '<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h1 class="uagb-heading-text">%s</h1></div>\n<!-- /wp:uagb/advanced-heading -->\n\n%s</div>\n<!-- /wp:uagb/container -->') % (
        A_HERO_WRAP % ID['hero'], ID['hero'], A_H1 % ID['h1'], ID['h1'], esc(H1), image_block('hero', 0, HERO['alt'], HERO['w'], HERO['h']))
    rows = [section_row(1, 'What Is Chair Massage?', WHAT_IS, cite),
            section_row(2, 'Common Reasons Clients Choose Chair Massage', COMMON, cite),
            section_row(3, 'Who Can Benefit From Chair Massage?', WHO, cite)]
    faq_row = ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s">%s\n\n'
               '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"></div>\n<!-- /wp:uagb/advanced-heading -->\n\n%s</div>\n<!-- /wp:uagb/container -->') % (
        A_ROW_LAST % ID['row4'], ID['row4'], image_block('section', 4, TINTS[3][1], 512, 416), A_SPACER % ID['sp4'], ID['sp4'], faq_block())
    main = ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-block-%s alignfull uagb-is-root-container"><div class="uagb-container-inner-blocks-wrap">%s\n\n%s</div></div>\n<!-- /wp:uagb/container -->') % (
        A_MAIN_WRAP % ID['main'], ID['main'], '\n\n'.join(rows + [faq_row]), centered_heading('expect', 'What To Expect During Your Appointment'))
    content = '\n\n'.join([hero, main, para(esc(EXPECT_INTRO)), steps_block(), research(cite)]) + '\n'
    return content, cite


# ---- the Services row (same grid as the three service rows; the green tint; relative links) ----
ROW_ANCHOR = '<!-- wp:uagb/container {"block_id":"b3c7a91e"'   # the Corporate & Group row (added 2026-10-24): the Chair Massage row goes immediately before it
ROW_DESC = ('Short, seated massage through your clothing, focused on the back, shoulders, neck, arms and hands: what I bring to employee appreciation days and workplace events, '
            'in a portable chair that fits in a quiet corner. <a href="/%s/">Learn about Chair Massage</a>' % SLUG)
ROW_ATTRS = A_ROW.replace('\\u002d\\u002d', '--')   # the Services page stores its rows with literal "--" in the attributes (the corporate row too); both parse the same


def build_row():
    a = (A_IMG_SECTION % {'id': ID['svcimg'], 'url': '{{IMG_URL_1}}', 'alt': jattr(TINTS[0][1]), 'w': 512, 'h': 416}).replace('\\u002d\\u002d', '--')
    pic = ('<!-- wp:uagb/image %s -->\n<div class="wp-block-uagb-image uagb-block-%s wp-block-uagb-image--layout-default wp-block-uagb-image--effect-static wp-block-uagb-image--align-none"><figure class="wp-block-uagb-image__figure">'
           '<img srcset="{{IMG_URL_1}} ,{{IMG_URL_1}} 780w, {{IMG_URL_1}} 360w" sizes="(max-width: 480px) 150px" src="{{IMG_URL_1}}" alt="%s" width="200" height="200" title="%s" role="img"/></figure></div>\n'
           '<!-- /wp:uagb/image -->') % (a, ID['svcimg'], esc(TINTS[0][1]), esc(TINTS[0][1]))
    return ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s">%s\n\n'
            '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"></div>\n<!-- /wp:uagb/advanced-heading -->\n\n'
            '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h3 class="uagb-heading-text"><a href="/%s/">Chair Massage</a></h3><p class="uagb-desc-text">%s</p></div>\n'
            '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container -->\n\n') % (
        ROW_ATTRS % ID['svcrow'], ID['svcrow'], pic, A_SPACER.replace('\\u002d\\u002d', '--') % ID['svcsp'], ID['svcsp'], A_SECTION_TEXT % ID['svctxt'], ID['svctxt'], SLUG, ROW_DESC)


# ---- the two snippet insertions (exact single-occurrence replacements inside the snippets' current code) ----
def band_code():
    """CorporateBand as it is stored on staging38 (and live) since the booking-date change: booking-date-content.php `band_new`."""
    p = os.path.join(HERE, '..', 'booking-date', 'booking-date-content.php')
    s = open(p, encoding='utf-8').read()
    m = re.search(r"<<<'(\w+)'\n", s)
    body = s[m.end():s.rindex('\n' + m.group(1))]
    return json.loads(body)['band_new']


def jump_code():
    """A11yPageJumpLinks as stored (the installer's CODE heredoc plus the newline WPCode keeps at the end)."""
    s = open(os.path.join(HERE, '..', 'accessibility', 'install-jump-links.php'), encoding='utf-8').read()
    a = s.index("<<<'CODE'\n") + len("<<<'CODE'\n")
    return s[a:s.index('\nCODE;', a)] + '\n'


BAND_OLD = "'thai-massage', 'therapeutic-massage', 'deep-tissue-massage' )"
BAND_NEW = "'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' )"
JUMP_OLD = "\t\t'deep-tissue-massage' => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'What to expect', 'h', 'What To Expect' ) ),\n"
JUMP_NEW = JUMP_OLD + "\t\t'chair-massage'       => array( array( 'What it is', 'h', 'What Is' ), array( 'Common reasons', 'h', 'Common Reasons' ), array( 'Who can benefit', 'h', 'Who Can Benefit' ), array( 'What to expect', 'h', 'What To Expect' ) ),\n"
BAND_SHA256 = 'f5ec377ac66a414658aa8d926c80e318b2316ba4f2b7f47778ff3fcf7e92872a'   # staging38 snippet 1268, inspect chair-insp-snips-38, 2026-10-31
JUMP_SHA256 = 'e706b157113eaa9762e55a6c2f2dabfa65ce21f97cb0dde4ef6534b54c52e08d'   # staging38 snippet 1232, same inspect


def build():
    content, cite = build_page()
    for k in SRC:
        if k not in cite.order:
            raise SystemExit('registry has a source the text never cites: ' + k)
    band = band_code()
    jump = jump_code()
    if hashlib.sha256(band.encode()).hexdigest() != BAND_SHA256 or band.count(BAND_OLD) != 1 or BAND_NEW in band:
        raise SystemExit('the CorporateBand code is not the frozen staging38 version, or the page list is not as expected')
    if hashlib.sha256(jump.encode()).hexdigest() != JUMP_SHA256 or jump.count(JUMP_OLD) != 1 or "'chair-massage'" in jump:
        raise SystemExit('the A11yPageJumpLinks code is not the frozen staging38 version, or the plan list is not as expected')
    # ---- wording gate: the page as a reader sees it (markers filled with harmless values) ----
    filled = content
    for n in range(5):
        filled = filled.replace('{{IMG_URL_%d}}' % n, '/wp-content/uploads/x%d.webp' % n)
    faq_text = ' '.join(q + ' ' + a for q, a in FAQ)
    res = wt.check(filled + ' <p>' + html_mod.escape(faq_text) + '</p>')   # the FAQ lives in block attributes a tag-stripper cannot see: appended as text before the heading is NOT possible, so check it separately below
    res_faq = wt.hits(wt.text(html_mod.escape(faq_text)), wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
    res_meta = wt.hits(wt.text(' '.join([SEO_TITLE, SEO_DESC, PAGE_TITLE, H1, HERO['alt']] + [a for _, a in TINTS] + [ROW_DESC])), wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
    problems = []
    if not res['found']:
        problems.append('research heading not found exactly once (wording_tiers.RESEARCH_HEADINGS must list it)')
    if res['before']:
        problems.append('restricted wording outside the research section: %s' % res['before'])
    if res['research']:
        problems.append('claim verbs or voice words inside the research section: %s' % res['research'])
    if res['cites']:
        problems.append('voice words inside a source title: %s' % res['cites'])
    if res_faq:
        problems.append('restricted wording in the FAQ: %s' % res_faq)
    if res_meta:
        problems.append('restricted wording in the title, description, alt texts or Services row: %s' % res_meta)
    if len(SEO_TITLE) > 60 or len(SEO_DESC) > 160:
        problems.append('SEO title or description too long (%d / %d)' % (len(SEO_TITLE), len(SEO_DESC)))
    if problems:
        raise SystemExit('WORDING GATE FAILED:\n  ' + '\n  '.join(problems))
    before_text = wt.split_page(filled)[0]
    return {
        'slug': SLUG, 'title': PAGE_TITLE, 'h1': H1, 'seo_title': SEO_TITLE, 'seo_desc': SEO_DESC,
        'ids': sorted(ID.values()),
        'page_content': content,
        'hero': HERO, 'images': tint_images(),
        'row': build_row(), 'row_anchor': ROW_ANCHOR, 'row_id': ID['svcrow'],
        'band_title': 'CorporateBand', 'band_sha256': BAND_SHA256, 'band_old': BAND_OLD, 'band_new': BAND_NEW,
        'jump_title': 'A11yPageJumpLinks', 'jump_sha256': JUMP_SHA256, 'jump_old': JUMP_OLD, 'jump_new': JUMP_NEW,
        'faq_text': faq_text, 'before_text': before_text, 'research_heading': RESEARCH_HEADING,
        'faq_questions': [q for q, _ in FAQ], 'source_count': len(cite.order), 'source_urls': [SRC[k][0] for k in cite.order],
        'panel_titles': [t for t, _, _ in PANELS] + ['Sources'],
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'CHAIRJSON' not in js
    return ('<?php\n// GENERATED by build-chair-massage.py: do not edit by hand (edit the builder and run it again). install-chair-massage.php loads this.\n'
            'return json_decode( <<<\'CHAIRJSON\'\n%s\nCHAIRJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', html_mod.unescape(data['page_content']))))
        print()
        print('FAQ:', data['faq_text'])
        print()
        print('ROW:', re.sub(r'<[^>]+>', ' ', data['row']))
        sys.exit(0)
    out = php(data)
    if '--check' in sys.argv:
        cur = open(OUT, encoding='utf-8').read() if os.path.isfile(OUT) else None
        ok = cur == out
        print(('PASS ' if ok else 'FAIL ') + os.path.basename(OUT) + (' matches its builder' if ok else ' is not what the builder produces (run the builder, review the diff, commit)'))
        sys.exit(0 if ok else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(out)
    print('wrote %s (%d bytes; page %d bytes, %d sources, %d FAQ, %d block ids)' % (os.path.relpath(OUT, HERE), len(out), len(data['page_content']), data['source_count'], len(FAQ), len(data['ids'])))

# ref: ../thai/build-thai-evidence.py — attrs/esc/para/details helpers and the research-heading attributes; ../therapeutic/build-therapeutic-evidence.py — the shared source entries;
# ref: ../corporate/build-corporate-massage.py — the Services-row pattern; ../client-pages/wording_tiers.py — the wording gate; ./make-tints.py — the four pictures

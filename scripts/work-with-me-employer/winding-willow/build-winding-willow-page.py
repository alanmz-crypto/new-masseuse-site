#!/usr/bin/env python3
"""Build winding-willow-content.php: the exact text and block markup that install-winding-willow-page.php ADDS as the new, unlisted page /winding-willow/. Standard library only.

    python3 -I -B scripts/work-with-me-employer/winding-willow/build-winding-willow-page.py          write winding-willow-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/winding-willow/build-winding-willow-page.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/winding-willow/build-winding-willow-page.py --text   print the visible text (for review)

SOURCE OF THE REQUEST (Ryan, 2026-11-12/13, ButsabaJobScout session): Butsaba ("Sa") now offers massage at Winding Willow Acupuncture & Wellness. Ryan: "let's reframe our narative to how
Winding Willow offers a service that pairs well with other options ... let's try to keep things equal so we don't say go for the massage rather than the acupuncture or other things offered by
Winding Willow because we want people to consider Winding Willow as a whole and not just Butsaba"; the research section at the bottom "like all the other service pages", sandwiched
("A positive note before the disappointment/or-worse and a positive note after"); then "move it to staging so it follows all the rules of thaisolitude.com". Asked and answered 2026-11-13:
an UNLISTED page (no menu item, no Services row, no snippet change), the research heading registered in wording_tiers.py, "treatment" reworded (a claim verb). Target staging39.
The booking link (Ryan, 2026-11-13): Sa's Winding Willow profile, https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt (her ChARM calendar is embedded there).

FACTS: Winding Willow's own pages, fetched 2026-11-12 (services, all providers, acupuncture and massage providers, Sa's profile and calendar, Book Now). The evidence behind the
research section and every limit on what the page may claim (no coordinated-care claim, "complementary" is not "synergistic") is the audit note in Ryan's ButsabaJobScout workspace,
Research/Butsaba_Dusrai_Winding_Willow_Massage_Complementarity_Evidence_Audit_Portland_2026-11-12.md. Every study sentence was written against the paper's PubMed abstract.
Equal weight: the three service cards are alphabetical and the same size; both hero buttons have the same style. Prices and hours are left off (they change; Winding Willow's pages disagree on hours).

LAYOUT: the hero, the section rows and the cards reuse the Chair Massage page's Spectra attribute strings (copied there from the Therapeutic page), with fresh block ids; the research
heading, intro, slide-open details panels and Sources list use the Thai builder's helpers, exactly as on the other service pages. Buttons are the core outline buttons of the Contact page.

WORDING RULES (AGENTS.md, wording_tiers.py): first person, never we/our/us; the claim verbs never in prose; the OBMT evidence vocabulary only inside the research section
("What the Research Says About Massage Alongside Other Care" to the end of the page); study populations in plain words; titles verbatim in Sources. This builder refuses to write the file otherwise.
"""
import hashlib
import html as html_mod
import importlib.util
import json
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'winding-willow-content.php')


def _load(name, rel):
    spec = importlib.util.spec_from_file_location(name, os.path.join(HERE, rel))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


tb = _load('thai_evidence_builder', '../thai/build-thai-evidence.py')
cm = _load('chair_massage_builder', '../chair-massage/build-chair-massage.py')
wt = _load('wording_tiers', '../client-pages/wording_tiers.py')
esc, para, details = tb.esc, tb.para, tb.details

SLUG = 'winding-willow'
PAGE_TITLE = 'Massage at Winding Willow'
H1 = 'Massage with Sa at Winding Willow Acupuncture & Wellness'
SEO_TITLE = 'Massage at Winding Willow | Butsaba Dusrai, Oregon LMT'
SEO_DESC = 'Thai, deep tissue and Swedish massage with Butsaba “Sa” Dusrai, LMT, at Winding Willow Acupuncture & Wellness, 1540 SE Clinton St, Portland.'
RESEARCH_HEADING = 'What the Research Says About Massage Alongside Other Care'

WW = 'https://www.windingwillowacupuncture.com'
BOOK_SA = WW + '/butsaba-dusrai-lmt'   # Ryan, 2026-11-13: the "Book massage with Sa" link
LINKS = {'home': WW + '/', 'book_sa': BOOK_SA, 'acu': WW + '/acupuncture-providers', 'services': WW + '/our-services-and-blog',
         'massage': WW + '/massage-therapy-providers', 'book_now': WW + '/book-now', 'all': WW + '/all-providers-and-staff'}


def bid(name):
    """A fresh 8-hex block id, deterministic for this page (the installer refuses any id that already occurs in another post)."""
    return hashlib.sha1(('ts-winding-willow-2026-11-13:' + name).encode()).hexdigest()[:8]


# ---- the text ------------------------------------------------------------------------------------------------------------------------------------------
LEDE = ('Winding Willow is a neighborhood collective of independent practitioners in Southeast Portland. Acupuncture, herbal and nutrition consultations, '
        'and several styles of bodywork are all under one roof, and my massage is now one of them.')

OFFERED_HEAD = 'What’s Offered at Winding Willow'
OFFERED = ('Each practitioner works independently and books their own appointments. You can stay with the care that works for you, '
           'or try something different without changing clinics.')
CARDS = [   # alphabetical, the same size: massage is one card among several, and Sa one of five massage therapists in it
    ('Acupuncture',
     'Several licensed acupuncturists with different styles, including Japanese-style and orthopedic/sports acupuncture. Some also offer cupping and moxibustion.',
     'Dr. Jennifer Moody · Erin Cranford · Lindsey Pope', 'acu', 'Meet the acupuncturists'),
    ('Herb, Supplement & Nutrition Consults',
     'Consultations on Chinese herbal medicine, supplements and nutrition with practitioners trained in those fields.',
     'Herbalism: Dr. Moody, Erin Cranford · Nutrition: Lindsey Pope', 'services', 'See the services'),
    ('Massage & Bodywork',
     'Five massage therapists offering different approaches: Shiatsu, Swedish and deep tissue, myofascial and craniosacral work, movement-informed massage, and Thai massage.',
     'Tina Rea · Mitchy Fosnaugh · Quinn Linden Gumbiner · Joseph Liu · Butsaba “Sa” Dusrai', 'massage', 'Meet the massage therapists'),
]

SA_HEAD = 'Massage with Sa'
SA_INTRO = 'I offer three kinds of massage at Winding Willow, each in 60- or 90-minute sessions:'
SA_LIST = [('Thai massage', ', fully clothed, on a floor futon, with rhythmic pressure and assisted stretching'),
           ('Deep tissue & neuromuscular massage', ', with slow, focused work on tight, tender spots, on the table'),
           ('Swedish massage', ' for a gentler, relaxing session')]
SA_AFTER = 'Heated towels, hot stones or aromatherapy can be part of a session if you’d like them. On your first visit, book a “Massage Initial” appointment.'

BRANCH_HEAD = 'Branching Out'
BRANCH = ('Different approaches suit different people and different days. Because everything is in one place, it’s easy to try something new.', [
    ('Already seeing an acupuncturist there?', 'You can book a Thai, deep tissue or Swedish massage with me as a separate visit, at the same clinic.'),
    ('Coming for massage?', 'You can try acupuncture, Shiatsu or an herbal consult with another Winding Willow practitioner in the same way.'),
    ('Not sure where to start?', 'Winding Willow’s site introduces every practitioner, so you can compare their approaches and choose.'),
])

BEFORE_HEAD = 'Before You Book'
BEFORE = ['Tell each practitioner about any health conditions, medications, supplements, pregnancy, recent injury or surgery, and any other care you’re receiving. '
          'This matters for every service, from deep pressure to needling to herbs.',
          'New clients register through Winding Willow’s booking system and fill out intake forms before the first visit. Winding Willow’s {book_now|Book Now page} '
          'explains its cancellation policy and insurance billing.']

FIND_HEAD = 'Find Winding Willow'
FIND = '1540 SE Clinton St, Portland, OR 97202<br>Entrance at SE Clinton &amp; SE 16th · ADA accessible'

# ---- research: the audit note's findings, sandwiched (positive, then the weaker and negative results in full, then a positive close) -------------------
PM = 'https://pubmed.ncbi.nlm.nih.gov/%s/'
SRC = {
    'kumnerddee': (PM % '21299184', 'Kumnerddee W.', 'Effectiveness comparison between Thai traditional massage and Chinese acupuncture for myofascial back pain in Thai military personnel: a preliminary report', 'J Med Assoc Thai, 2009.'),
    'juntakarn': (PM % '28690703', 'Juntakarn C, Prasartritha T, Petrakard P.', 'The Effectiveness of Thai Massage and Joint Mobilization', 'Int J Ther Massage Bodywork, 2017.'),
    'bingolbali': (PM % '36645811', 'Bingölbali Ö, Taşkaya C, Alkan H, Altındağ Ö.', 'The effectiveness of deep tissue massage on pain, trigger point, disability, range of motion and quality of life in individuals with myofascial pain syndrome', 'Somatosens Mot Res, 2024.'),
    'zheng': (PM % '23427384', 'Zheng Z, Wang J, Gao Q, Hou J, Ma L, Jiang C, Chen G.', 'Therapeutic evaluation of lumbar tender point deep massage for chronic non-specific low back pain', 'J Tradit Chin Med, 2012.'),
    'ucurum': (PM % '40954653', 'Ucurum SG, Celenay ST, Keskin M, Kaya DO.', 'Short-term effects of classical massage added to a conventional physiotherapy on non-specific chronic low back Pain: A randomized, placebo-controlled study', 'J Bodyw Mov Ther, 2025.'),
    'siriyong': (PM % '42142427', 'Siriyong T, Mangkang J, Yotmadee P.', 'Clinical efficacy of court-type traditional Thai massage and acupuncture for low back pain management: a randomized controlled trial', 'Explore (NY), 2026.'),
    'laosee': (PM % '31987237', 'Laosee O, Sritoomma N, Wamontree P, Rattanapan C, Sitthi-Amorn C.', 'The effectiveness of traditional Thai massage versus massage with herbal compress among elderly patients with low back pain: A randomised controlled trial', 'Complement Ther Med, 2020.'),
}
RES_INTRO = ('Research on how massage fits with other kinds of care is limited. The studies are small, most compare one approach with another or add one kind of care to another, '
             'and most follow people for only a few weeks. The summaries below describe what the studies reported. They are general information, not medical advice or a promise of any result, '
             'and people respond differently. Each part starts with what the studies found in massage’s favor, then gives the results that were weaker, mixed or negative, and ends on what is '
             'encouraging: I included the studies I found with disappointing results, not only the favorable ones.')
PANELS = [
    ('Massage and Acupuncture Compared', True, [
        'Trials that compared Thai massage directly with another approach found that people improved with both. In 18 military personnel with back pain linked to tender spots in the muscles, '
        'five sessions of Thai massage or of acupuncture over 10 days were each followed by less pain, with the benefit beginning after the first session.{kumnerddee} In a hospital trial in Bangkok, '
        '120 adults had Thai massage or joint mobilization twice a week for four weeks, and both groups improved by similar amounts on pain and everyday-activity scores.{juntakarn}',
        'These trials do not name a clear winner, and they have limits. In the military trial, pain ratings did not differ between the groups, but one pain-questionnaire score was lower after '
        'acupuncture, and only the acupuncture group showed a higher tolerance for pressure on tender points.{kumnerddee} The Bangkok trial had no group that received neither approach, so it cannot '
        'show how much of the change was due to the sessions themselves.{juntakarn}',
        'The encouraging part is that people improved with each approach, so you can choose by what feels right to you. At Winding Willow, both are offered under one roof.',
    ]),
    ('Massage Added to Other Care', False, [
        'Three small trials added massage to other care and found more improvement than that care alone. In 80 adults with neck pain linked to tender spots in the muscles, 12 sessions of deep '
        'tissue massage on top of electrical stimulation, hot packs and ultrasound improved pain, a neck-disability score, quality of life and most neck movements more than those three alone.{bingolbali} '
        'In 60 adults with chronic low back pain, deep massage on tender points plus traction was followed by less pain and muscle hardness, and a higher tolerance for pressure, than traction alone.{zheng} '
        'In 41 adults with chronic low back pain who all had physiotherapy, both real and placebo massage were followed by less pain and disability, and only real massage improved quality of life.{ucurum}',
        'Adding other approaches to massage has been less clear. In 140 adults with low back pain, adding acupuncture to court-type Thai massage made no additional difference after one month of '
        'sessions or at a one-month follow-up.{siriyong} In 140 older adults with low back pain, Thai massage with an herbal compress and Thai massage alone led to similar improvements over 15 weeks, '
        'so the compress added no clear benefit.{laosee} The trials that added massage were small and followed people for weeks, not months, and the care massage was added to was physical-therapy '
        'care, not services offered at Winding Willow.',
        'Even so, both groups did well in the two Thai massage trials. In the larger back-pain trial, people who had Thai massage alone and people who also had acupuncture both reported clinically '
        'meaningful reductions in pain and moved more easily.{siriyong} In the herbal-compress trial, both groups improved.{laosee} What I can promise is a choice of skilled practitioners in one '
        'welcoming place.',
    ]),
]
SOURCES_NOTE = 'Each number above links to the source. Titles are quoted as published.'

# ---- block ids ----------------------------------------------------------------------------------------------------------------------------------------
ID = {k: bid(k) for k in ['hero', 'h1', 'main', 'row1', 'txt1', 'cards', 'card1', 'cardh1', 'card2', 'cardh2', 'card3', 'cardh3',
                           'row2', 'txt2', 'row3', 'txt3', 'row4', 'txt4', 'row5', 'txt5', 'research']}
assert len(set(ID.values())) == len(ID), 'block id collision'

# Hero: the Chair/Therapeutic hero container without its two-column grid (this page has no picture), so the heading, the intro and the buttons stack.
A_HERO_WRAP = re.sub(r',"layout":"grid".*\}$', '}', cm.A_HERO_WRAP)
assert '"isBlockRootParent":true' in A_HERO_WRAP and 'grid' not in A_HERO_WRAP
# Cards: the steps grid with three equal columns instead of two.
_COL = '{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}'
A_CARDS_WRAP = cm.A_STEPS_WRAP.replace('"gridColumnDesktop":[%s,%s]' % (_COL, _COL), '"gridColumnDesktop":[%s,%s,%s]' % (_COL, _COL, _COL))
# Main wrapper: the Chair page's, with less top padding (180/140 px there makes room for the hero picture that overlaps it; this page has no picture).
A_MAIN_WRAP = cm.A_MAIN_WRAP.replace('"topPaddingDesktop":180', '"topPaddingDesktop":80').replace('"topPaddingTablet":140', '"topPaddingTablet":60').replace('"topPaddingMobile":140', '"topPaddingMobile":48')
assert A_MAIN_WRAP.count('"topPaddingDesktop":80') == 1 and A_MAIN_WRAP.count('"topPaddingMobile":48') == 1
assert A_CARDS_WRAP != cm.A_STEPS_WRAP and A_CARDS_WRAP.count('"gridColumnDesktop":[%s,%s,%s]' % (_COL, _COL, _COL)) == 1


def link(key, label):
    return '<a href="%s">%s</a>' % (LINKS[key], esc(label))


def inline(text):
    """esc() the text, then turn {key|label} into a link to Winding Willow's page."""
    out = esc(text)
    return re.sub(r'\{([a-z_]+)\|([^}]+)\}', lambda m: link(m.group(1), m.group(2)), out)


def buttons(pairs):
    """Core outline buttons (the Contact page's markup): every button the same style, so neither choice is pushed."""
    items = ''.join('<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="%s">%s</a></div><!-- /wp:button -->'
                    % (LINKS[k], esc(label)) for k, label in pairs)
    return '<!-- wp:buttons --><div class="wp-block-buttons">%s</div><!-- /wp:buttons -->' % items


def row(n, heading, desc_html, extra=(), last=False):
    """A section row: the Chair page's text row (rule underneath unless last) with one heading block, then any extra blocks."""
    a_row = (cm.A_TEXT_ROW_LAST if last else cm.A_TEXT_ROW) % ID['row%d' % n]
    head = ('<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h2 class="uagb-heading-text">%s</h2><p class="uagb-desc-text">%s</p></div>\n'
            '<!-- /wp:uagb/advanced-heading -->') % (cm.A_SECTION_TEXT_WIDE % ID['txt%d' % n], ID['txt%d' % n], esc(heading), desc_html)
    return ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-block-%s">%s</div>\n<!-- /wp:uagb/container -->') % (
        a_row, ID['row%d' % n], '\n\n'.join([head] + list(extra)))


def cards():
    out = []
    for i, (title, desc, who, key, label) in enumerate(CARDS, 1):
        body = '%s<br><br><em>%s</em><br><br>%s →' % (esc(desc), esc(who), link(key, label))
        out.append('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-block-%s"><!-- wp:uagb/advanced-heading %s -->\n'
                   '<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h3 class="uagb-heading-text">%s</h3><p class="uagb-desc-text">%s</p></div>\n'
                   '<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container -->' % (cm.A_STEP_CARD % ID['card%d' % i], ID['card%d' % i], cm.A_STEP_TEXT % ID['cardh%d' % i], ID['cardh%d' % i], esc(title), body))
    return ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s alignfull uagb-is-root-container"><div class="uagb-container-inner-blocks-wrap">%s</div></div>\n'
            '<!-- /wp:uagb/container -->') % (A_CARDS_WRAP % ID['cards'], ID['cards'], '\n\n'.join(out))


def research(cite):
    panels = [details(title, opened, [para(cite.mark(esc(p)), plain=True) for p in paras]) for title, opened, paras in PANELS]
    items = ['<!-- wp:list-item -->\n<li><cite class="ts-source">%s <a href="%s">%s</a>. %s</cite></li>\n<!-- /wp:list-item -->' % (esc(SRC[k][1]), SRC[k][0], esc(SRC[k][2]), esc(SRC[k][3]))
             for k in cite.order]
    sources_list = '<!-- wp:list {"ordered":true,"style":{"typography":{"fontWeight":"400"}}} -->\n<ol class="wp-block-list" style="font-weight:400">%s</ol>\n<!-- /wp:list -->' % '\n\n'.join(items)
    panels.append(details('Sources', False, [para(SOURCES_NOTE, plain=True), sources_list]))
    head = ('<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><div class="uagb-separator"></div><h2 class="uagb-heading-text">%s</h2></div>\n'
            '<!-- /wp:uagb/advanced-heading -->') % (tb.HEAD_ATTRS % ID['research'], ID['research'], esc(RESEARCH_HEADING))
    return '\n\n'.join([head, para(esc(RES_INTRO))] + panels)


class Cite(cm.Cite):
    def mark(self, text):
        def repl(m):
            key = m.group(1)
            if key not in SRC:
                raise SystemExit('unknown source key: ' + key)
            if key not in self.order:
                self.order.append(key)
            return '<a href="%s">[%d]</a>' % (SRC[key][0], self.order.index(key) + 1)
        return re.sub(r'\{([a-z0-9_]+)\}', repl, text)


def build_page():
    cite = Cite()
    hero = ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-block-%s default uagb-is-root-container"><!-- wp:uagb/advanced-heading %s -->\n'
            '<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h1 class="uagb-heading-text">%s</h1></div>\n<!-- /wp:uagb/advanced-heading -->\n\n%s\n\n%s</div>\n<!-- /wp:uagb/container -->') % (
        A_HERO_WRAP % ID['hero'], ID['hero'], cm.A_H1 % ID['h1'], ID['h1'], esc(H1), para(esc(LEDE)),
        buttons([('book_sa', 'Book massage with Sa'), ('home', 'Explore Winding Willow')]))
    sa_list = '<!-- wp:list -->\n<ul class="wp-block-list">%s</ul>\n<!-- /wp:list -->' % ''.join(
        '<!-- wp:list-item -->\n<li><strong>%s</strong>%s</li>\n<!-- /wp:list-item -->' % (esc(a), esc(b)) for a, b in SA_LIST)
    branch_desc = esc(BRANCH[0]) + ''.join('<br><br><strong>%s</strong> %s' % (esc(q), esc(a)) for q, a in BRANCH[1])
    rows = [
        row(1, OFFERED_HEAD, esc(OFFERED)),
        cards(),
        row(2, SA_HEAD, esc(SA_INTRO), [sa_list, para(esc(SA_AFTER)), buttons([('book_sa', 'See Sa’s calendar')])]),
        row(3, BRANCH_HEAD, branch_desc),
        row(4, BEFORE_HEAD, '<br><br>'.join(inline(p) for p in BEFORE)),
        row(5, FIND_HEAD, FIND, [buttons([('book_sa', 'Book massage with Sa'), ('all', 'All practitioners')])], last=True),
    ]
    main = ('<!-- wp:uagb/container %s -->\n<div class="wp-block-uagb-container uagb-block-%s alignfull uagb-is-root-container"><div class="uagb-container-inner-blocks-wrap">%s</div></div>\n<!-- /wp:uagb/container -->') % (
        A_MAIN_WRAP % ID['main'], ID['main'], '\n\n'.join(rows))
    return '\n\n'.join([hero, main, research(cite)]) + '\n', cite


def build():
    content, cite = build_page()
    for k in SRC:
        if k not in cite.order:
            raise SystemExit('registry has a source the text never cites: ' + k)
    res = wt.check(content)
    res_meta = wt.hits(wt.text(' '.join([SEO_TITLE, SEO_DESC, PAGE_TITLE, H1])), wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
    problems = []
    if not res['found']:
        problems.append('research heading not found exactly once (wording_tiers.RESEARCH_HEADINGS must list it)')
    if res['before']:
        problems.append('restricted wording outside the research section: %s' % res['before'])
    if res['research']:
        problems.append('claim verbs or voice words inside the research section: %s' % res['research'])
    if res['cites']:
        problems.append('voice words inside a source title: %s' % res['cites'])
    if res_meta:
        problems.append('restricted wording in the title or description: %s' % res_meta)
    if len(SEO_TITLE) > 60 or len(SEO_DESC) > 160:
        problems.append('SEO title or description too long (%d / %d)' % (len(SEO_TITLE), len(SEO_DESC)))
    if re.search(r'trigger[- ]point|myofascial pain syndrome|\bsynerg', wt.split_page(content)[0], re.I):
        problems.append('a clinical label or a synergy claim outside the Sources titles')
    if problems:
        raise SystemExit('WORDING GATE FAILED:\n  ' + '\n  '.join(problems))
    return {
        'slug': SLUG, 'title': PAGE_TITLE, 'h1': H1, 'seo_title': SEO_TITLE, 'seo_desc': SEO_DESC,
        'ids': sorted(ID.values()), 'page_content': content, 'research_heading': RESEARCH_HEADING,
        'before_text': wt.split_page(content)[0], 'book_url': BOOK_SA,
        'source_count': len(cite.order), 'source_urls': [SRC[k][0] for k in cite.order],
        'panel_titles': [t for t, _, _ in PANELS] + ['Sources'],
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'WWJSON' not in js
    return ('<?php\n// GENERATED by build-winding-willow-page.py: do not edit by hand (edit the builder and run it again). install-winding-willow-page.php loads this.\n'
            'return json_decode( <<<\'WWJSON\'\n%s\nWWJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', html_mod.unescape(data['page_content']))))
        sys.exit(0)
    out = php(data)
    if '--check' in sys.argv:
        cur = open(OUT, encoding='utf-8').read() if os.path.isfile(OUT) else None
        ok = cur == out
        print(('PASS ' if ok else 'FAIL ') + os.path.basename(OUT) + (' matches its builder' if ok else ' is not what the builder produces (run the builder, review the diff, commit)'))
        sys.exit(0 if ok else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(out)
    print('wrote %s (%d bytes; page %d bytes, %d sources, %d block ids)' % (os.path.relpath(OUT, HERE), len(out), len(data['page_content']), data['source_count'], len(data['ids'])))

# ref: ../chair-massage/build-chair-massage.py — the Spectra attribute strings reused here; ../thai/build-thai-evidence.py — attrs/esc/para/details and the research heading;
# ref: ../client-pages/wording_tiers.py — the wording gate (the research heading is registered there)

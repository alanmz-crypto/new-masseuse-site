#!/usr/bin/env python3
"""Build seo-booking.json: the exact old and new values that install-seo-booking.php swaps. Standard library only.

    python3 -I -B scripts/work-with-me-employer/seo-booking/build-seo-booking.py          write seo-booking.json next to this file
    python3 -I -B scripts/work-with-me-employer/seo-booking/build-seo-booking.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/seo-booking/build-seo-booking.py --text   print every old -> new pair (for review)

SOURCE (Ryan, 2026-11-20): "now, i want you to approach the whole website from an seo perspective. the site is very low on impressions and very low on clicks",
then, after the before/after list was shown: "make changes so the SEO gets better scores. that's all i want".
WHY: Search Console (2026-08-20..11-17) shows the searches that find the site are people looking to book a massage ("thai massage portland", "massage near me"),
but the Thai, Therapeutic and Deep Tissue pages answer "How Do I Book a Session?" with "Individual appointments are planned for summer 2027", while Butsaba
("Sa") sees individual clients now at Winding Willow (the /winding-willow/ page, LIVE 2026-11-20). This tells searchers, in the answer and in the search result, where to book.

WHAT THIS CHANGES (every old value must be exactly the reviewed one, or the run stops; nothing else is touched):
  1. The "How Do I Book a Session?" FAQ answer on Thai, Therapeutic and Deep Tissue: ONE sentence appended (nothing reworded). Plain text, no link:
     Ryan 2026-11-20 made the Winding Willow menu item the one link to /winding-willow/ (push-winding-willow.sh checks it), so no new link is added anywhere.
  2. The search-result description (SureRank) of Thai, Deep Tissue and Home: the same text with where to book. Titles stay exactly as they are. Therapeutic keeps its description.
  3. The LocalBusiness structured data (WPCode snippet 1023 "LocalBusinessInfo"): the founder reference gains sameAs = her Winding Willow profile, so search engines can join
     the two pages about the same person. Nothing visible.
WORDING: Winding Willow's page lists Thai, deep tissue and Swedish massage for Sa, so the sentence names exactly those. The house word list applies (checked by the installer too).
"""
import hashlib
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'seo-booking.json')

# 1. the FAQ answer, as stored on staging40 (inspect seo-i-thai, 2026-11-20: Bundle D wording "&amp;", booking date November 14, 2026)
BOOK_Q = '"question":"How Do I Book a Session?","answer":"'
BOOK_OLD_TEXT = ('Corporate and group massage bookings begin November 14, 2026. The Corporate &amp; Group Massage page explains how to request a quote. '
                 'Individual appointments are planned for summer 2027.')
ADDED = (' Until then, you can book an individual Thai, deep tissue or Swedish massage with me at Winding Willow Acupuncture &amp; Wellness, '
         '1540 SE Clinton St, Portland.')
PAIRS = []
for slug, label in (('thai-massage', 'Thai Massage'), ('therapeutic-massage', 'Therapeutic Massage'), ('deep-tissue-massage', 'Deep Tissue Massage')):
    PAIRS.append({'slug': slug, 'label': label + ': FAQ "How Do I Book a Session?"',
                  'old': BOOK_Q + BOOK_OLD_TEXT + '"', 'new': BOOK_Q + BOOK_OLD_TEXT + ADDED + '"'})

# 2. search-result descriptions (titles unchanged; 'front' = the static front page)
META = [
    {'slug': 'thai-massage', 'label': 'Thai Massage', 'title': 'Thai Massage in Portland | Butsaba Dusrai, Oregon LMT',
     'old': 'Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.',
     'new': 'Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: compression, rocking and assisted stretching. Book with her at Winding Willow on SE Clinton.'},
    {'slug': 'deep-tissue-massage', 'label': 'Deep Tissue Massage', 'title': 'Deep Tissue Massage in Portland | Butsaba Dusrai, LMT',
     'old': 'Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.',
     'new': 'Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles. Book with her at Winding Willow on SE Clinton St.'},
    {'slug': 'front', 'label': 'Home', 'title': None,
     'old': 'Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT.',
     'new': 'Thai, therapeutic and deep tissue massage in Portland with Butsaba Dusrai, LMT. Book at Winding Willow, or ask about corporate and group massage.'},
]
for m in META:
    assert len(m['new']) <= 160 and m['new'].endswith('.'), (m['label'], len(m['new']))

# 3. the LocalBusiness snippet: one exact line
LB_OLD = open(os.path.join(HERE, 'localbusiness-v1.txt'), encoding='utf-8').read()
assert hashlib.sha256(LB_OLD.encode()).hexdigest() == 'ddb3d9639f0a58c1038082f62943650f086282974cc9197882983d8b99d0d72b'   # snippet 1023 on staging40 (inspect seo-i-lb, 2026-11-20)
FOUNDER_OLD = "        'founder' => array( '@id' => $home . '/#person' ),\n"
FOUNDER_NEW = "        'founder' => array( '@id' => $home . '/#person', 'sameAs' => array( 'https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt' ) ),\n"
assert LB_OLD.count(FOUNDER_OLD) == 1
LB_NEW = LB_OLD.replace(FOUNDER_OLD, FOUNDER_NEW)


def build():
    return {'pairs': PAIRS, 'meta': META, 'snippet': {'id': 1023, 'title': 'LocalBusinessInfo', 'old': LB_OLD, 'new': LB_NEW,
                                                      'old_sha256': hashlib.sha256(LB_OLD.encode()).hexdigest(), 'new_sha256': hashlib.sha256(LB_NEW.encode()).hexdigest()}}


def body(data):
    return json.dumps(data, ensure_ascii=False, indent=1) + '\n'


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        for p in data['pairs']:
            print('== %s [%s]\n  OLD: %s\n  NEW: %s\n' % (p['label'], p['slug'], p['old'], p['new']))
        for m in data['meta']:
            print('== %s description (%d -> %d characters)\n  OLD: %s\n  NEW: %s\n' % (m['label'], len(m['old']), len(m['new']), m['old'], m['new']))
        print('== LocalBusinessInfo snippet\n  OLD: %s  NEW: %s' % (FOUNDER_OLD, FOUNDER_NEW))
        sys.exit(0)
    b = body(data)
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('seo-booking.json matches the builder' if have == b else 'seo-booking.json is OUT OF DATE: run the builder')
        sys.exit(0 if have == b else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(b)
    print('wrote %s (%d bytes): %d FAQ sentences, %d descriptions, 1 snippet line' % (OUT, len(b.encode()), len(PAIRS), len(META)))

# ref: ./install-seo-booking.php — the installer that applies this; ../availability/build-availability-update.py — the pattern this follows
# ref: ../winding-willow/winding-willow-page.html — the page these sentences point to (services named there)

#!/usr/bin/env python3
"""Build home-card-content.php: the exact markup that install-home-corporate-card.php inserts at the TOP of "The Massage I Offer" on the Home page. Standard library only.

    python3 -I -B scripts/work-with-me-employer/home-card/build-home-corporate-card.py          write home-card-content.php and home-corporate-card.css next to this file
    python3 -I -B scripts/work-with-me-employer/home-card/build-home-corporate-card.py --check  exit 1 if either file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/home-card/build-home-corporate-card.py --text   print the visible text (for review)

SOURCE (Ryan, 2026-10-25, after approving the availability update: "put Corporate and Group at the top of 'Massage I Offer' on the home page now. Make it harmonize with the other services on the page.")
HOW IT HARMONIZES: the new card is a CLONE of the existing first card (card-template.txt = the stored Therapeutic Massage card on staging38, taken 2026-10-25): the same grid row, the same title and description
blocks and fonts, the same outlined "link" button, the same 16 px-rounded picture on the right, the same rule underneath, the same tablet and phone behaviour. Only these differ: new block ids, the words, the link
(the corporate page instead of Services) and the picture (the illustration Ryan supplied for the Services row, already in the media library, shown whole). It is inserted immediately BEFORE the Therapeutic card
(exact anchor: that card's block id); no existing block is touched.
"""
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'home-card-content.php')
OUT_CSS = os.path.join(HERE, 'home-corporate-card.css')
TEMPLATE = open(os.path.join(HERE, 'card-template.txt'), encoding='utf-8').read()

ANCHOR = '<!-- wp:uagb/container {"block_id":"db0b5e62"'      # the Therapeutic Massage card: the new card goes immediately before it (occurs exactly once)
WRAPPER = '<div class="wp-block-uagb-container uagb-block-883307ab">'   # the section's card list: the Therapeutic card is its FIRST child, so "wrapper + anchor" pins the insertion to the very top of the list
OLD_TO_NEW_IDS = {'db0b5e62': 'e7a31c58', '67ff30b9': '2b94d0f6', '6241311c': '8c05a7e1', '505b8d31': '3f6e92b4', 'd4e81e9b': 'a1d47c09', '2c16c927': '5e8b30d7'}
TITLE = 'Corporate &amp; Group Massage'
DESC = 'On-site chair massage for employee appreciation days and workplace events, with table massage by arrangement at suitable host venues.'
LABEL = 'Corporate'      # one word like the neighbours' "Services": the longer "Corporate Massage" wraps onto two lines in the narrow button column at desktop width
LINK = '/corporate-massage-portland/'
IMG_ALT = 'Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.'   # the same text as the Services row picture
IMG_NAME = 'corporate-group-massage.webp'     # already in the media library since the corporate release; the installer finds it by name and checks its sha256, and never imports a second copy
IMG_SHA256 = '764a7ef428cbd2985156b584116f8bc196dd5c3a900e2426ff4f49f8582a747e'


def build_card():
    card = TEMPLATE
    for old, new in OLD_TO_NEW_IDS.items():
        assert old in card, old
        card = card.replace(old, new)
    for old, new in (
        ('<h3 class="uagb-heading-text">Therapeutic Massage</h3><p class="uagb-desc-text">Gentle to moderate pressure, for when you want a quieter session and time to relax.</p>',
         '<h3 class="uagb-heading-text">%s</h3><p class="uagb-desc-text">%s</p>' % (TITLE, DESC)),
        ('"label":"Services","link":"services/"', '"label":"%s","link":"%s"' % (LABEL, LINK)),
        ('href="services/"', 'href="%s"' % LINK),
        ('<div class="uagb-button__link">Services</div>', '<div class="uagb-button__link">%s</div>' % LABEL),
    ):
        assert card.count(old) == 1, old[:60]
        card = card.replace(old, new)
    # the picture: the template's core image block (a Therapeutic illustration with a media id) becomes the corporate illustration, referred to by URL only (no media id), like the Services row
    i = card.index('<!-- wp:image ')
    j = card.index('<!-- /wp:image -->') + len('<!-- /wp:image -->')
    pic = ('<!-- wp:image {"sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"16px"}}} -->\n'
           '<figure class="wp-block-image size-full has-custom-border"><img src="{{IMG_URL}}" alt="%s" title="%s" style="border-radius:16px"/></figure>\n<!-- /wp:image -->') % (IMG_ALT, IMG_ALT)   # title = alt text, the site's convention (SureRank would otherwise add the page title as a tooltip)
    card = card[:i] + pic + card[j:]
    assert 'Therapeutic' not in card and 'staging38' not in card and 'services/' not in card
    return card


def build_css():
    """The picture-frame rules the site's Additional CSS already holds for the three existing cards ("homepage service illustration frames: 6:5" and "TS SERVICE IMAGE RESPONSIVE"), repeated for the new card's block id only; the declarations are copied unchanged."""
    sel = 'body.home .wp-block-uagb-container.uagb-block-%s > figure.wp-block-image' % OLD_TO_NEW_IDS['db0b5e62']
    return '''/* ts-home-corp:start (2026-10-25, Home: the Corporate and Group Massage card at the top of "The Massage I Offer") */
/* The same picture-frame rules the three existing cards have (a 6:5 frame, rounded, picture cropped to fill it; compact and centred when Spectra stacks the rows), for the new card's block id only. No colours, fonts or images of its own; nothing existing is edited. */
%(sel)s {
    width: 100%%;
    margin: 0;
    overflow: hidden;
    border-radius: 16px;
}
%(sel)s img {
    display: block;
    width: 100%%;
    height: auto;
    aspect-ratio: 6 / 5;
    object-fit: cover;
}
@media (max-width: 1024px) {
  %(sel)s {
    max-width: 320px;
    margin-inline: auto;
    justify-self: center;
  }
}
/* ts-home-corp:end */
''' % {'sel': sel}


def build():
    return {'card': build_card(), 'anchor': ANCHOR, 'wrapper': WRAPPER, 'ids': list(OLD_TO_NEW_IDS.values()), 'title': TITLE, 'desc': DESC, 'label': LABEL, 'link': LINK, 'alt': IMG_ALT, 'img_name': IMG_NAME, 'img_sha256': IMG_SHA256}


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'HCJSON' not in js
    return ('<?php\n// GENERATED by build-home-corporate-card.py: do not edit by hand (edit the builder and run it again). install-home-corporate-card.php loads this.\n'
            'return json_decode( <<<\'HCJSON\'\n%s\nHCJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        print(data['title'], '|', data['desc'], '| link:', data['label'], '->', data['link'], '| alt:', data['alt'])
        sys.exit(0)
    outputs = ((OUT, php(data)), (OUT_CSS, build_css()))
    if '--check' in sys.argv:
        bad = 0
        for path, body in outputs:
            have = open(path, encoding='utf-8').read() if os.path.exists(path) else ''
            print('%s %s' % (os.path.basename(path), 'matches the builder' if have == body else 'is OUT OF DATE: run the builder'))
            bad += have != body
        sys.exit(1 if bad else 0)
    for path, body in outputs:
        with open(path, 'w', encoding='utf-8') as f:
            f.write(body)
        print('wrote %s (%d bytes)' % (path, len(body.encode('utf-8'))))

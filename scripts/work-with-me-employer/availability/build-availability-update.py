#!/usr/bin/env python3
"""Build availability-content.php: the exact old and new text that install-availability-update.php swaps for the "Work With Me and availability" plan. Standard library only.

    python3 -I -B scripts/work-with-me-employer/availability/build-availability-update.py          write availability-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/availability/build-availability-update.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/availability/build-availability-update.py --text   print every old -> new pair (for review)

SOURCE (Ryan, 2026-10-25): docs/inter-model/ThaiSolitude_WorkWithMe_Availability_Plan.md, "Getting our facts straight. Not disrupting the site. Go staging first. We're using staging38 now."
OWNER DECISIONS in that plan: corporate/group bookings begin November 1, 2026; individual appointments at a dedicated location are PLANNED for summer 2027 (never a guarantee); no individual house calls; Thai Solitude runs
alongside Butsaba's employment and corporate bookings are scheduled around it; remove "Relocating from Chicago"; leave "For Practices", the repeated contact and résumé links and all work-history dates alone.

WHAT THE SCAN OF STAGING38 FOUND (read-only, 2026-10-25; scan-availability.sh):
  - "Relocating from Chicago" is NOT in any page, SEO field, setting or menu on the site, and the uploaded résumé (sha256 c0c8b7c6...) is the "proposed" résumé whose contact line says only "Portland, OR": nothing to remove.
    (The older ONLYOFFICE résumé in scripts/assets/ DOES say "(Relocating from Chicago, IL)" but it is not what the site serves.)
  - There is no stale "Coming Soon" copy. The only "November" statements are Work With Me's employment start and interview dates, which the plan says to keep.
  - What IS misleading: the three modality pages (Thai, Therapeutic, Deep Tissue) answer "Where Do Sessions Take Place?" with "Sessions take place in Mt. Tabor, Portland. I do not currently travel to clients." and
    "How Do I Book a Session?" with "Use the Contact page to reach me ... I will reply to arrange a time.": both read as individual bookings now.
WHAT THIS CHANGES (exact replacements; every old text must occur exactly once; nothing else is touched):
  1. The announcement strip (WPCode snippet "CorporateBand"): the plan's availability notice (headline "Currently booking corporate & group massage only", the November 1 sentence, the summer 2027 plan, a link to the
     corporate page), on the pages it already shows on AND Home (the plan asks for Home); never on Work With Me or the corporate page. From 2026-11-01 (Pacific time) it switches itself to "Now accepting corporate & group
     massage bookings", so it cannot go stale.
  2. The corporate page: one sentence in the hero fine print (bookings begin November 1, 2026) and one in the "How do I reserve a date?" answer (events are scheduled around her work commitments).
  3. Work With Me: one sentence appended to the independent-practice paragraph (the plan's wording) and one to the "For clients" paragraph (corporate bookings begin November 1; individual appointments are planned for 2027).
  4. The two FAQ answers above, on each of the three modality pages.
WORDING: the plan's recommended notice says "dedicated Thai Solitude studio"; "studio" is on the house list of words this site never uses (OBMT), so it reads "a dedicated Thai Solitude location". Ryan can override.
"""
import hashlib
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'availability-content.php')

BAND_OLD = open(os.path.join(HERE, 'corporate-band-v1.txt'), encoding='utf-8').read()
assert hashlib.sha256(BAND_OLD.encode()).hexdigest().startswith('347c0b038f902bef')   # the code that is live on thaisolitude.com and staging38 (snippet fingerprint)

BAND_PAGES = ['home', 'about', 'services', 'contact', 'privacy-policy', 'accessibility', 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage']
OPEN_DATE = '2026-11-01 00:00:00'   # America/Los_Angeles

BAND_NEW = ("// Corporate & Group Massage announcement strip under the header (Ryan 2026-10-24; wording from the Work With Me and availability plan, 2026-10-25). Shown only on the client-facing pages listed here\n"
            "// (NOT Work With Me, NOT the corporate page itself, and not the unrelated pages on this install). Remove this snippet and the ts-corp block in Additional CSS to take it away.\n"
            "// Before November 1, 2026 (Pacific time) it says bookings begin that day; from then on it says they are open, so it does not go stale (a page held in the site cache keeps the old wording until the cache is purged).\n"
            "add_action( 'astra_header_after', function () {\n"
            "\tif ( is_admin() || ! is_page( array( %s ) ) ) {\n\t\treturn;\n\t}\n"
            "\t$target = get_page_by_path( 'corporate-massage-portland' );\n"
            "\tif ( ! $target || 'publish' !== $target->post_status || is_page( $target->ID ) ) {\n\t\treturn;\n\t}\n"
            "\t$open = time() >= ( new DateTime( '%s', new DateTimeZone( 'America/Los_Angeles' ) ) )->getTimestamp();\n"
            "\t$head = $open ? 'Now accepting corporate &amp; group massage bookings.' : 'Currently booking corporate &amp; group massage only.';\n"
            "\t$text = $open\n"
            "\t\t? ' Thai Solitude is booking corporate and group massage at suitable Portland venues. Individual appointments at a dedicated Thai Solitude location are planned for summer 2027.'\n"
            "\t\t: ' Thai Solitude begins accepting corporate and group massage bookings on November 1, 2026, at suitable Portland venues. Individual appointments at a dedicated Thai Solitude location are planned for summer 2027.';\n"
            "\techo '<div class=\"ts-corp-band\"><div class=\"ts-corp-band__inner\">'\n"
            "\t\t. '<p class=\"ts-corp-band__label\">Corporate &amp; Group Massage &middot; Portland</p>'\n"
            "\t\t. '<p class=\"ts-corp-band__text\"><strong>' . $head . '</strong>' . $text . '</p>'\n"
            "\t\t. '<a class=\"ts-corp-band__link\" href=\"' . esc_url( get_permalink( $target ) ) . '\">Explore Corporate &amp; Group Massage <span aria-hidden=\"true\">&rarr;</span></a>'\n"
            "\t\t. '</div></div>';\n"
            "} );\n") % (', '.join("'%s'" % s for s in BAND_PAGES), OPEN_DATE)

WHERE_Q = '"question":"Where Do Sessions Take Place?","answer":'
BOOK_Q = '"question":"How Do I Book a Session?","answer":'
WHERE_OLD = WHERE_Q + '"Sessions take place in Mt. Tabor, Portland. I do not currently travel to clients."'
WHERE_NEW = WHERE_Q + ('"Right now I offer corporate and group massage only, at suitable Portland venues, with bookings beginning November 1, 2026. '
                       'Individual appointments at a dedicated Thai Solitude location are planned for summer 2027. I do not offer individual house calls."')
BOOK_OLD = BOOK_Q + '"Use the Contact page to reach me by phone or email, and tell me what you would like. I will reply to arrange a time."'
BOOK_NEW = BOOK_Q + ('"Corporate and group massage bookings begin November 1, 2026. The Corporate and Group Massage page explains how to request a quote. '
                     'Individual appointments are planned for summer 2027."')

SCHED = ' I schedule corporate bookings around my work commitments and only offer times when I am available.'
PAIRS = [
    # (slug, label, old, new)
    ('corporate-massage-portland', 'corporate page: hero fine print',
     '<p class="ts-corp__fine">Arranged through an organization or venue at a shared location. I do not make individual home visits.</p>',
     '<p class="ts-corp__fine">Corporate and group massage bookings begin November 1, 2026. Arranged through an organization or venue at a shared location. I do not make individual home visits.</p>'),
    ('corporate-massage-portland', 'corporate page: FAQ "How do I reserve a date?"',
     '"question":"How do I reserve a date?","answer":"Send an inquiry with your preferred date and event details. A date is not confirmed until availability, the arrangement, the price and any agreed booking terms have been confirmed."',
     '"question":"How do I reserve a date?","answer":"Send an inquiry with your preferred date and event details. A date is not confirmed until availability, the arrangement, the price and any agreed booking terms have been confirmed.'
     + SCHED + '"'),
    ('work-with-me', 'Work With Me: independent practice and employment',
     '<p>Thai Solitude is my independent practice. I would welcome a conversation about the role and schedule your team needs and how my practice can fit alongside that work.</p>',
     '<p>Thai Solitude is my independent practice. I would welcome a conversation about the role and schedule your team needs and how my practice can fit alongside that work.' + SCHED + '</p>'),
    ('work-with-me', 'Work With Me: For clients',
     '<p>Thai massage may include rhythmic compression, guided movement, and assisted stretching. A quieter relaxation session or more focused therapeutic work may be a better fit on another day. I will talk through what you want and adapt the work to your comfort.</p>',
     '<p>Thai massage may include rhythmic compression, guided movement, and assisted stretching. A quieter relaxation session or more focused therapeutic work may be a better fit on another day. I will talk through what you want and adapt the work to your comfort. '
     'Corporate and group massage bookings begin November 1, 2026; individual appointments at a dedicated Thai Solitude location are planned for summer 2027.</p>'),
]
for slug, label in (('thai-massage', 'Thai Massage'), ('therapeutic-massage', 'Therapeutic Massage'), ('deep-tissue-massage', 'Deep Tissue Massage')):
    PAIRS.append((slug, label + ': FAQ "Where Do Sessions Take Place?"', WHERE_OLD, WHERE_NEW))
    PAIRS.append((slug, label + ': FAQ "How Do I Book a Session?"', BOOK_OLD, BOOK_NEW))


def build():
    return {
        'band_old': BAND_OLD, 'band_new': BAND_NEW, 'band_title': 'CorporateBand', 'band_pages': BAND_PAGES, 'open_date': OPEN_DATE,
        'pairs': [{'slug': s, 'label': l, 'old': o, 'new': n} for s, l, o, n in PAIRS],
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'AVJSON' not in js
    return ('<?php\n// GENERATED by build-availability-update.py: do not edit by hand (edit the builder and run it again). install-availability-update.php loads this.\n'
            'return json_decode( <<<\'AVJSON\'\n%s\nAVJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        for p in data['pairs']:
            print('== %s [%s]\n  OLD: %s\n  NEW: %s\n' % (p['label'], p['slug'], p['old'], p['new']))
        print('== announcement strip code (new):\n' + data['band_new'])
        sys.exit(0)
    body = php(data)
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('availability-content.php matches the builder' if have == body else 'availability-content.php is OUT OF DATE: run the builder')
        sys.exit(0 if have == body else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(body)
    print('wrote %s (%d bytes); %d replacements and the strip' % (OUT, len(body.encode('utf-8')), len(data['pairs'])))

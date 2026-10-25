#!/usr/bin/env python3
"""Build corporate-massage-content.php: the exact text and block markup that install-corporate-massage.php adds for the Corporate & Group Massage offer. Standard library only.

    python3 -I -B scripts/work-with-me-employer/corporate/build-corporate-massage.py          write corporate-massage-content.php next to this file
    python3 -I -B scripts/work-with-me-employer/corporate/build-corporate-massage.py --check  exit 1 if that file is not what this script produces
    python3 -I -B scripts/work-with-me-employer/corporate/build-corporate-massage.py --text   print the visible text (for review)

SOURCE OF THE REQUEST (Ryan, 2026-10-24): the three briefs docs/inter-model/00_README_..., 01_Thai_Solitude_Corporate_Massage_Full_Build_Brief.md and 02_..._Local_Agent_Handoff.md, then
"Use staging37. Use staging first." and "Please, please, please don't change anything already established on the website ... all you need to do is add."
So EVERYTHING HERE IS ADDED; nothing already on the site is edited, removed or reworded. What is added (install-corporate-massage.php does all of it as one guarded change):
  1. A new page, /corporate-massage-portland/ (slug), "Corporate & Group Massage in Portland": hero, three at-a-glance cards, chair massage, table massage, booking blocks and rate, what I plan with the host,
     how it works, about Butsaba, a Spectra FAQ accordion (7 questions, link-free answers) and a closing quote panel. Core blocks + the site's own colour tokens; no picture (see below).
  2. A fourth row on the Services page, after Thai Massage and before "Your Session": the same row layout, Ryan's picture (corporate-group-massage.webp, imported into the media library with its sha256 checked), a heading
     link and a short description that link to the new page. Inserted by exact anchor (the block id of the "Your Session" row); no existing block is touched.
  3. One menu item "Corporate Massage" in the Primary Menu (header, phone menu and footer share that menu), placed after Services.
  4. A WPCode snippet "CorporateBand" that prints a slim announcement strip under the header on the client-facing pages listed in BAND_PAGES (NOT Home, NOT Work With Me, NOT the new page itself, and not the
     unrelated pages on this install), and one marked block "ts-corp" in Additional CSS (the strip, the page and the Services picture).
  5. SureRank's title and meta description for the new page only.
The quote path is a mailto: link with a prefilled subject and body (the Contact page uses the same pattern for LMT inquiries): the site has no form backend and its privacy policy says it has no forms.

NO PICTURE ON THE CORPORATE PAGE ITSELF: every illustration on the site shows a person on a massage table in a room; the briefs ask for no image that suggests individual table sessions or a treatment room. The one picture used
for this offer is the Services-page row's, which Ryan supplied on 2026-10-24 ("put the image ... next to the link on the services page"): an illustration of a therapist and a client on a table, shown whole (not cropped).
Nothing here invents facts: no travel, setup, minimum, deposit, cancellation, insurance, equipment or five-hour capacity statement; the rate is shown as "proposed" with "a written quote confirms".

WORDING RULES (AGENTS.md): first person ("I"), never we/our/us; none of the OBMT house-list words (the whole list applies: this page has no research section); no claim verbs. The installer re-checks every string.
"""
import json
import os
import sys
import urllib.parse
import importlib.util

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'corporate-massage-content.php')

_spec = importlib.util.spec_from_file_location('thai_evidence_builder', os.path.join(HERE, '..', 'thai', 'build-thai-evidence.py'))
tb = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(tb)
attrs, esc, faq_child = tb.attrs, tb.esc, tb.faq_child

# The FAQ heading: the same Spectra heading as the Thai and Therapeutic "Questions About ..." headings (centred, separator above), but without their large top and side padding,
# because here the heading sits inside a page that already spaces its sections.
HEAD_ATTRS = (tb.HEAD_ATTRS.replace('"blockTopPadding":100,', '"blockTopPadding":0,').replace('"blockRightPadding":220,"blockLeftPadding":220,', '"blockRightPadding":0,"blockLeftPadding":0,')
              .replace('"blockTopPaddingTablet":80,"blockRightPaddingTablet":40,"blockLeftPaddingTablet":40,', '"blockTopPaddingTablet":0,"blockRightPaddingTablet":0,"blockLeftPaddingTablet":0,')
              .replace('"blockTopPaddingMobile":60,', '"blockTopPaddingMobile":0,'))
assert HEAD_ATTRS != tb.HEAD_ATTRS and HEAD_ATTRS.count('Padding') == tb.HEAD_ATTRS.count('Padding')


def heading(block_id, text):
    return ('<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><div class="uagb-separator"></div>'
            '<h2 class="uagb-heading-text">%s</h2></div>\n<!-- /wp:uagb/advanced-heading -->') % (HEAD_ATTRS % block_id, block_id, esc(text))

SLUG = 'corporate-massage-portland'
PAGE_TITLE = 'Corporate & Group Massage in Portland'
NAV_LABEL = 'Corporate Massage'
IMG_NAME = 'corporate-group-massage.webp'
IMG_SHA256 = '764a7ef428cbd2985156b584116f8bc196dd5c3a900e2426ff4f49f8582a747e'
IMG_TITLE = 'Corporate and group massage illustration'
IMG_ALT = 'Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.'
NAV_CLASS = 'ts-corp-nav'             # on the menu item, so the CSS can keep it out of a header or footer row it would not fit in
EMAIL = 'butsabad@gmail.com'          # the address the Contact page and the footer already publish
CTA_LABEL = 'Request a Corporate Massage Quote'
BAND_PAGES = ['about', 'services', 'contact', 'privacy-policy', 'accessibility', 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage']
SEO_TITLE = 'Corporate Chair Massage in Portland, OR | Thai Solitude'
SEO_DESC = 'On-site chair massage for employee appreciation days and workplace events in Portland, with table massage at suitable host venues. Request a quote.'

# ---- block ids (8 hex characters; the installer refuses any that already occur in another post) ----------------------------------------------------
ID_FAQ_HEAD, ID_FAQ = '6c1d9e47', 'f2a85b30'
ID_FAQ_CHILD = ['0b7e3a91', '4d9c1f6e', 'a31e86c5', '7f52d0b8', 'e8a4137c', '19c6fb42', '5b0d8e23']
ID_ROW, ID_ROW_SPACER, ID_ROW_TEXT = 'b3c7a91e', '5d2e8f40', 'a8f06c37'

# ---- the quote email: a mailto link with a prefilled subject and body ----------------------------------------------------------------------------
MAIL_SUBJECT = 'Corporate massage quote request'
MAIL_BODY = ('Hi Butsaba,\n\nI would like a quote for a corporate massage event.\n\n'
             'Organization:\nContact name and phone:\nEvent date (or "flexible"):\nEvent location (venue and city):\nApproximate number of participants:\n'
             'Service hours requested (for example 3 or 5):\nMassage type (chair massage, table massage, or not sure yet):\nAnything else I should know:\n')
MAILTO = 'mailto:%s?subject=%s&body=%s' % (EMAIL, urllib.parse.quote(MAIL_SUBJECT, safe=''), urllib.parse.quote(MAIL_BODY, safe=''))
MAILTO_HTML = MAILTO.replace('&', '&amp;')

FAQ = [
    ('What kinds of events do you serve?',
     'Employee appreciation days, workplace events and other group occasions where the host can arrange a suitable space and schedule.'),
    ('Do you offer individual home visits?',
     'No. This service is arranged through an organization or venue at a shared location.'),
    ('How many employees can receive massage in three hours?',
     'A three-hour event can often fit around 12–15 short chair-massage appointments, depending on session length, transitions and setup. I will plan the schedule with you before confirming the event.'),
    ('Can you do table massage?',
     'Table massage may be possible at a suitable host venue with an appropriate private room. I will talk through the setting and the arrangement with you first.'),
    ('What does it cost?',
     'The proposed corporate rate is $100 per service hour. A written quote confirms the total and what is included.'),
    ('What does the host need to provide?',
     'Tell me what space is available and what the venue requires. I will go over setup and scheduling with you before the event.'),
    ('How do I reserve a date?',
     'Send an inquiry with your preferred date and event details. A date is not confirmed until availability, the arrangement, the price and any agreed booking terms have been confirmed.'),
]


# ---- block helpers (core blocks, written exactly as WordPress saves them) ------------------------------------------------------------------------
def p(html, cls=None):
    if cls:
        return '<!-- wp:paragraph {"className":"%s"} -->\n<p class="%s">%s</p>\n<!-- /wp:paragraph -->' % (cls, cls, html)
    return '<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->' % html


def h(level, html):
    if level == 2:
        return '<!-- wp:heading -->\n<h2 class="wp-block-heading">%s</h2>\n<!-- /wp:heading -->' % html
    return '<!-- wp:heading {"level":%d} -->\n<h%d class="wp-block-heading">%s</h%d>\n<!-- /wp:heading -->' % (level, level, html, level)


def group(cls, inner, anchor=None):
    a = {'anchor': anchor, 'className': cls} if anchor else {'className': cls}
    ident = ' id="%s"' % anchor if anchor else ''
    return '<!-- wp:group %s -->\n<div%s class="wp-block-group %s">%s</div>\n<!-- /wp:group -->' % (attrs(a), ident, cls, '\n'.join(inner))


def columns(cls, cols):
    body = '\n'.join('<!-- wp:column -->\n<div class="wp-block-column">%s</div>\n<!-- /wp:column -->' % '\n'.join(c) for c in cols)
    return '<!-- wp:columns {"className":"%s"} -->\n<div class="wp-block-columns %s">%s</div>\n<!-- /wp:columns -->' % (cls, cls, body)


def button(label, href, outline=False):
    cls = 'wp-block-button is-style-outline' if outline else 'wp-block-button'
    comment = '<!-- wp:button {"className":"is-style-outline"} -->' if outline else '<!-- wp:button -->'
    return '%s\n<div class="%s"><a class="wp-block-button__link wp-element-button" href="%s">%s</a></div>\n<!-- /wp:button -->' % (comment, cls, href, esc(label))


def buttons(items, cls=None):
    c = ' %s' % cls if cls else ''
    open_c = '<!-- wp:buttons {"className":"%s"} -->' % cls if cls else '<!-- wp:buttons -->'
    return '%s\n<div class="wp-block-buttons%s">%s</div>\n<!-- /wp:buttons -->' % (open_c, c, '\n'.join(items))


def ul(items, cls=None):
    li = '\n'.join('<!-- wp:list-item -->\n<li>%s</li>\n<!-- /wp:list-item -->' % i for i in items)
    if cls:
        return '<!-- wp:list {"className":"%s"} -->\n<ul class="wp-block-list %s">%s</ul>\n<!-- /wp:list -->' % (cls, cls, li)
    return '<!-- wp:list -->\n<ul class="wp-block-list">%s</ul>\n<!-- /wp:list -->' % li


def eyebrow(text):
    return p(esc(text), 'ts-corp__eyebrow')


# ---- the page -------------------------------------------------------------------------------------------------------------------------------------
def build_page():
    hero = group('ts-corp__hero', [
        eyebrow('Corporate & group massage · Portland'),
        h(1, 'Corporate &amp; Group Massage in Portland'),
        p('A thoughtful way to appreciate your team.', 'ts-corp__lead'),
        p('I offer on-site chair massage for employee appreciation days, workplace events and group occasions. Tell me your time block, how many people will take part and where the event is, '
          'and I will plan an arrangement that fits and send you a quote.'),
        buttons([button(CTA_LABEL, MAILTO_HTML), button('See booking options', '#booking-options', outline=True)], 'ts-corp__actions'),
        p('Arranged through an organization or venue at a shared location. I do not make individual home visits.', 'ts-corp__fine'),
    ])

    glance = columns('ts-corp__glance', [
        [p('Chair massage for teams', 'ts-corp__card-title'), p('Short appointments for employee appreciation days and workplace events.')],
        [p('Three- or five-hour booking blocks', 'ts-corp__card-title'), p('Plan around the time your organization can set aside.')],
        [p('Table massage by arrangement', 'ts-corp__card-title'), p('Considered when the host can provide a suitable private room and the details are agreed in advance.')],
    ])

    chair = group('ts-corp__section', [
        eyebrow('The main offer'),
        h(2, 'Chair Massage for Employee Appreciation'),
        p('Chair massage is a practical fit for employee appreciation days and workplace events. You set aside a block of time, and each person takes a short appointment during the event. '
          'I plan sessions at about 10–15 minutes per person, depending on your schedule and how many people take part.'),
        group('ts-corp__note', [
            p('<strong>A three-hour booking can often fit around 12–15 short appointments.</strong> The final number depends on appointment length, transitions, setup and any break time, '
              'so I will agree a realistic schedule with you before confirming the event. Please read this as a planning estimate, not a promise.'),
        ]),
        p('Planning a five-hour block? The number of appointments depends on session length and the schedule, so I work it out with you in your quote.'),
    ], anchor='chair-massage')

    table = group('ts-corp__section', [
        eyebrow('By arrangement'),
        h(2, 'Table Massage at Suitable Venues'),
        p('For hotels, senior-living communities and other organizations with a suitable private room, table massage may also be possible. The host and I would talk through room privacy, space, access, '
          'equipment, timing and the kind of appointments being requested before deciding whether it is a good fit.'),
        p('Table massage is arranged through the host organization at its own venue, not as individual home visits. I confirm session lengths and pricing for table massage in your quote.'),
    ], anchor='table-massage')

    booking = group('ts-corp__section ts-corp__booking', [
        eyebrow('Booking and price'),
        h(2, 'Plan the Time That Works for Your Team'),
        group('ts-corp__rate', [
            p('<strong>$100</strong> per service hour', 'ts-corp__rate-line'),
            p('Proposed corporate rate. What the billed time includes is confirmed in your written quote.', 'ts-corp__rate-note'),
        ]),
        columns('ts-corp__blocks', [
            [h(3, '3-hour block'), p('About 12–15 short chair-massage appointments, as an estimate. The exact schedule is agreed before the event is confirmed.')],
            [h(3, '5-hour block'), p('The number of appointments is set in your quote, once session length and the schedule are agreed.')],
        ]),
        p('Final pricing is confirmed in a written quote after the service format, event timing, venue and inclusions have been agreed.'),
    ], anchor='booking-options')

    plan = group('ts-corp__section', [
        eyebrow('Before the event'),
        h(2, 'What I’ll Plan With You'),
        p('Before I confirm an event, I go over the practical details with you:'),
        ul(['Event location, access and parking', 'The time block and the number of participants', 'Where the chair massage will be set up',
            'How employees will sign up or be scheduled', 'Whether a private room is available, if table massage is of interest',
            'Arrival, setup and pack-down timing', 'Any venue policies or paperwork you need from me']),
        p('I will confirm the space and setup requirements with you before the event.'),
    ])

    steps = group('ts-corp__section', [
        eyebrow('How it works'),
        h(2, 'Three Steps to a Quote'),
        columns('ts-corp__steps', [
            [p('1', 'ts-corp__num'), h(3, 'Tell me about your event'), p('Share the date, venue, number of people and the time block.')],
            [p('2', 'ts-corp__num'), h(3, 'Plan the schedule'), p('You and I talk through chair or table massage, approximate session length, setup and event logistics.')],
            [p('3', 'ts-corp__num'), h(3, 'Receive a quote'), p('Once the details are clear, I send a written quote with the proposed arrangement and price for your approval.')],
        ]),
    ])

    about = group('ts-corp__section ts-corp__about', [
        eyebrow('Who you will work with'),
        h(2, 'About Butsaba'),
        p('I am Butsaba Dusrai, an Oregon licensed massage therapist (LMT-29905). I have practiced massage since 2020 and trained in traditional Thai bodywork at Still Light Center. '
          'When you write to me, I plan your event and prepare your quote myself. <a href="/about/">Read more about me</a>.'),
    ])

    faq_attrs = {'block_id': ID_FAQ, 'borderStyle': '', 'borderWidth': '', 'borderRadius': '', 'borderColor': '',
                 'overallBorderTopWidth': 1, 'overallBorderLeftWidth': 1, 'overallBorderRightWidth': 1, 'overallBorderBottomWidth': 1,
                 'overallBorderTopLeftRadius': 2, 'overallBorderTopRightRadius': 2, 'overallBorderBottomLeftRadius': 2, 'overallBorderBottomRightRadius': 2,
                 'overallBorderStyle': 'solid', 'overallBorderColor': '#D2D2D2'}   # the same settings as the About, Therapeutic and Thai accordions
    faq = ('<!-- wp:uagb/faq %s -->\n'
           '<div class="wp-block-uagb-faq uagb-faq__outer-wrap uagb-block-%s uagb-faq-icon-row uagb-faq-layout-accordion uagb-faq-expand-first-true '
           'uagb-faq-inactive-other-true uagb-faq-equal-height" data-faqtoggle="true" role="tablist">%s</div>\n<!-- /wp:uagb/faq -->') % (
        attrs(faq_attrs), ID_FAQ, '\n\n'.join(faq_child(ID_FAQ_CHILD[i], q, a) for i, (q, a) in enumerate(FAQ)))

    cta = group('ts-corp__cta', [
        eyebrow('Corporate quote'),
        h(2, 'Let’s Plan Your Team’s Massage Event'),
        p('Share a few details about your organization, preferred date, venue and the time you have available. I will follow up to talk through the format and send you a quote.'),
        buttons([button(CTA_LABEL, MAILTO_HTML)], 'ts-corp__actions'),
        p('The button opens an email to <a href="mailto:%s">%s</a> with prompts for the date, venue, group size, hours and massage type, so you can fill it in and send. '
          'Planning a series of staff events? Mention the schedule in your inquiry.' % (EMAIL, EMAIL), 'ts-corp__fine'),
    ])

    parts = [hero, glance, chair, table, booking, plan, steps, about, heading(ID_FAQ_HEAD, 'Questions About Corporate Massage'), faq, cta]
    return '<!-- wp:group {"className":"ts-corp"} -->\n<div class="wp-block-group ts-corp">%s</div>\n<!-- /wp:group -->\n' % '\n\n'.join(parts)


# ---- the Services page row (same grid as the Thai Massage row; Ryan's picture; relative links so a staging-to-live push stays correct) -----------------
ROW_CONTAINER_ATTRS = ('{"block_id":"%s","bottomPaddingDesktop":40,"topPaddingTablet":0,"bottomPaddingTablet":40,"leftPaddingTablet":0,"rightPaddingTablet":0,"topPaddingMobile":0,"bottomPaddingMobile":30,'
                       '"leftPaddingMobile":0,"rightPaddingMobile":0,"paddingLink":false,"rowGapMobile":12,"columnGapDesktop":32,"columnGapTablet":20,"containerBorderTopWidth":0,"containerBorderLeftWidth":0,'
                       '"containerBorderRightWidth":0,"containerBorderBottomWidth":1,"containerBorderLink":false,"containerBorderStyle":"solid","containerBorderColor":"var(--ast-global-color-6)","layout":"grid",'
                       '"gridColumnDesktop":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":40}},{"default":"custom","min":{"unit":"px","value":10},'
                       '"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":8}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}],'
                       '"gridColumnTablet":[{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":40}},{"default":"custom","min":{"unit":"px","value":10},'
                       '"max":{"unit":"fr","value":1},"custom":{"unit":"%%","value":7}},{"default":"custom","min":{"unit":"px","value":10},"max":{"unit":"fr","value":1},"custom":{"unit":"fr","value":1}}],'
                       '"gridAlignItemsDesktop":"center","gridAlignItemsTablet":"start"}')
ROW_SPACER_ATTRS = ('{"block_id":"%s","classMigrate":true,"headingDescToggle":false,"headingColor":"var(--ast-global-color-0)","blockLeftPadding":28,"blockBottomPadding":100,"blockTopPaddingTablet":0,'
                    '"blockRightPaddingTablet":0,"blockLeftPaddingTablet":0,"blockBottomPaddingTablet":0,"blockTopPaddingMobile":8,"blockRightPaddingMobile":0,"blockLeftPaddingMobile":0,"blockBottomPaddingMobile":0}')
ROW_TEXT_ATTRS = '{"block_id":"%s","classMigrate":true,"headingDescToggle":true,"subHeadingColor":"","headingTag":"h3","headSpace":8,"subHeadSpace":0}'
ROW_ANCHOR = '<!-- wp:uagb/container {"block_id":"69a1e017"'      # the "Your Session" row: the new row goes immediately before it (it occurs exactly once)
ROW_DESC = ('On-site chair massage for employee appreciation days and workplace events, with table massage considered at suitable host venues. '
            'Tell me about your team and I will send a quote. <a href="/%s/">Explore Corporate &amp; Group Massage</a>' % SLUG)


def build_row():
    """The row as a template: {{IMG_URL}} is filled in by the installer once the picture is in the media library (the URL is made relative, so a staging-to-live push stays correct)."""
    # No media id in the markup (like the Work With Me photo): the row then depends only on the file's URL, so applying again after a rollback gives byte-identical content.
    pic = ('<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ts-corp-pic"} -->\n'
           '<figure class="wp-block-image size-full ts-corp-pic"><img src="{{IMG_URL}}" alt="%s" title="%s"/></figure>\n<!-- /wp:image -->') % (esc(IMG_ALT), esc(IMG_ALT))   # tooltip = alt text, the site's convention (SureRank would otherwise add the page title)
    return ('<!-- wp:uagb/container %s -->\n'
            '<div class="wp-block-uagb-container uagb-layout-grid uagb-block-%s">%s\n\n'
            '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"></div>\n<!-- /wp:uagb/advanced-heading -->\n\n'
            '<!-- wp:uagb/advanced-heading %s -->\n<div class="wp-block-uagb-advanced-heading uagb-block-%s"><h3 class="uagb-heading-text"><a href="/%s/">Corporate &amp; Group Massage</a></h3>'
            '<p class="uagb-desc-text">%s</p></div>\n<!-- /wp:uagb/advanced-heading --></div>\n<!-- /wp:uagb/container -->\n\n') % (
        ROW_CONTAINER_ATTRS % ID_ROW, ID_ROW, pic, ROW_SPACER_ATTRS % ID_ROW_SPACER, ID_ROW_SPACER, ROW_TEXT_ATTRS % ID_ROW_TEXT, ID_ROW_TEXT, SLUG, ROW_DESC)


# ---- the announcement band (WPCode PHP snippet, runs on astra_header_after) ---------------------------------------------------------------------------
BAND_LABEL = 'Corporate &amp; Group Massage &middot; Portland'
BAND_HEAD = 'Bring massage to your team.'
BAND_TEXT = 'On-site chair massage for employee appreciation days and workplace events. Table massage may be available at suitable host venues.'
BAND_LINK = 'Explore Corporate Massage'


def build_band_code():
    pages = ', '.join("'%s'" % s for s in BAND_PAGES)
    return ("// Corporate & Group Massage announcement strip under the header (Ryan 2026-10-24). Shown only on the client-facing pages listed here (NOT Home, NOT Work With Me, NOT the corporate page itself,\n"
            "// and not the unrelated pages on this install). Remove this snippet and the ts-corp block in Additional CSS to take it away.\n"
            "add_action( 'astra_header_after', function () {\n"
            "\tif ( is_admin() || ! is_page( array( %s ) ) ) {\n\t\treturn;\n\t}\n"
            "\t$target = get_page_by_path( '%s' );\n"
            "\tif ( ! $target || 'publish' !== $target->post_status || is_page( $target->ID ) ) {\n\t\treturn;\n\t}\n"
            "\techo '<div class=\"ts-corp-band\"><div class=\"ts-corp-band__inner\">'\n"
            "\t\t. '<p class=\"ts-corp-band__label\">%s</p>'\n"
            "\t\t. '<p class=\"ts-corp-band__text\"><strong>%s</strong> %s</p>'\n"
            "\t\t. '<a class=\"ts-corp-band__link\" href=\"' . esc_url( get_permalink( $target ) ) . '\">%s</a>'\n"
            "\t\t. '</div></div>' . \"\\n\";\n"
            "} );\n") % (pages, SLUG, BAND_LABEL, BAND_HEAD, BAND_TEXT, BAND_LINK)


def build():
    page = build_page()
    row = build_row()
    code = build_band_code()
    return {
        'slug': SLUG, 'title': PAGE_TITLE, 'nav_label': NAV_LABEL, 'nav_class': NAV_CLASS, 'email': EMAIL, 'mailto': MAILTO_HTML,
        'seo_title': SEO_TITLE, 'seo_desc': SEO_DESC,
        'ids': [ID_FAQ_HEAD, ID_FAQ] + ID_FAQ_CHILD + [ID_ROW, ID_ROW_SPACER, ID_ROW_TEXT],
        'page_content': page,
        'row': row, 'row_anchor': ROW_ANCHOR, 'row_id': ID_ROW,
        'image': {'name': IMG_NAME, 'sha256': IMG_SHA256, 'title': IMG_TITLE, 'alt': IMG_ALT},
        'band_code': code, 'band_pages': BAND_PAGES,
        'faq_text': ' '.join(q + ' ' + a for q, a in FAQ),     # the FAQ lives in block attributes, which a tag-stripper cannot see; the installer's wording check reads this copy
        'band_text': ' '.join([BAND_LABEL, BAND_HEAD, BAND_TEXT, BAND_LINK]),
        'faq_questions': [q for q, _ in FAQ],
    }


def php(data):
    js = json.dumps(data, ensure_ascii=False, indent=1)
    assert 'CORPJSON' not in js
    return ('<?php\n// GENERATED by build-corporate-massage.py: do not edit by hand (edit the builder and run it again). install-corporate-massage.php loads this.\n'
            'return json_decode( <<<\'CORPJSON\'\n%s\nCORPJSON\n, true );\n') % js


if __name__ == '__main__':
    data = build()
    if '--text' in sys.argv:
        import re
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['page_content'])))
        print()
        print(re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', data['row'])))
        print()
        print(data['faq_text'])
        sys.exit(0)
    body = php(data)
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('corporate-massage-content.php matches the builder' if have == body else 'corporate-massage-content.php is OUT OF DATE: run the builder')
        sys.exit(0 if have == body else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(body)
    print('wrote %s (%d bytes)' % (OUT, len(body.encode('utf-8'))))

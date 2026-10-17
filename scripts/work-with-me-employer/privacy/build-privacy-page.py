#!/usr/bin/env python3
"""Build privacy-policy.html (version 2 of the Privacy Policy page) from the text below. Standard library only.

    python3 -I -B scripts/work-with-me-employer/privacy/build-privacy-page.py          write privacy-policy.html next to this file
    python3 -I -B scripts/work-with-me-employer/privacy/build-privacy-page.py --check  exit 1 if privacy-policy.html is not what this script produces

Layout (Ryan, 2026-10-17: "make that a second section ... an accordion for both online privacy and client based privacy ... a slide open accordion like we do for the
About page, My Commitment to Your Wellness"): a short intro, ONE Spectra FAQ accordion (the same block and settings as the About page: first panel open, one open at a
time, slide animation) with two panels, "Online privacy" and "Client privacy", then the questions line and the date.

Spectra renders a panel's answer as <p>ANSWER</p> through wp_kses_post, so an answer may only contain inline HTML: sub-headings are <strong>...</strong> and paragraphs are
separated with <br><br>. (Real lists or headings inside an answer would nest a block element inside a <p>.)

Wording rules (AGENTS.md): first person ("I"), never we/our/us, none of the OBMT forbidden terms (install-privacy-page.php refuses them again before every write).
FACTS: the Online privacy panel states only what was verified on the live site on 2026-10-17 (0 forms, 0 iframes, no cookies, no analytics, Google Fonts the only outside
load). The Client privacy panel is Ryan's template made generic: it names no payment provider, no storage product and no retention period because none is known; every
sentence in it is hedged ("may collect", "such as"). Butsaba must confirm it describes how she actually works before it goes on the live site (see the review notes in
docs/inter-model/Thai Solitude - Privacy Policy - Longer Draft (review later).md).
"""
import json
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'privacy-policy.html')

# Fixed block ids (8 hex characters, unique on the site) so the output is reproducible.
FAQ_ID, ONLINE_ID, CLIENT_ID = 'a7c3e5f1', '5d2b9e48', 'b86f1a37'

INTRO = ('Thai Solitude is the practice of Butsaba Dusrai, LMT (Oregon License LMT-29905). This page explains what happens to your information, '
         'first online and then as a client. Choose a section below.')

ONLINE = ('<strong>What this site collects</strong><br>'
          'This site has no forms, no accounts, no ads, and no tracking tools. It does not ask you for personal information, and it does not set cookies for visitors.'
          '<br><br>'
          '<strong>If you contact me</strong><br>'
          'If you email or call me, I receive whatever you choose to share, such as your name, your email address or phone number, and your message. '
          'I use it only to reply and to arrange an appointment. I do not sell it or share it for marketing.'
          '<br><br>'
          '<strong>Hosting and fonts</strong><br>'
          'The site is hosted by SiteGround, which keeps routine server records, such as visitors’ IP addresses and the pages requested, to keep the site running and secure. '
          'The site’s fonts load from Google Fonts, so your browser connects to Google to display them.'
          '<br><br>'
          '<strong>Links to other sites</strong><br>'
          'Some pages link to other sites, such as the Oregon license lookup and Google Maps. Those sites have their own privacy practices.')

CLIENT = ('This section covers the information I handle if you book an appointment or receive a session.'
          '<br><br>'
          '<strong>Information I collect</strong><br>'
          'Depending on how you contact me, I may collect your name, phone number, email address, and appointment times; the amount and date of a payment; '
          'and health and session information you choose to share, such as your health history, the areas you would like me to focus on, and notes about how you prefer your sessions. '
          'Please share only what is relevant to your session, and feel free to ask how a piece of information will be used before you provide it.'
          '<br><br>'
          '<strong>How I use it</strong><br>'
          'I use information to schedule and confirm appointments, to provide and document massage therapy services, to communicate with you about your appointments, '
          'to process payments and keep business and professional records, to meet legal, licensing, insurance, and recordkeeping requirements, and to maintain this website. '
          'I do not sell your personal information or use your health information for advertising.'
          '<br><br>'
          '<strong>When I share it</strong><br>'
          'I do not share your information except as needed to run my practice or as permitted or required by law. '
          'For example, service providers that help with scheduling, payments, email, website hosting, or record storage may handle information on my behalf. '
          'I may also disclose information when the law requires it, to respond to a lawful request, or to protect the safety of clients or others.'
          '<br><br>'
          '<strong>How I protect and keep it</strong><br>'
          'I use reasonable safeguards to protect practice records and limit access to what is needed. '
          'No website, email, or electronic storage system can be guaranteed completely secure. '
          'I keep records for as long as needed for professional and business purposes and for legal and licensing requirements, '
          'and I dispose of them securely when they are no longer needed.')

QUESTIONS = ('To ask what information I have about you, to request a correction, or to ask about deletion, email '
             '<a href="mailto:butsabad@gmail.com">butsabad@gmail.com</a>. '
             'Some records may need to be kept under legal or licensing requirements, so not every record can be deleted on request.')
UPDATED = 'Last updated: October 17, 2026.'


def attrs(d):
    """Serialise block attributes exactly like WordPress's serialize_block_attributes()."""
    s = json.dumps(d, ensure_ascii=False, separators=(',', ':'))
    for a, b in (('--', '\\u002d\\u002d'), ('<', '\\u003c'), ('>', '\\u003e'), ('&', '\\u0026'), ('\\"', '\\u0022')):
        s = s.replace(a, b)
    return s


def paragraph(text):
    return '<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->' % text


def child(block_id, question, answer):
    return '<!-- wp:uagb/faq-child %s /-->' % attrs({'block_id': block_id, 'question': question, 'answer': answer, 'icon': 'chevron-up', 'iconActive': 'chevron-down'})


def build():
    faq_attrs = {'block_id': FAQ_ID, 'borderStyle': '', 'borderWidth': '', 'borderRadius': '', 'borderColor': '',
                 'overallBorderTopWidth': 1, 'overallBorderLeftWidth': 1, 'overallBorderRightWidth': 1, 'overallBorderBottomWidth': 1,
                 'overallBorderTopLeftRadius': 2, 'overallBorderTopRightRadius': 2, 'overallBorderBottomLeftRadius': 2, 'overallBorderBottomRightRadius': 2,
                 'overallBorderStyle': 'solid', 'overallBorderColor': '#D2D2D2'}   # the same settings as the About page accordion
    faq = ('<!-- wp:uagb/faq %s -->\n'
           '<div class="wp-block-uagb-faq uagb-faq__outer-wrap uagb-block-%s uagb-faq-icon-row uagb-faq-layout-accordion uagb-faq-expand-first-true '
           'uagb-faq-inactive-other-true uagb-faq-equal-height" data-faqtoggle="true" role="tablist">%s\n\n%s</div>\n<!-- /wp:uagb/faq -->') % (
        attrs(faq_attrs), FAQ_ID, child(ONLINE_ID, 'Online privacy', ONLINE), child(CLIENT_ID, 'Client privacy', CLIENT))
    return '\n\n'.join([paragraph(INTRO), faq, paragraph(QUESTIONS), paragraph(UPDATED)]) + '\n'


if __name__ == '__main__':
    html = build()
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('privacy-policy.html matches the builder' if have == html else 'privacy-policy.html is OUT OF DATE: run the builder')
        sys.exit(0 if have == html else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(html)
    print('wrote %s (%d bytes)' % (OUT, len(html.encode('utf-8'))))

#!/usr/bin/env python3
"""Build privacy-policy.html from the text below. Standard library only.

    python3 -I -B scripts/work-with-me-employer/privacy/build-privacy-page.py          write privacy-policy.html next to this file
    python3 -I -B scripts/work-with-me-employer/privacy/build-privacy-page.py --check  exit 1 if privacy-policy.html is not what this script produces

Layout (Ryan, 2026-10-17: "make that a second section ... an accordion for both online privacy and client based privacy ... a slide open accordion like we do for the
About page, My Commitment to Your Wellness"): a short intro, ONE Spectra FAQ accordion (the same block and settings as the About page: first panel open, one open at a
time, slide animation) with two panels, "Online privacy" and "Client privacy", then the questions line and the date.

Spectra renders a panel's answer as <p>ANSWER</p> through wp_kses_post, so an answer may only contain inline HTML: sub-headings are <strong>...</strong> and paragraphs are
separated with <br><br>. (Real lists or headings inside an answer would nest a block element inside a <p>.)

Wording rules (AGENTS.md): first person ("I"), never we/our/us, none of the OBMT forbidden terms (install-privacy-page.php refuses them again before every write).
FACTS (Ryan, 2026-10-17: "include what we can from this list while being completely true"): every sentence is either verified or plainly true.
  Online privacy: verified against the site on 2026-10-17 in a real browser (0 forms, no cookies set, no analytics, hosted by SiteGround; outside loads are Google Fonts on every page and, on the Contact page only, an embedded Google Map from Google's servers; an earlier raw-HTML check had missed the map because a script inserts it).
  Client privacy keeps only what the site itself says or what is true of any practice: the service pages tell clients to share "any health conditions, medications, pregnancy,
  recent injury or surgery" before a session; the About page speaks of sharing goals and tailoring each session; the footer says massage is not a substitute for medical care
  and that she does not diagnose or treat; the law-requires and no-system-is-completely-secure sentences are generally true. The one COMMITMENT in it, "I do not sell the
  information you share with me or use it for marketing", is the same promise the live page already makes for contact details; Butsaba should be comfortable with it.
  LEFT OUT because nothing documents it: payment details and processor, "I do not store full card numbers", how records are kept and protected, retention periods, service
  providers for scheduling/email/storage, promotional messages and unsubscribing, appointment history, any mailing address. Add them back only when Butsaba confirms them
  (original template and notes: docs/inter-model/Thai Solitude - Privacy Policy - Longer Draft (review later).md).
"""
import importlib.util
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'privacy-policy.html')

_spec = importlib.util.spec_from_file_location('thai_evidence_builder', os.path.join(HERE, '..', 'thai', 'build-thai-evidence.py'))
_tb = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(_tb)
attrs, paragraph, child = _tb.attrs, _tb.para, _tb.faq_child

# Fixed block ids (8 hex characters, unique on the site) so the output is reproducible.
FAQ_ID, ONLINE_ID, CLIENT_ID = 'a7c3e5f1', '5d2b9e48', 'b86f1a37'

INTRO = ('Thai Solitude is the practice of Butsaba Dusrai, LMT (Oregon License LMT-29905). This page explains what happens to your information, '
         'first online and then as a client. Choose a section below.')

ONLINE = ('<strong>What this site collects</strong><br>'
          'This site has no forms, no accounts, no ads, and no tracking tools. It does not ask you for personal information, and the site itself does not set cookies for visitors.'
          '<br><br>'
          '<strong>If you contact me</strong><br>'
          'If you email or call me, I receive whatever you choose to share, such as your name, your email address or phone number, and your message. '
          'I use it to reply to you and to arrange an appointment. I do not sell it or share it for marketing.'
          '<br><br>'
          '<strong>Hosting, fonts, and the map</strong><br>'
          'The site is hosted by SiteGround, which keeps routine server records, such as visitors’ IP addresses and the pages requested, to keep the site running and secure. '
          'The site’s fonts load from Google Fonts, and the Contact page shows an embedded Google Map. Both come from Google’s servers, so your browser connects to Google to display them, and Google handles that information under its own privacy policy.'
          '<br><br>'
          '<strong>Links to other sites</strong><br>'
          'Some pages link to other sites, such as the Oregon license lookup and the schools and organizations named on the For Practices page. Those sites have their own privacy practices.')

CLIENT = ('This section covers the information you share with me directly: when you reach out, when you arrange an appointment, and before or during a session.'
          '<br><br>'
          '<strong>What you may share</strong><br>'
          'Before a session, my service pages ask you to tell me about any health conditions, medications, pregnancy, or a recent injury or surgery. '
          'If you do, I receive that information, along with whatever else you choose to tell me, such as your name, your phone number or email address, and your goals for the session. '
          'Please share only what is relevant to your session, and feel free to ask how a piece of information will be used before you provide it.'
          '<br><br>'
          '<strong>How I use it</strong><br>'
          'I use what you share to plan and adjust your session and to arrange appointments. '
          'Massage is not a substitute for medical care, and I do not diagnose or treat medical conditions.'
          '<br><br>'
          '<strong>Sharing and selling</strong><br>'
          'I do not sell the information you share with me or use it for marketing. I may disclose information when the law requires it.'
          '<br><br>'
          '<strong>Security</strong><br>'
          'No website, email, or electronic storage system can be guaranteed completely secure, so please keep that in mind when deciding what to send by email.')

QUESTIONS = ('To ask what information I have about you, to request a correction, or to ask about deletion, email '
             '<a href="mailto:butsabad@gmail.com">butsabad@gmail.com</a>. '
             'Some records may need to be kept under legal or licensing requirements, so not every record can be deleted on request.')
UPDATED = 'Last updated: October 17, 2026.'


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

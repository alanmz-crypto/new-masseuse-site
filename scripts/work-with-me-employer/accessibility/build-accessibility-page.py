#!/usr/bin/env python3
"""Build accessibility-page.html (the /accessibility/ page) from the text below. Standard library only.

    python3 -I -B scripts/work-with-me-employer/accessibility/build-accessibility-page.py          write accessibility-page.html next to this file
    python3 -I -B scripts/work-with-me-employer/accessibility/build-accessibility-page.py --check  exit 1 if accessibility-page.html is not what this script produces

Ryan, 2026-10-17: "work on an accessibility page ... we might set it up like this: Accessibility / Thai Solitude aims to make this website accessible and usable for everyone.
I've reviewed the site ... If you encounter a barrier ... contact Butsaba at [email] or [phone]". W3C WAI (Developing an Accessibility Statement) says a statement should name a
standard only if it was really applied, is "not a technical assessment or declaration of conformity", should explain known limitations in common terms and be linked from the footer.

EVERY SENTENCE MUST STAY TRUE. What was actually done on 2026-10-17 (tools/a11y-audit.mjs on staging32, a copy of live): axe-core 4.13.0 with the WCAG 2.0/2.1/2.2 A and AA rules on the
9 pages at 1280, 390 and 320 px wide (the accordion panels opened one by one); keyboard Tab-through of every page; the footer row's text contrast measured from pixels (11.9:1 or better);
page language, titles, one h1 per page, landmarks, a "Skip to content" link, alt text on every image, zoom not blocked and no side-scrolling at 320 px all verified. It was NOT a full
audit by a specialist and no screen reader was used. Do not add conformance claims ("conforms", "compliant", "fully accessible") unless that changes.
Known limitation kept in the text: the Google Map on the Contact page is Google's content. The For Practices page limitation (two links inside its expandable sections) stays listed
until those links are moved or the markup is changed; remove that sentence when it is fixed. Never write the OBMT-restricted word "disabilities" (list in AGENTS.md); no we/our/us.
"""
import os
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'accessibility-page.html')

INTRO = ('Thai Solitude aims to make this website accessible and usable for everyone. '
         'I’ve reviewed the site for accessibility and will continue to make improvements as needed.')

CHECKED = ['In October 2026 the site was tested with an automated accessibility checker against the Web Content Accessibility Guidelines (WCAG) 2.2, Levels A and AA. '
           'It was also checked for keyboard use, text contrast, and how the pages look on a phone and when zoomed in. '
           'This was not a full audit by an accessibility specialist, and automated testing cannot find every barrier, so some parts of the site may still be hard to use.',
           'Every page has a “Skip to content” link, one main heading, and text descriptions for its images, and the pages can be zoomed in without side-scrolling.']

LIMITS = ['The map on the Contact page is provided by Google, so how it works with assistive technology is up to Google. The email address and phone number on that page are also available as text.',
          'On the For Practices page, two links inside the expandable sections may not be announced clearly by screen readers.']

FEEDBACK = ('If you encounter a barrier or have difficulty using any part of the website, please contact Butsaba at '
            '<a href="mailto:butsabad@gmail.com">butsabad@gmail.com</a> or by phone using the number on the Contact page. '
            'If possible, include the page or feature you were using and what happened. I’ll review your feedback and work to address the issue.')
UPDATED = 'Last updated: October 17, 2026.'


def para(text):
    return '<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->' % text


def heading(text):
    return '<!-- wp:heading -->\n<h2 class="wp-block-heading">%s</h2>\n<!-- /wp:heading -->' % text


def build():
    blocks = [para(INTRO), heading('What has been checked')] + [para(t) for t in CHECKED]
    blocks += [heading('Known limitations')] + [para(t) for t in LIMITS]
    blocks += [heading('Feedback'), para(FEEDBACK), para(UPDATED)]
    return '\n\n'.join(blocks) + '\n'


if __name__ == '__main__':
    html = build()
    if '--check' in sys.argv:
        have = open(OUT, encoding='utf-8').read() if os.path.exists(OUT) else ''
        print('accessibility-page.html matches the builder' if have == html else 'accessibility-page.html is OUT OF DATE: run the builder')
        sys.exit(0 if have == html else 1)
    with open(OUT, 'w', encoding='utf-8') as f:
        f.write(html)
    print('wrote %s (%d bytes)' % (OUT, len(html.encode('utf-8'))))

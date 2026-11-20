#!/usr/bin/env python3
"""Build winding-willow-page.html from the frozen staging39 capture plus Ryan's approved wording changes (standard library only).

  python3 -I -B build-winding-willow.py           write winding-willow-page.html and update content_sha256/content_length in winding-willow.json
  python3 -I -B build-winding-willow.py --check   change nothing; exit 1 unless both files are exactly what this builder makes (a check-all.sh gate)

winding-willow-page-staging39.html = staging39 page #1385 byte for byte (capture ww-capture-s39-1, 2026-11-18; sha256 below). Never edit it.
CHANGES = Ryan, 2026-11-18, "#1 approved" (after "keep the message pleasant"): four passages that made a combination sound better than the studies
show (the evidence audit's "omit combined benefit"; Siriyong 2026, PMID 42142427: acupuncture added nothing to Thai massage). Each old text must
occur exactly once (or the stated number of times); nothing else in the page changes.
Then the link labels (2026-11-18) and the opening sentence (2026-11-20); see the comments in CHANGES. The spec also gets the menu item and its CSS.
"""
import hashlib, json, os, sys

HERE = os.path.dirname(os.path.abspath(__file__))
SRC, OUT, SPEC = (os.path.join(HERE, n) for n in ("winding-willow-page-staging39.html", "winding-willow-page.html", "winding-willow.json"))
SRC_SHA256 = "e21a940bb248e37a80ecd5959fef179496f810af97f06bd81ea691fc7bb21b60"
INTRO = ("Alongside my own practice, Thai Solitude, I now also see massage clients at Winding Willow Acupuncture &amp; Wellness in Southeast Portland. "
         "Winding Willow is a separate, independent business that employs me, and you book those visits directly with them.")
CHANGES = [
    ("then gives the results that were weaker, mixed or negative, and ends on what is encouraging: I included the studies",
     "then gives the results that were weaker, mixed or negative. I included the studies"),
    ("The encouraging part is that people improved with each approach, so you can choose by what feels right to you.",
     "Neither approach came out clearly ahead, so you can choose by what feels right to you."),
    ("Even so, both groups did well in the two Thai massage trials. In the larger back-pain trial,",
     "In the larger back-pain trial,"),
    ("What I can promise is a choice of skilled practitioners in one welcoming place.",
     "So adding another service is a matter of choice, not a way to get more from a massage. Winding Willow offers several practitioners in one welcoming place, each booked separately."),
    # Ryan, 2026-11-18: "only links that obviously lead to Winding Willow should link to Winding Willow" and "Book at Winding Willow should only work on
    # their site": the three booking buttons keep their Winding Willow address and now say so (the first label occurs twice: the top and Find Winding Willow).
    ('">Book massage with Sa</a>', '">Book with Sa at Winding Willow</a>', 2),
    ('">See Sa\u2019s calendar</a>', '">See Sa\u2019s calendar at Winding Willow</a>'),
    # Ryan, 2026-11-18: "rename everything that leads to Winding Willow with Winding Willow in the link, somehow."
    ('">Meet the acupuncturists</a>', '">Meet Winding Willow\u2019s acupuncturists</a>'),
    ('">See the services</a>', '">See Winding Willow\u2019s services</a>'),
    ('">Meet the massage therapists</a>', '">Meet Winding Willow\u2019s massage therapists</a>'),
    ('">All practitioners</a>', '">All Winding Willow practitioners</a>'),
    ('Winding Willow\u2019s <a href="https://www.windingwillowacupuncture.com/book-now">Book Now page</a>',
     '<a href="https://www.windingwillowacupuncture.com/book-now">Winding Willow\u2019s Book Now page</a>'),
    # Ryan, 2026-11-20: "at the very top of the page people need to know why Winding Willow suddenly popped up as an option" (Winding Willow "is an outside,
    # fully independent business that employs Butsaba"), then "Do your thing." to this opening sentence and the two softened lines that would contradict it.
    ('<!-- wp:paragraph -->\n<p>Winding Willow is a neighborhood collective of independent practitioners in Southeast Portland.',
     '<!-- wp:paragraph -->\n<p>' + INTRO + '</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Winding Willow is a neighborhood clinic in Southeast Portland with several practitioners.'),
    ('Each practitioner works independently and books their own appointments.', 'Each practitioner books their own appointments.'),
]
# The page as it was first put on staging40 (2026-11-20, page #1344): the installer updates that exact version in place (and its rollback restores it).
PREVIOUS_SHA256 = "b598caf788208ff146fff70d5a08500025dda1cf397ae95936ab0b4e7d680cc9"
# Ryan, 2026-11-20: the menu link is called "Winding Willow" ("the hamburger menu"); placed after Corporate Massage so that item keeps its reviewed position.
NAV = {"nav_label": "Winding Willow", "nav_class": "ts-ww-nav", "nav_after_class": "ts-corp-nav"}
NAV_CSS = (
    "/* ts-ww:start \u2014 the \"Winding Willow\" menu item (class ts-ww-nav). Ryan 2026-11-20: first the hamburger menu, then \"yes, tighten\" so it shows on a PC too.\n"
    "   Measured on staging40: seven items fit the header on one line from 1256 px when the space between items is 0.5em, which the ts-corp block already uses\n"
    "   at 1120-1349 px; this block uses it from 1350 px as well. Below 1256 px (and in the footer, where seven never fit) the item is hidden, so those menus\n"
    "   look exactly as before. The phone and tablet menu (921 px and narrower) always shows it. */\n"
    "@media (max-width: 1255px) {\n  #ast-hf-menu-1 > .ts-ww-nav {\n    display: none;\n  }\n}\n"
    "@media (min-width: 1350px) {\n  #ast-hf-menu-1 > .menu-item > .menu-link {\n    padding-left: 0.5em;\n    padding-right: 0.5em;\n  }\n}\n"
    "#astra-footer-menu > .ts-ww-nav {\n  display: none;\n}\n"
    "/* ts-ww:end */")



def build():
    raw = open(SRC, "rb").read()
    if hashlib.sha256(raw).hexdigest() != SRC_SHA256:
        sys.exit("winding-willow-page-staging39.html is not the frozen capture (sha256 differs)")
    text = raw.decode("utf-8")
    for change in CHANGES:
        old, new, times = (change + (1,))[:3]
        if text.count(old) != times:
            sys.exit("expected exactly %d occurrence(s) of: %s" % (times, old[:70]))
        text = text.replace(old, new)
    out = text.encode("utf-8")
    spec = json.load(open(SPEC, encoding="utf-8"))
    spec.update(content_file="winding-willow-page.html", content_sha256=hashlib.sha256(out).hexdigest(), content_length=len(out),
                source=spec["source"].split(" | ")[0] + " | plus Ryan's approved wording of 2026-11-18 and 2026-11-20 (build-winding-willow.py)",
                previous_content_sha256=PREVIOUS_SHA256, nav_css=NAV_CSS, **NAV)
    return out, (json.dumps(spec, ensure_ascii=False, indent=1) + "\n").encode("utf-8")


page, spec = build()
if "--check" in sys.argv[1:]:
    ok = os.path.exists(OUT) and open(OUT, "rb").read() == page and open(SPEC, "rb").read() == spec
    print(("OK" if ok else "DIFFERS") + ": winding-willow-page.html / winding-willow.json vs build-winding-willow.py")
    sys.exit(0 if ok else 1)
open(OUT, "wb").write(page)
open(SPEC, "wb").write(spec)
print("wrote winding-willow-page.html (%d bytes, sha256 %s)" % (len(page), hashlib.sha256(page).hexdigest()))

# ref: ./install-winding-willow.php — reads the built page and the spec; ../client-pages/wording_tiers.py — the research heading added there the same day

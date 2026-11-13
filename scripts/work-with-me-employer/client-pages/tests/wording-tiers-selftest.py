#!/usr/bin/env python3
"""Self-test for wording_tiers.py: deliberate breaks must be caught, deliberate allowances must pass, and every case that relies on the exemption must FAIL when the exemption's anchor is removed.
Run: python3 -I -B scripts/work-with-me-employer/client-pages/tests/wording-tiers-selftest.py   (exit 1 on any wrong answer). Also run by scripts/check-all.sh."""
import importlib.util
import os
import sys

here = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location("wording_tiers", os.path.join(os.path.dirname(here), "wording_tiers.py"))
wt = importlib.util.module_from_spec(spec)
spec.loader.exec_module(wt)

H = wt.RESEARCH_HEADING
def page(before="", research="", cites=""):
    return ('<main><p>%s</p><h2>%s</h2><p>%s</p><ol><li><cite class="ts-source">%s</cite></li></ol></main>' % (before, H, research, cites))

fails = 0
def expect(name, cond):
    global fails
    print(("PASS " if cond else "FAIL ") + name)
    if not cond:
        fails += 1

clean = wt.check(page("You stay fully clothed.", "A 2015 review of six studies.", "Smith J. A title. J Test, 2015."))
expect("a clean page has no hits anywhere", not (clean["before"] or clean["research"] or clean["cites"]) and clean["found"])

# ---- EVIDENCE words: refused outside the research section, accepted inside it and in titles
for w in ("chronic", "anxiety", "headache", "disability", "effective", "relief", "relieve"):
    outside = wt.check(page("People with %s problems come to me." % w, "", ""))
    expect("'%s' is refused in the ordinary page text" % w, bool(outside["before"]))
    inside = wt.check(page("", "The trial measured %s in 40 people." % w, ""))
    expect("'%s' is accepted inside the research section" % w, not inside["research"] and not inside["before"])
    title = wt.check(page("", "", "Smith J. A study of %s. J Test, 2015." % w))
    expect("'%s' is accepted in a verbatim source title" % w, not title["cites"] and not title["before"])
    noanchor = wt.check(page("", "The trial measured %s in 40 people." % w, "").replace(H, "Something else"))
    expect("'%s' is REFUSED again if the research heading is missing (no exemption without the anchor)" % w, bool(noanchor["before"]))

# ---- CLAIMS words and VOICE words: never accepted in prose
for w in ("heal", "healing", "cure", "treat", "treatment", "diagnose", "guarantee", "detox", "inflammation"):
    expect("claim word '%s' is refused in research prose" % w, bool(wt.check(page("", "Massage can %s it." % w, ""))["research"]))
    expect("claim word '%s' is refused in ordinary text" % w, bool(wt.check(page("Massage can %s it." % w, "", ""))["before"]))
for w in ("we", "our", "us", "studio", "Portland-area", "Portland metro"):
    expect("house-voice word '%s' is refused in research prose" % w, bool(wt.check(page("", "Come to %s now." % w, ""))["research"]))
    expect("house-voice word '%s' is refused in a source title" % w, bool(wt.check(page("", "", "Smith J. Come to %s now. J Test, 2015." % w))["cites"]))

# ---- claim verbs ARE accepted inside a verbatim title ("...for the treatment of...")
expect("'treatment' is accepted in a verbatim title", not wt.check(page("", "", "Smith J. The efficacy of massage for the treatment of pain. J Test, 2015."))["cites"])

# ---- laundering attempts
expect("a <cite> placed BEFORE the research heading is not exempt", bool(wt.check('<main><p><cite class="ts-source">chronic</cite></p><h2>%s</h2><p>ok</p></main>' % H)["before"]))
expect("a second research heading disables the exemption (a word placed after it is refused)", bool(wt.check('<main><h2>%s</h2><p>ok</p><h2>%s</h2><p>chronic</p></main>' % (H, H))["before"]))
# ---- the Therapeutic Massage heading works the same way, and the headings do not combine
H2 = "What the Research Says About Therapeutic Massage"
def page2(before="", research="", cites=""):
    return page(before, research, cites).replace(H, H2)
expect("the Therapeutic heading is one of the approved headings", H2 in wt.RESEARCH_HEADINGS and H in wt.RESEARCH_HEADINGS)
expect("Therapeutic: 'chronic' is accepted inside its research section", not wt.check(page2("", "The trial measured chronic pain in 40 people.", ""))["research"] and wt.check(page2("", "The trial measured chronic pain in 40 people.", ""))["found"])
expect("Therapeutic: 'chronic' is refused before its research section", bool(wt.check(page2("People with chronic problems come to me.", "", ""))["before"]))
expect("Therapeutic: 'chronic' in a verbatim title is accepted", not wt.check(page2("", "", "Smith J. A study of chronic pain. J Test, 2015."))["cites"])
expect("Therapeutic: claim verbs are refused inside its research section", bool(wt.check(page2("", "Massage can treat it.", ""))["research"]))
expect("Therapeutic: 'we' is refused in its source titles", bool(wt.check(page2("", "", "Smith J. We came. J Test, 2015."))["cites"]))
expect("Therapeutic: no exemption if the heading is missing", bool(wt.check(page2("", "The trial measured chronic pain.", "").replace(H2, "Something else"))["before"]))
both = '<main><h2>%s</h2><p>ok</p><h2>%s</h2><p>chronic</p></main>' % (H, H2)
expect("two DIFFERENT approved headings on one page disable the exemption (a word placed after them is refused)", bool(wt.check(both)["before"]) and not wt.check(both)["found"])
expect("an unapproved heading ('...About Swedish Massage') gives no exemption", bool(wt.check('<main><h2>What the Research Says About Swedish Massage</h2><p>chronic</p></main>')["before"]))
# ---- the Deep Tissue Massage heading (2026-10-24) works the same way
H3 = "What the Research Says About Deep Tissue Massage"
def page3(before="", research="", cites=""):
    return page(before, research, cites).replace(H, H3)
expect("the Deep Tissue heading is one of the approved headings", H3 in wt.RESEARCH_HEADINGS)
expect("Deep Tissue: 'chronic' is accepted inside its research section", not wt.check(page3("", "The trial measured chronic pain in 40 people.", ""))["research"] and wt.check(page3("", "The trial measured chronic pain in 40 people.", ""))["found"])
expect("Deep Tissue: 'chronic' is refused before its research section", bool(wt.check(page3("People with chronic problems come to me.", "", ""))["before"]))
expect("Deep Tissue: 'chronic' in a verbatim title is accepted", not wt.check(page3("", "", "Smith J. A study of chronic pain. J Test, 2015."))["cites"])
expect("Deep Tissue: claim verbs are refused inside its research section", bool(wt.check(page3("", "Massage can treat it.", ""))["research"]))
expect("Deep Tissue: 'we' is refused in its source titles", bool(wt.check(page3("", "", "Smith J. We came. J Test, 2015."))["cites"]))
expect("Deep Tissue: no exemption if the heading is missing", bool(wt.check(page3("", "The trial measured chronic pain.", "").replace(H3, "Something else"))["before"]))
both3 = '<main><h2>%s</h2><p>ok</p><h2>%s</h2><p>chronic</p></main>' % (H2, H3)
expect("Therapeutic + Deep Tissue headings together disable the exemption", bool(wt.check(both3)["before"]) and not wt.check(both3)["found"])
# ---- the Chair Massage heading (2026-10-30, the new service page) works the same way
H4 = "What the Research Says About Chair Massage"
def page4(before="", research="", cites=""):
    return page(before, research, cites).replace(H, H4)
expect("the Chair Massage heading is one of the approved headings", H4 in wt.RESEARCH_HEADINGS)
expect("Chair Massage: 'anxiety' is accepted inside its research section", not wt.check(page4("", "Anxiety scores were lower in 38 nurses.", ""))["research"] and wt.check(page4("", "Anxiety scores were lower in 38 nurses.", ""))["found"])
expect("Chair Massage: 'anxiety' is refused before its research section", bool(wt.check(page4("Chair massage eases anxiety.", "", ""))["before"]))
expect("Chair Massage: 'anxiety' in a verbatim title is accepted", not wt.check(page4("", "", "Smith J. Massage therapy reduces anxiety. J Test, 1996."))["cites"])
expect("Chair Massage: claim verbs are refused inside its research section", bool(wt.check(page4("", "Chair massage can treat it.", ""))["research"]))
expect("Chair Massage: 'we' is refused in its source titles", bool(wt.check(page4("", "", "Smith J. We came. J Test, 2015."))["cites"]))
expect("Chair Massage: no exemption if the heading is missing", bool(wt.check(page4("", "Anxiety scores were lower.", "").replace(H4, "Something else"))["before"]))
both4 = '<main><h2>%s</h2><p>ok</p><h2>%s</h2><p>chronic</p></main>' % (H3, H4)
expect("Deep Tissue + Chair Massage headings together disable the exemption", bool(wt.check(both4)["before"]) and not wt.check(both4)["found"])
# ---- the Winding Willow heading (2026-11-13, the new unlisted page /winding-willow/) works the same way
H5 = "What the Research Says About Massage Alongside Other Care"
def page5(before="", research="", cites=""):
    return page(before, research, cites).replace(H, H5)
expect("the Winding Willow heading is one of the approved headings", H5 in wt.RESEARCH_HEADINGS)
expect("Winding Willow: 'chronic' is accepted inside its research section", not wt.check(page5("", "In 60 adults with chronic low back pain.", ""))["research"] and wt.check(page5("", "In 60 adults with chronic low back pain.", ""))["found"])
expect("Winding Willow: 'chronic' is refused before its research section", bool(wt.check(page5("Massage for chronic problems.", "", ""))["before"]))
expect("Winding Willow: claim verbs are refused inside its research section", bool(wt.check(page5("", "Adding other treatments to massage.", ""))["research"]))
expect("Winding Willow: 'we' is refused in its source titles", bool(wt.check(page5("", "", "Smith J. We came. J Test, 2015."))["cites"]))
expect("Winding Willow: no exemption if the heading is missing", bool(wt.check(page5("", "In 60 adults with chronic pain.", "").replace(H5, "Something else"))["before"]))
both5 = '<main><h2>%s</h2><p>ok</p><h2>%s</h2><p>chronic</p></main>' % (H4, H5)
expect("Chair Massage + Winding Willow headings together disable the exemption", bool(wt.check(both5)["before"]) and not wt.check(both5)["found"])
expect("the old list is unchanged for the Services and Therapeutic pages (BAD still has all 25 evidence/voice/heal patterns)", len(wt.BAD) == 1 + len(wt.EVIDENCE) + len(wt.VOICE) and len(wt.EVIDENCE) == 25)

print("\nRESULT: " + ("all cases right" if not fails else "%d case(s) WRONG" % fails))
sys.exit(1 if fails else 0)

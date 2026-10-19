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
expect("the old list is unchanged for the Services and Therapeutic pages (BAD still has all 25 evidence/voice/heal patterns)", len(wt.BAD) == 1 + len(wt.EVIDENCE) + len(wt.VOICE) and len(wt.EVIDENCE) == 25)

print("\nRESULT: " + ("all cases right" if not fails else "%d case(s) WRONG" % fails))
sys.exit(1 if fails else 0)

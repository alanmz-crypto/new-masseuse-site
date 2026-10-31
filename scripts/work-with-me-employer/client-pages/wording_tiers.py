"""Wording tiers for pages that carry cited research. Pure functions, standard library only; loaded by verify-client-pages.py (by file path: `python3 -I` does not put this folder on sys.path)
and by tests/wording-tiers-selftest.py.

Ryan, 2026-10-19: "If something is on my banned words list but evidence says those words should be used, then update my banned words to accept it."
The list below is the OBMT-derived house list that used to apply everywhere. It now has three tiers (AGENTS.md says the same):

  VOICE     never accepted anywhere: we/our/us, studio, Portland-area / Portland metro (house voice and location wording; they have nothing to do with evidence).
  CLAIMS    never accepted in prose: heal*, cure, treat*, diagnos*, guarantee, detox, inflammation. (A verbatim paper title may contain them, e.g. "...for the treatment of...".)
  EVIDENCE  the rest (chronic, anxiety, disability, effective, relief, headache...). Accepted ONLY in the research section of a page, where a study's own subject has to be named accurately;
            everywhere else on the page they are still refused.

The research section is everything from the page's research heading ("What the Research Says About Thai Massage", "... About Therapeutic Massage" or "... About Deep Tissue Massage"; see RESEARCH_HEADINGS) to the end of the page. Inside it, the verbatim bibliography entries
(<cite class="ts-source">, the Sources panel) are accepted as published apart from the VOICE tier. If the heading is NOT found there is NO exemption at all (so the markers cannot be
used on their own to slip words past the check), and a <cite> before the heading is scanned like any other text.
"""
import html
import re

VOICE = [r"\b(we|our|us)\b", r"\bstudio\b", r"Portland[- ]area", r"Portland metro"]
CLAIMS = [r"\bheal(?:s|ed|ers?|ing)?\b", r"cure", r"\btreat", r"diagnos", r"guarantee", r"detox", r"inflammation"]
EVIDENCE = [r"\bproof\b", r"\bmeasurable\b", r"\beffective", r"pain relief", r"relieve", r"\brelief\b", r"pain management", r"\bsciatica\b", r"\bheadaches?\b",
            r"\bchronic\b", r"\badhesions?\b", r"\bcorrective\b", r"\baccelerat", r"restore", r"nervous system", r"\bcirculation\b", r"\banxiety\b", r"\bcancer\b",
            r"\bneurological\b", r"\bdisabilit", r"\bprevention\b", r"\blasting\b", r"long-term wellness", r"overall health", r"real results"]
# The list the Services and Therapeutic checks have always used (VOICE + the original heal pattern + EVIDENCE). Unchanged for those pages.
BAD = [CLAIMS[0]] + EVIDENCE + VOICE

# One approved heading per page that carries a research section (Thai 2026-10-19, Therapeutic 2026-10-19, Deep Tissue 2026-10-24, Chair Massage 2026-10-30). The heading must occur EXACTLY ONCE among all of them on the page; to extend this to another service page add its heading here and a case to
# tests/wording-tiers-selftest.py.
RESEARCH_HEADINGS = ["What the Research Says About Thai Massage", "What the Research Says About Therapeutic Massage", "What the Research Says About Deep Tissue Massage", "What the Research Says About Chair Massage"]
RESEARCH_HEADING = RESEARCH_HEADINGS[0]   # kept for the self-test's generic cases
CITE = re.compile(r'<cite class="ts-source">.*?</cite>', re.S)


def text(fragment):
    fragment = re.sub(r"<(script|style)[^>]*>.*?</\1>", "", fragment, flags=re.S)
    return re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", fragment))).strip()


def hits(t, patterns):
    return [p for p in patterns if re.search(p, t, re.I)]


def split_page(main_html):
    """(before_text, research_text, cites_text, heading_found). Without the heading everything is 'before' (no exemption)."""
    found = [(main_html.find(h), h) for h in RESEARCH_HEADINGS if h in main_html]
    if sum(main_html.count(h) for h in RESEARCH_HEADINGS) != 1 or len(found) != 1:
        return text(main_html), "", "", False
    i = found[0][0]
    before, research_html = main_html[:i], main_html[i:]
    cites = CITE.findall(research_html)
    return text(before), text(CITE.sub(" ", research_html)), text(" ".join(cites)), True


def check(main_html):
    """Returns {'before': [...], 'research': [...], 'cites': [...], 'found': bool}: the patterns that matched in each part (empty lists = clean)."""
    before, research, cites, found = split_page(main_html)
    if not found:
        return {"before": hits(before, VOICE + CLAIMS + EVIDENCE), "research": [], "cites": [], "found": False}
    return {"before": hits(before, VOICE + CLAIMS + EVIDENCE), "research": hits(research, VOICE + CLAIMS), "cites": hits(cites, VOICE), "found": True}

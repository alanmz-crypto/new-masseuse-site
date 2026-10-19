#!/usr/bin/env python3
"""Read-only check of the rendered Services and Therapeutic Massage pages after install-client-pages-local.php.
Usage: python3 verify-client-pages.py [base_url]     e.g. http://localhost:8090
Reads rendered HTML with curl; changes nothing."""
import html, re, subprocess, sys

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8090").rstrip("/")
fails = 0


def get(path):
    r = subprocess.run(["curl", "-s", "-m", "30", "-L", "-w", "\n%{http_code}", BASE + path], capture_output=True, text=True)
    body, _, code = r.stdout.rpartition("\n")
    return body, int(code or 0)


def check(name, ok, detail=""):
    global fails
    print(("PASS " if ok else "FAIL ") + name + (("  [" + str(detail) + "]") if detail else ""))
    if not ok:
        fails += 1


def text_of(fragment):
    fragment = re.sub(r"<(script|style)[^>]*>.*?</\1>", "", fragment, flags=re.S)
    return re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", fragment))).strip()


def meta(page, attr, name):
    m = re.search(r'<meta[^>]+%s="%s"[^>]*content="([^"]*)"' % (attr, re.escape(name)), page)
    return html.unescape(m.group(1)) if m else None


import importlib.util
import os
_spec = importlib.util.spec_from_file_location("wording_tiers", os.path.join(os.path.dirname(os.path.abspath(__file__)), "wording_tiers.py"))
wt = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(wt)
BAD = wt.BAD   # unchanged for Services and Therapeutic Massage; the Thai Massage research section uses the three tiers in wording_tiers.py (Ryan, 2026-10-19)

# ---- Services -------------------------------------------------------------------------------------------------------------
svc, code = get("/services/")
check("Services returns 200", code == 200, code)
new_title = "Massage Services | Butsaba Dusrai, Oregon LMT"
new_desc = "Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Mt. Tabor, Portland. Every session starts with your goals."
t = re.search(r"<title>(.*?)</title>", svc, re.S)
title = html.unescape(t.group(1)) if t else ""
check("Services title", title == new_title, title)
check("Services meta description", meta(svc, "name", "description") == new_desc, meta(svc, "name", "description"))
check("Services og/twitter title and description follow", all(meta(svc, a, n) == v for a, n, v in (("property", "og:title", new_title), ("name", "twitter:title", new_title), ("property", "og:description", new_desc), ("name", "twitter:description", new_desc))))
check("Services description fits one snippet (<= 160) and names only services the page lists", len(new_desc) <= 160 and all(k in text_of(svc) for k in ("Thai Massage", "Therapeutic Massage", "Deep Tissue Massage")), len(new_desc))
head_text = " ".join(filter(None, [title, meta(svc, "name", "description")]))
bad = [p for p in BAD if re.search(p, head_text, re.I)]
check("Services title and descriptions carry no restricted wording", not bad, bad)

# ---- Therapeutic Massage --------------------------------------------------------------------------------------------------
th, code = get("/therapeutic-massage/")
check("Therapeutic Massage returns 200", code == 200, code)
main = re.search(r"<main.*?</main>", th, re.S)
tt = text_of(main.group(0)) if main else ""
check("page body found", bool(tt))
s1 = "Research has explored massage in several adult groups, including family caregivers. [2] [6] [7] [8]"
s2 = "Research has also examined massage in relation to stress and quality-of-life measures in specific adult groups. [9] [10] [5]"
check("research sentence 1 reworded, citations [2][6][7][8] kept, once", tt.count(s1) == 1)
check("research sentence 2 reworded, citations [9][10][5] kept, once", tt.count(s2) == 1)
links = re.findall(r'<a [^>]*href="(https://pubmed\.ncbi\.nlm\.nih\.gov/\d+/)"[^>]*>\[(\d+)\]</a>', main.group(0) if main else "")
check("all 12 numbered study links still present, same order", [n for _, n in links] == ["1", "2", "3", "4", "5", "2", "6", "7", "8", "9", "10", "5"], [n for _, n in links])
check("reference numbers still map to the same studies", {n: u for u, n in links} == {"1": "https://pubmed.ncbi.nlm.nih.gov/39008297/", "2": "https://pubmed.ncbi.nlm.nih.gov/27165971/", "3": "https://pubmed.ncbi.nlm.nih.gov/32426160/", "4": "https://pubmed.ncbi.nlm.nih.gov/32901072/", "5": "https://pubmed.ncbi.nlm.nih.gov/17709060/", "6": "https://pubmed.ncbi.nlm.nih.gov/33919371/", "7": "https://pubmed.ncbi.nlm.nih.gov/39558520/", "8": "https://pubmed.ncbi.nlm.nih.gov/31003683/", "9": "https://pubmed.ncbi.nlm.nih.gov/27901219/", "10": "https://pubmed.ncbi.nlm.nih.gov/33053005/"})
q = "Can I Ask You to Focus on My Neck and Shoulders?"
a = "Yes. Tell me at the start where you would like the focus, and you can change it at any point during the session. For anything new, severe, or worrying, please see a doctor first."
check("neck-and-shoulders FAQ present once (question and answer)", tt.count(q) == 1 and tt.count(a) == 1)
check("old headache FAQ gone from the rendered page", "Tension Headaches" not in tt and "headache" not in tt.lower())
bad = [p for p in BAD if re.search(p, tt, re.I)]
check("no restricted wording anywhere in the Therapeutic Massage body", not bad, bad)
th_head = " ".join(filter(None, [meta(th, "name", "description"), meta(th, "property", "og:description")]))
bad = [p for p in BAD if re.search(p, th_head, re.I)]
check("Therapeutic Massage meta/og descriptions carry no restricted wording", not bad, bad)
print("INFO Therapeutic meta description (auto-built from page text, not set): " + str(meta(th, "name", "description")))

# ---- Thai Massage ---------------------------------------------------------------------------------------------------------
# The page may or may not carry the evidence section yet (staging33 and localhost first, live later): the structure checks run only where the section exists,
# the wording checks always run (full list outside the research section, tiers inside it; see wording_tiers.py).
thm, code = get("/thai-massage/")
check("Thai Massage returns 200", code == 200, code)
tmain = re.search(r"<main.*?</main>", thm, re.S)
tmh = tmain.group(0) if tmain else ""
check("Thai Massage body found", bool(tmh))
res = wt.check(tmh)
check("Thai Massage: no restricted wording outside the research section", not res["before"], res["before"])
if res["found"]:
    check("Thai Massage research section: no claim verbs, no we/our/us/studio", not res["research"], res["research"])
    check("Thai Massage source titles: no we/our/us/studio", not res["cites"], res["cites"])
    tt = wt.text(tmh)
    for q in ["What Should I Wear to a Thai Massage?", "What Will I Lie On?", "How Long Is a Session?", "Do I Need to Be Flexible?", "Where Do Sessions Take Place?",
              "How Is Thai Massage Different From Your Other Services?", "How Do I Book a Session?", "Questions About Thai Massage"]:
        check("Thai Massage: %r appears once" % q, tt.count(q) == 1)
    for sentence in ["Sessions are 60 or 90 minutes.", "Thai massage is done on a mat.", "I do not currently travel to clients.", "I trained in traditional Thai bodywork at Still Light Center.",
                     "in 2019 UNESCO added Nuad Thai, traditional Thai massage,"]:
        check("Thai Massage: %r once" % sentence, tt.count(sentence) == 1)
    check("Thai Massage: no links inside FAQ answers (axe nested-interactive)", not re.search(r'uagb-faq-content[^>]*>(?:(?!</div>).)*<a ', tmh, re.S))
    summ = [wt.text(x) for x in re.findall(r"<summary>(.*?)</summary>", tmh, re.S)]
    check("Thai Massage: the five research panels in order", summ == ["Reviews, and What They Say About Limits", "Back Pain", "Neck, Shoulders, Arms and Feet", "Stress, Energy and Recovery", "Sources"], summ)
    det = re.findall(r"<details[^>]*>", tmh)
    check("Thai Massage: first panel open, the other four closed", [" open" in d for d in det] == [True, False, False, False, False], len(det))
    marks = re.findall(r'<a [^>]*href="([^"]+)"[^>]*>\[(\d+)\]</a>', tmh)
    order = []
    for _, n in marks:
        if int(n) not in order:
            order.append(int(n))
    check("Thai Massage: numbered markers run 1..N in order of first use", bool(order) and order == list(range(1, len(order) + 1)), order[-3:])
    mapping = {}
    for u, n in marks:
        mapping.setdefault(n, set()).add(u)
    check("Thai Massage: every number points to exactly one source", all(len(v) == 1 for v in mapping.values()))
    check("Thai Massage: as many Sources entries as numbers", len(re.findall(r'<cite class="ts-source">', tmh)) == len(order), len(order))
    hosts = {re.match(r"https://([^/]+)/", u).group(1) if re.match(r"https://([^/]+)/", u) else u for u, _ in marks}
    check("Thai Massage: sources are only PubMed, doi.org, Cochrane, NCCIH or UNESCO", hosts <= {"pubmed.ncbi.nlm.nih.gov", "doi.org", "www.cochrane.org", "www.nccih.nih.gov", "ich.unesco.org"}, sorted(hosts))
else:
    print("INFO Thai Massage: the evidence section is not on this site yet (structure checks skipped; the wording check above still ran)")
for old in ["You stay fully clothed, so wear something loose and comfortable.", "New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage."]:
    check("Thai Massage: existing wording kept: %r" % old[:48], wt.text(tmh).count(old) == 1)
check("Thai Massage: the three jump links are intact", all(('href="#%s"' % a) in thm for a in ("ts-what-it-is", "ts-common-reasons", "ts-what-to-expect")))
check("Thai Massage: licence number in the footer", "LMT-29905" in thm)
# Meta title and description (Ryan, 2026-10-19: "I like yours. Go with that meta."). Strict checks only where the change is in; the wording check always runs on whatever meta the page has.
THAI_TITLE = "Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT"
THAI_DESC = "Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace."
tt_m = re.search(r"<title>(.*?)</title>", thm, re.S)
thai_title = html.unescape(tt_m.group(1)).strip() if tt_m else ""
thai_desc = meta(thm, "name", "description")
head_all = " ".join(filter(None, [thai_title, thai_desc, meta(thm, "property", "og:description"), meta(thm, "name", "twitter:description")]))
bad_head = wt.hits(head_all, wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
check("Thai Massage title, meta/og/twitter descriptions carry no restricted wording", not bad_head, bad_head)
if thai_title == THAI_TITLE or thai_desc == THAI_DESC:
    check("Thai Massage title is the approved one", thai_title == THAI_TITLE, thai_title)
    check("Thai Massage meta description is the approved one", thai_desc == THAI_DESC, thai_desc)
    check("Thai Massage og and twitter title and description follow", all(meta(thm, a, n) == v for a, n, v in (("property", "og:title", THAI_TITLE), ("name", "twitter:title", THAI_TITLE), ("property", "og:description", THAI_DESC), ("name", "twitter:description", THAI_DESC))))
    check("Thai Massage description fits one snippet (<= 160) and the title one line (<= 60)", len(THAI_DESC) <= 160 and len(THAI_TITLE) <= 60, "%d/%d" % (len(THAI_DESC), len(THAI_TITLE)))
    check("Thai Massage description is a whole sentence (no heading text, no cut-off)", thai_desc is not None and thai_desc.endswith(".") and "What Is Thai Massage?" not in thai_desc)
else:
    print("INFO Thai Massage meta is still the auto-built one (not set yet): " + str(thai_desc))

# ---- Therapeutic Massage and Deep Tissue Massage: meta title and description (Ryan, 2026-10-19: "finish one and two then go to three") -----------------------------------------
# Strict checks only where the change is in (services-meta/install-service-meta.php); the wording check always runs on whatever meta the page has.
SERVICE_META = [
    ("Therapeutic Massage", "/therapeutic-massage/", "Therapeutic Massage | Butsaba Dusrai, Oregon LMT",
     "Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go."),
    ("Deep Tissue Massage", "/deep-tissue-massage/", "Deep Tissue Massage | Butsaba Dusrai, Oregon LMT",
     "Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask."),
]
for label, path, want_title, want_desc in SERVICE_META:
    pg, pcode = get(path)
    check("%s page returns 200 (meta check)" % label, pcode == 200, pcode)
    m_t = re.search(r"<title>(.*?)</title>", pg, re.S)
    got_title = html.unescape(m_t.group(1)).strip() if m_t else ""
    got_desc = meta(pg, "name", "description")
    head_text = " ".join(filter(None, [got_title, got_desc, meta(pg, "property", "og:description"), meta(pg, "name", "twitter:description")]))
    bad_h = wt.hits(head_text, wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
    check("%s title, meta/og/twitter descriptions carry no restricted wording" % label, not bad_h, bad_h)
    if got_title == want_title or got_desc == want_desc:
        check("%s title is the approved one" % label, got_title == want_title, got_title)
        check("%s meta description is the approved one" % label, got_desc == want_desc, got_desc)
        check("%s og and twitter title and description follow" % label, all(meta(pg, a, n) == v for a, n, v in (("property", "og:title", want_title), ("name", "twitter:title", want_title), ("property", "og:description", want_desc), ("name", "twitter:description", want_desc))))
        check("%s description fits one snippet (<= 160) and the title one line (<= 60)" % label, len(want_desc) <= 160 and len(want_title) <= 60, "%d/%d" % (len(want_desc), len(want_title)))
        check("%s description is a whole sentence (no heading text, no cut-off)" % label, got_desc is not None and got_desc.endswith(".") and ("What Is " + label) not in got_desc)
    else:
        print("INFO %s meta is still the auto-built one (not set yet): %s" % (label, got_desc))

print("\nRESULT: " + ("all checks passed" if not fails else f"{fails} check(s) FAILED"))
sys.exit(1 if fails else 0)

# ref: ./install-client-pages-local.php — the installer whose result this checks; ../home/verify-home.py — the Home counterpart

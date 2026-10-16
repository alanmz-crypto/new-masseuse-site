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


BAD = [r"\bheal(?:s|ed|ers?|ing)?\b", r"\bproof\b", r"\bmeasurable\b", r"\beffective", r"pain relief", r"relieve", r"\brelief\b", r"pain management", r"\bsciatica\b", r"\bheadaches?\b",
       r"\bchronic\b", r"\badhesions?\b", r"\bcorrective\b", r"\baccelerat", r"restore", r"nervous system", r"\bcirculation\b", r"\banxiety\b", r"\bcancer\b",
       r"\bneurological\b", r"\bdisabilit", r"\bprevention\b", r"\blasting\b", r"long-term wellness", r"overall health", r"real results", r"\bstudio\b",
       r"\b(we|our|us)\b", r"Portland[- ]area", r"Portland metro"]

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

print("\nRESULT: " + ("all checks passed" if not fails else f"{fails} check(s) FAILED"))
sys.exit(1 if fails else 0)

# ref: ./install-client-pages-local.php — the installer whose result this checks; ../home/verify-home.py — the Home counterpart

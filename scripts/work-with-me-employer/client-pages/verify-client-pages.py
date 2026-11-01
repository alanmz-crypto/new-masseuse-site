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


def has_block_heading(source, block_id, label):
    starts = list(re.finditer(r'<div\b[^>]*class="[^"]*\buagb-block-' + re.escape(block_id) + r'\b[^"]*"[^>]*>', source))
    if len(starts) != 1:
        return False
    heading = re.match(r'\s*(?:<div\b[^>]*class="[^"]*\buagb-separator\b[^"]*"[^>]*>\s*</div>\s*)?<h3\b[^>]*class="[^"]*\buagb-heading-text\b[^"]*"[^>]*>(.*?)</h3>', source[starts[0].end():], re.S)
    return bool(heading) and text_of(heading[1]) == label


def has_c_runtime(source):
    return bool(re.search(r'<script\b[^>]*\bid=["\']ts-accessibility-runtime["\']', source))


def strip_tags(source):
    return re.findall(r'<div\b[^>]*class="[^"]*\bts-corp-band\b[^"]*"[^>]*>', source)


import importlib.util
import os
_spec = importlib.util.spec_from_file_location("wording_tiers", os.path.join(os.path.dirname(os.path.abspath(__file__)), "wording_tiers.py"))
wt = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(wt)
BAD = wt.BAD   # unchanged for Services and Therapeutic Massage; the Thai Massage research section uses the three tiers in wording_tiers.py (Ryan, 2026-10-19)

# The two FAQ answers on the Thai, Therapeutic and Deep Tissue pages that implied individual bookings now were corrected on 2026-10-25 (availability/install-availability-update.php; Ryan's Work With Me and availability plan).
# A site that does not have that update yet still has the old answers: either wording is accepted, exactly one of them, once.
OLD_WHERE = "Sessions take place in Mt. Tabor, Portland. I do not currently travel to clients."
# Booking date (Ryan, 2026-10-30: "Change booking begins to Nov 14th. Everywhere."; booking-date/install-booking-date.php): a site not changed yet says November 1, 2026, a changed one November 14, 2026.
# BOOK_DATE is read from the Work With Me page here; every other page on the same site must then say the same date.
BOOK_DATE = "November 1, 2026"
if "Corporate and group massage bookings begin November 14, 2026;" in re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", get("/work-with-me/")[0]))):
    BOOK_DATE = "November 14, 2026"
NEW_WHERE_T = ("Right now I offer corporate and group massage only, at suitable Portland venues, with bookings beginning {d}. "
               "Individual appointments at a dedicated Thai Solitude location are planned for summer 2027. I do not offer individual house calls.")
OLD_BOOK = "Use the Contact page to reach me by phone or email, and tell me what you would like. I will reply to arrange a time."
NEW_BOOK_T = ("Corporate and group massage bookings begin {d}. The Corporate and Group Massage page explains how to request a quote. "
              "Individual appointments are planned for summer 2027.")
NEW_WHERE, NEW_BOOK = NEW_WHERE_T.format(d=BOOK_DATE), NEW_BOOK_T.format(d=BOOK_DATE)


def either(t, old, new):
    return t.count(old) + t.count(new) == 1


# ---- Services -------------------------------------------------------------------------------------------------------------
svc, code = get("/services/")
check("Services returns 200", code == 200, code)
BUNDLE_A = bool(re.search(r'<div class="[^"]*\bts-review-next\b', svc))
new_title = "Massage Services | Butsaba Dusrai, Oregon LMT"
# seo-fixes (Ryan, 2026-10-30: "fix Services"): the description says "in Portland"; a site not changed yet still has the approved "in Mt. Tabor, Portland" wording. Either one, and the social copies must match it.
SVC_DESC_OLD = "Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Mt. Tabor, Portland. Every session starts with your goals."
SVC_DESC_NEW = "Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Portland. Every session starts with your goals."
SVC_DESC_BUNDLE_B = "Thai, therapeutic, deep tissue and chair massage in Portland with Butsaba Dusrai, LMT, plus corporate and group events. Every session starts with your goals."
# Bundle B is one release: its Services description selects the same title and
# description state on every changed page; mixed old/new metadata must fail.
BUNDLE_B = meta(svc, "name", "description") == SVC_DESC_BUNDLE_B
new_desc = SVC_DESC_BUNDLE_B if BUNDLE_B else (SVC_DESC_NEW if meta(svc, "name", "description") == SVC_DESC_NEW else SVC_DESC_OLD)
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
# Two states. BEFORE the research section (therapeutic/install-therapeutic-evidence.php): the page as the 2026-10-18 copy change left it (the checks below the "old state" line). AFTER it: the new structure and wording
# (Ryan, 2026-10-19; plan in docs/inter-model/therapeutic-massage-page-enhancement-plan.md). The wording check always runs, with the full list outside the research section and the tiers inside it (wording_tiers.py).
th, code = get("/therapeutic-massage/")
check("Therapeutic Massage returns 200", code == 200, code)
main = re.search(r"<main.*?</main>", th, re.S)
mh_th = main.group(0) if main else ""
tt = text_of(mh_th)
BUNDLE_C = has_c_runtime(svc) or has_c_runtime(th) or any(text_of(h) == "Questions About Therapeutic Massage" for h in re.findall(r'<h2\b[^>]*>(.*?)</h2>', th, re.S))
check("page body found", bool(tt))
res_th = wt.check(mh_th)
q = "Can I Ask You to Focus on My Neck and Shoulders?"
a = "Yes. Tell me at the start where you would like the focus, and you can change it at any point during the session. For anything new, severe, or worrying, please see a doctor first."
check("neck-and-shoulders FAQ present once (question and answer)", tt.count(q) == 1 and tt.count(a) == 1)
check("old headache FAQ gone from the rendered page", "Tension Headaches" not in tt and "headache" not in wt.text(wt.split_page(mh_th)[0] if res_th["found"] else mh_th).lower())
links = re.findall(r'<a [^>]*href="(https://[^"]+)"[^>]*>\[(\d+)\]</a>', mh_th)
if not res_th["found"]:
    # ---- old state
    s1 = "Research has explored massage in several adult groups, including family caregivers. [2] [6] [7] [8]"
    s2 = "Research has also examined massage in relation to stress and quality-of-life measures in specific adult groups. [9] [10] [5]"
    check("research sentence 1 reworded, citations [2][6][7][8] kept, once", tt.count(s1) == 1)
    check("research sentence 2 reworded, citations [9][10][5] kept, once", tt.count(s2) == 1)
    pm = [(u, n) for u, n in links if "pubmed" in u]
    check("all 12 numbered study links still present, same order", [n for _, n in pm] == ["1", "2", "3", "4", "5", "2", "6", "7", "8", "9", "10", "5"], [n for _, n in pm])
    check("reference numbers still map to the same studies", {n: u for u, n in pm} == {"1": "https://pubmed.ncbi.nlm.nih.gov/39008297/", "2": "https://pubmed.ncbi.nlm.nih.gov/27165971/", "3": "https://pubmed.ncbi.nlm.nih.gov/32426160/", "4": "https://pubmed.ncbi.nlm.nih.gov/32901072/", "5": "https://pubmed.ncbi.nlm.nih.gov/17709060/", "6": "https://pubmed.ncbi.nlm.nih.gov/33919371/", "7": "https://pubmed.ncbi.nlm.nih.gov/39558520/", "8": "https://pubmed.ncbi.nlm.nih.gov/31003683/", "9": "https://pubmed.ncbi.nlm.nih.gov/27901219/", "10": "https://pubmed.ncbi.nlm.nih.gov/33053005/"})
    bad = [p for p in BAD if re.search(p, tt, re.I)]
    check("no restricted wording anywhere in the Therapeutic Massage body", not bad, bad)
    print("INFO Therapeutic Massage: the evidence section is not on this site yet (old-state checks ran; the wording check above ran with the full list)")
else:
    # ---- new state
    check("Therapeutic Massage: no restricted wording outside the research section", not res_th["before"], res_th["before"])
    check("Therapeutic Massage research section: no claim verbs, no we/our/us/studio", not res_th["research"], res_th["research"])
    check("Therapeutic Massage source titles: no we/our/us/studio", not res_th["cites"], res_th["cites"])
    for qq in ["What Is the Difference Between Therapeutic and Relaxation Massage?", "How Is a Therapeutic Session Planned?", "Will I Feel the Results Right Away?", "How Often Should I Come In for Therapeutic Massage?",
               "Is Therapeutic Massage Appropriate If I Have No Specific Complaint?", "Can I Ask You to Focus on My Neck and Shoulders?", "How Is Therapeutic Massage Different From Deep Tissue Massage?",
               "Can I Change the Pressure During a Session?", "Where Do Sessions Take Place?", "How Do I Book a Session?"]:
        check("Therapeutic Massage: FAQ question %r appears once" % qq, tt.count(qq) == 1)
    for sentence in ["Yes. You can ask for lighter pressure, a different focus, or a pause at any time, and I will adjust.",
                     "Deep tissue massage uses focused strokes and sustained pressure on areas that stay tight."]:
        check("Therapeutic Massage: %r once" % sentence[:56], tt.count(sentence) == 1)
    check("Therapeutic Massage: the 'Where Do Sessions Take Place?' answer is the old or the corrected text, once", either(tt, OLD_WHERE, NEW_WHERE))
    check("Therapeutic Massage: the 'How Do I Book a Session?' answer is the old or the corrected text, once", either(tt, OLD_BOOK, NEW_BOOK))
    check("Therapeutic Massage: no links inside FAQ answers (axe nested-interactive)", not re.search(r'uagb-faq-content[^>]*>(?:(?!</div>).)*<a ', mh_th, re.S))
    check("Therapeutic Massage: the reworded neck and shoulder sentence once, the old one gone", tt.count("Research on massage for neck and shoulder pain is mixed.") == 1 and "Research suggests massage may be helpful for some people with neck or shoulder pain" not in tt)
    check("Therapeutic Massage: an encouraging sentence before the 'mixed' one (pace and pressure) and one after it (the review's dose subgroup), once each", tt.count("and I adapt the pace and pressure to what suits you.") == 1
          and tt.count("which suggests that regular sessions may matter.") == 1)
    check("Therapeutic Massage: low-back sentence kept once", tt.count("Massage has also been studied for low-back pain, although the evidence is limited.") == 1)
    check("Therapeutic Massage: the post-stroke and cancer-pain studies are not cited", not any(u.endswith(("/33919371/", "/39558520/")) for u, _ in links))
    summ = [wt.text(x) for x in re.findall(r"<summary>(.*?)</summary>", mh_th, re.S)]
    check("Therapeutic Massage: the five research panels in order", summ == ["Reviews, and What They Say About Limits", "Neck, Shoulders and Back", "Exercise, Flexibility and Recovery", "Stress, Relaxation and Well-Being", "Sources"], summ)
    det = re.findall(r"<details[^>]*>", mh_th)
    check("Therapeutic Massage: first panel open, the other four closed", [" open" in d for d in det] == [True, False, False, False, False], len(det))
    order = []
    for _, n in links:
        if int(n) not in order:
            order.append(int(n))
    check("Therapeutic Massage: numbered markers run 1..N in order of first use", bool(order) and order == list(range(1, len(order) + 1)), order[-3:])
    mapping = {}
    for u, n in links:
        mapping.setdefault(n, set()).add(u)
    check("Therapeutic Massage: every number points to exactly one source", all(len(v) == 1 for v in mapping.values()))
    check("Therapeutic Massage: as many Sources entries as numbers", len(re.findall(r'<cite class="ts-source">', mh_th)) == len(order), len(order))
    hosts = {re.match(r"https://([^/]+)/", u).group(1) for u, _ in links if re.match(r"https://([^/]+)/", u)}
    check("Therapeutic Massage: sources are only PubMed, doi.org, Cochrane or NCCIH", hosts <= {"pubmed.ncbi.nlm.nih.gov", "doi.org", "www.cochrane.org", "www.nccih.nih.gov"}, sorted(hosts))
    # The "sandwich" version of the research panels (Ryan, 2026-10-19; installed by evidence-update/): strict checks only where it is in.
    if "studies of massage used for specific medical conditions are not summarized here" in tt:
        check("Therapeutic Massage: scope note and 'included the disappointing results' sentence once each", tt.count("studies of massage used for specific medical conditions are not summarized here") == 1 and tt.count("I included the studies I found with disappointing results, not only the favorable ones.") == 1)
        check("Therapeutic Massage: the safety results are on the page (adverse-events review) and the encouraging close once", "138 adverse events" in tt and tt.count("Serious events are rare: I ask about your health") == 1)
        check("Therapeutic Massage: the safety paragraph says who was involved (8 of 138 events were massage therapists) and closes on it, once each", tt.count("Massage therapists were the practitioners in only 8 of the 138 events") == 1 and tt.count("the serious injuries were mostly linked to people who were not massage therapists") == 1)
        check("Therapeutic Massage: the added discouraging results are there (lower vigor after hard exercise, placebo massage as good as real for back pain, no HRV or blood pressure difference)", "a lower mood score for vigor" in tt and "adding real massage or a placebo massage for three weeks both reduced pain" in tt and "not followed by significant differences in heart-rate variability or blood pressure" in tt)
    else:
        print("INFO Therapeutic Massage: the sandwich version of the research panels is not on this site yet (version 1 is; the structure checks above still ran)")
    alts = re.findall(r'<img [^>]*?alt="([^"]*)"[^>]*?title="([^"]*)"', mh_th)
    check("Therapeutic Massage: five pictures with five different alt texts, tooltip equal to the alt, none saying 'by Butsaba'", len(alts) == 5 and len({a1 for a1, _ in alts}) == 5 and all(a1 and a1 == t1 for a1, t1 in alts) and "by Butsaba" not in mh_th, len(alts))
for old in ["Before your session, tell me about any health conditions, medications, pregnancy, recent injury or surgery.", "New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage."]:
    check("Therapeutic Massage: existing wording kept: %r" % old[:48], tt.count(old) == 1)
check("Therapeutic Massage: the four jump links are intact", all(('href="#%s"' % a1) in th for a1 in ("ts-what-it-is", "ts-common-reasons", "ts-who-can-benefit", "ts-what-to-expect")))
check("Therapeutic Massage: licence number in the footer", "LMT-29905" in th)
th_head = " ".join(filter(None, [meta(th, "name", "description"), meta(th, "property", "og:description")]))
bad = [p for p in BAD if re.search(p, th_head, re.I)]
check("Therapeutic Massage meta/og descriptions carry no restricted wording", not bad, bad)
print("INFO Therapeutic meta description: " + str(meta(th, "name", "description")))

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
    for sentence in ["Sessions are 60 or 90 minutes.", "Thai massage is done on a mat.", "I trained in traditional Thai bodywork at Still Light Center.",
                     "in 2019 UNESCO added Nuad Thai, traditional Thai massage,"]:
        check("Thai Massage: %r once" % sentence, tt.count(sentence) == 1)
    check("Thai Massage: the 'Where Do Sessions Take Place?' answer is the old or the corrected text, once", either(tt, OLD_WHERE, NEW_WHERE))
    check("Thai Massage: the 'How Do I Book a Session?' answer is the old or the corrected text, once", either(tt, OLD_BOOK, NEW_BOOK))
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
    # The "sandwich" version of the research panels (Ryan, 2026-10-19; installed by evidence-update/): strict checks only where it is in.
    if "studies of massage used for specific medical conditions are not summarized here" in tt:
        check("Thai Massage: scope note and 'included the disappointing results' sentence once each", tt.count("studies of massage used for specific medical conditions are not summarized here") == 1 and tt.count("I included the studies I found with disappointing results, not only the favorable ones.") == 1)
        check("Thai Massage: the safety results are on the page (adverse-events review, the Thai case report, the commentary) and the encouraging close once", "138 adverse events" in tt and "spinal epidural hematoma" in tt and "heart attack around the time of a Thai massage" in tt and tt.count("Serious events are rare: I ask about your health") == 1)
        check("Thai Massage: the safety paragraph says who was involved (8 of 138 events were massage therapists) and closes on it, once each", tt.count("Massage therapists were the practitioners in only 8 of the 138 events") == 1 and tt.count("the serious injuries were mostly linked to people who were not massage therapists") == 1 and tt.count("given by a layperson, a friend") == 1)
        check("Thai Massage: the null shoulder-strength result is quoted from the authors", "strength in shoulders was not significantly different" in tt)
    else:
        print("INFO Thai Massage: the sandwich version of the research panels is not on this site yet (version 1 is; the structure checks above still ran)")
else:
    print("INFO Thai Massage: the evidence section is not on this site yet (structure checks skipped; the wording check above still ran)")
for old in ["You stay fully clothed, so wear something loose and comfortable.", "New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage."]:
    check("Thai Massage: existing wording kept: %r" % old[:48], wt.text(tmh).count(old) == 1)
check("Thai Massage: the three jump links are intact", all(('href="#%s"' % a) in thm for a in ("ts-what-it-is", "ts-common-reasons", "ts-what-to-expect")))
check("Thai Massage: licence number in the footer", "LMT-29905" in thm)
# Meta title and description (Ryan, 2026-10-19: "I like yours. Go with that meta."). Strict checks only where the change is in; the wording check always runs on whatever meta the page has.
# portland-meta (Ryan, 2026-10-29) changed "Mt. Tabor" to "Portland"; a site not changed yet still holds the OLD pair, which stays approved until it is.
THAI_TITLE_OLD = "Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT"
THAI_DESC_OLD = "Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace."
THAI_TITLE = "Thai Massage in Portland | Butsaba Dusrai, Oregon LMT"
THAI_DESC = "Fully clothed Thai massage in Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace."
tt_m = re.search(r"<title>(.*?)</title>", thm, re.S)
thai_title = html.unescape(tt_m.group(1)).strip() if tt_m else ""
thai_desc = meta(thm, "name", "description")
head_all = " ".join(filter(None, [thai_title, thai_desc, meta(thm, "property", "og:description"), meta(thm, "name", "twitter:description")]))
bad_head = wt.hits(head_all, wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
check("Thai Massage title, meta/og/twitter descriptions carry no restricted wording", not bad_head, bad_head)
if thai_title == THAI_TITLE_OLD and thai_desc == THAI_DESC_OLD:
    THAI_TITLE, THAI_DESC = THAI_TITLE_OLD, THAI_DESC_OLD
if thai_title == THAI_TITLE or thai_desc == THAI_DESC:
    check("Thai Massage title is the approved one", thai_title == THAI_TITLE, thai_title)
    check("Thai Massage meta description is the approved one", thai_desc == THAI_DESC, thai_desc)
    check("Thai Massage og and twitter title and description follow", all(meta(thm, a, n) == v for a, n, v in (("property", "og:title", THAI_TITLE), ("name", "twitter:title", THAI_TITLE), ("property", "og:description", THAI_DESC), ("name", "twitter:description", THAI_DESC))))
    check("Thai Massage description fits one snippet (<= 160) and the title one line (<= 60)", len(THAI_DESC) <= 160 and len(THAI_TITLE) <= 60, "%d/%d" % (len(THAI_DESC), len(THAI_TITLE)))
    check("Thai Massage description is a whole sentence (no heading text, no cut-off)", thai_desc is not None and thai_desc.endswith(".") and "What Is Thai Massage?" not in thai_desc)
else:
    print("INFO Thai Massage meta is still the auto-built one (not set yet): " + str(thai_desc))

# ---- Deep Tissue Massage: research section, FAQ and honest body paragraph (Ryan, 2026-10-24; deep-tissue/install-deep-tissue-evidence.php; plan in docs/inter-model/deep-tissue-massage-page-enhancement-plan.md) ----------
# The wording check always runs (full list outside the research section, tiers inside it); the strict structure checks run only on a site that has the section (staging36 first, live later on Ryan's go).
dtp, code = get("/deep-tissue-massage/")
check("Deep Tissue Massage returns 200", code == 200, code)
dmain = re.search(r"<main.*?</main>", dtp, re.S)
mh_dt = dmain.group(0) if dmain else ""
dt = text_of(mh_dt)
check("Deep Tissue Massage body found", bool(dt))
res_dt = wt.check(mh_dt)
check("Deep Tissue Massage: no restricted wording outside the research section", not res_dt["before"], res_dt["before"])
check("Deep Tissue Massage research section: no claim verbs, no we/our/us/studio", not res_dt["research"], res_dt["research"])
check("Deep Tissue Massage source titles: no we/our/us/studio", not res_dt["cites"], res_dt["cites"])
for old in ["Deep tissue massage uses slow, deliberate pressure on muscles that stay tight. I begin by asking about your goals and how much pressure you like, then check in as I work. Deep pressure is never required: you can ask me to lighten up at any time, and I will.",
            "Before your session, tell me about any health conditions, medications, pregnancy, recent injury or surgery."]:
    check("Deep Tissue Massage: existing wording kept: %r" % old[:48], dt.count(old) == 1)
check("Deep Tissue Massage: licence number in the footer", "LMT-29905" in dtp)
if not res_dt["found"]:
    print("INFO Deep Tissue Massage: the evidence section is not on this site yet (the wording check above ran with the full list)")
else:
    check("Deep Tissue Massage: the FAQ heading once", dt.count("Questions About Deep Tissue Massage") == 1)
    for qq in ["Does Deep Tissue Massage Have to Hurt?", "How Is Deep Tissue Massage Different From Therapeutic Massage?", "Can I Choose Where You Focus?", "What Should I Tell You Before a Session?",
               "Where Do Sessions Take Place?", "How Do I Book a Session?"]:
        check("Deep Tissue Massage: FAQ question %r appears once" % qq, dt.count(qq) == 1)
    for sentence in ["No. Deep pressure is never required."]:
        check("Deep Tissue Massage: %r once" % sentence[:56], dt.count(sentence) == 1)
    check("Deep Tissue Massage: the 'Where Do Sessions Take Place?' answer is the old or the corrected text, once", either(dt, OLD_WHERE, NEW_WHERE))
    check("Deep Tissue Massage: the 'How Do I Book a Session?' answer is the old or the corrected text, once", either(dt, OLD_BOOK, NEW_BOOK))
    check("Deep Tissue Massage: the page's safety sentence is on the page twice (page text and FAQ answer)", dt.count("New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage.") == 2)
    check("Deep Tissue Massage: no links inside FAQ answers (axe nested-interactive)", not re.search(r'uagb-faq-content[^>]*>(?:(?!</div>).)*<a ', mh_dt, re.S))
    check("Deep Tissue Massage: the new body paragraph once, the three old sentences gone", dt.count("Research on deep tissue massage is limited and mixed.") == 1 and "Trials in specific musculoskeletal conditions suggest deep tissue massage" not in dt
          and "A randomized trial in athletes linked repeated deep tissue massage" not in dt and "Research suggests deep tissue massage added to conventional rehabilitation may reduce pain intensity" not in dt)
    check("Deep Tissue Massage: 'myofascial' appears once, inside a verbatim source title", len(re.findall("myofascial", mh_dt, re.I)) == 1 and "myofascial" in " ".join(wt.CITE.findall(mh_dt)).lower())
    dsumm = [wt.text(x) for x in re.findall(r"<summary>(.*?)</summary>", mh_dt, re.S)]
    check("Deep Tissue Massage: the five research panels in order", dsumm == ["Reviews, and What They Say About Limits", "Neck, Shoulders and Back", "Exercise, Soreness and Recovery", "Relaxation and the Role of Pressure", "Sources"], dsumm)
    ddet = re.findall(r"<details[^>]*>", mh_dt)
    check("Deep Tissue Massage: first panel open, the other four closed", [" open" in d for d in ddet] == [True, False, False, False, False], len(ddet))
    dlinks = re.findall(r'<a [^>]*href="(https://[^"]+)"[^>]*>\[(\d+)\]</a>', mh_dt)
    dorder = []
    for _, n in dlinks:
        if int(n) not in dorder:
            dorder.append(int(n))
    check("Deep Tissue Massage: numbered markers run 1..N in order of first use", bool(dorder) and dorder == list(range(1, len(dorder) + 1)), dorder[-3:])
    dmap = {}
    for u, n in dlinks:
        dmap.setdefault(n, set()).add(u)
    check("Deep Tissue Massage: every number points to exactly one source", all(len(v) == 1 for v in dmap.values()))
    check("Deep Tissue Massage: as many Sources entries as numbers (33)", len(re.findall(r'<cite class="ts-source">', mh_dt)) == len(dorder) == 33, len(dorder))
    dhosts = {re.match(r"https://([^/]+)/", u).group(1) for u, _ in dlinks if re.match(r"https://([^/]+)/", u)}
    check("Deep Tissue Massage: sources are only PubMed, doi.org, Cochrane or NCCIH", dhosts <= {"pubmed.ncbi.nlm.nih.gov", "doi.org", "www.cochrane.org", "www.nccih.nih.gov"}, sorted(dhosts))
    check("Deep Tissue Massage: scope note and 'included the disappointing results' sentence once each", dt.count("studies of massage used for specific medical conditions are not summarized here") == 1 and dt.count("I included the studies I found with disappointing results, not only the favorable ones.") == 1)
    check("Deep Tissue Massage: the safety paragraph names force and who gave the massage, and closes on the practice, once each", dt.count("probably as a result of the forceful application of massage therapy") == 1 and dt.count("by an unlicensed therapist") == 1
          and dt.count("Massage therapists were the practitioners in only 8 of the 138 events") == 1 and dt.count("they were mostly linked to people who were not massage therapists and to forceful or unusual techniques") == 1
          and dt.count("Deep pressure is never required: before your session I ask you to tell me about health conditions") == 1)
    check("Deep Tissue Massage: the discouraging results are there (12-month null, not cost-effective, little or no difference vs placebo massage, calf stiffness unchanged, blood lactate unchanged)", "but not at later follow-ups" in dt and "not cost-effective" in dt
          and "probably makes little or no difference to pain, function or quality of life" in dt and "produced no change in calf muscle stiffness or ankle movement" in dt and "did not change blood lactate levels" in dt)
    check("Deep Tissue Massage: the athletes trial is in the research section with its limits stated", dt.count("describes no comparison group, and it measured outcomes with both objective and subjective assessments") == 1)
    dalts = re.findall(r'<img [^>]*?alt="([^"]*)"[^>]*?title="([^"]*)"', mh_dt)
    check("Deep Tissue Massage: three pictures, tooltip equal to the alt text, none saying 'by Butsaba'", len(dalts) == 3 and all(a1 and a1 == t1 for a1, t1 in dalts) and "by Butsaba" not in mh_dt, len(dalts))

# ---- Therapeutic Massage and Deep Tissue Massage: meta title and description (Ryan, 2026-10-19: "finish one and two then go to three") -----------------------------------------
# Strict checks only where the change is in (services-meta/install-service-meta.php); the wording check always runs on whatever meta the page has.
# portland-meta (Ryan, 2026-10-29) changed "Mt. Tabor, Portland" to "Portland" in both descriptions; a site not changed yet still holds the old one (the last value), which stays approved until Bundle B. Bundle B adds the Portland titles and keeps the Portland descriptions.
SERVICE_META = [
    ("Therapeutic Massage", "/therapeutic-massage/", "Therapeutic Massage | Butsaba Dusrai, Oregon LMT", "Therapeutic Massage in Portland | Butsaba Dusrai, LMT",
     "Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.",
     "Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go."),
    ("Deep Tissue Massage", "/deep-tissue-massage/", "Deep Tissue Massage | Butsaba Dusrai, Oregon LMT", "Deep Tissue Massage in Portland | Butsaba Dusrai, LMT",
     "Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.",
     "Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask."),
]
for label, path, old_title, bundle_b_title, want_desc, old_desc in SERVICE_META:
    want_title = bundle_b_title if BUNDLE_B else old_title
    pg, pcode = get(path)
    check("%s page returns 200 (meta check)" % label, pcode == 200, pcode)
    m_t = re.search(r"<title>(.*?)</title>", pg, re.S)
    got_title = html.unescape(m_t.group(1)).strip() if m_t else ""
    got_desc = meta(pg, "name", "description")
    head_text = " ".join(filter(None, [got_title, got_desc, meta(pg, "property", "og:description"), meta(pg, "name", "twitter:description")]))
    bad_h = wt.hits(head_text, wt.VOICE + wt.CLAIMS + wt.EVIDENCE)
    check("%s title, meta/og/twitter descriptions carry no restricted wording" % label, not bad_h, bad_h)
    if not BUNDLE_B and got_desc == old_desc:
        want_desc = old_desc
    if BUNDLE_B or got_title in (old_title, bundle_b_title) or got_desc == want_desc:
        check("%s title is the approved one" % label, got_title == want_title, got_title)
        check("%s meta description is the approved one" % label, got_desc == want_desc, got_desc)
        check("%s og and twitter title and description follow" % label, all(meta(pg, a, n) == v for a, n, v in (("property", "og:title", want_title), ("name", "twitter:title", want_title), ("property", "og:description", want_desc), ("name", "twitter:description", want_desc))))
        check("%s description fits one snippet (<= 160) and the title one line (<= 60)" % label, len(want_desc) <= 160 and len(want_title) <= 60, "%d/%d" % (len(want_desc), len(want_title)))
        check("%s description is a whole sentence (no heading text, no cut-off)" % label, got_desc is not None and got_desc.endswith(".") and ("What Is " + label) not in got_desc)
    else:
        print("INFO %s meta is still the auto-built one (not set yet): %s" % (label, got_desc))

# ---- Corporate & Group Massage (Ryan, 2026-10-24; corporate/install-corporate-massage.php) ------------------------------------------------------------------------------------------
# Everything here is ADDED (a new page, a Services row, a menu item, an announcement strip). The checks run only on a site that has the new page; there the whole house wording list applies (the page has no research section).
cp_page, cp_code = get("/corporate-massage-portland/")
if cp_code == 404:
    print("INFO Corporate & Group Massage: the page is not on this site yet (corporate/install-corporate-massage.php has not been applied here)")
else:
    check("Corporate & Group Massage returns 200", cp_code == 200, cp_code)
    cmain = re.search(r"<main.*?</main>", cp_page, re.S)
    cmh = cmain.group(0) if cmain else ""
    ct = text_of(cmh)
    check("Corporate & Group Massage body found", bool(ct))
    res_cp = wt.check(cmh)
    check("Corporate & Group Massage: no restricted wording anywhere on the page (no research section: the full list applies)", not res_cp["before"] and not res_cp["research"] and not res_cp["cites"], res_cp)
    CP_TITLE = "Corporate & Group Massage in Portland, OR | Thai Solitude" if BUNDLE_B else "Corporate Chair Massage in Portland, OR | Thai Solitude"
    CP_DESC_BUNDLE_B = "On-site chair massage for employee appreciation days and workplace events in Portland, with table massage by arrangement at suitable host venues. Request a quote."
    CP_DESC = CP_DESC_BUNDLE_B if BUNDLE_B else "On-site chair massage for employee appreciation days and workplace events in Portland, with table massage at suitable host venues. Request a quote."
    m_ct = re.search(r"<title>(.*?)</title>", cp_page, re.S)
    got_ct = html.unescape(m_ct.group(1)).strip() if m_ct else ""
    check("Corporate & Group Massage title and meta description are the approved ones", got_ct == CP_TITLE and meta(cp_page, "name", "description") == CP_DESC, got_ct)
    check("Corporate & Group Massage og and twitter title and description follow", all(meta(cp_page, a, n) == v for a, n, v in (("property", "og:title", CP_TITLE), ("name", "twitter:title", CP_TITLE), ("property", "og:description", CP_DESC), ("name", "twitter:description", CP_DESC))))
    check("Corporate & Group Massage description <= 160 (or the exact approved 162-character Bundle B copy) and title <= 60", (len(CP_DESC) <= 160 or (BUNDLE_B and CP_DESC == CP_DESC_BUNDLE_B and len(CP_DESC) == 162)) and len(CP_TITLE) <= 60, "%d/%d" % (len(CP_DESC), len(CP_TITLE)))
    check("Corporate & Group Massage: one h1", len(re.findall(r"<h1[ >]", cmh)) == 1)
    check("Corporate & Group Massage: licence number in the footer", "LMT-29905" in cp_page)
    check("Corporate & Group Massage: both quote buttons carry the same mailto link with a subject and a body", len(set(re.findall(r'href="(mailto:butsabad@gmail\.com\?subject=Corporate%20massage%20quote%20request&amp;body=[^"]+)"', cmh))) == 1 and len(re.findall(r'>Request a Corporate Massage Quote</a>', cmh)) == 2)
    check("Corporate & Group Massage: no link inside FAQ answers (axe nested-interactive)", not re.search(r'uagb-faq-content[^>]*>(?:(?!</div>).)*<a ', cmh, re.S))
    check("Corporate & Group Massage: the capacity figure is stated as an estimate", ct.count("A three-hour booking can often fit around 12–15 short appointments.") == 1 and "planning estimate, not a promise" in ct)
    check("Corporate & Group Massage: the rate is shown as proposed and confirmed by a written quote", "per service hour" in ct and "Proposed corporate rate." in ct and "confirmed in your written quote" in ct)
    check("Corporate & Group Massage: no invented fee, minimum, deposit or cancellation terms", not re.search(r"travel (fee|charge)|setup fee|minimum|deposit|cancell", ct, re.I), re.findall(r"travel (?:fee|charge)|setup fee|minimum|deposit|cancell\w*", ct, re.I))
    check("Corporate & Group Massage: no testimonials, logos or insurance claims", not re.search(r"testimonial|insured|insurance|background[- ]check", ct, re.I))
    svc2, _ = get("/services/")
    svt = text_of(svc2)
    if "Corporate & Group Massage" in svt:
        check("Services: the corporate row links to the new page and the three existing service links are still there",
              svc2.count('href="/corporate-massage-portland/">Corporate &amp; Group Massage</a>') == 1 and all(("/%s/" % s) in svc2 for s in ("thai-massage", "therapeutic-massage", "deep-tissue-massage")))
        check("Services: the existing descriptions are unchanged", all(s in svt for s in ("Thai massage is my specialty.", "Therapeutic massage gives steady attention", "Deep tissue massage uses focused strokes", "Every session begins with a conversation about what you want")))
    else:
        print("INFO Services: the Corporate & Group Massage row is not there yet")

# ---- Work With Me and availability (Ryan, 2026-10-25; availability/install-availability-update.php) --------------------------------------------------------------------------------
# Strict checks only on a site that has the update (the Work With Me sentence is there); on any site the things the plan says to KEEP are checked.
SCHED = "I schedule corporate bookings around my work commitments and only offer times when I am available."
wwm_page, wwm_code = get("/work-with-me/")
check("Work With Me returns 200", wwm_code == 200, wwm_code)
wm = text_of(wwm_page)
for keep in ["Start — From November 1 · open for days, evenings and weekends", "Interviews — By phone or video call now, in person from November 1", "Oregon license lookup", "Email me about an LMT position", "View résumé (PDF)"]:
    check("Work With Me: kept as it was: %r" % keep[:48], keep in wm)
check("Work With Me: the repeated résumé links are all still there (3)", wwm_page.count("Butsaba_Dusrai_LMT_Resume_License.pdf") >= 3, wwm_page.count("Butsaba_Dusrai_LMT_Resume_License.pdf"))
check("Work With Me: no 'Relocating from Chicago' wording", "Relocating" not in wm and "relocating" not in wm)
check("Work With Me: no announcement strip (it is an employment page)", not strip_tags(wwm_page))
home_page, _ = get("/")
check("The 'For Practices' menu label is unchanged", ">For Practices</a>" in home_page)
if SCHED in wm:
    print("INFO booking date on this site: " + BOOK_DATE)
    check("Work With Me: the independent-practice sentence is there once", wm.count(SCHED) == 1)
    check("Work With Me: the 'For clients' paragraph says corporate bookings begin %s and individual appointments are planned for summer 2027" % BOOK_DATE,
          wm.count("Corporate and group massage bookings begin " + BOOK_DATE + "; individual appointments at a dedicated Thai Solitude location are planned for summer 2027.") == 1)
    for label, path in (("Home", "/"), ("About", "/about/"), ("Services", "/services/"), ("Contact", "/contact/"), ("Thai Massage", "/thai-massage/"), ("Therapeutic Massage", "/therapeutic-massage/"), ("Deep Tissue Massage", "/deep-tissue-massage/")):
        pg, c = get(path)
        check("%s: returns 200 and holds exactly one availability strip" % label, c == 200 and len(strip_tags(pg)) == 1, c)
        bt = text_of(re.search(r'<div\b[^>]*class="[^"]*\bts-corp-band\b[^"]*"[^>]*>.*?</div>\s*</div>', pg, re.S).group(0)) if strip_tags(pg) else ""
        check("%s: the strip names the availability, links to the corporate page and avoids the house-list words" % label,
              ("Currently booking corporate & group massage only." in bt or "Now accepting corporate & group massage bookings." in bt) and "summer 2027" in bt and "/corporate-massage-portland/" in pg
              and not wt.hits(bt, wt.VOICE + wt.CLAIMS + wt.EVIDENCE), bt[:60])
    for label, path in (("Thai Massage", "/thai-massage/"), ("Therapeutic Massage", "/therapeutic-massage/"), ("Deep Tissue Massage", "/deep-tissue-massage/")):
        pg, _ = get(path)
        ptxt = text_of(pg)
        if "How Do I Book a Session?" in ptxt:   # a copy that predates the page's FAQ (an old localhost) has nothing to correct
            check("%s: both corrected FAQ answers are there once and the old ones are gone" % label, ptxt.count(NEW_WHERE) == 1 and ptxt.count(NEW_BOOK) == 1 and OLD_WHERE not in ptxt and OLD_BOOK not in ptxt)
        else:
            print("INFO %s: this copy has no FAQ yet (nothing to correct)" % label)
    cp, _ = get("/corporate-massage-portland/")
    ctxt = text_of(cp)
    check("Corporate page: states that bookings begin %s, and that events are scheduled around her work commitments" % BOOK_DATE,
          ctxt.count("Corporate and group massage bookings begin " + BOOK_DATE + ". Arranged through an organization or venue at a shared location.") == 1 and ctxt.count(SCHED) == 1)
    check("Corporate page: no strip on the page itself", not strip_tags(cp))
else:
    print("INFO Work With Me and availability: the update is not on this site yet (the parts the plan says to keep were checked above)")

# ---- Chair Massage: the new service page (Ryan, 2026-10-30; chair-massage/install-chair-massage.php) ----------------------------------------------------------------------------------------
# A site without the page gets an INFO line; a site with it gets the strict checks (wording tiers, structure, 32 sources, 11 questions, strip, jump links, pictures, Services row).
cm, cm_code = get("/chair-massage/")
if cm_code != 200:
    print("INFO Chair Massage: the page is not on this site yet (chair-massage/install-chair-massage.php has not been applied here)")
else:
    cmain = re.search(r"<main.*?</main>", cm, re.S)
    mh_cm = cmain.group(0) if cmain else ""
    ct = text_of(mh_cm)
    check("Chair Massage body found", bool(ct))
    res_cm = wt.check(mh_cm)
    check("Chair Massage: the research heading found once", res_cm["found"])
    check("Chair Massage: no restricted wording outside the research section", not res_cm["before"], res_cm["before"])
    check("Chair Massage research section: no claim verbs, no we/our/us/studio", not res_cm["research"], res_cm["research"])
    check("Chair Massage source titles: no we/our/us/studio", not res_cm["cites"], res_cm["cites"])
    check("Chair Massage: H1 once", cm.count(">Chair Massage in Portland</h1>") == 1)
    CM_TITLE = "Chair Massage in Portland | Butsaba Dusrai, Oregon LMT" if BUNDLE_B else "Chair Massage | Butsaba Dusrai, Oregon LMT"
    cm_title = re.search(r"<title>(.*?)</title>", cm, re.S)
    check("Chair Massage: title tag", bool(cm_title) and html.unescape(cm_title[1]).strip() == CM_TITLE, html.unescape(cm_title[1]).strip() if cm_title else "")
    CM_DESC = "Chair massage in Portland with Butsaba Dusrai, LMT: short, seated, fully clothed sessions for workplaces and events, with pressure adjusted as you go."
    check("Chair Massage: meta description and matching og/twitter titles and descriptions", meta(cm, "name", "description") == CM_DESC and all(meta(cm, a, n) == v for a, n, v in (("property", "og:title", CM_TITLE), ("name", "twitter:title", CM_TITLE), ("property", "og:description", CM_DESC), ("name", "twitter:description", CM_DESC))), meta(cm, "name", "description"))
    bad_cm = [w for w in BAD if re.search(w, " ".join(filter(None, [meta(cm, "name", "description"), meta(cm, "property", "og:title"), meta(cm, "property", "og:description")])), re.I)]
    check("Chair Massage meta/og carry no restricted wording", not bad_cm, bad_cm)
    for hh in ["What Is Chair Massage?", "Common Reasons Clients Choose Chair Massage", "Who Can Benefit From Chair Massage?", "Questions About Chair Massage", "What To Expect During Your Appointment", "What the Research Says About Chair Massage"]:
        check("Chair Massage: heading %r once" % hh, ct.count(hh) == 1)
    check("Chair Massage: the strip under the header, once", len(strip_tags(cm)) == 1)
    check("Chair Massage: the jump-links row with four targets", len(re.findall(r'<nav [^>]*class="ts-jump"', cm)) == 1 and all(('href="#%s"' % a) in cm and ('id="%s"' % a) in cm for a in ("ts-what-it-is", "ts-common-reasons", "ts-who-can-benefit", "ts-what-to-expect")))
    check("Chair Massage: the safety sentence once (in the paragraph before the steps)", ct.count("New numbness, weakness, unexplained swelling or radiating pain means you should see a doctor before massage.") == 1)
    for qq in ["Do I Need to Undress for Chair Massage?", "How Long Is a Chair Massage?", "What Is the Massage Chair Like?", "Is Chair Massage the Same as a Mechanical Massage Chair?", "How Much Pressure Will You Use?",
               "How Is Chair Massage Different From Your Other Sessions?", "Will I Feel the Results Right Away?", "What Does the Host Need to Provide?", "Where Do Sessions Take Place?", "How Do I Book a Session?"]:
        check("Chair Massage: FAQ question %r appears once" % qq, ct.count(qq) == 1)
    check("Chair Massage: eleven questions", mh_cm.count('class="uagb-question"') == 11, mh_cm.count('class="uagb-question"'))
    check("Chair Massage: the 'Where Do Sessions Take Place?' answer says %s, once" % BOOK_DATE, ct.count("with bookings beginning %s. Individual appointments at a dedicated Thai Solitude location are planned for summer 2027. I do not offer individual house calls." % BOOK_DATE) == 1)
    faq_links = re.findall(r'uagb-faq-content[^>]*>(?:(?!</div>).)*?<a ([^>]+)>(.*?)</a>', mh_cm, re.S)
    check("Chair Massage: only the three approved Corporate FAQ links after Bundle A; no links before it",
          (len(faq_links) == 3 and all('href="/corporate-massage-portland/"' in attrs and text_of(label) == 'Corporate and Group Massage page' for attrs, label in faq_links)) if BUNDLE_A else not faq_links, faq_links)
    csumm = [wt.text(x) for x in re.findall(r"<summary>(.*?)</summary>", mh_cm, re.S)]
    check("Chair Massage: the five research panels in order", csumm == ["Reviews, and What They Say About Limits", "Stress, Anxiety and Blood Pressure", "Desk Work, Muscle Discomfort and Movement", "Nurses, Hospital Staff and Carers", "Sources"], csumm)
    cdet = re.findall(r"<details[^>]*>", mh_cm)
    check("Chair Massage: first panel open, the other four closed", [" open" in d for d in cdet] == [True, False, False, False, False], len(cdet))
    clinks = re.findall(r'<a [^>]*href="(https://[^"]+)"[^>]*>\[(\d+)\]</a>', mh_cm)
    corder = []
    for _, n in clinks:
        if int(n) not in corder:
            corder.append(int(n))
    check("Chair Massage: numbered markers run 1..N in order of first use", bool(corder) and corder == list(range(1, len(corder) + 1)), corder[-3:])
    cmap = {}
    for u, n in clinks:
        cmap.setdefault(n, set()).add(u)
    check("Chair Massage: every number points to exactly one source", all(len(v) == 1 for v in cmap.values()))
    check("Chair Massage: as many Sources entries as numbers (32)", len(re.findall(r'<cite class="ts-source">', mh_cm)) == len(corder) == 32, len(corder))
    chosts = {re.match(r"https://([^/]+)/", u).group(1) for u, _ in clinks if re.match(r"https://([^/]+)/", u)}
    check("Chair Massage: sources are only PubMed or NCCIH", chosts <= {"pubmed.ncbi.nlm.nih.gov", "www.nccih.nih.gov"}, sorted(chosts))
    check("Chair Massage: scope note (no mechanical chairs, no patients) and 'disappointing results' sentence once each",
          ct.count("studies of mechanical massage chairs, and of massage given to patients as part of their care, are not summarized here") == 1 and ct.count("I included the studies I found with disappointing results, not only the favorable ones.") == 1)
    check("Chair Massage: the discouraging results are there (no comparison group, cortisol unchanged, no significant effects in the catheterization laboratory, yoga did more)",
          "had no comparison group, so the lower blood pressure cannot be separated" in ct and "urinary cortisol and blood pressure did not differ significantly" in ct and "no significant effects were found on fatigue, pain, relaxation, stress or tension" in ct and "yoga did more" in ct)
    calts = re.findall(r'<img [^>]*?alt="([^"]*)"[^>]*?title="([^"]*)"', mh_cm)
    check("Chair Massage: one picture (Ryan's watercolor, the hero), tooltip equal to the alt text, none saying 'by Butsaba'", len(calts) == 1 and all(a1 and a1 == t1 for a1, t1 in calts) and "by Butsaba" not in mh_cm and 'chair-massage-tree.webp" alt=' in mh_cm, len(calts))
    check("Chair Massage: licence number in the footer", "LMT-29905" in cm)
    svc_cm, _ = get("/services/")
    check("Services: the Chair Massage row links to the new page, once, before the corporate row", svc_cm.count('href="/chair-massage/">Chair Massage</a>') == 1 and svc_cm.find('href="/chair-massage/">Chair Massage</a>') < svc_cm.find('href="/corporate-massage-portland/">Corporate &amp; Group Massage</a>'))
    check("Services: the watercolor appears once (the Chair Massage row)", svc_cm.count('chair-massage-tree.webp" alt=') == 1)

# Bundle A is one release: once Services carries it, verify every new path.
# Before it is applied, retain the historical-site gates above.
if BUNDLE_A:
    offers = {"thai-massage": "Thai massage", "therapeutic-massage": "Therapeutic massage", "deep-tissue-massage": "Deep tissue massage", "chair-massage": "Chair massage", "corporate-massage-portland": "Corporate & group massage"}
    service_pages = {"thai-massage": thm, "therapeutic-massage": th, "deep-tissue-massage": dtp, "chair-massage": cm}
    for slug, body in {**service_pages, "services": svc}.items():
        main_body = re.search(r"<main.*?</main>", body, re.S)
        body = main_body[0] if main_body else ""
        check(f"Bundle A {slug}: one closing Next step with the agreed date and both buttons",
              len(re.findall(r'<div class="[^"]*\bts-review-next\b', body)) == 1
              and f'Corporate and group massage bookings begin {BOOK_DATE}.' in body
              and '>Request a corporate massage quote</a>' in body and '>Ask a question</a>' in body
              and 'href="/corporate-massage-portland/"' in body and 'href="/contact/"' in body)
        if slug != "services":
            other = re.findall(r'<p\b[^>]*class="[^"]*\bts-review-other\b[^"]*"[^>]*>(.*?)</p>', body, re.S)
            expected = {f"/{s}/" for s in offers if s != slug}
            positions = (body.find('ts-review-other'), body.find('What the Research Says'), body.rfind('ts-source'), body.find('ts-review-next'))
            check(f"Bundle A {slug}: other four services before research; Next step after Sources",
                  len(other) == 1 and set(re.findall(r'href="([^"]+)"', other[0])) == expected
                  and 0 <= positions[0] < positions[1] and 0 <= positions[2] < positions[3],
                  {'links': re.findall(r'href="([^"]+)"', other[0]) if other else [], 'positions': positions})
    order = [svc.find('uagb-block-' + bid + '"') for bid in ("8bb921f3", "b3c7a91e", "1f6539c9", "4e23bf70", "75fb60e4")]
    check("Bundle A Services: Chair, Corporate, Therapeutic, Deep Tissue, Thai row order", min(order) >= 0 and order == sorted(order))
    check("Bundle A Services: availability sentence present once", text_of(svc).count("Right now I offer corporate and group massage only, at suitable Portland venues; individual appointments at a dedicated Thai Solitude location are planned for summer 2027.") == 1)
    jump = re.search(r'<nav[^>]+class="ts-jump".*?</nav>', svc, re.S)
    check("Bundle A Services: four jumps find their targets", bool(jump) and all(f'href="#{anchor}"' in jump[0] and f'id="{anchor}"' in svc for anchor in ("ts-corporate-group", "ts-chair-massage", "ts-thai-massage", "ts-what-to-expect")))
    about_a, _ = get("/about/")
    contact_a, _ = get("/contact/")
    check("Bundle A About: closing licence and three paths", bool(re.search(r'<p\b[^>]*class="[^"]*\bts-review-license\b', about_a)) and 'LMT-29905' in about_a and all(f'href="{p}"' in about_a for p in ("/services/", "/corporate-massage-portland/", "/contact/")))
    check("Bundle A Contact: corporate card, quote email and jump target", bool(re.search(r'<div class="[^"]*\bts-review-contact\b', contact_a)) and 'id="corporate-quote"' in contact_a and 'href="#corporate-quote"' in contact_a and '>Corporate quote</a>' in contact_a and 'subject=Corporate%20massage%20quote%20request' in contact_a)
    check("Bundle A Corporate: Chair link and mobile shortcuts", 'href="/chair-massage/"' in cp_page and 'class="ts-corp__sticky"' in cp_page and '>Request a quote</a>' in cp_page and '>Email butsabad@gmail.com</a>' in cp_page)
else:
    print("INFO Review Bundle A has not been applied here")

# Privacy and Accessibility gain explicit metadata in Bundle B. Before that
# release their approved auto-generated metadata remains outside these checks.
if BUNDLE_B:
    policy_meta = (
        ("Privacy Policy", "/privacy-policy/", "Privacy Policy | Thai Solitude", "How Thai Solitude handles your information online and as a client of Butsaba Dusrai, LMT: no forms, no tracking, nothing sold or shared for marketing."),
        ("Accessibility", "/accessibility/", "Accessibility | Thai Solitude", "What has been checked on thaisolitude.com for accessibility (WCAG 2.2 A and AA, October 2026), its known limitations, and how to report a barrier."),
    )
    for label, path, want_title, want_desc in policy_meta:
        pg, pcode = get(path)
        check(f"Bundle B {label}: page returns 200", pcode == 200, pcode)
        got_title = re.search(r"<title>(.*?)</title>", pg, re.S)
        check(f"Bundle B {label}: explicit approved title and description", bool(got_title) and html.unescape(got_title[1]).strip() == want_title and meta(pg, "name", "description") == want_desc)
        check(f"Bundle B {label}: matching og/twitter titles and descriptions", all(meta(pg, a, n) == v for a, n, v in (("property", "og:title", want_title), ("name", "twitter:title", want_title), ("property", "og:description", want_desc), ("name", "twitter:description", want_desc))))
        check(f"Bundle B {label}: title <= 60 and description <= 160", len(want_title) <= 60 and len(want_desc) <= 160, f"{len(want_title)}/{len(want_desc)}")

if BUNDLE_C:
    about_c, about_code = get("/about/")
    contact_c, contact_code = get("/contact/")
    privacy_c, privacy_code = get("/privacy-policy/")
    access_c, access_code = get("/accessibility/")
    c_pages = {"home": home_page, "services": svc, "thai-massage": thm, "therapeutic-massage": th,
               "deep-tissue-massage": dtp, "chair-massage": cm, "corporate-massage-portland": cp_page,
               "about": about_c, "contact": contact_c, "privacy-policy": privacy_c, "accessibility": access_c,
               "work-with-me": wwm_page}
    check("Bundle C: About, Contact, Privacy and Accessibility return 200", all(c == 200 for c in (about_code, contact_code, privacy_code, access_code)))
    for slug, pg in c_pages.items():
        check(f"Bundle C {slug}: accessibility runtime present", has_c_runtime(pg))
        if slug not in ("corporate-massage-portland", "work-with-me"):
            strips = strip_tags(pg)
            check(f"Bundle C {slug}: one named booking-notice landmark", len(strips) == 1 and 'role="region"' in strips[0] and 'aria-label="Booking notice"' in strips[0])
    semantic_headings = {
        "home": (("e6d24376", "Experience"), ("0a8b3bc1", "Clear Communication"), ("eee8f105", "Thai and Therapeutic Training")),
        "services": (("2c1fc22f", "Consultation and Assessment"), ("3e2e789f", "Planning the Session"), ("4fdc0b6f", "The Massage"), ("3b941aaf", "After the Session")),
        "thai-massage": (("6c30a7ff", "Consultation and Assessment"), ("0422be24", "Personalized Session Plan"), ("863c3724", "Thai Massage Session"), ("cf15aaf5", "Post-Session Guidance")),
        "therapeutic-massage": (("b0f9a0b1", "Consultation and Assessment"), ("d2b1c2d3", "Planning the Session"), ("f4d3e4f5", "Therapeutic Massage Work"), ("b6f5a6b7", "Post-Session Guidance")),
        "deep-tissue-massage": (("2c1fc22f", "Consultation and Assessment"), ("3e2e789f", "Planning the Session"), ("4fdc0b6f", "Deep Tissue Work"), ("3b941aaf", "Post-Session Guidance")),
        "chair-massage": (("78b38aa2", "Quick Check-In"), ("05fef95b", "Settling Into the Chair"), ("084d247e", "Chair Massage Work"), ("f14b2a44", "Before You Go")),
    }
    for slug, headings in semantic_headings.items():
        for bid, label in headings:
            check(f"Bundle C {slug}: {label!r} is an H3 in its original block", has_block_heading(c_pages[slug], bid, label))
    corp_headings = [text_of(h) for h in re.findall(r'<h3\b[^>]*class="[^"]*\bts-corp__card-title\b[^"]*"[^>]*>(.*?)</h3>', cp_page, re.S)]
    check("Bundle C Corporate: three unchanged card titles are H3s", corp_headings == ["Chair massage for teams", "Three- or five-hour booking blocks", "Table massage by arrangement"])
    hidden = [(attrs, text_of(label)) for attrs, label in re.findall(r'<h2\b([^>]*)>(.*?)</h2>', svc, re.S) if 'id="ts-services-heading"' in attrs]
    check("Bundle C Services: hidden H2 names the service rows", len(hidden) == 1 and hidden[0][1] == "Massage services" and "screen-reader-text" in hidden[0][0] and svc.find('id="ts-services-heading"') < svc.find('uagb-block-8bb921f3'))
    check("Bundle C Therapeutic: FAQ heading appears once", [text_of(h) for h in re.findall(r'<h2\b[^>]*>(.*?)</h2>', th, re.S)].count("Questions About Therapeutic Massage") == 1)
    map_url = "https://maps.google.com/maps?q=Mt.%20Tabor%2C%20Portland&z=12&hl=en&t=m&output=embed&iwloc=near"
    map_frames = []
    for tag in re.findall(r'<iframe\b[^>]*>', contact_c, re.S):
        attrs = {k: html.unescape(v) for k, v in re.findall(r'([\w:-]+)="([^"]*)"', tag)}
        if attrs.get("title") == "Map of Portland, Oregon":
            map_frames.append(attrs)
    check("Bundle C Contact: lazy named map iframe preserves the Mt. Tabor query", len(map_frames) == 1 and map_frames[0].get("src") == map_url and map_frames[0].get("loading") == "lazy" and not re.search(r'<embed\b', contact_c, re.I))
    access_text = text_of(access_c)
    research_pages = "Thai Massage, Therapeutic Massage, Deep Tissue Massage and Chair Massage pages"
    chair_checks = "The Chair Massage research summaries and Sources panel were included in the mouse, Enter and Space keyboard, and light- and dark-version text-contrast checks."
    check("Bundle C Accessibility: both research lists include Chair", access_text.count(research_pages) == 2)
    check("Bundle C Accessibility: new panels' checks and November 1 date", access_text.count(chair_checks) == 1 and access_text.count("Last updated: November 1, 2026.") == 1)
    check("Bundle C Accessibility: screen-reader, specialist-audit and existing limitations remain", all(t in access_text for t in ("No screen reader was used in this testing.", "This was not a full audit by an accessibility specialist", "two links inside the expandable sections may not be announced clearly by screen readers.", "The map on the Contact page is provided by Google")))

print("\nRESULT: " + ("all checks passed" if not fails else f"{fails} check(s) FAILED"))
sys.exit(1 if fails else 0)

# ref: ./install-client-pages-local.php — the installer whose result this checks; ../home/verify-home.py — the Home counterpart

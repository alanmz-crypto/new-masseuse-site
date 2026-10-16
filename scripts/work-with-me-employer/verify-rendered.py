#!/usr/bin/env python3
"""Read-only check of the rendered local Work With Me page (stdlib only).

Usage: python3 -I scripts/work-with-me-employer/verify-rendered.py [base_url]
Default base_url is http://localhost:8090. It only issues GET/HEAD requests.
"""
import html
import re
import sys

BASE = (sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8090").rstrip("/")
TITLE = "Butsaba Dusrai, LMT | Thai Bodywork | Portland, OR"
DESC = ("Butsaba Dusrai, Oregon LMT-29905, is based in Portland and open to full-time or part-time roles. "
        "Explore her Thai and deep tissue massage experience.")
EMAIL = "butsabad@gmail.com"
MAILTO = ("mailto:butsabad@gmail.com?subject=LMT%20position%20in%20Portland"
          "&body=Hi%20Butsaba%2C%0A%0APractice%3A%20%0ARole%20and%20hours%3A%20%0ABest%20time%20to%20talk%3A%20")
RESUME = "/wp-content/uploads/2026/10/Butsaba_Dusrai_LMT_Resume_License.pdf"
LOOKUP = "https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx"
FORBIDDEN = [
    r"\bheal", r"\bproof\b", r"\bmeasurable\b", r"\beffective", r"pain relief", r"relieve pain",
    r"pain management", r"\bsciatica\b", r"\bheadaches?\b", r"\bchronic\b", r"\badhesions?\b",
    r"\bcorrective\b", r"root of the restriction", r"\baccelerat", r"restore balance",
    r"nervous system", r"\bcirculation\b", r"\banxiety\b", r"\bcancer\b", r"\bneurological\b",
    r"\bdisabilit", r"\bprevention\b", r"\blasting\b", r"long-term wellness", r"overall health",
    r"real results", r"\bstudio\b", r"\b(we|our|us)\b", r"Thai Lotus", r"October 28",
    r"Portland[- ]area", r"Portland metro",
]

results = []


def check(name, ok, detail=""):
    results.append((ok, name, detail))
    print(("PASS " if ok else "FAIL ") + name + (f"  [{detail}]" if detail else ""))


def get(path, method="GET"):
    """Read-only GET/HEAD through curl: SiteGround's firewall answers 403 to Python's HTTP client and to browser-spoofing agents, but accepts curl's own."""
    import subprocess, tempfile
    with tempfile.NamedTemporaryFile() as hf, tempfile.NamedTemporaryFile() as bf:
        cmd = ["curl", "-sS", "-L", "--max-time", "30",
               "-D", hf.name, "-o", bf.name, "-w", "%{http_code}", BASE + path]
        if method == "HEAD":
            cmd.insert(1, "-I")
        out = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
        code = int(out.stdout.strip() or 0)
        raw = open(hf.name, encoding="latin-1").read().split("\r\n\r\n")
        last = [b for b in raw if b.strip()][-1] if raw else ""
        headers = {}
        for line in last.splitlines()[1:]:
            if ":" in line:
                k, v = line.split(":", 1)
                headers[k.strip()] = v.strip()
        class H(dict):
            def get(self, k, d=""):
                for kk, vv in self.items():
                    if kk.lower() == k.lower():
                        return vv
                return d
        if code >= 400:
            raise SystemExit(f"HTTP {code} for {BASE + path}")
        return code, H(headers), open(bf.name, "rb").read()


status, _, body = get("/work-with-me/")
page = body.decode("utf8")
check("page returns 200", status == 200)

head = page.split("</head>")[0]
title = re.search(r"<title>(.*?)</title>", head, re.S)
check("title is the approved employer title", bool(title) and html.unescape(title.group(1)).strip() == TITLE,
      title.group(1).strip() if title else "missing")


def meta(attr, name):
    m = re.search(r'<meta[^>]+%s="%s"[^>]+content="([^"]*)"' % (attr, re.escape(name)), head)
    return html.unescape(m.group(1)) if m else None


for attr, name in (("name", "description"), ("property", "og:description"), ("name", "twitter:description")):
    value = meta(attr, name)
    check(f"{name} is the approved description, no banner text", value == DESC and "Coming soon" not in (value or ""),
          (value or "missing")[:80])
for attr, name in (("property", "og:title"), ("name", "twitter:title")):
    value = meta(attr, name)
    check(f"{name} matches the approved title", value == TITLE, value or "missing")
check("robots stays index, follow", meta("name", "robots") == "index, follow", str(meta("name", "robots")))
check("share image preserved", bool(meta("property", "og:image")) and "butsaba_homepage" in (meta("property", "og:image") or ""))

main = re.search(r'<div class="entry-content[^"]*"[^>]*>(.*?)</article>', page, re.S)
check("page content wrapper found", bool(main))
content = main.group(1) if main else page
content = re.sub(r"<(script|style)[^>]*>.*?</\1>", "", content, flags=re.S)
text = re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", content)))

banner = re.search(r'class="ts-coming-soon[ "]', content)
check("coming-soon banner removed from this page (approved, this page only)", not banner)
check("exactly one h1", len(re.findall(r"<h1[ >]", content)) == 1)
levels = [int(n) for n in re.findall(r"<h([1-6])[ >]", content)]
check("heading levels never skip downward", all(b - a <= 1 for a, b in zip(levels, levels[1:])), str(levels))
mailtos = re.findall(r'href="(mailto:[^"]*)"', content)
check("five email links, all the same address, subject and prefilled message body", len(mailtos) == 5 and len(set(mailtos)) == 1 and html.unescape(mailtos[0]) == MAILTO, f"{len(mailtos)} links, {len(set(mailtos))} distinct")
from urllib.parse import unquote
check("prefilled message asks for practice, role and hours, best time to talk", unquote(html.unescape(mailtos[0])).split("body=")[-1] == "Hi Butsaba,\n\nPractice: \nRole and hours: \nBest time to talk: " if mailtos else False)
check("visible email text present", EMAIL in text)
wb=re.findall(r'<div class="wp-block-button(?=[\s"])([^"]*)"', content)
check("all page buttons use the same (outline) style", len(wb) == 6 and all("is-style-outline" in w for w in wb), f"{len(wb)} buttons")
check("three résumé links (hero, closing, mobile bar)", content.count('href="%s"' % RESUME) == 3)
sticky = re.search(r'<nav class="ts-wwm__sticky" aria-label="Contact shortcuts">(.*?)</nav>', content, re.S)
check("mobile bar is a labelled nav with exactly the email and résumé links, no script",
      bool(sticky) and re.findall(r'>([^<]+)</a>', sticky.group(1)) == ["Email me", "Résumé"] and "<script" not in content)
check("mobile bar CSS is present, hides above 600px, pads for the home indicator",
      all(k in page for k in (".ts-wwm .ts-wwm__sticky{display:none}", "env(safe-area-inset-bottom", "max-width:600px")))
close = re.search(r"For Portland practices(.*?)Check my credentials first", text)
check("closing section: direct invitation, based in Portland, asks for practice name, role and a time to talk",
      bool(close) and "Let’s find a time to meet." in text and "Send me your practice’s name, the role and a good time to talk." in text, "")
check("closing section has no unconfirmed commitments (reply time, interview practical, hours, start date, 'I live')",
      not re.search(r"business day|within \d|practical|I live in|starting [A-Z]|\d+\s*[–-]\s*\d+ hours|contractor|employee", text.split("Let’s find a time to meet.")[-1].split("For clients")[0], re.I))
check("closing credentials line: license and BLS, with the license lookup link",
      bool(re.search(r"Check my credentials first: Oregon license LMT-29905 \(\s*Oregon license lookup\s*\) · BLS current through September 2028\.", text)))
check("closing block has an anchor (#contact)", 'id="contact"' in content)
check("license number shown once in credentials", content.count("License No. LMT-29905") == 1)
check("license lookup link kept, labelled neutrally",
      content.count('href="%s"' % LOOKUP) == 3 and "Oregon license lookup" in text and "Verify license" not in text)
check("SOAP is labelled as training at Estelle, never as workplace experience", "Training in treatment planning, SOAP-note documentation" in text and "Estelle Medical Academy" in text and not re.search(r"(used|use of|experience with|proficien\w*)\s+(an?\s+)?(EHR|charting software|ChARM)", text, re.I))
estelle_total = content.count("Estelle Medical Academy")
estelle_linked = len(re.findall(r'<a [^>]*href="https://www\.estellemedical\.edu/"[^>]*>Estelle Medical Academy</a>', content))
check("every mention of Estelle Medical Academy is a link", estelle_total >= 2 and estelle_total == estelle_linked,
      f"{estelle_linked} linked of {estelle_total}")
check("deep tissue named in the opening, with Swedish and relaxation", all(k in text.split("Massage experience")[0] for k in ("Thai massage is my specialty", "deep tissue", "Swedish and relaxation massage")))
check("Thai Solitude described as the independent practice", "Thai Solitude is my independent practice." in text)
check("Portland location and full-time/part-time openness stated", "I am based in Portland and welcome full-time or part-time massage opportunities" in text)
check("no move date, countdown, or Present label", not re.search(r"relocat|moving|arriv|(?<!2024–)\bPresent\b|currently|this week|next week", text, re.I) and not False)
check("November 1 appears only in the two approved phrases (start from it; in-person interviews from it)", text.count("November 1") == 2 and "From November 1 · open for days, evenings and weekends" in text and "in person from November 1" in text)
check("Windy City workload stated as past, approximate, with session lengths", "provided about 18–24 massages a week in 50-, 80- and 110-minute sessions" in text)
check("InterContinental stated as an assignment, not an employer", "including assignments at the InterContinental hotel spa" in text)
check("Windy City scheduling and payout records mentioned", "scheduling and payout records" in text)
check("treatment planning appears only as Estelle education (one list line)", "Training in treatment planning, SOAP-note documentation, and postural and range-of-motion work" in text and text.lower().count("treatment plan") == 1)
check("orientation sentence present, no charting disclaimer on the page", "I would welcome an orientation to your team’s documentation standards" in text and "routine charting" not in text)
check("experience is a Spectra FAQ accordion with 3 items, like About", "uagb-faq-layout-accordion" in content and len(re.findall(r'uagb-faq-child__outer-wrap', content)) == 3, f"{len(re.findall(r'uagb-faq-child__outer-wrap', content))} items")
check("accordion answers keep their links (Windy City, Thai Solitude)", 'href="https://windycitymassage.com/"' in content and re.search(r'<a href="/">Thai Solitude</a>', content) is not None)
check("three experience items with the fuller titles", all(k in text for k in ("Windy City Massage — Chicago", "Thai Solitude — independent practice", "Earlier Thai salon experience — Chicago")))
check("Thai Solitude founded in Chicago in 2024, Ayurvedic stretching, fully clothed Thai", all(k in text for k in ("in Chicago in 2024", "Ayurvedic Thai stretching", "fully clothed Thai massage")))
check("salon card keeps the specifics but not the name", all(k in text for k in ("coconut oil, Thai balm, hot stones and warm towels", "responsibility for the premises")) and "Thai Lotus" not in text)
check("training dates and coursework, BA kept", all(k in text for k in ("May 2025 – March 2026", "July 2024", "postural and range-of-motion work", "Thammasat University")))
unsupported = [p for p in (r"oncolog", r"retention|repeat-client", r"myofascial|craniosacral|prenatal|hypermobility|cupping|gua sha", r"six years", r"every time", r"guarantee", r"put clients", r"illustrative|placeholder") if re.search(p, text, re.I)]
check("no unsupported claims or placeholder story (handoff section 12/13)", not unsupported, ", ".join(unsupported))
story_paras = (
    "One of my clients falls asleep during his massages. Before I start, I ask what he’d like that day, since it isn’t always the same as last time. He tells me when the pressure feels right, and I begin slowly.",
    "At first he likes to talk, and I like to listen. As his answers get shorter and he goes quiet, I let the conversation rest too. I check in on the pressure while he’s still awake enough to answer, then keep my hands easy and steady while he drifts off.",
    "I don’t add a stronger technique just because it’s part of his usual routine. To me, paying attention means watching how someone responds and going from there.")   # Ryan cut the "he always ends up asleep" sentence and changed "regulars" to "clients" in the staging26 editor, 2026-10-16
check("approved story 'Attention throughout the session' is present, verbatim, once", text.count("Attention throughout the session") == 1 and all(text.count(pp) == 1 for pp in story_paras))
check("the full story sits in the approach section and the standards sentence follows it", 0 <= text.find("Attention throughout the session") < text.find("Throughout, I keep comfort, draping, professional boundaries, and a clean, prepared room in view."))
check("sleep appears only inside the approved story, no sedation or every-time language", text.lower().count("sleep") == 1 and not re.search(r"sedat|put (clients|people) to sleep|every time|guarantee", text, re.I))
check("trigger point and sports massage are named once, in Ryan's 'What I bring to a team' sentence, not in the hero", text.count("My other modalities include trigger point work and sports massage alongside my Thai, deep tissue, Swedish and relaxation sessions.") == 1 and "trigger point" not in text.split("Massage experience")[0].lower())
check("closing paragraph states what she is looking for (Ryan's staging26 wording, 'clients' not 'customers')",
      text.count("I am looking for a Portland practice where I can get to know returning clients, work alongside a team that communicates clearly, and keep learning.") == 1
      and "My other modalities include trigger point work and sports massage. I enjoy" not in text)
check("draping, boundaries and room preparation stated plainly, once, after the story", text.count("Throughout, I keep comfort, draping, professional boundaries, and a clean, prepared room in view.") == 1)
check("the ask / adjust / keep-in-view cards are gone and exactly one numbered list remains (the three technique items)", not any(k in text for k in ("What I ask", "What I adjust", "What I keep in view")) and len(re.findall(r'<p class="ts-wwm__number[ "]', content)) == 3)
check("at-a-glance facts list carries exactly Ryan's confirmed answers (roles, hours, schedule, interviews, reply), in the availability section", all(k in text for k in (
    "Roles — Employee or independent contractor", "Hours — Part-time or full-time", "Start — From November 1 · open for days, evenings and weekends",
    "Interviews — By phone or video call now, in person from November 1", "Reply — Within one business day")) and text.count("Within one business day") == 1 and 0 <= text.find("Roles — Employee") < text.find("Experience I would bring"))
check("references line present, linked, and says 'on request' only", bool(re.search(r"References available on request: Windy City Massage\s*, a former supervisor, and an instructor from Estelle Medical Academy\s*\.", text)) and 'href="https://windycitymassage.com/"' in content)
check("nothing unverified is published: no liability-insurance line (in progress), no ABMP/AMTA membership, no testimonial, and the only reply promise is the Reply row (within one business day, Ryan 2026-10-16)", not re.search(r"liability insurance|ABMP|AMTA|membership|testimonial", text, re.I) and text.count("business day") == 1 and not re.search(r"12 to 24|12–24", text))
check("no 'Coming Soon' notice, note or November launch line in the page body (Ryan removed it site-wide, 2026-10-16)", not re.search(r"coming soon|in November", text, re.I))
check("footer panel CSS: compact band for the one-line 'Portland, OR' footer (the widget text itself lives in options)", "#colophon .site-primary-footer-wrap{padding-top:2.2rem;padding-bottom:2.2rem}" in page and "#colophon .ts-footer-location{" in page)

# Thai signature section (handoff section 13): approved copy, placement, honest image treatment.
THAI_IMG = "/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp"
THAI_IMG_SHA = "2f2fc1b345e6bfbf5bfec7006422af17841d6b905e16489318341c188c46d545"
THAI_ALT = "Butsaba Dusrai applying pressure to the upper back of a clothed person leaning forward on a mat."   # Ryan confirmed on 2026-10-16 that the photo is her
thai = re.search(r'<div[^>]*\bid="thai-approach"[^>]*>(.*?)<div[^>]*class="wp-block-group ts-wwm__approach', content, re.S)   # attribute order differs after an editor save
ttext = re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", thai.group(1)))) if thai else ""
check("Thai signature section is present once, after experience and before 'Listen. Assess. Adapt.'",
      bool(thai) and content.count('id="thai-approach"') == 1
      and 0 <= text.find("Experience I would bring") < text.find("My signature Thai approach") < text.find("Listen. Assess. Adapt."))
check("Thai section carries the approved copy (eyebrow, original intro, three elements) and Ryan's 'What I bring to a team' text word for word, once", bool(thai) and all(k in ttext for k in (
    "Thai massage · Fully clothed bodywork", "My signature Thai approach",
    "Thai massage is the foundation of my work. I shape each session around the person’s goals, comfort and feedback, drawing on three elements.",
    "01 Rhythmic compression Steady, repeated pressure, adjusted to the client’s comfort.",
    "02 Guided movement Movement paced and adapted in response to the client’s feedback.",
    "03 Assisted stretching Supported stretches with the range adjusted to what feels comfortable.",
    "What I bring to a team My Thai background also informs my therapeutic and deep tissue work. It gives me several ways to adapt a session as the client’s needs and preferences become clearer. My other modalities include trigger point work and sports massage alongside my Thai, deep tissue, Swedish and relaxation sessions.")) and text.count("My Thai background also informs my therapeutic and deep tissue work.") == 1 and "It also informs my therapeutic" not in text)
check("experience headers carry their dates (Windy City 2025–2026, Thai Solitude 2024–present, salon 2020–2025), Windy City never 'present'",
      all(k in text for k in ("Windy City Massage — Chicago · 2025–2026", "Thai Solitude — independent practice · 2024–present", "Earlier Thai salon experience — Chicago · 2020–2025")) and not re.search(r"Windy City Massage — Chicago · \d{4}\s*[–-]\s*present", text, re.I))
timg = re.search(r'<img[^>]+src="%s"[^>]*>' % re.escape(THAI_IMG), thai.group(1)) if thai else None
check("Thai image: alt text names her (identity confirmed by Ryan) and says 'a clothed person', not a client session; square space reserved by CSS; never cover-cropped",
      bool(timg) and ('alt="%s"' % THAI_ALT) in timg.group(0)
      and ".ts-wwm .ts-wwm__thai-image img{display:block;width:100%;height:auto;aspect-ratio:1/1;" in page
      and not re.search(r"object-fit\s*:\s*cover", page.split(".ts-wwm__thai-image img", 1)[-1][:200])
      and "Butsaba Dusrai" in THAI_ALT and not re.search(r"client session|photograph|portrait", THAI_ALT, re.I))
# The block editor rejects attributes its blocks do not produce ("Block contains unexpected or invalid content"):
# aria-label on a Button block's link, width/height on the Image block's img. Keep the markup to what the blocks save.
check("block-editor safety: no aria-label on Button-block links, no width/height on the Thai image (editor would flag them invalid)",
      not re.search(r'wp-block-button__link[^>]*aria-label', content) and not (timg and re.search(r'\s(width|height)="', timg.group(0))))
check("Thai image has no caption (Ryan removed it, 2026-10-16); no testimonial or client-identity text near it",
      "figcaption" not in (thai.group(1) if thai else "") and "shown for illustration" not in ttext and not re.search(r"testimonial|my client|Butsaba", ttext))
check("Thai section makes no floor/table, benefit, exclusivity or technique-name claims",
      not re.search(r"\bfloor\b|\btable\b|benefit|exclusive|proprietary|invented|certified|\bnuad\b|\bsen\b|acupress|reflex", ttext, re.I))
_, _, thai_bytes = get(THAI_IMG)
import hashlib
check("served Thai image is exactly the reviewed WebP", hashlib.sha256(thai_bytes).hexdigest() == THAI_IMG_SHA and thai_bytes[:4] == b"RIFF" and thai_bytes[8:12] == b"WEBP",
      f"{len(thai_bytes)} bytes")

ids = set(re.findall(r'\bid="([^"]+)"', content))
anchors = re.findall(r'href="#([^"]+)"', content)
check("every in-page anchor resolves", all(a in ids for a in anchors), ", ".join(anchors))

hits = [p for p in FORBIDDEN if re.search(p, text, re.I)]
check("no restricted OBMT wording in page copy", not hits, ", ".join(hits))

# The résumé the link actually serves must agree with the page (checked on the served bytes, not a source file).
def resume_text():
    import subprocess, tempfile
    code, _, data = get(RESUME)
    with tempfile.NamedTemporaryFile(suffix=".pdf") as f:
        f.write(data); f.flush()
        pages = subprocess.run(["pdfinfo", f.name], capture_output=True, text=True).stdout
        txt = subprocess.run(["pdftotext", f.name, "-"], capture_output=True, text=True).stdout
    return int(re.search(r"Pages:\s+(\d+)", pages).group(1)) if "Pages:" in pages else 0, txt
rpages, rtxt = resume_text()
bad = [p for p in (r"Relocating", r"Thai Lotus", r"\bstudio\b", r"April 2025\s*[–-]\s*Present") if re.search(p, rtxt, re.I)]
check("served résumé has no relocation status, excluded salon name, 'studio', or Windy City 'Present'", not bad, ", ".join(bad) or f"{rpages} page(s)")
check("served résumé agrees with the page (Portland, 2025–2026, Founded in Chicago, InterContinental assignment)", all(k in rtxt for k in ("Portland, OR", "2025–2026", "Founded in Chicago", "InterContinental", "50-, 80-, and 110-minute")))
status, headers, _ = get(RESUME, "HEAD")
check("linked résumé PDF resolves", status == 200 and "pdf" in headers.get("Content-Type", ""), headers.get("Content-Type", ""))

nav = re.findall(r'<a[^>]+href="[^"]*/work-with-me/"[^>]*>\s*(?:<span[^>]*>)?\s*([^<]+)', page)
labels = [n.strip() for n in nav]
check("shared menu label is 'For Practices' and no 'Work With Me' label remains", "For Practices" in labels and "Work With Me" not in labels, str(labels[:4]))

# Contact page: an employer arriving from the header's "Get in Touch" button (it points at /contact/ on every page)
# must find a route to hiring, and the client details must be unchanged.
import os
if os.environ.get("WWM_SKIP_CONTACT") == "1":
    print("SKIP Contact page checks (WWM_SKIP_CONTACT=1: the Contact page is not part of this push)")
else:
    _, _, cbody = get("/contact/")
    cpage = cbody.decode("utf8")
    cmain = re.search(r'<div class="entry-content[^"]*"[^>]*>(.*?)</article>', cpage, re.S)
    ccontent = re.sub(r"<(script|style)[^>]*>.*?</\1>", "", cmain.group(1) if cmain else cpage, flags=re.S)
    sec = re.search(r'<div class="[^"]*ts-wwm__invitation[^"]*" id="employers">(.*?)</div>\s*</div>', ccontent, re.S)
    stext = re.sub(r"\s+", " ", html.unescape(re.sub(r"<[^>]+>", " ", sec.group(1)))) if sec else ""
    check("Contact has an employer section (#employers) with one h2", bool(sec) and len(re.findall(r"<h2[ >]", sec.group(1))) == 1 and "Hiring a massage therapist?" in stext)
    check("Contact employer section: Portland, full-time or part-time, links to email, employer page and résumé",
          "I am based in Portland and open to full-time or part-time massage roles." in stext
          and [html.unescape(m) for m in re.findall(r'href="(mailto:%s\?subject=[^"]*)"' % re.escape(EMAIL), sec.group(1))] == [MAILTO]
          and 'href="/work-with-me/"' in ccontent and ('href="%s"' % RESUME) in ccontent)
    check("Contact still has exactly one h1 and its client details (phone, email, address) and no Coming-soon banner",
          len(re.findall(r"<h1[ >]", ccontent)) == 1 and "tel:+13125394328" in ccontent
          and ("mailto:%s" % EMAIL) in ccontent and "Mt. Tabor, Portland" in ccontent and "ts-coming-soon" not in ccontent)
    check("no restricted OBMT wording in the Contact employer section", not [p for p in FORBIDDEN if re.search(p, stext, re.I)])
    check("header Get in Touch button still points at /contact/ (shared setting, unchanged)", 'href="/contact/"' in cpage and "Get in Touch" in cpage)

words = len(text.split())
print(f"INFO visible words in page content (incl. banner and clients section): {words}")
failed = [n for ok, n, _ in results if not ok]
print("\nRESULT:", "all checks passed" if not failed else f"{len(failed)} failed: " + "; ".join(failed))
sys.exit(1 if failed else 0)

# ref: ./install-local.php — applies the content, meta and CSS block these checks assert on
# ref: ../../AGENTS.md — restricted-wording list mirrored in FORBIDDEN

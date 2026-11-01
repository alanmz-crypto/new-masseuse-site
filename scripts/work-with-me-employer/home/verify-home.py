#!/usr/bin/env python3
"""Read-only check of the rendered Home page for the employer-path trial (localhost by default).
Usage: python3 verify-home.py [base_url]     e.g. http://localhost:8090
Reads rendered HTML with curl; changes nothing."""
import html, re, subprocess, sys
from html.parser import HTMLParser

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


def has_block_heading(source, block_id, label):
    starts = list(re.finditer(r'<div\b[^>]*class="[^"]*\buagb-block-' + re.escape(block_id) + r'\b[^"]*"[^>]*>', source))
    if len(starts) != 1:
        return False
    heading = re.match(r'\s*(?:<div\b[^>]*class="[^"]*\buagb-separator\b[^"]*"[^>]*>\s*</div>\s*)?<h3\b[^>]*class="[^"]*\buagb-heading-text\b[^"]*"[^>]*>(.*?)</h3>', source[starts[0].end():], re.S)
    return bool(heading) and text_of(heading[1]) == label


HOME_CTAS = (
    ("b9ecca73", ("contact/", "Ask about a session"), ("/contact/", "Ask about a session")),
    ("36c16d4d", ("about/", "About Thai Solitude"), ("/about/", "About Thai Solitude")),
    ("2c16c927", ("services/", "Services"), ("/therapeutic-massage/", "Therapeutic massage")),
    ("591505c6", ("services/", "Services"), ("/deep-tissue-massage/", "Deep tissue massage")),
    ("416130fd", ("services/", "Services"), ("/thai-massage/", "Thai massage")),
)


def home_ctas(source):
    """Read each CTA within its own block, without counting menus or jump links."""
    class CTAParser(HTMLParser):
        def __init__(self):
            super().__init__()
            self.depth, self.active, self.anchor = 0, [], None
            self.links = {block_id: [] for block_id, _, _ in HOME_CTAS}
            self.counts = dict.fromkeys(self.links, 0)

        def handle_starttag(self, tag, attrs):
            attrs = dict(attrs)
            if tag == "div":
                self.depth += 1
                classes = attrs.get("class", "").split()
                for block_id in self.links:
                    if "uagb-block-" + block_id in classes:
                        self.counts[block_id] += 1
                        self.active.append((block_id, self.depth))
            if tag == "a" and self.active:
                self.anchor = [attrs.get("href"), []]
                self.links[self.active[-1][0]].append(self.anchor)

        def handle_endtag(self, tag):
            if tag == "a":
                self.anchor = None
            if tag == "div":
                self.active = [(block_id, depth) for block_id, depth in self.active if depth != self.depth]
                self.depth -= 1

        def handle_data(self, data):
            if self.anchor is not None:
                self.anchor[1].append(data)

    parser = CTAParser()
    parser.feed(source)
    return tuple(
        (parser.links[block_id][0][0], re.sub(r"\s+", " ", "".join(parser.links[block_id][0][1])).strip())
        if parser.counts[block_id] == 1 and len(parser.links[block_id]) == 1 else None
        for block_id, _, _ in HOME_CTAS
    )


page, code = get("/")
check("Home returns 200", code == 200, code)
m = re.search(r'<div class="wp-block-uagb-container[^"]*uagb-block-e9f89e50[^"]*".*?(?=<div class="wp-block-uagb-container[^"]*uagb-block-c194462d)', page, re.S)
hero = m.group(0) if m else ""
check("hero container found, followed by the next section", bool(hero))
hero_text = text_of(hero)

check("exactly one H1, wording unchanged", len(re.findall(r"<h1[ >]", page)) == 1 and "Thai & Therapeutic Massage with Butsaba Dusrai, Oregon LMT" in html.unescape(page))
trust = re.findall(r'<p class="ts-home-trust[^"]*">(.*?)</p>', hero, re.S)
check("licence line appears once in the hero", len(trust) == 1, len(trust))
t = trust[0] if trust else ""
# onpage-portland (Ryan, 2026-10-29) changed "Mt. Tabor, Portland" to "Portland, OR" here; a site not changed yet still shows the old wording, which stays approved until it is.
_trust_text = text_of(t).replace(" )", ")").replace("( ", "(")
check("licence line wording: Oregon License LMT-29905 (verify) · Portland, OR (or, before onpage-portland, · Mt. Tabor, Portland)", _trust_text in ("Oregon License LMT-29905 (verify) · Mt. Tabor, Portland", "Oregon License LMT-29905 (verify) · Portland, OR"), text_of(t))
check("verify link goes to the Oregon licence lookup", 'href="https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx"' in t)
check("licence line sits between the H1 and the lead text", 0 <= hero.find("<h1") < hero.find("ts-home-trust") < hero.find("Thai and therapeutic massage that starts with"))

cta_links = home_ctas(page)
old_ctas = tuple(old for _, old, _ in HOME_CTAS)
new_ctas = tuple(new for _, _, new in HOME_CTAS)
check("Home contact, About and service-card buttons use one complete approved state (before or after Bundle A)", cta_links in (old_ctas, new_ctas), cta_links)
check("hero button says 'Ask about a session' and goes to the same approved Contact target", bool(re.search(r'<a href="%s"[^>]*class="uagb-infobox-cta-link[^"]*"[^>]*><span class="uagb-inline-editing">Ask about a session</span>' % re.escape("/contact/" if cta_links == new_ctas else "contact/"), hero)))
check("no 'Get in Touch' left in the page body (header copies are menu settings)", "Get in Touch" not in hero)

check("no hiring band on Home (tried and dropped at Ryan's request, 2026-10-16)", "ts-home-employers" not in page and "Hiring a massage therapist" not in page)
bt = ""

story = "I started Thai Solitude in Chicago in 2024, and I have worked in a busy Chicago massage practice where my clients included hotel guests and business travelers. Now I am bringing Thai Solitude to Portland. I am a native Thai speaker and fluent in English."
about, _ = get("/about/")
check("Home 'Learn About' paragraph carries the Chicago-to-Portland story and her language, once", text_of(page).count(story) == 1 and 'class="uagb-ifb-desc">I’m Butsaba Dusrai, an Oregon Licensed Massage Therapist' in page)
check("the story matches About's facts (Chicago 2024; busy Chicago practice with hotel guests and business travelers; bringing it to Portland)", all(k in text_of(about) for k in ("started Thai Solitude in Chicago in 2024", "a busy Chicago massage practice, where my clients included hotel guests and business travelers", "Now I am bringing Thai Solitude to Portland")))
# the announcement strip under the header (WPCode snippet "CorporateBand") is Ryan's 2026-10-25 availability plan: it carries "November 1, 2026" on Home on purpose, so it is left out of this check; every other word on Home is still checked
page_without_strip = re.sub(r'<div\b[^>]*class="[^"]*\bts-corp-band\b[^"]*"[^>]*>.*?</div>\s*</div>', "", page, flags=re.S)
check("no move date, countdown or opening date added on Home (the approved corporate strip is not counted)", not re.search(r"November|relocat|arriv|coming soon", text_of(page_without_strip), re.I))

home_headings = (("e6d24376", "Experience"), ("0a8b3bc1", "Clear Communication"), ("eee8f105", "Thai and Therapeutic Training"))
runtime = bool(re.search(r'<script\b[^>]*\bid=["\']ts-accessibility-runtime["\']', page))
if runtime or any(has_block_heading(page, bid, label) for bid, label in home_headings):
    check("Bundle C Home: accessibility runtime present", runtime)
    for bid, label in home_headings:
        check(f"Bundle C Home: {label!r} is an H3 in its original block", has_block_heading(page, bid, label))
    strips = re.findall(r'<div\b[^>]*class="[^"]*\bts-corp-band\b[^"]*"[^>]*>', page)
    check("Bundle C Home: one named booking-notice landmark", len(strips) == 1 and 'role="region"' in strips[0] and 'aria-label="Booking notice"' in strips[0])

title = html.unescape(re.search(r"<title>(.*?)</title>", page, re.S).group(1))
OLD_TITLE = "Thai Solitude | Thai & Therapeutic Massage, Mt. Tabor, Portland"
NEW_TITLE = "Thai Solitude | Thai & Therapeutic Massage in Portland, OR"   # portland-meta (Ryan, 2026-10-29); a site not changed yet still shows OLD_TITLE
check("page title (the approved one: before or after portland-meta)", title in (OLD_TITLE, NEW_TITLE), title)
if title == OLD_TITLE:
    print("INFO Home title and description still say Mt. Tabor (portland-meta/install-portland-meta.php has not been applied here)")
og = re.search(r'<meta[^>]+property="og:title"[^>]*content="([^"]*)"', page)
check("og:title follows the title", bool(og) and html.unescape(og.group(1)) == title)
OLD_DESC = "Personalized Thai, therapeutic and deep tissue massage in Mt. Tabor, Portland. Ease tension and unwind. Ask about a session with Butsaba Dusrai, LMT."
NEW_DESC = "Personalized Thai, therapeutic and deep tissue massage in Portland. Ease tension and unwind. Ask about corporate and group massage with Butsaba Dusrai, LMT."
new_desc = OLD_DESC if title == OLD_TITLE else NEW_DESC   # the description must match the same state as the title
def meta_of(attr, name):
    mm = re.search(r'<meta[^>]+%s="%s"[^>]*content="([^"]*)"' % (attr, re.escape(name)), page)
    return html.unescape(mm.group(1)) if mm else None
check("meta description is the new wording", meta_of("name", "description") == new_desc, meta_of("name", "description"))
check("og:description and twitter:description follow it", meta_of("property", "og:description") == new_desc and meta_of("name", "twitter:description") == new_desc)
check("meta description fits one search snippet (<= 160 characters)", len(new_desc) <= 160, len(new_desc))
check("the 'About Thai Solitude' link has a navy colour rule (it was white on cream, 1.1:1)", "body.home .uagb-block-36c16d4d .uagb-infobox-cta-link:focus-visible{color:var(--ast-global-color-0)}" in page)

nav = re.findall(r'<a[^>]+href="[^"]*/work-with-me/"[^>]*class="[^"]*menu-link[^"]*"[^>]*>(.*?)</a>|<a[^>]+class="[^"]*menu-link[^"]*"[^>]*href="[^"]*/work-with-me/"[^>]*>(.*?)</a>', page, re.S)
nav = [a or b for a, b in nav]
labels = [text_of(x) for x in nav]
check("every menu link to the hiring page now says 'For Practices' (header, mobile menu, footer menu)", len(labels) >= 3 and set(labels) == {"For Practices"}, labels)
check("menu link target unchanged (/work-with-me/)", all(True for _ in nav) and len(nav) >= 3)
check("'Work With Me' no longer appears as a menu label", "Work With Me" not in labels)

foot = re.search(r'<footer.*?</footer>', page, re.S)
ft = foot.group(0) if foot else ""
check("footer: Portland, OR", "Portland, OR" in text_of(ft))
check("footer: visible email address, linked", 'href="mailto:butsabad@gmail.com"' in ft and "butsabad@gmail.com" in text_of(ft))
check("footer: 'Résumé & experience' links to /work-with-me/", bool(re.search(r'<a href="/work-with-me/">Résumé &amp; experience</a>', ft)))
check("footer: licence line and medical-care disclaimer still present", "LMT-29905" in text_of(ft) and re.search(r"medical", text_of(ft), re.I) is not None, text_of(ft)[:140])

new_copy = " ".join([bt, story, text_of(t), "Ask about a session", "For Practices", title, new_desc, "butsabad@gmail.com Résumé & experience"])
bad = [p for p in (r"\bheal", r"\bproof\b", r"\bmeasurable\b", r"\beffective", r"pain relief", r"relieve pain", r"pain management", r"\bsciatica\b", r"\bheadaches?\b", r"\bchronic\b",
                   r"\badhesions?\b", r"\bcorrective\b", r"\baccelerat", r"restore balance", r"nervous system", r"\bcirculation\b", r"\banxiety\b", r"\bcancer\b",
                   r"\bneurological\b", r"\bdisabilit", r"\bprevention\b", r"\blasting\b", r"long-term wellness", r"overall health", r"real results", r"\bstudio\b",
                   r"\b(we|our|us)\b") if re.search(p, new_copy, re.I)]
check("no restricted OBMT wording in any new copy", not bad, bad)

robots, _ = get("/robots.txt")
print("INFO robots.txt first rules: " + " | ".join(robots.splitlines()[:3]))
print("\nRESULT: " + ("all checks passed" if not fails else f"{fails} check(s) FAILED"))
sys.exit(1 if fails else 0)

# ref: ./install-home-local.php — the installer whose result this checks; ../verify-rendered.py — the Work With Me counterpart

#!/usr/bin/env python3
"""Build winding-willow-page.html from the frozen staging39 capture plus Ryan's approved wording changes (standard library only).

  python3 -I -B build-winding-willow.py           write winding-willow-page.html and update content_sha256/content_length in winding-willow.json
  python3 -I -B build-winding-willow.py --check   change nothing; exit 1 unless both files are exactly what this builder makes (a check-all.sh gate)

winding-willow-page-staging39.html = staging39 page #1385 byte for byte (capture ww-capture-s39-1, 2026-11-18; sha256 below). Never edit it.
CHANGES = Ryan, 2026-11-18, "#1 approved" (after "keep the message pleasant"): four passages that made a combination sound better than the studies
show (the evidence audit's "omit combined benefit"; Siriyong 2026, PMID 42142427: acupuncture added nothing to Thai massage). Each old text must
occur exactly once; nothing else in the page changes.
"""
import hashlib, json, os, sys

HERE = os.path.dirname(os.path.abspath(__file__))
SRC, OUT, SPEC = (os.path.join(HERE, n) for n in ("winding-willow-page-staging39.html", "winding-willow-page.html", "winding-willow.json"))
SRC_SHA256 = "e21a940bb248e37a80ecd5959fef179496f810af97f06bd81ea691fc7bb21b60"
CHANGES = [
    ("then gives the results that were weaker, mixed or negative, and ends on what is encouraging: I included the studies",
     "then gives the results that were weaker, mixed or negative. I included the studies"),
    ("The encouraging part is that people improved with each approach, so you can choose by what feels right to you.",
     "Neither approach came out clearly ahead, so you can choose by what feels right to you."),
    ("Even so, both groups did well in the two Thai massage trials. In the larger back-pain trial,",
     "In the larger back-pain trial,"),
    ("What I can promise is a choice of skilled practitioners in one welcoming place.",
     "So adding another service is a matter of choice, not a way to get more from a massage. Winding Willow offers several practitioners in one welcoming place, each booked separately."),
]


def build():
    raw = open(SRC, "rb").read()
    if hashlib.sha256(raw).hexdigest() != SRC_SHA256:
        sys.exit("winding-willow-page-staging39.html is not the frozen capture (sha256 differs)")
    text = raw.decode("utf-8")
    for old, new in CHANGES:
        if text.count(old) != 1:
            sys.exit("expected exactly one occurrence of: " + old[:70])
        text = text.replace(old, new)
    out = text.encode("utf-8")
    spec = json.load(open(SPEC, encoding="utf-8"))
    spec.update(content_file="winding-willow-page.html", content_sha256=hashlib.sha256(out).hexdigest(), content_length=len(out),
                source=spec["source"].split(" | ")[0] + " | plus Ryan's 4 approved wording changes of 2026-11-18 (build-winding-willow.py)")
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

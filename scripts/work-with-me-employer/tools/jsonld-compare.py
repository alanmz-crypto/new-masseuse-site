#!/usr/bin/env python3
"""Compare the structured data (JSON-LD) two sites publish on the same pages, with each site's own address normalised.
Usage: python3 jsonld-compare.py <candidate-base-url> <reference-base-url>     e.g. https://staging31.thaisolitude.com https://thaisolitude.com
Reads four public pages from each (/, /contact/, /services/, /thai-massage/); changes nothing. Exits 0 only if both publish the same
non-empty set of items and every block parses. An empty result on either side is reported as INVALID, never as 'equal'.
ONE field is set aside: "dateModified" is WordPress's own last-edited timestamp, so it differs on any page that was edited on one site first (2026-10-19: the Thai page
after its evidence section went on staging33). It is not schema content. It is NOT ignored silently: the pages where it differs are listed on an INFO line, and every
other field must still be equal."""
import json
import re
import subprocess
import sys
from urllib.parse import urlparse

if len(sys.argv) < 3:
    sys.exit(__doc__)
PAGES = ("/", "/contact/", "/services/", "/thai-massage/")


def collect(base):
    host = urlparse(base).netloc
    out, unparseable = {}, 0
    for p in PAGES:
        html = subprocess.run(["curl", "-s", "-m", "30", "-L", base.rstrip("/") + p], capture_output=True, text=True).stdout
        items = []
        for block in re.findall(r"<script[^>]*application/ld\+json[^>]*>(.*?)</script>", html, re.S):
            try:
                d = json.loads(block)
            except ValueError:
                unparseable += 1
                continue
            items += d.get("@graph", [d]) if isinstance(d, dict) else d
        out[p] = json.loads(json.dumps(items, sort_keys=True).replace(host, "HOST"))
    return out, unparseable


def strip_modified(items):
    """(items without dateModified, the dateModified values found, in order)."""
    found = []

    def walk(o):
        if isinstance(o, dict):
            if "dateModified" in o:
                found.append(o["dateModified"])
            return {k: walk(v) for k, v in o.items() if k != "dateModified"}
        if isinstance(o, list):
            return [walk(v) for v in o]
        return o
    return walk(items), found


cand, bad_c = collect(sys.argv[1])
ref, bad_r = collect(sys.argv[2])
n_c = sum(len(v) for v in cand.values())
n_r = sum(len(v) for v in ref.values())
if not n_c or not n_r:
    print("TEST INVALID: found %d structured-data items on the candidate and %d on the reference." % (n_c, n_r))
    sys.exit(2)
print("items: candidate %d, reference %d | blocks that failed to parse: candidate %d, reference %d" % (n_c, n_r, bad_c, bad_r))
mod_diff = []
for p in PAGES:
    cand[p], mc = strip_modified(cand[p])
    ref[p], mr = strip_modified(ref[p])
    if mc != mr:
        mod_diff.append(p)
if mod_diff:
    print("INFO dateModified (WordPress's last-edited time, set aside) differs on: %s" % ", ".join(mod_diff))
if cand == ref and not bad_c and not bad_r:
    print("IDENTICAL: both sites publish the same structured data on all %d pages." % len(PAGES))
    sys.exit(0)
print("DIFFERENT:")
for p in PAGES:
    if cand[p] != ref[p]:
        print("  %s: candidate has %d items, reference %d" % (p, len(cand[p]), len(ref[p])))
sys.exit(1)

# ref: ../post-change-test.sh — runs this

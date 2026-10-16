#!/usr/bin/env python3
"""Read-only drift test for the generated staging installers.

Each staging installer is a copy of its localhost original; only the host allow-list, the mode names and the
success/refusal messages may differ. This fails if any other code line differs, or if an allow-list names a host it
should not: localhost originals may list only localhost:PORT, staging copies only stagingNN.thaisolitude.com or
localhost:PORT. A production host in a staging allow-list is the failure this exists to catch.

Usage: python3 check-installer-drift.py [folder]     folder defaults to the one containing this script. Changes nothing."""
import difflib
import os
import re
import sys

ROOT = os.path.abspath(sys.argv[1]) if len(sys.argv) > 1 else os.path.dirname(os.path.abspath(__file__))
PAIRS = [
    ("home/install-home-local.php", "home/install-home-staging.php"),
    ("client-pages/install-client-pages-local.php", "client-pages/install-client-pages-staging.php"),
]
ALLOWED_DIFF = [re.compile(p) for p in (
    r"^\s*if \( ! in_array\( \$host, array\(",
    r"^\s*WP_CLI::error\( 'Refusing to run: '",
    r"^\s*if \( ! in_array\( \$mode, array\( 'preflight', ",
    r"^\s*if \( 'apply(-local)?' === \$mode \) \{",
    r"^\s*WP_CLI::success\( '(Applied|Reverted|Rolled) ",
)]
LOCAL_HOST = re.compile(r"^localhost:\d+$")
STAGING_HOST = re.compile(r"^(staging\d+\.thaisolitude\.com|localhost:\d+)$")
fails = 0


def check(name, ok, detail=""):
    global fails
    print(("PASS " if ok else "FAIL ") + name + (("  [" + str(detail) + "]") if detail else ""))
    if not ok:
        fails += 1


def code_lines(path):
    out = []
    for line in open(path, encoding="utf-8").read().splitlines():
        s = line.strip()
        if s and not s.startswith(("*", "/*", "//", "#", "<?php")):
            out.append(line.rstrip())
    return out


def hosts(lines):
    for line in lines:
        if re.match(r"^\s*if \( ! in_array\( \$host, array\(", line):
            return re.findall(r"'([^']*)'", line.split("array(", 1)[1].split(")")[0])
    return None


for local, staging in PAIRS:
    lp, sp = os.path.join(ROOT, local), os.path.join(ROOT, staging)
    if not (os.path.isfile(lp) and os.path.isfile(sp)):
        check(f"{os.path.basename(staging)}: both files exist", False, f"{lp} / {sp}")
        continue
    a, b = code_lines(lp), code_lines(sp)
    changed = [d for d in difflib.unified_diff(a, b, lineterm="", n=0) if d[:1] in "+-" and d[:3] not in ("+++", "---")]
    stray = [d for d in changed if not any(p.match(d[1:]) for p in ALLOWED_DIFF)]
    check(f"{os.path.basename(staging)}: differs from its localhost original only in allow-list, mode names and messages", not stray, stray[:3] or f"{len(changed)} expected line changes")
    lh, sh = hosts(a), hosts(b)
    check(f"{os.path.basename(local)}: allow-list is localhost only", bool(lh) and all(LOCAL_HOST.match(h) for h in lh), lh)
    check(f"{os.path.basename(staging)}: allow-list is staging sites and localhost only, no production host", bool(sh) and all(STAGING_HOST.match(h) for h in sh), sh)

print("\nRESULT: " + ("all checks passed" if not fails else f"{fails} check(s) FAILED"))
sys.exit(1 if fails else 0)

# ref: ./home/install-home-staging.php, ./client-pages/install-client-pages-staging.php — the generated installers this guards
# ref: ../check-all.sh — runs this as a gate

#!/usr/bin/env python3
"""Compare two directories of page screenshots (from capture-pages.mjs) pixel for pixel.
Usage: python3 compare-shots.py <reference-dir> <candidate-dir> [diff-output-dir]
Exits 0 only if every page-view exists in both sets and is byte-for-byte identical (zero tolerance).
It refuses to say anything if there is nothing to compare, so an empty or mismatched set can never read as 'identical'.
Where pages differ it reports the height of each and the box that changed, and (optionally) writes red-highlighted diff images."""
import os
import sys

import numpy as np
from PIL import Image

if len(sys.argv) < 3:
    sys.exit(__doc__)
ref, cand = sys.argv[1], sys.argv[2]
outdir = sys.argv[3] if len(sys.argv) > 3 else None
names_ref = {n for n in os.listdir(ref) if n.endswith(".png")}
names_cand = {n for n in os.listdir(cand) if n.endswith(".png")}
common = sorted(names_ref & names_cand)
if not common:
    print("TEST INVALID: no page-views in common between the two sets (reference %d, candidate %d)." % (len(names_ref), len(names_cand)))
    sys.exit(2)
same, bad = 0, []
for n in common:
    a = np.asarray(Image.open(os.path.join(ref, n)).convert("RGB"))
    b = np.asarray(Image.open(os.path.join(cand, n)).convert("RGB"))
    if a.shape == b.shape and np.array_equal(a, b):
        same += 1
        continue
    if a.shape != b.shape:
        bad.append("%s: page height/width differs (reference %s, candidate %s)" % (n, a.shape[:2][::-1], b.shape[:2][::-1]))
        continue
    m = np.abs(a.astype(int) - b.astype(int)).max(axis=2) > 0
    ys, xs = np.where(m)
    bad.append("%s: %.4f%% of pixels differ, in box x %d-%d, y %d-%d" % (n, m.mean() * 100, xs.min(), xs.max(), ys.min(), ys.max()))
    if outdir:
        os.makedirs(outdir, exist_ok=True)
        o = b.copy()
        o[m] = (255, 0, 0)
        Image.fromarray(o).save(os.path.join(outdir, n))
missing = sorted(names_ref ^ names_cand)
print("page-views compared: %d | pixel-identical: %d | different: %d | present in only one set: %d" % (len(common), same, len(bad), len(missing)))
for line in bad:
    print("  DIFF", line)
for n in missing:
    print("  MISSING from", "candidate" if n in names_ref else "reference", n)
sys.exit(0 if not bad and not missing else 1)

# ref: ./capture-pages.mjs — makes the screenshot sets; ../post-change-test.sh — runs this

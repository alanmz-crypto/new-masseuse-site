#!/usr/bin/env bash
# Run Google's Lighthouse (the engine behind PageSpeed Insights) on ONE url with the mobile defaults PageSpeed uses (Moto G Power emulation, simulated Slow-4G, 4x CPU) and
# print one line: score, FCP, LCP, SI, TBT, CLS, image/render-blocking estimates and the LCP breakdown. Read-only: one page load per run.
#   tools/lighthouse-mobile.sh <url> <label> [extra lighthouse flags]     e.g.  --blocked-url-patterns='*fonts.googleapis.com*'  (a what-if: blocks a request in the TEST browser only)
# One-time install (isolated folder, no install scripts): mkdir -p ~/.cache/ts-lighthouse && cd ~/.cache/ts-lighthouse && npm init -y && npm install lighthouse@13.5.0 --ignore-scripts --save-exact
# Why 13.5.0: it is the version PageSpeed Insights ran on 2026-10-17; my local runs matched Google's report within a point (77 vs 76). Uses Playwright's Chromium.
# Scores wobble: run 3-5 times and compare medians; LCP can flip between about 2.0 s and 3.9 s when a late web font repaints the heading.
set -uo pipefail
base="${TS_LIGHTHOUSE_DIR:-$HOME/.cache/ts-lighthouse}"
[ -x "$base/node_modules/.bin/lighthouse" ] || { echo "Lighthouse is not installed in $base (see the install line at the top of this file)." >&2; exit 2; }
CH=$(ls -d "$HOME"/.cache/ms-playwright/chromium-*/chrome-linux64/chrome 2>/dev/null | tail -1); [ -n "$CH" ] || { echo "No Playwright Chromium found." >&2; exit 2; }
url="${1:?usage: lighthouse-mobile.sh <url> <label> [flags]}"; label="${2:?label}"; shift 2
out="$base/lh-$label.json"
CHROME_PATH="$CH" "$base/node_modules/.bin/lighthouse" "$url" "$@" --only-categories=performance --output=json --output-path="$out" --quiet --chrome-flags="--headless=new --no-sandbox --disable-gpu" >/dev/null 2>&1 || { echo "$label: lighthouse failed"; exit 1; }
python3 -I -B - "$out" "$label" <<'PY'
import json, sys
d = json.load(open(sys.argv[1])); a = d["audits"]; f = lambda k: a[k]["displayValue"] if k in a and "displayValue" in a[k] else "?"
items = (a.get("lcp-breakdown-insight", {}).get("details", {}).get("items") or [{}])[0].get("items", [])
bd = {i["subpart"]: round(i.get("duration", 0)) for i in items}
print(f"{sys.argv[2]:<24} score {round(d['categories']['performance']['score']*100):>3} | FCP {f('first-contentful-paint'):>6} | LCP {f('largest-contentful-paint'):>6} | SI {f('speed-index'):>6} | TBT {f('total-blocking-time'):>6} | CLS {f('cumulative-layout-shift')} | image-delivery {f('image-delivery-insight')} | render-blocking {f('render-blocking-insight')} | LCP parts {bd}")
PY

# ref: ./perf-lcp-runs.mjs, ./perf-images.mjs — the browser-side measurements; ../performance/install-perf-snippet.php — the snippets these numbers justify

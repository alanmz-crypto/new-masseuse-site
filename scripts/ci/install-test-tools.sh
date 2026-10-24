#!/usr/bin/env bash
# Install what the browser and pixel tests need on a machine that is not Ryan's computer (a GitHub runner or a cloud session VM).
# On Ryan's computer they come from the Mirror project (Playwright) and ~/.cache/ts-axe (axe-core); this puts the same versions in ~/.cache.
#
#   scripts/ci/install-test-tools.sh [--browsers]     --browsers also installs Chromium and Firefox with their system libraries (slow, ~1 min)
#
# Prints TS_PLAYWRIGHT_ROOT=... (the .mjs tools read it); in GitHub Actions it is also written to $GITHUB_ENV. Idempotent.
set -euo pipefail

PLAYWRIGHT_VERSION=1.63.0   # the version in the Mirror project on Ryan's computer, so screenshots are taken by the same browser builds
AXE_VERSION=4.13.0          # the version tools/a11y-audit.mjs reports
sudo_() { if [ "$(id -u)" -eq 0 ]; then "$@"; else sudo "$@"; fi; }

pw="$HOME/.cache/ts-playwright"
if [ ! -f "$pw/node_modules/playwright/package.json" ] || ! grep -q "\"version\": \"$PLAYWRIGHT_VERSION\"" "$pw/node_modules/playwright/package.json"; then
  mkdir -p "$pw" && (cd "$pw" && { [ -f package.json ] || npm init -y >/dev/null; } && npm install --no-audit --no-fund --ignore-scripts --save-exact "playwright@$PLAYWRIGHT_VERSION" >/dev/null)
fi
axe="$HOME/.cache/ts-axe"
if [ ! -f "$axe/node_modules/axe-core/axe.min.js" ]; then
  mkdir -p "$axe" && (cd "$axe" && { [ -f package.json ] || npm init -y >/dev/null; } && npm install --no-audit --no-fund --ignore-scripts --save-exact "axe-core@$AXE_VERSION" >/dev/null)
fi
# The pixel comparison (tools/compare-shots.py) runs under python3 -I, which ignores user site-packages: use the system packages.
python3 -I -c 'import PIL, numpy' 2>/dev/null || { sudo_ apt-get update -qq >/dev/null && sudo_ apt-get install -y -qq python3-pil python3-numpy >/dev/null; }
if [ "${1:-}" = --browsers ]; then
  (cd "$pw" && sudo_ env PATH="$PATH" npx --yes playwright install-deps chromium firefox >/dev/null && npx --yes playwright install chromium firefox >/dev/null)
fi

echo "TS_PLAYWRIGHT_ROOT=$pw/package.json"
[ -z "${GITHUB_ENV:-}" ] || echo "TS_PLAYWRIGHT_ROOT=$pw/package.json" >> "$GITHUB_ENV"
[ -z "${CLAUDE_ENV_FILE:-}" ] || echo "export TS_PLAYWRIGHT_ROOT=$pw/package.json" >> "$CLAUDE_ENV_FILE"

# ref: ../work-with-me-employer/tools/capture-pages.mjs (and the other .mjs tools) — read TS_PLAYWRIGHT_ROOT, else the Mirror project on Ryan's computer

#!/usr/bin/env bash
# One command for every code gate. Read-only: it touches no server and writes nothing into the tree.
#
#   scripts/check-all.sh                                   syntax and lint gates only (offline)
#   scripts/check-all.sh https://staging31.thaisolitude.com   the same, plus the rendered-page verifiers against that URL (read-only GETs)
#
# Gates:   PHP lint on the same pinned PHP image the practice stack uses (production is PHP 8.5.11)
#          bash -n on every shell script; shellcheck (warnings fatal in work-with-me-employer/, errors fatal elsewhere)
#          Python syntax on every file (no .pyc written); ruff for real errors only (syntax, undefined names)
#          installer drift: each generated staging installer differs from its localhost original only in allow-list, mode names
#          and messages, and no allow-list names a production host
#          structured-data installer: its snippet output and exact-bytes guard, tested offline against stubbed WordPress functions
#          headers installer: the old and new security-headers snippet run in a header-capturing harness (logged in/out, https/http)
#          posture check self-test: a mock site, then the same site broken one way at a time; every break must be caught
#          wording tiers self-test (the banned-word list and its research-section exemption) and the Thai, Therapeutic and Deep Tissue evidence builders' --check
#          with a URL: verify-home.py and verify-client-pages.py, and (for thaisolitude.com hosts) posture-check.py
# Not a gate: verify-rendered.py (the older whole-site check; run it by hand: python3 work-with-me-employer/verify-rendered.py URL).
# shellcheck disable=SC2329  # the gate functions below are called indirectly through `gate`
set -u

here=$(cd "$(dirname "$0")" && pwd)
root=$(dirname "$here")
fails=0
gate() { # gate NAME COMMAND...
  local name=$1; shift
  local out
  if out=$("$@" 2>&1); then echo "PASS  $name"; else echo "FAIL  $name"; printf '%s\n' "$out" | sed 's/^/        /' | head -25; fails=$((fails + 1)); fi
}
skip() { echo "SKIP  $1 ($2)"; }

# ---- PHP -----------------------------------------------------------------------------------------------------------------------------
img=$(sed -n 's/^[[:space:]]*image:[[:space:]]*\(wordpress:cli[^[:space:]]*\).*/\1/p' "$root/docker-compose.yml" | head -1)
php_lint() {
  docker run --rm --network none --entrypoint sh -v "$here:/s:ro" "$img" -c \
    'n=0; bad=0; for f in $(find /s -name "*.php" | sort); do n=$((n+1)); php -l "$f" >/dev/null 2>/tmp/e || { cat /tmp/e; bad=$((bad+1)); }; done; echo "$n files, $bad failing"; [ "$bad" -eq 0 ]'
}
if [ -n "$img" ] && command -v docker >/dev/null; then gate "php -l (pinned image)" php_lint; else skip "php -l" "docker or the pinned wordpress:cli image is not available"; fi

# ---- shell ---------------------------------------------------------------------------------------------------------------------------
mapfile -t sh_all < <(find "$here" -name '*.sh' -not -path '*/node_modules/*' | sort)
mapfile -t sh_new < <(find "$here/work-with-me-employer" -maxdepth 1 -name '*.sh' | sort)
bash_n() { local f; for f in "${sh_all[@]}"; do bash -n "$f" || return 1; done; echo "${#sh_all[@]} files"; }
gate "bash -n" bash_n
if command -v shellcheck >/dev/null; then
  gate "shellcheck, work-with-me-employer (warnings fatal)" shellcheck -S warning "${sh_new[@]}"
  gate "shellcheck, all scripts (errors fatal)" shellcheck -S error "${sh_all[@]}"
else skip "shellcheck" "not installed"; fi

# ---- Python --------------------------------------------------------------------------------------------------------------------------
mapfile -t py_all < <(find "$here" -name '*.py' -not -path '*/node_modules/*' | sort)
py_syntax() {
  python3 -I -B - "${py_all[@]}" <<'PY'
import ast, sys
bad = 0
for f in sys.argv[1:]:
    try:
        ast.parse(open(f, encoding="utf-8").read(), f)
    except SyntaxError as e:
        print("syntax error:", f, e); bad += 1
print(len(sys.argv) - 1, "files")
sys.exit(1 if bad else 0)
PY
}
gate "python syntax" py_syntax
if command -v ruff >/dev/null; then gate "ruff (syntax and undefined names)" ruff check --select E9,F63,F7,F82 --no-cache "$here"; else skip "ruff" "not installed"; fi

# ---- installer drift and allow-lists ---------------------------------------------------------------------------------------------------
gate "staging installers match their localhost originals; no production host in any allow-list" python3 -I -B "$here/work-with-me-employer/check-installer-drift.py"

# ---- structured-data installer: snippet behaviour and guard, offline --------------------------------------------------------------------
sd_test() { docker run --rm --network none --entrypoint php -v "$here/work-with-me-employer/structured-data:/pkg:ro" "$img" /pkg/tests/snippet-test.php; }
if [ -n "$img" ] && command -v docker >/dev/null; then gate "structured-data snippet and guard (offline test)" sd_test; else skip "structured-data offline test" "docker or the pinned image is not available"; fi

# ---- headers installer: security-headers snippet and CSP, offline -----------------------------------------------------------------------
hd_test() { docker run --rm --network none --entrypoint php -v "$here/work-with-me-employer/headers:/pkg:ro" "$img" /pkg/tests/csp-snippet-test.php; }
if [ -n "$img" ] && command -v docker >/dev/null; then gate "headers snippet and CSP (offline test)" hd_test; else skip "headers offline test" "docker or the pinned image is not available"; fi

# ---- posture check: self-test, offline --------------------------------------------------------------------------------------------------
gate "posture check self-test (mock site and deliberate breaks)" python3 -I -B "$here/work-with-me-employer/posture/tests/selftest.py"

# ---- wording tiers (banned-word list with the research exemption, 2026-10-19): self-test, offline -------------------------------------------
gate "wording tiers self-test (deliberate breaks, mutation-checked)" python3 -I -B "$here/work-with-me-employer/client-pages/tests/wording-tiers-selftest.py"
# the generated Thai content must be what the builder produces (a hand edit or a stale build is caught here)
gate "Thai evidence content matches its builder" python3 -I -B "$here/work-with-me-employer/thai/build-thai-evidence.py" --check
gate "Therapeutic evidence content matches its builder" python3 -I -B "$here/work-with-me-employer/therapeutic/build-therapeutic-evidence.py" --check
gate "Deep Tissue evidence content matches its builder" python3 -I -B "$here/work-with-me-employer/deep-tissue/build-deep-tissue-evidence.py" --check
gate "Accessibility page text matches its builder" python3 -I -B "$here/work-with-me-employer/accessibility/build-accessibility-page.py" --check
gate "Home corporate card content and CSS match their builder" python3 -I -B "$here/work-with-me-employer/home-card/build-home-corporate-card.py" --check
gate "Sandwich update content matches its builders" python3 -I -B "$here/work-with-me-employer/evidence-update/build-sandwich-update.py" --check
gate "Corporate massage content matches its builder" python3 -I -B "$here/work-with-me-employer/corporate/build-corporate-massage.py" --check
gate "Availability update content matches its builder" python3 -I -B "$here/work-with-me-employer/availability/build-availability-update.py" --check
gate "Booking date content matches its builder" python3 -I -B "$here/work-with-me-employer/booking-date/build-booking-date.py" --check
gate "Chair Massage content matches its builder (and passes its wording gate)" python3 -I -B "$here/work-with-me-employer/chair-massage/build-chair-massage.py" --check
gate "Review Bundle A exact fragments and new wording match their builder" python3 -I -B "$here/work-with-me-employer/review-a/build-bundle-a.py" --check
gate "Review Bundle B source values and approved wording match their builder" python3 -I -B "$here/work-with-me-employer/review-b/build-bundle-b.py" --check
gate "Review Bundle C frozen fragments and runtime sources match their builder" python3 -I -B "$here/work-with-me-employer/review-c/build-bundle-c.py" --check

# ---- rendered pages (read-only) ------------------------------------------------------------------------------------------------------
if [ -n "${1:-}" ] && ! edge_msg=$("$here/work-with-me-employer/posture/edge-check.sh" "$1" 2>&1); then
  # the web edge did not answer with the site (SiteGround bot challenge, or no answer): every verifier would fail for that one reason, so say it once
  echo "FAIL  the site is not reachable for testing: $edge_msg"; fails=$((fails + 1))
  skip "rendered-page verifiers and posture check" "not run: see the line above (INVALID, not a failure of the site)"
elif [ -n "${1:-}" ]; then
  gate "verify-home.py $1" python3 -I -B "$here/work-with-me-employer/home/verify-home.py" "$1"
  gate "verify-client-pages.py $1" python3 -I -B "$here/work-with-me-employer/client-pages/verify-client-pages.py" "$1"
  case "$1" in
    *//staging27.thaisolitude.com*|*//staging30.thaisolitude.com*) gate "posture-check.py $1 (no CSP: cloned before the 2026-10-17 deploy)" python3 -I -B "$here/work-with-me-employer/posture/posture-check.py" "$1" --csp skip ;;
    *//thaisolitude.com*|*.thaisolitude.com*) gate "posture-check.py $1" python3 -I -B "$here/work-with-me-employer/posture/posture-check.py" "$1" ;;
    *) skip "posture check" "only meaningful for thaisolitude.com hosts" ;;
  esac
else skip "rendered-page verifiers" "give a URL as the first argument to run them"; fi

echo
if [ "$fails" -eq 0 ]; then echo "ALL GATES PASSED"; else echo "$fails GATE(S) FAILED"; fi
exit $(( fails > 0 ))

# ref: ../docker-compose.yml — source of the pinned PHP image; ./practice-image-parity.sh — the PHP-version parity check
# ref: ./work-with-me-employer/home/verify-home.py, ./work-with-me-employer/client-pages/verify-client-pages.py, ./work-with-me-employer/posture/posture-check.py — the verifiers run with a URL

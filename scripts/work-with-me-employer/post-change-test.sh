#!/usr/bin/env bash
# "Push, then test": the whole after-a-change battery against one site, in one command. Read-only (public GETs plus one read of the error log).
#
#   ./post-change-test.sh <target> [minutes]       target: live staging24 staging25 staging26 staging27 staging30 staging31 staging32 staging33 staging34 staging36 staging37 staging38 staging39
#                                                  minutes = how far back to read the server's PHP error log (default 120)
#
# Run it right after changing a site (a PHP version switch, a plugin or theme update, a deploy). It checks:
#   1. every code gate and the rendered-page verifiers (check-all.sh <url>)
#   2. the 10 pages (the 8 original ones plus /privacy-policy/ and /accessibility/), sitemap and robots.txt answer 200
#   3. the structured data (JSON-LD) matches the reference site's, with each site's own address normalised
#   4. every page (20 page-views), at desktop and mobile width, is PIXEL-IDENTICAL to the reference site's (zero tolerance)
#   5. no new PHP errors, warnings or deprecations in the server's php_errorlog in the last N minutes
# Reference site: live for a staging copy; staging36 (the current working copy, a fresh copy of live pulled 2026-10-19 after the research-panel update; it was staging34 before, and staging33 lacks the phone-header fix and the Thai images) for live. If the two sites are meant to differ, differences are reported, not hidden.
# Needs: Playwright via the Mirror project, Pillow + numpy, and the ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock) for the log read.
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"; mins="${2:-120}"
case "$target" in
  live) host_t="thaisolitude.com"; ref_t="staging36"; host_r="staging36.thaisolitude.com" ;;
  staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38|staging39|staging40) host_t="$target.thaisolitude.com"; ref_t="live"; host_r="thaisolitude.com" ;;
  *) echo "Usage: $0 <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38|staging39> [minutes]" >&2; exit 1 ;;
esac
case "$mins" in ''|*[!0-9]*) echo "minutes must be a number" >&2; exit 1 ;; esac
url_t="https://$host_t"; url_r="https://$host_r"
work="${XDG_CACHE_HOME:-$HOME/.cache}/ts-post-change/$target-$(date +%Y%m%d-%H%M%S)"; mkdir -p "$work"
fails=0; step() { echo; echo "[$1] $2"; }
note() { if [ "$1" -eq 0 ]; then echo "  -> PASS"; else echo "  -> FAIL"; fails=$((fails + 1)); fi; }
echo "Target: $target ($url_t)   reference: $ref_t ($url_r)   $(date -u '+%Y-%m-%d %H:%M UTC')"

# One request each to the target and the reference: stop with ONE clear message if SiteGround's bot challenge (HTTP 202) is answering instead of the site.
for u in "$url_t" "$url_r"; do
  "$here/posture/edge-check.sh" "$u" || { echo "STOPPING: nothing was tested (INVALID). Wait, then run it once more; do not retry in a loop."; exit 2; }
done

step 1 "code gates and rendered-page verifiers"
"$here/../check-all.sh" "$url_t" 2>&1 | tail -20; [ "${PIPESTATUS[0]}" -eq 0 ]; note $?

step 2 "pages answer 200"
bad=0; for p in / /about/ /services/ /thai-massage/ /therapeutic-massage/ /deep-tissue-massage/ /work-with-me/ /contact/ /privacy-policy/ /accessibility/ /wp-sitemap.xml /robots.txt; do
  c=$(curl -s -o /dev/null -m 30 -w '%{http_code}' "$url_t$p"); printf '  %s %s\n' "$c" "$p"; [ "$c" = 200 ] || bad=1; done; note $bad

step 3 "structured data vs the reference"
python3 -I -B "$here/tools/jsonld-compare.py" "$url_t" "$url_r"; note $?

step 4 "pixel comparison vs the reference (20 page-views, zero tolerance)"
node "$here/tools/capture-pages.mjs" "$url_t" "$work/candidate" 2>&1 | tail -2 &
node "$here/tools/capture-pages.mjs" "$url_r" "$work/reference" 2>&1 | tail -2 &
wait
python3 -I -B "$here/tools/compare-shots.py" "$work/reference" "$work/candidate" "$work/diff"; rc=$?; note $rc
[ "$rc" -eq 0 ] || echo "  (screenshots and red-highlighted diffs kept in $work)"

step 5 "PHP errors in the server log, last $mins minutes"
if [ -f "$creds" ] && [ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ]; then
  field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
  host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
  out=$(ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=20 "$user@$host" "WANT=$host_t MINS=$mins python3 - " <<'PY' 2>&1
import os, re, glob, datetime, subprocess
want, mins = os.environ["WANT"], int(os.environ["MINS"])
doc = None
for cfg in glob.glob(os.path.expanduser("~/www/*/public_html/wp-config.php")):
    d = os.path.dirname(cfg)
    h = subprocess.run(["wp", "--path=" + d, "--skip-plugins", "--skip-themes", "option", "get", "home"], capture_output=True, text=True).stdout.strip()
    if re.sub(r"^https?://|/$", "", h) == want: doc = d
if not doc: print("NOHOST"); raise SystemExit
now = datetime.datetime.now(datetime.timezone.utc).replace(tzinfo=None); hits = 0; tool = 0; lines = []
for name in ("php_errorlog", "wp-content/debug.log"):
    p = os.path.join(doc, name)
    if not os.path.isfile(p): continue
    for line in open(p, errors="replace").read().splitlines()[-2000:]:
        m = re.match(r"\[(\d\d-\w{3}-\d{4} \d\d:\d\d:\d\d) UTC\] (.*)", line)
        if not m: continue
        t = datetime.datetime.strptime(m.group(1), "%d-%b-%Y %H:%M:%S")
        if (now - t).total_seconds() <= mins * 60 and re.search(r"PHP (Fatal|Parse|Warning|Notice|Deprecated)", m.group(2)):
            if "/usr/local/wp-cli/" in m.group(2): tool += 1; continue   # SiteGround's own CLI tool (e.g. the Site Tools login helper), not the site
            hits += 1; lines.append(line[:220].replace(doc, ""))
print("COUNT", hits)
if tool: print("NOTE %d diagnostic(s) came from /usr/local/wp-cli (SiteGround's own CLI tool, e.g. the Site Tools login helper), not from the site; not counted" % tool)
for l in lines[-10:]: print(l)
PY
)
  if [ "$(printf '%s' "$out" | head -1)" = "COUNT 0" ]; then echo "  no PHP errors, warnings or deprecations logged from the site's code"; printf '%s\n' "$out" | tail -n +2 | while IFS= read -r l; do printf '  %s\n' "$l"; done; note 0
  elif printf '%s' "$out" | head -1 | grep -q '^COUNT'; then printf '  %s\n' "${out//$'\n'/$'\n  '}"; note 1
  else echo "  could not read the log: $(printf '%s' "$out" | head -1)"; note 1; fi
else
  echo "  skipped: no credentials file or ssh-agent socket (so this step cannot vouch for the server log)"; note 1
fi

echo; if [ "$fails" -eq 0 ]; then echo "ALL 5 CHECKS PASSED for $target"; else echo "$fails CHECK(S) FAILED for $target" >&2; exit 1; fi

# ref: ./tools/capture-pages.mjs, ./tools/compare-shots.py, ./tools/jsonld-compare.py — the pieces this runs; ../check-all.sh — step 1
# ref: ./mirror-versions.sh — keeps a staging copy on live's plugin/theme versions so this comparison is like for like; ../../remote_execution.txt — never printed

#!/usr/bin/env bash
# RUNTIME PHP compatibility test: render every public page through the real WordPress stack (theme, blocks, all active plugins) from the command line
# under PHP 8.2 and under newer versions, compare the HTML, and collect every PHP warning/deprecation the newer version produces.
#
#   ./php-runtime-test.sh <target> [versions]       target: staging31;  versions default "83 84" (PHP 8.3, 8.4); PHP 8.2 is always run as the reference
#
# Why this exists: switching a staging copy's PHP in Site Tools is not possible (the screen only offers the main domain, i.e. LIVE), and
# php-compat-scan.sh only parses files. Here the server's own `php83`/`php84` CLI builds run WordPress itself: the wrapper /usr/local/bin/wp
# reads PHP_BIN, and this script launches /usr/local/wp-cli/wp-cli.php under each version directly so it can send PHP's log to a file of its own
# (a relative error_log would otherwise write into the site's php_errorlog; see php-compat-scan.sh). It changes no setting anywhere.
# Method: 8.2 is rendered twice (the difference between those two is the NOISE FLOOR: nonces, timestamps), then each newer version once.
# A version PASSES when every page is identical to the 8.2 render (after masking only what the noise run proves is volatile) and its
# log has no diagnostics from the site's own code. wp-cli's own notices are reported separately and do not count against the site.
# Not covered (needs a real switch in Site Tools): admin screens, AJAX and cron under the new version, and the web server's own PHP handler.
# Side effects: like any visitor, a render may refresh caches/transients on the staging copy. Nothing is changed on live.
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock).
# shellcheck disable=SC2016  # the commands sent to the server are single-quoted on purpose: they must expand there, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"; vers="${2:-83 84}"
case "$target" in staging31) want="staging31.thaisolitude.com" ;; *) echo "Usage: $0 <staging31> [versions, e.g. \"83 84\"]" >&2; exit 1 ;; esac
case "$vers" in ''|*[!0-9\ ]*) echo "versions must be digits like \"83 84\"" >&2; exit 1 ;; esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

"${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-php-runtime" && cat > "$HOME/wwm-php-runtime/render-page.php"' < "$here/tools/render-page.php"

"${ssh_cmd[@]}" bash -s -- "$want" "$vers" <<'REMOTE'
set -uo pipefail
want=$1; shift; vers="$*"   # ssh joins the arguments with spaces, so "83 84" arrives as two words
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
doc=""
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}; [ "$hh" = "$want" ] && doc=$d; done < "$cfgs"
[ -n "$doc" ] || { echo "No install reports $want"; exit 1; }
W=$HOME/wwm-php-runtime; rm -rf "$W/out"; mkdir -p "$W/out"
before=$(wc -l < "$doc/php_errorlog" 2>/dev/null || echo 0)
pages="/ /about/ /services/ /thai-massage/ /therapeutic-massage/ /deep-tissue-massage/ /work-with-me/ /contact/"
run() { # run PHPVERSION TAG
  local v=$1 tag=$2 p name
  mkdir -p "$W/out/$tag"; : > "$W/out/$tag.log"
  for p in $pages; do
    name=$([ "$p" = / ] && echo home || echo "${p//\//}")
    /usr/local/bin/php$v -d memory_limit=2048M -d display_errors=0 -d log_errors=1 -d error_reporting=-1 -d "error_log=$W/out/$tag.log" \
      /usr/local/wp-cli/wp-cli.php --path="$doc" --url="http://$want" eval-file "$W/render-page.php" "$p" "$W/out/$tag/$name.html" >"$W/out/$tag/$name.txt" 2>&1
  done
}
echo "positive control: a deliberate warning must reach each version's log, or a clean log would mean nothing ..."
for v in 82 $vers; do
  C=$W/out/control$v.log; : > "$C"
  /usr/local/bin/php$v -d memory_limit=2048M -d display_errors=0 -d log_errors=1 -d error_reporting=-1 -d "error_log=$C" /usr/local/wp-cli/wp-cli.php --path="$doc" --url="http://$want" eval 'trigger_error("wwm-control-warning", E_USER_WARNING);' >/dev/null 2>&1
  if grep -q "wwm-control-warning" "$C"; then echo "  PHP $v: log capture works"; else echo "TEST INVALID: PHP $v did not log the deliberate warning, so a clean log proves nothing."; exit 2; fi
done
echo "rendering 8 pages x PHP 8.2 (twice, for the noise floor) and PHP: $vers ..."
run 82 82a; run 82 82b; for v in $vers; do run "$v" "$v"; done
WWM_TAGS="82a 82b $vers" WWM_DIR="$W/out" WWM_DOC="$doc" python3 - <<'PY'
import hashlib, os, re, collections
d, tags = os.environ["WWM_DIR"], os.environ["WWM_TAGS"].split()
names = sorted(f[:-5] for f in os.listdir(os.path.join(d, "82a")) if f.endswith(".html"))
site = os.environ["WWM_DOC"]
MASKS = [(re.compile(r'(nonce["\']?\s*[:=]\s*["\']?)[0-9a-f]{8,12}', re.I), r'\1<n>'), (re.compile(r'(_wpnonce=)[0-9a-f]{8,12}', re.I), r'\1<n>'),
         (re.compile(r'(wp_block_styles_on_demand_placeholder:)[0-9a-f]+'), r'\1<random>')]   # core stamps a random token into every render
def load(tag, n, mask):
    p = os.path.join(d, tag, n + ".html")
    if not os.path.exists(p): return None
    h = open(p, encoding="utf-8", errors="replace").read()
    if mask:
        for rx, rep in MASKS: h = rx.sub(rep, h)
    return h
def sha(h): return hashlib.sha256(h.encode("utf-8", "replace")).hexdigest()[:12] if h is not None else None
missing = [(t, n) for t in tags for n in names if load(t, n, False) is None or "</html>" not in load(t, n, False) or "<title>" not in load(t, n, False)]   # absent, or not a whole page
if missing:
    print("TEST INVALID: %d page renders are missing, e.g. %s" % (len(missing), missing[:3]))
    for t, n in missing[:3]:
        p = os.path.join(d, t, n + ".txt")
        if os.path.exists(p): print("   ", t, n, ":", open(p, errors="replace").read()[:300].replace("\n", " "))
    raise SystemExit(2)
# 1. noise floor: the same PHP rendered twice
noise = [n for n in names if load("82a", n, False) != load("82b", n, False)]
need_mask = [n for n in noise if load("82a", n, True) == load("82b", n, True)]
still = [n for n in noise if n not in need_mask]
print("\n== NOISE FLOOR (PHP 8.2 rendered twice): %d of %d pages byte-identical; %d differ only in volatile tokens (nonces and core's random block-styles placeholder, masked from here on); %d differ otherwise ==" % (len(names) - len(noise), len(names), len(need_mask), len(still)))
for n in still: print("   unexplained noise on", n)
# 2. each newer version against 8.2
results = {}
for t in tags[2:]:
    same, diff = 0, []
    for n in names:
        a, b = load("82a", n, True), load(t, n, True)
        if sha(a) == sha(b): same += 1
        else:
            i = next((k for k in range(min(len(a), len(b))) if a[k] != b[k]), min(len(a), len(b)))
            diff.append((n, len(a), len(b), a[max(0, i - 60):i + 80].replace("\n", " "), b[max(0, i - 60):i + 80].replace("\n", " ")))
    results[t] = (same, diff)
    print("\n== PHP %s vs 8.2: %d of %d pages identical ==" % (t, same, len(names)))
    for n, la, lb, sa, sb in diff[:4]:
        print("   DIFF %s (8.2: %d bytes, %s: %d bytes)\n      8.2: ...%s...\n      %s: ...%s..." % (n, la, t, lb, sa, t, sb))
# 3. diagnostics
print("\n== PHP DIAGNOSTICS written while rendering (each version's own log) ==")
def klass(line):
    m = re.search(r" in (/\S+) on line", line) or re.search(r"(/home/\S+\.php)", line)
    p = m.group(1) if m else ""
    if "/wp-content/plugins/" in p: return "plugin:" + p.split("/wp-content/plugins/")[1].split("/")[0]
    if "/wp-content/themes/" in p: return "theme:" + p.split("/wp-content/themes/")[1].split("/")[0]
    if "/wp-includes/" in p or "/wp-admin/" in p: return "wordpress core"
    if "/usr/local/wp-cli/" in p: return "wp-cli itself"
    return "other/unknown"
summary = {}
for t in tags:
    lines = [l for l in open(os.path.join(d, t + ".log"), errors="replace").read().splitlines() if "PHP " in l]
    by = collections.Counter(); msgs = collections.Counter()
    for l in lines:
        lvl = re.search(r"PHP (Fatal error|Parse error|Warning|Notice|Deprecated)", l)
        k = klass(l); by[(k, lvl.group(1) if lvl else "other")] += 1
        msgs[re.sub(r"\s+", " ", re.sub(r"^\[[^\]]+\] PHP \w+( error)?:\s*", "", re.sub(r" in /\S+ on line \d+", "", l)))[:150] + "  [" + k + "]"] += 1
    site_code = sum(n for (k, lvl), n in by.items() if k.startswith(("plugin:", "theme:", "wordpress core", "other")))
    summary[t] = (len(lines), site_code)
    print("PHP %-4s %3d diagnostics (%d from the site's own code, %d from wp-cli itself)" % (t, len(lines), site_code, len(lines) - site_code))
    for (k, lvl), n in by.most_common(6): print("      %4d  %-12s %s" % (n, lvl, k))
    if t in tags[2:] or len(lines):
        for m, n in msgs.most_common(4): print("        e.g. x%d %s" % (n, m))
print("\n== VERDICT ==")
ok = True
for t in tags[2:]:
    same, diff = results[t]
    nd = summary[t][1] - summary["82a"][1]
    good = (not diff) and nd <= 0
    ok &= good
    print("PHP %-3s %s: %d/%d pages identical to 8.2; %+d diagnostics from the site's code vs 8.2" % (t, "PASS" if good else "NEEDS A LOOK", same, len(names), nd))
raise SystemExit(0 if ok and not still else 1)
PY
rc=$?
after=$(wc -l < "$doc/php_errorlog" 2>/dev/null || echo 0)
echo; echo "the site's own php_errorlog: $before lines before, $after after -> $([ "$before" = "$after" ] && echo "untouched" || echo "CHANGED (investigate)")"
exit $rc
REMOTE

# ref: ./tools/render-page.php — the page renderer uploaded to the server; ./php-compat-scan.sh — the compile-time scan; ./post-change-test.sh — run it after a real switch
# ref: ../../remote_execution.txt — gitignored access details (never printed)

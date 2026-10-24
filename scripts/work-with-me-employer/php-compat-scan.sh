#!/usr/bin/env bash
# Read-only PHP compatibility scan: lint every PHP file of a site (WordPress core, plugins, theme) under newer PHP versions installed on the server.
#
#   ./php-compat-scan.sh <target> [versions]       target: live staging24 staging25 staging26 staging27 staging30 staging31 staging32 staging33 staging34 staging36 staging37;  versions default "83 84" (PHP 8.3, 8.4)
#
# It runs `php -l` only, so it PARSES every file and executes none. It catches removed syntax (parse errors) and compile-time deprecations
# (for example PHP 8.4's "implicitly nullable parameter"), and proves it can see them with a deliberate bad snippet first. It cannot see
# RUNTIME problems (dynamic properties, removed functions called only on some pages): the real test is switching a staging copy's PHP version
# in Site Tools -> PHP Manager and running scripts/check-all.sh <url> plus the visual and structured-data comparisons.
# Counts only genuine PHP diagnostics (lines that start with Deprecated/Warning/Notice), never file names that merely contain "deprecated".
# OPCACHE IS SWITCHED OFF for every lint: with it on, the server's compile cache remembered files it had already compiled, so a repeat scan of the same
# site showed only 5 flagged files where the first scan showed 161 (compile-time notices are not re-emitted for cached files). Scans must give the same answer every time.
# Changes nothing: PHP's own logging is switched off for every lint (log_errors=0, error_log=/dev/null), because the server's default error_log is a
# RELATIVE file name, so a lint run from a site's folder would otherwise append a line to that site's php_errorlog for every file (it once wrote 42,000 lines).
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock).
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"; vers="${2:-83 84}"
case "$target" in
  live) want="thaisolitude.com" ;;
  staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37) want="$target.thaisolitude.com" ;;
  *) echo "Usage: $0 <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37> [versions, e.g. \"83 84\"]" >&2; exit 1 ;;
esac
case "$vers" in *[!0-9\ ]*) echo "versions must be digits like \"83 84\"" >&2; exit 1 ;; esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

# shellcheck disable=SC2016  # the remote script is single-quoted on purpose
ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=30 "$user@$host" "TARGET_HOST=$want VERS='$vers' bash -s" <<'EOS'
#!/usr/bin/env bash
want="${TARGET_HOST:?}"; vers="${VERS:-83 84}"
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
doc=""
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}; [ "$hh" = "$want" ] && doc=$d; done < "$cfgs"
[ -n "$doc" ] || { echo "no install reports $want"; exit 1; }
cd "$doc" || exit 1
lintone='b=$1; shift; out=$("$b" -d display_errors=1 -d html_errors=0 -d log_errors=0 -d error_log=/dev/null -d opcache.enable=0 -d opcache.file_cache_only=0 -d opcache.file_cache= -d error_reporting=-1 -l "$1" 2>&1); if printf "%s" "$out" | grep -q "No syntax errors"; then printf "%s" "$out" | grep -E "^(PHP )?(Deprecated|Warning|Notice): " | head -3 | sed "s#^#DEPR|$1|#"; else printf "%s" "$out" | head -2 | tr "\n" " " | sed "s#^#ERR|$1|#"; echo; fi'
echo "== self-test: can the scanner see what we are hunting for? (a deliberate PHP 8.4 deprecation) =="
t=$(mktemp --suffix=.php); printf '<?php\nfunction f(int $x = null) { return $x; }\n' > "$t"
for v in $vers; do printf 'php%s on a file with an implicit-nullable parameter: ' "$v"; /usr/local/php$v/bin/php -d display_errors=1 -d html_errors=0 -d log_errors=0 -d error_log=/dev/null -d opcache.enable=0 -d opcache.file_cache_only=0 -d opcache.file_cache= -d error_reporting=-1 -l "$t" 2>&1 | grep -E "^(PHP )?Deprecated: |No syntax" | head -2 | tr '\n' ' ' | cut -c1-150; echo; done; rm -f "$t"
echo
for v in $vers; do
  bin=/usr/local/php$v/bin/php
  echo "######## PHP $($bin -d log_errors=0 -d error_log=/dev/null -d opcache.enable=0 -d opcache.file_cache_only=0 -d opcache.file_cache= -v 2>/dev/null | head -1 | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')"
  res=$(mktemp)
  find . -type f -name '*.php' ! -path './wp-content/wpvividbackups/*' ! -path './wp-content/wpvivid_uploads/*' ! -path './wp-content/uploads/*' ! -path './wp-content/upgrade*' -print0 \
    | xargs -0 -P 4 -n 1 sh -c "$lintone" _ "$bin" > "$res" 2>/dev/null
  total=$(find . -type f -name '*.php' ! -path './wp-content/wpvividbackups/*' ! -path './wp-content/wpvivid_uploads/*' ! -path './wp-content/uploads/*' ! -path './wp-content/upgrade*' | wc -l)
  echo "files linted: $total | PARSE ERRORS: $(grep -c '^ERR|' "$res") | files with deprecation/warning output: $(grep '^DEPR|' "$res" | cut -d'|' -f2 | sort -u | wc -l)"
  echo "-- parse errors (first 8):"; grep '^ERR|' "$res" | head -8 | cut -c1-220
  echo "-- deprecations by area (files):"
  grep '^DEPR|' "$res" | cut -d'|' -f2 | sort -u | awk -F/ '{ if ($2=="wp-content") print $2"/"$3"/"$4; else print $2 }' | sort | uniq -c | sort -rn | head -10 | sed 's/^/   /'
  echo "-- most common messages:"
  grep '^DEPR|' "$res" | cut -d'|' -f3- | sed -E 's# in /[^ ]+ on line [0-9]+##; s#^PHP ##' | sort | uniq -c | sort -rn | head -6 | cut -c1-200 | sed 's/^/   /'
  rm -f "$res"; echo
done
EOS

# ref: ./check-no-new-zips.sh, ./mirror-versions.sh — same read-only/guarded style; ../check-all.sh — run it against the staging copy after switching its PHP version

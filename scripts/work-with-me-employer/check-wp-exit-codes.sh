#!/usr/bin/env bash
# Canary for a SiteGround Security bug: on PHP 8.5, WP-CLI exits 0 for EVERY failure on any site that is not a SiteGround staging copy (found 2026-10-17).
# Read-only. Runs three harmless probes (an eval that only exits, a WP_CLI::error, a missing option) twice per site: with the bare `wp` and with the
# workaround our scripts use (`wp --skip-plugins=sg-security`).
#
#   ./check-wp-exit-codes.sh [live|staging31|staging32|staging33|staging27|staging30]      default: live
#
# Verdicts:  BUG ACTIVE, workaround OK   bare wp swallows failures; our scripts are protected (exit 0). Normal today on live.
#            BUG NOT PRESENT             bare wp returns true codes (a staging copy, or SiteGround fixed the plugin): the workaround can be retired.
#            WORKAROUND BROKEN           even with the plugin skipped the codes are wrong: do not trust any `wp` exit status (exit 1).
# Cause: sg-security 1.6.9 Loader.php registers a shutdown handler whose body is `if ( defined( 'WP_CLI' ) ) { exit; }` (Activity_Log_Unknown.php); on
# PHP 8.5 that bare exit overwrites the real exit status. The handler is skipped when sg_security_disable_activity_log=1 or WP_ENVIRONMENT_TYPE=staging.
# Re-run after any SiteGround Security update (the activity log shows "Updated Plugin").
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock). Finds the install by `wp option get home`, never by folder name.
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-live}"
case "$target" in
  live) want="thaisolitude.com" ;;
  staging27|staging30|staging31|staging32|staging33) want="$target.thaisolitude.com" ;;
  *) echo "Usage: $0 [live|staging27|staging30|staging31|staging32|staging33]" >&2; exit 1 ;;
esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

out=$(ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" <<'REMOTE'
set -u
want=$1
cd "$HOME"
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"   # a temp file, not process substitution: the server's bash does not support <( )
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then doc="$d"; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "NOTFOUND"; exit 0; }
t() { "$@" >/dev/null 2>&1; echo -n "$?"; }
echo "BARE $(t wp --path="$doc" eval 'exit(3);') $(t wp --path="$doc" eval 'WP_CLI::error("probe");') $(t wp --path="$doc" option get no_such_option_probe)"
echo "FIXED $(t wp --path="$doc" --skip-plugins=sg-security eval 'exit(3);') $(t wp --path="$doc" --skip-plugins=sg-security eval 'WP_CLI::error("probe");') $(t wp --path="$doc" --skip-plugins=sg-security option get no_such_option_probe)"
echo "ENV $(wp --path="$doc" --skip-plugins --skip-themes eval 'echo defined("WP_ENVIRONMENT_TYPE") ? WP_ENVIRONMENT_TYPE : "(production default)";' 2>/dev/null)"
echo "PHP $(wp --path="$doc" --skip-plugins --skip-themes eval 'echo PHP_VERSION;' 2>/dev/null)"
REMOTE
)
grep -q '^NOTFOUND' <<<"$out" && { echo "No WordPress install under ~/www reports home host $want; nothing checked." >&2; exit 1; }
bare=$(sed -n 's/^BARE //p' <<<"$out"); fixed=$(sed -n 's/^FIXED //p' <<<"$out")
[ -n "$bare" ] && [ -n "$fixed" ] || { echo "No probe results came back; nothing is proven." >&2; echo "$out" >&2; exit 2; }
echo "site: $want | $(sed -n 's/^PHP /PHP /p' <<<"$out") | environment: $(sed -n 's/^ENV //p' <<<"$out")"
echo "correct codes are:                 exit(3) -> 3 | WP_CLI::error -> 1 | missing option -> 1"
echo "bare  wp                  gives:  exit(3) -> ${bare%% *} | error -> $(cut -d' ' -f2 <<<"$bare") | missing option -> $(cut -d' ' -f3 <<<"$bare")"
echo "wp --skip-plugins=sg-security:    exit(3) -> ${fixed%% *} | error -> $(cut -d' ' -f2 <<<"$fixed") | missing option -> $(cut -d' ' -f3 <<<"$fixed")"
if [ "$fixed" != "3 1 1" ]; then echo "WORKAROUND BROKEN: even with sg-security skipped the exit codes are wrong; do not trust any wp exit status."; exit 1
elif [ "$bare" = "3 1 1" ]; then echo "BUG NOT PRESENT on $target: bare wp returns true exit codes here (a staging copy, or SiteGround fixed the plugin). The workaround is harmless; retire it once live shows this too."
else echo "BUG ACTIVE on $target, workaround OK: bare wp swallows failures; our scripts use the workaround and are protected."; fi

# ref: headers/push-headers-csp.sh, structured-data/push-structured-data.sh, secure-backups.sh, mirror-versions.sh, check-no-new-zips.sh — each defines wp() with the workaround at the top of its remote section; ../../AGENTS.md — the rule

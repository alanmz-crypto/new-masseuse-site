#!/usr/bin/env bash
# Read-only: which social-image setting names does the installed SureRank plugin use? (greps the plugin's own source on the server; changes NOTHING)
# Ryan, 2026-10-30: "a cursory scan of all SEO material first, and then I'll request indexing."
# Per page: HTTP status, X-Robots-Tag, <title> and <meta description> (with lengths), canonical, robots meta, Open Graph and Twitter tags, H1 count and text, JSON-LD blocks (parse ok, @type),
# images without alt, leftover "Mt. Tabor" / "November 1," wording; then duplicates across pages and the sitemap/robots files. Prints WARN lines for anything odd; exits 0 unless a page does not answer 200.
#   S_TARGET=live|staging38 (default live). Usage: scan-seo.sh
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want." >&2; exit 1; }; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || { echo "No install for $want." >&2; exit 1; }
p="$doc/wp-content/plugins/surerank"; echo "plugin version: $(sed -n 's/^ \* Version:[[:space:]]*//p' "$p/surerank.php" | head -1)"
echo "-- files that mention facebook_image"; grep -rl "facebook_image" "$p" --include=*.php 2>/dev/null | head -10
echo "-- the lines (php only)"; grep -rhn "facebook_image\|twitter_image\|_thumbnail_id\|featured" "$p/inc" --include=*.php 2>/dev/null | cut -c1-200 | sort -u | head -60
EOS
"${ssh_cmd[@]}" bash -s -- staging38.thaisolitude.com <<<"$REMOTE"

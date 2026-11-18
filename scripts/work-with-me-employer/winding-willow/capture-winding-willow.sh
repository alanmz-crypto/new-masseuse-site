#!/usr/bin/env bash
# READ-ONLY: capture the Winding Willow page (and the CSS, snippets, images and links it depends on) from one SiteGround site, and list every
# install on the account with its home address (to pick a fresh staging copy). Changes nothing on any site.
#   S_TARGET=staging39 ./capture-winding-willow.sh          (default staging39, the only known copy of the page as of 2026-11-17)
# Through GitHub Actions: scripts/ci/request.sh run --target staging39 --script winding-willow/capture-winding-willow.sh --no-tests
# Access: credentials file + ssh-agent, as every push script; the install is found by `wp option get home`, never by folder name.
# shellcheck disable=SC2016  # the single-quoted commands below expand on the server
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
[ $# -eq 0 ] || { echo "Usage: S_TARGET=stagingNN $0   (read-only; takes no arguments)" >&2; exit 1; }
target="${S_TARGET:-staging39}"
case "$target" in staging[0-9]|staging[0-9][0-9]|live) ;; *) echo "S_TARGET must be stagingNN or live." >&2; exit 1 ;; esac
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - capture-winding-willow.php \
  | "${ssh_cmd[@]}" 'rm -rf "$HOME/wwm-package-ww-capture" && mkdir -p "$HOME/wwm-package-ww-capture" && tar -xf - -C "$HOME/wwm-package-ww-capture"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
target=$1
case "$target" in live) want="thaisolitude.com" ;; *) want="$target.thaisolitude.com" ;; esac
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null | sort > "$cfgs"
echo "== installs on the account (folder -> home) =="
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  echo "  ${d#"$HOME"/} -> ${hh:-?}   (wp-config modified $(date -u -d @"$(stat -c %Y "$cfg")" '+%Y-%m-%d %H:%M UTC'))"
  if [ "$hh" = "$want" ]; then
    [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }
    doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want." >&2; exit 1; }
echo "== $target is ${doc#"$HOME"/} (read-only capture) =="
wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-ww-capture/capture-winding-willow.php"
EOS
"${ssh_cmd[@]}" bash -s -- "$target" <<<"$REMOTE"

# ref: ./capture-winding-willow.php — the read-only capture this runs; ../../ci/inspect.sh — same connection and home-address guard

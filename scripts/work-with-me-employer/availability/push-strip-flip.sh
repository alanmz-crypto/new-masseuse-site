#!/usr/bin/env bash
# Announcement strip: on 2026-11-01 00:00 Pacific the strip (WPCode snippet "CorporateBand") switches itself from "Currently booking corporate & group massage only." to
# "Now accepting corporate & group massage bookings." This tool checks, from the server itself, that all nine pages show the wording that matches the current time, and
# (--apply) purges the site cache so that they do. It changes NO content, NO database row and NO file; the only write is SiteGround's cache purge.
# Ryan, 2026-10-25: handle the purge on 2026-11-01 with your own judgement. He starts the live run himself (Actions > "Site ops (LIVE)").
#
#   ./push-strip-flip.sh                 read-only: the date check, the site's cache settings, and the strip wording of each of the nine pages (fetched from the server). Fails if a page shows the wrong wording for now.
#   ./push-strip-flip.sh --check         the same (read-only), for use after the flip or on a schedule
#   ./push-strip-flip.sh --apply         refuses before the flip time (a purge would change nothing); after it: purge the site cache, then run the same check
#   S_TARGET=staging38|live (default staging38). The SSH account also holds other sites, so every step is conditional on `wp option get home` being exactly the chosen site.
# Access: credentials file + ssh-agent. After a purge there is nothing to roll back (the cache simply refills); the check is the proof.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys." >&2; exit 2; }

mode=check
case "${1:-}" in
  ""|--check) ;;
  --apply) mode=apply ;;
  *) echo "Usage: $0 [--check | --apply]" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging38}"
case "$target" in staging38|live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2
case "$target" in staging38) want="staging38.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging38 or live, got '$target'." >&2; exit 1 ;; esac
command -v wp >/dev/null || { echo "wp-cli not found on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); tmp=$(mktemp -d); trap 'rm -rf "$cfgs" "$tmp"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then
    [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }
    doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}"

now=$(date +%s); open=$(TZ=America/Los_Angeles date -d '2026-11-01 00:00:00' +%s)
if [ "$now" -ge "$open" ]; then state=open; else state=closed; fi
OPEN='Now accepting corporate &amp; group massage bookings\.'
CLOSED='Currently booking corporate &amp; group massage only\.'
if [ "$state" = open ]; then want_txt=$OPEN; other_txt=$CLOSED; else want_txt=$CLOSED; other_txt=$OPEN; fi

report() {
  echo "time now (UTC) $(date -u '+%Y-%m-%d %H:%M:%S'); flip time 2026-11-01 00:00 Pacific = $(date -u -d "@$open" '+%Y-%m-%d %H:%M:%S') UTC; the strip should say: $state"
  echo "SiteGround cache settings of this site (informational):"
  for o in siteground_optimizer_enable_cache siteground_optimizer_file_caching siteground_optimizer_autoflush_cache siteground_optimizer_enable_memcached; do
    echo "  $o = $(wp --path="$doc" option get "$o" 2>/dev/null || echo '(not set)')"
  done
  local bad=0 slug url code want_n other_n band cache
  for slug in "" about services contact privacy-policy accessibility thai-massage therapeutic-massage deep-tissue-massage; do
    url="https://$want/${slug:+$slug/}"
    code=$(curl -s -D "$tmp/h" -o "$tmp/b" -w '%{http_code}' --max-time 30 "$url" || true)
    band=$({ grep -o -F 'class="ts-corp-band"' "$tmp/b" || true; } | wc -l)
    want_n=$({ grep -o -E -- "$want_txt" "$tmp/b" || true; } | wc -l)
    other_n=$({ grep -o -E -- "$other_txt" "$tmp/b" || true; } | wc -l)
    cache=$(tr -d '\r' < "$tmp/h" | grep -i -E '^x-proxy-cache:' | head -1 | cut -d: -f2- | tr -d ' ' || true)
    if [ "$code" = 200 ] && [ "$band" = 1 ] && [ "$want_n" = 1 ] && [ "$other_n" = 0 ]; then
      echo "  OK     $code /${slug:+$slug/}  strip says $state  (x-proxy-cache: ${cache:-none})"
    else
      echo "  WRONG  $code /${slug:+$slug/}  strips=$band right-wording=$want_n other-wording=$other_n  (x-proxy-cache: ${cache:-none})"
      bad=$((bad + 1))
    fi
  done
  if [ "$bad" -eq 0 ]; then echo "all nine pages show the $state wording"; return 0; fi
  echo "CHECK FAILED: $bad of nine pages do not show the $state wording (a page held in the cache keeps the old wording until the cache is purged)." >&2
  return 1
}

case "$phase" in
  check) report ;;
  apply)
    if [ "$state" != open ]; then echo "It is before the flip time: the strip still correctly says 'closed' and a purge would change nothing. Run this again after 2026-11-01 00:00 Pacific." >&2; exit 1; fi
    echo "Purging the SiteGround cache ..."
    wp --path="$doc" --user=1 sg purge || { echo "sg purge failed; purge the cache in Site Tools (Speed > Caching) and run --check." >&2; exit 1; }
    echo "SiteGround cache purged"
    report ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS

case "$mode" in
  check) echo "[1] Read-only check:"; "${ssh_cmd[@]}" bash -s -- check "$target" <<<"$REMOTE" ;;
  apply) echo "[1] Purge the cache, then check:"; "${ssh_cmd[@]}" bash -s -- apply "$target" <<<"$REMOTE" ;;
esac

# ref: ./availability-content.php — the strip's two wordings and the flip time (open_date) this checks; ./push-availability-update.sh — the pattern (connection, guard, server-side public fetch)

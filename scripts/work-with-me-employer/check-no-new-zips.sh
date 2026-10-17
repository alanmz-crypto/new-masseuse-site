#!/usr/bin/env bash
# Read-only: confirm the WPvivid nightly backup stayed OFF and no backup zip is back in any public web root. One command, PASS or FAIL.
#
#   ./check-no-new-zips.sh        run it after a night has passed (WPvivid's job used to run between about 00:30 and 03:00 UTC)
#
# Checks live and staging27/30/31 (staging24/25/26 were deleted on purpose on 2026-10-16 and show as 'gone'; any OTHER missing site is a FAIL): zero zips in wp-content/wpvividbackups, the nightly flag still 0,
# no scheduled wpvivid_main_schedule_event, and the newest file in that folder is older than this fix. Changes nothing.
# This is about WPvivid only. SiteGround's own backups are separate (Site Tools -> Security -> Backups) and cannot be checked from here.
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

out=$(ssh -p "$port" -o BatchMode=yes -o ConnectTimeout=20 "$user@$host" bash -s <<'REMOTE'
set -u
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
for want in thaisolitude.com staging24.thaisolitude.com staging25.thaisolitude.com staging26.thaisolitude.com staging27.thaisolitude.com staging30.thaisolitude.com staging31.thaisolitude.com; do
  doc=""
  while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}; [ "$hh" = "$want" ] && doc=$d; done < "$cfgs"
  if [ -z "$doc" ]; then
    case "$want" in staging24.*|staging25.*|staging26.*) echo "$want|REMOVED|-|-|-" ;; *) echo "$want|NOT_FOUND|-|-|-" ;; esac   # REMOVED = deleted on purpose (2026-10-16, no longer in Site Tools); NOT_FOUND = a site that should exist is missing
    continue
  fi
  zips=$(find "$doc/wp-content/wpvividbackups" -maxdepth 1 -name '*.zip' 2>/dev/null | wc -l)
  flag=$(wp --path="$doc" --skip-plugins --skip-themes eval '$o = get_option("wpvivid_schedule_setting"); echo is_array($o) ? (int) ($o["enable"] ?? 0) : "n/a";' 2>/dev/null || echo "?")
  cron=$(wp --path="$doc" --skip-plugins --skip-themes eval '$n = 0; foreach ((array) _get_cron_array() as $h) { if (isset($h["wpvivid_main_schedule_event"])) { $n++; } } echo $n;' 2>/dev/null || echo "?")
  newest=$(find "$doc/wp-content/wpvividbackups" -type f -printf '%TY-%Tm-%Td %TH:%TM\n' 2>/dev/null | sort | tail -1)
  echo "$want|$zips|$flag|$cron|${newest:--}"
done
REMOTE
)
fail=0
printf '%-32s %5s %9s %6s  %s\n' "site" "zips" "nightly" "cron" "newest file in the backup folder (UTC)"
while IFS='|' read -r site zips flag cron newest; do
  if [ "$zips" = "REMOVED" ]; then st="gone"; elif [ "$zips" = "0" ] && [ "$flag" = "0" ] && [ "$cron" = "0" ]; then st="ok  "; else st="FAIL"; fail=1; fi
  printf '%s %-27s %5s %9s %6s  %s\n' "$st" "$site" "$zips" "$flag" "$cron" "$newest"
done <<<"$out"
echo
if [ "$fail" -eq 0 ]; then echo "PASS: no backup zip in any public web root, the nightly job is still off everywhere."; else echo "FAIL: a site has a zip back, or its nightly flag/cron is on. Fix with: scripts/work-with-me-employer/secure-backups.sh <site> --apply" >&2; exit 1; fi

# ref: ./secure-backups.sh — the tool that turned the nightly job off and moved the zips; ../../remote_execution.txt — gitignored access details (never printed)

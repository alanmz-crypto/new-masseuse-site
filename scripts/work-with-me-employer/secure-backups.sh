#!/usr/bin/env bash
# Take the WPvivid full-site backup zips out of a site's PUBLIC web root and stop its nightly local backup. One site per run.
#
#   ./secure-backups.sh <target>                       dry run: connect, locate the site, report zips + schedule + planned actions (writes nothing)
#   ./secure-backups.sh <target> --apply               DB export, manifest, move the zips to ~/private-backups/<target>-wpvivid-zips (sha256-verified, nothing deleted),
#                                                      set wpvivid_schedule_setting.enable=0, clear the wpvivid_main_schedule_event cron hook, purge the SG cache
#   ./secure-backups.sh <target> --rollback FILE       put the zips back, restore the schedule setting and cron event from a <target>-backups-secure-*.json manifest
#
# <target> is one of: live staging24 staging25 staging26 staging27 staging30 staging31   (anything else is refused, here and again on the server)
#
# Why: wp-content/wpvividbackups/*.zip answered HTTP 200 application/zip to anyone (the folder .htaccess is ignored for static files on SiteGround),
# and WPvivid wrote a new full-site zip there every night (local only, keep 3). Moving files alone would not hold, so the schedule goes off too.
# Proven on staging31 first (apply -> rollback -> identical state -> apply). SiteGround's own nightly backups are what cover a site once this is off.
# The remote side finds each install by `wp option get home` and refuses to act unless exactly one install reports the expected host.
# Access: credentials file + ssh-agent, as push-human-facing-staging.sh (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock).
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"
case "$target" in
  live|staging24|staging25|staging26|staging27|staging30|staging31) shift ;;
  *) echo "Usage: $0 <live|staging24|staging25|staging26|staging27|staging30|staging31> [--apply | --rollback FILE]" >&2; exit 1 ;;
esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys." >&2; exit 2; }

mode=dry; manifest=""
case "${1:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; manifest="${2:-}"; [ -n "$manifest" ] || { echo "--rollback needs the manifest path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $1" >&2; exit 1 ;;
esac

echo "Target: $target (mode: $mode)"
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$mode" "$target" "$manifest" <<'REMOTE'
set -euo pipefail
mode=$1; target=$2; manifest=${3:-}
case "$target" in
  live) want="thaisolitude.com" ;;
  staging24|staging25|staging26|staging27|staging30|staging31) want="${target}.thaisolitude.com" ;;
  *) echo "Remote refused: unknown target '$target'." >&2; exit 1 ;;
esac
command -v wp >/dev/null && command -v python3 >/dev/null || { echo "wp-cli and python3 are required on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
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
echo "$target is ${doc#"$HOME"/} (home host: $want)"
bk="$doc/wp-content/wpvividbackups"
pv="$HOME/private-backups"
vault="$pv/${target}-wpvivid-zips"
# Plugins are skipped for the reads/option write (no plugin output, no side effects); the rollback re-schedule loads them so the custom 'wpvivid_daily' interval exists.
WPQ() { wp --path="$doc" --skip-plugins --skip-themes "$@"; }
sha() { sha256sum "$1" | cut -d' ' -f1; }
enable_now() { WPQ eval '$o = get_option("wpvivid_schedule_setting"); echo (int) (is_array($o) ? ($o["enable"] ?? 0) : 0);'; }
cron_json() { WPQ eval '$o = array(); foreach ((array) _get_cron_array() as $ts => $hooks) { if (isset($hooks["wpvivid_main_schedule_event"])) { foreach ($hooks["wpvivid_main_schedule_event"] as $ev) { $o[] = array("ts" => (int) $ts, "schedule" => $ev["schedule"], "args" => $ev["args"]); } } } echo json_encode($o);'; }
report() {
  echo "-- zips in the public web root (wp-content/wpvividbackups):"
  shopt -s nullglob
  local z n=0
  for z in "$bk"/*.zip; do n=$((n+1)); printf '   %s  %s bytes  sha256 %s\n' "$(basename "$z")" "$(stat -c %s "$z")" "$(sha "$z" | cut -c1-16)"; done
  [ "$n" -gt 0 ] || echo "   (none)"
  echo "-- nightly local backup: wpvivid_schedule_setting.enable = $(enable_now)"
  echo "-- cron events for wpvivid_main_schedule_event: $(cron_json)"
  [ -d "$vault" ] && echo "-- vault $vault holds $(find "$vault" -maxdepth 1 -name '*.zip' | wc -l) zip(s)" || echo "-- vault does not exist yet"
}

if [ "$mode" = dry ]; then
  report
  echo "Dry run only. --apply would: export the DB, write a manifest, move every zip above into the vault (sha256 verified, nothing deleted), set enable=0, clear the cron event."
  exit 0
fi

ts=$(date +%Y%m%d-%H%M%S)
if [ "$mode" = apply ]; then
  shopt -s nullglob
  zips=("$bk"/*.zip)
  if [ "${#zips[@]}" -eq 0 ] && [ "$(enable_now)" = 0 ] && [ "$(cron_json)" = "[]" ]; then echo "Already secured: no zips in the web root, schedule off, no cron event. Nothing to do."; exit 0; fi
  # Fail early, before anything moves: no backup being written right now, and the schedule option has the shape this expects.
  if [ -d "$bk" ] && [ -n "$(find "$bk" -maxdepth 2 -mmin -2 -type f 2>/dev/null | head -1)" ]; then echo "Refusing: a file in wpvividbackups changed in the last 2 minutes (a backup may be running). Try again shortly." >&2; exit 1; fi
  WPQ eval '$o = get_option("wpvivid_schedule_setting"); exit(is_array($o) && array_key_exists("enable", $o) ? 0 : 1);' || { echo "Refusing: wpvivid_schedule_setting is missing or has an unexpected shape." >&2; exit 1; }
  mkdir -p "$pv" "$vault"; chmod 700 "$pv" "$vault"
  dump="$pv/${target}-before-backups-secure-$ts.sql"
  wp --path="$doc" db export "$dump" >/dev/null
  [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
  echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
  mf="$pv/${target}-backups-secure-$ts.json"
  opt_json=$(WPQ option get wpvivid_schedule_setting --format=json)
  cron=$(cron_json)
  list=""; for z in "${zips[@]}"; do list="$list$(basename "$z")|$(sha "$z")|$(stat -c %s "$z")"$'\n'; done
  OPT="$opt_json" CRON="$cron" LIST="$list" MF="$mf" SITE="$want" python3 - <<'PY'
import json, os
zips = [dict(zip(("name", "sha256", "size"), line.split("|"))) for line in os.environ["LIST"].splitlines() if line]
for z in zips: z["size"] = int(z["size"])
json.dump({"site": os.environ["SITE"], "option": json.loads(os.environ["OPT"]), "cron": json.loads(os.environ["CRON"]), "zips": zips}, open(os.environ["MF"], "w"), indent=1)
os.chmod(os.environ["MF"], 0o600)
PY
  echo "manifest (needed for rollback): ${mf#"$HOME"/}"
  for z in "${zips[@]}"; do
    b=$(basename "$z"); want_sha=$(sha "$z")
    [ ! -e "$vault/$b" ] || { echo "Refusing: $vault/$b already exists." >&2; exit 1; }
    mv -n -- "$z" "$vault/$b"
    [ ! -e "$z" ] || { echo "Move failed for $b (still in the web root)." >&2; exit 1; }
    [ "$(sha "$vault/$b")" = "$want_sha" ] || { echo "CHECKSUM MISMATCH for $b after the move; stop and use --rollback ${mf#"$HOME"/}" >&2; exit 1; }
    echo "moved + verified: $b"
  done
  WPQ eval '$o = get_option("wpvivid_schedule_setting"); if (!is_array($o) || !array_key_exists("enable", $o)) { fwrite(STDERR, "unexpected option shape; not changing it\n"); exit(1); } $o["enable"] = 0; update_option("wpvivid_schedule_setting", $o); wp_unschedule_hook("wpvivid_main_schedule_event"); echo "schedule set to off, cron event cleared\n";'
  wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable)"
  echo "== state after =="; report
  exit 0
fi

# ---- rollback ----
manifest=${manifest/#\$HOME/$HOME}; manifest=${manifest/#\~/$HOME}
case "$manifest" in "$pv/${target}-backups-secure-"*.json) ;; *) echo "Refusing: manifest must be $pv/${target}-backups-secure-*.json" >&2; exit 1 ;; esac
[ -f "$manifest" ] || { echo "Manifest not found: $manifest" >&2; exit 1; }
[ "$(python3 -c 'import json,sys; print(json.load(open(sys.argv[1]))["site"])' "$manifest")" = "$want" ] || { echo "Manifest is not for $want" >&2; exit 1; }
mkdir -p "$bk"
python3 -c 'import json,sys; [print("%s|%s" % (z["name"], z["sha256"])) for z in json.load(open(sys.argv[1]))["zips"]]' "$manifest" | while IFS='|' read -r b s; do
  if [ -e "$bk/$b" ]; then echo "already in place: $b"; continue; fi
  [ -e "$vault/$b" ] || { echo "Missing from both places: $b" >&2; exit 1; }
  mv -n -- "$vault/$b" "$bk/$b"
  [ "$(sha "$bk/$b")" = "$s" ] || { echo "CHECKSUM MISMATCH restoring $b" >&2; exit 1; }
  echo "restored + verified: $b"
done
WWM_MANIFEST="$manifest" WPQ eval '$m = json_decode(file_get_contents(getenv("WWM_MANIFEST")), true); update_option("wpvivid_schedule_setting", $m["option"]); echo "schedule setting restored\n";'
same=$(OPT=$(WPQ option get wpvivid_schedule_setting --format=json) python3 -c 'import json,os,sys; print(json.loads(os.environ["OPT"]) == json.load(open(sys.argv[1]))["option"])' "$manifest")
[ "$same" = True ] || { echo "Restored schedule setting does NOT equal the snapshot; stop and inspect." >&2; exit 1; }
echo "verified: schedule setting equals the snapshot exactly"
WWM_MANIFEST="$manifest" wp --path="$doc" eval '$m = json_decode(file_get_contents(getenv("WWM_MANIFEST")), true); foreach ($m["cron"] as $e) { if (!wp_next_scheduled("wpvivid_main_schedule_event", $e["args"])) { wp_schedule_event($e["ts"], $e["schedule"], "wpvivid_main_schedule_event", $e["args"]); } } echo "cron event restored\n";'
wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable)"
echo "== state after rollback =="; report
REMOTE

# ref: ./secure-backups-staging31.sh — the staging31-only original this generalises (same logic, proven apply -> rollback -> apply)
# ref: ./push-human-facing-staging.sh — same connection/guard pattern; ../../remote_execution.txt — gitignored access details (never printed)

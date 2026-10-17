#!/usr/bin/env bash
# Move WPvivid's leftover folders (wp-content/wpvividbackups, wpvivid_uploads, wpvivid_staging) out of a site's PUBLIC web root into the private vault. One site per run.
# Nothing is deleted: the folders are MOVED (sha256 of every file checked before and after), with a manifest that makes the move exactly reversible.
#
#   ./quarantine-wpvivid-leftovers.sh <target>                       dry run: locate the site, report what is there and what would happen (writes nothing)
#   ./quarantine-wpvivid-leftovers.sh <target> --apply               move the folders to ~/private-backups/<target>-wpvivid-leftovers-<ts>/, write the manifest, purge the SG cache
#   ./quarantine-wpvivid-leftovers.sh <target> --rollback MANIFEST   put the folders back from a <target>-wpvivid-leftovers-*.json manifest and verify every checksum
#
# <target> is one of: live staging27 staging30 staging31 (anything else is refused, here and again on the server).
# Why: after WPvivid is deleted, its uninstall leaves wp-content/wpvividbackups/wpvivid_log/*.txt behind. This server serves static files from there even though a
# .htaccess sits beside them (HTTP 200), so the logs are public. They hold no passwords or paths (WordPress/PHP/DB versions, table names, backup file names): low severity, but
# check-no-new-zips.sh rightly fails on them. Ryan, 2026-10-17: "use your judgement" (Option B: move, do not delete).
# Refuses when: the WPvivid plugin is still ACTIVE on the site (it would write there / recreate the folders), any zip is in the folder (use secure-backups.sh), or any file in
# the folders changed in the last 2 minutes (a backup may be running). The site is found by `wp option get home`, never by folder name.
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock). The SSH account also holds live, so a write to live still needs Ryan's go.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

target="${1:-}"
case "$target" in live|staging27|staging30|staging31) ;; *) echo "Usage: $0 <live|staging27|staging30|staging31> [--apply | --rollback MANIFEST]" >&2; exit 1 ;; esac
mode=dry; manifest=""
case "${2:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; manifest="${3:-}"; [ -n "$manifest" ] || { echo "--rollback needs the manifest path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $2" >&2; exit 1 ;;
esac
echo "Target site: $target   mode: $mode"
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$mode" "$target" "$manifest" <<'REMOTE'
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
mode=$1; target=$2; manifest=${3:-}
case "$target" in
  live) want="thaisolitude.com" ;;
  staging27|staging30|staging31) want="${target}.thaisolitude.com" ;;
  *) echo "Remote refused: unknown target '$target'." >&2; exit 1 ;;
esac
command -v python3 >/dev/null || { echo "python3 is required on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then
    [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }
    doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/} (home host: $want)"
FOLDERS="wpvividbackups wpvivid_uploads wpvivid_staging"
wc="$doc/wp-content"
pv="$HOME/private-backups"
sha() { sha256sum "$1" | cut -d' ' -f1; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a file still answers)"; }

plugin_state() { if [ -d "$wc/plugins/wpvivid-backuprestore" ]; then wp --path="$doc" --skip-plugins --skip-themes plugin get wpvivid-backuprestore --field=status 2>/dev/null || echo unknown; else echo gone; fi; }
report() {
  echo "-- WPvivid plugin: $(plugin_state)"
  local f any=0
  for f in $FOLDERS; do
    if [ -d "$wc/$f" ]; then any=1; echo "-- wp-content/$f: $(find "$wc/$f" -type f | wc -l) file(s), $(find "$wc/$f" -type f -name '*.zip' | wc -l) zip(s), $(du -sk "$wc/$f" | cut -f1) KB"; else echo "-- wp-content/$f: not there"; fi
  done
  [ "$any" -eq 1 ] || echo "   (nothing to move)"
}

if [ "$mode" = dry ]; then
  report
  echo "Dry run only. --apply would: refuse if the plugin is active, a zip is present or a file changed in the last 2 minutes; otherwise move the folders above into $pv/${target}-wpvivid-leftovers-<time>/ (sha256 of every file checked), write a manifest, purge the cache."
  exit 0
fi

ts=$(date +%Y%m%d-%H%M%S)
if [ "$mode" = apply ]; then
  [ "$(plugin_state)" != active ] || { echo "Refusing: the WPvivid plugin is ACTIVE on $target. Deactivate/delete it first (it would write into these folders or recreate them)." >&2; exit 1; }
  present=""; for f in $FOLDERS; do [ -d "$wc/$f" ] && present="$present $f"; done
  [ -n "$present" ] || { echo "Already clean: none of the three folders exists. Nothing to do."; exit 0; }
  for f in $present; do
    [ "$(find "$wc/$f" -type f -name '*.zip' | wc -l)" -eq 0 ] || { echo "Refusing: wp-content/$f contains zip(s). Use secure-backups.sh for backups; this tool is for leftovers." >&2; exit 1; }
    [ -z "$(find "$wc/$f" -type f -mmin -2 2>/dev/null | head -1)" ] || { echo "Refusing: a file in wp-content/$f changed in the last 2 minutes (a backup may be running). Try again shortly." >&2; exit 1; }
  done
  vault="$pv/${target}-wpvivid-leftovers-$ts"; mf="$pv/${target}-wpvivid-leftovers-$ts.json"
  [ ! -e "$vault" ] && [ ! -e "$mf" ] || { echo "Refusing: $vault or its manifest already exists." >&2; exit 1; }
  mkdir -p "$pv" && chmod 700 "$pv" && mkdir "$vault" && chmod 700 "$vault"
  list=""
  for f in $present; do
    rels=$(cd "$wc/$f" && find . -type f -printf '%P\n' | LC_ALL=C sort)   # a variable + here-string, not process substitution: the server's bash does not support <( )
    while IFS= read -r rel; do [ -n "$rel" ] || continue; list="$list$f/$rel|$(sha "$wc/$f/$rel")|$(stat -c %s "$wc/$f/$rel")"$'\n'; done <<<"$rels"
  done
  probe=$(printf '%s' "$list" | awk -F'|' '$1 ~ /_log\/.*\.txt$/ {print $1; exit}')
  SITE="$want" VAULT="$vault" FOLDERS_PRESENT="$present" LIST="$list" MF="$mf" python3 - <<'PY'
import json, os
files = [dict(zip(("path", "sha256", "size"), line.split("|"))) for line in os.environ["LIST"].splitlines() if line]
for f in files: f["size"] = int(f["size"])
json.dump({"site": os.environ["SITE"], "vault": os.environ["VAULT"], "folders": os.environ["FOLDERS_PRESENT"].split(), "files": files}, open(os.environ["MF"], "w"), indent=1)
os.chmod(os.environ["MF"], 0o600)
PY
  echo "manifest (needed for rollback): ${mf#"$HOME"/}  [$(printf '%s' "$list" | grep -c .) file(s)]"
  for f in $present; do
    mv -n -- "$wc/$f" "$vault/$f"
    [ ! -e "$wc/$f" ] || { echo "Move failed for $f (still in the web root). Use --rollback ${mf#"$HOME"/}" >&2; exit 1; }
  done
  bad=0
  while IFS='|' read -r p s _; do [ -n "$p" ] || continue; [ "$(sha "$vault/$p")" = "$s" ] || { echo "CHECKSUM MISMATCH for $p" >&2; bad=1; }; done <<<"$list"
  [ "$bad" -eq 0 ] || { echo "Checksum problem after the move; use --rollback ${mf#"$HOME"/}" >&2; exit 1; }
  echo "moved + verified: $(printf '%s' "$list" | grep -c .) file(s) in $(echo "$present" | wc -w) folder(s) -> ${vault#"$HOME"/}"
  purge
  if [ -n "$probe" ]; then printf 'a former log URL now answers: HTTP %s (404/403 is the goal; a stale 200 can linger at the edge for a while)\n' "$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "https://$want/wp-content/wpvividbackups/$probe" || echo "?")"; fi
  echo "== state after =="; report
  exit 0
fi

# ---- rollback ----
manifest=${manifest/#\$HOME/$HOME}; manifest=${manifest/#\~/$HOME}
case "$manifest" in "$pv/${target}-wpvivid-leftovers-"*.json) ;; *) echo "Refusing: manifest must be $pv/${target}-wpvivid-leftovers-*.json" >&2; exit 1 ;; esac
[ -f "$manifest" ] || { echo "Manifest not found: $manifest" >&2; exit 1; }
[ "$(python3 -c 'import json,sys; print(json.load(open(sys.argv[1]))["site"])' "$manifest")" = "$want" ] || { echo "Manifest is not for $want" >&2; exit 1; }
vault=$(python3 -c 'import json,sys; print(json.load(open(sys.argv[1]))["vault"])' "$manifest")
case "$vault" in "$pv/${target}-wpvivid-leftovers-"*) ;; *) echo "Refusing: vault path in the manifest is not under $pv" >&2; exit 1 ;; esac
folders=$(python3 -c 'import json,sys; print(" ".join(json.load(open(sys.argv[1]))["folders"]))' "$manifest")
for f in $folders; do
  case " $FOLDERS " in *" $f "*) ;; *) echo "Refusing: unexpected folder name in the manifest: $f" >&2; exit 1 ;; esac
  [ -d "$vault/$f" ] || { echo "Missing from the vault: $f" >&2; exit 1; }
  [ ! -e "$wc/$f" ] || { echo "Refusing: wp-content/$f already exists again; resolve by hand." >&2; exit 1; }
done
for f in $folders; do mv -n -- "$vault/$f" "$wc/$f"; echo "restored folder: $f"; done
bad=0
lines=$(python3 -c 'import json,sys; [print("%s|%s" % (f["path"], f["sha256"])) for f in json.load(open(sys.argv[1]))["files"]]' "$manifest")
while IFS='|' read -r p s; do [ -n "$p" ] || continue; [ "$(sha "$wc/$p")" = "$s" ] || { echo "CHECKSUM MISMATCH restoring $p" >&2; bad=1; }; done <<<"$lines"
[ "$bad" -eq 0 ] || { echo "Checksum problem after the rollback; stop and inspect." >&2; exit 1; }
echo "verified: every file is back and matches its checksum"
purge
echo "== state after rollback =="; report
REMOTE

# ref: ./remove-dead-plugins.sh, ../secure-backups.sh — same connection/guard/manifest pattern; ../check-no-new-zips.sh — the check that flags these leftovers; ../../../remote_execution.txt — gitignored access details (never printed)

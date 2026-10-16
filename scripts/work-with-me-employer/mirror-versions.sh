#!/usr/bin/env bash
# Bring a STAGING site's plugin and theme versions up to live's, so rehearsals run on the same code live runs. Never downgrades; never touches live.
#
#   ./mirror-versions.sh <target>                       dry run: show which plugins/themes differ from live and what would be done (writes nothing)
#   ./mirror-versions.sh <target> --apply               file backup of exactly those folders + DB export (both copied OFF the server and verified),
#                                                       then update each to live's exact version, verify, purge the SG cache
#   ./mirror-versions.sh <target> --rollback TARBALL    set the current folders aside (kept, renamed) and restore the backed-up ones from the tarball
#
# <target>: staging31 (the rehearsal copy). Live is read-only here: only its versions are read.
# Why a file backup: a database export does not contain plugin or theme files, and SiteGround's manual backups are limited.
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock).
# shellcheck disable=SC2016  # the single-quoted commands are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"
case "$target" in staging31) shift ;; *) echo "Usage: $0 <staging31> [--apply | --rollback TARBALL]" >&2; exit 1 ;; esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys." >&2; exit 2; }

mode=dry; tarball=""
case "${1:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; tarball="${2:-}"; [ -n "$tarball" ] || { echo "--rollback needs the tarball path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $1" >&2; exit 1 ;;
esac
echo "Target: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
phase=$1; target=$2; tarball=${3:-}
case "$target" in staging31) want="staging31.thaisolitude.com" ;; *) echo "Remote refused: unknown target '$target'." >&2; exit 1 ;; esac
command -v wp >/dev/null || { echo "wp-cli not found" >&2; exit 1; }
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
pick() { local cfg d h hh; while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}; [ "$hh" = "$1" ] && { echo "$d"; return; }; done < "$cfgs"; return 1; }
doc=$(pick "$want") || { echo "No install reports $want" >&2; exit 1; }
live=$(pick "thaisolitude.com") || { echo "No install reports thaisolitude.com" >&2; exit 1; }
[ "$doc" != "$live" ] || { echo "Refusing: target and live resolve to the same folder." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}   (live is read-only here: ${live#"$HOME"/})"
pv="$HOME/private-backups"; mkdir -p "$pv"; chmod 700 "$pv"
vers() { # vers PATH KIND -> "name version" lines (installed, excluding drop-ins)
  wp --path="$1" --skip-plugins --skip-themes "$2" list --fields=name,version --format=csv | tail -n +2 | tr ',' ' ' | grep -v '^object-cache'
}
plan=$(mktemp); la=$(mktemp); ta=$(mktemp); trap 'rm -f "$cfgs" "$plan" "$la" "$ta"' EXIT
for kind in plugin theme; do
  vers "$live" $kind | sort > "$la"; vers "$doc" $kind | sort > "$ta"
  join "$la" "$ta" | while read -r name lv tv; do
    [ "$lv" = "$tv" ] && continue
    older=$(printf '%s\n%s\n' "$lv" "$tv" | sort -V | head -1)
    if [ "$older" = "$tv" ]; then echo "$kind $name $tv $lv UPDATE" >> "$plan"; else echo "$kind $name $tv $lv SKIP(target is newer than live; never downgrading)" >> "$plan"; fi
  done
done
touch "$plan"
echo "-- differences from live (kind name target-version live-version action):"; if [ -s "$plan" ]; then sed 's/^/   /' "$plan"; else echo "   none: $target already runs live's versions"; fi
todo=$(awk '$5=="UPDATE"' "$plan")
case "$phase" in
  dry) [ -n "$todo" ] && echo "Dry run only. --apply would back up those folders + the DB, update each to live's exact version, verify, and purge the cache." || true; exit 0 ;;
  prepare)
    [ -n "$todo" ] || { echo "Nothing to do."; exit 0; }
    ts=$(date +%Y%m%d-%H%M%S); dirs=()
    while read -r kind name tv lv act; do dirs+=("wp-content/${kind}s/$name"); done <<<"$todo"
    ( umask 077
      tar -C "$doc" -czf "$pv/${target}-files-before-mirror-$ts.tar.gz" "${dirs[@]}"
      wp --path="$doc" db export "$pv/${target}-before-mirror-$ts.sql" >/dev/null )
    tar -tzf "$pv/${target}-files-before-mirror-$ts.tar.gz" >/dev/null || { echo "File backup failed its integrity test; stopping." >&2; exit 1; }
    [ -s "$pv/${target}-before-mirror-$ts.sql" ] || { echo "Database export is empty; stopping." >&2; exit 1; }
    cp "$plan" "$pv/${target}-mirror-plan-$ts.txt"; chmod 600 "$pv/${target}-mirror-plan-$ts.txt"
    echo "file backup: private-backups/${target}-files-before-mirror-$ts.tar.gz ($(stat -c%s "$pv/${target}-files-before-mirror-$ts.tar.gz") bytes, $(tar -tzf "$pv/${target}-files-before-mirror-$ts.tar.gz" | wc -l) entries, sha256 $(sha256sum "$pv/${target}-files-before-mirror-$ts.tar.gz" | cut -c1-16))"
    echo "database export: private-backups/${target}-before-mirror-$ts.sql ($(stat -c%s "$pv/${target}-before-mirror-$ts.sql") bytes)" ;;
  update)
    [ -n "$todo" ] || { echo "Nothing to do."; exit 0; }
    while read -r kind name tv lv act; do
      echo "updating $kind $name $tv -> $lv"
      wp --path="$doc" --user=1 "$kind" update "$name" --version="$lv" 2>&1 | tail -3
    done <<<"$todo"
    echo "-- verify against live:"; bad=0
    while read -r kind name tv lv act; do now=$(vers "$doc" $kind | awk -v n="$name" '$1==n{print $2}'); [ "$now" = "$lv" ] && echo "   ok  $kind $name $now" || { echo "   MISMATCH $kind $name now=$now live=$lv"; bad=1; }; done <<<"$todo"
    wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable)"
    [ "$bad" -eq 0 ] || exit 1 ;;
  rollback)
    tarball=${tarball/#\$HOME/$HOME}
    case "$tarball" in "$pv/${target}-files-before-mirror-"*.tar.gz) ;; *) echo "Refusing: tarball must be $pv/${target}-files-before-mirror-*.tar.gz" >&2; exit 1 ;; esac
    [ -f "$tarball" ] && tar -tzf "$tarball" >/dev/null || { echo "Tarball missing or corrupt: $tarball" >&2; exit 1; }
    ts=$(date +%Y%m%d-%H%M%S); aside="$pv/${target}-replaced-$ts"
    for rel in $(tar -tzf "$tarball" | awk -F/ 'NF>=3{print $1"/"$2"/"$3}' | sort -u); do
      if [ -d "$doc/$rel" ]; then mkdir -p "$aside/$(dirname "$rel")"; mv "$doc/$rel" "$aside/$rel"; echo "set aside, kept outside the web root: $rel -> private-backups/$(basename "$aside")/$rel"; fi
    done
    tar -C "$doc" -xzf "$tarball"; echo "restored from $(basename "$tarball")"
    if tar -C "$doc" -dzf "$tarball" >/dev/null 2>&1; then echo "verified: the files on disk equal the backup exactly"; else echo "WARNING: the files on disk still differ from the backup; stop and inspect." >&2; exit 1; fi
    wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || true ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) remote dry "$target" ;;
  apply)
    echo "[1] Back up (files + DB) on the server:"; remote prepare "$target"
    echo "[2] Copy the backups to this computer and verify:"; "$here/sync-server-backups.sh"
    d="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
    t=$("${ssh_cmd[@]}" 'ls -1t "$HOME"/private-backups/staging31-files-before-mirror-*.tar.gz 2>/dev/null | head -1')
    if [ -n "$t" ]; then
      want_sha=$("${ssh_cmd[@]}" "sha256sum \"$t\"" | cut -d' ' -f1)
      scp -q -P "$port" -o BatchMode=yes "$user@$host:$t" "$d/$(basename "$t")"
      chmod 600 "$d/$(basename "$t")"
      if [ "$(sha256sum "$d/$(basename "$t")" | cut -d' ' -f1)" = "$want_sha" ]; then echo "file backup copied off-server, sha256 verified: $(basename "$t")"; else echo "File backup copy does NOT match; stopping before any update." >&2; exit 1; fi
    fi
    echo "[3] Update to live's versions:"; remote update "$target" ;;
  rollback) remote rollback "$target" "$tarball" ;;
esac

# ref: ./sync-server-backups.sh — the off-server copy of the SQL export; ./secure-backups.sh, ./structured-data/push-structured-data.sh — same connection/guard pattern
# ref: ../../remote_execution.txt — gitignored access details (never printed)

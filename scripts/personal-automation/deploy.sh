#!/usr/bin/env bash
# staging36 by default; Ryan runs S_TARGET=live ... --apply himself after review.
# Every mutation exports the DB and verifies its off-server copy before writing.
# shellcheck disable=SC2016
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
creds=${S26_CREDS:-$here/../../remote_execution.txt}
target=${S_TARGET:-staging36}
mode=preflight
snapshot=""
files=(install.php unlisted.php home.html privacy.html)
case "${RPA_RELEASE:-base}" in
  base) installer=install.php ;;
  terms) installer=install-terms.php; files+=(install-terms.php terms.html) ;;
  *) echo 'RPA_RELEASE must be base or terms.' >&2; exit 1 ;;
esac
case "${1:-}" in
  '') ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; snapshot=${2:?A server manifest path is required} ;;
  *) echo 'Usage: deploy.sh [--apply | --rollback MANIFEST]' >&2; exit 1 ;;
esac
case "$target" in staging36|live) ;; *) echo 'Target must be staging36 or live.' >&2; exit 1 ;; esac
[[ -f "$creds" ]] || { echo 'Missing private SSH connection file.' >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user=${user%%@*}
[[ -n "$host" && -n "$port" && -n "$user" ]] || { echo 'Incomplete SSH connection settings.' >&2; exit 1; }
ssh-add -l >/dev/null || { echo 'SSH agent unavailable.' >&2; exit 1; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
# Content-addressed packages avoid overwriting another session's staging input.
package="rpa-package-$(cd "$here"; cat "${files[@]}" | sha256sum | cut -c1-16)"
tar -C "$here" -cf - "${files[@]}" |
  "${ssh_cmd[@]}" "umask 077; mkdir -p \"\$HOME/$package\"; tar -xf - -C \"\$HOME/$package\""
read -r -d '' remote <<'REMOTE' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
phase=$1; target=$2; package=$3; dump=${4:-}; snapshot=${5:-}; installer=$6
case "$installer" in install.php|install-terms.php) ;; *) exit 1 ;; esac
case "$target" in staging36) want=staging36.thaisolitude.com ;; live) want=thaisolitude.com ;; *) exit 1 ;; esac
[[ "$package" =~ ^rpa-package-[a-f0-9]{16}$ ]] || exit 1
doc=""
# SiteGround's shell has no /dev/fd, so process substitution is unavailable.
configs=$(mktemp)
trap 'rm -f "$configs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php > "$configs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" option get home 2>/dev/null || true)
  h=${h#http://}; h=${h#https://}; h=${h%/}
  if [[ "$h" == "$want" ]]; then
    [[ -z "$doc" ]] || { echo 'Ambiguous WordPress install'; exit 1; }
    doc=$d
  fi
done < "$configs"
[[ -n "$doc" ]] || { echo 'Target install not found'; exit 1; }
export RPA_BACKUP_DIR="$HOME/private-backups"
mkdir -p "$RPA_BACKUP_DIR"; chmod 700 "$RPA_BACKUP_DIR"
run() { wp --path="$doc" --user=1 eval-file "$HOME/$package/$installer" "$@"; }
case "$phase" in
  preflight) run preflight ;;
  prepare)
    run preflight
    dump="${target}-before-personal-automation-$(date +%Y%m%d-%H%M%S).sql"
    (umask 077; wp --path="$doc" db export "$RPA_BACKUP_DIR/$dump")
    [[ -s "$RPA_BACKUP_DIR/$dump" ]] || exit 1
    printf 'RPA_EXPORT=%s\n' "$dump"
    ;;
  apply|rollback)
    [[ "$dump" =~ ^(staging36|live)-before-personal-automation-[0-9-]+\.sql$ ]] || exit 1
    export RPA_DB_BACKUP="$RPA_BACKUP_DIR/$dump"
    run "$phase" "$snapshot"
    wp --path="$doc" sg purge
    run preflight
    ;;
  *) exit 1 ;;
esac
REMOTE
run_remote() {
  local command
  printf -v command 'bash -s -- %q %q %q %q %q %q' "$1" "$target" "$package" "${2:-}" "$snapshot" "$installer"
  "${ssh_cmd[@]}" "$command" <<< "$remote"
}
echo "Target: $target; operation: $mode"
if [[ "$mode" == preflight ]]; then
  run_remote preflight
  exit
fi
prepared=$(run_remote prepare)
printf '%s\n' "$prepared"
dump=$(printf '%s\n' "$prepared" | sed -n 's/^RPA_EXPORT=//p')
[[ "$dump" =~ ^(staging36|live)-before-personal-automation-[0-9-]+\.sql$ ]] || { echo 'Missing exact export filename'; exit 1; }
S26_CREDS="$creds" bash "$here/../work-with-me-employer/sync-server-backups.sh"
run_remote "$mode" "$dump"
S26_CREDS="$creds" bash "$here/../work-with-me-employer/sync-server-backups.sh"
# ref: ./install.php, ../work-with-me-employer/sync-server-backups.sh — guarded mutation and verified off-server backup

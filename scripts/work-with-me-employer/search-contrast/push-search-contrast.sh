#!/usr/bin/env bash
# One Additional CSS block, staging39 only. Every mutation requires its exact
# fresh export encrypted off-site; actual snapshot is preserved before rendering.
# shellcheck disable=SC2016
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
mode=dry; snap=""
case "${1:-}" in
  "") [ "$#" -eq 0 ] || exit 1 ;;
  --apply) [ "$#" -eq 1 ] || exit 1; mode=apply ;;
  --verify) [ "$#" -eq 2 ] || exit 1; mode=verify; snap=$2 ;;
  --rollback) [ "$#" -eq 2 ] || exit 1; mode=rollback; snap=$2 ;;
  *) echo "Usage: $0 [--apply | --verify FILE | --rollback FILE]" >&2; exit 1 ;;
esac
target="${S_TARGET:-staging39}"
[ "$target" = staging39 ] || { echo "S_TARGET must be staging39." >&2; exit 1; }
if [[ "$mode" = rollback || "$mode" = verify ]] && [[ ! "$snap" =~ ^(\$HOME|/home/[a-zA-Z0-9_-]+)/private-backups/search-contrast-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]]; then
  echo "Snapshot must be this Search update's private before-snapshot." >&2; exit 1
fi
if [[ "$mode" = apply || "$mode" = rollback ]] && { [ -z "${TS_OFFSITE_HOOK:-}" ] || [ ! -x "$TS_OFFSITE_HOOK" ]; }; then
  echo "Writes require the executable encrypted off-site hook." >&2; exit 1
fi
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials are incomplete." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket." >&2; exit 2; }
ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent holds no keys." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
echo "Target site: $target   component: search-contrast   mode: $mode"
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused." >&2; exit 2; }
tar -C "$here" -cf - install-search-contrast.php search-contrast.css manifest.json verify-render.php \
  | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-search-contrast" && tar -xf - -C "$HOME/wwm-package-search-contrast"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
phase=$1; target=$2; snap=${3:-}
[ "$target" = staging39 ] || { echo "Remote refused: staging39 only." >&2; exit 1; }
want=staging39.thaisolitude.com
command -v wp >/dev/null || { echo "wp-cli missing" >&2; exit 1; }
wp_discovery() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  candidate_prefix=$(wp_discovery --path="$d" config get table_prefix --type=variable 2>/dev/null) || continue
  [[ "$candidate_prefix" =~ ^[A-Za-z0-9_]+$ ]] || continue
  h=$(wp_discovery --path="$d" db query "SELECT option_value FROM ${candidate_prefix}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  case "$h" in "http://$want"|"https://$want"|"http://$want/"|"https://$want/")
    [ -z "$doc" ] || { echo "Multiple exact staging39 installs; refusing." >&2; exit 1; }; doc=$d ;;
  esac
done < "$cfgs"
[ -n "$doc" ] || { echo "No exact staging39 install found." >&2; exit 1; }
wp() { command wp --skip-plugins=sg-security "$@"; }
echo "$target is ${doc#"$HOME"/}"
# SiteGround's logical home resolves to /home/customer. Resolve that ancestor
# alias once; the private directory and snapshot themselves must not be symlinks.
# BEGIN SEARCH PRIVATE PATH FUNCTIONS (exercised with real filesystem aliases).
canonical_private_dir() {
  local supplied=$1
  [ -d "$supplied" ] && [ ! -L "$supplied" ] || { echo "Private backup directory must be an existing non-symlink directory." >&2; return 1; }
  ( cd -- "$supplied" && pwd -P )
}
canonical_snapshot() {
  local supplied=$1 expected=$2 parent
  [ -f "$supplied" ] && [ ! -L "$supplied" ] || { echo "Snapshot must be an existing non-symlink regular file." >&2; return 1; }
  parent=$(canonical_private_dir "${supplied%/*}") || return 1
  [ "$parent" = "$expected" ] || { echo "Snapshot resolves outside the configured private backup directory." >&2; return 1; }
  printf '%s/%s\n' "$parent" "${supplied##*/}"
}
# END SEARCH PRIVATE PATH FUNCTIONS.
[ ! -L "$HOME/private-backups" ] || { echo "Private backup directory must not be a symlink." >&2; exit 1; }
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
WWM_BACKUP_DIR=$(canonical_private_dir "$HOME/private-backups")
[[ "$WWM_BACKUP_DIR" =~ ^/home/[a-zA-Z0-9_-]+/private-backups$ ]] || { echo "Unexpected physical private-backup path." >&2; exit 1; }
export WWM_BACKUP_DIR
package="$HOME/wwm-package-search-contrast"
run() { wp --path="$doc" --user=1 eval-file "$package/install-search-contrast.php" "$@"; }
ready() {
  local state
  state=$(run preflight); printf '%s\n' "$state"
  case "$state" in *'search-contrast ready;'*) ;; *) echo "Apply requires the exact original CSS; use --verify for a completed apply." >&2; exit 1 ;; esac
}
rollback_ready() {
  local state
  state=$(run preflight); printf '%s\n' "$state"
  case "$state" in
    *'search-contrast applied;'*) run verify "$snap" ;;
    *'search-contrast ready;'*) run verify "$snap" original ;;
    *) echo "Unknown CSS recovery state; nothing changed." >&2; exit 1 ;;
  esac
}
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 || { echo "SiteGround purge failed; no rendered verification claimed." >&2; exit 1; }; echo "SiteGround cache purged"; }
render() {
  local which=$1 route code
  tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
  for route in '/?s=massage' '/'; do
    code=$(curl -sS -o "$tmp/page.html" -w '%{http_code}' --max-time 30 "https://$want$route")
    echo "  $code $route"
    [ "$code" = 200 ] || { echo "Rendered check failed: HTTP $code; HTTP202 is INVALID access, not verification." >&2; exit 1; }
    php "$package/verify-render.php" "$tmp/page.html" "$snap" "$which"
  done
  rm -rf "$tmp"
}
snap=${snap/#\$HOME/$HOME}
if [ -n "$snap" ]; then
  [[ "$snap" =~ ^/home/[a-zA-Z0-9_-]+/private-backups/search-contrast-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]] || { echo "Unexpected snapshot path." >&2; exit 1; }
  snap=$(canonical_snapshot "$snap" "$WWM_BACKUP_DIR")
fi
case "$phase" in
  preflight) run preflight ;;
  prepare|prepare-rollback)
    if [ "$phase" = prepare ]; then ready; else rollback_ready; fi
    dump="$WWM_BACKUP_DIR/${target}-before-search-contrast-$(date +%Y%m%d-%H%M%S)-$$.sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Empty export; stopping before mutation." >&2; exit 1; }
    echo "SEARCH-BACKUP $dump $(sha256sum "$dump" | cut -d' ' -f1) $(stat -c%s "$dump")" ;;
  apply) ready; run apply ;;
  verify|verify-purged)
    run verify "$snap"
    [ "$phase" != verify-purged ] || purge
    render applied
    run verify "$snap" ;;
  rollback)
    rollback_ready; run rollback "$snap"
    run verify "$snap" original
    purge; render original
    run verify "$snap" original ;;
  *) echo "Unknown remote phase." >&2; exit 1 ;;
esac
EOS
remote() { local quoted; printf -v quoted '%q ' "$@"; "${ssh_cmd[@]}" "bash -s -- $quoted" <<<"$REMOTE"; }
sync_exact() {
  local path=$1 sha=$2 bytes=$3 name dest after transfer
  [[ "$path" =~ ^/home/[a-zA-Z0-9_-]+/private-backups/(staging39-before-search-contrast-[0-9]{8}-[0-9]{6}-[0-9]+\.sql|search-contrast-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json)$ ]] || { echo "Refusing unexpected backup path." >&2; return 1; }
  [[ "$sha" =~ ^[a-f0-9]{64}$ && "$bytes" =~ ^[1-9][0-9]*$ ]] || { echo "Missing exact backup checksum or size." >&2; return 1; }
  name=${path##*/}; dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
  mkdir -p "$dest" && chmod 700 "$dest" || return 1
  # A later verification must never overwrite a previously preserved backup.
  if [ -e "$dest/$name" ] || [ -L "$dest/$name" ]; then
    [ -f "$dest/$name" ] && [ ! -L "$dest/$name" ] && [ -O "$dest/$name" ] || { echo "Existing local backup is not a regular owned file." >&2; return 1; }
    chmod 600 "$dest/$name" || return 1
  else
    transfer=$(mktemp "$dest/.search-transfer-XXXXXXXX") || return 1
    if ! "${ssh_cmd[@]}" "cat '$path'" > "$transfer"; then rm -f "$transfer"; return 1; fi
    if [ "$(stat -c%s "$transfer")" != "$bytes" ] || [ "$(sha256sum "$transfer" | cut -d' ' -f1)" != "$sha" ]; then
      rm -f "$transfer"; echo "Exact backup transfer checksum/size differs." >&2; return 1
    fi
    chmod 600 "$transfer" && ln "$transfer" "$dest/$name" || { rm -f "$transfer"; echo "Could not preserve the backup without replacing an existing file." >&2; return 1; }
    rm -f "$transfer"
  fi
  [ "$(stat -c%s "$dest/$name")" = "$bytes" ] && [ "$(sha256sum "$dest/$name" | cut -d' ' -f1)" = "$sha" ] || { echo "Exact backup copy checksum/size differs." >&2; return 1; }
  after=$("${ssh_cmd[@]}" "sha256sum '$path'") || return 1
  [ "${after%% *}" = "$sha" ] || { echo "Remote backup changed during copy." >&2; return 1; }
  "$TS_OFFSITE_HOOK" "$dest" "$name" || { echo "Encrypted off-site readback did not verify the exact file." >&2; return 1; }
  echo "Verified exact backup on server, local copy and encrypted off-site readback: $name ($bytes bytes, sha256 $sha)"
}
sync_marker() {
  local kind=$1 output=$2 path sha bytes marker_count
  local -a records
  marker_count=$(grep -c "^$kind\([[:space:]]\|$\)" <<<"$output" || true)
  [ "$marker_count" -eq 1 ] || { echo "Missing or duplicate $kind marker; refusing to guess." >&2; return 1; }
  mapfile -t records < <(sed -n "s/^$kind \(\/home\/[a-zA-Z0-9_-]*\/private-backups\/[^ ]*\) \([a-f0-9]\{64\}\) \([0-9][0-9]*\)$/\1 \2 \3/p" <<<"$output")
  [ "${#records[@]}" -eq 1 ] || { echo "No unique actual $kind binding; refusing to guess." >&2; return 1; }
  read -r path sha bytes <<<"${records[0]}"
  sync_exact "$path" "$sha" "$bytes" || return 1
  actual_path=$path
}
case "$mode" in
  dry) remote preflight "$target"; echo "Dry run only; nothing changed." ;;
  verify) remote verify "$target" "$snap" ;;
  apply)
    prepared=$(remote prepare "$target"); printf '%s\n' "$prepared"
    sync_marker SEARCH-BACKUP "$prepared"
    apply_rc=0; applied=$(remote apply "$target") || apply_rc=$?
    printf '%s\n' "$applied"
    snapshot_sync_rc=0; sync_marker SEARCH-SNAPSHOT "$applied" || snapshot_sync_rc=$?
    [ "$apply_rc" -eq 0 ] || { echo "Apply failed ($apply_rc); actual snapshot sync exit $snapshot_sync_rc. No rendered verification claimed." >&2; exit "$apply_rc"; }
    [ "$snapshot_sync_rc" -eq 0 ] || exit "$snapshot_sync_rc"
    snap=$actual_path
    mapfile -t completed < <(sed -n 's/^Success: Applied exact search-contrast CSS\. Before-snapshot: \(.*\)$/\1/p' <<<"$applied")
    [ "${#completed[@]}" -eq 1 ] && [ "${completed[0]}" = "$snap" ] || { echo "Apply completion differs from its actual private snapshot." >&2; exit 1; }
    remote verify-purged "$target" "$snap" ;;
  rollback)
    prepared=$(remote prepare-rollback "$target" "$snap"); printf '%s\n' "$prepared"
    sync_marker SEARCH-BACKUP "$prepared"
    sync_marker SEARCH-SNAPSHOT "$prepared"
    remote rollback "$target" "$snap" ;;
esac

#!/usr/bin/env bash
# First visitor install, exact raw-live profile only. Every mutation requires its exact
# fresh export encrypted off-site; actual journal is preserved even on failed apply.
# shellcheck disable=SC2016
set -euo pipefail
here=$(cd "$(dirname "$0")/peek-release" && pwd)
mode=dry; snap=""; inspect_sha=""; incident_sha=""; incident_bytes=""; incident_receipt=""
prepared=""; preserved=""; applied=""; result=""
live30_snapshot=/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json
live30_snapshot_sha=4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b
case "${1:-}" in
  "") [ "$#" -eq 0 ] || exit 1 ;;
  --apply)
    if [ "$#" -eq 2 ] && [ "$2" = --finish-live30 ]; then mode=finish-live30; snap=$live30_snapshot
    else [ "$#" -eq 1 ] || exit 1; mode=apply; fi ;;
  --verify) [ "$#" -eq 2 ] || exit 1; mode=verify; snap=$2 ;;
  --inspect-state) [ "$#" -eq 3 ] || exit 1; mode=inspect; snap=$2; inspect_sha=$3
    [[ "$inspect_sha" =~ ^[a-f0-9]{64}$ ]] || exit 1 ;;
  --inspect-live30) [ "$#" -eq 1 ] || exit 1; mode=inspect-live30; snap=$live30_snapshot ;;
  --rollback)
    if [ "$#" -eq 3 ] && [ "$3" = --incident-live30 ]; then mode=rollback-live30; snap=$2
    else [ "$#" -eq 2 ] || exit 1; mode=rollback; snap=$2; fi ;;
  *) echo "Usage: $0 [--apply [--finish-live30] | --verify FILE | --inspect-state FILE SHA256 | --inspect-live30 | --rollback FILE [--incident-live30]]" >&2; exit 1 ;;
esac
target="${S_TARGET:-live}"
[ "$target" = live ] || { echo "S_TARGET must be live." >&2; exit 1; }
if [[ "$mode" = *-live30 ]]; then
  [ "$snap" = "$live30_snapshot" ] || { echo "LIVE #30 requires its exact immutable actual journal." >&2; exit 1; }
  bindings=$(python3 "$here/validate-marker.py" --incident-bindings "$here") || exit 1
  binding_fields=$(python3 -c 'import json,sys;d=json.loads(sys.argv[1]);print(d["snapshot_basename"]);print(d["snapshot_sha256"]);print(d["snapshot_bytes"]);print(d["receipt_basename"]);print(d["receipt_sha256"])' "$bindings") || exit 1
  mapfile -t fields <<<"$binding_fields"
  [ "${#fields[@]}" -eq 5 ] && [ "${fields[0]}" = "${live30_snapshot##*/}" ] && [ "${fields[1]}" = "$live30_snapshot_sha" ] && [ "${fields[3]}" = incident-live30.json ] || { echo "LIVE #30 receipt bindings differ." >&2; exit 1; }
  incident_bytes=${fields[2]}; incident_receipt=${fields[3]}; incident_sha=${fields[4]}
  [[ "$incident_bytes" =~ ^[1-9][0-9]*$ && "$incident_sha" =~ ^[a-f0-9]{64}$ ]] || exit 1
fi
if [[ "$mode" = rollback || "$mode" = verify || "$mode" = inspect ]] && [[ ! "$snap" =~ ^(\$HOME|/home/[a-zA-Z0-9_-]+)/private-backups/peek-release-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]]; then
  echo "Snapshot must be this visitor release's private before-snapshot." >&2; exit 1
fi
if [[ "$mode" != dry && "$mode" != inspect && "$mode" != inspect-live30 ]] && { [ -z "${TS_OFFSITE_HOOK:-}" ] || [ ! -x "$TS_OFFSITE_HOOK" ]; }; then
  echo "Writes require the executable encrypted off-site hook." >&2; exit 1
fi
creds="${S26_CREDS:-$here/../../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials are incomplete." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket." >&2; exit 2; }
ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent holds no keys." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
echo "Target site: $target   component: peek-release   mode: $mode"
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused." >&2; exit 2; }
package_files=(install-peek-release.php source-binding.php payload manifest.json live-profile.json verify-render.php)
if [[ "$mode" = *-live30 ]]; then package_files+=(incident-receipt.php incident-live30-binding.json incident-live30.json); fi
tar -C "$here" -cf - "${package_files[@]}" \
  | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-peek-release" && tar -xf - -C "$HOME/wwm-package-peek-release"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
phase=$1; target=$2; snap=${3:-}; expected_sha=${4:-}; expected_receipt_sha=${5:-}
[ "$target" = live ] || { echo "Remote refused: live only." >&2; exit 1; }
want=thaisolitude.com
package="$HOME/wwm-package-peek-release"
if [[ "$phase" = *-live30 ]]; then
  [ "$snap" = /home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json ] && [ "$expected_sha" = 4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b ] || { echo "LIVE #30 exact journal binding required." >&2; exit 1; }
  [[ "$expected_receipt_sha" =~ ^[a-f0-9]{64}$ ]] || exit 1
  [ -f "$snap" ] && [ ! -L "$snap" ] && [ "$(stat -c %a "$snap")" = 600 ] && [ "$(sha256sum "$snap" | cut -d' ' -f1)" = "$expected_sha" ] || { echo "LIVE #30 actual journal changed; no site bootstrap." >&2; exit 1; }
  [ -f "$package/incident-live30.json" ] && [ ! -L "$package/incident-live30.json" ] && [ "$(sha256sum "$package/incident-live30.json" | cut -d' ' -f1)" = "$expected_receipt_sha" ] || { echo "LIVE #30 receipt changed; no site bootstrap." >&2; exit 1; }
  export TS_PEEK_RELEASE_EXPECT_SNAPSHOT_SHA256="$expected_sha"
fi
command -v wp >/dev/null || { echo "wp-cli missing" >&2; exit 1; }
wp_discovery() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  candidate_prefix=$(wp_discovery --path="$d" config get table_prefix --type=variable 2>/dev/null) || continue
  [[ "$candidate_prefix" =~ ^[A-Za-z0-9_]+$ ]] || continue
  h=$(wp_discovery --path="$d" db query "SELECT option_value FROM ${candidate_prefix}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  case "$h" in "http://$want")
    [ -z "$doc" ] || { echo "Multiple exact live installs; refusing." >&2; exit 1; }; doc=$d ;;
  esac
done < "$cfgs"
[ -n "$doc" ] || { echo "No exact live install found." >&2; exit 1; }
# SiteGround's logical home is an ancestor alias. Bootstrap WordPress through
# the selected site's physical root so its loaded plugin paths stay canonical.
doc=$(cd -- "$doc" && pwd -P)
wp() { command wp --skip-plugins=sg-security "$@"; }
echo "$target is ${doc#"$HOME"/}"
# SiteGround's logical home resolves to /home/customer. Resolve that ancestor
# alias once; the private directory and snapshot themselves must not be symlinks.
# BEGIN RELEASE PRIVATE PATH FUNCTIONS (exercised with real filesystem aliases).
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
# END RELEASE PRIVATE PATH FUNCTIONS.
[ ! -L "$HOME/private-backups" ] || { echo "Private backup directory must not be a symlink." >&2; exit 1; }
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
WWM_BACKUP_DIR=$(canonical_private_dir "$HOME/private-backups")
[[ "$WWM_BACKUP_DIR" =~ ^/home/[a-zA-Z0-9_-]+/private-backups$ ]] || { echo "Unexpected physical private-backup path." >&2; exit 1; }
export WWM_BACKUP_DIR
run() { TS_PEEK_RELEASE_PROFILE=raw-live-v1 wp --path="$doc" --user=1 eval-file "$package/install-peek-release.php" "$@"; }
journal_binding() {
  [ -f "$snap" ] && [ ! -L "$snap" ] && [ "$(stat -c %a "$snap")" = 600 ] || { echo "Actual recovery journal is absent or unsafe; no success claimed." >&2; return 1; }
  echo "RELEASE-JOURNAL $snap $(sha256sum "$snap" | cut -d' ' -f1) $(stat -c%s "$snap")"
}
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 || { echo "SiteGround purge failed; no rendered verification claimed." >&2; exit 1; }; echo "SiteGround cache purged"; }
render() {
  local which=$1 route code expected
  tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
  php -r '$m=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);foreach($m["pages"] as $p)echo $p["path"]."\n";echo "/?s=massage\n/peek-release-not-a-real-page/\n";' "$package/manifest.json" > "$tmp/routes"
  while IFS= read -r route; do
    expected=200; [ "$route" != /peek-release-not-a-real-page/ ] || expected=404
    code=$(curl -sS -o "$tmp/page.html" -w '%{http_code}' --max-time 30 "https://$want$route")
    echo "  $code $route"
    [ "$code" = "$expected" ] || { echo "Rendered check failed: HTTP $code; HTTP202 is INVALID access, not verification." >&2; exit 1; }
    php "$package/verify-render.php" "$tmp/page.html" "$snap" "$which" "$route"
  done < "$tmp/routes"
  rm -rf "$tmp"
}
snap=${snap/#\$HOME/$HOME}
if [ -n "$snap" ]; then
  [[ "$snap" =~ ^/home/[a-zA-Z0-9_-]+/private-backups/peek-release-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]] || { echo "Unexpected snapshot path." >&2; exit 1; }
  if [ "$phase" = apply ]; then
    [ "${snap%/*}" = "$WWM_BACKUP_DIR" ] && [ ! -e "$snap" ] && [ ! -L "$snap" ] || { echo "Selected journal must be unused and canonical." >&2; exit 1; }
  else
    snap=$(canonical_snapshot "$snap" "$WWM_BACKUP_DIR")
  fi
fi
if [[ "$phase" = rollback || "$phase" = verify || "$phase" = verify-purged || "$phase" = inspect-state ]]; then
  [[ "$expected_sha" =~ ^[a-f0-9]{64}$ ]] || { echo "Exact preserved snapshot hash required." >&2; exit 1; }
  [ "$(sha256sum "$snap" | cut -d' ' -f1)" = "$expected_sha" ] || { echo "Snapshot changed after preservation; no mutation." >&2; exit 1; }
  export TS_PEEK_RELEASE_EXPECT_SNAPSHOT_SHA256="$expected_sha"
fi
fresh_export() {
  local dump
  dump="$WWM_BACKUP_DIR/live-before-peek-release-$(date -u +%Y%m%d-%H%M%S)-$$.sql"
  [ ! -e "$dump" ] && [ ! -L "$dump" ] || exit 1
  ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
  [ -s "$dump" ] || { echo "Empty export; stopping before mutation." >&2; exit 1; }
  echo "RELEASE-BACKUP $dump $(sha256sum "$dump" | cut -d' ' -f1) $(stat -c%s "$dump")"
}
case "$phase" in
  preflight) run preflight ;;
  inspect-state) run verify "$snap" ;;
  inspect-live30) run verify-incident "$snap"; run recovery-check-incident "$snap" ;;
  prepare-finish-live30)
    run verify-incident "$snap"
    echo RELEASE-FINAL-BEGIN
    run recovery-check-incident "$snap"
    journal_binding; fresh_export ;;
  prepare-rollback-live30)
    run recovery-check-incident "$snap"
    journal_binding; fresh_export ;;
  finish-live30)
    run verify-incident "$snap"
    run recovery-check-incident "$snap"
    purge; render applied
    echo RELEASE-FINAL-BEGIN
    run verify-incident "$snap" ;;
  rollback-live30)
    run recovery-check-incident "$snap"
    run rollback-incident "$snap"
    run recovery-check-incident "$snap" restored
    purge; render original
    echo RELEASE-FINAL-BEGIN
    run recovery-check-incident "$snap" restored ;;
  prepare|prepare-rollback)
    if [ "$phase" = prepare ]; then
      run preflight
      intent="$WWM_BACKUP_DIR/peek-release-before-$(date -u +%Y%m%d-%H%M%S)-$(php -r 'echo bin2hex(random_bytes(4));').json"
      [ ! -e "$intent" ] && [ ! -L "$intent" ] || exit 1
      echo "RELEASE-INTENT $intent"
    else
      run recovery-check "$snap"
      journal_binding
    fi
    fresh_export ;;
  apply)
    # The caller retains the exact intended path independently of this output.
    rc=0; TS_PEEK_RELEASE_NEW_JOURNAL="$snap" run apply || rc=$?
    journal_binding || exit 1
    exit "$rc" ;;
  preserve) journal_binding ;;
  verify|verify-purged)
    run verify "$snap"
    [ "$phase" != verify-purged ] || purge
    render applied
    # Only this final marker is retained by the caller's exact-marker parser.
    echo RELEASE-FINAL-BEGIN
    run verify "$snap" ;;
  rollback)
    # Completed-apply verify and snapshot-free preflight are intentionally absent:
    # recognized incomplete journals and interrupted reverse prefixes are valid.
    run recovery-check "$snap"
    run rollback "$snap"
    run recovery-check "$snap" original
    purge; render original
    echo RELEASE-FINAL-BEGIN
    run recovery-check "$snap" original ;;
  *) echo "Unknown remote phase." >&2; exit 1 ;;
esac
EOS
remote() { local quoted; printf -v quoted '%q ' "$@"; "${ssh_cmd[@]}" "bash -s -- $quoted" <<<"$REMOTE"; }
capture_remote() {
  local output_name=$1 rc=0 output; shift
  output=$(remote "$@") || rc=$?
  printf '%s\n' "$output"
  printf -v "$output_name" '%s' "$output"
  return "$rc"
}
sync_exact() {
  local path=$1 sha=$2 bytes=$3 name dest after transfer
  [[ "$path" =~ ^/home/[a-zA-Z0-9_-]+/private-backups/(live-before-peek-release-[0-9]{8}-[0-9]{6}-[0-9]+\.sql|peek-release-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json)$ ]] || { echo "Refusing unexpected backup path." >&2; return 1; }
  [[ "$sha" =~ ^[a-f0-9]{64}$ && "$bytes" =~ ^[1-9][0-9]*$ ]] || { echo "Missing exact backup checksum or size." >&2; return 1; }
  name=${path##*/}; dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
  [ ! -L "$dest" ] || { echo "Local private backup directory must not be a symlink." >&2; return 1; }
  mkdir -p "$dest" && chmod 700 "$dest" || return 1
  dest=$(cd "$dest" && pwd -P) || return 1
  local checkout
  checkout=$(git -C "$here" rev-parse --show-toplevel 2>/dev/null || true)
  if [ -n "$checkout" ] && [[ "$dest/" = "$checkout/"* ]]; then
    echo "Private backups must stay outside the checkout." >&2; return 1
  fi
  # A later verification must never overwrite a previously preserved backup.
  if [ -e "$dest/$name" ] || [ -L "$dest/$name" ]; then
    [ -f "$dest/$name" ] && [ ! -L "$dest/$name" ] && [ -O "$dest/$name" ] || { echo "Existing local backup is not a regular owned file." >&2; return 1; }
    chmod 600 "$dest/$name" || return 1
  else
    transfer=$(mktemp "$dest/.release-transfer-XXXXXXXX") || return 1
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
  local kind=$1 output=$2 bound_path=${3:-} bound_sha=${4:-} bound_bytes=${5:-} path sha bytes marker_count
  local -a records
  marker_count=$(grep -c "^$kind\([[:space:]]\|$\)" <<<"$output" || true)
  [ "$marker_count" -eq 1 ] || { echo "Missing or duplicate $kind marker; refusing to guess." >&2; return 1; }
  mapfile -t records < <(sed -n "s/^$kind \(\/home\/[a-zA-Z0-9_-]*\/private-backups\/[^ ]*\) \([a-f0-9]\{64\}\) \([0-9][0-9]*\)$/\1 \2 \3/p" <<<"$output")
  [ "${#records[@]}" -eq 1 ] || { echo "No unique actual $kind binding; refusing to guess." >&2; return 1; }
  read -r path sha bytes <<<"${records[0]}"
  if [ -n "$bound_path" ]; then
    [ "$path" = "$bound_path" ] && [ "$sha" = "$bound_sha" ] && [ "$bytes" = "$bound_bytes" ] || { echo "Actual incident journal differs from the fixed receipt binding." >&2; return 1; }
  fi
  sync_exact "$path" "$sha" "$bytes" || return 1
  actual_path=$path; actual_sha=$sha
}
sync_incident_receipt() {
  local dest source name transfer checkout
  source="$here/$incident_receipt"; name=peek-release-live30-receipt.json
  [ -f "$source" ] && [ ! -L "$source" ] && [ "$(sha256sum "$source" | cut -d' ' -f1)" = "$incident_sha" ] || { echo "Pinned incident receipt changed before preservation." >&2; return 1; }
  dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
  [ ! -L "$dest" ] || return 1
  mkdir -p "$dest" && chmod 700 "$dest" || return 1
  dest=$(cd "$dest" && pwd -P) || return 1
  checkout=$(git -C "$here" rev-parse --show-toplevel 2>/dev/null || true)
  if [ -n "$checkout" ] && [[ "$dest/" = "$checkout/"* ]]; then echo "Private backups must stay outside the checkout." >&2; return 1; fi
  if [ -e "$dest/$name" ] || [ -L "$dest/$name" ]; then
    [ -f "$dest/$name" ] && [ ! -L "$dest/$name" ] && [ -O "$dest/$name" ] || return 1
    chmod 600 "$dest/$name" || return 1
  else
    transfer=$(mktemp "$dest/.incident-transfer-XXXXXXXX") || return 1
    if ! cat "$source" > "$transfer" || ! chmod 600 "$transfer" || ! ln "$transfer" "$dest/$name"; then rm -f "$transfer"; return 1; fi
    rm -f "$transfer"
  fi
  [ "$(sha256sum "$dest/$name" | cut -d' ' -f1)" = "$incident_sha" ] || { echo "Preserved incident receipt checksum differs." >&2; return 1; }
  "$TS_OFFSITE_HOOK" "$dest" "$name" || { echo "Incident receipt encrypted off-site readback failed." >&2; return 1; }
  echo "Verified pinned incident receipt encrypted off-site: $name (sha256 $incident_sha)"
}
tmp_local=$(mktemp -d); trap 'rm -rf "$tmp_local"' EXIT
validate() {
  local kind=$1 state=$2 output=$3
  printf '%s\n' "$output" > "$tmp_local/marker.log"
  local saved="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}/${snap##*/}"
  python3 "$here/validate-marker.py" "$here" "$tmp_local/marker.log" "$kind" "$state" "$snap" "$saved"
}
final_binding() {
  local output=$1 kind=$2 state=$3 count final
  count=$(grep -c '^RELEASE-FINAL-BEGIN$' <<<"$output" || true)
  [ "$count" -eq 1 ] || { echo "Missing or duplicate final state boundary." >&2; return 1; }
  final=${output##*RELEASE-FINAL-BEGIN$'\n'}
  validate "$kind" "$state" "$final"
}
case "$mode" in
  inspect-live30)
    remote inspect-live30 "$target" "$snap" "$live30_snapshot_sha" "$incident_sha" ;;
  finish-live30|rollback-live30)
    capture_remote prepared "prepare-$mode" "$target" "$snap" "$live30_snapshot_sha" "$incident_sha"
    sync_marker RELEASE-BACKUP "$prepared"
    sync_marker RELEASE-JOURNAL "$prepared" "$live30_snapshot" "$live30_snapshot_sha" "$incident_bytes"
    sync_incident_receipt
    if [ "$mode" = finish-live30 ]; then final_binding "$prepared" RELEASE-INCIDENT recoverable
    else validate RELEASE-INCIDENT recoverable "$prepared"; fi
    capture_remote result "$mode" "$target" "$snap" "$live30_snapshot_sha" "$incident_sha"
    if [ "$mode" = finish-live30 ]; then final_binding "$result" RELEASE-INCIDENT applied
    else final_binding "$result" RELEASE-INCIDENT restored; fi ;;
  inspect)
    # Stream diagnostics and preserve failure. No backup, purge, rendering,
    # installer mutation or local recovery-journal transfer in this mode.
    remote inspect-state "$target" "$snap" "$inspect_sha" ;;
  dry)
    preflight_rc=0; result=$(remote preflight "$target") || preflight_rc=$?
    printf '%s\n' "$result"
    [ "$preflight_rc" -eq 0 ] || exit "$preflight_rc"
    validate RELEASE-PREFLIGHT ready "$result"
    echo "Dry run only; no release writes." ;;
  verify)
    capture_remote preserved preserve "$target" "$snap"; sync_marker RELEASE-JOURNAL "$preserved"; snap=$actual_path
    capture_remote result verify "$target" "$snap" "$actual_sha"
    final_binding "$result" RELEASE-SNAPSHOT applied ;;
  apply)
    capture_remote prepared prepare "$target"
    validate RELEASE-PREFLIGHT ready "$prepared"
    count=$(grep -c '^RELEASE-INTENT\([[:space:]]\|$\)' <<<"$prepared" || true)
    [ "$count" -eq 1 ] || { echo "Missing/duplicate journal intent." >&2; exit 1; }
    snap=$(sed -n 's/^RELEASE-INTENT \(\/home\/[a-zA-Z0-9_-]*\/private-backups\/peek-release-before-[0-9]\{8\}-[0-9]\{6\}-[a-f0-9]\{8\}\.json\)$/\1/p' <<<"$prepared")
    [ -n "$snap" ] || { echo "Malformed journal intent." >&2; exit 1; }
    sync_marker RELEASE-BACKUP "$prepared"
    apply_rc=0; capture_remote applied apply "$target" "$snap" || apply_rc=$?
    # Always retrieve the preselected actual journal, even after an apply error
    # or missing completion marker. Never guess the newest file in a directory.
    preserve_rc=0; capture_remote preserved preserve "$target" "$snap" || preserve_rc=$?
    if [ "$preserve_rc" -eq 0 ]; then sync_marker RELEASE-JOURNAL "$preserved" || preserve_rc=$?; fi
    [ "$apply_rc" -eq 0 ] || { echo "Apply failed ($apply_rc); recovery journal preservation exit $preserve_rc. No success claimed." >&2; exit "$apply_rc"; }
    [ "$preserve_rc" -eq 0 ] || exit "$preserve_rc"
    [ "$actual_path" = "$snap" ] || { echo "Preserved journal differs from the selected intent." >&2; exit 1; }
    validate RELEASE-SNAPSHOT applied "$applied"
    capture_remote result verify-purged "$target" "$snap" "$actual_sha"
    final_binding "$result" RELEASE-SNAPSHOT applied ;;
  rollback)
    capture_remote prepared prepare-rollback "$target" "$snap"
    sync_marker RELEASE-BACKUP "$prepared"
    sync_marker RELEASE-JOURNAL "$prepared"; snap=$actual_path
    validate RELEASE-RECOVERY recoverable "$prepared"
    capture_remote result rollback "$target" "$snap" "$actual_sha"
    final_binding "$result" RELEASE-RECOVERY original ;;
esac

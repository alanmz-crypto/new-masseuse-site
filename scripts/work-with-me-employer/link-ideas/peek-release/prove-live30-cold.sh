#!/usr/bin/env bash
# Isolated causal proof for LIVE #30; the server is read-only.
# Default: fixed pre-apply SQL + current files, no warming/installer apply.
# --recovery: current SQL + exact private recovery inputs, isolated incident
# checks/rollback and static responses. Both modes preserve raw origin/cleanup.
# shellcheck disable=SC2016
set -uo pipefail
umask 077
here=$(cd "$(dirname "$0")" && pwd)
root=$(cd "$here/../../../.." && pwd)
wwm="$root/scripts/work-with-me-employer"
ci="$root/scripts/ci"
invalid() { echo "COLD PROOF INVALID: $*" >&2; exit 2; }
fail() { echo "COLD PROOF FAIL: $*" >&2; exit 1; }
proof_mode=cold
if [ "$#" -eq 1 ] && [ "$1" = --recovery ]; then proof_mode=recovery
elif [ "$#" -ne 0 ]; then invalid "only the fixed --recovery selector is supported"; fi
[ "${S_TARGET:-}" = live ] || invalid "requires fixed live read-only source"
[ -z "${TS_REHEARSAL_FROM_DIR:-}" ] && [ -z "${TS_REHEARSAL_TEST_PREP:-}" ] || invalid "source/preparation overrides are forbidden"
creds="${S26_CREDS:-$root/remote_execution.txt}"
[ -f "$creds" ] && [ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || invalid "read-only SSH prerequisites missing"
for executable in docker python3 ssh curl sha256sum; do command -v "$executable" >/dev/null || invalid "required executable missing"; done
if [ "$proof_mode" = cold ] && curl --noproxy '*' -s -o /dev/null -m 2 http://127.0.0.1:8090/; then invalid "loopback endpoint already in use"; fi
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [[ "$port" =~ ^[0-9]+$ ]] && [ -n "$user" ] || invalid "read-only SSH configuration incomplete"
img() { sed -n "s/^[[:space:]]*image:[[:space:]]*\($1[^[:space:]]*\).*/\1/p" "$root/docker-compose.yml" | head -1; }
db_img=$(img mariadb); wp_img=$(img 'wordpress:php'); cli_img=$(img 'wordpress:cli')
for pin in "$db_img" "$wp_img" "$cli_img"; do [[ "$pin" =~ @sha256:[a-f0-9]{64}$ ]] || invalid "practice image is not pinned"; done

release_rehearsal=1
owned_cleanup_component=peek-release
release_work_parent=$(cd -- "${RUNNER_TEMP:-$HOME/.cache}" && pwd -P) || invalid "temporary parent unavailable"
work="$release_work_parent/ts-rehearsal-$$"
project="ts-rehearsal-$$"
release_work_owned=0; release_relay_pid=""
pub="$work/public_html"; bk="$work/backups"; out="$work/out"
dc() { docker compose --progress quiet -p "$project" -f "$work/compose.yml" "$@"; }
# BEGIN IDENTICAL REHEARSAL CLEANUP
release_cleanup_resources() {
  local failed=0 directory_safe=1 current_parent resource ids relay_parent n
  local cleanup_component="${owned_cleanup_component:-}" cleanup_log
  case "$cleanup_component" in
    peek-release) [ "${release_rehearsal:-0}" = 1 ] || return 1 ;;
    core-title-resize) [ "${release_rehearsal:-0}" = 0 ] && [ "${inst:-}" = link-ideas/core-title-resize/install-peek-core-title-resize.php ] || return 1 ;;
    *) echo "FAIL cleanup: no exact owned-copy profile; no cleanup mutation attempted" >&2; return 1 ;;
  esac
  cleanup_log="${RUNNER_TEMP:-$HOME/.cache}/ops-out/$cleanup_component-native/cleanup.log"
  # Only fixed statuses/counts enter this retained log. Docker output, copied
  # source/configuration, SQL and private recovery snapshots are never retained.
  if ! mkdir -p -- "${cleanup_log%/*}" || ! touch -- "$cleanup_log" || ! chmod 600 -- "$cleanup_log"; then
    echo "FAIL release cleanup: cannot retain cleanup evidence" >&2
    failed=1; cleanup_log=/dev/null
  fi
  release_cleanup_note() {
    printf '%s\n' "$*" >> "$cleanup_log" && printf '%s\n' "$*"
  }
  current_parent=$(cd -- "${release_work_parent:-/nonexistent}" 2>/dev/null && pwd -P) || current_parent=""
  if [ "${release_work_owned:-0}" != 1 ] ||
     [ "$project" != "ts-rehearsal-$$" ] || [ -z "$release_work_parent" ] ||
     [ "$current_parent" != "$release_work_parent" ] ||
     [ "$work" != "$release_work_parent/ts-rehearsal-$$" ] || [ -L "$work" ]; then
    release_cleanup_note "FAIL release cleanup: exact owned project and directory identity not established; no cleanup mutation attempted" || true
    return 1
  fi

  if [ -n "${release_relay_pid:-}" ]; then
    if ! [[ "$release_relay_pid" =~ ^[1-9][0-9]*$ ]]; then
      release_cleanup_note "FAIL release cleanup: invalid relay identity; no signal sent" || true
      failed=1; directory_safe=0
    elif kill -0 "$release_relay_pid" 2>/dev/null; then
      relay_parent=$(ps -o ppid= -p "$release_relay_pid" 2>/dev/null) || relay_parent=""
      relay_parent=${relay_parent//[[:space:]]/}
      if [ "$relay_parent" != "$$" ]; then
        release_cleanup_note "FAIL release cleanup: relay is not this process's child; no signal sent" || true
        failed=1; directory_safe=0
      else
        kill -TERM "$release_relay_pid" 2>/dev/null || true
        for n in {1..30}; do kill -0 "$release_relay_pid" 2>/dev/null || break; sleep 0.1; done
        if kill -0 "$release_relay_pid" 2>/dev/null; then
          kill -KILL "$release_relay_pid" 2>/dev/null || true
          for n in {1..20}; do kill -0 "$release_relay_pid" 2>/dev/null || break; sleep 0.1; done
        fi
        if kill -0 "$release_relay_pid" 2>/dev/null; then
          release_cleanup_note "FAIL release cleanup: owned relay still exists after bounded stop" || true
          failed=1; directory_safe=0
        else
          wait "$release_relay_pid" 2>/dev/null || true
          release_relay_pid=""
        fi
      fi
    else
      wait "$release_relay_pid" 2>/dev/null || true
      release_relay_pid=""
    fi
  fi
  if [ -z "${release_relay_pid:-}" ]; then
    release_cleanup_note "INFO release cleanup: owned relay absent" || failed=1
  fi

  if [ -f "$work/compose.yml" ] && [ ! -L "$work/compose.yml" ]; then
    if dc down -v --remove-orphans >/dev/null 2>&1; then
      release_cleanup_note "INFO release cleanup: Compose teardown completed" || failed=1
    else
      release_cleanup_note "FAIL release cleanup: Compose teardown failed" || true
      failed=1; directory_safe=0
    fi
  elif [ -e "$work/compose.yml" ] || [ -L "$work/compose.yml" ]; then
    release_cleanup_note "FAIL release cleanup: unsafe Compose file; no Compose command attempted" || true
    failed=1; directory_safe=0
  else
    release_cleanup_note "INFO release cleanup: no Compose file was created" || failed=1
  fi

  # Successful empty results are required even on an early setup failure.
  # Exact Compose project labels scope every query; never use global prune.
  for resource in container volume network; do
    case "$resource" in
      container) ids=$(docker ps --all --quiet --filter "label=com.docker.compose.project=$project" 2>/dev/null); n=$? ;;
      volume) ids=$(docker volume ls --quiet --filter "label=com.docker.compose.project=$project" 2>/dev/null); n=$? ;;
      network) ids=$(docker network ls --quiet --filter "label=com.docker.compose.project=$project" 2>/dev/null); n=$? ;;
    esac
    if [ "$n" -ne 0 ] || [ -n "$ids" ]; then
      release_cleanup_note "FAIL release cleanup: owned $resource absence unproved" || true
      failed=1; directory_safe=0
    else
      release_cleanup_note "INFO release cleanup: owned $resource resources absent" || failed=1
    fi
  done

  if [ "$directory_safe" = 1 ]; then
    if ! rm -rf -- "$work" 2>/dev/null; then
      # The copy may contain files written by a container UID. This fallback
      # mounts only the exact owned folder and cannot reach any network.
      if [ -d "$work" ] && [ ! -L "$work" ]; then
        docker run --rm --network none --user 0 --entrypoint sh \
          --label "com.docker.compose.project=$project" --label com.docker.compose.service=release-cleanup \
          -v "$work:/w" "$cli_img" -c 'find /w -mindepth 1 -maxdepth 1 -exec rm -rf -- {} +' >/dev/null 2>&1 || failed=1
        rm -rf -- "$work" 2>/dev/null || failed=1
      else
        failed=1
      fi
    fi
    if [ -e "$work" ] || [ -L "$work" ]; then
      release_cleanup_note "FAIL release cleanup: owned temporary directory still exists" || true
      failed=1
    else
      release_cleanup_note "INFO release cleanup: owned temporary directory absent" || failed=1
    fi
    # The fallback also carries the project label; its --rm completion must
    # leave no owned container, volume or network behind.
    for resource in container volume network; do
      case "$resource" in
        container) ids=$(docker ps --all --quiet --filter "label=com.docker.compose.project=$project" 2>/dev/null); n=$? ;;
        volume) ids=$(docker volume ls --quiet --filter "label=com.docker.compose.project=$project" 2>/dev/null); n=$? ;;
        network) ids=$(docker network ls --quiet --filter "label=com.docker.compose.project=$project" 2>/dev/null); n=$? ;;
      esac
      if [ "$n" -ne 0 ] || [ -n "$ids" ]; then
        release_cleanup_note "FAIL release cleanup: final owned $resource absence unproved" || true
        failed=1
      fi
    done
  else
    release_cleanup_note "FAIL release cleanup: copy directory retained inside runner because resource teardown is unproved" || true
  fi
  if [ "$failed" -eq 0 ]; then
    release_cleanup_note "INFO release cleanup: PASSED; owned relay, containers, volumes, networks and temporary directory absent" || return 1
    return 0
  fi
  release_cleanup_note "FAIL release cleanup: deletion not fully verified" || true
  return 1
}
release_cleanup_exit() {
  local result=${1:-$?}
  trap - EXIT
  release_cleanup_resources || { [ "$result" -ne 0 ] || result=1; }
  exit "$result"
}
# END IDENTICAL REHEARSAL CLEANUP

trap release_cleanup_exit EXIT
[ ! -e "$work" ] && [ ! -L "$work" ] || invalid "refusing existing temporary directory"
mkdir -m 700 -- "$work" || invalid "exclusive temporary directory creation failed"
release_work_owned=1
mkdir -p -- "$pub" "$bk" "$out" && chmod 777 "$bk" || invalid "private copy directories unavailable"
evidence="${RUNNER_TEMP:-$HOME/.cache}/ops-out/peek-live30-$proof_mode"
cold_cleanup() {
  local result=$? file
  trap - EXIT
  if mkdir -p -- "$evidence"; then
    # Allowlist only our hash descriptors and fixed source/bootstrap receipts.
    # No export, copied source/config, response body or private snapshot escapes.
    for file in "$out"/cold-import.json "$out"/after-bootstrap.json \
      "$out"/first-1254.json "$out"/first-1255.json "$out"/first-1257.json \
      "$out"/repeat-1254.json "$out"/repeat-1255.json "$out"/repeat-1257.json \
      "$out"/import-receipt.json "$out"/bootstrap.log "$out"/source-binding.log "$out"/proof.log \
      "$out"/recovery-context.json "$out"/before-bootstrap.json \
      "$out"/after-verify-applied.json "$out"/after-recoverable.json "$out"/after-rollback.json \
      "$out"/after-recovery-restored.json "$out"/after-verify-restored.json \
      "$out"/after-verify-applied-after-render.json "$out"/after-verify-restored-after-render.json \
      "$out"/verify-applied.marker.json "$out"/recoverable.marker.json "$out"/rollback.marker.json \
      "$out"/recovery-restored.marker.json "$out"/verify-restored.marker.json \
      "$out"/verify-applied-after-render.marker.json "$out"/verify-restored-after-render.marker.json \
      "$out"/render-applied.log "$out"/render-original.log; do
      [ -f "$file" ] || continue
      cp -- "$file" "$evidence/" || { [ "$result" -ne 0 ] || result=1; }
    done
  else
    echo "FAIL cold proof: evidence retention failed" >&2
    [ "$result" -ne 0 ] || result=1
  fi
  release_cleanup_exit "$result"
}
trap cold_cleanup EXIT

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
part=$1
wp() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
doc=""
cfgs=$(find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null)
while IFS= read -r cfg; do
  [ -n "$cfg" ] || continue
  candidate=$(dirname "$cfg")
  prefix=$(wp --path="$candidate" config get table_prefix --type=variable 2>/dev/null) || continue
  [[ "$prefix" =~ ^[A-Za-z0-9_]+$ ]] || continue
  origin=$(wp --path="$candidate" db query "SELECT option_value FROM ${prefix}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  [ "$origin" = http://thaisolitude.com ] || continue
  [ -z "$doc" ] || { echo "COLD source identity ambiguous" >&2; exit 1; }
  doc=$(cd -- "$candidate" && pwd -P)
done <<< "$cfgs"
[ -n "$doc" ] || { echo "COLD raw source missing" >&2; exit 1; }
case "$part" in
  sql)
    backup="$HOME/private-backups/live-before-peek-release-20261111-162615-74851.sql"
    [ -f "$backup" ] && [ ! -L "$backup" ] || exit 1
    physical=$(realpath -- "$backup")
    case "$physical" in "$doc"|"$doc"/*) exit 1 ;; esac
    [ "$(stat -c%s "$backup")" = 3919398 ] || exit 1
    digest=$(sha256sum -- "$backup"); digest=${digest%% *}
    [ "$digest" = 4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0 ] || exit 1
    cat -- "$backup" ;;
  journal)
    backup="$HOME/private-backups/peek-release-before-20261111-162615-e00022b6.json"
    [ -f "$backup" ] && [ ! -L "$backup" ] || exit 1
    physical=$(realpath -- "$backup")
    case "$physical" in "$doc"|"$doc"/*) exit 1 ;; esac
    [ "$(stat -c%s "$backup")" = 1682009 ] || exit 1
    digest=$(sha256sum -- "$backup"); digest=${digest%% *}
    [ "$digest" = 4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b ] || exit 1
    cat -- "$backup" ;;
  current-sql) wp --path="$doc" db export - --single-transaction --skip-lock-tables --quiet ;;
  files)
    tar -C "$doc" -cf - --exclude=./wp-config.php --exclude=./.htaccess --exclude=./php_errorlog \
      --exclude='*.log' --exclude='*.sql' --exclude='*.sql.gz' --exclude='*.zip' --exclude='*.tar.gz' \
      --exclude=./wp-content/cache --exclude=./wp-content/upgrade --exclude='./wp-content/wpvivid*' \
      --exclude=./wp-content/backups --exclude=./wp-content/upgrade-temp-backup --exclude=./wp-content/debug.log . ;;
  *) exit 1 ;;
esac
EOS
remote() { ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$1" <<< "$REMOTE"; }

echo "LIVE30 $proof_mode copy: server reads only; raw URLs preserved"
remote sql > "$work/db.sql" || invalid "fixed backup read failed"
[ "$(stat -c%s "$work/db.sql")" = 3919398 ] || invalid "SQL size differs"
read -r backup_sha _ < <(sha256sum -- "$work/db.sql")
[ "$backup_sha" = 4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0 ] || invalid "SQL digest differs"
echo "COLD-SQL bytes=3919398 sha256=$backup_sha"
input_sql="$work/db.sql"; private_inside=/backups; expected_snapshot=""
if [ "$proof_mode" = recovery ]; then
  private_inside=/home/customer/private-backups
  expected_snapshot=4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b
  cp -- "$work/db.sql" "$bk/live-before-peek-release-20261111-162615-74851.sql" || invalid "private original SQL copy failed"
  remote journal > "$bk/peek-release-before-20261111-162615-e00022b6.json" || invalid "fixed journal read failed"
  chmod 600 "$bk/live-before-peek-release-20261111-162615-74851.sql" "$bk/peek-release-before-20261111-162615-e00022b6.json" || invalid "private input permissions failed"
  python3 -I -B "$here/recovery-copy-evidence.py" prepare "$here/incident-live30.json" "$here/incident-live30-binding.json" \
    "$bk/peek-release-before-20261111-162615-e00022b6.json" > "$out/recovery-context.json" || invalid "pinned incident context differs"
  remote current-sql > "$work/current.sql" || invalid "read-only current SQL stream failed"
  input_sql="$work/current.sql"
  [ "$(stat -c%s "$input_sql")" -gt 1000 ] || invalid "current SQL stream is empty"
  read -r current_sql_sha _ < <(sha256sum -- "$input_sql")
  echo "RECOVERY-CURRENT-SQL bytes=$(stat -c%s "$input_sql") sha256=$current_sql_sha"
fi
remote files | tar --no-same-owner -C "$pub" -xf - || invalid "current file copy failed"
[ -f "$pub/wp-includes/version.php" ] && [ ! -e "$pub/wp-config.php" ] || invalid "file copy prerequisites differ"
python3 -I -B - "$pub" "$here/manifest.json" > "$out/source-binding.log" <<'PY' || invalid "copied plugin source differs"
import hashlib,json,pathlib,sys
pub=pathlib.Path(sys.argv[1]).resolve(); manifest=json.loads(pathlib.Path(sys.argv[2]).read_text())
spec=manifest['spectra']; directory=pub/'wp-content/plugins/ultimate-addons-for-gutenberg'
if directory.is_symlink() or not directory.resolve().is_relative_to(pub):raise SystemExit('FAIL copied plugin directory')
for name,expected in spec['files'].items():
    file=directory/name
    if file.is_symlink() or not file.is_file() or not file.resolve().is_relative_to(directory.resolve()):raise SystemExit('FAIL copied source path')
    actual=hashlib.sha256(file.read_bytes()).hexdigest()
    if actual!=expected:raise SystemExit('FAIL copied source hash')
    print('COLD-SOURCE '+name+' '+actual)
PY
cat "$out/source-binding.log" || invalid "source evidence unreadable"
chmod -R a+rwX "$pub" || invalid "copy file permissions unavailable"
cat > "$pub/.htaccess" <<'HT'
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
HT
cat > "$pub/wp-config.php" <<'PHP'
<?php
define('DB_NAME','wp'); define('DB_USER','wp'); define('DB_PASSWORD','wp'); define('DB_HOST','db');
define('DB_CHARSET','utf8mb4'); define('DB_COLLATE',''); $table_prefix='iej_';
define('WP_ENVIRONMENT_TYPE','local'); define('DISABLE_WP_CRON',true);
define('WP_HTTP_BLOCK_EXTERNAL',true); define('FS_METHOD','direct');
define('WP_DEBUG',true); define('WP_DEBUG_DISPLAY',false);
define('WP_DEBUG_LOG','/var/www/html/wp-content/cold-proof-debug.log');
if(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/'); require_once ABSPATH.'wp-settings.php';
PHP
chmod 644 "$pub/wp-config.php" "$pub/.htaccess" || invalid "local configuration unavailable"
cat > "$work/compose.yml" <<YML
services:
  db:
    image: $db_img
    environment: { MARIADB_ROOT_PASSWORD: rehearsal, MARIADB_DATABASE: wp, MARIADB_USER: wp, MARIADB_PASSWORD: wp }
  wordpress:
    image: $wp_img
    depends_on: [db]
    volumes: ["$pub:/var/www/html"]
  wpcli:
    image: $cli_img
    depends_on: [db]
    volumes: ["$pub:/var/www/html", "$wwm:/wwm:ro", "$bk:$private_inside"]
    environment: { WWM_BACKUP_DIR: "$private_inside", TS_PEEK_RELEASE_PROFILE: raw-live-v1, TS_PEEK_RELEASE_EXPECT_SNAPSHOT_SHA256: "$expected_snapshot" }
networks:
  default:
    internal: true
YML
dc up -d db >/dev/null 2>&1 || invalid "isolated database startup failed"
ready=0
for _ in $(seq 1 60); do
  if dc exec -T db mariadb -uroot -prehearsal -e 'SELECT 1' >/dev/null 2>&1; then ready=1; break; fi
  sleep 1
done
[ "$ready" = 1 ] || invalid "database readiness failed"
db_container=$(dc ps -q db) || invalid "database container lookup failed"
[[ "$db_container" =~ ^[a-f0-9]{64}$ ]] || invalid "database container identity ambiguous"
db_network=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.NetworkID}}{{end}}' "$db_container") || invalid "database network lookup failed"
[[ "$db_network" =~ ^[a-f0-9]{64}$ ]] || invalid "database network identity ambiguous"
[ "$(docker network inspect --format '{{.Internal}}' "$db_network")" = true ] || invalid "copy network is not internal before bootstrap"
# Original SQL remains byte-exact and pinned. Only known CREATE TABLE DDL
# collation syntax may adapt for pinned MariaDB; INSERT/data bytes stay exact.
import_args=(import-sql "$input_sql" "$out/import-receipt.json")
[ "$proof_mode" != recovery ] || import_args=(import-current-sql "$input_sql" "$current_sql_sha" "$out/import-receipt.json")
python3 -I -B "$here/cold-proof-io.py" "${import_args[@]}" |
  dc exec -T db mariadb -uroot -prehearsal --default-character-set=utf8mb4 wp >/dev/null 2>&1
status=("${PIPESTATUS[@]}")
[ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] || invalid "SHA-bound SQL import failed"
cat "$out/import-receipt.json" || invalid "import receipt unavailable"
sql() { dc exec -T db mariadb -uroot -prehearsal --default-character-set=utf8mb4 -N -B --raw wp; }
observe() {
  local label=$1 status
  python3 -I -B "$here/cold-cache-evidence.py" sql iej_ "$here/manifest.json" "$here/live-profile.json" |
    sql |
    python3 -I -B "$here/cold-cache-evidence.py" capture "$label" "$here/manifest.json" "$here/live-profile.json" > "$out/$label.json"
  status=("${PIPESTATUS[@]}")
  [ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] && [ "${status[2]}" -eq 0 ] || fail "complete SQL observation failed"
}
compare() {
  python3 -I -B "$here/cold-cache-evidence.py" compare "$out/$1.json" "$out/$2.json" "$3" |
    tee -a "$out/proof.log"
  local status=("${PIPESTATUS[@]}")
  [ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] || fail "state comparison failed"
}
start_web() {
# No readiness GET is allowed: it would conceal the first cold page request.
dc up -d wordpress >/dev/null 2>&1 || invalid "isolated web startup failed"
container=$(dc ps -q wordpress) || invalid "web container lookup failed"
[[ "$container" =~ ^[a-f0-9]{64}$ ]] || invalid "web container identity ambiguous"
network=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.NetworkID}}{{end}}' "$container") || invalid "network lookup failed"
[[ "$network" =~ ^[a-f0-9]{64}$ ]] || invalid "web network identity ambiguous"
[ "$network" = "$db_network" ] || invalid "web and database isolation networks differ"
[ "$(docker network inspect --format '{{.Internal}}' "$network")" = true ] || invalid "copy network is not internal"
backend=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' "$container") || invalid "web address unavailable"
python3 -I -B "$ci/rehearsal-loopback.py" "$backend" > "$work/loopback.log" 2>&1 &
release_relay_pid=$!
ready=0
for _ in $(seq 1 30); do
  kill -0 "$release_relay_pid" 2>/dev/null || break
  if grep -qxF "READY rehearsal loopback 127.0.0.1:8090 -> $backend:80" "$work/loopback.log"; then ready=1; break; fi
  sleep 1
done
[ "$ready" = 1 ] || invalid "owned loopback relay unavailable"
# Check TCP only; do not send an HTTP request during readiness.
python3 -I -B - "$backend" <<'PY' || invalid "web TCP readiness failed"
import socket,sys,time
for _ in range(30):
    try:
        with socket.create_connection((sys.argv[1],80),timeout=1):pass
        break
    except OSError:time.sleep(1)
else:raise SystemExit(1)
PY
}

if [ "$proof_mode" = recovery ]; then
  recovery_context="$out/recovery-context.json"
  recovery_observe() {
    local label=$1 status
    python3 -I -B "$here/recovery-copy-evidence.py" sql iej_ "$recovery_context" |
      sql |
      python3 -I -B "$here/recovery-copy-evidence.py" capture "$label" "$recovery_context" > "$out/$label.json"
    status=("${PIPESTATUS[@]}")
    [ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] && [ "${status[2]}" -eq 0 ] || fail "complete recovery cache observation failed"
  }
  recovery_render() {
    local which=$1 route expected code curl_rc output render_rc count=0
    python3 -I -B - "$here/manifest.json" > "$work/recovery-routes.txt" <<'PY' || fail "fixed response inventory unavailable"
import json,sys
manifest=json.load(open(sys.argv[1]))
for page in manifest['pages']:print(page['path'])
print('/?s=massage')
print('/peek-release-not-a-real-page/')
PY
    [ "$(wc -l < "$work/recovery-routes.txt")" = 17 ] || fail "response inventory count differs"
    while IFS= read -r route; do
      sleep 6
      expected=200; [ "$route" != /peek-release-not-a-real-page/ ] || expected=404
      code=$(curl --noproxy '*' --proto '=http' --max-redirs 0 -sS --max-time 30 \
        -H 'Host: thaisolitude.com' -o "$bk/recovery-response.private.html" -w '%{http_code}' "http://127.0.0.1:8090$route")
      curl_rc=$?
      [ "$curl_rc" -eq 0 ] && [ "$code" = "$expected" ] || fail "isolated response status differs"
      output=$(dc run --rm -T --no-deps --user 0 --entrypoint php wpcli \
        /wwm/link-ideas/peek-release/verify-render.php "$private_inside/recovery-response.private.html" \
        "$private_inside/peek-release-before-20261111-162615-e00022b6.json" "$which" "$route" \
        2> "$work/render.private.stderr")
      render_rc=$?
      [ "$render_rc" -eq 0 ] && [ "$output" = "Exact public response verified: $which $route" ] || fail "isolated response verifier failed"
      echo "RECOVERY-RENDER $which $route HTTP=$code exact=true" | tee -a "$out/render-$which.log" || fail "render evidence retention failed"
      count=$((count + 1))
    done < "$work/recovery-routes.txt"
    [ "$count" = 17 ] || fail "response verification incomplete"
    rm -f -- "$bk/recovery-response.private.html" || fail "transient response removal failed"
  }
  recovery_observe before-bootstrap
  for step in verify-applied verify-applied-after-render recoverable rollback verify-restored-after-render recovery-restored verify-restored; do
    if [ "$step" = verify-applied-after-render ]; then start_web; recovery_render applied; fi
    if [ "$step" = verify-restored-after-render ]; then recovery_render original; fi
    case "$step" in
      verify-applied|verify-applied-after-render) mode_args=(verify-incident) ;;
      recoverable) mode_args=(recovery-check-incident) ;;
      rollback) mode_args=(rollback-incident) ;;
      recovery-restored) mode_args=(recovery-check-incident) ;;
      verify-restored|verify-restored-after-render) mode_args=(verify-incident) ;;
    esac
    mode_args+=("$private_inside/peek-release-before-20261111-162615-e00022b6.json")
    case "$step" in recovery-restored|verify-restored|verify-restored-after-render) mode_args+=(restored) ;; esac
    # Root is confined to this isolated copy and reads the host-owned 0600
    # inputs directly. It does not change their ownership or original bytes.
    dc run --rm -T --no-deps --user 0 --entrypoint wp wpcli --allow-root --skip-plugins=sg-security --user=1 \
      eval-file /wwm/link-ideas/peek-release/install-peek-release.php "${mode_args[@]}" \
      > "$work/$step.private.stdout" 2> "$work/$step.private.stderr"
    mode_rc=$?
    python3 -I -B "$here/recovery-copy-evidence.py" marker "$step" "$recovery_context" \
      < "$work/$step.private.stdout" > "$out/$step.marker.json"
    marker_rc=$?
    echo "RECOVERY-STEP $step wp_exit=$mode_rc marker_exit=$marker_rc"
    read -r mode_stderr_sha _ < <(sha256sum -- "$work/$step.private.stderr")
    echo "RECOVERY-STDERR $step bytes=$(stat -c%s "$work/$step.private.stderr") sha256=$mode_stderr_sha"
    [ "$mode_rc" -eq 0 ] && [ "$marker_rc" -eq 0 ] || fail "isolated recovery mode or exact marker failed"
    recovery_observe "after-$step"
  done
  python3 -I -B "$here/recovery-copy-evidence.py" verify "$recovery_context" "$out" | tee -a "$out/proof.log"
  status=("${PIPESTATUS[@]}")
  [ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] || fail "complete actual-state recovery receipt failed"
  read -r restored_journal_sha _ < <(sha256sum -- "$bk/peek-release-before-20261111-162615-e00022b6.json")
  read -r restored_sql_sha _ < <(sha256sum -- "$bk/live-before-peek-release-20261111-162615-74851.sql")
  [ "$restored_journal_sha" = "$expected_snapshot" ] && [ "$restored_sql_sha" = "$backup_sha" ] || fail "private recovery input changed"
  [ "$(stat -c%a "$bk/peek-release-before-20261111-162615-e00022b6.json")" = 600 ] && \
    [ "$(stat -c%a "$bk/live-before-peek-release-20261111-162615-74851.sql")" = 600 ] || fail "private recovery permissions changed"
  echo "RECOVERY-PRIVATE-INPUTS unchanged journal_sha256=$restored_journal_sha sql_sha256=$restored_sql_sha mode=0600"
  echo 'RECOVERY COPY PASSED: actual current IDs and immutable journal, exact incident verification/recovery/rollback, retained external cache rows; server was read-only'
  exit 0
fi
observe cold-import
echo 'COLD step: SQL baseline captured before any WordPress bootstrap'
dc run --rm -T --no-deps --entrypoint wp wpcli --skip-plugins=sg-security --user=1 \
  eval-file /wwm/link-ideas/peek-release/install-peek-release.php preflight \
  > "$work/bootstrap.private.stdout" 2> "$work/bootstrap.private.stderr"
bootstrap_rc=$?
python3 -I -B "$here/cold-proof-io.py" bootstrap "$work/bootstrap.private.stdout" "$work/bootstrap.private.stderr" \
  "$bootstrap_rc" "$here/manifest.json" "$here/live-profile.json" > "$out/bootstrap.log"
receipt_rc=$?
cat "$out/bootstrap.log" || fail "bootstrap receipt unreadable"
[ "$bootstrap_rc" -eq 0 ] || fail "explicit raw-profile preflight bootstrap failed"
[ "$receipt_rc" -eq 0 ] || fail "explicit bootstrap receipt invalid"
observe after-bootstrap
compare cold-import after-bootstrap exact

start_web

previous=after-bootstrap
for pass in first repeat; do
  for pair in '1254 /personal-automation/' '1255 /personal-automation/privacy/' '1257 /personal-automation/terms/'; do
    read -r id path <<< "$pair"
    sleep 6
    code=$(curl --noproxy '*' --proto '=http' --max-redirs 0 -sS --max-time 30 \
      -H 'Host: thaisolitude.com' -o /dev/null -w '%{http_code}' "http://127.0.0.1:8090$path")
    curl_rc=$?
    echo "COLD-GET pass=$pass post=$id HTTP=$code curl_exit=$curl_rc"
    [ "$curl_rc" -eq 0 ] && [ "$code" = 200 ] || fail "cold route did not answer directly with HTTP200"
    label="$pass-$id"
    observe "$label"
    mode=exact; [ "$pass" != first ] || mode="cold-$id"
    compare "$previous" "$label" "$mode"
    previous=$label
  done
done
python3 -I -B "$here/cold-cache-evidence.py" verify-final "$out/cold-import.json" "$out/$previous.json" \
  "$here/manifest.json" "$here/live-profile.json" | tee -a "$out/proof.log"
status=("${PIPESTATUS[@]}")
[ "${status[0]}" -eq 0 ] && [ "${status[1]}" -eq 0 ] || fail "final cold-cache causal receipt failed"
echo 'COLD PROOF PASSED: raw pre-apply copy, explicit bootstrap, three cold GETs and three stable repeats; live was read-only'

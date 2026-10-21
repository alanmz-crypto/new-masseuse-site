#!/usr/bin/env bash
# Use the running localhost stack, never a second copy of the runtime files.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
runtime=${RPA_RUNTIME:?Set RPA_RUNTIME to the existing WordPress runtime checkout}
mode=${1:-preflight}
case "${RPA_RELEASE:-base}" in base) installer=install.php ;; terms) installer=install-terms.php ;; *) echo 'RPA_RELEASE must be base or terms'; exit 1 ;; esac
case "$mode" in preflight|apply|rollback) ;; *) echo 'Use preflight, apply or rollback MANIFEST'; exit 1 ;; esac
private="$runtime/backups/personal-automation"
mkdir -p "$private"
chmod 700 "$private"
dc=(docker compose -f "$runtime/docker-compose.yml" run --rm --user "$(id -u):$(id -g)" -v "$here:/rpa:ro" -v "$private:/private")
dump=""
if [[ "$mode" != preflight ]]; then
  dump="/private/local-before-personal-automation-$(date +%Y%m%d-%H%M%S).sql"
  (umask 077; docker compose -f "$runtime/docker-compose.yml" exec -T db sh -c \
    'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mariadb-dump -uroot --single-transaction "$MYSQL_DATABASE"' > "$private/$(basename "$dump")")
  chmod 600 "$private/$(basename "$dump")"
fi
"${dc[@]}" -e RPA_BACKUP_DIR=/private -e "RPA_DB_BACKUP=$dump" wpcli wp --user=1 eval-file "/rpa/$installer" "$mode" "${2:-}"
# ref: ./install.php, ../../docker-compose.yml — local installer and runtime container configuration

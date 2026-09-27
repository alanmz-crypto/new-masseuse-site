#!/usr/bin/env bash
# On-demand backup of the LOCAL new-masseuse-site stack (:8090).
#
# Method (best-tool + combined, per your ask):
#   - restic  -> stores public_html FILES with dedup/incremental snapshots
#               (ideal for repeated backups while editing — only changes are stored)
#   - mysqldump | restic --stdin -> a CONSISTENT logical DB snapshot streamed
#               into the same restic repo (never snapshots the live /var/lib/mysql
#               volume directly, which could be torn/inconsistent)
#
# Everything stays LOCAL. Nothing is sent to SiteGround, WordPress, or any git
# remote. The restic repo lives in this folder under backups/restic-repo.
#
# Usage:
#   ./scripts/backup-site.sh                 # snapshot files + DB now
#   ./scripts/backup-site.sh --note wip      # add a tag to the snapshots
#   ./scripts/backup-site.sh --list          # list snapshots
#   ./scripts/backup-site.sh --files-only    # skip the DB
#   ./scripts/backup-site.sh --db-only       # skip the files
#   KEEP=10 ./scripts/backup-site.sh         # keep last 10 of each (default 10)
#
# Restore examples (see --help):
#   restic -r backups/restic-repo restore latest --target /tmp/restore --tag files
#   restic -r backups/restic-repo dump latest new_masseuse_db.sql --tag db \
#     | docker exec -i new-masseuse-db-1 mysql -u root -pnewrootpassword new_masseuse_db
set -euo pipefail

SITE_DIR="${SITE_DIR:-$HOME/WordPress/new-masseuse-site}"
cd "$SITE_DIR"

# --- restic repo + password (local, self-contained) ------------------------
export RESTIC_REPOSITORY="${MASSEUSE_RESTIC_REPO:-$SITE_DIR/backups/restic-repo}"
PW_FILE="${MASSEUSE_RESTIC_PASSWORD_FILE:-$SITE_DIR/backups/restic.password}"
export RESTIC_PASSWORD_FILE="$PW_FILE"

DB_CONTAINER="${MASSEUSE_DB_CONTAINER:-new-masseuse-db-1}"
DB_NAME="${MASSEUSE_DB_NAME:-new_masseuse_db}"
DB_ROOT_PW="${MASSEUSE_DB_ROOT_PW:-newrootpassword}"
KEEP="${KEEP:-10}"

NOTE=""
DO_FILES=1
DO_DB=1

usage() { sed -n '2,30p' "$0" | sed 's/^# \{0,1\}//'; }

while [[ $# -gt 0 ]]; do
  case "$1" in
    --note) NOTE="${2:?--note requires a value}"; shift 2 ;;
    --files-only) DO_DB=0; shift ;;
    --db-only) DO_FILES=0; shift ;;
    --list)
      if restic cat config >/dev/null 2>&1; then
        restic snapshots --compact
      else
        echo "No restic repo yet at $RESTIC_REPOSITORY (run a backup first)."
      fi
      exit 0
      ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown argument: $1" >&2; usage >&2; exit 1 ;;
  esac
done

mkdir -p "$SITE_DIR/backups"

# --- Ensure a local password + initialised repo (one-time, automatic) -------
if [[ ! -f "$PW_FILE" ]]; then
  echo "[init] Generating local restic password -> $PW_FILE"
  umask 077
  openssl rand -base64 32 > "$PW_FILE"
  chmod 600 "$PW_FILE"
fi
if ! restic cat config >/dev/null 2>&1; then
  echo "[init] Initialising local restic repo -> $RESTIC_REPOSITORY"
  restic init >/dev/null
fi

tags=(--tag new-masseuse-site)
[[ -n "$NOTE" ]] && tags+=(--tag "note:$NOTE")

echo "=============================================="
echo " new-masseuse-site backup (LOCAL restic)"
echo " Repo: $RESTIC_REPOSITORY"
echo "=============================================="

# --- Files via restic (dedup/incremental) ----------------------------------
if [[ "$DO_FILES" -eq 1 ]]; then
  if [[ -d "$SITE_DIR/public_html" ]]; then
    echo "[files] restic backup public_html ..."
    restic backup "${tags[@]}" --tag files \
      --exclude 'php_errorlog' \
      "$SITE_DIR/public_html"
  else
    echo "[files] Skipping — no public_html directory."
  fi
fi

# --- Database via mysqldump | restic --stdin (consistent snapshot) ----------
if [[ "$DO_DB" -eq 1 ]]; then
  if docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
    echo "[db] mysqldump '$DB_NAME' | restic --stdin ..."
    if docker exec "$DB_CONTAINER" mysqldump -u root -p"$DB_ROOT_PW" \
         --single-transaction --no-tablespaces "$DB_NAME" 2>/dev/null \
         | restic backup "${tags[@]}" --tag db \
             --stdin --stdin-filename "$DB_NAME.sql"; then
      :
    else
      echo "[db] WARN: database dump/backup failed." >&2
    fi
  else
    echo "[db] Skipping — container '$DB_CONTAINER' not running."
    echo "     Start the stack first (docker compose up -d)."
  fi
fi

# --- Retention: keep last N per tag group -----------------------------------
if [[ "$KEEP" -gt 0 ]]; then
  echo "[prune] Keeping last $KEEP snapshots per group ..."
  restic forget --keep-last "$KEEP" --group-by 'tags,paths' --prune >/dev/null 2>&1 \
    || echo "[prune] (nothing to prune yet)"
fi

echo ""
echo "Done. List with: ./scripts/backup-site.sh --list"
echo "Restore help:    ./scripts/backup-site.sh --help"

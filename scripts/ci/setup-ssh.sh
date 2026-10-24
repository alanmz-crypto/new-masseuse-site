#!/usr/bin/env bash
# GitHub Actions only: give this runner the same SiteGround access the tools expect on Ryan's computer, built from environment secrets.
#
#   SG_HOST, SG_PORT, SG_USER   -> a credentials file in the runner's temp folder, in the format of the gitignored remote_execution.txt
#   SG_SSH_KEY                  -> an ssh-agent holding the GitHub-only key (its public half is imported in SiteGround's SSH Keys Manager)
#   SG_KNOWN_HOSTS              -> the server's host key, pinned: a changed key is refused instead of accepted
#
# Exports S26_CREDS and SSH_AUTH_SOCK to later steps through $GITHUB_ENV (every push/test script reads those two), plus the settings that
# make sync-server-backups.sh copy only this run's new backups and store them encrypted outside the runner (offsite-store.sh).
# Prints no secret value. Exit 2 = the server refused the key (import the public key in SiteGround first, see CLOUD-SETUP.md).
set -euo pipefail

: "${RUNNER_TEMP:?this script runs in GitHub Actions only}" "${GITHUB_ENV:?}" "${GITHUB_WORKSPACE:?}"
for v in SG_HOST SG_PORT SG_USER SG_SSH_KEY SG_KNOWN_HOSTS; do
  [ -n "${!v:-}" ] || { echo "Missing secret $v in this GitHub environment (see CLOUD-SETUP.md)." >&2; exit 1; }
done
case "$SG_PORT" in ''|*[!0-9]*) echo "SG_PORT must be a number." >&2; exit 1 ;; esac
user="${SG_USER%%@*}"   # the same correction the tools make for remote_execution.txt's User: line

d="$RUNNER_TEMP/sg"; mkdir -p "$d"; chmod 700 "$d"
( umask 077; printf 'Host: %s\nPort: %s\nUser: %s\n' "$SG_HOST" "$SG_PORT" "$user" > "$d/remote_execution.txt" )

mkdir -p "$HOME/.ssh"; chmod 700 "$HOME/.ssh"
( umask 077; printf '%s\n' "$SG_KNOWN_HOSTS" >> "$HOME/.ssh/known_hosts" )
# The tools connect with StrictHostKeyChecking=accept-new, which refuses a CHANGED key but would accept an unknown one, so the pin must match.
ssh-keygen -F "[$SG_HOST]:$SG_PORT" -f "$HOME/.ssh/known_hosts" >/dev/null \
  || { echo "SG_KNOWN_HOSTS has no entry for [SG_HOST]:SG_PORT; the host key would not be pinned." >&2; exit 1; }

sock="$d/agent.sock"
eval "$(ssh-agent -a "$sock" -s)" >/dev/null
( umask 077; printf '%s\n' "$SG_SSH_KEY" > "$d/key" )
if ! SSH_AUTH_SOCK="$sock" ssh-add "$d/key" >/dev/null 2>&1; then rm -f "$d/key"; echo "SG_SSH_KEY could not be loaded (not a private key without a passphrase?)." >&2; exit 1; fi
rm -f "$d/key"

{
  echo "S26_CREDS=$d/remote_execution.txt"
  echo "SSH_AUTH_SOCK=$sock"
  echo "TS_SERVER_BACKUPS=$RUNNER_TEMP/server-backups"   # where sync-server-backups.sh puts its verified copies (plaintext, this runner only)
  echo "TS_SYNC_MAX_AGE_MIN=240"                         # a fresh runner holds no earlier copies: copy and verify only the last 4 hours
  echo "TS_OFFSITE_HOOK=$GITHUB_WORKSPACE/scripts/ci/offsite-store.sh"   # then encrypt and store each copy outside the runner, or stop
} >> "$GITHUB_ENV"

if SSH_AUTH_SOCK="$sock" ssh -p "$SG_PORT" -o BatchMode=yes -o ConnectTimeout=20 "$user@$SG_HOST" true 2>/dev/null; then
  echo "SSH to SiteGround: OK (host key pinned, GitHub-only key loaded)"
else
  echo "SSH to SiteGround: REFUSED. Has the GitHub key's public half been imported in Site Tools > Devs > SSH Keys Manager?" >&2; exit 2
fi

# ref: ../work-with-me-employer/sync-server-backups.sh, ./offsite-store.sh — the backup copy this configures
# ref: ../../CLOUD-SETUP.md — the secrets this reads and how they were made

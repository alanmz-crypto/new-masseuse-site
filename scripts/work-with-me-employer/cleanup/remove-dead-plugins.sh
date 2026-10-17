#!/usr/bin/env bash
# Remove dead (inactive, unused) plugin code from ONE site: autoptimize 3.1.16, siteground-migrator 2.1.2, and autoptimize's leftover
# wp-content/autoptimize_404_handler.php. Inactive plugin code is still reachable by URL and is a common way in once it goes out of date.
# Allow-list: staging31 and live. Live was added on Ryan's second go ("can you proceed?", 2026-10-17) after staging31 was applied, verified and rolled back/re-applied.
# Ryan, 2026-10-17: "you're the expert. proceed." (attack-surface cleanup #5; rehearsal on staging31 first). The apply itself is run by Ryan (`!`).
#
#   ./remove-dead-plugins.sh                        dry run: connect, locate the site, report the state (read-only on the site)
#   ./remove-dead-plugins.sh --apply                DB export + a tarball of exactly the files to be removed + manifest (all copied OFF the server and
#                                                   verified) -> remove -> verify -> purge cache. If the check after removal fails it restores the files itself.
#   ./remove-dead-plugins.sh --rollback TARBALL     put the files back from a *-dead-plugins-before-*.tar.gz on the server, then verify the file fingerprint
#
# Guards (on the server, every time): the site is found by `wp option get home`, never by folder name; each plugin must be present, INACTIVE and at the
# exact reviewed version, and the drop-in must not be referenced by .htaccess, wp-config.php or the cache folder; otherwise it stops and changes nothing.
# Rollback restores the FILES exactly (checked by a sha256 fingerprint). The plugins' own settings (autoptimize_* options) are removed by its uninstall
# routine and come back only from the SQL export or the options JSON written next to the tarball; both plugins were inactive and unused.
# After a push, test the site: scripts/work-with-me-employer/post-change-test.sh <target>.
# Access: credentials file + ssh-agent (SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock). The SSH account also holds live.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
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
target="${S_TARGET:-staging31}"
case "$target" in staging31|live) ;; *) echo "S_TARGET must be staging31 or live." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; arg=${3:-}
case "$target" in staging31) want="staging31.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging31 or live, got '$target'." >&2; exit 1 ;; esac
command -v wp >/dev/null || { echo "wp-cli not found on the server" >&2; exit 1; }
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
echo "$target is ${doc#"$HOME"/}"
cd "$HOME"
pv="$HOME/private-backups"; mkdir -p "$pv"; chmod 700 "$pv"
PLUGINS="autoptimize:3.1.16 siteground-migrator:2.1.2"
DROPIN="wp-content/autoptimize_404_handler.php"
ITEMS="wp-content/plugins/autoptimize wp-content/plugins/siteground-migrator $DROPIN"
W() { wp --path="$doc" --skip-plugins --skip-themes "$@"; }

# state: prints "ready" (all three present and as reviewed), "applied" (all gone), or "CHANGED: why" (anything else: stop and review)
state() {
  present=0; absent=0; bad=""
  for pvp in $PLUGINS; do
    p=${pvp%%:*}; v=${pvp##*:}
    if [ -d "$doc/wp-content/plugins/$p" ]; then
      st=$(W plugin get "$p" --field=status 2>/dev/null || echo unknown); ver=$(W plugin get "$p" --field=version 2>/dev/null || echo unknown)
      if [ "$st" = inactive ] && [ "$ver" = "$v" ]; then present=$((present+1)); else bad="$bad $p(status=$st,version=$ver)"; fi
    else absent=$((absent+1)); fi
  done
  if [ -f "$doc/$DROPIN" ]; then
    refs=$(grep -l "autoptimize_404_handler" "$doc/.htaccess" "$doc/wp-config.php" 2>/dev/null; grep -rl "autoptimize_404_handler" "$doc/wp-content/cache" 2>/dev/null || true)
    if [ -n "$refs" ]; then bad="$bad drop-in-referenced-by($(echo "$refs" | tr '\n' ' '))"; else present=$((present+1)); fi
  else absent=$((absent+1)); fi
  if [ -n "$bad" ]; then echo "CHANGED:$bad"
  elif [ "$absent" -eq 0 ]; then echo ready
  elif [ "$present" -eq 0 ]; then echo applied
  else echo "CHANGED: only some of the three items are present"; fi
}
# fingerprint of the exact file tree of the three items (path + sha256 of every file)
fp() { ( cd "$doc" && find $ITEMS -type f 2>/dev/null | LC_ALL=C sort | xargs -r sha256sum | sha256sum | cut -d' ' -f1 ); }
nfiles() { ( cd "$doc" && find $ITEMS -type f 2>/dev/null | wc -l ); }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
restore() { # restore TARBALL: put the files back and verify the fingerprint recorded at backup time
  tb=$1; man="${tb%.tar.gz}.manifest.json"
  [ -s "$tb" ] && [ -s "$man" ] || { echo "Tarball or manifest missing: $tb" >&2; return 1; }
  tar -xzf "$tb" -C "$doc"
  want_fp=$(sed -n 's/.*"fingerprint":"\([0-9a-f]*\)".*/\1/p' "$man")
  [ -n "$want_fp" ] && [ "$(fp)" = "$want_fp" ] || { echo "Restored files do NOT match the recorded fingerprint." >&2; return 1; }
  echo "files restored; fingerprint matches the backup ($(nfiles) files)"
}

case "$phase" in
  preflight)
    s=$(state); echo "host=http://$want items: $(nfiles) files  state=$s"
    for pvp in $PLUGINS; do p=${pvp%%:*}; [ -d "$doc/wp-content/plugins/$p" ] && echo "  $p: $(W plugin get "$p" --field=status 2>/dev/null) $(W plugin get "$p" --field=version 2>/dev/null), $(du -sk "$doc/wp-content/plugins/$p" | cut -f1) KB"; done
    [ "${s%%:*}" != CHANGED ] || { echo "The site is not in the reviewed state; nothing was changed." >&2; exit 1; } ;;
  prepare)
    s=$(state); [ "$s" = ready ] || { echo "State is '$s', not 'ready'; stopping before any change." >&2; exit 1; }
    ts=$(date +%Y%m%d-%H%M%S)
    dump="$pv/${target}-before-dead-plugins-$ts.sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    tb="$pv/${target}-dead-plugins-before-$ts.tar.gz"
    ( umask 077; tar -czf "$tb" -C "$doc" $ITEMS )
    [ "$(tar -tzf "$tb" | grep -vc '/$')" -eq "$(nfiles)" ] || { echo "Tarball does not hold every file; stopping before any change." >&2; exit 1; }
    ( umask 077; W option list --search='autoptimize*' --format=json > "$pv/${target}-dead-plugins-options-$ts.json" )
    ( umask 077; printf '{"target":"%s","created":"%s","tarball":"%s","files":%s,"fingerprint":"%s","items":"%s"}\n' "$target" "$(date -u +%FT%TZ)" "$(basename "$tb")" "$(nfiles)" "$(fp)" "$ITEMS" > "${tb%.tar.gz}.manifest.json" )
    echo "file backup: private-backups/$(basename "$tb") ($(stat -c%s "$tb") bytes, $(nfiles) files, fingerprint $(fp | cut -c1-16)...)"
    echo "PREPARED ts=$ts" ;;
  apply)
    ts=$arg; tb="$pv/${target}-dead-plugins-before-$ts.tar.gz"
    s=$(state); [ "$s" = ready ] || { echo "State is '$s', not 'ready'; nothing changed." >&2; exit 1; }
    [ -s "$tb" ] && [ -s "${tb%.tar.gz}.manifest.json" ] || { echo "No verified backup for $ts on the server; nothing changed." >&2; exit 1; }
    want_fp=$(sed -n 's/.*"fingerprint":"\([0-9a-f]*\)".*/\1/p' "${tb%.tar.gz}.manifest.json")
    [ "$(fp)" = "$want_fp" ] || { echo "The files changed since the backup was taken; nothing changed." >&2; exit 1; }
    wp --path="$doc" plugin uninstall autoptimize siteground-migrator 2>&1 | grep -E 'Uninstalled|Deleted|Success|Error|Warning' || true
    rm -f "$doc/$DROPIN"
    ok=1
    [ "$(state)" = applied ] || { echo "After removal the state is not 'applied'." >&2; ok=0; }
    [ "$(W option get home 2>/dev/null)" = "http://$want" ] || { echo "home option is not as expected after removal." >&2; ok=0; }
    wp --path="$doc" eval 'echo "wordpress-boots-ok";' 2>/dev/null | grep -q wordpress-boots-ok || { echo "WordPress does not boot after removal." >&2; ok=0; }
    if [ "$ok" -ne 1 ]; then echo "VERIFICATION FAILED: restoring the files from the backup." >&2; restore "$tb" || true; purge; exit 1; fi
    echo "removed and verified: plugins gone from disk and from the plugin list, drop-in gone, WordPress boots, home option unchanged"
    purge ;;
  rollback)
    tb=${arg/#\$HOME/$HOME}
    s=$(state); [ "$s" = applied ] || { echo "State is '$s'; rollback needs the three items to be absent. Nothing changed." >&2; exit 1; }
    restore "$tb"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[1] Preflight (read-only):"; remote preflight "$target"; echo "Dry run only. Re-run with --apply to write." ;;
  apply)
    echo "[1] Backup on the server (DB export, file tarball, manifest) + preflight:"
    out=$(remote prepare "$target"); echo "$out"
    ts=$(sed -n 's/^PREPARED ts=//p' <<<"$out" | tail -1); [ -n "$ts" ] || { echo "Backup step did not finish; nothing was changed." >&2; exit 1; }
    echo "[2] Copying the backup to this computer and verifying it (stops here if it cannot be verified):"
    "$here/../sync-server-backups.sh"
    dest="${S_BACKUP_DEST:-$HOME/WordPress/thai-solitude-server-backups}"; mkdir -p "$dest"; chmod 700 "$dest"
    t="private-backups/${target}-dead-plugins-before-$ts.tar.gz"
    want_sha=$("${ssh_cmd[@]}" "sha256sum \"\$HOME/$t\"" | cut -d' ' -f1)
    scp -q -P "$port" -o BatchMode=yes "$user@$host:$t" "$dest/$(basename "$t")"; chmod 600 "$dest/$(basename "$t")"
    if [ -n "$want_sha" ] && [ "$(sha256sum "$dest/$(basename "$t")" | cut -d' ' -f1)" = "$want_sha" ]; then
      echo "file tarball copied off-server, sha256 verified: $(basename "$t")"
    else
      echo "File tarball copy does NOT match; stopping before any change." >&2; exit 1
    fi
    echo "[3] Removing:"; remote apply "$target" "$ts" ;;
  rollback) echo "[1] Rolling back:"; remote rollback "$target" "$tarball" ;;
esac

# ref: ../headers/push-headers-csp.sh — same connection/guard/backup pattern; ../sync-server-backups.sh — off-server copy of the SQL/JSON backups
# ref: ../mirror-versions.sh — the tarball copy-and-verify step is the same as there; ../../../remote_execution.txt — gitignored access details (never printed)

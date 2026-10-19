#!/usr/bin/env bash
# Remove the database leftovers (options, user meta, transients) and public folders that a DELETED plugin left behind. Two families, each tied to one plugin folder:
#   sitekit  Google Site Kit    options/transients matching %googlesitekit%, user meta matching %googlesitekit%
#   starter  Starter Templates  options astra_sites%, zipwp%, ai_builder%; user meta astra-sites-%/%zipwp%; folders uploads/astra-sites + uploads/ai-builder are MOVED (not deleted)
# The shared Spectra design-library options (ast_block%, ast_blocks%) are NOT touched. Importer markers in post/term meta are left (tiny, harmless).
# Allow-list: live and staging31. Ryan, 2026-10-17: "go" (delete Site Kit and Starter Templates on live, then clean their leftover data).
#
#   ./remove-plugin-leftovers.sh <target> <family>                      dry run: report exactly what is there (read-only)
#   ./remove-plugin-leftovers.sh <target> <family> --apply              DB export + snapshot (+ files manifest), copied OFF the server and verified -> delete -> move folders -> verify -> purge
#   ./remove-plugin-leftovers.sh <target> <family> --rollback SNAPSHOT  put the rows (and folders) back from a <target>-<family>-leftovers-db-*.json snapshot and verify them
#
# Guards (on the server, every time): the site is found by `wp option get home`; the plugin FOLDER must be gone; the number of matching rows must be inside a sane bound; the
# snapshot is written (owner-only) and re-compared right before deleting; for the folders: nothing may reference them and no file may be younger than 2 minutes; afterwards the matching
# rows must be gone, the checksums of every moved file must match, and WordPress must still boot.
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

target="${1:-}"; family="${2:-}"
usage() { echo "Usage: $0 <live|staging31> <sitekit|starter> [--apply | --rollback SNAPSHOT]" >&2; exit 1; }
case "$target" in live|staging31) ;; *) usage ;; esac
case "$family" in sitekit|starter) ;; *) usage ;; esac
mode=dry; snapshot=""
case "${3:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; snapshot="${4:-}"; [ -n "$snapshot" ] || { echo "--rollback needs the snapshot path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $3" >&2; exit 1 ;;
esac
echo "Target site: $target   family: $family   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; family=$3; arg=${4:-}
case "$target" in live) want="thaisolitude.com" ;; staging31) want="staging31.thaisolitude.com" ;; *) echo "Remote refused: unknown target '$target'." >&2; exit 1 ;; esac
case "$family" in sitekit) plugin="google-site-kit"; folders="" ;; starter) plugin="astra-sites"; folders="uploads/astra-sites uploads/ai-builder" ;; *) echo "Remote refused: unknown family '$family'." >&2; exit 1 ;; esac
command -v python3 >/dev/null || { echo "python3 is required on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); phpf=$(mktemp); mv "$phpf" "$phpf.php"; phpf="$phpf.php"; trap 'rm -f "$cfgs" "$phpf"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/} (home host: $want)"
if [ "$phase" != report ] && [ -d "$doc/wp-content/plugins/$plugin" ]; then echo "Refusing: the plugin folder wp-content/plugins/$plugin still exists on $target. Delete the plugin first." >&2; exit 1; fi
echo "plugin folder wp-content/plugins/$plugin: $( [ -d "$doc/wp-content/plugins/$plugin" ] && echo 'STILL PRESENT (delete it first; apply will refuse)' || echo gone )"
cd "$HOME"; pv="$HOME/private-backups"; wc="$doc/wp-content"
cat > "$phpf" <<'PHP'
<?php
// modes: report FAMILY | snapshot FAMILY FILE | delete FAMILY | restore FAMILY FILE.  Touches ONLY the rows that match the family's patterns.
global $wpdb;   // NOT 'global $args': inside eval-file $args is a local of WP-CLI's command method
$args = isset( $args ) && is_array( $args ) ? $args : array();
$mode = $args[0] ?? 'report';
$fam  = $args[1] ?? '';
$file = $args[2] ?? '';
$defs = array(
	'sitekit' => array( 'opt' => array( '%googlesitekit%' ), 'um' => array( '%googlesitekit%' ), 'max_opt' => 40, 'max_um' => 30, 'ref' => '' ),
	'starter' => array( 'opt' => array( 'astra\_sites%', 'zipwp%', 'ai\_builder%' ), 'um' => array( 'astra-sites-%', '%zipwp%' ), 'max_opt' => 30, 'max_um' => 10, 'ref' => 'uploads/astra-sites' ),
);
if ( ! isset( $defs[ $fam ] ) ) { WP_CLI::error( 'unknown family' ); }
$d = $defs[ $fam ];
function wwm_like_or( $wpdb, $col, $pats ) { $parts = array(); foreach ( $pats as $p ) { $parts[] = $wpdb->prepare( "$col LIKE %s", $p ); } return '(' . implode( ' OR ', $parts ) . ')'; }
function wwm_match( $wpdb, $d ) {
	$o = $wpdb->get_results( "SELECT option_name, option_value, autoload FROM {$wpdb->options} WHERE " . wwm_like_or( $wpdb, 'option_name', $d['opt'] ) . ' ORDER BY option_name', ARRAY_A );
	$u = $wpdb->get_results( "SELECT umeta_id, user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE " . wwm_like_or( $wpdb, 'meta_key', $d['um'] ) . ' ORDER BY umeta_id', ARRAY_A );
	return array( $o, $u );
}
function wwm_snap( $wpdb, $d ) {
	list( $o, $u ) = wwm_match( $wpdb, $d );
	return array(
		'home'     => home_url(),
		'options'  => array_map( function ( $r ) { return array( 'name' => $r['option_name'], 'value_b64' => base64_encode( $r['option_value'] ), 'autoload' => $r['autoload'] ); }, $o ),
		'usermeta' => array_map( function ( $r ) { return array( 'umeta_id' => $r['umeta_id'], 'user_id' => $r['user_id'], 'meta_key' => $r['meta_key'], 'value_b64' => base64_encode( $r['meta_value'] ) ); }, $u ),
	);
}
list( $o, $u ) = wwm_match( $wpdb, $d );
$refs = 0;
if ( '' !== $d['ref'] ) {
	$like  = '%' . $wpdb->esc_like( $d['ref'] ) . '%';
	$refs  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s", $like ) );
	$refs += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) );
	$own   = array_column( $o, 'option_name' );
	foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_value LIKE %s AND option_name NOT LIKE %s", $like, '%transient%' ) ) as $n ) { if ( ! in_array( $n, $own, true ) ) { ++$refs; } }   // the family's own rows may mention the folder; anything else counts
}
if ( 'report' === $mode ) {
	echo 'options=' . count( $o ) . ' usermeta=' . count( $u ) . ' refs=' . $refs . ' within_bounds=' . ( count( $o ) <= $d['max_opt'] && count( $u ) <= $d['max_um'] ? 'yes' : 'NO' ) . "\n";
	foreach ( $o as $r ) { echo '  option ' . $r['option_name'] . ' (' . strlen( $r['option_value'] ) . " B)\n"; }
	foreach ( $u as $r ) { echo '  user meta ' . $r['meta_key'] . ' (' . strlen( $r['meta_value'] ) . " B)\n"; }
	return;
}
if ( count( $o ) > $d['max_opt'] || count( $u ) > $d['max_um'] ) { WP_CLI::error( 'More rows match than expected (' . count( $o ) . ' options, ' . count( $u ) . ' user meta); stop and review.' ); }
if ( 'snapshot' === $mode ) {
	if ( '' === $file || false === file_put_contents( $file, wp_json_encode( wwm_snap( $wpdb, $d ) ) ) ) { WP_CLI::error( 'Could not write the snapshot.' ); }
	chmod( $file, 0600 );
	$back = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $back ) || count( $back['options'] ) !== count( $o ) || count( $back['usermeta'] ) !== count( $u ) ) { WP_CLI::error( 'Snapshot does not match the live state; nothing was changed.' ); }
	echo 'snapshot written: options=' . count( $back['options'] ) . ' usermeta=' . count( $back['usermeta'] ) . "\n";
	return;
}
if ( 'delete' === $mode ) {
	foreach ( $o as $r ) { $wpdb->delete( $wpdb->options, array( 'option_name' => $r['option_name'] ) ); }
	foreach ( $u as $r ) { $wpdb->delete( $wpdb->usermeta, array( 'umeta_id' => $r['umeta_id'] ) ); }
	wp_cache_flush();
	list( $o2, $u2 ) = wwm_match( $wpdb, $d );
	if ( $o2 || $u2 ) { WP_CLI::error( 'Rows remain after the delete: ' . count( $o2 ) . ' options, ' . count( $u2 ) . ' user meta.' ); }
	echo "deleted: 0 matching options and 0 matching user-meta rows remain\n";
	return;
}
if ( 'restore' === $mode ) {
	$snap = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== home_url() ) { WP_CLI::error( 'Snapshot is missing or from another site.' ); }
	if ( $o || $u ) { WP_CLI::error( 'Matching rows already exist; resolve by hand before restoring.' ); }
	foreach ( $snap['options'] as $r ) { $wpdb->insert( $wpdb->options, array( 'option_name' => $r['name'], 'option_value' => base64_decode( $r['value_b64'] ), 'autoload' => $r['autoload'] ) ); }
	foreach ( $snap['usermeta'] as $r ) { $wpdb->insert( $wpdb->usermeta, array( 'umeta_id' => $r['umeta_id'], 'user_id' => $r['user_id'], 'meta_key' => $r['meta_key'], 'meta_value' => base64_decode( $r['value_b64'] ) ) ); }
	wp_cache_flush();
	$again = wwm_snap( $wpdb, $d );
	if ( $again['options'] !== $snap['options'] || $again['usermeta'] !== $snap['usermeta'] ) { WP_CLI::error( 'Restored data does NOT equal the snapshot; stop and inspect.' ); }
	echo "restored + verified: options and user meta equal the snapshot exactly\n";
	return;
}
WP_CLI::error( 'unknown mode' );
PHP
runf() { local m=$1; shift; wp --path="$doc" --user=1 eval-file "$phpf" "$m" "$family" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable)"; }
sha() { sha256sum "$1" | cut -d' ' -f1; }
case "$phase" in
  report) runf report ;;
  prepare)
    rep=$(runf report); printf '%s\n' "$rep" | sed -n '1p'
    case "$rep" in *"within_bounds=yes"*) ;; *) echo "Match count outside the expected bounds; stopping before any change." >&2; exit 1 ;; esac
    for f in $folders; do
      [ ! -d "$wc/$f" ] || { [ "$(printf '%s' "$rep" | sed -n '1s/.*refs=\([0-9]*\).*/\1/p')" = "0" ] || { echo "Refusing: something still references $f." >&2; exit 1; }; [ -z "$(find "$wc/$f" -type f -mmin -2 2>/dev/null | head -1)" ] || { echo "Refusing: a file in $f changed in the last 2 minutes." >&2; exit 1; }; }
    done
    ts=$(date +%Y%m%d-%H%M%S); mkdir -p "$pv"; chmod 700 "$pv"
    dump="$pv/${target}-before-${family}-leftovers-$ts.sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    runf snapshot "$pv/${target}-${family}-leftovers-db-$ts.json"
    list=""; present=""
    for f in $folders; do
      [ -d "$wc/$f" ] || continue
      present="$present $f"; rels=$(cd "$wc/$f" && find . -type f -printf '%P\n' | LC_ALL=C sort)
      while IFS= read -r rel; do [ -n "$rel" ] || continue; list="$list$f/$rel|$(sha "$wc/$f/$rel")|$(stat -c %s "$wc/$f/$rel")"$'\n'; done <<<"$rels"
    done
    SITE="$want" FOLDERS_PRESENT="$present" LIST="$list" MF="$pv/${target}-${family}-leftovers-files-$ts.json" VAULT="$pv/${target}-${family}-leftovers-$ts" python3 - <<'PY'
import json, os
files = [dict(zip(("path", "sha256", "size"), l.split("|"))) for l in os.environ["LIST"].splitlines() if l]
for f in files: f["size"] = int(f["size"])
json.dump({"site": os.environ["SITE"], "vault": os.environ["VAULT"], "folders": os.environ["FOLDERS_PRESENT"].split(), "files": files}, open(os.environ["MF"], "w"), indent=1)
os.chmod(os.environ["MF"], 0o600)
PY
    echo "files manifest: private-backups/${target}-${family}-leftovers-files-$ts.json [folders:${present:- none}]"
    echo "PREPARED ts=$ts" ;;
  apply)
    ts=$arg; sf="$pv/${target}-${family}-leftovers-db-$ts.json"; mf="$pv/${target}-${family}-leftovers-files-$ts.json"; vault="$pv/${target}-${family}-leftovers-$ts"
    [ -s "$sf" ] && [ -s "$mf" ] || { echo "No verified snapshot for $ts on the server; nothing changed." >&2; exit 1; }
    runf snapshot "$pv/.recheck-$ts.json" >/dev/null
    if cmp -s "$pv/.recheck-$ts.json" "$sf"; then rm -f "$pv/.recheck-$ts.json"; else rm -f "$pv/.recheck-$ts.json"; echo "The data changed since the snapshot; nothing changed." >&2; exit 1; fi
    runf delete
    present=$(python3 -c 'import json,sys; print(" ".join(json.load(open(sys.argv[1]))["folders"]))' "$mf")
    if [ -n "$present" ]; then
      mkdir "$vault"; chmod 700 "$vault"
      for f in $present; do mkdir -p "$vault/$(dirname "$f")"; mv -n -- "$wc/$f" "$vault/$f"; [ ! -e "$wc/$f" ] || { echo "Move failed for $f; use --rollback ${sf#"$HOME"/}" >&2; exit 1; }; done
      bad=0; lines=$(python3 -c 'import json,sys; [print("%s|%s" % (f["path"], f["sha256"])) for f in json.load(open(sys.argv[1]))["files"]]' "$mf")
      while IFS='|' read -r p s; do [ -n "$p" ] || continue; [ "$(sha "$vault/$p")" = "$s" ] || { echo "CHECKSUM MISMATCH for $p" >&2; bad=1; }; done <<<"$lines"
      [ "$bad" -eq 0 ] || { echo "Checksum problem after the move; use --rollback ${sf#"$HOME"/}" >&2; exit 1; }
      echo "moved + verified: $(python3 -c 'import json,sys; print(len(json.load(open(sys.argv[1]))["files"]))' "$mf") file(s) in folders:$present -> ${vault#"$HOME"/}"
    fi
    wp --path="$doc" eval 'echo "wordpress-boots-ok\n";' 2>/dev/null | grep -q wordpress-boots-ok || { echo "WordPress does not boot after the cleanup! Restore with --rollback ${sf#"$HOME"/}" >&2; exit 1; }
    echo "verified: WordPress boots, home option unchanged: $(wp --path="$doc" --skip-plugins --skip-themes option get home 2>/dev/null)"
    purge ;;
  rollback)
    arg=${arg/#\$HOME/$HOME}; case "$arg" in "$pv/${target}-${family}-leftovers-db-"*.json) ;; *) echo "Refusing: snapshot must be $pv/${target}-${family}-leftovers-db-*.json" >&2; exit 1 ;; esac
    [ -f "$arg" ] || { echo "Snapshot not found: $arg" >&2; exit 1; }
    mf=${arg/-leftovers-db-/-leftovers-files-}; [ -f "$mf" ] || { echo "Files manifest not found: $mf" >&2; exit 1; }
    vault=$(python3 -c 'import json,sys; print(json.load(open(sys.argv[1]))["vault"])' "$mf"); present=$(python3 -c 'import json,sys; print(" ".join(json.load(open(sys.argv[1]))["folders"]))' "$mf")
    case "$vault" in "$pv/${target}-${family}-leftovers-"*) ;; *) echo "Refusing: vault path in the manifest is not under $pv" >&2; exit 1 ;; esac
    for f in $present; do case " $folders " in *" $f "*) ;; *) echo "Refusing: unexpected folder in the manifest: $f" >&2; exit 1 ;; esac; [ -d "$vault/$f" ] || { echo "Missing from the vault: $f" >&2; exit 1; }; [ ! -e "$wc/$f" ] || { echo "Refusing: wp-content/$f already exists again." >&2; exit 1; }; done
    runf restore "$arg"
    for f in $present; do mv -n -- "$vault/$f" "$wc/$f"; echo "restored folder: $f"; done
    bad=0; lines=$(python3 -c 'import json,sys; [print("%s|%s" % (f["path"], f["sha256"])) for f in json.load(open(sys.argv[1]))["files"]]' "$mf")
    while IFS='|' read -r p s; do [ -n "$p" ] || continue; [ "$(sha "$wc/$p")" = "$s" ] || { echo "CHECKSUM MISMATCH restoring $p" >&2; bad=1; }; done <<<"$lines"
    [ "$bad" -eq 0 ] || { echo "Checksum problem after the rollback; stop and inspect." >&2; exit 1; }
    echo "verified: rows restored and every folder file matches its checksum"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[1] What is there (read-only):"; remote report "$target" "$family"; echo "Dry run only. --apply would: refuse while the plugin folder exists; export the DB, snapshot these rows, copy both OFF the server and verify, delete the rows, move the folders (if any) into the private vault, verify, purge the cache." ;;
  apply)
    echo "[1] Backup on the server (DB export + snapshot of exactly these rows):"
    out=$(remote prepare "$target" "$family"); echo "$out"
    ts=$(sed -n 's/^PREPARED ts=//p' <<<"$out" | tail -1); [ -n "$ts" ] || { echo "Backup step did not finish; nothing was changed." >&2; exit 1; }
    echo "[2] Copying the backup to this computer and verifying it (stops here if it cannot be verified):"
    "$here/../sync-server-backups.sh"
    echo "[3] Deleting:"; remote apply "$target" "$family" "$ts" ;;
  rollback) echo "[1] Rolling back:"; remote rollback "$target" "$family" "$snapshot" ;;
esac

# ref: ./remove-wpvivid-db-leftovers.sh, ./quarantine-wpvivid-leftovers.sh — same pattern; ../sync-server-backups.sh — the off-server copy (*.sql and *.json); ../../../remote_execution.txt — gitignored access details (never printed)

#!/usr/bin/env bash
# Remove WPvivid's database leftovers from a site whose WPvivid plugin has been DELETED: five options and the one-row table <prefix>wpvivid_options.
# Allow-list: live and staging31 (staging31 only after its WPvivid folder is deleted; today it is merely deactivated and the tool refuses).
# Ryan, 2026-10-17: "tidy up" (the leftovers I listed after deleting WPvivid on live).
#
#   ./remove-wpvivid-db-leftovers.sh <target>                       dry run: report exactly what is there (read-only)
#   ./remove-wpvivid-db-leftovers.sh <target> --apply               DB export + a full snapshot of the objects (both copied OFF the server and verified) -> delete -> verify -> purge
#   ./remove-wpvivid-db-leftovers.sh <target> --rollback MANIFEST   put the options and the table back from a <target>-wpvivid-db-leftovers-*.json snapshot and verify them
#
# Guards (on the server, every time): the site is found by `wp option get home`; the WPvivid plugin folder must be GONE; the set of wpvivid% options must be exactly the five
# expected names (anything else -> stop and review); the snapshot is written (owner-only) and compared before anything is deleted; afterwards zero wpvivid options and no
# wpvivid table must remain and WordPress must still boot.
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

target="${1:-}"
case "$target" in live|staging31) ;; *) echo "Usage: $0 <live|staging31> [--apply | --rollback MANIFEST]" >&2; exit 1 ;; esac
mode=dry; manifest=""
case "${2:-}" in
  "") ;;
  --apply) mode=apply ;;
  --rollback) mode=rollback; manifest="${3:-}"; [ -n "$manifest" ] || { echo "--rollback needs the manifest path on the server" >&2; exit 1; } ;;
  *) echo "Unknown option: $2" >&2; exit 1 ;;
esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; arg=${3:-}
case "$target" in live) want="thaisolitude.com" ;; staging31) want="staging31.thaisolitude.com" ;; *) echo "Remote refused: unknown target '$target'." >&2; exit 1 ;; esac
doc=""
cfgs=$(mktemp); phpf=$(mktemp); mv "$phpf" "$phpf.php"; phpf="$phpf.php"; trap 'rm -f "$cfgs" "$phpf"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" --skip-plugins --skip-themes option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/} (home host: $want)"
[ ! -d "$doc/wp-content/plugins/wpvivid-backuprestore" ] || { echo "Refusing: the WPvivid plugin folder still exists on $target. Delete the plugin first." >&2; exit 1; }
cd "$HOME"; pv="$HOME/private-backups"
cat > "$phpf" <<'PHP'
<?php
// modes: report | snapshot FILE | delete | restore FILE.  Touches ONLY the five expected options and <prefix>wpvivid_options.
global $wpdb;   // NOT 'global $args': inside eval-file $args is a local of WP-CLI's command method, and a global declaration would hide it
$args  = isset( $args ) && is_array( $args ) ? $args : array();
$mode  = $args[0] ?? 'report';
$file  = $args[1] ?? '';
$names = array( 'wpvivid_backup_reports', 'wpvivid_check_htaccess_rule_free', 'wpvivid_current_running_staging_task', 'wpvivid_options_db_version', 'wpvivid_staging_history' );
$table = $wpdb->prefix . 'wpvivid_options';
$found = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'wpvivid%' ORDER BY option_name" );
$has_t = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
$other = $wpdb->get_col( "SHOW TABLES LIKE '%wpvivid%'" );
$extra = array_values( array_diff( $found, $names ) );
$wptab = array_values( array_diff( $other, array( $table ) ) );
function wwm_snapshot( $wpdb, $names, $table, $has_t ) {
	$o = array();
	foreach ( $names as $n ) {
		$r = $wpdb->get_row( $wpdb->prepare( "SELECT option_value, autoload FROM {$wpdb->options} WHERE option_name = %s", $n ), ARRAY_A );
		if ( $r ) { $o[] = array( 'name' => $n, 'value_b64' => base64_encode( $r['option_value'] ), 'autoload' => $r['autoload'] ); }
	}
	$t = array( 'name' => $table, 'create' => '', 'rows' => array() );
	if ( $has_t ) {
		$c = $wpdb->get_row( "SHOW CREATE TABLE `$table`", ARRAY_N );
		$t['create'] = $c[1];
		foreach ( $wpdb->get_results( "SELECT * FROM `$table` ORDER BY option_id", ARRAY_A ) as $row ) { $t['rows'][] = array_map( 'base64_encode', array_map( 'strval', $row ) ); }
	}
	return array( 'home' => home_url(), 'options' => $o, 'table' => $t );
}
if ( 'report' === $mode ) {
	echo 'options=' . count( $found ) . ' table=' . ( $has_t ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$table`" ) : 'none' ) . ' unexpected_options=' . count( $extra ) . ' other_tables=' . count( $wptab ) . "\n";
	foreach ( $found as $n ) { echo '  option ' . $n . ( in_array( $n, $names, true ) ? '' : '   <-- UNEXPECTED' ) . "\n"; }
	if ( $has_t ) { echo '  table ' . $table . "\n"; }
	return;
}
if ( $extra || $wptab ) { WP_CLI::error( 'Unexpected wpvivid objects present (options: ' . implode( ',', $extra ) . '; tables: ' . implode( ',', $wptab ) . '). Stop and review.' ); }
if ( 'snapshot' === $mode ) {
	if ( '' === $file || false === file_put_contents( $file, wp_json_encode( wwm_snapshot( $wpdb, $names, $table, $has_t ) ) ) ) { WP_CLI::error( 'Could not write the snapshot.' ); }
	chmod( $file, 0600 );
	$back = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $back ) || count( $back['options'] ) !== count( $found ) || ( $has_t && count( $back['table']['rows'] ) !== (int) $wpdb->get_var( "SELECT COUNT(*) FROM `$table`" ) ) ) { WP_CLI::error( 'Snapshot does not match the live state; nothing was changed.' ); }
	echo 'snapshot written: options=' . count( $back['options'] ) . ' table_rows=' . count( $back['table']['rows'] ) . "\n";
	return;
}
if ( 'delete' === $mode ) {
	foreach ( $names as $n ) { $wpdb->delete( $wpdb->options, array( 'option_name' => $n ) ); }
	if ( $has_t ) { $wpdb->query( "DROP TABLE `$table`" ); }
	wp_cache_flush();
	$left = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'wpvivid%'" );
	$tleft = $wpdb->get_col( "SHOW TABLES LIKE '%wpvivid%'" );
	if ( $left || $tleft ) { WP_CLI::error( 'Leftovers remain after the delete: ' . implode( ',', array_merge( $left, $tleft ) ) ); }
	echo "deleted: 0 wpvivid options and 0 wpvivid tables remain\n";
	return;
}
if ( 'restore' === $mode ) {
	$snap = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $snap ) || ( $snap['home'] ?? '' ) !== home_url() ) { WP_CLI::error( 'Snapshot is missing or from another site.' ); }
	if ( $found || $has_t ) { WP_CLI::error( 'wpvivid objects already exist; resolve by hand before restoring.' ); }
	foreach ( $snap['options'] as $o ) { $wpdb->insert( $wpdb->options, array( 'option_name' => $o['name'], 'option_value' => base64_decode( $o['value_b64'] ), 'autoload' => $o['autoload'] ) ); }
	if ( $snap['table']['create'] ) {
		$wpdb->query( $snap['table']['create'] );
		foreach ( $snap['table']['rows'] as $row ) { $wpdb->insert( $snap['table']['name'], array_map( 'base64_decode', $row ) ); }
	}
	wp_cache_flush();
	$again = wwm_snapshot( $wpdb, $names, $table, (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) );
	if ( $again['options'] !== $snap['options'] || $again['table']['rows'] !== $snap['table']['rows'] ) { WP_CLI::error( 'Restored data does NOT equal the snapshot; stop and inspect.' ); }
	echo "restored + verified: options and table equal the snapshot exactly\n";
	return;
}
WP_CLI::error( 'unknown mode' );
PHP
run() { wp --path="$doc" --user=1 eval-file "$phpf" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable)"; }
case "$phase" in
  report) run report ;;
  prepare)
    rep=$(run report)
    case "$rep" in *"unexpected_options=0 other_tables=0"*) ;; *) echo "Unexpected wpvivid objects present; stopping before any change." >&2; printf '%s\n' "$rep" >&2; exit 1 ;; esac
    ts=$(date +%Y%m%d-%H%M%S); mkdir -p "$pv"; chmod 700 "$pv"
    dump="$pv/${target}-before-wpvivid-db-leftovers-$ts.sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run snapshot "$pv/${target}-wpvivid-db-leftovers-$ts.json"
    echo "snapshot: private-backups/${target}-wpvivid-db-leftovers-$ts.json"
    echo "PREPARED ts=$ts" ;;
  apply)
    ts=$arg; sf="$pv/${target}-wpvivid-db-leftovers-$ts.json"
    [ -s "$sf" ] || { echo "No snapshot for $ts on the server; nothing changed." >&2; exit 1; }
    run snapshot "$pv/.recheck-$ts.json" >/dev/null
    if cmp -s "$pv/.recheck-$ts.json" "$sf"; then rm -f "$pv/.recheck-$ts.json"; else rm -f "$pv/.recheck-$ts.json"; echo "The data changed since the snapshot; nothing changed." >&2; exit 1; fi
    run delete
    wp --path="$doc" eval 'echo "wordpress-boots-ok\n";' 2>/dev/null | grep -q wordpress-boots-ok || { echo "WordPress does not boot after the delete! Restore with --rollback ${sf#"$HOME"/}" >&2; exit 1; }
    echo "verified: WordPress boots, home option unchanged: $(wp --path="$doc" --skip-plugins --skip-themes option get home 2>/dev/null)"
    purge ;;
  rollback)
    arg=${arg/#\$HOME/$HOME}; case "$arg" in "$pv/${target}-wpvivid-db-leftovers-"*.json) ;; *) echo "Refusing: manifest must be $pv/${target}-wpvivid-db-leftovers-*.json" >&2; exit 1 ;; esac
    [ -f "$arg" ] || { echo "Manifest not found: $arg" >&2; exit 1; }
    run restore "$arg"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[1] What is there (read-only):"; remote report "$target"; echo "Dry run only. --apply would: export the DB, snapshot these objects, copy both OFF the server and verify, delete them, verify, purge the cache." ;;
  apply)
    echo "[1] Backup on the server (DB export + snapshot of exactly these objects):"
    out=$(remote prepare "$target"); echo "$out"
    ts=$(sed -n 's/^PREPARED ts=//p' <<<"$out" | tail -1); [ -n "$ts" ] || { echo "Backup step did not finish; nothing was changed." >&2; exit 1; }
    echo "[2] Copying the backup to this computer and verifying it (stops here if it cannot be verified):"
    "$here/../sync-server-backups.sh"
    echo "[3] Deleting:"; remote apply "$target" "$ts" ;;
  rollback) echo "[1] Rolling back:"; remote rollback "$target" "$manifest" ;;
esac

# ref: ./quarantine-wpvivid-leftovers.sh, ./remove-dead-plugins.sh — same connection/guard/backup pattern; ../sync-server-backups.sh — the off-server copy (*.sql and *.json); ../../../remote_execution.txt — gitignored access details (never printed)

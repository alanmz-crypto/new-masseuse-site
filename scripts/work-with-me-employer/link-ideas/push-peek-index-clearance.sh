#!/usr/bin/env bash
# Exact shared-core v8 -> frozen v9 index-clearance update, staging39 only; no page-data writes.
# Public verification binds to the actual complete core + four-page snapshot.
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
if [[ "$mode" = rollback || "$mode" = verify ]] && [[ ! "$snap" =~ ^(\$HOME|/home/[a-zA-Z0-9_-]+)/private-backups/peek-template-core-index-clearance-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json$ ]]; then
  echo "Snapshot must be this index-clearance upgrade's private before-snapshot." >&2; exit 1
fi
if [[ "$mode" = apply || "$mode" = rollback ]] && { [ -z "${TS_OFFSITE_HOOK:-}" ] || [ ! -x "$TS_OFFSITE_HOOK" ]; }; then
  echo "Writes require the configured executable TS_OFFSITE_HOOK (Actions setup-ssh.sh supplies the encrypted off-site store)." >&2; exit 1
fi
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent holds no keys." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
echo "Target site: $target   component: core-index-clearance   mode: $mode"
echo "[1] Uploading the reviewed package outside every web root ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused." >&2; exit 2; }
tar -C "$here" -cf - install-peek-nav.php peek-nav.php peek-nav-legacy.php peek-nav-shared-v1.php peek-nav-shared-v2.php peek-nav-shared-v3.php peek-nav-shared-v4.php peek-nav-shared-v5.php peek-nav-shared-v6.php peek-nav-shared-v7.php peek-nav-shared-v8.php peek-nav-shared-v9.php peek-data title-strip-pages.json \
  | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-peek-index-clearance" && tar -xf - -C "$HOME/wwm-package-peek-index-clearance"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
phase=$1; target=$2; snap=${3:-}
[ "$target" = staging39 ] || { echo "Remote refused: staging39 only." >&2; exit 1; }
want=staging39.thaisolitude.com
command -v wp >/dev/null || { echo "wp-cli missing" >&2; exit 1; }
# config and db query run before WordPress/MU plugins load; only the selected site may bootstrap.
wp_discovery() { command wp --skip-plugins --skip-themes --exec='ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' "$@"; }
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  candidate_prefix=$(wp_discovery --path="$d" config get table_prefix --type=variable 2>/dev/null) || continue
  [[ "$candidate_prefix" =~ ^[A-Za-z0-9_]+$ ]] || continue
  h=$(wp_discovery --path="$d" db query "SELECT option_value FROM ${candidate_prefix}options WHERE option_name='home';" --skip-column-names --batch --raw 2>/dev/null) || continue
  if [ "$h" = "http://$want" ] || [ "$h" = "https://$want" ] || [ "$h" = "http://$want/" ] || [ "$h" = "https://$want/" ]; then
    [ -z "$doc" ] || { echo "More than one install reports exact staging39; refusing." >&2; exit 1; }; doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No install reports exact staging39; nothing changed." >&2; exit 1; }
wp() { command wp --skip-plugins=sg-security "$@"; }
echo "$target is ${doc#"$HOME"/}"
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
export WWM_BACKUP_DIR="$HOME/private-backups"
package="$HOME/wwm-package-peek-index-clearance"
installer="$package/install-peek-nav.php"
core() { wp --path="$doc" --user=1 eval-file "$installer" "$1" core-index-clearance; }
verify_snapshot() {
  php -r '$s=json_decode(file_get_contents($argv[1]),true); if(!is_array($s)||($s["bundle"]??"")!=="peek-core-index-clearance-v1"||($s["component"]??"")!=="core-index-clearance"||($s["slug"]??null)!==""){fwrite(STDERR,"Snapshot belongs to another update; nothing changed.\n");exit(1);}' "$snap"
  wp --path="$doc" --user=1 eval-file "$installer" verify "$snap" "$@";
}
ready() {
  state=$(core preflight); printf '%s\n' "$state"
  case "$state" in *'core-index-clearance ready;'*) ;; *) echo "Apply requires frozen v8. Use --verify with the recorded snapshot for an applied core." >&2; exit 1 ;; esac
}
rollback_ready() {
  state=$(core preflight); printf '%s\n' "$state"
  case "$state" in
    *'core-index-clearance applied;'*) verify_snapshot ;;
    *'core-index-clearance ready;'*) verify_snapshot original ;;
    *) echo "Unknown index-clearance rollback state; nothing changed." >&2; exit 1 ;;
  esac
}
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 || { echo "SiteGround purge failed; no rendered verification claimed." >&2; exit 1; }; echo "SiteGround cache purged"; }
# Same existing inventory guard as the historical wrapper: published permalinks,
# not a second runtime navigation map. Its PHP body is regression-compared offline.
read -r -d '' INVENTORY <<'PHP' || true
$route_ok = static function ( $path ) { return is_string( $path ) && isset( $path[0] ) && '/' === $path[0] && 0 !== strpos( $path, '//' ) && ! preg_match( '/[\x00-\x20\x7f#]/', $path ); };
$routes = array( '/' => array( 'home', '__home__' ) );
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => true ) ) as $page ) {
    $url = wp_parse_url( get_permalink( $page ) );
    if ( ! is_array( $url ) || ! in_array( $url['scheme'] ?? '', array( 'http', 'https' ), true ) || ( $url['host'] ?? '' ) !== 'staging39.thaisolitude.com' || isset( $url['port'] ) || isset( $url['user'] ) || isset( $url['pass'] ) || isset( $url['fragment'] ) ) { WP_CLI::error( 'Published page permalink is outside exact staging39 origin.' ); }
    $path = ( $url['path'] ?? '/' ) . ( isset( $url['query'] ) ? '?' . $url['query'] : '' );
    if ( ! $route_ok( $path ) || ! is_string( $page->post_name ) || '' === $page->post_name || preg_match( '/[\x00-\x20\x7f]/', $page->post_name ) || (int) $page->ID < 1 ) { WP_CLI::error( 'Published page identity/path cannot be checked safely.' ); }
    if ( isset( $routes[ $path ] ) && 'home' !== $routes[ $path ][0] ) { WP_CLI::error( 'Duplicate published page permalink; inventory is ambiguous.' ); }
    $routes[ $path ] = array( 'page-' . (int) $page->ID, $page->post_name );
}
ksort( $routes, SORT_STRING );
$review_file = getenv( 'WWM_PEEK_REVIEWED_PAGES' );
if ( $review_file ) {
    $review = is_file( $review_file ) ? json_decode( file_get_contents( $review_file ), true ) : null;
    if ( ! is_array( $review ) || ! is_array( $review['pages'] ?? null ) ) { WP_CLI::error( 'Reviewed title-strip page inventory missing or invalid; nothing changed.' ); }
    $paths = array();
    foreach ( $review['pages'] as $page ) {
        $path = $page['path'] ?? null;
        if ( ! $route_ok( $path ) || isset( $paths[ $path ] ) ) { WP_CLI::error( 'Reviewed title-strip paths are invalid or duplicated; nothing changed.' ); }
        $paths[ $path ] = true;
    }
    ksort( $paths, SORT_STRING );
    if ( array_keys( $paths ) !== array_keys( $routes ) ) { WP_CLI::error( 'Published page paths differ from reviewed title-strip coverage; update the inventory and browser evidence before applying. Nothing changed.' ); }
}
foreach ( $routes as $path => $record ) { echo $record[0] . "\t" . $path . "\t" . $record[1] . "\n"; }
PHP
inventory() { WWM_PEEK_REVIEWED_PAGES="$package/title-strip-pages.json" wp --path="$doc" --user=1 eval "$INVENTORY"; }
render_all() {
  local source=$1 key route slug code
  tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
  inventory > "$tmp/pages.tsv"
  [ -s "$tmp/pages.tsv" ] || { echo "INVALID: published page inventory is empty." >&2; exit 1; }
  while IFS=$'\t' read -r key route slug; do
    code=$(curl -s -o "$tmp/$key.html" -w '%{http_code}' --max-time 30 "https://$want$route" || true)
    echo "  $code $route"
    [ "$code" = 200 ] || { echo "FAIL $route: HTTP $code; 202 means INVALID access." >&2; exit 1; }
    php -r '
      $source=file_get_contents($argv[1]); $html=file_get_contents($argv[2]); $slug=$argv[3];
      foreach(array("style"=>"ts-peek-nav-css","script"=>"ts-peek-nav-runtime") as $tag=>$id){
        $pattern="#<".$tag." id=\"".$id."\">(.*?)</".$tag.">#s";
        if(!preg_match($pattern,$source,$expected)||preg_match_all($pattern,$html,$actual)!==1||$actual[1][0]!==$expected[1]){fwrite(STDERR,"FAIL exact served ".$id." on ".$slug."\n");exit(1);}
      }
      $data=in_array($slug,array("thai-massage","therapeutic-massage","deep-tissue-massage","chair-massage"),true)?1:0;
      foreach(array("id=\"ts-peek-nav-css\""=>1,"id=\"ts-peek-nav-runtime\""=>1,"id=\"ts-peek-nav-config\""=>1,"id=\"ts-peek-nav-data\""=>$data,"ts-pill-nav"=>0) as $needle=>$count){
        if(substr_count($html,$needle)!==$count){fwrite(STDERR,"FAIL scope ".$needle." on ".$slug."\n");exit(1);}
      }
      if(in_array($slug,array("thai-massage","therapeutic-massage","deep-tissue-massage","chair-massage","services"),true)&&substr_count($html,"class=\"ts-jump\"")!==1){fwrite(STDERR,"FAIL retained jump row\n");exit(1);}
      echo "  exact served core/style and data scope: ".$slug."\n";
    ' "$source" "$tmp/$key.html" "$slug"
  done < "$tmp/pages.tsv"
  rm -rf "$tmp"
}
snap=${snap/#\$HOME/$HOME}
case "$phase" in
  preflight) inventory >/dev/null; core preflight ;;
  prepare|prepare-rollback)
    if [ "$phase" = prepare ]; then inventory >/dev/null; ready; else rollback_ready; fi
    dump="$HOME/private-backups/${target}-before-peek-index-clearance-$(date +%Y%m%d-%H%M%S)-$$.sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Empty database export; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)" ;;
  apply) inventory >/dev/null; ready; core apply ;;
  verify|verify-purged)
    verify_snapshot
    if [ "$phase" = verify-purged ]; then purge; fi
    render_all "$package/peek-nav-shared-v9.php"
    verify_snapshot ;;
  rollback)
    rollback_ready
    wp --path="$doc" --user=1 eval-file "$installer" rollback "$snap"
    purge; render_all "$package/peek-nav-shared-v8.php"
    verify_snapshot original ;;
  *) echo "Unknown remote phase." >&2; exit 1 ;;
esac
EOS
remote() { local quoted; printf -v quoted '%q ' "$@"; "${ssh_cmd[@]}" "bash -s -- $quoted" <<<"$REMOTE"; }
case "$mode" in
  dry) remote preflight "$target"; echo "Dry run only; nothing changed." ;;
  verify) remote verify "$target" "$snap" ;;
  apply)
    echo "[2] Fresh export and exact frozen-v8 preflight:"; remote prepare "$target"
    "$here/../sync-server-backups.sh"
    echo "[3] Apply the index-clearance core:"
    apply_rc=0; apply_output=$(remote apply "$target") || apply_rc=$?
    printf '%s\n' "$apply_output"
    echo "[4] Verify any actual apply snapshot off-site before parsing or rendering:"
    snapshot_sync_rc=0; "$here/../sync-server-backups.sh" || snapshot_sync_rc=$?
    [ "$apply_rc" -eq 0 ] || { echo "Apply failed (exit $apply_rc); snapshot sync exit $snapshot_sync_rc. No rendered verification claimed." >&2; exit "$apply_rc"; }
    [ "$snapshot_sync_rc" -eq 0 ] || exit "$snapshot_sync_rc"
    mapfile -t snapshots < <(sed -n 's/^Success: .*Before-snapshot: \(\/home\/[a-zA-Z0-9_-]*\/private-backups\/peek-template-core-index-clearance-before-[0-9]\{8\}-[0-9]\{6\}-[a-f0-9]\{8\}\.json\)$/\1/p' <<<"$apply_output")
    [ "${#snapshots[@]}" -eq 1 ] || { echo "No unique index-clearance snapshot returned; refusing to guess." >&2; exit 1; }
    echo "[5] Purge, render exact bytes and verify complete core/page state:"; remote verify-purged "$target" "${snapshots[0]}" ;;
  rollback)
    echo "[2] Fresh export and own-snapshot verification before rollback:"; remote prepare-rollback "$target" "$snap"
    "$here/../sync-server-backups.sh"
    echo "[3] Restore, purge, render frozen v8 and verify exact original state:"; remote rollback "$target" "$snap"
    "$here/../sync-server-backups.sh" ;;
esac

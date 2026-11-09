#!/usr/bin/env bash
# Shared Peek core and per-page WPCode data; staging39 only, WordPress storage only.
# Usage: push-peek-nav.sh [core | core-upgrade | core-summary-touch | core-title-strip | core-title-rail | core-kiro-fixes | core-input-focus | page SLUG] [--apply | --check | --rollback FILE]
# Frozen migrations: core legacy -> v1, core-upgrade v1 -> v2, core-summary-touch v2 -> v3, core-title-strip v3 -> v4; core-title-rail v4 -> v5; core-kiro-fixes v5 -> v6; core-input-focus v6 -> current.
# Every write, including rollback, exports and verifies an encrypted offsite backup first.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
# The input-focus lane binds public verification and rollback to its own snapshot.
# Earlier components keep their established wrapper behavior below.
if [ "${1:-}" = core-input-focus ]; then shift; exec "$here/push-peek-input-focus.sh" "$@"; fi
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent at $SSH_AUTH_SOCK holds no keys." >&2; exit 2; }

component=core; slug=""; mode=dry; snap=""
case "${1:-}" in
  core) shift ;;
  core-upgrade) component=core-upgrade; shift ;;
  core-summary-touch) component=core-summary-touch; shift ;;
  core-title-strip) component=core-title-strip; shift ;;
  core-title-rail) component=core-title-rail; shift ;;
  core-kiro-fixes) component=core-kiro-fixes; shift ;;
  core-input-focus) component=core-input-focus; shift ;;
  page) component=page; slug="${2:-}"; [[ "$slug" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]] || { echo "page needs a simple slug" >&2; exit 1; }; shift 2 ;;
esac
case "${1:-}" in
  "") [ "$#" -eq 0 ] || exit 1 ;;
  --apply) [ "$#" -eq 1 ] || exit 1; mode=apply ;;
  --check) [ "$#" -eq 1 ] || exit 1; mode=check ;;
  --rollback) [ "$#" -eq 2 ] || exit 1; mode=rollback; snap=$2 ;;
  *) echo "Usage: $0 [core | core-upgrade | core-summary-touch | core-title-strip | core-title-rail | core-kiro-fixes | core-input-focus | page SLUG] [--apply | --check | --rollback FILE]" >&2; exit 1 ;;
esac
if [ -n "$snap" ] && [[ ! "$snap" =~ ^(\$HOME|/home/[a-zA-Z0-9_-]+)/private-backups/peek-(nav|template-[a-z0-9-]+)-before-[a-zA-Z0-9.-]+\.json$ ]]; then
  echo "Snapshot must be a Peek before-snapshot inside private-backups." >&2; exit 1
fi
target="${S_TARGET:-staging39}"
case "$target" in staging39) ;; *) echo "S_TARGET must be staging39 (practice; live needs Ryan's go)." >&2; exit 1 ;; esac
echo "Target site: $target   mode: $mode"
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")

echo "[1] Connecting and uploading the package to ~/wwm-package-peek-nav (outside every web root) ..."
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
tar -C "$here" -cf - install-peek-nav.php peek-nav.php peek-nav-legacy.php peek-nav-shared-v1.php peek-nav-shared-v2.php peek-nav-shared-v3.php peek-nav-shared-v4.php peek-nav-shared-v5.php peek-nav-shared-v6.php peek-data title-strip-pages.json \
  | "${ssh_cmd[@]}" 'mkdir -p "$HOME/wwm-package-peek-nav" && tar -xf - -C "$HOME/wwm-package-peek-nav"'

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # SiteGround Security 1.6.9 + PHP 8.5 makes EVERY wp exit 0 on live (shutdown-handler bug); skipping that one plugin keeps exit codes true. See AGENTS.md and check-wp-exit-codes.sh.
phase=$1; target=$2; component=$3; slug=$4; snap=${5:-}
case "$component" in core|core-upgrade|core-summary-touch|core-title-strip|core-title-rail|core-kiro-fixes|core-input-focus) [ -z "$slug" ] || exit 1 ;; page) [[ "$slug" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]] || exit 1 ;; *) exit 1 ;; esac
case "$target" in staging39) want="staging39.thaisolitude.com" ;; *) echo "Remote refused: target must be staging39, got '$target'." >&2; exit 1 ;; esac
command -v wp >/dev/null || { echo "wp-cli not found on the server" >&2; exit 1; }
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg")
  h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then
    [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }
    doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}"
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"
export WWM_BACKUP_DIR="$HOME/private-backups"
installer="$HOME/wwm-package-peek-nav/install-peek-nav.php"
run() { local m=$1; shift; if [ "$m" = rollback ]; then wp --path="$doc" --user=1 eval-file "$installer" "$m" "$@"; elif [ "$component" = page ]; then wp --path="$doc" --user=1 eval-file "$installer" "$m" page "$slug"; else wp --path="$doc" --user=1 eval-file "$installer" "$m" "$component"; fi; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "(sg purge unavailable; purge the cache in Site Tools if a page looks stale)"; }
# Enumerate actual published page permalinks, including home, without a runtime map.
# The optional reviewed-path comparison guards only this new forward deployment.
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
inventory() { WWM_PEEK_REVIEWED_PAGES="${1:-}" wp --path="$doc" --user=1 eval "$INVENTORY"; }
reviewed_inventory() { inventory "$HOME/wwm-package-peek-nav/title-strip-pages.json" >/dev/null; echo "Published page paths match reviewed title-strip browser coverage."; }
case "$phase" in
  preflight) if [ "$component" = core-title-strip ] || [ "$component" = core-title-rail ] || [ "$component" = core-kiro-fixes ] || [ "$component" = core-input-focus ]; then reviewed_inventory; fi; run preflight ;;
  prepare)
    dump="$HOME/private-backups/${target}-before-peek-nav-$(date +%Y%m%d-%H%M%S).sql"
    ( umask 077; wp --path="$doc" db export "$dump" >/dev/null )
    [ -s "$dump" ] || { echo "Database export is empty; stopping before any change." >&2; exit 1; }
    echo "fresh database export: ${dump#"$HOME"/} ($(stat -c%s "$dump") bytes)"
    run preflight ;;
  apply) if [ "$component" = core-title-strip ] || [ "$component" = core-title-rail ] || [ "$component" = core-kiro-fixes ] || [ "$component" = core-input-focus ]; then reviewed_inventory; fi; run apply; purge ;;
  verify)
    # Public checks, made from the server itself (not challenged by SiteGround's bot protection). Each page is fetched once.
    tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT
    bad=0
    fetch() { local code; code=$(curl -s -o "$tmp/$1.html" -w '%{http_code}' --max-time 30 "https://$want$2" || true); echo "  $code $2"; [ "$code" = 200 ] || { echo "$2 does not answer 200 (202 = SiteGround's bot challenge = INVALID, retry later)."; exit 1; }; }
    need() { local n; n=$(awk -v needle="$2" '{ line=$0; while ((at=index(line,needle))>0) { total++; line=substr(line,at+length(needle)); } } END { print total+0 }' "$tmp/$1.html"); if [ "$n" = "$3" ]; then echo "  ok   $1: $n x $2"; else echo "  FAIL $1: $n x $2 (want $3)"; bad=1; fi; }
    shared_core=0; sitewide_strip=0
    if core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core-input-focus 2>/dev/null); then
      # Frozen v6 and the reviewed input-focus fix retain sitewide core/config emission.
      shared_core=1; sitewide_strip=1
    elif core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core-kiro-fixes 2>/dev/null); then
      # Frozen v5 and v6 retain sitewide core/config emission.
      shared_core=1; sitewide_strip=1
    elif core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core-title-rail 2>/dev/null); then
      # Both ready (frozen v4) and applied render the core/config sitewide.
      # Rails are client-rendered; the browser gates verify their actual scope.
      shared_core=1; sitewide_strip=1
    elif core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core-title-strip 2>/dev/null); then
      shared_core=1
      # Ready means frozen v3: only applied enables sitewide strip expectations.
      [[ "$core_state" != *'core-title-strip applied;'* ]] || sitewide_strip=1
    elif core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core-summary-touch 2>/dev/null); then
      # The exact v2 -> v3 transition keeps its historical output scope.
      shared_core=1
    elif core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core-upgrade 2>/dev/null); then
      # The original v1 -> v2 migration remains tied to its frozen payloads.
      shared_core=1
    else
      # Historical legacy/data-only recovery remains checkable. Unknown code fails every guard.
      core_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight core)
      [[ "$core_state" != *'core applied;'* ]] || shared_core=1
    fi
    inventory > "$tmp/pages.tsv"
    [ -s "$tmp/pages.tsv" ] || { echo "INVALID: published page inventory is empty"; exit 1; }
    declare -A page_data=()
    if [ "$shared_core" = 1 ]; then
      for pg in thai-massage therapeutic-massage deep-tissue-massage chair-massage; do
        page_state=$(wp --path="$doc" --user=1 eval-file "$installer" preflight page "$pg")
        page_data[$pg]=0
        [[ "$page_state" != *' applied;'* ]] || page_data[$pg]=1
      done
    fi
    while IFS=$'\t' read -r key route pg; do
      fetch "$key" "$route"
      expected=$sitewide_strip; data_expected=0
      if [ "$shared_core" = 1 ]; then
        case "$pg" in thai-massage|therapeutic-massage|deep-tissue-massage|chair-massage) data_expected=${page_data[$pg]} ;; esac
        [ "$data_expected" = 0 ] || expected=1
      elif [ "$pg" = thai-massage ]; then expected=1
      fi
      need "$key" 'id="ts-peek-nav-css"' "$expected"
      need "$key" 'id="ts-peek-nav-runtime"' "$expected"
      need "$key" 'id="ts-peek-nav-config"' "$sitewide_strip"
      need "$key" 'id="ts-peek-nav-data"' "$data_expected"
      case "$pg" in thai-massage|therapeutic-massage|deep-tissue-massage|chair-massage|services) need "$key" 'class="ts-jump"' 1 ;; esac
      need "$key" 'ts-pill-nav' 0
    done < "$tmp/pages.tsv"
    [ "$bad" = 0 ] || { echo "CHECK FAILED (see the lines above)"; exit 1; }
    echo "public check: all published pages and home checked; core/data scope matches installed snippets, existing service jump rows retained" ;;
  rollback) snap=${snap/#\$HOME/$HOME}; run rollback "$snap"; purge ;;
  *) echo "unknown phase" >&2; exit 1 ;;
esac
EOS
remote() { local quoted; printf -v quoted '%q ' "$@"; "${ssh_cmd[@]}" "bash -s -- $quoted" <<<"$REMOTE"; }

case "$mode" in
  dry) echo "[2] Preflight (read-only):"; remote preflight "$target" "$component" "$slug"; echo "Dry run only. Re-run with --apply to write." ;;
  check) echo "[2] Public checks (read-only):"; remote verify "$target" "$component" "$slug" ;;
  apply)
    echo "[2] Backup on the server + preflight:"; remote prepare "$target" "$component" "$slug"
    echo "[3] Copying the backup off the server and verifying it (stops here if it cannot be verified):"; "$here/../sync-server-backups.sh"
    echo "[4] Applying:"; remote apply "$target" "$component" "$slug"
    echo "[5] Copying the new before-snapshot off the server:"; "$here/../sync-server-backups.sh"
    echo "[6] Do the public pages show the change?"; remote verify "$target" "$component" "$slug" ;;
  rollback)
    echo "[2] Fresh backup before rollback:"; remote prepare "$target" "$component" "$slug"
    "$here/../sync-server-backups.sh"
    echo "[3] Rolling back:"; remote rollback "$target" "$component" "$slug" "$snap"
    "$here/../sync-server-backups.sh" ;;
esac

# ref: ./install-peek-nav.php — the guarded installer this runs on the server; ./push-pill-nav.sh — the script this was copied from (same connection/guard pattern)

#!/usr/bin/env bash
# READ-ONLY scan of one SiteGround site for the wording that the Work With Me and availability plan (docs/inter-model/ThaiSolitude_WorkWithMe_Availability_Plan.md) is about:
# Chicago / relocation, "coming soon", November, summer 2027, "studio", house calls, travel, where sessions take place, "now booking" and similar. It looks in every page's stored content
# (visible text AND block attributes such as FAQ answers), SureRank titles and descriptions, the theme settings (footer text), widgets, menu items, WPCode snippets and the site tagline,
# and lists the uploaded résumé file(s) with their sha256. It changes nothing: every command is a read. Output is kept short (the CI report keeps only the last 400 lines).
#
#   scan-availability.sh [TARGET]      TARGET (or S_TARGET): live or stagingNN
# Same connection as the other tools (credentials file + ssh-agent); the install is found by `wp option get home`, never by folder name.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
target="${1:-${S_TARGET:-}}"
case "$target" in
  live) want="thaisolitude.com" ;;
  staging[0-9]|staging[0-9][0-9]) want="$target.thaisolitude.com" ;;
  *) echo "Usage: $0 <live|stagingNN>" >&2; exit 1 ;;
esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
doc=""
cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want." >&2; exit 1; }
echo "# $want is ${doc#"$HOME"/}   (read-only scan)"
wp --path="$doc" eval --skip-themes '
$terms = array(
  "Chicago"            => "/chicago/i",
  "relocat*"           => "/relocat/i",
  "coming soon"        => "/coming\s+soon/i",
  "November"           => "/\bnov(ember|\.)?\s*\d/i",
  "summer 2027"        => "/summer\s+2027|2027/i",
  "studio"             => "/\bstudio\b/i",
  "house call/visit"   => "/house\s*call|home\s*visit|come\s+to\s+you|mobile\s+massage|outcall/i",
  "travel to clients"  => "/travel\s+to\s+clients|do\s+not\s+(currently\s+)?travel/i",
  "sessions take place"=> "/sessions?\s+take\s+place/i",
  "now/currently booking" => "/(now|currently)\s+(accepting|booking|taking)|accepting\s+(new\s+)?(clients|bookings|appointments)|not\s+yet\s+(open|available|accepting)/i",
  "sessions begin/start" => "/sessions?\s+(will\s+)?(begin|start)|(begin|start)(s|ing)?\s+(in\s+)?(november|nov)/i",
  "Mt. Tabor"          => "/mt\.?\s*tabor/i",
  "available/availability" => "/availab/i",
);
$decode = function ( $s ) { return preg_replace_callback( "/\\\\u([0-9a-fA-F]{4})/", function ( $m ) { return html_entity_decode( "&#x" . $m[1] . ";", ENT_QUOTES, "UTF-8" ); }, (string) $s ); };
$hits = 0;
$scan = function ( $label, $raw ) use ( $terms, $decode, &$hits ) {
  $t = $decode( $raw );
  $t = html_entity_decode( $t, ENT_QUOTES, "UTF-8" );
  $t = preg_replace( "/\s+/", " ", $t );
  foreach ( $terms as $name => $re ) {
    if ( preg_match_all( $re, $t, $m, PREG_OFFSET_CAPTURE ) ) {
      $n = 0; $lastEnd = -1;
      foreach ( $m[0] as $hit ) {
        $pos = $hit[1];
        if ( $pos < $lastEnd ) { continue; }
        $from = max( 0, $pos - 70 ); $ctx = substr( $t, $from, 190 );
        $lastEnd = $from + 190;
        printf( "%s | %s | ...%s...\n", $label, $name, $ctx );
        $hits++;
        if ( ++$n >= 4 ) { break; }
      }
    }
  }
};
echo "== PAGES (id slug status) ==\n";
$pages = get_posts( array( "post_type" => "page", "post_status" => array( "publish", "draft", "private" ), "numberposts" => -1, "orderby" => "ID", "order" => "ASC" ) );
foreach ( $pages as $p ) { printf( "%d %s %s len=%d\n", $p->ID, $p->post_name, $p->post_status, strlen( $p->post_content ) ); }
echo "== MATCHES IN PAGE CONTENT (visible text and block attributes) ==\n";
foreach ( $pages as $p ) {
  if ( in_array( $p->post_name, array( "personal-automation", "privacy", "terms" ), true ) ) { continue; }
  $scan( "page:" . $p->post_name, $p->post_content );
}
echo "== SEO TITLES AND DESCRIPTIONS ==\n";
foreach ( $pages as $p ) {
  $g = get_post_meta( $p->ID, "surerank_settings_general", true );
  if ( is_array( $g ) ) { printf( "seo:%s | title=%s | desc=%s\n", $p->post_name, isset( $g["page_title"] ) ? $g["page_title"] : "-", isset( $g["page_description"] ) ? $g["page_description"] : "-" ); }
}
echo "== MATCHES IN SETTINGS, WIDGETS, MENUS, SNIPPETS ==\n";
$scan( "option:blogdescription", (string) get_option( "blogdescription" ) );
$scan( "option:astra-settings", wp_json_encode( get_option( "astra-settings" ) ) );
$scan( "option:widget_block", wp_json_encode( get_option( "widget_block" ) ) );
foreach ( get_posts( array( "post_type" => "nav_menu_item", "post_status" => "any", "numberposts" => -1 ) ) as $mi ) { $scan( "menu_item:" . $mi->ID, $mi->post_title ); }
foreach ( get_posts( array( "post_type" => "wpcode", "post_status" => "publish", "numberposts" => -1 ) ) as $sn ) { $scan( "snippet:" . $sn->ID . ":" . $sn->post_title, $sn->post_content ); }
echo "== MENU (primary) ==\n";
$locs = get_nav_menu_locations(); $mid = isset( $locs["primary"] ) ? (int) $locs["primary"] : 0;
if ( $mid ) { foreach ( wp_get_nav_menu_items( $mid ) as $it ) { printf( "%d order=%d %s -> %s\n", $it->ID, $it->menu_order, $it->title, $it->url ); } }
echo "== LINKS TO THE RESUME IN PAGE CONTENT ==\n";
foreach ( $pages as $p ) { if ( preg_match_all( "/(?:href|src)=\"([^\"]*resume[^\"]*)\"/i", $p->post_content, $m ) ) { foreach ( array_unique( $m[1] ) as $u ) { printf( "%s -> %s\n", $p->post_name, $u ); } } }
printf( "== total matches printed: %d ==\n", $hits );
'
echo "== UPLOADED RESUME FILES =="
find "$doc/wp-content/uploads" -maxdepth 4 -iname '*resume*' -type f -printf '%TY-%Tm-%Td %TH:%TM  %s bytes  %p\n' 2>/dev/null | sed "s#$doc/##" | sort
find "$doc/wp-content/uploads" -maxdepth 4 -iname '*resume*.pdf' -type f 2>/dev/null | while IFS= read -r f; do printf '%s  %s\n' "$(sha256sum "$f" | cut -d' ' -f1)" "${f#"$doc"/}"; done
EOS

ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" <<<"$REMOTE"

# ref: ../../ci/inspect.sh — the connection pattern this follows; ../../../docs/inter-model/ThaiSolitude_WorkWithMe_Availability_Plan.md — the plan whose wording this looks for

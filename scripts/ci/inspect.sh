#!/usr/bin/env bash
# Read-only look at one SiteGround site, for sessions that cannot SSH themselves (a cloud session asks GitHub Actions to run this).
# Same connection as the push scripts (credentials file + ssh-agent); finds the install by `wp option get home`, never by folder name.
#
#   inspect.sh TARGET QUERY [ARG]      TARGET: live staging36 staging34 staging33 ...
#     page SLUG|ID       the page's stored post_content, exact bytes (between BEGIN/END lines), plus ID, status, modified, length, sha256
#     seo SLUG|ID        the page's SureRank SEO rows (surerank_settings_*), raw
#     snippets           every WPCode snippet: ID, status, title, length, sha256
#     snippet ID         one snippet's code, exact bytes
#     css                the Additional CSS (custom_css post of the active theme), exact bytes
#     option NAME        one option, raw; only names on the list below (none that holds a key, password or salt)
#     plugins            wp plugin list
#     fingerprint        the restore-proof fingerprint lines (row counts, page/CSS/snippet/SEO/option hashes), as prove-restore.sh computes them
# Changes nothing: every command is a read. Output can be long (a page is up to ~100 KB).
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../remote_execution.txt}"
target="${1:-}"; query="${2:-}"; arg="${3:-}"
case "$target" in
  live) want="thaisolitude.com" ;;
  staging[0-9]|staging[0-9][0-9]) want="$target.thaisolitude.com" ;;
  *) echo "Usage: $0 <live|stagingNN> <page|seo|snippets|snippet|css|option|plugins|fingerprint> [arg]" >&2; exit 1 ;;
esac
case "$query" in
  page|seo) [[ "$arg" =~ ^[a-z0-9-]{1,80}$ ]] || { echo "$query needs a page slug or ID" >&2; exit 1; } ;;
  snippet) [[ "$arg" =~ ^[0-9]{1,8}$ ]] || { echo "snippet needs an ID" >&2; exit 1; } ;;
  option)
    case "$arg" in
      astra-settings|widget_block|nav_menu_locations|blogname|blogdescription|home|siteurl|active_plugins|template|stylesheet|permalink_structure|\
surerank_settings|wpcode_snippets|ihaf_insert_header|ihaf_insert_footer|wp_page_for_privacy_policy|page_on_front|show_on_front|sidebars_widgets|\
sg_security_disable_activity_log|blog_public) ;;
      *) echo "option '$arg' is not on the read list in $0 (add it there if it holds no secret)" >&2; exit 1 ;;
    esac ;;
  snippets|css|plugins|fingerprint) ;;
  *) echo "unknown query '$query'" >&2; exit 1 ;;
esac
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }   # true exit codes on live under PHP 8.5 (see AGENTS.md, check-wp-exit-codes.sh)
want=$1; query=$2; arg=${3:-}
doc=""
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi
done <<<"$(find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null || true)"   # a here-string, not < <(...): the SiteGround shell has no /dev/fd
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want." >&2; exit 1; }
w() { INSPECT_ARG="$arg" wp --path="$doc" "$@"; }   # wp eval takes no arguments: the PHP below reads the slug/ID from INSPECT_ARG
echo "# $want is ${doc#"$HOME"/}   (read-only inspect: $query $arg)"
case "$query" in
  page)
    w eval --skip-themes '$a = (string) getenv("INSPECT_ARG"); $p = ctype_digit($a) ? get_post((int) $a) : get_page_by_path($a, OBJECT, "page");
      if (!$p) { WP_CLI::error("no page " . $a); }
      $c = $p->post_content;
      printf("ID %d  status %s  modified %s UTC  length %d  sha256 %s\n----- BEGIN post_content -----\n%s\n----- END post_content -----\n", $p->ID, $p->post_status, $p->post_modified_gmt, strlen($c), hash("sha256", $c), $c);' ;;
  seo)
    w eval --skip-themes '$a = (string) getenv("INSPECT_ARG"); $p = ctype_digit($a) ? get_post((int) $a) : get_page_by_path($a, OBJECT, "page");
      if (!$p) { WP_CLI::error("no page " . $a); }
      foreach (get_post_meta($p->ID) as $k => $v) { if (strpos($k, "surerank") === 0) { printf("%s = %s\n", $k, $v[0]); } }' ;;
  snippets)
    w eval --skip-themes 'foreach (get_posts(array("post_type" => "wpcode", "post_status" => "any", "numberposts" => -1, "orderby" => "ID", "order" => "ASC")) as $p) {
      printf("%d\t%s\t%s\t%d\t%s\n", $p->ID, $p->post_status, $p->post_title, strlen($p->post_content), hash("sha256", $p->post_content)); }' ;;
  snippet)
    w eval --skip-themes '$p = get_post((int) getenv("INSPECT_ARG")); if (!$p || "wpcode" !== $p->post_type) { WP_CLI::error("no snippet " . getenv("INSPECT_ARG")); }
      printf("ID %d  status %s  title %s  length %d  sha256 %s\n----- BEGIN code -----\n%s\n----- END code -----\n", $p->ID, $p->post_status, $p->post_title, strlen($p->post_content), hash("sha256", $p->post_content), $p->post_content);' ;;
  css)
    w eval '$p = wp_get_custom_css_post(); if (!$p) { WP_CLI::error("no Additional CSS post"); }
      printf("ID %d  length %d  sha256 %s\n----- BEGIN custom_css -----\n%s\n----- END custom_css -----\n", $p->ID, strlen($p->post_content), hash("sha256", $p->post_content), $p->post_content);' ;;
  option) w option get "$arg" --format=json ;;
  plugins) w plugin list --fields=name,status,version,update_version --format=table ;;
  fingerprint)
    p=$(w db prefix)
    {
      echo "SET NAMES utf8mb4;"
      for t in $(w db tables --all-tables-with-prefix --format=csv | tr ',' ' '); do echo "SELECT 'rows', '$t', COUNT(*) FROM \`$t\`;"; done
      echo "SELECT 'page', post_name, SHA2(REPLACE(REPLACE(post_content,'https://$want','HOST'),'http://$want','HOST'),256) FROM \`${p}posts\` WHERE post_type='page' AND post_status='publish' ORDER BY post_name;"
      echo "SELECT 'css', ID, SHA2(post_content,256) FROM \`${p}posts\` WHERE post_type='custom_css' AND post_status='publish' ORDER BY ID;"
      echo "SELECT 'snippet', ID, CONCAT(post_status, ':', SHA2(post_content,256)) FROM \`${p}posts\` WHERE post_type='wpcode' ORDER BY ID;"
      echo "SELECT 'seo', CONCAT(post_id,':',meta_key), SHA2(meta_value,256) FROM \`${p}postmeta\` WHERE meta_key LIKE 'surerank_settings_%' ORDER BY post_id, meta_key;"
      echo "SELECT 'option', option_name, SHA2(option_value,256) FROM \`${p}options\` WHERE option_name IN ('astra-settings','widget_block','nav_menu_locations','blogname','blogdescription','siteurl','home','active_plugins','template','stylesheet','permalink_structure','wpvivid_schedule_setting','surerank_settings','wpcode_snippets') ORDER BY option_name;"
    } | command wp --path="$doc" --skip-plugins --skip-themes db query --skip-column-names ;;
esac
EOS
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- "$want" "$query" "$arg" <<<"$REMOTE"

# ref: ../work-with-me-employer/prove-restore.sh — the same fingerprint queries; ./run-op.sh — runs this for a cloud session's "inspect" request
# ref: ../../remote_execution.txt — gitignored access details (never printed)

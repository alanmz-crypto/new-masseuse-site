#!/usr/bin/env bash
# Read-only inventory for the requested title strip on EVERY staging39 page.
# Enumerates WordPress pages (including unlisted pages), reads published permalinks
# from the origin, then attempts actual Chromium structure/captures from Actions.
# No database, cache, page, snippet, theme or plugin changes. Temporary files only.
# Usage through Actions: S_TARGET=staging39 inspect-title-pages.sh
# Decode TITLE-INVENTORY-BEGIN/END as base64 tar.gz; the same bundle is retained
# under RUNNER_TEMP/ops-out/peek-title-inventory for the Actions artifact.
# shellcheck disable=SC2016
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
[[ $# -eq 0 && ${S_TARGET:-staging39} == staging39 ]] || { echo 'Only staging39, with no arguments, is allowed.' >&2; exit 1; }
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[[ -f "$creds" ]] || { echo 'Missing credentials file.' >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[[ -n "$host" && -n "$port" && -n "$user" ]] || { echo 'Incomplete connection fields.' >&2; exit 1; }
[[ -n ${SSH_AUTH_SOCK:-} && -S "$SSH_AUTH_SOCK" ]] || { echo 'No ssh-agent socket.' >&2; exit 2; }
out="${RUNNER_TEMP:-${TMPDIR:-/tmp}}/ops-out/peek-title-inventory"
mkdir -p "$out/origin" "$out/rendered"

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
[[ ${1:-} == staging39.thaisolitude.com ]] || { echo 'Remote host refused.' >&2; exit 1; }
want=$1
wp() { command wp --skip-plugins=sg-security --exec='ini_set("log_errors", "0");' "$@"; }
tmp=$(mktemp -d); trap 'rm -rf "$tmp"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$tmp/configs"
doc=''
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [[ "$hh" == "$want" ]]; then
    [[ -z "$doc" ]] || { echo 'Multiple matching installs; refused.' >&2; exit 1; }
    doc=$d
  fi
done < "$tmp/configs"
[[ -n "$doc" ]] || { echo 'No exact staging39 install found.' >&2; exit 1; }
mkdir -p "$tmp/inventory/public"
wp --path="$doc" eval --skip-themes '
  if (wp_parse_url(home_url(), PHP_URL_HOST) !== "staging39.thaisolitude.com") { WP_CLI::error("Host changed"); }
  $pages = array(); $routes = array(); $snippets = array();
  foreach (get_posts(array("post_type" => "page", "post_status" => array_values(get_post_stati()), "numberposts" => -1, "orderby" => "ID", "order" => "ASC", "suppress_filters" => true)) as $p) {
    $url = set_url_scheme(get_permalink($p), "https");
    if (wp_parse_url($url, PHP_URL_HOST) !== "staging39.thaisolitude.com" || wp_parse_url($url, PHP_URL_USER) || wp_parse_url($url, PHP_URL_PASS) || wp_parse_url($url, PHP_URL_PORT)) { WP_CLI::error("Foreign permalink"); }
    $query = wp_parse_url($url, PHP_URL_QUERY);
    $path = (wp_parse_url($url, PHP_URL_PATH) ?: "/") . ($query ? "?" . $query : "");
    $row = array("id" => $p->ID, "slug" => $p->post_name, "title" => $p->post_title, "status" => $p->post_status, "parent" => $p->post_parent, "menu_order" => $p->menu_order, "modified_gmt" => $p->post_modified_gmt, "password_protected" => $p->post_password !== "", "path" => $path, "permalink" => $url, "template" => get_page_template_slug($p), "content_bytes" => strlen($p->post_content), "content_sha256" => hash("sha256", $p->post_content));
    $pages[] = $row;
    if ($p->post_status === "publish") { $routes[] = $row; }
  }
  foreach (get_posts(array("post_type" => "wpcode", "post_status" => array_values(get_post_stati()), "numberposts" => -1, "orderby" => "ID", "order" => "ASC", "suppress_filters" => true)) as $p) {
    $snippets[] = array("id" => $p->ID, "status" => $p->post_status, "title" => $p->post_title, "modified_gmt" => $p->post_modified_gmt, "code_bytes" => strlen($p->post_content), "code_sha256" => hash("sha256", $p->post_content));
  }
  if (!$routes) { WP_CLI::error("No published pages"); }
  $data = array("schema" => 1, "captured_utc" => gmdate("c"), "host" => "staging39.thaisolitude.com", "home" => home_url(), "siteurl" => site_url(), "page_on_front" => (int) get_option("page_on_front"), "show_on_front" => get_option("show_on_front"), "page_for_posts" => (int) get_option("page_for_posts"), "pages" => $pages, "routes" => $routes, "snippets" => $snippets);
  $json = wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if (!is_string($json)) { WP_CLI::error("JSON encoding failed"); }
  echo $json . "\n";
' > "$tmp/inventory/routes.json"
php -r '$d=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR); foreach($d["routes"] as $r){if(!is_int($r["id"]) || preg_match("/[\\r\\n\\t]/",$r["permalink"])) exit(1); echo $r["id"]."\t".$r["permalink"]."\n";}' "$tmp/inventory/routes.json" > "$tmp/routes.tsv"
printf 'id\tstatus\turl\thtml_sha256\n' > "$tmp/inventory/origin.tsv"
while IFS=$'\t' read -r id url; do
  [[ "$id" =~ ^[0-9]+$ && "$url" == https://staging39.thaisolitude.com/* ]] || { echo 'Invalid route.' >&2; exit 1; }
  rc=0
  status=$(curl --silent --show-error --proto '=https' --max-time 40 --dump-header "$tmp/inventory/public/$id.headers" --output "$tmp/inventory/public/$id.html" --write-out '%{http_code}' "$url") || rc=$?
  [[ -f "$tmp/inventory/public/$id.html" ]] || : > "$tmp/inventory/public/$id.html"
  sha=$(sha256sum "$tmp/inventory/public/$id.html" | cut -d' ' -f1)
  printf '%s\t%s\t%s\t%s\n' "$id" "$status" "$url" "$sha" >> "$tmp/inventory/origin.tsv"
  printf 'ORIGIN %s HTTP%s curl=%s %s\n' "$id" "$status" "$rc" "$url" >&2
done < "$tmp/routes.tsv"
tar czf - -C "$tmp/inventory" .
EOS
ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host" bash -s -- staging39.thaisolitude.com <<<"$REMOTE" > "$out/origin.tar.gz"
tar xzf "$out/origin.tar.gz" -C "$out/origin"
browser_exit=0
if [[ -z ${TS_PLAYWRIGHT_ROOT:-} ]]; then
  if bash "$here/../../ci/install-test-tools.sh" --browsers >/dev/null; then
    export TS_PLAYWRIGHT_ROOT="$HOME/.cache/ts-playwright/package.json"
  else
    echo 'INVALID browser dependencies unavailable; origin capture retained.'
    browser_exit=2
  fi
fi
if [[ "$browser_exit" -eq 0 ]]; then
  node "$here/inspect-title-pages.mjs" "$out/origin/routes.json" "$out/rendered" || browser_exit=$?
fi
tar czf "$out/title-inventory.tar.gz" -C "$out" origin rendered
printf 'TITLE-INVENTORY-SHA256 %s\n' "$(sha256sum "$out/title-inventory.tar.gz" | cut -d' ' -f1)"
printf 'TITLE-INVENTORY-BYTES %s\n' "$(wc -c < "$out/title-inventory.tar.gz")"
echo 'TITLE-INVENTORY-BEGIN'
base64 -w0 "$out/title-inventory.tar.gz" | fold -w 200000
printf '\nTITLE-INVENTORY-END\n'
printf 'Origin inventory retained; browser exit=%s. Artifact directory: %s\n' "$browser_exit" "$out"
exit "$browser_exit"

#!/usr/bin/env bash
# Read-only Bundle F capture: six safe option rows, public core semantics,
# discussion/menu preservation digests and unauthenticated public responses.
# Temporary archive files only; no DB/config/post/menu/user/cache updates.
# shellcheck disable=SC2016
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
repo="${S26_REPO:-}"
if [ -z "$repo" ]; then
  if [ -f "$here/../seo-scan/dump-site.sh" ]; then repo=$(cd "$here/../../.." && pwd)
  elif [ -f /workspace/new-masseuse-site/scripts/work-with-me-employer/seo-scan/dump-site.sh ]; then repo=/workspace/new-masseuse-site
  else echo "Set S26_REPO to the repository root." >&2; exit 1; fi
fi
dump="$repo/scripts/work-with-me-employer/seo-scan/dump-site.sh"
[ -f "$dump" ] || { echo "Missing dump-site.sh." >&2; exit 1; }
case "${1:-}" in ''|--sources-only) ;; *) echo "Usage: inspect-bundle-f.sh [--sources-only]" >&2; exit 1 ;; esac
[ "${S_TARGET:-staging38}" = staging38 ] || { echo "Bundle F capture is staging38 only." >&2; exit 1; }
creds="${S26_CREDS:-$repo/remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file." >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent key." >&2; exit 2; }
if [ "${1:-}" != --sources-only ]; then S_TARGET=staging38 S26_CREDS="$creds" bash "$dump"; fi

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
umask 077
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
[ "$want" = staging38.thaisolitude.com ] || { echo "Remote target refused." >&2; exit 1; }
doc=""; cfgs=$(mktemp); scratch=$(mktemp -d); trap 'rm -rf "$cfgs" "$scratch"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want." >&2; exit 1; }; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || { echo "No install for $want." >&2; exit 1; }
mkdir -p "$scratch/data/source" "$scratch/data/public"
export TS_F_EXPORT="$scratch/data"
wp --path="$doc" eval '
$out = getenv("TS_F_EXPORT"); global $wpdb, $wp_version;
$write = function ($name, $data) use ($out) { $json = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); if (false === $json || file_put_contents($out."/".$name, $json) !== strlen($json)) { WP_CLI::error("Capture serialization failed: ".$name); } };
$safe_url = function ($value) { $u = is_string($value) ? wp_parse_url($value) : false; if (!$u || !in_array($u["scheme"] ?? "", array("http", "https"), true) || empty($u["host"]) || isset($u["user"]) || isset($u["pass"]) || isset($u["query"]) || isset($u["fragment"])) { WP_CLI::error("Unexpected URL option/override shape; value omitted."); } return $value; };
$names = array("siteurl", "home", "default_comment_status", "default_ping_status", "default_pingback_flag", "show_avatars");
$options = array(); $settings = get_registered_settings();
foreach ($names as $name) {
    $rows = $wpdb->get_results($wpdb->prepare("SELECT option_value, autoload FROM {$wpdb->options} WHERE option_name=%s", $name), ARRAY_A);
    if (count($rows) !== 1) { WP_CLI::error("Missing/ambiguous safe option: ".$name); }
    $effective = get_option($name);
    if (in_array($name, array("siteurl", "home"), true)) { $safe_url($rows[0]["option_value"]); $safe_url($effective); }
    $r = $settings[$name] ?? array();
    $options[$name] = array("raw_value"=>$rows[0]["option_value"], "autoload"=>$rows[0]["autoload"], "effective"=>$effective, "effective_type"=>gettype($effective), "registered_type"=>$r["type"] ?? null, "sanitize_callback"=>isset($r["sanitize_callback"]) && is_string($r["sanitize_callback"]) ? $r["sanitize_callback"] : null);
}
$overrides = array();
foreach (array("WP_HOME", "WP_SITEURL") as $name) { $overrides[$name] = array("defined"=>defined($name), "value"=>defined($name) ? $safe_url(constant($name)) : null); }
$write("options.json", array("target_host"=>"staging38.thaisolitude.com", "wp_version"=>$wp_version, "options"=>$options, "url_overrides"=>$overrides, "staging_indexing"=>get_option("blog_public"), "htaccess_sha256"=>is_file(ABSPATH.".htaccess") ? hash_file("sha256", ABSPATH.".htaccess") : null, "url_views"=>array("home_url"=>$safe_url(home_url()), "site_url"=>$safe_url(site_url()), "admin_url"=>$safe_url(admin_url()))));
$discussion = $wpdb->get_results($wpdb->prepare("SELECT ID, post_type, comment_status, ping_status FROM {$wpdb->posts} WHERE post_status=%s ORDER BY ID", "publish"), ARRAY_A);
$counts = array(); foreach ($discussion as $r) { $key = $r["post_type"]."|".$r["comment_status"]."|".$r["ping_status"]; $counts[$key] = ($counts[$key] ?? 0)+1; } ksort($counts);
$menus = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key=%s ORDER BY post_id, meta_id", "_menu_item_url"), ARRAY_A);
$write("preservation.json", array("published_discussion_counts"=>$counts, "published_discussion_rows"=>count($discussion), "published_discussion_sha256"=>hash("sha256", json_encode($discussion)), "stored_menu_url_rows"=>count($menus), "stored_menu_url_sha256"=>hash("sha256", json_encode($menus))));
$core = realpath(ABSPATH.WPINC); if (!$core) { WP_CLI::error("Public core directory unavailable."); } $core = wp_normalize_path($core);
$relative = function ($file) use ($core) { $p = realpath($file); if (!$p) { return false; } $p = wp_normalize_path($p); return 0 === strpos($p, $core."/") ? WPINC."/".substr($p, strlen($core)+1) : false; };
$source = "<?php\n// Installed public core option functions; read-only excerpts.\n"; $functions = array();
foreach (array("get_option", "update_option", "sanitize_option", "wp_determine_option_autoload_value", "_config_wp_home", "_config_wp_siteurl", "update_home_siteurl", "flush_rewrite_rules") as $name) {
    if (!function_exists($name)) { WP_CLI::error("Required public core function unavailable: ".$name); }
    $ref = new ReflectionFunction($name); $file = $ref->getFileName(); $rel = $file ? $relative($file) : false;
    if (false === $rel || !is_readable($file)) { WP_CLI::error("Required public core source unavailable: ".$name); }
    $first = $ref->getStartLine(); $last = $ref->getEndLine(); if ($last-$first > 1200) { WP_CLI::error("Unexpected core function size: ".$name); }
    $lines = file($file); if (false === $lines) { WP_CLI::error("Core function read failed."); }
    $functions[$name] = array("file"=>$rel, "first_line"=>$first, "last_line"=>$last);
    $source .= "\n// ".$name." — ".$rel.":".$first."\n".implode("", array_slice($lines, $first-1, $last-$first+1))."\n";
}
if (file_put_contents($out."/source/options-core.php", $source) !== strlen($source)) { WP_CLI::error("Core option source capture failed."); }
$avatar_file = $core."/rest-api/endpoints/class-wp-rest-users-controller.php"; $rel = $relative($avatar_file);
if (false === $rel || !is_readable($avatar_file)) { WP_CLI::error("Public REST user source unavailable."); }
$lines = file($avatar_file); if (false === $lines) { WP_CLI::error("REST user source read failed."); }
$selected = array(); foreach ($lines as $i=>$line) { if (!preg_match("/show_avatars|avatar_urls/", $line)) { continue; } foreach (range(max(0,$i-4),min(count($lines)-1,$i+4)) as $n) { $selected[$n]=true; } } ksort($selected);
$text = $rel."\n"; $last = -2; foreach ($selected as $n=>$unused) { if ($n !== $last+1) { $text .= "...\n"; } $text .= ($n+1).": ".$lines[$n]; $last=$n; }
if (file_put_contents($out."/source/rest-user-avatars.txt", $text) !== strlen($text)) { WP_CLI::error("REST user source capture failed."); }
$write("core.json", array("wp_version"=>$wp_version, "functions"=>$functions, "rest_user_file"=>$rel, "rest_user_sha256"=>hash_file("sha256", $avatar_file)));
WP_CLI::log("Six safe options, URL overrides and preservation/core facts captured read-only.");
'

bad=0
request() { # LABEL URL GET|HEAD; no cookies, auth, curlrc, or automatic redirects
  local label=$1 url=$2 method=$3; local raw="$scratch/$label.hdr" body="$scratch/$label.body"
  if [ "$method" = HEAD ]; then code=$(curl -q -sS --max-time 40 -I -D "$raw" -o /dev/null -w '%{http_code}' "$url") || code=000
  else code=$(curl -q -sS --max-time 40 -D "$raw" -o "$body" -w '%{http_code}' "$url") || code=000; fi
  if [ -f "$raw" ]; then sed '/^[Ss][Ee][Tt]-[Cc][Oo][Oo][Kk][Ii][Ee]:/d' "$raw" > "$scratch/data/public/$label.hdr"; fi
  printf '%s\t%s\t%s\n' "$label" "$code" "$url" >> "$scratch/data/public/manifest.tsv"
  location=""
  if [ -f "$raw" ]; then location=$(sed -n 's/^[Ll]ocation:[[:space:]]*//p' "$raw" | tail -1 | tr -d '\r'); fi
}
for name in root users; do
  if [ "$name" = root ]; then path=/wp-json/; else path=/wp-json/wp/v2/users; fi
  request "rest-$name" "https://$want$path" GET
  body="$scratch/rest-$name.body"
  if [ "$code" = 200 ] && php -r 'json_decode(file_get_contents($argv[1]), true); exit(JSON_ERROR_NONE === json_last_error() ? 0 : 1);' "$body"; then cp "$body" "$scratch/data/public/rest-$name.json"
  else echo "INVALID: public REST $name not available as JSON."; bad=1; fi
done
request admin "https://$want/wp-admin/" HEAD
case "$code" in 200|301|302|303|307|308) ;; *) echo "INVALID: unauthenticated admin response unavailable."; bad=1 ;; esac
case "$code" in 3??)
  case "$location" in
    "https://$want"|"http://$want"|"https://$want/"*|"http://$want/"*) ;;
    //*) echo "INVALID: admin redirect leaves staging38."; bad=1 ;;
    /*) ;;
    *) echo "INVALID: admin redirect leaves staging38 or has an unsupported shape."; bad=1 ;;
  esac ;;
esac
login="https://$want/wp-login.php"; verified=0
for hop in 0 1 2 3; do
  request "login-$hop" "$login" GET
  if [ "$code" = 200 ] && grep -qi 'id="loginform"' "$scratch/login-$hop.body" && grep -qi 'name="pwd"' "$scratch/login-$hop.body"; then
    cp "$scratch/login-$hop.body" "$scratch/data/public/login-form.html"; verified=1; break
  fi
  case "$code" in 3??) ;; *) break ;; esac
  case "$location" in
    "https://$want"|"http://$want"|"https://$want/"*|"http://$want/"*) login=$location ;;
    //*) echo "INVALID: login redirect leaves staging38."; bad=1; break ;;
    /*) login="https://$want$location" ;;
    *) echo "INVALID: login redirect leaves staging38 or has an unsupported shape."; bad=1; break ;;
  esac
done
if [ "$verified" -ne 1 ]; then echo "INVALID: no verified unauthenticated public login form; body omitted."; bad=1; fi
printf 'login_form_verified=%s\nresponse_cookies_omitted=1\nno_authentication_performed=1\n' "$verified" > "$scratch/data/public/checks.txt"
tar czf "$scratch/data.tgz" -C "$scratch/data" .
echo "BUNDLEF-SHA256 $(sha256sum "$scratch/data.tgz" | cut -d' ' -f1)"
echo BUNDLEF-BEGIN
base64 -w0 "$scratch/data.tgz" | fold -w 200000
echo; echo BUNDLEF-END
[ "$bad" -eq 0 ] || exit 2
EOS
"${ssh_cmd[@]}" bash -s -- staging38.thaisolitude.com <<<"$REMOTE"

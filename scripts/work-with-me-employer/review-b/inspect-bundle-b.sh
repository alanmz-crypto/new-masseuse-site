#!/usr/bin/env bash
# Read-only Bundle B capture: selected staging38/live dump, non-secret SureRank
# settings, existing published logo/fonts and public plugin schema implementation.
# No site writes; archive files are temporary and remain outside the web root.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
target="${S_TARGET:-staging38}"
case "$target" in
  staging38) want="staging38.thaisolitude.com" ;;
  live) want="thaisolitude.com" ;;
  *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;;
esac
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

case "${1:-}" in
  "") bash "$here/../seo-scan/dump-site.sh" ;;
  --sources-only) ;; # no public page requests; settings/logo/public source only
  *) echo "Usage: $0 [--sources-only]" >&2; exit 1 ;;
esac

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
doc=""; cfgs=$(mktemp); scratch=$(mktemp -d); trap 'rm -rf "$cfgs" "$scratch"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want." >&2; exit 1; }; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || { echo "No install for $want." >&2; exit 1; }
mkdir -p "$scratch/data/assets" "$scratch/data/plugin"
export TS_B_EXPORT="$scratch/data"
wp --path="$doc" eval '
$out = getenv("TS_B_EXPORT");
$meta = array("wordpress" => get_bloginfo("version"), "php" => PHP_VERSION, "surerank_settings" => get_option("surerank_settings"), "blogdescription" => get_option("blogdescription"), "custom_logo" => get_theme_mod("custom_logo"));
$id = (int) $meta["custom_logo"];
$meta["logo_url"] = wp_get_attachment_url($id);
$meta["logo_meta"] = wp_get_attachment_metadata($id);
$f = get_attached_file($id);
if (!$f || !is_file($f)) { WP_CLI::error("Published custom logo is missing."); }
copy($f, $out . "/assets/logo." . pathinfo($f, PATHINFO_EXTENSION));
$meta["logo_sha256"] = hash_file("sha256",$f);
$css = wp_get_custom_css();
preg_match_all("~https?://[^\\s)\\\"\\x27]+\\.(?:woff2?|ttf)~",$css,$matches);
foreach (array_unique($matches[0]) as $url) {
  $path = wp_parse_url($url, PHP_URL_PATH);
  $pos = strpos($path, "/wp-content/");
  if (false === $pos) { continue; }
  $font = WP_CONTENT_DIR . substr($path,$pos+strlen("/wp-content"));
  if (is_file($font) && filesize($font)<600000) { copy($font,$out."/assets/".basename($font)); }
}
file_put_contents($out."/metadata.json",wp_json_encode($meta,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
file_put_contents($out."/additional.css",$css);
'
# Public plugin source only: inspect schema/settings implementation, never credentials.
tar -C "$doc/wp-content/plugins/surerank" -cf - inc | tar -C "$scratch/data/plugin" -xf -
tar czf "$scratch/data.tgz" -C "$scratch/data" .
echo "BUNDLEB-SHA256 $(sha256sum "$scratch/data.tgz" | cut -d' ' -f1)"
echo BUNDLEB-BEGIN
base64 -w0 "$scratch/data.tgz" | fold -w 200000
echo; echo BUNDLEB-END
EOS
"${ssh_cmd[@]}" bash -s -- "$want" <<<"$REMOTE"

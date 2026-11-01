#!/usr/bin/env bash
# Read-only Bundle D capture: selected staging38/live dump and exact native stores. No credentials/private plugin files.
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
  --sources-only) ;; # exact native stores, no public page requests
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
mkdir -p "$scratch/data/source"
export TS_D_EXPORT="$scratch/data"
wp --path="$doc" eval '
$out = getenv("TS_D_EXPORT");
$data = array("home"=>home_url(), "page_on_front"=>(int)get_option("page_on_front"), "wordpress"=>get_bloginfo("version"), "php_cli"=>PHP_VERSION, "theme_version"=>wp_get_theme()->get("Version"), "astra-settings"=>get_option("astra-settings"), "pages"=>array(), "snippets"=>array());
foreach (get_posts(array("post_type"=>"page", "post_status"=>"publish", "numberposts"=>-1, "suppress_filters"=>true)) as $p) {
  $data["pages"][$p->post_name] = array("id"=>$p->ID,"status"=>$p->post_status,"title"=>$p->post_title,"type"=>$p->post_type,"content"=>$p->post_content,"meta"=>get_post_meta($p->ID));
}
foreach (get_posts(array("post_type"=>"wpcode", "post_status"=>"publish", "numberposts"=>-1,"suppress_filters"=>true)) as $p) {
  $terms=array(); foreach(array("wpcode_type","wpcode_location") as $tax) { $v=wp_get_object_terms($p->ID,$tax,array("fields"=>"slugs")); if(is_wp_error($v)) { WP_CLI::error("Snippet terms unreadable."); } sort($v); $terms[$tax]=$v; }
  $data["snippets"][$p->post_title] = array("id"=>$p->ID,"status"=>$p->post_status,"title"=>$p->post_title,"type"=>$p->post_type,"content"=>$p->post_content,"meta"=>get_post_meta($p->ID),"terms"=>$terms);
}
$css = wp_get_custom_css_post("astra");
if (!$css) { WP_CLI::error("Additional CSS missing."); }
$data["css"] = array("id"=>$css->ID,"content"=>$css->post_content);
file_put_contents($out."/stores.json", wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
'
tar czf "$scratch/data.tgz" -C "$scratch/data" .
echo "BUNDLED-SHA256 $(sha256sum "$scratch/data.tgz" | cut -d' ' -f1)"
echo BUNDLED-BEGIN
base64 -w0 "$scratch/data.tgz" | fold -w 200000
echo; echo BUNDLED-END
EOS
"${ssh_cmd[@]}" bash -s -- "$want" <<<"$REMOTE"

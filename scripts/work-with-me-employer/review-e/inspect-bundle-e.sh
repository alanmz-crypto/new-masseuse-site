#!/usr/bin/env bash
# Read-only Bundle E capture on staging38: fresh dump, exact native stores,
# seven known upload originals and installed core emoji callbacks/source.
# No conversion, import, option/post/cache updates or persistent server files.
# Image/emoji outputs contain no EXIF, credentials or absolute server paths.
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
case "${1:-}" in ''|--sources-only) ;; *) echo "Usage: inspect-bundle-e.sh [--sources-only]" >&2; exit 1 ;; esac
[ "${S_TARGET:-staging38}" = staging38 ] || { echo "Bundle E capture is staging38 only." >&2; exit 1; }
creds="${S26_CREDS:-$repo/remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file." >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }
if [ "${1:-}" != --sources-only ]; then S_TARGET=staging38 S26_CREDS="$creds" bash "$dump"; fi

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
[ "$want" = staging38.thaisolitude.com ] || { echo "Remote target refused." >&2; exit 1; }
doc=""; cfgs=$(mktemp); scratch=$(mktemp -d); trap 'rm -rf "$cfgs" "$scratch"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want." >&2; exit 1; }; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || { echo "No install for $want." >&2; exit 1; }
mkdir -p "$scratch/data/source"
export TS_E_EXPORT="$scratch/data"
wp --path="$doc" eval '
$out = getenv("TS_E_EXPORT");
$write = function ($name, $data) use ($out) {
    $json = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    if (false === $json || false === file_put_contents($out."/".$name, $json)) { WP_CLI::error("Capture serialization failed: ".$name); }
};
$content = function ($p) { return array("id"=>$p->ID, "content"=>$p->post_content, "sha256"=>hash("sha256", $p->post_content), "bytes"=>strlen($p->post_content), "meta"=>get_post_meta($p->ID)); };
$data = array("home"=>home_url(), "astra-settings"=>get_option("astra-settings"), "pages"=>array(), "snippets"=>array());
foreach (get_posts(array("post_type"=>"page", "post_status"=>"publish", "numberposts"=>-1, "suppress_filters"=>true)) as $p) { $data["pages"][$p->post_name] = $content($p); }
foreach (get_posts(array("post_type"=>"wpcode", "post_status"=>"publish", "numberposts"=>-1, "suppress_filters"=>true)) as $p) { $data["snippets"][$p->post_title] = $content($p); }
$css = wp_get_custom_css_post("astra");
if (!$css) { WP_CLI::error("Additional CSS missing."); }
$data["css"] = array("id"=>$css->ID, "content"=>$css->post_content, "sha256"=>hash("sha256", $css->post_content), "bytes"=>strlen($css->post_content));
$write("stores.json", $data);

$uploads = wp_upload_dir(null, false, false);
if (!empty($uploads["error"]) || !is_dir($uploads["basedir"])) { WP_CLI::error("Uploads directory unavailable."); }
$upload_root = realpath($uploads["basedir"]);
$assets = array(
    "therapeutic-png"=>"2026/06/m0KN9hXmTHYQo2KXHhj4c_aa9c21bbb6df4fa79691af5de41f6f48-1.png",
    "corporate"=>"2026/10/corporate-group-massage.webp",
    "portrait"=>"2026/09/butsaba_homepage.webp",
    "thai-session-cutout"=>"2026/10/thai-massage-therapy-session-cutout.webp",
    "home-therapeutic-target"=>"2026/06/ComfyUI_01040_curves.webp",
    "home-deep-tissue-target"=>"2026/06/ComfyUI_01044_.webp",
    "home-thai-target"=>"2026/06/00illaCNN512Curve.webp"
);
$images = array(); global $wpdb;
foreach ($assets as $key=>$relative) {
    $url = "https://staging38.thaisolitude.com/wp-content/uploads/".$relative;
    $ids = array();
    foreach (array($url, str_replace("https://", "http://", $url), trailingslashit($uploads["baseurl"]).$relative) as $candidate) { $id = attachment_url_to_postid($candidate); if ($id) { $ids[] = (int)$id; } }
    foreach ($wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", "_wp_attached_file", $relative)) as $id) { if ("attachment" === get_post_type($id)) { $ids[] = (int)$id; } }
    $ids = array_values(array_unique($ids)); sort($ids);
    if (count($ids)>1) { WP_CLI::error("Ambiguous attachment: ".$relative); }
    $id = $ids ? $ids[0] : null;
    if (!$id && "thai-session-cutout" !== $key) { WP_CLI::error("Expected attachment missing: ".$relative); }
    $file = realpath($upload_root."/".$relative);
    if (!$file || 0 !== strpos($file, $upload_root.DIRECTORY_SEPARATOR) || !is_file($file) || !is_readable($file)) { WP_CLI::error("Known public image unavailable: ".$relative); }
    $size = @getimagesize($file); $bytes = filesize($file); $sha = hash_file("sha256", $file);
    if (!$size || empty($size["mime"]) || false === $bytes || false === $sha) { WP_CLI::error("Image inspection failed: ".$relative); }
    $item = array("url"=>$url, "relative_file"=>$relative, "attachment_id"=>$id, "width"=>$size[0], "height"=>$size[1], "mime"=>$size["mime"], "filesize"=>$bytes, "sha256"=>$sha);
    if ($id) {
        $stored = get_post_meta($id, "_wp_attached_file", true);
        if ($stored !== $relative) { WP_CLI::error("Attachment file differs: ".$relative); }
        $meta = wp_get_attachment_metadata($id);
        $item["attachment"] = array("relative_file"=>$stored, "width"=>is_array($meta) ? ($meta["width"] ?? null) : null, "height"=>is_array($meta) ? ($meta["height"] ?? null) : null, "mime"=>get_post_mime_type($id));
    }
    $images[$key] = $item;
}
$write("images.json", $images);

global $wp_version, $wp_filter;
$usual = array("init", "wp_enqueue_scripts", "wp_print_scripts", "wp_print_styles", "wp_head", "wp_footer", "embed_head", "admin_init", "admin_enqueue_scripts", "admin_print_scripts", "admin_print_styles", "login_enqueue_scripts", "login_head", "login_footer", "wp_mail", "the_content_feed", "comment_text_rss", "tiny_mce_plugins", "wp_resource_hints", "wp_default_scripts", "wp_default_styles", "script_loader_tag", "emoji_svg_url", "emoji_url", "emoji_ext", "emoji_svg_ext");
$hooks = array_fill_keys($usual, array()); $functions = array();
foreach ($wp_filter as $hook=>$object) {
    if (!isset($object->callbacks)) { continue; }
    foreach ($object->callbacks as $priority=>$entries) {
        foreach ($entries as $entry) {
            $cb = $entry["function"];
            if (!is_string($cb) || false === stripos($cb, "emoji") || !function_exists($cb)) { continue; }
            $ref = new ReflectionFunction($cb); $source = $ref->getFileName();
            if (!$source || 0 !== strpos(wp_normalize_path($source), wp_normalize_path(ABSPATH.WPINC)."/")) { continue; }
            if (!isset($hooks[$hook])) { $hooks[$hook] = array(); }
            $hooks[$hook][] = array("callback"=>$cb, "priority"=>(int)$priority, "accepted_args"=>$entry["accepted_args"]);
        }
    }
}
$source_text = "<?php\n// Read-only excerpts from installed public WordPress core; not an executable bundle.\n";
foreach (get_defined_functions()["user"] as $name) {
    if (false === stripos($name, "emoji")) { continue; }
    $ref = new ReflectionFunction($name); $file = $ref->getFileName();
    if (!$file || 0 !== strpos(wp_normalize_path($file), wp_normalize_path(ABSPATH.WPINC)."/")) { continue; }
    $relative = substr(wp_normalize_path($file), strlen(wp_normalize_path(ABSPATH)));
    $first = $ref->getStartLine(); $last = $ref->getEndLine();
    if ($last-$first > 500) { WP_CLI::error("Core emoji function unexpectedly large: ".$name); }
    $lines = file($file);
    if (false === $lines) { WP_CLI::error("Public core source unavailable."); }
    $functions[$name] = array("file"=>$relative, "first_line"=>$first, "last_line"=>$last);
    $source_text .= "\n// ".$name." — ".$relative.":".$first."\n".implode("", array_slice($lines, $first-1, $last-$first+1))."\n";
}
ksort($hooks); ksort($functions);
$write("emoji.json", array("wp_version"=>$wp_version, "hook_context"=>"WP-CLI bootstrap; no frontend actions invoked", "hooks"=>$hooks, "core_functions"=>$functions));
if (false === file_put_contents($out."/source/emoji-core.php", $source_text)) { WP_CLI::error("Core source capture failed."); }
WP_CLI::log("Bundle E native stores, seven image originals and public core emoji facts captured read-only.");
'
tar czf "$scratch/data.tgz" -C "$scratch/data" .
echo "BUNDLEE-SHA256 $(sha256sum "$scratch/data.tgz" | cut -d' ' -f1)"
echo BUNDLEE-BEGIN
base64 -w0 "$scratch/data.tgz" | fold -w 200000
echo; echo BUNDLEE-END
EOS
"${ssh_cmd[@]}" bash -s -- staging38.thaisolitude.com <<<"$REMOTE"

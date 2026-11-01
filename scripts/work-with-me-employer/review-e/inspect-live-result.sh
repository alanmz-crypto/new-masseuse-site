#!/usr/bin/env bash
# Read-only proof of the exact Ryan-owned Bundle E live result and snapshot.
# No import, cleanup, purge, content/option write, media conversion or cache refresh.
# shellcheck disable=SC2016
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
repo="${S26_REPO:-}"
if [ -z "$repo" ]; then
  if [ -f "$here/../seo-scan/dump-site.sh" ]; then repo=$(cd "$here/../../.." && pwd)
  elif [ -f /workspace/new-masseuse-site/scripts/work-with-me-employer/seo-scan/dump-site.sh ]; then repo=/workspace/new-masseuse-site
  else echo "Set S26_REPO to the repository root." >&2; exit 1; fi
fi
[ "${S_TARGET:-}" = live ] || { echo "Bundle E result proof permits S_TARGET=live only." >&2; exit 1; }
[ "$#" -eq 0 ] || { echo "Bundle E result proof takes no arguments." >&2; exit 1; }
creds="${S26_CREDS:-$repo/remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file." >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
[ "$want" = thaisolitude.com ] || { echo "Remote target refused." >&2; exit 1; }
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
  d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true)
  hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then
    [ -z "$doc" ] || { echo "More than one live install reports the approved host." >&2; exit 1; }; doc=$d
  fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No install reports the approved live host." >&2; exit 1; }
export TS_E_RESULT_PACKAGE="$HOME/wwm-package-bundle-e/bundle-e-content.php"
export TS_E_RESULT_SNAPSHOT="$HOME/private-backups/bundle-e-before-20261101-191447.json"
wp --path="$doc" --user=1 eval '
global $wp_version, $wpdb;
$must = function ($ok, $message) { if (!$ok) { throw new \RuntimeException($message); } };
$checks = 0;
$pass = function ($message) use (&$checks) { ++$checks; WP_CLI::log("PASS native E ".$message); };
try {
    $home = untrailingslashit(home_url());
    $must("http://thaisolitude.com" === $home && "7.1.4" === $wp_version && "astra" === get_stylesheet() && current_user_can("manage_options"), "Unexpected live identity, version, theme or CLI user.");
    $root = realpath(ABSPATH);
    $must(is_string($root) && is_dir($root), "Site root unavailable.");
    $outside = function ($path) use ($root) { return is_string($path) && $path !== $root && 0 !== strpos($path, $root.DIRECTORY_SEPARATOR); };
    $package = realpath((string)getenv("TS_E_RESULT_PACKAGE"));
    $expected_package_sha = "ab06069f6f755abe3f65df2dd5782edf17f7ffa0529f2aa514a537469e6d8307";
    $must($outside($package) && is_file($package) && is_readable($package) && "bundle-e-content.php" === basename($package) && hash_file("sha256", $package) === $expected_package_sha, "Outside-root package differs from reviewed main bytes.");
    $data = require $package;
    $must(is_array($data) && ($data["wp_versions"]["thaisolitude.com"] ?? null) === $wp_version && count($data["pages"] ?? array()) === 2 && count($data["originals"] ?? array()) === 10, "Canonical package profile differs.");
    $pass("live identity and exact canonical package SHA");

    $snapshot_file = realpath((string)getenv("TS_E_RESULT_SNAPSHOT"));
    $must($outside($snapshot_file) && is_file($snapshot_file) && is_readable($snapshot_file) && "bundle-e-before-20261101-191447.json" === basename($snapshot_file), "Exact outside-root snapshot unavailable.");
    $snapshot_sha = hash_file("sha256", $snapshot_file);
    $must(is_string($snapshot_sha) && 0 === strpos($snapshot_sha, "0ffe75e3aa18"), "Actual live snapshot SHA differs from verified apply report.");
    $snapshot_bytes = file_get_contents($snapshot_file);
    $snap = is_string($snapshot_bytes) ? json_decode($snapshot_bytes, true) : null;
    $must(is_array($snap) && ($snap["bundle"] ?? null) === "review-e-v1" && ($snap["home"] ?? null) === $home && ($snap["complete"] ?? null) === true && is_array($snap["old"] ?? null) && is_array($snap["new"] ?? null), "Live snapshot identity or completion invalid.");
    $must(hash("sha256", wp_json_encode($snap["old"])) === ($snap["old_sha256"] ?? null) && hash("sha256", wp_json_encode($snap["new"])) === ($snap["new_sha256"] ?? null), "Snapshot old/new store checksum differs.");
    $pass("actual completed snapshot target and old/new checksums");

    $uploads = wp_upload_dir(null, false, false);
    $upload_root = empty($uploads["error"]) ? realpath($uploads["basedir"]) : false;
    $must(is_string($upload_root) && is_dir($upload_root) && "/wp-content/uploads" === rtrim((string)wp_parse_url($uploads["baseurl"], PHP_URL_PATH), "/"), "Unexpected upload root or URL.");
    $inside_uploads = function ($path) use ($upload_root) { return is_string($path) && 0 === strpos($path, $upload_root.DIRECTORY_SEPARATOR) && is_file($path) && is_readable($path); };
    $file_info = function ($relative) use ($must, $upload_root, $inside_uploads) {
        $must(is_string($relative) && "" !== $relative && "/" !== $relative[0] && !in_array("..", explode("/", $relative), true), "Unexpected relative upload identity.");
        $path = realpath($upload_root."/".$relative);
        $must($inside_uploads($path), "Known upload unavailable or outside upload root.");
        $dim = getimagesize($path); $bytes = filesize($path); $sha = hash_file("sha256", $path);
        $must(is_array($dim) && is_int($bytes) && is_string($sha), "Known upload inspection failed.");
        return array("width"=>$dim[0], "height"=>$dim[1], "mime"=>$dim["mime"], "filesize"=>$bytes, "sha256"=>$sha);
    };
    $originals = array();
    foreach ($data["originals"] as $cfg) {
        $relative = $cfg["relative_file"]; $info = $file_info($relative);
        foreach (array("width", "height", "mime", "filesize", "sha256") as $key) { $must($info[$key] === $cfg[$key], "Original file differs from approved identity."); }
        $ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value=%s", "_wp_attached_file", $relative));
        $must(count($ids) === ($cfg["attachment_expected"] ? 1 : 0) && (!$ids || ("attachment" === get_post_type($ids[0]) && realpath(get_attached_file($ids[0])) === realpath($upload_root."/".$relative))), "Original attachment identity differs.");
        $originals[$relative] = $info;
        foreach ($cfg["sizes"] as $size) {
            $info = $file_info($size["relative_file"]);
            foreach (array("width", "height", "mime", "filesize") as $key) { $must($info[$key] === $size[$key], "Original responsive dimensions/MIME/bytes differ."); }
            $originals[$size["relative_file"]] = $info;
        }
    }
    ksort($originals);
    $must(count($originals) === 16 && $originals === ($snap["old"]["original_files"] ?? null) && $originals === ($snap["new"]["original_files"] ?? null), "The sixteen original file SHA/dimension/MIME/size states differ.");
    $pass("all 16 original and responsive files exactly preserved");

    $rows = function ($id) { $out = array(); foreach (get_post_meta($id) as $key=>$values) { if (0 === strpos($key, "surerank_settings_")) { $out[$key] = $values; } } ksort($out); return $out; };
    $all_meta = function ($id) { $out = get_post_meta($id); ksort($out); return $out; };
    $all_statuses = array("publish", "draft", "private", "pending", "future", "trash");
    $page_summary = array(); $pages = array();
    $must(array_keys($data["pages"]) === array_keys($snap["old"]["pages"] ?? array()) && array_keys($data["pages"]) === array_keys($snap["new"]["pages"] ?? array()), "Snapshot page set differs.");
    foreach ($data["pages"] as $slug=>$cfg) {
        $found = get_posts(array("post_type"=>"page", "name"=>$slug, "post_status"=>$all_statuses, "numberposts"=>-1, "suppress_filters"=>true));
        $must(count($found) === 1, "Page missing or ambiguous."); $p = $found[0];
        $old = $snap["old"]["pages"][$slug]; $new = $snap["new"]["pages"][$slug];
        $must("publish" === $p->post_status && "page" === $p->post_type && (int)$p->ID === ($old["id"] ?? null) && (int)$p->ID === ($new["id"] ?? null), "Page ID/type/status differs from exact snapshot.");
        foreach (array("id", "type", "status", "title", "rows") as $key) { $must($old[$key] === $new[$key], "Unchanged page identity or raw SureRank rows differ between snapshot states."); }
        $must(hash("sha256", $old["content"]) === $cfg["hashes"]["thaisolitude.com"]["old"] && hash("sha256", $new["content"]) === $cfg["hashes"]["thaisolitude.com"]["new"] && hash("sha256", $p->post_content) === $cfg["hashes"]["thaisolitude.com"]["new"], "Page whole-content hashes differ from canonical applied profile.");
        $current = array("id"=>(int)$p->ID, "type"=>$p->post_type, "status"=>$p->post_status, "title"=>$p->post_title, "content"=>$p->post_content, "rows"=>$rows($p->ID));
        $must($current === $new && $current["rows"] === $old["rows"], "Current complete page store or raw SureRank rows differ.");
        if ("home" === $slug) { $must((int)get_option("page_on_front") === (int)$p->ID && (int)$p->ID === 296, "Home front-page identity differs."); }
        $pages[$slug] = $current; $page_summary[$slug] = array("id"=>(int)$p->ID, "sha256"=>hash("sha256", $p->post_content));
        $pass("page ".$slug." complete applied store and unchanged raw SureRank rows");
    }
    $must(array("pages"=>$pages, "original_files"=>$originals) === $snap["new"], "Whole captured new state differs.");

    $terms = function ($id) use ($must) { $out = array(); foreach (array("wpcode_type", "wpcode_location") as $tax) { $values = wp_get_object_terms($id, $tax, array("fields"=>"slugs")); $must(!is_wp_error($values), "Snippet terms unavailable."); sort($values); $out[$tax] = $values; } return $out; };
    $must(array_keys($data["new_snippets"] ?? array()) === array("SiteReviewPerformance", "SiteReviewEmoji") && array_keys($data["new_snippets"]) === array_keys($snap["created_snippet_ids"] ?? array()) && array_keys($data["new_snippets"]) === array_keys($snap["created_snippets"] ?? array()), "New snippet set or snapshot ownership differs.");
    $snippet_summary = array();
    foreach ($data["new_snippets"] as $title=>$cfg) {
        $found = get_posts(array("post_type"=>"wpcode", "title"=>$title, "post_status"=>$all_statuses, "numberposts"=>-1, "suppress_filters"=>true));
        $must(count($found) === 1, "Snippet title missing or ambiguous."); $p = $found[0]; $id = (int)$p->ID;
        $must($id === $snap["created_snippet_ids"][$title] && "publish" === $p->post_status && $p->post_title === $title && $p->post_content === $cfg["code"] && hash("sha256", $p->post_content) === $cfg["sha256"], "Owned snippet ID/status/title/runtime differs.");
        $expected_meta = $cfg["meta"]; ksort($expected_meta);
        $must($all_meta($id) === $expected_meta && $terms($id) === array("wpcode_type"=>array("php"), "wpcode_location"=>array("everywhere")), "Owned snippet metadata or taxonomy differs.");
        $current = array("id"=>$id, "post"=>get_post($id, ARRAY_A, "raw"), "meta"=>$all_meta($id), "terms"=>$terms($id));
        $must($current === $snap["created_snippets"][$title], "Owned snippet complete post/meta/terms differs from snapshot.");
        $snippet_summary[$title] = array("id"=>$id, "sha256"=>$cfg["sha256"]);
        $pass("owned snippet ".$title." full post/meta/terms and exact runtime");
    }

    $media = $data["media"];
    $must(($media["width"] ?? null) === 400 && ($media["height"] ?? null) === 313 && ($media["filesize"] ?? null) === 21796 && ($media["sha256"] ?? null) === "e795b73e4272e90f92db59b2734be41dd06365ac8ca6f0e8aa5e02d11ee1a756", "Canonical prepared WebP profile differs.");
    $media_id = $snap["created_media_id"] ?? 0;
    $must(is_int($media_id) && $media_id > 0 && array_key_exists("previous_media", $snap) && $snap["previous_media"] === null && is_array($snap["created_media"] ?? null) && is_array($snap["final_media"] ?? null) && $snap["created_media"] === $snap["final_media"] && ($snap["final_media"]["id"] ?? null) === $media_id, "Snapshot newly owned media identity or final state differs.");
    $found = get_posts(array("post_type"=>"attachment", "post_status"=>array_merge(array("inherit"), $all_statuses), "numberposts"=>-1, "suppress_filters"=>true, "meta_query"=>array("relation"=>"OR", array("key"=>"_wp_attached_file", "value"=>$media["relative_file"]), array("key"=>"_ts_review_e_sha256", "value"=>$media["sha256"]))));
    $must(count($found) === 1 && (int)$found[0]->ID === $media_id && "inherit" === $found[0]->post_status && get_post_mime_type($media_id) === $media["mime"], "Owned prepared WebP attachment missing, duplicated or changed.");
    $logical_file = trailingslashit($uploads["basedir"]).$media["relative_file"];
    $must(get_post_meta($media_id, "_wp_attached_file", true) === $media["relative_file"] && get_attached_file($media_id) === $logical_file && _wp_relative_upload_path($logical_file) === $media["relative_file"], "Prepared attachment logical/relative upload path differs.");
    $must(get_post_meta($media_id, "_ts_review_e_sha256", true) === $media["sha256"] && get_post_meta($media_id, "_ts_review_e_source_sha256", true) === $media["source_sha256"] && get_post_meta($media_id, "_wp_attachment_image_alt", true) === $media["alt"], "Owned WebP provenance or alt metadata differs.");
    $must($file_info($media["relative_file"]) === array("width"=>$media["width"], "height"=>$media["height"], "mime"=>$media["mime"], "filesize"=>$media["filesize"], "sha256"=>$media["sha256"]), "Prepared WebP bytes/dimensions/MIME differ.");
    $metadata = wp_get_attachment_metadata($media_id);
    $must(is_array($metadata) && ($metadata["file"] ?? null) === $media["relative_file"] && ($metadata["width"] ?? null) === 400 && ($metadata["height"] ?? null) === 313, "Prepared media generated dimensions or relative metadata file differ.");
    $paths = array($logical_file);
    foreach ((array)($metadata["sizes"] ?? array()) as $size) { $must(is_array($size) && is_string($size["file"] ?? null) && basename($size["file"]) === $size["file"], "Owned responsive filename invalid."); $paths[] = dirname($logical_file)."/".$size["file"]; }
    if (!empty($metadata["original_image"])) { $must(is_string($metadata["original_image"]) && basename($metadata["original_image"]) === $metadata["original_image"], "Owned original image filename invalid."); $paths[] = dirname($logical_file)."/".$metadata["original_image"]; }
    $files = array();
    foreach (array_unique($paths) as $path) { $physical = realpath($path); $must($inside_uploads($physical), "Owned media file unavailable or outside uploads."); $files[$physical] = hash_file("sha256", $physical); }
    ksort($files);
    $current_media = array("id"=>$media_id, "post"=>get_post($media_id, ARRAY_A, "raw"), "meta"=>$all_meta($media_id), "files"=>$files);
    $must($current_media === $snap["final_media"] && $current_media === $snap["created_media"], "Owned media full post/meta/files differ from exact snapshot.");
    $pass("owned WebP full post/meta/files, relative logical path and prepared bytes");

    $owned_uploads = array();
    $prefix = dirname($upload_root."/".$media["relative_file"])."/".pathinfo($media["name"], PATHINFO_FILENAME);
    foreach (glob($prefix."*") as $path) { $physical = realpath($path); $must($inside_uploads($physical), "Owned upload prefix escapes uploads or has unavailable files."); $owned_uploads[$physical] = hash_file("sha256", $physical); }
    ksort($owned_uploads);
    $must(!empty($owned_uploads) && $owned_uploads === ($snap["created_upload_files"] ?? null), "Owned upload file set or SHA differs from snapshot.");
    foreach ($snap["created_upload_files"] as $path=>$sha) { $must(realpath($path) === $path && $inside_uploads($path) && hash_file("sha256", $path) === $sha, "Snapshot owned upload changed or is outside uploads."); }
    $pass("every owned upload file and prefix SHA intact inside uploads");
    WP_CLI::log("NATIVE_E_RESULT ".wp_json_encode(array("snapshot"=>basename($snapshot_file), "snapshot_sha256"=>$snapshot_sha, "package_sha256"=>$expected_package_sha, "wp_version"=>$wp_version, "pages"=>$page_summary, "snippets"=>$snippet_summary, "media"=>array("id"=>$media_id, "relative_file"=>$media["relative_file"], "sha256"=>$media["sha256"], "width"=>400, "height"=>313, "bytes"=>21796, "file_count"=>count($files)), "original_file_count"=>count($originals), "owned_upload_file_count"=>count($owned_uploads)), JSON_UNESCAPED_SLASHES));
    WP_CLI::log("Bundle E native result: ".$checks." checks, 0 failures; no content writes attempted.");
} catch (\Throwable $error) { WP_CLI::error("Native E proof stopped: ".$error->getMessage()." No content writes attempted."); }
'
EOS
"${ssh_cmd[@]}" bash -s -- thaisolitude.com <<<"$REMOTE"

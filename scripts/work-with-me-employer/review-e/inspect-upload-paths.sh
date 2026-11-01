#!/usr/bin/env bash
# Read-only Bundle E capture on staging38: fresh dump, exact native stores,
# ten known upload originals and installed core emoji callbacks/source.
# A verified PNG copy is converted in the temporary archive directory only.
# No import, option/post/cache updates or persistent server files.
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


read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
want=$1
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}; if [ "$hh" = "$want" ]; then [ -z "$doc" ] || exit 1; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || exit 1
wp --path="$doc" eval '
$u = wp_upload_dir(null,false,false); $relative = "2026/11/therapeutic-red-400.webp";
$logical = $u["basedir"]."/".$relative; $physical = realpath($u["basedir"])."/".$relative;
$files=glob(getenv("HOME")."/private-backups/bundle-e-before-*.json"); rsort($files);
if (!$files) { WP_CLI::error("No E snapshot available."); }
$snap=json_decode(file_get_contents($files[0]),true);
if (!is_array($snap) || ($snap["bundle"] ?? "") !== "review-e-v1" || ($snap["home"] ?? "") !== "http://staging38.thaisolitude.com") { WP_CLI::error("Unexpected snapshot target."); }
$out=array("logical_basedir_matches_realpath"=>$u["basedir"] === realpath($u["basedir"]), "logical_relative_correct"=>_wp_relative_upload_path($logical) === $relative, "physical_relative_correct"=>_wp_relative_upload_path($physical) === $relative, "physical_relative_is_absolute"=>str_starts_with(_wp_relative_upload_path($physical), "/"), "snapshot"=>basename($files[0]), "snapshot_complete"=>$snap["complete"], "owned_media_record_remains"=>!empty($snap["created_media_id"]) && (bool)get_post($snap["created_media_id"]), "attached_file_was_absolute"=>str_starts_with($snap["created_media"]["meta"]["_wp_attached_file"][0] ?? "", "/"), "created_upload_file_count"=>count($snap["created_upload_files"]), "created_upload_files_remaining"=>count(array_filter(array_keys($snap["created_upload_files"]), "is_file")), "page_old_state"=>array());
foreach ($snap["old"]["pages"] as $slug=>$p) { $out["page_old_state"][$slug] = get_post_field("post_content",$p["id"]) === $p["content"]; }
$out["new_snippets_remaining"] = count(get_posts(array("post_type"=>"wpcode", "post_status"=>array("publish","draft","private","trash"), "title"=>"SiteReviewPerformance", "numberposts"=>-1))) + count(get_posts(array("post_type"=>"wpcode", "post_status"=>array("publish","draft","private","trash"), "title"=>"SiteReviewEmoji", "numberposts"=>-1)));
WP_CLI::log(wp_json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
'
EOS
"${ssh_cmd[@]}" bash -s -- staging38.thaisolitude.com <<<"$REMOTE"

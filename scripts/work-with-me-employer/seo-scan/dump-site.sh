#!/usr/bin/env bash
# Read-only dump of one site as a visitor and as WordPress stores it, fetched FROM THE SERVER (SiteGround's bot challenge never answers the server's
# own curl), for a session that cannot reach the site itself (a cloud session: thaisolitude.com is outside its network allow-list). Changes NOTHING:
# no database row, no file, no cache; every command is a read.
#   S_TARGET=live|staging38 (default live). Usage: dump-site.sh
# What it collects into ONE tar.gz, printed as base64 between DUMP-BEGIN and DUMP-END (lines of 200000 characters, so the report's 400-line window keeps it):
#   public/   robots.txt, every sitemap, every sitemap page (NN.hdr = response headers, NN.body = the HTML), a few probe paths (feeds, REST listings of
#             pages and media with their alt text, author and attachment URLs, search, a missing page, http/www redirects), manifest.tsv (file, HTTP code, URL)
#   wp/       read-only WP-CLI listings: core and theme versions, plugins, pages of any status (the stored content only of PUBLISHED pages), posts, media,
#             menus and their items, comments, a few non-secret options, the Additional CSS, the WPCode snippets, each published page's meta rows
# Decode: sed -n '/^DUMP-BEGIN$/,/^DUMP-END$/p' report.md | sed '1d;$d' | tr -d '\n' | base64 -d | tar xz -C DIR
# Exit 0 when the pages were fetched (a probe may answer anything); 1 when a sitemap page did not answer 200; 2 when nothing could be fetched.
# shellcheck disable=SC2016  # the single-quoted commands below are meant to expand on the server, not here
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
creds="${S26_CREDS:-$here/../../../remote_execution.txt}"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
target="${S_TARGET:-live}"
case "$target" in live|staging38) ;; *) echo "S_TARGET must be live or staging38." >&2; exit 1 ;; esac
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
"${ssh_cmd[@]}" true 2>/dev/null || { echo "SSH login refused; check the ssh-agent holds the right key." >&2; exit 2; }

read -r -d '' REMOTE <<'EOS' || true
set -uo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
target=$1
case "$target" in staging38) want="staging38.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging38 or live." >&2; exit 1 ;; esac
doc=""; cfgs=$(mktemp); tmp=$(mktemp -d); trap 'rm -rf "$cfgs" "$tmp"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing dumped." >&2; exit 2; }
echo "$target is ${doc#"$HOME"/}   (read-only site dump)"
w() { wp --path="$doc" "$@"; }
pub="$tmp/dump/public"; wpd="$tmp/dump/wp"; mkdir -p "$pub" "$wpd"
n=0; bad=0
get() { # get NAME URL [curl options]   headers to NAME.hdr, body to NAME.body, one manifest line; never follows redirects (they are what we want to see)
  local name=$1 url=$2; shift 2; n=$((n+1)); local f; f=$(printf '%s/%02d-%s' "$pub" "$n" "$name")
  local code; code=$(curl -s -D "$f.hdr" -o "$f.body" -w '%{http_code}' --max-time 40 "$@" "$url" 2>/dev/null || echo 000)
  printf '%s\t%s\t%s\n' "$(basename "$f")" "$code" "$url" >> "$pub/manifest.tsv"; echo "  $code  $url"
}
echo "-- site files and sitemaps"
get robots "https://$want/robots.txt"
get sitemap-index "https://$want/wp-sitemap.xml"
mapfile -t subs < <(grep -o '<loc>[^<]*</loc>' "$pub"/02-sitemap-index.body 2>/dev/null | sed 's/<[^>]*>//g')
for s in "${subs[@]}"; do get "sitemap-$(basename "$s" .xml)" "$s"; done
{ echo "https://$want/"; for f in "$pub"/*-sitemap-*.body; do grep -o '<loc>[^<]*</loc>' "$f" 2>/dev/null | sed 's/<[^>]*>//g'; done; } | grep -v '\.xml$' | sort -u > "$tmp/urls"
echo "-- $(wc -l < "$tmp/urls") pages from the sitemaps"
while IFS= read -r u; do p=${u#https://$want}; p=${p#/}; p=${p%/}; [ -n "$p" ] || p=home
  get "page-${p//\//_}" "$u"; code=$(tail -1 "$pub/manifest.tsv" | cut -f2); [ "$code" = 200 ] || bad=$((bad+1)); done < "$tmp/urls"
echo "-- probes (what a visitor or a crawler gets from paths outside the sitemap)"
get redirect-http "http://$want/" -I
get redirect-www "https://www.$want/" -I
get redirect-http-www "http://www.$want/" -I
get noslash-about "https://$want/about" -I
get index-php-about "https://$want/index.php/about/" -I
get feed "https://$want/feed/"
get comments-feed "https://$want/comments/feed/"
get rest-root "https://$want/wp-json/"
get rest-users "https://$want/wp-json/wp/v2/users"
get rest-pages "https://$want/wp-json/wp/v2/pages?per_page=100&_fields=id,slug,status,link,modified,title,parent,template"
get rest-media "https://$want/wp-json/wp/v2/media?per_page=100&_fields=id,slug,link,alt_text,caption,title,source_url,mime_type,media_details.width,media_details.height,media_details.filesize"
get rest-posts "https://$want/wp-json/wp/v2/posts?per_page=100&_fields=id,slug,status,link,title"
get author-1 "https://$want/?author=1" -I
get author-slug "https://$want/author/butsaba/" -I
get xmlrpc "https://$want/xmlrpc.php" -I
get wp-login "https://$want/wp-login.php" -I
get search "https://$want/?s=massage"
get missing-page "https://$want/this-page-does-not-exist-9f3a/"
get attachment-sample "https://$want/services/butsaba_avatar-2/butsaba-avatar/" -I
get sample-page "https://$want/sample-page/" -I
get hello-world "https://$want/hello-world/" -I
get category-uncategorized "https://$want/category/uncategorized/" -I
get page-personal-automation "https://$want/personal-automation/" -I
get page-privacy "https://$want/privacy/" -I
get page-terms "https://$want/terms/" -I
get favicon "https://$want/favicon.ico" -I
get security-txt "https://$want/.well-known/security.txt" -I
get sitemap-xml-alias "https://$want/sitemap.xml" -I
get readme-html "https://$want/readme.html" -I
get wp-content-uploads-dir "https://$want/wp-content/uploads/" -I
get wp-includes-dir "https://$want/wp-includes/" -I
get wp-config-bak "https://$want/wp-config.php.bak" -I
get dot-env "https://$want/.env" -I
get dot-git-head "https://$want/.git/HEAD" -I
# assets the pages name: HEAD each distinct image, stylesheet, script, font and PDF on the site's own host (cache headers, sizes; nothing is downloaded)
grep -ho 'https://'"$want"'/wp-content/[^"'"'"' )<>]*\.\(webp\|png\|jpg\|jpeg\|gif\|svg\|pdf\|css\|js\|woff2\?\|ttf\)' "$pub"/*-page-*.body 2>/dev/null | sed 's/[?#].*$//' | sort -u | head -150 > "$tmp/assets"
echo "-- $(wc -l < "$tmp/assets") distinct assets named by the pages (headers only)"
while IFS= read -r a; do b=$(basename "$a"); get "asset-${b//[^A-Za-z0-9._-]/_}" "$a" -I >/dev/null; done < "$tmp/assets"
echo "-- WP-CLI listings (read-only)"
w core version > "$wpd/core-version.txt" 2>&1 || true
w theme list --format=csv > "$wpd/themes.csv" 2>&1 || true
w plugin list --format=csv > "$wpd/plugins.csv" 2>&1 || true
w post list --post_type=page --post_status=any --fields=ID,post_name,post_status,post_title,post_date,post_modified,post_parent,menu_order --format=csv > "$wpd/pages.csv" 2>&1 || true
w post list --post_type=post --post_status=any --fields=ID,post_name,post_status,post_title,post_date,post_modified --format=csv > "$wpd/posts.csv" 2>&1 || true
w post list --post_type=attachment --post_status=any --fields=ID,post_name,post_title,post_excerpt,post_mime_type,post_parent,post_date,guid --format=csv > "$wpd/media.csv" 2>&1 || true
w post list --post_type=wp_block --post_status=any --fields=ID,post_name,post_status,post_title --format=csv > "$wpd/reusable-blocks.csv" 2>&1 || true
w post list --post_type=wpcode --post_status=any --fields=ID,post_name,post_status,post_title,post_modified --format=csv > "$wpd/wpcode-snippets.csv" 2>&1 || true
w post list --post_type=custom_css --post_status=any --fields=ID,post_name,post_status,post_modified --format=csv > "$wpd/custom-css.csv" 2>&1 || true
w comment list --fields=comment_ID,comment_post_ID,comment_approved,comment_type,comment_date --format=csv > "$wpd/comments.csv" 2>&1 || true
w user list --fields=ID,display_name,user_nicename,roles,user_registered --format=csv > "$wpd/users-public-fields.csv" 2>&1 || true
w menu list --format=csv > "$wpd/menus.csv" 2>&1 || true
for m in $(w menu list --fields=term_id --format=csv 2>/dev/null | tail -n +2); do w menu item list "$m" --fields=db_id,type,object,object_id,title,link,position,menu_item_parent,classes,target,attr_title,description --format=csv > "$wpd/menu-$m-items.csv" 2>&1 || true; done
for o in blogname blogdescription blog_public permalink_structure timezone_string date_format time_format page_on_front show_on_front page_for_posts wp_page_for_privacy_policy users_can_register default_role default_comment_status default_ping_status comment_registration close_comments_for_old_posts site_icon template stylesheet WPLANG default_pingback_flag uploads_use_yearmonth_folders thumbnail_size_w medium_size_w large_size_w; do
  printf '%s=' "$o" >> "$wpd/options.txt"; w option get "$o" >> "$wpd/options.txt" 2>&1 || echo "(none)" >> "$wpd/options.txt"; done
w option get astra-settings --format=json > "$wpd/option-astra-settings.json" 2>&1 || true
w option get nav_menu_locations --format=json > "$wpd/option-nav_menu_locations.json" 2>&1 || true
w option get ihaf_insert_header > "$wpd/option-ihaf_insert_header.txt" 2>&1 || true
w option get ihaf_insert_footer > "$wpd/option-ihaf_insert_footer.txt" 2>&1 || true
w cron event list --fields=hook,next_run_relative,recurrence --format=csv > "$wpd/cron.csv" 2>&1 || true
w rewrite list --format=csv > "$wpd/rewrites.csv" 2>&1 || true
mkdir -p "$wpd/css" "$wpd/snippets" "$wpd/pages"
for id in $(w post list --post_type=custom_css --post_status=any --fields=ID --format=csv 2>/dev/null | tail -n +2); do w post get "$id" --field=post_content > "$wpd/css/$id.css" 2>&1 || true; done
for id in $(w post list --post_type=wpcode --post_status=any --fields=ID --format=csv 2>/dev/null | tail -n +2); do w post get "$id" --field=post_content > "$wpd/snippets/$id.txt" 2>&1 || true; w post meta list "$id" --format=json > "$wpd/snippets/$id.meta.json" 2>&1 || true; done
for id in $(w post list --post_type=page --post_status=publish --fields=ID --format=csv 2>/dev/null | tail -n +2); do w post get "$id" --field=post_content > "$wpd/pages/$id.post_content" 2>&1 || true; w post meta list "$id" --format=json > "$wpd/pages/$id.meta.json" 2>&1 || true; done
[ -f "$doc/wp-content/debug.log" ] && tail -n 80 "$doc/wp-content/debug.log" > "$wpd/debug-log-tail.txt" 2>/dev/null
ls -la "$doc" > "$wpd/docroot-listing.txt" 2>&1 || true
ls -la "$doc/wp-content" "$doc/wp-content/uploads" > "$wpd/wp-content-listing.txt" 2>&1 || true
echo "-- packing"
tar czf "$tmp/dump.tgz" -C "$tmp/dump" .
echo "DUMP-SHA256 $(sha256sum "$tmp/dump.tgz" | cut -d' ' -f1) $(wc -c < "$tmp/dump.tgz") bytes, $(find "$tmp/dump" -type f | wc -l) files"
echo "DUMP-BEGIN"; base64 -w0 "$tmp/dump.tgz" | fold -w 200000; echo; echo "DUMP-END"
echo "$n requests from the server; sitemap pages not answering 200: $bad"
[ "$bad" -eq 0 ] || exit 1
EOS
"${ssh_cmd[@]}" bash -s -- "$target" <<<"$REMOTE"

# ref: ./scan-seo.sh — the read-only server-side pattern this follows (connection, install guard, curl from the server); ../../ci/inspect.sh — the stored-bytes queries

#!/usr/bin/env bash
# Apply/check/rollback the reviewed sitewide booking notice on staging43 only.
# The database backup is taken and copied off-server before every write.
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../../.." && pwd)
creds="$repo/remote_execution.txt"
[ -f "$creds" ] || { echo "Missing credentials file: $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "Credentials file is missing Host, Port or User." >&2; exit 1; }
[ -n "${SSH_AUTH_SOCK:-}" ] && [ -S "$SSH_AUTH_SOCK" ] || { echo "No ssh-agent socket (SSH_AUTH_SOCK)." >&2; exit 2; }
SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh-add -l >/dev/null 2>&1 || { echo "The ssh-agent holds no keys." >&2; exit 2; }

mode=dry; snapshot=""
case "${1:-}" in
	"") ;;
	--apply) mode=apply ;;
	--check) mode=check ;;
	--rollback) mode=rollback; snapshot="${2:-}"; [ -n "$snapshot" ] || { echo "--rollback needs the server-side snapshot path." >&2; exit 1; } ;;
	*) echo "Usage: $0 [--apply | --check | --rollback SNAPSHOT]" >&2; exit 1 ;;
esac
target=staging43
want=staging43.thaisolitude.com
ssh_cmd=(ssh -p "$port" -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 "$user@$host")
echo "Target: $target (mode: $mode)"
tar -C "$here" -cf - install-corporate-band-staging43.php | "${ssh_cmd[@]}" "mkdir -p \"\$HOME/wwm-package-corporate-band-staging43\" && tar -xf - -C \"\$HOME/wwm-package-corporate-band-staging43\""

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
phase=$1; target=staging43; want=staging43.thaisolitude.com; snapshot=${2:-}
command -v wp >/dev/null || { echo "wp-cli not found" >&2; exit 1; }
doc=""; cfgs=$(mktemp); trap 'rm -f "$cfgs"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do
	d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
	if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing." >&2; exit 1; }; doc=$d; fi
done < "$cfgs"
[ -n "$doc" ] || { echo "No install reports $want; nothing changed." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}"
mkdir -p "$HOME/private-backups"; chmod 700 "$HOME/private-backups"; export WWM_BACKUP_DIR="$HOME/private-backups"
run() { wp --path="$doc" --user=1 eval-file "$HOME/wwm-package-corporate-band-staging43/install-corporate-band-staging43.php" "$@"; }
purge() { wp --path="$doc" --user=1 sg purge >/dev/null 2>&1 && echo "SiteGround cache purged" || echo "Purge cache in Site Tools if the public pages remain stale."; }
case "$phase" in
	preflight) run preflight ;;
	apply) run apply; purge ;;
	rollback) run rollback "$snapshot"; purge ;;
	verify)
		pages=$(wp --path="$doc" post list --post_type=page --post_status=publish --field=post_name)
		tmp=$(mktemp -d); trap 'rm -rf "$tmp" "$cfgs"' EXIT; total=0; failures=0
		while IFS= read -r slug; do
			[ -n "$slug" ] || continue
			path="/$slug/"; [ "$slug" = home ] && path="/"
			html="$tmp/page-$total.html"; headers="$tmp/headers-$total.txt"; band="$tmp/band-$total.html"
			code=$(curl -sS -L -D "$headers" -o "$html" -w '%{http_code}' --max-time 30 "https://$want$path" || true)
			if [ "$code" != 200 ]; then echo "FAIL $path HTTP $code"; failures=$((failures + 1)); continue; fi
			if ! perl -0777 -ne 'if (/(<div class="ts-corp-band".*?<\/div><\/div>)/s) { print $1 }' "$html" > "$band" || [ ! -s "$band" ]; then echo "FAIL $path notice missing or malformed"; failures=$((failures + 1)); continue; fi
			count() { grep -o -F -- "$1" "$band" | wc -l | tr -d ' '; }
			page_fail=0
			[ "$(count 'class="ts-corp-band"')" = 1 ] || { echo "FAIL $path notice count"; page_fail=1; }
			[ "$(count 'href="/winding-willow/"')" = 2 ] || { echo "FAIL $path Winding Willow link count"; page_fail=1; }
			[ "$(count 'href="/corporate-massage-portland/"')" = 2 ] || { echo "FAIL $path corporate route link count"; page_fail=1; }
			[ "$(count 'href="https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt"')" = 2 ] || { echo "FAIL $path Butsaba booking link count"; page_fail=1; }
			[ "$(count 'Now booking appointments at')" = 2 ] || { echo "FAIL $path heading copy"; page_fail=1; }
			[ "$(count 'Book an appointment with Butsaba')" = 2 ] || { echo "FAIL $path paragraph copy"; page_fail=1; }
			[ "$(count 'arrange a corporate or group massage at a suitable Portland venue with Thai Solitude.')" = 2 ] || { echo "FAIL $path paragraph ending"; page_fail=1; }
			[ "$(count 'Winding Willow Acupuncture &middot; Corporate &amp; Group Massage &middot; Portland')" = 1 ] || { echo "FAIL $path label"; page_fail=1; }
			if ! grep -Eqi '<meta[^>]+name=["'"'"']robots["'"'"'][^>]+content=["'"'"'][^"'"'"']*noindex|<meta[^>]+content=["'"'"'][^"'"'"']*noindex[^"'"'"']*["'"'"'][^>]+name=["'"'"']robots' "$html"; then echo "FAIL $path robots noindex meta"; page_fail=1; fi
			if grep -Eqi '^x-robots-tag:.*noindex' "$headers"; then echo "INFO $path also has X-Robots-Tag noindex"; fi
			! grep -q 'staging43.thaisolitude.com/corporate-massage-portland' "$band" || { echo "FAIL $path corporate link still points to staging"; page_fail=1; }
			if [ "$page_fail" = 0 ]; then echo "PASS $path: exact notice links/copy and noindex"; else failures=$((failures + 1)); fi
			total=$((total + 1))
		done <<<"$pages"
		robots=$(curl -sS --max-time 20 "https://$want/robots.txt" || true)
		printf '%s' "$robots" | grep -Eqi 'Disallow:[[:space:]]*/' || { echo "FAIL staging robots.txt is not disallowing crawl"; failures=$((failures + 1)); }
		[ "$total" -gt 0 ] || { echo "FAIL no published pages were checked"; failures=$((failures + 1)); }
		post_slug=$(wp --path="$doc" post list --post_type=post --post_status=publish --field=post_name | head -1)
		archive_url=$(wp --path="$doc" term list category --fields=slug --format=csv 2>/dev/null | tail -n +2 | head -1 | tr -d '\r')
		if [ -n "$post_slug" ]; then
			route="/$post_slug/"; html="$tmp/route-post.html"; code=$(curl -sS -L -o "$html" -w '%{http_code}' --max-time 30 "https://$want$route" || true)
			if [ "$code" != 200 ] || ! grep -q 'class="ts-corp-band"' "$html"; then echo "FAIL representative post $route HTTP $code or notice absent"; failures=$((failures + 1)); else echo "PASS representative post $route: notice present"; fi
		else echo "INFO no published posts available for representative post check"; fi
		for route in "/category/${archive_url:-__no_categories__}/"; do
			html="$tmp/route-$total.html"; code=$(curl -sS -o "$html" -w '%{http_code}' --max-time 30 "https://$want$route" || true)
			if [ "$code" != 200 ] || ! grep -q 'class="ts-corp-band"' "$html"; then echo "FAIL representative route $route HTTP $code or notice absent"; failures=$((failures + 1)); else echo "PASS representative route $route: notice present"; fi
		done
		html="$tmp/route-404.html"; code=$(curl -sS -o "$html" -w '%{http_code}' --max-time 30 "https://$want/codex-verify-not-found-20261128/" || true)
		if [ "$code" != 404 ] || ! grep -q 'class="ts-corp-band"' "$html"; then echo "FAIL representative 404 route HTTP $code or notice absent"; failures=$((failures + 1)); else echo "PASS representative 404 route: HTTP 404 and notice present"; fi
		echo "Checked $total published WordPress pages plus post/archive/404 routes on staging43; failures=$failures."
		[ "$failures" = 0 ] ;;
	*) echo "Unknown phase: $phase" >&2; exit 1 ;;
esac

# ref: ./install-corporate-band-staging43.php — exact guarded WordPress change; ../../ci/backup-now.sh and ../sync-server-backups.sh — verified backup routine
EOS
remote() { "${ssh_cmd[@]}" bash -s -- "$@" <<<"$REMOTE"; }

case "$mode" in
	dry) remote preflight; echo "Dry run only; no WordPress value changed." ;;
	check) remote verify ;;
	apply)
		remote preflight
		bash "$repo/scripts/ci/backup-now.sh" staging43
		remote apply
		remote verify
		bash "$repo/scripts/work-with-me-employer/sync-server-backups.sh" ;;
	rollback)
		remote preflight
		bash "$repo/scripts/ci/backup-now.sh" staging43
		remote rollback "$snapshot"
		remote verify
		bash "$repo/scripts/work-with-me-employer/sync-server-backups.sh" ;;
esac

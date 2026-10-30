#!/usr/bin/env bash
# Read-only SEO scan of every page in a site's sitemap, fetched from the server itself (not challenged by SiteGround's bot protection). Changes NOTHING: no database row, no file, no cache.
# Ryan, 2026-10-30: "a cursory scan of all SEO material first, and then I'll request indexing."
# Per page: HTTP status, X-Robots-Tag, <title> and <meta description> (with lengths), canonical, robots meta, Open Graph and Twitter tags, H1 count and text, JSON-LD blocks (parse ok, @type),
# images without alt, leftover "Mt. Tabor" / "November 1," wording; then duplicates across pages and the sitemap/robots files. Prints WARN lines for anything odd; exits 0 unless a page does not answer 200.
#   S_TARGET=live|staging38 (default live). Usage: scan-seo.sh
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

read -r -d '' PHPSCAN <<'EOP' || true
<?php
$dir = $argv[1]; $base = $argv[2];
$files = glob("$dir/*.html"); sort($files);
$titles = $descs = array(); $bad = 0;
foreach ($files as $f) {
  $url = trim(file_get_contents(substr($f, 0, -5) . '.url')); $code = trim(file_get_contents(substr($f, 0, -5) . '.code')); $hdr = file_get_contents(substr($f, 0, -5) . '.hdr');
  $html = file_get_contents($f);
  echo "\n== $url  (HTTP $code, " . strlen($html) . " bytes)\n";
  if ($code !== '200') { echo "  FAIL page does not answer 200\n"; $bad++; continue; }
  if (preg_match('/^x-robots-tag:\s*(.*)$/mi', $hdr, $m)) { echo "  X-Robots-Tag: " . trim($m[1]) . (stripos($m[1], 'noindex') !== false ? "   WARN noindex!" : "") . "\n"; }
  libxml_use_internal_errors(true); $d = new DOMDocument(); $d->loadHTML('<?xml encoding="utf-8"?>' . $html); $x = new DOMXPath($d);
  $meta = function ($attr, $name) use ($x) { $n = $x->query("//meta[@$attr='$name']/@content"); return $n->length ? trim($n->item(0)->nodeValue) : null; };
  $t = $x->query('//title'); $title = $t->length ? trim($t->item(0)->textContent) : null; $desc = $meta('name', 'description');
  echo "  title (" . strlen((string) $title) . "): $title\n  description (" . strlen((string) $desc) . "): $desc\n";
  if ($title === null || $title === '') echo "  WARN no title\n"; elseif (mb_strlen($title) > 65) echo "  WARN title longer than 65 characters (may be cut in results)\n";
  if ($desc === null || $desc === '') echo "  WARN no meta description\n"; elseif (mb_strlen($desc) > 160) echo "  WARN description longer than 160 characters\n"; elseif (mb_strlen($desc) < 70) echo "  WARN description shorter than 70 characters\n";
  $titles[$title][] = $url; $descs[$desc][] = $url;
  $c = $x->query("//link[@rel='canonical']/@href"); $canon = $c->length ? $c->item(0)->nodeValue : null;
  echo "  canonical: " . ($canon ?? 'MISSING') . "\n";
  if ($canon === null) echo "  WARN no canonical\n"; elseif (rtrim($canon, '/') !== rtrim($url, '/')) echo "  WARN canonical differs from the page URL\n";
  $rob = $meta('name', 'robots'); echo "  robots meta: " . ($rob ?? '(none)') . ($rob && stripos($rob, 'noindex') !== false ? "   WARN noindex!" : "") . "\n";
  foreach (array('og:title', 'og:description', 'og:url', 'og:image') as $p) { $v = $meta('property', $p); if ($v === null) echo "  WARN missing $p\n"; }
  foreach (array('twitter:title', 'twitter:description') as $p) { if ($meta('name', $p) === null) echo "  WARN missing $p\n"; }
  if ($meta('property', 'og:title') !== null && $meta('property', 'og:title') !== $title) echo "  WARN og:title differs from the title\n";
  if ($meta('property', 'og:description') !== null && $desc !== null && $meta('property', 'og:description') !== $desc) echo "  WARN og:description differs from the description\n";
  if ($meta('property', 'og:url') !== null && $canon !== null && rtrim($meta('property', 'og:url'), '/') !== rtrim($canon, '/')) echo "  WARN og:url differs from the canonical\n";
  $h1 = $x->query('//h1'); $h1t = array(); foreach ($h1 as $h) { $h1t[] = trim(preg_replace('/\s+/', ' ', $h->textContent)); }
  echo "  H1 (" . count($h1t) . "): " . implode(' | ', $h1t) . "\n"; if (count($h1t) !== 1) echo "  WARN expected exactly one H1\n";
  $ld = $x->query("//script[@type='application/ld+json']"); $types = array(); $ldbad = 0;
  foreach ($ld as $s) { $j = json_decode($s->textContent, true); if ($j === null) { $ldbad++; continue; } $stack = array($j); while ($stack) { $n = array_pop($stack); if (!is_array($n)) continue; if (isset($n['@type'])) { $types[] = is_array($n['@type']) ? implode('/', $n['@type']) : $n['@type']; } foreach ($n as $v) { if (is_array($v)) $stack[] = $v; } } }
  echo "  JSON-LD blocks: " . $ld->length . ", unparseable: $ldbad, types: " . implode(', ', array_unique($types)) . "\n"; if ($ldbad) echo "  WARN a JSON-LD block does not parse\n";
  $imgs = $x->query('//img'); $noalt = 0; foreach ($imgs as $i) { if (!$i->hasAttribute('alt')) $noalt++; }
  echo "  images: " . $imgs->length . ", without an alt attribute: $noalt\n";
  $body = preg_replace('/<(script|style)\b.*?<\/\1>/is', '', $html); $text = html_entity_decode(strip_tags($body), ENT_QUOTES, 'UTF-8'); $meta_all = $html;
  $tab_text = preg_match_all('/Mt\.\s?Tabor/i', $text); $tab_all = preg_match_all('/Mt\.?(%20|\s)Tabor/i', $meta_all); $n1 = preg_match_all('/November 1,/', $text);
  echo "  leftovers: 'Mt. Tabor' in visible text $tab_text, anywhere in the HTML $tab_all; 'November 1,' in visible text $n1\n";
  if ($tab_text) echo "  WARN visible 'Mt. Tabor' wording\n";
  if ($n1 && strpos($url, 'work-with-me') === false) echo "  WARN 'November 1,' still on this page\n";
}
foreach (array('title' => $titles, 'description' => $descs) as $k => $arr) { foreach ($arr as $v => $urls) { if ($v !== '' && count($urls) > 1) echo "\nWARN duplicate $k on " . count($urls) . " pages: " . implode(', ', $urls) . "\n"; } }
echo "\n" . count($files) . " pages scanned; pages not answering 200: $bad\n"; exit($bad ? 1 : 0);
EOP

read -r -d '' REMOTE <<'EOS' || true
set -euo pipefail
wp() { command wp --skip-plugins=sg-security "$@"; }
target=$1
case "$target" in staging38) want="staging38.thaisolitude.com" ;; live) want="thaisolitude.com" ;; *) echo "Remote refused: target must be staging38 or live." >&2; exit 1 ;; esac
doc=""; cfgs=$(mktemp); tmp=$(mktemp -d); trap 'rm -rf "$cfgs" "$tmp"' EXIT
find "$HOME/www" -maxdepth 4 -name wp-config.php 2>/dev/null > "$cfgs"
while IFS= read -r cfg; do d=$(dirname "$cfg"); h=$(wp --path="$d" option get home 2>/dev/null || true); hh=${h#http://}; hh=${hh#https://}; hh=${hh%/}
  if [ "$hh" = "$want" ]; then [ -z "$doc" ] || { echo "More than one install reports $want; refusing to guess." >&2; exit 1; }; doc=$d; fi; done < "$cfgs"
[ -n "$doc" ] || { echo "No WordPress install under ~/www reports home host $want; nothing scanned." >&2; exit 1; }
echo "$target is ${doc#"$HOME"/}   (read-only SEO scan)"
for f in robots.txt wp-sitemap.xml wp-sitemap-posts-page-1.xml; do
  code=$(curl -s -o "$tmp/$f" -w '%{http_code}' --max-time 30 "https://$want/$f" || true); echo "-- /$f -> HTTP $code"; [ "$code" = 200 ] && head -c 1500 "$tmp/$f" | sed 's/^/   /'; echo
done
mkdir -p "$tmp/pages"
{ echo "https://$want/"; grep -o '<loc>[^<]*</loc>' "$tmp/wp-sitemap-posts-page-1.xml" 2>/dev/null | sed 's/<[^>]*>//g'; } | sort -u > "$tmp/urls"
i=0; while IFS= read -r u; do i=$((i+1)); n=$(printf '%s/pages/p%02d' "$tmp" "$i")
  code=$(curl -s -D "$n.hdr" -o "$n.html" -w '%{http_code}' --max-time 30 "$u" || true); echo "$u" > "$n.url"; echo "$code" > "$n.code"; done < "$tmp/urls"
php -r "$PHPSCAN_CODE" "$tmp/pages" "https://$want"
EOS
"${ssh_cmd[@]}" bash -s -- "$target" <<<"PHPSCAN_CODE=$(printf '%q' "${PHPSCAN#<?php}")
$REMOTE"

# ref: ../availability/push-strip-flip.sh — the read-only server-side pattern this follows (connection, install guard, curl from the server)

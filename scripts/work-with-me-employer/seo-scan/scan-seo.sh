#!/usr/bin/env bash
# Read-only SEO scan of every page in a site's sitemap, fetched from the server itself (not challenged by SiteGround's bot protection). Changes NOTHING: no database row, no file, no cache.
# Ryan, 2026-10-30: "a cursory scan of all SEO material first, and then I'll request indexing."
# Per page: HTTP status, X-Robots-Tag, <title> and <meta description> (with lengths), canonical, robots meta, Open Graph and Twitter tags, H1 count and text, JSON-LD blocks (parse ok, @type),
# images without alt, leftover "Mt. Tabor" / "November 1," wording; then duplicates across pages and the sitemap/robots files. Prints WARN lines for general observations; non-200 pages and Bundle B expectation mismatches fail the scan.
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
$share_state = isset($argv[3]) && is_file($argv[3]) ? json_decode(file_get_contents($argv[3]), true) : null;
$files = glob("$dir/*.html"); sort($files);
$titles = $descs = array(); $bad = $mismatches = 0;
$expected_b = array(
  'therapeutic-massage' => array('Therapeutic Massage in Portland | Butsaba Dusrai, LMT', 'Therapeutic massage in Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.'),
  'deep-tissue-massage' => array('Deep Tissue Massage in Portland | Butsaba Dusrai, LMT', 'Deep tissue massage in Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.'),
  'chair-massage' => array('Chair Massage in Portland | Butsaba Dusrai, Oregon LMT', 'Chair massage in Portland with Butsaba Dusrai, LMT: short, seated, fully clothed sessions for workplaces and events, with pressure adjusted as you go.'),
  'corporate-massage-portland' => array('Corporate & Group Massage in Portland, OR | Thai Solitude', 'On-site chair massage for employee appreciation days and workplace events in Portland, with table massage by arrangement at suitable host venues. Request a quote.'),
  'privacy-policy' => array('Privacy Policy | Thai Solitude', 'How Thai Solitude handles your information online and as a client of Butsaba Dusrai, LMT: no forms, no tracking, nothing sold or shared for marketing.'),
  'accessibility' => array('Accessibility | Thai Solitude', 'What has been checked on thaisolitude.com for accessibility (WCAG 2.2 A and AA, October 2026), its known limitations, and how to report a barrier.'),
  'services' => array('Massage Services | Butsaba Dusrai, Oregon LMT', 'Thai, therapeutic, deep tissue and chair massage in Portland with Butsaba Dusrai, LMT, plus corporate and group events. Every session starts with your goals.'),
);
$require = function ($ok, $message) use (&$mismatches) { if (!$ok) { echo "  FAIL $message\n"; $mismatches++; } };
$bundle_b = false; $seen_b = array();
libxml_use_internal_errors(true);
foreach ($files as $f) {
  $url = trim(file_get_contents(substr($f, 0, -5) . '.url'));
  if (trim((string) parse_url($url, PHP_URL_PATH), '/') !== 'services') continue;
  $d = new DOMDocument(); $d->loadHTML('<?xml encoding="utf-8"?>' . file_get_contents($f)); $x = new DOMXPath($d);
  $n = $x->query("//meta[@name='description']/@content");
  $bundle_b = $n->length && trim($n->item(0)->nodeValue) === $expected_b['services'][1];
}
echo $bundle_b ? "Bundle B detected: exact metadata, social copies and entity links are required.\n" : "Bundle B not detected: historical-site observations apply.\n";
$fallback_image = is_array($share_state) ? ($share_state['fallback_image'] ?? '') : ''; $seen_home = false;
$approved_share_sha = '7b769b1a2b82b2cb3ef2bf11a5e6ba65f271b963952fdfe1041c53b6ade5319d';
if ($bundle_b) {
  $require(is_array($share_state), 'Bundle B share-card state could not be read from WordPress');
  $require(is_string($fallback_image) && parse_url($fallback_image, PHP_URL_SCHEME) === 'https' && parse_url($fallback_image, PHP_URL_HOST) === parse_url($base, PHP_URL_HOST), 'Bundle B default share image must use HTTPS on this site');
  $require(($share_state['home_image'] ?? null) === $fallback_image && !empty($share_state['attachment_id']), 'Bundle B Home social image and global fallback must reference the approved attachment');
  $require(($share_state['sha256'] ?? '') === $approved_share_sha && ($share_state['width'] ?? 0) === 1200 && ($share_state['height'] ?? 0) === 630 && ($share_state['attachment_width'] ?? 0) === 1200 && ($share_state['attachment_height'] ?? 0) === 630 && ($share_state['mime'] ?? '') === 'image/png', 'Bundle B default/Home share card bytes and attachment dimensions must match the approved 1200x630 PNG');
  echo 'Bundle B share card: ' . $fallback_image . '; attachment ' . ($share_state['attachment_id'] ?? 0) . '; ' . ($share_state['width'] ?? 0) . 'x' . ($share_state['height'] ?? 0) . '; approved SHA256 ' . (($share_state['sha256'] ?? '') === $approved_share_sha ? 'MATCH' : 'MISMATCH') . "\n";
}
foreach ($files as $f) {
  $url = trim(file_get_contents(substr($f, 0, -5) . '.url')); $code = trim(file_get_contents(substr($f, 0, -5) . '.code')); $hdr = file_get_contents(substr($f, 0, -5) . '.hdr');
  $html = file_get_contents($f);
  $slug = trim((string) parse_url($url, PHP_URL_PATH), '/');
  echo "\n== $url  (HTTP $code, " . strlen($html) . " bytes)\n";
  if ($code !== '200') { echo "  FAIL page does not answer 200\n"; $bad++; continue; }
  if (preg_match('/^x-robots-tag:\s*(.*)$/mi', $hdr, $m)) { echo "  X-Robots-Tag: " . trim($m[1]) . (stripos($m[1], 'noindex') !== false ? "   WARN noindex!" : "") . "\n"; }
  libxml_use_internal_errors(true); $d = new DOMDocument(); $d->loadHTML('<?xml encoding="utf-8"?>' . $html); $x = new DOMXPath($d);
  $meta = function ($attr, $name) use ($x) { $n = $x->query("//meta[@$attr='$name']/@content"); return $n->length ? trim($n->item(0)->nodeValue) : null; };
  $t = $x->query('//title'); $title = $t->length ? trim($t->item(0)->textContent) : null; $desc = $meta('name', 'description');
  echo "  title (" . strlen((string) $title) . "): $title\n  description (" . strlen((string) $desc) . "): $desc\n";
  if ($title === null || $title === '') echo "  WARN no title\n"; elseif (mb_strlen($title) > 65) echo "  WARN title longer than 65 characters (may be cut in results)\n";
  if ($desc === null || $desc === '') echo "  WARN no meta description\n";
  elseif (mb_strlen($desc) > 160) {
    if ($slug === 'corporate-massage-portland') {
      $approved_long = $bundle_b && $desc === $expected_b[$slug][1] && mb_strlen($desc) === 162;
      $require($approved_long, 'Corporate description exceeds 160 and is not the exact approved 162-character Bundle B copy');
      if ($approved_long) echo "  INFO exact approved 162-character Corporate description\n";
    } else echo "  WARN description longer than 160 characters\n";
  } elseif (mb_strlen($desc) < 70) echo "  WARN description shorter than 70 characters\n";
  if ($bundle_b && isset($expected_b[$slug])) {
    $seen_b[$slug] = true; list($want_title, $want_desc) = $expected_b[$slug];
    $require($title === $want_title && $desc === $want_desc, 'Bundle B title or description differs from the approved copy');
    foreach (array(array('property', 'og:title', $want_title), array('property', 'og:description', $want_desc), array('name', 'twitter:title', $want_title), array('name', 'twitter:description', $want_desc)) as $field) {
      $require($meta($field[0], $field[1]) === $field[2], 'Bundle B ' . $field[1] . ' differs from the approved copy');
    }
  }
  if ($bundle_b && ($slug === '' || $slug === 'home')) {
    $seen_home = true;
    $require($meta('property', 'og:image') === $fallback_image && $meta('name', 'twitter:image') === $fallback_image, 'Bundle B Home og:image and twitter:image must use the approved HTTPS share card');
    $require($meta('property', 'og:image:width') === '1200' && $meta('property', 'og:image:height') === '630', 'Bundle B Home Open Graph image dimensions must be 1200x630');
  }
  if ($bundle_b && $slug === 'corporate-massage-portland') {
    $corporate_image = rtrim($base, '/') . '/wp-content/uploads/2026/10/corporate-group-massage.webp';
    $require($meta('property', 'og:image') === $corporate_image && $meta('name', 'twitter:image') === $corporate_image, 'Bundle B Corporate og:image and twitter:image must use the approved HTTPS image URL');
  }
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
  $ld = $x->query("//script[@type='application/ld+json']"); $types = $nodes = array(); $ldbad = 0;
  foreach ($ld as $s) { $j = json_decode($s->textContent, true); if ($j === null) { $ldbad++; continue; } if (is_array($j)) { $top = isset($j['@graph']) && is_array($j['@graph']) ? $j['@graph'] : (array_is_list($j) ? $j : array($j)); $nodes = array_merge($nodes, $top); } $stack = array($j); while ($stack) { $n = array_pop($stack); if (!is_array($n)) continue; if (isset($n['@type'])) { $types[] = is_array($n['@type']) ? implode('/', $n['@type']) : $n['@type']; } foreach ($n as $v) { if (is_array($v)) $stack[] = $v; } } }
  echo "  JSON-LD blocks: " . $ld->length . ", unparseable: $ldbad, types: " . implode(', ', array_unique($types)) . "\n"; if ($ldbad) echo "  WARN a JSON-LD block does not parse\n";
  if ($bundle_b) {
    $require($ldbad === 0, 'Bundle B JSON-LD does not parse');
    $home = rtrim($base, '/'); $person_id = $home . '/#person';
    $avatar = $home . '/wp-content/uploads/2026/09/cropped-cropped-butsaba_avatar-1.webp';
    $of_type = function ($type) use ($nodes) { return array_values(array_filter($nodes, function ($node) use ($type) { return is_array($node) && in_array($type, (array) ($node['@type'] ?? array()), true); })); };
    $people = $of_type('Person'); $businesses = $of_type('LocalBusiness');
    $person = count($people) === 1 ? $people[0] : array();
    $business = count($businesses) === 1 ? $businesses[0] : array();
    $require(count($people) === 1 && ($person['@id'] ?? '') === $person_id && ($person['name'] ?? '') === 'Butsaba Dusrai' && ($person['jobTitle'] ?? '') === 'Licensed Massage Therapist' && ($person['url'] ?? '') === $home . '/about/' && ($person['image'] ?? '') === $avatar, 'Bundle B needs one global Person with the fixed home/#person ID and approved identity fields');
    $address = isset($business['address']) && is_array($business['address']) ? $business['address'] : array();
    $require(count($businesses) === 1 && ($business['@id'] ?? '') === $home . '/#localbusiness' && ($business['founder']['@id'] ?? '') === $person_id && ($business['image'] ?? '') === $avatar && ($business['logo'] ?? '') === $avatar && ($business['email'] ?? '') === 'butsabad@gmail.com' && ($business['description'] ?? '') === $expected_b['services'][1] && ($business['areaServed']['name'] ?? '') === 'Portland' && ($address['addressLocality'] ?? '') === 'Portland' && !array_key_exists('postalCode', $address), 'Bundle B LocalBusiness founder, media, email, description or Portland address differs; postalCode must be absent');
    if (in_array($slug, array('about', 'contact'), true)) {
      $organizations = $of_type('Organization'); $organization = count($organizations) === 1 ? $organizations[0] : array();
      $require(count($organizations) === 1 && ($organization['telephone'] ?? '') === '(312) 539-4328' && ($organization['knowsLanguage'] ?? '') === 'Thai, English', 'Bundle B About/Contact Organization must include the published phone and Thai, English languages');
    }
  }
  $imgs = $x->query('//img'); $noalt = 0; foreach ($imgs as $i) { if (!$i->hasAttribute('alt')) $noalt++; }
  echo "  images: " . $imgs->length . ", without an alt attribute: $noalt\n";
  $body = preg_replace('/<(script|style)\b.*?<\/\1>/is', '', $html); $text = html_entity_decode(strip_tags($body), ENT_QUOTES, 'UTF-8'); $meta_all = $html;
  $tab_text = preg_match_all('/Mt\.\s?Tabor/i', $text); $tab_all = preg_match_all('/Mt\.?(%20|\s)Tabor/i', $meta_all); $n1 = preg_match_all('/November 1,/', $text);
  echo "  leftovers: 'Mt. Tabor' in visible text $tab_text, anywhere in the HTML $tab_all; 'November 1,' in visible text $n1\n";
  if ($tab_text) echo "  WARN visible 'Mt. Tabor' wording\n";
  if ($n1 && strpos($url, 'work-with-me') === false) echo "  WARN 'November 1,' still on this page\n";
}
foreach (array('title' => $titles, 'description' => $descs) as $k => $arr) { foreach ($arr as $v => $urls) { if ($v !== '' && count($urls) > 1) echo "\nWARN duplicate $k on " . count($urls) . " pages: " . implode(', ', $urls) . "\n"; } }
if ($bundle_b) { foreach (array_diff_key($expected_b, $seen_b) as $slug => $expected) $require(false, 'Bundle B expected sitemap page missing: /' . $slug . '/'); $require($seen_home, 'Bundle B Home page missing from the scan'); }
echo "\n" . count($files) . " pages scanned; pages not answering 200: $bad; expectation failures: $mismatches\n"; exit(($bad || $mismatches) ? 1 : 0);
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
# Read the configured default/Home image and original file locally. No HTTP
# image fetch, image generation, metadata write or plugin saver is involved.
wp --path="$doc" eval '
$settings = get_option("surerank_settings", array());
$social = get_post_meta((int) get_option("page_on_front"), "surerank_settings_social", true);
$url = $settings["fallback_image"] ?? "";
$id = $url ? attachment_url_to_postid($url) : 0;
$file = $id ? get_attached_file($id) : "";
$size = $file && is_file($file) ? getimagesize($file) : false;
$metadata = $id ? wp_get_attachment_metadata($id) : array();
echo wp_json_encode(array("fallback_image" => $url, "home_image" => $social["facebook_image_url"] ?? null, "attachment_id" => (int) $id, "width" => (int) ($size[0] ?? 0), "height" => (int) ($size[1] ?? 0), "attachment_width" => (int) ($metadata["width"] ?? 0), "attachment_height" => (int) ($metadata["height"] ?? 0), "mime" => $size["mime"] ?? "", "sha256" => $file && is_file($file) ? hash_file("sha256", $file) : ""));
' > "$tmp/share-state.json"
php -r "$PHPSCAN_CODE" "$tmp/pages" "https://$want" "$tmp/share-state.json"
EOS
"${ssh_cmd[@]}" bash -s -- "$target" <<<"PHPSCAN_CODE=$(printf '%q' "${PHPSCAN#<?php}")
$REMOTE"

# ref: ../availability/push-strip-flip.sh — the read-only server-side pattern this follows (connection, install guard, curl from the server)

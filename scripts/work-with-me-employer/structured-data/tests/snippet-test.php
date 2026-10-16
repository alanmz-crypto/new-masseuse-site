<?php
// Offline test (runs in check-all.sh): pull the new snippet and the guard hash out of the installer and run the snippet against stubbed WordPress functions that behave like staging31.
$src = file_get_contents('/pkg/install-structured-data.php');
preg_match("/<<<'CODE'\n(.*?)\nCODE;/s", $src, $m); $new = $m[1] . "\n";
preg_match("/\\\$snippet_old_sha = '([0-9a-f]{64})'/", $src, $g); $guard = $g[1];
$fail = 0; $t = function ($ok, $name, $detail = '') use (&$fail) { echo ($ok ? "PASS " : "FAIL ") . $name . ($detail !== '' ? "  [$detail]" : '') . "\n"; if (!$ok) $fail++; };
$hooks = array();
function add_action($tag, $fn) { global $hooks; $hooks[$tag] = $fn; }
function untrailingslashit($s) { return rtrim($s, '/\\'); }
// Faithful to staging31: the home option is http:// and (as under WP-CLI) home_url() IGNORES a requested scheme; set_url_scheme() works.
function home_url($path = '', $scheme = null) { return 'http://' . $GLOBALS['site'] . $path; }
function set_url_scheme($u, $s) { return preg_replace('#^[a-z]+://#', $s . '://', $u); }
function wp_json_encode($d, $f = 0) { return json_encode($d, $f); }
foreach (array('staging31.thaisolitude.com', 'thaisolitude.com') as $site) {
    $GLOBALS['site'] = $site; $hooks = array();
    eval($new);
    $t(isset($hooks['wp_head']), "[$site] snippet registers a wp_head hook");
    ob_start(); $hooks['wp_head'](); $out = ob_get_clean();
    $t(1 === preg_match('#^<script type="application/ld\+json">(.*)</script>$#s', $out, $mm), "[$site] output is exactly one ld+json script");
    $j = json_decode($mm[1], true);
    $t(is_array($j) && JSON_ERROR_NONE === json_last_error(), "[$site] the JSON parses");
    $t('LocalBusiness' === ($j['@type'] ?? null) && 'Thai Solitude' === ($j['name'] ?? null), "[$site] type and name kept");
    $t("https://$site/#localbusiness" === ($j['@id'] ?? null) && "https://$site" === ($j['url'] ?? null), "[$site] @id and url come from home_url over https", $j['@id'] ?? '');
    $t('+1-312-539-4328' === ($j['telephone'] ?? null), "[$site] phone unchanged");
    $t(!isset($j['priceRange']) && !array_key_exists('streetAddress', $j['address'] ?? array()), "[$site] no priceRange and no empty streetAddress");
    $t(array('@type' => 'PostalAddress', 'addressLocality' => 'Portland', 'addressRegion' => 'OR', 'postalCode' => '97213', 'addressCountry' => 'US') === ($j['address'] ?? null), "[$site] Portland, OR 97213, US address kept");
    $t(false === stripos($out, 'staging31') || 'staging31.thaisolitude.com' === $site, "[$site] no foreign host leaks into the output");
}
// the guard constant must equal sha256 of the real old bytes with the host normalised
$old = file_get_contents('/pkg/tests/fixtures/snippet-1023-old.bin');
$t(hash('sha256', $old) === $guard, 'guard constant equals sha256 of the real old bytes ({HOME}-normalised)', substr($guard, 0, 16));
foreach (array('staging31.thaisolitude.com', 'thaisolitude.com') as $site) {
    $raw = str_replace('{HOME}', "https://$site", $old);
    $t(hash('sha256', str_replace("https://$site", '{HOME}', $raw)) === $guard, "[$site] guard recognises that site's stored form", strlen($raw) . ' bytes');
}
$t($new !== $old && false !== strpos($new, "\n") && false === strpos($new, "\r"), 'new code uses LF line endings');
echo "\nRESULT: " . ($fail ? "$fail FAILED" : 'all passed') . "\n"; exit($fail ? 1 : 0);

// ref: ../install-structured-data.php — the installer under test; tests/fixtures/snippet-1023-old.bin — the real stored bytes of WPCode snippet 1023 with the site address replaced by {HOME}

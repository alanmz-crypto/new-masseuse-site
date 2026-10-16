<?php
/**
 * Apply the approved design to a WordPress site using ONLY WordPress-native storage (no files, no mu-plugins):
 *   Additional CSS (astra custom_css), option ihaf_insert_header, astra-settings[footer-copyright-editor], page post_content (all pages in the release).
 * No argument: read-only. With FILL placeholders it prints the current-state hashes to pin; once pinned it runs the full preflight.
 * apply-native: apply. Idempotent: a value already equal to the release is accepted. Rolls itself back on any mismatch.
 * Run with WP-CLI:  wp eval-file install-native.php [apply-native]     (release JSON via env TS_NATIVE_RELEASE, default ./release.json next to this file)
 */
if (!defined('ABSPATH') || !class_exists('WP_CLI')) { exit('Run with WP-CLI.'); }
$rel_path = getenv('TS_NATIVE_RELEASE') ?: (__DIR__ . '/release.json');
$p = json_decode(file_get_contents($rel_path), true);
if (!$p || ($p['scope'] ?? '') !== 'wordpress-native-sitewide' || rtrim(home_url(), '/') !== $p['target'] || rtrim(site_url(), '/') !== $p['target'] || get_stylesheet() !== 'astra') {
    WP_CLI::error('Refusing: expected target ' . ($p['target'] ?? '?') . ' with the Astra theme and a wordpress-native-sitewide release.');
}
$is_local = (strpos($p['target'], 'http://localhost') === 0);
$ids = array_map('intval', array_keys($p['pages'])); sort($ids);
$posts = [];
foreach ($ids as $id) {
    $pg = get_post($id);
    if (!$pg || $pg->post_type !== 'page' || $pg->post_status !== 'publish' || $pg->post_name !== $p['pages'][$id]['slug']) { WP_CLI::error('Refusing: page ' . $id . ' is not the published "' . $p['pages'][$id]['slug'] . '" page.'); }
    $posts[$id] = $pg;
}
if ((int) get_option('page_on_front') !== (int) $p['front_page_id']) { WP_CLI::error('Refusing: front page differs from the release.'); }
$h = static function ($s) { return hash('sha256', (string) $s); };
$settings = get_option('astra-settings', []);
$cur = ['css' => wp_get_custom_css('astra'), 'header' => (string) get_option('ihaf_insert_header', ''), 'footer' => $settings['footer-copyright-editor'] ?? ''];
$now = ['css' => $h($cur['css']), 'header' => $h($cur['header']), 'footer' => $h($cur['footer'])];
foreach ($ids as $id) { $now['page_' . $id] = $h($posts[$id]->post_content); }
WP_CLI::log('Target: ' . home_url());
WP_CLI::log('Current state sha256: ' . json_encode($now));
$mu = glob(WPMU_PLUGIN_DIR . '/ts-*'); if ($mu) { WP_CLI::warning('Leftover ts-* MU files present (not touched by this installer; remove separately): ' . implode(', ', array_map('basename', $mu))); }
$pending = (strpos($p['accept_before_css_sha256'], 'FILL') === 0) || (strpos($p['accept_before_header_sha256'], 'FILL') === 0) || (strpos($p['footer_accept_before'], 'FILL') === 0);
foreach ($ids as $id) { if (strpos($p['pages'][$id]['accept_before_sha256'], 'FILL') === 0) { $pending = true; } }
if ($pending) { WP_CLI::success('Read-only state read. Pin these values as accept_before_* in the release JSON, then re-run.'); return; }
// pinned: current must equal the pinned pre-state OR already equal the release (idempotent)
$ok = static function ($now_hash, $before, $rel_hash) { return hash_equals($before, $now_hash) || hash_equals($rel_hash, $now_hash); };
foreach ($ids as $id) {
    if (!$ok($now['page_' . $id], $p['pages'][$id]['accept_before_sha256'], $p['pages'][$id]['content_sha256'])) { WP_CLI::error('Refusing: page ' . $id . ' changed since preparation.'); }
    $c = $p['pages'][$id]['content'];
    if (!hash_equals($p['pages'][$id]['content_sha256'], $h($c)) || strpos($c, 'localhost') !== false && !$is_local) { WP_CLI::error('Refusing: page ' . $id . ' release payload invalid.'); }
}
if (!$ok($now['css'], $p['accept_before_css_sha256'], $p['css_sha256'])) { WP_CLI::error('Refusing: Additional CSS changed since preparation.'); }
if (!$ok($now['header'], $p['accept_before_header_sha256'], $p['header_sha256'])) { WP_CLI::error('Refusing: header option changed since preparation.'); }
if ($cur['footer'] !== $p['footer'] && $cur['footer'] !== $p['footer_accept_before']) { WP_CLI::error('Refusing: footer setting changed since preparation.'); }
if (!hash_equals($p['css_sha256'], $h($p['css'])) || !hash_equals($p['header_sha256'], $h($p['header']))) { WP_CLI::error('Refusing: CSS/header payload hash mismatch.'); }
if (substr_count($p['css'], '/* TS RESTYLE START') !== 1 || strpos($p['css'], 'TS FONT INLINE') === false || strpos($p['css'], 'TS HEADER FLASH FIX') === false || strpos($p['css'], 'TS SERVICE IMAGE RESPONSIVE START') === false || strpos($p['css'], 'TS WWM NUMBER CONTRAST START') === false || strpos($p['css'], '@import') !== false) { WP_CLI::error('Refusing: accepted design corrections missing or @import present.'); }
if (strpos($p['header'], 'ts-fade-in') === false) { WP_CLI::error('Refusing: header script missing the fade class.'); }
if (!$is_local) {
    $backup = rtrim(getenv('HOME'), '/') . '/private-backups/' . $p['backup_basename'];
    if (!is_file($backup) || !hash_equals($p['backup_sha256'], hash_file('sha256', $backup))) { WP_CLI::error('Refusing: verified backup missing or changed.'); }
}
global $wpdb;
$settings_other_before = $settings; unset($settings_other_before['footer-copyright-editor']); $settings_other_before = $h(serialize($settings_other_before));
$in = implode(',', $ids);
$others_q = "SELECT ID, post_title, post_content, post_status, post_name FROM {$wpdb->posts} WHERE post_type IN ('page','post') AND ID NOT IN ($in) ORDER BY ID";
$others_before = $h(serialize($wpdb->get_results($others_q, ARRAY_A)));
WP_CLI::log('Release: ' . count($ids) . ' pages, css ' . strlen($p['css']) . ' bytes, header ' . strlen($p['header']) . ' bytes.');
if (($args[0] ?? '') !== 'apply-native') { if (!empty($args)) { WP_CLI::error('Unknown argument.'); } WP_CLI::success('Read-only preflight passed.'); return; }
$rb = 'ts_native_sitewide_before_' . gmdate('Ymd');
if (get_option($rb, false) !== false) { WP_CLI::error('Rollback option ' . $rb . ' exists; inspect before repeating.'); }
$saved = ['css' => $cur['css'], 'header' => $cur['header'], 'footer' => $cur['footer'], 'pages' => []];
foreach ($ids as $id) { $saved['pages'][$id] = $posts[$id]->post_content; }
if (!add_option($rb, $saved, '', false)) { WP_CLI::error('Could not save rollback values.'); }
$done_pages = []; $css_done = false; $hdr_done = false; $ftr_done = false;
try {
    foreach ($ids as $id) {
        $r = wp_update_post(wp_slash(['ID' => $id, 'post_content' => $p['pages'][$id]['content']]), true);
        if (is_wp_error($r)) { throw new RuntimeException($r->get_error_message()); }
        $done_pages[] = $id;
    }
    $r = wp_update_custom_css_post($p['css'], ['stylesheet' => 'astra']); if (is_wp_error($r)) { throw new RuntimeException($r->get_error_message()); } $css_done = true;
    update_option('ihaf_insert_header', $p['header']); $hdr_done = true;
    $s = get_option('astra-settings', []); $s['footer-copyright-editor'] = $p['footer']; update_option('astra-settings', $s); $ftr_done = true;
    foreach ($ids as $id) { if (get_post($id)->post_content !== $p['pages'][$id]['content']) { throw new RuntimeException('Read-back mismatch on page ' . $id); } }
    $s2 = get_option('astra-settings', []); $o2 = $s2; unset($o2['footer-copyright-editor']);
    if (wp_get_custom_css('astra') !== $p['css'] || get_option('ihaf_insert_header', '') !== $p['header'] || ($s2['footer-copyright-editor'] ?? '') !== $p['footer']
        || $h(serialize($o2)) !== $settings_other_before || $h(serialize($wpdb->get_results($others_q, ARRAY_A))) !== $others_before) { throw new RuntimeException('Post-apply read-back or isolation mismatch.'); }
} catch (Throwable $e) {
    foreach ($done_pages as $id) { wp_update_post(wp_slash(['ID' => $id, 'post_content' => $saved['pages'][$id]]), true); }
    if ($css_done) { wp_update_custom_css_post($saved['css'], ['stylesheet' => 'astra']); }
    if ($hdr_done) { update_option('ihaf_insert_header', $saved['header']); }
    if ($ftr_done) { $s = get_option('astra-settings', []); $s['footer-copyright-editor'] = $saved['footer']; update_option('astra-settings', $s); }
    wp_cache_flush(); if (function_exists('sg_cachepress_purge_cache')) { sg_cachepress_purge_cache(); }
    WP_CLI::error($e->getMessage() . '; everything restored to the saved values.');
}
wp_cache_flush(); if (function_exists('sg_cachepress_purge_cache')) { sg_cachepress_purge_cache(); }
WP_CLI::success('Applied (WordPress-native only). Rollback option: ' . $rb);

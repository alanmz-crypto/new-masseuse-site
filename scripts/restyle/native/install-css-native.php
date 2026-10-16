<?php
/**
 * CSS-ONLY native apply: replaces ONLY the Additional CSS (astra custom_css) on a site. Never touches pages, footer, header option or any setting.
 * Use this when content on the target may have been edited since the last full package (staging/production copy edits must never be overwritten).
 * No arg = read-only (prints current hash; with pins filled, runs the full preflight). apply-css = apply. Rolls itself back on mismatch.
 * Release JSON (env TS_CSS_RELEASE or ./release-css.json): {scope:"css-only", target, css, css_sha256, accept_before_css_sha256, backup_basename, backup_sha256}
 */
if (!defined('ABSPATH') || !class_exists('WP_CLI')) { exit('Run with WP-CLI.'); }
$p = json_decode(file_get_contents(getenv('TS_CSS_RELEASE') ?: (__DIR__ . '/release-css.json')), true);
if (!$p || ($p['scope'] ?? '') !== 'css-only' || rtrim(home_url(), '/') !== $p['target'] || rtrim(site_url(), '/') !== $p['target'] || get_stylesheet() !== 'astra') { WP_CLI::error('Refusing: expected target ' . ($p['target'] ?? '?') . ', Astra, and a css-only release.'); }
$h = static function ($s) { return hash('sha256', (string) $s); };
global $wpdb;
$cur = wp_get_custom_css('astra'); $now = $h($cur);
WP_CLI::log('Target: ' . home_url() . ' | current Additional CSS: ' . strlen($cur) . ' bytes, sha256 ' . $now);
if (strpos($p['accept_before_css_sha256'], 'FILL') === 0) { WP_CLI::success('Read-only state read. Pin accept_before_css_sha256, then re-run.'); return; }
if (!hash_equals($p['accept_before_css_sha256'], $now) && !hash_equals($p['css_sha256'], $now)) { WP_CLI::error('Refusing: Additional CSS changed since preparation.'); }
if (!hash_equals($p['css_sha256'], $h($p['css'])) || strpos($p['css'], 'localhost') !== false || strpos($p['css'], '@import') !== false || substr_count($p['css'], '/* TS RESTYLE START') !== 1 || strpos($p['css'], 'TS HERO IMAGE STACKING START') === false) { WP_CLI::error('Refusing: CSS payload invalid.'); }
if (strpos($p['target'], 'http://localhost') !== 0) {
    $backup = rtrim(getenv('HOME'), '/') . '/private-backups/' . $p['backup_basename'];
    if (!is_file($backup) || !hash_equals($p['backup_sha256'], hash_file('sha256', $backup))) { WP_CLI::error('Refusing: verified backup missing or changed.'); }
}
$mu = glob(WPMU_PLUGIN_DIR . '/*'); if ($mu) { WP_CLI::error('Refusing: mu-plugins directory is not empty (no-MU rule).'); }
$fp = static function () use ($wpdb, $h) { return $h(serialize([$wpdb->get_results("SELECT ID, post_title, post_content, post_excerpt, post_status, post_name FROM {$wpdb->posts} WHERE post_type IN ('page','post') ORDER BY ID", ARRAY_A), get_option('ihaf_insert_header', ''), get_option('astra-settings', [])])); };
$before = $fp();
WP_CLI::log('Pages + header option + astra-settings fingerprint: ' . $before);
if (($args[0] ?? '') !== 'apply-css') { if (!empty($args)) { WP_CLI::error('Unknown argument.'); } WP_CLI::success('Read-only CSS-only preflight passed.'); return; }
$rb = 'ts_native_css_before_' . gmdate('Ymd');
if (get_option($rb, false) !== false) { WP_CLI::error('Rollback option ' . $rb . ' exists; inspect before repeating.'); }
if (!add_option($rb, ['css' => $cur], '', false)) { WP_CLI::error('Could not save rollback value.'); }
try {
    $r = wp_update_custom_css_post($p['css'], ['stylesheet' => 'astra']); if (is_wp_error($r)) { throw new RuntimeException($r->get_error_message()); }
    if (wp_get_custom_css('astra') !== $p['css']) { throw new RuntimeException('CSS read-back mismatch.'); }
    if ($fp() !== $before) { throw new RuntimeException('Isolation mismatch: pages/header/settings changed.'); }
} catch (Throwable $e) {
    wp_update_custom_css_post($cur, ['stylesheet' => 'astra']); wp_cache_flush();
    WP_CLI::error($e->getMessage() . '; Additional CSS restored.');
}
wp_cache_flush();
WP_CLI::success('Additional CSS replaced; pages, footer, header script and settings verified unchanged. Rollback option: ' . $rb);

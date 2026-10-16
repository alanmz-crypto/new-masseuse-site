<?php
/** Undo install-native.php using the option it saved (ts_native_sitewide_before_<YYYYMMDD>). WordPress-native only.
 *  usage: TS_ROLLBACK_OPTION=ts_native_sitewide_before_20261004 wp eval-file rollback-native.php [apply-rollback]   (no arg = show what would be restored) */
if (!defined('ABSPATH') || !class_exists('WP_CLI')) { exit('Run with WP-CLI.'); }
$name = getenv('TS_ROLLBACK_OPTION'); if (!$name || strpos($name, 'ts_native_sitewide_before_') !== 0) { WP_CLI::error('Set TS_ROLLBACK_OPTION to a ts_native_sitewide_before_* option name.'); }
$saved = get_option($name, false); if (!is_array($saved) || !isset($saved['pages'], $saved['css'])) { WP_CLI::error('Rollback option not found or malformed.'); }
$h = static function ($s) { return hash('sha256', (string) $s); };
WP_CLI::log('Would restore: css ' . strlen($saved['css']) . ' bytes (sha ' . substr($h($saved['css']), 0, 12) . '), header ' . strlen($saved['header']) . ' bytes, footer, pages ' . implode(',', array_keys($saved['pages'])));
if (($args[0] ?? '') !== 'apply-rollback') { WP_CLI::success('Dry run only.'); return; }
foreach ($saved['pages'] as $id => $content) { $r = wp_update_post(wp_slash(['ID' => (int) $id, 'post_content' => $content]), true); if (is_wp_error($r)) { WP_CLI::error('Page ' . $id . ': ' . $r->get_error_message()); } }
$r = wp_update_custom_css_post($saved['css'], ['stylesheet' => 'astra']); if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
update_option('ihaf_insert_header', $saved['header']);
$s = get_option('astra-settings', []); $s['footer-copyright-editor'] = $saved['footer']; update_option('astra-settings', $s);
foreach ($saved['pages'] as $id => $content) { if (get_post((int) $id)->post_content !== $content) { WP_CLI::error('Read-back mismatch on page ' . $id); } }
if (wp_get_custom_css('astra') !== $saved['css'] || get_option('ihaf_insert_header', '') !== $saved['header']) { WP_CLI::error('Read-back mismatch on CSS/header.'); }
delete_option($name); wp_cache_flush(); if (function_exists('sg_cachepress_purge_cache')) { sg_cachepress_purge_cache(); }
WP_CLI::success('Rolled back and removed ' . $name . '.');

<?php
/** Restore the Additional CSS saved by install-css-native.php. usage: TS_ROLLBACK_OPTION=ts_native_css_before_YYYYMMDD wp eval-file rollback-css-native.php [apply-rollback] */
if (!defined('ABSPATH') || !class_exists('WP_CLI')) { exit('Run with WP-CLI.'); }
$n = getenv('TS_ROLLBACK_OPTION'); if (!$n || strpos($n, 'ts_native_css_before_') !== 0) { WP_CLI::error('Set TS_ROLLBACK_OPTION to a ts_native_css_before_* name.'); }
$s = get_option($n, false); if (!is_array($s) || !isset($s['css'])) { WP_CLI::error('Rollback option not found.'); }
WP_CLI::log('Would restore Additional CSS: ' . strlen($s['css']) . ' bytes, sha256 ' . hash('sha256', $s['css']));
if (($args[0] ?? '') !== 'apply-rollback') { WP_CLI::success('Dry run only.'); return; }
$r = wp_update_custom_css_post($s['css'], ['stylesheet' => 'astra']); if (is_wp_error($r)) { WP_CLI::error($r->get_error_message()); }
if (wp_get_custom_css('astra') !== $s['css']) { WP_CLI::error('Read-back mismatch.'); }
delete_option($n); wp_cache_flush(); WP_CLI::success('Additional CSS restored; removed ' . $n);

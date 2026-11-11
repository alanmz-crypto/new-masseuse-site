<?php
// Offline WordPress model: exact content-only transition and refusal on drift.
error_reporting( E_ALL );
$testdir = sys_get_temp_dir() . '/rail-input-installer-' . bin2hex( random_bytes( 5 ) );
mkdir( $testdir ); mkdir( $testdir . '/web' ); mkdir( $testdir . '/backups' );
define( 'ABSPATH', $testdir . '/web/' ); define( 'ARRAY_A', 'ARRAY_A' );
putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
$home = 'http://localhost:8090'; $theme = 'astra'; $admin = true; $db = array(); $updates = 0; $deletes = 0; $captures = 0; $race = false;
$fail_write = false; $fail_db_error = false; $wrong_row_count = false; $fail_after_write = false; $fail_snapshot_complete = false; $concurrent_drift = false; $cas_race = ''; $message = ''; $pass = 0;
$cache = array(); $cache_events = array(); $queries = 0; $last_changed = 0; $save_hooks = 0; $logs = array();
class WP_CLI { static function error( $m ) { throw new RuntimeException( $m ); } static function success( $m ) { $GLOBALS['message'] = $m; } static function log( $m ) { $GLOBALS['logs'][] = $m; } }
function home_url() { return $GLOBALS['home']; }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function wp_parse_url( $s, $component = -1 ) { return parse_url( $s, $component ); }
function get_stylesheet() { return $GLOBALS['theme']; }
function current_user_can( $c ) { return $GLOBALS['admin']; }
function get_object_taxonomies( $type ) { return array( 'page_topic' ); }
function get_post_stati() { return array( 'publish', 'draft', 'trash' ); }
function get_posts( $args ) {
	$GLOBALS['captures']++;
	if ( $GLOBALS['race'] && $GLOBALS['captures'] === 2 ) { if ( $GLOBALS['race'] === 'duplicate' ) { $GLOBALS['db'][9999] = $GLOBALS['db'][1348]; $GLOBALS['db'][9999]['id'] = $GLOBALS['db'][9999]['post']['ID'] = 9999; } else { $GLOBALS['db'][1348]['meta']['independent'] = array( 'new editor value' ); } }
	if ( ! isset( $args['name'] ) && ( $args['cache_results'] ?? true ) !== false ) { throw new RuntimeException( 'Ownership enumeration must bypass the query cache.' ); }
	$pages = array_filter( $GLOBALS['db'], static function ( $r ) use ( $args ) { $statuses = $args['post_status'] === 'any' ? array( 'publish', 'draft' ) : (array) $args['post_status']; return $r['post']['post_type'] === 'page' && in_array( $r['post']['post_status'], $statuses, true ) && ( ! isset( $args['name'] ) || $r['post']['post_name'] === $args['name'] ); } );
	return array_map( static fn( $r ) => (object) $r['post'], array_values( $pages ) );
}
function get_post( $id, $out = null, $context = null ) { $p = $GLOBALS['cache']['posts'][$id] ??= ( $GLOBALS['db'][$id]['post'] ?? null ); return $out === ARRAY_A ? $p : ( $p ? (object) $p : null ); }
function get_post_meta( $id, $key = '', $single = false ) { $m = $GLOBALS['cache']['post_meta'][$id] ??= ( $GLOBALS['db'][$id]['meta'] ?? array() ); return $key === '' ? $m : ( $single ? ( $m[$key][0] ?? '' ) : ( $m[$key] ?? array() ) ); }
function wp_get_object_terms( $id, $taxonomy, $args ) { if ( ( $args['cache_results'] ?? true ) !== false ) { throw new RuntimeException( 'Terms must bypass the relationship query cache.' ); } return $GLOBALS['db'][$id]['terms'][$taxonomy] ?? array(); }
function is_wp_error( $v ) { return false; }
function wp_json_encode( $v, $flags = 0 ) { if ( $GLOBALS['fail_snapshot_complete'] && is_array( $v ) && ( $v['complete'] ?? false ) === true ) { $GLOBALS['fail_snapshot_complete'] = false; return false; } return json_encode( $v, $flags ); }
function wp_slash( $v ) { return is_array( $v ) ? array_map( 'wp_slash', $v ) : ( is_string( $v ) ? addslashes( $v ) : $v ); }
function wp_unslash( $v ) { return is_array( $v ) ? array_map( 'wp_unslash', $v ) : ( is_string( $v ) ? stripslashes( $v ) : $v ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function wp_update_post( $arr, $error = false ) { $GLOBALS['save_hooks']++; throw new RuntimeException( 'General save hooks must not run.' ); }
function clean_post_cache( $id ) { $GLOBALS['save_hooks']++; throw new RuntimeException( 'General cache hooks must not run.' ); }
function wp_cache_delete( $id, $group ) { if ( $id !== 1348 || ! in_array( $group, array( 'posts', 'post_meta' ), true ) ) { throw new RuntimeException( 'Unexpected cache invalidation scope.' ); } $GLOBALS['cache_events'][] = array( $id, $group ); unset( $GLOBALS['cache'][$group][$id] ); return true; }
function wp_cache_set_posts_last_changed() { $GLOBALS['last_changed']++; }
class Rail_Test_WPDB {
	public $posts = 'wp_posts'; public $prepared = array(); public $last_args = array();
	function prepare( $format, ...$args ) {
		if ( $format !== 'UPDATE wp_posts SET post_content = %s WHERE ID = %d AND BINARY post_content = BINARY %s' || count( $args ) !== 3 || $args[1] !== 1348 || ! is_string( $args[0] ) || ! is_string( $args[2] ) ) { throw new RuntimeException( 'Only prepared byte-exact content CAS on page 1348 is allowed.' ); }
		$key = 'prepared:' . count( $this->prepared ); $this->prepared[$key] = $args; $this->last_args = $args; return $key;
	}
	function query( $statement ) {
		if ( ! isset( $this->prepared[$statement] ) ) { throw new RuntimeException( 'Unprepared query refused.' ); }
		$GLOBALS['queries']++; list( $new, $id, $old ) = $this->prepared[$statement];
		if ( $GLOBALS['fail_write'] ) { $GLOBALS['fail_write'] = false; return 0; }
		if ( $GLOBALS['fail_db_error'] ) { $GLOBALS['fail_db_error'] = false; return false; }
		if ( $GLOBALS['cas_race'] === 'case' ) { $GLOBALS['db'][$id]['post']['post_content'] = preg_replace( '/wp:html/', 'WP:html', $old, 1 ); }
		if ( $GLOBALS['cas_race'] === 'trailing_space' ) { $GLOBALS['db'][$id]['post']['post_content'] = $old . ' '; }
		if ( ( $GLOBALS['db'][$id]['post']['post_content'] ?? null ) !== $old ) { return 0; }
		$GLOBALS['db'][$id]['post']['post_content'] = $new; $GLOBALS['updates']++;
		if ( $GLOBALS['concurrent_drift'] ) { $GLOBALS['db'][$id]['post']['post_parent'] = '0'; $GLOBALS['db'][$id]['post']['comment_status'] = 'SYNTHETIC_PRIVATE_SAVE_VALUE'; $GLOBALS['db'][$id]['meta']['unknown_setting'][0] = 'SYNTHETIC_PRIVATE_META_VALUE'; $GLOBALS['db'][$id]['meta']['new_hook_setting'] = array( 'SYNTHETIC_PRIVATE_ADDED_VALUE' ); $GLOBALS['db'][$id]['terms']['page_topic'][] = 'SYNTHETIC_PRIVATE_TERM_VALUE'; }
		if ( $GLOBALS['fail_after_write'] ) { $GLOBALS['fail_after_write'] = false; throw new RuntimeException( 'Forced failure after recognized write' ); }
		if ( $GLOBALS['wrong_row_count'] ) { $GLOBALS['wrong_row_count'] = false; return 2; }
		return 1;
	}
}
$wpdb = new Rail_Test_WPDB();
function wp_delete_post( $id, $force ) { $p = $GLOBALS['db'][$id] ?? null; unset( $GLOBALS['db'][$id] ); $GLOBALS['deletes']++; return $p; }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $label ); } $GLOBALS['pass']++; }
function invoke( $args, $legacy = false ) { $GLOBALS['message'] = ''; $GLOBALS['logs'] = array(); $GLOBALS['captures'] = 0; $GLOBALS['cache'] = array(); if ( $legacy ) { require __DIR__ . '/../install-rail-ideas.php'; } else { require __DIR__ . '/install-rail-input.php'; } return $GLOBALS['message']; }
function refusal( $args, $label, $legacy = false ) { $before = $GLOBALS['db']; $writes = $GLOBALS['updates'] + $GLOBALS['deletes']; $error = null; try { invoke( $args, $legacy ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before && $writes === $GLOBALS['updates'] + $GLOBALS['deletes'], $label ); }
function observe( $args ) { $GLOBALS['message'] = ''; $GLOBALS['logs'] = array(); $GLOBALS['captures'] = 0; $GLOBALS['cache'] = array(); require __DIR__ . '/observe-state.php'; return $GLOBALS['message']; }
function observe_refusal( $args, $label ) { $before = $GLOBALS['db']; $writes = $GLOBALS['queries'] + $GLOBALS['deletes']; $error = null; try { observe( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before && $writes === $GLOBALS['queries'] + $GLOBALS['deletes'], $label ); }
function observation_path() { if ( ! preg_match( '/Observation: (.+)$/', $GLOBALS['message'], $m ) ) { throw new RuntimeException( 'Observation path missing' ); } return $m[1]; }
function observation_write( $data ) { $file = $GLOBALS['testdir'] . '/backups/rail-input-observe-20261109-000000-' . bin2hex( random_bytes( 4 ) ) . '.json'; file_put_contents( $file, json_encode( $data ) ); chmod( $file, 0600 ); return $file; }
function snap_write( $snap, $name ) { $file = $GLOBALS['testdir'] . '/backups/' . $name . '.json'; file_put_contents( $file, json_encode( $snap ) ); return $file; }
function snapshot_path() { if ( ! preg_match( '/Before-snapshot: (.+)$/', $GLOBALS['message'], $m ) ) { throw new RuntimeException( 'Snapshot path missing' ); } return $m[1]; }
$content = "<!-- wp:html -->\n" . rtrim( file_get_contents( __DIR__ . '/../rail-ideas-v1.html' ) ) . "\n<!-- /wp:html -->";
$record = array( 'id' => 1348, 'post' => array( 'ID' => 1348, 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'rail-nav-ideas', 'post_title' => 'Side rail ideas', 'post_content' => $content, 'post_author' => '1', 'post_parent' => 0, 'post_excerpt' => '', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_date' => '2026-11-07 18:42:14', 'post_date_gmt' => '2026-11-08 02:42:14', 'post_modified' => '2026-11-07 18:42:14', 'post_modified_gmt' => '2026-11-08 02:42:14', 'guid' => 'http://localhost:8090/?page_id=1348' ), 'meta' => array( '_ts_rail_ideas' => array( '1' ), 'site-post-title' => array( 'disabled' ), 'surerank_settings_general' => array( 'a:1:{s:5:"title";s:11:"Keep my SEO";}' ), 'surerank_seo_checks_last_updated' => array( '1794081134' ), '_uag_css_file_name' => array( 'uag-css-1348.css' ), '_uag_page_assets' => array( 'a:0:{}' ), 'unknown_setting' => array( 'preserve', 'both values' ) ), 'terms' => array( 'page_topic' => array( 'practice' ) ) );
ksort( $record['meta'] ); $db[1348] = $record;
$db[900] = array( 'post' => array( 'ID' => 900, 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'unrelated', 'post_title' => 'Unrelated', 'post_content' => 'Untouched content' ), 'meta' => array(), 'terms' => array() );
$original = $db;
check( hash( 'sha256', $content ) === '0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748', 'exact inspected frozen content' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'http://localhost:8091', 'https://staging39.thaisolitude.com:443', 'https://staging39.thaisolitude.com/other', 'https://staging39.thaisolitude.com.evil.test' ) as $bad ) { $home = $bad; refusal( array( 'apply' ), 'refuse exact origin ' . $bad ); }
$home = 'http://localhost:8090'; $theme = 'other'; refusal( array( 'apply' ), 'non-Astra refused' ); $theme = 'astra'; $admin = false; refusal( array( 'apply' ), 'non-admin refused' ); $admin = true;
foreach ( array( array( 'unknown' ), array( 'apply', 'extra' ), array( 'rollback' ), array( 'rollback', 'x', 'extra' ), array( 'verify' ), array( 'verify', 'x', 'extra' ), array( 'verify', 'x', 'original', 'extra' ) ) as $args ) { refusal( $args, 'bad mode/argument count' ); }
invoke( array() ); check( $db === $original && str_contains( $message, 'Preflight ready' ), 'default preflight read-only' );
foreach ( array( array(), array( 'record', 'extra' ), array( 'compare' ), array( 'compare', 'x', 'waive' ) ) as $args ) { observe_refusal( $args, 'observation refuses invalid mode/argument count' ); }
$observation_queries = $queries; observe( array( 'record' ) ); $cold_path = observation_path(); $cold = json_decode( file_get_contents( $cold_path ), true ); $cold_hash = hash_file( 'sha256', $cold_path );
check( $db === $original && $queries === $observation_queries && ( fileperms( $cold_path ) & 0777 ) === 0600 && $cold['state'] === $original[1348], 'private observation captures exact original page without a database write' );
$asset = $original[1348]['meta']['_uag_page_assets'][0]; $observation_logs = implode( '\n', $logs );
check( str_contains( $observation_logs, 'type=string bytes=' . strlen( $asset ) ) && str_contains( $observation_logs, 'byte_sha256=' . hash( 'sha256', $asset ) ) && str_contains( $observation_logs, 'serialize_sha256=' . hash( 'sha256', serialize( $asset ) ) ) && ! str_contains( $observation_logs, $asset ), 'observation logs raw asset type/length and both hashes without its value' );
observe( array( 'compare', $cold_path ) ); check( $db === $original && $queries === $observation_queries && hash_file( 'sha256', $cold_path ) === $cold_hash, 'strict stable comparison writes neither WordPress nor observation' );
observe( array( 'compare', $cold_path, 'cold-assets' ) ); check( str_contains( $message, 'unchanged after' ), 'cold comparison accepts already warm unchanged state' );
$db[1348]['meta']['_uag_page_assets'][0] = 'SYNTHETIC_PRIVATE_WARM_ASSET'; $warm = $db;
observe_refusal( array( 'compare', $cold_path ), 'strict comparison refuses frontend asset drift' );
observe( array( 'compare', $cold_path, 'cold-assets' ) );
check( $db === $warm && $queries === $observation_queries && hash_file( 'sha256', $cold_path ) === $cold_hash && str_contains( $message, 'Only _uag_page_assets changed' ) && ! str_contains( implode( '\n', $logs ), 'SYNTHETIC_PRIVATE' ), 'cold classification permits only asset drift without writing, rebasing or exposing its value' );
observe( array( 'record' ) ); $warm_path = observation_path(); observe( array( 'compare', $warm_path ) ); check( $db === $warm && $queries === $observation_queries && $warm_path !== $cold_path, 'second complete warm observation is stable and separately retained' );
foreach ( array( 'meta', 'post', 'terms', 'content' ) as $mutation ) {
	$db = $warm;
	if ( $mutation === 'meta' ) { $db[1348]['meta']['unknown_setting'][0] = 'new'; }
	if ( $mutation === 'post' ) { $db[1348]['post']['post_excerpt'] = 'new'; }
	if ( $mutation === 'terms' ) { $db[1348]['terms']['page_topic'][] = 'other'; }
	if ( $mutation === 'content' ) { $db[1348]['post']['post_content'] .= 'edited'; }
	observe_refusal( array( 'compare', $cold_path, 'cold-assets' ), 'cold classification refuses unrelated ' . $mutation . ' change' );
}
$db = $original;
foreach ( array( 'home', 'bundle', 'payload', 'hash', 'id_rehashed', 'unknown_meta_rehashed' ) as $mutation ) {
	$o = $cold;
	if ( $mutation === 'home' ) { $o['home'] = 'https://staging39.thaisolitude.com'; }
	if ( $mutation === 'bundle' ) { $o['bundle'] = 'rail-input-v1'; }
	if ( $mutation === 'payload' ) { $o['payloads']['new_content_sha256'] = str_repeat( '0', 64 ); }
	if ( $mutation === 'hash' ) { $o['state_sha256'] = str_repeat( '0', 64 ); }
	if ( $mutation === 'id_rehashed' ) { $o['state']['id'] = 9999; $o['state_sha256'] = hash( 'sha256', json_encode( $o['state'] ) ); }
	if ( $mutation === 'unknown_meta_rehashed' ) { $o['state']['meta']['unknown_setting'][0] = 'forged'; $o['state_sha256'] = hash( 'sha256', json_encode( $o['state'] ) ); }
	observe_refusal( array( 'compare', observation_write( $o ), 'cold-assets' ), 'observation refuses forged ' . $mutation );
}
$obs_outside = $testdir . '/' . basename( $cold_path ); copy( $cold_path, $obs_outside ); chmod( $obs_outside, 0600 ); observe_refusal( array( 'compare', $obs_outside ), 'observation refuses outside private directory' );
$obs_public = observation_write( $cold ); chmod( $obs_public, 0644 ); observe_refusal( array( 'compare', $obs_public ), 'observation refuses broad permissions' );
putenv( 'WWM_BACKUP_DIR=' . ABSPATH ); observe_refusal( array( 'record' ), 'observation refuses webroot storage' ); putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
foreach ( array( 'id', 'slug', 'title', 'status', 'trash', 'code', 'owner', 'owner_duplicate', 'title_setting', 'date', 'duplicate', 'duplicate_other_slug', 'duplicate_trash' ) as $mutation ) {
	$db = $original;
	if ( $mutation === 'id' ) { $db[9999] = $db[1348]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; unset( $db[1348] ); }
	if ( $mutation === 'slug' ) { $db[1348]['post']['post_name'] = 'other'; }
	if ( $mutation === 'title' ) { $db[1348]['post']['post_title'] .= ' edited'; }
	if ( $mutation === 'status' ) { $db[1348]['post']['post_status'] = 'draft'; }
	if ( $mutation === 'trash' ) { $db[1348]['post']['post_status'] = 'trash'; }
	if ( $mutation === 'code' ) { $db[1348]['post']['post_content'] .= 'edited'; }
	if ( $mutation === 'owner' ) { unset( $db[1348]['meta']['_ts_rail_ideas'] ); }
	if ( $mutation === 'owner_duplicate' ) { $db[1348]['meta']['_ts_rail_ideas'][] = '1'; }
	if ( $mutation === 'title_setting' ) { $db[1348]['meta']['site-post-title'] = array( 'enabled' ); }
	if ( $mutation === 'date' ) { $db[1348]['post']['post_modified_gmt'] = '2099-01-01 00:00:00'; }
	if ( in_array( $mutation, array( 'duplicate', 'duplicate_other_slug', 'duplicate_trash' ), true ) ) { $db[9999] = $db[1348]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; if ( $mutation !== 'duplicate' ) { $db[9999]['post']['post_name'] = 'renamed-copy'; $db[9999]['post']['post_title'] = 'Renamed copy'; } if ( $mutation === 'duplicate_trash' ) { $db[9999]['post']['post_status'] = 'trash'; } }
	refusal( array( 'apply' ), 'apply refuses ' . $mutation );
}
$db = $original;
foreach ( array( '', '.', ABSPATH ) as $dir ) { putenv( 'WWM_BACKUP_DIR=' . $dir ); refusal( array( 'apply' ), 'private directory required: ' . $dir ); }
putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
foreach ( array( 'fail_write', 'fail_db_error' ) as $flag ) { $GLOBALS[$flag] = true; $generation = $last_changed; refusal( array( 'apply' ), $flag . ' leaves original intact' ); check( $last_changed === $generation + 1, 'failed CAS still invalidates query cache before recovery read' ); }
foreach ( array( 'fail_after_write', 'fail_snapshot_complete', 'wrong_row_count' ) as $flag ) {
	$GLOBALS[$flag] = true; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
	check( $error !== null && $db === $original, $flag . ' restores complete original state' );
}
$race = true; $writes = $updates; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $updates === $writes && $db[1348]['post']['post_content'] === $content && $db[1348]['meta']['independent'] === array( 'new editor value' ), 'concurrent independent edit is retained without overwrite' ); $race = false; $db = $original;
$race = 'duplicate'; $writes = $updates; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $updates === $writes && isset( $db[9999] ) && $db[1348] === $original[1348], 'fresh ownership enumeration sees a late duplicate and writes nothing' ); $race = false; $db = $original;
foreach ( array( 'case', 'trailing_space' ) as $cas_race ) {
	$writes = $updates; $generation = $last_changed; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
	$changed_content = $cas_race === 'case' ? preg_replace( '/wp:html/', 'WP:html', $content, 1 ) : $content . ' ';
	check( $error !== null && $updates === $writes && $db[1348]['post']['post_content'] === $changed_content && $last_changed === $generation + 1, 'byte-exact CAS refuses concurrent ' . $cas_race . ' change without overwriting it' ); $db = $original;
}
$cas_race = ''; $concurrent_drift = true; $writes = $updates; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $updates === $writes + 1 && $db[1348]['meta']['new_hook_setting'] === array( 'SYNTHETIC_PRIVATE_ADDED_VALUE' ), 'fresh post/meta read sees concurrent drift despite prior cached values and refuses recovery overwrite' );
$diagnostic = $error ? $error->getMessage() : '';
foreach ( array( 'state["post"]["post_parent"]', 'state["post"]["comment_status"]', 'state["meta"]["unknown_setting"][0]', 'state["meta"]["new_hook_setting"]', 'state["terms"]["page_topic"][1]' ) as $path ) { check( str_contains( $diagnostic, $path ), 'diagnostic identifies changed field path: ' . $path ); }
check( str_contains( $diagnostic, 'expected integer sha256=' ) && str_contains( $diagnostic, 'actual string sha256=' ) && str_contains( $diagnostic, 'expected absent actual array sha256=' ) && ! str_contains( $diagnostic, 'SYNTHETIC_PRIVATE' ), 'diagnostic preserves datatype and absent-value distinctions without exposing any stored value' );
$concurrent_drift = false; $db = $original;
$generation = $last_changed; invoke( array( 'apply' ) ); $path = snapshot_path(); $applied = $db; $snapshot = json_decode( file_get_contents( $path ), true );
$expected = $original; $expected[1348]['post']['post_content'] = "<!-- wp:html -->\n" . rtrim( file_get_contents( __DIR__ . '/../rail-ideas.html' ) ) . "\n<!-- /wp:html -->";
check( $applied === $expected, 'only content changed; original dates/ID/meta/SEO/terms/other page stay exact' );
check( $wpdb->last_args === array( $expected[1348]['post']['post_content'], 1348, $content ), 'prepared parameters preserve complete source bytes, quotes, backslashes and percent signs' );
check( $last_changed === $generation + 1 && $save_hooks === 0 && in_array( array( 1348, 'posts' ), $cache_events, true ) && in_array( array( 1348, 'post_meta' ), $cache_events, true ), 'targeted object/query caches refreshed without general save or cache hooks' );
check( true === $snapshot['complete'] && ( fileperms( $path ) & 0777 ) === 0600, 'completed private snapshot persisted' );
$verify_queries = $queries; $snapshot_hash = hash_file( 'sha256', $path ); invoke( array( 'verify', $path ) );
check( $db === $applied && $queries === $verify_queries && hash_file( 'sha256', $path ) === $snapshot_hash && str_contains( $message, 'verified read-only' ), 'verify proves exact applied state without SQL or snapshot writes' );
refusal( array( 'verify', $path, 'original' ), 'original verification refuses the applied state' );
observe_refusal( array( 'record' ), 'observation cannot rebase an applied page' );
refusal( array( 'verify', $cold_path ), 'installer verify refuses an observation in place of its complete install snapshot' );
$writes = $updates; invoke( array( 'apply' ) ); check( $db === $applied && $updates === $writes, 'idempotent apply writes nothing' );
foreach ( array( 'code', 'title', 'status', 'slug', 'meta', 'assets', 'terms', 'date', 'author', 'excerpt', 'duplicate', 'duplicate_trash' ) as $mutation ) {
	$db = $applied;
	if ( $mutation === 'code' ) { $db[1348]['post']['post_content'] .= 'edited'; }
	if ( $mutation === 'title' ) { $db[1348]['post']['post_title'] .= 'edited'; }
	if ( $mutation === 'status' ) { $db[1348]['post']['post_status'] = 'draft'; }
	if ( $mutation === 'slug' ) { $db[1348]['post']['post_name'] = 'other'; }
	if ( $mutation === 'meta' ) { $db[1348]['meta']['unknown_setting'][] = 'changed'; }
	if ( $mutation === 'assets' ) { $db[1348]['meta']['_uag_page_assets'][0] .= 'changed'; }
	if ( $mutation === 'terms' ) { $db[1348]['terms']['page_topic'][] = 'other'; }
	if ( $mutation === 'date' ) { $db[1348]['post']['post_modified'] = '2099-01-01 00:00:00'; }
	if ( $mutation === 'author' ) { $db[1348]['post']['post_author'] = '2'; }
	if ( $mutation === 'excerpt' ) { $db[1348]['post']['post_excerpt'] = 'new'; }
	if ( $mutation === 'duplicate' || $mutation === 'duplicate_trash' ) { $db[9999] = $db[1348]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; if ( $mutation === 'duplicate_trash' ) { $db[9999]['post']['post_status'] = 'trash'; } }
	refusal( array( 'rollback', $path ), 'rollback refuses full-state drift: ' . $mutation );
	refusal( array( 'verify', $path ), 'verify refuses full-state drift: ' . $mutation );
}
$db = $applied;
foreach ( array( 'home', 'bundle', 'hash', 'incomplete', 'id', 'payload', 'old_meta_rehashed', 'old_date_rehashed' ) as $mutation ) {
	$s = $snapshot;
	if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
	if ( $mutation === 'bundle' ) { $s['bundle'] = 'rail-ideas-v1'; }
	if ( $mutation === 'hash' ) { $s['new_sha256'] = str_repeat( '0', 64 ); }
	if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
	if ( $mutation === 'id' ) { $s['new']['id'] = 9999; }
	if ( $mutation === 'payload' ) { $s['payloads']['old_content_sha256'] = str_repeat( '0', 64 ); }
	if ( $mutation === 'old_meta_rehashed' ) { $s['old']['meta']['unknown_setting'] = array( 'forged' ); $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
	if ( $mutation === 'old_date_rehashed' ) { $s['old']['post']['post_modified'] = '2099-01-01 00:00:00'; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
	refusal( array( 'rollback', snap_write( $s, 'bad-' . $mutation ) ), 'snapshot refuses ' . $mutation );
	refusal( array( 'verify', snap_write( $s, 'bad-verify-' . $mutation ) ), 'verify snapshot refuses ' . $mutation );
}
$outside = $testdir . '/outside.json'; file_put_contents( $outside, json_encode( $snapshot ) ); refusal( array( 'rollback', $outside ), 'outside snapshot refused' );
$truncated = $testdir . '/backups/truncated.json'; file_put_contents( $truncated, '{"bundle":' ); refusal( array( 'rollback', $truncated ), 'truncated snapshot refused' );
$generation = $last_changed; invoke( array( 'rollback', $path ) ); check( $db === $original, 'exact full-state rollback' );
check( $wpdb->last_args === array( $content, 1348, $applied[1348]['post']['post_content'] ) && $last_changed === $generation + 1 && $save_hooks === 0, 'rollback uses exact reverse content CAS and refreshes cache without save hooks' );
refusal( array( 'verify', $path ), 'verify refuses the original restored state as not applied' );
$verify_queries = $queries; invoke( array( 'verify', $path, 'original' ) );
check( $db === $original && $queries === $verify_queries && hash_file( 'sha256', $path ) === $snapshot_hash && str_contains( $message, 'Exact original Rail state verified read-only' ), 'original verification proves restored full state without SQL or snapshot writes' );
foreach ( array( '_uag_page_assets', 'unknown_setting' ) as $key ) { $db[1348]['meta'][$key][0] .= 'changed'; refusal( array( 'verify', $path, 'original' ), 'original verification refuses post-render drift: ' . $key ); $db = $original; }
$writes = $updates; invoke( array( 'rollback', $path ) ); check( $db === $original && $writes === $updates, 'idempotent rollback writes nothing' );
invoke( array( 'apply' ) ); check( $db === $applied, 'reapply exactly reproduces reviewed state' );
// Original creation snapshots must never delete the later update or changed v1.
$legacy = array( 'home' => $home, 'slug' => 'rail-nav-ideas', 'page_id' => 1348, 'content_sha256' => hash( 'sha256', $content ) );
$legacy_path = snap_write( $legacy, 'legacy' );
refusal( array( 'rollback', $legacy_path ), 'legacy rollback refuses new input version', true );
foreach ( array( 'content', 'hash', 'missing_hash', 'id', 'title', 'status', 'trash', 'owner', 'title_setting', 'duplicate', 'duplicate_trash' ) as $mutation ) {
	$db = $original; $s = $legacy;
	if ( $mutation === 'content' ) { $db[1348]['post']['post_content'] .= 'changed'; }
	if ( $mutation === 'hash' ) { $s['content_sha256'] = str_repeat( '0', 64 ); }
	if ( $mutation === 'missing_hash' ) { unset( $s['content_sha256'] ); }
	if ( $mutation === 'id' ) { $s['page_id'] = 9999; }
	if ( $mutation === 'title' ) { $db[1348]['post']['post_title'] = 'Other page'; }
	if ( $mutation === 'status' ) { $db[1348]['post']['post_status'] = 'draft'; }
	if ( $mutation === 'trash' ) { $db[1348]['post']['post_status'] = 'trash'; }
	if ( $mutation === 'owner' ) { $db[1348]['meta']['_ts_rail_ideas'] = array( '0' ); }
	if ( $mutation === 'title_setting' ) { $db[1348]['meta']['site-post-title'] = array( 'enabled' ); }
	if ( $mutation === 'duplicate' || $mutation === 'duplicate_trash' ) { $db[9999] = $db[1348]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; if ( $mutation === 'duplicate_trash' ) { $db[9999]['post']['post_status'] = 'trash'; } }
	refusal( array( 'rollback', snap_write( $s, 'legacy-' . $mutation ) ), 'legacy removal refuses ' . $mutation, true );
}
$db = $original; invoke( array( 'preflight' ), true ); check( $db === $original, 'legacy creation preflight still recognizes frozen v1' );
invoke( array( 'rollback', $legacy_path ), true ); check( ! isset( $db[1348] ) && $db[900] === $original[900], 'matching legacy snapshot removes only owned exact v1 in the mock' );
invoke( array( 'rollback', $legacy_path ), true ); check( ! isset( $db[1348] ), 'legacy removal is idempotent in the mock' );
echo 'PASS rail input installer: ' . $pass . " checks; exact content-only apply/rollback, preserved unknown state and legacy deletion guards.\n";

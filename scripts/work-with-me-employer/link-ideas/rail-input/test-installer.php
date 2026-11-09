<?php
// Offline WordPress model: exact content-only transition and refusal on drift.
error_reporting( E_ALL );
$testdir = sys_get_temp_dir() . '/rail-input-installer-' . bin2hex( random_bytes( 5 ) );
mkdir( $testdir ); mkdir( $testdir . '/web' ); mkdir( $testdir . '/backups' );
define( 'ABSPATH', $testdir . '/web/' ); define( 'ARRAY_A', 'ARRAY_A' );
putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
$home = 'http://localhost:8090'; $theme = 'astra'; $admin = true; $filters = array(); $db = array(); $updates = 0; $deletes = 0; $captures = 0; $race = false;
$fail_write = false; $fail_after_write = false; $fail_snapshot_complete = false; $message = ''; $pass = 0;
class WP_CLI { static function error( $m ) { throw new RuntimeException( $m ); } static function success( $m ) { $GLOBALS['message'] = $m; } static function log( $m ) {} }
function home_url() { return $GLOBALS['home']; }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function wp_parse_url( $s, $component = -1 ) { return parse_url( $s, $component ); }
function get_stylesheet() { return $GLOBALS['theme']; }
function current_user_can( $c ) { return $GLOBALS['admin']; }
function get_object_taxonomies( $type ) { return array( 'page_topic' ); }
function get_post_stati() { return array( 'publish', 'draft', 'trash' ); }
function get_posts( $args ) {
	$GLOBALS['captures']++;
	if ( $GLOBALS['race'] && $GLOBALS['captures'] === 2 ) { $GLOBALS['db'][1348]['meta']['independent'] = array( 'new editor value' ); }
	$pages = array_filter( $GLOBALS['db'], static function ( $r ) use ( $args ) { $statuses = $args['post_status'] === 'any' ? array( 'publish', 'draft' ) : (array) $args['post_status']; return $r['post']['post_type'] === 'page' && in_array( $r['post']['post_status'], $statuses, true ) && ( ! isset( $args['name'] ) || $r['post']['post_name'] === $args['name'] ); } );
	return array_map( static fn( $r ) => (object) $r['post'], array_values( $pages ) );
}
function get_post( $id, $out = null, $context = null ) { $p = $GLOBALS['db'][$id]['post'] ?? null; return $out === ARRAY_A ? $p : ( $p ? (object) $p : null ); }
function get_post_meta( $id, $key = '', $single = false ) { $m = $GLOBALS['db'][$id]['meta'] ?? array(); return $key === '' ? $m : ( $single ? ( $m[$key][0] ?? '' ) : ( $m[$key] ?? array() ) ); }
function wp_get_object_terms( $id, $taxonomy, $args ) { return $GLOBALS['db'][$id]['terms'][$taxonomy] ?? array(); }
function is_wp_error( $v ) { return false; }
function wp_json_encode( $v, $flags = 0 ) { if ( $GLOBALS['fail_snapshot_complete'] && is_array( $v ) && ( $v['complete'] ?? false ) === true ) { $GLOBALS['fail_snapshot_complete'] = false; return false; } return json_encode( $v, $flags ); }
function wp_slash( $v ) { return is_array( $v ) ? array_map( 'wp_slash', $v ) : ( is_string( $v ) ? addslashes( $v ) : $v ); }
function wp_unslash( $v ) { return is_array( $v ) ? array_map( 'wp_unslash', $v ) : ( is_string( $v ) ? stripslashes( $v ) : $v ); }
function wp_strip_all_tags( $s ) { return strip_tags( $s ); }
function add_filter( $tag, $callback, $priority = 10, $count = 1 ) { $GLOBALS['filters'][$tag][$priority][] = $callback; }
function remove_filter( $tag, $callback, $priority ) { foreach ( $GLOBALS['filters'][$tag][$priority] as $i => $c ) { if ( $c === $callback ) { unset( $GLOBALS['filters'][$tag][$priority][$i] ); } } }
function apply_filters( $tag, $value, ...$extra ) { $groups = $GLOBALS['filters'][$tag] ?? array(); ksort( $groups ); foreach ( $groups as $callbacks ) { foreach ( $callbacks as $c ) { $value = $c( $value, ...$extra ); } } return $value; }
function wp_update_post( $arr, $error = false ) {
	if ( $GLOBALS['fail_write'] ) { $GLOBALS['fail_write'] = false; return 0; }
	$arr = wp_unslash( $arr ); $id = $arr['ID']; $p = array_merge( $GLOBALS['db'][$id]['post'], $arr );
	$p['post_modified'] = $p['post_modified_gmt'] = '2099-01-01 00:00:00';
	check( apply_filters( 'wp_revisions_to_keep', 5, (object) array( 'ID' => 1348 ) ) === 0, 'owned update does not create a revision' );
	check( apply_filters( 'wp_revisions_to_keep', 5, (object) array( 'ID' => 900 ) ) === 5, 'other pages retain revision policy' );
	$GLOBALS['db'][$id]['post'] = apply_filters( 'wp_insert_post_data', $p, $arr ); $GLOBALS['updates']++;
	if ( $GLOBALS['fail_after_write'] ) { $GLOBALS['fail_after_write'] = false; throw new RuntimeException( 'Forced failure after recognized write' ); }
	return $id;
}
function wp_delete_post( $id, $force ) { $p = $GLOBALS['db'][$id] ?? null; unset( $GLOBALS['db'][$id] ); $GLOBALS['deletes']++; return $p; }
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $label ); } $GLOBALS['pass']++; }
function invoke( $args, $legacy = false ) { $GLOBALS['message'] = ''; $GLOBALS['captures'] = 0; if ( $legacy ) { require __DIR__ . '/../install-rail-ideas.php'; } else { require __DIR__ . '/install-rail-input.php'; } return $GLOBALS['message']; }
function refusal( $args, $label, $legacy = false ) { $before = $GLOBALS['db']; $writes = $GLOBALS['updates'] + $GLOBALS['deletes']; $error = null; try { invoke( $args, $legacy ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before && $writes === $GLOBALS['updates'] + $GLOBALS['deletes'], $label ); }
function snap_write( $snap, $name ) { $file = $GLOBALS['testdir'] . '/backups/' . $name . '.json'; file_put_contents( $file, json_encode( $snap ) ); return $file; }
function snapshot_path() { if ( ! preg_match( '/Before-snapshot: (.+)$/', $GLOBALS['message'], $m ) ) { throw new RuntimeException( 'Snapshot path missing' ); } return $m[1]; }
$content = "<!-- wp:html -->\n" . rtrim( file_get_contents( __DIR__ . '/../rail-ideas-v1.html' ) ) . "\n<!-- /wp:html -->";
$record = array( 'id' => 1348, 'post' => array( 'ID' => 1348, 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'rail-nav-ideas', 'post_title' => 'Side rail ideas', 'post_content' => $content, 'post_author' => '1', 'post_parent' => 0, 'post_excerpt' => '', 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_date' => '2026-11-07 18:42:14', 'post_date_gmt' => '2026-11-08 02:42:14', 'post_modified' => '2026-11-07 18:42:14', 'post_modified_gmt' => '2026-11-08 02:42:14', 'guid' => 'http://localhost:8090/?page_id=1348' ), 'meta' => array( '_ts_rail_ideas' => array( '1' ), 'site-post-title' => array( 'disabled' ), 'surerank_settings_general' => array( 'a:1:{s:5:"title";s:11:"Keep my SEO";}' ), 'unknown_setting' => array( 'preserve', 'both values' ) ), 'terms' => array( 'page_topic' => array( 'practice' ) ) );
ksort( $record['meta'] ); $db[1348] = $record;
$db[900] = array( 'post' => array( 'ID' => 900, 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'unrelated', 'post_title' => 'Unrelated', 'post_content' => 'Untouched content' ), 'meta' => array(), 'terms' => array() );
$original = $db;
check( hash( 'sha256', $content ) === '0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748', 'exact inspected frozen content' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'http://localhost:8091', 'https://staging39.thaisolitude.com:443', 'https://staging39.thaisolitude.com/other', 'https://staging39.thaisolitude.com.evil.test' ) as $bad ) { $home = $bad; refusal( array( 'apply' ), 'refuse exact origin ' . $bad ); }
$home = 'http://localhost:8090'; $theme = 'other'; refusal( array( 'apply' ), 'non-Astra refused' ); $theme = 'astra'; $admin = false; refusal( array( 'apply' ), 'non-admin refused' ); $admin = true;
foreach ( array( array( 'unknown' ), array( 'apply', 'extra' ), array( 'rollback' ), array( 'rollback', 'x', 'extra' ) ) as $args ) { refusal( $args, 'bad mode/argument count' ); }
invoke( array() ); check( $db === $original && str_contains( $message, 'Preflight ready' ), 'default preflight read-only' );
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
$fail_write = true; refusal( array( 'apply' ), 'failed update leaves original intact' );
foreach ( array( 'fail_after_write', 'fail_snapshot_complete' ) as $flag ) {
	$GLOBALS[$flag] = true; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
	check( $error !== null && $db === $original, $flag . ' restores complete original state' );
}
$race = true; $writes = $updates; $error = null; try { invoke( array( 'apply' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $updates === $writes && $db[1348]['post']['post_content'] === $content && $db[1348]['meta']['independent'] === array( 'new editor value' ), 'concurrent independent edit is retained without overwrite' ); $race = false; $db = $original;
invoke( array( 'apply' ) ); $path = snapshot_path(); $applied = $db; $snapshot = json_decode( file_get_contents( $path ), true );
$expected = $original; $expected[1348]['post']['post_content'] = "<!-- wp:html -->\n" . rtrim( file_get_contents( __DIR__ . '/../rail-ideas.html' ) ) . "\n<!-- /wp:html -->";
check( $applied === $expected, 'only content changed; original dates/ID/meta/SEO/terms/other page stay exact' );
check( true === $snapshot['complete'] && ( fileperms( $path ) & 0777 ) === 0600, 'completed private snapshot persisted' );
$writes = $updates; invoke( array( 'apply' ) ); check( $db === $applied && $updates === $writes, 'idempotent apply writes nothing' );
foreach ( array( 'code', 'title', 'status', 'slug', 'meta', 'terms', 'date', 'author', 'excerpt', 'duplicate', 'duplicate_trash' ) as $mutation ) {
	$db = $applied;
	if ( $mutation === 'code' ) { $db[1348]['post']['post_content'] .= 'edited'; }
	if ( $mutation === 'title' ) { $db[1348]['post']['post_title'] .= 'edited'; }
	if ( $mutation === 'status' ) { $db[1348]['post']['post_status'] = 'draft'; }
	if ( $mutation === 'slug' ) { $db[1348]['post']['post_name'] = 'other'; }
	if ( $mutation === 'meta' ) { $db[1348]['meta']['unknown_setting'][] = 'changed'; }
	if ( $mutation === 'terms' ) { $db[1348]['terms']['page_topic'][] = 'other'; }
	if ( $mutation === 'date' ) { $db[1348]['post']['post_modified'] = '2099-01-01 00:00:00'; }
	if ( $mutation === 'author' ) { $db[1348]['post']['post_author'] = '2'; }
	if ( $mutation === 'excerpt' ) { $db[1348]['post']['post_excerpt'] = 'new'; }
	if ( $mutation === 'duplicate' || $mutation === 'duplicate_trash' ) { $db[9999] = $db[1348]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; if ( $mutation === 'duplicate_trash' ) { $db[9999]['post']['post_status'] = 'trash'; } }
	refusal( array( 'rollback', $path ), 'rollback refuses full-state drift: ' . $mutation );
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
}
$outside = $testdir . '/outside.json'; file_put_contents( $outside, json_encode( $snapshot ) ); refusal( array( 'rollback', $outside ), 'outside snapshot refused' );
$truncated = $testdir . '/backups/truncated.json'; file_put_contents( $truncated, '{"bundle":' ); refusal( array( 'rollback', $truncated ), 'truncated snapshot refused' );
invoke( array( 'rollback', $path ) ); check( $db === $original, 'exact full-state rollback' );
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

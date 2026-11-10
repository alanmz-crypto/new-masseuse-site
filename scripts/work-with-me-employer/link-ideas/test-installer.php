<?php
// Offline WordPress model: exercise destructive guards, exact migration and emission.
error_reporting( E_ALL );
$testdir = sys_get_temp_dir() . '/peek-installer-' . bin2hex( random_bytes( 5 ) );
mkdir( $testdir ); mkdir( $testdir . '/web' ); mkdir( $testdir . '/backups' );
define( 'ABSPATH', $testdir . '/web/' ); define( 'ARRAY_A', 'ARRAY_A' );
putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
$db = array(); $next_id = 2000; $filters = array(); $home = 'http://localhost:8090'; $environment_type = 'local'; $clock = '2026-11-09 05:00:00'; $mutate_page_on_refresh = null; $fail_write = false; $fail_cache = false; $fail_snapshot_complete = false; $page = 'thai-massage'; $admin = false; $feed = false;
$write_calls = 0; $cache_refreshes = 0;
class WP_CLI { static function error( $m ) { throw new RuntimeException( $m ); } static function success( $m ) { $GLOBALS['message'] = $m; } }
function home_url() { return $GLOBALS['home']; }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function wp_parse_url( $s, $component = -1 ) { return parse_url( $s, $component ); }
function get_stylesheet() { return 'astra'; }
function current_user_can( $s ) { return true; }
function wp_get_environment_type() { return $GLOBALS['environment_type']; }
function wpcode() { static $o; return $o ??= (object) array( 'cache' => new class { function cache_all_loaded_snippets() { $GLOBALS['cache_refreshes']++; if ( $GLOBALS['fail_cache'] ) { $GLOBALS['fail_cache'] = false; throw new RuntimeException( 'Forced cache failure' ); } if ( is_callable( $GLOBALS['mutate_page_on_refresh'] ) ) { $callback = $GLOBALS['mutate_page_on_refresh']; $GLOBALS['mutate_page_on_refresh'] = null; $callback(); } } } ); }
function get_object_taxonomies( $t ) { return array( 'wpcode_type', 'wpcode_location' ); }
function get_post_stati() { return array( 'publish', 'draft', 'trash' ); }
function get_posts( $args ) { if ( ( $args['post_type'] ?? '' ) === 'page' ) { return array_map( static fn( $p ) => (object) $p, array_filter( $GLOBALS['inventory_pages'], static fn( $p ) => ( $p['post_status'] ?? 'publish' ) === ( $args['post_status'] ?? 'publish' ) ) ); } return array_map( static fn( $r ) => (object) $r['post'], array_values( $GLOBALS['db'] ) ); }
function get_permalink( $post ) { return $post->permalink; }
function get_post( $id, $out = null, $context = null ) { $p = $GLOBALS['db'][ $id ]['post'] ?? null; return $out === ARRAY_A ? $p : ( $p ? (object) $p : null ); }
function get_post_meta( $id ) { return $GLOBALS['db'][ $id ]['meta']; }
function wp_get_object_terms( $id, $tax, $args ) { return $GLOBALS['db'][ $id ]['terms'][ $tax ]; }
function is_wp_error( $v ) { return false; }
function wp_json_encode( $v, $flags = 0 ) { if ( $GLOBALS['fail_snapshot_complete'] && is_array( $v ) && isset( $v['bundle'] ) && true === ( $v['complete'] ?? false ) ) { $GLOBALS['fail_snapshot_complete'] = false; return false; } return json_encode( $v, $flags ); }
function wp_slash( $v ) { return is_array( $v ) ? array_map( 'wp_slash', $v ) : ( is_string( $v ) ? addslashes( $v ) : $v ); }
function wp_unslash( $v ) { return is_array( $v ) ? array_map( 'wp_unslash', $v ) : ( is_string( $v ) ? stripslashes( $v ) : $v ); }
function current_time( $v, $gmt = false ) { return $GLOBALS['clock']; }
function add_filter( $tag, $callback, $priority = 10, $count = 1 ) { $GLOBALS['filters'][ $tag ][ $priority ][] = $callback; }
function remove_filter( $tag, $callback, $priority ) { foreach ( $GLOBALS['filters'][ $tag ][ $priority ] as $i => $c ) { if ( $c === $callback ) { unset( $GLOBALS['filters'][ $tag ][ $priority ][ $i ] ); } } }
function apply_filters( $tag, $v, ...$extra ) { $groups = $GLOBALS['filters'][ $tag ] ?? array(); ksort( $groups ); foreach ( $groups as $callbacks ) { foreach ( $callbacks as $c ) { $v = $c( $v, ...$extra ); } } return $v; }
function add_action( $tag, $callback, $priority = 10 ) { add_filter( $tag, $callback, $priority ); }
function do_action( $tag ) { $groups = $GLOBALS['filters'][ $tag ] ?? array(); ksort( $groups ); foreach ( $groups as $callbacks ) { foreach ( $callbacks as $c ) { $c(); } } }
function is_admin() { return $GLOBALS['admin']; }
function is_feed() { return $GLOBALS['feed']; }
function is_search() { return false; }
function is_page( $slug = null ) { return $slug === null ? (bool) $GLOBALS['page'] : $GLOBALS['page'] === $slug; }
function get_queried_object() { return (object) array( 'post_name' => $GLOBALS['page'], 'post_title' => 'Page ' . $GLOBALS['page'] ); }
function wp_get_document_title() { return 'Visitor page'; }
function wp_strip_all_tags( $text ) { return trim( strip_tags( preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text ) ) ); }
function wp_update_post( $arr, $error = false ) {
    $GLOBALS['write_calls']++;
    if ( $GLOBALS['fail_write'] ) { $GLOBALS['fail_write'] = false; return 0; }
    $arr = wp_unslash( $arr ); $id = $arr['ID']; $p = array_merge( $GLOBALS['db'][ $id ]['post'], $arr );
    $p['post_modified'] = $p['post_modified_gmt'] = current_time( 'mysql' );
    $GLOBALS['db'][ $id ]['post'] = apply_filters( 'wp_insert_post_data', $p, $arr ); return $id;
}
function wp_delete_post( $id, $force ) { $GLOBALS['write_calls']++; $p = $GLOBALS['db'][ $id ] ?? null; unset( $GLOBALS['db'][ $id ] ); return $p; }
function record( $id, $title, $code, $note ) {
    $meta = array( '_wpcode_auto_insert' => array( '1' ), '_wpcode_priority' => array( '10' ), '_wpcode_note' => array( $note ), '_wpcode_shortcode_attributes' => array( 'a:0:{}' ) ); ksort( $meta );
    return array( 'id' => $id, 'post' => array( 'ID' => $id, 'post_type' => 'wpcode', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $code, 'post_modified' => '2026-11-09 01:00:37', 'post_modified_gmt' => '2026-11-09 01:00:37', 'post_author' => '1', 'post_name' => 'historical-name' ), 'meta' => $meta, 'terms' => array( 'wpcode_location' => array( 'everywhere' ), 'wpcode_type' => array( 'php' ) ) );
}
class WPCode_Snippet { function __construct( private $args ) {} function save() { $GLOBALS['write_calls']++; $id = $GLOBALS['next_id']++; $GLOBALS['db'][ $id ] = record( $id, $this->args['title'], $this->args['code'], $this->args['note'] ); return $id; } }
$pass = 0;
function check( $ok, $label ) { if ( ! $ok ) { throw new RuntimeException( 'FAIL: ' . $label ); } $GLOBALS['pass']++; }
function invoke( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/install-peek-nav.php'; return $GLOBALS['message']; }
function refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, $label ); }
function invoke_page_rehearsal( $page_slug, $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/peek-page/' . $page_slug . '/install-peek-page-data.php'; return $GLOBALS['message']; }
function page_rehearsal_refusal( $page_slug, $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_page_rehearsal( $page_slug, $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, $page_slug . ': ' . $label ); }
function invoke_upgrade_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-upgrade/install-peek-core-upgrade.php'; return $GLOBALS['message']; }
function upgrade_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_upgrade_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'core upgrade adapter: ' . $label ); }
function invoke_summary_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-summary-touch/install-peek-core-summary-touch.php'; return $GLOBALS['message']; }
function summary_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_summary_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'summary-touch adapter: ' . $label ); }
function invoke_title_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-title-strip/install-peek-core-title-strip.php'; return $GLOBALS['message']; }
function title_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_title_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'title-strip adapter: ' . $label ); }
function invoke_rail_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-title-rail/install-peek-core-title-rail.php'; return $GLOBALS['message']; }
function rail_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_rail_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'title-rail adapter: ' . $label ); }
function invoke_kiro_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-kiro-fixes/install-peek-core-kiro-fixes.php'; return $GLOBALS['message']; }
function kiro_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_kiro_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'kiro-fixes adapter: ' . $label ); }
function invoke_focus_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-input-focus/install-peek-core-input-focus.php'; return $GLOBALS['message']; }
function focus_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_focus_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'input-focus adapter: ' . $label ); }
function invoke_neighbours_rehearsal( $args ) { $GLOBALS['message'] = ''; require __DIR__ . '/core-neighbours/install-peek-core-neighbours.php'; return $GLOBALS['message']; }
function neighbours_rehearsal_refusal( $args, $label ) { $before = $GLOBALS['db']; $error = null; try { invoke_neighbours_rehearsal( $args ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'neighbours adapter: ' . $label ); }
function invoke_package( $package, $args ) { require $package . '/install-peek-nav.php'; }
function snapshot_path() { preg_match( '/Before-snapshot: (.+)$/', $GLOBALS['message'], $m ); if ( ! isset( $m[1] ) ) { throw new RuntimeException( 'No completed snapshot: ' . $GLOBALS['message'] ); } return $m[1]; }
function snap_write( $data, $name ) { $p = $GLOBALS['testdir'] . '/backups/' . $name . '.json'; file_put_contents( $p, json_encode( $data ) ); return $p; }
function backup_hashes() { $out = array(); foreach ( glob( $GLOBALS['testdir'] . '/backups/*' ) as $file ) { $out[ $file ] = array( hash_file( 'sha256', $file ), fileperms( $file ) ); } return $out; }
function snapshot_verify_check( $args, $should_pass, $label ) {
    $before = $GLOBALS['db']; $writes = $GLOBALS['write_calls']; $refreshes = $GLOBALS['cache_refreshes']; $files = backup_hashes(); $error = null;
    try { invoke( $args ); } catch ( Throwable $e ) { $error = $e; }
    check( ( $should_pass ? null === $error && str_contains( $GLOBALS['message'], 'core and all four page-data records verified read-only' ) : null !== $error ) && $GLOBALS['db'] === $before && $GLOBALS['write_calls'] === $writes && $GLOBALS['cache_refreshes'] === $refreshes && backup_hashes() === $files, 'read-only snapshot verification: ' . $label );
}
$legacy = record( 1380, 'TSPeekNav practice (Thai, staging39)', substr( file_get_contents( __DIR__ . '/peek-nav-legacy.php' ), 6 ), 'Created by link-ideas/install-peek-nav.php (2026-11-08); exact owned-snippet rollback only.' );
$db[1380] = $legacy; $original = $db;
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other', 'https://staging39.thaisolitude.com:443', 'https://staging39.thaisolitude.com.evil.test' ) as $bad ) { $home = $bad; refusal( array( 'apply' ), 'refuse exact origin ' . $bad ); }
$home = 'http://localhost:8090';
refusal( array( 'apply' ), 'core needs Thai data' );
refusal( array( 'apply', 'page', '../thai-massage' ), 'reject traversing slug' );
refusal( array( 'apply', 'page', 'missing-page' ), 'reject missing page data' );
refusal( array( 'apply', 'core', 'extra' ), 'reject extra arguments' );
putenv( 'WWM_BACKUP_DIR' ); refusal( array( 'apply', 'page', 'thai-massage' ), 'missing private directory refused' );
putenv( 'WWM_BACKUP_DIR=.' ); refusal( array( 'apply', 'page', 'thai-massage' ), 'relative private directory refused' );
putenv( 'WWM_BACKUP_DIR=' . ABSPATH ); refusal( array( 'apply', 'page', 'thai-massage' ), 'webroot backup directory refused' );
putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
$fail_write = true; refusal( array( 'apply', 'page', 'thai-massage' ), 'failed page save removes only recognized new record' );
$fail_snapshot_complete = true; refusal( array( 'apply', 'page', 'thai-massage' ), 'page snapshot completion failure recovers original' );
invoke( array( 'apply', 'page', 'thai-massage' ) ); $page_snap = snapshot_path(); $data_state = $db; $data_id = array_values( array_diff( array_keys( $db ), array( 1380 ) ) )[0];
check( $db[1380] === $legacy && count( $db ) === 2, 'page data leaves exact legacy record unchanged' );
invoke( array( 'apply', 'page', 'thai-massage' ) ); check( $db === $data_state, 'page apply idempotent' );
invoke( array( 'apply' ) ); $core_snap = snapshot_path(); $applied = $db;
check( $db[1380]['id'] === 1380 && $db[1380]['meta'] === $legacy['meta'] && $db[1380]['terms'] === $legacy['terms'], 'core retains ID metadata taxonomy' );
invoke( array( 'apply', 'core' ) ); check( $db === $applied, 'core apply idempotent' );
foreach ( array( 'code', 'title', 'status', 'meta', 'terms', 'date', 'duplicate' ) as $mutation ) {
    $db = $applied;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '\n// edited'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $core_snap ), 'core rollback refuses ' . $mutation );
}
$db = $applied;
$good_snapshot = json_decode( file_get_contents( $core_snap ), true );
foreach ( array( 'home', 'hash', 'incomplete', 'id', 'component', 'payload' ) as $mutation ) {
    $s = $good_snapshot;
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    if ( $mutation === 'hash' ) { $s['new_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'id' ) { $s['new']['id'] = 12; }
    if ( $mutation === 'component' ) { $s['component'] = 'other'; }
    if ( $mutation === 'payload' ) { $s['payloads']['code_sha256'] = str_repeat( '1', 64 ); }
    refusal( array( 'rollback', snap_write( $s, 'bad-' . $mutation ) ), 'refuse snapshot ' . $mutation );
}
$bad_path = $testdir . '/outside.json'; file_put_contents( $bad_path, json_encode( $good_snapshot ) ); refusal( array( 'rollback', $bad_path ), 'snapshot outside private dir refused' );
$truncated = $testdir . '/backups/truncated.json'; file_put_contents( $truncated, '{"home":' ); refusal( array( 'rollback', $truncated ), 'truncated snapshot refused' );
invoke( array( 'rollback', $core_snap ) ); check( $db === $data_state, 'core rollback exact original post/meta/terms/dates' );
invoke( array( 'rollback', $core_snap ) ); check( $db === $data_state, 'core rollback idempotent' );
$fail_write = true; refusal( array( 'apply' ), 'forced save failure preserves old state' );
$fail_cache = true; refusal( array( 'apply' ), 'forced cache failure recovers exact original' );
$fail_snapshot_complete = true; refusal( array( 'apply' ), 'core snapshot completion failure recovers exact original' );
invoke( array( 'apply' ) ); check( $db === $applied, 'core reapply exact prior applied state' );
invoke( array( 'rollback', $core_snap ) );
foreach ( array( 'code', 'title', 'meta', 'id', 'duplicate' ) as $mutation ) {
    $db = $data_state;
    if ( $mutation === 'code' ) { $db[$data_id]['post']['post_content'] .= '// newer version'; }
    if ( $mutation === 'title' ) { $db[$data_id]['post']['post_title'] = 'Other snippet'; }
    if ( $mutation === 'meta' ) { $db[$data_id]['meta']['extra'] = array( 'edited' ); }
    if ( $mutation === 'id' ) { $db[$data_id]['post']['post_title'] = 'Other snippet'; $db[$data_id]['post']['post_content'] = 'unrelated();'; }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[$data_id]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $page_snap ), 'page rollback refuses ' . $mutation );
}
$db = $data_state; invoke( array( 'rollback', $page_snap ) ); check( $db === $original, 'page rollback removes only its own ID' );
invoke( array( 'rollback', $page_snap ) ); check( $db === $original, 'page rollback idempotent' );
$legacy_snapshot = array( 'home' => $home, 'title' => $legacy['post']['post_title'], 'snippet_id' => 1380, 'code_sha256' => hash( 'sha256', $legacy['post']['post_content'] ) ); $legacy_path = snap_write( $legacy_snapshot, 'legacy' );
foreach ( array( 'code', 'status', 'type', 'duplicate', 'hash' ) as $mutation ) {
    $db = $original; $s = $legacy_snapshot;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'type' ) { $db[1380]['terms']['wpcode_type'] = array( 'html' ); }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'hash' ) { $s['code_sha256'] = str_repeat( 'f', 64 ); }
    refusal( array( 'rollback', snap_write( $s, 'legacy-' . $mutation ) ), 'legacy deletion refuses ' . $mutation );
}
$db = $original; invoke( array( 'rollback', $legacy_path ) ); check( $db === array(), 'matching legacy snapshot can remove only exact owned snippet' );
invoke( array( 'rollback', $legacy_path ) ); check( $db === array(), 'legacy rollback idempotent' );
// Each rehearsal package must exercise only its named page on an isolated copy.
foreach ( array( 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) as $page_slug ) {
    $db = $applied; $baseline = $db;
    invoke_page_rehearsal( $page_slug, array() ); check( $db === $baseline, $page_slug . ': default preflight is read-only' );
    page_rehearsal_refusal( $page_slug, array( 'other' ), 'unknown mode refused' );
    page_rehearsal_refusal( $page_slug, array( 'apply', 'core' ), 'extra arguments cannot override target' );
    $home = 'https://staging39.thaisolitude.com'; page_rehearsal_refusal( $page_slug, array( 'apply' ), 'staging origin refused by rehearsal adapter' );
    $home = 'http://localhost:8090/other'; page_rehearsal_refusal( $page_slug, array( 'apply' ), 'inexact localhost origin refused' );
    $home = 'http://localhost:8090'; $environment_type = 'staging'; page_rehearsal_refusal( $page_slug, array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
    invoke_page_rehearsal( $page_slug, array( 'apply' ) ); $rehearsal_snap = snapshot_path(); $first = $db;
    $added = array_diff_key( $db, $baseline ); $new_id = array_key_first( $added ); $new_record = $added[ $new_id ];
    check( count( $added ) === 1 && array_intersect_key( $db, $baseline ) === $baseline && $new_record['post']['post_title'] === 'TSPeekData ' . $page_slug . ' (staging39)', $page_slug . ': apply changes only named page' );
    invoke_page_rehearsal( $page_slug, array( 'apply' ) ); check( $db === $first, $page_slug . ': apply is idempotent' );
    page_rehearsal_refusal( $page_slug, array( 'rollback' ), 'missing snapshot refused' );
    page_rehearsal_refusal( $page_slug, array( 'rollback', $rehearsal_snap, 'extra' ), 'extra rollback arguments refused' );
    page_rehearsal_refusal( $page_slug, array( 'rollback', $core_snap ), 'core snapshot refused' );
    page_rehearsal_refusal( $page_slug, array( 'rollback', $page_snap ), 'other-page snapshot refused' );
    invoke_page_rehearsal( $page_slug, array( 'rollback', $rehearsal_snap ) ); check( $db === $baseline, $page_slug . ': rollback restores exact baseline' );
    invoke_page_rehearsal( $page_slug, array( 'rollback', $rehearsal_snap ) ); check( $db === $baseline, $page_slug . ': rollback is idempotent' );
    invoke_page_rehearsal( $page_slug, array( 'apply' ) ); $recreated = array_values( array_diff_key( $db, $baseline ) )[0];
    $recreated['id'] = $new_record['id']; $recreated['post']['ID'] = $new_record['post']['ID'];
    check( $recreated === $new_record && array_intersect_key( $db, $baseline ) === $baseline, $page_slug . ': reapply matches first state except new ID' );
}
// Original upgrade remains exact v1 -> frozen v2; its snapshot meaning never changes.
$db = $applied;
foreach ( array( 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ) as $page_slug ) { invoke( array( 'apply', 'page', $page_slug ) ); }
$upgrade_before = $db; $data_before = array_diff_key( $db, array( 1380 => true ) ); $clock = '2026-11-09 06:00:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === 'dda8aecd38e417ad37766d2df951c6bdc761bf44997e030bbef5a36e9321f573', 'original core installer still produces exact frozen shared v1' );
invoke_upgrade_rehearsal( array( 'preflight' ) ); check( $db === $upgrade_before && str_contains( $message, 'core-upgrade ready;' ), 'upgrade rehearsal preflight is read-only and ready' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-upgrade' ), 'upgrade refuses foreign origin ' . $bad ); }
$home = 'https://staging39.thaisolitude.com'; upgrade_rehearsal_refusal( array( 'apply' ), 'staging origin refused' );
$home = 'http://localhost:8090'; $environment_type = 'staging'; upgrade_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
upgrade_rehearsal_refusal( array( 'other' ), 'unknown mode refused' );
upgrade_rehearsal_refusal( array( 'apply', 'core' ), 'target override refused' );
upgrade_rehearsal_refusal( array( 'rollback', $core_snap ), 'original migration snapshot refused' );
upgrade_rehearsal_refusal( array( 'rollback', $page_snap ), 'page snapshot refused' );
foreach ( array( 'legacy', 'absent', 'code', 'title', 'status', 'meta', 'terms', 'duplicate', 'id' ) as $mutation ) {
    $db = $upgrade_before;
    if ( $mutation === 'legacy' ) { $db[1380] = $legacy; }
    if ( $mutation === 'absent' ) { unset( $db[1380] ); }
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// drift'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_type'] = array( 'html' ); }
    if ( $mutation === 'duplicate' || $mutation === 'id' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'id' ) { unset( $db[1380] ); }
    refusal( array( 'apply', 'core-upgrade' ), 'upgrade refuses ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) {
    foreach ( array( 'missing', 'code', 'meta', 'terms', 'status', 'duplicate' ) as $mutation ) {
        $db = $upgrade_before;
        if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
        if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// drift'; }
        if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['_wpcode_priority'] = array( '11' ); }
        if ( $mutation === 'terms' ) { $db[ $page_id ]['terms']['wpcode_location'] = array( 'footer' ); }
        if ( $mutation === 'status' ) { $db[ $page_id ]['post']['post_status'] = 'draft'; }
        if ( $mutation === 'duplicate' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        refusal( array( 'apply', 'core-upgrade' ), 'upgrade refuses page ' . $page_id . ' ' . $mutation );
    }
}
$db = $upgrade_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-upgrade' ), 'upgrade ' . $failure . ' recovers exact v1 and every page' ); }
$drift_id = array_key_first( $data_before );
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-09 06:01:00'; };
$expected_drift = $upgrade_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-09 06:01:00'; $error = null;
try { invoke( array( 'apply', 'core-upgrade' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'concurrent page drift restores core and never overwrites independent data change' );
$db = $upgrade_before; invoke_upgrade_rehearsal( array( 'apply' ) ); $upgrade_snap = snapshot_path(); $upgraded = $db;
check( $db[1380]['post']['post_content'] === substr( file_get_contents( __DIR__ . '/peek-nav-shared-v2.php' ), 6 ) && $db[1380]['post']['post_modified'] === $clock && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'upgrade changes only core code/dates and preserves all page records' );
invoke( array( 'apply', 'core-upgrade' ) ); check( $db === $upgraded, 'upgrade apply is idempotent' );
$without_page = $upgraded; unset( $without_page[ $drift_id ] ); $db = $without_page;
invoke( array( 'preflight', 'core-upgrade' ) ); check( $db === $without_page && str_contains( $message, 'core-upgrade applied;' ), 'read-only core status remains valid after a separate page-only removal' );
invoke( array( 'apply', 'core-upgrade' ) ); check( $db === $without_page, 'already-applied upgrade never recreates separately removed page data' );
refusal( array( 'rollback', $upgrade_snap ), 'upgrade reversal requires the recorded complete page set' );
$db = $upgraded;
refusal( array( 'apply', 'core' ), 'historical migration cannot replace upgraded core' );
refusal( array( 'rollback', $core_snap ), 'historical migration snapshot cannot roll back upgraded core' );
refusal( array( 'rollback', $legacy_path ), 'legacy delete snapshot cannot remove upgraded core' );
$upgrade_snapshot = json_decode( file_get_contents( $upgrade_snap ), true );
check( $upgrade_snapshot['pages'] === array_combine( array( 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage' ), array_values( $data_before ) ), 'upgrade snapshot binds complete unchanged page records' );
foreach ( array( 'bundle', 'component', 'before-hash', 'old-state', 'new-state', 'pages', 'pages-hash', 'incomplete', 'home' ) as $mutation ) {
    $s = $upgrade_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-template-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core'; }
    if ( $mutation === 'before-hash' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-state' ) { $s['old']['post']['post_content'] = $legacy['post']['post_content']; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-state' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'pages' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    refusal( array( 'rollback', snap_write( $s, 'upgrade-' . $mutation ) ), 'upgrade rollback refuses snapshot ' . $mutation );
}
foreach ( array( 'code', 'meta', 'date', 'duplicate' ) as $mutation ) {
    $db = $upgraded;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// newer'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $upgrade_snap ), 'upgrade rollback refuses current core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) { $db = $upgraded; $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; refusal( array( 'rollback', $upgrade_snap ), 'upgrade rollback refuses changed full page state ' . $page_id ); }
$db = $upgraded; invoke_upgrade_rehearsal( array( 'rollback', $upgrade_snap ) ); check( $db === $upgrade_before, 'upgrade rollback restores exact v1 dates and leaves every data record intact' );
invoke_upgrade_rehearsal( array( 'rollback', $upgrade_snap ) ); check( $db === $upgrade_before, 'upgrade rollback is idempotent' );
invoke_upgrade_rehearsal( array( 'apply' ) ); check( $db === $upgraded, 'upgrade reapply equals first upgrade exactly' );
invoke( array( 'rollback', $upgrade_snap ) ); invoke( array( 'rollback', $core_snap ) );
check( $db[1380] === $legacy && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'original migration recovery still works after upgrade reversal with all page data preserved' );
// Frozen baseline corruption is tested in a disposable package, never by editing source.
$package = $testdir . '/package'; mkdir( $package );
foreach ( array( 'install-peek-nav.php', 'peek-nav-legacy.php', 'peek-nav-shared-v1.php', 'peek-nav-shared-v2.php', 'peek-nav-shared-v3.php', 'peek-nav-shared-v4.php', 'peek-nav-shared-v5.php', 'peek-nav-shared-v6.php', 'peek-nav-shared-v7.php', 'peek-nav.php' ) as $name ) { copy( __DIR__ . '/' . $name, $package . '/' . $name ); }
$db = $upgrade_before; file_put_contents( $package . '/peek-nav-shared-v1.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-upgrade' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v1 payload differs' ) && $db === $upgrade_before, 'altered frozen v1 file hash stops upgrade before any write' );
copy( __DIR__ . '/peek-nav-shared-v1.php', $package . '/peek-nav-shared-v1.php' ); file_put_contents( $package . '/peek-nav-shared-v2.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-summary-touch' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v2 payload differs' ) && $db === $upgrade_before, 'altered frozen v2 file hash stops summary-touch upgrade before any write' );
copy( __DIR__ . '/peek-nav-shared-v2.php', $package . '/peek-nav-shared-v2.php' ); file_put_contents( $package . '/peek-nav-shared-v3.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-title-strip' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v3 payload differs' ) && $db === $upgrade_before, 'altered frozen v3 file hash stops title-strip upgrade before any write' );
copy( __DIR__ . '/peek-nav-shared-v3.php', $package . '/peek-nav-shared-v3.php' ); file_put_contents( $package . '/peek-nav-shared-v4.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-title-rail' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v4 payload differs' ) && $db === $upgrade_before, 'altered frozen v4 file hash stops title-rail upgrade before any write' );
copy( __DIR__ . '/peek-nav-shared-v4.php', $package . '/peek-nav-shared-v4.php' ); file_put_contents( $package . '/peek-nav-shared-v5.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-kiro-fixes' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v5 payload differs' ) && $db === $upgrade_before, 'altered frozen v5 file hash stops Kiro fixes before any write' );
copy( __DIR__ . '/peek-nav-shared-v5.php', $package . '/peek-nav-shared-v5.php' ); file_put_contents( $package . '/peek-nav-shared-v6.php', "\n// changed", FILE_APPEND );
foreach ( array( 'core-kiro-fixes', 'core-input-focus', 'core-neighbours' ) as $component ) {
    $error = null;
    try { invoke_package( $package, array( 'apply', $component ) ); } catch ( Throwable $e ) { $error = $e; }
    check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v6 payload differs' ) && $db === $upgrade_before, 'altered frozen v6 file hash stops ' . $component . ' before any write' );
}
copy( __DIR__ . '/peek-nav-shared-v6.php', $package . '/peek-nav-shared-v6.php' ); file_put_contents( $package . '/peek-nav-shared-v7.php', "\n// changed", FILE_APPEND );
foreach ( array( 'core-input-focus', 'core-neighbours' ) as $component ) {
    $error = null;
    try { invoke_package( $package, array( 'apply', $component ) ); } catch ( Throwable $e ) { $error = $e; }
    check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v7 payload differs' ) && $db === $upgrade_before, 'altered frozen v7 stops ' . $component . ' before any write' );
}
foreach ( glob( $package . '/*' ) as $f ) { unlink( $f ); } rmdir( $package ); $clock = '2026-11-09 05:00:00';
// A second explicit transition binds v2, leaving both earlier recoveries intact.
$db = $upgraded; $summary_before = $db; $clock = '2026-11-09 07:30:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === '357e1d447ecf21ce1fa130f08f917bf7965d074463905106182fd3db9f8c39d8', 'original upgrade still produces exact deployed shared v2' );
invoke_summary_rehearsal( array() ); check( $db === $summary_before && str_contains( $message, 'core-summary-touch ready;' ), 'summary-touch default preflight is read-only and ready' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-summary-touch' ), 'summary-touch refuses foreign/inexact origin ' . $bad ); }
$home = 'https://staging39.thaisolitude.com'; summary_rehearsal_refusal( array( 'apply' ), 'staging origin refused' );
$home = 'http://localhost:8090'; $environment_type = 'staging'; summary_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
summary_rehearsal_refusal( array( 'other' ), 'unknown mode refused' );
summary_rehearsal_refusal( array( 'apply', 'core-upgrade' ), 'target override refused' );
foreach ( array( $core_snap, $upgrade_snap, $page_snap ) as $other_snapshot ) { summary_rehearsal_refusal( array( 'rollback', $other_snapshot ), 'earlier/other-component snapshot refused' ); }
foreach ( array( 'legacy', 'v1', 'missing', 'code', 'title', 'status', 'meta', 'terms', 'duplicate', 'id' ) as $mutation ) {
    $db = $summary_before;
    if ( $mutation === 'legacy' ) { $db[1380] = $legacy; }
    if ( $mutation === 'v1' ) { $db[1380] = $upgrade_before[1380]; }
    if ( $mutation === 'missing' ) { unset( $db[1380] ); }
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// drift'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' || $mutation === 'id' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'id' ) { unset( $db[1380] ); }
    refusal( array( 'apply', 'core-summary-touch' ), 'summary-touch refuses core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) {
    foreach ( array( 'missing', 'code', 'meta', 'duplicate' ) as $mutation ) {
        $db = $summary_before;
        if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
        if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// independently changed'; }
        if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['_wpcode_priority'] = array( '11' ); }
        if ( $mutation === 'duplicate' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        refusal( array( 'apply', 'core-summary-touch' ), 'summary-touch refuses page ' . $page_id . ' ' . $mutation );
    }
}
$db = $summary_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-summary-touch' ), 'summary-touch ' . $failure . ' recovers exact v2 and every page' ); }
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-09 07:31:00'; };
$expected_drift = $summary_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-09 07:31:00'; $error = null;
try { invoke( array( 'apply', 'core-summary-touch' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'summary-touch concurrent page drift restores core without overwriting independent change' );
$db = $summary_before; invoke_summary_rehearsal( array( 'apply' ) ); $summary_snap = snapshot_path(); $summary_applied = $db;
check( $db[1380]['post']['post_content'] === substr( file_get_contents( __DIR__ . '/peek-nav-shared-v3.php' ), 6 ) && $db[1380]['post']['post_modified'] === $clock && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'summary-touch changes only core payload/dates and preserves all page records' );
invoke_summary_rehearsal( array( 'apply' ) ); check( $db === $summary_applied, 'summary-touch apply is idempotent' );
foreach ( array( $legacy_path, $core_snap, $upgrade_snap ) as $earlier_snapshot ) { refusal( array( 'rollback', $earlier_snapshot ), 'earlier snapshot cannot reverse latest summary-touch payload' ); }
refusal( array( 'apply', 'core-upgrade' ), 'earlier upgrade cannot overwrite latest summary-touch payload' );
$summary_snapshot = json_decode( file_get_contents( $summary_snap ), true );
foreach ( array( 'bundle', 'component', 'before-hash', 'old-state', 'new-state', 'pages', 'pages-hash', 'incomplete' ) as $mutation ) {
    $s = $summary_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-upgrade-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-upgrade'; }
    if ( $mutation === 'before-hash' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-state' ) { $s['old'] = $upgrade_before[1380]; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-state' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'pages' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    refusal( array( 'rollback', snap_write( $s, 'summary-' . $mutation ) ), 'summary-touch refuses snapshot ' . $mutation );
}
foreach ( array( 'code', 'date', 'meta', 'duplicate' ) as $mutation ) {
    $db = $summary_applied;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $summary_snap ), 'summary-touch rollback refuses current core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) { $db = $summary_applied; $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; refusal( array( 'rollback', $summary_snap ), 'summary-touch rollback refuses changed complete page state ' . $page_id ); }
$db = $summary_applied; invoke_summary_rehearsal( array( 'rollback', $summary_snap ) ); check( $db === $summary_before, 'summary-touch rollback restores exact v2 post/settings/dates and all data' );
invoke_summary_rehearsal( array( 'rollback', $summary_snap ) ); check( $db === $summary_before, 'summary-touch rollback is idempotent' );
invoke_summary_rehearsal( array( 'apply' ) ); check( $db === $summary_applied, 'summary-touch reapply exactly matches first result' );
$without_page = $summary_applied; unset( $without_page[ $drift_id ] ); $db = $without_page;
invoke( array( 'preflight', 'core-summary-touch' ) ); check( $db === $without_page && str_contains( $message, 'core-summary-touch applied;' ), 'latest core remains inspectable after a separate page-only removal' );
refusal( array( 'rollback', $summary_snap ), 'latest core rollback refuses separately removed page data' );
$db = $summary_applied; invoke( array( 'rollback', $summary_snap ) ); invoke( array( 'rollback', $upgrade_snap ) );
check( $db === $upgrade_before, 'existing v1-to-v2 snapshot restores exact v1 after summary-touch reversal' );
invoke( array( 'rollback', $core_snap ) ); check( $db[1380] === $legacy && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'all three exact reversals preserve historical legacy recovery and every page record' );
// Sitewide titles use a separate exact v3 transition; no older snapshot changes meaning.
$db = $summary_applied; $title_before = $db; $clock = '2026-11-09 09:00:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === 'a20e7a69141843d08dbc0104f1eb7c4ae81ff99b0c7c6594ed1690d23fc0f4ac', 'summary-touch still produces exact deployed shared v3' );
invoke_title_rehearsal( array() ); check( $db === $title_before && str_contains( $message, 'core-title-strip ready;' ), 'title-strip default preflight is read-only and ready' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-title-strip' ), 'title-strip refuses foreign/inexact origin ' . $bad ); }
$home = 'https://staging39.thaisolitude.com'; title_rehearsal_refusal( array( 'apply' ), 'staging origin refused' );
$home = 'http://localhost:8090'; $environment_type = 'staging'; title_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
title_rehearsal_refusal( array( 'other' ), 'unknown mode refused' );
title_rehearsal_refusal( array( 'apply', 'core-summary-touch' ), 'target override refused' );
title_rehearsal_refusal( array( 'rollback' ), 'missing snapshot refused' );
title_rehearsal_refusal( array( 'rollback', $summary_snap, 'extra' ), 'extra rollback arguments refused' );
foreach ( array( $core_snap, $upgrade_snap, $summary_snap, $page_snap ) as $other_snapshot ) { title_rehearsal_refusal( array( 'rollback', $other_snapshot ), 'earlier/other-component snapshot refused' ); }
foreach ( array( 'legacy', 'v1', 'v2', 'missing', 'code', 'title', 'status', 'meta', 'terms', 'duplicate', 'id' ) as $mutation ) {
    $db = $title_before;
    if ( $mutation === 'legacy' ) { $db[1380] = $legacy; }
    if ( $mutation === 'v1' ) { $db[1380] = $upgrade_before[1380]; }
    if ( $mutation === 'v2' ) { $db[1380] = $summary_before[1380]; }
    if ( $mutation === 'missing' ) { unset( $db[1380] ); }
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// drift'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' || $mutation === 'id' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'id' ) { unset( $db[1380] ); }
    refusal( array( 'apply', 'core-title-strip' ), 'title-strip refuses core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) {
    foreach ( array( 'missing', 'code', 'meta', 'terms', 'status', 'duplicate' ) as $mutation ) {
        $db = $title_before;
        if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
        if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// independently changed'; }
        if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['_wpcode_priority'] = array( '11' ); }
        if ( $mutation === 'terms' ) { $db[ $page_id ]['terms']['wpcode_location'] = array( 'footer' ); }
        if ( $mutation === 'status' ) { $db[ $page_id ]['post']['post_status'] = 'draft'; }
        if ( $mutation === 'duplicate' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        refusal( array( 'apply', 'core-title-strip' ), 'title-strip refuses page ' . $page_id . ' ' . $mutation );
    }
}
$db = $title_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-title-strip' ), 'title-strip ' . $failure . ' recovers exact v3 and every page' ); }
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-09 09:01:00'; };
$expected_drift = $title_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-09 09:01:00'; $error = null;
try { invoke( array( 'apply', 'core-title-strip' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'title-strip concurrent page drift restores core without overwriting independent change' );
$db = $title_before; invoke_title_rehearsal( array( 'apply' ) ); $title_snap = snapshot_path(); $title_applied = $db;
check( $db[1380]['post']['post_content'] === substr( file_get_contents( __DIR__ . '/peek-nav-shared-v4.php' ), 6 ) && $db[1380]['post']['post_modified'] === $clock && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'title-strip changes only core payload/dates and preserves all page records' );
invoke_title_rehearsal( array( 'apply' ) ); check( $db === $title_applied, 'title-strip apply is idempotent' );
foreach ( array( $legacy_path, $core_snap, $upgrade_snap, $summary_snap ) as $earlier_snapshot ) { refusal( array( 'rollback', $earlier_snapshot ), 'earlier snapshot cannot reverse title-strip payload' ); }
refusal( array( 'apply', 'core-summary-touch' ), 'summary-touch cannot overwrite title-strip payload' );
$title_snapshot = json_decode( file_get_contents( $title_snap ), true );
foreach ( array( 'bundle', 'component', 'before-hash', 'new-hash', 'old-state', 'new-state', 'pages', 'pages-hash', 'incomplete', 'home' ) as $mutation ) {
    $s = $title_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-summary-touch-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-summary-touch'; }
    if ( $mutation === 'before-hash' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-hash' ) { $s['payloads']['code_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-state' ) { $s['old'] = $summary_before[1380]; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-state' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'pages' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    refusal( array( 'rollback', snap_write( $s, 'title-' . $mutation ) ), 'title-strip refuses snapshot ' . $mutation );
}
foreach ( array( 'code', 'date', 'meta', 'duplicate' ) as $mutation ) {
    $db = $title_applied;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $title_snap ), 'title-strip rollback refuses current core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) { $db = $title_applied; $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; refusal( array( 'rollback', $title_snap ), 'title-strip rollback refuses changed complete page state ' . $page_id ); }
$db = $title_applied; invoke_title_rehearsal( array( 'rollback', $title_snap ) ); check( $db === $title_before, 'title-strip rollback restores exact v3 post/settings/dates and all data' );
invoke_title_rehearsal( array( 'rollback', $title_snap ) ); check( $db === $title_before, 'title-strip rollback is idempotent' );
invoke_title_rehearsal( array( 'apply' ) ); check( $db === $title_applied, 'title-strip reapply exactly matches first result' );
$without_page = $title_applied; unset( $without_page[ $drift_id ] ); $db = $without_page;
invoke( array( 'preflight', 'core-title-strip' ) ); check( $db === $without_page && str_contains( $message, 'core-title-strip applied;' ), 'sitewide core remains inspectable after a separate page-only removal' );
invoke( array( 'apply', 'core-title-strip' ) ); check( $db === $without_page, 'idempotent sitewide apply never recreates separately removed data' );
refusal( array( 'rollback', $title_snap ), 'title-strip rollback refuses separately removed page data' );
$db = $title_applied; invoke( array( 'rollback', $title_snap ) ); invoke( array( 'rollback', $summary_snap ) );
check( $db === $summary_before, 'existing v2-to-v3 snapshot restores exact v2 after title-strip reversal' );
invoke( array( 'rollback', $upgrade_snap ) ); check( $db === $upgrade_before, 'existing v1-to-v2 recovery remains exact after both newer reversals' );
invoke( array( 'rollback', $core_snap ) ); check( $db[1380] === $legacy && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'all four exact reversals preserve legacy recovery and every page record' );
// Rails without new summaries use a separate exact v4 transition; all recovery meanings remain pinned.
$db = $title_applied; $rail_before = $db; $clock = '2026-11-09 11:00:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === '4162d3b40cfa54807f0a7b3851233aaca7482686ccab7a0a2e343f8afa665af5', 'title-strip still produces exact deployed shared v4' );
invoke_rail_rehearsal( array() ); check( $db === $rail_before && str_contains( $message, 'core-title-rail ready;' ), 'title-rail default preflight is read-only and ready' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-title-rail' ), 'title-rail refuses foreign/inexact origin ' . $bad ); }
$home = 'https://staging39.thaisolitude.com'; rail_rehearsal_refusal( array( 'apply' ), 'staging origin refused' );
$home = 'http://localhost:8090'; $environment_type = 'staging'; rail_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
rail_rehearsal_refusal( array( 'other' ), 'unknown mode refused' );
rail_rehearsal_refusal( array( 'apply', 'core-summary-touch' ), 'target override refused' );
rail_rehearsal_refusal( array( 'rollback' ), 'missing snapshot refused' );
rail_rehearsal_refusal( array( 'rollback', $summary_snap, 'extra' ), 'extra rollback arguments refused' );
foreach ( array( $core_snap, $upgrade_snap, $summary_snap, $title_snap, $page_snap ) as $other_snapshot ) { rail_rehearsal_refusal( array( 'rollback', $other_snapshot ), 'earlier/other-component snapshot refused' ); }
foreach ( array( 'legacy', 'v1', 'v2', 'v3', 'missing', 'code', 'title', 'status', 'meta', 'terms', 'duplicate', 'id' ) as $mutation ) {
    $db = $rail_before;
    if ( $mutation === 'legacy' ) { $db[1380] = $legacy; }
    if ( $mutation === 'v1' ) { $db[1380] = $upgrade_before[1380]; }
    if ( $mutation === 'v2' ) { $db[1380] = $summary_before[1380]; }
    if ( $mutation === 'v3' ) { $db[1380] = $title_before[1380]; }
    if ( $mutation === 'missing' ) { unset( $db[1380] ); }
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// drift'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' || $mutation === 'id' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'id' ) { unset( $db[1380] ); }
    refusal( array( 'apply', 'core-title-rail' ), 'title-rail refuses core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) {
    foreach ( array( 'missing', 'code', 'meta', 'terms', 'status', 'duplicate' ) as $mutation ) {
        $db = $rail_before;
        if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
        if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// independently changed'; }
        if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['_wpcode_priority'] = array( '11' ); }
        if ( $mutation === 'terms' ) { $db[ $page_id ]['terms']['wpcode_location'] = array( 'footer' ); }
        if ( $mutation === 'status' ) { $db[ $page_id ]['post']['post_status'] = 'draft'; }
        if ( $mutation === 'duplicate' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        refusal( array( 'apply', 'core-title-rail' ), 'title-rail refuses page ' . $page_id . ' ' . $mutation );
    }
}
$db = $rail_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-title-rail' ), 'title-rail ' . $failure . ' recovers exact v4 and every page' ); }
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-09 11:01:00'; };
$expected_drift = $rail_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-09 11:01:00'; $error = null;
try { invoke( array( 'apply', 'core-title-rail' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'title-rail concurrent page drift restores core without overwriting independent change' );
$db = $rail_before; invoke_rail_rehearsal( array( 'apply' ) ); $rail_snap = snapshot_path(); $rail_applied = $db;
check( $db[1380]['post']['post_content'] === substr( file_get_contents( __DIR__ . '/peek-nav-shared-v5.php' ), 6 ) && $db[1380]['post']['post_modified'] === $clock && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'title-rail changes only core payload/dates and preserves all page records' );
invoke_rail_rehearsal( array( 'apply' ) ); check( $db === $rail_applied, 'title-rail apply is idempotent' );
foreach ( array( $legacy_path, $core_snap, $upgrade_snap, $summary_snap, $title_snap ) as $earlier_snapshot ) { refusal( array( 'rollback', $earlier_snapshot ), 'earlier snapshot cannot reverse title-rail payload' ); }
refusal( array( 'apply', 'core-title-strip' ), 'title-strip cannot overwrite title-rail payload' );
$rail_snapshot = json_decode( file_get_contents( $rail_snap ), true );
foreach ( array( 'bundle', 'component', 'before-hash', 'new-hash', 'old-state', 'new-state', 'pages', 'pages-hash', 'incomplete', 'home' ) as $mutation ) {
    $s = $rail_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-title-strip-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-title-strip'; }
    if ( $mutation === 'before-hash' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-hash' ) { $s['payloads']['code_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-state' ) { $s['old'] = $title_before[1380]; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-state' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'pages' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    refusal( array( 'rollback', snap_write( $s, 'rail-' . $mutation ) ), 'title-rail refuses snapshot ' . $mutation );
}
foreach ( array( 'code', 'date', 'meta', 'duplicate' ) as $mutation ) {
    $db = $rail_applied;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $rail_snap ), 'title-rail rollback refuses current core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) { $db = $rail_applied; $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; refusal( array( 'rollback', $rail_snap ), 'title-rail rollback refuses changed complete page state ' . $page_id ); }
$db = $rail_applied; invoke_rail_rehearsal( array( 'rollback', $rail_snap ) ); check( $db === $rail_before, 'title-rail rollback restores exact v4 post/settings/dates and all data' );
invoke_rail_rehearsal( array( 'rollback', $rail_snap ) ); check( $db === $rail_before, 'title-rail rollback is idempotent' );
invoke_rail_rehearsal( array( 'apply' ) ); check( $db === $rail_applied, 'title-rail reapply exactly matches first result' );
$without_page = $rail_applied; unset( $without_page[ $drift_id ] ); $db = $without_page;
invoke( array( 'preflight', 'core-title-rail' ) ); check( $db === $without_page && str_contains( $message, 'core-title-rail applied;' ), 'sitewide core remains inspectable after a separate page-only removal' );
invoke( array( 'apply', 'core-title-rail' ) ); check( $db === $without_page, 'idempotent sitewide apply never recreates separately removed data' );
refusal( array( 'rollback', $rail_snap ), 'title-rail rollback refuses separately removed page data' );
$db = $rail_applied; invoke( array( 'rollback', $rail_snap ) ); invoke( array( 'rollback', $title_snap ) );
check( $db === $title_before, 'existing v3-to-v4 snapshot restores exact v3 after title-rail reversal' );
invoke( array( 'rollback', $summary_snap ) );
check( $db === $summary_before, 'existing v2-to-v3 snapshot restores exact v2 after both later reversals' );
invoke( array( 'rollback', $upgrade_snap ) ); check( $db === $upgrade_before, 'existing v1-to-v2 recovery remains exact after all newer reversals' );
invoke( array( 'rollback', $core_snap ) ); check( $db[1380] === $legacy && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'all five exact reversals preserve legacy recovery and every page record' );
// Kiro corrections are a separate exact v5 transition. Historical snapshots stay pinned.
$db = $rail_applied; $kiro_before = $db; $clock = '2026-11-09 19:00:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === 'c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e', 'title-rail still produces exact deployed shared v5' );
invoke_kiro_rehearsal( array() ); check( $db === $kiro_before && str_contains( $message, 'core-kiro-fixes ready;' ), 'Kiro fixes default preflight is read-only and ready' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other', 'https://staging39.thaisolitude.com:443' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-kiro-fixes' ), 'Kiro fixes refuse foreign/inexact origin ' . $bad ); }
$home = 'https://staging39.thaisolitude.com'; kiro_rehearsal_refusal( array( 'apply' ), 'staging origin refused' );
$home = 'http://localhost:8090'; $environment_type = 'staging'; kiro_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
kiro_rehearsal_refusal( array( 'other' ), 'unknown mode refused' );
kiro_rehearsal_refusal( array( 'apply', 'core-title-rail' ), 'target override refused' );
kiro_rehearsal_refusal( array( 'rollback' ), 'missing snapshot refused' );
kiro_rehearsal_refusal( array( 'rollback', $rail_snap, 'extra' ), 'extra rollback arguments refused' );
foreach ( array( $core_snap, $upgrade_snap, $summary_snap, $title_snap, $rail_snap, $page_snap ) as $other_snapshot ) { kiro_rehearsal_refusal( array( 'rollback', $other_snapshot ), 'earlier/other-component snapshot refused' ); }
foreach ( array( 'legacy', 'v1', 'v2', 'v3', 'v4', 'missing', 'code', 'title', 'status', 'meta', 'terms', 'duplicate', 'id' ) as $mutation ) {
    $db = $kiro_before;
    if ( $mutation === 'legacy' ) { $db[1380] = $legacy; }
    if ( $mutation === 'v1' ) { $db[1380] = $upgrade_before[1380]; }
    if ( $mutation === 'v2' ) { $db[1380] = $summary_before[1380]; }
    if ( $mutation === 'v3' ) { $db[1380] = $title_before[1380]; }
    if ( $mutation === 'v4' ) { $db[1380] = $rail_before[1380]; }
    if ( $mutation === 'missing' ) { unset( $db[1380] ); }
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// drift'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' || $mutation === 'id' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'id' ) { unset( $db[1380] ); }
    refusal( array( 'apply', 'core-kiro-fixes' ), 'Kiro fixes refuse core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) {
    foreach ( array( 'missing', 'code', 'meta', 'terms', 'status', 'duplicate' ) as $mutation ) {
        $db = $kiro_before;
        if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
        if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// independently changed'; }
        if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['_wpcode_priority'] = array( '11' ); }
        if ( $mutation === 'terms' ) { $db[ $page_id ]['terms']['wpcode_location'] = array( 'footer' ); }
        if ( $mutation === 'status' ) { $db[ $page_id ]['post']['post_status'] = 'draft'; }
        if ( $mutation === 'duplicate' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        refusal( array( 'apply', 'core-kiro-fixes' ), 'Kiro fixes refuse page ' . $page_id . ' ' . $mutation );
    }
}
$db = $kiro_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-kiro-fixes' ), 'Kiro fixes ' . $failure . ' recovers exact v5 and every page' ); }
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-09 19:01:00'; };
$expected_drift = $kiro_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-09 19:01:00'; $error = null;
try { invoke( array( 'apply', 'core-kiro-fixes' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'Kiro fixes concurrent page drift restores core without overwriting independent change' );
$db = $kiro_before; invoke_kiro_rehearsal( array( 'apply' ) ); $kiro_snap = snapshot_path(); $kiro_applied = $db;
check( $db[1380]['post']['post_content'] === substr( file_get_contents( __DIR__ . '/peek-nav-shared-v6.php' ), 6 ) && $db[1380]['post']['post_modified'] === $clock && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'Kiro fixes remain pinned to v6 and change only core payload/dates, preserving all page records' );
invoke_kiro_rehearsal( array( 'apply' ) ); check( $db === $kiro_applied, 'Kiro fixes apply is idempotent' );
foreach ( array( $legacy_path, $core_snap, $upgrade_snap, $summary_snap, $title_snap, $rail_snap ) as $earlier_snapshot ) { refusal( array( 'rollback', $earlier_snapshot ), 'earlier snapshot cannot reverse Kiro fixes payload' ); }
refusal( array( 'apply', 'core-title-rail' ), 'frozen title-rail transition cannot overwrite Kiro fixes payload' );
$kiro_snapshot = json_decode( file_get_contents( $kiro_snap ), true );
check( $kiro_snapshot['bundle'] === 'peek-core-kiro-fixes-v1' && $kiro_snapshot['payloads']['before_sha256'] === 'c7ed404ebe3e19d24e7a91b91bb996505efb80027fd183245d1c7137f188991e', 'Kiro snapshot binds its own transition and exact deployed v5 predecessor' );
foreach ( array( 'bundle', 'component', 'before-hash', 'new-hash', 'old-state', 'new-state', 'pages', 'pages-hash', 'incomplete', 'home' ) as $mutation ) {
    $s = $kiro_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-title-rail-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-title-rail'; }
    if ( $mutation === 'before-hash' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-hash' ) { $s['payloads']['code_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-state' ) { $s['old'] = $rail_before[1380]; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-state' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'pages' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    refusal( array( 'rollback', snap_write( $s, 'kiro-' . $mutation ) ), 'Kiro fixes refuse snapshot ' . $mutation );
}
foreach ( array( 'code', 'date', 'title', 'status', 'meta', 'terms', 'duplicate' ) as $mutation ) {
    $db = $kiro_applied;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $kiro_snap ), 'Kiro fixes rollback refuses current core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) { $db = $kiro_applied; $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; refusal( array( 'rollback', $kiro_snap ), 'Kiro fixes rollback refuses changed complete page state ' . $page_id ); }
$db = $kiro_applied; invoke_kiro_rehearsal( array( 'rollback', $kiro_snap ) ); check( $db === $kiro_before, 'Kiro fixes rollback restores exact v5 post/settings/dates and all data' );
invoke_kiro_rehearsal( array( 'rollback', $kiro_snap ) ); check( $db === $kiro_before, 'Kiro fixes rollback is idempotent' );
invoke_kiro_rehearsal( array( 'apply' ) ); check( $db === $kiro_applied, 'Kiro fixes reapply exactly matches first result' );
$without_page = $kiro_applied; unset( $without_page[ $drift_id ] ); $db = $without_page;
invoke( array( 'preflight', 'core-kiro-fixes' ) ); check( $db === $without_page && str_contains( $message, 'core-kiro-fixes applied;' ), 'Kiro fixes core remains inspectable after a separate page-only removal' );
invoke( array( 'apply', 'core-kiro-fixes' ) ); check( $db === $without_page, 'idempotent Kiro fixes apply never recreates separately removed data' );
refusal( array( 'rollback', $kiro_snap ), 'Kiro fixes rollback refuses separately removed page data' );
$db = $kiro_applied; invoke( array( 'rollback', $kiro_snap ) ); invoke( array( 'rollback', $rail_snap ) );
check( $db === $rail_before, 'existing v4-to-v5 snapshot restores exact v4 after Kiro fixes reversal' );
invoke( array( 'rollback', $title_snap ) ); check( $db === $title_before, 'existing v3-to-v4 recovery stays exact after Kiro and title-rail reversals' );
invoke( array( 'rollback', $summary_snap ) ); invoke( array( 'rollback', $upgrade_snap ) ); invoke( array( 'rollback', $core_snap ) );
check( $db[1380] === $legacy && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'all six exact reversals preserve legacy recovery and every page record' );
// Input focus has its own v6 transition; the actual Kiro deployment remains a pinned v5 -> v6 route.
$db = $kiro_applied; $focus_before = $db; $clock = '2026-11-09 23:00:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === 'dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1', 'Kiro fixes still produce exact deployed v6 bytes' );
invoke_focus_rehearsal( array() ); check( $db === $focus_before && str_contains( $message, 'core-input-focus ready;' ), 'input-focus default preflight is read-only and ready' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other', 'https://staging39.thaisolitude.com:443' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-input-focus' ), 'input-focus refuses foreign/inexact origin ' . $bad ); }
foreach ( array( 'https://staging39.thaisolitude.com', 'http://localhost:8090/other' ) as $bad ) { $home = $bad; focus_rehearsal_refusal( array( 'apply' ), 'non-exact rehearsal origin refused: ' . $bad ); }
$home = 'http://localhost:8090'; $environment_type = 'staging'; focus_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
focus_rehearsal_refusal( array( 'other' ), 'unknown mode refused' );
focus_rehearsal_refusal( array( 'apply', 'core-kiro-fixes' ), 'target override refused' );
focus_rehearsal_refusal( array( 'rollback' ), 'missing snapshot refused' );
focus_rehearsal_refusal( array( 'rollback', $kiro_snap, 'extra' ), 'extra rollback arguments refused' );
foreach ( array( $core_snap, $upgrade_snap, $summary_snap, $title_snap, $rail_snap, $kiro_snap, $page_snap ) as $other_snapshot ) { focus_rehearsal_refusal( array( 'rollback', $other_snapshot ), 'earlier/other-component snapshot refused' ); }
foreach ( array( $legacy, $upgrade_before[1380], $summary_before[1380], $title_before[1380], $rail_before[1380], $kiro_before[1380] ) as $older_core ) { $db = $focus_before; $db[1380] = $older_core; refusal( array( 'apply', 'core-input-focus' ), 'input-focus cannot skip its exact v6 predecessor' ); }
foreach ( array( 'missing', 'code', 'title', 'status', 'meta', 'terms', 'duplicate', 'id' ) as $mutation ) {
    $db = $focus_before;
    if ( $mutation === 'missing' ) { unset( $db[1380] ); }
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// drift'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' || $mutation === 'id' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    if ( $mutation === 'id' ) { unset( $db[1380] ); }
    refusal( array( 'apply', 'core-input-focus' ), 'input-focus refuses core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) {
    foreach ( array( 'missing', 'code', 'meta', 'terms', 'status', 'duplicate' ) as $mutation ) {
        $db = $focus_before;
        if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
        if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// independently changed'; }
        if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['_wpcode_priority'] = array( '11' ); }
        if ( $mutation === 'terms' ) { $db[ $page_id ]['terms']['wpcode_location'] = array( 'footer' ); }
        if ( $mutation === 'status' ) { $db[ $page_id ]['post']['post_status'] = 'draft'; }
        if ( $mutation === 'duplicate' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        refusal( array( 'apply', 'core-input-focus' ), 'input-focus refuses page ' . $page_id . ' ' . $mutation );
    }
}
$db = $focus_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-input-focus' ), 'input-focus ' . $failure . ' recovers exact v6 and every page' ); }
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-09 23:01:00'; };
$expected_drift = $focus_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-09 23:01:00'; $error = null;
try { invoke( array( 'apply', 'core-input-focus' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'input-focus concurrent page drift restores core without overwriting independent change' );
$db = $focus_before; invoke_focus_rehearsal( array( 'apply' ) ); $focus_snap = snapshot_path(); $focus_applied = $db;
$expected_focus = $focus_before; $expected_focus[1380]['post']['post_content'] = substr( file_get_contents( __DIR__ . '/peek-nav-shared-v7.php' ), 6 ); $expected_focus[1380]['post']['post_modified'] = $expected_focus[1380]['post']['post_modified_gmt'] = $clock;
check( $db === $expected_focus, 'input-focus changes only core code/dates, retaining ID and complete settings/data states' );
invoke_focus_rehearsal( array( 'apply' ) ); check( $db === $focus_applied, 'input-focus apply is idempotent' );
foreach ( array( $legacy_path, $core_snap, $upgrade_snap, $summary_snap, $title_snap, $rail_snap, $kiro_snap ) as $earlier_snapshot ) { refusal( array( 'rollback', $earlier_snapshot ), 'earlier snapshot cannot reverse input-focus payload' ); }
refusal( array( 'apply', 'core-kiro-fixes' ), 'pinned Kiro transition cannot overwrite input-focus payload' );
$focus_snapshot = json_decode( file_get_contents( $focus_snap ), true );
check( $focus_snapshot['bundle'] === 'peek-core-input-focus-v1' && $focus_snapshot['payloads']['before_sha256'] === 'dec11c9e50cacba8986068e98748e966333aebe3366701f1f0e94e5e790113a1' && $focus_snapshot['old'] === $kiro_snapshot['new'], 'input-focus snapshot binds its own transition and the exact Kiro result including dates' );
// Snapshot-bound verification must never substitute preflight's permissive
// already-applied status for exact core dates and all four complete page records.
snapshot_verify_check( array( 'verify', $focus_snap ), true, 'exact applied core and pages; no writes, cache refresh or snapshot changes' );
snapshot_verify_check( array( 'verify', $focus_snap, 'original' ), false, 'applied state cannot be claimed as the original' );
$db = $focus_before;
snapshot_verify_check( array( 'verify', $focus_snap, 'original' ), true, 'exact original core and pages; no writes, cache refresh or snapshot changes' );
snapshot_verify_check( array( 'verify', $focus_snap ), false, 'original state cannot be claimed as applied' );
$db = $focus_applied;
foreach ( array( array( 'verify' ), array( 'verify', $focus_snap, 'other' ), array( 'verify', $focus_snap, 'original', 'extra' ), array( 'verify', $testdir . '/missing.json' ), array( 'verify', $bad_path ), array( 'verify', $truncated ) ) as $verify_args ) { snapshot_verify_check( $verify_args, false, 'invalid arguments, missing, outside or truncated snapshot refused' ); }
foreach ( array( $legacy_path, $core_snap, $upgrade_snap, $summary_snap, $title_snap, $rail_snap, $kiro_snap, $page_snap ) as $other_snapshot ) { snapshot_verify_check( array( 'verify', $other_snapshot ), false, 'historical/other-component snapshot cannot enter focus verification' ); }
foreach ( array( 'bundle', 'component', 'slug', 'missing-slug', 'home', 'incomplete', 'before-payload', 'new-payload', 'old-hash', 'new-hash', 'old-content-rehashed', 'new-author-rehashed', 'ids-rehashed', 'pages-hash', 'pages-missing', 'page-date-rehashed' ) as $mutation ) {
    $s = $focus_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-kiro-fixes-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-kiro-fixes'; }
    if ( $mutation === 'slug' ) { $s['slug'] = 'thai-massage'; }
    if ( $mutation === 'missing-slug' ) { unset( $s['slug'] ); }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'before-payload' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-payload' ) { $s['payloads']['code_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-hash' ) { $s['old_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-hash' ) { $s['new_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-content-rehashed' ) { $s['old']['post']['post_content'] .= '// changed'; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-author-rehashed' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'ids-rehashed' ) { foreach ( array( 'old', 'new' ) as $which ) { $s[ $which ]['id'] = $s[ $which ]['post']['ID'] = 9999; $s[ $which . '_sha256' ] = hash( 'sha256', json_encode( $s[ $which ] ) ); } }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'pages-missing' ) { unset( $s['pages']['chair-massage'] ); $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'page-date-rehashed' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    $verify_bad = snap_write( $s, 'focus-verify-' . $mutation );
    snapshot_verify_check( array( 'verify', $verify_bad ), false, 'damaged or mismatched snapshot ' . $mutation );
}
foreach ( array( 'applied' => $focus_applied, 'original' => $focus_before ) as $verify_state => $baseline ) {
    $verify_args = 'original' === $verify_state ? array( 'verify', $focus_snap, 'original' ) : array( 'verify', $focus_snap );
    foreach ( array( 'missing', 'code', 'date', 'gmt-date', 'author', 'meta', 'terms', 'duplicate', 'duplicate-trash', 'id' ) as $mutation ) {
        $db = $baseline;
        if ( $mutation === 'missing' ) { unset( $db[1380] ); }
        if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
        if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
        if ( $mutation === 'gmt-date' ) { $db[1380]['post']['post_modified_gmt'] = '2027-01-01 00:00:00'; }
        if ( $mutation === 'author' ) { $db[1380]['post']['post_author'] = '99'; }
        if ( $mutation === 'meta' ) { $db[1380]['meta']['unknown'] = array( 'new' ); }
        if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
        if ( in_array( $mutation, array( 'duplicate', 'duplicate-trash', 'id' ), true ) ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
        if ( $mutation === 'duplicate-trash' ) { $db[9999]['post']['post_status'] = 'trash'; }
        if ( $mutation === 'id' ) { unset( $db[1380] ); }
        snapshot_verify_check( $verify_args, false, $verify_state . ' core ' . $mutation . ' drift refused' );
    }
    foreach ( $data_before as $page_id => $record ) {
        foreach ( array( 'missing', 'code', 'date', 'author', 'meta', 'terms', 'duplicate', 'duplicate-trash' ) as $mutation ) {
            $db = $baseline;
            if ( $mutation === 'missing' ) { unset( $db[ $page_id ] ); }
            if ( $mutation === 'code' ) { $db[ $page_id ]['post']['post_content'] .= '// changed'; }
            if ( $mutation === 'date' ) { $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; }
            if ( $mutation === 'author' ) { $db[ $page_id ]['post']['post_author'] = '99'; }
            if ( $mutation === 'meta' ) { $db[ $page_id ]['meta']['unknown'] = array( 'new' ); }
            if ( $mutation === 'terms' ) { $db[ $page_id ]['terms']['wpcode_location'] = array( 'footer' ); }
            if ( $mutation === 'duplicate' || $mutation === 'duplicate-trash' ) { $db[9999] = $record; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
            if ( $mutation === 'duplicate-trash' ) { $db[9999]['post']['post_status'] = 'trash'; }
            snapshot_verify_check( $verify_args, false, $verify_state . ' page ' . $page_id . ' ' . $mutation . ' drift refused' );
        }
    }
}
$db = $focus_applied;
kiro_rehearsal_refusal( array( 'rollback', $focus_snap ), 'new input-focus snapshot cannot enter historical Kiro adapter' );
foreach ( array( 'bundle', 'component', 'before-hash', 'new-hash', 'old-state', 'new-state', 'pages', 'pages-hash', 'incomplete', 'home' ) as $mutation ) {
    $s = $focus_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-kiro-fixes-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-kiro-fixes'; }
    if ( $mutation === 'before-hash' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-hash' ) { $s['payloads']['code_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-state' ) { $s['old'] = $kiro_before[1380]; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-state' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'pages' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    refusal( array( 'rollback', snap_write( $s, 'focus-' . $mutation ) ), 'input-focus refuses snapshot ' . $mutation );
}
foreach ( array( 'code', 'date', 'title', 'status', 'meta', 'terms', 'duplicate' ) as $mutation ) {
    $db = $focus_applied;
    if ( $mutation === 'code' ) { $db[1380]['post']['post_content'] .= '// changed'; }
    if ( $mutation === 'date' ) { $db[1380]['post']['post_modified'] = '2027-01-01 00:00:00'; }
    if ( $mutation === 'title' ) { $db[1380]['post']['post_title'] .= ' edited'; }
    if ( $mutation === 'status' ) { $db[1380]['post']['post_status'] = 'draft'; }
    if ( $mutation === 'meta' ) { $db[1380]['meta']['_wpcode_priority'] = array( '11' ); }
    if ( $mutation === 'terms' ) { $db[1380]['terms']['wpcode_location'] = array( 'footer' ); }
    if ( $mutation === 'duplicate' ) { $db[9999] = $db[1380]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
    refusal( array( 'rollback', $focus_snap ), 'input-focus rollback refuses current core ' . $mutation );
}
foreach ( $data_before as $page_id => $record ) { $db = $focus_applied; $db[ $page_id ]['post']['post_modified'] = '2027-01-01 00:00:00'; refusal( array( 'rollback', $focus_snap ), 'input-focus rollback refuses changed complete page state ' . $page_id ); }
$db = $focus_applied; invoke_focus_rehearsal( array( 'rollback', $focus_snap ) ); check( $db === $focus_before, 'input-focus rollback restores exact v6 post/settings/dates and all data' );
invoke_focus_rehearsal( array( 'rollback', $focus_snap ) ); check( $db === $focus_before, 'input-focus rollback is idempotent' );
invoke_focus_rehearsal( array( 'apply' ) ); check( $db === $focus_applied, 'input-focus reapply exactly matches first result' );
$without_page = $focus_applied; unset( $without_page[ $drift_id ] ); $db = $without_page;
invoke( array( 'preflight', 'core-input-focus' ) ); check( $db === $without_page && str_contains( $message, 'core-input-focus applied;' ), 'input-focus remains inspectable after a separate page-only removal' );
invoke( array( 'apply', 'core-input-focus' ) ); check( $db === $without_page, 'idempotent input-focus apply never recreates separately removed data' );
refusal( array( 'rollback', $focus_snap ), 'input-focus rollback refuses separately removed page data' );
$db = $focus_applied; invoke( array( 'rollback', $focus_snap ) ); invoke( array( 'rollback', $kiro_snap ) );
check( $db === $kiro_before, 'existing Kiro snapshot still restores exact v5 after input-focus reversal' );
invoke( array( 'rollback', $rail_snap ) ); invoke( array( 'rollback', $title_snap ) ); invoke( array( 'rollback', $summary_snap ) ); invoke( array( 'rollback', $upgrade_snap ) ); invoke( array( 'rollback', $core_snap ) );
check( $db[1380] === $legacy && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'all seven exact reversals preserve legacy recovery and every page record' );
// Neighbours advances only v7; the historical input-focus snapshot remains v6 -> v7.
$db = $focus_applied; $neighbours_before = $db; $clock = '2026-11-10 10:00:00';
check( hash( 'sha256', $db[1380]['post']['post_content'] ) === 'a3a503f59dc8a557a24e5a03967481af824f29136668b1158d9cfa47ae3e537c', 'input-focus still produces the exact deployed v7, independent of current runtime' );
invoke_neighbours_rehearsal( array() ); check( $db === $neighbours_before && str_contains( $message, 'core-neighbours ready;' ), 'neighbours preflight is read-only on exact v7' );
foreach ( array( 'https://thaisolitude.com', 'https://staging38.thaisolitude.com', 'https://staging39.thaisolitude.com/other', 'https://staging39.thaisolitude.com:443' ) as $bad ) { $home = $bad; refusal( array( 'apply', 'core-neighbours' ), 'neighbours refuses foreign/inexact origin ' . $bad ); }
foreach ( array( 'https://staging39.thaisolitude.com', 'http://localhost:8090/other' ) as $bad ) { $home = $bad; neighbours_rehearsal_refusal( array( 'apply' ), 'non-exact rehearsal origin refused' ); }
$home = 'http://localhost:8090'; $environment_type = 'staging'; neighbours_rehearsal_refusal( array( 'apply' ), 'non-local environment refused' ); $environment_type = 'local';
foreach ( array( array( 'other' ), array( 'apply', 'core-input-focus' ), array( 'rollback' ), array( 'rollback', $focus_snap, 'extra' ), array( 'rollback', $focus_snap ) ) as $bad_args ) { neighbours_rehearsal_refusal( $bad_args, 'wrong mode/arguments/component refused' ); }
$db = $focus_before; refusal( array( 'apply', 'core-neighbours' ), 'neighbours cannot skip v6 to its current payload' );
$db = $neighbours_before;
foreach ( array( 'fail_write', 'fail_cache', 'fail_snapshot_complete' ) as $failure ) { $GLOBALS[ $failure ] = true; refusal( array( 'apply', 'core-neighbours' ), 'neighbours ' . $failure . ' recovers exact v7 and every page' ); }
$mutate_page_on_refresh = static function () use ( $drift_id ) { $GLOBALS['db'][ $drift_id ]['post']['post_modified'] = '2026-11-10 10:01:00'; };
$expected_drift = $neighbours_before; $expected_drift[ $drift_id ]['post']['post_modified'] = '2026-11-10 10:01:00'; $error = null;
try { invoke( array( 'apply', 'core-neighbours' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && $db === $expected_drift, 'neighbours concurrent page drift restores core without overwriting independent change' );
$db = $neighbours_before; invoke_neighbours_rehearsal( array( 'apply' ) ); $neighbours_snap = snapshot_path(); $neighbours_applied = $db;
$expected_neighbours = $neighbours_before; $expected_neighbours[1380]['post']['post_content'] = substr( file_get_contents( __DIR__ . '/peek-nav.php' ), 6 ); $expected_neighbours[1380]['post']['post_modified'] = $expected_neighbours[1380]['post']['post_modified_gmt'] = $clock;
check( $db === $expected_neighbours, 'neighbours changes only #1380 code/dates, preserving every complete page record and core setting' );
invoke_neighbours_rehearsal( array( 'apply' ) ); check( $db === $neighbours_applied, 'neighbours apply is idempotent' );
refusal( array( 'apply', 'core-input-focus' ), 'frozen input-focus cannot overwrite neighbours' );
refusal( array( 'rollback', $focus_snap ), 'historical focus snapshot cannot overwrite neighbours' );
focus_rehearsal_refusal( array( 'rollback', $neighbours_snap ), 'neighbours snapshot cannot enter historical focus adapter' );
$neighbours_snapshot = json_decode( file_get_contents( $neighbours_snap ), true );
check( $neighbours_snapshot['bundle'] === 'peek-core-neighbours-v1' && $neighbours_snapshot['payloads']['before_sha256'] === 'a3a503f59dc8a557a24e5a03967481af824f29136668b1158d9cfa47ae3e537c' && $neighbours_snapshot['old'] === $focus_snapshot['new'], 'neighbours snapshot binds exact historical focus result including dates' );
foreach ( array( 'applied' => $neighbours_applied, 'original' => $neighbours_before ) as $verify_state => $baseline ) {
    $args = 'original' === $verify_state ? array( 'verify', $neighbours_snap, 'original' ) : array( 'verify', $neighbours_snap );
    $db = $baseline; snapshot_verify_check( $args, true, 'neighbours exact ' . $verify_state );
    snapshot_verify_check( 'original' === $verify_state ? array( 'verify', $neighbours_snap ) : array( 'verify', $neighbours_snap, 'original' ), false, 'neighbours cannot claim opposite state' );
    foreach ( array_keys( $baseline ) as $record_id ) {
        foreach ( array( 'missing', 'code', 'date', 'gmt-date', 'author', 'meta', 'terms', 'status', 'duplicate-trash', 'id' ) as $mutation ) {
            $db = $baseline;
            if ( $mutation === 'missing' ) { unset( $db[$record_id] ); }
            if ( $mutation === 'code' ) { $db[$record_id]['post']['post_content'] .= '// drift'; }
            if ( $mutation === 'date' ) { $db[$record_id]['post']['post_modified'] = '2027-01-01 00:00:00'; }
            if ( $mutation === 'gmt-date' ) { $db[$record_id]['post']['post_modified_gmt'] = '2027-01-01 00:00:00'; }
            if ( $mutation === 'author' ) { $db[$record_id]['post']['post_author'] = '99'; }
            if ( $mutation === 'meta' ) { $db[$record_id]['meta']['unknown'] = array( 'independent' ); }
            if ( $mutation === 'terms' ) { $db[$record_id]['terms']['wpcode_location'] = array( 'footer' ); }
            if ( $mutation === 'status' ) { $db[$record_id]['post']['post_status'] = 'draft'; }
            if ( $mutation === 'duplicate-trash' || $mutation === 'id' ) { $db[9999] = $db[$record_id]; $db[9999]['id'] = $db[9999]['post']['ID'] = 9999; }
            if ( $mutation === 'duplicate-trash' ) { $db[9999]['post']['post_status'] = 'trash'; }
            if ( $mutation === 'id' ) { unset( $db[$record_id] ); }
            snapshot_verify_check( $args, false, 'neighbours ' . $verify_state . ' record ' . $record_id . ' ' . $mutation );
            if ( 'applied' === $verify_state ) { refusal( array( 'rollback', $neighbours_snap ), 'neighbours rollback refuses ' . $record_id . ' ' . $mutation ); }
            elseif ( ! in_array( $mutation, array( 'date', 'gmt-date', 'author' ), true ) && ( 'id' !== $mutation || 1380 === $record_id ) ) { refusal( array( 'apply', 'core-neighbours' ), 'neighbours apply refuses ' . $record_id . ' ' . $mutation ); }
        }
    }
}
$db = $neighbours_applied;
foreach ( array( 'bundle', 'component', 'slug', 'missing-slug', 'home', 'incomplete', 'before-payload', 'new-payload', 'old-hash', 'new-hash', 'old-content-rehashed', 'new-author-rehashed', 'ids-rehashed', 'pages-hash', 'pages-missing', 'page-date-rehashed' ) as $mutation ) {
    $s = $neighbours_snapshot;
    if ( $mutation === 'bundle' ) { $s['bundle'] = 'peek-core-input-focus-v1'; }
    if ( $mutation === 'component' ) { $s['component'] = 'core-input-focus'; }
    if ( $mutation === 'slug' ) { $s['slug'] = 'thai-massage'; }
    if ( $mutation === 'missing-slug' ) { unset( $s['slug'] ); }
    if ( $mutation === 'home' ) { $s['home'] = 'https://staging39.thaisolitude.com'; }
    if ( $mutation === 'incomplete' ) { $s['complete'] = false; }
    if ( $mutation === 'before-payload' ) { $s['payloads']['before_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-payload' ) { $s['payloads']['code_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-hash' ) { $s['old_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'new-hash' ) { $s['new_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'old-content-rehashed' ) { $s['old']['post']['post_content'] .= '// drift'; $s['old_sha256'] = hash( 'sha256', json_encode( $s['old'] ) ); }
    if ( $mutation === 'new-author-rehashed' ) { $s['new']['post']['post_author'] = '99'; $s['new_sha256'] = hash( 'sha256', json_encode( $s['new'] ) ); }
    if ( $mutation === 'ids-rehashed' ) { foreach ( array( 'old', 'new' ) as $which ) { $s[$which]['id'] = $s[$which]['post']['ID'] = 9999; $s[$which . '_sha256'] = hash( 'sha256', json_encode( $s[$which] ) ); } }
    if ( $mutation === 'pages-hash' ) { $s['pages_sha256'] = str_repeat( '0', 64 ); }
    if ( $mutation === 'pages-missing' ) { unset( $s['pages']['chair-massage'] ); $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    if ( $mutation === 'page-date-rehashed' ) { $s['pages']['thai-massage']['post']['post_modified'] = '2000-01-01 00:00:00'; $s['pages_sha256'] = hash( 'sha256', json_encode( $s['pages'] ) ); }
    $bad_neighbours = snap_write( $s, 'neighbours-' . $mutation );
    snapshot_verify_check( array( 'verify', $bad_neighbours ), false, 'neighbours snapshot ' . $mutation );
    neighbours_rehearsal_refusal( array( 'rollback', $bad_neighbours ), 'neighbours rollback snapshot ' . $mutation );
}
$db = $neighbours_applied; invoke_neighbours_rehearsal( array( 'rollback', $neighbours_snap ) ); check( $db === $neighbours_before, 'neighbours rollback restores exact v7 and every complete page record' );
$writes = $write_calls; $refreshes = $cache_refreshes; $files = backup_hashes();
invoke_neighbours_rehearsal( array( 'rollback', $neighbours_snap ) ); check( $db === $neighbours_before && $writes === $write_calls && $refreshes === $cache_refreshes && $files === backup_hashes(), 'neighbours repeated rollback has no writes/cache/file changes' );
snapshot_verify_check( array( 'verify', $focus_snap ), true, 'original focus snapshot still verifies restored v7 with exact historical dates' );
invoke( array( 'rollback', $focus_snap ) ); check( $db === $focus_before, 'original focus snapshot retains exact v6 recovery after neighbours rollback' );
$db = $neighbours_before; $clock = '2026-11-10 10:02:00'; invoke_neighbours_rehearsal( array( 'apply' ) ); $reapplied_neighbours_snap = snapshot_path();
snapshot_verify_check( array( 'verify', $neighbours_snap ), false, 'old neighbours snapshot cannot verify a later apply with new dates' );
snapshot_verify_check( array( 'verify', $reapplied_neighbours_snap ), true, 'later apply binds its own returned snapshot' );
invoke_neighbours_rehearsal( array( 'rollback', $reapplied_neighbours_snap ) ); check( $db === $neighbours_before, 'later apply rollback restores the same exact v7 baseline' );
// Execute the actual read-only origin inventory program from the push wrapper.
// Changing published coverage must block a forward deployment, without adding a runtime map.
$push_source = file_get_contents( __DIR__ . '/push-peek-nav.sh' );
if ( ! preg_match( "/read -r -d '' INVENTORY <<'PHP' \\|\\| true\\n(.*?)\\nPHP\\n/s", $push_source, $inventory_match ) ) { throw new RuntimeException( 'Missing reviewed-path inventory program.' ); }
$inventory_php = $inventory_match[1];
function inventory_output( $review = '' ) {
    putenv( 'WWM_PEEK_REVIEWED_PAGES=' . $review ); ob_start();
    try { eval( $GLOBALS['inventory_php'] ); return ob_get_contents(); }
    finally { ob_end_clean(); putenv( 'WWM_PEEK_REVIEWED_PAGES' ); }
}
function inventory_refusal( $review, $label ) { $before = $GLOBALS['db']; $error = null; try { inventory_output( $review ); } catch ( Throwable $e ) { $error = $e; } check( $error !== null && $GLOBALS['db'] === $before, 'origin inventory: ' . $label ); }
$inventory_pages = array(
    array( 'ID' => 1, 'post_name' => 'home', 'permalink' => 'http://staging39.thaisolitude.com/' ),
    array( 'ID' => 2, 'post_name' => 'corporate-massage-portland', 'permalink' => 'https://staging39.thaisolitude.com/corporate-massage-portland/' ),
    array( 'ID' => 3, 'post_name' => 'new-child', 'permalink' => 'https://staging39.thaisolitude.com/parent/new-child/' ),
    array( 'ID' => 4, 'post_name' => 'private-draft', 'post_status' => 'draft', 'permalink' => 'https://staging39.thaisolitude.com/private-draft/' ),
);
$published_inventory = $inventory_pages;
$review = array( 'pages' => array( array( 'id' => 3, 'path' => '/parent/new-child/', 'title' => 'Any title' ), array( 'id' => 1, 'path' => '/', 'title' => 'Home' ), array( 'id' => 2, 'path' => '/corporate-massage-portland/', 'title' => 'Corporate' ) ) );
$review_path = snap_write( $review, 'reviewed-title-pages' );
$expected_inventory = "page-1\t/\thome\npage-2\t/corporate-massage-portland/\tcorporate-massage-portland\npage-3\t/parent/new-child/\tnew-child\n";
check( inventory_output( $review_path ) === $expected_inventory, 'origin inventory covers published nested/unlisted pages and deduplicates home, excluding drafts' );
check( inventory_output() === $expected_inventory, 'origin verification can enumerate actual pages without a reviewed-map restriction' );
unset( $inventory_pages[0] );
$review_without_front = $review; $review_without_front['pages'][1]['id'] = 0;
check( str_starts_with( inventory_output( snap_write( $review_without_front, 'review-without-front-page' ) ), "home\t/\t__home__\n" ), 'origin inventory includes home when no published page owns the root' );
$inventory_pages = $published_inventory;
foreach ( array( 'missing-route', 'extra-route', 'duplicate-route', 'external-route', 'protocol-relative', 'newline', 'empty', 'bad-json' ) as $mutation ) {
    $r = $review;
    if ( $mutation === 'missing-route' ) { array_pop( $r['pages'] ); }
    if ( $mutation === 'extra-route' ) { $r['pages'][] = array( 'path' => '/not-published/' ); }
    if ( $mutation === 'duplicate-route' ) { $r['pages'][] = $r['pages'][0]; }
    if ( $mutation === 'external-route' ) { $r['pages'][0]['path'] = 'https://thaisolitude.com/'; }
    if ( $mutation === 'protocol-relative' ) { $r['pages'][0]['path'] = '//thaisolitude.com/'; }
    if ( $mutation === 'newline' ) { $r['pages'][0]['path'] = "/line\nbreak/"; }
    if ( $mutation === 'empty' ) { $r['pages'] = array(); }
    $r_path = snap_write( $r, 'reviewed-' . $mutation );
    if ( $mutation === 'bad-json' ) { file_put_contents( $r_path, '{"pages":' ); }
    inventory_refusal( $r_path, 'refuses reviewed ' . $mutation );
}
inventory_refusal( $testdir . '/missing-inventory.json', 'refuses missing reviewed inventory' );
foreach ( array( 'https://thaisolitude.com/path/', 'https://staging39.thaisolitude.com:443/path/', 'https://user@staging39.thaisolitude.com/path/', 'https://staging39.thaisolitude.com/path/#section', "https://staging39.thaisolitude.com/line%0Abreak/", 'https://staging39.thaisolitude.com//other/' ) as $url ) {
    $inventory_pages = $published_inventory; $inventory_pages[2]['permalink'] = $url;
    // Percent-encoded route characters are data, never shell syntax; only coverage changes.
    inventory_refusal( $review_path, 'refuses foreign/ambiguous or unreviewed permalink ' . $url );
}
$inventory_pages = $published_inventory; $inventory_pages[2]['permalink'] = $inventory_pages[1]['permalink']; inventory_refusal( '', 'refuses duplicate actual non-home paths' );
$inventory_pages = $published_inventory; $inventory_pages[2]['permalink'] = $inventory_pages[0]['permalink']; inventory_refusal( '', 'refuses duplicate actual home paths' );
$inventory_pages = $published_inventory; $inventory_pages[2]['post_name'] = ''; inventory_refusal( '', 'refuses empty slug before shell field parsing' );
$inventory_pages = $published_inventory; $inventory_pages[2]['post_name'] = "new\tchild"; inventory_refusal( '', 'refuses tab-separated slug injection' );
$inventory_pages = $published_inventory;
// Exercise the real phase dispatch: all sitewide forward transitions require
// reviewed coverage before touching WP; recovery remains possible after route edits.
$phase_start = strpos( $push_source, "case \"\$phase\" in\n" ); $phase_end = strpos( $push_source, "\nEOS\n", $phase_start );
if ( false === $phase_start || false === $phase_end ) { throw new RuntimeException( 'Missing remote phase dispatch.' ); }
$phase_dispatch = substr( $push_source, $phase_start, $phase_end - $phase_start );
function phase_check( $phase, $component, $coverage ) {
    $script = "set -euo pipefail\n";
    foreach ( array( 'phase', 'component', 'coverage' ) as $name ) { $script .= $name . '=' . escapeshellarg( $$name ) . "\n"; }
    $script .= "snap=/private/example.json\nreviewed_inventory() { [ \"\$coverage\" = ok ]; }\nrun() { printf 'RUN %s\\n' \"\$1\"; }\npurge() { :; }\n" . $GLOBALS['phase_dispatch'];
    $pipes = array(); $proc = proc_open( array( 'bash', '-c', $script ), array( array( 'pipe', 'r' ), array( 'pipe', 'w' ), array( 'pipe', 'w' ) ), $pipes );
    if ( ! is_resource( $proc ) ) { throw new RuntimeException( 'Cannot run offline forward coverage checks.' ); }
    fclose( $pipes[0] ); $out = stream_get_contents( $pipes[1] ); $err = stream_get_contents( $pipes[2] ); fclose( $pipes[1] ); fclose( $pipes[2] );
    return array( proc_close( $proc ), $out, $err );
}
foreach ( array( 'core-title-strip', 'core-title-rail', 'core-kiro-fixes', 'core-input-focus', 'core-neighbours' ) as $component ) {
    foreach ( array( 'preflight', 'apply' ) as $phase ) {
        $result = phase_check( $phase, $component, 'changed' );
        check( 0 !== $result[0] && ! str_contains( $result[1], 'RUN ' ), $component . ' ' . $phase . ' refuses changed published coverage before installer call' );
        $result = phase_check( $phase, $component, 'ok' );
        check( 0 === $result[0] && str_contains( $result[1], 'RUN ' . $phase ), $component . ' ' . $phase . ' accepts reviewed published coverage' );
    }
    $result = phase_check( 'rollback', $component, 'changed' );
    check( 0 === $result[0] && str_contains( $result[1], 'RUN rollback' ), $component . ' exact recovery does not depend on unchanged published routes' );
}
// Run the wrapper's actual verification branch with local WP/curl fixtures.
// No SSH or network process exists in this harness; state and HTML are independent inputs.
$verify_start = strpos( $push_source, "  verify)\n" ); $verify_end = strpos( $push_source, "  rollback) snap=", $verify_start );
if ( false === $verify_start || false === $verify_end ) { throw new RuntimeException( 'Missing origin verification branch.' ); }
$verify_branch = substr( $push_source, $verify_start, $verify_end - $verify_start );
$origin_stubs = <<<'BASH'
cfgs=$(mktemp); doc=/unused; installer=/unused; want=staging39.thaisolitude.com
wp() {
  local part="${*: -1}"
  case "$part" in
    core-neighbours)
      if [ "$model" = latest ]; then echo 'Success: core-neighbours applied; nothing written.'
      elif [ "$model" = v7 ] && [ -z "$missing_data" ]; then echo 'Success: core-neighbours ready; nothing written.'
      else return 1; fi ;;
    core-input-focus)
      if [ "$model" = v7 ]; then echo 'Success: core-input-focus applied; nothing written.'
      elif [ "$model" = v6 ] && [ -z "$missing_data" ]; then echo 'Success: core-input-focus ready; nothing written.'
      else return 1; fi ;;
    core-kiro-fixes)
      if [ "$model" = v6 ]; then echo 'Success: core-kiro-fixes applied; nothing written.'
      elif [ "$model" = v5 ] && [ -z "$missing_data" ]; then echo 'Success: core-kiro-fixes ready; nothing written.'
      else return 1; fi ;;
    core-title-rail)
      if [ "$model" = v5 ]; then echo 'Success: core-title-rail applied; nothing written.'
      elif [ "$model" = v4 ] && [ -z "$missing_data" ]; then echo 'Success: core-title-rail ready; nothing written.'
      else return 1; fi ;;
    core-title-strip)
      if [ "$model" = v4 ]; then echo 'Success: core-title-strip applied; nothing written.'
      elif [ "$model" = v3 ] && [ -z "$missing_data" ]; then echo 'Success: core-title-strip ready; nothing written.'
      else return 1; fi ;;
    core-summary-touch)
      if [ "$model" = v3 ]; then echo 'Success: core-summary-touch applied; nothing written.'
      elif [ "$model" = v2 ]; then echo 'Success: core-summary-touch ready; nothing written.'
      else return 1; fi ;;
    core-upgrade)
      if [ "$model" = v2 ]; then echo 'Success: core-upgrade applied; nothing written.'
      elif [ "$model" = v1 ]; then echo 'Success: core-upgrade ready; nothing written.'
      else return 1; fi ;;
    core)
      if [ "$model" = v1 ]; then echo 'Success: core applied; nothing written.'
      elif [ "$model" = legacy ]; then echo 'Success: core ready; nothing written.'
      else return 1; fi ;;
    thai-massage|therapeutic-massage|deep-tissue-massage|chair-massage)
      if [ "$part" = "$missing_data" ]; then echo "Success: page:$part ready; nothing written."
      else echo "Success: page:$part applied; nothing written."; fi ;;
    *) return 1 ;;
  esac
}
inventory() {
  printf 'home\t/\t__home__\n'
  for p in thai-massage therapeutic-massage deep-tissue-massage chair-massage services corporate-massage-portland work-with-me; do printf '%s\t/%s/\t%s\n' "$p" "$p" "$p"; done
  printf 'nested\t/parent/unlisted/\tunlisted\n'
}
curl() {
  local output='' url='' route pg has_core=0 has_data=0 jump=0
  while [ "$#" -gt 0 ]; do
    case "$1" in -o) output=$2; shift 2 ;; https://*) url=$1; shift ;; *) shift ;; esac
  done
  route=${url#https://staging39.thaisolitude.com}; pg=${route#/}; pg=${pg%/}
  case "$pg" in thai-massage|therapeutic-massage|deep-tissue-massage|chair-massage) jump=1; if [ "$render_mode" != legacy ] && [ "$pg" != "$missing_data" ]; then has_data=1; has_core=1; fi ;; services) jump=1 ;; esac
  case "$render_mode" in latest|v7|v6|v5|v4) has_core=1 ;; esac
  if [ "$render_mode" = legacy ] && [ "$pg" = thai-massage ]; then has_core=1; fi
  if [ "$fault" = missing-core ] && [ "$pg" = parent/unlisted ]; then has_core=0; fi
  if [ "$fault" = extra-data ] && [ "$pg" = services ]; then has_data=1; fi
  if [ "$fault" = removed-jump ] && [ "$pg" = services ]; then jump=0; fi
  {
    if [ "$has_core" = 1 ]; then printf '<style id="ts-peek-nav-css"></style><script id="ts-peek-nav-runtime"></script>'; fi
    case "$render_mode" in latest|v7|v6|v5|v4) printf '<script id="ts-peek-nav-config"></script>' ;; esac
    if [ "$has_data" = 1 ]; then printf '<script id="ts-peek-nav-data"></script>'; fi
    if [ "$jump" = 1 ]; then printf '<nav class="ts-jump"></nav>'; fi
    if [ "$fault" = duplicate-core ] && [ "$pg" = '' ]; then printf '<script id="ts-peek-nav-runtime"></script>'; fi
  } > "$output"
  if [ "$fault" = challenge ] && [ "$pg" = parent/unlisted ]; then printf 202; else printf 200; fi
}
BASH;
function origin_check( $model, $render_mode, $missing_data = '', $fault = '' ) {
    $script = "set -euo pipefail\n";
    foreach ( array( 'model', 'render_mode', 'missing_data', 'fault' ) as $name ) { $script .= $name . '=' . escapeshellarg( $$name ) . "\n"; }
    $script .= $GLOBALS['origin_stubs'] . "\ncase verify in\n" . $GLOBALS['verify_branch'] . "esac\n";
    $pipes = array(); $proc = proc_open( array( 'bash', '-c', $script ), array( array( 'pipe', 'r' ), array( 'pipe', 'w' ), array( 'pipe', 'w' ) ), $pipes );
    if ( ! is_resource( $proc ) ) { throw new RuntimeException( 'Cannot run offline origin guard checks.' ); }
    fclose( $pipes[0] ); $out = stream_get_contents( $pipes[1] ); $err = stream_get_contents( $pipes[2] ); fclose( $pipes[1] ); fclose( $pipes[2] );
    return array( proc_close( $proc ), $out, $err );
}
foreach ( array( 'latest', 'v7', 'v6', 'v5', 'v4', 'v3', 'v2', 'v1', 'legacy' ) as $version ) { $result = origin_check( $version, $version ); check( 0 === $result[0] && str_contains( $result[1], '200 /parent/unlisted/' ), 'origin verifier checks all actual pages with exact ' . $version . ' scope' ); }
foreach ( array( 'latest', 'v7', 'v6', 'v5', 'v4', 'v3' ) as $version ) { $result = origin_check( $version, $version, 'chair-massage' ); check( 0 === $result[0], 'origin verifier preserves separate page removal under ' . $version ); }
$result = origin_check( 'v3', 'latest' ); check( 0 !== $result[0] && str_contains( $result[1], 'FAIL' ), 'ready predecessor cannot be mistaken for applied sitewide core' );
$result = origin_check( 'unknown', 'latest' ); check( 0 !== $result[0] && ! str_contains( $result[1], '200 ' ), 'unknown core fails before any origin fetch' );
foreach ( array( 'missing-core', 'extra-data', 'duplicate-core', 'removed-jump', 'challenge' ) as $fault ) {
    $result = origin_check( 'latest', 'latest', '', $fault );
    check( 0 !== $result[0] && str_contains( $result[1], 'challenge' === $fault ? 'INVALID' : 'FAIL' ), 'origin verifier refuses ' . $fault . ': ' . json_encode( $result ) );
}
$clock = '2026-11-09 05:00:00';
// Execute the real generated page snippets and core against minimal rendering hooks.
$db = $original; invoke( array( 'apply', 'page', 'thai-massage' ) );
invoke( array( 'apply', 'page', 'therapeutic-massage' ) );
$filters = array();
foreach ( $db as $id => $r ) { if ( $id !== 1380 ) { eval( $r['post']['post_content'] ); } }
$saved_filters = $filters;
eval( "namespace PeekFrozenV3;\n" . substr( file_get_contents( __DIR__ . '/peek-nav-shared-v3.php' ), 6 ) );
$page = 'services'; ob_start(); do_action( 'wp_head' ); do_action( 'wp_footer' ); $frozen_html = ob_get_clean();
check( $frozen_html === '', 'frozen v3 unregistered page still emits nothing' );
$filters = $saved_filters; $page = 'thai-massage';
require __DIR__ . '/peek-nav.php'; require __DIR__ . '/peek-nav.php';
function render() { ob_start(); do_action( 'wp_head' ); do_action( 'wp_footer' ); return ob_get_clean(); }
$html = render(); check( substr_count( $html, 'id="ts-peek-nav-runtime"' ) === 1 && substr_count( $html, 'id="ts-peek-nav-css"' ) === 1, 'duplicate core registers output once' );
preg_match( '/id="ts-peek-nav-data">(.*?)<\/script>/s', $html, $m ); $decoded = json_decode( $m[1], true );
check( $decoded['slug'] === 'thai-massage' && ! str_contains( $m[1], 'therapeutic-massage' ), 'emits only current page data' );
$page = 'services'; $html = render();
check( substr_count( $html, 'id="ts-peek-nav-css"' ) === 1 && substr_count( $html, 'id="ts-peek-nav-runtime"' ) === 1 && substr_count( $html, 'id="ts-peek-nav-config"' ) === 1 && ! str_contains( $html, 'id="ts-peek-nav-data"' ), 'unregistered page gets one shared core without page data' );
$page = 'therapeutic-massage'; $html = render(); preg_match( '/id="ts-peek-nav-data">(.*?)<\/script>/s', $html, $m ); $decoded = json_decode( $m[1], true );
$therapeutic = json_decode( file_get_contents( __DIR__ . '/peek-data/therapeutic-massage.json' ), true );
check( $decoded === $therapeutic && ! str_contains( $m[1], 'Movement meets massage' ), 'approved Therapeutic emits its exact approved payload, no Thai lists' );
$draft_fixture = static function ( $pages ) { $pages['therapeutic-massage']['approval']['status'] = 'draft'; return $pages; };
add_filter( 'ts_pk_pages', $draft_fixture, 99 );
$html = render(); preg_match( '/id="ts-peek-nav-data">(.*?)<\/script>/s', $html, $m ); $decoded = json_decode( $m[1], true );
check( $decoded['approval']['status'] === 'draft' && $decoded['items'] === array() && $decoded['coverage'] === array(), 'draft fixture withholds summaries for whole-paragraph fallback' );
remove_filter( 'ts_pk_pages', $draft_fixture, 99 );
$admin = true; check( render() === '', 'admin emits nothing' ); $admin = false; $feed = true; check( render() === '', 'feed emits nothing' ); $feed = false;
$page = 'thai-massage';
add_filter( 'ts_pk_pages', static function ( $pages ) { $pages['thai-massage']['items'][] = array( '</script><script>alert("x")</script>&\'雪', 'ts-what-it-is' ); return $pages; }, 99 );
$html = render(); preg_match( '/id="ts-peek-nav-data">(.*?)<\/script>/s', $html, $m ); $decoded = json_decode( $m[1], true );
check( ! str_contains( $m[1], '<' ) && ! str_contains( $m[1], '&' ) && $decoded['items'][count( $decoded['items'] ) - 1][0] === '</script><script>alert("x")</script>&\'雪', 'JSON script safely round-trips hostile markup Unicode quotes' );
require __DIR__ . '/core-input-focus/test-render.php';
$pass += run_focus_render_tests();
foreach ( glob( $testdir . '/backups/*' ) as $f ) { unlink( $f ); } unlink( $bad_path ); rmdir( $testdir . '/backups' ); rmdir( $testdir . '/web' ); rmdir( $testdir );
echo 'Peek installer/emission: ' . $pass . " checks passed\n";

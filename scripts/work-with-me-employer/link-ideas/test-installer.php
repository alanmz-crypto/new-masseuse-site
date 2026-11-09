<?php
// Offline WordPress model: exercise destructive guards, exact migration and emission.
error_reporting( E_ALL );
$testdir = sys_get_temp_dir() . '/peek-installer-' . bin2hex( random_bytes( 5 ) );
mkdir( $testdir ); mkdir( $testdir . '/web' ); mkdir( $testdir . '/backups' );
define( 'ABSPATH', $testdir . '/web/' ); define( 'ARRAY_A', 'ARRAY_A' );
putenv( 'WWM_BACKUP_DIR=' . $testdir . '/backups' );
$db = array(); $next_id = 2000; $filters = array(); $home = 'http://localhost:8090'; $environment_type = 'local'; $clock = '2026-11-09 05:00:00'; $mutate_page_on_refresh = null; $fail_write = false; $fail_cache = false; $fail_snapshot_complete = false; $page = 'thai-massage'; $admin = false; $feed = false;
class WP_CLI { static function error( $m ) { throw new RuntimeException( $m ); } static function success( $m ) { $GLOBALS['message'] = $m; } }
function home_url() { return $GLOBALS['home']; }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function wp_parse_url( $s, $component ) { return parse_url( $s, $component ); }
function get_stylesheet() { return 'astra'; }
function current_user_can( $s ) { return true; }
function wp_get_environment_type() { return $GLOBALS['environment_type']; }
function wpcode() { static $o; return $o ??= (object) array( 'cache' => new class { function cache_all_loaded_snippets() { if ( $GLOBALS['fail_cache'] ) { $GLOBALS['fail_cache'] = false; throw new RuntimeException( 'Forced cache failure' ); } if ( is_callable( $GLOBALS['mutate_page_on_refresh'] ) ) { $callback = $GLOBALS['mutate_page_on_refresh']; $GLOBALS['mutate_page_on_refresh'] = null; $callback(); } } } ); }
function get_object_taxonomies( $t ) { return array( 'wpcode_type', 'wpcode_location' ); }
function get_post_stati() { return array( 'publish', 'draft', 'trash' ); }
function get_posts( $args ) { return array_map( static fn( $r ) => (object) $r['post'], array_values( $GLOBALS['db'] ) ); }
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
function is_page( $slug = null ) { return $slug === null ? (bool) $GLOBALS['page'] : $GLOBALS['page'] === $slug; }
function get_queried_object() { return (object) array( 'post_name' => $GLOBALS['page'] ); }
function wp_update_post( $arr, $error = false ) {
    if ( $GLOBALS['fail_write'] ) { $GLOBALS['fail_write'] = false; return 0; }
    $arr = wp_unslash( $arr ); $id = $arr['ID']; $p = array_merge( $GLOBALS['db'][ $id ]['post'], $arr );
    $p['post_modified'] = $p['post_modified_gmt'] = current_time( 'mysql' );
    $GLOBALS['db'][ $id ]['post'] = apply_filters( 'wp_insert_post_data', $p, $arr ); return $id;
}
function wp_delete_post( $id, $force ) { $p = $GLOBALS['db'][ $id ] ?? null; unset( $GLOBALS['db'][ $id ] ); return $p; }
function record( $id, $title, $code, $note ) {
    $meta = array( '_wpcode_auto_insert' => array( '1' ), '_wpcode_priority' => array( '10' ), '_wpcode_note' => array( $note ), '_wpcode_shortcode_attributes' => array( 'a:0:{}' ) ); ksort( $meta );
    return array( 'id' => $id, 'post' => array( 'ID' => $id, 'post_type' => 'wpcode', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $code, 'post_modified' => '2026-11-09 01:00:37', 'post_modified_gmt' => '2026-11-09 01:00:37', 'post_author' => '1', 'post_name' => 'historical-name' ), 'meta' => $meta, 'terms' => array( 'wpcode_location' => array( 'everywhere' ), 'wpcode_type' => array( 'php' ) ) );
}
class WPCode_Snippet { function __construct( private $args ) {} function save() { $id = $GLOBALS['next_id']++; $GLOBALS['db'][ $id ] = record( $id, $this->args['title'], $this->args['code'], $this->args['note'] ); return $id; } }
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
function invoke_package( $package, $args ) { require $package . '/install-peek-nav.php'; }
function snapshot_path() { preg_match( '/Before-snapshot: (.+)$/', $GLOBALS['message'], $m ); if ( ! isset( $m[1] ) ) { throw new RuntimeException( 'No completed snapshot: ' . $GLOBALS['message'] ); } return $m[1]; }
function snap_write( $data, $name ) { $p = $GLOBALS['testdir'] . '/backups/' . $name . '.json'; file_put_contents( $p, json_encode( $data ) ); return $p; }
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
foreach ( array( 'install-peek-nav.php', 'peek-nav-legacy.php', 'peek-nav-shared-v1.php', 'peek-nav-shared-v2.php', 'peek-nav.php' ) as $name ) { copy( __DIR__ . '/' . $name, $package . '/' . $name ); }
$db = $upgrade_before; file_put_contents( $package . '/peek-nav-shared-v1.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-upgrade' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v1 payload differs' ) && $db === $upgrade_before, 'altered frozen v1 file hash stops upgrade before any write' );
copy( __DIR__ . '/peek-nav-shared-v1.php', $package . '/peek-nav-shared-v1.php' ); file_put_contents( $package . '/peek-nav-shared-v2.php', "\n// changed", FILE_APPEND ); $error = null;
try { invoke_package( $package, array( 'apply', 'core-summary-touch' ) ); } catch ( Throwable $e ) { $error = $e; }
check( $error !== null && str_contains( $error->getMessage(), 'Frozen shared v2 payload differs' ) && $db === $upgrade_before, 'altered frozen v2 file hash stops summary-touch upgrade before any write' );
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
check( $db[1380]['post']['post_content'] === substr( file_get_contents( __DIR__ . '/peek-nav.php' ), 6 ) && $db[1380]['post']['post_modified'] === $clock && array_diff_key( $db, array( 1380 => true ) ) === $data_before, 'summary-touch changes only core payload/dates and preserves all page records' );
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
$clock = '2026-11-09 05:00:00';
// Execute the real generated page snippets and core against minimal rendering hooks.
$db = $original; invoke( array( 'apply', 'page', 'thai-massage' ) );
invoke( array( 'apply', 'page', 'therapeutic-massage' ) );
$filters = array();
foreach ( $db as $id => $r ) { if ( $id !== 1380 ) { eval( $r['post']['post_content'] ); } }
require __DIR__ . '/peek-nav.php'; require __DIR__ . '/peek-nav.php';
function render() { ob_start(); do_action( 'wp_head' ); do_action( 'wp_footer' ); return ob_get_clean(); }
$html = render(); check( substr_count( $html, 'id="ts-peek-nav-runtime"' ) === 1 && substr_count( $html, 'id="ts-peek-nav-css"' ) === 1, 'duplicate core registers output once' );
preg_match( '/id="ts-peek-nav-data">(.*?)<\/script>/s', $html, $m ); $decoded = json_decode( $m[1], true );
check( $decoded['slug'] === 'thai-massage' && ! str_contains( $m[1], 'therapeutic-massage' ), 'emits only current page data' );
$page = 'services'; check( render() === '', 'unregistered page emits nothing' );
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
foreach ( glob( $testdir . '/backups/*' ) as $f ) { unlink( $f ); } unlink( $bad_path ); rmdir( $testdir . '/backups' ); rmdir( $testdir . '/web' ); rmdir( $testdir );
echo 'Peek installer/emission: ' . $pass . " checks passed\n";

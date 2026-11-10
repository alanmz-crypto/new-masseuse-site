<?php
/**
 * Offline release state-machine controls; run with PHP 8.5 + PDO SQLite.
 * A temporary package changes only the baseline hash profile for synthetic raw
 * WordPress rows. Installer logic, localhost guard, routes and payload bytes are
 * unchanged; production payload integrity is checked separately before/after.
 * SQLite executes the actual prepared SQL with byte-sensitive comparisons.
 * WordPress saves, shared taxonomy counts, dates and derived cache are modeled.
 * This does not establish MySQL/WPCode integration or visitor behavior: the
 * actual live-copy rehearsal and rendered rollback gates remain mandatory.
 */
error_reporting( E_ALL );
define( 'ARRAY_A', 'ARRAY_A' );
$testdir = sys_get_temp_dir() . '/peek-release-installer-' . bin2hex( random_bytes( 6 ) );
mkdir( $testdir, 0700 ); mkdir( "$testdir/web", 0700 ); mkdir( "$testdir/backups", 0700 );
register_shutdown_function( static function () use ( $testdir ) { if ( is_dir( $testdir ) ) { remove_tree( $testdir ); } } );
define( 'ABSPATH', "$testdir/web/" );
putenv( "WWM_BACKUP_DIR=$testdir/backups" );
$home = 'http://localhost:8090'; $theme = 'astra'; $admin = true; $environment_type = 'local';
$clock = '2026-11-10 03:00:00'; $wp_version = '7.1.4'; $logs = array(); $message = '';
$pass = 0; $events = array(); $failures = array(); $event_counts = array();

class Harness_Error extends RuntimeException { function __construct( $message ) { $GLOBALS['harness_error'] = $message; parent::__construct( $message ); } }
class Refusal_Error extends RuntimeException {}
set_error_handler( static function ( $level, $message, $file, $line ) { throw new Harness_Error( $message . ' at ' . basename( $file ) . ':' . $line ); } );
class WP_CLI {
    static function error( $value ) {
        if ( preg_match( '/Call to undefined|Undefined (?:variable|array key)|Too few arguments|Unknown named parameter|must be of type/', $value ) ) { throw new Harness_Error( $value ); }
        throw new Refusal_Error( $value );
    }
    static function log( $value ) {
        if ( ( $GLOBALS['fail_state_diagnostic_log'] ?? false ) && str_starts_with( $value, 'RELEASE-STATE-DIAGNOSTIC ' ) ) { $GLOBALS['fail_state_diagnostic_log'] = false; throw new RuntimeException( 'Synthetic diagnostic log failure' ); }
        $GLOBALS['logs'][] = $value;
    }
    static function success( $value ) { $GLOBALS['message'] = $value; }
    static function warning( $value ) { $GLOBALS['logs'][] = 'Warning: ' . $value; }
}
class WP_Error { function __construct( public $code, public $message ) {} function get_error_message() { return $this->message; } }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function home_url() { return $GLOBALS['home']; }
function get_stylesheet() { return $GLOBALS['theme']; }
function current_user_can( $capability ) { return $GLOBALS['admin']; }
function wp_get_environment_type() { return $GLOBALS['environment_type']; }
function current_time( $type, $gmt = false ) { return $gmt ? get_gmt_from_date( $GLOBALS['clock'] ) : $GLOBALS['clock']; }
function get_gmt_from_date( $date, $format = 'Y-m-d H:i:s' ) { return ( new DateTimeImmutable( $date, new DateTimeZone( 'America/Los_Angeles' ) ) )->setTimezone( new DateTimeZone( 'UTC' ) )->format( $format ); }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function wp_parse_url( $value, $component = -1 ) { return parse_url( $value, $component ); }
function wp_slash( $value ) { return is_array( $value ) ? array_map( 'wp_slash', $value ) : ( is_string( $value ) ? addslashes( $value ) : $value ); }
function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : ( is_string( $value ) ? stripslashes( $value ) : $value ); }
function wp_json_encode( $value, $flags = 0 ) {
    if ( isset( $GLOBALS['encode_failure'] ) && $GLOBALS['encode_failure']( $value ) ) { $GLOBALS['encode_failure'] = null; return false; }
    return json_encode( $value, $flags );
}
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function get_post_stati() { return array( 'publish', 'draft', 'trash', 'private', 'pending', 'future' ); }
function get_object_taxonomies( $post_type ) { return $post_type === 'wpcode' ? array( 'wpcode_type', 'wpcode_location' ) : array(); }
function wp_cache_delete( $key, $group ) { $GLOBALS['events'][] = array( 'cache_delete', $key, $group ); return true; }
function wp_cache_set_posts_last_changed() { $GLOBALS['events'][] = array( 'posts_last_changed' ); }
function clean_post_cache( $id ) { throw new Harness_Error( 'Unexpected general post cache/save hooks' ); }

function event( $name, $id = 0, $after = false ) {
    $key = $name . ( $after ? ':after' : ':before' );
    $GLOBALS['event_counts'][$key] = ( $GLOBALS['event_counts'][$key] ?? 0 ) + 1;
    $GLOBALS['events'][] = array( $key, $id );
    foreach ( $GLOBALS['failures'] as $i => $failure ) {
        if ( $failure['key'] === $key && $failure['at'] === $GLOBALS['event_counts'][$key] ) {
            unset( $GLOBALS['failures'][$i] );
            if ( isset( $failure['change'] ) ) { $failure['change']( $id ); }
            if ( ( $failure['throw'] ?? true ) !== false ) { throw new RuntimeException( 'Synthetic ' . $key . ' failure' ); }
        }
    }
}

function result_override( $operation, $side ) {
    foreach ( $GLOBALS['result_overrides'] ?? array() as $key => $override ) {
        if ( $override['operation'] === $operation && $override['side'] === $side ) { unset( $GLOBALS['result_overrides'][$key] ); return array( true, $override['value'] ); }
    }
    return array( false, null );
}

class Release_Test_DB {
    public $posts = 'wp_posts'; public $postmeta = 'wp_postmeta'; public $options = 'wp_options';
    public $term_relationships = 'wp_term_relationships'; public $term_taxonomy = 'wp_term_taxonomy';
    public $terms = 'wp_terms'; public $termmeta = 'wp_termmeta'; public $last_error = ''; public $insert_id = 0;
    public $prepared = array(); public $reads = 0; public $writes = 0; public $pdo;
    function __construct() {
        $this->pdo = new PDO( 'sqlite::memory:', null, null, array( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_STRINGIFY_FETCHES => true ) );
    }
    function prepare( $format, ...$args ) {
        if ( count( $args ) === 1 && is_array( $args[0] ) ) { $args = $args[0]; }
        $count = 0;
        $sql = preg_replace_callback( '/%([sdf%])/', static function ( $match ) use ( &$count ) {
            if ( $match[1] === '%' ) { return '%'; } $count++; return '?';
        }, $format );
        if ( $count !== count( $args ) ) { throw new Harness_Error( 'Prepared query parameter mismatch' ); }
        $key = 'prepared:' . count( $this->prepared ); $this->prepared[$key] = array( $sql, $args ); return $key;
    }
    function statement( $sql ) {
        list( $sql, $parameters ) = $this->prepared[$sql] ?? array( $sql, array() );
        $sql = preg_replace( '/\bBINARY\s+/i', '', $sql );
        $sql = str_replace( ' AS BINARY)', ' AS BLOB)', $sql );
        try { $statement = $this->pdo->prepare( $sql ); $statement->execute( $parameters ); return $statement; }
        catch ( PDOException $error ) { throw new Harness_Error( 'SQLite model query failed: ' . $error->getMessage() ); }
    }
    function get_results( $sql, $mode = null ) {
        $this->reads++; if ( isset( $GLOBALS['read_hook'] ) ) { $GLOBALS['read_hook']( $sql ); }
        $rows = $this->statement( $sql )->fetchAll( PDO::FETCH_ASSOC );
        return $mode === ARRAY_A ? $rows : array_map( static fn( $row ) => (object) $row, $rows );
    }
    function get_row( $sql, $mode = null ) { return $this->get_results( $sql, $mode )[0] ?? null; }
    function get_var( $sql ) { $this->reads++; $value = $this->statement( $sql )->fetchColumn(); return $value === false ? null : $value; }
    function get_col( $sql ) { $this->reads++; return $this->statement( $sql )->fetchAll( PDO::FETCH_COLUMN ); }
    function query( $sql ) {
        list( $format ) = $this->prepared[$sql] ?? array( $sql );
        if ( ! preg_match( '/^UPDATE wp_(posts|options) SET /', $format ) ) { throw new Harness_Error( 'Unexpected direct SQL write: ' . $format ); }
        if ( ! isset( $this->prepared[$sql] ) ) { throw new Harness_Error( 'Direct write must be prepared' ); }
        $name = str_starts_with( $format, 'UPDATE wp_posts' ) ? 'css-cas' : 'astra-cas';
        event( $name ); list( $forced, $value ) = result_override( $name, 'before' ); if ( $forced ) { return $value; }
        $this->writes++; $count = $this->statement( $sql )->rowCount(); event( $name, 0, true );
        list( $forced, $value ) = result_override( $name, 'after' ); return $forced ? $value : $count;
    }
}
$wpdb = new Release_Test_DB();
function fixture_sql( $sql, $parameters = array() ) { $q = $GLOBALS['wpdb']->pdo->prepare( $sql ); $q->execute( $parameters ); return $q; }
function fixture_insert( $table, $row ) {
    fixture_sql( 'INSERT INTO ' . $table . ' (' . implode( ',', array_keys( $row ) ) . ') VALUES (' . implode( ',', array_fill( 0, count( $row ), '?' ) ) . ')', array_values( $row ) );
    return (int) $GLOBALS['wpdb']->pdo->lastInsertId();
}
function fixture_update( $table, $id_column, $id, $changes ) {
    fixture_sql( 'UPDATE ' . $table . ' SET ' . implode( ',', array_map( static fn( $key ) => "$key=?", array_keys( $changes ) ) ) . " WHERE $id_column=?", array_merge( array_values( $changes ), array( $id ) ) );
}
function fixture_post( $overrides = array() ) {
    $row = array( 'ID' => null, 'post_author' => '1', 'post_date' => '2026-11-01 00:00:00', 'post_date_gmt' => '2026-11-01 00:00:00',
        'post_content' => '', 'post_title' => '', 'post_excerpt' => '', 'post_status' => 'publish', 'comment_status' => 'closed', 'ping_status' => 'closed',
        'post_password' => '', 'post_name' => '', 'to_ping' => '', 'pinged' => '', 'post_modified' => '2026-11-01 00:00:00', 'post_modified_gmt' => '2026-11-01 00:00:00',
        'post_content_filtered' => '', 'post_parent' => '0', 'guid' => 'https://thaisolitude.com/?fixture=1', 'menu_order' => '0', 'post_type' => 'page', 'post_mime_type' => '', 'comment_count' => '0' );
    return array_replace( $row, $overrides );
}
function wpcode() { static $instance; return $instance ??= (object) array( 'cache' => new class {
    function cache_all_loaded_snippets() {
        event( 'cache-refresh' );
        // Model the derived option's observable purpose, not WPCode's private wire format.
        $loaded = fixture_sql( "SELECT ID,post_content,post_modified FROM wp_posts WHERE post_type='wpcode' AND post_status='publish' ORDER BY ID" )->fetchAll( PDO::FETCH_ASSOC );
        $option = fixture_sql( "SELECT * FROM wp_options WHERE option_name='wpcode_snippets'" )->fetch( PDO::FETCH_ASSOC );
        if ( $option ) { fixture_update( 'wp_options', 'option_id', $option['option_id'], array( 'option_value' => serialize( $loaded ) ) ); }
        else { fixture_insert( 'wp_options', array( 'option_name' => 'wpcode_snippets', 'option_value' => serialize( $loaded ), 'autoload' => 'on' ) ); }
        event( 'cache-refresh', 0, true );
    }
} ); }
function recount( $id, $delta ) {
    foreach ( fixture_sql( 'SELECT term_taxonomy_id FROM wp_term_relationships WHERE object_id=?', array( $id ) )->fetchAll( PDO::FETCH_COLUMN ) as $term ) {
        fixture_sql( 'UPDATE wp_term_taxonomy SET count=count+? WHERE term_taxonomy_id=?', array( $delta, $term ) );
    }
}
class WPCode_Snippet {
    function __construct( private $settings ) {}
    function save() {
        event( 'create' ); list( $forced, $value ) = result_override( 'create', 'before' ); if ( $forced ) { return $value; }
        $active = (bool) ( $this->settings['active'] ?? false );
        $row = fixture_post( array( 'post_title' => $this->settings['title'], 'post_content' => wp_unslash( $this->settings['code'] ), 'post_type' => 'wpcode', 'post_status' => $active ? 'publish' : 'draft', 'post_date' => current_time( 'mysql' ), 'post_date_gmt' => '0000-00-00 00:00:00', 'post_modified' => current_time( 'mysql' ), 'post_modified_gmt' => current_time( 'mysql', true ), 'post_name' => '' ) );
        unset( $row['ID'] ); $id = fixture_insert( 'wp_posts', $row );
        fixture_update( 'wp_posts', 'ID', $id, array( 'guid' => home_url() . '/?post_type=wpcode&p=' . $id ) );
        foreach ( array( '_wpcode_auto_insert' => '1', '_wpcode_priority' => (string) ( $this->settings['priority'] ?? 10 ), '_wpcode_note' => $this->settings['note'] ) as $key => $value ) {
            fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => $key, 'meta_value' => $value ) );
        }
        foreach ( array( 9, 10 ) as $term ) { fixture_insert( 'wp_term_relationships', array( 'object_id' => $id, 'term_taxonomy_id' => $term, 'term_order' => '0' ) ); }
        if ( $active ) { recount( $id, 1 ); }
        $GLOBALS['created_observations'][] = array( 'id' => $id, 'active' => $active, 'content' => $row['post_content'], 'meta' => rows( 'postmeta', 'post_id', $id, 'meta_id' ) );
        event( 'create', $id, true ); list( $forced, $value ) = result_override( 'create', 'after' ); return $forced ? $value : $id;
    }
}
function add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
    // Copy rehearsal 2 observed three initial WPCode keys; this models the
    // separate WordPress normalization call, including [] -> a:0:{}.
    $id = abs( (int) $post_id ); event( 'normalize-meta', $id );
    list( $forced, $value ) = result_override( 'normalize-meta', 'before' ); if ( $forced ) { return $value; }
    if ( ! $id || ! $meta_key ) { return false; }
    $key = wp_unslash( $meta_key ); $value = wp_unslash( $meta_value );
    if ( $unique && fixture_sql( 'SELECT COUNT(*) FROM wp_postmeta WHERE post_id=? AND meta_key=?', array( $id, $key ) )->fetchColumn() !== '0' ) { return false; }
    $before = rows( 'postmeta', 'post_id', $id, 'meta_id' );
    $stored = is_array( $value ) || is_object( $value ) ? serialize( $value ) : (string) $value;
    $meta_id = fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => $key, 'meta_value' => $stored ) );
    $GLOBALS['normalization_observations'][] = array( 'id' => $id, 'key' => $key, 'value' => $meta_value, 'unique' => $unique, 'meta_id' => $meta_id, 'before' => $before, 'after' => rows( 'postmeta', 'post_id', $id, 'meta_id' ) );
    event( 'normalize-meta', $id, true ); list( $forced, $value ) = result_override( 'normalize-meta', 'after' ); return $forced ? $value : $meta_id;
}
function sanitize_title( $value ) { return trim( strtolower( preg_replace( '/[^a-z0-9]+/i', '-', $value ) ), '-' ); }
function wp_unique_post_slug( $slug, $id, $status, $type, $parent ) {
    $candidate = $slug; $suffix = 2;
    while ( fixture_sql( 'SELECT COUNT(*) FROM wp_posts WHERE post_name=? AND ID<>?', array( $candidate, $id ) )->fetchColumn() ) { $candidate = $slug . '-' . $suffix++; }
    return $candidate;
}
function wp_update_post( $values, $wp_error = false ) {
    $values = wp_unslash( $values ); $id = $values['ID']; $edit_date = $values['edit_date'] ?? false; unset( $values['ID'], $values['edit_date'] );
    $name = isset( $values['post_status'] ) ? 'activate' : 'correct-code'; event( $name, $id );
    list( $forced, $value ) = result_override( $name, 'before' ); if ( $forced ) { return $value; }
    $old = fixture_sql( 'SELECT * FROM wp_posts WHERE ID=?', array( $id ) )->fetch( PDO::FETCH_ASSOC );
    if ( ! $old ) { return $wp_error ? new WP_Error( 'missing', 'No such post' ) : 0; }
    $GLOBALS['save_observations'][] = array( 'id' => $id, 'edit_date' => $edit_date );
    $GLOBALS['clock'] = ( new DateTimeImmutable( $GLOBALS['clock'] ) )->modify( '+2 seconds' )->format( 'Y-m-d H:i:s' );
    if ( $old['post_status'] === 'draft' && ! $edit_date ) { $values['post_date'] = current_time( 'mysql' ); }
    if ( ( $values['post_status'] ?? null ) === 'publish' && $old['post_date_gmt'] === '0000-00-00 00:00:00' ) { $values['post_date_gmt'] = get_gmt_from_date( $values['post_date'] ?? $old['post_date'] ); }
    if ( ( $values['post_status'] ?? null ) === 'publish' && $old['post_name'] === '' ) { $values['post_name'] = wp_unique_post_slug( sanitize_title( $old['post_title'] ), $id, 'publish', $old['post_type'], $old['post_parent'] ); }
    $values['post_modified'] = current_time( 'mysql' ); $values['post_modified_gmt'] = current_time( 'mysql', true );
    fixture_update( 'wp_posts', 'ID', $id, $values );
    if ( isset( $values['post_status'] ) && $old['post_status'] !== $values['post_status'] ) { recount( $id, $values['post_status'] === 'publish' ? 1 : -1 ); }
    if ( $name === 'activate' ) { $GLOBALS['activation_observations'][] = array( 'id' => $id, 'content' => $old['post_content'], 'meta' => rows( 'postmeta', 'post_id', $id, 'meta_id' ) ); }
    event( $name, $id, true ); list( $forced, $value ) = result_override( $name, 'after' ); return $forced ? $value : $id;
}
function wp_delete_post( $id, $force = false ) {
    event( 'delete', $id ); list( $forced, $value ) = result_override( 'delete', 'before' ); if ( $forced ) { return $value; }
    $row = fixture_sql( 'SELECT * FROM wp_posts WHERE ID=?', array( $id ) )->fetch( PDO::FETCH_ASSOC );
    if ( ! $row ) { return null; }
    if ( ! $force ) { fixture_update( 'wp_posts', 'ID', $id, array( 'post_status' => 'trash' ) ); return (object) $row; }
    if ( $row['post_status'] === 'publish' ) { recount( $id, -1 ); }
    fixture_sql( 'DELETE FROM wp_posts WHERE ID=?', array( $id ) ); fixture_sql( 'DELETE FROM wp_postmeta WHERE post_id=?', array( $id ) ); fixture_sql( 'DELETE FROM wp_term_relationships WHERE object_id=?', array( $id ) );
    event( 'delete', $id, true ); list( $forced, $value ) = result_override( 'delete', 'after' ); return $forced ? $value : (object) $row;
}
function check( $condition, $label ) { if ( ! $condition ) { throw new RuntimeException( 'FAIL: ' . $label ); } $GLOBALS['pass']++; }

function stable( $value ) {
    if ( is_array( $value ) ) {
        if ( ! array_is_list( $value ) ) { ksort( $value, SORT_STRING ); }
        foreach ( $value as &$item ) { $item = stable( $item ); } unset( $item );
    }
    return $value;
}
function digest( $value ) { return hash( 'sha256', json_encode( stable( $value ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS ) ); }
function all_state() {
    $out = array();
    foreach ( array( 'posts' => 'ID', 'postmeta' => 'meta_id', 'options' => 'option_id', 'term_relationships' => 'object_id,term_taxonomy_id', 'term_taxonomy' => 'term_taxonomy_id', 'terms' => 'term_id', 'termmeta' => 'meta_id' ) as $table => $order ) {
        $out[$table] = fixture_sql( "SELECT * FROM wp_$table ORDER BY $order" )->fetchAll( PDO::FETCH_ASSOC );
    }
    return $out;
}
function restore_state( $state ) {
    foreach ( $state as $table => $rows ) {
        fixture_sql( "DELETE FROM wp_$table" );
        foreach ( $rows as $row ) { fixture_insert( "wp_$table", $row ); }
    }
    $GLOBALS['events'] = $GLOBALS['event_counts'] = $GLOBALS['failures'] = $GLOBALS['created_observations'] = $GLOBALS['activation_observations'] = $GLOBALS['save_observations'] = $GLOBALS['normalization_observations'] = array(); $GLOBALS['clock'] = '2026-11-10 03:00:00'; $GLOBALS['encode_failure'] = null; $GLOBALS['result_overrides'] = array(); $GLOBALS['read_hook'] = null; $GLOBALS['fail_state_diagnostic_log'] = false;
}
function create_schema() {
    $columns = array_keys( fixture_post() );
    $fields = array_map( static fn( $key ) => $key === 'ID' ? 'ID INTEGER PRIMARY KEY AUTOINCREMENT' : "$key TEXT NOT NULL", $columns );
    fixture_sql( 'CREATE TABLE wp_posts (' . implode( ',', $fields ) . ')' );
    fixture_sql( 'CREATE TABLE wp_postmeta (meta_id INTEGER PRIMARY KEY AUTOINCREMENT, post_id INTEGER NOT NULL, meta_key TEXT, meta_value TEXT)' );
    fixture_sql( 'CREATE TABLE wp_options (option_id INTEGER PRIMARY KEY AUTOINCREMENT, option_name TEXT NOT NULL UNIQUE, option_value TEXT NOT NULL, autoload TEXT NOT NULL)' );
    fixture_sql( 'CREATE TABLE wp_term_relationships (object_id INTEGER NOT NULL, term_taxonomy_id INTEGER NOT NULL, term_order INTEGER NOT NULL, PRIMARY KEY(object_id,term_taxonomy_id))' );
    fixture_sql( 'CREATE TABLE wp_term_taxonomy (term_taxonomy_id INTEGER PRIMARY KEY AUTOINCREMENT, term_id INTEGER NOT NULL, taxonomy TEXT NOT NULL, description TEXT NOT NULL, parent INTEGER NOT NULL, count INTEGER NOT NULL)' );
    fixture_sql( 'CREATE TABLE wp_terms (term_id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, slug TEXT NOT NULL, term_group INTEGER NOT NULL)' );
    fixture_sql( 'CREATE TABLE wp_termmeta (meta_id INTEGER PRIMARY KEY AUTOINCREMENT, term_id INTEGER NOT NULL, meta_key TEXT, meta_value TEXT)' );
}
function fill_fixture( $manifest, $source ) {
    foreach ( $manifest['pages'] as $page ) {
        fixture_insert( 'wp_posts', fixture_post( array( 'ID' => $page['id'], 'post_name' => $manifest['page_slugs'][(string) $page['id']], 'post_title' => $page['title'], 'post_parent' => in_array( $page['id'], array( 1255, 1257 ), true ) ? '1254' : '0', 'post_content' => '<h2>Original ' . $manifest['page_slugs'][(string) $page['id']] . "</h2>\n<p>Preserve quotes: \" and ' and \\ and 雪.</p>" ) ) );
    }
    foreach ( array( 1232 => 'A11yPageJumpLinks', 1338 => 'TSSectionGuide v1' ) as $id => $title ) {
        fixture_insert( 'wp_posts', fixture_post( array( 'ID' => $id, 'post_type' => 'wpcode', 'post_title' => $title, 'post_name' => strtolower( str_replace( ' ', '-', $title ) ), 'post_content' => "// unchanged dependency $id\n" ) ) );
        foreach ( array( '_wpcode_auto_insert' => '1', '_wpcode_priority' => '10', '_wpcode_note' => 'Original dependency; do not edit.', '_wpcode_shortcode_attributes' => 'a:0:{}' ) as $key => $value ) {
            fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => $key, 'meta_value' => $value ) );
        }
        foreach ( array( 9, 10 ) as $term ) { fixture_insert( 'wp_term_relationships', array( 'object_id' => $id, 'term_taxonomy_id' => $term, 'term_order' => '0' ) ); }
    }
    for ( $i = 0; $i < 15; $i++ ) {
        $id = 1700 + $i;
        fixture_insert( 'wp_posts', fixture_post( array( 'ID' => $id, 'post_type' => 'wpcode', 'post_title' => "Unrelated snippet $i", 'post_name' => "unrelated-$i", 'post_content' => "// Unrelated stored value $i\n" ) ) );
        foreach ( array( 9, 10 ) as $term ) { fixture_insert( 'wp_term_relationships', array( 'object_id' => $id, 'term_taxonomy_id' => $term, 'term_order' => '0' ) ); }
    }
    fixture_insert( 'wp_posts', fixture_post( array( 'ID' => 2000, 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'Unrelated draft', 'post_content' => 'Preserve draft content.' ) ) );
    $progress = file_get_contents( "$source/payload/reading-progress.css" );
    fixture_insert( 'wp_posts', fixture_post( array( 'ID' => 29, 'post_type' => 'custom_css', 'post_name' => 'astra', 'post_title' => 'astra', 'post_content' => "/* original before */\nbody{color:#123;}" . $progress . "\n/* original after */\n" ) ) );
    foreach ( array( 1091, 1107, 1160, 1293 ) as $id ) {
        fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => 'unknown_plugin_setting', 'meta_value' => serialize( array( 'keep' => "raw quoted \'\\雪", 'url' => 'http://localhost:8090/' ) ) ) );
        fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => 'unknown_plugin_setting', 'meta_value' => null ) );
        fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => '_uag_page_assets', 'meta_value' => 'a:0:{}' ) );
    }
    foreach ( $manifest['baseline']['terms'] as $row ) { fixture_insert( 'wp_terms', $row ); }
    foreach ( $manifest['baseline']['taxonomies'] as $row ) { fixture_insert( 'wp_term_taxonomy', $row ); }
    $options = array( 'home' => home_url(), 'siteurl' => home_url(), 'template' => 'astra', 'stylesheet' => 'astra', 'show_on_front' => 'page', 'page_on_front' => '296', 'page_for_posts' => '0', 'permalink_structure' => '/%postname%/',
        'astra-settings' => serialize( array( 'scroll-to-top-enable' => true, 'unknown' => array( "quote's" => "Preserve \\ and 雪", 'url' => home_url() . '/path/' ), 'boolean' => false, 'integer' => 1, 'numeric-string' => '1' ) ),
        'unrelated_option' => 'SYNTHETIC_PRIVATE_UNRELATED_OPTION' );
    foreach ( $options as $name => $value ) { fixture_insert( 'wp_options', array( 'option_name' => $name, 'option_value' => $value, 'autoload' => 'on' ) ); }
}

function copy_package( $source, $target ) {
    mkdir( $target, 0700, true );
    foreach ( new DirectoryIterator( $source ) as $item ) {
        if ( $item->isDot() || $item->getFilename() === 'test-installer.php' ) { continue; }
        $to = $target . '/' . $item->getFilename();
        if ( $item->isDir() ) { copy_package( $item->getPathname(), $to ); }
        else { if ( ! copy( $item->getPathname(), $to ) ) { throw new RuntimeException( 'Package copy failed' ); } }
    }
}
function row( $table, $key, $value ) { return fixture_sql( "SELECT * FROM wp_$table WHERE $key=?", array( $value ) )->fetch( PDO::FETCH_ASSOC ) ?: null; }
function rows( $table, $key, $value, $order ) { return fixture_sql( "SELECT * FROM wp_$table WHERE $key=? ORDER BY $order", array( $value ) )->fetchAll( PDO::FETCH_ASSOC ); }
function fixture_profile( $manifest, $source ) {
    $profile = $manifest['baseline'];
    foreach ( $profile['post_sha256'] as $id => $_ ) { $profile['post_sha256'][$id] = digest( row( 'posts', 'ID', $id ) ); }
    foreach ( $profile['meta_sha256'] as $id => $_ ) { $profile['meta_sha256'][$id] = digest( rows( 'postmeta', 'post_id', $id, 'meta_id' ) ); }
    foreach ( $profile['relationship_sha256'] as $id => $_ ) { $profile['relationship_sha256'][$id] = digest( rows( 'term_relationships', 'object_id', $id, 'term_taxonomy_id' ) ); }
    foreach ( $profile['option_sha256'] as $name => $_ ) { $profile['option_sha256'][$name] = digest( row( 'options', 'option_name', $name ) ); }
    $css = row( 'posts', 'ID', 29 )['post_content'];
    $profile['css']['old_content_sha256'] = hash( 'sha256', $css );
    $profile['css']['new_content_sha256'] = hash( 'sha256', str_replace( file_get_contents( "$source/payload/reading-progress.css" ), '', $css ) );
    $astra = row( 'options', 'option_name', 'astra-settings' );
    $settings = unserialize( $astra['option_value'], array( 'allowed_classes' => false ) );
    $settings['scroll-to-top-enable'] = false; $astra['option_value'] = serialize( $settings );
    $profile['astra_new_row_sha256'] = digest( $astra );
    return $profile;
}
function invoke( $arguments, $directory = null ) {
    $args = $arguments; $GLOBALS['logs'] = array(); $GLOBALS['message'] = ''; $GLOBALS['harness_error'] = null;
    $directory ??= $GLOBALS['package'];
    try { require $directory . '/install-peek-release.php'; }
    catch ( Throwable $error ) {
        if ( $GLOBALS['harness_error'] !== null ) { throw new Harness_Error( $GLOBALS['harness_error'] ); }
        if ( ! $error instanceof Refusal_Error ) { throw $error; }
        throw $error;
    }
    if ( $GLOBALS['harness_error'] !== null ) { throw new Harness_Error( $GLOBALS['harness_error'] ); }
    // Expose the exact included closure for pure diagnostic boundary tests only.
    if ( isset( $release_equal ) ) { $GLOBALS['diagnostic_equal'] = $release_equal; }
    return $GLOBALS['message'];
}
function refusal( $arguments, $label, $directory = null ) {
    $before = all_state(); $before_events = count( $GLOBALS['events'] ); $before_writes = $GLOBALS['wpdb']->writes;
    $error = null;
    try { invoke( $arguments, $directory ); } catch ( Refusal_Error $caught ) { $error = $caught; }
    check( $error !== null, "$label: explicit installer refusal" );
    check( all_state() === $before && count( $GLOBALS['events'] ) === $before_events && $GLOBALS['wpdb']->writes === $before_writes, "$label: no database/cache/API writes" );
    check( ! str_contains( $error->getMessage() . implode( '\n', $GLOBALS['logs'] ), 'SYNTHETIC_PRIVATE' ), "$label: private values stay out of diagnostics" );
    return $error;
}
function marker( $prefix ) {
    $found = array_values( array_filter( $GLOBALS['logs'], static fn( $line ) => str_starts_with( $line, $prefix . ' ' ) ) );
    check( count( $found ) === 1, "$prefix emitted exactly once" );
    $data = json_decode( substr( $found[0], strlen( $prefix ) + 1 ), true, 512, JSON_THROW_ON_ERROR );
    check( is_array( $data ), "$prefix is JSON object" ); return $data;
}
function snapshot_write( $data ) {
    $path = $GLOBALS['testdir'] . '/backups/peek-release-before-20261110-030000-' . bin2hex( random_bytes( 4 ) ) . '.json';
    file_put_contents( $path, json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); chmod( $path, 0600 ); return $path;
}
function remove_tree( $path ) {
    if ( is_dir( $path ) && ! is_link( $path ) ) { foreach ( new DirectoryIterator( $path ) as $entry ) { if ( ! $entry->isDot() ) { remove_tree( $entry->getPathname() ); } } rmdir( $path ); }
    elseif ( file_exists( $path ) || is_link( $path ) ) { unlink( $path ); }
}
$source = $argv[1] ?? __DIR__;
$manifest_bytes = file_get_contents( "$source/manifest.json" );
$manifest = json_decode( $manifest_bytes, true, 512, JSON_THROW_ON_ERROR );
$payload_specs = array_merge( array( 'core' => $manifest['payloads']['core'], 'progress' => $manifest['payloads']['reading_progress'] ), $manifest['payloads']['data'] );
foreach ( $payload_specs as $role => $spec ) {
    check( hash_file( 'sha256', "$source/{$spec['file']}" ) === $spec['file_sha256'], "production $role payload matches frozen manifest" );
}
create_schema(); fill_fixture( $manifest, $source ); wpcode()->cache->cache_all_loaded_snippets(); $original = all_state(); restore_state( $original );
$package = "$testdir/package"; copy_package( $source, $package );
$fixture_manifest = $manifest; $fixture_manifest['baseline'] = fixture_profile( $manifest, $source );
file_put_contents( "$package/manifest.json", json_encode( $fixture_manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" );
$comparison = $fixture_manifest; $comparison['baseline'] = $manifest['baseline'];
check( $comparison === $manifest, 'fixture changes only baseline profile; production routes, payloads, host rules and source identity remain intact' );
check( hash_file( 'sha256', "$package/install-peek-release.php" ) === hash_file( 'sha256', "$source/install-peek-release.php" ), 'fixture runs identical production installer bytes' );
foreach ( $payload_specs as $role => $spec ) { check( hash_file( 'sha256', "$package/{$spec['file']}" ) === $spec['file_sha256'], "fixture retains production $role payload bytes" ); }

foreach ( array( 'https://thaisolitude.com', 'https://staging39.thaisolitude.com', 'http://127.0.0.1:8090', 'https://localhost:8090', 'http://localhost:8091', 'http://localhost:8090/other', 'http://localhost:8090.evil.test' ) as $bad ) {
    $home = $bad; refusal( array( 'apply' ), 'unsafe origin ' . $bad );
}
$home = 'http://localhost:8090'; $theme = 'other'; refusal( array( 'apply' ), 'non-Astra theme' ); $theme = 'astra';
$admin = false; refusal( array( 'apply' ), 'insufficient capability' ); $admin = true;
foreach ( array( array( 'unknown' ), array( 'apply', 'extra', 'extra' ), array( 'rollback' ), array( 'verify' ), array( 'verify', 'x', 'wrong' ), array( 'preflight', 'extra' ) ) as $arguments ) { refusal( $arguments, 'invalid mode or argument count' ); }
$before_reads = $wpdb->reads; invoke( array( 'preflight' ) ); $preflight = marker( 'RELEASE-PREFLIGHT' );
check( all_state() === $original && $wpdb->writes === 0 && $events === array() && $wpdb->reads > $before_reads, 'preflight uses real reads without writes' );
check( $preflight['state'] === 'ready' && $preflight['home'] === $home && $preflight['manifest_sha256'] === hash_file( 'sha256', "$package/manifest.json" ), 'preflight binds exact origin, readiness and fixture manifest' );

$mutations = array(
    'changed dependency code' => static fn() => fixture_update( 'wp_posts', 'ID', 1232, array( 'post_content' => 'edited dependency' ) ),
    'changed dependency status' => static fn() => fixture_update( 'wp_posts', 'ID', 1338, array( 'post_status' => 'draft' ) ),
    'changed dependency metadata' => static fn() => fixture_insert( 'wp_postmeta', array( 'post_id' => 1232, 'meta_key' => '_new', 'meta_value' => 'unreviewed' ) ),
    'changed Thai content' => static fn() => fixture_update( 'wp_posts', 'ID', 1160, array( 'post_content' => 'edited source page' ) ),
    'renamed route' => static fn() => fixture_update( 'wp_posts', 'ID', 298, array( 'post_name' => 'renamed-about' ) ),
    'moved nested route' => static fn() => fixture_update( 'wp_posts', 'ID', 1255, array( 'post_parent' => '0' ) ),
    'missing published route' => static fn() => fixture_update( 'wp_posts', 'ID', 301, array( 'post_status' => 'draft' ) ),
    'extra published route' => static fn() => fixture_insert( 'wp_posts', fixture_post( array( 'ID' => 3001, 'post_name' => 'extra', 'post_title' => 'Extra page' ) ) ),
    'changed CSS byte' => static fn() => fixture_update( 'wp_posts', 'ID', 29, array( 'post_content' => row( 'posts', 'ID', 29 )['post_content'] . ' ' ) ),
    'changed Astra autoload' => static fn() => fixture_update( 'wp_options', 'option_name', 'astra-settings', array( 'autoload' => 'off' ) ),
    'changed Astra raw data' => static fn() => fixture_update( 'wp_options', 'option_name', 'astra-settings', array( 'option_value' => serialize( array( 'scroll-to-top-enable' => 1 ) ) ) ),
    'changed shared taxonomy count' => static fn() => fixture_update( 'wp_term_taxonomy', 'term_taxonomy_id', 9, array( 'count' => '18' ) ),
    'new taxonomy metadata' => static fn() => fixture_insert( 'wp_termmeta', array( 'term_id' => 9, 'meta_key' => 'external', 'meta_value' => 'keep' ) ),
);
foreach ( $mutations as $label => $mutate ) { restore_state( $original ); $mutate(); refusal( array( 'apply' ), $label ); }
restore_state( $original );
foreach ( array( '', '.', ABSPATH ) as $directory ) { putenv( 'WWM_BACKUP_DIR=' . $directory ); refusal( array( 'apply' ), 'unsafe snapshot directory' ); }
putenv( 'WWM_BACKUP_DIR=' . "$testdir/backups" );

invoke( array( 'apply' ) ); $applied_marker = marker( 'RELEASE-SNAPSHOT' );
$path = $applied_marker['snapshot']; $snapshot = json_decode( file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
$applied = all_state(); $successful_event_counts = $event_counts; $ids = $applied_marker['ids']; $snapshot_bytes = file_get_contents( $path );
check( $applied_marker['state_sha256'] === digest( $snapshot['new'] ) && stable( $applied_marker['ids'] ) === stable( $snapshot['ids'] ), 'success marker binds exact state and ownership IDs' );
check( ! isset( $snapshot['old']['records'][1160]['meta'] ) && ! str_contains( $snapshot_bytes, 'raw quoted' ) && ! str_contains( $snapshot_bytes, 'SYNTHETIC_PRIVATE_UNRELATED_OPTION' ), 'unknown source metadata is witnessed by hash and unrelated option values are absent from snapshot' );
check( count( $ids ) === 5 && count( array_unique( $ids ) ) === 5 && isset( $ids['core'] ), 'release owns exactly five distinct records' );
check( ( fileperms( $path ) & 0777 ) === 0600 && dirname( $path ) === "$testdir/backups", 'snapshot stored privately outside webroot' );
check( $applied_marker['snapshot_sha256'] === hash_file( 'sha256', $path ) && $applied_marker['manifest_sha256'] === hash_file( 'sha256', "$package/manifest.json" ), 'success marker binds actual snapshot and manifest bytes' );
check( count( $created_observations ) === 5 && array_filter( $created_observations, static fn( $record ) => $record['active'] ) === array(), 'all five snippets created inactive' );
check( count( $normalization_observations ) === 5, 'all five three-key drafts receive one explicit metadata normalization' );
foreach ( $normalization_observations as $i => $normalization ) {
    $created = $created_observations[$i];
    check( array_column( $created['meta'], 'meta_key' ) === array( '_wpcode_auto_insert', '_wpcode_priority', '_wpcode_note' ), 'initial WPCode metadata matches the actual three-key copy evidence' );
    check( $normalization['id'] === $created['id'] && $normalization['key'] === '_wpcode_shortcode_attributes' && $normalization['value'] === array() && $normalization['unique'] === true, 'normalization adds unique empty-array attributes to the currently created draft only' );
    $new_rows = array_values( array_filter( $normalization['after'], static fn( $row ) => (int) $row['meta_id'] === $normalization['meta_id'] ) );
    $old_rows = array_values( array_filter( $normalization['after'], static fn( $row ) => (int) $row['meta_id'] !== $normalization['meta_id'] ) );
    check( $normalization['before'] === $created['meta'] && $old_rows === $created['meta'] && count( $new_rows ) === 1 && $new_rows[0]['post_id'] === (string) $created['id'] && $new_rows[0]['meta_value'] === 'a:0:{}', 'normalization preserves every original metadata ID/raw byte and adds exactly one serialized empty array' );
}
check( count( $save_observations ) >= 5 && array_filter( $save_observations, static fn( $record ) => $record['edit_date'] !== true ) === array(), 'all snippet corrections/activations explicitly preserve creation dates across advancing local/GMT clocks' );
check( count( $activation_observations ) === 5 && end( $activation_observations )['id'] === $ids['core'], 'data activated before shared core' );
foreach ( $ids as $role => $id ) {
    $record = row( 'posts', 'ID', $id ); $spec = $role === 'core' ? $manifest['payloads']['core'] : $manifest['payloads']['data'][$role];
    check( $record['post_type'] === 'wpcode' && $record['post_status'] === 'publish' && hash( 'sha256', $record['post_content'] ) === $spec['code_sha256'], "$role active with exact approved stored bytes" );
    $observed = array_values( array_filter( $activation_observations, static fn( $record ) => $record['id'] === $id ) );
    check( count( $observed ) === 1 && count( $observed[0]['meta'] ) === 4 && hash( 'sha256', $observed[0]['content'] ) === $spec['code_sha256'], "$role corrected before activation" );
}
$expected = $original;
foreach ( $expected['posts'] as &$post ) { if ( $post['ID'] === '29' ) { $post['post_content'] = str_replace( file_get_contents( "$source/payload/reading-progress.css" ), '', $post['post_content'] ); } } unset( $post );
foreach ( $expected['options'] as &$option ) { if ( $option['option_name'] === 'astra-settings' ) { $data = unserialize( $option['option_value'], array( 'allowed_classes' => false ) ); $data['scroll-to-top-enable'] = false; $option['option_value'] = serialize( $data ); } } unset( $option );
foreach ( $expected['term_taxonomy'] as &$term ) { if ( in_array( $term['term_taxonomy_id'], array( '9', '10' ), true ) ) { $term['count'] = (string) ( (int) $term['count'] + 5 ); } } unset( $term );
$cache = unserialize( row( 'options', 'option_name', 'wpcode_snippets' )['option_value'], array( 'allowed_classes' => false ) );
$expected_cache_ids = array_merge( array( 1232, 1338 ), range( 1700, 1714 ), array_values( $ids ) ); sort( $expected_cache_ids, SORT_NUMERIC );
check( array_map( 'intval', array_column( $cache, 'ID' ) ) === $expected_cache_ids, 'derived cache includes exactly original active snippets and five new owned records' );
foreach ( $expected['options'] as &$option ) { if ( $option['option_name'] === 'wpcode_snippets' ) { $option['option_value'] = row( 'options', 'option_name', 'wpcode_snippets' )['option_value']; } } unset( $option );
$unowned = $applied;
foreach ( array( 'posts' => 'ID', 'postmeta' => 'post_id', 'term_relationships' => 'object_id' ) as $table => $key ) { $unowned[$table] = array_values( array_filter( $unowned[$table], static fn( $record ) => ! in_array( (int) $record[$key], $ids, true ) ) ); }
check( $unowned === $expected, 'only five owned records, exact progress CSS removal, one Astra bool, two count deltas and derived cache changed; all unrelated bytes preserved' );
$event_count = count( $events ); $writes = $wpdb->writes;
invoke( array( 'verify', $path ) ); marker( 'RELEASE-SNAPSHOT' ); invoke( array( 'apply', $path ) ); marker( 'RELEASE-SNAPSHOT' );
check( all_state() === $applied && count( $events ) === $event_count && $wpdb->writes === $writes && file_get_contents( $path ) === $snapshot_bytes, 'verify and snapshot-bound repeat are strictly read-only' );
refusal( array( 'apply' ), 'already installed release requires explicit snapshot binding' );
refusal( array( 'verify', $path, 'original' ), 'applied state cannot verify as original' );
$drifts = array(
    'owned code' => static fn() => fixture_update( 'wp_posts', 'ID', $GLOBALS['ids']['core'], array( 'post_content' => 'SYNTHETIC_PRIVATE_EDITED_CODE' ) ),
    'owned status' => static fn() => fixture_update( 'wp_posts', 'ID', $GLOBALS['ids']['core'], array( 'post_status' => 'draft' ) ),
    'owned title' => static fn() => fixture_update( 'wp_posts', 'ID', $GLOBALS['ids']['core'], array( 'post_title' => 'Someone else owns this now' ) ),
    'owned date' => static fn() => fixture_update( 'wp_posts', 'ID', $GLOBALS['ids']['core'], array( 'post_modified_gmt' => '2099-01-01 00:00:00' ) ),
    'owned excerpt' => static fn() => fixture_update( 'wp_posts', 'ID', $GLOBALS['ids']['core'], array( 'post_excerpt' => 'External edit' ) ),
    'owned metadata' => static fn() => fixture_insert( 'wp_postmeta', array( 'post_id' => $GLOBALS['ids']['core'], 'meta_key' => 'unknown', 'meta_value' => 'SYNTHETIC_PRIVATE_CHANGED_META' ) ),
    'owned relationships' => static fn() => fixture_sql( 'DELETE FROM wp_term_relationships WHERE object_id=? AND term_taxonomy_id=9', array( $GLOBALS['ids']['core'] ) ),
    'missing owned record' => static fn() => fixture_sql( 'DELETE FROM wp_posts WHERE ID=?', array( $GLOBALS['ids']['core'] ) ),
    'original source unknown metadata' => static fn() => fixture_insert( 'wp_postmeta', array( 'post_id' => 1160, 'meta_key' => 'new external state', 'meta_value' => 'SYNTHETIC_PRIVATE_NEW_VALUE' ) ),
    'unrelated snippet code' => static fn() => fixture_update( 'wp_posts', 'ID', 1701, array( 'post_content' => 'External unrelated edit' ) ),
    'Astra autoload' => static fn() => fixture_update( 'wp_options', 'option_name', 'astra-settings', array( 'autoload' => 'off' ) ),
    'shared count' => static fn() => fixture_update( 'wp_term_taxonomy', 'term_taxonomy_id', 10, array( 'count' => '23' ) ),
    'shared term' => static fn() => fixture_update( 'wp_terms', 'term_id', 9, array( 'slug' => 'changed' ) ),
    'shared term metadata' => static fn() => fixture_insert( 'wp_termmeta', array( 'term_id' => 10, 'meta_key' => 'external', 'meta_value' => 'keep' ) ),
);
foreach ( $drifts as $label => $mutate ) {
    restore_state( $applied ); $mutate();
    refusal( array( 'verify', $path ), "verify refuses $label drift" );
    refusal( array( 'rollback', $path ), "rollback refuses $label drift" );
    refusal( array( 'apply', $path ), "repeat refuses $label drift" );
}
foreach ( array( 'publish', 'draft', 'trash' ) as $status ) {
    restore_state( $applied ); $duplicate = row( 'posts', 'ID', $ids['core'] ); $duplicate['ID'] = 4000; $duplicate['post_status'] = $status; fixture_insert( 'wp_posts', $duplicate );
    refusal( array( 'rollback', $path ), "duplicate owned title/code in $status prevents deletion" );
    restore_state( $original ); fixture_insert( 'wp_posts', $duplicate );
    refusal( array( 'apply' ), "any-status Peek ownership collision ($status) prevents installation" );
}
restore_state( $applied );
foreach ( array( 'home', 'bundle', 'schema', 'manifest_sha256' ) as $field ) {
    $forged = $snapshot; $forged[$field] = $field === 'schema' ? 999 : 'invalid'; $bad_path = snapshot_write( $forged );
    refusal( array( 'rollback', $bad_path ), "rollback refuses corrupted snapshot $field" );
    refusal( array( 'verify', $bad_path ), "verify refuses corrupted snapshot $field" );
}
$truncated = snapshot_write( $snapshot ); file_put_contents( $truncated, '{"schema":1,' ); refusal( array( 'rollback', $truncated ), 'truncated snapshot' );
$outside = "$testdir/" . basename( $path ); copy( $path, $outside ); chmod( $outside, 0600 ); refusal( array( 'rollback', $outside ), 'snapshot outside designated directory' );
$public = snapshot_write( $snapshot ); chmod( $public, 0644 ); refusal( array( 'rollback', $public ), 'world-readable snapshot' );
$link = "$testdir/backups/peek-release-before-20261110-030000-00000000.json"; symlink( $path, $link ); refusal( array( 'rollback', $link ), 'symlink snapshot' );

restore_state( $applied ); invoke( array( 'rollback', $path ) ); $restored_marker = marker( 'RELEASE-SNAPSHOT' );
check( all_state() === $original, 'rollback restores all original raw rows and shared counts exactly' );
$deletions = array_values( array_filter( $events, static fn( $entry ) => $entry[0] === 'delete:before' ) );
check( count( $deletions ) === 5 && $deletions[0][1] === $ids['core'], 'rollback removes core before data and only five owned records' );
check( file_get_contents( $path ) === $snapshot_bytes, 'complete snapshot is immutable through rollback' );
$restored_snapshot_bytes = file_get_contents( $path ); $event_count = count( $events ); $writes = $wpdb->writes;
invoke( array( 'verify', $path, 'original' ) ); marker( 'RELEASE-SNAPSHOT' );
check( all_state() === $original && count( $events ) === $event_count && $wpdb->writes === $writes && file_get_contents( $path ) === $restored_snapshot_bytes, 'original verification is strictly read-only' );
invoke( array( 'rollback', $path ) ); marker( 'RELEASE-SNAPSHOT' );
check( all_state() === $original && $wpdb->writes === $writes && file_get_contents( $path ) === $restored_snapshot_bytes && array_slice( $events, $event_count ) === array( array( 'cache-refresh:before', 0 ), array( 'cache-refresh:after', 0 ) ), 'repeated rollback changes no stored records and only refreshes derived cache' );
refusal( array( 'verify', $path ), 'restored state cannot verify as applied' );
// A deleted ID that was later reused is foreign, even when the original title disappeared.
$foreign = fixture_post( array( 'ID' => $ids['core'], 'post_type' => 'post', 'post_title' => 'Reused foreign ID' ) ); fixture_insert( 'wp_posts', $foreign );
refusal( array( 'rollback', $path ), 'restored snapshot cannot delete a reused foreign ID' ); restore_state( $original );
invoke( array( 'apply' ) ); $second = marker( 'RELEASE-SNAPSHOT' );
check( count( array_intersect( $ids, $second['ids'] ) ) === 0 && $second['snapshot'] !== $path, 'fresh reapply has distinct identities and its own snapshot' );
invoke( array( 'rollback', $second['snapshot'] ) ); check( all_state() === $original, 'fresh reapply round trip preserves exact original state' );

// Corruption cannot become trusted merely by recomputing its local state hash.
restore_state( $applied );
foreach ( array( 'old-meta', 'old-css-date', 'new-meta', 'new-date', 'foreign-id', 'incomplete' ) as $mutation ) {
    $forged = $snapshot;
    if ( $mutation === 'old-meta' ) { $forged['old']['records'][1232]['meta'][0]['meta_value'] = 'forged'; }
    if ( $mutation === 'old-css-date' ) { $forged['old']['records'][29]['post']['post_modified'] = '2099-01-01 00:00:00'; }
    if ( $mutation === 'new-meta' ) { $forged['new']['owned']['core']['meta'][0]['meta_value'] = 'forged'; }
    if ( $mutation === 'new-date' ) { $forged['new']['owned']['core']['post']['post_modified'] = '2099-01-01 00:00:00'; }
    if ( $mutation === 'foreign-id' ) { $forged['ids']['core'] = 1701; $forged['new']['owned']['core']['post']['ID'] = '1701'; }
    if ( $mutation === 'incomplete' ) { $forged['complete'] = false; }
    $forged['old_sha256'] = digest( $forged['old'] ); $forged['new_sha256'] = digest( $forged['new'] );
    $bad_path = snapshot_write( $forged );
    refusal( array( 'rollback', $bad_path ), "snapshot semantic validation refuses $mutation with recomputed hashes" );
}

// Fail before and after every actual API/SQL boundary used by successful apply.
$fault_cases = 0;
foreach ( array( 'create', 'normalize-meta', 'correct-code', 'activate', 'css-cas', 'astra-cas', 'cache-refresh' ) as $operation ) {
    foreach ( array( 'before', 'after' ) as $side ) {
        $key = "$operation:$side";
        for ( $at = 1; $at <= ( $successful_event_counts[$key] ?? 0 ); $at++ ) {
            restore_state( $original ); $fault_state = null;
            $failures = array( array( 'key' => $key, 'at' => $at, 'change' => static function () use ( &$fault_state ) { $fault_state = all_state(); } ) ); $caught = null;
            try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
            check( $caught !== null && $fault_state !== null && ( $event_counts[$key] ?? 0 ) >= $at, "$key occurrence $at fault reached and reported" );
            if ( $operation === 'normalize-meta' && $side === 'after' ) {
                check( all_state() === $fault_state && ( $event_counts['delete:before'] ?? 0 ) === 0 && str_contains( $caught->getMessage(), 'recovery stopped' ), "$key occurrence $at cannot adopt an inserted row without the returned metadata ID" );
                check( preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $fault_path ) === 1, 'unreturned metadata insertion names its actual incomplete journal' );
                refusal( array( 'rollback', $fault_path[1] ), "$key occurrence $at unknown insertion is outside the recorded journal" );
            } else { check( all_state() === $original, "$key occurrence $at restores all exact original rows" ); }
            check( ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "$key occurrence $at emits no success marker" );
            $fault_cases++;
        }
    }
}

// Mid-rollback crashes may resume only the exact known reverse prefix, never a drifted prefix.
foreach ( array( 'delete', 'css-cas', 'astra-cas', 'cache-refresh' ) as $operation ) {
    foreach ( array( 'before', 'after' ) as $side ) {
        $limit = $operation === 'delete' ? 5 : 1;
        for ( $at = 1; $at <= $limit; $at++ ) {
            restore_state( $applied ); $key = "$operation:$side"; $failures = array( array( 'key' => $key, 'at' => $at ) ); $caught = null;
            try { invoke( array( 'rollback', $path ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
            check( $caught !== null && str_contains( $caught->getMessage(), 'Synthetic ' . $key ), "rollback $key occurrence $at fault reached" );
            check( file_get_contents( $path ) === $snapshot_bytes, "rollback $key leaves complete snapshot immutable" );
            $partial = all_state();
            fixture_insert( 'wp_postmeta', array( 'post_id' => 1160, 'meta_key' => 'after-crash-edit', 'meta_value' => 'SYNTHETIC_PRIVATE_CONCURRENT_EDIT' ) );
            refusal( array( 'rollback', $path ), "rollback $key partial prefix with independent edit" );
            restore_state( $partial ); invoke( array( 'rollback', $path ) );
            check( all_state() === $original && file_get_contents( $path ) === $snapshot_bytes, "rollback $key occurrence $at resumes only known prefix to exact original" );
            $fault_cases++;
        }
    }
}

// A concurrent external change is not an authorized recovery state.
foreach ( array( 'created-code', 'source-meta', 'active-meta', 'css-race', 'astra-race' ) as $mutation ) {
    restore_state( $original );
    $failure = array( 'at' => 1, 'throw' => true );
    if ( $mutation === 'created-code' ) { $failure['key'] = 'create:after'; $failure['change'] = static fn( $id ) => fixture_update( 'wp_posts', 'ID', $id, array( 'post_content' => 'SYNTHETIC_PRIVATE_FOREIGN_CODE' ) ); }
    if ( $mutation === 'source-meta' ) { $failure['key'] = 'create:after'; $failure['change'] = static fn() => fixture_insert( 'wp_postmeta', array( 'post_id' => 1160, 'meta_key' => 'during-write', 'meta_value' => 'SYNTHETIC_PRIVATE_EXTERNAL_META' ) ); }
    if ( $mutation === 'active-meta' ) { $failure['key'] = 'activate:after'; $failure['at'] = 2; $failure['change'] = static fn( $id ) => fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => 'external', 'meta_value' => 'SYNTHETIC_PRIVATE_OWNED_META' ) ); }
    if ( $mutation === 'css-race' ) { $failure['key'] = 'css-cas:before'; $failure['throw'] = false; $failure['change'] = static fn() => fixture_update( 'wp_posts', 'ID', 29, array( 'post_content' => row( 'posts', 'ID', 29 )['post_content'] . ' ' ) ); }
    if ( $mutation === 'astra-race' ) { $failure['key'] = 'astra-cas:before'; $failure['throw'] = false; $failure['change'] = static fn() => fixture_update( 'wp_options', 'option_name', 'astra-settings', array( 'autoload' => 'off' ) ); }
    $failures = array( $failure ); $caught = null;
    try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && str_contains( $caught->getMessage(), 'recovery stopped' ), "unknown $mutation state stops recovery" );
    check( ( $event_counts['delete:before'] ?? 0 ) === 0, "unknown $mutation state deletes no record" );
    check( ! str_contains( $caught->getMessage() . implode( '\n', $logs ), 'SYNTHETIC_PRIVATE' ), "unknown $mutation values not disclosed" );
    if ( $mutation === 'created-code' ) { check( fixture_sql( 'SELECT COUNT(*) FROM wp_posts WHERE post_content=?', array( 'SYNTHETIC_PRIVATE_FOREIGN_CODE' ) )->fetchColumn() === '1', 'external created code retained' ); }
    if ( $mutation === 'source-meta' ) { check( fixture_sql( 'SELECT COUNT(*) FROM wp_postmeta WHERE meta_key=?', array( 'during-write' ) )->fetchColumn() === '1', 'external source metadata retained' ); }
    if ( $mutation === 'active-meta' ) { check( fixture_sql( 'SELECT COUNT(*) FROM wp_postmeta WHERE meta_key=?', array( 'external' ) )->fetchColumn() === '1', 'external owned metadata retained' ); }
    if ( $mutation === 'css-race' ) { check( str_ends_with( row( 'posts', 'ID', 29 )['post_content'], ' ' ), 'CSS byte-exact CAS retains concurrent whitespace edit' ); }
    if ( $mutation === 'astra-race' ) { check( row( 'options', 'option_name', 'astra-settings' )['autoload'] === 'off', 'Astra CAS retains concurrent autoload edit' ); }
    check( preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $match ) === 1, "unknown $mutation error identifies actual recovery snapshot" );
    refusal( array( 'rollback', $match[1] ), "incomplete $mutation snapshot cannot authorize destructive rollback" );
}

// API error returns and SQL zero/error/wrong-row returns are distinct from exceptions.
foreach ( array( 'create', 'normalize-meta', 'correct-code', 'activate', 'css-cas', 'astra-cas' ) as $operation ) {
    foreach ( array( 'before', 'after' ) as $side ) {
        $values = in_array( $operation, array( 'css-cas', 'astra-cas' ), true ) ? array( false, 0, 2 ) : ( $operation === 'normalize-meta' ? array( false, 0, true, 'invalid', 999999, new WP_Error( 'synthetic', 'Synthetic API failure' ) ) : array( 0, new WP_Error( 'synthetic', 'Synthetic API failure' ) ) );
        foreach ( $values as $value ) {
            restore_state( $original ); $result_overrides = array( array( 'operation' => $operation, 'side' => $side, 'value' => $value ) ); $caught = null;
            try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
            check( $caught !== null && $result_overrides === array(), "$operation $side invalid return was exercised" );
            if ( $operation === 'normalize-meta' && $side === 'after' ) {
                check( ( $event_counts['delete:before'] ?? 0 ) === 0 && str_contains( $caught->getMessage(), 'recovery stopped' ) && count( $normalization_observations ) === 1, 'invalid returned metadata ID never authorizes adoption or deletion of the inserted row' );
                $observed = $normalization_observations[0];
                check( rows( 'postmeta', 'post_id', $observed['id'], 'meta_id' ) === $observed['after'] && row( 'posts', 'ID', $observed['id'] )['post_status'] === 'draft', 'unproven metadata insertion stays intact on an inactive draft' );
            } else { check( all_state() === $original, "$operation $side invalid return leaves/restores exact original state" ); }
            $fault_cases++;
        }
    }
}
foreach ( array( 'before', 'after' ) as $side ) {
    restore_state( $applied ); $result_overrides = array( array( 'operation' => 'delete', 'side' => $side, 'value' => false ) ); $caught = null;
    try { invoke( array( 'rollback', $path ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && $result_overrides === array(), "delete $side false return was exercised" );
    invoke( array( 'rollback', $path ) ); check( all_state() === $original, "delete $side false return resumes safely from exact prefix" );
    $fault_cases++;
}

// A valid incomplete journal is recoverable after its immediate recovery also fails.
$partials = array();
foreach ( array( 'create', 'correct-code', 'css-cas', 'astra-cas', 'activate', 'cache-refresh' ) as $operation ) {
    $key = "$operation:after";
    for ( $at = 1; $at <= ( $successful_event_counts[$key] ?? 0 ); $at++ ) {
        restore_state( $original );
        $failures = array( array( 'key' => $key, 'at' => $at ), array( 'key' => 'delete:before', 'at' => 1 ) ); $caught = null;
        try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
        check( $caught !== null && str_contains( $caught->getMessage(), 'recovery stopped' ) && preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $found ) === 1, "$key $at retains an actual partial journal after interrupted recovery" );
        $partial_path = $found[1]; $partial_bytes = file_get_contents( $partial_path ); $partial_snapshot = json_decode( $partial_bytes, true, 512, JSON_THROW_ON_ERROR ); $partial_state = all_state();
        check( $partial_snapshot['complete'] === false && $partial_snapshot['journal_sha256'] === digest( $partial_snapshot['journal'] ), "$key $at journal has exact recorded state hash" );
        refusal( array( 'verify', $partial_path ), "$key $at partial cannot claim complete applied verification" );
        refusal( array( 'apply', $partial_path ), "$key $at partial cannot authorize repeat apply" );
        fixture_insert( 'wp_postmeta', array( 'post_id' => 1107, 'meta_key' => 'after-partial', 'meta_value' => 'SYNTHETIC_PRIVATE_AFTER_PARTIAL' ) );
        refusal( array( 'rollback', $partial_path ), "$key $at partial refuses independent edit" );
        restore_state( $partial_state ); invoke( array( 'rollback', $partial_path ) );
        check( all_state() === $original && file_get_contents( $partial_path ) === $partial_bytes && ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "$key $at valid partial restores original without changing journal or issuing complete release marker" );
        $partials["$operation:$at"] = array( 'snapshot' => $partial_snapshot, 'state' => $partial_state );
        $fault_cases += 2;
    }
}
// A returned normalization ID is accepted and journalled before the next step.
// Fail that next step, then fail automatic deletion, to exercise manual recovery
// of four-key metadata whose PHP may still need its separate correction.
for ( $at = 1; $at <= 5; $at++ ) {
    restore_state( $original );
    $failures = array(
        array( 'key' => 'normalize-meta:after', 'at' => $at, 'throw' => false, 'change' => static function () {
            $reads = 0;
            $GLOBALS['read_hook'] = static function ( $query ) use ( &$reads ) {
                if ( $query === 'SELECT * FROM wp_posts ORDER BY ID' && ++$reads === 2 ) { $GLOBALS['read_hook'] = null; throw new RuntimeException( 'Synthetic failure after returned normalization ID' ); }
            };
        } ),
        array( 'key' => 'delete:before', 'at' => 1 ),
    );
    $caught = null; try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && str_contains( $caught->getMessage(), 'Synthetic failure after returned normalization ID' ) && str_contains( $caught->getMessage(), 'recovery stopped' ) && preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $found ) === 1, "normalization $at accepted ID has a recoverable journal after interrupted recovery" );
    $partial_path = $found[1]; $partial_bytes = file_get_contents( $partial_path ); $partial_snapshot = json_decode( $partial_bytes, true, 512, JSON_THROW_ON_ERROR ); $partial_state = all_state();
    $role = array_merge( $manifest['summary_slugs'], array( 'core' ) )[$at - 1];
    $record = $partial_snapshot['journal']['owned'][$role]; $observation = $normalization_observations[$at - 1];
    check( count( $record['meta'] ) === 4 && stable( $record['meta'] ) === stable( $observation['after'] ) && $partial_snapshot['complete'] === false && $partial_snapshot['journal_sha256'] === digest( $partial_snapshot['journal'] ), "normalization $at journal records exactly the returned new row and unchanged old IDs" );
    if ( $at === 1 ) { check( hash( 'sha256', $record['post']['post_content'] ) !== $manifest['payloads']['data'][$role]['code_sha256'], 'normalized Thai journal still has uncorrected PHP, proving a distinct recoverable intermediate phase' ); }
    refusal( array( 'verify', $partial_path ), "normalization $at incomplete journal is not a complete release" );
    fixture_insert( 'wp_postmeta', array( 'post_id' => 1160, 'meta_key' => 'after-normalization', 'meta_value' => 'SYNTHETIC_PRIVATE_AFTER_NORMALIZATION' ) );
    refusal( array( 'rollback', $partial_path ), "normalization $at journal refuses an independent edit" );
    restore_state( $partial_state ); invoke( array( 'rollback', $partial_path ) );
    check( all_state() === $original && file_get_contents( $partial_path ) === $partial_bytes && ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "normalization $at accepted four-key journal restores exact original without changing evidence" );
    $partials["normalized:$at"] = array( 'snapshot' => $partial_snapshot, 'state' => $partial_state ); $fault_cases += 2;
}
// Remove normalization from an earlier role whose PHP is already correct. A
// following role must be refused because of the missing key, not PHP escaping.
restore_state( $partials['create:2']['state'] ); $forged = $partials['create:2']['snapshot'];
$forged['journal']['owned']['thai-massage']['meta'] = array_values( array_filter( $forged['journal']['owned']['thai-massage']['meta'], static fn( $row ) => $row['meta_key'] !== '_wpcode_shortcode_attributes' ) );
check( hash( 'sha256', $forged['journal']['owned']['thai-massage']['post']['post_content'] ) === $manifest['payloads']['data']['thai-massage']['code_sha256'], 'normalization-order forgery retains exact corrected PHP' );
$forged['journal_sha256'] = digest( $forged['journal'] );
$error = refusal( array( 'rollback', snapshot_write( $forged ) ), 'earlier three-key draft followed by another role' );
check( str_contains( $error->getMessage(), 'creation/correction order differs' ), 'missing normalization is a creation-prefix barrier even with corrected PHP' );
foreach ( array( 'css', 'astra', 'publish' ) as $phase ) {
    restore_state( $partials['create:5']['state'] ); $forged = $partials['create:5']['snapshot'];
    $forged['journal']['owned']['core']['post']['post_content'] = substr( file_get_contents( "$source/payload/core.php" ), 6 );
    check( count( $forged['ids'] ) === 5 && count( $forged['journal']['owned']['core']['meta'] ) === 3, "$phase forgery has all five roles and exact three-key final draft" );
    $forged['journal']['records'][29]['post']['post_content'] = str_replace( file_get_contents( "$source/payload/reading-progress.css" ), '', $forged['old']['records'][29]['post']['post_content'] );
    if ( $phase !== 'css' ) {
        $settings = unserialize( $forged['journal']['options']['astra-settings']['option_value'], array( 'allowed_classes' => false ) ); $settings['scroll-to-top-enable'] = false;
        $forged['journal']['options']['astra-settings']['option_value'] = serialize( $settings );
    }
    if ( $phase === 'publish' ) { $forged['journal']['owned']['thai-massage']['post']['post_status'] = 'publish'; }
    $forged['journal_sha256'] = digest( $forged['journal'] );
    $error = refusal( array( 'rollback', snapshot_write( $forged ) ), "$phase before final draft normalization" );
    check( str_contains( $error->getMessage(), 'cleanup/activation phase differs' ), "$phase cannot bypass final three-key normalization even when PHP is correct" );
}

$partial_base = $partials['css-cas:1'];
foreach ( array( 'creation-gap', 'uncorrected-early-draft', 'activation-gap', 'astra-before-css', 'activation-before-cleanup', 'hash' ) as $mutation ) {
    restore_state( $partial_base['state'] ); $forged = $partial_base['snapshot']; $role = 'thai-massage';
    if ( $mutation === 'creation-gap' ) { $forged['journal']['owned'][$role] = null; unset( $forged['ids'][$role] ); }
    if ( $mutation === 'uncorrected-early-draft' ) {
        $record = &$forged['journal']['owned'][$role]; $record['post']['post_content'] = wp_unslash( $record['post']['post_content'] ); unset( $record );
    }
    if ( $mutation === 'activation-gap' ) { $forged['journal']['owned']['deep-tissue-massage']['post']['post_status'] = 'publish'; }
    if ( $mutation === 'astra-before-css' ) {
        $forged['journal']['records'][29]['post']['post_content'] = $forged['old']['records'][29]['post']['post_content'];
        $settings = unserialize( $forged['journal']['options']['astra-settings']['option_value'], array( 'allowed_classes' => false ) ); $settings['scroll-to-top-enable'] = false;
        $forged['journal']['options']['astra-settings']['option_value'] = serialize( $settings );
    }
    if ( $mutation === 'activation-before-cleanup' ) { $forged['journal']['owned'][$role]['post']['post_status'] = 'publish'; }
    $forged['journal_sha256'] = $mutation === 'hash' ? str_repeat( '0', 64 ) : digest( $forged['journal'] );
    $error = refusal( array( 'rollback', snapshot_write( $forged ) ), "forged partial phase $mutation" );
    check( preg_match( '/order differs|phase differs|recorded state hash differs/', $error->getMessage() ) === 1, "$mutation rejected by phase/hash semantics before inspecting mutable state" );
}
// A cleanup phase cannot precede creation of all five records.
restore_state( $partials['create:1']['state'] ); $forged = $partials['create:1']['snapshot'];
$forged['journal']['records'][29]['post']['post_content'] = str_replace( file_get_contents( "$source/payload/reading-progress.css" ), '', $forged['old']['records'][29]['post']['post_content'] ); $forged['journal_sha256'] = digest( $forged['journal'] );
$error = refusal( array( 'rollback', snapshot_write( $forged ) ), 'cleanup before five owned drafts' );
check( str_contains( $error->getMessage(), 'phase differs' ), 'premature cleanup rejected by phase semantics' );

// Snapshot replacement is conditional on this invocation's last verified bytes.
foreach ( array( 'bytes', 'permissions', 'symlink', 'final-save' ) as $mutation ) {
    restore_state( $original ); $existing = glob( "$testdir/backups/peek-release-before-*.json" ); $changed_path = null; $changed_bytes = null;
    $failures = array( array( 'key' => $mutation === 'final-save' ? 'cache-refresh:after' : 'create:after', 'at' => 1, 'throw' => false,
        'change' => static function () use ( $mutation, $existing, &$changed_path, &$changed_bytes, $testdir ) {
            $added = array_values( array_diff( glob( "$testdir/backups/peek-release-before-*.json" ), $existing ) );
            check( count( $added ) === 1, 'snapshot race fixture targets only the current invocation file' );
            $changed_path = $added[0]; $changed_bytes = file_get_contents( $changed_path );
            if ( $mutation === 'permissions' ) { chmod( $changed_path, 0644 ); }
            elseif ( $mutation === 'symlink' ) { rename( $changed_path, $changed_path . '.external' ); symlink( $changed_path . '.external', $changed_path ); }
            else { $changed_bytes .= " \n"; file_put_contents( $changed_path, $changed_bytes ); }
        } ) );
    $caught = null; try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && str_contains( $caught->getMessage(), 'Snapshot' ), "external snapshot $mutation change detected" );
    check( all_state() === $original && file_get_contents( $changed_path ) === $changed_bytes, "external snapshot $mutation retained while known database transition reverses" );
    check( ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "external snapshot $mutation produces no success marker" );
    if ( $mutation === 'permissions' ) { check( ( fileperms( $changed_path ) & 0777 ) === 0644, 'external snapshot permissions not overwritten' ); }
    if ( $mutation === 'symlink' ) { check( is_link( $changed_path ), 'external snapshot symlink not overwritten' ); }
}

// A loaded snapshot is evidence only while those same file bytes remain unchanged.
foreach ( array( 'verify', 'apply', 'rollback' ) as $mode ) {
    restore_state( $applied ); $bound = snapshot_write( $snapshot ); $before_bytes = file_get_contents( $bound );
    $read_hook = static function ( $query ) use ( $bound, $before_bytes ) {
        if ( $query === 'SELECT * FROM wp_posts ORDER BY ID' ) { $GLOBALS['read_hook'] = null; file_put_contents( $bound, $before_bytes . " \n" ); }
    };
    $error = refusal( array( $mode, $bound ), "$mode binds loaded snapshot through current-state reads" );
    check( str_contains( strtolower( $error->getMessage() ), 'snapshot' ) && file_get_contents( $bound ) === $before_bytes . " \n", "$mode preserves and reports late external snapshot edit" );
    check( ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "$mode refuses a marker for stale evidence" );
}
restore_state( $original ); $existing = glob( "$testdir/backups/peek-release-before-*.json" ); $changed_path = null; $changed_bytes = null;
$failures = array( array( 'key' => 'create:after', 'at' => 1, 'throw' => false,
    'change' => static function () use ( $testdir, $existing, &$changed_path, &$changed_bytes ) {
        $reads = 0;
        $GLOBALS['read_hook'] = static function ( $query ) use ( &$reads, $testdir, $existing, &$changed_path, &$changed_bytes ) {
            if ( $query !== 'SELECT * FROM wp_posts ORDER BY ID' || ++$reads !== 2 ) { return; }
            $GLOBALS['read_hook'] = null; $added = array_values( array_diff( glob( "$testdir/backups/peek-release-before-*.json" ), $existing ) );
            check( count( $added ) === 1, 'before-step file edit targets current journal only' );
            $changed_path = $added[0]; $changed_bytes = file_get_contents( $changed_path ) . " \n"; file_put_contents( $changed_path, $changed_bytes );
        };
    } ) );
$caught = null; try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
check( $caught !== null && str_contains( $caught->getMessage(), 'Snapshot' ), 'journal edit during next-step capture detected' );
check( ( $event_counts['create:before'] ?? 0 ) === 1 && ( $event_counts['normalize-meta:before'] ?? 0 ) === 0 && ( $event_counts['correct-code:before'] ?? 0 ) === 0 && ( $event_counts['activate:before'] ?? 0 ) === 0, 'journal edit permits no subsequent forward database write' );
check( all_state() === $original && file_get_contents( $changed_path ) === $changed_bytes, 'known in-memory recovery restores database while retaining changed journal bytes' );
// Even an edit occurring during derived-cache refresh prevents a stale success marker.
restore_state( $applied ); $bound = snapshot_write( $snapshot ); $before_bytes = file_get_contents( $bound );
$failures = array( array( 'key' => 'cache-refresh:after', 'at' => 1, 'throw' => false, 'change' => static fn() => file_put_contents( $bound, $before_bytes . " \n" ) ) );
$caught = null; try { invoke( array( 'rollback', $bound ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
check( $caught !== null && all_state() === $original && file_get_contents( $bound ) === $before_bytes . " \n", 'rollback completes known stores but detects evidence edit during cache refresh' );
check( ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), 'late rollback evidence edit never receives success marker' );

// Rehearsal diagnostics identify rejected storage shapes without exposing values.
// These are synthetic failures; they do not identify the actual WPCode copy's shape.
foreach ( array( 'extra-meta', 'missing-meta', 'changed-meta', 'null-meta', 'null-first-duplicate', 'premature-attributes', 'extra-relationship', 'missing-relationship', 'changed-relationship', 'private-relationship' ) as $mutation ) {
    restore_state( $original ); $unexpected_state = null; $unexpected_id = null; $expected_diagnostic = null;
    $private_key = 'SYNTHETIC_PRIVATE_UNKNOWN_META_KEY'; $private_value = 'SYNTHETIC_PRIVATE_UNKNOWN_META_VALUE';
    $failures = array( array( 'key' => 'create:after', 'at' => 1, 'throw' => false,
        'change' => static function ( $id ) use ( $mutation, &$unexpected_state, &$unexpected_id, &$expected_diagnostic, $private_key, $private_value ) {
            $unexpected_id = $id; $before_meta = rows( 'postmeta', 'post_id', $id, 'meta_id' );
            if ( $mutation === 'premature-attributes' ) { fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => '_wpcode_shortcode_attributes', 'meta_value' => 'a:0:{}' ) ); }
            if ( $mutation === 'extra-meta' ) { fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => $private_key, 'meta_value' => $private_value ) ); }
            if ( $mutation === 'missing-meta' ) { fixture_sql( 'DELETE FROM wp_postmeta WHERE post_id=? AND meta_key=?', array( $id, '_wpcode_priority' ) ); }
            if ( $mutation === 'changed-meta' || $mutation === 'null-meta' ) { fixture_sql( 'UPDATE wp_postmeta SET meta_value=? WHERE post_id=? AND meta_key=?', array( $mutation === 'null-meta' ? null : $private_value, $id, '_wpcode_priority' ) ); }
            if ( $mutation === 'null-first-duplicate' ) {
                $row = fixture_sql( 'SELECT * FROM wp_postmeta WHERE post_id=? AND meta_key=?', array( $id, '_wpcode_auto_insert' ) )->fetch( PDO::FETCH_ASSOC );
                fixture_sql( 'UPDATE wp_postmeta SET meta_id=? WHERE meta_id=?', array( (int) $row['meta_id'] + 10000, $row['meta_id'] ) );
                fixture_insert( 'wp_postmeta', array( 'meta_id' => $row['meta_id'], 'post_id' => $id, 'meta_key' => '_wpcode_auto_insert', 'meta_value' => null ) );
            }
            if ( $mutation === 'extra-relationship' ) { fixture_insert( 'wp_term_relationships', array( 'object_id' => $id, 'term_taxonomy_id' => 99, 'term_order' => '0' ) ); }
            if ( $mutation === 'missing-relationship' ) { fixture_sql( 'DELETE FROM wp_term_relationships WHERE object_id=? AND term_taxonomy_id=9', array( $id ) ); }
            if ( $mutation === 'changed-relationship' ) { fixture_sql( 'UPDATE wp_term_relationships SET term_order=7 WHERE object_id=? AND term_taxonomy_id=9', array( $id ) ); }
            if ( $mutation === 'private-relationship' ) { fixture_sql( 'UPDATE wp_term_relationships SET term_order=? WHERE object_id=? AND term_taxonomy_id=9', array( $private_value, $id ) ); }
            $expected_diagnostic = array( 'before_meta' => $before_meta, 'meta' => rows( 'postmeta', 'post_id', $id, 'meta_id' ), 'relationships' => rows( 'term_relationships', 'object_id', $id, 'term_taxonomy_id' ) );
            $unexpected_state = all_state();
        } ) );
    $caught = null; try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && $unexpected_state !== null && str_contains( $caught->getMessage(), 'recovery stopped' ), "$mutation diagnostic accompanies explicit unknown-state refusal" );
    check( all_state() === $unexpected_state && row( 'posts', 'ID', $unexpected_id )['post_status'] === 'draft', "$mutation retains the exact unexpected inactive draft and every external row" );
    check( ( $event_counts['create:before'] ?? 0 ) === 1 && ( $event_counts['normalize-meta:before'] ?? 0 ) === 0 && ( $event_counts['correct-code:before'] ?? 0 ) === 0 && ( $event_counts['activate:before'] ?? 0 ) === 0 && ( $event_counts['delete:before'] ?? 0 ) === 0 && ( $event_counts['css-cas:before'] ?? 0 ) === 0 && ( $event_counts['astra-cas:before'] ?? 0 ) === 0, "$mutation causes no subsequent correction, activation, deletion or settings write" );
    $output = $caught->getMessage() . implode( "\n", $logs );
    check( ! str_contains( $output, 'SYNTHETIC_PRIVATE' ) && ! str_contains( $output, $private_key ) && ! str_contains( $output, $private_value ), "$mutation diagnostics contain no private metadata key/value bytes" );
    check( ! str_contains( $output, 'RELEASE-SNAPSHOT ' ), "$mutation diagnostic emits no release success marker" );
    $diagnostic = marker( 'RELEASE-OWNED-DIAGNOSTIC' );
    $reason = match ( $mutation ) {
        'null-first-duplicate' => 'metadata-identity-or-duplicate',
        'extra-relationship', 'missing-relationship' => 'relationship-count',
        'changed-relationship', 'private-relationship' => 'relationship-row',
        default => 'metadata',
    };
    check( $diagnostic['role'] === 'thai-massage' && $diagnostic['reasons'] === array( $reason ), "$mutation diagnosis names the exact refused shape" );
    $describe = static fn( $value ) => $value === null ? array( 'type' => 'null', 'bytes' => null, 'sha256' => null ) : array( 'type' => 'string', 'bytes' => strlen( $value ), 'sha256' => hash( 'sha256', $value ) );
    $expected_known = array();
    foreach ( $expected_diagnostic['before_meta'] as $row ) { $expected_known[$row['meta_key']] = $row['meta_value']; }
    $present_expected = array_keys( $expected_known ); $expected_known['_wpcode_shortcode_attributes'] = null; ksort( $expected_known );
    check( array_column( $diagnostic['known_meta'], 'key' ) === array_keys( $expected_known ), "$mutation reports only the current phase's approved literal metadata names" );
    foreach ( $diagnostic['known_meta'] as $item ) {
        $matching = array_values( array_filter( $expected_diagnostic['meta'], static fn( $row ) => $row['meta_key'] === $item['key'] ) );
        $descriptors = array_map( static fn( $row ) => $describe( $row['meta_value'] ), $matching );
        check( $item['expected_count'] === ( in_array( $item['key'], $present_expected, true ) ? 1 : 0 ) && $item['actual_count'] === count( $matching ) && $item['expected'] === ( in_array( $item['key'], $present_expected, true ) ? $describe( $expected_known[$item['key']] ) : null ) && $item['actual'] === $descriptors, "$mutation accurately fingerprints known key " . $item['key'] );
    }
    $expected_unknown = $mutation === 'extra-meta' ? array( array( 'key_sha256' => hash( 'sha256', $private_key ), 'count' => 1, 'values' => array( $describe( $private_value ) ) ) ) : array();
    check( $diagnostic['unknown_meta'] === $expected_unknown, "$mutation unknown metadata names and values appear only as fingerprints" );
    $expected_relationships = array_map( static function ( $row ) use ( $describe ) {
        $safe = array();
        foreach ( array( 'object_id', 'term_taxonomy_id', 'term_order' ) as $key ) { $safe[$key] = is_string( $row[$key] ) && ctype_digit( ltrim( $row[$key], '-' ) ) ? $row[$key] : $describe( $row[$key] ); }
        return $safe;
    }, $expected_diagnostic['relationships'] );
    check( $diagnostic['expected_taxonomy_ids'] === array( 9, 10 ) && $diagnostic['actual_relationship_count'] === count( $expected_relationships ) && $diagnostic['relationships'] === $expected_relationships, "$mutation relationship count/IDs/order faithfully distinguish expected and actual shape" );
    if ( $mutation === 'null-first-duplicate' ) {
        $duplicate = array_values( array_filter( $diagnostic['known_meta'], static fn( $item ) => $item['key'] === '_wpcode_auto_insert' ) )[0];
        check( $duplicate['actual_count'] === 2 && $duplicate['actual'][0]['type'] === 'null' && $duplicate['actual'][1] === $describe( '1' ), 'NULL-first duplicate is refused even when a later value matches the expected value' );
    }
    check( preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $found ) === 1, "$mutation identifies actual incomplete journal" );
    refusal( array( 'rollback', $found[1] ), "$mutation incomplete journal cannot delete the unexpected draft" );
}

// Unique add_post_meta must not adopt even a concurrent row with the same bytes.
foreach ( array( 'matching-row', 'null-row', 'private-row', 'old-id-edited', 'post-edited', 'relationship-edited', 'null-first-attributes' ) as $mutation ) {
    restore_state( $original ); $unexpected_state = null; $unexpected_id = null;
    $before_insert = in_array( $mutation, array( 'matching-row', 'null-row', 'private-row' ), true );
    $failures = array( array( 'key' => $before_insert ? 'normalize-meta:before' : 'normalize-meta:after', 'at' => 1, 'throw' => false,
        'change' => static function ( $id ) use ( $mutation, $before_insert, &$unexpected_state, &$unexpected_id ) {
            $unexpected_id = $id;
            if ( $before_insert ) {
                $value = $mutation === 'matching-row' ? 'a:0:{}' : ( $mutation === 'null-row' ? null : 'SYNTHETIC_PRIVATE_CONCURRENT_ATTRIBUTE' );
                fixture_insert( 'wp_postmeta', array( 'post_id' => $id, 'meta_key' => '_wpcode_shortcode_attributes', 'meta_value' => $value ) );
            }
            if ( $mutation === 'old-id-edited' ) {
                $old = fixture_sql( 'SELECT meta_id FROM wp_postmeta WHERE post_id=? AND meta_key=?', array( $id, '_wpcode_priority' ) )->fetchColumn();
                fixture_sql( 'UPDATE wp_postmeta SET meta_id=? WHERE meta_id=?', array( (int) $old + 10000, $old ) );
            }
            if ( $mutation === 'post-edited' ) { fixture_update( 'wp_posts', 'ID', $id, array( 'post_excerpt' => 'SYNTHETIC_PRIVATE_CONCURRENT_POST' ) ); }
            if ( $mutation === 'relationship-edited' ) { fixture_sql( 'UPDATE wp_term_relationships SET term_order=3 WHERE object_id=? AND term_taxonomy_id=9', array( $id ) ); }
            if ( $mutation === 'null-first-attributes' ) {
                $attribute = fixture_sql( 'SELECT * FROM wp_postmeta WHERE post_id=? AND meta_key=?', array( $id, '_wpcode_shortcode_attributes' ) )->fetch( PDO::FETCH_ASSOC );
                fixture_sql( 'UPDATE wp_postmeta SET meta_id=? WHERE meta_id=?', array( (int) $attribute['meta_id'] + 10000, $attribute['meta_id'] ) );
                fixture_insert( 'wp_postmeta', array( 'meta_id' => $attribute['meta_id'], 'post_id' => $id, 'meta_key' => '_wpcode_shortcode_attributes', 'meta_value' => null ) );
            }
            $unexpected_state = all_state();
        } ) );
    $caught = null; try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && str_contains( $caught->getMessage(), 'recovery stopped' ) && $unexpected_state !== null, "$mutation normalization race explicitly refuses adoption" );
    check( all_state() === $unexpected_state && row( 'posts', 'ID', $unexpected_id )['post_status'] === 'draft', "$mutation retains every concurrent raw row and the inactive draft" );
    check( ( $event_counts['create:before'] ?? 0 ) === 1 && ( $event_counts['normalize-meta:before'] ?? 0 ) === 1 && ( $event_counts['correct-code:before'] ?? 0 ) === 0 && ( $event_counts['activate:before'] ?? 0 ) === 0 && ( $event_counts['delete:before'] ?? 0 ) === 0 && ( $event_counts['css-cas:before'] ?? 0 ) === 0 && ( $event_counts['astra-cas:before'] ?? 0 ) === 0, "$mutation prevents subsequent correction, creation, activation, cleanup or deletion" );
    if ( $before_insert ) { check( $normalization_observations === array(), "$mutation unique check returns false without inserting or replacing an existing value, including NULL" ); }
    check( ! str_contains( $caught->getMessage() . implode( '\n', $logs ), 'SYNTHETIC_PRIVATE' ) && ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "$mutation exposes no raw value or success marker" );
    check( preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $found ) === 1, "$mutation identifies the actual pre-normalization journal" );
    refusal( array( 'rollback', $found[1] ), "$mutation unowned normalization change cannot authorize removal" );
}

// Probe the actual included comparison closure after read-only preflight. This
// changes neither the production helper nor its state-machine inputs.
restore_state( $original ); invoke( array( 'preflight' ) );
$state_descriptor = static function ( $value, $present = true ) {
    if ( ! $present ) { return array( 'present' => false ); }
    $encoded = json_encode( stable( $value ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_THROW_ON_ERROR );
    $descriptor = array( 'present' => true, 'type' => get_debug_type( $value ), 'bytes' => is_string( $value ) ? strlen( $value ) : strlen( $encoded ), 'sha256' => hash( 'sha256', get_debug_type( $value ) . ':' . $encoded ) );
    if ( is_array( $value ) ) { $descriptor['count'] = count( $value ); $descriptor['shape'] = array_is_list( $value ) ? 'list' : 'map'; }
    return $descriptor;
};
$probe_state_diagnostic = static function ( $actual, $expected, $label, $log_failure = false ) {
    $GLOBALS['logs'] = array(); $before = all_state(); $events_before = $GLOBALS['events']; $reads_before = $GLOBALS['wpdb']->reads; $writes_before = $GLOBALS['wpdb']->writes;
    $GLOBALS['fail_state_diagnostic_log'] = $log_failure; $caught = null;
    try { $GLOBALS['diagnostic_equal']( $actual, $expected, $label ); } catch ( RuntimeException $error ) { if ( $error instanceof Harness_Error ) { throw $error; } $caught = $error; }
    check( $caught !== null && $caught->getMessage() === $label . ' differs (expected ' . digest( $expected ) . ', actual ' . digest( $actual ) . ').', "$label retains the exact original refusal and hashes" );
    check( all_state() === $before && $GLOBALS['events'] === $events_before && $GLOBALS['wpdb']->reads === $reads_before && $GLOBALS['wpdb']->writes === $writes_before, "$label diagnostics use only captured values with no reads or writes" );
    check( ! str_contains( $caught->getMessage() . implode( '\n', $GLOBALS['logs'] ), 'SYNTHETIC_PRIVATE' ) && ! str_contains( implode( '\n', $GLOBALS['logs'] ), 'RELEASE-SNAPSHOT ' ), "$label contains no raw private key/value or success marker" );
    if ( $log_failure ) { check( $GLOBALS['logs'] === array() && $GLOBALS['fail_state_diagnostic_log'] === false, 'a diagnostic logging exception cannot replace or relax the state guard' ); return null; }
    $diagnostic = marker( 'RELEASE-STATE-DIAGNOSTIC' );
    check( $diagnostic['label'] === $label && is_bool( $diagnostic['truncated'] ) && count( $diagnostic['changes'] ) <= 32, "$label carries a bounded, accurately named diagnostic" );
    check( strlen( implode( '\n', $GLOBALS['logs'] ) ) < 30000, "$label diagnostic output remains bounded" );
    return $diagnostic;
};
// Equal values, including harmless map ordering, still produce no diagnostics.
$logs = array(); $events_before = $events; $reads_before = $wpdb->reads;
$diagnostic_equal( array( 'post' => array( 'count' => '17', 'ID' => '9' ) ), array( 'post' => array( 'ID' => '9', 'count' => '17' ) ), 'Equal comparison' );
check( $logs === array() && all_state() === $original && $events === $events_before && $wpdb->reads === $reads_before, 'equal canonical states remain a silent no-op' );

$diagnostic = $probe_state_diagnostic( array( 'post' => array( 'ID' => '9' ) ), array( 'post' => array( 'ID' => '9', 'post_excerpt' => null ) ), 'Absent versus null' );
check( $diagnostic['truncated'] === false && $diagnostic['changes'] === array( array( 'path' => array( 'post', 'post_excerpt' ), 'kind' => 'missing', 'expected' => $state_descriptor( null ), 'actual' => array( 'present' => false ) ) ), 'a present null value is distinguished from an absent key' );
$private_key = 'post_content/SYNTHETIC_PRIVATE_UNKNOWN_PATH.雪';
$diagnostic = $probe_state_diagnostic( array( 'post' => array( 'ID' => '9', $private_key => null ) ), array( 'post' => array( 'ID' => '9' ) ), 'Unknown added path' );
check( $diagnostic['changes'] === array( array( 'path' => array( 'post', 'sha256:' . hash( 'sha256', $private_key ) ), 'kind' => 'added', 'expected' => array( 'present' => false ), 'actual' => $state_descriptor( null ) ) ), 'unknown path keys are fully hashed even when they begin with a known schema name' );
$before_private = 'SYNTHETIC_PRIVATE_BEFORE_雪'; $after_private = 'SYNTHETIC_PRIVATE_AFTER_雪';
$diagnostic = $probe_state_diagnostic( array( 'post' => array( 'post_content' => $after_private ) ), array( 'post' => array( 'post_content' => $before_private ) ), 'Raw content changed' );
check( $diagnostic['changes'][0] === array( 'path' => array( 'post', 'post_content' ), 'kind' => 'value', 'expected' => $state_descriptor( $before_private ), 'actual' => $state_descriptor( $after_private ) ), 'raw content diagnostics contain only exact typed hashes and byte lengths' );
$diagnostic = $probe_state_diagnostic( array( 'count' => 1.0 ), array( 'count' => 1 ), 'Scalar type changed' );
check( $diagnostic['changes'][0]['kind'] === 'type' && $diagnostic['changes'][0]['expected'] === $state_descriptor( 1 ) && $diagnostic['changes'][0]['actual'] === $state_descriptor( 1.0 ) && $diagnostic['changes'][0]['expected']['sha256'] !== $diagnostic['changes'][0]['actual']['sha256'], 'typed fingerprints distinguish integer and float despite identical canonical JSON bytes' );
$diagnostic = $probe_state_diagnostic( array( 'records' => array( 'ID' => '9' ) ), array( 'records' => array( '9' ) ), 'Array shape changed' );
check( $diagnostic['changes'][0]['kind'] === 'shape' && $diagnostic['changes'][0]['expected'] === $state_descriptor( array( '9' ) ) && $diagnostic['changes'][0]['actual'] === $state_descriptor( array( 'ID' => '9' ) ), 'list versus map reports a shape change with count and hash' );
$diagnostic = $probe_state_diagnostic( array( 'witnesses' => array( 'posts' => array( 0 => 'new0', '00' => 'new00' ) ) ), array( 'witnesses' => array( 'posts' => array( 0 => 'old0', '00' => 'old00' ) ) ), 'Distinct numeric-looking keys' );
check( count( $diagnostic['changes'] ) === 2 && array_column( $diagnostic['changes'], 'path' ) === array( array( 'witnesses', 'posts', '0' ), array( 'witnesses', 'posts', '00' ) ), 'zero and double-zero keys remain separate changed paths' );

$numeric_private_key = '12345678901234567890';
$diagnostic = $probe_state_diagnostic( array( $numeric_private_key => $after_private ), array( $numeric_private_key => $before_private ), 'Unknown numeric field' );
check( $diagnostic['changes'][0]['path'] === array( 'sha256:' . hash( 'sha256', $numeric_private_key ) ) && ! str_contains( json_encode( $diagnostic ), $numeric_private_key ), 'numeric-looking unknown field names are hashed outside approved ID maps and lists' );
$many_expected = $many_actual = array();
for ( $i = 0; $i < 100; $i++ ) { $key = 'SYNTHETIC_PRIVATE_PATH_' . $i; $many_expected[$key] = $before_private; $many_actual[$key] = $after_private; }
$diagnostic = $probe_state_diagnostic( array( 'witnesses' => $many_actual ), array( 'witnesses' => $many_expected ), 'Changed-path cap' );
check( count( $diagnostic['changes'] ) === 32 && $diagnostic['truncated'] === true && count( array_filter( $diagnostic['changes'], static fn( $change ) => $change['kind'] === 'value' && str_starts_with( $change['path'][1], 'sha256:' ) && strlen( $change['path'][1] ) === 71 ) ) === 32, 'more than 32 differences are bounded without leaking unknown path keys' );
$deep_expected = $before_private; $deep_actual = $after_private;
for ( $i = 0; $i < 11; $i++ ) { $deep_expected = array( 'post' => $deep_expected ); $deep_actual = array( 'post' => $deep_actual ); }
$diagnostic = $probe_state_diagnostic( $deep_actual, $deep_expected, 'Path-depth cap' );
check( count( $diagnostic['changes'] ) === 1 && $diagnostic['truncated'] === true && $diagnostic['changes'][0]['kind'] === 'bounded' && count( $diagnostic['changes'][0]['path'] ) === 8 && $diagnostic['changes'][0]['expected']['shape'] === 'map', 'deep changes report one hashed bounded branch at depth eight' );
$wide_expected = array_fill( 0, 3000, 'unchanged' ); $wide_actual = $wide_expected; $wide_actual[2999] = $after_private;
$diagnostic = $probe_state_diagnostic( array( 'records' => $wide_actual ), array( 'records' => $wide_expected ), 'Scan-budget cap' );
check( count( $diagnostic['changes'] ) === 1 && $diagnostic['truncated'] === true && $diagnostic['changes'][0]['kind'] === 'bounded' && $diagnostic['changes'][0]['path'] === array( 'records' ) && $diagnostic['changes'][0]['actual']['count'] === 3000, 'scan exhaustion reports a bounded branch instead of silently claiming no changes' );
for ( $i = 0; $i < 32; $i++ ) { $wide_actual[$i] = $after_private; }
$diagnostic = $probe_state_diagnostic( array( 'records' => $wide_actual ), array( 'records' => $wide_expected ), 'Combined entry and scan caps' );
check( count( $diagnostic['changes'] ) === 32 && $diagnostic['truncated'] === true && count( array_filter( $diagnostic['changes'], static fn( $change ) => $change['kind'] === 'value' ) ) === 32, 'scan exhaustion after 32 changes cannot append a 33rd bounded entry' );
$probe_state_diagnostic( array( 'count' => '18' ), array( 'count' => '17' ), 'Logging failure retains refusal', true );

// State diagnostics describe the changed path without adopting the changed state.
foreach ( array( 'shared-counts', 'unrelated-witness', 'raw-content' ) as $mutation ) {
    restore_state( $original ); $unexpected_state = null; $unexpected_id = null;
    $failures = array( array( 'key' => 'create:after', 'at' => 1, 'throw' => false,
        'change' => static function ( $id ) use ( $mutation, &$unexpected_state, &$unexpected_id ) {
            $unexpected_id = $id;
            if ( $mutation === 'shared-counts' ) { fixture_sql( 'UPDATE wp_term_taxonomy SET count=count+1 WHERE term_taxonomy_id IN (9,10)' ); }
            if ( $mutation === 'unrelated-witness' ) { fixture_update( 'wp_posts', 'ID', 2000, array( 'post_excerpt' => 'SYNTHETIC_PRIVATE_WITNESS_EDIT' ) ); }
            if ( $mutation === 'raw-content' ) { fixture_update( 'wp_posts', 'ID', 1160, array( 'post_content' => 'SYNTHETIC_PRIVATE_RAW_CONTENT_EDIT' ) ); }
            $unexpected_state = all_state();
        } ) );
    $caught = null; try { invoke( array( 'apply' ) ); } catch ( Refusal_Error $error ) { $caught = $error; }
    check( $caught !== null && str_contains( $caught->getMessage(), 'Inactive creation full state differs' ) && str_contains( $caught->getMessage(), 'recovery stopped' ), "$mutation diagnostic preserves the original full-state refusal" );
    check( all_state() === $unexpected_state && row( 'posts', 'ID', $unexpected_id )['post_status'] === 'draft', "$mutation diagnostics retain the exact unknown state and inactive draft" );
    check( ( $event_counts['create:before'] ?? 0 ) === 1 && ( $event_counts['normalize-meta:before'] ?? 0 ) === 0 && ( $event_counts['correct-code:before'] ?? 0 ) === 0 && ( $event_counts['activate:before'] ?? 0 ) === 0 && ( $event_counts['delete:before'] ?? 0 ) === 0 && ( $event_counts['css-cas:before'] ?? 0 ) === 0 && ( $event_counts['astra-cas:before'] ?? 0 ) === 0, "$mutation diagnostic permits no adoption or subsequent database action" );
    check( ! str_contains( $caught->getMessage() . implode( '\n', $logs ), 'SYNTHETIC_PRIVATE' ) && ! str_contains( implode( '\n', $logs ), 'RELEASE-SNAPSHOT ' ), "$mutation diagnostics contain neither raw values nor a success marker" );
    $diagnostics = array();
    foreach ( $logs as $line ) { if ( str_starts_with( $line, 'RELEASE-STATE-DIAGNOSTIC ' ) ) { $diagnostics[] = json_decode( substr( $line, strlen( 'RELEASE-STATE-DIAGNOSTIC ' ) ), true, 512, JSON_THROW_ON_ERROR ); } }
    $phase = array_values( array_filter( $diagnostics, static fn( $item ) => $item['label'] === 'Inactive creation full state' ) );
    check( count( $phase ) === 1 && $phase[0]['truncated'] === false, "$mutation reports the exact initial-creation mismatch once without truncation" );
    $expected_changes = array();
    if ( $mutation === 'shared-counts' ) {
        foreach ( array( '0', '1' ) as $index ) { $expected_changes[] = array( 'path' => array( 'taxonomies', $index, 'count' ), 'kind' => 'value', 'expected' => $state_descriptor( '17' ), 'actual' => $state_descriptor( '18' ) ); }
    } elseif ( $mutation === 'unrelated-witness' ) {
        $old_row = array_values( array_filter( $original['posts'], static fn( $row ) => $row['ID'] === '2000' ) )[0];
        $expected_changes[] = array( 'path' => array( 'witnesses', 'posts', '2000' ), 'kind' => 'value', 'expected' => $state_descriptor( digest( $old_row ) ), 'actual' => $state_descriptor( digest( row( 'posts', 'ID', 2000 ) ) ) );
    } else {
        $old_row = array_values( array_filter( $original['posts'], static fn( $row ) => $row['ID'] === '1160' ) )[0];
        $expected_changes[] = array( 'path' => array( 'records', '1160', 'post', 'post_content' ), 'kind' => 'value', 'expected' => $state_descriptor( $old_row['post_content'] ), 'actual' => $state_descriptor( 'SYNTHETIC_PRIVATE_RAW_CONTENT_EDIT' ) );
    }
    check( $phase[0]['changes'] === $expected_changes, "$mutation pinpoints the actual captured difference using exact typed fingerprints" );
    check( preg_match( '/Snapshot: (.+)$/', $caught->getMessage(), $found ) === 1, "$mutation retains the actual private recovery journal" );
    refusal( array( 'rollback', $found[1] ), "$mutation diagnostic cannot authorize removal of the unknown draft" );
}

restore_state( $original );
check( file_get_contents( "$source/manifest.json" ) === $manifest_bytes, 'production manifest was never modified by synthetic fixture' );
foreach ( $payload_specs as $role => $spec ) { check( hash_file( 'sha256', "$source/{$spec['file']}" ) === $spec['file_sha256'], "production $role payload unchanged after all controls" ); }
remove_tree( $testdir );
echo 'PASS Peek visitor release installer: ' . $pass . ' checks, ' . $fault_cases . " injected write-boundary failures; isolated SQLite/WP API model only, real WordPress copy rehearsal still required.\n";

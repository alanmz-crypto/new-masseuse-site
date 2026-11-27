<?php
// Shared Peek behavior. Visitor pages without an existing practice rail receive
// a title-only rail; ts_pk_pages registrations enable the approved summaries.
// The server-rendered .ts-jump row owns its destinations and no-JavaScript
// fallback. Other pages use visible H2 targets, or their main reading start.
// Section names = the row's labels; descriptions = the first paragraph of each section; reading time = its words at 200 a minute. No page content is changed.
// A duplicate core must not register a second set of hooks. Data snippets register
// their filters at load time; the current page is selected only during rendering.
if ( ! function_exists( 'ts_pk_page_data' ) ) {
function ts_pk_page_data() {
	if ( is_admin() || is_feed() || ! is_page() ) { return null; }
	$page = get_queried_object();
	$pages = apply_filters( 'ts_pk_pages', array() );
	$data = $page && is_array( $pages ) ? ( $pages[ $page->post_name ] ?? null ) : null;
	if ( ! is_array( $data ) || ( $data['schema'] ?? null ) !== 1 || ( $data['slug'] ?? '' ) !== $page->post_name ) { return null; }
	// Draft text stays in WordPress/repository storage, never in public source.
	if ( ( $data['approval']['status'] ?? '' ) !== 'approved' ) { $data['items'] = array(); $data['coverage'] = (object) array(); }
	return $data;
}
} else { return; }
// Astra also schedules a fragment jump 750 ms after DOMContentLoaded. On
// fragment arrivals with a summary rail, let Peek own alignment. Ordinary
// links retain native fragment navigation and the rail keeps its controls.
add_action( 'wp_enqueue_scripts', function () {
	if ( ts_pk_page_data() ) {
		wp_add_inline_script( 'astra-theme-js', 'if (location.hash && window.astra) { window.astra.is_scroll_to_id = false; }', 'before' );
	}
}, 100 );
// Remember interaction while the document and external runtime are loading.
add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() ) { return; }
	?>
<script id="ts-peek-arrival">
(function (marker) {
  if (!location.hash) return;
  marker.dataset.hash = location.hash;
  var events = ['wheel', 'touchstart', 'pointerdown', 'keydown', 'mousedown', 'hashchange', 'popstate'];
  function clear() { events.forEach(function (type) { window.removeEventListener(type, cancel, true); }); }
  function cancel() { delete marker.dataset.hash; clear(); }
  events.forEach(function (type) { window.addEventListener(type, cancel, { passive: true, capture: true }); });
  window.addEventListener('load', clear, { once: true });
})(document.currentScript);
</script>
	<?php
}, 1 );
add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() ) {
		return;
	}
	?>
<link rel="stylesheet" id="ts-peek-nav-css" href="<?php echo esc_url( content_url( '/ts-peek/v2/navigation.css' ) ); ?>">
	<?php
}, 200 );

add_action( 'wp_footer', function () {
	if ( is_admin() || is_feed() ) {
		return;
	}
	$page = get_queried_object();
	$title = is_page() && $page && ! empty( $page->post_title ) ? $page->post_title : wp_get_document_title();
	$title = html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	// Search/404 identity should not repeat the site's branding in every box.
	if ( ! is_page() && function_exists( 'get_bloginfo' ) ) {
		$site_name = html_entity_decode( wp_strip_all_tags( (string) get_bloginfo( 'name' ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( '' !== $site_name ) { $title = preg_replace( '/\s+[-|–—]\s+' . preg_quote( $site_name, '/' ) . '$/u', '', $title ); }
	}
	$flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
	$config = wp_json_encode( array( 'title' => $title, 'mode' => is_search() ? 'fixed' : 'sections' ), $flags );
	if ( false === $config ) { return; }
	echo '<script type="application/json" id="ts-peek-nav-config">' . $config . '</script>';
	$data = ts_pk_page_data();
	if ( $data ) {
		$json = wp_json_encode( $data, $flags );
		if ( false !== $json ) { echo '<script type="application/json" id="ts-peek-nav-data">' . $json . '</script>'; }
	}
	?>
<script id="ts-peek-nav-runtime" src="<?php echo esc_url( content_url( '/ts-peek/v2/navigation.js' ) ); ?>"></script>
	<?php
}, 30 );

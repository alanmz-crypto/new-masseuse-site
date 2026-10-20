<?php
// Stored in WPCode without this opening tag. Public by URL, unlisted elsewhere.
$rpa_ids = static function () {
	static $ids = null;
	if ( null === $ids ) {
		$ids = array();
		foreach ( array( 'personal-automation', 'personal-automation/privacy' ) as $path ) {
			$page = get_page_by_path( $path );
			if ( $page ) {
				$ids[] = (int) $page->ID;
			}
		}
	}
	return $ids;
};
add_filter( 'wp_robots', static function ( $robots ) use ( $rpa_ids ) {
	if ( $rpa_ids() && is_page( $rpa_ids() ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	}
	return $robots;
} );
add_action( 'template_redirect', static function () use ( $rpa_ids ) {
	if ( $rpa_ids() && is_page( $rpa_ids() ) && ! headers_sent() ) {
		header( 'X-Robots-Tag: noindex', false );
	}
} );
add_action( 'pre_get_posts', static function ( $query ) use ( $rpa_ids ) {
	if ( ! is_admin() && $query->is_search() ) {
		$query->set( 'post__not_in', array_values( array_unique( array_merge( (array) $query->get( 'post__not_in' ), $rpa_ids() ) ) ) );
	}
} );
add_filter( 'wp_list_pages_excludes', static function ( $ids ) use ( $rpa_ids ) {
	return array_values( array_unique( array_merge( $ids, $rpa_ids() ) ) );
} );
add_filter( 'wp_nav_menu_objects', static function ( $items ) use ( $rpa_ids ) {
	return array_values( array_filter( $items, static function ( $item ) use ( $rpa_ids ) {
		return ! ( 'page' === $item->object && in_array( (int) $item->object_id, $rpa_ids(), true ) );
		} ) );
} );
add_filter( 'wp_sitemaps_posts_query_args', static function ( $args, $type ) use ( $rpa_ids ) {
	if ( 'page' === $type ) {
		$args['post__not_in'] = array_values( array_unique( array_merge( $args['post__not_in'] ?? array(), $rpa_ids() ) ) );
	}
	return $args;
}, 10, 2 );
add_filter( 'surerank_exclude_posts_from_sitemap', static function ( $ids ) use ( $rpa_ids ) {
	return array_values( array_unique( array_merge( $ids, $rpa_ids() ) ) );
} );
// ref: ./install.php — installs this snippet and per-page SureRank noindex metadata

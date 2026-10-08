<?php
/**
 * Polylang integration.
 *
 * - Translatable post types / taxonomies.
 * - Same slug across languages (/company/ and /en/company/) — a Polylang Pro
 *   feature, implemented here for the free version.
 * - Language helpers used by templates (work without Polylang too).
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current language slug ('ja' | 'en').
 */
function netelly_lang(): string {
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = pll_current_language();
		if ( $lang ) {
			return $lang;
		}
		$default = pll_default_language();
		if ( $default ) {
			return $default;
		}
	}
	return 'ja';
}

/**
 * Post IDs with a slug in one language, straight from the DB.
 * Polylang rewrites/strips language filters on WP_Query depending on context
 * (admin, WP-CLI, early front-end hooks), so this bypasses WP_Query on purpose.
 *
 * @param string   $post_type Post type.
 * @param string   $slug      post_name.
 * @param string   $lang      Language slug.
 * @param int      $exclude   Post ID to ignore.
 * @param string[] $statuses  Post statuses.
 * @param int      $parent    Parent ID (hierarchical types) or -1 to ignore.
 * @return int[]
 */
function netelly_ids_by_slug( string $post_type, string $slug, string $lang, int $exclude = 0, array $statuses = array( 'publish' ), int $parent = -1 ): array {
	global $wpdb;
	$term = get_term_by( 'slug', $lang, 'language' );
	if ( ! $term ) {
		return array();
	}
	$status_in = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
	$sql       = "SELECT p.ID FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID AND tr.term_taxonomy_id = %d
		WHERE p.post_type = %s AND p.post_name = %s AND p.ID <> %d AND p.post_status IN ($status_in)";
	$args      = array_merge( array( (int) $term->term_taxonomy_id, $post_type, $slug, $exclude ), $statuses );
	if ( $parent >= 0 ) {
		$sql   .= ' AND p.post_parent = %d';
		$args[] = $parent;
	}
	return array_map( 'intval', $wpdb->get_col( $wpdb->prepare( $sql . ' ORDER BY p.menu_order, p.ID', $args ) ) ); // phpcs:ignore WordPress.DB
}

/**
 * Translated post ID in the current language (falls back to the given ID).
 */
function netelly_tr_id( int $post_id ): int {
	if ( function_exists( 'pll_get_post' ) ) {
		$tr = pll_get_post( $post_id );
		if ( $tr ) {
			return (int) $tr;
		}
	}
	return $post_id;
}

/**
 * Permalink of the page with the given (JP) slug, in the current language.
 */
function netelly_page_url( string $slug ): string {
	$ids = netelly_ids_by_slug( 'page', $slug, netelly_lang() );
	return $ids ? (string) get_permalink( $ids[0] ) : home_url( '/' . $slug . '/' );
}

/**
 * Home URL in the current language.
 */
function netelly_home_url(): string {
	return function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' );
}

// Translatable content types.
add_filter(
	'pll_get_post_types',
	static function ( $types, $is_settings ) {
		$types['work']     = 'work';
		$types['position'] = 'position';
		return $types;
	},
	10,
	2
);

/*
 * Shared slugs across languages.
 * 1) Allow a slug that only collides with posts in another language.
 */
add_filter(
	'wp_unique_post_slug',
	static function ( $slug, $post_id, $post_status, $post_type, $post_parent, $original_slug ) {
		if ( $slug === $original_slug || ! function_exists( 'pll_get_post_language' ) || ! pll_is_translated_post_type( $post_type ) ) {
			return $slug;
		}
		$lang = pll_get_post_language( $post_id );
		if ( ! $lang && isset( $_POST['post_lang_choice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- read-only, Polylang verifies.
			$lang = sanitize_key( wp_unslash( $_POST['post_lang_choice'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		if ( ! $lang ) {
			return $slug;
		}
		$clash = netelly_ids_by_slug(
			$post_type,
			$original_slug,
			$lang,
			(int) $post_id,
			array( 'publish', 'future', 'draft', 'pending', 'private' ),
			is_post_type_hierarchical( $post_type ) ? (int) $post_parent : -1
		);
		return $clash ? $slug : $original_slug;
	},
	10,
	6
);

/*
 * 2) Resolve page / work slugs within the language of the URL.
 *    WP's own lookup (get_page_by_path) ignores language and would return the JP post.
 */
add_filter(
	'request',
	static function ( $qv ) {
		if ( is_admin() || ! function_exists( 'pll_languages_list' ) ) {
			return $qv;
		}
		$lang = $qv['lang'] ?? '';
		if ( ! $lang ) {
			$lang = pll_default_language();
		}
		if ( ! empty( $qv['pagename'] ) ) {
			$parts = explode( '/', trim( $qv['pagename'], '/' ) );
			$id    = netelly_find_by_slug( 'page', end( $parts ), $lang );
			if ( $id ) {
				$qv['page_id'] = $id;
				unset( $qv['pagename'] );
			}
		} elseif ( ! empty( $qv['work'] ) && ! empty( $qv['post_type'] ) && 'work' === $qv['post_type'] ) {
			$id = netelly_find_by_slug( 'work', $qv['work'], $lang );
			if ( $id ) {
				$qv['p'] = $id;
				unset( $qv['work'], $qv['name'] );
			}
		}
		return $qv;
	}
);

/**
 * Post ID by slug in a language.
 */
function netelly_find_by_slug( string $post_type, string $slug, string $lang ): int {
	$statuses = is_user_logged_in() ? array( 'publish', 'private', 'draft', 'future', 'pending' ) : array( 'publish' );
	$ids      = netelly_ids_by_slug( $post_type, sanitize_title( $slug ), $lang, 0, $statuses );
	return $ids ? $ids[0] : 0;
}

/**
 * JP / EN switcher target: the translation of the current view, else that language's home.
 *
 * @return array<string,string> [ 'ja' => url, 'en' => url ]
 */
function netelly_lang_links(): array {
	$out = array();
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return array( 'ja' => home_url( '/' ) );
	}
	$langs = pll_the_languages(
		array(
			'raw'                    => 1,
			'hide_if_no_translation' => 0,
			'force_home'             => 0,
		)
	);
	foreach ( (array) $langs as $l ) {
		// Polylang returns the home URL when no translation exists (no_translation flag).
		$out[ $l['slug'] ] = $l['url'];
	}
	return $out;
}

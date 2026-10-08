<?php
/**
 * Sample data (JP copy from the design, EN draft translations).
 *
 * Run:  npm run seed
 *  (= wp-env run cli wp eval-file wp-content/themes/netelly-theme/bin/seed.php)
 *
 * Idempotent: posts created here are tracked in the option "netelly_seed_map"
 * and updated in place on re-run. Content edited in the admin IS overwritten
 * by a re-run, so run it only on a fresh/local site.
 *
 * @package netelly
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}
if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'Polylang と Secure Custom Fields（または ACF Pro）を有効化してください。' );
}

require __DIR__ . '/seed-data.php';

/* --------------------------------------------------------------------------
 * Helpers
 * ----------------------------------------------------------------------- */

/**
 * Field key from group + name (acf-json keys are "field_nt_{group}_{name}").
 */
function nts_key( string $group, string $name ): string {
	return 'field_nt_' . $group . '_' . $name;
}

/**
 * Update several fields of one group.
 *
 * @param string     $group  Group slug.
 * @param array      $values name => value.
 * @param int|string $target Post ID or options post_id.
 */
function nts_fields( string $group, array $values, $target ): void {
	foreach ( $values as $name => $value ) {
		update_field( nts_key( $group, $name ), $value, $target );
	}
}

/**
 * Link field value.
 */
function nts_link( string $title, string $url, bool $blank = false ): array {
	return array(
		'title'  => $title,
		'url'    => $url,
		'target' => $blank ? '_blank' : '',
	);
}

/**
 * Insert or update a post tracked by seed key + language.
 */
function nts_post( string $key, string $lang, array $args ): int {
	$map = (array) get_option( 'netelly_seed_map', array() );
	$id  = (int) ( $map[ $key . ':' . $lang ] ?? 0 );
	if ( $id && ! get_post( $id ) ) {
		$id = 0;
	}
	$args = array_merge( array( 'post_status' => 'publish' ), $args );
	if ( isset( $args['post_date'] ) ) {
		// Keep GMT in sync so re-runs never leave a post "scheduled".
		$args['post_date_gmt'] = get_gmt_from_date( $args['post_date'] );
		$args['edit_date']     = true;
	}
	if ( $id ) {
		$args['ID'] = $id;
		wp_update_post( wp_slash( $args ) );
	} else {
		$slug = $args['post_name'] ?? '';
		unset( $args['post_name'] );
		$id = (int) wp_insert_post( wp_slash( $args ), true );
		if ( ! $id ) {
			WP_CLI::error( "Failed to insert {$key} ({$lang})" );
		}
		$args['post_name'] = $slug;
	}
	pll_set_post_language( $id, $lang );
	// Re-apply the slug now that the language is known (shared slugs, see inc/i18n.php).
	if ( ! empty( $args['post_name'] ) ) {
		wp_update_post(
			array(
				'ID'        => $id,
				'post_name' => $args['post_name'],
			)
		);
	}
	$map[ $key . ':' . $lang ] = $id;
	update_option( 'netelly_seed_map', $map, false );
	return $id;
}

/**
 * Link translations of a seed key.
 */
function nts_link_tr( array $ids ): void {
	pll_save_post_translations( $ids );
}

/* --------------------------------------------------------------------------
 * 1. Languages (Polylang)
 * ----------------------------------------------------------------------- */
WP_CLI::log( '1/8 Languages' );
update_option( 'timezone_string', 'Asia/Tokyo' );
update_option( 'date_format', 'Y.m.d' );
update_option( 'blogname', 'Netelly' );
$model = PLL()->model;
$have  = wp_list_pluck( $model->languages->get_list(), 'slug' );
if ( ! in_array( 'ja', $have, true ) ) {
	$r = $model->languages->add( array( 'locale' => 'ja', 'slug' => 'ja', 'name' => '日本語', 'term_group' => 0, 'flag' => 'jp' ) );
	if ( is_wp_error( $r ) ) {
		WP_CLI::error( $r->get_error_message() );
	}
}
if ( ! in_array( 'en', $have, true ) ) {
	$r = $model->languages->add( array( 'locale' => 'en_US', 'slug' => 'en', 'name' => 'English', 'term_group' => 1, 'flag' => 'us' ) );
	if ( is_wp_error( $r ) ) {
		WP_CLI::error( $r->get_error_message() );
	}
}
$opts = PLL()->options;
$opts->set( 'force_lang', 1 );      // Language from the directory name: /en/.
$opts->set( 'hide_default', true ); // No /ja/ prefix.
$opts->set( 'rewrite', true );      // /en/ instead of /language/en/.
$opts->set( 'browser', false );     // Never auto-redirect by browser language.
$opts->set( 'redirect_lang', true ); // EN front page at /en/ (not /en/home/).
$opts->save();
$model->languages->update_default( 'ja' );
$model->clean_languages_cache();
// Content created before Polylang (Hello world, Sample Page …) goes to JP.
$model->set_language_in_mass();
// Languages are configured above, so Polylang's setup wizard notice is not needed.
update_option( 'pll_dismissed_notices', array_values( array_unique( array_merge( (array) get_option( 'pll_dismissed_notices', array() ), array( 'wizard' ) ) ) ) );

/* --------------------------------------------------------------------------
 * 2. News categories
 * ----------------------------------------------------------------------- */
WP_CLI::log( '2/8 Categories' );
$cats = array();
foreach ( NTS_CATEGORIES as $slug => $names ) {
	$tr = array();
	foreach ( array( 'ja', 'en' ) as $lang ) {
		$term_slug = 'ja' === $lang ? $slug : $slug . '-en';
		$term      = get_term_by( 'slug', $term_slug, 'category' );
		if ( $term ) {
			wp_update_term( $term->term_id, 'category', array( 'name' => $names[ $lang ] ) );
			$tid = (int) $term->term_id;
		} else {
			$t   = wp_insert_term( $names[ $lang ], 'category', array( 'slug' => $term_slug ) );
			$tid = (int) $t['term_id'];
		}
		pll_set_term_language( $tid, $lang );
		$tr[ $lang ]            = $tid;
		$cats[ $slug ][ $lang ] = $tid;
	}
	pll_save_term_translations( $tr );
}

/* --------------------------------------------------------------------------
 * 3. Pages
 * ----------------------------------------------------------------------- */
WP_CLI::log( '3/8 Pages' );
$pages = array();
foreach ( NTS_PAGES as $key => $p ) {
	$tr = array();
	foreach ( array( 'ja', 'en' ) as $lang ) {
		$id = nts_post(
			'page_' . $key,
			$lang,
			array(
				'post_type'    => 'page',
				'post_title'   => $p['title'][ $lang ],
				'post_name'    => $p['slug'],
				'post_content' => '',
				'menu_order'   => $p['order'],
			)
		);
		update_post_meta( $id, '_wp_page_template', $p['template'] ?? 'default' );
		if ( isset( $p['en_title'] ) ) {
			update_field( nts_key( 'page_hero', 'page_en_title' ), $p['en_title'], $id );
		}
		$tr[ $lang ]            = $id;
		$pages[ $key ][ $lang ] = $id;
	}
	nts_link_tr( $tr );
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $pages['home']['ja'] );
update_option( 'page_for_posts', $pages['news']['ja'] );
update_option( 'posts_per_page', 10 );
update_option( 'wp_page_for_privacy_policy', $pages['privacy']['ja'] );
// WP's auto-created draft "Privacy Policy" page is replaced by ours.
$wp_privacy = get_page_by_path( 'privacy-policy' );
if ( $wp_privacy && 'draft' === $wp_privacy->post_status ) {
	wp_delete_post( $wp_privacy->ID, true );
}

/**
 * Page permalink in a language.
 */
$purl = static function ( string $key, string $lang, string $hash = '' ) use ( &$pages ): string {
	return get_permalink( $pages[ $key ][ $lang ] ) . $hash;
};

/* --------------------------------------------------------------------------
 * 4. Works
 * ----------------------------------------------------------------------- */
WP_CLI::log( '4/8 Works' );
$works = array();
foreach ( NTS_WORKS as $i => $w ) {
	$tr = array();
	foreach ( array( 'ja', 'en' ) as $lang ) {
		$id = nts_post(
			'work_' . $w['slug'],
			$lang,
			array(
				'post_type'  => 'work',
				'post_title' => $w['title'][ $lang ],
				'post_name'  => $w['slug'],
				'menu_order' => $i + 1,
				'post_date'  => $w['date'],
			)
		);
		$f = array();
		foreach ( $w['fields'] as $name => $v ) {
			$f[ $name ] = is_array( $v ) && isset( $v['ja'] ) ? $v[ $lang ] : $v;
		}
		foreach ( array( 'episodes', 'cast', 'staff' ) as $rep ) {
			if ( isset( $w[ $rep ] ) ) {
				$f[ $rep ] = array_map(
					static fn( $row ) => array_map( static fn( $c ) => is_array( $c ) ? $c[ $lang ] : $c, $row ),
					$w[ $rep ]
				);
			}
		}
		nts_fields( 'work', $f, $id );
		$tr[ $lang ]                   = $id;
		$works[ $w['slug'] ][ $lang ] = $id;
	}
	nts_link_tr( $tr );
}

/* --------------------------------------------------------------------------
 * 5. News
 * ----------------------------------------------------------------------- */
WP_CLI::log( '5/8 News' );
foreach ( NTS_NEWS as $i => $n ) {
	$tr = array();
	foreach ( array( 'ja', 'en' ) as $lang ) {
		$id = nts_post(
			'news_' . $i,
			$lang,
			array(
				'post_type'     => 'post',
				'post_title'    => $n['title'][ $lang ],
				'post_content'  => isset( $n['body'] ) ? implode( "\n\n", array_map( static fn( $p ) => "<!-- wp:paragraph -->\n<p>{$p}</p>\n<!-- /wp:paragraph -->", $n['body'][ $lang ] ) ) : '',
				'post_date'     => $n['date'] . ' 00:00:00',
				'post_category' => array( $cats[ $n['cat'] ][ $lang ] ),
			)
		);
		nts_fields(
			'post',
			array(
				'external_url' => ! empty( $n['fund'] ) ? NTS_FUND_URL : '',
				'related_work' => isset( $n['work'] ) ? $works[ $n['work'] ][ $lang ] : '',
				'work_summary' => isset( $n['summary'] ) ? $n['summary'][ $lang ] : '',
			),
			$id
		);
		$tr[ $lang ] = $id;
	}
	nts_link_tr( $tr );
}
// Default WP sample content is not part of the site.
foreach ( array( 1 => 'post', 2 => 'page' ) as $sample_id => $type ) {
	$sample = get_post( $sample_id );
	if ( $sample && in_array( $sample->post_name, array( 'hello-world', 'sample-page' ), true ) ) {
		wp_delete_post( $sample_id, true );
	}
}

/* --------------------------------------------------------------------------
 * 6. Positions
 * ----------------------------------------------------------------------- */
WP_CLI::log( '6/8 Positions' );
foreach ( NTS_POSITIONS as $i => $p ) {
	$tr = array();
	foreach ( array( 'ja', 'en' ) as $lang ) {
		$id = nts_post(
			'position_' . $i,
			$lang,
			array(
				'post_type'  => 'position',
				'post_title' => $p['title'][ $lang ],
				'menu_order' => $i + 1,
			)
		);
		nts_fields(
			'position',
			array(
				'employment_type' => $p['type'][ $lang ],
				'location'        => $p['location'][ $lang ],
				'summary'         => $p['summary'][ $lang ],
				'entry_url'       => '',
			),
			$id
		);
		$tr[ $lang ] = $id;
	}
	nts_link_tr( $tr );
}

/* --------------------------------------------------------------------------
 * 7. Page fields + site settings (per language)
 * ----------------------------------------------------------------------- */
WP_CLI::log( '7/8 Page fields & site settings' );
foreach ( array( 'ja', 'en' ) as $lang ) {
	$data = nts_lang_data( $lang, $purl, $works );
	foreach ( $data['pages'] as $key => $groups ) {
		foreach ( $groups as $group => $values ) {
			nts_fields( $group, $values, $pages[ $key ][ $lang ] );
		}
	}
	nts_fields( 'settings', $data['settings'], 'netelly_' . $lang );
}

/* --------------------------------------------------------------------------
 * 8. Menus + string translations
 * ----------------------------------------------------------------------- */
WP_CLI::log( '8/8 Menus & strings' );
$locations = array();
foreach ( array( 'ja', 'en' ) as $lang ) {
	foreach ( nts_menus( $lang, $purl, $works ) as $location => $items ) {
		$name = "{$location}-{$lang}";
		$menu = wp_get_nav_menu_object( $name );
		$mid  = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
		foreach ( (array) wp_get_nav_menu_items( $mid ) as $old ) {
			wp_delete_post( $old->ID, true );
		}
		foreach ( $items as $item ) {
			$parent = wp_update_nav_menu_item(
				$mid,
				0,
				array(
					'menu-item-title'  => $item[0],
					'menu-item-url'    => $item[1],
					'menu-item-type'   => 'custom',
					'menu-item-status' => 'publish',
				)
			);
			foreach ( $item[2] ?? array() as $child ) {
				wp_update_nav_menu_item(
					$mid,
					0,
					array(
						'menu-item-title'     => $child[0],
						'menu-item-url'       => $child[1],
						'menu-item-type'      => 'custom',
						'menu-item-status'    => 'publish',
						'menu-item-parent-id' => $parent,
					)
				);
			}
		}
		$locations[ $location ][ $lang ] = $mid;
	}
}
// Polylang keeps one menu per location per language.
$nav_menus                                   = (array) $opts->get( 'nav_menus' );
$nav_menus[ get_option( 'stylesheet' ) ]     = $locations;
$opts->set( 'nav_menus', $nav_menus );
$opts->save();
set_theme_mod(
	'nav_menu_locations',
	array(
		'primary' => $locations['primary']['ja'],
		'footer'  => $locations['footer']['ja'],
	)
);

// EN values for the theme's UI strings (言語 › 翻訳).
$en = PLL()->model->languages->get( 'en' );
$mo = new PLL_MO();
$mo->import_from_db( $en );
foreach ( netelly_strings() as $s ) {
	$mo->add_entry( $mo->make_entry( $s[0], $s[1] ) );
}
$mo->export_to_db( $en );

// Polylang caches front/posts page translations in the language list.
PLL()->model->clean_languages_cache();
flush_rewrite_rules();
WP_CLI::success( 'Seeded: pages ' . count( $pages ) . ' × 2, works ' . count( $works ) . ' × 2, news ' . count( NTS_NEWS ) . ' × 2, positions ' . count( NTS_POSITIONS ) . ' × 2.' );

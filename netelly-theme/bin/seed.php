<?php
/**
 * Sample data (JP copy from the design, EN draft translations).
 *
 * Run:  npm run seed
 *  (= wp-env run cli wp eval-file wp-content/themes/netelly-theme/bin/seed.php)
 *  or, on a server without WP-CLI: 管理画面 › ツール › Netelly 初期データ.
 *
 * Idempotent: posts created here are tracked in the option "netelly_seed_map"
 * and updated in place on re-run. Content edited in the admin IS overwritten
 * by a re-run, so run it only on a fresh/local site.
 *
 * @package netelly
 */

// WP-CLI (npm run seed) or the admin screen ツール › Netelly 初期データ (inc/admin-seed.php).
if ( ! defined( 'WP_CLI' ) && ! defined( 'NETELLY_SEED_ADMIN' ) ) {
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

/**
 * Serialized Snow Monkey Forms item block (markup = the plugin's item/save.js).
 *
 * @param string $label    Item label.
 * @param string $for      Control id the label points to ('' = no <label>).
 * @param array  $control  [ block name, attrs ].
 * @param bool   $show     Show the label column.
 */
function nts_smf_item( string $label, string $for, array $control, bool $show = true ): string {
	$inner = serialize_block(
		array(
			'blockName'    => 'snow-monkey-forms/' . $control[0],
			'attrs'        => $control[1],
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		)
	);
	$head  = '';
	if ( $show ) {
		$text = '<span class="smf-item__label__text">' . esc_html( $label ) . '</span>';
		$head = '<div class="smf-item__col smf-item__col--label"><div class="smf-item__label">'
			. ( $for ? '<label for="' . esc_attr( $for ) . '">' . $text . '</label>' : $text )
			. '</div></div>';
	}
	$attrs = $show ? array() : array( 'isDisplayLabelColumn' => false );
	return '<!-- wp:snow-monkey-forms/item ' . ( $attrs ? serialize_block_attributes( $attrs ) . ' ' : '' ) . '-->'
		. '<div class="wp-block-snow-monkey-forms-item smf-item' . ( $show ? '' : ' smf-item--divider' ) . '">' . $head
		. '<div class="smf-item__col smf-item__col--controls"><div class="smf-item__controls">' . $inner . '</div></div></div>'
		. '<!-- /wp:snow-monkey-forms/item -->';
}

/**
 * Full post_content of a contact form (input + complete screens).
 */
function nts_form_content( array $d ): string {
	$l   = $d['labels'];
	$ph  = $d['placeholders'];
	$req = wp_json_encode( array( 'required' => true ) );

	$items = array(
		nts_smf_item( $l['type'], '', array( 'control-radio-buttons', array( 'name' => 'type', 'options' => implode( "\n", $d['types'] ), 'value' => $d['types'][0], 'validations' => $req ) ) ),
		nts_smf_item( $l['company'], 'company', array( 'control-text', array( 'name' => 'company', 'id' => 'company', 'placeholder' => $ph['company'], 'autocomplete' => 'organization' ) ) ),
		nts_smf_item( $l['name'], 'name', array( 'control-text', array( 'name' => 'name', 'id' => 'name', 'placeholder' => $ph['name'], 'autocomplete' => 'name', 'validations' => $req ) ) ),
		nts_smf_item( $l['email'], 'email', array( 'control-email', array( 'name' => 'email', 'id' => 'email', 'placeholder' => $ph['email'], 'validations' => wp_json_encode( array( 'required' => true, 'email' => true ) ) ) ) ),
		nts_smf_item( $l['tel'], 'tel', array( 'control-tel', array( 'name' => 'tel', 'id' => 'tel', 'placeholder' => $ph['tel'] ) ) ),
		nts_smf_item( $l['message'], 'message', array( 'control-textarea', array( 'name' => 'message', 'id' => 'message', 'rows' => 8, 'placeholder' => $ph['message'], 'validations' => $req ) ) ),
		nts_smf_item( '', '', array( 'control-checkboxes', array( 'name' => 'consent', 'options' => $l['consent'], 'validations' => $req ) ), false ),
	);

	$complete = '<!-- wp:heading -->' . "\n" . '<h2 class="wp-block-heading">' . esc_html( $d['complete']['heading'] ) . '</h2>' . "\n" . '<!-- /wp:heading -->'
		. "\n\n" . '<!-- wp:paragraph -->' . "\n" . '<p>' . esc_html( $d['complete']['text'] ) . '</p>' . "\n" . '<!-- /wp:paragraph -->';

	return '<!-- wp:snow-monkey-forms/form--input -->' . "\n" . '<div class="wp-block-snow-monkey-forms-form--input smf-form">' . implode( "\n\n", $items ) . '</div>' . "\n" . '<!-- /wp:snow-monkey-forms/form--input -->'
		. "\n\n" . '<!-- wp:snow-monkey-forms/form--complete -->' . "\n" . $complete . "\n" . '<!-- /wp:snow-monkey-forms/form--complete -->';
}

/**
 * Import a file from bin/press into the media library once (tracked in the seed map).
 */
function nts_media( string $key, string $path, string $title ): int {
	$map = (array) get_option( 'netelly_seed_map', array() );
	$id  = (int) ( $map[ 'media:' . $key ] ?? 0 );
	if ( $id && get_post( $id ) ) {
		return $id;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( basename( $path ) );
	copy( $path, $tmp );
	$id = media_handle_sideload( array( 'name' => basename( $path ), 'tmp_name' => $tmp ), 0, $title );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}
	$map[ 'media:' . $key ] = $id;
	update_option( 'netelly_seed_map', $map, false );
	return (int) $id;
}

/**
 * Press kit media: logo PNGs + a ZIP with SVG / PNG logos.
 */
function nts_press_media(): array {
	$dir = __DIR__ . '/press';
	$zip = get_temp_dir() . 'netelly-press-kit.zip';
	if ( class_exists( 'ZipArchive' ) && ! is_file( $zip ) ) {
		$z = new ZipArchive();
		$z->open( $zip, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		foreach ( array_merge( glob( $dir . '/netelly-logo-*.svg' ), glob( $dir . '/netelly-logo-*.png' ) ) as $file ) { // No GLOB_BRACE on musl.
			$z->addFile( $file, 'netelly-press-kit/logo/' . basename( $file ) );
		}
		$z->close();
	}
	return array(
		'black' => nts_media( 'logo-black', $dir . '/netelly-logo-black.png', 'Netelly logo (black)' ),
		'white' => nts_media( 'logo-white', $dir . '/netelly-logo-white.png', 'Netelly logo (white)' ),
		'zip'   => is_file( $zip ) ? nts_media( 'press-zip', $zip, 'Netelly press kit' ) : 0,
	);
}

/* --------------------------------------------------------------------------
 * 1. Languages (Polylang)
 * ----------------------------------------------------------------------- */
WP_CLI::log( '1/8 Languages' );
update_option( 'timezone_string', 'Asia/Tokyo' );
// News URLs are /news/{id}/ (pages and works keep their own slugs).
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/news/%post_id%/' );
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
 * 6b. Contact forms (Snow Monkey Forms; one form per language)
 * ----------------------------------------------------------------------- */
WP_CLI::log( '6b Contact forms' );
$forms = array();
if ( post_type_exists( 'snow-monkey-forms' ) ) {
	$map = (array) get_option( 'netelly_seed_map', array() );
	foreach ( array( 'ja', 'en' ) as $lang ) {
		$d    = nts_form_data( $lang );
		$id   = (int) ( $map[ 'form:' . $lang ] ?? 0 );
		$args = array(
			'post_type'    => 'snow-monkey-forms',
			'post_status'  => 'publish',
			'post_title'   => $d['title'] . ( 'en' === $lang ? ' (EN)' : '' ),
			'post_content' => nts_form_content( $d ),
		);
		if ( $id && get_post( $id ) ) {
			$args['ID'] = $id;
			wp_update_post( wp_slash( $args ) );
		} else {
			$id = (int) wp_insert_post( wp_slash( $args ) );
		}
		foreach ( $d['meta'] as $k => $v ) {
			update_post_meta( $id, $k, $v );
		}
		$map[ 'form:' . $lang ] = $id;
		$forms[ $lang ]         = $id;
	}
	update_option( 'netelly_seed_map', $map, false );
}

/* --------------------------------------------------------------------------
 * 7. Page fields + site settings (per language)
 * ----------------------------------------------------------------------- */
WP_CLI::log( '7/8 Page fields & site settings' );
foreach ( array( 'ja', 'en' ) as $lang ) {
	$data = nts_lang_data( $lang, $purl, $works );
	foreach ( $data['pages'] as $key => $groups ) {
		foreach ( $groups as $group => $values ) {
			if ( 'page_contact' === $group ) {
				$values['form'] = $forms[ $lang ] ?? '';
			}
			if ( 'page_press' === $group ) {
				$ja                 = 'ja' === $lang;
				$pm                 = nts_press_media();
				$fmt                = 'PNG / 2400×523';
				$values['kit_file'] = $pm['zip'];
				$values['logos']    = array(
					array( 'name' => $ja ? 'ロゴ（黒）' : 'Logo (black)', 'preview' => $pm['black'], 'file' => $pm['black'], 'format' => $fmt, 'dark' => 0 ),
					array( 'name' => $ja ? 'ロゴ（白）' : 'Logo (white)', 'preview' => $pm['white'], 'file' => $pm['white'], 'format' => $fmt, 'dark' => 1 ),
				);
				$cat                = get_category_by_slug( $ja ? 'press' : 'press-en' );
				$values['rel_link'] = $cat ? nts_link( $ja ? 'プレスリリース一覧 →' : 'All press releases →', get_category_link( $cat ) ) : '';
			}
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
if ( ! function_exists( 'save_mod_rewrite_rules' ) ) {
	require_once ABSPATH . 'wp-admin/includes/misc.php'; // Writes .htaccess on Apache (e.g. shared hosting).
}
flush_rewrite_rules( true );
// Polylang registers /en/ rules only once the languages exist, i.e. on the next request.
update_option( 'netelly_flush_rewrite', 1 );
WP_CLI::success( 'Seeded: pages ' . count( $pages ) . ' × 2, works ' . count( $works ) . ' × 2, news ' . count( NTS_NEWS ) . ' × 2, positions ' . count( NTS_POSITIONS ) . ' × 2.' );

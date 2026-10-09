<?php
/**
 * Search / AI-search layer: meta description, Open Graph + X cards, JSON-LD
 * (Organization, WebSite, Person + ProfilePage for the CEO, NewsArticle,
 * works, breadcrumbs) and /llms.txt. Everything comes from editable fields.
 *
 * Entity setup: one Organization (#organization) and one Person (#person, the CEO)
 * with stable @ids on every page; the CEO message page is the Person's ProfilePage
 * (entity home), and its visible PROFILE block matches the structured data.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plain text: strip tags, join Japanese lines without spaces, collapse whitespace.
 */
function netelly_plain( string $text, int $max = 0 ): string {
	$text = preg_replace( '/(?<=[^\x00-\x7F])\s+(?=[^\x00-\x7F])/u', '', wp_strip_all_tags( $text ) );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', (string) $text ) );
	return $max ? wp_html_excerpt( $text, $max, '…' ) : $text;
}

/**
 * Page description: news → excerpt/body, work → synopsis, CEO page → bio,
 * otherwise サイト設定「検索結果の説明文」.
 */
function netelly_meta_description(): string {
	$text = '';
	if ( is_singular( 'post' ) ) {
		$text = has_excerpt() ? get_the_excerpt() : (string) get_post_field( 'post_content', get_queried_object_id() );
	} elseif ( is_singular( 'work' ) ) {
		$id   = get_queried_object_id();
		$text = netelly_field( 'synopsis_lead', $id ) . ' ' . netelly_field( 'synopsis', $id );
	} elseif ( is_page_template( 'page-message.php' ) ) {
		$name = (string) netelly_opt( 'msg_name' );
		$en   = ucwords( strtolower( (string) netelly_opt( 'msg_name_en' ) ) );
		$role = (string) netelly_opt( 'msg_signature_role' );
		$bio  = (string) netelly_opt( 'ceo_bio' );
		$sep  = 'en' === netelly_lang() ? '. ' : '。';
		$text = $name . ( $en && 0 !== strcasecmp( $en, $name ) ? '（' . $en . '）' : '' ) . ' — ' . rtrim( $role, '.。' ) . $sep;
		// Skip a bio that only repeats the title.
		if ( $bio && false === strpos( netelly_plain( $bio ), netelly_plain( $role ) ) ) {
			$text .= $bio;
		}
	}
	$text = netelly_plain( (string) $text, 120 );
	return '' !== $text ? $text : netelly_plain( (string) netelly_opt( 'meta_description' ), 120 );
}

/**
 * Share image URL for the current view.
 */
function netelly_share_image(): string {
	$id = 0;
	if ( is_singular( 'work' ) ) {
		$wid = get_queried_object_id();
		$id  = (int) ( netelly_field( 'key_visual', $wid ) ?: netelly_field( 'key_art', $wid ) );
	} elseif ( is_page_template( 'page-message.php' ) ) {
		$id = (int) netelly_opt( 'msg_photo' );
	} elseif ( is_singular() && has_post_thumbnail() ) {
		$id = (int) get_post_thumbnail_id();
	}
	if ( ! $id ) {
		$id = (int) netelly_opt( 'ogp_image' );
	}
	$url = $id ? wp_get_attachment_image_url( $id, 'large' ) : '';
	return $url ? $url : NETELLY_URI . '/assets/images/ogp-default.png';
}

/**
 * Canonical URL of the current view (matches WordPress' rel=canonical on singular pages).
 */
function netelly_current_url(): string {
	if ( is_singular() ) {
		return (string) wp_get_canonical_url();
	}
	if ( is_post_type_archive( 'work' ) ) {
		return (string) get_post_type_archive_link( 'work' );
	}
	if ( is_category() ) {
		return (string) get_category_link( get_queried_object_id() );
	}
	if ( is_home() ) {
		return (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	return netelly_home_url();
}

// Home page <title> from サイト設定 › SEO・SNS共有 (company name + what it does).
add_filter(
	'pre_get_document_title',
	static function ( $title ) {
		if ( is_front_page() && netelly_opt( 'seo_home_title' ) ) {
			return esc_html( (string) netelly_opt( 'seo_home_title' ) );
		}
		return $title;
	}
);

// Title: the CEO page also carries the name, so a name search matches the page title.
add_filter(
	'document_title_parts',
	static function ( $parts ) {
		if ( is_page_template( 'page-message.php' ) && netelly_opt( 'msg_name' ) ) {
			$parts['title'] = sprintf( '%s｜%s %s', $parts['title'], netelly_opt( 'msg_name' ), netelly_opt( 'msg_role' ) );
		}
		return $parts;
	},
	20
);

// Meta description + Open Graph + X card.
add_action(
	'wp_head',
	static function () {
		$desc  = netelly_meta_description();
		$title = wp_get_document_title();
		$en    = 'en' === netelly_lang();
		$tags  = array(
			array( 'name', 'description', $desc ),
			array( 'property', 'og:site_name', (string) ( netelly_opt( 'company_name' ) ?: get_bloginfo( 'name' ) ) ),
			array( 'property', 'og:type', is_singular( 'post' ) ? 'article' : ( is_page_template( 'page-message.php' ) ? 'profile' : 'website' ) ),
			array( 'property', 'og:title', $title ),
			array( 'property', 'og:description', $desc ),
			array( 'property', 'og:url', netelly_current_url() ),
			array( 'property', 'og:image', netelly_share_image() ),
			array( 'property', 'og:locale', $en ? 'en_US' : 'ja_JP' ),
			array( 'property', 'og:locale:alternate', $en ? 'ja_JP' : 'en_US' ),
			array( 'name', 'twitter:card', 'summary_large_image' ),
		);
		foreach ( $tags as $t ) {
			if ( '' !== (string) $t[2] ) {
				printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $t[0] ), esc_attr( $t[1] ), esc_attr( (string) $t[2] ) );
			}
		}
	},
	3
);

/**
 * JSON-LD graph for the current view.
 */
function netelly_schema_graph(): array {
	$home     = trailingslashit( (string) get_option( 'home' ) ); // Same @ids on JP and EN pages.
	$lang     = 'en' === netelly_lang() ? 'en' : 'ja';
	$org_id   = $home . '#organization';
	$site_id  = $home . '#website';
	$ceo_page = netelly_page_url( 'message' );
	$ceo_id   = $home . '#ceo';
	$url      = netelly_current_url();

	$socials = wp_list_pluck( netelly_socials(), 'url' );
	$extra   = array_filter( wp_list_pluck( array_filter( (array) netelly_opt( 'org_same_as' ), 'is_array' ), 'url' ) );
	$org     = array_filter(
		array(
			'@type'         => 'Organization',
			'@id'           => $org_id,
			'name'          => (string) netelly_opt( 'company_name', 'ja' ),
			'alternateName' => array_values( array_unique( array_filter( array( (string) netelly_opt( 'company_name', 'en' ), 'Netelly', 'ネテリー' ) ) ) ),
			'url'           => $home,
			'logo'          => NETELLY_URI . '/assets/images/logo-netelly.png',
			'description'   => netelly_plain( (string) netelly_opt( 'meta_description' ) ),
			'foundingDate'  => (string) netelly_opt( 'org_founding_date' ),
			'address'       => netelly_plain( (string) netelly_opt( 'company_address' ) ),
			'email'         => (string) netelly_opt( 'contact_email' ),
			'sameAs'        => array_values( array_unique( array_merge( $socials, $extra ) ) ),
			'employee'      => array( '@id' => $ceo_id ),
		)
	);

	$ceo_links = array_filter( wp_list_pluck( array_filter( (array) netelly_opt( 'ceo_links' ), 'is_array' ), 'url' ) );
	$photo     = (int) netelly_opt( 'msg_photo' );
	$person    = array_filter(
		array(
			'@type'            => 'Person',
			'@id'              => $ceo_id,
			'name'             => (string) netelly_opt( 'msg_name', 'ja' ),
			'alternateName'    => array_values( array_unique( array_filter( array( str_replace( array( ' ', '　' ), '', (string) netelly_opt( 'msg_name', 'ja' ) ), ucwords( strtolower( (string) netelly_opt( 'msg_name_en', 'ja' ) ) ), (string) netelly_opt( 'ceo_name_kana', 'ja' ) ) ) ) ),
			'jobTitle'         => (string) netelly_opt( 'msg_signature_role' ),
			'description'      => netelly_plain( (string) netelly_opt( 'ceo_bio' ) ),
			'image'            => $photo ? wp_get_attachment_image_url( $photo, 'large' ) : '',
			'url'              => $ceo_page,
			'worksFor'         => array( '@id' => $org_id ),
			'mainEntityOfPage' => $ceo_page,
			'sameAs'           => array_values( $ceo_links ),
		)
	);

	$graph = array(
		$org,
		array(
			'@type'      => 'WebSite',
			'@id'        => $site_id,
			'url'        => $home,
			'name'       => 'Netelly',
			'inLanguage' => $lang,
			'publisher'  => array( '@id' => $org_id ),
		),
		$person,
	);

	// Page node (ProfilePage on the CEO message page).
	$page = array(
		'@type'      => is_page_template( 'page-message.php' ) ? array( 'WebPage', 'ProfilePage' ) : 'WebPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => html_entity_decode( wp_get_document_title(), ENT_QUOTES, 'UTF-8' ),
		'inLanguage' => $lang,
		'isPartOf'   => array( '@id' => $site_id ),
		'about'      => array( '@id' => $org_id ),
	);
	if ( is_page_template( 'page-message.php' ) ) {
		$page['mainEntity'] = array( '@id' => $ceo_id );
		$page['about']      = array( '@id' => $ceo_id );
	}

	// Breadcrumbs (all but the front page).
	if ( ! is_front_page() ) {
		$crumbs = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => netelly_t( 'breadcrumb_top' ),
				'item'     => netelly_home_url(),
			),
		);
		if ( is_singular( 'post' ) ) {
			$crumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => netelly_t( 'news_label' ),
				'item'     => netelly_page_url( 'news' ),
			);
		} elseif ( is_singular( 'work' ) ) {
			$crumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => (string) netelly_opt( 'wa_title' ),
				'item'     => (string) get_post_type_archive_link( 'work' ),
			);
		}
		$crumbs[]             = array(
			'@type'    => 'ListItem',
			'position' => count( $crumbs ) + 1,
			'name'     => html_entity_decode( is_singular() ? get_the_title() : wp_get_document_title(), ENT_QUOTES, 'UTF-8' ),
			'item'     => $url,
		);
		$page['breadcrumb'] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $crumbs,
		);
	}
	$graph[] = $page;

	// News article.
	if ( is_singular( 'post' ) ) {
		$pid     = get_queried_object_id();
		$graph[] = array(
			'@type'            => 'NewsArticle',
			'@id'              => $url . '#article',
			'headline'         => html_entity_decode( get_the_title( $pid ), ENT_QUOTES, 'UTF-8' ),
			'datePublished'    => get_the_date( 'c', $pid ),
			'dateModified'     => get_the_modified_date( 'c', $pid ),
			'image'            => array( netelly_share_image() ),
			'inLanguage'       => $lang,
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'author'           => array( '@id' => $org_id ),
			'publisher'        => array( '@id' => $org_id ),
		);
	}

	// Work: series (drama / variety / in development) or movie.
	if ( is_singular( 'work' ) ) {
		$wid   = get_queried_object_id();
		$names = static fn( array $rows ) => array_values(
			array_map(
				static fn( $r ) => array(
					'@type' => 'Person',
					'name'  => (string) $r['name'],
				),
				array_filter( $rows, static fn( $r ) => ! empty( $r['name'] ) )
			)
		);
		$staff     = (array) netelly_field( 'staff', $wid );
		$directors = array_filter( $staff, static fn( $r ) => (bool) preg_match( '/監督|Director/i', (string) ( $r['role'] ?? '' ) ) );
		$year      = (string) netelly_field( 'year', $wid );
		$graph[]   = array_filter(
			array(
				'@type'             => 'film' === netelly_field( 'genre', $wid ) ? 'Movie' : 'TVSeries',
				'@id'               => $url . '#work',
				'name'              => html_entity_decode( get_the_title( $wid ), ENT_QUOTES, 'UTF-8' ),
				'url'               => $url,
				'description'       => netelly_plain( netelly_field( 'synopsis_lead', $wid ) . ' ' . netelly_field( 'synopsis', $wid ), 300 ),
				'image'             => netelly_share_image(),
				'genre'             => netelly_genres()[ (string) netelly_field( 'genre', $wid ) ] ?? '',
				'datePublished'     => preg_match( '/^\d{4}$/', $year ) ? $year : '',
				'inLanguage'        => 'ja',
				'productionCompany' => array( '@id' => $org_id ),
				'actor'             => $names( (array) netelly_field( 'cast', $wid ) ),
				'director'          => $names( $directors ),
			)
		);
	}

	return $graph;
}

add_action(
	'wp_head',
	static function () {
		$json = wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => netelly_schema_graph(),
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
		echo '<script type="application/ld+json">' . str_replace( '</', '<\/', (string) $json ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	4
);

// The users sitemap would publish login names; companies list people on pages instead.
add_filter(
	'wp_sitemaps_add_provider',
	static fn( $provider, $name ) => 'users' === $name ? false : $provider,
	10,
	2
);

/*
 * /llms.txt: a plain-text summary for AI assistants / AI search crawlers
 * (emerging convention; harmless for regular search engines).
 */
add_action(
	'parse_request',
	static function () {
		$path = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( '/llms.txt' !== $path ) {
			return;
		}
		$ja    = static fn( $k ) => netelly_plain( (string) netelly_opt( $k, 'ja' ) );
		$en    = static fn( $k ) => netelly_plain( (string) netelly_opt( $k, 'en' ) );
		$pages = array( 'company', 'message', 'business', 'news', 'careers', 'press', 'contact' );
		$lines = array(
			'# ' . $ja( 'company_name' ) . ' / ' . $en( 'company_name' ),
			'',
			'> ' . $ja( 'meta_description' ),
			'> ' . $en( 'meta_description' ),
			'',
			'## Key facts',
			'- CEO: ' . $ja( 'msg_name' ) . ' (' . ucwords( strtolower( $ja( 'msg_name_en' ) ) ) . ') — ' . $ja( 'msg_signature_role' ),
			'- CEO profile: ' . netelly_page_url( 'message' ),
			'- Founded: ' . $ja( 'org_founding_date' ),
			'- Address: ' . $ja( 'company_address' ),
			'- Contact: ' . $ja( 'contact_email' ),
			'',
			'## Pages (Japanese)',
		);
		foreach ( $pages as $slug ) {
			$id = netelly_find_by_slug( 'page', $slug, 'ja' );
			if ( $id ) {
				$lines[] = '- [' . get_the_title( $id ) . '](' . get_permalink( $id ) . ')';
			}
		}
		$lines[] = '- [' . $ja( 'wa_title' ) . '](' . home_url( '/works/' ) . ')';
		$lines[] = '';
		$lines[] = '## Pages (English)';
		foreach ( $pages as $slug ) {
			$id = netelly_find_by_slug( 'page', $slug, 'en' );
			if ( $id ) {
				$lines[] = '- [' . get_the_title( $id ) . '](' . get_permalink( $id ) . ')';
			}
		}
		$lines[] = '- [' . $en( 'wa_title' ) . '](' . home_url( '/en/works/' ) . ')';
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo implode( "\n", array_map( 'wp_strip_all_tags', $lines ) ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	},
	0
);

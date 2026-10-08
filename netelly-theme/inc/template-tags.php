<?php
/**
 * Template tags shared by the common parts (header, hero, section head, buttons, footer).
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Text with line breaks → escaped HTML with <br>.
 */
function netelly_br( ?string $text ): string {
	return nl2br( esc_html( (string) $text ), false );
}

/**
 * Wraps a trailing arrow (→ / ↗) of a label in a span so it can move on hover.
 */
function netelly_label( string $label ): string {
	if ( preg_match( '/^(.*?)\s*([→↗])$/u', $label, $m ) ) {
		return esc_html( $m[1] ) . ' <span class="arrow" aria-hidden="true">' . $m[2] . '</span>';
	}
	return esc_html( $label );
}

/**
 * True when a string is Latin-only (design sets those in Archivo, e.g. "For Creators").
 */
function netelly_is_latin( string $text ): bool {
	return (bool) preg_match( '/^[\x20-\x7E’‘“”–—·]+$/u', $text );
}

/**
 * HTML attributes for a link, adding target/rel + screen-reader note for external URLs.
 *
 * @param string $url   URL.
 * @param bool   $blank Force new tab.
 */
function netelly_link_attrs( string $url, bool $blank = false ): string {
	$attrs = ' href="' . esc_url( $url ) . '"';
	if ( $blank || netelly_is_external( $url ) ) {
		$attrs .= ' target="_blank" rel="noopener"';
	}
	return $attrs;
}

/**
 * Screen-reader suffix for links opening a new tab.
 */
function netelly_new_tab_note( string $url, bool $blank = false ): string {
	return ( $blank || netelly_is_external( $url ) ) ? '<span class="screen-reader-text">' . esc_html( netelly_t( 'new_tab' ) ) . '</span>' : '';
}

/**
 * Button.
 *
 * @param string $label   Label (a trailing → becomes the animated arrow).
 * @param string $url     URL.
 * @param string $variant primary|outline.
 * @param array  $args    { blank: bool, class: string }.
 */
function netelly_button( string $label, string $url, string $variant = 'primary', array $args = array() ): string {
	if ( '' === $label || '' === $url ) {
		return '';
	}
	$blank = ! empty( $args['blank'] );
	return sprintf(
		'<a class="btn btn--%1$s %2$s"%3$s><span class="btn__label">%4$s</span>%5$s</a>',
		esc_attr( $variant ),
		esc_attr( $args['class'] ?? '' ),
		netelly_link_attrs( $url, $blank ),
		netelly_label( $label ),
		netelly_new_tab_note( $url, $blank )
	);
}

/**
 * Button from an ACF link field value.
 *
 * @param mixed  $link    ACF link array.
 * @param string $variant primary|outline.
 * @param array  $args    See netelly_button().
 */
function netelly_link_button( $link, string $variant = 'primary', array $args = array() ): string {
	if ( ! is_array( $link ) || empty( $link['url'] ) ) {
		return '';
	}
	$args['blank'] = ! empty( $args['blank'] ) || '_blank' === ( $link['target'] ?? '' );
	return netelly_button( (string) $link['title'], (string) $link['url'], $variant, $args );
}

/**
 * Small mono text link (e.g. "ニュース一覧 →").
 *
 * @param mixed  $link  ACF link array or [ title, url ].
 * @param string $class Extra class.
 */
function netelly_text_link( $link, string $class = '' ): string {
	if ( ! is_array( $link ) ) {
		return '';
	}
	$title = $link['title'] ?? ( $link[0] ?? '' );
	$url   = $link['url'] ?? ( $link[1] ?? '' );
	if ( '' === $title || '' === $url ) {
		return '';
	}
	$blank = '_blank' === ( $link['target'] ?? '' );
	return sprintf( '<a class="text-link %1$s"%2$s>%3$s%4$s</a>', esc_attr( $class ), netelly_link_attrs( $url, $blank ), netelly_label( $title ), netelly_new_tab_note( $url, $blank ) );
}

/**
 * Logo (inline SVG, currentColor).
 *
 * @param string $class CSS class.
 */
function netelly_logo( string $class = '' ): string {
	// Archivo 125% / 600 wordmark (client decision; tools/build-wordmark.py). The supplied italic SVG stays in assets/.
	return netelly_svg(
		'netelly-wordmark',
		array(
			'class'       => $class,
			'role'        => 'img',
			'aria-label'  => 'NETELLY',
			'focusable'   => 'false',
		)
	);
}

/**
 * Menu tree for a location in the current language.
 *
 * @return array<int,array{title:string,url:string,external:bool,current:bool,children:array}>
 */
function netelly_menu( string $location ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return array();
	}
	$path  = (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$home  = (string) wp_parse_url( netelly_home_url(), PHP_URL_PATH );
	$nodes = array();
	foreach ( $items as $item ) {
		$item_path = (string) wp_parse_url( $item->url, PHP_URL_PATH );
		$external  = in_array( 'is-external', (array) $item->classes, true );
		$nodes[ $item->ID ] = array(
			'title'    => $item->title,
			'url'      => $item->url,
			'external' => $external,
			// Section match: /works/ is current on /works/foo/, /news/ on /news/123/.
			'current'  => ! $external && '#' !== $item->url && $item_path && trailingslashit( $item_path ) !== trailingslashit( $home ) && str_starts_with( trailingslashit( $path ), trailingslashit( $item_path ) ),
			'parent'   => (int) $item->menu_item_parent,
			'children' => array(),
		);
	}
	$tree = array();
	foreach ( $nodes as $id => &$node ) {
		if ( $node['parent'] && isset( $nodes[ $node['parent'] ] ) ) {
			$nodes[ $node['parent'] ]['children'][] = &$node;
		} else {
			$tree[] = &$node;
		}
	}
	unset( $node );
	return $tree;
}

/**
 * Menu link markup (adds ↗ for external, aria-current for the current section).
 *
 * @param array  $item  Node from netelly_menu().
 * @param string $class Link class.
 */
function netelly_menu_link( array $item, string $class = '' ): string {
	return sprintf(
		'<a class="%1$s%2$s"%3$s%4$s>%5$s%6$s%7$s</a>',
		esc_attr( $class ),
		$item['current'] ? ' is-current' : '',
		netelly_link_attrs( $item['url'], $item['external'] ),
		$item['current'] ? ' aria-current="page"' : '',
		esc_html( $item['title'] ),
		$item['external'] ? '&nbsp;<span class="ext" aria-hidden="true">↗</span>' : '', // nbsp: the arrow never wraps alone.
		$item['external'] ? '<span class="screen-reader-text">' . esc_html( netelly_t( 'new_tab' ) ) . '</span>' : ''
	);
}

/**
 * JP / EN switcher.
 */
function netelly_lang_switch( string $class = '' ): string {
	$links = netelly_lang_links();
	$cur   = netelly_lang();
	$parts = array();
	foreach ( array( 'ja' => 'JP', 'en' => 'EN' ) as $lang => $label ) {
		if ( $lang === $cur || empty( $links[ $lang ] ) ) {
			$parts[] = sprintf( '<span class="lang-switch__item is-current" lang="%1$s" aria-current="true">%2$s</span>', esc_attr( $lang ), esc_html( $label ) );
		} else {
			$parts[] = sprintf( '<a class="lang-switch__item" href="%1$s" lang="%2$s" hreflang="%2$s">%3$s</a>', esc_url( $links[ $lang ] ), esc_attr( $lang ), esc_html( $label ) );
		}
	}
	return sprintf( '<div class="lang-switch %1$s" role="group" aria-label="%2$s">%3$s</div>', esc_attr( $class ), esc_attr( netelly_t( 'lang_switch' ) ), implode( '<span aria-hidden="true"> / </span>', $parts ) );
}

/**
 * Header variant for the current view: 'dark' when the page starts with a black hero.
 */
function netelly_header_variant(): string {
	return ( is_singular( 'post' ) || is_404() || is_search() ) ? 'light' : 'dark';
}

/**
 * Page hero data for the current view.
 *
 * @return array{en:string,title:string}|null
 */
function netelly_hero_data(): ?array {
	if ( is_post_type_archive( 'work' ) ) {
		return array(
			'en'    => (string) netelly_opt( 'wa_en_title' ),
			'title' => (string) netelly_opt( 'wa_title' ),
		);
	}
	$page_id = 0;
	if ( is_home() || is_category() ) {
		$page_id = (int) get_option( 'page_for_posts' );
		$page_id = $page_id ? netelly_tr_id( $page_id ) : 0;
	} elseif ( is_page() ) {
		$page_id = (int) get_queried_object_id();
	}
	if ( ! $page_id ) {
		return null;
	}
	return array(
		'en'    => (string) netelly_field( 'page_en_title', $page_id ),
		'title' => get_the_title( $page_id ),
	);
}

/**
 * Social links from サイト設定 (labels are brand names).
 *
 * @return array<int,array{label:string,url:string}>
 */
function netelly_socials(): array {
	$out = array();
	foreach ( array( 'youtube' => 'YOUTUBE', 'line' => 'LINE', 'x' => 'X', 'instagram' => 'INSTAGRAM' ) as $key => $label ) {
		$url = (string) netelly_opt( $key . '_url' );
		if ( $url ) {
			$out[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}
	return $out;
}

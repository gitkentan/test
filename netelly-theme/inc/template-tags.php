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
 * Splits text into spans for the scroll-lit statement: one span per Latin word, one per
 * CJK character (index in --i). The readable text is given separately to assistive tech.
 *
 * @return array{html:string,count:int}
 */
function netelly_split_words( string $text ): array {
	$html = '';
	$i    = 0;
	foreach ( preg_split( '/(\s+)/u', trim( $text ), -1, PREG_SPLIT_DELIM_CAPTURE ) as $token ) {
		if ( '' === $token ) {
			continue;
		}
		if ( preg_match( '/^\s+$/u', $token ) ) {
			$html .= str_contains( $token, "\n" ) ? '<br>' : ' ';
			continue;
		}
		// Latin runs stay whole words; everything else (Japanese) goes character by character.
		foreach ( preg_split( '/([\x21-\x7E’‘“”–—]+)/u', $token, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY ) as $part ) {
			$units = preg_match( '/^[\x21-\x7E’‘“”–—]+$/u', $part ) ? array( $part ) : mb_str_split( $part );
			foreach ( $units as $u ) {
				$html .= '<span class="manifesto__w" style="--i:' . $i++ . '">' . esc_html( $u ) . '</span>';
			}
		}
	}
	return array(
		'html'  => $html,
		'count' => $i,
	);
}

/**
 * Colour mode switch: dark (default) / light / prism. fx.js sets html[data-theme] and
 * remembers the choice on the device.
 */
function netelly_theme_switch( string $class = '' ): string {
	$modes = array(
		'dark'  => netelly_t( 'theme_dark' ),
		'light' => netelly_t( 'theme_light' ),
		'prism' => netelly_t( 'theme_prism' ),
	);
	$html = '';
	foreach ( $modes as $mode => $name ) {
		$html .= sprintf( '<button class="theme-switch__btn theme-switch__btn--%1$s" type="button" role="radio" aria-checked="false" data-theme-set="%1$s" aria-label="%2$s" title="%2$s"></button>', esc_attr( $mode ), esc_attr( $name ) );
	}
	return sprintf(
		'<div class="theme-switch %1$s" role="radiogroup" aria-label="%2$s">%3$s<span class="theme-switch__label" aria-hidden="true">DARK / LIGHT / PRISM</span></div>',
		esc_attr( $class ),
		esc_attr( netelly_t( 'theme_label' ) ),
		$html
	);
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
	// Whitespace-insensitive: admin textareas save line breaks as \r\n.
	return (bool) preg_match( '/^[\x20-\x7E’‘“”–—·]+$/u', (string) preg_replace( '/\s+/u', ' ', trim( $text ) ) );
}

/**
 * HTML attributes for a link, adding target/rel + screen-reader note for external URLs.
 *
 * @param string $url   URL.
 * @param bool   $blank Force new tab.
 */
function netelly_link_attrs( string $url, bool $blank = false ): string {
	$url   = netelly_local_url( $url );
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
	$title = esc_html( $item['title'] );
	if ( $item['external'] ) {
		// Keep the last character and ↗ together so the arrow never wraps alone.
		$last  = mb_substr( $item['title'], -1 );
		$title = esc_html( mb_substr( $item['title'], 0, -1 ) ) . '<span class="nowrap">' . esc_html( $last ) . ' <span class="ext" aria-hidden="true">↗</span></span>';
	}
	return sprintf(
		'<a class="%1$s%2$s"%3$s%4$s>%5$s%6$s</a>',
		esc_attr( $class ) . ( netelly_is_latin( $item['title'] ) ? ' is-latin' : '' ),
		$item['current'] ? ' is-current' : '',
		netelly_link_attrs( $item['url'], $item['external'] ),
		$item['current'] ? ' aria-current="page"' : '',
		$title,
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

/**
 * Image box with a fixed aspect ratio. Without an image it renders the grey
 * placeholder (keep the ratio). Inner <img> is for hover/reveal motion.
 *
 * @param int|string $id    Attachment ID (0/'' = placeholder).
 * @param string     $ratio CSS aspect-ratio, e.g. '4/5'.
 * @param array      $args  { size, class, eager (bool), sizes, alt, vt (view-transition name) }.
 */
function netelly_media( $id, string $ratio, array $args = array() ): string {
	$id    = (int) $id;
	$class = trim( 'media ' . ( $args['class'] ?? '' ) . ( $id ? '' : ' is-placeholder' ) );
	$style = 'aspect-ratio:' . $ratio . ';' . ( ! empty( $args['vt'] ) ? 'view-transition-name:' . $args['vt'] . ';' : '' );
	$img   = '';
	if ( $id ) {
		$attr = array(
			'class'    => 'media__img',
			'loading'  => empty( $args['eager'] ) ? 'lazy' : 'eager',
			'decoding' => 'async',
			'sizes'    => $args['sizes'] ?? '(max-width: 768px) 100vw, 50vw',
		);
		if ( ! empty( $args['eager'] ) ) {
			$attr['fetchpriority'] = 'high';
		}
		if ( isset( $args['alt'] ) ) {
			$attr['alt'] = $args['alt'];
		}
		$img = wp_get_attachment_image( $id, $args['size'] ?? 'large', false, $attr );
	}
	return sprintf( '<div class="%1$s" style="%2$s">%3$s</div>', esc_attr( $class ), esc_attr( $style ), $img );
}

/**
 * Genre labels (value => EN caps shown on cards; tab labels come from netelly_t()).
 */
function netelly_genres(): array {
	return array(
		'drama'          => 'DRAMA',
		'variety'        => 'VARIETY',
		'film'           => 'FILM',
		'in_development' => 'IN DEVELOPMENT',
	);
}

/**
 * Card meta line: "DRAMA · 2021".
 */
function netelly_work_meta( int $id ): string {
	$genres = netelly_genres();
	$genre  = (string) netelly_field( 'genre', $id );
	$parts  = array_filter( array( $genres[ $genre ] ?? '', (string) netelly_field( 'year', $id ) ) );
	return implode( ' · ', $parts );
}

/**
 * Whether a work is shown as the COMING SOON card.
 */
function netelly_is_coming_soon( int $id ): bool {
	return 'in_development' === netelly_field( 'genre', $id ) && ! netelly_field( 'key_art', $id );
}

/**
 * Supplied L-mark (decorative).
 */
function netelly_lmark( string $class = '' ): string {
	return netelly_svg(
		'netelly-l-mark',
		array(
			'class'       => $class,
			'aria-hidden' => 'true',
			'focusable'   => 'false',
		)
	);
}

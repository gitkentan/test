<?php
/**
 * Theme supports and front-end cleanup.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'netelly', NETELLY_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
	}
);

// Pages are edited through fields (ページヒーロー + page groups), not the body.
add_action(
	'init',
	static function () {
		remove_post_type_support( 'page', 'editor' );
		remove_post_type_support( 'page', 'comments' );
		remove_post_type_support( 'page', 'thumbnail' );
	}
);

// Classic theme: no emoji script, no generator tag, no block-template skip links.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

// Generate WebP sub-sizes for uploaded JPEG/PNG (README 10: WebP/AVIF + srcset).
add_filter(
	'image_editor_output_format',
	static function ( $formats ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
		return $formats;
	}
);

// <title> for the works archive comes from サイト設定 (per language).
add_filter(
	'document_title_parts',
	static function ( $parts ) {
		if ( is_post_type_archive( 'work' ) ) {
			$title = (string) netelly_opt( 'wa_title' );
			if ( $title ) {
				$parts['title'] = $title;
			}
		}
		return $parts;
	}
);

// Body classes used by header.js / motion.css.
add_filter(
	'body_class',
	static function ( $classes ) {
		if ( is_singular( 'work' ) ) {
			$classes[] = 'page-is-dark';
		}
		return $classes;
	}
);

/*
 * Favicon: symbol mark 1 (一文字), small-size cut. Used until a Site Icon is set
 * in 外観 › カスタマイズ › サイト基本情報 (WordPress then prints its own tags).
 */
add_action(
	'wp_head',
	static function () {
		if ( has_site_icon() ) {
			return;
		}
		$base = NETELLY_URI . '/assets/favicon/';
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $base . 'favicon.svg' ) );
		printf( '<link rel="icon" href="%s" sizes="32x32" type="image/png">' . "\n", esc_url( $base . 'favicon-32.png' ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'apple-touch-icon.png' ) );
	},
	5
);

/*
 * Meta description: news → excerpt, work → synopsis, otherwise サイト設定「検索結果の説明文」.
 */
add_action(
	'wp_head',
	static function () {
		$text = '';
		if ( is_singular( 'post' ) ) {
			$text = has_excerpt() ? get_the_excerpt() : wp_strip_all_tags( (string) get_post_field( 'post_content', get_queried_object_id() ) );
		} elseif ( is_singular( 'work' ) ) {
			$id   = get_queried_object_id();
			$text = trim( netelly_field( 'synopsis_lead', $id ) . ' ' . netelly_field( 'synopsis', $id ) );
		}
		if ( '' === trim( $text ) ) {
			$text = (string) netelly_opt( 'meta_description' );
		}
		$text = preg_replace( '/(?<=[^\x00-\x7F])\s+(?=[^\x00-\x7F])/u', '', wp_strip_all_tags( $text ) ); // No spaces between Japanese lines.
		$text = wp_html_excerpt( preg_replace( '/\s+/u', ' ', $text ), 120, '…' );
		if ( '' !== trim( $text ) ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $text ) );
		}
	},
	3
);

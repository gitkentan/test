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

// Photos (JPEG) are stored as WebP (README 10: WebP/AVIF + srcset). PNG stays PNG so logos /
// press-kit files keep transparency and the format people download.
add_filter(
	'image_editor_output_format',
	static function ( $formats ) {
		$formats['image/jpeg'] = 'image/webp';
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

// Body classes used by header.js / motion.css. The cinematic theme runs every page dark,
// so the components' own dark styles (.is-dark …, .page-is-dark header) apply site-wide.
add_filter(
	'body_class',
	static function ( $classes ) {
		$classes[] = 'is-dark';
		$classes[] = 'page-is-dark';
		return $classes;
	}
);

/*
 * Favicon: the "N" of the NETELLY wordmark on a black tile. Used until a Site Icon is set
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
		// Google's search-result icon prefers ≥48px (multiples of 48).
		printf( '<link rel="icon" href="%s" sizes="192x192" type="image/png">' . "\n", esc_url( $base . 'favicon-192.png' ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'apple-touch-icon.png' ) );
	},
	5
);

// One-time rewrite refresh after the seed (see bin/seed.php), so /en/ URLs work right away.
add_action(
	'init',
	static function () {
		if ( get_option( 'netelly_flush_rewrite' ) ) {
			delete_option( 'netelly_flush_rewrite' );
			flush_rewrite_rules( false );
		}
	},
	99
);

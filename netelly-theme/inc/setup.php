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

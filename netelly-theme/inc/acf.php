<?php
/**
 * ACF / Secure Custom Fields: options pages and accessors.
 *
 * Field groups live in acf-json/ (loaded automatically by ACF Pro and SCF).
 * Options are stored per language: post_id "netelly_ja" / "netelly_en".
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

const NETELLY_OPTION_LANGS = array(
	'ja' => '日本語（JP）',
	'en' => 'English（EN）',
);

add_action(
	'acf/init',
	static function () {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}
		acf_add_options_page(
			array(
				'page_title' => 'サイト設定',
				'menu_title' => 'サイト設定',
				'menu_slug'  => 'netelly-settings',
				'capability' => 'edit_pages',
				'redirect'   => true,
				'position'   => 3,
				'icon_url'   => 'dashicons-admin-site-alt3',
			)
		);
		foreach ( NETELLY_OPTION_LANGS as $lang => $label ) {
			acf_add_options_sub_page(
				array(
					'page_title'  => 'サイト設定 — ' . $label,
					'menu_title'  => $label,
					'menu_slug'   => 'netelly-settings-' . $lang,
					'parent_slug' => 'netelly-settings',
					'capability'  => 'edit_pages',
					'post_id'     => 'netelly_' . $lang,
				)
			);
		}
	}
);

/**
 * Site setting in the current language (falls back to JP when empty).
 *
 * @param string      $name Field name.
 * @param string|null $lang Language slug; current language when null.
 * @return mixed
 */
function netelly_opt( string $name, ?string $lang = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$lang  = $lang ?? netelly_lang();
	$value = get_field( $name, 'netelly_' . $lang );
	if ( ( null === $value || '' === $value || false === $value || array() === $value ) && 'ja' !== $lang ) {
		$value = get_field( $name, 'netelly_ja' );
	}
	return $value;
}

/**
 * Field of a post (current post by default).
 *
 * @param string   $name    Field name.
 * @param int|null $post_id Post ID.
 * @return mixed
 */
function netelly_field( string $name, ?int $post_id = null ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id ?? get_the_ID() ) : null;
}

/**
 * Creators Fund URL (external site; README rule).
 */
function netelly_fund_url(): string {
	$url = (string) netelly_opt( 'fund_url' );
	return $url ? $url : 'https://fund.netelly.co.jp';
}

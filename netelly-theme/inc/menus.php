<?php
/**
 * Menus (外観 › メニュー, per language via Polylang).
 *
 * A custom link whose URL is "#creators-fund" points to the Creators Fund URL
 * from サイト設定 and opens in a new tab with ↗ (README: external fund site).
 * Any other link to another host is treated as external the same way.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

const NETELLY_FUND_PLACEHOLDER = '#creators-fund';

add_action(
	'after_setup_theme',
	static function () {
		register_nav_menus(
			array(
				'primary' => 'ヘッダー（PC・SPメニュー共通）',
				'footer'  => 'フッター（1階層目＝列見出し、2階層目＝リンク）',
			)
		);
	}
);

/**
 * Whether a URL leaves this site.
 */
function netelly_is_external( string $url ): bool {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host;
}

// Resolve the fund placeholder and flag external items (applies to every menu render).
add_filter(
	'wp_setup_nav_menu_item',
	static function ( $item ) {
		if ( is_admin() || empty( $item->url ) ) {
			return $item;
		}
		if ( NETELLY_FUND_PLACEHOLDER === $item->url ) {
			$item->url = netelly_fund_url();
		}
		if ( netelly_is_external( $item->url ) ) {
			$item->target    = '_blank';
			$item->xfn       = 'noopener';
			$item->classes[] = 'is-external';
		}
		return $item;
	}
);

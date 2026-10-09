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

// Earlier addresses of this same site (the pre-launch test domain). Links saved with them
// stay in the same tab and are rewritten to the current domain.
const NETELLY_OLD_HOSTS = array( 'new.netelly.co.jp' );

/**
 * Whether a host is this site: the current domain, its www form or an earlier address.
 *
 * @param string $host Host name.
 */
function netelly_is_own_host( string $host ): bool {
	$home = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$host = strtolower( $host );
	return preg_replace( '/^www\./', '', $host ) === preg_replace( '/^www\./', '', $home ) || in_array( $host, NETELLY_OLD_HOSTS, true );
}

/**
 * Point a link saved with an earlier address of this site at the current domain.
 *
 * @param string $url URL.
 */
function netelly_local_url( string $url ): string {
	$parts = wp_parse_url( $url );
	if ( empty( $parts['host'] ) || ! netelly_is_own_host( $parts['host'] ) ) {
		return $url;
	}
	$home = untrailingslashit( home_url() );
	if ( (string) wp_parse_url( $home, PHP_URL_HOST ) === $parts['host'] ) {
		return $url;
	}
	return preg_replace( '#^(?:[a-z]+:)?//[^/?\#]+#i', $home, $url );
}

/**
 * Whether a URL leaves this site.
 */
function netelly_is_external( string $url ): bool {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return $host && ! netelly_is_own_host( $host );
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
		$item->url = netelly_local_url( $item->url );
		if ( netelly_is_external( $item->url ) ) {
			$item->target    = '_blank';
			$item->xfn       = 'noopener';
			$item->classes[] = 'is-external';
		}
		return $item;
	}
);

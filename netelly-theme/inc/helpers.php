<?php
/**
 * Template helpers.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG from assets/images (metadata-stripped copies).
 *
 * @param string $name  File name without extension (e.g. 'netelly-logo').
 * @param array  $attrs Extra attributes for the <svg> element.
 */
function netelly_svg( string $name, array $attrs = array() ): string {
	static $cache = array();
	if ( ! isset( $cache[ $name ] ) ) {
		$file           = NETELLY_DIR . '/assets/images/' . sanitize_file_name( $name ) . '.min.svg';
		$cache[ $name ] = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
	$attr = '';
	foreach ( $attrs as $k => $v ) {
		$attr .= sprintf( ' %s="%s"', esc_attr( $k ), esc_attr( $v ) );
	}
	return preg_replace( '/^<svg/', '<svg' . $attr, $cache[ $name ], 1 );
}

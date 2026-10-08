<?php
/**
 * Assets: Vite manifest (assets/dist) + self-hosted fonts.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read the Vite manifest once.
 */
function netelly_manifest(): array {
	static $manifest = null;
	if ( null === $manifest ) {
		$file     = NETELLY_DIR . '/assets/dist/.vite/manifest.json';
		$manifest = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
	return $manifest;
}

/**
 * Built file URL for a Vite entry (path relative to src/).
 */
function netelly_asset( string $entry ): ?string {
	$m = netelly_manifest();
	return isset( $m[ $entry ]['file'] ) ? NETELLY_URI . '/assets/dist/' . $m[ $entry ]['file'] : null;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'netelly-fonts', NETELLY_URI . '/assets/fonts/fonts.css', array(), NETELLY_VERSION );

		$css = netelly_asset( 'css/main.css' );
		if ( $css ) {
			wp_enqueue_style( 'netelly', $css, array( 'netelly-fonts' ), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- hashed file name.
		}
		$js = netelly_asset( 'js/main.js' );
		if ( $js ) {
			wp_enqueue_script_module( 'netelly', $js, array(), null );
		}

		// The theme styles everything itself; drop core front-end CSS it does not use.
		if ( ! is_singular( 'post' ) ) {
			wp_dequeue_style( 'wp-block-library' );
		}
		wp_dequeue_style( 'classic-theme-styles' );
	},
	20
);

// theme.json presets are for the editor only; the front end uses tokens.css.
add_action(
	'init',
	static function () {
		if ( is_admin() ) {
			return;
		}
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	}
);

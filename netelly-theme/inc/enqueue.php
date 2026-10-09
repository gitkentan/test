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
		$css = netelly_asset( 'css/main.css' );
		if ( $css ) {
			wp_enqueue_style( 'netelly', $css, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- hashed file name.
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

/*
 * Inline head script (runs before first paint):
 * - html.js            : CSS may hide reveal targets until motion.js shows them.
 * - html.intro(-full|-short) on the top page (README 1): full sequence once per
 *   session (sessionStorage "netelly_intro_seen"), shortened afterwards.
 * Nothing is hidden when the visitor prefers reduced motion.
 * - html[data-theme]   : dark (default), light or prism, as chosen with the header switch (fx.js).
 */
add_action(
	'wp_head',
	static function () {
		$front = is_front_page() ? 'true' : 'false';
		$js    = "(function(d){var h=d.documentElement;var t=null;try{t=localStorage.getItem('netelly-theme')}catch(e){}h.setAttribute('data-theme',t==='light'||t==='prism'?t:'dark');h.classList.add('js');setTimeout(function(){if(!h.classList.contains('motion-ready')){h.classList.remove('js','intro','intro-full','intro-short');}},3000);try{if(matchMedia('(prefers-reduced-motion: reduce)').matches){h.classList.add('reduced');return;}if({$front}){h.classList.add('intro',sessionStorage.getItem('netelly_intro_seen')?'intro-short':'intro-full');}}catch(e){}})(document);";
		echo '<script>' . $js . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	1
);

/*
 * Fonts (README 10): the @font-face list is large (Japanese unicode-range slices), so it is
 * loaded without blocking the first paint; text shows in the fallback until the woff2 arrive
 * (font-display: swap).
 */
add_action(
	'wp_head',
	static function () {
		$url = esc_url( NETELLY_URI . '/assets/fonts/fonts.min.css?ver=' . NETELLY_VERSION );
		echo '<link rel="preload" href="' . $url . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.EnqueuedResources
		echo '<noscript><link rel="stylesheet" href="' . $url . '"></noscript>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.EnqueuedResources
	},
	2
);

// Hero display face (Instrument Serif) is preloaded on the top page so the headline doesn't swap late.
add_action(
	'wp_head',
	static function () {
		if ( ! is_front_page() || ! netelly_is_latin( str_replace( "\n", ' ', (string) netelly_opt( 'hero_copy' ) ) ) ) {
			return;
		}
		foreach ( array( 'instrument-serif-400-latin.woff2', 'instrument-serif-400-italic-latin.woff2' ) as $f ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( NETELLY_URI . '/assets/fonts/' . $f ) );
		}
	},
	2
);

/*
 * PRISM light fields (shown only in that theme), and for the light theme: drop the site-wide
 * dark classes before the body paints (the work detail page keeps its black ground).
 */
add_action(
	'wp_body_open',
	static function () {
		echo '<div class="prism" aria-hidden="true"><div class="prism__field prism__field--a"></div><div class="prism__field prism__field--b"></div></div>' . "\n";
		echo "<script>if(document.documentElement.getAttribute('data-theme')==='light'){var b=document.body;b.classList.remove('is-dark');if(!b.classList.contains('single-work')){b.classList.remove('page-is-dark');}}</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	1
);

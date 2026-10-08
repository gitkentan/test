<?php
/**
 * Snow Monkey Forms integration (contact page, design #p11 / README 9).
 *
 * - 必須 / 任意 badges next to item labels, derived from each control's validations.
 * - Validation messages from the theme strings (言語 › 翻訳).
 * - Plugin CSS is replaced by the theme's; plugin JS (and reCAPTCHA) only on pages with a form.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add the required / optional badge to an item label.
 */
add_filter(
	'render_block_snow-monkey-forms/item',
	static function ( string $html, array $block ): string {
		$control = $block['innerBlocks'][0]['attrs'] ?? null;
		if ( null === $control || false === strpos( $html, 'smf-item__label__text' ) ) {
			return $html;
		}
		$rules    = json_decode( (string) ( $control['validations'] ?? '{}' ), true );
		$required = ! empty( $rules['required'] );
		$badge    = sprintf(
			'<span class="smf-badge%s">%s</span>',
			$required ? ' smf-badge--required' : '',
			esc_html( netelly_t( $required ? 'form_required' : 'form_optional' ) )
		);
		return (string) preg_replace( '#(<span class="smf-item__label__text">.*?</span>)#s', '$1' . $badge, $html, 1 );
	},
	10,
	2
);

/**
 * Server-side validation messages (shown with ⚠ by CSS, same as the client-side ones).
 */
add_filter(
	'snow_monkey_forms/validator/error_message',
	static function ( $message, $validation ) {
		if ( 'required' === $validation ) {
			return netelly_t( 'form_error_required' );
		}
		if ( 'email' === $validation ) {
			return netelly_t( 'form_error_email' );
		}
		return $message;
	},
	10,
	2
);

/**
 * Whether the current view renders a form (contact page template).
 */
function netelly_has_form(): bool {
	return is_page_template( 'page-contact.php' );
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		// The theme styles the form itself (src/css/components/form.css).
		wp_dequeue_style( 'snow-monkey-forms' );
		wp_dequeue_style( 'sass-basis-core' );
		if ( ! netelly_has_form() ) {
			wp_dequeue_script( 'snow-monkey-forms' );
			wp_dequeue_script( 'google-recaptcha' );
			wp_dequeue_script( 'snow-monkey-forms@recaptcha' );
		}
	},
	100
);

// Block-asset hook used by the plugin for its CSS.
add_action(
	'enqueue_block_assets',
	static function () {
		if ( ! is_admin() ) {
			wp_dequeue_style( 'snow-monkey-forms' );
			wp_dequeue_style( 'sass-basis-core' );
		}
	},
	100
);

// Per-block styles of the plugin (inlined on demand) would override the theme on the front end.
add_filter(
	'register_block_type_args',
	static function ( array $args, string $name ): array {
		if ( ! is_admin() && 0 === strpos( $name, 'snow-monkey-forms/' ) ) {
			unset( $args['style'], $args['style_handles'] );
		}
		return $args;
	},
	10,
	2
);

// Core heading / paragraph styles requested by the form's complete screen are not needed.
add_action(
	'wp_footer',
	static function () {
		if ( netelly_has_form() ) {
			wp_dequeue_style( 'wp-block-heading' );
			wp_dequeue_style( 'wp-block-paragraph' );
		}
	},
	1
);

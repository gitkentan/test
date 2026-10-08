<?php
/**
 * Plugin Name: Netelly local mail log
 * Description: wp-env only (mapped in .wp-env.json). The local containers cannot send mail,
 *              so wp_mail() writes each message to wp-content/uploads/netelly-mail.log instead.
 *
 * @package netelly
 */

if ( 'local' !== wp_get_environment_type() ) {
	return;
}

add_filter(
	'pre_wp_mail',
	static function ( $short, array $atts ) {
		$to   = implode( ', ', (array) $atts['to'] );
		$line = sprintf( "==== %s\nTo: %s\nSubject: %s\nHeaders: %s\n\n%s\n\n", wp_date( 'Y-m-d H:i:s' ), $to, $atts['subject'], implode( ' | ', (array) $atts['headers'] ), $atts['message'] );
		file_put_contents( WP_CONTENT_DIR . '/uploads/netelly-mail.log', $line, FILE_APPEND ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		return true;
	},
	10,
	2
);

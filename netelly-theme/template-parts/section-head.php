<?php
/**
 * Section heading: mono label + large heading + optional link, 1px rule below.
 *
 * Args: label, heading, link (ACF link array), tag (h2), id.
 *
 * @package netelly
 */

$label   = (string) ( $args['label'] ?? '' );
$heading = (string) ( $args['heading'] ?? '' );
$tag     = in_array( $args['tag'] ?? 'h2', array( 'h1', 'h2', 'h3' ), true ) ? ( $args['tag'] ?? 'h2' ) : 'h2';
?>
<div class="section-head"<?php echo ! empty( $args['id'] ) ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>>
	<div class="section-head__text">
		<?php if ( $label ) : ?>
			<p class="section-head__label"><?php echo esc_html( $label ); ?></p>
		<?php endif; ?>
		<<?php echo esc_html( $tag ); ?> class="section-head__heading"><span class="mask"><span><?php echo netelly_br( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></span></<?php echo esc_html( $tag ); ?>>
	</div>
	<?php echo netelly_text_link( $args['link'] ?? null, 'section-head__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

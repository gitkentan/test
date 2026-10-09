<?php
/**
 * Section heading: large English label with the (Japanese) heading set small under it,
 * optional link, 1px rule below. Without a label the heading itself is set large.
 *
 * Args: label, heading, link (ACF link array), tag (h2), id.
 *
 * @package netelly
 */

$label   = (string) ( $args['label'] ?? '' );
$heading = (string) ( $args['heading'] ?? '' );
$tag     = in_array( $args['tag'] ?? 'h2', array( 'h1', 'h2', 'h3' ), true ) ? ( $args['tag'] ?? 'h2' ) : 'h2';
?>
<div class="section-head<?php echo $label ? ' section-head--en' : ''; ?>"<?php echo ! empty( $args['id'] ) ? ' id="' . esc_attr( $args['id'] ) . '"' : ''; ?>>
	<div class="section-head__text">
		<<?php echo esc_html( $tag ); ?> class="section-head__heading">
			<?php if ( $label ) : ?>
				<span class="section-head__en" lang="en"><span class="mask"><span><?php echo esc_html( $label ); ?></span></span></span>
				<?php // English pages: skip the small line when it only repeats the label ("NEWS" / "News"). ?>
				<?php if ( $heading && 0 !== strcasecmp( trim( $heading ), trim( $label ) ) ) : ?>
					<span class="section-head__ja"><?php echo netelly_br( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				<?php endif; ?>
			<?php else : ?>
				<span class="mask"><span><?php echo netelly_br( $heading ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></span>
			<?php endif; ?>
		</<?php echo esc_html( $tag ); ?>>
	</div>
	<?php echo netelly_text_link( $args['link'] ?? null, 'section-head__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

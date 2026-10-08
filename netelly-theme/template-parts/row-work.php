<?php
/**
 * ORIGINALS index row: large key art + number, meta, title, logline and link.
 * Rows alternate sides (CSS), so a short line-up reads as a curated index.
 * Key art keeps view-transition-name work-{id} for the morph into the work page.
 *
 * Args: id (int), index (int).
 *
 * @package netelly
 */

$wid   = (int) ( $args['id'] ?? get_the_ID() );
$num   = sprintf( '%02d', (int) ( $args['index'] ?? 1 ) );
$soon  = netelly_is_coming_soon( $wid );
$lead  = (string) netelly_field( 'synopsis_lead', $wid );
$note  = (string) ( netelly_field( 'card_note', $wid ) ?: netelly_field( 'format', $wid ) );
$plat  = (string) netelly_field( 'platform', $wid );
?>
<a class="work-row<?php echo $soon ? ' is-soon' : ''; ?>" href="<?php echo esc_url( get_permalink( $wid ) ); ?>">
	<div class="work-row__visual">
		<?php if ( $soon ) : ?>
			<div class="media card-work__soon" style="aspect-ratio:4/5">
				<?php echo netelly_lmark( 'card-work__lmark' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="card-work__soon-label"><?php echo esc_html( netelly_t( 'coming_soon' ) ); ?></span>
			</div>
		<?php else : ?>
			<?php echo netelly_media( netelly_field( 'key_art', $wid ), '4/5', array( 'vt' => 'work-' . $wid, 'sizes' => '(max-width: 768px) 100vw, 40vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
	</div>
	<div class="work-row__text">
		<?php // Decorative index number, drawn by CSS (::before) so it stays out of the text. ?>
		<span class="work-row__num" data-num="<?php echo esc_attr( $num ); ?>" aria-hidden="true"></span>
		<span class="work-row__meta"><?php echo esc_html( implode( ' · ', array_filter( array( netelly_work_meta( $wid ), $note ) ) ) ); ?></span>
		<span class="work-row__title"><?php echo esc_html( get_the_title( $wid ) ); ?></span>
		<?php if ( $lead ) : ?>
			<span class="work-row__lead"><?php echo netelly_br( $lead ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
		<?php endif; ?>
		<?php if ( $plat ) : ?>
			<span class="work-row__platform"><?php echo esc_html( $plat ); ?></span>
		<?php endif; ?>
		<span class="text-link work-row__link"><?php echo netelly_label( netelly_t( 'view_work' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
	</div>
</a>

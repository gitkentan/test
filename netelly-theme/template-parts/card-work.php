<?php
/**
 * Work card (top ORIGINALS, works archive, MORE ORIGINALS).
 * Key art carries view-transition-name: work-{id} for the shared-element morph (README 2).
 *
 * Args: id (int), class (string).
 *
 * @package netelly
 */

$wid    = (int) ( $args['id'] ?? get_the_ID() );
$soon   = netelly_is_coming_soon( $wid );
$note   = (string) netelly_field( 'card_note', $wid );
$note   = $note ? $note : (string) netelly_field( 'format', $wid );
$genre  = (string) netelly_field( 'genre', $wid );
?>
<a class="card card-work<?php echo $soon ? ' is-soon' : ''; ?> <?php echo esc_attr( $args['class'] ?? '' ); ?>" href="<?php echo esc_url( get_permalink( $wid ) ); ?>" data-genre="<?php echo esc_attr( $genre ); ?>">
	<?php if ( $soon ) : ?>
		<div class="media card-work__soon" style="aspect-ratio:4/5">
			<?php echo netelly_lmark( 'card-work__lmark' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="card-work__soon-label"><?php echo esc_html( netelly_t( 'coming_soon' ) ); ?></span>
		</div>
	<?php else : ?>
		<?php echo netelly_media( netelly_field( 'key_art', $wid ), '4/5', array( 'vt' => 'work-' . $wid, 'sizes' => '(max-width: 768px) 50vw, 25vw', 'remote' => netelly_work_youtube_thumb( $wid ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<?php endif; ?>
	<span class="card-work__meta"><?php echo esc_html( netelly_work_meta( $wid ) ); ?></span>
	<span class="card-work__title"><?php echo esc_html( get_the_title( $wid ) ); ?></span>
	<?php if ( $note ) : ?>
		<span class="card-work__note"><?php echo esc_html( $note ); ?></span>
	<?php endif; ?>
</a>

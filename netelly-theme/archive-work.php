<?php
/**
 * ORIGINALS (works archive, design #p05): featured work, then an editorial index of
 * large alternating rows (template-parts/row-work.php) instead of the genre-tab grid,
 * so a small line-up reads as curated rather than sparse. Creators Fund band at the end.
 *
 * @package netelly
 */

get_header();

$all     = get_posts(
	array(
		'post_type'      => 'work',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'fields'         => 'ids',
	)
);
$featured = (int) netelly_opt( 'wa_featured' );
?>
<main id="main" class="page-works">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<?php if ( $featured ) : ?>
		<section class="section section--tight">
			<a class="featured-work" href="<?php echo esc_url( get_permalink( $featured ) ); ?>">
				<?php echo netelly_media( netelly_field( 'key_visual', $featured ), '16/9', array( 'vt' => 'work-kv-' . $featured, 'sizes' => '(max-width: 768px) 100vw, 60vw', 'remote' => netelly_work_youtube_thumb( (int) $featured ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="featured-work__text">
					<span class="featured-work__meta"><?php echo esc_html( implode( ' · ', array_filter( array( netelly_t( 'featured' ), netelly_work_meta( $featured ) ) ) ) ); ?></span>
					<span class="featured-work__title"><?php echo esc_html( get_the_title( $featured ) ); ?></span>
					<span class="featured-work__body"><?php echo esc_html( (string) netelly_field( 'featured_text', $featured ) ); ?></span>
					<span class="text-link"><?php echo netelly_label( netelly_t( 'view_work' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				</span>
			</a>
		</section>
	<?php endif; ?>

	<section class="section works-index" aria-label="<?php echo esc_attr( (string) netelly_opt( 'wa_title' ) ); ?>">
		<?php // The featured work is shown above, so the index lists the others. ?>
		<?php foreach ( array_values( array_diff( $all, array( $featured ) ) ) as $i => $wid ) : ?>
			<?php get_template_part( 'template-parts/row-work', null, array( 'id' => $wid, 'index' => $i + 1 ) ); ?>
		<?php endforeach; ?>
	</section>
	<div class="end-spacer"></div>

	<?php
	get_template_part(
		'template-parts/fund-cta',
		null,
		array(
			'label'   => (string) netelly_opt( 'wa_cta_label' ),
			'heading' => (string) netelly_opt( 'wa_cta_heading' ),
			'text'    => (string) netelly_opt( 'wa_cta_text' ),
			'primary' => (string) netelly_opt( 'wa_cta_button' ),
		)
	);
	?>
</main>
<?php
get_footer();

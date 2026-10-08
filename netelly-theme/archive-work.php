<?php
/**
 * Works archive (design #p05): featured work, genre tabs (README 8: filtered in place
 * by filter.js; ?genre= also works without JS), 3-column grid, Creators Fund band.
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
$current = isset( $_GET['genre'] ) ? sanitize_key( wp_unslash( $_GET['genre'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$counts  = array();
foreach ( $all as $wid ) {
	$g            = (string) netelly_field( 'genre', $wid );
	$counts[ $g ] = ( $counts[ $g ] ?? 0 ) + 1;
}
$base     = (string) get_post_type_archive_link( 'work' );
$featured = (int) netelly_opt( 'wa_featured' );
$fmt      = netelly_t( 'count_format' );
?>
<main id="main" class="page-works">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<?php if ( $featured ) : ?>
		<section class="section section--tight">
			<a class="featured-work" href="<?php echo esc_url( get_permalink( $featured ) ); ?>">
				<?php echo netelly_media( netelly_field( 'key_visual', $featured ), '16/9', array( 'vt' => 'work-kv-' . $featured, 'sizes' => '(max-width: 768px) 100vw, 60vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="featured-work__text">
					<span class="featured-work__meta"><?php echo esc_html( implode( ' · ', array_filter( array( netelly_t( 'featured' ), netelly_work_meta( $featured ) ) ) ) ); ?></span>
					<span class="featured-work__title"><?php echo esc_html( get_the_title( $featured ) ); ?></span>
					<span class="featured-work__body"><?php echo esc_html( (string) netelly_field( 'featured_text', $featured ) ); ?></span>
					<span class="text-link"><?php echo netelly_label( netelly_t( 'view_work' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				</span>
			</a>
		</section>
	<?php endif; ?>

	<section class="section works-list" data-filter>
		<div class="filter-bar">
			<div class="tabs" role="group" aria-label="<?php echo esc_attr( netelly_t( 'genre_all' ) ); ?>">
				<a class="tab<?php echo '' === $current ? ' is-current' : ''; ?>" href="<?php echo esc_url( $base ); ?>" data-genre=""<?php echo '' === $current ? ' aria-current="true"' : ''; ?>><?php echo esc_html( sprintf( $fmt, netelly_t( 'genre_all' ), count( $all ) ) ); ?></a>
				<?php foreach ( array_keys( netelly_genres() ) as $g ) : ?>
					<?php
					if ( empty( $counts[ $g ] ) ) {
						continue;
					}
					?>
					<a class="tab<?php echo $g === $current ? ' is-current' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'genre', $g, $base ) ); ?>" data-genre="<?php echo esc_attr( $g ); ?>"<?php echo $g === $current ? ' aria-current="true"' : ''; ?>><?php echo esc_html( sprintf( $fmt, netelly_t( 'genre_' . $g ), $counts[ $g ] ) ); ?></a>
				<?php endforeach; ?>
			</div>
			<span class="filter-bar__sort"><?php echo esc_html( netelly_t( 'sort_newest' ) ); ?></span>
		</div>
		<div class="grid-works" data-stagger="60" aria-live="polite">
			<?php foreach ( $all as $wid ) : ?>
				<?php
				$hidden = $current && netelly_field( 'genre', $wid ) !== $current;
				get_template_part( 'template-parts/card-work', null, array( 'id' => $wid, 'class' => $hidden ? 'is-hidden' : '' ) );
				?>
			<?php endforeach; ?>
		</div>
		<p class="works-empty"<?php echo ( $current && empty( $counts[ $current ] ) ) ? '' : ' hidden'; ?>><?php echo esc_html( netelly_t( 'no_works' ) ); ?></p>
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

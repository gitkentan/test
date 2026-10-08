<?php
/**
 * Work detail (design #p06): full-black page. KV with horizon, story, total views
 * (counts up, README 3), episodes, cast, staff, more originals.
 *
 * @package netelly
 */

get_header();
the_post();
$wid      = get_the_ID();
$f        = static fn( $name ) => netelly_field( $name, $wid );
$kv_video = (int) $f( 'key_visual_video' );
$trailer  = (string) $f( 'trailer_url' );
$playlist = (string) $f( 'playlist_url' );
preg_match( '~(?:youtu\.be/|v=|embed/|shorts/)([\w-]{11})~', $trailer, $yt );
// Title fit: 84px on one line when it fits (design); otherwise 56px / 44px, wrapping (PC only).
$est      = mb_strwidth( get_the_title() ) * 42 * 1.03; // ≈ px at 84px (half-width unit ≈ .5em) + tracking.
$size     = $est <= 880 ? '' : ( $est <= 880 * 2 * 84 / 56 ? ' is-m' : ' is-s' );
$meta     = implode( ' · ', array_filter( array( $f( 'year' ), $f( 'format' ), $f( 'platform' ) ) ) );
$episodes = (array) $f( 'episodes' );
$cast     = (array) $f( 'cast' );
$staff    = (array) $f( 'staff' );
$views    = (int) $f( 'total_views' );
$more     = get_posts(
	array(
		'post_type'      => 'work',
		'posts_per_page' => 4,
		'post__not_in'   => array( $wid ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => 'genre',
				'value'   => 'in_development',
				'compare' => '!=',
			),
		),
	)
);
?>
<main id="main" class="page-work is-dark">
	<section class="work-hero" aria-labelledby="work-title">
		<div class="work-hero__media" data-parallax>
			<?php echo netelly_media( $f( 'key_visual' ), 'auto', array( 'vt' => 'work-' . $wid, 'eager' => true, 'size' => 'full', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $kv_video ) : ?>
				<video class="work-hero__video" muted loop playsinline preload="none" aria-hidden="true" data-src="<?php echo esc_url( (string) wp_get_attachment_url( $kv_video ) ); ?>"></video>
			<?php endif; ?>
		</div>
		<div class="work-hero__shade" aria-hidden="true"></div>
		<div class="work-hero__line horizon" aria-hidden="true"></div>
		<div class="work-hero__body">
			<div class="work-hero__text">
				<p class="work-hero__series"><?php echo esc_html( (string) $f( 'series_label' ) ); ?></p>
				<h1 class="work-hero__title<?php echo esc_attr( $size ); ?>" id="work-title"><span class="mask"><span><?php the_title(); ?></span></span></h1>
				<p class="work-hero__meta"><?php echo esc_html( $meta ); ?></p>
			</div>
			<div class="btn-group work-hero__cta">
				<?php if ( $trailer ) : ?>
					<a class="btn btn--primary" href="<?php echo esc_url( $trailer ); ?>" target="_blank" rel="noopener"<?php echo $yt ? ' data-trailer="' . esc_attr( $yt[1] ) . '"' : ''; ?>><span class="btn__label"><?php echo netelly_label( netelly_t( 'trailer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></a>
				<?php endif; ?>
				<?php echo netelly_button( netelly_t( 'watch_all' ), $playlist, 'outline', array( 'blank' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>

	<section class="section">
		<?php if ( $f( 'synopsis_lead' ) || $f( 'synopsis' ) ) : ?>
			<div class="statement">
				<p class="statement__label"><?php echo esc_html( netelly_t( 'story_label' ) ); ?></p>
				<div class="statement__body work-story">
					<h2 class="work-story__lead"><?php echo netelly_br( (string) $f( 'synopsis_lead' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<p class="work-story__text"><?php echo esc_html( (string) $f( 'synopsis' ) ); ?></p>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( $views ) : ?>
			<div class="statement statement--ruled work-views">
				<p class="statement__label"><?php echo esc_html( netelly_t( 'views_label' ) ); ?></p>
				<div class="work-views__body">
					<p class="work-views__num" data-count="<?php echo esc_attr( (string) $views ); ?>"><span class="work-views__digits"><?php echo esc_html( number_format( $views ) ); ?></span><?php echo esc_html( (string) $f( 'total_views_suffix' ) ); ?></p>
					<p class="work-views__note"><?php echo esc_html( (string) $f( 'total_views_note' ) ); ?></p>
				</div>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( $episodes ) : ?>
		<section class="section">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => netelly_t( 'episodes_label' ), 'heading' => netelly_t( 'episodes_heading' ) ) ); ?>
			<div class="episodes" data-stagger="60">
				<?php foreach ( $episodes as $ep ) : ?>
					<?php $ep_url = (string) ( $ep['url'] ?? '' ); ?>
					<<?php echo $ep_url ? 'a' : 'div'; ?> class="card episode"<?php echo $ep_url ? netelly_link_attrs( $ep_url ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<?php echo netelly_media( $ep['thumb'] ?? 0, '16/9', array( 'sizes' => '(max-width: 768px) 140px, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="episode__body">
							<span class="episode__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $ep['no'] ?? '', $ep['date'] ?? '' ) ) ) ); ?></span>
							<span class="episode__title"><?php echo esc_html( (string) ( $ep['title'] ?? '' ) ); ?></span>
						</span>
					</<?php echo $ep_url ? 'a' : 'div'; ?>>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $cast ) : ?>
		<section class="section">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => netelly_t( 'cast_label' ), 'heading' => netelly_t( 'cast_heading' ) ) ); ?>
			<ul class="cast" data-stagger="60">
				<?php foreach ( $cast as $c ) : ?>
					<li class="cast__item">
						<?php echo netelly_media( $c['photo'] ?? 0, '3/4', array( 'sizes' => '(max-width: 768px) 33vw, 30vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="cast__role"><?php echo esc_html( sprintf( netelly_t( 'cast_role' ), (string) ( $c['role'] ?? '' ) ) ); ?></span>
						<span class="cast__name"><?php echo esc_html( (string) ( $c['name'] ?? '' ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php if ( $staff ) : ?>
		<section class="section">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => netelly_t( 'staff_label' ), 'heading' => netelly_t( 'staff_heading' ) ) ); ?>
			<dl class="staff">
				<?php foreach ( $staff as $s ) : ?>
					<div class="staff__row">
						<dt class="staff__role"><?php echo esc_html( (string) ( $s['role'] ?? '' ) ); ?></dt>
						<dd class="staff__name"><?php echo esc_html( (string) ( $s['name'] ?? '' ) ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>

	<?php if ( $more ) : ?>
		<section class="section">
			<?php
			get_template_part(
				'template-parts/section-head',
				null,
				array(
					'label'   => netelly_t( 'more_label' ),
					'heading' => netelly_t( 'more_heading' ),
					'link'    => array( netelly_t( 'all_works' ), (string) get_post_type_archive_link( 'work' ) ),
				)
			);
			?>
			<div class="grid-works grid-works--4" data-stagger="60">
				<?php foreach ( $more as $mid ) : ?>
					<?php get_template_part( 'template-parts/card-work', null, array( 'id' => $mid ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
	<div class="end-spacer"></div>
</main>

<?php if ( $yt ) : ?>
	<dialog class="trailer-modal" aria-label="<?php echo esc_attr( netelly_t( 'trailer_title' ) ); ?>" data-video="<?php echo esc_attr( $yt[1] ); ?>">
		<button class="trailer-modal__close" type="button"><span class="screen-reader-text"><?php echo esc_html( netelly_t( 'modal_close' ) ); ?></span><span aria-hidden="true">×</span></button>
		<div class="trailer-modal__frame"></div>
	</dialog>
<?php endif; ?>
<?php
get_footer();

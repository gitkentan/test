<?php
/**
 * Template Name: CREATIVE STUDIO
 *
 * CREATIVE STUDIO (/studio/): films for companies and brands: intro, services, why Netelly, process, works and
 * a contact band that opens the contact form with 制作・配給のご相談 selected (?type=production).
 * Texts: 固定ページ › 企業・ブランドの映像制作 (defaults in inc/studio.php).
 *
 * @package netelly
 */

get_header();
$f        = static fn( $name ) => netelly_field( $name );
$services = array_values( (array) $f( 'services' ) );
$points   = array_values( (array) $f( 'points' ) );
$steps    = array_values( (array) $f( 'steps' ) );
$works    = array_filter( array_map( 'intval', (array) $f( 'works' ) ) );
if ( ! $works ) {
	$works = get_posts(
		array(
			'post_type'      => 'work',
			'posts_per_page' => 3,
			'fields'         => 'ids',
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);
}
$contact = add_query_arg( 'type', 'production', netelly_page_url( 'contact' ) );
?>
<main id="main" class="page-studio">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<?php /* ---------- Intro ---------- */ ?>
	<section class="section studio-intro">
		<p class="studio-label" lang="en"><?php echo esc_html( (string) $f( 'intro_label' ) ); ?></p>
		<h2 class="studio-intro__heading"><?php echo netelly_br( (string) $f( 'intro_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<p class="studio-intro__text"><?php echo esc_html( (string) $f( 'intro_text' ) ); ?></p>
	</section>

	<?php /* ---------- Services ---------- */ ?>
	<?php if ( $services ) : ?>
		<section class="section">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'svc_label' ), 'heading' => (string) $f( 'svc_heading' ) ) ); ?>
			<ol class="studio-services" data-stagger="60">
				<?php foreach ( $services as $i => $s ) : ?>
					<li class="studio-service">
						<span class="studio-service__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<p class="studio-service__en" lang="en"><?php echo esc_html( (string) ( $s['en'] ?? '' ) ); ?></p>
						<h3 class="studio-service__title"><?php echo esc_html( (string) ( $s['title'] ?? '' ) ); ?></h3>
						<p class="studio-service__text"><?php echo esc_html( (string) ( $s['text'] ?? '' ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php /* ---------- Why Netelly ---------- */ ?>
	<?php if ( $points ) : ?>
		<section class="section">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'why_label' ), 'heading' => (string) $f( 'why_heading' ) ) ); ?>
			<ol class="studio-points" data-stagger="80">
				<?php foreach ( $points as $i => $pt ) : ?>
					<li class="studio-point">
						<span class="studio-point__num" lang="en"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="studio-point__title"><?php echo esc_html( (string) ( $pt['title'] ?? '' ) ); ?></h3>
						<p class="studio-point__text"><?php echo esc_html( (string) ( $pt['text'] ?? '' ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php /* ---------- Process ---------- */ ?>
	<?php if ( $steps ) : ?>
		<section class="section">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'flow_label' ), 'heading' => (string) $f( 'flow_heading' ) ) ); ?>
			<ol class="studio-flow" style="--steps:<?php echo (int) count( $steps ); ?>" data-stagger="80">
				<?php foreach ( $steps as $i => $s ) : ?>
					<li class="studio-step">
						<span class="studio-step__num" lang="en">STEP <?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="studio-step__title"><?php echo esc_html( (string) ( $s['title'] ?? '' ) ); ?></h3>
						<p class="studio-step__text"><?php echo esc_html( (string) ( $s['text'] ?? '' ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php /* ---------- Works ---------- */ ?>
	<?php if ( $works ) : ?>
		<section class="section">
			<?php
			get_template_part(
				'template-parts/section-head',
				null,
				array(
					'label'   => (string) $f( 'works_label' ),
					'heading' => (string) $f( 'works_heading' ),
					'link'    => array( netelly_t( 'all_works' ), (string) get_post_type_archive_link( 'work' ) ),
				)
			);
			?>
			<?php if ( $f( 'works_text' ) ) : ?>
				<p class="studio-works__text"><?php echo esc_html( (string) $f( 'works_text' ) ); ?></p>
			<?php endif; ?>
			<div class="grid-works studio-works" data-stagger="60">
				<?php foreach ( $works as $wid ) : ?>
					<?php get_template_part( 'template-parts/card-work', null, array( 'id' => $wid ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---------- Contact band ---------- */ ?>
	<section class="studio-cta is-dark">
		<div class="studio-cta__glow" aria-hidden="true"></div>
		<p class="studio-label" lang="en"><?php echo esc_html( (string) $f( 'cta_label' ) ); ?></p>
		<h2 class="studio-cta__heading"><?php echo netelly_br( (string) $f( 'cta_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		<div class="studio-cta__foot">
			<p class="studio-cta__text"><?php echo esc_html( (string) $f( 'cta_text' ) ); ?></p>
			<?php echo netelly_button( (string) $f( 'cta_button' ), $contact, 'primary', array( 'class' => 'studio-cta__btn' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>
</main>
<?php
get_footer();

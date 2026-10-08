<?php
/**
 * Template Name: 企業情報
 *
 * Company (design #p02): MISSION / VISION, profile table, history, access.
 *
 * @package netelly
 */

get_header();
$f = static fn( $name ) => netelly_field( $name );
?>
<main id="main" class="page-company">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<section class="section" id="mission">
		<div class="statement">
			<p class="statement__label"><?php echo esc_html( (string) $f( 'mission_label' ) ); ?></p>
			<div class="statement__body">
				<h2 class="statement__heading statement__heading--xl"><?php echo netelly_br( (string) $f( 'mission_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="statement__sub" lang="en"><?php echo esc_html( (string) $f( 'mission_sub' ) ); ?></p>
				<p class="statement__text"><?php echo esc_html( (string) $f( 'mission_text' ) ); ?></p>
			</div>
		</div>
		<div class="statement statement--ruled" id="vision">
			<p class="statement__label"><?php echo esc_html( (string) $f( 'vision_label' ) ); ?></p>
			<div class="statement__body">
				<h2 class="statement__heading"><?php echo netelly_br( (string) $f( 'vision_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="statement__text"><?php echo esc_html( (string) $f( 'vision_text' ) ); ?></p>
				<?php echo netelly_text_link( $f( 'vision_link' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>

	<section class="section" id="profile">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'profile_label' ), 'heading' => (string) $f( 'profile_heading' ) ) ); ?>
		<dl class="table">
			<?php foreach ( (array) netelly_opt( 'profile' ) as $row ) : ?>
				<div class="table__row">
					<dt class="table__label"><?php echo esc_html( (string) $row['label'] ); ?></dt>
					<dd class="table__value"><?php echo netelly_br( (string) $row['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</section>

	<section class="section" id="history">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'history_label' ), 'heading' => (string) $f( 'history_heading' ) ) ); ?>
		<ol class="table table--history">
			<?php foreach ( (array) netelly_opt( 'history' ) as $row ) : ?>
				<li class="table__row">
					<span class="table__year"><?php echo esc_html( (string) $row['year'] ); ?></span>
					<span class="table__value"><?php echo esc_html( (string) $row['text'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<section class="section" id="access">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'access_label' ), 'heading' => (string) $f( 'access_heading' ) ) ); ?>
		<div class="access">
			<?php echo netelly_media( $f( 'access_map' ), '16/9', array( 'sizes' => '(max-width: 768px) 100vw, 60vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="access__text">
				<p class="access__name"><?php echo esc_html( (string) $f( 'access_name' ) ); ?></p>
				<p class="access__address"><?php echo netelly_br( (string) $f( 'access_address' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php echo netelly_text_link( $f( 'access_map_link' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

<?php
/**
 * 404 (not in the design): light header + the site's statement layout and horizon.
 *
 * @package netelly
 */

get_header();
?>
<main id="main" class="page-404">
	<section class="section section--tight">
		<div class="statement">
			<p class="statement__label"><?php echo esc_html( netelly_t( 'nf_label' ) ); ?></p>
			<div class="statement__body">
				<h1 class="statement__heading"><?php echo esc_html( netelly_t( 'nf_heading' ) ); ?></h1>
				<p class="statement__text"><?php echo esc_html( netelly_t( 'nf_text' ) ); ?></p>
				<?php echo netelly_button( netelly_t( 'nf_button' ), netelly_home_url(), 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

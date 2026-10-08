<?php
/**
 * Fallback template (any view without a dedicated template, e.g. search).
 *
 * @package netelly
 */

get_header();
?>
<main id="main">
	<?php if ( netelly_hero_data() ) : ?>
		<?php get_template_part( 'template-parts/page-hero' ); ?>
	<?php endif; ?>
	<section class="section section--tight">
		<?php if ( have_posts() ) : ?>
			<div class="list-news">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/row-news', null, array( 'id' => get_the_ID() ) );
				endwhile;
				?>
			</div>
		<?php else : ?>
			<p class="list-empty"><?php echo esc_html( netelly_t( 'no_posts' ) ); ?></p>
		<?php endif; ?>
	</section>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

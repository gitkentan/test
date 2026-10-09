<?php
/**
 * News list (design #p08) — posts page and category archives.
 * Category tabs, 10 per page, square pager.
 *
 * @package netelly
 */

get_header();
$posts_page = (int) get_option( 'page_for_posts' );
$news_url   = $posts_page ? get_permalink( netelly_tr_id( $posts_page ) ) : home_url( '/' );
$cats       = get_terms(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
		'orderby'    => 'term_id',
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	)
);
$cats       = array_filter( (array) $cats, static fn( $t ) => $t instanceof WP_Term && ! str_starts_with( $t->slug, 'uncategorized' ) );
$current    = is_category() ? get_queried_object_id() : 0;
?>
<main id="main" class="page-news">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
	<section class="section news-list">
		<nav class="tabs" aria-label="<?php echo esc_attr( netelly_t( 'cat_all' ) ); ?>">
			<a class="tab<?php echo $current ? '' : ' is-current'; ?>" href="<?php echo esc_url( $news_url ); ?>"<?php echo $current ? '' : ' aria-current="page"'; ?>><?php echo esc_html( netelly_t( 'cat_all' ) ); ?></a>
			<?php foreach ( $cats as $cat ) : ?>
				<a class="tab<?php echo $current === $cat->term_id ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"<?php echo $current === $cat->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php if ( have_posts() ) : ?>
			<?php // No staggered reveal here: the list is the page content and shows at once (UX / LCP). ?>
			<div class="list-news">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/row-news', null, array( 'id' => get_the_ID() ) );
				endwhile;
				?>
			</div>
			<?php get_template_part( 'template-parts/pager' ); ?>
		<?php else : ?>
			<p class="list-empty"><?php echo esc_html( netelly_t( 'no_posts' ) ); ?></p>
		<?php endif; ?>
	</section>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

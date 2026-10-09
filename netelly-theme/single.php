<?php
/**
 * News article (design #p09): light header, body 720px, work summary box, share, prev / next.
 * Posts with an external URL are listed with ↗ and never reach this page from the list.
 *
 * @package netelly
 */

get_header();
the_post();
$pid      = get_the_ID();
$cats     = get_the_category();
$work     = (int) netelly_field( 'related_work' );
$summary  = (string) netelly_field( 'work_summary' );
$posts_pg = (int) get_option( 'page_for_posts' );
$news_url = $posts_pg ? get_permalink( netelly_tr_id( $posts_pg ) ) : home_url( '/' );
$prev     = get_previous_post();
$next     = get_next_post();
$share    = rawurlencode( (string) get_permalink() );
$title    = rawurlencode( get_the_title() );
$adjacent = static function ( $p, string $label, string $class ) {
	if ( ! $p ) {
		return '<span class="adjacent__item ' . esc_attr( $class ) . ' is-empty"></span>';
	}
	$ext = (string) netelly_field( 'external_url', $p->ID );
	$url = $ext ? $ext : get_permalink( $p );
	$ext = $ext && netelly_is_external( $ext ) ? $ext : '';
	return sprintf(
		'<a class="adjacent__item %1$s"%2$s><span class="adjacent__label">%3$s</span><span class="adjacent__title">%4$s</span>%5$s</a>',
		esc_attr( $class ),
		netelly_link_attrs( $url, (bool) $ext ),
		esc_html( $label ),
		esc_html( get_the_title( $p ) ),
		netelly_new_tab_note( $url, (bool) $ext )
	);
};
?>
<main id="main" class="page-article">
	<article class="article">
		<a class="article__back text-link" href="<?php echo esc_url( $news_url ); ?>"><?php echo esc_html( netelly_t( 'back_news' ) ); ?></a>
		<header class="article__head">
			<p class="article__meta">
				<time class="article__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
				<?php if ( $cats ) : ?>
					<a class="article__cat" href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
				<?php endif; ?>
			</p>
			<h1 class="article__title"><?php the_title(); ?></h1>
		</header>
		<?php if ( has_post_thumbnail() ) : // No アイキャッチ → no empty frame; the body follows the title. ?>
			<?php echo netelly_media( get_post_thumbnail_id(), '16/9', array( 'eager' => true, 'sizes' => '(max-width: 768px) 100vw, 912px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php endif; ?>
		<div class="article__body">
			<div class="article__content"><?php the_content(); ?></div>
			<?php if ( $work && $summary ) : ?>
				<aside class="work-summary">
					<p class="work-summary__title"><?php echo esc_html( netelly_t( 'work_summary' ) ); ?></p>
					<p class="work-summary__text"><?php echo netelly_br( $summary ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php echo netelly_text_link( array( netelly_t( 'to_work' ), (string) get_permalink( $work ) ), 'work-summary__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</aside>
			<?php endif; ?>
			<div class="share">
				<span class="share__label"><?php echo esc_html( netelly_t( 'share' ) ); ?></span>
				<a href="https://x.com/intent/post?url=<?php echo esc_attr( $share ); ?>&amp;text=<?php echo esc_attr( $title ); ?>" target="_blank" rel="noopener">X<span class="screen-reader-text"><?php echo esc_html( netelly_t( 'new_tab' ) ); ?></span></a>
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $share ); ?>" target="_blank" rel="noopener">FACEBOOK<span class="screen-reader-text"><?php echo esc_html( netelly_t( 'new_tab' ) ); ?></span></a>
				<button class="share__copy" type="button" data-url="<?php echo esc_url( (string) get_permalink() ); ?>" data-done="<?php echo esc_attr( netelly_t( 'copied' ) ); ?>"><?php echo esc_html( netelly_t( 'share_link' ) ); ?></button>
				<span class="share__status" role="status" aria-live="polite"></span>
			</div>
		</div>
		<nav class="adjacent" aria-label="<?php echo esc_attr( netelly_t( 'back_news' ) ); ?>">
			<?php
			echo $adjacent( $prev, netelly_t( 'prev_post' ), 'is-prev' ); // phpcs:ignore WordPress.Security.EscapeOutput
			echo $adjacent( $next, netelly_t( 'next_post' ), 'is-next' ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</nav>
	</article>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

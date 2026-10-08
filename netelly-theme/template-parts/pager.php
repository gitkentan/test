<?php
/**
 * Square pager (design #p08): 44px boxes, current filled, next arrow.
 *
 * @package netelly
 */

global $wp_query;
$total = (int) $wp_query->max_num_pages;
if ( $total < 2 ) {
	return;
}
$paged = max( 1, (int) get_query_var( 'paged' ) );
?>
<nav class="pager" aria-label="pagination">
	<?php if ( $paged > 1 ) : ?>
		<a class="pager__item" href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>"><span aria-hidden="true">←</span><span class="screen-reader-text"><?php echo esc_html( netelly_t( 'page_prev' ) ); ?></span></a>
	<?php endif; ?>
	<?php for ( $i = 1; $i <= $total; $i++ ) : ?>
		<?php if ( $i === $paged ) : ?>
			<span class="pager__item is-current" aria-current="page"><?php echo (int) $i; ?></span>
		<?php else : ?>
			<a class="pager__item" href="<?php echo esc_url( get_pagenum_link( $i ) ); ?>"><?php echo (int) $i; ?></a>
		<?php endif; ?>
	<?php endfor; ?>
	<?php if ( $paged < $total ) : ?>
		<a class="pager__item" href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"><span aria-hidden="true">→</span><span class="screen-reader-text"><?php echo esc_html( netelly_t( 'page_next' ) ); ?></span></a>
	<?php endif; ?>
</nav>

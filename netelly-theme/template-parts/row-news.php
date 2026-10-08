<?php
/**
 * News row (top NEWS, news list). External-URL posts open in a new tab with ↗ (案B).
 *
 * Args: id (int).
 *
 * @package netelly
 */

$nid  = (int) ( $args['id'] ?? get_the_ID() );
$ext  = (string) netelly_field( 'external_url', $nid );
$url  = $ext ? $ext : get_permalink( $nid );
$cats = get_the_category( $nid );
$cat  = $cats ? $cats[0]->name : '';
?>
<a class="row-link row-news"<?php echo netelly_link_attrs( $url, (bool) $ext ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<span class="row-news__date"><?php echo esc_html( get_the_date( 'Y.m.d', $nid ) ); ?></span>
	<?php if ( $cat ) : ?>
		<span class="row-news__cat"><?php echo esc_html( $cat ); ?></span>
	<?php endif; ?>
	<span class="row-news__title"><?php echo esc_html( get_the_title( $nid ) ); ?></span>
	<span class="row-news__arrow arrow" aria-hidden="true"><?php echo $ext ? '↗' : '→'; ?></span>
	<?php echo netelly_new_tab_note( $url, (bool) $ext ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</a>

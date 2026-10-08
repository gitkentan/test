<?php
/**
 * Page hero with the horizon line (signature motif, README "地平線モチーフ").
 * English title sits above the line, Japanese title + breadcrumb below it.
 *
 * Args: en (string), title (string). Defaults to netelly_hero_data().
 *
 * @package netelly
 */

$data  = array_merge( (array) netelly_hero_data(), array_filter( (array) $args ) );
$en    = (string) ( $data['en'] ?? '' );
$title = (string) ( $data['title'] ?? '' );
?>
<section class="page-hero" aria-labelledby="page-title">
	<div class="page-hero__line horizon" aria-hidden="true"></div>
	<div class="page-hero__upper">
		<?php if ( $en ) : ?>
			<p class="page-hero__en" lang="en"><span class="mask"><span><?php echo esc_html( $en ); ?></span></span></p>
		<?php endif; ?>
	</div>
	<div class="page-hero__lower">
		<h1 class="page-hero__title" id="page-title"><span class="mask"><span><?php echo esc_html( $title ); ?></span></span></h1>
		<nav class="page-hero__crumbs" aria-label="breadcrumb">
			<a href="<?php echo esc_url( netelly_home_url() ); ?>"><?php echo esc_html( netelly_t( 'breadcrumb_top' ) ); ?></a>
			<span aria-hidden="true"> / </span>
			<span aria-current="page"><?php echo esc_html( $title ); ?></span>
		</nav>
	</div>
</section>

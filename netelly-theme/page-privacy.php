<?php
/**
 * Template Name: プライバシーポリシー
 *
 * Privacy policy (design #p12): enacted date left, intro + numbered articles right.
 *
 * @package netelly
 */

get_header();
$f = static fn( $name ) => netelly_field( $name );
?>
<main id="main" class="page-privacy">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
	<section class="section section--tight">
		<div class="policy">
			<p class="policy__date"><?php echo esc_html( (string) $f( 'enacted' ) ); ?></p>
			<div class="policy__body">
				<p class="policy__intro"><?php echo esc_html( (string) $f( 'intro' ) ); ?></p>
				<?php foreach ( (array) $f( 'articles' ) as $i => $a ) : ?>
					<section class="policy__article">
						<h2 class="policy__title"><span class="policy__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo esc_html( (string) $a['title'] ); ?></h2>
						<p class="policy__text"><?php echo netelly_br( (string) $a['body'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

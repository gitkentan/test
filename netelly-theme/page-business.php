<?php
/**
 * Template Name: 事業
 *
 * Business (design #p04): statement, four alternating rows, Creators Fund band.
 *
 * @package netelly
 */

get_header();
$f   = static fn( $name ) => netelly_field( $name );
$ids = array( 'creative', 'media', 'community', 'creators-fund' );
?>
<main id="main" class="page-business">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<section class="section">
		<div class="statement">
			<p class="statement__label"><?php echo esc_html( (string) $f( 'intro_label' ) ); ?></p>
			<div class="statement__body">
				<h2 class="statement__heading statement__heading--intro"><?php echo netelly_br( (string) $f( 'intro_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
		</div>
	</section>

	<section class="biz-rows">
		<?php foreach ( (array) $f( 'items' ) as $i => $item ) : ?>
			<?php
			$is_fund = ! empty( $item['is_fund'] );
			$link    = $item['link'] ?? null;
			if ( $is_fund && is_array( $link ) ) {
				$link['url']    = netelly_fund_url();
				$link['target'] = netelly_is_external( $link['url'] ) ? '_blank' : '';
			}
			?>
			<article class="biz-row<?php echo $i % 2 ? ' is-reverse' : ''; ?>" id="<?php echo esc_attr( $ids[ $i ] ?? 'business-' . $i ); ?>">
				<div class="biz-row__media"><?php echo netelly_media( $item['image'] ?? 0, '4/3', array( 'sizes' => '(max-width: 768px) 100vw, 50vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="biz-row__text">
					<span class="biz-row__num"><?php echo esc_html( (string) ( $item['num'] ?? '' ) ); ?></span>
					<h2 class="biz-row__en" lang="en"><?php echo esc_html( (string) ( $item['en'] ?? '' ) ); ?></h2>
					<p class="biz-row__title"><?php echo esc_html( (string) ( $item['title'] ?? '' ) ); ?></p>
					<p class="biz-row__body"><?php echo esc_html( (string) ( $item['text'] ?? '' ) ); ?></p>
					<?php if ( ! empty( $item['note'] ) ) : ?>
						<p class="biz-row__note"><?php echo esc_html( (string) $item['note'] ); ?></p>
					<?php endif; ?>
					<?php echo netelly_text_link( $link, 'biz-row__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</article>
		<?php endforeach; ?>
	</section>

	<?php
	get_template_part(
		'template-parts/fund-cta',
		null,
		array(
			'label'     => (string) $f( 'cta_label' ),
			'heading'   => (string) $f( 'cta_heading' ),
			'text'      => (string) $f( 'cta_text' ),
			'primary'   => (string) $f( 'cta_primary' ),
			'secondary' => (string) $f( 'cta_secondary' ),
		)
	);
	?>
</main>
<?php
get_footer();

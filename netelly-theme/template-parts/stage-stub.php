<?php
/**
 * Temporary stage-3 check: page hero + section heads + buttons. Replaced in stage 4.
 *
 * @package netelly
 */

?>
<main id="main">
	<?php if ( netelly_hero_data() ) { get_template_part( 'template-parts/page-hero' ); } ?>
	<section class="section">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => 'SECTION HEAD',
				'heading' => 'en' === netelly_lang() ? 'Section heading' : 'セクション見出し',
				'link'    => array( 'title' => 'en' === netelly_lang() ? 'All news →' : 'ニュース一覧 →', 'url' => netelly_page_url( 'news' ) ),
			)
		);
		?>
		<div class="stage-demo">
			<p class="stage-demo__note">BUTTONS — PRIMARY / OUTLINE (LIGHT)</p>
			<div class="btn-group">
				<?php echo netelly_button( 'Netellyについて →', netelly_page_url( 'company' ), 'primary' ); // phpcs:ignore ?>
				<?php echo netelly_button( '作品を見る', home_url( '/works/' ), 'outline' ); // phpcs:ignore ?>
			</div>
			<div class="stage-demo__dark is-dark">
				<p class="stage-demo__note">BUTTONS — DARK</p>
				<div class="btn-group">
					<?php echo netelly_button( '募集職種を見る →', netelly_page_url( 'careers' ), 'primary' ); // phpcs:ignore ?>
					<?php echo netelly_button( '募集要項を見る', netelly_fund_url(), 'outline' ); // phpcs:ignore ?>
				</div>
			</div>
		</div>
	</section>
	<section class="section is-dark" style="padding-bottom:var(--sec)">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => 'NETELLY ORIGINALS',
				'heading' => 'en' === netelly_lang() ? 'Originals' : 'オリジナル作品',
				'link'    => array( 'title' => 'en' === netelly_lang() ? 'All works →' : 'すべての作品 →', 'url' => home_url( '/works/' ) ),
			)
		);
		?>
	</section>
	<div class="end-spacer"></div>
</main>

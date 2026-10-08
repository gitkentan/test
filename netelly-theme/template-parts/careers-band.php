<?php
/**
 * Careers band (top, CEO message): copy + button left, studio photo right. サイト設定 › 採用帯.
 *
 * @package netelly
 */

?>
<section class="careers-band is-dark">
	<div class="careers-band__text">
		<div class="careers-band__head">
			<p class="careers-band__label"><?php echo esc_html( (string) netelly_opt( 'cb_label' ) ); ?></p>
			<h2 class="careers-band__heading"><?php echo netelly_br( (string) netelly_opt( 'cb_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<div class="careers-band__cta"><?php echo netelly_link_button( netelly_opt( 'cb_button' ), 'primary', array( 'class' => 'is-block-sp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
	<div class="careers-band__media"><?php echo netelly_media( netelly_opt( 'cb_image' ), 'auto', array( 'sizes' => '(max-width: 768px) 100vw, 50vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</section>

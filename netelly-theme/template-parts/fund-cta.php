<?php
/**
 * Creators Fund band (business, works archive): cream ground, horizon line,
 * L-mark, external buttons to the fund site.
 *
 * Args: label, heading, text, primary, secondary (labels; both link to the fund URL).
 *
 * @package netelly
 */

$fund = netelly_fund_url();
?>
<section class="fund-cta">
	<div class="fund-cta__upper">
		<div class="fund-cta__head">
			<p class="fund-cta__label"><?php echo esc_html( (string) ( $args['label'] ?? '' ) ); ?></p>
			<h2 class="fund-cta__heading"><?php echo netelly_br( (string) ( $args['heading'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<?php echo netelly_lmark( 'fund-cta__mark' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<div class="fund-cta__line horizon" aria-hidden="true"></div>
	<div class="fund-cta__lower">
		<p class="fund-cta__text"><?php echo esc_html( (string) ( $args['text'] ?? '' ) ); ?></p>
		<div class="btn-group fund-cta__buttons">
			<?php echo netelly_button( (string) ( $args['primary'] ?? '' ), $fund, 'primary', array( 'blank' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo netelly_button( (string) ( $args['secondary'] ?? '' ), $fund, 'outline', array( 'blank' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</section>

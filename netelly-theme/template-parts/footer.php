<?php
/**
 * Site footer: logo + address, menu columns (外観 › メニュー "footer"), copyright.
 * Text colour is fixed to --ink on every page (design fix for the dark work page).
 *
 * @package netelly
 */

$columns = netelly_menu( 'footer' );
?>
<footer class="site-footer">
	<div class="site-footer__main">
		<div class="site-footer__brand">
			<a class="site-footer__logo" href="<?php echo esc_url( netelly_home_url() ); ?>"><?php echo netelly_logo( 'logo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<p class="site-footer__address"><?php echo esc_html( (string) netelly_opt( 'company_name' ) ); ?><br><?php echo netelly_br( (string) netelly_opt( 'company_address' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</div>
		<?php foreach ( $columns as $col ) : ?>
			<div class="site-footer__col">
				<?php if ( '#' === $col['url'] || '' === $col['url'] ) : ?>
					<p class="site-footer__heading"><?php echo esc_html( $col['title'] ); ?></p>
				<?php else : ?>
					<p class="site-footer__heading"><?php echo netelly_menu_link( $col ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<?php endif; ?>
				<?php if ( $col['children'] ) : ?>
					<ul>
						<?php foreach ( $col['children'] as $child ) : ?>
							<li><?php echo netelly_menu_link( $child, 'site-footer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="site-footer__bottom">
		<small><?php echo esc_html( (string) netelly_opt( 'copyright' ) ); ?></small>
		<span><?php echo esc_html( (string) netelly_opt( 'footer_domain' ) ); ?></span>
	</div>
</footer>

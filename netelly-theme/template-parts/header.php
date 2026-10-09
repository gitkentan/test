<?php
/**
 * Site header (PC nav / SP toggle) + SP full-screen menu.
 *
 * States (stage 5 motion): data-variant="dark|light" is the colour over the
 * first screen; data-state="top|solid|hidden" is driven by header.js.
 *
 * @package netelly
 */

$variant = $args['variant'] ?? 'dark';
$menu    = netelly_menu( 'primary' );
$contact = netelly_page_url( 'contact' );
?>
<header class="site-header" data-variant="<?php echo esc_attr( $variant ); ?>" data-state="top">
	<div class="site-header__bar">
		<a class="site-header__logo" href="<?php echo esc_url( netelly_home_url() ); ?>"><?php echo netelly_logo( 'logo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>

		<?php if ( $menu ) : ?>
			<nav class="site-nav" aria-label="<?php echo esc_attr( netelly_t( 'nav_label' ) ); ?>">
				<ul class="site-nav__list">
					<?php foreach ( $menu as $item ) : ?>
						<li><?php echo netelly_menu_link( $item, 'site-nav__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>

		<div class="site-header__tools">
			<?php echo netelly_theme_switch(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo netelly_lang_switch(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<a class="site-header__cta<?php echo netelly_is_latin( netelly_t( 'header_contact' ) ) ? ' is-latin' : ''; ?>" href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( netelly_t( 'header_contact' ) ); ?></a>
			<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="sp-menu" data-label-open="<?php echo esc_attr( netelly_t( 'menu_open' ) ); ?>" data-label-close="<?php echo esc_attr( netelly_t( 'menu_close' ) ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( netelly_t( 'menu_open' ) ); ?></span>
				<span class="menu-toggle__line menu-toggle__line--1" aria-hidden="true"></span>
				<span class="menu-toggle__line menu-toggle__line--2" aria-hidden="true"></span>
			</button>
		</div>
	</div>
</header>

<div class="sp-menu" id="sp-menu" hidden>
	<div class="sp-menu__inner">
		<?php if ( $menu ) : ?>
			<nav class="sp-menu__nav" aria-label="<?php echo esc_attr( netelly_t( 'nav_label' ) ); ?>">
				<ul>
					<?php foreach ( $menu as $i => $item ) : ?>
						<li class="sp-menu__item" style="--i:<?php echo (int) $i; ?>">
							<span class="mask"><?php echo netelly_menu_link( $item, 'sp-menu__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php if ( $item['sub'] ) : ?>
								<span class="sp-menu__sub" aria-hidden="true"><?php echo esc_html( $item['sub'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
		<div class="sp-menu__foot">
			<?php echo netelly_button( netelly_t( 'header_contact' ), $contact, 'primary', array( 'class' => 'sp-menu__cta' . ( netelly_is_latin( netelly_t( 'header_contact' ) ) ? ' is-latin' : '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<ul class="sp-menu__sns">
				<?php foreach ( netelly_socials() as $sns ) : ?>
					<li><a href="<?php echo esc_url( $sns['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $sns['label'] ); ?> <span class="ext" aria-hidden="true">↗</span><span class="screen-reader-text"><?php echo esc_html( netelly_t( 'new_tab' ) ); ?></span></a></li>
				<?php endforeach; ?>
			</ul>
			<?php echo netelly_lang_switch( 'sp-menu__lang' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php echo netelly_theme_switch( 'sp-menu__theme' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</div>
</div>
<?php if ( 'light' === $variant ) : ?>
	<div class="header-spacer" aria-hidden="true"></div>
<?php endif; ?>

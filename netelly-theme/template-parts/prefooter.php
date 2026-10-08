<?php
/**
 * Prefooter: SNS bar + Sponsor / For Creators cards (all pages).
 *
 * @package netelly
 */

$sponsor = netelly_opt( 'pf_sponsor_link' );
$cards   = array(
	array(
		'title' => (string) netelly_opt( 'pf_sponsor_title' ),
		'badge' => '',
		'text'  => (string) netelly_opt( 'pf_sponsor_text' ),
		'url'   => is_array( $sponsor ) && ! empty( $sponsor['url'] ) ? $sponsor['url'] : netelly_page_url( 'contact' ),
		'blank' => false,
	),
	array(
		'title' => (string) netelly_opt( 'pf_creators_title' ),
		'badge' => (string) netelly_opt( 'pf_creators_badge' ),
		'text'  => (string) netelly_opt( 'pf_creators_text' ),
		'url'   => netelly_fund_url(),
		'blank' => true,
	),
);
?>
<aside class="prefooter is-dark">
	<?php $socials = netelly_socials(); ?>
	<?php if ( $socials ) : ?>
		<ul class="sns-bar">
			<?php foreach ( $socials as $sns ) : ?>
				<li><a class="sns-bar__link" href="<?php echo esc_url( $sns['url'] ); ?>" target="_blank" rel="noopener"><span class="sns-bar__label"><?php echo esc_html( $sns['label'] ); ?></span><span class="sns-bar__arrow arrow" aria-hidden="true">↗</span><span class="screen-reader-text"><?php echo esc_html( netelly_t( 'new_tab' ) ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<div class="pf-cards">
		<?php foreach ( $cards as $card ) : ?>
			<?php
			if ( '' === $card['title'] ) {
				continue;
			}
			?>
			<a class="pf-card"<?php echo netelly_link_attrs( $card['url'], $card['blank'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<span class="pf-card__body">
					<span class="pf-card__head">
						<span class="pf-card__title<?php echo netelly_is_latin( $card['title'] ) ? ' is-latin' : ''; ?>"><?php echo esc_html( $card['title'] ); ?></span>
						<?php if ( $card['badge'] ) : ?>
							<span class="pf-card__badge"><?php echo esc_html( $card['badge'] ); ?></span>
						<?php endif; ?>
					</span>
					<?php if ( $card['text'] ) : ?>
						<span class="pf-card__text"><?php echo esc_html( $card['text'] ); ?></span>
					<?php endif; ?>
				</span>
				<span class="pf-card__arrow arrow" aria-hidden="true">→</span>
				<?php echo netelly_new_tab_note( $card['url'], $card['blank'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
		<?php endforeach; ?>
	</div>
</aside>

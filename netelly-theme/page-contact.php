<?php
/**
 * Template Name: お問い合わせ
 *
 * Contact (design #p11): lead + email / tel on the left, Snow Monkey Forms on the right.
 *
 * @package netelly
 */

get_header();
$f     = static fn( $name ) => netelly_field( $name );
$form  = (int) $f( 'form' );
$email = (string) netelly_opt( 'contact_email' );
$tel   = (string) netelly_opt( 'contact_tel' );
?>
<main id="main" class="page-contact">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
	<section class="section section--tight">
		<div class="contact">
			<div class="contact__info">
				<h2 class="contact__heading"><?php echo netelly_br( (string) $f( 'lead_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="contact__lead"><?php echo esc_html( (string) $f( 'lead_text' ) ); ?></p>
				<?php if ( $email ) : ?>
					<p class="contact__item contact__item--first">
						<span class="contact__label"><?php echo esc_html( netelly_t( 'email_label' ) ); ?></span>
						<a class="contact__value" href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
					</p>
				<?php endif; ?>
				<?php if ( $tel ) : ?>
					<p class="contact__item">
						<span class="contact__label"><?php echo esc_html( netelly_t( 'tel_label' ) ); ?></span>
						<a class="contact__value" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $tel ) ); ?>"><?php echo esc_html( $tel ); ?></a>
						<span class="contact__hours"><?php echo esc_html( (string) netelly_opt( 'contact_hours' ) ); ?></span>
					</p>
				<?php endif; ?>
			</div>
			<div class="contact__form">
				<?php
				if ( $form && shortcode_exists( 'snow_monkey_form' ) ) {
					echo do_shortcode( '[snow_monkey_form id="' . $form . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</div>
		</div>
	</section>
	<div class="end-spacer"></div>
</main>
<?php
get_footer();

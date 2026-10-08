<?php
/**
 * Template Name: 代表メッセージ
 *
 * CEO message (design #p03): portrait sticky on the left, full text from サイト設定.
 * The body is the client's original text (「ことをを」 kept as-is until confirmed).
 *
 * @package netelly
 */

get_header();
?>
<main id="main" class="page-message">
	<?php get_template_part( 'template-parts/page-hero' ); ?>
	<section class="section">
		<div class="message">
			<div class="message__photo"><?php echo netelly_media( netelly_opt( 'msg_photo' ), '4/5', array( 'sizes' => '(max-width: 768px) 100vw, 40vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="message__text">
				<p class="message__label"><?php echo esc_html( (string) netelly_opt( 'msg_label' ) ); ?></p>
				<h2 class="message__heading"><?php echo netelly_br( (string) netelly_opt( 'msg_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<div class="message__body"><?php echo wp_kses_post( (string) netelly_opt( 'msg_body' ) ); ?></div>
				<p class="message__sign">
					<span class="message__role"><?php echo esc_html( (string) netelly_opt( 'msg_signature_role' ) ); ?></span>
					<span class="message__name"><?php echo esc_html( (string) netelly_opt( 'msg_name' ) ); ?></span>
					<span class="message__name-en" lang="en"><?php echo esc_html( (string) netelly_opt( 'msg_name_en' ) ); ?></span>
				</p>
			</div>
		</div>
	</section>
	<div class="end-spacer"></div>
	<?php get_template_part( 'template-parts/careers-band' ); ?>
</main>
<?php
get_footer();

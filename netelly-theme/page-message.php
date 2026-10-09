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

	<?php
	// CEO profile: the visible counterpart of the Person / ProfilePage structured data (inc/seo.php).
	$ceo_name = (string) netelly_opt( 'msg_name' );
	$ceo_en   = (string) netelly_opt( 'msg_name_en' );
	$ceo_kana = (string) netelly_opt( 'ceo_name_kana' );
	$career   = array_filter( (array) netelly_opt( 'ceo_career' ), static fn( $r ) => is_array( $r ) && ! empty( $r['text'] ) );
	$links    = array_filter( (array) netelly_opt( 'ceo_links' ), static fn( $l ) => is_array( $l ) && ! empty( $l['url'] ) );
	?>
	<section class="section" id="profile">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => 'PROFILE', 'heading' => netelly_t( 'ceo_profile' ) ) ); ?>
		<dl class="table ceo-profile">
			<div class="table__row">
				<dt class="table__label"><?php echo esc_html( netelly_t( 'name_label' ) ); ?></dt>
				<dd class="table__value"><?php echo esc_html( $ceo_name ); ?><?php echo $ceo_kana ? '（' . esc_html( $ceo_kana ) . '）' : ''; ?><?php echo $ceo_en ? ' <span lang="en">' . esc_html( ucwords( strtolower( $ceo_en ) ) ) . '</span>' : ''; ?></dd>
			</div>
			<div class="table__row">
				<dt class="table__label"><?php echo esc_html( netelly_t( 'role_label' ) ); ?></dt>
				<dd class="table__value"><?php echo esc_html( (string) netelly_opt( 'msg_signature_role' ) ); ?></dd>
			</div>
			<?php if ( netelly_opt( 'ceo_bio' ) ) : ?>
				<div class="table__row">
					<dt class="table__label"><?php echo esc_html( netelly_t( 'ceo_profile' ) ); ?></dt>
					<dd class="table__value"><?php echo esc_html( (string) netelly_opt( 'ceo_bio' ) ); ?></dd>
				</div>
			<?php endif; ?>
			<?php if ( $career ) : ?>
				<div class="table__row">
					<dt class="table__label"><?php echo esc_html( netelly_t( 'career_label' ) ); ?></dt>
					<dd class="table__value">
						<ol class="ceo-career">
							<?php foreach ( $career as $row ) : ?>
								<li><span class="ceo-career__year"><?php echo esc_html( (string) $row['year'] ); ?></span><span><?php echo esc_html( (string) $row['text'] ); ?></span></li>
							<?php endforeach; ?>
						</ol>
					</dd>
				</div>
			<?php endif; ?>
			<?php if ( $links ) : ?>
				<div class="table__row">
					<dt class="table__label"><?php echo esc_html( netelly_t( 'links_label' ) ); ?></dt>
					<dd class="table__value ceo-links">
						<?php foreach ( $links as $l ) : ?>
							<a class="text-link" href="<?php echo esc_url( $l['url'] ); ?>" target="_blank" rel="noopener me"><?php echo esc_html( (string) ( $l['label'] ?: $l['url'] ) ); ?> <span class="arrow" aria-hidden="true">↗</span><span class="screen-reader-text"><?php echo esc_html( netelly_t( 'new_tab' ) ); ?></span></a>
						<?php endforeach; ?>
					</dd>
				</div>
			<?php endif; ?>
		</dl>
	</section>
	<div class="end-spacer"></div>
	<?php get_template_part( 'template-parts/careers-band' ); ?>
</main>
<?php
get_footer();

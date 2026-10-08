<?php
/**
 * Top page (design #p01). All copy and media come from サイト設定 (per language).
 *
 * @package netelly
 */

get_header();

$latest = get_posts(
	array(
		'numberposts' => 3,
		'post_type'   => 'post',
	)
);
?>
<main id="main" class="page-top">

	<?php /* ---------- Hero: video / poster, horizon at 60% (SP 50%), copy below the line ---------- */ ?>
	<section class="hero is-dark" aria-labelledby="hero-title">
		<div class="hero__media" data-parallax>
			<?php
			$poster = (int) netelly_opt( 'hero_poster' );
			$video  = (int) netelly_opt( 'hero_video' );
			if ( $poster ) {
				echo wp_get_attachment_image(
					$poster,
					'full',
					false,
					array(
						'class'         => 'hero__poster',
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '100vw',
						'alt'           => '',
					)
				);
			}
			if ( $video ) :
				?>
				<video class="hero__video" muted loop playsinline preload="none" aria-hidden="true" data-src="<?php echo esc_url( (string) wp_get_attachment_url( $video ) ); ?>"<?php echo $poster ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $poster, 'full' ) ) . '"' : ''; ?>></video>
			<?php endif; ?>
		</div>
		<div class="hero__shade" aria-hidden="true"></div>
		<div class="hero__line horizon" aria-hidden="true"></div>
		<div class="hero__body">
			<div class="hero__text">
				<p class="hero__label"><span class="mask"><span><?php echo esc_html( (string) netelly_opt( 'hero_label' ) ); ?></span></span></p>
				<h1 class="hero__copy" id="hero-title">
					<?php foreach ( preg_split( '/\R/', (string) netelly_opt( 'hero_copy' ) ) as $line ) : ?>
						<span class="mask hero__line-text"><span><?php echo esc_html( $line ); ?></span></span>
					<?php endforeach; ?>
				</h1>
				<p class="hero__lead"><span class="mask"><span><?php echo esc_html( (string) netelly_opt( 'hero_lead' ) ); ?></span></span></p>
			</div>
			<div class="btn-group hero__cta">
				<?php echo netelly_link_button( netelly_opt( 'hero_cta1' ), 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo netelly_link_button( netelly_opt( 'hero_cta2' ), 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
		<?php if ( $video ) : ?>
			<button class="hero__toggle" type="button" hidden aria-pressed="false" data-label-pause="<?php echo esc_attr( netelly_t( 'video_pause' ) ); ?>" data-label-play="<?php echo esc_attr( netelly_t( 'video_play' ) ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( netelly_t( 'video_pause' ) ); ?></span>
				<span class="hero__toggle-icon" aria-hidden="true"></span>
			</button>
		<?php endif; ?>
	</section>

	<?php /* ---------- News band: latest post ---------- */ ?>
	<?php if ( $latest ) : ?>
		<?php
		$band_id  = $latest[0]->ID;
		$band_ext = (string) netelly_field( 'external_url', $band_id );
		$band_url = $band_ext ? $band_ext : get_permalink( $band_id );
		?>
		<a class="row-link news-band"<?php echo netelly_link_attrs( $band_url, (bool) $band_ext ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<span class="news-band__meta">
				<span class="news-band__label"><?php echo esc_html( netelly_t( 'news_label' ) ); ?></span><span class="news-band__sep" aria-hidden="true"> · </span><span class="news-band__date"><?php echo esc_html( get_the_date( 'Y.m.d', $band_id ) ); ?></span>
			</span>
			<span class="news-band__title"><?php echo esc_html( get_the_title( $band_id ) ); ?></span>
			<span class="news-band__arrow arrow" aria-hidden="true"><?php echo $band_ext ? '↗' : '→'; ?></span>
		</a>
	<?php endif; ?>

	<?php /* ---------- Vertical short drama: copy + three 9:16 frames (middle one lowered) ---------- */ ?>
	<section class="short-drama">
		<div class="short-drama__text">
			<p class="short-drama__label"><?php echo esc_html( (string) netelly_opt( 'sd_label' ) ); ?></p>
			<h2 class="short-drama__heading"><?php echo netelly_br( (string) netelly_opt( 'sd_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p class="short-drama__body"><?php echo esc_html( (string) netelly_opt( 'sd_text' ) ); ?></p>
			<?php $sd_links = (array) netelly_opt( 'sd_links' ); ?>
			<?php if ( $sd_links ) : ?>
				<div class="short-drama__links">
					<?php foreach ( $sd_links as $row ) : ?>
						<?php echo netelly_text_link( $row['link'] ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="short-drama__frames" data-stagger="120">
			<?php
			$items = array_slice( array_pad( (array) netelly_opt( 'sd_items' ), 3, array() ), 0, 3 );
			foreach ( $items as $i => $item ) :
				$link  = $item['link'] ?? null;
				$vid   = (int) ( $item['video'] ?? 0 );
				$tag   = is_array( $link ) && ! empty( $link['url'] ) ? 'a' : 'div';
				$attrs = 'a' === $tag ? netelly_link_attrs( $link['url'], '_blank' === ( $link['target'] ?? '' ) ) . ' aria-label="' . esc_attr( $link['title'] ?: ( 'Short drama ' . ( $i + 1 ) ) ) . '"' : '';
				?>
				<<?php echo esc_html( $tag ); ?> class="short-drama__frame"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<?php echo netelly_media( $item['image'] ?? 0, '9/16', array( 'sizes' => '(max-width: 768px) 33vw, 20vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $vid ) : ?>
						<video class="short-drama__video" muted loop playsinline preload="none" aria-hidden="true" data-src="<?php echo esc_url( (string) wp_get_attachment_url( $vid ) ); ?>"></video>
					<?php endif; ?>
				</<?php echo esc_html( $tag ); ?>>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---------- Black band: ORIGINALS (4) + MEDIA (3 episodes) ---------- */ ?>
	<div class="top-band is-dark">
		<section class="top-band__block">
			<?php
			get_template_part(
				'template-parts/section-head',
				null,
				array(
					'label'   => (string) netelly_opt( 'orig_label' ),
					'heading' => (string) netelly_opt( 'orig_heading' ),
					'link'    => netelly_opt( 'orig_link' ),
				)
			);
			?>
			<div class="grid-works grid-works--4" data-stagger="60">
				<?php foreach ( (array) netelly_opt( 'orig_works' ) as $wid ) : ?>
					<?php get_template_part( 'template-parts/card-work', null, array( 'id' => (int) $wid ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>

		<?php
		$media_work = (int) netelly_opt( 'media_work' );
		$episodes   = $media_work ? array_slice( (array) netelly_field( 'episodes', $media_work ), 0, 3 ) : array();
		?>
		<?php if ( $episodes ) : ?>
			<section class="top-band__block">
				<div class="section-head section-head--media">
					<div class="section-head__text">
						<?php echo netelly_logo( 'section-head__logo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<h2 class="section-head__media-label"><?php echo esc_html( (string) netelly_opt( 'media_label' ) ); ?></h2>
					</div>
					<?php echo netelly_text_link( netelly_opt( 'media_link' ), 'section-head__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
				<div class="grid-media" data-stagger="60">
					<?php foreach ( $episodes as $ep ) : ?>
						<?php $ep_url = ! empty( $ep['url'] ) ? $ep['url'] : get_permalink( $media_work ); ?>
						<a class="card card-episode"<?php echo netelly_link_attrs( $ep_url ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<?php echo netelly_media( $ep['thumb'] ?? 0, '16/9', array( 'sizes' => '(max-width: 768px) 100vw, 50vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="card-episode__meta"><?php echo esc_html( get_the_title( $media_work ) . ' · ' . ( $ep['no'] ?? '' ) ); ?></span>
							<span class="card-episode__title"><?php echo esc_html( (string) ( $ep['title'] ?? '' ) ); ?></span>
							<?php echo netelly_new_tab_note( $ep_url ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	</div>

	<?php /* ---------- Business: lead + four cards ---------- */ ?>
	<section class="section">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => (string) netelly_opt( 'biz_label' ),
				'heading' => (string) netelly_opt( 'biz_heading' ),
				'link'    => netelly_opt( 'biz_link' ),
			)
		);
		?>
		<p class="lead-text"><?php echo esc_html( (string) netelly_opt( 'biz_lead' ) ); ?></p>
		<div class="grid-biz" data-stagger="60">
			<?php foreach ( (array) netelly_opt( 'biz_items' ) as $biz ) : ?>
				<?php
				$is_fund = ! empty( $biz['is_fund'] );
				$burl    = $is_fund ? netelly_fund_url() : ( $biz['link']['url'] ?? '' );
				?>
				<a class="card card-biz"<?php echo netelly_link_attrs( $burl, $is_fund ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<?php echo netelly_media( $biz['image'] ?? 0, '4/5', array( 'sizes' => '(max-width: 768px) 50vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="card-biz__body">
						<span class="card-biz__num"><?php echo esc_html( (string) ( $biz['num'] ?? '' ) ); ?></span>
						<span class="card-biz__en"><?php echo esc_html( (string) ( $biz['en'] ?? '' ) ); ?></span>
						<span class="card-biz__title"><?php echo esc_html( (string) ( $biz['title'] ?? '' ) ); ?><?php echo $is_fund ? ' <span class="ext" aria-hidden="true">↗</span>' : ''; ?></span>
						<span class="card-biz__text"><?php echo esc_html( (string) ( $biz['text'] ?? '' ) ); ?></span>
					</span>
					<?php echo netelly_new_tab_note( $burl, $is_fund ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---------- CEO message excerpt ---------- */ ?>
	<section class="section">
		<div class="top-message">
			<?php echo netelly_media( netelly_opt( 'msg_photo' ), '4/5', array( 'class' => 'top-message__photo', 'sizes' => '(max-width: 768px) 100vw, 40vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="top-message__text">
				<p class="top-message__label"><?php echo esc_html( (string) netelly_opt( 'tm_label' ) ); ?></p>
				<h2 class="top-message__heading"><?php echo netelly_br( (string) netelly_opt( 'msg_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="top-message__body"><?php echo esc_html( (string) netelly_opt( 'msg_excerpt' ) ); ?></p>
				<p class="signature">
					<span class="signature__role"><?php echo esc_html( (string) netelly_opt( 'msg_role' ) ); ?></span>
					<span class="signature__name"><?php echo esc_html( (string) netelly_opt( 'msg_name' ) ); ?></span>
				</p>
				<?php echo netelly_text_link( netelly_opt( 'tm_link' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
	</section>

	<?php /* ---------- News (latest 3) ---------- */ ?>
	<section class="section">
		<?php
		get_template_part(
			'template-parts/section-head',
			null,
			array(
				'label'   => (string) netelly_opt( 'tn_label' ),
				'heading' => (string) netelly_opt( 'tn_heading' ),
				'link'    => netelly_opt( 'tn_link' ),
			)
		);
		?>
		<div class="list-news" data-stagger="60">
			<?php foreach ( $latest as $p ) : ?>
				<?php get_template_part( 'template-parts/row-news', null, array( 'id' => $p->ID ) ); ?>
			<?php endforeach; ?>
		</div>
	</section>
	<div class="end-spacer"></div>

	<?php get_template_part( 'template-parts/careers-band' ); ?>
</main>
<?php
get_footer();

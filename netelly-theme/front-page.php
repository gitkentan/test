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
	<?php $hero_copy = (string) netelly_opt( 'hero_copy' ); ?>
	<?php // Latin-only copy gets the editorial display treatment (Instrument Serif, second line italic). ?>
	<section class="hero is-dark<?php echo netelly_is_latin( str_replace( "\n", ' ', $hero_copy ) ) ? ' hero--display' : ''; ?>" aria-labelledby="hero-title">
		<div class="hero__media" data-parallax>
			<?php
			$poster = (int) netelly_opt( 'hero_poster' );
			$video  = (int) netelly_opt( 'hero_video' );
			$video_sp = (int) netelly_opt( 'hero_video_sp' );
			$video_hd = (int) netelly_opt( 'hero_video_hd' );
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
				<video class="hero__video" muted loop playsinline preload="none" aria-hidden="true" data-src="<?php echo esc_url( (string) wp_get_attachment_url( $video ) ); ?>"<?php echo $video_hd ? ' data-src-hd="' . esc_url( (string) wp_get_attachment_url( $video_hd ) ) . '"' : ''; ?><?php echo $video_sp ? ' data-src-sp="' . esc_url( (string) wp_get_attachment_url( $video_sp ) ) . '"' : ''; ?><?php echo $poster ? ' poster="' . esc_url( (string) wp_get_attachment_image_url( $poster, 'full' ) ) . '"' : ''; ?>></video>
			<?php endif; ?>
		</div>
		<div class="hero__shade" aria-hidden="true"></div>
		<div class="hero__line horizon" aria-hidden="true"></div>
		<div class="hero__body">
			<div class="hero__text">
				<?php $i = 0; // Intro stagger order (--i, README 1). ?>
				<p class="hero__label"><span class="mask" style="--i:<?php echo (int) $i++; ?>"><span><?php echo esc_html( (string) netelly_opt( 'hero_label' ) ); ?></span></span></p>
				<h1 class="hero__copy<?php echo netelly_is_latin( str_replace( "\n", ' ', $hero_copy ) ) ? ' is-latin' : ''; ?>" id="hero-title">
					<?php foreach ( preg_split( '/\R/', $hero_copy ) as $line ) : ?>
						<span class="mask hero__line-text" style="--i:<?php echo (int) $i++; ?>"><span><?php echo esc_html( $line ); ?></span></span>
					<?php endforeach; ?>
				</h1>
				<p class="hero__lead"><span class="mask" style="--i:<?php echo (int) $i++; ?>"><span><?php echo esc_html( (string) netelly_opt( 'hero_lead' ) ); ?></span></span></p>
			</div>
			<div class="btn-group hero__cta" style="--i:<?php echo (int) $i; ?>">
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

	<?php /* ---------- Numbers band (サイト設定 › トップ：数字の帯; hidden until real figures are entered) ---------- */ ?>
	<?php $stats = array_filter( (array) netelly_opt( 'stats' ), static fn( $r ) => is_array( $r ) && '' !== trim( (string) ( $r['value'] ?? '' ) ) ); ?>
	<?php if ( $stats ) : ?>
		<section class="stats" aria-label="<?php echo esc_attr( (string) netelly_opt( 'stats_label' ) ); ?>">
			<?php if ( netelly_opt( 'stats_label' ) ) : ?>
				<p class="stats__label"><?php echo esc_html( (string) netelly_opt( 'stats_label' ) ); ?></p>
			<?php endif; ?>
			<dl class="stats__list" data-stagger="80">
				<?php foreach ( $stats as $st ) : ?>
					<?php
					$val = trim( (string) $st['value'] );
					$num = str_replace( ',', '', $val );
					?>
					<?php // Long figures (e.g. 4,000,000) get a smaller size so four fit in a row. ?>
					<div class="stat<?php echo mb_strlen( $val . ( $st['unit'] ?? '' ) ) > 6 ? ' is-long' : ''; ?>">
						<dt class="stat__label"><?php echo esc_html( (string) ( $st['title'] ?? '' ) ); ?></dt>
						<dd class="stat__value"<?php echo ctype_digit( $num ) ? ' data-count="' . esc_attr( $num ) . '"' : ''; ?>><span class="stat__digits" data-digits><?php echo esc_html( $val ); ?></span><?php if ( ! empty( $st['unit'] ) ) : ?><span class="stat__unit"><?php echo esc_html( (string) $st['unit'] ); ?></span><?php endif; ?></dd>
						<?php if ( ! empty( $st['note'] ) ) : ?>
							<dd class="stat__note"><?php echo esc_html( (string) $st['note'] ); ?></dd>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>

	<?php /* ---------- Statement (サイト設定 › トップ：ステートメント): lights up as it scrolls by ---------- */ ?>
	<?php
	$manifesto = netelly_opt( 'manifesto_text' );
	if ( null === $manifesto ) { // Never saved (site seeded before this field existed): use the company line.
		$manifesto = 'en' === netelly_lang() ? 'Netelly is a global entertainment company creating stories that travel across countries and languages.' : 'Netellyは、国や言語を越えて届く物語をつくるグローバル・エンターテインメント企業です。';
	}
	$manifesto = trim( (string) $manifesto );
	?>
	<?php if ( '' !== $manifesto ) : ?>
		<?php
		$m_split = netelly_split_words( $manifesto );
		$m_label = netelly_opt( 'manifesto_label' );
		$m_label = null === $m_label ? 'WHO WE ARE' : (string) $m_label;
		$m_link  = netelly_opt( 'manifesto_link' );
		$m_link  = null === $m_link ? netelly_opt( 'hero_cta1' ) : $m_link;
		?>
		<section class="manifesto" data-manifesto style="--n:<?php echo (int) $m_split['count']; ?>">
			<?php if ( $m_label ) : ?>
				<p class="manifesto__label"><?php echo esc_html( $m_label ); ?></p>
			<?php endif; ?>
			<p class="manifesto__text<?php echo netelly_is_latin( $manifesto ) ? ' is-latin' : ''; ?>">
				<span class="screen-reader-text"><?php echo esc_html( $manifesto ); ?></span>
				<span aria-hidden="true"><?php echo $m_split['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per unit. ?></span>
			</p>
			<?php echo netelly_text_link( $m_link ? $m_link : null, 'manifesto__link' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</section>
	<?php endif; ?>

	<?php /* ---------- Business: lead + four stacked sections (no works on the top page) ---------- */ ?>
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
		<div class="biz-list" data-stagger="80">
			<?php foreach ( (array) netelly_opt( 'biz_items' ) as $biz ) : ?>
				<?php
				$is_fund = ! empty( $biz['is_fund'] );
				$burl    = $is_fund ? netelly_fund_url() : ( $biz['link']['url'] ?? '' );
				?>
				<?php $bimg = ! empty( $biz['image'] ) ? (string) wp_get_attachment_image_url( (int) $biz['image'], 'medium_large' ) : ''; ?>
				<a class="biz-item<?php echo $bimg ? '' : ' is-textonly'; ?>"<?php echo $bimg ? ' data-preview="' . esc_url( $bimg ) . '"' : ''; ?><?php echo netelly_link_attrs( $burl, $is_fund ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<span class="biz-item__num"><?php echo esc_html( (string) ( $biz['num'] ?? '' ) ); ?></span>
					<span class="biz-item__head">
						<span class="biz-item__en"><?php echo esc_html( (string) ( $biz['en'] ?? '' ) ); ?></span>
						<span class="biz-item__title"><?php echo esc_html( (string) ( $biz['title'] ?? '' ) ); ?></span>
					</span>
					<span class="biz-item__text"><?php echo esc_html( (string) ( $biz['text'] ?? '' ) ); ?></span>
					<span class="biz-item__media"><?php echo netelly_media( $biz['image'] ?? 0, '4/3', array( 'sizes' => '(max-width: 768px) 100vw, 25vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="biz-item__arrow arrow" aria-hidden="true"><?php echo $is_fund ? '↗' : '→'; ?></span>
					<?php echo netelly_new_tab_note( $burl, $is_fund ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---------- Marquee (サイト設定 › トップ：流れる帯; decorative, hidden when empty) ---------- */ ?>
	<?php $marquee = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) netelly_opt( 'marquee_text' ) ) ) ) ); ?>
	<?php if ( $marquee ) : ?>
		<div class="marquee" aria-hidden="true">
			<div class="marquee__track">
				<?php for ( $r = 0; $r < 2; $r++ ) : ?>
					<?php foreach ( $marquee as $item ) : ?>
						<span class="marquee__item"><?php echo esc_html( $item ); ?><span class="marquee__dot"></span></span>
					<?php endforeach; ?>
				<?php endfor; ?>
			</div>
		</div>
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

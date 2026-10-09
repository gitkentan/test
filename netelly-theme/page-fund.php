<?php
/**
 * Template Name: Creators Fund
 *
 * Creators Fund (/fund/): full-screen light hero, scroll-lit statement, what we offer,
 * roles marquee, flow, FAQ and the application form (Snow Monkey Forms).
 * Always dark, in every colour mode. Texts: 固定ページ › Creators Fund (inc/fund.php defaults).
 *
 * @package netelly
 */

get_header();
$f      = static fn( $name ) => netelly_field( $name );
$form   = (int) $f( 'form' );
$offers = array_values( (array) $f( 'offers' ) );
$steps  = array_values( (array) $f( 'steps' ) );
$faqs   = array_values( (array) $f( 'faqs' ) );
$roles  = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $f( 'who_roles' ) ) ) ) );
$title  = (string) $f( 'hero_title' );
$image  = (int) $f( 'hero_image' );
$video  = (int) $f( 'hero_video' );
$st     = trim( (string) $f( 'st_text' ) );
?>
<main id="main" class="page-fund">

	<?php /* ---------- Hero: drifting gold light (or image / video), giant title, apply ---------- */ ?>
	<section class="fund-hero" aria-labelledby="fund-title">
		<div class="fund-hero__bg" aria-hidden="true">
			<?php if ( $image ) : ?>
				<?php echo wp_get_attachment_image( $image, 'full', false, array( 'class' => 'fund-hero__img', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw', 'alt' => '' ) ); ?>
			<?php endif; ?>
			<?php if ( $video ) : ?>
				<video class="fund-hero__video" muted loop playsinline autoplay preload="metadata" src="<?php echo esc_url( (string) wp_get_attachment_url( $video ) ); ?>"></video>
			<?php endif; ?>
			<span class="fund-hero__light fund-hero__light--a"></span>
			<span class="fund-hero__light fund-hero__light--b"></span>
			<span class="fund-hero__light fund-hero__light--c"></span>
			<span class="fund-hero__grain"></span>
		</div>
		<div class="fund-hero__body">
			<p class="fund-hero__label"><?php echo esc_html( (string) $f( 'hero_label' ) ); ?></p>
			<h1 class="fund-hero__title" id="fund-title" lang="en">
				<span class="screen-reader-text"><?php echo esc_html( $title ); ?></span>
				<span class="fund-hero__letters" aria-hidden="true">
					<?php
					$n = 0;
					foreach ( preg_split( '/\s+/', $title ) as $w => $word ) :
						?>
						<span class="fund-hero__word">
							<?php foreach ( mb_str_split( $word ) as $ch ) : ?>
								<span class="fund-hero__ch" style="--i:<?php echo (int) $n++; ?>"><?php echo esc_html( $ch ); ?></span>
							<?php endforeach; ?>
						</span>
					<?php endforeach; ?>
				</span>
			</h1>
			<div class="fund-hero__foot">
				<p class="fund-hero__copy"><?php echo netelly_br( (string) $f( 'hero_copy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				<div class="fund-hero__side">
					<p class="fund-hero__lead"><?php echo esc_html( (string) $f( 'hero_lead' ) ); ?></p>
					<?php if ( $form ) : ?>
						<a class="fund-btn" href="#apply"><span><?php echo esc_html( (string) $f( 'hero_cta' ) ); ?></span><span class="fund-btn__arrow" aria-hidden="true">↓</span></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<span class="fund-hero__scroll" aria-hidden="true">SCROLL</span>
	</section>

	<?php /* ---------- Statement (scroll-lit per character) ---------- */ ?>
	<?php if ( '' !== $st ) : ?>
		<?php $split = netelly_split_words( $st ); ?>
		<section class="manifesto fund-statement" data-manifesto style="--n:<?php echo (int) $split['count']; ?>">
			<p class="manifesto__label"><?php echo esc_html( (string) $f( 'st_label' ) ); ?></p>
			<p class="manifesto__text<?php echo netelly_is_latin( $st ) ? ' is-latin' : ''; ?>">
				<span class="screen-reader-text"><?php echo esc_html( $st ); ?></span>
				<span aria-hidden="true"><?php echo $split['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per unit. ?></span>
			</p>
		</section>
	<?php endif; ?>

	<?php /* ---------- What we offer ---------- */ ?>
	<?php if ( $offers ) : ?>
		<section class="fund-sec">
			<div class="fund-sec__head">
				<p class="fund-sec__label" lang="en"><?php echo esc_html( (string) $f( 'offer_label' ) ); ?></p>
				<h2 class="fund-sec__heading"><?php echo netelly_br( (string) $f( 'offer_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<ol class="fund-offers" data-stagger="80">
				<?php foreach ( $offers as $i => $o ) : ?>
					<li class="fund-offer">
						<span class="fund-offer__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<?php if ( ! empty( $o['tag'] ) ) : ?>
							<span class="fund-offer__tag"><?php echo esc_html( (string) $o['tag'] ); ?></span>
						<?php endif; ?>
						<p class="fund-offer__en" lang="en"><?php echo esc_html( (string) ( $o['en'] ?? '' ) ); ?></p>
						<h3 class="fund-offer__title"><?php echo esc_html( (string) ( $o['title'] ?? '' ) ); ?></h3>
						<p class="fund-offer__text"><?php echo esc_html( (string) ( $o['text'] ?? '' ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php /* ---------- Who it is for: roles marquee ---------- */ ?>
	<?php if ( $roles ) : ?>
		<section class="fund-who">
			<div class="fund-sec__head fund-who__head">
				<p class="fund-sec__label" lang="en"><?php echo esc_html( (string) $f( 'who_label' ) ); ?></p>
				<h2 class="fund-sec__heading"><?php echo netelly_br( (string) $f( 'who_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<div class="marquee fund-roles" aria-hidden="true">
				<div class="marquee__track">
					<?php for ( $r = 0; $r < 2; $r++ ) : ?>
						<?php foreach ( $roles as $k => $role ) : ?>
							<span class="marquee__item fund-roles__item<?php echo $k % 2 ? ' is-outline' : ''; ?>"><?php echo esc_html( $role ); ?><span class="fund-roles__star">✦</span></span>
						<?php endforeach; ?>
					<?php endfor; ?>
				</div>
			</div>
			<p class="screen-reader-text"><?php echo esc_html( implode( ' / ', $roles ) ); ?></p>
			<?php if ( $f( 'who_note' ) ) : ?>
				<p class="fund-who__note"><?php echo esc_html( (string) $f( 'who_note' ) ); ?></p>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php /* ---------- Flow ---------- */ ?>
	<?php if ( $steps ) : ?>
		<section class="fund-sec">
			<div class="fund-sec__head">
				<p class="fund-sec__label" lang="en"><?php echo esc_html( (string) $f( 'flow_label' ) ); ?></p>
				<h2 class="fund-sec__heading"><?php echo netelly_br( (string) $f( 'flow_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<ol class="fund-flow" style="--steps:<?php echo (int) count( $steps ); ?>" data-stagger="90">
				<?php foreach ( $steps as $i => $s ) : ?>
					<li class="fund-step">
						<span class="fund-step__dot" aria-hidden="true"></span>
						<span class="fund-step__num" lang="en">STEP <?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="fund-step__title"><?php echo esc_html( (string) ( $s['title'] ?? '' ) ); ?></h3>
						<p class="fund-step__text"><?php echo esc_html( (string) ( $s['text'] ?? '' ) ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php /* ---------- FAQ ---------- */ ?>
	<?php if ( $faqs ) : ?>
		<section class="fund-sec fund-faq">
			<div class="fund-sec__head">
				<p class="fund-sec__label" lang="en"><?php echo esc_html( (string) $f( 'faq_label' ) ); ?></p>
				<h2 class="fund-sec__heading"><?php echo netelly_br( (string) $f( 'faq_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<div class="fund-faq__list">
				<?php foreach ( $faqs as $q ) : ?>
					<details class="fund-qa">
						<summary class="fund-qa__q"><span><?php echo esc_html( (string) ( $q['q'] ?? '' ) ); ?></span><span class="fund-qa__icon" aria-hidden="true"></span></summary>
						<p class="fund-qa__a"><?php echo esc_html( (string) ( $q['a'] ?? '' ) ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---------- Apply ---------- */ ?>
	<?php if ( $form && 'snow-monkey-forms' === get_post_type( $form ) ) : ?>
		<section class="fund-apply" id="apply">
			<div class="fund-apply__intro">
				<p class="fund-sec__label" lang="en"><?php echo esc_html( (string) $f( 'apply_label' ) ); ?></p>
				<h2 class="fund-apply__heading"><?php echo netelly_br( (string) $f( 'apply_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="fund-apply__text"><?php echo esc_html( (string) $f( 'apply_text' ) ); ?></p>
			</div>
			<?php
			$privacy = netelly_find_by_slug( 'page', 'privacy', netelly_lang() );
			$data    = array(
				'privacy-url'    => $privacy ? get_permalink( $privacy ) : '',
				'privacy-title'  => $privacy ? get_the_title( $privacy ) : '',
				'required'       => netelly_t( 'form_required' ),
				'optional'       => netelly_t( 'form_optional' ),
				'error-required' => netelly_t( 'form_error_required' ),
				'error-email'    => netelly_t( 'form_error_email' ),
				'sending'        => netelly_t( 'form_sending' ),
			);
			?>
			<div class="contact__form fund-apply__form"<?php foreach ( $data as $k => $v ) { printf( ' data-%s="%s"', esc_attr( $k ), esc_attr( $v ) ); } // phpcs:ignore Generic.ControlStructures.InlineControlStructure ?>>
				<?php echo do_blocks( '<!-- wp:snow-monkey-forms/snow-monkey-form ' . wp_json_encode( array( 'formId' => $form ) ) . ' /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();

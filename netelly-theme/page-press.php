<?php
/**
 * Template Name: プレスキット
 *
 * Press kit for media: intro + bulk download, boilerplate (copy), logos, fact sheet
 * (company profile from サイト設定), executives, usage guidelines, latest press
 * releases and a media-contact band. No design comp exists; built from the site's
 * components (statement, section-head, table, rows, entry band).
 *
 * @package netelly
 */

get_header();
$f       = static fn( $name ) => netelly_field( $name );
$contact = add_query_arg( 'type', 'press', netelly_page_url( 'contact' ) );
$kit     = (int) $f( 'kit_file' );
$email   = (string) ( $f( 'press_email' ) ?: netelly_opt( 'contact_email' ) );

/**
 * Download link for an attachment ("ダウンロード ↓" + format).
 */
$download = static function ( int $id, string $note = '' ): string {
	$url = $id ? wp_get_attachment_url( $id ) : '';
	if ( ! $url ) {
		return '';
	}
	return sprintf(
		'<a class="text-link press-dl" href="%1$s" download>%2$s <span class="arrow" aria-hidden="true">↓</span></a>%3$s',
		esc_url( $url ),
		esc_html( netelly_t( 'download' ) ),
		$note ? '<span class="press-dl__note">' . esc_html( $note ) . '</span>' : ''
	);
};
?>
<main id="main" class="page-press">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<section class="section" id="press-kit">
		<div class="statement">
			<p class="statement__label"><?php echo esc_html( (string) $f( 'intro_label' ) ); ?></p>
			<div class="statement__body">
				<h2 class="statement__heading"><?php echo netelly_br( (string) $f( 'intro_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="statement__text"><?php echo esc_html( (string) $f( 'intro_text' ) ); ?></p>
				<div class="btn-group">
					<?php if ( $kit && wp_get_attachment_url( $kit ) ) : ?>
						<a class="btn btn--primary" href="<?php echo esc_url( (string) wp_get_attachment_url( $kit ) ); ?>" download><span class="btn__label"><?php echo esc_html( (string) $f( 'kit_button' ) ); ?></span> <span class="arrow" aria-hidden="true">↓</span></a>
					<?php endif; ?>
					<?php echo netelly_button( (string) $f( 'contact_button' ), $contact, 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</div>
			</div>
		</div>
	</section>

	<?php $boilerplates = (array) $f( 'boilerplates' ); ?>
	<?php if ( $boilerplates ) : ?>
		<section class="section" id="boilerplate">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'boiler_label' ), 'heading' => (string) $f( 'boiler_heading' ) ) ); ?>
			<dl class="table press-boiler">
				<?php foreach ( $boilerplates as $i => $row ) : ?>
					<div class="table__row">
						<dt class="table__label"><?php echo esc_html( (string) $row['label'] ); ?></dt>
						<dd class="table__value press-boiler__body">
							<p class="press-boiler__text" id="boiler-<?php echo (int) $i; ?>"><?php echo esc_html( (string) $row['text'] ); ?></p>
							<span class="press-boiler__tools">
								<button class="press-copy" type="button" data-copy="boiler-<?php echo (int) $i; ?>" data-done="<?php echo esc_attr( netelly_t( 'copied_text' ) ); ?>"><?php echo esc_html( netelly_t( 'copy_text' ) ); ?></button>
								<span class="press-copy__status" role="status" aria-live="polite"></span>
							</span>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
	<?php endif; ?>

	<?php $logos = (array) $f( 'logos' ); ?>
	<?php if ( $logos ) : ?>
		<section class="section" id="logo">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'logo_label' ), 'heading' => (string) $f( 'logo_heading' ) ) ); ?>
			<ul class="press-assets" data-stagger="60">
				<?php foreach ( $logos as $logo ) : ?>
					<li class="press-asset<?php echo ! empty( $logo['dark'] ) ? ' is-dark-bg' : ''; ?>">
						<div class="press-asset__preview">
							<?php
							$pid = (int) ( $logo['preview'] ?? 0 );
							echo $pid ? wp_get_attachment_image( $pid, 'large', false, array( 'alt' => (string) ( $logo['name'] ?? '' ), 'loading' => 'lazy' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput
							?>
						</div>
						<p class="press-asset__name"><?php echo esc_html( (string) ( $logo['name'] ?? '' ) ); ?></p>
						<p class="press-asset__dl"><?php echo $download( (int) ( $logo['file'] ?? 0 ), (string) ( $logo['format'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<section class="section" id="fact-sheet">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'facts_label' ), 'heading' => (string) $f( 'facts_heading' ) ) ); ?>
		<dl class="table">
			<?php foreach ( (array) netelly_opt( 'profile' ) as $row ) : ?>
				<div class="table__row">
					<dt class="table__label"><?php echo esc_html( (string) $row['label'] ); ?></dt>
					<dd class="table__value"><?php echo netelly_br( (string) $row['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</section>

	<?php $people = (array) $f( 'people' ); ?>
	<?php if ( $people ) : ?>
		<section class="section" id="executives">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'people_label' ), 'heading' => (string) $f( 'people_heading' ) ) ); ?>
			<ul class="press-people" data-stagger="60">
				<?php foreach ( $people as $person ) : ?>
					<li class="press-person">
						<?php echo netelly_media( (int) ( $person['photo'] ?? 0 ), '4/5', array( 'sizes' => '(max-width: 768px) 100vw, 30vw', 'alt' => (string) ( $person['name'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<div class="press-person__body">
							<p class="press-person__role"><?php echo esc_html( (string) ( $person['role'] ?? '' ) ); ?></p>
							<p class="press-person__name"><?php echo esc_html( (string) ( $person['name'] ?? '' ) ); ?>
								<?php if ( ! empty( $person['name_en'] ) ) : ?>
									<span class="press-person__name-en" lang="en"><?php echo esc_html( (string) $person['name_en'] ); ?></span>
								<?php endif; ?>
							</p>
							<?php if ( ! empty( $person['bio'] ) ) : ?>
								<p class="press-person__bio"><?php echo esc_html( (string) $person['bio'] ); ?></p>
							<?php endif; ?>
							<?php echo $download( (int) ( $person['photo_file'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php $guides = (array) $f( 'guides' ); ?>
	<?php if ( $guides ) : ?>
		<section class="section" id="guidelines">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'guide_label' ), 'heading' => (string) $f( 'guide_heading' ) ) ); ?>
			<ol class="table table--history press-guides">
				<?php foreach ( $guides as $i => $g ) : ?>
					<li class="table__row">
						<span class="table__year"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<span class="table__value"><?php echo esc_html( (string) $g['text'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php
	$press_cat = get_category_by_slug( 'en' === netelly_lang() ? 'press-en' : 'press' );
	$releases  = $press_cat ? get_posts(
		array(
			'cat'            => $press_cat->term_id,
			'posts_per_page' => 3,
			'no_found_rows'  => true,
		)
	) : array();
	?>
	<?php if ( $releases ) : ?>
		<section class="section" id="press-releases">
			<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'rel_label' ), 'heading' => (string) $f( 'rel_heading' ), 'link' => $f( 'rel_link' ) ) ); ?>
			<div class="list-news">
				<?php foreach ( $releases as $p ) : ?>
					<?php get_template_part( 'template-parts/row-news', null, array( 'id' => $p->ID ) ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
	<div class="end-spacer"></div>

	<section class="entry-band is-dark" id="media-contact">
		<div class="entry-band__head">
			<p class="entry-band__label"><?php echo esc_html( (string) $f( 'cta_label' ) ); ?></p>
			<h2 class="entry-band__heading"><?php echo netelly_br( (string) $f( 'cta_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( $email ) : ?>
				<p class="press-email"><span class="press-email__label"><?php echo esc_html( (string) $f( 'cta_email_label' ) ); ?></span><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></p>
			<?php endif; ?>
		</div>
		<div class="entry-band__cta"><?php echo netelly_button( (string) $f( 'contact_button' ), $contact, 'primary', array( 'class' => 'is-block-sp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</section>
</main>
<?php
get_footer();

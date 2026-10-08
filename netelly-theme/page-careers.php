<?php
/**
 * Template Name: 採用
 *
 * Careers (design #p10): message, reasons, open positions (#positions), process, entry band.
 * Position rows link to entry_url, else to the contact page with 採用 preselected
 * and the position name prefilled (?type=recruit&position=…).
 *
 * @package netelly
 */

get_header();
$f       = static fn( $name ) => netelly_field( $name );
$contact = netelly_page_url( 'contact' );
$entry   = add_query_arg( 'type', 'recruit', $contact );
$roles   = get_posts(
	array(
		'post_type'      => 'position',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	)
);
?>
<main id="main" class="page-careers">
	<?php get_template_part( 'template-parts/page-hero' ); ?>

	<section class="section">
		<div class="careers-intro">
			<div class="careers-intro__text">
				<p class="careers-intro__label"><?php echo esc_html( (string) $f( 'cm_label' ) ); ?></p>
				<h2 class="careers-intro__heading"><?php echo netelly_br( (string) $f( 'cm_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<p class="careers-intro__body"><?php echo esc_html( (string) $f( 'cm_text' ) ); ?></p>
			</div>
			<?php echo netelly_media( $f( 'cm_image' ), '4/5', array( 'sizes' => '(max-width: 768px) 100vw, 40vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>

	<section class="section">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'why_label' ), 'heading' => (string) $f( 'why_heading' ) ) ); ?>
		<ol class="reasons" data-stagger="60">
			<?php foreach ( (array) $f( 'reasons' ) as $i => $r ) : ?>
				<li class="reason">
					<span class="reason__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3 class="reason__title"><?php echo esc_html( (string) $r['title'] ); ?></h3>
					<p class="reason__text"><?php echo esc_html( (string) $r['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<section class="section" id="positions">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'pos_label' ), 'heading' => (string) $f( 'pos_heading' ) ) ); ?>
		<div class="positions" data-stagger="60">
			<?php foreach ( $roles as $role ) : ?>
				<?php
				$url  = (string) netelly_field( 'entry_url', $role->ID );
				$ext  = $url && netelly_is_external( $url );
				$url  = $url ? $url : add_query_arg( 'position', rawurlencode( get_the_title( $role ) ), $entry );
				$tags = array_filter( array( netelly_field( 'employment_type', $role->ID ), netelly_field( 'location', $role->ID ) ) );
				?>
				<a class="row-link position"<?php echo netelly_link_attrs( $url, $ext ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<span class="position__title"><?php echo esc_html( get_the_title( $role ) ); ?></span>
					<span class="position__arrow arrow" aria-hidden="true"><?php echo $ext ? '↗' : '→'; ?></span>
					<span class="position__tag"><?php echo esc_html( implode( ' · ', $tags ) ); ?></span>
					<span class="position__summary"><?php echo esc_html( (string) netelly_field( 'summary', $role->ID ) ); ?></span>
					<?php echo netelly_new_tab_note( $url, $ext ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section">
		<?php get_template_part( 'template-parts/section-head', null, array( 'label' => (string) $f( 'proc_label' ), 'heading' => (string) $f( 'proc_heading' ) ) ); ?>
		<ol class="steps">
			<?php foreach ( (array) $f( 'steps' ) as $i => $s ) : ?>
				<li class="step">
					<span class="step__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<h3 class="step__title"><?php echo esc_html( (string) $s['title'] ); ?></h3>
					<p class="step__text"><?php echo esc_html( (string) $s['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>
	<div class="end-spacer"></div>

	<section class="entry-band is-dark">
		<div class="entry-band__head">
			<p class="entry-band__label"><?php echo esc_html( (string) $f( 'entry_label' ) ); ?></p>
			<h2 class="entry-band__heading"><?php echo netelly_br( (string) $f( 'entry_heading' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<div class="entry-band__cta"><?php echo netelly_button( (string) $f( 'entry_button' ), $entry, 'primary', array( 'class' => 'is-block-sp' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</section>
</main>
<?php
get_footer();

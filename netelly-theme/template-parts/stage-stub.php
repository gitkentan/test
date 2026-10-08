<?php
/**
 * Temporary stub for templates implemented in stage 4: shows title + language.
 *
 * @package netelly
 */

?>
<main id="main" class="specimen">
	<p class="label"><?php echo esc_html( strtoupper( netelly_lang() ) ); ?> · <?php echo esc_html( get_page_template_slug() ? get_page_template_slug() : 'index.php' ); ?></p>
	<h1 style="font-size:44px"><?php echo esc_html( (string) netelly_field( 'page_en_title' ) ); ?></h1>
	<p><?php the_title(); ?></p>
</main>

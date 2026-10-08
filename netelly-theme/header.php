<?php
/**
 * Document head. The visual header lives in template-parts/header.php (stage 3).
 *
 * @package netelly
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#main"><?php esc_html_e( '本文へスキップ', 'netelly' ); ?></a>

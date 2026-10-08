<?php
/**
 * Document head + site header.
 *
 * @package netelly
 */

$netelly_variant = netelly_header_variant();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'header-' . $netelly_variant ); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#main"><?php echo esc_html( netelly_t( 'skip' ) ); ?></a>
<?php
get_template_part( 'template-parts/header', null, array( 'variant' => $netelly_variant ) );

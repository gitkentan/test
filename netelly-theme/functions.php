<?php
/**
 * Netelly theme bootstrap.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

define( 'NETELLY_VERSION', '0.1.0' );
define( 'NETELLY_DIR', get_template_directory() );
define( 'NETELLY_URI', get_template_directory_uri() );

require_once NETELLY_DIR . '/inc/helpers.php';
require_once NETELLY_DIR . '/inc/setup.php';
require_once NETELLY_DIR . '/inc/enqueue.php';

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
require_once NETELLY_DIR . '/inc/i18n.php';
require_once NETELLY_DIR . '/inc/strings.php';
require_once NETELLY_DIR . '/inc/acf.php';
require_once NETELLY_DIR . '/inc/cpt.php';
require_once NETELLY_DIR . '/inc/menus.php';
require_once NETELLY_DIR . '/inc/template-tags.php';
require_once NETELLY_DIR . '/inc/samples.php';
require_once NETELLY_DIR . '/inc/setup.php';
require_once NETELLY_DIR . '/inc/enqueue.php';
require_once NETELLY_DIR . '/inc/forms.php';
require_once NETELLY_DIR . '/inc/seo.php';
require_once NETELLY_DIR . '/inc/tone.php';
require_once NETELLY_DIR . '/inc/access.php';
if ( is_admin() ) {
	require_once NETELLY_DIR . '/inc/admin-seed.php';
	require_once NETELLY_DIR . '/inc/admin-news-import.php';
}

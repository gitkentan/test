<?php
/**
 * Custom post types: work (作品) and position (募集職種).
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function () {
		register_post_type(
			'work',
			array(
				'labels'        => array(
					'name'          => '作品',
					'singular_name' => '作品',
					'add_new_item'  => '作品を追加',
					'edit_item'     => '作品を編集',
					'all_items'     => '作品一覧',
				),
				'public'        => true,
				'has_archive'   => 'works',
				'rewrite'       => array(
					'slug'       => 'works',
					'with_front' => false,
				),
				'menu_position' => 5,
				'menu_icon'     => 'dashicons-format-video',
				'supports'      => array( 'title', 'page-attributes', 'revisions' ),
				'show_in_rest'  => true,
			)
		);

		// Positions are listed on /careers/#positions only; no single pages.
		register_post_type(
			'position',
			array(
				'labels'             => array(
					'name'          => '募集職種',
					'singular_name' => '募集職種',
					'add_new_item'  => '募集職種を追加',
					'edit_item'     => '募集職種を編集',
				),
				'public'             => false,
				'show_ui'            => true,
				'show_in_nav_menus'  => false,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'menu_position'      => 6,
				'menu_icon'          => 'dashicons-groups',
				'supports'           => array( 'title', 'page-attributes' ),
			)
		);
	}
);

// Admin list: order works/positions by menu_order like the site does.
add_action(
	'pre_get_posts',
	static function ( WP_Query $q ) {
		if ( is_admin() && $q->is_main_query() && in_array( $q->get( 'post_type' ), array( 'work', 'position' ), true ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		}
	}
);

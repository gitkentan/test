<?php
/**
 * STUDIO page (page-studio.php, /studio/): commissioned production for companies and brands.
 * Default texts, one-time setup on theme update (JA/EN page, header + footer menu items)
 * and the page lookup used by the top-page band.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published STUDIO page in a language (0 = none).
 *
 * @param string $lang ja|en.
 */
function netelly_studio_page_id( string $lang ): int {
	foreach ( netelly_ids_by_slug( 'page', 'studio', $lang ) as $id ) {
		if ( 'page-studio.php' === get_page_template_slug( $id ) ) {
			return (int) $id;
		}
	}
	return 0;
}

/**
 * Default page texts.
 *
 * @param string $lang ja|en.
 */
function netelly_studio_defaults( string $lang ): array {
	$ja = 'en' !== $lang;
	$L  = static fn( $j, $e ) => $ja ? $j : $e;
	return array(
		'intro_label'   => 'NETELLY STUDIO',
		'intro_heading' => $L( "企業・ブランドの映像を、\nドラマの力でつくる。", "Films for companies and brands,\nmade with the power of drama." ),
		'intro_text'    => $L(
			'Netellyは、オリジナルドラマの企画・制作で培ったノウハウを活かし、企業やブランドの映像制作を、企画から撮影・編集・納品までお引き受けします。',
			'Drawing on what we have learned making our own original dramas, Netelly takes on film projects for companies and brands — from concept through shooting, editing and delivery.'
		),
		'svc_label'     => 'SERVICES',
		'svc_heading'   => $L( 'つくれるもの', 'What we make' ),
		'services'      => array(
			array( 'en' => 'BRANDED DRAMA', 'title' => $L( 'ブランデッドドラマ', 'Branded drama' ), 'text' => $L( '商品やサービスの世界観を、物語として描きます。', 'We tell the world of your product or service as a story.' ) ),
			array( 'en' => 'SHORT DRAMA', 'title' => $L( '縦型・ショートドラマ', 'Vertical & short drama' ), 'text' => $L( 'SNSや広告で見られる、短尺のドラマを制作します。', 'Short-form drama made to be watched on social media and in ads.' ) ),
			array( 'en' => 'COMMERCIAL', 'title' => $L( 'CM・広告映像', 'Commercials' ), 'text' => $L( 'Web CMや広告用の映像を、企画から制作します。', 'Web commercials and advertising films, from idea to delivery.' ) ),
			array( 'en' => 'YOUTUBE', 'title' => $L( 'YouTube番組・チャンネル', 'YouTube shows & channels' ), 'text' => $L( '番組の企画・撮影・編集から、チャンネルの運用まで。', 'From planning, shooting and editing shows to running the channel.' ) ),
			array( 'en' => 'SOCIAL', 'title' => $L( 'SNS動画', 'Social video' ), 'text' => $L( 'TikTok・Instagram・Xなど、各SNSに合わせた動画をつくります。', 'Videos made for TikTok, Instagram, X and other platforms.' ) ),
			array( 'en' => 'PRODUCTION', 'title' => $L( 'キャスティング・撮影', 'Casting & production' ), 'text' => $L( 'キャスティング、ロケ、撮影、編集までまとめてお任せいただけます。', 'Casting, locations, shooting and editing, all in one place.' ) ),
		),
		'why_label'     => 'WHY NETELLY',
		'why_heading'   => $L( 'Netellyに頼む理由', 'Why work with us' ),
		'points'        => array(
			array( 'title' => $L( '物語でつくる', 'Story first' ), 'text' => $L( '自社でオリジナルドラマを企画・制作しているから、伝えたいことを「見たくなる物語」に変えられます。', 'Because we develop and produce our own dramas, we can turn your message into a story people want to watch.' ) ),
			array( 'title' => $L( '届け方まで考える', 'Made to be seen' ), 'text' => $L( 'YouTubeやSNSでの公開を前提に、尺・構成・サムネイルまで設計します。', 'We design length, structure and thumbnails for release on YouTube and social media.' ) ),
			array( 'title' => $L( '企画から納品まで', 'Concept to delivery' ), 'text' => $L( '企画・脚本・キャスティング・撮影・編集を、一つのチームで進めます。', 'Concept, script, casting, shooting and editing are handled by one team.' ) ),
		),
		'flow_label'    => 'PROCESS',
		'flow_heading'  => $L( '制作の流れ', 'How we work' ),
		'steps'         => array(
			array( 'title' => $L( 'ご相談', 'Talk' ), 'text' => $L( '目的やご予算、時期をお聞かせください。', 'Tell us your goals, budget and timing.' ) ),
			array( 'title' => $L( '企画・お見積り', 'Plan & quote' ), 'text' => $L( '企画案とお見積りをご提案します。', 'We propose a concept and a quote.' ) ),
			array( 'title' => $L( '撮影・制作', 'Production' ), 'text' => $L( 'キャスティングから撮影・編集まで進めます。', 'Casting, shooting and editing.' ) ),
			array( 'title' => $L( '納品・公開', 'Delivery' ), 'text' => $L( '納品後の公開・運用もご相談いただけます。', 'We can also help with release and running it.' ) ),
		),
		'works_label'   => 'WORKS',
		'works_heading' => $L( 'Netellyがつくった作品', 'Our work' ),
		'works_text'    => $L( 'オリジナル作品から、Netellyの映像づくりをご覧ください。', 'See how we make films through our original works.' ),
		'cta_label'     => 'CONTACT',
		'cta_heading'   => $L( "映像のご相談は、\nお気軽に。", "Let’s talk about\nyour film." ),
		'cta_text'      => $L( '企画が固まっていなくても大丈夫です。まずはお話をお聞かせください。', 'No need for a finished plan — just tell us what you have in mind.' ),
		'cta_button'    => $L( '制作について相談する →', 'Talk to us about a project →' ),
		'band_label'    => 'STUDIO',
		'band_heading'  => $L( "企業の映像も、\nNetellyがつくる。", "We also make films\nfor companies and brands." ),
		'band_text'     => $L( 'ブランデッドドラマ、ショートドラマ、CM、YouTube番組まで。企画から納品までお引き受けします。', 'Branded drama, short drama, commercials and YouTube shows — from concept to delivery.' ),
		'band_button'   => $L( '受託制作について →', 'About our studio →' ),
	);
}

/**
 * Insert a custom link into a menu right after the item whose URL contains $after (else at the end).
 *
 * @param string $menu_name Menu name (e.g. primary-ja).
 * @param string $title     Item title.
 * @param string $url       Item URL.
 * @param string $after     URL fragment of the item to follow.
 * @param bool   $child     Insert as a sibling child of that item's parent.
 */
function netelly_studio_menu_insert( string $menu_name, string $title, string $url, string $after, bool $child = false ): void {
	$menu = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		return;
	}
	$items = (array) wp_get_nav_menu_items( $menu->term_id );
	foreach ( $items as $it ) {
		if ( false !== strpos( (string) $it->url, '/studio/' ) ) {
			return; // Already there.
		}
	}
	$anchor = null;
	foreach ( $items as $it ) {
		if ( false !== strpos( (string) $it->url, $after ) && ( $child ? (int) $it->menu_item_parent : ! (int) $it->menu_item_parent ) ) {
			$anchor = $it;
			break;
		}
	}
	$order = $anchor ? (int) $anchor->menu_order + 1 : count( $items ) + 1;
	foreach ( $items as $it ) {
		if ( (int) $it->menu_order >= $order ) {
			wp_update_post( array( 'ID' => $it->ID, 'menu_order' => (int) $it->menu_order + 1 ) );
		}
	}
	wp_update_nav_menu_item(
		$menu->term_id,
		0,
		array(
			'menu-item-title'     => $title,
			'menu-item-url'       => $url,
			'menu-item-type'      => 'custom',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $order,
			'menu-item-parent-id' => $anchor && $child ? (int) $anchor->menu_item_parent : 0,
		)
	);
}

/*
 * One-time setup on theme update (option netelly_studio_v1): JA/EN page with default texts,
 * "STUDIO" after BUSINESS in the header menu, 受託制作 under 事業 in the footer.
 */
add_action(
	'init',
	static function () {
		if ( get_option( 'netelly_studio_v1' ) || ! function_exists( 'update_field' ) || ! function_exists( 'pll_set_post_language' ) ) {
			return;
		}
		update_option( 'netelly_studio_v1', 1 );
		foreach ( array( 'ja', 'en' ) as $lang ) {
			if ( netelly_ids_by_slug( 'page', 'studio', $lang, 0, array( 'publish', 'draft', 'private', 'pending', 'future' ) ) ) {
				return;
			}
		}
		$tr = array();
		foreach ( array( 'ja', 'en' ) as $lang ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => 'en' === $lang ? 'Studio' : '受託制作',
					'menu_order'  => 3,
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				return;
			}
			pll_set_post_language( $id, $lang );
			wp_update_post( array( 'ID' => $id, 'post_name' => 'studio' ) );
			update_post_meta( $id, '_wp_page_template', 'page-studio.php' );
			update_field( 'field_nt_page_hero_page_en_title', 'STUDIO', $id );
			update_field( 'field_nt_page_hero_seo_description', 'en' === $lang ? 'Netelly Studio: branded drama, short drama, commercials, YouTube shows and social video for companies and brands, from concept to delivery.' : 'Netellyの受託制作。ブランデッドドラマ、ショートドラマ、CM、YouTube番組、SNS動画など、企業・ブランドの映像を企画から納品までお引き受けします。', $id );
			foreach ( netelly_studio_defaults( $lang ) as $name => $value ) {
				update_field( 'field_nt_page_studio_' . $name, $value, $id );
			}
			$tr[ $lang ] = (int) $id;
		}
		pll_save_post_translations( $tr );
		flush_rewrite_rules( false );
		foreach ( $tr as $lang => $id ) {
			$url = (string) get_permalink( $id );
			netelly_studio_menu_insert( 'primary-' . $lang, 'STUDIO', $url, '/business/' );
			netelly_studio_menu_insert( 'footer-' . $lang, 'en' === $lang ? 'Studio' : '受託制作', $url, '/business/#creative', true );
		}
	},
	31
);

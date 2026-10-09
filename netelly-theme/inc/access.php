<?php
/**
 * Company page › access: defaults (map location and station access, as on the previous
 * netelly.co.jp) and a one-time fill-in for sites seeded before these fields existed.
 *
 * @package netelly
 */

const NETELLY_ACCESS_QUERY = '住友不動産大崎ガーデンタワー 東京都品川区西品川1-1-1';

/**
 * Default rows for 企業情報 › アクセス情報.
 *
 * @return array<int,array{label:string,value:string}>
 */
function netelly_default_access_rows( string $lang ): array {
	if ( 'en' === $lang ) {
		return array(
			array(
				'label' => 'Access',
				'value' => "6 min walk from Osaki Station, South Exit (JR / Rinkai Line)\n13 min walk from Oimachi Station (JR / Tokyu Oimachi Line / Rinkai Line)",
			),
		);
	}
	return array(
		array(
			'label' => 'アクセス',
			'value' => "大崎駅南口 徒歩6分（JR／りんかい線）\n大井町駅 徒歩13分（JR線・東急大井町線・りんかい線）",
		),
	);
}

/**
 * Default links for 企業情報 › アクセス (shuttle bus timetable of the building, PDF).
 *
 * @return array<int,array{link:array{title:string,url:string,target:string}}>
 */
function netelly_default_access_links( string $lang ): array {
	return array(
		array(
			'link' => array(
				'title'  => 'en' === $lang ? 'Free shuttle bus timetable (from Osaki, Oimachi and Shinagawa stations; weekdays only) ↗' : '無料シャトルバス時刻表（大崎駅発、大井町駅発、品川駅発 平日のみ運行） ↗',
				'url'    => 'https://www.segasammy.co.jp/cms/wp-content/uploads/pdf/ja/img-bustimetable_20251216-1.pdf',
				'target' => '_blank',
			),
		),
	);
}

/**
 * Google Maps embed URL for a place (no API key needed).
 */
function netelly_map_embed_url( string $query ): string {
	return 'https://www.google.com/maps?' . http_build_query(
		array(
			'q'      => $query,
			'z'      => 16,
			'hl'     => netelly_lang(),
			'output' => 'embed',
		)
	);
}

add_action(
	'acf/init',
	static function () {
		if ( ! function_exists( 'update_field' ) ) {
			return;
		}
		$v1 = ! get_option( 'netelly_access_v1' );
		$v2 = ! get_option( 'netelly_access_v2' );
		if ( ! $v1 && ! $v2 ) {
			return;
		}
		update_option( 'netelly_access_v1', 1 );
		update_option( 'netelly_access_v2', 1 );
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'page-company.php', // phpcs:ignore WordPress.DB.SlowDBQuery
				'lang'           => '',
				'fields'         => 'ids',
			)
		);
		foreach ( $pages as $id ) {
			$lang = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : 'ja';
			// v2: shuttle bus timetable link, unless links were already entered.
			if ( $v2 && ! (int) get_post_meta( $id, 'access_links', true ) ) {
				update_field( 'field_nt_page_company_access_links', netelly_default_access_links( $lang ), $id );
			}
			if ( ! $v1 ) {
				continue;
			}
			if ( ! metadata_exists( 'post', $id, 'access_map_query' ) ) {
				update_field( 'field_nt_page_company_access_map_query', NETELLY_ACCESS_QUERY, $id );
			}
			if ( ! metadata_exists( 'post', $id, 'access_rows' ) ) {
				update_field( 'field_nt_page_company_access_rows', netelly_default_access_rows( $lang ), $id );
			}
		}
	}
);

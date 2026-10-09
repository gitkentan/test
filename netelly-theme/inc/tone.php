<?php
/**
 * One-time copy updates. Each set replaces the original default texts in サイト設定 and
 * page fields with the new ones; text the site owner has already edited no longer contains
 * the old default and is left alone. Each set runs once per site (option flag).
 *
 * - v1: toned-down "global" wording.
 * - v2: capital "as of" date confirmed by the client.
 *
 * @package netelly
 */

add_action(
	'init',
	static function () {
		$sets = array(
			'netelly_tone_v1' => array(
				'Netelly株式会社は、東京・ニューヨーク・ロサンゼルス発のグローバル・エンターテインメントカンパニーです。2019年の創業以来、' => 'Netelly株式会社は、2019年の創業以来、',
				'東京・ニューヨーク・ロサンゼルス発のグローバル・エンターテインメントカンパニー' => 'ドラマ・映画・ショートドラマを企画・制作するエンターテインメントカンパニー',
				'東京・ニューヨーク・ロサンゼルス発、グローバル・エンターテインメントカンパニー。' => 'ドラマ・映画・ショートドラマを企画・制作する、エンターテインメントカンパニー。',
				'東京、ニューヨーク、ロサンゼルス。国境を越えて物語を企画し、制作し、届けるグローバル・エンターテインメントカンパニー。' => 'ドラマ・映画・ショートドラマを自社で企画・制作し、配信・YouTube・SNS・劇場へ届けるエンターテインメントカンパニー、Netelly株式会社の公式サイト。',
				'Netellyは、国や言語を越えて届く物語をつくるグローバル・エンターテインメント企業です。ショートドラマからドラマ・映画・コメディまでを自社で企画・制作し、配信やYouTube、SNS、劇場へ届けています。' => 'Netellyは、ショートドラマからドラマ・映画・コメディまでを自社で企画・制作し、配信やYouTube、SNS、劇場へ届けるエンターテインメント企業です。',
				'Netellyは、国や言語を越えて届く物語をつくるグローバル・エンターテインメント企業です。' => 'Netellyは、ショートドラマからドラマ・映画・コメディまでを自社で企画・制作し、届けるエンターテインメント企業です。',
				'ドラマ・映画・ショートドラマを、世界に向けて企画し、制作する。' => 'ドラマ・映画・ショートドラマを、企画から制作まで手がける。',
				'NETELLY INC. · TOKYO / NEW YORK / LOS ANGELES' => 'NETELLY INC. · EST. 2019',
				'Tokyo — New York — Los Angeles' => 'Create — Deliver — Connect — Support',
				'Netelly Inc. is a global entertainment company from Tokyo, New York and Los Angeles. Since its founding in 2019, Netelly has' => 'Since its founding in 2019, Netelly Inc. has',
				'Netelly is a global entertainment company from Tokyo, New York and Los Angeles. Since its founding in 2019, Netelly has' => 'Since its founding in 2019, Netelly has',
				'Netelly is a global entertainment company from Tokyo, New York and Los Angeles.' => 'Netelly is an entertainment company developing and producing series, films and short dramas.',
				'Netelly Inc. | A global entertainment company from Tokyo, New York and Los Angeles' => 'Netelly Inc. | An entertainment company developing series, films and short dramas',
				'A global entertainment company from Tokyo, New York and Los Angeles.' => 'An entertainment company developing and producing series, films and short dramas.',
				'Tokyo, New York, Los Angeles — a global entertainment company that develops, produces and delivers stories across borders.' => 'Official site of Netelly Inc., an entertainment company that develops and produces series, films and short dramas in-house and delivers them through streaming, YouTube, social media and theaters.',
				'Netelly is a global entertainment company creating stories that travel across countries and languages. We develop and produce everything in-house — from short dramas to series, films and comedy — and deliver it through' => 'Netelly is an entertainment company that develops and produces everything in-house — from short dramas to series, films and comedy — and delivers it through',
				'Netelly is a global entertainment company creating stories that travel across countries and languages.' => 'Netelly is an entertainment company that develops, produces and delivers everything in-house — from short dramas to series, films and comedy.',
				'We develop and produce series, films and short dramas for the world.' => 'We develop and produce series, films and short dramas.',
			),
			'netelly_tone_v2' => array(
				'13,498,750円（2021年7月時点）'     => '13,498,750円（2026年7月時点）',
				'JPY 13,498,750 (as of July 2021)' => 'JPY 13,498,750 (as of July 2026)',
			),
		);
		$done = false;
		foreach ( $sets as $flag => $pairs ) {
			if ( get_option( $flag ) ) {
				continue;
			}
			update_option( $flag, 1 );
			netelly_replace_texts( $pairs );
			$done = true;
		}
		if ( $done ) {
			wp_cache_flush();
		}
	},
	20
);

/**
 * Replaces text in サイト設定 (options netelly_*) and page fields (post meta); serialized values are skipped.
 *
 * @param array<string,string> $pairs old => new.
 */
function netelly_replace_texts( array $pairs ): void {
	global $wpdb;
	foreach ( $pairs as $old => $new ) {
		$like = '%' . $wpdb->esc_like( $old ) . '%';
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value LIKE %s", $wpdb->esc_like( 'netelly_' ) . '%', $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $rows as $row ) {
			if ( ! is_serialized( $row->option_value ) ) {
				update_option( $row->option_name, str_replace( $old, $new, $row->option_value ) );
			}
		}
		$metas = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $metas as $meta ) {
			if ( ! is_serialized( $meta->meta_value ) ) {
				$wpdb->update( $wpdb->postmeta, array( 'meta_value' => str_replace( $old, $new, $meta->meta_value ) ), array( 'meta_id' => $meta->meta_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			}
		}
	}
}

<?php
/**
 * Fixed UI strings. Registered to Polylang (言語 › 翻訳) so editors can change
 * both the Japanese and English wording without touching code.
 *
 * Templates call netelly_t( 'key' ). The 'ja' value is the Polylang source
 * string; 'en' is only the seed value imported by bin/seed.php.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registry: key => [ ja, en ].
 *
 * @return array<string,array{0:string,1:string}>
 */
function netelly_strings(): array {
	return array(
		// Header / navigation.
		'header_contact'   => array( 'CONTACT', 'CONTACT' ),
		'skip'             => array( '本文へスキップ', 'Skip to content' ),
		'nav_label'        => array( 'メインメニュー', 'Main menu' ),
		'menu_open'        => array( 'メニューを開く', 'Open menu' ),
		'menu_close'       => array( 'メニューを閉じる', 'Close menu' ),
		'new_tab'          => array( '（新しいタブで開きます）', '(opens in a new tab)' ),
		'breadcrumb_top'   => array( 'TOP', 'TOP' ),
		'lang_switch'      => array( '言語を切り替える', 'Switch language' ),
		// Hero video.
		'video_pause'      => array( '背景動画を一時停止', 'Pause background video' ),
		'cursor_view'      => array( 'VIEW', 'VIEW' ),
		'video_play'       => array( '背景動画を再生', 'Play background video' ),
		// News band / lists.
		'news_label'       => array( 'NEWS', 'NEWS' ),
		'cat_all'          => array( 'すべて', 'All' ),
		'no_posts'         => array( '記事はありません', 'No articles yet.' ),
		'page_next'        => array( '次のページ', 'Next page' ),
		'page_prev'        => array( '前のページ', 'Previous page' ),
		// Works.
		'genre_all'        => array( 'すべて', 'All' ),
		'genre_drama'      => array( 'ドラマ', 'Drama' ),
		'genre_variety'    => array( 'バラエティ', 'Variety' ),
		'genre_film'       => array( '映画', 'Film' ),
		'genre_in_development' => array( '制作中', 'In development' ),
		'count_format'     => array( '%1$s（%2$d）', '%1$s (%2$d)' ),
		'sort_newest'      => array( 'SORT · NEWEST', 'SORT · NEWEST' ),
		'no_works'         => array( '該当する作品はありません', 'No works found.' ),
		'featured'         => array( 'FEATURED', 'FEATURED' ),
		'view_work'        => array( '作品を見る →', 'View work →' ),
		'coming_soon'      => array( 'COMING SOON', 'COMING SOON' ),
		// Work detail.
		'trailer'          => array( '予告編を見る →', 'Watch trailer →' ),
		'watch_all'        => array( 'YouTubeで全話を見る', 'Watch all on YouTube' ),
		'story_label'      => array( 'STORY', 'STORY' ),
		'views_label'      => array( 'TOTAL VIEWS', 'TOTAL VIEWS' ),
		'episodes_label'   => array( 'EPISODES', 'EPISODES' ),
		'episodes_heading' => array( 'エピソード', 'Episodes' ),
		'cast_label'       => array( 'CAST', 'CAST' ),
		'cast_heading'     => array( 'キャスト', 'Cast' ),
		'cast_role'        => array( '%s 役', 'as %s' ),
		'staff_label'      => array( 'STAFF', 'STAFF' ),
		'staff_heading'    => array( 'スタッフ', 'Staff' ),
		'more_label'       => array( 'MORE ORIGINALS', 'MORE ORIGINALS' ),
		'more_heading'     => array( 'ほかの作品', 'More originals' ),
		'all_works'        => array( 'すべての作品 →', 'All works →' ),
		'modal_close'      => array( '閉じる', 'Close' ),
		'trailer_title'    => array( '予告編', 'Trailer' ),
		// News detail.
		'back_news'        => array( '← ニュース一覧', '← All news' ),
		'work_summary'     => array( '作品概要', 'About the work' ),
		'to_work'          => array( '作品ページへ →', 'View work →' ),
		'share'            => array( 'SHARE', 'SHARE' ),
		'share_link'       => array( 'LINK', 'LINK' ),
		'copied'           => array( 'リンクをコピーしました', 'Link copied' ),
		'prev_post'        => array( '← 前の記事', '← Previous' ),
		'next_post'        => array( '次の記事 →', 'Next →' ),
		// Contact.
		'email_label'      => array( 'EMAIL', 'EMAIL' ),
		'tel_label'        => array( 'TEL', 'TEL' ),
		// 404.
		'nf_label'         => array( '404', '404' ),
		'nf_heading'       => array( 'ページが見つかりません', 'Page not found' ),
		'nf_text'          => array( 'お探しのページは移動または削除された可能性があります。', 'The page you are looking for may have been moved or deleted.' ),
		'nf_button'        => array( 'トップへ戻る →', 'Back to home →' ),
		// CEO profile (message page).
		'ceo_profile'         => array( 'プロフィール', 'Profile' ),
		'name_label'          => array( '氏名', 'Name' ),
		'role_label'          => array( '役職', 'Title' ),
		'career_label'        => array( '経歴', 'Career' ),
		'links_label'         => array( 'リンク', 'Links' ),
		// Press kit.
		'download'            => array( 'ダウンロード', 'Download' ),
		'copy_text'           => array( 'コピー', 'Copy' ),
		'copied_text'         => array( 'コピーしました', 'Copied' ),
		// Contact form (README 9).
		'form_required'       => array( '必須', 'Required' ),
		'form_optional'       => array( '任意', 'Optional' ),
		'form_error_required' => array( '入力してください', 'This field is required' ),
		'form_error_email'    => array( 'メールアドレスの形式で入力してください', 'Enter a valid email address' ),
		'form_sending'        => array( '送信中…', 'Sending…' ),
	);
}

add_action(
	'init',
	static function () {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}
		foreach ( netelly_strings() as $key => $s ) {
			pll_register_string( $key, $s[0], 'Netelly テーマ', false );
		}
	}
);

/**
 * Translated UI string by key (Polylang string translation, current language).
 */
function netelly_t( string $key ): string {
	$all = netelly_strings();
	if ( ! isset( $all[ $key ] ) ) {
		return $key;
	}
	if ( function_exists( 'pll__' ) ) {
		return pll__( $all[ $key ][0] );
	}
	return 'en' === netelly_lang() ? $all[ $key ][1] : $all[ $key ][0];
}

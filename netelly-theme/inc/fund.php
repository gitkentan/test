<?php
/**
 * Creators Fund page (page-fund.php, /fund/): default texts, one-time setup on theme
 * update (Japanese + English page and application form), and the fund link target.
 *
 * Every text is edited in 固定ページ › Creators Fund; the form in Snow Monkey Forms.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published Creators Fund page in a language (0 = none).
 *
 * @param string $lang ja|en.
 */
function netelly_fund_page_id( string $lang ): int {
	$ids = netelly_ids_by_slug( 'page', 'fund', $lang );
	foreach ( $ids as $id ) {
		if ( 'page-fund.php' === get_page_template_slug( $id ) ) {
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
function netelly_fund_defaults( string $lang ): array {
	$ja = 'en' !== $lang;
	$L  = static fn( $j, $e ) => $ja ? $j : $e;
	return array(
		'hero_label'    => 'NETELLY CREATORS FUND',
		'hero_title'    => 'CREATORS FUND',
		'hero_copy'     => $L( "その企画を、\n作品にしよう。", "Make your idea\ninto a film." ),
		'hero_lead'     => $L(
			'Netellyと一緒に作品をつくり、NetellyのYouTubeチャンネルで届ける。監督・脚本家・映像クリエイターのための、作品づくりのプログラムです。',
			'Make your project with Netelly and release it on the Netelly YouTube channel. A program for directors, writers and filmmakers.'
		),
		'hero_cta'      => $L( '企画を応募する', 'Submit your project' ),
		'st_label'      => 'STATEMENT',
		'st_text'       => $L(
			'いいアイデアは、どこにでもある。足りないのは、形にする場所と、届ける場所。Creators Fundは、その両方をつくります。',
			'Great ideas are everywhere. What is missing is a place to make them and a place to share them. Creators Fund builds both.'
		),
		'offer_label'   => 'WHAT WE OFFER',
		'offer_heading' => $L( "作る。公開する。\n届ける。", "Make. Release.\nReach." ),
		'offers'        => array(
			array( 'en' => 'MAKE', 'title' => $L( '作品をつくる', 'Make the film' ), 'text' => $L( 'Netellyのプロデューサー・制作チームと一緒に、企画を映像作品として形にします。', 'Develop and produce your project together with Netelly’s producers and crew.' ), 'tag' => '' ),
			array( 'en' => 'RELEASE', 'title' => $L( 'YouTubeで公開する', 'Release on YouTube' ), 'text' => $L( '完成した作品は、NetellyのYouTubeチャンネルで公開します。', 'Finished works are released on the Netelly YouTube channel.' ), 'tag' => '' ),
			array( 'en' => 'PROMOTE', 'title' => $L( '届ける', 'Promote' ), 'text' => $L( 'SNSを中心に、作品とつくり手のプロモーションを行います。', 'We promote the work and its makers, mainly on social media.' ), 'tag' => '' ),
			array( 'en' => 'FUNDING', 'title' => $L( '制作費を支援する', 'Production funding' ), 'text' => $L( '企画の内容に応じて、制作費の支援も検討します。', 'Depending on the project, we may also support production costs.' ), 'tag' => $L( '企画により', 'Case by case' ) ),
		),
		'flow_label'    => 'HOW IT WORKS',
		'flow_heading'  => $L( '応募から公開まで', 'From idea to release' ),
		'steps'         => array(
			array( 'title' => $L( '応募', 'Submit' ), 'text' => $L( 'フォームから企画を送ってください。構想段階でも大丈夫です。', 'Send us your project through the form. Early ideas are welcome.' ) ),
			array( 'title' => $L( '相談', 'Talk' ), 'text' => $L( '内容を拝見し、Netellyからご連絡します。', 'We review it and get in touch.' ) ),
			array( 'title' => $L( '制作', 'Make' ), 'text' => $L( '一緒に企画を磨き、作品をつくります。', 'We shape the project together and make the film.' ) ),
			array( 'title' => $L( '公開', 'Release' ), 'text' => $L( 'NetellyのYouTubeチャンネルで公開し、プロモーションします。', 'We release it on the Netelly YouTube channel and promote it.' ) ),
		),
		'who_label'     => 'FOR',
		'who_heading'   => $L( "つくりたい人、\nすべてに。", "For everyone\nwho makes." ),
		'who_roles'     => "DIRECTOR\nWRITER\nACTOR\nCINEMATOGRAPHER\nEDITOR\nPLANNER\nCREATOR",
		'who_note'      => $L( 'ドラマ・ショートドラマ・短編映画など、映像作品の企画であればジャンルは問いません。', 'Drama, short drama, short film — any genre of moving-image project is welcome.' ),
		'faq_label'     => 'FAQ',
		'faq_heading'   => $L( 'よくある質問', 'Questions' ),
		'faqs'          => array(
			array( 'q' => $L( 'どんな企画を応募できますか？', 'What kind of projects can I submit?' ), 'a' => $L( 'ドラマ、ショートドラマ、短編映画など、映像作品の企画であればジャンルは問いません。', 'Any moving-image project — drama, short drama, short film and more.' ) ),
			array( 'q' => $L( '企画書がなくても応募できますか？', 'Do I need a written proposal?' ), 'a' => $L( 'はい。アイデアの段階でも応募できます。わかる範囲で内容を書いてください。', 'No. You can apply at the idea stage — tell us as much as you can.' ) ),
			array( 'q' => $L( '制作費は支援してもらえますか？', 'Will you fund my production?' ), 'a' => $L( '企画の内容に応じて個別にご相談します。すべての企画が対象になるわけではありません。', 'We discuss it case by case. Not every project receives funding.' ) ),
			array( 'q' => $L( '応募後はどうなりますか？', 'What happens after I apply?' ), 'a' => $L( '内容を確認のうえ、Netellyからご連絡します。', 'We review your project and get in touch.' ) ),
		),
		'apply_label'   => 'APPLY',
		'apply_heading' => $L( '企画を送る', 'Send your project' ),
		'apply_text'    => $L( 'まずは気軽に。あなたのアイデアを聞かせてください。', 'Start simple. Tell us your idea.' ),
	);
}

/**
 * Snow Monkey Forms content + meta of the application form.
 *
 * @param string $lang ja|en.
 */
function netelly_fund_form( string $lang ): array {
	$ja     = 'en' !== $lang;
	$L      = static fn( $j, $e ) => $ja ? $j : $e;
	$req    = wp_json_encode( array( 'required' => true ) );
	$labels = array(
		'name'      => $L( 'お名前', 'Name' ),
		'email'     => $L( 'メールアドレス', 'Email' ),
		'role'      => $L( '肩書き・活動内容', 'Role / what you do' ),
		'title'     => $L( '企画タイトル', 'Project title' ),
		'logline'   => $L( 'ログライン（企画を一行で）', 'Logline' ),
		'synopsis'  => $L( '企画の内容', 'About the project' ),
		'portfolio' => $L( '作品・ポートフォリオのURL', 'Portfolio / past work URL' ),
		'file'      => $L( '企画書（PDFなど）', 'Proposal (PDF etc.)' ),
	);
	$item   = static function ( string $label, string $for, string $type, array $attrs, bool $show = true ): string {
		$inner = serialize_block(
			array(
				'blockName'    => 'snow-monkey-forms/' . $type,
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		$head  = '';
		if ( $show ) {
			$text = '<span class="smf-item__label__text">' . esc_html( $label ) . '</span>';
			$head = '<div class="smf-item__col smf-item__col--label"><div class="smf-item__label">' . ( $for ? '<label for="' . esc_attr( $for ) . '">' . $text . '</label>' : $text ) . '</div></div>';
		}
		return '<!-- wp:snow-monkey-forms/item ' . ( $show ? '' : '{"isDisplayLabelColumn":false} ' ) . '-->'
			. '<div class="wp-block-snow-monkey-forms-item smf-item' . ( $show ? '' : ' smf-item--divider' ) . '">' . $head
			. '<div class="smf-item__col smf-item__col--controls"><div class="smf-item__controls">' . $inner . '</div></div></div>'
			. '<!-- /wp:snow-monkey-forms/item -->';
	};
	$items  = array(
		$item( $labels['name'], 'name', 'control-text', array( 'name' => 'name', 'id' => 'name', 'placeholder' => $L( '山田 太郎', 'Taro Yamada' ), 'autocomplete' => 'name', 'validations' => $req ) ),
		$item( $labels['email'], 'email', 'control-email', array( 'name' => 'email', 'id' => 'email', 'placeholder' => 'name@example.com', 'validations' => wp_json_encode( array( 'required' => true, 'email' => true ) ) ) ),
		$item( $labels['role'], 'role', 'control-text', array( 'name' => 'role', 'id' => 'role', 'placeholder' => $L( '映像ディレクター / 脚本家 など', 'Director, writer…' ) ) ),
		$item( $labels['title'], 'title', 'control-text', array( 'name' => 'title', 'id' => 'title', 'validations' => $req ) ),
		$item( $labels['logline'], 'logline', 'control-text', array( 'name' => 'logline', 'id' => 'logline', 'placeholder' => $L( '例：上京した新人マネージャーが、人気YouTuberの裏側を知る', 'In one sentence, what is it about?' ), 'validations' => $req ) ),
		$item( $labels['synopsis'], 'synopsis', 'control-textarea', array( 'name' => 'synopsis', 'id' => 'synopsis', 'rows' => 8, 'placeholder' => $L( 'あらすじ、つくりたい理由、想定する尺や形式など', 'Story, why you want to make it, length and format…' ), 'validations' => $req ) ),
		$item( $labels['portfolio'], 'portfolio', 'control-url', array( 'name' => 'portfolio', 'id' => 'portfolio', 'placeholder' => 'https://' ) ),
		$item( $labels['file'], 'file', 'control-file', array( 'name' => 'file', 'id' => 'file' ) ),
		$item( '', '', 'control-checkboxes', array( 'name' => 'consent', 'options' => $L( 'プライバシーポリシーに同意する', 'I agree to the Privacy Policy' ), 'validations' => $req ), false ),
	);
	$done   = '<!-- wp:heading -->' . "\n" . '<h2 class="wp-block-heading">' . esc_html( $L( '応募を受け付けました', 'Thank you for your submission' ) ) . '</h2>' . "\n" . '<!-- /wp:heading -->'
		. "\n\n" . '<!-- wp:paragraph -->' . "\n" . '<p>' . esc_html( $L( '内容を確認のうえ、Netellyからご連絡します。自動返信メールをお送りしましたので、あわせてご確認ください。', 'We will review your project and get in touch. A confirmation has been sent to your email address.' ) ) . '</p>' . "\n" . '<!-- /wp:paragraph -->';
	$fields = implode( "\n\n", array_map( static fn( $k, $v ) => "■ {$v}\n{{$k}}", array_keys( $labels ), $labels ) );
	return array(
		'title'   => $L( 'Creators Fund 応募', 'Creators Fund (EN)' ),
		'content' => '<!-- wp:snow-monkey-forms/form--input -->' . "\n" . '<div class="wp-block-snow-monkey-forms-form--input smf-form">' . implode( "\n\n", $items ) . '</div>' . "\n" . '<!-- /wp:snow-monkey-forms/form--input -->'
			. "\n\n" . '<!-- wp:snow-monkey-forms/form--complete -->' . "\n" . $done . "\n" . '<!-- /wp:snow-monkey-forms/form--complete -->',
		'meta'    => array(
			'use_confirm_page'            => true,
			'confirm_button_label'        => $L( '内容を確認する →', 'Review →' ),
			'back_button_label'           => $L( '修正する', 'Edit' ),
			'send_button_label'           => $L( '応募する →', 'Submit →' ),
			'administrator_email_to'      => get_option( 'admin_email' ),
			'administrator_email_subject' => $L( '【Creators Fund】企画の応募がありました', '[Creators Fund] New submission (EN)' ),
			'administrator_email_body'    => $L( "Creators Fundに企画の応募がありました。\n\n", "A new project was submitted from the English site.\n\n" ) . $fields,
			'administrator_email_replyto' => '{email}',
			'administrator_email_sender'  => 'Netelly Creators Fund',
			'auto_reply_email_to'         => '{email}',
			'auto_reply_email_subject'    => $L( '【Netelly Creators Fund】ご応募ありがとうございます', '[Netelly Creators Fund] Thank you for your submission' ),
			'auto_reply_email_body'       => $L(
				"{name} 様\n\nNetelly Creators Fundへご応募いただき、ありがとうございます。\n以下の内容で受け付けました。内容を確認のうえ、ご連絡します。\n\n" . $fields . "\n\n――\nNetelly株式会社",
				"Dear {name},\n\nThank you for submitting to Netelly Creators Fund.\nWe have received the project below and will get in touch after reviewing it.\n\n" . $fields . "\n\n--\nNetelly Inc."
			),
			'auto_reply_email_sender'     => 'Netelly Creators Fund',
		),
	);
}

/*
 * One-time setup on theme update: Japanese + English page, application forms and texts.
 * Runs once (option netelly_fund_v1); an existing /fund/ page is left as it is.
 */
add_action(
	'init',
	static function () {
		if ( get_option( 'netelly_fund_v1' ) || ! function_exists( 'update_field' ) || ! function_exists( 'pll_set_post_language' ) ) {
			return;
		}
		update_option( 'netelly_fund_v1', 1 );
		foreach ( array( 'ja', 'en' ) as $lang ) {
			if ( netelly_ids_by_slug( 'page', 'fund', $lang, 0, array( 'publish', 'draft', 'private', 'pending', 'future' ) ) ) {
				return;
			}
		}
		$tr = array();
		foreach ( array( 'ja', 'en' ) as $lang ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => 'Creators Fund',
					'menu_order'  => 9,
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				return;
			}
			pll_set_post_language( $id, $lang );
			// Slug after the language is known (shared slugs, see inc/i18n.php).
			wp_update_post( array( 'ID' => $id, 'post_name' => 'fund' ) );
			update_post_meta( $id, '_wp_page_template', 'page-fund.php' );
			update_field( 'field_nt_page_hero_seo_description', 'en' === $lang ? 'Netelly Creators Fund: make your project with Netelly and release it on the Netelly YouTube channel.' : 'Netelly Creators Fund：Netellyと一緒に作品をつくり、NetellyのYouTubeチャンネルで届ける、クリエイターのためのプログラム。', $id );
			foreach ( netelly_fund_defaults( $lang ) as $name => $value ) {
				update_field( 'field_nt_page_fund_' . $name, $value, $id );
			}
			if ( post_type_exists( 'snow-monkey-forms' ) ) {
				$form = netelly_fund_form( $lang );
				// No logged-in user here: keep KSES from rewriting the block comments ("form--input").
				kses_remove_filters();
				$fid = wp_insert_post(
					wp_slash(
						array(
							'post_type'    => 'snow-monkey-forms',
							'post_status'  => 'publish',
							'post_title'   => $form['title'],
							'post_content' => $form['content'],
						)
					)
				);
				kses_init();
				if ( $fid ) {
					foreach ( $form['meta'] as $k => $v ) {
						update_post_meta( $fid, $k, $v );
					}
					update_field( 'field_nt_page_fund_form', $fid, $id );
				}
			}
			$tr[ $lang ] = (int) $id;
		}
		if ( count( $tr ) > 1 ) {
			pll_save_post_translations( $tr );
		}
		// News that linked out to the old fund site now open the page.
		$linked = get_posts(
			array(
				'post_type'        => 'post',
				'post_status'      => 'any',
				'posts_per_page'   => -1,
				'fields'           => 'ids',
				'lang'             => '',
				'suppress_filters' => false,
				'meta_query'       => array( array( 'key' => 'external_url', 'value' => 'fund.netelly.co.jp', 'compare' => 'LIKE' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		foreach ( $linked as $pid ) {
			$lang = (string) pll_get_post_language( $pid );
			if ( isset( $tr[ $lang ] ) ) {
				update_post_meta( $pid, 'external_url', get_permalink( $tr[ $lang ] ) );
			}
		}
		flush_rewrite_rules( false );
	},
	30
);

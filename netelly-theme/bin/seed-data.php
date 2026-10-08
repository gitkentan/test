<?php
/**
 * Seed content. JP = design copy verbatim. EN = draft translation for review.
 *
 * @package netelly
 */

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment

const NTS_FUND_URL = 'https://fund.netelly.co.jp';

const NTS_CATEGORIES = array(
	'press' => array( 'ja' => 'プレスリリース', 'en' => 'Press release' ),
	'works' => array( 'ja' => '作品', 'en' => 'Works' ),
	'fund'  => array( 'ja' => 'ファンド', 'en' => 'Fund' ),
	'info'  => array( 'ja' => 'お知らせ', 'en' => 'Announcements' ),
);

const NTS_PAGES = array(
	'home'     => array( 'slug' => 'home', 'order' => 0, 'title' => array( 'ja' => 'トップ', 'en' => 'Home' ) ),
	'company'  => array( 'slug' => 'company', 'order' => 1, 'template' => 'page-company.php', 'en_title' => 'COMPANY', 'title' => array( 'ja' => '企業情報', 'en' => 'Company' ) ),
	'message'  => array( 'slug' => 'message', 'order' => 2, 'template' => 'page-message.php', 'en_title' => 'MESSAGE', 'title' => array( 'ja' => '代表メッセージ', 'en' => 'CEO Message' ) ),
	'business' => array( 'slug' => 'business', 'order' => 3, 'template' => 'page-business.php', 'en_title' => 'BUSINESS', 'title' => array( 'ja' => '事業', 'en' => 'Business' ) ),
	'news'     => array( 'slug' => 'news', 'order' => 4, 'en_title' => 'NEWS', 'title' => array( 'ja' => 'ニュース', 'en' => 'News' ) ),
	'careers'  => array( 'slug' => 'careers', 'order' => 5, 'template' => 'page-careers.php', 'en_title' => 'CAREERS', 'title' => array( 'ja' => '採用', 'en' => 'Careers' ) ),
	'contact'  => array( 'slug' => 'contact', 'order' => 6, 'template' => 'page-contact.php', 'en_title' => 'CONTACT', 'title' => array( 'ja' => 'お問い合わせ', 'en' => 'Contact' ) ),
	'privacy'  => array( 'slug' => 'privacy', 'order' => 7, 'template' => 'page-privacy.php', 'en_title' => 'PRIVACY', 'title' => array( 'ja' => 'プライバシーポリシー', 'en' => 'Privacy Policy' ) ),
);

const NTS_WORKS = array(
	array(
		'slug'   => 'more-than-friends',
		'date'   => '2021-02-19 10:00:00',
		'title'  => array( 'ja' => '友達以上、恋人未満', 'en' => 'More Than Friends, Less Than Lovers' ),
		'fields' => array(
			'genre'              => 'drama',
			'year'               => '2021',
			'format'             => array( 'ja' => '恋愛ドラマ · 全4話', 'en' => 'Romance drama · 4 episodes' ),
			'platform'           => 'YouTube',
			'card_note'          => '',
			'is_featured'        => 1,
			'featured_text'      => array(
				'ja' => 'マッチングアプリで出会った男女の、名前のつかない関係を描く全4話の恋愛ドラマ。総再生回数は400万回を超えました。',
				'en' => 'A four-episode romance about a man and a woman who meet on a dating app, and the relationship they can’t put a name to. It has passed 4 million total views.',
			),
			'trailer_url'        => 'https://www.youtube.com/',  // Replace with the real trailer URL.
			'playlist_url'       => 'https://www.youtube.com/',  // Replace with the real playlist URL.
			'series_label'       => 'NETELLY ORIGINAL SERIES',
			'synopsis_lead'      => array( 'ja' => "近づくほど、\n名前のない関係になっていく。", 'en' => "The closer they get,\nthe more nameless it becomes." ),
			'synopsis'           => array(
				'ja' => 'マッチングアプリで出会い、会うたびに距離を縮めていく三輪可乃子と玉田秀介。けれど二人の関係には、いつまでも名前がつかない。友達以上、恋人未満のまま続く日々に不満を抱いた可乃子は、ある夜、重い口を開く。',
				'en' => 'Kanoko Miwa and Shusuke Tamada met on a dating app and grow closer every time they meet. Yet their relationship never gets a name. Frustrated by days that stay “more than friends, less than lovers,” Kanoko finally speaks up one night.',
			),
			'total_views'        => 4000000,
			'total_views_suffix' => '+',
			'total_views_note'   => array( 'ja' => 'YouTube・TikTok等の総再生回数（2022年1月時点）', 'en' => 'Total views across YouTube, TikTok and more (as of January 2022)' ),
		),
		'episodes' => array(
			array( 'no' => 'EP.01', 'title' => array( 'ja' => '彼とは"マッチングアプリ"で出会った', 'en' => 'I met him on a “dating app”' ), 'date' => '20210219', 'url' => '' ),
			array( 'no' => 'EP.02', 'title' => array( 'ja' => '薬局でコンドームを買う', 'en' => 'Buying condoms at the drugstore' ), 'date' => '20210422', 'url' => '' ),
			array( 'no' => 'EP.03', 'title' => array( 'ja' => '私たちって、セフレ？', 'en' => 'So are we friends with benefits?' ), 'date' => '20210501', 'url' => '' ),
			array( 'no' => 'EP.04', 'title' => array( 'ja' => 'ただ、触れただけ。', 'en' => 'We only touched.' ), 'date' => '20210508', 'url' => '' ),
		),
		'cast' => array(
			array( 'role' => array( 'ja' => '三輪可乃子', 'en' => 'Kanoko Miwa' ), 'name' => array( 'ja' => '佐咲日菜', 'en' => 'Hina Sasaki' ) ),
			array( 'role' => array( 'ja' => '玉田秀介', 'en' => 'Shusuke Tamada' ), 'name' => array( 'ja' => '鈴木研', 'en' => 'Ken Suzuki' ) ),
			array( 'role' => array( 'ja' => '森山雫', 'en' => 'Shizuku Moriyama' ), 'name' => array( 'ja' => '鈴木あかり', 'en' => 'Akari Suzuki' ) ),
		),
		'staff' => array(
			array( 'role' => array( 'ja' => '監督・脚本', 'en' => 'Director / Writer' ), 'name' => array( 'ja' => '深谷晃成', 'en' => 'Kosei Fukaya' ) ),
			array( 'role' => array( 'ja' => '音楽', 'en' => 'Music' ), 'name' => array( 'ja' => '大垣友', 'en' => 'Yu Ogaki' ) ),
			array( 'role' => array( 'ja' => 'エンディング', 'en' => 'Ending theme' ), 'name' => array( 'ja' => '『夜道の先に』作曲：大垣友', 'en' => '“Yomichi no Saki ni” composed by Yu Ogaki' ) ),
			array( 'role' => array( 'ja' => '撮影・編集', 'en' => 'Cinematography / Editing' ), 'name' => array( 'ja' => '村上岳', 'en' => 'Gaku Murakami' ) ),
			array( 'role' => array( 'ja' => '撮影助手', 'en' => 'Camera assistant' ), 'name' => array( 'ja' => '齋藤工', 'en' => 'Takumi Saito' ) ),
			array( 'role' => array( 'ja' => '録音・MA', 'en' => 'Sound / MA' ), 'name' => array( 'ja' => '野原啓太', 'en' => 'Keita Nohara' ) ),
			array( 'role' => array( 'ja' => 'ヘアメイク', 'en' => 'Hair & makeup' ), 'name' => array( 'ja' => '安藤メイ', 'en' => 'Mei Ando' ) ),
			array( 'role' => array( 'ja' => 'プロデューサー・監督助手', 'en' => 'Producer / Assistant director' ), 'name' => array( 'ja' => '岸本拓之', 'en' => 'Hiroyuki Kishimoto' ) ),
			array( 'role' => array( 'ja' => '制作', 'en' => 'Production' ), 'name' => array( 'ja' => '佐藤新太、もりみさき、尾崎果奈、箸本のぞみ、大垣友', 'en' => 'Arata Sato, Misaki Mori, Kana Ozaki, Nozomi Hashimoto, Yu Ogaki' ) ),
			array( 'role' => array( 'ja' => '制作協力', 'en' => 'Production cooperation' ), 'name' => array( 'ja' => '第27班', 'en' => 'Dai 27 Han' ) ),
			array( 'role' => array( 'ja' => '製作・配給', 'en' => 'Produced & distributed by' ), 'name' => array( 'ja' => 'Netelly Inc.', 'en' => 'Netelly Inc.' ) ),
		),
	),
	array(
		'slug'   => 'youtuber-manager',
		'date'   => '2022-04-30 10:00:00',
		'title'  => array( 'ja' => 'YouTuberのマネージャーをやってみたらわかったこと。', 'en' => 'What I Learned as a YouTuber’s Manager' ),
		'fields' => array(
			'genre'     => 'drama',
			'year'      => '2022',
			'format'    => array( 'ja' => 'Z世代向けドラマ', 'en' => 'Drama for Gen Z' ),
			'platform'  => 'YouTube',
			'card_note' => '',
		),
	),
	array(
		'slug'   => 'waiting-room',
		'date'   => '2022-06-01 10:00:00',
		'title'  => array( 'ja' => 'Waiting Room -来世を待つ人々-', 'en' => 'Waiting Room -People Waiting for the Next Life-' ),
		'fields' => array(
			'genre'     => 'drama',
			'year'      => '',
			'format'    => '',
			'card_note' => array( 'ja' => '出演：坂口候一、辻凪子', 'en' => 'Starring Koichi Sakaguchi, Nagiko Tsuji' ),
		),
	),
	array(
		'slug'   => 'our-five-days',
		'date'   => '2022-07-01 10:00:00',
		'title'  => array( 'ja' => '私たちの4泊5日', 'en' => 'Our Five Days, Four Nights' ),
		'fields' => array(
			'genre'     => 'drama',
			'year'      => '',
			'format'    => '',
			'card_note' => array( 'ja' => '出演：高橋アリス', 'en' => 'Starring Alice Takahashi' ),
		),
	),
	array(
		'slug'   => 'three-guys-trip',
		'date'   => '2022-08-01 10:00:00',
		'title'  => array( 'ja' => 'おとこ3人の旅は暑苦しいですか？', 'en' => 'Is a Trip with Three Guys Too Much?' ),
		'fields' => array(
			'genre'     => 'variety',
			'year'      => '',
			'format'    => '',
			'card_note' => array( 'ja' => '出演：小西成弥、樋口裕太、河原田巧也', 'en' => 'Starring Seiya Konishi, Yuta Higuchi, Takuya Kawaharada' ),
		),
	),
	array(
		'slug'   => 'new-original-series',
		'date'   => '2026-10-01 10:00:00',
		'title'  => array( 'ja' => '新作オリジナルシリーズ', 'en' => 'New Original Series' ),
		'fields' => array(
			'genre'     => 'in_development',
			'year'      => '',
			'format'    => '',
			'card_note' => array( 'ja' => '制作中', 'en' => 'In production' ),
		),
	),
);

const NTS_NEWS = array(
	array(
		'date'  => '2026-10-08',
		'cat'   => 'info',
		'title' => array( 'ja' => 'コーポレートサイトをリニューアルしました', 'en' => 'We have renewed our corporate website' ),
	),
	array(
		'date'  => '2022-04-30',
		'cat'   => 'works',
		'work'  => 'youtuber-manager',
		'title' => array( 'ja' => 'Z世代向けドラマ『YouTuberのマネージャーをやってみたらわかったこと。』配信スタート', 'en' => 'Gen Z drama “What I Learned as a YouTuber’s Manager” now streaming' ),
	),
	array(
		'date'    => '2022-01-26',
		'cat'     => 'works',
		'work'    => 'more-than-friends',
		'title'   => array( 'ja' => "オリジナルドラマ『友達以上、恋人未満』総再生回数が400万回を突破", 'en' => 'Original drama “More Than Friends, Less Than Lovers” surpasses 4 million total views' ),
		'body'    => array(
			'ja' => array(
				'Netelly株式会社は、女優・モデルの佐咲日菜さんがヒロインを演じたオリジナルドラマ『友達以上、恋人未満』の総再生回数が、YouTube・TikTokなどのSNS合計で400万回を突破したことをお知らせします。',
				'本作は、2021年春に公開したNetellyオリジナル第1弾の映像作品です。マッチングアプリで出会った20代の男女が、友達以上、恋人未満の関係を続けるなかで、主人公の可乃子が自分の気持ちと向き合う姿を描いています。',
				'三輪可乃子役を佐咲日菜さん、玉田秀介役を鈴木研さんが演じ、総監督は若手演出家コンクール2019年度最優秀賞を受賞した深谷晃成さんが務めました。',
			),
			'en' => array(
				'Netelly Inc. is pleased to announce that its original drama “More Than Friends, Less Than Lovers,” starring actress and model Hina Sasaki as the heroine, has surpassed 4 million total views across YouTube, TikTok and other social platforms.',
				'Released in spring 2021, the series is Netelly’s first original video production. It follows a man and a woman in their twenties who meet on a dating app, and portrays the heroine Kanoko as she faces her own feelings while their relationship stays “more than friends, less than lovers.”',
				'Kanoko Miwa is played by Hina Sasaki and Shusuke Tamada by Ken Suzuki. The series was directed by Kosei Fukaya, winner of the top prize at the 2019 Young Directors Competition.',
			),
		),
		'summary' => array(
			'ja' => "タイトル：友達以上、恋人未満\n話数：全4話（予告編あり）\n監督・脚本：深谷晃成\n出演：佐咲日菜、鈴木研、鈴木あかり\n製作・配給：Netelly Inc.",
			'en' => "Title: More Than Friends, Less Than Lovers\nEpisodes: 4 (with trailer)\nDirector / Writer: Kosei Fukaya\nCast: Hina Sasaki, Ken Suzuki, Akari Suzuki\nProduced & distributed by: Netelly Inc.",
		),
	),
	array(
		'date'  => '2021-08-03',
		'cat'   => 'fund',
		'fund'  => true,
		'title' => array( 'ja' => '若手クリエイターへ出資する「Creators Fund」を設立', 'en' => 'Netelly establishes “Creators Fund” to invest in emerging creators' ),
	),
	array(
		'date'  => '2021-02-15',
		'cat'   => 'press',
		'title' => array( 'ja' => '動画配信サービス「Netelly」を正式リリース', 'en' => 'Streaming service “Netelly” officially launched' ),
	),
	array(
		'date'  => '2020-03-31',
		'cat'   => 'fund',
		'fund'  => true,
		'title' => array( 'ja' => 'クリエイターに最大50万円の番組制作費を付与', 'en' => 'Creators to receive up to JPY 500,000 in production funding' ),
	),
	array(
		'date'  => '2020-01-22',
		'cat'   => 'press',
		'title' => array( 'ja' => 'KVPより2,500万円の資金調達を実施', 'en' => 'Netelly raises JPY 25 million from KVP' ),
	),
);

const NTS_POSITIONS = array(
	array( 'title' => array( 'ja' => 'プロデューサー', 'en' => 'Producer' ), 'type' => array( 'ja' => '正社員', 'en' => 'Full-time' ), 'location' => array( 'ja' => '東京', 'en' => 'Tokyo' ), 'summary' => array( 'ja' => '企画開発、予算・スケジュール管理、キャスティング、配給計画', 'en' => 'Development, budget and schedule management, casting, distribution planning' ) ),
	array( 'title' => array( 'ja' => '映像ディレクター', 'en' => 'Director' ), 'type' => array( 'ja' => '正社員 / 業務委託', 'en' => 'Full-time / Contract' ), 'location' => array( 'ja' => '東京', 'en' => 'Tokyo' ), 'summary' => array( 'ja' => 'ドラマ・ショート作品の演出、撮影現場の進行', 'en' => 'Directing dramas and short-form works, running shoots on set' ) ),
	array( 'title' => array( 'ja' => '脚本家', 'en' => 'Screenwriter' ), 'type' => array( 'ja' => '業務委託', 'en' => 'Contract' ), 'location' => array( 'ja' => '東京', 'en' => 'Tokyo' ), 'summary' => array( 'ja' => 'オリジナルシリーズの脚本開発', 'en' => 'Script development for original series' ) ),
	array( 'title' => array( 'ja' => '映像編集', 'en' => 'Video Editor' ), 'type' => array( 'ja' => '正社員 / 業務委託', 'en' => 'Full-time / Contract' ), 'location' => array( 'ja' => '東京', 'en' => 'Tokyo' ), 'summary' => array( 'ja' => '本編・予告編・SNS向け動画の編集', 'en' => 'Editing episodes, trailers and social videos' ) ),
	array( 'title' => array( 'ja' => 'SNSマーケター', 'en' => 'Social Media Marketer' ), 'type' => array( 'ja' => '正社員', 'en' => 'Full-time' ), 'location' => array( 'ja' => '東京', 'en' => 'Tokyo' ), 'summary' => array( 'ja' => '作品のSNS展開、YouTube・TikTokの運用と分析', 'en' => 'Social rollout of our works; running and analyzing YouTube and TikTok' ) ),
	array( 'title' => array( 'ja' => '長期インターン', 'en' => 'Long-term Intern' ), 'type' => array( 'ja' => '学生', 'en' => 'Student' ), 'location' => array( 'ja' => '東京', 'en' => 'Tokyo' ), 'summary' => array( 'ja' => '制作アシスタント、リサーチ、SNS運用補助', 'en' => 'Production assistance, research, social media support' ) ),
);

/**
 * Per-language page fields and site settings.
 *
 * @param string   $lang  ja|en.
 * @param callable $purl  ( key, lang, hash ) => page URL.
 * @param array    $works slug => [ lang => id ].
 */
function nts_lang_data( string $lang, callable $purl, array $works ): array {
	$ja   = 'ja' === $lang;
	$L    = static fn( $j, $e ) => $ja ? $j : $e;
	$p    = static fn( $key, $hash = '' ) => $purl( $key, $lang, $hash );
	$wurl = home_url( $ja ? '/works/' : '/en/works/' );
	$addr = $L( "〒141-0033\n東京都品川区西品川1-1-1\n住友不動産大崎ガーデンタワー 9F", "Sumitomo Fudosan Osaki Garden Tower 9F\n1-1-1 Nishi-Shinagawa, Shinagawa-ku,\nTokyo 141-0033, Japan" );
	$map  = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( '住友不動産大崎ガーデンタワー' );

	$msg_p1 = $L(
		'今、世界中でテクノロジーの発展による社会、産業、ライフスタイルの変革が起こっています。つまり世界は「時代の転換点」を迎えているのです。',
		'Around the world, technological progress is transforming society, industry and the way we live. In other words, the world has reached a turning point.'
	);
	// Body is the client's original text; 「ことをを」 is kept as-is until confirmed (CLAUDE.md).
	$msg_body = implode(
		"\n\n",
		array(
			$msg_p1,
			$L(
				'米国の自国利益中心主義と中国型グローバリズムの伸長による、米中を軸とするダイナミズムの変化。 金融緩和の正常化による資金調達環境の変化。そしてデジタルトランスフォーメーションの一層の拡大による産業構造と競争環境の変化など。このような世界では、社会や産業のあらゆる分野が次々と再定義され、ビジネスのあり方やライフスタイルを根幹から変え、新しい事業の機会が創出されると考えています。我々はこうした大きなターニングポイントに直面しているという現状認識の下、エンターテインメントとテクノロジーを融合させ、文化をアップデートすることをを目標にしております。',
				'The shifting dynamics between the US and China, driven by America’s self-interest-first policies and the rise of Chinese-style globalism. The changing financing environment as monetary easing normalizes. And the transformation of industrial structures and competition brought about by an ever-expanding digital transformation. In a world like this, every field of society and industry is being redefined one after another, fundamentally changing how business is done and how people live — and, we believe, creating new business opportunities. Recognizing that we are facing a major turning point, our goal is to fuse entertainment and technology and to update culture.'
			),
			$L(
				'Netellyの使命は、グローバル社会の責任ある一員として、社会的課題解決に取り組み、社会経済の発展や人々の生活の向上に寄与する、より高い付加価値を提供していくこと、そしてあらゆる分野が再定義される中で、エンターテインメントの力による次世代のインフラ構築や、イノベーションを起こしていきたいと考えております。そして私たちは、あくなき「エンターテイナー」であり続け、エンターテインメントの分野・市場において世界のトッププレーヤーとの競争に勝ち抜き、地域経済や社会に貢献する真のグローバルエンターテインメント企業を目指して参ります。',
				'Netelly’s mission is to take on social issues as a responsible member of the global community and to deliver greater added value that contributes to economic development and better lives — and, as every field is being redefined, to build next-generation infrastructure and drive innovation through the power of entertainment. We will remain relentless entertainers, competing with and winning against the world’s top players in entertainment, and aim to become a truly global entertainment company that contributes to local economies and society.'
			),
		)
	);

	$settings = array(
		// Company / contact.
		'company_name'       => $L( 'Netelly株式会社', 'Netelly Inc.' ),
		'company_address'    => $L( "〒141-0033 東京都品川区西品川1-1-1\n住友不動産大崎ガーデンタワー 9F", "Sumitomo Fudosan Osaki Garden Tower 9F\n1-1-1 Nishi-Shinagawa, Shinagawa-ku, Tokyo" ),
		'contact_email'      => 'info@netelly.com',
		'contact_tel'        => '03-4400-1235',
		'contact_hours'      => $L( '平日 10:00–18:00', 'Weekdays 10:00–18:00 JST' ),
		'copyright'          => '© 2026 NETELLY INC.',
		'footer_domain'      => 'NETELLY.CO.JP',
		// External.
		'fund_url'           => NTS_FUND_URL,
		'youtube_url'        => 'https://www.youtube.com/',
		'line_url'           => 'https://line.me/',
		'x_url'              => 'https://x.com/',
		'instagram_url'      => 'https://www.instagram.com/',
		// Prefooter.
		'pf_sponsor_title'   => $L( 'スポンサー募集', 'Sponsorship' ),
		'pf_sponsor_text'    => $L( '協賛・タイアップのご相談はこちらから', 'For sponsorship and tie-up inquiries' ),
		'pf_sponsor_link'    => nts_link( $L( 'スポンサー募集', 'Sponsorship' ), $p( 'contact' ) ),
		'pf_creators_title'  => 'For Creators',
		'pf_creators_badge'  => $L( 'エントリー受付中', 'Now open' ),
		'pf_creators_text'   => $L( '制作会社・クリエイター・インフルエンサー募集', 'Calling production companies, creators and influencers' ),
		// Careers band.
		'cb_label'           => 'CAREERS',
		'cb_heading'         => $L( "好きな作品を、\n仕事にする。", "Make the stories you love\nyour work." ),
		'cb_button'          => nts_link( $L( '募集職種を見る →', 'View open positions →' ), $p( 'careers', '#positions' ) ),
		// Top: hero.
		'hero_label'         => 'NETELLY INC. · TOKYO / NEW YORK / LOS ANGELES',
		'hero_copy'          => $L( "まだ誰も見たことのない、\n物語を。", "Stories no one\nhas seen yet." ),
		'hero_lead'          => $L( '縦型ショートドラマから映画まで。企画し、制作し、届けるエンターテインメントカンパニー。', 'From vertical short dramas to feature films — an entertainment company that develops, produces and delivers.' ),
		'hero_cta1'          => nts_link( $L( 'Netellyについて →', 'About Netelly →' ), $p( 'company' ) ),
		'hero_cta2'          => nts_link( $L( '作品を見る', 'View works' ), $wurl ),
		// Top: short drama.
		'sd_label'           => 'VERTICAL SHORT DRAMA · PIONEER',
		'sd_heading'         => $L( "縦型ショートドラマを、\nいち早く。", "Vertical short drama,\nahead of the curve." ),
		'sd_text'            => $L(
			'Netellyは、スマートフォンの縦画面で観るショートドラマに、国内でいち早く取り組んできた制作会社です。1話数分の尺と縦の画面に合わせた脚本・撮影・編集のノウハウを、作品づくりの中で積み上げています。',
			'Netelly is a production company that was among the first in Japan to take on short dramas made for the vertical smartphone screen. Work by work, we keep building know-how in writing, shooting and editing for a few minutes per episode and a vertical frame.'
		),
		'sd_links'           => array(
			array( 'link' => nts_link( $L( 'ショートドラマを見る →', 'Watch short dramas →' ), $wurl ) ),
			array( 'link' => nts_link( $L( '制作のご相談 →', 'Discuss a production →' ), $p( 'contact' ) ) ),
		),
		'sd_items'           => array(
			array( 'image' => '', 'video' => '', 'link' => '' ),
			array( 'image' => '', 'video' => '', 'link' => '' ),
			array( 'image' => '', 'video' => '', 'link' => '' ),
		),
		// Top: originals + media.
		'orig_label'         => 'NETELLY ORIGINALS',
		'orig_heading'       => $L( 'オリジナル作品', 'Originals' ),
		'orig_link'          => nts_link( $L( 'すべての作品 →', 'All works →' ), $wurl ),
		'orig_works'         => array( $works['more-than-friends'][ $lang ], $works['youtuber-manager'][ $lang ], $works['waiting-room'][ $lang ], $works['new-original-series'][ $lang ] ),
		'media_label'        => 'MEDIA',
		'media_link'         => nts_link( $L( 'YouTubeで見る →', 'Watch on YouTube →' ), 'https://www.youtube.com/', true ),
		'media_work'         => $works['more-than-friends'][ $lang ],
		// Top: business.
		'biz_label'          => 'BUSINESS',
		'biz_heading'        => $L( '事業', 'Business' ),
		'biz_link'           => nts_link( $L( '事業について →', 'About our business →' ), $p( 'business' ) ),
		'biz_lead'           => $L(
			'Netellyは、作る・届ける・つなぐ・支えるという四つの機能を一つの組織の中に持ち、それらを循環させることで、才能と作品が生まれ続ける環境をつくっています。',
			'Netelly brings four functions — creating, delivering, connecting and supporting — together in one organization, and keeps them in motion so that talent and new work keep emerging.'
		),
		'biz_items'          => array(
			array( 'num' => 'I', 'en' => 'CREATIVE', 'title' => $L( '制作', 'Production' ), 'text' => $L( '縦型ショートドラマ、ドラマ・映画を企画し、制作する。', 'We develop and produce vertical short dramas, series and films.' ), 'image' => '', 'link' => nts_link( 'CREATIVE', $p( 'business', '#creative' ) ), 'is_fund' => 0 ),
			array( 'num' => 'II', 'en' => 'MEDIA', 'title' => $L( '配給・メディア', 'Distribution & Media' ), 'text' => $L( '劇場・配信・YouTubeへ作品を届ける。', 'We bring our work to theaters, streaming and YouTube.' ), 'image' => '', 'link' => nts_link( 'MEDIA', $p( 'business', '#media' ) ), 'is_fund' => 0 ),
			array( 'num' => 'III', 'en' => 'COMMUNITY', 'title' => $L( 'コミュニティ', 'Community' ), 'text' => $L( 'つくり手が出会い、次の企画が生まれる場。', 'A place where creators meet and new projects begin.' ), 'image' => '', 'link' => nts_link( 'COMMUNITY', $p( 'business', '#community' ) ), 'is_fund' => 0 ),
			array( 'num' => 'IV', 'en' => 'CREATORS FUND', 'title' => $L( 'クリエイターズファンド', 'Creators Fund' ), 'text' => $L( '監督・脚本家・プランナーの企画に出資する。', 'We invest in projects by directors, writers and planners.' ), 'image' => '', 'link' => '', 'is_fund' => 1 ),
		),
		// Top: message + news heads.
		'tm_label'           => 'MESSAGE',
		'tm_link'            => nts_link( $L( 'メッセージ全文 →', 'Read the full message →' ), $p( 'message' ) ),
		'tn_label'           => 'NEWS',
		'tn_heading'         => $L( 'ニュース', 'News' ),
		'tn_link'            => nts_link( $L( 'ニュース一覧 →', 'All news →' ), $p( 'news' ) ),
		// CEO message.
		'msg_label'          => 'MESSAGE FROM THE CEO',
		'msg_heading'        => $L( "文化を、\nアップデートする。", "Updating\nculture." ),
		'msg_excerpt'        => $msg_p1,
		'msg_body'           => $msg_body,
		'msg_role'           => $L( '代表取締役社長 兼 CEO', 'CEO' ),
		'msg_signature_role' => $L( 'Netelly株式会社 代表取締役社長 兼 CEO', 'CEO, Netelly Inc.' ),
		'msg_name'           => $L( '乙崎 健太', 'Kenta Otozaki' ),
		'msg_name_en'        => 'KENTA OTOZAKI',
		// Company profile + history.
		'profile'            => array(
			array( 'label' => $L( '会社名', 'Company name' ), 'value' => $L( 'Netelly株式会社（Netelly Inc.）', 'Netelly Inc.' ) ),
			array( 'label' => $L( '設立', 'Established' ), 'value' => $L( '2019年10月10日', 'October 10, 2019' ) ),
			array( 'label' => $L( '代表者', 'Representative' ), 'value' => $L( '代表取締役社長 兼 CEO　乙崎 健太', 'Kenta Otozaki, CEO' ) ),
			array( 'label' => $L( '資本金', 'Capital' ), 'value' => $L( '13,498,750円（2021年7月時点）', 'JPY 13,498,750 (as of July 2021)' ) ),
			array( 'label' => $L( '所在地', 'Address' ), 'value' => $L( "〒141-0033\n東京都品川区西品川1-1-1 住友不動産大崎ガーデンタワー 9F", "Sumitomo Fudosan Osaki Garden Tower 9F,\n1-1-1 Nishi-Shinagawa, Shinagawa-ku, Tokyo 141-0033" ) ),
			array( 'label' => $L( '事業内容', 'Business' ), 'value' => $L( "映像作品の企画・制作・配給\n動画配信サービスの開発・運営\nクリエイター支援事業（Creators Fund）", "Planning, production and distribution of video works\nDevelopment and operation of video streaming services\nCreator support (Creators Fund)" ) ),
			array( 'label' => $L( '制作拠点', 'Production bases' ), 'value' => $L( '東京 / ニューヨーク / ロサンゼルス', 'Tokyo / New York / Los Angeles' ) ),
			array( 'label' => $L( '主要取引銀行', 'Main bank' ), 'value' => $L( 'みずほ銀行 渋谷中央支店', 'Mizuho Bank, Shibuya-Chuo Branch' ) ),
			array( 'label' => $L( 'お問い合わせ', 'Contact' ), 'value' => 'info@netelly.com' ),
		),
		'history'            => array(
			array( 'year' => '2019.10', 'text' => $L( '東京都品川区にNetelly株式会社を設立', 'Netelly Inc. founded in Shinagawa, Tokyo' ) ),
			array( 'year' => '2020.01', 'text' => $L( '株式会社KVP（現 株式会社ANOBAKA）より2,500万円の資金調達を実施', 'Raised JPY 25 million from KVP Inc. (now ANOBAKA Inc.)' ) ),
			array( 'year' => '2021.02', 'text' => $L( '動画配信サービス「Netelly」を正式リリース。オリジナルドラマ『友達以上、恋人未満』配信開始', 'Officially launched the streaming service “Netelly” and began streaming the original drama “More Than Friends, Less Than Lovers”' ) ),
			array( 'year' => '2021.08', 'text' => $L( '若手クリエイターへ出資する「Creators Fund」を設立', 'Established “Creators Fund” to invest in emerging creators' ) ),
			array( 'year' => '2022.01', 'text' => $L( '『友達以上、恋人未満』の総再生回数が400万回を突破', '“More Than Friends, Less Than Lovers” surpassed 4 million total views' ) ),
			array( 'year' => '2022.04', 'text' => $L( 'Z世代向けドラマ『YouTuberのマネージャーをやってみたらわかったこと。』配信開始', 'Began streaming the Gen Z drama “What I Learned as a YouTuber’s Manager”' ) ),
			// Year not confirmed yet (CLAUDE.md) — edit in サイト設定 › 会社概要・沿革.
			array( 'year' => '20XX', 'text' => $L( '米国ニューヨーク・ロサンゼルスにて映像制作に従事', 'Engaged in film production in New York and Los Angeles, USA' ) ),
			array( 'year' => '2026', 'text' => $L( '新作オリジナルシリーズの制作を開始', 'Began production of a new original series' ) ),
		),
		// Works archive.
		'wa_en_title'        => 'WORKS',
		'wa_title'           => $L( '作品', 'Works' ),
		'wa_featured'        => $works['more-than-friends'][ $lang ],
		'wa_cta_label'       => $L( 'NETELLY CREATORS FUND · 常時募集', 'NETELLY CREATORS FUND · OPEN CALL' ),
		'wa_cta_heading'     => $L( '次の作品は、あなたの企画から。', 'Our next work starts with your idea.' ),
		'wa_cta_text'        => $L( '監督・脚本家・プランナーの企画に、Netellyが制作費を出資します。', 'Netelly funds the production of projects by directors, writers and planners.' ),
		'wa_cta_button'      => $L( '企画を応募する →', 'Submit a project →' ),
	);

	$pages = array(
		'company'  => array(
			'page_company' => array(
				'mission_label'   => 'MISSION',
				'mission_heading' => $L( "エンタメで、\n新しい価値を生み出す。", "Creating new value\nthrough entertainment." ),
				'mission_sub'     => 'NEW ENTERTAINMENT, NEW VALUE.',
				'mission_text'    => $L(
					'Netellyは、縦型ショートドラマにいち早く取り組んできたエンターテインメント企業です。ショートドラマからドラマ・映画・コメディまでを自社で企画・制作し、配信やYouTube、SNS、劇場へ届けています。2019年の創業以来、オリジナル作品だけをつくり続けてきました。',
					'Netelly is an entertainment company that was among the first to take on vertical short dramas. We develop and produce everything in-house — from short dramas to series, films and comedy — and deliver it through streaming, YouTube, social media and theaters. Since our founding in 2019, we have made nothing but original work.'
				),
				'vision_label'    => 'VISION',
				'vision_heading'  => $L( '文化を、アップデートする。', 'Updating culture.' ),
				'vision_text'     => $L(
					'エンターテインメントとテクノロジーを融合させ、作品のつくり方と届け方を更新する。東京を拠点に、ニューヨーク・ロサンゼルスでの制作経験を生かして、世界に通用するオリジナル作品をつくります。',
					'We fuse entertainment and technology to update how stories are made and delivered. Based in Tokyo and drawing on production experience in New York and Los Angeles, we create original work that can stand on the world stage.'
				),
				'vision_link'     => nts_link( $L( '代表メッセージを読む →', 'Read the CEO message →' ), $p( 'message' ) ),
				'profile_label'   => 'PROFILE',
				'profile_heading' => $L( '会社概要', 'Company profile' ),
				'history_label'   => 'HISTORY',
				'history_heading' => $L( '沿革', 'History' ),
				'access_label'    => 'ACCESS',
				'access_heading'  => $L( 'アクセス', 'Access' ),
				'access_name'     => $L( '本社', 'Head office' ),
				'access_address'  => $addr,
				'access_map'      => '',
				'access_map_link' => nts_link( 'GOOGLE MAPS →', $map, true ),
			),
		),
		'business' => array(
			'page_business' => array(
				'intro_label'   => 'WHAT WE DO',
				'intro_heading' => $L( "作る・届ける・つなぐ・支える。\n四つの機能を、一つの組織の中に。", "Create, deliver, connect, support.\nFour functions in one organization." ),
				'items'         => array(
					array(
						'num' => 'I', 'en' => 'CREATIVE', 'title' => $L( '制作', 'Production' ),
						'text' => $L( 'スマートフォン向けの縦型ショートドラマから、ドラマ・映画・コメディまで、企画から自社で手がけます。脚本開発、キャスティング、撮影、編集までを一つのチームで行い、作品の方向性を最後まで守ります。', 'From vertical short dramas for smartphones to series, films and comedy, we develop and produce in-house. One team handles everything from script development and casting to shooting and editing, protecting each work’s vision to the very end.' ),
						'note' => $L( '主な作品：『友達以上、恋人未満』ほか', 'Selected works: “More Than Friends, Less Than Lovers” and more' ),
						'link' => nts_link( $L( '作品一覧 →', 'All works →' ), $wurl ), 'is_fund' => 0, 'image' => '',
					),
					array(
						'num' => 'II', 'en' => 'MEDIA', 'title' => $L( '配給・メディア', 'Distribution & Media' ),
						'text' => $L( '完成した作品を、動画配信サービス・公式YouTubeチャンネル・SNS・劇場など、作品に合った場所へ届けます。予告編やショート動画の展開まで含めて設計します。', 'We deliver finished works to the places that suit them — streaming services, our official YouTube channel, social media and theaters — and design the rollout down to trailers and short-form clips.' ),
						'note' => $L( '公式YouTubeチャンネル「Netelly」', 'Official YouTube channel “Netelly”' ),
						'link' => nts_link( $L( 'YouTubeを見る →', 'Watch on YouTube →' ), 'https://www.youtube.com/', true ), 'is_fund' => 0, 'image' => '',
					),
					array(
						'num' => 'III', 'en' => 'COMMUNITY', 'title' => $L( 'コミュニティ', 'Community' ),
						'text' => $L( '監督・脚本家・俳優・制作スタッフが出会い、次の企画が生まれる場をつくります。作品ごとに集まったチームが、次の作品でもまた一緒に動ける関係を育てます。', 'We create a place where directors, writers, actors and crew meet and new projects begin, nurturing teams that come together for one work and keep working together on the next.' ),
						'note' => '',
						'link' => nts_link( $L( 'お問い合わせ →', 'Contact us →' ), $p( 'contact' ) ), 'is_fund' => 0, 'image' => '',
					),
					array(
						'num' => 'IV', 'en' => 'CREATORS FUND', 'title' => $L( 'クリエイターズファンド', 'Creators Fund' ),
						'text' => $L( '「トガった作品を世の中に出したい」という考えのもと、映像クリエイター・脚本家・監督・制作会社の企画に、制作費の全額または一部を出資します。', 'Driven by the wish to “get bold, edgy work out into the world,” we fund all or part of the production costs for projects by filmmakers, screenwriters, directors and production companies.' ),
						'note' => $L( '出資額は作品の予算に応じて決定', 'Investment amounts are set according to each project’s budget' ),
						'link' => nts_link( $L( '募集要項を見る →', 'View guidelines →' ), NTS_FUND_URL, true ), 'is_fund' => 1, 'image' => '',
					),
				),
				'cta_label'     => $L( 'NETELLY CREATORS FUND · 常時募集', 'NETELLY CREATORS FUND · OPEN CALL' ),
				'cta_heading'   => $L( '脚本がなくても、応募できる。', 'No script? You can still apply.' ),
				'cta_text'      => $L( '監督・脚本家・プランナーの「トガった企画」に、Netellyが制作費を出資します。必要なのはアイディアだけ。プロ・アマは問いません。', 'Netelly funds the production of “edgy” projects by directors, writers and planners. All you need is an idea — professionals and amateurs alike are welcome.' ),
				'cta_primary'   => $L( '企画を応募する →', 'Submit a project →' ),
				'cta_secondary' => $L( '募集要項を見る', 'View guidelines' ),
			),
		),
		'careers'  => array(
			'page_careers' => array(
				'cm_label'      => 'MESSAGE',
				'cm_heading'    => $L( "好きな作品を、\n仕事にする。", "Make the stories you love\nyour work." ),
				'cm_text'       => $L( 'Netellyは、企画・制作・配給までを自社で手がける少人数のチームです。一人ひとりが作品の最初から最後まで関わり、自分の名前がクレジットに残る仕事をしています。', 'Netelly is a small team that handles everything from development and production to distribution in-house. Each of us is involved in a work from start to finish — work that leaves our names in the credits.' ),
				'cm_image'      => '',
				'why_label'     => 'WHY NETELLY',
				'why_heading'   => $L( 'Netellyで働く理由', 'Why work at Netelly' ),
				'reasons'       => array(
					array( 'title' => $L( '企画から配給まで', 'From development to distribution' ), 'text' => $L( '一つの作品に、企画・制作・届け方のすべての段階で関われます。', 'You can take part in every stage of a work — development, production and delivery.' ) ),
					array( 'title' => $L( '世界を見据えた制作', 'Made with the world in view' ), 'text' => $L( '東京を拠点に、ニューヨーク・ロサンゼルスでの制作経験を生かした作品づくりを行います。', 'Based in Tokyo, we make work that draws on our production experience in New York and Los Angeles.' ) ),
					array( 'title' => $L( '少人数、大きな裁量', 'Small team, big ownership' ), 'text' => $L( '職種の枠を越えて提案でき、良いアイディアはそのまま企画になります。', 'You can propose ideas beyond your job title, and good ideas become projects as they are.' ) ),
				),
				'pos_label'     => 'OPEN POSITIONS',
				'pos_heading'   => $L( '募集職種', 'Open positions' ),
				'proc_label'    => 'PROCESS',
				'proc_heading'  => $L( '選考の流れ', 'Selection process' ),
				'steps'         => array(
					array( 'title' => $L( 'エントリー', 'Entry' ), 'text' => $L( 'フォームから応募。ポートフォリオや過去作品があれば添付。', 'Apply via the form. Attach a portfolio or past work if you have one.' ) ),
					array( 'title' => $L( '書類選考', 'Document screening' ), 'text' => $L( '担当者が内容を確認し、結果を連絡する。', 'We review your application and let you know the result.' ) ),
					array( 'title' => $L( '面接（2〜3回）', 'Interviews (2–3 rounds)' ), 'text' => $L( '現場メンバー・代表と話し、互いの考えを確かめる。', 'Talk with team members and the CEO to see whether our thinking aligns.' ) ),
					array( 'title' => $L( '内定', 'Offer' ), 'text' => $L( '条件をすり合わせ、入社日を決める。', 'We align on conditions and set your start date.' ) ),
				),
				'entry_label'   => 'ENTRY',
				'entry_heading' => $L( "次の作品の\nクレジットに、あなたの名前を。", "Put your name in\nthe credits of our next work." ),
				'entry_button'  => $L( 'エントリーする →', 'Apply →' ),
			),
		),
		'contact'  => array(
			'page_contact' => array(
				'lead_heading' => $L( "制作・配給・協業の\nご相談はこちらから。", "Production, distribution\nand partnership inquiries." ),
				'lead_text'    => $L( '内容を確認のうえ、担当者よりご連絡します。お急ぎの場合はメールでもお問い合わせいただけます。', 'We will review your message and a member of our team will get back to you. For urgent matters, you can also reach us by email.' ),
				'form'         => '',
			),
		),
		'privacy'  => array(
			'page_privacy' => array(
				'enacted'  => $L( '制定日：2026年10月8日', 'Established: October 8, 2026' ),
				'intro'    => $L( 'Netelly株式会社（以下「当社」）は、個人情報の重要性を認識し、以下の方針に基づき個人情報を適切に取り扱います。', 'Netelly Inc. (the “Company”) recognizes the importance of personal information and handles it appropriately in accordance with the following policy.' ),
				'articles' => array(
					array( 'title' => $L( '個人情報の取得', 'Collection of personal information' ), 'body' => $L( '当社は、お問い合わせ、応募、各種サービスの利用にあたり、氏名、メールアドレス、電話番号その他の個人情報を、適正な手段により取得します。', 'When you contact us, apply or use our services, the Company collects personal information such as your name, email address and phone number by fair and lawful means.' ) ),
					array( 'title' => $L( '利用目的', 'Purpose of use' ), 'body' => $L( '取得した個人情報は、お問い合わせへの回答、Creators Fundおよび採用の選考、作品・サービスに関するご案内、サービスの改善のために利用します。', 'Personal information we collect is used to respond to inquiries, to screen Creators Fund and job applications, to send information about our works and services, and to improve our services.' ) ),
					array( 'title' => $L( '第三者への提供', 'Provision to third parties' ), 'body' => $L( '法令に基づく場合を除き、ご本人の同意なく個人情報を第三者に提供することはありません。', 'Except as required by law, we do not provide personal information to third parties without your consent.' ) ),
					array( 'title' => $L( '安全管理', 'Security' ), 'body' => $L( '個人情報の漏えい、滅失、毀損を防ぐため、必要かつ適切な安全管理措置を講じます。業務を委託する場合は、委託先を適切に監督します。', 'We take necessary and appropriate measures to prevent the leakage, loss or damage of personal information. When we outsource work, we supervise our contractors appropriately.' ) ),
					array( 'title' => $L( '開示・訂正・削除', 'Disclosure, correction and deletion' ), 'body' => $L( 'ご本人から個人情報の開示、訂正、利用停止、削除の求めがあった場合は、本人確認のうえ、法令に従い対応します。', 'If you request disclosure, correction, suspension of use or deletion of your personal information, we will respond in accordance with the law after verifying your identity.' ) ),
					array( 'title' => $L( 'Cookie等の利用', 'Cookies' ), 'body' => $L( '当社サイトでは、利用状況の把握とサービス改善のためにCookie等を使用する場合があります。ブラウザの設定により無効にできます。', 'Our website may use cookies and similar technologies to understand usage and improve our services. You can disable them in your browser settings.' ) ),
					array( 'title' => $L( 'ポリシーの改定', 'Revisions' ), 'body' => $L( '本ポリシーの内容は、法令の改正等に応じて変更することがあります。変更後の内容は当サイトに掲載した時点から効力を生じます。', 'This policy may be revised in response to changes in laws and regulations. Revised content takes effect when it is posted on this website.' ) ),
					array( 'title' => $L( 'お問い合わせ窓口', 'Contact' ), 'body' => $L( "Netelly株式会社 個人情報お問い合わせ窓口\n〒141-0033 東京都品川区西品川1-1-1 住友不動産大崎ガーデンタワー 9F\ninfo@netelly.com", "Personal Information Desk, Netelly Inc.\nSumitomo Fudosan Osaki Garden Tower 9F, 1-1-1 Nishi-Shinagawa, Shinagawa-ku, Tokyo 141-0033\ninfo@netelly.com" ) ),
				),
			),
		),
	);

	return array(
		'settings' => $settings,
		'pages'    => $pages,
	);
}

/**
 * Menus per language: location => [ [ title, url, children[] ], … ].
 */
function nts_menus( string $lang, callable $purl, array $works ): array {
	$ja   = 'ja' === $lang;
	$L    = static fn( $j, $e ) => $ja ? $j : $e;
	$p    = static fn( $key, $hash = '' ) => $purl( $key, $lang, $hash );
	$wurl = home_url( $ja ? '/works/' : '/en/works/' );
	$fund = '#creators-fund';

	return array(
		'primary' => array(
			array( $L( '企業情報', 'Company' ), $p( 'company' ) ),
			array( $L( '事業', 'Business' ), $p( 'business' ) ),
			array( $L( '作品', 'Works' ), $wurl ),
			array( $L( 'クリエイターズファンド', 'Creators Fund' ), $fund ),
			array( $L( 'ニュース', 'News' ), $p( 'news' ) ),
			array( $L( '採用', 'Careers' ), $p( 'careers' ) ),
		),
		'footer'  => array(
			array(
				$L( '企業情報', 'Company' ),
				'#',
				array(
					array( $L( '理念', 'Philosophy' ), $p( 'company', '#mission' ) ),
					array( $L( '会社概要', 'Profile' ), $p( 'company', '#profile' ) ),
					array( $L( '沿革', 'History' ), $p( 'company', '#history' ) ),
					array( $L( '代表メッセージ', 'CEO Message' ), $p( 'message' ) ),
				),
			),
			array(
				$L( '事業', 'Business' ),
				'#',
				array(
					array( $L( '制作', 'Production' ), $p( 'business', '#creative' ) ),
					array( $L( '配給・メディア', 'Distribution & Media' ), $p( 'business', '#media' ) ),
					array( $L( 'コミュニティ', 'Community' ), $p( 'business', '#community' ) ),
					array( $L( 'クリエイターズファンド', 'Creators Fund' ), $fund ),
				),
			),
			array(
				$L( '作品', 'Works' ),
				'#',
				array(
					array( $L( '作品一覧', 'All works' ), $wurl ),
					array( $L( '友達以上、恋人未満', 'More Than Friends, Less Than Lovers' ), get_permalink( $works['more-than-friends'][ $lang ] ) ),
				),
			),
			array(
				$L( 'サポート', 'Support' ),
				'#',
				array(
					array( $L( 'ニュース', 'News' ), $p( 'news' ) ),
					array( $L( '採用', 'Careers' ), $p( 'careers' ) ),
					array( $L( 'お問い合わせ', 'Contact' ), $p( 'contact' ) ),
					array( $L( 'プライバシーポリシー', 'Privacy Policy' ), $p( 'privacy' ) ),
				),
			),
		),
	);
}

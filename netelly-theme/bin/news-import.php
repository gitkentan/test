<?php
/**
 * News articles for ツール › ニュース一括追加 (inc/admin-news-import.php).
 *
 * key    : unique id (also the slug; English gets "-en"). Re-running never duplicates a key.
 * cat    : press | works | fund | info (NTS_CATEGORIES).
 * sample : true = fictional sample from the client draft → defaults to 下書き.
 * skip   : true = defaults to 追加しない (e.g. an article that already exists).
 * note   : shown next to the row in the admin screen.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

return array(
	array(
		'key'  => '2022-03-youtuber-manager-drama',
		'date' => '2022-03-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netellyオリジナルドラマ『YouTuberのマネージャーをやってみたらわかったこと。』を公開',
			'body'  => array(
				'Netellyは、オリジナルドラマシリーズ『YouTuberのマネージャーをやってみたらわかったこと。』を公開しました。',
				'本作は、上京した若い女性が人気YouTuberのマネージャーとして働き始めたことをきっかけに、仕事、共同生活、恋愛、人間関係が複雑に交差していく姿を描いた全6話のドラマシリーズです。',
				'インターネットによって生まれた新しい職業や人間関係を題材に、オンライン上では見えにくいクリエイターの生活と、その周囲で働く人々の日常を描いています。',
				'Netellyは今後も、現代のライフスタイルやカルチャーから生まれる物語を、独自の視点で映像化していきます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Releases Original Drama Series “What I Learned from Becoming a YouTuber’s Manager”',
			'body'  => array(
				'Netelly has released a new original six-episode drama series, “What I Learned from Becoming a YouTuber’s Manager.”',
				'The series follows a young woman who moves to Tokyo and begins working as the manager of a rising online creator. As her professional and private lives increasingly overlap, the story explores work, relationships, shared living, and the realities behind digital fame.',
				'The project reflects Netelly’s continuing interest in stories shaped by contemporary culture, technology, and changing forms of human connection.',
			),
		),
	),
	array(
		'key'  => '2022-06-original-development',
		'date' => '2022-06-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netelly、次世代オリジナル映像企画の開発を開始',
			'body'  => array(
				'Netellyは、これまでのオリジナル作品制作を踏まえ、次世代の映像企画開発を開始しました。',
				'ドラマ、短編映画、ドキュメンタリー、リアリティ、デジタル向けシリーズなど、既存のジャンルや尺にとらわれず、企画ごとに最適なフォーマットを検討します。',
				'YouTubeをはじめとするデジタルプラットフォームだけでなく、映画館、配信サービス、国際共同制作なども視野に入れ、長期的に展開できるオリジナルIPの開発を進めます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Development of a New Generation of Original Projects',
			'body'  => array(
				'Netelly has begun developing a new generation of original moving-image projects building on its previous independent productions.',
				'The development slate will span fiction, documentary, short-form work, episodic storytelling, and emerging formats without being limited by conventional genre or distribution structures.',
				'The company will also explore projects designed for theatrical exhibition, streaming, digital platforms, and future international collaboration.',
			),
		),
	),
	array(
		'key'  => '2022-09-hopeiro',
		'date' => '2022-09-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => '映画『ホペイロの憂鬱』をNetellyチャンネルにて配信',
			'body'  => array(
				'Netellyは、サッカークラブの用具係「ホペイロ」を主人公とした映像作品『ホペイロの憂鬱』をNetellyチャンネルにて公開しました。',
				'本作は、ピッチ上の選手ではなく、チームを裏側から支えるスタッフに焦点を当てた作品です。',
				'デジタル環境での視聴体験を考慮し、複数のエピソードに分けて公開しています。',
				'Netellyは今後も、新作だけでなく既存の映像作品についても、デジタル環境に適した形で再編集・再配信する取り組みを進めていきます。',
			),
		),
		'en'   => array(
			'title' => '“The Melancholy of the Hópeiro” Released on Netelly',
			'body'  => array(
				'Netelly has released “The Melancholy of the Hópeiro,” a film centered on the equipment manager working behind the scenes of a football club.',
				'Rather than focusing on the players on the field, the story explores the people whose work quietly supports the team.',
				'The film has been presented in an episodic format designed for digital audiences as part of Netelly’s broader exploration of new approaches to distribution and archival access.',
			),
		),
	),
	array(
		'key'  => '2022-11-original-research',
		'date' => '2022-11-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netelly、次期オリジナル作品の企画リサーチを開始',
			'body'  => array(
				'Netellyは、今後のオリジナル映像作品に向け、新たな企画リサーチを開始しました。',
				'若者、都市、仕事、人間関係、デジタルコミュニケーションなど、現代を生きる人々を取り巻くテーマを中心に、映画・シリーズ・短編など複数のフォーマットを想定した企画開発を進めます。',
				'単発の作品制作だけではなく、中長期的に継続できるオリジナル企画の基盤づくりを目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Research for Future Original Projects',
			'body'  => array(
				'Netelly has begun early-stage research for a new generation of original moving-image projects.',
				'Development will explore themes surrounding contemporary life, cities, work, youth, relationships, and digital communication across film, episodic, and short-form formats.',
				'The initiative is part of Netelly’s broader effort to build a sustainable pipeline of original projects beyond individual productions.',
			),
		),
	),
	array(
		'key'  => '2023-02-creative-archive',
		'date' => '2023-02-01',
		'cat'  => 'info',
		'note' => '2026.06「デジタルアーカイブ整備を開始」とほぼ同じ内容',
		'ja'   => array(
			'title' => 'Netelly Archive、過去作品の整理を開始',
			'body'  => array(
				'Netellyは、これまでに制作・公開してきた映像作品について、作品情報、スタッフクレジット、スチール、映像素材、企画資料などの整理を開始しました。',
				'単なる制作実績一覧ではなく、Netellyがどのような時代に、どのようなテーマや表現に関心を持ってきたのかを記録する「Creative Archive」として整備していきます。',
				'今後、新たなコーポレートサイトや作品ページを通じて、段階的に公開していく予定です。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Building Its Creative Archive',
			'body'  => array(
				'Netelly has begun organizing project information, credits, stills, production materials, and development documents from its previous work.',
				'Rather than treating past projects simply as a portfolio, the archive is intended to document the evolution of Netelly’s creative interests, methods, and cultural context over time.',
				'Selected materials will gradually become accessible through Netelly’s digital platforms.',
			),
		),
	),
	array(
		'key'    => '2023-04-pale-city',
		'date'   => '2023-04-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、ロンドンを舞台とした短編映画『PALE CITY』を制作',
			'body'  => array(
				'Netellyは、イギリス・ロンドンを舞台とした短編映画『PALE CITY』を制作しました。',
				'本作は、ロンドンで暮らす一人の日本人青年が、帰国前の最後の24時間を街の中で過ごす姿を描いた短編ドラマです。',
				'地下鉄、雨、ホテル、深夜の街、見知らぬ人との短い会話など、日常の断片を積み重ねながら、「都市を離れること」と「そこに残される記憶」をテーマに描いています。',
				'撮影はロンドン市内を中心に行われ、日本と英国の少人数の制作チームによって制作されました。',
				'Netellyは今後も、日本国内だけでなく、異なる都市や文化を背景とした映像制作を通じて、国境を越えて共有できる物語や感情の表現を探っていきます。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Produces Short Film “PALE CITY” in London',
			'body'  => array(
				'Netelly has produced “PALE CITY,” a short film set in London, United Kingdom.',
				'The film follows a young Japanese resident during his final 24 hours in the city before returning home.',
				'Through fragments of everyday life — underground stations, rain, hotel rooms, late-night streets, and brief encounters with strangers — the story explores departure, memory, and the relationship between people and the cities they leave behind.',
				'Filmed across London, the project was produced by a small team of Japanese and UK-based creatives.',
				'Netelly will continue exploring stories shaped by different cities, cultures, and perspectives while expanding its approach to internationally produced moving-image work.',
			),
		),
	),
	array(
		'key'  => '2023-06-creator-network',
		'date' => '2023-06-01',
		'cat'  => 'info',
		'ja'   => array(
			'title' => '映像クリエイターとの制作ネットワーク拡大へ',
			'body'  => array(
				'Netellyは、今後のオリジナル作品および受託制作の幅を広げるため、映像クリエイターとのネットワーク構築を進めています。',
				'対象は監督、プロデューサー、脚本家、撮影監督、照明、録音、編集、VFX、音楽、俳優など、映像制作に関わる幅広い職種です。',
				'固定された制作チームだけに依存せず、企画ごとに最も適した人材が集まる柔軟な制作環境を目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Expands Its Network of Film and Visual Creators',
			'body'  => array(
				'Netelly is expanding its network of filmmakers and creative professionals to support a wider range of original and commissioned projects.',
				'The network spans directors, producers, writers, cinematographers, editors, sound professionals, composers, actors, and other disciplines involved in moving-image production.',
				'The aim is to build a flexible creative ecosystem in which each project can bring together the people best suited to its ideas and ambitions.',
			),
		),
	),
	array(
		'key'    => '2023-09-between-stations',
		'date'   => '2023-09-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、ソウルにて映像プロジェクト『BETWEEN STATIONS』を制作',
			'body'  => array(
				'Netellyは、韓国・ソウルを舞台とした映像プロジェクト『BETWEEN STATIONS』を制作しました。',
				'本作は、地下鉄駅に存在する「待つ時間」に焦点を当てた短編映像作品です。',
				'目的地へ移動する人々ではなく、ホームで電車を待つ人、乗り換え通路で立ち止まる人、終電前の駅に残る人など、都市の中に生まれる短い静止の瞬間を記録しています。',
				'東京とソウルという異なる都市を比較するリサーチから始まった企画で、都市インフラ、人の動き、沈黙、距離感を映像として再構成しました。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Produces Visual Project “BETWEEN STATIONS” in Seoul',
			'body'  => array(
				'Netelly has produced “BETWEEN STATIONS,” a visual project filmed in Seoul, South Korea.',
				'The work focuses on moments of waiting inside the city’s subway system.',
				'Rather than documenting movement itself, the film observes passengers standing on platforms, pausing in transfer corridors, and remaining in stations late at night.',
				'Originally developed through research comparing Tokyo and Seoul, the project explores urban infrastructure, silence, movement, and the subtle distance between people living within large cities.',
			),
		),
	),
	array(
		'key'  => '2023-10-original-ip',
		'date' => '2023-10-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netelly、オリジナルIP開発を強化',
			'body'  => array(
				'Netellyは、これまでの短編・シリーズ制作を基盤に、オリジナルIPの企画開発を強化します。',
				'単発の映像コンテンツではなく、シリーズ化、長編化、海外展開、異なるメディアへの展開など、継続的な成長可能性を持つ企画を中心に開発していきます。',
				'企画段階から脚本、ビジュアル開発、制作方式、配信・公開方法までを一体として考えることで、作品単位ではなく長期的なクリエイティブ資産の形成を目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Expands Its Original IP Development',
			'body'  => array(
				'Netelly is expanding its development of original intellectual property across film, series, and emerging formats.',
				'Rather than focusing solely on standalone productions, the company will prioritize projects with the potential to evolve across seasons, feature-length adaptations, international markets, and multiple forms of distribution.',
				'Development will integrate story, visual identity, production strategy, and audience experience from the earliest stages.',
			),
		),
	),
	array(
		'key'  => '2023-12-looking-back',
		'date' => '2023-12-01',
		'cat'  => 'info',
		'ja'   => array(
			'title' => '2023年のクリエイティブ活動を振り返って',
			'body'  => array(
				'2023年、Netellyはオリジナル映像の企画開発、クリエイターネットワークの拡大、過去作品のアーカイブ整備など、今後の活動を支える基盤づくりを進めました。',
				'作品を制作するだけではなく、作品が生まれる前の企画開発から、制作後のアーカイブ、そして次のプロジェクトにつながるクリエイターとの関係までを一つの活動として捉えています。',
				'今後も、映像を中心とした新しい制作体制と文化的な活動領域の拡大を目指します。',
			),
		),
		'en'   => array(
			'title' => 'Looking Back on Netelly in 2023',
			'body'  => array(
				'Throughout 2023, Netelly continued building the foundations for future original development, creative collaboration, and long-term preservation of its work.',
				'The company increasingly views filmmaking not simply as the act of producing individual projects, but as a wider process spanning development, collaboration, production, distribution, and archive.',
				'Netelly will continue expanding this creative infrastructure in the years ahead.',
			),
		),
	),
	array(
		'key'  => '2024-02-studio-framework',
		'date' => '2024-02-01',
		'cat'  => 'info',
		'ja'   => array(
			'title' => 'Netelly Studio、制作体制を再編',
			'body'  => array(
				'Netellyは、オリジナル作品開発と外部クライアントからの制作案件を並行して進めるため、制作体制を再編しました。',
				'プロジェクトごとに監督、プロデューサー、撮影、編集、デザインなどのチームを柔軟に編成することで、作品の規模や目的に応じた制作体制を構築します。',
				'国内案件だけでなく、今後の国際共同制作や海外ブランドとの協業にも対応できる制作基盤を目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Studio Introduces a New Production Framework',
			'body'  => array(
				'Netelly has restructured its production model to support both original development and commissioned creative work.',
				'Rather than relying on a fixed production structure, Netelly Studio will build project-specific teams across direction, production, cinematography, editing, design, and other creative disciplines.',
				'The framework is also intended to support future cross-border collaborations and international productions.',
			),
		),
	),
	array(
		'key'  => '2024-04-international-development',
		'date' => '2024-04-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => '海外クリエイターとの共同開発プロジェクトを開始',
			'body'  => array(
				'Netellyは、海外の映像クリエイターとの共同開発による新たな映像企画を開始しました。',
				'本プロジェクトでは、日本側と海外側のクリエイターがそれぞれ異なる文化的背景や制作手法を持ち寄り、一つの作品を共同で開発することを目的としています。',
				'現在は企画・リサーチ段階で、作品形式、撮影地域、制作スケジュールについて協議を進めています。',
				'Netellyは今後、東京を起点に、海外の制作会社やクリエイターとの共同開発・共同制作を段階的に拡大していきます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins an International Creative Development Project',
			'body'  => array(
				'Netelly has begun developing a new moving-image project in collaboration with international creative partners.',
				'The project brings together creators from Japan and abroad, combining different cultural perspectives and approaches to filmmaking within a single development process.',
				'The project is currently in its research and early development phase, with format, locations, and production structure under consideration.',
				'Netelly plans to gradually expand its international development and co-production activities from its base in Tokyo.',
			),
		),
	),
	array(
		'key'    => '2024-06-parallel',
		'date'   => '2024-06-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、ベルリンの制作チームとの共同短編『PARALLEL』を完成',
			'body'  => array(
				'Netellyは、ドイツ・ベルリンの制作チームと共同で短編映像作品『PARALLEL』を完成しました。',
				'『PARALLEL』は、東京とベルリンで暮らす二人の人物の日常を、それぞれ同じ時間帯、同じ構図、似た行動によって並行して描く映像作品です。',
				'異なる言語、文化、建築、生活リズムを持つ二つの都市において、人間の日常がどこまで異なり、どこまで似ているのかを視覚的に探っています。',
				'東京パートをNetelly、ベルリンパートを現地制作チームが担当し、企画、編集、サウンドデザインを共同で進めました。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Completes “PARALLEL” with Berlin-Based Production Team',
			'body'  => array(
				'Netelly has completed “PARALLEL,” a short-form project developed in collaboration with a Berlin-based production team.',
				'The film follows two individuals living separately in Tokyo and Berlin, presenting their daily lives through corresponding times, compositions, and actions.',
				'By placing two culturally and architecturally distinct cities alongside one another, the project explores where everyday life diverges and where it unexpectedly becomes universal.',
				'Netelly led production in Tokyo, while the Berlin sequences were produced locally. Development, editing, and sound design were completed collaboratively across both teams.',
			),
		),
	),
	array(
		'key'  => '2024-07-filmmaking-community',
		'date' => '2024-07-01',
		'cat'  => 'info',
		'note' => '2026.07「Netelly Film Community、運営準備を開始」とほぼ同じ内容',
		'ja'   => array(
			'title' => '映像制作コミュニティ構想の開発を開始',
			'body'  => array(
				'Netellyは、映像制作に携わる人々が職種や所属を越えてつながるためのコミュニティ構想を開始しました。',
				'監督、プロデューサー、撮影、編集、脚本、俳優など、それぞれ異なる立場で映像に関わる人々が、新しい関係や共同制作のきっかけを生み出せる環境を目指します。',
				'オンライン上の交流だけではなく、将来的には上映、制作企画、トーク、ワークショップなどへの展開も検討します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Developing a Filmmaking Community',
			'body'  => array(
				'Netelly has begun developing a community concept designed to connect people working across different disciplines of moving-image production.',
				'The initiative aims to bring together directors, producers, cinematographers, editors, writers, actors, and other creative professionals beyond traditional boundaries of role or affiliation.',
				'Future possibilities include screenings, collaborative projects, talks, workshops, and other forms of creative exchange.',
			),
		),
	),
	array(
		'key'    => '2024-10-nocturne',
		'date'   => '2024-10-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、パリでファッションフィルム『NOCTURNE』を制作',
			'body'  => array(
				'Netellyは、フランス・パリにてファッションフィルム『NOCTURNE』を制作しました。',
				'本作は、夜のパリを舞台に、衣服、身体、都市の光を一つの映像として構成した短編作品です。',
				'従来の広告映像のように商品説明を中心とするのではなく、人物の動き、光、建築、音楽を通じて、ブランドやプロダクトが持つ世界観を映画的に表現することを試みました。',
				'撮影はパリ市内の複数ロケーションで実施し、日本とフランスのクリエイターによる混成チームで制作しました。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Produces Fashion Film “NOCTURNE” in Paris',
			'body'  => array(
				'Netelly has produced “NOCTURNE,” a fashion film shot in Paris, France.',
				'Set across the city at night, the film brings together clothing, movement, architecture, light, and sound within a cinematic visual language.',
				'Rather than functioning as a conventional product-driven commercial, the project explores how the identity of a brand or object can be expressed through atmosphere, rhythm, and image.',
				'Filmed across multiple Paris locations, the production brought together Japanese and French creatives.',
			),
		),
	),
	array(
		'key'  => '2024-11-originals-slate',
		'date' => '2024-11-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netelly Originals、新規企画スレートの開発を開始',
			'body'  => array(
				'Netelly Originalsは、複数のオリジナル映画・シリーズ企画について初期開発を開始しました。',
				'今後数年間にわたり継続的に作品を生み出せる体制を構築するため、単一作品ごとの開発ではなく、複数の企画を並行して育てる「スレート型」の開発体制を進めます。',
				'企画ごとに脚本、監督、プロデューサー、制作規模、配信・公開形式などを検討し、最適なタイミングで制作へ移行することを目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Originals Begins Development of a New Project Slate',
			'body'  => array(
				'Netelly Originals has begun early-stage development on multiple original film and series concepts.',
				'Rather than developing individual projects in isolation, Netelly is building a slate-based approach designed to support a continuous pipeline of future work.',
				'Each project will be developed independently across story, creative team, production scale, and potential distribution strategy before moving toward production.',
			),
		),
	),
	array(
		'key'  => '2025-02-creators-fund',
		'date' => '2025-02-01',
		'cat'  => 'fund',
		'note' => '既存ニュース（2021.08「Creators Fundを設立」）と時系列が矛盾',
		'ja'   => array(
			'title' => 'Creators Fund構想の開発を開始',
			'body'  => array(
				'Netellyは、新しい才能と映像作品の誕生を支える「Creators Fund」の構想開発を開始しました。',
				'Creators Fundでは、若手監督、脚本家、映像クリエイターなどが持つ企画に対し、開発、制作、メンタリング、ネットワーク形成、そして必要に応じた資金面での支援を行う仕組みを検討します。',
				'単なるコンテストや助成制度ではなく、企画を実際の作品として世に出すところまで伴走できる環境を目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Development of Creators Fund',
			'body'  => array(
				'Netelly has begun developing Creators Fund, an initiative intended to support emerging filmmakers, writers, and visual creators.',
				'The program is being designed around development support, production, mentorship, creative networks, and, where appropriate, selective financial backing.',
				'Rather than functioning simply as a competition or grant program, Creators Fund aims to help promising ideas move from early concept to completed work.',
			),
		),
	),
	array(
		'key'    => '2025-03-northline',
		'date'   => '2025-03-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、青森・コペンハーゲンを舞台とした長編映画『NORTHLINE』を制作',
			'body'  => array(
				'Netellyは、青森とデンマーク・コペンハーゲンを舞台とした長編映画『NORTHLINE』を制作しました。',
				'本作は、冬の青森を訪れたデンマーク人写真家と、家族のもとに残るか東京へ戻るか迷う日本人青年との出会いを描いたドラマです。',
				'雪に覆われた北日本の風景と、北欧の都市生活を対比させながら、土地、家族、移動、帰属意識をテーマに物語を構成しています。',
				'日本とデンマークの制作チームが参加し、両国で撮影を実施しました。',
				'Netellyは今後も、日本を起点としながら、異なる文化や土地を横断するオリジナル作品の開発・制作を進めていきます。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Produces Feature Film “NORTHLINE” Across Aomori and Copenhagen',
			'body'  => array(
				'Netelly has produced “NORTHLINE,” a feature film set across Aomori, Japan, and Copenhagen, Denmark.',
				'The drama follows a Danish photographer visiting northern Japan during winter and a young Japanese man caught between remaining with his family and returning to Tokyo.',
				'Through the contrast between snow-covered northern Japan and urban Scandinavia, the film explores place, family, movement, and the question of where a person belongs.',
				'Production took place in both Japan and Denmark with creative teams from each country.',
				'“NORTHLINE” represents Netelly’s continuing effort to develop original stories from Japan that move across languages, cultures, and borders.',
			),
		),
	),
	array(
		'key'  => '2025-05-series-concepts',
		'date' => '2025-05-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netelly Originals、新たなオリジナルシリーズ企画の開発を開始',
			'body'  => array(
				'Netelly Originalsは、複数の新規シリーズ企画について初期開発を開始しました。',
				'映画や短編だけではなく、複数話を通じて人物や世界観を長期的に描くシリーズ形式を今後の主要な開発領域の一つとして位置付けます。',
				'現在、企画リサーチ、シリーズ構成、脚本開発、ビジュアルコンセプトの検討を進めています。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Originals Begins Development of New Series Concepts',
			'body'  => array(
				'Netelly Originals has entered early-stage development on several new original series concepts.',
				'In addition to feature films and short-form projects, the company is placing greater emphasis on episodic storytelling capable of developing characters and worlds over a longer period.',
				'Research, series structure, script development, and early visual concepts are currently underway.',
			),
		),
	),
	array(
		'key'    => '2025-07-almost-home',
		'date'   => '2025-07-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、米国制作チームと共同で新シリーズ『ALMOST HOME』を制作',
			'body'  => array(
				'Netellyは、米国の独立系制作チームとの共同制作によるドラマシリーズ『ALMOST HOME』を制作しました。',
				'本作は、ロサンゼルスに暮らす3人の若者を中心に、仕事、友情、家族との距離、そして「どこを自分の居場所と呼ぶのか」を描く全6話のシリーズです。',
				'Netellyは企画開発および一部演出、東京側でのポストプロダクションを担当し、米国チームが現地制作を担いました。',
				'異なる国に制作機能を分散させながら、一つの作品を共同で完成させる制作方式を採用しています。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Produces New Series “ALMOST HOME” with U.S. Production Partner',
			'body'  => array(
				'Netelly has produced “ALMOST HOME,” a six-episode drama series developed with an independent U.S.-based production team.',
				'Set in Los Angeles, the series follows three young people navigating work, friendship, family, and the emotional question of where they truly consider home.',
				'Netelly participated in development and selected directing responsibilities while also overseeing elements of post-production from Tokyo. Principal production was carried out by the U.S. team.',
				'The project was structured as a distributed international production, with creative and technical responsibilities shared across both countries.',
			),
		),
	),
	array(
		'key'  => '2025-09-english-communications',
		'date' => '2025-09-01',
		'cat'  => 'info',
		'ja'   => array(
			'title' => '海外展開を見据えた英語コミュニケーション体制を強化',
			'body'  => array(
				'Netellyは、海外企業、制作会社、スタジオ、クリエイターとの協業拡大を見据え、英語による情報発信および制作対応の強化を進めています。',
				'コーポレートサイト、企画資料、作品情報などの英語対応に加え、海外案件における制作コミュニケーションの標準化を進めます。',
				'東京を拠点とする日本のクリエイティブカンパニーとして、国内外を横断する制作機会を増やしていくことを目指します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Strengthens Its English-Language Communications',
			'body'  => array(
				'Netelly is strengthening its English-language communications and production capabilities to support future international collaboration.',
				'This includes English-language corporate materials, project information, development documents, and more standardized communication across international productions.',
				'As a Tokyo-based creative company, Netelly aims to create more opportunities for projects that move between Japan and international markets.',
			),
		),
	),
	array(
		'key'    => '2025-11-taipei-brand-film',
		'date'   => '2025-11-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly、台北を舞台としたブランドフィルムを制作',
			'body'  => array(
				'Netellyは、台湾・台北を舞台としたブランドフィルムを制作しました。',
				'本作では、都市の日常、建築、夜市、交通、人の動きなどを通じて、商品そのものを直接的に説明するのではなく、そのブランドが存在する世界観を映像として表現しています。',
				'企画・ディレクションをNetellyが担当し、現地プロダクションと連携して撮影を実施しました。',
				'海外でのブランド案件においても、日本国内と同様に単なる撮影代行ではなく、企画、演出、ローカルプロダクションとの連携、ポストプロダクションまで一貫して対応する制作体制を構築しています。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Produces Brand Film in Taipei',
			'body'  => array(
				'Netelly has produced a new brand film shot in Taipei, Taiwan.',
				'Rather than directly explaining a product, the project uses the city’s architecture, markets, movement, nightlife, and everyday rhythms to build a cinematic world around the brand.',
				'Netelly led concept development and direction, working closely with a local production partner throughout filming in Taipei.',
				'For international brand projects, Netelly aims to provide more than local production support, integrating concept, direction, international coordination, and post-production within a single creative process.',
			),
		),
	),
	array(
		'key'  => '2025-12-brand-development',
		'date' => '2025-12-01',
		'cat'  => 'press',
		'ja'   => array(
			'title' => 'Netelly、次の成長フェーズに向けたブランド再構築を開始',
			'body'  => array(
				'Netellyは、今後の事業拡大を見据え、企業ブランド、ウェブサイト、ビジュアルアイデンティティの再構築に着手しました。',
				'映像制作だけではなく、オリジナル作品開発、監督活動、Creators Fund、コミュニティなどへ活動領域が広がる中、それらを一つのNetellyブランドとして再定義することを目指します。',
				'単なる制作会社ではなく、作品、人、文化を生み出すクリエイティブカンパニーとしての位置付けを明確にしていきます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins a New Phase of Brand Development',
			'body'  => array(
				'Netelly has begun rethinking its corporate brand, digital presence, and visual identity in preparation for the company’s next stage of growth.',
				'As its activities expand across original development, production, directing, creator support, and community, Netelly is working to bring these areas together under a clearer and more unified identity.',
				'The objective is to position Netelly not simply as a production company, but as a creative organization working across moving images, people, and culture.',
			),
		),
	),
	array(
		'key'    => '2026-02-nocturne-17',
		'date'   => '2026-02-01',
		'cat'    => 'works',
		'sample' => true,
		'ja'     => array(
			'title' => 'Netelly Originals、東京・ニューヨーク共同制作作品『NOCTURNE 17』を公開',
			'body'  => array(
				'Netelly Originalsは、東京とニューヨークで制作した実験短編『NOCTURNE 17』を公開しました。',
				'本作では、東京で収録された深夜の都市音と、ニューヨークで撮影された映像を組み合わせています。',
				'映像と音が同じ場所で記録されるという通常の映画制作の前提をあえて外し、異なる都市から生まれた素材が、感情やリズムによって一つの世界として成立する可能性を探りました。',
				'日本と米国の少人数のクリエイティブチームによって制作され、編集および最終仕上げは東京で行われました。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Originals Releases Tokyo–New York Collaboration “NOCTURNE 17”',
			'body'  => array(
				'Netelly Originals has released “NOCTURNE 17,” an experimental short created between Tokyo and New York.',
				'The project combines late-night field recordings captured in Tokyo with images filmed separately in New York.',
				'By deliberately separating sound from the place in which the images were recorded, the work explores whether two distant cities can become a single cinematic space through emotion, rhythm, and editing.',
				'The project was produced by small creative teams in Japan and the United States, with final post-production completed in Tokyo.',
			),
		),
	),
	array(
		'key'  => '2026-03-netelly-journal',
		'date' => '2026-03-01',
		'cat'  => 'info',
		'ja'   => array(
			'title' => 'Netelly Journalを準備中',
			'body'  => array(
				'Netellyは、新たな編集プロジェクト「Netelly Journal」の準備を開始しました。',
				'映画や映像作品そのものだけではなく、それを生み出す人々、都市、技術、建築、ファッション、音楽、社会など、映像文化を取り巻く幅広いテーマを扱います。',
				'インタビュー、エッセイ、制作ノート、リサーチ、作品紹介などを通じ、Netellyが考える映像文化を継続的に記録していきます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Journal Is in Development',
			'body'  => array(
				'Netelly is developing Netelly Journal, a new editorial platform dedicated to the wider culture surrounding moving images.',
				'Its scope will extend beyond films themselves to the people, cities, technology, architecture, music, fashion, and ideas that shape contemporary visual culture.',
				'The platform is expected to include interviews, essays, production notes, research, and selected cultural coverage.',
			),
		),
	),
	array(
		'key'  => '2026-04-feature-research',
		'date' => '2026-04-01',
		'cat'  => 'works',
		'ja'   => array(
			'title' => '新たな長編映像企画のリサーチを開始',
			'body'  => array(
				'Netelly Originalsは、新たな長編映像企画に向けたリサーチを開始しました。',
				'都市、若者、移動、記憶、文化的距離などをテーマに、日本と海外の双方を舞台とするストーリーを検討しています。',
				'現在はロケーション、人物設定、リサーチ対象、作品形式などを検討する初期段階です。',
				'作品の詳細については、企画開発の進行に合わせて今後発表します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Research for a New Feature-Length Project',
			'body'  => array(
				'Netelly Originals has begun early-stage research for a new feature-length moving-image project.',
				'The project is exploring themes of cities, youth, movement, memory, and cultural distance across Japan and an international setting.',
				'Development currently remains at the research stage, including locations, characters, narrative structure, and production possibilities.',
				'Further details will be announced as development progresses.',
			),
		),
	),
	array(
		'key'    => '2026-05-international-production',
		'date'   => '2026-05-01',
		'cat'    => 'press',
		'sample' => true,
		'note'   => '架空サンプルの制作実績（ロンドン・ソウル等）を根拠にしている',
		'ja'     => array(
			'title' => 'Netelly、海外制作部門を再編。東京を起点とした国際制作体制を強化',
			'body'  => array(
				'Netellyは、海外での映像制作案件の増加に伴い、国際制作体制を再編しました。',
				'ロンドン、ロサンゼルス、ソウル、ベルリン、パリ、コペンハーゲン、台北などでの制作経験をもとに、海外ロケーションにおける企画、現地プロダクション選定、キャスティング、撮影、ポストプロダクションまでを一貫して管理できる体制を整備します。',
				'また、日本で撮影を希望する海外企業・スタジオに対しても、東京を中心とした制作コーディネーション、ロケーションリサーチ、クリエイティブディレクションに対応します。',
				'Netellyは、単に「海外でも撮影できる制作会社」ではなく、日本と世界のクリエイターを一つの作品のために接続するプロダクションモデルを構築していきます。',
			),
		),
		'en'     => array(
			'title' => 'Netelly Expands International Production Operations from Tokyo',
			'body'  => array(
				'Netelly has expanded and reorganized its international production operations in response to a growing range of cross-border projects.',
				'Drawing on production experience across cities including London, Los Angeles, Seoul, Berlin, Paris, Copenhagen, and Taipei, the company is building a more integrated structure for concept development, local production partnerships, casting, principal photography, and post-production.',
				'Netelly is also expanding production support for international companies and studios seeking to create work in Japan, including location research, Tokyo-based production coordination, and creative direction.',
				'The objective is not simply to operate as a production company capable of filming overseas, but to build a model that connects Japanese and international creative talent around individual projects.',
			),
		),
	),
	array(
		'key'  => '2026-05-fair-collaboration',
		'date' => '2026-05-15',
		'cat'  => 'press',
		'ja'   => array(
			'title' => 'クリエイターとの取引に関する基本方針を策定',
			'body'  => array(
				'Netellyは、映像制作に携わるクリエイターとの継続的かつ公正な関係構築を目的に、取引に関する基本方針を策定しました。',
				'契約条件、権利関係、クレジット表記、制作条件などについて、可能な限り透明性の高い制作環境を目指します。',
				'作品の完成だけではなく、その制作過程に参加する人々が適切に評価され、長期的に活動できる環境をつくることも、Netellyのクリエイティブ活動の一部と考えています。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Establishes Principles for Fair Collaboration with Creators',
			'body'  => array(
				'Netelly has established a set of basic principles for building sustainable and fair relationships with creative collaborators.',
				'The principles address areas including contractual clarity, rights management, crediting, and transparent production practices.',
				'Netelly believes that creative work should be evaluated not only by the finished project, but also by the quality and fairness of the process through which that work is created.',
			),
		),
	),
	array(
		'key'  => '2026-06-digital-archive',
		'date' => '2026-06-01',
		'cat'  => 'info',
		'note' => '2023.02「過去作品の整理を開始」とほぼ同じ内容',
		'ja'   => array(
			'title' => 'Netelly Archive、過去作品のデジタルアーカイブ整備を開始',
			'body'  => array(
				'Netellyは、これまでに制作・公開してきた映像作品について、作品情報、ビジュアル、スタッフクレジット、関連資料などを整理するデジタルアーカイブの整備を開始しました。',
				'過去作品を単なる制作実績としてではなく、Netellyがこれまでどのような作品を生み出し、どのように変化してきたのかを残す創作活動の記録として保存していきます。',
				'今後、公式サイトなどを通じて一部を段階的に公開する予定です。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Development of Its Digital Archive',
			'body'  => array(
				'Netelly has begun organizing project information, visual materials, credits, and documentation as part of an ongoing effort to preserve its creative history.',
				'Rather than treating previous projects solely as portfolio pieces, the archive will document how Netelly’s creative interests, collaborators, and methods have evolved over time.',
				'Selected archive materials are expected to be gradually made available through Netelly’s digital platforms.',
			),
		),
	),
	array(
		'key'  => '2026-06-sustainability',
		'date' => '2026-06-15',
		'cat'  => 'press',
		'ja'   => array(
			'title' => 'Netelly、映像制作におけるサステナビリティ方針を策定',
			'body'  => array(
				'Netellyは、映像制作現場における環境負荷を低減するため、サステナビリティに関する基本方針を策定しました。',
				'制作資料のデジタル化、不要な印刷物の削減、ロケーション移動の効率化、使い捨て資材の見直しなど、制作規模を問わず実行可能な項目から段階的に取り組みます。',
				'大規模なスローガンを掲げるのではなく、制作現場の日常的な意思決定を改善することを重視します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Establishes a Sustainability Policy for Production',
			'body'  => array(
				'Netelly has established a sustainability policy aimed at reducing the environmental impact of its production activities.',
				'Initial measures include digitizing production documents, reducing unnecessary printing, improving location and transport planning, and reconsidering the use of disposable production materials.',
				'Rather than treating sustainability as a promotional statement, Netelly intends to focus on practical changes within everyday production decisions.',
			),
		),
	),
	array(
		'key'  => '2026-07-film-community',
		'date' => '2026-07-01',
		'cat'  => 'info',
		'note' => '2024.07「映像制作コミュニティ構想の開発を開始」とほぼ同じ内容',
		'ja'   => array(
			'title' => 'Netelly Film Community、運営準備を開始',
			'body'  => array(
				'Netellyは、映像制作に携わるクリエイター同士が、職種や所属を越えてつながるためのコミュニティ構想を進めています。',
				'監督、プロデューサー、撮影、編集、脚本、俳優など、それぞれ異なる領域で活動する人々が、新しい作品や関係性を生み出せる場を目指します。',
				'単なるオンラインコミュニティではなく、制作、上映、トーク、企画開発など、実際の創作活動につながるネットワークとしての運営を検討しています。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Begins Development of a New Film Community',
			'body'  => array(
				'Netelly has begun developing a community initiative connecting people working across film and moving images beyond traditional professional boundaries.',
				'The community is intended to bring together directors, producers, cinematographers, editors, writers, actors, and other creative professionals.',
				'Rather than existing solely as an online network, the initiative is being designed around real creative activity, including production, screenings, discussion, and project development.',
			),
		),
	),
	array(
		'key'  => '2026-08-creators-fund-program',
		'date' => '2026-08-01',
		'cat'  => 'fund',
		'ja'   => array(
			'title' => 'Creators Fund、次世代映像クリエイター支援プログラムを開発',
			'body'  => array(
				'NetellyはCreators Fundを通じ、次世代の監督、脚本家、プロデューサー、映像クリエイターを対象とした支援制度の開発を進めています。',
				'単に資金を提供するだけではなく、企画開発、脚本、制作体制構築、クリエイターとの接続、作品完成後の発表方法まで含めた包括的なサポートを目指します。',
				'才能はあるものの、最初の作品を実現する機会やネットワークを持たないクリエイターに対し、Netellyが作品を生み出すための基盤の一部となることを目指します。',
			),
		),
		'en'   => array(
			'title' => 'Creators Fund Develops Program for Emerging Filmmakers',
			'body'  => array(
				'Through Creators Fund, Netelly is developing a support framework for emerging directors, writers, producers, and moving-image creators.',
				'The initiative is intended to extend beyond financial support, potentially including development, script work, production planning, mentorship, creative introductions, and strategies for presenting completed work.',
				'Creators Fund aims to help creators move from promising ideas toward realized projects.',
			),
		),
	),
	array(
		'key'    => '2026-08-the-quiet-side',
		'date'   => '2026-08-15',
		'cat'    => 'fund',
		'sample' => true,
		'ja'     => array(
			'title' => 'Creators Fund初の国際共同制作作品『THE QUIET SIDE』が完成',
			'body'  => array(
				'NetellyのCreators Fundは、支援作品『THE QUIET SIDE』の制作を完了しました。',
				'本作は、東京を拠点とする若手監督と、オランダの若手撮影監督によって制作された短編作品です。',
				'地方都市に暮らす姉妹の一日を通じて、家族の間で言葉にされない感情と、土地に残ることへの葛藤を描いています。',
				'Creators Fundは、企画開発、プリプロダクション、制作費の一部、海外クリエイターとのマッチング、完成後の作品展開について支援しました。',
			),
		),
		'en'     => array(
			'title' => 'Creators Fund Completes First International Co-Production “THE QUIET SIDE”',
			'body'  => array(
				'Netelly’s Creators Fund has completed production on “THE QUIET SIDE,” its first internationally collaborative supported project.',
				'The short film was created by an emerging Tokyo-based director working with a young cinematographer from the Netherlands.',
				'Through a single day in the lives of two sisters living in a regional Japanese city, the film explores unspoken family relationships and the complicated decision of whether to remain in the place where one grew up.',
				'Creators Fund supported project development, pre-production, part of the production budget, international creative matching, and planning for the film’s post-completion strategy.',
			),
		),
	),
	array(
		'key'  => '2026-09-ai-guidelines',
		'date' => '2026-09-01',
		'cat'  => 'press',
		'ja'   => array(
			'title' => 'Netelly、映像制作におけるAI活用方針を策定',
			'body'  => array(
				'Netellyは、企画開発、リサーチ、制作管理、翻訳、情報整理などにおけるAIの利用に関する基本方針を策定しました。',
				'AIは、人間のクリエイターを置き換えるものではなく、制作過程における反復的な作業や情報処理を効率化し、より多くの時間を創造的判断へ振り向けるためのツールとして位置付けます。',
				'著作権、クリエイターの権利、機密情報、作品の真正性などに配慮しながら、適切な活用方法を継続的に検討していきます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Establishes Guidelines for AI in Creative Production',
			'body'  => array(
				'Netelly has established internal guidelines governing the use of artificial intelligence across development, research, production management, translation, and information workflows.',
				'AI is positioned not as a substitute for filmmakers and creators, but as infrastructure capable of reducing repetitive operational work and creating more time for creative decision-making.',
				'Netelly will continue evaluating its use with particular attention to copyright, creator rights, confidentiality, and the integrity of creative work.',
			),
		),
	),
	array(
		'key'  => '2026-09-new-original-series',
		'date' => '2026-09-15',
		'cat'  => 'works',
		'ja'   => array(
			'title' => 'Netelly Originals、次期オリジナルシリーズの企画開発を開始',
			'body'  => array(
				'Netelly Originalsは、新たなオリジナルシリーズの企画開発を開始しました。',
				'現在、複数の企画について初期リサーチ、シリーズ構成、脚本開発、クリエイターとの協議を進めています。',
				'映画、配信、デジタルプラットフォームなど公開形態を最初から限定せず、作品ごとに最適なフォーマットと届け方を検討します。',
				'詳細については、企画開発の進行に合わせて今後発表します。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Originals Begins Development of a New Original Series',
			'body'  => array(
				'Netelly Originals has begun development of a new original series, with multiple concepts currently progressing through research, series structure, script development, and creative discussions.',
				'Rather than defining a distribution format from the outset, each project will be developed around the form and audience experience most appropriate to its story.',
				'Further details will be announced as development progresses.',
			),
		),
	),
	array(
		'key'  => '2026-10-corporate-identity',
		'date' => '2026-10-01',
		'cat'  => 'press',
		'skip' => true,
		'note' => '同じタイトルの記事（2025.11.28）がすでにあるため、初期値は「追加しない」',
		'ja'   => array(
			'title' => 'Netelly、コーポレートアイデンティティを刷新',
			'body'  => array(
				'Netellyは、今後の事業展開を見据え、コーポレートアイデンティティを再構築しました。',
				'これまで個別に存在していた、オリジナルシリーズ・映画の企画開発、国内外での受託制作、代表による監督活動、Creators Fund、映像制作コミュニティを、一つのNetellyブランドの下で再定義します。',
				'Netellyを単なる映像制作会社としてではなく、作品をつくり、人をつなぎ、新しい才能や企画を支え、映像を取り巻く文化そのものに関わるクリエイティブカンパニーへ発展させていきます。',
			),
		),
		'en'   => array(
			'title' => 'Netelly Unveils a Renewed Corporate Identity',
			'body'  => array(
				'Netelly has renewed its corporate identity as part of the company’s next stage of development.',
				'The new structure brings together original film and series development, commissioned production in Japan and internationally, the founder’s directing practice, Creators Fund, and Netelly’s filmmaking community under a single brand.',
				'Rather than defining itself solely as a production company, Netelly is building an organization that develops work, makes work, connects creators, supports new talent, and contributes to the culture surrounding moving images.',
			),
		),
	),
);

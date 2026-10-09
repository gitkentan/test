<?php
/**
 * ツール › ニュース一括追加: creates the articles in bin/news-import.php as linked
 * Japanese / English posts (Polylang). Each article is created once; re-running skips it.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function () {
		add_management_page( 'ニュース一括追加', 'ニュース一括追加', 'manage_options', 'netelly-news-import', 'netelly_news_import_page' );
	}
);

/**
 * Posts already created for an article key, by language.
 *
 * @param string $key Article key.
 * @return int[] lang => post ID.
 */
function netelly_news_import_existing( string $key ): array {
	$ids = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'any',
			'posts_per_page'   => 2,
			'fields'           => 'ids',
			'lang'             => '',
			'suppress_filters' => false,
			'meta_key'         => '_netelly_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	$out = array();
	foreach ( $ids as $id ) {
		$lang         = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $id ) : 'ja';
		$out[ $lang ] = (int) $id;
	}
	return $out;
}

/**
 * Create one article in both languages.
 *
 * @param array  $a      Article (see bin/news-import.php).
 * @param string $status publish|draft.
 * @return string Result line.
 */
function netelly_news_import_one( array $a, string $status ): string {
	$done = netelly_news_import_existing( $a['key'] );
	$tr   = $done;
	foreach ( array( 'ja', 'en' ) as $lang ) {
		if ( isset( $done[ $lang ] ) || empty( $a[ $lang ]['title'] ) ) {
			continue;
		}
		$body = implode(
			"\n\n",
			array_map(
				static fn( $p ) => "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->",
				$a[ $lang ]['body']
			)
		);
		$cat  = get_category_by_slug( 'ja' === $lang ? $a['cat'] : $a['cat'] . '-en' );
		$id   = wp_insert_post(
			wp_slash(
				array(
					'post_type'     => 'post',
					'post_status'   => $status,
					'post_title'    => $a[ $lang ]['title'],
					'post_content'  => $body,
					'post_name'     => 'ja' === $lang ? $a['key'] : $a['key'] . '-en',
					'post_date'     => $a['date'] . ' 10:00:00',
					'post_category' => $cat ? array( (int) $cat->term_id ) : array(),
					'comment_status' => 'closed',
					'ping_status'   => 'closed',
					'meta_input'    => array( '_netelly_import_key' => $a['key'] ),
				)
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return 'エラー：' . $a['ja']['title'] . '（' . $id->get_error_message() . '）';
		}
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $id, $lang );
		}
		$tr[ $lang ] = (int) $id;
	}
	if ( function_exists( 'pll_save_post_translations' ) && count( $tr ) > 1 ) {
		pll_save_post_translations( $tr );
	}
	if ( count( $done ) === 2 ) {
		return '追加済みのためスキップ：' . $a['ja']['title'];
	}
	return ( 'draft' === $status ? '下書きで追加：' : '公開で追加：' ) . $a['ja']['title'];
}

/**
 * Admin screen.
 */
function netelly_news_import_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$articles = require NETELLY_DIR . '/bin/news-import.php';
	$labels   = array( 'press' => 'プレスリリース', 'works' => '作品', 'fund' => 'ファンド', 'info' => 'お知らせ' );
	$log      = array();
	if ( isset( $_POST['netelly_news_import'] ) && check_admin_referer( 'netelly_news_import' ) ) {
		$choice = isset( $_POST['status'] ) ? array_map( 'sanitize_key', wp_unslash( (array) $_POST['status'] ) ) : array();
		foreach ( $articles as $a ) {
			$st = $choice[ $a['key'] ] ?? 'skip';
			if ( in_array( $st, array( 'publish', 'draft' ), true ) ) {
				$log[] = netelly_news_import_one( $a, $st );
			}
		}
		if ( ! $log ) {
			$log[] = '追加する記事が選ばれていません。';
		}
	}
	?>
	<div class="wrap">
		<h1>ニュース一括追加</h1>
		<p>下の記事を、日本語と英語の記事（翻訳として紐づけ済み）でまとめて追加します。追加済みの記事は何度押しても重複しません。</p>
		<p><strong>※架空サンプル</strong>の記事は、実在しない作品・実績の文章です。初期値は「下書き」にしています。実際の内容に書き換えてから公開してください。</p>
		<?php if ( $log ) : ?>
			<div class="notice notice-info"><pre style="white-space:pre-wrap"><?php echo esc_html( implode( "\n", $log ) ); ?></pre></div>
		<?php endif; ?>
		<?php if ( ! function_exists( 'pll_set_post_language' ) ) : ?>
			<div class="notice notice-error"><p>Polylang が有効になっていません。</p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'netelly_news_import' ); ?>
			<table class="widefat striped">
				<thead><tr><th style="width:9em">日付</th><th>タイトル</th><th style="width:9em">カテゴリー</th><th style="width:11em">追加方法</th></tr></thead>
				<tbody>
				<?php
				foreach ( $articles as $a ) :
					$done    = count( netelly_news_import_existing( $a['key'] ) ) === 2;
					$default = ! empty( $a['skip'] ) ? 'skip' : ( ! empty( $a['sample'] ) ? 'draft' : 'publish' );
					?>
					<tr>
						<td><?php echo esc_html( str_replace( '-', '.', $a['date'] ) ); ?></td>
						<td>
							<?php echo esc_html( $a['ja']['title'] ); ?>
							<?php if ( ! empty( $a['sample'] ) ) : ?>
								<strong style="color:#b32d2e">※架空サンプル</strong>
							<?php endif; ?>
							<?php if ( ! empty( $a['note'] ) ) : ?>
								<br><span style="color:#996800">⚠ <?php echo esc_html( $a['note'] ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $labels[ $a['cat'] ] ?? $a['cat'] ); ?></td>
						<td>
							<?php if ( $done ) : ?>
								追加済み
							<?php else : ?>
								<select name="status[<?php echo esc_attr( $a['key'] ); ?>]">
									<option value="publish"<?php selected( $default, 'publish' ); ?>>公開</option>
									<option value="draft"<?php selected( $default, 'draft' ); ?>>下書き</option>
									<option value="skip"<?php selected( $default, 'skip' ); ?>>追加しない</option>
								</select>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p><button class="button button-primary" name="netelly_news_import" value="1">選んだ記事を追加する</button></p>
		</form>
	</div>
	<?php
}

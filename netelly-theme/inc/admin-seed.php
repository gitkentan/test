<?php
/**
 * ツール › Netelly 初期データ: runs bin/seed.php from the admin, for servers
 * without WP-CLI (e.g. shared hosting). Same script as `npm run seed`.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function () {
		add_management_page( 'Netelly 初期データ', 'Netelly 初期データ', 'manage_options', 'netelly-seed', 'netelly_seed_admin_page' );
	}
);

/**
 * Admin screen + runner.
 */
function netelly_seed_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$missing = array_filter(
		array(
			'Polylang'                              => function_exists( 'pll_set_post_language' ),
			'Secure Custom Fields（または ACF Pro）' => function_exists( 'update_field' ),
			'Snow Monkey Forms'                     => post_type_exists( 'snow-monkey-forms' ),
		),
		static fn( $ok ) => ! $ok
	);
	$log = array();
	$ran = false;
	if ( isset( $_POST['netelly_seed'] ) && check_admin_referer( 'netelly_seed' ) && ! $missing && ! empty( $_POST['netelly_seed_confirm'] ) ) {
		$ran = true;
		$log = netelly_seed_run();
	}
	?>
	<div class="wrap">
		<h1>Netelly 初期データ</h1>
		<p>デザインの文章・作品・ニュース・募集職種・お問い合わせフォーム・メニュー・サイト設定（日本語／英語）を作成します。</p>
		<p><strong>注意：</strong>何度実行しても重複はしませんが、この機能で作った固定ページ・作品・ニュース・サイト設定は<strong>初期値に戻ります</strong>（管理画面で編集した内容は上書きされます）。公開前の最初の1回だけ使ってください。</p>
		<?php if ( $missing ) : ?>
			<div class="notice notice-error"><p>先に次のプラグインを有効化してください：<?php echo esc_html( implode( '、', array_keys( $missing ) ) ); ?></p></div>
		<?php endif; ?>
		<?php if ( $ran ) : ?>
			<div class="notice notice-info"><pre style="white-space:pre-wrap"><?php echo esc_html( implode( "\n", $log ) ); ?></pre></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'netelly_seed' ); ?>
			<p><label><input type="checkbox" name="netelly_seed_confirm" value="1"> 上書きされることを理解しました</label></p>
			<p><button class="button button-primary" name="netelly_seed" value="1"<?php disabled( (bool) $missing ); ?>>初期データを作成する</button></p>
		</form>
	</div>
	<?php
}

/**
 * Run bin/seed.php with a minimal WP_CLI stand-in that collects messages.
 *
 * @return string[] Log lines.
 */
function netelly_seed_run(): array {
	if ( ! class_exists( 'WP_CLI' ) ) {
		require_once __DIR__ . '/seed-cli-shim.php';
	}
	if ( ! defined( 'NETELLY_SEED_ADMIN' ) ) {
		define( 'NETELLY_SEED_ADMIN', true );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions
	}
	try {
		require NETELLY_DIR . '/bin/seed.php';
	} catch ( RuntimeException $e ) {
		WP_CLI::$lines[] = 'エラー: ' . $e->getMessage();
	}
	return WP_CLI::$lines;
}

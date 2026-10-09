<?php
/**
 * Temporary images: abstract light frames (assets/images/samples, tools/build-samples.py)
 * shown in image slots that have no image yet. An uploaded image always wins.
 * On/off: 設定 › 表示設定 › 仮画像.
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether empty image slots show a temporary image.
 */
function netelly_samples_on(): bool {
	return '1' === (string) get_option( 'netelly_sample_images', '1' );
}

/**
 * Next temporary image for a slot (cycles so neighbouring slots differ).
 *
 * @param string $ratio CSS aspect ratio ("16/9", "4/5" …).
 * @return array{url:string,w:int,h:int}
 */
function netelly_sample_image( string $ratio ): array {
	static $n = 0;
	$parts    = array_map( 'floatval', explode( '/', $ratio ) );
	$portrait = isset( $parts[1] ) && $parts[1] > 0 && $parts[0] / $parts[1] < 1;
	$i        = ( $n++ % 8 ) + 1;
	return array(
		'url' => NETELLY_URI . '/assets/images/samples/' . ( $portrait ? 'portrait' : 'landscape' ) . '-' . $i . '.webp',
		'w'   => $portrait ? 1000 : 1600,
		'h'   => $portrait ? 1400 : 1000,
	);
}

/**
 * <img> for a temporary image.
 *
 * @param string $ratio CSS aspect ratio.
 * @param string $class Image class.
 */
function netelly_sample_img( string $ratio, string $class ): string {
	$s = netelly_sample_image( $ratio );
	return sprintf(
		'<img class="%1$s" src="%2$s" width="%3$d" height="%4$d" alt="" loading="lazy" decoding="async">',
		esc_attr( $class ),
		esc_url( $s['url'] ),
		$s['w'],
		$s['h']
	);
}

add_action(
	'admin_init',
	static function () {
		register_setting(
			'reading',
			'netelly_sample_images',
			array(
				'type'              => 'string',
				'default'           => '1',
				'sanitize_callback' => static fn( $v ) => $v ? '1' : '0',
			)
		);
		add_settings_field(
			'netelly_sample_images',
			'仮画像',
			static function () {
				printf(
					'<input type="hidden" name="netelly_sample_images" value="0"><label><input type="checkbox" name="netelly_sample_images" value="1"%s> 画像が未設定の場所に仮の画像を表示する</label><p class="description">本番の写真を入れた場所は、その写真が表示されます。すべて入れ終わったらチェックを外してください。</p>',
					checked( netelly_samples_on(), true, false )
				);
			},
			'reading'
		);
	}
);

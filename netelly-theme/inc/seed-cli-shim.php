<?php
/**
 * Stand-in for the WP_CLI class when bin/seed.php runs from the admin (no WP-CLI).
 *
 * @package netelly
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable Generic.Files.OneObjectStructurePerFile, Squiz.Classes.ClassFileName
/**
 * Collects WP_CLI messages instead of printing them.
 */
class WP_CLI {
	/**
	 * Collected lines.
	 *
	 * @var string[]
	 */
	public static array $lines = array();

	public static function log( string $m ): void {
		self::$lines[] = $m;
	}

	public static function success( string $m ): void {
		self::$lines[] = '完了: ' . $m;
	}

	public static function warning( string $m ): void {
		self::$lines[] = '警告: ' . $m;
	}

	public static function error( string $m ): void {
		throw new RuntimeException( $m ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

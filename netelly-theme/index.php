<?php
/**
 * Fallback template. Until stage 4 it renders a token/font check page.
 *
 * @package netelly
 */

get_header();
$swatches = array( 'ink', 'paper', 'cream', 'black', 'line', 'line-dark', 'sub', 'sub-dark', 'muted', 'muted-dark' );
?>
<main id="main" class="specimen">
	<h1>NETELLY</h1>
	<div style="margin:24px 0 64px;color:var(--ink)"><?php echo netelly_svg( 'netelly-logo', array( 'width' => 180, 'aria-label' => 'NETELLY', 'role' => 'img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

	<div class="row"><span class="label">COLORS</span>
		<div class="swatches">
			<?php foreach ( $swatches as $s ) : ?>
				<span class="sw"><i style="background:var(--<?php echo esc_attr( $s ); ?>)"></i>--<?php echo esc_html( $s ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="row"><span class="label">ZEN KAKU 400</span><p style="font-size:16px">今、世界中でテクノロジーの発展による社会、産業、ライフスタイルの変革が起こっています。</p></div>
	<div class="row"><span class="label">ZEN KAKU 500</span><p style="font-size:16px;font-weight:500">縦型ショートドラマを、いち早く。</p></div>
	<div class="row"><span class="label">ZEN KAKU 700</span><p style="font-size:44px;font-weight:700;line-height:1.4">まだ誰も見たことのない、物語を。</p></div>
	<div class="row"><span class="label">ARCHIVO 125% 600</span><p style="font-family:var(--f-en);font-stretch:125%;font-weight:600;font-size:44px;letter-spacing:.02em;line-height:1.2">CREATORS FUND</p></div>
	<div class="row"><span class="label">ARCHIVO 125% 500</span><p style="font-family:var(--f-en);font-stretch:125%;font-weight:500;font-size:13px;letter-spacing:.36em">NEW ENTERTAINMENT, NEW VALUE.</p></div>
	<div class="row"><span class="label">ARCHIVO 100% 400</span><p style="font-family:var(--f-en);font-stretch:100%;font-size:16px">EN body copy — The quick brown fox jumps over the lazy dog. 1234567890</p></div>
	<div class="row"><span class="label">IBM PLEX MONO</span><p style="font-family:var(--f-mono);font-size:12px;letter-spacing:.14em">NETELLY INC. · TOKYO / NEW YORK / LOS ANGELES · 2026.10.08</p></div>
</main>
<?php
get_footer();

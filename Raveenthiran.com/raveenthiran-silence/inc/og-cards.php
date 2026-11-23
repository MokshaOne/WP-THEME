<?php
/**
 * #69 — Dynamic per-project Open Graph share cards.
 *
 * Composites a 1200×630 card (the correct social ratio) per project:
 *   featured photo (cover-fit) → dark gradient → title + category·year + mark.
 * Served from a virtual endpoint /nr-og/<id>.jpg and cached to the uploads
 * dir (keyed on the post's modified time, so edits bust the cache).
 *
 * Fully defensive: if GD is missing, the source photo can't be read, or the
 * bundled TTF is absent, the endpoint streams the plain featured image and
 * the meta still resolves to a valid picture — so nothing ever breaks.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

const NR_OG_W = 1200;
const NR_OG_H = 630;

/* Point a single project's og:image at the dynamic card (seo.php reads this). */
function nr_og_card_url( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id || ! has_post_thumbnail( $post_id ) ) return '';
	$v = substr( md5( (string) get_post_modified_time( 'U', true, $post_id ) ), 0, 8 );
	return home_url( '/nr-og/' . $post_id . '.jpg?v=' . $v );
}

/* ── Virtual endpoint: /nr-og/<id>.jpg ─────────────────────────── */
add_action( 'template_redirect', function () {
	$path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	if ( ! preg_match( '#/nr-og/(\d+)\.jpg$#', (string) $path, $m ) ) return;
	nr_og_serve( (int) $m[1] );
}, 0 );

function nr_og_serve( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || $post->post_type !== 'nr_project' || ! has_post_thumbnail( $post_id ) ) {
		status_header( 404 ); exit;
	}

	$src_path = get_attached_file( get_post_thumbnail_id( $post_id ) );
	if ( ! $src_path || ! file_exists( $src_path ) ) { status_header( 404 ); exit; }

	$up    = wp_upload_dir();
	$dir   = trailingslashit( $up['basedir'] ) . 'nr-og';
	$key   = $post_id . '-' . substr( md5( (string) get_post_modified_time( 'U', true, $post_id ) ), 0, 10 ) . '.jpg';
	$cache = $dir . '/' . $key;

	header( 'Content-Type: image/jpeg' );
	header( 'Cache-Control: public, max-age=86400' );

	if ( file_exists( $cache ) ) { readfile( $cache ); exit; }

	$bytes = nr_og_render( $post_id, $src_path );
	if ( $bytes === null ) { // GD/render failed → stream the source photo
		header( 'Content-Type: ' . ( get_post_mime_type( get_post_thumbnail_id( $post_id ) ) ?: 'image/jpeg' ) );
		readfile( $src_path ); exit;
	}
	wp_mkdir_p( $dir );
	@file_put_contents( $cache, $bytes );
	echo $bytes;
	exit;
}

/* Returns JPEG bytes, or null to signal "fall back to the raw photo". */
function nr_og_render( $post_id, $src_path ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) || ! function_exists( 'imagecreatefromstring' ) ) return null;

	$raw = @file_get_contents( $src_path );
	if ( ! $raw ) return null;
	$photo = @imagecreatefromstring( $raw );
	if ( ! $photo ) return null;

	$canvas = imagecreatetruecolor( NR_OG_W, NR_OG_H );

	// cover-fit the photo
	$sw = imagesx( $photo ); $sh = imagesy( $photo );
	$scale = max( NR_OG_W / $sw, NR_OG_H / $sh );
	$dw = (int) ceil( $sw * $scale ); $dh = (int) ceil( $sh * $scale );
	$dx = (int) ( ( NR_OG_W - $dw ) / 2 ); $dy = (int) ( ( NR_OG_H - $dh ) / 2 );
	imagecopyresampled( $canvas, $photo, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh );
	imagedestroy( $photo );

	// darken: flat veil + stronger bottom gradient for text legibility
	nr_og_veil( $canvas );

	$accent = nr_og_hex( $canvas, nr_opt( 'nr_accent', '#EBEAE6' ) );
	$bone   = imagecolorallocate( $canvas, 242, 239, 233 );
	$mut    = imagecolorallocate( $canvas, 196, 192, 184 );

	$font_b = get_template_directory() . '/assets/fonts/inter-tight-700.ttf';
	$font_m = get_template_directory() . '/assets/fonts/inter-tight-500.ttf';
	$has_ttf = function_exists( 'imagettftext' ) && file_exists( $font_b ) && file_exists( $font_m );

	$pad = 64;
	if ( $has_ttf ) {
		$cat  = '';
		$terms = wp_get_post_terms( $post_id, 'nr_project_cat', [ 'fields' => 'names' ] );
		if ( ! is_wp_error( $terms ) && $terms ) $cat = $terms[0];
		$yr   = get_post_meta( $post_id, 'project_year', true ) ?: get_the_date( 'Y', $post_id );
		$eyebrow = strtoupper( trim( $cat . ( $cat && $yr ? '   ·   ' : '' ) . $yr ) );
		$title   = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' );

		// accent rule
		imagefilledrectangle( $canvas, $pad, NR_OG_H - 232, $pad + 46, NR_OG_H - 229, $accent );

		if ( $eyebrow ) imagettftext( $canvas, 20, 0, $pad, NR_OG_H - 196, $mut, $font_m, $eyebrow );

		// title — wrap to width, cap 3 lines
		$lines = nr_og_wrap( $title, $font_b, 60, NR_OG_W - $pad * 2 );
		$lines = array_slice( $lines, 0, 3 );
		$ly = NR_OG_H - 150 + ( 3 - count( $lines ) ) * 0; // baseline of first line
		foreach ( $lines as $ln ) {
			imagettftext( $canvas, 60, 0, $pad, $ly, $bone, $font_b, $ln );
			$ly += 74;
		}

		// studio wordmark, top-left
		imagettftext( $canvas, 22, 0, $pad, 84, $bone, $font_b, (string) nr_opt( 'nr_logo_text', 'raveenthiran' ) );
	}

	ob_start();
	imagejpeg( $canvas, null, 86 );
	$out = ob_get_clean();
	imagedestroy( $canvas );
	return $out;
}

/* dark veil + bottom gradient */
function nr_og_veil( $im ) {
	imagealphablending( $im, true );
	// flat 22% darken
	$veil = imagecolorallocatealpha( $im, 11, 12, 16, 100 );
	imagefilledrectangle( $im, 0, 0, NR_OG_W, NR_OG_H, $veil );
	// bottom gradient (last ~55%)
	$start = (int) ( NR_OG_H * 0.42 );
	for ( $y = $start; $y < NR_OG_H; $y++ ) {
		$t = ( $y - $start ) / ( NR_OG_H - $start );
		$a = (int) ( 110 - $t * 110 ); // 110→0 alpha (0 = opaque)
		$c = imagecolorallocatealpha( $im, 11, 12, 16, max( 0, $a ) );
		imagefilledrectangle( $im, 0, $y, NR_OG_W, $y, $c );
	}
}

function nr_og_hex( $im, $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( strlen( $hex ) === 3 ) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) $hex = 'F2A03D';
	return imagecolorallocate( $im, hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/* greedy word-wrap using the TTF metrics */
function nr_og_wrap( $text, $font, $size, $maxw ) {
	$words = preg_split( '/\s+/', trim( $text ) );
	$lines = []; $cur = '';
	foreach ( $words as $w ) {
		$try = $cur === '' ? $w : $cur . ' ' . $w;
		$bb  = imagettfbbox( $size, 0, $font, $try );
		$wid = abs( $bb[2] - $bb[0] );
		if ( $wid > $maxw && $cur !== '' ) { $lines[] = $cur; $cur = $w; }
		else { $cur = $try; }
	}
	if ( $cur !== '' ) $lines[] = $cur;
	return $lines;
}

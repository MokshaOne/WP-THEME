<?php
/**
 * inc/medium4.php — IDEAS-50-NEXT finishers (v4.67.0).
 *   #27 Dominant-colour placeholder — store a 1px average colour per image at
 *       upload (GD); paint it behind the image so there's no empty box while it
 *       loads. (Lighter than BlurHash; degrades silently when GD can't decode,
 *       e.g. AVIF without support.)
 *   #15 Series mood-board — [nr_moodboard series="slug"] a cover grid + a colour
 *       palette strip built from those dominant colours (no live decoding).
 *   #12 Image hotspots — [nr_hotspots img="123"]35,40 | note ;; 60,70 | note[/nr_hotspots]
 *       annotate a frame with positioned note pins (hover/focus/tap).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── #27 — capture a dominant colour at upload ─────────────────────────── */
add_action( 'add_attachment', function ( $id ) {
	if ( strpos( (string) get_post_mime_type( $id ), 'image/' ) !== 0 ) return;
	if ( ! function_exists( 'imagecreatefromstring' ) ) return;
	$file = get_attached_file( $id );
	if ( ! $file || ! is_readable( $file ) ) return;
	if ( (int) @filesize( $file ) > 8 * 1024 * 1024 ) return;   // skip very large files
	$data = @file_get_contents( $file );
	if ( ! $data ) return;
	$img = @imagecreatefromstring( $data );                      // false for formats GD can't decode
	if ( ! $img ) return;
	$w = imagesx( $img ); $h = imagesy( $img );
	$one = imagecreatetruecolor( 1, 1 );
	imagecopyresampled( $one, $img, 0, 0, 0, 0, 1, 1, $w, $h );
	$rgb = imagecolorat( $one, 0, 0 );
	imagedestroy( $img ); imagedestroy( $one );
	update_post_meta( $id, '_nr_dom', sprintf( '#%02x%02x%02x', ( $rgb >> 16 ) & 255, ( $rgb >> 8 ) & 255, $rgb & 255 ) );
} );

/* Paint the dominant colour behind the image until it decodes. */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $att ) {
	$dom = get_post_meta( $att->ID, '_nr_dom', true );
	if ( $dom && preg_match( '/^#[0-9a-f]{6}$/i', $dom ) ) {
		$attr['style'] = rtrim( (string) ( $attr['style'] ?? '' ), '; ' ) . ';background-color:' . $dom . ';';
	}
	return $attr;
}, 10, 2 );

function nr_dom_colour( $att_id ) {
	$d = get_post_meta( $att_id, '_nr_dom', true );
	return ( $d && preg_match( '/^#[0-9a-f]{6}$/i', $d ) ) ? $d : '';
}

/* ── #15 — series mood-board : [nr_moodboard series="slug" count="12"] ── */
add_shortcode( 'nr_moodboard', function ( $atts ) {
	$a = shortcode_atts( [ 'series' => '', 'count' => 12 ], $atts, 'nr_moodboard' );
	if ( $a['series'] === '' ) return '';
	$q = new WP_Query( [
		'post_type'      => 'nr_project',
		'posts_per_page' => max( 1, min( 24, (int) $a['count'] ) ),
		'no_found_rows'  => true,
		'tax_query'      => [ [ 'taxonomy' => 'nr_project_series', 'field' => 'slug', 'terms' => $a['series'] ] ],
	] );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$cells = ''; $palette = [];
	while ( $q->have_posts() ) { $q->the_post();
		$tid = get_post_thumbnail_id( get_the_ID() );
		if ( ! $tid ) continue;
		$dom = nr_dom_colour( $tid );
		if ( $dom && ! in_array( $dom, $palette, true ) && count( $palette ) < 6 ) $palette[] = $dom;
		$cells .= '<a class="nr-mood__cell" href="' . esc_url( get_permalink() ) . '"'
			. ( $dom ? ' style="background-color:' . esc_attr( $dom ) . '"' : '' ) . '>'
			. get_the_post_thumbnail( get_the_ID(), 'nr-thumb', [ 'loading' => 'lazy', 'decoding' => 'async', 'alt' => esc_attr( get_the_title() ) ] )
			. '</a>';
	}
	wp_reset_postdata();
	if ( $cells === '' ) return '';
	$strip = '';
	foreach ( $palette as $c ) $strip .= '<span style="background-color:' . esc_attr( $c ) . '"></span>';
	return nr_medium4_css()
		. '<figure class="nr-mood">'
		. ( $strip ? '<div class="nr-mood__palette" aria-hidden="true">' . $strip . '</div>' : '' )
		. '<div class="nr-mood__grid">' . $cells . '</div>'
		. '</figure>';
} );

/* ── #12 — image hotspots : [nr_hotspots img="123"]x,y | note ;; …[/nr_hotspots] ── */
add_shortcode( 'nr_hotspots', function ( $atts, $content = '' ) {
	$a = shortcode_atts( [ 'img' => '', 'size' => 'nr-hero' ], $atts, 'nr_hotspots' );
	$id = (int) $a['img'];
	if ( ! $id && is_singular() ) $id = (int) get_post_thumbnail_id( get_the_ID() );
	if ( ! $id ) return '';
	$img = wp_get_attachment_image( $id, $a['size'], false, [ 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ] );
	if ( ! $img ) return '';
	$pins = '';
	foreach ( array_filter( array_map( 'trim', explode( ';;', (string) $content ) ) ) as $i => $row ) {
		$parts = array_map( 'trim', explode( '|', $row, 2 ) );
		if ( ! preg_match( '/^\s*(\d{1,3})\s*,\s*(\d{1,3})\s*$/', $parts[0], $m ) ) continue;
		$x = min( 100, max( 0, (int) $m[1] ) ); $y = min( 100, max( 0, (int) $m[2] ) );
		$note = $parts[1] ?? '';
		$pins .= '<button type="button" class="nr-hs__pin" style="left:' . $x . '%;top:' . $y . '%" aria-label="' . esc_attr( $note ) . '">'
			. '<span class="nr-hs__dot">' . ( $i + 1 ) . '</span>'
			. ( $note !== '' ? '<span class="nr-hs__note">' . esc_html( $note ) . '</span>' : '' )
			. '</button>';
	}
	if ( $pins === '' ) return $img;
	return nr_medium4_css() . '<figure class="nr-hs">' . $img . $pins . '</figure>'
		. '<script>(function(){var f=document.currentScript.previousElementSibling;if(!f)return;'
		. 'f.addEventListener("click",function(e){var p=e.target.closest(".nr-hs__pin");'
		. '[].forEach.call(f.querySelectorAll(".nr-hs__pin"),function(b){if(b!==p)b.classList.remove("is-on");});'
		. 'if(p)p.classList.toggle("is-on");});})();</script>';
} );

function nr_medium4_css() {
	static $done = false; if ( $done ) return ''; $done = true;
	return '<style id="nr-medium4">'
		. '.nr-mood{margin:24px 0}'
		. '.nr-mood__palette{display:flex;height:10px;border-radius:3px;overflow:hidden;margin-bottom:12px}'
		. '.nr-mood__palette span{flex:1}'
		. '.nr-mood__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px}'
		. '.nr-mood__cell{display:block;aspect-ratio:4/5;overflow:hidden;border-radius:4px}'
		. '.nr-mood__cell img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s var(--ease,ease)}'
		. '.nr-mood__cell:hover img{transform:scale(1.05)}'
		. '.nr-hs{position:relative;display:inline-block;max-width:100%;margin:24px 0}'
		. '.nr-hs img{display:block;max-width:100%;height:auto;border-radius:4px}'
		. '.nr-hs__pin{position:absolute;transform:translate(-50%,-50%);background:none;border:0;padding:0;cursor:pointer;z-index:2}'
		. '.nr-hs__dot{display:grid;place-items:center;width:26px;height:26px;border-radius:50%;background:var(--amber,#F2A03D);color:#0B0C10;font:600 12px/1 var(--font-mono,monospace);box-shadow:0 0 0 4px rgba(242,160,61,.25);transition:transform .15s}'
		. '.nr-hs__pin:hover .nr-hs__dot,.nr-hs__pin:focus-visible .nr-hs__dot{transform:scale(1.15)}'
		. '.nr-hs__note{position:absolute;left:50%;bottom:calc(100% + 8px);transform:translateX(-50%);min-width:140px;max-width:240px;'
		. 'background:rgba(11,12,16,.94);color:#F2EFE9;border:1px solid rgba(242,239,233,.18);border-radius:6px;padding:8px 10px;'
		. 'font-size:13px;line-height:1.4;opacity:0;pointer-events:none;transition:opacity .15s;white-space:normal}'
		. '.nr-hs__pin:hover .nr-hs__note,.nr-hs__pin:focus-visible .nr-hs__note,.nr-hs__pin.is-on .nr-hs__note{opacity:1}'
		. '</style>';
}

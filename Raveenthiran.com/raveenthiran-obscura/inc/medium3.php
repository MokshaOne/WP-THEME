<?php
/**
 * inc/medium3.php — IDEAS-50-NEXT, Medium batch 3 (v4.63.0).
 * Two front-end viewers, self-contained (no EXIF, no external libs):
 *   #6  Print-size wall preview — [nr_wallpreview img="id"] a frame at
 *       A4/A3/A2/A1 against a 175 cm person for scale (viz only, no sale).
 *   #10 Burst / sequence viewer — [nr_burst ids="1,2,3"] step through a rapid
 *       series frame-by-frame (← → / buttons / scrubber).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Resolve a list of image attachment IDs from an "ids" attr, or — on a single
 * project — from its gallery field. Returns ints. */
function nr_m3_gallery_ids( $ids_attr = '' ) {
	$ids = [];
	if ( $ids_attr !== '' ) {
		foreach ( explode( ',', $ids_attr ) as $x ) { $x = (int) trim( $x ); if ( $x ) $ids[] = $x; }
		return $ids;
	}
	if ( is_singular( 'nr_project' ) && function_exists( 'nr_field' ) ) {
		$g = nr_field( 'project_gallery', get_the_ID() );
		if ( is_array( $g ) ) foreach ( $g as $row ) {
			$id = is_array( $row ) ? (int) ( $row['ID'] ?? $row['id'] ?? 0 ) : (int) $row;
			if ( $id ) $ids[] = $id;
		}
	}
	return $ids;
}

/* ════════════════════════════════════════════════════════════════════════
 * #6 — Print-size wall preview : [nr_wallpreview img="id" sizes="A4,A3,A2,A1"]
 *  No "img" → the current project's featured image.
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_wallpreview', function ( $atts ) {
	$a = shortcode_atts( [ 'img' => '', 'start' => 'A2' ], $atts, 'nr_wallpreview' );
	$id = (int) $a['img'];
	if ( ! $id && is_singular() ) $id = (int) get_post_thumbnail_id( get_the_ID() );
	if ( ! $id ) return '';
	$img = wp_get_attachment_image( $id, 'nr-card', false, [ 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ] );
	if ( ! $img ) return '';
	$sizes = [ 'A4', 'A3', 'A2', 'A1' ];
	$start = in_array( $a['start'], $sizes, true ) ? $a['start'] : 'A2';
	$btns = '';
	foreach ( $sizes as $s ) {
		$btns .= '<button type="button" class="nr-wall__btn' . ( $s === $start ? ' is-on' : '' ) . '" data-size="' . esc_attr( $s ) . '">' . esc_html( $s ) . '</button>';
	}
	$labels = [ 'A4' => '21 × 29.7 cm', 'A3' => '29.7 × 42 cm', 'A2' => '42 × 59.4 cm', 'A1' => '59.4 × 84.1 cm' ];
	return nr_medium3_css()
		. '<figure class="nr-wall" data-wall data-start="' . esc_attr( $start ) . '">'
		. '<div class="nr-wall__room">'
		. '<span class="nr-wall__person" aria-hidden="true"></span>'
		. '<span class="nr-wall__frame">' . $img . '</span>'
		. '</div>'
		. '<figcaption class="nr-wall__bar"><span class="nr-wall__sizes">' . $btns . '</span>'
		. '<span class="nr-wall__dim" data-wall-dim>' . esc_html( $labels[ $start ] ) . '</span>'
		. '<span class="nr-wall__note">' . esc_html__( 'approx. scale · 175 cm reference', 'raveenthiran' ) . '</span></figcaption>'
		. '</figure>'
		. '<script>' . nr_medium3_wall_js( wp_json_encode( $labels ) ) . '</script>';
} );

function nr_medium3_wall_js( $labels_json ) {
	// wall = 260 cm tall; print height % of wall = printHcm / 260 * 100.
	return '(function(){var L=' . $labels_json . ';var H={A4:29.7,A3:42,A2:59.4,A1:84.1},W={A4:21,A3:29.7,A2:42,A1:59.4};'
		. 'var w=document.currentScript.previousElementSibling;if(!w||!w.matches(".nr-wall"))w=document.querySelector(".nr-wall[data-wall]:not([data-done])");if(!w)return;w.setAttribute("data-done","1");'
		. 'var fr=w.querySelector(".nr-wall__frame"),dim=w.querySelector("[data-wall-dim]");'
		. 'function set(s){fr.style.height=(H[s]/260*100)+"%";fr.style.aspectRatio=W[s]+"/"+H[s];if(dim)dim.textContent=L[s]||"";'
		. '[].forEach.call(w.querySelectorAll(".nr-wall__btn"),function(b){b.classList.toggle("is-on",b.dataset.size===s);});}'
		. 'w.addEventListener("click",function(e){var b=e.target.closest(".nr-wall__btn");if(b)set(b.dataset.size);});'
		. 'set(w.getAttribute("data-start")||"A2");})();';
}

/* ════════════════════════════════════════════════════════════════════════
 * #10 — Burst / sequence viewer : [nr_burst ids="12,13,14"]
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_burst', function ( $atts ) {
	$a   = shortcode_atts( [ 'ids' => '' ], $atts, 'nr_burst' );
	$ids = nr_m3_gallery_ids( (string) $a['ids'] );
	if ( count( $ids ) < 2 ) return '';
	$frames = '';
	foreach ( $ids as $i => $id ) {
		$im = wp_get_attachment_image( $id, 'nr-card', false, [
			'loading' => $i === 0 ? 'eager' : 'lazy', 'decoding' => 'async', 'alt' => '',
			'class' => 'nr-burst__img' . ( $i === 0 ? ' is-on' : '' ),
		] );
		if ( $im ) $frames .= $im;
	}
	if ( $frames === '' ) return '';
	$n = count( $ids );
	return nr_medium3_css()
		. '<figure class="nr-burst" data-burst tabindex="0" aria-label="' . esc_attr__( 'Frame sequence', 'raveenthiran' ) . '">'
		. '<div class="nr-burst__stage">' . $frames . '</div>'
		. '<div class="nr-burst__bar">'
		. '<button type="button" class="nr-burst__nav" data-burst-prev aria-label="' . esc_attr__( 'Previous frame', 'raveenthiran' ) . '">←</button>'
		. '<input type="range" class="nr-burst__scrub" min="1" max="' . (int) $n . '" value="1" aria-label="' . esc_attr__( 'Frame', 'raveenthiran' ) . '">'
		. '<button type="button" class="nr-burst__nav" data-burst-next aria-label="' . esc_attr__( 'Next frame', 'raveenthiran' ) . '">→</button>'
		. '<span class="nr-burst__count"><b data-burst-i>1</b> / ' . (int) $n . '</span>'
		. '</div></figure>'
		. '<script>' . nr_medium3_burst_js() . '</script>';
} );

function nr_medium3_burst_js() {
	return '(function(){var b=document.currentScript.previousElementSibling;if(!b||!b.matches(".nr-burst"))b=document.querySelector(".nr-burst[data-burst]:not([data-done])");if(!b)return;b.setAttribute("data-done","1");'
		. 'var im=b.querySelectorAll(".nr-burst__img"),sc=b.querySelector(".nr-burst__scrub"),ci=b.querySelector("[data-burst-i]"),i=0;'
		. 'function go(n){i=(n+im.length)%im.length;[].forEach.call(im,function(x,k){x.classList.toggle("is-on",k===i);});if(sc)sc.value=i+1;if(ci)ci.textContent=i+1;}'
		. 'b.querySelector("[data-burst-prev]").addEventListener("click",function(){go(i-1);});'
		. 'b.querySelector("[data-burst-next]").addEventListener("click",function(){go(i+1);});'
		. 'if(sc)sc.addEventListener("input",function(){go(parseInt(sc.value,10)-1);});'
		. 'b.addEventListener("keydown",function(e){if(e.key==="ArrowLeft"){go(i-1);e.preventDefault();}else if(e.key==="ArrowRight"){go(i+1);e.preventDefault();}});})();';
}

/* ── one shared <style>, printed once per request ──────────────────────── */
function nr_medium3_css() {
	static $done = false; if ( $done ) return ''; $done = true;
	return '<style id="nr-medium3">'
		/* wall preview */
		. '.nr-wall{margin:24px 0;max-width:680px}'
		. '.nr-wall__room{position:relative;height:clamp(300px,44vw,480px);background:linear-gradient(180deg,#15161b,#0e0f13 78%,#0a0b0e);border:1px solid rgba(242,239,233,.1);border-radius:6px;display:flex;align-items:flex-end;justify-content:center;gap:6%;padding:0 8%;overflow:hidden}'
		. '.nr-wall__room::after{content:"";position:absolute;left:0;right:0;bottom:14%;height:1px;background:rgba(242,239,233,.14)}' /* floor line */
		. '.nr-wall__person{width:42px;height:67.3%;align-self:flex-end;margin-bottom:14%;background:rgba(242,239,233,.16);border-radius:40px 40px 8px 8px/60px 60px 8px 8px;position:relative;flex:0 0 auto}'
		. '.nr-wall__person::before{content:"";position:absolute;top:-9%;left:50%;transform:translateX(-50%);width:26px;height:26px;border-radius:50%;background:rgba(242,239,233,.16)}'
		. '.nr-wall__frame{align-self:flex-end;margin-bottom:14%;height:23%;aspect-ratio:42/59.4;background:#000;border:3px solid #f4f1ea;box-shadow:0 12px 30px rgba(0,0,0,.5);transition:height .35s var(--ease,ease),aspect-ratio .35s;display:block;flex:0 0 auto;overflow:hidden}'
		. '.nr-wall__frame img{width:100%;height:100%;object-fit:cover;display:block}'
		. '.nr-wall__bar{display:flex;flex-wrap:wrap;align-items:center;gap:10px 16px;margin-top:12px;font-size:13px}'
		. '.nr-wall__sizes{display:inline-flex;gap:4px}'
		. '.nr-wall__btn{padding:5px 11px;border:1px solid rgba(242,239,233,.22);background:none;color:inherit;border-radius:6px;cursor:pointer;font:600 12px/1 var(--font-mono,monospace)}'
		. '.nr-wall__btn.is-on{background:var(--amber,#F2A03D);color:#0B0C10;border-color:var(--amber,#F2A03D)}'
		. '.nr-wall__dim{font-family:var(--font-mono,monospace);opacity:.85}.nr-wall__note{opacity:.5;font-size:11px;margin-left:auto}'
		/* burst viewer */
		. '.nr-burst{margin:24px 0;max-width:760px;outline:none}'
		. '.nr-burst__stage{position:relative;width:100%;aspect-ratio:4/5;background:var(--bg-2,#14151A);border-radius:6px;overflow:hidden}'
		. '.nr-burst__img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .12s linear}'
		. '.nr-burst__img.is-on{opacity:1}'
		. '.nr-burst__bar{display:flex;align-items:center;gap:12px;margin-top:10px}'
		. '.nr-burst__nav{width:38px;height:38px;border:1px solid rgba(242,239,233,.22);background:none;color:inherit;border-radius:50%;cursor:pointer;font-size:16px;flex:0 0 auto}'
		. '.nr-burst__nav:hover{border-color:var(--amber,#F2A03D)}'
		. '.nr-burst__scrub{flex:1 1 auto;accent-color:var(--amber,#F2A03D)}'
		. '.nr-burst__count{font:600 12px/1 var(--font-mono,monospace);opacity:.7;flex:0 0 auto}'
		. '</style>';
}

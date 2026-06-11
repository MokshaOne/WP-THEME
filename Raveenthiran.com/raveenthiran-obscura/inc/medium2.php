<?php
/**
 * inc/medium2.php — IDEAS-50-NEXT, Medium batch 2 (v4.61.0; trimmed v4.63.0).
 * Editorial surfacing, all self-contained. (The EXIF-based items #2/#3/#9 were
 * removed in v4.63.0 — the studio works in AVIF/WebP, which carry no readable
 * EXIF, so they never had data.)
 *   #7  Aspect-true masonry  — [nr_masonry] archive honouring each crop.
 *   #11 Field notes          — [nr_fieldnotes] short-form micro-journal band.
 *   #16 Pull-quote rotator   — [nr_pullquotes] rotating strong lines.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════════════
 * #7 — Aspect-true masonry : [nr_masonry cat="slug" count="24"]
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_masonry', function ( $atts ) {
	$a = shortcode_atts( [ 'cat' => '', 'count' => 24 ], $atts, 'nr_masonry' );
	$args = [ 'post_type' => 'nr_project', 'posts_per_page' => (int) $a['count'], 'no_found_rows' => true ];
	if ( $a['cat'] !== '' ) $args['tax_query'] = [ [ 'taxonomy' => 'nr_project_cat', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $a['cat'] ) ) ] ];
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$out = nr_medium2_css() . '<div class="nr-masonry">';
	while ( $q->have_posts() ) { $q->the_post();
		if ( ! has_post_thumbnail() ) continue;
		$out .= '<a class="nr-masonry__item" href="' . esc_url( get_permalink() ) . '">'
			. get_the_post_thumbnail( get_the_ID(), 'nr-card', [ 'loading' => 'lazy', 'decoding' => 'async' ] )
			. '<span class="nr-masonry__cap">' . esc_html( get_the_title() ) . '</span></a>';
	}
	wp_reset_postdata();
	return $out . '</div>';
} );

/* ════════════════════════════════════════════════════════════════════════
 * #11 — Field notes : [nr_fieldnotes cat="field-notes" count="6"]
 *  A short-form micro-journal. Falls back to recent short journal entries if
 *  the category doesn't exist yet.
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_fieldnotes', function ( $atts ) {
	$a = shortcode_atts( [ 'cat' => 'field-notes', 'count' => 6 ], $atts, 'nr_fieldnotes' );
	$args = [ 'post_type' => 'nr_journal', 'posts_per_page' => (int) $a['count'], 'no_found_rows' => true ];
	if ( $a['cat'] !== '' && term_exists( $a['cat'], 'nr_journal_cat' ) ) {
		$args['tax_query'] = [ [ 'taxonomy' => 'nr_journal_cat', 'field' => 'slug', 'terms' => $a['cat'] ] ];
	}
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$out = nr_medium2_css() . '<div class="nr-fieldnotes">';
	while ( $q->have_posts() ) { $q->the_post();
		$note = wp_trim_words( get_the_excerpt(), 26, '…' );
		$out .= '<a class="nr-fnote" href="' . esc_url( get_permalink() ) . '">'
			. ( has_post_thumbnail() ? '<span class="nr-fnote__img">' . get_the_post_thumbnail( get_the_ID(), 'nr-thumb', [ 'loading' => 'lazy', 'decoding' => 'async' ] ) . '</span>' : '' )
			. '<span class="nr-fnote__body"><time class="nr-fnote__date">' . esc_html( get_the_date( 'j M Y' ) ) . '</time>'
			. '<span class="nr-fnote__txt">' . esc_html( $note ) . '</span></span></a>';
	}
	wp_reset_postdata();
	return $out . '</div>';
} );

/* ════════════════════════════════════════════════════════════════════════
 * #16 — Pull-quote rotator : [nr_pullquotes]
 *  Source: the nr_pullquotes setting ("quote | source" per line). If empty,
 *  falls back to journal excerpts. Rotates with a tiny inline script.
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_pullquotes', function () {
	$quotes = [];
	$raw = function_exists( 'nr_opt' ) ? (string) nr_opt( 'nr_pullquotes', '' ) : '';
	if ( $raw !== '' && function_exists( 'nr_parse_pipe_lines' ) ) {
		foreach ( nr_parse_pipe_lines( $raw ) as $r ) {
			$quotes[] = [ 'q' => $r[0] ?? '', 'src' => $r[1] ?? '' ];
		}
	}
	if ( ! $quotes ) {
		$q = new WP_Query( [ 'post_type' => 'nr_journal', 'posts_per_page' => 6, 'no_found_rows' => true, 'orderby' => 'rand' ] );
		while ( $q->have_posts() ) { $q->the_post();
			$ex = trim( wp_strip_all_tags( get_the_excerpt() ) );
			if ( $ex !== '' ) $quotes[] = [ 'q' => wp_trim_words( $ex, 22, '…' ), 'src' => get_the_title() ];
		}
		wp_reset_postdata();
	}
	$quotes = array_values( array_filter( $quotes, function ( $x ) { return trim( (string) $x['q'] ) !== ''; } ) );
	if ( ! $quotes ) return '';
	$items = '';
	foreach ( $quotes as $i => $x ) {
		$items .= '<figure class="nr-pq__item' . ( $i === 0 ? ' is-on' : '' ) . '"><blockquote>' . esc_html( $x['q'] ) . '</blockquote>'
			. ( $x['src'] !== '' ? '<figcaption>— ' . esc_html( $x['src'] ) . '</figcaption>' : '' ) . '</figure>';
	}
	$js = '';
	if ( count( $quotes ) > 1 ) {
		$js = '<script>(function(){var r=document.currentScript.previousElementSibling;if(!r)return;'
			. 'if(window.matchMedia&&window.matchMedia("(prefers-reduced-motion: reduce)").matches)return;'
			. 'var it=r.querySelectorAll(".nr-pq__item"),i=0;setInterval(function(){it[i].classList.remove("is-on");i=(i+1)%it.length;it[i].classList.add("is-on");},6000);})();</script>';
	}
	return nr_medium2_css() . '<div class="nr-pq" role="group" aria-label="' . esc_attr__( 'Quotes', 'raveenthiran' ) . '">' . $items . '</div>' . $js;
} );

/* ── one shared <style>, printed once per request ──────────────────────── */
function nr_medium2_css() {
	static $done = false; if ( $done ) return ''; $done = true;
	return '<style id="nr-medium2">'
		. '.nr-masonry{columns:3 280px;column-gap:14px}.nr-masonry__item{display:inline-block;width:100%;margin:0 0 14px;text-decoration:none;color:inherit;break-inside:avoid;position:relative}'
		. '.nr-masonry__item img{width:100%;height:auto;display:block;border-radius:4px}'
		. '.nr-masonry__cap{position:absolute;left:10px;bottom:10px;font-size:12px;padding:3px 8px;background:rgba(11,12,16,.6);border-radius:3px;opacity:0;transition:opacity .2s}'
		. '.nr-masonry__item:hover .nr-masonry__cap{opacity:1}'
		. '.nr-fieldnotes{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin:24px 0}'
		. '.nr-fnote{display:flex;gap:12px;text-decoration:none;color:inherit;border-top:1px solid rgba(242,239,233,.14);padding-top:12px}'
		. '.nr-fnote__img{flex:0 0 72px}.nr-fnote__img img{width:72px;height:72px;object-fit:cover;border-radius:4px;display:block}'
		. '.nr-fnote__date{display:block;font:600 10px/1 var(--font-mono,monospace);letter-spacing:.1em;text-transform:uppercase;opacity:.55;margin-bottom:4px}'
		. '.nr-fnote__txt{font-size:14px;line-height:1.45}'
		. '.nr-pq{position:relative;min-height:5em;margin:32px 0;max-width:680px}'
		. '.nr-pq__item{position:absolute;inset:0;opacity:0;transition:opacity .8s;pointer-events:none}.nr-pq__item.is-on{opacity:1;pointer-events:auto;position:relative}'
		. '.nr-pq blockquote{font-size:var(--fs-lg,1.4rem);line-height:1.4;margin:0;font-style:italic}'
		. '.nr-pq figcaption{margin-top:10px;font:600 12px/1 var(--font-mono,monospace);letter-spacing:.08em;opacity:.6}'
		. '</style>';
}

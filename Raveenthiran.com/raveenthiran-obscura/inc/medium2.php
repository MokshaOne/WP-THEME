<?php
/**
 * inc/medium2.php — IDEAS-50-NEXT, Medium batch 2 (v4.61.0).
 * The body of work as data + a little editorial surfacing. All self-contained:
 * no external libraries, no uploads, no service-worker changes.
 *   #2  "Shot on" facet      — index cameras/lenses per project, filter the
 *                              portfolio archive, [nr_shot_on] chip list.
 *   #3  Lens & focal chart   — [nr_focal_chart] inline-SVG histogram.
 *   #9  Related-by-EXIF      — [nr_related_gear] other work on the same body.
 *   #7  Aspect-true masonry  — [nr_masonry] archive honouring each crop.
 *   #11 Field notes          — [nr_fieldnotes] short-form micro-journal band.
 *   #16 Pull-quote rotator   — [nr_pullquotes] rotating strong lines.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── shared: collect a project's image attachment IDs (featured + attached) ── */
function nr_project_image_ids( $post_id ) {
	$ids = [];
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb ) $ids[] = (int) $thumb;
	$kids = get_children( [ 'post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => -1, 'fields' => 'ids' ] );
	foreach ( (array) $kids as $k ) $ids[] = (int) $k;
	return array_values( array_unique( array_filter( $ids ) ) );
}

/* ════════════════════════════════════════════════════════════════════════
 * #2 — "Shot on" facet : index gear per project, filter the archive
 * ════════════════════════════════════════════════════════════════════════ */

/* Build the gear index on save: a display array in _nr_gear, plus one flat
 * _nr_camera meta row per distinct camera so meta_query can filter on it. */
function nr_index_project_gear( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || get_post_type( $post_id ) !== 'nr_project' ) return;
	if ( ! function_exists( 'nr_get_exif' ) ) return;
	$cams = []; $lenses = [];
	foreach ( nr_project_image_ids( $post_id ) as $aid ) {
		$ex = nr_get_exif( $aid );
		if ( ! empty( $ex['camera'] ) ) $cams[ $ex['camera'] ]   = true;
		if ( ! empty( $ex['lens'] ) )   $lenses[ $ex['lens'] ]   = true;
	}
	$cams = array_keys( $cams ); $lenses = array_keys( $lenses );
	if ( $cams || $lenses ) update_post_meta( $post_id, '_nr_gear', [ 'cameras' => $cams, 'lenses' => $lenses ] );
	else delete_post_meta( $post_id, '_nr_gear' );
	// flat, queryable camera rows
	delete_post_meta( $post_id, '_nr_camera' );
	foreach ( $cams as $c ) add_post_meta( $post_id, '_nr_camera', $c );
	delete_transient( 'nr_gear_facets' );
}
add_action( 'save_post_nr_project', 'nr_index_project_gear' );

/* One-time backfill so the facet works on projects saved before this version.
 * (Cheap: reads each project's existing _nr_exif; runs once per install.) */
add_action( 'admin_init', function () {
	if ( get_option( 'nr_gear_indexed' ) ) return;
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'any', 'no_found_rows' => true ] );
	foreach ( (array) $ids as $pid ) nr_index_project_gear( $pid );
	update_option( 'nr_gear_indexed', 1, false );
} );

/* Filter the portfolio archive when ?shot_on=… is present. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) return;
	if ( ! $q->is_post_type_archive( 'nr_project' ) ) return;
	$cam = isset( $_GET['shot_on'] ) ? sanitize_text_field( wp_unslash( $_GET['shot_on'] ) ) : '';
	if ( $cam !== '' ) $q->set( 'meta_query', [ [ 'key' => '_nr_camera', 'value' => $cam ] ] );
} );

/* Distinct cameras across published projects (cached). */
function nr_gear_facets() {
	$cached = get_transient( 'nr_gear_facets' );
	if ( is_array( $cached ) ) return $cached;
	global $wpdb;
	$rows = $wpdb->get_col( "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_nr_camera' AND p.post_status = 'publish' AND p.post_type = 'nr_project'
		ORDER BY pm.meta_value ASC" );
	$rows = array_values( array_filter( array_map( 'strval', (array) $rows ) ) );
	set_transient( 'nr_gear_facets', $rows, 12 * HOUR_IN_SECONDS );
	return $rows;
}

/* [nr_shot_on] — chip links to the portfolio filtered by camera. */
add_shortcode( 'nr_shot_on', function () {
	$cams = nr_gear_facets();
	if ( ! $cams ) return '';
	$base = get_post_type_archive_link( 'nr_project' );
	$cur  = isset( $_GET['shot_on'] ) ? sanitize_text_field( wp_unslash( $_GET['shot_on'] ) ) : '';
	$out  = '<div class="nr-shoton"><span class="nr-shoton__label">' . esc_html__( 'Shot on', 'raveenthiran' ) . '</span>';
	$out .= '<a class="nr-chip' . ( $cur === '' ? ' is-on' : '' ) . '" href="' . esc_url( $base ) . '">' . esc_html__( 'All', 'raveenthiran' ) . '</a>';
	foreach ( $cams as $c ) {
		$on = $cur === $c ? ' is-on' : '';
		$out .= '<a class="nr-chip' . $on . '" href="' . esc_url( add_query_arg( 'shot_on', rawurlencode( $c ), $base ) ) . '">' . esc_html( $c ) . '</a>';
	}
	$out .= '</div>';
	return nr_medium2_css() . $out;
} );

/* ════════════════════════════════════════════════════════════════════════
 * #3 — Lens & focal-length chart : [nr_focal_chart]
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_focal_chart', function () {
	$buckets = get_transient( 'nr_focal_buckets' );
	if ( ! is_array( $buckets ) ) {
		$defs = [
			[ '≤24', 0, 24 ], [ '25–35', 25, 35 ], [ '36–50', 36, 50 ],
			[ '51–85', 51, 85 ], [ '86–135', 86, 135 ], [ '135+', 136, 100000 ],
		];
		$counts = array_fill( 0, count( $defs ), 0 );
		$q = new WP_Query( [ 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'image',
			'posts_per_page' => 2000, 'fields' => 'ids', 'meta_key' => '_nr_exif', 'no_found_rows' => true ] );
		foreach ( $q->posts as $aid ) {
			$ex = function_exists( 'nr_get_exif' ) ? nr_get_exif( $aid ) : [];
			if ( empty( $ex['focal'] ) ) continue;
			$mm = (int) preg_replace( '/[^0-9].*$/', '', (string) $ex['focal'] );
			if ( $mm <= 0 ) continue;
			foreach ( $defs as $i => $d ) { if ( $mm >= $d[1] && $mm <= $d[2] ) { $counts[ $i ]++; break; } }
		}
		$buckets = [];
		foreach ( $defs as $i => $d ) $buckets[] = [ 'label' => $d[0], 'n' => $counts[ $i ] ];
		set_transient( 'nr_focal_buckets', $buckets, 12 * HOUR_IN_SECONDS );
	}
	$max = 0; foreach ( $buckets as $b ) $max = max( $max, $b['n'] );
	if ( $max === 0 ) return '';
	$bw = 56; $gap = 18; $h = 150; $pad = 24; $w = $pad * 2 + count( $buckets ) * $bw + ( count( $buckets ) - 1 ) * $gap;
	$svg = '<svg class="nr-focal" viewBox="0 0 ' . $w . ' ' . ( $h + 46 ) . '" role="img" aria-label="' . esc_attr__( 'Focal length distribution', 'raveenthiran' ) . '">';
	foreach ( $buckets as $i => $b ) {
		$x  = $pad + $i * ( $bw + $gap );
		$bh = $b['n'] ? max( 3, (int) round( $b['n'] / $max * $h ) ) : 0;
		$y  = $pad + $h - $bh;
		$svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $bw . '" height="' . $bh . '" rx="3" class="nr-focal__bar"/>';
		$svg .= '<text x="' . ( $x + $bw / 2 ) . '" y="' . ( $y - 6 ) . '" class="nr-focal__n" text-anchor="middle">' . (int) $b['n'] . '</text>';
		$svg .= '<text x="' . ( $x + $bw / 2 ) . '" y="' . ( $pad + $h + 20 ) . '" class="nr-focal__lab" text-anchor="middle">' . esc_html( $b['label'] ) . '</text>';
	}
	$svg .= '<text x="' . $pad . '" y="' . ( $pad + $h + 40 ) . '" class="nr-focal__cap">' . esc_html__( 'frames by focal length (mm)', 'raveenthiran' ) . '</text>';
	$svg .= '</svg>';
	return nr_medium2_css() . '<figure class="nr-focal-wrap">' . $svg . '</figure>';
} );

/* keep the chart fresh when EXIF is (re)indexed */
add_action( 'save_post_nr_project', function () { delete_transient( 'nr_focal_buckets' ); } );

/* ════════════════════════════════════════════════════════════════════════
 * #9 — Related by EXIF : [nr_related_gear] (other work on the same camera)
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_related_gear', function () {
	if ( ! is_singular( 'nr_project' ) ) return '';
	$id  = get_the_ID();
	$gear = get_post_meta( $id, '_nr_gear', true );
	$cams = is_array( $gear ) && ! empty( $gear['cameras'] ) ? $gear['cameras'] : [];
	if ( ! $cams ) return '';
	$q = new WP_Query( [ 'post_type' => 'nr_project', 'posts_per_page' => 6, 'post__not_in' => [ $id ],
		'no_found_rows' => true, 'orderby' => 'rand',
		'meta_query' => [ [ 'key' => '_nr_camera', 'value' => $cams, 'compare' => 'IN' ] ] ] );
	if ( ! $q->have_posts() ) { wp_reset_postdata(); return ''; }
	$cam = esc_html( $cams[0] );
	$out = nr_medium2_css() . '<section class="nr-relgear"><h2 class="nr-relgear__h">' . sprintf( esc_html__( 'More work shot on %s', 'raveenthiran' ), $cam ) . '</h2><div class="nr-relgear__row">';
	while ( $q->have_posts() ) { $q->the_post();
		$out .= '<a class="nr-relgear__card" href="' . esc_url( get_permalink() ) . '">'
			. ( has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'nr-thumb', [ 'loading' => 'lazy', 'decoding' => 'async' ] ) : '' )
			. '<span>' . esc_html( get_the_title() ) . '</span></a>';
	}
	wp_reset_postdata();
	return $out . '</div></section>';
} );

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
		. '.nr-shoton{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:18px 0}'
		. '.nr-shoton__label{font:600 11px/1 var(--font-mono,monospace);letter-spacing:.12em;text-transform:uppercase;opacity:.6;margin-right:4px}'
		. '.nr-chip{display:inline-block;padding:6px 12px;border:1px solid rgba(242,239,233,.22);border-radius:999px;font-size:13px;text-decoration:none;color:inherit;transition:border-color .15s,background .15s}'
		. '.nr-chip:hover{border-color:var(--amber,#F2A03D)}.nr-chip.is-on{background:var(--amber,#F2A03D);color:#0B0C10;border-color:var(--amber,#F2A03D)}'
		. '.nr-focal-wrap{margin:24px 0}.nr-focal{width:100%;height:auto;max-width:520px}'
		. '.nr-focal__bar{fill:var(--amber,#F2A03D);opacity:.85}.nr-focal__n{fill:currentColor;font:600 12px/1 var(--font-mono,monospace)}'
		. '.nr-focal__lab{fill:currentColor;opacity:.7;font:500 11px/1 var(--font-mono,monospace)}.nr-focal__cap{fill:currentColor;opacity:.5;font:500 10px/1 var(--font-mono,monospace);letter-spacing:.08em;text-transform:uppercase}'
		. '.nr-relgear{margin:48px 0}.nr-relgear__h{font-size:var(--fs-md,1.1rem);margin:0 0 14px}'
		. '.nr-relgear__row{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}'
		. '.nr-relgear__card{text-decoration:none;color:inherit;display:block}.nr-relgear__card img{width:100%;height:auto;display:block;border-radius:4px}'
		. '.nr-relgear__card span{display:block;font-size:13px;margin-top:6px;opacity:.85}'
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

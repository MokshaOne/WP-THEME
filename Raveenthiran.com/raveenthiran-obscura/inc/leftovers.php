<?php
/**
 * inc/leftovers.php — IDEAS-50-NEXT small leftovers (v4.62.0).
 * The remaining S-tier items, all self-contained:
 *   #4  Time-of-day facet  — golden/blue/day/night from EXIF hour, [nr_tod].
 *   #14 Diptych compare    — [nr_diptych a="" b=""] two projects side by side.
 *   #17 Studio log         — [nr_studiolog] a public "what's new" timeline.
 *   #28 Video poster       — show frame 0 instead of a black flash (no ffmpeg).
 * (#18 footnoted credits is folded into nr_project_credits_markup in smallwins.php.)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ════════════════════════════════════════════════════════════════════════
 * #4 — Time-of-day facet (from the EXIF hour captured in inc/performance.php)
 * ════════════════════════════════════════════════════════════════════════ */
function nr_tod_label( $hour ) {
	$h = (int) $hour;
	if ( $h >= 5 && $h < 7 )   return 'blue';   // dawn blue hour
	if ( $h >= 7 && $h < 9 )   return 'golden'; // morning golden
	if ( $h >= 9 && $h < 17 )  return 'day';
	if ( $h >= 17 && $h < 19 ) return 'golden'; // evening golden
	if ( $h >= 19 && $h < 21 ) return 'blue';   // dusk blue hour
	return 'night';
}
function nr_tod_name( $slug ) {
	$map = [ 'blue' => __( 'Blue hour', 'raveenthiran' ), 'golden' => __( 'Golden hour', 'raveenthiran' ),
		'day' => __( 'Daylight', 'raveenthiran' ), 'night' => __( 'Night', 'raveenthiran' ) ];
	return $map[ $slug ] ?? $slug;
}

/* Index distinct time-of-day labels per project (flat _nr_tod rows for querying). */
function nr_index_project_tod( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || get_post_type( $post_id ) !== 'nr_project' ) return;
	if ( ! function_exists( 'nr_get_exif' ) || ! function_exists( 'nr_project_image_ids' ) ) return;
	$tods = [];
	foreach ( nr_project_image_ids( $post_id ) as $aid ) {
		$ex = nr_get_exif( $aid );
		if ( isset( $ex['hour'] ) ) $tods[ nr_tod_label( $ex['hour'] ) ] = true;
	}
	delete_post_meta( $post_id, '_nr_tod' );
	foreach ( array_keys( $tods ) as $t ) add_post_meta( $post_id, '_nr_tod', $t );
	delete_transient( 'nr_tod_facets' );
}
add_action( 'save_post_nr_project', 'nr_index_project_tod' );

/* One-time backfill (separate flag from the gear index). */
add_action( 'admin_init', function () {
	if ( get_option( 'nr_tod_indexed' ) ) return;
	$ids = get_posts( [ 'post_type' => 'nr_project', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'any', 'no_found_rows' => true ] );
	foreach ( (array) $ids as $pid ) nr_index_project_tod( $pid );
	update_option( 'nr_tod_indexed', 1, false );
} );

/* Filter the portfolio archive when ?tod=golden|blue|day|night is present. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'nr_project' ) ) return;
	$tod = isset( $_GET['tod'] ) ? sanitize_key( wp_unslash( $_GET['tod'] ) ) : '';
	if ( in_array( $tod, [ 'blue', 'golden', 'day', 'night' ], true ) ) {
		$mq = (array) $q->get( 'meta_query' );
		$mq[] = [ 'key' => '_nr_tod', 'value' => $tod ];
		$q->set( 'meta_query', $mq );
	}
} );

function nr_tod_facets() {
	$cached = get_transient( 'nr_tod_facets' );
	if ( is_array( $cached ) ) return $cached;
	global $wpdb;
	$rows = $wpdb->get_col( "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_nr_tod' AND p.post_status = 'publish' AND p.post_type = 'nr_project'" );
	$order = [ 'blue' => 0, 'golden' => 1, 'day' => 2, 'night' => 3 ];
	$rows  = array_values( array_filter( array_map( 'strval', (array) $rows ) ) );
	usort( $rows, function ( $a, $b ) use ( $order ) { return ( $order[ $a ] ?? 9 ) <=> ( $order[ $b ] ?? 9 ); } );
	set_transient( 'nr_tod_facets', $rows, 12 * HOUR_IN_SECONDS );
	return $rows;
}

add_shortcode( 'nr_tod', function () {
	$tods = nr_tod_facets();
	if ( ! $tods ) return '';
	$base = get_post_type_archive_link( 'nr_project' );
	$cur  = isset( $_GET['tod'] ) ? sanitize_key( wp_unslash( $_GET['tod'] ) ) : '';
	$chip_css = function_exists( 'nr_medium2_css' ) ? nr_medium2_css() : '';
	$out  = $chip_css . nr_leftovers_css() . '<div class="nr-shoton"><span class="nr-shoton__label">' . esc_html__( 'Time of day', 'raveenthiran' ) . '</span>';
	$out .= '<a class="nr-chip' . ( $cur === '' ? ' is-on' : '' ) . '" href="' . esc_url( $base ) . '">' . esc_html__( 'Any', 'raveenthiran' ) . '</a>';
	foreach ( $tods as $t ) {
		$out .= '<a class="nr-chip' . ( $cur === $t ? ' is-on' : '' ) . '" href="' . esc_url( add_query_arg( 'tod', $t, $base ) ) . '">' . esc_html( nr_tod_name( $t ) ) . '</a>';
	}
	return $out . '</div>';
} );

/* ════════════════════════════════════════════════════════════════════════
 * #14 — Diptych compare : [nr_diptych a="slug|id" b="slug|id"]
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_diptych', function ( $atts ) {
	$a = shortcode_atts( [ 'a' => '', 'b' => '' ], $atts, 'nr_diptych' );
	$resolve = function ( $ref ) {
		$ref = trim( (string) $ref );
		if ( $ref === '' ) return 0;
		if ( ctype_digit( $ref ) ) return (int) $ref;
		$p = get_page_by_path( $ref, OBJECT, 'nr_project' );
		return $p ? (int) $p->ID : 0;
	};
	$ids = array_filter( [ $resolve( $a['a'] ), $resolve( $a['b'] ) ] );
	if ( count( $ids ) < 2 ) return '';
	$out = nr_leftovers_css() . '<div class="nr-diptych">';
	foreach ( $ids as $id ) {
		$out .= '<a class="nr-diptych__side" href="' . esc_url( get_permalink( $id ) ) . '">'
			. ( has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'nr-card', [ 'loading' => 'lazy', 'decoding' => 'async' ] ) : '' )
			. '<span class="nr-diptych__cap">' . esc_html( get_the_title( $id ) ) . '</span></a>';
	}
	return $out . '</div>';
} );

/* ════════════════════════════════════════════════════════════════════════
 * #17 — Studio log : [nr_studiolog count="8"]
 *  Source: the nr_studiolog setting ("YYYY-MM-DD | note" per line). If empty,
 *  falls back to the most recent journal entries.
 * ════════════════════════════════════════════════════════════════════════ */
add_shortcode( 'nr_studiolog', function ( $atts ) {
	$a   = shortcode_atts( [ 'count' => 8 ], $atts, 'nr_studiolog' );
	$raw = function_exists( 'nr_opt' ) ? (string) nr_opt( 'nr_studiolog', '' ) : '';
	$items = [];
	if ( $raw !== '' && function_exists( 'nr_parse_pipe_lines' ) ) {
		foreach ( nr_parse_pipe_lines( $raw, (int) $a['count'] ) as $r ) {
			$when = $r[0] ?? ''; $note = $r[1] ?? '';
			if ( $note === '' ) continue;
			$ts = strtotime( $when );
			$items[] = [ 'date' => $ts ? date_i18n( 'j M Y', $ts ) : $when, 'note' => $note, 'url' => '' ];
		}
	}
	if ( ! $items ) {
		$q = new WP_Query( [ 'post_type' => 'nr_journal', 'posts_per_page' => (int) $a['count'], 'no_found_rows' => true ] );
		while ( $q->have_posts() ) { $q->the_post();
			$items[] = [ 'date' => get_the_date( 'j M Y' ), 'note' => get_the_title(), 'url' => get_permalink() ];
		}
		wp_reset_postdata();
	}
	if ( ! $items ) return '';
	$out = nr_leftovers_css() . '<ol class="nr-studiolog">';
	foreach ( $items as $it ) {
		$note = $it['url'] ? '<a href="' . esc_url( $it['url'] ) . '">' . esc_html( $it['note'] ) . '</a>' : esc_html( $it['note'] );
		$out .= '<li><time class="nr-studiolog__date">' . esc_html( $it['date'] ) . '</time><span class="nr-studiolog__note">' . $note . '</span></li>';
	}
	return $out . '</ol>';
} );

/* ════════════════════════════════════════════════════════════════════════
 * #28 — Video poster: show frame 0 instead of a black box, no ffmpeg needed.
 *  For any <video> without a poster, set preload=metadata and append #t=0.1 to
 *  the source so the browser paints the first frame as the still.
 * ════════════════════════════════════════════════════════════════════════ */
add_action( 'wp_footer', function () {
	?>
	<script>
	(function(){
		function fix(v){
			if(v.getAttribute('poster')) return;
			if(!v.hasAttribute('preload')) v.preload='metadata';
			if(v.hasAttribute('autoplay')) return; // autoplay already shows motion
			var s = v.currentSrc || v.getAttribute('src') ||
				(v.querySelector('source') && v.querySelector('source').getAttribute('src'));
			if(s && s.indexOf('#t=')===-1){
				try{ v.src ? v.src = s+'#t=0.1' : v.querySelector('source').setAttribute('src', s+'#t=0.1'); v.load(); }catch(e){}
			}
		}
		var run=function(){ document.querySelectorAll('video').forEach(fix); };
		if(document.readyState!=='loading') run(); else document.addEventListener('DOMContentLoaded', run);
	})();
	</script>
	<?php
}, 99 );

/* ── one shared <style>, printed once per request ──────────────────────── */
function nr_leftovers_css() {
	static $done = false; if ( $done ) return ''; $done = true;
	return '<style id="nr-leftovers">'
		. '.nr-diptych{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:24px 0}'
		. '.nr-diptych__side{position:relative;text-decoration:none;color:inherit;display:block}'
		. '.nr-diptych__side img{width:100%;height:auto;display:block;border-radius:4px}'
		. '.nr-diptych__cap{position:absolute;left:10px;bottom:10px;font-size:12px;padding:3px 8px;background:rgba(11,12,16,.6);border-radius:3px}'
		. '@media (max-width:640px){.nr-diptych{grid-template-columns:1fr}}'
		. '.nr-studiolog{list-style:none;margin:24px 0;padding:0;max-width:640px}'
		. '.nr-studiolog li{display:flex;gap:16px;padding:10px 0;border-top:1px solid rgba(242,239,233,.14)}'
		. '.nr-studiolog__date{flex:0 0 92px;font:600 11px/1.5 var(--font-mono,monospace);letter-spacing:.08em;text-transform:uppercase;opacity:.55}'
		. '.nr-studiolog__note{font-size:14px;line-height:1.5}.nr-studiolog__note a{color:inherit}'
		. '</style>';
}

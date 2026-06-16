<?php
/**
 * Cal.com booking — self-hosted integration (cal.m1o.at).
 *
 * The theme only embeds Cal; booking processing (Cal webhook → n8n → CRM) is
 * server-side and out of scope here.
 *
 * Config lives on an ACF Options page **Obscura → Buchung**:
 *   • cal_origin (URL, default https://cal.m1o.at) — hard-guarded away from
 *     cal.com / localhost in nr_cal_origin().
 *   • event_types repeater → key (slug) · label · cal_link (e.g. raveenthiran/portrait)
 *
 * Two front-end blocks (added in later phases) — inline embed + popup button —
 * are placed by shortcode and pull the Cal `embed.js` from `cal_origin` only
 * when present on the page (never global, never on home/portfolio). The origin +
 * event map are handed to assets/js/cal-embed.js via wp_localize_script.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── ACF: Options page "Buchung" + fields (no-op without ACF Pro) ──────── */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) return;

	acf_add_options_page( [
		'page_title'  => __( 'Buchung', 'raveenthiran' ),
		'menu_title'  => __( 'Buchung', 'raveenthiran' ),
		'menu_slug'   => 'nr-cal-booking',
		'parent_slug' => 'nr-theme-settings',   // under the Obscura menu
		'capability'  => 'manage_options',
	] );

	acf_add_local_field_group( [
		'key'      => 'group_nr_cal',
		'title'    => __( 'Cal.com booking', 'raveenthiran' ),
		'fields'   => [
			[
				'key'           => 'field_nr_cal_origin',
				'label'         => __( 'Cal origin', 'raveenthiran' ),
				'name'          => 'cal_origin',
				'type'          => 'url',
				'default_value' => 'https://cal.m1o.at',
				'instructions'  => __( 'Your self-hosted Cal instance. Must NOT be cal.com or localhost — it is enforced in code.', 'raveenthiran' ),
			],
			[
				'key'          => 'field_nr_cal_events',
				'label'        => __( 'Event types', 'raveenthiran' ),
				'name'         => 'event_types',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Add event type', 'raveenthiran' ),
				'sub_fields'   => [
					[ 'key' => 'field_nr_cal_ev_key',   'label' => __( 'Key', 'raveenthiran' ),      'name' => 'key',      'type' => 'text', 'instructions' => __( 'Slug used in the shortcode, e.g. portrait', 'raveenthiran' ) ],
					[ 'key' => 'field_nr_cal_ev_label', 'label' => __( 'Label', 'raveenthiran' ),    'name' => 'label',    'type' => 'text' ],
					[ 'key' => 'field_nr_cal_ev_link',  'label' => __( 'Cal link', 'raveenthiran' ), 'name' => 'cal_link', 'type' => 'text', 'instructions' => __( 'e.g. raveenthiran/portrait', 'raveenthiran' ) ],
				],
			],
		],
		'location' => [ [ [ 'param' => 'options_page', 'operator' => '==', 'value' => 'nr-cal-booking' ] ] ],
	] );
} );

/* ── Config readers (ACF, with the theme's get_field polyfill fallback) ── */

/** The self-hosted Cal origin, hard-guarded away from cal.com / localhost. */
function nr_cal_origin() {
	$o = function_exists( 'get_field' ) ? (string) get_field( 'cal_origin', 'option' ) : (string) get_option( 'options_cal_origin', '' );
	$o = trim( $o ) ?: 'https://cal.m1o.at';
	$host = strtolower( (string) wp_parse_url( $o, PHP_URL_HOST ) );
	if ( $host === '' || $host === 'cal.com' || $host === 'www.cal.com'
		|| strpos( $host, 'localhost' ) !== false || strpos( $host, '127.0.0.1' ) !== false ) {
		$o = 'https://cal.m1o.at';
	}
	return untrailingslashit( esc_url_raw( $o ) );
}

/** [ key => [ 'label' => …, 'link' => 'raveenthiran/portrait' ], … ] */
function nr_cal_events() {
	$rows = function_exists( 'get_field' ) ? get_field( 'event_types', 'option' ) : [];
	$out  = [];
	if ( is_array( $rows ) ) {
		foreach ( $rows as $r ) {
			$key  = sanitize_key( $r['key'] ?? '' );
			$link = trim( (string) ( $r['cal_link'] ?? '' ) );
			if ( $key === '' || $link === '' ) continue;
			$out[ $key ] = [ 'label' => (string) ( $r['label'] ?? $key ), 'link' => ltrim( $link, '/' ) ];
		}
	}
	return $out;
}

/** One event by key, or null. */
function nr_cal_event( $key ) {
	$e = nr_cal_events();
	return $e[ sanitize_key( $key ) ] ?? null;
}

/* ── Assets: registered globally, enqueued only on demand ──────────────── */
add_action( 'wp_enqueue_scripts', function () {
	$base = get_template_directory_uri();
	wp_register_style( 'nr-cal', $base . '/assets/css/cal-booking.css', [ 'nr-theme' ], NR_THEME_VERSION );
	wp_register_script( 'nr-cal', $base . '/assets/js/cal-embed.js', [], NR_THEME_VERSION, true );
} );

/**
 * Enqueue the Cal assets + hand the config to JS. Called by the render helpers
 * (later phases) so Cal never loads on a page without a booking block.
 */
function nr_cal_enqueue() {
	static $done = false;
	if ( $done ) return;
	$done = true;
	wp_enqueue_style( 'nr-cal' );
	wp_enqueue_script( 'nr-cal' );
	wp_localize_script( 'nr-cal', 'NR_CAL', [
		'origin' => nr_cal_origin(),
		'events' => nr_cal_events(),
	] );
}

/* ── Phase 2: inline embed — nr_cal_inline() + [nr_cal_inline key="…"] ──── */

/**
 * Render an inline Cal embed for an event-type key. Returns '' when the key is
 * unknown (or an admin hint for editors). Enqueues Cal only when used.
 */
function nr_cal_inline( $key ) {
	$ev = nr_cal_event( $key );
	if ( ! $ev ) {
		return current_user_can( 'edit_theme_options' )
			? '<p class="nr-admin-hint">' . esc_html( sprintf( __( 'Cal: no event type “%s” — add it under Obscura → Buchung.', 'raveenthiran' ), $key ) ) . '</p>'
			: '';
	}
	nr_cal_enqueue();
	ob_start();
	get_template_part( 'parts/booking/cal-inline', '', $ev );
	return ob_get_clean();
}

add_shortcode( 'nr_cal_inline', function ( $atts ) {
	$a = shortcode_atts( [ 'key' => '' ], $atts, 'nr_cal_inline' );
	return nr_cal_inline( $a['key'] );
} );

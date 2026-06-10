<?php
/**
 * inc/conversion-extra.php — IDEAS-200 Batch 2 (conversion, low-risk slice).
 *
 * Shipped here: #41 visitor shortlist · #44 exit-intent offer · #47 trust microcopy ·
 * #49 availability line on Enquire · #68 referral-code capture · #69 packages table ·
 * #71 press wall · #73 seasonal banner · #74 WhatsApp project ref · #77 share button.
 *
 * Visitor-facing toggles default OFF. The front-end behaviours live in theme.js
 * and read a small JSON config printed in the footer.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Body classes for the JS-driven, opt-in features. */
add_filter( 'body_class', function ( $c ) {
	if ( nr_opt( 'nr_fx_shortlist', '0' ) === '1' ) $c[] = 'nr-shortlist';
	if ( nr_opt( 'nr_fx_exit', '0' ) === '1' && ! is_singular( 'nr_project' ) ) $c[] = 'nr-exit';
	return $c;
} );

/* Config the front-end reads (trust line, exit offer, availability, referral). */
add_action( 'wp_footer', function () {
	$promo = nr_promo_active();
	$cfg = [
		'trust'   => wp_strip_all_tags( (string) nr_opt( 'nr_trust_text', '' ) ),
		'exit'    => wp_strip_all_tags( (string) nr_opt( 'nr_exit_text', '' ) ),
		'avail'   => wp_strip_all_tags( (string) nr_opt( 'nr_avail_dates', '' ) ),
		'enquire' => function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' ),
	];
	echo '<script type="application/json" id="nr-conv">' . wp_json_encode( $cfg ) . '</script>' . "\n";

	/* #73 — seasonal banner (date-bounded; dismissible client-side). */
	if ( $promo ) {
		printf(
			'<div class="nr-promo" data-promo role="region" aria-label="%s"><span>%s</span>' .
			'<button class="nr-promo__x" aria-label="%s">✕</button></div>',
			esc_attr__( 'Announcement', 'raveenthiran' ),
			wp_kses( $promo, [ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ], 'strong' => [], 'em' => [] ] ),
			esc_attr__( 'Dismiss', 'raveenthiran' )
		);
	}
}, 30 );

/* Is the seasonal banner currently within its window? */
function nr_promo_active() {
	$txt = trim( (string) nr_opt( 'nr_promo_text', '' ) );
	if ( $txt === '' ) return '';
	$until = trim( (string) nr_opt( 'nr_promo_until', '' ) );
	if ( $until !== '' ) {
		$ts = strtotime( $until . ' 23:59:59' );
		if ( $ts && time() > $ts ) return '';
	}
	return $txt;
}

/* =============================================================
   #69 — packages comparison table. One tier per line:
   "Name | €from | feature; feature; feature"
   ============================================================= */
add_shortcode( 'nr_packages', function () {
	$raw = trim( (string) nr_opt( 'nr_packages', '' ) );
	if ( $raw === '' ) return '';
	$rows = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) );
	if ( ! $rows ) return '';
	$out = '<div class="nr-packages">';
	foreach ( $rows as $r ) {
		$p = array_map( 'trim', explode( '|', $r ) );
		$name = $p[0] ?? ''; $price = $p[1] ?? ''; $feats = isset( $p[2] ) ? array_filter( array_map( 'trim', explode( ';', $p[2] ) ) ) : [];
		if ( $name === '' ) continue;
		$out .= '<div class="nr-package"><div class="nr-package__head"><span class="nr-package__name">' . esc_html( $name ) . '</span>';
		if ( $price ) $out .= '<span class="nr-package__price">' . esc_html( $price ) . '</span>';
		$out .= '</div>';
		if ( $feats ) { $out .= '<ul>'; foreach ( $feats as $f ) $out .= '<li>' . esc_html( $f ) . '</li>'; $out .= '</ul>'; }
		$url = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );
		$out .= '<a class="nr-btn nr-btn--ghost" href="' . esc_url( add_query_arg( 'service', sanitize_title( $name ), $url ) ) . '"><span>' . esc_html__( 'Enquire', 'raveenthiran' ) . '</span> <span>→</span></a></div>';
	}
	return $out . '</div>';
} );

/* =============================================================
   #71 — press wall. Renders the ACF press_list repeater (option).
   ============================================================= */
add_shortcode( 'nr_press', function () {
	if ( ! function_exists( 'get_field' ) ) return '';
	$rows = get_field( 'press_list', 'option' );
	if ( ! is_array( $rows ) || ! $rows ) return '';
	$out = '<div class="nr-press">';
	foreach ( $rows as $r ) {
		$medium = $r['medium'] ?? ''; $title = $r['pr_title'] ?? ''; $year = $r['year'] ?? ''; $url = $r['pr_url'] ?? '';
		if ( $medium === '' && $title === '' ) continue;
		$inner = '<span class="nr-press__medium">' . esc_html( $medium ) . '</span>'
			. ( $title ? '<span class="nr-press__title">' . esc_html( $title ) . '</span>' : '' )
			. ( $year ? '<span class="nr-press__year">' . esc_html( $year ) . '</span>' : '' );
		$out .= $url
			? '<a class="nr-press__item" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $inner . '</a>'
			: '<div class="nr-press__item">' . $inner . '</div>';
	}
	return $out . '</div>';
} );

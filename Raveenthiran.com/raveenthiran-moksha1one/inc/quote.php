<?php
/**
 * Quote / pricing module.
 *
 * Powers the interactive price-check calculator in the Enquire popover.
 * Reads pricing through ACF first (get_field('…','option')), then falls
 * back to the acf-polyfill option store, then to sensible built-in
 * defaults — so the calculator works with ACF Pro, free ACF, or neither.
 *
 * Also registers the previously-orphaned `nr-site-settings` ACF options
 * page (the quote fields in inc/acf-fields.php targeted it but it was
 * never created), and redirects the retired Booking / Contact pages to
 * the merged Enquire page.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────────────────────────────
 * Register the ACF options page the quote fields target.
 * ───────────────────────────────────────────────────────────── */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_options_page' ) ) return;
	// One discoverable, top-level home for EVERY ACF content group (hero, about,
	// CTA, FAQ, availability, quote, license, local SEO, newsletter, awards,
	// press). It was buried under Appearance → "Pricing & Quote" even though it
	// holds all site content — now it sits in the main menu as "Site Content".
	acf_add_options_page( [
		'page_title'  => __( 'moksha1one — Site Content', 'raveenthiran' ),
		'menu_title'  => __( 'Site Content', 'raveenthiran' ),
		'menu_slug'   => 'nr-site-settings',
		'capability'  => 'manage_options',
		'position'    => 59,
		'icon_url'    => 'dashicons-layout',
		'redirect'    => false,
	] );
} );

/* ─────────────────────────────────────────────────────────────
 * Pricing data — normalised, with defaults.
 *   types   => [ ['slug','label','base','hours'], … ]
 *   extras  => [ ['slug','label','price'], … ]
 *   license => float   (flat add-on)
 *   per_km  => float   (travel cost per km)
 *   currency => '€'
 * ───────────────────────────────────────────────────────────── */
function nr_quote_data() {
	$raw_types  = function_exists( 'get_field' ) ? get_field( 'quote_base_prices', 'option' ) : null;
	$raw_extras = function_exists( 'get_field' ) ? get_field( 'quote_extras', 'option' )       : null;
	$license    = function_exists( 'get_field' ) ? get_field( 'license_base_price', 'option' )  : null;
	$per_km     = function_exists( 'get_field' ) ? get_field( 'travel_per_km', 'option' )       : null;

	if ( ! is_array( $raw_types ) || empty( $raw_types ) ) {
		$raw_types = [
			[ 'type' => 'Portrait',   'base_price' => 450,  'hours' => 2 ],
			[ 'type' => 'Editorial',  'base_price' => 1200, 'hours' => 6 ],
			[ 'type' => 'Event',      'base_price' => 900,  'hours' => 4 ],
			[ 'type' => 'Commercial', 'base_price' => 1800, 'hours' => 8 ],
		];
	}
	if ( ! is_array( $raw_extras ) || empty( $raw_extras ) ) {
		$raw_extras = [
			[ 'label' => 'Extra hour on set',      'price' => 180 ],
			[ 'label' => '+10 retouched selects',  'price' => 240 ],
			[ 'label' => 'Second location',        'price' => 300 ],
			[ 'label' => 'Express delivery · 48h', 'price' => 350 ],
		];
	}

	$types = [];
	foreach ( $raw_types as $r ) {
		$label = trim( (string) ( $r['type'] ?? '' ) );
		if ( $label === '' ) continue;
		$types[] = [
			'slug'  => sanitize_title( $label ),
			'label' => $label,
			'base'  => (float) ( $r['base_price'] ?? 0 ),
			'hours' => (float) ( $r['hours'] ?? 0 ),
		];
	}

	$extras = [];
	foreach ( $raw_extras as $r ) {
		$label = trim( (string) ( $r['label'] ?? '' ) );
		if ( $label === '' ) continue;
		$extras[] = [
			'slug'  => sanitize_title( $label ),
			'label' => $label,
			'price' => (float) ( $r['price'] ?? 0 ),
		];
	}

	return [
		'types'    => $types,
		'extras'   => $extras,
		'license'  => (float) ( $license !== null && $license !== '' ? $license : 150 ),
		'per_km'   => (float) ( $per_km !== null && $per_km !== '' ? $per_km : 0.42 ),
		'currency' => nr_opt( 'nr_currency', '€' ),
	];
}

/* ─────────────────────────────────────────────────────────────
 * FAQ items — merged into the Enquire page (no standalone FAQ page).
 * Reads an ACF `faq_items` repeater (question/answer) on the Enquire
 * page if present, otherwise returns sensible defaults.
 * ───────────────────────────────────────────────────────────── */
function nr_faq_items() {
	$rows = function_exists( 'get_field' ) ? get_field( 'faq_items', nr_enquire_page_id() ?: false ) : null;
	$out  = [];
	if ( is_array( $rows ) ) {
		foreach ( $rows as $r ) {
			$qn = trim( (string) ( $r['question'] ?? '' ) );
			$an = trim( (string) ( $r['answer'] ?? '' ) );
			if ( $qn && $an ) $out[] = [ 'q' => $qn, 'a' => $an ];
		}
	}
	if ( $out ) return $out;

	return [
		[ 'q' => __( 'How far in advance should I book?', 'raveenthiran' ),         'a' => __( 'For portrait sessions I recommend booking 2–4 weeks in advance. Editorial commissions and weddings require a minimum of 6–8 weeks, depending on the scope and travel involved.', 'raveenthiran' ) ],
		[ 'q' => __( 'Do you travel for shoots?', 'raveenthiran' ),                 'a' => __( 'Yes. I am based in Vienna but regularly work across Europe and beyond. Travel costs are invoiced separately at cost. For international projects, a travel day rate applies.', 'raveenthiran' ) ],
		[ 'q' => __( 'What is your editing style?', 'raveenthiran' ),               'a' => __( 'I work in a quiet, analogue-feeling style — warm shadows, restrained contrast, a sense of stillness. I do not over-retouch. The aim is that the photographs still look like photographs.', 'raveenthiran' ) ],
		[ 'q' => __( 'How are images delivered?', 'raveenthiran' ),                 'a' => __( 'Selects are delivered via a private online gallery within 5–7 business days, as high-resolution JPEGs. Print-ready TIFFs and RAW files are available on request.', 'raveenthiran' ) ],
		[ 'q' => __( 'Can I use the images for commercial purposes?', 'raveenthiran' ), 'a' => __( 'Usage rights are specified in the contract. Personal sessions include personal use; editorial commissions include publication rights for the agreed outlet. Broader commercial licensing is available separately.', 'raveenthiran' ) ],
		[ 'q' => __( 'What if I need to reschedule?', 'raveenthiran' ),             'a' => __( 'Rescheduling is possible without penalty up to 7 days before the shoot date. Cancellations within 48 hours of the session are subject to the full session fee.', 'raveenthiran' ) ],
	];
}

/* ─────────────────────────────────────────────────────────────
 * Enquire page resolution.
 * ───────────────────────────────────────────────────────────── */
function nr_enquire_page_id() {
	static $id = null;
	if ( $id !== null ) return $id;

	$pages = get_posts( [
		'post_type'   => 'page',
		'post_status' => 'publish',
		'numberposts' => 1,
		'fields'      => 'ids',
		'meta_key'    => '_wp_page_template',
		'meta_value'  => 'page-enquire.php',
	] );
	if ( ! empty( $pages ) ) return $id = (int) $pages[0];

	$by_slug = get_page_by_path( 'enquire' );
	return $id = ( $by_slug ? (int) $by_slug->ID : 0 );
}

function nr_enquire_url() {
	$pid = nr_enquire_page_id();
	return $pid ? get_permalink( $pid ) : home_url( '/enquire' );
}

/* ─────────────────────────────────────────────────────────────
 * Merge: redirect retired Booking / Contact pages to Enquire.
 * Only redirects when an Enquire page actually exists, so the
 * legacy pages keep working until it's created.
 * ───────────────────────────────────────────────────────────── */
add_action( 'template_redirect', function () {
	if ( is_admin() || ! is_page() ) return;

	$tpl = (string) get_page_template_slug( get_queried_object_id() );
	$is_legacy =
		   in_array( $tpl, [ 'page-booking.php', 'page-contact.php', 'page-faq.php' ], true )
		|| is_page( [ 'booking', 'contact', 'faq' ] );
	if ( ! $is_legacy ) return;

	$enq = nr_enquire_page_id();
	if ( $enq && get_queried_object_id() !== $enq ) {
		wp_safe_redirect( get_permalink( $enq ), 301 );
		exit;
	}
} );

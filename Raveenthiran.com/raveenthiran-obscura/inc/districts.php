<?php
/**
 * inc/districts.php — IDEAS-50-NEXT #36: Vienna district landing pages (v4.66.0).
 * A plugin-light, low-risk local-SEO helper. Drop [nr_district name="Neubau"
 * code="1070"] on a thin page per area; it renders a localised heading + intro,
 * a small project grid, a CTA to enquire, and LocalBusiness/areaServed schema.
 * No rewrite rules (nothing to flush), no new admin pages.
 *
 *   [nr_district name="Neubau" code="1070" cat="editorial" intro="..." count="6"]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'nr_district', function ( $atts ) {
	$a = shortcode_atts( [
		'name'  => '',
		'code'  => '',
		'cat'   => '',
		'intro' => '',
		'count' => 6,
	], $atts, 'nr_district' );

	$name = trim( (string) $a['name'] );
	if ( $name === '' ) return '';
	$code  = trim( (string) $a['code'] );
	$city  = (string) nr_opt( 'nr_location', 'Vienna' );
	$place = $code ? "$name ($code), $city" : "$name, $city";
	$brand = get_bloginfo( 'name' );

	$heading = sprintf(
		/* translators: %s = district, city */
		__( 'Editorial &amp; portrait photography in %s', 'raveenthiran' ),
		esc_html( $place )
	);
	$intro = $a['intro'] !== ''
		? $a['intro']
		: sprintf(
			__( '%1$s works with clients across %2$s — editorial portraits, brand and event photography, shot on location and finished to gallery standard. Based in %3$s and available for commissions throughout the district.', 'raveenthiran' ),
			$brand, esc_html( $name ), esc_html( $city )
		);

	// A small, relevant project grid (optionally filtered by category).
	$args = [ 'post_type' => 'nr_project', 'posts_per_page' => max( 1, min( 12, (int) $a['count'] ) ), 'no_found_rows' => true ];
	if ( $a['cat'] !== '' ) {
		$args['tax_query'] = [ [ 'taxonomy' => 'nr_project_cat', 'field' => 'slug', 'terms' => array_map( 'trim', explode( ',', $a['cat'] ) ) ] ];
	}
	$q = new WP_Query( $args );
	$grid = '';
	while ( $q->have_posts() ) { $q->the_post();
		$grid .= '<a class="nr-district__card" href="' . esc_url( get_permalink() ) . '">';
		if ( has_post_thumbnail() ) {
			$grid .= get_the_post_thumbnail( get_the_ID(), 'nr-thumb', [ 'loading' => 'lazy', 'decoding' => 'async', 'alt' => esc_attr( get_the_title() ) ] );
		}
		$grid .= '<span>' . esc_html( get_the_title() ) . '</span></a>';
	}
	wp_reset_postdata();

	$enquire = function_exists( 'nr_enquire_url' ) ? nr_enquire_url() : home_url( '/enquire' );

	// LocalBusiness + areaServed schema (helps the long-tail local query).
	$schema = [
		'@context' => 'https://schema.org',
		'@type'    => 'ProfessionalService',
		'name'     => $brand,
		'areaServed' => [ '@type' => 'Place', 'name' => $place ],
		'address'  => [ '@type' => 'PostalAddress', 'addressLocality' => $city, 'postalCode' => $code, 'addressCountry' => 'AT' ],
		'url'      => home_url( '/' ),
		'image'    => get_site_icon_url( 512 ) ?: '',
		'makesOffer' => [ '@type' => 'Offer', 'itemOffered' => [ '@type' => 'Service', 'name' => 'Photography', 'areaServed' => $name ] ],
	];

	$out  = nr_district_css();
	$out .= '<section class="nr-district">';
	$out .= '<h1 class="nr-district__h">' . $heading . '</h1>';
	$out .= '<p class="nr-district__intro">' . esc_html( $intro ) . '</p>';
	if ( $grid !== '' ) $out .= '<div class="nr-district__grid">' . $grid . '</div>';
	$out .= '<p class="nr-district__cta"><a class="nr-btn nr-btn--primary" href="' . esc_url( $enquire ) . '"><span>'
		. esc_html__( 'Enquire about a shoot', 'raveenthiran' ) . '</span> <span aria-hidden="true">→</span></a></p>';
	$out .= '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	$out .= '</section>';
	return $out;
} );

function nr_district_css() {
	static $done = false; if ( $done ) return ''; $done = true;
	return '<style id="nr-district">'
		. '.nr-district{max-width:920px;margin:0 auto;padding:8px 0}'
		. '.nr-district__h{font-size:clamp(28px,4vw,52px);line-height:1.05;letter-spacing:-.02em;margin:0 0 18px}'
		. '.nr-district__intro{font-size:var(--fs-lede,1.15rem);line-height:1.6;color:var(--ink-2,#b8b4ab);max-width:64ch;margin:0 0 28px}'
		. '.nr-district__grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;margin:0 0 28px}'
		. '.nr-district__card{text-decoration:none;color:inherit;display:block}'
		. '.nr-district__card img{width:100%;height:auto;aspect-ratio:4/5;object-fit:cover;border-radius:4px;display:block}'
		. '.nr-district__card span{display:block;font-size:13px;margin-top:6px;opacity:.85}'
		. '.nr-district__cta{margin:8px 0 0}'
		. '</style>';
}

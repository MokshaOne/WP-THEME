<?php
/**
 * VPG v2 — helpers · tiny utilities used across templates.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Convert  <em>Word</em>  in content fields safely without losing semantics */
function vpg_em( $html ) {
    return wp_kses( $html, [ 'em' => [], 'strong' => [], 'br' => [], 'span' => [ 'class' => [] ], 'small' => [], 'sup' => [] ] );
}

/* Current request URL (used by the Gallery header to flag the active nav item) */
function vpg_current_url() {
    $scheme = is_ssl() ? 'https://' : 'http://';
    $host   = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '';
    $uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
    return esc_url_raw( $scheme . $host . strtok( $uri, '?' ) );
}

/* Site-wide identity (used by header/footer/schema) */
function vpg_identity() {
    return [
        'brand'    => get_bloginfo( 'name' ),
        'tagline'  => get_bloginfo( 'description' ),
        'email'    => get_theme_mod( 'vpg_email',    get_option( 'admin_email' ) ),
        'location' => get_theme_mod( 'vpg_location', 'Wien · UTC+1' ),
        'booking'  => get_theme_mod( 'vpg_booking',  'Q3 · slots open' ),
        'since'    => get_theme_mod( 'vpg_since',    '2018' ),
    ];
}

/* Format a date like "Jun MMXXVI" → fancy month + Roman year. Used in single-magazine. */
function vpg_roman_date( $ts ) {
    $month = date_i18n( 'F', $ts );
    $year  = (int) date_i18n( 'Y', $ts );
    return $month . ' ' . vpg_roman( $year );
}

function vpg_roman( $n ) {
    $map = [ 1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I' ];
    $out = '';
    foreach ( $map as $v => $r ) {
        while ( $n >= $v ) { $out .= $r; $n -= $v; }
    }
    return $out;
}

/* Count helper · returns "n type" with proper pluralisation */
function vpg_count_label( $post_type, $singular = '', $plural = '' ) {
    $c = (int) ( wp_count_posts( $post_type )->publish ?? 0 );
    if ( ! $singular || ! $plural ) return $c;
    return sprintf( _n( $singular, $plural, $c, 'vpg-v2' ), $c );
}

/* Member badge · returns true if current user has the 'vpg_member' role */
function vpg_is_member() {
    if ( ! is_user_logged_in() ) return false;
    $u = wp_get_current_user();
    return in_array( 'vpg_member', (array) $u->roles, true ) || in_array( 'administrator', (array) $u->roles, true );
}

/* Safe ACF read · works whether ACF Pro is installed or not */
function vpg_field( $key, $post_id = null ) {
    if ( function_exists( 'get_field' ) ) return get_field( $key, $post_id );
    return get_post_meta( $post_id ?: get_the_ID(), $key, true );
}

/* Get lat/lng for a post · supports both the v2 split-field model
   (location_lat + location_lng) AND the v1 ACF "Lat, Lng" combined field
   (coordinates / studio_coordinates / shop_coordinates).
   Returns [lat, lng] floats, or null if nothing valid is set. */
function vpg_get_coords( $post_id ) {
    $type = get_post_type( $post_id );

    // v2 split fields
    $split_keys = [
        'vpg_location' => [ 'location_lat', 'location_lng', 'coordinates' ],
        'vpg_studio'   => [ 'studio_lat',   'studio_lng',   'studio_coordinates' ],
        'vpg_shop'     => [ 'shop_lat',     'shop_lng',     'shop_coordinates' ],
    ];
    if ( ! isset( $split_keys[ $type ] ) ) return null;

    list( $latKey, $lngKey, $comboKey ) = $split_keys[ $type ];

    $lat = get_post_meta( $post_id, $latKey, true );
    $lng = get_post_meta( $post_id, $lngKey, true );
    if ( $lat !== '' && $lng !== '' && is_numeric( $lat ) && is_numeric( $lng ) ) {
        return [ (float) $lat, (float) $lng ];
    }

    // v1 combined field · "lat, lng" or "lat,lng"
    $combo = function_exists( 'get_field' ) ? get_field( $comboKey, $post_id ) : get_post_meta( $post_id, $comboKey, true );
    if ( $combo && is_string( $combo ) && strpos( $combo, ',' ) !== false ) {
        $parts = array_map( 'trim', explode( ',', $combo, 2 ) );
        if ( count( $parts ) === 2 && is_numeric( $parts[0] ) && is_numeric( $parts[1] ) ) {
            $lat = (float) $parts[0];
            $lng = (float) $parts[1];
            if ( abs( $lat ) <= 90 && abs( $lng ) <= 180 ) return [ $lat, $lng ];
        }
    }

    return null;
}

/* Reading-time estimate in minutes · ~220 wpm by default */
function vpg_reading_time( $text, $wpm = 220 ) {
    $words = str_word_count( wp_strip_all_tags( (string) $text ) );
    return max( 1, (int) ceil( $words / max( 60, $wpm ) ) );
}

/* Reading-time for a magazine issue (sum of all article bodies) */
function vpg_issue_reading_time( $issue_id ) {
    $articles = function_exists( 'vpg_get_articles' ) ? vpg_get_articles( $issue_id ) : [];
    $words    = 0;
    foreach ( $articles as $a ) $words += str_word_count( wp_strip_all_tags( $a['body'] ?? '' ) );
    return max( 1, (int) ceil( $words / 220 ) );
}

/* Render a single skin-style chip · used everywhere */
function vpg_chip( $post_type, $label = '' ) {
    $cls = [
        'vpg_event'    => 'event',
        'vpg_location' => 'loc',
        'vpg_magazine' => 'mag',
        'vpg_review'   => 'review',
        'vpg_shop'     => 'shop',
        'vpg_studio'   => 'studio',
        'vpg_tutorial' => 'tut',
    ];
    $modifier = $cls[ $post_type ] ?? '';
    $defaults = [
        'vpg_event'    => 'Event',
        'vpg_location' => 'Location',
        'vpg_magazine' => 'Magazine',
        'vpg_review'   => 'Review',
        'vpg_shop'     => 'Shop',
        'vpg_studio'   => 'Studio',
        'vpg_tutorial' => 'Tutorial',
    ];
    $label = $label ?: ( $defaults[ $post_type ] ?? ucfirst( str_replace( 'vpg_', '', $post_type ) ) );
    printf(
        '<span class="vpg-chip vpg-chip--%s"><span class="vpg-chip__dot"></span> %s</span>',
        esc_attr( $modifier ),
        esc_html( $label )
    );
}
